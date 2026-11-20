<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fare_rule_rollback_audits_v2', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('fare_rule_id');

            $table->unsignedInteger('from_version');
            $table->unsignedInteger('target_version');
            $table->unsignedInteger('new_version');

            $table->unsignedBigInteger('requested_by');
            $table->unsignedBigInteger('approved_by');

            $table->text('reason');

            $table->string('status', 30)->default('completed');

            $table->timestamp('executed_at');

            $table->timestamps();

            $table->foreign('fare_rule_id')
                ->references('id')
                ->on('fare_rules_v2')
                ->restrictOnDelete();

            $table->index(['fare_rule_id', 'executed_at'],
                'fr_rollback_rule_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fare_rule_rollback_audits_v2');
    }
};
