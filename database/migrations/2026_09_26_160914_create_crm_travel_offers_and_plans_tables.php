<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('crm_travel_offers')) {
            Schema::create('crm_travel_offers', function (Blueprint $table) {
                $table->id();
                $table->string('reference', 40)->unique();
                $table->string('title', 190);
                $table->string('offer_type', 50)->default('custom')->index();
                $table->string('status', 30)->default('Draft')->index();

                $table->string('currency', 8)->default('USD');
                $table->decimal('price', 12, 2)->default(0);
                $table->string('cabin_class', 40)->nullable();

                $table->unsignedSmallInteger('domestic_trips')->default(0);
                $table->unsignedSmallInteger('international_trips')->default(0);

                $table->json('domestic_airlines')->nullable();
                $table->text('international_airline_rule')->nullable();

                $table->unsignedSmallInteger('domestic_advance_months')->nullable();
                $table->unsignedSmallInteger('international_advance_months')->nullable();

                $table->date('valid_from')->nullable();
                $table->date('valid_until')->nullable()->index();

                $table->unsignedInteger('slot_limit')->nullable();
                $table->string('availability_message', 190)->nullable();

                $table->text('short_description')->nullable();
                $table->longText('benefits')->nullable();
                $table->longText('terms')->nullable();
                $table->string('banner_image', 255)->nullable();

                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->unsignedBigInteger('updated_by')->nullable()->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('crm_travel_offer_versions')) {
            Schema::create('crm_travel_offer_versions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('travel_offer_id')->index();
                $table->unsignedInteger('version')->default(1);

                $table->string('customer_name', 190)->nullable();
                $table->string('customer_email', 190)->nullable()->index();
                $table->string('customer_phone', 80)->nullable();

                $table->string('subject', 255)->nullable();
                $table->longText('offer_snapshot');
                $table->string('status', 30)->default('Prepared')->index();

                $table->timestamp('sent_at')->nullable()->index();
                $table->timestamp('viewed_at')->nullable();
                $table->timestamp('accepted_at')->nullable();
                $table->timestamp('declined_at')->nullable();
                $table->timestamp('expires_at')->nullable();

                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->timestamps();

                $table->unique(['travel_offer_id', 'version', 'customer_email'], 'travel_offer_version_customer_unique');
            });
        }

        if (!Schema::hasTable('crm_travel_plans')) {
            Schema::create('crm_travel_plans', function (Blueprint $table) {
                $table->id();
                $table->string('reference', 40)->unique();

                $table->unsignedBigInteger('travel_offer_id')->nullable()->index();
                $table->unsignedBigInteger('offer_version_id')->nullable()->index();

                $table->string('customer_name', 190);
                $table->string('customer_email', 190)->index();
                $table->string('customer_phone', 80)->nullable();

                $table->string('status', 30)->default('Pending Payment')->index();
                $table->string('payment_status', 30)->default('Unpaid')->index();

                $table->string('currency', 8)->default('USD');
                $table->decimal('sale_amount', 12, 2)->default(0);
                $table->decimal('amount_paid', 12, 2)->default(0);
                $table->decimal('total_cost', 12, 2)->default(0);

                $table->string('payment_reference', 190)->nullable()->index();
                $table->date('starts_on')->nullable();
                $table->date('expires_on')->nullable()->index();

                $table->string('terms_version', 50)->nullable();
                $table->timestamp('agreement_accepted_at')->nullable();

                $table->text('notes')->nullable();
                $table->unsignedBigInteger('assigned_to')->nullable()->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('crm_travel_plan_trips')) {
            Schema::create('crm_travel_plan_trips', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('travel_plan_id')->index();

                $table->string('trip_type', 30)->index();
                $table->unsignedSmallInteger('sequence')->default(1);
                $table->string('title', 190);
                $table->string('status', 30)->default('Available')->index();

                $table->string('cabin_class', 40)->nullable();
                $table->json('eligible_airlines')->nullable();
                $table->unsignedSmallInteger('advance_booking_months')->nullable();

                $table->unsignedBigInteger('booking_id')->nullable()->index();
                $table->string('pnr', 80)->nullable()->index();
                $table->string('airline', 190)->nullable();

                $table->string('origin', 10)->nullable();
                $table->string('destination', 10)->nullable();
                $table->date('departure_date')->nullable();
                $table->date('return_date')->nullable();

                $table->decimal('ticket_cost', 12, 2)->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['travel_plan_id', 'trip_type', 'sequence'], 'travel_plan_trip_sequence_unique');
            });
        }

        if (!Schema::hasTable('crm_travel_plan_activity')) {
            Schema::create('crm_travel_plan_activity', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('travel_plan_id')->index();
                $table->unsignedBigInteger('travel_plan_trip_id')->nullable()->index();
                $table->unsignedBigInteger('admin_id')->nullable()->index();

                $table->string('activity_type', 60)->index();
                $table->string('title', 190);
                $table->longText('details')->nullable();
                $table->longText('meta_json')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_travel_plan_activity');
        Schema::dropIfExists('crm_travel_plan_trips');
        Schema::dropIfExists('crm_travel_plans');
        Schema::dropIfExists('crm_travel_offer_versions');
        Schema::dropIfExists('crm_travel_offers');
    }
};
