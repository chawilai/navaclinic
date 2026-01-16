<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;

use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('is_admin', false);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('patient_id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        $patients = $query->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Patients/Index', [
            'patients' => $patients,
            'filters' => $request->only(['search']),
        ]);
    }

    public function show(User $user)
    {
        // Ensure we are viewing a patient, not an admin (optional, depending on requirements)
        if ($user->is_admin) {
            // Redirect or show error if needed, but for now we might want to see admins too?
            // Let's stick to patients for consistency with the controller name, but viewing admins is also fine.
        }

        $user->load(['bookings.doctor']); // Load booking history

        return Inertia::render('Admin/Patients/Show', [
            'patient' => $user,
            'bookings' => $user->bookings()->latest()->get()
        ]);
    }
}
