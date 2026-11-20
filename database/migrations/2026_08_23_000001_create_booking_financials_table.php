<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_financials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->unique();

            // Snapshot / operational finance values entered in CRM.
            $table->decimal('selling_amount', 12, 2)->default(0);
            $table->decimal('supplier_cost', 12, 2)->default(0);
            $table->decimal('processing_cost', 12, 2)->default(0);
            $table->decimal('other_cost', 12, 2)->default(0);

            // Stored server-side so reports do not depend on browser calculations.
            $table->decimal('gross_profit', 12, 2)->default(0);
            $table->decimal('net_profit', 12, 2)->default(0);

            $table->unsignedBigInteger('updated_by')->nullable();
            $table->text('finance_note')->nullable();
            $table->timestamps();

            $table->index('updated_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_financials');
    }
};
