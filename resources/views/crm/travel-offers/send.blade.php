@extends('crm.layout')

@section('content')
@php
    $symbols = ['USD'=>'$','CAD'=>'C$','GBP'=>'£','EUR'=>'€','INR'=>'₹'];
    $symbol = $symbols[$offer->currency] ?? ($offer->currency.' ');
    $totalTrips = (int)$offer->domestic_trips + (int)$offer->international_trips;
@endphp

<style>
.offer-send-wrap{max-width:980px;margin:0 auto}
.offer-send-head{display:flex;justify-content:space-between;align-items:flex-start;gap:20px;margin-bottom:22px}
.offer-send-head h1{margin:0;color:#17395f;font-size:25px;font-weight:800}
.offer-send-head p{margin:6px 0 0;color:#718096;font-size:12px}
.offer-back{display:inline-flex;align-items:center;gap:7px;text-decoration:none;color:#526b85;font-size:12px;font-weight:700}
.offer-send-grid{display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:20px}
.offer-send-card,.offer-summary{background:#fff;border:1px solid #e3e9f0;border-radius:15px;box-shadow:0 8px 26px rgba(20,48,82,.05)}
.offer-send-card{padding:25px}
.offer-summary{overflow:hidden}
.offer-summary-top{padding:24px;background:linear-gradient(135deg,#071f43,#124879);color:#fff}
.offer-summary-top span{display:block;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.09em;color:rgba(255,255,255,.65)}
.offer-summary-top h2{margin:8px 0 15px;font-size:21px;line-height:1.25}
.offer-summary-price{font-size:31px;font-weight:900;color:#fff}
.offer-summary-price small{font-size:10px;font-weight:700;color:rgba(255,255,255,.65)}
.offer-summary-body{padding:20px}
.offer-summary-row{display:flex;justify-content:space-between;gap:15px;padding:10px 0;border-bottom:1px solid #edf1f5;font-size:11px}
.offer-summary-row:last-child{border-bottom:0}
.offer-summary-row span{color:#78899c}
.offer-summary-row b{color:#294967;text-align:right}
.offer-field{margin-bottom:18px}
.offer-field label{display:block;margin-bottom:7px;color:#294967;font-size:11px;font-weight:800}
.offer-field input{width:100%;height:43px;border:1px solid #dbe3ec;border-radius:9px;padding:0 12px;color:#263e57;font-size:12px;outline:none}
.offer-field input:focus{border-color:#2e6ca5;box-shadow:0 0 0 3px rgba(46,108,165,.08)}
.offer-required{color:#d91d42}
.offer-errors{margin-bottom:18px;padding:12px 15px;border-radius:9px;background:#fff1f2;border:1px solid #ffd7dd;color:#a91f38;font-size:11px}
.offer-actions{display:flex;align-items:center;gap:10px;margin-top:24px}
.offer-send-btn{border:0;border-radius:9px;background:#d91d42;color:#fff;padding:12px 22px;font-size:11px;font-weight:900;cursor:pointer}
.offer-cancel{padding:11px 17px;border:1px solid #dce4ec;border-radius:9px;color:#536b83;text-decoration:none;font-size:11px;font-weight:800}
.offer-note{display:flex;gap:9px;margin-top:20px;padding:13px 14px;border-radius:9px;background:#f5f8fb;color:#657b91;font-size:10px;line-height:1.55}
.offer-note i{color:#2f6fa6;margin-top:2px}
@media(max-width:850px){.offer-send-grid{grid-template-columns:1fr}.offer-summary{order:-1}}
</style>

<div class="offer-send-wrap">

    <div class="offer-send-head">
        <div>
            <h1>Send Offer to Customer</h1>
            <p>Send a locked customer-specific version of {{ $offer->reference }}.</p>
        </div>

        <a class="offer-back" href="{{ route('crm.travel_offers.preview', $offer->id) }}">
            <i class="fa-solid fa-arrow-left"></i> Back to Preview
        </a>
    </div>

    <div class="offer-send-grid">

        <div class="offer-send-card">

            @if($errors->any())
                <div class="offer-errors">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if(session('crm_error'))
                <div class="offer-errors">{{ session('crm_error') }}</div>
            @endif

            <form method="POST" action="{{ route('crm.travel_offers.send.submit', $offer->id) }}">
                @csrf

                <div class="offer-field">
                    <label>Customer Name <span class="offer-required">*</span></label>
                    <input type="text" name="customer_name" value="{{ old('customer_name') }}" maxlength="150" required>
                </div>

                <div class="offer-field">
                    <label>Customer Email <span class="offer-required">*</span></label>
                    <input type="email" name="customer_email" value="{{ old('customer_email') }}" maxlength="190" required>
                </div>

                <div class="offer-field">
                    <label>Customer Phone</label>
                    <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" maxlength="50">
                </div>

                <div class="offer-field">
                    <label>Email Subject <span class="offer-required">*</span></label>
                    <input
                        type="text"
                        name="subject"
                        maxlength="255"
                        value="{{ old('subject', 'Exclusive FaresHunt Offer: '.$offer->title) }}"
                        required
                    >
                </div>

                <div class="offer-note">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>
                        Sending creates a locked snapshot of this offer. Future edits to the master offer will not change the version already sent to this customer.
                    </span>
                </div>

                <div class="offer-actions">
                    <button class="offer-send-btn" type="submit">
                        <i class="fa-solid fa-paper-plane me-1"></i> Send Offer
                    </button>

                    <a class="offer-cancel" href="{{ route('crm.travel_offers.preview', $offer->id) }}">
                        Cancel
                    </a>
                </div>
            </form>

        </div>

        <aside class="offer-summary">
            <div class="offer-summary-top">
                <span>Offer Being Sent</span>
                <h2>{{ $offer->title }}</h2>
                <div class="offer-summary-price">
                    {{ $symbol }}{{ number_format((float)$offer->price,0) }}
                    <small>{{ $offer->currency }} / passenger</small>
                </div>
            </div>

            <div class="offer-summary-body">
                <div class="offer-summary-row">
                    <span>Reference</span>
                    <b>{{ $offer->reference }}</b>
                </div>
                <div class="offer-summary-row">
                    <span>Included Trips</span>
                    <b>{{ $totalTrips }}</b>
                </div>
                <div class="offer-summary-row">
                    <span>Cabin</span>
                    <b>{{ $offer->cabin_class ?: 'As specified' }}</b>
                </div>
                <div class="offer-summary-row">
                    <span>Valid Until</span>
                    <b>{{ $offer->valid_until ? \Carbon\Carbon::parse($offer->valid_until)->format('M d, Y') : 'As specified' }}</b>
                </div>
            </div>
        </aside>

    </div>
</div>
@endsection
