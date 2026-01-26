<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PatientPackageController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'service_package_id' => 'required|exists:service_packages,id',
        ]);

        $package = \App\Models\ServicePackage::findOrFail($validated['service_package_id']);

        // Check if package is active? (Optional validation)
        if (!$package->is_active) {
            return back()->withErrors(['service_package_id' => 'This package is no longer active.']);
        }

        \App\Models\PatientPackage::create([
            'user_id' => $validated['user_id'],
            'service_package_id' => $package->id,
            'remaining_sessions' => $package->total_sessions,
            'purchase_date' => now(),
            'expiry_date' => now()->addDays($package->validity_days),
            'status' => 'active',
            'notes' => 'Manual purchase via Admin', // Could add more details if needed
        ]);

        return back()->with('success', 'Package sold successfully!');
    }
}
