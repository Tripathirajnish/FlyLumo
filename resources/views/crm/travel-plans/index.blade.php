@extends('crm.layout')

@section('content')
<style>
.tp-wrap{padding:4px}
.tp-head{display:flex;justify-content:space-between;gap:20px;align-items:flex-start;margin-bottom:22px}
.tp-head h1{margin:3px 0 5px;font-size:28px;font-weight:800;color:#092d50}
.tp-head p{margin:0;color:#6b7b8d}
.tp-kicker{font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.12em;color:#d92332}
.tp-stats{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:18px}
.tp-stat{background:#fff;border:1px solid #e2e8ef;border-radius:12px;padding:15px}
.tp-stat b{display:block;font-size:23px;color:#092d50}
.tp-stat span{font-size:12px;color:#718096}
.tp-filter{display:flex;flex-wrap:wrap;gap:10px;background:#fff;border:1px solid #e2e8ef;border-radius:12px;padding:14px;margin-bottom:18px}
.tp-filter input,.tp-filter select{border:1px solid #d8e0e8;border-radius:8px;padding:9px 11px;background:#fff}
.tp-filter input{min-width:260px;flex:1}
.tp-btn{border:0;border-radius:8px;padding:9px 15px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;justify-content:center}
.tp-btn-primary{background:#092d50;color:#fff}
.tp-btn-light{background:#eef3f7;color:#092d50}
.tp-card{background:#fff;border:1px solid #e2e8ef;border-radius:14px;overflow:hidden}
.tp-table{width:100%;border-collapse:collapse}
.tp-table th{background:#f7f9fb;padding:12px;text-align:left;font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:#64748b}
.tp-table td{padding:14px 12px;border-top:1px solid #edf1f5;vertical-align:middle}
.tp-ref{font-weight:800;color:#092d50}
.tp-customer{font-weight:700;color:#1f2937}
.tp-small{font-size:12px;color:#718096;margin-top:3px}
.tp-badge{display:inline-block;border-radius:999px;padding:5px 9px;font-size:11px;font-weight:800}
.tp-pending,.tp-unpaid{background:#fff3cd;color:#8a6500}
.tp-active,.tp-paid{background:#dcfce7;color:#166534}
.tp-completed{background:#dbeafe;color:#1e40af}
.tp-cancelled,.tp-expired,.tp-refunded{background:#fee2e2;color:#991b1b}
.tp-partially-paid{background:#e0f2fe;color:#075985}
.tp-empty{text-align:center;padding:45px 20px;color:#718096}
@media(max-width:1000px){.tp-stats{grid-template-columns:repeat(2,1fr)}.tp-card{overflow:auto}.tp-table{min-width:900px}}
@media(max-width:600px){.tp-stats{grid-template-columns:1fr 1fr}.tp-head{display:block}}
</style>

<div class="tp-wrap">
    <div class="tp-head">
        <div>
            <div class="tp-kicker">Travel Products</div>
            <h1>Travel Plans</h1>
            <p>Manage customer travel plans, payment status and journey entitlements.</p>
        </div>
    </div>

    <div class="tp-stats">
        <div class="tp-stat"><b>{{ $counts['all'] }}</b><span>All Plans</span></div>
        <div class="tp-stat"><b>{{ $counts['pending'] }}</b><span>Pending Payment</span></div>
        <div class="tp-stat"><b>{{ $counts['active'] }}</b><span>Active Plans</span></div>
        <div class="tp-stat"><b>{{ $counts['unpaid'] }}</b><span>Unpaid</span></div>
        <div class="tp-stat"><b>{{ $counts['paid'] }}</b><span>Paid</span></div>
    </div>

    <form class="tp-filter" method="GET" action="{{ route('crm.travel_plans') }}">
        <input type="text" name="q" value="{{ $q }}" placeholder="Search plan, customer, email, phone or offer...">

        <select name="status">
            <option value="">All Plan Statuses</option>
            @foreach($statuses as $status)
                <option value="{{ $status }}" @selected($activeStatus === $status)>{{ $status }}</option>
            @endforeach
        </select>

        <select name="payment_status">
            <option value="">All Payment Statuses</option>
            @foreach($paymentStatuses as $paymentStatus)
                <option value="{{ $paymentStatus }}" @selected($activePaymentStatus === $paymentStatus)>{{ $paymentStatus }}</option>
            @endforeach
        </select>

        <button class="tp-btn tp-btn-primary" type="submit">Filter</button>
        <a class="tp-btn tp-btn-light" href="{{ route('crm.travel_plans') }}">Reset</a>
    </form>

    <div class="tp-card">
        @if($plans->count())
            <table class="tp-table">
                <thead>
                    <tr>
                        <th>Travel Plan</th>
                        <th>Customer</th>
                        <th>Offer</th>
                        <th>Amount</th>
                        <th>Plan Status</th>
                        <th>Payment</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($plans as $plan)
                        @php
                            $planClass = strtolower(str_replace(' ', '-', $plan->status));
                            $payClass = strtolower(str_replace(' ', '-', $plan->payment_status));
                        @endphp
                        <tr>
                            <td><div class="tp-ref">{{ $plan->reference }}</div></td>
                            <td>
                                <div class="tp-customer">{{ $plan->customer_name }}</div>
                                <div class="tp-small">{{ $plan->customer_email }}</div>
                            </td>
                            <td>
                                <div>{{ $plan->offer_title ?: 'Travel Offer' }}</div>
                                <div class="tp-small">{{ $plan->offer_reference ?: '—' }}</div>
                            </td>
                            <td><strong>{{ $plan->currency }} {{ number_format((float)$plan->sale_amount, 2) }}</strong></td>
                            <td><span class="tp-badge tp-{{ $planClass }}">{{ $plan->status }}</span></td>
                            <td><span class="tp-badge tp-{{ $payClass }}">{{ $plan->payment_status }}</span></td>
                            <td>{{ $plan->created_at ? \Carbon\Carbon::parse($plan->created_at)->format('M d, Y') : '—' }}</td>
                            <td><a class="tp-btn tp-btn-primary" href="{{ route('crm.travel_plans.show', $plan->id) }}">View Plan</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="padding:14px">{{ $plans->links() }}</div>
        @else
            <div class="tp-empty">
                <strong>No Travel Plans found.</strong><br>
                Confirmed customer plans will appear here automatically.
            </div>
        @endif
    </div>
</div>
@endsection
