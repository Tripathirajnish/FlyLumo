@extends('layout')

@php
    // fh-confirm-v2 : public reference is always FH + id (same as email / CRM). DB prefix column is not touched.
    $ref = function_exists('fh_booking_ref') ? fh_booking_ref($info->id) : 'FH'.$info->id;
@endphp

@section('header-seo')
<meta name="robots" content="noindex, nofollow">
<title>Booking Confirmation {{ $ref }} | FaresHunt</title>
<meta name="description" content="Your FaresHunt booking confirmation and itinerary reference.">
<link href="/assets/css/tnb-booking-confirmation-v1.css" rel="stylesheet">
<style id="fh-confirm-v2">
/* Page CSS loads before the site CSS, so these rules use body.tnb-modern to win. */
body.tnb-modern .tnb-confirm-fare h2{color:#fff}
body.tnb-modern .tnb-confirm-fare>div{align-items:center;padding:10px 0}
body.tnb-modern .tnb-confirm-fare>div span{font-size:11px;color:#d3e0ec}
body.tnb-modern .tnb-confirm-fare>div strong{font-family:"Inter","Segoe UI",sans-serif;font-size:15px;font-weight:700}
body.tnb-modern .tnb-confirm-fare>div.fh-fare-total{border-bottom:0;padding-top:14px}
body.tnb-modern .tnb-confirm-fare>div.fh-fare-total span{font-size:12px;font-weight:700;color:#fff;text-transform:uppercase;letter-spacing:.06em}
body.tnb-modern .tnb-confirm-fare>div.fh-fare-total strong{font-family:Georgia,serif;font-size:30px;color:#fff}
body.tnb-modern .tnb-confirm-fare>small{color:#c3d3e2;font-size:10px}
body.tnb-modern .tnb-confirm-ref-grid small em{font-style:normal;text-transform:none;color:#c3d3e2}
.fh-seg-leg{display:inline-block;margin-left:6px;padding:2px 7px;border-radius:5px;background:rgba(255,255,255,.14)}
.fh-layover{display:flex;gap:10px;align-items:flex-start;margin:-4px 0 14px;padding:11px 14px;border:1px dashed #e7c98d;border-radius:12px;background:#fffaf0;color:#7a5512;font-size:11px;line-height:1.5}
.fh-layover i{margin-top:2px}
.fh-layover b{color:#5b3e08}
.fh-layover.fh-selftransfer{border-color:#f0b4c1;background:#fff4f6;color:#9b2c44}
.fh-layover.fh-selftransfer b{color:#7d1730}
.fh-layover small{display:block;margin-top:2px;font-size:10px}
.fh-contact-help{margin-top:14px;padding-top:12px;border-top:1px solid #edf1f5;font-size:10px;color:#6f7f91}
.fh-contact-help a{display:inline-flex;align-items:center;gap:7px;margin-top:7px;padding:8px 12px;border-radius:9px;background:#fff0f3;color:#d31d46!important;font-weight:800;font-size:11px;text-decoration:none!important}
</style>
@endsection

@section('mid-content')
@php
    $setting = getAllSetting();
    $sectorGo = (array) json_decode((string)$info->o_SectorGo,true);
    $sectorBack = (array) json_decode((string)$info->o_SectorBack,true);
    $passengers = (array) json_decode((string)$info->o_Passenger,true);
    $price = (array) json_decode((string)$info->o_Price,true);

    // Supplier dates come as "d.m.Y H:i" / DOB as "d.m.Y" - read that format first.
    $fhDate = function($raw){
        $raw = trim((string)$raw);
        if($raw === '') return null;
        foreach(['d.m.Y H:i','d.m.Y H:i:s','d.m.Y','Y-m-d H:i:s','Y-m-d'] as $fmt){
            try { $d = \Carbon\Carbon::createFromFormat('!'.$fmt, $raw); if($d && $d->format($fmt) === $raw) return $d; } catch(\Throwable $e) {}
        }
        try { return \Carbon\Carbon::parse($raw); } catch(\Throwable $e) { return null; }
    };
    $fhDur = function($mins){
        $mins = (int)$mins;
        if($mins <= 0) return '';
        $h = intdiv($mins,60); $m = $mins % 60;
        return ($h ? $h.'h ' : '').$m.'m';
    };
    $fhCabin = function($c){
        $map = ['Econom'=>'Economy','Economy'=>'Economy','PremiumEconomy'=>'Premium Economy','Business'=>'Business','First'=>'First'];
        $c = trim((string)$c);
        return $map[$c] ?? ($c !== '' ? $c : 'Cabin');
    };
    $fhBag = function($s){
        $count = $s['Bag_Count'] ?? null;
        $type  = strtolower(trim((string)($s['Bag_BType'] ?? '')));
        if($count === null || $count === '') return 'As per fare rules';
        if((int)$count <= 0 || $type === 'nil') return 'No checked bag';
        return (int)$count.' checked bag'.((int)$count > 1 ? 's' : '');
    };

    // Segments in order, with the leg (Departure / Return) and layover info between flights of the same leg.
    $segments = [];
    foreach([['Departure',$sectorGo],['Return',$sectorBack]] as [$legName,$legSegs]){
        $legSegs = array_values(array_filter($legSegs,'is_array'));
        foreach($legSegs as $i=>$s){
            $layover = null;
            if($i > 0){
                $prev = $legSegs[$i-1];
                $arr = $fhDate($prev['Arrival_Date'] ?? '');
                $dep = $fhDate($s['Departure_Date'] ?? '');
                $layover = [
                    'mins'   => ($arr && $dep && $dep->greaterThan($arr)) ? (int) $arr->diffInMinutes($dep) : 0,
                    'city'   => $prev['Arrival_City'] ?? $prev['Arrival_Name'] ?? '',
                    'iata'   => $prev['Arrival_Iata'] ?? '',
                    'change' => !empty($prev['Arrival_Iata']) && !empty($s['Departure_Iata']) && $prev['Arrival_Iata'] !== $s['Departure_Iata'],
                    'self'   => !empty($s['SelfConnect']) && $s['SelfConnect'] !== 'false',
                ];
            }
            $segments[] = ['s'=>$s,'leg'=>$legName,'layover'=>$layover];
        }
    }

    // Fare: add-ons that were selected + flight fare = total - add-ons (always adds up to the charged total).
    $selectedAddons = [];
    $addonsTotal = 0;
    foreach ($price as $addonKey => $addonRow) {
        if (!is_array($addonRow) || strtolower((string)($addonRow['Enable'] ?? 'No')) !== 'yes') {
            continue;
        }
        $amount = (float)($addonRow['SubTotal'] ?? $addonRow['Price'] ?? 0);
        $addonsTotal += $amount;
        $selectedAddons[] = [
            'name' => ucwords(str_replace('_', ' ', (string)$addonKey)),
            'amount' => $amount,
        ];
    }
    $fullPrice = (float)$info->o_FullPrice;
    $flightFare = round($fullPrice - $addonsTotal, 2);
    $paxCount = count($passengers) ?: 1;

    $status = $info->status ?: 'New Booking';
    $statusLower = strtolower($status);
    $issued = str_contains($statusLower,'ticket') || str_contains($statusLower,'issued') || $statusLower==='e-ticket';

    $supportPhone = $setting['header_contact'] ?? '';
    $supportPhoneDigits = preg_replace('/[^0-9+]/', '', (string)$supportPhone);
@endphp

<section class="tnb-confirm-page">
<div class="container">
    <div class="tnb-confirm-shell">
        <div class="tnb-confirm-top">
            <div class="tnb-confirm-success">
                <span class="tnb-confirm-check"><i class="fa-solid fa-check"></i></span>
                <div>
                    <span class="eyebrow">Your reservation is</span>
                    <h1>Confirmed</h1>
                    <p>Your booking has been received. A detailed confirmation has been sent to <strong>{{ $info->o_Email }}</strong>. Final e-ticket documents will follow after ticketing is completed.</p>
                </div>
            </div>
            <div class="tnb-confirm-ref">
                <span>Booking Reference</span>
                <strong>{{ $ref }}</strong>
                <div class="tnb-confirm-ref-grid">
                    <div><small>Booked on <em>(UTC)</em></small>{{ \Carbon\Carbon::parse($info->created_at)->format('d M Y, h:i A') }}</div>
                    <div><small>Passengers</small>{{ $paxCount }}</div>
                </div>
                <div class="tnb-mini-bars"></div>
            </div>
        </div>

        <div class="tnb-ticket-steps">
            <div class="done"><b><i class="fa-solid fa-check"></i></b><span>Confirmed<small>Reservation received</small></span></div>
            <i></i>
            <div class="{{ $issued?'done':'active' }}"><b>{{ $issued?'✓':'02' }}</b><span>Ticketing<small>{{ $issued?'Completed':'Being finalized' }}</small></span></div>
            <i></i>
            <div class="{{ $issued?'done':'' }}"><b>{{ $issued?'✓':'03' }}</b><span>E-Ticket<small>{{ $issued?'Issued':'Delivered after ticketing' }}</small></span></div>
        </div>

        <div class="tnb-confirm-section">
            <div class="eyebrow">Your journey</div><h2>Flight itinerary</h2>
            @unless($issued)<div class="tnb-ticketing-note"><i class="fa-regular fa-clock"></i> Ticketing is in progress. No action is needed unless our team contacts you.</div>@endunless

            @forelse($segments as $idx=>$row)
            @php $s = $row['s']; $lo = $row['layover']; $depD = $fhDate($s['Departure_Date'] ?? ''); $arrD = $fhDate($s['Arrival_Date'] ?? ''); @endphp

            @if($lo)
            <div class="fh-layover {{ $lo['self'] ? 'fh-selftransfer' : '' }}">
                <i class="{{ $lo['self'] ? 'fa-solid fa-triangle-exclamation' : 'fa-regular fa-clock' }}"></i>
                <div>
                    <b>{{ $lo['mins'] ? $fhDur($lo['mins']).' layover' : 'Layover' }}</b> in {{ $lo['city'] }}{{ $lo['iata'] ? ' ('.$lo['iata'].')' : '' }}
                    @if($lo['change'])<small>Change of airport: your next flight departs from {{ $s['Departure_Iata'] }}.</small>@endif
                    @if($lo['self'])<small><b>Self-transfer:</b> collect your checked bags, go through check-in and security again for your next flight.</small>@endif
                </div>
            </div>
            @endif

            <article class="tnb-confirm-segment">
                <div class="tnb-seg-head">
                    <span><b>{{ $idx+1 }}</b> Flight Segment <span class="fh-seg-leg">{{ $row['leg'] }}</span></span>
                    <span>{{ $fhCabin($s['FlightClass'] ?? '') }} · {{ $s['AirlineName'] ?? $s['Airline'] ?? 'Airline' }}</span>
                </div>
                <div class="tnb-seg-body">
                    <div class="tnb-seg-airline">
                        @if(!empty($s['AirlineLogo']))<img src="{{ $s['AirlineLogo'] }}" alt="">@endif
                        <b>{{ $s['AirlineName'] ?? $s['Airline'] ?? 'Airline' }}</b>
                        <small>{{ $s['FlightNum'] ?? '' }}</small>
                    </div>
                    <div class="tnb-seg-route">
                        <div><strong>{{ $s['Departure_Iata'] ?? '—' }}</strong><span>{{ $s['Departure_City'] ?? $s['Departure_Name'] ?? 'Departure' }}</span><time>{{ $depD ? $depD->format('D, d M · h:i A') : '—' }}</time></div>
                        <div class="tnb-seg-line"><i class="fa-solid fa-plane"></i><span>{{ !empty($s['FlightMinutes']) ? $fhDur($s['FlightMinutes']) : 'Flight' }}</span></div>
                        <div class="text-end"><strong>{{ $s['Arrival_Iata'] ?? '—' }}</strong><span>{{ $s['Arrival_City'] ?? $s['Arrival_Name'] ?? 'Arrival' }}</span><time>{{ $arrD ? $arrD->format('D, d M · h:i A') : '—' }}</time></div>
                    </div>
                    <div class="tnb-seg-stub">
                        <span>Booking Reference</span><b>{{ $ref }}</b>
                        <small>Cabin: {{ $fhCabin($s['FlightClass'] ?? '') }}</small>
                        <small>Baggage: {{ $fhBag($s) }}</small>
                        <div class="tnb-stub-bars"></div>
                    </div>
                </div>
            </article>
            @empty
                <div class="tnb-ticketing-note">Your itinerary has been stored with booking {{ $ref }}. Final segment information will be included with the e-ticket.</div>
            @endforelse
        </div>

        <div class="tnb-confirm-section">
            <div class="eyebrow">Passenger details</div><h2>Traveler</h2>
            <div class="tnb-passenger-list">
                @forelse($passengers as $i=>$p)
                    @php $dob = !empty($p['BirthDay']) ? $fhDate($p['BirthDay']) : null; @endphp
                    <div class="tnb-passenger-card">
                        <i class="fa-regular fa-user"></i>
                        <div><small>Passenger {{ $i+1 }} · {{ $p['AgeType'] ?? 'Traveler' }}</small><b>{{ trim(preg_replace('/\s+/', ' ', ($p['Title']??'').' '.($p['Name']??'').' '.($p['MiddleName']??'').' '.($p['Surname']??''))) }}</b></div>
                        <div>@if(!empty($p['GenderType']))<small>Gender</small><b>{{ $p['GenderType'] }}</b>@endif</div>
                        <div>@if($dob)<small>Date of birth</small><b>{{ $dob->format('d M Y') }}</b>@endif</div>
                    </div>
                @empty
                    <div class="tnb-passenger-card"><i class="fa-regular fa-user"></i><div><small>Passenger</small><b>{{ $info->o_Name }}</b></div></div>
                @endforelse
            </div>
        </div>

        <div class="tnb-confirm-bottom">
            <div class="tnb-confirm-contact">
                <div class="eyebrow">Stay in touch</div><h2>Contact</h2>
                <p><i class="fa-regular fa-envelope"></i><span><small>Email</small>{{ $info->o_Email }}</span></p>
                <p><i class="fa-solid fa-phone"></i><span><small>Mobile</small>{{ $info->o_Phone }}</span></p>
                @if($supportPhone)
                <div class="fh-contact-help">
                    Questions about this booking? Our travel experts are available 24/7.
                    <br><a href="tel:{{ $supportPhoneDigits }}"><i class="fa-solid fa-headset"></i> Call {{ $supportPhone }} · quote {{ $ref }}</a>
                </div>
                @endif
            </div>
            <div class="tnb-confirm-fare">
                <div class="eyebrow">Payment</div><h2>Fare summary</h2>
                @if($flightFare > 0)
                    <div><span>Flight fare · {{ $paxCount }} traveler{{ $paxCount > 1 ? 's' : '' }} (taxes &amp; fees incl.)</span><strong>${{ number_format($flightFare,2) }}</strong></div>
                @endif
                @foreach($selectedAddons as $addon)
                    <div><span>{{ $addon['name'] }}</span><strong>${{ number_format((float)$addon['amount'],2) }}</strong></div>
                @endforeach
                <div class="fh-fare-total"><span>Total</span><strong>${{ number_format($fullPrice,2) }}</strong></div>
                <small>All prices in USD · Booking reference: {{ $ref }}</small>
            </div>
        </div>

        <div class="tnb-what-next"><i class="fa-regular fa-file-lines"></i><div><b>What happens next</b><p>Your e-ticket and final travel documents will be sent to your email once ticketing is completed.</p></div></div>
        <div class="tnb-confirm-warning"><i class="fa-regular fa-circle-question"></i> This page is a booking confirmation and itinerary reference. It is not an airline-issued boarding pass and cannot be used for airport boarding.</div>

        <div class="tnb-confirm-actions">
            <a class="btn btn-primary" href="{{ url('/') }}"><i class="fa-solid fa-plane-departure me-2"></i>Book Another Flight</a>
            <a class="btn btn-light" href="{{ route('contact_us') }}"><i class="fa-solid fa-headset me-2"></i>Contact Support</a>
        </div>
    </div>
</div>
</section>
@endsection
