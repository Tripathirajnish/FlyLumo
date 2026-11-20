@extends('crm.layout')
@section('content')
<div class="crm-page-head">
  <div>
    <a href="{{ route('crm.cms') }}" class="crm-back-link"><i class="fa-solid fa-arrow-left"></i> Website CMS</a>
    <h1 class="title mt-2">Travel Guides</h1>
    <div class="sub">Create and manage customer-facing Travel Guide articles, categories, visibility and content.</div>
  </div>
  <a class="btn btn-primary" href="{{ route('crm.travel_guide_create') }}"><i class="fa-solid fa-plus me-1"></i>New Travel Guide</a>
</div>

<div class="crm-card">
  <div class="crm-card-head">
    <form method="GET" class="pages-search">
      <input class="search" type="search" name="q" value="{{ request('q') }}" placeholder="Search travel guides...">
      <button class="btn btn-light"><i class="fa-solid fa-magnifying-glass"></i></button>
      @if(request('q'))<a class="btn btn-light" href="{{ route('crm.travel_guides_manager') }}">Clear</a>@endif
    </form>
    <span class="soft blue">{{ $guides->count() }} guides</span>
  </div>

  <div class="pages-manager-grid">
    @forelse($guides as $guide)
      <article class="page-manager-card">
        <div class="page-manager-top">
          <span class="page-type basic">{{ $guide->category_title ?: 'Travel Guide' }}</span>
          <span class="page-status {{ $guide->is_visible==='1'?'live':'hidden' }}">
            <i class="fa-solid fa-circle"></i> {{ $guide->is_visible==='1'?'Published':'Hidden' }}
          </span>
        </div>

        <h3>{{ $guide->title }}</h3>
        <code>/blog/{{ $guide->slug }}</code>

        <p>{{ \Illuminate\Support\Str::limit(strip_tags($guide->short ?: $guide->description),120) ?: 'No article summary yet.' }}</p>

        <div class="page-manager-actions">
          <a class="btn btn-light" href="{{ url('/blog/'.$guide->slug) }}" target="_blank">
            <i class="fa-regular fa-eye me-1"></i>Preview
          </a>
          <a class="btn btn-primary" href="{{ route('crm.travel_guide_edit',$guide->id) }}">
            <i class="fa-regular fa-pen-to-square me-1"></i>Edit Content
          </a>
        </div>
      </article>
    @empty
      <div class="empty">No Travel Guides found.</div>
    @endforelse
  </div>
</div>
@endsection
