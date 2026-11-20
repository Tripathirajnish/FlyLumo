<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        /*
         * Sync the existing Demo fallback configuration into the new secure
         * API Control V3 storage. Existing V3 values are NEVER overwritten.
         *
         * Secret values are encrypted with Laravel Crypt / APP_KEY.
         * WSDL, SOAP endpoint and currency are configuration values and remain
         * readable because the CRM needs to show/edit them.
         */
        $demo = [
            'api_demo_login'     => ['secret' => true,  'value' => 'test'],
            'api_demo_password'  => ['secret' => true,  'value' => 'test'],
            'api_demo_device_id' => ['secret' => true,  'value' => 'test'],
            'api_demo_token'     => ['secret' => true,  'value' => '00000000-0000-0000-0000-000000000000'],
            'api_demo_currency'  => ['secret' => false, 'value' => 'EUR'],
            'api_demo_wsdl'      => ['secret' => false, 'value' => 'http://test-api.xml.agency/SiteCity?singlewsdl'],
            'api_demo_soap'      => ['secret' => false, 'value' => 'http://test-api.xml.agency/SiteCity'],
        ];

        foreach ($demo as $name => $item) {
            $exists = DB::table('settings')->where('name', $name)->exists();

            if ($exists) {
                continue;
            }

            DB::table('settings')->insert([
                'name'       => $name,
                'value'      => $item['secret']
                    ? Crypt::encryptString($item['value'])
                    : $item['value'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        /*
         * Intentionally do nothing.
         * Rolling this migration back must not delete API configuration.
         */
    }
};
