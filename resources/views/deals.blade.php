@extends('layout')

@section('canonical')
<link rel="canonical" href="{{ url('/deals') }}">
@endsection

@section('header-seo')
    <title>Flight Deals | FaresHunt</title>
    <meta name="description" content="Explore live domestic, international, Business Class, First Class, holiday and last-minute flight deals with FaresHunt.">
@endsection

@section('mid-content')

@php
    $cmsv = fn($key,$default='') => $cmsSettings[$key] ?? $default;
    $cards = fn($section) => $cmsCards[$section] ?? collect();

    $cardImage = function($d, $fallback='/assets/img/banner-8.jpg') {
        $code = strtoupper((string)($d->to_code ?? ''));
        $map = [
            'LHR'=>'/assets/img/city/c-1.png',
            'NRT'=>'/assets/img/destination/tr-3.jpg',
            'DXB'=>'/assets/img/city/ct-8.png',
            'CDG'=>'/assets/img/city/c-6.png',
            'BOM'=>'/assets/img/city/ct-1.png',
            'DEL'=>'/assets/img/city/ct-4.png',
            'BLR'=>'/assets/img/city/ct-10.png',
            'HYD'=>'/assets/img/city/ct-5.png',
            'SYD'=>'/assets/img/destination/tr-9.jpg',
            'SIN'=>'/assets/img/city/ct-12.png',
        ];

        return $map[$code] ?? ($d->image ?: $fallback);
    };
@endphp

