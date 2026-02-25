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
        'payment_proof',
        'admin_acknowledged',
        'payment_method',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'is_admin_booked' => 'boolean',
        'admin_acknowledged' => 'boolean',
        'price' => 'decimal:2',
    ];

    protected $appends = ['payment_proof_url'];

    public function getPaymentProofUrlAttribute()
    {
        return $this->payment_proof ? asset('storage/' . $this->payment_proof) : null;
    }

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
