<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Step F (owner-approved D1, 5 Nov 2026): one encrypted booking snapshot per Stripe attempt.
 * Holds passenger / contact / billing data and the session price data needed to book after
 * the Stripe webhook. NEVER card data. New Stripe-only table; no other table is touched.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('stripe_booking_snapshots')) {
            return;
        }

        Schema::create('stripe_booking_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attempt_id')->unique();
            $table->unsignedTinyInteger('payload_version')->default(1);
            $table->longText('payload');
            $table->timestamp('consumed_at')->nullable();
            $table->timestamp('purge_after')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stripe_booking_snapshots');
    }
};
