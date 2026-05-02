<?php

use App\Console\Commands\GenerateMonthlyInvoices;
use App\Console\Commands\SendContractExpiryReminders;
use App\Console\Commands\SendOverdueReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─── Cron Schedule ────────────────────────────────────────────────────────────

// Generate monthly invoices on 1st of each month at 08:00
Schedule::command(GenerateMonthlyInvoices::class)->monthlyOn(1, '08:00');

// Send overdue reminders daily at 09:00
Schedule::command(SendOverdueReminders::class)->dailyAt('09:00');

// Send contract expiry reminders daily at 08:30
Schedule::command(SendContractExpiryReminders::class)->dailyAt('08:30');
