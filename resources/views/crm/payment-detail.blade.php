@extends('crm.layout')
@section('content')

<style id="fareshunt-payment-detail-polish-v1">
:root{
    --pd-navy:#0c3762;
    --pd-blue:#2866dc;
    --pd-line:#dbe6f2;
    --pd-muted:#74859a;
}

/* Typography consistency */
.pd-page,
.pd-page *{
    font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Arial,sans-serif;
}

/* Hero */
.pd-hero{
    display:flex!important;
    align-items:center!important;
    justify-content:space-between!important;
    gap:18px!important;
    padding:22px 26px!important;
    margin-bottom:14px!important;
}
.pd-title-row{
    display:flex!important;
    align-items:center!important;
    gap:12px!important;
    flex-wrap:wrap!important;
}
.pd-title-row h1{
    margin:6px 0 0!important;
    font-size:36px!important;
    line-height:1.05!important;
    font-weight:700!important;
    letter-spacing:-.03em!important;
}
.pd-hero p{
    margin:7px 0 0!important;
    font-size:14px!important;
}
.pd-hero-actions{
    display:flex!important;
    align-items:center!important;
    gap:10px!important;
    flex-wrap:wrap!important;
}
.pd-hero-actions .btn{
    min-height:46px!important;
    height:46px!important;
    padding:0 16px!important;
    display:inline-flex!important;
    align-items:center!important;
    justify-content:center!important;
    border-radius:12px!important;
    white-space:nowrap!important;
}
.pd-hero-actions form{
    margin:0!important;
}

/* Action center */
.pd-action-center{
    padding:14px 18px!important;
    min-height:76px!important;
    margin-bottom:14px!important;
}
.pd-action-center > div{
    gap:12px!important;
}
.pd-action-center nav{
    display:flex!important;
    align-items:center!important;
    gap:8px!important;
    flex-wrap:wrap!important;
    justify-content:flex-end!important;
}
.pd-action-center nav a{
    min-height:38px!important;
    padding:0 12px!important;
    display:inline-flex!important;
    align-items:center!important;
    gap:6px!important;
    border-radius:10px!important;
    font-size:12px!important;
    white-space:nowrap!important;
}

/* Summary cards */
.pd-summary{
    gap:10px!important;
    margin-bottom:14px!important;
}
.pd-summary>div{
    min-height:108px!important;
    padding:14px!important;
}
.pd-summary small{
    font-size:9px!important;
}
.pd-summary strong{
    font-size:18px!important;
}

/* Main layout */
@media(min-width:1051px){
    .pd-layout{
        grid-template-columns:minmax(0,1.62fr) 390px!important;
        gap:16px!important;
        align-items:start!important;
    }
}
.pd-card{
    margin-bottom:14px!important;
}
.pd-card>header{
    min-height:62px!important;
}
.pd-card>header h3{
    font-size:18px!important;
}
.pd-card>header span{
    font-size:10px!important;
}

/* Overview tiles */
.pd-info-grid{
    gap:10px!important;
}
.pd-info-grid>div{
    min-height:102px!important;
    padding:14px!important;
}
.pd-info-grid small{
    font-size:9px!important;
}
.pd-info-grid strong{
    font-size:13px!important;
}

/* Card metadata */
.pd-card-layout{
    grid-template-columns:minmax(300px,.9fr) minmax(0,1.1fr)!important;
    gap:14px!important;
}
.pd-payment-card{
    min-height:190px!important;
    padding:22px!important;
}
.pd-security-notice{
    min-height:190px!important;
}

