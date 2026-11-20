<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fare_rule_versions_v2', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fare_rule_id')
                ->constrained('fare_rules_v2')
                ->cascadeOnDelete();

            $table->unsignedInteger('version_number');
            $table->json('configuration');
            $table->text('change_summary')->nullable();

            $table->string('status', 30)->default('draft');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();

            $table->dateTime('approved_at')->nullable();
            $table->dateTime('published_at')->nullable();

            $table->unsignedInteger('rolled_back_from_version')->nullable();

            $table->timestamps();

            $table->unique(['fare_rule_id', 'version_number'], 'frv2_rule_version_unique');
            $table->index(['fare_rule_id', 'status'], 'frv2_rule_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fare_rule_versions_v2');
    }
};
