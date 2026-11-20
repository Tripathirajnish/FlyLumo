@extends('crm.layout')
@section('content')
@php
  $isEdit = $mode === 'edit';
  $slug = old('slug', $page->slug ?? '');
  $friendly = in_array($slug,['about-us','why-choose-us','privacy-policy','cookie-policy','terms-of-use','terms-and-conditions','return-policy','refund-and-dispute-policy','shipping-policy','cancellation-policy','disclaimer']);
  $previewUrl = $slug === 'contact-us' ? url('/contact-us') : ($friendly ? url('/'.$slug) : ($slug ? url('/page/'.$slug) : '#'));
@endphp

<div class="crm-page-head">
  <div>
    <a href="{{ route('crm.pages_manager') }}" class="crm-back-link"><i class="fa-solid fa-arrow-left"></i> Website Pages</a>
    <h1 class="title mt-2">{{ $isEdit ? 'Edit Page' : 'Create Page' }}</h1>
    <div class="sub">Write actual customer-facing website content here.</div>
  </div>
  @if($isEdit)<a class="btn btn-light" href="{{ $previewUrl }}" target="_blank"><i class="fa-regular fa-eye me-1"></i>Preview Page</a>@endif
</div>

@if($errors->any())
<div class="alert alert-danger">
  <b>Please fix the following:</b>
  <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ $isEdit ? route('crm.page_update',$page->id) : route('crm.page_store') }}" id="pageEditorForm" enctype="multipart/form-data">
@csrf
@if($isEdit) @method('PUT') @endif

