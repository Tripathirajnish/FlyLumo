@extends('crm.layout')
@section('content')
<div class="crm-headline">
 <div><h1>Authorization & Verification</h1><p>Booking #{{ fh_booking_ref($booking->id) }} · {{ $booking->o_Name }} · {{ $booking->o_Email }}</p></div>
 <a class="btn btn-light" href="{{ route('crm.booking_detail',$booking->id) }}">Back to booking</a>
</div>

@if(session('crm_success'))<div class="alert alert-success">{{ session('crm_success') }}</div>@endif
@if(session('crm_error'))<div class="alert alert-danger">{{ session('crm_error') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<div class="crm-card" style="padding:22px;margin-bottom:20px">
 <h3>Create & email secure authorization</h3>
 <p class="text-muted">A booking-specific itinerary and passenger snapshot is frozen when you create the request. The secure signing link is emailed directly to <b>{{ $booking->o_Email ?: 'the passenger email' }}</b>.</p>
 <form method="POST" action="{{ route('crm.authorization_create',$booking->id) }}" style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:12px;align-items:end">
 @csrf
 <label>Authorized amount<input class="form-control" type="number" step="0.01" name="amount" value="{{ $booking->o_FullPrice }}" required></label>
 <label>Currency<input class="form-control" name="currency" value="USD" required></label>
 <label>Expires in days<input class="form-control" type="number" name="expires_days" value="7" min="1" max="30" required></label>
 <button class="btn btn-primary">Create & Email Link</button>
 </form>
 <div style="font-size:11px;color:#7a899c;margin-top:10px">Full card number and CVV are not collected. Passenger enters card brand, last 4 and expiry only.</div>
</div>

<div class="crm-card">
<table class="crm-table">
<thead><tr><th>Created</th><th>Amount</th><th>Status</th><th>Email</th><th>Viewed</th><th>Completed</th><th>Evidence</th><th>Actions</th></tr></thead>
<tbody>
@forelse($items as $a)
<tr>
<td>{{ $a->created_at }}</td>
<td>{{ $a->currency }} {{ number_format($a->authorized_amount,2) }}</td>
<td><b>{{ strtoupper($a->status) }}</b></td>
<td>
 <div>{{ $a->recipient_email ?: $booking->o_Email }}</div>
 <small class="text-muted">{{ $a->email_status ? strtoupper($a->email_status) : '—' }}{{ $a->emailed_at ? ' · '.$a->emailed_at : '' }}</small>
</td>
<td>{{ $a->viewed_at ?: '—' }}</td>
<td>{{ $a->completed_at ?: '—' }}</td>
<td>@if($a->status==='completed')<a class="btn btn-sm btn-light" href="{{ route('crm.authorization_evidence',$a->id) }}">View / Print</a>@else—@endif</td>
<td>
 @if(!$a->revoked_at && $a->status!=='completed')
 <div style="display:flex;gap:6px;flex-wrap:wrap">
  @if(!empty($a->token_encrypted))
  <form method="POST" action="{{ route('crm.authorization_resend',$a->id) }}">@csrf<button class="btn btn-sm btn-primary">Resend Email</button></form>
  @endif
  <form method="POST" action="{{ route('crm.authorization_revoke',$a->id) }}" onsubmit="return confirm('Revoke this authorization link?')">@csrf<button class="btn btn-sm btn-light">Revoke</button></form>
 </div>
 @endif
</td>
</tr>
@empty
<tr><td colspan="8" class="empty">No authorization requests yet.</td></tr>
@endforelse
</tbody>
</table>
</div>
@endsection
