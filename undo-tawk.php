<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\DB::table('settings')->where('name','footer_script')
  ->update(['value' => file_get_contents(__DIR__.'/storage/footer_script.before-tawk-20261026.txt')]);
echo "purana footer_script wapas\n";
