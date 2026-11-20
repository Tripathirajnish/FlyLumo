<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Review Travel Plan | FaresHunt</title>
<style>
*{box-sizing:border-box}body{margin:0;background:#f4f7fb;color:#102a43;font-family:Arial,sans-serif}
.wrap{max-width:900px;margin:35px auto;padding:0 18px}
.card{background:#fff;border-radius:18px;box-shadow:0 10px 35px rgba(15,42,67,.10);overflow:hidden}
.head{background:#062b4f;color:#fff;padding:28px 32px}
.logo{max-width:190px;max-height:64px;margin-bottom:18px}
.head h1{margin:0 0 7px;font-size:28px}.head p{margin:0;color:#d9e6f2}
.body{padding:30px 32px}
.offer{display:flex;justify-content:space-between;gap:20px;border:1px solid #dfe7ef;border-radius:14px;padding:20px}
.offer h2{margin:0 0 6px;font-size:20px}.price{font-size:28px;font-weight:800;color:#c51f32;white-space:nowrap}
.title{font-size:18px;font-weight:800;margin:27px 0 13px}
.info,.trip{border:1px solid #dfe7ef;border-radius:13px;padding:17px;margin-bottom:12px}
.info p,.trip p{margin:5px 0}.muted{color:#60758a}
.notice{background:#f6f8fb;border-radius:11px;padding:16px;margin-top:22px;font-size:14px;line-height:1.55}
.agree{display:flex;gap:10px;align-items:flex-start;margin:20px 0}
.agree input{margin-top:3px}.agree label{font-size:14px;line-height:1.45}
.actions{display:flex;justify-content:flex-end;margin-top:20px}
.btn{border:0;border-radius:9px;background:#c51f32;color:#fff;padding:14px 24px;font-weight:800;font-size:15px;cursor:pointer}
@media(max-width:650px){.wrap{margin:15px auto}.head,.body{padding:22px 18px}.offer{display:block}.price{margin-top:12px}}

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
<div class="wrap"><div class="card">

<div class="head">
<img class="logo" src="https://fareshunt.com/assets/img/fareshunt-logo-transparent.png" alt="FaresHunt">
<h1>Review Your Travel Plan</h1>
<p>Please confirm the details below before submitting your request.</p>
</div>

<div class="body">
<div class="offer">
<div>
<h2>{{ $offer->title }}</h2>
<div class="muted">Reference: {{ $offer->reference ?? '—' }} • {{ $offer->cabin_class ?? 'Economy' }}</div>
</div>
<div class="price">{{ ($offer->currency ?? 'USD') === 'USD' ? '$' : ($offer->currency.' ') }}{{ number_format((float)$offer->price,0) }}</div>
</div>

<div class="title">Customer Details</div>
<div class="info">
<p><b>{{ $data['customer_name'] }}</b></p>
<p>{{ $data['customer_email'] }}</p>
@if(!empty($data['customer_phone']))<p>{{ $data['customer_phone'] }}</p>@endif
</div>

<div class="title">Included Trips</div>

@for($i=1;$i<=(int)($offer->domestic_trips ?? 0);$i++)
@php($t=$data['trips']['domestic_'.$i] ?? [])
<div class="trip">
<p><b>Domestic Round Trip #{{ $i }}</b></p>
@if(!empty($t['origin']) || !empty($t['destination']) || !empty($t['departure_date']) || !empty($t['return_date']))
<p class="muted">{{ $t['origin'] ?? 'TBD' }} → {{ $t['destination'] ?? 'TBD' }}</p>
<p class="muted">{{ $t['departure_date'] ?? 'Date TBD' }} — {{ $t['return_date'] ?? 'Date TBD' }}</p>
@else
<p class="muted">Travel details will be provided later.</p>
@endif
</div>
@endfor

@for($i=1;$i<=(int)($offer->international_trips ?? 0);$i++)
@php($t=$data['trips']['international_'.$i] ?? [])
<div class="trip">
<p><b>International Round Trip #{{ $i }}</b></p>
@if(!empty($t['origin']) || !empty($t['destination']) || !empty($t['departure_date']) || !empty($t['return_date']))
<p class="muted">{{ $t['origin'] ?? 'TBD' }} → {{ $t['destination'] ?? 'TBD' }}</p>
<p class="muted">{{ $t['departure_date'] ?? 'Date TBD' }} — {{ $t['return_date'] ?? 'Date TBD' }}</p>
@else
<p class="muted">Travel details will be provided later.</p>
@endif
</div>
@endfor

<div class="notice">
<b>Payment is not collected on this page.</b><br>
After confirmation, your Travel Plan request will be created as <b>Pending Payment</b>. FaresHunt will send payment instructions separately.
</div>

<form method="POST" action="{{ URL::temporarySignedRoute('travel_offer.confirm', $version->expires_at ? \Carbon\Carbon::parse($version->expires_at) : now()->addDays(30), ['version'=>$version->id]) }}">
@csrf
<input type="hidden" name="customer_name" value="{{ $data['customer_name'] }}">
<input type="hidden" name="customer_email" value="{{ $data['customer_email'] }}">
<input type="hidden" name="customer_phone" value="{{ $data['customer_phone'] ?? '' }}">
<input type="hidden" name="details_later" value="{{ !empty($data['details_later']) ? '1' : '0' }}">

@foreach(($data['trips'] ?? []) as $key=>$trip)
@foreach(['origin','destination','departure_date','return_date'] as $field)
<input type="hidden" name="trips[{{ $key }}][{{ $field }}]" value="{{ $trip[$field] ?? '' }}">
@endforeach
@endforeach

<div class="agree">
<input type="checkbox" id="agreement" name="agreement" value="1" required>
<label for="agreement">I confirm that the information above is correct and I agree to submit this Travel Plan request under the terms of this offer.</label>
</div>

<div class="actions">
<button class="btn" type="submit">Confirm Travel Plan →</button>
</div>
</form>

</div>
</div></div>
</body>
</html>
