@extends('layout')

@section('header-seo')
    <title>Flight Booking</title>
    <meta name="description" content="Flight Booking">

@endsection

@section('mid-content')

@php $setting = getAllSetting(); @endphp

<style>
    .note {
    font-size: 11px;
    }

    #loadingSubmit {
    z-index: 99999;
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 20px;
    border-radius: 10px;
    font-size: 18px;
    text-align: center;
  }

  #loadingSubmit img {
    width: 50px;
    margin-top: 10px;
  }

/* Premium checkout Country + State Select2 */
body.tnb-checkout-active #billing_country + .select2-container,
body.tnb-checkout-active #billing_state_97 + .select2-container{
    width:100%!important;
}

body.tnb-checkout-active #billing_country + .select2-container .select2-selection--single,
body.tnb-checkout-active #billing_state_97 + .select2-container .select2-selection--single{
    height:54px!important;
    min-height:54px!important;
    display:flex!important;
    align-items:center!important;
    border:1px solid #cfd9e6!important;
    border-radius:14px!important;
    background:linear-gradient(180deg,#ffffff 0%,#fbfcfe 100%)!important;
    box-shadow:0 4px 14px rgba(13,42,75,.045)!important;
    transition:border-color .18s ease,box-shadow .18s ease,transform .18s ease!important;
}

body.tnb-checkout-active #billing_country + .select2-container .select2-selection--single:hover,
body.tnb-checkout-active #billing_state_97 + .select2-container .select2-selection--single:hover{
    border-color:#aebed1!important;
    box-shadow:0 7px 18px rgba(13,42,75,.07)!important;
}

body.tnb-checkout-active #billing_country + .select2-container.select2-container--focus .select2-selection--single,
body.tnb-checkout-active #billing_country + .select2-container.select2-container--open .select2-selection--single,
body.tnb-checkout-active #billing_state_97 + .select2-container.select2-container--focus .select2-selection--single,
body.tnb-checkout-active #billing_state_97 + .select2-container.select2-container--open .select2-selection--single{
    border-color:#4f8ff7!important;
    box-shadow:0 0 0 3px rgba(79,143,247,.12),0 10px 24px rgba(13,42,75,.08)!important;
}

body.tnb-checkout-active #billing_country + .select2-container .select2-selection__rendered,
body.tnb-checkout-active #billing_state_97 + .select2-container .select2-selection__rendered{
    line-height:52px!important;
    padding-left:18px!important;
    padding-right:48px!important;
    color:#17263c!important;
    font-size:15px!important;
    font-weight:600!important;
}

body.tnb-checkout-active #billing_country + .select2-container .select2-selection__arrow,
body.tnb-checkout-active #billing_state_97 + .select2-container .select2-selection__arrow{
    height:52px!important;
    width:42px!important;
    right:6px!important;
    top:0!important;
}

body.tnb-checkout-active #billing_country + .select2-container .select2-selection__arrow b,
body.tnb-checkout-active #billing_state_97 + .select2-container .select2-selection__arrow b{
    border-color:#718096 transparent transparent transparent!important;
    border-width:5px 4px 0 4px!important;
    margin-left:-4px!important;
    margin-top:-2px!important;
}

body.tnb-checkout-active .select2-dropdown{
    border:1px solid #d7e0ea!important;
    border-radius:14px!important;
    overflow:hidden!important;
    box-shadow:0 18px 45px rgba(7,31,55,.16)!important;
    background:#fff!important;
}

body.tnb-checkout-active .select2-search--dropdown{
    padding:10px!important;
    background:#fff!important;
}

body.tnb-checkout-active .select2-search--dropdown .select2-search__field{
    height:42px!important;
    border:1px solid #d6e0eb!important;
    border-radius:10px!important;
    padding:0 12px!important;
    outline:none!important;
    font-size:14px!important;
}

body.tnb-checkout-active .select2-search--dropdown .select2-search__field:focus{
    border-color:#4f8ff7!important;
    box-shadow:0 0 0 3px rgba(79,143,247,.10)!important;
}

body.tnb-checkout-active .select2-results__option{
    padding:10px 14px!important;
    font-size:14px!important;
}

body.tnb-checkout-active .select2-results__option--highlighted[aria-selected]{
    background:#f2f6fb!important;
    color:#17263c!important;
}

body.tnb-checkout-active .select2-results__option[aria-selected=true]{
    background:#edf4ff!important;
    color:#17263c!important;
    font-weight:700!important;
}


