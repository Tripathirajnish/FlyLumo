<?php

namespace App\Console\Commands;

use App\Services\SupplierAlertService;
use Illuminate\Console\Command;

/** L5-5b (17 Nov 2026): email when the flight supplier is down / recovered (reads meta-monitor results only). */
class SupplierAlertCheck extends Command
{
    protected $signature = 'supplier:alert-check {--dry-run : Show what would be emailed; send nothing}';

    protected $description = 'Email the team when the flight supplier is down or recovered (reads meta monitor checks).';

    public function handle(SupplierAlertService $alerts): int
    {
        $r = $alerts->run((bool) $this->option('dry-run'));
        $this->line('status='.$r['status'].' sent='.$r['sent'].($r['details'] ? ' '.implode('; ', $r['details']) : ''));

        return self::SUCCESS;
    }
}
