<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use App\Models\Doctor;
use App\Models\Booking;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OwnerDashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', 'monthly'); // daily, weekly, monthly, yearly
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::now();

        // Base query
        $query = Visit::with(['doctor', 'patient', 'booking'])
            ->whereNotNull('doctor_id');

        // Determine date range
        if ($period === 'daily') {
            $startDate = $date->copy()->startOfDay();
            $endDate = $date->copy()->endOfDay();
            $dateFormat = '%H:00'; // SQLite/MySQL format for hour, adjusted in logic usually
            $labelFormat = 'H:i';
        } elseif ($period === 'weekly') {
            $startDate = $date->copy()->startOfWeek();
            $endDate = $date->copy()->endOfWeek();
            $dateFormat = 'Y-m-d';
            $labelFormat = 'D d M';
        } elseif ($period === 'yearly') {
            $startDate = $date->copy()->startOfYear();
            $endDate = $date->copy()->endOfYear();
            $dateFormat = 'Y-m';
            $labelFormat = 'M Y';
        } else { // monthly
            $startDate = $date->copy()->startOfMonth();
            $endDate = $date->copy()->endOfMonth();
            $dateFormat = 'Y-m-d';
            $labelFormat = 'd M';
        }

        $visits = $query->whereBetween('visit_date', [$startDate, $endDate])
            ->orderBy('visit_date', 'desc')
            ->get();

        // --- Aggregates ---
        $totalRevenue = $visits->sum('treatment_fee'); // Gross Revenue
        $totalDiscount = $visits->reduce(function ($carry, $visit) {
            $fee = $visit->treatment_fee ?? $visit->price;
            $val = $visit->discount_value ?? 0;
            $type = $visit->discount_type ?? 'amount';
            if ($type === 'percent') {
                return $carry + ($fee * ($val / 100));
            }
            return $carry + $val;
        }, 0);

        $totalDuration = $visits->sum('duration_minutes');
        $totalVisits = $visits->count();
        $totalPatients = $visits->unique('patient_id')->count();
        $avgTicketSize = $totalVisits > 0 ? $visits->sum('price') / $totalVisits : 0; // Avg Net Paid

        // --- Revenue Chart Data (Gross) -> User asked for Treatment Fee based on pre-discount
        // But maybe chart should remain Net (Cash flow)? Let's assume Gross for "Revenue" consistency if label is "Revenue".
        // However, "Revenue" usually means Net.
        // User request: "ค่ารักษา ... ไม่ใช่เลขหลังลด" (Treatment fee ... not the number after discount).
        // I will make the chart data reflect 'treatment_fee' (Gross).

        $revenueData = $visits->groupBy(function ($date) use ($period) {
            if ($period === 'daily') {
                return $date->visit_date->format('H:00');
            } elseif ($period === 'yearly') {
                return $date->visit_date->format('Y-m');
            }
            return $date->visit_date->format('Y-m-d');
        })->map(function ($dayVisits) {
            return $dayVisits->sum('treatment_fee');
        });

        // Fill in missing slots for the chart
        $chartLabels = [];
        $chartValues = [];

        if ($period === 'daily') {
            for ($i = 9; $i <= 20; $i++) { // 9 AM to 8 PM
                $time = sprintf('%02d:00', $i);
                $chartLabels[] = $time;
                $chartValues[] = $revenueData->get($time, 0);
            }
        } elseif ($period === 'yearly') {
            $current = $startDate->copy();
            while ($current <= $endDate) {
                $key = $current->format('Y-m');
                $chartLabels[] = $current->format('M');
                $chartValues[] = $revenueData->get($key, 0);
                $current->addMonth();
            }
        } else {
            $current = $startDate->copy();
            while ($current <= $endDate) {
                $key = $current->format('Y-m-d');
                $chartLabels[] = $current->format($labelFormat);
                $chartValues[] = $revenueData->get($key, 0);
                $current->addDay();
            }
        }

        // --- Top Patients ---
        $topPatients = $visits->groupBy('patient_id')
            ->map(function ($patientVisits) {
                $patient = $patientVisits->first()->patient;
                return [
                    'id' => $patient ? $patient->id : null,
                    'name' => $patient ? $patient->name : 'Unknown',
                    'total_spent' => $patientVisits->sum('price'), // Keep as Net Spent by patient? Yes.
                    'visits_count' => $patientVisits->count(),
                ];
            })
            ->sortByDesc('total_spent')
            ->take(5)
            ->values();

        // --- Doctor Stats ---
        $doctorStats = $visits->groupBy('doctor_id')->map(function ($doctorVisits) {
            $doctor = $doctorVisits->first()->doctor;

            $gross = $doctorVisits->sum('treatment_fee');
            $discount = $doctorVisits->reduce(function ($carry, $visit) {
                $fee = $visit->treatment_fee ?? $visit->price;
                $val = $visit->discount_value ?? 0;
                $type = $visit->discount_type ?? 'amount';
                return $carry + ($type === 'percent' ? ($fee * ($val / 100)) : $val);
            }, 0);
            $net = $doctorVisits->sum('price');
            $doctorFee = $doctorVisits->sum(function ($visit) {
                return $visit->doctor_commission ?? ($visit->price * ($visit->doctor->commission_rate ?? 50) / 100);
            });

            return [
                'doctor_id' => $doctor->id,
                'doctor_name' => $doctor->name,
                'total_revenue' => $gross, // Modified to Gross as requested for "Treatment Fee" context
                'total_discount' => $discount,
                'net_revenue' => $net,
                'total_duration' => $doctorVisits->sum('duration_minutes'),
                'total_doctor_fee' => $doctorFee,
                'total_tip' => $doctorVisits->sum('tip'),
                'visits' => $doctorVisits->map(function ($visit) {
                    // Calc discount for this visit
                    $fee = $visit->treatment_fee ?? $visit->price;
                    $val = $visit->discount_value ?? 0;
                    $type = $visit->discount_type ?? 'amount';
                    $discAmt = ($type === 'percent' ? ($fee * ($val / 100)) : $val);

                    return [
                        'patient_name' => $visit->patient ? $visit->patient->name : 'Unknown',
                        'visit_date' => $visit->visit_date->format('d M Y'),
                        'visit_time' => $visit->visit_date->format('H:i'),
                        'duration_minutes' => $visit->duration_minutes,
                        'price' => $visit->price, // Net
                        'treatment_fee' => $visit->treatment_fee ?? $visit->price, // Gross
                        'discount' => $discAmt,
                        'doctor_fee' => $visit->doctor_commission ?? ($visit->price * ($visit->doctor->commission_rate ?? 50) / 100),
                        'tip' => $visit->tip ?? 0,
                    ];
                })

            ];
        })->values();

        // --- New vs Returning Patients ---
        // Get unique patients in the current period
        $patientIdsInPeriod = $visits->pluck('patient_id')->unique()->filter();

        // Count how many of these patients had visits BEFORE the start date
        // If they had a visit before, they are "Returning". If not, they are "New".
        $returningPatientsCount = 0;
        if ($patientIdsInPeriod->isNotEmpty()) {
            $returningPatientsCount = Visit::whereIn('patient_id', $patientIdsInPeriod)
                ->where('visit_date', '<', $startDate)
                ->distinct('patient_id')
                ->count('patient_id');
        }

        $totalUniquePatients = $patientIdsInPeriod->count();
        $newPatientsCount = $totalUniquePatients - $returningPatientsCount;

        // --- Peak Hours ---
        // Group visits by hour (0-23)
        $visitsByHour = $visits->groupBy(function ($visit) {
            return $visit->visit_date->format('H');
        });

        $peakHoursData = [];
        // Initialize all likely hours (e.g., 8 to 20) with 0
        for ($h = 8; $h <= 20; $h++) {
            $hourStr = sprintf('%02d', $h);
            $count = isset($visitsByHour[$hourStr]) ? $visitsByHour[$hourStr]->count() : 0;
            $peakHoursData[] = [
                'hour' => $hourStr . ':00',
                'count' => $count
            ];
        }

        // --- Upcoming Bookings ---
        $upcomingBookings = Booking::with(['user', 'doctor'])
            ->where('appointment_date', '>=', Carbon::today())
            ->where('status', '!=', 'cancelled') // Assuming we want active bookings
            ->orderBy('appointment_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->limit(5)
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'patient_name' => $booking->user ? $booking->user->name : $booking->customer_name ?? 'Guest',
                    'doctor_name' => $booking->doctor ? $booking->doctor->name : 'Unassigned',
                    'date' => Carbon::parse($booking->appointment_date)->format('d M Y'),
                    'time' => Carbon::parse($booking->start_time)->format('H:i'),
                    'status' => ucfirst($booking->status),
                ];
            });

        // --- Financial Overview (Today, Month, Year) ---
        // Fetch all visits for the selected year to minimize queries
        $currentDate = $date->copy();
        $yearlyVisits = Visit::with('doctor')
            ->whereYear('visit_date', $currentDate->year)
            ->whereNotNull('doctor_id')
            ->get();

        $calculateMetrics = function ($visitsCollection) {
            $grossRevenue = $visitsCollection->sum('treatment_fee');

            $discount = $visitsCollection->reduce(function ($carry, $visit) {
                $fee = $visit->treatment_fee ?? $visit->price;
                $val = $visit->discount_value ?? 0;
                $type = $visit->discount_type ?? 'amount';
                return $carry + ($type === 'percent' ? ($fee * ($val / 100)) : $val);
            }, 0);

            $netRevenue = $visitsCollection->sum('price');

            $doctorFee = $visitsCollection->reduce(function ($carry, $visit) {
                if ($visit->doctor_commission !== null) {
                    return $carry + $visit->doctor_commission;
                }
                $rate = $visit->doctor ? $visit->doctor->commission_rate : 50; // Default 50%
                return $carry + ($visit->price * $rate / 100);
            }, 0);

            return [
                'revenue' => $grossRevenue, // Showing Gross as requested
                'discount' => $discount,
                'net_revenue' => $netRevenue,
                'doctor_fee' => $doctorFee,
                'net_profit' => $netRevenue - $doctorFee
            ];
        };

        $todayMetrics = $calculateMetrics($yearlyVisits->filter(function ($visit) use ($currentDate) {
            return $visit->visit_date->isSameDay($currentDate);
        }));

        $monthMetrics = $calculateMetrics($yearlyVisits->filter(function ($visit) use ($currentDate) {
            return $visit->visit_date->isSameMonth($currentDate);
        }));

        $yearMetrics = $calculateMetrics($yearlyVisits);

        return Inertia::render('Admin/Owner/Dashboard', [
            'stats' => [
                'total_revenue' => $totalRevenue,
                'total_discount' => $totalDiscount,
                'total_duration' => $totalDuration,
                'total_patients' => $totalPatients,
                'avg_ticket_size' => $avgTicketSize,
                'total_visits' => $totalVisits,
            ],
            'financial_overview' => [
                'today' => $todayMetrics,
                'month' => $monthMetrics,
                'year' => $yearMetrics,
            ],
            'chart_data' => [
                'labels' => $chartLabels,
                'data' => $chartValues,
            ],
            'chart_new_vs_returning' => [
                'labels' => ['New Patients', 'Returning Patients'],
                'data' => [$newPatientsCount, $returningPatientsCount],
            ],
            'chart_peak_hours' => [
                'labels' => array_column($peakHoursData, 'hour'),
                'data' => array_column($peakHoursData, 'count'),
            ],
            'upcoming_bookings' => $upcomingBookings,
            'top_patients' => $topPatients,
            'doctor_stats' => $doctorStats,
            'filters' => [
                'period' => $period,
                'date' => $date->format('Y-m-d'),
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d'),
            ]
        ]);
    }
}
