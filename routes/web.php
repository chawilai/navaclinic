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
    return Inertia::render('Services');
})->name('services');

Route::get('/siam-retreat', function () {
    return Inertia::render('SiamRetreat');
})->name('siam.retreat');

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->doctor) {
        // For doctors, we want to see upcoming and recent bookings
        $query = $user->doctor->bookings()->with('user');

        // Calculate Today's Stats
        $today = now()->format('Y-m-d');
        $todayStats = [
            'total' => $user->doctor->bookings()->whereDate('appointment_date', $today)->count(),
            'pending' => $user->doctor->bookings()->whereDate('appointment_date', $today)->whereIn('status', ['pending', 'confirmed'])->count(),
            'completed' => $user->doctor->bookings()->whereDate('appointment_date', $today)->where('status', 'completed')->count(),
            'revenue' => $user->doctor->bookings()->whereDate('appointment_date', $today)->where('status', '!=', 'cancelled')->sum('price'),
        ];

        // Weekly Workload Chart Data (Last 7 Days)
        $dates = collect(range(6, 0))->map(function ($days) {
            return now()->subDays($days)->format('Y-m-d');
        });

        $workloadCounts = $user->doctor->bookings()
            ->whereIn('appointment_date', $dates)
            ->selectRaw('DATE(appointment_date) as date, count(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        $chartData = [
            'labels' => $dates->map(fn($date) => \Carbon\Carbon::parse($date)->format('D d'))->toArray(),
            'data' => $dates->map(fn($date) => $workloadCounts->get($date, 0))->toArray(),
        ];

        // Find Next Booking (Earliest upcoming today or in future)
        $nextBooking = $user->doctor->bookings()
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

        // Main List: Sort by date descending (newest/future first)
        $bookings = $query->orderBy('appointment_date', 'desc')
            ->orderBy('start_time', 'asc')
            ->get();

        return Inertia::render('Dashboard', [
            'bookings' => $bookings,
            'isDoctor' => true,
            'todayStats' => $todayStats,
            'nextBooking' => $nextBooking,
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

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/owner-dashboard', [\App\Http\Controllers\Admin\OwnerDashboardController::class, 'index'])->name('admin.owner.dashboard');

    // Booking Management
    Route::get('/bookings/create', [\App\Http\Controllers\Admin\BookingController::class, 'create'])->name('admin.bookings.create');
    Route::post('/bookings', [\App\Http\Controllers\Admin\BookingController::class, 'store'])->name('admin.bookings.store');
    Route::get('/bookings/{booking}/edit', [\App\Http\Controllers\Admin\BookingController::class, 'edit'])->name('admin.bookings.edit'); // New
    Route::patch('/bookings/{booking}', [\App\Http\Controllers\Admin\BookingController::class, 'update'])->name('admin.bookings.update'); // New
    Route::get('/bookings/{booking}', [\App\Http\Controllers\Admin\BookingController::class, 'show'])->name('admin.bookings.show');
    Route::patch('/bookings/{booking}/status', [\App\Http\Controllers\Admin\BookingController::class, 'updateStatus'])->name('admin.bookings.update-status');

    // Payments
    Route::post('/bookings/{booking}/payments', [\App\Http\Controllers\Admin\PaymentController::class, 'store'])->name('admin.payments.store');
    Route::delete('/payments/{payment}', [\App\Http\Controllers\Admin\PaymentController::class, 'destroy'])->name('admin.payments.destroy');

    // Treatment Records
    Route::get('/bookings/{booking}/treatment/create', [\App\Http\Controllers\Admin\TreatmentController::class, 'create'])->name('admin.treatment.create');
    Route::post('/bookings/{booking}/treatment', [\App\Http\Controllers\Admin\TreatmentController::class, 'store'])->name('admin.treatment.store');

    // Patient Management
    Route::get('/patients', [\App\Http\Controllers\Admin\PatientController::class, 'index'])->name('admin.patients.index');
    Route::get('/patients/guest/{booking}', [\App\Http\Controllers\Admin\PatientController::class, 'showGuest'])->name('admin.patients.guest.show');
    Route::put('/patients/{user}', [\App\Http\Controllers\Admin\PatientController::class, 'update'])->name('admin.patients.update');
    Route::get('/patients/{user}', [\App\Http\Controllers\Admin\PatientController::class, 'show'])->name('admin.patients.show');

    // Doctor Management
    // Doctor Management
    Route::get('/doctors', [\App\Http\Controllers\Admin\DoctorController::class, 'index'])->name('admin.doctors.index');
    Route::post('/doctors', [\App\Http\Controllers\Admin\DoctorController::class, 'store'])->name('admin.doctors.store');
    Route::get('/doctors/{doctor}', [\App\Http\Controllers\Admin\DoctorController::class, 'show'])->name('admin.doctors.show');
    Route::patch('/doctors/{doctor}', [\App\Http\Controllers\Admin\DoctorController::class, 'update'])->name('admin.doctors.update');
    Route::delete('/doctors/{doctor}', [\App\Http\Controllers\Admin\DoctorController::class, 'destroy'])->name('admin.doctors.destroy');

    // Shop Settings
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('admin.settings.index');
    Route::post('/settings/schedule', [\App\Http\Controllers\Admin\SettingController::class, 'updateSchedule'])->name('admin.settings.schedule.update');
    Route::post('/settings/holidays', [\App\Http\Controllers\Admin\SettingController::class, 'storeHoliday'])->name('admin.settings.holidays.store');
    Route::delete('/settings/holidays/{holiday}', [\App\Http\Controllers\Admin\SettingController::class, 'destroyHoliday'])->name('admin.settings.holidays.destroy');
    // Visit Management
    Route::get('/visits/check-availability', [\App\Http\Controllers\Admin\VisitController::class, 'checkAvailability'])->name('admin.visits.check-availability');
    Route::get('/visits/create', [\App\Http\Controllers\Admin\VisitController::class, 'create'])->name('admin.visits.create');
    Route::post('/visits', [\App\Http\Controllers\Admin\VisitController::class, 'store'])->name('admin.visits.store');
    Route::get('/visits/{visit}', [\App\Http\Controllers\Admin\VisitController::class, 'show'])->name('admin.visits.show');

    // Visit Treatment Records
    Route::get('/visits/{visit}/treatment/create', [\App\Http\Controllers\Admin\TreatmentController::class, 'createForVisit'])->name('admin.visits.treatment.create');
    Route::post('/visits/{visit}/treatment', [\App\Http\Controllers\Admin\TreatmentController::class, 'storeForVisit'])->name('admin.visits.treatment.store');

    // Visit Payments
    Route::post('/visits/{visit}/payments', [\App\Http\Controllers\Admin\PaymentController::class, 'storeForVisit'])->name('admin.visits.payments.store');

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

    // Assign Package to Patient
    Route::post('/patients/packages', [\App\Http\Controllers\Admin\PatientPackageController::class, 'store'])->name('admin.patient-packages.store');
});

require __DIR__ . '/auth.php';

use App\Http\Controllers\BookingController;

Route::get('/booking', [BookingController::class, 'create'])->name('booking.create');
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
Route::get('/api/availability', [BookingController::class, 'checkAvailability'])->name('api.availability');
Route::get('/api/available-slots', [BookingController::class, 'getAvailableTimeSlots'])->name('api.available-slots');
