<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DoctorUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create User
        $user = User::firstOrCreate(
            ['email' => 'doc1@gmail.com'],
            [
                'name' => 'Doctor One',
                'password' => Hash::make('doc1'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]
        );

        // Create Doctor Profile Linked to User
        $doctor = Doctor::firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => 'Doctor One',
                'specialty' => 'General', // Default specialty
            ]
        );

        // Update user name if needed or ensure consistency
        $user->update(['name' => $doctor->name]);
    }
}
