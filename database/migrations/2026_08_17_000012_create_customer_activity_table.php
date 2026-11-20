<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('customer_activity')) return;
        Schema::create('customer_activity', function(Blueprint $table){
            $table->id();
            $table->string('customer_email',255)->index();
            $table->unsignedBigInteger('admin_id')->nullable()->index();
            $table->string('activity_type',40)->index();
            $table->string('title',255);
            $table->text('details')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('customer_activity'); }
};
