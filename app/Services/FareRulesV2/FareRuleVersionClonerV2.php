<?php

namespace App\Services\FareRulesV2;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

final class FareRuleVersionClonerV2
{
    public function prepareClone(int $ruleId, int $targetVersion): array
    {
        if (DB::connection()->getDatabaseName() !== 'fareshunt_v2_staging') {
            throw new RuntimeException('Staging database required');
        }

        if ($ruleId <= 0 || $targetVersion <= 0) {
            throw new InvalidArgumentException('Invalid rule or version');
        }

        $rule = DB::table('fare_rules_v2')
            ->where('id', $ruleId)
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

        $conditions = DB::table('fare_rule_conditions_v2')
            ->where('fare_rule_version_id', $source->id)
            ->orderBy('condition_group')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $actions = DB::table('fare_rule_actions_v2')
            ->where('fare_rule_version_id', $source->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($conditions->isEmpty() || $actions->isEmpty()) {
            throw new InvalidArgumentException(
                'Source version requires conditions and actions'
            );
        }

        $maxVersion = DB::table('fare_rule_versions_v2')
            ->where('fare_rule_id', $ruleId)
            ->max('version_number');

        $nextVersion = max(
            (int) $maxVersion,
            (int) $rule->current_version
        ) + 1;

        return [
            'prepared' => true,
            'execution_performed' => false,
            'rule_id' => $ruleId,
            'current_version' => (int) $rule->current_version,
            'target_version' => $targetVersion,
            'new_version' => $nextVersion,
            'source_version_id' => (int) $source->id,
            'configuration' => $source->configuration,
            'conditions' => $conditions->map(
                static function ($row) {
                    return [
                        'condition_group' => $row->condition_group,
                        'sort_order' => $row->sort_order,
                        'field' => $row->field,
                        'operator' => $row->operator,
                        'value' => $row->value,
                    ];
                }
            )->all(),
            'actions' => $actions->map(
                static function ($row) {
                    return [
                        'sort_order' => $row->sort_order,
                        'action_type' => $row->action_type,
                        'calculation_base' => $row->calculation_base,
                        'amount' => $row->amount,
                        'currency' => $row->currency,
                        'minimum_adjustment' => $row->minimum_adjustment,
                        'maximum_adjustment' => $row->maximum_adjustment,
                        'is_active' => $row->is_active,
                    ];
                }
            )->all(),
        ];
    }
}
