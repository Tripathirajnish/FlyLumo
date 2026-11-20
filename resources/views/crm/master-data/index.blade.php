@extends('crm.layout')

@section('content')
<style>
.tmd-page{padding-bottom:24px}
.tmd-head{display:flex;justify-content:space-between;align-items:flex-end;gap:18px;margin-bottom:15px}
.tmd-head h1{font-size:34px;line-height:1.05;margin:6px 0 0;color:#082d59;letter-spacing:-.03em}
.tmd-head p{margin:7px 0 0;color:#718299;font-size:14px}
.tmd-eyebrow{font-size:10px;text-transform:uppercase;font-weight:900;letter-spacing:.09em;color:#1f5fda}
.tmd-safety{padding:13px 16px;border:1px solid #cfe0fa;background:#f3f8ff;border-radius:14px;margin-bottom:14px;color:#264e7d;font-size:12px}
.tmd-stats{display:grid;grid-template-columns:repeat(5,1fr);gap:10px;margin-bottom:14px}
.tmd-stat{display:block;text-decoration:none;padding:15px;background:#fff;border:1px solid #e0e7ef;border-radius:14px}
.tmd-stat.active{border-color:#2459df;box-shadow:0 0 0 2px rgba(36,89,223,.08)}
.tmd-stat small{display:block;font-size:9px;text-transform:uppercase;color:#7c8da1;font-weight:900}
.tmd-stat strong{display:block;font-size:22px;margin-top:8px;color:#0f3057}
.tmd-filter{padding:14px;background:#fff;border:1px solid #dfe7ef;border-radius:16px;margin-bottom:12px;display:flex;gap:10px}
.tmd-filter .form-control{height:46px;border-radius:11px}
.tmd-filter .form-select{height:46px;border-radius:11px;max-width:150px}
.tmd-filter .btn{height:46px;border-radius:11px;display:inline-flex;align-items:center;justify-content:center;white-space:nowrap;line-height:1}
.tmd-card{background:#fff;border:1px solid #dfe7ef;border-radius:16px;overflow:hidden}
.tmd-scroll{overflow-x:auto}
.tmd-table{margin:0;min-width:900px}
.tmd-table th{font-size:9px!important;text-transform:uppercase;background:#f4f7fb!important;color:#526981!important;padding:13px 10px!important;white-space:nowrap}
.tmd-table td{font-size:11px!important;padding:12px 10px!important;vertical-align:middle}
.tmd-code{font-weight:850;color:#123e72}
.tmd-logo{width:42px;height:28px;object-fit:contain;border:1px solid #e5eaf0;border-radius:6px;background:#fff}
.tmd-pill{display:inline-flex;padding:5px 8px;border-radius:999px;font-size:8px;font-weight:850;background:#eef2f6;color:#5d6e82}
.tmd-pill.green{background:#e8f8ef;color:#16814f}
.tmd-footer{display:flex;justify-content:space-between;align-items:center;padding:12px 14px;border-top:1px solid #e8edf3;font-size:11px}
.tmd-footer .pagination{margin:0}
.tmd-footer > div nav .d-none.flex-sm-fill.d-sm-flex > div:first-child{display:none}
.tmd-footer > div nav .d-none.flex-sm-fill.d-sm-flex{justify-content:flex-end!important}
@media(max-width:800px){.tmd-stats{grid-template-columns:repeat(2,1fr)}.tmd-head{flex-direction:column;align-items:stretch}.tmd-filter{flex-wrap:wrap}.tmd-filter .form-control{width:100%}}
</style>

<div class="tmd-page">
    <div class="tmd-head">
        <div>
            <span class="tmd-eyebrow">Travel operations reference</span>
            <h1>Travel Master Data</h1>
            <p>Manage country, city, airport, airline and booking add-on reference data used across FaresHunt.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            @if($canManage)
                <span class="tmd-pill green"><i class="fa-solid fa-shield-halved me-1"></i>Management enabled</span>
                <a class="btn btn-primary"
                   href="{{ route('crm.master_data_create',['section'=>$section]) }}">
                    <i class="fa-solid fa-plus me-1"></i>Add {{ ['countries'=>'Country','cities'=>'City','airports'=>'Airport','airlines'=>'Airline','booking_addons'=>'Booking Add-on'][$section] ?? 'Record' }}
                </a>
            @else
                <span class="tmd-pill"><i class="fa-solid fa-eye me-1"></i>Read only</span>
            @endif
        </div>
    </div>

    <div class="tmd-safety">
        <b>Protected master data:</b>
        existing IDs and historical booking snapshots are preserved. Destructive actions will be dependency-checked before they are enabled.
    </div>

    <div class="tmd-stats">
        @foreach([
            'countries' => ['Countries',$counts['countries']],
            'cities' => ['Cities',$counts['cities']],
            'airports' => ['Airports',$counts['airports']],
            'airlines' => ['Airlines',$counts['airlines']],
            'booking_addons' => ['Booking Add-ons',$counts['booking_addons']],
        ] as $key => $stat)
            <a class="tmd-stat {{ $section===$key ? 'active' : '' }}"
               href="{{ route('crm.master_data',['section'=>$key]) }}">
                <small>{{ $stat[0] }}</small>
                <strong>{{ number_format($stat[1]) }}</strong>
            </a>
        @endforeach
    </div>

    <form class="tmd-filter" method="GET" action="{{ route('crm.master_data') }}">
        <input type="hidden" name="section" value="{{ $section }}">
        <input class="form-control" name="q" value="{{ request('q') }}"
               placeholder="{{ $section === 'booking_addons' ? 'Search add-ons by name, key or ID' : 'Search '.$section.' by name, code or ID' }}">
        <select class="form-select" name="per_page">
            @foreach([15,25,50,100] as $n)
                <option value="{{ $n }}" @selected((int)request('per_page',25)===$n)>{{ $n }} / page</option>
            @endforeach
        </select>
        <button class="btn btn-primary"><i class="fa-solid fa-magnifying-glass me-2"></i>Search</button>
        <a class="btn btn-light" href="{{ route('crm.master_data',['section'=>$section]) }}">Reset</a>
    </form>

    <div class="tmd-card">
        <div class="tmd-scroll">
            <table class="table tmd-table">
                <thead>
                @if($section==='countries')
                    <tr><th>ID</th><th>Country</th><th>Code</th><th>Updated</th>@if($canManage)<th>Actions</th>@endif</tr>
                @elseif($section==='cities')
                    <tr><th>ID</th><th>City</th><th>Country</th><th>Country Code</th><th>Updated</th>@if($canManage)<th>Actions</th>@endif</tr>
                @elseif($section==='airports')
                    <tr><th>ID</th><th>IATA / Code</th><th>Airport</th><th>City</th><th>Country</th><th>Updated</th>@if($canManage)<th>Actions</th>@endif</tr>
                @elseif($section==='airlines')
                    <tr><th>ID</th><th>Logo</th><th>Code</th><th>Airline</th><th>Popular</th><th>Flight Type</th><th>Updated</th>@if($canManage)<th>Actions</th>@endif</tr>
                @elseif($section==='booking_addons')
                    <tr><th>ID</th><th>Key</th><th>Add-on</th><th>Price</th><th>Status</th><th>Order</th><th>Updated</th>@if($canManage)<th>Actions</th>@endif</tr>
                @endif
                </thead>
                <tbody>
                @forelse($records as $r)
                    @if($section==='countries')
                        <tr>
                            <td>#{{ $r->id }}</td>
                            <td>{{ $r->name ?: '—' }}</td>
                            <td><span class="tmd-code">{{ $r->code ?: '—' }}</span></td>
                            <td>{{ $r->updated_at ?: '—' }}</td>
                            @if($canManage)
                                <td>
                                    <div class="d-flex gap-1 flex-nowrap">
                                        <a class="btn btn-sm btn-outline-primary"
                                           href="{{ route('crm.master_data_edit',['section'=>$section,'id'=>$r->id]) }}">
                                            <i class="fa-solid fa-pen"></i> Edit
                                        </a>
                                        <form method="POST"
                                              action="{{ route('crm.master_data_delete',['section'=>$section,'id'=>$r->id]) }}"
                                              onsubmit="return confirm('Delete this record? This action will be blocked if the record is in use.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fa-solid fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @elseif($section==='cities')
                        <tr>
                            <td>#{{ $r->id }}</td>
                            <td>{{ $r->name ?: '—' }}</td>
                            <td>{{ $r->country_name ?: 'Unknown' }}</td>
                            <td><span class="tmd-code">{{ $r->country_code ?: '—' }}</span></td>
                            <td>{{ $r->updated_at ?: '—' }}</td>
                            @if($canManage)
                                <td>
                                    <div class="d-flex gap-1 flex-nowrap">
                                        <a class="btn btn-sm btn-outline-primary"
                                           href="{{ route('crm.master_data_edit',['section'=>$section,'id'=>$r->id]) }}">
                                            <i class="fa-solid fa-pen"></i> Edit
                                        </a>
                                        <form method="POST"
                                              action="{{ route('crm.master_data_delete',['section'=>$section,'id'=>$r->id]) }}"
                                              onsubmit="return confirm('Delete this record? This action will be blocked if the record is in use.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fa-solid fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @elseif($section==='airports')
                        <tr>
                            <td>#{{ $r->id }}</td>
                            <td><span class="tmd-code">{{ $r->code ?: '—' }}</span></td>
                            <td>{{ $r->name ?: '—' }}</td>
                            <td>{{ $r->city_name ?: 'Unknown' }}</td>
                            <td>{{ $r->country_name ?: 'Unknown' }} @if($r->country_code)({{ $r->country_code }})@endif</td>
                            <td>{{ $r->updated_at ?: '—' }}</td>
                            @if($canManage)
                                <td>
                                    <div class="d-flex gap-1 flex-nowrap">
                                        <a class="btn btn-sm btn-outline-primary"
                                           href="{{ route('crm.master_data_edit',['section'=>$section,'id'=>$r->id]) }}">
                                            <i class="fa-solid fa-pen"></i> Edit
                                        </a>
                                        <form method="POST"
                                              action="{{ route('crm.master_data_delete',['section'=>$section,'id'=>$r->id]) }}"
                                              onsubmit="return confirm('Delete this record? This action will be blocked if the record is in use.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fa-solid fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @elseif($section==='airlines')
                        <tr>
                            <td>#{{ $r->id }}</td>
                            <td>
                                @if($r->logo)
                                    <img class="tmd-logo" src="{{ $r->logo }}" alt="{{ $r->name }}">
                                @else
                                    <span class="tmd-pill">No logo</span>
                                @endif
                            </td>
                            <td><span class="tmd-code">{{ $r->code ?: '—' }}</span></td>
                            <td>{{ $r->name ?: '—' }}</td>
                            <td>{{ $r->popular==='1' ? 'Yes' : 'No' }}</td>
                            <td>{{ $r->flight_type ?: '—' }}</td>
                            <td>{{ $r->updated_at ?: '—' }}</td>
                            @if($canManage)
                                <td>
                                    <div class="d-flex gap-1 flex-nowrap">
                                        <a class="btn btn-sm btn-outline-primary"
                                           href="{{ route('crm.master_data_edit',['section'=>$section,'id'=>$r->id]) }}">
                                            <i class="fa-solid fa-pen"></i> Edit
                                        </a>
                                        <form method="POST"
                                              action="{{ route('crm.master_data_delete',['section'=>$section,'id'=>$r->id]) }}"
                                              onsubmit="return confirm('Delete this record? This action will be blocked if the record is in use.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fa-solid fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @elseif($section==='booking_addons')
                        <tr>
                            <td>#{{ $r->id }}</td>
                            <td><span class="tmd-code">{{ $r->key }}</span></td>
                            <td>
                                <strong>{{ $r->name }}</strong>
                                @if($r->description)
                                    <div class="text-muted mt-1">{{ $r->description }}</div>
                                @endif
                            </td>
                            <td>${{ number_format((float)$r->price, 2) }}</td>
                            <td>
                                @if((int)$r->is_active === 1)
                                    <span class="tmd-pill green">Active</span>
                                @else
                                    <span class="tmd-pill">Inactive</span>
                                @endif
                            </td>
                            <td>{{ $r->display_order }}</td>
                            <td>{{ $r->updated_at ?: '—' }}</td>
                            @if($canManage)
                                <td>
                                    <div class="d-flex gap-1 flex-nowrap">
                                        <a class="btn btn-sm btn-outline-primary"
                                           href="{{ route('crm.master_data_edit',['section'=>$section,'id'=>$r->id]) }}">
                                            <i class="fa-solid fa-pen"></i> Edit
                                        </a>
                                        <form method="POST"
                                              action="{{ route('crm.master_data_delete',['section'=>$section,'id'=>$r->id]) }}"
                                              onsubmit="return confirm('Delete this add-on? If it exists in historical booking data, deletion will be blocked. Disable it instead.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fa-solid fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endif
                @empty
                    <tr><td colspan="8" class="text-center py-4 text-muted">No {{ $section }} found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="tmd-footer">
            <span>Showing {{ $records->firstItem() ?? 0 }}–{{ $records->lastItem() ?? 0 }} of {{ $records->total() }}</span>
            <div>{{ $records->links('pagination::bootstrap-5') }}</div>
        </div>
    </div>
</div>
@endsection
