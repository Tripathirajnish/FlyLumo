<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('website_cms_settings')) return;

        $now = now();
        $settings = [
            ['group'=>'global','key'=>'site.footer.description','value'=>'We make your dream more beautiful & enjoyable with lots of happiness.','type'=>'textarea','label'=>'Footer brand description','sort_order'=>10],
            ['group'=>'global','key'=>'site.footer.payment_title','value'=>'Secure payments','type'=>'text','label'=>'Footer payment eyebrow','sort_order'=>11],
            ['group'=>'global','key'=>'site.footer.payment_subtitle','value'=>'We accept major payment methods','type'=>'text','label'=>'Footer payment text','sort_order'=>12],
            ['group'=>'global','key'=>'site.footer.disclaimer_title','value'=>'Important information','type'=>'text','label'=>'Disclaimer heading','sort_order'=>13],
            ['group'=>'global','key'=>'site.footer.disclaimer','value'=>'','type'=>'textarea','label'=>'Footer disclaimer override (leave blank to use existing website disclaimer)','sort_order'=>14],
            ['group'=>'global','key'=>'site.footer.copyright_name','value'=>'Travel N Breakz','type'=>'text','label'=>'Copyright brand name','sort_order'=>15],
            ['group'=>'global','key'=>'site.footer.copyright_suffix','value'=>'All Rights Reserved.','type'=>'text','label'=>'Copyright suffix','sort_order'=>16],
            ['group'=>'global','key'=>'site.footer.secure_text','value'=>'Secure booking experience','type'=>'text','label'=>'Footer security text','sort_order'=>17],
        ];

        foreach ($settings as $s) {
            if (!DB::table('website_cms_settings')->where('key',$s['key'])->exists()) {
                DB::table('website_cms_settings')->insert(array_merge($s, ['created_at'=>$now,'updated_at'=>$now]));
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('website_cms_settings')) return;
        DB::table('website_cms_settings')->whereIn('key', [
            'site.footer.description','site.footer.payment_title','site.footer.payment_subtitle',
            'site.footer.disclaimer_title','site.footer.disclaimer','site.footer.copyright_name',
            'site.footer.copyright_suffix','site.footer.secure_text'
        ])->delete();
    }
};
