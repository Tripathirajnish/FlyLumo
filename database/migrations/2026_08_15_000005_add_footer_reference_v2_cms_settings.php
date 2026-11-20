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
            ['group'=>'global','key'=>'site.footer.alerts.enabled','value'=>'1','type'=>'toggle','label'=>'Show fare-alert signup','sort_order'=>20],
            ['group'=>'global','key'=>'site.footer.alerts.title','value'=>'Never Miss a Flight Deal','type'=>'text','label'=>'Fare-alert heading','sort_order'=>21],
            ['group'=>'global','key'=>'site.footer.alerts.subtitle','value'=>'Subscribe to fare alerts and get useful flight deal updates delivered to your inbox.','type'=>'textarea','label'=>'Fare-alert subtitle','sort_order'=>22],
            ['group'=>'global','key'=>'site.footer.alerts.email_placeholder','value'=>'your@email.com','type'=>'text','label'=>'Email placeholder','sort_order'=>23],
            ['group'=>'global','key'=>'site.footer.alerts.airport_placeholder','value'=>'Home airport (e.g. JFK)','type'=>'text','label'=>'Home airport placeholder','sort_order'=>24],
            ['group'=>'global','key'=>'site.footer.alerts.button','value'=>'Get Alerts','type'=>'text','label'=>'Fare-alert button','sort_order'=>25],
            ['group'=>'global','key'=>'site.footer.alerts.privacy','value'=>'No spam. Unsubscribe anytime. We respect your privacy.','type'=>'text','label'=>'Fare-alert privacy text','sort_order'=>26],
            ['group'=>'global','key'=>'site.footer.trust.enabled','value'=>'1','type'=>'toggle','label'=>'Show footer trust strip','sort_order'=>27],
            ['group'=>'global','key'=>'site.footer.trust.1.title','value'=>'Secure Booking','type'=>'text','label'=>'Trust 1 title','sort_order'=>28],
            ['group'=>'global','key'=>'site.footer.trust.1.sub','value'=>'Encrypted checkout','type'=>'text','label'=>'Trust 1 subtitle','sort_order'=>29],
            ['group'=>'global','key'=>'site.footer.trust.2.title','value'=>'Privacy Protected','type'=>'text','label'=>'Trust 2 title','sort_order'=>30],
            ['group'=>'global','key'=>'site.footer.trust.2.sub','value'=>'Customer data handled securely','type'=>'text','label'=>'Trust 2 subtitle','sort_order'=>31],
            ['group'=>'global','key'=>'site.footer.trust.3.title','value'=>'Safe Payments','type'=>'text','label'=>'Trust 3 title','sort_order'=>32],
            ['group'=>'global','key'=>'site.footer.trust.3.sub','value'=>'Protected payment flow','type'=>'text','label'=>'Trust 3 subtitle','sort_order'=>33],
            ['group'=>'global','key'=>'site.footer.trust.4.title','value'=>'Travel Support','type'=>'text','label'=>'Trust 4 title','sort_order'=>34],
            ['group'=>'global','key'=>'site.footer.trust.4.sub','value'=>'Help when you need it','type'=>'text','label'=>'Trust 4 subtitle','sort_order'=>35],
            ['group'=>'global','key'=>'site.footer.toll_label','value'=>'Toll Free','type'=>'text','label'=>'Toll-free label','sort_order'=>36],
            ['group'=>'global','key'=>'site.footer.col1.title','value'=>'Flights','type'=>'text','label'=>'Footer column 1 title','sort_order'=>41],
            ['group'=>'global','key'=>'site.footer.col2.title','value'=>'Top Destinations','type'=>'text','label'=>'Footer column 2 title','sort_order'=>42],
            ['group'=>'global','key'=>'site.footer.col3.title','value'=>'Company','type'=>'text','label'=>'Footer column 3 title','sort_order'=>43],
            ['group'=>'global','key'=>'site.footer.col4.title','value'=>'Support','type'=>'text','label'=>'Footer column 4 title','sort_order'=>44],
            ['group'=>'global','key'=>'site.footer.col1.l1','value'=>'One Way Flights','type'=>'text','label'=>'Column 1 link 1 label','sort_order'=>50],
            ['group'=>'global','key'=>'site.footer.col1.u1','value'=>'/search-flight','type'=>'text','label'=>'Column 1 link 1 URL','sort_order'=>51],
            ['group'=>'global','key'=>'site.footer.col1.l2','value'=>'Round Trip Flights','type'=>'text','label'=>'Column 1 link 2 label','sort_order'=>52],
            ['group'=>'global','key'=>'site.footer.col1.u2','value'=>'/search-flight','type'=>'text','label'=>'Column 1 link 2 URL','sort_order'=>53],
            ['group'=>'global','key'=>'site.footer.col1.l3','value'=>'International Flights','type'=>'text','label'=>'Column 1 link 3 label','sort_order'=>54],
            ['group'=>'global','key'=>'site.footer.col1.u3','value'=>'/search-flight','type'=>'text','label'=>'Column 1 link 3 URL','sort_order'=>55],
            ['group'=>'global','key'=>'site.footer.col1.l4','value'=>'Business Class Deals','type'=>'text','label'=>'Column 1 link 4 label','sort_order'=>56],
            ['group'=>'global','key'=>'site.footer.col1.u4','value'=>'/search-flight','type'=>'text','label'=>'Column 1 link 4 URL','sort_order'=>57],
            ['group'=>'global','key'=>'site.footer.col1.l5','value'=>'First Class Deals','type'=>'text','label'=>'Column 1 link 5 label','sort_order'=>58],
            ['group'=>'global','key'=>'site.footer.col1.u5','value'=>'/search-flight','type'=>'text','label'=>'Column 1 link 5 URL','sort_order'=>59],
            ['group'=>'global','key'=>'site.footer.col1.l6','value'=>'Flight Offers','type'=>'text','label'=>'Column 1 link 6 label','sort_order'=>60],
            ['group'=>'global','key'=>'site.footer.col1.u6','value'=>'/offers','type'=>'text','label'=>'Column 1 link 6 URL','sort_order'=>61],
            ['group'=>'global','key'=>'site.footer.col2.l1','value'=>'Flights to London','type'=>'text','label'=>'Column 2 link 1 label','sort_order'=>62],
            ['group'=>'global','key'=>'site.footer.col2.u1','value'=>'/destinations','type'=>'text','label'=>'Column 2 link 1 URL','sort_order'=>63],
            ['group'=>'global','key'=>'site.footer.col2.l2','value'=>'Flights to Dubai','type'=>'text','label'=>'Column 2 link 2 label','sort_order'=>64],
            ['group'=>'global','key'=>'site.footer.col2.u2','value'=>'/destinations','type'=>'text','label'=>'Column 2 link 2 URL','sort_order'=>65],
            ['group'=>'global','key'=>'site.footer.col2.l3','value'=>'Flights to Tokyo','type'=>'text','label'=>'Column 2 link 3 label','sort_order'=>66],
            ['group'=>'global','key'=>'site.footer.col2.u3','value'=>'/destinations','type'=>'text','label'=>'Column 2 link 3 URL','sort_order'=>67],
            ['group'=>'global','key'=>'site.footer.col2.l4','value'=>'Flights to Mumbai','type'=>'text','label'=>'Column 2 link 4 label','sort_order'=>68],
            ['group'=>'global','key'=>'site.footer.col2.u4','value'=>'/destinations','type'=>'text','label'=>'Column 2 link 4 URL','sort_order'=>69],
            ['group'=>'global','key'=>'site.footer.col2.l5','value'=>'Flights to Paris','type'=>'text','label'=>'Column 2 link 5 label','sort_order'=>70],
            ['group'=>'global','key'=>'site.footer.col2.u5','value'=>'/destinations','type'=>'text','label'=>'Column 2 link 5 URL','sort_order'=>71],
            ['group'=>'global','key'=>'site.footer.col2.l6','value'=>'All Destinations','type'=>'text','label'=>'Column 2 link 6 label','sort_order'=>72],
            ['group'=>'global','key'=>'site.footer.col2.u6','value'=>'/destinations','type'=>'text','label'=>'Column 2 link 6 URL','sort_order'=>73],
            ['group'=>'global','key'=>'site.footer.col3.l1','value'=>'About Us','type'=>'text','label'=>'Column 3 link 1 label','sort_order'=>74],
            ['group'=>'global','key'=>'site.footer.col3.u1','value'=>'/','type'=>'text','label'=>'Column 3 link 1 URL','sort_order'=>75],
            ['group'=>'global','key'=>'site.footer.col3.l2','value'=>'Why Choose Us','type'=>'text','label'=>'Column 3 link 2 label','sort_order'=>76],
            ['group'=>'global','key'=>'site.footer.col3.u2','value'=>'/','type'=>'text','label'=>'Column 3 link 2 URL','sort_order'=>77],
            ['group'=>'global','key'=>'site.footer.col3.l3','value'=>'Travel Blog','type'=>'text','label'=>'Column 3 link 3 label','sort_order'=>78],
            ['group'=>'global','key'=>'site.footer.col3.u3','value'=>'/travel-guide','type'=>'text','label'=>'Column 3 link 3 URL','sort_order'=>79],
            ['group'=>'global','key'=>'site.footer.col3.l4','value'=>'Offers','type'=>'text','label'=>'Column 3 link 4 label','sort_order'=>80],
            ['group'=>'global','key'=>'site.footer.col3.u4','value'=>'/offers','type'=>'text','label'=>'Column 3 link 4 URL','sort_order'=>81],
            ['group'=>'global','key'=>'site.footer.col3.l5','value'=>'Destinations','type'=>'text','label'=>'Column 3 link 5 label','sort_order'=>82],
            ['group'=>'global','key'=>'site.footer.col3.u5','value'=>'/destinations','type'=>'text','label'=>'Column 3 link 5 URL','sort_order'=>83],
            ['group'=>'global','key'=>'site.footer.col3.l6','value'=>'Contact Us','type'=>'text','label'=>'Column 3 link 6 label','sort_order'=>84],
            ['group'=>'global','key'=>'site.footer.col3.u6','value'=>'/contact-us','type'=>'text','label'=>'Column 3 link 6 URL','sort_order'=>85],
            ['group'=>'global','key'=>'site.footer.col4.l1','value'=>'Contact Us','type'=>'text','label'=>'Column 4 link 1 label','sort_order'=>86],
            ['group'=>'global','key'=>'site.footer.col4.u1','value'=>'/contact-us','type'=>'text','label'=>'Column 4 link 1 URL','sort_order'=>87],
            ['group'=>'global','key'=>'site.footer.col4.l2','value'=>'Return Policy','type'=>'text','label'=>'Column 4 link 2 label','sort_order'=>88],
            ['group'=>'global','key'=>'site.footer.col4.u2','value'=>'/','type'=>'text','label'=>'Column 4 link 2 URL','sort_order'=>89],
            ['group'=>'global','key'=>'site.footer.col4.l3','value'=>'Privacy Policy','type'=>'text','label'=>'Column 4 link 3 label','sort_order'=>90],
            ['group'=>'global','key'=>'site.footer.col4.u3','value'=>'/','type'=>'text','label'=>'Column 4 link 3 URL','sort_order'=>91],
            ['group'=>'global','key'=>'site.footer.col4.l4','value'=>'Terms of Use','type'=>'text','label'=>'Column 4 link 4 label','sort_order'=>92],
            ['group'=>'global','key'=>'site.footer.col4.u4','value'=>'/','type'=>'text','label'=>'Column 4 link 4 URL','sort_order'=>93],
            ['group'=>'global','key'=>'site.footer.col4.l5','value'=>'Travel Guide','type'=>'text','label'=>'Column 4 link 5 label','sort_order'=>94],
            ['group'=>'global','key'=>'site.footer.col4.u5','value'=>'/travel-guide','type'=>'text','label'=>'Column 4 link 5 URL','sort_order'=>95],
            ['group'=>'global','key'=>'site.footer.col4.l6','value'=>'Flight Search','type'=>'text','label'=>'Column 4 link 6 label','sort_order'=>96],
            ['group'=>'global','key'=>'site.footer.col4.u6','value'=>'/search-flight','type'=>'text','label'=>'Column 4 link 6 URL','sort_order'=>97],
            ['group'=>'global','key'=>'site.footer.bottom.l1','value'=>'Privacy Policy','type'=>'text','label'=>'Bottom link 1 label','sort_order'=>98],
            ['group'=>'global','key'=>'site.footer.bottom.u1','value'=>'/','type'=>'text','label'=>'Bottom link 1 URL','sort_order'=>99],
            ['group'=>'global','key'=>'site.footer.bottom.l2','value'=>'Terms of Service','type'=>'text','label'=>'Bottom link 2 label','sort_order'=>100],
            ['group'=>'global','key'=>'site.footer.bottom.u2','value'=>'/','type'=>'text','label'=>'Bottom link 2 URL','sort_order'=>101],
            ['group'=>'global','key'=>'site.footer.bottom.l3','value'=>'Contact','type'=>'text','label'=>'Bottom link 3 label','sort_order'=>102],
            ['group'=>'global','key'=>'site.footer.bottom.u3','value'=>'/contact-us','type'=>'text','label'=>'Bottom link 3 URL','sort_order'=>103],
            ['group'=>'global','key'=>'site.footer.bottom.l4','value'=>'Sitemap','type'=>'text','label'=>'Bottom link 4 label','sort_order'=>104],
            ['group'=>'global','key'=>'site.footer.bottom.u4','value'=>'/','type'=>'text','label'=>'Bottom link 4 URL','sort_order'=>105]
        ];

        foreach ($settings as $s) {
            if (!DB::table('website_cms_settings')->where('key',$s['key'])->exists()) {
                DB::table('website_cms_settings')->insert(array_merge($s, [
                    'created_at'=>$now,
                    'updated_at'=>$now,
                ]));
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('website_cms_settings')) return;
        DB::table('website_cms_settings')->whereIn('key', ['site.footer.alerts.enabled','site.footer.alerts.title','site.footer.alerts.subtitle','site.footer.alerts.email_placeholder','site.footer.alerts.airport_placeholder','site.footer.alerts.button','site.footer.alerts.privacy','site.footer.trust.enabled','site.footer.trust.1.title','site.footer.trust.1.sub','site.footer.trust.2.title','site.footer.trust.2.sub','site.footer.trust.3.title','site.footer.trust.3.sub','site.footer.trust.4.title','site.footer.trust.4.sub','site.footer.toll_label','site.footer.col1.title','site.footer.col2.title','site.footer.col3.title','site.footer.col4.title','site.footer.col1.l1','site.footer.col1.u1','site.footer.col1.l2','site.footer.col1.u2','site.footer.col1.l3','site.footer.col1.u3','site.footer.col1.l4','site.footer.col1.u4','site.footer.col1.l5','site.footer.col1.u5','site.footer.col1.l6','site.footer.col1.u6','site.footer.col2.l1','site.footer.col2.u1','site.footer.col2.l2','site.footer.col2.u2','site.footer.col2.l3','site.footer.col2.u3','site.footer.col2.l4','site.footer.col2.u4','site.footer.col2.l5','site.footer.col2.u5','site.footer.col2.l6','site.footer.col2.u6','site.footer.col3.l1','site.footer.col3.u1','site.footer.col3.l2','site.footer.col3.u2','site.footer.col3.l3','site.footer.col3.u3','site.footer.col3.l4','site.footer.col3.u4','site.footer.col3.l5','site.footer.col3.u5','site.footer.col3.l6','site.footer.col3.u6','site.footer.col4.l1','site.footer.col4.u1','site.footer.col4.l2','site.footer.col4.u2','site.footer.col4.l3','site.footer.col4.u3','site.footer.col4.l4','site.footer.col4.u4','site.footer.col4.l5','site.footer.col4.u5','site.footer.col4.l6','site.footer.col4.u6','site.footer.bottom.l1','site.footer.bottom.u1','site.footer.bottom.l2','site.footer.bottom.u2','site.footer.bottom.l3','site.footer.bottom.u3','site.footer.bottom.l4','site.footer.bottom.u4'])->delete();
    }
};
