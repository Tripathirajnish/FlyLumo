@extends('crm.layout')

@section('content')
@php
    $statusTone = function($status){
        return match($status){
            'Requested' => 'warning',
            'Under Review' => 'info',
            'Approved' => 'success',
            'Processing' => 'info',
            'Refunded', 'Closed' => 'success',
            'Rejected', 'Cancelled' => 'danger',
            default => 'neutral',
        };
    };
@endphp

<style>
.rf-detail{max-width:1400px;margin:0 auto}
.rf-detail-head{display:flex;justify-content:space-between;gap:20px;align-items:flex-start;margin-bottom:22px}
.rf-detail-head h1{margin:4px 0 6px;font-size:30px}
.rf-eyebrow{font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#64748b}
.rf-sub{color:#64748b;margin:0}
.rf-actions{display:flex;gap:8px;flex-wrap:wrap}
.rf-btn{display:inline-flex;align-items:center;gap:7px;padding:10px 14px;border:1px solid #dbe3ed;border-radius:9px;text-decoration:none;color:#0f172a;background:#fff;font-weight:600}
.rf-grid{display:grid;grid-template-columns:2fr 1fr;gap:20px}
.rf-card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:20px;margin-bottom:20px}
.rf-card h2{font-size:17px;margin:0 0 16px}
.rf-kv{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
.rf-kv div{min-width:0}
.rf-kv small{display:block;color:#64748b;margin-bottom:4px}
.rf-kv strong,.rf-kv span{word-break:break-word}
.rf-badge{display:inline-flex;padding:5px 9px;border-radius:999px;font-size:12px;font-weight:700}
.rf-badge.warning{background:#fff7ed;color:#c2410c}
.rf-badge.info{background:#eff6ff;color:#1d4ed8}
.rf-badge.success{background:#ecfdf5;color:#047857}
.rf-badge.danger{background:#fef2f2;color:#b91c1c}
.rf-badge.neutral{background:#f1f5f9;color:#475569}
.rf-note{white-space:pre-wrap;color:#334155;line-height:1.55}
.rf-event{padding:14px 0;border-bottom:1px solid #eef2f7}
.rf-event:last-child{border-bottom:0}
.rf-event-top{display:flex;justify-content:space-between;gap:15px}
.rf-event small{color:#64748b}
.rf-empty{color:#64748b}
@media(max-width:900px){
    .rf-grid{grid-template-columns:1fr}
    .rf-detail-head{flex-direction:column}
    .rf-kv{grid-template-columns:1fr}
}
</style>

<div class="rf-detail">
    <div class="rf-detail-head">
        <div>
            <span class="rf-eyebrow">Refund case</span>
            <h1>{{ $case->case_reference }}</h1>
            <p class="rf-sub">
                Booking #FH{{ $case->booking_id }}
                · {{ $case->refund_type }}
            </p>
        </div>

        <div class="rf-actions">
            <a class="rf-btn" href="{{ route('crm.refunds') }}">
                <i class="fa-solid fa-arrow-left"></i> Back to Refunds
            </a>
            <a class="rf-btn" href="{{ route('crm.booking_detail',$case->booking_id) }}">
                <i class="fa-solid fa-plane"></i> Booking
            </a>
            <a class="rf-btn" href="{{ route('crm.payment_detail',$case->booking_id) }}">
                <i class="fa-regular fa-credit-card"></i> Payment
            </a>
        </div>
    </div>

    <div class="rf-grid">
        <div>
            <div class="rf-card">
                <h2>Case Summary</h2>

                <div class="rf-kv">
                    <div>
                        <small>Status</small>
                        <span class="rf-badge {{ $statusTone($case->status) }}">{{ $case->status }}</span>
                    </div>

                    <div>
                        <small>Refund Type</small>
                        <strong>{{ $case->refund_type }}</strong>
                    </div>

                    <div>
                        <small>Requested Amount</small>
                        <strong>${{ number_format((float)$case->requested_amount,2) }} {{ $case->currency ?: 'USD' }}</strong>
                    </div>

                    <div>
                        <small>Approved Amount</small>
                        <strong>{{ $case->approved_amount !== null ? '$'.number_format((float)$case->approved_amount,2) : '—' }}</strong>
                    </div>

                    <div>
                        <small>Processed Amount</small>
                        <strong>{{ $case->processed_amount !== null ? '$'.number_format((float)$case->processed_amount,2) : '—' }}</strong>
                    </div>

                    <div>
                        <small>Requested At</small>
                        <strong>{{ $case->requested_at ? \Carbon\Carbon::parse($case->requested_at)->format('d M Y h:i A') : '—' }}</strong>
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
                        <small>Approved By</small>
                        <strong>{{ $case->approved_name ?: '—' }}</strong>
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
                        <small>Refund Reference</small>
                        <strong>{{ $case->refund_reference ?: '—' }}</strong>
                    </div>
                </div>
            </div>

            <div class="rf-card">
                <h2>Reason</h2>
                <div class="rf-note">{{ $case->reason ?: '—' }}</div>
            </div>

            <div class="rf-card">
                <h2>Internal Note</h2>
                <div class="rf-note">{{ $case->internal_note ?: '—' }}</div>
            </div>

            @if(app(\App\Services\CrmAccessService::class)->can((int) session('crm_admin_id'),'refunds.approve'))
                <div class="rf-card">
                    <h2>Refund Actions</h2>

                    @if($case->status === 'Requested')
                        <form method="POST" action="{{ route('crm.refund_review',$case->id) }}">
                            @csrf
                            <button class="rf-btn" type="submit">
                                <i class="fa-solid fa-magnifying-glass"></i> Start Review
                            </button>
                        </form>
                    @elseif($case->status === 'Under Review')
                        <form method="POST" action="{{ route('crm.refund_approve',$case->id) }}" style="margin-bottom:14px">
                            @csrf
                            <label style="display:block;margin-bottom:6px;font-weight:600">Approved Amount</label>
                            <input
                                type="number"
                                name="approved_amount"
                                step="0.01"
                                min="0.01"
                                max="{{ number_format((float)$case->requested_amount,2,'.','') }}"
                                value="{{ old('approved_amount', number_format((float)$case->requested_amount,2,'.','')) }}"
                                required
                                style="width:100%;max-width:280px;padding:10px;border:1px solid #dbe3ed;border-radius:8px;margin-bottom:10px"
                            >
                            <br>
                            <button class="rf-btn" type="submit">
                                <i class="fa-solid fa-check"></i> Approve Refund
                            </button>
                        </form>

                        <form method="POST" action="{{ route('crm.refund_reject',$case->id) }}">
                            @csrf
                            <label style="display:block;margin-bottom:6px;font-weight:600">Rejection Note</label>
                            <textarea
                                name="note"
                                rows="3"
                                required
                                style="width:100%;padding:10px;border:1px solid #dbe3ed;border-radius:8px;margin-bottom:10px"
                            >{{ old('note') }}</textarea>
                            <button class="rf-btn" type="submit">
                                <i class="fa-solid fa-xmark"></i> Reject Refund
                            </button>
                        </form>
                    @elseif($case->status === 'Approved')
                        <form method="POST" action="{{ route('crm.refund_process',$case->id) }}" style="margin-bottom:14px">
                            @csrf
                            <button class="rf-btn" type="submit">
                                <i class="fa-solid fa-gears"></i> Start Processing
                            </button>
                        </form>

                        <form method="POST" action="{{ route('crm.refund_cancel',$case->id) }}">
                            @csrf
                            <label style="display:block;margin-bottom:6px;font-weight:600">Cancellation Note</label>
                            <textarea
                                name="note"
                                rows="3"
                                required
                                style="width:100%;padding:10px;border:1px solid #dbe3ed;border-radius:8px;margin-bottom:10px"
                            >{{ old('note') }}</textarea>
                            <button class="rf-btn" type="submit">
                                <i class="fa-solid fa-ban"></i> Cancel Refund
                            </button>
                        </form>
                    @elseif($case->status === 'Processing')
                        <form method="POST" action="{{ route('crm.refund_complete',$case->id) }}">
                            @csrf

                            <label style="display:block;margin-bottom:6px;font-weight:600">Processed Amount</label>
                            <input
                                type="number"
                                name="processed_amount"
                                step="0.01"
                                min="0.01"
                                max="{{ number_format((float)($case->approved_amount ?? 0),2,'.','') }}"
                                value="{{ old('processed_amount', number_format((float)($case->approved_amount ?? 0),2,'.','')) }}"
                                required
                                style="width:100%;max-width:280px;padding:10px;border:1px solid #dbe3ed;border-radius:8px;margin-bottom:10px"
                            >

                            <label style="display:block;margin-bottom:6px;font-weight:600">Processor</label>
                            <input
                                type="text"
                                name="processor"
                                maxlength="100"
                                value="{{ old('processor',$case->processor) }}"
                                style="width:100%;padding:10px;border:1px solid #dbe3ed;border-radius:8px;margin-bottom:10px"
                            >

                            <label style="display:block;margin-bottom:6px;font-weight:600">Refund Reference</label>
                            <input
                                type="text"
                                name="refund_reference"
                                maxlength="120"
                                value="{{ old('refund_reference') }}"
                                required
                                style="width:100%;padding:10px;border:1px solid #dbe3ed;border-radius:8px;margin-bottom:10px"
                            >

                            <p style="font-size:13px;color:#b45309;line-height:1.5">
                                Use this only after the refund has actually been completed externally/manual processor-side.
                                CRM does not send the money automatically.
                            </p>

                            <button class="rf-btn" type="submit">
                                <i class="fa-solid fa-circle-check"></i> Mark Refunded
                            </button>
                        </form>
                    @elseif($case->status === 'Refunded')
                        <form method="POST" action="{{ route('crm.refund_close',$case->id) }}">
                            @csrf
                            <button class="rf-btn" type="submit">
                                <i class="fa-solid fa-lock"></i> Close Case
                            </button>
                        </form>
                    @else
                        <div class="rf-empty">
                            No workflow action is available for this status.
                        </div>
                    @endif
                </div>
            @endif

            <div class="rf-card">
                <h2>Case History</h2>

                @forelse($events as $event)
                    <div class="rf-event">
                        <div class="rf-event-top">
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

                        <small>
                            By {{ $event->admin_name ?: 'System' }}
                            @if($event->amount !== null)
                                · ${{ number_format((float)$event->amount,2) }}
                            @endif
                            @if($event->reference)
                                · Ref: {{ $event->reference }}
                            @endif
                        </small>

                        @if($event->details)
                            <div class="rf-note" style="margin-top:7px">{{ $event->details }}</div>
                        @endif
                    </div>
                @empty
                    <div class="rf-empty">No refund case events recorded yet.</div>
                @endforelse
            </div>
        </div>

        <div>
            <div class="rf-card">
                <h2>Booking</h2>

                <div class="rf-kv" style="grid-template-columns:1fr">
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

            <div class="rf-card">
                <h2>Payment Summary</h2>

                <div class="rf-kv" style="grid-template-columns:1fr">
                    <div>
                        <small>Payment Status</small>
                        <strong>{{ $paymentMeta->payment_status ?? 'Untracked' }}</strong>
                    </div>

                    <div>
                        <small>Paid Amount</small>
                        <strong>{{ isset($paymentMeta->paid_amount) ? '$'.number_format((float)$paymentMeta->paid_amount,2) : '—' }}</strong>
                    </div>

                    <div>
                        <small>Current Refund Summary</small>
                        <strong>{{ isset($paymentMeta->refund_amount) ? '$'.number_format((float)$paymentMeta->refund_amount,2) : '—' }}</strong>
                    </div>

                    <div>
                        <small>Processor</small>
                        <strong>{{ $paymentMeta->processor ?? '—' }}</strong>
                    </div>
                </div>

                <p style="margin:16px 0 0;color:#64748b;font-size:13px;line-height:1.5">
                    CRM refund cases track operational workflow only. Money is not moved automatically from this screen.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
