<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('meta_monitor_sources')) {
            Schema::create('meta_monitor_sources', function (Blueprint $t) {
                $t->id();
                $t->string('name', 120);
                $t->string('slug', 120)->unique();
                $t->string('meta_name', 120)->nullable()->index();
                $t->string('source_match', 255)->nullable()->index();
                $t->text('entry_url')->nullable();
                $t->unsignedInteger('target_ms')->default(5000);
                $t->boolean('is_active')->default(true)->index();
                $t->string('status', 30)->default('unknown')->index();
                $t->unsignedInteger('last_response_ms')->nullable();
                $t->unsignedSmallInteger('last_http_status')->nullable();
                $t->text('last_error')->nullable();
                $t->timestamp('last_checked_at')->nullable()->index();
                $t->timestamp('last_success_at')->nullable()->index();
                $t->timestamps();
            });
        }

        if (!Schema::hasTable('meta_monitor_checks')) {
            Schema::create('meta_monitor_checks', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('source_id')->index();
                $t->string('check_type', 40)->default('listing')->index();
                $t->string('status', 30)->index();
                $t->unsignedSmallInteger('http_status')->nullable()->index();
                $t->unsignedInteger('response_ms')->nullable()->index();
                $t->boolean('redirect_ok')->nullable()->index();
                $t->string('final_path', 500)->nullable();
                $t->string('search_code', 120)->nullable()->index();
                $t->unsignedInteger('result_count')->nullable();
                $t->text('error_message')->nullable();
                $t->json('context')->nullable();
                $t->timestamp('checked_at')->nullable()->index();
                $t->timestamp('created_at')->nullable()->index();

                $t->index(['source_id', 'checked_at']);
                $t->index(['source_id', 'status']);
            });
        }

        if (Schema::hasTable('crm_access_roles') && Schema::hasTable('crm_access_role_permissions')) {
            $roleIds = DB::table('crm_access_roles')
                ->whereIn('slug', ['super-admin', 'manager'])
                ->pluck('id');

            foreach ($roleIds as $roleId) {
                DB::table('crm_access_role_permissions')->updateOrInsert(
                    ['role_id' => $roleId, 'permission_key' => 'meta_monitor.view'],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('crm_access_role_permissions')) {
            DB::table('crm_access_role_permissions')
                ->where('permission_key', 'meta_monitor.view')
                ->delete();
        }

        Schema::dropIfExists('meta_monitor_checks');
        Schema::dropIfExists('meta_monitor_sources');
    }
};
