<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('fare_rule_activity')) return;
        Schema::create('fare_rule_activity', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('markup_id')->nullable()->index();
            $table->unsignedBigInteger('admin_id')->nullable()->index();
            $table->string('action',50)->index();
            $table->string('summary',255);
            $table->longText('before_json')->nullable();
            $table->longText('after_json')->nullable();
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fare_rule_activity');
    }
};
