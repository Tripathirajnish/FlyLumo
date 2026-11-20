@extends('crm.layout')

@section('content')

<style>
.mm-page{
    --mm-navy:#10284b;
    --mm-blue:#1858c7;
    --mm-muted:#74839a;
    --mm-line:#e3eaf3;
    --mm-green:#18845b;
    --mm-orange:#b96a00;
    --mm-red:#c43d48;
}

.mm-head{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:18px;
    margin-bottom:18px;
}

.mm-eyebrow{
    display:flex;
    align-items:center;
    gap:8px;
    color:var(--mm-blue);
    font-size:10px;
    font-weight:800;
    letter-spacing:.08em;
    text-transform:uppercase;
}

.mm-eyebrow:before{
    content:"";
    width:28px;
    height:3px;
    border-radius:99px;
    background:#e43c46;
}

.mm-head h1{
    margin:7px 0 5px;
    font-size:30px;
    line-height:1.08;
    color:var(--mm-navy);
}

.mm-head p{
    margin:0;
    color:var(--mm-muted);
    font-size:13px;
}

.mm-refresh{
    display:flex;
    align-items:center;
    gap:8px;
}

.mm-refresh button{
    border:1px solid var(--mm-line);
    background:#fff;
    color:var(--mm-navy);
    border-radius:10px;
    padding:9px 13px;
    font-size:11px;
    font-weight:700;
    cursor:pointer;
}

.mm-kpis{
    display:grid;
    grid-template-columns:repeat(5,minmax(0,1fr));
    gap:14px;
    margin-bottom:18px;
}

.mm-kpi{
    background:#fff;
    border:1px solid var(--mm-line);
    border-radius:15px;
    padding:18px 19px;
    box-shadow:0 5px 18px rgba(18,40,75,.035);
}

.mm-kpi-label{
    font-size:10px;
    font-weight:800;
    text-transform:uppercase;
    color:#53657d;
    letter-spacing:.03em;
}

.mm-kpi-value{
    margin-top:7px;
    font-size:27px;
    font-weight:800;
    line-height:1;
    color:var(--mm-navy);
}

.mm-kpi-note{
    margin-top:8px;
    font-size:10px;
    color:#8390a2;
}

.mm-main{
    display:grid;
    grid-template-columns:minmax(0,2fr) minmax(300px,1fr);
    gap:18px;
    align-items:start;
}

.mm-card{
    background:#fff;
    border:1px solid var(--mm-line);
    border-radius:16px;
    overflow:hidden;
    box-shadow:0 5px 18px rgba(18,40,75,.03);
}

.mm-card-head{
    padding:16px 18px;
    border-bottom:1px solid #edf1f6;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
}

.mm-card-title{
    font-size:13px;
    font-weight:800;
    color:var(--mm-navy);
}

.mm-card-sub{
    font-size:10px;
    color:#8492a5;
    margin-top:3px;
}

.mm-table-wrap{
    overflow-x:auto;
}

.mm-table{
    width:100%;
    border-collapse:collapse;
}

.mm-table th{
    padding:11px 15px;
    background:#fafbfd;
    border-bottom:1px solid #e9eef5;
    text-align:left;
    font-size:9px;
    text-transform:uppercase;
    letter-spacing:.04em;
    color:#8190a4;
    white-space:nowrap;
}

.mm-table td{
    padding:12px 15px;
    border-bottom:1px solid #edf1f5;
    font-size:11px;
    color:#263b58;
    white-space:nowrap;
}

.mm-table tr:last-child td{
    border-bottom:0;
}

.mm-source{
    font-weight:800;
    color:#142945;
}

.mm-slug{
    display:block;
    margin-top:3px;
    font-size:9px;
    color:#8a97a8;
    font-weight:500;
}

.mm-badge{
    display:inline-flex;
    align-items:center;
    gap:5px;
    border-radius:999px;
    padding:5px 8px;
    font-size:9px;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.02em;
}

.mm-badge:before{
    content:"";
    width:5px;
    height:5px;
    border-radius:50%;
    background:currentColor;
}

.mm-healthy{
    color:var(--mm-green);
    background:#eaf8f2;
}

.mm-warning{
    color:var(--mm-orange);
    background:#fff4df;
}

.mm-down{
    color:var(--mm-red);
    background:#fdebed;
}

.mm-unknown{
    color:#66778d;
    background:#eef2f6;
}

.mm-speed{
    font-weight:800;
    font-variant-numeric:tabular-nums;
}

.mm-speed-good{color:var(--mm-green)}
.mm-speed-warn{color:var(--mm-orange)}
.mm-speed-bad{color:var(--mm-red)}

