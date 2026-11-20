<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profit_report_meta', function (Blueprint $table) {
            $table->date('report_date')->nullable()->after('booking_id');
            $table->string('booking_id_display', 100)->nullable()->after('report_date');
            $table->string('pnr_override', 100)->nullable()->after('booking_id_display');
            $table->text('pax_names_override')->nullable()->after('pnr_override');
            $table->string('travel_dates_override', 255)->nullable()->after('pax_names_override');
            $table->string('trip_type_override', 100)->nullable()->after('travel_dates_override');
            $table->string('route_override', 255)->nullable()->after('trip_type_override');
            $table->string('airlines_override', 255)->nullable()->after('route_override');

            $table->decimal('sale_amount_override', 12, 2)->nullable()->after('airlines_override');
            $table->decimal('ticket_cost_override', 12, 2)->nullable()->after('sale_amount_override');
            $table->decimal('merchant_fee_override', 12, 2)->nullable()->after('ticket_cost_override');

            $table->unsignedBigInteger('agent_override')->nullable()->after('merchant_fee_override');
            $table->string('status_override', 100)->nullable()->after('agent_override');

            $table->index('agent_override');
            $table->index('report_date');
        });
    }

    public function down(): void
    {
        Schema::table('profit_report_meta', function (Blueprint $table) {
            $table->dropIndex(['agent_override']);
            $table->dropIndex(['report_date']);

            $table->dropColumn([
                'report_date',
                'booking_id_display',
                'pnr_override',
                'pax_names_override',
                'travel_dates_override',
                'trip_type_override',
                'route_override',
                'airlines_override',
                'sale_amount_override',
                'ticket_cost_override',
                'merchant_fee_override',
                'agent_override',
                'status_override',
            ]);
        });
    }
};
