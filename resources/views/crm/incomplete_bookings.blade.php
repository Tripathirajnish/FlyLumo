@extends('crm.layout')
@section('content')

<style>
.ib-head{display:flex;justify-content:space-between;align-items:flex-end;gap:18px;margin-bottom:18px}
.ib-eyebrow{color:#1d5bd2;text-transform:uppercase;letter-spacing:.12em;font-size:9px;font-weight:800;margin-bottom:7px}
.ib-title{margin:0;color:#071f4f;font-size:30px;font-weight:800;letter-spacing:-.035em}
.ib-sub{color:#718198;margin-top:6px}
.ib-toolbar{padding:16px;margin-bottom:14px;border:1px solid #dbe6f2;border-radius:16px;background:#fff}
.ib-form{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.ib-search{min-width:300px;flex:1}
.ib-card{border:1px solid #dbe6f2;border-radius:18px;background:#fff;overflow:hidden;box-shadow:0 14px 30px rgba(14,47,92,.055)}
.ib-scroll{overflow:auto}
.ib-table{min-width:1100px;width:100%;border-collapse:collapse}
.ib-table th{padding:13px 12px;background:#f5f8fc;color:#425a78;font-size:9px;text-transform:uppercase;white-space:nowrap}
.ib-table td{padding:13px 12px;border-top:1px solid #edf1f6;color:#334f70;font-size:10px;vertical-align:middle}
.ib-id{font-weight:850;color:#0d438f;text-decoration:none}
.ib-route{font-weight:800;color:#12345c}
.ib-price{font-weight:850;color:#071f4f}
.ib-action{display:inline-flex;width:31px;height:31px;align-items:center;justify-content:center;border-radius:9px;background:#edf4ff;color:#1f5bd8;text-decoration:none}
.ib-footer{padding:14px 16px;border-top:1px solid #edf1f6}
</style>

@php
    $decode = function($value) {
        if (!$value) return [];
        $data = json_decode((string)$value, true);
        return is_array($data) ? $data : [];
    };

    $first = function($node, array $keys) use (&$first) {
        if (!is_array($node)) return null;
        foreach ($keys as $key) {
            if (isset($node[$key]) && $node[$key] !== '') return $node[$key];
        }
        foreach ($node as $child) {
            if (is_array($child)) {
                $found = $first($child, $keys);
                if ($found !== null && $found !== '') return $found;
            }
        }
        return null;
    };
@endphp

<div class="ib-head">
    <div>
        <div class="ib-eyebrow">Booking Recovery</div>
        <h1 class="ib-title">Incomplete Bookings</h1>
        <div class="ib-sub">Customers who started a flight booking but did not complete it.</div>
    </div>
    <span class="soft blue">{{ $incompleteBookings->total() }} Records</span>
</div>

<div class="ib-toolbar">
    <form method="GET" class="ib-form">
        <input class="form-control ib-search" type="search" name="q"
               value="{{ request('q') }}"
               placeholder="Search booking ID, email or phone">

        <select class="form-select" name="per_page" style="max-width:140px">
            @foreach([15,25,50,100] as $size)
                <option value="{{ $size }}" {{ (int)request('per_page',25)===$size?'selected':'' }}>
                    {{ $size }} / page
                </option>
            @endforeach
        </select>

        <button class="btn btn-primary" type="submit">
            <i class="fa-solid fa-magnifying-glass"></i> Search
        </button>

        @if(request()->filled('q'))
            <a class="btn btn-light" href="{{ route('crm.incomplete_bookings') }}">Clear</a>
        @endif
    </form>
</div>

<div class="ib-card">
    <div class="ib-scroll">
        <table class="ib-table">
            <thead>
            <tr>
                <th>S.No.</th>
                <th>Booking ID</th>
                <th>Email</th>
                <th>Contact</th>
                <th>Travel</th>
                <th>Departure</th>
                <th>Return</th>
                <th>Price</th>
                <th>Submitted</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse($incompleteBookings as $item)
                @php
                    $field = $decode($item->field);
                    $from = $first($field,['IATAFrom','From','from','Origin','origin']);
                    $to = $first($field,['IATATo','To','to','Destination','destination']);
                    $departure = $first($field,['DeptDate','DepartureDate','departure_date','DepartDate','departure']);
                    $return = $first($field,['RetnDate','ReturnDate','return_date','return']);
                    $flightType = $first($field,['FlightType','flight_type','TripType']);
                    $flightClass = $first($field,['FlightClass','flight_class','CabinClass']);
                @endphp
                <tr>
                    <td>{{ $incompleteBookings->firstItem() + $loop->index }}</td>
                    <td>
                        <a class="ib-id" href="{{ route('crm.incomplete_booking_detail',$item->id) }}">
                            FHIN{{ str_pad((string)$item->id, 3, '0', STR_PAD_LEFT) }}
                        </a>
                    </td>
                    <td>{{ $item->emailid ?: '—' }}</td>
                    <td>{{ $item->phone ?: '—' }}</td>
                    <td>
                        <span class="ib-route">
                            {{ $from ? strtoupper($from) : '—' }}
                            @if($from || $to) → @endif
                            {{ $to ? strtoupper($to) : '—' }}
                        </span>
                        @if($flightType || $flightClass)
                            <small style="display:block;color:#8290a4;margin-top:3px">
                                {{ $flightType ?: '' }}{{ $flightType && $flightClass ? ' · ' : '' }}{{ $flightClass ?: '' }}
                            </small>
                        @endif
                    </td>
                    <td>{{ $departure ?: '—' }}</td>
                    <td>{{ $return ?: '—' }}</td>
                    <td class="ib-price">{{ $item->price !== null && $item->price !== '' ? '$'.number_format((float)$item->price,2) : '—' }}</td>
                    <td>{{ $item->created_at ? $item->created_at->format('M d, Y h:i A') : '—' }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:7px">
                            <a class="ib-action" href="{{ route('crm.incomplete_booking_detail',$item->id) }}" title="View details">
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>

                            <form method="POST"
                                  action="{{ route('crm.incomplete_booking_delete',$item->id) }}"
                                  style="margin:0"
                                  onsubmit="return confirm('Permanently delete incomplete booking #{{ $item->id }}? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="confirmation" value="DELETE">

                                <button type="submit"
                                        class="ib-action"
                                        title="Delete permanently"
                                        style="border:1px solid #fecaca;background:#fff5f5;color:#dc2626;cursor:pointer">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="empty">No incomplete bookings found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($incompleteBookings->hasPages())
        <div class="ib-footer">{{ $incompleteBookings->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

@endsection
