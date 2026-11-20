<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('crm_api_control_logs')) {
            Schema::create('crm_api_control_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('admin_id')->nullable()->index();
                $table->string('mode',20)->nullable()->index();
                $table->string('action',60)->index();
                $table->string('status',30)->index();
                $table->text('message')->nullable();
                $table->unsignedInteger('latency_ms')->nullable();
                $table->timestamp('created_at')->nullable()->index();
            });
        }
    }

    public function down(): void {}
};
