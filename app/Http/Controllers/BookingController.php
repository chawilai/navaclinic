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
}
