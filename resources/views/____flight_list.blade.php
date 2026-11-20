@extends('layout')

@section('header-seo')
    <title>Flight Listing - Search</title>
    <meta name="description" content="Flight Listing - Search">
    <style>      
        .autocomplete-items {
        position: absolute;
        border-bottom: none;
        border-top: none;
        z-index: 99;
        top: 84%;
        width: 420px;
        border:1px solid #d4d4d4;
        }

        .autocomplete-items div {
        padding: 5px;
        cursor: pointer;
        background-color: #fff; 
        border-bottom: 1px solid #d4d4d4;
        }

        .autocomplete-items div:hover {
        background-color: #e9e9e9; 
        }

        .autocomplete-active {
        background-color: DodgerBlue !important; 
        color: #ffffff; 
        }
    </style>
@endsection

@section('mid-content')

@php $setting = getAllSetting(); @endphp

    <!-- Form -->
    <div class="py-4 position-relative" style="background: #ffffff">
        <div class="container">
            
            <div class="row justify-content-center align-items-center">
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                <div class="search-wrap position-relative">
                    <div class="row align-items-end gy-2 gx-md-2 gx-sm-2">

                        <form name="form2" id="form2" action="{{ url('submit-search') }}" method="post">
                            @csrf
                            <div class="row g-2">

                                <div class="flx-start mb-sm-0 mb-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="flight_types" id="flight_types1" value="round_trip"
                                        @checked($fields['FlightType']=='round_trip') onclick="checkFlightType(this.value)">
                                        <label class="form-check-label" for="flight_types1">Return</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="flight_types" id="flight_types2" value="one_way"
                                        @checked($fields['FlightType']=='one_way') onclick="checkFlightType(this.value)">
                                        <label class="form-check-label" for="flight_types2">One Way</label>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="row g-1">

                                        <div class="form-group col">
                                            <div>
                                                <input type="text" class="form-control" name="location_from" id="location_from" required placeholder="Enter departure city or airport"
                                                    value="{{@$fields['IATAFromFull']}}" autocomplete="off">
                                            </div>
                                        </div>
                            
                                        <div class="form-group col">
                                            <div>
                                                <input type="text" class="form-control"  name="location_to" id="location_to" required placeholder="Enter arrival city or airport"
                                                 value="{{@$fields['IATAToFull']}}" autocomplete="off">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="row g-1">

                                        <div class="form-group col">
                                            <input type="date" class="form-control" name="depature_date" id="depature_date" required onchange="setArrivalMinDate()" 
                                            onclick="this.showPicker()" value="{{ date('Y-m-d',strtotime($fields['DeptDate']))}}">
                                        </div>
                                        
                                        <div class="form-group col">
                                            <div id="return_date_div">
                                                <input type="date" class="form-control" name="return_date" id="return_date" onclick="this.showPicker()"
                                                @if(@$fields['RetnDate']) value="{{ date('Y-m-d',@strtotime(@$fields['RetnDate']))}}" @endif
                                                @if($fields['FlightType']=='one_way') disabled @endif >
                                            </div>
                                        </div>
                                        
                                    </div>
                                </div>
                                
                                <div class="col">
                                    <div class="form-group">
                                        <div class="dropdown">
                                            <button class="btn btn-md btn-outline-secondary" style="width:100%; padding:1em 1em !important" type="button" id="passenger-count" onclick="togglePassengerBox()">
                                                @if(@$fields['Adults']) {{@$fields['Adults']+@$fields['Childrens']+@$fields['Infants']}}  @else 1 @endif Passengers
                                            </button>
                                        </div>
                                        <div id="passenger-box" class="passenger-box">
                                            <div class="mb-2">
                                                <label for="adult" class="form-label">Adults</label>
                                                <select id="adult-count" name="adult" class="form-select" onchange="updatePassengerCount()" required>
                                                <option value="1" @if(@$fields['Adults']=='1' || @$fields['Adults']=='') selected @endif >1</option>
                                                <option value="2" @selected(@$fields['Adults']=='2') >2</option>
                                                <option value="3" @selected(@$fields['Adults']=='3') >3</option>
                                                <option value="4" @selected(@$fields['Adults']=='4') >4</option>
                                                <option value="5" @selected(@$fields['Adults']=='5') >5</option>
                                                <option value="6" @selected(@$fields['Adults']=='6') >6</option>
                                                <option value="7" @selected(@$fields['Adults']=='7') >7</option>
                                                <option value="8" @selected(@$fields['Adults']=='8') >8</option>
                                                <option value="9" @selected(@$fields['Adults']=='9') >9</option>
                                                </select>
                                            </div>
                                            <div class="mb-2">
                                                <label for="children" class="form-label">Children</label>
                                                <select id="child-count" name="children" class="form-select" onchange="updatePassengerCount()" required>
                                                    <option value="0" @if(@$fields['Childrens']=='') selected @endif >0</option>
                                                    <option value="1" @selected(@$fields['Childrens']=='1') >1</option>
                                                    <option value="2" @selected(@$fields['Childrens']=='2') >2</option>
                                                    <option value="3" @selected(@$fields['Childrens']=='3') >3</option>
                                                    <option value="4" @selected(@$fields['Childrens']=='4') >4</option>
                                                    <option value="5" @selected(@$fields['Childrens']=='5') >5</option>
                                                </select>
                                            </div>
                                            <div class="mb-2">
                                                <label for="infant" class="form-label">Infants</label>
                                                <select id="infant-count" name="infant" class="form-select" onchange="updatePassengerCount()" required >
                                                    <option value="0" @if(@$fields['Infants']=='') selected @endif >0</option>
                                                    <option value="1" @selected(@$fields['Infants']=='1') >1</option>
                                                    <option value="2" @selected(@$fields['Infants']=='2') >2</option>
                                                    <option value="3" @selected(@$fields['Infants']=='3') >3</option>
                                                    <option value="4" @selected(@$fields['Infants']=='4') >4</option>
                                                    <option value="5" @selected(@$fields['Infants']=='5') >5</option>
                                                </select>
                                            </div>
                                            <button type="button" class="btn btn-primary" onclick="closePassengerBox()">Done</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="col">
                                    <div class="form-group">
                                        <select class="form-control" name="cabin_classes" id="cabin_classes" required>
                                        <option value="Econom" @selected($fields['FlightClass']=='Econom')>Economy</option>
                                        <option value="PremiumEconomy" @selected($fields['FlightClass']=='PremiumEconomy')>Premium economy</option>
                                        <option value="Business" @selected($fields['FlightClass']=='Business')>Business</option>
                                        <option value="First" @selected($fields['FlightClass']=='First')>First class</option>
                                        </select>
                                    </div>
                                </div>
                    
                                <div class="col">
                                    <div class="form-group">
                                        <input type="submit" style="line-height:0px" class="btn btn-primary btn-md" > 
                                    </div>
                                </div>

                            </div>
                        </form>

                    </div>
                </div>
            </div>
            </div>
            
        </div>
    </div>

    @if($output && $status=='1')

        <section class="gray-simple">
            <div class="container">
                <div class="row justify-content-between gy-4 gx-xl-4 gx-lg-3 gx-md-3 gx-4">

                    <!-- Left Side -->
                    <div class="col-xl-3 col-lg-4 col-md-12">
                        <div class="filter-searchBar bg-white rounded-3">
                            <div class="filter-searchBar-head border-bottom">
                                <div class="searchBar-headerBody d-flex align-items-start justify-content-between px-3 py-3">
                                    <div class="searchBar-headerfirst">
                                        <h6 class="fw-bold fs-5 m-0">Filters</h6>
                                    </div>
                                    <div class="searchBar-headerlast text-end">
                                        <a href='{{url("flight-listing/$code")}}' class="text-md fw-medium text-primary active">Clear All</a>
                                    </div>
                                </div>
                            </div>

                            <div class="filter-searchBar-body">
                                
                                <x-left-filter />

                                <form name="form1" id="form1" method="post" action="{{ route('submit_filtering') }}">
                                    @csrf
                                    <input type="hidden" name="code" id="code" value="{{$code}}" />
                                    
                                    <!-- Stops -->
                                    <div class="searchBar-single px-3 py-3 border-bottom">
                                        <div class="searchBar-single-title d-flex mb-3">
                                            <h6 class="sidebar-subTitle fs-6 fw-medium m-0">Onward Stops</h6>
                                        </div>
                                        <div class="searchBar-single-wrap">
                                            <ul class="row align-items-center justify-content-between p-0 gx-3 gy-2">
                                                @if($lFilter['FilterDptStop'])
                                                    @foreach($lFilter['FilterDptStop'] as $FilterDptStops)
                                                        <x-left-filter-dpt-stop :lFilterDptStop="$FilterDptStops" :selected="$seletedlFilter" />
                                                    @endforeach
                                                @endif
                                            </ul>
                                        </div>

                                        @if(@$fields['FlightType']=='round_trip')
                                            <div class="searchBar-single-title d-flex mb-3">
                                                <h6 class="sidebar-subTitle fs-6 fw-medium m-0">Return Stops</h6>
                                            </div>
                                            <div class="searchBar-single-wrap">
                                                <ul class="row align-items-center justify-content-between p-0 gx-3 gy-2">
                                                    @if($lFilter['FilterRtnStop'])
                                                        @foreach($lFilter['FilterRtnStop'] as $FilterRtnStops)
                                                            <x-left-filter-rtn-stop :lFilterRtnStop="$FilterRtnStops" :selected="$seletedlFilter" />
                                                        @endforeach
                                                    @endif
                                                </ul>
                                            </div>
                                        @endif

                                    </div>

                                    <!-- Departure & Return -->
                                    <div class="searchBar-single px-3 py-3 border-bottom">
                                        <div class="searchBar-single-title d-flex mb-3">
                                            <h6 class="sidebar-subTitle fs-6 fw-medium m-0">Departure Time</h6>
                                        </div>
                                        <div class="searchBar-single-wrap mb-4">
                                            <ul class="row align-items-center justify-content-between p-0 gx-3 gy-2">
                                                @if($lFilter['FilterDptTime'])
                                                    @foreach($lFilter['FilterDptTime'] as $FilterDptTimes)
                                                        <li class="col-6">
                                                            <input type="radio" class="btn-check" id="f_dtp_time{{$FilterDptTimes['Code']}}" name="f_dtp_time" value="{{$FilterDptTimes['Code']}}"
                                                            onclick="submitForm()" @checked($FilterDptTimes['Code']==@$seletedlFilter['f_dtp_time']) >
                                                            <label class="btn btn-sm btn-secondary rounded-1 fw-medium px-2 full-width"
                                                                for="f_dtp_time{{$FilterDptTimes['Code']}}">{{$FilterDptTimes['Value']}}</label>
                                                        </li>
                                                    @endforeach
                                                @endif
                                            </ul>
                                        </div>

                                        @if(@$fields['FlightType']=='round_trip')
                                            <div class="searchBar-single-title d-flex mb-3">
                                                <h6 class="sidebar-subTitle fs-6 fw-medium m-0">Return Time</h6>
                                            </div>
                                            <div class="searchBar-single-wrap">
                                                <ul class="row align-items-center justify-content-between p-0 gx-3 gy-2">
                                                    @if($lFilter['FilterRtnTime'])
                                                        @foreach($lFilter['FilterRtnTime'] as $FilterRtnTimes)
                                                            <li class="col-6">
                                                                <input type="radio" class="btn-check" id="f_rtn_time{{$FilterRtnTimes['Code']}}" name="f_rtn_time" value="{{$FilterRtnTimes['Code']}}"
                                                                onclick="submitForm()" @checked($FilterRtnTimes['Code']==@$seletedlFilter['f_rtn_time']) >
                                                                <label class="btn btn-sm btn-secondary rounded-1 fw-medium px-2 full-width"
                                                                    for="f_rtn_time{{$FilterRtnTimes['Code']}}">{{$FilterRtnTimes['Value']}}</label>
                                                            </li>
                                                        @endforeach
                                                    @endif
                                                </ul>
                                            </div>
                                        @endif

                                    </div>

                                    <!-- Airlines -->
                                    <div class="searchBar-single px-3 py-3 border-bottom">
                                        <div class="searchBar-single-title d-flex align-items-center justify-content-between mb-3">
                                            <h6 class="sidebar-subTitle fs-6 fw-medium m-0">Preferred Airlines</h6>
                                        </div>
                                        <div class="searchBar-single-wrap">
                                            <ul class="row align-items-center justify-content-between p-0 gx-3 gy-2">
                                                @if($lFilter['FilterAirline'])
                                                    @foreach($lFilter['FilterAirline'] as $FilterAirlines)
                                                        <x-left-filter-airline :lFilterAirl="$FilterAirlines" :selected="$seletedlFilter" />
                                                    @endforeach
                                                @endif
                                            </ul>
                                        </div>

                                    </div>

                                    <div id="response"></div>
                                </form>

                            </div>
                        </div>
                    </div>

                    <!-- Right Side -->
                    <div class="col-xl-9 col-lg-8 col-md-12" style="margin-top:0px !important;">

                        <!-- Airline Slider -->
                        <!-- <div class="row align-items-center justify-content-between">

                            <style>
                                .table-container {
                                    display: flex;
                                    overflow: hidden;
                                    border: 1px solid #ddd;
                                    border-radius: 5px;
                                }
                                .fixed-column {
                                    flex: 0 0 150px;
                                    background: #f8f9fa;
                                    border-right: 2px solid #ddd;
                                    text-align: center;
                                    font-weight: bold;
                                }
                                .scrollable-table {
                                    display: flex;
                                    transition: transform 0.3s ease-in-out;
                                }
                                .airline-column {
                                    min-width: 150px;
                                    text-align: center;
                                    border-right: 1px solid #ddd;
                                }
                                .controls {
                                    text-align: right;
                                    margin-top: 5px;
                                }
                            </style>

                            <div class="container mt-4">
                                <h4 class="bg-primary text-white p-2">Our Best Options</h4>
                                <div class="table-container">
                                    <div class="fixed-column p-2">
                                        <div>Airline Matrix</div>
                                        <div>Non Stop</div>
                                        <div>1+ Stop</div>
                                        <div>2+ Stop</div>
                                    </div>
                                    <div id="scrollable" class="scrollable-table">
                                        <div class="airline-column">
                                            <img src="frontier.png" width="40"><br>Frontier Airlines
                                            <div>$73.76</div>
                                            <div>$135.32</div>
                                            <div>-</div>
                                        </div>
                                        <div class="airline-column">
                                            <img src="spirit.png" width="40"><br>Spirit Airlines
                                            <div>$80.26</div>
                                            <div>$96.14</div>
                                            <div>-</div>
                                        </div>
                                        <div class="airline-column">
                                            <img src="delta.png" width="40"><br>Delta Air Lines
                                            <div>$116.46</div>
                                            <div>-</div>
                                            <div>-</div>
                                        </div>
                                        <div class="airline-column">
                                            <img src="jetblue.png" width="40"><br>JetBlue Airways
                                            <div>$121.67</div>
                                            <div>-</div>
                                            <div>-</div>
                                        </div>
                                        <div class="airline-column">
                                            <img src="united.png" width="40"><br>United Airlines
                                            <div>$130.21</div>
                                            <div>-</div>
                                            <div>-</div>
                                        </div>
                                        <div class="airline-column">
                                            <img src="american.png" width="40"><br>American Airlines
                                            <div>$151.96</div>
                                            <div>-</div>
                                            <div>-</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="controls">
                                    <button class="btn btn-md btn-danger" onclick="prevSlide()">&lt; prev</button>
                                    <button class="btn btn-md btn-danger" onclick="nextSlide()">next &gt;</button>
                                </div>
                            </div>

                            <script>
                                let position = 0;
                                const container = document.getElementById("scrollable");
                                const items = document.querySelectorAll(".airline-column");
                                const totalItems = items.length;
                                const itemWidth = 150; 

                                function updatePosition() 
                                {
                                    container.style.transform = `translateX(${-position * itemWidth}px)`;
                                }

                                function prevSlide() 
                                {
                                    if (position > 0) {
                                        position--;
                                        updatePosition();
                                    }
                                }

                                function nextSlide() 
                                {
                                    if (position < totalItems - 3) 
                                    { 
                                        position++;
                                        updatePosition();
                                    }
                                }

                            </script>

                        </div>   -->
                    
                        <div class="row align-items-center justify-content-between">
                            <div class="col-xl-4 col-lg-4 col-md-4">
                                <h5 class="fs-6 mb-lg-0 mb-3">Showing {{@$output['Search-ResultCount']}} Search Results</h5>
                            </div>
                            <div class="col-xl-4 col-lg-8 col-md-12">
                                <div class="d-flex align-items-center justify-content-start justify-content-lg-end flex-wrap">

                                    <div class="flsx-first mt-sm-0 mt-2">
                                        <ul class="nav nav-pills nav-fill p-1 small lights blukker bg-dark rounded-3 shadow-sm"
                                            id="filtersblocks" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link active rounded-3" id="Recommended" data-bs-toggle="tab"
                                                    type="button" role="tab" aria-selected="true">Recommended</button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link rounded-3" id="Cheapest" data-bs-toggle="tab" type="button"
                                                    role="tab" aria-selected="false">Cheapest</button>
                                            </li>
                                            <!-- <li class="nav-item" role="presentation">
                                                <button class="nav-link rounded-3" id="Shortest" data-bs-toggle="tab" type="button"
                                                    role="tab" aria-selected="false">Shortest - $771.23</button>
                                            </li> -->
                                        </ul>
                                    </div>
                                    
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-4">&nbsp;</div>
                        </div>

                        <div class="row align-items-center g-4 mt-2">

                            @php $sno = 1; @endphp
                            @foreach($output['result'] as $key=>$results)

                                <div class="col-xl-12 col-lg12 col-md-12" 
                                data-price="1200" 
                                data-dpt-stop="2" data-rtn-stop="2" 
                                data-dpt-time="11:12" data-rtn-time="04:12" 
                                data-dpt-airline="airindia" data-rtn-airline="airindia">

                                    <div class="flights-accordion">
                                        <div class="flights-list-item bg-white rounded-3 p-3">

                                            <div class="row gy-4 align-items-center justify-content-between">
                                                
                                                <div class="col">
                                                    <x-flight-list-departure :$results />
                                                    @if($fields['FlightType']=='round_trip')
                                                        <x-flight-list-return :$results />
                                                    @endif
                                                </div>

                                                @php $searchguid = $output['Search-SearchGuid']; @endphp
                                                <x-flight-list-price :$key :$code :$searchguid :$results />

                                            </div>

                                            <div class="row mt-3">
                                                <div class="col-12 border-top pt-3">
                                                    <div class="accordion accordion-flush" id="accordionFlushExample-{{$key}}">
                                                        <div class="accordion-item mb-0">

                                                            <h2 class="accordion-header rounded-2"  
                                                            onclick='showDetail("{{$code}}","{{$key}}")'>
                                                                <button class="accordion-button collapsed p-0 text-sm" type="button"
                                                                    data-bs-toggle="collapse" data-bs-target="#flush-collapseOne-{{$key}}"
                                                                    aria-expanded="false" aria-controls="flush-collapseOne-{{$key}}">
                                                                    Flight Details
                                                                </button>
                                                            </h2>

                                                            <div id="flush-collapseOne-{{$key}}" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample-{{$key}}">
                                                                <div class="accordion-body shadow-wrap">
                                                                    <div class="airLines-fullsegment" id="fullsegment_{{$key}}">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    
                                </div>

                            @endforeach

                        </div>

                    </div>

                </div>
            </div>
        </section>

        <div class="modal fade" id="model1" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content main-boxx">
                    <div class="modal-body">
                        <div class="row gx-3 mb-3">
                            <div class="text-center" id="not-flight-msz"><img src='/assets/images/flight_loading.gif' style="width:100%"></div>
                            <div class="text-center" id="model-button" style="display:none; margin-top:20px;">
                                <a class="btn btn-md btn-primary" href="/">HOME</a>
                                <a class="btn btn-md btn-dark" href='{{url("flight-listing/$code")}}'>REFRESH</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="model2" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md">
                <div class="modal-content main-boxx">
                    <div class="modal-body">
                        <div class="row gx-3 mb-3">
                            <div class="text-center" id="not-flight-msz2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @else

        <section class="gray-simple">
            <div class="container">
                <div class="row justify-content-between gy-4 gx-xl-4 gx-lg-3 gx-md-3 gx-4">

                    <span style="text-align:center;">{{$error}}
                    @if($error=='Search limit')
                    : Current Application in Development Mode, Please rehit the link to get the result, <br/><a href="{{route('flight_listing',$code)}}">REHIT</a>
                    @endif
                    </span>

                </div>
            </div>
        </section>
    
    @endif

    <!-- popup after 5 sec -->
    <div class="modal fade" id="onload" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content main-boxx">
                <div class="modal-header modal-logo-section"><img src="{{ $setting['header_logo'] }}" width="150" /></div>
                
                <div class="modal-body">

                    <div class="row gx-3 mb-3">
                        <div class="col-auto">Special fare<br /></div>
                        <div class="col text-center">
                            <span class="text-sm">Get upto</span><br /><span style="font-weight: bold; color: rgb(61, 187, 61); font-size: 24px;">{{$setting['flist_popup_content']}}% Off</span>
                        </div>
                        <div class="col-auto">
                            <span class="text-sm">Limited Time Offer <br />Ends In </span><br /><span class="text-primary" style="font-weight: bold; font-size: 18px" id="timer">14m 35s</span>
                        </div>
                    </div>

                    <div class="row gx-lg-5 gx-3 gy-4 align-items-center">
                        <div class="col">
                            <div class="row gx-3">

                                <div class="col-auto">
                                    <div class="text-sm">{{ date('d M',strtotime($fields['DeptDate']))}} | {{@$fields['IATAFrom']}}</div>
                                </div>

                                <div class="col text-center">
                                    <div class="flightLine departure"><div></div><div></div></div>
                                    <div class="text-sm mt-3">@if($fields['FlightType']=='one_way') One Way @else Round Trip @endif<br />{{@$fields['FlightClass']}}</div>
                                </div>

                                <div class="col-auto">
                                    <div class="text-sm">{{@$fields['IATATo']}}</div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer text-center" style="display: block">
                    <p>
                        <button type="button" class="btn btn-primary btn-lg">
                            Call Now {{$setting['header_contact']}}
                        </button>
                    </p>

                    <p class="text-sm">*Fares are subject to seat availability and not guaranteed untilticketed.</p>
                </div>

            </div>
        </div>
    </div>

