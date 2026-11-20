@php
    $legs = array_values($results['AllFlight']['Rph_2'] ?? []);
    $first = $legs[0] ?? [];
    $last = count($legs) ? $legs[count($legs)-1] : [];

    // Flying time + layovers = total journey time (pehle sirf flying time dikhta tha).
    $minutes = collect($legs)->sum(fn($f)=>(int)($f['FlightMinutes'] ?? 0));
    for ($fhI = 1; $fhI < count($legs); $fhI++) {
        $fhPrevArr = strtotime((string)($legs[$fhI-1]['Arrival_Date'] ?? ''));
        $fhNextDep = strtotime((string)($legs[$fhI]['Departure_Date'] ?? ''));
        if ($fhPrevArr && $fhNextDep && $fhNextDep > $fhPrevArr) {
            $minutes += (int) round(($fhNextDep - $fhPrevArr) / 60);
        }
    }
    $h = intdiv($minutes,60); $m = $minutes%60;

    $stops = max(0,count($legs)-1);
    $layovers = collect($legs)->slice(0,-1)->map(fn($f)=>$f['Arrival_Iata'] ?? null)->filter()->implode(', ');

    // Arrival agle din ho to "+1" (local calendar dates ka fark).
    $fhDepTs = strtotime((string)($first['Departure_Date'] ?? ''));
    $fhArrTs = strtotime((string)($last['Arrival_Date'] ?? ''));
    $fhDayShift = ($fhDepTs && $fhArrTs)
        ? (int) round((strtotime(date('Y-m-d',$fhArrTs)) - strtotime(date('Y-m-d',$fhDepTs))) / 86400)
        : 0;
    $fhDayLabel = $fhDayShift === 0 ? '' : (($fhDayShift > 0 ? '+' : '-').abs($fhDayShift));
    $fhDayTitle = $fhDayShift === 0 ? '' : ('Arrives '.abs($fhDayShift).' day'.(abs($fhDayShift) > 1 ? 's' : '').' '.($fhDayShift > 0 ? 'later' : 'earlier'));

    // Supplier cabin code -> customer label.
    $fhCabinMap = [
        'Econom'=>'Economy','econom'=>'Economy','Economy'=>'Economy',
        'PremiumEconomy'=>'Premium Economy','Peconom'=>'Premium Economy',
        'Business'=>'Business','business'=>'Business',
        'First'=>'First Class','firstclass'=>'First Class',
    ];
    $fhCabinRaw = (string)($first['FlightClass'] ?? '');
    $fhCabin = $fhCabinMap[$fhCabinRaw] ?? ($fhCabinRaw !== '' ? $fhCabinRaw : 'Economy');

    // FlightNum me airline code pehle se hota hai (jaise "TK-6"); na ho tabhi code jodo.
    $fhNum = trim((string)($first['FlightNum'] ?? ''));
    $fhCode = trim((string)($first['Airline'] ?? ''));
    $fhFlightLabel = ($fhCode !== '' && $fhNum !== '' && stripos($fhNum, $fhCode) !== 0) ? $fhCode.' '.$fhNum : $fhNum;
@endphp
@if(count($legs))
<div class="tnb-itin-row tnb-return-row">
    <div class="tnb-airline">
        {{-- 13 Nov 2026: airline code badge when the logo is missing or fails to load --}}
        @if(!empty($first['AirlineLogo']))<img src="{{ $first['AirlineLogo'] }}" alt="{{ $first['AirlineName'] ?? 'Airline' }}" onerror="this.style.display='none';if(this.nextElementSibling)this.nextElementSibling.style.display='inline-flex';"><span class="tnb-airline-badge" style="display:none">{{ strtoupper((string)($first['Airline'] ?? '')) }}</span>@else<span class="tnb-airline-badge">{{ strtoupper((string)($first['Airline'] ?? '')) }}</span>@endif
        <div><strong>{{ $first['AirlineName'] ?? 'Airline' }}</strong><span>{{ $fhFlightLabel }}</span></div>
    </div>
    <div class="tnb-time">
        <strong>{{ !empty($first['Departure_Date']) ? date('h:i A',strtotime($first['Departure_Date'])) : '—' }}</strong>
        <span>{{ $first['Departure_Iata'] ?? '—' }}</span>
    </div>
    <div class="tnb-path">
        <span>{{ $h }}h {{ $m }}m</span>
        <div class="tnb-path-line"><i class="fa-solid fa-plane"></i></div>
        <b class="{{ $stops ? 'stop' : 'direct' }}">{{ $stops ? $stops.' stop'.($stops>1?'s':'') : 'Direct' }}</b>
        @if($layovers)<small>{{ $layovers }}</small>@endif
    </div>
    <div class="tnb-time">
        <strong>{{ !empty($last['Arrival_Date']) ? date('h:i A',strtotime($last['Arrival_Date'])) : '—' }}
            @if($fhDayLabel !== '')
                <sup title="{{ $fhDayTitle }}" style="font-size:9px;font-weight:800;color:#e23662;margin-left:1px">{{ $fhDayLabel }}</sup>
            @endif
        </strong>
        <span>{{ $last['Arrival_Iata'] ?? '—' }}</span>
    </div>
    <div class="tnb-cabin"><span>Return</span><strong>{{ $fhCabin }}</strong></div>
</div>
@endif
