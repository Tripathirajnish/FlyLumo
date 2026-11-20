@php
    $ref = 'FH'.$booking->id;
    $status = $booking->status ?: 'New Booking';

    // Fareshunt branding/support. Prefer configured values.
    $supportEmail = $setting['content_emailid'] ?? config('mail.from.address', '');
    $supportPhone = $setting['header_contact'] ?? '';
    $supportWhatsApp = $setting['content_whatsapp'] ?? '';

    $allSegments = collect($sectorGo)->values()->concat(collect($sectorBack)->values())->values();

    $statusText = strtolower(trim((string)$status));
    $isIssued = in_array($statusText, ['issued', 'ticketed', 'e-ticket'], true);
    $isCancelled = str_contains($statusText, 'cancel');
    $isPaymentPending = in_array($statusText, ['payment pending', 'payment declined'], true);
    $isBookingReceived = in_array($statusText, ['fresh', 'new booking', 'pending', 'incomplete'], true);

    $isPaymentDeclined = $statusText === 'payment declined';

    $heroWord = $isCancelled ? 'CANCELLED'
        : ($isIssued ? 'TICKET ISSUED'
        : ($isPaymentDeclined ? 'PAYMENT DECLINED'
        : ($isPaymentPending ? 'PAYMENT PENDING'
        : ($isBookingReceived ? 'RECEIVED' : 'PROCESSING'))));

    $fmtDate = function($v, $format='D, d M Y') {
        try { return \Carbon\Carbon::parse($v)->format($format); }
        catch (\Throwable $e) { return $v ?: '—'; }
    };

    $money = function($v) {
        if ($v === null || $v === '') return '—';
        if (is_numeric($v)) return '$'.number_format((float)$v, 2);
        return (string)$v;
    };

    $passengerCount = is_countable($passengers) ? count($passengers) : 0;

    $price = (array) json_decode((string)$booking->o_Price, true);
    $selectedAddons = [];

    foreach ($price as $addonKey => $addonRow) {
        if (!is_array($addonRow) || strtolower((string)($addonRow['Enable'] ?? 'No')) !== 'yes') {
            continue;
        }

        $selectedAddons[] = [
            'name' => ucwords(str_replace('_', ' ', (string)$addonKey)),
            'amount' => $addonRow['SubTotal'] ?? $addonRow['Price'] ?? null,
        ];
    }

    $fullName = function($p) {
        return implode(' ', array_filter([
            trim((string)($p['Title'] ?? '')),
            trim((string)($p['Name'] ?? '')),
            trim((string)($p['MiddleName'] ?? '')),
            trim((string)($p['Surname'] ?? '')),
        ], fn($part) => $part !== ''));
    };

    $segmentAirline = function($s) {
        return $s['AirlineName'] ?? $s['Airline'] ?? $s['MarketingAirline'] ?? 'Airline';
    };

    $segmentFlight = function($s) {
        return $s['FlightNum'] ?? $s['FlightNumber'] ?? '';
    };

    // Resolve a 2-character airline code from explicit API fields first,
    // then fall back to the flight-number prefix (e.g. AA-2917, UA57, F9-1483).
    $segmentAirlineCode = function($s) use ($segmentFlight) {
        $explicit = $s['AirlineCode']
            ?? $s['MarketingAirlineCode']
            ?? $s['OperatingAirlineCode']
            ?? $s['CarrierCode']
            ?? $s['Airline_Iata']
            ?? $s['AirlineIata']
            ?? '';

        $explicit = strtoupper(trim((string)$explicit));
        if ($explicit !== '') {
            return preg_replace('/[^A-Z0-9]/', '', $explicit);
        }

        $flight = strtoupper(trim((string)$segmentFlight($s)));
        if (preg_match('/^([A-Z0-9]{2})[\s\-]?[0-9]/', $flight, $m)) {
            return $m[1];
        }

        return '';
    };

    // Add matching PNG files under public/mail_images/airlines/.
    // If a logo file is missing, the template safely falls back to the airline code badge.
    $airlineLogoMap = [
        'AA' => 'american-airlines.png',
        'UA' => 'united-airlines.png',
        'DL' => 'delta-air-lines.png',
        'F9' => 'frontier-airlines.png',
        'B6' => 'jetblue.png',
        'NK' => 'spirit-airlines.png',
        'WN' => 'southwest-airlines.png',
        'AS' => 'alaska-airlines.png',
        'AC' => 'air-canada.png',
        'BA' => 'british-airways.png',
        'VS' => 'virgin-atlantic.png',
        'LH' => 'lufthansa.png',
        'AF' => 'air-france.png',
        'KL' => 'klm.png',
        'LX' => 'swiss.png',
        'TK' => 'turkish-airlines.png',
        'EK' => 'emirates.png',
        'QR' => 'qatar-airways.png',
        'AI' => 'air-india.png',
        'EY' => 'etihad-airways.png',
        'SQ' => 'singapore-airlines.png',
        'CX' => 'cathay-pacific.png',
        'QF' => 'qantas.png',
        'JL' => 'japan-airlines.png',
        'NH' => 'ana.png',
    ];

    $segmentAirlineLogo = function($s) use ($segmentAirlineCode, $airlineLogoMap) {
        $code = $segmentAirlineCode($s);
        if ($code === '' || empty($airlineLogoMap[$code])) return null;

        $relative = 'mail_images/airlines/'.$airlineLogoMap[$code];
        return file_exists(public_path($relative)) ? asset($relative) : null;
    };

    // Normalise cabin names coming from the supplier (e.g. "ECONOM", "ECONOMY", "BUSINESS").
    $segmentCabin = function($s) {
        $raw = trim((string)($s['FlightClass'] ?? $s['Cabin'] ?? $s['CabinClass'] ?? ''));
        if ($raw === '') return '—';
        $u = strtoupper($raw);
        if (str_starts_with($u, 'PREM')) return 'Premium Economy';
        if (str_starts_with($u, 'ECO') || $u === 'COACH') return 'Economy';
        if (str_starts_with($u, 'BUS')) return 'Business';
        if (str_starts_with($u, 'FIR')) return 'First';
        return ucwords(strtolower($raw));
    };

    $segmentBookingClass = function($s) {
        return trim((string)($s['BookingClass'] ?? $s['Class'] ?? ''));
    };

    $segmentStatus = $isCancelled ? 'Cancelled'
        : ($isIssued ? 'Ticketed'
        : ($isPaymentDeclined ? 'Payment Declined'
        : ($isPaymentPending ? 'Payment Pending'
        : ($isBookingReceived ? 'Booking Received' : 'Processing'))));

    $heroColor = ($isCancelled || $isPaymentDeclined) ? '#B4233A' : ($isIssued ? '#16855e' : '#9A6C28');
    $heroTint = ($isCancelled || $isPaymentDeclined) ? '#FFF5F6' : ($isIssued ? '#F6FCF9' : '#FAF7F0');
    $heroBorder = ($isCancelled || $isPaymentDeclined) ? '#F2C8D2' : ($isIssued ? '#D6EBE2' : '#E8D9BF');

    $outboundCount = collect($sectorGo)->count();
    $hasReturn = collect($sectorBack)->count() > 0;

    $initials = function($p) {
        $a = trim((string)($p['Name'] ?? ''));
        $b = trim((string)($p['Surname'] ?? ''));
        $out = ($a !== '' ? mb_substr($a, 0, 1) : '').($b !== '' ? mb_substr($b, 0, 1) : '');
        return $out !== '' ? mb_strtoupper($out) : 'P';
    };

    $billingLine = collect([
        $billing['billing_address'] ?? $billing['address'] ?? null,
        $billing['billing_city'] ?? null,
        (($billing['billing_country'] ?? '') === 'United States' ? ($billing['billing_state_97'] ?? null) : ($billing['billing_state'] ?? null)),
        $billing['billing_pincode'] ?? null,
        $billing['billing_country'] ?? null,
    ])->map(fn($v) => trim((string)$v))->filter(fn($v) => $v !== '')->implode(', ');

    $mailBase = rtrim(config('app.url'), '/');

    // ---- Real Code 128 (set B) barcode of the booking reference, rendered as an email-safe HTML table ----
    $code128Bars = function(string $text) {
        $patterns = ['212222','222122','222221','121223','121322','131222','122213','122312','132212','221213','221312','231212','112232','122132','122231','113222','123122','123221','223211','221132','221231','213212','223112','312131','311222','321122','321221','312212','322112','322211','212123','212321','232121','111323','131123','131321','112313','132113','132311','211313','231113','231311','112133','112331','132131','113123','113321','133121','313121','211331','231131','213113','213311','213131','311123','311321','331121','312113','312311','332111','314111','221411','431111','111224','111422','121124','121421','141122','141221','112214','112412','122114','122411','142112','142211','241211','221114','413111','241112','134111','111242','121142','121241','114212','124112','124211','411212','421112','421211','212141','214121','412121','111143','111341','131141','114113','114311','411113','411311','113141','114131','311141','411131','211412','211214','211232'];
        $codes = [104]; // Start B
        foreach (str_split($text) as $ch) {
            $v = ord($ch) - 32;
            if ($v < 0 || $v > 94) continue;
            $codes[] = $v;
        }
        $sum = $codes[0];
        for ($i = 1; $i < count($codes); $i++) $sum += $codes[$i] * $i;
        $codes[] = $sum % 103;
        $widths = '';
        foreach ($codes as $c) $widths .= $patterns[$c];
        $widths .= '2331112'; // Stop
        $bars = [];
        foreach (str_split($widths) as $k => $w) $bars[] = ['w' => (int)$w, 'dark' => $k % 2 === 0];
        return $bars;
    };
    $refBarcode = $code128Bars($ref);

    // ---- Card used for payment: only brand + last 4 digits are ever printed ----
    $cardBrand = null;
    $cardLast4 = null;
    try {
        $credit = (array) json_decode((string) ($booking->o_Credit ?? ''), true);
        $pan = preg_replace('/\D+/', '', (string) ($credit['creditcard_no'] ?? $credit['card_number'] ?? ''));
        $cardLast4 = !empty($credit['card_last4']) ? (string) $credit['card_last4'] : ($pan !== '' ? substr($pan, -4) : null);
        $cardBrand = $credit['card_brand'] ?? null;
        if (!$cardBrand && $pan !== '') {
            if (str_starts_with($pan, '4')) $cardBrand = 'Visa';
            elseif (preg_match('/^(5[1-5]|2[2-7])/', $pan)) $cardBrand = 'Mastercard';
            elseif (preg_match('/^3[47]/', $pan)) $cardBrand = 'American Express';
            elseif (preg_match('/^6(?:011|5)/', $pan)) $cardBrand = 'Discover';
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('booking_card_metadata')) {
            $cardMeta = \Illuminate\Support\Facades\DB::table('booking_card_metadata')->where('booking_id', $booking->id)->first();
            if ($cardMeta) {
                $cardLast4 = $cardMeta->last4 ?: $cardLast4;
                $cardBrand = $cardMeta->brand ?: $cardBrand;
            }
        }
    } catch (\Throwable $e) {
        // Card line is optional — never break the email over it.
    }
    $cardLast4 = preg_match('/^\d{4}$/', (string) $cardLast4) ? (string) $cardLast4 : null;
    $cardBrand = $cardBrand && $cardBrand !== 'Card' ? $cardBrand : 'Card';

    $segmentBaggage = function($s) {
        if (isset($s['Bag_Count']) && $s['Bag_Count'] !== '') return $s['Bag_Count'].' pcs';
        return $s['Baggage'] ?? $s['BaggageAllowance'] ?? 'As per fare';
    };

    $segmentLayover = function($current, $next) {
        if (empty($current['Arrival_Date']) || empty($next['Departure_Date'])) return null;

        try {
            $arrival = \Carbon\Carbon::createFromFormat('d.m.Y H:i', $current['Arrival_Date']);
            $departure = \Carbon\Carbon::createFromFormat('d.m.Y H:i', $next['Departure_Date']);

            if ($departure->lessThanOrEqualTo($arrival)) return null;

            $minutes = $arrival->diffInMinutes($departure);
            $hours = intdiv($minutes, 60);
            $mins = $minutes % 60;

            $duration = ($hours > 0 ? $hours.'h ' : '').($mins > 0 ? $mins.'m' : '');
            $airport = $current['Arrival_Iata'] ?? '';
            $city = $current['Arrival_City'] ?? $current['Arrival_Name'] ?? $airport;

            return [
                'minutes' => $minutes,
                'duration' => trim($duration),
                'airport' => $airport,
                'city' => $city,
            ];
        } catch (\Throwable $e) {
            return null;
        }
    };

    // Calculate connections separately for outbound and return journeys.
    // This prevents the gap between outbound and return flights being shown as a layover.
    $layoversAfter = [];
    $globalSegmentIndex = 0;

    foreach ([collect($sectorGo)->values(), collect($sectorBack)->values()] as $journeySegments) {
        $journeyCount = $journeySegments->count();

        for ($journeyIndex = 0; $journeyIndex < $journeyCount; $journeyIndex++) {
            if ($journeyIndex < $journeyCount - 1) {
                $currentSegment = $journeySegments[$journeyIndex];
                $nextSegment = $journeySegments[$journeyIndex + 1];

                $arrivalIata = strtoupper(trim((string)($currentSegment['Arrival_Iata'] ?? '')));
                $nextDepartureIata = strtoupper(trim((string)($nextSegment['Departure_Iata'] ?? '')));

                if ($arrivalIata !== '' && $arrivalIata === $nextDepartureIata) {
                    $layover = $segmentLayover($currentSegment, $nextSegment);

                    if ($layover) {
                        $layoversAfter[$globalSegmentIndex] = $layover;
                    }
                }
            }

            $globalSegmentIndex++;
        }
    }

