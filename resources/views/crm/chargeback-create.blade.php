@extends('crm.layout')

@section('content')
<style>
    .cb-wrap{max-width:1180px;margin:0 auto}
    .cb-head{display:flex;justify-content:space-between;align-items:flex-start;gap:20px;margin-bottom:22px}
    .cb-head h1{margin:0;font-size:28px;font-weight:700}
    .cb-sub{color:#6b7280;margin-top:5px}
    .cb-card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:22px;margin-bottom:20px}
    .cb-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
    .cb-grid-3{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}
    .cb-field label{display:block;font-weight:600;margin-bottom:7px}
    .cb-field input,.cb-field select,.cb-field textarea{
        width:100%;border:1px solid #d1d5db;border-radius:9px;padding:10px 12px;background:#fff
    }
    .cb-field textarea{min-height:110px;resize:vertical}
    .cb-muted{font-size:13px;color:#6b7280;margin-top:5px}
    .cb-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}
    .cb-stat{border:1px solid #e5e7eb;border-radius:10px;padding:14px}
    .cb-stat span{display:block;color:#6b7280;font-size:12px;margin-bottom:5px}
    .cb-stat strong{font-size:18px}
    .cb-actions{display:flex;gap:10px;justify-content:flex-end}
    .cb-btn{display:inline-flex;align-items:center;gap:7px;border-radius:9px;padding:10px 16px;text-decoration:none;border:1px solid #d1d5db;background:#fff;color:#111827;font-weight:600}
    .cb-btn-primary{background:#111827;color:#fff;border-color:#111827}
    .cb-alert{padding:12px 14px;border-radius:9px;margin-bottom:16px}
    .cb-alert-danger{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
    .cb-alert-info{background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe}
    @media(max-width:900px){
        .cb-grid,.cb-grid-3,.cb-summary{grid-template-columns:1fr}
        .cb-head{flex-direction:column}
    }
</style>

<div class="cb-wrap">
    <div class="cb-head">
        <div>
            <h1>Open Chargeback Case</h1>
            <div class="cb-sub">Record and manage a payment dispute case.</div>
        </div>
        <a href="{{ route('crm.chargebacks') }}" class="cb-btn">
            <i class="fa-solid fa-arrow-left"></i> Back to Chargebacks
        </a>
    </div>

    @if($errors->any())
        <div class="cb-alert cb-alert-danger">
            <strong>Please correct the following:</strong>
            <ul style="margin:8px 0 0 18px">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="cb-alert cb-alert-info">
        This CRM records the dispute workflow and evidence only. Processor/network submission and final chargeback decisions happen externally.
    </div>

    <div class="cb-card">
        <form method="GET" action="{{ route('crm.chargeback_create') }}">
            <div class="cb-grid" style="align-items:end">
                <div class="cb-field">
                    <label>Booking ID</label>
                    <input type="number" name="booking_id" min="1" value="{{ request('booking_id') }}" placeholder="Example: 11077" required>
                </div>
                <div>
                    <button type="submit" class="cb-btn cb-btn-primary">
                        <i class="fa-solid fa-magnifying-glass"></i> Load Booking
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if($booking)
        <div class="cb-card">
            <h3 style="margin-top:0">Booking & Payment Summary</h3>

            <div class="cb-summary">
                <div class="cb-stat">
                    <span>Booking</span>
                    <strong>#{{ $booking->id }}</strong>
                </div>
                <div class="cb-stat">
                    <span>Paid Amount</span>
                    <strong>${{ number_format((float)($paymentMeta->paid_amount ?? 0), 2) }}</strong>
                </div>
                <div class="cb-stat">
                    <span>Open Dispute Exposure</span>
                    <strong>${{ number_format((float)$openExposure, 2) }}</strong>
                </div>
                <div class="cb-stat">
                    <span>Remaining Disputable</span>
                    <strong>${{ number_format((float)$availableAmount, 2) }}</strong>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('crm.chargeback_store') }}">
            @csrf
            <input type="hidden" name="booking_id" value="{{ $booking->id }}">

            <div class="cb-card">
                <h3 style="margin-top:0">Dispute Details</h3>

                <div class="cb-grid-3">
                    <div class="cb-field">
                        <label>Disputed Amount *</label>
                        <input type="number" step="0.01" min="0.01" name="disputed_amount"
                               value="{{ old('disputed_amount') }}" required>
                    </div>

                    <div class="cb-field">
                        <label>Processor</label>
                        <input type="text" name="processor"
                               value="{{ old('processor', $paymentMeta->processor ?? '') }}"
                               maxlength="100">
                    </div>

                    <div class="cb-field">
                        <label>Reason Code</label>
                        <input type="text" name="reason_code"
                               value="{{ old('reason_code') }}"
                               maxlength="80">
                    </div>

                    <div class="cb-field">
                        <label>Transaction Reference</label>
                        <input type="text" name="transaction_reference"
                               value="{{ old('transaction_reference', $paymentMeta->transaction_reference ?? '') }}"
                               maxlength="120">
                    </div>

                    <div class="cb-field">
                        <label>Processor Case Reference</label>
                        <input type="text" name="processor_case_reference"
                               value="{{ old('processor_case_reference') }}"
                               maxlength="120">
                    </div>

                    <div class="cb-field">
                        <label>Card Brand</label>
                        <input type="text" name="card_brand"
                               value="{{ old('card_brand') }}"
                               maxlength="50">
                    </div>

                    <div class="cb-field">
                        <label>Card Last 4</label>
                        <input type="text" name="card_last4"
                               value="{{ old('card_last4') }}"
                               maxlength="4"
                               inputmode="numeric"
                               pattern="[0-9]{4}">
                        <div class="cb-muted">Last 4 digits only. Never enter full card number or CVV.</div>
                    </div>

                    <div class="cb-field">
                        <label>Received Date</label>
                        <input type="date" name="received_date" value="{{ old('received_date') }}">
                    </div>

                    <div class="cb-field">
                        <label>Response Deadline</label>
                        <input type="date" name="response_deadline" value="{{ old('response_deadline') }}">
                    </div>
                </div>
            </div>

            <div class="cb-card">
                <div class="cb-grid">
                    <div class="cb-field">
                        <label>Reason</label>
                        <textarea name="reason" maxlength="5000">{{ old('reason') }}</textarea>
                    </div>

                    <div class="cb-field">
                        <label>Internal Note</label>
                        <textarea name="internal_note" maxlength="5000">{{ old('internal_note') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="cb-actions">
                <a href="{{ route('crm.chargebacks') }}" class="cb-btn">Cancel</a>
                <button type="submit" class="cb-btn cb-btn-primary">
                    <i class="fa-solid fa-folder-plus"></i> Open Chargeback Case
                </button>
            </div>
        </form>
    @elseif(request('booking_id'))
        <div class="cb-alert cb-alert-danger">
            Booking #{{ request('booking_id') }} was not found.
        </div>
    @endif
</div>
@endsection
