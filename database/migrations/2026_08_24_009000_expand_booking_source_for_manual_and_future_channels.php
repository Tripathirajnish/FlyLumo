<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'source')) {
            // The legacy schema used ENUM('Website','FareLeader').
            // A VARCHAR keeps all existing values intact while allowing Manual,
            // Jetcost, FareCompare and future meta/source identifiers without
            // another schema migration for every partner.
            DB::statement("ALTER TABLE `bookings` MODIFY `source` VARCHAR(40) NOT NULL DEFAULT 'Website'");
        }
    }

    public function down(): void
    {
        // Intentionally keep VARCHAR on rollback. Converting back to the old
        // two-value ENUM could destroy valid Manual/future source values.
    }
};
