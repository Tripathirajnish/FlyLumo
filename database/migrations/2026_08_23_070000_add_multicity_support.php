<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('markups', function (Blueprint $table) {
            if (!Schema::hasColumn('markups', 'is_multicity')) {
                $table->string('is_multicity', 3)->default('No')->after('is_roundtrip');
            }
        });
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'o_Itinerary')) {
                $table->longText('o_Itinerary')->nullable()->after('o_SectorBack');
            }
        });
    }
    public function down(): void
    {
        Schema::table('markups', function (Blueprint $table) {
            if (Schema::hasColumn('markups', 'is_multicity')) $table->dropColumn('is_multicity');
        });
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'o_Itinerary')) $table->dropColumn('o_Itinerary');
        });
    }
};