/* Right workspace: compact sticky card without internal scroll */
.pd-layout aside{
    align-self:start!important;
}
.pd-workspace{
    position:sticky!important;
    top:92px!important;
}
.pd-workspace .pd-body{
    padding:14px!important;
}
.pd-work-status{
    gap:9px!important;
    margin-bottom:12px!important;
}
.pd-work-status>div{
    min-height:78px!important;
    padding:12px!important;
}
.pd-work-status small{
    font-size:9px!important;
}
.pd-work-status strong{
    font-size:12px!important;
}
.pd-ops-form{
    display:grid!important;
    gap:10px!important;
}
.pd-ops-form label{
    margin:0!important;
}
.pd-ops-form label>span{
    display:block!important;
    margin:0 0 5px 2px!important;
    font-size:9px!important;
    font-weight:800!important;
    letter-spacing:.03em!important;
    text-transform:uppercase!important;
}
.pd-ops-form .form-control,
.pd-ops-form .form-select{
    min-height:44px!important;
    height:44px!important;
    border-radius:11px!important;
}
.pd-ops-form textarea.form-control{
    height:auto!important;
    min-height:86px!important;
}
.pd-two{
    gap:10px!important;
}
.pd-live-math{
    gap:10px!important;
}
.pd-live-math>div{
    padding:10px 12px!important;
    min-height:64px!important;
}
.pd-form-note{
    padding:10px 12px!important;
    font-size:10px!important;
}
.pd-ops-form>.btn{
    min-height:46px!important;
    border-radius:11px!important;
}

/* Keep Copy/Clear buttons, just make them neater */
.pd-page .crm-ua-form-tools{
    margin-top:6px!important;
    display:flex!important;
    justify-content:flex-end!important;
    gap:6px!important;
}
.pd-page .crm-ua-form-tools button,
.pd-page .crm-ua-form-tools .btn{
    min-height:30px!important;
    padding:0 9px!important;
    border-radius:8px!important;
    font-size:11px!important;
}

/* Activity history */
.pd-activity-form{
    grid-template-columns:180px minmax(0,1fr)!important;
    gap:10px!important;
    align-items:start!important;
}
.pd-activity-form input[name="details"]{
    grid-column:1/2!important;
}
.pd-activity-form .btn{
    grid-column:2/3!important;
    min-height:46px!important;
    border-radius:11px!important;
}
.pd-timeline{
    margin-top:16px!important;
}
.pd-timeline>div{
    padding:8px 0!important;
}
.pd-timeline p b{
    font-size:12px!important;
}
.pd-timeline p em,
.pd-timeline p small{
    font-size:9px!important;
}

/* Bottom whitespace reduction */
.pd-page{
    padding-bottom:22px!important;
}
.pd-layout main .pd-card:last-child{
    margin-bottom:0!important;
}

@media(max-width:1050px){
    .pd-layout{
        grid-template-columns:1fr!important;
    }
    .pd-workspace{
        position:static!important;
    }
}
@media(max-width:760px){
    .pd-hero{
        flex-direction:column!important;
        align-items:flex-start!important;
    }
    .pd-hero-actions{
        width:100%!important;
    }
    .pd-hero-actions .btn,
    .pd-hero-actions form{
        flex:1 1 100%!important;
        width:100%!important;
    }
    .pd-action-center{
        align-items:flex-start!important;
    }
    .pd-action-center nav{
        justify-content:flex-start!important;
    }
    .pd-summary{
        grid-template-columns:1fr 1fr!important;
    }
    .pd-card-layout,
    .pd-activity-form{
        grid-template-columns:1fr!important;
    }
    .pd-activity-form input[name="details"],
    .pd-activity-form .btn{
        grid-column:auto!important;
    }
}
</style>

@php
$status=$paymentMeta->payment_status ?: 'Untracked';
$tone=match($status){
    'Paid'=>'green','Pending','Partially Paid'=>'amber','Declined','Failed','Chargeback'=>'red',
    'Refund Pending','Partially Refunded','Refunded'=>'purple',default=>'slate'
};
$amount=max(0,(float)preg_replace('/[^0-9.\-]/','',(string)$booking->o_FullPrice));
$paid=$paymentMeta->paid_amount !== null ? max(0,(float)$paymentMeta->paid_amount) : 0.0;
$refund=$paymentMeta->refund_amount !== null ? max(0,(float)$paymentMeta->refund_amount) : 0.0;
$balance=max(0,$amount-$paid);
$netCollected=max(0,$paid-$refund);
$currentProcessor=$paymentMeta->processor ?: '';
@endphp

<div class="pd-page">
@if($errors->any())
    <div class="pd-validation-alert" role="alert">
        <div class="pd-validation-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div><strong>Please check the payment details.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    </div>
