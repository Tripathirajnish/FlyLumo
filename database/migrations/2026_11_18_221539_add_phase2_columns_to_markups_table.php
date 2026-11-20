
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('markups', function (Blueprint $table) {
            $table->string('rule_action', 20)
                ->default('discount');

            $table->unsignedSmallInteger('adv_min_days')
                ->nullable();

            $table->unsignedSmallInteger('adv_max_days')
                ->nullable();
        });

        Cache::forget('markups_phase2_cols');
    }

    public function down(): void
    {
        Schema::table('markups', function (Blueprint $table) {
            $table->dropColumn([
                'rule_action',
                'adv_min_days',
                'adv_max_days'
            ]);
        });

        Cache::forget('markups_phase2_cols');
    }
};

