<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    private function guard(Request $request)
    {
        if (!$request->session()->has('crm_admin_id')) {
            return redirect()->route('crm.login');
        }
        return null;
    }

    public function index(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        [$from, $to, $preset] = $this->dateRange($request);
        $report = $this->buildReport($request, $from, $to);

        return view('crm.reports', array_merge($report, compact('from','to','preset')));
    }

    public function export(Request $request): StreamedResponse
    {
        if ($redirect = $this->guard($request)) return $redirect;

        [$from, $to] = $this->dateRange($request);
        $report = $this->buildReport($request, $from, $to);
        $rows = $report['records'];

        $filename = 'fareshunt-report-'.$from->format('Ymd').'-'.$to->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Booking ID','Booking Ref','Customer','Email','Phone','Booking Status',
                'Payment Status','Ticket Status','Source','Route','Marketing Airline',
                'Operating Airline','Cabin','Trip Type','Passengers','Booking Value',
                'Markup Rule','Markup Discount','Assigned Agent','Booking Date'
            ]);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['booking']->id,
                    fh_booking_ref($r['booking']->id),
                    $r['booking']->o_Name,
                    $r['booking']->o_Email,
                    $r['booking']->o_Phone,
                    $r['booking']->status,
                    $r['payment_status'],
                    $r['ticket_status'],
                    $r['booking']->source,
                    $r['route'],
                    $r['airline'],
                    $r['operating_airline'],
                    $r['cabin'],
                    $r['trip_type'],
                    $r['pax'],
                    number_format($r['fare'], 2, '.', ''),
                    $r['markup_name'],
                    number_format($r['markup_discount'], 2, '.', ''),
                    $r['agent_name'],
                    optional($r['booking']->created_at)->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function buildReport(Request $request, Carbon $from, Carbon $to): array
    {
        $query = Booking::query()
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);

        if ($request->filled('source')) $query->where('source', $request->input('source'));
        if ($request->filled('booking_status')) $query->where('status', $request->input('booking_status'));
        if ($request->filled('agent') && ctype_digit((string)$request->input('agent'))) {
            $query->where('assign_to', (int)$request->input('agent'));
        }

        $bookings = $query->orderBy('created_at')->get();
        $ids = $bookings->pluck('id')->all();

        $bookingMeta = Schema::hasTable('booking_crm_meta') && $ids
            ? DB::table('booking_crm_meta')->whereIn('booking_id',$ids)->get()->keyBy('booking_id')
            : collect();

        $paymentMeta = Schema::hasTable('payment_crm_meta') && $ids
            ? DB::table('payment_crm_meta')->whereIn('booking_id',$ids)->get()->keyBy('booking_id')
            : collect();

        $admins = DB::table('admins')->pluck('name','id');

        $records = $bookings->map(function($b) use ($bookingMeta,$paymentMeta,$admins) {
            $search = $this->json($b->i_field_encode);
            $go = $this->json($b->o_SectorGo);
            $other = $this->json($b->o_Other);
            $passengers = $this->json($b->o_Passenger);

            $first = $this->firstSegment($go);
            $last = $this->lastSegment($go);

            $from = strtoupper(trim((string)(
                $first['Departure_Iata'] ?? $first['DepartureIata'] ?? $search['IATAFrom'] ?? ''
            )));
            $to = strtoupper(trim((string)(
                $last['Arrival_Iata'] ?? $last['ArrivalIata'] ?? $search['IATATo'] ?? ''
            )));

            $airline = strtoupper(trim((string)($first['Airline'] ?? '')));
            $operating = strtoupper(trim((string)($first['OperatingAirline'] ?? '')));
            $cabin = (string)($first['FlightClass'] ?? $search['FlightClass'] ?? 'Unknown');
            $trip = (string)($search['FlightType'] ?? (!empty($b->o_SectorBack) ? 'round_trip' : 'one_way'));

            $bm = $bookingMeta->get($b->id);
            $pm = $paymentMeta->get($b->id);

            $paymentStatus = $pm->payment_status ?? $bm->payment_status ?? $this->legacyPaymentStatus($b->status);
            $ticketStatus = $bm->ticket_status ?? $this->legacyTicketStatus($b->status);

            $markupName = trim((string)($other['MarkupName'] ?? ''));
            $markupDiscount = $this->money($other['MarkupDiscount'] ?? 0);
            $fare = max(0, $this->money($b->o_FullPrice));

            $pax = is_array($passengers) ? count($passengers) : max(
                1,
                (int)($search['Adults'] ?? 0) + (int)($search['Childrens'] ?? 0) + (int)($search['Infants'] ?? 0)
            );

            $agentName = $b->assign_to ? ($admins[$b->assign_to] ?? ('Agent #'.$b->assign_to)) : 'Unassigned';

            return [
                'booking'=>$b,
                'fare'=>$fare,
                'route'=>($from && $to) ? "{$from} → {$to}" : 'Unknown',
                'from'=>$from ?: 'Unknown',
                'to'=>$to ?: 'Unknown',
                'airline'=>$airline ?: 'Unknown',
                'operating_airline'=>$operating ?: ($airline ?: 'Unknown'),
                'cabin'=>$cabin ?: 'Unknown',
                'trip_type'=>$trip === 'round_trip' ? 'Round Trip' : 'One Way',
                'pax'=>$pax,
                'payment_status'=>$paymentStatus ?: 'Untracked',
                'ticket_status'=>$ticketStatus ?: 'Untracked',
                'markup_name'=>$markupName ?: 'No rule recorded',
                'markup_discount'=>$markupDiscount,
                'agent_name'=>$agentName,
                'paid_amount'=>$pm ? max(0,(float)($pm->paid_amount ?? 0)) : 0.0,
                'refund_amount'=>$pm ? max(0,(float)($pm->refund_amount ?? 0)) : 0.0,
            ];
        });

        // Collection-level filters that depend on JSON-derived flight/payment metadata.
        $airlineFilter = strtoupper(trim((string)$request->input('airline','')));
        if ($airlineFilter !== '') {
            $records = $records->filter(fn($r) =>
                $r['airline'] === $airlineFilter || $r['operating_airline'] === $airlineFilter
            )->values();
        }

        $routeFilter = strtoupper(trim((string)$request->input('route','')));
        if ($routeFilter !== '') {
            $records = $records->filter(fn($r) =>
                str_contains(str_replace(' ','',$r['route']), str_replace([' ','-','>'],'',$routeFilter))
                || str_contains(str_replace(' ','',$r['route']), str_replace(' ','',$routeFilter))
            )->values();
        }

        $paymentFilter = trim((string)$request->input('payment_status',''));
        if ($paymentFilter !== '') {
            $records = $records->where('payment_status',$paymentFilter)->values();
        }

        $ticketFilter = trim((string)$request->input('ticket_status',''));
        if ($ticketFilter !== '') {
            $records = $records->where('ticket_status',$ticketFilter)->values();
        }

        $gross = $records->sum('fare');
        $count = $records->count();
        $average = $count ? $gross / $count : 0;
        $paidCount = $records->where('payment_status','Paid')->count();
        $ticketedCount = $records->filter(fn($r) =>
            in_array(strtolower($r['ticket_status']), ['ticketed','issued'], true)
            || str_contains(strtolower((string)$r['booking']->status),'ticket')
        )->count();
        $refundCount = $records->filter(fn($r) =>
            str_contains(strtolower($r['payment_status']),'refund') || $r['refund_amount'] > 0
        )->count();
        $paymentIssues = $records->filter(fn($r) =>
            in_array($r['payment_status'],['Failed','Declined','Chargeback'],true)
            || in_array($r['booking']->status,['Payment Declined','Chargeback'],true)
        )->count();

        $collected = $records->sum('paid_amount');
        $refundAmount = $records->sum('refund_amount');
        $netCollected = max(0,$collected-$refundAmount);

        $byStatus = $this->breakdown($records, fn($r) => $r['booking']->status ?: 'Unknown', 'fare');
        $bySource = $this->breakdown($records, fn($r) => $r['booking']->source ?: 'Unknown', 'fare');
        $byPayment = $this->breakdown($records, fn($r) => $r['payment_status'], 'fare');

        $topRoutes = $this->breakdown($records, fn($r) => $r['route'], 'fare')->take(10)->values();
        $topAirlines = $this->breakdown($records, fn($r) => $r['airline'], 'fare')->take(10)->values();
        $agents = $this->breakdown($records, fn($r) => $r['agent_name'], 'fare')->take(10)->values();

        $monthly = $records->groupBy(fn($r) => optional($r['booking']->created_at)->format('Y-m') ?: 'Unknown')
            ->map(function(Collection $rows, $month) {
                return [
                    'label'=>$month,
                    'bookings'=>$rows->count(),
                    'revenue'=>$rows->sum('fare'),
                    'avg'=>$rows->count() ? $rows->sum('fare')/$rows->count() : 0,
                ];
            })->sortKeys()->values();

        $daily = $records->groupBy(fn($r) => optional($r['booking']->created_at)->format('Y-m-d') ?: 'Unknown')
            ->map(function(Collection $rows, $day) {
                return ['label'=>$day,'bookings'=>$rows->count(),'revenue'=>$rows->sum('fare')];
            })->sortKeys()->values();

        $markupRecords = $records->filter(fn($r) => $r['markup_name'] !== 'No rule recorded' || $r['markup_discount'] > 0);
        $fareRules = $this->breakdown($markupRecords, fn($r) => $r['markup_name'], 'fare')->take(10)->values();
        $totalFareReduction = $markupRecords->sum('markup_discount');

        $leadStats = $this->leadStats($from,$to);
        $funnel = [
            ['label'=>'Enquiries','value'=>$leadStats['total']],
            ['label'=>'Qualified','value'=>$leadStats['qualified']],
            ['label'=>'Quoted','value'=>$leadStats['quoted']],
            ['label'=>'Converted','value'=>$leadStats['converted']],
            ['label'=>'Bookings','value'=>$count],
            ['label'=>'Paid','value'=>$paidCount],
            ['label'=>'Ticketed','value'=>$ticketedCount],
        ];

        $kpis = [
            'bookings'=>$count,
            'gross'=>$gross,
            'average'=>$average,
            'paid'=>$paidCount,
            'ticketed'=>$ticketedCount,
            'refunds'=>$refundCount,
            'payment_issues'=>$paymentIssues,
            'collected'=>$collected,
            'refund_amount'=>$refundAmount,
            'net_collected'=>$netCollected,
            'fare_rule_bookings'=>$markupRecords->count(),
            'fare_reduction'=>$totalFareReduction,
            'booking_to_enquiry'=>$leadStats['total'] ? round(($count/$leadStats['total'])*100,1) : null,
        ];

        $bookingStatuses = Booking::whereNotNull('status')->where('status','!=','')->distinct()->orderBy('status')->pluck('status');
        $sources = Booking::whereNotNull('source')->where('source','!=','')->distinct()->orderBy('source')->pluck('source');
        $paymentStatuses = ['Untracked','Pending','Partially Paid','Paid','Failed','Declined','Refund Pending','Partially Refunded','Refunded','Chargeback'];
        $ticketStatuses = ['Untracked','Pending','On Hold','Ticketed','Issued','Cancelled','Void'];
        $adminOptions = DB::table('admins')->orderBy('name')->get(['id','name']);

        $airlineOptions = $records->pluck('airline')->filter(fn($v)=>$v!=='Unknown')->unique()->sort()->values();

        return compact(
            'records','kpis','byStatus','bySource','byPayment','topRoutes','topAirlines','agents',
            'monthly','daily','fareRules','funnel','leadStats',
            'bookingStatuses','sources','paymentStatuses','ticketStatuses','adminOptions','airlineOptions'
        );
    }

    private function leadStats(Carbon $from, Carbon $to): array
    {
        $total = DB::table('enquiries')
            ->whereBetween('created_at',[$from->copy()->startOfDay(),$to->copy()->endOfDay()])
            ->count();

        $stats = ['total'=>$total,'new'=>$total,'qualified'=>0,'quoted'=>0,'converted'=>0,'lost'=>0];

        if (!Schema::hasTable('lead_crm_meta')) return $stats;

        $rows = DB::table('lead_crm_meta')
            ->join('enquiries','lead_crm_meta.lead_id','=','enquiries.id')
            ->whereBetween('enquiries.created_at',[$from->copy()->startOfDay(),$to->copy()->endOfDay()])
            ->select('lead_crm_meta.stage',DB::raw('COUNT(*) total'))
            ->groupBy('lead_crm_meta.stage')->pluck('total','stage');

        $stats['new'] = (int)($rows['New'] ?? 0);
        $stats['qualified'] = (int)($rows['Qualified'] ?? 0);
        $stats['quoted'] = (int)($rows['Quoted'] ?? 0);
        $stats['converted'] = (int)($rows['Converted'] ?? 0);
        $stats['lost'] = (int)($rows['Lost'] ?? 0);
        return $stats;
    }

    private function breakdown(Collection $records, callable $keyFn, string $sumField): Collection
    {
        return $records->groupBy($keyFn)
            ->map(function(Collection $rows, $label) use ($sumField) {
                $revenue = $rows->sum($sumField);
                return [
                    'label'=>(string)$label,
                    'count'=>$rows->count(),
                    'revenue'=>$revenue,
                    'avg'=>$rows->count() ? $revenue/$rows->count() : 0,
                ];
            })
            ->sortByDesc('count')->values();
    }

    private function dateRange(Request $request): array
    {
        $preset = (string)$request->input('range','30d');
        $today = now()->startOfDay();

        if ($preset === '24h') {
            return [now()->subDay(), now(), $preset];
        }
        if ($preset === '7d') {
            return [$today->copy()->subDays(6), now(), $preset];
        }
        if ($preset === 'custom') {
            $from = $request->filled('date_from') ? Carbon::parse($request->input('date_from'))->startOfDay() : $today->copy()->subDays(29);
            $to = $request->filled('date_to') ? Carbon::parse($request->input('date_to'))->endOfDay() : now();
            if ($from->gt($to)) [$from,$to] = [$to->copy()->startOfDay(),$from->copy()->endOfDay()];
            return [$from,$to,$preset];
        }
        return [$today->copy()->subDays(29), now(), '30d'];
    }

    private function json($value): array
    {
        if (!$value) return [];
        if (is_array($value)) return $value;
        $decoded = json_decode((string)$value,true);
        return json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : [];
    }

    private function firstSegment(array $node): array
    {
        if (isset($node['Departure_Iata']) || isset($node['DepartureIata'])) return $node;
        foreach ($node as $child) {
            if (is_array($child)) {
                $found = $this->firstSegment($child);
                if ($found) return $found;
            }
        }
        return [];
    }

    private function lastSegment(array $node): array
    {
        $segments = [];
        $walk = function($value) use (&$walk,&$segments) {
            if (!is_array($value)) return;
            if (isset($value['Departure_Iata']) || isset($value['DepartureIata']) || isset($value['Arrival_Iata']) || isset($value['ArrivalIata'])) {
                $segments[] = $value;
                return;
            }
            foreach ($value as $child) $walk($child);
        };
        $walk($node);
        return $segments ? end($segments) : [];
    }

    private function money($value): float
    {
        return (float)preg_replace('/[^0-9.\-]/','',(string)$value);
    }

    private function legacyPaymentStatus($bookingStatus): string
    {
        if ($bookingStatus === 'Payment Declined') return 'Declined';
        if ($bookingStatus === 'Chargeback') return 'Chargeback';
        if ($bookingStatus === 'Refunded') return 'Refunded';
        if ($bookingStatus === 'Partial Refund') return 'Partially Refunded';
        return 'Untracked';
    }

    private function legacyTicketStatus($bookingStatus): string
    {
        $s = strtolower((string)$bookingStatus);
        if (str_contains($s,'ticket')) return 'Ticketed';
        if (str_contains($s,'cancel')) return 'Cancelled';
        return 'Untracked';
    }
}
