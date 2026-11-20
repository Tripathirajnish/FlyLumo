<?php

namespace App\Services\FareRulesV2;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class FareRuleEngineV2
{
    public function evaluate(array $flight): array
    {
        $baseFare = $flight['base_fare'] ?? null;

        if (!is_numeric($baseFare) ||
            !is_finite((float) $baseFare) ||
            (float) $baseFare < 0) {
            throw new InvalidArgumentException('Invalid base fare');
        }

        $matcher = new FareRuleMatcherV2();
        $processor = new FareRuleProcessorV2();
        $matchedRuleData = [];

        $now = now()->toDateTimeString();

        $rules = DB::table('fare_rules_v2 as r')
            ->join('fare_rule_versions_v2 as v', function ($join) {
                $join->on('v.fare_rule_id', '=', 'r.id')
                    ->on('v.version_number', '=', 'r.current_version');
            })
            ->where('r.status', 'published')
            ->where('v.status', 'published')
            ->whereNotNull('r.approved_at')
            ->whereNotNull('v.approved_at')
            ->whereNotNull('v.published_at')
            ->where(function ($q) use ($now) {
                $q->whereNull('r.effective_from')
                    ->orWhere('r.effective_from', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('r.effective_to')
                    ->orWhere('r.effective_to', '>=', $now);
            })
            ->orderBy('r.priority')
            ->orderBy('r.id')
            ->select(
                'r.id',
                'r.rule_code',
                'r.priority',
                'r.stackable',
                'r.stop_processing',
                'v.id as version_id',
                'v.version_number'
            )
            ->get();

        foreach ($rules as $rule) {
            $conditions = DB::table('fare_rule_conditions_v2')
                ->where('fare_rule_version_id', $rule->version_id)
                ->orderBy('condition_group')
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($row) => [
                    'field' => $row->field,
                    'operator' => $row->operator,
                    'value' => $row->value,
                    'condition_group' => $row->condition_group,
                ])
                ->all();

            if (empty($conditions)) {
                continue;
            }

            if (!$matcher->matches($flight, $conditions)) {
                continue;
            }

            $actions = DB::table('fare_rule_actions_v2')
                ->where('fare_rule_version_id', $rule->version_id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            if ($actions->isEmpty()) {
                continue;
            }

            $actionData = [];

            foreach ($actions as $action) {
                if ($action->calculation_base !== 'base_fare' ||
                    $action->currency !== null ||
                    $action->minimum_adjustment !== null ||
                    $action->maximum_adjustment !== null) {
                    throw new InvalidArgumentException(
                        'Unsupported V2 pricing configuration'
                    );
                }

                $actionData[] = [
                    'action_type' => $action->action_type,
                    'amount' => $action->amount,
                ];
            }

            $matchedRuleData[] = [
                'id' => $rule->id,
                'rule_code' => $rule->rule_code,
                'version' => $rule->version_number,
                'priority' => $rule->priority,
                'stackable' => (bool) $rule->stackable,
                'stop_processing' => (bool) $rule->stop_processing,
                'actions' => $actionData,
            ];
        }

        return $processor->process(
            (float) $baseFare,
            $matchedRuleData
        );
    }
}
