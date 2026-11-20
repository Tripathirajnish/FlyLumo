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
        Schema::create('fare_rule_conditions_v2', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fare_rule_version_id')
                ->constrained('fare_rule_versions_v2')
                ->cascadeOnDelete();

            $table->unsignedInteger('condition_group')->default(1);
            $table->unsignedInteger('sort_order')->default(0);

            $table->string('field', 80);
            $table->string('operator', 30);
            $table->json('value');

            $table->timestamps();

            $table->index(
                ['fare_rule_version_id', 'condition_group'],
                'frc_v2_version_group_idx'
            );
            $table->index(
                ['field', 'operator'],
                'frc_v2_field_operator_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fare_rule_conditions_v2');
    }
};
