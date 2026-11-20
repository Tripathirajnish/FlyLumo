@extends('crm.layout')

@section('content')
@php
    $money = fn($v) => '$'.number_format((float)$v, 2);
@endphp

<style>
    .pr-wrap{padding:2px 0 28px;color:#172033}
    .pr-top{display:flex;justify-content:space-between;align-items:center;gap:18px;margin-bottom:14px}
    .pr-heading{display:flex;align-items:center;gap:12px}
    .pr-title-icon{width:40px;height:40px;border:1px solid #cdd8e8;border-radius:8px;display:flex;align-items:center;justify-content:center;background:#fff;color:#143969;font-size:19px}
    .pr-title{margin:0;font-size:25px;font-weight:800;color:#172033}
    .pr-sub{font-size:12px;color:#667085;margin-top:3px}
    .pr-crumb{font-size:12px;color:#667085;white-space:nowrap}
    .pr-crumb span{color:#1670e8;font-weight:700}

    .pr-alert{padding:11px 14px;border-radius:9px;margin-bottom:14px;background:#ecfdf3;color:#166534;border:1px solid #bbf7d0;font-weight:700;font-size:13px}

    .pr-kpis{display:grid;grid-template-columns:repeat(7,minmax(150px,1fr));gap:10px;margin-bottom:14px}
    .pr-kpi{position:relative;min-height:86px;border:1px solid #e2e8f0;border-radius:9px;padding:14px 12px 12px 54px;overflow:hidden}
    .pr-kpi i{position:absolute;left:14px;top:18px;width:30px;height:30px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:16px}
    .pr-kpi-label{font-size:11px;font-weight:800;color:#344054}
    .pr-kpi-val{font-size:18px;font-weight:800;margin-top:3px;white-space:nowrap}
    .pr-kpi-note{font-size:10px;color:#667085;margin-top:3px}
    .kpi-sales{background:#eefbf4}.kpi-sales i{background:#d8f6e5;color:#0f9f5f}.kpi-sales .pr-kpi-val{color:#087b49}
    .kpi-ticket{background:#eef6ff}.kpi-ticket i{background:#dcecff;color:#1769c2}.kpi-ticket .pr-kpi-val{color:#174f91}
    .kpi-mco{background:#fff5eb}.kpi-mco i{background:#ffe5cb;color:#ed7715}.kpi-mco .pr-kpi-val{color:#b84e08}
    .kpi-merchant{background:#fff0f2}.kpi-merchant i{background:#ffdce2;color:#e23755}.kpi-merchant .pr-kpi-val{color:#b51e38}
    .kpi-supplier{background:#f5f1ff}.kpi-supplier i{background:#e9e0ff;color:#7047dd}.kpi-supplier .pr-kpi-val{color:#5530b9}
    .kpi-net{background:#eefbf4}.kpi-net i{background:#d8f6e5;color:#0f9f5f}.kpi-net .pr-kpi-val{color:#087b49}
    .kpi-bookings{background:#eef6ff}.kpi-bookings i{background:#dcecff;color:#1769c2}.kpi-bookings .pr-kpi-val{color:#174f91}

    .pr-panel{background:#fff;border:1px solid #e1e7ef;border-radius:9px;margin-bottom:14px;box-shadow:0 2px 8px rgba(15,23,42,.03)}
    .pr-filter-panel{padding:13px}
    .pr-filter-grid{display:grid;grid-template-columns:repeat(6,minmax(135px,1fr));gap:10px;align-items:end}
    .pr-field label{display:block;font-size:10px;font-weight:800;color:#344054;text-transform:uppercase;letter-spacing:.025em;margin-bottom:5px}
    .pr-field input,.pr-field select,.pr-field textarea{box-sizing:border-box;width:100%;border:1px solid #d5ddea;border-radius:7px;padding:8px 9px;font-size:12px;background:#fff;color:#263244;outline:none}
    .pr-field input:focus,.pr-field select:focus,.pr-field textarea:focus{border-color:#6da7ec;box-shadow:0 0 0 2px rgba(22,112,232,.08)}
    .pr-filter-actions{display:flex;gap:7px}
    .pr-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;border:1px solid #d4dce7;background:#fff;color:#263244;padding:8px 13px;border-radius:7px;font-weight:700;font-size:12px;text-decoration:none;cursor:pointer;white-space:nowrap}
    .pr-btn:hover{background:#f7f9fc;color:#172033}
    .pr-btn-primary{background:#086ee8;border-color:#086ee8;color:#fff}
    .pr-btn-primary:hover{background:#055dbf;color:#fff}
    .pr-btn-green{background:#0aa66b;border-color:#0aa66b;color:#fff}
    .pr-btn-green:hover{background:#078957;color:#fff}

    .pr-toolbar{display:flex;justify-content:flex-end;gap:8px;padding:10px 12px;border-top:1px solid #edf1f5;margin-top:12px}

    .pr-table-wrap{overflow:auto}
    .pr-table{width:100%;min-width:1900px;border-collapse:collapse;font-size:11px}
    .pr-table th{background:#dff0ff;color:#24364d;font-size:10px;font-weight:800;padding:9px 8px;text-align:left;border:1px solid #d5e3ef;white-space:nowrap;position:sticky;top:0;z-index:2}
    .pr-table td{padding:8px;border:1px solid #e1e8ef;color:#263244;vertical-align:middle;white-space:nowrap}
    .pr-table tbody tr:hover{background:#f8fbff}
    .pr-table .pr-num{text-align:right}
    .pr-link{font-weight:800;color:#096bd8;text-decoration:none}
    .pr-link:hover{text-decoration:underline}
    .pr-status{display:inline-flex;align-items:center;padding:4px 8px;border-radius:5px;font-size:10px;font-weight:800;background:#edf2f7;color:#475467}
    .pr-status.ticketed,.pr-status.confirmed{background:#d9f7e5;color:#087a47}
    .pr-status.issued{background:#dcecff;color:#075cae}
    .pr-status.on-hold,.pr-status.hold,.pr-status.pending{background:#fff1c7;color:#9a6600}
    .pr-status.cancelled,.pr-status.canceled{background:#ffe0e0;color:#b42318}
    .pr-positive{font-weight:800;color:#0a8d50}
    .pr-negative{font-weight:800;color:#c4322b}
    .pr-total td{background:#eaf5ff;font-weight:800}
    .pr-empty{text-align:center!important;padding:30px!important;color:#667085!important}

    .pr-actions-cell{text-align:center}
    .pr-icon-btn{width:30px;height:27px;border:1px solid #d5deea;border-radius:6px;background:#fff;color:#31506f;cursor:pointer;display:inline-flex;align-items:center;justify-content:center}
    .pr-icon-btn:hover{background:#edf6ff;color:#096bd8;border-color:#9bc4f1}

    .pr-table-footer{display:flex;justify-content:space-between;align-items:center;padding:10px 12px;font-size:11px;color:#667085}
    .pr-pagination{display:flex;gap:4px;align-items:center}
    .pr-page-btn{min-width:30px;height:29px;border:1px solid #d5deea;border-radius:6px;background:#fff;font-size:11px;font-weight:700;color:#344054;cursor:pointer}
    .pr-page-btn.active{background:#086ee8;color:#fff;border-color:#086ee8}
    .pr-page-btn:disabled{opacity:.45;cursor:default}

    .pr-bottom-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:14px}
    #pr-edit-card{grid-column:1 / -1}
    .pr-card{background:#fff;border:1px solid #e1e7ef;border-radius:9px;overflow:hidden}
    .pr-card-head{padding:11px 13px;border-bottom:1px solid #e8edf3;font-size:13px;font-weight:800;color:#172033;display:flex;align-items:center;gap:8px}
    .pr-card-head i{color:#086ee8}
    .pr-card-body{padding:12px}
    .pr-admin-note{font-size:10px;color:#096bd8;background:#eef6ff;border:1px solid #d9eaff;border-radius:6px;padding:7px 8px;margin-bottom:10px}

    .pr-edit-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
    .pr-edit-grid .wide{grid-column:span 2}
    .pr-edit-grid .pr-field{min-width:0}
    .pr-edit-grid input,
    .pr-edit-grid select,
    .pr-edit-grid textarea,
    .pr-edit-grid .pr-formula{min-width:0;max-width:100%}
    .pr-edit-grid textarea{min-height:64px;resize:vertical}
    .pr-formula{width:100%;box-sizing:border-box;background:#eef2f6;border:1px solid #d5ddea;border-radius:7px;padding:8px 9px;font-weight:800;color:#344054;font-size:12px}
    .pr-edit-buttons{display:flex;justify-content:flex-end;gap:8px;margin-top:10px}
    .pr-edit-placeholder{padding:30px 10px;text-align:center;color:#7a8495;font-size:12px}

    .pr-mini-wrap{overflow:auto;max-height:315px}
    .pr-mini-table{width:100%;border-collapse:collapse;font-size:10px}
    .pr-mini-table th{background:#e3f1ff;color:#344054;text-align:left;padding:8px;border:1px solid #d9e5f0;font-size:9px;text-transform:uppercase}
    .pr-mini-table td{padding:8px;border:1px solid #e4eaf0;color:#344054;vertical-align:top}
    .pr-mini-positive{font-weight:800;color:#0a8d50}
    .pr-audit-value{max-width:150px;white-space:normal;word-break:break-word}
    .pr-muted{color:#98a2b3}

    @media(max-width:1400px){
        .pr-kpis{grid-template-columns:repeat(4,1fr)}
        .pr-filter-grid{grid-template-columns:repeat(4,1fr)}
        .pr-edit-grid{grid-template-columns:repeat(3,minmax(0,1fr))}
        .pr-bottom-grid{grid-template-columns:1fr 1fr}
    }
    @media(max-width:1050px){
        .pr-edit-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
        .pr-bottom-grid{grid-template-columns:1fr}
    }
    @media(max-width:850px){
        .pr-top{display:block}
        .pr-crumb{margin-top:8px}
        .pr-kpis{grid-template-columns:repeat(2,1fr)}
        .pr-filter-grid{grid-template-columns:repeat(2,1fr)}
        .pr-edit-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
    }
    @media(max-width:560px){
        .pr-kpis,.pr-filter-grid,.pr-edit-grid{grid-template-columns:1fr}
        .pr-edit-grid .wide{grid-column:span 1}
        .pr-table-footer{display:block}
        .pr-pagination{margin-top:8px}
    }

    @media print{
        .pr-filter-panel,.pr-toolbar,.pr-bottom-grid,.pr-icon-btn,.pr-crumb{display:none!important}
        .pr-table{min-width:0;font-size:8px}
        .pr-table th,.pr-table td{padding:4px}
    }
</style>

<div class="pr-wrap">

    @if(session('success'))
        <div class="pr-alert">{{ session('success') }}</div>
    @endif

    <div class="pr-top">
        <div class="pr-heading">
            <div class="pr-title-icon"><i class="fa-solid fa-chart-column"></i></div>
            <div>
                <h1 class="pr-title">Profit Report</h1>
                <div class="pr-sub">
                    {{ $isSuperAdmin
                        ? 'View booking profits, costs and fees with detailed breakdown.'
                        : 'Your assigned booking profit and financial performance report.' }}
                </div>
            </div>
        </div>

        <div class="pr-crumb">
            <span>Home</span> &nbsp;›&nbsp; <span>Reports</span> &nbsp;›&nbsp; Profit Report
        </div>
    </div>

    <div class="pr-kpis">
        <div class="pr-kpi kpi-sales">
            <i class="fa-solid fa-sack-dollar"></i>
            <div class="pr-kpi-label">Total Sales</div>
            <div class="pr-kpi-val">{{ $money($kpis['total_sales']) }}</div>
            <div class="pr-kpi-note">{{ number_format($kpis['bookings']) }} Bookings</div>
        </div>

        <div class="pr-kpi kpi-ticket">
            <i class="fa-solid fa-plane"></i>
            <div class="pr-kpi-label">Total Ticket Cost</div>
            <div class="pr-kpi-val">{{ $money($kpis['ticket_cost']) }}</div>
            <div class="pr-kpi-note">{{ number_format($kpis['bookings']) }} Bookings</div>
        </div>

        <div class="pr-kpi kpi-mco">
            <i class="fa-solid fa-chart-line"></i>
            <div class="pr-kpi-label">Total MCO</div>
            <div class="pr-kpi-val">{{ $money($kpis['mco']) }}</div>
            <div class="pr-kpi-note">Sale − Ticket Cost</div>
        </div>

        <div class="pr-kpi kpi-merchant">
            <i class="fa-solid fa-credit-card"></i>
            <div class="pr-kpi-label">Merchant Fees</div>
            <div class="pr-kpi-val">{{ $money($kpis['merchant_fees']) }}</div>
            <div class="pr-kpi-note">Processing cost</div>
        </div>

        <div class="pr-kpi kpi-supplier">
            <i class="fa-solid fa-coins"></i>
            <div class="pr-kpi-label">Supplier Fees</div>
            <div class="pr-kpi-val">{{ $money($kpis['supplier_fees']) }}</div>
            <div class="pr-kpi-note">Supplier charges</div>
        </div>

        <div class="pr-kpi kpi-net">
            <i class="fa-solid fa-money-bill-trend-up"></i>
            <div class="pr-kpi-label">Net MCO</div>
            <div class="pr-kpi-val">{{ $money($kpis['net_mco']) }}</div>
            <div class="pr-kpi-note">
                @if((float)$kpis['total_sales'] != 0)
                    {{ number_format(($kpis['net_mco'] / $kpis['total_sales']) * 100, 2) }}% Margin
                @else
                    0.00% Margin
                @endif
            </div>
        </div>

        <div class="pr-kpi kpi-bookings">
            <i class="fa-solid fa-clipboard-list"></i>
            <div class="pr-kpi-label">Total Bookings</div>
            <div class="pr-kpi-val">{{ number_format($kpis['bookings']) }}</div>
            <div class="pr-kpi-note">Filtered records</div>
        </div>
    </div>

    <div class="pr-panel pr-filter-panel">
        <form method="GET" action="{{ route('crm.profit_report') }}">
            <div class="pr-filter-grid">
                <div class="pr-field">
                    <label>Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}">
                </div>

                <div class="pr-field">
                    <label>Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}">
                </div>

                <div class="pr-field">
                    <label>Booking ID</label>
                    <input type="text" name="booking_id" value="{{ request('booking_id') }}" placeholder="Booking ID">
                </div>

                <div class="pr-field">
                    <label>PNR</label>
                    <input type="text" name="pnr" value="{{ request('pnr') }}" placeholder="PNR">
                </div>

                <div class="pr-field">
                    <label>Pax Name</label>
                    <input type="text" name="pax" value="{{ request('pax') }}" placeholder="Passenger">
                </div>

                <div class="pr-field">
                    <label>Airline</label>
                    <input type="text" name="airline" value="{{ request('airline') }}" placeholder="Airline">
                </div>

                <div class="pr-field">
                    <label>Trip Type</label>
                    <select name="trip_type">
                        <option value="">All</option>
                        <option value="One Way" @selected(request('trip_type')==='One Way')>One Way</option>
                        <option value="Round Trip" @selected(request('trip_type')==='Round Trip')>Round Trip</option>
                        <option value="Multi City" @selected(request('trip_type')==='Multi City')>Multi City</option>
                    </select>
                </div>

                <div class="pr-field">
                    <label>Status</label>
                    <input type="text" name="status" value="{{ request('status') }}" placeholder="Booking status">
                </div>

                <div class="pr-field">
                    <label>Issuance Type</label>
                    <input type="text" name="issuance_type" value="{{ request('issuance_type') }}" placeholder="Issuance type">
                </div>

                @if($isSuperAdmin)
                <div class="pr-field">
                    <label>Agent</label>
                    <select name="agent">
                        <option value="">All Agents</option>
                        @foreach($adminOptions as $admin)
                            <option value="{{ $admin->id }}" @selected((string)request('agent')===(string)$admin->id)>
                                {{ $admin->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="pr-filter-actions">
                    <button type="submit" class="pr-btn pr-btn-primary">
                        <i class="fa-solid fa-magnifying-glass"></i> Search
                    </button>
                    <a href="{{ route('crm.profit_report') }}" class="pr-btn">Reset</a>
                </div>
            </div>
        </form>

        <div class="pr-toolbar">
            <a class="pr-btn pr-btn-green" href="{{ route('crm.profit_report_export', request()->query()) }}">
                <i class="fa-solid fa-file-csv"></i> Export CSV
            </a>
            <button class="pr-btn" type="button" onclick="window.print()">
                <i class="fa-solid fa-print"></i> Print
            </button>
        </div>
    </div>

    <div class="pr-panel">
        <div class="pr-table-wrap">
            <table class="pr-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Booking ID</th>
                        <th>PNR</th>
                        <th>Pax Names</th>
                        <th>Travel Dates</th>
                        <th>Trip Type</th>
                        <th>Origin - Destination</th>
                        <th>Airlines</th>
                        <th class="pr-num">Sale Amount</th>
                        <th class="pr-num">TKT Cost</th>
                        <th class="pr-num">MCO</th>
                        <th class="pr-num">Merchant Fee</th>
                        <th class="pr-num">Supplier Fees</th>
                        <th class="pr-num">Net MCO</th>
                        <th>Agent</th>
                        <th>Status</th>
                        <th>Issuance Type</th>
                        <th>Issuance Date</th>
                        <th>Remarks</th>
                        @if($isSuperAdmin)<th>Actions</th>@endif
                    </tr>
                </thead>

                <tbody id="pr-report-body">
                    @forelse($records as $row)
                        @php
                            $statusClass = strtolower(trim((string)$row['status']));
                            $statusClass = str_replace([' ', '_'], '-', $statusClass);
                        @endphp

                        <tr class="js-report-row">
                            <td>{{ $row['date'] ?: '—' }}</td>

                            <td>
                                <a class="pr-link" href="{{ route('crm.booking_detail', $row['booking_id']) }}">
                                    #{{ $row['booking_id_display'] ?: $row['booking_id'] }}
                                </a>
                            </td>

                            <td>{{ $row['pnr'] ?: '—' }}</td>
                            <td>{{ $row['pax_names'] ?: '—' }}</td>
                            <td>{{ $row['travel_dates'] ?: '—' }}</td>
                            <td>{{ $row['trip_type'] ?: '—' }}</td>
                            <td>{{ $row['origin_destination'] ?: '—' }}</td>
                            <td>{{ $row['airlines'] ?: '—' }}</td>

                            <td class="pr-num">{{ $money($row['sale_amount']) }}</td>
                            <td class="pr-num">{{ $money($row['ticket_cost']) }}</td>

                            <td class="pr-num {{ $row['mco'] < 0 ? 'pr-negative' : 'pr-positive' }}">
                                {{ $money($row['mco']) }}
                            </td>

                            <td class="pr-num">{{ $money($row['merchant_fee']) }}</td>
                            <td class="pr-num">{{ $money($row['supplier_fees']) }}</td>

                            <td class="pr-num {{ $row['net_mco'] < 0 ? 'pr-negative' : 'pr-positive' }}">
                                {{ $money($row['net_mco']) }}
                            </td>

                            <td>{{ $row['agent'] ?: 'Unassigned' }}</td>

                            <td>
                                <span class="pr-status {{ $statusClass }}">{{ $row['status'] ?: 'Unknown' }}</span>
                            </td>

                            <td>{{ $row['issuance_type'] ?: '—' }}</td>
                            <td>{{ $row['issuance_date'] ?: '—' }}</td>
                            <td title="{{ $row['remarks'] }}">{{ \Illuminate\Support\Str::limit($row['remarks'], 32) ?: '—' }}</td>

                            @if($isSuperAdmin)
                            <td class="pr-actions-cell">
                                <button
                                    type="button"
                                    class="pr-icon-btn js-edit-row"
                                    title="Edit Profit Details"
                                    data-url="{{ route('crm.profit_report_update', $row['booking_id']) }}"
                                    data-booking-id="{{ $row['booking_id'] }}"
                                    data-date="{{ $row['date'] }}"
                                    data-booking-display="{{ $row['booking_id_display'] }}"
                                    data-pnr="{{ $row['pnr'] }}"
                                    data-pax="{{ $row['pax_names'] }}"
                                    data-travel="{{ $row['travel_dates'] }}"
                                    data-trip="{{ $row['trip_type'] }}"
                                    data-route="{{ $row['origin_destination'] }}"
                                    data-airlines="{{ $row['airlines'] }}"
                                    data-sale="{{ number_format((float)$row['sale_amount'],2,'.','') }}"
                                    data-ticket="{{ number_format((float)$row['ticket_cost'],2,'.','') }}"
                                    data-merchant="{{ number_format((float)$row['merchant_fee'],2,'.','') }}"
                                    data-supplier="{{ number_format((float)$row['supplier_fees'],2,'.','') }}"
                                    data-agent="{{ $row['agent_id'] }}"
                                    data-agent-name="{{ $row['agent'] }}"
                                    data-status="{{ $row['status'] }}"
                                    data-issuance-type="{{ $row['issuance_type'] }}"
                                    data-issuance-date="{{ $row['issuance_date'] }}"
                                    data-remarks="{{ $row['remarks'] }}"
                                >
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            </td>
                            @endif
                        </tr>

                    @empty
                        <tr>
                            <td colspan="{{ $isSuperAdmin ? 20 : 19 }}" class="pr-empty">
                                No Profit Report records found for these filters.
                            </td>
                        </tr>
                    @endforelse

                    @if($records->count())
                    <tr class="pr-total" id="pr-total-row">
                        <td colspan="8">TOTAL ({{ number_format($kpis['bookings']) }} BOOKINGS)</td>
                        <td class="pr-num">{{ $money($kpis['total_sales']) }}</td>
                        <td class="pr-num">{{ $money($kpis['ticket_cost']) }}</td>
                        <td class="pr-num">{{ $money($kpis['mco']) }}</td>
                        <td class="pr-num">{{ $money($kpis['merchant_fees']) }}</td>
                        <td class="pr-num">{{ $money($kpis['supplier_fees']) }}</td>
                        <td class="pr-num">{{ $money($kpis['net_mco']) }}</td>
                        <td colspan="{{ $isSuperAdmin ? 6 : 5 }}"></td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if($records->count())
        <div class="pr-table-footer">
            <div id="pr-page-info">Showing records</div>
            <div class="pr-pagination" id="pr-pagination"></div>
        </div>
        @endif
    </div>

    @if($isSuperAdmin)
    <div class="pr-bottom-grid">

        <div class="pr-card" id="pr-edit-card">
            <div class="pr-card-head">
                <i class="fa-solid fa-pen-to-square"></i>
                Edit Profit Details (Super Admin Only)
            </div>

            <div class="pr-card-body">
                <div class="pr-admin-note">
                    <i class="fa-solid fa-shield-halved"></i>
                    Only Super Admin can edit Profit Report details. Original booking records remain unchanged.
                </div>

                <div id="pr-edit-placeholder" class="pr-edit-placeholder">
                    Select the edit icon from a booking row to load its Profit Report details here.
                </div>

                <form id="pr-edit-form" method="POST" style="display:none">
                    @csrf

                    <div class="pr-edit-grid">
                        <div class="pr-field">
                            <label>Date</label>
                            <input type="date" name="report_date">
                        </div>

                        <div class="pr-field">
                            <label>Booking ID (Display)</label>
                            <input type="text" name="booking_id_display">
                        </div>

                        <div class="pr-field">
                            <label>PNR</label>
                            <input type="text" name="pnr_override">
                        </div>

                        <div class="pr-field">
                            <label>Pax Names</label>
                            <input type="text" name="pax_names_override">
                        </div>

                        <div class="pr-field">
                            <label>Travel Dates</label>
                            <input type="text" name="travel_dates_override">
                        </div>

                        <div class="pr-field">
                            <label>Trip Type</label>
                            <select name="trip_type_override">
                                <option value="">—</option>
                                <option value="One Way">One Way</option>
                                <option value="Round Trip">Round Trip</option>
                                <option value="Multi City">Multi City</option>
                            </select>
                        </div>

                        <div class="pr-field wide">
                            <label>Origin - Destination</label>
                            <input type="text" name="route_override">
                        </div>

                        <div class="pr-field wide">
                            <label>Airlines</label>
                            <input type="text" name="airlines_override">
                        </div>

                        <div class="pr-field">
                            <label>Sale Amount</label>
                            <input class="js-sale" type="number" step="0.01" name="sale_amount_override">
                        </div>

                        <div class="pr-field">
                            <label>TKT Cost</label>
                            <input class="js-ticket" type="number" step="0.01" name="ticket_cost_override">
                        </div>

                        <div class="pr-field">
                            <label>MCO (Auto)</label>
                            <div class="pr-formula js-mco">$0.00</div>
                        </div>

                        <div class="pr-field">
                            <label>Merchant Fee</label>
                            <input class="js-merchant" type="number" step="0.01" min="0" name="merchant_fee_override">
                        </div>

                        <div class="pr-field">
                            <label>Supplier Fees</label>
                            <input class="js-supplier" type="number" step="0.01" min="0" name="supplier_fees">
                        </div>

                        <div class="pr-field">
                            <label>Net MCO (Auto)</label>
                            <div class="pr-formula js-net">$0.00</div>
                        </div>

                        <div class="pr-field">
                            <label>Agent</label>
                            <select name="agent_override">
                                <option value="">Unassigned</option>
                                @foreach($adminOptions as $admin)
                                    <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="pr-field">
                            <label>Status</label>
                            <input type="text" name="status_override">
                        </div>

                        <div class="pr-field">
                            <label>Issuance Type</label>
                            <input type="text" name="issuance_type">
                        </div>

                        <div class="pr-field">
                            <label>Issuance Date</label>
                            <input type="date" name="issuance_date">
                        </div>

                        <div class="pr-field wide">
                            <label>Remarks</label>
                            <textarea name="remarks"></textarea>
                        </div>
                    </div>

                    <div class="pr-edit-buttons">
                        <button type="button" class="pr-btn" id="pr-edit-cancel">Cancel</button>
                        <button type="submit" class="pr-btn pr-btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="pr-card">
            <div class="pr-card-head">
                <i class="fa-solid fa-clock-rotate-left"></i>
                Edit History (Audit Log)
            </div>

            <div class="pr-mini-wrap">
                <table class="pr-mini-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Booking</th>
                            <th>Field</th>
                            <th>Old Value</th>
                            <th>New Value</th>
                            <th>Edited By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $auditRows = 0; @endphp

                        @foreach($auditHistory as $audit)
                            @php
                                $oldAudit = json_decode($audit->old_values, true) ?: [];
                                $newAudit = json_decode($audit->new_values, true) ?: [];
                                $auditKeys = array_unique(array_merge(array_keys($oldAudit), array_keys($newAudit)));
                            @endphp

                            @foreach($auditKeys as $field)
                                @php
                                    $oldValue = $oldAudit[$field] ?? null;
                                    $newValue = $newAudit[$field] ?? null;
                                @endphp

                                @if((string)$oldValue !== (string)$newValue)
                                    @php $auditRows++; @endphp
                                    <tr>
                                        <td>{{ $audit->created_at }}</td>
                                        <td>#{{ $audit->booking_id }}</td>
                                        <td>{{ ucwords(str_replace('_',' ',$field)) }}</td>
                                        <td class="pr-audit-value">{{ $oldValue === null || $oldValue === '' ? '—' : $oldValue }}</td>
                                        <td class="pr-audit-value">{{ $newValue === null || $newValue === '' ? '—' : $newValue }}</td>
                                        <td>{{ $audit->changed_by_name ?: ('Admin #'.$audit->changed_by) }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        @endforeach

                        @if($auditRows === 0)
                            <tr>
                                <td colspan="6" class="pr-muted">No Profit Report edits recorded yet.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="pr-card">
            <div class="pr-card-head">
                <i class="fa-solid fa-chart-simple"></i>
                Agent Performance Summary
            </div>

            <div class="pr-mini-wrap">
                <table class="pr-mini-table">
                    <thead>
                        <tr>
                            <th>Agent</th>
                            <th>Bookings</th>
                            <th>Sales</th>
                            <th>Net MCO</th>
                            <th>Margin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($agentPerformance as $agent)
                            @php
                                $margin = (float)$agent['sales'] != 0
                                    ? ((float)$agent['net_mco'] / (float)$agent['sales']) * 100
                                    : 0;
                            @endphp
                            <tr>
                                <td><strong>{{ $agent['agent'] }}</strong></td>
                                <td>{{ number_format($agent['bookings']) }}</td>
                                <td>{{ $money($agent['sales']) }}</td>
                                <td>{{ $money($agent['net_mco']) }}</td>
                                <td class="pr-mini-positive">{{ number_format($margin,2) }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="pr-muted">No agent performance data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    @endif

</div>

<script>
(function(){
    const rows = Array.from(document.querySelectorAll('.js-report-row'));
    const pageSize = 8;
    let currentPage = 1;
    const pager = document.getElementById('pr-pagination');
    const info = document.getElementById('pr-page-info');

    function renderPage(){
        if (!rows.length || !pager || !info) return;

        const totalPages = Math.max(1, Math.ceil(rows.length / pageSize));
        currentPage = Math.min(currentPage, totalPages);

        rows.forEach(function(row, index){
            const start = (currentPage - 1) * pageSize;
            const end = start + pageSize;
            row.style.display = (index >= start && index < end) ? '' : 'none';
        });

        const startEntry = ((currentPage - 1) * pageSize) + 1;
        const endEntry = Math.min(currentPage * pageSize, rows.length);
        info.textContent = 'Showing ' + startEntry + ' to ' + endEntry + ' of ' + rows.length + ' entries';

        pager.innerHTML = '';

        const prev = document.createElement('button');
        prev.type = 'button';
        prev.className = 'pr-page-btn';
        prev.innerHTML = '‹';
        prev.disabled = currentPage === 1;
        prev.onclick = function(){ currentPage--; renderPage(); };
        pager.appendChild(prev);

        for (let p = 1; p <= totalPages; p++) {
            if (totalPages > 8 && p > 3 && p < totalPages - 2 && Math.abs(p-currentPage) > 1) {
                if (!pager.querySelector('[data-ellipsis="' + p + '"]')) {
                    if (p === 4 || p === totalPages - 3) {
                        const dots = document.createElement('span');
                        dots.textContent = '…';
                        dots.style.padding = '0 3px';
                        pager.appendChild(dots);
                    }
                }
                continue;
            }

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'pr-page-btn' + (p === currentPage ? ' active' : '');
            btn.textContent = p;
            btn.onclick = function(){ currentPage = p; renderPage(); };
            pager.appendChild(btn);
        }

        const next = document.createElement('button');
        next.type = 'button';
        next.className = 'pr-page-btn';
        next.innerHTML = '›';
        next.disabled = currentPage === totalPages;
        next.onclick = function(){ currentPage++; renderPage(); };
        pager.appendChild(next);
    }

    renderPage();

    const form = document.getElementById('pr-edit-form');
    const placeholder = document.getElementById('pr-edit-placeholder');
    const card = document.getElementById('pr-edit-card');
    const cancel = document.getElementById('pr-edit-cancel');

    if (!form) return;

    const fields = {
        report_date: form.querySelector('[name="report_date"]'),
        booking_id_display: form.querySelector('[name="booking_id_display"]'),
        pnr_override: form.querySelector('[name="pnr_override"]'),
        pax_names_override: form.querySelector('[name="pax_names_override"]'),
        travel_dates_override: form.querySelector('[name="travel_dates_override"]'),
        trip_type_override: form.querySelector('[name="trip_type_override"]'),
        route_override: form.querySelector('[name="route_override"]'),
        airlines_override: form.querySelector('[name="airlines_override"]'),
        sale_amount_override: form.querySelector('[name="sale_amount_override"]'),
        ticket_cost_override: form.querySelector('[name="ticket_cost_override"]'),
        merchant_fee_override: form.querySelector('[name="merchant_fee_override"]'),
        supplier_fees: form.querySelector('[name="supplier_fees"]'),
        agent_override: form.querySelector('[name="agent_override"]'),
        status_override: form.querySelector('[name="status_override"]'),
        issuance_type: form.querySelector('[name="issuance_type"]'),
        issuance_date: form.querySelector('[name="issuance_date"]'),
        remarks: form.querySelector('[name="remarks"]')
    };

    const mco = form.querySelector('.js-mco');
    const net = form.querySelector('.js-net');

    function number(el){
        const value = parseFloat(el && el.value);
        return Number.isFinite(value) ? value : 0;
    }

    function money(value){
        const sign = value < 0 ? '-$' : '$';
        return sign + Math.abs(value).toFixed(2);
    }

    function recalc(){
        const gross = number(fields.sale_amount_override) - number(fields.ticket_cost_override);
        const finalNet = gross - number(fields.merchant_fee_override) - number(fields.supplier_fees);

        mco.textContent = money(gross);
        net.textContent = money(finalNet);

        mco.classList.toggle('pr-negative', gross < 0);
        mco.classList.toggle('pr-positive', gross >= 0);
        net.classList.toggle('pr-negative', finalNet < 0);
        net.classList.toggle('pr-positive', finalNet >= 0);
    }

    [
        fields.sale_amount_override,
        fields.ticket_cost_override,
        fields.merchant_fee_override,
        fields.supplier_fees
    ].forEach(function(el){
        if (el) el.addEventListener('input', recalc);
    });

    document.querySelectorAll('.js-edit-row').forEach(function(button){
        button.addEventListener('click', function(){
            form.action = button.dataset.url;

            fields.report_date.value = button.dataset.date || '';
            fields.booking_id_display.value = button.dataset.bookingDisplay || '';
            fields.pnr_override.value = button.dataset.pnr || '';
            fields.pax_names_override.value = button.dataset.pax || '';
            fields.travel_dates_override.value = button.dataset.travel || '';
            fields.trip_type_override.value = button.dataset.trip || '';
            fields.route_override.value = button.dataset.route || '';
            fields.airlines_override.value = button.dataset.airlines || '';
            fields.sale_amount_override.value = button.dataset.sale || '0.00';
            fields.ticket_cost_override.value = button.dataset.ticket || '0.00';
            fields.merchant_fee_override.value = button.dataset.merchant || '0.00';
            fields.supplier_fees.value = button.dataset.supplier || '0.00';
            const agentId = button.dataset.agent || '';
            const agentName = button.dataset.agentName || '';

            fields.agent_override.value = agentId;

            if (agentId && fields.agent_override.value !== agentId) {
                const option = document.createElement('option');
                option.value = agentId;
                option.textContent = agentName || ('Agent #' + agentId);
                option.dataset.currentAssignment = '1';
                fields.agent_override.appendChild(option);
                fields.agent_override.value = agentId;
            }

            fields.status_override.value = button.dataset.status || '';
            fields.issuance_type.value = button.dataset.issuanceType || '';
            fields.issuance_date.value = button.dataset.issuanceDate || '';
            fields.remarks.value = button.dataset.remarks || '';

            placeholder.style.display = 'none';
            form.style.display = 'block';
            recalc();

            card.scrollIntoView({behavior:'smooth', block:'start'});
        });
    });

    if (cancel) {
        cancel.addEventListener('click', function(){
            form.style.display = 'none';
            placeholder.style.display = 'block';
            form.reset();
        });
    }
})();
</script>
@endsection
