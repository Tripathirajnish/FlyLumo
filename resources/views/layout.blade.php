<!doctype html>
<html lang="en">

@php
  $setting = getAllSetting();
  $tnbTollFree = $setting['header_contact'] ?? '';
  $tnbWhatsApp = '+1 (307) 263-8435';
  $tnbWhatsAppDigits = preg_replace('/\D+/', '', (string) $tnbWhatsApp);
@endphp


@php
  $tnbCms = collect();
  if (\Illuminate\Support\Facades\Schema::hasTable('website_cms_settings')) {
      $tnbCms = \Illuminate\Support\Facades\DB::table('website_cms_settings')->pluck('value','key');
  }
  $cmsv = fn($key,$default='') => $tnbCms[$key] ?? $default;
@endphp

<head>
  <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-L52ZZL5MN0"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-L52ZZL5MN0');
  </script>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
@php
    // fh-critical-css (26 Oct 2026): homepage only. Each render-blocking stylesheet becomes
    // its inline above-the-fold subset (public/assets/css/critical/<name>.css) + the full file loaded async,
    // at the SAME position, so the CSS order (cascade) stays exactly as before.
    // If the critical file is missing, the normal blocking <link> is printed (old behaviour).
    $fhIsHome = request()->is('/');
    $fhCss = function ($href, $name) use ($fhIsHome) {
        $crit = $fhIsHome ? @file_get_contents(public_path('assets/css/critical/'.$name.'.css')) : false;
        if ($crit === false || trim($crit) === '') {
            return '<link href="'.$href.'" rel="stylesheet">';
        }
        return '<style data-fh-critical="'.$name.'">'.$crit.'</style>'
            .'<link rel="preload" as="style" href="'.$href.'" onload="this.onload=null;this.rel=\'stylesheet\'">'
            .'<noscript><link rel="stylesheet" href="'.$href.'"></noscript>';
    };
@endphp
@if($fhIsHome)
  {{-- fh-inter-selfhost: homepage uses a self-hosted Inter (same font, no Google Fonts connection). --}}
  <link rel="preload" href="/assets/webfonts/inter-latin-wght-normal.woff2" as="font" type="font/woff2" crossorigin>
  <style id="fh-inter-selfhost">@font-face{font-family:"Inter";font-style:normal;font-weight:100 900;font-display:optional;src:url(/assets/webfonts/inter-latin-wght-normal.woff2) format("woff2");unicode-range:U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+2074,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD}@font-face{font-family:"Inter";font-style:normal;font-weight:100 900;font-display:optional;src:url(/assets/webfonts/inter-latin-ext-wght-normal.woff2) format("woff2");unicode-range:U+0100-02AF,U+0304,U+0308,U+0329,U+1E00-1E9F,U+1EF2-1EFF,U+2020,U+20A0-20AB,U+20AD-20C0,U+2113,U+2C60-2C7F,U+A720-A7FF}</style>
@endif
  <link rel="icon" type="image/x-icon" href="/assets/img/favicon.png">

  @yield('header-seo')
  @yield('canonical')

  {{-- FaresHunt official business identity structured data --}}
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": ["Organization", "TravelAgency"],
    "@id": "https://fareshunt.com/#organization",
    "name": "FaresHunt",
    "legalName": "Aeronza Travel Group LLC",
    "url": "https://fareshunt.com/",
    "logo": "https://ik.imagekit.io/esch8qk6i/flight_W6BYBhk2-.png",
    "email": "info@fareshunt.com",
    "telephone": "+1-888-717-0346",
    "address": {
      "@type": "PostalAddress",
      "streetAddress": "1603 Capitol Ave 413g 2599",
      "addressLocality": "Cheyenne",
      "addressRegion": "WY",
      "postalCode": "82001",
      "addressCountry": "US"
    },
    "contactPoint": {
      "@type": "ContactPoint",
      "telephone": "+1-888-717-0346",
      "email": "info@fareshunt.com",
      "contactType": "customer support",
      "areaServed": "US",
      "availableLanguage": "English"
    }
  }
  </script>

  {!! $fhCss('/assets/css/bootstrap.min.css', 'bootstrap.min') !!}
  @unless(request()->is('/'))
  <link href="/assets/css/animation.css" rel="stylesheet">
  @endunless
@unless(request()->is('/') || request()->is('flight-listing/*'))
  <link href="/assets/css/dropzone.min.css" rel="stylesheet">
@endunless
  {!! $fhCss('/assets/css/flatpickr.min.css', 'flatpickr.min') !!}
@unless(request()->is('/'))
  <link href="/assets/css/flickity.min.css" rel="stylesheet">
@endunless
@unless(request()->is('/') || request()->is('flight-listing/*'))
  <link href="/assets/css/lightbox.min.css" rel="stylesheet">
@endunless
@unless(request()->is('/') || request()->is('flight-listing/*'))
  <link href="/assets/css/magnifypopup.css" rel="stylesheet">
@endunless
@unless(request()->is('/'))
  <link href="/assets/css/select2.min.css" rel="stylesheet">
@endunless
@unless(request()->is('/'))
  <link href="/assets/css/rangeSlider.min.css" rel="stylesheet">
@endunless
@unless(request()->is('/') || request()->is('flight-listing/*'))
  <link href="/assets/css/prism.css" rel="stylesheet">
@endunless

  @unless(request()->is('/'))
    <link href="/assets/css/bootstrap-icons.css" rel="stylesheet">
@endunless
  {!! $fhCss('/assets/css/fontawesome.css', 'fontawesome') !!}

  @unless($fhIsHome)
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=optional" as="style" onload="this.onload=null;this.rel='stylesheet'"><noscript><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=optional" rel="stylesheet"></noscript>
  @endunless

  @if(request()->is('/'))
  {!! $fhCss('/assets/css/style-home.css', 'style-home') !!}
  @else
  <link href="/assets/css/style.css" rel="stylesheet">
  @endif
  {!! $fhCss('/assets/css/travelnb-modern.css', 'travelnb-modern') !!}
  {!! $fhCss('/assets/css/tnb-premium-v3.css', 'tnb-premium-v3') !!}
  @unless(request()->is('/'))
  <link href="/assets/css/tnb-checkout-v4.css" rel="stylesheet">
  @endunless
  @unless(request()->is('/'))
  <link href="/assets/css/tnb-results-v4.css" rel="stylesheet">
  @endunless
  {!! $fhCss('/assets/css/fareshunt-inspired.css', 'fareshunt-inspired') !!}
  {!! $fhCss('/assets/css/tnb-worldclass-v3.css', 'tnb-worldclass-v3') !!}
  @unless(request()->is('/'))
  <link href="/assets/css/tnb-boardpass-loader-v5.css" rel="stylesheet">
  @endunless
  {!! $fhCss('/assets/css/tnb-header-premium-v6.css', 'tnb-header-premium-v6') !!}
  {!! $fhCss('/assets/css/fareshunt-phase1.css', 'fareshunt-phase1') !!}
  {!! $fhCss('/assets/css/fareshunt-top-reference-v1.css', 'fareshunt-top-reference-v1') !!}
  {!! $setting['header_script'] !!}

<style>
:root{
  --tnb-cms-primary: {{ $cmsv('theme.primary','#d91f43') }};
  --tnb-cms-navy: {{ $cmsv('theme.navy','#071b31') }};
  --tnb-cms-font: {!! $cmsv('theme.font','Inter, "Segoe UI", Arial, sans-serif') !!};
}

body{
  font-family:var(--tnb-cms-font)!important;
}

.tnb-utility-bar,
.footer.skin-light-footer{
  background:var(--tnb-cms-navy)!important;
}

.tnb-utility-call,
.wc-search-btn,
.wc3-search-btn{
  background:var(--tnb-cms-primary)!important;
}

/* Footer logo — same original asset, rendered pure white on the dark footer */
.fh-footer .fh-footer-brand > img{
  display:block !important;
  width:235px !important;
  max-width:100% !important;
  height:auto !important;
  object-fit:contain !important;

  /* No box/card behind the logo */
  background:transparent !important;
  padding:0 !important;
  border:0 !important;
  border-radius:0 !important;
  box-shadow:none !important;

  /* Convert the transparent colored logo artwork to white */
  filter:brightness(0) invert(1) !important;

  opacity:1 !important;
  margin:0 0 20px 0 !important;
}

@media (max-width:767px){
  .fh-footer .fh-footer-brand > img{
    width:215px !important;
  }
}

/* =========================================================
   SEARCH LOADER — REFERENCE BOARDING PASS V7
   Keeps existing JS hooks/data attributes intact.
   ========================================================= */
.tnb-search-overlay.tnb-ref-loader{
  background:#122540 !important;
  color:#fff !important;
}
.tnb-ref-stage{
  width:min(980px,92vw) !important;
  max-width:980px !important;
  margin:0 auto !important;
  padding:38px 0 28px !important;
  text-align:center !important;
}
.tnb-ref-stage > h2{
  margin:0 0 8px !important;
  color:#fff !important;
  font-family:Inter,"Segoe UI",Arial,sans-serif !important;
  font-size:28px !important;
  line-height:1.15 !important;
  font-weight:600 !important;
  letter-spacing:-.03em !important;
}
.tnb-ref-stage > p{
  margin:0 0 18px !important;
  color:#b8c5d6 !important;
  font-size:14px !important;
}
.tnb-ref-progress{
  position:relative !important;
  width:560px !important;
  max-width:78vw !important;
  height:5px !important;
  margin:0 auto 28px !important;
  border-radius:999px !important;
  background:#52627a !important;
  overflow:visible !important;
}
.tnb-ref-progress:before{
  content:"" !important;
  position:absolute !important;
  inset:0 auto 0 0 !important;
  width:32% !important;
  border-radius:999px !important;
  background:#4b94ff !important;
  animation:tnbRefProgressFill 2.8s ease-in-out infinite alternate !important;
}
.tnb-ref-progress i{
  position:absolute !important;
  z-index:2 !important;
  top:50% !important;
  left:32% !important;
  transform:translate(-50%,-50%) !important;
  color:#fff !important;
  font-size:18px !important;
  animation:tnbRefPlaneMove 2.8s ease-in-out infinite alternate !important;
  filter:drop-shadow(0 2px 4px rgba(0,0,0,.25)) !important;
}
@keyframes tnbRefProgressFill{
  0%{width:18%}
  100%{width:86%}
}
@keyframes tnbRefPlaneMove{
  0%{left:18%}
  100%{left:86%}
}

