<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('website_cms_settings')) {
            Schema::create('website_cms_settings', function (Blueprint $table) {
                $table->id();
                $table->string('group', 80)->index();
                $table->string('key', 150)->unique();
                $table->longText('value')->nullable();
                $table->string('type', 30)->default('text');
                $table->string('label', 150)->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('website_cms_cards')) {
            Schema::create('website_cms_cards', function (Blueprint $table) {
                $table->id();
                $table->string('section', 80)->index();
                $table->string('title', 150);
                $table->string('subtitle', 255)->nullable();
                $table->string('from_code', 10)->nullable();
                $table->string('to_code', 10)->nullable();
                $table->string('city', 120)->nullable();
                $table->string('image', 500)->nullable();
                $table->string('badge', 80)->nullable();
                $table->string('cabin', 40)->default('Econom');
                $table->integer('days')->default(30);
                $table->string('airline', 120)->nullable();
                $table->string('duration', 80)->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        $now = now();
        $settings = [
            ['group'=>'theme','key'=>'theme.primary','value'=>'#d91f43','type'=>'color','label'=>'Primary accent color','sort_order'=>1],
            ['group'=>'theme','key'=>'theme.navy','value'=>'#071b31','type'=>'color','label'=>'Navy color','sort_order'=>2],
            ['group'=>'theme','key'=>'theme.font','value'=>'Inter, "Segoe UI", Arial, sans-serif','type'=>'text','label'=>'Website font family','sort_order'=>3],

            ['group'=>'hero','key'=>'home.hero.enabled','value'=>'1','type'=>'toggle','label'=>'Show hero section','sort_order'=>1],
            ['group'=>'hero','key'=>'home.hero.badge','value'=>'Premium flight search','type'=>'text','label'=>'Hero badge','sort_order'=>2],
            ['group'=>'hero','key'=>'home.hero.title1','value'=>'Find better flights.','type'=>'text','label'=>'Hero title line 1','sort_order'=>3],
            ['group'=>'hero','key'=>'home.hero.title2','value'=>'Book with confidence.','type'=>'text','label'=>'Hero title line 2','sort_order'=>4],
            ['group'=>'hero','key'=>'home.hero.subtitle','value'=>'Compare live domestic and international options, explore premium cabins, and move from search to checkout without the clutter.','type'=>'textarea','label'=>'Hero subtitle','sort_order'=>5],
            ['group'=>'hero','key'=>'home.hero.background','value'=>'/assets/img/banner-8.jpg','type'=>'image','label'=>'Hero background image','sort_order'=>6],

            ['group'=>'search','key'=>'home.search.from_label','value'=>'Flying from','type'=>'text','label'=>'From label','sort_order'=>1],
            ['group'=>'search','key'=>'home.search.to_label','value'=>'Flying to','type'=>'text','label'=>'To label','sort_order'=>2],
            ['group'=>'search','key'=>'home.search.departure_label','value'=>'Departure','type'=>'text','label'=>'Departure label','sort_order'=>3],
            ['group'=>'search','key'=>'home.search.return_label','value'=>'Return','type'=>'text','label'=>'Return label','sort_order'=>4],
            ['group'=>'search','key'=>'home.search.passengers_label','value'=>'Passengers','type'=>'text','label'=>'Passengers label','sort_order'=>5],
            ['group'=>'search','key'=>'home.search.cabin_label','value'=>'Cabin','type'=>'text','label'=>'Cabin label','sort_order'=>6],
            ['group'=>'search','key'=>'home.search.button','value'=>'Search Flights','type'=>'text','label'=>'Search button text','sort_order'=>7],
            ['group'=>'search','key'=>'home.search.secure','value'=>'Secure search & checkout','type'=>'text','label'=>'Secure message','sort_order'=>8],

            ['group'=>'trust','key'=>'home.trust.1','value'=>'Secure booking','type'=>'text','label'=>'Trust item 1','sort_order'=>1],
            ['group'=>'trust','key'=>'home.trust.2','value'=>'Travel support','type'=>'text','label'=>'Trust item 2','sort_order'=>2],
            ['group'=>'trust','key'=>'home.trust.3','value'=>'Worldwide routes','type'=>'text','label'=>'Trust item 3','sort_order'=>3],
            ['group'=>'trust','key'=>'home.trust.4','value'=>'Clear fare summary','type'=>'text','label'=>'Trust item 4','sort_order'=>4],

            ['group'=>'featured','key'=>'home.featured.enabled','value'=>'1','type'=>'toggle','label'=>'Show Featured Flight Deals','sort_order'=>1],
            ['group'=>'featured','key'=>'home.featured.kicker','value'=>'Featured fares','type'=>'text','label'=>'Kicker','sort_order'=>2],
            ['group'=>'featured','key'=>'home.featured.title','value'=>'Featured Flight Deals','type'=>'text','label'=>'Section title','sort_order'=>3],
            ['group'=>'featured','key'=>'home.featured.subtitle','value'=>'Live API fares load as the cards come into view. Final availability is confirmed on results.','type'=>'textarea','label'=>'Section subtitle','sort_order'=>4],

            ['group'=>'international','key'=>'home.international.enabled','value'=>'1','type'=>'toggle','label'=>'Show International Destinations','sort_order'=>1],
            ['group'=>'international','key'=>'home.international.kicker','value'=>'International favorites','type'=>'text','label'=>'Kicker','sort_order'=>2],
            ['group'=>'international','key'=>'home.international.title','value'=>'Top International Destinations','type'=>'text','label'=>'Section title','sort_order'=>3],
            ['group'=>'international','key'=>'home.international.subtitle','value'=>'Popular long-haul city pairs with live fare previews.','type'=>'textarea','label'=>'Section subtitle','sort_order'=>4],

            ['group'=>'domestic','key'=>'home.domestic.enabled','value'=>'1','type'=>'toggle','label'=>'Show Domestic Routes','sort_order'=>1],
            ['group'=>'domestic','key'=>'home.domestic.kicker','value'=>'Best-value city pairs','type'=>'text','label'=>'Kicker','sort_order'=>2],
            ['group'=>'domestic','key'=>'home.domestic.title','value'=>'Top US Domestic Routes','type'=>'text','label'=>'Section title','sort_order'=>3],
            ['group'=>'domestic','key'=>'home.domestic.subtitle','value'=>'Tap any route to run a live one-way search with your existing API.','type'=>'textarea','label'=>'Section subtitle','sort_order'=>4],

            ['group'=>'india','key'=>'home.india.enabled','value'=>'1','type'=>'toggle','label'=>'Show USA to India Deals','sort_order'=>1],
            ['group'=>'india','key'=>'home.india.kicker','value'=>'USA → India','type'=>'text','label'=>'Kicker','sort_order'=>2],
            ['group'=>'india','key'=>'home.india.title','value'=>'USA → India Flight Deals','type'=>'text','label'=>'Section title','sort_order'=>3],
            ['group'=>'india','key'=>'home.india.subtitle','value'=>'Popular India routes with live fare previews and direct access to results.','type'=>'textarea','label'=>'Section subtitle','sort_order'=>4],

            ['group'=>'premium','key'=>'home.premium.enabled','value'=>'1','type'=>'toggle','label'=>'Show Business & First Class','sort_order'=>1],
            ['group'=>'premium','key'=>'home.premium.badge','value'=>'Premium Cabin Deals','type'=>'text','label'=>'Premium badge','sort_order'=>2],
            ['group'=>'premium','key'=>'home.premium.title','value'=>'Business & First Class','type'=>'text','label'=>'Premium title','sort_order'=>3],
            ['group'=>'premium','key'=>'home.premium.accent','value'=>'with a premium touch','type'=>'text','label'=>'Premium accent text','sort_order'=>4],
            ['group'=>'premium','key'=>'home.premium.subtitle','value'=>'Live fare previews, elevated cabin styling, and one-click route search.','type'=>'textarea','label'=>'Premium subtitle','sort_order'=>5],

            ['group'=>'global','key'=>'site.nav.home','value'=>'Home','type'=>'text','label'=>'Menu: Home','sort_order'=>1],
            ['group'=>'global','key'=>'site.nav.flights','value'=>'Flights','type'=>'text','label'=>'Menu: Flights','sort_order'=>2],
            ['group'=>'global','key'=>'site.nav.offers','value'=>'Offers','type'=>'text','label'=>'Menu: Offers','sort_order'=>3],
            ['group'=>'global','key'=>'site.nav.guide','value'=>'Travel Guide','type'=>'text','label'=>'Menu: Travel Guide','sort_order'=>4],
            ['group'=>'global','key'=>'site.nav.destinations','value'=>'Destinations','type'=>'text','label'=>'Menu: Destinations','sort_order'=>5],
            ['group'=>'global','key'=>'site.nav.contact','value'=>'Contact','type'=>'text','label'=>'Menu: Contact','sort_order'=>6],
            ['group'=>'global','key'=>'site.footer.explore_title','value'=>'Explore','type'=>'text','label'=>'Footer Explore title','sort_order'=>7],
            ['group'=>'global','key'=>'site.footer.quick_title','value'=>'Quick Links','type'=>'text','label'=>'Footer Quick Links title','sort_order'=>8],
            ['group'=>'global','key'=>'site.footer.help_title','value'=>'Need help with your trip?','type'=>'text','label'=>'Footer help title','sort_order'=>9],
        ];

        foreach ($settings as $s) {
            if (!DB::table('website_cms_settings')->where('key',$s['key'])->exists()) {
                DB::table('website_cms_settings')->insert(array_merge($s, [
                    'created_at'=>$now,'updated_at'=>$now
                ]));
            }
        }

        $cards = [
            ['section'=>'featured','title'=>'London','from_code'=>'JFK','to_code'=>'LHR','city'=>'London','image'=>'/assets/img/destination/tr-7.jpg','badge'=>'Hot Deal','cabin'=>'Econom','days'=>24,'sort_order'=>1],
            ['section'=>'featured','title'=>'Tokyo','from_code'=>'LAX','to_code'=>'NRT','city'=>'Tokyo','image'=>'/assets/img/destination/tr-3.jpg','badge'=>'Direct','cabin'=>'Econom','days'=>28,'sort_order'=>2],
            ['section'=>'featured','title'=>'Dubai','from_code'=>'ORD','to_code'=>'DXB','city'=>'Dubai','image'=>'/assets/img/destination/tr-1.jpg','badge'=>'Popular','cabin'=>'Econom','days'=>30,'sort_order'=>3],
            ['section'=>'featured','title'=>'Paris','from_code'=>'JFK','to_code'=>'CDG','city'=>'Paris','image'=>'/assets/img/destination/tr-2.jpg','badge'=>'Flash Sale','cabin'=>'Econom','days'=>32,'sort_order'=>4],

            ['section'=>'international','title'=>'London','from_code'=>'JFK','to_code'=>'LHR','city'=>'London','image'=>'/assets/img/destination/tr-7.jpg','badge'=>'Trending','cabin'=>'Econom','days'=>30,'sort_order'=>1],
            ['section'=>'international','title'=>'Paris','from_code'=>'JFK','to_code'=>'CDG','city'=>'Paris','image'=>'/assets/img/banner-3.jpg','badge'=>'Romantic','cabin'=>'Econom','days'=>30,'sort_order'=>2],
            ['section'=>'international','title'=>'Dubai','from_code'=>'ORD','to_code'=>'DXB','city'=>'Dubai','image'=>'/assets/img/destination/tr-1.jpg','badge'=>'Luxury Hub','cabin'=>'Econom','days'=>30,'sort_order'=>3],
            ['section'=>'international','title'=>'Tokyo','from_code'=>'LAX','to_code'=>'NRT','city'=>'Tokyo','image'=>'/assets/img/destination/tr-3.jpg','badge'=>'Trending','cabin'=>'Econom','days'=>30,'sort_order'=>4],

            ['section'=>'domestic','title'=>'New York to Los Angeles','subtitle'=>'New York → Los Angeles','from_code'=>'JFK','to_code'=>'LAX','cabin'=>'Econom','days'=>21,'sort_order'=>1],
            ['section'=>'domestic','title'=>'Chicago to Miami','subtitle'=>'Chicago → Miami','from_code'=>'ORD','to_code'=>'MIA','cabin'=>'Econom','days'=>21,'sort_order'=>2],
            ['section'=>'domestic','title'=>'Los Angeles to New York','subtitle'=>'Los Angeles → New York','from_code'=>'LAX','to_code'=>'JFK','cabin'=>'Econom','days'=>21,'sort_order'=>3],
            ['section'=>'domestic','title'=>'Atlanta to Las Vegas','subtitle'=>'Atlanta → Las Vegas','from_code'=>'ATL','to_code'=>'LAS','cabin'=>'Econom','days'=>21,'sort_order'=>4],
            ['section'=>'domestic','title'=>'Dallas to Seattle','subtitle'=>'Dallas → Seattle','from_code'=>'DFW','to_code'=>'SEA','cabin'=>'Econom','days'=>21,'sort_order'=>5],
            ['section'=>'domestic','title'=>'Miami to Boston','subtitle'=>'Miami → Boston','from_code'=>'MIA','to_code'=>'BOS','cabin'=>'Econom','days'=>21,'sort_order'=>6],

            ['section'=>'india','title'=>'Mumbai','from_code'=>'JFK','to_code'=>'BOM','city'=>'Mumbai','image'=>'/assets/img/city/ct-1.png','badge'=>'Nonstop','cabin'=>'Econom','days'=>35,'sort_order'=>1],
            ['section'=>'india','title'=>'New Delhi','from_code'=>'ORD','to_code'=>'DEL','city'=>'New Delhi','image'=>'/assets/img/city/ct-4.png','badge'=>'Direct','cabin'=>'Econom','days'=>35,'sort_order'=>2],
            ['section'=>'india','title'=>'Bangalore','from_code'=>'SFO','to_code'=>'BLR','city'=>'Bangalore','image'=>'/assets/img/destination/tr-4.jpg','badge'=>'Tech City','cabin'=>'Econom','days'=>35,'sort_order'=>3],
            ['section'=>'india','title'=>'Hyderabad','from_code'=>'IAH','to_code'=>'HYD','city'=>'Hyderabad','image'=>'/assets/img/destination/tr-3.jpg','badge'=>'Popular','cabin'=>'Econom','days'=>35,'sort_order'=>4],

            ['section'=>'business','title'=>'JFK → LHR','from_code'=>'JFK','to_code'=>'LHR','airline'=>'British Airways','duration'=>'7h 05m','cabin'=>'Business','days'=>30,'sort_order'=>1],
            ['section'=>'business','title'=>'LAX → NRT','from_code'=>'LAX','to_code'=>'NRT','airline'=>'ANA','duration'=>'11h 40m','cabin'=>'Business','days'=>30,'sort_order'=>2],
            ['section'=>'business','title'=>'ORD → DXB','from_code'=>'ORD','to_code'=>'DXB','airline'=>'Emirates','duration'=>'13h 20m','cabin'=>'Business','days'=>30,'sort_order'=>3],
            ['section'=>'business','title'=>'SFO → SIN','from_code'=>'SFO','to_code'=>'SIN','airline'=>'Singapore Airlines','duration'=>'17h 30m','cabin'=>'Business','days'=>30,'sort_order'=>4],

            ['section'=>'first','title'=>'JFK → DXB','from_code'=>'JFK','to_code'=>'DXB','airline'=>'Emirates','duration'=>'13h 20m','cabin'=>'firstclass','days'=>30,'sort_order'=>1],
            ['section'=>'first','title'=>'JFK → LHR','from_code'=>'JFK','to_code'=>'LHR','airline'=>'British Airways','duration'=>'7h 05m','cabin'=>'firstclass','days'=>30,'sort_order'=>2],
            ['section'=>'first','title'=>'LAX → SYD','from_code'=>'LAX','to_code'=>'SYD','airline'=>'Qantas','duration'=>'15h 25m','cabin'=>'firstclass','days'=>30,'sort_order'=>3],
            ['section'=>'first','title'=>'SFO → NRT','from_code'=>'SFO','to_code'=>'NRT','airline'=>'ANA','duration'=>'11h 40m','cabin'=>'firstclass','days'=>30,'sort_order'=>4],
        ];

        if (DB::table('website_cms_cards')->count() === 0) {
            foreach ($cards as $c) {
                DB::table('website_cms_cards')->insert(array_merge($c, [
                    'subtitle'=>$c['subtitle'] ?? null,
                    'image'=>$c['image'] ?? null,
                    'badge'=>$c['badge'] ?? null,
                    'airline'=>$c['airline'] ?? null,
                    'duration'=>$c['duration'] ?? null,
                    'is_active'=>1,
                    'created_at'=>$now,'updated_at'=>$now,
                ]));
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('website_cms_cards');
        Schema::dropIfExists('website_cms_settings');
    }
};
