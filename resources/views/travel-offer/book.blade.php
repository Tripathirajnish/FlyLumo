<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Book Your Slot | FaresHunt</title>
<style>
*{box-sizing:border-box}body{margin:0;background:#f4f7fb;color:#102a43;font-family:Arial,sans-serif}
.wrap{max-width:920px;margin:35px auto;padding:0 18px}
.card{background:#fff;border-radius:18px;box-shadow:0 10px 35px rgba(15,42,67,.10);overflow:hidden}
.head{background:#062b4f;color:#fff;padding:28px 32px}
.logo{max-width:190px;max-height:64px;margin-bottom:20px}
.head h1{margin:0 0 7px;font-size:28px}.head p{margin:0;color:#d9e6f2}
.body{padding:30px 32px}.offer{display:flex;justify-content:space-between;gap:20px;padding:20px;border:1px solid #dfe7ef;border-radius:14px;margin-bottom:26px}
.offer h2{margin:0 0 6px;font-size:20px}.price{font-size:28px;font-weight:800;color:#c51f32;white-space:nowrap}
.section-title{font-size:18px;font-weight:800;margin:26px 0 14px}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:15px}
label{font-size:13px;font-weight:700;display:block;margin-bottom:6px}
input{width:100%;padding:12px;border:1px solid #ccd8e3;border-radius:9px;font-size:15px}
.trip{border:1px solid #dfe7ef;border-radius:14px;padding:18px;margin:14px 0}
.trip h3{margin:0 0 5px;font-size:16px}.trip small{color:#60758a}
.check{display:flex;gap:10px;align-items:flex-start;margin:20px 0;background:#f6f8fb;padding:15px;border-radius:10px}
.check input{width:auto;margin-top:3px}.check label{font-weight:500;margin:0}
.actions{display:flex;justify-content:flex-end;margin-top:24px}
.btn{border:0;border-radius:9px;background:#c51f32;color:#fff;padding:14px 24px;font-weight:800;font-size:15px;cursor:pointer}
.note{color:#60758a;font-size:13px;margin-top:12px}
.error{background:#fff1f2;color:#9f1239;padding:12px 15px;border-radius:9px;margin-bottom:18px}
@media(max-width:650px){.wrap{margin:15px auto}.body,.head{padding:22px 18px}.grid{grid-template-columns:1fr}.offer{display:block}.price{margin-top:12px}}

.logo{
    display:block;
    width:190px;
    max-width:100%;
    max-height:none;
    height:auto;
    object-fit:contain;
    background:#fff;
    padding:10px 14px;
    border-radius:10px;
    box-shadow:0 5px 18px rgba(0,0,0,.12);
}

</style>

<style id="fareshunt-white-logo-final">
.head .logo{
    display:block;
    width:190px;
    max-width:100%;
    height:auto;
    max-height:none;
    object-fit:contain;
    background:transparent !important;
    padding:0 !important;
    border-radius:0 !important;
    box-shadow:none !important;
    filter:brightness(0) invert(1) !important;
    opacity:1 !important;
}
</style>

</head>
<body>
<div class="wrap">
<div class="card">
<div class="head">
    <img class="logo" src="https://fareshunt.com/assets/img/fareshunt-logo-transparent.png" alt="FaresHunt">
    <h1>Book Your Slot</h1>
    <p>Review your offer and tell us your travel preferences.</p>
</div>

<div class="body">
@if($errors->any())
<div class="error">{{ $errors->first() }}</div>
@endif

<div class="offer">
    <div>
        <h2>{{ $offer->title }}</h2>
        <div>Reference: {{ $offer->reference ?? '—' }} &nbsp; • &nbsp; {{ $offer->cabin_class ?? 'Economy' }}</div>
    </div>
    <div class="price">
        {{ ($offer->currency ?? 'USD') === 'USD' ? '$' : ($offer->currency.' ') }}{{ number_format((float)$offer->price,0) }}
    </div>
</div>

<form method="POST" action="{{ URL::temporarySignedRoute('travel_offer.review', $version->expires_at ? \Carbon\Carbon::parse($version->expires_at) : now()->addDays(30), ['version'=>$version->id]) }}">
@csrf

<div class="section-title">Your Details</div>
<div class="grid">
    <div>
        <label>Full Name</label>
        <input name="customer_name" value="{{ old('customer_name',$version->customer_name) }}" required>
    </div>
    <div>
        <label>Email</label>
        <input type="email" name="customer_email" value="{{ old('customer_email',$version->customer_email) }}" required>
    </div>
    <div>
        <label>Phone</label>
        <input name="customer_phone" value="{{ old('customer_phone',$version->customer_phone) }}">
    </div>
</div>

<div class="section-title">Travel Preferences <span style="font-weight:400;color:#60758a">(Optional)</span></div>

@for($i=1;$i<=(int)($offer->domestic_trips ?? 0);$i++)
<div class="trip">
    <h3>Domestic Round Trip #{{ $i }}</h3>
    <small>Book at least {{ (int)($offer->domestic_advance_months ?? 0) }} months before travel.</small>
    <div class="grid" style="margin-top:13px">
        <div><label>Origin</label><input name="trips[domestic_{{ $i }}][origin]" maxlength="10" placeholder="e.g. JFK"></div>
        <div><label>Destination</label><input name="trips[domestic_{{ $i }}][destination]" maxlength="10" placeholder="e.g. MIA"></div>
        <div><label>Departure Date</label><input type="date" name="trips[domestic_{{ $i }}][departure_date]"></div>
        <div><label>Return Date</label><input type="date" name="trips[domestic_{{ $i }}][return_date]"></div>
    </div>
</div>
@endfor

@for($i=1;$i<=(int)($offer->international_trips ?? 0);$i++)
<div class="trip">
    <h3>International Round Trip #{{ $i }}</h3>
    <small>Book at least {{ (int)($offer->international_advance_months ?? 0) }} months before travel.</small>
    <div class="grid" style="margin-top:13px">
        <div><label>Origin</label><input name="trips[international_{{ $i }}][origin]" maxlength="10" placeholder="e.g. JFK"></div>
        <div><label>Destination</label><input name="trips[international_{{ $i }}][destination]" maxlength="10" placeholder="e.g. LHR"></div>
        <div><label>Departure Date</label><input type="date" name="trips[international_{{ $i }}][departure_date]"></div>
        <div><label>Return Date</label><input type="date" name="trips[international_{{ $i }}][return_date]"></div>
    </div>
</div>
@endfor

<div class="check">
    <input type="checkbox" id="later" name="details_later" value="1">
    <label for="later"><b>I'll provide my travel details later.</b><br>Travel dates and destinations are not required to reserve your Travel Plan request.</label>
</div>

<div class="actions">
    <button class="btn" type="submit">Review Travel Plan →</button>
</div>

<div class="note">No payment will be collected on this page. Payment instructions will be sent separately after your Travel Plan request is confirmed.</div>
</form>
</div>
</div>
</div>
</body>
</html>
