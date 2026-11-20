@extends('layout')

@section('canonical')
<link rel="canonical" href="{{ url('/airlines/'.$slug) }}">
@endsection

@section('header-seo')
<title>{{ $airline['name'] }} | Airline Guide | Fares Hunt</title>
<meta name="description" content="Learn about {{ $airline['name'] }}, cabin options, network information and travel planning with Fares Hunt.">
@endsection

@section('mid-content')
<style>
.fha-page{--navy:#071a31;--navy2:#103255;--red:#d91f43;--gold:#e2b437;--ink:#0b213e;--muted:#667b95;--line:#e0e7ef;background:#f7f9fc;color:var(--ink)}
.fha-hero{position:relative;overflow:hidden;padding:64px 20px 68px;background:radial-gradient(circle at 82% 18%,rgba(217,31,67,.10),transparent 25%),linear-gradient(120deg,#07182d,#0b213d 58%,#103255);color:#fff}
.fha-hero:after{content:"";position:absolute;inset:0;background:linear-gradient(rgba(255,255,255,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.025) 1px,transparent 1px);background-size:46px 46px}
.fha-container{position:relative;z-index:2;width:min(1280px,calc(100% - 40px));margin:auto}
.fha-kicker{display:inline-flex;align-items:center;gap:8px;margin-bottom:18px;color:#f5c348!important;font-size:11px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}
.fha-hero h1{margin:0 0 14px!important;color:#fff!important;font-size:clamp(46px,4.6vw,66px)!important;line-height:1.02!important;font-weight:700!important;letter-spacing:-.045em!important;text-shadow:0 2px 18px rgba(0,0,0,.18)!important}
.fha-hero p{max-width:820px;margin:0!important;color:#e6edf5!important;font-size:16px!important;line-height:1.65!important;font-weight:400!important}
.fha-badges{display:flex;flex-wrap:wrap;gap:9px;margin-top:22px}
.fha-badges span{display:inline-flex;align-items:center;min-height:36px;padding:0 13px;border:1px solid rgba(255,255,255,.22);border-radius:999px;background:rgba(255,255,255,.08);font-size:11px;color:#fff!important;box-shadow:inset 0 1px 0 rgba(255,255,255,.04)}
.fha-main{padding:48px 0 64px}
.fha-grid{display:grid;grid-template-columns:minmax(0,1.6fr) minmax(310px,.7fr);gap:28px;align-items:start}
.fha-card{padding:28px;border:1px solid var(--line);border-radius:20px;background:#fff;box-shadow:0 12px 30px rgba(17,42,74,.05)}
.fha-card+.fha-card{margin-top:20px}
.fha-card h2{margin:0 0 14px!important;color:var(--ink)!important;font-size:24px!important;letter-spacing:-.025em!important;font-weight:700!important}
.fha-card p{margin:0 0 13px!important;color:var(--muted)!important;font-size:13.5px!important;line-height:1.75!important}
.fha-card p:last-child{margin-bottom:0}
.fha-facts{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.fha-fact{padding:16px;border:1px solid #e5ebf2;border-radius:14px;background:#fafcff}
.fha-fact b{display:block;margin-bottom:5px;color:var(--ink);font-size:11px}
.fha-fact span{color:var(--muted);font-size:12px;line-height:1.45}
.fha-cabins{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin:0;padding:0;list-style:none}
.fha-cabins li{position:relative;padding:12px 12px 12px 31px;border:1px solid #e5ebf2;border-radius:12px;background:#fafcff;color:#5f748e;font-size:12px}
.fha-cabins li:before{content:"";position:absolute;left:13px;top:17px;width:6px;height:6px;border-radius:50%;background:var(--red)}
.fha-alert{padding:16px 17px;border-left:4px solid var(--gold);border-radius:0 13px 13px 0;background:#fff9ea;color:#6c6f74;font-size:12px;line-height:1.65}
.fha-cta{position:sticky;top:100px;padding:26px;border:1px solid #dce5ef;border-radius:20px;background:linear-gradient(180deg,#fff,#f8fafc)}
.fha-code{display:inline-grid;place-items:center;width:52px;height:52px;margin-bottom:15px;border-radius:15px;background:#0b213d;color:#fff;font-size:18px;font-weight:800}
.fha-cta h3{margin:0 0 8px;color:var(--ink);font-size:20px}
.fha-cta p{margin:0 0 18px;color:var(--muted);font-size:12.5px;line-height:1.65}
.fha-button{display:flex;align-items:center;justify-content:center;gap:8px;min-height:48px;padding:0 18px;border-radius:13px;background:linear-gradient(135deg,#d91f43,#b71031);color:#fff!important;text-decoration:none!important;font-size:13px;font-weight:700}
.fha-back{display:inline-flex;margin-top:13px;color:#667b95!important;font-size:11.5px;text-decoration:none!important}
@media(max-width:900px){.fha-grid{grid-template-columns:1fr}.fha-cta{position:static}}
@media(max-width:640px){.fha-container{width:calc(100% - 24px)}.fha-hero{padding:54px 12px}.fha-main{padding:34px 0 46px}.fha-card{padding:21px}.fha-facts,.fha-cabins{grid-template-columns:1fr}}
</style>

<div class="fha-page">
    <section class="fha-hero">
        <div class="fha-container">
            <span class="fha-kicker"><i class="fa-solid fa-plane"></i> Airline Guide</span>
            <h1>{{ $airline['name'] }}</h1>
            <p>{{ $airline['tagline'] }}</p>
            <div class="fha-badges">
                <span>IATA {{ $airline['code'] }}</span>
                <span>{{ $airline['program'] }}</span>
                <span>{{ $airline['alliance'] }}</span>
            </div>
        </div>
    </section>

    <section class="fha-main">
        <div class="fha-container fha-grid">
            <main>
                <article class="fha-card">
                    <h2>About {{ $airline['name'] }}</h2>
                    <p>{{ $airline['overview'] }}</p>
                    <p>{{ $airline['network'] }}</p>
                </article>

                <article class="fha-card">
                    <h2>Cabin options</h2>
                    <p>Cabin names and availability can vary by aircraft, route and fare. Common {{ $airline['name'] }} cabin products include:</p>
                    <ul class="fha-cabins">
                        @foreach($airline['cabins'] as $cabin)
                            <li>{{ $cabin }}</li>
                        @endforeach
                    </ul>
                </article>

                <article class="fha-card">
                    <h2>Before you book</h2>
                    <div class="fha-facts">
                        <div class="fha-fact"><b>Frequent flyer program</b><span>{{ $airline['program'] }}</span></div>
                        <div class="fha-fact"><b>Airline / alliance</b><span>{{ $airline['alliance'] }}</span></div>
                        <div class="fha-fact"><b>IATA code</b><span>{{ $airline['code'] }}</span></div>
                        <div class="fha-fact"><b>Fare availability</b><span>Varies by route, travel date and booking class.</span></div>
                    </div>
                </article>

                <article class="fha-card">
                    <h2>Baggage, seats and fare rules</h2>
                    <p>Baggage allowances, seat selection, change rules, cancellation terms and other optional services depend on the exact fare and itinerary selected.</p>
                    <div class="fha-alert">Always review the fare rules displayed for your selected itinerary before completing a booking. Airline policies may change and can differ by route, cabin and ticket type.</div>
                </article>
            </main>

            <aside class="fha-cta">
                <div class="fha-code">{{ $airline['code'] }}</div>
                <h3>Search {{ $airline['name'] }} flights</h3>
                <p>Search live flight availability and compare available itineraries through Fares Hunt.</p>
                <a class="fha-button" href="{{ route('search_flight') }}?airline={{ $airline['search_code'] }}">
                    <i class="fa-solid fa-magnifying-glass"></i> Search Flights
                </a>
                <a class="fha-back" href="{{ route('sitemap') }}">View Site Map</a>
            </aside>
        </div>
    </section>
</div>
@endsection