.mm-side{
    display:grid;
    gap:18px;
}

.mm-stat-list{
    padding:8px 18px 14px;
}

.mm-stat-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    padding:12px 0;
    border-bottom:1px solid #edf1f5;
}

.mm-stat-row:last-child{
    border-bottom:0;
}

.mm-stat-label{
    font-size:11px;
    color:#53657d;
}

.mm-stat-value{
    font-size:13px;
    font-weight:800;
    color:var(--mm-navy);
    font-variant-numeric:tabular-nums;
}

.mm-empty{
    padding:34px 18px;
    text-align:center;
    color:#8492a5;
    font-size:11px;
}

.mm-fail{
    color:var(--mm-red);
    max-width:260px;
    overflow:hidden;
    text-overflow:ellipsis;
}

.mm-history{
    margin-top:18px;
}

@media(max-width:1200px){
    .mm-kpis{
        grid-template-columns:repeat(3,minmax(0,1fr));
    }
}

@media(max-width:1050px){
    .mm-main{
        grid-template-columns:1fr;
    }
}

@media(max-width:650px){
    .mm-head{
        flex-direction:column;
    }

    .mm-kpis{
        grid-template-columns:1fr 1fr;
        gap:10px;
    }

    .mm-kpi{
        padding:16px;
    }

    .mm-kpi-value{
        font-size:24px;
    }

    .mm-table th,
    .mm-table td{
        padding:10px 12px;
    }
}

@media(max-width:420px){
    .mm-kpis{
        grid-template-columns:1fr;
    }
}
</style>

@php
    $total = (int)($summary['total'] ?? 0);
    $active = (int)($summary['active'] ?? 0);
    $healthy = (int)($summary['healthy'] ?? 0);
    $warning = (int)($summary['warning'] ?? 0);
    $down = (int)($summary['down'] ?? 0);

    $latest = $recentChecks->first();
    $latestMs = $latest ? (int)($latest->response_ms ?? 0) : 0;

    $statusClass = function($status) {
        return match(strtolower((string)$status)) {
            'healthy' => 'mm-healthy',
            'warning' => 'mm-warning',
            'down', 'error' => 'mm-down',
            default => 'mm-unknown',
        };
    };

    $speedClass = function($ms, $target) {
        $ms = (int)$ms;
        $target = max(1, (int)$target);

        if ($ms <= $target) return 'mm-speed-good';
        if ($ms <= ($target * 1.5)) return 'mm-speed-warn';
        return 'mm-speed-bad';
    };
@endphp