@endif
<section class="pd-hero">
    <div>
        <a href="{{ route('crm.payments') }}" class="crm-back"><i class="fa-solid fa-arrow-left me-1"></i>Back to payments</a>
        <div class="pd-title-row"><h1>Payment · #FH{{ $booking->id }}</h1><span class="pm-badge {{ $tone }}">{{ $status }}</span></div>
        <p>{{ $booking->o_Name ?: 'Guest' }} · {{ $route }} · Created {{ optional($booking->created_at)->format('d M Y, h:i A') }}</p>
    </div>
    <div class="pd-hero-actions">
        <a class="btn btn-light" href="{{ route('crm.booking_detail',$booking->id) }}"><i class="fa-solid fa-plane me-1"></i>Open Booking</a>
        @if($booking->o_Email)<a class="btn btn-light" href="mailto:{{ $booking->o_Email }}"><i class="fa-regular fa-envelope me-1"></i>Email</a>@endif
        <a class="btn btn-primary" href="#payment-operations"><i class="fa-regular fa-pen-to-square me-1"></i>Update Payment</a>
        <form method="POST" action="{{ route('crm.universal_archive') }}" onsubmit="return confirm('Move this payment record to CRM Trash?')">@csrf
            <input type="hidden" name="module" value="payment"><input type="hidden" name="record_key" value="{{ $booking->id }}"><input type="hidden" name="label" value="Payment · #FH{{ $booking->id }}">
            <button class="btn btn-light crm-ua-archive"><i class="fa-regular fa-trash-can me-1"></i>Delete / Archive</button>
        </form>
    </div>
</section>

<section class="pd-action-center">
    <div><span><i class="fa-solid fa-bolt"></i></span><div><small>Payment Action Center</small><strong>Review, follow up and resolve</strong></div></div>
    <nav>
        <a href="{{ route('crm.booking_detail',$booking->id) }}"><i class="fa-solid fa-plane"></i>Booking</a>
        <a href="#activity" data-payment-action="note"><i class="fa-regular fa-note-sticky"></i>Add Note</a>
        <a href="#payment-operations" data-payment-action="followup"><i class="fa-regular fa-bell"></i>Follow-up</a>
        <a href="#payment-operations" data-payment-action="paid"><i class="fa-solid fa-circle-check"></i>Paid Status</a>
        <a href="#payment-operations" data-payment-action="refund"><i class="fa-solid fa-arrow-rotate-left"></i>Refund</a>
        @if($booking->o_Phone)<a href="tel:{{ $booking->o_Phone }}"><i class="fa-solid fa-phone"></i>Call</a>@endif
    </nav>
</section>

<section class="pd-summary">
    <div class="blue"><span><i class="fa-solid fa-dollar-sign"></i></span><small>Booking Amount</small><strong>${{ number_format($amount,2) }}</strong></div>
    <div class="green"><span><i class="fa-solid fa-circle-check"></i></span><small>Paid Amount</small><strong>${{ number_format($paid,2) }}</strong></div>
    <div class="amber"><span><i class="fa-solid fa-scale-balanced"></i></span><small>Outstanding Balance</small><strong>${{ number_format($balance,2) }}</strong></div>
    <div class="purple"><span><i class="fa-solid fa-arrow-rotate-left"></i></span><small>Refunded</small><strong>${{ number_format($refund,2) }}</strong></div>
    <div class="cyan"><span><i class="fa-regular fa-credit-card"></i></span><small>Net Collected</small><strong>${{ number_format($netCollected,2) }}</strong></div>
    <div class="slate"><span><i class="fa-solid fa-user-tie"></i></span><small>Assigned Agent</small><strong>{{ $paymentMeta->agent_name ?: 'Unassigned' }}</strong></div>
</section>

