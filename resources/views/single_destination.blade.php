@extends('layout')

@php $setting = getAllSetting(); @endphp

@section('header-seo')
    <title>{{$destination->seo_title}}</title>
    <meta name="description" content="{{$destination->seo_description}}">
@endsection

@section('canonical')
<link rel="canonical" href="{{ url('/destination/'.$destination->slug) }}">
@endsection

@section('mid-content')

<section class="bg-cover position-relative" style="background:url('{{$destination->banner}}')no-repeat;" data-overlay="5">
    <div class="container">
        <div class="row align-items-center justify-content-center">
            <div class="col-xl-8 col-lg-9 col-md-12">

                <div class="fpc-capstion text-center my-4">
                    <div class="fpc-captions">
                        <h1 class="xl-heading text-light">{{$destination->title}}</h1>
                        <p class="text-light" style="font-size: 18px;">{{$destination->short}}</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<div class="py-4 position-relative" style="background:rgb(43, 80, 122)">
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
                                        onclick="checkFlightType(this.value)" checked >
                                        <label class="form-check-label text-white" for="flight_types1">Return</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="flight_types" id="flight_types2" value="one_way"
                                        onclick="checkFlightType(this.value)">
                                        <label class="form-check-label text-white" for="flight_types2">One Way</label>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="row g-1">

                                        <div class="form-group col">
                                            <div>
                                                <input type="text" class="form-control" name="location_from" id="location_from" required placeholder="Enter departure city or airport"
                                                autocomplete="off">
                                            </div>
                                        </div>
                            
                                        <div class="form-group col">
                                            <div>
                                                <input type="text" class="form-control"  name="location_to" id="location_to" required placeholder="Enter arrival city or airport"
                                                autocomplete="off">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="row g-1">

                                        <div class="form-group col">
                                            <input type="date" class="form-control" name="depature_date" id="depature_date" required onchange="setArrivalMinDate()" 
                                            onclick="this.showPicker()">
                                        </div>
                                        
                                        <div class="form-group col">
                                            <div id="return_date_div">
                                                <input type="date" class="form-control" name="return_date" id="return_date" onclick="this.showPicker()"
                                                >
                                            </div>
                                        </div>
                                        
                                    </div>
                                </div>
                                
                                <div class="col">
                                    <div class="form-group">
                                        <div class="dropdown">
                                            <button class="btn btn-md btn-outline-light bg-white text-dark" style="width:100%; padding:1em 1em !important" type="button" id="passenger-count" onclick="togglePassengerBox()">
                                            1 Passengers
                                            </button>
                                        </div>
                                        <div id="passenger-box" class="passenger-box">
                                            <div class="mb-2">
                                                <label for="adult" class="form-label">Adults</label>
                                                <select id="adult-count" name="adult" class="form-select" onchange="updatePassengerCount()" required>
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                                <option value="4">4</option>
                                                <option value="5">5</option>
                                                <option value="6">6</option>
                                                <option value="7">7</option>
                                                <option value="8">8</option>
                                                <option value="9">9</option>
                                                </select>
                                            </div>
                                            <div class="mb-2">
                                                <label for="children" class="form-label">Children</label>
                                                <select id="child-count" name="children" class="form-select" onchange="updatePassengerCount()" required>
                                                    <option value="0">0</option>
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option>
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>
                                                </select>
                                            </div>
                                            <div class="mb-2">
                                                <label for="infant" class="form-label">Infants</label>
                                                <select id="infant-count" name="infant" class="form-select" onchange="updatePassengerCount()" required >
                                                    <option value="0">0</option>
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option>
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>
                                                </select>
                                            </div>
                                            <button type="button" class="btn btn-primary" onclick="closePassengerBox()">Done</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="col">
                                    <div class="form-group">
                                        <select class="form-control" name="cabin_classes" id="cabin_classes" required>
                                        <option value="Econom">Economy</option>
                                        <option value="PremiumEconomy">Premium economy</option>
                                        <option value="Business">Business</option>
                                        <option value="First">First class</option>
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

<section>
    <div class="container">

        <div class="row justify-content-between g-4">

            <div class="col-xl-12 col-lg-9 col-md-12">
                <div class="overview-wrap full-width">
                    <div class="card mb-4 border rounded-3">
                        <div class="card-body">{!!$destination->description!!}</div>
                    </div>

                </div>
            </div>

        </div>

    </div>
</section>

<div class="py-5" style="background:rgb(54, 70, 121);;">
    <div class="container">
    <div class="row align-items-center justify-content-between">

        <div class="col-xl-4 col-lg-4 col-md-6">
        <h4 class="text-light fw-bold lh-base m-0">Join our Newsletter To Keep Up To Date With Us!</h4>
        </div>

        <div class="col-xl-5 col-lg-5 col-md-6">
        <div class="newsletter-forms mt-md-0 mt-4">
            <form>
            <div class="row align-items-center justify-content-between bg-white rounded-3 p-2 gx-0">

                <div class="col-xl-9 col-lg-8 col-md-8">
                <div class="form-group m-0">
                    <input type="text" class="form-control bold ps-1 border-0" placeholder="Enter Your Mail!">
                </div>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-4">
                <div class="form-group m-0">
                    <button type="button" class="btn btn-dark fw-medium full-width">Submit<i class="fa-solid fa-arrow-trend-up ms-2"></i></button>
                </div>
                </div>

            </div>
            </form>
        </div>
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

