<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lead_quotations')) return;

        Schema::create('lead_quotations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id')->index();
            $table->unsignedBigInteger('admin_id')->nullable()->index();
            $table->string('quote_number', 60)->unique();
            $table->string('status', 20)->default('Draft')->index();
            $table->string('currency', 10)->default('USD');
            $table->decimal('base_fare', 12, 2)->default(0);
            $table->decimal('markup', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->date('valid_until')->nullable();
            $table->text('itinerary_summary')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();

            $table->index(['lead_id','status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_quotations');
    }
};
