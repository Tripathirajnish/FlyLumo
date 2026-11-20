@extends('crm.layout')
@section('content')
@php
$maxDaily = max(1,(float)($daily->max('revenue') ?? 1));
$maxRoute = max(1,(int)($topRoutes->max('count') ?? 1));
$maxAirline = max(1,(int)($topAirlines->max('count') ?? 1));
$filterQuery = request()->except('page');
@endphp
<style>
.rp-head{display:flex;justify-content:space-between;align-items:flex-end;gap:15px;margin-bottom:14px}.rp-head h1{font-size:30px;margin:0}.rp-head p{margin:4px 0 0;color:#718099}.rp-actions{display:flex;gap:7px}.rp-filter{background:#fff;border:1px solid #e0e7ef;border-radius:16px;padding:12px;margin-bottom:12px}.rp-ranges{display:flex;gap:6px;margin-bottom:10px;flex-wrap:wrap}.rp-range{border:1px solid #dce4ed;background:#f7f9fc;color:#63748a;border-radius:9px;padding:7px 11px;text-decoration:none;font-size:9px}.rp-range.active{background:#2459df;color:#fff;border-color:#2459df}.rp-filter-grid{display:grid;grid-template-columns:repeat(8,minmax(115px,1fr));gap:7px}.rp-filter-grid .form-select,.rp-filter-grid .form-control{height:39px;font-size:9px}.rp-kpis{display:grid;grid-template-columns:2fr repeat(5,1fr);gap:9px;margin-bottom:10px}.rp-kpi{background:#fff;border:1px solid #e0e7ef;border-radius:15px;padding:15px;min-height:112px}.rp-kpi.hero{background:linear-gradient(145deg,#164795,#2f68e8);color:#fff;border:0;position:relative;overflow:hidden}.rp-kpi.hero:after{content:"";position:absolute;width:260px;height:140px;border-radius:50%;background:rgba(255,255,255,.08);right:-70px;bottom:-70px}.rp-kpi small{display:block;font-size:7px;letter-spacing:.08em;text-transform:uppercase;color:#7a899c;font-weight:900}.rp-kpi.hero small{color:#dbe6ff}.rp-kpi strong{display:block;font-size:23px;margin-top:8px}.rp-kpi.hero strong{font-size:34px}.rp-kpi span{display:block;margin-top:4px;font-size:8px;color:#7a899c}.rp-kpi.hero span{color:#dbe6ff}.rp-grid{display:grid;grid-template-columns:1.35fr .65fr;gap:10px;margin-bottom:10px}.rp-card{background:#fff;border:1px solid #e0e7ef;border-radius:16px;overflow:hidden}.rp-card header{padding:12px 15px;border-bottom:1px solid #e9eef4;display:flex;justify-content:space-between;align-items:center}.rp-card header h3{font-size:13px;margin:0}.rp-card header small{font-size:8px;color:#7b8a9d}.rp-body{padding:14px}.rp-trend{height:235px;position:relative;padding:18px 10px 28px;overflow:hidden}.rp-trend-grid{position:absolute;inset:18px 10px 28px;display:flex;flex-direction:column;justify-content:space-between;pointer-events:none}.rp-trend-grid i{display:block;border-top:1px dashed #e7edf5}.rp-trend svg{position:absolute;left:10px;right:10px;top:18px;width:calc(100% - 20px);height:calc(100% - 46px);overflow:visible}.rp-trend-line{fill:none;stroke:#2f68e8;stroke-width:3;stroke-linecap:round;stroke-linejoin:round}.rp-trend-area{fill:url(#rpTrendFill);opacity:.18}.rp-trend-point{fill:#fff;stroke:#2f68e8;stroke-width:3}.rp-trend-point-hit{fill:transparent;cursor:pointer}.rp-trend-tip{position:absolute;transform:translate(-50%,-115%);white-space:nowrap;background:#112946;color:#fff;padding:6px 8px;border-radius:7px;font-size:8px;line-height:1.25;box-shadow:0 8px 20px rgba(17,41,70,.18);opacity:0;pointer-events:none;transition:opacity .15s;z-index:5}.rp-trend-point-wrap:hover .rp-trend-tip{opacity:1}.rp-trend-single{height:235px;display:flex;align-items:center;justify-content:center;padding:20px}.rp-trend-single-card{min-width:210px;text-align:center;border:1px solid #dfe7f1;background:#f8fbff;border-radius:14px;padding:22px}.rp-trend-single-dot{width:14px;height:14px;border-radius:50%;background:#fff;border:4px solid #2f68e8;margin:0 auto 12px;box-shadow:0 0 0 6px rgba(47,104,232,.09)}.rp-trend-single strong{display:block;font-size:22px;color:#0b1f3a}.rp-trend-single span{display:block;margin-top:5px;font-size:9px;color:#718099}.rp-trend-labels{position:absolute;left:10px;right:10px;bottom:5px;display:flex;justify-content:space-between;gap:8px;font-size:7px;color:#8290a3}.rp-trend-labels span{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.rp-list{display:grid;gap:11px}.rp-row{display:grid;grid-template-columns:minmax(80px,125px) 1fr 55px;gap:8px;align-items:center;font-size:9px}.rp-row b{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.rp-bar{height:7px;background:#eef2f6;border-radius:999px;overflow:hidden}.rp-bar span{display:block;height:100%;background:#2f68e8;border-radius:999px}.rp-row em{font-style:normal;text-align:right;color:#66778d}.rp-quad{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-bottom:10px}.rp-table{min-width:920px}.rp-table th{font-size:7.5px!important;text-transform:uppercase}.rp-table td{font-size:8.8px!important}.rp-pill{display:inline-flex;padding:5px 7px;border-radius:999px;background:#eef2f6;font-size:7.5px;font-weight:800}.rp-pill.blue{background:#eaf2ff;color:#2459df}.rp-pill.green{background:#e8f8ef;color:#16814f}.rp-pill.amber{background:#fff3db;color:#9b670d}.rp-funnel{display:grid;grid-template-columns:repeat(7,1fr);gap:6px}.rp-funnel-item{padding:11px;border-radius:11px;background:#f6f8fb;border:1px solid #e7ecf2;text-align:center}.rp-funnel-item strong{font-size:20px;display:block}.rp-funnel-item small{font-size:7px;color:#74859a;text-transform:uppercase}.rp-note{font-size:8px;color:#7d8ca0;margin-top:8px}.rp-empty{padding:28px;text-align:center;color:#8290a3}.rp-print-only{display:none}@media(max-width:1350px){.rp-filter-grid{grid-template-columns:repeat(4,1fr)}.rp-kpis{grid-template-columns:repeat(3,1fr)}.rp-kpi.hero{grid-column:span 2}.rp-funnel{grid-template-columns:repeat(4,1fr)}}@media(max-width:900px){.rp-grid,.rp-quad{grid-template-columns:1fr}.rp-kpis{grid-template-columns:1fr 1fr}.rp-kpi.hero{grid-column:1/-1}}@media(max-width:620px){.rp-filter-grid,.rp-kpis,.rp-funnel{grid-template-columns:1fr}.rp-kpi.hero{grid-column:auto}.rp-head{flex-direction:column;align-items:stretch}}@media print{
@page{size:A4 portrait;margin:8mm}
html,body{background:#fff!important}
*,*::before,*::after{-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important}
.crm-sidebar,.crm-top,.rp-filter,.rp-actions{display:none!important}
.crm-main{margin:0!important;padding:0!important;width:100%!important;max-width:none!important}
.crm-content{padding:0!important;margin:0!important;width:100%!important;max-width:none!important}
.rp-print-only{display:block!important;font-size:9px!important;color:#53657d!important;margin:0 0 4mm!important}
.rp-head{margin:0 0 4mm!important;align-items:flex-start!important}
.rp-head h1{font-size:25px!important;color:#0b1f3a!important}.rp-head p{font-size:9px!important;max-width:165mm!important}
.rp-pill.blue{background:#eaf2ff!important;color:#2459df!important}
.rp-kpis{grid-template-columns:2fr repeat(5,1fr)!important;gap:2.5mm!important;margin-bottom:3mm!important}
.rp-kpi{min-height:24mm!important;padding:3mm!important;border-color:#dce5ef!important;background:#fff!important;break-inside:avoid!important}
.rp-kpi.hero{background:linear-gradient(145deg,#164795,#2f68e8)!important;color:#fff!important}
.rp-kpi.hero small,.rp-kpi.hero span{color:#dbe6ff!important}.rp-kpi.hero strong{color:#fff!important;font-size:25px!important}
.rp-kpi strong{font-size:18px!important}.rp-kpi small{font-size:6px!important}.rp-kpi span{font-size:6.5px!important}
.rp-grid{grid-template-columns:1.35fr .65fr!important;gap:3mm!important;margin-bottom:3mm!important}
.rp-quad{grid-template-columns:1fr 1fr!important;gap:3mm!important;margin-bottom:3mm!important}
.rp-card{box-shadow:none!important;border-color:#dce5ef!important;border-radius:10px!important;break-inside:avoid!important;overflow:hidden!important}
.rp-card header{padding:2.5mm 3mm!important}.rp-card header h3{font-size:10px!important}.rp-card header small{font-size:6.5px!important}
.rp-body{padding:3mm!important}.rp-trend,.rp-trend-single{height:42mm!important;overflow:hidden!important}
.rp-trend-single-card{padding:4mm!important}.rp-trend-area{opacity:.12!important}.rp-trend-line{stroke:#2f68e8!important}.rp-trend-tip{display:none!important}
.rp-bar{background:#eef2f6!important}.rp-bar span{background:#2f68e8!important}.rp-row{font-size:7.5px!important;grid-template-columns:30mm 1fr 12mm!important;gap:2mm!important}
.rp-funnel{grid-template-columns:repeat(7,1fr)!important;gap:2mm!important}.rp-funnel-item{padding:2.5mm 1mm!important;background:#f6f8fb!important}.rp-funnel-item strong{font-size:16px!important}.rp-funnel-item small{font-size:5.5px!important}
.rp-note{font-size:6.5px!important}
.table-responsive{overflow:visible!important;width:100%!important}
.rp-table{min-width:0!important;width:100%!important;table-layout:fixed!important;margin:0!important}
.rp-table th,.rp-table td{white-space:normal!important;overflow-wrap:anywhere!important;word-break:normal!important;padding:2.2mm 2.5mm!important;vertical-align:middle!important}
.rp-table th{font-size:6px!important}.rp-table td{font-size:7px!important}
.rp-pill{padding:1.2mm 2mm!important;font-size:6px!important}
.rp-pill.green{background:#e8f8ef!important;color:#16814f!important}.rp-pill.amber{background:#fff3db!important;color:#9b670d!important}
section.rp-card{page-break-inside:avoid!important}
}

</style>

<div class="rp-head">
 <div><span class="rp-pill blue">Operations intelligence</span><h1>Reports & Analytics</h1><p>Bookings, revenue, source, route, airline, payment, fare-rule and lead-funnel reporting from the existing CRM database.</p></div>
 <div class="rp-actions">
  <a class="btn btn-light" href="{{ route('crm.reports_export',$filterQuery) }}"><i class="fa-solid fa-file-csv me-1"></i>Export CSV</a>
  <button class="btn btn-light" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>Print / PDF</button>
 </div>
</div>
<div class="rp-print-only small mb-2">Report period: {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}</div>

<form class="rp-filter" method="GET">
 <div class="rp-ranges">
  @foreach(['24h'=>'Last 24h','7d'=>'Last 7 days','30d'=>'Last 30 days','custom'=>'Custom'] as $k=>$label)
   <a class="rp-range {{ $preset===$k?'active':'' }}" href="{{ route('crm.reports',array_merge(request()->except(['range','date_from','date_to']),['range'=>$k])) }}">{{ $label }}</a>
  @endforeach
 </div>
 <input type="hidden" name="range" value="{{ $preset }}">
 <div class="rp-filter-grid">
  <select class="form-select" name="source"><option value="">All sources</option>@foreach($sources as $s)<option value="{{ $s }}" @selected(request('source')===$s)>{{ $s }}</option>@endforeach</select>
  <select class="form-select" name="booking_status"><option value="">All booking status</option>@foreach($bookingStatuses as $s)<option value="{{ $s }}" @selected(request('booking_status')===$s)>{{ $s }}</option>@endforeach</select>
  <select class="form-select" name="payment_status"><option value="">All payment status</option>@foreach($paymentStatuses as $s)<option value="{{ $s }}" @selected(request('payment_status')===$s)>{{ $s }}</option>@endforeach</select>
  <select class="form-select" name="ticket_status"><option value="">All ticket status</option>@foreach($ticketStatuses as $s)<option value="{{ $s }}" @selected(request('ticket_status')===$s)>{{ $s }}</option>@endforeach</select>
  <input class="form-control" name="airline" value="{{ request('airline') }}" placeholder="Airline code e.g. UA">
  <input class="form-control" name="route" value="{{ request('route') }}" placeholder="Route e.g. EWR-LAX">
  <select class="form-select" name="agent"><option value="">All agents</option>@foreach($adminOptions as $a)<option value="{{ $a->id }}" @selected((string)request('agent')===(string)$a->id)>{{ $a->name }}</option>@endforeach</select>
  <div class="d-flex gap-1"><button class="btn btn-primary flex-fill">Apply</button><a class="btn btn-light" href="{{ route('crm.reports') }}">Reset</a></div>
  @if($preset==='custom')
  <input class="form-control" type="date" name="date_from" value="{{ request('date_from',$from->format('Y-m-d')) }}">
  <input class="form-control" type="date" name="date_to" value="{{ request('date_to',$to->format('Y-m-d')) }}">
  @endif
 </div>
</form>

<div class="rp-kpis">
 <div class="rp-kpi hero"><small>Gross booking value</small><strong>${{ number_format($kpis['gross'],2) }}</strong><span>{{ $kpis['bookings'] }} bookings · ${{ number_format($kpis['average'],2) }} average ticket</span></div>
 <div class="rp-kpi"><small>Total bookings</small><strong>{{ $kpis['bookings'] }}</strong><span>Filtered records</span></div>
 <div class="rp-kpi"><small>Paid</small><strong>{{ $kpis['paid'] }}</strong><span>${{ number_format($kpis['collected'],2) }} tracked collected</span></div>
 <div class="rp-kpi"><small>Ticketed</small><strong>{{ $kpis['ticketed'] }}</strong><span>Issued/ticketed status</span></div>
 <div class="rp-kpi"><small>Payment issues</small><strong>{{ $kpis['payment_issues'] }}</strong><span>Failed / declined / chargeback</span></div>
 <div class="rp-kpi"><small>Refunds</small><strong>{{ $kpis['refunds'] }}</strong><span>${{ number_format($kpis['refund_amount'],2) }} tracked refunds</span></div>
</div>

<div class="rp-grid">
 <section class="rp-card"><header><div><h3>Booking value trend</h3><small>Daily gross booking value within selected range</small></div><b>${{ number_format($kpis['gross'],2) }}</b></header><div class="rp-body">
  @if($daily->count() === 1)
    @php $onlyDay = $daily->first(); @endphp
    <div class="rp-trend-single">
      <div class="rp-trend-single-card">
        <div class="rp-trend-single-dot"></div>
        <strong>${{ number_format($onlyDay['revenue'],2) }}</strong>
        <span>{{ $onlyDay['label'] }} · {{ $onlyDay['bookings'] }} booking{{ $onlyDay['bookings']==1?'':'s' }}</span>
      </div>
    </div>
  @elseif($daily->count() > 1)
    @php
      $trendItems = $daily->values();
      $trendCount = $trendItems->count();
      $trendPoints = [];
      foreach($trendItems as $i => $d){
          $x = $trendCount > 1 ? (($i / ($trendCount - 1)) * 100) : 50;
          $y = 92 - (((float)$d['revenue'] / $maxDaily) * 76);
          $trendPoints[] = [
              'x' => round($x,3),
              'y' => round($y,3),
              'label' => $d['label'],
              'revenue' => (float)$d['revenue'],
              'bookings' => (int)$d['bookings'],
          ];
      }
      $linePoints = collect($trendPoints)->map(fn($p) => $p['x'].','.$p['y'])->implode(' ');
      $areaPoints = '0,100 '.$linePoints.' 100,100';
    @endphp
    <div class="rp-trend">
      <div class="rp-trend-grid"><i></i><i></i><i></i><i></i></div>
      <svg viewBox="0 0 100 100" preserveAspectRatio="none" aria-label="Booking value trend">
        <defs>
          <linearGradient id="rpTrendFill" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#2f68e8" stop-opacity=".65"/>
            <stop offset="100%" stop-color="#2f68e8" stop-opacity=".04"/>
          </linearGradient>
        </defs>
        <polygon class="rp-trend-area" points="{{ $areaPoints }}"></polygon>
        <polyline class="rp-trend-line" points="{{ $linePoints }}"></polyline>
        @foreach($trendPoints as $p)
          <circle class="rp-trend-point" cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="1.35" vector-effect="non-scaling-stroke"></circle>
        @endforeach
      </svg>
      @foreach($trendPoints as $p)
        <div class="rp-trend-point-wrap" style="position:absolute;left:calc(10px + (100% - 20px) * {{ $p['x']/100 }});top:calc(18px + (100% - 46px) * {{ $p['y']/100 }});">
          <span class="rp-trend-tip">{{ $p['label'] }} · ${{ number_format($p['revenue'],2) }} · {{ $p['bookings'] }} booking{{ $p['bookings']==1?'':'s' }}</span>
          <span class="rp-trend-point-hit" style="position:absolute;width:24px;height:24px;left:-12px;top:-12px;border-radius:50%;"></span>
        </div>
      @endforeach
      <div class="rp-trend-labels">
        <span>{{ $trendItems->first()['label'] }}</span>
        <span>{{ $trendItems->last()['label'] }}</span>
      </div>
    </div>
  @else
    <div class="rp-empty">No bookings in this period.</div>
  @endif
 </div></section>
 <section class="rp-card"><header><div><h3>Sales channel</h3><small>Website vs FareLeader</small></div></header><div class="rp-body rp-list">
 @forelse($bySource as $r)<div class="rp-row"><b>{{ $r['label'] }}</b><div class="rp-bar"><span style="width:{{ $kpis['bookings']?($r['count']/$kpis['bookings']*100):0 }}%"></span></div><em>{{ $r['count'] }}</em></div>@empty<div class="rp-empty">No data</div>@endforelse
 </div></section>
</div>

<div class="rp-quad">
 <section class="rp-card"><header><div><h3>Top routes</h3><small>Bookings and value</small></div></header><div class="rp-body rp-list">@forelse($topRoutes as $r)<div class="rp-row"><b title="{{ $r['label'] }}">{{ $r['label'] }}</b><div class="rp-bar"><span style="width:{{ ($r['count']/$maxRoute)*100 }}%"></span></div><em>{{ $r['count'] }}</em></div>@empty<div class="rp-empty">No route data</div>@endforelse</div></section>
 <section class="rp-card"><header><div><h3>Top airlines</h3><small>Marketing airline from booked itinerary</small></div></header><div class="rp-body rp-list">@forelse($topAirlines as $r)<div class="rp-row"><b>{{ $r['label'] }}</b><div class="rp-bar"><span style="width:{{ ($r['count']/$maxAirline)*100 }}%"></span></div><em>{{ $r['count'] }}</em></div>@empty<div class="rp-empty">No airline data</div>@endforelse</div></section>
</div>

<section class="rp-card mb-3"><header><div><h3>Operational funnel</h3><small>Enquiries → qualified → quoted → converted → bookings → paid → ticketed</small></div></header><div class="rp-body">
 <div class="rp-funnel">@foreach($funnel as $f)<div class="rp-funnel-item"><small>{{ $f['label'] }}</small><strong>{{ $f['value'] }}</strong></div>@endforeach</div>
 <div class="rp-note">Funnel stages are operational counts inside the selected date period. Enquiries and bookings are not cohort-linked customer-by-customer, so this is an operational funnel rather than an attribution model.</div>
</div></section>

<div class="rp-quad">
 <section class="rp-card"><header><div><h3>Payment operations</h3><small>Current CRM payment status on filtered bookings</small></div><b>Net ${{ number_format($kpis['net_collected'],2) }}</b></header><div class="table-responsive"><table class="table rp-table mb-0"><thead><tr><th>Status</th><th>Bookings</th><th>Booking Value</th><th>Avg Ticket</th></tr></thead><tbody>@forelse($byPayment as $r)<tr><td><span class="rp-pill {{ $r['label']==='Paid'?'green':(in_array($r['label'],['Failed','Declined','Chargeback'])?'amber':'') }}">{{ $r['label'] }}</span></td><td>{{ $r['count'] }}</td><td>${{ number_format($r['revenue'],2) }}</td><td>${{ number_format($r['avg'],2) }}</td></tr>@empty<tr><td colspan="4" class="rp-empty">No payment data</td></tr>@endforelse</tbody></table></div></section>
 <section class="rp-card"><header><div><h3>Fare-rule impact</h3><small>Recorded markup rule metadata from booked fare payloads</small></div><b>${{ number_format($kpis['fare_reduction'],2) }}</b></header><div class="rp-body rp-list">@forelse($fareRules as $r)<div class="rp-row"><b title="{{ $r['label'] }}">{{ $r['label'] }}</b><div class="rp-bar"><span style="width:{{ $kpis['fare_rule_bookings']?($r['count']/$kpis['fare_rule_bookings']*100):0 }}%"></span></div><em>{{ $r['count'] }}</em></div>@empty<div class="rp-empty">No fare-rule metadata recorded for these bookings.</div>@endforelse<div class="rp-note">{{ $kpis['fare_rule_bookings'] }} booking(s) contain recorded fare-rule metadata. “Fare reduction” uses the booking payload's recorded MarkupDiscount field.</div></div></section>
</div>

<div class="rp-quad">
 <section class="rp-card"><header><div><h3>Booking status</h3><small>Lifecycle distribution</small></div></header><div class="table-responsive"><table class="table rp-table mb-0"><thead><tr><th>Status</th><th>Bookings</th><th>Booking Value</th></tr></thead><tbody>@forelse($byStatus as $r)<tr><td>{{ $r['label'] }}</td><td>{{ $r['count'] }}</td><td>${{ number_format($r['revenue'],2) }}</td></tr>@empty<tr><td colspan="3" class="rp-empty">No data</td></tr>@endforelse</tbody></table></div></section>
 <section class="rp-card"><header><div><h3>Agent performance</h3><small>Based on bookings.assign_to</small></div></header><div class="table-responsive"><table class="table rp-table mb-0"><thead><tr><th>Agent</th><th>Bookings</th><th>Booking Value</th><th>Avg Ticket</th></tr></thead><tbody>@forelse($agents as $r)<tr><td>{{ $r['label'] }}</td><td>{{ $r['count'] }}</td><td>${{ number_format($r['revenue'],2) }}</td><td>${{ number_format($r['avg'],2) }}</td></tr>@empty<tr><td colspan="4" class="rp-empty">No agent assignments in this period.</td></tr>@endforelse</tbody></table></div></section>
</div>

<section class="rp-card"><header><div><h3>Monthly summary</h3><small>Bookings, gross value and average ticket</small></div></header><div class="table-responsive"><table class="table rp-table mb-0"><thead><tr><th>Month</th><th>Bookings</th><th>Gross Booking Value</th><th>Average Ticket</th></tr></thead><tbody>@forelse($monthly as $r)<tr><td><b>{{ $r['label'] }}</b></td><td>{{ $r['bookings'] }}</td><td>${{ number_format($r['revenue'],2) }}</td><td>${{ number_format($r['avg'],2) }}</td></tr>@empty<tr><td colspan="4" class="rp-empty">No monthly data.</td></tr>@endforelse</tbody></table></div></section>
@endsection
