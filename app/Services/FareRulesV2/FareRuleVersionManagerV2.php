<?php

namespace App\Services\FareRulesV2;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class FareRuleVersionManagerV2
{
    public function history(int $ruleId): array
    {
        if ($ruleId <= 0) {
            throw new InvalidArgumentException('Invalid rule ID');
        }

        $rule = DB::table('fare_rules_v2')
            ->where('id', $ruleId)
            ->first();

        if (!$rule) {
            throw new InvalidArgumentException('Rule not found');
        }

        $versions = DB::table('fare_rule_versions_v2')
            ->where('fare_rule_id', $ruleId)
            ->orderByDesc('version_number')
            ->get();

        return [
            'rule_id' => $rule->id,
            'rule_code' => $rule->rule_code,
            'current_version' => (int) $rule->current_version,
            'versions' => $versions->map(
                static fn ($version) => [
                    'id' => $version->id,
                    'version_number' => (int) $version->version_number,
                    'status' => $version->status,
                    'approved' => $version->approved_at !== null,
                    'published' => $version->published_at !== null,
                    'rolled_back_from_version' =>
                        $version->rolled_back_from_version,
                    'created_at' => $version->created_at,
                ]
            )->all(),
        ];
    }

    public function checkRollbackEligibility(
        int $ruleId,
        int $targetVersion
    ): array {
        if ($ruleId <= 0 || $targetVersion <= 0) {
            throw new InvalidArgumentException(
                'Invalid rule or version'
            );
        }

        $rule = DB::table('fare_rules_v2')
            ->where('id', $ruleId)
            ->first();

        if (!$rule) {
            throw new InvalidArgumentException('Rule not found');
        }

        $version = DB::table('fare_rule_versions_v2')
            ->where('fare_rule_id', $ruleId)
            ->where('version_number', $targetVersion)
            ->first();

        if (!$version) {
            return [
                'eligible' => false,
                'reason' => 'version_not_found',
            ];
        }

        if ((int) $rule->current_version === $targetVersion) {
            return [
                'eligible' => false,
                'reason' => 'already_current_version',
            ];
        }

        if ($version->status !== 'published' ||
            $version->approved_at === null ||
            $version->published_at === null) {
            return [
                'eligible' => false,
                'reason' => 'version_not_approved_and_published',
            ];
        }

        return [
            'eligible' => true,
            'reason' => 'eligible_for_review',
            'rule_id' => $ruleId,
            'current_version' => (int) $rule->current_version,
            'target_version' => $targetVersion,
        ];
    }
}
