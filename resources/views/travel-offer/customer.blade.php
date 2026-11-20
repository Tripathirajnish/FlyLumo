<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $offer->title }} | FaresHunt</title>
<style>
*{box-sizing:border-box}
html,body{margin:0;padding:0;background:#f3f6fa;font-family:Arial,Helvetica,sans-serif}
body{padding:28px 14px}
@media(max-width:700px){body{padding:12px 6px}}
</style>
</head>
<body>
@php
$currencySymbols = ['USD'=>'$','CAD'=>'C$','GBP'=>'£','EUR'=>'€','INR'=>'₹'];
$symbol = $currencySymbols[$offer->currency] ?? ($offer->currency.' ');
$totalTrips = (int)$offer->domestic_trips + (int)$offer->international_trips;
$benefits = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string)$offer->benefits))));
@endphp

<style>
.op-head{display:flex;justify-content:space-between;align-items:center;gap:14px;margin-bottom:18px}
.op-actions{display:flex;gap:8px;flex-wrap:wrap}
.op-wrap{max-width:940px;margin:0 auto}
.op-flyer{background:#fff;border:1px solid #dce5f0;border-radius:24px;overflow:hidden;box-shadow:0 20px 55px rgba(14,39,74,.10)}
.op-top{position:relative;padding:30px 38px 34px;background:linear-gradient(135deg,#f7fbff 0%,#fff 52%,#fff5f7 100%);border-bottom:1px solid #e6edf5}
.op-logo{width:220px;max-width:55%;height:auto;display:block}
.op-ref{position:absolute;right:34px;top:32px;font-size:10px;font-weight:800;color:#7890aa;letter-spacing:.06em}
.op-kicker{margin-top:32px;color:#d51d3f;font-size:12px;font-weight:900;letter-spacing:.13em;text-transform:uppercase}
.op-title{max-width:720px;margin:7px 0 0;color:#082a61;font-size:42px;line-height:1.04;letter-spacing:-.045em;font-weight:900}
.op-desc{max-width:720px;margin:13px 0 0;color:#58708e;font-size:14px;line-height:1.65}
.op-price-band{display:grid;grid-template-columns:1fr auto;gap:20px;align-items:center;padding:26px 38px;background:#082f67;color:#fff}
.op-trip-count{font-size:12px;text-transform:uppercase;letter-spacing:.12em;color:#bcd2ef;font-weight:800}
.op-trip-count strong{display:block;margin-top:4px;color:#fff;font-size:27px;letter-spacing:-.03em;text-transform:none}
.op-price{text-align:right}
.op-price small{display:block;color:#c6d7ed;font-size:10px;text-transform:uppercase;letter-spacing:.12em;font-weight:800}
.op-price strong{display:block;font-size:42px;line-height:1;margin-top:5px;letter-spacing:-.04em}

.op-premium-hero{position:relative;min-height:430px;background:url('{{ asset("assets/img/deals-hero.png") }}') center 57%/cover no-repeat;color:#fff;overflow:hidden}
.op-premium-overlay{position:absolute;inset:0;background:linear-gradient(90deg,rgba(4,27,61,.94) 0%,rgba(5,34,72,.82) 48%,rgba(5,25,55,.48) 100%)}
.op-premium-content{position:relative;z-index:2;padding:28px 38px 42px}
.op-premium-brand{display:flex;align-items:flex-start;justify-content:space-between;gap:20px}
.op-premium-brand .op-logo{width:190px;filter:brightness(0) invert(1)}
.op-premium-brand .op-ref{position:static;color:rgba(255,255,255,.72);font-size:10px}
.op-premium-grid{display:grid;grid-template-columns:minmax(0,1fr) 270px;gap:42px;align-items:end;margin-top:70px}
.op-premium-copy .op-kicker{margin:0;color:#ff5b70}
.op-premium-copy .op-title{color:#fff;max-width:560px;font-size:46px;text-shadow:0 3px 18px rgba(0,0,0,.22)}
.op-premium-copy .op-desc{color:rgba(255,255,255,.82);max-width:570px}
.op-hero-trip{display:flex;align-items:center;gap:12px;margin-top:23px;font-size:12px}
.op-hero-trip>i{width:42px;height:42px;border-radius:50%;display:grid;place-items:center;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.22)}
.op-hero-trip span{display:flex;flex-direction:column}
.op-hero-trip b{font-size:14px}
.op-hero-trip small{margin-top:2px;color:rgba(255,255,255,.68)}
.op-premium-price{background:rgba(255,255,255,.97);color:#153555;border-radius:20px;padding:24px;box-shadow:0 20px 50px rgba(0,0,0,.22)}
.op-price-label{display:block;color:#697c94;font-size:10px;font-weight:900;text-transform:uppercase;letter-spacing:.1em}
.op-premium-price>strong{display:block;color:#d51d3f;font-size:48px;line-height:1;margin-top:8px;letter-spacing:-.05em}
.op-price-currency{display:block;margin-top:5px;color:#71839a;font-size:10px;font-weight:800}
.op-price-divider{height:1px;background:#e5eaf0;margin:18px 0}
.op-price-includes{display:block;color:#526982;font-size:10px;line-height:1.5}
.op-price-cta{margin-top:17px;text-align:center;padding:12px;border-radius:9px;background:#d51d3f;color:#fff;font-size:11px;font-weight:900}

.op-body{padding:30px 38px}
.op-section-title{margin:0 0 14px;color:#153555;font-size:14px;font-weight:900}
.op-trip-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px;margin-bottom:26px}
.op-trip{border:1px solid #e0e8f1;border-radius:17px;padding:18px;background:#fbfdff}
.op-trip-icon{width:39px;height:39px;border-radius:12px;display:grid;place-items:center;background:#edf4ff;color:#2459df;margin-bottom:12px}
.op-trip:nth-child(2) .op-trip-icon{background:#fff0f3;color:#d51d3f}
.op-trip b{display:block;color:#153555;font-size:15px}
.op-trip p{margin:7px 0 0;color:#71839a;font-size:11px;line-height:1.55}
.op-airlines{display:flex;gap:7px;flex-wrap:wrap;margin-top:10px}
.op-airline{padding:6px 9px;border-radius:999px;background:#eef4fb;color:#47617f;font-size:9px;font-weight:800}

.op-visual-trips{gap:18px}
.op-visual-trip{padding:0;overflow:hidden;background:#fff;box-shadow:0 10px 28px rgba(15,43,78,.08)}
.op-trip-photo{height:150px;position:relative;background-size:cover;background-position:center}
.op-domestic-photo{background-image:url('{{ asset("assets/img/banner.jpg") }}')}
.op-international-photo{background-image:url('{{ asset("assets/img/destination/tr-3.jpg") }}')}
.op-photo-shade{position:absolute;inset:0;background:linear-gradient(180deg,rgba(5,26,55,.06),rgba(5,27,59,.76))}
.op-photo-label{position:absolute;left:18px;right:18px;bottom:15px;color:#fff}
.op-photo-label span{display:block;font-size:9px;font-weight:900;text-transform:uppercase;letter-spacing:.1em;opacity:.82}
.op-photo-label strong{display:block;margin-top:3px;font-size:20px;letter-spacing:-.025em}
.op-trip-content{padding:17px 18px 19px}
.op-trip-heading{display:flex;gap:12px;align-items:flex-start}
.op-trip-heading .op-trip-icon{flex:0 0 39px;margin:0}
.op-trip-heading>div:last-child{min-width:0}
.op-trip-heading p{margin:5px 0 0}
.op-visual-trip .op-airlines{margin-top:14px;padding-top:12px;border-top:1px solid #edf1f5}
.op-visual-trip .op-airline{background:#f3f6fa;border:1px solid #e4eaf1}
.op-rule{display:flex;align-items:center;gap:7px;margin-top:14px;padding-top:12px;border-top:1px solid #edf1f5;color:#63778f;font-size:10px;font-weight:700}
.op-rule i{color:#15915f}

.op-info-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:11px;margin-bottom:26px}
.op-info{padding:14px;border:1px solid #e4eaf2;border-radius:14px}
.op-info span{display:block;color:#8391a4;font-size:9px;text-transform:uppercase;font-weight:800}
.op-info b{display:block;color:#173654;margin-top:6px;font-size:11px}
.op-benefits{display:grid;grid-template-columns:repeat(2,1fr);gap:9px;margin-bottom:26px}
.op-benefit{display:flex;gap:9px;align-items:flex-start;padding:11px 12px;border-radius:12px;background:#f6f9fc;color:#526982;font-size:11px;line-height:1.45}
.op-benefit i{color:#15915f;margin-top:2px}
.op-alert{padding:15px 17px;border-radius:14px;background:#fff5e7;color:#865c17;font-size:11px;line-height:1.6;margin-bottom:25px}
.op-terms{border-top:1px solid #e6ecf3;padding-top:20px;color:#74849a;font-size:10px;line-height:1.65;white-space:pre-line}
.op-footer{display:flex;justify-content:space-between;gap:20px;align-items:center;padding:20px 38px;background:#f7f9fc;border-top:1px solid #e4eaf1}
.op-contact b{display:block;color:#123665;font-size:12px}
.op-contact span{display:block;color:#74869d;font-size:10px;margin-top:3px}
.op-cta{padding:11px 17px;border-radius:11px;background:#d51d3f;color:#fff;font-size:11px;font-weight:900}
@media(max-width:700px){.op-head{align-items:flex-start;flex-direction:column}.op-top,.op-body{padding:24px}.op-title{font-size:32px}.op-price-band{padding:22px 24px;grid-template-columns:1fr}.op-price{text-align:left}.op-trip-grid,.op-info-grid,.op-benefits{grid-template-columns:1fr}.op-footer{padding:20px 24px;align-items:flex-start;flex-direction:column}.op-ref{position:static;margin-top:15px}}
</style>

<div class="op-head">
    <div>
        <span class="eyebrow">Customer-ready preview</span>
        <h1 class="title">Offer Preview</h1>
    </div>
    
</div>

<div class="op-wrap">
<div class="op-flyer">

<div class="op-premium-hero">
    <div class="op-premium-overlay"></div>

    <div class="op-premium-content">
        <div class="op-premium-brand">
            <img class="op-logo" src="{{ asset('assets/img/fareshunt-logo-transparent.png') }}" alt="FaresHunt">
            <div class="op-ref">{{ $offer->reference }}</div>
        </div>

        <div class="op-premium-grid">
            <div class="op-premium-copy">
                <div class="op-kicker">
                    {{ $offer->cabin_class ?: 'Travel' }} Special Offer
                </div>

                <h2 class="op-title">{{ $offer->title }}</h2>

                @if($offer->short_description)
                    <p class="op-desc">{{ $offer->short_description }}</p>
                @endif

                <div class="op-hero-trip">
                    <i class="fa-solid fa-plane-departure"></i>
                    <span>
                        <b>{{ $totalTrips }} Round {{ $totalTrips === 1 ? 'Trip' : 'Trips' }}</b>
                        <small>included in this exclusive travel offer</small>
                    </span>
                </div>
            </div>

            <div class="op-premium-price">
                <span class="op-price-label">One Special Price</span>
                <strong>{{ $symbol }}{{ number_format((float)$offer->price,0) }}</strong>
                <span class="op-price-currency">{{ $offer->currency }} / passenger</span>

                <div class="op-price-divider"></div>

                <span class="op-price-includes">
                    Includes {{ (int)$offer->domestic_trips }} domestic +
                    {{ (int)$offer->international_trips }} international
                </span>

                <a class="op-price-cta" href="{{ URL::temporarySignedRoute('travel_offer.book', $version->expires_at ? \Carbon\Carbon::parse($version->expires_at) : now()->addDays(30), ['version'=>$version->id]) }}" style="text-decoration:none">Book Your Slot</a>
            </div>
        </div>
    </div>
</div>

<div class="op-body">

<h3 class="op-section-title">Your Travel Package</h3>

<div class="op-trip-grid op-visual-trips">

    @if((int)$offer->domestic_trips > 0)
    <div class="op-trip op-visual-trip">
        <div class="op-trip-photo op-domestic-photo">
            <div class="op-photo-shade"></div>
            <div class="op-photo-label">
                <span>U.S. Domestic Travel</span>
                <strong>{{ (int)$offer->domestic_trips }} Round {{ (int)$offer->domestic_trips === 1 ? 'Trip' : 'Trips' }}</strong>
            </div>
        </div>

        <div class="op-trip-content">
            <div class="op-trip-heading">
                <div class="op-trip-icon"><i class="fa-solid fa-plane"></i></div>
                <div>
                    <b>{{ (int)$offer->domestic_trips }} U.S. Domestic Round {{ (int)$offer->domestic_trips === 1 ? 'Trip' : 'Trips' }}</b>
                    @if($offer->domestic_advance_months)
                    <p>Request at least {{ $offer->domestic_advance_months }} {{ (int)$offer->domestic_advance_months === 1 ? 'month' : 'months' }} before travel.</p>
                    @endif
                </div>
            </div>

            @if(count($airlines))
            <div class="op-airlines">
                @foreach($airlines as $airline)
                    <span class="op-airline">{{ $airline }}</span>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    @endif

    @if((int)$offer->international_trips > 0)
    <div class="op-trip op-visual-trip">
        <div class="op-trip-photo op-international-photo">
            <div class="op-photo-shade"></div>
            <div class="op-photo-label">
                <span>International Travel</span>
                <strong>{{ (int)$offer->international_trips }} Round {{ (int)$offer->international_trips === 1 ? 'Trip' : 'Trips' }}</strong>
            </div>
        </div>

        <div class="op-trip-content">
            <div class="op-trip-heading">
                <div class="op-trip-icon"><i class="fa-solid fa-earth-americas"></i></div>
                <div>
                    <b>{{ (int)$offer->international_trips }} International Round {{ (int)$offer->international_trips === 1 ? 'Trip' : 'Trips' }}</b>
                    @if($offer->international_advance_months)
                    <p>Request at least {{ $offer->international_advance_months }} {{ (int)$offer->international_advance_months === 1 ? 'month' : 'months' }} before travel.</p>
                    @endif
                </div>
            </div>

            @if($offer->international_airline_rule)
                <div class="op-rule"><i class="fa-solid fa-circle-check"></i>{{ $offer->international_airline_rule }}</div>
            @endif
        </div>
    </div>
    @endif

</div>

<div class="op-info-grid">
    <div class="op-info">
        <span>Cabin</span>
        <b>{{ $offer->cabin_class ?: 'As specified' }}</b>
    </div>

    <div class="op-info">
        <span>Offer Valid Until</span>
        <b>{{ $offer->valid_until ? \Carbon\Carbon::parse($offer->valid_until)->format('M d, Y') : 'As specified' }}</b>
    </div>

    <div class="op-info">
        <span>Availability</span>
        <b>{{ $offer->availability_message ?: ($offer->slot_limit ? $offer->slot_limit.' slots available' : 'Subject to availability') }}</b>
    </div>
</div>

@if(count($benefits))
<h3 class="op-section-title">Offer Highlights</h3>
<div class="op-benefits">
    @foreach($benefits as $benefit)
    <div class="op-benefit">
        <i class="fa-solid fa-circle-check"></i>
        <span>{{ $benefit }}</span>
    </div>
    @endforeach
</div>
@endif

<div class="op-alert">
    <i class="fa-solid fa-circle-info me-1"></i>
    All travel is subject to applicable availability and the offer terms shown below.
</div>

@if($offer->terms)
<h3 class="op-section-title">Terms & Conditions</h3>
<div class="op-terms">{{ $offer->terms }}</div>
@endif

</div>

<div class="op-footer">
    <div class="op-contact">
        <b>FaresHunt — Hunt the Best Fares</b>
        <span>+1-888-717-0346 &nbsp; • &nbsp; info@fareshunt.com &nbsp; • &nbsp; fareshunt.com</span>
    </div>
    <a class="op-cta" href="{{ URL::temporarySignedRoute('travel_offer.book', $version->expires_at ? \Carbon\Carbon::parse($version->expires_at) : now()->addDays(30), ['version'=>$version->id]) }}" style="text-decoration:none">Book Your Slot</a>
</div>

</div>
</div>

<style id="travel-offer-final-premium-polish">
.op-benefits{
    gap:12px;
}
.op-benefit{
    background:linear-gradient(135deg,#f7faff,#f1f6fb);
    border:1px solid #e3ebf4;
    border-radius:12px;
    padding:14px 15px;
}
.op-benefit i{
    font-size:14px;
}
.op-alert{
    margin-top:24px;
    border:1px solid #f0dfbd;
    border-radius:12px;
    padding:14px 16px;
}
.op-footer{
    position:relative;
    overflow:hidden;
    background:linear-gradient(110deg,#071f43 0%,#0d3767 100%);
    border-top:0;
    padding:25px 30px;
}
.op-footer:after{
    content:"";
    position:absolute;
    width:190px;
    height:190px;
    right:120px;
    top:-120px;
    border-radius:50%;
    background:rgba(255,255,255,.045);
}
.op-contact{
    position:relative;
    z-index:2;
}
.op-contact b{
    color:#fff;
    font-size:13px;
}
.op-contact span{
    color:rgba(255,255,255,.68);
    margin-top:5px;
}
.op-footer .op-cta{
    position:relative;
    z-index:2;
    min-width:145px;
    text-align:center;
    padding:13px 20px;
    border-radius:10px;
    background:#d91d42;
    color:#fff;
    box-shadow:0 8px 22px rgba(217,29,66,.22);
}
@media(max-width:700px){
    .op-premium-grid{
        grid-template-columns:1fr;
        gap:24px;
        margin-top:45px;
    }
    .op-premium-copy .op-title{
        font-size:34px;
    }
    .op-premium-price{
        max-width:none;
    }
    .op-footer{
        align-items:flex-start;
        gap:18px;
    }
    .op-footer .op-cta{
        width:100%;
    }
}
</style>

</body>
</html>

<style id="travel-offer-logo-price-card-fix">
/* Keep the official FaresHunt logo in its original colors */
.op-premium-brand .op-logo{
    width:190px;
    height:auto;
    max-width:55%;
    filter:none !important;
    opacity:1;
    object-fit:contain;
}

/* Prevent price-card content and CTA from overlapping */
.op-premium-price{
    display:flex;
    flex-direction:column;
    align-items:stretch;
    min-height:0;
}

.op-price-includes{
    display:block;
    position:relative;
    margin:0;
    padding:0;
    line-height:1.55;
}

.op-price-cta{
    display:block;
    position:relative;
    width:100%;
    margin-top:18px;
    padding:12px 16px;
    line-height:1.25;
    text-align:center;
    white-space:nowrap;
}

@media(max-width:700px){
    .op-premium-brand .op-logo{
        width:165px;
        max-width:65%;
    }
}
</style>

<style id="travel-offer-hero-logo-visibility-final">
.op-premium-brand .op-logo{
    filter:brightness(0) invert(1) !important;
    opacity:1 !important;
}
</style>