.tnb-ref-boardpass{
  display:grid !important;
  grid-template-columns:minmax(0,1fr) 260px !important;
  width:min(900px,92vw) !important;
  min-height:505px !important;
  margin:0 auto !important;
  overflow:hidden !important;
  border:1px solid rgba(255,255,255,.09) !important;
  border-radius:28px !important;
  background:#162c49 !important;
  box-shadow:0 28px 70px rgba(0,0,0,.18) !important;
  text-align:left !important;
}
.tnb-ref-board-main{
  position:relative !important;
  min-width:0 !important;
  padding:0 28px 24px !important;
  background:#162c49 !important;
}
.tnb-ref-board-head{
  height:58px !important;
  margin:0 -28px 22px !important;
  padding:0 28px !important;
  display:flex !important;
  align-items:center !important;
  border-bottom:1px solid rgba(255,255,255,.06) !important;
  background:#09172b !important;
}
.tnb-ref-board-head > div{
  display:flex !important;
  align-items:center !important;
  gap:10px !important;
  color:#fff !important;
  font-size:15px !important;
  font-weight:700 !important;
  letter-spacing:.08em !important;
}
.tnb-ref-board-head i{color:#fff !important}

.tnb-ref-passenger-row{
  display:grid !important;
  grid-template-columns:1.3fr .9fr .9fr !important;
  gap:20px !important;
  margin-bottom:24px !important;
}
.tnb-ref-passenger-row small,
.tnb-ref-flight-meta small,
.tnb-ref-city small,
.tnb-ref-side-grid small{
  display:block !important;
  margin-bottom:5px !important;
  color:#7f92ad !important;
  font-size:9px !important;
  font-weight:600 !important;
  letter-spacing:.08em !important;
}
.tnb-ref-passenger-row strong,
.tnb-ref-flight-meta strong{
  color:#fff !important;
  font-size:14px !important;
  font-weight:600 !important;
}

.tnb-ref-board-route{
  display:grid !important;
  grid-template-columns:1fr 1.3fr 1fr !important;
  align-items:center !important;
  gap:15px !important;
  margin:0 !important;
  padding:0 0 25px !important;
  border-bottom:1px solid rgba(255,255,255,.10) !important;
}
.tnb-ref-city{
  display:flex !important;
  align-items:center !important;
  gap:14px !important;
}
.tnb-ref-city.right{
  justify-content:flex-end !important;
  text-align:right !important;
}
.tnb-ref-city .tnb-board-code{
  margin:0 !important;
  color:#fff !important;
  font-size:52px !important;
  line-height:.95 !important;
  font-weight:700 !important;
  letter-spacing:-.055em !important;
}
.tnb-ref-city .tnb-board-city{
  margin-top:6px !important;
  color:#fff !important;
  font-size:15px !important;
  font-weight:500 !important;
}
.tnb-ref-city > div > span{
  display:block !important;
  margin-top:4px !important;
  color:#8192aa !important;
  font-size:9px !important;
}
.tnb-ref-barcode-mini{
  width:20px !important;
  height:116px !important;
  opacity:.9 !important;
  background:repeating-linear-gradient(
    to bottom,
    #e8edf4 0 3px,
    transparent 3px 6px
  ) !important;
}
.tnb-ref-route-line{
  position:relative !important;
  display:flex !important;
  align-items:center !important;
  justify-content:center !important;
  gap:0 !important;
  height:68px !important;
  color:#4f94ff !important;
}
.tnb-ref-route-line:before{
  content:"" !important;
  position:absolute !important;
  left:8px !important;
  right:8px !important;
  top:29px !important;
  border-top:1px dashed #637590 !important;
}
.tnb-ref-route-line i{
  position:relative !important;
  z-index:2 !important;
  padding:0 8px !important;
  background:#162c49 !important;
  color:#4b94ff !important;
  font-size:18px !important;
}
.tnb-ref-route-line .dot{
  position:absolute !important;
  z-index:2 !important;
  top:25px !important;
  width:8px !important;
  height:8px !important;
  border-radius:50% !important;
  background:#7d8da4 !important;
}
.tnb-ref-route-line .dot:first-child{left:4px !important}
.tnb-ref-route-line .dot:nth-of-type(2){right:4px !important}
.tnb-ref-route-line b{
  position:absolute !important;
  top:46px !important;
  left:50% !important;
  transform:translateX(-50%) !important;
  min-width:96px !important;
  padding:5px 10px !important;
  border-radius:999px !important;
  background:#263d5c !important;
  color:#dbe4ef !important;
  font-size:9px !important;
  text-align:center !important;
  font-weight:600 !important;
}

.tnb-ref-flight-meta{
  display:grid !important;
  grid-template-columns:repeat(5,minmax(0,1fr)) !important;
  gap:14px !important;
  margin-top:20px !important;
}
.tnb-ref-gate-note{
  display:flex !important;
  align-items:center !important;
  gap:8px !important;
  margin-top:20px !important;
  color:#71839c !important;
  font-size:10px !important;
}
.tnb-ref-wave{
  width:210px !important;
  height:42px !important;
  margin-top:18px !important;
  opacity:.16 !important;
  background:repeating-linear-gradient(
    90deg,
    #4c6280 0 4px,
    transparent 4px 7px
  ) !important;
}

.tnb-ref-board-side{
  position:relative !important;
  min-width:0 !important;
  padding:0 20px 20px !important;
  background:linear-gradient(180deg,#cc1738,#c51535) !important;
  color:#fff !important;
}
.tnb-ref-board-side:before{
  content:"" !important;
  position:absolute !important;
  left:-1px !important;
  top:10px !important;
  bottom:10px !important;
  border-left:1px dashed rgba(255,255,255,.25) !important;
}
.tnb-ref-board-side h3{
  height:58px !important;
  margin:0 -20px 16px !important;
  padding:0 20px !important;
  display:flex !important;
  align-items:center !important;
  gap:8px !important;
  background:#b91633 !important;
  color:#fff !important;
  font-size:15px !important;
  font-weight:700 !important;
}
.tnb-ref-board-side dl{
  display:grid !important;
  grid-template-columns:1fr !important;
  gap:0 !important;
  margin:0 !important;
}
.tnb-ref-board-side dt{
  margin-top:11px !important;
  color:#f2a9b7 !important;
  font-size:8px !important;
  font-weight:600 !important;
  letter-spacing:.08em !important;
}
.tnb-ref-board-side dd{
  margin:3px 0 0 !important;
  color:#fff !important;
  font-size:13px !important;
  font-weight:600 !important;
}
.tnb-ref-side-grid{
  display:grid !important;
  grid-template-columns:1fr 1fr !important;
  gap:12px !important;
  margin-top:16px !important;
}
.tnb-ref-side-grid > div:last-child{grid-column:1/-1 !important}
.tnb-ref-side-grid small{color:#f2a9b7 !important}
.tnb-ref-side-grid strong{
  color:#fff !important;
  font-size:10px !important;
  font-weight:600 !important;
}
.tnb-ref-ticket-code{
  margin-top:18px !important;
  padding:10px 12px !important;
  border-radius:14px !important;
  background:rgba(255,255,255,.12) !important;
  color:#ffdbe3 !important;
  font-size:7px !important;
  letter-spacing:.05em !important;
}
.tnb-ref-side-bars{
  position:absolute !important;
  left:20px !important;
  right:20px !important;
  bottom:18px !important;
  height:48px !important;
  background:repeating-linear-gradient(
    90deg,
    rgba(255,255,255,.95) 0 2px,
    transparent 2px 5px,
    rgba(255,255,255,.8) 5px 8px,
    transparent 8px 11px
  ) !important;
}
.tnb-ref-board-foot{
  display:flex !important;
  align-items:center !important;
  justify-content:center !important;
  gap:8px !important;
  margin-top:18px !important;
  color:#93a5bd !important;
  font-size:12px !important;
}
.tnb-ref-board-foot > span{
  width:7px !important;
  height:7px !important;
  border-radius:50% !important;
  background:#4b94ff !important;
  box-shadow:0 0 0 4px rgba(75,148,255,.08) !important;
}

@media(max-width:820px){
  .tnb-ref-boardpass{
    grid-template-columns:1fr !important;
    width:min(640px,94vw) !important;
  }
  .tnb-ref-board-side{
    min-height:300px !important;
  }
}
@media(max-width:600px){
  .tnb-ref-stage{padding-top:20px !important}
  .tnb-ref-stage > h2{font-size:22px !important}
  .tnb-ref-board-main{padding:0 18px 20px !important}
  .tnb-ref-board-head{margin-left:-18px !important;margin-right:-18px !important;padding:0 18px !important}
  .tnb-ref-passenger-row{grid-template-columns:1fr 1fr !important}
  .tnb-ref-board-route{grid-template-columns:1fr !important;text-align:center !important}
  .tnb-ref-city,.tnb-ref-city.right{justify-content:center !important;text-align:center !important}
  .tnb-ref-route-line{height:60px !important}
  .tnb-ref-flight-meta{grid-template-columns:repeat(2,1fr) !important}
  .tnb-ref-city .tnb-board-code{font-size:44px !important}
}


/* =========================================================
   SEARCH LOADER V8 — COMPACT REFERENCE MATCH
   Visual-only refinement. Existing loader JS/hooks preserved.
   ========================================================= */
.tnb-search-overlay.tnb-ref-loader{
    background:#172844!important;
}
.tnb-ref-stage{
    width:min(940px,92vw)!important;
    max-width:940px!important;
    padding:42px 0 30px!important;
}
.tnb-ref-stage>h2{
    margin:0 0 7px!important;
    font-size:24px!important;
    line-height:1.15!important;
    font-weight:600!important;
    letter-spacing:-.03em!important;
}
.tnb-ref-stage>p{
    margin:0 0 18px!important;
    color:#aebed1!important;
    font-size:13px!important;
}
.tnb-ref-progress{
    width:505px!important;
    max-width:70vw!important;
    height:4px!important;
    margin:0 auto 28px!important;
    background:#526076!important;
}
.tnb-ref-progress i{
    font-size:16px!important;
}
.tnb-ref-boardpass{
    grid-template-columns:minmax(0,1fr) 235px!important;
    width:min(865px,90vw)!important;
    min-height:0!important;
    border-radius:26px!important;
    box-shadow:0 24px 55px rgba(0,0,0,.15)!important;
}
.tnb-ref-board-main{
    padding:0 26px 22px!important;
}
.tnb-ref-board-head{
    height:54px!important;
    margin:0 -26px 20px!important;
    padding:0 26px!important;
}
.tnb-ref-board-head>div{
    gap:9px!important;
    font-size:14px!important;
}
.tnb-ref-passenger-row{
    grid-template-columns:1.15fr .85fr .85fr!important;
    gap:18px!important;
    margin-bottom:20px!important;
}
.tnb-ref-passenger-row small,
.tnb-ref-flight-meta small,
.tnb-ref-city small,
.tnb-ref-side-grid small{
    margin-bottom:4px!important;
    font-size:8px!important;
}
.tnb-ref-passenger-row strong,
.tnb-ref-flight-meta strong{
    font-size:12px!important;
}
.tnb-ref-board-route{
    grid-template-columns:1fr 1.15fr 1fr!important;
    gap:12px!important;
    padding-bottom:20px!important;
}
.tnb-ref-city{
    gap:11px!important;
}
.tnb-ref-city .tnb-board-code{
    font-size:47px!important;
}
.tnb-ref-city .tnb-board-city{
    margin-top:5px!important;
    font-size:13px!important;
}
.tnb-ref-city>div>span{
    margin-top:3px!important;
    font-size:8px!important;
}
.tnb-ref-barcode-mini{
    width:17px!important;
    height:104px!important;
}
.tnb-ref-route-line{
    height:62px!important;
}
.tnb-ref-route-line:before{
    top:27px!important;
}
.tnb-ref-route-line i{
    font-size:17px!important;
}
.tnb-ref-route-line .dot{
    top:23px!important;
    width:7px!important;
    height:7px!important;
}
.tnb-ref-route-line b{
    top:43px!important;
    min-width:86px!important;
    padding:4px 9px!important;
    font-size:8px!important;
}
.tnb-ref-flight-meta{
    gap:10px!important;
    margin-top:17px!important;
}
.tnb-ref-gate-note{
    margin-top:17px!important;
    font-size:9px!important;
}
.tnb-ref-wave{
    width:190px!important;
    height:38px!important;
    margin-top:15px!important;
}
.tnb-ref-board-side{
    padding:0 18px 18px!important;
}
.tnb-ref-board-side h3{
    height:54px!important;
    margin:0 -18px 14px!important;
    padding:0 18px!important;
    font-size:14px!important;
}
.tnb-ref-board-side dt{
    margin-top:9px!important;
    font-size:7px!important;
}
.tnb-ref-board-side dd{
    margin-top:2px!important;
    font-size:12px!important;
}
.tnb-ref-side-grid{
    gap:10px!important;
    margin-top:14px!important;
}
.tnb-ref-side-grid strong{
    font-size:9px!important;
}
.tnb-ref-ticket-code{
    margin-top:15px!important;
    padding:9px 10px!important;
    border-radius:12px!important;
    font-size:6px!important;
}
.tnb-ref-side-bars{
    left:18px!important;
    right:18px!important;
    bottom:16px!important;
    height:43px!important;
}
.tnb-ref-board-foot{
    margin-top:16px!important;
    font-size:11px!important;
}
@media(max-width:820px){
    .tnb-ref-boardpass{
        grid-template-columns:1fr!important;
        width:min(610px,94vw)!important;
    }
    .tnb-ref-board-side{
        min-height:270px!important;
    }
}

</style>

@unless(request()->is('/'))
<link href="/assets/css/crm-visual-pages-manager-v1.css" rel="stylesheet">
@endunless

<style id="tnb-loader-v9-final">
html body #tnbSearchOverlay.tnb-search-overlay.tnb-ref-loader{background:#172844!important}
html body #tnbSearchOverlay .tnb-ref-stage{width:920px!important;max-width:92vw!important;padding:34px 0 24px!important;margin:0 auto!important}
html body #tnbSearchOverlay .tnb-ref-stage>h2{margin:0 0 7px!important;font-size:24px!important;line-height:1.15!important;font-weight:600!important;letter-spacing:-.03em!important}
html body #tnbSearchOverlay .tnb-ref-stage>p{margin:0 0 16px!important;font-size:13px!important;color:#b6c3d4!important}
html body #tnbSearchOverlay .tnb-ref-progress{width:500px!important;max-width:66vw!important;height:4px!important;margin:0 auto 24px!important}
html body #tnbSearchOverlay .tnb-ref-boardpass{display:grid!important;grid-template-columns:minmax(0,1fr) 220px!important;width:820px!important;max-width:90vw!important;min-height:0!important;height:470px!important;margin:0 auto!important;border-radius:24px!important;overflow:hidden!important}
html body #tnbSearchOverlay .tnb-ref-board-main{padding:0 24px 18px!important;min-width:0!important}
html body #tnbSearchOverlay .tnb-ref-board-head{height:52px!important;margin:0 -24px 18px!important;padding:0 24px!important}
html body #tnbSearchOverlay .tnb-ref-board-head>div{font-size:14px!important}
html body #tnbSearchOverlay .tnb-ref-passenger-row{grid-template-columns:1.15fr .85fr .85fr!important;gap:16px!important;margin-bottom:17px!important}
html body #tnbSearchOverlay .tnb-ref-passenger-row small,
html body #tnbSearchOverlay .tnb-ref-flight-meta small,
html body #tnbSearchOverlay .tnb-ref-city small,
html body #tnbSearchOverlay .tnb-ref-side-grid small{font-size:8px!important;margin-bottom:3px!important}
html body #tnbSearchOverlay .tnb-ref-passenger-row strong,
html body #tnbSearchOverlay .tnb-ref-flight-meta strong{font-size:12px!important}
html body #tnbSearchOverlay .tnb-ref-board-route{grid-template-columns:1fr 1.15fr 1fr!important;gap:10px!important;padding:0 0 17px!important}
html body #tnbSearchOverlay .tnb-ref-city{gap:10px!important}
html body #tnbSearchOverlay .tnb-ref-city .tnb-board-code{font-size:46px!important;line-height:.92!important;letter-spacing:-.055em!important}
html body #tnbSearchOverlay .tnb-ref-city .tnb-board-city{margin-top:5px!important;font-size:12px!important;font-weight:500!important}
html body #tnbSearchOverlay .tnb-ref-city>div>span{display:none!important}
html body #tnbSearchOverlay .tnb-ref-barcode-mini{width:16px!important;height:96px!important}
html body #tnbSearchOverlay .tnb-ref-route-line{height:58px!important}
html body #tnbSearchOverlay .tnb-ref-route-line:before{top:25px!important}
html body #tnbSearchOverlay .tnb-ref-route-line .dot{top:22px!important;width:7px!important;height:7px!important}
html body #tnbSearchOverlay .tnb-ref-route-line i{font-size:16px!important}
html body #tnbSearchOverlay .tnb-ref-route-line b{top:40px!important;min-width:84px!important;padding:4px 8px!important;font-size:8px!important}
html body #tnbSearchOverlay .tnb-ref-flight-meta{gap:8px!important;margin-top:15px!important}
html body #tnbSearchOverlay .tnb-ref-gate-note{margin-top:14px!important;font-size:9px!important}
html body #tnbSearchOverlay .tnb-ref-wave{width:170px!important;height:34px!important;margin-top:12px!important}
html body #tnbSearchOverlay .tnb-ref-board-side{padding:0 16px 16px!important}
html body #tnbSearchOverlay .tnb-ref-board-side h3{height:52px!important;margin:0 -16px 12px!important;padding:0 16px!important;font-size:13px!important}
html body #tnbSearchOverlay .tnb-ref-board-side dt{margin-top:8px!important;font-size:7px!important}
html body #tnbSearchOverlay .tnb-ref-board-side dd{margin-top:2px!important;font-size:11px!important}
html body #tnbSearchOverlay .tnb-ref-side-grid{gap:8px!important;margin-top:12px!important}
html body #tnbSearchOverlay .tnb-ref-side-grid strong{font-size:8px!important}
html body #tnbSearchOverlay .tnb-ref-ticket-code{margin-top:12px!important;padding:8px 9px!important;font-size:6px!important}
html body #tnbSearchOverlay .tnb-ref-side-bars{left:16px!important;right:16px!important;bottom:14px!important;height:38px!important}
html body #tnbSearchOverlay .tnb-ref-board-foot{margin-top:13px!important;font-size:10px!important}
@media(max-width:760px){
  html body #tnbSearchOverlay .tnb-ref-boardpass{grid-template-columns:1fr!important;width:min(600px,94vw)!important;height:auto!important}
  html body #tnbSearchOverlay .tnb-ref-board-side{min-height:255px!important}
}
</style>


<style id="tnb-loader-v10-reference-remake">
/* =========================================================
   V10 — REBUILT SEARCH LOADER
   Matches the 2nd reference screenshot more closely.
   Existing search JS/data hooks remain untouched.
   ========================================================= */

html body #tnbSearchOverlay.tnb-search-overlay{
    background:
        radial-gradient(circle at 12% 72%, rgba(64,116,175,.20), transparent 26%),
        radial-gradient(circle at 86% 20%, rgba(40,84,140,.18), transparent 28%),
        linear-gradient(180deg,#1b3152 0%,#152945 50%,#10233d 100%) !important;
    color:#fff!important;
}

/* subtle light/cloudy atmosphere */
html body #tnbSearchOverlay.tnb-search-overlay:before{
    content:"";
    position:absolute;
    inset:0;
    pointer-events:none;
    background:
        radial-gradient(ellipse at 15% 100%, rgba(255,255,255,.07), transparent 26%),
        radial-gradient(ellipse at 84% 100%, rgba(255,255,255,.06), transparent 29%),
        linear-gradient(180deg, rgba(255,255,255,.025), transparent 40%);
}

html body #tnbSearchOverlay .tnb-ref-stage{
    position:relative!important;
    z-index:1!important;
    width:940px!important;
    max-width:94vw!important;
    padding:28px 0 26px!important;
    margin:0 auto!important;
}

/* Heading */
html body #tnbSearchOverlay .tnb-ref-stage>h2{
    margin:0 0 8px!important;
    color:#fff!important;
    font-size:25px!important;
    line-height:1.15!important;
    font-weight:600!important;
    letter-spacing:-.03em!important;
    text-shadow:0 2px 12px rgba(0,0,0,.12)!important;
}
html body #tnbSearchOverlay .tnb-ref-stage>p{
    margin:0 0 18px!important;
    color:#c2cedd!important;
    font-size:13px!important;
}

