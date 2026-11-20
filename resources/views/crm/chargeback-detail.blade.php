@extends('crm.layout')

@section('content')
@php
    $statusTone = function($status){
        return match($status){
            'New', 'Evidence Required' => 'warning',
            'Evidence Ready', 'Submitted', 'Under Review' => 'info',
            'Won', 'Closed' => 'success',
            'Lost', 'Accepted' => 'danger',
            default => 'neutral',
        };
    };
@endphp

<style>
.cb-detail{max-width:1400px;margin:0 auto}
.cb-detail-head{display:flex;justify-content:space-between;gap:20px;align-items:flex-start;margin-bottom:22px}
.cb-detail-head h1{margin:4px 0 6px;font-size:30px}
.cb-eyebrow{font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#64748b}
.cb-sub{color:#64748b;margin:0}
.cb-actions{display:flex;gap:8px;flex-wrap:wrap}
.cb-btn{display:inline-flex;align-items:center;gap:7px;padding:10px 14px;border:1px solid #dbe3ed;border-radius:9px;text-decoration:none;color:#0f172a;background:#fff;font-weight:600}
.cb-grid{display:grid;grid-template-columns:2fr 1fr;gap:20px}
.cb-card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:20px;margin-bottom:20px}
.cb-card h2{font-size:17px;margin:0 0 16px}
.cb-kv{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
.cb-kv div{min-width:0}
.cb-kv small{display:block;color:#64748b;margin-bottom:4px}
.cb-kv strong,.cb-kv span{word-break:break-word}
.cb-badge{display:inline-flex;padding:5px 9px;border-radius:999px;font-size:12px;font-weight:700}
.cb-badge.warning{background:#fff7ed;color:#c2410c}
.cb-badge.info{background:#eff6ff;color:#1d4ed8}
.cb-badge.success{background:#ecfdf5;color:#047857}
.cb-badge.danger{background:#fef2f2;color:#b91c1c}
.cb-badge.neutral{background:#f1f5f9;color:#475569}
.cb-note{white-space:pre-wrap;color:#334155;line-height:1.55}
.cb-event{padding:14px 0;border-bottom:1px solid #eef2f7}
.cb-event:last-child{border-bottom:0}
.cb-event-top{display:flex;justify-content:space-between;gap:15px}
.cb-event small{color:#64748b}
.cb-empty{color:#64748b}
.cb-doc{display:flex;justify-content:space-between;gap:15px;align-items:center;padding:12px 0;border-bottom:1px solid #eef2f7}
.cb-doc:last-child{border-bottom:0}
@media(max-width:900px){
    .cb-grid{grid-template-columns:1fr}
    .cb-detail-head{flex-direction:column}
    .cb-kv{grid-template-columns:1fr}
}
</style>

<div class="cb-detail">
    <div class="cb-detail-head">
        <div>
            <span class="cb-eyebrow">Chargeback case</span>
            <h1>{{ $case->case_reference }}</h1>
            <p class="cb-sub">
                Booking #FH{{ $case->booking_id }}
                · Dispute
            </p>
        </div>

        <div class="cb-actions">
            <a class="cb-btn" href="{{ route('crm.chargebacks') }}">
                <i class="fa-solid fa-arrow-left"></i> Back to Chargebacks
            </a>
            <a class="cb-btn" href="{{ route('crm.booking_detail',$case->booking_id) }}">
                <i class="fa-solid fa-plane"></i> Booking
            </a>
            <a class="cb-btn" href="{{ route('crm.payment_detail',$case->booking_id) }}">
                <i class="fa-regular fa-credit-card"></i> Payment
            </a>
        </div>
    </div>

    <div class="cb-grid">
        <div>
            <div class="cb-card">
                <h2>Case Summary</h2>

                <div class="cb-kv">
                    <div>
                        <small>Status</small>
                        <span class="cb-badge {{ $statusTone($case->status) }}">{{ $case->status }}</span>
                    </div>

                    <div>
                        <small>Disputed Amount</small>
                        <strong>${{ number_format((float)$case->disputed_amount,2) }} {{ $case->currency ?: 'USD' }}</strong>
                    </div>

                    <div>
                        <small>Processor</small>
                        <strong>{{ $case->processor ?: '—' }}</strong>
                    </div>

                    <div>
                        <small>Transaction Reference</small>
                        <strong>{{ $case->transaction_reference ?: '—' }}</strong>
                    </div>

                    <div>
                        <small>Processor Case Reference</small>
                        <strong>{{ $case->processor_case_reference ?: '—' }}</strong>
                    </div>

                    <div>
                        <small>Reason Code</small>
                        <strong>{{ $case->reason_code ?: '—' }}</strong>
                    </div>

                    <div>
                        <small>Card</small>
                        <strong>
                            {{ $case->card_brand ?: 'Card' }}
                            @if($case->card_last4)
                                · **** {{ $case->card_last4 }}
                            @endif
                        </strong>
                    </div>

                    <div>
                        <small>Received Date</small>
                        <strong>{{ $case->received_date ? \Carbon\Carbon::parse($case->received_date)->format('d M Y') : '—' }}</strong>
                    </div>

                    <div>
                        <small>Response Deadline</small>
                        <strong>{{ $case->response_deadline ? \Carbon\Carbon::parse($case->response_deadline)->format('d M Y') : '—' }}</strong>
                    </div>

                    <div>
                        <small>Created By</small>
                        <strong>{{ $case->created_name ?: '—' }}</strong>
                    </div>

                    <div>
                        <small>Assigned To</small>
                        <strong>{{ $case->assigned_name ?: 'Unassigned' }}</strong>
                    </div>

                    <div>
                        <small>Outcome</small>
                        <strong>{{ $case->outcome ?: '—' }}</strong>
                    </div>
                </div>
            </div>

            <div class="cb-card">
                <h2>Reason</h2>
                <div class="cb-note">{{ $case->reason ?: '—' }}</div>
            </div>

            <div class="cb-card">
                <h2>Internal Note</h2>
                <div class="cb-note">{{ $case->internal_note ?: '—' }}</div>
            </div>

            <div class="cb-card">
                <h2>Evidence Documents</h2>

                @forelse($documents as $document)
                    <div class="cb-doc">
                        <div>
                            <strong>{{ $document->original_name }}</strong>
                            <div>
                                <small>
                                    {{ $document->document_type ?: 'Evidence' }}
                                    · Uploaded by {{ $document->uploaded_by_name ?: 'System' }}
                                    @if($document->created_at)
                                        · {{ \Carbon\Carbon::parse($document->created_at)->format('d M Y h:i A') }}
                                    @endif
                                </small>
                            </div>
                        </div>
                        <span class="cb-badge neutral">
                            {{ $document->mime_type ?: 'File' }}
                        </span>
                    </div>
                @empty
                    <div class="cb-empty">No chargeback evidence documents uploaded yet.</div>
                @endforelse
            </div>

            <div class="cb-card">
                <h2>Case History</h2>

                @forelse($events as $event)
                    <div class="cb-event">
                        <div class="cb-event-top">
                            <div>
                                <strong>{{ ucfirst(str_replace('_',' ',$event->event_type)) }}</strong>
                                @if($event->from_status || $event->to_status)
                                    <small>
                                        {{ $event->from_status ?: '—' }}
                                        →
                                        {{ $event->to_status ?: '—' }}
                                    </small>
                                @endif
                            </div>

                            <small>
                                {{ $event->created_at ? \Carbon\Carbon::parse($event->created_at)->format('d M Y h:i A') : '—' }}
                            </small>
                        </div>

                        <small>By {{ $event->admin_name ?: 'System' }}</small>

                        @if($event->details)
                            <div class="cb-note" style="margin-top:7px">{{ $event->details }}</div>
                        @endif
                    </div>
                @empty
                    <div class="cb-empty">No chargeback case events recorded yet.</div>
                @endforelse
            </div>
        </div>

        <div>
            <div class="cb-card">
                <h2>Case Workflow</h2>

                @if($case->status === 'New')
                    <form method="POST" action="{{ route('crm.chargeback_evidence_required',$case->id) }}">
                        @csrf
                        <label style="display:block;font-size:12px;font-weight:700;color:#64748b;margin-bottom:6px">
                            Evidence request note
                        </label>
                        <textarea name="note" rows="3" maxlength="2000"
                                  style="width:100%;border:1px solid #dbe3ed;border-radius:9px;padding:10px;margin-bottom:10px"
                                  placeholder="Optional internal note"></textarea>
                        <button type="submit" class="cb-btn">
                            <i class="fa-solid fa-folder-open"></i>
                            Mark Evidence Required
                        </button>
                    </form>

                @elseif($case->status === 'Evidence Required')
                    <form method="POST" action="{{ route('crm.chargeback_evidence_ready',$case->id) }}">
                        @csrf
                        <label style="display:block;font-size:12px;font-weight:700;color:#64748b;margin-bottom:6px">
                            Evidence note
                        </label>
                        <textarea name="note" rows="3" maxlength="2000"
                                  style="width:100%;border:1px solid #dbe3ed;border-radius:9px;padding:10px;margin-bottom:10px"
                                  placeholder="Optional internal note"></textarea>
                        <button type="submit" class="cb-btn">
                            <i class="fa-solid fa-circle-check"></i>
                            Mark Evidence Ready
                        </button>
                    </form>

                @elseif($case->status === 'Evidence Ready')
                    <form method="POST" action="{{ route('crm.chargeback_submit',$case->id) }}">
                        @csrf
                        <label style="display:block;font-size:12px;font-weight:700;color:#64748b;margin-bottom:6px">
                            Processor Case Reference
                        </label>
                        <input type="text"
                               name="processor_case_reference"
                               maxlength="120"
                               value="{{ old('processor_case_reference',$case->processor_case_reference) }}"
                               style="width:100%;border:1px solid #dbe3ed;border-radius:9px;padding:10px;margin-bottom:10px"
                               placeholder="Optional external processor case/reference">

                        <label style="display:block;font-size:12px;font-weight:700;color:#64748b;margin-bottom:6px">
                            Submission note
                        </label>
                        <textarea name="note" rows="3" maxlength="2000"
                                  style="width:100%;border:1px solid #dbe3ed;border-radius:9px;padding:10px;margin-bottom:10px"
                                  placeholder="Optional internal note"></textarea>

                        <p style="margin:0 0 12px;color:#64748b;font-size:12px;line-height:1.5">
                            This records an external submission in CRM only. It does not submit anything automatically to the processor or card network.
                        </p>

                        <button type="submit" class="cb-btn">
                            <i class="fa-solid fa-paper-plane"></i>
                            Record as Submitted
                        </button>
                    </form>

                @elseif($case->status === 'Submitted')
                    <form method="POST" action="{{ route('crm.chargeback_review',$case->id) }}">
                        @csrf
                        <button type="submit" class="cb-btn">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            Move to Under Review
                        </button>
                    </form>

                @elseif($case->status === 'Under Review')
                    <form method="POST" action="{{ route('crm.chargeback_resolve',$case->id) }}">
                        @csrf

                        <label style="display:block;font-size:12px;font-weight:700;color:#64748b;margin-bottom:6px">
                            Final Outcome
                        </label>
                        <select name="outcome" required
                                style="width:100%;border:1px solid #dbe3ed;border-radius:9px;padding:10px;margin-bottom:10px;background:#fff">
                            <option value="">Select outcome</option>
                            <option value="Won" @selected(old('outcome') === 'Won')>Won</option>
                            <option value="Lost" @selected(old('outcome') === 'Lost')>Lost</option>
                            <option value="Accepted" @selected(old('outcome') === 'Accepted')>Accepted</option>
                        </select>

                        <label style="display:block;font-size:12px;font-weight:700;color:#64748b;margin-bottom:6px">
                            Resolution note
                        </label>
                        <textarea name="note" rows="4" maxlength="2000" required
                                  style="width:100%;border:1px solid #dbe3ed;border-radius:9px;padding:10px;margin-bottom:10px"
                                  placeholder="Record the external processor/acquirer decision and relevant details">{{ old('note') }}</textarea>

                        <button type="submit" class="cb-btn">
                            <i class="fa-solid fa-gavel"></i>
                            Record Outcome
                        </button>
                    </form>

                @elseif(in_array($case->status, ['Won','Lost','Accepted'], true))
                    <div style="margin-bottom:12px">
                        <small style="display:block;color:#64748b;margin-bottom:4px">Recorded Outcome</small>
                        <span class="cb-badge {{ $statusTone($case->status) }}">{{ $case->status }}</span>
                    </div>

                    <form method="POST" action="{{ route('crm.chargeback_close',$case->id) }}">
                        @csrf
                        <button type="submit" class="cb-btn">
                            <i class="fa-solid fa-lock"></i>
                            Close Chargeback Case
                        </button>
                    </form>

                @elseif($case->status === 'Closed')
                    <div class="cb-empty">
                        This chargeback case is closed.
                        @if($case->outcome)
                            Final outcome: <strong>{{ $case->outcome }}</strong>.
                        @endif
                    </div>

                @else
                    <div class="cb-empty">
                        No workflow action is available for the current status.
                    </div>
                @endif

                @if($errors->has('chargeback'))
                    <div style="margin-top:12px;padding:10px 12px;border-radius:9px;background:#fef2f2;color:#b91c1c;font-size:13px">
                        {{ $errors->first('chargeback') }}
                    </div>
                @endif
            </div>

        <div>
            <div class="cb-card">
                <h2>Booking</h2>

                <div class="cb-kv" style="grid-template-columns:1fr">
                    <div>
                        <small>Booking ID</small>
                        <strong>#FH{{ $case->booking_id }}</strong>
                    </div>

                    <div>
                        <small>Passenger</small>
                        <strong>{{ $booking->o_Name ?? 'Guest' }}</strong>
                    </div>

                    <div>
                        <small>Email</small>
                        <span>{{ $booking->o_Email ?? '—' }}</span>
                    </div>

                    <div>
                        <small>Booking Fare</small>
                        <strong>{{ isset($booking->o_FullPrice) ? '$'.number_format((float)preg_replace('/[^0-9.\-]/','',(string)$booking->o_FullPrice),2) : '—' }}</strong>
                    </div>
                </div>
            </div>

            <div class="cb-card">
                <h2>Payment Summary</h2>

                <div class="cb-kv" style="grid-template-columns:1fr">
                    <div>
                        <small>Payment Status</small>
                        <strong>{{ $paymentMeta->payment_status ?? 'Untracked' }}</strong>
                    </div>

                    <div>
                        <small>Paid Amount</small>
                        <strong>{{ isset($paymentMeta->paid_amount) ? '$'.number_format((float)$paymentMeta->paid_amount,2) : '—' }}</strong>
                    </div>

                    <div>
                        <small>Refund Summary</small>
                        <strong>{{ isset($paymentMeta->refund_amount) ? '$'.number_format((float)$paymentMeta->refund_amount,2) : '—' }}</strong>
                    </div>

                    <div>
                        <small>Processor</small>
                        <strong>{{ $paymentMeta->processor ?? '—' }}</strong>
                    </div>
                </div>

                <p style="margin:16px 0 0;color:#64748b;font-size:13px;line-height:1.5">
                    CRM tracks chargeback workflow and evidence only. Processor/network submission and final dispute decisions happen externally.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
