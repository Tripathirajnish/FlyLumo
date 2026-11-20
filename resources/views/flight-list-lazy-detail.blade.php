                                                                    @php
                                                                        $detailOutbound = $results['AllFlight']['Rph_1'] ?? [];
                                                                        $detailReturn   = $results['AllFlight']['Rph_2'] ?? [];

                                                                        // For Multi-City, treat every Rph_N as its own requested flight leg.
                                                                        // Do not interpret Rph_2 as a "return" journey.
                                                                        $detailMultiCity = [];
                                                                        if (($fields['FlightType'] ?? '') === 'multi_city') {
                                                                            $orderedRph = $results['AllFlight'] ?? [];
                                                                            uksort($orderedRph, function ($a, $b) {
                                                                                return (int)str_replace('Rph_', '', $a) <=> (int)str_replace('Rph_', '', $b);
                                                                            });

                                                                            foreach ($orderedRph as $rphKey => $segments) {
                                                                                $legNo = (int) str_replace('Rph_', '', $rphKey);
                                                                                if ($legNo < 1 || empty($segments)) continue;

                                                                                $searchLeg = $fields['SearchLegs'][$legNo - 1] ?? [];
                                                                                $detailMultiCity[] = [
                                                                                    'leg_no' => $legNo,
                                                                                    'segments' => $segments,
                                                                                    'search_leg' => is_object($searchLeg) ? (array)$searchLeg : (array)$searchLeg,
                                                                                ];
                                                                            }
                                                                        }

                                                                        /*
                                                                         * Flight APIs in this project use slightly different key names
                                                                         * between providers. Resolve several known variants instead of
                                                                         * assuming one schema.
                                                                         */
                                                                        $segVal = function($seg, $keys, $default='') {
                                                                            foreach ((array)$keys as $k) {
                                                                                if (is_array($seg) && array_key_exists($k,$seg) && $seg[$k] !== '' && $seg[$k] !== null) {
                                                                                    return $seg[$k];
                                                                                }
                                                                            }
                                                                            return $default;
                                                                        };

                                                                        $cleanDateValue = function($value) {
                                                                            if (!$value) return null;

                                                                            // Exact format returned by this provider, e.g. 26.08.2026 21:15
                                                                            foreach (['d.m.Y H:i','d.m.Y H:i:s','d.m.Y','d-m-Y H:i','Y-m-d H:i:s','Y-m-d H:i','Y-m-d','m/d/Y H:i','m/d/Y'] as $fmt) {
                                                                                try {
                                                                                    return \Carbon\Carbon::createFromFormat($fmt,(string)$value);
                                                                                } catch (\Throwable $e) {}
                                                                            }

                                                                            try {
                                                                                return \Carbon\Carbon::parse($value);
                                                                            } catch (\Throwable $e) {}

                                                                            return null;
                                                                        };

                                                                        $fmtTime = function($value) {
                                                                            if (!$value) return '—';

                                                                            foreach (['d.m.Y H:i','d.m.Y H:i:s','Y-m-d H:i:s','Y-m-d H:i','m/d/Y H:i'] as $fmt) {
                                                                                try {
                                                                                    return \Carbon\Carbon::createFromFormat($fmt,(string)$value)->format('h:i A');
                                                                                } catch (\Throwable $e) {}
                                                                            }

                                                                            try { return \Carbon\Carbon::parse($value)->format('h:i A'); } catch (\Throwable $e) {}

                                                                            if (preg_match('/^\d{1,2}:\d{2}/',(string)$value)) {
                                                                                try { return \Carbon\Carbon::parse($value)->format('h:i A'); } catch (\Throwable $e) {}
                                                                            }
                                                                            return (string)$value;
                                                                        };

                                                                        $fmtDateCarbon = function($carbon) {
                                                                            return $carbon ? $carbon->format('D, d M Y') : '—';
                                                                        };

                                                                        $fmtMinutes = function($mins) {
                                                                            $mins=(int)$mins;
                                                                            if ($mins<=0) return '—';
                                                                            $h=intdiv($mins,60); $m=$mins%60;
                                                                            return trim(($h?$h.'h ':'').($m?$m.'m':''));
                                                                        };

                                                                        // Shared segment normalizer for BOTH outbound (Rph_1) and return (Rph_2).
                                                                        // Current provider payload uses Departure_Date / Arrival_Date like:
                                                                        // 26.08.2026 21:15 and 30.09.2026 05:30.
                                                                        $normalizeSegment = function($segment) use ($segVal,$cleanDateValue,$fmtTime,$fmtMinutes,$fields) {
                                                                            $depRaw = $segVal($segment,[
                                                                                'Departure_Date','DepartureDate','DepartureDateTime',
                                                                                'Departure_DateTime','DepartureDatetime','DepartureTime',
                                                                                'Departure_Time','DptTime','DepartTime','DepTime','Departure'
                                                                            ],'');

                                                                            $arrRaw = $segVal($segment,[
                                                                                'Arrival_Date','ArrivalDate','ArrivalDateTime',
                                                                                'Arrival_DateTime','ArrivalDatetime','ArrivalTime',
                                                                                'Arrival_Time','ArrTime','ArvTime','ReachTime','Arrival'
                                                                            ],'');

                                                                            $airlineCode = $segVal($segment,[
                                                                                'Airline','MarketingAirline','MarketingCarrier',
                                                                                'OperatingAirline','Carrier','CarrierCode'
                                                                            ],'');

                                                                            $flightNo = $segVal($segment,[
                                                                                'FlightNum','FlightNumber','FlightNo','Flight_Number',
                                                                                'Flight_No','Flight','Number','FlightCode'
                                                                            ],'');

                                                                            $displayFlightNo = trim((string)$flightNo);
                                                                            if ($displayFlightNo && $airlineCode) {
                                                                                $normalizedFlight = strtoupper(str_replace(' ','',$displayFlightNo));
                                                                                $normalizedCarrier = strtoupper(str_replace(' ','',$airlineCode));
                                                                                if (!str_starts_with($normalizedFlight,$normalizedCarrier)) {
                                                                                    $displayFlightNo = trim($airlineCode.' '.$displayFlightNo);
                                                                                }
                                                                            } elseif (!$displayFlightNo) {
                                                                                $displayFlightNo = $airlineCode ?: '—';
                                                                            }

                                                                            $cabin = $segVal($segment,[
                                                                                'FlightClass','CabinClass','Cabin','Class','BookingClass'
                                                                            ], $fields['FlightClass'] ?? 'Economy');

                                                                            $cabinMap = [
                                                                                'Econom'=>'Economy',
                                                                                'econom'=>'Economy',
                                                                                'PremiumEconomy'=>'Premium Economy',
                                                                                'Peconom'=>'Premium Economy',
                                                                                'Business'=>'Business',
                                                                                'business'=>'Business',
                                                                                'First'=>'First',
                                                                                'firstclass'=>'First'
                                                                            ];
                                                                            $cabin = $cabinMap[$cabin] ?? $cabin;

                                                                            $mins = (int)$segVal($segment,[
                                                                                'FlightMinutes','DurationMinutes','ElapsedTime',
                                                                                'Duration','JourneyMinutes'
                                                                            ],0);

                                                                            return [
                                                                                'dep_raw'       => $depRaw,
                                                                                'arr_raw'       => $arrRaw,
                                                                                'dep_carbon'    => $cleanDateValue($depRaw),
                                                                                'arr_carbon'    => $cleanDateValue($arrRaw),
                                                                                'dep_time'      => $fmtTime($depRaw),
                                                                                'arr_time'      => $fmtTime($arrRaw),
                                                                                'duration_min'  => $mins,
                                                                                'duration_text' => $fmtMinutes($mins),
                                                                                'airline_code'  => $airlineCode,
                                                                                'flight_no'     => $displayFlightNo,
                                                                                'cabin'         => $cabin,
                                                                            ];
                                                                        };

                                                                        // Search dates are guaranteed by the listing request and are the
                                                                        // safest fallback when a provider omits date fields per segment.
                                                                        $searchDepartDate = $cleanDateValue($fields['DeptDate'] ?? null);
                                                                        $searchReturnDate = $cleanDateValue($fields['RetnDate'] ?? null);

                                                                        $fromFull = (string)($fields['IATAFromFull'] ?? $fields['IATAFrom'] ?? '');
                                                                        $toFull   = (string)($fields['IATAToFull'] ?? $fields['IATATo'] ?? '');
                                                                    @endphp

                                                                    {{-- IMPORTANT:
                                                                         Expanded segment times come from each segment's own
                                                                         Departure_Date / Arrival_Date. They must not be replaced
                                                                         by the compact itinerary's overall journey arrival time. --}}
                                                                    <div class="tnb-inline-details" id="fullsegment_{{$key}}" data-loaded="1">
                                                                        @php
                                                                            if (($fields['FlightType'] ?? '') === 'multi_city') {
                                                                                $journeyDetails = [];
                                                                                foreach ($detailMultiCity as $mcJourney) {
                                                                                    $mcLegNo = (int)($mcJourney['leg_no'] ?? 0);
                                                                                    $mcSearchLeg = (array)($mcJourney['search_leg'] ?? []);
                                                                                    $mcSegments = (array)($mcJourney['segments'] ?? []);

                                                                                    $journeyDetails[] = [
                                                                                        'label' => 'Flight '.$mcLegNo,
                                                                                        'segments' => $mcSegments,
                                                                                        'icon' => 'fa-plane-departure',
                                                                                        'fallbackDate' => $cleanDateValue($mcSearchLeg['Date'] ?? null),
                                                                                        'fallbackStart' => null,
                                                                                        'originFull' => (string)($mcSearchLeg['IATAFrom'] ?? ''),
                                                                                        'destFull' => (string)($mcSearchLeg['IATATo'] ?? ''),
                                                                                    ];
                                                                                }
                                                                            } else {
                                                                                $journeyDetails = [
                                                                                    ['label'=>'Depart','segments'=>$detailOutbound,'icon'=>'fa-plane-departure','fallbackDate'=>$searchDepartDate,'fallbackStart'=>$results['FtrDptTime'] ?? null,'originFull'=>$fromFull,'destFull'=>$toFull],
                                                                                    ['label'=>'Return','segments'=>$detailReturn,'icon'=>'fa-plane-arrival','fallbackDate'=>$searchReturnDate,'fallbackStart'=>$results['FtrRtnTime'] ?? null,'originFull'=>$toFull,'destFull'=>$fromFull],
                                                                                ];
                                                                            }
                                                                        @endphp

                                                                        @foreach($journeyDetails as $journeyDetail)

                                                                            @if(count($journeyDetail['segments']))
                                                                                @php
                                                                                    $firstSeg = $journeyDetail['segments'][0] ?? [];

                                                                                    $journeyRawDate = $segVal($firstSeg,[
                                                                                        'DepartureDateTime','Departure_DateTime','DepartureDatetime',
                                                                                        'DepartureDate','Departure_Date','DptDate','DepartDate',
                                                                                        'DateOfDeparture','Departure'
                                                                                    ],'');

                                                                                    $journeyDateObj = $cleanDateValue($journeyRawDate) ?: $journeyDetail['fallbackDate'];
                                                                                @endphp

                                                                                <div class="tnb-detail-journey-head" data-detail-journey="{{ strtolower(str_replace(' ','-',$journeyDetail['label'])) }}">
                                                                                    <i class="fa-solid {{ $journeyDetail['icon'] }}"></i>
                                                                                    <strong>{{ $journeyDetail['label'] }}</strong>
                                                                                    @if(($fields['FlightType'] ?? '') === 'multi_city' && !empty($journeyDetail['originFull']) && !empty($journeyDetail['destFull']))
                                                                                        <span>· {{ $journeyDetail['originFull'] }} → {{ $journeyDetail['destFull'] }}</span>
                                                                                    @endif
                                                                                    <span>· {{ $fmtDateCarbon($journeyDateObj) }}</span>{{-- 14 Nov 2026: "Depart · Sun, 13 Dec 2026" (was "Depart , Sun") --}}
                                                                                </div>

                                                                                @foreach($journeyDetail['segments'] as $segIndex=>$segment)
                                                                                    @php
                                                                                        $isFirstSeg = $segIndex === 0;
                                                                                        $isLastSeg  = $segIndex === count($journeyDetail['segments'])-1;

                                                                                        $depRaw = $segVal($segment,[
                                                                                            'DepartureDateTime','Departure_DateTime','DepartureDatetime',
                                                                                            'Departure_Date','DepartureDate',
                                                                                            'DepartureTime','Departure_Time','DptTime','DepartTime',
                                                                                            'DepTime','Departure','DTime','Dpt_Time','Dep_Time',
                                                                                            'Departure_Time_Local','LocalDepartureTime','StartTime',
                                                                                            'SegmentDepartureTime','SchDepTime','ScheduledDepartureTime'
                                                                                        ], $isFirstSeg ? ($journeyDetail['fallbackStart'] ?? '') : '');

                                                                                        $arrRaw = $segVal($segment,[
                                                                                            'ArrivalDateTime',
                                                                                            'Arrival_DateTime',
                                                                                            'ArrivalDatetime',
                                                                                            'Arrival_Date',
                                                                                            'ArrivalDate',
                                                                                            'ArrivalTime',
                                                                                            'Arrival_Time',
                                                                                            'ArrTime',
                                                                                            'ArvTime',
                                                                                            'ReachTime',
                                                                                            'Arrival',
                                                                                            'ATime',
                                                                                            'Arr_Time',
                                                                                            'Arv_Time',
                                                                                            'Arrival_Time_Local',
                                                                                            'LocalArrivalTime',
                                                                                            'EndTime',
                                                                                            'SegmentArrivalTime',
                                                                                            'SchArrTime',
                                                                                            'ScheduledArrivalTime',

                                                                                            // Provider-specific arrival time variants
                                                                                            'ArrivalTime_1',
                                                                                            'Arv_Time',
                                                                                            'Arr_Time',
                                                                                            'Arrival_Time_1',
                                                                                            'ToTime',
                                                                                            'DestinationTime',
                                                                                            'Destination_Time',
                                                                                            'ArrivalClock',
                                                                                            'ArriveTime'
                                                                                        ],'');

                                                                                        $depDateRaw = $segVal($segment,[
                                                                                            'DepartureDate','Departure_Date','DptDate','DepartDate','DateOfDeparture'
                                                                                        ],'');

                                                                                        $arrDateRaw = $segVal($segment,[
                                                                                            'ArrivalDate','Arrival_Date','ArrDate','ArvDate','DateOfArrival'
                                                                                        ],'');

                                                                                        $depDateObj = $cleanDateValue($depDateRaw ?: $depRaw) ?: $journeyDateObj;
                                                                                        $arrDateObj = $cleanDateValue($arrDateRaw ?: $arrRaw) ?: $depDateObj;

                                                                                        // If an overnight arrival time is earlier than departure time,
                                                                                        // show the following day when provider omitted the arrival date.
                                                                                        try {
                                                                                            if (!$arrDateRaw && $depDateObj && $depRaw && $arrRaw) {
                                                                                                $depClock = \Carbon\Carbon::parse($depRaw);
                                                                                                $arrClock = \Carbon\Carbon::parse($arrRaw);
                                                                                                if ($arrClock->format('H:i') < $depClock->format('H:i')) {
                                                                                                    $arrDateObj = $depDateObj->copy()->addDay();
                                                                                                }
                                                                                            }
                                                                                        } catch (\Throwable $e) {}

                                                                                        $airlineName = $segVal($segment,[
                                                                                            'AirlineName','MarketingAirlineName','OperatingAirlineName',
                                                                                            'CarrierName','Airline'
                                                                                        ],'Airline');

                                                                                        $airlineCode = $segVal($segment,[
                                                                                            'Airline','MarketingAirline','MarketingCarrier',
                                                                                            'OperatingAirline','Carrier','CarrierCode'
                                                                                        ],'');

                                                                                        $flightNo = $segVal($segment,[
                                                                                            'FlightNumber','FlightNo','Flight_Number','Flight_No',
                                                                                            'FlightNum','Flight','Number','FlightCode'
                                                                                        ],'');

                                                                                        $logo = $segVal($segment,['AirlineLogo','Logo','CarrierLogo'],'');
                                                                                        $depCode = $segVal($segment,[
                                                                                            'Departure_Iata','DepartureIata','DepartureIATA',
                                                                                            'Origin','OriginCode','From','FromCode'
                                                                                        ],'');

                                                                                        $arrCode = $segVal($segment,[
                                                                                            'Arrival_Iata','ArrivalIata','ArrivalIATA',
                                                                                            'Destination','DestinationCode','To','ToCode'
                                                                                        ],'');

                                                                                        $depName = $segVal($segment,[
                                                                                            'DepartureAirportName','Departure_AirportName',
                                                                                            'OriginAirportName','DepartureAirport','OriginName','FromName'
                                                                                        ],'');

                                                                                        $arrName = $segVal($segment,[
                                                                                            'ArrivalAirportName','Arrival_AirportName',
                                                                                            'DestinationAirportName','ArrivalAirport','DestinationName','ToName'
                                                                                        ],'');

                                                                                        // First/last point fallbacks come from the actual search request.
                                                                                        if (!$depName && $isFirstSeg) $depName = $journeyDetail['originFull'];
                                                                                        if (!$arrName && $isLastSeg)  $arrName = $journeyDetail['destFull'];

                                                                                        $mins = $segVal($segment,[
                                                                                            'FlightMinutes','DurationMinutes','ElapsedTime',
                                                                                            'Duration','JourneyMinutes'
                                                                                        ],0);

                                                                                        $cabin = $segVal($segment,[
                                                                                            'CabinClass','Cabin','Class','BookingClass'
                                                                                        ], $fields['FlightClass'] ?? 'Economy');

                                                                                        // Convert internal cabin codes to customer-facing labels.
                                                                                        $cabinMap = [
                                                                                            'Econom'=>'Economy',
                                                                                            'econom'=>'Economy',
                                                                                            'PremiumEconomy'=>'Premium Economy',
                                                                                            'Peconom'=>'Premium Economy',
                                                                                            'Business'=>'Business',
                                                                                            'business'=>'Business',
                                                                                            'First'=>'First',
                                                                                            'firstclass'=>'First'
                                                                                        ];
                                                                                        $cabin = $cabinMap[$cabin] ?? $cabin;
                                                                                    @endphp

                                                                                    @if($segIndex>0)
                                                                                        @php
                                                                                            $prev = $journeyDetail['segments'][$segIndex-1] ?? [];
                                                                                            $prevArrRaw = $segVal($prev,[
                                                                                                'ArrivalDateTime','Arrival_DateTime','ArrivalDatetime',
                                                                                                'Arrival_Date','ArrivalDate',
                                                                                                'ArrivalTime','Arrival_Time','ArrTime'
                                                                                            ],'');
                                                                                            $connectionMins = 0;

                                                                                            // Prefer provider-supplied layover/connection metadata.
                                                                                            $layoverRaw = $segVal($segment,[
                                                                                                'LayoverMinutes','LayoverTime','Layover',
                                                                                                'ConnectionMinutes','ConnectionTime','ConnectionDuration',
                                                                                                'StopoverMinutes','StopoverTime','StopOverTime',
                                                                                                'GroundTime','TransitTime','WaitingTime'
                                                                                            ],0);

                                                                                            if (is_numeric($layoverRaw)) {
                                                                                                $connectionMins = (int)$layoverRaw;
                                                                                            } elseif (is_string($layoverRaw) && preg_match('/(?:(\d+)\s*h)?\s*(?:(\d+)\s*m)?/i',$layoverRaw,$lm)) {
                                                                                                $connectionMins = ((int)($lm[1] ?? 0) * 60) + (int)($lm[2] ?? 0);
                                                                                            }

                                                                                            // Otherwise calculate from previous arrival -> next departure.
                                                                                            if ($connectionMins <= 0 && $prevArrRaw && $depRaw) {
                                                                                                $pa = $cleanDateValue($prevArrRaw);
                                                                                                $pd = $cleanDateValue($depRaw);

                                                                                                if ($pa && $pd) {
                                                                                                    // Absolute elapsed minutes between previous arrival and next departure.
                                                                                                    // Works for same-day, overnight and multi-day connections.
                                                                                                    $connectionMins = abs($pa->diffInMinutes($pd,false));
                                                                                                }
                                                                                            }
                                                                                        @endphp
                                                                                        <div class="tnb-connection-bar">
                                                                                            <span>Change of plane required</span>
                                                                                            <b>
                                                                                                Stopover
                                                                                                @if($connectionMins>0)
                                                                                                    of {{ $fmtMinutes($connectionMins) }}
                                                                                                @endif
                                                                                                @if($depCode || $depName)
                                                                                                    at {{ $depCode ?: $depName }}
                                                                                                @endif
                                                                                            </b>
                                                                                        </div>
                                                                                    @endif
<div class="tnb-detail-segment" data-journey="{{ strtolower($journeyDetail['label']) }}" data-segment-index="{{ $segIndex }}">
                                                                                        <div class="tnb-detail-airline">
                                                                                            @if($logo)
                                                                                                <img src="{{ $logo }}" alt="{{ $airlineName }}" onerror="this.style.display='none'">
                                                                                            @endif
                                                                                            <div>
                                                                                                <strong>{{ $airlineName }}</strong>
                                                                                                @php
                                                                                                    $displayFlightNo = trim((string)$flightNo);
                                                                                                    if ($displayFlightNo && $airlineCode) {
                                                                                                        $normalizedFlight = strtoupper(str_replace(' ','',$displayFlightNo));
                                                                                                        $normalizedCarrier = strtoupper(str_replace(' ','',$airlineCode));
                                                                                                        if (!str_starts_with($normalizedFlight,$normalizedCarrier)) {
                                                                                                            $displayFlightNo = trim($airlineCode.' '.$displayFlightNo);
                                                                                                        }
                                                                                                    } elseif (!$displayFlightNo) {
                                                                                                        $displayFlightNo = $airlineCode ?: '—';
                                                                                                    }
                                                                                                @endphp
                                                                                                <span>Flight Number: <b>{{ $displayFlightNo }}</b></span>
                                                                                                <em>Class: {{ $cabin ?: 'Economy' }}</em>
                                                                                            </div>
                                                                                        </div>

                                                                                        <div class="tnb-detail-point">
                                                                                            <strong>{{ $fmtTime($depRaw) }}</strong>
                                                                                            <span>{{ $depName ?: $depCode }}@if($depCode) ({{ $depCode }})@endif</span>
                                                                                            <b>{{ $fmtDateCarbon($depDateObj) }}</b>
                                                                                        </div>

                                                                                        <div class="tnb-detail-duration">
                                                                                            <i class="fa-solid fa-clock"></i>
                                                                                            <strong>{{ $fmtMinutes($mins) }}</strong>
                                                                                        </div>

                                                                                        <div class="tnb-detail-point text-end tnb-detail-arrival" data-detail-arrival="{{ strtolower($journeyDetail['label']) }}">
                                                                                            <strong class="tnb-detail-arrival-time">{{ $fmtTime($arrRaw) }}</strong>
                                                                                            <span>{{ $arrName ?: $arrCode }}@if($arrCode) ({{ $arrCode }})@endif</span>
                                                                                            <b>{{ $fmtDateCarbon($arrDateObj) }}</b>
                                                                                        </div>
                                                                                    </div>
                                                                                @endforeach
                                                                            @endif
                                                                        @endforeach
                                                                    </div>                                                                    </div>
