<?php

namespace App\Services\FareRulesV2;

use App\Services\CrmAccessService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class LegacyFareRuleApprovalService
{
    public function review(int $adminId, int $requestId, bool $approve, ?string $note = null): array
    {
        app(FareRulePublishingEnvironmentGuardV2::class)->assertAllowed();

        if ($adminId <= 0 || $requestId <= 0 ||
            !app(CrmAccessService::class)->can($adminId, 'fare_rules_v2.approve')) {
            throw new RuntimeException('Review not authorized');
        }

        return DB::transaction(function () use ($adminId, $requestId, $approve, $note) {
            $environment = config('app.env');
            $database = DB::connection()->getDatabaseName();

            if ($environment === 'staging' && $database === 'fareshunt_v2_staging') {
                // Staging publishing allowed.
            } elseif (
                $environment === 'production' &&
                $database === 'travelnbreakz' &&
                config('fare_rules.legacy_publish_enabled', false) === true
            ) {
                // Production requires explicit configuration opt-in.
            } else {
                throw new RuntimeException('Legacy fare rule publishing disabled');
            }

            if (!app(CrmAccessService::class)->can($adminId, 'fare_rules_v2.approve')) {
                throw new RuntimeException('Review permission revoked');
            }

            $change = DB::table('fare_rule_change_requests')
                ->where('id', $requestId)
                ->lockForUpdate()
                ->first();

            if (!$change || $change->status !== 'pending') {
                throw new RuntimeException('Request is not pending');
            }

            if ((int) $change->created_by <= 0 ||
                (int) $change->created_by === $adminId) {
                throw new RuntimeException('Independent Checker required');
            }

            if (!in_array($change->operation, ['create', 'update'], true)) {
                throw new RuntimeException('Unsupported operation');
            }

            $proposed = json_decode($change->proposed_data, true);
            if (!is_array($proposed) || !$proposed) {
                throw new RuntimeException('Invalid proposed data');
            }


            if ($approve) {
                foreach ([
                    'name', 'departure_type', 'departure_code',
                    'arrival_type', 'arrival_code', 'markup',
                    'amt_per', 'from_date', 'to_date', 'is_active'
                ] as $field) {
                    if (!isset($proposed[$field]) ||
                        !is_scalar($proposed[$field]) ||
                        trim((string) $proposed[$field]) === '') {
                        throw new RuntimeException('Invalid field: ' . $field);
                    }
                }

                if (!is_string($proposed['name']) ||
                    trim($proposed['name']) === '' ||
                    mb_strlen($proposed['name']) > 100 ||
                    !in_array($proposed['departure_type'], ['airport', 'country'], true) ||
                    !in_array($proposed['arrival_type'], ['airport', 'country'], true) ||
                    !in_array($proposed['amt_per'], ['amt', 'per'], true) ||
                    !in_array($proposed['is_active'], ['Yes', 'No'], true) ||
                    filter_var($proposed['markup'], FILTER_VALIDATE_INT) === false) {
                    throw new RuntimeException('Invalid rule values');
                }

                foreach (['departure_code', 'arrival_code'] as $field) {
                    if (!is_string($proposed[$field]) ||
                        strlen($proposed[$field]) > 255 ||
                        trim($proposed[$field], " ,\t\n\r") === '') {
                        throw new RuntimeException('Invalid route code');
                    }
                }

                foreach ([
                    ['is_ownsite', 'is_facecompare'],
                    ['is_oneway', 'is_roundtrip', 'is_multicity'],
                    ['is_economy', 'is_peconomy', 'is_business', 'is_fclass']
                ] as $group) {
                    $selected = false;
                    foreach ($group as $flag) {
                        if (!isset($proposed[$flag]) ||
                            !in_array($proposed[$flag], ['Yes', 'No'], true)) {
                            throw new RuntimeException('Invalid flag: ' . $flag);
                        }
                        if ($proposed[$flag] === 'Yes') {
                            $selected = true;
                        }
                    }
                    if (!$selected) {
                        throw new RuntimeException('Missing rule selection');
                    }
                }

                if ($proposed['amt_per'] === 'per' &&
                    abs((int) $proposed['markup']) > 100) {
                    throw new RuntimeException('Percentage exceeds 100');
                }

                foreach (['from_date', 'to_date'] as $field) {
                    $date = \DateTimeImmutable::createFromFormat(
                        '!Y-m-d', (string) $proposed[$field]
                    );
                    if (!$date ||
                        $date->format('Y-m-d') !== $proposed[$field]) {
                        throw new RuntimeException('Invalid date: ' . $field);
                    }
                }

                if ($proposed['to_date'] < $proposed['from_date']) {
                    throw new RuntimeException('Invalid date range');
                }

                if ($change->operation === 'update' &&
                    ((int) $change->markup_id <= 0 ||
                     !DB::table('markups')
                         ->where('id', $change->markup_id)
                         ->exists())) {
                    throw new RuntimeException('Update target missing');
                }
            }


            // STAGING_LEGACY_FARE_RULE_TRANSACTIONAL_PUBLISH
            if ($approve) {
                app(FareRulePublishingEnvironmentGuardV2::class)->assertAllowed();

                $environment = config('app.env');
                $database = DB::connection()->getDatabaseName();

                $stagingAllowed = $environment === 'staging'
                    && $database === 'fareshunt_v2_staging';

                $productionAllowed = $environment === 'production'
                    && $database === 'travelnbreakz'
                    && config('fare_rules.legacy_publish_enabled', false) === true;

                if (!$stagingAllowed && !$productionAllowed) {
                    throw new RuntimeException('Publishing environment blocked');
                }

                $allowed = [
                    'name', 'departure_type', 'departure_code',
                    'arrival_type', 'arrival_code', 'airline_code',
                    'markup', 'amt_per',
                    'is_oneway', 'is_roundtrip', 'is_multicity',
                    'is_economy', 'is_peconomy', 'is_business', 'is_fclass',
                    'is_ownsite', 'is_facecompare',
                    'fare_min_price', 'from_date', 'to_date', 'is_active'
                ];

                $unsupported = array_diff(array_keys($proposed), $allowed);

                if ($unsupported) {
                    throw new \RuntimeException(
                        'Unsupported fare rule fields: ' . implode(', ', $unsupported)
                    );
                }

                $values = array_intersect_key(
                    $proposed,
                    array_flip($allowed)
                );

                if ($change->operation === 'create') {
                    if ($change->markup_id !== null) {
                        throw new RuntimeException('Create request has unexpected markup ID');
                    }

                    $values['created_at'] = now();
                    $values['updated_at'] = now();

                    $publishedId = DB::table('markups')->insertGetId($values);
                } else {
                    $previous = json_decode($change->previous_data, true);

                    if (!is_array($previous) || !$previous ||
                        (int) $change->markup_id <= 0) {
                        throw new RuntimeException('Invalid previous rule snapshot');
                    }

                    $current = DB::table('markups')
                        ->where('id', $change->markup_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$current) {
                        throw new RuntimeException('Rule no longer exists');
                    }

                    foreach ($previous as $field => $oldValue) {
                        if (!property_exists($current, $field) ||
                            (string) ($current->$field ?? '') !==
                            (string) ($oldValue ?? '')) {
                            throw new RuntimeException('Rule changed since request creation');
                        }
                    }

                    $values['updated_at'] = now();

                    DB::table('markups')
                        ->where('id', $change->markup_id)
                        ->update($values);

                    $publishedId = (int) $change->markup_id;
                }
            }

            $status = $approve ? 'approved' : 'rejected';
            $now = now();

            $updated = DB::table('fare_rule_change_requests')
                ->where('id', $requestId)
                ->where('status', 'pending')
                ->update([
                    'status' => $status,
                    'reviewed_by' => $adminId,
                    'reviewed_at' => $now,
                    'review_note' => $note,
                    'updated_at' => $now,
                ]);

            if ($updated !== 1) {
                throw new RuntimeException('Review transition failed');
            }

            DB::table('crm_access_audit')->insert([
                'actor_admin_id' => $adminId,
                'target_admin_id' => (int) $change->created_by,
                'action' => 'fare_rules.legacy.' . $status,
                'message' => json_encode([
                    'request_id' => $requestId,
                    'operation' => $change->operation,
                    'markup_id' => $change->markup_id,
                    'review_note' => $note,
                ], JSON_THROW_ON_ERROR),
                'ip_address' => null,
                'created_at' => $now,
            ]);

            return [
                'request_id' => $requestId,
                'status' => $status,
                'reviewed_by' => $adminId,
            ];
        }, 3);
    }
}
