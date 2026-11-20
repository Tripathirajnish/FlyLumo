<?php

namespace App\Services\FareRulesV2;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

final class FareRuleRollbackServiceV2
{
    public function validateRequest(
        int $ruleId,
        int $targetVersion,
        int $requestedBy,
        int $approvedBy,
        string $reason
    ): array {
        if (DB::connection()->getDatabaseName() !== 'fareshunt_v2_staging') {
            throw new RuntimeException(
                'Rollback service restricted to staging database'
            );
        }

        if ($ruleId <= 0 || $targetVersion <= 0) {
            throw new InvalidArgumentException(
                'Invalid rule or target version'
            );
        }

        if ($requestedBy <= 0 || $approvedBy <= 0) {
            throw new InvalidArgumentException(
                'Valid requester and approver IDs required'
            );
        }

        if ($requestedBy === $approvedBy) {
            throw new InvalidArgumentException(
                'Requester and approver must be different'
            );
        }

        $reason = trim($reason);

        if (mb_strlen($reason) < 10) {
            throw new InvalidArgumentException(
                'Rollback reason must contain at least 10 characters'
            );
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

        return [
            'validated' => true,
            'execution_performed' => false,
            'rule_id' => $ruleId,
            'from_version' => $eligibility['current_version'],
            'target_version' => $targetVersion,
            'requested_by' => $requestedBy,
            'approved_by' => $approvedBy,
            'reason' => $reason,
        ];
    }
}
