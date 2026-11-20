@php $fields = (array) @json_decode($fields); @endphp
<div class="segmentDeparture-head mb-3 mt-2">
    <h6 class="m-0">Departure</h6>
</div>

<div class="col-xl-12 col-lg12 col-md-12">
    <div class="flights-accordion">

        @if(@$results)

            @php $previousValue = ''; @endphp

            @foreach(@$results['AllFlight']['Rph_1'] as $results_key=>$results_flight)

                @if($results_key!='0')

                    @php
                        $date1 = new DateTime($previousValue);
                        $date2 = new DateTime($results_flight['Departure_Date']);

                        $interval = $date1->diff($date2);

                        if ($interval->d > 0) 
                        {
                            $overlay = $interval->format('%d days, %h hr, %i min ');
                        } 
                        else 
                        {
                            if ($interval->h > 0) 
                            {
                                $overlay = $interval->format('%h hr, %i min ');
                            } 
                            else 
                            {
                                $overlay = $interval->format('%i min ');
                            }
                        }

                        $overlay.= "layover in ".@$results_flight['Departure_Name']." (".@$results_flight['Departure_Iata'].")";

                    @endphp

                    <div class="segmentDeparture-overlay" style="text-align:center">
                        <span style="background: #fff7d2; padding:5px 10px;">
                            <i class="fa-regular fa-clock me-1"></i> {{$overlay}}
                        </span>
                    </div>

                @endif

                @php 
                    $previousValue  =   $results_flight['Arrival_Date'];
                    $overlay        =   ''; 
                @endphp

                <div class="row gx-lg-5 gx-3 gy-4 align-items-center">

                    <div class="col-sm-auto">
                        <div class="d-flex align-items-center justify-content-start" >
                            <div class="d-start fl-pic">
                                <img class="img-fluid" src="{{@$results_flight['AirlineLogo']}}" width="45" alt="image" />
                            </div>
                            <div class="d-end fl-title ps-2" >
                                <div class="text-dark fw-medium">{{@$results_flight['AirlineName']}}</div>
                                <div class="text-sm">Flight {{@$results_flight['FlightNum']}}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="row gx-3 align-items-center" >
                            <div class="col-auto">
                                <div>{{@$results_flight['Departure_Name']}} ({{@$results_flight['Departure_Iata']}})</div>
                                <div class="text-dark fw-bold" >{{ @date('h:i A',strtotime(@$results_flight['Departure_Date'])) }}</div>
                                <div>{{ @date('D, M d',strtotime(@$results_flight['Departure_Date'])) }}</div>
                            </div>

                            <div class="col text-center" >
                                <div class="flightLine departure" >
                                <div></div>
                                <div></div>
                                </div>
                                <div class="text-sm mt-3" >
                                    @php 
                                    $hours              = floor(@$results_flight['FlightMinutes'] / 60);
                                    $remainingMinutes   = @$results_flight['FlightMinutes'] % 60;
                                    echo "Flight Duration: ".$hours."h ".$remainingMinutes."m";
                                    @endphp
                                </div>
                            </div>

                            <div class="col-auto">
                                <div>{{$results_flight['Arrival_Name']}} ({{$results_flight['Arrival_Iata']}})</div>
                                <div class="text-dark fw-bold" >{{ @date('h:i A',strtotime(@$results_flight['Arrival_Date'])) }}</div>
                                <div>{{ @date('D, M d',strtotime(@$results_flight['Arrival_Date'])) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-auto">
                        <div class="text-dark">{{@$results_flight['FlightClass']}}</div>
                    </div>

                </div>

            @endforeach

        @endif

    </div>
</div>

@if(@$fields['FlightType']=='round_trip')
    <div class="segmentDeparture-wrap mt-3 border-top pt-3">

        <div class="segmentDeparture-head mb-3 mt-2">
            <h6 class="m-0">Return</h6>
        </div>
            
        <div class="col-xl-12 col-lg12 col-md-12">
            <div class="flights-accordion">

                @if(@$results)

                    @php $previousValue1 = ''; @endphp

                    @foreach($results['AllFlight']['Rph_2'] as $results_key=>$results_flight)

                        @if($results_key!='0')

                            @php
                                $date1 = new DateTime($previousValue1);
                                $date2 = new DateTime($results_flight['Departure_Date']);

                                $interval = $date1->diff($date2);

                                if ($interval->d > 0) 
                                {
                                    $overlay = $interval->format('%d days, %h hr, %i min ');
                                } 
                                else 
                                {
                                    if ($interval->h > 0) 
                                    {
                                        $overlay = $interval->format('%h hr, %i min ');
                                    } 
                                    else 
                                    {
                                        $overlay = $interval->format('%i min ');
                                    }
                                }

                                $overlay.= "layover in ".@$results_flight['Departure_Name']." (".@$results_flight['Departure_Iata'].")";

                            @endphp

                            <div class="segmentDeparture-overlay" style="text-align:center">
                                <span style="background: #fff7d2; padding:5px 10px;">
                                    <i class="fa-regular fa-clock me-1"></i> {{$overlay}}
                                </span>
                            </div>

                        @endif

                        @php 
                            $previousValue1  =   $results_flight['Arrival_Date'];
                            $overlay         =   ''; 
                        @endphp

                        <div class="row gx-lg-5 gx-3 gy-4 align-items-center">

                            <div class="col-sm-auto">
                                <div class="d-flex align-items-center justify-content-start" >
                                    <div class="d-start fl-pic">
                                        <img class="img-fluid" src="{{@$results_flight['AirlineLogo']}}" width="45" alt="image" />
                                    </div>
                                    <div class="d-end fl-title ps-2" >
                                        <div class="text-dark fw-medium">{{@$results_flight['AirlineName']}}</div>
                                        <div class="text-sm">Flight {{@$results_flight['FlightNum']}}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col">
                                <div class="row gx-3 align-items-center" >
                                    <div class="col-auto">
                                        <div>{{@$results_flight['Departure_Name']}} ({{@$results_flight['Departure_Iata']}})</div>
                                        <div class="text-dark fw-bold" >{{ @date('h:i A',strtotime(@$results_flight['Departure_Date'])) }}</div>
                                        <div>{{ @date('D, M d',strtotime(@$results_flight['Departure_Date'])) }}</div>
                                    </div>

                                    <div class="col text-center" >
                                        <div class="flightLine departure" >
                                        <div></div>
                                        <div></div>
                                        </div>
                                        <div class="text-sm mt-3" >
                                            @php 
                                            $hours              = floor(@$results_flight['FlightMinutes'] / 60);
                                            $remainingMinutes   = @$results_flight['FlightMinutes'] % 60;
                                            echo "Flight Duration: ".$hours."h ".$remainingMinutes."m";
                                            @endphp
                                        </div>
                                    </div>

                                    <div class="col-auto">
                                        <div>{{$results_flight['Arrival_Name']}} ({{$results_flight['Arrival_Iata']}})</div>
                                        <div class="text-dark fw-bold" >{{ @date('h:i A',strtotime(@$results_flight['Arrival_Date'])) }}</div>
                                        <div>{{ @date('D, M d',strtotime(@$results_flight['Arrival_Date'])) }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-auto">
                                <div class="text-dark">{{@$results_flight['FlightClass']}}</div>
                            </div>

                        </div>

                    @endforeach
                    
                @endif

            </div>
        </div>

    </div>
@endif