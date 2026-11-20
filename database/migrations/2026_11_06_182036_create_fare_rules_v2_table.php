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
        Schema::create('fare_rules_v2', function (Blueprint $table) {
            $table->id();
            $table->string('rule_code', 80)->unique();
            $table->string('name', 200);
            $table->text('description')->nullable();

            $table->string('status', 30)->default('draft');
            $table->unsignedInteger('priority')->default(100);
            $table->unsignedInteger('current_version')->default(1);

            $table->boolean('stackable')->default(false);
            $table->boolean('stop_processing')->default(true);

            $table->dateTime('effective_from')->nullable();
            $table->dateTime('effective_to')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'priority']);
            $table->index(['effective_from', 'effective_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fare_rules_v2');
    }
};
