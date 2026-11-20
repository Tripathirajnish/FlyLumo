<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('crm_tracking_events')) {
            Schema::create('crm_tracking_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('booking_id')->nullable()->index();
                $table->string('event_type',60)->default('Manual')->index();
                $table->string('title');
                $table->text('note')->nullable();
                $table->unsignedBigInteger('admin_id')->nullable()->index();
                $table->timestamp('created_at')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_tracking_events');
    }
};
