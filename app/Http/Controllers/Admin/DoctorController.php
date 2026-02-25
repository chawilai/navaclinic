<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Inertia\Inertia;

class DoctorController extends Controller
{
    public function index()
    {
        $doctors = Doctor::with('user')->latest()->get();

        return Inertia::render('Admin/Doctors/Index', [
            'doctors' => $doctors
        ]);
    }

    public function show(Doctor $doctor)
    {
        $bookings = $doctor->bookings()
            ->with('user') // Eager load user for bookings
            ->latest()
            ->get();

        return Inertia::render('Admin/Doctors/Show', [
            'doctor' => $doctor,
            'bookings' => $bookings
        ]);
    }
    public function store(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specialty' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'is_on_leave' => 'boolean',
            'leave_reason' => 'nullable|string|max:255',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($validated) {
            $user = \App\Models\User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
                'is_doctor' => true,
            ]);

            Doctor::create([
                'name' => $validated['name'],
                'specialty' => $validated['specialty'],
                'user_id' => $user->id,
                'plain_password' => $validated['password'],
                'is_on_leave' => $validated['is_on_leave'] ?? false,
                'leave_reason' => $validated['leave_reason'] ?? null,
            ]);
        });

        return redirect()->back();
    }

    public function update(\Illuminate\Http\Request $request, Doctor $doctor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specialty' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $doctor->user_id,
            'password' => 'nullable|string|min:8',
            'is_on_leave' => 'boolean',
            'leave_reason' => 'nullable|string|max:255',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $doctor) {
            $doctor->update([
                'name' => $validated['name'],
                'specialty' => $validated['specialty'],
                'plain_password' => !empty($validated['password']) ? $validated['password'] : $doctor->plain_password,
                'is_on_leave' => $validated['is_on_leave'] ?? false,
                'leave_reason' => $validated['leave_reason'] ?? null,
            ]);

            $user = $doctor->user;
            $user->name = $validated['name'];
            $user->email = $validated['email'];

            if (!empty($validated['password'])) {
                $user->password = \Illuminate\Support\Facades\Hash::make($validated['password']);
            }

            $user->save();
        });

        return redirect()->back();
    }

    public function destroy(Doctor $doctor)
    {
        $user = $doctor->user;

        if ($user) {
            $user->delete();
        } else {
            $doctor->delete();
        }

        return redirect()->back();
    }
}
