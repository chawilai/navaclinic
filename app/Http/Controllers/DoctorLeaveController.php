<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDoctorLeaveRequest;
use App\Http\Requests\UpdateDoctorLeaveRequest;
use App\Models\DoctorLeave;

class DoctorLeaveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($doctorId)
    {
        $doctor = \App\Models\Doctor::findOrFail($doctorId);
        $this->authorizeAccess($doctor);

        return response()->json($doctor->leaves()->orderBy('date', 'desc')->orderBy('start_time', 'asc')->get());
    }

    public function store(\Illuminate\Http\Request $request, $doctorId)
    {
        $doctor = \App\Models\Doctor::findOrFail($doctorId);
        $this->authorizeAccess($doctor);

        $validated = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'reason' => 'nullable|string|max:255',
        ]);

        $leave = $doctor->leaves()->create($validated);

        return response()->json($leave, 201);
    }

    public function destroy($doctorId, $leaveId)
    {
        $doctor = \App\Models\Doctor::findOrFail($doctorId);
        $this->authorizeAccess($doctor);

        $leave = $doctor->leaves()->findOrFail($leaveId);
        $leave->delete();

        return response()->json(null, 204);
    }

    private function authorizeAccess($doctor)
    {
        $user = auth()->user();
        if (!$user->is_admin && !$user->is_owner && ($user->id !== $doctor->user_id)) {
            abort(403, 'Unauthorized access');
        }
    }
}
