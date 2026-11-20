@extends('crm.layout')

@section('content')
<div class="crm-page-head">
    <div>
        <a href="{{ route('crm.cms') }}" class="crm-back-link">
            <i class="fa-solid fa-arrow-left"></i> Website CMS
        </a>
        <h1 class="title mt-2">Destinations</h1>
        <div class="sub">Manage destination landing pages, images, visibility and SEO.</div>
    </div>

    <div>
        <a href="{{ route('crm.destination_create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i>Add Destination
        </a>
    </div>
</div>

<div class="crm-card mb-3">
    <div class="p-3">
        <form method="GET" action="{{ route('crm.destinations_manager') }}" class="d-flex gap-2">
            <input class="form-control"
                   name="q"
                   value="{{ request('q') }}"
                   placeholder="Search destination...">
            <button class="btn btn-primary">Search</button>
            @if(request('q'))
                <a href="{{ route('crm.destinations_manager') }}" class="btn btn-light">Clear</a>
            @endif
        </form>
    </div>
</div>

<div class="crm-card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
            <tr>
                <th>Image</th>
                <th>Destination</th>
                <th>URL</th>
                <th>Sequence</th>
                <th>Status</th>
                <th style="width:190px">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($destinations as $destination)
                <tr>
                    <td>
                        @if($destination->image)
                            <img src="{{ $destination->image }}"
                                 alt="{{ $destination->title }}"
                                 style="width:90px;height:58px;object-fit:cover;border-radius:8px">
                        @else
                            —
                        @endif
                    </td>

                    <td>
                        <strong>{{ $destination->title }}</strong>
                    </td>

                    <td>
                        <a href="{{ url('/destination/'.$destination->slug) }}"
                           target="_blank">
                            /destination/{{ $destination->slug }}
                        </a>
                    </td>

                    <td>{{ $destination->sequence ?? '—' }}</td>

                    <td>
                        @if((string)$destination->is_visible === '1')
                            <span class="badge bg-success">Visible</span>
                        @else
                            <span class="badge bg-secondary">Hidden</span>
                        @endif
                    </td>

                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('crm.destination_edit',$destination->id) }}"
                               class="btn btn-sm btn-primary">
                                Edit
                            </a>

                            <form method="POST"
                                  action="{{ route('crm.destination_delete',$destination->id) }}"
                                  onsubmit="return confirm('Delete this destination?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-light">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center p-4">No destinations found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
