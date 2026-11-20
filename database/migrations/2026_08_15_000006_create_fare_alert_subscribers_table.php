<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('fare_alert_subscribers')) {
            Schema::create('fare_alert_subscribers', function (Blueprint $table) {
                $table->id();
                $table->string('email',190)->index();
                $table->string('home_airport',10)->nullable();
                $table->string('status',20)->default('active')->index();
                $table->string('source',40)->default('website_footer');
                $table->timestamps();
                $table->unique(['email','home_airport']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fare_alert_subscribers');
    }
};
