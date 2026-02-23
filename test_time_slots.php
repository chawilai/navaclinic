<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$date = '2026-02-26';
$duration = 90;
$dayOfWeek = 4;
$schedule = \App\Models\ClinicSchedule::where('day_of_week', $dayOfWeek)->first();

$adminStart = !empty($schedule->admin_booking_start_time) ? $schedule->admin_booking_start_time : $schedule->open_time;
$adminEnd = !empty($schedule->admin_booking_end_time) ? $schedule->admin_booking_end_time : $schedule->close_time;

$startOfDay = \Carbon\Carbon::parse($date . ' ' . $adminStart);
$endOfDay = \Carbon\Carbon::parse($date . ' ' . $adminEnd);

echo "AdminStart: {$adminStart}\n";
echo "AdminEnd: {$adminEnd}\n";
echo "StartOfDay: {$startOfDay->toDateTimeString()}\n";
echo "EndOfDay: {$endOfDay->toDateTimeString()}\n";

$current = $startOfDay->copy();
$endCheckBounds = $endOfDay->copy();
echo "EndCheckBounds: {$endCheckBounds->toDateTimeString()}\n\n";

$slots = [];
$i = 0;
while ($current->copy()->addMinutes($duration) <= $endCheckBounds) {
    if ($i++ > 20) {
        echo "Loop breaking to prevent infinite loop.\n";
        break;
    }
    echo "Evaluating slot: {$current->toDateTimeString()}\n";
    $slotStart = $current->copy();
    $slots[] = $slotStart->format('H:i');
    $current->addMinutes(30);
}

echo "Generated slots count: " . count($slots) . "\n";
print_r($slots);
