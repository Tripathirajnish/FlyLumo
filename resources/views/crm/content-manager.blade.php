@extends('crm.layout')
@section('content')
<div class="crm-page-head">
  <div>
    <a href="{{ route('crm.cms') }}" class="crm-back-link"><i class="fa-solid fa-arrow-left"></i> Website CMS</a>
    <h1 class="title mt-2">Website Content Manager</h1>
    <div class="sub">Write website content in normal fields. No technical CMS keys required.</div>
  </div>

  <div class="d-flex gap-2">
    <a class="btn btn-light" href="{{ route('crm.footer_manager') }}"><i class="fa-solid fa-bars-staggered me-1"></i>Footer Links</a>
    <a class="btn btn-primary" href="/" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Preview Website</a>
  </div>
</div>

<form method="POST" action="{{ route('crm.content_manager_update') }}" enctype="multipart/form-data">
@csrf

<div class="visual-content-layout">
  <aside class="crm-card visual-content-nav">
    <div class="crm-card-head"><h3>Website Sections</h3></div>
    <nav>
      @foreach($groups as $id => $group)
        <a href="#content-{{ $id }}">{{ $group['title'] }}</a>
      @endforeach
    </nav>
  </aside>

  <main class="visual-content-main">
    @foreach($groups as $id => $group)
      <section class="crm-card visual-content-section" id="content-{{ $id }}">
        <div class="crm-card-head">
          <div>
            <h3>{{ $group['title'] }}</h3>
            <small>{{ $group['description'] }}</small>
          </div>
        </div>

        <div class="visual-content-fields">
          @foreach($group['fields'] as $field)
            <div class="visual-content-field {{ $field['type']==='textarea' ? 'wide' : '' }}">
              <label>{{ $field['label'] }}</label>

              @if($field['type'] === 'toggle')
                <input type="hidden" name="toggle_keys[{{ $field['key'] }}]" value="1">
                <label class="visual-toggle">
                  <input
                    type="checkbox"
                    name="content[{{ $field['key'] }}]"
                    value="1"
                    @checked((string)$field['value'] === '1')>
                  <span></span>
                  <b>{{ (string)$field['value'] === '1' ? 'Visible' : 'Hidden' }}</b>
                </label>
              @elseif($field['type'] === 'textarea')
                <textarea
                  class="form-control"
                  rows="4"
                  name="content[{{ $field['key'] }}]"
                  placeholder="Write content here...">{{ $field['value'] }}</textarea>
              @elseif($field['type'] === 'image')
                @if(!empty($field['value']))
                  <div class="mb-2">
                    <img src="{{ $field['value'] }}"
                         alt="{{ $field['label'] }}"
                         style="max-width:320px;max-height:140px;object-fit:cover;border-radius:8px">
                  </div>
                @endif
                <input
                  class="form-control"
                  type="file"
                  name="content_images[{{ $field['key'] }}]"
                  accept=".jpg,.jpeg,.png,.webp">
                <small>JPG, JPEG, PNG or WEBP · Max 5 MB · Leave empty to keep current image</small>
              @else
                <input
                  class="form-control"
                  name="content[{{ $field['key'] }}]"
                  value="{{ $field['value'] }}"
                  placeholder="Write content here...">
              @endif
            </div>
          @endforeach
        </div>
      </section>
    @endforeach
  </main>
</div>

<div class="visual-content-savebar">
  <div>
    <strong>Website Content</strong>
    <span>Save changes and refresh the website to see the result.</span>
  </div>

  <div>
    <a class="btn btn-light" href="/" target="_blank">Preview Website</a>
    <button class="btn btn-primary">
      <i class="fa-solid fa-floppy-disk me-1"></i>Save Website Content
    </button>
  </div>
</div>
</form>

<script>
document.querySelectorAll('.visual-toggle input').forEach(function(input){
  input.addEventListener('change', function(){
    const label = this.closest('.visual-toggle').querySelector('b');
    label.textContent = this.checked ? 'Visible' : 'Hidden';
  });
});
</script>
@endsection
