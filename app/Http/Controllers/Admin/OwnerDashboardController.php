<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use App\Models\Doctor;
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
        $totalRevenue = $visits->sum('price');
        $totalDuration = $visits->sum('duration_minutes');
        $totalVisits = $visits->count();
        $totalPatients = $visits->unique('patient_id')->count();
        $avgTicketSize = $totalVisits > 0 ? $totalRevenue / $totalVisits : 0;

        // --- Revenue Chart Data ---
        // Group by date/time part
        $revenueData = $visits->groupBy(function ($date) use ($period) {
            if ($period === 'daily') {
                return $date->visit_date->format('H:00');
            } elseif ($period === 'yearly') {
                return $date->visit_date->format('Y-m');
            }
            return $date->visit_date->format('Y-m-d');
        })->map(function ($dayVisits) {
            return $dayVisits->sum('price');
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
                    'total_spent' => $patientVisits->sum('price'),
                    'visits_count' => $patientVisits->count(),
                ];
            })
            ->sortByDesc('total_spent')
            ->take(5)
            ->values();

        // --- Doctor Stats ---
        $doctorStats = $visits->groupBy('doctor_id')->map(function ($doctorVisits) {
            $doctor = $doctorVisits->first()->doctor;
            return [
                'doctor_id' => $doctor->id,
                'doctor_name' => $doctor->name,
                'total_revenue' => $doctorVisits->sum('price'),
                'total_duration' => $doctorVisits->sum('duration_minutes'),
                'visits' => $doctorVisits->map(function ($visit) {
                    return [
                        'patient_name' => $visit->patient ? $visit->patient->name : 'Unknown',
                        'visit_date' => $visit->visit_date->format('d M Y'),
                        'visit_time' => $visit->visit_date->format('H:i'),
                        'duration_minutes' => $visit->duration_minutes,
                        'price' => $visit->price,
                    ];
                })
            ];
        })->values();

        return Inertia::render('Admin/Owner/Dashboard', [
            'stats' => [
                'total_revenue' => $totalRevenue,
                'total_duration' => $totalDuration,
                'total_patients' => $totalPatients,
                'avg_ticket_size' => $avgTicketSize,
                'total_visits' => $totalVisits,
            ],
            'chart_data' => [
                'labels' => $chartLabels,
                'data' => $chartValues,
            ],
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
