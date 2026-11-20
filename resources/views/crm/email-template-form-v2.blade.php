@extends('crm.layout')
@section('content')
<style>
.etf-grid{display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:14px}.etf-vars{display:flex;flex-wrap:wrap;gap:6px}.etf-vars code{font-size:9px;padding:5px 7px;background:#f0f4f9;border-radius:7px;color:#354a65}.etf-code{font-family:Consolas,"Courier New",monospace!important;font-size:11px!important;line-height:1.5!important}.etf-save{position:sticky;bottom:10px;z-index:4;margin-top:14px;display:flex;justify-content:flex-end;gap:8px;padding:11px;background:rgba(255,255,255,.95);border:1px solid var(--crm-line);border-radius:13px;box-shadow:0 10px 35px rgba(9,34,62,.09)}@media(max-width:900px){.etf-grid{grid-template-columns:1fr}}
</style>
<div class="crm-page-head"><div><a class="crm-back-link" href="{{ route('crm.email_templates') }}"><i class="fa-solid fa-arrow-left"></i> Email Templates</a><h1 class="title mt-2">{{ $template ? 'Edit Email Template' : 'Create Email Template' }}</h1><div class="sub">Template management only. SMTP credentials and flight/booking logic are not changed here.</div></div>@if($template)<a class="btn btn-light" target="_blank" href="{{ route('crm.email_template_preview',$template->id) }}"><i class="fa-regular fa-eye me-1"></i> Preview Current</a>@endif</div>
@if(session('crm_success'))<div class="crm-success mt-3">{{ session('crm_success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger mt-3"><b>Please fix:</b><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
<form method="post" action="{{ $template ? route('crm.email_template_update',$template->id) : route('crm.email_template_store') }}">@csrf @if($template)@method('PUT')@endif
<div class="etf-grid"><div class="crm-card"><div class="crm-card-head"><h3>Template details</h3></div><div class="p-3"><div class="row g-3"><div class="col-md-6"><label class="form-label">Label</label><input class="form-control" name="label" required value="{{ old('label',$template->label ?? '') }}"></div><div class="col-md-6"><label class="form-label">Legacy type</label><input class="form-control" name="type" required value="{{ old('type',$template->type ?? 'Custom') }}"></div><div class="col-md-6"><label class="form-label">Category</label><select class="form-select" name="category">@foreach($categories as $c)<option {{ old('category',$template->category ?? 'Custom')===$c?'selected':'' }}>{{ $c }}</option>@endforeach</select></div><div class="col-md-6"><label class="form-label">Status</label><div class="form-check mt-2"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active',isset($template)?(int)($template->is_active ?? 1):0)?'checked':'' }}><label class="form-check-label" for="is_active">Active / available for use</label></div></div><div class="col-12"><label class="form-label">Email subject</label><input class="form-control" name="subject" required value="{{ old('subject',$template->subject ?? '') }}"></div><div class="col-12"><label class="form-label">Variables / internal notes</label><textarea class="form-control etf-code" rows="4" name="variable">{{ old('variable',$template->variable ?? '') }}</textarea></div></div></div></div>
<aside class="crm-card"><div class="crm-card-head"><h3>Legacy-safe variables</h3></div><div class="p-3"><div class="etf-vars">@foreach($variables as $v)<code>{{ $v }}</code>@endforeach</div><p class="mt-3 mb-0" style="font-size:9px;color:#75859a">Keep the exact <code>$variable</code> spelling for legacy templates. Do not place full card number or CVV in email content.</p></div></aside></div>
@php
    $isSystemTicketBooking =
        $template
        && !empty($template->is_system)
        && strcasecmp(trim((string) ($template->label ?? '')), 'Ticket Booking') === 0
        && strcasecmp(trim((string) ($template->category ?? '')), 'Booking Confirmation') === 0;
@endphp

@if($isSystemTicketBooking)
    <input type="hidden" name="body" value="{{ old('body',$template->body ?? '') }}">
    <input type="hidden" name="tnc" value="{{ old('tnc',$template->tnc ?? '') }}">

    <div class="crm-card mt-3">
        <div class="crm-card-head">
            <div>
                <h3>Email body</h3>
                <small>System-managed modern booking confirmation.</small>
            </div>
        </div>
        <div class="p-3">
            <div class="alert alert-info mb-0">
                The passenger-facing email body is rendered from the modern
                <code>emails.booking-confirmation</code> template. The legacy database body is preserved for compatibility but is not used by the automatic booking confirmation email.
            </div>
        </div>
    </div>

    <div class="crm-card mt-3">
        <div class="crm-card-head"><h3>Terms / footer content</h3></div>
        <div class="p-3">
            <div class="alert alert-info mb-0">
                Footer and booking-confirmation content are managed by the same modern system template. The stored legacy footer content is preserved and will not be changed from this screen.
            </div>
        </div>
    </div>
@else
    <div class="crm-card mt-3"><div class="crm-card-head"><div><h3>Email body</h3><small>HTML is supported by the existing template database.</small></div></div><div class="p-3"><textarea class="form-control etf-code" style="min-height:390px" name="body">{{ old('body',$template->body ?? '') }}</textarea></div></div>
    <div class="crm-card mt-3"><div class="crm-card-head"><h3>Terms / footer content</h3></div><div class="p-3"><textarea class="form-control etf-code" rows="9" name="tnc">{{ old('tnc',$template->tnc ?? '') }}</textarea></div></div>
@endif
<div class="etf-save"><a class="btn btn-light" href="{{ route('crm.email_templates') }}">Cancel</a><button class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>{{ $template?'Save Template':'Create Template' }}</button></div></form>
@endsection