@endsection


@section('footer')

<script>

    function checkArray(code,OfferCode,SearchGuid,key)
    {
        //jQuery('#not-flight-msz').html("<img src='/assets/images/flight_loading.gif'>");
        jQuery('#model1').modal('show');

        jQuery.ajax({
            url:"/submit-prebook/ajax/"+code+"/"+OfferCode+"/"+SearchGuid+"/"+key,
            type:'GET',
            success:function(data)
            {
                var datas = data.split('***');

                if(datas[0]=='1')
                {
                    window.location.href = "/flight-payment/"+code+"/"+key;
                }
                else
                {
                    jQuery('#not-flight-msz').html(datas[1]);
                    jQuery('#model-button').css('display', 'block');
                }
            }
        });
    }

    function submitForm() 
    {
        //jQuery('#not-flight-msz').html("<img src='/assets/images/flight_loading.gif'>");
        jQuery('#model1').modal('show');
        document.getElementById("form1").submit();
    }

    function checkFlightType(val)
    {
        if(val=='round_trip')
        {
            //alert('round_trip');
            //jQuery('#return_date_div').show();
            jQuery('#return_date').prop("disabled", false);
            jQuery('#return_date').addAttribute('required', "true");
        }
        if(val=='one_way')
        {
            //alert('one_way');
            //jQuery('#return_date_div').hide();
            jQuery('#return_date').val('').prop("disabled", true);
            jQuery('#return_date').removeAttribute('required');
        }
    }

    function showDetail(code,key)
    {
        $.ajax({
            url: '/ajax/get-flight-detail', 
            type: 'GET', 
            data: { code: code,key: key },
            success: function(response) 
            {
                jQuery("#fullsegment_"+key).html(response);
            }
        });
    }

    function showMarkup(code,key)
    {
        $.ajax({
            url: '/ajax/get-markup-detail', 
            type: 'GET', 
            data: { code: code,key: key },
            success: function(response) 
            {
                jQuery('#not-flight-msz2').html(response);
                jQuery('#model2').modal('show');
            }
        });
    }

    document.getElementById('form2').addEventListener('submit', function() 
    {
        jQuery('#model1').modal('show');

        // setTimeout(() => 
        // {
        //     $("#onload").modal("show");
        // }, 5000);
    });

