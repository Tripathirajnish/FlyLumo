<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('crm_email_logs')) {
            Schema::create('crm_email_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('booking_id')->index();
                $table->unsignedBigInteger('admin_id')->nullable()->index();
                $table->string('email_type', 80)->default('booking_confirmation');
                $table->string('recipient', 255);
                $table->string('subject', 255)->nullable();
                $table->string('status', 30)->default('sent')->index();
                $table->string('source', 60)->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();

                $table->index(['booking_id', 'email_type']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_email_logs');
    }
};
