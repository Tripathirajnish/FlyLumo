@extends('crm.layout')
@section('content')

<style id="fareshunt-booking-management-premium-v2">
:root{
    --fh-navy:#071f4f;
    --fh-navy-2:#0c316f;
    --fh-blue:#1f5bd8;
    --fh-blue-2:#3a78f1;
    --fh-red:#e3194a;
    --fh-red-dark:#c91541;
    --fh-bg:#f4f8fd;
    --fh-line:#dbe6f2;
    --fh-muted:#718198;
    --fh-card:#fff;
}

.bm-head{
    position:relative;
    display:flex;
    align-items:flex-end;
    justify-content:space-between;
    gap:20px;
    margin-bottom:18px;
    padding:0 0 2px 0;
}
.bm-head:before{
    content:"";
    position:absolute;
    left:0;
    top:7px;
    width:30px;
    height:3px;
    border-radius:99px;
    background:var(--fh-red);
}
.bm-head > div{
    padding-left:0;
}
.bm-eyebrow{
    display:inline-block;
    margin-bottom:7px;
    padding-left:42px;
    color:#1d5bd2;
    text-transform:uppercase;
    letter-spacing:.12em;
    font-size:9px;
    font-weight:800;
}
.bm-head .title{
    margin:0;
    color:var(--fh-navy);
    font-size:32px;
    line-height:1.05;
    letter-spacing:-.035em;
    font-weight:800;
}
.bm-head .sub{
    margin-top:7px;
    color:var(--fh-muted);
    font-size:13px;
}
.bm-head > .btn{
    min-height:44px;
    padding:0 16px!important;
    display:inline-flex;
    align-items:center;
    gap:7px;
    border-radius:12px!important;
    background:linear-gradient(135deg,#164fbe,#2869e5)!important;
    border:1px solid #1f5ed3!important;
    box-shadow:0 10px 22px rgba(31,91,216,.16);
}

.bm-toolbar{
    position:relative;
    margin-bottom:14px;
    padding:18px!important;
    border:1px solid var(--fh-line)!important;
    border-radius:18px!important;
    background:
        radial-gradient(circle at 100% 0,rgba(35,91,216,.045),transparent 25%),
        #fff!important;
    box-shadow:0 12px 28px rgba(12,44,88,.05)!important;
}
.bm-filter-form{
    display:grid;
    grid-template-columns:minmax(280px,1.7fr) repeat(2,minmax(190px,.85fr)) minmax(175px,.8fr);
    gap:12px;
    align-items:center;
}
.bm-search{
    position:relative;
}
.bm-search i{
    position:absolute;
    left:15px;
    top:50%;
    transform:translateY(-50%);
    color:#6f85a2;
    font-size:13px;
    z-index:2;
}
.bm-search input{
    width:100%;
    height:46px;
    padding:0 15px 0 42px;
    border:1px solid #d6e2ef;
    border-radius:12px;
    outline:0;
    background:#fff;
    color:#163555;
    font-size:12px;
    transition:.16s;
}
.bm-search input:focus{
    border-color:#6f9fe6;
    box-shadow:0 0 0 4px rgba(31,91,216,.08);
}
.bm-filter-form .form-select,
.bm-filter-form .form-control{
    min-height:46px!important;
    border:1px solid #d6e2ef!important;
    border-radius:12px!important;
    background-color:#fff!important;
    color:#173555!important;
    font-size:11px!important;
}
.bm-filter-form .btn{
    min-height:46px;
}
.bm-per-page{
    max-width:140px;
}
.bm-toolbar-meta{
    position:absolute;
    top:18px;
    right:18px;
}
.bm-toolbar-meta .soft{
    padding:7px 10px;
    border-radius:999px;
    background:#edf4ff!important;
    color:#1f5bd8!important;
    font-size:9px;
    font-weight:800;
}

.bm-table-card{
    overflow:hidden!important;
    border:1px solid var(--fh-line)!important;
    border-radius:18px!important;
    background:#fff!important;
    box-shadow:0 14px 30px rgba(14,47,92,.055)!important;
}
.bm-scroll{
    overflow:auto;
}
.bm-table{
    min-width:1650px;
}
.bm-table thead th{
    position:sticky;
    top:0;
    z-index:3;
    padding:13px 12px!important;
    background:linear-gradient(180deg,#f8fbff,#f2f6fb)!important;
    color:#425a78!important;
    border-bottom:1px solid #dce6f0!important;
    text-transform:uppercase;
    letter-spacing:.045em;
    font-size:8px!important;
    font-weight:800!important;
    white-space:nowrap;
}
.bm-table tbody td{
    padding:13px 12px!important;
    border-bottom:1px solid #edf1f6!important;
    color:#334f70;
    font-size:9px!important;
    vertical-align:middle;
    white-space:nowrap;
}
.bm-table tbody tr{
    transition:.15s;
}
.bm-table tbody tr:hover td{
    background:#fbfdff!important;
}
.bm-table td:nth-child(4),
.bm-table td:nth-child(5){
    white-space:normal;
}
.bm-table td b,
.bm-table td strong{
    color:#12345c;
}
.bm-table td small{
    display:block;
    margin-top:3px;
    color:#8a98aa;
    font-size:7px;
}
.booking-link{
    display:inline-flex;
    align-items:center;
    gap:5px;
    color:#0d438f!important;
    font-weight:850!important;
    text-decoration:none;
}
.booking-link:before{
    content:"FH";
    display:inline-grid;
    place-items:center;
    width:22px;
    height:22px;
    border-radius:7px;
    background:#edf4ff;
    color:#1f5bd8;
    font-size:7px;
    font-weight:900;
}
.bm-pnr{
    color:#0f315c!important;
}
.bm-pax{
    min-width:28px;
    height:28px;
    display:inline-grid;
    place-items:center;
    border-radius:9px;
    background:#eef3f9;
    color:#183c69;
    font-weight:800;
}
.bm-state{
    display:inline-flex;
    align-items:center;
    padding:6px 9px;
    border-radius:999px;
    font-size:8px;
    font-weight:800;
}
.bm-success{background:#eaf8f0;color:#16804b}
.bm-warning{background:#fff5df;color:#b87107}
.bm-danger{background:#fff0f2;color:#c92b49}
.bm-purple{background:#f2ebff;color:#6d3cc3}
.bm-info{background:#edf4ff;color:#205bcc}
.bm-neutral{background:#eef2f6;color:#5a6d84}

.bm-sticky-action{
    position:sticky!important;
    right:0;
    z-index:4;
    background:#fff!important;
    box-shadow:-8px 0 18px rgba(11,40,81,.04);
}
thead .bm-sticky-action{
    background:#f3f7fb!important;
    z-index:6;
}
.bm-actions{
    display:flex;
    align-items:center;
    gap:6px;
}
.bm-actions > a,
.bm-actions form button{
    width:34px;
    height:34px;
    display:grid;
    place-items:center;
    border:1px solid #d7e2ee!important;
    border-radius:10px!important;
    background:#fff!important;
    color:#5d728e!important;
    text-decoration:none;
    transition:.15s;
    padding:0!important;
}
.bm-actions > a:hover{
    color:#1e5fd5!important;
    border-color:#bdd1ee!important;
    background:#f5f9ff!important;
    transform:translateY(-1px);
}
.bm-actions form button:hover{
    color:#c51c43!important;
    border-color:#f0c0cb!important;
    background:#fff5f7!important;
}
.bm-pagination{
    min-height:62px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:16px;
    padding:13px 16px;
    border-top:1px solid #e4ebf3;
    color:#74849a;
    font-size:9px;
}
.bm-pagination b{
    color:#17395f;
}
.bm-pagination nav{
    display:flex;
    align-items:center;
    gap:8px;
}
.bm-pagination nav a,
.bm-pagination nav span{
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
.bm-pagination nav a:hover{
    border-color:#bfd1e8;
    color:#1c5bd0;
}
.bm-page-number{
    background:#f5f8fc!important;
    color:#244466!important;
}
.bm-pagination .disabled{
    opacity:.5;
}

@media(max-width:1250px){
    .bm-filter-form{
        grid-template-columns:repeat(3,minmax(0,1fr));
    }
    .bm-search{
        grid-column:span 2;
    }
}
@media(max-width:900px){
    .bm-head{
        flex-direction:column;
        align-items:flex-start;
    }
    .bm-filter-form{
        grid-template-columns:1fr 1fr;
    }
    .bm-search{
        grid-column:1/-1;
    }
}
@media(max-width:620px){
    .bm-filter-form{
        grid-template-columns:1fr;
    }
    .bm-search{
        grid-column:auto;
    }
    .bm-head .title{
        font-size:27px;
    }
    .bm-pagination{
        align-items:flex-start;
        flex-direction:column;
    }
}

.bm-remark{display:flex;align-items:flex-start;gap:7px;max-width:230px;min-width:150px;color:#344761;line-height:1.35}
.bm-remark i{color:#d18a00;margin-top:2px;flex:0 0 auto}
.bm-remark span{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;word-break:break-word}
.bm-no-remark{color:#a0acba}
</style>

@php
    $toneFor = fn($status) => $statusMeta[$status]['tone'] ?? 'slate';
    $paymentTone = function($status){
        return match($status){
            'Paid' => 'success',
            'Partially Paid','Pending','Refund Pending' => 'warning',
            'Declined','Chargeback' => 'danger',
            'Refunded' => 'purple',
            default => 'neutral',
        };
    };
    $ticketTone = function($status){
        return match($status){
            'Ticketed','E-Ticket','Issued' => 'success',
            'Ticketing' => 'info',
            'Voided' => 'danger',
            'Exchanged' => 'purple',
            default => 'neutral',
        };
    };
@endphp

<div class="crm-page-head bm-head">
    <div>
        <span class="bm-eyebrow">Booking operations</span>
        <h1 class="title">Booking Management</h1>
        <div class="sub">Search, filter and manage the complete booking lifecycle.</div>
    </div>
    <div class="bm-head-actions" style="display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end;">
        @if(\Illuminate\Support\Facades\Route::has('crm.manual_booking_create'))
            <a class="btn btn-danger" href="{{ route('crm.manual_booking_create') }}"><i class="fa-solid fa-plus me-1"></i>New Manual Booking</a>
        @endif
        <a class="btn btn-primary" href="{{ route('crm.export','bookings') }}">
            <i class="fa-solid fa-download me-2"></i>Export CSV
        </a>
    </div>
</div>

<div class="bm-toolbar crm-card">
    <form method="GET" class="bm-filter-form">
        <div class="bm-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input name="q" value="{{ request('q') }}" placeholder="Booking ID, PNR, ticket, name, email or phone">
        </div>

        <select name="status" class="form-select">
            <option value="">All booking statuses</option>
            @foreach($statusGroups as $group => $statuses)
                <optgroup label="{{ $group }}">
                    @foreach($statuses as $s)
                        <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>

        <select name="payment_status" class="form-select">
            <option value="">All payment statuses</option>
            @foreach($paymentStatuses as $s)
                <option value="{{ $s }}" @selected(request('payment_status')===$s)>{{ $s }}</option>
            @endforeach
        </select>

        <select name="ticket_status" class="form-select">
            <option value="">All ticket statuses</option>
            @foreach($ticketStatuses as $s)
                <option value="{{ $s }}" @selected(request('ticket_status')===$s)>{{ $s }}</option>
            @endforeach
        </select>

        <select name="source" class="form-select">
            <option value="">All sources</option>
            <option value="Website" @selected(request('source')==='Website')>Website</option>
            <option value="FareLeader" @selected(request('source')==='FareLeader')>FareLeader</option>
            <option value="FareLeaders Core" @selected(request('source')==='FareLeaders Core')>FareLeaders Core</option>
            <option value="FareLeaders Compare" @selected(request('source')==='FareLeaders Compare')>FareLeaders Compare</option>
            <option value="Manual" @selected(request('source')==='Manual')>Manual / Offline</option>
        </select>

        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}" title="Booking date from">
        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}" title="Booking date to">

        <select name="per_page" class="form-select bm-per-page">
            @foreach([15,25,50,100] as $size)
                <option value="{{ $size }}" @selected((int)request('per_page',15)===$size)>{{ $size }} / page</option>
            @endforeach
        </select>

        <button class="btn btn-primary"><i class="fa-solid fa-filter me-1"></i>Apply</button>

        @if(request()->hasAny(['q','status','source','payment_status','ticket_status','date_from','date_to','per_page']))
            <a class="btn btn-light" href="{{ route('crm.bookings') }}">Clear</a>
        @endif
    </form>

    <div class="bm-toolbar-meta">
        <span class="soft blue">{{ number_format($bookings->total()) }} records</span>
    </div>
</div>

<div class="crm-card bm-table-card">
    <div class="table-responsive bm-scroll">
        <table class="crm-table bm-table">
            <thead>
                <tr>
                    <th>S.No.</th>
                    <th>Booking Ref</th>
                    <th>PNR</th>
                    <th>Customer</th>
                    <th>Contact</th>
                    <th>Trip</th>
                    <th>Departure</th>
                    <th>Return</th>
                    <th>Pax</th>
                    <th>Airline</th>
                    <th>Total</th>
                    <th>Markup</th>
                    <th>Payment</th>
                    <th>Ticket</th>
                    <th>Via / Source</th>
                    <th>Booking Date</th>
                    <th>Status</th>
                    <th>Remarks</th>
                    <th class="bm-sticky-action">Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($bookings as $b)
                <tr>
                    <td>{{ $bookings->firstItem() + $loop->index }}</td>

                    {{-- DISPLAY-ONLY FaresHunt reference.
                         Database ID / primary key remains unchanged. --}}
                    <td>
                        <a class="booking-link" href="{{ route('crm.booking_detail',$b->id) }}">
                            #FH{{ $b->id }}
                        </a>
                    </td>

                    <td><strong class="bm-pnr">{{ $b->crm_pnr }}</strong></td>
                    <td>
                        <b>{{ $b->o_Name ?: 'Guest' }}</b>
                        <small>{{ $b->o_Email ?: '—' }}</small>
                    </td>
                    <td>{{ $b->o_Phone ?: '—' }}</td>
                    <td><strong>{{ $b->crm_route }}</strong></td>
                    <td>{{ $b->crm_departure ? \Carbon\Carbon::parse($b->crm_departure)->format('d M Y') : '—' }}</td>
                    <td>{{ $b->crm_return ? \Carbon\Carbon::parse($b->crm_return)->format('d M Y') : '—' }}</td>
                    <td><span class="bm-pax">{{ $b->crm_pax_count }}</span></td>
                    <td>{{ $b->crm_airline }}</td>
                    <td><strong>{{ $b->o_FullPrice ?: '—' }}</strong></td>
                    <td>{{ $b->crm_markup_amount !== null ? '$'.number_format((float)$b->crm_markup_amount,2) : ($b->is_markup ?: '—') }}</td>
                    <td><span class="bm-state bm-{{ $paymentTone($b->crm_payment_status) }}">{{ $b->crm_payment_status }}</span></td>
                    <td><span class="bm-state bm-{{ $ticketTone($b->crm_ticket_status) }}">{{ $b->crm_ticket_status }}</span></td>
                    <td><span class="soft {{ $b->source==='Website'?'blue':'amber' }}">{{ $b->source ?: 'Unknown' }}</span></td>
                    <td>
                        {{ optional($b->created_at)->format('d M Y') }}
                        <small>{{ optional($b->created_at)->format('h:i A') }}</small>
                    </td>
                    <td>
                        <span class="crm-status-badge tone-{{ $toneFor($b->status) }}">
                            <span class="crm-status-dot"></span>{{ $b->status ?: 'Unspecified' }}
                        </span>
                    </td>
                    <td>
                        @php $rowRemark = trim((string)($b->remark ?? '')); @endphp
                        @if($rowRemark !== '')
                            <div class="bm-remark" title="{{ $rowRemark }}">
                                <i class="fa-regular fa-note-sticky"></i>
                                <span>{{ \Illuminate\Support\Str::limit($rowRemark, 110) }}</span>
                            </div>
                        @else
                            <span class="bm-no-remark">—</span>
                        @endif
                    </td>
                    <td class="bm-sticky-action">
                        <div class="bm-actions">
                            <a href="{{ route('crm.booking_detail',$b->id) }}" title="View booking"><i class="fa-regular fa-eye"></i></a>
                            <a href="{{ route('crm.booking_detail',$b->id) }}#booking-operations" title="Edit operations"><i class="fa-regular fa-pen-to-square"></i></a>
                            @if(\Illuminate\Support\Facades\Route::has('crm.invoice_create'))
                                <a href="{{ route('crm.invoice_create',$b->id) }}" title="Generate invoice"><i class="fa-solid fa-file-invoice-dollar"></i></a>
                            @endif
                            <a href="{{ route('crm.booking_email_preview',$b->id) }}" target="_blank" title="Preview confirmation"><i class="fa-solid fa-file-arrow-down"></i></a>
                            <form method="POST" action="{{ route('crm.universal_archive') }}" style="display:inline" onsubmit="return confirm('Move this booking to CRM Trash?')">@csrf
                                <input type="hidden" name="module" value="booking">
                                <input type="hidden" name="record_key" value="{{ $b->id }}">
                                <input type="hidden" name="label" value="#FH{{ $b->id }} · {{ $b->o_Name }}">
                                <button type="submit" title="Delete / Archive"><i class="fa-regular fa-trash-can"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="19" class="empty">No matching bookings.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="bm-pagination">
        <div>
            Showing <b>{{ $bookings->firstItem() ?? 0 }}</b> to <b>{{ $bookings->lastItem() ?? 0 }}</b>
            of <b>{{ number_format($bookings->total()) }}</b> results
        </div>

        <nav aria-label="Booking pagination">
            @if($bookings->onFirstPage())
                <span class="disabled"><i class="fa-solid fa-chevron-left"></i> Previous</span>
            @else
                <a href="{{ $bookings->previousPageUrl() }}"><i class="fa-solid fa-chevron-left"></i> Previous</a>
            @endif

            <span class="bm-page-number">Page {{ $bookings->currentPage() }} of {{ $bookings->lastPage() }}</span>

            @if($bookings->hasMorePages())
                <a href="{{ $bookings->nextPageUrl() }}">Next <i class="fa-solid fa-chevron-right"></i></a>
            @else
                <span class="disabled">Next <i class="fa-solid fa-chevron-right"></i></span>
            @endif
        </nav>
    </div>
</div>
@endsection