/* RICH PREMIUM ITINERARY V1 */
.fh-premium-itinerary-card{
    overflow:hidden;
    margin-bottom:24px;
    border:1px solid #dce5ef;
    border-radius:24px;
    background:#fff;
    box-shadow:0 14px 38px rgba(8,28,52,.07);
}
.fh-premium-itinerary-heading{
    position:relative;
    padding:25px 28px;
    background:linear-gradient(125deg,#07192f,#10365c);
    border-bottom:3px solid #d6153c;
}
.fh-premium-itinerary-heading span{
    display:block;
    margin-bottom:7px;
    color:#cbd7e6;
    font-size:10px;
    font-weight:800;
    letter-spacing:2px;
}
.fh-premium-itinerary-heading h2{
    margin:0!important;
    color:#fff!important;
    font-size:25px!important;
    font-weight:800!important;
}
.fh-premium-itinerary-card .fh-itin{
    padding:5px 26px;
}
.fh-premium-itinerary-card .fh-leg{
    padding:23px 0;
}
.fh-premium-itinerary-card .fh-leg-top strong{
    color:#0c2948;
    font-size:13px;
}
.fh-premium-itinerary-card .fh-time b{
    font-size:21px;
}
@media(max-width:760px){
    .fh-premium-itinerary-card{
        margin-bottom:16px;
        border-radius:18px;
    }
    .fh-premium-itinerary-heading{
        padding:20px;
    }
    .fh-premium-itinerary-card .fh-premium-itinerary-heading h2{
        margin:0!important;
        font-size:21px!important;
        line-height:1.25!important;
        color:#fff!important;
    }
    .fh-premium-itinerary-card .fh-itin{
        padding:2px 16px;
    }
    .fh-premium-itinerary-card .fh-time b{
        font-size:17px;
    }
}


/* RICH PREMIUM FARE SIDEBAR V1 */
.fh-summary .fh-trip-card{
    overflow:hidden;
    border:1px solid #dce5ef;
    border-radius:22px;
    background:#fff;
    box-shadow:0 14px 38px rgba(8,28,52,.08);
}
.fh-summary .fh-trip-head{
    padding:23px 24px;
    background:linear-gradient(125deg,#07192f,#10365c);
    border-bottom:3px solid #d6153c;
}
.fh-summary .fh-trip-head,
.fh-summary .fh-trip-head strong,
.fh-summary .fh-trip-head span{
    color:#fff;
}
.fh-summary .fh-fares{
    padding:24px;
}
.fh-summary .fh-fares h6{
    margin-bottom:20px;
    color:#0c2948;
    font-size:18px;
    font-weight:800;
}
.fh-summary .fh-fare-row{
    display:flex;
    justify-content:space-between;
    gap:12px;
    padding:11px 0;
    border-bottom:1px solid #edf1f6;
}
.fh-summary .fh-fare-row strong{
    color:#102b49;
    font-weight:750;
}
.fh-summary .fh-total{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    margin-top:18px;
    padding:19px 17px;
    border-radius:14px;
    background:#f0f5fb;
    border-left:4px solid #d6153c;
}
.fh-summary .fh-total strong{
    color:#0b2949;
    font-size:27px;
    font-weight:850;
}
.fh-summary .fh-total-note{
    margin-top:12px;
    color:#63758b;
    font-size:12px;
}
@media(max-width:760px){
    .fh-summary .fh-trip-card{
        border-radius:18px;
    }
    .fh-summary .fh-trip-head,
    .fh-summary .fh-fares{
        padding:19px;
    }
    .fh-summary .fh-total strong{
        font-size:23px;
    }
}

</style>

<style>
#form4>.card,#form4>.col-xl-12>.card{border:1px solid #e0e7ef!important;border-radius:18px!important;box-shadow:0 8px 24px rgba(10,32,55,.04)!important;overflow:hidden}
#form4 .card-header{background:#fff!important;border-bottom:1px solid #edf1f5!important;padding:17px 20px!important}
#form4 .card-body{padding:20px!important}
#form4 .form-control,#form4 .form-select{min-height:48px;border-radius:11px!important;border-color:#dbe3ed!important;background:#fbfcfe}
#submitBtn{min-width:280px;border-radius:10px!important;padding:14px 20px!important;font-weight:800!important}
</style>


<style>
/* Final checkout polish - keeps existing form names/routes/backend logic intact. */
body.tnb-checkout-active .tnb-payment-card .card-header,
body.tnb-checkout-active .tnb-billing-card .card-header{
    display:none;
}
body.tnb-checkout-active .tnb-payment-card,
body.tnb-checkout-active .tnb-billing-card{
    border:1px solid #e1e7ef!important;
    border-radius:20px!important;
    box-shadow:0 10px 30px rgba(8,24,47,.055)!important;
    overflow:hidden;
}
body.tnb-checkout-active .tnb-payment-card .card-body,
body.tnb-checkout-active .tnb-billing-card .card-body{
    padding:24px!important;
}
body.tnb-checkout-active .tnb-payment-card .form-group.row{
    display:block!important;
    margin:0 0 18px!important;
}
body.tnb-checkout-active .tnb-payment-card .form-group.row>.col-5,
body.tnb-checkout-active .tnb-payment-card .form-group.row>.col-7{
    width:100%!important;
    max-width:100%!important;
    flex:0 0 100%!important;
    text-align:left!important;
}
body.tnb-checkout-active .tnb-payment-card .form-group.row>.col-5{
    margin-bottom:7px!important;
}
body.tnb-checkout-active .tnb-payment-card .form-label{
    font-size:11px!important;
    font-weight:800!important;
    color:#60718b!important;
    margin:0!important;
}
body.tnb-checkout-active .tnb-payment-card .form-control,
body.tnb-checkout-active .tnb-billing-card .form-control{
    min-height:50px!important;
    border-radius:12px!important;
    border:1px solid #dbe3ed!important;
    background:#fbfcfe!important;
}
body.tnb-checkout-active .tnb-payment-card img{
    max-width:290px;
    height:auto;
}
.tnb-card-field-wrap{
    position:relative;
}
.tnb-card-brand-badge{
    position:absolute;
    right:12px;
    top:50%;
    transform:translateY(-50%);
    min-width:72px;
    height:34px;
    padding:0 10px;
    border:1px solid #dbe3ed;
    border-radius:9px;
    display:none;
    align-items:center;
    justify-content:center;
    background:#fff;
    color:#0d1c31;
    font-size:11px;
    font-weight:900;
    letter-spacing:.02em;
    box-shadow:0 5px 14px rgba(8,24,47,.06);
}
.tnb-card-brand-badge.show{display:flex}
#creditcard_no{padding-right:100px!important}
.tnb-card-help{
    display:block;
    margin-top:7px;
    font-size:11px;
    color:#718199;
}
.tnb-card-help.valid{color:#159b57;font-weight:700}
.tnb-card-help.invalid{color:#c81738;font-weight:700}
.tnb-payment-security{
    display:flex;
    align-items:center;
    gap:8px;
    margin:0 0 20px;
    padding:12px 14px;
    background:#eefaf3;
    border:1px solid #d1f1df;
    border-radius:12px;
    color:#147747;
    font-size:12px;
    font-weight:700;
}
@media(max-width:575px){
    body.tnb-checkout-active .tnb-payment-card .card-body,
    body.tnb-checkout-active .tnb-billing-card .card-body{padding:16px!important}
}
</style>



<style id="fh-checkout-3step-final">
:root{
    --fh-navy:#081b31;
    --fh-navy2:#0d2a4b;
    --fh-red:#df173d;
    --fh-red2:#bd0e31;
    --fh-green:#18a95b;
    --fh-text:#10223b;
    --fh-muted:#6f819d;
    --fh-border:#dfe7f0;
    --fh-bg:#f5f7fb;
    --fh-card:#ffffff;
}
body.tnb-checkout-active{background:var(--fh-bg)!important}
.fh-checkout{background:var(--fh-bg);padding:26px 0 0}
.fh-checkout>.container{max-width:1560px!important}
.fh-progress{
    width:min(620px,100%);
    margin:0 auto 16px;
    display:grid;
    grid-template-columns:auto 1fr auto 1fr auto;
    align-items:start;
    gap:14px
}
.fh-progress-line{height:1px;background:#d8e1ec;margin-top:23px}
.fh-progress-step{display:flex;flex-direction:column;align-items:center;gap:8px;color:#7a8ba6;font-size:12px;font-weight:700}
.fh-progress-step .dot{
    width:46px;height:46px;border-radius:50%;display:grid;place-items:center;
    border:1px solid #d6e0eb;background:#f9fbfd;color:#6d809d;font-size:15px
}
.fh-progress-step.active{color:var(--fh-red)}
.fh-progress-step.active .dot{
    color:#fff;background:linear-gradient(135deg,var(--fh-red),var(--fh-red2));
    border-color:transparent;box-shadow:0 12px 25px rgba(223,23,61,.22)
}
.fh-progress-step.done{color:var(--fh-green)}
.fh-progress-step.done .dot{background:#ebfaf2;border-color:#bfe9d0;color:var(--fh-green)}
.fh-secure{
    display:flex;align-items:center;justify-content:center;gap:9px;
    margin-bottom:24px;color:#667a99;font-size:12px
}
.fh-secure i{color:var(--fh-green)}
.fh-layout{display:grid;grid-template-columns:minmax(0,1fr) 410px;gap:30px;align-items:start}
.fh-main h2{margin:0 0 14px;font-size:24px;font-weight:600;color:var(--fh-text)}
.fh-step-panel{display:none}
.fh-step-panel.active{display:block}
.fh-card{
    border:1px solid var(--fh-border)!important;border-radius:24px!important;background:#fff!important;
    box-shadow:0 10px 28px rgba(7,28,53,.045)!important;overflow:hidden!important
}
.fh-card .card-header{padding:18px 22px!important;background:#fff!important;border-bottom:1px solid #edf1f5!important}
.fh-card .card-body{padding:22px!important}
.fh-pax-head{display:flex;align-items:center;gap:13px}
.fh-pax-icon{
    width:38px;height:38px;border-radius:12px;display:grid;place-items:center;
    background:#fff1f4;color:var(--fh-red)
}
.fh-pax-head strong{display:block;color:#11223b;font-size:14px}
.fh-pax-head small{display:block;margin-top:2px;color:#7b8ca6;font-size:11px}
.fh-checkout .form-group{margin-bottom:15px}
.fh-checkout .form-label{display:block;margin:0 0 7px!important;color:#627695!important;font-size:11px!important;font-weight:700!important}
.fh-checkout .form-control,.fh-checkout .form-select{
    min-height:50px!important;border:1px solid #dbe4ee!important;border-radius:14px!important;
    background:#f9fbfd!important;color:#14263f!important;box-shadow:none!important
}
.fh-checkout .form-control:focus,.fh-checkout .form-select:focus{
    border-color:#9db0c7!important;background:#fff!important;box-shadow:0 0 0 4px rgba(48,88,140,.08)!important
}
.fh-helper{font-size:11px;color:#7a8da8;line-height:1.5}
.fh-kicker{margin:0 0 16px;color:#627694;font-size:12px}
.fh-step-actions{display:flex;gap:12px;justify-content:flex-end;margin:18px 0 30px}
.fh-btn-primary,.fh-btn-secondary{
    min-height:50px;border-radius:14px;padding:0 24px;border:0;font-weight:800
}
.fh-btn-primary{
    flex:1;color:#fff;background:linear-gradient(135deg,var(--fh-red),var(--fh-red2));
    box-shadow:0 14px 28px rgba(223,23,61,.22)
}
.fh-btn-secondary{background:#eef2f7;color:#223955}
.fh-addon-card{padding:0}
.fh-addon-choice{
    display:grid;grid-template-columns:1fr auto;gap:18px;align-items:center;padding:22px
}
.fh-addon-choice+.fh-addon-choice{border-top:1px solid #edf1f5}
.fh-addon-title{font-size:15px;font-weight:800;color:#13243c}
.fh-addon-price{font-size:23px;font-weight:900;color:var(--fh-red);text-align:right}
.fh-addon-price small{display:block;margin-top:3px;font-size:10px;font-weight:500;color:#7b8da8}
.fh-choice-buttons{display:flex;gap:8px;margin-top:12px}
.fh-choice-buttons label{border-radius:10px!important;font-weight:700!important}
.fh-payment-card .form-group.row{display:block!important;margin:0 0 16px!important}
.fh-payment-card .form-group.row>.col-5,.fh-payment-card .form-group.row>.col-7{
    width:100%!important;max-width:100%!important;flex:0 0 100%!important;text-align:left!important
}
.fh-payment-card .form-group.row>.col-5{margin-bottom:7px!important}
.fh-payment-card img{max-width:300px;height:auto}
.fh-payment-security{margin-bottom:18px!important}
.fh-summary{position:sticky;top:18px}
.fh-trip-card{
    border:1px solid var(--fh-border);border-radius:24px;background:#fff;overflow:hidden;
    box-shadow:0 13px 36px rgba(8,28,52,.085)
}
.fh-trip-head{
    padding:22px;background:linear-gradient(145deg,#07192f,#0d2b4e);color:#fff
}
.fh-trip-eyebrow{font-size:10px;letter-spacing:.09em;text-transform:uppercase;color:#aab9ca;font-weight:800}
.fh-trip-route{display:flex;justify-content:space-between;align-items:end;gap:12px;margin-top:8px}
.fh-trip-route strong{font-size:24px;line-height:1}
.fh-trip-route span{font-size:11px;color:#bec9d8}
.fh-itin{padding:0 20px}
.fh-leg{padding:20px 0}
.fh-leg+.fh-leg{border-top:1px solid #e7edf4}
.fh-leg-top{display:flex;justify-content:space-between;gap:12px;margin-bottom:13px}
.fh-leg-top strong{font-size:12px;text-transform:uppercase;color:#1b2b41}
.fh-leg-top span{font-size:11px;color:#71839d}
.fh-segment{display:grid;grid-template-columns:52px 1fr;gap:12px;align-items:start}
.fh-airline-logo{
    width:44px;height:44px;border-radius:10px;background:#f4f7fb;display:grid;place-items:center;overflow:hidden
}
.fh-airline-logo img{width:100%;height:100%;object-fit:contain}
.fh-airline-meta strong{display:block;font-size:13px;color:#14243a}
.fh-airline-meta small{display:block;color:#7486a0;font-size:10px;margin-top:2px}
.fh-time-row{
    grid-column:1/-1;display:grid;grid-template-columns:1fr minmax(110px,1.15fr) 1fr;
    gap:10px;align-items:center;margin-top:12px
}
.fh-time{text-align:left}
.fh-time.right{text-align:right}
.fh-time b{display:block;color:#10223a;font-size:18px}
.fh-time span{display:block;color:#6f819d;font-size:10px;margin-top:3px}
.fh-flight-line{text-align:center}
.fh-flight-line .line{height:1px;background:#aab9cb;position:relative}
.fh-flight-line .line:before,.fh-flight-line .line:after{
    content:"";position:absolute;top:50%;width:7px;height:7px;border-radius:50%;background:#fff;border:1px solid #9eb0c5;transform:translateY(-50%)
}
.fh-flight-line .line:before{left:-1px}.fh-flight-line .line:after{right:-1px}
.fh-flight-line i{position:relative;top:-10px;background:#fff;padding:0 6px;color:#173f6b}
.fh-flight-line small{display:block;margin-top:-5px;color:#667a97;font-size:10px}
.fh-fares{padding:18px 20px;border-top:1px solid #e7edf4}
.fh-fares h6{margin:0 0 10px;color:#1b2b41;font-size:11px;text-transform:uppercase;letter-spacing:.06em}
.fh-fare-row{display:flex;justify-content:space-between;gap:12px;padding:7px 0;color:#657895;font-size:12px}
.fh-fare-row strong{color:#17263c}
.fh-total{display:flex;justify-content:space-between;align-items:end;gap:12px;padding-top:13px;margin-top:10px;border-top:1px solid #e7edf4}
.fh-total span{font-size:14px;font-weight:800;color:#17263c}
.fh-total strong{font-size:29px;color:var(--fh-red)}
.fh-total-note{text-align:right;color:#7a8ca6;font-size:10px;margin-top:4px}
.fh-help{margin-top:16px;border:1px solid var(--fh-border);border-radius:20px;background:#fff;padding:17px 18px}
.fh-help strong{display:block;color:#17273f;font-size:13px;margin-bottom:5px}
.fh-help p{margin:0;color:#6f819b;font-size:11px;line-height:1.5}
.fh-trust{margin-top:36px;background:#07192f;color:#fff}
.fh-trust .inner{
    max-width:1560px;margin:0 auto;padding:24px 28px;display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));gap:20px
}
.fh-trust-item{display:flex;align-items:center;gap:12px}
.fh-trust-icon{
    width:40px;height:40px;border-radius:50%;display:grid;place-items:center;
    background:#122a47;color:#f0c83a
}
.fh-trust-item strong{display:block;font-size:12px}
.fh-trust-item small{display:block;margin-top:2px;color:#9fb0c6;font-size:10px}
.fh-terms{font-size:11px;color:#687b97;line-height:1.55;margin-bottom:13px}
#submitBtn{
    width:100%!important;min-height:52px!important;border:0!important;border-radius:14px!important;
    background:linear-gradient(135deg,var(--fh-red),var(--fh-red2))!important;color:#fff!important;
    font-weight:900!important;box-shadow:0 14px 28px rgba(223,23,61,.22)!important
}
.fh-invalid{border-color:#d91f43!important;box-shadow:0 0 0 3px rgba(217,31,67,.08)!important}
@media(max-width:1199px){
    .fh-layout{grid-template-columns:1fr}
    .fh-summary{position:static}
}
@media(max-width:767px){
    .fh-checkout{padding-top:18px}
    .fh-progress{gap:7px}.fh-progress-line{margin-top:20px}
    .fh-progress-step .dot{width:40px;height:40px}
    .fh-main h2{font-size:21px}
    .fh-card .card-body{padding:16px!important}
    .fh-addon-choice{grid-template-columns:1fr}
    .fh-addon-price{text-align:left}
    .fh-trust .inner{grid-template-columns:1fr 1fr;padding:20px 16px}
}
@media(max-width:480px){.fh-trust .inner{grid-template-columns:1fr}}

/* FINAL LAYOUT FIXES */
.fh-step-panel[data-step-panel="1"] .fh-card:last-of-type .fh-btn-primary{
    width:100%!important;
    display:flex!important;
    align-items:center!important;
    justify-content:center!important;
    margin-top:4px!important;
}
.fh-layout{
    margin-top:0!important;
}
.fh-main{
    min-width:0!important;
}
.fh-summary{
    min-width:0!important;
}
.fh-trip-card{
    width:100%!important;
}
body.tnb-checkout-active .fh-checkout{
    min-height:auto!important;
}


/* UNIFIED ADD-ON LOOK */
.fh-addon-card .fh-addon-choice{
    min-height:112px!important;
    grid-template-columns:minmax(0,1fr) 190px!important;
    padding:20px 20px!important;
    align-items:center!important;
}
.fh-addon-card .fh-addon-title{
    display:flex!important;
    align-items:center!important;
    gap:8px!important;
    flex-wrap:wrap!important;
    font-size:15px!important;
}
.fh-addon-card .fh-addon-price{
    text-align:right!important;
    font-size:22px!important;
    line-height:1.1!important;
}
.fh-addon-card .fh-addon-price small{
    margin-top:5px!important;
}
.fh-addon-card .fh-choice-buttons,
.fh-addon-card .text-end .fh-choice-buttons{
    display:flex!important;
    justify-content:flex-end!important;
    gap:8px!important;
    margin-top:10px!important;
}
.fh-addon-card .btn-check + .btn,
.fh-addon-card .fh-premium-addon + .btn{
    min-width:118px!important;
    min-height:38px!important;
    padding:8px 14px!important;
    border-radius:10px!important;
    display:inline-flex!important;
    align-items:center!important;
    justify-content:center!important;
    font-size:12px!important;
    font-weight:800!important;
    line-height:1!important;
}
.fh-addon-card .btn-outline-secondary{
    border-color:#cfd8e4!important;
    color:#5f718c!important;
    background:#fff!important;
}
.fh-addon-card .btn-outline-secondary:hover{
    background:#f4f7fb!important;
}
.fh-addon-card .btn-check:checked + .btn-primary,
.fh-addon-card .fh-premium-addon:checked + .btn{
    background:#18a95b!important;
    border-color:#18a95b!important;
    color:#fff!important;
}
.fh-addon-card .badge{
    font-size:9px!important;
    padding:5px 8px!important;
    border-radius:999px!important;
    font-weight:700!important;
}
.fh-addon-card .form-switch{
    display:none!important;
}
@media(max-width:767px){
    .fh-addon-card .fh-addon-choice{
        grid-template-columns:1fr!important;
    }
    .fh-addon-card .fh-addon-price{
        text-align:left!important;
    }
    .fh-addon-card .fh-choice-buttons,
    .fh-addon-card .text-end .fh-choice-buttons{
        justify-content:flex-start!important;
    }
}


/* PREMIUM PAYMENT / BILLING / SUPPORT */
.fh-payment-card,
.tnb-billing-card{
    border:1px solid #dbe5ef!important;
    box-shadow:0 16px 40px rgba(9,31,57,.06)!important;
}
.fh-premium-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:16px;
    width:100%;
}
.fh-premium-head-left{
    display:flex;
    align-items:center;
    gap:12px;
}
.fh-premium-head-icon{
    width:42px;
    height:42px;
    border-radius:13px;
    display:grid;
    place-items:center;
    background:linear-gradient(135deg,#fff1f4,#ffe7ec);
    color:var(--fh-red);
    font-size:16px;
    box-shadow:inset 0 0 0 1px rgba(223,23,61,.08);
}
.fh-premium-head strong{
    display:block;
    color:#10223a;
    font-size:15px;
    font-weight:850;
}
.fh-premium-head small{
    display:block;
    margin-top:3px;
    color:#73859e;
    font-size:10px;
}
.fh-secure-badge{
    display:inline-flex;
    align-items:center;
    gap:7px;
    padding:8px 11px;
    border:1px solid #bfe8d0;
    border-radius:999px;
    background:#effbf4;
    color:#12864a;
    font-size:10px;
    font-weight:800;
    white-space:nowrap;
}
.fh-payment-card .card-body{
    padding:24px!important;
}
.fh-payment-card .tnb-payment-security{
    border:1px solid #ccebd8!important;
    border-radius:14px!important;
    background:linear-gradient(180deg,#f2fcf6,#edf9f2)!important;
    color:#117943!important;
    padding:13px 15px!important;
    font-size:11px!important;
    font-weight:700!important;
}
.fh-card-brand-panel{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:16px;
    padding:14px 16px;
    margin-bottom:18px;
    border:1px solid #e2e9f1;
    border-radius:15px;
    background:#fbfcfe;
}
.fh-card-brand-panel .label{
    color:#657895;
    font-size:11px;
    font-weight:750;
}
.fh-card-brand-panel img{
    max-width:300px!important;
    width:auto!important;
    max-height:30px!important;
}
.fh-payment-card .form-control,
.tnb-billing-card .form-control,
.tnb-billing-card select.form-control{
    min-height:54px!important;
    border-radius:15px!important;
    background:#fbfcfe!important;
}
.fh-payment-card .form-control:focus,
.tnb-billing-card .form-control:focus,
.tnb-billing-card select.form-control:focus{
    background:#fff!important;
    border-color:#91a8c3!important;
    box-shadow:0 0 0 4px rgba(39,79,128,.08)!important;
}
.fh-payment-card .form-label,
.tnb-billing-card .form-label{
    color:#516985!important;
    letter-spacing:.01em;
}
.fh-payment-note{
    display:flex;
    align-items:flex-start;
    gap:10px;
    margin-top:16px;
    padding:13px 15px;
    border-radius:14px;
    background:#f7f9fc;
    color:#72839c;
    font-size:10px;
    line-height:1.45;
}
.fh-payment-note i{
    color:#244c7b;
    margin-top:2px;
}
.tnb-billing-card .card-body{
    padding:24px!important;
}
.fh-billing-intro{
    display:flex;
    align-items:center;
    gap:10px;
    margin-bottom:18px;
    padding:12px 14px;
    border-radius:13px;
    background:#f7f9fc;
    color:#657895;
    font-size:11px;
}
.fh-billing-intro i{color:#244c7b}
.fh-terms{
    padding:14px 16px!important;
    margin:4px 0 14px!important;
    border:1px solid #e0e7f0;
    border-radius:14px;
    background:#fff;
    color:#657895!important;
}
.fh-step-actions{
    align-items:center!important;
}
.fh-step-actions .fh-btn-secondary{
    min-width:112px!important;
    background:#fff!important;
    border:1px solid #d9e3ed!important;
    color:#24405f!important;
    box-shadow:0 7px 18px rgba(9,31,57,.04)!important;
}
.fh-step-actions #submitBtn{
    min-height:54px!important;
    border-radius:14px!important;
    font-size:15px!important;
    letter-spacing:.01em;
}
.fh-help{
    position:relative;
    overflow:hidden;
    padding:0!important;
    border:1px solid #dce6f0!important;
    border-radius:20px!important;
    background:#fff!important;
    box-shadow:0 12px 30px rgba(9,31,57,.06)!important;
}
.fh-help:before{
    content:"";
    position:absolute;
    inset:0 auto 0 0;
    width:4px;
    background:linear-gradient(180deg,var(--fh-red),#ef5a75);
}
.fh-help-inner{
    display:grid;
    grid-template-columns:46px 1fr;
    gap:13px;
    align-items:center;
    padding:17px 18px 16px 20px;
}
.fh-help-icon{
    width:46px;
    height:46px;
    border-radius:14px;
    display:grid;
    place-items:center;
    background:#fff1f4;
    color:var(--fh-red);
    font-size:18px;
}
.fh-help-copy strong{
    margin:0!important;
    color:#11243d!important;
    font-size:13px!important;
}
.fh-help-copy p{
    margin:4px 0 0!important;
    color:#70839e!important;
    font-size:10px!important;
    line-height:1.45!important;
}
.fh-help-meta{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
    padding:0 18px 16px 79px;
}
.fh-help-chip{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:7px 9px;
    border-radius:999px;
    background:#f5f8fb;
    color:#516b88;
    font-size:9px;
    font-weight:750;
}
@media(max-width:767px){
    .fh-premium-head{align-items:flex-start}
    .fh-secure-badge{display:none}
    .fh-card-brand-panel{align-items:flex-start;flex-direction:column}
    .fh-help-meta{padding-left:20px}
}


/* CARD BRAND DUPLICATE FIX */
#creditcard_no ~ .card-brand-badge,
#creditcard_no ~ .cardBrandBadge,
#creditcard_no + .card-brand-badge,
#creditcard_no + .cardBrandBadge,
.card-brand-floating,
.card-brand-overlay,
#cardBrandFloating,
#cardBrandOverlay{
    display:none!important;
}


/* PREMIUM LIVE CARD PREVIEW */
.fh-payment-layout{
    display:grid;
    grid-template-columns:minmax(0,1fr) 360px;
    gap:22px;
    align-items:start;
}
.fh-card-preview-wrap{
    position:sticky;
    top:18px;
}
.fh-card-preview{
    position:relative;
    min-height:215px;
    border-radius:24px;
    padding:24px 24px 22px;
    overflow:hidden;
    background:
        radial-gradient(circle at 85% 15%,rgba(255,255,255,.14),transparent 28%),
        linear-gradient(145deg,#0a1d36 0%,#12385f 55%,#0a2341 100%);
    color:#fff;
    box-shadow:0 22px 45px rgba(7,25,48,.22);
}
.fh-card-preview:before{
    content:"";
    position:absolute;
    width:190px;
    height:190px;
    border-radius:50%;
    right:-70px;
    bottom:-100px;
    background:rgba(255,255,255,.06);
}
.fh-card-preview:after{
    content:"";
    position:absolute;
    width:120px;
    height:120px;
    border-radius:50%;
    right:22px;
    top:-55px;
    background:rgba(255,255,255,.05);
}
.fh-card-preview-top{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:15px;
    position:relative;
    z-index:2;
}
.fh-card-preview-brand{
    font-size:16px;
    font-weight:850;
    letter-spacing:.02em;
}
.fh-card-preview-shield{
    width:34px;
    height:34px;
    border-radius:50%;
    display:grid;
    place-items:center;
    background:rgba(255,255,255,.10);
    border:1px solid rgba(255,255,255,.12);
}
.fh-card-preview-chiprow{
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-top:26px;
    position:relative;
    z-index:2;
}
.fh-card-preview-chip{
    width:44px;
    height:32px;
    border-radius:8px;
    background:linear-gradient(135deg,#d9b766,#f7e4a0);
    box-shadow:inset 0 0 0 1px rgba(78,62,15,.15);
}
.fh-card-preview-contactless{
    color:rgba(255,255,255,.75);
    font-size:18px;
}
.fh-card-preview-number{
    position:relative;
    z-index:2;
    margin-top:26px;
    font-size:22px;
    font-weight:700;
    letter-spacing:.15em;
    white-space:nowrap;
}
.fh-card-preview-bottom{
    position:relative;
    z-index:2;
    display:grid;
    grid-template-columns:1fr auto;
    gap:18px;
    align-items:end;
    margin-top:24px;
}
.fh-card-preview-label{
    display:block;
    margin-bottom:4px;
    color:rgba(255,255,255,.58);
    font-size:8px;
    text-transform:uppercase;
    letter-spacing:.08em;
}
.fh-card-preview-value{
    display:block;
    color:#fff;
    font-size:12px;
    font-weight:700;
    text-transform:uppercase;
    max-width:210px;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
}
.fh-card-preview-note{
    margin-top:12px;
    display:flex;
    align-items:center;
    gap:8px;
    color:#70839e;
    font-size:9px;
    line-height:1.4;
}
.fh-card-preview-note i{color:#2f5f95}
.fh-brand-pill{
    display:inline-flex;
    align-items:center;
    gap:7px;
    margin-top:10px;
    padding:7px 10px;
    border-radius:999px;
    background:#f4f7fb;
    color:#546a85;
    font-size:10px;
    font-weight:800;
}
.fh-brand-pill.detected{
    background:#edf9f2;
    color:#17834d;
}
@media(max-width:1100px){
    .fh-payment-layout{
        grid-template-columns:1fr;
    }
    .fh-card-preview-wrap{
        position:static;
        order:-1;
    }
}
@media(max-width:480px){
    .fh-card-preview{
        min-height:195px;
        padding:20px;
    }
    .fh-card-preview-number{
        font-size:18px;
        letter-spacing:.12em;
    }
}


/* =========================================================
   FARESHUNT BOOKING MOBILE PASS 1
   Focused checkout UX for <= 760px
   ========================================================= */
@media(max-width:760px){

    /* Checkout should be focused: no fare timer or promotional strip. */
body.tnb-checkout-active .fh-offer-rotator{
        display:none!important;
    }

    body.tnb-checkout-active{
        overflow-x:hidden!important;
    }

    body.tnb-checkout-active .fh-checkout{
        padding:10px 0 0!important;
        margin:0!important;
        min-height:auto!important;
    }

    body.tnb-checkout-active .fh-checkout>.container{
        width:100%!important;
        max-width:100%!important;
        padding-left:10px!important;
        padding-right:10px!important;
    }

    /* Compact 3-step progress */
    .fh-progress{
        width:100%!important;
        max-width:430px!important;
        grid-template-columns:auto 1fr auto 1fr auto!important;
        gap:6px!important;
        margin:0 auto 8px!important;
        padding:0 6px!important;
    }

    .fh-progress-line{
        margin-top:18px!important;
    }

    .fh-progress-step{
        gap:5px!important;
        font-size:10px!important;
        line-height:1.15!important;
        white-space:nowrap!important;
    }

    .fh-progress-step .dot{
        width:38px!important;
        height:38px!important;
        font-size:13px!important;
    }

    .fh-secure{
        margin:8px 0 16px!important;
        gap:7px!important;
        font-size:10px!important;
        line-height:1.35!important;
        text-align:center!important;
        flex-wrap:wrap!important;
    }

    /* Main content */
    .fh-layout{
        display:block!important;
        width:100%!important;
        margin:0!important;
    }

    .fh-main,
    .fh-summary{
        width:100%!important;
        max-width:100%!important;
        min-width:0!important;
    }

    .fh-main h2{
        margin:0 4px 10px!important;
        font-size:21px!important;
        line-height:1.2!important;
    }

    .fh-kicker{
        margin:0 4px 12px!important;
        font-size:11px!important;
    }

    /* Passenger/contact cards */
    .fh-card{
        border-radius:18px!important;
        margin-bottom:14px!important;
        box-shadow:0 8px 22px rgba(7,28,53,.045)!important;
    }

    .fh-card .card-header{
        padding:14px 14px!important;
    }

    .fh-card .card-body{
        padding:14px!important;
    }

    .fh-pax-head{
        gap:10px!important;
        align-items:flex-start!important;
    }

    .fh-pax-icon{
        width:34px!important;
        height:34px!important;
        border-radius:10px!important;
        flex:0 0 34px!important;
    }

    .fh-pax-head strong{
        font-size:13px!important;
    }

    .fh-pax-head small{
        font-size:10px!important;
        line-height:1.4!important;
    }

    .fh-checkout .row{
        --bs-gutter-x:10px!important;
    }

    .fh-checkout .form-group{
        margin-bottom:12px!important;
    }

    .fh-checkout .form-label{
        margin-bottom:6px!important;
        font-size:10px!important;
    }

    .fh-checkout .form-control,
    .fh-checkout .form-select{
        min-height:48px!important;
        height:48px!important;
        border-radius:12px!important;
        padding-left:13px!important;
        padding-right:13px!important;
        font-size:16px!important; /* prevents iOS zoom */
    }

    /* Phone row should remain usable on 390px. */
    .fh-checkout .input-group,
    .fh-checkout .fh-phone-group{
        display:flex!important;
        width:100%!important;
        gap:8px!important;
    }

    .fh-checkout #flight_tel_code{
        flex:0 0 104px!important;
        width:104px!important;
        max-width:104px!important;
    }

    .fh-checkout #flight_mobile{
        flex:1 1 auto!important;
        min-width:0!important;
    }

    /* Primary step button */
    .fh-step-panel[data-step-panel="1"] .fh-btn-primary{
        min-height:52px!important;
        border-radius:13px!important;
        margin-top:2px!important;
        font-size:14px!important;
    }

    /* Summary should feel like part of checkout, not a second huge page. */
    .fh-summary{
        position:static!important;
        margin-top:18px!important;
    }

    .fh-trip-card{
        border-radius:18px!important;
        box-shadow:0 8px 24px rgba(8,28,52,.07)!important;
    }

    .fh-trip-head{
        padding:16px!important;
    }

    .fh-trip-route{
        display:block!important;
        margin-top:6px!important;
    }

    .fh-trip-route strong{
        display:block!important;
        font-size:22px!important;
        line-height:1.1!important;
    }

    .fh-trip-route span{
        display:block!important;
        margin-top:5px!important;
        font-size:10px!important;
    }

    .fh-itin{
        padding:0 14px!important;
    }

    .fh-leg{
        padding:16px 0!important;
    }

    .fh-leg-top{
        margin-bottom:10px!important;
    }

    .fh-segment{
        grid-template-columns:44px 1fr!important;
        gap:9px!important;
    }

    .fh-airline-logo{
        width:40px!important;
        height:40px!important;
    }

    .fh-airline-meta strong{
        font-size:12px!important;
    }

    .fh-time-row{
        grid-template-columns:1fr 90px 1fr!important;
        gap:7px!important;
        margin-top:11px!important;
    }

    .fh-time b{
        font-size:18px!important;
    }

    .fh-flight-line small{
        font-size:9px!important;
        line-height:1.2!important;
    }

    .fh-fares{
        padding:15px 14px!important;
    }

    .fh-fare-row{
        padding:6px 0!important;
        font-size:11px!important;
    }

    .fh-total{
        margin-top:7px!important;
        padding-top:10px!important;
    }

    .fh-total strong{
        font-size:27px!important;
        white-space:nowrap!important;
    }

    .fh-total-note{
        font-size:9px!important;
    }

    .fh-help{
        margin-top:12px!important;
        border-radius:16px!important;
        padding:14px!important;
    }

    /* Existing help card makes the floating support button redundant on checkout. */
    body.tnb-checkout-active .fh-support-widget{
        display:none!important;
    }

    /* Back-to-top overlaps CTA/price on checkout mobile. */
    body.tnb-checkout-active #backToTopBtn,
    body.tnb-checkout-active .back-to-top,
    body.tnb-checkout-active .scroll-top{
        display:none!important;
    }

    /* Trust strip at bottom */
    .fh-trust{
        margin-top:22px!important;
    }

    .fh-trust .inner{
        grid-template-columns:1fr 1fr!important;
        gap:12px!important;
        padding:18px 14px!important;
    }

    .fh-trust-item{
        gap:9px!important;
        align-items:flex-start!important;
    }

    .fh-trust-icon{
        width:34px!important;
        height:34px!important;
        flex:0 0 34px!important;
    }

    .fh-trust-item strong{
        font-size:10px!important;
    }

    .fh-trust-item small{
        font-size:9px!important;
        line-height:1.35!important;
    }

    /* Footer starts closer to checkout end. */
    body.tnb-checkout-active footer{
        margin-top:0!important;
    }
}

@media(max-width:430px){
    .fh-checkout>.container{
        padding-left:7px!important;
        padding-right:7px!important;
    }

    .fh-progress{
        gap:4px!important;
    }

    .fh-progress-step{
        font-size:9px!important;
    }

    .fh-progress-step .dot{
        width:36px!important;
        height:36px!important;
    }

    .fh-time-row{
        grid-template-columns:1fr 76px 1fr!important;
    }

    .fh-trust .inner{
        grid-template-columns:1fr 1fr!important;
    }
}


/* =========================================================
   FARESHUNT BOOKING MOBILE PASS 2
   Add-ons + Payment + Billing refinement
   ========================================================= */
@media(max-width:760px){

    /* Remove the remaining dead space between progress and active step. */
    .fh-secure{
        margin-bottom:6px!important;
    }
    .fh-layout{
        margin-top:0!important;
        padding-top:0!important;
    }
    .fh-main{
        margin-top:0!important;
        padding-top:0!important;
    }
    .fh-step-panel.active{
        margin-top:0!important;
        padding-top:0!important;
    }

    /* ---------- Add-ons ---------- */
    .fh-step-panel[data-step-panel="2"] .fh-kicker{
        margin-bottom:10px!important;
    }

    .fh-addon-card{
        margin-bottom:12px!important;
        border-radius:18px!important;
    }

    .fh-addon-card .fh-addon-choice{
        min-height:0!important;
        display:grid!important;
        grid-template-columns:1fr!important;
        gap:10px!important;
        padding:15px 14px!important;
        align-items:start!important;
    }

    .fh-addon-card .fh-addon-title{
        font-size:14px!important;
        line-height:1.25!important;
        gap:6px!important;
    }

    .fh-addon-card .fh-helper{
        margin-top:4px!important;
        font-size:10.5px!important;
        line-height:1.4!important;
    }

    /* Price + action live on one compact row. */
    .fh-addon-card .fh-addon-choice > .text-end{
        width:100%!important;
        display:grid!important;
        grid-template-columns:auto minmax(0,1fr)!important;
        gap:10px!important;
        align-items:center!important;
        text-align:left!important;
    }

    .fh-addon-card .fh-addon-price{
        min-width:70px!important;
        margin:0!important;
        text-align:left!important;
        font-size:22px!important;
        line-height:1!important;
        white-space:nowrap!important;
    }

    .fh-addon-card .fh-addon-price small{
        max-width:92px!important;
        margin-top:4px!important;
        font-size:8.5px!important;
        line-height:1.25!important;
        white-space:normal!important;
    }

    .fh-addon-card .fh-choice-buttons,
    .fh-addon-card .text-end .fh-choice-buttons{
        width:100%!important;
        display:flex!important;
        justify-content:flex-end!important;
        flex-wrap:wrap!important;
        gap:6px!important;
        margin:0!important;
    }

    .fh-addon-card .btn-check + .btn,
    .fh-addon-card .fh-premium-addon + .btn{
        min-width:112px!important;
        min-height:40px!important;
        padding:8px 11px!important;
        font-size:11px!important;
        border-radius:11px!important;
    }

    .fh-step-panel[data-step-panel="2"] .fh-step-actions{
        display:grid!important;
        grid-template-columns:110px minmax(0,1fr)!important;
        gap:8px!important;
        margin:12px 0 18px!important;
    }

    .fh-step-panel[data-step-panel="2"] .fh-btn-secondary,
    .fh-step-panel[data-step-panel="2"] .fh-btn-primary{
        width:100%!important;
        min-width:0!important;
        padding:0 10px!important;
        min-height:50px!important;
    }

    /* ---------- Payment ---------- */
    .fh-step-panel[data-step-panel="3"] > h2{
        margin-bottom:10px!important;
    }

    .fh-payment-card{
        margin-bottom:14px!important;
    }

    .fh-payment-card .card-header{
        padding:12px!important;
    }

    .fh-premium-head-left{
        gap:9px!important;
    }

    .fh-premium-head-icon{
        width:36px!important;
        height:36px!important;
        flex:0 0 36px!important;
        border-radius:10px!important;
    }

    .fh-premium-head-left strong{
        font-size:12px!important;
    }

    .fh-premium-head-left small{
        display:none!important;
    }

    .fh-payment-card .card-body{
        padding:12px!important;
    }

    .fh-payment-layout{
        display:flex!important;
        flex-direction:column!important;
        gap:10px!important;
    }

    /*
     * Keep the live card preview ABOVE the card-entry fields on mobile,
     * matching the intended payment-page hierarchy.
     */
    .fh-payment-form{
        order:2!important;
        width:100%!important;
    }

    .fh-card-preview-wrap{
        order:1!important;
        position:static!important;
        width:100%!important;
        margin:0 0 10px!important;
    }

    .fh-card-preview{
        display:block!important;
        width:100%!important;
        max-width:360px!important;
        min-height:0!important;
        height:auto!important;
        aspect-ratio:1.62 / 1!important;
        margin:0 auto 10px!important;
        border-radius:18px!important;
        padding:18px!important;
        transform:none!important;
    }

    .fh-card-preview .fh-card-title{
        font-size:18px!important;
    }

    .fh-card-preview .fh-chip{
        width:50px!important;
        height:36px!important;
    }

    .fh-card-preview .fh-card-number{
        font-size:18px!important;
        letter-spacing:3px!important;
        margin-top:22px!important;
    }

    .fh-card-preview .fh-card-bottom{
        margin-top:18px!important;
        gap:10px!important;
    }

    .fh-card-preview .fh-card-bottom small{
        font-size:8px!important;
    }

    .fh-card-preview .fh-card-bottom strong{
        font-size:13px!important;
    }

    .fh-card-preview-note{
        display:flex!important;
        margin:0 0 8px!important;
        font-size:9px!important;
        line-height:1.4!important;
    }

    .fh-brand-pill{
        display:flex!important;
        width:max-content!important;
        max-width:100%!important;
        margin:2px 0 0!important;
        padding:7px 10px!important;
        font-size:9px!important;
    }

    .fh-card-brand-panel{
        padding:10px!important;
        margin-bottom:12px!important;
        border-radius:12px!important;
    }

    .fh-card-brand-panel .label{
        font-size:10px!important;
    }

    .fh-card-brand-panel img{
        width:auto!important;
        max-width:100%!important;
        max-height:32px!important;
    }

    .fh-payment-card .form-group.row{
        margin-bottom:11px!important;
    }

    .fh-payment-card .form-group.row > .col-5{
        margin-bottom:5px!important;
    }

    .fh-payment-card .form-control{
        min-height:48px!important;
        height:48px!important;
    }

    /* Month / Year / CVV on one row instead of three full-screen rows. */
    .fh-payment-card .form-group.row .col-7 > .row.g-2{
        display:grid!important;
        grid-template-columns:1fr 1fr 1fr!important;
        gap:7px!important;
        margin:0!important;
    }

    .fh-payment-card .form-group.row .col-7 > .row.g-2 > .col-md-4{
        width:100%!important;
        max-width:100%!important;
        padding:0!important;
        margin:0!important;
    }

    .fh-payment-card #expire_month,
    .fh-payment-card #expire_year,
    .fh-payment-card #cvv{
        padding-left:9px!important;
        padding-right:9px!important;
        font-size:15px!important;
    }

    .fh-payment-note{
        margin:10px 0 0!important;
        padding:10px!important;
        font-size:9.5px!important;
        line-height:1.4!important;
        border-radius:12px!important;
    }

    /* ---------- Billing ---------- */
    .fh-step-panel[data-step-panel="3"] > h2:nth-of-type(2){
        margin-top:14px!important;
        margin-bottom:10px!important;
    }

    .tnb-billing-card{
        margin-bottom:12px!important;
    }

    /* Outer H2 already says Billing Information, so remove duplicate card heading on phone. */
    .tnb-billing-card > .card-header{
        display:none!important;
    }

    .tnb-billing-card .card-body{
        padding:12px!important;
    }

    .fh-billing-intro{
        margin-bottom:12px!important;
        padding:10px!important;
        border-radius:12px!important;
        font-size:10px!important;
        line-height:1.4!important;
    }

    .tnb-billing-card .row.g-3{
        --bs-gutter-y:10px!important;
    }

    .tnb-billing-card .form-group{
        margin-bottom:0!important;
    }

    .tnb-billing-card .d-flex.gap-2{
        gap:7px!important;
    }

    .tnb-billing-card #billing_mobile_telcode{
        flex:0 0 96px!important;
        width:96px!important;
        max-width:96px!important;
    }

    .fh-terms{
        margin:10px 3px 12px!important;
        padding:11px!important;
        border:1px solid #dce5ef!important;
        border-radius:12px!important;
        background:#fff!important;
        font-size:9.5px!important;
        line-height:1.45!important;
    }

    .fh-step-panel[data-step-panel="3"] .fh-step-actions{
        display:grid!important;
        grid-template-columns:105px minmax(0,1fr)!important;
        gap:8px!important;
        margin:0 0 18px!important;
    }

    .fh-step-panel[data-step-panel="3"] .fh-btn-secondary,
    .fh-step-panel[data-step-panel="3"] #submitBtn{
        width:100%!important;
        min-width:0!important;
        min-height:52px!important;
        margin:0!important;
        padding:0 10px!important;
    }

    /*
     * Checkout already contains legal links, support, trust and contact cues.
     * The full marketing footer adds several phone screens and distracts from
     * conversion, so suppress it only during mobile checkout.
     */
    body.tnb-checkout-active footer,
    body.tnb-checkout-active .fh-reference-footer,
    body.tnb-checkout-active .tnb-footer{
        display:none!important;
    }

    .fh-trust{
        margin-top:16px!important;
    }
}

@media(max-width:390px){
    .fh-addon-card .fh-addon-choice > .text-end{
        grid-template-columns:64px minmax(0,1fr)!important;
    }

    .fh-addon-card .btn-check + .btn,
    .fh-addon-card .fh-premium-addon + .btn{
        min-width:104px!important;
        padding-left:8px!important;
        padding-right:8px!important;
    }

    .fh-payment-card .form-group.row .col-7 > .row.g-2{
        gap:5px!important;
    }
}


/* =========================================================
   MOBILE CARD PREVIEW FINAL FIX
   Prevents CARDHOLDER / EXPIRES values from being clipped.
   ========================================================= */
@media(max-width:760px){
    .fh-card-preview{
        display:flex!important;
        flex-direction:column!important;
        justify-content:space-between!important;
        width:100%!important;
        max-width:360px!important;

        /* Do NOT use the previous short aspect-ratio height on phones. */
        aspect-ratio:auto!important;
        height:238px!important;
        min-height:238px!important;

        margin:0 auto 10px!important;
        padding:18px 20px 16px!important;
        overflow:hidden!important;
        border-radius:18px!important;
    }

    .fh-card-preview .fh-card-top{
        flex:0 0 auto!important;
    }

    .fh-card-preview .fh-card-number{
        flex:0 0 auto!important;
        margin:12px 0 0!important;
        font-size:18px!important;
        line-height:1.2!important;
        letter-spacing:2.6px!important;
        white-space:nowrap!important;
    }

    .fh-card-preview .fh-card-bottom{
        flex:0 0 auto!important;
        display:grid!important;
        grid-template-columns:minmax(0,1fr) auto!important;
        align-items:end!important;
        gap:14px!important;
        width:100%!important;
        margin:0!important;
        padding:0!important;
    }

    .fh-card-preview .fh-card-bottom > div{
        min-width:0!important;
    }

    .fh-card-preview .fh-card-bottom > div:last-child{
        text-align:right!important;
    }

    .fh-card-preview .fh-card-bottom small{
        display:block!important;
        margin-bottom:4px!important;
        font-size:8px!important;
        line-height:1.1!important;
        letter-spacing:.05em!important;
    }

    .fh-card-preview .fh-card-bottom strong{
        display:block!important;
        max-width:100%!important;
        font-size:13px!important;
        line-height:1.15!important;
        white-space:nowrap!important;
        overflow:hidden!important;
        text-overflow:ellipsis!important;
    }

    /* Make sure decorative pseudo-elements never sit over the bottom values. */
    .fh-card-preview::before,
    .fh-card-preview::after{
        pointer-events:none!important;
    }
}

@media(max-width:390px){
    .fh-card-preview{
        height:232px!important;
        min-height:232px!important;
        padding:17px 18px 15px!important;
    }

    .fh-card-preview .fh-card-number{
        font-size:17px!important;
        letter-spacing:2.2px!important;
    }

    .fh-card-preview .fh-card-bottom strong{
        font-size:12px!important;
    }
}


/* =========================================================
   FARESHUNT V10 FINAL QA FIXES
   ========================================================= */
body.tnb-checkout-active .fh-checkout{position:relative;overflow:hidden;padding-top:18px!important}
body.tnb-checkout-active .fh-checkout>.container{position:relative;z-index:1}
body.tnb-checkout-active .fh-checkout:before{content:"✈";position:absolute;right:4vw;top:140px;font-size:210px;line-height:1;color:rgba(11,43,78,.025);transform:rotate(-13deg);pointer-events:none;z-index:0}
.fh-progress{margin-bottom:8px!important}.fh-secure{margin:0 0 14px!important}.fh-layout{margin-top:0!important;padding-top:0!important}.fh-main,.fh-step-panel.active{margin-top:0!important;padding-top:0!important}.fh-main h2{margin-top:0!important}
@media(min-width:1200px){.fh-layout{gap:26px!important}.fh-summary{top:14px!important}.fh-step-panel.active>h2{margin-bottom:12px!important}.fh-checkout{padding-bottom:26px!important}}
.fh-hidden-optout{display:none!important}
.fh-addon-card .fh-choice-buttons{justify-content:flex-end!important}.fh-addon-card .fh-choice-buttons .btn{min-width:126px}
#addon_web,#addon_can,#addon_premium{display:block!important;padding:0!important}.fh-addon-line{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;padding:7px 0;color:#657895;font-size:12px}.fh-addon-line+.fh-addon-line{border-top:1px dashed #edf1f5}.fh-addon-line span{min-width:0;overflow-wrap:anywhere}.fh-addon-line strong{flex:0 0 auto;color:#17263c;white-space:nowrap}
.fh-help-actions{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin:14px 0 10px}.fh-help-contact{display:flex;align-items:center;gap:10px;min-height:58px;padding:9px 12px;border:1px solid #dfe7f0;border-radius:14px;background:#fff;text-decoration:none!important;color:#17273f!important;transition:.18s ease}.fh-help-contact:hover{transform:translateY(-1px);box-shadow:0 8px 20px rgba(8,30,55,.07)}.fh-help-contact>i{width:34px;height:34px;display:grid;place-items:center;border-radius:10px;background:#f7f9fc}.fh-help-contact span{display:flex;flex-direction:column;min-width:0}.fh-help-contact small{font-size:9px;color:#6f819b}.fh-help-contact b{font-size:11px;white-space:nowrap}.fh-help-contact.call b{color:#d6153c}.fh-help-contact.whatsapp i,.fh-help-contact.whatsapp b{color:#16a05b}.fh-help-meta{display:flex;flex-wrap:wrap;gap:7px!important}.fh-help-chip{text-decoration:none!important}
/* Premium DOB picker uses the same language as the homepage calendar. */
.fh-dob-picker[readonly]{background:#f9fbfd!important;cursor:pointer}.flatpickr-calendar.fh-dob-calendar{border:1px solid #dfe7ef!important;border-radius:18px!important;box-shadow:0 24px 60px rgba(7,31,55,.18)!important;padding:8px!important;background:#fff!important;z-index:2147483646!important;box-sizing:border-box!important;max-width:calc(100vw - 24px)!important}
@media(min-width:761px){.flatpickr-calendar.fh-dob-calendar{width:340px!important;min-width:340px!important;max-width:min(340px,calc(100vw - 24px))!important;position:fixed!important;right:auto!important;bottom:auto!important;transform:none!important}}.fh-dob-calendar .flatpickr-months{padding:5px 0 8px}.fh-dob-calendar .flatpickr-current-month{font-size:14px!important;font-weight:800!important}.fh-dob-calendar .flatpickr-current-month .flatpickr-monthDropdown-months,.fh-dob-calendar .flatpickr-current-month input.cur-year{font-weight:800!important}.fh-dob-calendar .flatpickr-day{border-radius:10px!important}.fh-dob-calendar .flatpickr-day.selected{background:#d6153c!important;border-color:#d6153c!important}.fh-dob-calendar .flatpickr-day:hover{background:#f8edf0!important;border-color:#f8edf0!important}
@media(max-width:760px){body.tnb-checkout-active .fh-checkout:before{display:none}.fh-help-actions{grid-template-columns:1fr}.fh-help-contact{min-height:54px}.fh-progress{margin-bottom:6px!important}.fh-secure{margin-bottom:8px!important}.flatpickr-calendar.fh-dob-calendar{position:fixed!important;left:8px!important;right:8px!important;bottom:8px!important;top:auto!important;width:auto!important;max-width:none!important}.fh-dob-calendar .flatpickr-days,.fh-dob-calendar .dayContainer{width:100%!important;min-width:100%!important;max-width:100%!important}.fh-dob-calendar .flatpickr-day{max-width:none!important;flex-basis:14.2857%!important}}
</style>

<div id="screenBlocker" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
     background: rgba(255, 255, 255, 0.6); z-index: 99998; cursor: not-allowed;"></div>

<div id="loadingSubmit">
    <div>Please wait while your booking is being created.</div>
    <img src="/assets/images/loading.gif" alt="Loading..." style="width: 50px; margin-top: 10px;">
</div>


<section class="fh-checkout">
<div class="container">

    <div class="fh-progress" id="fhProgress">
        <div class="fh-progress-step active" data-step-indicator="1">
            <span class="dot"><i class="fa-regular fa-user"></i></span><span>Passengers</span>
        </div>
        <span class="fh-progress-line"></span>
        <div class="fh-progress-step" data-step-indicator="2">
            <span class="dot"><i class="fa-solid fa-box-open"></i></span><span>Add-ons</span>
        </div>
        <span class="fh-progress-line"></span>
        <div class="fh-progress-step" data-step-indicator="3">
            <span class="dot"><i class="fa-regular fa-credit-card"></i></span><span>Payment</span>
        </div>
    </div>

    <div class="fh-secure">
        <i class="fa-solid fa-lock"></i>
        <span>Secure checkout · SSL encrypted transaction</span>
        <i class="fa-solid fa-shield-halved"></i>
    </div>

    @if(Session::get('Booking-Error-Msz'))
        <div class="alert alert-danger">
            {{Session::get('Booking-Error-Msz')}}
            @php session(['Booking-Error-Msz'=>'']); @endphp
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    @php
        $count_adult    = (int)@$fields['Adults'];
        $count_children = (int)@$fields['Childrens'];
        $count_infant   = (int)@$fields['Infants'];
        $total_pass     = $count_adult + $count_children + $count_infant;
        $loop           = $total_pass - 1;
        $newarray       = [];

        for($l=0;$l<$count_adult;$l++){ $newarray[$l] = "Adult"; }
        for($p=0;$p<$count_children;$p++){ $newarray[$l] = "Child"; $l++; }
        for($p=0;$p<$count_infant;$p++){ $newarray[$l] = "Infant"; $l++; }

        $departSegments = @$resultSelected['AllFlight']['Rph_1'];
        $returnSegments = @$resultSelected['AllFlight']['Rph_2'];

        if(!is_array($departSegments)) $departSegments = [];
        if(!is_array($returnSegments)) $returnSegments = [];

        $firstDepart = $departSegments[0] ?? [];
        $lastDepart  = count($departSegments) ? $departSegments[count($departSegments)-1] : [];
        $firstReturn = $returnSegments[0] ?? [];
        $lastReturn  = count($returnSegments) ? $returnSegments[count($returnSegments)-1] : [];

        $fmtDate = function($raw,$format='D, M d'){
            if(!$raw) return '';
            try { return \Carbon\Carbon::createFromFormat('d.m.Y H:i',$raw)->format($format); }
            catch(\Throwable $e){
                try { return \Carbon\Carbon::parse($raw)->format($format); }
                catch(\Throwable $e2){ return ''; }
            }
        };
        $fmtTime = function($raw) use ($fmtDate){ return $fmtDate($raw,'h:i A'); };

        $durationText = function($segments){
            $mins = 0;
            foreach($segments as $s){ $mins += (int)($s['FlightMinutes'] ?? 0); }
            if(!$mins) return '';
            return floor($mins/60).'h '.($mins%60).'m';
        };

        $stopsText = function($segments){
            $stops = max(0,count($segments)-1);
            return $stops===0 ? 'Direct' : ($stops===1 ? '1 stop' : $stops.' stops');
        };

        if($resultSelected['Is_Markup']=='No'){
            $total_price = $resultSelected['TotalPrice'];
        }else{
            $total_price = $resultSelected['MinMarkup']['TotalPrice'];
        }

        $service = array();
        $total_pax_charge = (int)$fields['Adults'] + (int)$fields['Childrens'];
        $FlightType = @$fields['FlightType'];
        $way = $FlightType=='round_trip' ? 2 : 1;
        $total_count = $total_pax_charge*$way;
        $web_checkin_cost = $setting['booking_web_checkin']*$total_count;
        $cancellation_cost = $setting['booking_cancellation_protection']*$total_pax_charge;

        $service['Web_Check_In'] = [
            'Enable'=>'No','Price'=>$setting['booking_web_checkin'],
            'Pax'=>$total_pax_charge,'Way'=>$way,'SubTotal'=>$web_checkin_cost
        ];
        $service['Cancellation'] = [
            'Enable'=>'No','Price'=>$setting['booking_cancellation_protection'],
            'Pax'=>$total_pax_charge,'Way'=>$way,'SubTotal'=>$cancellation_cost
        ];

        // Premium add-ons are managed from CRM Booking Add-ons master data.
        $premium_addons = DB::table('booking_addons')
            ->where('is_active', 1)
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();

        foreach ($premium_addons as $addon) {
            $service[$addon->key] = [
                'Enable' => 'No',
                'Price' => (float) $addon->price,
                'SubTotal' => (float) $addon->price,
            ];
        }

        $service_decoded = json_encode($service);
    @endphp

    <form name="form4" id="form4" method="post" action="javascript:void(0)" onsubmit="return false;">
        @csrf
        <input type="hidden" id="OfferCode" name="OfferCode" value="{{$OfferCode}}" />
        <input type="hidden" id="SearchGuid" name="SearchGuid" value="{{$SearchGuid}}" />
        <input type="hidden" id="priceinfo" name="priceinfo" value="" />
        <input type="hidden" id="field" name="field" value="" />
        <input type="hidden" id="code" name="code" value="{{$code}}" />
        <input type="hidden" id="key" name="key" value="{{$key}}" />
        <input type="hidden" name="priceMarkup" value="{{json_encode($resultSelected['priceInfo'])}}" />
        <input type="hidden" name="infoMarkup" value="{{json_encode($resultSelected['priceMarkup'])}}" />
        <input type="hidden" name="o_FullPrice" value="" />
        <input type="hidden" name="i_field_encode" id="i_field_encode" value="{{json_encode($fields)}}" />
        <textarea name="resultSelected" id="resultSelected" hidden>{{json_encode($resultSelected)}}</textarea>
        <input type="hidden" name="loop" id="loop" value="{{$loop}}" />
        <textarea name="service" id="service" hidden>{{$service_decoded}}</textarea>
        <input type="hidden" name="is_markup" id="is_markup" value="{{@$resultSelected['Is_Markup']}}" />
        <input type="hidden" value="Yes" id="tnc" name="tnc">
        <input type="hidden" id="total_price" name="total_price" value="{{$total_price}}" />

        <div class="fh-layout">
            <main class="fh-main">
            <!-- Rich Premium: Flight itinerary -->
            <section class="fh-premium-itinerary-card">
                <div class="fh-premium-itinerary-heading">
                    <span>YOUR FLIGHT DETAILS</span>
                    <h2>Review Your Journey</h2>
                </div>
<div class="fh-itin">
                        @if(count($departSegments))
                        <div class="fh-leg">
                            <div class="fh-leg-top">
                                <strong>Departure</strong><span>{{$fmtDate($firstDepart['Departure_Date'] ?? '')}}</span>
                            </div>
                            <div class="fh-segment">
                                <div class="fh-airline-logo">
                                    @if(!empty($firstDepart['AirlineLogo']))
                                        <img src="{{$firstDepart['AirlineLogo']}}" alt="{{$firstDepart['AirlineName'] ?? 'Airline'}}">
                                    @else
                                        <span>{{strtoupper($firstDepart['Airline'] ?? '')}}</span>
                                    @endif
                                </div>
                                <div class="fh-airline-meta">
                                    <strong>{{$firstDepart['AirlineName'] ?? ($firstDepart['Airline'] ?? 'Airline')}}</strong>
                                    <small>{{$firstDepart['FlightNum'] ?? ''}}</small>
                                </div>
                                <div class="fh-time-row">
                                    <div class="fh-time">
                                        <b>{{$fmtTime($firstDepart['Departure_Date'] ?? '')}}</b>
                                        <span>{{$firstDepart['Departure_Iata'] ?? ''}}</span>
                                    </div>
                                    <div class="fh-flight-line">
                                        <div class="line"></div><i class="fa-solid fa-plane"></i>
                                        <small>{{$stopsText($departSegments)}} · {{$durationText($departSegments)}}</small>
                                    </div>
                                    <div class="fh-time right">
                                        <b>{{$fmtTime($lastDepart['Arrival_Date'] ?? '')}}</b>
                                        <span>{{$lastDepart['Arrival_Iata'] ?? ''}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if(count($returnSegments))
                        <div class="fh-leg">
                            <div class="fh-leg-top">
                                <strong>Return</strong><span>{{$fmtDate($firstReturn['Departure_Date'] ?? '')}}</span>
                            </div>
                            <div class="fh-segment">
                                <div class="fh-airline-logo">
                                    @if(!empty($firstReturn['AirlineLogo']))
                                        <img src="{{$firstReturn['AirlineLogo']}}" alt="{{$firstReturn['AirlineName'] ?? 'Airline'}}">
                                    @else
                                        <span>{{strtoupper($firstReturn['Airline'] ?? '')}}</span>
                                    @endif
                                </div>
                                <div class="fh-airline-meta">
                                    <strong>{{$firstReturn['AirlineName'] ?? ($firstReturn['Airline'] ?? 'Airline')}}</strong>
                                    <small>{{$firstReturn['FlightNum'] ?? ''}}</small>
                                </div>
                                <div class="fh-time-row">
                                    <div class="fh-time">
                                        <b>{{$fmtTime($firstReturn['Departure_Date'] ?? '')}}</b>
                                        <span>{{$firstReturn['Departure_Iata'] ?? ''}}</span>
                                    </div>
                                    <div class="fh-flight-line">
                                        <div class="line"></div><i class="fa-solid fa-plane"></i>
                                        <small>{{$stopsText($returnSegments)}} · {{$durationText($returnSegments)}}</small>
                                    </div>
                                    <div class="fh-time right">
                                        <b>{{$fmtTime($lastReturn['Arrival_Date'] ?? '')}}</b>
                                        <span>{{$lastReturn['Arrival_Iata'] ?? ''}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                                </section>



                <!-- STEP 1 -->
                <section class="fh-step-panel active" data-step-panel="1">
                    <h2>Passenger Details</h2>

                    @for($k=0;$k<=$total_pass-1;$k++)
                    <div class="card fh-card mb-4">
                        <div class="card-header">
                            <div class="fh-pax-head">
                                <span class="fh-pax-icon"><i class="fa-regular fa-user"></i></span>
                                <span>
                                    <strong>Passenger {{$k+1}} — {{$newarray[$k]}}</strong>
                                    <small>Enter traveler details exactly as shown on the travel document</small>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <input type="hidden" name="AgeType_{{$k}}" value="{{$newarray[$k]}}" />
                            <div class="row g-3">
                                <div class="col-lg-3 col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Title</label>
                                        <select required name="adult_title_{{$k}}" class="form-control">
                                            <option value="">Select</option>
                                            <option value="Mr" @selected(@$Booking_Form["adult_title_".$k]=='Mr')>Mr</option>
                                            <option value="Mrs" @selected(@$Booking_Form["adult_title_".$k]=='Mrs')>Mrs</option>
                                            <option value="Miss" @selected(@$Booking_Form["adult_title_".$k]=='Miss')>Miss</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">First Name</label>
                                        <input required name="adult_fname_{{$k}}" type="text" maxlength="50" class="form-control" placeholder="As on ID"
                                               value='{{@$Booking_Form["adult_fname_".$k]}}'>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Middle Name</label>
                                        <input name="adult_mname_{{$k}}" type="text" maxlength="50" class="form-control" placeholder="Optional"
                                               value='{{@$Booking_Form["adult_mname_".$k]}}'>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Last Name</label>
                                        <input required name="adult_lname_{{$k}}" type="text" maxlength="50" class="form-control" placeholder="As on ID"
                                               value='{{@$Booking_Form["adult_lname_".$k]}}'>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Gender</label>
                                        <select required name="adult_gender_{{$k}}" class="form-control">
                                            <option value="">Select Gender</option>
                                            <option value="Male" @selected(@$Booking_Form["adult_gender_".$k]=='Male')>Male</option>
                                            <option value="Female" @selected(@$Booking_Form["adult_gender_".$k]=='Female')>Female</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label class="form-label">Date of Birth</label>
                                        <input required name="adult_dob_{{$k}}" class="form-control fh-dob-picker" data-age-type="{{$newarray[$k]}}" type="text" inputmode="numeric" placeholder="MM/DD/YYYY"
                                               value='{{@$Booking_Form["adult_dob_".$k]}}'>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endfor

                    <h2>Contact Information</h2>
                    <div class="card fh-card mb-4">
                        <div class="card-body">
                            <p class="fh-kicker">Booking confirmation and e-ticket updates will be sent using these details.</p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" required name="flight_emailid" id="flight_emailid" class="form-control"
                                               placeholder="you@example.com" value="{{@$Booking_Form['flight_emailid']}}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Contact Number</label>
                                        <div class="d-flex gap-2">
                                            <select class="form-control" name="flight_mobile_telcode" id="flight_mobile_telcode" required style="max-width:96px">
                                                @if($telcode)
                                                    @foreach($telcode as $telcodes)
                                                    <option value="{{$telcodes->code}}"
                                                        @if(@$Booking_Form["flight_mobile_telcode"]==$telcodes->code) selected
                                                        @else @if($telcodes->id=='2') selected @endif @endif
                                                    >{{$telcodes->code}}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <input type="text" required name="flight_mobile" id="flight_mobile" class="form-control"
                                                   maxlength="10" placeholder="Phone number" value="{{@$Booking_Form['flight_mobile']}}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="fh-btn-primary" data-next-step="2">Continue to Add-ons <i class="fa-solid fa-arrow-right ms-2"></i></button>
                        </div>
                    </div>
                </section>

                <!-- STEP 2 -->
                <section class="fh-step-panel" data-step-panel="2">
                    <h2>Enhance Your Trip</h2>
                    <p class="fh-kicker">Optional services to make your journey more comfortable and protected.</p>

                    <div class="card fh-card fh-addon-card mb-4">

                        @foreach($premium_addons as $addon)
                        @php
                            $addonId = 'addon_' . strtolower(preg_replace('/[^A-Za-z0-9]+/', '_', $addon->key));
                            $addonIcon = match($addon->key) {
                                'Travel_Care' => '<i class="fa-solid fa-shield-halved me-2 text-primary"></i>',
                                'Priority_Checkin' => '<i class="fa-regular fa-star me-2" style="color:#7c3aed"></i>',
                                'Airport_Lounge' => '<i class="fa-solid fa-mug-hot me-2 text-warning"></i>',
                                'Flexible_Ticket' => '<i class="fa-regular fa-calendar-check me-2 text-success"></i>',
                                'Extra_Baggage' => '<i class="fa-solid fa-suitcase-rolling me-2" style="color:#f97316"></i>',
                                default => '<i class="fa-solid fa-circle-plus me-2 text-primary"></i>',
                            };
                        @endphp
                        <div class="fh-addon-choice">
                            <div>
                                <div class="fh-addon-title">{!! $addonIcon !!}{{ $addon->name }}</div>
                                <div class="fh-helper">{{ $addon->description ?: 'Optional add-on for your booking.' }}</div>
                            </div>
                            <div class="text-end">
                                <div class="fh-addon-price">+${{ rtrim(rtrim(number_format((float)$addon->price, 2, '.', ''), '0'), '.') }}</div>
                                <div class="fh-choice-buttons justify-content-end mt-2">
                                    <input type="checkbox"
                                           class="btn-check fh-premium-addon"
                                           id="{{ $addonId }}"
                                           data-addon="{{ $addon->key }}"
                                           autocomplete="off">
                                    <label class="btn btn-sm btn-primary" for="{{ $addonId }}">Add to Booking</label>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        @if($setting['is_booking_web_checkin']=='ON')
                        <div class="fh-addon-choice">
                            <div>
                                <div class="fh-addon-title"><i class="fa-solid fa-plane-circle-check me-2 text-primary"></i>Web Check-In</div>
                                <div class="fh-helper">We can complete check-in for you and send the boarding pass by email.</div>
                                </div>
                            <div class="text-end">
                                <div class="fh-addon-price">${{number_format((float)$setting['booking_web_checkin'],2,'.','')}}<small>each way / passenger</small></div>
                                <div class="fh-choice-buttons">
                                    <input type="radio" class="btn-check" name="web_checkin" id="web_checkin_1" value="Yes" autocomplete="off">
                                    <label class="btn btn-sm btn-primary" for="web_checkin_1" id="div_web_checkin_1">Add to Booking</label>
                                    <input type="radio" class="btn-check" name="web_checkin" id="web_checkin_2" value="No" autocomplete="off" checked>
                                    <label class="btn btn-sm btn-outline-secondary fh-hidden-optout" for="web_checkin_2" id="div_web_checkin_2" aria-hidden="true">No thanks</label>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($setting['is_booking_cancellation_protection']=='ON')
                        <div class="fh-addon-choice">
                            <div>
                                <div class="fh-addon-title"><i class="fa-solid fa-umbrella me-2 text-success"></i>Cancellation Protection</div>
                                <div class="fh-helper">Optional cancellation protection available for this booking.</div>
                                </div>
                            <div class="text-end">
                                <div class="fh-addon-price">${{number_format((float)$setting['booking_cancellation_protection'],2,'.','')}}<small>per passenger</small></div>
                                <div class="fh-choice-buttons">
                                    <input type="radio" class="btn-check" name="cancellation" id="cancellation1" value="Yes">
                                    <label class="btn btn-sm btn-primary" for="cancellation1">Add to Booking</label>
                                    <input type="radio" class="btn-check" name="cancellation" id="cancellation2" value="No" checked>
                                    <label class="btn btn-sm btn-outline-secondary fh-hidden-optout" for="cancellation2" aria-hidden="true">No thanks</label>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="fh-step-actions">
                        <button type="button" class="fh-btn-secondary" data-prev-step="1"><i class="fa-solid fa-arrow-left me-2"></i>Back</button>
                        <button type="button" class="fh-btn-primary" data-next-step="3">Continue to Payment <i class="fa-solid fa-arrow-right ms-2"></i></button>
                    </div>
                </section>

                <!-- STEP 3 -->
                <section class="fh-step-panel" data-step-panel="3">
                    <h2>Payment</h2>
                    <div class="card fh-card fh-payment-card tnb-payment-card mb-4">
                        <div class="card-header">
                            <div class="fh-premium-head">
                                <div class="fh-premium-head-left">
                                    <span class="fh-premium-head-icon"><i class="fa-regular fa-credit-card"></i></span>
                                    <span>
                                        <strong>Secure Payment</strong>
                                        <small>Enter your card details to complete this booking</small>
                                    </span>
                                </div>
                                <span class="fh-secure-badge"><i class="fa-solid fa-lock"></i> SSL encrypted</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="fh-payment-layout">
                                <div class="fh-payment-form">

                            <div class="full-width d-flex flex-column mb-1 position-relative">
                                <div class="row align-items-stat g-1">
                                    <div class="fh-card-brand-panel">
                                        <span class="label">Accepted payment methods</span>
                                        <img src="{{ $setting['footer_payment_image'] }}" width="300" alt="Accepted payment cards">
                                    </div>
                                    <div class="form-group row align-items-center">
                                        <div class="col-5"><label class="form-label">Credit/Debit Card No</label></div>
                                        <div class="col-7">
                                            <input type="text" name="creditcard_no" id="creditcard_no" required class="form-control"
                                                   value="{{@$Booking_Form['creditcard_no']}}" placeholder="Credit/Debit Card No."
                                                   maxlength="23" inputmode="numeric" autocomplete="cc-number">
                                        </div>
                                    </div>
                                    <div class="form-group row align-items-center">
                                        <div class="col-5"><label class="form-label">Card Holder's Name</label></div>
                                        <div class="col-7">
                                            <input type="text" name="cardholder_name" id="cardholder_name" required class="form-control"
                                                   value="{{@$Booking_Form['cardholder_name']}}" placeholder="Card Holder Name">
                                        </div>
                                    </div>
                                    <div class="form-group row align-items-center">
                                        <div class="col-5"><label class="form-label">Expiration Date / CVV</label></div>
                                        <div class="col-7">
                                            <div class="row g-2">
                                                <div class="col-md-4">
                                                    <select class="form-control" name="expire_month" id="expire_month" required>
                                                        <option value="">Month</option>
                                                        @for($m=1;$m<=12;$m++)
                                                            @php $mm=str_pad($m,2,'0',STR_PAD_LEFT); @endphp
                                                            <option value="{{$mm}}" @selected(@$Booking_Form['expire_month']==$mm)>{{$mm}}</option>
                                                        @endfor
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <select class="form-control" name="expire_year" id="expire_year" required>
                                                        <option value="">Year</option>
                                                        @for($y=(int)date('Y');$y<=(int)date('Y')+15;$y++)
                                                            <option value="{{$y}}" @selected(@$Booking_Form['expire_year']==$y)>{{$y}}</option>
                                                        @endfor
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" name="cvv" id="cvv" value="{{@$Booking_Form['cvv']}}" class="form-control"
                                                           maxlength="4" placeholder="CVV" required inputmode="numeric" autocomplete="cc-csc">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="fh-payment-note">
                                <i class="fa-solid fa-shield-halved"></i>
                                <span>Card information is checked in the browser before submission. Please review the trip total before confirming your booking.</span>
                            </div>
                        
                                </div>

                                <aside class="fh-card-preview-wrap" aria-live="polite">
                                    <div class="fh-card-preview">
                                        <div class="fh-card-preview-top">
                                            <span class="fh-card-preview-brand" id="fhPreviewBrand">Card</span>
                                            <span class="fh-card-preview-shield"><i class="fa-solid fa-shield-halved"></i></span>
                                        </div>

                                        <div class="fh-card-preview-chiprow">
                                            <span class="fh-card-preview-chip"></span>
                                            <span class="fh-card-preview-contactless"><i class="fa-solid fa-wifi"></i></span>
                                        </div>

                                        <div class="fh-card-preview-number" id="fhPreviewNumber">•••• •••• •••• ••••</div>

                                        <div class="fh-card-preview-bottom">
                                            <div>
                                                <span class="fh-card-preview-label">Cardholder</span>
                                                <span class="fh-card-preview-value" id="fhPreviewName">Your Name</span>
                                            </div>
                                            <div>
                                                <span class="fh-card-preview-label">Expires</span>
                                                <span class="fh-card-preview-value" id="fhPreviewExpiry">MM/YY</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="fh-brand-pill" id="fhPreviewBrandPill">
                                        <i class="fa-regular fa-credit-card"></i>
                                        <span>Card brand will appear here</span>
                                    </div>

                                    <div class="fh-card-preview-note">
                                        <i class="fa-solid fa-lock"></i>
                                        <span>Preview is masked. Full card number and CVV are never shown here.</span>
                                    </div>
                                </aside>
                            </div>
</div>
                    </div>

                    <h2>Billing Information</h2>
                    <div class="card fh-card tnb-billing-card mb-4">
                        <div class="card-header">
                            <div class="fh-premium-head">
                                <div class="fh-premium-head-left">
                                    <span class="fh-premium-head-icon"><i class="fa-solid fa-location-dot"></i></span>
                                    <span>
                                        <strong>Billing Information</strong>
                                        <small>Use the billing details associated with your payment method</small>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="fh-billing-intro"><i class="fa-regular fa-circle-check"></i><span>Make sure the billing address matches the information on file with your card issuer.</span></div>
                            <div class="row g-3">
                                <div class="col-12"><div class="form-group"><label class="form-label">Street Address</label>
                                    <input required type="text" name="billing_address" id="billing_address" value="{{@$Booking_Form['billing_address']}}" class="form-control" placeholder="Address"></div></div>
                                <div class="col-md-6"><div class="form-group"><label class="form-label">Country</label>
                                    <select class="form-control" name="billing_country" id="billing_country" required onchange="checkCountry(this.value)">
                                        <option value="">Select Country</option>
                                        @if($country) @foreach($country as $countries)
                                            <option value="{{$countries->name}}" @selected(@$Booking_Form['billing_country']==$countries->name)>{{$countries->name}}</option>
                                        @endforeach @endif
                                    </select></div></div>
                                <div class="col-md-6"><div class="form-group"><label class="form-label">State</label>
                                    <div id="state_input"><input type="text" name="billing_state" id="billing_state" placeholder="State Name" class="form-control" value="{{@$Booking_Form['billing_state']}}"></div>
                                    <div id="state_select" style="display:none"><select class="form-control" name="billing_state_97" id="billing_state_97"><option value="">Select State</option>
                                        @if($state) @foreach($state as $states)<option value="{{$states->name}}">{{$states->name}}</option>@endforeach @endif
                                    </select></div></div></div>
                                <div class="col-md-6"><div class="form-group"><label class="form-label">City</label>
                                    <input type="text" name="billing_city" id="billing_city" value="{{@$Booking_Form['billing_city']}}" required class="form-control" placeholder="City / Town"></div></div>
                                <div class="col-md-6"><div class="form-group"><label class="form-label">Postal/Zip Code</label>
                                    <input type="text" name="billing_pincode" id="billing_pincode" value="{{@$Booking_Form['billing_pincode']}}" required class="form-control" maxlength="10" placeholder="Postal/Zip Code"></div></div>
                                <div class="col-12"><div class="form-group"><label class="form-label">Billing Mobile</label>
                                    <div class="d-flex gap-2">
                                        <select class="form-control" style="max-width:96px" name="billing_mobile_telcode" id="billing_mobile_telcode" required>
                                            @if($telcode) @foreach($telcode as $telcodes)
                                            <option value="{{$telcodes->code}}"
                                                @if(@$Booking_Form["billing_mobile_telcode"]==$telcodes->code) selected
                                                @else @if($telcodes->id=='2') selected @endif @endif
                                            >{{$telcodes->code}}</option>
                                            @endforeach @endif
                                        </select>
                                        <input type="text" name="billing_mobile" id="billing_mobile" value="{{@$Booking_Form['billing_mobile']}}" required maxlength="10" class="form-control" placeholder="Billing Mobile">
                                    </div></div></div>
                            </div>
                        </div>
                    </div>

                    <div class="fh-terms">
                        By clicking Confirm and Book, I agree that I have read and accepted
                        @php getContentLegalPaymentPage() @endphp
                        of this website.
                    </div>
                    <div class="fh-step-actions">
                        <button type="button" class="fh-btn-secondary" data-prev-step="2"><i class="fa-solid fa-arrow-left me-2"></i>Back</button>
                        <input type="button" id="submitBtn" disabled class="btn btn-primary" value="Confirm and Book">
                    </div>
                </section>
            </main>

            <!-- Sticky summary -->
            <aside class="fh-summary">
                <div class="fh-trip-card">
                    <div class="fh-trip-head">
                        <div class="fh-trip-eyebrow">Your Trip</div>
                        <div class="fh-trip-route">
                            <strong>{{@strtoupper(@$fields['IATAFrom'])}} → {{@strtoupper(@$fields['IATATo'])}}</strong>
                            <span>{{@$fields['FlightType']=='round_trip' ? 'Round Trip' : 'One Way'}} · {{$total_pass}} passenger{{ $total_pass==1 ? '' : 's' }}</span>
                        </div>
                    </div>

                    <div class="fh-fares">
                        <h6>Fare Breakdown</h6>
                        @if($fields['Adults'])
                        <div class="fh-fare-row"><span>Adult × {{$fields['Adults']}}</span>
                            @if($resultSelected['Is_Markup']=='No')
                                <strong>${{number_format((float)($resultSelected['AdultPrice']*$fields['Adults']),2,'.','')}}</strong>
                            @else
                                <strong>${{number_format((float)@$resultSelected['MinMarkup']['Sub_Adult'],2,'.','')}}</strong>
                            @endif
                        </div>
                        @endif
                        @if($fields['Childrens'])
                        <div class="fh-fare-row"><span>Child × {{$fields['Childrens']}}</span>
                            @if($resultSelected['Is_Markup']=='No')
                                <strong>${{number_format((float)($resultSelected['ChildPrice']*$fields['Childrens']),2,'.','')}}</strong>
                            @else
                                <strong>${{number_format((float)@$resultSelected['MinMarkup']['Sub_Child'],2,'.','')}}</strong>
                            @endif
                        </div>
                        @endif
                        @if($fields['Infants'])
                        <div class="fh-fare-row"><span>Infant × {{$fields['Infants']}}</span>
                            <strong>${{number_format((float)($resultSelected['InfantPrice']*$fields['Infants']),2,'.','')}}</strong>
                        </div>
                        @endif
                        <div id="addon_web" class="fh-fare-row"></div>
                        <div id="addon_can" class="fh-fare-row"></div>
                        <div id="addon_premium" class="fh-fare-row"></div>
                        <div class="fh-total"><span>Total</span><strong id="div_total_price">${{number_format((float)$total_price,2,'.','')}}</strong></div>
                        <div class="fh-total-note">Displayed taxes &amp; fees included · USD</div>
                    </div>
                </div>

                <div class="fh-help">
                    <div class="fh-help-inner">
                        <span class="fh-help-icon"><i class="fa-solid fa-headset"></i></span>
                        <span class="fh-help-copy">
                            <strong>Need help with this booking?</strong>
                            <p>Our travel support team can assist with fare and checkout questions before you confirm.</p>
                        </span>
                    </div>
                    @php
                        $fhHelpPhone = $setting['header_contact'] ?? '+1-888-717-0346';
                        $fhHelpPhoneDigits = preg_replace('/[^0-9+]/', '', (string)$fhHelpPhone);
                        $fhWhatsApp = $setting['content_whatsapp'] ?? $fhHelpPhone;
                        $fhWhatsAppDigits = preg_replace('/\D+/', '', (string)$fhWhatsApp);
                    @endphp
                    <div class="fh-help-actions">
                        <a class="fh-help-contact call" href="tel:{{ $fhHelpPhoneDigits }}"><i class="fa-solid fa-phone"></i><span><small>Call Toll-Free</small><b>{{ $fhHelpPhone }}</b></span></a>
                        @if($fhWhatsAppDigits)
                        <a class="fh-help-contact whatsapp" href="https://wa.me/{{ $fhWhatsAppDigits }}" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp"></i><span><small>WhatsApp Us</small><b>Chat on WhatsApp</b></span></a>
                        @endif
                    </div>
                    <div class="fh-help-meta">
                        <a class="fh-help-chip" href="tel:{{ $fhHelpPhoneDigits }}"><i class="fa-solid fa-plane"></i> Fare help</a>
                        <a class="fh-help-chip" href="tel:{{ $fhHelpPhoneDigits }}"><i class="fa-solid fa-credit-card"></i> Checkout help</a>
                    </div>
                </div>
            </aside>
        </div>
    </form>
</div>

</section>

@endsection


<script id="fh-card-brand-singleton-fix">
document.addEventListener('DOMContentLoaded', function(){
    const card = document.getElementById('creditcard_no');
    if(!card) return;

    function removeDuplicateBrandBadges(){
        const candidates = Array.from(document.querySelectorAll(
            '#cardBrandBadge, .card-brand-badge, .cardBrandBadge, .card-brand-floating, .card-brand-overlay'
        ));
        if(candidates.length <= 1) return;

        // Keep the first visible/primary badge only.
        let kept = false;
        candidates.forEach(function(el){
            if(!kept && el.offsetParent !== null){
                kept = true;
                return;
            }
            el.remove();
        });
    }

    removeDuplicateBrandBadges();

    const observer = new MutationObserver(removeDuplicateBrandBadges);
    observer.observe(card.parentElement || document.body, {childList:true, subtree:true});
});
</script>


<script id="fh-premium-card-preview-js">
document.addEventListener('DOMContentLoaded', function(){
    const number = document.getElementById('creditcard_no');
    const name = document.getElementById('cardholder_name');
    const month = document.getElementById('expire_month');
    const year = document.getElementById('expire_year');

    const pBrand = document.getElementById('fhPreviewBrand');
    const pNumber = document.getElementById('fhPreviewNumber');
    const pName = document.getElementById('fhPreviewName');
    const pExpiry = document.getElementById('fhPreviewExpiry');
    const pPill = document.getElementById('fhPreviewBrandPill');

    if(!number || !pNumber) return;

    function digits(v){
        return String(v || '').replace(/\D/g,'');
    }

    function detectBrand(raw){
        const d = digits(raw);
        if(/^4/.test(d)) return 'Visa';
        if(/^(5[1-5]|2(2[2-9]|[3-6]\d|7[01]|720))/.test(d)) return 'Mastercard';
        if(/^3[47]/.test(d)) return 'American Express';
        if(/^(6011|65|64[4-9])/.test(d)) return 'Discover';
        if(/^35/.test(d)) return 'JCB';
        if(/^62/.test(d)) return 'UnionPay';
        if(/^(30[0-5]|36|38|39)/.test(d)) return 'Diners Club';
        if(/^(50|5[6-9]|6)/.test(d)) return 'Maestro';
        return 'Card';
    }

    function masked(raw){
        const d = digits(raw);
        if(!d) return '•••• •••• •••• ••••';

        const visible = d.slice(-4);
        const total = Math.max(16, d.length);
        const maskedCount = Math.max(0, total - visible.length);
        const mask = '•'.repeat(maskedCount) + visible;

        const groups = [];
        for(let i=0;i<mask.length;i+=4){
            groups.push(mask.slice(i,i+4));
        }
        return groups.join(' ');
    }

    function expiry(){
        const m = month && month.value ? month.value : 'MM';
        let y = year && year.value ? String(year.value) : 'YY';
        if(y.length === 4) y = y.slice(-2);
        return m + '/' + y;
    }

    function refresh(){
        const brand = detectBrand(number.value);
        pBrand.textContent = brand;
        pNumber.textContent = masked(number.value);

        if(name && pName){
            const clean = name.value.trim();
            pName.textContent = clean ? clean : 'Your Name';
        }

        if(pExpiry){
            pExpiry.textContent = expiry();
        }

        if(pPill){
            const span = pPill.querySelector('span');
            if(brand !== 'Card'){
                pPill.classList.add('detected');
                if(span) span.textContent = brand + ' detected';
            }else{
                pPill.classList.remove('detected');
                if(span) span.textContent = 'Card brand will appear here';
            }
        }
    }

    number.addEventListener('input', refresh);
    if(name) name.addEventListener('input', refresh);
    if(month) month.addEventListener('change', refresh);
    if(year) year.addEventListener('change', refresh);

    refresh();
});
</script>

@section('footer')
<script>
document.addEventListener('DOMContentLoaded', function(){
    document.body.classList.add('tnb-checkout-active');

    const panels = [...document.querySelectorAll('[data-step-panel]')];
    const indicators = [...document.querySelectorAll('[data-step-indicator]')];

    function setStep(step){
        step = Number(step);
        panels.forEach(p => p.classList.toggle('active', Number(p.dataset.stepPanel) === step));
        indicators.forEach(i => {
            const n = Number(i.dataset.stepIndicator);
            i.classList.toggle('active', n === step);
            i.classList.toggle('done', n < step);
            const icon = i.querySelector('.dot i');
            if(icon){
                if(n < step){
                    icon.className = 'fa-solid fa-check';
                }else if(n === 1){
                    icon.className = 'fa-regular fa-user';
                }else if(n === 2){
                    icon.className = 'fa-solid fa-box-open';
                }else{
                    icon.className = 'fa-regular fa-credit-card';
                }
            }
        });
        window.scrollTo({top:0,behavior:'smooth'});
    }

    function validatePanel(step){
        const panel = document.querySelector('[data-step-panel="'+step+'"]');
        if(!panel) return true;
        const required = [...panel.querySelectorAll('[required]')].filter(el => !el.disabled);
        for(const el of required){
            el.classList.remove('fh-invalid');
            if(!el.checkValidity()){
                el.classList.add('fh-invalid');
                el.reportValidity();
                el.focus({preventScroll:false});
                return false;
            }
        }

        if(Number(step) === 1){
            const passengerNames = panel.querySelectorAll('input[name^="adult_fname_"], input[name^="adult_mname_"], input[name^="adult_lname_"]');
            const nameRegex = /^[\p{L}\p{M} .'’\-]+$/u;
            for(const nameField of passengerNames){
                const value = (nameField.value || '').trim();
                nameField.classList.remove('fh-invalid');
                if(value && !nameRegex.test(value)){
                    nameField.classList.add('fh-invalid');
                    alert('Passenger names can contain letters, spaces, hyphens and apostrophes only.');
                    nameField.focus();
                    return false;
                }
            }

            const phone = document.getElementById('flight_mobile');
            if(phone && !/^\d{10}$/.test(phone.value || '')){
                phone.classList.add('fh-invalid');
                alert('Please enter a valid 10-digit mobile number.');
                phone.focus();
                return false;
            }
        }
        return true;
    }

    document.querySelectorAll('[data-next-step]').forEach(btn=>{
        btn.addEventListener('click',function(){
            const current = Number(this.closest('[data-step-panel]').dataset.stepPanel);
            if(validatePanel(current)) setStep(this.dataset.nextStep);
        });
    });

    document.querySelectorAll('[data-prev-step]').forEach(btn=>{
        btn.addEventListener('click',function(){ setStep(this.dataset.prevStep); });
    });

    window.fhCheckoutSetStep = setStep;
});
</script>


<!-- Form Validations & Form Submission -->
<script>
    function checkCountry(countryname)
    {
        if(countryname=='United States')
        {
            jQuery('#state_input').hide();
            jQuery('#state_select').show();
            jQuery('#billing_state_97').prop('required', true);
            jQuery('#billing_state').prop('required', false);
        }
        else
        {
            jQuery('#state_select').hide();
            jQuery('#state_input').show();
            jQuery('#billing_state_97').prop('required', false);
            jQuery('#billing_state').prop('required', true);
        }
    }

    document.addEventListener('DOMContentLoaded', function ()
    {
        if (window.jQuery && jQuery.fn && jQuery.fn.select2) {
            const $country = jQuery('#billing_country');
            const $state = jQuery('#billing_state_97');

            if ($country.length && !$country.hasClass('select2-hidden-accessible')) {
                $country.select2({
                    width: '100%',
                    placeholder: 'Select Country'
                });
            }

            if ($state.length && !$state.hasClass('select2-hidden-accessible')) {
                $state.select2({
                    width: '100%',
                    placeholder: 'Select State'
                });
            }
        }

        const cardNumberInput = document.getElementById('creditcard_no');
        const cvvInput = document.getElementById('cvv');
        const monthInput = document.getElementById('expire_month');
        const yearInput = document.getElementById('expire_year');
        const form = document.getElementById('form4');

        if (!cardNumberInput || !cvvInput || !form) return;

        // Mark the existing payment/billing cards so the premium CSS can reshape them.
        document.querySelectorAll('#form4 .card').forEach(function(card){
            const heading = (card.querySelector('.card-header h6')?.textContent || '').trim().toLowerCase();
            if (heading.includes('payment and billing')) card.classList.add('tnb-payment-card');
            if (heading.includes('billing information')) card.classList.add('tnb-billing-card');
        });

        // Add a secure-payment message and a live card-brand badge without changing field names.
        const paymentCard = document.querySelector('.tnb-payment-card .card-body');
        if (paymentCard && !paymentCard.querySelector('.tnb-payment-security')) {
            const secure = document.createElement('div');
            secure.className = 'tnb-payment-security';
            secure.innerHTML = '<i class="fa-solid fa-lock"></i> Secure payment · card details are validated in your browser before submission';
            paymentCard.insertBefore(secure, paymentCard.firstChild);
        }

        const cardParent = cardNumberInput.parentElement;
        if (cardParent && !cardParent.classList.contains('tnb-card-field-wrap')) {
            cardParent.classList.add('tnb-card-field-wrap');

            const badge = document.createElement('span');
            badge.id = 'tnbCardBrand';
            badge.className = 'tnb-card-brand-badge';
            cardParent.appendChild(badge);

            const help = document.createElement('small');
            help.id = 'tnbCardHelp';
            help.className = 'tnb-card-help';
            help.textContent = 'Enter card number';
            cardParent.appendChild(help);
        }

        function digits(value) {
            return String(value || '').replace(/\D/g, '');
        }

        function detectCard(number) {
            const n = digits(number);

            // Order matters because some ranges overlap.
            if (/^3[47]/.test(n)) return {name:'AMEX', min:15, max:15, cvv:4, luhn:true};
            if (/^(30[0-5]|36|38|39)/.test(n)) return {name:'DINERS', min:14, max:14, cvv:3, luhn:true};
            if (/^(352[89]|35[3-8])/.test(n)) return {name:'JCB', min:16, max:19, cvv:3, luhn:true};
            if (/^(6011|65|64[4-9])/.test(n)) return {name:'DISCOVER', min:16, max:19, cvv:3, luhn:true};
            if (/^(5[1-5]|2(2[2-9]|[3-6]\d|7[01]|720))/.test(n)) return {name:'MASTERCARD', min:16, max:16, cvv:3, luhn:true};
            if (/^4/.test(n)) return {name:'VISA', min:13, max:19, cvv:3, luhn:true};
            if (/^62/.test(n)) return {name:'UNIONPAY', min:16, max:19, cvv:3, luhn:false};
            if (/^(50|5[6-9]|6[0-9])/.test(n)) return {name:'MAESTRO', min:12, max:19, cvv:3, luhn:true};
            return null;
        }

        function luhnCheck(number) {
            const n = digits(number);
            let sum = 0;
            let doubleDigit = false;

            for (let i = n.length - 1; i >= 0; i--) {
                let d = parseInt(n.charAt(i), 10);
                if (doubleDigit) {
                    d *= 2;
                    if (d > 9) d -= 9;
                }
                sum += d;
                doubleDigit = !doubleDigit;
            }
            return n.length > 0 && sum % 10 === 0;
        }

        function formatCard(number, brand) {
            const n = digits(number).slice(0, 19);

            if (brand && brand.name === 'AMEX') {
                return [n.slice(0,4), n.slice(4,10), n.slice(10,15)].filter(Boolean).join(' ');
            }
            if (brand && brand.name === 'DINERS') {
                return [n.slice(0,4), n.slice(4,10), n.slice(10,14)].filter(Boolean).join(' ');
            }

            return n.match(/.{1,4}/g)?.join(' ') || '';
        }

        function updateCardUI() {
            const n = digits(cardNumberInput.value);
            const brand = detectCard(n);
            const badge = document.getElementById('tnbCardBrand');
            const help = document.getElementById('tnbCardHelp');

            cardNumberInput.value = formatCard(n, brand);

            if (badge) {
                if (brand) {
                    badge.textContent = brand.name;
                    badge.classList.add('show');
                } else {
                    badge.textContent = '';
                    badge.classList.remove('show');
                }
            }

            if (help) {
                help.className = 'tnb-card-help';
                if (!n.length) {
                    help.textContent = 'Enter card number';
                } else if (!brand) {
                    help.textContent = 'Card type not recognised yet';
                } else if (n.length < brand.min) {
                    help.textContent = brand.name + ' · Enter complete card number';
                } else if (n.length > brand.max) {
                    help.textContent = 'Too many digits for ' + brand.name;
                    help.classList.add('invalid');
                } else if (brand.luhn && !luhnCheck(n)) {
                    help.textContent = brand.name + ' number is incomplete or invalid';
                    help.classList.add('invalid');
                } else {
                    help.textContent = '✓ Card number looks valid';
                    help.classList.add('valid');
                }
            }

            if (brand) {
                cvvInput.maxLength = brand.cvv;
                cvvInput.setAttribute('inputmode', 'numeric');
                cvvInput.setAttribute('autocomplete', 'cc-csc');
            }
        }

        function validateExpiry() {
            const month = parseInt(monthInput?.value || '0', 10);
            const year = parseInt(yearInput?.value || '0', 10);
            if (!month || !year) return false;

            const now = new Date();
            const currentMonth = now.getMonth() + 1;
            const currentYear = now.getFullYear();

            return year > currentYear || (year === currentYear && month >= currentMonth);
        }

        function validateCardDetails() {
            const n = digits(cardNumberInput.value);
            const brand = detectCard(n);
            const cvv = digits(cvvInput.value);

            if (!brand) return {ok:false, message:'Please enter a valid supported card number.'};
            if (n.length < brand.min || n.length > brand.max) {
                return {ok:false, message:'Invalid ' + brand.name + ' card number length.'};
            }
            if (brand.luhn && !luhnCheck(n)) {
                return {ok:false, message:'Please check the card number and try again.'};
            }
            if (cvv.length !== brand.cvv) {
                return {ok:false, message:brand.name + ' requires a ' + brand.cvv + '-digit security code.'};
            }
            if (!validateExpiry()) {
                return {ok:false, message:'Please select a valid, non-expired card expiry date.'};
            }

            return {ok:true, message:''};
        }

        cardNumberInput.addEventListener('input', updateCardUI);
        cardNumberInput.addEventListener('paste', function(){
            setTimeout(updateCardUI, 0);
        });

        cvvInput.addEventListener('input', function(e) {
            const brand = detectCard(cardNumberInput.value);
            const max = brand ? brand.cvv : 4;
            e.target.value = digits(e.target.value).slice(0, max);
        });

        updateCardUI();

        form.addEventListener('submit', function (e)
        {
            const mobile = document.getElementById("flight_mobile").value;
            const mobileRegex = /^\d{10}$/;

            if (!mobileRegex.test(mobile))
            {
                e.preventDefault();
                alert("Please enter a valid 10-digit mobile number.");
                return;
            }

            const result = validateCardDetails();

            if (!result.ok)
            {
                e.preventDefault();
                alert(result.message);
                cardNumberInput.focus();
                return;
            }

            // Keep backend compatibility: submit only digits, not visual spaces.
            cardNumberInput.value = digits(cardNumberInput.value);
            cvvInput.value = digits(cvvInput.value);

            document.getElementById("submitBtn").disabled = true;
            document.getElementById("screenBlocker").style.display = 'block';
            document.getElementById('loadingSubmit').style.display = 'block';
        });
    });
</script>

<script>
window.addEventListener("pageshow", function () {
    const submitBtn = document.getElementById("submitBtn");
    const screenBlocker = document.getElementById("screenBlocker");
    const loadingSubmit = document.getElementById("loadingSubmit");

    if (submitBtn) submitBtn.disabled = true;
    if (screenBlocker) screenBlocker.style.display = "none";
    if (loadingSubmit) loadingSubmit.style.display = "none";
});
</script>

<!-- Form Validations -->
<script>
    
  document.getElementById("flight_mobile").addEventListener("input", function (e) 
  {
    let cleaned = e.target.value.replace(/\D/g, '');

    if (cleaned.length > 10) 
    {
      cleaned = cleaned.slice(0, 10);
    }

    e.target.value = cleaned;
  });

  document.getElementById("billing_mobile").addEventListener("input", function (e) 
  {
    let cleaned = e.target.value.replace(/\D/g, '');
    
    if (cleaned.length > 10) 
    {
      cleaned = cleaned.slice(0, 10);
    }

    e.target.value = cleaned;
  });

  document.getElementById("billing_pincode").addEventListener("input", function (e) 
  {
    let cleaned = e.target.value.replace(/[^A-Za-z0-9 -]/g, '');
    
    if (cleaned.length > 10) 
    {
      cleaned = cleaned.slice(0, 10);
    }

    e.target.value = cleaned.toUpperCase();
  });
document.getElementById("cvv").addEventListener("input", function (e) 
  {
    let cleaned = e.target.value.replace(/\D/g, '');
    
    if (cleaned.length > 4) 
    {
      cleaned = cleaned.slice(0, 4);
    }

    e.target.value = cleaned;
  });

</script>

<!-- Temp Booking -->
<script>

    let tempLeadSent = false;

    function sendTempLead() 
    {
        const email             =   $('#flight_emailid').val();
        const phone             =   $('#flight_mobile').val();
        const i_field_encode    =   $('#i_field_encode').val();
        const total_price       =   $('#total_price').val();
        const resultSelected    =   $('#resultSelected').val();

        if (email && phone && !tempLeadSent) {
            // Lock immediately so email/phone blur cannot fire simultaneous duplicate requests.
            tempLeadSent = true;

            $.ajax({
                url: '/ajax/store-temp-booking',
                method: 'POST',
                data: {
                    email:          email,
                    phone:          phone,
                    i_field_encode: i_field_encode,
                    total_price:    total_price,
                    resultSelected: resultSelected,
                    _token: '{{ csrf_token() }}'
                },
                success: function () 
                {
                    // Lead stored successfully; keep the lock for this checkout page.
                },
                error: function ()
                {
                    // Allow a later retry only if the request itself failed.
                    tempLeadSent = false;
                }
            });
        }
    }

    $('#flight_emailid, #flight_mobile').on('blur', function () 
    {
        // Rich Premium display-only preview: no temporary lead submission.
    });

</script>

<!-- WebcheckIn & Cancel -->
<script>

    function CheckService()
    {
        const web_checkin = jQuery('input[name="web_checkin"]:checked').val() || 'No';
        const cancellation = jQuery('input[name="cancellation"]:checked').val() || 'No';
        const total_price = jQuery('#total_price').val();
        const service = jQuery('#service').val();

        const premium = {};
        document.querySelectorAll('.fh-premium-addon').forEach(function(el){
            premium[el.dataset.addon] = el.checked ? 'Yes' : 'No';
        });

        jQuery.ajax({
            url: '/ajax/get-service-info',
            method: 'POST',
            data: {
                service: service,
                web_checkin: web_checkin,
                cancellation: cancellation,
                premium_addons: premium,
                total_price: total_price,
                _token: '{{ csrf_token() }}'
            },
            success: function(response)
            {
                const parts = response.split('***');
                jQuery('#service').val(parts[0] || service);
                jQuery('#addon_web').html(parts[1] || '');
                jQuery('#addon_can').html(parts[2] || '');
                jQuery('#addon_premium').html(parts[3] || '');
                jQuery('#div_total_price').html(parts[4] || ('$'+total_price));
            }
        });
    }

    jQuery(document).on('change',
        '.fh-premium-addon, input[name="web_checkin"], input[name="cancellation"]',
        function(){
            if (this.classList && this.classList.contains('fh-premium-addon')) {
                const label = document.querySelector('label[for="' + this.id + '"]');
                if (label) {
                    label.textContent = this.checked ? 'Added ✓' : 'Add to Booking';
                    label.classList.toggle('btn-success', this.checked);
                    label.classList.toggle('btn-primary', !this.checked);
                }
            }

            if (this.name === 'web_checkin') {
                const yesLabel = document.querySelector('label[for="web_checkin_1"]');
                if (yesLabel) yesLabel.textContent = (this.value === 'Yes' && this.checked) ? 'Added ✓' : 'Add to Booking';
            }

            if (this.name === 'cancellation') {
                const yesLabel = document.querySelector('label[for="cancellation1"]');
                if (yesLabel) yesLabel.textContent = (this.value === 'Yes' && this.checked) ? 'Added ✓' : 'Add to Booking';
            }
            CheckService();
        }
    );
</script>




<script id="fareshunt-v10-checkout-final-js">
document.addEventListener('DOMContentLoaded', function(){
  /* Premium DOB picker, fast month/year navigation, preserves YYYY-MM-DD payload. */
  if(window.flatpickr){
    document.querySelectorAll('.fh-dob-picker').forEach(function(input){
      if(input._flatpickr) return;
      function placeDobCalendar(fp){
        if(!fp || !fp.calendarContainer || window.innerWidth<=760) return;
        var cal=fp.calendarContainer;
        var anchor=fp.altInput || fp.input;
        if(!anchor) return;

        cal.classList.add('fh-dob-calendar');
        cal.style.position='fixed';
        cal.style.right='auto';
        cal.style.bottom='auto';
        cal.style.transform='none';

        var pad=12;
        var ar=anchor.getBoundingClientRect();
        var width=Math.min(340,Math.max(280,window.innerWidth-(pad*2)));
        cal.style.width=width+'px';
        cal.style.minWidth=width+'px';
        cal.style.maxWidth=width+'px';

        /* Measure only after Flatpickr has opened the calendar. */
        var ch=cal.offsetHeight || 390;
        var left=ar.left + (ar.width/2) - (width/2);
        left=Math.max(pad,Math.min(left,window.innerWidth-width-pad));

        var top=ar.bottom+8;
        if(top+ch > window.innerHeight-pad){
          top=ar.top-ch-8;
        }
        top=Math.max(pad,Math.min(top,window.innerHeight-ch-pad));

        cal.style.left=Math.round(left)+'px';
        cal.style.top=Math.round(top)+'px';
      }

      var ageType=input.dataset.ageType || 'Adult';
      var today=new Date();
      var dobMin=null;
      var dobMax=new Date(today.getFullYear(),today.getMonth(),today.getDate());

      if(ageType==='Adult'){
        dobMax=new Date(today.getFullYear()-12,today.getMonth(),today.getDate());
      }else if(ageType==='Child'){
        dobMin=new Date(today.getFullYear()-12,today.getMonth(),today.getDate()+1);
        dobMax=new Date(today.getFullYear()-2,today.getMonth(),today.getDate());
      }else if(ageType==='Infant'){
        dobMin=new Date(today.getFullYear()-2,today.getMonth(),today.getDate()+1);
      }

      var fp=flatpickr(input,{
        dateFormat:'Y-m-d',
        altInput:true,
        altFormat:'m/d/Y',
        minDate:dobMin,
        maxDate:dobMax,
        disableMobile:true,
        monthSelectorType:'dropdown',
        allowInput:true,
        static:false,
        position:'auto',
        onReady:function(_,__,instance){
          instance.calendarContainer.classList.add('fh-dob-calendar');

          var currentMonth = instance.currentMonth;
          var currentYear = instance.currentYear;
          var minYear = 1900;
          var maxYear = new Date().getFullYear();

          var yearInput = instance.calendarContainer.querySelector('input.cur-year');
          if(yearInput && !instance.calendarContainer.querySelector('.fh-dob-year-select')){
            var yearSelect = document.createElement('select');
            yearSelect.className = 'fh-dob-year-select';
            yearSelect.setAttribute('aria-label','Select year');

            for(var y=maxYear; y>=minYear; y--){
              var opt = document.createElement('option');
              opt.value = y;
              opt.textContent = y;
              if(y === currentYear) opt.selected = true;
              yearSelect.appendChild(opt);
            }

            yearInput.style.display = 'none';
            yearInput.parentNode.insertBefore(yearSelect, yearInput);

            yearSelect.addEventListener('change', function(){
              instance.changeYear(parseInt(this.value,10));
              instance.jumpToDate(new Date(parseInt(this.value,10), currentMonth, 1), false);
            });

            instance.config.onYearChange.push(function(_,__,fp){
              yearSelect.value = String(fp.currentYear);
            });
          }
        },
        onOpen:function(_,__,instance){
          requestAnimationFrame(function(){placeDobCalendar(instance);});
          setTimeout(function(){placeDobCalendar(instance);},40);
        },
        onMonthChange:function(_,__,instance){requestAnimationFrame(function(){placeDobCalendar(instance);});},
        onYearChange:function(_,__,instance){requestAnimationFrame(function(){placeDobCalendar(instance);});}
      });

      if(fp){
        var reposition=function(){
          if(fp.isOpen) requestAnimationFrame(function(){placeDobCalendar(fp);});
        };
        window.addEventListener('resize',reposition,{passive:true});
        window.addEventListener('scroll',reposition,{passive:true,capture:true});
      }
    });
  }
  /* Radio add-ons behave like toggles without exposing a friction-heavy No thanks button. */
  [['web_checkin_1','web_checkin_2'],['cancellation1','cancellation2']].forEach(function(pair){
    var yes=document.getElementById(pair[0]), no=document.getElementById(pair[1]);
    var label=document.querySelector('label[for="'+pair[0]+'"]');
    if(!yes||!no||!label) return;
    label.addEventListener('click',function(e){
      if(yes.checked){e.preventDefault();no.checked=true;no.dispatchEvent(new Event('change',{bubbles:true}));label.textContent='Add to Booking';}
    });
  });
});
</script>

@endsection