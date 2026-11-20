<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mail_templates')) return;
        Schema::table('mail_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('mail_templates','category')) $table->string('category',100)->nullable()->after('type');
            if (!Schema::hasColumn('mail_templates','is_active')) $table->boolean('is_active')->default(true)->after('variable');
            if (!Schema::hasColumn('mail_templates','is_system')) $table->boolean('is_system')->default(false)->after('is_active');
        });

        DB::table('mail_templates')->whereNull('category')->orWhere('category','')->update(['category' => 'Custom']);
        if (DB::table('mail_templates')->where('id',3)->exists()) {
            DB::table('mail_templates')->where('id',3)->update([
                'category' => 'Booking Confirmation',
                'is_active' => 1,
                'is_system' => 1,
            ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('mail_templates')) return;
        Schema::table('mail_templates', function (Blueprint $table) {
            foreach (['is_system','is_active','category'] as $column) {
                if (Schema::hasColumn('mail_templates',$column)) $table->dropColumn($column);
            }
        });
    }
};
