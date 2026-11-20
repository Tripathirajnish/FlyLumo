@extends('crm.layout')

@section('content')

<div class="container-fluid py-4 tracking-v2-page">

<style>
.tracking-v2-page .tracking-page-head{
    display:flex;
    flex-wrap:wrap;
    justify-content:space-between;
    align-items:flex-start;
    gap:18px;
    margin-bottom:24px;
}
.tracking-v2-page .tracking-title-block{
    min-width:0;
}
.tracking-v2-page .tracking-eyebrow{
    display:flex;
    align-items:center;
    gap:12px;
    margin:0 0 8px;
    color:#1554c5;
    font-size:11px;
    line-height:1;
    font-weight:700;
    letter-spacing:.13em;
    text-transform:uppercase;
}
.tracking-v2-page .tracking-eyebrow:before{
    content:"";
    width:34px;
    height:3px;
    border-radius:999px;
    background:#e3194a;
    flex:0 0 34px;
}
.tracking-v2-page .tracking-page-head h1{
    margin:0;
    color:#071f4f;
    font-size:36px;
    line-height:1.08;
    font-weight:500;
    letter-spacing:-.035em;
}
.tracking-v2-page .tracking-page-head p{
    margin:7px 0 0;
    color:#718099;
    font-size:14px;
    line-height:1.55;
    font-weight:400;
}
.tracking-v2-page .tracking-page-head .btn{
    white-space:nowrap;
}
.tracking-v2-page .card{
    border:1px solid #dfe7f0!important;
    border-radius:14px!important;
    box-shadow:0 10px 28px rgba(11,37,75,.045)!important;
}
.tracking-v2-page .card-header{
    border-bottom:1px solid #e8eef5!important;
}
.tracking-v2-page .card-header .fw-bold,
.tracking-v2-page .card-body .fw-bold{
    font-weight:600!important;
}
.tracking-v2-page .tracking-kpi-label{
    font-size:10px;
    color:#718099;
    text-transform:uppercase;
    letter-spacing:.06em;
    font-weight:600;
}
.tracking-v2-page .tracking-kpi-value{
    margin-top:7px;
    font-size:28px;
    line-height:1;
    font-weight:500!important;
}
.tracking-v2-page .table thead th{
    font-size:10px!important;
    font-weight:600!important;
    letter-spacing:.04em;
    text-transform:uppercase;
}
.tracking-v2-page .table tbody td{
    font-size:12px!important;
    vertical-align:middle;
}
.tracking-v2-page .badge{
    font-weight:500!important;
}
@media(max-width:760px){
    .tracking-v2-page .tracking-page-head{
        flex-direction:column;
        align-items:flex-start;
    }
    .tracking-v2-page .tracking-page-head h1{
        font-size:30px;
    }
    .tracking-v2-page .tracking-page-head .btn{
        width:100%;
    }
}
</style>

<div class="tracking-page-head">
    <div class="tracking-title-block">
        <div class="tracking-eyebrow">Booking Operations</div>
        <h1>Booking Tracking</h1>
        <p>Stage history, CRM changes and follow-ups in one operational timeline.</p>
    </div>

    <a class="btn btn-primary" href="#manual-tracking-note">
        <i class="fa-solid fa-plus me-1"></i>Add Tracking Note
    </a>
</div>

