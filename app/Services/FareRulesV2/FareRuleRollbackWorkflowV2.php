<?php

namespace App\Services\FareRulesV2;

use App\Services\CrmAccessService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

final class FareRuleRollbackWorkflowV2
{
    private const WORKFLOW_WRITES_ENABLED = true;
    private const ROLLBACK_EXECUTION_ENABLED = false;

    private function assertStaging(): void
    {
        if (
            config('app.env') !== 'staging'
            || DB::connection()->getDatabaseName() !== 'fareshunt_v2_staging'
        ) {
            throw new RuntimeException(
                'Fare Rules V2 workflow restricted to staging'
            );
        }
    }

    public function status(): array
    {
        $this->assertStaging();

        return [
            'service' => 'fare_rules_v2_rollback_workflow',
            'request_creation_enabled' => self::WORKFLOW_WRITES_ENABLED,
            'approval_enabled' => false,
            'rejection_enabled' => false,
            'execution_enabled' => self::ROLLBACK_EXECUTION_ENABLED,
            'authenticated_requester_required' => true,
            'independent_approver_required' => true,
            'persistent_audit_required' => true,
        ];
    }

    public function requestRollback(
        int $authenticatedRequesterId,
        int $ruleId,
        int $targetVersion,
        string $reason
    ): array {
        $this->assertStaging();

        if (!self::WORKFLOW_WRITES_ENABLED) {
            throw new RuntimeException('Request creation disabled');
        }

        if ($authenticatedRequesterId <= 0 || $ruleId <= 0 || $targetVersion <= 0) {
            throw new InvalidArgumentException('Invalid request identifiers');
        }

        $reason = trim($reason);

        if (mb_strlen($reason) < 10 || mb_strlen($reason) > 2000) {
            throw new InvalidArgumentException(
                'Reason must contain between 10 and 2000 characters'
            );
        }

        $access = app(CrmAccessService::class);

        if (!$access->can(
            $authenticatedRequesterId,
            'fare_rules_v2.rollback.request'
        )) {
            throw new RuntimeException('Requester permission denied');
        }

        return DB::transaction(function () use (
            $authenticatedRequesterId,
            $ruleId,
            $targetVersion,
            $reason
        ) {
            $rule = DB::table('fare_rules_v2')
                ->where('id', $ruleId)
                ->lockForUpdate()
                ->first();

            if (!$rule) {
                throw new InvalidArgumentException('Rule not found');
            }

            $manager = new FareRuleVersionManagerV2();

            $eligibility = $manager->checkRollbackEligibility(
                $ruleId,
                $targetVersion
            );

            if (!$eligibility['eligible']) {
                throw new InvalidArgumentException(
                    'Rollback not eligible: ' . $eligibility['reason']
                );
            }

            $currentVersion = (int) $rule->current_version;

            if ($currentVersion !== (int) $eligibility['current_version']) {
                throw new RuntimeException('Current version changed');
            }

            $duplicate = DB::table('fare_rule_rollback_requests_v2')
                ->where('fare_rule_id', $ruleId)
                ->where('status', 'pending')
                ->where('expires_at', '>', now())
                ->exists();

            if ($duplicate) {
                throw new RuntimeException(
                    'An active pending rollback request already exists'
                );
            }

            $now = now();

            $requestId = DB::table('fare_rule_rollback_requests_v2')
                ->insertGetId([
                    'fare_rule_id' => $ruleId,
                    'expected_current_version' => $currentVersion,
                    'target_version' => $targetVersion,
                    'requested_by' => $authenticatedRequesterId,
                    'approved_by' => null,
                    'reason' => $reason,
                    'status' => 'pending',
                    'expires_at' => $now->copy()->addHours(24),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            DB::table('crm_access_audit')->insert([
                'actor_admin_id' => $authenticatedRequesterId,
                'target_admin_id' => null,
                'action' => 'fare_rules_v2.rollback.request_created',
                'message' => json_encode([
                    'request_id' => (int) $requestId,
                    'rule_id' => $ruleId,
                    'expected_current_version' => $currentVersion,
                    'target_version' => $targetVersion,
                    'status' => 'pending',
                    'execution_performed' => false,
                ], JSON_THROW_ON_ERROR),
                'ip_address' => null,
                'created_at' => $now,
            ]);

            return [
                'request_id' => (int) $requestId,
                'rule_id' => $ruleId,
                'expected_current_version' => $currentVersion,
                'target_version' => $targetVersion,
                'requested_by' => $authenticatedRequesterId,
                'status' => 'pending',
                'execution_performed' => false,
            ];
        }, 3);
    }

    private const APPROVAL_WRITES_ENABLED = false;

    public function approveRollback(
        int $authenticatedApproverId,
        int $requestId
    ): array {
        $this->assertStaging();

        if (!self::APPROVAL_WRITES_ENABLED) {
            throw new RuntimeException(
                'Rollback approval disabled pending security tests'
            );
        }

        if ($authenticatedApproverId <= 0 || $requestId <= 0) {
            throw new InvalidArgumentException(
                'Invalid approval identifiers'
            );
        }

        $access = app(CrmAccessService::class);

        if (!$access->can(
            $authenticatedApproverId,
            'fare_rules_v2.rollback.approve'
        )) {
            throw new RuntimeException('Approver permission denied');
        }

        return DB::transaction(function () use (
            $authenticatedApproverId,
            $requestId
        ) {
            if (!app(CrmAccessService::class)->can(
                $authenticatedApproverId,
                'fare_rules_v2.rollback.approve'
            )) {
                throw new RuntimeException(
                    'Approver permission revoked or inactive'
                );
            }

            $requestReference = DB::table('fare_rule_rollback_requests_v2')
                ->where('id', $requestId)
                ->first();

            if (!$requestReference) {
                throw new InvalidArgumentException(
                    'Rollback request not found'
                );
            }

            $rule = DB::table('fare_rules_v2')
                ->where('id', $requestReference->fare_rule_id)
                ->lockForUpdate()
                ->first();

            if (!$rule) {
                throw new RuntimeException('Fare rule missing');
            }

            $request = DB::table('fare_rule_rollback_requests_v2')
                ->where('id', $requestId)
                ->lockForUpdate()
                ->first();

            if (
                !$request
                || (int) $request->fare_rule_id !== (int) $rule->id
            ) {
                throw new RuntimeException(
                    'Rollback request changed during approval'
                );
            }

            if (!$request) {
                throw new InvalidArgumentException(
                    'Rollback request not found'
                );
            }

            if ($request->status !== 'pending') {
                throw new RuntimeException(
                    'Rollback request is not pending'
                );
            }

            if (
                (int) $request->requested_by
                === $authenticatedApproverId
            ) {
                throw new RuntimeException(
                    'Requester cannot approve own rollback'
                );
            }

            if (
                strtotime($request->expires_at)
                <= now()->timestamp
            ) {
                throw new RuntimeException(
                    'Rollback request expired'
                );
            }

            if (
                (int) $rule->current_version
                !== (int) $request->expected_current_version
            ) {
                throw new RuntimeException(
                    'Fare rule version changed after request'
                );
            }

            $manager = new FareRuleVersionManagerV2();

            $eligibility = $manager->checkRollbackEligibility(
                (int) $request->fare_rule_id,
                (int) $request->target_version
            );

            if (!$eligibility['eligible']) {
                throw new RuntimeException(
                    'Target version no longer eligible'
                );
            }

            $now = now();

            $updated = DB::table('fare_rule_rollback_requests_v2')
                ->where('id', $requestId)
                ->where('status', 'pending')
                ->where('expires_at', '>', $now)
                ->update([
                    'status' => 'approved',
                    'approved_by' => $authenticatedApproverId,
                    'approved_at' => $now,
                    'updated_at' => $now,
                ]);

            if ($updated !== 1) {
                throw new RuntimeException(
                    'Approval state transition failed'
                );
            }

            DB::table('crm_access_audit')->insert([
                'actor_admin_id' => $authenticatedApproverId,
                'target_admin_id' => (int) $request->requested_by,
                'action' => 'fare_rules_v2.rollback.approved',
                'message' => json_encode([
                    'request_id' => $requestId,
                    'rule_id' => (int) $request->fare_rule_id,
                    'expected_current_version' =>
                        (int) $request->expected_current_version,
                    'target_version' =>
                        (int) $request->target_version,
                    'requested_by' =>
                        (int) $request->requested_by,
                    'approved_by' => $authenticatedApproverId,
                    'execution_performed' => false,
                ], JSON_THROW_ON_ERROR),
                'ip_address' => null,
                'created_at' => $now,
            ]);

            return [
                'request_id' => $requestId,
                'status' => 'approved',
                'approved_by' => $authenticatedApproverId,
                'execution_performed' => false,
            ];
        }, 3);
    }

    public function rejectRollback(
        int $authenticatedApproverId,
        int $requestId,
        string $reason
    ): array {
        $this->assertStaging();

        throw new RuntimeException(
            'Rollback rejection disabled until audited workflow implementation'
        );
    }
}
