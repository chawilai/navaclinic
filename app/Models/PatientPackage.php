<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientPackage extends Model
{
    protected $fillable = [
        'user_id',
        'service_package_id',
        'remaining_sessions',
        'purchase_date',
        'expiry_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function servicePackage()
    {
        return $this->belongsTo(ServicePackage::class);
    }
}
