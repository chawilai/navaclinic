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
        // 1. Base Query for Registered Users (excluding admins and doctors)
        $query = User::where('is_admin', false)
            ->where('is_doctor', false)
            ->latest();

        // 2. Filter / Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('patient_id', 'like', "%{$search}%")
                    ->orWhere('id_card_number', 'like', "%{$search}%");
            });
        }

        // 3. Paginate and Transform
        $patients = $query->paginate(10)->through(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'patient_id' => $user->patient_id,
                'id_card_number' => $user->id_card_number,
                'created_at' => $user->created_at,
                'type' => 'user',
            ];
        });

        // 4. Get Unregistered Bookings for the Registration Modal
        $unregisteredBookings = \App\Models\Booking::whereNull('user_id')
            ->whereIn('status', ['pending', 'confirmed', 'completed'])
            ->orderBy('appointment_date', 'desc')
            ->orderBy('start_time', 'asc')
            ->take(100)
            ->get(['id', 'customer_name', 'customer_phone', 'appointment_date', 'start_time']);

        return Inertia::render('Admin/Patients/Index', [
            'patients' => $patients,
            'filters' => $request->only(['search']),
            'availablePackages' => \App\Models\ServicePackage::where('is_active', true)->get(),
            'unregisteredBookings' => $unregisteredBookings,
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
            'total_visits' => $user->visits()->count(),
            'last_visit' => $user->visits()->latest('visit_date')->first()?->visit_date?->format('Y-m-d'),
            'next_appointment' => $user->bookings()->whereIn('status', ['pending', 'confirmed'])->where('appointment_date', '>=', now()->toDateString())->orderBy('appointment_date')->orderBy('start_time')->first(),
        ];

        // Get latest treatment record for medical summary
        // CHANGED: Source from Visits instead of Bookings to include walk-ins
        $latestRecord = \App\Models\TreatmentRecord::whereIn('visit_id', $user->visits()->pluck('id'))
            ->latest()
            ->first();

        // Fallback: If no visit-based record, check booking-based (legacy support)
        if (!$latestRecord) {
            $latestRecord = \App\Models\TreatmentRecord::whereIn('booking_id', $user->bookings()->pluck('id'))
                ->latest()
                ->first();
        }

        $medicalSummary = $latestRecord ? [
            // Existing fields
            'weight' => $latestRecord->weight,
            'height' => $latestRecord->height,
            'blood_pressure' => $latestRecord->blood_pressure,

            // New Vitals
            'temperature' => $latestRecord->temperature,
            'pulse_rate' => $latestRecord->pulse_rate,
            'respiratory_rate' => $latestRecord->respiratory_rate,

            // Medical Info
            'drug_allergy' => $latestRecord->drug_allergy,
            'underlying_disease' => $latestRecord->underlying_disease,
            'chief_complaint' => $latestRecord->chief_complaint,
            'diagnosis' => $latestRecord->diagnosis,

            'pain_areas' => $latestRecord->pain_areas,
            'last_updated' => $latestRecord->created_at,
        ] : null;

        $user->load(['bookings.doctor', 'visits.doctor', 'visits.booking']); // Load history

        return Inertia::render('Admin/Patients/Show', [
            'patient' => $user,
            'bookings' => $user->bookings()->where('is_admin_booked', false)->with('doctor')->latest()->get(),
            'appointments' => $user->bookings()->where('is_admin_booked', true)->with('doctor')->latest()->get(),
            'visits' => $user->visits()->with(['doctor', 'booking'])->latest('visit_date')->get(),
            'stats' => $stats,
            'medicalSummary' => $medicalSummary
        ]);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'id_card_number' => 'required|string|max:20',
            'date_of_birth' => 'required|date',
            'gender' => 'required|string',
            'race' => 'nullable|string',
            'nationality' => 'nullable|string',
            'religion' => 'nullable|string',
            'occupation' => 'nullable|string',
            'address' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string',
            'emergency_contact_phone' => 'nullable|string',
            'underlying_disease' => 'nullable|string',
            'surgery_history' => 'nullable|string',
            'drug_allergy' => 'nullable|string',
            'accident_history' => 'nullable|string',
        ]);

        // Generate HN
        $datePart = now()->format('dmY');
        $countToday = User::whereDate('created_at', now()->toDateString())->count() + 1;
        $hnId = 'HN-' . $datePart . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);

        // Generate dummy email if not provided (required by DB)
        $email = $validated['phone_number']
            ? 'patient_' . $validated['phone_number'] . '@navaclinic.com'
            : 'patient_' . uniqid() . '@navaclinic.com';

        // Check for existing email to avoid unique constraint violation if re-registering same phone
        if (User::where('email', $email)->exists()) {
            $email = 'patient_' . uniqid() . '@navaclinic.com';
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $email,
            'password' => \Illuminate\Support\Facades\Hash::make('12345678'),
            'phone_number' => $validated['phone_number'] ?? null,
            'patient_id' => $hnId,
            'is_admin' => false,
            'is_doctor' => false,
            'id_card_number' => $validated['id_card_number'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'race' => $validated['race'] ?? null,
            'nationality' => $validated['nationality'] ?? null,
            'religion' => $validated['religion'] ?? null,
            'occupation' => $validated['occupation'] ?? null,
            'address' => $validated['address'] ?? null,
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
            'underlying_disease' => $validated['underlying_disease'] ?? null,
            'surgery_history' => $validated['surgery_history'] ?? null,
            'drug_allergy' => $validated['drug_allergy'] ?? null,
            'accident_history' => $validated['accident_history'] ?? null,
        ]);

        // Link Booking if selected
        if ($request->filled('link_booking_id')) {
            $booking = \App\Models\Booking::find($request->input('link_booking_id'));
            if ($booking) {
                // If the booking had a guest phone/name, we assume this patient REPLACES that identity
                $booking->update(['user_id' => $user->id]);

                // Optional: You could also fuzzy match other bookings, but let's stick to the explicit selection for now
            }
        }

        return redirect()->route('admin.patients.index')->with('success', 'Patient registered successfully.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'id_card_number' => 'required|string|max:20',
            'date_of_birth' => 'required|date',
            'gender' => 'required|string',
            'race' => 'nullable|string',
            'nationality' => 'nullable|string',
            'religion' => 'nullable|string',
            'occupation' => 'nullable|string',
            'address' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string',
            'emergency_contact_phone' => 'nullable|string',

            // Medical
            'underlying_disease' => 'nullable|string',
            'surgery_history' => 'nullable|string',
            'drug_allergy' => 'nullable|string',
            'accident_history' => 'nullable|string',
        ]);

        if (str_starts_with($id, 'guest_')) {
            // CONVERT GUEST TO REGISTERED USER
            $bookingId = str_replace('guest_', '', $id);
            $latestBooking = \App\Models\Booking::findOrFail($bookingId);

            // Create new User
            // We'll use phone number as email if email is not available or make a dummy one?
            // Generate Patient ID (HN)
            // Format: HN-ddmmyyyy-XXXX (e.g., HN-21012026-0001)
            $datePart = now()->format('dmY');
            $countToday = User::whereDate('created_at', now()->toDateString())->count() + 1;
            $hnId = 'HN-' . $datePart . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);

            // Generate dummy email if needed (required by DB)
            $email = $validated['phone_number']
                ? 'guest_' . $validated['phone_number'] . '@navaclinic.com'
                : 'guest_' . uniqid() . '@navaclinic.com';

            $user = User::create([
                'patient_id' => $hnId, // Set the generated HN
                'name' => $validated['name'],
                'phone_number' => $validated['phone_number'] ?? $latestBooking->customer_phone,
                'email' => $email,
                'password' => \Illuminate\Support\Facades\Hash::make($validated['phone_number'] ?? '12345678'),
                'is_admin' => false,
                'is_doctor' => false,
            ]);

            // Fill the rest of the profile
            $user->update($validated);

            // Link all matching guest bookings to this new user
            \App\Models\Booking::whereNull('user_id')
                ->where('customer_name', $latestBooking->customer_name)
                ->where('customer_phone', $latestBooking->customer_phone)
                ->update(['user_id' => $user->id]);

            return redirect()->route('admin.patients.show', $user->id)
                ->with('success', 'Guest patient promoted to registered user and updated successfully.');

        } else {
            // REGULAR USER UPDATE
            $user = User::findOrFail($id);
            $user->update($validated);
            return redirect()->back()->with('success', 'Patient information updated successfully.');
        }
    }

    public function showGuest(\App\Models\Booking $booking)
    {
        // Find other bookings by this guest (matching name and phone)
        $relatedBookings = \App\Models\Booking::whereNull('user_id')
            ->where('customer_name', $booking->customer_name)
            ->where('customer_phone', $booking->customer_phone)
            ->latest()
            ->with(['doctor', 'treatmentRecord'])
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
            'total_visits' => \App\Models\Visit::whereIn('booking_id', $guestBookingIds)->count(),
            'last_visit' => \App\Models\Visit::whereIn('booking_id', $guestBookingIds)->latest('visit_date')->first()?->visit_date?->format('Y-m-d'),
            'next_appointment' => $relatedBookings->whereIn('status', ['pending', 'confirmed'])->where('appointment_date', '>=', now()->toDateString())->sortBy('appointment_date')->first(),
        ];

        // Get latest treatment record for guest
        // CHANGED: Source from Visits instead of Bookings
        $latestRecord = \App\Models\TreatmentRecord::whereIn('booking_id', $guestBookingIds) // Guests usually stick to bookings for now, but better logic might be needed if they have walk-in visits without user_id?
            ->latest() // existing logic for guests assumes booking-based for now as they are "Guests" in booking system mostly.
            ->first();

        // NOTE: For guests, linking visits might be tricky if they don't have a user_id. 
        // Visits usually demand a patient_id (User). 
        // So for "Guests" coming from Bookings table, we probably still rely on Booking IDs.
        // But let's add the extra fields anyway.

        $medicalSummary = $latestRecord ? [
            'weight' => $latestRecord->weight,
            'height' => $latestRecord->height,
            'blood_pressure' => $latestRecord->blood_pressure,

            // New Vitals
            'temperature' => $latestRecord->temperature,
            'pulse_rate' => $latestRecord->pulse_rate,
            'respiratory_rate' => $latestRecord->respiratory_rate,

            'drug_allergy' => $latestRecord->drug_allergy,
            'underlying_disease' => $latestRecord->underlying_disease,
            'chief_complaint' => $latestRecord->chief_complaint,
            'diagnosis' => $latestRecord->diagnosis,

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
