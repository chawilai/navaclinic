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

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Basic Stats
        $stats = [
            'total_patients' => User::where('is_admin', false)->count() + Booking::whereNull('user_id')->whereNotNull('customer_phone')->distinct()->count('customer_phone'),
            'total_doctors' => Doctor::count(),
            'total_bookings' => Booking::count(),
            'today_bookings' => Booking::whereDate('appointment_date', Carbon::today())->count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
        ];

        // 1.1 Treatment Effectiveness (Avg Pain Reduction)
        $painStats = \App\Models\TreatmentRecord::selectRaw('AVG(pain_level_before - pain_level_after) as avg_reduction')
            ->whereNotNull('pain_level_before')
            ->whereNotNull('pain_level_after')
            ->first();

        $stats['avg_pain_reduction'] = $painStats ? round($painStats->avg_reduction, 1) : 0;

        // 2. Chart Data Logic (Main Bar Chart - Bookings)
        // Default to current year if no params provided, or 'year' param for backward compat
        $bookingsYear = $request->input('bookings_year', $request->input('year', Carbon::now()->year));
        $bookingsMonth = $request->input('bookings_month', $request->input('month', null));

        $chartData = [
            'labels' => [],
            'data' => [],
            'title' => ''
        ];

        if ($bookingsMonth) {
            // Monthly View: Show days of the specific month
            $startDate = Carbon::createFromDate($bookingsYear, $bookingsMonth, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $thaiMonthsFull = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
            $chartData['title'] = "สถิติการจองประจำเดือน " . $thaiMonthsFull[$bookingsMonth - 1] . " " . ($bookingsYear + 543);

            // Fetch all bookings for the range and group by day
            $bookings = Booking::whereBetween('appointment_date', [$startDate, $endDate])
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
            // Yearly View: Show all 12 months
            $chartData['title'] = "สถิติการจองประจำปี " . ($bookingsYear + 543);

            // Fetch all bookings for the year and group by month
            $bookings = Booking::whereYear('appointment_date', $bookingsYear)
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
        // Default to current year if no params provided, or 'year' param for backward compat
        // Crucially, if only one set of filters was provided previously (year=...), both charts used it.
        // We will default visitsYear to the bookingsYear ONLY if visitsYear is not explicitly set, 
        // to ensure they start in sync on fresh load but can diverge. 
        // Actually, simplest is default to current year.
        $visitsYear = $request->input('visits_year', Carbon::now()->year);
        $visitsMonth = $request->input('visits_month', null);

        // If it's a legacy request (only 'year' param provided), sync visits to it as well
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

        if ($visitsMonth) {
            // Monthly View
            $startDate = Carbon::createFromDate($visitsYear, $visitsMonth, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $thaiMonthsFull = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
            $visitsChartData['title'] = "สถิติการเข้าพบแพทย์ประจำเดือน " . $thaiMonthsFull[$visitsMonth - 1] . " " . ($visitsYear + 543);

            $visits = Visit::whereBetween('visit_date', [$startDate, $endDate])
                ->get()
                ->groupBy(function ($date) {
                    return Carbon::parse($date->visit_date)->format('Y-m-d');
                });

            $daysInMonth = $startDate->daysInMonth; // Re-calculate days for visits month

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::createFromDate($visitsYear, $visitsMonth, $day);
                $dateString = $date->toDateString();

                $visitsChartData['labels'][] = $day;
                $visitsChartData['data'][] = isset($visits[$dateString]) ? $visits[$dateString]->count() : 0;
            }

        } else {
            // Yearly View
            $visitsChartData['title'] = "สถิติการเข้าพบแพทย์ประจำปี " . ($visitsYear + 543);

            $visits = Visit::whereYear('visit_date', $visitsYear)
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
        $statusCounts = Booking::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $pieChartData = [
            'labels' => array_keys($statusCounts),
            'data' => array_values($statusCounts),
        ];

        // 4. Top Doctors
        $topDoctors = Doctor::withCount('bookings')
            ->orderByDesc('bookings_count')
            ->limit(5)
            ->get();

        // 5. Upcoming Allocations (Next bookings for today)
        $upcomingBookings = Booking::with(['user', 'doctor'])
            ->whereDate('appointment_date', Carbon::today())
            ->whereTime('start_time', '>', Carbon::now()->format('H:i'))
            ->orderBy('start_time')
            ->limit(5)
            ->get();

        // 6. Latest Bookings (General)
        $latestBookings = Booking::with(['user', 'doctor'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // 7. Latest Visits (Completed Checkups)
        $latestVisits = Visit::with(['patient', 'doctor', 'treatmentRecord'])
            ->latest()
            ->paginate(5, ['*'], 'visits_page')
            ->withQueryString();

        return Inertia::render('Admin/Dashboard', [
            'bookings' => $latestBookings,
            'latestVisits' => $latestVisits,
            'upcomingBookings' => $upcomingBookings,
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
            ]
        ]);
    }
}
