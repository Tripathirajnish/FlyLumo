<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stripe_payment_attempts', function (Blueprint $table) {
            $table->char('checkout_identity_hash', 64)
                ->nullable()
                ->unique();
        });
    }

    public function down(): void
    {
        Schema::table('stripe_payment_attempts', function (Blueprint $table) {
            $table->dropUnique([
                'checkout_identity_hash'
            ]);

            $table->dropColumn('checkout_identity_hash');
        });
    }
};
