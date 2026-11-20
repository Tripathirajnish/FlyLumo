@extends('layout')

@section('canonical')
<link rel="canonical" href="{{ url('/offer/'.$detail->slug) }}">
@endsection

@section('header-seo')
    <title>{{ $detail->seo_title ?: (($detail->title ?: 'Flight Deal').' | FaresHunt') }}</title>
    <meta name="description" content="{{ $detail->seo_description ?: \Illuminate\Support\Str::limit(strip_tags($detail->description ?? ''), 155) }}">
@endsection

@section('mid-content')

    <section class="p-0">
        <div class="thumb-wrap">
            <img src="{{$detail->banner}}" class="img-fluid full-width ht-500 object-fit" alt="">
        </div>
    </section>

    <section>
        <div class="container">
            <div class="row">
                
                <div class="col-lg-10 mx-auto">
                    <div class="row g-4">
                        <div class="col-md-12 col-lg-12">
                            <h1>{{$detail->title}}</h1>
                            <p class="pb-0 pt-3">{!! $detail->description !!}</p>
                        </div>
                    </div>
        
                </div>	
            </div>
        </div>
    </section>

@endsection

@section('mid-content')

@endsection