/* top progress */
html body #tnbSearchOverlay .tnb-ref-progress{
    width:515px!important;
    max-width:70vw!important;
    height:4px!important;
    margin:0 auto 25px!important;
    border-radius:999px!important;
    background:#52627a!important;
}
html body #tnbSearchOverlay .tnb-ref-progress:before{
    background:linear-gradient(90deg,#3478ff,#53a3ff)!important;
    box-shadow:0 0 12px rgba(69,145,255,.35)!important;
}
html body #tnbSearchOverlay .tnb-ref-progress i{
    color:#fff!important;
    font-size:17px!important;
    filter:drop-shadow(0 0 6px rgba(255,255,255,.25))!important;
}

/* Main boarding card */
html body #tnbSearchOverlay .tnb-ref-boardpass{
    display:grid!important;
    grid-template-columns:minmax(0,1fr) 236px!important;
    width:835px!important;
    max-width:92vw!important;
    height:420px!important;
    min-height:0!important;
    margin:0 auto!important;
    overflow:hidden!important;
    border:1px solid rgba(126,164,211,.35)!important;
    border-radius:22px!important;
    background:#0c203a!important;
    box-shadow:
        0 24px 65px rgba(2,13,31,.27),
        inset 0 1px 0 rgba(255,255,255,.03)!important;
}

html body #tnbSearchOverlay .tnb-ref-board-main{
    position:relative!important;
    min-width:0!important;
    padding:0 20px 16px!important;
    background:
        radial-gradient(circle at 52% 54%, rgba(41,80,127,.16), transparent 30%),
        linear-gradient(180deg,#0c2039 0%,#0e2744 100%)!important;
}

/* faint world-map-ish dot texture */
html body #tnbSearchOverlay .tnb-ref-board-main:after{
    content:"";
    position:absolute;
    left:145px;
    right:70px;
    top:112px;
    height:145px;
    pointer-events:none;
    opacity:.13;
    background-image:
        radial-gradient(circle,#6fa4db 1px,transparent 1.2px);
    background-size:7px 7px;
    -webkit-mask-image:
        radial-gradient(ellipse at 50% 50%,#000 0 53%,transparent 72%);
    mask-image:
        radial-gradient(ellipse at 50% 50%,#000 0 53%,transparent 72%);
}

/* Boarding pass header */
html body #tnbSearchOverlay .tnb-ref-board-head{
    position:relative!important;
    z-index:2!important;
    height:51px!important;
    margin:0 -20px 14px!important;
    padding:0 18px!important;
    border-bottom:1px solid rgba(255,255,255,.05)!important;
    background:#08192e!important;
}
html body #tnbSearchOverlay .tnb-ref-board-head>div{
    gap:10px!important;
    font-size:14px!important;
    font-weight:600!important;
    letter-spacing:.02em!important;
}
html body #tnbSearchOverlay .tnb-ref-board-head i{
    display:grid!important;
    place-items:center!important;
    width:34px!important;
    height:34px!important;
    border-radius:9px!important;
    background:#12345d!important;
    color:#67a8ff!important;
}

/* passenger row */
html body #tnbSearchOverlay .tnb-ref-passenger-row{
    position:relative!important;
    z-index:2!important;
    grid-template-columns:1.2fr .85fr .85fr!important;
    gap:16px!important;
    margin-bottom:12px!important;
    padding:0 2px!important;
}
html body #tnbSearchOverlay .tnb-ref-passenger-row small,
html body #tnbSearchOverlay .tnb-ref-flight-meta small,
html body #tnbSearchOverlay .tnb-ref-city small,
html body #tnbSearchOverlay .tnb-ref-side-grid small{
    margin-bottom:3px!important;
    color:#7f96b3!important;
    font-size:7px!important;
    line-height:1!important;
    letter-spacing:.08em!important;
}
html body #tnbSearchOverlay .tnb-ref-passenger-row strong,
html body #tnbSearchOverlay .tnb-ref-flight-meta strong{
    color:#fff!important;
    font-size:11px!important;
    font-weight:500!important;
}

/* Route */
html body #tnbSearchOverlay .tnb-ref-board-route{
    position:relative!important;
    z-index:2!important;
    grid-template-columns:1fr 1.28fr 1fr!important;
    gap:10px!important;
    margin:0!important;
    padding:10px 0 14px!important;
    border-bottom:1px solid rgba(255,255,255,.08)!important;
}

html body #tnbSearchOverlay .tnb-ref-city{
    gap:10px!important;
}
html body #tnbSearchOverlay .tnb-ref-city .tnb-board-code{
    color:#fff!important;
    font-size:40px!important;
    line-height:.92!important;
    font-weight:600!important;
    letter-spacing:-.055em!important;
}
html body #tnbSearchOverlay .tnb-ref-city .tnb-board-city{
    margin-top:5px!important;
    color:#fff!important;
    font-size:11px!important;
    font-weight:400!important;
}
html body #tnbSearchOverlay .tnb-ref-city>div>span{
    display:block!important;
    margin-top:3px!important;
    color:#7188a5!important;
    font-size:7px!important;
}
html body #tnbSearchOverlay .tnb-ref-barcode-mini{
    width:15px!important;
    height:82px!important;
    opacity:.62!important;
}

/* route line + continuously moving plane */
html body #tnbSearchOverlay .tnb-ref-route-line{
    position:relative!important;
    height:68px!important;
    overflow:visible!important;
}
html body #tnbSearchOverlay .tnb-ref-route-line:before{
    left:10px!important;
    right:10px!important;
    top:31px!important;
    border-top:1px dashed rgba(126,151,181,.58)!important;
}
html body #tnbSearchOverlay .tnb-ref-route-line .dot{
    top:28px!important;
    width:7px!important;
    height:7px!important;
    background:#647c98!important;
}
html body #tnbSearchOverlay .tnb-ref-route-line i{
    position:absolute!important;
    z-index:4!important;
    top:20px!important;
    left:10%!important;
    padding:0 5px!important;
    background:#102843!important;
    color:#4194ff!important;
    font-size:17px!important;
    transform:translateX(-50%)!important;
    animation:tnbRoutePlanePingPong 2.4s ease-in-out infinite alternate!important;
}
html body #tnbSearchOverlay .tnb-ref-route-line b{
    top:46px!important;
    min-width:88px!important;
    padding:4px 9px!important;
    border-radius:999px!important;
    background:#1b3a60!important;
    color:#dfeaff!important;
    font-size:7px!important;
}
html body #tnbSearchOverlay .tnb-ref-route-line b:after{
    content:"";
    display:inline-block;
    width:6px;
    height:6px;
    margin-left:6px;
    border-radius:50%;
    background:#31d47b;
    box-shadow:0 0 8px rgba(49,212,123,.55);
}
@keyframes tnbRoutePlanePingPong{
    from{left:12%}
    to{left:88%}
}

/* lower flight metadata */
html body #tnbSearchOverlay .tnb-ref-flight-meta{
    position:relative!important;
    z-index:2!important;
    grid-template-columns:repeat(5,minmax(0,1fr))!important;
    gap:8px!important;
    margin-top:13px!important;
    padding-bottom:12px!important;
}
html body #tnbSearchOverlay .tnb-ref-gate-note{
    position:relative!important;
    z-index:2!important;
    display:flex!important;
    align-items:center!important;
    min-height:42px!important;
    margin-top:0!important;
    padding:0 12px!important;
    border:1px solid rgba(111,143,181,.10)!important;
    border-radius:12px!important;
    background:rgba(4,18,35,.20)!important;
    color:#95a8bf!important;
    font-size:8px!important;
}
html body #tnbSearchOverlay .tnb-ref-gate-note i{
    display:grid!important;
    place-items:center!important;
    width:25px!important;
    height:25px!important;
    margin-right:6px!important;
    border-radius:8px!important;
    background:#0d3156!important;
    color:#3193ff!important;
}

/* hide old fake bar wave inside main panel */
html body #tnbSearchOverlay .tnb-ref-wave{
    display:none!important;
}

/* red side */
html body #tnbSearchOverlay .tnb-ref-board-side{
    position:relative!important;
    padding:0 16px 15px!important;
    background:
        radial-gradient(circle at 70% 20%,rgba(255,91,114,.10),transparent 26%),
        linear-gradient(180deg,#a90c2b 0%,#b71031 45%,#991029 100%)!important;
    box-shadow:inset 18px 0 35px rgba(45,0,12,.10)!important;
}
html body #tnbSearchOverlay .tnb-ref-board-side:before{
    border-left:1px dashed rgba(255,255,255,.18)!important;
}
html body #tnbSearchOverlay .tnb-ref-board-side h3{
    height:51px!important;
    margin:0 -16px 13px!important;
    padding:0 16px!important;
    background:#941029!important;
    font-size:13px!important;
    font-weight:600!important;
}
html body #tnbSearchOverlay .tnb-ref-board-side h3>span:first-child{
    display:grid!important;
    place-items:center!important;
    width:31px!important;
    height:31px!important;
    border-radius:8px!important;
    background:rgba(255,255,255,.07)!important;
}
html body #tnbSearchOverlay .tnb-ref-board-side dl{
    display:grid!important;
    grid-template-columns:1fr 1fr!important;
    column-gap:15px!important;
    row-gap:0!important;
}
html body #tnbSearchOverlay .tnb-ref-board-side dl dt:first-of-type,
html body #tnbSearchOverlay .tnb-ref-board-side dl dd:first-of-type{
    grid-column:1 / -1!important;
}
html body #tnbSearchOverlay .tnb-ref-board-side dt{
    margin-top:9px!important;
    color:#e4a6b4!important;
    font-size:7px!important;
}
html body #tnbSearchOverlay .tnb-ref-board-side dd{
    margin-top:2px!important;
    color:#fff!important;
    font-size:11px!important;
    font-weight:500!important;
}

