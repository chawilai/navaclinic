<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClinicSchedule;

class ClinicScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 0=Sunday, 1=Monday, ..., 6=Saturday
        $schedules = [
            ['day_of_week' => 1, 'is_open' => true, 'open_time' => '09:00', 'close_time' => '20:00'], // Mon
            ['day_of_week' => 2, 'is_open' => true, 'open_time' => '09:00', 'close_time' => '20:00'], // Tue
            ['day_of_week' => 3, 'is_open' => true, 'open_time' => '09:00', 'close_time' => '20:00'], // Wed
            ['day_of_week' => 4, 'is_open' => true, 'open_time' => '09:00', 'close_time' => '20:00'], // Thu
            ['day_of_week' => 5, 'is_open' => true, 'open_time' => '09:00', 'close_time' => '20:00'], // Fri
            ['day_of_week' => 6, 'is_open' => true, 'open_time' => '09:00', 'close_time' => '20:00'], // Sat
            ['day_of_week' => 0, 'is_open' => true, 'open_time' => '09:00', 'close_time' => '20:00'], // Sun (Default open)
        ];

        foreach ($schedules as $schedule) {
            ClinicSchedule::updateOrCreate(
                ['day_of_week' => $schedule['day_of_week']],
                $schedule
            );
        }
    }
}
