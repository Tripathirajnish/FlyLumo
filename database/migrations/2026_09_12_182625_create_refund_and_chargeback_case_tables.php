<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('refund_cases')) {
            Schema::create('refund_cases', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('booking_id')->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->unsignedBigInteger('assigned_to')->nullable()->index();
                $table->unsignedBigInteger('approved_by')->nullable()->index();

                $table->string('case_reference', 50)->unique();
                $table->string('refund_type', 50)->index();
                $table->string('status', 40)->default('Requested')->index();

                $table->decimal('requested_amount', 12, 2);
                $table->decimal('approved_amount', 12, 2)->nullable();
                $table->decimal('processed_amount', 12, 2)->nullable();
                $table->string('currency', 8)->default('USD');

                $table->string('processor', 100)->nullable();
                $table->string('transaction_reference', 120)->nullable()->index();
                $table->string('refund_reference', 120)->nullable()->index();

                $table->text('reason');
                $table->text('internal_note')->nullable();

                $table->timestamp('requested_at')->nullable()->index();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('refund_case_events')) {
            Schema::create('refund_case_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('refund_case_id')->index();
                $table->unsignedBigInteger('admin_id')->nullable()->index();
                $table->string('event_type', 50)->index();
                $table->string('from_status', 40)->nullable();
                $table->string('to_status', 40)->nullable();
                $table->decimal('amount', 12, 2)->nullable();
                $table->string('reference', 120)->nullable();
                $table->text('details')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('chargeback_cases')) {
            Schema::create('chargeback_cases', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('booking_id')->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->unsignedBigInteger('assigned_to')->nullable()->index();

                $table->string('case_reference', 50)->unique();
                $table->string('status', 40)->default('New')->index();

                $table->decimal('disputed_amount', 12, 2);
                $table->string('currency', 8)->default('USD');

                $table->string('processor', 100)->nullable();
                $table->string('transaction_reference', 120)->nullable()->index();
                $table->string('processor_case_reference', 120)->nullable()->index();

                $table->string('reason_code', 80)->nullable();
                $table->text('reason')->nullable();

                $table->string('card_brand', 50)->nullable();
                $table->string('card_last4', 4)->nullable();

                $table->date('received_date')->nullable()->index();
                $table->date('response_deadline')->nullable()->index();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamp('closed_at')->nullable();

                $table->string('outcome', 30)->nullable()->index();
                $table->text('internal_note')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('chargeback_case_events')) {
            Schema::create('chargeback_case_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('chargeback_case_id')->index();
                $table->unsignedBigInteger('admin_id')->nullable()->index();
                $table->string('event_type', 50)->index();
                $table->string('from_status', 40)->nullable();
                $table->string('to_status', 40)->nullable();
                $table->text('details')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('chargeback_case_documents')) {
            Schema::create('chargeback_case_documents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('chargeback_case_id')->index();
                $table->unsignedBigInteger('uploaded_by')->nullable()->index();
                $table->string('document_type', 80)->nullable()->index();
                $table->string('original_name');
                $table->string('stored_path');
                $table->string('mime_type', 120)->nullable();
                $table->unsignedBigInteger('size_bytes')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chargeback_case_documents');
        Schema::dropIfExists('chargeback_case_events');
        Schema::dropIfExists('chargeback_cases');
        Schema::dropIfExists('refund_case_events');
        Schema::dropIfExists('refund_cases');
    }
};
