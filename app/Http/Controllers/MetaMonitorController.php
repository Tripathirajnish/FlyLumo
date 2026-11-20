<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\CrmAccessService;

class MetaMonitorController extends Controller
{
    private function guard(Request $request)
    {
        return $request->session()->has('crm_admin_id')
            ? null
            : redirect()->route('crm.login');
    }

    public function index(Request $request)
    {
        if ($x = $this->guard($request)) {
            return $x;
        }

        $adminId = (int) $request->session()->get('crm_admin_id');

        abort_unless(
            app(CrmAccessService::class)->can($adminId, 'meta_monitor.view'),
            403
        );

        abort_unless(
            Schema::hasTable('meta_monitor_sources') &&
            Schema::hasTable('meta_monitor_checks'),
            404
        );

        $sources = DB::table('meta_monitor_sources')
            ->orderBy('name')
            ->get();

        $summary = [
            'total' => $sources->count(),
            'active' => $sources->where('is_active', 1)->count(),
            'healthy' => $sources->where('status', 'healthy')->count(),
            'warning' => $sources->where('status', 'warning')->count(),
            'down' => $sources->whereIn('status', ['down', 'error'])->count(),
        ];

        $recentChecks = DB::table('meta_monitor_checks as c')
            ->leftJoin('meta_monitor_sources as s', 's.id', '=', 'c.source_id')
            ->select(
                'c.*',
                's.name as source_name',
                's.slug as source_slug',
                's.target_ms'
            )
            ->orderByDesc('c.checked_at')
            ->orderByDesc('c.id')
            ->limit(100)
            ->get();

        $performance = DB::table('meta_monitor_checks as c')
            ->join('meta_monitor_sources as s', 's.id', '=', 'c.source_id')
            ->where('c.checked_at', '>=', now()->subHours(24))
            ->groupBy('c.source_id', 's.name', 's.slug')
            ->select(
                'c.source_id',
                's.name',
                's.slug',
                DB::raw('COUNT(*) as total_checks'),
                DB::raw('ROUND(AVG(c.response_ms)) as avg_response_ms'),
                DB::raw("SUM(CASE WHEN c.status IN ('healthy','warning') THEN 1 ELSE 0 END) as successful_checks"),
                DB::raw("SUM(CASE WHEN c.status = 'healthy' THEN 1 ELSE 0 END) as healthy_checks"),
                DB::raw("SUM(CASE WHEN c.status IN ('warning','down','error') THEN 1 ELSE 0 END) as problem_checks")
            )
            ->orderBy('s.name')
            ->get();

        foreach ($performance as $row) {
            $row->success_rate = $row->total_checks > 0
                ? round(($row->successful_checks / $row->total_checks) * 100, 1)
                : 0;

            $times = DB::table('meta_monitor_checks')
                ->where('source_id', $row->source_id)
                ->where('checked_at', '>=', now()->subHours(24))
                ->whereNotNull('response_ms')
                ->orderBy('response_ms')
                ->pluck('response_ms')
                ->values();

            if ($times->isEmpty()) {
                $row->p95_response_ms = null;
            } else {
                $index = (int) ceil($times->count() * 0.95) - 1;
                $index = max(0, min($index, $times->count() - 1));
                $row->p95_response_ms = (int) $times[$index];
            }
        }

        return view('crm.meta-monitor.index', compact(
            'sources',
            'summary',
            'recentChecks',
            'performance'
        ));
    }
}