html body #tnbSearchOverlay .tnb-ref-side-grid{
    grid-template-columns:1fr 1fr!important;
    gap:9px 14px!important;
    margin-top:12px!important;
    padding-top:10px!important;
    border-top:1px solid rgba(255,255,255,.16)!important;
}
html body #tnbSearchOverlay .tnb-ref-side-grid>div:last-child{
    grid-column:1/-1!important;
    padding-top:8px!important;
    border-top:1px solid rgba(255,255,255,.12)!important;
}
html body #tnbSearchOverlay .tnb-ref-side-grid strong{
    color:#fff!important;
    font-size:8px!important;
}
html body #tnbSearchOverlay .tnb-ref-side-grid>div:last-child strong:before{
    content:"";
    display:inline-block;
    width:6px;
    height:6px;
    margin-right:6px;
    border-radius:50%;
    background:#ff7f69;
}

/* ticket tags */
html body #tnbSearchOverlay .tnb-ref-ticket-code{
    margin-top:12px!important;
    padding:0!important;
    border-radius:0!important;
    background:transparent!important;
    color:transparent!important;
    font-size:0!important;
}
html body #tnbSearchOverlay .tnb-ref-ticket-code:after{
    content:"SEA   LIVE   PNR   FARESHUNT";
    display:block;
    padding:7px 8px!important;
    border-radius:999px!important;
    border:1px solid rgba(255,255,255,.14)!important;
    background:rgba(255,255,255,.04)!important;
    color:#e8b7c2!important;
    font-size:6px!important;
    letter-spacing:.08em!important;
}

/* barcode */
html body #tnbSearchOverlay .tnb-ref-side-bars{
    left:16px!important;
    right:16px!important;
    bottom:14px!important;
    height:38px!important;
    opacity:.63!important;
}

/* lower status pill */
html body #tnbSearchOverlay .tnb-ref-board-foot{
    display:inline-flex!important;
    align-items:center!important;
    justify-content:center!important;
    gap:8px!important;
    min-height:31px!important;
    margin:14px auto 0!important;
    padding:0 14px!important;
    border:1px solid rgba(104,139,184,.18)!important;
    border-radius:999px!important;
    background:rgba(4,18,35,.22)!important;
    color:#c2cfdf!important;
    font-size:9px!important;
}
html body #tnbSearchOverlay .tnb-ref-board-foot>span{
    width:6px!important;
    height:6px!important;
    background:#398cff!important;
}

/* mobile */
@media(max-width:760px){
    html body #tnbSearchOverlay .tnb-ref-boardpass{
        grid-template-columns:1fr!important;
        width:min(610px,94vw)!important;
        height:auto!important;
    }
    html body #tnbSearchOverlay .tnb-ref-board-side{
        min-height:255px!important;
    }
    html body #tnbSearchOverlay .tnb-ref-route-line{
        min-width:180px!important;
    }
}
</style>


<style id="tnb-loader-v11-plane-line-fix">
/* V11 FIX: animated top plane + remove unwanted vertical divider */

/* Remove the vertical dotted/dashed line completely */
html body #tnbSearchOverlay .tnb-ref-board-side:before,
html body #tnbSearchOverlay .tnb-ref-board-side::before{
    display:none !important;
    content:none !important;
    border:0 !important;
}
html body #tnbSearchOverlay .tnb-ref-board-main{
    border-right:0 !important;
}
html body #tnbSearchOverlay .tnb-ref-board-side{
    border-left:0 !important;
}

/* Top loading track */
html body #tnbSearchOverlay .tnb-ref-progress{
    position:relative !important;
    overflow:visible !important;
}
html body #tnbSearchOverlay .tnb-ref-progress i{
    position:absolute !important;
    z-index:10 !important;
    top:50% !important;
    left:0 !important;
    right:auto !important;
    margin:0 !important;
    transform:translate(-50%,-50%) !important;
    animation:tnbTopPlaneTravel 2.1s ease-in-out infinite alternate !important;
    will-change:left !important;
}

/* Force actual left-to-right and right-to-left movement */
@keyframes tnbTopPlaneTravel{
    0%{
        left:0%;
        transform:translate(-10%,-50%) rotate(0deg);
    }
    100%{
        left:100%;
        transform:translate(-90%,-50%) rotate(0deg);
    }
}

/* Center route plane also keeps travelling */
html body #tnbSearchOverlay .tnb-ref-route-line i{
    animation:tnbCenterPlaneTravel 2.2s ease-in-out infinite alternate !important;
    will-change:left !important;
}
@keyframes tnbCenterPlaneTravel{
    from{left:12%;}
    to{left:88%;}
}

@media (prefers-reduced-motion: reduce){
    html body #tnbSearchOverlay .tnb-ref-progress i,
    html body #tnbSearchOverlay .tnb-ref-route-line i{
        animation-duration:2.1s !important;
        animation-iteration-count:infinite !important;
    }
}
</style>


<style id="tnb-loader-v12-hard-fix">
/* HARD FIX — remove divider by covering the join, and let JS control planes */
html body #tnbSearchOverlay .tnb-ref-board-side:before,
html body #tnbSearchOverlay .tnb-ref-board-side::before{
    display:none!important;
    content:none!important;
    border:0!important;
    width:0!important;
    opacity:0!important;
}

html body #tnbSearchOverlay .tnb-ref-board-main{
    position:relative!important;
    border-right:0!important;
}

/* Cover any inherited/grid divider exactly at the join */
html body #tnbSearchOverlay .tnb-ref-board-main:before{
    content:""!important;
    position:absolute!important;
    z-index:50!important;
    top:0!important;
    right:-3px!important;
    bottom:0!important;
    width:6px!important;
    background:#0e2744!important;
    pointer-events:none!important;
}

html body #tnbSearchOverlay .tnb-ref-board-side{
    border-left:0!important;
    outline:0!important;
}

/* Disable conflicting CSS animation — JS below controls movement */
html body #tnbSearchOverlay .tnb-ref-progress i,
html body #tnbSearchOverlay .tnb-ref-route-line i{
    animation:none!important;
    transition:none!important;
    will-change:left!important;
}
</style>


<style id="fhx-loader-v13-css">
/* =========================
   FULLSCREEN WRAPPER
   ========================= */
#tnbSearchOverlay.fhx-loader{
  position:fixed!important;
  inset:0!important;
  z-index:2147483000!important;
  display:none!important;
  align-items:center!important;
  justify-content:center!important;
  overflow:auto!important;
  padding:28px 16px!important;
  background:
    radial-gradient(circle at 15% 82%,rgba(115,161,211,.14),transparent 27%),
    radial-gradient(circle at 87% 12%,rgba(93,137,192,.12),transparent 26%),
    linear-gradient(180deg,#243b5d 0%,#1c3352 47%,#172d4a 100%)!important;
  font-family:Inter,"Segoe UI",Arial,sans-serif!important;
}
#tnbSearchOverlay.fhx-loader.fhx-show{
  display:flex!important;
}
#tnbSearchOverlay.fhx-loader *{
  box-sizing:border-box!important;
}

.fhx-shell{
  width:min(900px,94vw);
  margin:auto;
  color:#fff;
}

/* =========================
   HEADING + TOP MOVING PLANE
   ========================= */
.fhx-heading{
  text-align:center;
}
.fhx-heading h2{
  margin:0 0 8px!important;
  color:#fff!important;
  font-size:28px!important;
  line-height:1.15!important;
  font-weight:600!important;
  letter-spacing:-.035em!important;
}
.fhx-heading p{
  margin:0!important;
  color:#c8d4e2!important;
  font-size:14px!important;
}
.fhx-progress{
  position:relative;
  width:min(520px,68vw);
  height:4px;
  margin:22px auto 28px;
  overflow:visible;
  border-radius:99px;
  background:#65758c;
}
.fhx-progress-fill{
  position:absolute;
  inset:0 auto 0 0;
  width:42%;
  border-radius:99px;
  background:linear-gradient(90deg,#377dff,#5da7ff);
  box-shadow:0 0 14px rgba(76,151,255,.28);
}
.fhx-top-plane{
  position:absolute!important;
  z-index:4;
  top:50%;
  left:3%;
  color:#fff!important;
  font-size:18px!important;
  transform:translate(-50%,-50%);
  filter:drop-shadow(0 2px 5px rgba(0,0,0,.24));
  animation:fhxTopPlane 2.4s ease-in-out infinite alternate!important;
}
@keyframes fhxTopPlane{
  from{left:3%}
  to{left:97%}
}

/* =========================
   TICKET
   ========================= */
.fhx-ticket{
  display:grid;
  grid-template-columns:minmax(0,1fr) 235px;
  width:min(840px,92vw);
  height:420px;
  margin:0 auto;
  overflow:hidden;
  border:1px solid rgba(145,177,215,.30);
  border-radius:22px;
  background:#0c213b;
  box-shadow:0 28px 70px rgba(3,14,31,.28);
}

.fhx-ticket-main{
  min-width:0;
  padding:0 20px 18px;
  background:
    radial-gradient(circle at 51% 50%,rgba(58,100,150,.12),transparent 33%),
    linear-gradient(180deg,#0c2039 0%,#102945 100%);
}
.fhx-ticket-head{
  height:52px;
  margin:0 -20px 14px;
  padding:0 18px;
  display:flex;
  align-items:center;
  gap:10px;
  border-bottom:1px solid rgba(255,255,255,.05);
  background:#091a2f;
}
.fhx-head-icon{
  display:grid;
  place-items:center;
  width:34px;
  height:34px;
  border-radius:9px;
  background:#12375f;
  color:#71b0ff;
}
.fhx-ticket-head strong{
  color:#fff;
  font-size:14px;
  font-weight:600;
  letter-spacing:.025em;
}

.fhx-passenger-grid{
  display:grid;
  grid-template-columns:1.25fr .85fr .85fr;
  gap:16px;
  padding:0 2px;
}
.fhx-passenger-grid small,
.fhx-meta small,
.fhx-airport small,
.fhx-ticket-side small{
  display:block;
  margin-bottom:4px;
  color:#8299b5;
  font-size:8px;
  font-weight:500;
  letter-spacing:.08em;
}
.fhx-passenger-grid b{
  color:#fff;
  font-size:12px;
  font-weight:500;
}

/* =========================
   ROUTE
   ========================= */
.fhx-route{
  display:grid;
  grid-template-columns:1fr 1.35fr 1fr;
  gap:10px;
  align-items:center;
  margin-top:18px;
  padding:8px 0 18px;
  border-bottom:1px solid rgba(255,255,255,.09);
}
.fhx-airport{
  display:flex;
  align-items:center;
  gap:10px;
}
.fhx-airport strong{
  display:block;
  color:#fff;
  font-size:42px;
  line-height:.92;
  font-weight:600;
  letter-spacing:-.055em;
}
.fhx-airport b{
  display:block;
  margin-top:6px;
  color:#fff;
  font-size:11px;
  font-weight:400;
}
.fhx-destination{
  justify-content:flex-end;
  text-align:right;
}
.fhx-mini-bars{
  width:15px;
  height:84px;
  opacity:.65;
  background:repeating-linear-gradient(
    to bottom,
    #d9e4ef 0 3px,
    transparent 3px 6px
  );
}

.fhx-route-center{
  position:relative;
  height:82px;
}
.fhx-route-dash{
  position:absolute;
  left:9px;
  right:9px;
  top:31px;
  border-top:1px dashed rgba(136,160,188,.58);
}
.fhx-route-dot{
  position:absolute;
  z-index:2;
  top:28px;
  width:7px;
  height:7px;
  border-radius:50%;
  background:#7388a3;
}
.fhx-dot-left{left:5px}
.fhx-dot-right{right:5px}

.fhx-route-plane{
  position:absolute!important;
  z-index:5;
  top:23px;
  left:12%;
  color:#4b9cff!important;
  font-size:18px!important;
  transform:translateX(-50%);
  filter:drop-shadow(0 0 5px rgba(75,156,255,.35));
  animation:fhxRoutePlane 2.25s ease-in-out infinite alternate!important;
}
@keyframes fhxRoutePlane{
  from{left:12%}
  to{left:88%}
}

.fhx-live-pill{
  position:absolute;
  top:50px;
  left:50%;
  transform:translateX(-50%);
  display:flex;
  align-items:center;
  gap:6px;
  padding:5px 10px;
  border-radius:999px;
  background:#1b3a60;
  color:#e3eeff;
  font-size:7px;
  font-weight:600;
  white-space:nowrap;
}
.fhx-live-pill i{
  width:6px;
  height:6px;
  border-radius:50%;
  background:#2ed37c;
  box-shadow:0 0 8px rgba(46,211,124,.48);
}

/* =========================
   LOWER META
   ========================= */
.fhx-meta{
  display:grid;
  grid-template-columns:repeat(5,minmax(0,1fr));
  gap:8px;
  margin-top:14px;
}
.fhx-meta b{
  color:#fff;
  font-size:11px;
  font-weight:500;
}
.fhx-meta b i{
  display:inline-block;
  width:6px;
  height:6px;
  margin-left:4px;
  border-radius:50%;
  background:#29cf78;
}

.fhx-compare{
  position:relative;
  min-height:47px;
  margin-top:14px;
  display:flex;
  align-items:center;
  gap:9px;
  overflow:hidden;
  padding:0 12px;
  border:1px solid rgba(121,150,186,.10);
  border-radius:12px;
  background:rgba(4,17,33,.19);
  color:#9cb0c7;
  font-size:8px;
}
.fhx-sync-icon{
  display:grid;
  place-items:center;
  width:25px;
  height:25px;
  border-radius:8px;
  background:#0c3157;
  color:#3295ff;
}
.fhx-sync-icon i{
  animation:fhxSpin 1.3s linear infinite;
}
@keyframes fhxSpin{to{transform:rotate(360deg)}}
.fhx-compare-bars{
  position:absolute;
  right:14px;
  width:185px;
  height:29px;
  opacity:.15;
  background:repeating-linear-gradient(
    90deg,#5795da 0 3px,transparent 3px 7px
  );
}

/* =========================
   RED SIDE
   ========================= */
.fhx-ticket-side{
  min-width:0;
  padding:0 16px 15px;
  background:
    radial-gradient(circle at 68% 17%,rgba(255,105,128,.12),transparent 27%),
    linear-gradient(180deg,#ac0d2d 0%,#ba1234 52%,#941027 100%);
}
.fhx-ticket-side header{
  height:52px;
  margin:0 -16px 15px;
  padding:0 16px;
  display:flex;
  align-items:center;
  gap:9px;
  background:#941029;
}
.fhx-ticket-side header span{
  display:grid;
  place-items:center;
  width:31px;
  height:31px;
  border-radius:8px;
  background:rgba(255,255,255,.08);
  color:#fff;
}
.fhx-ticket-side header strong{
  color:#fff;
  font-size:13px;
  font-weight:600;
}

.fhx-side-block{
  padding-bottom:10px;
}
.fhx-ticket-side b{
  color:#fff;
  font-size:11px;
  font-weight:500;
}
.fhx-side-row{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:13px;
  padding:10px 0;
  border-top:1px solid rgba(255,255,255,.14);
}
.fhx-status{
  padding:10px 0;
  border-top:1px solid rgba(255,255,255,.14);
}
.fhx-status b{
  display:flex;
  align-items:center;
  gap:6px;
  font-size:9px;
}
.fhx-status b i{
  width:6px;
  height:6px;
  border-radius:50%;
  background:#ff826d;
}
.fhx-tags{
  display:flex;
  gap:5px;
  flex-wrap:wrap;
  margin-top:9px;
}
.fhx-tags span{
  padding:5px 7px;
  border:1px solid rgba(255,255,255,.15);
  border-radius:999px;
  color:#e8b6c0;
  font-size:6px;
}
.fhx-barcode{
  height:37px;
  margin-top:26px;
  opacity:.62;
  background:repeating-linear-gradient(
    90deg,#fff 0 2px,transparent 2px 5px,#fff 5px 7px,transparent 7px 10px
  );
}

/* =========================
   FOOTER STATUS
   ========================= */
.fhx-footer-status{
  width:max-content;
  max-width:90vw;
  min-height:31px;
  margin:14px auto 0;
  padding:0 14px;
  display:flex;
  align-items:center;
  justify-content:center;
  gap:8px;
  border:1px solid rgba(117,151,195,.18);
  border-radius:999px;
  background:rgba(5,19,36,.22);
  color:#ccd7e5;
  font-size:9px;
}
.fhx-footer-status i{
  width:6px;
  height:6px;
  border-radius:50%;
  background:#3b91ff;
}

/* =========================
   MOBILE
   ========================= */
@media(max-width:760px){
  #tnbSearchOverlay.fhx-loader{
    align-items:flex-start!important;
    padding-top:24px!important;
  }
  .fhx-heading h2{font-size:22px!important}
  .fhx-progress{width:min(450px,82vw)}
  .fhx-ticket{
    grid-template-columns:1fr;
    width:min(610px,94vw);
    height:auto;
  }
  .fhx-ticket-side{
    min-height:260px;
  }
  .fhx-route{
    grid-template-columns:1fr;
    text-align:center;
  }
  .fhx-airport,
  .fhx-destination{
    justify-content:center;
    text-align:center;
  }
  .fhx-route-center{
    width:220px;
    margin:0 auto;
  }
}
</style>


<style id="fhx-loader-v15-progress-sync">
/* =========================================================
   V15 — Plane + blue progress line move together
   ========================================================= */

/* Base track */
#tnbSearchOverlay.fhx-loader .fhx-progress{
    position:relative!important;
    overflow:visible!important;
    background:#66778f!important;
}

/* The blue line now grows/shrinks WITH the plane */
#tnbSearchOverlay.fhx-loader .fhx-progress-fill{
    position:absolute!important;
    left:0!important;
    top:0!important;
    bottom:0!important;
    width:3%!important;
    border-radius:999px!important;
    background:linear-gradient(90deg,#347aff,#5aa8ff)!important;
    box-shadow:0 0 12px rgba(75,151,255,.34)!important;
    animation:fhxProgressFillSync 2.4s ease-in-out infinite alternate!important;
}

/* Plane uses exactly the same timing */
#tnbSearchOverlay.fhx-loader .fhx-top-plane{
    position:absolute!important;
    top:50%!important;
    left:3%!important;
    right:auto!important;
    margin:0!important;
    transform:translate(-50%,-50%)!important;
    animation:fhxTopPlaneSync 2.4s ease-in-out infinite alternate!important;
    will-change:left!important;
}

