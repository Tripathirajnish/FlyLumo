@extends('crm.layout')
@section('content')
<style>
.mb-page{font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Arial,sans-serif;color:#173555}.mb-head{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:16px}.mb-head h1{margin:0;color:#071f4f;font-size:30px;font-weight:850}.mb-head p{margin:5px 0 0;color:#74849a;font-size:12px}.mb-card{background:#fff;border:1px solid #dbe6f2;border-radius:18px;padding:18px;margin-bottom:14px;box-shadow:0 10px 25px rgba(12,44,88,.045)}.mb-card h3{margin:0 0 14px;color:#0b315f;font-size:15px}.mb-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:11px}.mb-field label{display:block;margin:0 0 5px 2px;color:#6f8199;font-size:9px;font-weight:800;text-transform:uppercase}.mb-field input,.mb-field select,.mb-field textarea{width:100%;min-height:46px;border:1px solid #d6e2ef;border-radius:11px;background:#fbfdff;padding:9px 11px;color:#173555;font-size:12px}.mb-field textarea{min-height:88px}.mb-repeat{border:1px solid #e3ebf4;border-radius:14px;padding:12px;margin-bottom:9px;background:#fbfdff}.mb-repeat-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:9px}.mb-repeat-head strong{font-size:11px;color:#244766}.mb-remove{border:0;background:#fff0f3;color:#d7193f;border-radius:8px;width:32px;height:32px}.mb-add{border:1px dashed #93b1d4;background:#f5f9ff;color:#1d5bd2;border-radius:10px;min-height:42px;padding:0 13px;font-size:11px;font-weight:800}.mb-total{display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-radius:13px;background:#071f4f;color:#fff}.mb-total small{font-size:9px;color:#bfcce0}.mb-total strong{font-size:26px}.mb-actions{display:flex;justify-content:flex-end;gap:9px;margin-top:16px}.mb-actions .btn{min-height:46px;border-radius:11px}.mb-help{padding:10px 12px;background:#eef7ff;border:1px solid #d7eafa;border-radius:11px;color:#52708f;font-size:10px;line-height:1.45;margin-bottom:14px}@media(max-width:1000px){.mb-grid{grid-template-columns:repeat(2,1fr)}}@media(max-width:620px){.mb-head{align-items:flex-start;flex-direction:column}.mb-grid{grid-template-columns:1fr}.mb-actions{display:grid;grid-template-columns:1fr 1fr}.mb-actions .btn{width:100%}}
</style>

<div class="mb-page">
    <div class="mb-head">
        <div>
            <a href="{{ route('crm.bookings') }}" class="crm-back"><i class="fa-solid fa-arrow-left me-1"></i>Back to bookings</a>
            <h1>New Manual Booking</h1>
            <p>Create phone, email or offline bookings inside the same FaresHunt CRM.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <b>Please fix the highlighted information.</b>
            <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('crm.manual_booking_store') }}" id="manualBookingForm">
        @csrf

        <section class="mb-card">
            <h3>Booking & customer</h3>
            <div class="mb-grid">
                <div class="mb-field"><label>Customer name</label><input name="customer_name" value="{{ old('customer_name') }}" required></div>
                <div class="mb-field"><label>Email</label><input type="email" name="customer_email" value="{{ old('customer_email') }}"></div>
                <div class="mb-field"><label>Phone</label><input name="customer_phone" value="{{ old('customer_phone') }}"></div>
                <div class="mb-field"><label>Trip type</label><select name="trip_type" id="tripType"><option value="one_way">One Way</option><option value="round_trip">Round Trip</option><option value="multi_city">Multi-City</option></select></div>
                <div class="mb-field"><label>Booking channel</label><select name="channel"><option>Phone</option><option>Email</option><option selected>Offline</option><option>Walk-in</option><option>Other</option></select></div>
                <div class="mb-field"><label>Booking status</label><select name="booking_status"><option>Fresh</option><option>Pending</option><option>Confirmed</option><option>Ticketed</option></select></div>
                <div class="mb-field"><label>PNR</label><input name="pnr" value="{{ old('pnr') }}"></div>
                <div class="mb-field"><label>Ticket number</label><input name="ticket_number" value="{{ old('ticket_number') }}"></div>
                @if($canAssignOthers)
                <div class="mb-field"><label>Assign to</label><select name="assigned_to"><option value="">Current / unassigned</option>@foreach($admins as $a)<option value="{{ $a->id }}">{{ $a->name ?: $a->username }}</option>@endforeach</select></div>
                @endif
            </div>
        </section>

        <section class="mb-card">
            <h3>Passengers</h3>
            <div id="passengerRows"></div>
            <button type="button" class="mb-add" id="addPassenger"><i class="fa-solid fa-plus me-1"></i>Add passenger</button>
        </section>

        <section class="mb-card">
            <h3>Flight itinerary</h3>
            <div class="mb-help">Use the same leg number for connecting segments in one journey. Round Trip normally uses leg 1 for outbound and leg 2 for return. Multi-City can use leg 1, 2, 3, etc.</div>
            <div id="segmentRows"></div>
            <button type="button" class="mb-add" id="addSegment"><i class="fa-solid fa-plus me-1"></i>Add flight segment</button>
        </section>

        <section class="mb-card">
            <h3>Fare & payment</h3>
            <div class="mb-grid">
                <div class="mb-field"><label>Base fare</label><input type="number" step="0.01" min="0" name="base_fare" class="money" value="{{ old('base_fare','0.00') }}"></div>
                <div class="mb-field"><label>Taxes</label><input type="number" step="0.01" min="0" name="taxes" class="money" value="{{ old('taxes','0.00') }}"></div>
                <div class="mb-field"><label>Service fee / markup</label><input type="number" step="0.01" min="0" name="service_fee" class="money" value="{{ old('service_fee','0.00') }}"></div>
                <div class="mb-field"><label>Add-ons</label><input type="number" step="0.01" min="0" name="addons" class="money" value="{{ old('addons','0.00') }}"></div>
                <div class="mb-field"><label>Discount</label><input type="number" step="0.01" min="0" name="discount" class="money" value="{{ old('discount','0.00') }}"></div>
                <div class="mb-field"><label>Paid amount</label><input type="number" step="0.01" min="0" name="paid_amount" value="{{ old('paid_amount','0.00') }}"></div>
                <div class="mb-field"><label>Payment status</label><select name="payment_status"><option>Untracked</option><option>Pending</option><option>Partially Paid</option><option>Paid</option></select></div>
                <div class="mb-field"><label>Payment method</label><input name="payment_method" placeholder="Card, bank transfer, cash, etc."></div>
            </div>
            <div class="mb-total mt-3"><div><small>Calculated selling total</small><div>Base + taxes + fee + add-ons − discount</div></div><strong id="calcTotal">$0.00</strong></div>
        </section>

        <section class="mb-card">
            <h3>Internal finance & notes</h3>
            <div class="mb-grid">
                <div class="mb-field"><label>Supplier cost</label><input type="number" step="0.01" min="0" name="supplier_cost" value="0.00"></div>
                <div class="mb-field"><label>Processing cost</label><input type="number" step="0.01" min="0" name="processing_cost" value="0.00"></div>
                <div class="mb-field"><label>Other cost</label><input type="number" step="0.01" min="0" name="other_cost" value="0.00"></div>
            </div>
            <div class="mb-field mt-3"><label>Internal notes</label><textarea name="notes">{{ old('notes') }}</textarea></div>
        </section>

        <div class="mb-actions">
            <a href="{{ route('crm.bookings') }}" class="btn btn-light">Cancel</a>
            <button class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Create Manual Booking</button>
        </div>
    </form>
