<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            !Schema::hasTable('crm_access_roles') ||
            !Schema::hasTable('crm_access_role_permissions')
        ) {
            return;
        }

        $rolePermissions = [
            'manager' => [
                'refunds.view',
                'refunds.request',
                'refunds.approve',
                'chargebacks.view',
                'chargebacks.manage',
            ],
            'agent' => [
                'refunds.view',
                'refunds.request',
                'chargebacks.view',
            ],
            'finance' => [
                'refunds.view',
                'refunds.request',
                'refunds.approve',
                'chargebacks.view',
                'chargebacks.manage',
            ],
            'read-only' => [
                'refunds.view',
                'chargebacks.view',
            ],
        ];

        foreach ($rolePermissions as $slug => $permissions) {
            $roleId = DB::table('crm_access_roles')
                ->where('slug', $slug)
                ->value('id');

            if (!$roleId) {
                continue;
            }

            foreach ($permissions as $permission) {
                DB::table('crm_access_role_permissions')->insertOrIgnore([
                    'role_id' => $roleId,
                    'permission_key' => $permission,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive.
        // Access-control permissions may have been customized after deployment,
        // so rollback must not remove live role permissions.
    }
};
