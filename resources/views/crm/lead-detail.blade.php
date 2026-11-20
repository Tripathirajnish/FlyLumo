@extends('crm.layout')
@section('content')
@php
    $stage = $leadMeta->stage ?: 'New';
    $priority = $leadMeta->priority ?: 'Normal';
    $stageIndex = array_search($stage,$leadStages,true);
    $stageIndex = $stageIndex === false ? 0 : $stageIndex;
    $displayStages = ['New','Follow-up','Qualified','Quoted','Negotiation','Converted'];
    $created = \Carbon\Carbon::parse($lead->created_at);
    $nextFollow = $leadMeta->next_follow_up_at ? \Carbon\Carbon::parse($leadMeta->next_follow_up_at) : null;
    $destination = $leadMeta->travel_to ?: ($lead->going_to ?: 'Not specified');
    $travelDate = $lead->checkin_date ? \Carbon\Carbon::parse($lead->checkin_date) : null;
@endphp

<div class="ld-page">
    <section class="ld-hero">
        <div>
            <a href="{{ route('crm.leads') }}" class="crm-back"><i class="fa-solid fa-arrow-left me-1"></i>Back to leads</a>
            <div class="ld-title-row">
                <h1>Lead #{{ $lead->id }}</h1>
                <span class="ld-stage-badge">{{ $stage }}</span>
                <span class="ld-priority-badge priority-{{ strtolower($priority) }}">{{ $priority }}</span>
            </div>
            <p>{{ $lead->name ?: 'New enquiry' }} · {{ $destination }} · Received {{ $created->diffForHumans() }}</p>
        </div>
        <div class="ld-hero-actions">
            @if($lead->contact)<a class="btn btn-light" href="tel:{{ $lead->contact }}"><i class="fa-solid fa-phone me-1"></i>Call</a>@endif
            @if($lead->emailid)<a class="btn btn-light" href="#lead-email-composer"><i class="fa-regular fa-envelope me-1"></i>Email</a>@endif
            <a class="btn btn-primary" href="#lead-operations"><i class="fa-regular fa-pen-to-square me-1"></i>Update Lead</a>
            <form method="POST" action="{{ route('crm.universal_archive') }}" onsubmit="return confirm('Move this lead to CRM Trash?')">@csrf
                <input type="hidden" name="module" value="lead"><input type="hidden" name="record_key" value="{{ $lead->id }}"><input type="hidden" name="label" value="Lead #{{ $lead->id }} · {{ $lead->name }}">
                <button class="btn btn-light crm-ua-archive"><i class="fa-regular fa-trash-can me-1"></i>Delete / Archive</button>
            </form>
        </div>
    </section>

    <section class="ld-pipeline">
        @foreach($displayStages as $i=>$s)
            @php $idx=array_search($s,$leadStages,true); @endphp
            <div class="{{ $idx < $stageIndex ? 'done' : ($s===$stage ? 'active' : '') }}">
                <span>{{ $idx < $stageIndex ? '✓' : $loop->iteration }}</span>
                <b>{{ $s }}</b>
            </div>
        @endforeach
    </section>

    <section class="ld-action-center">
        <div><span><i class="fa-solid fa-bolt"></i></span><div><small>Agent Action Center</small><strong>Move the lead forward</strong></div></div>
        <nav>
            @if($lead->contact)<a href="tel:{{ $lead->contact }}"><i class="fa-solid fa-phone"></i>Call</a>@endif
            @if($lead->emailid)<a href="#lead-email-composer"><i class="fa-regular fa-envelope"></i>Email</a>@endif
            <a href="#activity"><i class="fa-regular fa-note-sticky"></i>Add Note</a>
            <a href="#lead-operations"><i class="fa-regular fa-bell"></i>Set Follow-up</a>
            <a href="#activity"><i class="fa-solid fa-file-lines"></i>Quotation</a>
            <a href="#lead-operations"><i class="fa-solid fa-user-check"></i>Assign Agent</a>
        </nav>
    </section>

    <section class="ld-summary">
        <div class="blue"><span><i class="fa-solid fa-location-dot"></i></span><small>Destination</small><strong>{{ $destination }}</strong></div>
        <div class="purple"><span><i class="fa-regular fa-calendar"></i></span><small>Travel Date</small><strong>{{ $travelDate ? $travelDate->format('d M Y') : 'Not specified' }}</strong></div>
        <div class="green"><span><i class="fa-solid fa-user-tie"></i></span><small>Assigned Agent</small><strong>{{ $leadMeta->agent_name ?: 'Unassigned' }}</strong></div>
        <div class="amber"><span><i class="fa-regular fa-bell"></i></span><small>Next Follow-up</small><strong>{{ $nextFollow ? $nextFollow->format('d M, h:i A') : 'Not scheduled' }}</strong></div>
        <div class="rose"><span><i class="fa-solid fa-fire"></i></span><small>Priority</small><strong>{{ $priority }}</strong></div>
        <div class="cyan"><span><i class="fa-solid fa-tag"></i></span><small>Lead Type</small><strong>{{ $lead->type ?: 'Default' }}</strong></div>
    </section>

    <div class="ld-layout">
        <main>
            <section class="ld-card">
                <header><div><span>Lead details</span><h3>Customer & enquiry overview</h3></div></header>
                <div class="ld-body">
                    <div class="ld-info-grid">
                        <div><small>Lead ID</small><strong>#{{ $lead->id }}</strong></div>
                        <div data-crm-ua data-ua-module="lead" data-ua-key="{{ $lead->id }}" data-ua-field="name" data-ua-label="Customer Name" data-ua-value="{{ $lead->name }}"><small>Customer</small><strong>{{ $lead->name ?: 'New enquiry' }}</strong></div>
                        <div data-crm-ua data-ua-module="lead" data-ua-key="{{ $lead->id }}" data-ua-field="emailid" data-ua-label="Email" data-ua-value="{{ $lead->emailid }}"><small>Email</small><strong>{{ $lead->emailid ?: '—' }}</strong></div>
                        <div data-crm-ua data-ua-module="lead" data-ua-key="{{ $lead->id }}" data-ua-field="contact" data-ua-label="Contact" data-ua-value="{{ $lead->contact }}"><small>Contact</small><strong>{{ $lead->contact ?: '—' }}</strong></div>
                        <div data-crm-ua data-ua-module="lead" data-ua-key="{{ $lead->id }}" data-ua-field="going_to" data-ua-label="Destination" data-ua-value="{{ $lead->going_to }}"><small>Destination</small><strong>{{ $destination }}</strong></div>
                        <div data-crm-ua data-ua-module="lead" data-ua-key="{{ $lead->id }}" data-ua-field="checkin_date" data-ua-label="Check-in / Travel Date" data-ua-value="{{ $lead->checkin_date }}"><small>Check-in / Travel Date</small><strong>{{ $lead->checkin_date ?: '—' }}</strong></div>
                        <div data-crm-ua data-ua-module="lead" data-ua-key="{{ $lead->id }}" data-ua-field="occupant" data-ua-label="Occupants" data-ua-value="{{ $lead->occupant }}"><small>Occupants</small><strong>{{ $lead->occupant ?: '—' }}</strong></div>
                        <div data-crm-ua data-ua-module="lead" data-ua-key="{{ $lead->id }}" data-ua-field="source" data-ua-label="Lead Source" data-ua-value="{{ $leadMeta->source }}"><small>Source</small><strong>{{ $leadMeta->source ?: 'Website enquiry' }}</strong></div>
                        <div><small>Created</small><strong>{{ $created->format('d M Y, h:i A') }}</strong></div>
                    </div>
                </div>
            </section>

            <section class="ld-card">
                <header><div><span>Travel requirement</span><h3>Trip requirements</h3></div></header>
                <div class="ld-body">
                    <div class="ld-travel-grid">
                        <div><small>From</small><strong>{{ $leadMeta->travel_from ?: 'Not specified' }}</strong></div>
                        <div><small>To</small><strong>{{ $destination }}</strong></div>
                        <div><small>Departure</small><strong>{{ $lead->checkin_date ?: 'Not specified' }}</strong></div>
                        <div><small>Return</small><strong>{{ $leadMeta->return_date ?: 'Not specified' }}</strong></div>
                        <div><small>Adults</small><strong>{{ $leadMeta->adults ?? '—' }}</strong></div>
                        <div><small>Children</small><strong>{{ $leadMeta->children ?? '—' }}</strong></div>
                        <div><small>Infants</small><strong>{{ $leadMeta->infants ?? '—' }}</strong></div>
                        <div><small>Cabin</small><strong>{{ $leadMeta->cabin ?: 'Not specified' }}</strong></div>
                        <div><small>Budget</small><strong>{{ $leadMeta->budget !== null ? '$'.number_format((float)$leadMeta->budget,2) : 'Not specified' }}</strong></div>
                    </div>

                    <div class="ld-message-block">
                        <div><small>Subject</small><strong>{{ $lead->subject ?: 'General enquiry' }}</strong></div>
                        <div><small>Customer Message</small><p>{{ $lead->message ?: 'No message provided.' }}</p></div>
                    </div>
                </div>
            </section>

            @if($lead->emailid)
            <section class="ld-card" id="lead-email-composer">
                <header>
                    <div>
                        <span>Customer communication</span>
                        <h3>Send Email</h3>
                    </div>
                </header>
                <div class="ld-body">
                    <form method="POST" action="{{ route('crm.lead_email_send',$lead->id) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">To</label>
                            <input class="form-control" type="email" value="{{ $lead->emailid }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <input class="form-control" name="subject" maxlength="255" required
                                   value="{{ old('subject', 'Regarding your travel enquiry') }}"
                                   placeholder="Email subject">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea class="form-control" name="message" rows="8" maxlength="10000" required
                                      placeholder="Write your message to the customer...">{{ old('message') }}</textarea>
                        </div>
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <small class="text-muted">
                                Email will be sent to {{ $lead->emailid }} and recorded in Lead Activity.
                            </small>
                            <button class="btn btn-primary" type="submit"
                                    onclick="return confirm('Send this email to {{ addslashes($lead->emailid) }}?')">
                                <i class="fa-regular fa-paper-plane me-1"></i>Send Email
                            </button>
                        </div>
                    </form>
                </div>
            </section>
            @endif

            <section class="ld-card" id="activity">
                <header><div><span>Lead activity</span><h3>Follow-ups, calls, notes & history</h3></div></header>
                <div class="ld-body">
                    <form method="POST" action="{{ route('crm.lead_activity',$lead->id) }}" class="ld-activity-form">
                        @csrf
                        <select class="form-select" name="activity_type">
                            <option value="note">Note</option>
                            <option value="call">Call</option>
                            <option value="email">Email</option>
                            <option value="follow_up">Follow-up</option>
                            <option value="task">Task</option>
                            <option value="quotation">Quotation</option>
                            <option value="meeting">Meeting</option>
                        </select>
                        <input class="form-control" name="title" required placeholder="What happened?">
                        <input class="form-control" name="details" placeholder="Optional detail">
                        <button class="btn btn-primary">Add activity</button>
                    </form>

                    <div class="ld-timeline">
                        <div class="created">
                            <span><i class="fa-solid fa-plus"></i></span>
                            <p><b>Lead received</b><small>{{ $created->format('d M Y, h:i A') }} · Website enquiry</small></p>
                        </div>
                        @foreach($activity as $a)
                            <div class="{{ $a->activity_type }}">
                                <span>
                                    <i class="fa-solid {{
                                        $a->activity_type==='call'?'fa-phone':
                                        ($a->activity_type==='email'?'fa-envelope':
                                        ($a->activity_type==='quotation'?'fa-file-lines':
                                        ($a->activity_type==='meeting'?'fa-calendar-check':
                                        ($a->activity_type==='stage_change'?'fa-arrows-rotate':'fa-note-sticky'))))
                                    }}"></i>
                                </span>
                                <p><b>{{ $a->title }}</b>@if($a->details)<em>{{ $a->details }}</em>@endif<small>{{ \Carbon\Carbon::parse($a->created_at)->format('d M Y, h:i A') }} · {{ $a->admin_name ?: 'CRM' }}</small></p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        </main>

        <aside>
            <section class="ld-card ld-workspace" id="lead-operations">
                <header><div><span>Agent workspace</span><h3>Lead operations</h3></div></header>
                <div class="ld-body">
                    <div class="ld-work-status">
                        <div><small>Stage</small><strong>{{ $stage }}</strong></div>
                        <div><small>Priority</small><strong>{{ $priority }}</strong></div>
                        <div><small>Owner</small><strong>{{ $leadMeta->agent_name ?: 'Unassigned' }}</strong></div>
                        <div><small>Age</small><strong>{{ $created->diffForHumans() }}</strong></div>
                    </div>

                    <form method="POST" action="{{ route('crm.lead_operations',$lead->id) }}" class="ld-ops-form">
                        @csrf
                        <label><span>Lead Stage</span><select class="form-select" name="stage">@foreach($leadStages as $s)<option value="{{ $s }}" @selected($stage===$s)>{{ $s }}</option>@endforeach</select></label>
                        <label><span>Priority</span><select class="form-select" name="priority">@foreach($leadPriorities as $p)<option value="{{ $p }}" @selected($priority===$p)>{{ $p }}</option>@endforeach</select></label>
                        <label><span>Assigned Agent</span><select class="form-select" name="assigned_to"><option value="">Unassigned</option>@foreach($admins as $a)<option value="{{ $a->id }}" @selected((string)$leadMeta->assigned_to===(string)$a->id)>{{ $a->name }}</option>@endforeach</select></label>
                        <label><span>Lead Source</span><input class="form-control" name="source" value="{{ old('source',$leadMeta->source) }}" placeholder="Website / PPC / Referral / Call"></label>
                        <label><span>Next Action</span><input class="form-control" name="next_action" value="{{ old('next_action',$leadMeta->next_action) }}" placeholder="Call customer / send quote / follow up"></label>
                        <label><span>Next Follow-up</span><input class="form-control" type="datetime-local" name="next_follow_up_at" value="{{ $leadMeta->next_follow_up_at ? \Carbon\Carbon::parse($leadMeta->next_follow_up_at)->format('Y-m-d\TH:i') : '' }}"></label>

                        <div class="ld-two">
                            <label><span>Travel From</span><input class="form-control" name="travel_from" value="{{ old('travel_from',$leadMeta->travel_from) }}"></label>
                            <label><span>Travel To</span><input class="form-control" name="travel_to" value="{{ old('travel_to',$leadMeta->travel_to ?: $lead->going_to) }}"></label>
                        </div>

                        <div class="ld-two">
                            <label><span>Return Date</span><input class="form-control" type="date" name="return_date" value="{{ $leadMeta->return_date }}"></label>
                            <label><span>Cabin</span><select class="form-select" name="cabin">@foreach(['','Economy','Premium Economy','Business','First'] as $c)<option value="{{ $c }}" @selected($leadMeta->cabin===$c)>{{ $c ?: 'Select cabin' }}</option>@endforeach</select></label>
                        </div>

                        <div class="ld-three">
                            <label><span>Adults</span><input class="form-control" type="number" min="0" name="adults" value="{{ $leadMeta->adults }}"></label>
                            <label><span>Children</span><input class="form-control" type="number" min="0" name="children" value="{{ $leadMeta->children }}"></label>
                            <label><span>Infants</span><input class="form-control" type="number" min="0" name="infants" value="{{ $leadMeta->infants }}"></label>
                        </div>

                        <label><span>Budget</span><input class="form-control" type="number" min="0" step="0.01" name="budget" value="{{ $leadMeta->budget }}" placeholder="0.00"></label>
                        <label><span>Private Note</span><textarea class="form-control" rows="4" name="private_note" placeholder="Internal agent-only note">{{ $leadMeta->private_note }}</textarea></label>

                        <button class="btn btn-primary w-100"><i class="fa-solid fa-floppy-disk me-1"></i>Save Lead Operations</button>
                    </form>
                </div>
            </section>
        </aside>
    </div>
</div>
@endsection
