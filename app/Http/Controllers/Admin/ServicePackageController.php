<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServicePackageController extends Controller
{
    public function index()
    {
        $packages = \App\Models\ServicePackage::all();
        return Inertia::render('Admin/Packages/Index', [
            'packages' => $packages
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Packages/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'total_sessions' => 'required|integer|min:1',
            'validity_days' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        \App\Models\ServicePackage::create($validated);

        return redirect()->route('admin.packages.index')->with('success', 'Package created successfully!');
    }

    public function edit(\App\Models\ServicePackage $package)
    {
        return Inertia::render('Admin/Packages/Edit', [
            'package' => $package
        ]);
    }

    public function update(Request $request, \App\Models\ServicePackage $package)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'total_sessions' => 'required|integer|min:1',
            'validity_days' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $package->update($validated);

        return redirect()->route('admin.packages.index')->with('success', 'Package updated successfully!');
    }

    public function destroy(\App\Models\ServicePackage $package)
    {
        $package->delete();
        return redirect()->route('admin.packages.index')->with('success', 'Package deleted successfully!');
    }
}
