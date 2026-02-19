<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use App\Models\Booking;
use App\Models\User;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class VisitController extends Controller
{
    public function create(Request $request)
    {
        $patientId = $request->input('user_id');

        if (str_starts_with($patientId, 'guest_')) {
            $bookingId = str_replace('guest_', '', $patientId);
            $booking = Booking::findOrFail($bookingId);

            // Create temporary patient object (Using Array to preserve ID string, as Model casts 'id' to int)
            $patient = [
                'id' => $patientId, // Keep guest ID string
                'name' => $booking->customer_name,
                'phone_number' => $booking->customer_phone,
            ];

            // Fetch pending bookings for this guest
            $bookings = Booking::whereNull('user_id')
                ->where('customer_name', $booking->customer_name)
                ->where('customer_phone', $booking->customer_phone)
                ->whereIn('status', ['pending', 'confirmed'])
                ->orderBy('appointment_date')
                ->get();

        } else {
            $patient = User::findOrFail($patientId);

            // Fetch pending bookings for this patient
            $bookings = Booking::where('user_id', $patientId)
                ->whereIn('status', ['pending', 'confirmed'])
                ->with('doctor')
                ->orderBy('appointment_date')
                ->get();
        }

        $doctors = Doctor::all();

        return Inertia::render('Admin/Visits/Create', [
            'patient' => $patient,
            'bookings' => $bookings,
            'doctors' => $doctors,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required', // Allow string for guest_
            'type' => 'required|in:booking,walk_in',
            'booking_id' => 'required_if:type,booking|nullable|exists:bookings,id',
            'doctor_id' => 'required_if:type,walk_in|nullable|exists:doctors,id',
            'visit_date' => 'required_if:type,walk_in|nullable|date',
            'symptoms' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $finalPatientId = DB::transaction(function () use ($validated, $request) {
            $patientId = $validated['patient_id'];

            // Handle Guest Registration if needed
            if (str_starts_with($patientId, 'guest_')) {
                $bookingId = str_replace('guest_', '', $patientId);
                $guestBooking = Booking::findOrFail($bookingId);

                // Generate HN
                $datePart = now()->format('dmY');
                $countToday = User::whereDate('created_at', now()->toDateString())->count() + 1;
                $hnId = 'HN-' . $datePart . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);

                // Create User
                $email = $guestBooking->customer_phone
                    ? 'guest_' . $guestBooking->customer_phone . '@navaclinic.com'
                    : 'guest_' . uniqid() . '@navaclinic.com';

                $newUser = User::create([
                    'patient_id' => $hnId,
                    'name' => $guestBooking->customer_name,
                    'phone_number' => $guestBooking->customer_phone,
                    'email' => $email,
                    'password' => \Illuminate\Support\Facades\Hash::make($guestBooking->customer_phone ?? '12345678'),
                    'is_admin' => false,
                    'is_doctor' => false,
                ]);

                // Link guest bookings
                Booking::whereNull('user_id')
                    ->where('customer_name', $guestBooking->customer_name)
                    ->where('customer_phone', $guestBooking->customer_phone)
                    ->update(['user_id' => $newUser->id]);

                $patientId = $newUser->id;
                // Update validated array for use below if needed, though we use $patientId variable
            }

            $visit = null;
            $doctorId = null;

            if ($validated['type'] === 'booking') {
                $booking = Booking::find($validated['booking_id']);

                // Ensure booking is linked to user if it wasn't already (e.g. if we just migrated)
                if (!$booking->user_id) {
                    $booking->update(['user_id' => $patientId]);
                }

                $bookingStart = \Carbon\Carbon::parse($booking->appointment_date . ' ' . $booking->start_time, 'Asia/Bangkok')->setTimezone('UTC');
                $doctorId = $booking->doctor_id;

                // Create Visit linked to Booking
                $visit = Visit::create([
                    'visit_date' => $bookingStart, // Scheduled time
                    'time_in' => now(), // Actual Check-in time
                    'patient_id' => $patientId,
                    'doctor_id' => $doctorId,
                    'booking_id' => $booking->id,
                    'symptoms' => $booking->symptoms, // Original booking symptoms
                    'notes' => $validated['notes'] ?? null,
                    'status' => 'ongoing',
                ]);

                // Update Booking Status to completed
                $booking->update(['status' => 'completed']);
            } else {
                $doctorId = $validated['doctor_id'];
                $duration = $request->integer('duration_minutes', 90); // Default to 90 if not set

                // --- Double Booking Check ---
                $start = \Carbon\Carbon::parse($validated['visit_date']); // This comes from frontend already shifted to UTC? Or we treat it as the time to save.
                // The frontend shifts it by -7 hours. 
                // So if user chose 9:00 AM (BKK), frontend sends 02:00 AM.
                // If we parse 02:00 AM, that is the UTC time. Perfect.

                $end = $start->copy()->addMinutes($duration);

                // Check Bookings (Convert UTC start/end to BKK time to compare with Booking Date/Time)
                // Actually, Booking Date/Time are "Local" (Bangkok) effectively, stored as Date and Time string.
                // so we should convert our $start (UTC) to Bangkok to compare.
                $startBkk = $start->copy()->setTimezone('Asia/Bangkok');
                $endBkk = $end->copy()->setTimezone('Asia/Bangkok');

                $conflictingBooking = Booking::where('doctor_id', $doctorId)
                    ->whereDate('appointment_date', $startBkk->format('Y-m-d'))
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->get()
                    ->first(function ($booking) use ($startBkk, $endBkk) {
                        $bStart = \Carbon\Carbon::parse($booking->appointment_date . ' ' . $booking->start_time, 'Asia/Bangkok');
                        $bEnd = $bStart->copy()->addMinutes($booking->duration_minutes + 30); // Buffer
                        return $startBkk->lt($bEnd) && $endBkk->gt($bStart);
                    });

                if ($conflictingBooking) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'doctor_id' => 'Doctor has a booking at this time (' . $conflictingBooking->start_time . ').'
                    ]);
                }

                // Check Visits
                $conflictingVisit = Visit::where('doctor_id', $doctorId)
                    // ->whereDate('visit_date', $start->format('Y-m-d')) // DB is in UTC usually? 
                    // Let's just use range check on the datetime column directly if possible, or fetch and filter.
                    // Safest is fetch potential overlaps.
                    ->whereIn('status', ['ongoing', 'pending'])
                    ->get()
                    ->first(function ($v) use ($start, $end) {
                        if ($v->booking_id)
                            return false;

                        $vStart = \Carbon\Carbon::parse($v->visit_date); // Assumed parsed as UTC if DB driver handles it, or App timezone
                        $vEnd = $vStart->copy()->addMinutes(($v->duration_minutes ?? 30) + 30); // Buffer
                        return $start->lt($vEnd) && $end->gt($vStart);
                    });

                if ($conflictingVisit) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'doctor_id' => 'Doctor is busy with another walk-in patient.'
                    ]);
                }
                // -----------------------------

                // Using array syntax for create to ensure clarity
                $visit = Visit::create([
                    'visit_date' => $validated['visit_date'], // Scheduled/Selected time
                    'time_in' => now(), // Actual Check-in time
                    'patient_id' => $patientId,
                    'doctor_id' => $doctorId,
                    'booking_id' => null, // Explicitly null for walk-in
                    'symptoms' => $validated['symptoms'], // Chief Complaint alias
                    'notes' => $validated['notes'] ?? null,
                    'status' => 'ongoing',
                    'duration_minutes' => $duration,
                ]);
            }

            // Always create Treatment Record for every visit so appropriate sections appear in details
            // Sync symptoms/chief_complaint from the visit (which got it from booking or form)
            $visit->treatmentRecord()->create([
                'booking_id' => $visit->booking_id,
                'weight' => $request->input('weight'),
                'height' => $request->input('height'),
                'blood_pressure' => $request->input('blood_pressure'),
                'temperature' => $request->input('temperature'),
                'pulse_rate' => $request->input('pulse_rate'),
                'respiratory_rate' => $request->input('respiratory_rate'),
                'chief_complaint' => $visit->symptoms,
                'physical_exam' => $request->input('physical_exam'),
                'treatment_details' => $request->input('treatment_details'),
                'diagnosis' => $request->input('diagnosis'),
                'pain_areas' => $request->input('pain_areas', []),
                'status' => $request->input('treatment_details') ? 'completed' : 'pending',
            ]);

            return $patientId;
        });

        return redirect()->route('admin.patients.show', $finalPatientId)->with('success', 'Visit started successfully.');
    }

    public function checkAvailability(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'start_time' => 'required|date_format:H:i',
            'duration_minutes' => 'required|integer|in:30,60,90',
            'date' => 'required|date',
        ]);

        $doctor = Doctor::find($request->doctor_id);
        $start = \Carbon\Carbon::parse($request->date . ' ' . $request->start_time);
        $end = $start->copy()->addMinutes($request->duration_minutes);

        // Check Bookings
        $conflictingBooking = Booking::where('doctor_id', $doctor->id)
            ->where('appointment_date', $request->date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get()
            ->first(function ($booking) use ($start, $end) {
                $bStart = \Carbon\Carbon::parse($booking->appointment_date . ' ' . $booking->start_time);
                $bEnd = $bStart->copy()->addMinutes($booking->duration_minutes);
                return $start < $bEnd && $end > $bStart;
            });

        if ($conflictingBooking) {
            return response()->json([
                'available' => false,
                'reason' => 'Doctor has a booking at this time.'
            ]);
        }

        // Check Visits
        $conflictingVisit = Visit::where('doctor_id', $doctor->id)
            ->whereDate('visit_date', $request->date)
            ->whereIn('status', ['ongoing', 'pending'])
            ->get()
            ->first(function ($visit) use ($start, $end) {
                if ($visit->booking_id)
                    return false; // Already checked in booking
                $vStart = \Carbon\Carbon::parse($visit->visit_date);
                $vEnd = $vStart->copy()->addMinutes($visit->duration_minutes ?? 30);
                return $start < $vEnd && $end > $vStart;
            });

        if ($conflictingVisit) {
            return response()->json([
                'available' => false,
                'reason' => 'Doctor is currently in a visit.'
            ]);
        }

        return response()->json(['available' => true]);
    }
    public function show(Visit $visit)
    {
        $visit->load(['patient', 'doctor', 'booking', 'treatmentRecord', 'payments']);
        $visit->patient->load('patientPackages.servicePackage'); // Load packages

        return Inertia::render('Admin/Visits/Show', [
            'visit' => $visit,
        ]);
    }
}
