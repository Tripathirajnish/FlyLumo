<?php

namespace App\Services\FareRulesV2;

use Illuminate\Support\Facades\DB;
use RuntimeException;

final class FareRuleRollbackWriterV2
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
            throw new RuntimeException('Staging database required');
        }

        if (!self::EXECUTION_ENABLED) {
            throw new RuntimeException(
                'Rollback writer disabled until authenticated approval is implemented'
            );
        }

        throw new RuntimeException('Rollback writer not yet activated');
    }

    private function copyVersionInsideTransaction(
        array $prepared,
        int $createdBy
    ): int {
        if (DB::transactionLevel() < 1) {
            throw new RuntimeException('Active transaction required');
        }

        if (DB::connection()->getDatabaseName() !== 'fareshunt_v2_staging') {
            throw new RuntimeException('Staging database required');
        }

        $now = date('Y-m-d H:i:s');

        $newVersionId = DB::table('fare_rule_versions_v2')
            ->insertGetId([
                'fare_rule_id' => $prepared['rule_id'],
                'version_number' => $prepared['new_version'],
                'configuration' => $prepared['configuration'],
                'change_summary' => 'Rollback copy of version '
                    . $prepared['target_version'],
                'status' => 'draft',
                'created_by' => $createdBy,
                'rolled_back_from_version' => $prepared['target_version'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

        foreach ($prepared['conditions'] as $condition) {
            $condition['fare_rule_version_id'] = $newVersionId;
            $condition['created_at'] = $now;
            $condition['updated_at'] = $now;

            DB::table('fare_rule_conditions_v2')->insert($condition);
        }

        foreach ($prepared['actions'] as $action) {
            $action['fare_rule_version_id'] = $newVersionId;
            $action['created_at'] = $now;
            $action['updated_at'] = $now;

            DB::table('fare_rule_actions_v2')->insert($action);
        }

        return (int) $newVersionId;
    }

    private function writeRollbackAuditInsideTransaction(
        int $ruleId,
        int $fromVersion,
        int $targetVersion,
        int $newVersion,
        int $requestedBy,
        int $approvedBy,
        string $reason
    ): int {
        if (DB::connection()->getDatabaseName() !== 'fareshunt_v2_staging') {
            throw new RuntimeException('Staging database required');
        }

        if (DB::transactionLevel() < 1) {
            throw new RuntimeException('Active transaction required');
        }

        if (
            $ruleId <= 0 ||
            $fromVersion <= 0 ||
            $targetVersion <= 0 ||
            $newVersion <= 0 ||
            $requestedBy <= 0 ||
            $approvedBy <= 0 ||
            $requestedBy === $approvedBy ||
            mb_strlen(trim($reason)) < 10
        ) {
            throw new RuntimeException('Invalid rollback audit data');
        }

        $now = date('Y-m-d H:i:s');

        return (int) DB::table('fare_rule_rollback_audits_v2')
            ->insertGetId([
                'fare_rule_id' => $ruleId,
                'from_version' => $fromVersion,
                'target_version' => $targetVersion,
                'new_version' => $newVersion,
                'requested_by' => $requestedBy,
                'approved_by' => $approvedBy,
                'reason' => trim($reason),
                'status' => 'completed',
                'executed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
    }

    private function activateVersionInsideTransaction(
        int $ruleId,
        int $newVersionId,
        int $newVersionNumber,
        int $approvedBy
    ): void {
        if (DB::connection()->getDatabaseName() !== 'fareshunt_v2_staging') {
            throw new RuntimeException('Staging database required');
        }

        if (DB::transactionLevel() < 1) {
            throw new RuntimeException('Active transaction required');
        }

        if (
            $ruleId <= 0 ||
            $newVersionId <= 0 ||
            $newVersionNumber <= 0 ||
            $approvedBy <= 0
        ) {
            throw new RuntimeException('Invalid activation data');
        }

        $now = date('Y-m-d H:i:s');

        $versionUpdated = DB::table('fare_rule_versions_v2')
            ->where('id', $newVersionId)
            ->where('fare_rule_id', $ruleId)
            ->where('version_number', $newVersionNumber)
            ->where('status', 'draft')
            ->update([
                'status' => 'published',
                'approved_by' => $approvedBy,
                'approved_at' => $now,
                'published_at' => $now,
                'updated_at' => $now,
            ]);

        if ($versionUpdated !== 1) {
            throw new RuntimeException('Version activation failed');
        }

        $ruleUpdated = DB::table('fare_rules_v2')
            ->where('id', $ruleId)
            ->where('status', 'published')
            ->update([
                'current_version' => $newVersionNumber,
                'approved_by' => $approvedBy,
                'approved_at' => $now,
                'updated_at' => $now,
            ]);

        if ($ruleUpdated !== 1) {
            throw new RuntimeException('Rule activation failed');
        }
    }

    private function prepareLockedRollbackInsideTransaction(
        int $ruleId,
        int $targetVersion,
        int $expectedCurrentVersion
    ): array {
        if (DB::connection()->getDatabaseName() !== 'fareshunt_v2_staging') {
            throw new RuntimeException('Staging database required');
        }

        if (DB::transactionLevel() < 1) {
            throw new RuntimeException('Active transaction required');
        }

        if (
            $ruleId <= 0 ||
            $targetVersion <= 0 ||
            $expectedCurrentVersion <= 0
        ) {
            throw new RuntimeException('Invalid rollback parameters');
        }

        $rule = DB::table('fare_rules_v2')
            ->where('id', $ruleId)
            ->lockForUpdate()
            ->first();

        if (!$rule || $rule->status !== 'published') {
            throw new RuntimeException('Published rule not found');
        }

        if ((int) $rule->current_version !== $expectedCurrentVersion) {
            throw new RuntimeException('Current version changed');
        }

        if ($targetVersion === $expectedCurrentVersion) {
            throw new RuntimeException('Target version already current');
        }

        $prepared = (new FareRuleVersionClonerV2())
            ->prepareClone($ruleId, $targetVersion);

        if (
            !$prepared['prepared'] ||
            (int) $prepared['current_version'] !== $expectedCurrentVersion ||
            empty($prepared['conditions']) ||
            empty($prepared['actions'])
        ) {
            throw new RuntimeException('Rollback preparation failed');
        }

        return $prepared;
    }

    private function performAtomicRollbackInsideTransaction(
        int $ruleId,
        int $targetVersion,
        int $expectedCurrentVersion,
        int $requestedBy,
        int $approvedBy,
        string $reason
    ): array {
        if (DB::connection()->getDatabaseName() !== 'fareshunt_v2_staging') {
            throw new RuntimeException('Staging database required');
        }

        if (DB::transactionLevel() < 1) {
            throw new RuntimeException('Active transaction required');
        }

        if (
            $requestedBy <= 0 ||
            $approvedBy <= 0 ||
            $requestedBy === $approvedBy ||
            mb_strlen(trim($reason)) < 10
        ) {
            throw new RuntimeException('Invalid rollback authorization data');
        }

        $prepared = $this->prepareLockedRollbackInsideTransaction(
            $ruleId,
            $targetVersion,
            $expectedCurrentVersion
        );

        $newVersionId = $this->copyVersionInsideTransaction(
            $prepared,
            $requestedBy
        );

        $this->activateVersionInsideTransaction(
            $ruleId,
            $newVersionId,
            (int) $prepared['new_version'],
            $approvedBy
        );

        $auditId = $this->writeRollbackAuditInsideTransaction(
            $ruleId,
            $expectedCurrentVersion,
            $targetVersion,
            (int) $prepared['new_version'],
            $requestedBy,
            $approvedBy,
            $reason
        );

        return [
            'execution_performed' => true,
            'rule_id' => $ruleId,
            'from_version' => $expectedCurrentVersion,
            'target_version' => $targetVersion,
            'new_version' => (int) $prepared['new_version'],
            'new_version_id' => $newVersionId,
            'audit_id' => $auditId,
        ];
    }

    public function status(): array
    {
        return [
            'execution_enabled' => self::EXECUTION_ENABLED,
            'authentication_verified' => false,
            'approval_verified' => false,
            'atomic_writer_activated' => false,
        ];
    }
}
