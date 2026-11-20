@extends('crm.layout')
@section('content')
<div class="crm-page-head">
    <div>
        <h1 class="title">Email Templates</h1>
        <div class="sub">Open, preview and edit reusable passenger communication templates.</div>
    </div>
</div>

<div class="crm-card crm-email-template-index">
    <div class="crm-card-head">
        <form method="GET" class="crm-template-search">
            <input class="search" type="search" name="q" value="{{ request('q') }}" placeholder="Search label, type or subject">
            <button class="btn btn-primary"><i class="fa-solid fa-magnifying-glass me-1"></i>Search</button>
            @if(request('q'))<a class="btn btn-light" href="{{ route('crm.email_templates') }}">Clear</a>@endif
        </form>
        <span class="soft blue">{{ $templates->count() }} templates</span>
    </div>

    <div class="crm-template-grid">
        @forelse($templates as $t)
            <article class="crm-template-card">
                <div class="crm-template-card-top">
                    <span class="crm-template-icon"><i class="fa-regular fa-envelope"></i></span>
                    <div>
                        <small>{{ $t->type }}</small>
                        <h3>{{ $t->label }}</h3>
                    </div>
                </div>

                <div class="crm-template-subject">
                    <span>Subject</span>
                    <strong>{{ $t->subject }}</strong>
                </div>

                <div class="crm-template-vars">
                    <span>Variables</span>
                    <p>{{ \Illuminate\Support\Str::limit(strip_tags($t->variable ?? ''), 130) ?: 'No variables documented.' }}</p>
                </div>

                <div class="crm-template-actions">
                    <a class="btn btn-light" target="_blank" href="{{ route('crm.email_template_preview',$t->id) }}">
                        <i class="fa-regular fa-eye me-1"></i>Preview
                    </a>
                    <a class="btn btn-primary" href="{{ route('crm.email_template_edit',$t->id) }}">
                        <i class="fa-regular fa-pen-to-square me-1"></i>Open / Edit
                    </a>
                </div>
            </article>
        @empty
            <div class="empty p-4">No email templates configured.</div>
        @endforelse
    </div>
</div>
@endsection
