<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function store(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $booking->payments()->create($validated);

        return back()->with('success', 'Payment added successfully!');
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();
        return back()->with('success', 'Payment deleted successfully!');
    }
    public function storeForVisit(Request $request, \App\Models\Visit $visit)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'package_id' => 'required_if:payment_method,package|nullable|exists:patient_packages,id',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        if ($validated['payment_method'] === 'package') {
            // Logic for Package Payment
            $package = \App\Models\PatientPackage::findOrFail($validated['package_id']);

            if ($package->remaining_sessions <= 0) {
                return back()->withErrors(['amount' => 'This package has no remaining sessions.']);
            }

            // Deduct session
            $package->decrement('remaining_sessions');

            // Check if depleted
            if ($package->remaining_sessions <= 0) {
                $package->update(['status' => 'completed']);
            }

            // Create Payment Record (Amount = 0 or Virtual Amount? Usually 0 cash wise but we can track value if needed)
            // For now, let's record the Amount entered as 'Value' but method is package.
            // But usually amount entered is the 'Price of Treatment'.
            // If paying by package, the 'Amount Paid' is effectively covering the bill.

            $visit->payments()->create([
                'amount' => $validated['amount'], // We count this as "Paid Amount" to clear the debt, even if no cash flow.
                'payment_method' => 'package (' . $package->servicePackage->name . ')',
                'payment_date' => $validated['payment_date'],
                'notes' => $validated['notes'] . " (Package ID: {$package->id}, Remaining: {$package->remaining_sessions})",
            ]);

            return back()->with('success', 'Session deducted from package successfully!');

        } else {
            // Normal Payment
            $visit->payments()->create([
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'payment_date' => $validated['payment_date'],
                'notes' => $validated['notes'],
            ]);

            return back()->with('success', 'Payment added successfully!');
        }
    }
}
