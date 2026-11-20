<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('contents')) return;

        $now = now();

        $pages = [
            [
                'type'=>'Basic','slug'=>'about-us','title'=>'About Us',
                'description'=>'<h2>About Travel N Breakz</h2><p>Write your About Us content here from CRM → Pages.</p>',
                'sequence'=>10,'seo_title'=>'About Us','seo_keyword'=>'About Us',
                'seo_description'=>'Learn more about Travel N Breakz.'
            ],
            [
                'type'=>'Basic','slug'=>'why-choose-us','title'=>'Why Choose Us',
                'description'=>'<h2>Why Choose Travel N Breakz</h2><p>Write your customer value proposition here from CRM → Pages.</p>',
                'sequence'=>11,'seo_title'=>'Why Choose Us','seo_keyword'=>'Why Choose Us',
                'seo_description'=>'Why travelers choose Travel N Breakz.'
            ],
            [
                'type'=>'Basic','slug'=>'contact-us','title'=>'Contact Us',
                'description'=>'<p>Need help with a booking or have a travel question? Contact our support team using the details and form below.</p>',
                'sequence'=>12,'seo_title'=>'Contact Us','seo_keyword'=>'Contact Us',
                'seo_description'=>'Contact Travel N Breakz.'
            ],
            [
                'type'=>'Legal','slug'=>'cookie-policy','title'=>'Cookie Policy',
                'description'=>'<h2>Cookie Policy</h2><p>Write your Cookie Policy here from CRM → Pages.</p>',
                'sequence'=>20,'seo_title'=>'Cookie Policy','seo_keyword'=>'Cookie Policy',
                'seo_description'=>'Travel N Breakz Cookie Policy.'
            ],
            [
                'type'=>'Legal','slug'=>'cancellation-policy','title'=>'Cancellation Policy',
                'description'=>'<h2>Cancellation Policy</h2><p>Write your cancellation and change policy here from CRM → Pages.</p>',
                'sequence'=>21,'seo_title'=>'Cancellation Policy','seo_keyword'=>'Cancellation Policy',
                'seo_description'=>'Travel N Breakz Cancellation Policy.'
            ],
        ];

        foreach ($pages as $page) {
            if (!DB::table('contents')->where('slug',$page['slug'])->exists()) {
                DB::table('contents')->insert(array_merge($page, [
                    'banner'=>null,
                    'is_visible'=>'1',
                    'created_at'=>$now,
                    'updated_at'=>$now,
                ]));
            }
        }

        // Replace only obvious placeholder "/" footer targets.
        if (Schema::hasTable('website_cms_settings')) {
            $fixes = [
                'site.footer.col3.u1'=>'/about-us',
                'site.footer.col3.u2'=>'/why-choose-us',
                'site.footer.col4.u2'=>'/return-policy',
                'site.footer.col4.u3'=>'/privacy-policy',
                'site.footer.col4.u4'=>'/terms-of-use',
                'site.footer.bottom.u1'=>'/privacy-policy',
                'site.footer.bottom.u2'=>'/terms-of-use',
            ];

            foreach ($fixes as $key=>$url) {
                $row = DB::table('website_cms_settings')->where('key',$key)->first();
                if ($row && trim((string)$row->value) === '/') {
                    DB::table('website_cms_settings')->where('key',$key)->update([
                        'value'=>$url,
                        'updated_at'=>$now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // Do not delete content pages automatically: users may have edited them.
    }
};
