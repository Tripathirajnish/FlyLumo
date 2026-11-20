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
        Schema::create('booking_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('attempt_key', 64)->unique();
            $table->string('status', 32)->default('processing')->index();
            $table->unsignedBigInteger('booking_id')->nullable()->index();
            $table->string('supplier_book_id', 100)->nullable()->index();
            $table->string('supplier_book_guid', 150)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('last_error_code', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_attempts');
    }
};
