<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  if (Schema::hasTable('crm_notification_reads')) return;
  Schema::create('crm_notification_reads', function(Blueprint $table){
   $table->id(); $table->unsignedBigInteger('admin_id')->index();
   $table->string('notification_key',190); $table->timestamp('read_at')->nullable(); $table->timestamps();
   $table->unique(['admin_id','notification_key']);
  });
 }
 public function down(): void { Schema::dropIfExists('crm_notification_reads'); }
};
