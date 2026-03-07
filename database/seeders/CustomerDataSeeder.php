<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;

class CustomerDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = File::get(database_path('data.json'));
        $data = json_decode($json, true);

        $defaultPassword = Hash::make('password123');
        $now = now();

        foreach ($data as $item) {
            $nameParts = [];
            if (!empty($item['ชื่อ'])) {
                $nameParts[] = trim($item['ชื่อ']);
            }
            if (!empty($item['สกุล'])) {
                $nameParts[] = trim($item['สกุล']);
            }

            $name = implode(' ', $nameParts);
            if (empty($name)) {
                $name = 'Unknown';
            }

            $hn = isset($item['HN']) ? trim($item['HN']) : null;
            $email = isset($item['email']) ? trim($item['email']) : null;

            if (!$hn) {
                continue;
            }

            // Generate fallback email if missing
            if (!$email) {
                $email = strtolower($hn) . '@navaclinic.com';
            }

            // Check if user already exists
            $user = User::where('email', $email)->orWhere('patient_id', $hn)->first();

            if (!$user) {
                User::create([
                    'name' => $name,
                    'patient_id' => $hn,
                    'email' => $email,
                    // Give a default password
                    'password' => $defaultPassword,
                    'is_client' => true,
                    'email_verified_at' => $now,
                ]);
            } else {
                // Update the existing user if found
                $user->update([
                    'name' => $name,
                    'patient_id' => $hn,
                    'is_client' => true,
                ]);
            }
        }
    }
}
