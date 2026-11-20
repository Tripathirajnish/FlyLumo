<?php

namespace App\Http\Controllers;

use App\Services\CrmAccessService;
use App\Services\FareRulesV2\FareRulePublishingEnvironmentGuardV2;
use App\Services\FareRulesV2\LegacyFareRuleApprovalService;
use Illuminate\Http\Request;

final class LegacyFareRuleReviewController extends Controller
{
    private function checker(Request $request): int
    {
        app(FareRulePublishingEnvironmentGuardV2::class)->assertAllowed();

        $adminId = (int) $request->session()->get('crm_admin_id', 0);

        if ($adminId <= 0) {
            abort(403, 'CRM authentication required');
        }

        try {
            $allowed = app(CrmAccessService::class)
                ->can($adminId, 'fare_rules_v2.approve');
        } catch (\Throwable $e) {
            $allowed = false;
        }

        abort_unless($allowed, 403, 'Independent Checker permission required');

        return $adminId;
    }

    public function approve(Request $request, int $requestId)
    {
        $adminId = $this->checker($request);

        $result = app(LegacyFareRuleApprovalService::class)
            ->review($adminId, $requestId, true);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'result' => $result,
            ]);
        }

        return redirect()->route('crm.fare_rules')
            ->with('success', 'Fare rule approved and applied to pricing rules.');
    }

    public function reject(Request $request, int $requestId)
    {
        $adminId = $this->checker($request);

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $result = app(LegacyFareRuleApprovalService::class)
            ->review($adminId, $requestId, false, $data['reason']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'result' => $result,
            ]);
        }

        return redirect()->route('crm.fare_rules')
            ->with('success', 'Fare rule request rejected. Pricing unchanged.');
    }
}
