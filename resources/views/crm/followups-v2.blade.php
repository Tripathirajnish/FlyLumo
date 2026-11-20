@extends('crm.layout')
@section('content')
@php
$types=['Call','Email','Payment','Ticketing','Schedule Change','Refund','Documentation','General'];
$priorities=['Low','Normal','High','Urgent'];
$statuses=['Pending','In Progress','Completed','Cancelled'];
@endphp

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <div class="small text-primary fw-bold text-uppercase" style="letter-spacing:.08em;">Agent task workspace</div>
            <h1 class="mb-1">Follow-ups</h1>
            <div class="text-muted">Actionable booking and passenger follow-ups.</div>
        </div>

        <a href="#new-followup-panel" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i> New Follow-up
        </a>
    </div>

    @if(session('crm_success'))
        <div class="alert alert-success">{{ session('crm_success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please fix the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div id="new-followup-panel" class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <div class="fw-bold">Create new follow-up</div>
                <div class="small text-muted">Link it to a booking when applicable.</div>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('crm.followup_store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Booking ID</label>
                        <input class="form-control" type="number" min="1" name="booking_id" value="{{ old('booking_id') }}" placeholder="Optional">
                        <div class="form-text">Use the numeric booking ID, e.g. 11046.</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Assigned agent</label>
                        <select class="form-select" name="assigned_to">
                            <option value="">Unassigned</option>
                            @foreach($admins as $a)
                                <option value="{{ $a->id }}" @selected((string)old('assigned_to')===(string)$a->id)>{{ $a->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Due date & time</label>
                        <input class="form-control" type="datetime-local" name="due_at" value="{{ old('due_at') }}" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Subject</label>
                        <input class="form-control" name="subject" value="{{ old('subject') }}" placeholder="Call passenger / issue ticket / payment follow-up" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Type</label>
                        <select class="form-select" name="followup_type">
                            @foreach($types as $t)
                                <option value="{{ $t }}" @selected(old('followup_type','General')===$t)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Priority</label>
                        <select class="form-select" name="priority">
                            @foreach($priorities as $p)
                                <option value="{{ $p }}" @selected(old('priority','Normal')===$p)>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            @foreach($statuses as $s)
                                <option value="{{ $s }}" @selected(old('status','Pending')===$s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" rows="3" name="notes" placeholder="Internal follow-up details">{{ old('notes') }}</textarea>
                    </div>

                    <div class="col-12 d-flex justify-content-end">
                        <button class="btn btn-primary px-4">
                            <i class="fa-solid fa-plus me-1"></i> Create Follow-up
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach([
            ['Due Today',$stats['today'],'primary'],
            ['Overdue',$stats['overdue'],'danger'],
            ['Upcoming',$stats['upcoming'],'info'],
            ['Completed',$stats['completed'],'success'],
            ['Unassigned',$stats['unassigned'],'warning']
        ] as [$l,$v,$c])
            <div class="col-6 col-lg">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="small text-muted text-uppercase fw-bold">{{ $l }}</div>
                        <div class="fs-2 fw-bold text-{{ $c }} mt-2">{{ $v }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form class="row g-2">
                <div class="col-lg-4">
                    <input class="form-control" name="q" value="{{ $q }}" placeholder="Booking, passenger, email, phone, note">
                </div>
                <div class="col-lg-2">
                    <select class="form-select" name="status">
                        <option value="">All statuses</option>
                        @foreach($statuses as $s)<option value="{{ $s }}" @selected($status===$s)>{{ $s }}</option>@endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <select class="form-select" name="priority">
                        <option value="">All priorities</option>
                        @foreach($priorities as $p)<option value="{{ $p }}" @selected($priority===$p)>{{ $p }}</option>@endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <select class="form-select" name="type">
                        <option value="">All types</option>
                        @foreach($types as $t)<option value="{{ $t }}" @selected($type===$t)>{{ $t }}</option>@endforeach
                    </select>
                </div>
                <div class="col-lg-2 d-flex gap-2">
                    <button class="btn btn-primary flex-fill">Apply</button>
                    <a class="btn btn-light" href="{{ route('crm.followups_v2') }}">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="fw-bold">Actionable follow-ups</div>
            <div class="small text-muted">Edit, copy, complete or delete tasks without relying on pop-up modals.</div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Booking / Customer</th>
                        <th>Task</th>
                        <th>Priority</th>
                        <th>Due</th>
                        <th>Agent</th>
                        <th>Status</th>
                        <th style="min-width:300px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($followups as $f)
                    @php
                        $over=$f->due_at && \Carbon\Carbon::parse($f->due_at)->isPast() && !in_array($f->status,['Completed','Cancelled']);
                    @endphp
                    <tr class="{{ $over ? 'table-danger' : '' }}">
                        <td>
                            <b>{{ $f->booking_id ? fh_booking_ref($f->booking_id) : 'No booking' }}</b>
                            <div>{{ $f->booking_name ?: 'â€”' }}</div>
                            <small class="text-muted">{{ $f->booking_email ?: $f->booking_phone }}</small>
                        </td>
                        <td>
                            <small class="text-primary fw-bold">{{ $f->followup_type }}</small>
                            <div class="fw-semibold">{{ $f->subject }}</div>
                            @if($f->notes)<small class="text-muted">{{ \Illuminate\Support\Str::limit($f->notes,80) }}</small>@endif
                        </td>
                        <td>{{ $f->priority }}</td>
                        <td>{{ $f->due_at ? \Carbon\Carbon::parse($f->due_at)->format('d M Y, h:i A') : 'â€”' }}</td>
                        <td>{{ $f->agent_name ?: 'Unassigned' }}</td>
                        <td>{{ $f->status }}</td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap mb-2">
                                @if($f->booking_id)
                                    <a class="btn btn-sm btn-light" href="{{ route('crm.booking_detail',$f->booking_id) }}">Open</a>
                                @endif

                                <details>
                                    <summary class="btn btn-sm btn-light" style="list-style:none;cursor:pointer;">Edit</summary>
                                    <div class="card shadow-sm mt-2" style="min-width:320px;position:absolute;z-index:20;">
                                        <div class="card-body">
                                            <form method="POST" action="{{ route('crm.followup_update',$f->id) }}">
                                                @csrf
                                                <div class="mb-2">
                                                    <label class="form-label small">Subject</label>
                                                    <input class="form-control form-control-sm" name="subject" value="{{ $f->subject }}" required>
                                                </div>
                                                <div class="row g-2">
                                                    <div class="col-6">
                                                        <label class="form-label small">Type</label>
                                                        <select class="form-select form-select-sm" name="followup_type">@foreach($types as $t)<option value="{{ $t }}" @selected($f->followup_type===$t)>{{ $t }}</option>@endforeach</select>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label small">Priority</label>
                                                        <select class="form-select form-select-sm" name="priority">@foreach($priorities as $p)<option value="{{ $p }}" @selected($f->priority===$p)>{{ $p }}</option>@endforeach</select>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label small">Status</label>
                                                        <select class="form-select form-select-sm" name="status">@foreach($statuses as $s)<option value="{{ $s }}" @selected($f->status===$s)>{{ $s }}</option>@endforeach</select>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label small">Agent</label>
                                                        <select class="form-select form-select-sm" name="assigned_to">
                                                            <option value="">Unassigned</option>
                                                            @foreach($admins as $a)<option value="{{ $a->id }}" @selected((int)$f->assigned_to===(int)$a->id)>{{ $a->name }}</option>@endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label small">Due</label>
                                                        <input class="form-control form-control-sm" type="datetime-local" name="due_at" value="{{ $f->due_at ? \Carbon\Carbon::parse($f->due_at)->format('Y-m-d\TH:i') : '' }}" required>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label small">Notes</label>
                                                        <textarea class="form-control form-control-sm" rows="3" name="notes">{{ $f->notes }}</textarea>
                                                    </div>
                                                </div>
                                                <button class="btn btn-primary btn-sm w-100 mt-2">Save changes</button>
                                            </form>
                                        </div>
                                    </div>
                                </details>

                                <form method="POST" action="{{ route('crm.followup_duplicate',$f->id) }}">@csrf<button class="btn btn-sm btn-light">Copy</button></form>

                                @if($f->status!=='Completed')
                                    <form method="POST" action="{{ route('crm.followup_complete',$f->id) }}">@csrf<button class="btn btn-sm btn-success">Complete</button></form>
                                @endif

                                <form method="POST" action="{{ route('crm.followup_delete',$f->id) }}" onsubmit="return confirm('Move this follow-up to trash?')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-5 text-muted">No follow-ups yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $followups->links() }}</div>
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><b>Fresh bookings</b></div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <tbody>
                        @foreach($freshBookings as $b)
                            <tr>
                                <td><b>#{{ fh_booking_ref($b->id) }}</b></td>
                                <td>{{ $b->o_Name }}<div class="small text-muted">{{ $b->o_Email }}</div></td>
                                <td>{{ optional($b->created_at)->format('d M Y, h:i A') }}</td>
                                <td><a class="btn btn-sm btn-light" href="{{ route('crm.booking_detail',$b->id) }}">Open</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <b>Recent activity</b>
                    <div class="small text-muted">Remarks are separate from actionable follow-ups.</div>
                </div>
                @forelse($recentActivity as $a)
                    <div class="border-bottom p-3">
                        <b>#{{ fh_booking_ref($a->booking_id) }} · {{ $a->o_Name ?: 'Passenger' }}</b>
                        <div class="small mt-1">{{ \Illuminate\Support\Str::limit($a->remark ?? '',120) }}</div>
                    </div>
                @empty
                    <div class="p-4 text-muted">No recent remarks.</div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection