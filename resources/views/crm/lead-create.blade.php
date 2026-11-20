@extends('crm.layout')
@section('content')

<style>
.ml-page{max-width:1200px;margin:0 auto;font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Arial,sans-serif}
.ml-head{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:22px}
.ml-back{color:#64748b;text-decoration:none;font-size:13px;font-weight:700}
.ml-back:hover{color:#1f5bd8}
.ml-head h1{margin:8px 0 4px;color:#071f4f;font-size:32px;font-weight:750;letter-spacing:-.03em}
.ml-head p{margin:0;color:#73839a;font-size:14px}
.ml-grid{display:grid;grid-template-columns:minmax(0,1.55fr) minmax(320px,.75fr);gap:18px;align-items:start}
.ml-card{background:#fff;border:1px solid #dbe6f2;border-radius:18px;box-shadow:0 8px 24px rgba(12,44,88,.05);overflow:hidden;margin-bottom:18px}
.ml-card-head{padding:17px 20px;border-bottom:1px solid #e7eef6}
.ml-card-head small{display:block;color:#1f5bd8;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;margin-bottom:3px}
.ml-card-head h3{margin:0;color:#071f4f;font-size:17px;font-weight:750}
.ml-body{padding:20px}
.ml-two{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:15px}
.ml-three{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:15px}
.ml-field{display:block;margin-bottom:15px}
.ml-field>span{display:block;margin-bottom:6px;color:#34445b;font-size:12px;font-weight:750}
.ml-field .required{color:#e3194a}
.ml-field .form-control,.ml-field .form-select{min-height:44px;border:1px solid #d8e2ee;border-radius:10px}
.ml-field textarea.form-control{min-height:100px}
.ml-actions{display:flex;gap:10px;justify-content:flex-end;padding-top:4px}
.ml-actions .btn{min-height:44px;border-radius:10px;padding:0 18px;display:inline-flex;align-items:center;justify-content:center;font-weight:700}
.ml-save{background:#1f5bd8!important;border-color:#1f5bd8!important}
.ml-errors{padding:14px 18px;margin-bottom:18px;border:1px solid #fecdd3;border-radius:12px;background:#fff1f2;color:#9f1239;font-size:13px}
.ml-errors ul{margin:6px 0 0;padding-left:18px}
@media(max-width:900px){.ml-grid{grid-template-columns:1fr}.ml-two,.ml-three{grid-template-columns:1fr}.ml-head{align-items:flex-start;flex-direction:column}}
</style>

<div class="ml-page">
    <div class="ml-head">
        <div>
            <a class="ml-back" href="{{ route(request()->getHost()==='crm.fareshunt.com' ? 'crm.leads' : 'legacy.crm.leads') }}">
                <i class="fa-solid fa-arrow-left me-1"></i>Back to leads
            </a>
            <h1>Add New Lead</h1>
            <p>Create a manual sales enquiry and assign the next action to your CRM team.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="ml-errors">
            <strong>Please correct the following:</strong>
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route(request()->getHost()==='crm.fareshunt.com' ? 'crm.lead_store' : 'legacy.crm.lead_store') }}">
        @csrf

        <div class="ml-grid">
            <main>
                <section class="ml-card">
                    <div class="ml-card-head"><small>Customer</small><h3>Customer Details</h3></div>
                    <div class="ml-body">
                        <label class="ml-field">
                            <span>Customer Name <b class="required">*</b></span>
                            <input class="form-control" name="name" value="{{ old('name') }}" required placeholder="Full name">
                        </label>

                        <div class="ml-two">
                            <label class="ml-field">
                                <span>Phone <b class="required">*</b></span>
                                <input class="form-control" name="contact" value="{{ old('contact') }}" required placeholder="+1 555 000 0000">
                            </label>
                            <label class="ml-field">
                                <span>Email</span>
                                <input class="form-control" type="email" name="emailid" value="{{ old('emailid') }}" placeholder="customer@example.com">
                            </label>
                        </div>

                        <label class="ml-field">
                            <span>Subject</span>
                            <input class="form-control" name="subject" value="{{ old('subject') }}" placeholder="Flight enquiry / quotation request">
                        </label>

                        <label class="ml-field">
                            <span>Customer Message</span>
                            <textarea class="form-control" name="message" placeholder="Customer request or enquiry details">{{ old('message') }}</textarea>
                        </label>
                    </div>
                </section>

                <section class="ml-card">
                    <div class="ml-card-head"><small>Trip</small><h3>Travel Details</h3></div>
                    <div class="ml-body">
                        <div class="ml-two">
                            <label class="ml-field"><span>Travel From</span><input class="form-control" name="travel_from" value="{{ old('travel_from') }}" placeholder="JFK / New York"></label>
                            <label class="ml-field"><span>Travel To</span><input class="form-control" name="travel_to" value="{{ old('travel_to') }}" placeholder="LHR / London"></label>
                        </div>

                        <div class="ml-two">
                            <label class="ml-field"><span>Departure Date</span><input class="form-control" type="date" name="checkin_date" value="{{ old('checkin_date') }}"></label>
                            <label class="ml-field"><span>Return Date</span><input class="form-control" type="date" name="return_date" value="{{ old('return_date') }}"></label>
                        </div>

                        <div class="ml-three">
                            <label class="ml-field"><span>Adults</span><input class="form-control" type="number" min="1" max="50" name="adults" value="{{ old('adults',1) }}"></label>
                            <label class="ml-field"><span>Children</span><input class="form-control" type="number" min="0" max="50" name="children" value="{{ old('children',0) }}"></label>
                            <label class="ml-field"><span>Infants</span><input class="form-control" type="number" min="0" max="50" name="infants" value="{{ old('infants',0) }}"></label>
                        </div>

                        <div class="ml-two">
                            <label class="ml-field">
                                <span>Cabin</span>
                                <select class="form-select" name="cabin">
                                    <option value="">Select cabin</option>
                                    @foreach(['Economy','Premium Economy','Business','First'] as $c)
                                        <option value="{{ $c }}" @selected(old('cabin')===$c)>{{ $c }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="ml-field"><span>Budget (USD)</span><input class="form-control" type="number" min="0" step="0.01" name="budget" value="{{ old('budget') }}" placeholder="0.00"></label>
                        </div>
                    </div>
                </section>
            </main>

            <aside>
                <section class="ml-card">
                    <div class="ml-card-head"><small>CRM</small><h3>Lead Management</h3></div>
                    <div class="ml-body">
                        <label class="ml-field">
                            <span>Stage <b class="required">*</b></span>
                            <select class="form-select" name="stage" required>
                                @foreach($leadStages as $s)<option value="{{ $s }}" @selected(old('stage','New')===$s)>{{ $s }}</option>@endforeach
                            </select>
                        </label>

                        <label class="ml-field">
                            <span>Priority <b class="required">*</b></span>
                            <select class="form-select" name="priority" required>
                                @foreach($leadPriorities as $p)<option value="{{ $p }}" @selected(old('priority','Normal')===$p)>{{ $p }}</option>@endforeach
                            </select>
                        </label>

                        <label class="ml-field">
                            <span>Lead Source</span>
                            <input class="form-control" name="source" value="{{ old('source','Manual') }}" placeholder="Phone / Email / Referral / WhatsApp">
                        </label>

                        <label class="ml-field">
                            <span>Assigned Agent</span>
                            <select class="form-select" name="assigned_to">
                                <option value="">Unassigned</option>
                                @foreach($admins as $a)<option value="{{ $a->id }}" @selected((string)old('assigned_to')===(string)$a->id)>{{ $a->name }}</option>@endforeach
                            </select>
                        </label>

                        <label class="ml-field"><span>Next Action</span><input class="form-control" name="next_action" value="{{ old('next_action') }}" placeholder="Call customer / send quote"></label>
                        <label class="ml-field"><span>Next Follow-up</span><input class="form-control" type="datetime-local" name="next_follow_up_at" value="{{ old('next_follow_up_at') }}"></label>
                        <label class="ml-field"><span>Private Note</span><textarea class="form-control" name="private_note" placeholder="Internal agent-only note">{{ old('private_note') }}</textarea></label>

                        <div class="ml-actions">
                            <a class="btn btn-light" href="{{ route(request()->getHost()==='crm.fareshunt.com' ? 'crm.leads' : 'legacy.crm.leads') }}">Cancel</a>
                            <button class="btn btn-primary ml-save" type="submit"><i class="fa-solid fa-plus me-2"></i>Create Lead</button>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </form>
</div>
@endsection