</script>

<script>
    // Toggle the passenger box
    function togglePassengerBox() {
        const box = document.getElementById('passenger-box');
        box.style.display = (box.style.display === 'block') ? 'none' : 'block';
    }

    // Close the passenger box and update the passenger count
    function closePassengerBox() {
        const adults = parseInt(document.getElementById('adult-count').value, 10);
        const children = parseInt(document.getElementById('child-count').value, 10);
        const infants = parseInt(document.getElementById('infant-count').value, 10);
        const totalPassengers = adults + children + infants;

        // Update the main passenger count field
        document.getElementById('passenger-count').textContent = `${totalPassengers} Passengers`;

        // Close the box
        const box = document.getElementById('passenger-box');
        box.style.display = 'none';
    }

    // Update the passenger count dynamically
    function updatePassengerCount() {
        const adults = parseInt(document.getElementById('adult-count').value, 10);
        const children = parseInt(document.getElementById('child-count').value, 10);
        const infants = parseInt(document.getElementById('infant-count').value, 10);
        const totalPassengers = adults + children + infants;

        // Update the main passenger count field
        document.getElementById('passenger-count').textContent = `${totalPassengers} Passengers`;
    }

    // Set Arrival Date's minimum date to Departure Date
    function setArrivalMinDate() {
        const departureDate = document.getElementById('location_from').value;
        const arrivalDate = document.getElementById('location_to');
        
        if (departureDate) {
            arrivalDate.setAttribute('min', departureDate);
        }
    }

    // Trigger the date picker on click
    document.getElementById('location_from').addEventListener('click', function () {
        this.showPicker();
    });

    document.getElementById('location_to').addEventListener('click', function () {
        this.showPicker();
    });
