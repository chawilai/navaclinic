<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\TreatmentRecord;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TreatmentController extends Controller
{
    public function create(Booking $booking)
    {
        return Inertia::render('Admin/Treatment/Create', [
            'booking' => $booking->load(['user', 'doctor']),
            'previousRecord' => $booking->treatmentRecord,
        ]);
    }

    public function store(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'id_card_number' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'age' => 'nullable|integer',
            'gender' => 'nullable|string',
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
            'weight' => 'nullable|numeric|between:0,500',
            'height' => 'nullable|numeric|between:0,300',
            'temperature' => 'nullable|numeric|between:30,45',
            'pulse_rate' => 'nullable|integer|between:0,300',
            'respiratory_rate' => 'nullable|integer|between:0,100',
            'blood_pressure' => 'nullable|string|max:20',
            'chief_complaint' => 'nullable|string',
            'physical_exam' => 'nullable|string',
            'massage_weight' => 'nullable|string|in:light,medium,heavy',
            'diagnosis' => 'required|string',
            'treatment_details' => 'required|string',
            'notes' => 'nullable|string',
            'next_appointment' => 'nullable|date',
        ]);

        $booking->treatmentRecord()->updateOrCreate(
            ['booking_id' => $booking->id],
            $validated
        );

        return redirect()->route('admin.bookings.show', $booking->id)
            ->with('success', 'Treatment details saved successfully.');
    }
}
