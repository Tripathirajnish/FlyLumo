<?php

namespace App\Services\FareRulesV2;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

final class FareRuleRollbackTransactionV2
{
    public function inspectLockedRule(
        int $ruleId,
        int $targetVersion
    ): array {
        if (DB::connection()->getDatabaseName() !== 'fareshunt_v2_staging') {
            throw new RuntimeException('Staging database required');
        }

        if ($ruleId <= 0 || $targetVersion <= 0) {
            throw new InvalidArgumentException('Invalid rule or target version');
        }

        return DB::transaction(function () use ($ruleId, $targetVersion) {

            $rule = DB::table('fare_rules_v2')
                ->where('id', $ruleId)
                ->lockForUpdate()
                ->first();

            if (!$rule) {
                throw new InvalidArgumentException('Rule not found');
            }

            $source = DB::table('fare_rule_versions_v2')
                ->where('fare_rule_id', $ruleId)
                ->where('version_number', $targetVersion)
                ->first();

            if (!$source) {
                throw new InvalidArgumentException('Target version not found');
            }

            if (
                $source->status !== 'published' ||
                !$source->approved_at ||
                !$source->published_at
            ) {
                throw new InvalidArgumentException(
                    'Target version must be approved and published'
                );
            }

            if ((int) $rule->current_version === $targetVersion) {
                throw new InvalidArgumentException(
                    'Target version is already current'
                );
            }

            $cloner = new FareRuleVersionClonerV2();

            $prepared = $cloner->prepareClone(
                $ruleId,
                $targetVersion
            );

            return [
                'inspection_completed' => true,
                'execution_performed' => false,
                'lock_acquired_during_transaction' => true,
                'rule_id' => $ruleId,
                'from_version' => (int) $rule->current_version,
                'target_version' => $targetVersion,
                'new_version' => $prepared['new_version'],
                'conditions_count' => count($prepared['conditions']),
                'actions_count' => count($prepared['actions']),
            ];
        }, 3);
    }
}
