<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profit_report_meta', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->unique();
            $table->decimal('supplier_fees', 12, 2)->default(0);
            $table->string('issuance_type', 100)->nullable();
            $table->date('issuance_date')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->index('updated_by');
            $table->index('issuance_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profit_report_meta');
    }
};
