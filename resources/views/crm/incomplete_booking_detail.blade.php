@extends('crm.layout')
@section('content')

@php
    $field = json_decode((string)($incompleteBooking->field ?? ''), true);
    $field = is_array($field) ? $field : [];

    $results = json_decode((string)($incompleteBooking->result ?? ''), true);
    $results = is_array($results) ? $results : [];

    $formatDate = function($value, $format = 'M d, Y') {
        if (!$value) return '—';
        try {
            return \Carbon\Carbon::parse($value)->format($format);
        } catch (\Throwable $e) {
            return $value;
        }
    };

    $passengers =
        (int)($field['Adults'] ?? 0) +
        (int)($field['Childrens'] ?? 0) +
        (int)($field['Infants'] ?? 0);

    // 14 Nov 2026 (crm cabin label): show the cabin name, not the stored code ("Econom" -> "Economy").
    // Display only: stored data and the segment edit form keep the original value.
    $crmCabin = function ($cabin) {
        $c = trim((string) $cabin);
        if ($c === '') return '—';
        $map = ['econom' => 'Economy', 'economy' => 'Economy', 'peconom' => 'Premium Economy', 'premiumeconomy' => 'Premium Economy', 'premium economy' => 'Premium Economy', 'business' => 'Business', 'firstclass' => 'First', 'first' => 'First'];
        return $map[strtolower($c)] ?? $c;
    };

    $departureSegments = $results['AllFlight']['Rph_1'] ?? [];
    $returnSegments = $results['AllFlight']['Rph_2'] ?? [];

    if (!is_array($departureSegments)) $departureSegments = [];
    if (!is_array($returnSegments)) $returnSegments = [];
@endphp

