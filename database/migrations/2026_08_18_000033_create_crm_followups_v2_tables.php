<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if(!Schema::hasTable('crm_followups')) {
            Schema::create('crm_followups',function(Blueprint $t){
                $t->id(); $t->unsignedBigInteger('booking_id')->nullable()->index(); $t->string('subject');
                $t->string('followup_type',50)->default('General')->index(); $t->string('priority',20)->default('Normal')->index();
                $t->string('status',30)->default('Pending')->index(); $t->unsignedBigInteger('assigned_to')->nullable()->index();
                $t->dateTime('due_at')->nullable()->index(); $t->text('notes')->nullable(); $t->unsignedBigInteger('created_by')->nullable();
                $t->dateTime('completed_at')->nullable(); $t->dateTime('deleted_at')->nullable()->index(); $t->timestamps();
            });
        }
        if(!Schema::hasTable('crm_followup_audit')) {
            Schema::create('crm_followup_audit',function(Blueprint $t){
                $t->id(); $t->unsignedBigInteger('followup_id')->index(); $t->unsignedBigInteger('admin_id')->nullable()->index();
                $t->string('action',50); $t->longText('old_value')->nullable(); $t->longText('new_value')->nullable(); $t->timestamp('created_at')->nullable();
            });
        }
    }
    public function down(): void { Schema::dropIfExists('crm_followup_audit'); Schema::dropIfExists('crm_followups'); }
};