@keyframes fhxProgressFillSync{
    from{width:3%}
    to{width:97%}
}
@keyframes fhxTopPlaneSync{
    from{left:3%}
    to{left:97%}
}

/* Keep center route plane moving too */
#tnbSearchOverlay.fhx-loader .fhx-route-plane{
    animation:fhxRoutePlaneSync 2.25s ease-in-out infinite alternate!important;
}
@keyframes fhxRoutePlaneSync{
    from{left:12%}
    to{left:88%}
}
</style>


<style id="fh-footer-brand-alignment-final">
/* Footer first-column polish only */
@media (min-width:768px){
  .fh-footer .fh-footer-brand{
    text-align:left !important;
  }
  .fh-footer .fh-footer-brand > img{
    display:block !important;
    width:255px !important;
    max-width:100% !important;
    height:auto !important;
    margin:0 0 24px 0 !important;
    margin-left:0 !important;
    margin-right:auto !important;
    object-fit:contain !important;
    object-position:left center !important;
  }
}
@media (max-width:767px){
  .fh-footer .fh-footer-brand > img{
    width:215px !important;
    max-width:100% !important;
    height:auto !important;
    margin:0 0 20px 0 !important;
    margin-left:0 !important;
    margin-right:auto !important;
    object-position:left center !important;
  }
}
</style>



<style id="fh-header-brand-alignment-final">
/* FINAL HEADER BRAND ALIGNMENT
   Match the header's horizontal frame to the footer instead of Bootstrap's
   narrower centered .container. */
.fh-reference-header .fh-reference-header-inner{
    width:min(1500px,calc(100% - 48px)) !important;
    max-width:none !important;
    margin-left:auto !important;
    margin-right:auto !important;
    padding-left:0 !important;
    padding-right:0 !important;
}

.fh-reference-header .fh-reference-brand{
    display:flex !important;
    align-items:center !important;
    justify-content:flex-start !important;
    flex:0 0 auto !important;
    margin:0 !important;
    padding:0 !important;
}

.fh-reference-header .fh-reference-brand > img{
    display:block !important;
    width:230px !important;
    max-width:100% !important;
    height:auto !important;
    margin:0 !important;
    padding:0 !important;
    object-fit:contain !important;
    object-position:left center !important;
}

@media(max-width:760px){
    .fh-reference-header .fh-reference-header-inner{
        width:calc(100% - 28px) !important;
    }

    .fh-reference-header .fh-reference-brand > img{
        width:215px !important;
    }
}
</style>


<style id="fh-global-whatsapp-cta-final">
.fh-reference-actions .fh-reference-bookings{
    display:inline-flex !important;
    align-items:center !important;
    justify-content:center !important;
    gap:8px !important;
}
.fh-reference-actions .fh-reference-bookings i{
    font-size:16px !important;
}
/* Trust P1 (25 Oct): header WhatsApp was the same solid red as the "Search Flights" button and competed with it.
   Now a secondary WhatsApp-green outline button: still visible, but the red search CTA stays the main action. */
html body .fh-reference-actions .fh-reference-bookings{
    background:#ffffff !important;
    color:#0f7a3d !important;
    border:1.5px solid #25d366 !important;
    box-shadow:none !important;
}
html body .fh-reference-actions .fh-reference-bookings:hover,
html body .fh-reference-actions .fh-reference-bookings:focus-visible{
    background:#eafaf1 !important;
    color:#0b6131 !important;
}
html body .fh-reference-actions .fh-reference-bookings i,
html body .fh-reference-actions .fh-reference-bookings span{
    color:inherit !important;
}
/* Mobile (25 Oct): top offer bar messages were 21-61px wider than a 400px screen and were cut at the edge
   (white-space:nowrap, no ellipsis). Now each message stays 12px inside the screen and ends with "…". */
@media (max-width:480px){
    html body .fh-offer-rotator .fh-offer-slide{max-width:calc(100% - 24px) !important;min-width:0 !important}
    html body .fh-offer-rotator .fh-offer-slide > span:not(.fh-offer-icon){min-width:0 !important;overflow:hidden !important;text-overflow:ellipsis !important;white-space:nowrap !important}
}
/* Mobile header (26 Oct): at 320-400px the WhatsApp button and menu button were pushed off-screen (fixed grid
   columns 205px/71px), menu button was only 26px wide, and the trust-bar phone button was cut.
   Console-tested on live page: nothing off-screen, no overlap, header height unchanged (68px). */
@media (max-width:480px){
    html body #fhHeader .fh-reference-header-inner{grid-template-columns:minmax(0,1fr) auto !important}
    html body #fhHeader .fh-reference-brand{min-width:0 !important;overflow:hidden !important}
    html body #fhHeader .fh-reference-brand img{width:auto !important;max-width:100% !important;height:auto !important;max-height:50px !important}
    html body #fhHeader .fh-reference-actions{gap:6px !important;justify-self:end !important}
    html body #fhHeader .fh-reference-actions .fh-reference-bookings span{display:none !important}
    html body #fhHeader .fh-reference-actions .fh-reference-bookings{width:44px !important;min-width:44px !important;height:44px !important;padding:0 !important;justify-content:center !important;border-radius:50% !important}
    html body #fhHeader .fh-reference-mobile-toggle{min-width:44px !important;min-height:44px !important;display:inline-flex !important;align-items:center !important;justify-content:center !important}
    html body .fh-reference-trust-left,
    html body .fh-reference-trust-right > span{display:none !important}
    html body .fh-reference-trust-inner{justify-content:center !important}
    html body .fh-reference-trust-right{margin:0 auto !important}
}
/* Tablet header (26 Oct): at 481-1023px the WhatsApp + menu buttons sat left-aligned inside a wide actions column,
   overlapping the logo by 17px at 768px; menu button was 42px. Console-tested at 768px: no overlap, buttons at right edge. */
@media (min-width:481px) and (max-width:1023px){
    html body #fhHeader .fh-reference-actions{justify-content:flex-end !important}
    html body #fhHeader .fh-reference-mobile-toggle{min-width:44px !important;min-height:44px !important}
}
</style>

</head>

