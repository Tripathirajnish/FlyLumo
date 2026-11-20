@extends('crm.layout')
@section('content')
@php
$statusTone=fn($s)=>match($s){'VIP'=>'purple','Follow-up'=>'amber','At Risk'=>'red','Inactive','Do Not Contact'=>'slate',default=>'green'};
$tierTone=fn($t)=>match($t){'Platinum'=>'indigo','Gold'=>'amber','Silver'=>'slate',default=>'blue'};
@endphp

<div class="cm-page-head"><div><span class="cm-eyebrow">Customer intelligence</span><h1>Customers</h1><p>Customer value, booking history, service activity and preferences in one premium directory.</p></div></div>

<section class="cm-kpis">
<div class="cm-kpi blue"><span><i class="fa-solid fa-users"></i></span><div><small>Customers</small><strong>{{ number_format($stats['customers']) }}</strong></div></div>
<div class="cm-kpi cyan"><span><i class="fa-solid fa-plane"></i></span><div><small>Total Bookings</small><strong>{{ number_format($stats['bookings']) }}</strong></div></div>
<div class="cm-kpi green"><span><i class="fa-solid fa-wallet"></i></span><div><small>Lifetime Value</small><strong>${{ number_format($stats['lifetime_value'],0) }}</strong></div></div>
<div class="cm-kpi purple"><span><i class="fa-solid fa-repeat"></i></span><div><small>Repeat Customers</small><strong>{{ number_format($stats['repeat_customers']) }}</strong></div></div>
<div class="cm-kpi amber"><span><i class="fa-solid fa-crown"></i></span><div><small>VIP Customers</small><strong>{{ number_format($stats['vip_customers']) }}</strong></div></div>
</section>

<section class="crm-card cm-toolbar">
<form method="GET" class="cm-filters">
<div class="cm-search"><i class="fa-solid fa-magnifying-glass"></i><input name="q" value="{{ request('q') }}" placeholder="Search customer name, email or phone"></div>
<select class="form-select" name="sort"><option value="">Recent activity</option><option value="value" @selected(request('sort')==='value')>Highest lifetime value</option><option value="bookings" @selected(request('sort')==='bookings')>Most bookings</option></select>
<select class="form-select cm-page-size" name="per_page">@foreach([15,25,50,100] as $size)<option value="{{ $size }}" @selected((int)request('per_page',15)===$size)>{{ $size }} / page</option>@endforeach</select>
<button class="btn btn-primary"><i class="fa-solid fa-filter me-1"></i>Apply</button>
@if(request()->hasAny(['q','sort','per_page']))<a class="btn btn-light" href="{{ route('crm.customers') }}">Clear</a>@endif
</form><span class="soft blue">{{ number_format($customers->total()) }} customers</span>
</section>

<section class="crm-card cm-table-card"><div class="table-responsive"><table class="crm-table cm-table">
<thead><tr><th>Customer</th><th>Phone</th><th>Bookings</th><th>Lifetime Value</th><th>Avg Booking</th><th>Tier</th><th>Status</th><th>Assigned Agent</th><th>Last Booking</th><th>Next Action</th><th>Action</th></tr></thead>
<tbody>
@forelse($customers as $c)
<tr>
<td><div class="cm-customer-cell"><span class="cm-avatar">{{ strtoupper(substr($c->o_Name ?: 'C',0,1)) }}</span><div><a href="{{ route('crm.customer_detail',['email'=>$c->o_Email]) }}">{{ $c->o_Name ?: 'Guest' }}</a><small>{{ $c->o_Email ?: '—' }}</small></div>@if($c->crm_vip)<i class="fa-solid fa-crown cm-vip-crown"></i>@endif</div></td>
<td>{{ $c->o_Phone ?: '—' }}</td>
<td><span class="cm-count">{{ $c->bookings_count }}</span></td>
<td><strong>${{ number_format((float)$c->lifetime_value,2) }}</strong></td>
<td>${{ number_format((float)$c->average_booking_value,2) }}</td>
<td><span class="cm-badge {{ $tierTone($c->crm_tier) }}">{{ $c->crm_tier }}</span></td>
<td><span class="cm-badge {{ $statusTone($c->crm_status) }}">{{ $c->crm_status }}</span></td>
<td>{{ $c->crm_agent }}</td>
<td>{{ \Carbon\Carbon::parse($c->last_booking)->format('d M Y') }}<small>{{ \Carbon\Carbon::parse($c->last_booking)->diffForHumans() }}</small></td>
<td>@if($c->crm_next_action)<strong>{{ $c->crm_next_action }}</strong>@if($c->crm_next_action_at)<small>{{ \Carbon\Carbon::parse($c->crm_next_action_at)->format('d M, h:i A') }}</small>@endif @else <span class="text-muted">—</span>@endif</td>
<td><div style="display:flex;gap:6px;align-items:center">
<a class="cm-open" href="{{ route('crm.customer_detail',['email'=>$c->o_Email]) }}"><i class="fa-solid fa-arrow-right"></i></a>
<form method="POST" action="{{ route('crm.universal_archive') }}" onsubmit="return confirm('Move this customer to CRM Trash?')">@csrf
<input type="hidden" name="module" value="customer"><input type="hidden" name="record_key" value="{{ $c->o_Email }}"><input type="hidden" name="label" value="{{ $c->o_Name }}">
<button class="crm-ua-btn danger" title="Delete / Archive"><i class="fa-regular fa-trash-can"></i></button></form>
</div></td>
</tr>
@empty<tr><td colspan="11" class="empty">No customers found.</td></tr>@endforelse
</tbody></table></div>
<div class="cm-pagination"><span>Showing <b>{{ $customers->firstItem() ?? 0 }}</b> to <b>{{ $customers->lastItem() ?? 0 }}</b> of <b>{{ $customers->total() }}</b> customers</span><div>
@if($customers->onFirstPage())<span class="disabled"><i class="fa-solid fa-chevron-left"></i> Previous</span>@else<a href="{{ $customers->previousPageUrl() }}"><i class="fa-solid fa-chevron-left"></i> Previous</a>@endif
<span>Page {{ $customers->currentPage() }} of {{ $customers->lastPage() }}</span>
@if($customers->hasMorePages())<a href="{{ $customers->nextPageUrl() }}">Next <i class="fa-solid fa-chevron-right"></i></a>@else<span class="disabled">Next <i class="fa-solid fa-chevron-right"></i></span>@endif
</div></div></section>
@endsection
