@extends('layout')

@section('canonical')
<link rel="canonical" href="{{ url('/contact-us') }}">
@endsection

@section('header-seo')
    <title>{{ $detail->seo_title ?? 'Contact Fares Hunt | Travel Support' }}</title>
    <meta name="description" content="{{ $detail->seo_description ?? 'Contact Fares Hunt for flight bookings, reservation support, billing questions, urgent travel assistance and general customer service.' }}">
@endsection

@section('mid-content')

@php
    $setting = getAllSetting();

    $supportPhone = '+1-888-717-0346';
    $supportPhoneHref = '+18887170346';

    $whatsappDisplay = '+1 (307) 263-8435';
    $whatsappDigits = '13072638435';

    $generalEmail = 'info@fareshunt.com';
    $bookingEmail = 'bookings@fareshunt.com';

    $businessAddressLine1 = '1603 Capitol Ave Suite 413G-2599';
    $businessAddressLine2 = 'Cheyenne, Wyoming 82001, United States';

    $mapQuery = urlencode($businessAddressLine1 . ', ' . $businessAddressLine2);
@endphp

<style id="fh-contact-premium-v1">
.fhc-page{
    --fhc-navy:#071a31;
    --fhc-navy2:#0d294a;
    --fhc-red:#d91f43;
    --fhc-gold:#d7a82d;
    --fhc-ink:#0a2340;
    --fhc-copy:#60758f;
    --fhc-soft:#f5f8fc;
    --fhc-line:#e1e7ef;
    background:#fff;
    color:var(--fhc-ink);
    font-family:inherit;
}
.fhc-page *{box-sizing:border-box}
.fhc-page .container{
    width:min(1500px,calc(100% - 48px));
    max-width:none;
    margin:0 auto;
}

