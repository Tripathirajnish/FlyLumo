@extends('crm.layout')

@section('content')
<style id="fareshunt-refund-create-v1">
.refund-create-wrap{max-width:1100px;margin:0 auto}
.refund-create-head{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;margin-bottom:22px}
.refund-create-head h1{margin:4px 0 6px;font-size:28px;font-weight:700}
.refund-create-head p{margin:0;color:#64748b}
.refund-eyebrow{font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#2563eb}
.refund-back{display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border:1px solid #dbe2ea;border-radius:10px;text-decoration:none;color:#334155;background:#fff}
.refund-grid{display:grid;grid-template-columns:minmax(0,1.4fr) minmax(280px,.6fr);gap:22px}
.refund-card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:22px;box-shadow:0 4px 18px rgba(15,23,42,.04)}
.refund-card h2{font-size:18px;margin:0 0 18px}
.refund-field{margin-bottom:18px}
.refund-field label{display:block;font-weight:600;margin-bottom:7px;color:#334155}
.refund-field input,.refund-field select,.refund-field textarea{width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:11px 12px;background:#fff;color:#0f172a}
.refund-field textarea{min-height:110px;resize:vertical}
.refund-help{font-size:12px;color:#64748b;margin-top:6px}
.refund-error{font-size:12px;color:#dc2626;margin-top:6px}
.refund-summary-row{display:flex;justify-content:space-between;gap:14px;padding:12px 0;border-bottom:1px solid #eef2f7}
.refund-summary-row:last-child{border-bottom:0}
.refund-summary-label{color:#64748b}
.refund-summary-value{font-weight:700;color:#0f172a;text-align:right}
.refund-alert{border-radius:12px;padding:14px 16px;margin-bottom:18px}
.refund-alert.warning{background:#fff7ed;border:1px solid #fed7aa;color:#9a3412}
.refund-alert.info{background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af}
.refund-actions{display:flex;align-items:center;gap:12px;margin-top:20px}
.refund-submit{border:0;border-radius:10px;padding:11px 18px;background:#0f172a;color:#fff;font-weight:700;cursor:pointer}
.refund-submit:disabled{opacity:.5;cursor:not-allowed}
.refund-cancel{padding:10px 14px;text-decoration:none;color:#475569}
@media (max-width:900px){.refund-grid{grid-template-columns:1fr}.refund-create-head{flex-direction:column}}
</style>

<div class="refund-create-wrap">
    <div class="refund-create-head">
        <div>
            <span class="refund-eyebrow">Refund operations</span>
            <h1>New Refund Request</h1>
            <p>Create a controlled refund case for review and processing.</p>
        </div>
        <a class="refund-back" href="{{ route('crm.refunds') }}">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Refunds
        </a>
    </div>

    @if ($errors->any())
        <div class="refund-alert warning">
            <strong>Please review the form.</strong>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    <div class="refund-alert info">
        <strong>Important:</strong>
        This creates a CRM refund request only. It does not send money to Stripe, Authorize.Net, PayPal, a bank, or any other payment processor.
    </div>

    <div class="refund-grid">
        <div class="refund-card">
            <h2>Refund Request</h2>

            <form method="POST" action="{{ route('crm.refund_store') }}">
                @csrf

                <div class="refund-field">
                    <label for="booking_id">Booking ID</label>
                    <input
                        id="booking_id"
                        type="number"
                        min="1"
                        name="booking_id"
                        value="{{ old('booking_id', $booking?->id) }}"
                        required
                    >
                    <div class="refund-help">
                        Enter the CRM booking ID. Paid amount is always verified from Payment Detail on submission.
                    </div>
                    @error('booking_id')
                        <div class="refund-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="refund-field">
                    <label for="refund_type">Refund Type</label>
                    <select id="refund_type" name="refund_type" required>
                        <option value="">Select refund type</option>
                        @foreach($refundTypes as $type)
                            <option value="{{ $type }}" @selected(old('refund_type') === $type)>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                    @error('refund_type')
                        <div class="refund-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="refund-field">
                    <label for="requested_amount">Requested Amount (USD)</label>
                    <input
                        id="requested_amount"
                        type="number"
                        step="0.01"
                        min="0.01"
                        name="requested_amount"
                        value="{{ old('requested_amount') }}"
                        required
                    >
                    @if($booking)
                        <div class="refund-help">
                            Current available refundable amount: ${{ number_format($availableAmount, 2) }}
                        </div>
                    @endif
                    @error('requested_amount')
                        <div class="refund-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="refund-field">
                    <label for="reason">Reason</label>
                    <textarea id="reason" name="reason" maxlength="5000" required>{{ old('reason') }}</textarea>
                    <div class="refund-help">
                        Enter the business reason for this refund request. Do not enter full card number or CVV.
                    </div>
                    @error('reason')
                        <div class="refund-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="refund-field">
                    <label for="internal_note">Internal Note <span style="font-weight:400;color:#94a3b8">(optional)</span></label>
                    <textarea id="internal_note" name="internal_note" maxlength="5000">{{ old('internal_note') }}</textarea>
                    <div class="refund-help">
                        Internal CRM note only. Never enter full PAN/card number or CVV.
                    </div>
                    @error('internal_note')
                        <div class="refund-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="refund-actions">
                    <button class="refund-submit" type="submit">
                        Create Refund Request
                    </button>
                    <a class="refund-cancel" href="{{ route('crm.refunds') }}">Cancel</a>
                </div>
            </form>
        </div>

        <div class="refund-card">
            <h2>Payment Check</h2>

            @if($booking)
                <div class="refund-summary-row">
                    <span class="refund-summary-label">Booking ID</span>
                    <span class="refund-summary-value">#{{ $booking->id }}</span>
                </div>

                <div class="refund-summary-row">
                    <span class="refund-summary-label">Payment Status</span>
                    <span class="refund-summary-value">{{ $paymentMeta->payment_status ?? 'Untracked' }}</span>
                </div>

                <div class="refund-summary-row">
                    <span class="refund-summary-label">Paid Amount</span>
                    <span class="refund-summary-value">
                        ${{ number_format((float)($paymentMeta->paid_amount ?? 0), 2) }}
                    </span>
                </div>

                <div class="refund-summary-row">
                    <span class="refund-summary-label">Existing Refund Exposure</span>
                    <span class="refund-summary-value">${{ number_format($reservedAmount, 2) }}</span>
                </div>

                <div class="refund-summary-row">
                    <span class="refund-summary-label">Available to Request</span>
                    <span class="refund-summary-value">${{ number_format($availableAmount, 2) }}</span>
                </div>

                <div class="refund-summary-row">
                    <span class="refund-summary-label">Processor</span>
                    <span class="refund-summary-value">{{ $paymentMeta->processor ?? '—' }}</span>
                </div>

                <div class="refund-summary-row">
                    <span class="refund-summary-label">Transaction Reference</span>
                    <span class="refund-summary-value">{{ $paymentMeta->transaction_reference ?? '—' }}</span>
                </div>
            @else
                <p style="margin:0;color:#64748b">
                    Enter a Booking ID and submit the request. The server will verify the recorded paid amount and block over-refunds automatically.
                </p>
            @endif
        </div>
    </div>
</div>
@endsection
