<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('meta_monitor_sources')) {
            return;
        }

        Schema::table('meta_monitor_sources', function (Blueprint $table) {
            if (!Schema::hasColumn('meta_monitor_sources', 'adapter')) {
                $table->string('adapter', 120)
                    ->default('fareleaders')
                    ->after('source_match')
                    ->index();
            }

            if (!Schema::hasColumn('meta_monitor_sources', 'http_method')) {
                $table->string('http_method', 10)
                    ->default('GET')
                    ->after('entry_url');
            }

            if (!Schema::hasColumn('meta_monitor_sources', 'request_mapping')) {
                $table->json('request_mapping')
                    ->nullable()
                    ->after('http_method');
            }

            if (!Schema::hasColumn('meta_monitor_sources', 'auth_type')) {
                $table->string('auth_type', 40)
                    ->default('none')
                    ->after('request_mapping');
            }

            if (!Schema::hasColumn('meta_monitor_sources', 'auth_config')) {
                $table->json('auth_config')
                    ->nullable()
                    ->after('auth_type');
            }

            if (!Schema::hasColumn('meta_monitor_sources', 'monitor_config')) {
                $table->json('monitor_config')
                    ->nullable()
                    ->after('auth_config');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('meta_monitor_sources')) {
            return;
        }

        Schema::table('meta_monitor_sources', function (Blueprint $table) {
            $columns = [
                'adapter',
                'http_method',
                'request_mapping',
                'auth_type',
                'auth_config',
                'monitor_config',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('meta_monitor_sources', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