<body class="tnb-modern">
  <div id="preloader">
    <div class="preloader"><span></span><span></span></div>
  </div>

  <div id="tnbFareTimer" class="tnb-fare-timer">Fare session expires in <strong id="tnbFareClock">20:00</strong> · Complete booking before the fare is revalidated.</div>

  <div id="main-wrapper">
    {{-- FaresHunt Reference Header V1: presentation only; backend/search routes untouched --}}
    <div class="fh-offer-rotator theme-navy" id="fhOfferRotator" aria-live="polite">
      <div class="fh-offer-rotator-inner container">
        <div class="fh-offer-slide is-active" data-theme="navy">
          <span class="fh-offer-icon">♡</span>
          <span><strong>Secure Booking</strong> — Your card is never charged without expert confirmation</span>
        </div>
        <div class="fh-offer-slide" data-theme="red">
          <span class="fh-offer-icon">🏷</span>
          <span><strong>✈ Business Class Sale</strong> — Save up to 40% on select routes</span>
          <a href="{{ route('all_offers') }}">View Deals <i class="fa-solid fa-chevron-right"></i></a>
        </div>
        <div class="fh-offer-slide" data-theme="navy">
          <span class="fh-offer-icon">☎</span>
          <span><strong>24/7 Travel Experts</strong><?php if ($tnbTollFree): ?> — Call {{ $tnbTollFree }} for instant assistance<?php endif; ?></span>
          <?php if ($tnbTollFree): ?><a href="tel:{{ $tnbTollFree }}">Call Now <i class="fa-solid fa-chevron-right"></i></a><?php endif; ?>
        </div>
        <div class="fh-offer-slide" data-theme="navy">
          <span class="fh-offer-icon">🌴</span>
          <span><strong>Holiday Offers</strong> — Book your getaway before seats sell out</span>
          <a href="{{ route('all_offers') }}">Explore <i class="fa-solid fa-chevron-right"></i></a>
        </div>

        <div class="fh-offer-dots" aria-hidden="true">
          <span class="is-active"></span><span></span><span></span><span></span>
        </div>
        <button class="fh-offer-close" id="fhOfferClose" type="button" aria-label="Close offer banner"><i class="fa-solid fa-xmark"></i></button>
      </div>
    </div>

    <div class="fh-reference-trust">
      <div class="container fh-reference-trust-inner">
        <div class="fh-reference-trust-left">
          <span><i class="fa-regular fa-shield"></i> Secure &amp; Encrypted Booking</span>
          <span><i class="fa-regular fa-star"></i> 15+ Years Trusted Experience</span>
          <span><i class="fa-solid fa-globe"></i> USA Travel Specialists</span>
        </div>
        <div class="fh-reference-trust-right">
          <span><i class="fa-regular fa-clock"></i> 24/7 Travel Experts Available</span>
          <?php if ($tnbTollFree): ?><a href="tel:{{ $tnbTollFree }}"><i class="fa-solid fa-phone"></i>{{ $tnbTollFree }}</a><?php endif; ?>
        </div>
      </div>
    </div>

    <header class="fh-reference-header" id="fhHeader">
      <div class="container fh-reference-header-inner">
        <a class="fh-reference-brand" href="/" aria-label="FaresHunt home">
          <img src="/assets/img/fareshunt-logo-transparent-360-q90.webp" alt="FaresHunt">
        </a>

        <nav class="fh-reference-nav" aria-label="Primary navigation">
          <div class="fh-reference-flights" id="fhFlightsMenu">
            <button class="fh-reference-nav-link flights" id="fhFlightsToggle" type="button" aria-expanded="false" aria-controls="fhFlightsMega">
              <i class="fa-solid fa-plane"></i><span>{{ $cmsv('site.nav.flights','Flights') }}</span><i class="fa-solid fa-chevron-down arrow"></i>
            </button>
            <div class="fh-reference-mega" id="fhFlightsMega">
              <div class="fh-reference-mega-grid">
                <div class="fh-reference-mega-col">
                  <h3>FLIGHT TYPES</h3>
                  <a href="/#main-content"><strong>One Way Flights</strong><small>Best one-way fares</small></a>
                  <a href="/#main-content"><strong>Round Trip Flights</strong><small>Save on return trips</small></a>
                  <a href="/#main-content"><strong>Multi-City Flights</strong><small>Visit multiple destinations</small></a>
                </div>
                <div class="fh-reference-mega-col">
                  <h3>CABIN CLASSES</h3>
                  <a href="/#main-content"><strong>Economy Class</strong><small>Budget-friendly travel</small></a>
                  <a href="/#main-content"><strong>Premium Economy</strong><small>Extra comfort &amp; space</small></a>
                  <a href="/#main-content"><strong>Business Class</strong><small>Lie-flat beds &amp; lounge access</small></a>
                  <a href="/#main-content"><strong>First Class</strong><small>Ultimate luxury experience</small></a>
                </div>
                <div class="fh-reference-mega-col">
                  <h3>POPULAR ROUTES</h3>
                  <a href="/#main-content"><strong>New York → London</strong><small>From $489</small></a>
                  <a href="/#main-content"><strong>Los Angeles → Tokyo</strong><small>From $649</small></a>
                  <a href="/#main-content"><strong>Chicago → Dubai</strong><small>From $729</small></a>
                  <a href="/#main-content"><strong>Houston → Mumbai</strong><small>From $599</small></a>
                </div>
              </div>
              <div class="fh-reference-mega-help">
                <span><i class="fa-solid fa-headset"></i> Need help? Our experts are available 24/7</span>
                <?php if ($tnbTollFree): ?><a href="tel:{{ $tnbTollFree }}"><i class="fa-solid fa-phone"></i>{{ $tnbTollFree }}</a><?php endif; ?>
              </div>
            </div>
          </div>

          <span class="fh-reference-nav-link disabled"><i class="fa-regular fa-building"></i><span>Hotels</span><em>Soon</em></span>
          <span class="fh-reference-nav-link disabled"><i class="fa-solid fa-car-side"></i><span>Cars</span><em>Soon</em></span>
          <span class="fh-reference-nav-link disabled"><i class="fa-solid fa-ship"></i><span>Cruises</span><em>Soon</em></span>
          <a class="fh-reference-nav-link" href="{{ route('all_offers') }}"><i class="fa-regular fa-star"></i><span>Deals</span></a>
        </nav>

        <div class="fh-reference-actions">
          <?php if ($tnbTollFree): ?><a class="fh-reference-call" href="tel:{{ $tnbTollFree }}"><i class="fa-solid fa-phone"></i><span>{{ $tnbTollFree }}</span></a><?php endif; ?>
          <?php if ($tnbWhatsAppDigits): ?>
          <a class="fh-reference-bookings"
             href="https://wa.me/{{ $tnbWhatsAppDigits }}"
             target="_blank"
             rel="noopener"
             aria-label="Chat with Fares Hunt on WhatsApp">
            <i class="fa-brands fa-whatsapp"></i>
            <span>WhatsApp</span>
          </a>
          <?php endif; ?>
          <button class="fh-reference-mobile-toggle" id="fhMenuToggle" type="button" aria-label="Open navigation menu" aria-expanded="false" aria-controls="fhMobileMenu"><i class="fa-solid fa-bars" aria-hidden="true"></i></button>
        </div>
      </div>

      <div class="fh-mobile-menu fh-reference-mobile-menu" id="fhMobileMenu" hidden>
        <a href="{{ route('search_flight') }}">Flights</a>
        <a href="{{ route('all_offers') }}">Deals</a>
        <a href="{{ route('all_destinations') }}">Destinations</a>
        <a href="{{ route('all_blogs') }}">Travel Guide</a>
        <a href="{{ route('contact_us') }}">Contact</a>
        <?php if ($tnbTollFree): ?><a class="call" href="tel:{{ $tnbTollFree }}"><i class="fa-solid fa-phone"></i>{{ $tnbTollFree }}</a><?php endif; ?>
        <?php if ($tnbWhatsAppDigits): ?><a class="call" href="https://wa.me/{{ $tnbWhatsAppDigits }}" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp"></i>WhatsApp {{ $tnbWhatsApp }}</a><?php endif; ?>
      </div>
    </header>

    @yield('mid-content')


    <style id="fh-footer-premium-final-v1">
    /* =========================================================
       FARESHUNT FOOTER — PREMIUM FINAL
       Existing routes/settings/contact/payment data preserved.
       Help Center removed from footer.
       "Terms of Use" is displayed as "Terms & Conditions".
       ========================================================= */

    .fh-footer{
        --fhf-bg:#07172b;
        --fhf-bg2:#0a1d35;
        --fhf-line:rgba(255,255,255,.12);
        --fhf-text:#ffffff;
        --fhf-muted:#aab7c9;
        --fhf-gold:#f2ad24;
        --fhf-red:#d91f43;

        margin:0!important;
        background:
            radial-gradient(circle at 85% 12%,rgba(40,91,148,.16),transparent 28%),
            linear-gradient(180deg,var(--fhf-bg2),var(--fhf-bg))!important;
        color:var(--fhf-text)!important;
        font-family:inherit!important;
    }

    /* TRUST STRIP */
    .fh-footer .fh-footer-trust{
        border-top:1px solid rgba(242,173,36,.62)!important;
        border-bottom:1px solid var(--fhf-line)!important;
        background:rgba(3,16,31,.28)!important;
    }

    .fh-footer .fh-footer-trust>.container{
        width:min(1500px,calc(100% - 48px))!important;
        max-width:none!important;
        margin:0 auto!important;
        padding:18px 0!important;

        display:grid!important;
        grid-template-columns:repeat(4,minmax(0,1fr))!important;
        gap:0!important;
    }

    .fh-footer .fh-footer-trust>.container>div{
        position:relative!important;
        display:flex!important;
        align-items:center!important;
        gap:14px!important;
        min-width:0!important;
        padding:0 22px!important;
    }

    .fh-footer .fh-footer-trust>.container>div:not(:last-child)::after{
        content:""!important;
        position:absolute!important;
        right:0!important;
        top:5px!important;
        bottom:5px!important;
        width:1px!important;
        background:rgba(255,255,255,.16)!important;
    }

    .fh-footer .fh-footer-trust span{
        display:grid!important;
        place-items:center!important;
        flex:0 0 46px!important;
        width:46px!important;
        height:46px!important;
        border:1px solid rgba(242,173,36,.48)!important;
        border-radius:50%!important;
        background:rgba(242,173,36,.05)!important;
        color:var(--fhf-gold)!important;
        font-size:18px!important;
    }

    .fh-footer .fh-footer-trust p{
        margin:0!important;
        min-width:0!important;
    }

    .fh-footer .fh-footer-trust b{
        display:block!important;
        margin:0 0 4px!important;
        color:#fff!important;
        font-size:13px!important;
        line-height:1.2!important;
        font-weight:650!important;
    }

    .fh-footer .fh-footer-trust small{
        display:block!important;
        color:var(--fhf-muted)!important;
        font-size:11px!important;
        line-height:1.35!important;
    }

    /* MAIN DIRECTORY */
    .fh-footer .fh-footer-main{
        width:min(1500px,calc(100% - 48px))!important;
        max-width:none!important;
        margin:0 auto!important;
        padding:42px 0 36px!important;

        display:grid!important;
        grid-template-columns:minmax(300px,1.25fr) .72fr .72fr .82fr 1.08fr!important;
        gap:46px!important;
        align-items:start!important;
    }

    .fh-footer .fh-footer-brand{
        min-width:0!important;
        padding-right:34px!important;
        border-right:1px solid var(--fhf-line)!important;
            transform:translateX(-30px)!important;
    }

    .fh-footer .fh-footer-brand>img{
        display:block!important;
        width:255px!important;
        max-width:100%!important;
        height:auto!important;
        margin:0 0 18px!important;
        padding:0!important;
        border:0!important;
        background:transparent!important;
        box-shadow:none!important;
        filter:brightness(0) invert(1)!important;
    }

    .fh-footer .fh-footer-brand>p{
        max-width:410px!important;
        margin:0 0 18px!important;
        color:#d2dae5!important;
        font-size:12.5px!important;
        line-height:1.62!important;
    }

    .fh-footer .fh-footer-contact{
        display:grid!important;
        gap:9px!important;
        margin:0 0 20px!important;
    }

    .fh-footer .fh-footer-contact a,
    .fh-footer .fh-footer-contact span{
        display:flex!important;
        align-items:flex-start!important;
        gap:10px!important;
        color:#e7edf5!important;
        font-size:12px!important;
        line-height:1.45!important;
        text-decoration:none!important;
    }

    .fh-footer .fh-footer-contact i{
        flex:0 0 14px!important;
        width:14px!important;
        margin-top:2px!important;
        color:var(--fhf-gold)!important;
        text-align:center!important;
    }

    .fh-footer .fh-social{
        display:flex!important;
        flex-wrap:wrap!important;
        gap:9px!important;
    }

    .fh-footer .fh-social a{
        display:grid!important;
        place-items:center!important;
        width:38px!important;
        height:38px!important;
        border:1px solid rgba(255,255,255,.14)!important;
        border-radius:10px!important;
        background:rgba(255,255,255,.07)!important;
        color:#fff!important;
        font-size:15px!important;
        text-decoration:none!important;
        transition:transform .18s ease,border-color .18s ease,background .18s ease!important;
    }

    .fh-footer .fh-social a:hover{
        transform:translateY(-2px)!important;
        border-color:rgba(242,173,36,.55)!important;
        background:rgba(242,173,36,.10)!important;
        color:var(--fhf-gold)!important;
    }

    .fh-footer .fh-footer-col{
        min-width:0!important;
    }

    .fh-footer .fh-footer-col h3{
        position:relative!important;
        margin:0 0 20px!important;
        padding:0 0 11px!important;
        color:#fff!important;
        font-size:14px!important;
        line-height:1!important;
        font-weight:650!important;
        text-transform:uppercase!important;
        letter-spacing:.045em!important;
    }

    .fh-footer .fh-footer-col h3::after{
        content:""!important;
        position:absolute!important;
        left:0!important;
        bottom:0!important;
        width:36px!important;
        height:2px!important;
        border-radius:999px!important;
        background:var(--fhf-gold)!important;
    }

    .fh-footer .fh-footer-col ul{
        list-style:none!important;
        margin:0!important;
        padding:0!important;
    }

    .fh-footer .fh-footer-col li{
        margin:0 0 12px!important;
        padding:0!important;
    }

    .fh-footer .fh-footer-col a{
        position:relative!important;
        display:inline-flex!important;
        align-items:center!important;
        gap:8px!important;
        color:#d8e0eb!important;
        font-size:12px!important;
        line-height:1.35!important;
        font-weight:400!important;
        text-decoration:none!important;
        transition:color .16s ease,transform .16s ease!important;
    }

    .fh-footer .fh-footer-col a::before{
        content:"›"!important;
        color:var(--fhf-gold)!important;
        font-size:17px!important;
        line-height:.8!important;
        font-weight:700!important;
    }

    .fh-footer .fh-footer-col a:hover{
        color:#fff!important;
        transform:translateX(2px)!important;
    }

    /* SECURE PAYMENT ROW */
    .fh-footer .fh-footer-payments{
        border-top:1px solid var(--fhf-line)!important;
        border-bottom:1px solid var(--fhf-line)!important;
        background:rgba(255,255,255,.018)!important;
    }

    .fh-footer .fh-footer-payments>.container{
        width:min(1500px,calc(100% - 48px))!important;
        max-width:none!important;
        margin:0 auto!important;
        padding:17px 0!important;

        display:flex!important;
        align-items:center!important;
        justify-content:space-between!important;
        gap:24px!important;
    }

    .fh-footer .fh-payment-copy{
        display:flex!important;
        align-items:center!important;
        gap:13px!important;
        color:#fff!important;
    }

    .fh-footer .fh-payment-copy>span{
        display:grid!important;
        place-items:center!important;
        width:42px!important;
        height:42px!important;
        border:1px solid rgba(242,173,36,.48)!important;
        border-radius:50%!important;
        color:var(--fhf-gold)!important;
        font-size:16px!important;
    }

    .fh-footer .fh-payment-copy b{
        display:block!important;
        margin-bottom:3px!important;
        font-size:13px!important;
        font-weight:650!important;
    }

    .fh-footer .fh-payment-copy small{
        display:block!important;
        color:var(--fhf-muted)!important;
        font-size:10.5px!important;
    }

    .fh-footer .fh-footer-payments img{
        display:block!important;
        width:auto!important;
        max-width:min(520px,46vw)!important;
        max-height:34px!important;
        object-fit:contain!important;
    }

    /* DISCLAIMER */
    .fh-footer .fh-footer-disclaimer{
        border-bottom:1px solid var(--fhf-line)!important;
    }

    .fh-footer .fh-footer-disclaimer>.container{
        width:min(1500px,calc(100% - 48px))!important;
        max-width:none!important;
        margin:0 auto!important;
        padding:16px 0!important;

        display:flex!important;
        align-items:flex-start!important;
        gap:13px!important;
    }

    .fh-footer .fh-footer-disclaimer> .container > i{
        margin-top:2px!important;
        color:var(--fhf-gold)!important;
        font-size:18px!important;
    }

    .fh-footer .fh-footer-disclaimer p{
        margin:0!important;
        color:#aebacc!important;
        font-size:10.5px!important;
        line-height:1.55!important;
    }

    .fh-footer .fh-footer-disclaimer strong{
        color:var(--fhf-gold)!important;
        font-weight:650!important;
    }

    /* BOTTOM BAR */
    .fh-footer .fh-footer-bottom{
        margin:0!important;
        padding:0!important;
        border:0!important;
        background:rgba(1,12,25,.32)!important;
    }

    .fh-footer .fh-footer-bottom>.container{
        width:min(1500px,calc(100% - 48px))!important;
        max-width:none!important;
        min-height:58px!important;
        margin:0 auto!important;
        padding:0!important;

        display:flex!important;
        align-items:center!important;
        justify-content:space-between!important;
        gap:24px!important;
    }

    .fh-footer .fh-footer-bottom p{
        margin:0!important;
        color:#8fa0b8!important;
        font-size:10.5px!important;
    }

    .fh-footer .fh-footer-legal-bottom{
        display:flex!important;
        align-items:center!important;
        justify-content:flex-end!important;
        flex-wrap:wrap!important;
        gap:9px 15px!important;
    }

    .fh-footer .fh-footer-legal-bottom a{
        color:#bdc8d7!important;
        font-size:10.5px!important;
        text-decoration:none!important;
    }

    .fh-footer .fh-footer-legal-bottom a:hover{
        color:#fff!important;
    }

    .fh-footer .fh-footer-legal-bottom i{
        width:4px!important;
        height:4px!important;
        border-radius:50%!important;
        background:var(--fhf-gold)!important;
    }

    @media(max-width:1100px){
        .fh-footer .fh-footer-brand{
            transform:none!important;
        }

        .fh-footer .fh-footer-trust>.container{
            grid-template-columns:repeat(2,minmax(0,1fr))!important;
            row-gap:22px!important;
        }

        .fh-footer .fh-footer-trust>.container>div:nth-child(2)::after{
            display:none!important;
        }

        .fh-footer .fh-footer-main{
            grid-template-columns:repeat(4,minmax(0,1fr))!important;
        }

        .fh-footer .fh-footer-brand{
            grid-column:1/-1!important;
            padding:0 0 26px!important;
            border-right:0!important;
            border-bottom:1px solid var(--fhf-line)!important;
        }
    }

    @media(max-width:760px){
        .fh-footer .fh-footer-trust>.container,
        .fh-footer .fh-footer-main,
        .fh-footer .fh-footer-payments>.container,
        .fh-footer .fh-footer-disclaimer>.container,
        .fh-footer .fh-footer-bottom>.container{
            width:calc(100% - 28px)!important;
        }

        .fh-footer .fh-footer-trust>.container{
            grid-template-columns:1fr!important;
            padding:16px 0!important;
            row-gap:0!important;
        }

        .fh-footer .fh-footer-trust>.container>div{
            padding:13px 2px!important;
        }

        .fh-footer .fh-footer-trust>.container>div::after{
            display:none!important;
        }

        .fh-footer .fh-footer-main{
            padding:32px 0 28px!important;
            grid-template-columns:1fr 1fr!important;
            gap:30px 24px!important;
        }

        .fh-footer .fh-footer-brand{
            grid-column:1/-1!important;
        }

        .fh-footer .fh-footer-brand>img{
            width:225px!important;
        }

        .fh-footer .fh-footer-col:last-child{
            grid-column:1/-1!important;
        }

        .fh-footer .fh-footer-payments>.container{
            align-items:flex-start!important;
            flex-direction:column!important;
        }

        .fh-footer .fh-footer-payments img{
            max-width:100%!important;
        }

        .fh-footer .fh-footer-bottom>.container{
            min-height:0!important;
            padding:16px 0!important;
            align-items:flex-start!important;
            flex-direction:column!important;
            gap:11px!important;
        }

        .fh-footer .fh-footer-legal-bottom{
            justify-content:flex-start!important;
        }
    }

    @media(max-width:480px){
        .fh-footer .fh-footer-main{
            grid-template-columns:1fr!important;
        }

        .fh-footer .fh-footer-col:last-child{
            grid-column:auto!important;
        }
    }
    </style>

    @php
        /*
         * FINAL footer links.
         *
         * Do NOT parse getContentBasic()/getContentLegal() HTML here.
         * Parsing those generated <li> strings caused href values to swallow
         * neighbouring HTML (e.g. Shipping Policy markup inside About Us URL).
         *
         * Pages/routes remain intact. This only controls footer navigation.
         */
        $fhCompanySupportLinks = [];

        for ($i = 1; $i <= 9; $i++) {
            $label = trim((string) $cmsv("site.footer.col4.l{$i}", ''));
            $href  = trim((string) $cmsv("site.footer.col4.u{$i}", ''));

            if ($label !== '' && $href !== '') {
                $fhCompanySupportLinks[] = [
                    'label' => $label,
                    'url'   => $href,
                ];
            }
        }

        // Ensure the information row never looks empty/broken.
        $fhBottomLegalLinks = [];

        for ($i = 1; $i <= 5; $i++) {
            $label = trim((string) $cmsv("site.footer.bottom.l{$i}", ''));
            $href  = trim((string) $cmsv("site.footer.bottom.u{$i}", ''));

            if ($label !== '' && $href !== '') {
                $fhBottomLegalLinks[] = [
                    'label' => $label,
                    'url'   => $href,
                ];
            }
        }

        $fhFooterDisclaimerText = trim((string) $cmsv('site.footer.disclaimer',''));

        if ($fhFooterDisclaimerText === '') {
            $fhFooterDisclaimerText =
                'FaresHunt provides flight search and travel booking assistance through available travel suppliers. '
                . 'Fares, schedules, availability and applicable rules may change until a booking is confirmed. '
                . 'Please review the applicable fare rules and policies before completing your booking.';
        }
    @endphp

    <footer class="fh-footer">

      <?php if ($cmsv('site.footer.trust.enabled','1')==='1'): ?>
      <div class="fh-footer-trust">
        <div class="container">
          @foreach([
            ['fa-tags','site.footer.trust.1.title','site.footer.trust.1.sub','Best Fare Guarantee','Competitive fares and live availability'],
            ['fa-shield-halved','site.footer.trust.2.title','site.footer.trust.2.sub','Secure Booking','Protected booking experience'],
            ['fa-headset','site.footer.trust.3.title','site.footer.trust.3.sub','24/7 Support','Travel assistance when you need it'],
            ['fa-clock','site.footer.trust.4.title','site.footer.trust.4.sub','Easy & Quick Booking','Simple flight search and booking']
          ] as $ft)
          <div>
            <span><i class="fa-solid {{ $ft[0] }}"></i></span>
            <p>
              <b>{{ $cmsv($ft[1],$ft[3]) }}</b>
              <small>{{ $cmsv($ft[2],$ft[4]) }}</small>
            </p>
          </div>
          @endforeach
        </div>
      </div>
      <?php endif; ?>

      <div class="container fh-footer-main">

        <div class="fh-footer-brand">
          <img src="/assets/img/fareshunt-logo-transparent-360-q90.webp" alt="FaresHunt">

          <p>
            Premium flight search and expert travel support for domestic and international journeys.
          </p>

          <div class="fh-footer-contact">
            <?php if ($tnbTollFree): ?>
              <a href="tel:{{ $tnbTollFree }}">
                <i class="fa-solid fa-phone"></i>
                {{ $tnbTollFree }}
              </a>
            <?php endif; ?>

            <?php if ($tnbWhatsAppDigits): ?>
              <a href="https://wa.me/{{ $tnbWhatsAppDigits }}" target="_blank" rel="noopener">
                <i class="fa-brands fa-whatsapp"></i>
                WhatsApp {{ $tnbWhatsApp }}
              </a>
            <?php endif; ?>

            <?php if ($setting['content_emailid']): ?>
              <a href="mailto:{{ $setting['content_emailid'] }}">
                <i class="fa-regular fa-envelope"></i>
                {{ $setting['content_emailid'] }}
              </a>
            <?php endif; ?>

            <?php if ($setting['footer_address']): ?>
              <span>
                <i class="fa-solid fa-location-dot"></i>
                {{ $setting['footer_address'] }}
              </span>
            <?php endif; ?>
          </div>

          <div class="fh-social">
            <?php if ($setting['social_facebook']): ?><a href="{{ $setting['social_facebook'] }}" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a><?php endif; ?>
            <?php if ($setting['social_instagram']): ?><a href="{{ $setting['social_instagram'] }}" target="_blank" rel="noopener" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a><?php endif; ?>
            <?php if ($setting['social_youtube']): ?><a href="{{ $setting['social_youtube'] }}" target="_blank" rel="noopener" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a><?php endif; ?>
            <?php if ($setting['social_twitter']): ?><a href="{{ $setting['social_twitter'] }}" target="_blank" rel="noopener" aria-label="X"><i class="fa-brands fa-x-twitter"></i></a><?php endif; ?>
            <?php if ($setting['social_linkedin']): ?><a href="{{ $setting['social_linkedin'] }}" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a><?php endif; ?>
          </div>
        </div>

        <div class="fh-footer-col">
          <h3>Flights</h3>
          <ul>
            <li><a href="/#main-content">Round Trip Flights</a></li>
            <li><a href="/#main-content">One Way Flights</a></li>
            <li><a href="/#main-content">Economy Class</a></li>
            <li><a href="/#main-content">Premium Economy</a></li>
            <li><a href="/#main-content">Business Class</a></li>
            <li><a href="/#main-content">First Class</a></li>
          </ul>
        </div>

        <div class="fh-footer-col">
          <h3>Explore</h3>
          <ul>
            <li><a href="{{ route('all_offers') }}">Flight Deals</a></li>
            <li><a href="{{ route('all_destinations') }}">Destinations</a></li>
            <li><a href="{{ route('all_blogs') }}">Travel Guides</a></li>
            <li><a href="{{ route('search_flight') }}">Search Flights</a></li>
          </ul>
        </div>

        <div class="fh-footer-col">
          <h3>Airlines</h3>
          <ul>
            <li><a href="{{ route('airline_detail', ['slug'=>'united-airlines']) }}">United Airlines</a></li>
            <li><a href="{{ route('airline_detail', ['slug'=>'american-airlines']) }}">American Airlines</a></li>
            <li><a href="{{ route('airline_detail', ['slug'=>'delta-air-lines']) }}">Delta Air Lines</a></li>
            <li><a href="{{ route('airline_detail', ['slug'=>'british-airways']) }}">British Airways</a></li>
            <li><a href="{{ route('airline_detail', ['slug'=>'emirates']) }}">Emirates</a></li>
            <li><a href="{{ route('airline_detail', ['slug'=>'qatar-airways']) }}">Qatar Airways</a></li>
          </ul>
        </div>

        <div class="fh-footer-col">
          <h3>{{ $cmsv('site.footer.col4.title','Company & Support') }}</h3>
          <ul>
            @foreach($fhCompanySupportLinks as $fhFooterLink)
              <li><a href="{{ $fhFooterLink['url'] }}">{{ $fhFooterLink['label'] }}</a></li>
            @endforeach
          </ul>
        </div>

      </div>

      <div class="fh-footer-payments">
        <div class="container">
          <div class="fh-payment-copy">
            <span><i class="fa-solid fa-lock"></i></span>
            <div>
              <b>Secure Payments</b>
              <small>Protected checkout and accepted payment methods</small>
            </div>
          </div>

          <?php if ($setting['footer_payment_image']): ?>
            <img src="{{ $setting['footer_payment_image'] }}" alt="Accepted payment methods">
          <?php endif; ?>
        </div>
      </div>

      <div class="fh-footer-disclaimer">
        <div class="container">
          <i class="fa-solid fa-shield-halved"></i>
          <p>
            <strong>{{ $cmsv('site.footer.disclaimer_title','Important information') }}:</strong>
            {{ $fhFooterDisclaimerText }}
          </p>
        </div>
      </div>

      <div class="fh-footer-bottom">
        <div class="container">

          <p>
            © {{ date('Y') }}
            {{ $cmsv('site.footer.copyright_name','FaresHunt') }}
            · {{ $cmsv('site.footer.copyright_suffix','All Rights Reserved.') }}
          </p>

          <div class="fh-footer-legal-bottom">
            @foreach($fhBottomLegalLinks as $fhBottomLegalLink)
              @if(!$loop->first)
                <i aria-hidden="true"></i>
              @endif
              <a href="{{ $fhBottomLegalLink['url'] }}">{{ $fhBottomLegalLink['label'] }}</a>
            @endforeach
          </div>

        </div>
      </div>

    </footer>