/* HERO */
.fhc-hero{
    position:relative;
    overflow:hidden;
    padding:66px 20px 70px;
    background:
        radial-gradient(circle at 82% 14%,rgba(217,31,67,.11),transparent 24%),
        radial-gradient(circle at 14% 10%,rgba(75,132,194,.12),transparent 26%),
        linear-gradient(120deg,#07182d 0%,#0b213d 58%,#103255 100%);
    color:#fff;
    text-align:center;
}
.fhc-hero::before{
    content:"";
    position:absolute;
    inset:0;
    background:
        linear-gradient(rgba(255,255,255,.02) 1px,transparent 1px),
        linear-gradient(90deg,rgba(255,255,255,.02) 1px,transparent 1px);
    background-size:46px 46px;
}
.fhc-hero-inner{
    position:relative;
    z-index:1;
    max-width:900px;
    margin:auto;
}
.fhc-kicker{
    display:inline-flex;
    align-items:center;
    gap:10px;
    margin-bottom:14px;
    color:#f1bd3d;
    font-size:10px;
    font-weight:700;
    letter-spacing:.20em;
    text-transform:uppercase;
}
.fhc-kicker::before,.fhc-kicker::after{
    content:"";
    width:30px;
    height:1px;
    background:currentColor;
}
.fhc-hero h1{
    margin:0 0 14px!important;
    color:#fff!important;
    font-family:inherit!important;
    font-size:clamp(42px,4.3vw,62px)!important;
    line-height:1.04!important;
    font-weight:600!important;
    letter-spacing:-.045em!important;
}
.fhc-hero p{
    max-width:760px;
    margin:0 auto;
    color:#c9d4e2;
    font-size:14px;
    line-height:1.7;
}
.fhc-hero-trust{
    display:flex;
    flex-wrap:wrap;
    justify-content:center;
    gap:9px;
    margin-top:22px;
}
.fhc-hero-trust span{
    display:inline-flex;
    align-items:center;
    gap:7px;
    min-height:34px;
    padding:0 12px;
    border:1px solid rgba(255,255,255,.13);
    border-radius:999px;
    background:rgba(255,255,255,.05);
    color:#e6edf5;
    font-size:10.5px;
}

/* QUICK CONTACT */
.fhc-section{padding:56px 0}
.fhc-section.soft{background:var(--fhc-soft)}
.fhc-eyebrow{
    display:block;
    margin-bottom:8px;
    color:var(--fhc-gold);
    font-size:9px;
    font-weight:700;
    letter-spacing:.18em;
    text-transform:uppercase;
}
.fhc-title{
    margin:0 0 12px;
    color:var(--fhc-ink);
    font-size:clamp(30px,2.5vw,40px);
    line-height:1.08;
    font-weight:650;
    letter-spacing:-.035em;
}
.fhc-copy{
    color:var(--fhc-copy);
    font-size:14px;
    line-height:1.75;
}
.fhc-contact-grid{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:14px;
    margin-top:26px;
}
.fhc-contact-card{
    min-height:165px;
    padding:23px;
    border:1px solid var(--fhc-line);
    border-radius:19px;
    background:#fff;
    text-align:center;
    box-shadow:0 9px 24px rgba(20,44,77,.04);
}
.fhc-contact-card i{
    display:grid;
    place-items:center;
    width:44px;
    height:44px;
    margin:0 auto 14px;
    border-radius:14px;
    background:#edf3f8;
    color:#315a86;
    font-size:17px;
}
.fhc-contact-card b{
    display:block;
    margin-bottom:6px;
    color:var(--fhc-ink);
    font-size:13px;
}
.fhc-contact-card a,
.fhc-contact-card span{
    display:block;
    color:#617792!important;
    font-size:11.5px;
    line-height:1.55;
    text-decoration:none!important;
}

/* SUPPORT OPTIONS */
.fhc-support-grid{
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:14px;
    margin-top:26px;
}
.fhc-support-card{
    padding:22px;
    border:1px solid var(--fhc-line);
    border-radius:18px;
    background:#fff;
}
.fhc-support-card i{
    color:var(--fhc-red);
    font-size:18px;
}
.fhc-support-card h3{
    margin:12px 0 7px;
    color:var(--fhc-ink);
    font-size:14px;
    font-weight:650;
}
.fhc-support-card p{
    margin:0;
    color:#667b95;
    font-size:11.5px;
    line-height:1.58;
}

/* FORM + MAP */
.fhc-form-map{
    display:grid;
    grid-template-columns:minmax(0,1.05fr) minmax(380px,.95fr);
    gap:22px;
    align-items:stretch;
}
.fhc-form-card,
.fhc-map-card{
    overflow:hidden;
    border:1px solid var(--fhc-line);
    border-radius:22px;
    background:#fff;
    box-shadow:0 12px 30px rgba(20,44,77,.045);
}
.fhc-form-card{padding:28px}
.fhc-form-card h2{
    margin:0 0 7px;
    color:var(--fhc-ink);
    font-size:25px;
    font-weight:650;
    letter-spacing:-.025em;
}
.fhc-form-card>p{
    margin:0 0 22px;
    color:#687d96;
    font-size:12.5px;
}
.fhc-form-card .form-label{
    margin-bottom:6px;
    color:#263e5b;
    font-size:11.5px;
    font-weight:600;
}
.fhc-form-card .form-control{
    min-height:48px;
    border:1px solid #dce4ee;
    border-radius:12px;
    background:#fbfcfe;
    color:#0a2340;
    font-family:inherit;
    font-size:13px;
    box-shadow:none!important;
}
.fhc-form-card textarea.form-control{
    min-height:130px;
    resize:vertical;
}
.fhc-form-card .form-control:focus{
    border-color:#9db5cf;
    background:#fff;
}
.fhc-submit{
    min-height:48px;
    padding:0 22px;
    border:0;
    border-radius:12px;
    background:var(--fhc-red);
    color:#fff;
    font-size:12.5px;
    font-weight:700;
}
.fhc-map-card{
    min-height:100%;
    display:flex;
    flex-direction:column;
}
.fhc-map-card iframe{
    display:block;
    width:100%;
    flex:1;
    min-height:470px;
    border:0;
}
.fhc-map-meta{
    padding:17px 20px;
    border-top:1px solid var(--fhc-line);
}
.fhc-map-meta b{
    display:block;
    margin-bottom:4px;
    color:var(--fhc-ink);
    font-size:12.5px;
}
.fhc-map-meta span{
    color:#687d96;
    font-size:11.5px;
    line-height:1.5;
}

/* URGENT / BILLING */
.fhc-info-grid{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:18px;
}
.fhc-info-card{
    padding:26px;
    border:1px solid var(--fhc-line);
    border-radius:20px;
    background:#fff;
}
.fhc-info-card.warning{
    border-color:#eadfc2;
    background:#fffaf0;
}
.fhc-info-card h3{
    margin:0 0 9px;
    color:var(--fhc-ink);
    font-size:18px;
    font-weight:650;
}
.fhc-info-card p{
    margin:0;
    color:#60758e;
    font-size:12px;
    line-height:1.65;
}
.fhc-info-card ul{
    display:grid;
    gap:7px;
    margin:13px 0 0;
    padding:0;
    list-style:none;
}
.fhc-info-card li{
    position:relative;
    padding-left:18px;
    color:#60758e;
    font-size:11.5px;
}
.fhc-info-card li::before{
    content:"";
    position:absolute;
    left:0;
    top:7px;
    width:6px;
    height:6px;
    border-radius:50%;
    background:var(--fhc-red);
}

/* BUSINESS + FEEDBACK */
.fhc-lower-grid{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:18px;
}
.fhc-lower-card{
    padding:26px;
    border:1px solid var(--fhc-line);
    border-radius:20px;
    background:#fff;
}
.fhc-lower-card h3{
    margin:0 0 9px;
    color:var(--fhc-ink);
    font-size:18px;
    font-weight:650;
}
.fhc-lower-card p{
    margin:0 0 12px;
    color:#60758e;
    font-size:12px;
    line-height:1.65;
}
.fhc-lower-card a{
    color:var(--fhc-red)!important;
    font-size:12px;
    font-weight:700;
    text-decoration:none!important;
}

/* CLOSING */
.fhc-closing{
    padding:50px 20px 54px;
    background:linear-gradient(120deg,#071a31,#103255);
    color:#fff;
    text-align:center;
}
.fhc-closing h2{
    margin:0 0 10px;
    color:#fff!important;
    font-size:29px;
    font-weight:650;
}
.fhc-closing p{
    max-width:760px;
    margin:0 auto 11px;
    color:#d3dde9;
    font-size:13px;
    line-height:1.65;
}
.fhc-closing strong{
    color:#f1bd3d;
    font-size:12px;
}

@media(max-width:1050px){
    .fhc-contact-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
    .fhc-support-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
    .fhc-form-map{grid-template-columns:1fr}
}
@media(max-width:700px){
    .fhc-page .container{width:calc(100% - 24px)}
    .fhc-hero{padding:52px 14px 56px}
    .fhc-section{padding:42px 0}
    .fhc-contact-grid,
    .fhc-support-grid,
    .fhc-info-grid,
    .fhc-lower-grid{grid-template-columns:1fr}
    .fhc-form-card{padding:22px}
    .fhc-map-card iframe{min-height:330px}
}
</style>

<div class="fhc-page">

    <section class="fhc-hero">
        <div class="fhc-hero-inner">
            <span class="fhc-kicker">CONTACT FARES HUNT</span>
            <h1>We're Here to Help</h1>
            <p>
                Whether you need help with a new booking, an existing reservation, itinerary changes,
                refund inquiries, payment verification or general travel questions, our team is here to assist.
            </p>

            <div class="fhc-hero-trust">
                <span><i class="fa-solid fa-headset"></i> 24/7 customer support</span>
                <span><i class="fa-solid fa-shield-halved"></i> Secure assistance</span>
                <span><i class="fa-solid fa-plane"></i> Travel support worldwide</span>
            </div>
        </div>
    </section>


    <section class="fhc-section">
        <div class="container">
            <span class="fhc-eyebrow">GET IN TOUCH</span>
            <h2 class="fhc-title">Choose the support channel that works best for you</h2>
            <p class="fhc-copy">
                Our team assists with reservations, ticketing questions, billing concerns, urgent travel matters and general customer service.
            </p>

            <div class="fhc-contact-grid">
                <div class="fhc-contact-card">
                    <i class="fa-solid fa-phone"></i>
                    <b>Customer Support</b>
                    <a href="tel:{{ $supportPhoneHref }}">{{ $supportPhone }}</a>
                    <span>Available 24 hours a day, 7 days a week</span>
                </div>

                <div class="fhc-contact-card">
                    <i class="fa-brands fa-whatsapp"></i>
                    <b>WhatsApp</b>
                    <a href="https://wa.me/{{ $whatsappDigits }}" target="_blank" rel="noopener">{{ $whatsappDisplay }}</a>
                    <span>Fast assistance for travel-related questions</span>
                </div>

                <div class="fhc-contact-card">
                    <i class="fa-regular fa-envelope"></i>
                    <b>Booking Assistance</b>
                    <a href="mailto:{{ $bookingEmail }}">{{ $bookingEmail }}</a>
                    <span>Reservations, ticketing and itinerary support</span>
                </div>

                <div class="fhc-contact-card">
                    <i class="fa-solid fa-envelope-open-text"></i>
                    <b>General Support</b>
                    <a href="mailto:{{ $generalEmail }}">{{ $generalEmail }}</a>
                    <span>General service, business and partnership inquiries</span>
                </div>
            </div>
        </div>
    </section>


    <section class="fhc-section soft">
        <div class="container">
            <span class="fhc-eyebrow">CUSTOMER SUPPORT</span>
            <h2 class="fhc-title">How we can assist</h2>

            <div class="fhc-support-grid">
                @foreach([
                    ['fa-plane','Flight Reservations','New bookings, ticketing questions and flight reservation assistance.'],
                    ['fa-pen-to-square','Booking Modifications','Help with itinerary changes and reservation updates when permitted.'],
                    ['fa-ban','Cancellations','Cancellation requests and travel-plan changes.'],
                    ['fa-rotate-left','Refund Inquiries','Support with refund questions and transaction concerns.'],
                    ['fa-credit-card','Payment Verification','Assistance with payment verification and booking security checks.'],
                    ['fa-circle-question','General Support','Travel documentation questions and general customer service.'],
                ] as $item)
                <div class="fhc-support-card">
                    <i class="fa-solid {{ $item[0] }}"></i>
                    <h3>{{ $item[1] }}</h3>
                    <p>{{ $item[2] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>


    <section class="fhc-section">
        <div class="container">
            <div class="fhc-form-map">

                <div class="fhc-form-card">
                    <span class="fhc-eyebrow">SEND A MESSAGE</span>
                    <h2>Drop Us a Line</h2>
                    <p>Send us your question and our team will respond as promptly as possible.</p>

                    <form name="form2"
                          id="form2"
                          action="{{ url('submit-contact') }}"
                          method="post">
                        @csrf

                        <div style="position:absolute;left:-9999px;top:-9999px;" aria-hidden="true">
                            <label for="website">Website</label>
                            <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Your Name *</label>
                                <input type="text" class="form-control" name="name" id="name" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email Address *</label>
                                <input type="email" class="form-control" name="emailid" id="emailid" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Phone Number *</label>
                                <input type="text" class="form-control" name="phoneno" id="phoneno" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Subject *</label>
                                <input type="text" class="form-control" name="subject" id="subject" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Your Query *</label>
                                <textarea class="form-control" name="message" id="message" required></textarea>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="fhc-submit">Send Message</button>
                            </div>
                        </div>
                    </form>
                </div>


                <div class="fhc-map-card">
                    <iframe
                        src="https://www.google.com/maps?q={{ $mapQuery }}&output=embed"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        title="Fares Hunt business address map">
                    </iframe>

                    <div class="fhc-map-meta">
                        <b>Aeronza Travel Group LLC · Fares Hunt</b>
                        <span>{{ $businessAddressLine1 }}<br>{{ $businessAddressLine2 }}</span>
                    </div>
                </div>

            </div>
        </div>
    </section>


    <section class="fhc-section soft">
        <div class="container">
            <div class="fhc-info-grid">

                <div class="fhc-info-card warning">
                    <span class="fhc-eyebrow">URGENT TRAVEL ASSISTANCE</span>
                    <h3>Travel within the next 72 hours?</h3>
                    <p>
                        For time-sensitive travel matters, calling customer support is generally the fastest option.
                    </p>
                    <ul>
                        <li>Same-day travel issues</li>
                        <li>Flight disruptions</li>
                        <li>Schedule changes</li>
                        <li>Urgent booking assistance</li>
                    </ul>
                </div>

                <div class="fhc-info-card">
                    <span class="fhc-eyebrow">PAYMENT VERIFICATION &amp; SECURITY</span>
                    <h3>Helping protect your booking</h3>
                    <p>
                        For certain bookings, Fares Hunt may request additional verification information to help prevent
                        unauthorized transactions, fraud and identity theft. Verification may include identification,
                        billing-address confirmation, payment authorization or cardholder verification.
                    </p>
                </div>

                <div class="fhc-info-card">
                    <span class="fhc-eyebrow">REFUNDS &amp; BILLING</span>
                    <h3>Questions about a payment or refund?</h3>
                    <p>
                        Contact our support team for refund requests, billing concerns, payment disputes or
                        transaction-related questions so the matter can be reviewed and assistance can be provided.
                    </p>
                </div>

                <div class="fhc-info-card">
                    <span class="fhc-eyebrow">BUSINESS HOURS</span>
                    <h3>Customer support is available 24/7</h3>
                    <p>
                        Certain administrative, accounting, verification and supplier-relations functions may operate
                        during standard business hours or depend on supplier response times.
                    </p>
                </div>

            </div>
        </div>
    </section>


    <section class="fhc-section">
        <div class="container">
            <div class="fhc-lower-grid">

                <div class="fhc-lower-card">
                    <span class="fhc-eyebrow">BUSINESS &amp; PARTNERSHIPS</span>
                    <h3>Work with Fares Hunt</h3>
                    <p>
                        We welcome inquiries regarding corporate travel services, supplier relationships,
                        affiliate opportunities, marketing collaborations and media inquiries.
                    </p>
                    <a href="mailto:{{ $generalEmail }}">{{ $generalEmail }} →</a>
                </div>

                <div class="fhc-lower-card">
                    <span class="fhc-eyebrow">CUSTOMER FEEDBACK</span>
                    <h3>Your experience matters</h3>
                    <p>
                        Suggestions, compliments, concerns and feedback help us improve our services and better serve travelers worldwide.
                    </p>
                    <a href="mailto:{{ $generalEmail }}">Share your feedback →</a>
                </div>

            </div>
        </div>
    </section>


    <section class="fhc-closing">
        <div class="container">
            <h2>Thank You for Contacting Fares Hunt</h2>
            <p>
                We appreciate the opportunity to assist with your travel needs and look forward to helping you plan your next journey.
            </p>
            <strong>Making Travel Simpler, Smarter, and More Affordable.</strong>
        </div>
    </section>

</div>

@endsection
