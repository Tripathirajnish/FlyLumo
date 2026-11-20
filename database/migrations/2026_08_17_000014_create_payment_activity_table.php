<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('payment_activity')) return;
        Schema::create('payment_activity', function(Blueprint $table){
            $table->id(); $table->unsignedBigInteger('booking_id')->index();
            $table->unsignedBigInteger('admin_id')->nullable()->index();
            $table->string('activity_type',40)->index();
            $table->string('title',255); $table->text('details')->nullable(); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('payment_activity'); }
};
