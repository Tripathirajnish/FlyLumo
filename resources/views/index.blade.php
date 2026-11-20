@extends('layout')

@section('canonical')
<link rel="canonical" href="{{ url('/') }}">



@endsection

@section('header-seo')
@php $cmsv = fn($key,$default='') => $cmsSettings[$key] ?? $default; @endphp
@php
    /*
     * Safe homepage data defaults.
     */
    $homeAirports = collect($homeAirports ?? []);
    $homeAirlines = collect($homeAirlines ?? []);
    $h_blog = collect($h_blog ?? []);
    $h_usp = collect($h_usp ?? []);
@endphp
<title>Cheap Flights &amp; Flight Deals | FaresHunt</title>
<meta name="description" content="Search domestic and international flights with FaresHunt. Compare flight options and fares, explore travel deals, and get booking assistance from our U.S.-based travel team.">
<meta property="og:type" content="website">
<meta property="og:site_name" content="FaresHunt">
<meta property="og:title" content="Cheap Flights &amp; Flight Deals | FaresHunt">
<meta property="og:description" content="Search domestic and international flights with FaresHunt. Compare flight options and fares, explore travel deals, and get booking assistance from our U.S.-based travel team.">
<meta property="og:url" content="{{ url('/') }}">
<meta property="og:image" content="{{ url('/assets/img/fareshunt-premium-hero-bg-q90.webp') }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Cheap Flights &amp; Flight Deals | FaresHunt">
<meta name="twitter:description" content="Search domestic and international flights with FaresHunt. Compare flight options and fares, explore travel deals, and get booking assistance from our U.S.-based travel team.">
<meta name="twitter:image" content="{{ url('/assets/img/fareshunt-premium-hero-bg-q90.webp') }}">
    {{-- fh-hero-mobile-bg: phones (<=480px) get a 43 KB centre crop of the same hero image instead of the 158 KB desktop file. --}}
    <link rel="preload" as="image" href="/assets/img/fareshunt-premium-hero-bg-mobile-q85.webp" type="image/webp" fetchpriority="high" media="(max-width: 480px)">
    <link rel="preload" as="image" href="/assets/img/fareshunt-premium-hero-bg-q90.webp" type="image/webp" fetchpriority="high" media="(min-width: 481px)">
    {{-- fh-below-fold-cv (27 Oct 2026): desktop only. Browser hero ke neeche wale sections ka layout/paint tab tak
         skip karta hai jab tak wo screen ke paas na aayein (content-visibility:auto). Screen par aate hi same render. --}}
    <style id="fh-below-fold-cv">@media (min-width:992px){#fhHomeDeals>.fd-section,#fhHomeDeals>.fd-premium-section,#fhHomeDeals>.fd-container,#main-content>section.fh-section,#main-content>section.fh-why,#main-content>section.fh-alerts{content-visibility:auto;contain-intrinsic-size:auto 400px}}</style>
@php
    // fh-critical-css: same as layout.blade.php (inline above-the-fold subset + async full file, same position).
    $fhHomeCss = function ($href, $name) {
        $crit = @file_get_contents(public_path('assets/css/critical/'.$name.'.css'));
        if ($crit === false || trim($crit) === '') {
            return '<link rel="stylesheet" href="'.$href.'">';
        }
        return '<style data-fh-critical="'.$name.'">'.$crit.'</style>'
            .'<link rel="preload" as="style" href="'.$href.'" onload="this.onload=null;this.rel=\'stylesheet\'">'
            .'<noscript><link rel="stylesheet" href="'.$href.'"></noscript>';
    };
@endphp
{!! $fhHomeCss(asset('assets/css/fareshunt-home-responsive-v1.css').'?v=20260823c', 'fareshunt-home-responsive-v1') !!}
<style>
.ui-autocomplete{
    z-index:1000002!important;
    max-height:410px!important;
    margin-top:0!important;
    padding:8px!important;
    overflow-x:hidden!important;
    overflow-y:auto!important;
    border:1px solid #dce5f0!important;
    border-radius:21px!important;
    background:#fff!important;
    box-shadow:0 28px 75px rgba(7,27,52,.20)!important;
    font-family:Inter,"Segoe UI",Arial,sans-serif!important;
}
.ui-autocomplete .ui-menu-item{margin:0!important;padding:0!important}
.ui-autocomplete .ui-menu-item-wrapper{
    margin:0!important;
    padding:0!important;
    border:0!important;
    border-radius:15px!important;
    background:transparent!important;
}
.ui-autocomplete .ui-state-active{
    margin:0!important;
    border:0!important;
    background:transparent!important;
    color:inherit!important;
}
.fh-airport-result{
    display:grid!important;
    grid-template-columns:46px minmax(0,1fr) auto!important;
    align-items:center!important;
    gap:12px!important;
    min-height:72px!important;
    padding:10px 12px!important;
    border:1px solid transparent!important;
    border-radius:15px!important;
    background:#fff!important;
    transition:background .15s ease,border-color .15s ease!important;
}
.ui-state-active .fh-airport-result,
.fh-airport-result:hover{
    border-color:#e5ebf3!important;
    background:#f4f7fb!important;
}
.fh-airport-result-icon{
    display:grid!important;
    place-items:center!important;
    width:42px!important;
    height:42px!important;
    border-radius:13px!important;
    background:linear-gradient(145deg,#edf3f9,#f7f9fc)!important;
    color:#0c315b!important;
    font-size:15px!important;
}
.fh-airport-result-main{min-width:0!important}
.fh-airport-result-line{
    display:flex!important;
    align-items:baseline!important;
    gap:9px!important;
    min-width:0!important;
}
.fh-airport-result-code{
    flex:0 0 auto!important;
    color:#071a34!important;
    font-size:17px!important;
    line-height:1!important;
    font-weight:600!important;
}
.fh-airport-result-city{
    min-width:0!important;
    overflow:hidden!important;
    color:#183a5f!important;
    font-size:13px!important;
    font-weight:400!important;
    text-overflow:ellipsis!important;
    white-space:nowrap!important;
}
.fh-airport-result-name{
    display:block!important;
    margin-top:5px!important;
    overflow:hidden!important;
    color:#788aa3!important;
    font-size:11px!important;
    line-height:1.25!important;
    text-overflow:ellipsis!important;
    white-space:nowrap!important;
}
.fh-airport-result-country{
    max-width:90px!important;
    overflow:hidden!important;
    color:#98a7b9!important;
    font-size:10px!important;
    text-overflow:ellipsis!important;
    white-space:nowrap!important;
}
</style>

<style>
/* === FaresHunt Hero Reference V3 — final visual pass === */
.fh-home .fh-hero{
    --fh-navy:#0b1c35;
    --fh-navy2:#182c4f;
    --fh-gold:#f4c94f;
    --fh-red:#ce1736;
    --fh-ink:#101a2b;
    --fh-muted:#697995;
    position:relative;
    overflow:visible;
    min-height:640px;
    padding:66px 0 44px;
    background:
    linear-gradient(
        rgba(11, 28, 53, 0.20),
        rgba(11, 28, 53, 0.30)
    ),
    var(--fh-hero-bg);

background-size: cover;
background-position: center center;
background-repeat: no-repeat;
}
.fh-home .fh-hero::before{
    content:"";
    position:absolute;
    inset:0 0 auto;
    height:22px;
    background:linear-gradient(to bottom,rgba(255,255,255,.88),rgba(255,255,255,0));
    pointer-events:none;
}
.fh-home .fh-hero-grid-bg,
.fh-home .fh-hero-blob{display:none!important}

.fh-home .fh-hero-inner{
    position:relative;
    z-index:2;
    max-width:1480px;
    margin:0 auto;
    padding:0 30px;
}

.fh-home .fh-hero-copy{
    max-width:1380px;
    margin:0 auto 38px;
    text-align:center;
}
.fh-home .fh-pill{
    display:inline-flex!important;
    align-items:center!important;
    gap:10px!important;
    min-height:36px!important;
    padding:7px 17px!important;
    border:1px solid rgba(255,255,255,.22)!important;
    border-radius:999px!important;
    background:rgba(255,255,255,.085)!important;
    color:#f3f5f8!important;
    font-family:Inter,"Segoe UI",Arial,sans-serif!important;
    font-size:14px!important;
    line-height:1!important;
    font-weight:400!important;
    letter-spacing:0!important;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.04)!important;
}
.fh-home .fh-pill i{font-size:8px!important;color:#d2b52f!important}

.fh-home .fh-hero-copy h1{
    display:block!important;
    max-width:1420px!important;
    margin:24px auto 14px!important;
    color:#f7f8fb!important;
    font-family:Inter,"Segoe UI",Arial,sans-serif!important;
    font-size:clamp(46px,3.8vw,64px)!important;
    line-height:1.02!important;
    font-style:normal!important;
    font-weight:300!important;
    letter-spacing:-.052em!important;
    white-space:nowrap!important;
    text-shadow:none!important;
}
.fh-home .fh-hero-copy h1 span{
    color:var(--fh-gold)!important;
    font-family:inherit!important;
    font-size:inherit!important;
    font-style:normal!important;
    font-weight:300!important;
    letter-spacing:inherit!important;
}
.fh-home .fh-hero-copy p{
    max-width:850px!important;
    margin:0 auto!important;
    color:rgba(255,255,255,.79)!important;
    font-family:Inter,"Segoe UI",Arial,sans-serif!important;
    font-size:18px!important;
    line-height:1.52!important;
    font-weight:400!important;
    letter-spacing:0!important;
}

/* flatter/wider search panel like the reference */
.fh-home .fh-search-shell.fh-ref-search-shell{
    max-width:1280px!important;
    margin:0 auto!important;
    padding:24px 26px 22px!important;
    border:0!important;
    border-radius:31px!important;
    background:#fff!important;
    color:var(--fh-ink)!important;
    box-shadow:0 26px 68px rgba(2,13,33,.25)!important;
}
.fh-home .fh-ref-search-head{
    display:flex!important;
    align-items:center!important;
    justify-content:space-between!important;
    margin:0 0 22px!important;
}
.fh-home .fh-trip-tabs{
    display:flex!important;
    align-items:center!important;
    gap:4px!important;
    padding:3px!important;
    border-radius:15px!important;
    background:#f4f6fa!important;
}
.fh-home .fh-trip-tab{
    appearance:none!important;
    min-height:40px!important;
    padding:0 18px!important;
    border:0!important;
    border-radius:12px!important;
    background:transparent!important;
    color:#657493!important;
    font-family:Inter,"Segoe UI",Arial,sans-serif!important;
    font-size:14px!important;
    font-weight:500!important;
    box-shadow:none!important;
    cursor:pointer!important;
    transition:background .18s ease,color .18s ease,box-shadow .18s ease!important;
}
.fh-home .fh-trip-tab:hover{color:#111c2f!important}
.fh-home .fh-trip-tab.active{
    background:#fff!important;
    color:#0e192a!important;
    font-weight:700!important;
    box-shadow:0 3px 10px rgba(18,33,59,.10)!important;
}
.fh-home .fh-trip-tab.disabled{
    opacity:1!important;
    color:#657493!important;
    cursor:not-allowed!important;
}

/* row proportions */
.fh-home .fh-ref-search-grid-top{
    display:grid!important;
    grid-template-columns:minmax(0,1fr) 50px minmax(0,1fr) minmax(320px,.92fr)!important;
    align-items:end!important;
    gap:12px!important;
}
.fh-home .fh-ref-search-grid-bottom{
    display:grid!important;
    grid-template-columns:minmax(280px,.88fr) minmax(320px,.94fr) 210px!important;
    align-items:end!important;
    gap:12px!important;
    margin-top:15px!important;
}
.fh-home .fh-search-field{
    position:relative!important;
    display:block!important;
    min-width:0!important;
}
.fh-home .fh-search-field>span{
    display:block!important;
    margin:0 0 8px!important;
    color:#667695!important;
    font-family:Inter,"Segoe UI",Arial,sans-serif!important;
    font-size:11px!important;
    line-height:1!important;
    font-weight:700!important;
    letter-spacing:.055em!important;
}
.fh-home .fh-input,
.fh-home .fh-pax-button{
    width:100%!important;
    min-height:58px!important;
    border:1px solid #dce3ed!important;
    border-radius:20px!important;
    background:#fff!important;
    color:#132039!important;
    box-shadow:none!important;
}
.fh-home .fh-input{
    display:flex!important;
    align-items:center!important;
}
.fh-home .fh-input:focus-within,
.fh-home .fh-pax-button:focus{
    border-color:#bcc8d9!important;
    box-shadow:0 0 0 4px rgba(45,77,126,.055)!important;
}
.fh-home .fh-input>i{
    position:static!important;
    transform:none!important;
    flex:0 0 18px!important;
    width:18px!important;
    margin:0 0 0 17px!important;
    color:#7484a1!important;
    font-size:16px!important;
    text-align:center!important;
}
.fh-home .fh-input input,
.fh-home .fh-input select{
    width:100%!important;
    height:56px!important;
    min-height:0!important;
    padding:0 14px!important;
    border:0!important;
    outline:0!important;
    background:transparent!important;
    color:#132039!important;
    font-family:Inter,"Segoe UI",Arial,sans-serif!important;
    font-size:15px!important;
    font-weight:500!important;
    line-height:1!important;
    box-shadow:none!important;
}
.fh-home .fh-input input::placeholder{color:#7988a3!important;opacity:1!important}

/* swap */
.fh-home .fh-swap{
    align-self:end!important;
    width:48px!important;
    height:48px!important;
    margin:0 0 5px!important;
    border:1px solid #dde4ef!important;
    border-radius:50%!important;
    background:#fff!important;
    color:#667694!important;
    box-shadow:0 4px 13px rgba(18,34,61,.055)!important;
    transition:transform .2s ease,border-color .2s ease!important;
}
.fh-home .fh-swap:hover{transform:rotate(180deg)!important;border-color:#bdc9dc!important}

/* date pair */
.fh-home .fh-ref-dates .fh-ref-date-pair{
    display:grid!important;
    grid-template-columns:minmax(0,1fr) auto minmax(0,1fr)!important;
    align-items:center!important;
    min-height:58px!important;
    border:1px solid #dce3ed!important;
    border-radius:20px!important;
    background:#fff!important;
    overflow:hidden!important;
}
.fh-home .fh-ref-dates .fh-input{
    min-height:56px!important;
    border:0!important;
    border-radius:0!important;
}
.fh-home .fh-ref-dates .fh-input input{height:56px!important;padding-left:9px!important;padding-right:6px!important}
.fh-home .fh-ref-dates .fh-return-field input{padding-left:7px!important}
.fh-home .fh-date-dash{color:#98a3b6!important;font-size:15px!important}

/* passenger — force horizontal alignment and neutralize older absolute-position rules */
.fh-home .fh-pax-field{position:relative!important}
.fh-home .fh-pax-button{
    position:relative!important;
    display:flex!important;
    align-items:center!important;
    justify-content:flex-start!important;
    gap:0!important;
    min-height:58px!important;
    padding:0 16px!important;
    text-align:left!important;
    overflow:visible!important;
}
.fh-home .fh-pax-button>i:first-child{
    position:static!important;
    inset:auto!important;
    transform:none!important;
    display:inline-flex!important;
    align-items:center!important;
    justify-content:center!important;
    flex:0 0 20px!important;
    width:20px!important;
    height:20px!important;
    margin:0 13px 0 0!important;
    color:#7484a1!important;
    font-size:17px!important;
    line-height:1!important;
}
.fh-home .fh-pax-button>span{
    position:static!important;
    transform:none!important;
    display:flex!important;
    flex:1 1 auto!important;
    min-width:0!important;
    flex-direction:column!important;
    align-items:flex-start!important;
    justify-content:center!important;
    gap:3px!important;
    margin:0!important;
    padding:0!important;
}
.fh-home .fh-pax-button strong{
    display:block!important;
    margin:0!important;
    color:#101b2c!important;
    font-family:Inter,"Segoe UI",Arial,sans-serif!important;
    font-size:15px!important;
    line-height:1.05!important;
    font-weight:700!important;
    white-space:nowrap!important;
}
.fh-home .fh-pax-button small{
    display:block!important;
    margin:0!important;
    color:#7a88a1!important;
    font-family:Inter,"Segoe UI",Arial,sans-serif!important;
    font-size:12px!important;
    line-height:1.05!important;
}
.fh-home .fh-pax-button>i:last-child{
    position:static!important;
    inset:auto!important;
    transform:none!important;
    flex:0 0 auto!important;
    margin:0 0 0 12px!important;
    color:#7d8ca7!important;
    font-size:11px!important;
    line-height:1!important;
}

/* cabin with second line while preserving the actual select */
.fh-home .fh-ref-cabin{
    position:relative!important;
    min-height:58px!important;
    padding-right:10px!important;
}
.fh-home .fh-ref-cabin .fh-cabin-stack{
    display:flex!important;
    flex:1 1 auto!important;
    min-width:0!important;
    flex-direction:column!important;
    justify-content:center!important;
    gap:3px!important;
    margin-left:13px!important;
}
.fh-home .fh-ref-cabin .fh-cabin-stack select{
    appearance:auto!important;
    height:auto!important;
    min-height:0!important;
    padding:0 26px 0 0!important;
    color:#101b2c!important;
    font-size:15px!important;
    line-height:1.08!important;
    font-weight:700!important;
    cursor:pointer!important;
}
.fh-home .fh-ref-cabin .fh-cabin-stack small{
    display:block!important;
    max-width:220px!important;
    overflow:hidden!important;
    color:#7a88a1!important;
    font-family:Inter,"Segoe UI",Arial,sans-serif!important;
    font-size:12px!important;
    line-height:1.05!important;
    white-space:nowrap!important;
    text-overflow:ellipsis!important;
}
.fh-home .fh-ref-cabin .fh-cabin-stack select{
    position:absolute!important;
    display:none!important;
    width:1px!important;
    height:1px!important;
    min-width:1px!important;
    padding:0!important;
    margin:0!important;
    border:0!important;
    opacity:0!important;
    pointer-events:none!important;
}
.fh-home .fh-ref-cabin .fh-cabin-trigger{
    display:flex!important;
    align-items:center!important;
    justify-content:space-between!important;
    gap:10px!important;
    width:100%!important;
    min-height:0!important;
    height:auto!important;
    padding:0!important;
    border:0!important;
    background:transparent!important;
    color:#0d1f36!important;
    font-family:Inter,"Segoe UI",Arial,sans-serif!important;
    font-size:14px!important;
    font-weight:500!important;
    line-height:1.08!important;
    text-align:left!important;
    cursor:pointer!important;
    outline:none!important;
}
.fh-home .fh-ref-cabin .fh-cabin-trigger>i{
    flex:0 0 auto!important;
    margin-right:2px!important;
    color:#8795aa!important;
    font-size:12px!important;
    transition:transform .18s ease!important;
}
.fh-home .fh-ref-cabin.is-open .fh-cabin-trigger>i{
    transform:rotate(180deg)!important;
}
.fh-home .fh-ref-cabin .fh-cabin-menu{
    display:none!important;
    position:absolute!important;
    left:0!important;
    right:0!important;
    top:calc(100% + 10px)!important;
    padding:8px!important;
    border:1px solid #e0e6ef!important;
    border-radius:16px!important;
    background:#fff!important;
    box-shadow:0 22px 55px rgba(8,27,49,.17)!important;
    z-index:10030!important;
    max-height:240px!important;
    overflow-y:auto!important;
    overscroll-behavior:contain!important;
}
.fh-home .fh-ref-cabin.is-open .fh-cabin-menu{
    display:block!important;
}
.fh-home .fh-ref-cabin .fh-cabin-option{
    position:relative!important;
    display:block!important;
    width:100%!important;
    padding:9px 10px!important;
    border:0!important;
    border-radius:10px!important;
    background:transparent!important;
    text-align:left!important;
    cursor:pointer!important;
    transition:background .14s ease,transform .14s ease!important;
}
.fh-home .fh-ref-cabin .fh-cabin-option:hover{
    background:#f5f8fc!important;
    transform:translateX(1px)!important;
}
.fh-home .fh-ref-cabin .fh-cabin-option.is-active{
    background:#f1f5fb!important;
}
.fh-home .fh-ref-cabin .fh-cabin-option.is-active:after{
    content:"\2713"!important;
    position:absolute!important;
    top:50%!important;
    right:12px!important;
    transform:translateY(-50%)!important;
    color:#c91737!important;
    font-size:13px!important;
    font-weight:700!important;
}
.fh-home .fh-ref-cabin .fh-cabin-option b{
    display:block!important;
    overflow:hidden!important;
    color:#0d1f36!important;
    font-size:14px!important;
    font-weight:600!important;
    line-height:1.15!important;
    text-overflow:ellipsis!important;
    white-space:nowrap!important;
}
.fh-home .fh-ref-cabin .fh-cabin-option em{
    display:block!important;
    margin-top:0px!important;
    color:#8795aa!important;
    font-size:11px!important;
    font-style:normal!important;
    line-height:1.15!important;
}
/* reference-sized CTA */
.fh-home .fh-search-submit{
    justify-self:stretch!important;
    align-self:end!important;
    display:inline-flex!important;
    align-items:center!important;
    justify-content:center!important;
    gap:10px!important;
    width:100%!important;
    min-width:0!important;
    min-height:58px!important;
    margin:0 0 1px!important;
    padding:0 22px!important;
    border:0!important;
    border-radius:20px!important;
    background:linear-gradient(135deg,#d31b3d,#b90f2d)!important;
    color:#fff!important;
    font-family:Inter,"Segoe UI",Arial,sans-serif!important;
    font-size:15px!important;
    font-weight:700!important;
    box-shadow:0 13px 28px rgba(198,21,53,.24)!important;
    transition:transform .18s ease,box-shadow .18s ease!important;
}
.fh-home .fh-search-submit:hover{
    transform:translateY(-2px)!important;
    box-shadow:0 17px 33px rgba(198,21,53,.30)!important;
}

/* trust strip closer to panel and wave */
.fh-home .fh-hero-trust{
    display:flex!important;
    align-items:center!important;
    justify-content:center!important;
    flex-wrap:wrap!important;
    gap:30px!important;
    max-width:1100px!important;
    margin:30px auto 0!important;
    color:rgba(255,255,255,.84)!important;
    font-family:Inter,"Segoe UI",Arial,sans-serif!important;
    font-size:13px!important;
    font-weight:400!important;
}
.fh-home .fh-hero-trust span{
    display:inline-flex!important;
    align-items:center!important;
    gap:8px!important;
    white-space:nowrap!important;
}
.fh-home .fh-hero-trust i{color:#e4c22d!important}

.fh-home .fh-hero-wave{
    position:absolute!important;
    z-index:1!important;
    left:0!important;
    right:0!important;
    bottom:-1px!important;
    width:100%!important;
    height:50px!important;
}
.fh-home .fh-hero-wave path{fill:#f5f7fb!important}

/* responsive behavior */

/* === Featured Flight Deals — TARGET screenshot match === */
.fh-home .fh-featured-ref{
    padding:34px 0 28px!important;
    background:#f6f8fc!important;
    font-family:Inter,"Segoe UI",Arial,sans-serif!important;
}
.fh-home .fh-featured-ref>.container{
    width:100%!important;
    max-width:1700px!important;
    margin:0 auto!important;
    padding:0 34px!important;
}
.fh-home .fh-featured-ref .fh-section-head{
    display:flex!important;
    align-items:flex-end!important;
    justify-content:space-between!important;
    margin:0 0 38px!important;
    padding:0!important;
}
.fh-home .fh-featured-ref .fh-section-head h2{
    margin:0 0 5px!important;
    color:#071a34!important;
    font-size:30px!important;
    line-height:1.1!important;
    font-weight:400!important;
    letter-spacing:-.035em!important;
}
.fh-home .fh-featured-ref .fh-section-head p{
    margin:0!important;
    color:#66799d!important;
    font-size:15.5px!important;
    line-height:1.35!important;
    font-weight:400!important;
}
.fh-home .fh-featured-ref .fh-section-head a{
    display:inline-flex!important;
    align-items:center!important;
    gap:8px!important;
    margin:0 5px 2px 0!important;
    color:#d31335!important;
    font-size:15px!important;
    font-weight:500!important;
    text-decoration:none!important;
}
.fh-home .fh-featured-ref .fh-section-head a i{font-size:11px!important}

.fh-home .fh-featured-ref .fh-deal-grid{
    display:grid!important;
    grid-template-columns:repeat(4,minmax(0,1fr))!important;
    column-gap:24px!important;
    row-gap:25px!important;
}
.fh-home .fh-featured-ref .fh-featured-card{
    appearance:none!important;
    display:block!important;
    width:100%!important;
    min-width:0!important;
    height:auto!important;
    padding:0!important;
    overflow:hidden!important;
    border:1px solid #dce3ee!important;
    border-radius:29px!important;
    background:#fff!important;
    color:#0b1830!important;
    text-align:left!important;
    box-shadow:none!important;
    cursor:pointer!important;
    font-family:Inter,"Segoe UI",Arial,sans-serif!important;
}
.fh-home .fh-featured-ref .fh-deal-photo{
    position:relative!important;
    height:198px!important;
    min-height:198px!important;
    padding:0!important;
    background-size:cover!important;
    background-position:center center!important;
    border-radius:28px 28px 0 0!important;
    overflow:hidden!important;
}
.fh-home .fh-featured-ref .fh-deal-photo:after{
    content:"";
    position:absolute!important;
    inset:0!important;
    z-index:1!important;
    background:linear-gradient(to bottom,rgba(3,12,27,.01) 28%,rgba(2,9,20,.12) 50%,rgba(2,9,20,.82) 100%)!important;
    pointer-events:none!important;
}
.fh-home .fh-featured-ref .fh-deal-badge,
.fh-home .fh-featured-ref .fh-featured-discount{
    position:absolute!important;
    z-index:3!important;
    top:13px!important;
}
.fh-home .fh-featured-ref .fh-deal-badge{
    left:13px!important;
    padding:7px 13px!important;
    border:0!important;
    border-radius:999px!important;
    color:#fff!important;
    background:#df1738!important;
    font-size:12px!important;
    line-height:1!important;
    font-weight:500!important;
    text-transform:none!important;
}
.fh-home .fh-featured-ref .fh-featured-card[data-accent="green"] .fh-deal-badge{background:#13c65a!important}
.fh-home .fh-featured-ref .fh-featured-card[data-accent="gold"] .fh-deal-badge{background:#e4b72e!important;color:#181200!important}
.fh-home .fh-featured-ref .fh-featured-card[data-accent="purple"] .fh-deal-badge{background:#9338e7!important}
.fh-home .fh-featured-ref .fh-featured-card[data-accent="blue"] .fh-deal-badge{background:#2169e8!important}
.fh-home .fh-featured-ref .fh-featured-card[data-accent="orange"] .fh-deal-badge{background:#ff7617!important}
.fh-home .fh-featured-ref .fh-featured-discount{
    right:13px!important;
    display:inline-flex!important;
    align-items:center!important;
    gap:4px!important;
    padding:6px 10px!important;
    border-radius:999px!important;
    background:#18c965!important;
    color:#fff!important;
    font-size:12px!important;
    line-height:1!important;
    font-weight:600!important;
}
.fh-home .fh-featured-ref .fh-featured-discount i{font-size:10px!important}

.fh-home .fh-featured-ref .fh-featured-overlay{
    position:absolute!important;
    z-index:3!important;
    left:13px!important;
    right:13px!important;
    bottom:15px!important;
    display:flex!important;
    align-items:flex-end!important;
    justify-content:space-between!important;
    gap:12px!important;
    color:#fff!important;
}
.fh-home .fh-featured-ref .fh-featured-place{
    min-width:0!important;
}
.fh-home .fh-featured-ref .fh-featured-place small{
    display:block!important;
    min-height:12px!important;
    margin:0 0 7px!important;
    color:#fff!important;
    font-size:12px!important;
    line-height:1!important;
    font-weight:400!important;
}
.fh-home .fh-featured-ref .fh-featured-place small i{margin-right:5px!important;color:#f3d327!important}
.fh-home .fh-featured-ref .fh-featured-place h3{
    margin:0!important;
    color:#fff!important;
    font-size:18px!important;
    line-height:1.02!important;
    font-weight:500!important;
    letter-spacing:-.02em!important;
}
.fh-home .fh-featured-ref .fh-featured-price{
    flex:0 0 auto!important;
    max-width:205px!important;
    text-align:right!important;
    white-space:nowrap!important;
}
.fh-home .fh-featured-ref .fh-featured-price small{
    display:block!important;
    margin:0 0 3px!important;
    color:#e9edf4!important;
    font-size:12px!important;
    line-height:1!important;
    font-weight:400!important;
}
.fh-home .fh-featured-ref .fh-featured-price strong{
    display:block!important;
    overflow:hidden!important;
    color:#fff!important;
    font-size:27px!important;
    line-height:1!important;
    font-weight:400!important;
    letter-spacing:-.025em!important;
    text-overflow:ellipsis!important;
}

.fh-home .fh-featured-ref .fh-deal-body{
    display:grid!important;
    grid-template-columns:minmax(0,1fr) auto!important;
    align-items:center!important;
    gap:10px!important;
    min-height:83px!important;
    padding:13px 17px 12px!important;
    background:#fff!important;
}
.fh-home .fh-featured-ref .fh-featured-meta{min-width:0!important}
.fh-home .fh-featured-ref .fh-featured-route{
    display:flex!important;
    align-items:center!important;
    gap:8px!important;
    margin-bottom:9px!important;
}
.fh-home .fh-featured-ref .fh-airport-pill{
    display:inline-flex!important;
    align-items:center!important;
    justify-content:center!important;
    height:23px!important;
    padding:0 10px!important;
    border-radius:999px!important;
    background:#f3f5f9!important;
    color:#7182a2!important;
    font-size:12px!important;
    line-height:1!important;
    font-weight:400!important;
}
.fh-home .fh-featured-ref .fh-featured-route>i{
    color:#7182a2!important;
    font-size:11px!important;
    transform:rotate(-10deg)!important;
}
.fh-home .fh-featured-ref .fh-featured-details{
    display:flex!important;
    align-items:center!important;
    gap:13px!important;
    min-width:0!important;
    overflow:hidden!important;
    color:#66799d!important;
    font-size:12px!important;
    line-height:1!important;
    white-space:nowrap!important;
}
.fh-home .fh-featured-ref .fh-featured-details span{
    display:inline-flex!important;
    align-items:center!important;
    gap:5px!important;
    min-width:0!important;
}
.fh-home .fh-featured-ref .fh-featured-details i{font-size:11px!important}
.fh-home .fh-featured-ref .fh-featured-side{
    align-self:stretch!important;
    display:flex!important;
    flex-direction:column!important;
    align-items:flex-end!important;
    justify-content:center!important;
    gap:14px!important;
    white-space:nowrap!important;
}
.fh-home .fh-featured-ref .fh-cabin-label{
    color:#66799d!important;
    font-size:12px!important;
    line-height:1!important;
    font-weight:400!important;
}
.fh-home .fh-featured-ref .fh-featured-oldfare{
    color:#66799d!important;
    font-size:12px!important;
    line-height:1!important;
    text-decoration:line-through!important;
}

@media(max-width:1200px){
    .fh-home .fh-featured-ref>.container{padding:0 24px!important}
    .fh-home .fh-featured-ref .fh-deal-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important}
}
@media(max-width:680px){
    .fh-home .fh-featured-ref>.container{padding:0 14px!important}
    .fh-home .fh-featured-ref .fh-section-head{align-items:flex-start!important;gap:16px!important;margin-bottom:25px!important}
    .fh-home .fh-featured-ref .fh-section-head h2{font-size:25px!important}
    .fh-home .fh-featured-ref .fh-section-head p{font-size:14px!important}
    .fh-home .fh-featured-ref .fh-deal-grid{grid-template-columns:1fr!important;gap:18px!important}
    .fh-home .fh-featured-ref .fh-deal-photo{height:210px!important}
}


/* === Homepage section alignment system ===
   Keep Featured Deals and all standard homepage sections on the same horizontal grid. */
.fh-home > .fh-section > .container,
.fh-home > section.fh-section > .container{
    width:100%!important;
    max-width:1700px!important;
    margin-left:auto!important;
    margin-right:auto!important;
    padding-left:34px!important;
    padding-right:34px!important;
    box-sizing:border-box!important;
}

/* Featured section already uses the same grid; keep it locked to the shared width. */
.fh-home .fh-featured-ref > .container{
    width:100%!important;
    max-width:1700px!important;
    margin-left:auto!important;
    margin-right:auto!important;
    padding-left:34px!important;
    padding-right:34px!important;
    box-sizing:border-box!important;
}

/* Airport section: align heading and 5-column card grid with Featured Deals. */
.fh-home .fh-soft .fh-section-head{
    margin-left:0!important;
    margin-right:0!important;
}
.fh-home .fh-airport-grid{
    width:100%!important;
    display:grid!important;
    grid-template-columns:repeat(5,minmax(0,1fr))!important;
    gap:18px!important;
}
.fh-home .fh-airport-card{
    width:100%!important;
    min-width:0!important;
}

/* Keep section vertical rhythm consistent. */
.fh-home .fh-featured-ref{
    margin:0!important;
}
.fh-home .fh-featured-ref + .fh-section{
    margin-top:0!important;
}
.fh-home > .fh-section{
    box-sizing:border-box!important;
}

@media(max-width:1200px){
    .fh-home > .fh-section > .container,
    .fh-home > section.fh-section > .container,
    .fh-home .fh-featured-ref > .container{
        padding-left:24px!important;
        padding-right:24px!important;
    }

    .fh-home .fh-airport-grid{
        grid-template-columns:repeat(2,minmax(0,1fr))!important;
    }
}

@media(max-width:680px){
    .fh-home > .fh-section > .container,
    .fh-home > section.fh-section > .container,
    .fh-home .fh-featured-ref > .container{
        padding-left:14px!important;
        padding-right:14px!important;
    }

    .fh-home .fh-airport-grid{
        grid-template-columns:1fr!important;
        gap:14px!important;
    }
}

@media(max-width:1360px){
    .fh-home .fh-hero-copy h1{font-size:clamp(52px,4.7vw,74px)!important}
}
@media(max-width:1100px){
    .fh-home .fh-hero-copy h1{white-space:normal!important}
    .fh-home .fh-ref-search-grid-top{grid-template-columns:1fr 48px 1fr!important}
    .fh-home .fh-ref-dates{grid-column:1/-1!important}
    .fh-home .fh-ref-search-grid-bottom{grid-template-columns:1fr 1fr!important}
    .fh-home .fh-search-submit{grid-column:1/-1!important;width:100%!important;min-width:0!important;justify-self:stretch!important}
}
@media(max-width:760px){
    .fh-home .fh-hero{min-height:auto!important;padding:44px 0 60px!important}
    .fh-home .fh-hero-inner{padding:0 16px!important}
    .fh-home .fh-hero-copy{margin-bottom:28px!important}
    .fh-home .fh-pill{font-size:13px!important;min-height:34px!important;padding:7px 12px!important}
    .fh-home .fh-hero-copy h1{font-size:clamp(40px,11.5vw,62px)!important;margin-top:24px!important;white-space:normal!important}
    .fh-home .fh-hero-copy p{font-size:16px!important}
    .fh-home .fh-search-shell.fh-ref-search-shell{padding:19px 15px!important;border-radius:24px!important}
    .fh-home .fh-trip-tabs{width:100%!important;overflow:auto!important}
    .fh-home .fh-trip-tab{padding:0 13px!important;white-space:nowrap!important}
    .fh-home .fh-ref-search-grid-top{grid-template-columns:1fr!important}
    .fh-home .fh-swap{justify-self:center!important;margin:-2px 0 -4px!important;transform:rotate(90deg)!important}
    .fh-home .fh-swap:hover{transform:rotate(270deg)!important}
    .fh-home .fh-ref-search-grid-bottom{grid-template-columns:1fr!important}
    .fh-home .fh-search-submit{grid-column:auto!important}
    .fh-home .fh-hero-trust{gap:16px 22px!important;margin-top:26px!important;font-size:12px!important}
}

/* === Premium Search + Flatpickr V4 === */
.fh-home .fh-search-shell.fh-ref-search-shell{
    position:relative!important;
    overflow:visible!important;
    max-width:1320px!important;
    padding:26px 28px 24px!important;
    border:1px solid rgba(213,221,234,.88)!important;
    border-radius:34px!important;
    background:
        linear-gradient(180deg,rgba(255,255,255,1) 0%,rgba(252,253,255,1) 100%)!important;
    box-shadow:
        0 30px 70px rgba(2,13,33,.22),
        0 1px 0 rgba(255,255,255,.8) inset!important;
}

.fh-home .fh-ref-search-head{
    margin-bottom:24px!important;
}

.fh-home .fh-trip-tabs{
    gap:5px!important;
    padding:4px!important;
    border:1px solid #e8edf5!important;
    border-radius:16px!important;
    background:#f4f6fa!important;
}

.fh-home .fh-trip-tab{
    min-height:44px!important;
    padding:0 20px!important;
    border-radius:12px!important;
    font-size:14px!important;
    font-weight:600!important;
    color:#697896!important;
}

.fh-home .fh-trip-tab.active{
    color:#101b2d!important;
    background:#fff!important;
    box-shadow:0 5px 14px rgba(20,37,67,.12)!important;
}

.fh-home .fh-search-field>span{
    margin-bottom:9px!important;
    color:#657694!important;
    font-size:11px!important;
    letter-spacing:.07em!important;
}

.fh-home .fh-input,
.fh-home .fh-pax-button,
.fh-home .fh-ref-dates .fh-ref-date-pair{
    border:1px solid #d9e2ef!important;
    border-radius:21px!important;
    background:#fff!important;
    transition:
        border-color .18s ease,
        box-shadow .18s ease,
        transform .18s ease,
        background .18s ease!important;
}

.fh-home .fh-input:hover,
.fh-home .fh-pax-button:hover,
.fh-home .fh-ref-dates .fh-ref-date-pair:hover{
    border-color:#c3cfdf!important;
    background:#fdfefe!important;
}

.fh-home .fh-input:focus-within,
.fh-home .fh-pax-button:focus,
.fh-home .fh-ref-dates .fh-ref-date-pair:focus-within{
    border-color:#8da0be!important;
    box-shadow:0 0 0 4px rgba(63,91,137,.08)!important;
}

.fh-home .fh-input>i,
.fh-home .fh-pax-button>i:first-child{
    color:#7283a2!important;
}

.fh-home .fh-search-submit{
    min-height:58px!important;
    border-radius:19px!important;
    background:linear-gradient(135deg,#d51d3f 0%,#ba102e 100%)!important;
    box-shadow:0 14px 30px rgba(198,21,53,.26)!important;
}

.fh-home .fh-search-submit:hover{
    transform:translateY(-1px)!important;
    box-shadow:0 18px 34px rgba(198,21,53,.31)!important;
}

/* Flatpickr must sit above the hero/search/trust strip and never be clipped. */
.flatpickr-calendar{
    z-index:999999!important;
    width:340px!important;
    border:1px solid #e0e6ef!important;
    border-radius:22px!important;
    background:#fff!important;
    box-shadow:0 26px 70px rgba(8,24,50,.22)!important;
    font-family:Inter,"Segoe UI",Arial,sans-serif!important;
    overflow:hidden!important;
}

.flatpickr-calendar.open{
    display:block!important;
}

.flatpickr-months{
    min-height:58px!important;
    padding:8px 10px 0!important;
    background:#fff!important;
}

.flatpickr-months .flatpickr-month{
    height:46px!important;
    color:#101a2b!important;
}

.flatpickr-current-month{
    padding-top:9px!important;
    font-size:16px!important;
    font-weight:600!important;
}

.flatpickr-current-month .flatpickr-monthDropdown-months,
.flatpickr-current-month input.cur-year{
    font-size:16px!important;
    font-weight:600!important;
    color:#101a2b!important;
}

.flatpickr-prev-month,
.flatpickr-next-month{
    top:11px!important;
    width:38px!important;
    height:38px!important;
    padding:11px!important;
    border-radius:12px!important;
    color:#5f708e!important;
}

.flatpickr-prev-month:hover,
.flatpickr-next-month:hover{
    background:#f3f6fa!important;
}

.flatpickr-weekdays{
    height:38px!important;
    padding:0 10px!important;
}

span.flatpickr-weekday{
    color:#7b8ba5!important;
    font-size:12px!important;
    font-weight:600!important;
}

.flatpickr-days{
    width:340px!important;
    padding:4px 10px 12px!important;
}

.dayContainer{
    width:320px!important;
    min-width:320px!important;
    max-width:320px!important;
}

.flatpickr-day{
    max-width:42px!important;
    height:42px!important;
    line-height:42px!important;
    margin:1px!important;
    border-radius:12px!important;
    color:#24324a!important;
    font-size:14px!important;
    font-weight:500!important;
}

.flatpickr-day:hover{
    border-color:#edf1f6!important;
    background:#f3f6fa!important;
}

.flatpickr-day.today{
    border-color:#d51d3f!important;
    color:#c91737!important;
    background:#fff!important;
}

.flatpickr-day.selected,
.flatpickr-day.startRange,
.flatpickr-day.endRange{
    border-color:#c91737!important;
    background:#c91737!important;
    color:#fff!important;
    box-shadow:none!important;
}

.flatpickr-day.flatpickr-disabled,
.flatpickr-day.prevMonthDay,
.flatpickr-day.nextMonthDay{
    color:#c8ced8!important;
}

/* prevent dropdowns/popovers from being hidden by surrounding hero layers */
.fh-home .fh-hero,
.fh-home .fh-hero-inner,
.fh-home .fh-search-shell,
.fh-home .fh-ref-dates{
    overflow:visible!important;
}

@media(max-width:760px){
    .fh-home .fh-search-shell.fh-ref-search-shell{
        padding:20px 15px 18px!important;
        border-radius:26px!important;
    }

    .flatpickr-calendar{
        width:min(340px,calc(100vw - 24px))!important;
    }

    .flatpickr-days{
        width:100%!important;
    }

    .dayContainer{
        width:100%!important;
        min-width:100%!important;
        max-width:100%!important;
    }
}


/* === Search field containment fix V5 === */

/* Keep every search control inside the white search shell. */
.fh-home .fh-search-shell.fh-ref-search-shell{
    overflow:visible!important;
}

.fh-home .fh-ref-search-grid-top,
.fh-home .fh-ref-search-grid-bottom{
    width:100%!important;
    min-width:0!important;
}

.fh-home .fh-search-field,
.fh-home .fh-input,
.fh-home .fh-pax-field,
.fh-home .fh-ref-dates{
    min-width:0!important;
    max-width:100%!important;
}

/* Date control: one clean outer border only. */
.fh-home .fh-ref-dates .fh-ref-date-pair{
    position:relative!important;
    display:grid!important;
    grid-template-columns:minmax(0,1fr) 18px minmax(0,1fr)!important;
    align-items:center!important;
    min-height:58px!important;
    width:100%!important;
    padding:0 10px!important;
    border:1px solid #d9e2ef!important;
    border-radius:21px!important;
    background:#fff!important;
    box-shadow:none!important;
    overflow:hidden!important;
}

.fh-home .fh-ref-dates .fh-ref-date-pair:focus-within{
    border-color:#8da0be!important;
    box-shadow:0 0 0 4px rgba(63,91,137,.08)!important;
}

.fh-home .fh-ref-dates .fh-input{
    min-width:0!important;
    min-height:56px!important;
    height:56px!important;
    border:0!important;
    border-radius:0!important;
    background:transparent!important;
    box-shadow:none!important;
    overflow:hidden!important;
}

.fh-home .fh-ref-dates .fh-input:focus-within{
    border:0!important;
    box-shadow:none!important;
}

.fh-home .fh-ref-dates .fh-input>i{
    flex:0 0 18px!important;
    width:18px!important;
    margin-left:12px!important;
}

.fh-home .fh-ref-dates .fh-input input,
.fh-home .fh-ref-dates .fh-input .flatpickr-input,
.fh-home .fh-ref-dates .fh-input .form-control,
.fh-home .fh-ref-dates .fh-input input.flatpickr-input,
.fh-home .fh-ref-dates .fh-input input.form-control{
    min-width:0!important;
    width:100%!important;
    height:56px!important;
    padding:0 8px!important;
    border:0!important;
    border-radius:0!important;
    outline:0!important;
    background:transparent!important;
    box-shadow:none!important;
}

/* Flatpickr alt inputs are inserted beside the original input. */
.fh-home .fh-ref-dates .fh-input input[readonly]{
    min-width:0!important;
    width:100%!important;
    border:0!important;
    outline:0!important;
    box-shadow:none!important;
}

.fh-home .fh-date-dash{
    display:flex!important;
    align-items:center!important;
    justify-content:center!important;
    width:18px!important;
    color:#98a3b6!important;
    font-size:16px!important;
    pointer-events:none!important;
}

/* Passenger field: keep icon, copy and chevron fully contained. */
.fh-home .fh-pax-field{
    position:relative!important;
    overflow:visible!important;
}

.fh-home .fh-pax-button{
    position:relative!important;
    display:grid!important;
    grid-template-columns:22px minmax(0,1fr) 18px!important;
    align-items:center!important;
    width:100%!important;
    min-width:0!important;
    min-height:58px!important;
    padding:0 16px!important;
    border-radius:21px!important;
    overflow:hidden!important;
    box-sizing:border-box!important;
}

.fh-home .fh-pax-button>i:first-child{
    position:static!important;
    width:22px!important;
    height:22px!important;
    margin:0!important;
    display:flex!important;
    align-items:center!important;
    justify-content:center!important;
    transform:none!important;
}

.fh-home .fh-pax-button>span{
    min-width:0!important;
    margin:0!important;
    padding:0 12px!important;
    display:flex!important;
    flex-direction:column!important;
    align-items:flex-start!important;
    justify-content:center!important;
    overflow:hidden!important;
}

.fh-home .fh-pax-button strong,
.fh-home .fh-pax-button small{
    width:100%!important;
    overflow:hidden!important;
    text-overflow:ellipsis!important;
    white-space:nowrap!important;
}

.fh-home .fh-pax-button>i:last-child{
    position:static!important;
    width:18px!important;
    height:18px!important;
    margin:0!important;
    display:flex!important;
    align-items:center!important;
    justify-content:center!important;
    transform:none!important;
}

/* Bottom row proportions: passenger + cabin + CTA stay inside the panel. */
.fh-home .fh-ref-search-grid-bottom{
    grid-template-columns:minmax(0,1fr) minmax(0,1.08fr) minmax(190px,210px)!important;
    gap:12px!important;
}

/* Ensure passenger popover opens below the control rather than pushing layout. */
.fh-home .tnb-pax-popover{
    z-index:10030!important;
    padding:10px!important;
}
@media(min-width:761px){
    .fh-home .tnb-pax-popover{
        position:absolute!important;
        left:0!important;
        right:auto!important;
        top:calc(100% + 1px)!important;
        width:min(360px,calc(100vw - 32px))!important;
        max-width:360px!important;
    }
}
@media(min-width:761px){
    .fh-home .tnb-pax-popover{
        position:absolute!important;
        left:0!important;
        right:auto!important;
        top:calc(100% + 1px)!important;
        width:min(360px,calc(100vw - 32px))!important;
        max-width:360px!important;
    }
}
.fh-home .tnb-pax-popover .tnb-pax-row{
    min-height:0!important;
    padding:9px 6px!important;
}
.fh-home .tnb-pax-popover .tnb-pax-row>div:first-child{
    display:flex!important;
    flex-direction:column!important;
    gap:2px!important;
}
.fh-home .tnb-pax-popover .tnb-pax-row strong{
    font-size:14px!important;
    line-height:1.15!important;
}
.fh-home .tnb-pax-popover .tnb-pax-row small{
    font-size:11px!important;
    line-height:1.15!important;
}
.fh-home .tnb-pax-popover .tnb-counter{
    gap:8px!important;
}
.fh-home .tnb-pax-popover .tnb-counter button{
    width:44px!important;
    height:44px!important;
    min-height:44px!important;
    padding:0!important;
    font-size:14px!important;
    line-height:1!important;
}
.fh-home .tnb-pax-popover .tnb-pax-done{
    min-height:0!important;
    margin-top:8px!important;
    padding:9px!important;
    font-size:14px!important;
}
/* Keep generated Flatpickr calendar above every search/hero layer. */
.flatpickr-calendar{
    z-index:1000000!important;
}

@media(max-width:1100px){
    .fh-home .fh-ref-search-grid-bottom{
        grid-template-columns:1fr 1fr!important;
    }
    .fh-home .fh-search-submit{
        grid-column:1/-1!important;
        width:100%!important;
        min-width:0!important;
    }
}

@media(max-width:760px){
    .fh-home .fh-ref-search-grid-bottom{
        grid-template-columns:1fr!important;
    }

    .fh-home .fh-search-submit{
        grid-column:auto!important;
    }

    .fh-home .fh-ref-dates .fh-ref-date-pair{
        grid-template-columns:minmax(0,1fr) 14px minmax(0,1fr)!important;
        padding:0 6px!important;
    }

    .fh-home .fh-pax-button{
        grid-template-columns:22px minmax(0,1fr) 18px!important;
    }

    .fh-home .tnb-pax-popover{
        position:absolute!important;
        left:0!important;
        right:0!important;
        top:calc(100% + 1px)!important;
        bottom:auto!important;
        width:auto!important;
        max-width:none!important;
        max-height:min(76vh,560px)!important;
        overflow-y:auto!important;
        z-index:1000050!important;
    }
}


</style>
{!! $fhHomeCss(asset('assets/css/fareshunt-home-belowfold-v1.css').'?v=20260929a', 'fareshunt-home-belowfold-v1') !!}



<style>
/* SEARCH BAR V22 — final premium polish */
.fh-home .fh-search-shell.fh-ref-search-shell{
    border:1px solid rgba(222,229,239,.94)!important;
    background:
        radial-gradient(circle at 5% 0%,rgba(231,238,248,.48),transparent 24%),
        linear-gradient(180deg,#fff 0%,#fcfdff 100%)!important;
    box-shadow:
        0 34px 82px rgba(2,13,33,.24),
        inset 0 1px 0 rgba(255,255,255,.98)!important;
}
.fh-home .fh-search-field>span{
    margin-left:3px!important;
    color:#687a97!important;
    font-size:10px!important;
    font-weight:500!important;
    letter-spacing:.11em!important;
}
.fh-home .fh-input,
.fh-home .fh-pax-button,
.fh-home .fh-ref-dates .fh-ref-date-pair{
    border-color:#dbe3ee!important;
    background:linear-gradient(180deg,#fff,#fdfefe)!important;
    box-shadow:0 2px 5px rgba(12,34,62,.025)!important;
}
.fh-home .fh-input:hover,
.fh-home .fh-pax-button:hover,
.fh-home .fh-ref-dates .fh-ref-date-pair:hover{
    border-color:#bfcddd!important;
    box-shadow:0 7px 18px rgba(15,42,73,.055)!important;
}
.fh-home .fh-input:focus-within,
.fh-home .fh-ref-dates .fh-ref-date-pair:focus-within{
    border-color:#849bb9!important;
    background:#fff!important;
    box-shadow:
        0 0 0 4px rgba(62,94,143,.085),
        0 9px 24px rgba(15,42,73,.06)!important;
}
.fh-home #location_from.fh-airport-picked,
.fh-home #location_to.fh-airport-picked{
    color:#071a34!important;
    font-weight:500!important;
}
.fh-home .fh-swap{
    border-color:#d8e1ec!important;
    background:linear-gradient(145deg,#fff,#f5f8fb)!important;
    color:#49627f!important;
    box-shadow:0 7px 18px rgba(14,39,69,.07)!important;
}
.fh-home .fh-search-submit{
    background:linear-gradient(135deg,#dc2346 0%,#bd102f 100%)!important;
    box-shadow:
        0 15px 31px rgba(198,21,53,.25),
        inset 0 1px 0 rgba(255,255,255,.18)!important;
}
@media(max-width:760px){
    .ui-autocomplete{
        left:auto!important;
        right:auto!important;
        max-height:270px!important;
    }
    .fh-airport-result{
        grid-template-columns:42px minmax(0,1fr)!important;
        min-height:54px!important;
        padding:7px 9px!important;
        border-radius:12px!important;
    }
    .fh-airport-result-country{display:none!important}
}
</style>


<style>
/* =========================================================
   SEARCH BAR V23 — PREMIUM EXECUTIVE POLISH
   Visual-only refinement. Existing form / API / JS untouched.
   ========================================================= */

.fh-home .fh-search-shell.fh-ref-search-shell{
    max-width:1360px!important;
    padding:28px 30px 26px!important;
    border:1px solid rgba(217,226,238,.95)!important;
    border-radius:32px!important;
    background:
        radial-gradient(circle at 8% 0%,rgba(232,239,248,.58),transparent 24%),
        linear-gradient(180deg,#ffffff 0%,#fbfcfe 100%)!important;
    box-shadow:
        0 34px 86px rgba(1,14,34,.26),
        0 2px 0 rgba(255,255,255,.95) inset,
        0 -1px 0 rgba(17,42,72,.025) inset!important;
}

/* Trip selector */
.fh-home .fh-trip-tabs{
    gap:5px!important;
    padding:4px!important;
    border:1px solid #e6ecf4!important;
    border-radius:16px!important;
    background:#f3f6fa!important;
    box-shadow:inset 0 1px 2px rgba(19,42,70,.03)!important;
}
.fh-home .fh-trip-tab{
    min-height:42px!important;
    padding:0 19px!important;
    border-radius:12px!important;
    color:#6f7f99!important;
    font-size:13px!important;
    font-weight:500!important;
    letter-spacing:0!important;
}
.fh-home .fh-trip-tab.active{
    background:#fff!important;
    color:#0b1d34!important;
    box-shadow:
        0 5px 14px rgba(15,39,70,.10),
        inset 0 1px 0 rgba(255,255,255,.9)!important;
}

/* Field labels */
.fh-home .fh-search-field>span{
    margin:0 0 8px 4px!important;
    color:#73829a!important;
    font-size:10px!important;
    line-height:1!important;
    font-weight:500!important;
    letter-spacing:.105em!important;
}

/* Inputs */
.fh-home .fh-input,
.fh-home .fh-pax-button,
.fh-home .fh-ref-dates .fh-ref-date-pair{
    min-height:60px!important;
    border:1px solid #dbe4ef!important;
    border-radius:19px!important;
    background:
        linear-gradient(180deg,#ffffff 0%,#fcfdff 100%)!important;
    box-shadow:
        0 2px 6px rgba(18,42,72,.025),
        inset 0 1px 0 rgba(255,255,255,.98)!important;
    transition:
        border-color .16s ease,
        box-shadow .16s ease,
        background .16s ease,
        transform .16s ease!important;
}

.fh-home .fh-input:hover,
.fh-home .fh-pax-button:hover,
.fh-home .fh-ref-dates .fh-ref-date-pair:hover{
    border-color:#b9c7d8!important;
    background:#fff!important;
    box-shadow:
        0 8px 22px rgba(17,43,74,.06),
        inset 0 1px 0 rgba(255,255,255,.98)!important;
}

.fh-home .fh-input:focus-within,
.fh-home .fh-pax-button:focus{
    border-color:#8299b7!important;
    background:#fff!important;
    box-shadow:
        0 0 0 4px rgba(55,91,143,.085),
        0 10px 26px rgba(17,43,74,.07)!important;
}

.fh-home .fh-ref-dates .fh-ref-date-pair:focus-within{
    border-color:#8299b7!important;
    background:#fff!important;
    box-shadow:none!important;
}

/* Icons */
.fh-home .fh-input>i,
.fh-home .fh-pax-button>i:first-child{
    color:#607898!important;
    font-size:15px!important;
}

/* Search text */
.fh-home .fh-input input,
.fh-home .fh-input select{
    color:#0b1c32!important;
    font-size:15px!important;
    font-weight:400!important;
}
.fh-home .fh-input input::placeholder{
    color:#9aa7b9!important;
    opacity:1!important;
}
.fh-home #location_from.fh-airport-picked,
.fh-home #location_to.fh-airport-picked{
    color:#071a34!important;
    font-weight:500!important;
}

/* Swap button */
.fh-home .fh-swap{
    width:48px!important;
    height:48px!important;
    margin-bottom:6px!important;
    border:1px solid #d9e2ed!important;
    border-radius:50%!important;
    background:linear-gradient(145deg,#ffffff,#f3f6fa)!important;
    color:#49627f!important;
    box-shadow:
        0 8px 20px rgba(14,39,69,.08),
        inset 0 1px 0 rgba(255,255,255,.9)!important;
}
.fh-home .fh-swap:hover{
    border-color:#b4c3d5!important;
    color:#153b64!important;
    box-shadow:0 12px 26px rgba(14,39,69,.12)!important;
}

/* Passenger */
.fh-home .fh-pax-button{
    padding:0 17px!important;
}
.fh-home .fh-pax-button strong{
    color:#0d1f36!important;
    font-size:14px!important;
    font-weight:500!important;
}
.fh-home .fh-pax-button small{
    color:#8795aa!important;
    font-size:11px!important;
}

/* Cabin */
.fh-home .fh-ref-cabin .fh-cabin-stack select{
    color:#0d1f36!important;
    font-size:14px!important;
    font-weight:500!important;
}
.fh-home .fh-ref-cabin .fh-cabin-stack small{
    color:#8795aa!important;
    font-size:11px!important;
}

/* Dates */
.fh-home .fh-ref-dates .fh-ref-date-pair{
    min-height:60px!important;
    padding:0 11px!important;
}
.fh-home .fh-ref-dates .fh-input,
.fh-home .fh-ref-dates .fh-input input{
    height:58px!important;
    min-height:58px!important;
}
.fh-home .fh-date-dash{
    color:#a7b1c0!important;
    font-size:14px!important;
}

/* CTA */
.fh-home .fh-search-submit{
    min-height:60px!important;
    width:220px!important;
    min-width:220px!important;
    border-radius:19px!important;
    background:linear-gradient(135deg,#df2449 0%,#c21132 56%,#b30d2b 100%)!important;
    color:#fff!important;
    font-size:14px!important;
    font-weight:600!important;
    letter-spacing:.005em!important;
    box-shadow:
        0 16px 34px rgba(194,17,50,.28),
        inset 0 1px 0 rgba(255,255,255,.18)!important;
}
.fh-home .fh-search-submit:hover{
    transform:translateY(-2px)!important;
    box-shadow:
        0 20px 40px rgba(194,17,50,.33),
        inset 0 1px 0 rgba(255,255,255,.2)!important;
}

/* Premium airport dropdown */
.ui-autocomplete{
    border:1px solid #dbe4ef!important;
    border-radius:22px!important;
    background:#fff!important;
    box-shadow:0 30px 80px rgba(8,28,54,.22)!important;
    padding:9px!important;
}
.fh-airport-result{
    min-height:74px!important;
    padding:11px 12px!important;
    border-radius:15px!important;
}
.ui-state-active .fh-airport-result,
.fh-airport-result:hover{
    border-color:#e1e8f1!important;
    background:linear-gradient(180deg,#f6f9fc,#f2f6fb)!important;
}
.fh-airport-result-icon{
    background:linear-gradient(145deg,#eaf1f8,#f7f9fc)!important;
    color:#173f6b!important;
}
.fh-airport-result-code{
    color:#081d36!important;
    font-size:17px!important;
    font-weight:600!important;
}
.fh-airport-result-city{
    color:#294765!important;
}
.fh-airport-result-name{
    color:#7b8ca4!important;
}

/* Better rhythm between rows */
.fh-home .fh-ref-search-grid-top{
    gap:13px!important;
}
.fh-home .fh-ref-search-grid-bottom{
    gap:13px!important;
    margin-top:16px!important;
}

/* Subtle divider under tabs */
.fh-home .fh-ref-search-head{
    margin-bottom:23px!important;
    padding-bottom:2px!important;
}

@media(max-width:1100px){
    .fh-home .fh-search-shell.fh-ref-search-shell{
        padding:24px!important;
    }
    .fh-home .fh-search-submit{
        width:100%!important;
        min-width:0!important;
    }
}

@media(max-width:760px){
    .fh-home .fh-search-shell.fh-ref-search-shell{
        padding:18px 14px 16px!important;
        border-radius:24px!important;
    }
    .fh-home .fh-input,
    .fh-home .fh-pax-button,
    .fh-home .fh-ref-dates .fh-ref-date-pair{
        border-radius:17px!important;
    }
    .fh-home .fh-swap{
        margin:0 auto!important;
    }
}
</style>


<style id="fh-search-v24-fixes">
/* =========================================================
   V24 — Airport dropdown blue + full date visibility
   ========================================================= */

/* 1) Premium BLUE airport dropdown */
.ui-autocomplete{
    border:1px solid #b9c9dc!important;
    background:#eef5ff!important;
    box-shadow:0 26px 72px rgba(11,43,82,.20)!important;
}
.fh-airport-result{
    background:#eef5ff!important;
    border-color:transparent!important;
}
.ui-state-active .fh-airport-result,
.fh-airport-result:hover{
    background:linear-gradient(135deg,#0f3b68 0%,#164f86 100%)!important;
    border-color:#0f3b68!important;
    box-shadow:0 8px 20px rgba(15,59,104,.20)!important;
}
.ui-state-active .fh-airport-result-icon,
.fh-airport-result:hover .fh-airport-result-icon{
    background:rgba(255,255,255,.12)!important;
    color:#fff!important;
}
.ui-state-active .fh-airport-result-code,
.ui-state-active .fh-airport-result-city,
.ui-state-active .fh-airport-result-name,
.ui-state-active .fh-airport-result-country,
.fh-airport-result:hover .fh-airport-result-code,
.fh-airport-result:hover .fh-airport-result-city,
.fh-airport-result:hover .fh-airport-result-name,
.fh-airport-result:hover .fh-airport-result-country{
    color:#fff!important;
}
.fh-airport-result-code{color:#0a2d55!important}
.fh-airport-result-city{color:#264c72!important}
.fh-airport-result-name{color:#6f87a3!important}

/* 2) Date field — selected date should NEVER be clipped */
.fh-home .fh-ref-dates{
    min-width:0!important;
}
.fh-home .fh-ref-dates .fh-ref-date-pair{
    width:100%!important;
    min-width:0!important;
    padding:0 10px!important;
    overflow:visible!important;
}
.fh-home .fh-ref-dates .fh-input{
    min-width:0!important;
    width:100%!important;
    padding-left:10px!important;
    padding-right:6px!important;
    overflow:visible!important;
}
.fh-home .fh-ref-dates .fh-input input{
    width:100%!important;
    min-width:0!important;
    max-width:none!important;
    padding:0!important;
    overflow:visible!important;
    text-overflow:clip!important;
    white-space:nowrap!important;
    font-size:14px!important;
    letter-spacing:-.01em!important;
}
.fh-home .fh-ref-dates .fh-ref-date-pair > .fh-input:first-child{
    flex:1.18 1 0!important;
}
.fh-home .fh-ref-dates .fh-ref-date-pair > .fh-input:last-child{
    flex:1 1 0!important;
}
.fh-home .fh-date-dash{
    flex:0 0 22px!important;
    width:22px!important;
    text-align:center!important;
}

/* Give the date column slightly more room on desktop */
@media(min-width:1101px){
    .fh-home .fh-ref-search-grid-top{
        grid-template-columns:minmax(0,1fr) 48px minmax(0,1fr) minmax(370px,1.05fr)!important;
    }
}

/* On smaller screens keep full date readable */
@media(max-width:1100px){
    .fh-home .fh-ref-dates .fh-input input{
        font-size:13px!important;
    }
}
</style>


<style id="fh-airport-dropdown-v25-fix">
/* =========================================================
   V25 — Airport dropdown normal state + hover state
   Prevents the first result looking selected before selection.
   ========================================================= */

/* Dropdown container */
.ui-autocomplete{
    background:#ffffff!important;
    border:1px solid #d7e1ee!important;
    box-shadow:0 22px 55px rgba(11,37,70,.16)!important;
}

/* NORMAL result: white / very light surface with readable navy text */
.ui-autocomplete .fh-airport-result,
.ui-autocomplete .ui-menu-item-wrapper .fh-airport-result{
    background:#ffffff!important;
    border:1px solid transparent!important;
    box-shadow:none!important;
}

.ui-autocomplete .fh-airport-result-icon{
    background:#edf4fb!important;
    color:#103b68!important;
}

.ui-autocomplete .fh-airport-result-code{
    color:#071a34!important;
}
.ui-autocomplete .fh-airport-result-city{
    color:#294a6d!important;
}
.ui-autocomplete .fh-airport-result-name{
    color:#71849d!important;
}
.ui-autocomplete .fh-airport-result-country{
    color:#91a0b3!important;
}

/* ACTIVE/HOVER result only */
.ui-autocomplete .ui-menu-item-wrapper.ui-state-active .fh-airport-result,
.ui-autocomplete .ui-state-active .fh-airport-result,
.ui-autocomplete .fh-airport-result:hover{
    background:linear-gradient(135deg,#113c69 0%,#195486 100%)!important;
    border-color:#113c69!important;
    box-shadow:0 8px 20px rgba(15,59,104,.18)!important;
}

.ui-autocomplete .ui-menu-item-wrapper.ui-state-active .fh-airport-result-icon,
.ui-autocomplete .ui-state-active .fh-airport-result-icon,
.ui-autocomplete .fh-airport-result:hover .fh-airport-result-icon{
    background:rgba(255,255,255,.13)!important;
    color:#ffffff!important;
}

.ui-autocomplete .ui-menu-item-wrapper.ui-state-active .fh-airport-result-code,
.ui-autocomplete .ui-menu-item-wrapper.ui-state-active .fh-airport-result-city,
.ui-autocomplete .ui-menu-item-wrapper.ui-state-active .fh-airport-result-name,
.ui-autocomplete .ui-menu-item-wrapper.ui-state-active .fh-airport-result-country,
.ui-autocomplete .ui-state-active .fh-airport-result-code,
.ui-autocomplete .ui-state-active .fh-airport-result-city,
.ui-autocomplete .ui-state-active .fh-airport-result-name,
.ui-autocomplete .ui-state-active .fh-airport-result-country,
.ui-autocomplete .fh-airport-result:hover .fh-airport-result-code,
.ui-autocomplete .fh-airport-result:hover .fh-airport-result-city,
.ui-autocomplete .fh-airport-result:hover .fh-airport-result-name,
.ui-autocomplete .fh-airport-result:hover .fh-airport-result-country{
    color:#ffffff!important;
}

/* Remove the washed-out selected-looking state from jQuery UI */
.ui-autocomplete .ui-menu-item-wrapper.ui-state-active{
    background:transparent!important;
    border:0!important;
    color:inherit!important;
}
</style>


<style id="fh-airport-dropdown-v26-active-fix">
/* jQuery UI adds .ui-state-active to the SAME .fh-airport-result element.
   These same-element selectors prevent white text on a white card. */
.ui-autocomplete .fh-airport-result.ui-state-active,
.ui-autocomplete .fh-airport-result:hover,
.ui-autocomplete li.ui-menu-item > .fh-airport-result.ui-state-active{
    background:linear-gradient(135deg,#123f6e 0%,#1d5b91 100%)!important;
    border-color:#123f6e!important;
    box-shadow:0 9px 22px rgba(15,59,104,.20)!important;
    color:#fff!important;
}
.ui-autocomplete .fh-airport-result.ui-state-active .fh-airport-result-icon,
.ui-autocomplete .fh-airport-result:hover .fh-airport-result-icon{
    background:rgba(255,255,255,.14)!important;
    color:#fff!important;
}
.ui-autocomplete .fh-airport-result.ui-state-active .fh-airport-result-code,
.ui-autocomplete .fh-airport-result.ui-state-active .fh-airport-result-city,
.ui-autocomplete .fh-airport-result.ui-state-active .fh-airport-result-name,
.ui-autocomplete .fh-airport-result.ui-state-active .fh-airport-result-country,
.ui-autocomplete .fh-airport-result:hover .fh-airport-result-code,
.ui-autocomplete .fh-airport-result:hover .fh-airport-result-city,
.ui-autocomplete .fh-airport-result:hover .fh-airport-result-name,
.ui-autocomplete .fh-airport-result:hover .fh-airport-result-country{
    color:#fff!important;
}
/* Normal unselected result remains readable */
.ui-autocomplete .fh-airport-result:not(.ui-state-active){
    background:#fff!important;
}
</style>

<style id="fareshunt-home-mobile-final-v3">
/*
 * FINAL MOBILE OVERRIDES
 * Placed AFTER all legacy homepage style blocks so these rules actually win.
 * No backend/search/API/fare-rule logic is changed.
 */
@media (max-width:760px){

    html, body{
        max-width:100%!important;
        overflow-x:hidden!important;
    }

    .fh-home{
        max-width:100%!important;
        overflow-x:hidden!important;
    }

    /* HERO — compact and balanced */
    .fh-home .fh-hero{
        min-height:auto!important;
        padding:24px 0 30px!important;
    }

    .fh-home .fh-hero-inner{
        padding-left:12px!important;
        padding-right:12px!important;
    }

    .fh-home .fh-hero-copy{
        margin-bottom:16px!important;
        text-align:center!important;
    }

    .fh-home .fh-pill{
        min-height:34px!important;
        padding:7px 12px!important;
        font-size:12px!important;
        white-space:normal!important;
    }

    .fh-home .fh-hero-copy h1{
        max-width:360px!important;
        margin:18px auto 0!important;
        font-size:clamp(32px,9.5vw,42px)!important;
        line-height:1.04!important;
        letter-spacing:-.035em!important;
        white-space:normal!important;
        text-wrap:balance!important;
    }

    .fh-home .fh-hero-copy p{
        max-width:360px!important;
        margin:18px auto 0!important;
        font-size:14px!important;
        line-height:1.5!important;
    }

    /* SEARCH SHELL */
    .fh-home .fh-search-shell.fh-ref-search-shell{
        width:100%!important;
        max-width:100%!important;
        padding:12px!important;
        border-radius:20px!important;
        margin:0 auto!important;
    }

    .fh-home .fh-search-head{
        margin-bottom:12px!important;
    }

    .fh-home .fh-trip-tabs{
        display:grid!important;
        grid-template-columns:repeat(3,minmax(0,1fr))!important;
        width:100%!important;
        gap:5px!important;
        overflow:visible!important;
    }

    .fh-home .fh-trip-tab{
        width:100%!important;
        min-width:0!important;
        min-height:44px!important;
        padding:0 6px!important;
        font-size:12px!important;
        justify-content:center!important;
        white-space:nowrap!important;
    }

    /*
     * IMPORTANT:
     * Use FLEX here instead of the old grid. This guarantees the SWAP button
     * stays physically between FROM and TO and can never land beside DATES.
     */
    .fh-home #fhStandardSearch .fh-ref-search-grid-top{
        display:flex!important;
        flex-direction:column!important;
        grid-template-columns:none!important;
        gap:8px!important;
        width:100%!important;
    }

    .fh-home #fhStandardSearch .fh-ref-search-grid-top > label:nth-child(1){
        order:1!important;
    }

    .fh-home #fhStandardSearch .fh-ref-search-grid-top > #fhSwapAirports{
        order:2!important;
    }

    .fh-home #fhStandardSearch .fh-ref-search-grid-top > label:nth-child(3){
        order:3!important;
    }

    .fh-home #fhStandardSearch .fh-ref-search-grid-top > .fh-ref-dates{
        order:4!important;
    }

    .fh-home .fh-search-field,
    .fh-home .fh-ref-dates{
        width:100%!important;
        min-width:0!important;
    }

    .fh-home .fh-input,
    .fh-home .fh-pax-button{
        width:100%!important;
        min-width:0!important;
        min-height:54px!important;
        border-radius:16px!important;
    }

    /* SWAP: its own row BETWEEN FROM and TO */
    .fh-home #fhSwapAirports{
        position:relative!important;
        inset:auto!important;
        display:flex!important;
        align-items:center!important;
        justify-content:center!important;
        width:42px!important;
        min-width:42px!important;
        height:42px!important;
        min-height:42px!important;
        margin:-2px auto!important;
        transform:rotate(90deg)!important;
        z-index:2!important;
    }

    .fh-home #fhSwapAirports:hover{
        transform:rotate(270deg)!important;
    }

    /* DATES: force actual Depart + Return fields into two stacked rows. */
    .fh-home .fh-ref-dates .fh-ref-date-pair{
        display:flex!important;
        flex-direction:column!important;
        grid-template-columns:none!important;
        width:100%!important;
        height:auto!important;
        min-height:0!important;
        gap:8px!important;
        padding:0!important;
        border:0!important;
        background:transparent!important;
        box-shadow:none!important;
    }

    .fh-home .fh-ref-dates .fh-ref-date-pair > .fh-input{
        display:flex!important;
        width:100%!important;
        min-width:0!important;
        min-height:54px!important;
        opacity:1!important;
        visibility:visible!important;
        position:relative!important;
    }

    /* THIS is the actual existing Return wrapper in the page. */
    .fh-home .fh-ref-dates .fh-return-field{
        display:flex!important;
        width:100%!important;
        min-height:54px!important;
        opacity:1!important;
        visibility:visible!important;
    }

    .fh-home .fh-ref-dates .fh-return-field input{
        display:block!important;
        width:100%!important;
        opacity:1!important;
        visibility:visible!important;
    }

    .fh-home .fh-ref-dates .fh-date-dash{
        display:none!important;
    }

    /*
     * One Way still works because JS disables/clears #return_date.
     * Hide only when the input is actually disabled.
     */
    .fh-home .fh-ref-dates .fh-return-field:has(#return_date:disabled){
        display:none!important;
    }

    /* Bottom search row */
    .fh-home .fh-ref-search-grid-bottom{
        display:flex!important;
        flex-direction:column!important;
        grid-template-columns:none!important;
        gap:9px!important;
        width:100%!important;
        margin-top:9px!important;
    }

    .fh-home .fh-search-submit{
        width:100%!important;
        min-width:0!important;
        min-height:54px!important;
        border-radius:15px!important;
    }

    /* Multi-City phone layout */
    .fh-home .fh-multicity{
        width:100%!important;
        padding-top:4px!important;
    }

    .fh-home .fh-multi-leg{
        position:relative!important;
        display:flex!important;
        flex-direction:column!important;
        grid-template-columns:none!important;
        gap:8px!important;
        width:100%!important;
        padding:12px 10px!important;
        border-radius:15px!important;
    }

    .fh-home .fh-multi-remove{
        position:absolute!important;
        top:9px!important;
        right:9px!important;
        width:40px!important;
        height:40px!important;
    }

    .fh-home .fh-multicity-add{
        width:100%!important;
        min-height:48px!important;
    }

    /* Trust badges */
    .fh-home .fh-hero-trust{
        display:grid!important;
        grid-template-columns:1fr 1fr!important;
        gap:8px!important;
        margin-top:16px!important;
        padding-bottom:0!important;
    }

    .fh-home .fh-hero-trust span{
        min-width:0!important;
        min-height:38px!important;
        padding:6px 7px!important;
        font-size:11px!important;
        justify-content:flex-start!important;
    }

    .fh-home .fh-hero-trust span:last-child{
        grid-column:1/-1!important;
        justify-self:center!important;
        width:auto!important;
        min-width:180px!important;
    }
/* Reduce oversized space before Featured Flight Deals */
    .fh-home .fh-hero-wave{
        height:36px!important;
    }

    .fh-home .fh-featured-ref{
        padding-top:30px!important;
    }
}

@media (max-width:430px){
    .fh-home .fh-hero-inner{
        padding-left:9px!important;
        padding-right:9px!important;
    }

    .fh-home .fh-hero-copy h1{
        font-size:clamp(30px,9.7vw,39px)!important;
    }

    .fh-home .fh-search-shell.fh-ref-search-shell{
        padding:10px!important;
        border-radius:18px!important;
    }

    .fh-home .fh-input input,
    .fh-home .fh-input select,
    .fh-home .fh-pax-button{
        font-size:16px!important;
    }
}

@media (max-width:360px){
    .fh-home .fh-trip-tabs{
        grid-template-columns:1fr!important;
    }

    .fh-home .fh-hero-trust{
        grid-template-columns:1fr!important;
    }

    .fh-home .fh-hero-trust span:last-child{
        grid-column:auto!important;
        width:100%!important;
    }
}
</style>


<style id="fareshunt-mobile-date-fix-v4">
@media (max-width:760px){

    /* DATES should be two clean independent controls, not one oversized box. */
    .fh-home .fh-ref-dates .fh-ref-date-pair{
        display:flex!important;
        flex-direction:column!important;
        gap:8px!important;
        width:100%!important;
        min-height:0!important;
        padding:0!important;
        border:0!important;
        border-radius:0!important;
        background:transparent!important;
        box-shadow:none!important;
        overflow:visible!important;
    }

    .fh-home .fh-ref-dates .fh-ref-date-pair > label.fh-input{
        display:flex!important;
        align-items:center!important;
        width:100%!important;
        height:56px!important;
        min-height:56px!important;
        padding:0 14px!important;
        border:1px solid #dce3ed!important;
        border-radius:16px!important;
        background:#fff!important;
        box-shadow:0 5px 15px rgba(18,34,61,.035)!important;
        overflow:hidden!important;
    }

    .fh-home .fh-ref-dates .fh-ref-date-pair > label.fh-input > i{
        position:static!important;
        flex:0 0 20px!important;
        width:20px!important;
        margin:0 12px 0 0!important;
        transform:none!important;
        color:#7283a2!important;
        font-size:17px!important;
    }

    /*
     * Flatpickr hides the real input and injects .flatpickr-input.form-control.
     * Style the visible generated input directly.
     */
    .fh-home .fh-ref-dates .fh-ref-date-pair > label.fh-input input,
    .fh-home .fh-ref-dates .fh-ref-date-pair > label.fh-input input.flatpickr-input,
    .fh-home .fh-ref-dates .fh-ref-date-pair > label.fh-input input.form-control{
        position:static!important;
        display:block!important;
        flex:1 1 auto!important;
        width:100%!important;
        height:54px!important;
        min-width:0!important;
        padding:0!important;
        margin:0!important;
        border:0!important;
        background:transparent!important;
        box-shadow:none!important;
        color:#1b2c45!important;
        font-size:16px!important;
        font-weight:500!important;
        line-height:54px!important;
        opacity:1!important;
        visibility:visible!important;
        text-align:left!important;
    }

    .fh-home .fh-ref-dates .fh-return-field{
        display:flex!important;
    }

    .fh-home .fh-ref-dates .fh-return-field:has(#return_date:disabled){
        display:none!important;
    }

    /* Flatpickr mobile UX: centered sheet above content, never covering the whole form. */
    body > .flatpickr-calendar{
        position:fixed!important;
        left:12px!important;
        right:12px!important;
        top:auto!important;
        bottom:14px!important;
        width:auto!important;
        max-width:none!important;
        margin:0!important;
        transform:none!important;
        border-radius:20px!important;
        z-index:10000000!important;
        box-shadow:0 24px 70px rgba(5,20,45,.34)!important;
    }

    body > .flatpickr-calendar.open{
        display:block!important;
    }

    body > .flatpickr-calendar .flatpickr-days,
    body > .flatpickr-calendar .dayContainer{
        width:100%!important;
        min-width:100%!important;
        max-width:100%!important;
    }

    body > .flatpickr-calendar .flatpickr-day{
        max-width:none!important;
        flex-basis:14.2857%!important;
    }

    /* Dark veil so the date picker clearly feels modal on a phone. */
    body.fh-date-picker-open::before{
        content:"";
        position:fixed;
        inset:0;
        background:rgba(7,23,43,.28);
        z-index:9999999;
        pointer-events:none;
    }
}

@media (max-width:430px){
    body > .flatpickr-calendar{
        left:8px!important;
        right:8px!important;
        bottom:8px!important;
    }
}
</style>

<style id="fareshunt-v10-calendar-clarity">
@media (max-width:760px){
  body.fh-date-picker-open::before{background:rgba(7,23,43,.18)!important;z-index:999990!important;backdrop-filter:none!important}
  body > .flatpickr-calendar,
  body > .flatpickr-calendar.open{
    z-index:2147483646!important;background:#fff!important;opacity:1!important;filter:none!important;
    color:#10223b!important;isolation:isolate!important;border:1px solid #dce5ef!important;
  }
  body > .flatpickr-calendar .flatpickr-months,
  body > .flatpickr-calendar .flatpickr-innerContainer,
  body > .flatpickr-calendar .flatpickr-rContainer,
  body > .flatpickr-calendar .flatpickr-days,
  body > .flatpickr-calendar .dayContainer{background:#fff!important;opacity:1!important;filter:none!important}
  body > .flatpickr-calendar .flatpickr-day:not(.flatpickr-disabled):not(.prevMonthDay):not(.nextMonthDay){color:#162942!important;opacity:1!important}
  body > .flatpickr-calendar .flatpickr-day.flatpickr-disabled{color:#b3bfce!important;opacity:.72!important}
  body > .flatpickr-calendar .flatpickr-current-month,
  body > .flatpickr-calendar span.flatpickr-weekday{color:#18304e!important;opacity:1!important}
}
</style>

<style id="fh-domestic-lastminute-layout-v1">
/* Domestic cards: Last-Minute-style information hierarchy only. */
.fh-home .fh-deals-shot .fd-domestic{
    display:flex!important;
    flex-direction:column!important;
    min-height:174px!important;
    padding:16px!important;
    border:1px solid #dfe6ef!important;
    border-radius:18px!important;
    background:linear-gradient(180deg,#fff 0%,#fbfcfe 100%)!important;
    box-shadow:0 7px 20px rgba(20,45,80,.045)!important;
}
.fh-home .fh-deals-shot .fd-domestic-top{
    display:flex!important;
    align-items:center!important;
    justify-content:space-between!important;
    gap:8px!important;
}
.fh-home .fh-deals-shot .fd-domestic-label{
    display:inline-flex!important;
    align-items:center!important;
    gap:5px!important;
    padding:5px 8px!important;
    border-radius:999px!important;
    background:#f2f6fb!important;
    color:#173a68!important;
    font-size:8px!important;
    font-weight:700!important;
    letter-spacing:.035em!important;
    text-transform:uppercase!important;
}
.fh-home .fh-deals-shot .fd-domestic-availability{
    color:#8a98aa!important;
    font-size:8.5px!important;
    font-weight:500!important;
}
.fh-home .fh-deals-shot .fd-domestic-route{
    margin-top:17px!important;
    color:#0a2140!important;
    font-size:16px!important;
    line-height:1!important;
    font-weight:750!important;
    letter-spacing:.01em!important;
}
.fh-home .fh-deals-shot .fd-domestic-route i{
    margin:0 9px!important;
    color:#d71940!important;
    font-size:8px!important;
}
.fh-home .fh-deals-shot .fd-domestic-meta{
    display:flex!important;
    justify-content:flex-start!important;
    gap:14px!important;
    margin-top:11px!important;
    color:#7a899e!important;
    font-size:9px!important;
}
.fh-home .fh-deals-shot .fd-domestic-meta span{
    display:inline-flex!important;
    align-items:center!important;
    gap:5px!important;
}
.fh-home .fh-deals-shot .fd-domestic-meta i{
    color:#9aa8b9!important;
    font-size:8px!important;
}
.fh-home .fh-deals-shot .fd-domestic-fare-row{
    position:relative!important;
    display:grid!important;
    grid-template-columns:minmax(0,1fr) auto!important;
    align-items:end!important;
    gap:10px!important;
    margin-top:auto!important;
    padding-top:14px!important;
    padding-right:0!important;
    border-top:1px solid #e8edf4!important;
}
.fh-home .fh-deals-shot .fd-domestic-fare-copy{
    min-width:0!important;
    text-align:left!important;
}
.fh-home .fh-deals-shot .fd-domestic-fare-copy>small{
    display:block!important;
    margin-bottom:5px!important;
    color:#8a98aa!important;
    font-size:8px!important;
    line-height:1!important;
    font-weight:600!important;
    text-transform:uppercase!important;
    letter-spacing:.045em!important;
}
.fh-home .fh-deals-shot .fd-domestic-price{
    display:block!important;
    min-width:0!important;
    text-align:left!important;
}
.fh-home .fh-deals-shot .fd-domestic-price .fh-live-fare{
    justify-content:flex-start!important;
    min-width:0!important;
    color:#d71940!important;
    font-size:19px!important;
    line-height:1!important;
    font-weight:650!important;
}
.fh-home .fh-deals-shot .fd-domestic-price .fh-live-fare.fh-fare-missing{
    font-size:10px!important;
}
.fh-home .fh-deals-shot .fd-domestic-view{
    display:inline-flex!important;
    align-items:center!important;
    gap:6px!important;
    color:#153a68!important;
    font-size:10px!important;
    font-weight:700!important;
    white-space:nowrap!important;
    position:absolute!important;
    right:0!important;
    bottom:1px!important;
}
</style>

@endsection

@section('mid-content')
@php
$cmsv = fn($key,$default='') => $cmsSettings[$key] ?? $default;
$cards = fn($section) => $cmsCards[$section] ?? collect();
// 14 Nov 2026 (cabin label): show the cabin name, not the stored search code ("Econom" -> "Economy").
// Display only: onclick / data-cabin still pass the stored code to the search.
$fhCabinLabel = function ($cabin) {
    $c = trim((string) $cabin);
    $map = ['econom' => 'Economy', 'economy' => 'Economy', 'peconom' => 'Premium Economy', 'premium economy' => 'Premium Economy', 'business' => 'Business Class', 'firstclass' => 'First Class', 'first' => 'First Class'];
    return $map[strtolower($c)] ?? $c;
};
$cardImage = function($d, $fallback='/assets/img/banner-8.jpg') {
    $code = strtoupper((string)($d->to_code ?? ''));
    $map = [
        'LHR'=>'/assets/img/city/c-1-q85.webp',
        'NRT'=>'/assets/img/destination/tr-3.jpg',
        'DXB'=>'/assets/img/city/ct-8-q85.webp',
        'CDG'=>'/assets/img/city/c-6-q85.webp',
        'BOM'=>'/assets/img/city/ct-1-q85.webp',
        'DEL'=>'/assets/img/city/ct-4-q85.webp',
        'BLR'=>'/assets/img/city/ct-10-q85.webp',
        'HYD'=>'/assets/img/city/ct-5.png',
        'SYD'=>'/assets/img/destination/tr-9.jpg',
        'SIN'=>'/assets/img/city/ct-12-q85.webp',
    ];
    return !empty($d->image) ? $d->image : ($map[$code] ?? $fallback);
};
@endphp

<main class="fh-home" id="main-content">
@if($cmsv('home.hero.enabled','1')==='1')
@php
    $heroBg = trim((string) $cmsv('home.hero.background','/assets/img/fareshunt-premium-hero-bg-q90.webp'));
    $heroBgUrl = preg_match('~^https?://~i', $heroBg)
        ? $heroBg
        : asset(ltrim($heroBg, '/'));
@endphp
@if($heroBg === '/assets/img/fareshunt-premium-hero-bg-q90.webp')
{{-- fh-hero-mobile-bg: only for the default hero image. A different image set in the CMS is used as-is on all screens. --}}
<style id="fh-hero-mobile-bg">@media (max-width:480px){.fh-home .fh-hero{--fh-hero-bg:url('{{ asset('assets/img/fareshunt-premium-hero-bg-mobile-q85.webp') }}') !important}}</style>
@endif
<section class="fh-hero" style="--fh-hero-bg:url('{{ $heroBgUrl }}')">
    <div class="fh-hero-grid-bg"></div>
    <div class="fh-hero-blob one"></div>
    <div class="fh-hero-blob two"></div>

    <div class="container fh-hero-inner">
        <div class="fh-hero-copy">
            <span class="fh-pill"><i class="fa-solid fa-circle"></i>{{ $cmsv('home.hero.badge','Premium flight search') }}</span>
<h1>{{ $cmsv('home.hero.title1','Find better flights.') }} <span>{{ $cmsv('home.hero.title2','Book with confidence.') }}</span></h1>
<p>{{ $cmsv('home.hero.subtitle','Compare live flight options and continue through a clear booking experience.') }}</p>
        </div>

        <div class="fh-search-shell fh-ref-search-shell">
            <div id="fhSearchError" role="alert" style="display:{{ session('search_error') ? 'block' : 'none' }};margin:0 0 12px;padding:10px 14px;border:1px solid #f3c1c1;border-radius:12px;background:#fdeeee;color:#a11d1d;font-size:14px;font-weight:600;line-height:1.4;">{{ session('search_error') }}</div>
            <form id="form2" action="{{ route('submit_search') }}" method="post" novalidate>
                @csrf
                <input type="radio" hidden name="flight_types" id="flight_types_round_trip" value="round_trip" checked>
                <input type="radio" hidden name="flight_types" id="flight_types_one_way" value="one_way">
                <input type="radio" hidden name="flight_types" id="flight_types_multi_city" value="multi_city">

                <div class="fh-search-head fh-ref-search-head">
                    <div class="fh-trip-tabs">
                        <button class="fh-trip-tab tnb-trip-tab active" data-trip="round_trip" type="button" onclick="tnbSetTrip('round_trip')">{{ $cmsv('home.search.round_trip_label','Round Trip') }}</button>
                        <button class="fh-trip-tab tnb-trip-tab" data-trip="one_way" type="button" onclick="tnbSetTrip('one_way')">{{ $cmsv('home.search.one_way_label','One Way') }}</button>
                        <button class="fh-trip-tab tnb-trip-tab" data-trip="multi_city" type="button" onclick="tnbSetTrip('multi_city')">{{ $cmsv('home.search.multi_city_label','Multi-City') }}</button>
                    </div>
                </div>

                <div id="fhStandardSearch">
                <div class="fh-ref-search-grid-top">
                    <label class="fh-search-field">
                        <span>{{ $cmsv('home.search.from_label','Flying from') }}</span>
                        <div class="fh-input"><i class="fa-solid fa-location-dot"></i><input type="text" name="location_from" id="location_from" placeholder="City or airport" autocomplete="off" required></div>
                    </label>
                    <button class="fh-swap" type="button" id="fhSwapAirports" aria-label="Swap origin and destination"><i class="fa-solid fa-arrow-right-arrow-left"></i></button>
                    <label class="fh-search-field">
                        <span>{{ $cmsv('home.search.to_label','Flying to') }}</span>
                        <div class="fh-input"><i class="fa-solid fa-plane"></i><input type="text" name="location_to" id="location_to" placeholder="City or airport" autocomplete="off" required></div>
                    </label>
                    <div class="fh-search-field fh-ref-dates">
                        <span>{{ $cmsv('home.search.dates_label','DATES') }}</span>
                        <div class="fh-ref-date-pair">
                            <label class="fh-input"><i class="fa-regular fa-calendar"></i><input type="text" name="depature_date" id="depature_date" placeholder="{{ $cmsv('home.search.departure_label','Departure') }}" required></label>
                            <span class="fh-date-dash">—</span>
                            <label class="fh-input fh-return-field"><i class="fa-regular fa-calendar"></i><input type="text" name="return_date" id="return_date" placeholder="{{ $cmsv('home.search.return_label','Return') }}" required></label>
                        </div>
                    </div>
                </div>
                </div>

                <div id="fhMultiCitySearch" class="fh-multicity" hidden>
                    <div class="fh-multicity-head"><strong>{{ $cmsv('home.search.multicity_heading','Build your Multi-City trip') }}</strong><small>{{ $cmsv('home.search.multicity_help','2 to 5 flights · dates must be in travel order') }}</small></div>
                    <div id="fhMultiCityLegs"></div>
                    <button type="button" class="fh-multicity-add" id="fhAddMultiLeg"><i class="fa-solid fa-plus"></i> {{ $cmsv('home.search.multicity_add_button','Add another flight') }}</button>
                </div>

                <div class="fh-ref-search-grid-bottom">
                    <div class="fh-search-field fh-pax-field">
                        <span>{{ $cmsv('home.search.passengers_label','Passengers') }}</span>
                        <button id="passenger-count" class="fh-pax-button tnb-pax-button" type="button" onclick="tnbTogglePax()">
                            <i class="fa-regular fa-user"></i><span><strong>1 Passenger</strong><small>1 Adult</small></span><i class="fa-solid fa-chevron-down"></i>
                        </button>
                        <div id="tnbPaxPopover" class="tnb-pax-popover fh-pax-popover">
                            @foreach([['adult','Adults','12+ years',1],['child','Children','2–11 years',0],['infant','Infants','Under 2',0]] as $p)
                            <div class="tnb-pax-row"><div><strong>{{ $p[1] }}</strong><small>{{ $p[2] }}</small></div><div class="tnb-counter"><button type="button" onclick="tnbPaxStep('{{ $p[0] }}',-1)">−</button><span data-pax-value="{{ $p[0] }}">{{ $p[3] }}</span><button type="button" onclick="tnbPaxStep('{{ $p[0] }}',1)">+</button></div></div>
                            @endforeach
                            <button class="tnb-pax-done" type="button" onclick="tnbClosePax()">Done</button>
                        </div>
                        <select id="adult-count" name="adult" hidden>@for($i=1;$i<=9;$i++)<option value="{{ $i }}" @selected($i===1)>{{ $i }}</option>@endfor</select>
                        <select id="child-count" name="children" hidden>@for($i=0;$i<=5;$i++)<option value="{{ $i }}">{{ $i }}</option>@endfor</select>
                        <select id="infant-count" name="infant" hidden>@for($i=0;$i<=5;$i++)<option value="{{ $i }}">{{ $i }}</option>@endfor</select>
                    </div>

                    <label class="fh-search-field">
                        <span>{{ $cmsv('home.search.cabin_label','Cabin') }}</span>
                        <div class="fh-input fh-ref-cabin"><i class="fa-solid fa-couch"></i><div class="fh-cabin-stack"><select name="cabin_classes" id="cabin_classes"><option value="Econom" data-sub="Standard comfort at the best fares">Economy</option><option value="Peconom" data-sub="Extra legroom and priority boarding">Premium Economy</option><option value="Business" data-sub="Lie-flat seats and lounge access">Business Class</option><option value="firstclass" data-sub="The highest level of service">First Class</option></select><button type="button" class="fh-cabin-trigger" id="fh_cabin_trigger" aria-haspopup="listbox" aria-expanded="false"><span id="fh_cabin_label">Economy</span><i class="fa-solid fa-chevron-down"></i></button><small id="fh_cabin_sub">Standard comfort at the best fares</small></div><div class="fh-cabin-menu" id="fh_cabin_menu" role="listbox"></div></div>
                    </label>

                    <button class="fh-search-submit" type="submit"><i class="fa-solid fa-magnifying-glass"></i><span>{{ $cmsv('home.search.button','Search Flights') }}</span></button>
                </div>
            </form>

            <div id="fhSearchLoading" class="fh-search-loading" hidden aria-live="polite" aria-busy="true">
                <div class="fh-search-loading-card">
                    <div class="fh-search-loading-spinner" aria-hidden="true"></div>
                    <strong>Searching the best available flights...</strong>
                    <span>Checking live fares and availability.</span>
                </div>
            </div>
        </div>

        <div class="fh-hero-trust">
            <span><i class="fa-solid fa-shield-halved"></i>{{ $cmsv('home.hero_trust.1','Secure Booking') }}</span>
            <span><i class="fa-regular fa-star"></i>{{ $cmsv('home.hero_trust.2','15+ Years Experience') }}</span>
            <span><i class="fa-solid fa-headset"></i>{{ $cmsv('home.hero_trust.3','24/7 Support') }}</span>
            <span><i class="fa-solid fa-globe"></i>{{ $cmsv('home.hero_trust.4','USA Specialists') }}</span>
            <span><i class="fa-regular fa-clock"></i>{{ $cmsv('home.hero_trust.5','Best Price Guarantee') }}</span>
        </div>
    </div>
    <svg class="fh-hero-wave" viewBox="0 0 1440 80" preserveAspectRatio="none"><path d="M0,42 C240,66 485,70 735,59 C1010,47 1225,27 1440,43 L1440,80 L0,80 Z"></path></svg>
</section>
@endif

{{-- ============================================================
     SCREENSHOT-MATCH HOMEPAGE DEALS
     Only this homepage deals block is replaced.
     Existing search/API/FareLeaders/fare-rules remain untouched.
     ============================================================ --}}

@php
    $shotFeatured = $cards('featured')->take(8);
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
.fd-cat.active{background:#fff1f4;color:var(--fd-red);box-shadow:inset 0 -2px 0 var(--fd-red)}
.fd-cat.active i{color:var(--fd-red)}

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
.fd-grid.domestic{grid-template-columns:repeat(7,minmax(0,1fr))}

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
.fd-live-label{text-align:right}
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
        url('/assets/img/fareshunt-premium-hero-bg-q90.webp') center right/cover no-repeat;
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
    .fd-premium-card .fd-photo{
    height:185px;
    border-radius:11px 11px 0 0!important;
}
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
        url('/assets/img/fareshunt-premium-hero-bg-q90.webp') center right/cover no-repeat!important;
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
</style>

<section class="fh-deals-shot" id="fhHomeDeals">

    {{-- Homepage Deals Intro — premium final --}}
    <section class="fh-deals-intro">
        <div class="fh-deals-intro-inner">
            <div class="fh-deals-intro-label">EXCLUSIVE OFFERS</div>

            <div class="fh-deals-intro-divider">
                <span></span>
                <i class="fa-solid fa-star"></i>
                <span></span>
            </div>

            <h2>Explore Our Best Fares</h2>
        </div>
    </section>

    {{-- Featured Flight Deals --}}
    @if($shotFeatured->isNotEmpty() && $cmsv('home.featured.enabled','1') === '1')
    <section class="fd-section" id="fdFeatured">
        <div class="fd-container">
            <div class="fd-head">
                <div>
                    <div class="fd-title-row"><i class="fa-solid fa-fire"></i><h2>{{ $cmsv('home.featured.title','Featured Flight Deals') }}</h2></div>
                    <p>{{ $cmsv('home.featured.subtitle','Handpicked fares to popular destinations. Limited seats!') }}</p>
                </div>
                <a href="{{ route('all_offers') }}">View all deals <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <div class="fd-grid featured">
                @foreach($shotFeatured as $d)
                @php
                    $fidx = $loop->index;
                    $fallbackClass = ['Business Class','First Class','Business Class','Last Minute'][$fidx] ?? ($d->cabin ?: 'Economy');
                    $badgeClass = $fidx === 3 ? 'red' : '';
                    $fromCity = trim((string)($d->from_city ?? $d->subtitle ?? $d->from_code));
                    $toCity = $dealCity($d, $d->to_code);
                @endphp
                <button class="fd-card" type="button"
                    onclick="tnbSearchDeal('{{ $d->from_code }}','{{ $d->to_code }}','{{ $d->cabin }}',{{ $d->days }},'{{ $d->trip_type ?? 'round_trip' }}',{{ (int)($d->return_days ?? 7) }})">
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
                                <small>from</small>
                                <strong class="fh-live-fare" data-route-fare data-from="{{ $d->from_code }}" data-to="{{ $d->to_code }}" data-cabin="{{ $d->cabin }}" data-days="{{ $d->days }}" data-trip-type="{{ $d->trip_type ?? 'round_trip' }}" data-return-days="{{ (int)($d->return_days ?? 7) }}" data-fare-mode="{{ $d->fare_mode ?? 'manual' }}" data-display-fare="{{ $d->display_fare ?? '' }}"><span class="fh-fare-loading" aria-hidden="true"></span><span class="visually-hidden">Checking live fare</span></strong>
                            </div>
                        </div>
                        <div class="fd-meta">
                            <span><i class="fa-regular fa-calendar"></i>Live dates</span>
                            <span><i class="fa-solid fa-suitcase-rolling"></i>Bag options</span>
                        </div>
                        <div class="fd-card-foot">
                            <b><i class="fa-regular fa-shield"></i> {{ $d->cabin ? $fhCabinLabel($d->cabin) : $fallbackClass }}</b>
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
    @if($shotDomestic->isNotEmpty() && $cmsv('home.domestic.enabled','1') === '1')
    <section class="fd-section soft" id="fdDomestic">
        <div class="fd-container">
            <div class="fd-head">
                <div>
                    <div class="fd-title-row"><i class="fa-solid fa-flag-usa"></i><h2>{{ $cmsv('home.domestic.title','Top Deals Within USA') }}</h2></div>
                    <p>{{ $cmsv('home.domestic.subtitle','Best domestic fares — book your next trip now.') }}</p>
                </div>
                <a href="{{ route('all_offers') }}">View all <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="fd-grid domestic">
                @foreach($shotDomestic as $d)
                <button class="fd-card fd-domestic" type="button"
                    onclick="tnbSearchDeal('{{ $d->from_code }}','{{ $d->to_code }}','{{ $d->cabin }}',{{ $d->days }},'{{ $d->trip_type ?? 'round_trip' }}',{{ (int)($d->return_days ?? 7) }})">
                    <div class="fd-domestic-top">
                        <span class="fd-domestic-label"><i class="fa-solid fa-plane"></i> Domestic Deal</span>
                        <span class="fd-domestic-availability">Live availability</span>
                    </div>

                    <div class="fd-domestic-route">
                        {{ $d->from_code }}
                        <i class="fa-solid fa-arrow-right"></i>
                        {{ $d->to_code }}
                    </div>

                    <div class="fd-domestic-meta">
                        <span><i class="fa-solid fa-plane"></i>{{ $dealStops($d) }}</span>
                        <span><i class="fa-solid fa-chair"></i>{{ $d->cabin ? $fhCabinLabel($d->cabin) : 'Economy' }}</span>
                    </div>

                    <div class="fd-domestic-fare-row">
                        <div class="fd-domestic-fare-copy">
                            <small>{{ ($d->fare_mode ?? 'manual') === 'live' ? 'Live fare' : 'Starting from' }}</small>
                            <span class="fd-domestic-price">
                                <strong class="fh-live-fare" data-route-fare data-from="{{ $d->from_code }}" data-to="{{ $d->to_code }}" data-cabin="{{ $d->cabin }}" data-days="{{ $d->days }}" data-trip-type="{{ $d->trip_type ?? 'round_trip' }}" data-return-days="{{ (int)($d->return_days ?? 7) }}" data-fare-mode="{{ $d->fare_mode ?? 'manual' }}" data-display-fare="{{ $d->display_fare ?? '' }}"><span class="fh-fare-loading" aria-hidden="true"></span><span class="visually-hidden">Checking live fare</span></strong>
                            </span>
                        </div>

                        <span class="fd-domestic-view">View deal <i class="fa-solid fa-arrow-right"></i></span>
                    </div>
                </button>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- International Deals --}}
    @if($shotInternational->isNotEmpty() && $cmsv('home.international.enabled','1') === '1')
    <section class="fd-section" id="fdInternational">
        <div class="fd-container">
            <div class="fd-head">
                <div>
                    <div class="fd-title-row"><i class="fa-solid fa-earth-americas"></i><h2>{{ $cmsv('home.international.title','International Deals') }}</h2></div>
                    <p>{{ $cmsv('home.international.subtitle','Explore top destinations around the world.') }}</p>
                </div>
                <a href="{{ route('all_offers') }}">View all deals <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="fd-grid international">
                @foreach($shotInternational as $d)
                @php
                    $ibadges = ['Business Class','First Class','Business Class','Premium Economy','First Class'];
                    $ibadge = $d->badge ?: ($ibadges[$loop->index] ?? ($d->cabin ? $fhCabinLabel($d->cabin) : 'International'));
                    $ibadgeClass = str_contains(strtolower($ibadge),'business') ? 'red' : (str_contains(strtolower($ibadge),'first') ? 'dark' : '');
                @endphp
                <button class="fd-card" type="button"
                    onclick="tnbSearchDeal('{{ $d->from_code }}','{{ $d->to_code }}','{{ $d->cabin }}',{{ $d->days }},'{{ $d->trip_type ?? 'round_trip' }}',{{ (int)($d->return_days ?? 7) }})">
                    <div class="fd-photo fd-small-photo fh-lazy-bg" data-fh-bg="{{ $cardImage($d, '/assets/img/banner-3.jpg') }}">
                        <span class="fd-badge {{ $ibadgeClass }}">{{ $ibadge }}</span>
                        <span class="fd-heart"><i class="fa-regular fa-heart"></i></span>
                    </div>
                    <div class="fd-simple-body">
                        <div class="fd-simple-route"><span>{{ $d->from_code }}</span><i class="fa-solid fa-arrow-right"></i><span>{{ $d->to_code }}</span></div>
                        <div class="fd-simple-sub">
                            <small>{{ $dealStops($d) }}</small>
                            <strong class="fh-live-fare" data-route-fare data-from="{{ $d->from_code }}" data-to="{{ $d->to_code }}" data-cabin="{{ $d->cabin }}" data-days="{{ $d->days }}" data-trip-type="{{ $d->trip_type ?? 'round_trip' }}" data-return-days="{{ (int)($d->return_days ?? 7) }}" data-fare-mode="{{ $d->fare_mode ?? 'manual' }}" data-display-fare="{{ $d->display_fare ?? '' }}"><span class="fh-fare-loading" aria-hidden="true"></span><span class="visually-hidden">Checking live fare</span></strong>
                        </div>
                    </div>
                </button>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Business & First Class Deals --}}
    @if($shotPremium->isNotEmpty() && $cmsv('home.premium.enabled','1') === '1')
    <section class="fd-premium-section" id="fdPremium">
        <div class="fd-container">
            <div class="fd-premium-panel">
                <div class="fd-head">
                    <div>
                        <div class="fd-title-row"><i class="fa-regular fa-gem"></i><h2>{{ $cmsv('home.premium.title','Business & First Class') }} <span>{{ $cmsv('home.premium.accent','Deals') }}</span></h2></div>
                        <p>{{ $cmsv('home.premium.subtitle','Experience luxury travel at unbeatable prices.') }}</p>
                    </div>
                    <a href="{{ route('all_offers') }}">{{ $cmsv('home.premium.view_all_text','View all deals') }} <i class="fa-solid fa-arrow-right"></i></a>
                </div>

                <div class="fd-premium-grid">
                    @foreach($shotPremium as $d)
                    @php
                        $isFirst = str_contains(strtolower((string)$d->cabin),'first');
                        $pBadge = $d->badge ?: ($isFirst ? 'First Class' : 'Business Class');
                    @endphp
                    <button class="fd-premium-card" type="button"
                        onclick="tnbSearchDeal('{{ $d->from_code }}','{{ $d->to_code }}','{{ $d->cabin }}',{{ $d->days }},'{{ $d->trip_type ?? 'round_trip' }}',{{ (int)($d->return_days ?? 7) }})">
                        <div class="fd-photo fh-lazy-bg" data-fh-bg="{{ $cardImage($d, '/assets/img/fareshunt-premium-hero-bg-q90.webp') }}">
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
                                    <small>from</small>
                                    <strong class="fh-live-fare gold" data-route-fare data-from="{{ $d->from_code }}" data-to="{{ $d->to_code }}" data-cabin="{{ $d->cabin }}" data-days="{{ $d->days }}" data-trip-type="{{ $d->trip_type ?? 'round_trip' }}" data-return-days="{{ (int)($d->return_days ?? 7) }}" data-fare-mode="{{ $d->fare_mode ?? 'manual' }}" data-display-fare="{{ $d->display_fare ?? '' }}"><span class="fh-fare-loading" aria-hidden="true"></span><span class="visually-hidden">Checking live fare</span></strong>
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
    @if($shotHoliday->isNotEmpty() && $cmsv('home.holiday.enabled','1') === '1')
    <section class="fd-section soft" id="fdHoliday">
        <div class="fd-container">
            <div class="fd-head">
                <div>
                    <div class="fd-title-row"><i class="fa-solid fa-umbrella-beach"></i><h2>{{ $cmsv('home.holiday.title','Holiday Packages') }}</h2></div>
                    <p>{{ $cmsv('home.holiday.subtitle','Flight + Hotel deals for your perfect vacation.') }}</p>
                </div>
                <a href="{{ route('all_offers') }}">View all packages <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <div class="fd-grid holiday">
                @foreach($shotHoliday as $d)
                @php $nights = max(3, min(9, (int)round(($d->days ?: 28) / 7))); @endphp
                <button class="fd-card fd-holiday-card" type="button"
                    onclick="tnbSearchDeal('{{ $d->from_code }}','{{ $d->to_code }}','{{ $d->cabin }}',{{ $d->days }},'{{ $d->trip_type ?? 'round_trip' }}',{{ (int)($d->return_days ?? 7) }})">
                    <div class="fd-photo fd-small-photo fh-lazy-bg" data-fh-bg="{{ $cardImage($d, '/assets/img/banner-8.jpg') }}">
                        <span class="fd-badge dark">{{ strtoupper($dealCity($d,$d->to_code)) }}</span>
                        <span class="fd-heart"><i class="fa-regular fa-heart"></i></span>
                    </div>
                    <div class="fd-holiday-body">
                        <div class="fd-holiday-info">
                            <strong class="fd-holiday-title">{{ $dealCity($d,$d->to_code) }}</strong>
                            <strong><i class="fa-solid fa-suitcase-rolling"></i> Flight + Hotel</strong>
                            <small><i class="fa-regular fa-clock"></i> {{ $nights }} Nights</small>
                        </div>
                        <div class="fd-holiday-price">
                            <small>from</small>
                            <strong class="fh-live-fare" data-route-fare data-from="{{ $d->from_code }}" data-to="{{ $d->to_code }}" data-cabin="{{ $d->cabin }}" data-days="{{ $d->days }}" data-trip-type="{{ $d->trip_type ?? 'round_trip' }}" data-return-days="{{ (int)($d->return_days ?? 7) }}" data-fare-mode="{{ $d->fare_mode ?? 'manual' }}" data-display-fare="{{ $d->display_fare ?? '' }}"><span class="fh-fare-loading" aria-hidden="true"></span><span class="visually-hidden">Checking live fare</span></strong>
                        </div>
                    </div>
                </button>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Last Minute Deals --}}
    @if($shotLastMinute->isNotEmpty() && $cmsv('home.last_minute.enabled','1') === '1')
    <section class="fd-section" id="fdLastMinute">
        <div class="fd-container">
            <div class="fd-head">
                <div>
                    <div class="fd-title-row"><i class="fa-solid fa-bolt"></i><h2>{{ $cmsv('home.last_minute.title','Last Minute Deals') }}</h2></div>
                    <p>{{ $cmsv('home.last_minute.subtitle','Limited time only — check live availability before it changes.') }}</p>
                </div>
                <a href="{{ route('all_offers') }}">View all deals <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <div class="fd-grid last">
                @foreach($shotLastMinute as $d)
                <button class="fd-card fd-last-card" type="button"
                    onclick="tnbSearchDeal('{{ $d->from_code }}','{{ $d->to_code }}','{{ $d->cabin }}',{{ $d->days }},'{{ $d->trip_type ?? 'round_trip' }}',{{ (int)($d->return_days ?? 7) }})">
                    <div class="fd-last-top">
                        <span class="fd-last-label"><i class="fa-solid fa-bolt"></i> Last Minute</span>
                        <span class="fd-last-urgency">Limited availability</span>
                    </div>
                    <div class="fd-last-route">{{ $d->from_code }} <i class="fa-solid fa-arrow-right"></i> {{ $d->to_code }}</div>
                    <div class="fd-last-meta">
                        <span><i class="fa-solid fa-plane"></i>{{ $dealStops($d) }}</span>
                        <span><i class="fa-solid fa-chair"></i>{{ $d->cabin ? $fhCabinLabel($d->cabin) : 'Economy' }}</span>
                    </div>
                    <div class="fd-last-fare-row">
                        <div class="fd-last-fare-copy">
                            <small>{{ ($d->fare_mode ?? 'manual') === 'live' ? 'Live fare' : 'Starting from' }}</small>
                            <span class="fd-last-price"><strong class="fh-live-fare" data-route-fare data-from="{{ $d->from_code }}" data-to="{{ $d->to_code }}" data-cabin="{{ $d->cabin }}" data-days="{{ $d->days }}" data-trip-type="{{ $d->trip_type ?? 'round_trip' }}" data-return-days="{{ (int)($d->return_days ?? 7) }}" data-fare-mode="{{ $d->fare_mode ?? 'manual' }}" data-display-fare="{{ $d->display_fare ?? '' }}"><span class="fh-fare-loading" aria-hidden="true"></span><span class="visually-hidden">Checking live fare</span></strong></span>
                        </div>
                        <span class="fd-last-view">View deal <i class="fa-solid fa-arrow-right"></i></span>
                    </div>
                </button>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Screenshot-style trust strip --}}
    <div class="fd-container">
        <div class="fd-trust-strip">
            <div class="fd-trust-item"><i class="fa-solid fa-tag"></i><div><strong>{{ $cmsv('home.trust_strip.1.title','Best Price Guarantee') }}</strong><small>{{ $cmsv('home.trust_strip.1.subtitle','We match any price.') }}</small></div></div>
            <div class="fd-trust-item"><i class="fa-solid fa-headset"></i><div><strong>{{ $cmsv('home.trust_strip.2.title','24/7 Expert Support') }}</strong><small>{{ $cmsv('home.trust_strip.2.subtitle','Real people, real support.') }}</small></div></div>
            <div class="fd-trust-item"><i class="fa-solid fa-shield-halved"></i><div><strong>{{ $cmsv('home.trust_strip.3.title','Secure Booking') }}</strong><small>{{ $cmsv('home.trust_strip.3.subtitle','Your data is always protected.') }}</small></div></div>
            <div class="fd-trust-item"><i class="fa-regular fa-calendar-check"></i><div><strong>{{ $cmsv('home.trust_strip.4.title','Flexible Options') }}</strong><small>{{ $cmsv('home.trust_strip.4.subtitle','Book with confidence.') }}</small></div></div>
        </div>

        {{-- Fly Premium, Pay Less — light premium Business & First Class banner --}}
        @if($cmsv('home.premium.banner_enabled','1') == '1')
            @php
                $premiumBannerImage = trim((string) $cmsv('home.premium.banner_image','assets/img/fareshunt-premium-light-banner-q82.webp'));
                $premiumBannerLink = trim((string) $cmsv('home.premium.banner_link','/deals'));
                $premiumBannerAlt = trim((string) $cmsv('home.premium.banner_alt','Fly Premium, Pay Less — Business and First Class deals'));

                $premiumBannerImageUrl = preg_match('~^https?://~i', $premiumBannerImage)
                    ? $premiumBannerImage
                    : asset(ltrim($premiumBannerImage, '/'));

                $premiumBannerHref = preg_match('~^https?://~i', $premiumBannerLink)
                    ? $premiumBannerLink
                    : url('/'.ltrim($premiumBannerLink, '/'));
            @endphp
            <a class="fd-premium-banner-image"
               href="{{ $premiumBannerHref }}"
               aria-label="{{ $premiumBannerAlt }}">
                <img src="{{ $premiumBannerImageUrl }}"
                     alt="{{ $premiumBannerAlt }}"
                     loading="lazy"
                     decoding="async">
            </a>
        @endif
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.fd-cat[data-fd-target]').forEach(function(btn){
        btn.addEventListener('click', function(){
            document.querySelectorAll('.fd-cat').forEach(function(x){ x.classList.remove('active'); });
            btn.classList.add('active');
            var target = document.getElementById(btn.dataset.fdTarget);
            if(target){
                target.scrollIntoView({behavior:'smooth', block:'start'});
            }
        });
    });
});
</script>



@if($homeAirlines->isNotEmpty())
@php
    /*
     * V20 airline logo map.
     * Uses vector SVG artwork instead of low-resolution favicons.
     * This deliberately overrides broken/low-quality DB logo values
     * for the listed featured airlines only.
     *
     * Search, API, fares, routes, CMS and airline data are unchanged.
     */
    $featuredAirlineLogos = [
        'UA' => 'https://www.google.com/s2/favicons?sz=256&domain_url=https://www.united.com',
        'AA' => 'https://commons.wikimedia.org/wiki/Special:Redirect/file/AA_Symbol.svg',
        'DL' => 'https://commons.wikimedia.org/wiki/Special:Redirect/file/Delta_logo.svg',
        'AC' => 'https://commons.wikimedia.org/wiki/Special:Redirect/file/Air_Canada_2017.svg',
        'BA' => 'https://commons.wikimedia.org/wiki/Special:Redirect/file/BRITISH_AIRWAYS_logo.svg',
        'EK' => 'https://commons.wikimedia.org/wiki/Special:Redirect/file/Emirates_Logo.svg',
        'QR' => 'https://commons.wikimedia.org/wiki/Special:Redirect/file/Qatar_Airways_logo.svg',
        'LH' => 'https://commons.wikimedia.org/wiki/Special:Redirect/file/Lufthansa_Logo_2018_crane.svg',
        'AF' => 'https://commons.wikimedia.org/wiki/Special:Redirect/file/Air_France_Logo.svg',
        'AI' => 'https://commons.wikimedia.org/wiki/Special:Redirect/file/Air_India_2023.svg',
        'SQ' => 'https://commons.wikimedia.org/wiki/Special:Redirect/file/Singapore_Airlines_Logo.svg',
        'NH' => 'https://commons.wikimedia.org/wiki/Special:Redirect/file/All_Nippon_Airways_Logo.svg',
    ];
    // fh-airline-logos-local (26 Oct 2026): use the copy saved on our server (public/assets/img/airlines/<CODE>.svg|.png)
    // when it exists - Wikimedia sends no cache headers and sometimes refuses (429). Missing file = old URL, as before.
    foreach ($featuredAirlineLogos as $fhCode => $fhRemote) {
        foreach (['svg', 'png'] as $fhExt) {
            $fhLocal = 'assets/img/airlines/'.$fhCode.'.'.$fhExt;
            if (is_file(public_path($fhLocal)) && filesize(public_path($fhLocal)) > 200) {
                $featuredAirlineLogos[$fhCode] = asset($fhLocal);
                break;
            }
        }
    }
@endphp
<section class="fh-section fh-soft fh-airlines-reference">
    <div class="container">
        <div class="fh-section-head">
            <div>
                <span>Trusted carriers</span>
                <h2>Popular Airlines</h2>
                <p>Explore leading airlines available for domestic and international flight searches.</p>
            </div>
        </div>

        <div class="fh-airline-grid">
            @foreach($homeAirlines as $a)
            @php
                $airlineCode = strtoupper((string)$a->code);

                // Featured cards use the curated vector logo first.
                // For any other airline, keep the existing CMS/DB logo.
                $airlineLogo = $featuredAirlineLogos[$airlineCode]
                    ?? trim((string)($a->logo ?? ''));
            @endphp

            <a class="fh-airline-card" href="{{ route('search_flight') }}">
                <div class="fh-airline-logo">
                    @if($airlineLogo)
                        <img
                            src="{{ $airlineLogo }}"
                            alt="{{ $a->name }} logo"
                            loading="lazy"
                            referrerpolicy="no-referrer"
                            onerror="this.style.display='none';this.nextElementSibling.style.display='grid'"
                        >
                        <span style="display:none">{{ $airlineCode }}</span>
                    @else
                        <span>{{ $airlineCode }}</span>
                    @endif
                </div>

                <strong>{{ $a->name }}</strong>
                <small>{{ $airlineCode }}</small>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="fh-why">
    <div class="container">
        <div class="fh-section-head centered fh-why-reference-head"><div><h2>Why Travelers Choose <em>FaresHunt</em></h2><p>FaresHunt is a U.S.-based online travel agency operated by Aeronza Travel Group LLC,<br>helping travelers search domestic and international flights with dedicated booking assistance.</p></div></div>
        <div class="fh-why-grid">
            @php
            $fallbackWhy=[
                ['fa-dollar-sign','Best Price Search','Compare live flight availability and review available fare options for your journey.'],
                ['fa-headset','24/7 Travel Support','Reach your configured travel support channels when you need assistance.'],
                ['fa-shield-halved','Secure & Encrypted','Existing booking, payment and authorization safeguards remain in place.'],
                ['fa-earth-americas','USA Travel Specialists','Domestic and international flight search from one clear experience.'],
                ['fa-credit-card','Transparent Pricing','Fare details and booking information stay clear through the current booking flow.'],
                ['fa-award','Travel Expertise','Premium cabin and international route discovery in one polished experience.'],
                ['fa-clock','Fast Confirmation','Receive booking details and confirmation updates through our customer communication channels.'],
                ['fa-star','Traveler Focused','A cleaner, smoother interface built around real search and booking actions.'],
            ];
            @endphp
            @for($i=0;$i<8;$i++)
            @php $u=$h_usp[$i]??null; $fw=$fallbackWhy[$i]; @endphp
            <article class="fh-why-card"><div><i class="fa-solid {{ $fw[0] }}"></i></div><h3>{{ $u->title ?? $fw[1] }}</h3><p>{{ $u->description ?? $u->short ?? $fw[2] }}</p></article>
            @endfor
        </div>
    </div>
</section>

@if($h_blog->isNotEmpty())
@php $homeGuideCount = min(3, $h_blog->count()); @endphp
<section class="fh-section fh-guides-reference">
    <div class="container">
        <div class="fh-section-head fh-guides-head">
            <div>
                <span class="fh-guides-kicker">{{ $cmsv('home.travel_guides.kicker','TRAVEL INSPIRATION') }}</span>
                <h2>{{ $cmsv('home.travel_guides.title','Travel Guides & Insights') }}</h2>
                <p>{{ $cmsv('home.travel_guides.subtitle','Expert advice, destination ideas and practical travel tips from our specialists.') }}</p>
            </div>
            <a href="{{ route('all_blogs') }}">{{ $cmsv('home.travel_guides.view_all','View all guides') }} <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <div class="fh-guide-grid fh-guide-count-{{ $homeGuideCount }}">
            @foreach($h_blog->take(3) as $b)
            @php
                $guideText = trim(strip_tags($b->short ?? $b->description ?? ''));
                $wordCount = str_word_count($guideText);
                $readMins = max(3, (int) ceil(max(1,$wordCount) / 200));
                $guideDate = optional($b->created_at)->format('M d, Y');
                $guideCategory = optional($b->category)->title ?: 'Travel Guide';
                $guideImage = ($b->image ?? null) ?: (($b->banner ?? null) ?: '/assets/img/banner-7.jpg');
                // fh-guide-imagekit: ImageKit originals are full-size PNGs (500-700 KB) shown in a ~350px card.
                // Ask ImageKit for an 800px WebP/JPEG instead. Only when the URL has no transformation yet.
                if (is_string($guideImage) && str_contains($guideImage, 'ik.imagekit.io/') && !str_contains($guideImage, 'tr=') && !str_contains($guideImage, '/tr:')) {
                    $guideImage .= (str_contains($guideImage, '?') ? '&' : '?') . 'tr=w-800,q-75,f-auto';
                }
                $rawGuideTitle = trim((string)($b->title ?? ''));
                $genericGuideTitle = (bool) preg_match('/^(blog|post|article)\\s*\\d*$/i', $rawGuideTitle);
                $guideTitle = $genericGuideTitle ? 'Travel Inspiration & Expert Tips' : ($rawGuideTitle ?: 'Travel Inspiration & Expert Tips');
                $guideExcerpt = $guideText ?: 'Destination inspiration, practical planning advice and helpful travel tips from the FaresHunt Travel Desk.';
            @endphp
            <a class="fh-guide-card" href="{{ url('/blog/'.$b->slug) }}">
                <div class="fh-guide-image fh-lazy-bg" data-fh-bg="{{ $guideImage }}">
                    <span class="fh-guide-badge">{{ $guideCategory }}</span>
                    <span class="fh-guide-read"><i class="fa-regular fa-clock"></i>{{ $readMins }} {{ $cmsv('home.travel_guides.min_read_suffix','min read') }}</span>
                </div>
                <div class="fh-guide-body">
                    <div class="fh-guide-date">@if($guideDate){{ $guideDate }}@else FaresHunt Travel Desk @endif</div>
                    <h3>{{ $guideTitle }}</h3>
                    <p>{{ \Illuminate\Support\Str::limit($guideExcerpt,180) }}</p>
                    <div class="fh-guide-footer">
                        <span>{{ $cmsv('home.travel_guides.read_label','Read guide') }}</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

@if($cmsv('site.footer.alerts.enabled','1')==='1')
<section class="fh-alerts fh-alerts-reference">
    <div class="container">
        <div class="fh-alerts-inner">
            <div class="fh-alert-icon"><i class="fa-regular fa-bell"></i></div>
            <h2>Never Miss a <em>Flight Deal</em></h2>
            <p>Subscribe to fare alerts and get the best deals delivered to your inbox before they sell out.<br>Unsubscribe anytime.</p>

            @if(session('fare_alert_success'))
                <div class="fh-alert-success"><i class="fa-solid fa-circle-check"></i>{{ session('fare_alert_success') }}</div>
            @endif
            @if($errors->has('fare_alert_email'))
                <div class="fh-alert-error">{{ $errors->first('fare_alert_email') }}</div>
            @endif

            <form method="POST" action="{{ route('fare_alert.subscribe') }}" class="fh-alert-form">@csrf
                <label>
                    <i class="fa-regular fa-envelope"></i>
                    <input type="email" name="email" placeholder="{{ $cmsv('site.footer.alerts.email_placeholder','your@email.com') }}" required>
                </label>
                <label class="airport">
                    <i class="fa-solid fa-plane"></i>
                    <input type="text" name="home_airport" maxlength="10" placeholder="{{ $cmsv('site.footer.alerts.airport_placeholder','HOME AIRPORT (E.G. JFK)') }}">
                </label>
                <button type="submit"><i class="fa-regular fa-bell"></i>{{ $cmsv('site.footer.alerts.button','Get Alerts') }}</button>
            </form>

            <small>No spam. Unsubscribe with one click. We respect your privacy.</small>
        </div>
    </div>
</section>
@endif
</main>
@endsection


@section('footer')
<style id="fh-native-airport-autocomplete-fix">
.fh-native-airport-menu{
    box-sizing:border-box!important;
}
.fh-native-airport-menu .ui-menu-item{
    list-style:none!important;
}
</style>

<style id="fh-airport-selected-blue-state">
/* Persistent BLUE state after a valid airport is selected from dropdown */
.fh-home .fh-input.fh-airport-selected{
    border-color:#2563eb!important;
    background:linear-gradient(180deg,#f8fbff 0%,#eef5ff 100%)!important;
    box-shadow:
        0 0 0 4px rgba(37,99,235,.10),
        0 8px 22px rgba(37,99,235,.10)!important;
}

.fh-home .fh-input.fh-airport-selected > i{
    color:#2563eb!important;
}

.fh-home .fh-input.fh-airport-selected input{
    color:#123f7a!important;
    font-weight:600!important;
}

/* Keep blue selected state even after field loses focus */
.fh-home .fh-input.fh-airport-selected:hover{
    border-color:#1d4ed8!important;
    background:linear-gradient(180deg,#f8fbff 0%,#eef5ff 100%)!important;
}
</style>


<style id="fh-airport-dropdown-final-blue-override">
/* FINAL OVERRIDE — active/hover airport option must be BLUE */
body .fh-native-airport-menu .ui-menu-item.ui-state-active .fh-airport-result,
body .fh-native-airport-menu .ui-menu-item:hover .fh-airport-result,
body .ui-autocomplete .ui-menu-item.ui-state-active .fh-airport-result,
body .ui-autocomplete .ui-menu-item:hover .fh-airport-result{
    background:linear-gradient(135deg,#0f3b68 0%,#164f86 100%)!important;
    border-color:#0f3b68!important;
    box-shadow:0 8px 20px rgba(15,59,104,.20)!important;
}

body .fh-native-airport-menu .ui-menu-item.ui-state-active .fh-airport-result-icon,
body .fh-native-airport-menu .ui-menu-item:hover .fh-airport-result-icon,
body .ui-autocomplete .ui-menu-item.ui-state-active .fh-airport-result-icon,
body .ui-autocomplete .ui-menu-item:hover .fh-airport-result-icon{
    background:rgba(255,255,255,.14)!important;
    color:#fff!important;
}

body .fh-native-airport-menu .ui-menu-item.ui-state-active .fh-airport-result-code,
body .fh-native-airport-menu .ui-menu-item.ui-state-active .fh-airport-result-city,
body .fh-native-airport-menu .ui-menu-item.ui-state-active .fh-airport-result-name,
body .fh-native-airport-menu .ui-menu-item.ui-state-active .fh-airport-result-country,
body .fh-native-airport-menu .ui-menu-item:hover .fh-airport-result-code,
body .fh-native-airport-menu .ui-menu-item:hover .fh-airport-result-city,
body .fh-native-airport-menu .ui-menu-item:hover .fh-airport-result-name,
body .fh-native-airport-menu .ui-menu-item:hover .fh-airport-result-country,
body .ui-autocomplete .ui-menu-item.ui-state-active .fh-airport-result-code,
body .ui-autocomplete .ui-menu-item.ui-state-active .fh-airport-result-city,
body .ui-autocomplete .ui-menu-item.ui-state-active .fh-airport-result-name,
body .ui-autocomplete .ui-menu-item.ui-state-active .fh-airport-result-country,
body .ui-autocomplete .ui-menu-item:hover .fh-airport-result-code,
body .ui-autocomplete .ui-menu-item:hover .fh-airport-result-city,
body .ui-autocomplete .ui-menu-item:hover .fh-airport-result-name,
body .ui-autocomplete .ui-menu-item:hover .fh-airport-result-country{
    color:#fff!important;
}

/* Keep non-active options white */
body .fh-native-airport-menu .ui-menu-item:not(.ui-state-active) .fh-airport-result{
    background:#fff!important;
}
</style>


<style id="fh-airport-active-row-hard-fix">
/* Hard final state: first/keyboard/hovered airport row is BLUE */
body .fh-native-airport-menu li.ui-menu-item{
    padding:0!important;
    margin:0!important;
    border:0!important;
    background:transparent!important;
}
body .fh-native-airport-menu li.ui-menu-item > .fh-airport-result{
    width:100%!important;
    box-sizing:border-box!important;
}
body .fh-native-airport-menu .fh-airport-result.fh-airport-active,
body .fh-native-airport-menu li.ui-menu-item:hover > .fh-airport-result{
    background:linear-gradient(135deg,#0d3c6d 0%,#185b94 100%)!important;
    border-color:#0d3c6d!important;
    box-shadow:0 8px 20px rgba(13,60,109,.20)!important;
}
body .fh-native-airport-menu .fh-airport-result.fh-airport-active .fh-airport-result-icon,
body .fh-native-airport-menu li.ui-menu-item:hover > .fh-airport-result .fh-airport-result-icon{
    background:rgba(255,255,255,.14)!important;
    color:#fff!important;
}
body .fh-native-airport-menu .fh-airport-result.fh-airport-active .fh-airport-result-code,
body .fh-native-airport-menu .fh-airport-result.fh-airport-active .fh-airport-result-city,
body .fh-native-airport-menu .fh-airport-result.fh-airport-active .fh-airport-result-name,
body .fh-native-airport-menu .fh-airport-result.fh-airport-active .fh-airport-result-country,
body .fh-native-airport-menu li.ui-menu-item:hover > .fh-airport-result .fh-airport-result-code,
body .fh-native-airport-menu li.ui-menu-item:hover > .fh-airport-result .fh-airport-result-city,
body .fh-native-airport-menu li.ui-menu-item:hover > .fh-airport-result .fh-airport-result-name,
body .fh-native-airport-menu li.ui-menu-item:hover > .fh-airport-result .fh-airport-result-country{
    color:#fff!important;
}
</style>


<style id="fh-multicity-css">
.fh-multicity{padding:6px 0 2px}.fh-multicity-head{display:flex;justify-content:space-between;gap:12px;align-items:end;margin-bottom:12px}.fh-multicity-head strong{font-size:15px;color:#102a43}.fh-multicity-head small{color:#6b7b8f}.fh-multi-leg{display:grid;grid-template-columns:72px 1fr 1fr .8fr 42px;gap:10px;align-items:end;padding:12px;border:1px solid #e1e8f0;border-radius:16px;background:#fbfdff;margin-bottom:10px}.fh-multi-num{align-self:center;font-weight:800;font-size:12px;color:#173b65}.fh-multi-remove,.fh-multicity-add{border:1px solid #dce5ef;background:#fff;border-radius:12px;height:48px}.fh-multicity-add{padding:0 16px;font-weight:800;color:#164d86}.fh-multi-remove{color:#b4233c}.fh-multicity-add:disabled{opacity:.45}@media(max-width:800px){.fh-multicity-head{display:block}.fh-multi-leg{grid-template-columns:1fr 1fr}.fh-multi-num{grid-column:1/-1}.fh-multi-leg .fh-search-field:nth-of-type(3){grid-column:1/-1}.fh-multi-remove{position:absolute;right:22px;margin-top:-2px;width:40px}.fh-multi-leg{position:relative}}@media(max-width:520px){.fh-multi-leg{grid-template-columns:1fr}.fh-multi-leg .fh-search-field:nth-of-type(3){grid-column:auto}}
</style>


<script>
/* === Robust homepage airport autocomplete (no jQuery UI dependency) === */
(function(){
    const endpoint = '/ajax/get-airport';
    const inputs = [...document.querySelectorAll('[data-airport-input], #location_from, #location_to')].filter(Boolean);

    if (!inputs.length) return;

    const cache = new Map();
    const states = new WeakMap();

    function createMenu(input){
        const menu = document.createElement('ul');
        menu.className = 'ui-autocomplete ui-front ui-menu ui-widget ui-widget-content fh-native-airport-menu';
        menu.setAttribute('role','listbox');
        menu.style.display = 'none';
        menu.style.position = 'absolute';
        menu.style.listStyle = 'none';
        menu.style.zIndex = '1000002';
        document.body.appendChild(menu);

        const state = {
            menu,
            rows: [],
            activeIndex: -1,
            timer: null,
            controller: null
        };
        states.set(input,state);

        function position(){
            if (menu.style.display === 'none') return;
            const anchor = input.closest('.fh-input') || input;
            const rect = anchor.getBoundingClientRect();

            if (window.innerWidth <= 760) {
                const viewportGap = 8;
                const fieldWidth = Math.min(rect.width, window.innerWidth - (viewportGap * 2));
                const left = Math.min(
                    Math.max(viewportGap, rect.left),
                    window.innerWidth - viewportGap - fieldWidth
                );
                let top = rect.bottom;
                let available = window.innerHeight - top - viewportGap;
                let maxHeight = Math.min(270, Math.max(150, available));

                // If the keyboard/viewport leaves too little room below, open upward.
                if (available < 170) {
                    maxHeight = Math.min(300, Math.max(150, rect.top - 14));
                    top = Math.max(viewportGap, rect.top - maxHeight - 6);
                }

                menu.style.setProperty('position', 'fixed', 'important');
                menu.style.setProperty('left', left + 'px', 'important');
                menu.style.removeProperty('right');
                menu.style.setProperty('top', top + 'px', 'important');
                menu.style.setProperty('width', fieldWidth + 'px', 'important');
                menu.style.setProperty('max-width', fieldWidth + 'px', 'important');
                menu.style.setProperty('max-height', maxHeight + 'px', 'important');
                return;
            }

            menu.style.removeProperty('position');
            menu.style.removeProperty('right');
            menu.style.removeProperty('left');
            menu.style.removeProperty('top');
            menu.style.removeProperty('width');
            menu.style.removeProperty('max-width');
            menu.style.removeProperty('max-height');
            menu.style.setProperty('position','absolute','important');
            menu.style.setProperty('right','auto','important');
            menu.style.setProperty('left',(window.scrollX + rect.left)+'px','important');
            menu.style.setProperty('top',(window.scrollY + rect.bottom)+'px','important');
            menu.style.setProperty('width',rect.width+'px','important');
            menu.style.setProperty('max-width','none','important');
        }

        state.position = position;
        window.addEventListener('resize',position,{passive:true});
        window.addEventListener('scroll',position,{passive:true});

        return state;
    }

    function close(input){
        const state = states.get(input);
        if (!state) return;
        state.menu.style.display = 'none';
        state.menu.innerHTML = '';
        state.rows = [];
        state.activeIndex = -1;
    }

    function choose(input,item){
        input.value = item.value || item.label || '';
        input.dataset.airportCode = item.code || '';
        input.classList.add('fh-airport-picked');

        const fieldBox = input.closest('.fh-input');
        if (fieldBox) {
            fieldBox.classList.add('fh-airport-selected');
        }

        close(input);
        input.dispatchEvent(new Event('change',{bubbles:true}));
    }

    function setActive(input,index){
        const state = states.get(input);
        if (!state || !state.rows.length) return;

        index = Math.max(0,Math.min(index,state.rows.length-1));
        state.activeIndex = index;

        [...state.menu.children].forEach((li,i)=>{
            const isActive = i === index;
            li.classList.toggle('ui-state-active',isActive);
            li.setAttribute('aria-selected',isActive ? 'true' : 'false');

            const row = li.querySelector('.fh-airport-result');
            if (row) {
                row.classList.toggle('fh-airport-active',isActive);
            }
        });

        const active = state.menu.children[index];
        if (active) active.scrollIntoView({block:'nearest'});
    }

    function render(input,items){
        const state = states.get(input) || createMenu(input);
        const menu = state.menu;

        menu.innerHTML = '';
        state.rows = Array.isArray(items) ? items : [];
        state.activeIndex = -1;

        if (!state.rows.length){
            close(input);
            return;
        }

        state.rows.forEach((item,index)=>{
            const li = document.createElement('li');
            li.className = 'ui-menu-item';
            li.setAttribute('role','option');

            const row = document.createElement('div');
            row.className = 'fh-airport-result';

            const icon = document.createElement('span');
            icon.className = 'fh-airport-result-icon';
            icon.innerHTML = '<i class="fa-solid fa-plane-departure"></i>';

            const main = document.createElement('span');
            main.className = 'fh-airport-result-main';

            const line = document.createElement('span');
            line.className = 'fh-airport-result-line';

            const code = document.createElement('strong');
            code.className = 'fh-airport-result-code';
            code.textContent = item.code || String(item.value || '').split(' - ')[0] || '';

            const city = document.createElement('span');
            city.className = 'fh-airport-result-city';
            city.textContent = item.city || '';

            const airport = document.createElement('small');
            airport.className = 'fh-airport-result-name';
            airport.textContent = item.airport || 'Airport';

            const country = document.createElement('span');
            country.className = 'fh-airport-result-country';
            country.textContent = item.country || '';

            line.append(code,city);
            main.append(airport,line);
            row.append(icon,main,country);
            li.appendChild(row);

            li.addEventListener('mousedown',function(e){
                e.preventDefault();
                choose(input,item);
            });

            li.addEventListener('mouseenter',function(){
                setActive(input,index);
            });

            menu.appendChild(li);
        });

        menu.style.display = 'block';
        state.position();

        // Match the previous behavior: first suggestion is visibly BLUE immediately.
        setActive(input,0);
    }

    async function search(input){
        const state = states.get(input) || createMenu(input);
        const term = String(input.value || '').trim();

        if (input.classList.contains('fh-airport-picked')) return;

        if (term.length < 1){
            close(input);
            return;
        }

        const key = term.toLowerCase();

        if (cache.has(key)){
            render(input,cache.get(key));
            return;
        }

        if (state.controller) state.controller.abort();
        state.controller = new AbortController();

        try{
            const url = endpoint + '?term=' + encodeURIComponent(term);
            const res = await fetch(url,{
                method:'GET',
                headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
                signal:state.controller.signal,
                credentials:'same-origin',
                cache:'force-cache'
            });

            if (!res.ok) throw new Error('Airport search failed: ' + res.status);

            const data = await res.json();
            const rows = Array.isArray(data) ? data : [];
            cache.set(key,rows);

            if (String(input.value || '').trim().toLowerCase() === key){
                render(input,rows);
            }
        }catch(err){
            if (err && err.name === 'AbortError') return;
            console.error('Airport autocomplete error:',err);
            close(input);
        }
    }

    function bindInputs(nodeList){ [...nodeList].forEach(input=>{
        if(states.has(input)) return;
        const state = createMenu(input);

        input.setAttribute('autocomplete','off');

        input.addEventListener('input',function(){
            input.classList.remove('fh-airport-picked');
            delete input.dataset.airportCode;

            const fieldBox = input.closest('.fh-input');
            if (fieldBox) {
                fieldBox.classList.remove('fh-airport-selected');
            }

            const term = String(input.value || '').trim().toLowerCase();

            // INSTANT FEEL:
            // If the previous prefix is already cached, show matching rows immediately
            // while the exact term is fetched in the background.
            if (term.length >= 1) {
                let instantRows = [];

                for (const [cachedTerm, rows] of cache.entries()) {
                    if (term.startsWith(cachedTerm) || cachedTerm.startsWith(term)) {
                        instantRows = (Array.isArray(rows) ? rows : []).filter(function(item){
                            const haystack = [
                                item.code,
                                item.city,
                                item.airport,
                                item.country,
                                item.label,
                                item.value
                            ].filter(Boolean).join(' ').toLowerCase();

                            return haystack.includes(term);
                        });

                        if (instantRows.length) break;
                    }
                }

                if (instantRows.length) {
                    render(input,instantRows);
                }
            } else {
                close(input);
            }

            clearTimeout(state.timer);

            // Very small debounce: protects backend from every single key event,
            // but feels effectively instant to the user.
            state.timer = setTimeout(()=>search(input),25);
        });

        input.addEventListener('focus',function(){
            if (String(input.value || '').trim().length >= 1 &&
                !input.classList.contains('fh-airport-picked')){
                search(input);
            }
        });

        input.addEventListener('keydown',function(e){
            const s = states.get(input);
            if (!s || s.menu.style.display === 'none') return;

            if (e.key === 'ArrowDown'){
                e.preventDefault();
                setActive(input,s.activeIndex < 0 ? 0 : s.activeIndex + 1);
            }else if (e.key === 'ArrowUp'){
                e.preventDefault();
                setActive(input,s.activeIndex <= 0 ? 0 : s.activeIndex - 1);
            }else if (e.key === 'Enter' && s.activeIndex >= 0){
                e.preventDefault();
                choose(input,s.rows[s.activeIndex]);
            }else if (e.key === 'Escape'){
                close(input);
            }
        });
    });

    document.addEventListener('mousedown',function(e){
        inputs.forEach(input=>{
            const state = states.get(input);
            if (!state) return;
            if (e.target !== input && !state.menu.contains(e.target)){
                close(input);
            }
        });
    }); }
    bindInputs(inputs);
    window.fhBindAirportInput=bindInputs;
})();

const flatpickrBase={
    minDate:'today',
    dateFormat:'m/d/Y',
    altInput:true,
    altFormat:'D, M j',
    disableMobile:false,
    animate:true,
    monthSelectorType:'static',
    position:'below right',
    appendTo:document.body,
    onOpen:[function(){
        if(window.innerWidth<=760) document.body.classList.add('fh-date-picker-open');
    }],
    onClose:[function(){
        document.body.classList.remove('fh-date-picker-open');
    }]
};

// Multi-City search: isolated from the existing One Way/Round Trip form fields.
(function(){
    const box=document.getElementById('fhMultiCityLegs');
    const add=document.getElementById('fhAddMultiLeg');
    if(!box||!add) return;
    let count=0;
    function legTemplate(i){return `<div class="fh-multi-leg" data-multi-leg>
        <div class="fh-multi-num">Flight ${i+1}</div>
        <label class="fh-search-field"><span>{{ $cmsv('home.search.from_label','Flying from') }}</span><div class="fh-input"><i class="fa-solid fa-location-dot"></i><input data-airport-input type="text" name="multi_from[]" placeholder="City or airport" autocomplete="off"></div></label>
        <label class="fh-search-field"><span>{{ $cmsv('home.search.to_label','Flying to') }}</span><div class="fh-input"><i class="fa-solid fa-plane"></i><input data-airport-input type="text" name="multi_to[]" placeholder="City or airport" autocomplete="off"></div></label>
        <label class="fh-search-field"><span>{{ $cmsv('home.search.departure_label','Departure') }}</span><div class="fh-input"><i class="fa-regular fa-calendar"></i><input data-multi-date type="text" name="multi_date[]" placeholder="{{ $cmsv('home.search.departure_label','Departure') }}"></div></label>
        <button type="button" class="fh-multi-remove" aria-label="Remove flight"><i class="fa-solid fa-xmark"></i></button>
    </div>`}
    function refresh(){
        [...box.querySelectorAll('[data-multi-leg]')].forEach((el,i)=>{el.querySelector('.fh-multi-num').textContent='Flight '+(i+1);el.querySelector('.fh-multi-remove').hidden=(box.children.length<=2)});
        add.disabled=box.children.length>=5;
    }
    function addLeg(){if(box.children.length>=5)return;box.insertAdjacentHTML('beforeend',legTemplate(count++));const el=box.lastElementChild;flatpickr(el.querySelector('[data-multi-date]'),{...flatpickrBase,minDate:'today'});window.fhBindAirportInput?.(el.querySelectorAll('[data-airport-input]'));refresh()}
    box.addEventListener('click',e=>{const b=e.target.closest('.fh-multi-remove');if(b&&box.children.length>2){b.closest('[data-multi-leg]').remove();refresh()}});
    add.addEventListener('click',addLeg);addLeg();addLeg();
    window.fhToggleMultiCity=function(on){document.getElementById('fhStandardSearch').hidden=on;document.getElementById('fhMultiCitySearch').hidden=!on;document.querySelectorAll('#fhStandardSearch input').forEach(x=>x.required=!on && ['location_from','location_to','depature_date'].includes(x.id));document.querySelectorAll('#fhMultiCitySearch input').forEach(x=>x.required=on);};
})();

const dep=flatpickr('#depature_date',{
    ...flatpickrBase,
    onChange:function(ds){
        if(ds[0]){
            ret.set('minDate',ds[0]);
            const currentReturn=ret.selectedDates[0];
            if(currentReturn && currentReturn<ds[0]) ret.clear();
        }
    }
});

const ret=flatpickr('#return_date',{
    ...flatpickrBase
});

/* Accessibility: keep Flatpickr's native mobile date inputs in natural tab order. */
document.querySelectorAll('.flatpickr-mobile').forEach(function(input){
    const tabindex = parseInt(input.getAttribute('tabindex'), 10);
    if (Number.isFinite(tabindex) && tabindex > 0) {
        input.removeAttribute('tabindex');
    }
});

function tnbSearchDeal(from,to,cabin,days,tripType='round_trip',returnDays=7){
    const form=document.getElementById('form2');
    if(!form) return;

    tripType = tripType === 'one_way' ? 'one_way' : 'round_trip';
    returnDays = Math.max(1, Number(returnDays) || 7);

    document.getElementById('location_from').value=from;
    document.getElementById('location_to').value=to;
    const cabinSel=document.getElementById('cabin_classes');
    cabinSel.value=cabin||'Econom';
    cabinSel.dispatchEvent(new Event('change',{bubbles:true}));

    const oneWay=document.getElementById('flight_types_one_way');
    const roundTrip=document.getElementById('flight_types_round_trip');
    const returnInput=document.getElementById('return_date');

    oneWay.checked = tripType === 'one_way';
    roundTrip.checked = tripType === 'round_trip';

    document.querySelectorAll('.tnb-trip-tab').forEach(b=>{
        b.classList.toggle('active', b.dataset.trip===tripType);
    });

    const departure=new Date();
    departure.setHours(0,0,0,0);
    departure.setDate(departure.getDate()+(Number(days)||30));
    dep.setDate(departure,true);

    if(tripType==='round_trip'){
        const returnDate=new Date(departure);
        returnDate.setDate(returnDate.getDate()+returnDays);

        returnInput.disabled=false;
        returnInput.setAttribute('required','required');
        ret.setDate(returnDate,true);
    }else{
        ret.clear();
        returnInput.removeAttribute('required');
        returnInput.disabled=true;
    }

    if (typeof window.fhShowSearchLoader === 'function') {
        window.fhShowSearchLoader();
    }
    form.submit();
}
(function(){
    const allFareNodes=[...document.querySelectorAll('[data-route-fare]')];

    allFareNodes.forEach(el=>{
        const mode=(el.dataset.fareMode || 'manual').toLowerCase();
        if(mode !== 'manual') return;
        const raw=String(el.dataset.displayFare || '').trim();
        const fare=Number(raw);
        const tripLabel=(el.dataset.tripType || 'round_trip') === 'one_way' ? 'One Way' : 'Round Trip';
        el.dataset.loaded='1';
        el.dataset.tripLabel=tripLabel;
        if(raw !== '' && Number.isFinite(fare)){
            el.classList.remove('fh-fare-missing');
            el.textContent='$'+fare.toLocaleString('en-US',{minimumFractionDigits:0,maximumFractionDigits:2});
            el.setAttribute('title','Starting fare · '+tripLabel);
        }else{
            el.textContent='Check fare';
            el.classList.add('fh-fare-missing');
            el.setAttribute('title','Search for current fare');
        }
    });

    const nodes=allFareNodes.filter(el=>(el.dataset.fareMode || 'manual').toLowerCase()==='live');
    const q=[];
    let active=0;
    const max=4;

    function loadFare(el){
        if(!el || el.dataset.loaded==='1') return;
        el.dataset.loaded='1';
        el.classList.add('fh-fare-is-loading');
        active++;

        const params=new URLSearchParams({
            from:el.dataset.from || '',
            to:el.dataset.to || '',
            cabin:el.dataset.cabin || 'Econom',
            days:el.dataset.days || '30',
            trip_type:el.dataset.tripType || 'round_trip',
            return_days:el.dataset.returnDays || '7'
        });

        fetch('/ajax/route-fare?' + params.toString(), {
            method:'GET',
            headers:{'Accept':'application/json'},
            credentials:'same-origin'
        })
        .then(async response=>{
            if(!response.ok){
                throw new Error('Fare request failed: '+response.status);
            }
            return response.json();
        })
        .then(j=>{
            if(j && j.ok && j.label){
                const tripLabel = j.trip_label || ((el.dataset.tripType || 'round_trip') === 'one_way' ? 'One Way' : 'Round Trip');
                el.classList.remove('fh-fare-missing');
                el.textContent = j.label;
                el.setAttribute('title', tripLabel + ' fare');
                el.dataset.tripLabel = tripLabel;

                const premiumRow=el.closest('[data-premium-row]');
                if(premiumRow && j.price){
                    const discount=Math.max(1,Math.min(80,Number(premiumRow.dataset.discount)||33));
                    const live=Number(j.price);
                    const oldFare=premiumRow.querySelector('.fh-premium-oldfare');
                    if(oldFare && Number.isFinite(live)){
                        const oldPrice=live/(1-(discount/100));
                        oldFare.textContent='$'+Math.round(oldPrice).toLocaleString('en-US');
                    }
                }
            }else{
                el.textContent='Check fare';
                el.classList.add('fh-fare-missing');
            }
        })
        .catch(err=>{
            console.error('Route fare error', err);
            el.textContent='Check fare';
            el.classList.add('fh-fare-missing');
        })
        .finally(()=>{
            el.classList.remove('fh-fare-is-loading');
            active--;
            run();
        });
    }

    function run(){
        while(active<max && q.length){
            const el=q.shift();
            if(!el || el.dataset.loaded==='1') continue;
            loadFare(el);
        }
    }

    if('IntersectionObserver' in window){
        const io=new IntersectionObserver(entries=>{
            entries.forEach(entry=>{
                if(entry.isIntersecting){
                    q.push(entry.target);
                    io.unobserve(entry.target);
                }
            });
            run();
        },{rootMargin:'350px'});
        nodes.forEach(n=>io.observe(n));
    }else{
        q.push(...nodes);
        run();
    }
})();
</script>


<style id="fh-popular-airlines-logo-name-final-v4">
/* =========================================================
   POPULAR AIRLINES — FINAL FIX
   Logo on left + airline name on right.
   No airline code text inside logo box or under the name.
   Visual-only; DB/CMS/search logic untouched.
   ========================================================= */

.fh-home .fh-airlines-reference{
    padding-top:40px!important;
    padding-bottom:46px!important;
    background:#f6f8fc!important;
}

.fh-home .fh-airlines-reference>.container{
    width:100%!important;
    max-width:1700px!important;
    margin:0 auto!important;
    padding-left:30px!important;
    padding-right:30px!important;
}

.fh-home .fh-airlines-reference .fh-section-head{
    margin-bottom:24px!important;
}

.fh-home .fh-airlines-reference .fh-airline-grid{
    display:grid!important;
    grid-template-columns:repeat(6,minmax(0,1fr))!important;
    gap:14px!important;
}

.fh-home .fh-airlines-reference .fh-airline-card{
    position:relative!important;
    display:flex!important;
    flex-direction:row!important;
    align-items:center!important;
    justify-content:flex-start!important;
    gap:14px!important;

    min-width:0!important;
    min-height:92px!important;
    padding:14px 16px!important;

    border:1px solid #dce4ef!important;
    border-radius:18px!important;
    background:
        radial-gradient(circle at 0 0,rgba(232,238,247,.50),transparent 34%),
        linear-gradient(145deg,#ffffff 0%,#fbfcff 100%)!important;

    color:#071a34!important;
    text-align:left!important;
    text-decoration:none!important;

    box-shadow:
        0 8px 22px rgba(18,42,72,.05),
        inset 0 1px 0 rgba(255,255,255,.96)!important;

    overflow:hidden!important;

    transition:
        transform .18s ease,
        border-color .18s ease,
        box-shadow .18s ease!important;
}

.fh-home .fh-airlines-reference .fh-airline-card:hover{
    transform:translateY(-3px)!important;
    border-color:#d6b45d!important;
    box-shadow:
        0 15px 32px rgba(18,42,72,.09),
        0 0 0 1px rgba(214,180,93,.06)!important;
}

/* subtle premium gold hover line */
.fh-home .fh-airlines-reference .fh-airline-card::after{
    content:""!important;
    position:absolute!important;
    left:22%!important;
    right:22%!important;
    bottom:0!important;
    height:2px!important;
    background:linear-gradient(90deg,transparent,#d8a72d,transparent)!important;
    opacity:0!important;
    transition:opacity .18s ease!important;
}
.fh-home .fh-airlines-reference .fh-airline-card:hover::after{
    opacity:1!important;
}

/* LOGO BOX */
.fh-home .fh-airlines-reference .fh-airline-logo{
    flex:0 0 60px!important;
    display:flex!important;
    align-items:center!important;
    justify-content:center!important;

    width:60px!important;
    height:60px!important;

    margin:0!important;
    padding:8px!important;

    border:1px solid #e5ebf3!important;
    border-radius:15px!important;
    background:#fff!important;

    box-shadow:0 5px 14px rgba(15,40,72,.04)!important;

    overflow:hidden!important;
}

/* actual logo only */
.fh-home .fh-airlines-reference .fh-airline-logo img{
    display:block!important;
    width:100%!important;
    height:100%!important;
    max-width:48px!important;
    max-height:40px!important;

    object-fit:contain!important;
    object-position:center!important;

    margin:0!important;
    padding:0!important;

    border:0!important;
    border-radius:0!important;
    background:transparent!important;
}

/* IMPORTANT:
   existing onerror fallback was showing UA / AA / DL etc beside logo.
   User requested no text inside logo box, so hide it completely. */
.fh-home .fh-airlines-reference .fh-airline-logo span{
    display:none!important;
}

/* AIRLINE NAME — right side only */
.fh-home .fh-airlines-reference .fh-airline-card strong{
    display:block!important;
    flex:1 1 auto!important;

    width:auto!important;
    min-width:0!important;

    margin:0!important;

    color:#081c36!important;
    font-size:13px!important;
    line-height:1.28!important;
    font-weight:700!important;
    letter-spacing:-.012em!important;

    text-align:left!important;
    white-space:normal!important;
    overflow:visible!important;
    text-overflow:clip!important;
}

/* remove UA / AA / DL / etc under airline name */
.fh-home .fh-airlines-reference .fh-airline-card > small{
    display:none!important;
}

/* TABLET */
@media(max-width:1280px){
    .fh-home .fh-airlines-reference .fh-airline-grid{
        grid-template-columns:repeat(4,minmax(0,1fr))!important;
    }
}

@media(max-width:900px){
    .fh-home .fh-airlines-reference>.container{
        padding-left:20px!important;
        padding-right:20px!important;
    }

    .fh-home .fh-airlines-reference .fh-airline-grid{
        grid-template-columns:repeat(3,minmax(0,1fr))!important;
    }
}

/* MOBILE */
@media(max-width:680px){
    .fh-home .fh-airlines-reference{
        padding-top:32px!important;
        padding-bottom:36px!important;
    }

    .fh-home .fh-airlines-reference>.container{
        padding-left:14px!important;
        padding-right:14px!important;
    }

    .fh-home .fh-airlines-reference .fh-airline-grid{
        grid-template-columns:repeat(2,minmax(0,1fr))!important;
        gap:10px!important;
    }

    .fh-home .fh-airlines-reference .fh-airline-card{
        min-height:78px!important;
        padding:11px 12px!important;
        gap:10px!important;
        border-radius:14px!important;
    }

    .fh-home .fh-airlines-reference .fh-airline-logo{
        flex:0 0 48px!important;
        width:48px!important;
        height:48px!important;
        padding:6px!important;
        border-radius:12px!important;
    }

    .fh-home .fh-airlines-reference .fh-airline-logo img{
        max-width:38px!important;
        max-height:32px!important;
    }

    .fh-home .fh-airlines-reference .fh-airline-card strong{
        font-size:11.5px!important;
    }
}
</style>


<style id="fh-home-deals-intro-master-final">
/* Homepage deals intro — compact, premium, same site font */
.fh-home .fh-deals-intro{
    position:relative!important;
    display:block!important;
    width:100%!important;
    min-height:0!important;
    height:auto!important;
    margin:0!important;
    padding:14px 20px 22px!important;
    overflow:hidden!important;
    text-align:center!important;
    background:
        radial-gradient(circle at 50% 42%,
            rgba(225,174,55,.05) 0%,
            rgba(225,174,55,.015) 22%,
            transparent 45%),
        #fff!important;
}

.fh-home .fh-deals-intro-inner{
    position:relative!important;
    width:100%!important;
    max-width:980px!important;
    min-height:0!important;
    height:auto!important;
    margin:0 auto!important;
    padding:0!important;
}

.fh-home .fh-deals-intro-label{
    position:relative!important;
    top:-12px!important;
    margin:0!important;
    padding:0!important;
    color:#d89b17!important;
    font-family:inherit!important;
    font-size:10px!important;
    line-height:1!important;
    font-weight:700!important;
    letter-spacing:.22em!important;
    text-transform:uppercase!important;
}

.fh-home .fh-deals-intro-divider{
    display:flex!important;
    align-items:center!important;
    justify-content:center!important;
    width:min(270px,62%)!important;
    height:auto!important;
    margin:9px auto 11px!important;
    padding:0!important;
    gap:9px!important;
}

.fh-home .fh-deals-intro-divider span{
    display:block!important;
    flex:1 1 auto!important;
    height:1px!important;
    margin:0!important;
    padding:0!important;
    background:linear-gradient(90deg,transparent 0%,rgba(207,148,15,.62) 100%)!important;
}

.fh-home .fh-deals-intro-divider span:last-child{
    background:linear-gradient(90deg,rgba(207,148,15,.62) 0%,transparent 100%)!important;
}

.fh-home .fh-deals-intro-divider i{
    flex:0 0 auto!important;
    margin:0!important;
    padding:0!important;
    color:#d69a12!important;
    font-size:8px!important;
    line-height:1!important;
    transform:rotate(45deg)!important;
}

.fh-home .fh-deals-intro h2{
    margin:0!important;
    padding:0!important;
    color:#071a34!important;
    font-family:inherit!important;
    font-size:clamp(32px,2.55vw,44px)!important;
    line-height:1.06!important;
    font-weight:600!important;
    letter-spacing:-.035em!important;
    text-rendering:optimizeLegibility!important;
}

/* Featured section starts close to intro */
.fh-home .fh-deals-intro + .fd-section{
    margin-top:0!important;
    padding-top:18px!important;
}

@media(max-width:767px){
    .fh-home .fh-deals-intro{
        padding:10px 14px 18px!important;
    }
    .fh-home .fh-deals-intro-label{
        top:-8px!important;
        font-size:9px!important;
        letter-spacing:.19em!important;
    }
    .fh-home .fh-deals-intro-divider{
        width:min(215px,76%)!important;
        margin:8px auto 10px!important;
        gap:8px!important;
    }
    .fh-home .fh-deals-intro-divider i{
        font-size:7px!important;
    }
    .fh-home .fh-deals-intro h2{
        font-size:29px!important;
        line-height:1.08!important;
        letter-spacing:-.028em!important;
    }
}
</style>


<style id="fh-home-original-zigzag-no-fade-master-final">
/* Keep the original hero SVG/zigzag exactly, remove only faded wash below it */
.fh-home .fh-hero{
    position:relative!important;
    z-index:10!important;
    isolation:isolate!important;
    margin-bottom:0!important;
    border-bottom:0!important;
    box-shadow:none!important;
}

/* Solid navy only behind the lower hero/trust area */
.fh-home .fh-hero::after{
    content:""!important;
    position:absolute!important;
    left:0!important;
    right:0!important;
    bottom:0!important;
    height:96px!important;
    z-index:1!important;
    pointer-events:none!important;
    background:#0b1c35!important;
    opacity:1!important;
    filter:none!important;
}

.fh-home .fh-hero-inner{
    position:relative!important;
    z-index:2!important;
}

.fh-home .fh-hero-trust{
    position:relative!important;
    z-index:4!important;
}

/* Original existing SVG remains the separator */
.fh-home .fh-hero-wave{
    display:block!important;
    position:absolute!important;
    left:0!important;
    right:0!important;
    bottom:-1px!important;
    width:100%!important;
    height:50px!important;
    z-index:5!important;
    opacity:1!important;
    visibility:visible!important;
    filter:none!important;
    box-shadow:none!important;
}

.fh-home .fh-hero-wave path{
    fill:#fff!important;
    fill-opacity:1!important;
    opacity:1!important;
    stroke:none!important;
    filter:none!important;
}

.fh-home .fh-hero + *{
    margin-top:0!important;
    border-top:0!important;
    box-shadow:none!important;
}

@media(max-width:767px){
    .fh-home .fh-hero::after{
        height:78px!important;
    }
    .fh-home .fh-hero-wave{
        height:36px!important;
    }
}
</style>


<style id="fh-home-deals-layout-cleanup-v1">
/* =========================================================
   HOMEPAGE DEALS LAYOUT CLEANUP V1
   - No redesign
   - Keep existing cards, images, colors, live-fare behavior
   - Reduce vertical whitespace
   - Make grids use full desktop width
   - Remove duplicate trust strip above premium banner
   ========================================================= */

/* Shared container width */
.fh-home .fh-deals-shot .fd-container{
    width:min(1840px,calc(100% - 40px))!important;
    max-width:none!important;
    margin-left:auto!important;
    margin-right:auto!important;
}

/* Consistent compact section rhythm */
.fh-home .fh-deals-shot .fd-section{
    padding:28px 0 24px!important;
    margin:0!important;
}

.fh-home .fh-deals-shot .fd-premium-section{
    padding:28px 0 24px!important;
    margin:0!important;
}

/* Remove large inherited gaps */
.fh-home .fh-deals-shot .fd-section + .fd-section,
.fh-home .fh-deals-shot .fd-section + .fd-premium-section,
.fh-home .fh-deals-shot .fd-premium-section + .fd-section{
    margin-top:0!important;
}

/* Heading spacing */
.fh-home .fh-deals-shot .fd-head{
    margin-bottom:16px!important;
}

/* FEATURED: 4 equal full-width cards */
.fh-home .fh-deals-shot .fd-grid.featured{
    grid-template-columns:repeat(4,minmax(0,1fr))!important;
    gap:18px!important;
}

/* TOP USA: 6 equal-width cards filling available width */
.fh-home .fh-deals-shot .fd-grid.domestic{
    grid-template-columns:repeat(6,minmax(0,1fr))!important;
    gap:14px!important;
}

.fh-home .fh-deals-shot .fd-domestic{
    min-height:118px!important;
}

/* INTERNATIONAL / HOLIDAY / LAST MINUTE: 4 cards across */
.fh-home .fh-deals-shot .fd-grid.international,
.fh-home .fh-deals-shot .fd-grid.holiday,
.fh-home .fh-deals-shot .fd-grid.last{
    grid-template-columns:repeat(4,minmax(0,1fr))!important;
    gap:18px!important;
}

/* BUSINESS / FIRST: 4 across, same visual density */
.fh-home .fh-deals-shot .fd-premium-grid{
    grid-template-columns:repeat(4,minmax(0,1fr))!important;
    gap:16px!important;
}

.fh-home .fh-deals-shot .fd-premium-panel{
    padding:20px!important;
}

/* Remove duplicate trust strip from homepage only */
.fh-home .fh-deals-shot .fd-trust-strip{
    display:none!important;
}

/* Keep premium banner close to last-minute section */
.fh-home .fh-deals-shot .fd-premium-banner{
    margin-top:24px!important;
    margin-bottom:28px!important;
}

/* Prevent unnecessary oversized whitespace around the deals area */
.fh-home .fh-deals-shot{
    padding-bottom:0!important;
}

/* Tablet */
@media(max-width:1200px){
    .fh-home .fh-deals-shot .fd-container{
        width:min(100% - 32px,1500px)!important;
    }

    .fh-home .fh-deals-shot .fd-grid.featured,
    .fh-home .fh-deals-shot .fd-grid.international,
    .fh-home .fh-deals-shot .fd-grid.holiday,
    .fh-home .fh-deals-shot .fd-grid.last,
    .fh-home .fh-deals-shot .fd-premium-grid{
        grid-template-columns:repeat(2,minmax(0,1fr))!important;
    }

    .fh-home .fh-deals-shot .fd-grid.domestic{
        grid-template-columns:repeat(3,minmax(0,1fr))!important;
    }
}

/* Mobile */
@media(max-width:760px){
    .fh-home .fh-deals-shot .fd-container{
        width:calc(100% - 24px)!important;
    }

    .fh-home .fh-deals-shot .fd-section,
    .fh-home .fh-deals-shot .fd-premium-section{
        padding:22px 0 18px!important;
    }

    .fh-home .fh-deals-shot .fd-grid.featured,
    .fh-home .fh-deals-shot .fd-grid.domestic,
    .fh-home .fh-deals-shot .fd-grid.international,
    .fh-home .fh-deals-shot .fd-grid.holiday,
    .fh-home .fh-deals-shot .fd-grid.last,
    .fh-home .fh-deals-shot .fd-premium-grid{
        grid-template-columns:1fr!important;
        gap:14px!important;
    }

    .fh-home .fh-deals-shot .fd-premium-banner{
        margin-top:18px!important;
        margin-bottom:22px!important;
    }
}
</style>


<style id="fh-premium-light-image-banner-v1">
/* Light Business & First Class promotional banner.
   Uses the supplied/generated visual as-is and keeps the banner clickable. */
.fh-home .fh-deals-shot .fd-premium-banner-image{
    display:block!important;
    width:100%!important;
    margin:26px 0 30px!important;
    overflow:hidden!important;
    border:1px solid rgba(211,163,72,.26)!important;
    border-radius:22px!important;
    background:#fff!important;
    box-shadow:0 14px 34px rgba(14,32,58,.08)!important;
    text-decoration:none!important;
    line-height:0!important;
}

.fh-home .fh-deals-shot .fd-premium-banner-image img{
    display:block!important;
    width:100%!important;
    height:auto!important;
    min-height:280px!important;
    max-height:430px!important;
    object-fit:cover!important;
    object-position:center center!important;
}

.fh-home .fh-deals-shot .fd-premium-banner-image:hover{
    transform:translateY(-1px)!important;
    box-shadow:0 18px 42px rgba(14,32,58,.11)!important;
}

@media(max-width:900px){
    .fh-home .fh-deals-shot .fd-premium-banner-image{
        margin:22px 0 26px!important;
        border-radius:18px!important;
    }

    .fh-home .fh-deals-shot .fd-premium-banner-image img{
        min-height:220px!important;
        max-height:none!important;
    }
}

@media(max-width:600px){
    .fh-home .fh-deals-shot .fd-premium-banner-image{
        margin:18px 0 22px!important;
        border-radius:15px!important;
    }

    .fh-home .fh-deals-shot .fd-premium-banner-image img{
        min-height:0!important;
        width:155%!important;
        max-width:none!important;
        transform:translateX(-3%)!important;
    }
}
</style>


<style id="fh-why-premium-cleanup-v1">
/* =========================================================
   WHY TRAVELERS CHOOSE FARESHUNT — PREMIUM COMPACT PASS
   Visual-only cleanup. Content and functionality untouched.
   ========================================================= */

.fh-home .fh-why{
    padding:58px 0 60px!important;
    background:
        radial-gradient(circle at 50% 8%, rgba(34,79,132,.035), transparent 36%),
        #f7f9fc!important;
    border-top:1px solid #edf1f6!important;
    border-bottom:1px solid #e8edf4!important;
}

.fh-home .fh-why>.container{
    width:min(1600px,calc(100% - 48px))!important;
    max-width:none!important;
    margin:0 auto!important;
    padding:0!important;
}

/* Heading */
.fh-home .fh-why .fh-why-reference-head{
    margin:0 auto 32px!important;
    padding:0!important;
}

.fh-home .fh-why .fh-why-reference-head>div{
    max-width:860px!important;
}

.fh-home .fh-why .fh-why-reference-head h2{
    margin:0 0 8px!important;
    color:#071a34!important;
    font-family:inherit!important;
    font-size:clamp(38px,3vw,52px)!important;
    line-height:1.06!important;
    font-weight:400!important;
    letter-spacing:-.045em!important;
}

.fh-home .fh-why .fh-why-reference-head h2 em{
    color:#d91f43!important;
    font:inherit!important;
    font-style:normal!important;
    font-weight:400!important;
}

.fh-home .fh-why .fh-why-reference-head p{
    max-width:700px!important;
    margin:0 auto!important;
    color:#5f7393!important;
    font-family:inherit!important;
    font-size:14px!important;
    line-height:1.45!important;
    font-weight:400!important;
}

/* 4 x 2 compact grid */
.fh-home .fh-why-grid{
    display:grid!important;
    grid-template-columns:repeat(4,minmax(0,1fr))!important;
    gap:18px!important;
}

/* Card */
.fh-home .fh-why-card{
    display:flex!important;
    flex-direction:column!important;
    align-items:center!important;
    justify-content:flex-start!important;
    min-width:0!important;
    min-height:178px!important;
    padding:24px 22px 22px!important;

    border:1px solid #e1e7f0!important;
    border-radius:22px!important;
    background:#fff!important;

    text-align:center!important;
    box-shadow:
        0 10px 26px rgba(24,45,78,.055),
        0 1px 0 rgba(255,255,255,.85) inset!important;

    transition:
        transform .18s ease,
        border-color .18s ease,
        box-shadow .18s ease!important;
}

.fh-home .fh-why-card:hover{
    transform:translateY(-2px)!important;
    border-color:#d2dce9!important;
    box-shadow:0 15px 34px rgba(24,45,78,.085)!important;
}

/* One consistent icon system */
.fh-home .fh-why-card>div{
    display:grid!important;
    place-items:center!important;

    width:50px!important;
    height:50px!important;
    margin:0 0 14px!important;

    border:1px solid currentColor!important;
    border-radius:16px!important;

    background:#f7f9fc!important;
    font-size:17px!important;
    line-height:1!important;
}

/* Keep individual accent colors, but remove inconsistent filled/empty treatments */
.fh-home .fh-why-card:nth-child(1)>div{color:#1fa751!important;background:#f2fbf5!important}
.fh-home .fh-why-card:nth-child(2)>div{color:#2563eb!important;background:#f2f7ff!important}
.fh-home .fh-why-card:nth-child(3)>div{color:#8b38dc!important;background:#faf6ff!important}
.fh-home .fh-why-card:nth-child(4)>div{color:#d31d42!important;background:#fff6f8!important}
.fh-home .fh-why-card:nth-child(5)>div{color:#e87518!important;background:#fff8f1!important}
.fh-home .fh-why-card:nth-child(6)>div{color:#b66a00!important;background:#fff9ef!important}
.fh-home .fh-why-card:nth-child(7)>div{color:#0e9488!important;background:#f2fbfa!important}
.fh-home .fh-why-card:nth-child(8)>div{color:#d92f72!important;background:#fff6fa!important}

.fh-home .fh-why-card>div i{
    color:inherit!important;
}

.fh-home .fh-why-card h3{
    margin:0 0 7px!important;
    color:#071a34!important;
    font-family:inherit!important;
    font-size:15px!important;
    line-height:1.25!important;
    font-weight:650!important;
    letter-spacing:-.012em!important;
}

.fh-home .fh-why-card p{
    display:-webkit-box!important;
    -webkit-box-orient:vertical!important;
    -webkit-line-clamp:2!important;
    overflow:hidden!important;

    max-width:310px!important;
    margin:0!important;

    color:#607493!important;
    font-family:inherit!important;
    font-size:12.5px!important;
    line-height:1.48!important;
    font-weight:400!important;
}

/* Tablet */
@media(max-width:1100px){
    .fh-home .fh-why>.container{
        width:min(100% - 36px,1200px)!important;
    }

    .fh-home .fh-why-grid{
        grid-template-columns:repeat(2,minmax(0,1fr))!important;
    }
}

/* Mobile */
@media(max-width:700px){
    .fh-home .fh-why{
        padding:42px 0 44px!important;
    }

    .fh-home .fh-why>.container{
        width:calc(100% - 24px)!important;
    }

    .fh-home .fh-why .fh-why-reference-head{
        margin-bottom:24px!important;
    }

    .fh-home .fh-why .fh-why-reference-head h2{
        font-size:34px!important;
    }

    .fh-home .fh-why .fh-why-reference-head p{
        font-size:13px!important;
    }

    .fh-home .fh-why .fh-why-reference-head p br{
        display:none!important;
    }

    .fh-home .fh-why-grid{
        grid-template-columns:1fr!important;
        gap:14px!important;
    }

    .fh-home .fh-why-card{
        min-height:160px!important;
        padding:21px 18px!important;
        border-radius:18px!important;
    }
}
</style>


<style id="fh-home-guides-premium-v1">
/* =========================================================
   TRAVEL GUIDES & INSIGHTS — HOMEPAGE PREMIUM PASS
   ========================================================= */
.fh-home .fh-guides-reference{
    padding:56px 0 62px!important;
    background:#f7f9fc!important;
    border-top:1px solid #edf1f6!important;
}

.fh-home .fh-guides-reference>.container{
    width:min(1600px,calc(100% - 48px))!important;
    max-width:none!important;
    margin:0 auto!important;
    padding:0!important;
}

.fh-home .fh-guides-head{
    display:flex!important;
    align-items:flex-end!important;
    justify-content:space-between!important;
    gap:30px!important;
    margin:0 0 28px!important;
}

.fh-home .fh-guides-head>div{
    max-width:720px!important;
}

.fh-home .fh-guides-kicker{
    display:block!important;
    margin:0 0 9px!important;
    color:#d89b17!important;
    font-family:inherit!important;
    font-size:10px!important;
    line-height:1!important;
    font-weight:700!important;
    letter-spacing:.20em!important;
}

.fh-home .fh-guides-head h2{
    margin:0 0 7px!important;
    color:#071a34!important;
    font-family:inherit!important;
    font-size:clamp(32px,2.5vw,44px)!important;
    line-height:1.08!important;
    font-weight:600!important;
    letter-spacing:-.04em!important;
}

.fh-home .fh-guides-head p{
    margin:0!important;
    color:#607493!important;
    font-family:inherit!important;
    font-size:14px!important;
    line-height:1.45!important;
}

.fh-home .fh-guides-head>a{
    display:inline-flex!important;
    align-items:center!important;
    gap:9px!important;
    margin:0 0 3px!important;
    color:#071a34!important;
    font-family:inherit!important;
    font-size:13px!important;
    line-height:1!important;
    font-weight:700!important;
    text-decoration:none!important;
    white-space:nowrap!important;
}

.fh-home .fh-guides-head>a i{
    color:#d31d42!important;
}

.fh-home .fh-guide-grid{
    display:grid!important;
    grid-template-columns:repeat(3,minmax(0,1fr))!important;
    gap:20px!important;
}

/* If CMS currently has only one or two articles, do not leave a giant empty canvas. */
.fh-home .fh-guide-grid.fh-guide-count-1{
    grid-template-columns:minmax(0,1fr)!important;
}
.fh-home .fh-guide-grid.fh-guide-count-1 .fh-guide-card{
    display:grid!important;
    grid-template-columns:minmax(360px,.82fr) minmax(0,1.18fr)!important;
    min-height:300px!important;
}

.fh-home .fh-guide-grid.fh-guide-count-2{
    grid-template-columns:repeat(2,minmax(0,1fr))!important;
}

.fh-home .fh-guide-card{
    display:flex!important;
    flex-direction:column!important;
    min-width:0!important;
    overflow:hidden!important;
    border:1px solid #dfe6ef!important;
    border-radius:24px!important;
    background:#fff!important;
    color:#071a34!important;
    text-decoration:none!important;
    box-shadow:0 12px 28px rgba(24,45,78,.055)!important;
    transition:transform .2s ease,border-color .2s ease,box-shadow .2s ease!important;
}
.fh-home .fh-guide-card:hover{
    transform:translateY(-3px)!important;
    border-color:#ccd7e5!important;
    box-shadow:0 18px 38px rgba(24,45,78,.09)!important;
}

.fh-home .fh-guide-image{
    position:relative!important;
    min-height:225px!important;
    background-size:cover!important;
    background-position:center!important;
    background-repeat:no-repeat!important;
}
.fh-home .fh-guide-grid.fh-guide-count-1 .fh-guide-image{
    min-height:300px!important;
}
.fh-home .fh-guide-image:after{
    content:""!important;
    position:absolute!important;
    inset:0!important;
    background:linear-gradient(to bottom,rgba(4,18,38,.03) 35%,rgba(4,18,38,.55) 100%)!important;
    pointer-events:none!important;
}

.fh-home .fh-guide-badge,
.fh-home .fh-guide-read{
    position:absolute!important;
    z-index:2!important;
    display:inline-flex!important;
    align-items:center!important;
    min-height:28px!important;
    border-radius:999px!important;
    font-family:inherit!important;
    font-size:10px!important;
    line-height:1!important;
    font-weight:650!important;
}
.fh-home .fh-guide-badge{
    top:15px!important;
    left:15px!important;
    padding:0 12px!important;
    background:rgba(255,255,255,.93)!important;
    color:#15385d!important;
    box-shadow:0 5px 14px rgba(0,0,0,.08)!important;
}
.fh-home .fh-guide-read{
    right:15px!important;
    bottom:14px!important;
    gap:6px!important;
    padding:0 11px!important;
    background:rgba(7,26,52,.82)!important;
    color:#fff!important;
    backdrop-filter:blur(6px)!important;
}

.fh-home .fh-guide-body{
    display:flex!important;
    flex:1 1 auto!important;
    flex-direction:column!important;
    min-width:0!important;
    padding:21px 22px 20px!important;
}
.fh-home .fh-guide-grid.fh-guide-count-1 .fh-guide-body{
    justify-content:center!important;
    padding:34px 38px!important;
}

.fh-home .fh-guide-date{
    margin:0 0 8px!important;
    color:#d18c08!important;
    font-family:inherit!important;
    font-size:10px!important;
    line-height:1!important;
    font-weight:700!important;
    letter-spacing:.10em!important;
    text-transform:uppercase!important;
}
.fh-home .fh-guide-body h3{
    margin:0 0 10px!important;
    color:#071a34!important;
    font-family:inherit!important;
    font-size:21px!important;
    line-height:1.22!important;
    font-weight:650!important;
    letter-spacing:-.025em!important;
}
.fh-home .fh-guide-grid.fh-guide-count-1 .fh-guide-body h3{
    font-size:clamp(27px,2.2vw,36px)!important;
}
.fh-home .fh-guide-body p{
    display:-webkit-box!important;
    -webkit-box-orient:vertical!important;
    -webkit-line-clamp:3!important;
    overflow:hidden!important;
    margin:0 0 20px!important;
    color:#607493!important;
    font-family:inherit!important;
    font-size:13px!important;
    line-height:1.55!important;
}
.fh-home .fh-guide-footer{
    display:flex!important;
    align-items:center!important;
    justify-content:space-between!important;
    margin-top:auto!important;
    padding-top:15px!important;
    border-top:1px solid #e8edf4!important;
    color:#d31d42!important;
    font-size:12px!important;
    font-weight:700!important;
}

@media(max-width:1000px){
    .fh-home .fh-guide-grid,
    .fh-home .fh-guide-grid.fh-guide-count-2{
        grid-template-columns:repeat(2,minmax(0,1fr))!important;
    }
    .fh-home .fh-guide-grid.fh-guide-count-1 .fh-guide-card{
        grid-template-columns:1fr!important;
    }
    .fh-home .fh-guide-grid.fh-guide-count-1 .fh-guide-image{
        min-height:260px!important;
    }
}
@media(max-width:680px){
    .fh-home .fh-guides-reference{padding:42px 0 46px!important}
    .fh-home .fh-guides-reference>.container{width:calc(100% - 24px)!important}
    .fh-home .fh-guides-head{align-items:flex-start!important;flex-direction:column!important;gap:14px!important;margin-bottom:22px!important}
    .fh-home .fh-guide-grid,
    .fh-home .fh-guide-grid.fh-guide-count-2{grid-template-columns:1fr!important}
    .fh-home .fh-guide-image,
    .fh-home .fh-guide-grid.fh-guide-count-1 .fh-guide-image{min-height:220px!important}
    .fh-home .fh-guide-grid.fh-guide-count-1 .fh-guide-body{padding:23px 21px!important}
    .fh-home .fh-guide-grid.fh-guide-count-1 .fh-guide-body h3{font-size:25px!important}
}
</style>


<style id="fh-home-media-loading-polish-v1">
/* =========================================================
   HOMEPAGE DEAL MEDIA + LIVE FARE LOADING POLISH
   Visual-only. Existing tnbSearchDeal(), CMS cards and
   /ajax/route-fare endpoint remain unchanged.
   ========================================================= */

/* Neutral branded placeholder while an image is being decoded. */
.fh-home .fh-lazy-bg{
    position:relative;
    background:
        linear-gradient(110deg,#edf2f7 8%,#f8fafc 18%,#edf2f7 33%);
    background-size:220% 100%;
    background-position:100% 0;
    background-repeat:no-repeat;
    animation:fhMediaShimmer 1.35s linear infinite;
}

/* Once loaded, stop shimmer and show the actual CMS/fallback artwork. */
.fh-home .fh-lazy-bg.fh-bg-ready{
    animation:none;
    background-size:cover!important;
    background-position:center center!important;
    background-repeat:no-repeat!important;
}

/* If an image cannot load, use a clean branded fallback instead of a black/grey void. */
.fh-home .fh-lazy-bg.fh-bg-error{
    animation:none;
    background:
        radial-gradient(circle at 82% 22%,rgba(255,255,255,.10),transparent 28%),
        linear-gradient(135deg,#0b203b 0%,#173a62 55%,#0a1b32 100%)!important;
}

/* Keep overlays readable during the very short preload state. */
.fh-home .fh-lazy-bg:not(.fh-bg-ready):not(.fh-bg-error) .fd-route-overlay,
.fh-home .fh-lazy-bg:not(.fh-bg-ready):not(.fh-bg-error) .fd-badge,
.fh-home .fh-lazy-bg:not(.fh-bg-ready):not(.fh-bg-error) .fd-heart,
.fh-home .fh-lazy-bg:not(.fh-bg-ready):not(.fh-bg-error) .fh-guide-badge,
.fh-home .fh-lazy-bg:not(.fh-bg-ready):not(.fh-bg-error) .fh-guide-read{
    opacity:.78;
}

/* Fare placeholder: compact shimmer instead of raw "Loading..." copy. */
.fh-home .fh-fare-loading{
    display:inline-block;
    width:92px;
    max-width:100%;
    height:16px;
    border-radius:999px;
    vertical-align:middle;
    background:linear-gradient(90deg,#e8edf4 20%,#f7f9fc 50%,#e8edf4 80%);
    background-size:220% 100%;
    animation:fhFareShimmer 1.2s linear infinite;
}

/* On dark premium cards use a subtle gold-tinted skeleton. */
.fh-home .fd-premium-section .fh-fare-loading{
    background:linear-gradient(90deg,rgba(231,174,53,.18) 20%,rgba(255,225,153,.48) 50%,rgba(231,174,53,.18) 80%);
    background-size:220% 100%;
}

.fh-home .fh-live-fare.fh-fare-is-loading{
    min-width:92px;
}

/* Smooth arrival of fare text. */
.fh-home .fh-live-fare{
    transition:opacity .18s ease,color .18s ease;
}

@keyframes fhMediaShimmer{
    from{background-position:100% 0}
    to{background-position:-120% 0}
}
@keyframes fhFareShimmer{
    from{background-position:100% 0}
    to{background-position:-120% 0}
}

@media (prefers-reduced-motion:reduce){
    .fh-home .fh-lazy-bg,
    .fh-home .fh-fare-loading{
        animation:none!important;
    }
}
</style>

<script id="fh-home-lazy-backgrounds-v1">
(function(){
    const nodes=[...document.querySelectorAll('.fh-lazy-bg[data-fh-bg]')];
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
                fallback.onerror=function(){
                    el.classList.remove('fh-bg-ready');
                    el.classList.add('fh-bg-error');
                };
                fallback.src=FALLBACK;
            }else{
                el.classList.remove('fh-bg-ready');
                el.classList.add('fh-bg-error');
            }
        };

        img.src=src;
    }

    if('IntersectionObserver' in window){
        const io=new IntersectionObserver(entries=>{
            entries.forEach(entry=>{
                if(entry.isIntersecting){
                    resolve(entry.target);
                    io.unobserve(entry.target);
                }
            });
        },{rootMargin:'500px 0px'});
        nodes.forEach(n=>io.observe(n));
    }else{
        nodes.forEach(resolve);
    }
})();
</script>



<style id="fh-deal-trip-fare-label-v1">
.fh-home .fh-live-fare[data-trip-label]::after{
    content:" · " attr(data-trip-label);
    font-size:.55em;
    font-weight:600;
    opacity:.72;
    white-space:nowrap;
}
.fh-home .fd-premium-section .fh-live-fare[data-trip-label]::after{
    color:rgba(255,255,255,.76);
}
</style>


<style id="fh-manual-live-fare-mode-v1">
.fh-home .fh-live-fare[data-fare-mode="manual"]::before{content:"Starting from ";font-size:.55em;font-weight:600;opacity:.72}
.fh-home .fh-live-fare[data-fare-mode="live"]::before{content:"Live ";font-size:.55em;font-weight:700;opacity:.72}
</style>

<style id="fh-domestic-fare-label-cleanup-v1">
/* Domestic deal cards already receive Starting from / Live from fare-mode CSS.
   Hide the legacy standalone `from` label so it cannot render outside the fare line. */
.fh-home .fh-deals-shot .fd-domestic .fd-mini-fare{
    display:flex!important;
    align-items:baseline!important;
    justify-content:flex-start!important;
    gap:0!important;
    min-width:0!important;
    margin-top:14px!important;
    overflow:hidden!important;
}
.fh-home .fh-deals-shot .fd-domestic .fd-mini-fare>small{
    display:none!important;
}
.fh-home .fh-deals-shot .fd-domestic .fd-mini-fare .fh-live-fare{
    display:inline-block!important;
    min-width:0!important;
    max-width:100%!important;
    margin:0!important;
    line-height:1.2!important;
    white-space:nowrap!important;
}
</style>



<style id="fh-all-deals-fare-hierarchy-v2">
/* =========================================================
   ALL HOMEPAGE DEALS — unified premium fare hierarchy
   Presentation only. Existing CMS/manual-live fare/search
   and trip-type logic remains untouched.
   ========================================================= */

/* Legacy "from" labels are replaced by the fare-mode label below. */
.fh-home .fh-deals-shot .fd-live-label > small,
.fh-home .fh-deals-shot .fd-mini-fare > small,
.fh-home .fh-deals-shot .fd-holiday-price > small,
.fh-home .fh-deals-shot .fd-last-price > small,
.fh-home .fh-deals-shot .fd-premium-section .fd-live-label > small{
    display:none!important;
}

/* One consistent fare treatment across Featured, Domestic,
   International, Premium, Holiday and Last Minute cards. */
.fh-home .fh-deals-shot .fh-live-fare{
    display:inline-flex!important;
    align-items:center!important;
    justify-content:flex-end!important;
    flex-wrap:wrap!important;
    gap:5px!important;
    min-width:0!important;
    max-width:100%!important;
    margin:0!important;
    color:#e31b46!important;
    font-size:17px!important;
    line-height:1.15!important;
    font-weight:600!important;
    letter-spacing:-.02em!important;
    white-space:normal!important;
}

/* Manual / live label — quiet supporting text, not competing with price. */
.fh-home .fh-deals-shot .fh-live-fare[data-fare-mode="manual"]::before,
.fh-home .fh-deals-shot .fh-live-fare[data-fare-mode="live"]::before{
    margin:0 1px 0 0!important;
    color:#7b899d!important;
    font-size:9px!important;
    line-height:1!important;
    font-weight:500!important;
    letter-spacing:0!important;
    opacity:1!important;
    white-space:nowrap!important;
}
.fh-home .fh-deals-shot .fh-live-fare[data-fare-mode="manual"]::before{
    content:"Starting from"!important;
}
.fh-home .fh-deals-shot .fh-live-fare[data-fare-mode="live"]::before{
    content:"Live fare"!important;
}

/* Trip type becomes a real pill instead of inline dot-text. */
.fh-home .fh-deals-shot .fh-live-fare[data-trip-label]::after{
    content:attr(data-trip-label)!important;
    display:inline-flex!important;
    align-items:center!important;
    min-height:20px!important;
    margin-left:2px!important;
    padding:0 7px!important;
    border:1px solid #ffd2dc!important;
    border-radius:999px!important;
    background:#fff3f6!important;
    color:#c9183d!important;
    font-size:8px!important;
    line-height:1!important;
    font-weight:700!important;
    letter-spacing:.02em!important;
    opacity:1!important;
    white-space:nowrap!important;
}

/* Domestic cards: fare is the visual anchor. */
.fh-home .fh-deals-shot .fd-domestic .fd-mini-route{
    align-items:center!important;
}
.fh-home .fh-deals-shot .fd-domestic .fd-mini-airport b{
    color:#0a2140!important;
    font-weight:650!important;
}
.fh-home .fh-deals-shot .fd-domestic .fd-mini-airport small{
    margin-top:6px!important;
    color:#7c8ba2!important;
    font-size:9px!important;
    line-height:1.25!important;
}
.fh-home .fh-deals-shot .fd-domestic .fd-mini-fare{
    margin-top:16px!important;
    overflow:visible!important;
}
.fh-home .fh-deals-shot .fd-domestic .fd-mini-fare .fh-live-fare{
    justify-content:flex-start!important;
    font-size:22px!important;
    font-weight:600!important;
}
.fh-home .fh-deals-shot .fd-domestic{
    transition:transform .18s ease,border-color .18s ease,box-shadow .18s ease!important;
}
.fh-home .fh-deals-shot .fd-domestic:hover{
    transform:translateY(-2px)!important;
    border-color:#ccd8e7!important;
    box-shadow:0 12px 28px rgba(15,43,82,.07)!important;
}

/* Featured cards */
.fh-home .fh-deals-shot .fd-featured-body .fd-live-label{
    min-width:150px!important;
}
.fh-home .fh-deals-shot .fd-featured-body .fh-live-fare{
    font-size:18px!important;
}

/* International cards */
.fh-home .fh-deals-shot .fd-simple-sub{
    gap:10px!important;
}
.fh-home .fh-deals-shot .fd-simple-sub .fh-live-fare{
    font-size:16px!important;
}

/* Premium cabin cards keep the gold visual language. */
.fh-home .fh-deals-shot .fd-premium-section .fh-live-fare{
    color:#f0b43e!important;
    font-size:18px!important;
    line-height:1.1!important;
}

.fh-home .fh-deals-shot .fd-premium-section .fh-live-fare.fh-fare-missing{
    font-size:14px!important;
    line-height:1.1!important;
    font-weight:600!important;
}
.fh-home .fh-deals-shot .fd-premium-section .fh-live-fare[data-fare-mode]::before{
    color:rgba(255,255,255,.68)!important;
}
.fh-home .fh-deals-shot .fd-premium-section .fh-live-fare[data-trip-label]::after{
    border-color:rgba(255,255,255,.16)!important;
    background:rgba(255,255,255,.10)!important;
    color:#fff!important;
}

/* Holiday and last-minute cards */
.fh-home .fh-deals-shot .fd-holiday-price .fh-live-fare{
    font-size:18px!important;
}
.fh-home .fh-deals-shot .fd-last-price .fh-live-fare{
    color:#132640!important;
    font-size:17px!important;
}
.fh-home .fh-deals-shot .fd-last-price .fh-live-fare[data-trip-label]::after{
    border-color:#dce5ef!important;
    background:#f5f8fc!important;
    color:#54667e!important;
}
.fh-home .fh-deals-shot .fd-last-live strong{
    color:#173a68!important;
    font-weight:600!important;
}

/* Keep cards usable on narrower desktop/tablet widths without clipping fare pills. */
@media(max-width:1200px){
    .fh-home .fh-deals-shot .fh-live-fare{
        font-size:16px!important;
    }
    .fh-home .fh-deals-shot .fd-domestic .fd-mini-fare .fh-live-fare{
        font-size:20px!important;
    }
}
</style>



<style id="fh-all-deals-final-card-polish-v3">
/* =========================================================
   FINAL DEAL CARD POLISH
   Visual hierarchy only. CMS/manual-live fare, API search,
   trip type and card click behavior are unchanged.
   ========================================================= */

/* Across all deal types: label gets its own first line,
   value + trip pill live on the second line. */
.fh-home .fh-deals-shot .fh-live-fare{
    width:100%!important;
    max-width:100%!important;
    overflow:visible!important;
    box-sizing:border-box!important;
    justify-content:flex-start!important;
    align-content:flex-start!important;
    row-gap:6px!important;
    column-gap:7px!important;
}
.fh-home .fh-deals-shot .fh-live-fare[data-fare-mode]::before{
    flex:0 0 auto!important;
    width:auto!important;
    margin:0 5px 0 0!important;
    display:inline-block!important;
    color:#7e8ca0!important;
    font-size:9px!important;
    line-height:1!important;
    font-weight:500!important;
    letter-spacing:0!important;
}
.fh-home .fh-deals-shot .fh-live-fare[data-trip-label]::after{
    flex:0 0 auto!important;
    max-width:100%!important;
    margin:0 0 0 10px!important;
}

/* "Check fare" is an action/fallback, not a fake price. */
.fh-home .fh-deals-shot .fh-live-fare.fh-fare-missing{
    font-size:14px!important;
    line-height:1.1!important;
    font-weight:600!important;
    letter-spacing:0!important;
    color:#c91b3f!important;
}
.fh-home .fh-deals-shot .fh-live-fare.fh-fare-missing[data-fare-mode="manual"]::before{
    content:"Fare"!important;
}
.fh-home .fh-deals-shot .fh-live-fare.fh-fare-missing[data-fare-mode="live"]::before{
    content:"Live fare"!important;
}

/* Domestic card: compact modern OTA layout. */
.fh-home .fh-deals-shot .fd-domestic{
    min-height:148px!important;
    padding:18px 18px 16px!important;
    overflow:hidden!important;
    display:flex!important;
    flex-direction:column!important;
}
.fh-home .fh-deals-shot .fd-domestic .fd-mini-route{
    display:grid!important;
    grid-template-columns:minmax(0,1fr) 26px minmax(0,1fr)!important;
    align-items:center!important;
    width:100%!important;
    margin:0!important;
}
.fh-home .fh-deals-shot .fd-domestic .fd-mini-airport{
    min-width:0!important;
}
.fh-home .fh-deals-shot .fd-domestic .fd-mini-airport:last-child{
    text-align:right!important;
}
.fh-home .fh-deals-shot .fd-domestic .fd-mini-airport b{
    display:block!important;
    margin:0!important;
    color:#071d3b!important;
    font-size:16px!important;
    line-height:1!important;
    font-weight:650!important;
}
.fh-home .fh-deals-shot .fd-domestic .fd-mini-arrow{
    justify-self:center!important;
    color:#e51d46!important;
    font-size:11px!important;
}
.fh-home .fh-deals-shot .fd-mini-route-copy{
    width:100%!important;
    min-height:17px!important;
    margin:8px 0 0!important;
    overflow:hidden!important;
    color:#74859f!important;
    font-size:9px!important;
    line-height:1.25!important;
    font-weight:400!important;
    text-align:center!important;
    text-overflow:ellipsis!important;
    white-space:nowrap!important;
}
.fh-home .fh-deals-shot .fd-domestic .fd-mini-fare{
    width:100%!important;
    min-width:0!important;
    margin-top:auto!important;
    padding-top:14px!important;
    overflow:visible!important;
}
.fh-home .fh-deals-shot .fd-domestic .fd-mini-fare .fh-live-fare{
    width:100%!important;
    font-size:22px!important;
    line-height:1!important;
    font-weight:600!important;
}
.fh-home .fh-deals-shot .fd-domestic .fd-mini-fare .fh-live-fare.fh-fare-missing{
    font-size:14px!important;
    line-height:1.1!important;
    font-weight:600!important;
}
.fh-home .fh-deals-shot .fd-domestic .fh-live-fare[data-trip-label]::after{
    min-height:19px!important;
    padding:0 7px!important;
    font-size:8px!important;
}

/* Ensure fare rows on every other deal card have enough width for pill,
   while still wrapping safely on tighter cards. */
.fh-home .fh-deals-shot .fd-live-label,
.fh-home .fh-deals-shot .fd-simple-sub,
.fh-home .fh-deals-shot .fd-holiday-price,
.fh-home .fh-deals-shot .fd-last-price{
    min-width:0!important;
    overflow:visible!important;
}
.fh-home .fh-deals-shot .fd-live-label .fh-live-fare,
.fh-home .fh-deals-shot .fd-simple-sub .fh-live-fare,
.fh-home .fh-deals-shot .fd-holiday-price .fh-live-fare,
.fh-home .fh-deals-shot .fd-last-price .fh-live-fare{
    min-width:120px!important;
}

/* Slightly quieter hover; whole card remains the existing clickable search CTA. */
.fh-home .fh-deals-shot .fd-card{
    transition:transform .17s ease,border-color .17s ease,box-shadow .17s ease!important;
}
.fh-home .fh-deals-shot .fd-card:hover{
    transform:translateY(-2px)!important;
    border-color:#cbd7e6!important;
    box-shadow:0 12px 28px rgba(13,40,79,.07)!important;
}

@media(max-width:1200px){
    .fh-home .fh-deals-shot .fd-domestic{
        min-height:144px!important;
    }
}
/* Fare label sits above the value, not in the card corner */
.fh-home .fh-deals-shot .fd-live-label,
.fh-home .fh-deals-shot .fd-mini-fare,
.fh-home .fh-deals-shot .fd-holiday-price,
.fh-home .fh-deals-shot .fd-last-price{
    text-align:left!important;
}
</style>
<script>
(function(){
    function init(){
        var sel=document.getElementById('cabin_classes');
        var wrap=sel&&sel.closest('.fh-ref-cabin');
        var trigger=document.getElementById('fh_cabin_trigger');
        var menu=document.getElementById('fh_cabin_menu');
        var label=document.getElementById('fh_cabin_label');
        var sub=document.getElementById('fh_cabin_sub');
        if(!sel||!wrap||!trigger||!menu) return;

        Array.prototype.forEach.call(sel.options,function(opt){
            var b=document.createElement('button');
            b.type='button';
            b.className='fh-cabin-option';
            b.setAttribute('role','option');
            b.dataset.value=opt.value;
            b.innerHTML='<b></b><em></em>';
            b.querySelector('b').textContent=opt.textContent;
            b.querySelector('em').textContent=opt.dataset.sub||'';
            b.addEventListener('click',function(){
                sel.value=opt.value;
                sel.dispatchEvent(new Event('change',{bubbles:true}));
                close();
            });
            menu.appendChild(b);
        });

        function sync(){
            var opt=sel.options[sel.selectedIndex];
            if(!opt) return;
            if(label) label.textContent=opt.textContent;
            if(sub) sub.textContent=opt.dataset.sub||'';
            Array.prototype.forEach.call(menu.children,function(el){
                el.classList.toggle('is-active',el.dataset.value===sel.value);
            });
        }
        function open(){wrap.classList.add('is-open');trigger.setAttribute('aria-expanded','true');}
        function close(){wrap.classList.remove('is-open');trigger.setAttribute('aria-expanded','false');}

        trigger.addEventListener('click',function(e){
            e.stopPropagation();
            wrap.classList.contains('is-open')?close():open();
        });
        menu.addEventListener('click',function(e){e.stopPropagation();});
        document.addEventListener('click',close);
        document.addEventListener('keydown',function(e){if(e.key==='Escape')close();});
        sel.addEventListener('change',sync);
        sync();
    }
    document.readyState==='loading'
        ? document.addEventListener('DOMContentLoaded',init)
        : init();
})();
</script>


<style id="fh-home-lastminute-guides-targeted-v1">
/* Targeted final polish: Last Minute Deals + Travel Guides only. */
.fh-home .fh-deals-shot .fd-grid.last{gap:16px!important}
.fh-home .fh-deals-shot .fd-last-card{
    display:flex!important;flex-direction:column!important;min-height:174px!important;
    padding:16px!important;border:1px solid #dfe6ef!important;border-radius:18px!important;
    background:linear-gradient(180deg,#fff 0%,#fbfcfe 100%)!important;
    box-shadow:0 7px 20px rgba(20,45,80,.045)!important
}
.fh-home .fh-deals-shot .fd-last-top{align-items:center!important}
.fh-home .fh-deals-shot .fd-last-label{
    display:inline-flex!important;align-items:center!important;gap:5px!important;
    padding:5px 8px!important;font-size:8px!important;letter-spacing:.035em!important
}
.fh-home .fh-deals-shot .fd-last-urgency{color:#8a98aa!important;font-size:8.5px!important;font-weight:500!important}
.fh-home .fh-deals-shot .fd-last-route{
    margin-top:17px!important;color:#0a2140!important;font-size:16px!important;
    line-height:1!important;font-weight:750!important;letter-spacing:.01em!important
}
.fh-home .fh-deals-shot .fd-last-route i{margin:0 9px!important}
.fh-home .fh-deals-shot .fd-last-meta{
    justify-content:flex-start!important;gap:14px!important;margin-top:11px!important;
    color:#7a899e!important;font-size:9px!important
}
.fh-home .fh-deals-shot .fd-last-meta span{display:inline-flex!important;align-items:center!important;gap:5px!important}
.fh-home .fh-deals-shot .fd-last-meta i{color:#9aa8b9!important;font-size:8px!important}
.fh-home .fh-deals-shot .fd-last-fare-row{
    display:grid!important;grid-template-columns:minmax(0,1fr) auto!important;align-items:end!important;
    gap:12px!important;margin-top:auto!important;padding-top:14px!important;border-top:1px solid #e8edf4!important
}
.fh-home .fh-deals-shot .fd-last-fare-copy{min-width:0!important;text-align:left!important}
.fh-home .fh-deals-shot .fd-last-fare-copy>small{
    display:block!important;margin-bottom:5px!important;color:#8a98aa!important;font-size:8px!important;
    line-height:1!important;font-weight:600!important;text-transform:uppercase!important;letter-spacing:.045em!important
}
.fh-home .fh-deals-shot .fd-last-price{display:block!important;min-width:0!important;text-align:left!important}
.fh-home .fh-deals-shot .fd-last-price .fh-live-fare{
    justify-content:flex-start!important;min-width:0!important;color:#d71940!important;
    font-size:19px!important;line-height:1!important;font-weight:650!important
}
.fh-home .fh-deals-shot .fd-last-price .fh-live-fare.fh-fare-missing{font-size:13px!important}
.fh-home .fh-deals-shot .fd-last-view{
    display:inline-flex!important;align-items:center!important;gap:6px!important;
    color:#153a68!important;font-size:10px!important;font-weight:700!important;white-space:nowrap!important
}
.fh-home .fh-guides-reference .fh-guide-card{border-color:#dfe6ef!important}
.fh-home .fh-guide-grid.fh-guide-count-1 .fh-guide-card{min-height:286px!important}
.fh-home .fh-guide-grid.fh-guide-count-1 .fh-guide-image{min-height:286px!important}
.fh-home .fh-guide-grid.fh-guide-count-1 .fh-guide-body{padding:30px 34px!important}
.fh-home .fh-guide-body h3{max-width:760px!important}
.fh-home .fh-guide-body p{max-width:760px!important}
.fh-home .fh-guide-footer i{transition:transform .18s ease!important}
.fh-home .fh-guide-card:hover .fh-guide-footer i{transform:translateX(3px)!important}




/* ---------- Cabin dropdown ---------- */
.fh-home .fh-ref-cabin .fh-cabin-menu{
    padding:6px!important;
    border:1px solid #dbe4ef!important;
    border-radius:18px!important;
    background:#fff!important;
    box-shadow:0 22px 55px rgba(11,37,70,.16)!important;
    transform-origin:top center!important;
}
.fh-home .fh-ref-cabin.is-open .fh-cabin-menu{
    animation:fhCabinIn .16s cubic-bezier(.22,.68,.36,1)!important;
}
@keyframes fhCabinIn{
    from{opacity:0;transform:translateY(-5px) scale(.985)}
    to{opacity:1;transform:none}
}
@media(prefers-reduced-motion:reduce){
    .fh-home .fh-ref-cabin.is-open .fh-cabin-menu{animation:none!important}
    .fh-home .fh-ref-cabin .fh-cabin-trigger>i{transition:none!important}
}

.fh-home .fh-ref-cabin .fh-cabin-option{
    display:flex!important;
    align-items:center!important;
    justify-content:space-between!important;
    gap:12px!important;
    padding:10px 12px!important;
    border-radius:13px!important;
    transition:background .15s ease,color .15s ease!important;
}
.fh-home .fh-ref-cabin .fh-cabin-option+.fh-cabin-option{
    margin-top:2px!important;
}
.fh-home .fh-ref-cabin .fh-cabin-option-text{
    display:block!important;
    min-width:0!important;
}
.fh-home .fh-ref-cabin .fh-cabin-option b{
    display:block!important;
    color:#0d1f36!important;
    font-size:14px!important;
    font-weight:600!important;
    line-height:1.2!important;
}
.fh-home .fh-ref-cabin .fh-cabin-option em{
    display:block!important;
    margin-top:2px!important;
    color:#8795aa!important;
    font-size:11.5px!important;
    font-style:normal!important;
    line-height:1.25!important;
}
.fh-home .fh-ref-cabin .fh-cabin-option>i{
    flex:0 0 auto!important;
    opacity:0!important;
    font-size:12px!important;
    transition:opacity .15s ease!important;
}

.fh-home .fh-ref-cabin .fh-cabin-option:hover{
    background:#f2f6fb!important;
}
.fh-home .fh-ref-cabin .fh-cabin-option.is-active{
    background:linear-gradient(135deg,#0d3c6d 0%,#185b94 100%)!important;
    box-shadow:0 8px 20px rgba(13,60,109,.20)!important;
}
.fh-home .fh-ref-cabin .fh-cabin-option.is-active b{color:#fff!important}
.fh-home .fh-ref-cabin .fh-cabin-option.is-active em{color:#c3d8ef!important}
.fh-home .fh-ref-cabin .fh-cabin-option.is-active>i{
    opacity:1!important;
    color:#fff!important;
}
.fh-home .fh-ref-cabin .fh-cabin-option:focus-visible{
    outline:2px solid #185b94!important;
    outline-offset:1px!important;
}

</style>

<style id="fh-domestic-hide-extra-fare-label-v1">
/* Domestic only: STARTING FROM already provides the fare context. */
.fh-home .fh-deals-shot .fd-domestic .fh-live-fare[data-fare-mode]::before{
    display:none!important;
    content:none!important;
}
</style>

<style id="fh-domestic-fare-inline-v1">
.fh-home .fh-deals-shot .fd-domestic-price .fh-live-fare{
    display:inline-flex!important;
    flex-wrap:nowrap!important;
    align-items:center!important;
    justify-content:flex-start!important;
    gap:10px!important;
    width:auto!important;
    max-width:100%!important;
    white-space:nowrap!important;
}
.fh-home .fh-deals-shot .fd-domestic-price .fh-live-fare[data-trip-label]::after{
    margin:0px!important;
    flex:0 0 auto!important;
}
</style>


<script id="fh-search-loading-script-v1">
(function(){
    const form = document.getElementById('form2');
    const loader = document.getElementById('fhSearchLoading');
    if (!form || !loader) return;

    function showSearchLoader(){
        loader.hidden = false;
        document.body.setAttribute('aria-busy','true');

        const btn = form.querySelector('.fh-search-submit');
        if (btn){
            btn.disabled = true;
            const label = btn.querySelector('span');
            if (label) label.textContent = 'Searching...';
        }
    }

    form.addEventListener('submit', function(e){
        const trip = form.querySelector('input[name="flight_types"]:checked');
        const isMulti = trip && trip.value === 'multi_city';

        const required = isMulti
            ? [...document.querySelectorAll('#fhMultiCitySearch input[required]')]
            : [...document.querySelectorAll('#fhStandardSearch input[required]')];

        const missing = required.find(function(input){
            return !String(input.value || '').trim();
        });

        if (missing){
            e.preventDefault();
            missing.focus();
            return;
        }

        showSearchLoader();
    });

    window.fhShowSearchLoader = showSearchLoader;

    /* Back button (bfcache): page wapas aaye to Search button phir se chalu ho. */
    const fhSubmitBtn = form.querySelector('.fh-search-submit');
    const fhSubmitLabel = fhSubmitBtn && fhSubmitBtn.querySelector('span') ? fhSubmitBtn.querySelector('span').textContent : '';
    window.addEventListener('pageshow', function(){
        loader.hidden = true;
        document.body.removeAttribute('aria-busy');
        if (fhSubmitBtn){
            fhSubmitBtn.disabled = false;
            const label = fhSubmitBtn.querySelector('span');
            if (label && fhSubmitLabel) label.textContent = fhSubmitLabel;
        }
    });

    /* Search validation - layout.blade.php ka submit handler isse pehle call karta hai. */
    function fhCode(input){
        if (input && /^[A-Za-z]{3}$/.test(String(input.dataset.airportCode || ''))) {
            return String(input.dataset.airportCode).toUpperCase();
        }
        const value = String(input && input.value || '').trim();
        const match = value.match(/^([A-Za-z]{3})(?:$|\s*[-–—(])/);
        return match ? match[1].toUpperCase() : '';
    }
    function fhLooksLikeAirport(input){
        // Autocomplete selections carry the canonical IATA code. Deal cards set the IATA code directly.
        return !!fhCode(input);
    }
    function fhDate(v){
        const value = String(v || '').trim();
        let year, month, day, m;
        // Flatpickr desktop format is m/d/Y; native mobile date inputs use Y-m-d.
        if ((m = value.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/))) {
            month = Number(m[1]); day = Number(m[2]); year = Number(m[3]);
        } else if ((m = value.match(/^(\d{4})-(\d{2})-(\d{2})$/))) {
            year = Number(m[1]); month = Number(m[2]); day = Number(m[3]);
        } else {
            return null;
        }
        const date = new Date(year, month - 1, day);
        // Reject impossible dates such as 02/31/2026 rather than normalizing them.
        return date.getFullYear() === year && date.getMonth() === month - 1 && date.getDate() === day ? date : null;
    }
    function fhVisible(input){
        const fp = input && input._flatpickr;
        return fp ? (fp.mobileInput || fp.altInput || input) : input;
    }

    window.fhValidateSearch = function(f){
        const box = document.getElementById('fhSearchError');
        if (box){ box.style.display = 'none'; box.textContent = ''; }

        function fail(msg, input){
            if (box){ box.textContent = msg; box.style.display = 'block'; }
            const el = fhVisible(input);
            if (el && typeof el.focus === 'function'){ try { el.focus(); } catch(err){} }
            return false;
        }

        function checkPair(fromEl, toEl, prefix){
            if (!fromEl || !String(fromEl.value || '').trim()) return fail(prefix + 'Please enter the departure airport.', fromEl);
            if (!toEl || !String(toEl.value || '').trim()) return fail(prefix + 'Please enter the arrival airport.', toEl);
            if (!fhLooksLikeAirport(fromEl)) return fail(prefix + 'Please select the departure airport from the suggestions.', fromEl);
            if (!fhLooksLikeAirport(toEl)) return fail(prefix + 'Please select the arrival airport from the suggestions.', toEl);
            if (fhCode(fromEl) === fhCode(toEl)) return fail(prefix + 'Departure and arrival airports cannot be the same.', toEl);
            return true;
        }

        const tripEl = f.querySelector('input[name="flight_types"]:checked');
        const trip = tripEl ? tripEl.value : 'round_trip';

        if (trip === 'multi_city'){
            const legs = [...f.querySelectorAll('#fhMultiCityLegs [data-multi-leg]')];
            if (legs.length < 2) return fail('Please add at least 2 flights for a Multi-City trip.');
            let prev = null;
            for (let i = 0; i < legs.length; i++){
                const prefix = 'Flight ' + (i + 1) + ': ';
                if (!checkPair(legs[i].querySelector('[name="multi_from[]"]'), legs[i].querySelector('[name="multi_to[]"]'), prefix)) return false;
                const dEl = legs[i].querySelector('[name="multi_date[]"]');
                const d = fhDate(dEl && dEl.value);
                if (!d) return fail(prefix + 'Please select a travel date.', dEl);
                if (prev && d < prev) return fail(prefix + 'Date cannot be before Flight ' + i + '.', dEl);
                prev = d;
            }
            return true;
        }

        if (!checkPair(document.getElementById('location_from'), document.getElementById('location_to'), '')) return false;

        const depEl = document.getElementById('depature_date');
        const depDate = fhDate(depEl && depEl.value);
        if (!depDate) return fail('Please select a departure date.', depEl);

        if (trip === 'round_trip'){
            const retEl = document.getElementById('return_date');
            const retDate = fhDate(retEl && retEl.value);
            if (!retDate) return fail('Please select a return date.', retEl);
            if (retDate < depDate) return fail('Return date cannot be before the departure date.', retEl);
        }
        return true;
    };

    /* Trip state: One Way / Multi-City par dikhne wala Return field bhi khaali + disabled ho.
       tnbSetTrip() (tnb-premium-v3.js) sirf hidden input ko disable karta tha, Flatpickr ka visible field nahi. */
    document.addEventListener('DOMContentLoaded', function(){
        const baseSetTrip = window.tnbSetTrip;
        if (typeof baseSetTrip !== 'function') return;
        window.tnbSetTrip = function(v){
            baseSetTrip(v);
            const retEl = document.getElementById('return_date');
            const fp = retEl && retEl._flatpickr;
            if (!fp) return;
            const off = v !== 'round_trip';
            if (off) fp.clear();
            [fp.altInput, fp.mobileInput].forEach(function(el){
                if (!el) return;
                el.disabled = off;
                el.required = !off;
            });
            const box = document.getElementById('fhSearchError');
            if (box){ box.style.display = 'none'; box.textContent = ''; }
        };
        /* Back button par fields ko selected trip ke hisaab se dobara sync karo (deal card click ke baad wapas aane par). */
        window.addEventListener('pageshow', function(e){
            if (!e.persisted) return;
            const t = form.querySelector('input[name="flight_types"]:checked');
            if (t) window.tnbSetTrip(t.value);
        });
    });
})();
</script>
<style id="fh-trip-state-fix">
.fh-home .fh-ref-dates .fh-return-field input:disabled{opacity:.45!important;cursor:not-allowed!important}
</style>

<style id="fh-search-loading-style-v1">
.fh-search-loading[hidden]{display:none!important}
.fh-search-loading{
    position:fixed;
    inset:0;
    z-index:99999;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:20px;
    background:rgba(5,24,43,.48);
    backdrop-filter:blur(3px);
    -webkit-backdrop-filter:blur(3px);
}
.fh-search-loading-card{
    width:min(92vw,420px);
    padding:28px 24px;
    border-radius:18px;
    background:#fff;
    box-shadow:0 24px 70px rgba(0,0,0,.22);
    text-align:center;
}
.fh-search-loading-spinner{
    width:42px;
    height:42px;
    margin:0 auto 16px;
    border:4px solid #dbe7f2;
    border-top-color:#185b94;
    border-radius:50%;
    animation:fhSearchSpin .7s linear infinite;
}
.fh-search-loading-card strong{
    display:block;
    margin-bottom:6px;
    font-size:18px;
    line-height:1.3;
    color:#0d3c6d;
}
.fh-search-loading-card span{
    display:block;
    font-size:14px;
    line-height:1.45;
    color:#667788;
}
@keyframes fhSearchSpin{to{transform:rotate(360deg)}}
@media (prefers-reduced-motion:reduce){
    .fh-search-loading-spinner{animation-duration:1.5s}
}
</style>

<style id="fh-contrast-v1">
/* fh-contrast-v1 (26 Oct 2026): text colours only (no size / spacing change) so the PageSpeed
   "colour contrast" items reach WCAG AA 4.5:1. Old colour -> new colour (ratio). */
html body #fhHeader .fh-reference-nav .fh-reference-nav-link > span{color:#5b6b82!important}          /* #6f7e9e 4.07 -> 5.43 */
html body #fhHeader .fh-reference-nav .fh-reference-nav-link > em{color:#4f5e78!important}            /* #6d7da0 3.73 -> 5.94 */
html body .fd-featured-body .fd-airline-price .fd-airline > small{color:#5b6b82!important}           /* #74839a 3.84 -> 5.43 */
html body button.fd-card .fd-featured-body .fd-meta > span{color:#5b6b82!important}                  /* #6f7d92 4.17 -> 5.43 */
html body button.fd-card .fd-simple-body .fd-simple-sub > small{color:#5b6b82!important}             /* #77859a 3.74 -> 5.43 */
html body .fd-premium-body .fd-airline-price .fd-live-label > strong.fh-live-fare{color:#ff8096!important} /* #c91b3f on navy 2.95 -> 6.99 */
html body button.fd-card .fd-holiday-body .fd-holiday-info > small{color:#5b6b82!important}         /* #7d8a9e 3.49 -> 5.43 */
html body .fd-grid button.fd-card .fd-last-top > span.fd-last-label{color:#b3102f!important}         /* #df1640 4.08 -> 5.82 */
html body .fh-airlines-reference .fh-section-head p,
html body .fh-guides-reference .fh-section-head p,
html body .fh-home .fh-guides-reference .fh-section-head.fh-guides-head > div > p{color:#52627e!important}                           /* #64748b/#66799d ~4.2 -> 5.6+ */
html body .fh-guide-grid .fh-guide-card .fh-guide-body .fh-guide-date{color:#8a6116!important}       /* #d18c08 2.81 -> 5.52 */
html body .fh-guide-grid .fh-guide-card .fh-guide-body > p{color:#52627e!important}                  /* #66799d 4.38 -> 6.16 */
</style>

<style id="fh-contrast-v2">
/* =========================================================
   FINAL ACCESSIBILITY CONTRAST FIXES (Lighthouse 100)
   Fixes: Dark cards need light text, Light cards need dark text.
   ========================================================= */

/* 1. Premium (Navy) Cards — LIGHT text for dark background */
html body .fh-deals-shot .fd-premium-card .fd-meta span,
html body .fh-deals-shot .fd-premium-card .fd-airline small,
html body .fh-deals-shot .fd-premium-card .fd-live-label small,
html body .fh-deals-shot .fd-premium-body .fd-meta,
html body .fh-deals-shot .fd-premium-body .fd-airline small {
    color: #cbd5e1 !important; /* Light slate for high contrast on navy */
}

/* Premium (Navy) "Check fare" text */
html body .fh-deals-shot .fd-premium-card .fh-live-fare {
    color: #fca5a5 !important; /* Light red/pink for contrast on navy */
}

/* 2. White Cards (Featured, Domestic, Simple, Last Minute) — DARK text for white background */
html body .fh-deals-shot .fd-featured-body .fd-meta span,
html body .fh-deals-shot .fd-featured-body .fd-live-label small,
html body .fh-deals-shot .fd-simple-sub small,
html body .fh-deals-shot .fd-domestic-meta,
html body .fh-deals-shot .fd-domestic-meta span,
html body .fh-deals-shot .fd-domestic-availability,
html body .fh-deals-shot .fd-domestic .fd-mini-airport small,
html body .fh-deals-shot .fd-holiday-info small,
html body .fh-deals-shot .fd-last-meta,
html body .fh-deals-shot .fd-last-urgency,
html body .fh-deals-shot .fd-last-price small,
html body .fh-deals-shot .fd-last-fare-copy > small {
    color: #475569 !important; /* Dark slate for high contrast on white */
}

/* 3. "LAST MINUTE" Pill */
html body .fh-deals-shot .fd-last-label {
    color: #be123c !important; /* Darker red text */
    background-color: #ffe4e6 !important;
}

/* 4. Header "Soon" Badges */
html body #fhHeader .fh-reference-nav .fh-reference-nav-link.disabled > em {
    color: #4f5e78 !important; /* Darker slate */
}
</style>

@endsection



