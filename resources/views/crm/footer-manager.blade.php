@extends('crm.layout')

@section('content')

<div class="crm-page-head">
  <div>
    <a href="{{ route('crm.cms') }}" class="crm-back-link">
      <i class="fa-solid fa-arrow-left"></i> Website CMS
    </a>
    <h1 class="title mt-2">Footer Manager</h1>
    <div class="sub">Manage public footer navigation and legal links from one place.</div>
  </div>

  <div class="d-flex gap-2">
    <a class="btn btn-light" href="{{ route('crm.content_manager') }}">
      <i class="fa-regular fa-pen-to-square me-1"></i>Website Content
    </a>
    <a class="btn btn-primary" href="/" target="_blank">
      <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Open Website
    </a>
  </div>
</div>

@php
  /*
   * Public URL aliases.
   * The CMS may retain legacy database slugs, while the public website uses
   * cleaner canonical URLs. This affects only the page picker shown here.
   */
  $footerPublicUrl = function ($page) {
      $slug = trim((string) ($page->slug ?? ''));

      $canonical = [
          'terms-of-use'               => '/terms-and-conditions',
          'terms-and-conditions'       => '/terms-and-conditions',
          'return-policy'              => '/refund-and-dispute-policy',
          'refund-and-dispute-policy'  => '/refund-and-dispute-policy',
          'contact-us'                 => '/contact-us',
      ];

      if (isset($canonical[$slug])) {
          return url($canonical[$slug]);
      }

      $directSlugs = [
          'about-us',
          'why-choose-us',
          'privacy-policy',
          'cookie-policy',
          'shipping-policy',
          'cancellation-policy',
          'disclaimer',
      ];

      if (in_array($slug, $directSlugs, true)) {
          return url('/'.$slug);
      }

      return url('/page/'.$slug);
  };

  /*
   * Normalize legacy URLs already stored in Footer Manager fields so the
   * editor shows/saves the current public destination.
   */
  $normalizeFooterUrl = function ($value) {
      $value = trim((string) $value);

      $replacements = [
          '/offers'        => '/deals',
          '/terms-of-use'  => '/terms-and-conditions',
          '/return-policy' => '/refund-and-dispute-policy',
      ];

      if (isset($replacements[$value])) {
          return $replacements[$value];
      }

      return $value;
  };
@endphp

<form method="POST" action="{{ route('crm.footer_manager_update') }}">
@csrf

<div class="footer-manager-grid">
  @foreach($columns as $column)
    <section class="crm-card footer-manager-column">
      <div class="crm-card-head">
        <div>
          <span class="footer-manager-kicker">Footer column {{ $column['column'] }}</span>
          <input
            class="footer-column-title"
            name="columns[{{ $column['column'] }}][title]"
            value="{{ $column['title'] }}"
            aria-label="Column title">
        </div>
      </div>

      <div class="footer-link-list">
        @foreach($column['links'] as $link)
          <div class="footer-link-row">
            <div class="footer-link-number">{{ $link['slot'] }}</div>

            <div class="footer-link-fields">
              <label>Link name</label>
              <input
                class="form-control"
                name="columns[{{ $column['column'] }}][links][{{ $link['slot'] }}][label]"
                value="{{ $link['label'] }}"
                placeholder="e.g. One Way Flights">

              <label>Link goes to</label>
              <input
                class="form-control"
                name="columns[{{ $column['column'] }}][links][{{ $link['slot'] }}][url]"
                value="{{ $normalizeFooterUrl($link['url']) }}"
                placeholder="/search-flight or https://...">

              <select class="form-select footer-page-picker" onchange="pickFooterPage(this)">
                <option value="">Or choose a website page…</option>
                @foreach($websitePages as $wp)
                  <option value="{{ $footerPublicUrl($wp) }}">
                    {{ $wp->title }} ({{ $wp->type }})
                  </option>
                @endforeach
              </select>
            </div>

            <button
              class="footer-clear-link"
              type="button"
              title="Clear this link"
              aria-label="Clear footer link"
              onclick="clearFooterLink(this)">
              <i class="fa-regular fa-trash-can"></i>
            </button>
          </div>
        @endforeach
      </div>
    </section>
  @endforeach
</div>

<section class="crm-card mt-3">
  <div class="crm-card-head">
    <div>
      <h3>Bottom Footer Links</h3>
      <small>Manage Privacy Policy, Terms & Conditions, Contact, Sitemap and other footer links.</small>
    </div>
  </div>

  <div class="footer-bottom-link-grid">
    @foreach($bottomLinks as $link)
      <div class="footer-bottom-link-card">
        <span>Link {{ $link['slot'] }}</span>

        <label>Link name</label>
        <input
          class="form-control"
          name="bottom[{{ $link['slot'] }}][label]"
          value="{{ $link['label'] }}"
          placeholder="Privacy Policy">

        <label>Link goes to</label>
        <input
          class="form-control"
          name="bottom[{{ $link['slot'] }}][url]"
          value="{{ $normalizeFooterUrl($link['url']) }}"
          placeholder="/privacy-policy">

        <select class="form-select footer-page-picker" onchange="pickFooterPage(this)">
          <option value="">Or choose a website page…</option>
          @foreach($websitePages as $wp)
            <option value="{{ $footerPublicUrl($wp) }}">
              {{ $wp->title }}
            </option>
          @endforeach
        </select>
      </div>
    @endforeach
  </div>
</section>

<div class="footer-manager-savebar">
  <div>
    <strong>Footer changes</strong>
    <span>Save once, then refresh the public website.</span>
  </div>

  <div>
    <a class="btn btn-light" href="/" target="_blank">Preview Website</a>
    <button class="btn btn-primary">
      <i class="fa-solid fa-floppy-disk me-1"></i>Save Footer
    </button>
  </div>
</div>

</form>

<script>
function clearFooterLink(btn) {
  const row = btn.closest('.footer-link-row');
  if (!row) return;

  row.querySelectorAll('input').forEach(function (input) {
    input.value = '';
  });

  const picker = row.querySelector('.footer-page-picker');
  if (picker) picker.value = '';
}

function pickFooterPage(select) {
  if (!select.value) return;

  const box =
    select.closest('.footer-link-fields') ||
    select.closest('.footer-bottom-link-card');

  if (!box) return;

  const urlInput = box.querySelector('input[name$="[url]"]');
  if (!urlInput) return;

  try {
    const picked = new URL(select.value, window.location.origin);

    urlInput.value =
      picked.origin === window.location.origin
        ? picked.pathname + picked.search + picked.hash
        : picked.href;
  } catch (e) {
    urlInput.value = select.value.replace(window.location.origin, '');
  }
}
</script>

@endsection
