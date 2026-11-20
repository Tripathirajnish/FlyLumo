<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { if(!Schema::hasTable('crm_integrations')) Schema::create('crm_integrations',function(Blueprint $t){$t->id();$t->string('name',120);$t->string('category',40)->index();$t->string('provider',120)->nullable();$t->string('status',20)->default('draft')->index();$t->string('base_url',500)->nullable();$t->string('auth_type',30)->default('none');$t->text('credentials_encrypted')->nullable();$t->json('settings')->nullable();$t->json('field_map')->nullable();$t->unsignedBigInteger('created_by')->nullable();$t->timestamps();}); }
 public function down(): void { Schema::dropIfExists('crm_integrations'); }
};
