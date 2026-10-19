<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

$vendorTiangDeadlineCheck = Schedule::command('vendor-tiang:check-deadlines')
    ->name('vendor-tiang-deadline-check')
    ->withoutOverlapping();

if (config('services.vendor_tiang_deadline_seconds', 259200) <= 60) {
    $vendorTiangDeadlineCheck->everySecond();
} else {
    $vendorTiangDeadlineCheck->everyMinute();
}
