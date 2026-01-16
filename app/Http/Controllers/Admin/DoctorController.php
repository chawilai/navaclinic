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
}
