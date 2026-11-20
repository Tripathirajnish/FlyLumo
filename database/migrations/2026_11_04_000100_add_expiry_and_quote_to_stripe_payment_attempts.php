<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stripe-only table. Adds attempt expiry and the fresh supplier quote.
 * Does not touch any other table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stripe_payment_attempts', function (Blueprint $table) {
            if (!Schema::hasColumn('stripe_payment_attempts', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->index();
            }
            if (!Schema::hasColumn('stripe_payment_attempts', 'supplier_amount_cents')) {
                $table->unsignedBigInteger('supplier_amount_cents')->nullable();
            }
            if (!Schema::hasColumn('stripe_payment_attempts', 'quote_book_limit')) {
                $table->string('quote_book_limit', 40)->nullable();
            }
            if (!Schema::hasColumn('stripe_payment_attempts', 'search_code_hash')) {
                $table->char('search_code_hash', 64)->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('stripe_payment_attempts', function (Blueprint $table) {
            $table->dropIndex(['expires_at']);
            $table->dropIndex(['search_code_hash']);
            $table->dropColumn([
                'expires_at',
                'supplier_amount_cents',
                'quote_book_limit',
                'search_code_hash',
            ]);
        });
    }
};
