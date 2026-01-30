<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BookingController extends Controller
{
    public function create(Request $request)
    {
        $preselectedUser = null;
        if ($request->has('user_id')) {
            $preselectedUser = \App\Models\User::find($request->user_id);
        }

        // 1. Fetch Key Users (Admins excluded)
        $users = \App\Models\User::where('is_admin', false)
            ->select('id', 'name', 'phone_number', 'email')
            ->get()
            ->map(function ($u) {
                return [
                    'type' => 'user',
                    'id' => $u->id,
                    'name' => $u->name,
                    'phone' => $u->phone_number,
                    'email' => $u->email,
                    'display_text' => $u->name . ($u->phone_number ? " ({$u->phone_number})" : "")
                ];
            });

        // 2. Fetch Distinct Guests from Bookings (Where user_id is null)
        $guests = \App\Models\Booking::whereNull('user_id')
            ->whereNotNull('customer_name')
            ->distinct()
            ->get(['customer_name', 'customer_phone'])
            ->map(function ($g) {
                return [
                    'type' => 'guest',
                    'id' => 'guest_' . md5($g->customer_name . $g->customer_phone), // Temporary unique ID for frontend key
                    'name' => $g->customer_name,
                    'phone' => $g->customer_phone,
                    'email' => '-',
                    'display_text' => $g->customer_name . ($g->customer_phone ? " ({$g->customer_phone}) [Guest]" : " [Guest]")
                ];
            });

        // 3. Merge and Unique (by name+phone to avoid duplicates if guest became user? Or just show all)
        // For simplicity, just merge.
        $patients = $users->concat($guests)->sortBy('name')->values();

        return Inertia::render('Admin/Booking/Create', [
            'doctors' => \App\Models\Doctor::all(),
            'patients' => $patients, // Renamed from 'users'
            'preselectedUser' => $preselectedUser
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'duration_minutes' => 'required|in:30,60,90',
            'symptoms' => 'required|string',
            'user_type' => 'required|in:existing,guest',
            // If existing user
            'user_id' => 'required_if:user_type,existing|nullable|exists:users,id',
            // If guest
            'customer_name' => 'required_if:user_type,guest|nullable|string|max:255',
            'customer_phone' => 'required_if:user_type,guest|nullable|string|max:20',
        ]);

        // Validate Schedule & Holidays
        $date = $validated['appointment_date'];

        // Check Holiday
        $holiday = \App\Models\ClinicHoliday::where('date', $date)->first();
        if ($holiday) {
            return back()->withErrors(['appointment_date' => "Clinic is closed on this date: {$holiday->label}"]);
        }

        // Check Weekly Schedule
        $dayOfWeek = \Carbon\Carbon::parse($date)->dayOfWeek;
        $schedule = \App\Models\ClinicSchedule::where('day_of_week', $dayOfWeek)->first();
        if (!$schedule || !$schedule->is_open) {
            return back()->withErrors(['appointment_date' => "Clinic is closed on this day."]);
        }

        if ($validated['user_type'] === 'existing') {
            $userId = $validated['user_id'];

            // Standard Booking Flow for Existing Users
            // (Assuming 'existing' implies a standard booking, but if they want walk-in behavior for existing users too, 
            // they might need a toggle. For now, strict 'user_type === guest' is the trigger for "Visit Only")

            $bookingData = [
                'doctor_id' => $validated['doctor_id'],
                'appointment_date' => $validated['appointment_date'],
                'start_time' => $validated['start_time'],
                'duration_minutes' => $validated['duration_minutes'],
                'symptoms' => $validated['symptoms'],
                'user_id' => $userId,
                'status' => 'confirmed',
                'customer_name' => null,
                'customer_phone' => null,
                'is_admin_booked' => true,
            ];

            $booking = Booking::create($bookingData);

            // Auto-create visit for confirmed booking? Or wait?
            // Previous logic was auto-create. I'll keep it for consistency.
            \App\Models\Visit::create([
                'visit_date' => $booking->appointment_date . ' ' . $booking->start_time,
                'patient_id' => $userId,
                'doctor_id' => $booking->doctor_id,
                'booking_id' => $booking->id,
                'symptoms' => $booking->symptoms,
                'status' => 'pending',
                'price' => 0,
                'duration_minutes' => $validated['duration_minutes'],
            ]);

            return redirect()->route('admin.dashboard')->with('success', 'Booking created successfully!');

        } else {
            // "Walk-in" Guest Logic -> VISIT ONLY
            // 1. Try to find existing user by phone
            $existingUser = \App\Models\User::where('phone_number', $validated['customer_phone'])->first();

            if ($existingUser) {
                $userId = $existingUser->id;
            } else {
                // 2. Create new User
                $email = 'guest_' . preg_replace('/[^0-9]/', '', $validated['customer_phone']) . '@navaclinic.com';

                if (\App\Models\User::where('email', $email)->exists()) {
                    $email = 'guest_' . preg_replace('/[^0-9]/', '', $validated['customer_phone']) . '_' . uniqid() . '@navaclinic.com';
                }

                $datePart = now()->format('dmY');
                $countToday = \App\Models\User::whereDate('created_at', now()->toDateString())->count() + 1;
                $hnId = 'HN-' . $datePart . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);

                $newUser = \App\Models\User::create([
                    'name' => $validated['customer_name'],
                    'phone_number' => $validated['customer_phone'],
                    'patient_id' => $hnId,
                    'email' => $email,
                    'password' => \Illuminate\Support\Facades\Hash::make('password'),
                    'is_admin' => false,
                ]);
                $userId = $newUser->id;
            }

            // CREATE VISIT DIRECTLY (No Booking)
            $visitDate = $validated['appointment_date'] . ' ' . $validated['start_time'];

            \App\Models\Visit::create([
                'visit_date' => $visitDate,
                'patient_id' => $userId,
                'doctor_id' => $validated['doctor_id'],
                'booking_id' => null, // Explicitly null
                'symptoms' => $validated['symptoms'],
                'status' => 'pending',
                'price' => 0,
                'duration_minutes' => $validated['duration_minutes'],
            ]);

            return redirect()->route('admin.dashboard')->with('success', 'Walk-in Visit created successfully (No Booking generated).');
        }
    }
    public function edit(Booking $booking)
    {
        $booking->load(['user', 'doctor']);

        return Inertia::render('Admin/Booking/Edit', [
            'booking' => $booking,
            'doctors' => \App\Models\Doctor::all(),
        ]);
    }

    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date',
            'start_time' => 'required',
            'duration_minutes' => 'required|in:30,60,90',
            'symptoms' => 'required|string',
            'price' => 'nullable|numeric|min:0',
        ]);

        // Simple validation check (can be expanded like store)
        // Check Holiday & Schedule if date/time changed?
        // For now, let's assume Admin overrides logic or knows what they are doing for Edit.
        // But basic check is good practice.

        // Validation for Availability (Double Check)
        $doctor = \App\Models\Doctor::find($validated['doctor_id']);
        $date = $validated['appointment_date'];
        $startTime = $validated['start_time'];
        $duration = $validated['duration_minutes'];

        // Check for overlaps with OTHER bookings
        $start = \Carbon\Carbon::parse("$date $startTime");
        $end = $start->copy()->addMinutes($duration + 30); // 30 mins buffer

        $conflictingBooking = \App\Models\Booking::where('doctor_id', $doctor->id)
            ->where('id', '!=', $booking->id) // Exclude current booking
            ->where('appointment_date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get()
            ->first(function ($existing) use ($start, $end) {
                $eStart = \Carbon\Carbon::parse("{$existing->appointment_date} {$existing->start_time}");
                $eEnd = $eStart->copy()->addMinutes($existing->duration_minutes + 30);

                return $start < $eEnd && $end > $eStart;
            });

        if ($conflictingBooking) {
            return back()->withErrors([
                'start_time' => 'Doctor is not available at this time (Conflicting booking).'
            ]);
        }

        $booking->update($validated);



        return redirect()->route('admin.bookings.show', $booking->id)->with('success', 'Booking updated successfully!');
    }

    public function show(Booking $booking)
    {
        $booking->load(['user', 'doctor', 'treatmentRecord', 'payments']);
        return Inertia::render('Admin/Booking/Show', [
            'booking' => $booking
        ]);
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed',
        ]);

        $booking->update($validated);

        return back();
    }
}
