@extends('layout')

@section('header-seo')
    <meta name="robots" content="noindex, nofollow">
<title>Title</title>
    <meta name="description" content="Description">
@endsection

@section('mid-content')

    <div class="py-4 position-relative" style="background: #ffffff;">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                    
                   search 
                </div>
            </div>
        </div>
    </div>

    <section class="gray-simple">
        <div class="container">
            <div class="row justify-content-between gy-4 gx-xl-4 gx-lg-3 gx-md-3 gx-4">

               {{Session::get('Booking-Error-Msz')}}

            </div>
        </div>
    </section>

@endsection


@section('footer')


@endsection



