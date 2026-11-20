<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('booking_crm_meta')) return;

        Schema::create('booking_crm_meta', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->unique();
            $table->string('pnr', 50)->nullable()->index();
            $table->string('ticket_number', 100)->nullable()->index();
            $table->string('payment_status', 40)->nullable()->index();
            $table->string('ticket_status', 40)->nullable()->index();
            $table->decimal('markup_amount', 12, 2)->nullable();
            $table->string('via_source', 100)->nullable();
            $table->string('next_action', 255)->nullable();
            $table->dateTime('next_action_at')->nullable()->index();
            $table->text('operations_note')->nullable();
            $table->timestamps();

            $table->index(['payment_status','ticket_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_crm_meta');
    }
};
