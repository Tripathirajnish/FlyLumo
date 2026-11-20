@extends('layout')

@section('header-seo')
    <title>Title</title>
    <meta name="description" content="Description">
@endsection

@section('mid-content')

    <section class="bg-cover position-relative" style="background:url(/assets/img/bg-3.jpg)no-repeat; padding: 100px 0;" data-overlay="5">
			<div class="container">
				<div class="row align-items-center justify-content-center">
					<div class="col-xl-7 col-lg-9 col-md-12">
						<div class="fpc-capstion text-center my-4">
							<div class="fpc-captions">
								<h1 class="xl-heading text-light">Offers</h1>
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

				<div class="row justify-content-center g-5">

            @if(count($offer)>0)
                @foreach($offer as $key=>$offers)

                    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                        <div class="pop-touritems">
                            <a href='{{url("offer/$offers->slug")}}' class="card rounded-3 border br-dashed border-2 m-0">
                                <div class="offers-container d-flex align-items-center justify-content-start p-2">
                                    <div class="offers-flex position-relative">
                                        <div class="offer-tags position-absolute start-0 top-0 mt-2 ms-2">
                                            <span class="label text-light bg-danger fw-medium">20% Off</span>
                                        </div>
                                        <div class="offers-pic"><img src="{{$offers->image}}" class="img-fluid rounded" width="110"></div>
                                    </div>
                                    <div class="offers-captions ps-3">
                                        <h4 class="city fs-6 m-0 fw-bold">
                                            <span>{{$offers->title}}</span>
                                        </h4>
                                        <p class="detail ellipsis-container">
                                            <span class="ellipsis-item__normal">@if($offers->flighttype){{$offers->flighttype->name}}@endif</span>
                                            <span class="separate ellipsis-item__normal"></span>
                                            <span class="ellipsis-item">@if($offers->flightstop){{$offers->flightstop->name}}@endif</span>
                                        </p>
                                        <div class="booking-wrapes d-flex align-items-center justify-content-between">
                                            <h5 class="fs-5 low-price m-0"><span class="tag-span">starting from</span> <span class="price">${{$offers->price}}</span></h5>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                @endforeach
            @endif

				</div>

			</div>
		</section>

@endsection

@section('mid-content')

@endsection