<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DoctorScheduleController extends Controller
{
    public function index($doctorId)
    {
        $doctor = \App\Models\Doctor::findOrFail($doctorId);
        $this->authorizeAccess($doctor);

        $schedules = $doctor->schedules()->get()->keyBy('day_of_week');

        // Create default structure if empty (0-6)
        $defaultSchedules = [];
        for ($i = 0; $i < 7; $i++) {
            if ($schedules->has($i)) {
                $defaultSchedules[] = $schedules[$i];
            } else {
                $defaultSchedules[] = [
                    'day_of_week' => $i,
                    'is_working' => true,
                    'start_time' => null,
                    'end_time' => null,
                ];
            }
        }

        return response()->json($defaultSchedules);
    }

    public function update(Request $request, $doctorId)
    {
        $doctor = \App\Models\Doctor::findOrFail($doctorId);
        $this->authorizeAccess($doctor);

        $validated = $request->validate([
            'schedules' => 'required|array|size:7',
            'schedules.*.day_of_week' => 'required|integer|min:0|max:6',
            'schedules.*.is_working' => 'required|boolean',
            'schedules.*.start_time' => 'nullable|date_format:H:i:s,H:i|required_if:schedules.*.is_working,true',
            'schedules.*.end_time' => 'nullable|date_format:H:i:s,H:i|required_if:schedules.*.is_working,true',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($doctor, $validated) {
            foreach ($validated['schedules'] as $slot) {
                // Formatting time correctly (ensure it has seconds if needed by DB, or let eloquent handle it)
                $startTime = $slot['start_time'];
                $endTime = $slot['end_time'];

                if ($startTime && strlen($startTime) === 5) {
                    $startTime .= ':00';
                }

                if ($endTime && strlen($endTime) === 5) {
                    $endTime .= ':00';
                }

                $doctor->schedules()->updateOrCreate(
                    ['day_of_week' => $slot['day_of_week']],
                    [
                        'is_working' => $slot['is_working'],
                        'start_time' => $slot['is_working'] ? $startTime : null,
                        'end_time' => $slot['is_working'] ? $endTime : null,
                    ]
                );
            }
        });

        return response()->json(['message' => 'Schedule updated successfully']);
    }

    private function authorizeAccess($doctor)
    {
        $user = auth()->user();
        if (!$user->is_admin && !$user->is_owner && ($user->id !== $doctor->user_id)) {
            abort(403, 'Unauthorized access');
        }
    }
}
