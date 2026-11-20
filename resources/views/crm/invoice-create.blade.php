@extends('crm.layout')
@section('content')
<style>
.inv-prep{font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Arial,sans-serif;color:#173555}.inv-prep-head{display:flex;justify-content:space-between;align-items:flex-start;gap:15px;margin-bottom:16px}.inv-prep h1{margin:5px 0 0;color:#071f4f;font-size:30px}.inv-card{background:#fff;border:1px solid #dbe6f2;border-radius:18px;padding:18px;margin-bottom:14px}.inv-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:11px}.inv-field label{display:block;margin:0 0 5px 2px;color:#71839a;font-size:9px;text-transform:uppercase;font-weight:800}.inv-field input,.inv-field textarea{width:100%;min-height:46px;border:1px solid #d6e2ef;border-radius:11px;background:#fbfdff;padding:9px 11px;font-size:12px}.inv-field textarea{min-height:86px}.inv-total{display:flex;justify-content:space-between;align-items:center;background:#071f4f;color:#fff;border-radius:13px;padding:13px 15px}.inv-total strong{font-size:26px}.inv-old{display:grid;gap:8px}.inv-old a{display:flex;justify-content:space-between;align-items:center;padding:10px 12px;border:1px solid #e1eaf3;border-radius:11px;text-decoration:none;color:#244766}@media(max-width:700px){.inv-grid{grid-template-columns:1fr}.inv-prep-head{flex-direction:column}}
</style>

<div class="inv-prep">
    <div class="inv-prep-head">
        <div>
            <a href="{{ route('crm.booking_detail',$booking->id) }}" class="crm-back"><i class="fa-solid fa-arrow-left me-1"></i>Back to booking</a>
            <h1>Generate Invoice</h1>
            <div>#FH{{ $booking->id }} · {{ $booking->o_Name }}</div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('crm.invoice_store',$booking->id) }}">
        @csrf

        <section class="inv-card">
            <h5>Customer & invoice</h5>
            <div class="inv-grid">
                <div class="inv-field"><label>Customer name</label><input name="customer_name" value="{{ old('customer_name',$booking->o_Name) }}" required></div>
                <div class="inv-field"><label>Email</label><input type="email" name="customer_email" value="{{ old('customer_email',$booking->o_Email) }}"></div>
                <div class="inv-field"><label>Phone</label><input name="customer_phone" value="{{ old('customer_phone',$booking->o_Phone) }}"></div>
                <div class="inv-field"><label>Issue date</label><input type="date" name="issue_date" value="{{ old('issue_date',now()->format('Y-m-d')) }}" required></div>
                <div class="inv-field"><label>Due date</label><input type="date" name="due_date" value="{{ old('due_date') }}"></div>
                <div class="inv-field"><label>Currency</label><input name="currency" maxlength="3" value="{{ old('currency','USD') }}" required></div>
            </div>
        </section>

        <section class="inv-card">
            <h5>Invoice breakdown</h5>
            <div class="inv-grid">
                <div class="inv-field"><label>Base fare</label><input type="number" step="0.01" min="0" name="base_fare" class="inv-money" value="{{ old('base_fare',$defaults['base_fare']) }}" required></div>
                <div class="inv-field"><label>Taxes</label><input type="number" step="0.01" min="0" name="taxes" class="inv-money" value="{{ old('taxes',$defaults['taxes']) }}"></div>
                <div class="inv-field"><label>Service fee / markup</label><input type="number" step="0.01" min="0" name="service_fee" class="inv-money" value="{{ old('service_fee',$defaults['service_fee']) }}"></div>
                <div class="inv-field"><label>Add-ons</label><input type="number" step="0.01" min="0" name="addons" class="inv-money" value="{{ old('addons',$defaults['addons']) }}"></div>
                <div class="inv-field"><label>Discount</label><input type="number" step="0.01" min="0" name="discount" class="inv-money" value="{{ old('discount',$defaults['discount']) }}"></div>
                <div class="inv-field"><label>Amount paid</label><input type="number" step="0.01" min="0" name="paid_amount" value="{{ old('paid_amount',$defaults['paid_amount']) }}"></div>
            </div>
            <div class="inv-total mt-3"><span>Invoice total</span><strong id="invoiceCalc">$0.00</strong></div>
        </section>

        <section class="inv-card">
            <div class="inv-field">
                <label>Invoice notes</label>
                <textarea name="notes" placeholder="Terms, payment note, refund note or customer-facing message...">{{ old('notes') }}</textarea>
            </div>
        </section>

        <div class="d-flex gap-2 justify-content-end">
            <a class="btn btn-light" href="{{ route('crm.booking_detail',$booking->id) }}">Cancel</a>
            <button class="btn btn-primary"><i class="fa-solid fa-file-invoice-dollar me-1"></i>Generate Invoice</button>
        </div>
    </form>

    @if($invoices->count())
    <section class="inv-card mt-4">
        <h5>Previous invoices</h5>
        <div class="inv-old">
            @foreach($invoices as $row)
            <a href="{{ route('crm.invoice_show',$row->id) }}" target="_blank">
                <span><b>{{ $row->invoice_number }}</b><small class="d-block">{{ \Carbon\Carbon::parse($row->issue_date)->format('d M Y') }}</small></span>
                <strong>${{ number_format((float)$row->total_amount,2) }}</strong>
            </a>
            @endforeach
        </div>
    </section>
    @endif
</div>

<script>
(function(){
    function calc(){
        let v=n=>parseFloat(document.querySelector(`[name="${n}"]`)?.value||0)||0;
        let t=Math.max(0,v('base_fare')+v('taxes')+v('service_fee')+v('addons')-v('discount'));
        document.getElementById('invoiceCalc').textContent='$'+t.toFixed(2);
    }
    document.querySelectorAll('.inv-money').forEach(e=>e.addEventListener('input',calc));
    calc();
})();
</script>
@endsection
