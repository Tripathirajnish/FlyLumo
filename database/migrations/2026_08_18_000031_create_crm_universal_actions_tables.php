<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('crm_change_logs')) {
            Schema::create('crm_change_logs', function (Blueprint $table) {
                $table->id();
                $table->string('module', 40)->index();
                $table->string('record_key', 255)->index();
                $table->string('field', 120)->nullable()->index();
                $table->string('action', 30)->index();
                $table->longText('old_value')->nullable();
                $table->longText('new_value')->nullable();
                $table->unsignedBigInteger('admin_id')->nullable()->index();
                $table->timestamp('created_at')->nullable()->index();
            });
        }

        if (!Schema::hasTable('crm_archives')) {
            Schema::create('crm_archives', function (Blueprint $table) {
                $table->id();
                $table->string('module', 40)->index();
                $table->string('record_key', 255)->index();
                $table->string('label', 255)->nullable();
                $table->longText('payload')->nullable();
                $table->unsignedBigInteger('admin_id')->nullable()->index();
                $table->timestamp('archived_at')->nullable()->index();
                $table->timestamp('restored_at')->nullable()->index();
                $table->timestamps();
                $table->index(['module','record_key','restored_at'], 'crm_archives_lookup');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_archives');
        Schema::dropIfExists('crm_change_logs');
    }
};
