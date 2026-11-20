<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_cms_cards', function (Blueprint $table) {
            if (!Schema::hasColumn('website_cms_cards', 'trip_type')) {
                $table->string('trip_type', 20)->default('round_trip')->after('days');
            }

            if (!Schema::hasColumn('website_cms_cards', 'return_days')) {
                $table->unsignedSmallInteger('return_days')->nullable()->default(7)->after('trip_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('website_cms_cards', function (Blueprint $table) {
            if (Schema::hasColumn('website_cms_cards', 'return_days')) {
                $table->dropColumn('return_days');
            }

            if (Schema::hasColumn('website_cms_cards', 'trip_type')) {
                $table->dropColumn('trip_type');
            }
        });
    }
};
