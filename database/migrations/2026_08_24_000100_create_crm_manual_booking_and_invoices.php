<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('crm_manual_booking_meta')) {
            Schema::create('crm_manual_booking_meta', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('booking_id')->unique();
                $table->string('trip_type',30)->default('one_way');
                $table->string('channel',40)->default('Offline');
                $table->longText('itinerary_json')->nullable();
                $table->longText('financial_json')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('crm_invoices')) {
            Schema::create('crm_invoices', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('booking_id')->index();
                $table->string('invoice_number',80)->unique();
                $table->string('status',30)->default('Issued')->index();
                $table->string('currency',3)->default('USD');
                $table->date('issue_date');
                $table->date('due_date')->nullable();
                $table->decimal('base_fare',12,2)->default(0);
                $table->decimal('taxes',12,2)->default(0);
                $table->decimal('service_fee',12,2)->default(0);
                $table->decimal('addons',12,2)->default(0);
                $table->decimal('discount',12,2)->default(0);
                $table->decimal('total_amount',12,2)->default(0);
                $table->decimal('paid_amount',12,2)->default(0);
                $table->decimal('balance_due',12,2)->default(0);
                $table->string('customer_name',190);
                $table->string('customer_email',190)->nullable();
                $table->string('customer_phone',80)->nullable();
                $table->text('notes')->nullable();
                $table->longText('snapshot_json');
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_invoices');
        Schema::dropIfExists('crm_manual_booking_meta');
    }
};
