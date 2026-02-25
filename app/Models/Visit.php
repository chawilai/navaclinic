<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $fillable = [
        'visit_date',
        'time_in',
        'time_out',
        'patient_id',
        'doctor_id',
        'booking_id',
        'symptoms',
        'notes',
        'status',
        'price',
        'doctor_commission',
        'duration_minutes',
        'treatment_fee',
        'discount_type',
        'discount_value',
        'tip',
        'payment_method',
    ];

    protected $casts = [
        'visit_date' => 'datetime',
        'time_in' => 'datetime',
        'time_out' => 'datetime',
        'price' => 'decimal:2',
        'doctor_commission' => 'decimal:2',
        'treatment_fee' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'tip' => 'decimal:2',
    ];

    protected $appends = ['is_complete'];

    public function getIsCompleteAttribute()
    {
        $hasPhysicalExam = !empty($this->treatmentRecord?->physical_exam);
        $hasDiagnosis = !empty($this->treatmentRecord?->diagnosis);
        $hasTreatmentDetails = !empty($this->treatmentRecord?->treatment_details);

        return $hasPhysicalExam && $hasDiagnosis && $hasTreatmentDetails;
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function treatmentRecord()
    {
        return $this->hasOne(TreatmentRecord::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
