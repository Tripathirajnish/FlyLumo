<?php

namespace App\Console\Commands;

use App\Services\StripeBookingExecutorService;
use Illuminate\Console\Command;

/** Step F3b: cron backup for the booking executor (the webhook runs it first). */
class StripeProcessAuthorized extends Command
{
    protected $signature = 'stripe:process-authorized {--limit=5}';

    protected $description = 'Stripe: book flights for payments on hold (authorized). Never retries AeroBook.';

    public function handle(): int
    {
        $res = app(StripeBookingExecutorService::class)->runDue((int) $this->option('limit'));

        if (!$res) {
            $this->line('nothing to do');
        }
        foreach ($res as $id => $r) {
            $this->line('attempt #'.$id.': '.$r);
        }

        return self::SUCCESS;
    }
}
