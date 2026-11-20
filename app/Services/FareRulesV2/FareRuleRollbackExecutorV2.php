<?php

namespace App\Services\FareRulesV2;

use Illuminate\Support\Facades\DB;
use RuntimeException;

final class FareRuleRollbackExecutorV2
{
    private const EXECUTION_ENABLED = false;

    public function execute(
        int $ruleId,
        int $targetVersion,
        int $requestedBy,
        int $approvedBy,
        string $reason
    ): array {
        if (DB::connection()->getDatabaseName() !== 'fareshunt_v2_staging') {
            throw new RuntimeException(
                'Rollback execution restricted to staging database'
            );
        }

        if (!self::EXECUTION_ENABLED) {
            throw new RuntimeException(
                'Rollback execution disabled pending authorization and atomic transaction implementation'
            );
        }

        throw new RuntimeException(
            'Rollback execution implementation incomplete'
        );
    }

    public function status(): array
    {
        return [
            'service' => 'fare_rules_v2_rollback_executor',
            'execution_enabled' => self::EXECUTION_ENABLED,
            'authorization_implemented' => false,
            'atomic_transaction_implemented' => false,
            'audit_logging_implemented' => false,
        ];
    }
}
