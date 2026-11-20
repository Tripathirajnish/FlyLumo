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
        Schema::create('incomplete_booking_activity', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('incomplete_booking_id')->index();
            $table->unsignedBigInteger('admin_id')->nullable()->index();
            $table->string('activity_type', 50)->index();
            $table->string('title');
            $table->text('details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incomplete_booking_activity');
    }
};
