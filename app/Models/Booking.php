<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'user_id',
        'appointment_date',
        'start_time',
        'duration_minutes',
        'symptoms',
        'status',
        'customer_name',
        'customer_phone',
        'price',
        'is_admin_booked',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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
