@php
    $allFlight = (array)($results['AllFlight'] ?? []);
    uksort($allFlight, fn($a,$b)=>(int)str_replace('Rph_','',$a) <=> (int)str_replace('Rph_','',$b));
    $searchLegs = (array)($fields['SearchLegs'] ?? []);
@endphp
@foreach($allFlight as $rphKey => $segments)
    @continue(empty($segments))
    @php
        $legNo=(int)str_replace('Rph_','',$rphKey);
        $legMeta=$searchLegs[$legNo-1]??null;
        $previousValue='';
        $heading = ($fields['FlightType']??'')==='multi_city' ? 'Flight '.$legNo : ($legNo===1?'Departure':'Return');
    @endphp
    <div class="segmentDeparture-wrap {{ $legNo>1?'mt-3 border-top pt-3':'' }}">
        <div class="segmentDeparture-head mb-3 mt-2"><h6 class="m-0">{{ $heading }} @if($legMeta) · {{ $legMeta['IATAFrom'] }} → {{ $legMeta['IATATo'] }} @endif</h6></div>
        <div class="col-xl-12 col-lg12 col-md-12"><div class="flights-accordion">
        @foreach($segments as $results_key=>$results_flight)
            @if($results_key!='0' && $previousValue)
                @php
                    $date1=new DateTime($previousValue);$date2=new DateTime($results_flight['Departure_Date']);$interval=$date1->diff($date2);
                    $overlay=$interval->d>0?$interval->format('%d days, %h hr, %i min '):($interval->h>0?$interval->format('%h hr, %i min '):$interval->format('%i min '));
                    $overlay.='layover in '.($results_flight['Departure_Name']??'').' ('.($results_flight['Departure_Iata']??'').')';
                @endphp
                <div class="segmentDeparture-overlay"><span style="background:#fff7d2;padding:5px 10px"><i class="fa-regular fa-clock me-1"></i>{{ $overlay }}</span></div>
            @endif
            @php $previousValue=$results_flight['Arrival_Date']??''; @endphp
            <div class="row gx-lg-5 gx-3 gy-4 align-items-center">
                <div class="col-sm-auto"><div class="d-flex align-items-center"><div class="d-start fl-pic"><img class="img-fluid" src="{{ $results_flight['AirlineLogo']??'' }}" width="45" alt="airline"></div><div class="d-end fl-title ps-2"><div class="text-dark fw-medium">{{ $results_flight['AirlineName']??'' }}</div><div class="text-sm">Flight {{ $results_flight['FlightNum']??'' }}</div><div class="text-sm">{{ $results_flight['FlightClass']??'' }}</div></div></div></div>
                <div class="col"><div class="row gx-3 align-items-center"><div class="col-auto"><div>{{ $results_flight['Departure_Name']??'' }} ({{ $results_flight['Departure_Iata']??'' }})</div><div class="text-dark fw-bold">{{ @date('h:i A',strtotime($results_flight['Departure_Date']??'')) }}</div><div>{{ @date('D, M d',strtotime($results_flight['Departure_Date']??'')) }}</div></div><div class="col text-center"><div class="flightLine departure"><div></div><div></div></div><div class="text-sm mt-3">@php $mins=(int)($results_flight['FlightMinutes']??0); echo 'Flight Duration: '.floor($mins/60).'h '.($mins%60).'m'; @endphp</div></div><div class="col-auto"><div>{{ $results_flight['Arrival_Name']??'' }} ({{ $results_flight['Arrival_Iata']??'' }})</div><div class="text-dark fw-bold">{{ @date('h:i A',strtotime($results_flight['Arrival_Date']??'')) }}</div><div>{{ @date('D, M d',strtotime($results_flight['Arrival_Date']??'')) }}</div></div></div></div>
            </div>
        @endforeach
        </div></div>
    </div>
@endforeach
