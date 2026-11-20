<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('customer_crm_meta')) return;
        Schema::create('customer_crm_meta', function(Blueprint $table){
            $table->id();
            $table->string('email',255)->unique();
            $table->string('customer_status',40)->default('Active')->index();
            $table->string('customer_tier',30)->default('Standard')->index();
            $table->boolean('vip_flag')->default(false)->index();
            $table->unsignedBigInteger('assigned_to')->nullable()->index();
            $table->string('preferred_airline',100)->nullable();
            $table->string('preferred_cabin',50)->nullable();
            $table->string('home_airport',20)->nullable();
            $table->string('preferred_contact',30)->nullable();
            $table->string('next_action',255)->nullable();
            $table->dateTime('next_action_at')->nullable()->index();
            $table->text('private_note')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('customer_crm_meta'); }
};
