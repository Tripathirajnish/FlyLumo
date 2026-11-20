<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('travel_master_data_audit')) {
            Schema::create('travel_master_data_audit', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('actor_admin_id')->nullable()->index();
                $table->string('entity_type', 40)->index();
                $table->unsignedBigInteger('entity_id')->nullable()->index();
                $table->string('action', 40)->index();
                $table->string('summary', 255)->nullable();
                $table->longText('before_json')->nullable();
                $table->longText('after_json')->nullable();
                $table->string('ip_address', 64)->nullable();
                $table->timestamp('created_at')->nullable()->index();
            });
        }

        if (
            Schema::hasTable('crm_access_roles') &&
            Schema::hasTable('crm_access_role_permissions')
        ) {
            $grants = [
                'super-admin' => ['master_data.view', 'master_data.manage'],
                'manager'     => ['master_data.view', 'master_data.manage'],
                'read-only'   => ['master_data.view'],
            ];

            foreach ($grants as $roleSlug => $permissions) {
                $roleId = DB::table('crm_access_roles')
                    ->where('slug', $roleSlug)
                    ->value('id');

                if (!$roleId) {
                    continue;
                }

                foreach ($permissions as $permission) {
                    DB::table('crm_access_role_permissions')->updateOrInsert(
                        [
                            'role_id' => $roleId,
                            'permission_key' => $permission,
                        ],
                        [
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('crm_access_role_permissions')) {
            DB::table('crm_access_role_permissions')
                ->whereIn('permission_key', [
                    'master_data.view',
                    'master_data.manage',
                ])
                ->delete();
        }

        Schema::dropIfExists('travel_master_data_audit');
    }
};
