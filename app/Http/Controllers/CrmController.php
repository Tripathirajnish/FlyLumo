<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Booking;
use App\Models\Markup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Controllers\FlightController;

class CrmController extends Controller
{
    private function bookingStatusGroups(): array
    {
        return [
            'New / Sales' => [
                'Fresh',
                'New Booking',
                'Incomplete',
                'Follow Up',
                'No Response',
                'Booked',
                'On Hold',
            ],
            'Payment' => [
                'Payment Pending',
                'Payment Received',
                'Payment Declined',
                'Chargeback',
            ],
            'GDS / Processing' => [
                'GDS Pending',
                'GDS Declined',
                'Failed',
                'Seat Unavailable',
                'Fare Recheck Required',
                'Fare Changed',
            ],
            'Ticketing' => [
                'Sent to Ticketing',
                'Sent for Issuance',
                'Issuance in Progress',
                'Issued',
                'Ticketed',
                'E-Ticket',
            ],
            'Changes' => [
                'Date Change',
                'Exchange Requested',
                'Exchanged',
                'Schedule Change',
            ],
            'Cancellation / Refund' => [
                'Cancellation Requested',
                'Cancelled',
                'Refund Requested',
                'Refund Pending',
                'Partial Refund',
                'Refunded',
            ],
            'Risk / Internal' => [
                'Duplicate Booking',
                'Test Booking',
                'Fraud Booking',
            ],
        ];
    }

    private function bookingStatusOptions(): array
    {
        return collect($this->bookingStatusGroups())->flatten()->values()->all();
    }

    private function bookingStatusMeta(): array
    {
        $tones = [
            'New / Sales' => 'blue',
            'Payment' => 'amber',
            'GDS / Processing' => 'purple',
            'Ticketing' => 'green',
            'Changes' => 'cyan',
            'Cancellation / Refund' => 'red',
            'Risk / Internal' => 'slate',
        ];

        $meta = [];
        foreach ($this->bookingStatusGroups() as $group => $statuses) {
            foreach ($statuses as $status) {
                $meta[$status] = [
                    'group' => $group,
                    'tone' => $tones[$group] ?? 'slate',
                ];
            }
        }
        return $meta;
    }

    private function guard(Request $request)
    {
        $adminId = (int) $request->session()->get('crm_admin_id');

        if ($adminId <= 0) {
            return redirect()->route('crm.login');
        }

        $query = DB::table('admins')->where('id', $adminId)->where('status', '1');
        if (Schema::hasColumn('admins', 'crm_deleted_at')) {
            $query->whereNull('crm_deleted_at');
        }

        if (!$query->exists()) {
            $request->session()->forget('crm_admin_id');
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('crm.login');
        }

        return null;
    }

    private function crmRoleSlug(Request $request): string
    {
        if (!Schema::hasTable('crm_access_roles')) {
            return '';
        }

        return (string) DB::table('admins as a')
            ->leftJoin('crm_access_roles as r', 'a.crm_access_role_id', '=', 'r.id')
            ->where('a.id', (int) $request->session()->get('crm_admin_id'))
            ->value('r.slug');
    }

    // FH card visibility (31 Oct 2026): only the Super Admin role may see full card details.
    // Fails closed: no role table, no role, or any other role => false.
    private function isCardAdmin(Request $request): bool
    {
        return $this->crmRoleSlug($request) === 'super-admin';
    }

    private function canWorkBooking(Request $request, Booking $booking): bool
    {
        $roleSlug = $this->crmRoleSlug($request);

        if (in_array($roleSlug, ['super-admin', 'manager'], true)) {
            return true;
        }

        $adminId = (int) $request->session()->get('crm_admin_id');

        return $adminId > 0
            && !empty($booking->assign_to)
            && (int) $booking->assign_to === $adminId;
    }

    private function requireBookingWorkAccess(Request $request, Booking $booking): void
    {
        abort_unless(
            $this->canWorkBooking($request, $booking),
            403,
            'This booking is read-only for you until it is assigned to your account.'
        );
    }

    public function login()
    {
        return view('crm.login');
    }

    public function authenticate(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $admin = Admin::where('username', $data['username'])->where('status', '1')->first();
        if (!$admin || !Hash::check($data['password'], $admin->password)) {
            return back()->withErrors(['username' => 'Invalid CRM credentials.'])->withInput();
        }

        $request->session()->regenerate();
        $request->session()->put('crm_admin_id', $admin->id);
        return redirect()->route('crm.dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('crm_admin_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('crm.login');
    }

    public function dashboard(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $currentAdminId = (int) $request->session()->get('crm_admin_id');
        $currentAdmin = DB::table('admins')->where('id', $currentAdminId)->first();

        $superRoleId = Schema::hasTable('crm_access_roles')
            ? (int) DB::table('crm_access_roles')->where('slug', 'super-admin')->value('id')
            : 0;

        $isSuperAdmin = $currentAdmin
            && $superRoleId > 0
            && (int) ($currentAdmin->crm_access_role_id ?? 0) === $superRoleId;

        $period = (string) $request->query('period', '30d');
        $allowedPeriods = ['1d','7d','30d','all'];
        if (!in_array($period, $allowedPeriods, true)) $period = '30d';

        $periodStart = match ($period) {
            '1d' => now()->subDay(),
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            default => null,
        };

        $bookingScope = Booking::query();
        $enquiryScope = DB::table('enquiries');

        // Keep dashboard enquiry counts aligned with Lead Management:
        // archived leads remain stored for history/restore but are not active enquiries.
        if (Schema::hasTable('crm_archives')) {
            $enquiryScope->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('crm_archives')
                    ->where('crm_archives.module', 'lead')
                    ->whereNull('crm_archives.restored_at')
                    ->whereRaw('CAST(crm_archives.record_key AS UNSIGNED) = enquiries.id');
            });
        }

        // Normal agents see only bookings assigned to them.
        // Super Admin keeps the full-company dashboard.
        if (!$isSuperAdmin) {
            $bookingScope->where('assign_to', $currentAdminId);
        }

        if ($periodStart) {
            $bookingScope->where('created_at', '>=', $periodStart);
            $enquiryScope->where('created_at', '>=', $periodStart);
        }

        $periodBookings = (clone $bookingScope)
            ->select(
                'id','prefix','o_Name','o_Email','o_Phone','o_FullPrice','o_SectorGo',
                'status','source','assign_to','created_at'
            )
            ->latest()
            ->get();

        $periodEnquiries = (clone $enquiryScope)->count();

        $moneyValue = function ($value): float {
            $clean = preg_replace('/[^0-9.\-]/', '', (string) $value);
            return is_numeric($clean) ? (float) $clean : 0.0;
        };

        $grossRevenue = $periodBookings->sum(fn($b) => $moneyValue($b->o_FullPrice));
        $averageTicket = $periodBookings->count() ? ($grossRevenue / $periodBookings->count()) : 0;
        $freshCount = $periodBookings->whereIn('status', ['Fresh','New Booking'])->count();
        $conversionRate = $periodEnquiries > 0
            ? round(($periodBookings->count() / $periodEnquiries) * 100, 1)
            : ($periodBookings->count() > 0 ? 100 : 0);

        $stats = [
            'bookings' => $periodBookings->count(),
            'fresh' => $freshCount,
            'website' => $periodBookings->where('source', 'Website')->count(),
            'fareleader' => $periodBookings->where('source', 'FareLeader')->count(),
            'enquiries' => $periodEnquiries,
            'admins' => DB::table('admins')->where('status', '1')->count(),
        ];

        $recentBookings = $periodBookings->take(8)->values();
        $recentEnquiriesQuery = DB::table('enquiries')->latest()->limit(6);
        if ($periodStart) $recentEnquiriesQuery->where('created_at', '>=', $periodStart);
        $recentEnquiries = $recentEnquiriesQuery->get();

        $statusBreakdown = $periodBookings
            ->groupBy(fn($b) => $b->status ?: 'Unknown')
            ->map->count()
            ->sortDesc();

        $sourceBreakdown = $periodBookings
            ->groupBy(fn($b) => $b->source ?: 'Unknown')
            ->map->count()
            ->sortDesc();

        // Revenue trend: daily for short windows, monthly for all-time.
        if ($period === 'all') {
            $monthlyRevenue = $periodBookings
                ->groupBy(fn($b) => optional($b->created_at)->format('Y-m') ?: 'Unknown')
                ->map(fn($rows) => round($rows->sum(fn($b) => $moneyValue($b->o_FullPrice)), 2))
                ->sortKeys()
                ->take(-12);
            $trendLabel = 'Latest 12 months';
        } else {
            $days = $period === '1d' ? 1 : ($period === '7d' ? 7 : 30);
            $daily = collect();
            for ($i = $days - 1; $i >= 0; $i--) {
                $key = now()->subDays($i)->format('Y-m-d');
                $daily[$key] = 0;
            }

            foreach ($periodBookings as $b) {
                $key = optional($b->created_at)->format('Y-m-d');
                if ($key && $daily->has($key)) {
                    $daily[$key] += $moneyValue($b->o_FullPrice);
                }
            }

            $monthlyRevenue = $daily->map(fn($v) => round($v, 2));
            $trendLabel = $period === '1d' ? 'Last 24 hours' : ($period === '7d' ? 'Last 7 days' : 'Last 30 days');
        }

        $actionStatuses = [
            'New Booking','Fresh','Incomplete','Follow Up','No Response',
            'Payment Pending','Payment Declined',
            'GDS Pending','GDS Declined','Failed','Fare Recheck Required','Fare Changed',
            'Sent to Ticketing','Sent for Issuance','Issuance in Progress',
            'Cancellation Requested','Refund Requested','Refund Pending',
            'Chargeback','Fraud Booking'
        ];

        $actionCounts = $periodBookings
            ->whereIn('status', $actionStatuses)
            ->groupBy(fn($b) => $b->status ?: 'Unknown')
            ->map->count()
            ->sortDesc();

        $ticketingStatuses = [
            'Booked','Sent to Ticketing','Sent for Issuance',
            'Issuance in Progress','Issued','Ticketed','E-Ticket'
        ];
        $ticketingFunnel = collect($ticketingStatuses)->mapWithKeys(function($status) use ($periodBookings) {
            return [$status => $periodBookings->where('status',$status)->count()];
        });

        $riskStatuses = [
            'Payment Declined','GDS Declined','Failed','Chargeback','Fraud Booking',
            'Cancellation Requested','Cancelled','Refund Requested','Refund Pending',
            'Partial Refund','Refunded'
        ];
        $riskCounts = collect($riskStatuses)->mapWithKeys(function($status) use ($periodBookings) {
            return [$status => $periodBookings->where('status',$status)->count()];
        })->filter(fn($count) => $count > 0);

        $extractFirstSegment = function ($value): ?array {
            if (!$value) return null;
            $decoded = json_decode((string) $value, true);
            if (!is_array($decoded)) return null;

            $walk = function ($node) use (&$walk) {
                if (!is_array($node)) return null;

                if (
                    isset($node['Departure_Iata']) || isset($node['Arrival_Iata']) ||
                    isset($node['AirlineName']) || isset($node['FlightNum'])
                ) {
                    return $node;
                }

                foreach ($node as $child) {
                    if (is_array($child)) {
                        $found = $walk($child);
                        if ($found) return $found;
                    }
                }

                return null;
            };

            return $walk($decoded);
        };

        $routeCounts = [];
        $airlineCounts = [];

        foreach ($periodBookings as $booking) {
            $segment = $extractFirstSegment($booking->o_SectorGo);
            if (!$segment) continue;

            $from = trim((string)($segment['Departure_Iata'] ?? $segment['DepartureIata'] ?? ''));
            $to = trim((string)($segment['Arrival_Iata'] ?? $segment['ArrivalIata'] ?? ''));
            $airline = trim((string)($segment['AirlineName'] ?? $segment['OperatingAirlineName'] ?? $segment['Airline'] ?? ''));

            if ($from !== '' && $to !== '') {
                $route = strtoupper($from).' → '.strtoupper($to);
                $routeCounts[$route] = ($routeCounts[$route] ?? 0) + 1;
            }

            if ($airline !== '') {
                $airlineCounts[$airline] = ($airlineCounts[$airline] ?? 0) + 1;
            }
        }

        arsort($routeCounts);
        arsort($airlineCounts);

        $topRoutes = collect($routeCounts)->take(6);
        $topAirlines = collect($airlineCounts)->take(6);

        $severityMap = [
            'Fraud Booking' => 'critical',
            'Chargeback' => 'critical',
            'Payment Declined' => 'critical',
            'GDS Declined' => 'critical',
            'Failed' => 'critical',
            'Refund Pending' => 'high',
            'Refund Requested' => 'high',
            'Cancellation Requested' => 'high',
            'Fare Recheck Required' => 'high',
            'Fare Changed' => 'high',
            'Sent to Ticketing' => 'medium',
            'Sent for Issuance' => 'medium',
            'Issuance in Progress' => 'medium',
            'Follow Up' => 'medium',
            'No Response' => 'medium',
            'Incomplete' => 'medium',
            'New Booking' => 'normal',
            'Fresh' => 'normal',
        ];

        $alertWeight = ['critical'=>1,'high'=>2,'medium'=>3,'normal'=>4];

        $activeAlerts = $periodBookings
            ->whereIn('status', $actionStatuses)
            ->map(function($booking) use ($severityMap) {
                $booking->dashboard_severity = $severityMap[$booking->status] ?? 'normal';
                return $booking;
            })
            ->sortBy(function($booking) use ($alertWeight) {
                return [
                    $alertWeight[$booking->dashboard_severity] ?? 9,
                    optional($booking->created_at)->timestamp ?? PHP_INT_MAX,
                ];
            })
            ->take(8)
            ->values();

        $criticalCount = $periodBookings->whereIn('status',[
            'Fraud Booking','Chargeback','Payment Declined','GDS Declined','Failed'
        ])->count();

        $gdsIssueCount = $periodBookings->whereIn('status',[
            'GDS Declined','Failed','Fare Recheck Required','Fare Changed'
        ])->count();

        $fraudRiskCount = $periodBookings->whereIn('status',[
            'Fraud Booking','Chargeback'
        ])->count();

        $refundPendingCount = $periodBookings->whereIn('status',[
            'Refund Requested','Refund Pending','Partial Refund'
        ])->count();

        $followUpCount = $periodBookings->whereIn('status',['Follow Up','No Response','Incomplete'])->count();
        $paymentIssueCount = $periodBookings->whereIn('status',['Payment Pending','Payment Declined','Chargeback'])->count();
        $ticketingActionCount = $periodBookings->whereIn('status',['Booked','Sent to Ticketing','Sent for Issuance','Issuance in Progress'])->count();
        $refundActionCount = $periodBookings->whereIn('status',['Cancellation Requested','Refund Requested','Refund Pending','Partial Refund'])->count();

        $recentBookings = $recentBookings->map(function($booking) use ($extractFirstSegment) {
            $segment = $extractFirstSegment($booking->o_SectorGo);

            $from = trim((string)($segment['Departure_Iata'] ?? $segment['DepartureIata'] ?? ''));
            $to = trim((string)($segment['Arrival_Iata'] ?? $segment['ArrivalIata'] ?? ''));
            $airline = trim((string)($segment['AirlineName'] ?? $segment['OperatingAirlineName'] ?? $segment['Airline'] ?? ''));

            $booking->dashboard_route = ($from !== '' && $to !== '')
                ? strtoupper($from).' → '.strtoupper($to)
                : 'Route unavailable';

            $booking->dashboard_airline = $airline !== '' ? $airline : null;
            $booking->dashboard_age = optional($booking->created_at)->diffForHumans() ?: '—';

            return $booking;
        });

        /*
         * Agent Performance Dashboard
         * Uses booking assignment (bookings.assign_to) for attribution.
         * Profit values come only from the separate booking_financials table;
         * no fare/API value is guessed or overwritten.
         */
        $financeByBooking = collect();
        if (Schema::hasTable('booking_financials') && $periodBookings->isNotEmpty()) {
            $financeByBooking = DB::table('booking_financials')
                ->whereIn('booking_id', $periodBookings->pluck('id'))
                ->get()
                ->keyBy('booking_id');
        }

        $agentPerformance = function ($bookings) use ($moneyValue, $financeByBooking) {
            $sales = 0.0;
            $grossProfit = 0.0;
            $netProfit = 0.0;

            foreach ($bookings as $b) {
                $sales += $moneyValue($b->o_FullPrice);
                $fin = $financeByBooking->get($b->id);
                if ($fin) {
                    $grossProfit += (float) $fin->gross_profit;
                    $netProfit += (float) $fin->net_profit;
                }
            }

            return [
                'bookings' => $bookings->count(),
                'sales' => round($sales, 2),
                'gross_profit' => round($grossProfit, 2),
                'net_profit' => round($netProfit, 2),
                'avg_booking' => $bookings->count() ? round($sales / $bookings->count(), 2) : 0,
            ];
        };

        $myBookings = $periodBookings->where('assign_to', $currentAdminId)->values();
        $myPerformance = $agentPerformance($myBookings);

        $todayBookings = Booking::query()
            ->where('assign_to', $currentAdminId)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        $monthBookingsQuery = Booking::query()
            ->where('assign_to', $currentAdminId)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month);

        $monthBookingRows = $monthBookingsQuery
            ->select('id','o_FullPrice')
            ->get();

        $monthSales = $monthBookingRows->sum(fn($b) => $moneyValue($b->o_FullPrice));

        $monthGrossProfit = 0.0;
        $monthNetProfit = 0.0;
        if (Schema::hasTable('booking_financials') && $monthBookingRows->isNotEmpty()) {
            $monthFinance = DB::table('booking_financials')
                ->whereIn('booking_id', $monthBookingRows->pluck('id'))
                ->get();
            $monthGrossProfit = (float) $monthFinance->sum('gross_profit');
            $monthNetProfit = (float) $monthFinance->sum('net_profit');
        }

        $myPerformance['today_bookings'] = $todayBookings;
        $myPerformance['month_bookings'] = $monthBookingRows->count();
        $myPerformance['month_sales'] = round($monthSales, 2);
        $myPerformance['month_gross_profit'] = round($monthGrossProfit, 2);
        $myPerformance['month_net_profit'] = round($monthNetProfit, 2);

        $allAgentPerformance = collect();
        if ($isSuperAdmin) {
            $agents = DB::table('admins')
                ->where('status', '1')
                ->whereNull('crm_deleted_at')
                ->orderBy('name')
                ->get(['id','name','username']);

            $allAgentPerformance = $agents->map(function ($agent) use ($periodBookings, $agentPerformance) {
                $rows = $periodBookings->where('assign_to', (int) $agent->id)->values();
                return (object) array_merge([
                    'id' => (int) $agent->id,
                    'name' => $agent->name ?: $agent->username,
                    'username' => $agent->username,
                ], $agentPerformance($rows));
            });
        }

        $travelDeskStats = collect(['flight','hotel','car','package','custom'])->mapWithKeys(function ($type) {
            $count = Schema::hasTable('crm_travel_requests')
                ? DB::table('crm_travel_requests')->where('type',$type)->count()
                : 0;
            return [$type => $count];
        });

        $recentTravelRequests = Schema::hasTable('crm_travel_requests')
            ? DB::table('crm_travel_requests')->latest()->limit(5)->get()
            : collect();

        // Premium dashboard intelligence: all values are derived from current CRM data.
        $uniqueCustomers = $periodBookings->map(function($b){
            $email = strtolower(trim((string)($b->o_Email ?? '')));
            $phone = preg_replace('/\D+/', '', (string)($b->o_Phone ?? ''));
            return $email !== '' ? 'e:'.$email : ($phone !== '' ? 'p:'.$phone : 'b:'.$b->id);
        })->unique()->count();

        $bookingTrend = $monthlyRevenue->map(function($revenue,$key) use ($periodBookings,$period){
            $count = $periodBookings->filter(function($b) use ($key,$period){
                $date = optional($b->created_at);
                return $period === 'all' ? $date->format('Y-m') === $key : $date->format('Y-m-d') === $key;
            })->count();
            return ['label'=>$key,'revenue'=>(float)$revenue,'bookings'=>$count];
        })->values();

        // Lead Funnel must use the same visible/non-archived enquiry scope as Lead Management.
        // This prevents archived lead_crm_meta rows from appearing as Active/Hot when Total Leads is 0.
        $leadFunnelQuery = DB::table('enquiries');

        if ($periodStart) {
            $leadFunnelQuery->where('enquiries.created_at', '>=', $periodStart);
        }

        if (Schema::hasTable('crm_archives')) {
            $leadFunnelQuery->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('crm_archives')
                    ->where('crm_archives.module', 'lead')
                    ->whereNull('crm_archives.restored_at')
                    ->whereRaw('CAST(crm_archives.record_key AS UNSIGNED) = enquiries.id');
            });
        }

        $leadFunnel = [
            'total' => (clone $leadFunnelQuery)->count(),
            'active' => 0,
            'response' => 0,
            'hot' => 0,
            'converted' => 0,
        ];

        if (Schema::hasTable('lead_crm_meta')) {
            $leadMeta = (clone $leadFunnelQuery)
                ->leftJoin('lead_crm_meta', 'lead_crm_meta.lead_id', '=', 'enquiries.id')
                ->get(['lead_crm_meta.stage', 'lead_crm_meta.priority']);

            $leadFunnel['active'] = $leadMeta
                ->filter(fn($r) => !in_array((string)($r->stage ?? ''), ['Converted','Closed','Lost'], true))
                ->count();
            $leadFunnel['response'] = $leadMeta
                ->whereIn('stage', ['Contacted','Follow Up','Qualified','In Progress'])
                ->count();
            $leadFunnel['hot'] = $leadMeta
                ->filter(fn($r) => in_array(strtolower((string)($r->priority ?? '')), ['urgent','high','hot'], true))
                ->count();
            $leadFunnel['converted'] = $leadMeta->where('stage', 'Converted')->count();
        } else {
            $leadFunnel['active'] = $leadFunnel['total'];
        }

        $productMix = [
            'Flights'=>$periodBookings->count(),
            'Hotels'=>(int)($travelDeskStats['hotel'] ?? 0),
            'Car Rentals'=>(int)($travelDeskStats['car'] ?? 0),
            'Holiday Packages'=>(int)($travelDeskStats['package'] ?? 0),
            'Custom Trips'=>(int)($travelDeskStats['custom'] ?? 0),
        ];

        $paymentMethods = collect();

