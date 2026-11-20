<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('booking_authorizations')) return;

        Schema::table('booking_authorizations', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_authorizations','card_expiry_month')) $table->string('card_expiry_month',2)->nullable()->after('card_last4');
            if (!Schema::hasColumn('booking_authorizations','card_expiry_year')) $table->string('card_expiry_year',4)->nullable()->after('card_expiry_month');
            if (!Schema::hasColumn('booking_authorizations','recipient_email')) $table->string('recipient_email')->nullable()->after('admin_id');
            if (!Schema::hasColumn('booking_authorizations','emailed_at')) $table->timestamp('emailed_at')->nullable()->after('viewed_at');
            if (!Schema::hasColumn('booking_authorizations','email_status')) $table->string('email_status',30)->nullable()->after('recipient_email');
            if (!Schema::hasColumn('booking_authorizations','token_encrypted')) $table->longText('token_encrypted')->nullable()->after('token_hash');
        });
    }

    public function down(): void
    {
        // Intentionally non-destructive. Authorization evidence fields are retained.
    }
};
