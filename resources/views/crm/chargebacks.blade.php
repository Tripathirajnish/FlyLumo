@extends('crm.layout')

@section('content')

<style id="fareshunt-chargebacks-v1">
:root{
    --cb-navy:#08244f;
    --cb-blue:#2563eb;
    --cb-red:#e3194a;
    --cb-line:#dbe6f2;
    --cb-muted:#74849a;
}

.cb-page{
    font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Arial,sans-serif!important;
}
.cb-head{
    display:flex;
    align-items:flex-end;
    justify-content:space-between;
    gap:20px;
    margin-bottom:18px;
}
.cb-eyebrow{
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
.cb-eyebrow:before{
    content:"";
    width:30px;
    height:3px;
    border-radius:999px;
    background:var(--cb-red);
}
.cb-head h1{
    margin:0;
    color:var(--cb-navy);
    font-size:34px;
    line-height:1.05;
    font-weight:700;
    letter-spacing:-.03em;
}
.cb-head p{
    margin:7px 0 0;
    color:var(--cb-muted);
    font-size:14px;
}

.cb-module-tabs{
    display:flex;
    align-items:center;
    gap:8px;
    margin:0 0 16px;
    padding:5px;
    width:max-content;
    max-width:100%;
    border:1px solid var(--cb-line);
    border-radius:12px;
    background:#fff;
}
.cb-module-tab{
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
.cb-module-tab:hover{
    color:#1f5bd8;
    background:#f5f8fc;
}
.cb-module-tab.active{
    background:#edf4ff;
    color:#1f5bd8;
}

.cb-kpis{
    display:grid;
    grid-template-columns:repeat(7,minmax(0,1fr));
    gap:12px;
    margin-bottom:14px;
}
.cb-kpi{
    min-height:86px;
    display:flex;
    align-items:center;
    gap:12px;
    padding:14px;
    border:1px solid var(--cb-line);
    border-radius:16px;
    background:#fff;
    box-shadow:0 8px 20px rgba(12,44,88,.04);
}
.cb-kpi>span{
    width:42px;
    height:42px;
    display:grid;
    place-items:center;
    border-radius:12px;
}
.cb-kpi.blue>span{background:#edf4ff;color:#1f5bd8}
.cb-kpi.amber>span{background:#fff5df;color:#b87107}
.cb-kpi.purple>span{background:#f2ebff;color:#7440c6}
.cb-kpi.green>span{background:#eaf8f0;color:#16804b}
.cb-kpi.red>span{background:#fff0f3;color:#d3294d}
.cb-kpi.cyan>span{background:#eaf9fb;color:#10889a}
.cb-kpi.slate>span{background:#eef2f6;color:#5a6d84}
.cb-kpi small{
    display:block;
    color:#7c8ca1;
    font-size:9px;
    text-transform:uppercase;
    font-weight:800;
}
.cb-kpi strong{
    display:block;
    margin-top:3px;
    color:var(--cb-navy);
    font-size:21px;
}

.cb-toolbar{
    padding:18px!important;
    margin-bottom:14px;
    border:1px solid var(--cb-line)!important;
    border-radius:18px!important;
}
.cb-filters{
    display:grid;
    grid-template-columns:minmax(300px,1.4fr) minmax(220px,.6fr) auto;
    gap:12px;
    align-items:end;
}
.cb-field label{
    display:block;
    margin:0 0 6px 2px;
    color:#7a899c;
    font-size:10px;
    font-weight:800;
    text-transform:uppercase;
}
.cb-field input,
.cb-field select{
    width:100%;
    height:46px!important;
    border:1px solid #d6e2ef!important;
    border-radius:12px!important;
    background:#fff!important;
    color:#173555!important;
    font-size:14px!important;
}
.cb-actions{
    display:flex;
    gap:8px;
}

.cb-table-card{
    overflow:hidden!important;
    border:1px solid var(--cb-line)!important;
    border-radius:18px!important;
}
.cb-table-wrap{
    width:100%;
    overflow-x:auto;
}
.cb-table{
    width:max-content!important;
    min-width:1750px!important;
    border-collapse:separate!important;
    border-spacing:0!important;
}
.cb-table th{
    padding:13px 12px!important;
    background:#f7faff!important;
    color:#5b708c!important;
    font-size:10px!important;
    font-weight:800!important;
    text-transform:uppercase;
    white-space:nowrap;
}
.cb-table td{
    padding:14px 12px!important;
    border-bottom:1px solid #edf1f6!important;
    color:#38536f;
    font-size:12px!important;
    vertical-align:middle;
    white-space:nowrap;
}
.cb-table td strong{color:#153555}
.cb-table td small{
    display:block;
    margin-top:3px;
    color:#8796a8;
    font-size:10px;
}
.cb-link{
    color:#0d438f!important;
    font-weight:800;
    text-decoration:none!important;
}
.cb-badge{
    display:inline-flex;
    align-items:center;
    padding:6px 9px;
    border-radius:999px;
    font-size:10px;
    font-weight:800;
}
.cb-badge.green{background:#eaf8f0;color:#16804b}
.cb-badge.amber{background:#fff5df;color:#b87107}
.cb-badge.red{background:#fff0f3;color:#d3294d}
.cb-badge.purple{background:#f2ebff;color:#7440c6}
.cb-badge.blue{background:#edf4ff;color:#1f5bd8}
.cb-badge.slate{background:#eef2f6;color:#5a6d84}

.cb-open{
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

.cb-create-btn{
    flex:0 0 auto;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    min-height:42px;
    padding:0 16px;
    border:1px solid #d7e2ee;
    border-radius:11px;
    background:#fff;
    color:#1f5bd8;
    font-size:13px;
    font-weight:700;
    line-height:1;
    white-space:nowrap;
    text-decoration:none;
}

.cb-pagination{
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
.cb-pagination b{color:#17395f}
.cb-pagination>div{
    display:flex;
    align-items:center;
    gap:8px;
}
.cb-pagination a,
.cb-pagination>div>span{
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
.cb-pagination .disabled{opacity:.5}

@media(max-width:1300px){
    .cb-kpis{grid-template-columns:repeat(4,1fr)}
}
@media(max-width:760px){
    .cb-head{flex-direction:column;align-items:flex-start}
    .cb-kpis{grid-template-columns:1fr 1fr}
    .cb-filters{grid-template-columns:1fr}
    .cb-pagination{flex-direction:column;align-items:flex-start}
}
</style>

@php
    $tone = fn($status) => match($status){
        'Won','Closed' => 'green',
        'New','Evidence Required' => 'amber',
        'Evidence Ready','Submitted','Under Review' => 'purple',
        'Lost','Accepted' => 'red',
        default => 'slate',
    };
@endphp

<div class="cb-page">

<div class="cb-head">
    <div>
        <span class="cb-eyebrow">Dispute operations</span>
        <h1>Chargebacks</h1>
        <p>Track disputes, evidence deadlines, processor cases and outcomes without exposing sensitive card credentials.</p>
    </div>

    <a href="{{ route('crm.chargeback_create') }}" class="cb-create-btn">
        <i class="fa-solid fa-plus"></i>
        Open Chargeback Case
    </a>
</div>

<nav class="cb-module-tabs" aria-label="Refunds and Chargebacks">
    <a href="{{ route('crm.refunds') }}" class="cb-module-tab">
        <i class="fa-solid fa-rotate-left"></i>
        Refunds
    </a>
    <a href="{{ route('crm.chargebacks') }}" class="cb-module-tab active">
        <i class="fa-solid fa-shield-halved"></i>
        Chargebacks
    </a>
</nav>

<section class="cb-kpis">
    <div class="cb-kpi blue">
        <span><i class="fa-solid fa-layer-group"></i></span>
        <div><small>Total Cases</small><strong>{{ number_format($stats['total']) }}</strong></div>
    </div>

    <div class="cb-kpi amber">
        <span><i class="fa-regular fa-clock"></i></span>
        <div><small>Open</small><strong>{{ number_format($stats['open']) }}</strong></div>
    </div>

    <div class="cb-kpi red">
        <span><i class="fa-solid fa-file-circle-exclamation"></i></span>
        <div><small>Evidence Required</small><strong>{{ number_format($stats['evidence_required']) }}</strong></div>
    </div>

    <div class="cb-kpi purple">
        <span><i class="fa-solid fa-paper-plane"></i></span>
        <div><small>Submitted / Review</small><strong>{{ number_format($stats['submitted']) }}</strong></div>
    </div>

    <div class="cb-kpi green">
        <span><i class="fa-solid fa-trophy"></i></span>
        <div><small>Won</small><strong>{{ number_format($stats['won']) }}</strong></div>
    </div>

    <div class="cb-kpi slate">
        <span><i class="fa-solid fa-circle-xmark"></i></span>
        <div><small>Lost / Accepted</small><strong>{{ number_format($stats['lost']) }}</strong></div>
    </div>

    <div class="cb-kpi cyan">
        <span><i class="fa-solid fa-dollar-sign"></i></span>
        <div><small>Open Exposure</small><strong>${{ number_format($stats['exposure'],2) }}</strong></div>
    </div>
</section>

<section class="crm-card cb-toolbar">
    <form method="GET" class="cb-filters">

        <div class="cb-field">
            <label>Search cases</label>
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Case ref, booking ID, processor case, transaction or reason code">
        </div>

        <div class="cb-field">
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

        <div class="cb-actions">
            <button class="btn btn-primary" type="submit">
                <i class="fa-solid fa-magnifying-glass me-1"></i>Filter
            </button>

            @if(request()->hasAny(['q','status']))
                <a class="btn btn-light" href="{{ route('crm.chargebacks') }}">Clear</a>
            @endif
        </div>

    </form>
</section>

<section class="crm-card cb-table-card">
    <div class="cb-table-wrap">
        <table class="crm-table cb-table">
            <thead>
                <tr>
                    <th>S.No.</th>
                    <th>Case</th>
                    <th>Booking</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Disputed Amount</th>
                    <th>Processor</th>
                    <th>Processor Case</th>
                    <th>Reason Code</th>
                    <th>Card</th>
                    <th>Received</th>
                    <th>Response Deadline</th>
                    <th>Assigned</th>
                    <th>Outcome</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
            @forelse($cases as $case)
                @php $booking = $bookings->get($case->booking_id); @endphp

                <tr>
                    <td>{{ ($cases->firstItem() ?? 1) + $loop->index }}</td>

                    <td>
                        <strong>{{ $case->case_reference }}</strong>
                        <small>{{ $case->currency ?: 'USD' }}</small>
                    </td>

                    <td>
                        <a class="cb-link" href="{{ route('crm.booking_detail',$case->booking_id) }}">
                            #FH{{ $case->booking_id }}
                        </a>
                    </td>

                    <td>
                        <strong>{{ $booking->o_Name ?? 'Guest' }}</strong>
                        <small>{{ $booking->o_Email ?? '—' }}</small>
                    </td>

                    <td>
                        <span class="cb-badge {{ $tone($case->status) }}">
                            {{ $case->status }}
                        </span>
                    </td>

                    <td>
                        <strong>${{ number_format((float)$case->disputed_amount,2) }}</strong>
                    </td>

                    <td>{{ $case->processor ?: '—' }}</td>
                    <td>{{ $case->processor_case_reference ?: '—' }}</td>
                    <td>{{ $case->reason_code ?: '—' }}</td>

                    <td>
                        @if($case->card_last4)
                            {{ $case->card_brand ?: 'Card' }} · **** {{ $case->card_last4 }}
                        @else
                            —
                        @endif
                    </td>

                    <td>
                        {{ $case->received_date
                            ? \Carbon\Carbon::parse($case->received_date)->format('d M Y')
                            : '—' }}
                    </td>

                    <td>
                        {{ $case->response_deadline
                            ? \Carbon\Carbon::parse($case->response_deadline)->format('d M Y')
                            : '—' }}
                    </td>

                    <td>{{ $case->assigned_name ?: 'Unassigned' }}</td>
                    <td>{{ $case->outcome ?: '—' }}</td>

                    <td>
                        <a class="cb-open"
                           href="{{ route('crm.chargeback_detail',$case->id) }}"
                           title="Open case">
                            <i class="fa-solid fa-folder-open"></i>
                        </a>

                        <a class="cb-open"
                           href="{{ route('crm.booking_detail',$case->booking_id) }}"
                           title="Open booking">
                            <i class="fa-solid fa-plane"></i>
                        </a>

                        <a class="cb-open"
                           href="{{ route('crm.payment_detail',$case->booking_id) }}"
                           title="Open payment">
                            <i class="fa-regular fa-credit-card"></i>
                        </a>
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="15" class="empty">No chargeback cases found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="cb-pagination">
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
