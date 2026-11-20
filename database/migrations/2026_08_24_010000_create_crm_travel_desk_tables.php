<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('crm_travel_requests')) {
            Schema::create('crm_travel_requests', function (Blueprint $table) {
                $table->id();
                $table->string('reference', 40)->unique();
                $table->string('type', 30)->index(); // flight, hotel, car, package, custom
                $table->string('status', 30)->default('Draft')->index();
                $table->string('source', 40)->default('CRM')->index();
                $table->string('customer_name', 190);
                $table->string('customer_email', 190)->nullable()->index();
                $table->string('customer_phone', 80)->nullable();
                $table->string('destination', 190)->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->unsignedSmallInteger('travelers')->default(1);
                $table->string('currency', 8)->default('USD');
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->decimal('service_fee', 12, 2)->default(0);
                $table->decimal('discount', 12, 2)->default(0);
                $table->decimal('total', 12, 2)->default(0);
                $table->string('payment_status', 30)->default('Unpaid')->index();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('assigned_to')->nullable()->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('crm_travel_request_items')) {
            Schema::create('crm_travel_request_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('travel_request_id')->index();
                $table->string('item_type', 30); // flight/hotel/car/activity/transfer/fee/other
                $table->string('title', 190);
                $table->text('description')->nullable();
                $table->decimal('quantity', 10, 2)->default(1);
                $table->decimal('unit_price', 12, 2)->default(0);
                $table->decimal('total', 12, 2)->default(0);
                $table->json('meta')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_travel_request_items');
        Schema::dropIfExists('crm_travel_requests');
    }
};
