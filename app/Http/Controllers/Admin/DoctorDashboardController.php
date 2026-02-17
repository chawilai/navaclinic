<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Doctor;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DoctorDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Helper query for bookings
        $bookingQuery = Booking::query();
        // Helper query for visits
        $visitQuery = Visit::query();

        if ($user->is_doctor) {
            if ($user->doctor) {
                $bookingQuery->where('doctor_id', $user->doctor->id);
                $visitQuery->where('doctor_id', $user->doctor->id);
            } else {
                // If is_doctor but no doctor record, show nothing
                $bookingQuery->whereRaw('1 = 0');
                $visitQuery->whereRaw('1 = 0');
            }
        }

        // 1. Basic Stats
        // 1. Basic Stats
        $stats = [
            'total_patients' => $user->is_doctor && $user->doctor
                ? Visit::where('doctor_id', $user->doctor->id)->distinct('patient_id')->count('patient_id')
                : User::where('is_admin', false)->count(),
            // 'total_doctors' => Doctor::count(), // Removed as not used in doctor dashboard
            'total_bookings' => (clone $bookingQuery)->count(),
            'today_bookings' => (clone $bookingQuery)->whereDate('appointment_date', Carbon::today())->count(),
            'pending_bookings' => (clone $bookingQuery)->where('status', 'pending')->count(),
        ];

        // 1.1 Treatment Effectiveness (Avg Pain Reduction)
        // Check if we should filter this by doctor too? The user asked for "bookings and visits".
        // Pain reduction is treatment record data. Treatment record belongs to visit. Visit belongs to doctor.
        // So yes, if doctor, filter by doctor.
        $painQuery = \App\Models\TreatmentRecord::selectRaw('AVG(pain_level_before - pain_level_after) as avg_reduction')
            ->whereNotNull('pain_level_before')
            ->whereNotNull('pain_level_after');

        if ($user->is_doctor) {
            if ($user->doctor) {
                $docId = $user->doctor->id;
                $painQuery->whereHas('visit', function ($q) use ($docId) {
                    $q->where('doctor_id', $docId);
                });
            } else {
                $painQuery->whereRaw('1 = 0');
            }
        }

        $painStats = $painQuery->first();

        $stats['avg_pain_reduction'] = $painStats ? round($painStats->avg_reduction, 1) : 0;

        // 2. Chart Data Logic (Main Bar Chart - Bookings)
        $bookingsYear = $request->input('bookings_year', $request->input('year', Carbon::now()->year));
        $bookingsMonth = $request->input('bookings_month', $request->input('month', null));

        $chartData = [
            'labels' => [],
            'data' => [],
            'title' => ''
        ];

        // Prepare base query for chart
        $chartBookingsQuery = (clone $bookingQuery);

        if ($bookingsMonth) {
            // Monthly View
            $startDate = Carbon::createFromDate($bookingsYear, $bookingsMonth, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $thaiMonthsFull = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
            $chartData['title'] = "สถิติการจองประจำเดือน " . $thaiMonthsFull[$bookingsMonth - 1] . " " . ($bookingsYear + 543);

            $bookings = $chartBookingsQuery->whereBetween('appointment_date', [$startDate, $endDate])
                ->get()
                ->groupBy(function ($date) {
                    return Carbon::parse($date->appointment_date)->format('Y-m-d');
                });

            $daysInMonth = $startDate->daysInMonth;
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::createFromDate($bookingsYear, $bookingsMonth, $day);
                $dateString = $date->toDateString();

                $chartData['labels'][] = $day;
                $chartData['data'][] = isset($bookings[$dateString]) ? $bookings[$dateString]->count() : 0;
            }

        } else {
            // Yearly View
            $chartData['title'] = "สถิติการจองประจำปี " . ($bookingsYear + 543);

            $bookings = $chartBookingsQuery->whereYear('appointment_date', $bookingsYear)
                ->get()
                ->groupBy(function ($date) {
                    return Carbon::parse($date->appointment_date)->format('n'); // 1-12
                });

            $thaiMonthsShort = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];

            for ($m = 1; $m <= 12; $m++) {
                $chartData['labels'][] = $thaiMonthsShort[$m - 1];
                $chartData['data'][] = isset($bookings[$m]) ? $bookings[$m]->count() : 0;
            }
        }

        // Visit Chart Data Logic
        $visitsYear = $request->input('visits_year', Carbon::now()->year);
        $visitsMonth = $request->input('visits_month', null);

        if ($request->has('year') && !$request->has('visits_year')) {
            $visitsYear = $request->input('year');
        }
        if ($request->has('month') && !$request->has('visits_month')) {
            $visitsMonth = $request->input('month');
        }

        $visitsChartData = [
            'labels' => [],
            'data' => [],
            'title' => ''
        ];

        $chartVisitsQuery = (clone $visitQuery);

        if ($visitsMonth) {
            // Monthly View
            $startDate = Carbon::createFromDate($visitsYear, $visitsMonth, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $thaiMonthsFull = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
            $visitsChartData['title'] = "สถิติการเข้าพบแพทย์ประจำเดือน " . $thaiMonthsFull[$visitsMonth - 1] . " " . ($visitsYear + 543);

            $visits = $chartVisitsQuery->whereBetween('visit_date', [$startDate, $endDate])
                ->get()
                ->groupBy(function ($date) {
                    return Carbon::parse($date->visit_date)->format('Y-m-d');
                });

            $daysInMonth = $startDate->daysInMonth;

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::createFromDate($visitsYear, $visitsMonth, $day);
                $dateString = $date->toDateString();

                $visitsChartData['labels'][] = $day;
                $visitsChartData['data'][] = isset($visits[$dateString]) ? $visits[$dateString]->count() : 0;
            }

        } else {
            // Yearly View
            $visitsChartData['title'] = "สถิติการเข้าพบแพทย์ประจำปี " . ($visitsYear + 543);

            $visits = $chartVisitsQuery->whereYear('visit_date', $visitsYear)
                ->get()
                ->groupBy(function ($date) {
                    return Carbon::parse($date->visit_date)->format('n');
                });

            $thaiMonthsShort = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];

            for ($m = 1; $m <= 12; $m++) {
                $visitsChartData['labels'][] = $thaiMonthsShort[$m - 1];
                $visitsChartData['data'][] = isset($visits[$m]) ? $visits[$m]->count() : 0;
            }
        }

        // 3. Status Distribution (Pie Chart)
        $statusCounts = (clone $bookingQuery)->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $pieChartData = [
            'labels' => array_keys($statusCounts),
            'data' => array_values($statusCounts),
        ];

        // 4. Top Doctors
        // If user is doctor, only show themselves? Or show where they rank?
        // User said "dashboard for doctor... show booking and visit for cases where doctor is that doctor".
        // So Top Doctors might not be relevant or should just show themselves.
        // Let's keep it as is, or maybe hide it in frontend if doctor. 
        // For now, I will NOT filter top doctors because it's a leaderboard, unless privacy is a concern. 
        // Given the request, it's about "my cases".
        // Let's leave it global for now, assuming doctors can see who is top performing.
        $topDoctors = Doctor::withCount('bookings')
            ->orderByDesc('bookings_count')
            ->limit(5)
            ->get();

        // 5. Upcoming Allocations (Next bookings for today)
        $upcomingBookings = (clone $bookingQuery)->with(['user', 'doctor'])
            ->whereDate('appointment_date', Carbon::today())
            ->whereTime('start_time', '>', Carbon::now()->format('H:i'))
            ->orderBy('start_time')
            ->limit(5)
            ->get();

        // 6. All Bookings (Filtered)
        $bookingsFilterType = $request->input('bookings_filter_type', 'all');
        $bookingsFilterDate = $request->input('bookings_filter_date', Carbon::today()->toDateString());

        $bookingTableQuery = (clone $bookingQuery)->with(['user', 'doctor']);

        try {
            $filterDate = Carbon::parse($bookingsFilterDate);

            if ($bookingsFilterType === 'day') {
                $bookingTableQuery->whereDate('appointment_date', $filterDate->toDateString());
            } elseif ($bookingsFilterType === 'week') {
                $startOfWeek = $filterDate->copy()->startOfWeek();
                $endOfWeek = $filterDate->copy()->endOfWeek();
                $bookingTableQuery->whereBetween('appointment_date', [$startOfWeek, $endOfWeek]);
            } elseif ($bookingsFilterType === 'month') {
                $bookingTableQuery->whereMonth('appointment_date', $filterDate->month)
                    ->whereYear('appointment_date', $filterDate->year);
            } elseif ($bookingsFilterType === 'year') {
                $bookingTableQuery->whereYear('appointment_date', $filterDate->year);
            }
        } catch (\Exception $e) {
            // Invalid date format
        }

        $latestBookings = $bookingTableQuery
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // 7. All Visits (Filtered)
        $visitsFilterType = $request->input('visits_filter_type', 'all');
        $visitsFilterDate = $request->input('visits_filter_date', Carbon::today()->toDateString());

        $visitTableQuery = (clone $visitQuery)->with(['patient', 'doctor', 'treatmentRecord']);

        try {
            $filterDate = Carbon::parse($visitsFilterDate);

            if ($visitsFilterType === 'day') {
                $visitTableQuery->whereDate('visit_date', $filterDate->toDateString());
            } elseif ($visitsFilterType === 'week') {
                $startOfWeek = $filterDate->copy()->startOfWeek();
                $endOfWeek = $filterDate->copy()->endOfWeek();
                $visitTableQuery->whereBetween('visit_date', [$startOfWeek, $endOfWeek]);
            } elseif ($visitsFilterType === 'month') {
                $visitTableQuery->whereMonth('visit_date', $filterDate->month)
                    ->whereYear('visit_date', $filterDate->year);
            } elseif ($visitsFilterType === 'year') {
                $visitTableQuery->whereYear('visit_date', $filterDate->year);
            }
        } catch (\Exception $e) {
            // Invalid date format
        }

        $latestVisits = $visitTableQuery
            ->latest()
            ->paginate(5, ['*'], 'visits_page')
            ->withQueryString();

        return Inertia::render('Admin/DoctorDashboard', [
            'bookings' => $latestBookings,
            'latestVisits' => $latestVisits,
            'upcomingBookings' => $upcomingBookings,
            'newBookings' => (clone $bookingQuery)->with(['user', 'doctor'])
                ->where('admin_acknowledged', false)
                ->orderByDesc('created_at')
                ->get(),
            'stats' => $stats,
            'chartData' => $chartData,
            'visitsChartData' => $visitsChartData,
            'pieChartData' => $pieChartData,
            'topDoctors' => $topDoctors,
            'filters' => [
                'bookings_year' => (int) $bookingsYear,
                'bookings_month' => $bookingsMonth ? (int) $bookingsMonth : null,
                'visits_year' => (int) $visitsYear,
                'visits_month' => $visitsMonth ? (int) $visitsMonth : null,
                // Table Filters
                'bookings_filter_type' => $bookingsFilterType,
                'bookings_filter_date' => $bookingsFilterDate,
                'visits_filter_type' => $visitsFilterType,
                'visits_filter_date' => $visitsFilterDate,
            ]
        ]);
    }
}
