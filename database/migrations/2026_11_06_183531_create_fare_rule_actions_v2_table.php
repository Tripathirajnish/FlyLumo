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
        Schema::create('fare_rule_actions_v2', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fare_rule_version_id')
                ->constrained('fare_rule_versions_v2')
                ->cascadeOnDelete();

            $table->unsignedInteger('sort_order')->default(0);

            $table->string('action_type', 50);
            $table->string('calculation_base', 40)->default('base_fare');

            $table->decimal('amount', 15, 4);
            $table->char('currency', 3)->nullable();

            $table->decimal('minimum_adjustment', 15, 4)->nullable();
            $table->decimal('maximum_adjustment', 15, 4)->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(
                ['fare_rule_version_id', 'sort_order'],
                'fra_v2_version_order_idx'
            );

            $table->index(
                ['action_type', 'is_active'],
                'fra_v2_type_active_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fare_rule_actions_v2');
    }
};
