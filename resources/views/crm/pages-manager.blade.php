@extends('crm.layout')
@section('content')
<div class="crm-page-head">
  <div>
    <a href="{{ route('crm.cms') }}" class="crm-back-link"><i class="fa-solid fa-arrow-left"></i> Website CMS</a>
    <h1 class="title mt-2">Website Pages</h1>
    <div class="sub">Write and manage About Us, Privacy Policy, Cookie Policy, Terms, Return Policy and other website pages.</div>
  </div>
  <a class="btn btn-primary" href="{{ route('crm.page_create') }}"><i class="fa-solid fa-plus me-1"></i>New Page</a>
</div>

<div class="crm-card">
  <div class="crm-card-head">
    <form method="GET" class="pages-search">
      <input class="search" type="search" name="q" value="{{ request('q') }}" placeholder="Search pages...">
      <button class="btn btn-light"><i class="fa-solid fa-magnifying-glass"></i></button>
      @if(request('q'))<a class="btn btn-light" href="{{ route('crm.pages_manager') }}">Clear</a>@endif
    </form>
    <span class="soft blue">{{ $pages->count() }} pages</span>
  </div>

  <div class="pages-manager-grid">
    @forelse($pages as $page)
      @php
        $friendly = in_array($page->slug,['about-us','why-choose-us','privacy-policy','cookie-policy','terms-of-use','terms-and-conditions','return-policy','refund-and-dispute-policy','shipping-policy','cancellation-policy','disclaimer']);
        $previewUrl = $page->slug === 'contact-us' ? url('/contact-us') : ($friendly ? url('/'.$page->slug) : url('/page/'.$page->slug));
      @endphp
      <article class="page-manager-card">
        <div class="page-manager-top">
          <span class="page-type {{ strtolower($page->type) }}">{{ $page->type }}</span>
          <span class="page-status {{ $page->is_visible==='1'?'live':'hidden' }}"><i class="fa-solid fa-circle"></i> {{ $page->is_visible==='1'?'Published':'Hidden' }}</span>
        </div>
        <h3>{{ $page->title }}</h3>
        <code>/{{ $page->slug }}</code>
        <p>{{ \Illuminate\Support\Str::limit(strip_tags($page->description),120) ?: 'No page content yet.' }}</p>
        <div class="page-manager-actions">
          <a class="btn btn-light" href="{{ $previewUrl }}" target="_blank"><i class="fa-regular fa-eye me-1"></i>Preview</a>
          <a class="btn btn-primary" href="{{ route('crm.page_edit',$page->id) }}"><i class="fa-regular fa-pen-to-square me-1"></i>Edit Content</a>
        </div>
      </article>
    @empty
      <div class="empty">No website pages found.</div>
    @endforelse
  </div>
</div>
@endsection
