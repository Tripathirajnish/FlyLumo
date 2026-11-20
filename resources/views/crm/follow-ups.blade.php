@extends('crm.layout')
@section('content')
<div class="crm-headline"><div><h1>Follow-ups</h1><p>Fresh bookings and internal follow-up activity.</p></div></div>
<div class="crm-grid4 mb-3">
 <div class="crm-metric"><div class="label">Fresh bookings</div><div class="value">{{ $fresh->count() }}</div></div>
 <div class="crm-metric"><div class="label">Recent notes</div><div class="value">{{ $items->total() }}</div></div>
</div>
<div class="crm-card mb-3"><div class="crm-card-head"><b>Fresh bookings needing attention</b></div><div class="table-responsive"><table class="crm-table"><thead><tr><th>Booking</th><th>Customer</th><th>Status</th><th>Created</th><th></th></tr></thead><tbody>@forelse($fresh as $b)<tr><td><a class="booking-link" href="{{ route('crm.booking_detail',$b->id) }}">#{{ fh_booking_ref($b->id) }}</a></td><td>{{ $b->o_Name }}<small class="d-block text-muted">{{ $b->o_Email }}</small></td><td><span class="soft green">{{ $b->status }}</span></td><td>{{ optional($b->created_at)->format('d M Y H:i') }}</td><td><a class="row-action" href="{{ route('crm.booking_detail',$b->id) }}"><i class="fa-solid fa-arrow-right"></i></a></td></tr>@empty<tr><td colspan="5" class="empty">No fresh bookings.</td></tr>@endforelse</tbody></table></div></div>
<div class="crm-card"><div class="crm-card-head"><b>Internal activity</b></div><div class="table-responsive"><table class="crm-table"><thead><tr><th>Booking</th><th>Customer</th><th>Remark</th><th>Created</th></tr></thead><tbody>@forelse($items as $r)<tr><td><a class="booking-link" href="{{ route('crm.booking_detail',$r->booking_id) }}">#{{ fh_booking_ref($r->booking_id) }}</a></td><td>{{ $r->o_Name ?: 'Guest' }}</td><td>{{ $r->remark }}</td><td>{{ \Carbon\Carbon::parse($r->created_at)->format('d M Y H:i') }}</td></tr>@empty<tr><td colspan="4" class="empty">No follow-up activity.</td></tr>@endforelse</tbody></table></div><div class="p-3">{{ $items->links() }}</div></div>
@endsection
