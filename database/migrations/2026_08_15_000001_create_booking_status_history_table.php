<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('booking_status_history')) {
            Schema::create('booking_status_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('booking_id')->index();
                $table->unsignedBigInteger('admin_id')->nullable()->index();
                $table->string('old_status', 100)->nullable();
                $table->string('new_status', 100);
                $table->text('note')->nullable();
                $table->timestamps();

                $table->index(['booking_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_status_history');
    }
};
