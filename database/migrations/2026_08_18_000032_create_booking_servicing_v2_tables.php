<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  if(!Schema::hasTable('booking_service_history')) Schema::create('booking_service_history',function(Blueprint $t){$t->id();$t->unsignedBigInteger('booking_id')->index();$t->unsignedBigInteger('admin_id')->nullable()->index();$t->string('area',80);$t->string('action',150);$t->longText('before_json')->nullable();$t->longText('after_json')->nullable();$t->text('note')->nullable();$t->timestamps();});
  if(!Schema::hasTable('booking_fare_history')) Schema::create('booking_fare_history',function(Blueprint $t){$t->id();$t->unsignedBigInteger('booking_id')->index();$t->unsignedBigInteger('admin_id')->nullable()->index();$t->decimal('old_total',12,2)->nullable();$t->decimal('new_total',12,2);$t->decimal('markup_amount',12,2)->nullable();$t->text('reason');$t->timestamps();});
  if(!Schema::hasTable('booking_card_metadata')) Schema::create('booking_card_metadata',function(Blueprint $t){$t->id();$t->unsignedBigInteger('booking_id')->unique();$t->string('cardholder_name',150)->nullable();$t->string('brand',50)->nullable();$t->string('last4',4)->nullable();$t->unsignedTinyInteger('expire_month')->nullable();$t->unsignedSmallInteger('expire_year')->nullable();$t->text('note')->nullable();$t->timestamps();});
 }
 public function down(): void { Schema::dropIfExists('booking_card_metadata'); Schema::dropIfExists('booking_fare_history'); Schema::dropIfExists('booking_service_history'); }
};
