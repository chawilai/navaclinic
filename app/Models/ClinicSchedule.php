<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicSchedule extends Model
{
    use HasFactory;

    protected $fillable = ['day_of_week', 'is_open', 'open_time', 'close_time'];

    protected $casts = [
        'is_open' => 'boolean',
    ];

    // Helper to get day name
    public function getDayNameAttribute()
    {
        return date('l', strtotime("Sunday +{$this->day_of_week} days"));
    }
}
