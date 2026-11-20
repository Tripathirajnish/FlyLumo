<?php

namespace App\Services\FareRulesV2;

use InvalidArgumentException;

final class FareCalculatorV2
{
    public function calculate(float $baseFare, array $actions): array
    {
        if (!is_finite($baseFare) || $baseFare < 0) {
            throw new InvalidArgumentException('Invalid base fare');
        }

        $originalFare = round($baseFare, 2);
        $currentFare = $originalFare;
        $adjustments = [];

        foreach ($actions as $action) {
            $type = $action['action_type'] ?? '';
            $amount = $action['amount'] ?? null;

            if (!is_numeric($amount) ||
                !is_finite((float) $amount) ||
                (float) $amount < 0) {
                throw new InvalidArgumentException('Invalid adjustment amount');
            }

            $amount = (float) $amount;

            switch ($type) {
                case 'percentage_markup':
                    if ($amount > 100) {
                        throw new InvalidArgumentException('Percentage exceeds 100');
                    }
                    $adjustment = $currentFare * $amount / 100;
                    break;

                case 'fixed_markup':
                    $adjustment = $amount;
                    break;

                case 'percentage_discount':
                    if ($amount > 100) {
                        throw new InvalidArgumentException('Percentage exceeds 100');
                    }
                    $adjustment = -$currentFare * $amount / 100;
                    break;

                case 'fixed_discount':
                    $adjustment = -$amount;
                    break;

                default:
                    throw new InvalidArgumentException('Unsupported action type');
            }

            $adjustment = round($adjustment, 2);

            if ($currentFare + $adjustment < 0) {
                throw new InvalidArgumentException('Adjustment produces negative fare');
            }

            $currentFare = round($currentFare + $adjustment, 2);

            $adjustments[] = [
                'action_type' => $type,
                'amount' => $amount,
                'adjustment' => $adjustment,
                'fare_after' => $currentFare,
            ];
        }

        return [
            'original_fare' => $originalFare,
            'final_fare' => $currentFare,
            'total_adjustment' => round($currentFare - $originalFare, 2),
            'adjustments' => $adjustments,
        ];
    }
}
