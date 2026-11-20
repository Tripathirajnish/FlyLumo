@extends('crm.layout')

@section('content')

<div class="crm-headline comm-head">
    <div>
        <h1>Communications</h1>
        <p>Customer enquiries, follow-ups and booking conversations in one workspace.</p>
    </div>

    <a href="{{ route('crm.leads') }}" class="btn btn-primary">
        <i class="fa-solid fa-user-plus"></i> Lead Management
    </a>
</div>

<div class="comm-stats">
    <div>
        <span>Total enquiries</span>
        <strong>{{ number_format($stats['total']) }}</strong>
    </div>

    <div>
        <span>New</span>
        <strong>{{ number_format($stats['new']) }}</strong>
    </div>

    <div>
        <span>Open</span>
        <strong>{{ number_format($stats['open']) }}</strong>
    </div>

    <div>
        <span>Follow-ups</span>
        <strong>{{ number_format($stats['follow_up']) }}</strong>
    </div>
</div>

<div class="crm-card mb-3">

    <div class="crm-card-head comm-card-head">
        <div>
            <b>Customer enquiries</b>
            <small>Open an enquiry to manage status, owner, follow-up and activity.</small>
        </div>
    </div>

    <div class="crm-card-body comm-filter-wrap">
        <form method="get" class="comm-filter-form">

            <div class="comm-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input
                    type="text"
                    name="q"
                    value="{{ $q }}"
                    placeholder="Search name, email, phone, subject or message"
                >
            </div>

            <select name="stage">
                <option value="">All statuses</option>
                @foreach($leadStages as $s)
                    <option value="{{ $s }}" @selected($stage===$s)>{{ $s }}</option>
                @endforeach
            </select>

            <button class="btn btn-primary" type="submit">Apply</button>

            @if($q!=='' || $stage!=='')
                <a class="btn btn-light" href="{{ route('crm.communications') }}">Clear</a>
            @endif

        </form>
    </div>

    <div class="table-responsive">
        <table class="crm-table comm-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Subject / destination</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Owner / follow-up</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
            @forelse($enquiries as $e)

                @php($s = $e->crm_stage ?? 'New')

                <tr>
                    <td>
                        <b>{{ $e->name ?: 'New enquiry' }}</b>
                        <small class="d-block text-muted">{{ $e->emailid ?: 'No email' }}</small>
                        <small class="d-block text-muted">{{ $e->contact ?: 'No phone' }}</small>
                    </td>

                    <td>
                        <b>{{ $e->going_to ?: ($e->subject ?: 'General enquiry') }}</b>
                        <small class="d-block text-muted">Enquiry #FH-L{{ $e->id }}</small>
                    </td>

                    <td class="comm-message">
                        {{ \Illuminate\Support\Str::limit($e->message ?: 'No message supplied',100) }}
                    </td>

                    <td>
                        <span class="comm-status {{ strtolower(str_replace([' ','-'],'_',$s)) }}">
                            {{ $s }}
                        </span>

                        @if(($e->crm_priority ?? 'Normal')==='Urgent')
                            <span class="comm-urgent">Urgent</span>
                        @endif
                    </td>

                    <td>
                        <b>{{ $e->crm_agent_name ?? 'Unassigned' }}</b>

                        @if(!empty($e->crm_next_action))
                            <small class="d-block text-muted">
                                {{ \Illuminate\Support\Str::limit($e->crm_next_action,42) }}
                            </small>
                        @endif

                        @if(!empty($e->crm_next_follow_up_at))
                            <small class="d-block comm-due">
                                {{ \Carbon\Carbon::parse($e->crm_next_follow_up_at)->format('d M, h:i A') }}
                            </small>
                        @endif
                    </td>

                    <td>
                        {{ \Carbon\Carbon::parse($e->created_at)->format('d M Y') }}
                        <small class="d-block text-muted">
                            {{ \Carbon\Carbon::parse($e->created_at)->format('h:i A') }}
                        </small>
                    </td>

                    <td>
                        <div class="comm-actions">
                            <a
                                title="Open"
                                href="{{ route('crm.communication_detail',$e->id) }}"
                            >
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>

                            @if($e->emailid)
                                <a title="Email" href="mailto:{{ $e->emailid }}">
                                    <i class="fa-regular fa-envelope"></i>
                                </a>
                            @endif

                            @if($e->contact)
                                <a title="Call" href="tel:{{ preg_replace('/[^0-9+]/','',$e->contact) }}">
                                    <i class="fa-solid fa-phone"></i>
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="7" class="empty">No enquiries match these filters.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-3">{{ $enquiries->links('pagination::bootstrap-5') }}</div>

</div>

<div class="crm-card">

    <div class="crm-card-head comm-card-head">
        <div>
            <b>Booking conversations</b>
            <small>Recent internal remarks grouped by booking instead of repeated as separate cards.</small>
        </div>
    </div>

    <div class="crm-card-body">
        <div class="comm-thread-grid">

        @forelse($remarkGroups as $group)

            <div class="comm-thread">

                <div class="comm-thread-top">
                    <div>
                        <span>Booking conversation</span>
                        <a href="{{ route('crm.booking_detail',$group->booking_id) }}">
                            #FH{{ $group->booking_id }}
                        </a>
                    </div>

                    <span class="comm-count">
                        {{ $group->count }} {{ $group->count===1 ? 'remark' : 'remarks' }}
                    </span>
                </div>

                <div class="comm-thread-latest">
                    {{ \Illuminate\Support\Str::limit($group->latest_remark,150) }}
                </div>

                <div class="comm-thread-meta">
                    {{ $group->latest_admin ?: 'CRM user' }} ·
                    {{ \Carbon\Carbon::parse($group->latest_at)->format('d M Y, h:i A') }}
                </div>

                <div class="comm-thread-preview">
                    @foreach($group->items as $r)
                        <div>
                            <i class="fa-regular fa-message"></i>
                            <span>{{ \Illuminate\Support\Str::limit($r->remark,78) }}</span>
                        </div>
                    @endforeach
                </div>

                <a
                    class="comm-open-booking"
                    href="{{ route('crm.booking_detail',$group->booking_id) }}"
                >
                    Open booking conversation
                    <i class="fa-solid fa-arrow-right"></i>
                </a>

            </div>

        @empty
            <div class="text-muted">No internal remarks.</div>
        @endforelse

        </div>
    </div>

</div>

@endsection