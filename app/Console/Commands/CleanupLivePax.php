<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanupLivePax extends Command
{
    protected $signature = 'live-pax:cleanup {--days=}';
    protected $description = 'Delete expired Live Pax recordings and session telemetry.';

    public function handle(): int
    {
        $days = max(1, min(3650, (int)($this->option('days') ?: config('live_pax.retention_days', 30))));
        $cutoff = now()->subDays($days);
        if (!Schema::hasTable('live_pax_sessions')) return self::SUCCESS;
        $ids = DB::table('live_pax_sessions')->where('started_at','<',$cutoff)->pluck('id');
        foreach ($ids->chunk(500) as $chunk) {
            if (Schema::hasTable('live_pax_interactions')) DB::table('live_pax_interactions')->whereIn('session_id',$chunk)->delete();
            if (Schema::hasTable('live_pax_recording_chunks')) DB::table('live_pax_recording_chunks')->whereIn('session_id',$chunk)->delete();
            if (Schema::hasTable('live_pax_page_views')) DB::table('live_pax_page_views')->whereIn('session_id',$chunk)->delete();
            DB::table('live_pax_sessions')->whereIn('id',$chunk)->delete();
        }
        $this->info("Live Pax data older than {$days} days removed.");
        return self::SUCCESS;
    }
}