@endphp
<!-- Fares Hunt Booking Confirmation V6.0 (refreshed UI) -->
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="x-apple-disable-message-reformatting">
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<title>Fareshunt Booking Confirmation {{ $ref }}</title>
<style>
    body { margin:0; padding:0; -webkit-text-size-adjust:100%; }
    a { color:#143d6a; }
    @media only screen and (max-width:620px) {
        .fh-px { padding-left:16px !important; padding-right:16px !important; }
        .fh-stack { display:block !important; width:100% !important; padding-left:0 !important; padding-right:0 !important; }
        .fh-gap { padding-top:12px !important; }
        .fh-hide-sm { display:none !important; }
        .fh-iata { font-size:26px !important; }
        .fh-hero-word { font-size:30px !important; }
        .fh-cell-sm { display:inline-block !important; width:50% !important; padding-bottom:10px !important; box-sizing:border-box; }
        .fh-center-sm { text-align:center !important; }
        .fh-pl { padding-left:16px !important; }
    }
</style>
</head>
<body style="margin:0;padding:0;background:#f4f6f9;color:#132b4b;font-family:Inter,'Segoe UI',Arial,sans-serif;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">
Fares Hunt booking {{ $ref }} is {{ strtolower($heroWord) }} — itinerary, passenger and ticketing details inside.
</div>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;background:#f4f6f9;">
<tr>
<td align="center" style="padding:20px 8px;">

<table role="presentation" width="760" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:760px;background:#ffffff;border:1px solid #e4e9f0;border-radius:20px;overflow:hidden;">

<!-- LUXURY HEADER: STATUS AWARE -->
@if($isIssued)
<tr>
<td style="padding:0;background:#ffffff;">
    <img
        src="{{ $mailBase . '/mail_images/booking-confirmation/fareshunt-luxury-banner-draft.jpg' }}"
        alt="Fares Hunt — Your Journey is Confirmed"
        width="760"
        style="display:block;width:100%;max-width:760px;height:auto;border:0;outline:none;text-decoration:none;"
    >
</td>
</tr>
@else
<tr>
<td align="center" style="padding:34px 24px;background:#081D33;">
    <div style="color:#D9B77A;font-size:14px;font-weight:700;letter-spacing:4px;">
        FARES HUNT
    </div>
    <div style="color:#ffffff;font-family:Georgia,'Times New Roman',serif;font-size:30px;line-height:1.3;margin-top:16px;">
        Booking Update
    </div>
    <div style="color:#DCE5EE;font-size:13px;line-height:1.6;margin-top:10px;">
        Your reservation status: {{ $heroWord }}
    </div>
</td>
</tr>
@endif

<!-- HERO -->
<tr>
<td class="fh-px" style="padding:26px 30px 18px;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td class="fh-stack" width="57%" valign="top" style="padding-right:8px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:{{ $heroTint }};border:1px solid {{ $heroBorder }};border-radius:16px;">
                    <tr>
                        <td style="padding:22px 22px 20px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td width="24" height="24" align="center" valign="middle" style="width:24px;height:24px;border-radius:12px;background:{{ $heroColor }};color:#ffffff;font-size:13px;font-weight:700;line-height:24px;">{!! ($isCancelled || $isPaymentDeclined) ? '&#10005;' : ($isIssued ? '&#10003;' : '&#8226;') !!}</td>
                                    <td style="padding-left:10px;font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:{{ $heroColor }};font-weight:700;">Your reservation is</td>
                                </tr>
                            </table>
                            <div class="fh-hero-word" style="margin:10px 0 10px;color:{{ $heroColor }};font-size:36px;font-weight:700;line-height:1.05;letter-spacing:-.02em;">{{ $heroWord }}</div>
                            <div style="color:#4f6074;font-size:13px;line-height:1.65;">
                                @if($isCancelled)
                                    Your reservation has been cancelled. Please contact our support team for assistance.
                                @elseif($isIssued)
                                    Your booking is marked as ticket issued. Please refer to your ticketing information for the latest details.
                                @elseif($isPaymentDeclined)
                                    Your payment was declined. Your airline ticket has not been confirmed as issued. Please contact our support team for assistance.
                                @elseif($isPaymentPending)
                                    We have received your booking request. Payment requires further verification or action before ticket issuance can be confirmed.
                                @elseif($isBookingReceived)
                                    Thank you for choosing FARES HUNT. We have received your booking request and our team is reviewing the reservation. Your airline ticket has not yet been confirmed as issued.
                                @else
                                    Your booking is being processed. We will share further ticketing updates as they become available.
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>
            </td>

            <td class="fh-stack fh-gap" width="43%" valign="top" style="padding-left:8px;">
                @php
                    $ageTypeCounts = collect($passengers)->countBy(function($p) {
                        return trim((string)($p['AgeType'] ?? 'Traveler')) ?: 'Traveler';
                    });

                    $passengerSummary = $ageTypeCounts->map(function($count, $type) {
                        $label = $type;
                        if ($count > 1) {
                            $label = strtolower($type) === 'child' ? 'Children' : $type.'s';
                        }
                        return $count.' '.$label;
                    })->implode(', ');
                @endphp
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#081D33;border:1px solid #C9A66B;border-radius:16px;">
                    <tr>
                        <td style="padding:22px 22px 20px;color:#ffffff;">
                            <div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#D9C8AA;">Booking reference</div>
                            <div style="margin:8px 0 18px;color:#C9A66B;font-size:30px;font-weight:700;letter-spacing:.04em;line-height:1.1;">{{ $ref }}</div>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-top:1px solid #46546A;">
                                <tr>
                                    <td width="62%" valign="top" style="padding-top:14px;padding-right:8px;">
                                        <div style="font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:#D9C8AA;">Booked on</div>
                                        <div style="font-size:13px;line-height:1.45;margin-top:4px;color:#ffffff;">{{ $fmtDate($booking->created_at,'d M Y') }}<br><span style="color:#E2D8C8;font-size:12px;">{{ $fmtDate($booking->created_at,'h:i A') }}</span></div>
                                    </td>
                                    <td width="38%" valign="top" style="padding-top:14px;">
                                        <div style="font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:#D9C8AA;">Passenger(s)</div>
                                        <div style="font-size:13px;line-height:1.45;margin-top:4px;color:#ffffff;">{{ $passengerSummary ?: (($passengerCount ?: 1).' Adult') }}</div>
                                    </td>
                                </tr>
                            </table>
                            <!-- Code 128 barcode of booking reference -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:16px;background:#ffffff;border-radius:8px;">
                                <tr>
                                    <td align="center" style="padding:10px 12px 6px;">
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center">
                                            <tr>
                                                @foreach($refBarcode as $bar)<td width="{{ $bar['w'] * 2 }}" height="44" style="width:{{ $bar['w'] * 2 }}px;min-width:{{ $bar['w'] * 2 }}px;height:44px;padding:0;background:{{ $bar['dark'] ? '#0b1f38' : '#ffffff' }};font-size:0;line-height:0;">&nbsp;</td>@endforeach
                                            </tr>
                                        </table>
                                        <div style="margin-top:5px;font-size:11px;color:#081D33;letter-spacing:.3em;font-weight:600;">{{ $ref }}</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</td>
</tr>

<!-- PROGRESS -->
@if(!$isCancelled)
@php
    // done | current | pending
    $steps = [
        ['label' => 'Received', 'note' => 'Booking request received', 'state' => 'done', 'num' => 1],
        ['label' => 'Review', 'note' => $isIssued ? 'Review completed' : 'Under review', 'state' => $isIssued ? 'done' : 'current', 'num' => 2],
        ['label' => 'Ticketing', 'note' => $isIssued ? 'Ticket issued' : 'Awaiting issuance', 'state' => $isIssued ? 'done' : 'pending', 'num' => 3],
        ['label' => 'E-ticket', 'note' => 'Check ticket details', 'state' => 'pending', 'num' => 4],
    ];
@endphp
<tr>
<td class="fh-px" style="padding:0 30px 22px;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#FAF7F0;border:1px solid #E8D9BF;border-radius:16px;">
        <tr>
            @foreach($steps as $si => $step)
                @php
                    $circleBg = $step['state'] === 'done' ? '#16855e' : ($step['state'] === 'current' ? '#F6EAD5' : '#ffffff');
                    $circleBorder = $step['state'] === 'done' ? '#16855e' : ($step['state'] === 'current' ? '#C9A66B' : '#D7CCBA');
                    $circleColor = $step['state'] === 'done' ? '#ffffff' : ($step['state'] === 'current' ? '#866329' : '#718096');
                    $labelColor = $step['state'] === 'done' ? '#16855e' : ($step['state'] === 'current' ? '#866329' : '#536477');
                @endphp
                <td align="center" width="25%" valign="top" style="padding:16px 6px 14px;{{ $si > 0 ? 'border-left:1px solid #E8D9BF;' : '' }}">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center">
                        <tr>
                            <td width="26" height="26" align="center" valign="middle" style="width:26px;height:26px;border-radius:13px;background:{{ $circleBg }};border:2px solid {{ $circleBorder }};color:{{ $circleColor }};font-size:12px;font-weight:700;line-height:26px;">{!! $step['state'] === 'done' ? '&#10003;' : $step['num'] !!}</td>
                        </tr>
                    </table>
                    <div style="margin-top:8px;font-size:11px;color:{{ $labelColor }};font-weight:700;letter-spacing:.06em;text-transform:uppercase;">{{ $step['label'] }}</div>
                    <div class="fh-hide-sm" style="font-size:11px;color:#687586;margin-top:3px;line-height:1.4;">{{ $step['note'] }}</div>
                </td>
            @endforeach
        </tr>
    </table>
</td>
</tr>
@endif

@if(!$isIssued && !$isCancelled)
<tr>
<td class="fh-px" style="padding:0 30px 24px;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#FAF7F0;border:1px solid #E8D9BF;border-left:4px solid #C9A66B;border-radius:10px;">
        <tr>
            <td style="padding:16px 18px;color:#536477;font-size:13px;line-height:1.7;">
                @if($isPaymentDeclined)
                    <div style="font-weight:700;font-size:14px;color:#081D33;margin-bottom:5px;">Payment declined</div>
                    Your payment could not be completed. Please contact our support team for assistance before attempting another payment.
                @elseif($isPaymentPending)
                    <div style="font-weight:700;font-size:14px;color:#081D33;margin-bottom:5px;">Payment review required</div>
                    Your booking request has been received. Payment verification or further action may be required before ticket issuance.
                @elseif($isBookingReceived)
                    <div style="font-weight:700;font-size:14px;color:#081D33;margin-bottom:5px;">Reservation request received</div>
                    Our team is reviewing your booking request. Airline ticket issuance has not yet been confirmed.
                @else
                    <div style="font-weight:700;font-size:14px;color:#081D33;margin-bottom:5px;">Reservation processing</div>
                    Your booking is being processed. We will provide ticketing updates as they become available.
                @endif
            </td>
        </tr>
    </table>
</td>
</tr>
@endif

<!-- ITINERARY TITLE -->
<tr>
<td class="fh-px" style="padding:0 30px 12px;">
    <div style="font-size:11px;letter-spacing:.16em;text-transform:uppercase;color:#B48A4A;font-weight:700;">Your journey</div>
    <div style="font-size:22px;font-weight:700;color:#081D33;letter-spacing:-.015em;margin-top:4px;line-height:1.2;">Flight itinerary</div>
</td>
</tr>

<!-- SEGMENTS -->
@forelse($allSegments as $idx => $s)
@php
    $airlineCode = $segmentAirlineCode($s);
    $airlineLogo = $segmentAirlineLogo($s);
    $leg = $hasReturn ? ($idx < $outboundCount ? 'Outbound' : 'Return') : null;
    $cabin = $segmentCabin($s);
    $bookingClass = $segmentBookingClass($s);
    $durationText = null;
    if (!empty($s['FlightMinutes'])) {
        $durationMinutes = (int) $s['FlightMinutes'];
        $durationHours = intdiv($durationMinutes, 60);
        $durationRemainder = $durationMinutes % 60;
        $durationText = trim(($durationHours > 0 ? $durationHours.'h ' : '').($durationRemainder > 0 ? $durationRemainder.'m' : ($durationHours > 0 ? '' : '0m')));
    }
    $stopsText = !empty($s['Stops']) && $s['Stops'] != 0 ? $s['Stops'].' stop'.($s['Stops'] > 1 ? 's' : '') : 'Direct';
    $statusColor = in_array($segmentStatus, ['Cancelled', 'Payment Declined'], true)
        ? '#B4233A'
        : ($isIssued ? '#16855e' : '#9A6C28');
@endphp
<tr>
<td class="fh-px" style="padding:0 30px 14px;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border:1px solid #E8D9BF;border-radius:14px;overflow:hidden;">
        <!-- segment bar -->
        <tr>
            <td style="background:#081D33;color:#ffffff;padding:11px 16px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td style="font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#ffffff;">
                            Flight {{ $idx+1 }}@if($leg)<span style="color:#D9C8AA;font-weight:600;"> &middot; {{ $leg }}</span>@endif
                        </td>
                        <td align="right" style="font-size:12px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:#E8DCC9;white-space:nowrap;">
                            {{ strtoupper($fmtDate($s['Departure_Date'] ?? null,'D, d M Y')) }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td style="padding:16px 18px 6px;">
                <!-- airline row -->
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td valign="middle" width="44" style="padding-right:10px;">
                            @if($airlineLogo)
                                <img src="{{ $airlineLogo }}" alt="{{ $segmentAirline($s) }}" width="34" style="display:block;width:34px;max-width:34px;height:auto;max-height:24px;border:0;">
                            @else
                                <span style="display:inline-block;background:#FAF7F0;color:#081D33;border-radius:6px;padding:5px 7px;font-size:11px;font-weight:700;letter-spacing:.04em;">{{ $airlineCode ?: 'FL' }}</span>
                            @endif
                        </td>
                        <td valign="middle" style="font-size:13px;color:#081D33;font-weight:600;">
                            {{ $segmentAirline($s) }}
                            @if($segmentFlight($s))<span style="color:#687586;font-weight:400;"> &middot; {{ $segmentFlight($s) }}</span>@endif
                        </td>
                        <td valign="middle" align="right">
                            <span style="display:inline-block;border:1px solid {{ $statusColor }};color:{{ $statusColor }};border-radius:20px;padding:3px 10px;font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;white-space:nowrap;">{{ $segmentStatus }}</span>
                        </td>
                    </tr>
                </table>

                <!-- route row -->
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:14px;">
                    <tr>
                        <td width="36%" valign="top">
                            <div class="fh-iata" style="font-size:32px;font-weight:700;color:#081D33;letter-spacing:-.01em;line-height:1.1;">{{ $s['Departure_Iata'] ?? '—' }}</div>
                            <div style="font-size:12px;color:#536477;margin-top:3px;">{{ $s['Departure_City'] ?? $s['Departure_Name'] ?? 'Departure' }}</div>
                            <div style="font-size:18px;color:#B48A4A;font-weight:700;margin-top:10px;">{{ $fmtDate($s['Departure_Date'] ?? null,'h:i A') }}</div>
                            <div style="font-size:11px;color:#687586;margin-top:2px;text-transform:uppercase;letter-spacing:.04em;">{{ $fmtDate($s['Departure_Date'] ?? null,'D, d M') }}</div>
                        </td>

                        <td width="28%" align="center" valign="middle" style="padding:0 6px;">
                            <div style="font-size:12px;color:#536477;font-weight:600;">{{ $durationText ?: 'Flight' }}</div>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:6px 0;">
                                <tr>
                                    <td style="border-top:1px dashed #C9A66B;font-size:0;line-height:0;">&nbsp;</td>
                                    <td width="22" align="center" style="color:#B48A4A;font-size:16px;line-height:16px;padding:0 4px;">&#9992;</td>
                                    <td style="border-top:1px dashed #C9A66B;font-size:0;line-height:0;">&nbsp;</td>
                                </tr>
                            </table>
                            <div style="font-size:11px;color:#16855e;text-transform:uppercase;letter-spacing:.08em;font-weight:700;">{{ $stopsText }}</div>
                        </td>

                        <td width="36%" align="right" valign="top">
                            <div class="fh-iata" style="font-size:32px;font-weight:700;color:#081D33;letter-spacing:-.01em;line-height:1.1;">{{ $s['Arrival_Iata'] ?? '—' }}</div>
                            <div style="font-size:12px;color:#536477;margin-top:3px;">{{ $s['Arrival_City'] ?? $s['Arrival_Name'] ?? 'Arrival' }}</div>
                            <div style="font-size:18px;color:#B48A4A;font-weight:700;margin-top:10px;">{{ $fmtDate($s['Arrival_Date'] ?? null,'h:i A') }}</div>
                            <div style="font-size:11px;color:#687586;margin-top:2px;text-transform:uppercase;letter-spacing:.04em;">{{ $fmtDate($s['Arrival_Date'] ?? null,'D, d M') }}</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- details strip -->
        <tr>
            <td style="padding:12px 18px 16px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#FAF7F0;border-radius:10px;">
                    <tr>
                        <td class="fh-cell-sm" width="25%" valign="top" style="padding:10px 12px;">
                            <div style="font-size:10px;color:#687586;text-transform:uppercase;letter-spacing:.08em;">Cabin</div>
                            <div style="font-size:13px;color:#081D33;font-weight:600;margin-top:3px;">{{ $cabin }}@if($bookingClass !== '')<span style="color:#687586;font-weight:400;"> ({{ $bookingClass }})</span>@endif</div>
                        </td>
                        <td class="fh-cell-sm" width="25%" valign="top" style="padding:10px 12px;">
                            <div style="font-size:10px;color:#687586;text-transform:uppercase;letter-spacing:.08em;">Baggage</div>
                            <div style="font-size:13px;color:#081D33;font-weight:600;margin-top:3px;">{{ $segmentBaggage($s) }}</div>
                        </td>
                        <td class="fh-cell-sm" width="25%" valign="top" style="padding:10px 12px;">
                            <div style="font-size:10px;color:#687586;text-transform:uppercase;letter-spacing:.08em;">Seat</div>
                            <div style="font-size:13px;color:#081D33;font-weight:600;margin-top:3px;">At check-in</div>
                        </td>
                        <td class="fh-cell-sm" width="25%" valign="top" style="padding:10px 12px;">
                            <div style="font-size:10px;color:#687586;text-transform:uppercase;letter-spacing:.08em;">Status</div>
                            <div style="font-size:13px;color:{{ $statusColor }};font-weight:700;margin-top:3px;">{{ $segmentStatus }}</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</td>
</tr>
@if(isset($layoversAfter[$idx]))
<tr>
<td class="fh-px" style="padding:0 30px 14px;" align="center">
    <span style="display:inline-block;padding:7px 16px;border-radius:16px;background:#fff8ec;border:1px solid #f3dfb7;color:#7a5414;font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;">
        &#9201;&nbsp; {{ $layoversAfter[$idx]['duration'] }} layover &middot; {{ $layoversAfter[$idx]['city'] }}{{ $layoversAfter[$idx]['airport'] ? ' ('.$layoversAfter[$idx]['airport'].')' : '' }}
    </span>
</td>
</tr>
@endif
@if($hasReturn && $idx === $outboundCount - 1)
<tr><td style="padding:0 30px 6px;font-size:0;line-height:0;">&nbsp;</td></tr>
@endif
@empty
<tr>
<td class="fh-px" style="padding:0 30px 16px;">
    <div style="padding:12px 14px;border-radius:10px;background:#fff8ec;color:#7a5414;font-size:13px;line-height:1.6;">
        @if($isCancelled)
        This reservation has been cancelled. The itinerary above is provided for reference only.
        @elseif($isIssued)
        This itinerary is associated with booking {{ $ref }}. Please refer to your airline ticket details for confirmed travel information.
        @elseif($isPaymentDeclined)
        This itinerary is associated with booking request {{ $ref }}. Payment was declined and ticket issuance has not been confirmed.
        @else
        This itinerary is associated with booking request {{ $ref }}. Flight details remain subject to ticket issuance and airline confirmation.
        @endif
    </div>
</td>
</tr>
@endforelse

<!-- PASSENGERS -->
<tr>
<td class="fh-px" style="padding:16px 30px 12px;">
    <div style="font-size:11px;letter-spacing:.16em;text-transform:uppercase;color:#B48A4A;font-weight:700;">Travellers</div>
    <div style="font-size:22px;font-weight:700;color:#081D33;letter-spacing:-.015em;margin-top:4px;line-height:1.2;">Passenger details</div>
</td>
</tr>

@forelse($passengers as $i => $p)
<tr>
<td class="fh-px" style="padding:0 30px 10px;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border:1px solid #E8D9BF;border-radius:14px;">
        <tr>
            <td class="fh-hide-sm" width="40" valign="middle" style="padding:14px 0 14px 16px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td width="38" height="38" align="center" valign="middle" style="width:38px;height:38px;border-radius:19px;background:#FAF7F0;color:#081D33;font-size:13px;font-weight:700;line-height:38px;">{{ $initials($p) }}</td>
                    </tr>
                </table>
            </td>
            <td class="fh-stack fh-pl" valign="middle" style="padding:14px 8px 14px 12px;">
                <div style="font-size:10px;color:#687586;text-transform:uppercase;letter-spacing:.08em;">Passenger {{ $i+1 }} &middot; {{ $p['AgeType'] ?? 'Traveler' }}</div>
                <div style="font-size:15px;font-weight:600;color:#081D33;margin-top:4px;">{{ $fullName($p) ?: ($booking->o_Name ?: 'Traveler') }}</div>
            </td>
            <td class="fh-cell-sm fh-pl" width="80" valign="middle" style="padding:14px 6px;">
                <div style="font-size:10px;color:#687586;text-transform:uppercase;letter-spacing:.08em;">Gender</div>
                <div style="font-size:13px;font-weight:600;color:#081D33;margin-top:4px;">{{ $p['GenderType'] ?? $p['Gender'] ?? '—' }}</div>
            </td>
            <td class="fh-cell-sm" width="104" valign="middle" style="padding:14px 16px 14px 6px;">
                <div style="font-size:10px;color:#687586;text-transform:uppercase;letter-spacing:.08em;">Date of birth</div>
                <div style="font-size:13px;font-weight:600;color:#081D33;margin-top:4px;white-space:nowrap;">{{ !empty($p['BirthDay']) ? $fmtDate($p['BirthDay'],'d M Y') : '—' }}</div>
            </td>
        </tr>
    </table>
</td>
</tr>
@empty
<tr>
<td class="fh-px" style="padding:0 30px 10px;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border:1px solid #E8D9BF;border-radius:14px;">
        <tr>
            <td style="padding:14px 16px;font-size:15px;font-weight:600;color:#081D33;">{{ $booking->o_Name ?: 'Traveler' }}</td>
        </tr>
    </table>
</td>
</tr>
@endforelse

<!-- CONTACT + FARE -->
<tr>
<td class="fh-px" style="padding:22px 30px 22px;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td class="fh-stack" width="50%" valign="top" style="padding-right:8px;">
                <div style="font-size:11px;letter-spacing:.16em;text-transform:uppercase;color:#B48A4A;font-weight:700;">Stay in touch</div>
                <div style="font-size:22px;font-weight:700;color:#081D33;letter-spacing:-.015em;margin:4px 0 12px;line-height:1.2;">Contact</div>
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border:1px solid #E8D9BF;border-radius:14px;">
                    <tr>
                        <td style="padding:16px;">
                            <div style="font-size:10px;color:#687586;text-transform:uppercase;letter-spacing:.08em;">Email</div>
                            <div style="font-size:13px;font-weight:600;color:#081D33;margin:4px 0 12px;word-break:break-word;">{{ $booking->o_Email }}</div>

                            <div style="font-size:10px;color:#687586;text-transform:uppercase;letter-spacing:.08em;">Mobile</div>
                            <div style="font-size:13px;font-weight:600;color:#081D33;margin:4px 0 {{ $billingLine !== '' ? '12px' : '0' }};">{{ $booking->o_Phone ?: '—' }}</div>

                            @if($billingLine !== '')
                                <div style="font-size:10px;color:#687586;text-transform:uppercase;letter-spacing:.08em;">Billing address</div>
                                <div style="font-size:13px;font-weight:600;color:#081D33;margin-top:4px;line-height:1.5;">{{ $billingLine }}</div>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>

            <td class="fh-stack fh-gap" width="50%" valign="top" style="padding-left:8px;">
                <div style="font-size:11px;letter-spacing:.16em;text-transform:uppercase;color:#B48A4A;font-weight:700;">Payment</div>
                <div style="font-size:22px;font-weight:700;color:#081D33;letter-spacing:-.015em;margin:4px 0 12px;line-height:1.2;">Fare summary</div>
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#081D33;border-radius:14px;">
                    <tr>
                        <td style="padding:18px;color:#ffffff;">
                            @if(count($selectedAddons))
                                <div style="font-size:10px;color:#D9C8AA;text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px;">Selected add-ons</div>
                                @foreach($selectedAddons as $addon)
                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:6px;">
                                        <tr>
                                            <td style="font-size:13px;color:#ffffff;">{{ $addon['name'] }}</td>
                                            <td align="right" style="font-size:13px;color:#ffffff;white-space:nowrap;">{{ $money($addon['amount']) }}</td>
                                        </tr>
                                    </table>
                                @endforeach
                                <div style="border-top:1px solid #46546A;margin:10px 0 0;font-size:0;line-height:0;">&nbsp;</div>
                            @endif

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td valign="middle" style="padding-top:{{ count($selectedAddons) ? '10px' : '0' }};">
                                        <div style="font-size:11px;color:#C9A66B;text-transform:uppercase;font-weight:700;letter-spacing:.1em;">Booking total</div>
                                    </td>
                                    <td align="right" valign="middle" style="padding-top:{{ count($selectedAddons) ? '10px' : '0' }};font-size:30px;font-weight:700;letter-spacing:-.02em;white-space:nowrap;color:#ffffff;">{{ $money($booking->o_FullPrice) }}</td>
                                </tr>
                            </table>
                            @if($cardLast4)
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:14px;border-top:1px solid #46546A;">
                                <tr>
                                    <td style="font-size:11px;color:#D9C8AA;text-transform:uppercase;letter-spacing:.1em;padding-top:12px;">Card on file</td>
                                    <td align="right" style="font-size:13px;color:#ffffff;font-weight:600;white-space:nowrap;padding-top:12px;">{{ $cardBrand }} &nbsp;&bull;&bull;&bull;&bull;&nbsp;{{ $cardLast4 }}</td>
                                </tr>
                            </table>
                            @endif
                            <div style="font-size:11px;color:#D9C8AA;margin-top:14px;padding-top:12px;border-top:1px solid #46546A;">
                                {{ $passengerCount ?: 1 }} {{ ($passengerCount ?: 1) === 1 ? 'Passenger' : 'Passengers' }} &nbsp;&middot;&nbsp; Fare rules and refund conditions apply
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</td>
</tr>

<!-- WHAT NEXT -->
<tr>
<td class="fh-px" style="padding:0 30px 12px;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border:1px solid #E8D9BF;border-radius:14px;background:#FAF7F0;">
        <tr>
            <td width="62" align="center" valign="top" style="padding:16px 6px 16px 16px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td width="40" height="40" align="center" valign="middle" style="width:40px;height:40px;border-radius:20px;background:#E8D9BF;color:#081D33;line-height:40px;font-size:20px;font-weight:700;font-family:Georgia,serif;">i</td>
                    </tr>
                </table>
            </td>
            <td valign="middle" style="padding:16px 10px 16px 6px;">
                <div style="font-size:15px;font-weight:700;color:#081D33;margin-bottom:5px;">What happens next</div>
                <div style="font-size:13px;color:#536477;line-height:1.6;">
                    @if($isCancelled)
                    Your reservation is cancelled. Please contact our support team if you need assistance.
                    @elseif($isIssued)
                    Your booking is marked as ticket issued. Please check your email for ticketing information and contact our support team if you need assistance.
                    @elseif($isPaymentDeclined)
                    Your payment was declined. Please contact our support team for assistance with your booking.
                    @else
                    Your booking request is being processed. Once ticket issuance is confirmed, we will provide the available ticketing information. Please check your inbox and spam folder.
                    @endif
                </div>
            </td>
            <td class="fh-hide-sm" width="130" align="center" valign="middle" style="padding:8px 16px 8px 4px;">
                <img src="{{ $mailBase . '/mail_images/booking-confirmation/fareshunt-what-next-illustration.png' }}" alt="" width="116" style="display:block;width:116px;max-width:100%;height:auto;border:0;margin:0 auto;">
            </td>
        </tr>
    </table>
</td>
</tr>

<!-- IMPORTANT SPECIAL FARE NOTICE -->
<tr>
<td class="fh-px" style="padding:0 30px 24px;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#FAF7F0;border:1px solid #E8D9BF;border-radius:14px;">
        <tr>
            <td width="62" align="center" valign="top" style="padding:16px 6px 16px 16px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td width="40" height="40" align="center" valign="middle" style="width:40px;height:40px;border-radius:20px;background:#B48A4A;color:#ffffff;line-height:40px;font-size:18px;font-weight:700;">!</td>
                    </tr>
                </table>
            </td>
            <td valign="middle" style="padding:16px 10px 16px 6px;">
                <div style="font-size:13px;font-weight:700;color:#8D6835;margin-bottom:5px;letter-spacing:.02em;">Important notice for discounted or special deal fares</div>
                <div style="font-size:13px;line-height:1.6;color:#536477;">
                    This email acknowledges your booking status but is not an airline e-ticket or boarding pass. Check-in opening times and boarding pass availability depend on the airline, route and applicable travel requirements. Please follow your airline's check-in instructions.
                </div>
            </td>
            <td class="fh-hide-sm" width="126" align="center" valign="middle" style="padding:8px 16px 8px 4px;">
                <img src="{{ $mailBase . '/mail_images/booking-confirmation/fareshunt-boarding-24h-illustration.png' }}" alt="" width="104" style="display:block;width:104px;max-width:100%;height:auto;border:0;margin:0 auto;">
            </td>
        </tr>
    </table>
</td>
</tr>

<!-- FOOTER -->
<tr>
<td class="fh-px" style="padding:18px 30px 16px;border-top:1px solid #E8D9BF;background:#FAF7F0;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td class="fh-stack fh-center-sm" width="30%" valign="middle">
                <img src="{{ $mailBase . '/mail_images/fareshunt-footer-logo.png' }}" alt="Fares Hunt" width="140" style="display:inline-block;width:140px;max-width:100%;height:auto;border:0;">
            </td>
            <td class="fh-stack fh-gap fh-center-sm" width="70%" align="right" valign="middle" style="font-size:12px;color:#536477;line-height:1.8;">
                <div style="font-size:11px;color:#687586;text-transform:uppercase;letter-spacing:.1em;font-weight:700;">Need help? Contact us</div>
                @if($supportEmail)
                    <a href="mailto:{{ $supportEmail }}" style="color:#081D33;text-decoration:none;font-weight:600;">&#9993;&nbsp;{{ $supportEmail }}</a>
                @endif
                @if($supportPhone)
                    &nbsp;&middot;&nbsp; <a href="tel:{{ preg_replace('/[^0-9+]/', '', $supportPhone) }}" style="color:#081D33;text-decoration:none;font-weight:600;">&#9742;&nbsp;{{ $supportPhone }}</a>
                @endif
                @if($supportWhatsApp)
                    &nbsp;&middot;&nbsp; <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $supportWhatsApp) }}" style="color:#081D33;text-decoration:none;font-weight:600;">WhatsApp&nbsp;{{ $supportWhatsApp }}</a>
                @endif
                <div style="font-size:12px;color:#536477;">Booking reference: <strong style="color:#081D33;">{{ $ref }}</strong></div>
            </td>
        </tr>
    </table>
</td>
</tr>

<tr>
<td align="center" style="background:#081D33;padding:13px 20px;font-size:12px;color:#ffffff;">
    <a href="{{ $mailBase . '/privacy-policy' }}" style="color:#ffffff;text-decoration:none;">Privacy Policy</a>
    &nbsp;&nbsp;|&nbsp;&nbsp;
    <a href="{{ $mailBase . '/terms-and-conditions' }}" style="color:#ffffff;text-decoration:none;">Terms &amp; Conditions</a>
</td>
</tr>

</table>
</td>
</tr>
</table>
</body>
</html>