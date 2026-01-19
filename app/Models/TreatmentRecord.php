<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreatmentRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'patient_name',
        'id_card_number',
        'date_of_birth',
        'age',
        'gender',
        'race',
        'nationality',
        'religion',
        'occupation',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'underlying_disease',
        'surgery_history',
        'drug_allergy',
        'accident_history',
        'weight',
        'height',
        'blood_pressure',
        'temperature',
        'pulse_rate',
        'respiratory_rate',
        'chief_complaint',
        'physical_exam',
        'massage_weight',
        'pain_level_before',
        'pain_level_after',
        'diagnosis',
        'treatment_details',
        'notes',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
