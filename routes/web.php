<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('ShopSelection');
})->name('shop.selection');

Route::get('/nava-clinic', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('welcome');

Route::get('/services', function () {
    return Inertia::render('Services', [
        'initialServices' => \App\Models\Service::where('is_active', true)->get()
    ]);
})->name('services');

Route::get('/siam-retreat', function () {
    return Inertia::render('SiamRetreat');
})->name('siam.retreat');

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->doctor) {
        // For doctors, we want to see upcoming and recent bookings
        $bookingsQuery = $user->doctor->bookings()->with('user');

        // Fetch Walk-in Visits (visits without booking_id)
        $visitsQuery = $user->doctor->visits()
            ->whereNull('booking_id')
            ->with(['patient', 'doctor']);

        // Calculate Today's Stats (Bookings + Walk-ins)
        $today = now()->format('Y-m-d');

        // Stats from Bookings
        $bookingStats = [
            'total' => $user->doctor->bookings()->whereDate('appointment_date', $today)->count(),
            'pending' => $user->doctor->bookings()->whereDate('appointment_date', $today)->whereIn('status', ['pending', 'confirmed'])->count(),
            'completed' => $user->doctor->bookings()->whereDate('appointment_date', $today)->where('status', 'completed')->count(),
            'revenue' => $user->doctor->bookings()->whereDate('appointment_date', $today)->where('status', '!=', 'cancelled')->sum('price'),
        ];

        // Stats from Walk-ins
        $visitStats = [
            'total' => $user->doctor->visits()->whereDate('visit_date', $today)->whereNull('booking_id')->count(),
            'pending' => $user->doctor->visits()->whereDate('visit_date', $today)->whereNull('booking_id')->whereIn('status', ['ongoing', 'pending'])->count(),
            'completed' => $user->doctor->visits()->whereDate('visit_date', $today)->whereNull('booking_id')->where('status', 'completed')->count(),
            'revenue' => $user->doctor->visits()->whereDate('visit_date', $today)->whereNull('booking_id')->where('status', '!=', 'cancelled')->sum('price'),
        ];

        $todayStats = [
            'total' => $bookingStats['total'] + $visitStats['total'],
            'pending' => $bookingStats['pending'] + $visitStats['pending'],
            'completed' => $bookingStats['completed'] + $visitStats['completed'],
            'revenue' => $bookingStats['revenue'] + $visitStats['revenue'],
        ];

        // Weekly Workload Chart Data (Last 7 Days)
        $dates = collect(range(6, 0))->map(function ($days) {
            return now()->subDays($days)->format('Y-m-d');
        });

        // Calculate Workload for each day
        $chartDataValues = $dates->map(function ($date) use ($user) {
            $bCount = $user->doctor->bookings()->whereDate('appointment_date', $date)->count();
            $vCount = $user->doctor->visits()->whereDate('visit_date', $date)->whereNull('booking_id')->count();
            return $bCount + $vCount;
        })->toArray();

        $chartData = [
            'labels' => $dates->map(fn($date) => \Carbon\Carbon::parse($date)->format('D d'))->toArray(),
            'data' => $chartDataValues,
        ];

        // Find Next Booking or Visit
        // Check Bookings
        $nextBookingItem = $user->doctor->bookings()
            ->with('user')
            ->where('status', 'confirmed')
            ->where(function ($q) use ($today) {
                $q->whereDate('appointment_date', '>', $today)
                    ->orWhere(function ($sub) use ($today) {
                        $sub->whereDate('appointment_date', $today)
                            ->where('start_time', '>=', now()->format('H:i:s'));
                    });
            })
            ->orderBy('appointment_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->first();

        // Check Visits (Ongoing is Priority)
        $currentVisit = $user->doctor->visits()
            ->whereNull('booking_id')
            ->whereIn('status', ['ongoing', 'pending'])
            ->with('patient')
            ->orderBy('visit_date', 'asc') // Oldest ongoing first
            ->first();

        $nextItem = null;
        if ($currentVisit) {
            $nextItem = [
                'id' => $currentVisit->id,
                'type' => 'visit',
                'user' => $currentVisit->patient,
                'customer_name' => $currentVisit->patient->name ?? 'Walk-in Guest',
                'customer_phone' => $currentVisit->patient->phone_number ?? null,
                'symptoms' => $currentVisit->symptoms,
                'start_time' => $currentVisit->visit_date->setTimezone('Asia/Bangkok')->format('H:i:s'),
                'appointment_date' => $currentVisit->visit_date->setTimezone('Asia/Bangkok')->format('Y-m-d'),
                'status' => 'confirmed', // Map ongoing to confirmed for color
            ];
        } elseif ($nextBookingItem) {
            $nextItem = $nextBookingItem;
            $nextItem['type'] = 'booking';
        }

        // Main List: Fetch and Merge
        $bookings = $bookingsQuery->orderBy('appointment_date', 'desc')->orderBy('start_time', 'asc')->get();
        $visits = $visitsQuery->orderBy('visit_date', 'desc')->get();

        $combinedList = collect();

        foreach ($bookings as $booking) {
            $booking->type = 'booking';
            $combinedList->push($booking);
        }

        foreach ($visits as $visit) {
            // Normalize Visit to Booking structure
            $combinedList->push([
                'id' => $visit->id,
                'type' => 'visit',
                'doctor_id' => $visit->doctor_id,
                'user_id' => $visit->patient_id,
                'appointment_date' => $visit->visit_date->setTimezone('Asia/Bangkok')->format('Y-m-d'),
                'start_time' => $visit->visit_date->setTimezone('Asia/Bangkok')->format('H:i:s'),
                'duration_minutes' => $visit->duration_minutes ?? 30,
                'symptoms' => $visit->symptoms,
                'status' => $visit->status === 'ongoing' ? 'confirmed' : $visit->status,
                'customer_name' => $visit->patient->name ?? 'Walk-in Guest',
                'customer_phone' => $visit->patient->phone_number ?? null,
                'price' => $visit->price,
                'user' => $visit->patient,
                'is_walk_in' => true,
            ]);
        }

        // Sort combined list by date desc, time desc
        $sortedList = $combinedList->sort(function ($a, $b) {
            $dateA = $a['appointment_date'] . ' ' . $a['start_time'];
            $dateB = $b['appointment_date'] . ' ' . $b['start_time'];
            return strcmp($dateB, $dateA); // Descending
        })->values();

        return Inertia::render('Dashboard', [
            'bookings' => $sortedList,
            'isDoctor' => true,
            'todayStats' => $todayStats,
            'nextBooking' => $nextItem,
            'workloadChart' => $chartData,
        ]);
    } else {
        // For patients
        $bookings = $user->bookings()
            ->with('doctor')
            ->orderBy('appointment_date', 'desc')
            ->orderBy('start_time', 'asc')
            ->get();

        return Inertia::render('Dashboard', [
            'bookings' => $bookings,
            'isDoctor' => false,
        ]);
    }
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->prefix('admin')->group(function () {
    // Shared Routes for Staff (Admins + Doctors)
    Route::middleware('staff')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/doctor-dashboard', [\App\Http\Controllers\Admin\DoctorDashboardController::class, 'index'])->name('admin.doctor.dashboard');

        // Booking Management
        Route::get('/bookings/create', [\App\Http\Controllers\Admin\BookingController::class, 'create'])->name('admin.bookings.create');
        Route::post('/bookings', [\App\Http\Controllers\Admin\BookingController::class, 'store'])->name('admin.bookings.store');
        Route::get('/bookings/{booking}/edit', [\App\Http\Controllers\Admin\BookingController::class, 'edit'])->name('admin.bookings.edit');
        Route::patch('/bookings/{booking}', [\App\Http\Controllers\Admin\BookingController::class, 'update'])->name('admin.bookings.update');
        Route::get('/bookings/{booking}', [\App\Http\Controllers\Admin\BookingController::class, 'show'])->name('admin.bookings.show');
        Route::patch('/bookings/{booking}/status', [\App\Http\Controllers\Admin\BookingController::class, 'updateStatus'])->name('admin.bookings.update-status');

        // Payments
        Route::post('/bookings/{booking}/payments', [\App\Http\Controllers\Admin\PaymentController::class, 'store'])->name('admin.payments.store');
        Route::delete('/payments/{payment}', [\App\Http\Controllers\Admin\PaymentController::class, 'destroy'])->name('admin.payments.destroy');

        // Treatment Records
        Route::get('/bookings/{booking}/treatment/create', [\App\Http\Controllers\Admin\TreatmentController::class, 'create'])->name('admin.treatment.create');
        Route::post('/bookings/{booking}/treatment', [\App\Http\Controllers\Admin\TreatmentController::class, 'store'])->name('admin.treatment.store');

        // Treatment Details Step (Step 2)
        Route::get('/treatment-records/{treatmentRecord}/details', [\App\Http\Controllers\Admin\TreatmentController::class, 'details'])->name('admin.treatment.details');
        Route::put('/treatment-records/{treatmentRecord}/details', [\App\Http\Controllers\Admin\TreatmentController::class, 'updateDetails'])->name('admin.treatment.update-details');

        // Patient Management
        Route::post('/patients', [\App\Http\Controllers\Admin\PatientController::class, 'store'])->name('admin.patients.store');
        Route::get('/patients', [\App\Http\Controllers\Admin\PatientController::class, 'index'])->name('admin.patients.index');
        Route::get('/patients/guest/{booking}', [\App\Http\Controllers\Admin\PatientController::class, 'showGuest'])->name('admin.patients.guest.show');
        Route::put('/patients/{user}', [\App\Http\Controllers\Admin\PatientController::class, 'update'])->name('admin.patients.update');
        Route::get('/patients/{user}', [\App\Http\Controllers\Admin\PatientController::class, 'show'])->name('admin.patients.show');

        // Assign Package to Patient
        Route::post('/patients/packages', [\App\Http\Controllers\Admin\PatientPackageController::class, 'store'])->name('admin.patient-packages.store');

        // Visit Management
        Route::get('/visits/check-availability', [\App\Http\Controllers\Admin\VisitController::class, 'checkAvailability'])->name('admin.visits.check-availability');
        Route::get('/visits/create', [\App\Http\Controllers\Admin\VisitController::class, 'create'])->name('admin.visits.create');
        Route::post('/visits', [\App\Http\Controllers\Admin\VisitController::class, 'store'])->name('admin.visits.store');
        Route::get('/visits/{visit}', [\App\Http\Controllers\Admin\VisitController::class, 'show'])->name('admin.visits.show');

        // Visit Documents (Printable)
        Route::get('/visits/{visit}/documents/receipt', [\App\Http\Controllers\Admin\DocumentController::class, 'receipt'])->name('admin.documents.receipt');
        Route::get('/visits/{visit}/documents/medical-certificate', [\App\Http\Controllers\Admin\DocumentController::class, 'medicalCertificate'])->name('admin.documents.medical-certificate');

        // Visit Treatment Records
        Route::get('/visits/{visit}/treatment/create', [\App\Http\Controllers\Admin\TreatmentController::class, 'createForVisit'])->name('admin.visits.treatment.create');
        Route::post('/visits/{visit}/treatment', [\App\Http\Controllers\Admin\TreatmentController::class, 'storeForVisit'])->name('admin.visits.treatment.store');

        // Visit Payments
        Route::post('/visits/{visit}/payments', [\App\Http\Controllers\Admin\PaymentController::class, 'storeForVisit'])->name('admin.visits.payments.store');
    });

    // Admin Only Routes
    Route::middleware('admin')->group(function () {

        // Owner Only Routes
        Route::middleware([\App\Http\Middleware\OwnerRequired::class])->group(function () {
            Route::get('/owner-dashboard', [\App\Http\Controllers\Admin\OwnerDashboardController::class, 'index'])->name('admin.owner.dashboard');

            // Doctor Management
            Route::get('/doctors', [\App\Http\Controllers\Admin\DoctorController::class, 'index'])->name('admin.doctors.index');
            Route::post('/doctors', [\App\Http\Controllers\Admin\DoctorController::class, 'store'])->name('admin.doctors.store');
            Route::get('/doctors/{doctor}', [\App\Http\Controllers\Admin\DoctorController::class, 'show'])->name('admin.doctors.show');
            Route::patch('/doctors/{doctor}', [\App\Http\Controllers\Admin\DoctorController::class, 'update'])->name('admin.doctors.update');
            Route::delete('/doctors/{doctor}', [\App\Http\Controllers\Admin\DoctorController::class, 'destroy'])->name('admin.doctors.destroy');

            // Admin Management
            Route::get('/admins', [\App\Http\Controllers\Admin\AdminUserController::class, 'index'])->name('admin.admins.index');
            Route::post('/admins', [\App\Http\Controllers\Admin\AdminUserController::class, 'store'])->name('admin.admins.store');
            Route::patch('/admins/{admin}', [\App\Http\Controllers\Admin\AdminUserController::class, 'update'])->name('admin.admins.update');
            Route::delete('/admins/{admin}', [\App\Http\Controllers\Admin\AdminUserController::class, 'destroy'])->name('admin.admins.destroy');
        });

        // Shop Settings
        Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('admin.settings.index');
        Route::post('/settings/schedule', [\App\Http\Controllers\Admin\SettingController::class, 'updateSchedule'])->name('admin.settings.schedule.update');
        Route::post('/settings/holidays', [\App\Http\Controllers\Admin\SettingController::class, 'storeHoliday'])->name('admin.settings.holidays.store');
        Route::delete('/settings/holidays/{holiday}', [\App\Http\Controllers\Admin\SettingController::class, 'destroyHoliday'])->name('admin.settings.holidays.destroy');

        // Service Packages
        Route::resource('packages', \App\Http\Controllers\Admin\ServicePackageController::class)->names([
            'index' => 'admin.packages.index',
            'create' => 'admin.packages.create',
            'store' => 'admin.packages.store',
            'show' => 'admin.packages.show',
            'edit' => 'admin.packages.edit',
            'update' => 'admin.packages.update',
            'destroy' => 'admin.packages.destroy',
        ]);

        // Services
        Route::resource('services', \App\Http\Controllers\Admin\ServiceController::class)->names([
            'index' => 'admin.services.index',
            'create' => 'admin.services.create',
            'store' => 'admin.services.store',
            'show' => 'admin.services.show',
            'edit' => 'admin.services.edit',
            'update' => 'admin.services.update',
            'destroy' => 'admin.services.destroy',
        ]);
    });
});

require __DIR__ . '/auth.php';

use App\Http\Controllers\BookingController;

Route::get('/booking', [BookingController::class, 'create'])->name('booking.create');
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
Route::get('/api/availability', [BookingController::class, 'checkAvailability'])->name('api.availability');
Route::get('/api/available-slots', [BookingController::class, 'getAvailableTimeSlots'])->name('api.available-slots');