<div class="pd-layout">
<main>
<section class="pd-card">
    <header><div><span>Payment overview</span><h3>Customer & transaction summary</h3></div></header>
    <div class="pd-body"><div class="pd-info-grid">
        <div><small>Booking Ref</small><strong>#FH{{ $booking->id }}</strong></div>
        <div data-crm-ua data-ua-module="booking" data-ua-key="{{ $booking->id }}" data-ua-field="o_Name" data-ua-label="Customer Name" data-ua-value="{{ $booking->o_Name }}"><small>Customer</small><strong>{{ $booking->o_Name ?: 'Guest' }}</strong></div>
        <div data-crm-ua data-ua-module="booking" data-ua-key="{{ $booking->id }}" data-ua-field="o_Email" data-ua-label="Customer Email" data-ua-value="{{ $booking->o_Email }}"><small>Email</small><strong>{{ $booking->o_Email ?: '—' }}</strong></div>
        <div data-crm-ua data-ua-module="booking" data-ua-key="{{ $booking->id }}" data-ua-field="o_Phone" data-ua-label="Customer Phone" data-ua-value="{{ $booking->o_Phone }}"><small>Phone</small><strong>{{ $booking->o_Phone ?: '—' }}</strong></div>
        <div><small>Trip</small><strong>{{ $route }}</strong></div>
        <div><small>Total Fare</small><strong>${{ number_format($amount,2) }}</strong></div>
        <div data-crm-ua data-ua-module="payment" data-ua-key="{{ $booking->id }}" data-ua-field="payment_status" data-ua-label="Payment Status" data-ua-value="{{ $status }}"><small>Payment Status</small><strong>{{ $status }}</strong></div>
        <div data-crm-ua data-ua-module="payment" data-ua-key="{{ $booking->id }}" data-ua-field="transaction_reference" data-ua-label="Transaction Reference" data-ua-value="{{ $paymentMeta->transaction_reference }}"><small>Transaction Ref</small><strong>{{ $paymentMeta->transaction_reference ?: 'Not recorded' }}</strong></div>
        <div data-crm-ua data-ua-module="payment" data-ua-key="{{ $booking->id }}" data-ua-field="processor" data-ua-label="Processor" data-ua-value="{{ $paymentMeta->processor }}"><small>Processor</small><strong>{{ $paymentMeta->processor ?: 'Not recorded' }}</strong></div>
    </div></div>
</section>

<section class="pd-card">
    <header><div><span>Safe card metadata</span><h3>Payment method</h3></div><span class="soft green">Masked only</span></header>
    <div class="pd-body pd-card-layout">
        <div class="pd-payment-card">
            <div class="pd-card-top"><strong>{{ $payment['brand'] }}</strong><i class="fa-solid fa-shield-halved"></i></div>
            <div class="pd-card-number">**** **** **** {{ $payment['last4'] ?: '****' }}</div>
            <div class="pd-card-meta"><span>{{ $payment['holder'] ?: 'Card holder not stored' }}</span><span>Masked metadata</span></div>
            <small>Sensitive card credentials are never displayed in this CRM view.</small>
        </div>
        <div class="pd-security-notice"><span><i class="fa-solid fa-lock"></i></span><div><strong>Masked payment metadata only</strong><p>Use transaction references and processor records for payment operations. Never enter full card numbers or CVV/CVC in notes, exports or activity history.</p></div></div>
    </div>
</section>

<section class="pd-card" id="activity">
    <header><div><span>Payment activity</span><h3>Payment, refund & follow-up history</h3></div></header>
    <div class="pd-body">
        <form method="POST" action="{{ route('crm.payment_activity',$booking->id) }}" class="pd-activity-form">@csrf
            <select class="form-select" name="activity_type" id="payment-activity-type">
                <option value="note">Note</option><option value="call">Call</option><option value="follow_up">Follow-up</option>
                <option value="payment">Payment Update</option><option value="refund">Refund</option><option value="email">Email</option>
                <option value="customer_contact">Customer Contact</option><option value="task">Task</option><option value="other">Other</option>
            </select>
            <input class="form-control" id="payment-activity-title" name="title" required placeholder="What happened?">
            <input class="form-control" name="details" placeholder="Optional details">
            <button class="btn btn-primary">Add activity</button>
        </form>

        <div class="pd-timeline">
            @foreach($activity as $a)
                @php
                    $activityIcon=match($a->activity_type){
                        'refund'=>'fa-arrow-rotate-left','chargeback'=>'fa-triangle-exclamation','payment'=>'fa-credit-card',
                        'call'=>'fa-phone','email'=>'fa-envelope','follow_up'=>'fa-bell','assignment'=>'fa-user-tie',
                        'status_change'=>'fa-arrows-rotate',default=>'fa-note-sticky'
                    };
                @endphp
                <div class="{{ $a->activity_type }}"><span><i class="fa-solid {{ $activityIcon }}"></i></span><p><b>{{ $a->title }}</b>@if($a->details)<em>{{ $a->details }}</em>@endif<small>{{ \Carbon\Carbon::parse($a->created_at)->format('d M Y, h:i A') }} · {{ $a->admin_name ?: 'CRM' }}</small></p></div>
            @endforeach
            <div class="created"><span><i class="fa-solid fa-plus"></i></span><p><b>Booking/payment record created</b><small>{{ optional($booking->created_at)->format('d M Y, h:i A') }}</small></p></div>
        </div>
    </div>
