<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lead_crm_meta')) return;

        Schema::create('lead_crm_meta', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id')->unique();
            $table->string('stage',40)->default('New')->index();
            $table->string('priority',20)->default('Normal')->index();
            $table->string('source',100)->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable()->index();
            $table->string('next_action',255)->nullable();
            $table->dateTime('next_follow_up_at')->nullable()->index();
            $table->string('travel_from',100)->nullable();
            $table->string('travel_to',100)->nullable();
            $table->date('return_date')->nullable();
            $table->unsignedSmallInteger('adults')->nullable();
            $table->unsignedSmallInteger('children')->nullable();
            $table->unsignedSmallInteger('infants')->nullable();
            $table->string('cabin',50)->nullable();
            $table->decimal('budget',12,2)->nullable();
            $table->text('private_note')->nullable();
            $table->timestamps();

            $table->index(['stage','priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_crm_meta');
    }
};
