@extends('layout')

@section('canonical')
<link rel="canonical" href="{{ url('/destinations') }}">
@endsection

@section('header-seo')
    <title>Flight Destinations & Travel Ideas | FaresHunt</title>
    <meta name="description" content="Explore flight destinations and travel ideas with FaresHunt. Discover places to visit and start planning domestic and international trips.">
@endsection

@section('mid-content')

    <section class="bg-cover position-relative" style="background:url(/assets/img/bg-3.jpg)no-repeat; padding: 100px 0;" data-overlay="5">
        <div class="container">
            <div class="row align-items-center justify-content-center">
                <div class="col-xl-7 col-lg-9 col-md-12">
                    <div class="fpc-capstion text-center my-4">
                        <div class="fpc-captions">
                            <h1 class="xl-heading text-light">Popular Flight Destinations</h1>
                            <p class="text-light">Explore the world with our expert travel guides, offering tips, itineraries, and hidden gems to help you plan your perfect journey. Let us be your companion on every adventure!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="fpc-banner"></div>
    </section>

    <section>
        <div class="container">

            <div class="row align-items-center justify-content-center">
                <div class="col-xl-12 col-lg-12 col-md-12">
                    <div class="row g-4">

                        @if(count($destination)>0)
                            @foreach($destination as $key=>$destinations)

                                <div class="col-3">
                                    <div class="cardCities cursor rounded-2">
                                        <div class="cardCities-image ratio ratio-4">
                                            <img src="{{$destinations->image}}" class="img-fluid object-fit" alt="{{ $destinations->title }}">
                                        </div>

                                        <div class="citiesCard-content d-flex flex-column justify-content-between text-center px-4 py-4">
                                            <div class="cardCities-bg"></div>

                                            <div class="citiesCard-topcaps">
                                                <div class="d-flex align-items-center justify-content-center flex-wrap">
                                                </div>
                                            </div>

                                            <div class="citiesCard-bottomcaps">
                                                <h4 class="text-light fs-3 mb-3">{{$destinations->title}}</h4>
                                                <div style="display: inline-flex;">
                                                    <a href='{{ url("/destination/$destinations->slug") }}' class="btn btn-whitener btn-md me-2">Details</a>
                                                    <a href="javascript:void();" class="btn btn-primary btn-md me-2">Call Now</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            @endforeach
                        @endif

                    </div>
                </div>
            </div>

        </div>
    </section>

@endsection

@section('mid-content')

@endsection