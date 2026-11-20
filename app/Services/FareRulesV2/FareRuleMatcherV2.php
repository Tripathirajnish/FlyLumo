<?php

namespace App\Services\FareRulesV2;

use InvalidArgumentException;

final class FareRuleMatcherV2
{
    private const ALLOWED_FIELDS = [
        'airline',
        'origin',
        'destination',
        'cabin',
        'supplier',
        'travel_date',
        'booking_date',
        'trip_type',
    ];

    private const ALLOWED_OPERATORS = [
        'equals',
        'not_equals',
        'in',
        'not_in',
        'between',
    ];

    public function matches(array $flight, array $conditions): bool
    {
        if (empty($conditions)) {
            return true;
        }

        $groups = [];

        foreach ($conditions as $condition) {
            $group = (int) ($condition['condition_group'] ?? 1);

            if ($group < 1) {
                throw new InvalidArgumentException('Invalid condition group');
            }

            $groups[$group][] = $condition;
        }

        // AND within each group, OR between groups.
        foreach ($groups as $groupConditions) {
            $allMatch = true;

            foreach ($groupConditions as $condition) {
                if (!$this->matchCondition($flight, $condition)) {
                    $allMatch = false;
                    break;
                }
            }

            if ($allMatch) {
                return true;
            }
        }

        return false;
    }

    private function matchCondition(array $flight, array $condition): bool
    {
        $field = $condition['field'] ?? '';
        $operator = $condition['operator'] ?? '';
        $expected = $condition['value'] ?? null;

        if (!in_array($field, self::ALLOWED_FIELDS, true)) {
            throw new InvalidArgumentException('Unsupported condition field');
        }

        if (!in_array($operator, self::ALLOWED_OPERATORS, true)) {
            throw new InvalidArgumentException('Unsupported condition operator');
        }

        if (!array_key_exists($field, $flight) || $flight[$field] === null) {
            return false;
        }

        $actual = $flight[$field];

        if (!is_scalar($actual)) {
            throw new InvalidArgumentException('Invalid flight field value');
        }

        if (is_string($expected)) {
            $decoded = json_decode($expected, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $expected = $decoded;
            }
        }

        $normalize = static function ($value): string {
            if (!is_scalar($value)) {
                throw new InvalidArgumentException('Invalid condition value');
            }

            return strtoupper(trim((string) $value));
        };

        $actualValue = $normalize($actual);

        switch ($operator) {
            case 'equals':
                return $actualValue === $normalize($expected);

            case 'not_equals':
                return $actualValue !== $normalize($expected);

            case 'in':
            case 'not_in':
                if (!is_array($expected)) {
                    throw new InvalidArgumentException('Expected array');
                }

                $values = array_map($normalize, $expected);
                $found = in_array($actualValue, $values, true);

                return $operator === 'in' ? $found : !$found;

            case 'between':
                if (!is_array($expected) || count($expected) !== 2) {
                    throw new InvalidArgumentException('Expected two boundaries');
                }

                $start = $normalize($expected[0]);
                $end = $normalize($expected[1]);

                if ($start > $end) {
                    throw new InvalidArgumentException('Invalid range');
                }

                return $actualValue >= $start && $actualValue <= $end;
        }

        return false;
    }
}