</section>
</main>

<aside>
<section class="pd-card pd-workspace" id="payment-operations">
    <header><div><span>Payment workspace</span><h3>Operations & reconciliation</h3></div></header>
    <div class="pd-body">
        <div class="pd-work-status">
            <div><small>Status</small><strong>{{ $status }}</strong></div><div><small>Booking</small><strong>#FH{{ $booking->id }}</strong></div>
            <div><small>Amount</small><strong>${{ number_format($amount,2) }}</strong></div><div><small>Method</small><strong>{{ $payment['brand'] }}</strong></div>
        </div>

        <form method="POST" action="{{ route('crm.payment_operations',$booking->id) }}" class="pd-ops-form" id="payment-ops-form">@csrf
            <label><span>Payment Status</span><select class="form-select" name="payment_status" id="payment-status">@foreach($paymentStatuses as $s)<option value="{{ $s }}" @selected($status===$s)>{{ $s }}</option>@endforeach</select></label>
            <div class="pd-two">
                <label><span>Paid Amount</span><input class="form-control" id="paid-amount" type="number" min="0" step="0.01" name="paid_amount" value="{{ old('paid_amount',$paymentMeta->paid_amount) }}" placeholder="0.00"></label>
                <label><span>Refund Amount</span><input class="form-control @error('refund_amount') is-invalid @enderror" id="refund-amount" type="number" min="0" step="0.01" name="refund_amount" value="{{ old('refund_amount',$paymentMeta->refund_amount) }}" placeholder="0.00"><small class="pd-field-error" id="refund-amount-error">@error('refund_amount'){{ $message }}@enderror</small></label>
            </div>
            <div class="pd-live-math" data-booking-amount="{{ $amount }}"><div><small>Outstanding</small><strong id="live-balance">${{ number_format($balance,2) }}</strong></div><div><small>Net Collected</small><strong id="live-net">${{ number_format($netCollected,2) }}</strong></div></div>

            <label><span>Transaction Reference</span><input class="form-control" id="transaction-reference" name="transaction_reference" value="{{ old('transaction_reference',$paymentMeta->transaction_reference) }}" placeholder="Gateway / merchant transaction ref"></label>
            <label><span>Processor / Channel</span><select class="form-select" id="processor" name="processor"><option value="">Select processor/channel</option>@foreach($processors as $processor)<option value="{{ $processor }}" @selected($currentProcessor===$processor)>{{ $processor }}</option>@endforeach @if($currentProcessor && !in_array($currentProcessor,$processors,true))<option value="{{ $currentProcessor }}" selected>{{ $currentProcessor }}</option>@endif</select></label>
            <label><span>Refund Reference</span><input class="form-control" id="refund-reference" name="refund_reference" value="{{ old('refund_reference',$paymentMeta->refund_reference) }}" placeholder="Refund reference"></label>
            <label><span>Refund Reason</span><textarea class="form-control @error('refund_reason') is-invalid @enderror" id="refund-reason" rows="2" name="refund_reason" placeholder="Why is money being refunded?">{{ old('refund_reason',$paymentMeta->refund_reason ?? '') }}</textarea><small class="pd-field-error" id="refund-reason-error">@error('refund_reason'){{ $message }}@enderror</small></label>

            <label><span>Assigned Agent</span><select class="form-select" name="assigned_to"><option value="">Unassigned</option>@foreach($admins as $a)<option value="{{ $a->id }}" @selected((string)$paymentMeta->assigned_to===(string)$a->id)>{{ $a->name }}</option>@endforeach</select></label>
            <label><span>Next Action</span><input class="form-control" id="next-action" name="next_action" value="{{ old('next_action',$paymentMeta->next_action) }}" placeholder="Verify payment / call customer / follow up"></label>
            <label><span>Next Action Date & Time</span><input class="form-control" id="next-action-at" type="datetime-local" name="next_action_at" value="{{ old('next_action_at',$paymentMeta->next_action_at ? \Carbon\Carbon::parse($paymentMeta->next_action_at)->format('Y-m-d\TH:i') : '') }}"></label>
            <label><span>Operations Note</span><textarea class="form-control" rows="4" name="operations_note" placeholder="Internal note — never enter full card number or CVV">{{ old('operations_note',$paymentMeta->operations_note) }}</textarea></label>
            <div class="pd-form-note"><i class="fa-solid fa-shield-halved"></i><span>Refund cannot exceed paid amount. Balance and net collected are calculated automatically.</span></div>
            <button class="btn btn-primary w-100"><i class="fa-solid fa-floppy-disk me-1"></i>Save Payment Operations</button>
        </form>
    </div>
