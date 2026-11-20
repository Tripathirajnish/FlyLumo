<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="/assets/css/flatpickr.min.css" rel="stylesheet">
<link href="/assets/css/tnb-premium-calendar.css" rel="stylesheet">

@php $setting = getAllSetting(); @endphp

<div class="container-fluid py-3 px-5">
    <h3>MetaSite API - Search Flight:</h3>
    <div class="card-body">
    <!-- Form -->
    <form name="form1" id="form1" action="{{ route('fareleader_ui_search') }}" method="post">
        @csrf
        <div class="row g-1 justify-content-between">

            <div class="flx-start mb-sm-0 mb-2">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="tripType" id="tripType" value="roundTrip"
                    @checked(@$form_fields['tripType']=='roundTrip') onclick="checkFlightType(this.value)">
                    <label class="form-check-label" for="tripType1">Return</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="tripType" id="tripType" value="oneway"
                    @checked(@$form_fields['tripType']=='oneway') onclick="checkFlightType(this.value)">
                    <label class="form-check-label" for="tripType2">One Way</label>
                </div>
            </div>

            <div class="d-flex gap-1 w-100 align-items-end">
                <div class="col">
                    <div class="row g-1">

                        <div class="form-group col">
                            <div>
                                <input type="text" class="form-control" name="origin1" id="origin1" required placeholder="Departure Airport"
                                    value="{{@$form_fields['origin1']}}" autocomplete="off">
                                    <span style="font-size:11px;color:gray; ">&nbsp;</span>
                            </div>
                        </div>
            
                        <div class="form-group col">
                            <div>
                                <input type="text" class="form-control"  name="destination1" id="destination1" required placeholder="Arrival Airport"
                                    value="{{@$form_fields['destination1']}}" autocomplete="off">
                                    <span style="font-size:11px;color:gray; ">&nbsp;</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="row g-1">

                        <div class="form-group col">
                            <input type="input" class="form-control" name="departDate1" id="departDate1" data-premium-date data-alt-format="M j, Y" required  placeholder="Departure Date"
                            @if(@$form_fields['departDate1'])  value="{{ $form_fields['departDate1'] }}"   @endif>
                            <span style="font-size:11px;color:gray; ">18-May-2025</span>
                        </div>
                        
                        <div class="form-group col">
                            <div id="return_date_div">
                                <input type="input" class="form-control" name="returnDate1" id="returnDate1" data-premium-date data-alt-format="M j, Y" placeholder="Return Date"
                                @if(@$form_fields['returnDate1']) value="{{ @$form_fields['returnDate1'] }}" @endif
                                @if(@$form_fields['tripType']=='oneway') disabled @endif >
                                <span style="font-size:11px;color:gray; ">25-May-2025</span>
                            </div>
                        </div>
                        
                    </div>
                </div>
                
                <div class="col">
                    <div class="row g-1">
                        <div class="form-group col">
                            <label for="adult" class="form-label">Adults</label>
                            <select id="adults" name="adults" class="form-select" required>
                            <option value="1" @if(@$form_fields['adults']=='1' || @$form_fields['adults']=='') selected @endif >1</option>
                            <option value="2" @selected(@$form_fields['adults']=='2') >2</option>
                            <option value="3" @selected(@$form_fields['adults']=='3') >3</option>
                            <option value="4" @selected(@$form_fields['adults']=='4') >4</option>
                            <option value="5" @selected(@$form_fields['adults']=='5') >5</option>
                            <option value="6" @selected(@$form_fields['adults']=='6') >6</option>
                            <option value="7" @selected(@$form_fields['adults']=='7') >7</option>
                            <option value="8" @selected(@$form_fields['adults']=='8') >8</option>
                            <option value="9" @selected(@$form_fields['adults']=='9') >9</option>
                            </select>
                            <span style="font-size:11px;color:gray; ">&nbsp;</span>
                        </div>
                        <div class="form-group col">
                            <label for="children" class="form-label">Children</label>
                            <select id="childs" name="childs" class="form-select" required>
                                <option value="0" @if(@$form_fields['childs']=='') selected @endif >0</option>
                                <option value="1" @selected(@$form_fields['childs']=='1') >1</option>
                                <option value="2" @selected(@$form_fields['childs']=='2') >2</option>
                                <option value="3" @selected(@$form_fields['childs']=='3') >3</option>
                                <option value="4" @selected(@$form_fields['childs']=='4') >4</option>
                                <option value="5" @selected(@$form_fields['childs']=='5') >5</option>
                            </select>
                            <span style="font-size:11px;color:gray; ">&nbsp;</span>
                        </div>
                        <div class="form-group col">
                            <label for="infant" class="form-label">Infants</label>
                            <select id="infants" name="infants" class="form-select" required >
                                <option value="0" @if(@$form_fields['infants']=='') selected @endif >0</option>
                                <option value="1" @selected(@$form_fields['infants']=='1') >1</option>
                                <option value="2" @selected(@$form_fields['infants']=='2') >2</option>
                                <option value="3" @selected(@$form_fields['infants']=='3') >3</option>
                                <option value="4" @selected(@$form_fields['infants']=='4') >4</option>
                                <option value="5" @selected(@$form_fields['infants']=='5') >5</option>
                            </select>
                            <span style="font-size:11px;color:gray; ">&nbsp;</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-1">
                    <div class="form-group">
                        <select class="form-control" name="classType" id="classType" required>
                        <option value="Y" @selected(@$form_fields['classType']=='Y')>Economy</option>
                        <option value="W" @selected(@$form_fields['classType']=='W')>Premium economy</option>
                        <option value="C" @selected(@$form_fields['classType']=='C')>Business</option>
                        <option value="F" @selected(@$form_fields['classType']=='F')>First class</option>
                        </select>
                        <span style="font-size:11px;color:gray; ">&nbsp;</span>
                        <input type="hidden" value="USD" name="currency" id="currency">
                        <input type="hidden" value="fareleaders"  name="metaName" id="metaName">
                    </div>
                </div>

                <div class="col-auto">
                    <div class="form-group">
                        <input type="submit" class="btn btn-primary btn-md" > <br/>
                        <span style="font-size:11px;color:gray; ">&nbsp;</span>
                    </div>
                </div>
            </div>

        </div>
    </form>
    </div>

    <h5>Output</h5>
    @php 

    if(@$filterResult)
    {
        //echo "<pre>";
        //print_r($filterResult);
        $flight =   $filterResult['flightItineraries'];

        echo "Total Result: ".count($flight);
        
        foreach($flight as $key=>$flights)
        {
            //$match        =   $flights['match'];
            $price          =   $flights['price'];
            $leg1           =   $flights['leg1'];
            $leg2           =   $flights['leg2'];
            $other          =   $flights['other'];
            $deeplinkUrl    =   $flights['deeplinkUrl'];
            
            echo "<table border='1' width='100%' style='margin-bottom:20px; font-size:14px;' cellpadding='6' cellmargin='5'>";
           
            echo "<tr><td colspan='5'>";
                echo "<table width='100%' style='margin-bottom:20px; font-size:14px;'>";
                echo "<tr><td>Old Price Info</td><td>&nbsp;</td><td colspan='4'>".$other['oldInfo']."</td></tr>";
                echo "<tr><td>New Price Info</td><td>&nbsp;</td><td colspan='4'>".$other['newInfo']."</td></tr>";
                echo "</table>";
            echo "</td><td><a class='btn btn-sm btn-success' target='_blank' href='".$deeplinkUrl."'>Select</a></td></tr>";

            echo "<tr style='font-weight:bold'>";
            echo "<td>Airline Code</td>";
            echo "<td>Cabin</td>";
            echo "<td>Departure Airport (DateTime)</td>";
            echo "<td>Arrival Airport (DateTime)</td>";
            echo "<td>Flight Number</td>";
            echo "<td>Aircraft Code</td>";
            echo "</tr>";

            if(@$leg1)
            {
                $segments   =   $leg1['segments'];

                foreach($segments as $segmentss)
                {
                    $cabin                  =   $segmentss['cabin'];
                    $flightNumber           =   $segmentss['flightNumber'];
                    $airlineCode            =   $segmentss['airlineCode'];
                    $aircraftCode           =   $segmentss['aircraftCode'];
                    $departureDateTime      =   $segmentss['departureDateTime'];
                    $arrivalDateTime        =   $segmentss['arrivalDateTime'];
                    $departureAirportCode   =   $segmentss['departureAirportCode'];
                    $arrivalAirportCode     =   $segmentss['arrivalAirportCode'];
                    
                    echo "<tr>";
                    echo "<td>$airlineCode</td>";
                    echo "<td>$cabin</td>";
                    echo "<td>$departureAirportCode ($departureDateTime)</td>";
                    echo "<td>$arrivalAirportCode ($arrivalDateTime)</td>";
                    echo "<td>$flightNumber</td>";
                    echo "<td>$aircraftCode</td>";
                    echo "</tr>";
                }
            }

            if(@$leg2)
            {
                $segments   =   $leg2['segments'];

                echo "<tr><td colspan='8'><u>Return</u></td></tr>";

                foreach($segments as $segmentss)
                {
                    $cabin                  =   $segmentss['cabin'];
                    $flightNumber           =   $segmentss['flightNumber'];
                    $airlineCode            =   $segmentss['airlineCode'];
                    $aircraftCode           =   $segmentss['aircraftCode'];
                    $departureDateTime      =   $segmentss['departureDateTime'];
                    $arrivalDateTime        =   $segmentss['arrivalDateTime'];
                    $departureAirportCode   =   $segmentss['departureAirportCode'];
                    $arrivalAirportCode     =   $segmentss['arrivalAirportCode'];

                    echo "<tr>";
                    echo "<td>$airlineCode</td>";
                    echo "<td>$cabin</td>";
                    echo "<td>$departureAirportCode ($departureDateTime)</td>";
                    echo "<td>$arrivalAirportCode ($arrivalDateTime)</td>";
                    echo "<td>$flightNumber</td>";
                    echo "<td>$aircraftCode</td>";
                    echo "</tr>";
                }  
            }

            echo "<tr><td colspan='8'>&nbsp;</td></tr>";
            echo "</table>";
        }

        
    }
    else
    {
        echo "No Flights are there.";
    }
    
    @endphp

</div>

<script src="/assets/js/jquery.min.js"></script>

<script>

    function checkFlightType(val)
    {
        if(val=='round_trip')
        {
            jQuery('#return_date').prop("disabled", false);
            jQuery('#return_date').addAttribute('required', "true");
        }
        if(val=='one_way')
        {
            jQuery('#return_date').val('').prop("disabled", true);
            jQuery('#return_date').removeAttribute('required');
        }
    }

</script>
<script src="/assets/js/flatpickr.js"></script>
<script src="/assets/js/tnb-premium-calendar.js"></script>