<div class="mm-page">

    <div class="mm-head">
        <div>
            <div class="mm-eyebrow">Meta Intelligence</div>
            <h1>Meta Performance Monitor</h1>
            <p>Partner traffic, redirect health and flight-listing performance across comparison channels.</p>
        </div>

        <div class="mm-refresh">
            <button type="button" onclick="window.location.reload()">
                <i class="fa-solid fa-rotate"></i>
                Refresh
            </button>
        </div>
    </div>

    <div class="mm-kpis">

        <div class="mm-kpi">
            <div class="mm-kpi-label">Meta Partners</div>
            <div class="mm-kpi-value">{{ number_format($total) }}</div>
            <div class="mm-kpi-note">{{ number_format($active) }} active</div>
        </div>

        <div class="mm-kpi">
            <div class="mm-kpi-label">Healthy</div>
            <div class="mm-kpi-value">{{ number_format($healthy) }}</div>
            <div class="mm-kpi-note">Within performance target</div>
        </div>

        <div class="mm-kpi">
            <div class="mm-kpi-label">Warnings</div>
            <div class="mm-kpi-value">{{ number_format($warning) }}</div>
            <div class="mm-kpi-note">Above target / degraded</div>
        </div>

        <div class="mm-kpi">
            <div class="mm-kpi-label">Down / Error</div>
            <div class="mm-kpi-value">{{ number_format($down) }}</div>
            <div class="mm-kpi-note">Requires attention</div>
        </div>

        <div class="mm-kpi">
            <div class="mm-kpi-label">Latest Response</div>
            <div class="mm-kpi-value">
                {{ $latestMs ? number_format($latestMs).' ms' : '—' }}
            </div>
            <div class="mm-kpi-note">Most recent recorded check</div>
        </div>

    </div>

    <div class="mm-main">

        <div class="mm-card">

            <div class="mm-card-head">
                <div>
                    <div class="mm-card-title">Partner Health</div>
                    <div class="mm-card-sub">Current status and latest listing response</div>
                </div>
            </div>

            <div class="mm-table-wrap">
                <table class="mm-table">
                    <thead>
                        <tr>
                            <th>Partner</th>
                            <th>Status</th>
                            <th>API</th>
                            <th>Listing</th>
                            <th>Total</th>
                            <th>Slow Stage</th>
                            <th>Target</th>
                            <th>HTTP</th>
                            <th>Last Checked</th>
                        </tr>
                    </thead>

                    <tbody>
                    @forelse($sources as $source)

                        @php
                            $ms = (int)($source->last_response_ms ?? 0);
                            $target = (int)($source->target_ms ?? 5000);

                            $sourceLatest = $recentChecks->firstWhere('source_id', $source->id);
                            $sourceContext = [];

                            if ($sourceLatest && !empty($sourceLatest->context)) {
                                $sourceContext = json_decode($sourceLatest->context, true) ?: [];
                            }

                            $apiMs = isset($sourceContext['api_ms'])
                                ? (int)$sourceContext['api_ms']
                                : null;

                            $listingMs = isset($sourceContext['listing_ms'])
                                ? (int)$sourceContext['listing_ms']
                                : null;

                            if (!$sourceLatest) {
                                $slowStage = 'No data';
                            } elseif (!$sourceLatest->redirect_ok) {
                                $slowStage = 'Redirect';
                            } elseif ($listingMs !== null && $listingMs > $target) {
                                $slowStage = 'Listing';
                            } elseif ($ms > $target) {
                                $slowStage = 'Total';
                            } elseif (($sourceLatest->status ?? '') === 'error') {
                                $slowStage = 'Request';
                            } else {
                                $slowStage = 'Healthy';
                            }
                        @endphp

                        <tr>
                            <td>
                                <span class="mm-source">{{ $source->name }}</span>
                                <span class="mm-slug">{{ $source->slug }}</span>
                            </td>

                            <td>
                                <span class="mm-badge {{ $statusClass($source->status) }}">
                                    {{ $source->status ?: 'unknown' }}
                                </span>
                            </td>

                            <td class="mm-speed">
                                {{ $apiMs !== null ? number_format($apiMs).' ms' : '—' }}
                            </td>

                            <td class="mm-speed {{ $listingMs !== null ? $speedClass($listingMs, $target) : '' }}">
                                {{ $listingMs !== null ? number_format($listingMs).' ms' : '—' }}
                            </td>

                            <td class="mm-speed {{ $speedClass($ms, $target) }}">
                                {{ $ms ? number_format($ms).' ms' : '—' }}
                            </td>

                            <td>
                                @if($slowStage === 'Healthy')
                                    <span class="mm-badge mm-healthy">Healthy</span>
                                @elseif($slowStage === 'No data')
                                    <span class="mm-badge mm-unknown">No data</span>
                                @else
                                    <span class="mm-badge mm-warning">{{ $slowStage }}</span>
                                @endif
                            </td>

                            <td>
                                {{ number_format($target) }} ms
                            </td>

                            <td>
                                {{ $source->last_http_status ?: '—' }}
                            </td>

                            <td>
                                {{ $source->last_checked_at ?: 'Never' }}
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="9" class="mm-empty">
                                No meta partners configured yet.
                            </td>
                        </tr>

                    @endforelse
                    </tbody>
                </table>
            </div>

        </div>

        <div class="mm-side">

            <div class="mm-card">
                <div class="mm-card-head">
                    <div>
                        <div class="mm-card-title">24h Performance</div>
                        <div class="mm-card-sub">Partner-level health summary</div>
                    </div>
                </div>

                <div class="mm-table-wrap">
                    <table class="mm-table">
                        <thead>
                            <tr>
                                <th>Partner</th>
                                <th>Avg</th>
                                <th>P95</th>
                                <th>Success Rate</th>
                                <th>Checks</th>
                            </tr>
                        </thead>

                        <tbody>
                        @forelse($performance as $item)

                            <tr>
                                <td class="mm-source">{{ $item->name }}</td>
                                <td class="mm-speed">
                                    {{ $item->avg_response_ms ? number_format($item->avg_response_ms).' ms' : '—' }}
                                </td>
                                <td class="mm-speed">
                                    {{ $item->p95_response_ms !== null ? number_format($item->p95_response_ms).' ms' : '—' }}
                                </td>
                                <td>
                                    {{ number_format($item->success_rate, 1) }}%
                                </td>
                                <td>
                                    {{ number_format($item->total_checks) }}
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="5" class="mm-empty">
                                    No checks recorded yet.
                                </td>
                            </tr>

                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mm-card">

                <div class="mm-card-head">
                    <div>
                        <div class="mm-card-title">System Status</div>
                        <div class="mm-card-sub">Latest monitor state</div>
                    </div>
                </div>

                <div class="mm-stat-list">

                    <div class="mm-stat-row">
                        <span class="mm-stat-label">Configured partners</span>
                        <span class="mm-stat-value">{{ number_format($total) }}</span>
                    </div>

                    <div class="mm-stat-row">
                        <span class="mm-stat-label">Active partners</span>
                        <span class="mm-stat-value">{{ number_format($active) }}</span>
                    </div>

                    <div class="mm-stat-row">
                        <span class="mm-stat-label">Healthy</span>
                        <span class="mm-stat-value">{{ number_format($healthy) }}</span>
                    </div>

                    <div class="mm-stat-row">
                        <span class="mm-stat-label">Needs attention</span>
                        <span class="mm-stat-value">{{ number_format($warning + $down) }}</span>
                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="mm-card mm-history">

        <div class="mm-card-head">
            <div>
                <div class="mm-card-title">Recent Checks</div>
                <div class="mm-card-sub">Latest redirect and listing monitoring events</div>
            </div>
        </div>

        <div class="mm-table-wrap">
            <table class="mm-table">
                <thead>
                    <tr>
                        <th>Partner</th>
                        <th>Status</th>
                        <th>API</th>
                        <th>Listing</th>
                        <th>Total</th>
                        <th>Slow Stage</th>
                        <th>Redirect</th>
                        <th>HTTP</th>
                        <th>Results</th>
                        <th>Search Code</th>
                        <th>Checked</th>
                        <th>Error</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($recentChecks as $check)

                    @php
                        $target = (int)($check->target_ms ?? 5000);
                        $context = [];

                        if (!empty($check->context)) {
                            $context = json_decode($check->context, true) ?: [];
                        }

                        $apiMs = isset($context['api_ms'])
                            ? (int)$context['api_ms']
                            : null;

                        $listingMs = isset($context['listing_ms'])
                            ? (int)$context['listing_ms']
                            : null;

                        if (!array_key_exists('api_ms', $context)) {
                            $slowStage = 'Legacy';
                        } elseif (!$check->redirect_ok) {
                            $slowStage = 'Redirect';
                        } elseif ($listingMs !== null && $listingMs > $target) {
                            $slowStage = 'Listing';
                        } elseif ((int)$check->response_ms > $target) {
                            $slowStage = 'Total';
                        } elseif (($check->status ?? '') === 'error') {
                            $slowStage = 'Request';
                        } else {
                            $slowStage = 'Healthy';
                        }
                    @endphp

                    <tr>
                        <td class="mm-source">{{ $check->source_name }}</td>

                        <td>
                            <span class="mm-badge {{ $statusClass($check->status) }}">
                                {{ $check->status }}
                            </span>
                        </td>

                        <td class="mm-speed">
                            {{ $apiMs !== null ? number_format($apiMs).' ms' : '—' }}
                        </td>

                        <td class="mm-speed {{ $listingMs !== null ? $speedClass($listingMs, $target) : '' }}">
                            {{ $listingMs !== null ? number_format($listingMs).' ms' : '—' }}
                        </td>

                        <td class="mm-speed {{ $speedClass($check->response_ms, $target) }}">
                            {{ $check->response_ms ? number_format($check->response_ms).' ms' : '—' }}
                        </td>

                        <td>
                            @if($slowStage === 'Healthy')
                                <span class="mm-badge mm-healthy">Healthy</span>
                            @elseif($slowStage === 'Legacy')
                                <span class="mm-badge mm-unknown">Legacy</span>
                            @else
                                <span class="mm-badge mm-warning">{{ $slowStage }}</span>
                            @endif
                        </td>

                        <td>
                            @if($check->redirect_ok === null)
                                —
                            @elseif($check->redirect_ok)
                                <span class="mm-healthy">OK</span>
                            @else
                                <span class="mm-speed-bad">Failed</span>
                            @endif
                        </td>

                        <td>{{ $check->http_status ?: '—' }}</td>

                        <td>{{ $check->result_count !== null ? number_format($check->result_count) : '—' }}</td>

                        <td>{{ $check->search_code ?: '—' }}</td>

                        <td>{{ $check->checked_at ?: '—' }}</td>

                        <td class="mm-fail" title="{{ $check->error_message ?: '' }}">
                            {{ $check->error_message ?: '—' }}
                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="12" class="mm-empty">
                            No monitoring checks recorded yet. The monitor is ready for its first partner check.
                        </td>
                    </tr>

                @endforelse
                </tbody>
            </table>
        </div>

    </div>

</div>

@endsection
