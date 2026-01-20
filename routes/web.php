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
        $bookings = $user->doctor->bookings()->with('user')->latest()->get();
    } else {
        $bookings = $user->bookings()->with('doctor')->latest()->get();
    }
    return Inertia::render('Dashboard', [
        'bookings' => $bookings,
        'isDoctor' => $user->doctor ? true : false,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');

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
});

require __DIR__ . '/auth.php';

use App\Http\Controllers\BookingController;

Route::get('/booking', [BookingController::class, 'create'])->name('booking.create');
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
Route::get('/api/availability', [BookingController::class, 'checkAvailability'])->name('api.availability');
Route::get('/api/available-slots', [BookingController::class, 'getAvailableTimeSlots'])->name('api.available-slots');
