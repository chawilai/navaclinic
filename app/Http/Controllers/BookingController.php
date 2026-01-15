<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Doctor;
use App\Models\Booking;

class BookingController extends Controller
{
    public function create()
    {
        return Inertia::render('Booking/Create', [
            'doctors' => Doctor::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required', // Simple validation for now
            'duration_minutes' => 'required|in:30,60,90',
            'symptoms' => 'required|string',
        ]);

        $booking = Booking::create([
            'doctor_id' => $validated['doctor_id'],
            'user_id' => auth()->id(), // Nullable if guest
            'appointment_date' => $validated['appointment_date'],
            'start_time' => $validated['start_time'],
            'duration_minutes' => $validated['duration_minutes'],
            'symptoms' => $validated['symptoms'],
            'status' => 'pending',
        ]);

        return redirect()->route('dashboard')->with('success', 'Booking created successfully!');
    }

    public function checkAvailability(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2024',
        ]);

        $start = \Carbon\Carbon::createFromDate($request->year, $request->month, 1);
        $end = $start->copy()->endOfMonth();

        // Get all bookings for this doctor in this month
        $bookings = Booking::where('doctor_id', $request->doctor_id)
            ->whereBetween('appointment_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->get()
            ->groupBy('appointment_date');

        $availability = [];
        $current = $start->copy();

        while ($current <= $end) {
            $dateStr = $current->format('Y-m-d');
            $dayBookings = $bookings[$dateStr] ?? collect([]);

            // Simple logic: If more than 8 bookings, it's full. 
            // Real logic would be checking time slot overlaps.
            $isFull = $dayBookings->count() >= 8;

            $availability[$dateStr] = [
                'status' => $isFull ? 'full' : 'available',
                'count' => $dayBookings->count()
            ];

            $current->addDay();
        }

        return response()->json($availability);
    }

    public function getAvailableTimeSlots(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'duration' => 'required|integer|in:30,60,90',
        ]);

        $date = $request->date;
        $duration = (int) $request->duration;

        \Illuminate\Support\Facades\Log::info("Checking slots for Date: $date, Duration: $duration");

        $doctors = Doctor::all();
        \Illuminate\Support\Facades\Log::info("Doctors found: " . $doctors->count());

        $bookings = Booking::where('appointment_date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get();
        \Illuminate\Support\Facades\Log::info("Bookings found: " . $bookings->count());

        $startOfDay = \Carbon\Carbon::parse($date . ' 09:00:00');
        $endOfDay = \Carbon\Carbon::parse($date . ' 17:00:00');

        $slots = [];
        $current = $startOfDay->copy();

        $now = \Carbon\Carbon::now();
        $isToday = $now->format('Y-m-d') === $date;

        while ($current->copy()->addMinutes($duration) <= $endOfDay) {
            $slotStart = $current->copy();

            // Skip past times if today
            if ($isToday && $slotStart->lt($now)) {
                $current->addMinutes(30);
                continue;
            }

            $timeStr = $slotStart->format('H:i');

            // Calculate effective end time for the requested slot (Duration + 30m buffer)
            // We check if the [Start, Start + Duration + 30) range is free.
            $checkEnd = $slotStart->copy()->addMinutes($duration + 30);

            // Find available doctors for this specific slot
            $availableDoctors = $doctors->filter(function ($doctor) use ($bookings, $slotStart, $checkEnd) {
                // Check if doctor has any overlapping booking
                $hasConflict = $bookings->where('doctor_id', $doctor->id)->contains(function ($booking) use ($slotStart, $checkEnd) {
                    $bookingStart = \Carbon\Carbon::parse($booking->appointment_date . ' ' . $booking->start_time);

                    // Existing booking effectively blocks its Duration + 30m buffer as well.
                    // This ensures we respect the buffer of existing bookings.
                    $bookingEnd = $bookingStart->copy()->addMinutes($booking->duration_minutes + 30);

                    // Check overlap: (StartA < EndB) and (EndA > StartB)
                    // Request Range: [$slotStart, $checkEnd)
                    // Existing Range: [$bookingStart, $bookingEnd)
                    return $slotStart < $bookingEnd && $checkEnd > $bookingStart;
                });

                return !$hasConflict;
            });

            if ($availableDoctors->isNotEmpty()) {
                $slots[] = [
                    'time' => $timeStr,
                    'available_doctors' => $availableDoctors->values()
                ];
            }

            // Move to next 30 min slot
            $current->addMinutes(30);
        }

        \Illuminate\Support\Facades\Log::info("Slots generated: " . count($slots));

        return response()->json($slots);
    }
}
