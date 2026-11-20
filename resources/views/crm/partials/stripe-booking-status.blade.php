{{-- Stripe payment status for this booking (Step G2) + Super-Admin actions (Step G5). Renders nothing when there is no Stripe attempt. --}}
@php
    $fhStripeRows = [];
    $fhStripeCanManage = false;
    $fhStripeActions = [];
    try {
        $fhAccess = app(\App\Services\CrmAccessService::class);
        $fhAdmin = (int) session('crm_admin_id');
        if ($fhAccess->can($fhAdmin, 'payments.view')) {
            $fhStripeRows = app(\App\Services\StripeBookingStatusService::class)->forBooking((int) ($bookingId ?? 0));
        }
        $fhStripeCanManage = count($fhStripeRows) && $fhAccess->can($fhAdmin, 'stripe_gateway.manage');
        if ($fhStripeCanManage) {
            $fhSvc = app(\App\Services\StripeAttemptActionService::class);
            foreach ($fhStripeRows as $fhRow) {
                $fhA = \Illuminate\Support\Facades\DB::table('stripe_payment_attempts')->where('id', $fhRow['id'])->first(['id', 'status', 'booking_status', 'stripe_payment_intent_id']);
                $fhStripeActions[$fhRow['id']] = $fhA ? $fhSvc->allowed($fhA) : [];
            }
        }
    } catch (\Throwable $e) {
        $fhStripeRows = [];
        $fhStripeActions = [];
    }
    $fhStripeTone = fn ($s) => match ($s) {
        'captured' => 'background:#e7f6ec;color:#127a3a',
        'authorized' => 'background:#e8f0fe;color:#1d4ed8',
        'pending', 'created' => 'background:#fff4e5;color:#b54708',
        default => 'background:#fdecec;color:#b42318',
    };
    $fhActLabel = ['capture' => 'Capture payment', 'cancel' => 'Cancel hold', 'refund' => 'Refund in full'];
    $fhActHelp = [
        'capture' => 'Charges the customer now for the full amount on hold. Do this only after the supplier booking is confirmed.',
        'cancel' => 'Releases the hold; the customer is not charged. If a supplier booking exists, cancel it with the supplier too.',
        'refund' => 'Returns the full captured amount to the customer card. This cannot be undone.',
    ];
@endphp
@if(count($fhStripeRows))
<section class="bdo-card" id="stripe-payment">
    <header>
        <div><span class="bdo-kicker">Online payment</span><h3>Stripe payment @php $fhMode = app(\App\Services\StripeTestPaymentService::class)->mode(); @endphp<span style="display:inline-block;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:700;{{ $fhMode === 'live' ? 'background:#e7f6ec;color:#127a3a' : 'background:#fff4e5;color:#b54708' }};vertical-align:middle">{{ strtoupper($fhMode) }}</span></h3></div>
    </header>
    <div style="padding:14px 18px;overflow-x:auto">
        @if($errors->has('attempt_action') || $errors->has('reason') || $errors->has('confirm'))
            <div style="background:#fdecec;color:#b42318;border-radius:8px;padding:8px 12px;margin-bottom:10px;font-size:13px">{{ $errors->first('attempt_action') ?: ($errors->first('reason') ?: $errors->first('confirm')) }}</div>
        @endif
        <table style="width:100%;border-collapse:collapse;font-size:13px">
            <thead><tr style="text-align:left;color:#667085">
                <th style="padding:6px 8px">#</th><th style="padding:6px 8px">Status</th><th style="padding:6px 8px">Amount</th>
                <th style="padding:6px 8px">Booking step</th><th style="padding:6px 8px">Authorized</th><th style="padding:6px 8px">Captured</th>
                <th style="padding:6px 8px">Cancelled</th><th style="padding:6px 8px">Error</th><th style="padding:6px 8px">Stripe ref</th>
            </tr></thead>
            <tbody>
            @foreach($fhStripeRows as $r)
                <tr style="border-top:1px solid #eef0f4">
                    <td style="padding:6px 8px">{{ $r['id'] }}</td>
                    <td style="padding:6px 8px"><span style="display:inline-block;padding:2px 8px;border-radius:999px;font-size:12px;font-weight:600;{{ $fhStripeTone($r['status']) }}">{{ ucfirst($r['status']) }}</span></td>
                    <td style="padding:6px 8px">{{ $r['currency'] }} {{ $r['amount'] }}</td>
                    <td style="padding:6px 8px">{{ $r['booking_status'] !== '' ? $r['booking_status'] : '-' }}</td>
                    <td style="padding:6px 8px">{{ $r['authorized_at'] ?? '-' }}</td>
                    <td style="padding:6px 8px">{{ $r['captured_at'] ?? '-' }}</td>
                    <td style="padding:6px 8px">{{ $r['cancelled_at'] ?? '-' }}</td>
                    <td style="padding:6px 8px">{{ $r['error'] !== '' ? $r['error'] : '-' }}</td>
                    <td style="padding:6px 8px">{{ $r['intent_tail'] !== '' ? $r['intent_tail'] : '-' }}</td>
                </tr>
                @foreach(($fhStripeActions[$r['id']] ?? []) as $fhAct)
                <tr><td colspan="9" style="padding:4px 8px 10px">
                    <details style="border:1px solid #e4e7ec;border-radius:8px;padding:8px 12px;background:#fbfcfe">
                        <summary style="cursor:pointer;font-weight:600;color:{{ $fhAct === 'capture' ? '#127a3a' : '#b42318' }}">{{ $fhActLabel[$fhAct] }} - payment #{{ $r['id'] }} ({{ $r['currency'] }} {{ $r['amount'] }})</summary>
                        <form method="POST" action="{{ route('crm.stripe_gateway_update') }}" style="margin-top:8px" onsubmit="var b=this.querySelector('button');if(b){b.disabled=true;}">
                            @csrf
                            <input type="hidden" name="attempt_id" value="{{ $r['id'] }}">
                            <input type="hidden" name="attempt_action" value="{{ $fhAct }}">
                            <p style="font-size:12px;color:#475467;margin:0 0 6px">{{ $fhActHelp[$fhAct] }}</p>
                            <input type="text" name="reason" required minlength="5" maxlength="300" placeholder="Reason (saved in the audit log)" class="form-control" style="margin-bottom:6px">
                            <label style="font-size:12px;display:block;margin-bottom:6px"><input type="checkbox" name="confirm" value="1" required> I confirm: {{ strtolower($fhActLabel[$fhAct]) }} for {{ $r['currency'] }} {{ $r['amount'] }}</label>
                            <button type="submit" class="btn btn-sm {{ $fhAct === 'capture' ? 'btn-success' : 'btn-danger' }}">{{ $fhActLabel[$fhAct] }}</button>
                        </form>
                    </details>
                </td></tr>
                @endforeach
            @endforeach
            </tbody>
        </table>
        <p style="font-size:12px;color:#667085;margin:10px 0 0">Times are server time (UTC). No card data is stored for Stripe payments. Actions are Super Admin only, full amount only, and every action is saved in the payment gateway audit log.</p>
    </div>
</section>
@endif