@if(session('crm_success'))
    <div class="alert alert-success">{{ session('crm_success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="row g-3 mb-4">
@foreach([
    ['Total Activities',$stats['total'],'primary'],
    ['Booking Changes',$stats['booking_changes'],'info'],
    ['Fare Changes',$stats['fare_changes'],'warning'],
    ['Payments',$stats['payments'],'success'],
    ['Follow-ups',$stats['followups'],'secondary']
] as [$label,$value,$tone])
    <div class="col-6 col-lg">
        <div class="card h-100">
            <div class="card-body">
                <div class="tracking-kpi-label">{{ $label }}</div>
                <div class="tracking-kpi-value text-{{ $tone }}">{{ $value }}</div>
            </div>
        </div>
    </div>
@endforeach
</div>

<div class="card mb-4">
    <div class="card-body">
        <form class="row g-2 align-items-end">

            <div class="col-xl-4">
                <label class="form-label small text-muted">Search</label>
                <input class="form-control"
                       name="q"
                       value="{{ $q }}"
                       placeholder="Booking, passenger, email, phone, activity, agent">
            </div>

            <div class="col-xl-2">
                <label class="form-label small text-muted">Activity type</label>
                <select class="form-select" name="type">
                    <option value="">All types</option>
                    @foreach($types as $t)
                        <option value="{{ $t }}" @selected($type === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-xl-2">
                <label class="form-label small text-muted">Agent</label>
                <select class="form-select" name="agent">
                    <option value="">All agents</option>
                    @foreach($admins as $a)
                        <option value="{{ $a->id }}" @selected((string)$agent === (string)$a->id)>
                            {{ $a->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-xl-2">
                <label class="form-label small text-muted">From</label>
                <input class="form-control" type="date" name="date_from" value="{{ $dateFrom }}">
            </div>

            <div class="col-xl-2">
                <label class="form-label small text-muted">To</label>
                <input class="form-control" type="date" name="date_to" value="{{ $dateTo }}">
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                <a class="btn btn-light" href="{{ route('crm.tracking_v2') }}">Reset</a>
                <button class="btn btn-primary px-4">Apply Filters</button>
            </div>

        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-white py-3">
        <div class="fw-bold">Complete activity timeline</div>
        <div class="small text-muted">
            Audit history is read-only. Corrections should be added as a new tracking note instead of editing old history.
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Booking / Customer</th>
                    <th>Type</th>
                    <th>Activity</th>
                    <th>Old Value</th>
                    <th>New Value / Detail</th>
                    <th>Agent</th>
                    <th>Date & Time</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($eventsPaginator as $e)
                <tr>
                    <td>
                        @if($e['booking_id'])
                            <a class="text-decoration-none fw-semibold"
                               href="{{ route('crm.booking_detail',$e['booking_id']) }}">
                                #{{ $e['booking_ref'] }}
                            </a>
                        @else
                            <div class="fw-semibold">{{ $e['booking_ref'] }}</div>
                        @endif

                        <div>{{ $e['customer'] }}</div>

                        @if($e['email'])
                            <small class="text-muted">{{ $e['email'] }}</small>
                        @endif
                    </td>

                    <td>
                        <span class="badge bg-light text-dark border">{{ $e['type'] }}</span>
                    </td>

                    <td style="min-width:220px;">
                        <div class="fw-semibold">{{ $e['activity'] }}</div>
                        <small class="text-muted">{{ $e['source'] }}</small>
                    </td>

                    <td style="max-width:260px;">
                        <div class="small text-break">{{ $e['old_value'] ?: '—' }}</div>
                    </td>

                    <td style="max-width:320px;">
                        <div class="small text-break">{{ $e['new_value'] ?: '—' }}</div>
                    </td>

                    <td>{{ $e['agent'] }}</td>

                    <td class="text-nowrap">
                        {{ \Carbon\Carbon::parse($e['created_at'])->format('d M Y, h:i A') }}
                    </td>

                    <td>
                        @if($e['booking_id'])
                            <a class="btn btn-sm btn-light"
                               href="{{ route('crm.booking_detail',$e['booking_id']) }}">
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        No tracking activity matches the current filters.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white">
        {{ $eventsPaginator->links() }}
    </div>
</div>

<div id="manual-tracking-note" class="card">
    <div class="card-header bg-white py-3">
        <div class="fw-bold">Add tracking note</div>
        <div class="small text-muted">
            Append-only correction or context entry. Existing audit rows cannot be edited or deleted here.
        </div>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('crm.tracking_v2_manual') }}">
            @csrf

            <div class="row g-3">

                <div class="col-md-3">
                    <label class="form-label">Booking ID</label>
                    <input type="number"
                           min="1"
                           class="form-control"
                           name="booking_id"
                           placeholder="Optional numeric ID">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select class="form-select" name="event_type" required>
                        <option>Manual</option>
                        <option>Booking Change</option>
                        <option>Passenger</option>
                        <option>Itinerary</option>
                        <option>Fare</option>
                        <option>Payment</option>
                        <option>Ticketing</option>
                        <option>Contact/Billing</option>
                        <option>Follow-up</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Title</label>
                    <input class="form-control"
                           name="title"
                           placeholder="Correction / manual operational note"
                           required>
                </div>

                <div class="col-12">
                    <label class="form-label">Note</label>
                    <textarea class="form-control"
                              rows="3"
                              name="note"
                              placeholder="Explain the event or correction"></textarea>
                </div>

                <div class="col-12 d-flex justify-content-end">
                    <button class="btn btn-primary">Add to Timeline</button>
                </div>

            </div>
        </form>
    </div>
</div>

</div>

@endsection
