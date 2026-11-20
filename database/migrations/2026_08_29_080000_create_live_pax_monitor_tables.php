<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('live_pax_sessions')) {
            Schema::create('live_pax_sessions', function (Blueprint $t) {
                $t->id();
                $t->string('session_key', 80)->unique();
                $t->string('visitor_key', 80)->index();
                $t->string('current_path', 500)->nullable()->index();
                $t->string('current_title', 255)->nullable();
                $t->string('funnel_stage', 80)->nullable()->index();
                $t->string('device_type', 24)->nullable()->index();
                $t->string('browser', 80)->nullable();
                $t->string('operating_system', 80)->nullable();
                $t->string('country', 80)->nullable()->index();
                $t->string('country_code', 8)->nullable()->index();
                $t->string('region', 120)->nullable();
                $t->string('city', 120)->nullable();
                $t->text('referrer')->nullable();
                $t->string('source', 120)->nullable()->index();
                $t->string('ip_hash', 64)->nullable()->index();
                $t->unsignedInteger('viewport_width')->nullable();
                $t->unsignedInteger('viewport_height')->nullable();
                $t->unsignedInteger('pages_visited')->default(0);
                $t->timestamp('started_at')->nullable()->index();
                $t->timestamp('last_activity_at')->nullable()->index();
                $t->timestamp('last_heartbeat_at')->nullable()->index();
                $t->timestamp('ended_at')->nullable()->index();
                $t->boolean('recording_enabled')->default(true);
                $t->boolean('is_sensitive_page')->default(false);
                $t->timestamps();
            });
        }

        if (!Schema::hasTable('live_pax_page_views')) {
            Schema::create('live_pax_page_views', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('session_id')->index();
                $t->string('path', 500)->index();
                $t->string('page_title', 255)->nullable();
                $t->string('funnel_stage', 80)->nullable()->index();
                $t->timestamp('entered_at')->nullable()->index();
                $t->timestamp('exited_at')->nullable();
                $t->unsignedInteger('duration_seconds')->default(0);
                $t->boolean('sensitive')->default(false);
                $t->timestamps();
            });
        }

        if (!Schema::hasTable('live_pax_recording_chunks')) {
            Schema::create('live_pax_recording_chunks', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('session_id')->index();
                $t->unsignedInteger('sequence')->default(0);
                $t->string('path', 500)->nullable()->index();
                $t->unsignedBigInteger('started_ms')->nullable();
                $t->unsignedBigInteger('ended_ms')->nullable();
                $t->unsignedInteger('event_count')->default(0);
                $t->longText('payload_json');
                $t->timestamp('created_at')->nullable()->index();
                $t->unique(['session_id', 'sequence']);
            });
        }

        if (!Schema::hasTable('live_pax_interactions')) {
            Schema::create('live_pax_interactions', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('session_id')->index();
                $t->string('event_type', 40)->index();
                $t->string('path', 500)->nullable()->index();
                $t->unsignedInteger('x')->nullable();
                $t->unsignedInteger('y')->nullable();
                $t->unsignedInteger('viewport_width')->nullable();
                $t->unsignedInteger('viewport_height')->nullable();
                $t->string('target_tag', 40)->nullable();
                $t->string('target_hint', 160)->nullable();
                $t->unsignedInteger('relative_ms')->nullable();
                $t->timestamp('created_at')->nullable()->index();
            });
        }

        // Grant the new capability to operational leadership only by default.
        if (Schema::hasTable('crm_access_roles') && Schema::hasTable('crm_access_role_permissions')) {
            $roleIds = DB::table('crm_access_roles')->whereIn('slug', ['super-admin', 'manager'])->pluck('id');
            foreach ($roleIds as $roleId) {
                DB::table('crm_access_role_permissions')->updateOrInsert(
                    ['role_id' => $roleId, 'permission_key' => 'live_pax.view'],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('crm_access_role_permissions')) {
            DB::table('crm_access_role_permissions')->where('permission_key', 'live_pax.view')->delete();
        }
        Schema::dropIfExists('live_pax_interactions');
        Schema::dropIfExists('live_pax_recording_chunks');
        Schema::dropIfExists('live_pax_page_views');
        Schema::dropIfExists('live_pax_sessions');
    }
};
