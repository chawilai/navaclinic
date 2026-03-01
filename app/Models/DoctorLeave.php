<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorLeave extends Model
{
    /** @use HasFactory<\Database\Factories\DoctorLeaveFactory> */
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'date',
        'start_time',
        'end_time',
        'reason',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
