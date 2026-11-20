                                @php
                                    $sortPrice = $resolveDisplayedFare($results);
                                    $sortMinutes = collect($results['AllFlight'] ?? [])->flatten(1)->sum(fn($f)=>(int)($f['FlightMinutes'] ?? 0));
                                @endphp
                                <div class="col-xl-12 col-lg12 col-md-12 result-step 
                                price-{{@$results['priceInfo']['Adult']['Price']}} 
                                dptstop-{{@$results['DptFlightStop']}} rtnstop-{{@$results['RtnFlightStop']}} 
                                dpttime-{{@$results['FtrDptTime']}} rtntime-{{@$results['FtrRtnTime']}} 
                                dptairline-{{@$results['AllFlight']['Rph_1'][0]['Airline']}} rtnairline-{{@$results['AllFlight']['Rph_2'][0]['Airline']}} 
                                dptairport-{{@$results['AllFlight']['Rph_1'][0]['Departure_Iata']}} rtnairport-{{@$results['AllFlight']['Rph_2'][0]['Departure_Iata']}}"
                                data-price="{{ $sortPrice }}"
                                data-duration="{{ $sortMinutes }}"
                                data-airline="{{ strtoupper((string)($results['AllFlight']['Rph_1'][0]['Airline'] ?? '')) }}"
                                data-stops="{{ (int)($results['DptFlightStop'] ?? 0) }}"
                                data-return-stops="{{ (int)($results['RtnFlightStop'] ?? 0) }}"
                                data-cabin="{{ strtolower((string)($results['CabinClass'] ?? $results['FlightClass'] ?? $fields['FlightClass'] ?? '')) }}"
                                data-refundable="{{ in_array(strtolower((string)($results['Refundable'] ?? $results['IsRefundable'] ?? '')), ['yes','true','1','refundable'], true) ? '1' : '0' }}"
                                data-baggage="{{ (!empty($results['Baggage']) || !empty($results['BaggageInfo']) || !empty($results['BaggageAllowance'])) ? '1' : '0' }}"
                                data-alliance="{{ strtolower((string)($results['Alliance'] ?? $results['AirlineAlliance'] ?? '')) }}">

                                    <div class="flights-accordion">
                                        <div class="flights-list-item bg-white rounded-3 p-3">

                                            
                                            @php
                                                $mR1=(array)($results['AllFlight']['Rph_1']??[]);
                                                $mR2=(array)($results['AllFlight']['Rph_2']??[]);
                                                $mF1=$mR1[0]??null; $mL1=!empty($mR1)?$mR1[count($mR1)-1]:null;
                                                $mF2=$mR2[0]??null; $mL2=!empty($mR2)?$mR2[count($mR2)-1]:null;
                                                $mAirline=$mF1['AirlineName']??$mF1['Airline']??'Airline';
                                                $mAirlineCode=$mF1['Airline']??'';
                                                $mFlightNo=$mF1['FlightNum']??$mF1['FlightNumber']??'';
                                                $mLogo=$mF1['AirlineLogo']??$mF1['Logo']??'';
                                                $mOutFrom=$mF1['Departure_Iata']??($fields['IATAFrom']??'');
                                                $mOutTo=$mL1['Arrival_Iata']??($fields['IATATo']??'');
                                                $mOutDep=$mF1['Departure_Date']??'';
                                                $mOutArr=$mL1['Arrival_Date']??'';
                                                $mOutStops=max(0,count($mR1)-1);
                                                $mRetFrom=$mF2['Departure_Iata']??($fields['IATATo']??'');
                                                $mRetTo=$mL2['Arrival_Iata']??($fields['IATAFrom']??'');
                                                $mRetDep=$mF2['Departure_Date']??'';
                                                $mRetArr=$mL2['Arrival_Date']??'';
                                                $mRetStops=max(0,count($mR2)-1);
                                            @endphp

                                            <div class="tnb-mobile-flight-summary">
                                                @if(($fields['FlightType']??'')==='multi_city')
                                                    @php
                                                        $mLegs=$results['AllFlight']??[];
                                                        uksort($mLegs,function($a,$b){return (int)str_replace('Rph_','',$a)<=>(int)str_replace('Rph_','',$b);});
                                                    @endphp
                                                    @foreach($mLegs as $mRph=>$mSegments)
                                                        @php
                                                            $mNo=(int)str_replace('Rph_','',$mRph);
                                                            $mSegments=(array)$mSegments;
                                                            $mF=$mSegments[0]??null;
                                                            $mL=!empty($mSegments)?$mSegments[count($mSegments)-1]:null;
                                                            $mSearch=(array)($fields['SearchLegs'][$mNo-1]??[]);
                                                        @endphp
                                                        @if($mF && $mL)
                                                            <div class="tnb-mobile-leg">
                                                                <div class="tnb-mobile-leg-head"><span>Flight {{ $mNo }}</span><strong>{{ $mSearch['IATAFrom']??($mF['Departure_Iata']??'') }} → {{ $mSearch['IATATo']??($mL['Arrival_Iata']??'') }}</strong></div>
                                                                <div class="tnb-mobile-leg-times">
                                                                    <div><b>{{ !empty($mF['Departure_Date'])?@date('h:i A',strtotime($mF['Departure_Date'])):'—' }}</b><small>{{ $mF['Departure_Iata']??'' }}</small></div>
                                                                    <div class="tnb-mobile-leg-path"><i class="fa-solid fa-plane"></i><small>{{ max(0,count($mSegments)-1)===0?'Nonstop':max(0,count($mSegments)-1).' stop(s)' }}</small></div>
                                                                    <div class="text-end"><b>{{ !empty($mL['Arrival_Date'])?@date('h:i A',strtotime($mL['Arrival_Date'])):'—' }}</b><small>{{ $mL['Arrival_Iata']??'' }}</small></div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    <div class="tnb-mobile-airline">
                                                        @if($mLogo)<img src="{{ $mLogo }}" alt="{{ $mAirline }}">@else<span>{{ strtoupper(substr((string)$mAirlineCode,0,2)) }}</span>@endif
                                                        <div><strong>{{ $mAirline }}</strong><small>{{ ($mFlightNo !== '' && $mAirlineCode !== '' && stripos((string)$mFlightNo, (string)$mAirlineCode) === 0) ? $mFlightNo : trim($mAirlineCode.' '.$mFlightNo) }}</small></div>
                                                    </div>

                                                    <div class="tnb-mobile-leg">
                                                        <div class="tnb-mobile-leg-head"><span>Depart</span><strong>{{ $mOutFrom }} → {{ $mOutTo }}</strong></div>
                                                        <div class="tnb-mobile-leg-times">
                                                            <div><b>{{ $mOutDep?@date('h:i A',strtotime($mOutDep)):'—' }}</b><small>{{ $mOutFrom }}</small></div>
                                                            <div class="tnb-mobile-leg-path"><i class="fa-solid fa-plane"></i><small>{{ $mOutStops===0?'Nonstop':$mOutStops.' stop(s)' }}</small></div>
                                                            <div class="text-end"><b>{{ $mOutArr?@date('h:i A',strtotime($mOutArr)):'—' }}</b><small>{{ $mOutTo }}</small></div>
                                                        </div>
                                                    </div>

                                                    @if(($fields['FlightType']??'')==='round_trip' && $mF2 && $mL2)
                                                        <div class="tnb-mobile-leg">
                                                            <div class="tnb-mobile-leg-head"><span>Return</span><strong>{{ $mRetFrom }} → {{ $mRetTo }}</strong></div>
                                                            <div class="tnb-mobile-leg-times">
                                                                <div><b>{{ $mRetDep?@date('h:i A',strtotime($mRetDep)):'—' }}</b><small>{{ $mRetFrom }}</small></div>
                                                                <div class="tnb-mobile-leg-path"><i class="fa-solid fa-plane"></i><small>{{ $mRetStops===0?'Nonstop':$mRetStops.' stop(s)' }}</small></div>
                                                                <div class="text-end"><b>{{ $mRetArr?@date('h:i A',strtotime($mRetArr)):'—' }}</b><small>{{ $mRetTo }}</small></div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif

                                                @php $searchguid = $output['Search-SearchGuid']; @endphp
                                                <div class="tnb-mobile-price-wrap">
                                                    <x-flight-list-price :$key :$code :$searchguid :$results />
                                                </div>
                                            </div>

                                            <div class="row gy-4 align-items-center justify-content-between tnb-desktop-flight-summary">
                                                
                                                <div class="col">
                                                    @if(($fields['FlightType']??'') === 'multi_city')
                                                        @php
                                                            $multiCompactLegs = $results['AllFlight'] ?? [];
                                                            uksort($multiCompactLegs, function ($a, $b) {
                                                                return (int)str_replace('Rph_', '', $a) <=> (int)str_replace('Rph_', '', $b);
                                                            });
                                                        @endphp

                                                        <div class="tnb-multicity-card-legs">
                                                            @foreach($multiCompactLegs as $multiRph=>$multiSegments)
                                                                @php
                                                                    $multiNo = (int)str_replace('Rph_','',$multiRph);
                                                                    $mf = $multiSegments[0] ?? null;
                                                                    $ml = $multiSegments ? end($multiSegments) : null;
                                                                    $requestedLeg = (array)($fields['SearchLegs'][$multiNo-1] ?? []);
                                                                    $requestedFrom = $requestedLeg['IATAFrom'] ?? ($mf['Departure_Iata'] ?? '');
                                                                    $requestedTo = $requestedLeg['IATATo'] ?? ($ml['Arrival_Iata'] ?? '');
                                                                    $multiStops = max(0, count($multiSegments)-1);
                                                                    $multiAirlineName = $mf['AirlineName'] ?? $mf['MarketingAirlineName'] ?? $mf['Airline'] ?? 'Airline';
                                                                    $multiAirlineCode = $mf['Airline'] ?? $mf['MarketingAirline'] ?? '';
                                                                    $multiFlightNo = $mf['FlightNumber'] ?? $mf['FlightNum'] ?? $mf['FlightNo'] ?? '';
                                                                    $multiLogo = $mf['AirlineLogo'] ?? $mf['Logo'] ?? '';
                                                                    $multiDep = $mf['Departure_Date'] ?? '';
                                                                    $multiArr = $ml['Arrival_Date'] ?? '';
                                                                    $multiMinutes = collect($multiSegments)->sum(fn($seg)=>(int)($seg['FlightMinutes'] ?? 0));
                                                                    $multiHours = intdiv($multiMinutes,60);
                                                                    $multiMins = $multiMinutes % 60;
                                                                    $multiDuration = $multiMinutes > 0 ? trim(($multiHours ? $multiHours.'h ' : '').($multiMins ? $multiMins.'m' : '')) : '';
                                                                @endphp

                                                                @if($mf && $ml)
                                                                    <div class="tnb-multicity-card-leg">
                                                                        <div class="tnb-mc-leg-meta">
                                                                            <span class="tnb-mc-leg-pill">Flight {{ $multiNo }}</span>
                                                                            <strong>{{ $requestedFrom }} → {{ $requestedTo }}</strong>
                                                                        </div>

                                                                        <div class="tnb-mc-leg-main">
                                                                            <div class="tnb-mc-airline">
                                                                                @if($multiLogo)
                                                                                    <img src="{{ $multiLogo }}" alt="{{ $multiAirlineName }}" loading="lazy">
                                                                                @else
                                                                                    <div class="tnb-mc-airline-fallback">
                                                                                        {{ strtoupper(substr((string)$multiAirlineCode,0,2)) }}
                                                                                    </div>
                                                                                @endif
                                                                                <div>
                                                                                    <strong>{{ $multiAirlineName }}</strong>
                                                                                    @if($multiAirlineCode || $multiFlightNo)
                                                                                        <small>{{ ($multiFlightNo !== '' && $multiAirlineCode !== '' && stripos((string)$multiFlightNo, (string)$multiAirlineCode) === 0) ? $multiFlightNo : trim($multiAirlineCode.' '.$multiFlightNo) }}</small>
                                                                                    @endif
                                                                                </div>
                                                                            </div>

                                                                            <div class="tnb-mc-time">
                                                                                <strong>{{ $multiDep ? date('h:i A',strtotime($multiDep)) : '—' }}</strong>
                                                                                <small>{{ $requestedFrom }}</small>
                                                                            </div>

                                                                            <div class="tnb-mc-route-line">
                                                                                @if($multiDuration)<span>{{ $multiDuration }}</span>@endif
                                                                                <i class="fa-solid fa-plane"></i>
                                                                                <em>{{ $multiStops === 0 ? 'Nonstop' : $multiStops.' stop'.($multiStops>1?'s':'') }}</em>
                                                                            </div>

                                                                            <div class="tnb-mc-time tnb-mc-arrival">
                                                                                <strong>{{ $multiArr ? date('h:i A',strtotime($multiArr)) : '—' }}</strong>
                                                                                <small>{{ $requestedTo }}</small>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <x-flight-list-departure :$results />
                                                        @if($fields['FlightType']=='round_trip')
                                                            <x-flight-list-return :$results />
                                                        @endif
                                                    @endif
                                                </div>

                                                @php $searchguid = $output['Search-SearchGuid']; @endphp
                                                <x-flight-list-price :$key :$code :$searchguid :$results />

                                            </div>

                                            @php
                                                // 13 Nov 2026: tell the customer when the flight uses another airport than the one searched.
                                                // Metro codes (NYC, LON, ...) cover several airports, so they are not compared.
                                                $apNotes = [];
                                                if (($fields['FlightType'] ?? '') !== 'multi_city') {
                                                    $apMetro = ['NYC','WAS','CHI','LON','PAR','TYO','OSA','MIL','ROM','STO','BJS','SHA','SEL','SAO','RIO','BUE','YTO','YMQ','YEA','MOW','DTT','QDF','QHO','QLA','QSF','BER','BRU','JKT','BKK','DXB','REK','OSL','HOU','ORL','MIA'];
                                                    $apSearchFrom = strtoupper((string)($fields['IATAFrom'] ?? ''));
                                                    $apSearchTo = strtoupper((string)($fields['IATATo'] ?? ''));
                                                    $apName = function ($seg, $side) { $c = strtoupper((string)($seg[$side.'_Iata'] ?? '')); $n = trim((string)($seg[$side.'_Name'] ?? '')); return $n !== '' ? $n.' ('.$c.')' : $c; };
                                                    $apCheck = function ($seg, $side, $searched, $label) use (&$apNotes, $apMetro, $apName) {
                                                        $c = strtoupper((string)($seg[$side.'_Iata'] ?? ''));
                                                        if ($seg && $c !== '' && $searched !== '' && !in_array($searched, $apMetro, true) && $c !== $searched) {
                                                            $apNotes[] = $label.' '.$apName($seg, $side).', not '.$searched;
                                                        }
                                                    };
                                                    $apCheck($mF1, 'Departure', $apSearchFrom, 'Departs from');
                                                    $apCheck($mL1, 'Arrival', $apSearchTo, 'Arrives at');
                                                    if (($fields['FlightType'] ?? '') === 'round_trip') {
                                                        $apCheck($mF2, 'Departure', $apSearchTo, 'Return departs from');
                                                        $apCheck($mL2, 'Arrival', $apSearchFrom, 'Return arrives at');
                                                    }
                                                }
                                            @endphp
                                            @if($apNotes)
                                                <div class="tnb-airport-notice"><i class="fa-solid fa-circle-info"></i><span>Different airport: {{ implode(' · ', $apNotes) }}.</span></div>
                                            @endif

                                            <div class="row mt-3">
                                                <div class="col-12 border-top pt-3">
                                                    <div class="accordion accordion-flush" id="accordionFlushExample-{{$key}}">
                                                        <div class="accordion-item mb-0">

                                                            <h2 class="accordion-header rounded-2">
                                                                <button class="accordion-button collapsed p-0 text-sm tnb-detail-trigger" type="button"
                                                                    data-bs-toggle="collapse" data-bs-target="#flush-collapseOne-{{$key}}"
                                                                    data-detail-code="{{$code}}" data-detail-key="{{$key}}"
                                                                    aria-expanded="false" aria-controls="flush-collapseOne-{{$key}}">
                                                                    Flight Detail
                                                                </button>
                                                            </h2>

                                                            <div id="flush-collapseOne-{{$key}}" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample-{{$key}}">
                                                                <div class="accordion-body shadow-wrap">
                                                                    <div class="tnb-detail-lazy-loader" id="detail-loader-{{$key}}" data-loaded="0"></div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    
                                </div>{{-- 14 Nov 2026 (card close): closes .result-step; without it every card was nested inside the previous one --}}
