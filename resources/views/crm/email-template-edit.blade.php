@extends('crm.layout')
@section('content')
<div class="crm-page-head">
    <div>
        <a class="crm-back-link" href="{{ route('crm.email_templates') }}"><i class="fa-solid fa-arrow-left"></i> Email Templates</a>
        <h1 class="title mt-2">Edit Email Template</h1>
        <div class="sub">Changes affect the reusable database template. Preview before using it for passenger communications.</div>
    </div>
    <a class="btn btn-light" target="_blank" href="{{ route('crm.email_template_preview',$template->id) }}">
        <i class="fa-regular fa-eye me-1"></i>Preview Current
    </a>
</div>

@if($errors->any())
<div class="alert alert-danger">
    <b>Please fix the following:</b>
    <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('crm.email_template_update',$template->id) }}">
    @csrf
    @method('PUT')

    <div class="crm-template-editor-grid">
        <div class="crm-card">
            <div class="crm-card-head"><h3>Template details</h3></div>
            <div class="p-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Label</label>
                        <input class="form-control" name="label" value="{{ old('label',$template->label) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Type</label>
                        <input class="form-control" name="type" value="{{ old('type',$template->type) }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Email Subject</label>
                        <input class="form-control" name="subject" value="{{ old('subject',$template->subject) }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Variables / Notes</label>
                        <textarea class="form-control crm-code-area" rows="4" name="variable" placeholder="Document supported placeholders here">{{ old('variable',$template->variable ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <aside class="crm-card crm-template-help">
            <div class="crm-card-head"><h3>Common variables</h3></div>
            <div class="p-3">
                @foreach(['$bookingid','$c_name','$c_emailid','$c_phone','$pnr','$passengers','$sectorgos','$sectorreturns','$header_contact','$content_emailid'] as $v)
                    <code>{{ $v }}</code>
                @endforeach
                <p class="mt-3 mb-0">Keep variable spelling unchanged if the legacy mail builder uses it.</p>
            </div>
        </aside>
    </div>

    <div class="crm-card mt-3">
        <div class="crm-card-head">
            <div><h3>Email body</h3><small>HTML is supported by the existing mail template system.</small></div>
        </div>
        <div class="p-3">
            <textarea class="form-control crm-html-editor" rows="20" name="body">{{ old('body',$template->body ?? '') }}</textarea>
        </div>
    </div>

    <div class="crm-card mt-3">
        <div class="crm-card-head"><h3>Terms / Footer content</h3></div>
        <div class="p-3">
            <textarea class="form-control crm-html-editor" rows="8" name="tnc">{{ old('tnc',$template->tnc ?? '') }}</textarea>
        </div>
    </div>

    <div class="crm-template-savebar">
        <a class="btn btn-light" href="{{ route('crm.email_templates') }}">Cancel</a>
        <button class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Save Template</button>
    </div>
</form>
@endsection
