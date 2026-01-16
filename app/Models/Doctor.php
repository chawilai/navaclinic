<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $fillable = ['name', 'specialty'];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
