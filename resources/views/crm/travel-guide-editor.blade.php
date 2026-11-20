@extends('crm.layout')
@section('content')
@php
  $isEdit = $mode === 'edit';
  $slug = old('slug', $guide->slug ?? '');
  $previewUrl = $slug ? url('/blog/'.$slug) : '#';
@endphp

<div class="crm-page-head">
  <div>
    <a href="{{ route('crm.travel_guides_manager') }}" class="crm-back-link"><i class="fa-solid fa-arrow-left"></i> Travel Guides</a>
    <h1 class="title mt-2">{{ $isEdit ? 'Edit Travel Guide' : 'Create Travel Guide' }}</h1>
    <div class="sub">Manage the customer-facing article, category, images, publishing details and SEO.</div>
  </div>
  @if($isEdit)
    <a class="btn btn-light" href="{{ $previewUrl }}" target="_blank"><i class="fa-regular fa-eye me-1"></i>Preview Guide</a>
  @endif
</div>

@if($errors->any())
<div class="alert alert-danger">
  <b>Please fix the following:</b>
  <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ $isEdit ? route('crm.travel_guide_update',$guide->id) : route('crm.travel_guide_store') }}" id="travelGuideEditorForm" enctype="multipart/form-data">
@csrf
@if($isEdit) @method('PUT') @endif

