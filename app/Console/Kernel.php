<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Keep privacy-safe session telemetry bounded. Command is a no-op when tables are absent.
        $schedule->command('live-pax:cleanup')->dailyAt('03:30')->withoutOverlapping();

        // Monitor active meta/comparison partners without overlapping checks.
        $schedule->command('meta-monitor:check')->everyFiveMinutes()->withoutOverlapping();

        // Stripe TEST payments: email the team when a payment is stuck or webhooks fail (read-only check, owner-approved 4 Nov 2026).
        // No withoutOverlapping(): the shared cache folder is apache-owned; the command uses its own run lock (Fix 14c).
        $schedule->command('stripe:alert-check')->everyFiveMinutes();

        // L5-5b (17 Nov 2026): email when the flight supplier is down / recovered. Reads meta-monitor results only.
        // withoutOverlapping(10): the cron (apache) then creates this job's lock folder, so
        // 'php artisan schedule:list' as ec2-user keeps working (it checks every job's lock).
        $schedule->command('supplier:alert-check')->everyFiveMinutes()->withoutOverlapping(10);

        // Daily health report (17 Nov 2026): email about the previous UTC day, 08:50 IST. Read-only.
        $schedule->command('fh:daily-report')->dailyAt('03:20');

        // Stripe TEST Step F3b (owner-approved D3, 5 Nov 2026): backup for the booking executor the webhook runs.
        // Safe to overlap: each attempt is claimed with a compare-and-set, AeroBook is never retried.
        $schedule->command('stripe:process-authorized')->everyMinute();

        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
