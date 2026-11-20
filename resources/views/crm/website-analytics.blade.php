@extends('crm.layout')

@section('content')

<style>
.wa-page{--wa-blue:#1858c7;--wa-navy:#10284b;--wa-muted:#74839a;--wa-line:#e3eaf3}
.wa-head{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;margin-bottom:18px}
.wa-eyebrow{display:flex;align-items:center;gap:8px;color:var(--wa-blue);font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
.wa-eyebrow:before{content:"";width:28px;height:3px;border-radius:99px;background:#e43c46}
.wa-head h1{margin:7px 0 5px;font-size:30px;line-height:1.08;color:var(--wa-navy)}
.wa-head p{margin:0;color:var(--wa-muted);font-size:13px}
.wa-range{min-width:112px}

.wa-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:18px}
.wa-kpi{background:#fff;border:1px solid var(--wa-line);border-radius:15px;padding:19px 20px;box-shadow:0 5px 18px rgba(18,40,75,.035)}
.wa-kpi-label{font-size:10px;font-weight:800;text-transform:uppercase;color:#53657d;letter-spacing:.02em}
.wa-kpi-value{margin-top:7px;font-size:28px;font-weight:800;line-height:1;color:var(--wa-navy)}
.wa-kpi-note{margin-top:9px;font-size:10px;color:#8390a2}
.wa-kpi-note strong{color:#315f9d}

.wa-main{display:grid;grid-template-columns:minmax(0,2fr) minmax(280px,1fr);gap:18px;align-items:start}
.wa-card{background:#fff;border:1px solid var(--wa-line);border-radius:16px;overflow:hidden;box-shadow:0 5px 18px rgba(18,40,75,.03)}
.wa-card-head{padding:16px 18px;border-bottom:1px solid #edf1f6;font-size:13px;font-weight:800;color:var(--wa-navy)}
.wa-table{width:100%;border-collapse:collapse}
.wa-table th{padding:11px 18px;background:#fafbfd;border-bottom:1px solid #e9eef5;text-align:left;font-size:9px;text-transform:uppercase;letter-spacing:.04em;color:#8190a4}
.wa-table td{padding:11px 18px;border-bottom:1px solid #edf1f5;font-size:12px;color:#263b58}
.wa-table tr:last-child td{border-bottom:0}
.wa-route{font-weight:750;color:#142945}

.wa-breakdown{padding:16px 18px}
.wa-breakdown + .wa-card-head{border-top:1px solid #edf1f6}
.wa-bar-row{margin-bottom:15px}
.wa-bar-row:last-child{margin-bottom:0}
.wa-bar-top{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:7px;font-size:11px}
.wa-bar-top span:first-child{color:#30445f;font-weight:650}
.wa-bar-top span:last-child{color:#687b94;font-variant-numeric:tabular-nums}
.wa-track{height:7px;background:#edf2f8;border-radius:999px;overflow:hidden}
.wa-fill{height:100%;border-radius:999px;background:#2459df;min-width:2px}

.wa-opps{margin-top:18px}
.wa-empty{padding:34px 18px;text-align:center;color:#8492a5;font-size:11px}

@media(max-width:1050px){
  .wa-kpis{grid-template-columns:repeat(2,1fr)}
  .wa-main{grid-template-columns:1fr}
}
@media(max-width:650px){
  .wa-head{flex-direction:column}
  .wa-range{width:100%}
  .wa-kpis{grid-template-columns:1fr 1fr;gap:10px}
  .wa-kpi{padding:16px}
  .wa-kpi-value{font-size:24px}
  .wa-table th,.wa-table td{padding:10px 12px}
}
@media(max-width:420px){
  .wa-kpis{grid-template-columns:1fr}
}
</style>

@php
    $searches  = (int)($stats['searches'] ?? 0);
    $results   = (int)($stats['results'] ?? 0);
    $checkouts = (int)($stats['checkouts'] ?? 0);
    $bookings  = (int)($stats['bookings'] ?? 0);

    // Only show meaningful downstream conversion rates.
    // Result-page events may exceed searches because analytics can record multiple result-page events per search lifecycle.
    $checkoutRate = $searches > 0 ? ($checkouts / $searches) * 100 : 0;
    $bookingRate  = $searches > 0 ? ($bookings / $searches) * 100 : 0;

    $sourceMax = max(1, (int)collect($sources)->max('events'));
    $deviceMax = max(1, (int)collect($devices)->max('events'));
@endphp

<div class="wa-page">
    <div class="wa-head">
        <div>
            <div class="wa-eyebrow">Website Intelligence</div>
            <h1>Website Analytics</h1>
            <p>Passenger search demand and booking funnel. Analytics failures never block flight search.</p>
        </div>

        <form>
            <select class="form-select wa-range" name="days" onchange="this.form.submit()">
                <option value="7" @selected($days == 7)>7 days</option>
                <option value="30" @selected($days == 30)>30 days</option>
                <option value="90" @selected($days == 90)>90 days</option>
            </select>
        </form>
    </div>

    <div class="wa-kpis">
        <div class="wa-kpi">
            <div class="wa-kpi-label">Searches</div>
            <div class="wa-kpi-value">{{ number_format($searches) }}</div>
            <div class="wa-kpi-note">Passenger search events</div>
        </div>
        <div class="wa-kpi">
            <div class="wa-kpi-label">Results Pages</div>
            <div class="wa-kpi-value">{{ number_format($results) }}</div>
            <div class="wa-kpi-note">Recorded result-page events</div>
        </div>
        <div class="wa-kpi">
            <div class="wa-kpi-label">Checkout Starts</div>
            <div class="wa-kpi-value">{{ number_format($checkouts) }}</div>
            <div class="wa-kpi-note"><strong>{{ number_format($checkoutRate, 1) }}%</strong> of searches reached checkout</div>
        </div>
        <div class="wa-kpi">
            <div class="wa-kpi-label">Bookings</div>
            <div class="wa-kpi-value">{{ number_format($bookings) }}</div>
            <div class="wa-kpi-note"><strong>{{ number_format($bookingRate, 1) }}%</strong> search-to-booking conversion</div>
        </div>
    </div>


    @php
        $fl = $fareleaderFunnel ?? [];
        $flResults = (int)($fl['results'] ?? 0);
        $flStages = [
            ['Results', $flResults],
            ['Checkout', (int)($fl['checkout'] ?? 0)],
            ['Contact Entered', (int)($fl['contact_entered'] ?? 0)],
            ['Payment Section', (int)($fl['payment_section'] ?? 0)],
            ['Confirm Clicked', (int)($fl['confirm_clicked'] ?? 0)],
            ['Book API Success', (int)($fl['book_api_success'] ?? 0)],
            ['Booking', (int)($fl['booking'] ?? 0)],
        ];
        $flFailed = (int)($fl['book_api_failed'] ?? 0);
    @endphp

    <div class="wa-card" style="margin-bottom:18px">
        <div class="wa-card-head">
            FareLeaders Conversion Funnel
            <span style="float:right;font-size:10px;font-weight:600;color:#74839a">
                Unique sessions · {{ $days }} days
            </span>
        </div>

        <div style="padding:18px;overflow-x:auto">
            <div style="display:grid;grid-template-columns:repeat(7,minmax(125px,1fr));gap:10px;min-width:900px">
                @foreach($flStages as $index => $stage)
                    @php
                        $value = $stage[1];
                        $pct = $flResults > 0 ? ($value / $flResults) * 100 : 0;
                    @endphp
                    <div style="border:1px solid #e3eaf3;border-radius:13px;padding:14px;background:#fff">
                        <div style="font-size:9px;font-weight:800;text-transform:uppercase;color:#74839a">
                            {{ $stage[0] }}
                        </div>
                        <div style="font-size:25px;font-weight:800;color:#10284b;margin-top:6px">
                            {{ number_format($value) }}
                        </div>
                        <div style="font-size:10px;color:#8390a2;margin-top:7px">
                            @if($index === 0)
                                Funnel entry
                            @else
                                {{ number_format($pct,1) }}% of results
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div style="margin-top:12px;font-size:11px;color:#687b94">
                Book API Failed: <strong style="color:#b42318">{{ number_format($flFailed) }}</strong>
                &nbsp;·&nbsp; Counts use unique analytics sessions to reduce duplicate-event inflation.
            </div>
        </div>
    </div>

    <div class="wa-main">
        <div class="wa-card">
            <div class="wa-card-head">Top searched routes</div>
            <div style="overflow-x:auto">
                <table class="wa-table">
                    <thead>
                        <tr><th>Route</th><th>Searches</th></tr>
                    </thead>
                    <tbody>
                    @forelse($routes as $x)
                        <tr>
                            <td class="wa-route">{{ $x->origin }} → {{ $x->destination }}</td>
                            <td>{{ number_format($x->searches) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="wa-empty">No search data yet. Run test searches on the website.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="wa-card">
            <div class="wa-card-head">Traffic sources</div>
            <div class="wa-breakdown">
                @forelse($sources as $x)
                    @php $pct = min(100, ((int)$x->events / $sourceMax) * 100); @endphp
                    <div class="wa-bar-row">
                        <div class="wa-bar-top">
                            <span>{{ $x->source ?: 'Direct' }}</span>
                            <span>{{ number_format($x->events) }}</span>
                        </div>
                        <div class="wa-track"><div class="wa-fill" style="width:{{ $pct }}%"></div></div>
                    </div>
                @empty
                    <div class="wa-empty">No traffic-source data yet.</div>
                @endforelse
            </div>

            <div class="wa-card-head">Devices</div>
            <div class="wa-breakdown">
                @forelse($devices as $x)
                    @php $pct = min(100, ((int)$x->events / $deviceMax) * 100); @endphp
                    <div class="wa-bar-row">
                        <div class="wa-bar-top">
                            <span>{{ ucfirst($x->device ?: 'Unknown') }}</span>
                            <span>{{ number_format($x->events) }}</span>
                        </div>
                        <div class="wa-track"><div class="wa-fill" style="width:{{ $pct }}%"></div></div>
                    </div>
                @empty
                    <div class="wa-empty">No device data yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="wa-card wa-opps">
        <div class="wa-card-head">No-result route opportunities</div>
        <div style="overflow-x:auto">
            <table class="wa-table">
                <thead>
                    <tr><th>Route</th><th>No-result hits</th></tr>
                </thead>
                <tbody>
                @forelse($noResults as $x)
                    <tr>
                        <td class="wa-route">{{ $x->origin ?: '—' }} → {{ $x->destination ?: '—' }}</td>
                        <td>{{ number_format($x->hits) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="wa-empty">No zero-result routes recorded yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
