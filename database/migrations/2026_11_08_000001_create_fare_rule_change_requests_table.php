<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('fare_rule_change_requests')) {
            return;
        }

        Schema::create('fare_rule_change_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('markup_id')->nullable()->index();
            $table->string('operation', 20);
            $table->string('status', 30)->default('pending')->index();
            $table->longText('proposed_data')->nullable();
            $table->longText('previous_data')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Intentionally non-destructive.
        // Preserve approval requests and audit history during rollback.
    }
};
