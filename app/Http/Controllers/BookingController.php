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
            'doctor_id' => 'nullable|exists:doctors,id',
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
            // Check if there is an existing user with this name and phone
            $existingUser = \App\Models\User::where('name', $validated['customer_name'])
                ->where('phone_number', $validated['customer_phone'])
                ->where('is_admin', false)
                ->first();

            if ($existingUser) {
                $bookingData['user_id'] = $existingUser->id;
            } else {
                $bookingData['user_id'] = null;
            }

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

        // 1. Fetch Bookings
        $bookings = Booking::where('appointment_date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get();

        // 2. Fetch Visits (Walk-ins) on this date
        // Note: Visit has 'visit_date' which is a DateTime. We need to filter by DATE part.
        $visits = \App\Models\Visit::whereDate('visit_date', $date)
            ->whereIn('status', ['pending', 'ongoing']) // Assuming pending/ongoing blocks the doctor. Completed might strictly mean "Done", but for today's schedule, it WAS blocked? 
            // Actually, if it's completed, it's in the past relative to execution? Or it blocked that slot.
            // Let's include 'completed' as well if it was today, because you can't double book a past slot anyway, or we want to show it as "Busy" in history.
            // Usually 'cancelled' is the only one that frees up the slot.
            ->where('status', '!=', 'cancelled')
            ->get();

        \Illuminate\Support\Facades\Log::info("Bookings found: " . $bookings->count() . ", Visits found: " . $visits->count());

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
            $checkEnd = $slotStart->copy()->addMinutes($duration + 30);

            // Process all doctors for this slot
            $doctorsWithStatus = $doctors->map(function ($doctor) use ($bookings, $visits, $slotStart, $checkEnd) {

                // --- Check Bookings ---
                $doctorBookings = $bookings->where('doctor_id', $doctor->id);

                // --- Check Visits ---
                $doctorVisits = $visits->where('doctor_id', $doctor->id);

                // Merge Busy Slots for display
                $busySlots = [];

                foreach ($doctorBookings as $b) {
                    $bStart = \Carbon\Carbon::parse($b->appointment_date . ' ' . $b->start_time)->format('H:i');
                    $bEnd = \Carbon\Carbon::parse($b->appointment_date . ' ' . $b->start_time)->addMinutes($b->duration_minutes)->format('H:i');
                    $busySlots[] = "$bStart-$bEnd";
                }
                foreach ($doctorVisits as $v) {
                    // Check if this visit is already covered by a booking (linked)
                    // If linked, we skip to avoid double counting, although overlap logic handles it fine.
                    if ($v->booking_id)
                        continue;

                    $vStart = \Carbon\Carbon::parse($v->visit_date);
                    $duration = $v->duration_minutes ?? 30; // Default if null
                    $vEnd = $vStart->copy()->addMinutes($duration);
                    $busySlots[] = $vStart->format('H:i') . '-' . $vEnd->format('H:i');
                }

                // Check Overlaps

                // 1. Bookings Overlap
                $conflictingBooking = $doctorBookings->first(function ($booking) use ($slotStart, $checkEnd) {
                    $bookingStart = \Carbon\Carbon::parse($booking->appointment_date . ' ' . $booking->start_time);
                    $bookingEnd = $bookingStart->copy()->addMinutes($booking->duration_minutes + 30);
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

                // 2. Visits Overlap (Walk-ins without Booking)
                $conflictingVisit = $doctorVisits->first(function ($visit) use ($slotStart, $checkEnd) {
                    if ($visit->booking_id)
                        return false; // Handled by booking

                    $visitStart = \Carbon\Carbon::parse($visit->visit_date);
                    $duration = $visit->duration_minutes ?? 30;
                    $visitEnd = $visitStart->copy()->addMinutes($duration + 30);

                    return $slotStart < $visitEnd && $checkEnd > $visitStart;
                });

                if ($conflictingVisit) {
                    $vStart = \Carbon\Carbon::parse($conflictingVisit->visit_date);
                    $duration = $conflictingVisit->duration_minutes ?? 30;
                    $vEnd = $vStart->copy()->addMinutes($duration);

                    return [
                        'id' => $doctor->id,
                        'name' => $doctor->name,
                        'specialty' => $doctor->specialty,
                        'status' => 'busy',
                        'reason' => "Walk-in {$vStart->format('H:i')}-{$vEnd->format('H:i')}",
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
            // The user wants to see "Time Slot" -> Then see doctors. 
            // If we filter out slots where EVERYONE is busy, the user can't see "Busy" status.
            // But if we show every 30 min slot, it might be too many.
            // Standard practice: Show slots that have AT LEAST ONE available doctor?
            // The user request "Please add info saying which doctor is not free". 
            // This implies they want to see the busy doctors WHEN they select a time.
            // If a time slot has 0 available doctors, it shouldn't be selectable (or maybe shown but disabled).
            // THIS IS KEY. 
            // However, usually you don't care about a time if NO ONE is free.
            // I'll stick to: Show slot if at least 1 is free. And in that slot, show busy doctors too.
            // WAIT, the user said: "If that day, some time doctor is not free, show red box and cannot select".
            // This implies the slot SHOUDL BE SHOWN but RED if the doctor is busy.
            // So we should return the slot if it is within opening hours.

            // We want to return ALL slots within opening hours, regardless of availability.
            // The frontend will decide how to render based on the selected doctor or overall availability.

            $slots[] = [
                'time' => $timeStr,
                'doctors' => $doctorsWithStatus->values()
            ];

            // Move to next 30 min slot
            $current->addMinutes(30);
        }

        \Illuminate\Support\Facades\Log::info("Slots generated: " . count($slots));

        return response()->json($slots);
    }
}
