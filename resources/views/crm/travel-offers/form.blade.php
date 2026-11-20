@extends('crm.layout')
@section('content')
@php
$isEdit = !empty($offer);
$val = function($key,$default='') use ($offer) {
    return old($key, $offer->$key ?? $default);
};
@endphp

<style>
.of-head{display:flex;align-items:end;justify-content:space-between;gap:16px;margin-bottom:18px}
.of-head h1{font-size:30px;margin:0;color:#0a2448;letter-spacing:-.035em}
.of-head p{margin:6px 0 0;color:#718199}
.of-grid{display:grid;grid-template-columns:minmax(0,1.45fr) minmax(310px,.55fr);gap:16px}
.of-card{border:1px solid #dfe7f0;border-radius:18px;background:#fff;padding:18px;box-shadow:0 8px 26px rgba(19,48,84,.045);margin-bottom:14px}
.of-card h3{font-size:14px;margin:0 0 14px;color:#153555}
.of-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:11px}
.of-field label{display:block;font-size:10px;color:#6f8199;font-weight:700;margin:0 0 6px 2px}
.of-field.full{grid-column:1/-1}
.of-summary{position:sticky;top:90px}
.of-price{font-size:31px;font-weight:900;color:#0a3d87;letter-spacing:-.04em;margin:4px 0 14px}
.of-line{display:flex;justify-content:space-between;gap:12px;padding:9px 0;border-bottom:1px solid #edf1f5;color:#61758e}
.of-note{padding:12px;border-radius:12px;background:#f1f7ff;color:#557393;font-size:11px;line-height:1.55;margin-top:14px}
.of-submit{width:100%;min-height:48px;margin-top:14px}
.of-preview{width:100%;margin-top:8px}
.of-errors{border:1px solid #f0b7c2;background:#fff4f6;color:#9f2941;border-radius:13px;padding:12px 15px;margin-bottom:15px}
@media(max-width:1100px){.of-grid{grid-template-columns:1fr}.of-summary{position:static}}
@media(max-width:760px){.of-head{align-items:flex-start;flex-direction:column}.of-fields{grid-template-columns:1fr}.of-field.full{grid-column:auto}}
</style>

<div class="of-head">
    <div>
        <span class="eyebrow">Travel Offers</span>
        <h1>{{ $isEdit ? 'Edit Offer' : 'Create Offer' }}</h1>
        <p>Build a reusable customer-ready travel promotion without changing the normal booking flow.</p>
    </div>
    <a class="btn btn-light" href="{{ route('crm.travel_offers') }}">
        <i class="fa-solid fa-arrow-left me-1"></i>Back
    </a>
</div>

@if($errors->any())
<div class="of-errors">
    <b>Please fix the following:</b>
    <ul class="mb-0 mt-2">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST"
      action="{{ $isEdit ? route('crm.travel_offers.update',$offer->id) : route('crm.travel_offers.store') }}"
      enctype="multipart/form-data">
@csrf
@if($isEdit) @method('PUT') @endif

<div class="of-grid">
<div>

<div class="of-card">
<h3>Offer Basics</h3>
<div class="of-fields">

<div class="of-field full">
<label>Offer Title</label>
<input class="form-control" required name="title"
       value="{{ $val('title') }}"
       placeholder="e.g. 3 Round Trips – One Special Price">
</div>

<div class="of-field">
<label>Offer Type</label>
<select class="form-select" name="offer_type">
@foreach($types as $key=>$label)
<option value="{{ $key }}" @selected($val('offer_type','economy')===$key)>{{ $label }}</option>
@endforeach
</select>
</div>

<div class="of-field">
<label>Status</label>
<select class="form-select" name="status">
@foreach($statuses as $status)
<option value="{{ $status }}" @selected($val('status','Draft')===$status)>{{ $status }}</option>
@endforeach
</select>
</div>

<div class="of-field">
<label>Price</label>
<input id="offerPrice" class="form-control" required type="number" min="0" step="0.01"
       name="price" value="{{ $val('price',0) }}">
</div>

<div class="of-field">
<label>Currency</label>
<select id="offerCurrency" class="form-select" name="currency">
@foreach(['USD','CAD','GBP','EUR','INR'] as $currency)
<option @selected($val('currency','USD')===$currency)>{{ $currency }}</option>
@endforeach
</select>
</div>

<div class="of-field">
<label>Cabin Class</label>
<input class="form-control" name="cabin_class"
       value="{{ $val('cabin_class','Economy') }}"
       placeholder="Economy / Business / First">
</div>

<div class="of-field">
<label>{{ $isEdit && !empty($offer->banner_image) ? 'Replace Banner Image' : 'Banner Image' }}</label>
@if($isEdit && !empty($offer->banner_image))
<div class="mb-2"><img src="{{ $offer->banner_image }}" alt="Current offer banner" style="max-width:320px;max-height:140px;object-fit:cover;border-radius:8px"></div>
@endif
<input class="form-control" type="file" name="banner_image_file" accept=".jpg,.jpeg,.png,.webp">
<small>JPG, JPEG, PNG or WEBP · Max 5 MB{{ $isEdit && !empty($offer->banner_image) ? ' · Leave empty to keep current banner' : '' }}</small>
</div>

<div class="of-field full">
<label>Short Description</label>
<textarea class="form-control" rows="3" name="short_description"
placeholder="Short customer-facing summary...">{{ $val('short_description') }}</textarea>
</div>

</div>
</div>

<div class="of-card">
<h3>Included Trips & Airline Rules</h3>
<div class="of-fields">

<div class="of-field">
<label>Domestic Round Trips</label>
<input id="domesticTrips" class="form-control" required type="number" min="0" max="99"
       name="domestic_trips" value="{{ $val('domestic_trips',0) }}">
</div>

<div class="of-field">
<label>International Round Trips</label>
<input id="internationalTrips" class="form-control" required type="number" min="0" max="99"
       name="international_trips" value="{{ $val('international_trips',0) }}">
</div>

<div class="of-field full">
<label>Eligible Domestic Airlines</label>
<input class="form-control" name="domestic_airlines"
       value="{{ old('domestic_airlines',$domesticAirlines) }}"
       placeholder="Frontier (F9), Southwest (WN), Alaska (AS), American Airlines (AA)">
</div>

<div class="of-field full">
<label>International Airline Rule</label>
<textarea class="form-control" rows="2" name="international_airline_rule"
placeholder="e.g. Any eligible available airline">{{ $val('international_airline_rule') }}</textarea>
</div>

<div class="of-field">
<label>Domestic Advance Booking (Months)</label>
<input class="form-control" type="number" min="0" max="60"
       name="domestic_advance_months" value="{{ $val('domestic_advance_months') }}">
</div>

<div class="of-field">
<label>International Advance Booking (Months)</label>
<input class="form-control" type="number" min="0" max="60"
       name="international_advance_months" value="{{ $val('international_advance_months') }}">
</div>

</div>
</div>

<div class="of-card">
<h3>Validity & Availability</h3>
<div class="of-fields">

<div class="of-field">
<label>Valid From</label>
<input class="form-control" type="date" name="valid_from" value="{{ $val('valid_from') }}">
</div>

<div class="of-field">
<label>Valid Until</label>
<input class="form-control" type="date" name="valid_until" value="{{ $val('valid_until') }}">
</div>

<div class="of-field">
<label>Slot Limit</label>
<input class="form-control" type="number" min="1" name="slot_limit"
       value="{{ $val('slot_limit') }}" placeholder="Leave blank for open">
</div>

<div class="of-field">
<label>Availability Message</label>
<input class="form-control" name="availability_message"
       value="{{ $val('availability_message') }}"
       placeholder="Limited slots available">
</div>

</div>
</div>

<div class="of-card">
<h3>Benefits & Terms</h3>
<div class="of-fields">

<div class="of-field full">
<label>Benefits / Inclusions</label>
<textarea class="form-control" rows="6" name="benefits"
placeholder="One benefit per line, or customer-facing offer details...">{{ $val('benefits') }}</textarea>
</div>

<div class="of-field full">
<label>Terms & Conditions</label>
<textarea class="form-control" rows="8" name="terms"
placeholder="Cancellation, changes, baggage, peak dates, eligibility, unused trips, expiry...">{{ $val('terms') }}</textarea>
</div>

</div>
</div>

</div>

<aside>
<div class="of-card of-summary">
<h3>Offer Summary</h3>

<div class="of-price" id="summaryPrice">USD 0.00</div>

<div class="of-line">
<span>Domestic trips</span>
<b id="summaryDomestic">0</b>
</div>

<div class="of-line">
<span>International trips</span>
<b id="summaryInternational">0</b>
</div>

<div class="of-line">
<span>Status</span>
<b>{{ $val('status','Draft') }}</b>
</div>

@if($isEdit)
<div class="of-line">
<span>Reference</span>
<b>{{ $offer->reference }}</b>
</div>
@endif

<div class="of-note">
<i class="fa-solid fa-circle-info me-1"></i>
This promotional layer is separate from existing flight APIs, fare rules, payment processing and normal Booking Management.
</div>

<button class="btn btn-danger of-submit">
<i class="fa-solid fa-floppy-disk me-1"></i>
{{ $isEdit ? 'Save Changes' : 'Create Offer' }}
</button>

@if($isEdit)
<a class="btn btn-light of-preview"
   href="{{ route('crm.travel_offers.preview',$offer->id) }}">
<i class="fa-solid fa-eye me-1"></i>Preview Customer Offer
</a>
@endif
</div>
</aside>

</div>
</form>

<script>
(function(){
const price=document.getElementById('offerPrice');
const currency=document.getElementById('offerCurrency');
const domestic=document.getElementById('domesticTrips');
const international=document.getElementById('internationalTrips');

function refresh(){
document.getElementById('summaryPrice').textContent =
    currency.value+' '+Number(price.value||0).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2});
document.getElementById('summaryDomestic').textContent=domestic.value||0;
document.getElementById('summaryInternational').textContent=international.value||0;
}

[price,currency,domestic,international].forEach(el=>el.addEventListener('input',refresh));
refresh();
})();
</script>
@endsection
