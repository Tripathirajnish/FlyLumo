@extends('layout')

@section('header-seo')
<title>Site Map | Fares Hunt</title>
<meta name="description" content="Explore Fares Hunt pages, flight resources, airline guides, travel information and support pages.">
@endsection

@section('mid-content')
<style>
.fhs-page{--navy:#071a31;--red:#d91f43;--gold:#e1b238;--ink:#0b213e;--muted:#667b95;--line:#e0e7ef;background:#f7f9fc}
.fhs-hero{padding:62px 20px;text-align:center;background:linear-gradient(120deg,#07182d,#0b213d 58%,#103255);color:#fff}
.fhs-hero h1{margin:0 0 10px;color:#fff;font-size:clamp(40px,4vw,58px);font-weight:650;letter-spacing:-.045em}
.fhs-hero p{max-width:720px;margin:auto;color:#cad5e2;font-size:14px;line-height:1.65}
.fhs-wrap{width:min(1280px,calc(100% - 40px));margin:auto;padding:48px 0 64px}
.fhs-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}
.fhs-card{padding:24px;border:1px solid var(--line);border-radius:18px;background:#fff}
.fhs-card h2{margin:0 0 15px;padding-bottom:12px;border-bottom:1px solid #edf1f5;color:var(--ink);font-size:18px}
.fhs-card ul{margin:0;padding:0;list-style:none}
.fhs-card li+li{margin-top:8px}
.fhs-card a{display:flex;align-items:center;gap:9px;color:#60758f!important;font-size:12.5px;line-height:1.4;text-decoration:none!important}
.fhs-card a:before{content:"›";color:var(--gold);font-size:20px;line-height:1}
.fhs-card a:hover{color:var(--red)!important}
@media(max-width:900px){.fhs-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:620px){.fhs-wrap{width:calc(100% - 24px);padding:34px 0 46px}.fhs-grid{grid-template-columns:1fr}}
</style>

<div class="fhs-page">
<section class="fhs-hero">
    <h1>Site Map</h1>
    <p>Quick links to flight search, deals, airline guides, travel resources and Fares Hunt support information.</p>
</section>

<div class="fhs-wrap">
    <div class="fhs-grid">
        <section class="fhs-card">
            <h2>Flights</h2>
            <ul>
                <li><a href="/#main-content">Round Trip Flights</a></li>
                <li><a href="/#main-content">One Way Flights</a></li>
                <li><a href="/#main-content">Economy Class</a></li>
                <li><a href="/#main-content">Premium Economy</a></li>
                <li><a href="/#main-content">Business Class</a></li>
                <li><a href="/#main-content">First Class</a></li>
            </ul>
        </section>

        <section class="fhs-card">
            <h2>Explore</h2>
            <ul>
                <li><a href="{{ route('all_offers') }}">Flight Deals</a></li>
                <li><a href="{{ route('all_destinations') }}">Destinations</a></li>
                <li><a href="{{ route('all_blogs') }}">Travel Guides</a></li>
                <li><a href="{{ route('search_flight') }}">Search Flights</a></li>
            </ul>
        </section>

        <section class="fhs-card">
            <h2>Airline Guides</h2>
            <ul>
                <li><a href="{{ route('airline_detail',['slug'=>'united-airlines']) }}">United Airlines</a></li>
                <li><a href="{{ route('airline_detail',['slug'=>'american-airlines']) }}">American Airlines</a></li>
                <li><a href="{{ route('airline_detail',['slug'=>'delta-air-lines']) }}">Delta Air Lines</a></li>
                <li><a href="{{ route('airline_detail',['slug'=>'british-airways']) }}">British Airways</a></li>
                <li><a href="{{ route('airline_detail',['slug'=>'emirates']) }}">Emirates</a></li>
                <li><a href="{{ route('airline_detail',['slug'=>'qatar-airways']) }}">Qatar Airways</a></li>
            </ul>
        </section>

        <section class="fhs-card">
            <h2>Company</h2>
            <ul>
                <li><a href="{{ url('/about-us') }}">About Us</a></li>
                <li><a href="{{ route('contact_us') }}">Contact Us</a></li>
            </ul>
        </section>

        <section class="fhs-card">
            <h2>Policies</h2>
            <ul>
                <li><a href="{{ url('/terms-and-conditions') }}">Terms &amp; Conditions</a></li>
                <li><a href="{{ url('/refund-and-dispute-policy') }}">Refund &amp; Dispute</a></li>
                <li><a href="{{ url('/privacy-policy') }}">Privacy Policy</a></li>
                <li><a href="{{ url('/cookie-policy') }}">Cookies Policy</a></li>
                <li><a href="{{ url('/cancellation-policy') }}">Cancellation Policy</a></li>
                <li><a href="{{ url('/disclaimer') }}">Disclaimer</a></li>
            </ul>
        </section>

        <section class="fhs-card">
            <h2>Support</h2>
            <ul>
                <li><a href="tel:+18887170346">Call +1-888-717-0346</a></li>
                <li><a href="https://wa.me/13072638435" target="_blank" rel="noopener">WhatsApp +1 (307) 263-8435</a></li>
                <li><a href="mailto:info@fareshunt.com">info@fareshunt.com</a></li>
            </ul>
        </section>
    </div>
</div>
</div>
@endsection