</div>


  <!-- =======================================================
       FARESHUNT SEARCH LOADER V13 — CLEAN REBUILD
       Completely isolated from the old boarding-pass CSS/JS.
       ======================================================= -->
  <div id="tnbSearchOverlay" class="fhx-loader" aria-live="polite" aria-hidden="true">
    <div class="fhx-shell">

      <div class="fhx-heading">
        <h2>Searching for the best flights for you...</h2>
        <p>This may take a few seconds while we compare live availability.</p>

        <div class="fhx-progress" aria-hidden="true">
          <span class="fhx-progress-fill"></span>
          <i class="fa-solid fa-plane fhx-top-plane"></i>
        </div>
      </div>

      <div class="fhx-ticket">

        <section class="fhx-ticket-main">
          <header class="fhx-ticket-head">
            <span class="fhx-head-icon"><i class="fa-solid fa-plane"></i></span>
            <strong>BOARDING PASS</strong>
          </header>

          <div class="fhx-passenger-grid">
            <div>
              <small>PASSENGER</small>
              <b>Guest Traveller</b>
            </div>
            <div>
              <small>DATE</small>
              <b id="fhxDate">—</b>
            </div>
            <div>
              <small>TIME</small>
              <b id="fhxTime">—</b>
            </div>
          </div>

          <div class="fhx-route">

            <div class="fhx-airport fhx-origin">
              <span class="fhx-mini-bars" aria-hidden="true"></span>
              <div>
                <small>FROM</small>
                <strong id="fhxFromCode">JFK</strong>
                <b id="fhxFromCity">New York</b>
              </div>
            </div>

            <div class="fhx-route-center">
              <span class="fhx-route-dot fhx-dot-left"></span>
              <span class="fhx-route-dash"></span>

              <i class="fa-solid fa-plane fhx-route-plane"></i>

              <span class="fhx-route-dot fhx-dot-right"></span>

              <span class="fhx-live-pill">
                LIVE SEARCH
                <i></i>
              </span>
            </div>

            <div class="fhx-airport fhx-destination">
              <div>
                <small>TO</small>
                <strong id="fhxToCode">LHR</strong>
                <b id="fhxToCity">London</b>
              </div>
            </div>

          </div>

          <div class="fhx-meta">
            <div><small>FLIGHT</small><b>LIVE <i></i></b></div>
            <div><small>TERMINAL</small><b>—</b></div>
            <div><small>GATE</small><b>—</b></div>
            <div><small>SEAT</small><b>—</b></div>
            <div><small>CLASS</small><b id="fhxCabin">ECONOMY</b></div>
          </div>

          <div class="fhx-compare">
            <span class="fhx-sync-icon"><i class="fa-solid fa-rotate"></i></span>
            <span>Comparing routes &amp; fares...</span>
            <span class="fhx-compare-bars" aria-hidden="true"></span>
          </div>
        </section>

        <aside class="fhx-ticket-side">
          <header>
            <span><i class="fa-solid fa-couch"></i></span>
            <strong id="fhxCabinSide">ECONOMY</strong>
          </header>

          <div class="fhx-side-block">
            <small>PASSENGER</small>
            <b>Guest Traveller</b>
          </div>

          <div class="fhx-side-row">
            <div>
              <small>FROM</small>
              <b id="fhxFromCodeSide">JFK</b>
            </div>
            <div>
              <small>TO</small>
              <b id="fhxToCodeSide">LHR</b>
            </div>
          </div>

          <div class="fhx-side-row">
            <div>
              <small>DATE</small>
              <b id="fhxDateSide">—</b>
            </div>
            <div>
              <small>TIME</small>
              <b id="fhxTimeSide">—</b>
            </div>
          </div>

          <div class="fhx-status">
            <small>STATUS</small>
            <b><i></i> Searching live inventory</b>
          </div>

          <div class="fhx-tags">
            <span>SEA</span>
            <span>LIVE</span>
            <span>PNR</span>
            <span>FARESHUNT</span>
          </div>

          <div class="fhx-barcode" aria-hidden="true"></div>
        </aside>

      </div>

      <div class="fhx-footer-status">
        <i></i>
        Searching live flight availability
      </div>

    </div>
  </div>

  <script src="/assets/js/jquery.min.js"></script>
  <script src="/assets/js/popper.min.js"></script>
  <script src="/assets/js/bootstrap.min.js"></script>
