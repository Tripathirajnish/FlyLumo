<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('settings')) return;

        foreach (['demo','live'] as $env) {
            $legacy = DB::table('settings')->where('name',"api_{$env}_soap")->value('value');

            if ($legacy) {
                DB::table('settings')->updateOrInsert(
                    ['name'=>"api_{$env}_search_soap"],
                    ['value'=>$legacy,'updated_at'=>now()]
                );
                DB::table('settings')->updateOrInsert(
                    ['name'=>"api_{$env}_general_soap"],
                    ['value'=>$legacy,'updated_at'=>now()]
                );
            }
        }
    }

    public function down(): void {}
};
