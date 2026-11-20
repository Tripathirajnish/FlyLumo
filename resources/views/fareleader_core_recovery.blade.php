@extends('layout')

@section('header-seo')
<meta name="robots" content="noindex, nofollow">
<title>Flight Availability Update</title>
@endsection

@section('mid-content')
<section class="gray-simple py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <h2>Flight Availability Update</h2>
                        <p class="mt-3">
                            The flight fare you selected is no longer available
                            at its previously quoted price or availability.
                        </p>
                        <p>
                            We can check current flight options for your journey.
                            Any updated itinerary and price will be shown for
                            your review before you continue to passenger details.
                        </p>
                        <div class="alert alert-info">
                            No booking or payment has been made.
                        </div>
                        @if(!empty($offers))
                            <h4 class="mt-4 mb-3">Available Flight Options</h4>
                            <p class="text-muted">
                                These are newly searched options. Prices and availability
                                may change until the airline confirms your selection.
                            </p>

                            @foreach($offers as $offer)
                                @php
                                    $segments = $offer['leg1']['segments'] ?? [];
                                    $first = $segments[0] ?? [];
                                    $last = !empty($segments) ? end($segments) : [];
                                    $price = $offer['price'] ?? [];
                                    $link = $offer['deeplinkUrl'] ?? '';
                                @endphp

                                @if(!empty($segments) && !empty($link) && isset($price['totalAmount']))
                                    <div class="card border mb-3">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between flex-wrap gap-3">
                                                <div>
                                                    <strong>
                                                        {{ $first['departureAirportCode'] ?? '' }}
                                                        &rarr;
                                                        {{ $last['arrivalAirportCode'] ?? '' }}
                                                    </strong>
                                                    <div class="text-muted small mt-1">
                                                        Airline: {{ $first['airlineCode'] ?? 'N/A' }}
                                                        |
                                                        Flight: {{ $first['flightNumber'] ?? 'N/A' }}
                                                    </div>
                                                    <div class="small mt-2">
                                                        Departure: {{ $first['departureDateTime'] ?? 'N/A' }}
                                                    </div>
                                                    <div class="small">
                                                        Arrival: {{ $last['arrivalDateTime'] ?? 'N/A' }}
                                                    </div>
                                                    <div class="small">
                                                        Stops: {{ max(0, count($segments) - 1) }}
                                                    </div>
                                                </div>

                                                <div class="text-md-end">
                                                    <h4 class="mb-2">
                                                        {{ $price['currencyCode'] ?? 'USD' }}
                                                        {{ number_format((float) $price['totalAmount'], 2) }}
                                                    </h4>
                                                    <a class="btn btn-primary"
                                                       href="{{ $link }}"
                                                       rel="nofollow">
                                                        Select Flight
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <div class="alert alert-warning mt-4 mb-0">
                                No updated flight options are currently available
                                for this journey. Please try again later.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
