@extends('crm.layout')
@section('content')
@php
    $flows = [
        'website' => ['Website bookings', 'fa-globe'],
        'fareleaders' => ['FareLeaders bookings (Compare + Core)', 'fa-plane'],
    ];
    $yes = fn ($b) => $b ? '<span class="sg-pill sg-ok">Yes</span>' : '<span class="sg-pill sg-no">No</span>';
@endphp
<style>
    .sg-wrap{max-width:1100px;margin:0 auto;padding:8px 4px 32px}
    .sg-card{background:#fff;border:1px solid #e6e9f0;border-radius:12px;padding:20px;margin-bottom:18px}
    .sg-card h2{font-size:18px;margin:0 0 6px}.sg-card p.sg-sub{color:#667085;margin:0 0 14px;font-size:13px}
    .sg-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px}
    .sg-pill{display:inline-block;padding:2px 10px;border-radius:999px;font-size:12px;font-weight:600}
    .sg-ok{background:#e7f6ec;color:#127a3a}.sg-no{background:#fdecec;color:#b42318}.sg-test{background:#fff4e5;color:#b54708}.sg-live{background:#fdecec;color:#b42318}
    .sg-big{font-size:22px;font-weight:700}
    .sg-table{width:100%;border-collapse:collapse;font-size:13px}.sg-table th,.sg-table td{padding:8px;border-bottom:1px solid #eef0f4;text-align:left}
    .sg-row{display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px dashed #eef0f4;font-size:13px}
    .sg-btn{border:0;border-radius:8px;padding:8px 14px;font-weight:600;cursor:pointer}
    .sg-on{background:#127a3a;color:#fff}.sg-off{background:#b42318;color:#fff}.sg-btn[disabled]{opacity:.45;cursor:not-allowed}.sg-note-pill{background:#eef2f6;color:#475467}
    .sg-note{background:#f8fafc;border-left:3px solid #94a3b8;padding:10px 12px;font-size:13px;color:#475467;border-radius:6px}
</style>
<div class="sg-wrap">
    <div class="sg-card">
        <h2><i class="fa-solid fa-credit-card"></i> Payment Gateway (Stripe)</h2>
        <p class="sg-sub">Super Admin only. OFF = customers use the existing booking flow exactly as today. Every change is recorded in the audit log below.</p>

        @if(session('crm_success'))<div class="alert alert-success">{{ session('crm_success') }}</div>@endif
        @if(isset($errors) && $errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        <div class="sg-row"><span>Stripe key mode in use</span>
            <span class="sg-pill {{ $status['mode'] === 'LIVE' ? 'sg-live' : 'sg-test' }}">{{ $status['mode'] }}</span></div>
        <div class="sg-row"><span>Webhook secret configured on server</span><span>{!! $yes($webhookReady) !!}</span></div>
        <div class="sg-note" style="margin-top:12px">API keys are only kept on the server (.env) and are never shown here. The CRM only chooses which set (Test or Live) is used.</div>
    </div>

    @php
        $fhMi = $modeInfo ?? [];
        $fhActive = ($fhMi['active'] ?? 'test') === 'live' ? 'live' : 'test';
        $fhTarget = $fhActive === 'live' ? 'test' : 'live';
        $fhCanGo = $fhTarget === 'live'
            ? (($fhMi['live_allowed'] ?? false) && ($fhMi['live_ready'] ?? false))
            : ($fhMi['test_ready'] ?? false);
        $fhInflight = $fhMi['inflight'] ?? [];
    @endphp
    <div class="sg-card" id="stripe-mode">
        <h2><i class="fa-solid fa-toggle-on"></i> Stripe mode (Test / Live)
            <span class="sg-pill {{ $fhActive === 'live' ? 'sg-live' : 'sg-test' }}">{{ strtoupper($fhActive) }}</span></h2>
        <p class="sg-sub">TEST = no real money (test cards only). LIVE = real customer cards are charged. Every switch is recorded in the audit log below.</p>
        <div class="sg-row"><span>Active mode</span><span class="sg-pill {{ $fhActive === 'live' ? 'sg-live' : 'sg-test' }}">{{ strtoupper($fhActive) }}</span></div>
        <div class="sg-row"><span>Chosen by</span><span>{{ $fhMi['source'] ?? '-' }}</span></div>
        <div class="sg-row"><span>Stripe checkout working in this mode</span><span>{!! $yes($fhMi['checkout_enabled'] ?? false) !!}</span></div>
        <div class="sg-row"><span>Test keys set on server</span><span>{!! $yes($fhMi['test_ready'] ?? false) !!}</span></div>
        <div class="sg-row"><span>Live keys set on server</span><span>{!! $yes($fhMi['live_ready'] ?? false) !!}</span></div>
        <div class="sg-row"><span>Server allows Live (.env STRIPE_LIVE_ENABLED)</span><span>{!! $yes($fhMi['live_allowed'] ?? false) !!}</span></div>
        <div class="sg-row"><span>Payments in progress (on hold / checkout open)</span><span>{{ count($fhInflight) ? '#'.implode(', #', $fhInflight) : 'None' }}</span></div>

        @if(!$fhCanGo)
            <div class="sg-note" style="margin-top:12px">
                @if($fhTarget === 'live')
                    Switch to LIVE is locked: {{ !($fhMi['live_allowed'] ?? false) ? 'the server does not allow Live (.env STRIPE_LIVE_ENABLED is off).' : 'live keys are not fully set on the server.' }}
                @else
                    Switch to TEST is locked: test keys are not fully set on the server.
                @endif
            </div>
        @else
            <form method="POST" action="{{ route('crm.stripe_gateway_update') }}" style="margin-top:14px">
                @csrf
                <input type="hidden" name="stripe_mode" value="{{ $fhTarget }}">
                <label style="display:block;font-size:13px;margin-bottom:6px">Reason (required)
                    <input type="text" name="reason" required minlength="5" maxlength="300" class="form-control" style="margin-top:4px" placeholder="e.g. {{ $fhTarget === 'live' ? 'Going live for customers' : 'Testing a change with test cards' }}">
                </label>
                @if(count($fhInflight))
                    <label style="display:block;font-size:13px;margin-bottom:6px;color:#b42318">
                        <input type="checkbox" name="force_inflight" value="1"> Switch anyway. I understand payments #{{ implode(', #', $fhInflight) }} can only be captured / cancelled after switching back to {{ strtoupper($fhActive) }}.
                    </label>
                @endif
                <label style="display:block;font-size:13px;margin-bottom:8px">
                    <input type="checkbox" name="confirm" value="1" required>
                    @if($fhTarget === 'live')
                        I confirm: switch to LIVE. Real customer cards will be charged.
                    @else
                        I confirm: switch to TEST. Real cards will NOT work until switched back to Live.
                    @endif
                </label>
                <button type="submit" class="sg-btn {{ $fhTarget === 'live' ? 'sg-off' : 'sg-on' }}">Switch to {{ strtoupper($fhTarget) }}</button>
            </form>
        @endif
    </div>

    <div class="sg-grid">
        @foreach($flows as $flow => [$label, $icon])
            @php $f = $status['flows'][$flow] ?? []; $isOn = ($f['crm_switch'] ?? 'OFF') === 'ON'; $ready = ($f['ready'] ?? false) === true; @endphp
            <div class="sg-card">
                <h2><i class="fa-solid {{ $icon }}"></i> {{ $label }}</h2>
                <p class="sg-sub">Customers currently use:
                    <strong>{{ ($f['effective'] ?? 'LEGACY') === 'STRIPE' ? 'Stripe checkout' : 'Existing (legacy) flow' }}</strong></p>
                <div class="sg-row"><span>CRM switch</span><span class="sg-pill {{ $isOn ? 'sg-ok' : 'sg-no' }}">{{ $isOn ? 'ON' : 'OFF' }}</span></div>
                <div class="sg-row"><span>Server (.env) allows Stripe</span><span>{!! $yes($f['server_allows'] ?? false) !!}</span></div>
                <div class="sg-row"><span>Flow ready for Stripe</span><span>{!! $yes($ready) !!}</span></div>

                @if(!$ready)
                    <div class="sg-note" style="margin-top:12px">Not ready yet - this switch is locked. Bookings stay on the existing flow.</div>
                @else
                    <form method="POST" action="{{ route('crm.stripe_gateway_update') }}" style="margin-top:14px">
                        @csrf
                        <input type="hidden" name="flow" value="{{ $flow }}">
                        @if($isOn)
                            <input type="hidden" name="state" value="OFF">
                            <button type="submit" class="sg-btn sg-off">Turn OFF (back to existing flow)</button>
                        @else
                            <input type="hidden" name="state" value="ON">
                            <label style="display:block;font-size:13px;margin-bottom:8px">
                                <input type="checkbox" name="confirm" value="1"> I confirm Stripe should be used for {{ strtolower($label) }}
                            </label>
                            <button type="submit" class="sg-btn sg-on">Turn ON</button>
                        @endif
                    </form>
                    @if(!($f['server_allows'] ?? false))
                        <div class="sg-note" style="margin-top:10px">The server does not allow Stripe right now, so even when ON the existing flow is used.</div>
                    @endif
                @endif
            </div>
        @endforeach
    </div>

    @php
        $fhNeed = $needsAttention ?? [];
        $fhWh = $webhookHealth ?? [];
        $fhLevel = ['high' => 'sg-no', 'medium' => 'sg-test', 'low' => 'sg-note-pill'];
    @endphp
    <div class="sg-card" id="needs-attention">
        <h2>Needs attention @if(count($fhNeed))<span class="sg-pill sg-no">{{ count($fhNeed) }}</span>@else<span class="sg-pill sg-ok">0</span>@endif</h2>
        <p class="sg-sub">Stripe payments stuck in the middle: money on hold more than {{ \App\Services\StripeAttentionService::AUTHORIZED_WAIT_MIN }} min without a finished booking, checkout open more than {{ \App\Services\StripeAttentionService::PENDING_STUCK_MIN }} min with no result, or an error. Read-only.</p>
        <table class="sg-table">
            <thead><tr><th>#</th><th>Level</th><th>Status</th><th>Amount</th><th>Age</th><th>Booking</th><th>Why</th><th>What to do</th><th>Error</th></tr></thead>
            <tbody>
            @forelse($fhNeed as $n)
                <tr>
                    <td>{{ $n['id'] }}</td>
                    <td><span class="sg-pill {{ $fhLevel[$n['level']] ?? 'sg-test' }}">{{ strtoupper($n['level']) }}</span></td>
                    <td><span class="sg-pill {{ $status['mode'] === 'LIVE' ? 'sg-live' : 'sg-test' }}">{{ $status['mode'] }}</span> {{ $n['status'] }}</td>
                    <td>{{ $n['currency'] }} {{ $n['amount'] }}</td>
                    <td>{{ $n['age'] }}</td>
                    <td>@if($n['booking_id'] && \Illuminate\Support\Facades\Route::has('crm.booking_detail'))<a href="{{ route('crm.booking_detail', $n['booking_id']) }}">#{{ $n['booking_id'] }}</a>@else - @endif {{ $n['booking_status'] }}</td>
                    <td>{{ $n['reason'] }}</td>
                    <td>{{ $n['action'] }}</td>
                    <td>{{ $n['error'] !== '' ? $n['error'] : '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="sg-sub">Nothing needs attention.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="sg-card" id="webhook-health">
        <h2>Webhook health</h2>
        <p class="sg-sub">Stripe sends payment results to the website. If these stop arriving, payments get stuck.</p>
        @if(!empty($fhWh['warning']))<div class="alert alert-danger">{{ $fhWh['warning'] }}</div>@endif
        <div class="sg-row"><span>Webhook secret configured on server</span><span>{!! $yes($webhookReady) !!}</span></div>
        <div class="sg-row"><span>Last event received</span><span>{{ $fhWh['last_received_at'] ?? 'Never' }}</span></div>
        <div class="sg-row"><span>Last event processed</span><span>{{ $fhWh['last_processed_at'] ?? 'Never' }}</span></div>
        <div class="sg-row"><span>Events in last 24 hours (processed / failed)</span><span>{{ (int) ($fhWh['day_total'] ?? 0) }} ({{ (int) ($fhWh['day_processed'] ?? 0) }} / {{ (int) ($fhWh['day_failed'] ?? 0) }})</span></div>
        <div class="sg-row"><span>Events stuck in processing</span><span>{{ (int) ($fhWh['stuck_received'] ?? 0) }}</span></div>
        @if(!empty($fhWh['problems']))
        <table class="sg-table" style="margin-top:12px">
            <thead><tr><th>Event</th><th>Type</th><th>Status</th><th>Tries</th><th>Error</th><th>Received</th></tr></thead>
            <tbody>
            @foreach($fhWh['problems'] as $e)
                <tr><td>{{ $e['event'] }}</td><td>{{ $e['type'] }}</td><td>{{ $e['status'] }}</td><td>{{ $e['attempts'] }}</td><td>{{ $e['error'] !== '' ? $e['error'] : '-' }}</td><td>{{ $e['created_at'] }}</td></tr>
            @endforeach
            </tbody>
        </table>
        @endif
        <div class="sg-note" style="margin-top:12px">Times are server time (UTC).</div>
    </div>

    <div class="sg-card">
        <h2>Payment attempts</h2>
        <p class="sg-sub">Stripe-only records. No card data is ever stored here.</p>
        <div class="sg-grid" style="grid-template-columns:repeat(auto-fit,minmax(120px,1fr));margin-bottom:12px">
            @foreach(['pending','authorized','captured','cancelled','expired','failed'] as $s)
                <div><div class="sg-big">{{ (int)($attemptCounts[$s] ?? 0) }}</div><div class="sg-sub" style="margin:0">{{ ucfirst($s) }}</div></div>
            @endforeach
        </div>
        <table class="sg-table">
            <thead><tr><th>#</th><th>Flow</th><th>Amount</th><th>Status</th><th>Booking</th><th>Error</th><th>Created</th></tr></thead>
            <tbody>
            @forelse($recentAttempts as $a)
                <tr><td>{{ $a->id }}</td><td>{{ $a->flow }}</td><td>{{ $a->currency }} {{ number_format($a->amount_cents / 100, 2) }}</td>
                    <td><span class="sg-pill {{ $status['mode'] === 'LIVE' ? 'sg-live' : 'sg-test' }}">{{ $status['mode'] }}</span> {{ $a->status }}</td><td>{{ $a->booking_status }}</td><td>{{ $a->last_error_code }}</td><td>{{ $a->created_at }}</td></tr>
            @empty
                <tr><td colspan="7" class="sg-sub">No Stripe payment attempts yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="sg-card">
        <h2>Audit log</h2>
        <table class="sg-table">
            <thead><tr><th>When</th><th>Who</th><th>Switch</th><th>From</th><th>To</th></tr></thead>
            <tbody>
            @forelse($audit as $l)
                <tr><td>{{ $l->created_at }}</td><td>{{ $l->admin_name ?? '-' }}</td><td>{{ $l->record_key }}</td><td>{{ $l->old_value }}</td><td>{{ $l->new_value }}</td></tr>
            @empty
                <tr><td colspan="5" class="sg-sub">No changes yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
