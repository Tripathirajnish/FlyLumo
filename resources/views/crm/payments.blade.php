@extends('crm.layout')

@section('content')

<style id="fareshunt-payments-clean-v1">
:root{
    --pm-navy:#08244f;
    --pm-blue:#2563eb;
    --pm-red:#e3194a;
    --pm-line:#dbe6f2;
    --pm-muted:#74849a;
}

.pm-page{
    font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Arial,sans-serif!important;
}

.pm-page-head{
    display:flex;
    align-items:flex-end;
    justify-content:space-between;
    gap:20px;
    margin-bottom:18px;
}
.pm-eyebrow{
    display:flex;
    align-items:center;
    gap:12px;
    margin-bottom:7px;
    color:#1d5bd2;
    text-transform:uppercase;
    letter-spacing:.12em;
    font-size:10px;
    font-weight:800;
}
.pm-eyebrow:before{
    content:"";
    width:30px;
    height:3px;
    border-radius:999px;
    background:var(--pm-red);
}
.pm-page-head h1{
    margin:0;
    color:var(--pm-navy);
    font-size:34px;
    line-height:1.05;
    font-weight:700;
    letter-spacing:-.03em;
}
.pm-page-head p{
    margin:7px 0 0;
    color:var(--pm-muted);
    font-size:14px;
}
.pm-page-head .btn{
    min-height:44px;
    padding:0 16px!important;
    display:inline-flex;
    align-items:center;
    border-radius:12px!important;
    box-shadow:0 10px 22px rgba(37,99,235,.15);
}

