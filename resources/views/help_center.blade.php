@extends('layout')

@section('canonical')
<link rel="canonical" href="{{ url('/help-center') }}">
@endsection

@section('header-seo')
    <title>Help Center | Flight Booking Support | FaresHunt</title>
    <meta name="description" content="Visit the FaresHunt Help Center for answers to common questions about flight bookings, reservations, cancellations, changes, payments and travel support.">
@endsection

@section('mid-content')

<section>
    <div class="container">

        <div class="row mb-4">
            <div class="col-12 text-center">
                <h1>Frequently Asked Questions</h1>
                <p class="mb-0">Find answers to common questions about flight bookings, reservations, cancellations, payments and travel support.</p>
            </div>
        </div>

        <div class="row align-items-start">
            <div class="col-xl-12 col-lg-12 col-md-12 mt-4">

                <div class="accordion accordion-flush" id="accordionFlushExample">

                    @if(count($faq)>0)
                        @foreach($faq as $key=>$faqs)

                            <div class="accordion-item border rounded-2">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#flush-{{$key}}" aria-expanded="false" aria-controls="flush-{{$key}}">
                                        {{$faqs['question']}}
                                    </button>
                                </h2>
                                <div id="flush-{{$key}}" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                                    <div class="accordion-body">{!! $faqs['answer'] !!}</div>
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

