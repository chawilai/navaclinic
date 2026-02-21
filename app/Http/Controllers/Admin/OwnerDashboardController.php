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
        $query = Visit::with(['doctor', 'patient', 'booking', 'treatmentRecord'])
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

        // --- Financial Breakdown Chart (Revenue, Fee, Tip) ---
        // --- Chart Specific Filtering ---
        $chartPeriod = $request->input('chart_period', $period);
        $chartDateRaw = $request->input('chart_date', $request->input('date'));
        $chartDate = $chartDateRaw ? Carbon::parse($chartDateRaw) : Carbon::now();
        $chartDoctorId = $request->input('chart_doctor_id');

        // Determine chart date range
        if ($chartPeriod === 'daily') {
            $chartStartDate = $chartDate->copy()->startOfDay();
            $chartEndDate = $chartDate->copy()->endOfDay();
        } elseif ($chartPeriod === 'weekly') {
            $chartStartDate = $chartDate->copy()->startOfWeek();
            $chartEndDate = $chartDate->copy()->endOfWeek();
        } elseif ($chartPeriod === 'yearly') {
            $chartStartDate = $chartDate->copy()->startOfYear();
            $chartEndDate = $chartDate->copy()->endOfYear();
        } else { // monthly
            $chartStartDate = $chartDate->copy()->startOfMonth();
            $chartEndDate = $chartDate->copy()->endOfMonth();
        }

        $chartQuery = Visit::with(['doctor', 'treatmentRecord'])
            ->whereNotNull('doctor_id')
            ->whereBetween('visit_date', [$chartStartDate, $chartEndDate]);

        if ($chartDoctorId) {
            $chartQuery->where('doctor_id', $chartDoctorId);
        }

        $chartVisits = $chartQuery->orderBy('visit_date', 'asc')->get();

        // --- Financial Breakdown Chart (Revenue, Fee, Tip) ---
        // Using $chartVisits instead of global $visits
        $financialGrouped = $chartVisits->groupBy(function ($date) use ($chartPeriod) {
            if ($chartPeriod === 'daily') {
                return $date->visit_date->format('H:00');
            } elseif ($chartPeriod === 'yearly') {
                return $date->visit_date->format('Y-m');
            }
            return $date->visit_date->format('Y-m-d');
        })->map(function ($dayVisits) {
            $revenue = $dayVisits->sum('treatment_fee'); // Gross
            $tip = $dayVisits->sum('tip');
            $fee = $dayVisits->sum(function ($visit) {
                if (!$visit->is_complete)
                    return 0;
                return $visit->doctor_commission ?? ($visit->price * ($visit->doctor->commission_rate ?? 50) / 100);
            });
            return [
                'revenue' => $revenue,
                'fee' => $fee,
                'tip' => $tip
            ];
        });

        $finRevenueValues = [];
        $finFeeValues = [];
        $finTipValues = [];
        $finChartLabels = []; // Re-calculate labels based on CHART period

        if ($chartPeriod === 'daily') {
            for ($i = 9; $i <= 20; $i++) {
                $time = sprintf('%02d:00', $i);
                $finChartLabels[] = $time;
                $d = $financialGrouped->get($time, ['revenue' => 0, 'fee' => 0, 'tip' => 0]);
                $finRevenueValues[] = $d['revenue'];
                $finFeeValues[] = $d['fee'];
                $finTipValues[] = $d['tip'];
            }
        } elseif ($chartPeriod === 'yearly') {
            $current = $chartStartDate->copy();
            while ($current <= $chartEndDate) {
                $key = $current->format('Y-m');
                $finChartLabels[] = $current->format('M');
                $d = $financialGrouped->get($key, ['revenue' => 0, 'fee' => 0, 'tip' => 0]);
                $finRevenueValues[] = $d['revenue'];
                $finFeeValues[] = $d['fee'];
                $finTipValues[] = $d['tip'];
                $current->addMonth();
            }
        } else {
            $current = $chartStartDate->copy();
            $chartLabelFormat = ($chartPeriod === 'weekly') ? 'D d M' : 'd M';
            $chartKeyFormat = 'Y-m-d';

            while ($current <= $chartEndDate) {
                $key = $current->format($chartKeyFormat);
                $finChartLabels[] = $current->format($chartLabelFormat);
                $d = $financialGrouped->get($key, ['revenue' => 0, 'fee' => 0, 'tip' => 0]);
                $finRevenueValues[] = $d['revenue'];
                $finFeeValues[] = $d['fee'];
                $finTipValues[] = $d['tip'];
                $current->addDay();
            }
        }

        // --- Chart Totals (for summary boxes above chart) ---
        $chartTotalRevenue = $chartVisits->sum('treatment_fee');
        $chartTotalTip = $chartVisits->sum('tip');
        $chartTotalFee = $chartVisits->sum(function ($visit) {
            if (!$visit->is_complete)
                return 0;
            return $visit->doctor_commission ?? ($visit->price * ($visit->doctor->commission_rate ?? 50) / 100);
        });

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

        // --- Doctor Performance Stats Filtering ---
        $doctorPeriod = $request->input('doctor_period', $period);
        $doctorDateRaw = $request->input('doctor_date', $request->input('date'));
        $doctorDate = $doctorDateRaw ? Carbon::parse($doctorDateRaw) : Carbon::now();

        // Determine doctor stats date range
        if ($doctorPeriod === 'daily') {
            $doctorStartDate = $doctorDate->copy()->startOfDay();
            $doctorEndDate = $doctorDate->copy()->endOfDay();
        } elseif ($doctorPeriod === 'weekly') {
            $doctorStartDate = $doctorDate->copy()->startOfWeek();
            $doctorEndDate = $doctorDate->copy()->endOfWeek();
        } elseif ($doctorPeriod === 'yearly') {
            $doctorStartDate = $doctorDate->copy()->startOfYear();
            $doctorEndDate = $doctorDate->copy()->endOfYear();
        } else { // monthly
            $doctorStartDate = $doctorDate->copy()->startOfMonth();
            $doctorEndDate = $doctorDate->copy()->endOfMonth();
        }

        // Query visits specific to doctor stats
        $doctorVisitsQuery = Visit::with(['doctor', 'patient', 'treatmentRecord'])
            ->whereNotNull('doctor_id')
            ->whereBetween('visit_date', [$doctorStartDate, $doctorEndDate])
            ->orderBy('visit_date', 'desc');

        $doctorVisits = $doctorVisitsQuery->get();

        // --- Doctor Stats ---
        // Use $doctorVisits instead of global $visits
        $doctorStats = $doctorVisits->groupBy('doctor_id')->map(function ($uniqueDoctorVisits) {
            $doctor = $uniqueDoctorVisits->first()->doctor;

            $gross = $uniqueDoctorVisits->sum('treatment_fee');
            $discount = $uniqueDoctorVisits->reduce(function ($carry, $visit) {
                $fee = $visit->treatment_fee ?? $visit->price;
                $val = $visit->discount_value ?? 0;
                $type = $visit->discount_type ?? 'amount';
                return $carry + ($type === 'percent' ? ($fee * ($val / 100)) : $val);
            }, 0);
            $net = $uniqueDoctorVisits->sum('price');
            $doctorFee = $uniqueDoctorVisits->sum(function ($visit) {
                if (!$visit->is_complete)
                    return 0;
                return $visit->doctor_commission ?? ($visit->price * ($visit->doctor->commission_rate ?? 50) / 100);
            });

            return [
                'doctor_id' => $doctor->id,
                'doctor_name' => $doctor->name,
                'total_revenue' => $gross,
                'total_discount' => $discount,
                'net_revenue' => $net,
                'total_duration' => $uniqueDoctorVisits->sum('duration_minutes'),
                'total_doctor_fee' => $doctorFee,
                'total_tip' => $uniqueDoctorVisits->sum('tip'),
                'visits' => $uniqueDoctorVisits->map(function ($visit) {
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
                        'is_complete' => $visit->is_complete,
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
        $yearlyVisits = Visit::with(['doctor', 'treatmentRecord'])
            ->whereYear('visit_date', $currentDate->year)
            ->whereNotNull('doctor_id')
            ->get();

        $doctors = Doctor::orderBy('name')->get();

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
                if (!$visit->is_complete)
                    return $carry;
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
            'financial_chart' => [
                'labels' => $finChartLabels,
                'revenue' => $finRevenueValues,
                'fee' => $finFeeValues,
                'tip' => $finTipValues,
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
            'doctors' => $doctors,
            'chart_totals' => [
                'revenue' => $chartTotalRevenue,
                'fee' => $chartTotalFee,
                'tip' => $chartTotalTip,
            ],
            'filters' => [
                'period' => $period,
                'date' => $date->format('Y-m-d'),
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d'),
            ],
            'chart_filters' => [
                'period' => $chartPeriod,
                'date' => $chartDate->format('Y-m-d'),
                'doctor_id' => $chartDoctorId,
            ],
            'doctor_filters' => [
                'period' => $doctorPeriod,
                'date' => $doctorDate->format('Y-m-d'),
            ]
        ]);
    }
}
