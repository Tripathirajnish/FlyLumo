@extends('crm.layout')

@section('content')
<style>
.to-head{display:flex;justify-content:space-between;gap:18px;align-items:end;margin-bottom:18px}
.to-head h1{margin:0;font-size:30px;letter-spacing:-.035em;color:#0a2448}
.to-head p{margin:6px 0 0;color:#70819a}
.to-actions{display:flex;gap:9px;flex-wrap:wrap}
.to-stats{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin-bottom:16px}
.to-stat{display:block;padding:16px;border:1px solid #dce5f0;border-radius:16px;background:#fff;text-decoration:none;color:#183654;box-shadow:0 8px 24px rgba(18,48,85,.045)}
.to-stat:hover{transform:translateY(-2px);border-color:#c7d8ea}
.to-stat.active{border-color:#9dbcf0;background:#f5f9ff}
.to-stat b{display:block;font-size:21px;color:#0a3d87}
.to-stat small{display:block;color:#8190a4;margin-top:3px}
.to-toolbar{display:flex;gap:10px;align-items:center;justify-content:space-between;padding:13px 14px;border:1px solid #e0e7f0;border-radius:15px;background:#fff;margin-bottom:14px}
.to-toolbar form{display:flex;gap:8px;flex:1;flex-wrap:wrap}
.to-toolbar input{max-width:360px}
.to-toolbar select{max-width:190px}
.to-table-wrap{overflow:auto;border:1px solid #dfe7f0;border-radius:16px;background:#fff}
.to-table{min-width:1050px}
.to-price{font-weight:800;color:#0a3d87}
.to-status{display:inline-flex;padding:5px 9px;border-radius:999px;background:#eef4fb;color:#47617f;font-size:10px;font-weight:700}
.to-status.active{background:#e9f8f0;color:#16845b}
.to-status.draft{background:#fff6df;color:#9a6a00}
.to-row-actions{display:flex;gap:6px;justify-content:flex-end}
.to-row-actions a{display:inline-grid;place-items:center;width:32px;height:32px;border:1px solid #dce5f0;border-radius:9px;text-decoration:none;color:#315273;background:#fff}
@media(max-width:1100px){.to-stats{grid-template-columns:repeat(2,1fr)}}
@media(max-width:700px){.to-head{align-items:flex-start;flex-direction:column}.to-stats{grid-template-columns:1fr}.to-toolbar,.to-toolbar form{align-items:stretch;flex-direction:column}.to-toolbar input,.to-toolbar select{max-width:none}}
</style>

<div class="to-head">
    <div>
        <span class="eyebrow">Travel products</span>
        <h1>Travel Offers</h1>
        <p>Create reusable promotional offers, preview customer-ready deals and manage their lifecycle.</p>
    </div>
    <div class="to-actions">
        <a class="btn btn-danger" href="{{ route('crm.travel_offers.create') }}">
            <i class="fa-solid fa-plus me-1"></i>New Offer
        </a>
    </div>
</div>

<div class="to-stats">
    @foreach($statuses as $item)
        <a class="to-stat {{ $activeStatus === $item ? 'active' : '' }}"
           href="{{ route('crm.travel_offers', ['status'=>$item]) }}">
            <b>{{ number_format($counts[$item] ?? 0) }}</b>
            <small>{{ $item }}</small>
        </a>
    @endforeach
</div>

<div class="to-toolbar">
    <form method="GET">
        <input class="form-control"
               name="q"
               value="{{ request('q') }}"
               placeholder="Search reference, title or description">

        <select class="form-control" name="type">
            <option value="">All offer types</option>
            @foreach($types as $key=>$label)
                <option value="{{ $key }}" {{ $activeType === $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        @if($activeStatus)
            <input type="hidden" name="status" value="{{ $activeStatus }}">
        @endif

        <button class="btn btn-primary">
            <i class="fa-solid fa-magnifying-glass me-1"></i>Search
        </button>

        @if(request('q') || $activeType || $activeStatus)
            <a class="btn btn-light" href="{{ route('crm.travel_offers') }}">Clear</a>
        @endif
    </form>

    <span class="soft blue">{{ number_format($offers->total()) }} offers</span>
</div>

<div class="to-table-wrap">
<table class="crm-table to-table">
    <thead>
        <tr>
            <th>Reference</th>
            <th>Offer</th>
            <th>Type</th>
            <th>Trips</th>
            <th>Price</th>
            <th>Validity</th>
            <th>Slots</th>
            <th>Status</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    @forelse($offers as $offer)
        <tr>
            <td><b>{{ $offer->reference }}</b></td>

            <td>
                <b>{{ $offer->title }}</b>
                @if($offer->cabin_class)
                    <br><small>{{ $offer->cabin_class }}</small>
                @endif
            </td>

            <td>{{ $types[$offer->offer_type] ?? ucfirst($offer->offer_type) }}</td>

            <td>
                {{ (int)$offer->domestic_trips }} Domestic
                <br>
                <small>{{ (int)$offer->international_trips }} International</small>
            </td>

            <td class="to-price">
                {{ $offer->currency }} {{ number_format((float)$offer->price,2) }}
            </td>

            <td>
                {{ $offer->valid_from ?: '—' }}
                @if($offer->valid_until)
                    <br><small>to {{ $offer->valid_until }}</small>
                @endif
            </td>

            <td>{{ $offer->slot_limit ? number_format($offer->slot_limit) : 'Open' }}</td>

            <td>
                <span class="to-status {{ strtolower($offer->status) }}">
                    {{ $offer->status }}
                </span>
            </td>

            <td>
                <div class="to-row-actions">
                    <a href="{{ route('crm.travel_offers.preview',$offer->id) }}"
                       title="Preview">
                        <i class="fa-solid fa-eye"></i>
                    </a>

                    <a href="{{ route('crm.travel_offers.edit',$offer->id) }}"
                       title="Edit">
                        <i class="fa-solid fa-pen"></i>
                    </a>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="9" class="empty">
                No travel offers yet. Create your first reusable promotional offer.
            </td>
        </tr>
    @endforelse
    </tbody>
</table>
</div>

<div class="mt-3">{{ $offers->links() }}</div>
@endsection
