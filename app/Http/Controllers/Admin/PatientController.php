<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;

use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        // 1. Fetch Registered Users
        $users = User::where('is_admin', false)->latest()->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'patient_id' => $user->patient_id,
                'created_at' => $user->created_at,
                'type' => 'user',
            ];
        });

        // 2. Fetch Guests (Group by Name + Phone)
        $guests = \App\Models\Booking::whereNull('user_id')
            ->whereNotNull('customer_name')
            ->select('customer_name', 'customer_phone')
            ->selectRaw('MIN(created_at) as created_at')
            ->selectRaw('MIN(id) as first_booking_id')
            ->selectRaw('MAX(id) as latest_booking_id')
            ->groupBy('customer_name', 'customer_phone')
            ->get()
            ->map(function ($guest) {
                // Calculate HN-dmY-XXXX
                $firstBookingDate = \Carbon\Carbon::parse($guest->created_at);

                // Count bookings in that month up to the first booking ID
                $monthlySequence = \App\Models\Booking::whereYear('created_at', $firstBookingDate->year)
                    ->whereMonth('created_at', $firstBookingDate->month)
                    ->where('id', '<=', $guest->first_booking_id)
                    ->count();

                $hnId = 'HN-' . $firstBookingDate->format('dmY') . '-' . str_pad($monthlySequence, 4, '0', STR_PAD_LEFT);

                return [
                    'id' => 'guest_' . $guest->latest_booking_id,
                    'name' => $guest->customer_name,
                    'email' => $guest->customer_phone,
                    'phone_number' => $guest->customer_phone,
                    'patient_id' => $hnId,
                    'created_at' => $guest->created_at,
                    'type' => 'guest',
                    'booking_id' => $guest->latest_booking_id
                ];
            });

        // 3. Merge Collection
        $allPatients = $users->concat($guests);

        // 4. Filter / Search
        if ($request->has('search')) {
            $search = strtolower($request->input('search'));
            $allPatients = $allPatients->filter(function ($item) use ($search) {
                return str_contains(strtolower($item['name']), $search) ||
                    str_contains(strtolower($item['email']), $search) ||
                    str_contains(strtolower($item['phone_number']), $search) ||
                    str_contains(strtolower($item['patient_id']), $search);
            });
        }

        // 5. Sort by created_at desc
        $allPatients = $allPatients->sortByDesc('created_at')->values();

        // 6. Paginate manually
        $perPage = 10;
        $page = $request->input('page', 1);
        $total = $allPatients->count();
        $items = $allPatients->slice(($page - 1) * $perPage, $perPage)->values();

        $patients = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return Inertia::render('Admin/Patients/Index', [
            'patients' => $patients,
            'filters' => $request->only(['search']),
        ]);
    }

    public function show(User $user)
    {
        // Ensure we are viewing a patient, not an admin (optional, depending on requirements)
        if ($user->is_admin) {
            // Redirect or show error if needed, but for now we might want to see admins too?
            // Let's stick to patients for consistency with the controller name, but viewing admins is also fine.
        }

        // Helper to get stats and medical summary
        $stats = [
            'total_visits' => $user->bookings()->where('status', 'completed')->count(),
            'last_visit' => $user->bookings()->where('status', 'completed')->latest('appointment_date')->first()?->appointment_date,
            'next_appointment' => $user->bookings()->whereIn('status', ['pending', 'confirmed'])->where('appointment_date', '>=', now()->toDateString())->orderBy('appointment_date')->orderBy('start_time')->first(),
        ];

        // Get latest treatment record for medical summary
        $latestRecord = \App\Models\TreatmentRecord::whereIn('booking_id', $user->bookings()->pluck('id'))
            ->latest()
            ->first();

        $medicalSummary = $latestRecord ? [
            'drug_allergy' => $latestRecord->drug_allergy,
            'underlying_disease' => $latestRecord->underlying_disease,
            'weight' => $latestRecord->weight,
            'height' => $latestRecord->height,
            'blood_pressure' => $latestRecord->blood_pressure,
            'pain_areas' => $latestRecord->pain_areas,
            'last_updated' => $latestRecord->created_at,
        ] : null;

        $user->load(['bookings.doctor']); // Load booking history

        return Inertia::render('Admin/Patients/Show', [
            'patient' => $user,
            'bookings' => $user->bookings()->latest()->get(),
            'stats' => $stats,
            'medicalSummary' => $medicalSummary
        ]);
    }

    public function showGuest(\App\Models\Booking $booking)
    {
        // Find other bookings by this guest (matching name and phone)
        $relatedBookings = \App\Models\Booking::whereNull('user_id')
            ->where('customer_name', $booking->customer_name)
            ->where('customer_phone', $booking->customer_phone)
            ->latest()
            ->with(['doctor'])
            ->get();

        // Logic to reconstruct the ID
        // We need the FIRST booking of this guest to establish the "Start Date" and "Sequence"
        $firstBooking = \App\Models\Booking::whereNull('user_id')
            ->where('customer_name', $booking->customer_name)
            ->where('customer_phone', $booking->customer_phone)
            ->orderBy('id')
            ->first();

        $firstBookingDate = \Carbon\Carbon::parse($firstBooking->created_at);

        $monthlySequence = \App\Models\Booking::whereYear('created_at', $firstBookingDate->year)
            ->whereMonth('created_at', $firstBookingDate->month)
            ->where('id', '<=', $firstBooking->id)
            ->count();

        $hnId = 'HN-' . $firstBookingDate->format('dmY') . '-' . str_pad($monthlySequence, 4, '0', STR_PAD_LEFT);

        // Construct a "Fake" patient object
        $guestPatient = [
            'id' => 'guest_' . $booking->id,
            'name' => $booking->customer_name,
            'email' => '-',
            'phone_number' => $booking->customer_phone,
            'patient_id' => $hnId,
            'created_at' => $firstBooking->created_at,
            'is_guest' => true
        ];

        // Guest Stats
        $guestBookingIds = $relatedBookings->pluck('id');

        $stats = [
            'total_visits' => $relatedBookings->where('status', 'completed')->count(),
            'last_visit' => $relatedBookings->where('status', 'completed')->sortByDesc('appointment_date')->first()?->appointment_date,
            'next_appointment' => $relatedBookings->whereIn('status', ['pending', 'confirmed'])->where('appointment_date', '>=', now()->toDateString())->sortBy('appointment_date')->first(),
        ];

        // Get latest treatment record for guest
        $latestRecord = \App\Models\TreatmentRecord::whereIn('booking_id', $guestBookingIds)
            ->latest()
            ->first();

        $medicalSummary = $latestRecord ? [
            'drug_allergy' => $latestRecord->drug_allergy,
            'underlying_disease' => $latestRecord->underlying_disease,
            'weight' => $latestRecord->weight,
            'height' => $latestRecord->height,
            'blood_pressure' => $latestRecord->blood_pressure,
            'pain_areas' => $latestRecord->pain_areas,
            'last_updated' => $latestRecord->created_at,
        ] : null;

        return Inertia::render('Admin/Patients/Show', [
            'patient' => $guestPatient,
            'bookings' => $relatedBookings,
            'stats' => $stats,
            'medicalSummary' => $medicalSummary
        ]);
    }
}
