<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'patient_id',
        'name',
        'email',
        'password',
        'is_admin',
        'is_doctor',
        'phone_number',
        'id_card_number',
        'date_of_birth',
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
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_doctor' => 'boolean',
        ];
    }
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function visits()
    {
        return $this->hasMany(Visit::class, 'patient_id');
    }

    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }

    public function patientPackages()
    {
        return $this->hasMany(PatientPackage::class);
    }
}