<style>
.ibd-head{
    display:flex;
    justify-content:space-between;
    align-items:flex-end;
    gap:16px;
    margin-bottom:18px;
}
.ibd-eyebrow{
    color:#1d5bd2;
    text-transform:uppercase;
    letter-spacing:.12em;
    font-size:9px;
    font-weight:800;
    margin-bottom:7px;
}
.ibd-title{
    margin:0;
    color:#071f4f;
    font-size:30px;
    font-weight:800;
    letter-spacing:-.035em;
}
.ibd-sub{
    margin-top:6px;
    color:#718198;
    font-size:12px;
}
.ibd-back{
    display:inline-flex;
    align-items:center;
    gap:7px;
    min-height:40px;
    padding:0 13px;
    border:1px solid #dbe6f2;
    border-radius:11px;
    color:#173f72;
    background:#fff;
    text-decoration:none;
    font-weight:750;
}
.ibd-card{
    margin-bottom:16px;
    border:1px solid #dbe6f2;
    border-radius:18px;
    overflow:hidden;
    background:#fff;
    box-shadow:0 12px 28px rgba(14,47,92,.05);
}
.ibd-card-head{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:14px 17px;
    border-bottom:1px solid #e7edf5;
    background:linear-gradient(180deg,#f9fbfe,#f4f8fc);
}
.ibd-card-head h3{
    margin:0;
    color:#0b2b58;
    font-size:14px;
    font-weight:800;
}
.ibd-grid{
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
}
.ibd-item{
    padding:15px 17px;
    border-right:1px solid #edf1f6;
    border-bottom:1px solid #edf1f6;
}
.ibd-label{
    margin-bottom:5px;
    color:#8190a4;
    font-size:8px;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.05em;
}
.ibd-value{
    color:#14385f;
    font-size:11px;
    font-weight:750;
    word-break:break-word;
}
.ibd-sector-wrap{overflow:auto}
.ibd-sector{
    width:100%;
    min-width:900px;
    border-collapse:collapse;
}
.ibd-sector th{
    padding:11px 13px;
    background:#f5f8fc;
    color:#526982;
    font-size:8px;
    font-weight:850;
    text-transform:uppercase;
}
.ibd-sector td{
    padding:14px 13px;
    border-top:1px solid #edf1f6;
    color:#34506d;
    font-size:10px;
    vertical-align:middle;
}
.ibd-trip-row td{
    background:#edf4ff;
    color:#1850a9;
    font-size:9px;
    font-weight:900;
    text-transform:uppercase;
}
.ibd-airline{
    display:flex;
    align-items:center;
    gap:9px;
}
.ibd-airline img{
    width:38px;
    height:38px;
    object-fit:contain;
}
.ibd-code{
    color:#102f57;
    font-weight:850;
}
.ibd-time{
    margin-top:3px;
    color:#102f57;
    font-size:11px;
    font-weight:850;
}
.ibd-muted{
    margin-top:2px;
    color:#8290a4;
    font-size:9px;
}
.ibd-layover td{
    text-align:center;
    background:#fff9e9;
    color:#99670b;
    font-weight:750;
}
.ibd-empty{
    padding:25px!important;
    text-align:center;
    color:#8492a5!important;
}
@media(max-width:900px){
    .ibd-grid{grid-template-columns:1fr 1fr}
}
@media(max-width:600px){
    .ibd-head{align-items:flex-start;flex-direction:column}
    .ibd-grid{grid-template-columns:1fr}
}
</style>

<div class="ibd-head">
    <div>
        <div class="ibd-eyebrow">Booking Recovery</div>
        <h1 class="ibd-title">
            Incomplete Booking FHIN{{ str_pad((string)$incompleteBooking->id, 3, '0', STR_PAD_LEFT) }}
        </h1>
        <div class="ibd-sub">
            Captured booking and flight information before checkout completion.
        </div>
    </div>

    <a href="{{ route('crm.incomplete_bookings') }}" class="ibd-back">
        <i class="fa-solid fa-arrow-left"></i> Back to Incomplete Bookings
    </a>
</div>

<div class="ibd-card">
    <div class="ibd-card-head">
        <h3>Booking Information</h3>
    </div>

    <div class="ibd-grid">
        <div class="ibd-item">
            <div class="ibd-label">Booking ID</div>
            <div class="ibd-value">FHIN{{ str_pad((string)$incompleteBooking->id, 3, '0', STR_PAD_LEFT) }}</div>
        </div>

        <div class="ibd-item">
            <div class="ibd-label">Customer Email</div>
            <div class="ibd-value">{{ $incompleteBooking->emailid ?: '—' }}</div>
        </div>

        <div class="ibd-item">
            <div class="ibd-label">Customer Phone</div>
            <div class="ibd-value">{{ $incompleteBooking->phone ?: '—' }}</div>
        </div>

        <div class="ibd-item">
            <div class="ibd-label">Travel</div>
            <div class="ibd-value">
                {{ $field['IATAFrom'] ?? '—' }}
                @if(!empty($field['IATAFrom']) || !empty($field['IATATo'])) → @endif
                {{ $field['IATATo'] ?? '—' }}
            </div>
        </div>

        <div class="ibd-item">
            <div class="ibd-label">Departure Date</div>
            <div class="ibd-value">{{ $formatDate($field['DeptDate'] ?? null) }}</div>
        </div>

        <div class="ibd-item">
            <div class="ibd-label">Return Date</div>
            <div class="ibd-value">
                {{ (($field['FlightType'] ?? '') === 'round_trip') ? $formatDate($field['RetnDate'] ?? null) : '—' }}
            </div>
        </div>

        <div class="ibd-item">
            <div class="ibd-label">Flight Type</div>
            <div class="ibd-value">{{ $field['FlightType'] ?? '—' }}</div>
        </div>

        <div class="ibd-item">
            <div class="ibd-label">Flight Class</div>
            <div class="ibd-value">{{ $crmCabin($field['FlightClass'] ?? '') }}</div>
        </div>

        <div class="ibd-item">
            <div class="ibd-label">Total Passengers</div>
            <div class="ibd-value">{{ $passengers ?: '—' }}</div>
        </div>

        <div class="ibd-item">
            <div class="ibd-label">Adults</div>
            <div class="ibd-value">{{ $field['Adults'] ?? 0 }}</div>
        </div>

        <div class="ibd-item">
            <div class="ibd-label">Children</div>
            <div class="ibd-value">{{ $field['Childrens'] ?? 0 }}</div>
        </div>

        <div class="ibd-item">
            <div class="ibd-label">Infants</div>
            <div class="ibd-value">{{ $field['Infants'] ?? 0 }}</div>
        </div>

        <div class="ibd-item">
            <div class="ibd-label">Price</div>
            <div class="ibd-value">
                {{ $incompleteBooking->price !== null && $incompleteBooking->price !== ''
                    ? '$'.number_format((float)$incompleteBooking->price,2)
                    : '—' }}
            </div>
        </div>

        <div class="ibd-item">
            <div class="ibd-label">Submission Date</div>
            <div class="ibd-value">
                {{ $incompleteBooking->created_at
                    ? $incompleteBooking->created_at->format('M d, Y h:i A')
                    : '—' }}
            </div>
        </div>
    </div>
</div>

<div class="ibd-card">
    <div class="ibd-card-head">
        <h3>Flight Sector Details</h3>
    </div>

    <div class="ibd-sector-wrap">
        <table class="ibd-sector">
            <thead>
                <tr>
                    <th>Flight</th>
                    <th>Origin</th>
                    <th>Duration</th>
                    <th>Destination</th>
                    <th>Info</th>
                </tr>
            </thead>
            <tbody>

            <tr class="ibd-trip-row">
                <td colspan="5">Departure</td>
            </tr>

            @forelse($departureSegments as $index => $segment)

                @if($index > 0)
                    @php
                        $prev = $departureSegments[$index - 1] ?? [];
                        $layover = null;

                        try {
                            if (!empty($prev['Arrival_Date']) && !empty($segment['Departure_Date'])) {
                                $start = \Carbon\Carbon::parse($prev['Arrival_Date']);
                                $end = \Carbon\Carbon::parse($segment['Departure_Date']);
                                $mins = max(0, $start->diffInMinutes($end));
                                $hours = intdiv($mins,60);
                                $remaining = $mins % 60;

                                $layover = ($hours ? $hours.'h ' : '').$remaining.'m layover in '.
                                    ($segment['Departure_Name'] ?? '').
                                    (!empty($segment['Departure_Iata']) ? ' ('.$segment['Departure_Iata'].')' : '');
                            }
                        } catch (\Throwable $e) {}
                    @endphp

                    @if($layover)
                        <tr class="ibd-layover">
                            <td colspan="5">{{ $layover }}</td>
                        </tr>
                    @endif
                @endif

                <tr>
                    <td>
                        <div class="ibd-airline">
                            @if(!empty($segment['AirlineLogo']))
                                <img src="{{ $segment['AirlineLogo'] }}" alt="">
                            @endif
                            <div>
                                <div class="ibd-code">{{ $segment['AirlineName'] ?? '—' }}</div>
                                <div class="ibd-muted">Flight {{ $segment['FlightNum'] ?? '—' }}</div>
                            </div>
                        </div>
                    </td>

                    <td>
                        <div class="ibd-code">
                            {{ $segment['Departure_Name'] ?? '—' }}
                            @if(!empty($segment['Departure_Iata']))
                                ({{ $segment['Departure_Iata'] }})
                            @endif
                        </div>
                        <div class="ibd-time">{{ $formatDate($segment['Departure_Date'] ?? null,'h:i A') }}</div>
                        <div class="ibd-muted">{{ $formatDate($segment['Departure_Date'] ?? null,'D, M d') }}</div>
                    </td>

                    <td>
                        @php
                            $minutes = (int)($segment['FlightMinutes'] ?? 0);
                            $hours = intdiv($minutes,60);
                            $remaining = $minutes % 60;
                        @endphp
                        {{ $minutes ? $hours.'h '.$remaining.'m' : '—' }}
                    </td>

                    <td>
                        <div class="ibd-code">
                            {{ $segment['Arrival_Name'] ?? '—' }}
                            @if(!empty($segment['Arrival_Iata']))
                                ({{ $segment['Arrival_Iata'] }})
                            @endif
                        </div>
                        <div class="ibd-time">{{ $formatDate($segment['Arrival_Date'] ?? null,'h:i A') }}</div>
                        <div class="ibd-muted">{{ $formatDate($segment['Arrival_Date'] ?? null,'D, M d') }}</div>
                    </td>

                    <td>
                        <div class="ibd-code">{{ $segment['AirlineName'] ?? '—' }}</div>
                        <div class="ibd-muted">Flight {{ $segment['FlightNum'] ?? '—' }}</div>
                        <div class="ibd-muted">{{ $crmCabin($segment['FlightClass'] ?? '') }}</div>
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="5" class="ibd-empty">No departure flight sector data available.</td>
                </tr>
            @endforelse


            @if(($field['FlightType'] ?? '') === 'round_trip')
                <tr class="ibd-trip-row">
                    <td colspan="5">Return</td>
                </tr>

                @forelse($returnSegments as $index => $segment)

                    @if($index > 0)
                        @php
                            $prev = $returnSegments[$index - 1] ?? [];
                            $layover = null;

                            try {
                                if (!empty($prev['Arrival_Date']) && !empty($segment['Departure_Date'])) {
                                    $start = \Carbon\Carbon::parse($prev['Arrival_Date']);
                                    $end = \Carbon\Carbon::parse($segment['Departure_Date']);
                                    $mins = max(0, $start->diffInMinutes($end));
                                    $hours = intdiv($mins,60);
                                    $remaining = $mins % 60;

                                    $layover = ($hours ? $hours.'h ' : '').$remaining.'m layover in '.
                                        ($segment['Departure_Name'] ?? '').
                                        (!empty($segment['Departure_Iata']) ? ' ('.$segment['Departure_Iata'].')' : '');
                                }
                            } catch (\Throwable $e) {}
                        @endphp

                        @if($layover)
                            <tr class="ibd-layover">
                                <td colspan="5">{{ $layover }}</td>
                            </tr>
                        @endif
                    @endif

                    <tr>
                        <td>
                            <div class="ibd-airline">
                                @if(!empty($segment['AirlineLogo']))
                                    <img src="{{ $segment['AirlineLogo'] }}" alt="">
                                @endif
                                <div>
                                    <div class="ibd-code">{{ $segment['AirlineName'] ?? '—' }}</div>
                                    <div class="ibd-muted">Flight {{ $segment['FlightNum'] ?? '—' }}</div>
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="ibd-code">
                                {{ $segment['Departure_Name'] ?? '—' }}
                                @if(!empty($segment['Departure_Iata']))
                                    ({{ $segment['Departure_Iata'] }})
                                @endif
                            </div>
                            <div class="ibd-time">{{ $formatDate($segment['Departure_Date'] ?? null,'h:i A') }}</div>
                            <div class="ibd-muted">{{ $formatDate($segment['Departure_Date'] ?? null,'D, M d') }}</div>
                        </td>

                        <td>
                            @php
                                $minutes = (int)($segment['FlightMinutes'] ?? 0);
                                $hours = intdiv($minutes,60);
                                $remaining = $minutes % 60;
                            @endphp
                            {{ $minutes ? $hours.'h '.$remaining.'m' : '—' }}
                        </td>

                        <td>
                            <div class="ibd-code">
                                {{ $segment['Arrival_Name'] ?? '—' }}
                                @if(!empty($segment['Arrival_Iata']))
                                    ({{ $segment['Arrival_Iata'] }})
                                @endif
                            </div>
                            <div class="ibd-time">{{ $formatDate($segment['Arrival_Date'] ?? null,'h:i A') }}</div>
                            <div class="ibd-muted">{{ $formatDate($segment['Arrival_Date'] ?? null,'D, M d') }}</div>
                        </td>

                        <td>
                            <div class="ibd-code">{{ $segment['AirlineName'] ?? '—' }}</div>
                            <div class="ibd-muted">Flight {{ $segment['FlightNum'] ?? '—' }}</div>
                            <div class="ibd-muted">{{ $crmCabin($segment['FlightClass'] ?? '') }}</div>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="5" class="ibd-empty">No return flight sector data available.</td>
                    </tr>
                @endforelse
            @endif

            </tbody>
        </table>
    </div>
</div>


<div class="ibd-card" id="incomplete-email-composer">
    <div class="ibd-card-head">
        <h3>Email Passenger</h3>
    </div>

    @if(!empty($incompleteBooking->emailid) && filter_var($incompleteBooking->emailid, FILTER_VALIDATE_EMAIL))
        <form method="POST"
              action="{{ route(request()->getHost() === 'crm.fareshunt.com' ? 'crm.incomplete_booking_email_send' : 'legacy.crm.incomplete_booking_email_send', $incompleteBooking->id) }}"
              onsubmit="return confirm('Send this email to {{ addslashes($incompleteBooking->emailid) }}?');">
            @csrf

            <div class="ibd-grid">
                <div class="ibd-item">
                    <div class="ibd-label">To</div>
                    <input type="email"
                           value="{{ $incompleteBooking->emailid }}"
                           readonly
                           style="width:100%;padding:10px 12px;border:1px solid #d8dee8;border-radius:8px;background:#f7f9fc;">
                </div>

                <div class="ibd-item">
                    <div class="ibd-label">Subject</div>
                    <input type="text"
                           name="subject"
                           maxlength="255"
                           required
                           value="{{ old('subject', 'Complete Your Flight Booking') }}"
                           style="width:100%;padding:10px 12px;border:1px solid #d8dee8;border-radius:8px;">
                </div>
            </div>

            <div style="margin-top:16px;">
                <div class="ibd-label">Message</div>
                <textarea name="message"
                          rows="8"
                          maxlength="10000"
                          required
                          style="width:100%;padding:12px;border:1px solid #d8dee8;border-radius:8px;resize:vertical;">{{ old('message', "Hello,\n\nWe noticed that your flight booking was not completed. If you need any assistance completing your reservation, please reply to this email and our travel support team will be happy to assist you.\n\nThank you,\nFaresHunt Support") }}</textarea>
            </div>

            <div style="margin-top:16px;">
                <button type="submit"
                        style="border:0;border-radius:8px;padding:11px 18px;font-weight:700;cursor:pointer;background:#0d6efd;color:#fff;">
                    <i class="fa-solid fa-envelope"></i> Send Email
                </button>
            </div>
        </form>
    @else
        <div class="ibd-empty">
            This incomplete booking does not have a valid passenger email address.
        </div>
    @endif
</div>

<div class="ibd-card">
    <div class="ibd-card-head">
        <h3>Activity History</h3>
    </div>

    @forelse($activities as $activity)
        <div style="padding:14px 0;border-bottom:1px solid #edf0f5;">
            <div style="font-weight:700;">
                {{ $activity->title }}
            </div>

            @if(!empty($activity->details))
                <div class="ibd-muted" style="margin-top:4px;">
                    {{ $activity->details }}
                </div>
            @endif

            <div class="ibd-muted" style="margin-top:5px;">
                {{ $activity->admin_name ?: 'CRM User' }}
                ·
                {{ \Carbon\Carbon::parse($activity->created_at)->format('M d, Y h:i A') }}
            </div>
        </div>
    @empty
        <div class="ibd-empty">No activity recorded yet.</div>
    @endforelse
</div>

@endsection