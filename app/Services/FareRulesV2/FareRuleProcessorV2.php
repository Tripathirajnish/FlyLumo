<?php

namespace App\Services\FareRulesV2;

use InvalidArgumentException;

final class FareRuleProcessorV2
{
    public function process(float $baseFare, array $rules): array
    {
        $calculator = new FareCalculatorV2();

        // Validate base fare even when no rules match.
        $initial = $calculator->calculate($baseFare, []);
        $currentFare = $initial['final_fare'];

        usort($rules, static function ($a, $b) {
            $priority = (int) ($a['priority'] ?? 100);
            $otherPriority = (int) ($b['priority'] ?? 100);

            if ($priority !== $otherPriority) {
                return $priority <=> $otherPriority;
            }

            return (int) ($a['id'] ?? 0) <=> (int) ($b['id'] ?? 0);
        });

        $matchedRules = [];
        $adjustments = [];
        $stopReason = null;

        foreach ($rules as $rule) {
            if (empty($rule['actions']) ||
                !is_array($rule['actions'])) {
                continue;
            }

            $stackable = $rule['stackable'] ?? false;
            $stopProcessing = $rule['stop_processing'] ?? true;

            if (!is_bool($stackable) ||
                !is_bool($stopProcessing)) {
                throw new InvalidArgumentException(
                    'Invalid stacking configuration'
                );
            }

            if (!empty($matchedRules) && !$stackable) {
                $stopReason = 'non_stackable_rule';
                break;
            }

            $result = $calculator->calculate(
                $currentFare,
                $rule['actions']
            );

            $currentFare = $result['final_fare'];

            $matchedRules[] = [
                'id' => $rule['id'] ?? null,
                'rule_code' => $rule['rule_code'] ?? null,
                'priority' => $rule['priority'] ?? 100,
            ];

            foreach ($result['adjustments'] as $adjustment) {
                $adjustment['rule_code'] =
                    $rule['rule_code'] ?? null;

                $adjustments[] = $adjustment;
            }

            if ($stopProcessing) {
                $stopReason = 'stop_processing';
                break;
            }

            if (!$stackable) {
                $stopReason = 'non_stackable_rule';
                break;
            }
        }

        return [
            'engine' => 'fare_rules_v2',
            'status' => empty($matchedRules)
                ? 'no_match'
                : 'matched',
            'original_fare' => $initial['original_fare'],
            'final_fare' => $currentFare,
            'total_adjustment' => round(
                $currentFare - $initial['original_fare'],
                2
            ),
            'matched_rules' => $matchedRules,
            'adjustments' => $adjustments,
            'stop_reason' => $stopReason,
        ];
    }
}
