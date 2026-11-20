<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('payment_crm_meta')) return;
        Schema::create('payment_crm_meta', function(Blueprint $table){
            $table->id(); $table->unsignedBigInteger('booking_id')->unique();
            $table->string('payment_status',40)->default('Untracked')->index();
            $table->string('transaction_reference',120)->nullable()->index();
            $table->string('processor',100)->nullable();
            $table->decimal('paid_amount',12,2)->nullable();
            $table->decimal('refund_amount',12,2)->nullable();
            $table->string('refund_reference',120)->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable()->index();
            $table->string('next_action',255)->nullable();
            $table->dateTime('next_action_at')->nullable()->index();
            $table->text('operations_note')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('payment_crm_meta'); }
};
