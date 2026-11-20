<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payment_crm_meta')) return;
        if (Schema::hasColumn('payment_crm_meta', 'refund_reason')) return;

        Schema::table('payment_crm_meta', function (Blueprint $table) {
            $table->text('refund_reason')->nullable()->after('refund_reference');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('payment_crm_meta')) return;
        if (!Schema::hasColumn('payment_crm_meta', 'refund_reason')) return;

        Schema::table('payment_crm_meta', function (Blueprint $table) {
            $table->dropColumn('refund_reason');
        });
    }
};