{{-- Keep the existing page/banner language, but rename Offers -> Deals. --}}
<section class="bg-cover position-relative" style="background:url(/assets/img/deals-hero.png) no-repeat center/cover; padding:76px 0;" data-overlay="5">
    <div class="container">
        <div class="row align-items-center justify-content-center">
            <div class="col-xl-8 col-lg-9 col-md-12">
                <div class="fpc-capstion text-center my-4">
                    <div class="fpc-captions">
                        <h1 class="xl-heading text-light">Flight Deals &amp; Special Offers</h1>
                        <p class="text-light mb-0">Explore our complete collection of live domestic, international and premium-cabin deals in one place.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <svg class="fh-page-wave" viewBox="0 0 1440 80" preserveAspectRatio="none" aria-hidden="true"><path d="M0,42 C240,66 485,70 735,59 C1010,47 1225,27 1440,43 L1440,80 L0,80 Z"></path></svg>
    <style>
    .fh-page-wave{
        display:block;
        position:absolute;
        left:0;
        right:0;
        bottom:-1px;
        width:100%;
        height:50px;
        z-index:5;
        pointer-events:none;
    }
    .fh-page-wave path{fill:#fff;stroke:none}
    @media(max-width:767px){
        .fh-page-wave{height:36px}
    }
    </style>
</section>

{{-- Hidden compatibility form used only when a deal card is clicked.
     It posts into the EXISTING search route using the same field names as homepage. --}}
<form id="fhDealsSearchForm" action="{{ route('submit_search') }}" method="post" hidden>
    @csrf
    <input type="hidden" name="flight_types" value="one_way">
    <input type="hidden" name="location_from" id="fhDealsFrom">
    <input type="hidden" name="location_to" id="fhDealsTo">
    <input type="hidden" name="depature_date" id="fhDealsDepart">
    <input type="hidden" name="return_date" value="">
    <input type="hidden" name="adult" value="1">
    <input type="hidden" name="children" value="0">
    <input type="hidden" name="infant" value="0">
    <input type="hidden" name="cabin_classes" id="fhDealsCabin" value="Econom">
</form>

{{-- ============================================================
     SCREENSHOT-MATCH HOMEPAGE DEALS
     Only this homepage deals block is replaced.
     Existing search/API/FareLeaders/fare-rules remain untouched.
     ============================================================ --}}

@php
    $shotFeatured = $cards('featured')->take(4);
    $shotDomestic = $cards('domestic')->take(7);
    $shotInternational = $cards('international')->take(5);

    $holidaySource = $cards('holiday');
    if ($holidaySource->isEmpty()) {
        $holidaySource = $cards('international');
    }
    $shotHoliday = $holidaySource->take(5);

    $lastMinuteSource = $cards('last_minute');
    if ($lastMinuteSource->isEmpty()) {
        $lastMinuteSource = $cards('india');
    }
    $shotLastMinute = $lastMinuteSource->take(5);

    $shotPremium = $cards('business')->take(2)
        ->concat($cards('first')->take(2))
        ->values();

    $dealCity = function($d, $fallback='Destination') {
        return trim((string)($d->city ?: $d->title ?: $fallback));
    };

    $dealAirline = function($d) {
        return trim((string)($d->airline ?: 'Premium airline'));
    };

    $dealStops = function($d, $fallback='Non-stop') {
        $s = strtolower((string)($d->subtitle ?? ''));
        if (str_contains($s, '1 stop')) return '1 stop';
        if (str_contains($s, '2 stop')) return '2 stops';
        if (str_contains($s, 'non-stop') || str_contains($s, 'nonstop') || str_contains($s, 'direct')) return 'Non-stop';
        return $fallback;
    };

    $dealCabin = function($d, $fallback='Economy') {
        return [
            'econom'     => 'Economy',
            'peconom'    => 'Premium Economy',
            'business'   => 'Business Class',
            'firstclass' => 'First Class',
        ][strtolower((string)($d->cabin ?? ''))] ?? ($d->cabin ?: $fallback);
    };
@endphp

<style id="fh-home-deals-screenshot-v1">
.fh-deals-shot{
    --fd-navy:#081a33;
    --fd-red:#df1640;
    --fd-gold:#e7ae35;
    --fd-ink:#0c1f3a;
    --fd-muted:#6c7c96;
    --fd-line:#e4e9f1;
    --fd-soft:#f7f9fc;
    background:#fff;
    color:var(--fd-ink);
    font-family:Inter,"Segoe UI",Arial,sans-serif;
}
.fh-deals-shot *{box-sizing:border-box}
.fh-deals-shot .fd-container{
    width:min(1540px,calc(100% - 48px));
    margin:0 auto;
}
.fh-deals-shot button{font:inherit}
.fd-category-wrap{
    position:relative;
    z-index:5;
    padding:0 0 22px;
    margin-top:-20px;
}
.fd-category-bar{
    display:flex;
    align-items:center;
    gap:6px;
    overflow-x:auto;
    padding:8px;
    border:1px solid #e3e8f0;
    border-radius:16px;
    background:#fff;
    box-shadow:0 10px 28px rgba(16,38,69,.10);
    scrollbar-width:none;
}
.fd-category-bar::-webkit-scrollbar{display:none}
.fd-cat{
    flex:0 0 auto;
    min-height:48px;
    padding:0 18px;
    border:0;
    border-radius:12px;
    background:transparent;
    color:#273b59;
    font-size:13px;
    font-weight:700;
    cursor:pointer;
    white-space:nowrap;
}
.fd-cat i{margin-right:8px;color:#7283a1}
.fd-cat.active i{color:inherit}
.fd-cat{color:#4a5b78!important;font-weight:600!important;min-height:42px!important;padding:0 16px!important;border-radius:10px!important;transition:background .15s ease,color .15s ease}
.fd-cat:hover{background:#f4f7fb;color:#0d1f36}
.fd-cat.active{background:var(--fd-red)!important;color:#fff!important;font-weight:700!important;box-shadow:0 6px 16px rgba(211,29,66,.26)!important}
.fd-category-bar{width:fit-content;max-width:100%;margin:0 auto}
@media(max-width:900px){.fd-category-bar{width:auto;margin:0}}


.fd-section{padding:30px 0 18px}
.fd-section.soft{background:linear-gradient(180deg,#fafbfe,#fff)}
.fd-head{
    display:flex;
    align-items:flex-end;
    justify-content:space-between;
    gap:20px;
    margin-bottom:18px;
}
.fd-title-row{display:flex;align-items:center;gap:9px}
.fd-title-row i{font-size:20px;color:var(--fd-gold)}
.fd-head h2{
    margin:0;
    color:#12233e;
    font-size:25px;
    line-height:1.1;
    font-weight:750;
    letter-spacing:-.025em;
}
.fd-head p{
    margin:5px 0 0;
    color:#72819a;
    font-size:12.5px;
}
.fd-head a{
    color:#102b4d;
    font-size:12px;
    font-weight:700;
    text-decoration:none;
    white-space:nowrap;
}
.fd-head a i{margin-left:7px;color:var(--fd-red);font-size:10px}

.fd-grid{
    display:grid;
    gap:16px;
}
.fd-grid.featured{grid-template-columns:repeat(4,minmax(0,1fr))}
.fd-grid.international,
.fd-grid.holiday,
.fd-grid.last{grid-template-columns:repeat(5,minmax(0,1fr))}
.fd-grid.domestic{grid-template-columns:repeat(auto-fit,minmax(190px,1fr))}

.fd-card{
    position:relative;
    min-width:0;
    overflow:hidden;
    border:1px solid #dfe5ee;
    border-radius:14px;
    background:#fff;
    box-shadow:0 8px 20px rgba(25,51,83,.07);
}
button.fd-card{
    width:100%;
    padding:0;
    text-align:left;
    cursor:pointer;
}
.fd-card:hover{transform:translateY(-2px);box-shadow:0 14px 30px rgba(25,51,83,.11)}
.fd-photo{
    position:relative;
    height:188px;
    background-size:cover;
    background-position:center;
    overflow:hidden;
}
.fd-photo::after{
    content:"";
    position:absolute;
    inset:0;
    background:linear-gradient(180deg,rgba(4,13,26,.02) 22%,rgba(4,13,26,.15) 50%,rgba(4,13,26,.88) 100%);
}
.fd-badge{
    position:absolute;
    z-index:3;
    top:12px;
    left:12px;
    display:inline-flex;
    align-items:center;
    min-height:24px;
    padding:0 9px;
    border-radius:999px;
    background:var(--fd-gold);
    color:#2a2106;
    font-size:9px;
    line-height:1;
    font-weight:850;
    text-transform:uppercase;
    letter-spacing:.03em;
}
.fd-badge.red{background:var(--fd-red);color:#fff}
.fd-badge.dark{background:#0f203b;color:#fff;border:1px solid rgba(231,174,53,.75)}
.fd-heart{
    position:absolute;
    z-index:3;
    top:12px;
    right:12px;
    display:grid;
    place-items:center;
    width:26px;
    height:26px;
    border:1px solid rgba(255,255,255,.55);
    border-radius:50%;
    color:#fff;
    font-size:11px;
}
.fd-route-overlay{
    position:absolute;
    z-index:3;
    left:14px;
    right:14px;
    bottom:12px;
    color:#fff;
}
.fd-route-main{
    display:flex;
    align-items:center;
    gap:9px;
    font-size:22px;
    line-height:1;
    font-weight:800;
    letter-spacing:-.03em;
}
.fd-route-main i{font-size:12px;transform:rotate(-8deg)}
.fd-route-city{
    display:flex;
    justify-content:space-between;
    gap:10px;
    margin-top:5px;
    color:rgba(255,255,255,.92);
    font-size:10px;
}
.fd-featured-body{padding:13px 14px 12px}
.fd-airline-price{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:10px;
}
.fd-airline strong{display:block;color:#1a2940;font-size:12px}
.fd-airline small{display:block;margin-top:3px;color:#74839a;font-size:10px}
.fd-live-label{text-align:left}
.fd-live-label small{display:block;color:#77859a;font-size:9px}
.fd-live-label strong{
    display:block;
    margin-top:2px;
    color:var(--fd-red);
    font-size:18px;
    line-height:1;
}
.fd-meta{
    display:flex;
    align-items:center;
    gap:10px;
    margin-top:13px;
    padding-top:11px;
    border-top:1px solid #edf0f5;
    color:#6f7d92;
    font-size:9.5px;
    white-space:nowrap;
}
.fd-meta span{display:inline-flex;align-items:center;gap:5px}
.fd-meta i{color:#9a6f1e}
.fd-card-foot{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
    margin-top:10px;
    color:#7d5f22;
    font-size:9.5px;
}
.fd-card-foot b{color:#986d16}
.fd-card-foot span:last-child{font-weight:800}
.fd-card-foot i{margin-left:5px}

.fd-domestic{
    min-height:112px;
    padding:16px 14px 13px!important;
    border-radius:12px!important;
}
.fd-domestic .fd-mini-route{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:8px;
}
.fd-mini-airport b{display:block;color:#142640;font-size:14px}
.fd-mini-airport small{display:block;margin-top:3px;color:#78879d;font-size:8px}
.fd-mini-arrow{margin-top:6px;color:var(--fd-red);font-size:10px}
.fd-mini-fare{
    display:flex;
    justify-content:flex-end;
    margin-top:14px;
}
.fd-mini-fare strong{color:var(--fd-red);font-size:17px}
.fd-mini-fare small{margin-right:3px;color:#98a2b1;font-size:8px}

.fd-small-photo{height:135px}
.fd-simple-body{padding:11px 12px 12px}
.fd-simple-route{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:7px;
    color:#162844;
    font-size:11px;
    font-weight:800;
}
.fd-simple-route i{color:var(--fd-red);font-size:9px}
.fd-simple-sub{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:8px;
    margin-top:8px;
}
.fd-simple-sub small{color:#77859a;font-size:8.5px}
.fd-simple-sub strong{color:var(--fd-red);font-size:13px}

.fd-premium-section{padding:22px 0;background:#fff}
.fd-premium-panel{
    padding:18px;
    border-radius:16px;
    background:
        radial-gradient(circle at 100% 0,rgba(55,92,143,.20),transparent 30%),
        linear-gradient(135deg,#071a33,#0b2444);
    box-shadow:0 14px 32px rgba(6,25,48,.15);
}
.fd-premium-panel .fd-head{margin-bottom:16px}
.fd-premium-panel .fd-head h2,
.fd-premium-panel .fd-head p,
.fd-premium-panel .fd-head a{color:#fff}
.fd-premium-panel .fd-head h2 span{color:#f0b43e}
.fd-premium-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}
.fd-premium-card{
    overflow:hidden;
    padding:0!important;
    border:1px solid rgba(231,174,53,.55);
    border-radius:12px;
    background:#0a1e38;
    color:#fff;
}
.fd-premium-card .fd-photo{
    height:150px;
    border-radius:11px 11px 0 0!important;
}
.fd-premium-card .fd-route-main{font-size:15px}
.fd-premium-body{padding:11px 12px 10px}
.fd-premium-body .fd-airline strong{color:#fff}
.fd-premium-body .fd-airline small{color:#aebbd0}
.fd-premium-body .fd-live-label strong{color:#f0b43e}
.fd-premium-body .fd-meta{border-color:rgba(255,255,255,.10);color:#c3cddd}
.fd-premium-body .fd-meta i{color:#f0b43e}

.fd-holiday-card .fd-small-photo{height:130px}
.fd-holiday-body{
    display:grid;
    grid-template-columns:1fr auto;
    gap:8px;
    align-items:end;
    padding:11px 12px 12px;
}
.fd-holiday-info strong{display:block;color:#182a44;font-size:11px}
.fd-holiday-info small{display:block;margin-top:6px;color:#7d8a9e;font-size:9px}
.fd-holiday-price{text-align:right}
.fd-holiday-price small{display:block;color:#98a2b2;font-size:8px}
.fd-holiday-price strong{display:block;margin-top:2px;color:var(--fd-red);font-size:14px}

.fd-last-card{
    min-height:132px;
    padding:13px!important;
}
.fd-last-top{display:flex;align-items:center;justify-content:space-between;gap:8px}
.fd-last-label{
    padding:4px 7px;
    border-radius:999px;
    background:#ffe5eb;
    color:var(--fd-red);
    font-size:8px;
    font-weight:850;
    text-transform:uppercase;
}
.fd-last-price small{color:#8b96a7;font-size:8px}
.fd-last-price strong{color:#132640;font-size:14px}
.fd-last-route{margin-top:10px;color:#152843;font-size:11px;font-weight:800}
.fd-last-route i{margin:0 5px;color:var(--fd-red);font-size:8px}
.fd-last-meta{
    display:flex;
    justify-content:space-between;
    gap:8px;
    margin-top:7px;
    color:#7d899b;
    font-size:8.5px;
}
.fd-last-live{
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-top:14px;
    padding-top:10px;
    border-top:1px dashed #e4e8ef;
}
.fd-last-live span{color:var(--fd-red);font-size:9px;font-weight:800}
.fd-last-live strong{color:var(--fd-red);font-size:13px}

.fd-trust-strip{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:0;
    margin:22px auto;
    overflow:hidden;
    border:1px solid rgba(221,167,58,.55);
    border-radius:13px;
    background:#fff;
    box-shadow:0 7px 20px rgba(25,51,83,.05);
}
.fd-trust-item{
    display:grid;
    grid-template-columns:38px 1fr;
    align-items:center;
    gap:10px;
    padding:13px 18px;
}
.fd-trust-item i{
    display:grid;
    place-items:center;
    width:38px;
    height:38px;
    color:#d89920;
    font-size:22px;
}
.fd-trust-item strong{display:block;color:#172841;font-size:11px}
.fd-trust-item small{display:block;margin-top:3px;color:#768399;font-size:9px}

.fd-premium-banner{
    position:relative;
    min-height:150px;
    overflow:hidden;
    margin-bottom:26px;
    border-radius:14px;
    background:
        linear-gradient(90deg,rgba(5,24,48,.98) 0%,rgba(5,24,48,.89) 46%,rgba(5,24,48,.22) 74%),
        url('/assets/img/fareshunt-premium-hero-bg.png') center right/cover no-repeat;
}
.fd-premium-banner-copy{
    position:absolute;
    left:34px;
    top:50%;
    transform:translateY(-50%);
    max-width:560px;
}
.fd-premium-banner h3{
    margin:0;
    color:#f0b43e;
    font-size:26px;
    font-weight:750;
}
.fd-premium-banner p{margin:6px 0 16px;color:#fff;font-size:13px}
.fd-premium-banner a{
    display:inline-flex;
    align-items:center;
    min-height:40px;
    padding:0 17px;
    border-radius:7px;
    background:#e4a72f;
    color:#14233a;
    font-size:11px;
    font-weight:800;
    text-decoration:none;
}

@media(max-width:1200px){
    .fd-grid.featured{grid-template-columns:repeat(2,minmax(0,1fr))}
    .fd-grid.international,.fd-grid.holiday,.fd-grid.last{grid-template-columns:repeat(3,minmax(0,1fr))}
    .fd-grid.domestic{grid-template-columns:repeat(4,minmax(0,1fr))}
    .fd-premium-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
}
@media(max-width:760px){
    .fh-deals-shot .fd-container{width:calc(100% - 24px)}
    .fd-category-wrap{margin-top:-10px;padding-bottom:12px}
    .fd-category-bar{padding:6px;border-radius:13px}
    .fd-cat{min-height:42px;padding:0 13px;font-size:11px}
    .fd-section{padding:23px 0 12px}
    .fd-head{align-items:flex-start;margin-bottom:14px}
    .fd-head h2{font-size:20px}
    .fd-head p{font-size:11px}
    .fd-head a{font-size:10px}
    .fd-grid.featured,
    .fd-grid.international,
    .fd-grid.holiday,
    .fd-grid.last,
    .fd-grid.domestic,
    .fd-premium-grid{grid-template-columns:1fr}
    .fd-photo{height:205px}
    .fd-small-photo{height:180px}
    .fd-domestic{min-height:104px}
    .fd-premium-panel{padding:13px;border-radius:14px}
    .fd-premium-card .fd-photo{height:185px}
    .fd-trust-strip{grid-template-columns:1fr 1fr}
    .fd-trust-item{padding:11px 10px}
    .fd-premium-banner{min-height:205px}
    .fd-premium-banner-copy{left:20px;right:20px}
    .fd-premium-banner h3{font-size:22px}
}
@media(max-width:430px){
    .fd-head{gap:8px}
    .fd-head a{display:none}
    .fd-trust-strip{grid-template-columns:1fr}
}
</style>


<style id="fh-premium-banner-readability-fix-v2">
/* Fix only the Fly Premium, Pay Less banner text/readability.
   No other homepage design or functionality is changed. */
.fh-home .fh-deals-shot .fd-premium-banner{
    background:
        linear-gradient(90deg,
            rgba(5,24,48,.98) 0%,
            rgba(5,24,48,.94) 38%,
            rgba(5,24,48,.70) 58%,
            rgba(5,24,48,.16) 82%
        ),
        url('/assets/img/fareshunt-premium-hero-bg.png') center right/cover no-repeat!important;
}

.fh-home .fh-deals-shot .fd-premium-banner-copy{
    z-index:2!important;
}

.fh-home .fh-deals-shot .fd-premium-banner h3{
    margin:0!important;
    color:#f2b63f!important;
    font-size:26px!important;
    line-height:1.12!important;
    font-weight:750!important;
    text-shadow:0 2px 12px rgba(0,0,0,.18)!important;
    opacity:1!important;
}

.fh-home .fh-deals-shot .fd-premium-banner p{
    margin:7px 0 17px!important;
    color:#ffffff!important;
    font-size:13px!important;
    line-height:1.45!important;
    font-weight:500!important;
    opacity:1!important;
    text-shadow:0 2px 10px rgba(0,0,0,.16)!important;
}

.fh-home .fh-deals-shot .fd-premium-banner a{
    color:#10233e!important;
    background:#efb336!important;
    opacity:1!important;
}

.fh-home .fh-deals-shot .fd-premium-banner a:hover{
    background:#f7c34e!important;
    color:#10233e!important;
}

@media(max-width:760px){
    .fh-home .fh-deals-shot .fd-premium-banner h3{
        font-size:23px!important;
    }
    .fh-home .fh-deals-shot .fd-premium-banner p{
        font-size:12px!important;
    }
}
</style>


<style id="fh-premium-banner-size-fix-v3">
/* Only enlarge the Fly Premium, Pay Less banner. */
@media (min-width:761px){
    .fh-home .fh-deals-shot .fd-premium-banner{
        min-height:190px!important;
    }
    .fh-home .fh-deals-shot .fd-premium-banner-copy{
        left:40px!important;
    }
    .fh-home .fh-deals-shot .fd-premium-banner h3{
        font-size:30px!important;
    }
    .fh-home .fh-deals-shot .fd-premium-banner p{
        font-size:14px!important;
        margin:8px 0 18px!important;
    }
    .fh-home .fh-deals-shot .fd-premium-banner a{
        min-height:44px!important;
        padding:0 20px!important;
    }
}
@media (max-width:760px){
    .fh-home .fh-deals-shot .fd-premium-banner{
        min-height:220px!important;
    }
}

.fd-domestic{
    display:flex!important;
    flex-direction:column!important;
    gap:6px!important;
    min-height:0!important;
    padding:16px 16px 14px!important;
    border-radius:14px!important;
    text-align:left!important;
    transition:border-color .16s ease,box-shadow .16s ease,transform .16s ease!important;
}
.fd-domestic:hover{
    border-color:#c3d0e2!important;
    box-shadow:0 10px 26px rgba(11,37,70,.10)!important;
    transform:translateY(-2px)!important;
}
.fd-domestic .fd-mini-route{
    display:flex;
    align-items:center;
    gap:9px;
}
.fd-domestic .fd-mini-route b{
    color:#0d1f36;
    font-size:16px;
    font-weight:700;
    letter-spacing:.3px;
}
.fd-mini-arrow{color:#9fb0c6;font-size:11px}
.fd-mini-cities{
    display:block;
    color:#7c8ca3;
    font-size:11.5px;
    line-height:1.3;
    min-height:15px;
}
.fd-mini-fare{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
    margin-top:6px;
    padding-top:10px;
    border-top:1px solid #eef2f7;
}
.fd-mini-fare strong{color:var(--fd-red);font-size:16px;font-weight:700}
.fd-mini-go{color:#c3d0e2;font-size:12px;transition:color .16s ease}
.fd-domestic:hover .fd-mini-go{color:var(--fd-red)}


/* Fare label — shows only when a real price is loaded. */
.fh-live-fare::before{
    content:"from ";
    font-size:.58em;
    font-weight:600;
    opacity:.72;
}
.fh-live-fare.fh-fare-missing::before{
    content:none;
}
</style>

<section class="fh-deals-shot" id="fhHomeDeals">

    {{-- Deal category navigation --}}
    <div class="fd-category-wrap">
        <div class="fd-container">
            <nav class="fd-category-bar" aria-label="Homepage deal categories">
                <button class="fd-cat active" type="button" data-fd-filter="all"><i class="fa-solid fa-gift"></i>All Deals</button>
                <button class="fd-cat" type="button" data-fd-filter="business"><i class="fa-solid fa-couch"></i>Business Class</button>
                <button class="fd-cat" type="button" data-fd-filter="first"><i class="fa-regular fa-star"></i>First Class</button>
                <button class="fd-cat" type="button" data-fd-filter="premium-economy"><i class="fa-solid fa-crown"></i>Premium Economy</button>
                <button class="fd-cat" type="button" data-fd-filter="last-minute"><i class="fa-regular fa-clock"></i>Last Minute</button>
                <button class="fd-cat" type="button" data-fd-filter="holiday"><i class="fa-regular fa-star"></i>Weekend Getaways</button>
                <button class="fd-cat" type="button" data-fd-filter="top-rated"><i class="fa-regular fa-star"></i>Top Rated</button>
            </nav>
        </div>
    </div>

    {{-- Featured Flight Deals --}}
    @if($shotFeatured->isNotEmpty())
    <section class="fd-section" id="fdFeatured">
        <div class="fd-container">
            <div class="fd-head">
                <div>
                    <div class="fd-title-row"><i class="fa-solid fa-fire"></i><h2>Featured Flight Deals</h2></div>
                    <p>Handpicked fares to popular destinations. Limited seats!</p>
                </div>
                <a href="{{ route('all_deals') }}">View all deals <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <div class="fd-grid featured">
                @foreach($shotFeatured as $d)
                @php
                    $fidx = $loop->index;
                    $fallbackClass = ['Business Class','First Class','Business Class','Last Minute'][$fidx] ?? $dealCabin($d);
                    $badgeClass = $fidx === 3 ? 'red' : '';
                    $fromCity = trim((string)($d->from_city ?? $d->subtitle ?? $d->from_code));
                    $toCity = $dealCity($d, $d->to_code);
                @endphp
                <button class="fd-card" type="button"
                    onclick="tnbSearchDeal('{{ $d->from_code }}','{{ $d->to_code }}','{{ $d->cabin }}',{{ $d->days }})">
                    <div class="fd-photo fh-lazy-bg" data-fh-bg="{{ $cardImage($d, '/assets/img/banner-8.jpg') }}">
                        <span class="fd-badge {{ $badgeClass }}">{{ $d->badge ?: $fallbackClass }}</span>
                        <span class="fd-heart"><i class="fa-regular fa-heart"></i></span>
                        <div class="fd-route-overlay">
                            <div class="fd-route-main"><span>{{ $d->from_code }}</span><i class="fa-solid fa-plane"></i><span>{{ $d->to_code }}</span></div>
                            <div class="fd-route-city"><span>{{ $fromCity }}</span><span>{{ $toCity }}</span></div>
                        </div>
                    </div>
                    <div class="fd-featured-body">
                        <div class="fd-airline-price">
                            <div class="fd-airline">
                                <strong>{{ $dealAirline($d) }}</strong>
                                <small>{{ $dealStops($d) }}</small>
                            </div>
                            <div class="fd-live-label">
                                
                                <strong class="fh-live-fare fh-fare-missing" data-route-fare data-from="{{ $d->from_code }}" data-to="{{ $d->to_code }}" data-cabin="{{ $d->cabin }}" data-days="{{ $d->days }}">Loading…</strong>
                            </div>
                        </div>
                        <div class="fd-meta">
                            <span><i class="fa-regular fa-calendar"></i>Live dates</span>
                            <span><i class="fa-solid fa-suitcase-rolling"></i>Bag options</span>
                        </div>
                        <div class="fd-card-foot">
                            <b><i class="fa-regular fa-shield"></i> {{ $dealCabin($d, $fallbackClass) }}</b>
                            <span>View Deal <i class="fa-solid fa-arrow-right"></i></span>
                        </div>
                    </div>
                </button>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Top Deals Within USA --}}
    @if($shotDomestic->isNotEmpty())
    <section class="fd-section soft" id="fdDomestic">
        <div class="fd-container">
            <div class="fd-head">
                <div>
                    <div class="fd-title-row"><i class="fa-solid fa-flag-usa"></i><h2>Top Deals Within USA</h2></div>
                    <p>Best domestic fares — book your next trip now.</p>
                </div>
                <a href="{{ route('all_deals') }}">View all <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="fd-grid domestic">
                @foreach($shotDomestic as $d)
                <button class="fd-card fd-domestic" type="button"
                    onclick="tnbSearchDeal('{{ $d->from_code }}','{{ $d->to_code }}','{{ $d->cabin }}',{{ $d->days }})">
                    <div class="fd-mini-route">
                        <div class="fd-mini-airport"><b>{{ $d->from_code }}</b><small>{{ $d->from_city ?? 'Origin' }}</small></div>
                        <i class="fa-solid fa-arrow-right fd-mini-arrow"></i>
                        <div class="fd-mini-airport"><b>{{ $d->to_code }}</b><small>{{ $dealCity($d,'Destination') }}</small></div>
                    </div>
                    <div class="fd-mini-fare">
                        
                        <strong class="fh-live-fare fh-fare-missing" data-route-fare data-from="{{ $d->from_code }}" data-to="{{ $d->to_code }}" data-cabin="{{ $d->cabin }}" data-days="{{ $d->days }}">Loading…</strong>
                    </div>
                </button>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- International Deals --}}
    @if($shotInternational->isNotEmpty())
    <section class="fd-section" id="fdInternational">
        <div class="fd-container">
            <div class="fd-head">
                <div>
                    <div class="fd-title-row"><i class="fa-solid fa-earth-americas"></i><h2>International Deals</h2></div>
                    <p>Explore top destinations around the world.</p>
                </div>
                <a href="{{ route('all_deals') }}">View all deals <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="fd-grid international">
                @foreach($shotInternational as $d)
                @php
                    $ibadges = ['Business Class','First Class','Business Class','Premium Economy','First Class'];
                    $ibadge = $d->badge ?: ($ibadges[$loop->index] ?? $dealCabin($d, 'International'));
                    $ibadgeClass = str_contains(strtolower($ibadge),'business') ? 'red' : (str_contains(strtolower($ibadge),'first') ? 'dark' : '');
                @endphp
                <button class="fd-card" type="button" data-fd-cabin="{{ strtolower((string)($d->cabin ?? '')) }}"
                    onclick="tnbSearchDeal('{{ $d->from_code }}','{{ $d->to_code }}','{{ $d->cabin }}',{{ $d->days }})">
                    <div class="fd-photo fd-small-photo fh-lazy-bg" data-fh-bg="{{ $cardImage($d, '/assets/img/banner-3.jpg') }}">
                        <span class="fd-badge {{ $ibadgeClass }}">{{ $ibadge }}</span>
                        <span class="fd-heart"><i class="fa-regular fa-heart"></i></span>
                    </div>
                    <div class="fd-simple-body">
                        <div class="fd-simple-route"><span>{{ $d->from_code }}</span><i class="fa-solid fa-arrow-right"></i><span>{{ $d->to_code }}</span></div>
                        <div class="fd-simple-sub">
                            <small>{{ $dealStops($d) }}</small>
                            <strong class="fh-live-fare fh-fare-missing" data-route-fare data-from="{{ $d->from_code }}" data-to="{{ $d->to_code }}" data-cabin="{{ $d->cabin }}" data-days="{{ $d->days }}">Loading…</strong>
                        </div>
                    </div>
                </button>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Business & First Class Deals --}}
    @if($shotPremium->isNotEmpty())
    <section class="fd-premium-section" id="fdPremium">
        <div class="fd-container">
            <div class="fd-premium-panel">
                <div class="fd-head">
                    <div>
                        <div class="fd-title-row"><i class="fa-regular fa-gem"></i><h2>Business &amp; First Class <span>Deals</span></h2></div>
                        <p>Experience luxury travel at unbeatable prices.</p>
                    </div>
                    <a href="{{ route('all_deals') }}">View all deals <i class="fa-solid fa-arrow-right"></i></a>
                </div>

                <div class="fd-premium-grid">
                    @foreach($shotPremium as $d)
                    @php
                        $isFirst = str_contains(strtolower((string)$d->cabin),'first');
                        $pBadge = $d->badge ?: ($isFirst ? 'First Class' : 'Business Class');
                    @endphp
                    <button class="fd-premium-card" type="button" data-fd-cabin="{{ $isFirst ? 'firstclass' : 'business' }}"
                        onclick="tnbSearchDeal('{{ $d->from_code }}','{{ $d->to_code }}','{{ $d->cabin }}',{{ $d->days }})">
                        <div class="fd-photo fh-lazy-bg" data-fh-bg="{{ $cardImage($d, '/assets/img/fareshunt-premium-hero-bg.png') }}">
                            <span class="fd-badge {{ $isFirst ? 'dark' : 'red' }}">{{ $pBadge }}</span>
                            <span class="fd-heart"><i class="fa-regular fa-heart"></i></span>
                            <div class="fd-route-overlay">
                                <div class="fd-route-main"><span>{{ $d->from_code }}</span><i class="fa-solid fa-plane"></i><span>{{ $d->to_code }}</span></div>
                            </div>
                        </div>
                        <div class="fd-premium-body">
                            <div class="fd-airline-price">
                                <div class="fd-airline"><strong>{{ $dealAirline($d) }}</strong><small>{{ $dealStops($d) }}</small></div>
                                <div class="fd-live-label">
                                    
                                    <strong class="fh-live-fare gold fh-fare-missing" data-route-fare data-from="{{ $d->from_code }}" data-to="{{ $d->to_code }}" data-cabin="{{ $d->cabin }}" data-days="{{ $d->days }}">Loading…</strong>
                                </div>
                            </div>
                            <div class="fd-meta">
                                <span><i class="fa-solid fa-couch"></i>{{ $isFirst ? 'Premium suite' : 'Lounge access' }}</span>
                                <span><i class="fa-solid fa-suitcase-rolling"></i>Bag options</span>
                                <span><i class="fa-regular fa-star"></i>{{ $isFirst ? 'First Class' : 'Flat-bed seat' }}</span>
                            </div>
                        </div>
                    </button>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- Holiday Packages --}}
    @if($shotHoliday->isNotEmpty())
    <section class="fd-section soft" id="fdHoliday">
        <div class="fd-container">
            <div class="fd-head">
                <div>
                    <div class="fd-title-row"><i class="fa-solid fa-umbrella-beach"></i><h2>Holiday Packages</h2></div>
                    <p>Flight + Hotel deals for your perfect vacation.</p>
                </div>
                <a href="{{ route('all_deals') }}">View all packages <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <div class="fd-grid holiday">
                @foreach($shotHoliday as $d)
                @php $nights = max(3, min(9, (int)round(($d->days ?: 28) / 7))); @endphp
                <button class="fd-card fd-holiday-card" type="button"
                    onclick="tnbSearchDeal('{{ $d->from_code }}','{{ $d->to_code }}','{{ $d->cabin }}',{{ $d->days }})">
                    <div class="fd-photo fd-small-photo fh-lazy-bg" data-fh-bg="{{ $cardImage($d, '/assets/img/banner-8.jpg') }}">
                        <span class="fd-badge dark">{{ strtoupper($dealCity($d,$d->to_code)) }}</span>
                        <span class="fd-heart"><i class="fa-regular fa-heart"></i></span>
                    </div>
                    <div class="fd-holiday-body">
                        <div class="fd-holiday-info">
                            <strong><i class="fa-solid fa-suitcase-rolling"></i> Flight + Hotel</strong>
                            <small><i class="fa-regular fa-clock"></i> {{ $nights }} Nights</small>
                        </div>
                        <div class="fd-holiday-price">
                            
                            <strong class="fh-live-fare fh-fare-missing" data-route-fare data-from="{{ $d->from_code }}" data-to="{{ $d->to_code }}" data-cabin="{{ $d->cabin }}" data-days="{{ $d->days }}">Loading…</strong>
                        </div>
                    </div>
                </button>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Last Minute Deals --}}
    @if($shotLastMinute->isNotEmpty())
    <section class="fd-section" id="fdLastMinute">
        <div class="fd-container">
            <div class="fd-head">
                <div>
                    <div class="fd-title-row"><i class="fa-solid fa-bolt"></i><h2>Last Minute Deals</h2></div>
                    <p>Limited time only — check live availability before it changes.</p>
                </div>
                <a href="{{ route('all_deals') }}">View all deals <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <div class="fd-grid last">
                @foreach($shotLastMinute as $d)
                <button class="fd-card fd-last-card" type="button"
                    onclick="tnbSearchDeal('{{ $d->from_code }}','{{ $d->to_code }}','{{ $d->cabin }}',{{ $d->days }})">
                    <div class="fd-last-top">
                        <span class="fd-last-label">Last Minute</span>
                        <span class="fd-last-price"><strong class="fh-live-fare fh-fare-missing" data-route-fare data-from="{{ $d->from_code }}" data-to="{{ $d->to_code }}" data-cabin="{{ $d->cabin }}" data-days="{{ $d->days }}">Loading…</strong></span>
                    </div>
                    <div class="fd-last-route">{{ $d->from_code }} <i class="fa-solid fa-arrow-right"></i> {{ $d->to_code }}</div>
                    <div class="fd-last-meta"><span>{{ $dealStops($d) }}</span><span>{{ $dealCabin($d) }}</span></div>
                    <div class="fd-last-live"><span><i class="fa-solid fa-circle"></i> LIVE FARE</span><strong>Check now</strong></div>
                </button>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Screenshot-style trust strip --}}
    <div class="fd-container">
        <div class="fd-trust-strip">
            <div class="fd-trust-item"><i class="fa-solid fa-tag"></i><div><strong>Best Price Guarantee</strong><small>We match any price.</small></div></div>
            <div class="fd-trust-item"><i class="fa-solid fa-headset"></i><div><strong>24/7 Expert Support</strong><small>Real people, real support.</small></div></div>
            <div class="fd-trust-item"><i class="fa-solid fa-shield-halved"></i><div><strong>Secure Booking</strong><small>Your data is always protected.</small></div></div>
            <div class="fd-trust-item"><i class="fa-regular fa-calendar-check"></i><div><strong>Flexible Options</strong><small>Book with confidence.</small></div></div>
        </div>

        {{-- Fly Premium, Pay Less --}}
        <div class="fd-premium-banner">
            <div class="fd-premium-banner-copy">
                <h3>Fly Premium, Pay Less</h3>
                <p>Discover exclusive Business &amp; First Class fares.</p>
                <a href="{{ route('all_deals') }}">Explore Premium Deals <i class="fa-solid fa-arrow-right" style="margin-left:8px"></i></a>
            </div>
        </div>
    </div>
</section>

<section class="py-4">
    <div class="container">
        <div class="text-center mb-3">
            <h2>Explore More International Flight Deals</h2>
            <p class="mb-0">Planning an international trip from the United States? Explore our flight deal pages and compare available options.</p>
        </div>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="{{ url('/offer/usa-to-india-flight-deals-from-$699') }}" class="btn btn-outline-primary">USA to India Flight Deals</a>
            <a href="{{ url('/offer/usa-to-europe-flight-deals') }}" class="btn btn-outline-primary">USA to Europe Flight Deals</a>
        </div>
    </div>
</section>


<script>
document.addEventListener('DOMContentLoaded', function(){
    const buttons = [...document.querySelectorAll('.fd-cat[data-fd-filter]')];
    const sections = [...document.querySelectorAll('#fhHomeDeals > .fd-section, #fhHomeDeals > .fd-premium-section')];

    function applyFilter(filter){
        sections.forEach(function(section){
            let show = filter === 'all';
            if(filter === 'business' || filter === 'first') show = section.id === 'fdPremium';
            if(filter === 'premium-economy') show = section.id === 'fdInternational';
            if(filter === 'last-minute') show = section.id === 'fdLastMinute';
            if(filter === 'holiday') show = section.id === 'fdHoliday';
            if(filter === 'top-rated') show = section.id === 'fdFeatured';

            section.style.display = show ? '' : 'none';

            if(show && (filter === 'business' || filter === 'first' || filter === 'premium-economy')){
                const wanted = filter === 'business' ? 'business' : (filter === 'first' ? 'firstclass' : 'peconom');
                section.querySelectorAll('[data-fd-cabin]').forEach(function(card){
                    card.style.display = card.dataset.fdCabin === wanted ? '' : 'none';
                });
            }else{
                section.querySelectorAll('[data-fd-cabin]').forEach(function(card){ card.style.display = ''; });
            }
        });
    }

    buttons.forEach(function(btn){
        btn.addEventListener('click', function(){
            buttons.forEach(function(x){ x.classList.remove('active'); });
            btn.classList.add('active');
            applyFilter(btn.dataset.fdFilter);
        });
    });
});
</script>

<script>
/* Deals page adapter for the existing booking/search contract.
   No API/FareLeaders/fare-rule logic is changed. */
(function(){
    const nodes=[...document.querySelectorAll('#fhHomeDeals .fh-lazy-bg[data-fh-bg]')];
    if(!nodes.length) return;
    const FALLBACK='/assets/img/banner-8.jpg';

    function resolve(el){
        if(!el || el.dataset.fhBgLoaded==='1') return;
        el.dataset.fhBgLoaded='1';
        let src=(el.getAttribute('data-fh-bg') || '').trim();
        if(!src) src=FALLBACK;
        const img=new Image();
        img.onload=function(){
            el.style.setProperty('background-image','url("' + src.replace(/"/g,'\\"') + '")','important');
            el.classList.remove('fh-bg-error');
            el.classList.add('fh-bg-ready');
        };
        img.onerror=function(){
            if(src !== FALLBACK){
                const fallback=new Image();
                fallback.onload=function(){
                    el.style.setProperty('background-image','url("' + FALLBACK + '")','important');
                    el.classList.remove('fh-bg-error');
                    el.classList.add('fh-bg-ready');
                };
                fallback.onerror=function(){ el.classList.add('fh-bg-error'); };
                fallback.src=FALLBACK;
            }else{ el.classList.add('fh-bg-error'); }
        };
        img.src=src;
    }

    if('IntersectionObserver' in window){
        const io=new IntersectionObserver(entries=>{
            entries.forEach(entry=>{
                if(entry.isIntersecting){ resolve(entry.target); io.unobserve(entry.target); }
            });
        },{rootMargin:'500px 0px'});
        nodes.forEach(n=>io.observe(n));
    }else{
        nodes.forEach(resolve);
    }
})();

function tnbSearchDeal(from,to,cabin,days){
    const form = document.getElementById('fhDealsSearchForm');
    if(!form) return;

    const depart = new Date();
    depart.setDate(depart.getDate() + (Number(days) || 30));

    const mm = String(depart.getMonth() + 1).padStart(2,'0');
    const dd = String(depart.getDate()).padStart(2,'0');
    const yyyy = depart.getFullYear();

    document.getElementById('fhDealsFrom').value = from || '';
    document.getElementById('fhDealsTo').value = to || '';
    document.getElementById('fhDealsCabin').value = cabin || 'Econom';
    document.getElementById('fhDealsDepart').value = mm + '/' + dd + '/' + yyyy;

    form.submit();
}

/* Same lazy live-fare endpoint used on the homepage. */
(function(){
    const nodes = [...document.querySelectorAll('#fhHomeDeals [data-route-fare]')];
    const q = [];
    let active = 0;
    const max = 2;

    function loadFare(el){
        if(!el || el.dataset.loaded === '1') return;

        el.dataset.loaded = '1';
        active++;

        const params = new URLSearchParams({
            from: el.dataset.from || '',
            to: el.dataset.to || '',
            cabin: el.dataset.cabin || 'Econom',
            days: el.dataset.days || '30'
        });

        fetch('/ajax/route-fare?' + params.toString(), {
            method:'GET',
            headers:{'Accept':'application/json'},
            credentials:'same-origin'
        })
        .then(async response => {
            if(!response.ok) throw new Error('Fare request failed: ' + response.status);
            return response.json();
        })
        .then(j => {
            if(j && j.ok && j.label){
                el.textContent = j.label;
                el.classList.remove('fh-fare-missing');
            }else{
                el.textContent = 'Check live fare';
                el.classList.add('fh-fare-missing');
            }
        })
        .catch(err => {
            console.error('Route fare error', err);
            el.textContent = 'Check live fare';
            el.classList.add('fh-fare-missing');
        })
        .finally(() => {
            active--;
            run();
        });
    }

    function run(){
        while(active < max && q.length){
            loadFare(q.shift());
        }
    }

    const observer = ('IntersectionObserver' in window)
        ? new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if(entry.isIntersecting){
                    observer.unobserve(entry.target);
                    q.push(entry.target);
                    run();
                }
            });
        }, {rootMargin:'280px 0px'})
        : null;

    nodes.forEach(el => {
        if(observer) observer.observe(el);
        else q.push(el);
    });

    run();
})();
</script>

@endsection