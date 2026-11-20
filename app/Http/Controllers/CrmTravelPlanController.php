<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CrmTravelPlanController extends Controller
{
    private function guard(Request $request)
    {
        if (!$request->session()->has('crm_admin_id')) {
            return redirect()->route('crm.login');
        }

        abort_unless(
            Schema::hasTable('crm_travel_plans')
            && Schema::hasTable('crm_travel_plan_trips'),
            503,
            'Run the Travel Plans migration to enable this module.'
        );

        return null;
    }

    private function statuses(): array
    {
        return [
            'Pending Payment',
            'Active',
            'Completed',
            'Cancelled',
            'Expired',
        ];
    }

    private function paymentStatuses(): array
    {
        return [
            'Unpaid',
            'Partially Paid',
            'Paid',
            'Refunded',
        ];
    }

    public function index(Request $request)
    {
        if ($redirect = $this->guard($request)) {
            return $redirect;
        }

        $status = trim((string) $request->query('status', ''));
        $paymentStatus = trim((string) $request->query('payment_status', ''));
        $q = trim((string) $request->query('q', ''));

        $query = DB::table('crm_travel_plans as p')
            ->leftJoin('crm_travel_offers as o', 'o.id', '=', 'p.travel_offer_id')
            ->select(
                'p.*',
                'o.reference as offer_reference',
                'o.title as offer_title'
            )
            ->orderByDesc('p.created_at');

        if ($status !== '' && in_array($status, $this->statuses(), true)) {
            $query->where('p.status', $status);
        }

        if ($paymentStatus !== '' && in_array($paymentStatus, $this->paymentStatuses(), true)) {
            $query->where('p.payment_status', $paymentStatus);
        }

        if ($q !== '') {
            $query->where(function ($where) use ($q) {
                $where->where('p.reference', 'like', "%{$q}%")
                    ->orWhere('p.customer_name', 'like', "%{$q}%")
                    ->orWhere('p.customer_email', 'like', "%{$q}%")
                    ->orWhere('p.customer_phone', 'like', "%{$q}%")
                    ->orWhere('o.reference', 'like', "%{$q}%")
                    ->orWhere('o.title', 'like', "%{$q}%");
            });
        }

        $plans = $query->paginate(25)->withQueryString();

        $counts = [
            'all' => DB::table('crm_travel_plans')->count(),
            'pending' => DB::table('crm_travel_plans')->where('status', 'Pending Payment')->count(),
            'active' => DB::table('crm_travel_plans')->where('status', 'Active')->count(),
            'unpaid' => DB::table('crm_travel_plans')->where('payment_status', 'Unpaid')->count(),
            'paid' => DB::table('crm_travel_plans')->where('payment_status', 'Paid')->count(),
        ];

        return view('crm.travel-plans.index', [
            'plans' => $plans,
            'counts' => $counts,
            'statuses' => $this->statuses(),
            'paymentStatuses' => $this->paymentStatuses(),
            'activeStatus' => $status,
            'activePaymentStatus' => $paymentStatus,
            'q' => $q,
        ]);
    }

    public function show(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) {
            return $redirect;
        }

        $plan = DB::table('crm_travel_plans as p')
            ->leftJoin('crm_travel_offers as o', 'o.id', '=', 'p.travel_offer_id')
            ->leftJoin('crm_travel_offer_versions as v', 'v.id', '=', 'p.offer_version_id')
            ->select(
                'p.*',
                'o.reference as offer_reference',
                'o.title as offer_title',
                'v.version as offer_version_number'
            )
            ->where('p.id', $id)
            ->first();

        abort_unless($plan, 404);

        $trips = DB::table('crm_travel_plan_trips')
            ->where('travel_plan_id', $id)
            ->orderByRaw("CASE WHEN trip_type = 'Domestic' THEN 0 ELSE 1 END")
            ->orderBy('sequence')
            ->get();

        $activity = Schema::hasTable('crm_travel_plan_activity')
            ? DB::table('crm_travel_plan_activity')
                ->where('travel_plan_id', $id)
                ->orderByDesc('created_at')
                ->get()
            : collect();

        return view('crm.travel-plans.show', [
            'plan' => $plan,
            'trips' => $trips,
            'activity' => $activity,
        ]);
    }
}
