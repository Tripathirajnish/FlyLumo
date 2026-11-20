<?php

namespace App\Console\Commands;

use App\Services\DailyHealthReportService;
use Illuminate\Console\Command;

/** 17 Nov 2026: daily health email about the previous UTC day (read-only). */
class DailyHealthReport extends Command
{
    protected $signature = 'fh:daily-report {--date= : UTC day Y-m-d (default: yesterday)} {--dry-run : print, send nothing} {--force : send again}';

    protected $description = 'Email the daily FaresHunt health report (searches, payments, bookings, supplier, errors).';

    public function handle(DailyHealthReportService $report): int
    {
        $r = $report->run($this->option('date') ?: null, (bool) $this->option('dry-run'), (bool) $this->option('force'));
        $this->line('status='.$r['status'].' attention='.$r['attention']);
        if (isset($r['subject'])) {
            $this->line('subject: '.$r['subject']);
        }
        if (isset($r['body'])) {
            $this->line($r['body']);
        }

        return self::SUCCESS;
    }
}