if (
    Schema::hasTable('payment_crm_meta') &&
    Schema::hasColumn('payment_crm_meta', 'processor') &&
    $periodBookings->isNotEmpty()
) {
    $paymentMethods = DB::table('payment_crm_meta')
        ->whereIn('booking_id', $periodBookings->pluck('id'))
        ->selectRaw("COALESCE(NULLIF(processor,''),'Untracked') as method, COUNT(*) as total")
        ->groupBy('method')
        ->orderByDesc('total')
        ->limit(5)
        ->pluck('total', 'method');
}

        $dashboard = compact(
            'grossRevenue','averageTicket','freshCount','conversionRate',
            'statusBreakdown','sourceBreakdown','monthlyRevenue','trendLabel',
            'actionCounts','ticketingFunnel','riskCounts','topRoutes','topAirlines',
            'followUpCount','paymentIssueCount','ticketingActionCount','refundActionCount',
            'criticalCount','gdsIssueCount','fraudRiskCount','refundPendingCount',
            'uniqueCustomers','bookingTrend','leadFunnel','productMix','paymentMethods'
        );

        $statusMeta = $this->bookingStatusMeta();

        return view('crm.dashboard', compact(
            'stats','recentBookings','recentEnquiries','dashboard','statusMeta',
            'period','activeAlerts','currentAdmin','isSuperAdmin','myPerformance','allAgentPerformance','travelDeskStats','recentTravelRequests'
        ));
    }

    public function bookings(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $query = Booking::query();

        if (Schema::hasTable('crm_archives')) {
            $query->whereNotExists(function($sub) {
                $sub->select(DB::raw(1))
                    ->from('crm_archives')
                    ->where('crm_archives.module','booking')
                    ->whereNull('crm_archives.restored_at')
                    ->whereRaw('CAST(crm_archives.record_key AS UNSIGNED) = bookings.id');
            });
        }

        $hasOps = Schema::hasTable('booking_crm_meta');

        if ($request->filled('q')) {
            $q = trim((string)$request->input('q'));

            $query->where(function ($builder) use ($q, $hasOps) {
                $builder->where('o_Name', 'like', "%{$q}%")
                    ->orWhere('o_Email', 'like', "%{$q}%")
                    ->orWhere('o_Phone', 'like', "%{$q}%")
                    ->orWhere('o_SectorGo', 'like', "%{$q}%")
                    ->orWhere('o_SectorBack', 'like', "%{$q}%");

                if (ctype_digit($q)) {
                    $builder->orWhere('id', (int)$q);
                }

                if ($hasOps) {
                    $builder->orWhereExists(function($sub) use ($q) {
                        $sub->select(DB::raw(1))
                            ->from('booking_crm_meta')
                            ->whereColumn('booking_crm_meta.booking_id', 'bookings.id')
                            ->where(function($w) use ($q) {
                                $w->where('pnr','like',"%{$q}%")
                                  ->orWhere('ticket_number','like',"%{$q}%");
                            });
                    });
                }
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('source')) {
            $query->where('source', $request->input('source'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        if ($hasOps && $request->filled('payment_status')) {
            $paymentStatus = $request->input('payment_status');
            $query->whereExists(function($sub) use ($paymentStatus) {
                $sub->select(DB::raw(1))
                    ->from('booking_crm_meta')
                    ->whereColumn('booking_crm_meta.booking_id', 'bookings.id')
                    ->where('payment_status', $paymentStatus);
            });
        }

        if ($hasOps && $request->filled('ticket_status')) {
            $ticketStatus = $request->input('ticket_status');
            $query->whereExists(function($sub) use ($ticketStatus) {
                $sub->select(DB::raw(1))
                    ->from('booking_crm_meta')
                    ->whereColumn('booking_crm_meta.booking_id', 'bookings.id')
                    ->where('ticket_status', $ticketStatus);
            });
        }

        $perPage = (int)$request->input('per_page', 15);
        if (!in_array($perPage, [15,25,50,100], true)) $perPage = 15;

        $bookings = $query->latest()->paginate($perPage)->withQueryString();

        $ids = collect($bookings->items())->pluck('id')->all();
        $opsMap = $hasOps
            ? DB::table('booking_crm_meta')->whereIn('booking_id', $ids)->get()->keyBy('booking_id')
            : collect();

        $decode = function ($value) {
            if (!$value) return null;
            $decoded = json_decode((string)$value, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        };

        $findFirst = function($node, array $keys) use (&$findFirst) {
            if (!is_array($node)) return null;

            foreach ($keys as $key) {
                if (isset($node[$key]) && $node[$key] !== '') return $node[$key];
            }

            foreach ($node as $child) {
                if (is_array($child)) {
                    $found = $findFirst($child, $keys);
                    if ($found !== null && $found !== '') return $found;
                }
            }

            return null;
        };

        $findSegment = function($node) use (&$findSegment) {
            if (!is_array($node)) return null;

            if (
                isset($node['Departure_Iata']) || isset($node['DepartureIata']) ||
                isset($node['Arrival_Iata']) || isset($node['ArrivalIata']) ||
                isset($node['AirlineName']) || isset($node['FlightNum'])
            ) return $node;

            foreach ($node as $child) {
                if (is_array($child)) {
                    $found = $findSegment($child);
                    if ($found) return $found;
                }
            }

            return null;
        };

        $bookings->getCollection()->transform(function($booking) use ($opsMap, $decode, $findFirst, $findSegment) {
            $ops = $opsMap->get($booking->id);
            $sectorGo = $decode($booking->o_SectorGo);
            $sectorBack = $decode($booking->o_SectorBack);
            $passengers = $decode($booking->o_Passenger);
            $other = $decode($booking->o_Other);

            $segment = $findSegment($sectorGo ?: []);
            $returnSegment = $findSegment($sectorBack ?: []);

            $from = trim((string)($segment['Departure_Iata'] ?? $segment['DepartureIata'] ?? ''));
            $to = trim((string)($segment['Arrival_Iata'] ?? $segment['ArrivalIata'] ?? ''));
            $airline = trim((string)($segment['AirlineName'] ?? $segment['OperatingAirlineName'] ?? $segment['Airline'] ?? ''));
            $departure = $segment['Departure_Date'] ?? $segment['DepartureDate'] ?? null;
            $returnDate = $returnSegment['Departure_Date'] ?? $returnSegment['DepartureDate'] ?? null;

            $pnr = $ops->pnr ?? null;
            if (!$pnr) {
                $pnr = $findFirst($sectorGo ?: [], ['PNR','Pnr','pnr','BookingReference','BookingRef','RecordLocator']);
            }
            if (!$pnr) {
                $pnr = $findFirst($sectorBack ?: [], ['PNR','Pnr','pnr','BookingReference','BookingRef','RecordLocator']);
            }
            if (!$pnr) {
                $pnr = $findFirst($other ?: [], ['PNR','Pnr','pnr','BookingReference','BookingRef','RecordLocator']);
            }

            $paxCount = 0;
            if (is_array($passengers)) {
                if (array_keys($passengers) === range(0, max(0,count($passengers)-1))) {
                    $paxCount = count($passengers);
                } elseif ($passengers) {
                    $paxCount = 1;
                }
            }

            if ($paxCount < 1) $paxCount = 1;

            $ticketStatus = $ops->ticket_status ?? null;
            if (!$ticketStatus) {
                $ticketStatus = in_array($booking->status, ['Issued','Ticketed','E-Ticket'], true)
                    ? 'Ticketed'
                    : 'Not Ticketed';
            }

            $paymentStatus = $ops->payment_status ?? null;
            if (!$paymentStatus) {
                if ($booking->status === 'Payment Declined') $paymentStatus = 'Declined';
                elseif (in_array($booking->status,['Refunded','Partial Refund'],true)) $paymentStatus = 'Refunded';
                else $paymentStatus = 'Untracked';
            }

            $booking->crm_pnr = $pnr ?: '—';
            $booking->crm_route = ($from && $to) ? strtoupper($from).' → '.strtoupper($to) : '—';
            $booking->crm_from = $from ?: '—';
            $booking->crm_to = $to ?: '—';
            $booking->crm_airline = $airline ?: '—';
            $booking->crm_departure = $departure;
            $booking->crm_return = $returnDate;
            $booking->crm_pax_count = $paxCount;
            $booking->crm_payment_status = $paymentStatus;
            $booking->crm_ticket_status = $ticketStatus;
            $booking->crm_markup_amount = $ops->markup_amount ?? null;

            return $booking;
        });

        $statusGroups = $this->bookingStatusGroups();
        $statusMeta = $this->bookingStatusMeta();

        $paymentStatuses = ['Untracked','Pending','Paid','Partially Paid','Declined','Refund Pending','Refunded','Chargeback'];
        $ticketStatuses = ['Not Ticketed','Ticketing','Issued','Ticketed','E-Ticket','Voided','Exchanged'];

        return view('crm.bookings', compact(
            'bookings','statusGroups','statusMeta','paymentStatuses','ticketStatuses'
        ));
    }

    public function incompleteBookings(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $query = \App\Models\Temp::query();

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($builder) use ($q) {
                $builder->where('emailid', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");

                if (ctype_digit($q)) {
                    $builder->orWhere('id', (int) $q);
                }
            });
        }

        $perPage = (int) $request->input('per_page', 25);
        if (!in_array($perPage, [15,25,50,100], true)) $perPage = 25;

        $incompleteBookings = $query->latest()->paginate($perPage)->withQueryString();

        return view('crm.incomplete_bookings', compact('incompleteBookings'));
    }

    public function incompleteBookingDetail(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $incompleteBooking = \App\Models\Temp::findOrFail($id);

        $activities = Schema::hasTable('incomplete_booking_activity')
            ? DB::table('incomplete_booking_activity')
                ->leftJoin('admins', 'incomplete_booking_activity.admin_id', '=', 'admins.id')
                ->where('incomplete_booking_activity.incomplete_booking_id', $id)
                ->select('incomplete_booking_activity.*', 'admins.name as admin_name')
                ->orderByDesc('incomplete_booking_activity.id')
                ->get()
            : collect();

        return view('crm.incomplete_booking_detail', compact('incompleteBooking', 'activities'));
    }


    public function sendIncompleteBookingEmail(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $incompleteBooking = \App\Models\Temp::findOrFail($id);

        if (empty($incompleteBooking->emailid) || !filter_var($incompleteBooking->emailid, FILTER_VALIDATE_EMAIL)) {
            return back()->with('crm_error', 'This incomplete booking does not have a valid passenger email address.');
        }

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:10000'],
        ]);

        $mailSetting = getAllSetting();
        if (($mailSetting['is_mail'] ?? 'OFF') !== 'ON') {
            return back()->with('crm_error', 'Mail sending is currently disabled in website settings.');
        }

        $subject = trim($data['subject']);
        $message = trim($data['message']);

        // Render CRM-entered plain text inside the shared FaresHunt branded email.
        $body = view('emails.crm-branded-message', [
            'subject' => $subject,
            'message' => $message,
            'heading' => 'Your Flight Booking is Not Yet Completed',
            'subheading' => 'Our travel support team is here to help you complete your reservation.',
            'supportEmail' => $mailSetting['content_emailid'] ?? config('mail.from.address', ''),
            'supportPhone' => $mailSetting['header_contact'] ?? '',
            'supportWhatsApp' => $mailSetting['content_whatsapp'] ?? '',
        ])->render();

        try {
            $sent = app(FlightController::class)->sendSmptEmail(
                $incompleteBooking->emailid,
                $subject,
                $body
            );

            if (!$sent) {
                throw new \RuntimeException('SMTP sender returned false.');
            }

            if (Schema::hasTable('incomplete_booking_activity')) {
                DB::table('incomplete_booking_activity')->insert([
                    'incomplete_booking_id' => $id,
                    'admin_id' => (int)$request->session()->get('crm_admin_id') ?: null,
                    'activity_type' => 'email',
                    'title' => 'Email sent: '.$subject,
                    'details' => 'Sent to '.$incompleteBooking->emailid,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return back()->with(
                'crm_success',
                'Email sent successfully to '.$incompleteBooking->emailid.'.'
            );
        } catch (\Throwable $e) {
            \Log::error('CRM incomplete booking email send failed', [
                'incomplete_booking_id' => $id,
                'recipient' => $incompleteBooking->emailid,
                'error' => $e->getMessage(),
            ]);

            return back()->with(
                'crm_error',
                'Email could not be sent. Check SMTP/mail settings and try again.'
            );
        }
    }

    public function incompleteBookingDelete(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        abort_unless(
            $this->crmRoleSlug($request) === 'super-admin',
            403,
            'Only a Super Admin can permanently delete incomplete bookings.'
        );

        $request->validate([
            'confirmation' => ['required', 'string', \Illuminate\Validation\Rule::in(['DELETE'])],
        ]);

        $incompleteBooking = \App\Models\Temp::findOrFail($id);
        $incompleteBooking->delete();

        return redirect()
            ->route('crm.incomplete_bookings')
            ->with('crm_success', 'Incomplete booking permanently deleted.');
    }

    public function bookingDetail(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $booking = Booking::findOrFail($id);
        $related = Booking::where('o_Email', $booking->o_Email)
            ->where('id', '!=', $booking->id)
            ->latest()->take(5)->get();

        $decode = function ($value) {
            if (!$value) return null;
            $decoded = json_decode($value, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        };

        $details = [
            'passengers' => $decode($booking->o_Passenger),
            'sector_go' => $decode($booking->o_SectorGo),
            'sector_back' => $decode($booking->o_SectorBack),
            'price' => $decode($booking->o_Price),
            'billing' => $decode($booking->o_Billing),
            'other' => $decode($booking->o_Other),
        ];

        // Never expose stored CVV/full PAN through the CRM UI. Only derive safe payment metadata.
        $payment = [
    'holder' => null,
    'last4'  => null,
    'brand'  => null,
    'expire_month' => null,
    'expire_year'  => null,
    'cvv' => null
];
        $credit = $decode($booking->o_Credit);
        if (is_array($credit)) {
            $number = preg_replace('/\D+/', '', (string)($credit['creditcard_no'] ?? $credit['card_number'] ?? ''));
            $payment['holder'] = $credit['cardholder_name'] ?? $credit['card_holder_name'] ?? null;
            $payment['last4'] = $number ? $number  : null;
            $payment['expire_month'] = $credit['expire_month'] ?? null;
$payment['expire_year']  = $credit['expire_year'] ?? null;
$payment['cvv']  = $credit['cvv'] ?? null;
            if ($number) {
                if (str_starts_with($number, '4')) $payment['brand'] = 'Visa';
                elseif (preg_match('/^(5[1-5]|2[2-7])/', $number)) $payment['brand'] = 'Mastercard';
                elseif (preg_match('/^3[47]/', $number)) $payment['brand'] = 'American Express';
                elseif (preg_match('/^6/', $number)) $payment['brand'] = 'Discover / Other';
            }
        }

        $remarks = DB::table('booking_remarks')->where('booking_id',$booking->id)->orderByDesc('id')->get();
        $assignments = DB::table('booking_assigns')->where('booking_id',$booking->id)->orderByDesc('id')->get();
        $docs = DB::table('booking_docs')->where('booking_id',$booking->id)->orderByDesc('id')->get();
        $admins = DB::table('admins')->where('status','1')->orderBy('name')->get();

        $statusHistory = collect();
        if (Schema::hasTable('booking_status_history')) {
            $statusHistory = DB::table('booking_status_history')
                ->leftJoin('admins','booking_status_history.admin_id','=','admins.id')
                ->where('booking_status_history.booking_id', $booking->id)
                ->select('booking_status_history.*','admins.name as admin_name')
                ->orderByDesc('booking_status_history.id')
                ->get();
        }

        $statusGroups = $this->bookingStatusGroups();
        $statusMeta = $this->bookingStatusMeta();

        $crmMeta = null;
        if (Schema::hasTable('booking_crm_meta')) {
            $crmMeta = DB::table('booking_crm_meta')->where('booking_id',$booking->id)->first();
        }

        if (!$crmMeta) {
            $crmMeta = (object)[
                'pnr' => null,
                'ticket_number' => null,
                'payment_status' => null,
                'ticket_status' => null,
                'markup_amount' => null,
                'via_source' => null,
                'next_action' => null,
                'next_action_at' => null,
                'operations_note' => null,
            ];
        }

        // Payment CRM is the source of truth for payment operations; mirror it into Booking Detail.
        if (Schema::hasTable('payment_crm_meta')) {
            $paymentCrmMeta = DB::table('payment_crm_meta')->where('booking_id',$booking->id)->first();
            if ($paymentCrmMeta && !empty($paymentCrmMeta->payment_status)) {
                $crmMeta->payment_status = $paymentCrmMeta->payment_status;
            }
        }

        // Booking Servicing V2: audit, fare history and PCI-safe card metadata.
        $serviceHistory = Schema::hasTable('booking_service_history')
            ? DB::table('booking_service_history')->leftJoin('admins','booking_service_history.admin_id','=','admins.id')->where('booking_service_history.booking_id',$booking->id)->select('booking_service_history.*','admins.name as admin_name')->orderByDesc('booking_service_history.id')->limit(50)->get()
            : collect();
        $fareHistory = Schema::hasTable('booking_fare_history')
            ? DB::table('booking_fare_history')->leftJoin('admins','booking_fare_history.admin_id','=','admins.id')->where('booking_fare_history.booking_id',$booking->id)->select('booking_fare_history.*','admins.name as admin_name')->orderByDesc('booking_fare_history.id')->get()
            : collect();
        $cardMeta = Schema::hasTable('booking_card_metadata') ? DB::table('booking_card_metadata')->where('booking_id',$booking->id)->first() : null;
        if ($cardMeta) {
            $payment['holder'] = $cardMeta->cardholder_name ?: $payment['holder'];
            $payment['last4'] = $cardMeta->last4 ?: $payment['last4'];
            $payment['brand'] = $cardMeta->brand ?: $payment['brand'];
        }

        // FH card visibility (31 Oct 2026): full card details (holder, number, expiry, CVV)
        // are shown only to Super Admin. Every other role sees card brand + last 4 digits only.
        // Card data stored in bookings.o_Credit is NOT changed by this.
        $isCardAdmin = $this->isCardAdmin($request);
        $storedCardNumber = is_array($credit)
            ? preg_replace('/\D+/', '', (string)($credit['creditcard_no'] ?? $credit['card_number'] ?? ''))
            : '';
        $lastFourDigits = preg_replace('/\D+/', '', (string)($payment['last4'] ?? ''));
        $lastFourDigits = $lastFourDigits !== '' ? substr($lastFourDigits, -4) : null;

        if ($isCardAdmin) {
            $payment['full_number'] = $storedCardNumber !== '' ? $storedCardNumber : null;
            $payment['last4'] = $lastFourDigits;
        } else {
            $payment = [
                'holder' => null,
                'last4' => $lastFourDigits,
                'brand' => $payment['brand'] ?? null,
                'expire_month' => null,
                'expire_year' => null,
                'cvv' => null,
                'full_number' => null,
            ];
        }
        // D5b (5 Nov 2026): Stripe bookings store only brand + last 4 + Stripe ref (never the card number).
        $stripeCredit = is_array($credit ?? null) ? $credit : [];
        if (str_starts_with((string) ($stripeCredit['payment_method'] ?? ''), 'stripe')) {
            $stripeLast4 = (string) ($stripeCredit['card_last4'] ?? '');
            $stripeRef = (string) ($stripeCredit['stripe_ref'] ?? '');
            $payment['last4'] = preg_match('/^\d{4}$/D', $stripeLast4) ? $stripeLast4 : null;
            $payment['brand'] = (string) ($stripeCredit['card_brand'] ?? '') !== '' ? (string) $stripeCredit['card_brand'] : 'Card';
            $payment['holder'] = 'Paid via Stripe'.(($stripeCredit['payment_method'] ?? '') === 'stripe_test' ? ' (TEST)' : '')
                .($stripeRef !== '' ? ' - ref ...'.substr($stripeRef, -6) : '');
            $payment['full_number'] = null;
            $payment['expire_month'] = null;
            $payment['expire_year'] = null;
            $payment['cvv'] = null;
        }
        $payment['is_card_admin'] = $isCardAdmin;

        $emailLogs = collect();
        $latestConfirmationEmail = null;
        if (Schema::hasTable('crm_email_logs')) {
            $emailLogs = DB::table('crm_email_logs')
                ->leftJoin('admins','crm_email_logs.admin_id','=','admins.id')
                ->where('crm_email_logs.booking_id', $booking->id)
                ->select('crm_email_logs.*','admins.name as admin_name')
                ->orderByDesc('crm_email_logs.id')
                ->limit(20)
                ->get();

            $latestConfirmationEmail = $emailLogs
                ->first(fn($log) => ($log->email_type ?? '') === 'booking_confirmation');
        }

        $templateQuery = DB::table('mail_templates');

        // The protected/system booking confirmation keeps its existing dedicated buttons.
        // This dropdown is for CRM-managed passenger templates.
        if (Schema::hasColumn('mail_templates', 'is_system')) {
            $templateQuery->where(function ($q) {
                $q->whereNull('is_system')->orWhere('is_system', 0);
            });
        }

        if (Schema::hasColumn('mail_templates', 'category')) {
            $templateQuery->orderBy('category');
        }

        $emailTemplates = $templateQuery
            ->orderBy('label')
            ->get();

        /*
         * Agent Performance / Finance Step 1
         * Financials live in a separate additive table so existing booking/API/fare
         * data remains untouched.
         */
        $bookingFinancial = Schema::hasTable('booking_financials')
            ? DB::table('booking_financials')->where('booking_id', $booking->id)->first()
            : null;

        // View is allowed, but work access is role/assignment based.
        $detailRoleSlug = $this->crmRoleSlug($request);
        $canAssignBooking = in_array($detailRoleSlug, ['super-admin', 'manager'], true);
        $canWorkBooking = $this->canWorkBooking($request, $booking);

        $defaultSellingAmount = max(
            0,
            (float) preg_replace('/[^0-9.\-]/', '', (string) $booking->o_FullPrice)
        );

        if (!$bookingFinancial) {
            $bookingFinancial = (object) [
                'selling_amount' => $defaultSellingAmount,
                'supplier_cost' => 0,
                'processing_cost' => 0,
                'other_cost' => 0,
                'gross_profit' => 0,
                'net_profit' => 0,
                'finance_note' => null,
            ];
        }

        return view('crm.booking-detail', compact(
            'booking', 'related', 'details', 'payment', 'remarks', 'assignments', 'docs', 'admins',
            'statusHistory', 'statusGroups', 'statusMeta', 'emailLogs', 'latestConfirmationEmail',
            'crmMeta', 'emailTemplates', 'serviceHistory', 'fareHistory', 'cardMeta',
            'bookingFinancial', 'canAssignBooking', 'canWorkBooking'
        ));
    }

    public function customerDetail(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $email = trim((string)$request->query('email'));
        abort_if($email === '', 404);

        $bookings = Booking::where('o_Email', $email)->latest()->get();
        abort_if($bookings->isEmpty(), 404);

        $customer = $bookings->first();

        $customerMeta = null;
        if (Schema::hasTable('customer_crm_meta')) {
            $customerMeta = DB::table('customer_crm_meta')
                ->leftJoin('admins','customer_crm_meta.assigned_to','=','admins.id')
                ->where('customer_crm_meta.email',$email)
                ->select('customer_crm_meta.*','admins.name as agent_name')
                ->first();
        }

        if (!$customerMeta) {
            $customerMeta = (object)[
                'customer_status'=>'Active','customer_tier'=>'Standard','vip_flag'=>0,
                'assigned_to'=>null,'agent_name'=>null,'preferred_airline'=>null,
                'preferred_cabin'=>null,'home_airport'=>null,'preferred_contact'=>'Email',
                'next_action'=>null,'next_action_at'=>null,'private_note'=>null,
            ];
        }

        $activity = Schema::hasTable('customer_activity')
            ? DB::table('customer_activity')
                ->leftJoin('admins','customer_activity.admin_id','=','admins.id')
                ->where('customer_activity.customer_email',$email)
                ->select('customer_activity.*','admins.name as admin_name')
                ->orderByDesc('customer_activity.id')->get()
            : collect();

        $totalFare = $bookings->sum(fn($b)=>(float)$b->o_FullPrice);
        $avgFare = $bookings->count() ? $totalFare / $bookings->count() : 0;
        $firstBooking = $bookings->min('created_at');
        $lastBooking = $bookings->max('created_at');

        $decode = function($value){
            if (!$value) return null;
            $decoded=json_decode((string)$value,true);
            return json_last_error()===JSON_ERROR_NONE ? $decoded : null;
        };

        $findSegment = function($node) use (&$findSegment){
            if (!is_array($node)) return null;
            if (isset($node['Departure_Iata']) || isset($node['DepartureIata']) ||
                isset($node['Arrival_Iata']) || isset($node['ArrivalIata']) ||
                isset($node['AirlineName']) || isset($node['FlightNum'])) return $node;
            foreach($node as $child){
                if(is_array($child)){
                    $found=$findSegment($child);
                    if($found) return $found;
                }
            }
            return null;
        };

        $routeCounts=collect();
        $airlineCounts=collect();

        foreach($bookings as $booking){
            $segment=$findSegment($decode($booking->o_SectorGo) ?: []);
            $from=$segment ? trim((string)($segment['Departure_Iata'] ?? $segment['DepartureIata'] ?? '')) : '';
            $to=$segment ? trim((string)($segment['Arrival_Iata'] ?? $segment['ArrivalIata'] ?? '')) : '';
            $airline=$segment ? trim((string)($segment['AirlineName'] ?? $segment['OperatingAirlineName'] ?? $segment['Airline'] ?? '')) : '';

            $booking->crm_route=($from && $to) ? strtoupper($from).' → '.strtoupper($to) : '—';
            $booking->crm_airline=$airline ?: '—';

            if($from && $to){
                $route=$booking->crm_route;
                $routeCounts[$route]=($routeCounts[$route] ?? 0)+1;
            }
            if($airline) $airlineCounts[$airline]=($airlineCounts[$airline] ?? 0)+1;
        }

        $stats=[
            'bookings'=>$bookings->count(),
            'total_fare'=>$totalFare,
            'average_fare'=>$avgFare,
            'first_booking'=>$firstBooking,
            'last_booking'=>$lastBooking,
            'sources'=>$bookings->groupBy(fn($b)=>$b->source ?: 'Unknown')->map->count()->sortDesc(),
            'statuses'=>$bookings->groupBy(fn($b)=>$b->status ?: 'Unspecified')->map->count()->sortDesc(),
            'top_routes'=>$routeCounts->sortDesc()->take(5),
            'top_airlines'=>$airlineCounts->sortDesc()->take(5),
        ];

        $admins=DB::table('admins')->where('status','1')->orderBy('name')->get();
        $statusMeta=$this->bookingStatusMeta();

        return view('crm.customer-detail',compact(
            'customer','bookings','stats','customerMeta','activity','admins','statusMeta'
        ));
    }

    public function customerProfileUpdate(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        abort_unless(Schema::hasTable('customer_crm_meta'),503,'Run customer CRM migration first.');

        $data=$request->validate([
            'email'=>['required','email','max:255'],
            'customer_status'=>['required','string',Rule::in(['Active','Follow-up','VIP','At Risk','Inactive','Do Not Contact'])],
            'customer_tier'=>['required','string',Rule::in(['Standard','Silver','Gold','Platinum'])],
            'vip_flag'=>['nullable','boolean'],
            'assigned_to'=>['nullable','integer','exists:admins,id'],
            'preferred_airline'=>['nullable','string','max:100'],
            'preferred_cabin'=>['nullable','string','max:50'],
            'home_airport'=>['nullable','string','max:20'],
            'preferred_contact'=>['nullable','string',Rule::in(['Email','Phone','WhatsApp'])],
            'next_action'=>['nullable','string','max:255'],
            'next_action_at'=>['nullable','date'],
            'private_note'=>['nullable','string','max:5000'],
        ]);

        $email=trim($data['email']);
        abort_unless(Booking::where('o_Email',$email)->exists(),404);

        $payload=[
            'email'=>$email,
            'customer_status'=>$data['customer_status'],
            'customer_tier'=>$data['customer_tier'],
            'vip_flag'=>$request->boolean('vip_flag'),
            'assigned_to'=>$data['assigned_to'] ?? null,
            'preferred_airline'=>$data['preferred_airline'] ?? null,
            'preferred_cabin'=>$data['preferred_cabin'] ?? null,
            'home_airport'=>$data['home_airport'] ?? null,
            'preferred_contact'=>$data['preferred_contact'] ?? 'Email',
            'next_action'=>$data['next_action'] ?? null,
            'next_action_at'=>$data['next_action_at'] ?? null,
            'private_note'=>$data['private_note'] ?? null,
            'updated_at'=>now(),
        ];

        $existing=DB::table('customer_crm_meta')->where('email',$email)->first();
        if($existing){
            DB::table('customer_crm_meta')->where('email',$email)->update($payload);
        }else{
            $payload['created_at']=now();
            DB::table('customer_crm_meta')->insert($payload);
        }

        return back()->with('crm_success','Customer profile updated.');
    }

    public function customerActivityStore(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        abort_unless(Schema::hasTable('customer_activity'),503,'Run customer CRM migration first.');

        $data=$request->validate([
            'email'=>['required','email','max:255'],
            'activity_type'=>['required','string',Rule::in(['note','call','email','follow_up','service','complaint','refund','task'])],
            'title'=>['required','string','max:255'],
            'details'=>['nullable','string','max:3000'],
        ]);

        $email=trim($data['email']);
        abort_unless(Booking::where('o_Email',$email)->exists(),404);

        DB::table('customer_activity')->insert([
            'customer_email'=>$email,
            'admin_id'=>(int)$request->session()->get('crm_admin_id') ?: null,
            'activity_type'=>$data['activity_type'],
            'title'=>$data['title'],
            'details'=>$data['details'] ?? null,
            'created_at'=>now(),'updated_at'=>now(),
        ]);

        return back()->with('crm_success','Customer activity added.');
    }

    public function customers(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $q = trim((string)$request->input('q', ''));
        $hasMeta = Schema::hasTable('customer_crm_meta');

        /*
         * CUSTOMER IDENTITY RULE
         * One CRM customer = one normalized email address.
         *
         * Previously the query grouped by name + email + phone, so the same
         * person appeared multiple times when their phone/name formatting changed.
         * Grouping by LOWER(TRIM(email)) keeps one customer row while preserving
         * all booking totals/history.
         */
        $query = Booking::query()
            ->whereNotNull('o_Email')
            ->whereRaw("TRIM(o_Email) <> ''");

        /*
         * Exclude archived customers.
         * Explicit COLLATE prevents utf8mb4_unicode_ci vs utf8mb4_general_ci
         * comparison errors without changing the database schema.
         */
        if (Schema::hasTable('crm_archives')) {
            $query->whereNotExists(function($sub) {
                $sub->select(DB::raw(1))
                    ->from('crm_archives')
                    ->where('crm_archives.module', 'customer')
                    ->whereNull('crm_archives.restored_at')
                    ->whereRaw(
                        'LOWER(TRIM(crm_archives.record_key)) COLLATE utf8mb4_unicode_ci = ' .
                        'LOWER(TRIM(bookings.o_Email)) COLLATE utf8mb4_unicode_ci'
                    );
            });
        }

        /*
         * Aggregate bookings into one row per normalized email.
         * MIN/MAX are used only to pick representative display values;
         * booking_count, lifetime_value and dates include all matching bookings.
         */
        $query->selectRaw("
                MAX(NULLIF(TRIM(o_Name), '')) as o_Name,
                MIN(TRIM(o_Email)) as o_Email,
                MAX(NULLIF(TRIM(o_Phone), '')) as o_Phone,
                COUNT(*) as bookings_count,
                SUM(CAST(o_FullPrice AS DECIMAL(14,2))) as lifetime_value,
                AVG(CAST(o_FullPrice AS DECIMAL(14,2))) as average_booking_value,
                MIN(created_at) as first_booking,
                MAX(created_at) as last_booking
            ")
            ->when($q, function($builder) use ($q) {
                $builder->where(function($b) use ($q) {
                    $b->where('o_Name', 'like', "%{$q}%")
                      ->orWhere('o_Email', 'like', "%{$q}%")
                      ->orWhere('o_Phone', 'like', "%{$q}%");
                });
            })
            ->groupBy(DB::raw('LOWER(TRIM(o_Email))'));

        if ($request->input('sort') === 'value') {
            $query->orderByDesc('lifetime_value');
        } elseif ($request->input('sort') === 'bookings') {
            $query->orderByDesc('bookings_count');
        } else {
            $query->orderByDesc('last_booking');
        }

        $perPage = (int)$request->input('per_page', 15);
        if (!in_array($perPage, [15,25,50,100], true)) $perPage = 15;

        $customers = $query->paginate($perPage)->withQueryString();

        /*
         * Customer CRM meta lookup is normalized as well, so case/whitespace
         * differences in an email do not create a second CRM identity.
         */
        $emails = collect($customers->items())
            ->pluck('o_Email')
            ->filter()
            ->map(fn($email) => strtolower(trim((string)$email)))
            ->values()
            ->all();

        $metaMap = $hasMeta
            ? DB::table('customer_crm_meta')
                ->leftJoin('admins', 'customer_crm_meta.assigned_to', '=', 'admins.id')
                ->whereIn(DB::raw('LOWER(TRIM(customer_crm_meta.email))'), $emails)
                ->select('customer_crm_meta.*', 'admins.name as agent_name')
                ->get()
                ->keyBy(fn($row) => strtolower(trim((string)$row->email)))
            : collect();

        $customers->getCollection()->transform(function($c) use ($metaMap) {
            $emailKey = strtolower(trim((string)$c->o_Email));
            $meta = $metaMap->get($emailKey);

            $c->crm_status = $meta->customer_status ?? 'Active';
            $c->crm_tier = $meta->customer_tier ?? 'Standard';
            $c->crm_vip = (bool)($meta->vip_flag ?? false);
            $c->crm_agent = $meta->agent_name ?? 'Unassigned';
            $c->crm_next_action = $meta->next_action ?? null;
            $c->crm_next_action_at = $meta->next_action_at ?? null;

            return $c;
        });

        /*
         * KPI calculations use the same normalized-email identity rule as the
         * table, so "Customers" and the actual customer rows stay consistent.
         */
        $customerCounts = DB::table('bookings')
            ->selectRaw("LOWER(TRIM(o_Email)) as email_key, COUNT(*) as c")
            ->whereNotNull('o_Email')
            ->whereRaw("TRIM(o_Email) <> ''")
            ->groupBy(DB::raw('LOWER(TRIM(o_Email))'));

        $stats = [
            'customers' => DB::query()
                ->fromSub(
                    DB::table('bookings')
                        ->selectRaw("LOWER(TRIM(o_Email)) as email_key")
                        ->whereNotNull('o_Email')
                        ->whereRaw("TRIM(o_Email) <> ''")
                        ->groupBy(DB::raw('LOWER(TRIM(o_Email))')),
                    'customer_keys'
                )
                ->count(),

            'bookings' => DB::table('bookings')->count(),

            'lifetime_value' => (float)DB::table('bookings')
                ->sum(DB::raw('CAST(o_FullPrice AS DECIMAL(14,2))')),

            'repeat_customers' => DB::query()
                ->fromSub($customerCounts, 'cc')
                ->where('c', '>', 1)
                ->count(),

            'vip_customers' => $hasMeta
                ? DB::table('customer_crm_meta')->where('vip_flag', 1)->count()
                : 0,
        ];

        return view('crm.customers', compact('customers', 'stats'));
    }


    public function leads(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $q = trim((string) $request->input('q', ''));
        $hasMeta = Schema::hasTable('lead_crm_meta');

        $query = DB::table('enquiries');

        if (Schema::hasTable('crm_archives')) {
            $query->whereNotExists(function($sub) {
                $sub->select(DB::raw(1))
                    ->from('crm_archives')
                    ->where('crm_archives.module','lead')
                    ->whereNull('crm_archives.restored_at')
                    ->whereRaw('CAST(crm_archives.record_key AS UNSIGNED) = enquiries.id');
            });
        }

        if ($hasMeta) {
            $query->leftJoin('lead_crm_meta','lead_crm_meta.lead_id','=','enquiries.id')
                ->leftJoin('admins','lead_crm_meta.assigned_to','=','admins.id')
                ->select(
                    'enquiries.*',
                    'lead_crm_meta.stage as crm_stage',
                    'lead_crm_meta.priority as crm_priority',
                    'lead_crm_meta.source as crm_source',
                    'lead_crm_meta.next_action as crm_next_action',
                    'lead_crm_meta.next_follow_up_at as crm_next_follow_up_at',
                    'lead_crm_meta.assigned_to as crm_assigned_to',
                    'admins.name as crm_agent_name'
                );
        } else {
            $query->select('enquiries.*');
        }

        $query->when($q, function ($builder) use ($q, $hasMeta) {
            $builder->where(function ($b) use ($q, $hasMeta) {
                $b->where('enquiries.name', 'like', "%{$q}%")
                  ->orWhere('enquiries.emailid', 'like', "%{$q}%")
                  ->orWhere('enquiries.contact', 'like', "%{$q}%")
                  ->orWhere('enquiries.going_to', 'like', "%{$q}%")
                  ->orWhere('enquiries.subject', 'like', "%{$q}%");

                if (ctype_digit($q)) {
                    $b->orWhere('enquiries.id',(int)$q);
                }

                if ($hasMeta) {
                    $b->orWhere('lead_crm_meta.source','like',"%{$q}%")
                      ->orWhere('lead_crm_meta.next_action','like',"%{$q}%");
                }
            });
        });

        if ($hasMeta && $request->filled('stage')) {
            $query->where('lead_crm_meta.stage',$request->input('stage'));
        }

        if ($hasMeta && $request->filled('priority')) {
            $query->where('lead_crm_meta.priority',$request->input('priority'));
        }

        if ($request->filled('type')) {
            $query->where('enquiries.type',$request->input('type'));
        }

        if ($hasMeta && $request->filled('agent')) {
            $query->where('lead_crm_meta.assigned_to',(int)$request->input('agent'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('enquiries.created_at','>=',$request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('enquiries.created_at','<=',$request->input('date_to'));
        }

        $perPage = (int)$request->input('per_page',15);
        if (!in_array($perPage,[15,25,50,100],true)) $perPage = 15;

        $leads = $query->orderByDesc('enquiries.id')->paginate($perPage)->withQueryString();

        $leadStages = ['New','Follow-up','Qualified','Quoted','Negotiation','Converted','Closed','Lost'];
        $leadPriorities = ['Low','Normal','High','Urgent'];

        /*
         * Lead filters and KPI cards must use the same ACTIVE lead population
         * as the table above. Archived leads remain in enquiries for restore/history,
         * but they must not continue to appear in Total/New/Urgent counters.
         */
        $activeLeadIds = DB::table('enquiries')->select('enquiries.id');

        if (Schema::hasTable('crm_archives')) {
            $activeLeadIds->whereNotExists(function($sub) {
                $sub->select(DB::raw(1))
                    ->from('crm_archives')
                    ->where('crm_archives.module','lead')
                    ->whereNull('crm_archives.restored_at')
                    ->whereRaw('CAST(crm_archives.record_key AS UNSIGNED) = enquiries.id');
            });
        }

        $leadTypes = DB::table('enquiries')
            ->whereIn('id', (clone $activeLeadIds))
            ->whereNotNull('type')
            ->where('type','!=','')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        $adminQuery = DB::table('admins')->where('status','1')->orderBy('name');
        if (Schema::hasColumn('admins','crm_deleted_at')) {
            $adminQuery->whereNull('crm_deleted_at');
        }
        $admins = $adminQuery->get();

        $activeTotal = (clone $activeLeadIds)->count();

        if ($hasMeta) {
            $activeMeta = DB::table('lead_crm_meta')
                ->join('enquiries','lead_crm_meta.lead_id','=','enquiries.id');

            if (Schema::hasTable('crm_archives')) {
                $activeMeta->whereNotExists(function($sub) {
                    $sub->select(DB::raw(1))
                        ->from('crm_archives')
                        ->where('crm_archives.module','lead')
                        ->whereNull('crm_archives.restored_at')
                        ->whereRaw('CAST(crm_archives.record_key AS UNSIGNED) = enquiries.id');
                });
            }

            $stats = [
                'total' => $activeTotal,
                'new' => (clone $activeMeta)->where('lead_crm_meta.stage','New')->distinct('enquiries.id')->count('enquiries.id'),
                'follow_up' => (clone $activeMeta)->where('lead_crm_meta.stage','Follow-up')->distinct('enquiries.id')->count('enquiries.id'),
                'qualified' => (clone $activeMeta)->where('lead_crm_meta.stage','Qualified')->distinct('enquiries.id')->count('enquiries.id'),
                'converted' => (clone $activeMeta)->where('lead_crm_meta.stage','Converted')->distinct('enquiries.id')->count('enquiries.id'),
                'urgent' => (clone $activeMeta)->where('lead_crm_meta.priority','Urgent')->distinct('enquiries.id')->count('enquiries.id'),
            ];
        } else {
            $stats = [
                'total' => $activeTotal,
                'new' => $activeTotal,
                'follow_up' => 0,
                'qualified' => 0,
                'converted' => 0,
                'urgent' => 0,
            ];
        }

        return view('crm.leads', compact(
            'leads','leadStages','leadPriorities','leadTypes','admins','stats'
        ));
    }

    public function leadCreate(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        abort_unless(Schema::hasTable('lead_crm_meta'), 503, 'Run lead CRM migration first.');

        $adminQuery = DB::table('admins')->where('status', '1')->orderBy('name');
        if (Schema::hasColumn('admins', 'crm_deleted_at')) {
            $adminQuery->whereNull('crm_deleted_at');
        }

        $admins = $adminQuery->get();
        $leadStages = ['New','Follow-up','Qualified','Quoted','Negotiation','Converted','Closed','Lost'];
        $leadPriorities = ['Low','Normal','High','Urgent'];

        return view('crm.lead-create', compact('admins','leadStages','leadPriorities'));
    }

    public function leadImport(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        abort_unless(Schema::hasTable('lead_crm_meta'), 503, 'Run lead CRM migration first.');

        return view('crm.lead-import');
    }

    public function leadImportStore(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        abort_unless(Schema::hasTable('lead_crm_meta'), 503, 'Run lead CRM migration first.');

        $request->validate([
            'lead_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $file = $request->file('lead_file');
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return back()->withErrors(['lead_file' => 'Unable to read the uploaded CSV file.']);
        }

        $expectedHeaders = [
            'Pax / Contact Name',
            'Email',
            'Phone',
            'Origin',
            'Destination',
            'Depart Date',
            'Return Date',
            'Airline',
            'Passengers',
            'Quoted Price (USD)',
            'Source',
            'Device',
            'Lead Stage / Notes',
        ];

        $headers = fgetcsv($handle);

        if (!$headers) {
            fclose($handle);
            return back()->withErrors(['lead_file' => 'CSV file is empty or has no header row.']);
        }

        $headers = array_map(function ($value) {
            $value = trim((string)$value);
            return preg_replace('/^\xEF\xBB\xBF/', '', $value);
        }, $headers);

        if ($headers !== $expectedHeaders) {
            fclose($handle);
            return back()->withErrors([
                'lead_file' => 'CSV headers do not match the required FaresHunt lead import format.'
            ]);
        }

        $imported = 0;
        $duplicates = 0;
        $invalid = 0;
        $rowNumber = 1;

        $normalizeEmail = function ($value) {
            return strtolower(trim((string)$value));
        };

        $normalizePhone = function ($value) {
            return preg_replace('/\D+/', '', (string)$value);
        };

        $parseDate = function ($value) {
            $value = trim((string)$value);

            if ($value === '' || strcasecmp($value, 'One Way') === 0) {
                return null;
            }

            foreach (['Y-m-d', 'm/d/Y', 'n/j/Y', 'm/d/y', 'n/j/y'] as $format) {
                $date = \DateTime::createFromFormat('!' . $format, $value);
                if ($date && $date->format($format) === $value) {
                    return $date->format('Y-m-d');
                }
            }

            $timestamp = strtotime($value);
            return $timestamp !== false ? date('Y-m-d', $timestamp) : null;
        };

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if (count(array_filter($row, fn($v) => trim((string)$v) !== '')) === 0) {
                continue;
            }

            if (count($row) !== count($expectedHeaders)) {
                $invalid++;
                continue;
            }

            $data = array_combine($expectedHeaders, $row);

            $name = trim((string)$data['Pax / Contact Name']);
            $email = $normalizeEmail($data['Email']);
            $phoneRaw = trim((string)$data['Phone']);
            $phoneNormalized = $normalizePhone($phoneRaw);
            $origin = trim((string)$data['Origin']);
            $destination = trim((string)$data['Destination']);
            $departRaw = trim((string)$data['Depart Date']);
            $returnRaw = trim((string)$data['Return Date']);
            $departDate = $parseDate($departRaw);
            $returnDate = $parseDate($returnRaw);
            $airline = trim((string)$data['Airline']);
            $passengers = (int)preg_replace('/\D+/', '', (string)$data['Passengers']);
            $priceRaw = trim((string)$data['Quoted Price (USD)']);
            $source = trim((string)$data['Source']);
            $device = trim((string)$data['Device']);
            $originalStage = trim((string)$data['Lead Stage / Notes']);

            // A lead is importable when at least one usable contact method exists.
            // Name, route and travel dates may legitimately be unknown for early-stage leads.
            if ($phoneNormalized === '' && $email === '') {
                $invalid++;
                continue;
            }

            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $invalid++;
                continue;
            }

            if ($returnRaw !== '' && strcasecmp($returnRaw, 'One Way') !== 0 && $returnDate === null) {
                $invalid++;
                continue;
            }

            if ($returnDate !== null && $returnDate < $departDate) {
                $invalid++;
                continue;
            }

            $passengers = max(1, min(50, $passengers ?: 1));

            $budget = null;
            if ($priceRaw !== '') {
                $cleanPrice = preg_replace('/[^0-9.\-]/', '', $priceRaw);
                if ($cleanPrice !== '' && is_numeric($cleanPrice) && (float)$cleanPrice >= 0) {
                    $budget = (float)$cleanPrice;
                }
            }

            $duplicateQuery = DB::table('enquiries');

            $duplicateQuery->where(function ($q) use ($email, $phoneNormalized) {
                $hasCondition = false;

                if ($email !== '') {
                    $q->whereRaw('LOWER(TRIM(emailid)) = ?', [$email]);
                    $hasCondition = true;
                }

                if ($phoneNormalized !== '') {
                    $method = $hasCondition ? 'orWhereRaw' : 'whereRaw';
                    $q->{$method}(
                        "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(contact, ' ', ''), '-', ''), '(', ''), ')', ''), '+', ''), '.', '') = ?",
                        [$phoneNormalized]
                    );
                }
            });

            if ($duplicateQuery->exists()) {
                $duplicates++;
                continue;
            }

            $noteParts = [];

            if ($airline !== '') {
                $noteParts[] = 'Airline: ' . $airline;
            }

            if ($device !== '') {
                $noteParts[] = 'Device: ' . $device;
            }

            if ($originalStage !== '') {
                $noteParts[] = 'Original funnel stage/notes: ' . $originalStage;
            }

            if (strcasecmp($returnRaw, 'One Way') === 0) {
                $noteParts[] = 'Trip type: One Way';
            }

            $noteParts[] = 'Imported from bulk lead CSV.';
            $privateNote = implode("\n", $noteParts);

            try {
                DB::transaction(function () use (
                    $request,
                    $name,
                    $email,
                    $phoneRaw,
                    $origin,
                    $destination,
                    $departDate,
                    $returnDate,
                    $passengers,
                    $budget,
                    $source,
                    $privateNote
                ) {
                    $now = now();

                    $leadId = DB::table('enquiries')->insertGetId([
                        'type' => 'Default',
                        'going_to' => $destination ?: null,
                        'checkin_date' => $departDate,
                        'occupant' => $passengers,
                        'name' => $name,
                        'contact' => $phoneRaw,
                        'emailid' => $email !== '' ? $email : null,
                        'subject' => 'Imported CRM Lead',
                        'message' => 'Lead imported through CRM bulk lead import.',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    DB::table('lead_crm_meta')->insert([
                        'lead_id' => $leadId,
                        'stage' => 'New',
                        'priority' => 'Normal',
                        'source' => $source !== '' ? $source : 'Bulk Import',
                        'assigned_to' => null,
                        'next_action' => null,
                        'next_follow_up_at' => null,
                        'travel_from' => $origin !== '' ? $origin : null,
                        'travel_to' => $destination !== '' ? $destination : null,
                        'return_date' => $returnDate,
                        'adults' => $passengers,
                        'children' => 0,
                        'infants' => 0,
                        'cabin' => null,
                        'budget' => $budget,
                        'private_note' => $privateNote,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    if (Schema::hasTable('lead_activity')) {
                        DB::table('lead_activity')->insert([
                            'lead_id' => $leadId,
                            'admin_id' => (int)$request->session()->get('crm_admin_id') ?: null,
                            'activity_type' => 'note',
                            'title' => 'Lead imported from Excel',
                            'details' => 'Lead imported through CRM bulk lead import.',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                });

                $imported++;
            } catch (\Throwable $e) {
                report($e);
                $invalid++;
            }
        }

        fclose($handle);

        return back()->with(
            'crm_success',
            "Import complete. Imported: {$imported} | Skipped duplicates: {$duplicates} | Invalid rows: {$invalid}"
        );
    }

    public function leadStore(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        abort_unless(Schema::hasTable('lead_crm_meta'), 503, 'Run lead CRM migration first.');

        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'emailid' => ['nullable','email','max:150'],
            'contact' => ['required','string','max:150'],
            'travel_from' => ['nullable','string','max:100'],
            'travel_to' => ['nullable','string','max:100'],
            'checkin_date' => ['nullable','date'],
            'return_date' => ['nullable','date','after_or_equal:checkin_date'],
            'adults' => ['nullable','integer','min:1','max:50'],
            'children' => ['nullable','integer','min:0','max:50'],
            'infants' => ['nullable','integer','min:0','max:50'],
            'cabin' => ['nullable','string','max:50'],
            'budget' => ['nullable','numeric','min:0'],
            'source' => ['nullable','string','max:100'],
            'stage' => ['required','string',Rule::in(['New','Follow-up','Qualified','Quoted','Negotiation','Converted','Closed','Lost'])],
            'priority' => ['required','string',Rule::in(['Low','Normal','High','Urgent'])],
            'assigned_to' => ['nullable','integer','exists:admins,id'],
            'next_action' => ['nullable','string','max:255'],
            'next_follow_up_at' => ['nullable','date'],
            'subject' => ['nullable','string','max:255'],
            'message' => ['nullable','string','max:5000'],
            'private_note' => ['nullable','string','max:5000'],
        ]);

        $leadId = DB::transaction(function () use ($data, $request) {
            $now = now();

            $leadId = DB::table('enquiries')->insertGetId([
                'type' => 'Default',
                'going_to' => $data['travel_to'] ?? null,
                'checkin_date' => $data['checkin_date'] ?? null,
                'occupant' => $data['adults'] ?? 1,
                'name' => $data['name'],
                'contact' => $data['contact'],
                'emailid' => $data['emailid'] ?? null,
                'subject' => $data['subject'] ?? 'Manual CRM Lead',
                'message' => $data['message'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('lead_crm_meta')->insert([
                'lead_id' => $leadId,
                'stage' => $data['stage'],
                'priority' => $data['priority'],
                'source' => $data['source'] ?? 'Manual',
                'assigned_to' => $data['assigned_to'] ?? null,
                'next_action' => $data['next_action'] ?? null,
                'next_follow_up_at' => $data['next_follow_up_at'] ?? null,
                'travel_from' => $data['travel_from'] ?? null,
                'travel_to' => $data['travel_to'] ?? null,
                'return_date' => $data['return_date'] ?? null,
                'adults' => $data['adults'] ?? 1,
                'children' => $data['children'] ?? 0,
                'infants' => $data['infants'] ?? 0,
                'cabin' => $data['cabin'] ?? null,
                'budget' => $data['budget'] ?? null,
                'private_note' => $data['private_note'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if (Schema::hasTable('lead_activity')) {
                DB::table('lead_activity')->insert([
                    'lead_id' => $leadId,
                    'admin_id' => (int)$request->session()->get('crm_admin_id') ?: null,
                    'activity_type' => 'note',
                    'title' => 'Manual lead created',
                    'details' => 'Lead created manually in CRM.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            return $leadId;
        });

        $route = $request->getHost() === 'crm.fareshunt.com'
            ? 'crm.lead_detail'
            : 'legacy.crm.lead_detail';

        return redirect()->route($route, $leadId)
            ->with('crm_success', 'Manual lead created successfully.');
    }

    public function reports(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        $byStatus = Booking::select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->orderByDesc('total')->get();
        $bySource = Booking::select('source', DB::raw('COUNT(*) as total'))->groupBy('source')->orderByDesc('total')->get();
        $monthly = Booking::select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('COUNT(*) as total'))
            ->groupBy('month')->orderBy('month')->get()->take(-12);
        return view('crm.reports', compact('byStatus', 'bySource', 'monthly'));
    }

    public function fareRules(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $rules = Markup::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = trim($request->input('q'));
                $q->where(function ($w) use ($term) {
                    $w->where('name', 'like', "%{$term}%")
                      ->orWhere('departure_code', 'like', "%{$term}%")
                      ->orWhere('arrival_code', 'like', "%{$term}%")
                      ->orWhere('airline_code', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('status'), fn($q) => $q->where('is_active', $request->input('status')))
            ->when($request->filled('journey'), function ($q) use ($request) {
                $field = $request->input('journey') === 'one_way' ? 'is_oneway' : 'is_roundtrip';
                $q->where($field, 'Yes');
            })
            ->when($request->filled('source'), function ($q) use ($request) {
                if ($request->input('source') === 'website') {
                    $q->where('is_ownsite', 'Yes');
                } elseif ($request->input('source') === 'metasite') {
                    $q->where('is_facecompare', 'Yes');
                }
            })
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => Markup::count(),
            'active' => Markup::where('is_active', 'Yes')->count(),
            'expired' => Markup::whereDate('to_date', '<', now()->toDateString())->count(),
            'airline' => Markup::whereNotNull('airline_code')->where('airline_code', '!=', '')->count(),
        ];

        return view('crm.fare-rules', compact('rules', 'stats'));
    }

    public function fareRuleCreate(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        return view('crm.fare-rule-form', ['rule' => null]);
    }

    public function fareRuleStore(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $data = $this->validateFareRule($request);
        Markup::create($data);

        return redirect()->route('crm.fare_rules')->with('success', 'Fare rule created successfully.');
    }

    public function fareRuleEdit(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        $rule = Markup::findOrFail($id);
        return view('crm.fare-rule-form', compact('rule'));
    }

    public function fareRuleUpdate(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $rule = Markup::findOrFail($id);
        $rule->update($this->validateFareRule($request));

        return redirect()->route('crm.fare_rules')->with('success', 'Fare rule updated successfully.');
    }

    public function fareRuleToggle(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        $rule = Markup::findOrFail($id);
        $rule->is_active = in_array($rule->is_active, ['Yes', '1'], true) ? 'No' : 'Yes';
        $rule->save();
        return back()->with('success', 'Fare rule status updated.');
    }

    public function fareRuleDuplicate(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        $source = Markup::findOrFail($id);
        $copy = $source->replicate();
        $copy->name = $source->name . ' - Copy';
        $copy->is_active = 'No';
        $copy->save();
        return redirect()->route('crm.fare_rule_edit', $copy->id)->with('success', 'Rule duplicated as inactive copy.');
    }

    public function fareRuleDelete(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        Markup::findOrFail($id)->delete();
        return back()->with('success', 'Fare rule deleted.');
    }

    public function fareRuleTest(Request $request)
{
    if ($redirect = $this->guard($request)) return $redirect;

    // Only run validation after Run Simulation is clicked.
    if (!$request->boolean('run_simulation')) {
        return view('crm.fare-rule-test');
    }

    $data = $request->validate([
            'source' => ['required', 'in:website,metasite'],
            'departure_code' => ['required', 'string', 'max:10'],
            'departure_country' => ['nullable', 'string', 'max:3'],
            'arrival_code' => ['required', 'string', 'max:10'],
            'arrival_country' => ['nullable', 'string', 'max:3'],
            'airline_code' => ['nullable', 'string', 'max:10'],
            'journey' => ['required', 'in:one_way,round_trip'],
            'cabin' => ['required', 'in:Econom,Peconom,Business,firstclass'],
            'adult_fare' => ['required', 'numeric', 'min:0'],
            'child_fare' => ['nullable', 'numeric', 'min:0'],
            'infant_fare' => ['nullable', 'numeric', 'min:0'],
            'adults' => ['required', 'integer', 'min:1'],
            'children' => ['nullable', 'integer', 'min:0'],
            'infants' => ['nullable', 'integer', 'min:0'],
            'travel_date' => ['required', 'date'],
        ]);

        foreach (['departure_code','departure_country','arrival_code','arrival_country','airline_code'] as $field) {
            $data[$field] = strtoupper(trim((string)($data[$field] ?? '')));
        }

        $date = $data['travel_date'];
        $journeyField = $data['journey'] === 'one_way' ? 'is_oneway' : 'is_roundtrip';
        $cabinField = [
            'Econom' => 'is_economy',
            'Peconom' => 'is_peconomy',
            'Business' => 'is_business',
            'firstclass' => 'is_fclass'
        ][$data['cabin']];
        $channelField = $data['source'] === 'metasite' ? 'is_facecompare' : 'is_ownsite';

        $rules = Markup::query()
            ->where('is_active', 'Yes')
            ->where($channelField, 'Yes')
            ->whereDate('from_date', '<=', $date)
            ->whereDate('to_date', '>=', $date)
            ->where($journeyField, 'Yes')
            ->where($cabinField, 'Yes')
            ->get();

        $matches = $rules->filter(function ($r) use ($data) {
            $dep = array_values(array_filter(array_map('trim', explode(',', strtoupper((string)$r->departure_code)))));
            $arr = array_values(array_filter(array_map('trim', explode(',', strtoupper((string)$r->arrival_code)))));
            $air = array_values(array_filter(array_map('trim', explode(',', strtoupper((string)$r->airline_code)))));

            $depValue = $r->departure_type === 'airport'
                ? $data['departure_code']
                : ($data['departure_country'] ?? '');

            $arrValue = $r->arrival_type === 'airport'
                ? $data['arrival_code']
                : ($data['arrival_country'] ?? '');

            $depOk = in_array($depValue, $dep, true);
            $arrOk = in_array($arrValue, $arr, true);
            $airOk = empty($air) || in_array($data['airline_code'] ?? '', $air, true);

            return $depOk && $arrOk && $airOk;
        })->values();

        $evaluated = $matches->map(function ($r) use ($data) {
            $adult = (float)$data['adult_fare'];
            $child = (float)($data['child_fare'] ?? 0);
            $infant = (float)($data['infant_fare'] ?? 0);
            $markup = (float)$r->markup;

            // Match the current website/meta engine:
            // positive rule values reduce the displayed fare; negative values increase it.
            $adultDelta = $r->amt_per === 'per' ? $adult * ($markup / 100) : $markup;
            $childDelta = $r->amt_per === 'per' ? $child * ($markup / 100) : $markup;

            $newAdult = max(0, $adult - $adultDelta);
            $newChild = $child > 0 ? max(0, $child - $childDelta) : 0;
            $newInfant = $infant;

            $specificity =
                ($r->departure_type === 'airport' ? 30 : 10) +
                ($r->arrival_type === 'airport' ? 30 : 10) +
                (!empty(trim((string)$r->airline_code)) ? 20 : 0) +
                ((int)$r->fare_min_price > 0 ? 5 : 0);

            $baseTotal =
                ($adult * (int)$data['adults']) +
                ($child * (int)($data['children'] ?? 0)) +
                ($infant * (int)($data['infants'] ?? 0));

            $finalTotal =
                ($newAdult * (int)$data['adults']) +
                ($newChild * (int)($data['children'] ?? 0)) +
                ($newInfant * (int)($data['infants'] ?? 0));

            return [
                'rule' => $r,
                'adult_base' => $adult,
                'child_base' => $child,
                'infant_base' => $infant,
                'new_adult' => $newAdult,
                'new_child' => $newChild,
                'new_infant' => $newInfant,
                'delta_adult' => $adultDelta,
                'base_total' => $baseTotal,
                'final_total' => $finalTotal,
                'total_delta' => $baseTotal - $finalTotal,
                'specificity' => $specificity,
            ];
        });

        // Existing engine effectively chooses the lowest resulting adult fare.
        // Specificity is used here only as a deterministic tie-breaker for the simulator.
        $selected = $evaluated
            ->sortBy([
                ['new_adult', 'asc'],
                ['specificity', 'desc'],
            ])
            ->first();

        $result = null;
        if ($selected) {
            $result = [
                'rule' => $selected['rule'],
                'adult_price' => $selected['new_adult'],
                'child_price' => $selected['new_child'],
                'infant_price' => $selected['new_infant'],
                'base_total' => $selected['base_total'],
                'total' => $selected['final_total'],
                'delta_total' => $selected['total_delta'],
                'specificity' => $selected['specificity'],
            ];
        }

        $diagnostics = [
            'channel' => $data['source'] === 'metasite' ? 'Meta Site / Fare Compare' : 'Website / Own Site',
            'route' => $data['departure_code'].' → '.$data['arrival_code'],
            'airline' => $data['airline_code'] ?: 'Any airline',
            'journey' => $data['journey'] === 'round_trip' ? 'Round Trip' : 'One Way',
            'cabin' => [
                'Econom' => 'Economy',
                'Peconom' => 'Premium Economy',
                'Business' => 'Business',
                'firstclass' => 'First Class',
            ][$data['cabin']],
            'travel_date' => $date,
        ];

        return view('crm.fare-rule-test', compact('data','evaluated','result','diagnostics'));
    }

    private function validateFareRule(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required','string','max:100'],
            'departure_type' => ['required','in:airport,country'],
            'departure_code' => ['nullable','string','max:255'],
            'arrival_type' => ['required','in:airport,country'],
            'arrival_code' => ['nullable','string','max:255'],
            'airline_code' => ['nullable','string','max:255'],
            'markup' => ['required','integer'],
            'amt_per' => ['required','in:amt,per'],
            'is_oneway' => ['nullable','boolean'],
            'is_roundtrip' => ['nullable','boolean'],
            'is_economy' => ['nullable','boolean'],
            'is_peconomy' => ['nullable','boolean'],
            'is_business' => ['nullable','boolean'],
            'is_fclass' => ['nullable','boolean'],
            'is_ownsite' => ['nullable','boolean'],
            'is_facecompare' => ['nullable','boolean'],
            'fare_min_price' => ['nullable','integer','min:0'],
            'from_date' => ['nullable','date'],
            'to_date' => ['nullable','date','after_or_equal:from_date'],
            'is_active' => ['required','in:Yes,No'],
        ]);

        foreach (['is_oneway','is_roundtrip','is_economy','is_peconomy','is_business','is_fclass','is_ownsite','is_facecompare'] as $field) {
            $data[$field] = $request->boolean($field) ? 'Yes' : 'No';
        }
        $data['departure_code'] = strtoupper(trim((string)($data['departure_code'] ?? '')));
        $data['arrival_code'] = strtoupper(trim((string)($data['arrival_code'] ?? '')));
        $data['airline_code'] = strtoupper(trim((string)($data['airline_code'] ?? '')));
        $data['is_active'] = $request->input('is_active') === 'Yes' ? 'Yes' : 'No';

        if ($data['is_ownsite'] !== 'Yes' && $data['is_facecompare'] !== 'Yes') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'is_ownsite' => 'Select at least one channel: Website or Meta Site.',
            ]);
        }

        return $data;
    }

    public function payments(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $hasPaymentMeta = Schema::hasTable('payment_crm_meta');
        $hasBookingMeta = Schema::hasTable('booking_crm_meta');
        $query = Booking::query();

        if (Schema::hasTable('crm_archives')) {
            $query->whereNotExists(function($sub) {
                $sub->select(DB::raw(1))
                    ->from('crm_archives')
                    ->where('crm_archives.module','payment')
                    ->whereNull('crm_archives.restored_at')
                    ->whereRaw('CAST(crm_archives.record_key AS UNSIGNED) = bookings.id');
            });
        }

        if ($request->filled('q')) {
            $term = trim((string)$request->input('q'));
            $query->where(function($q) use ($term, $hasPaymentMeta) {
                $q->where('o_Name','like',"%{$term}%")
                  ->orWhere('o_Email','like',"%{$term}%")
                  ->orWhere('o_Phone','like',"%{$term}%");
                if (ctype_digit($term)) $q->orWhere('id',(int)$term);

                if ($hasPaymentMeta) {
                    $q->orWhereExists(function($sub) use ($term) {
                        $sub->select(DB::raw(1))->from('payment_crm_meta')
                            ->whereColumn('payment_crm_meta.booking_id','bookings.id')
                            ->where(function($w) use ($term) {
                                $w->where('transaction_reference','like',"%{$term}%")
                                  ->orWhere('processor','like',"%{$term}%")
                                  ->orWhere('refund_reference','like',"%{$term}%");
                            });
                    });
                }
            });
        }

        if ($request->filled('booking_status')) $query->where('status',$request->input('booking_status'));
        if ($request->filled('date_from')) $query->whereDate('created_at','>=',$request->input('date_from'));
        if ($request->filled('date_to')) $query->whereDate('created_at','<=',$request->input('date_to'));

        if ($request->filled('payment_status')) {
            $status = $request->input('payment_status');
            if ($status === 'Untracked') {
                $query->where(function($q) use ($hasPaymentMeta, $hasBookingMeta) {
                    if ($hasPaymentMeta) {
                        $q->where(function($inner) use ($hasBookingMeta) {
                            $inner->whereNotExists(function($sub) {
                                $sub->select(DB::raw(1))->from('payment_crm_meta')
                                    ->whereColumn('payment_crm_meta.booking_id','bookings.id');
                            })->orWhereExists(function($sub) {
                                $sub->select(DB::raw(1))->from('payment_crm_meta')
                                    ->whereColumn('payment_crm_meta.booking_id','bookings.id')
                                    ->where('payment_status','Untracked');
                            });
                            if ($hasBookingMeta) {
                                $inner->where(function($legacy) {
                                    $legacy->whereNotExists(function($sub) {
                                        $sub->select(DB::raw(1))->from('booking_crm_meta')
                                            ->whereColumn('booking_crm_meta.booking_id','bookings.id')
                                            ->whereNotNull('payment_status');
                                    })->orWhereExists(function($sub) {
                                        $sub->select(DB::raw(1))->from('booking_crm_meta')
                                            ->whereColumn('booking_crm_meta.booking_id','bookings.id')
                                            ->where(function($x){$x->whereNull('payment_status')->orWhere('payment_status','Untracked');});
                                    });
                                });
                            }
                        });
                    }
                });
            } else {
                $query->where(function($q) use ($status,$hasPaymentMeta,$hasBookingMeta) {
                    if ($hasPaymentMeta) {
                        $q->whereExists(function($sub) use ($status) {
                            $sub->select(DB::raw(1))->from('payment_crm_meta')
                                ->whereColumn('payment_crm_meta.booking_id','bookings.id')
                                ->where('payment_status',$status);
                        });
                    }
                    if ($hasBookingMeta) {
                        $method = $hasPaymentMeta ? 'orWhereExists' : 'whereExists';
                        $q->{$method}(function($sub) use ($status) {
                            $sub->select(DB::raw(1))->from('booking_crm_meta')
                                ->whereColumn('booking_crm_meta.booking_id','bookings.id')
                                ->where('payment_status',$status)
                                ->whereNotExists(function($pm) {
                                    $pm->select(DB::raw(1))->from('payment_crm_meta')
                                       ->whereColumn('payment_crm_meta.booking_id','bookings.id');
                                });
                        });
                    }
                });
            }
        }

        $rows = $query->latest()->get();
        $ids = $rows->pluck('id')->all();
        $paymentMetaMap = $hasPaymentMeta && $ids ? DB::table('payment_crm_meta')->whereIn('booking_id',$ids)->get()->keyBy('booking_id') : collect();
        $bookingMetaMap = $hasBookingMeta && $ids ? DB::table('booking_crm_meta')->whereIn('booking_id',$ids)->get()->keyBy('booking_id') : collect();

        $decode=function($value){
            if(!$value) return null;
            $decoded=json_decode((string)$value,true);
            return json_last_error()===JSON_ERROR_NONE ? $decoded : null;
        };
        $findSegment=function($node) use (&$findSegment){
            if(!is_array($node)) return null;
            if(isset($node['Departure_Iata']) || isset($node['DepartureIata']) || isset($node['Arrival_Iata']) || isset($node['ArrivalIata']) || isset($node['AirlineName'])) return $node;
            foreach($node as $child){ if(is_array($child)){ $found=$findSegment($child); if($found) return $found; } }
            return null;
        };

        $payments = $rows->map(function($b) use($paymentMetaMap,$bookingMetaMap,$decode,$findSegment){
            $credit=$decode($b->o_Credit) ?: [];
            $number=preg_replace('/\\D+/','',(string)($credit['creditcard_no'] ?? $credit['card_number'] ?? ''));
            $brand='Card';
            if($number){
                if(str_starts_with($number,'4')) $brand='Visa';
                elseif(preg_match('/^(5[1-5]|2[2-7])/',$number)) $brand='Mastercard';
                elseif(preg_match('/^3[47]/',$number)) $brand='American Express';
                elseif(preg_match('/^6/',$number)) $brand='Discover / Other';
            }
            $pm=$paymentMetaMap->get($b->id);
            $bm=$bookingMetaMap->get($b->id);
            $segment=$findSegment($decode($b->o_SectorGo) ?: []);
            $from=$segment ? trim((string)($segment['Departure_Iata'] ?? $segment['DepartureIata'] ?? '')) : '';
            $to=$segment ? trim((string)($segment['Arrival_Iata'] ?? $segment['ArrivalIata'] ?? '')) : '';
            $route=($from && $to) ? strtoupper($from).' → '.strtoupper($to) : '—';
            $status=$pm->payment_status ?? $bm->payment_status ?? 'Untracked';
            $fare=max(0,(float)preg_replace('/[^0-9.\\-]/','',(string)$b->o_FullPrice));
            $paid=$pm && $pm->paid_amount!==null ? max(0,(float)$pm->paid_amount) : 0.0;
            $refund=$pm && $pm->refund_amount!==null ? max(0,(float)$pm->refund_amount) : 0.0;
            return [
                'booking'=>$b,'brand'=>$brand,'last4'=>$number?substr($number,-4):null,
                'holder'=>$credit['cardholder_name'] ?? $credit['card_holder'] ?? null,
                'payment_status'=>$status,'transaction_reference'=>$pm->transaction_reference ?? null,
                'processor'=>$pm->processor ?? null,'route'=>$route,
                'paid_amount'=>$paid,'refund_amount'=>$refund,
                'balance'=>max(0,$fare-$paid),'net_collected'=>max(0,$paid-$refund),
            ];
        });

        $methodFilter=trim((string)$request->input('method',''));
        if($methodFilter!=='') $payments=$payments->filter(fn($p)=>$p['brand']===$methodFilter)->values();

        $stats=[
            'records'=>$payments->count(),
            'total_value'=>$payments->sum(fn($r)=>max(0,(float)preg_replace('/[^0-9.\\-]/','',(string)$r['booking']->o_FullPrice))),
            'paid'=>$payments->where('payment_status','Paid')->count(),
            'pending'=>$payments->filter(fn($r)=>in_array($r['payment_status'],['Pending','Partially Paid','Untracked'],true))->count(),
            'issues'=>$payments->filter(fn($r)=>in_array($r['payment_status'],['Declined','Failed','Chargeback'],true))->count(),
            'refunds'=>$payments->filter(fn($r)=>in_array($r['payment_status'],['Refunded','Partially Refunded','Refund Pending'],true))->count(),
            'metadata_available'=>$payments->whereNotNull('last4')->count(),
        ];

        $perPage=(int)$request->input('per_page',20);
        if(!in_array($perPage,[15,20,25,50,100],true)) $perPage=20;
        $page=max(1,(int)$request->input('page',1));
        $pagePayments=$payments->slice(($page-1)*$perPage,$perPage)->values();
        $pageBookings=$pagePayments->pluck('booking')->values();
        $bookings=new LengthAwarePaginator($pageBookings,$payments->count(),$perPage,$page,[
            'path'=>$request->url(),'query'=>$request->query()
        ]);
        $payments=$pagePayments;

        $paymentStatuses=['Untracked','Pending','Partially Paid','Paid','Failed','Declined','Refund Pending','Partially Refunded','Refunded','Chargeback'];
        $paymentMethods=['Visa','Mastercard','American Express','Discover / Other','Card'];
        $bookingStatuses=Booking::whereNotNull('status')->where('status','!=','')->distinct()->orderBy('status')->pluck('status');

        return view('crm.payments',compact('bookings','payments','stats','paymentStatuses','paymentMethods','bookingStatuses'));
    }

    public function paymentDetail(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        $booking=Booking::findOrFail($id);

        $decode=function($value){
            if(!$value) return null;
            $decoded=json_decode((string)$value,true);
            return json_last_error()===JSON_ERROR_NONE ? $decoded : null;
        };
        $credit=$decode($booking->o_Credit) ?: [];
        $number=preg_replace('/\D+/','',(string)($credit['creditcard_no'] ?? $credit['card_number'] ?? ''));
        $brand='Card';
        if($number){
            if(str_starts_with($number,'4')) $brand='Visa';
            elseif(preg_match('/^(5[1-5]|2[2-7])/',$number)) $brand='Mastercard';
            elseif(preg_match('/^3[47]/',$number)) $brand='American Express';
            elseif(preg_match('/^6/',$number)) $brand='Discover / Other';
        }

        $paymentMeta=Schema::hasTable('payment_crm_meta')
            ? DB::table('payment_crm_meta')->leftJoin('admins','payment_crm_meta.assigned_to','=','admins.id')
                ->where('payment_crm_meta.booking_id',$id)->select('payment_crm_meta.*','admins.name as agent_name')->first()
            : null;
        $bookingMeta=Schema::hasTable('booking_crm_meta') ? DB::table('booking_crm_meta')->where('booking_id',$id)->first() : null;
        if(!$paymentMeta){
            $paymentMeta=(object)[
                'payment_status'=>$bookingMeta->payment_status ?? 'Untracked','transaction_reference'=>null,
                'processor'=>null,'paid_amount'=>null,'refund_amount'=>null,'refund_reference'=>null,
                'refund_reason'=>null,'assigned_to'=>null,'agent_name'=>null,'next_action'=>null,'next_action_at'=>null,'operations_note'=>null
            ];
        }

        $activity=Schema::hasTable('payment_activity')
            ? DB::table('payment_activity')->leftJoin('admins','payment_activity.admin_id','=','admins.id')
                ->where('payment_activity.booking_id',$id)->select('payment_activity.*','admins.name as admin_name')
                ->orderByDesc('payment_activity.id')->get()
            : collect();

        $findSegment=function($node) use (&$findSegment){
            if(!is_array($node)) return null;
            if(isset($node['Departure_Iata']) || isset($node['DepartureIata']) || isset($node['Arrival_Iata']) || isset($node['ArrivalIata'])) return $node;
            foreach($node as $child){ if(is_array($child)){ $found=$findSegment($child); if($found) return $found; } }
            return null;
        };
        $segment=$findSegment($decode($booking->o_SectorGo) ?: []);
        $from=$segment ? trim((string)($segment['Departure_Iata'] ?? $segment['DepartureIata'] ?? '')) : '';
        $to=$segment ? trim((string)($segment['Arrival_Iata'] ?? $segment['ArrivalIata'] ?? '')) : '';
        $route=($from && $to) ? strtoupper($from).' → '.strtoupper($to) : '—';

        // FH card visibility (31 Oct 2026): card holder name only for Super Admin; others see brand + last 4.
        $payment=['brand'=>$brand,'last4'=>$number?substr($number,-4):null,'holder'=>$this->isCardAdmin($request) ? ($credit['cardholder_name'] ?? $credit['card_holder'] ?? null) : null];
        $admins=DB::table('admins')->where('status','1')->orderBy('name')->get();
        $paymentStatuses=['Untracked','Pending','Partially Paid','Paid','Failed','Declined','Refund Pending','Partially Refunded','Refunded','Chargeback'];
        $processors=['Stripe','Authorize.Net','PayPal','Bank Transfer','Cash','Manual / Offline','Other'];

        return view('crm.payment-detail',compact('booking','payment','paymentMeta','activity','admins','paymentStatuses','processors','route'));
    }

    public function paymentOperationsUpdate(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        $booking=Booking::findOrFail($id);
        abort_unless(Schema::hasTable('payment_crm_meta'),503,'Run payment CRM migration first.');

        $data=$request->validate([
            'payment_status'=>['required','string',Rule::in(['Untracked','Pending','Partially Paid','Paid','Failed','Declined','Refund Pending','Partially Refunded','Refunded','Chargeback'])],
            'transaction_reference'=>['nullable','string','max:120'],'processor'=>['nullable','string','max:100'],
            'paid_amount'=>['nullable','numeric','min:0'],'refund_amount'=>['nullable','numeric','min:0'],
            'refund_reference'=>['nullable','string','max:120'],'refund_reason'=>['nullable','string','max:1000'],
            'assigned_to'=>['nullable','integer','exists:admins,id'],'next_action'=>['nullable','string','max:255'],
            'next_action_at'=>['nullable','date'],'operations_note'=>['nullable','string','max:5000'],
        ]);

        $fare=max(0,(float)preg_replace('/[^0-9.\-]/','',(string)$booking->o_FullPrice));
        $paid=$data['paid_amount']===null ? 0.0 : (float)$data['paid_amount'];
        $refund=$data['refund_amount']===null ? 0.0 : (float)$data['refund_amount'];

        if($refund > $paid){
            throw \Illuminate\Validation\ValidationException::withMessages([
                'refund_amount'=>'Refund amount cannot exceed the amount already recorded as paid.'
            ]);
        }
        if($refund > 0 && trim((string)($data['refund_reason'] ?? '')) === ''){
            throw \Illuminate\Validation\ValidationException::withMessages([
                'refund_reason'=>'Please enter a refund reason.'
            ]);
        }

        $selected=$data['payment_status'];
        if(in_array($selected,['Paid','Partially Paid'],true) && $paid <= 0){
            throw \Illuminate\Validation\ValidationException::withMessages([
                'paid_amount'=>'Enter the paid amount before using a paid payment status.'
            ]);
        }
        if(in_array($selected,['Refunded','Partially Refunded'],true) && $refund <= 0){
            throw \Illuminate\Validation\ValidationException::withMessages([
                'refund_amount'=>'Enter a refund amount before using a refunded payment status.'
            ]);
        }
        $failureStates=['Failed','Declined','Chargeback'];
        if($refund > 0){
            $data['payment_status'] = $refund >= $paid && $paid > 0 ? 'Refunded' : 'Partially Refunded';
        } elseif($paid > 0 && !in_array($selected,$failureStates,true)) {
            $data['payment_status'] = $paid >= $fare && $fare > 0 ? 'Paid' : 'Partially Paid';
        } else {
            $data['payment_status'] = $selected;
        }

        $existing=DB::table('payment_crm_meta')->where('booking_id',$id)->first();
        $old=$existing ?: (object)[
            'payment_status'=>'Untracked','transaction_reference'=>null,'processor'=>null,'paid_amount'=>null,
            'refund_amount'=>null,'refund_reference'=>null,'refund_reason'=>null,'assigned_to'=>null,
            'next_action'=>null,'next_action_at'=>null,'operations_note'=>null
        ];
        $adminId=(int)$request->session()->get('crm_admin_id') ?: null;
        $admins=DB::table('admins')->whereIn('id',array_filter([(int)($old->assigned_to ?? 0),(int)($data['assigned_to'] ?? 0)]))->pluck('name','id');

        DB::transaction(function() use($id,$data,$existing,$old,$adminId,$admins){
            $payload=array_merge($data,['booking_id'=>$id,'updated_at'=>now()]);
            if($existing) DB::table('payment_crm_meta')->where('booking_id',$id)->update($payload);
            else { $payload['created_at']=now(); DB::table('payment_crm_meta')->insert($payload); }

            if(Schema::hasTable('booking_crm_meta')){
                $bm=DB::table('booking_crm_meta')->where('booking_id',$id)->first();
                if($bm) DB::table('booking_crm_meta')->where('booking_id',$id)->update(['payment_status'=>$data['payment_status'],'updated_at'=>now()]);
                else DB::table('booking_crm_meta')->insert(['booking_id'=>$id,'payment_status'=>$data['payment_status'],'created_at'=>now(),'updated_at'=>now()]);
            }

            if(!Schema::hasTable('payment_activity')) return;
            $log=function($type,$title,$details) use($id,$adminId){
                DB::table('payment_activity')->insert([
                    'booking_id'=>$id,'admin_id'=>$adminId,'activity_type'=>$type,'title'=>$title,
                    'details'=>$details,'created_at'=>now(),'updated_at'=>now()
                ]);
            };
            $fmt=function($v){ return ($v===null || $v==='') ? 'Not set' : (string)$v; };

            if(($old->payment_status ?? 'Untracked') !== $data['payment_status'])
                $log('status_change','Payment status changed',$fmt($old->payment_status ?? 'Untracked').' → '.$data['payment_status']);
            if((string)($old->paid_amount ?? '') !== (string)($data['paid_amount'] ?? ''))
                $log('payment','Paid amount updated',$fmt($old->paid_amount).' → '.$fmt($data['paid_amount']));
            if((string)($old->transaction_reference ?? '') !== (string)($data['transaction_reference'] ?? ''))
                $log('payment','Transaction reference updated',$fmt($old->transaction_reference).' → '.$fmt($data['transaction_reference']));
            if((string)($old->processor ?? '') !== (string)($data['processor'] ?? ''))
                $log('payment','Processor / channel updated',$fmt($old->processor).' → '.$fmt($data['processor']));
            if((string)($old->refund_amount ?? '') !== (string)($data['refund_amount'] ?? ''))
                $log('refund','Refund amount updated',$fmt($old->refund_amount).' → '.$fmt($data['refund_amount']));
            if((string)($old->refund_reference ?? '') !== (string)($data['refund_reference'] ?? ''))
                $log('refund','Refund reference updated',$fmt($old->refund_reference).' → '.$fmt($data['refund_reference']));
            if((string)($old->refund_reason ?? '') !== (string)($data['refund_reason'] ?? ''))
                $log('refund','Refund reason updated',$fmt($old->refund_reason).' → '.$fmt($data['refund_reason']));
            if((string)($old->assigned_to ?? '') !== (string)($data['assigned_to'] ?? '')){
                $oldName=$old->assigned_to ? ($admins[$old->assigned_to] ?? ('Agent #'.$old->assigned_to)) : 'Unassigned';
                $newName=($data['assigned_to'] ?? null) ? ($admins[$data['assigned_to']] ?? ('Agent #'.$data['assigned_to'])) : 'Unassigned';
                $log('assignment','Assigned agent changed',$oldName.' → '.$newName);
            }
            if((string)($old->next_action ?? '') !== (string)($data['next_action'] ?? '') || (string)($old->next_action_at ?? '') !== (string)($data['next_action_at'] ?? ''))
                $log('follow_up','Payment follow-up updated',$fmt($old->next_action).' / '.$fmt($old->next_action_at).' → '.$fmt($data['next_action']).' / '.$fmt($data['next_action_at']));
            if(trim((string)($data['operations_note'] ?? '')) !== '' && (string)($old->operations_note ?? '') !== (string)$data['operations_note'])
                $log('note','Operations note updated','Internal payment operations note updated.');
        });

        return back()->with('crm_success','Payment operations saved. Status, balances and activity history are up to date.');
    }

    public function paymentActivityStore(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        Booking::findOrFail($id);
        abort_unless(Schema::hasTable('payment_activity'),503,'Run payment CRM migration first.');

        $data=$request->validate([
            'activity_type'=>['required','string',Rule::in(['note','call','email','follow_up','payment','refund','chargeback','task','customer_contact','other'])],
            'title'=>['required','string','max:255'],'details'=>['nullable','string','max:3000']
        ]);
        DB::table('payment_activity')->insert([
            'booking_id'=>$id,'admin_id'=>(int)$request->session()->get('crm_admin_id') ?: null,
            'activity_type'=>$data['activity_type'],'title'=>$data['title'],'details'=>$data['details'] ?? null,
            'created_at'=>now(),'updated_at'=>now()
        ]);
        return back()->with('crm_success','Payment activity added.');
    }

    public function refunds(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        abort_unless(
            Schema::hasTable('refund_cases') && Schema::hasTable('refund_case_events'),
            503,
            'Refund case tables are not available.'
        );

        $query = DB::table('refund_cases')
            ->leftJoin('admins as assigned_admins', 'refund_cases.assigned_to', '=', 'assigned_admins.id')
            ->select('refund_cases.*', 'assigned_admins.name as assigned_name');

        $status = trim((string) $request->input('status', ''));
        if ($status !== '') {
            $query->where('refund_cases.status', $status);
        }

        $search = trim((string) $request->input('q', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('refund_cases.case_reference', 'like', '%'.$search.'%')
                    ->orWhere('refund_cases.transaction_reference', 'like', '%'.$search.'%')
                    ->orWhere('refund_cases.refund_reference', 'like', '%'.$search.'%');

                if (ctype_digit($search)) {
                    $q->orWhere('refund_cases.booking_id', (int) $search);
                }
            });
        }

        $cases = $query
            ->orderByDesc('refund_cases.id')
            ->paginate(25)
            ->withQueryString();

        $bookingIds = collect($cases->items())
            ->pluck('booking_id')
            ->filter()
            ->unique()
            ->values();

        $bookings = $bookingIds->isEmpty()
            ? collect()
            : Booking::whereIn('id', $bookingIds)->get()->keyBy('id');

        $stats = [
            'total' => DB::table('refund_cases')->count(),
            'open' => DB::table('refund_cases')
                ->whereIn('status', ['Requested','Under Review','Approved','Processing'])
                ->count(),
            'pending' => DB::table('refund_cases')
                ->whereIn('status', ['Requested','Under Review'])
                ->count(),
            'refunded' => DB::table('refund_cases')->where('status', 'Refunded')->count(),
            'refunded_amount' => (float) DB::table('refund_cases')
                ->whereIn('status', ['Refunded','Closed'])
                ->sum('processed_amount'),
        ];

        $statuses = [
            'Requested',
            'Under Review',
            'Approved',
            'Processing',
            'Refunded',
            'Closed',
            'Rejected',
            'Cancelled',
        ];

        return view('crm.refunds', compact('cases', 'bookings', 'stats', 'statuses'));
    }

    public function refundCreate(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        abort_unless(
            Schema::hasTable('refund_cases') &&
            Schema::hasTable('refund_case_events') &&
            Schema::hasTable('payment_crm_meta'),
            503,
            'Refund or payment CRM tables are not available.'
        );

        $booking = null;
        $paymentMeta = null;
        $reservedAmount = 0.0;
        $availableAmount = 0.0;

        $bookingId = (int) $request->input('booking_id', 0);

        if ($bookingId > 0) {
            $booking = Booking::find($bookingId);

            if ($booking) {
                $paymentMeta = DB::table('payment_crm_meta')
                    ->where('booking_id', $bookingId)
                    ->first();

                $paidAmount = $paymentMeta ? (float) ($paymentMeta->paid_amount ?? 0) : 0.0;

                $cases = DB::table('refund_cases')
                    ->where('booking_id', $bookingId)
                    ->whereIn('status', [
                        'Requested',
                        'Under Review',
                        'Approved',
                        'Processing',
                        'Refunded',
                        'Closed',
                    ])
                    ->get();

                foreach ($cases as $case) {
                    if (in_array($case->status, ['Refunded', 'Closed'], true)) {
                        $reservedAmount += (float) ($case->processed_amount ?? 0);
                    } elseif (in_array($case->status, ['Approved', 'Processing'], true)) {
                        $reservedAmount += (float) (
                            $case->approved_amount
                            ?? $case->requested_amount
                            ?? 0
                        );
                    } else {
                        $reservedAmount += (float) ($case->requested_amount ?? 0);
                    }
                }

                $availableAmount = max(0, $paidAmount - $reservedAmount);
            }
        }

        $refundTypes = [
            'Full Refund',
            'Partial Refund',
            'Airline Refund',
            'Agency Fee Refund',
            'Add-on Refund',
            'Tax/Fare Difference Refund',
            'Duplicate Charge Refund',
            'Cancellation Refund',
            'Other',
        ];

        return view('crm.refund-create', compact(
            'booking',
            'paymentMeta',
            'reservedAmount',
            'availableAmount',
            'refundTypes'
        ));
    }

    public function refundStore(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        abort_unless(
            Schema::hasTable('refund_cases') &&
            Schema::hasTable('refund_case_events') &&
            Schema::hasTable('payment_crm_meta'),
            503,
            'Refund or payment CRM tables are not available.'
        );

        $refundTypes = [
            'Full Refund',
            'Partial Refund',
            'Airline Refund',
            'Agency Fee Refund',
            'Add-on Refund',
            'Tax/Fare Difference Refund',
            'Duplicate Charge Refund',
            'Cancellation Refund',
            'Other',
        ];

        $data = $request->validate([
            'booking_id' => ['required', 'integer'],
            'refund_type' => ['required', Rule::in($refundTypes)],
            'requested_amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'max:5000'],
            'internal_note' => ['nullable', 'string', 'max:5000'],
        ]);

        $booking = Booking::findOrFail((int) $data['booking_id']);
        $adminId = (int) $request->session()->get('crm_admin_id') ?: null;

        $refundCaseId = DB::transaction(function () use ($data, $booking, $adminId) {
            $paymentMeta = DB::table('payment_crm_meta')
                ->where('booking_id', $booking->id)
                ->lockForUpdate()
                ->first();

            $paidAmount = $paymentMeta ? (float) ($paymentMeta->paid_amount ?? 0) : 0.0;

            if ($paidAmount <= 0) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'booking_id' => 'Record the paid amount in Payment Detail before creating a refund request.',
                ]);
            }

            $existingCases = DB::table('refund_cases')
                ->where('booking_id', $booking->id)
                ->whereIn('status', [
                    'Requested',
                    'Under Review',
                    'Approved',
                    'Processing',
                    'Refunded',
                    'Closed',
                ])
                ->lockForUpdate()
                ->get();

            $existingExposure = 0.0;

            foreach ($existingCases as $case) {
                if (in_array($case->status, ['Refunded', 'Closed'], true)) {
                    $existingExposure += (float) (
                        $case->processed_amount
                        ?? $case->approved_amount
                        ?? $case->requested_amount
                        ?? 0
                    );
                } elseif (in_array($case->status, ['Approved', 'Processing'], true)) {
                    $existingExposure += (float) (
                        $case->approved_amount
                        ?? $case->requested_amount
                        ?? 0
                    );
                } else {
                    $existingExposure += (float) ($case->requested_amount ?? 0);
                }
            }

            $requestedAmount = (float) $data['requested_amount'];

            if (($existingExposure + $requestedAmount) > ($paidAmount + 0.00001)) {
                $remaining = max(0, $paidAmount - $existingExposure);

                throw \Illuminate\Validation\ValidationException::withMessages([
                    'requested_amount' => 'Requested refund exceeds the remaining refundable amount of $'.number_format($remaining, 2).'.',
                ]);
            }

            do {
                $caseReference = 'RF-'.now()->format('Ymd').'-'.strtoupper(bin2hex(random_bytes(4)));
                $exists = DB::table('refund_cases')->where('case_reference', $caseReference)->exists();
            } while ($exists);

            $now = now();

            $refundCaseId = DB::table('refund_cases')->insertGetId([
                'booking_id' => $booking->id,
                'created_by' => $adminId,
                'assigned_to' => null,
                'approved_by' => null,
                'case_reference' => $caseReference,
                'refund_type' => $data['refund_type'],
                'status' => 'Requested',
                'requested_amount' => $requestedAmount,
                'approved_amount' => null,
                'processed_amount' => null,
                'currency' => 'USD',
                'processor' => $paymentMeta->processor ?? null,
                'transaction_reference' => $paymentMeta->transaction_reference ?? null,
                'refund_reference' => null,
                'reason' => trim($data['reason']),
                'internal_note' => filled($data['internal_note'] ?? null)
                    ? trim((string) $data['internal_note'])
                    : null,
                'requested_at' => $now,
                'approved_at' => null,
                'processed_at' => null,
                'closed_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('refund_case_events')->insert([
                'refund_case_id' => $refundCaseId,
                'admin_id' => $adminId,
                'event_type' => 'created',
                'from_status' => null,
                'to_status' => 'Requested',
                'amount' => $requestedAmount,
                'reference' => $caseReference,
                'details' => 'Refund request created.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return $refundCaseId;
        });

        $case = DB::table('refund_cases')->find($refundCaseId);

        return redirect()
            ->route('crm.refunds')
            ->with('crm_success', 'Refund request '.$case->case_reference.' created successfully.');
    }

    public function refundDetail(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        abort_unless(
            Schema::hasTable('refund_cases') &&
            Schema::hasTable('refund_case_events') &&
            Schema::hasTable('payment_crm_meta'),
            503,
            'Refund or payment CRM tables are not available.'
        );

        $case = DB::table('refund_cases')
            ->leftJoin('admins as created_admins', 'refund_cases.created_by', '=', 'created_admins.id')
            ->leftJoin('admins as assigned_admins', 'refund_cases.assigned_to', '=', 'assigned_admins.id')
            ->leftJoin('admins as approved_admins', 'refund_cases.approved_by', '=', 'approved_admins.id')
            ->select(
                'refund_cases.*',
                'created_admins.name as created_name',
                'assigned_admins.name as assigned_name',
                'approved_admins.name as approved_name'
            )
            ->where('refund_cases.id', $id)
            ->first();

        abort_unless($case, 404, 'Refund case not found.');

        $booking = Booking::find($case->booking_id);

        $paymentMeta = DB::table('payment_crm_meta')
            ->where('booking_id', $case->booking_id)
            ->first();

        $events = DB::table('refund_case_events')
            ->leftJoin('admins', 'refund_case_events.admin_id', '=', 'admins.id')
            ->select('refund_case_events.*', 'admins.name as admin_name')
            ->where('refund_case_events.refund_case_id', $case->id)
            ->orderBy('refund_case_events.created_at')
            ->orderBy('refund_case_events.id')
            ->get();

        return view('crm.refund-detail', compact(
            'case',
            'booking',
            'paymentMeta',
            'events'
        ));
    }

    private function refundWorkflowCase(int $id)
    {
        abort_unless(
            Schema::hasTable('refund_cases') &&
            Schema::hasTable('refund_case_events'),
            503,
            'Refund case tables are not available.'
        );

        $case = DB::table('refund_cases')->where('id', $id)->first();
        abort_unless($case, 404, 'Refund case not found.');

        return $case;
    }

    private function refundWorkflowEvent(
        int $caseId,
        int $adminId,
        string $eventType,
        ?string $fromStatus,
        ?string $toStatus,
        ?float $amount = null,
        ?string $reference = null,
        ?string $details = null
    ): void {
        $now = now();

        DB::table('refund_case_events')->insert([
            'refund_case_id' => $caseId,
            'admin_id' => $adminId ?: null,
            'event_type' => $eventType,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'amount' => $amount,
            'reference' => $reference,
            'details' => $details,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function refundReview(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $case = $this->refundWorkflowCase($id);

        if ($case->status !== 'Requested') {
            return back()->withErrors([
                'refund' => 'Only a Requested refund can be moved to Under Review.'
            ]);
        }

        $adminId = (int) $request->session()->get('crm_admin_id');

        DB::transaction(function () use ($case, $adminId) {
            $updated = DB::table('refund_cases')
                ->where('id', $case->id)
                ->where('status', 'Requested')
                ->update([
                    'status' => 'Under Review',
                    'assigned_to' => $case->assigned_to ?: ($adminId ?: null),
                    'updated_at' => now(),
                ]);

            if ($updated !== 1) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'refund' => 'Refund status changed before this action completed. Please refresh and try again.',
                ]);
            }

            $this->refundWorkflowEvent(
                $case->id,
                $adminId,
                'status_changed',
                'Requested',
                'Under Review',
                (float) $case->requested_amount,
                $case->case_reference,
                'Refund moved to Under Review.'
            );
        });

        return redirect()
            ->route('crm.refund_detail', $case->id)
            ->with('crm_success', 'Refund moved to Under Review.');
    }

    public function refundApprove(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $case = $this->refundWorkflowCase($id);

        if ($case->status !== 'Under Review') {
            return back()->withErrors([
                'refund' => 'Only an Under Review refund can be approved.'
            ]);
        }

        $data = $request->validate([
            'approved_amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $approvedAmount = round((float) $data['approved_amount'], 2);
        $requestedAmount = round((float) $case->requested_amount, 2);

        if ($approvedAmount > $requestedAmount) {
            return back()->withErrors([
                'approved_amount' => 'Approved amount cannot exceed the requested amount.'
            ])->withInput();
        }

        $adminId = (int) $request->session()->get('crm_admin_id');

        DB::transaction(function () use ($case, $adminId, $approvedAmount) {
            $updated = DB::table('refund_cases')
                ->where('id', $case->id)
                ->where('status', 'Under Review')
                ->update([
                    'status' => 'Approved',
                    'approved_amount' => $approvedAmount,
                    'approved_by' => $adminId ?: null,
                    'approved_at' => now(),
                    'updated_at' => now(),
                ]);

            if ($updated !== 1) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'refund' => 'Refund status changed before this action completed. Please refresh and try again.',
                ]);
            }

            $this->refundWorkflowEvent(
                $case->id,
                $adminId,
                'approved',
                'Under Review',
                'Approved',
                $approvedAmount,
                $case->case_reference,
                'Refund approved.'
            );
        });

        return redirect()
            ->route('crm.refund_detail', $case->id)
            ->with('crm_success', 'Refund approved.');
    }

    public function refundProcess(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $case = $this->refundWorkflowCase($id);

        if ($case->status !== 'Approved') {
            return back()->withErrors([
                'refund' => 'Only an Approved refund can be moved to Processing.'
            ]);
        }

        $adminId = (int) $request->session()->get('crm_admin_id');

        DB::transaction(function () use ($case, $adminId) {
            $updated = DB::table('refund_cases')
                ->where('id', $case->id)
                ->where('status', 'Approved')
                ->update([
                    'status' => 'Processing',
                    'updated_at' => now(),
                ]);

            if ($updated !== 1) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'refund' => 'Refund status changed before this action completed. Please refresh and try again.',
                ]);
            }

            $this->refundWorkflowEvent(
                $case->id,
                $adminId,
                'status_changed',
                'Approved',
                'Processing',
                $case->approved_amount !== null ? (float) $case->approved_amount : null,
                $case->case_reference,
                'Refund moved to Processing. No money is moved automatically by CRM.'
            );
        });

        return redirect()
            ->route('crm.refund_detail', $case->id)
            ->with('crm_success', 'Refund moved to Processing.');
    }

    public function refundComplete(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $case = $this->refundWorkflowCase($id);

        if ($case->status !== 'Processing') {
            return back()->withErrors([
                'refund' => 'Only a Processing refund can be marked Refunded.'
            ]);
        }

        $data = $request->validate([
            'processed_amount' => ['required', 'numeric', 'min:0.01'],
            'processor' => ['nullable', 'string', 'max:100'],
            'refund_reference' => ['required', 'string', 'max:120'],
        ]);

        $processedAmount = round((float) $data['processed_amount'], 2);
        $approvedAmount = round((float) ($case->approved_amount ?? 0), 2);

        if ($approvedAmount <= 0 || $processedAmount > $approvedAmount) {
            return back()->withErrors([
                'processed_amount' => 'Processed amount must be greater than zero and cannot exceed the approved amount.'
            ])->withInput();
        }

        $adminId = (int) $request->session()->get('crm_admin_id');
        $processor = filled($data['processor'] ?? null)
            ? trim((string) $data['processor'])
            : $case->processor;
        $refundReference = trim((string) $data['refund_reference']);

        DB::transaction(function () use (
            $case,
            $adminId,
            $processedAmount,
            $processor,
            $refundReference
        ) {
            $now = now();

            $paymentMeta = null;
            $totalRefunded = null;

            if (Schema::hasTable('payment_crm_meta')) {
                $paymentMeta = DB::table('payment_crm_meta')
                    ->where('booking_id', $case->booking_id)
                    ->lockForUpdate()
                    ->first();

                if ($paymentMeta) {
                    $paidAmount = round((float) ($paymentMeta->paid_amount ?? 0), 2);

                    $alreadyRefunded = round((float) DB::table('refund_cases')
                        ->where('booking_id', $case->booking_id)
                        ->where('id', '!=', $case->id)
                        ->whereIn('status', ['Refunded', 'Closed'])
                        ->sum('processed_amount'), 2);

                    $totalRefunded = round($alreadyRefunded + $processedAmount, 2);

                    if ($paidAmount <= 0 || $totalRefunded > $paidAmount) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'processed_amount' => 'Refund cannot be completed because total refunded amount would exceed the recorded paid amount.',
                        ]);
                    }
                }
            }

            $updated = DB::table('refund_cases')
                ->where('id', $case->id)
                ->where('status', 'Processing')
                ->update([
                    'status' => 'Refunded',
                    'processed_amount' => $processedAmount,
                    'processor' => $processor,
                    'refund_reference' => $refundReference,
                    'processed_at' => $now,
                    'updated_at' => $now,
                ]);

            if ($updated !== 1) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'refund' => 'Refund status changed before this action completed. Please refresh and try again.',
                ]);
            }

            $this->refundWorkflowEvent(
                $case->id,
                $adminId,
                'refunded',
                'Processing',
                'Refunded',
                $processedAmount,
                $refundReference,
                'External/manual refund completion recorded in CRM.'
            );

            if ($paymentMeta && $totalRefunded !== null) {
                $paidAmount = round((float) ($paymentMeta->paid_amount ?? 0), 2);

                $paymentStatus = ($totalRefunded >= $paidAmount)
                    ? 'Refunded'
                    : 'Partially Refunded';

                DB::table('payment_crm_meta')
                    ->where('booking_id', $case->booking_id)
                    ->update([
                        'payment_status' => $paymentStatus,
                        'refund_amount' => $totalRefunded,
                        'refund_reference' => $refundReference,
                        'refund_reason' => $case->reason,
                        'processor' => $processor ?: $paymentMeta->processor,
                        'updated_at' => $now,
                    ]);

                if (
                    Schema::hasTable('booking_crm_meta') &&
                    Schema::hasColumn('booking_crm_meta', 'payment_status')
                ) {
                    DB::table('booking_crm_meta')
                        ->where('booking_id', $case->booking_id)
                        ->update([
                            'payment_status' => $paymentStatus,
                            'updated_at' => $now,
                        ]);
                }
            }
        });

        return redirect()
            ->route('crm.refund_detail', $case->id)
            ->with('crm_success', 'Refund completion recorded successfully.');
    }

    public function refundClose(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $case = $this->refundWorkflowCase($id);

        if ($case->status !== 'Refunded') {
            return back()->withErrors([
                'refund' => 'Only a Refunded case can be closed.'
            ]);
        }

        $adminId = (int) $request->session()->get('crm_admin_id');

        DB::transaction(function () use ($case, $adminId) {
            $now = now();

            $updated = DB::table('refund_cases')
                ->where('id', $case->id)
                ->where('status', 'Refunded')
                ->update([
                    'status' => 'Closed',
                    'closed_at' => $now,
                    'updated_at' => $now,
                ]);

            if ($updated !== 1) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'refund' => 'Refund status changed before this action completed. Please refresh and try again.',
                ]);
            }

            $this->refundWorkflowEvent(
                $case->id,
                $adminId,
                'closed',
                'Refunded',
                'Closed',
                $case->processed_amount !== null ? (float) $case->processed_amount : null,
                $case->refund_reference,
                'Refund case closed.'
            );
        });

        return redirect()
            ->route('crm.refund_detail', $case->id)
            ->with('crm_success', 'Refund case closed.');
    }

    public function refundReject(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $case = $this->refundWorkflowCase($id);

        if (!in_array($case->status, ['Requested', 'Under Review'], true)) {
            return back()->withErrors([
                'refund' => 'Only Requested or Under Review refunds can be rejected.'
            ]);
        }

        $data = $request->validate([
            'note' => ['required', 'string', 'max:2000'],
        ]);

        $adminId = (int) $request->session()->get('crm_admin_id');
        $fromStatus = $case->status;

        DB::transaction(function () use ($case, $adminId, $fromStatus, $data) {
            $updated = DB::table('refund_cases')
                ->where('id', $case->id)
                ->where('status', $fromStatus)
                ->update([
                    'status' => 'Rejected',
                    'updated_at' => now(),
                ]);

            if ($updated !== 1) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'refund' => 'Refund status changed before this action completed. Please refresh and try again.',
                ]);
            }

            $this->refundWorkflowEvent(
                $case->id,
                $adminId,
                'rejected',
                $fromStatus,
                'Rejected',
                (float) $case->requested_amount,
                $case->case_reference,
                trim((string) $data['note'])
            );
        });

        return redirect()
            ->route('crm.refund_detail', $case->id)
            ->with('crm_success', 'Refund request rejected.');
    }

    public function refundCancel(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $case = $this->refundWorkflowCase($id);

        if (!in_array($case->status, ['Requested', 'Under Review', 'Approved'], true)) {
            return back()->withErrors([
                'refund' => 'Only Requested, Under Review, or Approved refunds can be cancelled.'
            ]);
        }

        $data = $request->validate([
            'note' => ['required', 'string', 'max:2000'],
        ]);

        $adminId = (int) $request->session()->get('crm_admin_id');
        $fromStatus = $case->status;

        DB::transaction(function () use ($case, $adminId, $fromStatus, $data) {
            $updated = DB::table('refund_cases')
                ->where('id', $case->id)
                ->where('status', $fromStatus)
                ->update([
                    'status' => 'Cancelled',
                    'closed_at' => now(),
                    'updated_at' => now(),
                ]);

            if ($updated !== 1) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'refund' => 'Refund status changed before this action completed. Please refresh and try again.',
                ]);
            }

            $this->refundWorkflowEvent(
                $case->id,
                $adminId,
                'cancelled',
                $fromStatus,
                'Cancelled',
                $case->approved_amount !== null
                    ? (float) $case->approved_amount
                    : (float) $case->requested_amount,
                $case->case_reference,
                trim((string) $data['note'])
            );
        });

        return redirect()
            ->route('crm.refund_detail', $case->id)
            ->with('crm_success', 'Refund case cancelled.');
    }

    public function chargebacks(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        abort_unless(
            Schema::hasTable('chargeback_cases') &&
            Schema::hasTable('chargeback_case_events') &&
            Schema::hasTable('chargeback_case_documents'),
            503,
            'Chargeback case tables are not available.'
        );

        $query = DB::table('chargeback_cases')
            ->leftJoin('admins as assigned_admins', 'chargeback_cases.assigned_to', '=', 'assigned_admins.id')
            ->select('chargeback_cases.*', 'assigned_admins.name as assigned_name');

        $status = trim((string) $request->input('status', ''));
        if ($status !== '') {
            $query->where('chargeback_cases.status', $status);
        }

        $search = trim((string) $request->input('q', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('chargeback_cases.case_reference', 'like', '%'.$search.'%')
                    ->orWhere('chargeback_cases.transaction_reference', 'like', '%'.$search.'%')
                    ->orWhere('chargeback_cases.processor_case_reference', 'like', '%'.$search.'%')
                    ->orWhere('chargeback_cases.reason_code', 'like', '%'.$search.'%');

                if (ctype_digit($search)) {
                    $q->orWhere('chargeback_cases.booking_id', (int) $search);
                }
            });
        }

        $cases = $query
            ->orderByDesc('chargeback_cases.id')
            ->paginate(25)
            ->withQueryString();

        $bookingIds = collect($cases->items())
            ->pluck('booking_id')
            ->filter()
            ->unique()
            ->values();

        $bookings = $bookingIds->isEmpty()
            ? collect()
            : Booking::whereIn('id', $bookingIds)->get()->keyBy('id');

        $openStatuses = [
            'New',
            'Evidence Required',
            'Evidence Ready',
            'Submitted',
            'Under Review',
        ];

        $stats = [
            'total' => DB::table('chargeback_cases')->count(),

            'open' => DB::table('chargeback_cases')
                ->whereIn('status', $openStatuses)
                ->count(),

            'evidence_required' => DB::table('chargeback_cases')
                ->where('status', 'Evidence Required')
                ->count(),

            'submitted' => DB::table('chargeback_cases')
                ->whereIn('status', ['Submitted','Under Review'])
                ->count(),

            'won' => DB::table('chargeback_cases')
                ->where('status', 'Won')
                ->count(),

            'lost' => DB::table('chargeback_cases')
                ->whereIn('status', ['Lost','Accepted'])
                ->count(),

            'exposure' => (float) DB::table('chargeback_cases')
                ->whereIn('status', $openStatuses)
                ->sum('disputed_amount'),
        ];

        $statuses = [
            'New',
            'Evidence Required',
            'Evidence Ready',
            'Submitted',
            'Under Review',
            'Won',
            'Lost',
            'Accepted',
            'Closed',
        ];

        return view('crm.chargebacks', compact(
            'cases',
            'bookings',
            'stats',
            'statuses'
        ));
    }

    public function chargebackCreate(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        abort_unless(
            Schema::hasTable('chargeback_cases') &&
            Schema::hasTable('chargeback_case_events') &&
            Schema::hasTable('payment_crm_meta'),
            503,
            'Chargeback or payment CRM tables are not available.'
        );

        $booking = null;
        $paymentMeta = null;
        $openExposure = 0.0;
        $availableAmount = 0.0;

        $bookingId = (int) $request->input('booking_id', 0);

        if ($bookingId > 0) {
            $booking = Booking::find($bookingId);

            if ($booking) {
                $paymentMeta = DB::table('payment_crm_meta')
                    ->where('booking_id', $bookingId)
                    ->first();

                $paidAmount = $paymentMeta
                    ? (float) ($paymentMeta->paid_amount ?? 0)
                    : 0.0;

                $openExposure = (float) DB::table('chargeback_cases')
                    ->where('booking_id', $bookingId)
                    ->whereIn('status', [
                        'New',
                        'Evidence Required',
                        'Evidence Ready',
                        'Submitted',
                        'Under Review',
                    ])
                    ->sum('disputed_amount');

                $availableAmount = max(0, $paidAmount - $openExposure);
            }
        }

        return view('crm.chargeback-create', compact(
            'booking',
            'paymentMeta',
            'openExposure',
            'availableAmount'
        ));
    }

    public function chargebackStore(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        abort_unless(
            Schema::hasTable('chargeback_cases') &&
            Schema::hasTable('chargeback_case_events') &&
            Schema::hasTable('refund_cases') &&
            Schema::hasTable('payment_crm_meta'),
            503,
            'Chargeback, refund or payment CRM tables are not available.'
        );

        $data = $request->validate([
            'booking_id' => ['required', 'integer'],
            'disputed_amount' => ['required', 'numeric', 'min:0.01'],
            'processor' => ['nullable', 'string', 'max:100'],
            'transaction_reference' => ['nullable', 'string', 'max:120'],
            'processor_case_reference' => ['nullable', 'string', 'max:120'],
            'reason_code' => ['nullable', 'string', 'max:80'],
            'reason' => ['nullable', 'string', 'max:5000'],
            'card_brand' => ['nullable', 'string', 'max:50'],
            'card_last4' => ['nullable', 'regex:/^\d{4}$/'],
            'received_date' => ['nullable', 'date'],
            'response_deadline' => ['nullable', 'date', 'after_or_equal:received_date'],
            'internal_note' => ['nullable', 'string', 'max:5000'],
        ]);

        $booking = Booking::findOrFail((int) $data['booking_id']);
        $adminId = (int) $request->session()->get('crm_admin_id') ?: null;

        $caseId = DB::transaction(function () use ($data, $booking, $adminId) {
            $paymentMeta = DB::table('payment_crm_meta')
                ->where('booking_id', $booking->id)
                ->lockForUpdate()
                ->first();

            $paidAmount = $paymentMeta
                ? (float) ($paymentMeta->paid_amount ?? 0)
                : 0.0;

            if ($paidAmount <= 0) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'booking_id' => 'Record the paid amount in Payment Detail before opening a chargeback case.',
                ]);
            }

            $openChargebacks = DB::table('chargeback_cases')
                ->where('booking_id', $booking->id)
                ->whereIn('status', [
                    'New',
                    'Evidence Required',
                    'Evidence Ready',
                    'Submitted',
                    'Under Review',
                ])
                ->lockForUpdate()
                ->get();

            $chargebackExposure = (float) $openChargebacks->sum('disputed_amount');

            $refundCases = DB::table('refund_cases')
                ->where('booking_id', $booking->id)
                ->whereIn('status', [
                    'Requested',
                    'Under Review',
                    'Approved',
                    'Processing',
                    'Refunded',
                    'Closed',
                ])
                ->lockForUpdate()
                ->get();

            $refundExposure = 0.0;

            foreach ($refundCases as $refundCase) {
                if (in_array($refundCase->status, ['Refunded', 'Closed'], true)) {
                    $refundExposure += (float) (
                        $refundCase->processed_amount
                        ?? $refundCase->approved_amount
                        ?? $refundCase->requested_amount
                        ?? 0
                    );
                } elseif (in_array($refundCase->status, ['Approved', 'Processing'], true)) {
                    $refundExposure += (float) (
                        $refundCase->approved_amount
                        ?? $refundCase->requested_amount
                        ?? 0
                    );
                } else {
                    $refundExposure += (float) ($refundCase->requested_amount ?? 0);
                }
            }

            $disputedAmount = (float) $data['disputed_amount'];
            $remainingExposure = max(
                0,
                $paidAmount - $chargebackExposure - $refundExposure
            );

            if (
                ($chargebackExposure + $refundExposure + $disputedAmount)
                > ($paidAmount + 0.00001)
            ) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'disputed_amount' =>
                        'Chargeback would exceed the remaining unprotected paid amount of $'
                        .number_format($remainingExposure, 2)
                        .'. Existing refund and open chargeback exposure are included.',
                ]);
            }

            do {
                $caseReference = 'CB-'
                    .now()->format('Ymd')
                    .'-'
                    .strtoupper(bin2hex(random_bytes(4)));

                $exists = DB::table('chargeback_cases')
                    ->where('case_reference', $caseReference)
                    ->exists();
            } while ($exists);

            $now = now();

            $caseId = DB::table('chargeback_cases')->insertGetId([
                'booking_id' => $booking->id,
                'created_by' => $adminId,
                'assigned_to' => null,
                'case_reference' => $caseReference,
                'status' => 'New',
                'disputed_amount' => $disputedAmount,
                'currency' => 'USD',
                'processor' => filled($data['processor'] ?? null)
                    ? trim((string) $data['processor'])
                    : ($paymentMeta->processor ?? null),
                'transaction_reference' => filled($data['transaction_reference'] ?? null)
                    ? trim((string) $data['transaction_reference'])
                    : ($paymentMeta->transaction_reference ?? null),
                'processor_case_reference' => filled($data['processor_case_reference'] ?? null)
                    ? trim((string) $data['processor_case_reference'])
                    : null,
                'reason_code' => filled($data['reason_code'] ?? null)
                    ? trim((string) $data['reason_code'])
                    : null,
                'reason' => filled($data['reason'] ?? null)
                    ? trim((string) $data['reason'])
                    : null,
                'card_brand' => filled($data['card_brand'] ?? null)
                    ? trim((string) $data['card_brand'])
                    : null,
                'card_last4' => filled($data['card_last4'] ?? null)
                    ? (string) $data['card_last4']
                    : null,
                'received_date' => $data['received_date'] ?? null,
                'response_deadline' => $data['response_deadline'] ?? null,
                'submitted_at' => null,
                'resolved_at' => null,
                'closed_at' => null,
                'outcome' => null,
                'internal_note' => filled($data['internal_note'] ?? null)
                    ? trim((string) $data['internal_note'])
                    : null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('chargeback_case_events')->insert([
                'chargeback_case_id' => $caseId,
                'admin_id' => $adminId,
                'event_type' => 'created',
                'from_status' => null,
                'to_status' => 'New',
                'details' => 'Chargeback case opened in CRM.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return $caseId;
        });

        $case = DB::table('chargeback_cases')->find($caseId);

        return redirect()
            ->route('crm.chargeback_detail', $caseId)
            ->with('crm_success', 'Chargeback case '.$case->case_reference.' opened successfully.');
    }

    public function chargebackDetail(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        abort_unless(
            Schema::hasTable('chargeback_cases') &&
            Schema::hasTable('chargeback_case_events') &&
            Schema::hasTable('chargeback_case_documents'),
            503,
            'Chargeback case tables are not available.'
        );

        $case = DB::table('chargeback_cases')
            ->leftJoin('admins as created_admins', 'chargeback_cases.created_by', '=', 'created_admins.id')
            ->leftJoin('admins as assigned_admins', 'chargeback_cases.assigned_to', '=', 'assigned_admins.id')
            ->select(
                'chargeback_cases.*',
                'created_admins.name as created_name',
                'assigned_admins.name as assigned_name'
            )
            ->where('chargeback_cases.id', $id)
            ->first();

        abort_unless($case, 404, 'Chargeback case not found.');

        $booking = Booking::find($case->booking_id);

        $paymentMeta = Schema::hasTable('payment_crm_meta')
            ? DB::table('payment_crm_meta')
                ->where('booking_id', $case->booking_id)
                ->first()
            : null;

        $events = DB::table('chargeback_case_events')
            ->leftJoin('admins', 'chargeback_case_events.admin_id', '=', 'admins.id')
            ->select('chargeback_case_events.*', 'admins.name as admin_name')
            ->where('chargeback_case_events.chargeback_case_id', $case->id)
            ->orderBy('chargeback_case_events.created_at')
            ->orderBy('chargeback_case_events.id')
            ->get();

        $documents = DB::table('chargeback_case_documents')
            ->leftJoin('admins', 'chargeback_case_documents.uploaded_by', '=', 'admins.id')
            ->select('chargeback_case_documents.*', 'admins.name as uploaded_by_name')
            ->where('chargeback_case_documents.chargeback_case_id', $case->id)
            ->orderByDesc('chargeback_case_documents.created_at')
            ->orderByDesc('chargeback_case_documents.id')
            ->get();

        return view('crm.chargeback-detail', compact(
            'case',
            'booking',
            'paymentMeta',
            'events',
            'documents'
        ));
    }

    private function chargebackWorkflowCase(int $id)
    {
        abort_unless(
            Schema::hasTable('chargeback_cases') &&
            Schema::hasTable('chargeback_case_events'),
            503,
            'Chargeback case tables are not available.'
        );

        $case = DB::table('chargeback_cases')->where('id', $id)->first();
        abort_unless($case, 404, 'Chargeback case not found.');

        return $case;
    }

    private function chargebackWorkflowEvent(
        int $caseId,
        int $adminId,
        string $eventType,
        ?string $fromStatus,
        ?string $toStatus,
        ?string $details = null
    ): void {
        $now = now();

        DB::table('chargeback_case_events')->insert([
            'chargeback_case_id' => $caseId,
            'admin_id' => $adminId ?: null,
            'event_type' => $eventType,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'details' => $details,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function chargebackEvidenceRequired(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $case = $this->chargebackWorkflowCase($id);

        if ($case->status !== 'New') {
            return back()->withErrors([
                'chargeback' => 'Only a New chargeback can be moved to Evidence Required.'
            ]);
        }

        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $adminId = (int) $request->session()->get('crm_admin_id');

        DB::transaction(function () use ($case, $adminId, $data) {
            $updated = DB::table('chargeback_cases')
                ->where('id', $case->id)
                ->where('status', 'New')
                ->update([
                    'status' => 'Evidence Required',
                    'assigned_to' => $case->assigned_to ?: ($adminId ?: null),
                    'updated_at' => now(),
                ]);

            if ($updated !== 1) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'chargeback' => 'Chargeback status changed before this action completed. Please refresh and try again.',
                ]);
            }

            $this->chargebackWorkflowEvent(
                $case->id,
                $adminId,
                'evidence_required',
                'New',
                'Evidence Required',
                filled($data['note'] ?? null)
                    ? trim((string) $data['note'])
                    : 'Evidence requested for chargeback case.'
            );
        });

        return redirect()
            ->route('crm.chargeback_detail', $case->id)
            ->with('crm_success', 'Chargeback moved to Evidence Required.');
    }

    public function chargebackEvidenceReady(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $case = $this->chargebackWorkflowCase($id);

        if ($case->status !== 'Evidence Required') {
            return back()->withErrors([
                'chargeback' => 'Only an Evidence Required chargeback can be marked Evidence Ready.'
            ]);
        }

        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $adminId = (int) $request->session()->get('crm_admin_id');

        DB::transaction(function () use ($case, $adminId, $data) {
            $updated = DB::table('chargeback_cases')
                ->where('id', $case->id)
                ->where('status', 'Evidence Required')
                ->update([
                    'status' => 'Evidence Ready',
                    'updated_at' => now(),
                ]);

            if ($updated !== 1) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'chargeback' => 'Chargeback status changed before this action completed. Please refresh and try again.',
                ]);
            }

            $this->chargebackWorkflowEvent(
                $case->id,
                $adminId,
                'evidence_ready',
                'Evidence Required',
                'Evidence Ready',
                filled($data['note'] ?? null)
                    ? trim((string) $data['note'])
                    : 'Chargeback evidence marked ready.'
            );
        });

        return redirect()
            ->route('crm.chargeback_detail', $case->id)
            ->with('crm_success', 'Chargeback evidence marked ready.');
    }

    public function chargebackSubmit(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $case = $this->chargebackWorkflowCase($id);

        if ($case->status !== 'Evidence Ready') {
            return back()->withErrors([
                'chargeback' => 'Only an Evidence Ready chargeback can be marked Submitted.'
            ]);
        }

        $data = $request->validate([
            'processor_case_reference' => ['nullable', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $adminId = (int) $request->session()->get('crm_admin_id');
        $processorCaseReference = filled($data['processor_case_reference'] ?? null)
            ? trim((string) $data['processor_case_reference'])
            : $case->processor_case_reference;

        DB::transaction(function () use ($case, $adminId, $processorCaseReference, $data) {
            $now = now();

            $updated = DB::table('chargeback_cases')
                ->where('id', $case->id)
                ->where('status', 'Evidence Ready')
                ->update([
                    'status' => 'Submitted',
                    'processor_case_reference' => $processorCaseReference,
                    'submitted_at' => $now,
                    'updated_at' => $now,
                ]);

            if ($updated !== 1) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'chargeback' => 'Chargeback status changed before this action completed. Please refresh and try again.',
                ]);
            }

            $details = 'External chargeback submission recorded in CRM. CRM did not submit automatically to the processor/network.';
            if (filled($data['note'] ?? null)) {
                $details .= ' Note: '.trim((string) $data['note']);
            }

            $this->chargebackWorkflowEvent(
                $case->id,
                $adminId,
                'submitted',
                'Evidence Ready',
                'Submitted',
                $details
            );
        });

        return redirect()
            ->route('crm.chargeback_detail', $case->id)
            ->with('crm_success', 'External chargeback submission recorded successfully.');
    }

    public function chargebackReview(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $case = $this->chargebackWorkflowCase($id);

        if ($case->status !== 'Submitted') {
            return back()->withErrors([
                'chargeback' => 'Only a Submitted chargeback can be moved to Under Review.'
            ]);
        }

        $adminId = (int) $request->session()->get('crm_admin_id');

        DB::transaction(function () use ($case, $adminId) {
            $updated = DB::table('chargeback_cases')
                ->where('id', $case->id)
                ->where('status', 'Submitted')
                ->update([
                    'status' => 'Under Review',
                    'updated_at' => now(),
                ]);

            if ($updated !== 1) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'chargeback' => 'Chargeback status changed before this action completed. Please refresh and try again.',
                ]);
            }

            $this->chargebackWorkflowEvent(
                $case->id,
                $adminId,
                'status_changed',
                'Submitted',
                'Under Review',
                'Chargeback moved to Under Review.'
            );
        });

        return redirect()
            ->route('crm.chargeback_detail', $case->id)
            ->with('crm_success', 'Chargeback moved to Under Review.');
    }

    public function chargebackResolve(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $case = $this->chargebackWorkflowCase($id);

        if ($case->status !== 'Under Review') {
            return back()->withErrors([
                'chargeback' => 'Only an Under Review chargeback can be resolved.'
            ]);
        }

        $data = $request->validate([
            'outcome' => ['required', 'in:Won,Lost,Accepted'],
            'note' => ['required', 'string', 'max:2000'],
        ]);

        $adminId = (int) $request->session()->get('crm_admin_id');
        $outcome = (string) $data['outcome'];

        DB::transaction(function () use ($case, $adminId, $outcome, $data) {
            $now = now();

            $updated = DB::table('chargeback_cases')
                ->where('id', $case->id)
                ->where('status', 'Under Review')
                ->update([
                    'status' => $outcome,
                    'outcome' => $outcome,
                    'resolved_at' => $now,
                    'updated_at' => $now,
                ]);

            if ($updated !== 1) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'chargeback' => 'Chargeback status changed before this action completed. Please refresh and try again.',
                ]);
            }

            $this->chargebackWorkflowEvent(
                $case->id,
                $adminId,
                'resolved',
                'Under Review',
                $outcome,
                trim((string) $data['note'])
            );
        });

        return redirect()
            ->route('crm.chargeback_detail', $case->id)
            ->with('crm_success', 'Chargeback outcome recorded as '.$outcome.'.');
    }

    public function chargebackClose(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $case = $this->chargebackWorkflowCase($id);

        if (!in_array($case->status, ['Won', 'Lost', 'Accepted'], true)) {
            return back()->withErrors([
                'chargeback' => 'Only a Won, Lost, or Accepted chargeback can be closed.'
            ]);
        }

        $adminId = (int) $request->session()->get('crm_admin_id');
        $fromStatus = $case->status;

        DB::transaction(function () use ($case, $adminId, $fromStatus) {
            $now = now();

            $updated = DB::table('chargeback_cases')
                ->where('id', $case->id)
                ->where('status', $fromStatus)
                ->update([
                    'status' => 'Closed',
                    'closed_at' => $now,
                    'updated_at' => $now,
                ]);

            if ($updated !== 1) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'chargeback' => 'Chargeback status changed before this action completed. Please refresh and try again.',
                ]);
            }

            $this->chargebackWorkflowEvent(
                $case->id,
                $adminId,
                'closed',
                $fromStatus,
                'Closed',
                'Chargeback case closed. Final outcome: '.($case->outcome ?: $fromStatus).'.'
            );
        });

        return redirect()
            ->route('crm.chargeback_detail', $case->id)
            ->with('crm_success', 'Chargeback case closed.');
    }

    public function notifications(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $adminId = (int) $request->session()->get('crm_admin_id');
        $hasState = Schema::hasTable('crm_notification_reads');
        $now = now();
        $alerts = collect();

        // Fresh/new bookings are operational alerts, not copied notification records.
        // Live-ready rule: records currently archived in CRM Trash must not reappear as notifications.
        $freshBookingAlerts = Booking::whereIn('status',['Fresh','New Booking']);
        if (Schema::hasTable('crm_archives')) {
            $freshBookingAlerts->whereNotExists(function($sub) {
                $sub->select(DB::raw(1))
                    ->from('crm_archives')
                    ->where('crm_archives.module','booking')
                    ->whereNull('crm_archives.restored_at')
                    ->whereRaw('CAST(crm_archives.record_key AS UNSIGNED) = bookings.id');
            });
        }
        $freshBookingAlerts->latest()->take(60)->get()->each(function($b) use ($alerts) {
            $source = trim((string)($b->source ?? 'Website')) ?: 'Website';
            $amount = is_numeric($b->o_FullPrice ?? null) ? '$'.number_format((float)$b->o_FullPrice,2) : null;
            $alerts->push((object)[
                'key'=>'booking:'.$b->id, 'type'=>'booking', 'category'=>'Bookings', 'priority'=>'Normal',
                'title'=>'#'.($b->prefix ?? '').$b->id.' · '.(($b->o_Name ?? '') ?: 'Guest'),
                'meta'=>$source.' · '.collect([$amount])->filter()->implode(' · '),
                'created_at'=>$b->created_at, 'url'=>route('crm.booking_detail',$b->id),
                'icon'=>'fa-plane', 'tone'=>'blue', 'record_id'=>$b->id,
            ]);
        });

        $leadAlertQuery = DB::table('enquiries');
        if (Schema::hasTable('crm_archives')) {
            $leadAlertQuery->whereNotExists(function($sub) {
                $sub->select(DB::raw(1))
                    ->from('crm_archives')
                    ->where('crm_archives.module','lead')
                    ->whereNull('crm_archives.restored_at')
                    ->whereRaw('CAST(crm_archives.record_key AS UNSIGNED) = enquiries.id');
            });
        }
        $leadAlertQuery->latest()->take(50)->get()->each(function($e) use ($alerts) {
            $alerts->push((object)[
                'key'=>'lead:'.$e->id, 'type'=>'lead', 'category'=>'Leads', 'priority'=>'Normal',
                'title'=>(($e->name ?? '') ?: 'New enquiry'),
                'meta'=>(($e->subject ?? '') ?: (($e->going_to ?? '') ?: 'General enquiry')),
                'created_at'=>$e->created_at, 'url'=>route('crm.lead_detail',$e->id),
                'icon'=>'fa-user-plus', 'tone'=>'amber', 'record_id'=>$e->id,
            ]);
        });

        if (Schema::hasTable('booking_crm_meta')) {
            $paymentAlertQuery = DB::table('booking_crm_meta')->join('bookings','booking_crm_meta.booking_id','=','bookings.id')
                ->whereIn('booking_crm_meta.payment_status',['Failed','Declined','Payment Declined','Chargeback']);
            if (Schema::hasTable('crm_archives')) {
                $paymentAlertQuery->whereNotExists(function($sub) {
                    $sub->select(DB::raw(1))
                        ->from('crm_archives')
                        ->whereIn('crm_archives.module',['booking','payment'])
                        ->whereNull('crm_archives.restored_at')
                        ->whereRaw('CAST(crm_archives.record_key AS UNSIGNED) = bookings.id');
                });
            }
            $paymentAlertQuery
                ->select('booking_crm_meta.*','bookings.prefix','bookings.o_Name','bookings.created_at as booking_created_at')
                ->latest('booking_crm_meta.updated_at')->take(40)->get()->each(function($r) use ($alerts) {
                    $alerts->push((object)[
                        'key'=>'payment:'.$r->booking_id, 'type'=>'payment', 'category'=>'Payments', 'priority'=>'Critical',
                        'title'=>'Payment issue · #'.($r->prefix ?? '').$r->booking_id,
                        'meta'=>($r->payment_status ?: 'Payment issue').' · '.(($r->o_Name ?? '') ?: 'Guest'),
                        'created_at'=>$r->updated_at ?: $r->booking_created_at, 'url'=>route('crm.payment_detail',$r->booking_id),
                        'icon'=>'fa-credit-card', 'tone'=>'red', 'record_id'=>$r->booking_id,
                    ]);
                });

            $bookingFollowupAlertQuery = DB::table('booking_crm_meta')->join('bookings','booking_crm_meta.booking_id','=','bookings.id')
                ->whereNotNull('booking_crm_meta.next_action_at')->where('booking_crm_meta.next_action_at','<=',$now);
            if (Schema::hasTable('crm_archives')) {
                $bookingFollowupAlertQuery->whereNotExists(function($sub) {
                    $sub->select(DB::raw(1))
                        ->from('crm_archives')
                        ->where('crm_archives.module','booking')
                        ->whereNull('crm_archives.restored_at')
                        ->whereRaw('CAST(crm_archives.record_key AS UNSIGNED) = bookings.id');
                });
            }
            $bookingFollowupAlertQuery
                ->select('booking_crm_meta.*','bookings.prefix','bookings.o_Name')
                ->latest('booking_crm_meta.next_action_at')->take(40)->get()->each(function($r) use ($alerts) {
                    $alerts->push((object)[
                        'key'=>'followup:'.$r->booking_id, 'type'=>'followup', 'category'=>'Follow-ups', 'priority'=>'High',
                        'title'=>'Follow-up due · #'.($r->prefix ?? '').$r->booking_id,
                        'meta'=>(($r->next_action ?? '') ?: 'Booking follow-up').' · due '.\Carbon\Carbon::parse($r->next_action_at)->diffForHumans(),
                        'created_at'=>$r->next_action_at, 'url'=>route('crm.booking_detail',$r->booking_id),
                        'icon'=>'fa-phone', 'tone'=>'purple', 'record_id'=>$r->booking_id,
                    ]);
                });
        }

        if (Schema::hasTable('lead_crm_meta')) {
            $leadFollowupAlertQuery = DB::table('lead_crm_meta')->join('enquiries','lead_crm_meta.lead_id','=','enquiries.id')
                ->whereNotNull('lead_crm_meta.next_follow_up_at')->where('lead_crm_meta.next_follow_up_at','<=',$now);
            if (Schema::hasTable('crm_archives')) {
                $leadFollowupAlertQuery->whereNotExists(function($sub) {
                    $sub->select(DB::raw(1))
                        ->from('crm_archives')
                        ->where('crm_archives.module','lead')
                        ->whereNull('crm_archives.restored_at')
                        ->whereRaw('CAST(crm_archives.record_key AS UNSIGNED) = enquiries.id');
                });
            }
            $leadFollowupAlertQuery
                ->select('lead_crm_meta.*','enquiries.name')
                ->latest('lead_crm_meta.next_follow_up_at')->take(40)->get()->each(function($r) use ($alerts) {
                    $alerts->push((object)[
                        'key'=>'lead-followup:'.$r->lead_id, 'type'=>'lead-followup', 'category'=>'Follow-ups', 'priority'=>($r->priority ?: 'High'),
                        'title'=>'Lead follow-up due · '.(($r->name ?? '') ?: 'Enquiry #'.$r->lead_id),
                        'meta'=>(($r->next_action ?? '') ?: 'Lead follow-up').' · due '.\Carbon\Carbon::parse($r->next_follow_up_at)->diffForHumans(),
                        'created_at'=>$r->next_follow_up_at, 'url'=>route('crm.lead_detail',$r->lead_id),
                        'icon'=>'fa-clock', 'tone'=>'purple', 'record_id'=>$r->lead_id,
                    ]);
                });
        }

        $readKeys = collect();
        if ($hasState && $adminId) {
            $readKeys = DB::table('crm_notification_reads')->where('admin_id',$adminId)->pluck('notification_key')->flip();
        }
        $alerts = $alerts->unique('key')->map(function($a) use ($readKeys) {
            $a->is_read = $readKeys->has($a->key);
            return $a;
        })->sortByDesc(function($a){ return \Carbon\Carbon::parse($a->created_at)->timestamp; })->values();

        $q = trim((string)$request->input('q',''));
        $category = $request->input('category','All');
        $view = $request->input('view','all');
        if ($q !== '') $alerts = $alerts->filter(fn($a)=>stripos($a->title.' '.$a->meta,$q)!==false)->values();
        if ($category !== 'All') $alerts = $alerts->where('category',$category)->values();
        if ($view === 'unread') $alerts = $alerts->where('is_read',false)->values();

        $allAlerts = $alerts;
        $perPage = 20;
        $page = max(1,(int)$request->input('page',1));
        $paged = new LengthAwarePaginator($alerts->forPage($page,$perPage)->values(),$alerts->count(),$perPage,$page,[
            'path'=>$request->url(),'query'=>$request->query()
        ]);

        $stats = [
            'total'=>$allAlerts->count(), 'unread'=>$allAlerts->where('is_read',false)->count(),
            'bookings'=>$allAlerts->where('category','Bookings')->count(), 'leads'=>$allAlerts->where('category','Leads')->count(),
            'payments'=>$allAlerts->where('category','Payments')->count(), 'followups'=>$allAlerts->where('category','Follow-ups')->count(),
        ];
        return view('crm.notifications', ['alerts'=>$paged,'stats'=>$stats,'hasState'=>$hasState]);
    }

    public function notificationRead(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        abort_unless(Schema::hasTable('crm_notification_reads'),503,'Run Notifications V2 migration first.');
        $data=$request->validate(['key'=>['required','string','max:190']]);
        $adminId=(int)$request->session()->get('crm_admin_id');
        DB::table('crm_notification_reads')->updateOrInsert(
            ['admin_id'=>$adminId,'notification_key'=>$data['key']],
            ['read_at'=>now(),'updated_at'=>now(),'created_at'=>now()]
        );
        return back()->with('crm_success','Notification marked as read.');
    }

    public function notificationsReadAll(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        abort_unless(Schema::hasTable('crm_notification_reads'),503,'Run Notifications V2 migration first.');
        $adminId=(int)$request->session()->get('crm_admin_id');
        foreach((array)$request->input('keys',[]) as $key){
            if(!is_string($key) || strlen($key)>190) continue;
            DB::table('crm_notification_reads')->updateOrInsert(
                ['admin_id'=>$adminId,'notification_key'=>$key],
                ['read_at'=>now(),'updated_at'=>now(),'created_at'=>now()]
            );
        }
        return back()->with('crm_success','Visible notifications marked as read.');
    }

    public function leadDetail(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $lead = DB::table('enquiries')->where('id',$id)->first();
        abort_unless($lead,404);

        $leadMeta = null;
        if (Schema::hasTable('lead_crm_meta')) {
            $leadMeta = DB::table('lead_crm_meta')
                ->leftJoin('admins','lead_crm_meta.assigned_to','=','admins.id')
                ->where('lead_crm_meta.lead_id',$id)
                ->select('lead_crm_meta.*','admins.name as agent_name')
                ->first();
        }

        if (!$leadMeta) {
            $leadMeta = (object)[
                'stage' => 'New',
                'priority' => 'Normal',
                'source' => null,
                'assigned_to' => null,
                'agent_name' => null,
                'next_action' => null,
                'next_follow_up_at' => null,
                'travel_from' => null,
                'travel_to' => null,
                'return_date' => null,
                'adults' => null,
                'children' => null,
                'infants' => null,
                'cabin' => null,
                'budget' => null,
                'private_note' => null,
            ];
        }

        $activity = Schema::hasTable('lead_activity')
            ? DB::table('lead_activity')
                ->leftJoin('admins','lead_activity.admin_id','=','admins.id')
                ->where('lead_activity.lead_id',$id)
                ->select('lead_activity.*','admins.name as admin_name')
                ->orderByDesc('lead_activity.id')
                ->get()
            : collect();

        $admins = DB::table('admins')->where('status','1')->orderBy('name')->get();
        $leadStages = ['New','Follow-up','Qualified','Quoted','Negotiation','Converted','Closed','Lost'];
        $leadPriorities = ['Low','Normal','High','Urgent'];

        return view('crm.lead-detail', compact(
            'lead','leadMeta','activity','admins','leadStages','leadPriorities'
        ));
    }

    public function leadOperationsUpdate(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $lead = DB::table('enquiries')->where('id',$id)->first();
        abort_unless($lead,404);
        abort_unless(Schema::hasTable('lead_crm_meta'),503,'Run lead CRM migration first.');

        $data = $request->validate([
            'stage' => ['required','string',Rule::in(['New','Follow-up','Qualified','Quoted','Negotiation','Converted','Closed','Lost'])],
            'priority' => ['required','string',Rule::in(['Low','Normal','High','Urgent'])],
            'source' => ['nullable','string','max:100'],
            'assigned_to' => ['nullable','integer','exists:admins,id'],
            'next_action' => ['nullable','string','max:255'],
            'next_follow_up_at' => ['nullable','date'],
            'travel_from' => ['nullable','string','max:100'],
            'travel_to' => ['nullable','string','max:100'],
            'return_date' => ['nullable','date'],
            'adults' => ['nullable','integer','min:0','max:50'],
            'children' => ['nullable','integer','min:0','max:50'],
            'infants' => ['nullable','integer','min:0','max:50'],
            'cabin' => ['nullable','string','max:50'],
            'budget' => ['nullable','numeric','min:0'],
            'private_note' => ['nullable','string','max:5000'],
        ]);

        $existing = DB::table('lead_crm_meta')->where('lead_id',$id)->first();

        // Snapshot the values before saving so every operational change can be audited.
        $oldValues = [
            'stage' => $existing->stage ?? 'New',
            'priority' => $existing->priority ?? 'Normal',
            'source' => $existing->source ?? null,
            'assigned_to' => $existing->assigned_to ?? null,
            'next_action' => $existing->next_action ?? null,
            'next_follow_up_at' => $existing->next_follow_up_at ?? null,
            'travel_from' => $existing->travel_from ?? null,
            'travel_to' => $existing->travel_to ?? null,
            'return_date' => $existing->return_date ?? null,
            'adults' => $existing->adults ?? null,
            'children' => $existing->children ?? null,
            'infants' => $existing->infants ?? null,
            'cabin' => $existing->cabin ?? null,
            'budget' => $existing->budget ?? null,
            'private_note' => $existing->private_note ?? null,
        ];

        $payload = array_merge($data,[
            'lead_id' => $id,
            'updated_at' => now(),
        ]);

        if ($existing) {
            DB::table('lead_crm_meta')->where('lead_id',$id)->update($payload);
        } else {
            $payload['created_at'] = now();
            DB::table('lead_crm_meta')->insert($payload);
        }

        if (Schema::hasTable('lead_activity')) {
            $adminId = (int)$request->session()->get('crm_admin_id') ?: null;
            $now = now();

            $labels = [
                'stage' => 'Enquiry status',
                'priority' => 'Priority',
                'source' => 'Source',
                'assigned_to' => 'Assigned agent',
                'next_action' => 'Next action',
                'next_follow_up_at' => 'Next follow-up',
                'travel_from' => 'Travel from',
                'travel_to' => 'Travel to',
                'return_date' => 'Return date',
                'adults' => 'Adults',
                'children' => 'Children',
                'infants' => 'Infants',
                'cabin' => 'Cabin',
                'budget' => 'Budget',
                'private_note' => 'Private note',
            ];

            $agentIds = array_values(array_unique(array_filter([
                $oldValues['assigned_to'] ?? null,
                $data['assigned_to'] ?? null,
            ])));
            $agentNames = empty($agentIds)
                ? collect()
                : DB::table('admins')->whereIn('id',$agentIds)->pluck('name','id');

            $displayValue = function (string $field, $value) use ($agentNames) {
                if ($value === null || $value === '') return 'Not set';
                if ($field === 'assigned_to') return $agentNames[(int)$value] ?? ('Agent #'.$value);
                if ($field === 'budget') return '$'.number_format((float)$value,2);
                if (in_array($field,['next_follow_up_at','return_date'],true)) {
                    try { return \Carbon\Carbon::parse($value)->format($field === 'return_date' ? 'd M Y' : 'd M Y, h:i A'); }
                    catch (\Throwable $e) { return (string)$value; }
                }
                return (string)$value;
            };

            foreach ($labels as $field => $label) {
                $old = $oldValues[$field] ?? null;
                $new = $data[$field] ?? null;

                // Normalize empty form values so null and an empty string do not create fake activity.
                $oldComparable = ($old === '' ? null : $old);
                $newComparable = ($new === '' ? null : $new);
                if ((string)$oldComparable === (string)$newComparable) continue;

                $activityType = $field === 'stage' ? 'stage_change' : 'update';
                $title = $field === 'stage' ? 'Enquiry status changed' : $label.' updated';
                $details = $field === 'private_note'
                    ? 'Private note changed'
                    : $displayValue($field,$old).' → '.$displayValue($field,$new);

                DB::table('lead_activity')->insert([
                    'lead_id' => $id,
                    'admin_id' => $adminId,
                    'activity_type' => $activityType,
                    'title' => $title,
                    'details' => $details,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        return back()->with('crm_success','Lead operations updated.');
    }

    public function leadActivityStore(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        abort_unless(DB::table('enquiries')->where('id',$id)->exists(),404);
        abort_unless(Schema::hasTable('lead_activity'),503,'Run lead CRM migration first.');

        $data = $request->validate([
            'activity_type' => ['required','string',Rule::in(['note','call','email','follow_up','task','quotation','meeting'])],
            'title' => ['required','string','max:255'],
            'details' => ['nullable','string','max:3000'],
        ]);

        DB::table('lead_activity')->insert([
            'lead_id' => $id,
            'admin_id' => (int)$request->session()->get('crm_admin_id') ?: null,
            'activity_type' => $data['activity_type'],
            'title' => $data['title'],
            'details' => $data['details'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('crm_success','Lead activity added.');
    }

    public function sendLeadEmail(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $lead = DB::table('enquiries')->where('id', $id)->first();
        abort_unless($lead, 404);

        if (empty($lead->emailid) || !filter_var($lead->emailid, FILTER_VALIDATE_EMAIL)) {
            return back()->with('crm_error', 'This lead does not have a valid passenger email address.');
        }

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:10000'],
        ]);

        $mailSetting = getAllSetting();
        if (($mailSetting['is_mail'] ?? 'OFF') !== 'ON') {
            return back()->with('crm_error', 'Mail sending is currently disabled in website settings.');
        }

        $subject = trim($data['subject']);
        $message = trim($data['message']);

        // Render CRM-entered plain text inside the shared FaresHunt branded email.
        $body = view('emails.crm-branded-message', [
            'subject' => $subject,
            'message' => $message,
            'heading' => 'Thank You for Reaching Out!',
            'subheading' => 'Our travel experts are ready to assist with your flight enquiry.',
            'supportEmail' => $mailSetting['content_emailid'] ?? config('mail.from.address', ''),
            'supportPhone' => $mailSetting['header_contact'] ?? '',
            'supportWhatsApp' => $mailSetting['content_whatsapp'] ?? '',
        ])->render();

        try {
            $sent = app(FlightController::class)->sendSmptEmail(
                $lead->emailid,
                $subject,
                $body
            );

            if (!$sent) {
                throw new \RuntimeException('SMTP sender returned false.');
            }

            if (Schema::hasTable('lead_activity')) {
                DB::table('lead_activity')->insert([
                    'lead_id' => $id,
                    'admin_id' => (int)$request->session()->get('crm_admin_id') ?: null,
                    'activity_type' => 'email',
                    'title' => 'Email sent: '.$subject,
                    'details' => 'Sent to '.$lead->emailid,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return back()->with(
                'crm_success',
                'Email sent successfully to '.$lead->emailid.'.'
            );
        } catch (\Throwable $e) {
            \Log::error('CRM lead email send failed', [
                'lead_id' => $id,
                'recipient' => $lead->emailid,
                'error' => $e->getMessage(),
            ]);

            return back()->with(
                'crm_error',
                'Email could not be sent. Check SMTP/mail settings and try again.'
            );
        }
    }

    public function bookingStatus(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $data = $request->validate([
            'status' => ['required', 'string', Rule::in($this->bookingStatusOptions())],
            'status_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $booking = Booking::findOrFail($id);
        $this->requireBookingWorkAccess($request, $booking);
        $oldStatus = (string) ($booking->status ?: 'Unspecified');
        $newStatus = $data['status'];
        $adminId = (int) $request->session()->get('crm_admin_id');

        if ($oldStatus !== $newStatus) {
            $booking->status = $newStatus;
            $booking->save();

            if (Schema::hasTable('booking_status_history')) {
                DB::table('booking_status_history')->insert([
                    'booking_id' => $booking->id,
                    'admin_id' => $adminId ?: null,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'note' => $data['status_note'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } elseif (!empty($data['status_note']) && Schema::hasTable('booking_status_history')) {
            DB::table('booking_status_history')->insert([
                'booking_id' => $booking->id,
                'admin_id' => $adminId ?: null,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'note' => $data['status_note'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('crm_success', 'Booking status updated to '.$newStatus.'.');
    }

    private function renderBookingCrmTemplate(Booking $booking, object $template): array
    {
        $setting = getAllSetting();

        $decode = function ($value) {
            $decoded = json_decode((string) $value, true);
            return json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : [];
        };

        $sectorGo = $decode($booking->o_SectorGo);
        $sectorBack = $decode($booking->o_SectorBack);
        $passengers = $decode($booking->o_Passenger);
        $price = $decode($booking->o_Price);
        $billing = $decode($booking->o_Billing);
        $other = $decode($booking->o_Other);

        $isSystemTicketBooking =
            !empty($template->is_system)
            && strcasecmp(trim((string) ($template->label ?? '')), 'Ticket Booking') === 0
            && strcasecmp(trim((string) ($template->category ?? '')), 'Booking Confirmation') === 0;

        if ($isSystemTicketBooking) {
            $subject = str_replace(
                '$bookingid',
                'FH'.$booking->id,
                (string) $template->subject
            );

            $body = view('emails.booking-confirmation', [
                'booking' => $booking,
                'setting' => $setting,
                'sectorGo' => $sectorGo,
                'sectorBack' => $sectorBack,
                'passengers' => $passengers,
                'price' => $price,
                'billing' => $billing,
                'other' => $other,
            ])->render();

            $tnc = '';

            return compact('subject', 'body', 'tnc');
        }

        $segments = function ($node) use (&$segments) {
            $found = [];
            if (!is_array($node)) return $found;

            $looksLikeSegment =
                isset($node['Departure_Iata']) ||
                isset($node['Arrival_Iata']) ||
                isset($node['FlightNum']) ||
                isset($node['AirlineName']);

            if ($looksLikeSegment) return [$node];

            foreach ($node as $child) {
                if (is_array($child)) {
                    $found = array_merge($found, $segments($child));
                }
            }

            return $found;
        };

        $goSegments = $segments($sectorGo);
        $backSegments = $segments($sectorBack);

        $formatSegmentList = function (array $list): string {
            if (!$list) return '—';

            $rows = [];
            foreach ($list as $s) {
                $from = strtoupper(trim((string)($s['Departure_Iata'] ?? $s['DepartureIata'] ?? '')));
                $to = strtoupper(trim((string)($s['Arrival_Iata'] ?? $s['ArrivalIata'] ?? '')));
                $airline = trim((string)($s['AirlineName'] ?? $s['OperatingAirlineName'] ?? $s['Airline'] ?? 'Airline'));
                $flight = trim((string)($s['FlightNum'] ?? $s['FlightNumber'] ?? ''));
                $dep = $s['Departure_Date'] ?? $s['DepartureDate'] ?? null;

                $date = '';
                if ($dep) {
                    try {
                        $date = \Carbon\Carbon::parse($dep)->format('d M Y, h:i A');
                    } catch (\Throwable $e) {
                        $date = (string)$dep;
                    }
                }

                $route = trim(($from ?: '—').' → '.($to ?: '—'));
                $meta = trim($airline.($flight ? ' · '.$flight : '').($date ? ' · '.$date : ''));

                $rows[] =
                    '<div style="padding:8px 0;border-bottom:1px solid #edf1f5;">'
                    .'<b>'.e($route).'</b>'
                    .'<div style="margin-top:3px;color:#607186;">'.e($meta).'</div>'
                    .'</div>';
            }

            return implode('', $rows);
        };

        $findValue = function ($node, array $keys) use (&$findValue) {
            if (!is_array($node)) return null;

            foreach ($keys as $key) {
                if (isset($node[$key]) && $node[$key] !== '') return $node[$key];
            }

            foreach ($node as $child) {
                if (is_array($child)) {
                    $found = $findValue($child, $keys);
                    if ($found !== null && $found !== '') return $found;
                }
            }

            return null;
        };

        $crmMeta = Schema::hasTable('booking_crm_meta')
            ? DB::table('booking_crm_meta')->where('booking_id', $booking->id)->first()
            : null;

        $pnr =
            ($crmMeta->pnr ?? null)
            ?: $findValue($sectorGo, ['PNR','Pnr','pnr','BookingReference','BookingRef','RecordLocator'])
            ?: $findValue($sectorBack, ['PNR','Pnr','pnr','BookingReference','BookingRef','RecordLocator'])
            ?: $findValue($other, ['PNR','Pnr','pnr','BookingReference','BookingRef','RecordLocator'])
            ?: 'Pending';

        $passengerSummary = [];
        foreach ($passengers as $p) {
            if (!is_array($p)) continue;

            $full = trim(implode(' ', array_filter([
                $p['Title'] ?? '',
                $p['FirstName'] ?? $p['Name'] ?? '',
                $p['MiddleName'] ?? '',
                $p['LastName'] ?? $p['Surname'] ?? '',
            ], fn($v) => trim((string)$v) !== '')));

            if ($full !== '') $passengerSummary[] = $full;
        }

        $passengerText = $passengerSummary
            ? implode(', ', $passengerSummary)
            : ($booking->o_Name ?: 'Traveler');

        $bookingRef = 'FH'.$booking->id;
        $total = is_numeric($booking->o_FullPrice)
            ? '$'.number_format((float)$booking->o_FullPrice, 2)
            : (string)($booking->o_FullPrice ?: '—');

        $supportPhone = $setting['header_contact'] ?? '';
        $supportEmail = $setting['content_emailid'] ?? config('mail.from.address', '');

        $map = [
            '$bookingid' => $bookingRef,
            '$name' => $booking->o_Name ?: 'Traveler',
            '$c_name' => $booking->o_Name ?: 'Traveler',
            '$email' => $booking->o_Email ?: '',
            '$c_emailid' => $booking->o_Email ?: '',
            '$phone' => $booking->o_Phone ?: '',
            '$c_phone' => $booking->o_Phone ?: '',
            '$pnr' => (string)$pnr,
            '$price' => $total,
            '$totalprice' => $total,
            '$passengers' => $passengerText,
            '$sectorgos' => $formatSegmentList($goSegments),
            '$sectorreturns' => $backSegments ? $formatSegmentList($backSegments) : '',
            '$header_contact' => (string)$supportPhone,
            '$content_emailid' => (string)$supportEmail,
            '{{ asset("mail_images/fareshunt-logo.png") }}' => asset('mail_images/fareshunt-logo.png'),
            "{{ asset('mail_images/fareshunt-logo.png') }}" => asset('mail_images/fareshunt-logo.png'),
        ];

        // FH template XSS fix (31 Oct 2026): booking values come from the public booking form,
        // so every plain-text value is HTML-escaped before it goes into template HTML.
        // Segment lists are already built as safe HTML (each value passed through e()).
        $htmlSafeKeys = ['$sectorgos', '$sectorreturns'];
        $htmlMap = [];
        foreach ($map as $placeholder => $value) {
            $htmlMap[$placeholder] = (in_array($placeholder, $htmlSafeKeys, true) || str_starts_with($placeholder, '{{'))
                ? (string) $value
                : e((string) $value);
        }
        // Subject is plain text (email header): no HTML escaping, but no line breaks.
        $subjectMap = array_map(fn ($value) => str_replace(["\r", "\n"], ' ', (string) $value), $map);

        $subject = strtr((string)$template->subject, $subjectMap);
        $body = strtr((string)($template->body ?? ''), $htmlMap);
        $tnc = strtr((string)($template->tnc ?? ''), $htmlMap);

        if (trim($tnc) !== '') {
            $body .= '<div style="max-width:680px;margin:0 auto;padding:18px 30px;font-family:Arial,Helvetica,sans-serif;font-size:11px;line-height:1.65;color:#6f7e90;">'.$tnc.'</div>';
        }

        return compact('subject', 'body', 'tnc');
    }

    public function bookingTemplateEmailPreview(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $data = $request->validate([
            'template_id' => ['required', 'integer'],
        ]);

        $booking = Booking::findOrFail($id);
        $template = DB::table('mail_templates')->where('id', (int)$data['template_id'])->first();
        abort_unless($template, 404);

        $rendered = $this->renderBookingCrmTemplate($booking, $template);

        return view('crm.booking-template-preview', [
            'booking' => $booking,
            'template' => $template,
            'subject' => $rendered['subject'],
            'body' => $rendered['body'],
        ]);
    }

    public function sendBookingTemplateEmail(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $data = $request->validate([
            'template_id' => ['required', 'integer'],
        ]);

        $booking = Booking::findOrFail($id);
        $this->requireBookingWorkAccess($request, $booking);

        if (empty($booking->o_Email) || !filter_var($booking->o_Email, FILTER_VALIDATE_EMAIL)) {
            return back()->with('crm_error', 'This booking does not have a valid passenger email address.');
        }

        $template = DB::table('mail_templates')->where('id', (int)$data['template_id'])->first();
        if (!$template) {
            return back()->with('crm_error', 'Selected email template was not found.');
        }

        if (Schema::hasColumn('mail_templates', 'is_active') && !(bool)$template->is_active) {
            return back()->with(
                'crm_error',
                'The selected template is inactive. Activate it in Email Templates before sending.'
            );
        }

        $mailSetting = getAllSetting();
        if (($mailSetting['is_mail'] ?? 'OFF') !== 'ON') {
            return back()->with('crm_error', 'Mail sending is currently disabled in website settings.');
        }

        $rendered = $this->renderBookingCrmTemplate($booking, $template);
        $adminId = (int)$request->session()->get('crm_admin_id') ?: null;
        $emailType = 'crm_template_'.(int)$template->id;

        try {
            $sent = app(FlightController::class)->sendSmptEmail(
                $booking->o_Email,
                $rendered['subject'],
                $rendered['body']
            );

            if (!$sent) {
                throw new \RuntimeException('SMTP sender returned false.');
            }

            if (Schema::hasTable('crm_email_logs')) {
                DB::table('crm_email_logs')->insert([
                    'booking_id' => $booking->id,
                    'admin_id' => $adminId,
                    'email_type' => $emailType,
                    'recipient' => $booking->o_Email,
                    'subject' => $rendered['subject'],
                    'status' => 'sent',
                    'source' => 'crm_template_manual',
                    'error_message' => null,
                    'sent_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return back()->with(
                'crm_success',
                ($template->label ?? 'Email').' sent successfully to '.$booking->o_Email.'.'
            );
        } catch (\Throwable $e) {
            if (Schema::hasTable('crm_email_logs')) {
                DB::table('crm_email_logs')->insert([
                    'booking_id' => $booking->id,
                    'admin_id' => $adminId,
                    'email_type' => $emailType,
                    'recipient' => $booking->o_Email,
                    'subject' => $rendered['subject'],
                    'status' => 'failed',
                    'source' => 'crm_template_manual',
                    'error_message' => mb_substr($e->getMessage(), 0, 1000),
                    'sent_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return back()->with(
                'crm_error',
                'Email could not be sent. Check SMTP/mail settings and try again.'
            );
        }
    }
    public function bookingEmailPreview(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $booking = Booking::findOrFail($id);
        $setting = getAllSetting();

        return view('emails.booking-confirmation', [
            'booking' => $booking,
            'setting' => $setting,
            'sectorGo' => (array) json_decode((string) $booking->o_SectorGo, true),
            'sectorBack' => (array) json_decode((string) $booking->o_SectorBack, true),
            'passengers' => (array) json_decode((string) $booking->o_Passenger, true),
            'price' => (array) json_decode((string) $booking->o_Price, true),
            'billing' => (array) json_decode((string) $booking->o_Billing, true),
            'other' => (array) json_decode((string) $booking->o_Other, true),
        ]);
    }

    public function resendBookingConfirmation(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $booking = Booking::findOrFail($id);
        $this->requireBookingWorkAccess($request, $booking);

        if (empty($booking->o_Email) || !filter_var($booking->o_Email, FILTER_VALIDATE_EMAIL)) {
            return back()->with('crm_error', 'This booking does not have a valid passenger email address.');
        }

        $mailSetting = getAllSetting();
        if (($mailSetting['is_mail'] ?? 'OFF') !== 'ON') {
            return back()->with('crm_error', 'Mail sending is currently disabled in website settings.');
        }

        try {
            app(FlightController::class)->SendingMail($booking->id, [
                'source' => 'crm_resend',
                'admin_id' => (int) $request->session()->get('crm_admin_id'),
            ]);

            return back()->with('crm_success', 'Booking confirmation email sent to '.$booking->o_Email.'.');
        } catch (\Throwable $e) {
            if (Schema::hasTable('crm_email_logs')) {
                DB::table('crm_email_logs')->insert([
                    'booking_id' => $booking->id,
                    'admin_id' => (int) $request->session()->get('crm_admin_id') ?: null,
                    'email_type' => 'booking_confirmation',
                    'recipient' => $booking->o_Email,
                    'subject' => 'Booking Confirmation - '.fh_booking_ref($booking->id),
                    'status' => 'failed',
                    'source' => 'crm_resend',
                    'error_message' => mb_substr($e->getMessage(), 0, 1000),
                    'sent_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return back()->with('crm_error', 'Email could not be sent. Check SMTP/mail settings and try again.');
        }
    }

    public function bookingRemark(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $booking = Booking::findOrFail($id);
        $this->requireBookingWorkAccess($request, $booking);

        $data = $request->validate(['remark'=>['required','string','max:1000']]);
        $adminId=(int)$request->session()->get('crm_admin_id');
        DB::table('booking_remarks')->insert(['booking_id'=>$id,'role_id'=>null,'admin_id'=>$adminId,'remark'=>$data['remark'],'created_at'=>now(),'updated_at'=>now()]);
        Booking::where('id',$id)->update(['remark'=>$data['remark']]);
        return back()->with('crm_success','Remark added.');
    }

    public function bookingAssign(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        // Security: assignment/reassignment is restricted to Super Admin and Manager.
        $adminId = (int) $request->session()->get('crm_admin_id');
        $roleSlug = Schema::hasTable('crm_access_roles')
            ? (string) DB::table('admins as a')
                ->leftJoin('crm_access_roles as r', 'a.crm_access_role_id', '=', 'r.id')
                ->where('a.id', $adminId)
                ->value('r.slug')
            : '';

        abort_unless(in_array($roleSlug, ['super-admin', 'manager'], true), 403);

        $data = $request->validate(['assign_to'=>['required','integer','exists:admins,id']]);
        Booking::where('id',$id)->update(['assign_to'=>$data['assign_to']]);
        $admin=DB::table('admins')->where('id',$data['assign_to'])->first();
        DB::table('booking_assigns')->insert(['booking_id'=>$id,'role_id'=>$admin->role_id ?? null,'assign_to'=>$data['assign_to'],'created_at'=>now(),'updated_at'=>now()]);
        return back()->with('crm_success','Booking assigned.');
    }
public function bookingOperations(Request $request, int $id)
{
    if ($redirect = $this->guard($request)) {
        return $redirect;
    }

    $booking = Booking::findOrFail($id);
        $this->requireBookingWorkAccess($request, $booking);

    abort_unless(
        Schema::hasTable('booking_crm_meta'),
        503,
        'Run booking CRM migration first.'
    );

    $data = $request->validate([
        'pnr' => ['nullable', 'string', 'max:100'],
        'ticket_number' => ['nullable', 'string', 'max:150'],

        'payment_status' => [
            'required',
            'string',
            Rule::in([
                'Untracked',
                'Pending',
                'Paid',
                'Partially Paid',
                'Declined',
                'Refund Pending',
                'Refunded',
                'Chargeback'
            ])
        ],

        'ticket_status' => [
            'required',
            'string',
            Rule::in([
                'Not Ticketed',
                'Ticketing',
                'Issued',
                'Ticketed',
                'E-Ticket',
                'Voided',
                'Exchanged'
            ])
        ],

        'markup_amount' => ['nullable', 'numeric', 'min:0'],
        'via_source' => ['nullable', 'string', 'max:150'],
        'next_action' => ['nullable', 'string', 'max:255'],
        'next_action_at' => ['nullable', 'date'],
        'operations_note' => ['nullable', 'string', 'max:5000'],

        // Agent Performance / Finance
        'selling_amount' => ['nullable', 'numeric', 'min:0'],
        'supplier_cost' => ['nullable', 'numeric', 'min:0'],
        'processing_cost' => ['nullable', 'numeric', 'min:0'],
        'other_cost' => ['nullable', 'numeric', 'min:0'],
        'finance_note' => ['nullable', 'string', 'max:2000'],
    ]);

    $existing = DB::table('booking_crm_meta')
        ->where('booking_id', $booking->id)
        ->first();

    $payload = [
        'booking_id' => $booking->id,
        'pnr' => isset($data['pnr']) ? trim($data['pnr']) : null,
        'ticket_number' => isset($data['ticket_number']) ? trim($data['ticket_number']) : null,
        'payment_status' => $data['payment_status'],
        'ticket_status' => $data['ticket_status'],
        'markup_amount' => $data['markup_amount'] ?? null,
        'via_source' => isset($data['via_source']) ? trim($data['via_source']) : null,
        'next_action' => isset($data['next_action']) ? trim($data['next_action']) : null,
        'next_action_at' => $data['next_action_at'] ?? null,
        'operations_note' => $data['operations_note'] ?? null,
        'updated_at' => now(),
    ];

    /*
     * Financial values are calculated on the server.
     * Existing booking fare/API payloads are never overwritten.
     */
    $sellingAmount = array_key_exists('selling_amount', $data) && $data['selling_amount'] !== null
        ? (float) $data['selling_amount']
        : max(0, (float) preg_replace('/[^0-9.\-]/', '', (string) $booking->o_FullPrice));

    $supplierCost = (float) ($data['supplier_cost'] ?? 0);
    $processingCost = (float) ($data['processing_cost'] ?? 0);
    $otherCost = (float) ($data['other_cost'] ?? 0);

    $grossProfit = round($sellingAmount - $supplierCost, 2);
    $netProfit = round($grossProfit - $processingCost - $otherCost, 2);

    $financialPayload = [
        'booking_id' => $booking->id,
        'selling_amount' => round($sellingAmount, 2),
        'supplier_cost' => round($supplierCost, 2),
        'processing_cost' => round($processingCost, 2),
        'other_cost' => round($otherCost, 2),
        'gross_profit' => $grossProfit,
        'net_profit' => $netProfit,
        'finance_note' => isset($data['finance_note']) ? trim((string) $data['finance_note']) : null,
        'updated_by' => (int) $request->session()->get('crm_admin_id') ?: null,
        'updated_at' => now(),
    ];

    DB::transaction(function () use ($booking, $existing, $payload, $data, $financialPayload) {

        if ($existing) {
            DB::table('booking_crm_meta')
                ->where('booking_id', $booking->id)
                ->update($payload);
        } else {
            $payload['created_at'] = now();

            DB::table('booking_crm_meta')->insert($payload);
        }

        if (Schema::hasTable('booking_financials')) {
            $financeExists = DB::table('booking_financials')
                ->where('booking_id', $booking->id)
                ->exists();

            if ($financeExists) {
                DB::table('booking_financials')
                    ->where('booking_id', $booking->id)
                    ->update($financialPayload);
            } else {
                $financialPayload['created_at'] = now();
                DB::table('booking_financials')->insert($financialPayload);
            }
        }

        /*
         * Keep Payment CRM status aligned if a Payment CRM record
         * already exists. Payment CRM is treated as the payment
         * source of truth elsewhere in this controller.
         */
        if (Schema::hasTable('payment_crm_meta')) {

            $paymentMeta = DB::table('payment_crm_meta')
                ->where('booking_id', $booking->id)
                ->first();

            if ($paymentMeta) {

                $paymentStatusMap = [
                    'Untracked' => 'Untracked',
                    'Pending' => 'Pending',
                    'Paid' => 'Paid',
                    'Partially Paid' => 'Partially Paid',
                    'Declined' => 'Declined',
                    'Refund Pending' => 'Refund Pending',
                    'Refunded' => 'Refunded',
                    'Chargeback' => 'Chargeback',
                ];

                DB::table('payment_crm_meta')
                    ->where('booking_id', $booking->id)
                    ->update([
                        'payment_status' =>
                            $paymentStatusMap[$data['payment_status']]
                            ?? $data['payment_status'],

                        'updated_at' => now(),
                    ]);
            }
        }
    });

    return back()->with(
        'crm_success',
        'Booking operations saved successfully.'
    );
}
    public function export(Request $request, string $type)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        $filename='travelcrm-'.$type.'-'.date('Ymd-His').'.csv';
        $filters=$request->query();

        $callback=function() use ($type,$filters){
            $out=fopen('php://output','w');
            if($type==='bookings'){
                fputcsv($out,['Booking','Customer','Email','Phone','Fare','Source','Status','Created']);
                Booking::orderBy('id')->chunk(500,function($rows) use ($out){foreach($rows as $b)fputcsv($out,[($b->prefix.$b->id),$b->o_Name,$b->o_Email,$b->o_Phone,$b->o_FullPrice,$b->source,$b->status,$b->created_at]);});
            } elseif($type==='customers'){
                fputcsv($out,['Customer','Email','Phone','Bookings','Last booking']);
                $rows=Booking::select('o_Name','o_Email','o_Phone',DB::raw('COUNT(*) c'),DB::raw('MAX(created_at) last_booking'))->groupBy('o_Name','o_Email','o_Phone')->get();
                foreach($rows as $r)fputcsv($out,[$r->o_Name,$r->o_Email,$r->o_Phone,$r->c,$r->last_booking]);
            } elseif($type==='payments'){
                fputcsv($out,['Booking Reference','Customer','Email','Phone','Booking Amount','Paid Amount','Balance','Refunded Amount','Net Collected','Payment Status','Payment Method','Card Last4','Transaction Reference','Processor','Booking Status','Assigned Agent','Created Date']);

                $q=Booking::query();
                $term=trim((string)($filters['q'] ?? ''));
                if($term!==''){
                    $q->where(function($w) use($term){
                        $w->where('o_Name','like',"%{$term}%")->orWhere('o_Email','like',"%{$term}%")->orWhere('o_Phone','like',"%{$term}%");
                        if(ctype_digit($term)) $w->orWhere('id',(int)$term);
                        if(Schema::hasTable('payment_crm_meta')){
                            $w->orWhereExists(function($sub) use($term){
                                $sub->select(DB::raw(1))->from('payment_crm_meta')->whereColumn('payment_crm_meta.booking_id','bookings.id')
                                    ->where(function($x) use($term){$x->where('transaction_reference','like',"%{$term}%")->orWhere('processor','like',"%{$term}%")->orWhere('refund_reference','like',"%{$term}%");});
                            });
                        }
                    });
                }
                if(!empty($filters['booking_status'])) $q->where('status',$filters['booking_status']);
                if(!empty($filters['date_from'])) $q->whereDate('created_at','>=',$filters['date_from']);
                if(!empty($filters['date_to'])) $q->whereDate('created_at','<=',$filters['date_to']);
                if(!empty($filters['payment_status']) && Schema::hasTable('payment_crm_meta')){
                    $ps=$filters['payment_status'];
                    if($ps==='Untracked'){
                        $q->where(function($w){
                            $w->whereNotExists(function($sub){$sub->select(DB::raw(1))->from('payment_crm_meta')->whereColumn('payment_crm_meta.booking_id','bookings.id');})
                              ->orWhereExists(function($sub){$sub->select(DB::raw(1))->from('payment_crm_meta')->whereColumn('payment_crm_meta.booking_id','bookings.id')->where('payment_status','Untracked');});
                        });
                    }else{
                        $q->whereExists(function($sub) use($ps){$sub->select(DB::raw(1))->from('payment_crm_meta')->whereColumn('payment_crm_meta.booking_id','bookings.id')->where('payment_status',$ps);});
                    }
                }

                $methodFilter=trim((string)($filters['method'] ?? ''));
                $admins=DB::table('admins')->pluck('name','id');
                $q->orderBy('id')->chunk(500,function($rows) use($out,$methodFilter,$admins){
                    $ids=$rows->pluck('id')->all();
                    $meta=Schema::hasTable('payment_crm_meta') ? DB::table('payment_crm_meta')->whereIn('booking_id',$ids)->get()->keyBy('booking_id') : collect();
                    foreach($rows as $b){
                        $c=json_decode((string)$b->o_Credit,true) ?: [];
                        $n=preg_replace('/\D+/','',(string)($c['creditcard_no'] ?? $c['card_number'] ?? ''));
                        $brand='Card';
                        if($n){if(str_starts_with($n,'4'))$brand='Visa';elseif(preg_match('/^(5[1-5]|2[2-7])/',$n))$brand='Mastercard';elseif(preg_match('/^3[47]/',$n))$brand='American Express';elseif(preg_match('/^6/',$n))$brand='Discover / Other';}
                        if($methodFilter!=='' && $brand!==$methodFilter) continue;
                        $pm=$meta->get($b->id);
                        $fare=(float)preg_replace('/[^0-9.\-]/','',(string)$b->o_FullPrice);
                        $paid=$pm && $pm->paid_amount!==null ? (float)$pm->paid_amount : 0.0;
                        $refund=$pm && $pm->refund_amount!==null ? (float)$pm->refund_amount : 0.0;
                        $status=$pm->payment_status ?? 'Untracked';
                        $balance=max(0,$fare-$paid);
                        $net=max(0,$paid-$refund);
                        fputcsv($out,[
                            $b->prefix.$b->id,$b->o_Name,$b->o_Email,$b->o_Phone,$fare,$paid,$balance,$refund,$net,$status,$brand,
                            $n?substr($n,-4):'', $pm->transaction_reference ?? '',$pm->processor ?? '',$b->status,
                            ($pm && $pm->assigned_to ? ($admins[$pm->assigned_to] ?? ('Agent #'.$pm->assigned_to)) : 'Unassigned'),$b->created_at
                        ]);
                    }
                });
            } else {
                fputcsv($out,['ID','Type','Destination','Name','Contact','Email','Subject','Created']);
                DB::table('enquiries')->orderBy('id')->chunk(500,function($rows) use ($out){foreach($rows as $r)fputcsv($out,[$r->id,$r->type,$r->going_to,$r->name,$r->contact,$r->emailid,$r->subject,$r->created_at]);});
            }
            fclose($out);
        };
        return response()->streamDownload($callback,$filename,['Content-Type'=>'text/csv; charset=UTF-8']);
    }


    public function followUps(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        $items = DB::table('booking_remarks')
            ->leftJoin('bookings','booking_remarks.booking_id','=','bookings.id')
            ->select('booking_remarks.*','bookings.prefix','bookings.o_Name','bookings.o_Email','bookings.status')
            ->orderByDesc('booking_remarks.id')->paginate(20);
        $fresh = Booking::where('status','Fresh')->latest()->take(12)->get();
        return view('crm.follow-ups', compact('items','fresh'));
    }

    public function apiControl(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        $settings = DB::table('settings')->whereIn('name',['api_type','header_contact'])->pluck('value','name');
        $stats = [
            'airports' => DB::table('airports')->count(),
            'airlines' => DB::table('airlines')->count(),
            'active_rules' => DB::table('markups')->whereIn('is_active',['Yes','1'])->count(),
            'website_bookings' => Booking::where('source','Website')->count(),
        ];
        return view('crm.api-control', compact('settings','stats'));
    }

    public function tracking(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        $stages = DB::table('booking_stages')
            // Only live source bookings belong in operational Tracking.
            ->join('bookings','booking_stages.booking_id','=','bookings.id')
            ->leftJoin('admins','booking_stages.admin_id','=','admins.id');
        if (Schema::hasTable('crm_archives')) {
            $stages->whereNotExists(function($sub) {
                $sub->select(DB::raw(1))
                    ->from('crm_archives')
                    ->where('crm_archives.module','booking')
                    ->whereNull('crm_archives.restored_at')
                    ->whereRaw('CAST(crm_archives.record_key AS UNSIGNED) = bookings.id');
            });
        }
        $stages = $stages
            ->select('booking_stages.*','bookings.prefix','bookings.o_Name','bookings.o_Email','admins.name as admin_name')
            ->orderByDesc('booking_stages.id')->paginate(20);
        return view('crm.tracking', compact('stages'));
    }

    public function pagesManager(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $query = DB::table('contents');

        if ($request->filled('q')) {
            $q = trim((string)$request->q);
            $query->where(function($sub) use ($q) {
                $sub->where('title','like','%'.$q.'%')
                    ->orWhere('slug','like','%'.$q.'%')
                    ->orWhere('type','like','%'.$q.'%');
            });
        }

        $pages = $query->orderBy('sequence')->orderBy('title')->get();

        return view('crm.pages-manager', compact('pages'));
    }

    public function pageCreate(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        return view('crm.page-editor', [
            'page' => null,
            'mode' => 'create',
        ]);
    }

    public function pageStore(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $data = $this->validateWebsitePage($request);

        $slug = \Illuminate\Support\Str::slug($data['slug'] ?: $data['title']);
        if ($slug === '') {
            return back()->withInput()->with('crm_error','Please enter a valid page title or slug.');
        }

        if (DB::table('contents')->where('slug',$slug)->exists()) {
            return back()->withInput()->withErrors(['slug'=>'This page URL is already in use.']);
        }

        $banner = null;
        if ($request->hasFile('banner_file')) {
            $file = $request->file('banner_file');
            $dir = public_path('uploads/cms');
            if (!is_dir($dir)) @mkdir($dir, 0755, true);
            $name = 'page-banner-'.time().'-'.bin2hex(random_bytes(3)).'.'.$file->getClientOriginalExtension();
            $file->move($dir, $name);
            $banner = '/uploads/cms/'.$name;
        }

        $id = DB::table('contents')->insertGetId([
            'type' => $data['type'],
            'slug' => $slug,
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'banner' => $banner,
            'sequence' => $data['sequence'] ?? 0,
            'is_visible' => $request->boolean('is_visible') ? '1' : '0',
            'seo_title' => $data['seo_title'] ?: $data['title'],
            'seo_keyword' => $data['seo_keyword'] ?? '',
            'seo_description' => $data['seo_description'] ?? '',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('crm.page_edit',$id)
            ->with('crm_success','Website page created successfully.');
    }

    public function pageEdit(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $page = DB::table('contents')->where('id',$id)->first();
        abort_unless($page,404);

        return view('crm.page-editor', [
            'page' => $page,
            'mode' => 'edit',
        ]);
    }

    public function pageUpdate(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $page = DB::table('contents')->where('id',$id)->first();
        abort_unless($page,404);

        $data = $this->validateWebsitePage($request);

        $slug = \Illuminate\Support\Str::slug($data['slug'] ?: $data['title']);

        if (DB::table('contents')->where('slug',$slug)->where('id','!=',$id)->exists()) {
            return back()->withInput()->withErrors(['slug'=>'This page URL is already in use.']);
        }

        $banner = $page->banner;
        if ($request->hasFile('banner_file')) {
            $file = $request->file('banner_file');
            $dir = public_path('uploads/cms');
            if (!is_dir($dir)) @mkdir($dir, 0755, true);
            $name = 'page-banner-'.time().'-'.bin2hex(random_bytes(3)).'.'.$file->getClientOriginalExtension();
            $file->move($dir, $name);
            $banner = '/uploads/cms/'.$name;
        }

        DB::table('contents')->where('id',$id)->update([
            'type' => $data['type'],
            'slug' => $slug,
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'banner' => $banner,
            'sequence' => $data['sequence'] ?? 0,
            'is_visible' => $request->boolean('is_visible') ? '1' : '0',
            'seo_title' => $data['seo_title'] ?: $data['title'],
            'seo_keyword' => $data['seo_keyword'] ?? '',
            'seo_description' => $data['seo_description'] ?? '',
            'updated_at' => now(),
        ]);

        return back()->with('crm_success','Website page updated successfully.');
    }

    public function pageDelete(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $page = DB::table('contents')->where('id',$id)->first();
        abort_unless($page,404);

        DB::table('contents')->where('id',$id)->delete();

        return redirect()->route('crm.pages_manager')
            ->with('crm_success','Website page deleted.');
    }

    private function validateWebsitePage(Request $request): array
    {
        return $request->validate([
            'type' => ['required','in:Basic,Legal'],
            'title' => ['required','string','max:255'],
            'slug' => ['nullable','string','max:255'],
            'description' => ['nullable','string'],
            'banner_file' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:5120'],
            'sequence' => ['nullable','integer','min:0','max:9999'],
            'is_visible' => ['nullable'],
            'seo_title' => ['nullable','string','max:255'],
            'seo_keyword' => ['nullable','string','max:500'],
            'seo_description' => ['nullable','string','max:500'],
        ]);
    }

    public function destinationsManager(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $query = DB::table('destinations');

        if ($request->filled('q')) {
            $q = trim((string)$request->q);
            $query->where(function($sub) use ($q) {
                $sub->where('title','like','%'.$q.'%')
                    ->orWhere('slug','like','%'.$q.'%');
            });
        }

        $destinations = $query
            ->orderByRaw('CASE WHEN sequence IS NULL THEN 1 ELSE 0 END')
            ->orderBy('sequence')
            ->orderBy('title')
            ->get();

        return view('crm.destinations-manager', compact('destinations'));
    }

    public function destinationCreate(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        return view('crm.destination-editor', [
            'destination' => null,
            'mode' => 'create',
        ]);
    }

    public function destinationStore(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $data = $this->validateDestination($request);

        $slug = \Illuminate\Support\Str::slug($data['slug'] ?: $data['title']);

        if ($slug === '') {
            return back()->withInput()->withErrors([
                'slug' => 'Please enter a valid destination title or slug.'
            ]);
        }

        if (DB::table('destinations')->where('slug',$slug)->exists()) {
            return back()->withInput()->withErrors([
                'slug' => 'This destination URL is already in use.'
            ]);
        }

        if (!$request->hasFile('image_file')) {
            return back()->withInput()->withErrors([
                'image_file' => 'Please upload a destination image.'
            ]);
        }

        $dir = public_path('uploads/destinations');
        if (!is_dir($dir)) @mkdir($dir,0755,true);

        $file = $request->file('image_file');
        $name = 'dest-'.time().'-'.bin2hex(random_bytes(3)).'.'.$file->getClientOriginalExtension();
        $file->move($dir,$name);
        $image = '/uploads/destinations/'.$name;

        $banner = null;

        if ($request->hasFile('banner_file')) {
            $file = $request->file('banner_file');
            $name = 'banner-'.time().'-'.bin2hex(random_bytes(3)).'.'.$file->getClientOriginalExtension();
            $file->move($dir,$name);
            $banner = '/uploads/destinations/'.$name;
        }

        $id = DB::table('destinations')->insertGetId([
            'slug' => $slug,
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'image' => $image,
            'banner' => $banner,
            'sequence' => $data['sequence'] ?? null,
            'is_visible' => $request->boolean('is_visible') ? '1' : '0',
            'seo_title' => $data['seo_title'] ?: $data['title'],
            'seo_keyword' => $data['seo_keyword'] ?? '',
            'seo_description' => $data['seo_description'] ?? '',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('crm.destination_edit',$id)
            ->with('crm_success','Destination created successfully.');
    }

    public function destinationEdit(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $destination = DB::table('destinations')->where('id',$id)->first();
        abort_unless($destination,404);

        return view('crm.destination-editor', [
            'destination' => $destination,
            'mode' => 'edit',
        ]);
    }

    public function destinationUpdate(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $destination = DB::table('destinations')->where('id',$id)->first();
        abort_unless($destination,404);

        $data = $this->validateDestination($request);

        $slug = \Illuminate\Support\Str::slug($data['slug'] ?: $data['title']);

        if ($slug === '') {
            return back()->withInput()->withErrors([
                'slug' => 'Please enter a valid destination title or slug.'
            ]);
        }

        if (
            DB::table('destinations')
                ->where('slug',$slug)
                ->where('id','!=',$id)
                ->exists()
        ) {
            return back()->withInput()->withErrors([
                'slug' => 'This destination URL is already in use.'
            ]);
        }

        $image = $destination->image;
        $banner = $destination->banner;

        $dir = public_path('uploads/destinations');
        if (!is_dir($dir)) @mkdir($dir,0755,true);

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $name = 'dest-'.time().'-'.bin2hex(random_bytes(3)).'.'.$file->getClientOriginalExtension();
            $file->move($dir,$name);
            $image = '/uploads/destinations/'.$name;
        }

        if ($request->hasFile('banner_file')) {
            $file = $request->file('banner_file');
            $name = 'banner-'.time().'-'.bin2hex(random_bytes(3)).'.'.$file->getClientOriginalExtension();
            $file->move($dir,$name);
            $banner = '/uploads/destinations/'.$name;
        }

        DB::table('destinations')->where('id',$id)->update([
            'slug' => $slug,
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'image' => $image,
            'banner' => $banner,
            'sequence' => $data['sequence'] ?? null,
            'is_visible' => $request->boolean('is_visible') ? '1' : '0',
            'seo_title' => $data['seo_title'] ?: $data['title'],
            'seo_keyword' => $data['seo_keyword'] ?? '',
            'seo_description' => $data['seo_description'] ?? '',
            'updated_at' => now(),
        ]);

        return back()->with('crm_success','Destination updated successfully.');
    }

    public function destinationDelete(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $destination = DB::table('destinations')->where('id',$id)->first();
        abort_unless($destination,404);

        DB::table('destinations')->where('id',$id)->delete();

        return redirect()->route('crm.destinations_manager')
            ->with('crm_success','Destination deleted.');
    }

    private function validateDestination(Request $request): array
    {
        return $request->validate([
            'title' => ['required','string','max:255'],
            'slug' => ['nullable','string','max:255'],
            'description' => ['nullable','string'],
            'image_file' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:5120'],
            'banner_file' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:5120'],
            'sequence' => ['nullable','integer','min:0','max:9999'],
            'is_visible' => ['nullable'],
            'seo_title' => ['nullable','string','max:255'],
            'seo_keyword' => ['nullable','string','max:255'],
            'seo_description' => ['nullable','string','max:255'],
        ]);
    }

    public function travelGuidesManager(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $query = DB::table('blogs')
            ->leftJoin('categories', 'categories.id', '=', 'blogs.category_id')
            ->select('blogs.*', 'categories.title as category_title');

        if ($request->filled('q')) {
            $q = trim((string) $request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('blogs.title', 'like', '%'.$q.'%')
                    ->orWhere('blogs.slug', 'like', '%'.$q.'%')
                    ->orWhere('blogs.author', 'like', '%'.$q.'%')
                    ->orWhere('categories.title', 'like', '%'.$q.'%');
            });
        }

        $guides = $query
            ->orderByRaw('CASE WHEN blogs.sequence IS NULL THEN 1 ELSE 0 END')
            ->orderBy('blogs.sequence')
            ->orderByDesc('blogs.id')
            ->get();

        return view('crm.travel-guides-manager', compact('guides'));
    }

    public function travelGuideCreate(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $categories = DB::table('categories')
            ->orderByRaw('CASE WHEN sequence IS NULL THEN 1 ELSE 0 END')
            ->orderBy('sequence')
            ->orderBy('title')
            ->get();

        return view('crm.travel-guide-editor', [
            'guide' => null,
            'categories' => $categories,
            'mode' => 'create',
        ]);
    }

    public function travelGuideStore(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $data = $this->validateTravelGuide($request);

        $slug = \Illuminate\Support\Str::slug($data['slug'] ?: $data['title']);
        if ($slug === '') {
            return back()->withInput()
                ->with('crm_error', 'Please enter a valid Travel Guide title or slug.');
        }

        if (DB::table('blogs')->where('slug', $slug)->exists()) {
            return back()->withInput()
                ->withErrors(['slug' => 'This Travel Guide URL is already in use.']);
        }

        if (!$request->hasFile('image_file')) {
            return back()->withInput()->withErrors(['image_file' => 'Please upload a Travel Guide image.']);
        }

        $dir = public_path('uploads/cms');
        if (!is_dir($dir)) @mkdir($dir, 0755, true);

        $file = $request->file('image_file');
        $name = 'guide-image-'.time().'-'.bin2hex(random_bytes(3)).'.'.$file->getClientOriginalExtension();
        $file->move($dir, $name);
        $image = '/uploads/cms/'.$name;

        $banner = null;
        if ($request->hasFile('banner_file')) {
            $file = $request->file('banner_file');
            $name = 'guide-banner-'.time().'-'.bin2hex(random_bytes(3)).'.'.$file->getClientOriginalExtension();
            $file->move($dir, $name);
            $banner = '/uploads/cms/'.$name;
        }

        $id = DB::table('blogs')->insertGetId([
            'category_id' => $data['category_id'],
            'slug' => $slug,
            'title' => $data['title'],
            'short' => $data['short'],
            'description' => $data['description'] ?? null,
            'image' => $image,
            'banner' => $banner,
            'author' => $data['author'],
            'date' => $data['date'],
            'sequence' => $data['sequence'] ?? null,
            'is_visible' => $request->boolean('is_visible') ? '1' : '0',
            'seo_title' => $data['seo_title'] ?: $data['title'],
            'seo_keyword' => $data['seo_keyword'] ?? '',
            'seo_description' => $data['seo_description'] ?? '',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('crm.travel_guide_edit', $id)
            ->with('crm_success', 'Travel Guide created successfully.');
    }

    public function travelGuideEdit(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $guide = DB::table('blogs')->where('id', $id)->first();
        abort_unless($guide, 404);

        $categories = DB::table('categories')
            ->orderByRaw('CASE WHEN sequence IS NULL THEN 1 ELSE 0 END')
            ->orderBy('sequence')
            ->orderBy('title')
            ->get();

        return view('crm.travel-guide-editor', [
            'guide' => $guide,
            'categories' => $categories,
            'mode' => 'edit',
        ]);
    }

    public function travelGuideUpdate(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $guide = DB::table('blogs')->where('id', $id)->first();
        abort_unless($guide, 404);

        $data = $this->validateTravelGuide($request);

        $submittedSlug = trim((string) ($data['slug'] ?? ''));

        if ($submittedSlug === '' || $submittedSlug === (string) $guide->slug) {
            $slug = (string) $guide->slug;
        } else {
            $slug = \Illuminate\Support\Str::slug($submittedSlug);
            if ($slug === '') {
                return back()->withInput()
                    ->with('crm_error', 'Please enter a valid Travel Guide slug.');
            }
        }

        if (DB::table('blogs')
            ->where('slug', $slug)
            ->where('id', '!=', $id)
            ->exists()) {
            return back()->withInput()
                ->withErrors(['slug' => 'This Travel Guide URL is already in use.']);
        }

        $image = $guide->image;
        $banner = $guide->banner;

        $dir = public_path('uploads/cms');
        if (!is_dir($dir)) @mkdir($dir, 0755, true);

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $name = 'guide-image-'.time().'-'.bin2hex(random_bytes(3)).'.'.$file->getClientOriginalExtension();
            $file->move($dir, $name);
            $image = '/uploads/cms/'.$name;
        }

        if ($request->hasFile('banner_file')) {
            $file = $request->file('banner_file');
            $name = 'guide-banner-'.time().'-'.bin2hex(random_bytes(3)).'.'.$file->getClientOriginalExtension();
            $file->move($dir, $name);
            $banner = '/uploads/cms/'.$name;
        }

        DB::table('blogs')->where('id', $id)->update([
            'category_id' => $data['category_id'],
            'slug' => $slug,
            'title' => $data['title'],
            'short' => $data['short'],
            'description' => $data['description'] ?? null,
            'image' => $image,
            'banner' => $banner,
            'author' => $data['author'],
            'date' => $data['date'],
            'sequence' => $data['sequence'] ?? null,
            'is_visible' => $request->boolean('is_visible') ? '1' : '0',
            'seo_title' => $data['seo_title'] ?: $data['title'],
            'seo_keyword' => $data['seo_keyword'] ?? '',
            'seo_description' => $data['seo_description'] ?? '',
            'updated_at' => now(),
        ]);

        return back()->with('crm_success', 'Travel Guide updated successfully.');
    }

    public function travelGuideDelete(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $guide = DB::table('blogs')->where('id', $id)->first();
        abort_unless($guide, 404);

        DB::table('blogs')->where('id', $id)->delete();

        return redirect()->route('crm.travel_guides_manager')
            ->with('crm_success', 'Travel Guide deleted.');
    }

    private function validateTravelGuide(Request $request): array
    {
        return $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'short' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'banner_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'author' => ['required', 'string', 'max:100'],
            'date' => ['required', 'date_format:Y-m-d', 'max:100'],
            'sequence' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_visible' => ['nullable'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_keyword' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:255'],
        ]);
    }

    public function contentManager(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        abort_unless(
            \Illuminate\Support\Facades\Schema::hasTable('website_cms_settings'),
            503,
            'Run CMS migrations first.'
        );

        $settings = DB::table('website_cms_settings')->pluck('value','key');

        $groups = [
            'hero' => [
                'title' => 'Homepage Hero',
                'description' => 'Main banner content shown above the flight search.',
                'fields' => [
                    ['key'=>'home.hero.enabled','label'=>'Show Hero','type'=>'toggle','default'=>'1'],
                    ['key'=>'home.hero.badge','label'=>'Small Badge Text','type'=>'text','default'=>'Premium flight search'],
                    ['key'=>'home.hero.title1','label'=>'Main Heading — Line 1','type'=>'text','default'=>'Find better flights.'],
                    ['key'=>'home.hero.title2','label'=>'Main Heading — Line 2','type'=>'text','default'=>'Book with confidence.'],
                    ['key'=>'home.hero.subtitle','label'=>'Hero Description','type'=>'textarea','default'=>'Compare live flight options and continue through a clear booking experience.'],
                    ['key'=>'home.hero.background','label'=>'Hero Background Image','type'=>'image','default'=>'/assets/img/fareshunt-premium-hero-bg.png'],
                ],
            ],
            'search' => [
                'title' => 'Flight Search Box',
                'description' => 'Change the customer-facing labels without changing search logic.',
                'fields' => [
                    ['key'=>'home.search.from_label','label'=>'From Field Label','type'=>'text','default'=>'Flying from'],
                    ['key'=>'home.search.to_label','label'=>'To Field Label','type'=>'text','default'=>'Flying to'],
                    ['key'=>'home.search.round_trip_label','label'=>'Round Trip Tab Label','type'=>'text','default'=>'Round Trip'],
                    ['key'=>'home.search.one_way_label','label'=>'One Way Tab Label','type'=>'text','default'=>'One Way'],
                    ['key'=>'home.search.multi_city_label','label'=>'Multi-City Tab Label','type'=>'text','default'=>'Multi-City'],
                    ['key'=>'home.search.multicity_heading','label'=>'Multi-City Heading','type'=>'text','default'=>'Build your Multi-City trip'],
                    ['key'=>'home.search.multicity_help','label'=>'Multi-City Help Text','type'=>'text','default'=>'2 to 5 flights · dates must be in travel order'],
                    ['key'=>'home.search.multicity_add_button','label'=>'Add Flight Button Text','type'=>'text','default'=>'Add another flight'],
                    ['key'=>'home.search.dates_label','label'=>'Dates Group Label','type'=>'text','default'=>'DATES'],
                    ['key'=>'home.search.departure_label','label'=>'Departure Label','type'=>'text','default'=>'Departure'],
                    ['key'=>'home.search.return_label','label'=>'Return Label','type'=>'text','default'=>'Return'],
                    ['key'=>'home.search.passengers_label','label'=>'Passengers Label','type'=>'text','default'=>'Passengers'],
                    ['key'=>'home.search.adults_label','label'=>'Adults Label','type'=>'text','default'=>'Adults'],
                    ['key'=>'home.search.adults_help','label'=>'Adults Age Text','type'=>'text','default'=>'12+ years'],
                    ['key'=>'home.search.children_label','label'=>'Children Label','type'=>'text','default'=>'Children'],
                    ['key'=>'home.search.children_help','label'=>'Children Age Text','type'=>'text','default'=>'2–11 years'],
                    ['key'=>'home.search.infants_label','label'=>'Infants Label','type'=>'text','default'=>'Infants'],
                    ['key'=>'home.search.infants_help','label'=>'Infants Age Text','type'=>'text','default'=>'Under 2'],
                    ['key'=>'home.search.passenger_done','label'=>'Passenger Done Button','type'=>'text','default'=>'Done'],
                    ['key'=>'home.search.cabin_label','label'=>'Cabin Label','type'=>'text','default'=>'Cabin'],
                    ['key'=>'home.search.button','label'=>'Search Button Text','type'=>'text','default'=>'Search Flights'],
                    ['key'=>'home.search.secure','label'=>'Security Message','type'=>'text','default'=>'Secure search & checkout'],
                ],
            ],
            'trust' => [
                'title' => 'Search Trust Strip',
                'description' => 'The four short benefits below the main search box.',
                'fields' => [
                    ['key'=>'home.trust.1','label'=>'Trust Item 1','type'=>'text','default'=>'Secure booking'],
                    ['key'=>'home.trust.2','label'=>'Trust Item 2','type'=>'text','default'=>'Travel support'],
                    ['key'=>'home.trust.3','label'=>'Trust Item 3','type'=>'text','default'=>'Worldwide routes'],
                    ['key'=>'home.trust.4','label'=>'Trust Item 4','type'=>'text','default'=>'Clear fare summary'],
                ],
            ],
            'home_trust_strip' => [
                'title' => 'Homepage Trust Strip',
                'description' => 'The four trust benefits shown below the homepage deals.',
                'fields' => [
                    ['key'=>'home.trust_strip.1.title','label'=>'Item 1 Title','type'=>'text','default'=>'Best Price Guarantee'],
                    ['key'=>'home.trust_strip.1.subtitle','label'=>'Item 1 Subtitle','type'=>'text','default'=>'We match any price.'],
                    ['key'=>'home.trust_strip.2.title','label'=>'Item 2 Title','type'=>'text','default'=>'24/7 Expert Support'],
                    ['key'=>'home.trust_strip.2.subtitle','label'=>'Item 2 Subtitle','type'=>'text','default'=>'Real people, real support.'],
                    ['key'=>'home.trust_strip.3.title','label'=>'Item 3 Title','type'=>'text','default'=>'Secure Booking'],
                    ['key'=>'home.trust_strip.3.subtitle','label'=>'Item 3 Subtitle','type'=>'text','default'=>'Your data is always protected.'],
                    ['key'=>'home.trust_strip.4.title','label'=>'Item 4 Title','type'=>'text','default'=>'Flexible Options'],
                    ['key'=>'home.trust_strip.4.subtitle','label'=>'Item 4 Subtitle','type'=>'text','default'=>'Book with confidence.'],
                ],
            ],
            'hero_trust' => [
                'title' => 'Homepage Search Trust Strip',
                'description' => 'The five trust items shown directly below the homepage search box.',
                'fields' => [
                    ['key'=>'home.hero_trust.1','label'=>'Item 1','type'=>'text','default'=>'Secure Booking'],
                    ['key'=>'home.hero_trust.2','label'=>'Item 2','type'=>'text','default'=>'15+ Years Experience'],
                    ['key'=>'home.hero_trust.3','label'=>'Item 3','type'=>'text','default'=>'24/7 Support'],
                    ['key'=>'home.hero_trust.4','label'=>'Item 4','type'=>'text','default'=>'USA Specialists'],
                    ['key'=>'home.hero_trust.5','label'=>'Item 5','type'=>'text','default'=>'Best Price Guarantee'],
                ],
            ],
            'featured' => [
                'title' => 'Featured Flight Deals',
                'description' => 'Heading content above the featured live fare cards.',
                'fields' => [
                    ['key'=>'home.featured.enabled','label'=>'Show Section','type'=>'toggle','default'=>'1'],
                    ['key'=>'home.featured.kicker','label'=>'Small Heading','type'=>'text','default'=>'Featured fares'],
                    ['key'=>'home.featured.title','label'=>'Section Heading','type'=>'text','default'=>'Featured Flight Deals'],
                    ['key'=>'home.featured.subtitle','label'=>'Section Description','type'=>'textarea','default'=>'Live API fares load as the cards come into view.'],
                ],
            ],
            'international' => [
                'title' => 'International Destinations',
                'description' => 'Content above international destination cards.',
                'fields' => [
                    ['key'=>'home.international.enabled','label'=>'Show Section','type'=>'toggle','default'=>'1'],
                    ['key'=>'home.international.kicker','label'=>'Small Heading','type'=>'text','default'=>'International favorites'],
                    ['key'=>'home.international.title','label'=>'Section Heading','type'=>'text','default'=>'Top International Destinations'],
                    ['key'=>'home.international.subtitle','label'=>'Section Description','type'=>'textarea','default'=>'Popular long-haul city pairs with live fare previews.'],
                ],
            ],
            'domestic' => [
                'title' => 'US Domestic Routes',
                'description' => 'Content above domestic route cards.',
                'fields' => [
                    ['key'=>'home.domestic.enabled','label'=>'Show Section','type'=>'toggle','default'=>'1'],
                    ['key'=>'home.domestic.kicker','label'=>'Small Heading','type'=>'text','default'=>'Best-value city pairs'],
                    ['key'=>'home.domestic.title','label'=>'Section Heading','type'=>'text','default'=>'Top US Domestic Routes'],
                    ['key'=>'home.domestic.subtitle','label'=>'Section Description','type'=>'textarea','default'=>'Tap any route to run a live one-way search.'],
                ],
            ],
            'india' => [
                'title' => 'USA → India Deals',
                'description' => 'Content above the USA to India deals section.',
                'fields' => [
                    ['key'=>'home.india.enabled','label'=>'Show Section','type'=>'toggle','default'=>'1'],
                    ['key'=>'home.india.kicker','label'=>'Small Heading','type'=>'text','default'=>'USA → India'],
                    ['key'=>'home.india.title','label'=>'Section Heading','type'=>'text','default'=>'USA → India Flight Deals'],
                    ['key'=>'home.india.subtitle','label'=>'Section Description','type'=>'textarea','default'=>'Popular India routes with live fare previews.'],
                ],
            ],
            'premium' => [
                'title' => 'Business & First Class',
                'description' => 'Premium cabin section content.',
                'fields' => [
                    ['key'=>'home.premium.enabled','label'=>'Show Section','type'=>'toggle','default'=>'1'],
                    ['key'=>'home.premium.badge','label'=>'Premium Badge','type'=>'text','default'=>'Premium Cabin Deals'],
                    ['key'=>'home.premium.title','label'=>'Main Heading','type'=>'text','default'=>'Business & First Class'],
                    ['key'=>'home.premium.accent','label'=>'Highlighted Heading Text','type'=>'text','default'=>'Deals'],
                    ['key'=>'home.premium.subtitle','label'=>'Section Description','type'=>'textarea','default'=>'Live fare previews and elevated cabin styling.'],
                    ['key'=>'home.premium.view_all_text','label'=>'View All Button Text','type'=>'text','default'=>'View all deals'],
                    ['key'=>'home.premium.banner_enabled','label'=>'Show Promotional Banner','type'=>'toggle','default'=>'1'],
                    ['key'=>'home.premium.banner_image','label'=>'Banner Image','type'=>'image','default'=>'assets/img/fareshunt-premium-light-banner.png'],
                    ['key'=>'home.premium.banner_link','label'=>'Banner Link','type'=>'text','default'=>'/deals'],
                    ['key'=>'home.premium.banner_alt','label'=>'Banner Alt Text','type'=>'text','default'=>'Fly Premium, Pay Less — Business and First Class deals'],
                ],
            ],
            'holiday' => [
                'title' => 'Holiday Packages',
                'description' => 'Holiday Packages section content.',
                'fields' => [
                    ['key'=>'home.holiday.enabled','label'=>'Show Section','type'=>'toggle','default'=>'1'],
                    ['key'=>'home.holiday.kicker','label'=>'Small Heading','type'=>'text','default'=>'Holiday Packages'],
                    ['key'=>'home.holiday.title','label'=>'Section Heading','type'=>'text','default'=>'Holiday Packages'],
                    ['key'=>'home.holiday.subtitle','label'=>'Section Description','type'=>'textarea','default'=>'Flight + Hotel deals for your perfect vacation.'],
                ],
            ],
            'last_minute' => [
                'title' => 'Last Minute Deals',
                'description' => 'Last Minute Deals section content.',
                'fields' => [
                    ['key'=>'home.last_minute.enabled','label'=>'Show Section','type'=>'toggle','default'=>'1'],
                    ['key'=>'home.last_minute.kicker','label'=>'Small Heading','type'=>'text','default'=>'Last Minute'],
                    ['key'=>'home.last_minute.title','label'=>'Section Heading','type'=>'text','default'=>'Last Minute Deals'],
                    ['key'=>'home.last_minute.subtitle','label'=>'Section Description','type'=>'textarea','default'=>'Limited time only — check live availability before it changes.'],
                ],
            ],
            'footer_alerts' => [
                'title' => 'Footer Fare Alerts',
                'description' => 'The large subscription area at the top of the footer.',
                'fields' => [
                    ['key'=>'site.footer.alerts.enabled','label'=>'Show Fare Alerts','type'=>'toggle','default'=>'1'],
                    ['key'=>'site.footer.alerts.title','label'=>'Main Heading','type'=>'text','default'=>'Never Miss a Flight Deal'],
                    ['key'=>'site.footer.alerts.subtitle','label'=>'Description','type'=>'textarea','default'=>'Subscribe to fare alerts and get useful flight deal updates delivered to your inbox.'],
                    ['key'=>'site.footer.alerts.email_placeholder','label'=>'Email Placeholder','type'=>'text','default'=>'your@email.com'],
                    ['key'=>'site.footer.alerts.airport_placeholder','label'=>'Airport Placeholder','type'=>'text','default'=>'Home airport (e.g. JFK)'],
                    ['key'=>'site.footer.alerts.button','label'=>'Button Text','type'=>'text','default'=>'Get Alerts'],
                    ['key'=>'site.footer.alerts.privacy','label'=>'Privacy Note','type'=>'text','default'=>'No spam. Unsubscribe anytime. We respect your privacy.'],
                ],
            ],
            'footer_brand' => [
                'title' => 'Footer Brand Content',
                'description' => 'Company description and labels shown in the footer.',
                'fields' => [
                    ['key'=>'site.footer.description','label'=>'Footer Company Description','type'=>'textarea','default'=>'Premium flight search and travel support for domestic and international journeys.'],
                    ['key'=>'site.footer.toll_label','label'=>'Toll-Free Label','type'=>'text','default'=>'Toll Free'],
                    ['key'=>'site.footer.copyright_name','label'=>'Copyright Brand Name','type'=>'text','default'=>'FaresHunt'],
                    ['key'=>'site.footer.copyright_suffix','label'=>'Copyright Ending','type'=>'text','default'=>'All Rights Reserved.'],
                    ['key'=>'site.footer.disclaimer_title','label'=>'Disclaimer Heading','type'=>'text','default'=>'Important information'],
                    ['key'=>'site.footer.disclaimer','label'=>'Footer Disclaimer','type'=>'textarea','default'=>''],
                ],
            ],
            'travel_guides' => [
                'title' => 'Homepage Travel Guides',
                'description' => 'Customer-facing labels for the Travel Guides section on the homepage. Guide cards and article links are managed separately.',
                'fields' => [
                    ['key'=>'home.travel_guides.kicker','label'=>'Section Kicker','type'=>'text','default'=>'TRAVEL INSPIRATION'],
                    ['key'=>'home.travel_guides.title','label'=>'Section Heading','type'=>'text','default'=>'Travel Guides & Insights'],
                    ['key'=>'home.travel_guides.subtitle','label'=>'Section Description','type'=>'textarea','default'=>'Expert advice, destination ideas and practical travel tips from our specialists.'],
                    ['key'=>'home.travel_guides.view_all','label'=>'View All Link Text','type'=>'text','default'=>'View all guides'],
                    ['key'=>'home.travel_guides.min_read_suffix','label'=>'Read Time Suffix','type'=>'text','default'=>'min read'],
                    ['key'=>'home.travel_guides.read_label','label'=>'Guide Card Link Text','type'=>'text','default'=>'Read guide'],
                ],
            ],
            'travel_guide_listing' => [
                'title' => 'Travel Guide Listing Page',
                'description' => 'Customer-facing SEO, headings and card labels for the public /travel-guide page.',
                'fields' => [
                    ['key'=>'travel_guides.listing.seo_title','label'=>'SEO Title','type'=>'text','default'=>'Travel Guides & Insights | FaresHunt'],
                    ['key'=>'travel_guides.listing.seo_description','label'=>'SEO Description','type'=>'textarea','default'=>'Explore FaresHunt travel guides, destination ideas and practical flight-booking insights.'],
                    ['key'=>'travel_guides.listing.hero_kicker','label'=>'Hero Kicker','type'=>'text','default'=>'FARESHUNT TRAVEL DESK'],
                    ['key'=>'travel_guides.listing.hero_title','label'=>'Hero Heading','type'=>'text','default'=>'Travel Guides & Insights'],
                    ['key'=>'travel_guides.listing.hero_subtitle','label'=>'Hero Description','type'=>'textarea','default'=>'Expert destination inspiration, practical travel advice and smarter ways to plan your next journey.'],
                    ['key'=>'travel_guides.listing.section_kicker','label'=>'Listing Kicker','type'=>'text','default'=>'TRAVEL INSPIRATION'],
                    ['key'=>'travel_guides.listing.section_title','label'=>'Listing Heading','type'=>'text','default'=>'Latest Travel Guides'],
                    ['key'=>'travel_guides.listing.section_subtitle','label'=>'Listing Description','type'=>'textarea','default'=>'Fresh ideas and useful information from the FaresHunt travel desk.'],
                    ['key'=>'travel_guides.listing.min_read_suffix','label'=>'Read Time Suffix','type'=>'text','default'=>'min read'],
                    ['key'=>'travel_guides.listing.read_label','label'=>'Guide Card Link Text','type'=>'text','default'=>'Read guide'],
                    ['key'=>'travel_guides.listing.empty_text','label'=>'Empty State Text','type'=>'textarea','default'=>'New travel guides are being prepared. Please check back soon.'],
                ],
            ],
            'travel_guide_article' => [
                'title' => 'Travel Guide Article Page',
                'description' => 'Customer-facing labels and CTA text used on individual Travel Guide article pages.',
                'fields' => [
                    ['key'=>'travel_guides.article.back_label','label'=>'Back Link Text','type'=>'text','default'=>'All travel guides'],
                    ['key'=>'travel_guides.article.min_read_suffix','label'=>'Read Time Suffix','type'=>'text','default'=>'min read'],
                    ['key'=>'travel_guides.article.cta_kicker','label'=>'CTA Kicker','type'=>'text','default'=>'PLAN YOUR NEXT TRIP'],
                    ['key'=>'travel_guides.article.cta_title','label'=>'CTA Heading','type'=>'text','default'=>'Ready to search flights?'],
                    ['key'=>'travel_guides.article.cta_description','label'=>'CTA Description','type'=>'textarea','default'=>'Use FaresHunt flight search to compare available itineraries for your next journey.'],
                    ['key'=>'travel_guides.article.cta_button','label'=>'CTA Button Text','type'=>'text','default'=>'Search Flights'],
                    ['key'=>'travel_guides.article.empty_content','label'=>'Empty Article Content Text','type'=>'textarea','default'=>'Travel guide content coming soon.'],
                ],
            ],
            'footer_trust' => [
                'title' => 'Footer Trust Strip',
                'description' => 'The four trust items displayed above the footer link directory.',
                'fields' => [
                    ['key'=>'site.footer.trust.enabled','label'=>'Show Trust Strip','type'=>'toggle','default'=>'1'],
                    ['key'=>'site.footer.trust.1.title','label'=>'Item 1 Heading','type'=>'text','default'=>'Secure Booking'],
                    ['key'=>'site.footer.trust.1.sub','label'=>'Item 1 Description','type'=>'text','default'=>'Encrypted checkout'],
                    ['key'=>'site.footer.trust.2.title','label'=>'Item 2 Heading','type'=>'text','default'=>'Privacy Protected'],
                    ['key'=>'site.footer.trust.2.sub','label'=>'Item 2 Description','type'=>'text','default'=>'Customer data handled securely'],
                    ['key'=>'site.footer.trust.3.title','label'=>'Item 3 Heading','type'=>'text','default'=>'Safe Payments'],
                    ['key'=>'site.footer.trust.3.sub','label'=>'Item 3 Description','type'=>'text','default'=>'Protected payment flow'],
                    ['key'=>'site.footer.trust.4.title','label'=>'Item 4 Heading','type'=>'text','default'=>'Travel Support'],
                    ['key'=>'site.footer.trust.4.sub','label'=>'Item 4 Description','type'=>'text','default'=>'Help when you need it'],
                ],
            ],
        ];

        foreach ($groups as &$group) {
            foreach ($group['fields'] as &$field) {
                $field['value'] = $settings[$field['key']] ?? $field['default'];
            }
        }

        return view('crm.content-manager', compact('groups'));
    }

    public function contentManagerUpdate(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        abort_unless(
            \Illuminate\Support\Facades\Schema::hasTable('website_cms_settings'),
            503,
            'Run CMS migrations first.'
        );

        $content = (array) $request->input('content', []);

        $imageKeys = [
            'home.hero.background',
            'home.premium.banner_image',
        ];

        $request->validate([
            'content_images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        foreach ($imageKeys as $key) {
            if ($request->hasFile("content_images.$key")) {
                $file = $request->file("content_images.$key");
                $dir = public_path('uploads/cms');
                if (!is_dir($dir)) @mkdir($dir, 0755, true);

                $safeKey = str_replace('.', '-', $key);
                $name = $safeKey.'-'.time().'-'.bin2hex(random_bytes(3)).'.'.$file->getClientOriginalExtension();

                $file->move($dir, $name);
                $this->saveVisualCmsValue($key, '/uploads/cms/'.$name);
            }
        }

        $toggleKeys = array_filter(
            array_keys((array) $request->input('toggle_keys', []))
        );

        foreach ($content as $key => $value) {
            $this->saveVisualCmsValue(
                trim((string)$key),
                is_array($value) ? json_encode($value) : trim((string)$value)
            );
        }

        foreach ($toggleKeys as $key) {
            $this->saveVisualCmsValue(
                trim((string)$key),
                array_key_exists($key, $content) ? '1' : '0'
            );
        }

        return back()->with('crm_success', 'Website content updated successfully.');
    }

    private function saveVisualCmsValue(string $key, string $value): void
    {
        if ($key === '') return;

        $row = DB::table('website_cms_settings')->where('key', $key)->first();

        if ($row) {
            DB::table('website_cms_settings')->where('key', $key)->update([
                'value' => $value,
                'updated_at' => now(),
            ]);
            return;
        }

        DB::table('website_cms_settings')->insert([
            'group' => 'global',
            'key' => $key,
            'value' => $value,
            'type' => 'text',
            'label' => $key,
            'sort_order' => 999,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function footerManager(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        abort_unless(\Illuminate\Support\Facades\Schema::hasTable('website_cms_settings'), 503, 'Run CMS migrations first.');

        $settings = DB::table('website_cms_settings')->pluck('value','key');

        $columns = [];
        $defaults = [
            1 => 'Flights',
            2 => 'Top Destinations',
            3 => 'Company',
            4 => 'Support',
        ];

        foreach ($defaults as $column => $defaultTitle) {
            $links = [];
            $maxLinks = $column === 4 ? 9 : 6;
            for ($i = 1; $i <= $maxLinks; $i++) {
                $links[] = [
                    'slot' => $i,
                    'label' => $settings["site.footer.col{$column}.l{$i}"] ?? '',
                    'url' => $settings["site.footer.col{$column}.u{$i}"] ?? '',
                ];
            }

            $columns[] = [
                'column' => $column,
                'title' => $settings["site.footer.col{$column}.title"] ?? $defaultTitle,
                'links' => $links,
            ];
        }

        $bottomLinks = [];
        for ($i = 1; $i <= 5; $i++) {
            $bottomLinks[] = [
                'slot' => $i,
                'label' => $settings["site.footer.bottom.l{$i}"] ?? '',
                'url' => $settings["site.footer.bottom.u{$i}"] ?? '',
            ];
        }

        $websitePages = DB::table('contents')
            ->where('is_visible','1')
            ->orderBy('type')
            ->orderBy('title')
            ->get(['title','slug','type']);

        return view('crm.footer-manager', compact('columns','bottomLinks','websitePages'));
    }

    public function footerManagerUpdate(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        abort_unless(\Illuminate\Support\Facades\Schema::hasTable('website_cms_settings'), 503, 'Run CMS migrations first.');

        $data = $request->validate([
            'columns' => ['required','array'],
            'columns.*.title' => ['nullable','string','max:100'],
            'columns.*.links' => ['required','array'],
            'columns.*.links.*.label' => ['nullable','string','max:120'],
            'columns.*.links.*.url' => ['nullable','string','max:500'],
            'bottom' => ['nullable','array'],
            'bottom.*.label' => ['nullable','string','max:120'],
            'bottom.*.url' => ['nullable','string','max:500'],
        ]);

        foreach ($data['columns'] as $column => $payload) {
            $col = (int) $column;
            if ($col < 1 || $col > 4) continue;

            $this->saveFooterCmsValue("site.footer.col{$col}.title", trim((string)($payload['title'] ?? '')));

            foreach (($payload['links'] ?? []) as $slot => $link) {
                $i = (int) $slot;
                $maxLinks = $col === 4 ? 9 : 6;
                if ($i < 1 || $i > $maxLinks) continue;

                $this->saveFooterCmsValue("site.footer.col{$col}.l{$i}", trim((string)($link['label'] ?? '')));
                $this->saveFooterCmsValue("site.footer.col{$col}.u{$i}", trim((string)($link['url'] ?? '')));
            }
        }

        foreach (($data['bottom'] ?? []) as $slot => $link) {
            $i = (int) $slot;
            if ($i < 1 || $i > 5) continue;

            $this->saveFooterCmsValue("site.footer.bottom.l{$i}", trim((string)($link['label'] ?? '')));
            $this->saveFooterCmsValue("site.footer.bottom.u{$i}", trim((string)($link['url'] ?? '')));
        }

        return back()->with('crm_success', 'Footer links updated successfully.');
    }

    private function saveFooterCmsValue(string $key, string $value): void
    {
        $row = DB::table('website_cms_settings')->where('key', $key)->first();

        if ($row) {
            DB::table('website_cms_settings')->where('key', $key)->update([
                'value' => $value,
                'updated_at' => now(),
            ]);
            return;
        }

        DB::table('website_cms_settings')->insert([
            'group' => 'global',
            'key' => $key,
            'value' => $value,
            'type' => 'text',
            'label' => $key,
            'sort_order' => 999,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function cms(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $contents = DB::table('contents')->orderBy('sequence')->get();
        $blogs = DB::table('blogs')->orderByDesc('id')->take(10)->get();
        $destinations = DB::table('destinations')->orderByDesc('id')->take(10)->get();
        $offers = DB::table('offers')->orderByDesc('id')->take(10)->get();

        $cmsSettings = collect();
        $cmsCards = collect();

        if (Schema::hasTable('website_cms_settings')) {
            $cmsSettings = DB::table('website_cms_settings')
                ->orderBy('group')->orderBy('sort_order')->get()->groupBy('group');
        }

        if (Schema::hasTable('website_cms_cards')) {
            $cmsCards = DB::table('website_cms_cards')
                ->orderBy('section')->orderBy('sort_order')->orderBy('id')->get()->groupBy('section');
        }

        return view('crm.cms', compact(
            'contents','blogs','destinations','offers','cmsSettings','cmsCards'
        ));
    }

    public function cmsSettingsUpdate(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        abort_unless(Schema::hasTable('website_cms_settings'), 503, 'Run website CMS migration first.');

        $settings = (array) $request->input('settings', []);
        $files = $request->file('images', []);

        foreach ($settings as $key => $value) {
            $row = DB::table('website_cms_settings')->where('key', $key)->first();
            if (!$row) continue;

            if (($row->type ?? '') === 'toggle') {
                $value = $value ? '1' : '0';
            }

            // 7 Nov 2026: record who changed which CMS setting.
            $newCmsValue = is_array($value) ? json_encode($value) : (string) $value;
            if ((string) ($row->value ?? '') !== $newCmsValue) {
                $this->crmUniversalLog($request, 'cms_settings', (string) $key, 'value', 'update', mb_substr((string) ($row->value ?? ''), 0, 490), mb_substr($newCmsValue, 0, 490));
            }

            DB::table('website_cms_settings')->where('key', $key)->update([
                'value' => is_array($value) ? json_encode($value) : (string) $value,
                'updated_at' => now(),
            ]);
        }

        // Unchecked toggle inputs are not posted; explicitly turn them off.
        DB::table('website_cms_settings')->where('type','toggle')->get()->each(function($row) use ($settings, $request) {
            if (!array_key_exists($row->key, $settings)) {
                if ((string) ($row->value ?? '') !== '0') {
                    $this->crmUniversalLog($request, 'cms_settings', (string) $row->key, 'value', 'update', (string) ($row->value ?? ''), '0');
                }
                DB::table('website_cms_settings')->where('id',$row->id)->update([
                    'value'=>'0','updated_at'=>now()
                ]);
            }
        });

        if (is_array($files)) {
            foreach ($files as $key => $file) {
                if (!$file || !$file->isValid()) continue;
                $row = DB::table('website_cms_settings')->where('key',$key)->first();
                if (!$row || ($row->type ?? '') !== 'image') continue;

                $request->validate([
                    'images.'.$key => ['image','mimes:jpg,jpeg,png,webp','max:5120'],
                ]);

                $dir = public_path('uploads/cms');
                if (!is_dir($dir)) @mkdir($dir, 0755, true);
                $name = 'cms-'.preg_replace('/[^a-z0-9]+/i','-', $key).'-'.time().'.'.$file->getClientOriginalExtension();
                $file->move($dir, $name);

                $this->crmUniversalLog($request, 'cms_settings', (string) $key, 'value', 'update', mb_substr((string) ($row->value ?? ''), 0, 490), '/uploads/cms/'.$name);
                DB::table('website_cms_settings')->where('key',$key)->update([
                    'value'=>'/uploads/cms/'.$name,
                    'updated_at'=>now(),
                ]);
            }
        }

        return back()->with('crm_success','Website CMS settings saved. Changes are live immediately.');
    }

    public function cmsCardStore(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        abort_unless(Schema::hasTable('website_cms_cards'), 503, 'Run website CMS migration first.');

        $data = $request->validate([
            'section' => ['required','in:featured,international,domestic,india,business,first,holiday,last_minute'],
            'title' => ['required','string','max:150'],
            'subtitle' => ['nullable','string','max:255'],
            'from_code' => ['nullable','string','max:10'],
            'to_code' => ['nullable','string','max:10'],
            'city' => ['nullable','string','max:120'],
            'image_file' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:5120'],
            'badge' => ['nullable','string','max:80'],
            'cabin' => ['required','in:Econom,Peconom,Business,firstclass'],
            'days' => ['required','integer','min:1','max:365'],
            'airline' => ['nullable','string','max:120'],
            'duration' => ['nullable','string','max:80'],
            'sort_order' => ['nullable','integer','min:0','max:999'],
            'is_active' => ['nullable'],
            'trip_type' => ['nullable','in:round_trip,one_way'],
            'return_days' => ['nullable','integer','min:1','max:365'],
            'fare_mode' => ['nullable','in:manual,live'],
            'display_fare' => ['nullable','numeric','min:0','max:999999.99'],
        ]);

        $image = null;
        if ($request->hasFile('image_file')) {
            $dir = public_path('uploads/cms');
            if (!is_dir($dir)) @mkdir($dir, 0755, true);
            $file = $request->file('image_file');
            $name = 'deal-'.time().'-'.bin2hex(random_bytes(3)).'.'.$file->getClientOriginalExtension();
            $file->move($dir, $name);
            $image = '/uploads/cms/'.$name;
        }

        DB::table('website_cms_cards')->insert([
            'section'=>$data['section'],
            'title'=>$data['title'],
            'subtitle'=>$data['subtitle'] ?? null,
            'from_code'=>strtoupper($data['from_code'] ?? ''),
            'to_code'=>strtoupper($data['to_code'] ?? ''),
            'city'=>$data['city'] ?? null,
            'image'=>$image,
            'badge'=>$data['badge'] ?? null,
            'cabin'=>$data['cabin'],
            'days'=>$data['days'],
            'airline'=>$data['airline'] ?? null,
            'duration'=>$data['duration'] ?? null,
            'sort_order'=>$data['sort_order'] ?? 0,
            'is_active'=>$request->boolean('is_active', true),
            'trip_type'=>$data['trip_type'] ?? 'round_trip',
            'return_days'=>$data['return_days'] ?? 7,
            'fare_mode'=>$data['fare_mode'] ?? 'manual',
            'display_fare'=>($data['fare_mode'] ?? 'manual') === 'manual'
                ? ($data['display_fare'] ?? null)
                : null,
            'created_at'=>now(),'updated_at'=>now(),
        ]);

        return back()->with('crm_success','Website card added.');
    }

    public function cmsCardUpdate(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        $card = DB::table('website_cms_cards')->where('id',$id)->first();
        abort_unless($card,404);

        $data = $request->validate([
            'title' => ['required','string','max:150'],
            'subtitle' => ['nullable','string','max:255'],
            'from_code' => ['nullable','string','max:10'],
            'to_code' => ['nullable','string','max:10'],
            'city' => ['nullable','string','max:120'],
            'image_file' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:5120'],
            'badge' => ['nullable','string','max:80'],
            'cabin' => ['required','in:Econom,Peconom,Business,firstclass'],
            'days' => ['required','integer','min:1','max:365'],
            'airline' => ['nullable','string','max:120'],
            'duration' => ['nullable','string','max:80'],
            'sort_order' => ['nullable','integer','min:0','max:999'],
            'is_active' => ['nullable'],
            'trip_type' => ['nullable','in:round_trip,one_way'],
            'return_days' => ['nullable','integer','min:1','max:365'],
            'fare_mode' => ['nullable','in:manual,live'],
            'display_fare' => ['nullable','numeric','min:0','max:999999.99'],
        ]);

        $image = $card->image;
        if ($request->hasFile('image_file')) {
            $dir = public_path('uploads/cms');
            if (!is_dir($dir)) @mkdir($dir, 0755, true);
            $file = $request->file('image_file');
            $name = 'deal-'.time().'-'.bin2hex(random_bytes(3)).'.'.$file->getClientOriginalExtension();
            $file->move($dir, $name);
            $image = '/uploads/cms/'.$name;
        }

        DB::table('website_cms_cards')->where('id',$id)->update([
            'title'=>$data['title'],
            'subtitle'=>$data['subtitle'] ?? null,
            'from_code'=>strtoupper($data['from_code'] ?? ''),
            'to_code'=>strtoupper($data['to_code'] ?? ''),
            'city'=>$data['city'] ?? null,
            'image'=>$image,
            'badge'=>$data['badge'] ?? null,
            'cabin'=>$data['cabin'],
            'days'=>$data['days'],
            'airline'=>$data['airline'] ?? null,
            'duration'=>$data['duration'] ?? null,
            'sort_order'=>$data['sort_order'] ?? 0,
            'is_active'=>$request->boolean('is_active'),
            'trip_type'=>$data['trip_type'] ?? ($card->trip_type ?? 'round_trip'),
            'return_days'=>$data['return_days'] ?? ($card->return_days ?? 7),
            'fare_mode'=>$data['fare_mode'] ?? ($card->fare_mode ?? 'manual'),
            'display_fare'=>($data['fare_mode'] ?? ($card->fare_mode ?? 'manual')) === 'manual'
                ? ($data['display_fare'] ?? null)
                : null,
            'updated_at'=>now(),
        ]);

        return back()->with('crm_success','Website card updated.');
    }

    public function cmsCardDelete(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        DB::table('website_cms_cards')->where('id',$id)->delete();
        return back()->with('crm_success','Website card deleted.');
    }

    public function emailTemplates(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $query = DB::table('mail_templates');

        if ($request->filled('q')) {
            $q = trim((string) $request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('label', 'like', '%'.$q.'%')
                    ->orWhere('type', 'like', '%'.$q.'%')
                    ->orWhere('subject', 'like', '%'.$q.'%');
            });
        }

        $templates = $query->orderBy('type')->orderBy('label')->get();

        return view('crm.email-templates', compact('templates'));
    }

    public function emailTemplateEdit(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $template = DB::table('mail_templates')->where('id', $id)->first();
        abort_unless($template, 404);

        return view('crm.email-template-edit', compact('template'));
    }

    public function emailTemplateUpdate(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        abort_unless(DB::table('mail_templates')->where('id', $id)->exists(), 404);

        $data = $request->validate([
            'label' => ['required', 'string', 'max:150'],
            'type' => ['required', 'string', 'max:150'],
            'subject' => ['required', 'string', 'max:255'],
            'variable' => ['nullable', 'string'],
            'body' => ['nullable', 'string'],
            'tnc' => ['nullable', 'string'],
        ]);

        $payload = [
            'label' => $data['label'],
            'type' => $data['type'],
            'subject' => $data['subject'],
            'variable' => $data['variable'] ?? '',
            'body' => $data['body'] ?? '',
            'tnc' => $data['tnc'] ?? '',
        ];

        // Support both older mail_templates tables and Laravel timestamp-enabled tables.
        if (\Illuminate\Support\Facades\Schema::hasColumn('mail_templates', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::table('mail_templates')->where('id', $id)->update($payload);

        return redirect()
            ->route('crm.email_template_edit', $id)
            ->with('crm_success', 'Email template updated successfully.');
    }

    public function emailTemplatePreview(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $template = DB::table('mail_templates')->where('id', $id)->first();
        abort_unless($template, 404);

        $sample = [
            '$bookingid' => 'HTB123456',
            '$name' => 'Guest Traveller',
            '$c_name' => 'Guest Traveller',
            '$email' => 'guest@example.com',
            '$c_emailid' => 'guest@example.com',
            '$phone' => '+1 800 555 0123',
            '$c_phone' => '+1 800 555 0123',
            '$pnr' => 'ABC123',
            '$price' => '$699.99',
            '$totalprice' => '$699.99',
            '$passengers' => '1 Adult',
            '$sectorgos' => 'JFK → LHR · Example Airline · 10:25 AM',
            '$sectorreturns' => 'LHR → JFK · Example Airline · 06:15 PM',
            '$header_contact' => '+1 800 555 0123',
            '$content_emailid' => 'support@example.com',
        ];

        $subject = strtr((string) $template->subject, $sample);
        $body = strtr((string) ($template->body ?? ''), $sample);
        $tnc = strtr((string) ($template->tnc ?? ''), $sample);

        return view('crm.email-template-preview', compact('template', 'subject', 'body', 'tnc'));
    }

    public function communications(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $q = trim((string) $request->input('q', ''));
        $stage = trim((string) $request->input('stage', ''));
        $hasMeta = Schema::hasTable('lead_crm_meta');

        $query = DB::table('enquiries');

        $applyActiveLeadScope = function ($builder) {
            if (Schema::hasTable('crm_archives')) {
                $builder->whereNotExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('crm_archives')
                        ->where('crm_archives.module', 'lead')
                        ->whereNull('crm_archives.restored_at')
                        ->whereRaw('CAST(crm_archives.record_key AS UNSIGNED) = enquiries.id');
                });
            }
            return $builder;
        };

        $applyActiveLeadScope($query);

        if ($hasMeta) {
            $query->leftJoin('lead_crm_meta','lead_crm_meta.lead_id','=','enquiries.id')
                ->leftJoin('admins','lead_crm_meta.assigned_to','=','admins.id')
                ->select('enquiries.*','lead_crm_meta.stage as crm_stage','lead_crm_meta.priority as crm_priority',
                    'lead_crm_meta.next_action as crm_next_action','lead_crm_meta.next_follow_up_at as crm_next_follow_up_at',
                    'admins.name as crm_agent_name');
        } else {
            $query->select('enquiries.*');
        }

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('enquiries.name','like',"%{$q}%")
                  ->orWhere('enquiries.emailid','like',"%{$q}%")
                  ->orWhere('enquiries.contact','like',"%{$q}%")
                  ->orWhere('enquiries.subject','like',"%{$q}%")
                  ->orWhere('enquiries.going_to','like',"%{$q}%")
                  ->orWhere('enquiries.message','like',"%{$q}%");
                if (ctype_digit($q)) $w->orWhere('enquiries.id',(int)$q);
            });
        }
        if ($hasMeta && $stage !== '') $query->where('lead_crm_meta.stage',$stage);

        $enquiries = $query->orderByDesc('enquiries.id')->paginate(15)->withQueryString();
        $leadStages = ['New','Follow-up','Qualified','Quoted','Negotiation','Converted','Closed','Lost'];

        $remarkRows = DB::table('booking_remarks')
            ->leftJoin('admins','booking_remarks.admin_id','=','admins.id')
            ->select('booking_remarks.*','admins.name as admin_name')
            ->orderByDesc('booking_remarks.id')->take(60)->get();
        $remarkGroups = $remarkRows->groupBy('booking_id')->map(function ($items) {
            return (object)[
                'booking_id' => $items->first()->booking_id,
                'latest_at' => $items->first()->created_at,
                'latest_remark' => $items->first()->remark,
                'latest_admin' => $items->first()->admin_name,
                'count' => $items->count(),
                'items' => $items->take(4),
            ];
        })->values()->take(12);

        $totalQuery = $applyActiveLeadScope(DB::table('enquiries'));

        if ($hasMeta) {
            $newQuery = DB::table('enquiries')
                ->leftJoin('lead_crm_meta','lead_crm_meta.lead_id','=','enquiries.id');
            $applyActiveLeadScope($newQuery);
            $newQuery->where(function($w){
                $w->whereNull('lead_crm_meta.stage')
                  ->orWhere('lead_crm_meta.stage','New');
            });

            $openQuery = DB::table('enquiries')
                ->leftJoin('lead_crm_meta','lead_crm_meta.lead_id','=','enquiries.id');
            $applyActiveLeadScope($openQuery);
            $openQuery->where(function($w){
                $w->whereNull('lead_crm_meta.stage')
                  ->orWhereNotIn('lead_crm_meta.stage',['Converted','Closed','Lost']);
            });
        }

        $stats = [
            'total' => (clone $totalQuery)->count(),
            'new' => $hasMeta ? $newQuery->count() : (clone $totalQuery)->count(),
            'follow_up' => $hasMeta ? DB::table('lead_crm_meta')
                ->join('enquiries','lead_crm_meta.lead_id','=','enquiries.id')
                ->where('lead_crm_meta.stage','Follow-up')
                ->whereNotExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('crm_archives')
                        ->where('crm_archives.module', 'lead')
                        ->whereNull('crm_archives.restored_at')
                        ->whereRaw('CAST(crm_archives.record_key AS UNSIGNED) = enquiries.id');
                })
                ->count() : 0,
            'open' => $hasMeta ? $openQuery->count() : (clone $totalQuery)->count(),
        ];

        return view('crm.communications', compact('enquiries','remarkGroups','leadStages','stats','q','stage'));
    }

    public function communicationDetail(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $lead = DB::table('enquiries')->where('id',$id)->first();
        abort_unless($lead,404);

        $leadMeta = null;
        if (Schema::hasTable('lead_crm_meta')) {
            $leadMeta = DB::table('lead_crm_meta')
                ->leftJoin('admins','lead_crm_meta.assigned_to','=','admins.id')
                ->where('lead_crm_meta.lead_id',$id)
                ->select('lead_crm_meta.*','admins.name as agent_name')
                ->first();
        }

        if (!$leadMeta) {
            $leadMeta = (object)[
                'stage' => 'New', 'priority' => 'Normal', 'source' => null,
                'assigned_to' => null, 'agent_name' => null, 'next_action' => null,
                'next_follow_up_at' => null, 'travel_from' => null, 'travel_to' => null,
                'return_date' => null, 'adults' => null, 'children' => null, 'infants' => null,
                'cabin' => null, 'budget' => null, 'private_note' => null,
            ];
        }

        $activity = Schema::hasTable('lead_activity')
            ? DB::table('lead_activity')
                ->leftJoin('admins','lead_activity.admin_id','=','admins.id')
                ->where('lead_activity.lead_id',$id)
                ->select('lead_activity.*','admins.name as admin_name')
                ->orderByDesc('lead_activity.id')->get()
            : collect();

        $admins = DB::table('admins')->where('status','1')->orderBy('name')->get();
        $leadStages = ['New','Follow-up','Qualified','Quoted','Negotiation','Converted','Closed','Lost'];
        $leadPriorities = ['Low','Normal','High','Urgent'];
        $quotations = Schema::hasTable('lead_quotations')
            ? DB::table('lead_quotations')
                ->leftJoin('admins','lead_quotations.admin_id','=','admins.id')
                ->where('lead_quotations.lead_id',$id)
                ->select('lead_quotations.*','admins.name as admin_name')
                ->orderByDesc('lead_quotations.id')->get()
            : collect();

        $mailer = (string) config('mail.default', 'smtp');
        $smtp = (array) config('mail.mailers.smtp', []);
        $mailReady = $mailer === 'smtp'
            && !empty($smtp['host'])
            && filter_var((string) config('mail.from.address'), FILTER_VALIDATE_EMAIL);

        return view('crm.communication-detail', compact(
            'lead','leadMeta','activity','admins','leadStages','leadPriorities','quotations','mailReady'
        ));
    }

    public function communicationQuotationStore(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $lead = DB::table('enquiries')->where('id',$id)->first();
        abort_unless($lead,404);
        abort_unless(Schema::hasTable('lead_quotations'),503,'Run the Communications quotation migration first.');

        $data = $request->validate([
            'base_fare' => ['required','numeric','min:0'],
            'markup' => ['nullable','numeric','min:0'],
            'currency' => ['required','string',Rule::in(['USD','EUR','GBP','CAD','AUD','INR'])],
            'valid_until' => ['nullable','date','after_or_equal:today'],
            'itinerary_summary' => ['nullable','string','max:3000'],
            'notes' => ['nullable','string','max:5000'],
            'submit_action' => ['required','string',Rule::in(['draft','send'])],
            'quote_id' => ['nullable','integer'],
        ]);

        if ($data['submit_action'] === 'send' && !filter_var((string)$lead->emailid, FILTER_VALIDATE_EMAIL)) {
            return back()->withInput()->with('crm_error','A valid customer email address is required before sending a quotation.');
        }

        $baseFare = round((float)$data['base_fare'],2);
        $markup = round((float)($data['markup'] ?? 0),2);
        $total = round($baseFare + $markup,2);
        $adminId = (int)$request->session()->get('crm_admin_id') ?: null;
        $quote = null;
        if (!empty($data['quote_id'])) {
            $quote = DB::table('lead_quotations')->where('lead_id',$id)->where('id',(int)$data['quote_id'])->first();
            abort_unless($quote,404);
            if ($quote->status !== 'Draft') return back()->with('crm_error','Only Draft quotations can be edited or sent.');
        }

        if ($quote) {
            $quoteId = $quote->id;
            $quoteNumber = $quote->quote_number;
            DB::table('lead_quotations')->where('id',$quoteId)->update([
                'admin_id'=>$adminId,'currency'=>$data['currency'],'base_fare'=>$baseFare,'markup'=>$markup,
                'total_amount'=>$total,'valid_until'=>$data['valid_until'] ?? null,
                'itinerary_summary'=>$data['itinerary_summary'] ?? null,'notes'=>$data['notes'] ?? null,'updated_at'=>now(),
            ]);
        } else {
            $sequence = DB::table('lead_quotations')->where('lead_id',$id)->count() + 1;
            $quoteNumber = 'Q-ENQ'.$id.'-'.now()->format('Ymd').'-'.str_pad((string)$sequence,3,'0',STR_PAD_LEFT);
            $quoteId = DB::table('lead_quotations')->insertGetId([
            'lead_id' => $id,
            'admin_id' => $adminId,
            'quote_number' => $quoteNumber,
            'status' => 'Draft',
            'currency' => $data['currency'],
            'base_fare' => $baseFare,
            'markup' => $markup,
            'total_amount' => $total,
            'valid_until' => $data['valid_until'] ?? null,
            'itinerary_summary' => $data['itinerary_summary'] ?? null,
            'notes' => $data['notes'] ?? null,
            'sent_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
            ]);
        }

        $activityTitle = $quote ? 'Quotation draft updated' : 'Quotation draft saved';
        $activityDetails = $quoteNumber.' · '.$data['currency'].' '.number_format($total,2);

        if ($data['submit_action'] === 'send') {
            $customerName = e($lead->name ?: 'Customer');
            $itinerary = nl2br(e($data['itinerary_summary'] ?? 'Travel itinerary to be confirmed.'));
            $notes = nl2br(e($data['notes'] ?? ''));
            $valid = !empty($data['valid_until']) ? \Carbon\Carbon::parse($data['valid_until'])->format('d M Y') : 'Subject to availability';
            $currency = e($data['currency']);
            $subject = 'FaresHunt Quotation '.$quoteNumber;
            $message = '<div style="font-family:Arial,sans-serif;color:#17304f;max-width:720px;margin:auto;background:#ffffff;border:1px solid #e4eaf1;border-radius:18px;overflow:hidden">'
                .'<div style="padding:26px 30px;background:linear-gradient(135deg,#102a49,#2459df);color:#ffffff">'
                .'<div style="font-size:12px;letter-spacing:1.6px;font-weight:700;opacity:.8">FARESHUNT</div>'
                .'<h2 style="margin:8px 0 0;font-size:28px">Flight Quotation</h2>'
                .'<div style="margin-top:8px;font-size:13px;opacity:.88">Quotation '.e($quoteNumber).'</div>'
                .'</div>'
                .'<div style="padding:28px 30px">'
                .'<p style="font-size:15px">Hello <b>'.$customerName.'</b>,</p>'
                .'<p style="color:#5f7288;line-height:1.65">Thank you for your enquiry. Please find your travel quotation below.</p>'
                .'<div style="margin:20px 0;padding:16px 18px;background:#f7f9fc;border:1px solid #e6ecf2;border-radius:12px">'
                .'<div style="font-size:11px;color:#7b8ca0;text-transform:uppercase;font-weight:700">Itinerary</div>'
                .'<div style="margin-top:6px;font-size:15px;line-height:1.6">'.$itinerary.'</div>'
                .'</div>'
                .'<div style="display:block;margin:20px 0;padding:22px;border-radius:14px;background:#eaf2ff;color:#173e79">'
                .'<div style="font-size:11px;text-transform:uppercase;font-weight:700;letter-spacing:.6px">Total quotation</div>'
                .'<div style="font-size:30px;font-weight:800;margin-top:7px">'.$currency.' '.number_format($total,2).'</div>'
                .'<div style="font-size:12px;margin-top:6px;color:#4e6786">Final customer-facing amount</div>'
                .'</div>'
                .'<p><b>Valid until:</b> '.e($valid).'</p>'
                .(!empty($data['notes']) ? '<div style="margin-top:18px"><b>Fare notes & conditions</b><div style="margin-top:6px;color:#5f7288;line-height:1.65">'.$notes.'</div></div>' : '')
                .'<p style="margin-top:22px;color:#6c7e91;font-size:12px;line-height:1.6">Fares and availability remain subject to confirmation until ticketed. Ticket issuance is a separate booking operation.</p>'
                .'<p style="margin-top:22px">Thank you,<br><b>FaresHunt</b></p>'
                .'</div></div>';

            $sent = app(FlightController::class)->sendSmptEmail($lead->emailid,$subject,$message);
            if (!$sent) {
                if (Schema::hasTable('lead_activity')) {
                    DB::table('lead_activity')->insert([
                        'lead_id'=>$id,'admin_id'=>$adminId,'activity_type'=>'email',
                        'title'=>'Quotation email failed',
                        'details'=>$quoteNumber.' could not be sent. Check SMTP/mail settings.',
                        'created_at'=>now(),'updated_at'=>now(),
                    ]);
                }
                return back()->with('crm_error','Quotation saved as Draft, but email could not be sent. Check SMTP/mail settings.');
            }

            DB::table('lead_quotations')->where('id',$quoteId)->update([
                'status'=>'Sent','sent_at'=>now(),'updated_at'=>now(),
            ]);
            $activityTitle = 'Quotation sent';
            $activityDetails = $quoteNumber.' · '.$data['currency'].' '.number_format($total,2).' · '.$lead->emailid;

            if (Schema::hasTable('lead_crm_meta')) {
                $meta = DB::table('lead_crm_meta')->where('lead_id',$id)->first();
                $currentStage = $meta->stage ?? 'New';
                if (in_array($currentStage,['New','Follow-up','Qualified'],true)) {
                    if ($meta) {
                        DB::table('lead_crm_meta')->where('lead_id',$id)->update(['stage'=>'Quoted','updated_at'=>now()]);
                    } else {
                        DB::table('lead_crm_meta')->insert([
                            'lead_id'=>$id,'stage'=>'Quoted','priority'=>'Normal','created_at'=>now(),'updated_at'=>now()
                        ]);
                    }
                }
            }
        }

        if (Schema::hasTable('lead_activity')) {
            DB::table('lead_activity')->insert([
                'lead_id'=>$id,'admin_id'=>$adminId,'activity_type'=>'quotation',
                'title'=>$activityTitle,'details'=>$activityDetails,
                'created_at'=>now(),'updated_at'=>now(),
            ]);
        }

        return back()->with('crm_success',$data['submit_action'] === 'send' ? 'Quotation sent successfully.' : 'Quotation draft saved.');
    }

    public function communicationQuotationPrint(Request $request, int $id, int $quoteId)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $lead = DB::table('enquiries')->where('id',$id)->first();
        abort_unless($lead,404);
        abort_unless(Schema::hasTable('lead_quotations'),404);

        $quote = DB::table('lead_quotations')
            ->leftJoin('admins','lead_quotations.admin_id','=','admins.id')
            ->where('lead_quotations.lead_id',$id)
            ->where('lead_quotations.id',$quoteId)
            ->select('lead_quotations.*','admins.name as admin_name')
            ->first();
        abort_unless($quote,404);

        $leadMeta = Schema::hasTable('lead_crm_meta')
            ? DB::table('lead_crm_meta')->where('lead_id',$id)->first()
            : null;

        $agency = DB::table('settings')->whereIn('name',[
            'header_contact','content_emailid','footer_address','header_logo'
        ])->pluck('value','name');

        return view('crm.quotation-print', compact('lead','leadMeta','quote','agency'));
    }

    public function settings(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $admin = Admin::find($request->session()->get('crm_admin_id'));

        $agency = DB::table('settings')
            ->whereIn('name', [
                'header_contact',
                'content_whatsapp',
                'content_emailid',
                'footer_address',
                'api_type',
                'header_logo',
            ])
            ->pluck('value', 'name');

        // 7 Nov 2026: the staff list needs users.view (it was shown to anyone with settings.view);
        // only the columns the page shows are loaded (never password columns).
        $access = app(\App\Services\CrmAccessService::class);
        $viewerId = (int) $request->session()->get('crm_admin_id');
        $canViewTeam = $access->can($viewerId, 'users.view');
        $canManageTeam = $access->can($viewerId, 'users.manage');
        $team = collect();
        if ($canViewTeam) {
            $teamQuery = DB::table('admins')->orderBy('name')->select(['id','name','username','emailid','mobile','status']);
            if (Schema::hasColumn('admins','crm_deleted_at')) {
                $teamQuery->whereNull('crm_deleted_at');
            }
            $team = $teamQuery->get();
        }

        return view('crm.settings', compact('admin','agency','team','canViewTeam','canManageTeam'));
    }

    public function settingsContactUpdate(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $data = $request->validate([
            'header_contact' => ['nullable','string','max:50'],
            'content_whatsapp' => ['nullable','string','max:50'],
            'content_emailid' => ['nullable','email','max:190'],
            'footer_address' => ['nullable','string','max:500'],
        ]);

        foreach ($data as $name => $value) {
            // 7 Nov 2026: record who changed which public contact setting.
            $oldSetting = DB::table('settings')->where('name', $name)->value('value');
            if ((string) $oldSetting !== trim((string) $value)) {
                $this->crmUniversalLog($request, 'settings', (string) $name, 'value', 'update', mb_substr((string) $oldSetting, 0, 490), mb_substr(trim((string) $value), 0, 490));
            }
            $exists = DB::table('settings')->where('name', $name)->exists();

            if ($exists) {
                DB::table('settings')->where('name', $name)->update([
                    'value' => trim((string) $value),
                ]);
            } else {
                DB::table('settings')->insert([
                    'name' => $name,
                    'value' => trim((string) $value),
                ]);
            }
        }

        return back()->with(
            'crm_success',
            'Website contact details updated. Header, footer and support links now use the new values.'
        );
    }

    public function generic(Request $request, string $page)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        $titles = [
            'tasks' => ['Follow-ups', 'Fresh bookings and enquiries that need operational attention.'],
            'api-control' => ['API Control', 'Read-only operational health view. Existing API integrations are not modified here.'],
            'tracking' => ['Booking Tracking', 'Monitor booking status, source and recent activity.'],
            'cms' => ['Website CMS', 'Overview of content already stored for your travel site.'],
            'email-templates' => ['Email Templates', 'Existing reusable communication templates.'],
            'communications' => ['Communications', 'Quick contact workspace using existing customer details.'],
            'settings' => ['Settings', 'CRM profile and agency configuration overview.'],
        ];
        abort_unless(isset($titles[$page]),404);
        [$title,$description]=$titles[$page];
        $data=[];
        if($page==='tasks'){$data['bookings']=Booking::where('status','Fresh')->latest()->take(20)->get();$data['enquiries']=DB::table('enquiries')->latest()->take(20)->get();}
        elseif($page==='api-control'){$data['apiType']=DB::table('masters')->where('name','api_type')->value('value');$data['website']=Booking::where('source','Website')->count();$data['fareleader']=Booking::where('source','FareLeader')->count();$data['lastBooking']=Booking::latest()->first();}
        elseif($page==='tracking'){$data['bookings']=Booking::latest()->take(40)->get();}
        elseif($page==='cms'){$data['counts']=['Blogs'=>DB::table('blogs')->count(),'Destinations'=>DB::table('destinations')->count(),'Offers'=>DB::table('offers')->count(),'Contents'=>DB::table('contents')->count(),'FAQs'=>DB::table('faqs')->count()];}
        elseif($page==='email-templates'){$data['templates']=DB::table('mail_templates')->orderBy('id')->get();}
        elseif($page==='communications'){$data['customers']=Booking::select('o_Name','o_Email','o_Phone',DB::raw('MAX(created_at) last_booking'))->groupBy('o_Name','o_Email','o_Phone')->orderByDesc('last_booking')->take(30)->get();}
        elseif($page==='settings'){$data['admin']=Admin::find($request->session()->get('crm_admin_id'));$data['settings']=DB::table('masters')->whereIn('name',['header_contact','content_emailid','api_type'])->pluck('value','name');}
        return view('crm.generic', compact('title','description','page','data'));
    }


    private function crmUniversalLog(Request $request, string $module, string $recordKey, ?string $field, string $action, $oldValue = null, $newValue = null): void
    {
        if (!Schema::hasTable('crm_change_logs')) return;

        DB::table('crm_change_logs')->insert([
            'module' => $module,
            'record_key' => $recordKey,
            'field' => $field,
            'action' => $action,
            'old_value' => is_scalar($oldValue) || $oldValue === null ? (string)($oldValue ?? '') : json_encode($oldValue),
            'new_value' => is_scalar($newValue) || $newValue === null ? (string)($newValue ?? '') : json_encode($newValue),
            'admin_id' => (int)$request->session()->get('crm_admin_id') ?: null,
            'created_at' => now(),
        ]);
    }

    private function crmUniversalMetaUpsert(string $table, string $keyColumn, $keyValue, string $field, $value): void
    {
        abort_unless(Schema::hasTable($table), 503, "Required CRM table {$table} is missing.");
        abort_unless(Schema::hasColumn($table, $field), 422, 'This field is not available in the current database.');

        $existing = DB::table($table)->where($keyColumn, $keyValue)->first();

        if ($existing) {
            DB::table($table)->where($keyColumn, $keyValue)->update([
                $field => $value,
                'updated_at' => now(),
            ]);
        } else {
            DB::table($table)->insert([
                $keyColumn => $keyValue,
                $field => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function universalFieldUpdate(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $data = $request->validate([
            'module' => ['required','string',Rule::in(['booking','customer','lead','payment'])],
            'record_key' => ['required','string','max:255'],
            'field' => ['required','string','max:120'],
            'value' => ['nullable'],
            'return_to' => ['nullable','string','max:2000'],
        ]);

        $module = $data['module'];
        $key = $data['record_key'];
        $field = $data['field'];
        $value = $data['value'] ?? null;
        $oldValue = null;
        $customerNewEmail = null;

        if ($module === 'booking') {
            $booking = Booking::findOrFail((int)$key);

            $core = ['o_Name','o_Email','o_Phone','source'];
            $meta = ['pnr','ticket_number','payment_status','ticket_status','markup_amount','via_source','next_action','next_action_at','operations_note'];

            if (in_array($field, $core, true)) {
                $oldValue = $booking->{$field};
                if ($field === 'o_Email' && $value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return back()->with('crm_error','Please enter a valid passenger email address.');
                }
                $booking->{$field} = $value;
                $booking->save();
            } elseif (in_array($field, $meta, true)) {
                $existing = Schema::hasTable('booking_crm_meta')
                    ? DB::table('booking_crm_meta')->where('booking_id',$booking->id)->first()
                    : null;
                $oldValue = $existing->{$field} ?? null;
                $this->crmUniversalMetaUpsert('booking_crm_meta','booking_id',$booking->id,$field,$value);
            } else {
                abort(422, 'This booking field is protected or not editable.');
            }
        }

        if ($module === 'customer') {
            $email = $key;
            $booking = Booking::where('o_Email',$email)->latest()->firstOrFail();

            $coreMap = [
                'name' => 'o_Name',
                'phone' => 'o_Phone',
                'email' => 'o_Email',
            ];
            $meta = ['customer_status','customer_tier','vip_flag','assigned_to','preferred_airline','preferred_cabin','home_airport','preferred_contact','next_action','next_action_at','private_note'];

            if (isset($coreMap[$field])) {
                $column = $coreMap[$field];
                $oldValue = $booking->{$column};

                if ($field === 'email') {
                    $newEmail = trim((string)$value);
                    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                        return back()->with('crm_error','Please enter a valid customer email address.');
                    }

                    DB::transaction(function () use ($email,$newEmail) {
                        Booking::where('o_Email',$email)->update(['o_Email'=>$newEmail]);

                        if (Schema::hasTable('customer_crm_meta')) {
                            DB::table('customer_crm_meta')->where('email',$email)->update(['email'=>$newEmail,'updated_at'=>now()]);
                        }
                        if (Schema::hasTable('customer_activity')) {
                            DB::table('customer_activity')->where('customer_email',$email)->update(['customer_email'=>$newEmail]);
                        }
                    });
                    $key = $newEmail;
                    $customerNewEmail = $newEmail;
                } else {
                    Booking::where('o_Email',$email)->update([$column=>$value]);
                }
            } elseif (in_array($field,$meta,true)) {
                $existing = Schema::hasTable('customer_crm_meta')
                    ? DB::table('customer_crm_meta')->where('email',$email)->first()
                    : null;
                $oldValue = $existing->{$field} ?? null;
                $this->crmUniversalMetaUpsert('customer_crm_meta','email',$email,$field,$value);
            } else {
                abort(422, 'This customer field is protected or not editable.');
            }
        }

        if ($module === 'lead') {
            $id = (int)$key;
            $lead = DB::table('enquiries')->where('id',$id)->first();
            abort_unless($lead,404);

            $core = ['name','emailid','contact','going_to','checkin_date','occupant','subject','type'];
            $meta = ['stage','priority','source','assigned_to','next_action','next_follow_up_at','travel_from','travel_to','return_date','adults','children','infants','cabin','budget','private_note'];

            if (in_array($field,$core,true)) {
                abort_unless(Schema::hasColumn('enquiries',$field),422,'Lead field does not exist in this database.');
                $oldValue = $lead->{$field} ?? null;
                DB::table('enquiries')->where('id',$id)->update([$field=>$value,'updated_at'=>now()]);
            } elseif (in_array($field,$meta,true)) {
                $existing = Schema::hasTable('lead_crm_meta')
                    ? DB::table('lead_crm_meta')->where('lead_id',$id)->first()
                    : null;
                $oldValue = $existing->{$field} ?? null;
                $this->crmUniversalMetaUpsert('lead_crm_meta','lead_id',$id,$field,$value);
            } else {
                abort(422, 'This lead field is protected or not editable.');
            }
        }

        if ($module === 'payment') {
            $id = (int)$key;
            $booking = Booking::findOrFail($id);
            $meta = ['payment_status','transaction_reference','processor','paid_amount','refund_amount','refund_reference','assigned_to','next_action','next_action_at','operations_note','refund_reason'];

            if (!in_array($field,$meta,true)) {
                abort(422, 'Payment card credentials and protected payment fields cannot be edited here.');
            }

            $existing = Schema::hasTable('payment_crm_meta')
                ? DB::table('payment_crm_meta')->where('booking_id',$id)->first()
                : null;
            $oldValue = $existing->{$field} ?? null;
            $this->crmUniversalMetaUpsert('payment_crm_meta','booking_id',$id,$field,$value);
        }

        $this->crmUniversalLog($request,$module,$key,$field,($value === null || $value === '') ? 'clear' : 'update',$oldValue,$value);

        if ($module === 'customer' && $field === 'email' && $customerNewEmail) {
            return redirect()->route('crm.customer_detail',['email'=>$customerNewEmail])->with('crm_success','Customer email updated successfully.');
        }

        $returnTo = $data['return_to'] ?? null;
        if ($returnTo && str_starts_with($returnTo, url('/'))) {
            return redirect($returnTo)->with('crm_success','Field updated successfully.');
        }

        return back()->with('crm_success','Field updated successfully.');
    }

    public function universalArchive(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $data = $request->validate([
            'module' => ['required','string',Rule::in(['booking','customer','lead','payment'])],
            'record_key' => ['required','string','max:255'],
            'label' => ['nullable','string','max:255'],
        ]);

        abort_unless(Schema::hasTable('crm_archives'),503,'Run the CRM Universal Actions migration first.');

        $exists = DB::table('crm_archives')
            ->where('module',$data['module'])
            ->where('record_key',$data['record_key'])
            ->whereNull('restored_at')
            ->exists();

        if (!$exists) {
            DB::table('crm_archives')->insert([
                'module'=>$data['module'],
                'record_key'=>$data['record_key'],
                'label'=>$data['label'] ?? null,
                'payload'=>null,
                'admin_id'=>(int)$request->session()->get('crm_admin_id') ?: null,
                'archived_at'=>now(),
                'restored_at'=>null,
                'created_at'=>now(),
                'updated_at'=>now(),
            ]);
            $this->crmUniversalLog($request,$data['module'],$data['record_key'],null,'archive',null,$data['label'] ?? null);
        }

        return redirect()->route('crm.universal_trash')->with('crm_success','Record moved to CRM Trash. Source data was not permanently deleted.');
    }

    public function universalRestore(Request $request, int $archiveId)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        abort_unless(Schema::hasTable('crm_archives'),503,'Run the CRM Universal Actions migration first.');
        $archive = DB::table('crm_archives')->where('id',$archiveId)->first();
        abort_unless($archive,404);

        DB::table('crm_archives')->where('id',$archiveId)->update([
            'restored_at'=>now(),
            'updated_at'=>now(),
        ]);

        $this->crmUniversalLog($request,$archive->module,$archive->record_key,null,'restore',$archive->label,null);

        return back()->with('crm_success','Record restored.');
    }

    public function universalTrash(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $archives = Schema::hasTable('crm_archives')
            ? DB::table('crm_archives')
                ->leftJoin('admins','crm_archives.admin_id','=','admins.id')
                ->whereNull('crm_archives.restored_at')
                // Do not surface stale/orphan Trash rows after source-data cleanup.
                ->where(function($q) {
                    $q->where(function($x) {
                        $x->whereIn('crm_archives.module',['booking','payment'])
                          ->whereExists(function($sub) {
                              $sub->select(DB::raw(1))
                                  ->from('bookings')
                                  ->whereRaw('bookings.id = CAST(crm_archives.record_key AS UNSIGNED)');
                          });
                    })->orWhere(function($x) {
                        $x->where('crm_archives.module','lead')
                          ->whereExists(function($sub) {
                              $sub->select(DB::raw(1))
                                  ->from('enquiries')
                                  ->whereRaw('enquiries.id = CAST(crm_archives.record_key AS UNSIGNED)');
                          });
                    })->orWhere(function($x) {
                        $x->where('crm_archives.module','customer')
                          ->whereExists(function($sub) {
                              $sub->select(DB::raw(1))
                                  ->from('bookings')
                                  ->whereRaw('BINARY LOWER(TRIM(bookings.o_Email)) = BINARY LOWER(TRIM(crm_archives.record_key))');
                          });
                    });
                })
                ->select('crm_archives.*','admins.name as admin_name')
                ->orderByDesc('crm_archives.archived_at')
                ->paginate(30)
            : collect();

        $canPermanentDelete = $this->crmRoleSlug($request) === 'super-admin';

        return view('crm.universal-trash', compact('archives','canPermanentDelete'));
    }

    public function universalPermanentDelete(Request $request, int $archiveId)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        abort_unless($this->crmRoleSlug($request) === 'super-admin', 403, 'Only a Super Admin can permanently delete CRM records.');
        abort_unless(Schema::hasTable('crm_archives'), 503, 'Run the CRM Universal Actions migration first.');

        $request->validate([
            'confirmation' => ['required', 'string', Rule::in(['DELETE'])],
        ]);

        $archive = DB::table('crm_archives')
            ->where('id', $archiveId)
            ->whereNull('restored_at')
            ->first();
        abort_unless($archive, 404);

        $deleteBookingData = function (int $bookingId): void {
            $tables = [
                'booking_crm_meta','booking_assigns','booking_authorizations','booking_authorization_documents',
                'booking_card_metadata','booking_docs','booking_fare_history','booking_financials','booking_remarks',
                'booking_service_history','booking_stages','booking_status_history','crm_manual_booking_meta',
                'payment_crm_meta','payment_activity','crm_email_logs','crm_followups','crm_followup_audit','crm_tracking_events',
            ];

            foreach ($tables as $table) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, 'booking_id')) {
                    DB::table($table)->where('booking_id', $bookingId)->delete();
                }
            }

            if (Schema::hasTable('bookings')) {
                DB::table('bookings')->where('id', $bookingId)->delete();
            }
        };

        DB::transaction(function () use ($request, $archive, $deleteBookingData) {
            $module = (string) $archive->module;
            $key = (string) $archive->record_key;

            if ($module === 'booking') {
                $deleteBookingData((int) $key);
            } elseif ($module === 'payment') {
                $bookingId = (int) $key;
                foreach (['payment_activity','payment_crm_meta'] as $table) {
                    if (Schema::hasTable($table) && Schema::hasColumn($table, 'booking_id')) {
                        DB::table($table)->where('booking_id', $bookingId)->delete();
                    }
                }
            } elseif ($module === 'lead') {
                $leadId = (int) $key;
                foreach (['lead_activity','lead_quotations','lead_crm_meta','crm_followups','crm_followup_audit'] as $table) {
                    if (Schema::hasTable($table) && Schema::hasColumn($table, 'lead_id')) {
                        DB::table($table)->where('lead_id', $leadId)->delete();
                    }
                }
                if (Schema::hasTable('enquiries')) {
                    DB::table('enquiries')->where('id', $leadId)->delete();
                }
            } elseif ($module === 'customer') {
                $email = trim($key);
                $bookingIds = Schema::hasTable('bookings')
                    ? DB::table('bookings')
                        ->whereRaw('BINARY LOWER(TRIM(o_Email)) = BINARY LOWER(TRIM(?))', [$email])
                        ->pluck('id')
                    : collect();

                foreach ($bookingIds as $bookingId) {
                    $deleteBookingData((int) $bookingId);
                }

                foreach ([['customer_crm_meta','email'],['customer_activity','customer_email']] as [$table,$column]) {
                    if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                        DB::table($table)
                            ->whereRaw('BINARY LOWER(TRIM('.$column.')) = BINARY LOWER(TRIM(?))', [$email])
                            ->delete();
                    }
                }
            } else {
                abort(422, 'Unsupported CRM Trash module.');
            }

            $this->crmUniversalLog(
                $request,
                $module,
                $key,
                null,
                'permanent_delete',
                $archive->label,
                'Source record permanently deleted from CRM Trash.'
            );

            DB::table('crm_archives')
                ->where('module', $module)
                ->where('record_key', $key)
                ->delete();
        });

        return redirect()->route('crm.universal_trash')
            ->with('crm_success', 'Record permanently deleted. This action cannot be undone.');
    }


}