</div>

<template id="passengerTemplate">
<div class="mb-repeat passenger-row">
    <div class="mb-repeat-head"><strong>Passenger <span class="row-num"></span></strong><button type="button" class="mb-remove remove-row"><i class="fa-solid fa-xmark"></i></button></div>
    <div class="mb-grid">
        <div class="mb-field"><label>Type</label><select data-name="type"><option>Adult</option><option>Child</option><option>Infant</option></select></div>
        <div class="mb-field"><label>Title</label><select data-name="title"><option value="">—</option><option>Mr</option><option>Ms</option><option>Mrs</option><option>Miss</option></select></div>
        <div class="mb-field"><label>First name</label><input data-name="first_name" required></div>
        <div class="mb-field"><label>Middle name</label><input data-name="middle_name"></div>
        <div class="mb-field"><label>Last name</label><input data-name="last_name" required></div>
        <div class="mb-field"><label>Date of birth</label><input type="date" data-name="dob"></div>
        <div class="mb-field"><label>Nationality</label><input data-name="nationality"></div>
    </div>
</div>
</template>

<template id="segmentTemplate">
<div class="mb-repeat segment-row">
    <div class="mb-repeat-head"><strong>Flight segment <span class="row-num"></span></strong><button type="button" class="mb-remove remove-row"><i class="fa-solid fa-xmark"></i></button></div>
    <div class="mb-grid">
        <div class="mb-field"><label>Leg #</label><input type="number" min="1" max="20" value="1" data-name="leg_no" required></div>
        <div class="mb-field"><label>From</label><input maxlength="10" data-name="from" placeholder="DEN" required></div>
        <div class="mb-field"><label>To</label><input maxlength="10" data-name="to" placeholder="LAX" required></div>
        <div class="mb-field"><label>Cabin</label><select data-name="cabin"><option>Economy</option><option>Premium Economy</option><option>Business</option><option>First</option></select></div>
        <div class="mb-field"><label>Departure</label><input type="datetime-local" data-name="departure_at" required></div>
        <div class="mb-field"><label>Arrival</label><input type="datetime-local" data-name="arrival_at" required></div>
        <div class="mb-field"><label>Airline</label><input data-name="airline_name" placeholder="United Airlines" required></div>
        <div class="mb-field"><label>Airline code</label><input maxlength="10" data-name="airline_code" placeholder="UA"></div>
        <div class="mb-field"><label>Flight number</label><input data-name="flight_number" placeholder="2316"></div>
    </div>
