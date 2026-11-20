<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_cms_cards', function (Blueprint $table) {
            if (!Schema::hasColumn('website_cms_cards', 'fare_mode')) {
                $table->string('fare_mode', 20)->default('manual')->after('return_days');
            }
            if (!Schema::hasColumn('website_cms_cards', 'display_fare')) {
                $table->decimal('display_fare', 10, 2)->nullable()->after('fare_mode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('website_cms_cards', function (Blueprint $table) {
            if (Schema::hasColumn('website_cms_cards', 'display_fare')) $table->dropColumn('display_fare');
            if (Schema::hasColumn('website_cms_cards', 'fare_mode')) $table->dropColumn('fare_mode');
        });
    }
};
