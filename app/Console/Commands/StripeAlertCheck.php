<?php

namespace App\Console\Commands;

use App\Services\StripeAlertService;
use Illuminate\Console\Command;

class StripeAlertCheck extends Command
{
    protected $signature = 'stripe:alert-check {--dry-run : Show what would be emailed; send nothing}';

    protected $description = 'Email the team when a Stripe payment is stuck or webhooks fail (read-only check).';

    public function handle(StripeAlertService $alerts): int
    {
        $r = $alerts->run((bool) $this->option('dry-run'));
        $this->line('status='.$r['status'].' items='.$r['items'].' recipients='.$r['recipients']);
        if ($r['status'] === 'dry_run') {
            $this->line('subject: '.$r['subject']);
            $this->line($r['body']);
        }

        return self::SUCCESS;
    }
}
