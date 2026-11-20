@extends('crm.layout')

@section('content')

<style id="fareshunt-refunds-v1">
:root{
    --rf-navy:#08244f;
    --rf-blue:#2563eb;
    --rf-red:#e3194a;
    --rf-line:#dbe6f2;
    --rf-muted:#74849a;
}

.rf-page{
    font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Arial,sans-serif!important;
}

.rf-head{
    display:flex;
    align-items:flex-end;
    justify-content:space-between;
    gap:20px;
    margin-bottom:18px;
}
.rf-eyebrow{
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
.rf-eyebrow:before{
    content:"";
    width:30px;
    height:3px;
    border-radius:999px;
    background:var(--rf-red);
}
.rf-head h1{
    margin:0;
    color:var(--rf-navy);
    font-size:34px;
    line-height:1.05;
    font-weight:700;
    letter-spacing:-.03em;
}
.rf-head p{
    margin:7px 0 0;
    color:var(--rf-muted);
    font-size:14px;
}

.rf-module-tabs{
    display:flex;
    align-items:center;
    gap:8px;
    margin:0 0 16px;
    padding:5px;
    width:max-content;
    max-width:100%;
    border:1px solid var(--rf-line);
    border-radius:12px;
    background:#fff;
}
.rf-module-tab{
    display:inline-flex;
    align-items:center;
    gap:7px;
    min-height:38px;
    padding:0 14px;
    border-radius:9px;
    color:#55708f;
    font-size:13px;
    font-weight:700;
    text-decoration:none;
    white-space:nowrap;
}
.rf-module-tab:hover{
    color:#1f5bd8;
    background:#f5f8fc;
}
.rf-module-tab.active{
    background:#edf4ff;
    color:#1f5bd8;
}

.rf-kpis{
    display:grid;
    grid-template-columns:repeat(5,minmax(0,1fr));
    gap:12px;
    margin-bottom:14px;
}
.rf-kpi{
    min-height:86px;
    display:flex;
    align-items:center;
    gap:12px;
    padding:14px;
    border:1px solid var(--rf-line);
    border-radius:16px;
    background:#fff;
    box-shadow:0 8px 20px rgba(12,44,88,.04);
}
.rf-kpi>span{
    width:42px;
    height:42px;
    display:grid;
    place-items:center;
    border-radius:12px;
}
.rf-kpi.blue>span{background:#edf4ff;color:#1f5bd8}
.rf-kpi.amber>span{background:#fff5df;color:#b87107}
.rf-kpi.purple>span{background:#f2ebff;color:#7440c6}
.rf-kpi.green>span{background:#eaf8f0;color:#16804b}
.rf-kpi.cyan>span{background:#eaf9fb;color:#10889a}
.rf-kpi small{
    display:block;
    color:#7c8ca1;
    font-size:9px;
    text-transform:uppercase;
    font-weight:800;
}
.rf-kpi strong{
    display:block;
    margin-top:3px;
    color:var(--rf-navy);
    font-size:22px;
}

.rf-toolbar{
    padding:18px!important;
    margin-bottom:14px;
    border:1px solid var(--rf-line)!important;
    border-radius:18px!important;
}
.rf-filters{
    display:grid;
    grid-template-columns:minmax(300px,1.4fr) minmax(220px,.6fr) auto;
    gap:12px;
    align-items:end;
}
.rf-field label{
    display:block;
    margin:0 0 6px 2px;
    color:#7a899c;
    font-size:10px;
    font-weight:800;
    text-transform:uppercase;
}
.rf-field input,
.rf-field select{
    width:100%;
    height:46px!important;
    border:1px solid #d6e2ef!important;
    border-radius:12px!important;
    background:#fff!important;
    color:#173555!important;
    font-size:14px!important;
}
.rf-actions{
    display:flex;
    gap:8px;
}

.rf-table-card{
    overflow:hidden!important;
    border:1px solid var(--rf-line)!important;
    border-radius:18px!important;
}
.rf-table-wrap{
    width:100%;
    overflow-x:auto;
}
.rf-table{
    width:max-content!important;
    min-width:1500px!important;
    border-collapse:separate!important;
    border-spacing:0!important;
}
.rf-table th{
    padding:13px 12px!important;
    background:#f7faff!important;
    color:#5b708c!important;
    font-size:10px!important;
    font-weight:800!important;
    text-transform:uppercase;
    white-space:nowrap;
}
.rf-table td{
    padding:14px 12px!important;
    border-bottom:1px solid #edf1f6!important;
    color:#38536f;
    font-size:12px!important;
    vertical-align:middle;
    white-space:nowrap;
}
.rf-table td strong{
    color:#153555;
}
.rf-table td small{
    display:block;
    margin-top:3px;
    color:#8796a8;
    font-size:10px;
}
.rf-link{
    color:#0d438f!important;
    font-weight:800;
    text-decoration:none!important;
}
.rf-badge{
    display:inline-flex;
    align-items:center;
    padding:6px 9px;
    border-radius:999px;
    font-size:10px;
    font-weight:800;
}
.rf-badge.green{background:#eaf8f0;color:#16804b}
.rf-badge.amber{background:#fff5df;color:#b87107}
.rf-badge.red{background:#fff0f3;color:#d3294d}
.rf-badge.purple{background:#f2ebff;color:#7440c6}
.rf-badge.blue{background:#edf4ff;color:#1f5bd8}
.rf-badge.slate{background:#eef2f6;color:#5a6d84}

.rf-open{
    width:34px;
    height:34px;
    display:inline-grid;
    place-items:center;
    border:1px solid #d7e2ee;
    border-radius:10px;
    background:#fff;
    color:#1f5bd8;
    text-decoration:none;
    margin-right:5px;
}

.rf-pagination{
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
.rf-pagination b{color:#17395f}
.rf-pagination>div{
    display:flex;
    align-items:center;
    gap:8px;
}
.rf-pagination a,
.rf-pagination>div>span{
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
.rf-pagination .disabled{opacity:.5}

@media(max-width:1100px){
    .rf-kpis{grid-template-columns:repeat(3,1fr)}
}
@media(max-width:760px){
    .rf-head{flex-direction:column;align-items:flex-start}
    .rf-kpis{grid-template-columns:1fr 1fr}
    .rf-filters{grid-template-columns:1fr}
    .rf-pagination{flex-direction:column;align-items:flex-start}
}
</style>

@php
    $statusTone = fn($status) => match($status){
        'Refunded','Closed' => 'green',
        'Requested','Under Review' => 'amber',
        'Approved','Processing' => 'purple',
        'Rejected','Cancelled' => 'red',
        default => 'slate',
    };
@endphp

<div class="rf-page">

    <div class="rf-head">
        <div>
            <span class="rf-eyebrow">Refund operations</span>
            <h1>Refunds</h1>
            <p>Track refund requests, approvals, processing and completed refunds with a full case history.</p>
        </div>

        @if(app(\App\Services\CrmAccessService::class)->can((int) session('crm_admin_id'),'refunds.request'))
            <div>
                <a href="{{ route('crm.refund_create') }}" class="rf-btn">
                    <i class="fa-solid fa-plus"></i>
                    New Refund Request
                </a>
            </div>
        @endif
    </div>

    <nav class="rf-module-tabs" aria-label="Refunds and Chargebacks">
        <a href="{{ route('crm.refunds') }}" class="rf-module-tab active">
            <i class="fa-solid fa-rotate-left"></i>
            Refunds
        </a>
        <a href="{{ route('crm.chargebacks') }}" class="rf-module-tab">
            <i class="fa-solid fa-shield-halved"></i>
            Chargebacks
        </a>
    </nav>

    <section class="rf-kpis">
        <div class="rf-kpi blue">
            <span><i class="fa-solid fa-layer-group"></i></span>
            <div><small>Total Cases</small><strong>{{ number_format($stats['total']) }}</strong></div>
        </div>

        <div class="rf-kpi amber">
            <span><i class="fa-regular fa-clock"></i></span>
            <div><small>Open</small><strong>{{ number_format($stats['open']) }}</strong></div>
        </div>

        <div class="rf-kpi purple">
            <span><i class="fa-solid fa-hourglass-half"></i></span>
            <div><small>Pending Review</small><strong>{{ number_format($stats['pending']) }}</strong></div>
        </div>

        <div class="rf-kpi green">
            <span><i class="fa-solid fa-circle-check"></i></span>
            <div><small>Refunded</small><strong>{{ number_format($stats['refunded']) }}</strong></div>
        </div>

        <div class="rf-kpi cyan">
            <span><i class="fa-solid fa-dollar-sign"></i></span>
            <div><small>Refunded Amount</small><strong>${{ number_format($stats['refunded_amount'],2) }}</strong></div>
        </div>
    </section>

    <section class="crm-card rf-toolbar">
        <form method="GET" class="rf-filters">
            <div class="rf-field">
                <label>Search cases</label>
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Case ref, booking ID, transaction or refund reference">
            </div>

            <div class="rf-field">
                <label>Status</label>
                <select name="status">
                    <option value="">All statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status')===$status)>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="rf-actions">
                <button class="btn btn-primary" type="submit">
                    <i class="fa-solid fa-magnifying-glass me-1"></i>Filter
                </button>

                @if(request()->hasAny(['q','status']))
                    <a class="btn btn-light" href="{{ route('crm.refunds') }}">Clear</a>
                @endif
            </div>
        </form>
    </section>

    <section class="crm-card rf-table-card">
        <div class="rf-table-wrap">
            <table class="crm-table rf-table">
                <thead>
                    <tr>
                        <th>S.No.</th>
                        <th>Case</th>
                        <th>Booking</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Requested</th>
                        <th>Approved</th>
                        <th>Processed</th>
                        <th>Processor</th>
                        <th>Transaction Ref</th>
                        <th>Assigned</th>
                        <th>Requested</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($cases as $case)
                    @php
                        $booking = $bookings->get($case->booking_id);
                    @endphp

                    <tr>
                        <td>{{ ($cases->firstItem() ?? 1) + $loop->index }}</td>

                        <td>
                            <strong>{{ $case->case_reference }}</strong>
                            <small>{{ $case->currency ?: 'USD' }}</small>
                        </td>

                        <td>
                            <a class="rf-link" href="{{ route('crm.booking_detail',$case->booking_id) }}">
                                #FH{{ $case->booking_id }}
                            </a>
                        </td>

                        <td>
                            <strong>{{ $booking->o_Name ?? 'Guest' }}</strong>
                            <small>{{ $booking->o_Email ?? '—' }}</small>
                        </td>

                        <td>{{ $case->refund_type }}</td>

                        <td>
                            <span class="rf-badge {{ $statusTone($case->status) }}">
                                {{ $case->status }}
                            </span>
                        </td>

                        <td><strong>${{ number_format((float)$case->requested_amount,2) }}</strong></td>

                        <td>
                            {{ $case->approved_amount !== null
                                ? '$'.number_format((float)$case->approved_amount,2)
                                : '—' }}
                        </td>

                        <td>
                            {{ $case->processed_amount !== null
                                ? '$'.number_format((float)$case->processed_amount,2)
                                : '—' }}
                        </td>

                        <td>{{ $case->processor ?: '—' }}</td>
                        <td>{{ $case->transaction_reference ?: '—' }}</td>
                        <td>{{ $case->assigned_name ?: 'Unassigned' }}</td>

                        <td>
                            {{ $case->requested_at ? \Carbon\Carbon::parse($case->requested_at)->format('d M Y') : '—' }}
                            @if($case->requested_at)
                                <small>{{ \Carbon\Carbon::parse($case->requested_at)->format('h:i A') }}</small>
                            @endif
                        </td>

                        <td>
                            <a class="rf-open"
                               href="{{ route('crm.refund_detail',$case->id) }}"
                               title="Open refund case">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>

                            <a class="rf-open"
                               href="{{ route('crm.booking_detail',$case->booking_id) }}"
                               title="Open booking">
                                <i class="fa-solid fa-plane"></i>
                            </a>

                            <a class="rf-open"
                               href="{{ route('crm.payment_detail',$case->booking_id) }}"
                               title="Open payment">
                                <i class="fa-regular fa-credit-card"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="14" class="empty">No refund cases found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="rf-pagination">
            <span>
                Showing <b>{{ $cases->firstItem() ?? 0 }}</b>
                to <b>{{ $cases->lastItem() ?? 0 }}</b>
                of <b>{{ $cases->total() }}</b> cases
            </span>

            <div>
                @if($cases->onFirstPage())
                    <span class="disabled">
                        <i class="fa-solid fa-chevron-left"></i> Previous
                    </span>
                @else
                    <a href="{{ $cases->previousPageUrl() }}">
                        <i class="fa-solid fa-chevron-left"></i> Previous
                    </a>
                @endif

                <span>Page {{ $cases->currentPage() }} of {{ $cases->lastPage() }}</span>

                @if($cases->hasMorePages())
                    <a href="{{ $cases->nextPageUrl() }}">
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
