<?php

namespace App\Http\Middleware;

use App\Services\CrmAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CrmPermissionGate
{
    public function __construct(private CrmAccessService $access) {}

    public function handle(Request $request, Closure $next): Response
    {
        $routeName = (string) optional($request->route())->getName();

        $isCanonicalCrmRoute = str_starts_with($routeName, 'crm.');
        $isLegacyCrmPath = $request->is('crm') || $request->is('crm/*');
        $isCrmHost = strtolower($request->getHost()) === 'crm.fareshunt.com';

        if (!$isCanonicalCrmRoute && !$isLegacyCrmPath && !$isCrmHost) {
            return $next($request);
        }
        $normalizedRouteName = str_starts_with($routeName, 'legacy.crm.')
            ? substr($routeName, strlen('legacy.'))
            : $routeName;
        if (in_array($normalizedRouteName, ['crm.login','crm.authenticate','crm.logout'], true)) {
            return $next($request);
        }

        $adminId = (int)$request->session()->get('crm_admin_id');
        if (!$adminId) {
            return $next($request); // Existing controller guards will handle authentication.
        }

        $permission = $this->access->permissionForRequest($request);
        // Only explicitly exempt auth routes may have no permission. An unmapped or unnamed
        // CRM route must never pass through the gate for an authenticated CRM user.
        if (!$permission || !$this->access->can($adminId, $permission)) {
            abort(403, 'You do not have permission to perform this CRM action.');
        }

        // Exports need both the export capability and visibility into the exported module.
        // This prevents a user with generic report-export access from exporting payments,
        // customer records, or bookings they are not allowed to view.
        if ($normalizedRouteName === 'crm.export') {
            $modulePermission = match ((string) $request->route('type')) {
                'bookings' => 'bookings.view',
                'customers' => 'customers.view',
                'payments' => 'payments.view',
                'leads' => 'leads.view',
                default => null,
            };
            if (!$modulePermission || !$this->access->can($adminId, $modulePermission)) {
                abort(403, 'You do not have permission to export this CRM module.');
            }
        }

        return $next($request);
    }
}