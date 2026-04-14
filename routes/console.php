<?php

use App\Models\SystemSetting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

$backupTimezone = config('app.timezone');

try {
    $backupTimezone = SystemSetting::current()->business_timezone ?: $backupTimezone;
} catch (\Throwable) {
    // Fall back to the app timezone if settings are unavailable.
}

Schedule::command('backups:create --note="Scheduled daily backup"')
    ->dailyAt('02:00')
    ->timezone($backupTimezone)
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/backup-schedule.log'));