</div>
</template>

<script>
(function(){
    const paxBox=document.getElementById('passengerRows');
    const segBox=document.getElementById('segmentRows');
    const paxTpl=document.getElementById('passengerTemplate');
    const segTpl=document.getElementById('segmentTemplate');

    function renumber(box,type){
        [...box.children].forEach((row,i)=>{
            row.querySelector('.row-num').textContent=i+1;
            row.querySelectorAll('[data-name]').forEach(el=>{
                el.name=`${type}[${i}][${el.dataset.name}]`;
            });
            row.querySelector('.remove-row').style.visibility=box.children.length===1?'hidden':'visible';
        });
    }

    function add(box,tpl,type){
        box.appendChild(tpl.content.cloneNode(true));
        const row=box.lastElementChild;
        row.querySelector('.remove-row').addEventListener('click',()=>{
            row.remove();
            renumber(box,type);
        });
        renumber(box,type);
    }

    document.getElementById('addPassenger').addEventListener('click',()=>add(paxBox,paxTpl,'passengers'));
    document.getElementById('addSegment').addEventListener('click',()=>add(segBox,segTpl,'segments'));

    add(paxBox,paxTpl,'passengers');
    add(segBox,segTpl,'segments');

    function total(){
        const v=n=>parseFloat(document.querySelector(`[name="${n}"]`)?.value||0)||0;
        const t=Math.max(0,v('base_fare')+v('taxes')+v('service_fee')+v('addons')-v('discount'));
        document.getElementById('calcTotal').textContent='$'+t.toFixed(2);
    }

    document.querySelectorAll('.money').forEach(el=>el.addEventListener('input',total));
    total();

    document.getElementById('tripType').addEventListener('change',function(){
        if(this.value==='round_trip' && segBox.children.length<2){
            add(segBox,segTpl,'segments');
            segBox.lastElementChild.querySelector('[data-name="leg_no"]').value=2;
        }
    });
})();
</script>
@endsection
