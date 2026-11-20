<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProfitReportController extends Controller
{
    private function guard(Request $request)
    {
        if (!$request->session()->has('crm_admin_id')) {
            return redirect()->route('crm.login');
        }

        return null;
    }

    private function adminId(Request $request): int
    {
        return (int) $request->session()->get('crm_admin_id');
    }

    private function roleSlug(Request $request): string
    {
        $adminId = $this->adminId($request);

        if (!$adminId) {
            return '';
        }

        if (Schema::hasTable('crm_access_roles') && Schema::hasColumn('admins', 'crm_access_role_id')) {
            return (string) DB::table('admins as a')
                ->leftJoin('crm_access_roles as r', 'a.crm_access_role_id', '=', 'r.id')
                ->where('a.id', $adminId)
                ->value('r.slug');
        }

        return '';
    }

    private function isSuperAdmin(Request $request): bool
    {
        return $this->roleSlug($request) === 'super-admin';
    }

    private function json($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function firstSegment(array $segments): array
    {
        if (!$segments) {
            return [];
        }

        if (isset($segments[0]) && is_array($segments[0])) {
            return $segments[0];
        }

        foreach ($segments as $segment) {
            if (is_array($segment)) {
                return $segment;
            }
        }

        return [];
    }

    private function lastSegment(array $segments): array
    {
        if (!$segments) {
            return [];
        }

        $arraySegments = array_values(array_filter($segments, 'is_array'));

        return $arraySegments ? end($arraySegments) : [];
    }

    private function passengerNames(array $passengers, Booking $booking): string
    {
        $names = [];

        foreach ($passengers as $pax) {
            if (!is_array($pax)) {
                continue;
            }

            $name = trim((string)(
                $pax['Name']
                ?? $pax['PassengerName']
                ?? $pax['FullName']
                ?? $pax['name']
                ?? ''
            ));

            if ($name !== '') {
                $names[] = $name;
            }
        }

        if ($names) {
            return implode(', ', array_unique($names));
        }

        return trim((string)$booking->o_Name) ?: 'Unknown';
    }

    private function travelDates(array $go, array $back): string
    {
        $firstGo = $this->firstSegment($go);
        $firstBack = $this->firstSegment($back);

        $depart = trim((string)(
            $firstGo['Departure_Date']
            ?? $firstGo['DepartureDate']
            ?? ''
        ));

        $return = trim((string)(
            $firstBack['Departure_Date']
            ?? $firstBack['DepartureDate']
            ?? ''
        ));

        if ($depart && $return) {
            return $depart.' → '.$return;
        }

        return $depart ?: ($return ?: 'Unknown');
    }

    public function index(Request $request)
    {
        if ($redirect = $this->guard($request)) {
            return $redirect;
        }

        if (!app(\App\Services\CrmAccessService::class)->can($this->adminId($request), 'profit_report.view')) {
            abort(403, 'You do not have permission to view the Profit Report.');
        }

        $query = Booking::query();

        if (!$this->isSuperAdmin($request)) {
            $query->where('assign_to', $this->adminId($request));
        } elseif ($request->filled('agent') && ctype_digit((string)$request->input('agent'))) {
            $query->where('assign_to', (int)$request->input('agent'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        if ($request->filled('booking_id') && ctype_digit((string)$request->input('booking_id'))) {
            $query->where('id', (int)$request->input('booking_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $bookings = $query
            ->orderByDesc('created_at')
            ->get();

        $ids = $bookings->pluck('id')->all();

        $bookingMeta = Schema::hasTable('booking_crm_meta') && $ids
            ? DB::table('booking_crm_meta')->whereIn('booking_id', $ids)->get()->keyBy('booking_id')
            : collect();

        $financials = Schema::hasTable('booking_financials') && $ids
            ? DB::table('booking_financials')->whereIn('booking_id', $ids)->get()->keyBy('booking_id')
            : collect();

        $profitMeta = Schema::hasTable('profit_report_meta') && $ids
            ? DB::table('profit_report_meta')->whereIn('booking_id', $ids)->get()->keyBy('booking_id')
            : collect();

        $admins = DB::table('admins')->pluck('name', 'id');

        $records = $bookings->map(function (Booking $booking) use ($bookingMeta, $financials, $profitMeta, $admins) {
            $search = $this->json($booking->i_field_encode);
            $go = $this->json($booking->o_SectorGo);
            $back = $this->json($booking->o_SectorBack);
            $passengers = $this->json($booking->o_Passenger);

            $first = $this->firstSegment($go);
            $last = $this->lastSegment($go);

            $from = strtoupper(trim((string)(
                $first['Departure_Iata']
                ?? $first['DepartureIata']
                ?? $search['IATAFrom']
                ?? ''
            )));

            $to = strtoupper(trim((string)(
                $last['Arrival_Iata']
                ?? $last['ArrivalIata']
                ?? $search['IATATo']
                ?? ''
            )));

            $airline = trim((string)(
                $first['AirlineName']
                ?? $first['OperatingAirlineName']
                ?? $first['Airline']
                ?? ''
            ));

            $tripRaw = (string)(
                $search['FlightType']
                ?? (!empty($booking->o_SectorBack) ? 'round_trip' : 'one_way')
            );

            $tripType = strtolower($tripRaw) === 'round_trip'
                ? 'Round Trip'
                : (strtolower($tripRaw) === 'multi_city' ? 'Multi City' : 'One Way');

            $bm = $bookingMeta->get($booking->id);
            $bf = $financials->get($booking->id);
            $pm = $profitMeta->get($booking->id);

            $baseSaleAmount = $bf
                ? (float)($bf->selling_amount ?? 0)
                : max(0, (float)$booking->o_FullPrice);

            $baseTicketCost = $bf ? (float)($bf->supplier_cost ?? 0) : 0.0;
            $baseMerchantFee = $bf ? (float)($bf->processing_cost ?? 0) : 0.0;

            $saleAmount = ($pm && $pm->sale_amount_override !== null)
                ? (float)$pm->sale_amount_override
                : $baseSaleAmount;

            $ticketCost = ($pm && $pm->ticket_cost_override !== null)
                ? (float)$pm->ticket_cost_override
                : $baseTicketCost;

            $merchantFee = ($pm && $pm->merchant_fee_override !== null)
                ? (float)$pm->merchant_fee_override
                : $baseMerchantFee;

            $supplierFees = $pm ? (float)($pm->supplier_fees ?? 0) : 0.0;

            $agentId = ($pm && $pm->agent_override !== null)
                ? (int)$pm->agent_override
                : ($booking->assign_to ? (int)$booking->assign_to : null);

            $mco = round($saleAmount - $ticketCost, 2);
            $netMco = round($mco - $merchantFee - $supplierFees, 2);

            return [
                'date' => ($pm && $pm->report_date) ? $pm->report_date : optional($booking->created_at)->format('Y-m-d'),
                'booking_id' => $booking->id,
                'booking_id_display' => ($pm && $pm->booking_id_display) ? $pm->booking_id_display : (string)$booking->id,
                'pnr' => ($pm && $pm->pnr_override !== null) ? $pm->pnr_override : ($bm->pnr ?? ''),
                'pax_names' => ($pm && $pm->pax_names_override !== null) ? $pm->pax_names_override : $this->passengerNames($passengers, $booking),
                'travel_dates' => ($pm && $pm->travel_dates_override !== null) ? $pm->travel_dates_override : $this->travelDates($go, $back),
                'trip_type' => ($pm && $pm->trip_type_override !== null) ? $pm->trip_type_override : $tripType,
                'origin_destination' => ($pm && $pm->route_override !== null) ? $pm->route_override : (($from && $to) ? "{$from} → {$to}" : 'Unknown'),
                'airlines' => ($pm && $pm->airlines_override !== null) ? $pm->airlines_override : ($airline ?: 'Unknown'),
                'sale_amount' => round($saleAmount, 2),
                'ticket_cost' => round($ticketCost, 2),
                'mco' => $mco,
                'merchant_fee' => round($merchantFee, 2),
                'supplier_fees' => round($supplierFees, 2),
                'net_mco' => $netMco,
                'agent_id' => $agentId,
                'agent' => $agentId
                    ? ($admins[$agentId] ?? ('Agent #'.$agentId))
                    : 'Unassigned',
                'status' => ($pm && $pm->status_override !== null) ? $pm->status_override : ($booking->status ?: 'Unknown'),
                'issuance_type' => $pm->issuance_type ?? '',
                'issuance_date' => $pm->issuance_date ?? null,
                'remarks' => $pm->remarks ?? '',
            ];
        });

        if ($request->filled('pnr')) {
            $needlePnr = strtolower(trim((string)$request->input('pnr')));
            $records = $records->filter(function ($row) use ($needlePnr) {
                return str_contains(strtolower((string)$row['pnr']), $needlePnr);
            })->values();
        }

        if ($request->filled('pax')) {
            $needlePax = strtolower(trim((string)$request->input('pax')));
            $records = $records->filter(function ($row) use ($needlePax) {
                return str_contains(strtolower((string)$row['pax_names']), $needlePax);
            })->values();
        }

        if ($request->filled('airline')) {
            $needleAirline = strtolower(trim((string)$request->input('airline')));
            $records = $records->filter(function ($row) use ($needleAirline) {
                return str_contains(strtolower((string)$row['airlines']), $needleAirline);
            })->values();
        }

        if ($request->filled('trip_type')) {
            $tripType = strtolower(trim((string)$request->input('trip_type')));
            $records = $records->filter(function ($row) use ($tripType) {
                return strtolower((string)$row['trip_type']) === $tripType;
            })->values();
        }

        if ($request->filled('issuance_type')) {
            $issuanceType = strtolower(trim((string)$request->input('issuance_type')));
            $records = $records->filter(function ($row) use ($issuanceType) {
                return strtolower((string)$row['issuance_type']) === $issuanceType;
            })->values();
        }

        $isSuperAdmin = $this->isSuperAdmin($request);

        $kpis = [
            'total_sales' => round((float)$records->sum('sale_amount'), 2),
            'ticket_cost' => round((float)$records->sum('ticket_cost'), 2),
            'mco' => round((float)$records->sum('mco'), 2),
            'merchant_fees' => round((float)$records->sum('merchant_fee'), 2),
            'supplier_fees' => round((float)$records->sum('supplier_fees'), 2),
            'net_mco' => round((float)$records->sum('net_mco'), 2),
            'bookings' => $records->count(),
        ];

        $adminOptions = $isSuperAdmin
            ? DB::table('admins')
                ->where('status', 1)
                ->whereNull('crm_deleted_at')
                ->orderBy('name')
                ->get(['id', 'name'])
            : collect();

        $agentPerformance = $isSuperAdmin
            ? $records
                ->groupBy('agent')
                ->map(function ($rows, $agent) {
                    return [
                        'agent' => $agent,
                        'bookings' => $rows->count(),
                        'sales' => round((float)$rows->sum('sale_amount'), 2),
                        'mco' => round((float)$rows->sum('mco'), 2),
                        'net_mco' => round((float)$rows->sum('net_mco'), 2),
                    ];
                })
                ->sortByDesc('net_mco')
                ->values()
            : collect();

        $auditHistory = collect();

        if ($isSuperAdmin && Schema::hasTable('profit_report_audit')) {
            $visibleBookingIds = $records->pluck('booking_id')->all();

            if ($visibleBookingIds) {
                $auditHistory = DB::table('profit_report_audit as pra')
                    ->leftJoin('admins as a', 'pra.changed_by', '=', 'a.id')
                    ->whereIn('pra.booking_id', $visibleBookingIds)
                    ->orderByDesc('pra.created_at')
                    ->limit(100)
                    ->get([
                        'pra.id',
                        'pra.booking_id',
                        'pra.changed_by',
                        'pra.old_values',
                        'pra.new_values',
                        'pra.created_at',
                        'a.name as changed_by_name',
                    ]);
            }
        }

        return view('crm.profit-report', [
            'records' => $records,
            'kpis' => $kpis,
            'isSuperAdmin' => $isSuperAdmin,
            'roleSlug' => $this->roleSlug($request),
            'adminOptions' => $adminOptions,
            'agentPerformance' => $agentPerformance,
            'auditHistory' => $auditHistory,
        ]);
    }

    public function update(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) {
            return $redirect;
        }

        if (!app(\App\Services\CrmAccessService::class)->can($this->adminId($request), 'profit_report.edit')) {
            abort(403, 'You do not have permission to edit Profit Report records.');
        }

        if (!$this->isSuperAdmin($request)) {
            abort(403, 'Only Super Admin can edit Profit Report records.');
        }

        $booking = Booking::findOrFail($id);

        $data = $request->validate([
            'report_date' => ['nullable', 'date'],
            'booking_id_display' => ['nullable', 'string', 'max:100'],
            'pnr_override' => ['nullable', 'string', 'max:100'],
            'pax_names_override' => ['nullable', 'string', 'max:5000'],
            'travel_dates_override' => ['nullable', 'string', 'max:255'],
            'trip_type_override' => ['nullable', 'string', 'max:100'],
            'route_override' => ['nullable', 'string', 'max:255'],
            'airlines_override' => ['nullable', 'string', 'max:255'],
            'sale_amount_override' => ['nullable', 'numeric'],
            'ticket_cost_override' => ['nullable', 'numeric'],
            'merchant_fee_override' => ['nullable', 'numeric', 'min:0'],
            'supplier_fees' => ['required', 'numeric', 'min:0'],
            'agent_override' => ['nullable', 'integer', 'exists:admins,id'],
            'status_override' => ['nullable', 'string', 'max:100'],
            'issuance_type' => ['nullable', 'string', 'max:100'],
            'issuance_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ]);

        $old = DB::table('profit_report_meta')
            ->where('booking_id', $booking->id)
            ->first();

        $keys = [
            'report_date','booking_id_display','pnr_override','pax_names_override',
            'travel_dates_override','trip_type_override','route_override','airlines_override',
            'sale_amount_override','ticket_cost_override','merchant_fee_override',
            'supplier_fees','agent_override','status_override',
            'issuance_type','issuance_date','remarks'
        ];

        $oldValues = [];
        foreach ($keys as $key) {
            $oldValues[$key] = $old->$key ?? null;
        }

        $newValues = [
            'report_date' => $data['report_date'] ?? null,
            'booking_id_display' => $data['booking_id_display'] ?? null,
            'pnr_override' => $data['pnr_override'] ?? null,
            'pax_names_override' => $data['pax_names_override'] ?? null,
            'travel_dates_override' => $data['travel_dates_override'] ?? null,
            'trip_type_override' => $data['trip_type_override'] ?? null,
            'route_override' => $data['route_override'] ?? null,
            'airlines_override' => $data['airlines_override'] ?? null,
            'sale_amount_override' => isset($data['sale_amount_override']) ? round((float)$data['sale_amount_override'], 2) : null,
            'ticket_cost_override' => isset($data['ticket_cost_override']) ? round((float)$data['ticket_cost_override'], 2) : null,
            'merchant_fee_override' => isset($data['merchant_fee_override']) ? round((float)$data['merchant_fee_override'], 2) : null,
            'supplier_fees' => round((float)$data['supplier_fees'], 2),
            'agent_override' => $data['agent_override'] ?? null,
            'status_override' => $data['status_override'] ?? null,
            'issuance_type' => $data['issuance_type'] ?? null,
            'issuance_date' => $data['issuance_date'] ?? null,
            'remarks' => $data['remarks'] ?? null,
        ];

        DB::transaction(function () use ($booking, $newValues, $oldValues, $request) {
            $existing = DB::table('profit_report_meta')
                ->where('booking_id', $booking->id)
                ->exists();

            $payload = $newValues + [
                'updated_by' => $this->adminId($request),
                'updated_at' => now(),
            ];

            if ($existing) {
                DB::table('profit_report_meta')
                    ->where('booking_id', $booking->id)
                    ->update($payload);
            } else {
                $payload['booking_id'] = $booking->id;
                $payload['created_at'] = now();

                DB::table('profit_report_meta')->insert($payload);
            }

            if ($oldValues !== $newValues) {
                DB::table('profit_report_audit')->insert([
                    'booking_id' => $booking->id,
                    'changed_by' => $this->adminId($request),
                    'old_values' => json_encode($oldValues),
                    'new_values' => json_encode($newValues),
                    'created_at' => now(),
                ]);
            }
        });

        return redirect()
            ->back()
            ->with('success', 'Profit Report updated successfully.');
    }


    public function export(Request $request)
    {
        if ($redirect = $this->guard($request)) {
            return $redirect;
        }

        if (!app(\App\Services\CrmAccessService::class)->can($this->adminId($request), 'profit_report.export')) {
            abort(403, 'You do not have permission to export the Profit Report.');
        }

        $query = Booking::query();

        if (!$this->isSuperAdmin($request)) {
            $query->where('assign_to', $this->adminId($request));
        } elseif ($request->filled('agent') && ctype_digit((string)$request->input('agent'))) {
            $query->where('assign_to', (int)$request->input('agent'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        if ($request->filled('booking_id') && ctype_digit((string)$request->input('booking_id'))) {
            $query->where('id', (int)$request->input('booking_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $bookings = $query
            ->orderByDesc('created_at')
            ->get();

        $ids = $bookings->pluck('id')->all();

        $bookingMeta = Schema::hasTable('booking_crm_meta') && $ids
            ? DB::table('booking_crm_meta')->whereIn('booking_id', $ids)->get()->keyBy('booking_id')
            : collect();

        $financials = Schema::hasTable('booking_financials') && $ids
            ? DB::table('booking_financials')->whereIn('booking_id', $ids)->get()->keyBy('booking_id')
            : collect();

        $profitMeta = Schema::hasTable('profit_report_meta') && $ids
            ? DB::table('profit_report_meta')->whereIn('booking_id', $ids)->get()->keyBy('booking_id')
            : collect();

        $admins = DB::table('admins')->pluck('name', 'id');

        $filename = 'profit-report-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($request, $bookings, $bookingMeta, $financials, $profitMeta, $admins) {
            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'Date',
                'Booking ID',
                'PNR',
                'Pax Names',
                'Travel Dates',
                'Trip Type',
                'Origin-Destination',
                'Airlines',
                'SALE AMOUNT',
                'TKT COST',
                'MCO',
                'MERCHANT FEE',
                'SUPPLIER FEES',
                'NET MCO',
                'AGENT',
                'Status',
                'Issuance Type',
                'Issuance Date',
                'Remarks',
            ]);

            foreach ($bookings as $booking) {
                $search = $this->json($booking->i_field_encode);
                $go = $this->json($booking->o_SectorGo);
                $back = $this->json($booking->o_SectorBack);
                $passengers = $this->json($booking->o_Passenger);

                $first = $this->firstSegment($go);
                $last = $this->lastSegment($go);

                $from = strtoupper(trim((string)(
                    $first['Departure_Iata']
                    ?? $first['DepartureIata']
                    ?? $search['IATAFrom']
                    ?? ''
                )));

                $to = strtoupper(trim((string)(
                    $last['Arrival_Iata']
                    ?? $last['ArrivalIata']
                    ?? $search['IATATo']
                    ?? ''
                )));

                $airline = trim((string)(
                    $first['AirlineName']
                    ?? $first['OperatingAirlineName']
                    ?? $first['Airline']
                    ?? ''
                ));

                $tripRaw = (string)(
                    $search['FlightType']
                    ?? (!empty($booking->o_SectorBack) ? 'round_trip' : 'one_way')
                );

                $tripType = strtolower($tripRaw) === 'round_trip'
                    ? 'Round Trip'
                    : (strtolower($tripRaw) === 'multi_city' ? 'Multi City' : 'One Way');

                $bm = $bookingMeta->get($booking->id);
                $bf = $financials->get($booking->id);
                $pm = $profitMeta->get($booking->id);

                $baseSaleAmount = $bf
                    ? (float)($bf->selling_amount ?? 0)
                    : max(0, (float)$booking->o_FullPrice);

                $baseTicketCost = $bf ? (float)($bf->supplier_cost ?? 0) : 0.0;
                $baseMerchantFee = $bf ? (float)($bf->processing_cost ?? 0) : 0.0;

                $saleAmount = ($pm && $pm->sale_amount_override !== null) ? (float)$pm->sale_amount_override : $baseSaleAmount;
                $ticketCost = ($pm && $pm->ticket_cost_override !== null) ? (float)$pm->ticket_cost_override : $baseTicketCost;
                $merchantFee = ($pm && $pm->merchant_fee_override !== null) ? (float)$pm->merchant_fee_override : $baseMerchantFee;
                $supplierFees = $pm ? (float)($pm->supplier_fees ?? 0) : 0.0;

                $mco = round($saleAmount - $ticketCost, 2);
                $netMco = round($mco - $merchantFee - $supplierFees, 2);

                $pnr = (string)(($pm && $pm->pnr_override !== null) ? $pm->pnr_override : ($bm->pnr ?? ''));
                $paxNames = (string)(($pm && $pm->pax_names_override !== null) ? $pm->pax_names_override : $this->passengerNames($passengers, $booking));
                $issuanceType = (string)($pm->issuance_type ?? '');

                if ($request->filled('pnr')
                    && !str_contains(strtolower($pnr), strtolower(trim((string)$request->input('pnr'))))) {
                    continue;
                }

                if ($request->filled('pax')
                    && !str_contains(strtolower($paxNames), strtolower(trim((string)$request->input('pax'))))) {
                    continue;
                }

                if ($request->filled('airline')
                    && !str_contains(strtolower((string)$airline), strtolower(trim((string)$request->input('airline'))))) {
                    continue;
                }

                if ($request->filled('trip_type')
                    && strtolower($tripType) !== strtolower(trim((string)$request->input('trip_type')))) {
                    continue;
                }

                if ($request->filled('issuance_type')
                    && strtolower($issuanceType) !== strtolower(trim((string)$request->input('issuance_type')))) {
                    continue;
                }

                $agentId = ($pm && $pm->agent_override !== null)
                    ? (int)$pm->agent_override
                    : ($booking->assign_to ? (int)$booking->assign_to : null);

                fputcsv($out, [
                    ($pm && $pm->report_date) ? $pm->report_date : optional($booking->created_at)->format('Y-m-d'),
                    ($pm && $pm->booking_id_display) ? $pm->booking_id_display : $booking->id,
                    $pnr,
                    $paxNames,
                    ($pm && $pm->travel_dates_override !== null) ? $pm->travel_dates_override : $this->travelDates($go, $back),
                    ($pm && $pm->trip_type_override !== null) ? $pm->trip_type_override : $tripType,
                    ($pm && $pm->route_override !== null) ? $pm->route_override : (($from && $to) ? "{$from} → {$to}" : 'Unknown'),
                    ($pm && $pm->airlines_override !== null) ? $pm->airlines_override : ($airline ?: 'Unknown'),
                    number_format($saleAmount, 2, '.', ''),
                    number_format($ticketCost, 2, '.', ''),
                    number_format($mco, 2, '.', ''),
                    number_format($merchantFee, 2, '.', ''),
                    number_format($supplierFees, 2, '.', ''),
                    number_format($netMco, 2, '.', ''),
                    $agentId ? ($admins[$agentId] ?? ('Agent #'.$agentId)) : 'Unassigned',
                    ($pm && $pm->status_override !== null) ? $pm->status_override : ($booking->status ?: 'Unknown'),
                    $pm->issuance_type ?? '',
                    $pm->issuance_date ?? '',
                    $pm->remarks ?? '',
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

}
