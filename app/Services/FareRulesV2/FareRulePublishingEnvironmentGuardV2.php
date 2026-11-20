<?php

namespace App\Services\FareRulesV2;

use Illuminate\Support\Facades\DB;
use RuntimeException;

final class FareRulePublishingEnvironmentGuardV2
{
    public function assertAllowed(): void
    {
        $environment = config('app.env');

        $allowed = [
            'staging' => [
                'database' => 'fareshunt_v2_staging',
                'account' => 'fareshunt_v2_user@localhost',
            ],
            'testing' => [
                'database' => 'fareshunt_v2_test',
                'account' => 'fareshunt_v2_test_user@localhost',
            ],
            'production' => [
                'database' => 'travelnbreakz',
                'account' => 'traveluser@localhost',
            ],
        ];

        if (!isset($allowed[$environment])) {
            throw new RuntimeException(
                'Fare Rules V2 publishing environment not allowed'
            );
        }

        if (DB::getDefaultConnection() !== 'mysql') {
            throw new RuntimeException(
                'Fare Rules V2 publishing requires MySQL'
            );
        }

        $expected = $allowed[$environment];
        $connection = DB::connection();

        if ($connection->getDatabaseName() !== $expected['database']) {
            throw new RuntimeException(
                'Fare Rules V2 publishing database mismatch'
            );
        }

        $identity = $connection->selectOne(
            'SELECT DATABASE() AS database_name, CURRENT_USER() AS account_name'
        );

        if (
            !$identity ||
            $identity->database_name !== $expected['database'] ||
            $identity->account_name !== $expected['account']
        ) {
            throw new RuntimeException(
                'Fare Rules V2 publishing database identity mismatch'
            );
        }
    }
}
