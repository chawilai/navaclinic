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
        $rules = [
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'duration_minutes' => 'required|in:30,60,90',
            'symptoms' => 'required|string',
        ];

        if (!auth()->check()) {
            $rules['customer_name'] = 'required|string|max:255';
            $rules['customer_phone'] = 'required|string|max:20';
        }

        $validated = $request->validate($rules);

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

        // Prepare data
        $bookingData = [
            'doctor_id' => $validated['doctor_id'],
            'appointment_date' => $validated['appointment_date'],
            'start_time' => $validated['start_time'],
            'duration_minutes' => $validated['duration_minutes'],
            'symptoms' => $validated['symptoms'],
            'status' => 'pending',
        ];

        if (auth()->check()) {
            $bookingData['user_id'] = auth()->id();
        } else {
            $bookingData['user_id'] = null;
            $bookingData['customer_name'] = $validated['customer_name'];
            $bookingData['customer_phone'] = $validated['customer_phone'];
        }

        $booking = Booking::create($bookingData);

        if (auth()->check()) {
            return redirect()->route('dashboard')->with('success', 'Booking created successfully!');
        }

        return redirect()->route('welcome')->with('success', 'Booking created successfully! We will contact you shortly.');
    }

    public function checkAvailability(Request $request)
    {
        $request->validate([
            'doctor_id' => 'nullable|exists:doctors,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2024',
        ]);

        $start = \Carbon\Carbon::createFromDate($request->year, $request->month, 1);
        $end = $start->copy()->endOfMonth();

        // Get all bookings for this doctor in this month IF doctor is selected
        $bookings = collect([]);
        if ($request->doctor_id) {
            $bookings = Booking::where('doctor_id', $request->doctor_id)
                ->whereBetween('appointment_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                ->get()
                ->groupBy('appointment_date');
        }

        // Get special holidays for this month
        $holidays = \App\Models\ClinicHoliday::whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->get()
            ->keyBy(function ($item) {
                return $item->date->format('Y-m-d');
            });

        // Get weekly schedule
        $schedules = \App\Models\ClinicSchedule::all()->keyBy('day_of_week');

        $availability = [];
        $current = $start->copy();

        while ($current <= $end) {
            $dateStr = $current->format('Y-m-d');
            $dayBookings = $bookings[$dateStr] ?? collect([]);

            // Simple logic: If more than 8 bookings, it's full. 
            // Real logic would be checking time slot overlaps.
            $isFull = $request->doctor_id && $dayBookings->count() >= 8;

            // Check if it's a holiday
            if (isset($holidays[$dateStr])) {
                $availability[$dateStr] = [
                    'status' => 'closed',
                    'reason' => $holidays[$dateStr]->label
                ];
            }
            // Check if matches weekly closed day
            elseif (isset($schedules[$current->dayOfWeek]) && !$schedules[$current->dayOfWeek]->is_open) {
                $availability[$dateStr] = [
                    'status' => 'closed',
                    'reason' => 'วันหยุดร้าน' // Regular Shop Holiday
                ];
            } else {
                $availability[$dateStr] = [
                    'status' => $isFull ? 'full' : 'available',
                    'count' => $dayBookings->count()
                ];
            }

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

        // 1. Check for Special Holidays
        // 1. Check for Special Holidays
        $holiday = \App\Models\ClinicHoliday::whereDate('date', $date)->first();
        if ($holiday) {
            return response()->json(['message' => "Clinic is closed: {$holiday->label}"], 422);
        }

        // 2. Check Weekly Schedule
        $dayOfWeek = \Carbon\Carbon::parse($date)->dayOfWeek; // 0 (Sun) - 6 (Sat)
        $schedule = \App\Models\ClinicSchedule::where('day_of_week', $dayOfWeek)->first();

        if (!$schedule || !$schedule->is_open) {
            return response()->json(['message' => "Clinic is closed on this day."], 422);
        }

        \Illuminate\Support\Facades\Log::info("Checking slots for Date: $date, Duration: $duration");

        $doctors = Doctor::all();
        \Illuminate\Support\Facades\Log::info("Doctors found: " . $doctors->count());

        $bookings = Booking::where('appointment_date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get();
        \Illuminate\Support\Facades\Log::info("Bookings found: " . $bookings->count());

        // Use dynamic Open/Close times from Schedule
        $startOfDay = \Carbon\Carbon::parse($date . ' ' . $schedule->open_time);
        $endOfDay = \Carbon\Carbon::parse($date . ' ' . $schedule->close_time);

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

            // Process all doctors for this slot
            $doctorsWithStatus = $doctors->map(function ($doctor) use ($bookings, $slotStart, $checkEnd) {
                // Get all bookings for this doctor to show full schedule
                $doctorBookings = $bookings->where('doctor_id', $doctor->id);

                $busySlots = $doctorBookings->map(function ($booking) {
                    $bStart = \Carbon\Carbon::parse($booking->appointment_date . ' ' . $booking->start_time)->format('H:i');
                    $bEnd = \Carbon\Carbon::parse($booking->appointment_date . ' ' . $booking->start_time)->addMinutes($booking->duration_minutes)->format('H:i');
                    return "$bStart-$bEnd";
                })->values()->all();

                // Check if doctor has any overlapping booking for THIS slot
                $conflictingBooking = $doctorBookings->first(function ($booking) use ($slotStart, $checkEnd) {
                    $bookingStart = \Carbon\Carbon::parse($booking->appointment_date . ' ' . $booking->start_time);

                    // Existing booking effectively blocks its Duration + 30m buffer as well.
                    $bookingEnd = $bookingStart->copy()->addMinutes($booking->duration_minutes + 30);

                    // Check overlap: (StartA < EndB) and (EndA > StartB)
                    return $slotStart < $bookingEnd && $checkEnd > $bookingStart;
                });

                if ($conflictingBooking) {
                    $bStart = \Carbon\Carbon::parse($conflictingBooking->appointment_date . ' ' . $conflictingBooking->start_time)->format('H:i');
                    $bEnd = \Carbon\Carbon::parse($conflictingBooking->appointment_date . ' ' . $conflictingBooking->start_time)->addMinutes($conflictingBooking->duration_minutes)->format('H:i');

                    return [
                        'id' => $doctor->id,
                        'name' => $doctor->name,
                        'specialty' => $doctor->specialty,
                        'status' => 'busy',
                        'reason' => "ติดคิว $bStart-$bEnd",
                        'busy_slots' => $busySlots
                    ];
                }

                return [
                    'id' => $doctor->id,
                    'name' => $doctor->name,
                    'specialty' => $doctor->specialty,
                    'status' => 'available',
                    'reason' => null,
                    'busy_slots' => $busySlots
                ];
            });

            // We include the slot even if all doctors are busy? 
            // The user usually wants to see "Time Slot" -> Then see doctors. 
            // If we filter out slots where EVERYONE is busy, the user can't see "Busy" status.
            // But if we show every 30 min slot, it might be too many.
            // Standard practice: Show slots that have AT LEAST ONE available doctor?
            // The user request "Please add info saying which doctor is not free". 
            // This implies they want to see the busy doctors WHEN they select a time.
            // If a time slot has 0 available doctors, it shouldn't be selectable (or maybe shown but disabled).
            // THIS IS KEY. 
            // However, usually you don't care about a time if NO ONE is free.
            // I'll stick to: Show slot if at least 1 is free. And in that slot, show busy doctors too.

            $hasAvailableInfo = $doctorsWithStatus->where('status', 'available')->isNotEmpty();

            if ($hasAvailableInfo) {
                $slots[] = [
                    'time' => $timeStr,
                    'doctors' => $doctorsWithStatus->values() // Renamed to 'doctors' to imply all
                ];
            }

            // Move to next 30 min slot
            $current->addMinutes(30);
        }

        \Illuminate\Support\Facades\Log::info("Slots generated: " . count($slots));

        return response()->json($slots);
    }
}