</section>
</aside>
</div>
</div>

<script>
(function(){
    const bookingAmount = Number(document.querySelector('.pd-live-math')?.dataset.bookingAmount || 0);
    const paid = document.getElementById('paid-amount');
    const refund = document.getElementById('refund-amount');
    const balance = document.getElementById('live-balance');
    const net = document.getElementById('live-net');
    const status = document.getElementById('payment-status');

    function recalc(){
        const p = Math.max(0, Number(paid?.value || 0));
        const r = Math.max(0, Number(refund?.value || 0));
        if(balance) balance.textContent = '$' + Math.max(0, bookingAmount - p).toFixed(2);
        if(net) net.textContent = '$' + Math.max(0, p - r).toFixed(2);
    }
    paid?.addEventListener('input', recalc); refund?.addEventListener('input', recalc); recalc();

    document.querySelectorAll('[data-payment-action]').forEach(function(a){
        a.addEventListener('click', function(){
            const action=a.dataset.paymentAction;
            setTimeout(function(){
                if(action==='note'){
                    document.getElementById('payment-activity-type').value='note';
                    document.getElementById('payment-activity-title')?.focus();
                }
                if(action==='followup') document.getElementById('next-action')?.focus();
                if(action==='paid'){
                    if(status) status.value='Paid';
                    paid?.focus();
                }
                if(action==='refund'){
                    document.getElementById('refund-amount')?.focus();
                }
            },120);
        });
    });

    document.getElementById('payment-ops-form')?.addEventListener('submit', function(e){
        const p=Math.max(0,Number(paid?.value||0));
        const r=Math.max(0,Number(refund?.value||0));
        const refundError=document.getElementById('refund-amount-error');
        const reason=document.getElementById('refund-reason');
        const reasonError=document.getElementById('refund-reason-error');
        let invalid=false;

        refund?.classList.remove('is-invalid');
        reason?.classList.remove('is-invalid');
        if(refundError) refundError.textContent='';
        if(reasonError) reasonError.textContent='';

        if(r>p){
            e.preventDefault(); invalid=true;
            refund?.classList.add('is-invalid');
            if(refundError) refundError.textContent='Refund amount cannot exceed paid amount ($'+p.toFixed(2)+').';
            refund?.focus();
        } else if(r>0 && !reason?.value.trim()){
            e.preventDefault(); invalid=true;
            reason?.classList.add('is-invalid');
            if(reasonError) reasonError.textContent='Please enter a refund reason.';
            reason?.focus();
        }
        if(invalid) document.getElementById('payment-operations')?.scrollIntoView({behavior:'smooth',block:'start'});
    });
})();
</script>
@endsection