@unless(request()->is('/') || request()->is('flight-listing/*'))
  <script src="/assets/js/dropzone.min.js"></script>
@endunless
  <script src="/assets/js/flatpickr.js"></script>
@unless(request()->is('/'))
  <script src="/assets/js/flickity.pkgd.min.js"></script>
@endunless
@unless(request()->is('/') || request()->is('flight-listing/*'))
  <script src="/assets/js/lightbox.min.js"></script>
@endunless
@unless(request()->is('/'))
  <script src="/assets/js/rangeslider.js"></script>
@endunless
@unless(request()->is('/'))
  <script src="/assets/js/select2.min.js"></script>
@endunless
@unless(request()->is('/') || request()->is('flight-listing/*'))
  <script src="/assets/js/counterup.min.js"></script>
@endunless
@unless(request()->is('/') || request()->is('flight-listing/*'))
  <script src="/assets/js/prism.js"></script>
@endunless

  <script src="/assets/js/addadult.js"></script>
  <script src="/assets/js/custom.js"></script>
  @unless(request()->is('/'))
  <script src="/assets/js/travelnb-modern.js"></script>
  @endunless
  <script src="/assets/js/tnb-premium-v3.js"></script>
  @unless(request()->is('/'))
  <script src="/assets/js/tnb-checkout-v4.js"></script>
  @endunless
  @unless(request()->is('/'))
  <script src="/assets/js/tnb-results-v4.js"></script>
  @endunless
<script src="/assets/js/fareshunt-phase1.js"></script>
  <script src="/assets/js/fareshunt-top-reference-v1.js"></script>

  {!! $setting['footer_script'] !!}


<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.querySelector('.tnb-menu-toggle');
    const menu = document.querySelector('.tnb-mobile-menu');
    if (!toggle || !menu) return;
    toggle.addEventListener('click', function () {
        const opening = menu.hasAttribute('hidden');
        if (opening) menu.removeAttribute('hidden'); else menu.setAttribute('hidden', '');
        toggle.setAttribute('aria-expanded', opening ? 'true' : 'false');
        const icon = toggle.querySelector('i');
        if (icon) icon.className = opening ? 'fa-solid fa-xmark' : 'fa-solid fa-bars';
    });
});
</script>





<script id="fhx-loader-v14-js">
(function(){
  const overlay = document.getElementById('tnbSearchOverlay');
  if(!overlay) return;

  function airportParts(value){
    const raw = String(value || '').trim();
    const chunks = raw.split(' - ');
    return {
      code:(chunks[0] || '').trim().toUpperCase(),
      city:((chunks.slice(1).join(' - ').split(',')[0]) || '').trim()
    };
  }

  function cabinText(value){
    const map = {
      Econom:'ECONOMY',
      Peconom:'PREMIUM ECONOMY',
      Business:'BUSINESS',
      firstclass:'FIRST CLASS'
    };
    return map[value] || String(value || 'ECONOMY').replace(/_/g,' ').toUpperCase();
  }

  function dateText(value){
    if(!value) return '—';
    const d = new Date(value + 'T12:00:00');
    if(isNaN(d.getTime())) return value;

    return d.toLocaleDateString('en-US',{
      month:'2-digit',
      day:'2-digit',
      year:'numeric'
    });
  }

  function currentTime(){
    return new Date().toLocaleTimeString('en-US',{
      hour:'numeric',
      minute:'2-digit'
    });
  }

  function setText(id,value){
    const el = document.getElementById(id);
    if(el && value) el.textContent = value;
  }

  function hydrate(){
    const from = airportParts(document.getElementById('location_from')?.value);
    const to = airportParts(document.getElementById('location_to')?.value);
    const depart = document.getElementById('depature_date')?.value || '';
    const cabin = cabinText(document.getElementById('cabin_classes')?.value);

    setText('fhxFromCode',from.code || 'FROM');
    setText('fhxFromCodeSide',from.code || 'FROM');
    setText('fhxFromCity',from.city || 'Departure');

    setText('fhxToCode',to.code || 'TO');
    setText('fhxToCodeSide',to.code || 'TO');
    setText('fhxToCity',to.city || 'Arrival');

    const d = dateText(depart);
    const t = currentTime();

    setText('fhxDate',d);
    setText('fhxDateSide',d);
    setText('fhxTime',t);
    setText('fhxTimeSide',t);

    setText('fhxCabin',cabin);
    setText('fhxCabinSide',cabin);
  }

  function showLoader(){
    hydrate();

    overlay.classList.add('fhx-show');
    overlay.setAttribute('aria-hidden','false');

    document.documentElement.style.setProperty('overflow','hidden','important');
    document.body.style.setProperty('overflow','hidden','important');

    clearTimeout(window.fhxSlowSearchTimer);
    window.fhxSlowSearchTimer=setTimeout(function(){
      if(overlay.classList.contains('fhx-show')){
        const title=overlay.querySelector('h2');
        if(title) title.textContent='Still searching live fares…';
      }
    },8000);
  }

  function hideLoader(){
    clearTimeout(window.fhxSlowSearchTimer);
    overlay.classList.remove('fhx-show');
    overlay.setAttribute('aria-hidden','true');

    document.documentElement.style.removeProperty('overflow');
    document.body.style.removeProperty('overflow');
  }

  /*
   * IMPORTANT:
   * Your homepage search form is #form2, NOT #form1.
   * V13 was listening for the wrong form, which is why the loader disappeared.
   *
   * We briefly delay native submission so the browser gets a paint frame
   * and the loader becomes visible before navigation starts.
   */
  document.addEventListener('submit',function(e){
    const form = e.target;

    if(!form || form.id !== 'form2') return;
    if(form.dataset.fhxSubmitting === '1') return;

    /* FH search validation: invalid search must stop here, before the loader and forced submit. */
    if(typeof window.fhValidateSearch === 'function' && !window.fhValidateSearch(form)){
      e.preventDefault();
      e.stopPropagation();
      return;
    }

    e.preventDefault();

    form.dataset.fhxSubmitting = '1';
    showLoader();

    requestAnimationFrame(function(){
      requestAnimationFrame(function(){
        setTimeout(function(){
          HTMLFormElement.prototype.submit.call(form);
        },60);
      });
    });
  },true);

  /*
   * Immediate visual feedback on Search Flights click.
   * Actual navigation still happens through the submit handler above.
   */
  document.addEventListener('click',function(e){
    const button = e.target.closest('#form2 .fh-search-submit');

    if(button){
      hydrate();
    }
  },true);

  /*
   * Back/forward cache protection:
   * if the passenger returns to the homepage, do not leave the overlay stuck.
   */
  window.addEventListener('pageshow',function(){
    hideLoader();

    const form = document.getElementById('form2');
    if(form) delete form.dataset.fhxSubmitting;
  });

  window.FaresHuntSearchLoader = {
    show:showLoader,
    hide:hideLoader,
    hydrate:hydrate
  };
})();
</script>




<script id="fhx-loader-progress-motion-final">
(function(){
    let start = null;

    function animate(ts){
        if(start === null) start = ts;

        const overlay = document.getElementById('tnbSearchOverlay');
        /* fh-loader-idle-gate: loader hidden => no DOM work this frame */
        if(!overlay || !overlay.classList.contains('fhx-show')){ requestAnimationFrame(animate); return; }
        const plane = overlay ? overlay.querySelector('.fhx-top-plane') : null;
        const fill  = overlay ? overlay.querySelector('.fhx-progress-fill') : null;
        const routePlane = overlay ? overlay.querySelector('.fhx-route-plane') : null;

        if(plane && fill){
            const elapsed = (ts - start) / 2100;
            const phase = elapsed % 2;
            const ping = phase <= 1 ? phase : (2 - phase);

            const pct = 3 + (94 * ping);

            plane.style.setProperty('left', pct + '%', 'important');
            plane.style.setProperty('right', 'auto', 'important');
            plane.style.setProperty('transform', 'translate(-50%,-50%)', 'important');
            plane.style.setProperty('animation', 'none', 'important');

            fill.style.setProperty('width', pct + '%', 'important');
            fill.style.setProperty('animation', 'none', 'important');
        }

        if(routePlane){
            const elapsed = (ts - start) / 2200;
            const phase = elapsed % 2;
            const ping = phase <= 1 ? phase : (2 - phase);
            const pct = 12 + (76 * ping);

            routePlane.style.setProperty('left', pct + '%', 'important');
            routePlane.style.setProperty('right', 'auto', 'important');
            routePlane.style.setProperty('transform', 'translateX(-50%)', 'important');
            routePlane.style.setProperty('animation', 'none', 'important');
        }

        requestAnimationFrame(animate);
    }

    requestAnimationFrame(animate);
})();
</script>

{{-- Live Pax Monitor: isolated, fail-open session telemetry. No form values are collected. --}}
@if(config('live_pax.enabled', true))
<script src="{{ asset('assets/js/fareshunt-live-pax-recorder.js') }}?v=20261026a" defer></script>
@endif

</body>

</html>

@yield('footer')