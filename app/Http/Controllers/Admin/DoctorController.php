<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Inertia\Inertia;

class DoctorController extends Controller
{
    public function index()
    {
        $doctors = Doctor::latest()->get();

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
        ]);

        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        ]);

        Doctor::create([
            'name' => $validated['name'],
            'specialty' => $validated['specialty'],
            'user_id' => $user->id,
        ]);

        return redirect()->back();
    }

    public function update(\Illuminate\Http\Request $request, Doctor $doctor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specialty' => 'required|string|max:255',
        ]);

        $doctor->update($validated);

        return redirect()->back();
    }

    public function destroy(Doctor $doctor)
    {
        $doctor->delete();

        return redirect()->back();
    }
}