<div class="page-editor-layout">
  <main>
    <section class="crm-card">
      <div class="crm-card-head"><div><h3>Page Content</h3><small>This is what visitors read on the website.</small></div></div>
      <div class="p-3">
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label">Page Title</label>
            <input class="form-control" id="pageTitle" name="title" value="{{ old('title',$page->title ?? '') }}" placeholder="Privacy Policy" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Page Type</label>
            <select class="form-select" name="type">
              <option value="Basic" @selected(old('type',$page->type ?? 'Basic')==='Basic')>Basic Page</option>
              <option value="Legal" @selected(old('type',$page->type ?? 'Basic')==='Legal')>Legal / Policy</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">Page URL</label>
            <div class="page-slug-wrap"><span>/</span><input class="form-control" id="pageSlug" name="slug" value="{{ old('slug',$page->slug ?? '') }}" placeholder="privacy-policy"></div>
            <small class="form-text">Example: <b>privacy-policy</b> â†’ website URL /privacy-policy for core policy pages.</small>
          </div>

          <div class="col-12">
            <label class="form-label">Page Content</label>
            <div class="rich-toolbar">
              <button type="button" onclick="editorCmd('bold')" title="Bold"><i class="fa-solid fa-bold"></i></button>
              <button type="button" onclick="editorCmd('italic')" title="Italic"><i class="fa-solid fa-italic"></i></button>
              <button type="button" onclick="editorBlock('h2')" title="Heading"><b>H2</b></button>
              <button type="button" onclick="editorBlock('h3')" title="Subheading"><b>H3</b></button>
              <button type="button" onclick="editorCmd('insertUnorderedList')" title="Bullet list"><i class="fa-solid fa-list-ul"></i></button>
              <button type="button" onclick="editorCmd('insertOrderedList')" title="Numbered list"><i class="fa-solid fa-list-ol"></i></button>
              <button type="button" onclick="editorLink()" title="Add link"><i class="fa-solid fa-link"></i></button>
              <button type="button" onclick="editorCmd('removeFormat')" title="Clear formatting"><i class="fa-solid fa-eraser"></i></button>
            </div>
            <div id="richPageEditor" class="rich-page-editor" contenteditable="true">{!! old('description',$page->description ?? '<p>Start writing your page content here...</p>') !!}</div>
            <textarea name="description" id="pageDescription" hidden></textarea>
          </div>
        </div>
      </div>
    </section>

    <section class="crm-card mt-3">
      <div class="crm-card-head"><div><h3>SEO</h3><small>Controls how the page appears in search engines.</small></div></div>
      <div class="p-3">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">SEO Title</label>
            <input class="form-control" name="seo_title" value="{{ old('seo_title',$page->seo_title ?? '') }}" placeholder="Privacy Policy | FaresHunt">
          </div>
          <div class="col-12">
            <label class="form-label">Meta Description</label>
            <textarea class="form-control" rows="3" name="seo_description">{{ old('seo_description',$page->seo_description ?? '') }}</textarea>
          </div>
          <div class="col-12">
            <label class="form-label">SEO Keywords</label>
            <input class="form-control" name="seo_keyword" value="{{ old('seo_keyword',$page->seo_keyword ?? '') }}" placeholder="travel, flights, policy">
          </div>
        </div>
      </div>
    </section>
  </main>

  <aside>
    <section class="crm-card page-editor-side">
      <div class="crm-card-head"><h3>Publish</h3></div>
      <div class="p-3">
        <label class="page-publish-toggle">
          <input type="checkbox" name="is_visible" value="1" @checked(old('is_visible',$page->is_visible ?? '1')==='1' || old('is_visible')===1)>
          <span></span><b>Published</b>
        </label>

        <label class="form-label mt-3">Display Order</label>
        <input class="form-control" type="number" name="sequence" value="{{ old('sequence',$page->sequence ?? 0) }}" min="0">

        <label class="form-label mt-3">Banner Image</label>
        @if(!empty($page->banner))
        <div class="mb-2"><small>Current banner</small><br><img src="{{ $page->banner }}" alt="Current banner" style="max-width:320px;max-height:140px;object-fit:cover;border-radius:8px"></div>
        @endif
        <input class="form-control" type="file" name="banner_file" accept=".jpg,.jpeg,.png,.webp">
        <small>JPG, JPEG, PNG or WEBP · Max 5 MB{{ !empty($page->banner) ? ' · Leave empty to keep current banner' : '' }}</small>

        <button class="btn btn-primary w-100 mt-3"><i class="fa-solid fa-floppy-disk me-1"></i>{{ $isEdit ? 'Save Page' : 'Create Page' }}</button>
      </div>
    </section>

    @if($isEdit)
    <section class="crm-card mt-3 page-danger-zone">
      <div class="crm-card-head"><h3>Delete Page</h3></div>
      <div class="p-3">
        <p>Deleting a page can break footer links pointing to it.</p>
        <button type="button" class="btn btn-danger w-100" onclick="document.getElementById('deletePageForm').submit()"><i class="fa-regular fa-trash-can me-1"></i>Delete Page</button>
      </div>
    </section>
    @endif
  </aside>
</div>
</form>

@if($isEdit)
<form method="POST" action="{{ route('crm.page_delete',$page->id) }}" id="deletePageForm" onsubmit="return confirm('Delete this website page permanently?');">
@csrf @method('DELETE')
</form>
@endif

<script>
const pageForm = document.getElementById('pageEditorForm');
const richEditor = document.getElementById('richPageEditor');
const hiddenDescription = document.getElementById('pageDescription');
const titleInput = document.getElementById('pageTitle');
const slugInput = document.getElementById('pageSlug');

pageForm.addEventListener('submit', function(){
  hiddenDescription.value = richEditor.innerHTML;
});

@if(!$isEdit)
let slugTouched = false;
slugInput.addEventListener('input',()=>slugTouched=true);
titleInput.addEventListener('input',function(){
  if(slugTouched) return;
  slugInput.value = this.value.toLowerCase().trim()
    .replace(/[^a-z0-9]+/g,'-')
    .replace(/^-+|-+$/g,'');
});
@endif

function editorCmd(command){
  richEditor.focus();
  document.execCommand(command,false,null);
}
function editorBlock(tag){
  richEditor.focus();
  document.execCommand('formatBlock',false,tag);
}
function editorLink(){
  const url = prompt('Enter link URL, for example /contact-us or https://example.com');
  if(url){
    richEditor.focus();
    document.execCommand('createLink',false,url);
  }
}
</script>
@endsection