</script>

<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function() 
    {
        if (window.location.search === "") 
        {            
            setTimeout(() => 
            {
                $("#onload").modal("show");
            }, 5000);
        }
    });
</script>


<script>
    let totalSeconds = 15 * 60; // 15 minutes in seconds

    function updateTimer() {
        let minutes = Math.floor(totalSeconds / 60);
        let seconds = totalSeconds % 60;
        document.getElementById("timer").innerText = 
            `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;

        if (totalSeconds > 0) {
            totalSeconds--;
            setTimeout(updateTimer, 1000);
        }
    }

    updateTimer(); // Start the timer
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.0/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.0/themes/base/jquery-ui.css">

<script>
  $(document).ready(function () 
  {
      $("#location_from").autocomplete(
        {
          source: function (request, response) 
          {
              $.ajax(
              {
                url: "/ajax/get-airport",
                data: { term: request.term },
                success: function (data) 
                {
                    response(data);
                }
              });
          },
          minLength: 3
      });

      $("#location_to").autocomplete(
        {
          source: function (request, response) 
          {
              $.ajax(
              {
                url: "/ajax/get-airport",
                data: { term: request.term },
                success: function (data) 
                {
                    response(data);
                }
              });
          },
          minLength: 3
      });
  });
</script>

@endsection