/* KPI */
.pm-kpis{
    display:grid;
    grid-template-columns:repeat(6,minmax(0,1fr));
    gap:12px;
    margin-bottom:14px;
}
.pm-kpi{
    min-height:86px;
    display:flex;
    align-items:center;
    gap:12px;
    padding:14px;
    border:1px solid var(--pm-line);
    border-radius:16px;
    background:#fff;
    box-shadow:0 8px 20px rgba(12,44,88,.04);
}
.pm-kpi>span{
    width:42px;
    height:42px;
    display:grid;
    place-items:center;
    border-radius:12px;
    font-size:16px;
}
.pm-kpi.blue>span{background:#edf4ff;color:#1f5bd8}
.pm-kpi.green>span{background:#eaf8f0;color:#16804b}
.pm-kpi.amber>span{background:#fff5df;color:#b87107}
.pm-kpi.red>span{background:#fff0f3;color:#d3294d}
.pm-kpi.purple>span{background:#f2ebff;color:#7440c6}
.pm-kpi.cyan>span{background:#eaf9fb;color:#10889a}
.pm-kpi small{
    display:block;
    color:#7c8ca1;
    font-size:9px;
    text-transform:uppercase;
    font-weight:800;
    letter-spacing:.05em;
}
.pm-kpi strong{
    display:block;
    margin-top:3px;
    color:var(--pm-navy);
    font-size:23px;
    line-height:1;
}

/* FILTERS */
.pm-toolbar{
    padding:18px!important;
    margin-bottom:14px;
    border:1px solid var(--pm-line)!important;
    border-radius:18px!important;
}
.pm-filters{
    display:grid!important;
    grid-template-columns:minmax(330px,1.45fr) minmax(190px,.7fr) minmax(190px,.7fr) minmax(190px,.7fr);
    gap:12px;
    align-items:start;
}
.pm-search{
    position:relative;
}
.pm-search i{
    position:absolute;
    left:15px;
    top:23px;
    transform:translateY(-50%);
    z-index:2;
    color:#6f85a2;
}
.pm-search input,
.pm-filters .form-select,
.pm-filters .form-control{
    width:100%;
    height:46px!important;
    min-height:46px!important;
    border:1px solid #d6e2ef!important;
    border-radius:12px!important;
    background:#fff!important;
    color:#173555!important;
    font-size:14px!important;
    box-shadow:none!important;
}
.pm-search input{
    padding:0 14px 0 42px;
    outline:none;
}
.pm-search input:focus,
.pm-filters .form-select:focus,
.pm-filters .form-control:focus{
    border-color:#79a3e5!important;
    box-shadow:0 0 0 4px rgba(31,91,216,.07)!important;
}
.pm-filter-group{
    min-width:0;
}
.pm-filter-label{
    display:block;
    margin:0 0 6px 2px;
    color:#7a899c;
    font-size:10px;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.06em;
}
.pm-tools-row{
    margin-top:6px;
    display:flex;
    justify-content:flex-end;
    gap:6px;
}
.pm-tools-row .crm-ua-form-tools{
    margin:0!important;
}

/* Keep Copy/Clear buttons, but align them cleanly */
.pm-page .crm-ua-form-tools{
    display:flex!important;
    align-items:center!important;
    justify-content:flex-end!important;
    gap:6px!important;
    margin-top:6px!important;
}
.pm-page .crm-ua-form-tools button,
.pm-page .crm-ua-form-tools .btn{
    min-height:30px!important;
    padding:0 9px!important;
    border-radius:8px!important;
    font-size:11px!important;
}

.pm-filter-row-2{
    grid-column:1/-1;
    display:grid;
    grid-template-columns:minmax(180px,.75fr) minmax(170px,.75fr) minmax(170px,.75fr) 150px auto auto 1fr;
    gap:12px;
    align-items:start;
    margin-top:2px;
}
.pm-page-size{
    width:150px!important;
}
.pm-apply{
    min-width:110px;
    height:46px;
    border-radius:11px!important;
}
.pm-clear-main{
    min-width:86px;
    height:46px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border:1px solid #dbe5f0!important;
    background:#fff!important;
    color:#5c7089!important;
    border-radius:11px!important;
}
.pm-records{
    justify-self:end;
    align-self:center;
    white-space:nowrap;
}

/* remove old oval/pill visual */
.pm-toolbar > .soft{
    display:none!important;
}

/* TABLE */
.pm-table-card{
    overflow:hidden!important;
    border:1px solid var(--pm-line)!important;
    border-radius:18px!important;
}
.pm-table-wrap{
    width:100%;
    overflow-x:auto;
}
.pm-table{
    width:max-content!important;
    min-width:1560px!important;
    border-collapse:separate!important;
    border-spacing:0!important;
}
.pm-table th{
    padding:13px 12px!important;
    background:#f7faff!important;
    color:#5b708c!important;
    font-size:10px!important;
    font-weight:800!important;
    text-transform:uppercase;
    letter-spacing:.04em;
    white-space:nowrap;
}
.pm-table td{
    padding:14px 12px!important;
    border-bottom:1px solid #edf1f6!important;
    color:#38536f;
    font-size:12px!important;
    vertical-align:middle;
    white-space:nowrap;
}
.pm-table td:nth-child(3){
    white-space:normal;
    min-width:270px;
}
.pm-table td strong{
    color:#153555;
    font-size:13px;
}
.pm-table td small{
    display:block;
    margin-top:3px;
    color:#8796a8;
    font-size:10px;
}
.pm-booking{
    color:#0d438f!important;
    font-size:13px;
    font-weight:850!important;
    text-decoration:none!important;
}
.pm-badge{
    display:inline-flex;
    align-items:center;
    padding:6px 9px;
    border-radius:999px;
    font-size:10px;
    font-weight:800;
}
.pm-badge.green{background:#eaf8f0;color:#16804b}
.pm-badge.amber{background:#fff5df;color:#b87107}
.pm-badge.red{background:#fff0f3;color:#d3294d}
.pm-badge.purple{background:#f2ebff;color:#7440c6}
.pm-badge.slate{background:#eef2f6;color:#5a6d84}
.pm-card-pill{
    display:inline-flex;
    align-items:center;
    padding:7px 10px;
    border-radius:10px;
    background:#f1f4f8;
    color:#173555;
    letter-spacing:.08em;
    font-weight:700;
}
.pm-open{
    width:34px;
    height:34px;
    display:grid;
    place-items:center;
    border:1px solid #d7e2ee;
    border-radius:10px;
    background:#fff;
    color:#1f5bd8;
    text-decoration:none;
}
.pm-action-cell{
    display:flex;
    gap:6px;
    align-items:center;
}

/* PAGINATION */
.pm-pagination{
    min-height:62px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:16px;
    padding:13px 16px;
    border-top:1px solid #e4ebf3;
    color:#74849a;
    font-size:12px;
}
.pm-pagination b{color:#17395f}
.pm-pagination>div{
    display:flex;
    align-items:center;
    gap:8px;
}
.pm-pagination a,
.pm-pagination>div>span{
    min-height:32px;
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:0 10px;
    border:1px solid #dbe5f0;
    border-radius:9px;
    background:#fff;
    color:#395778;
    text-decoration:none;
}
.pm-pagination .disabled{opacity:.5}

@media(max-width:1250px){
    .pm-kpis{grid-template-columns:repeat(3,1fr)}
    .pm-filters{grid-template-columns:1fr 1fr}
    .pm-search{grid-column:1/-1}
    .pm-filter-row-2{grid-template-columns:1fr 1fr}
    .pm-records{justify-self:start}
}
@media(max-width:760px){
    .pm-page-head{flex-direction:column;align-items:flex-start}
    .pm-kpis{grid-template-columns:1fr 1fr}
    .pm-filters,
    .pm-filter-row-2{grid-template-columns:1fr}
    .pm-page-size{width:100%!important}
    .pm-pagination{flex-direction:column;align-items:flex-start}
}
</style>

@php
    $tone = fn($s) => match($s){
        'Paid' => 'green',
        'Pending','Partially Paid' => 'amber',
        'Declined','Failed','Chargeback' => 'red',
        'Refund Pending','Partially Refunded','Refunded' => 'purple',
        default => 'slate'
    };
@endphp

<div class="pm-page">

<div class="pm-page-head">
    <div>
        <span class="pm-eyebrow">Payment operations</span>
        <h1>Payments</h1>
        <p>Monitor payment status, masked card metadata, refunds and payment follow-up without exposing sensitive card credentials.</p>
    </div>

    <a class="btn btn-primary"
       href="{{ route('crm.export','payments') }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}">
        <i class="fa-solid fa-download me-2"></i>Export CSV
    </a>
</div>

<section class="pm-kpis">
    <div class="pm-kpi blue">
        <span><i class="fa-solid fa-wallet"></i></span>
        <div><small>Total Value</small><strong>${{ number_format($stats['total_value'],2) }}</strong></div>
    </div>

    <div class="pm-kpi green">
        <span><i class="fa-solid fa-circle-check"></i></span>
        <div><small>Paid</small><strong>{{ $stats['paid'] }}</strong></div>
    </div>

    <div class="pm-kpi amber">
        <span><i class="fa-regular fa-clock"></i></span>
        <div><small>Pending / Untracked</small><strong>{{ $stats['pending'] }}</strong></div>
    </div>

    <div class="pm-kpi red">
        <span><i class="fa-solid fa-triangle-exclamation"></i></span>
        <div><small>Payment Issues</small><strong>{{ $stats['issues'] }}</strong></div>
    </div>

    <div class="pm-kpi purple">
        <span><i class="fa-solid fa-arrow-rotate-left"></i></span>
        <div><small>Refunds</small><strong>{{ $stats['refunds'] }}</strong></div>
    </div>

    <div class="pm-kpi cyan">
        <span><i class="fa-regular fa-credit-card"></i></span>
        <div><small>Card Metadata</small><strong>{{ $stats['metadata_available'] }}</strong></div>
    </div>
</section>

<section class="crm-card pm-toolbar">
    <form method="GET" class="pm-filters">

        <div class="pm-filter-group pm-search">
            <span class="pm-filter-label">Search payments</span>
            <i class="fa-solid fa-magnifying-glass"></i>
            <input name="q"
                   value="{{ request('q') }}"
                   placeholder="FH booking ID, customer, phone, transaction ref or processor">
        </div>

        <div class="pm-filter-group">
            <span class="pm-filter-label">Payment status</span>
            <select class="form-select" name="payment_status">
                <option value="">All payment statuses</option>
                @foreach($paymentStatuses as $s)
                    <option value="{{ $s }}" @selected(request('payment_status')===$s)>{{ $s }}</option>
                @endforeach
            </select>
        </div>

        <div class="pm-filter-group">
            <span class="pm-filter-label">Payment method</span>
            <select class="form-select" name="method">
                <option value="">All payment methods</option>
                @foreach($paymentMethods as $m)
                    <option value="{{ $m }}" @selected(request('method')===$m)>{{ $m }}</option>
                @endforeach
            </select>
        </div>

        <div class="pm-filter-group">
            <span class="pm-filter-label">Booking status</span>
            <select class="form-select" name="booking_status">
                <option value="">All booking statuses</option>
                @foreach($bookingStatuses as $s)
                    <option value="{{ $s }}" @selected(request('booking_status')===$s)>{{ $s }}</option>
                @endforeach
            </select>
        </div>

        <div class="pm-filter-row-2">
            <div class="pm-filter-group">
                <span class="pm-filter-label">From date</span>
                <input class="form-control" type="date" name="date_from" value="{{ request('date_from') }}">
            </div>

            <div class="pm-filter-group">
                <span class="pm-filter-label">To date</span>
                <input class="form-control" type="date" name="date_to" value="{{ request('date_to') }}">
            </div>

            <div class="pm-filter-group">
                <span class="pm-filter-label">Rows</span>
                <select class="form-select pm-page-size" name="per_page">
                    @foreach([15,20,25,50,100] as $size)
                        <option value="{{ $size }}" @selected((int)request('per_page',20)===$size)>{{ $size }} / page</option>
                    @endforeach
                </select>
            </div>

            <button class="btn btn-primary pm-apply">
                <i class="fa-solid fa-filter me-1"></i>Apply
            </button>

            @if(request()->hasAny(['q','payment_status','method','booking_status','date_from','date_to','per_page']))
                <a class="btn pm-clear-main" href="{{ route('crm.payments') }}">Clear filters</a>
            @endif

            <span class="soft blue pm-records">{{ number_format($bookings->total()) }} records</span>
            <div></div>
        </div>

    </form>
</section>

<section class="crm-card pm-table-card">
    <div class="pm-table-wrap">
        <table class="crm-table pm-table">
            <thead>
                <tr>
                    <th>S.No.</th>
                    <th>Booking</th>
                    <th>Customer</th>
                    <th>Trip</th>
                    <th>Amount</th>
                    <th>Payment Status</th>
                    <th>Method</th>
                    <th>Card</th>
                    <th>Transaction Ref</th>
                    <th>Processor</th>
                    <th>Booking Status</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
            @forelse($payments as $p)
                @php $b = $p['booking']; @endphp

                <tr>
                    <td>{{ $bookings->firstItem()+$loop->index }}</td>

                    <td>
                        <a class="pm-booking" href="{{ route('crm.payment_detail',$b->id) }}">
                            #FH{{ $b->id }}
                        </a>
                    </td>

                    <td>
                        <strong>{{ $b->o_Name ?: 'Guest' }}</strong>
                        <small>{{ $b->o_Email ?: '—' }} · {{ $b->o_Phone ?: '—' }}</small>
                    </td>

                    <td><strong>{{ $p['route'] }}</strong></td>

                    <td><strong>{{ $b->o_FullPrice ?: '—' }}</strong></td>

                    <td>
                        <span class="pm-badge {{ $tone($p['payment_status']) }}">
                            {{ $p['payment_status'] }}
                        </span>
                    </td>

                    <td>{{ $p['brand'] }}</td>

                    <td>
                        @if($p['last4'])
                            <span class="pm-card-pill">**** {{ $p['last4'] }}</span>
                        @else
                            —
                        @endif
                    </td>

                    <td>{{ $p['transaction_reference'] ?: '—' }}</td>
                    <td>{{ $p['processor'] ?: '—' }}</td>

                    <td>
                        <span class="soft {{ $b->status==='Fresh'?'green':'blue' }}">
                            {{ $b->status ?: 'Unspecified' }}
                        </span>
                    </td>

                    <td>
                        {{ optional($b->created_at)->format('d M Y') }}
                        <small>{{ optional($b->created_at)->format('h:i A') }}</small>
                    </td>

                    <td>
                        <div class="pm-action-cell">
                            <a class="pm-open"
                               href="{{ route('crm.payment_detail',$b->id) }}"
                               title="Open payment">
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>

                            <form method="POST"
                                  action="{{ route('crm.universal_archive') }}"
                                  onsubmit="return confirm('Move this payment to CRM Trash?')">
                                @csrf
                                <input type="hidden" name="module" value="payment">
                                <input type="hidden" name="record_key" value="{{ $b->id }}">
                                <input type="hidden" name="label" value="Payment · #FH{{ $b->id }}">

                                <button class="crm-ua-btn danger" title="Delete / Archive">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="13" class="empty">No payment records found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="pm-pagination">
        <span>
            Showing <b>{{ $bookings->firstItem() ?? 0 }}</b>
            to <b>{{ $bookings->lastItem() ?? 0 }}</b>
            of <b>{{ $bookings->total() }}</b> records
        </span>

        <div>
            @if($bookings->onFirstPage())
                <span class="disabled">
                    <i class="fa-solid fa-chevron-left"></i> Previous
                </span>
            @else
                <a href="{{ $bookings->previousPageUrl() }}">
                    <i class="fa-solid fa-chevron-left"></i> Previous
                </a>
            @endif

            <span>Page {{ $bookings->currentPage() }} of {{ $bookings->lastPage() }}</span>

            @if($bookings->hasMorePages())
                <a href="{{ $bookings->nextPageUrl() }}">
                    Next <i class="fa-solid fa-chevron-right"></i>
                </a>
            @else
                <span class="disabled">
                    Next <i class="fa-solid fa-chevron-right"></i>
                </span>
            @endif
        </div>
    </div>
</section>

</div>

@endsection