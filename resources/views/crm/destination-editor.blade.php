@extends('crm.layout')

@section('content')
@php
$isEdit = $mode === 'edit';
@endphp

<div class="crm-page-head">
    <div>
        <a href="{{ route('crm.destinations_manager') }}" class="crm-back-link">
            <i class="fa-solid fa-arrow-left"></i> Destinations
        </a>

        <h1 class="title mt-2">
            {{ $isEdit ? 'Edit Destination' : 'Add Destination' }}
        </h1>

        <div class="sub">
            Manage destination content, images and search-engine metadata.
        </div>
    </div>

    @if($isEdit)
        <a href="{{ url('/destination/'.$destination->slug) }}"
           target="_blank"
           class="btn btn-light">
            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>
            View Page
        </a>
    @endif
</div>

@if($errors->any())
<div class="alert alert-danger">
    <strong>Please fix the following:</strong>
    <ul class="mb-0 mt-2">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST"
      enctype="multipart/form-data"
      action="{{ $isEdit ? route('crm.destination_update',$destination->id) : route('crm.destination_store') }}">

    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="crm-card mb-3">
        <div class="crm-card-head">
            <h3>Destination Details</h3>
        </div>

        <div class="p-3">
            <div class="row g-3">

                <div class="col-md-8">
                    <label class="form-label">Title</label>
                    <input class="form-control"
                           required
                           name="title"
                           value="{{ old('title',$destination->title ?? '') }}"
                           placeholder="Flights to Orlando">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Sequence</label>
                    <input class="form-control"
                           type="number"
                           min="0"
                           name="sequence"
                           value="{{ old('sequence',$destination->sequence ?? '') }}">
                </div>

                <div class="col-md-12">
                    <label class="form-label">URL Slug</label>
                    <input class="form-control"
                           name="slug"
                           value="{{ old('slug',$destination->slug ?? '') }}"
                           placeholder="flights-to-orlando">
                    <small>Leave blank to generate automatically from the title.</small>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Page Content</label>
                    <textarea class="form-control"
                              rows="14"
                              name="description"
                              placeholder="Destination page content...">{{ old('description',$destination->description ?? '') }}</textarea>
                    <small>Existing HTML content is supported.</small>
                </div>

                <div class="col-md-12">
                    <div class="form-check">
                        <input type="checkbox"
                               class="form-check-input"
                               id="is_visible"
                               name="is_visible"
                               value="1"
                               @checked(old('is_visible',$destination->is_visible ?? '1') == '1')>
                        <label class="form-check-label" for="is_visible">
                            Visible on website
                        </label>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="crm-card mb-3">
        <div class="crm-card-head">
            <h3>Destination Images</h3>
        </div>

        <div class="p-3">
            <div class="row g-4">

                <div class="col-md-6">
                    <label class="form-label">Main Destination Image</label>

                    @if($isEdit && !empty($destination->image))
                        <div class="mb-2">
                            <img src="{{ $destination->image }}"
                                 alt="Current destination image"
                                 style="width:100%;max-width:420px;height:200px;object-fit:cover;border-radius:10px">
                        </div>
                    @endif

                    <input class="form-control"
                           type="file"
                           name="image_file"
                           accept=".jpg,.jpeg,.png,.webp"
                           {{ $isEdit ? '' : 'required' }}>

                    <small>
                        JPG, JPEG, PNG or WEBP · Max 5 MB
                        @if($isEdit) · Leave empty to keep current image @endif
                    </small>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Banner Image</label>

                    @if($isEdit && !empty($destination->banner))
                        <div class="mb-2">
                            <img src="{{ $destination->banner }}"
                                 alt="Current destination banner"
                                 style="width:100%;max-width:420px;height:200px;object-fit:cover;border-radius:10px">
                        </div>
                    @endif

                    <input class="form-control"
                           type="file"
                           name="banner_file"
                           accept=".jpg,.jpeg,.png,.webp">

                    <small>
                        JPG, JPEG, PNG or WEBP · Max 5 MB
                        @if($isEdit && !empty($destination->banner))
                            · Leave empty to keep current banner
                        @endif
                    </small>
                </div>

            </div>
        </div>
    </div>

    <div class="crm-card mb-3">
        <div class="crm-card-head">
            <h3>SEO</h3>
        </div>

        <div class="p-3">
            <div class="row g-3">

                <div class="col-md-12">
                    <label class="form-label">SEO Title</label>
                    <input class="form-control"
                           name="seo_title"
                           maxlength="255"
                           value="{{ old('seo_title',$destination->seo_title ?? '') }}">
                </div>

                <div class="col-md-12">
                    <label class="form-label">SEO Keywords</label>
                    <input class="form-control"
                           name="seo_keyword"
                           maxlength="255"
                           value="{{ old('seo_keyword',$destination->seo_keyword ?? '') }}">
                </div>

                <div class="col-md-12">
                    <label class="form-label">SEO Description</label>
                    <textarea class="form-control"
                              rows="3"
                              maxlength="255"
                              name="seo_description">{{ old('seo_description',$destination->seo_description ?? '') }}</textarea>
                </div>

            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-4">
        <a href="{{ route('crm.destinations_manager') }}" class="btn btn-light">
            Cancel
        </a>

        <button class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk me-1"></i>
            {{ $isEdit ? 'Save Destination' : 'Create Destination' }}
        </button>
    </div>

</form>
@endsection
