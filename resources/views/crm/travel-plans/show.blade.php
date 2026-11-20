@extends('crm.layout')

@section('content')
<style>
.tpd{padding:4px;color:#24364b}.tpd-head{display:flex;justify-content:space-between;gap:18px;align-items:flex-start;margin-bottom:20px}.tpd-head h1{margin:3px 0 5px;font-size:28px;font-weight:800;color:#092d50}.tpd-kicker{font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.12em;color:#d92332}.tpd-sub{color:#718096}.tpd-back{background:#eef3f7;color:#092d50;text-decoration:none;padding:10px 14px;border-radius:9px;font-weight:700}.tpd-grid{display:grid;grid-template-columns:1.25fr .75fr;gap:16px;margin-bottom:16px}.tpd-card{background:#fff;border:1px solid #e1e8ef;border-radius:14px;padding:18px}.tpd-card h2{font-size:17px;margin:0 0 15px;color:#092d50}.tpd-info{display:grid;grid-template-columns:1fr 1fr;gap:14px}.tpd-label{font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:#8090a2;font-weight:800;margin-bottom:4px}.tpd-value{font-weight:700;color:#24364b;word-break:break-word}.tpd-badges{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}.tpd-badge{display:inline-block;border-radius:999px;padding:6px 10px;font-size:12px;font-weight:800}.tpd-warning{background:#fff3cd;color:#8a6500}.tpd-good{background:#dcfce7;color:#166534}.tpd-neutral{background:#e8eef5;color:#34475c}.tpd-trips{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}.tpd-trip{background:#fff;border:1px solid #e1e8ef;border-radius:14px;padding:17px;position:relative}.tpd-trip-no{font-size:11px;color:#d92332;font-weight:900;text-transform:uppercase;letter-spacing:.08em}.tpd-trip h3{margin:6px 0 13px;color:#092d50;font-size:17px}.tpd-trip-row{margin:9px 0}.tpd-trip-row span{display:block;font-size:11px;color:#8090a2;text-transform:uppercase;font-weight:800}.tpd-trip-row strong{font-size:13px;color:#334155}.tpd-activity{margin-top:16px}.tpd-event{border-left:3px solid #092d50;padding:2px 0 14px 14px;margin-left:4px}.tpd-event:last-child{padding-bottom:0}.tpd-event strong{display:block;color:#092d50}.tpd-event small{color:#8090a2}.tpd-event p{margin:4px 0 0;color:#5f6f80}.tpd-money{font-size:27px;font-weight:900;color:#092d50}.tpd-note{background:#f7f9fb;border-radius:10px;padding:12px;color:#617184;font-size:13px;margin-top:12px}
@media(max-width:1000px){.tpd-grid{grid-template-columns:1fr}.tpd-trips{grid-template-columns:1fr 1fr}}
@media(max-width:650px){.tpd-head{display:block}.tpd-back{display:inline-block;margin-top:12px}.tpd-info,.tpd-trips{grid-template-columns:1fr}}
</style>

@php
    $planStatusClass = in_array($plan->status, ['Active','Completed']) ? 'tpd-good' : ($plan->status === 'Pending Payment' ? 'tpd-warning' : 'tpd-neutral');
    $paymentClass = $plan->payment_status === 'Paid' ? 'tpd-good' : ($plan->payment_status === 'Unpaid' ? 'tpd-warning' : 'tpd-neutral');
@endphp

<div class="tpd">
    <div class="tpd-head">
        <div>
            <div class="tpd-kicker">Travel Plan</div>
            <h1>{{ $plan->reference }}</h1>
            <div class="tpd-sub">{{ $plan->customer_name }} · {{ $plan->customer_email }}</div>
            <div class="tpd-badges">
                <span class="tpd-badge {{ $planStatusClass }}">{{ $plan->status }}</span>
                <span class="tpd-badge {{ $paymentClass }}">{{ $plan->payment_status }}</span>
            </div>
        </div>
        <a class="tpd-back" href="{{ route('crm.travel_plans') }}">← All Travel Plans</a>
    </div>

    <div class="tpd-grid">
        <div class="tpd-card">
            <h2>Customer & Plan Details</h2>
            <div class="tpd-info">
                <div><div class="tpd-label">Customer</div><div class="tpd-value">{{ $plan->customer_name }}</div></div>
                <div><div class="tpd-label">Email</div><div class="tpd-value">{{ $plan->customer_email }}</div></div>
                <div><div class="tpd-label">Phone</div><div class="tpd-value">{{ $plan->customer_phone ?: '—' }}</div></div>
                <div><div class="tpd-label">Offer</div><div class="tpd-value">{{ $plan->offer_title ?: 'Travel Offer' }}</div></div>
                <div><div class="tpd-label">Offer Reference</div><div class="tpd-value">{{ $plan->offer_reference ?: '—' }}</div></div>
                <div><div class="tpd-label">Offer Version</div><div class="tpd-value">{{ $plan->offer_version_number ?: '—' }}</div></div>
                <div><div class="tpd-label">Plan Start</div><div class="tpd-value">{{ $plan->starts_on ? \Carbon\Carbon::parse($plan->starts_on)->format('M d, Y') : '—' }}</div></div>
                <div><div class="tpd-label">Plan Expiry</div><div class="tpd-value">{{ $plan->expires_on ? \Carbon\Carbon::parse($plan->expires_on)->format('M d, Y') : '—' }}</div></div>
                <div><div class="tpd-label">Terms Version</div><div class="tpd-value">{{ $plan->terms_version ?: '—' }}</div></div>
                <div><div class="tpd-label">Agreement Accepted</div><div class="tpd-value">{{ $plan->agreement_accepted_at ? \Carbon\Carbon::parse($plan->agreement_accepted_at)->format('M d, Y h:i A') : '—' }}</div></div>
            </div>
        </div>

        <div class="tpd-card">
            <h2>Financial Summary</h2>
            <div class="tpd-label">Plan Amount</div>
            <div class="tpd-money">{{ $plan->currency }} {{ number_format((float)$plan->sale_amount, 2) }}</div>
            <div class="tpd-info" style="margin-top:16px">
                <div><div class="tpd-label">Amount Paid</div><div class="tpd-value">{{ $plan->currency }} {{ number_format((float)$plan->amount_paid, 2) }}</div></div>
                <div><div class="tpd-label">Recorded Cost</div><div class="tpd-value">{{ $plan->currency }} {{ number_format((float)$plan->total_cost, 2) }}</div></div>
                <div><div class="tpd-label">Payment Status</div><div class="tpd-value">{{ $plan->payment_status }}</div></div>
                <div><div class="tpd-label">Payment Reference</div><div class="tpd-value">{{ $plan->payment_reference ?: '—' }}</div></div>
            </div>
            <div class="tpd-note">Payment is handled separately. This page does not charge the customer.</div>
        </div>
    </div>

    <div class="tpd-card">
        <h2>Journey Entitlements</h2>
        <div class="tpd-trips">
            @forelse($trips as $trip)
                @php
                    $airlines = $trip->eligible_airlines;
                    $decodedAirlines = json_decode((string)$airlines, true);
                    if (is_array($decodedAirlines)) $airlines = implode(', ', $decodedAirlines);
                @endphp
                <div class="tpd-trip">
                    <div class="tpd-trip-no">{{ $trip->trip_type }} #{{ $trip->sequence }}</div>
                    <h3>{{ $trip->title ?: $trip->trip_type.' Round Trip' }}</h3>
                    <div class="tpd-trip-row"><span>Status</span><strong>{{ $trip->status }}</strong></div>
                    <div class="tpd-trip-row"><span>Cabin</span><strong>{{ $trip->cabin_class ?: '—' }}</strong></div>
                    <div class="tpd-trip-row"><span>Eligible Airlines</span><strong>{{ $airlines ?: 'Any eligible available airline' }}</strong></div>
                    <div class="tpd-trip-row"><span>Advance Booking</span><strong>{{ $trip->advance_booking_months !== null ? $trip->advance_booking_months.' months' : '—' }}</strong></div>
                    <div class="tpd-trip-row"><span>Route</span><strong>{{ $trip->origin && $trip->destination ? $trip->origin.' → '.$trip->destination : 'Travel details to be provided later' }}</strong></div>
                    <div class="tpd-trip-row"><span>Travel Dates</span><strong>{{ $trip->departure_date ? \Carbon\Carbon::parse($trip->departure_date)->format('M d, Y') : 'Not provided' }}{{ $trip->return_date ? ' – '.\Carbon\Carbon::parse($trip->return_date)->format('M d, Y') : '' }}</strong></div>
                    <div class="tpd-trip-row"><span>PNR / Booking</span><strong>{{ $trip->pnr ?: 'Not booked yet' }}</strong></div>
                </div>
            @empty
                <div class="tpd-note">No journey entitlements found for this plan.</div>
            @endforelse
        </div>
    </div>

    <div class="tpd-card tpd-activity">
        <h2>Activity History</h2>
        @forelse($activity as $event)
            <div class="tpd-event">
                <strong>{{ $event->title }}</strong>
                <small>{{ $event->created_at ? \Carbon\Carbon::parse($event->created_at)->format('M d, Y h:i A') : '' }}</small>
                @if($event->details)<p>{{ $event->details }}</p>@endif
            </div>
        @empty
            <div class="tpd-note">No activity recorded yet.</div>
        @endforelse
    </div>
</div>
@endsection