<div class="page-editor-layout">
  <main>
    <section class="crm-card">
      <div class="crm-card-head">
        <div><h3>Travel Guide Content</h3><small>This is what visitors read on the website.</small></div>
      </div>

      <div class="p-3">
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label">Guide Title</label>
            <input class="form-control" id="guideTitle" name="title" value="{{ old('title',$guide->title ?? '') }}" required>
          </div>

          <div class="col-md-4">
            <label class="form-label">Category</label>
            <select class="form-select" name="category_id" required>
              <option value="">Select category</option>
              @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected((string)old('category_id',$guide->category_id ?? '') === (string)$category->id)>
                  {{ $category->title }}{{ $category->is_visible === '1' ? '' : ' (Hidden)' }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Guide URL</label>
            <div class="page-slug-wrap">
              <span>/blog/</span>
              <input class="form-control" id="guideSlug" name="slug" value="{{ old('slug',$guide->slug ?? '') }}" placeholder="your-travel-guide">
            </div>
            <small class="form-text">Changing an existing URL can break old links. Leave the current slug unchanged unless you intentionally want a new URL.</small>
          </div>

          <div class="col-12">
            <label class="form-label">Short Summary</label>
            <textarea class="form-control" rows="3" maxlength="255" name="short" required>{{ old('short',$guide->short ?? '') }}</textarea>
            <small class="form-text">Maximum 255 characters. Used for Travel Guide cards and summaries.</small>
          </div>

          <div class="col-12">
            <label class="form-label">Article Content</label>
            <div class="rich-toolbar">
              <button type="button" onclick="guideEditorCmd('bold')" title="Bold"><i class="fa-solid fa-bold"></i></button>
              <button type="button" onclick="guideEditorCmd('italic')" title="Italic"><i class="fa-solid fa-italic"></i></button>
              <button type="button" onclick="guideEditorBlock('h2')" title="Heading"><b>H2</b></button>
              <button type="button" onclick="guideEditorBlock('h3')" title="Subheading"><b>H3</b></button>
              <button type="button" onclick="guideEditorCmd('insertUnorderedList')" title="Bullet list"><i class="fa-solid fa-list-ul"></i></button>
              <button type="button" onclick="guideEditorCmd('insertOrderedList')" title="Numbered list"><i class="fa-solid fa-list-ol"></i></button>
              <button type="button" onclick="guideEditorLink()" title="Add link"><i class="fa-solid fa-link"></i></button>
              <button type="button" onclick="guideEditorCmd('removeFormat')" title="Clear formatting"><i class="fa-solid fa-eraser"></i></button>
            </div>

            <div id="richGuideEditor" class="rich-page-editor" contenteditable="true">{!! old('description',$guide->description ?? '<p>Start writing your Travel Guide here...</p>') !!}</div>
            <textarea name="description" id="guideDescription" hidden></textarea>
          </div>
        </div>
      </div>
    </section>

    <section class="crm-card mt-3">
      <div class="crm-card-head"><div><h3>SEO</h3><small>Search-engine information stored with this Travel Guide.</small></div></div>
      <div class="p-3">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">SEO Title</label>
            <input class="form-control" name="seo_title" value="{{ old('seo_title',$guide->seo_title ?? '') }}" maxlength="255">
          </div>

          <div class="col-12">
            <label class="form-label">Meta Description</label>
            <textarea class="form-control" rows="3" name="seo_description" maxlength="255">{{ old('seo_description',$guide->seo_description ?? '') }}</textarea>
          </div>

          <div class="col-12">
            <label class="form-label">SEO Keywords</label>
            <input class="form-control" name="seo_keyword" value="{{ old('seo_keyword',$guide->seo_keyword ?? '') }}" maxlength="255">
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
          <input type="checkbox" name="is_visible" value="1" @checked(old('is_visible',$guide->is_visible ?? '1') === '1' || old('is_visible') === 1)>
          <span></span><b>Published</b>
        </label>

        <label class="form-label mt-3">Author</label>
        <input class="form-control" name="author" value="{{ old('author',$guide->author ?? 'Fares Hunt Team') }}" maxlength="100" required>

        <label class="form-label mt-3">Article Date</label>
        <input class="form-control" type="date" name="date" value="{{ old('date',$guide->date ?? date('Y-m-d')) }}" required>

        <label class="form-label mt-3">Display Order</label>
        <input class="form-control" type="number" name="sequence" value="{{ old('sequence',$guide->sequence ?? 0) }}" min="0" max="9999">

        <label class="form-label mt-3">Card Image</label>
        @if(!empty($guide->image))
        <div class="mb-2"><img src="{{ $guide->image }}" alt="Current image" style="max-width:260px;max-height:140px;object-fit:cover;border-radius:8px"></div>
        @endif
        <input class="form-control" type="file" name="image_file" accept=".jpg,.jpeg,.png,.webp" {{ $isEdit ? '' : 'required' }}>
        <small>JPG, JPEG, PNG or WEBP · Max 5 MB{{ $isEdit ? ' · Leave empty to keep current image' : '' }}</small>
        <small class="form-text">Maximum 100 characters due to the current database field.</small>

        <label class="form-label mt-3">Banner Image</label>
        @if(!empty($guide->banner))
        <div class="mb-2"><img src="{{ $guide->banner }}" alt="Current banner" style="max-width:320px;max-height:140px;object-fit:cover;border-radius:8px"></div>
        @endif
        <input class="form-control" type="file" name="banner_file" accept=".jpg,.jpeg,.png,.webp">
        <small>JPG, JPEG, PNG or WEBP · Max 5 MB{{ !empty($guide->banner) ? ' · Leave empty to keep current banner' : '' }}</small>

        <button class="btn btn-primary w-100 mt-3">
          <i class="fa-solid fa-floppy-disk me-1"></i>{{ $isEdit ? 'Save Travel Guide' : 'Create Travel Guide' }}
        </button>
      </div>
    </section>

    @if($isEdit)
    <section class="crm-card mt-3 page-danger-zone">
      <div class="crm-card-head"><h3>Delete Travel Guide</h3></div>
      <div class="p-3">
        <p>Deleting this guide permanently removes the article and its public URL.</p>
        <button type="button" class="btn btn-danger w-100" onclick="document.getElementById('deleteTravelGuideForm').submit()">
          <i class="fa-regular fa-trash-can me-1"></i>Delete Travel Guide
        </button>
      </div>
    </section>
    @endif
  </aside>
</div>
</form>

@if($isEdit)
<form method="POST" action="{{ route('crm.travel_guide_delete',$guide->id) }}" id="deleteTravelGuideForm" onsubmit="return confirm('Delete this Travel Guide permanently?');">
@csrf
@method('DELETE')
</form>
@endif

<script>
const guideForm = document.getElementById('travelGuideEditorForm');
const guideRichEditor = document.getElementById('richGuideEditor');
const guideHiddenDescription = document.getElementById('guideDescription');
const guideTitleInput = document.getElementById('guideTitle');
const guideSlugInput = document.getElementById('guideSlug');

guideForm.addEventListener('submit', function(){
  guideHiddenDescription.value = guideRichEditor.innerHTML;
});

@if(!$isEdit)
let guideSlugTouched = false;
guideSlugInput.addEventListener('input', () => guideSlugTouched = true);
guideTitleInput.addEventListener('input', function(){
  if(guideSlugTouched) return;
  guideSlugInput.value = this.value.toLowerCase().trim()
    .replace(/[^a-z0-9]+/g,'-')
    .replace(/^-+|-+$/g,'');
});
@endif

function guideEditorCmd(command){
  guideRichEditor.focus();
  document.execCommand(command,false,null);
}

function guideEditorBlock(tag){
  guideRichEditor.focus();
  document.execCommand('formatBlock',false,tag);
}

function guideEditorLink(){
  const url = prompt('Enter link URL, for example /deals or https://example.com');
  if(url){
    guideRichEditor.focus();
    document.execCommand('createLink',false,url);
  }
}
</script>
@endsection
