<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Models\Setting;
use App\Models\Airline;
use App\Models\Airport;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Usp;
use App\Models\TopDeal;
use App\Models\Content;
use App\Models\Destination;
use App\Models\Faq;
use App\Models\MailTemplate;
use App\Models\Markup;
use App\Models\Master;
use App\Models\Offer;

class DefaultController extends Controller
{
    public function home()
    {
        $setting = getAllSetting();

        $h_usp = Master::where('type','usp')->get();
        $h_topdeal = TopDeal::get();
        $h_destination = Destination::where('is_visible','1')->inRandomOrder()->limit(12)->get();
        $h_blog = Blog::with('category')
            ->where('is_visible','1')
            ->whereNotIn('slug',[
                'privacy-policy','return-policy','terms-of-use','terms-of-service',
                'shipping-policy','cancellation-policy','cookie-policy'
            ])
            ->orderByDesc('id')
            ->limit(3)
            ->get();
        $h_offer = Offer::where('is_visible','1')->inRandomOrder()->limit(3)->get();

        // Phase 1 public-site UI data. These are read-only lookups only;
        // flight search/API/fare-rule behaviour remains unchanged.
        $homeAirports = Airport::with(['city','country'])
            ->whereIn('code',['JFK','LAX','ORD','ATL','DFW','MIA','SFO','IAH','BOS','SEA'])
            ->get()
            ->groupBy(fn($a) => strtoupper((string)$a->code))
            ->map(fn($group) => $group->sortBy('id')->first())
            ->sortBy(fn($a) => array_search(strtoupper((string)$a->code),['JFK','LAX','ORD','ATL','DFW','MIA','SFO','IAH','BOS','SEA']))
            ->values();

        $homeAirlines = Airline::whereIn('code',['UA','AA','DL','AC','BA','EK','QR','LH','AF','AI','SQ','NH'])
            ->orderByRaw("FIELD(code,'UA','AA','DL','AC','BA','EK','QR','LH','AF','AI','SQ','NH')")
            ->limit(12)
            ->get();

        $cmsSettings = collect();
        $cmsCards = collect();

        if (Schema::hasTable('website_cms_settings')) {
            $cmsSettings = DB::table('website_cms_settings')->pluck('value','key');
        }

        if (Schema::hasTable('website_cms_cards')) {
            $cmsCards = DB::table('website_cms_cards')
                ->where('is_active', 1)
                ->orderBy('section')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->groupBy('section');
        }

        $info = compact(
            'setting','h_usp','h_topdeal','h_destination','h_blog','h_offer',
            'homeAirports','homeAirlines','cmsSettings','cmsCards'
        );

        return view('index')->with($info);
    }

    public function all_blogs()
    {
        $blog = Blog::where('is_visible','1')->get();

        $cmsSettings = collect();
        if (Schema::hasTable('website_cms_settings')) {
            $cmsSettings = DB::table('website_cms_settings')->pluck('value','key');
        }

        $info = compact('blog','cmsSettings');

        return view('blogs')->with($info);
    }

    public function single_blog($slug)
    {
        $detail = Blog::where('slug',$slug)->first();
        if($detail)
        {
            $cmsSettings = collect();
            if (Schema::hasTable('website_cms_settings')) {
                $cmsSettings = DB::table('website_cms_settings')->pluck('value','key');
            }

            $info = compact('detail','cmsSettings');

            return view('single_blog')->with($info);
        }
        else
        {
            return redirect('no_found');
        }
    }

    public function single_content($slug)
    {
        $detail = Content::where('slug',$slug)->first();
        if($detail)
        {
            $info = compact('detail');

            return view('single_content')->with($info);
        }
        else
        {
            return redirect('no_found');
        }
    }

    /**
     * New public Deals page.
     *
     * Uses the same CMS deal-card source as the homepage so the public Deals
     * page and homepage remain consistent. This is read-only and does not
     * change flight APIs, FareLeaders, markup or fare-rule logic.
     */
    public function all_deals()
    {
        $setting = getAllSetting();

        $cmsSettings = collect();
        $cmsCards = collect();

        if (Schema::hasTable('website_cms_settings')) {
            $cmsSettings = DB::table('website_cms_settings')->pluck('value','key');
        }

        if (Schema::hasTable('website_cms_cards')) {
            $cmsCards = DB::table('website_cms_cards')
                ->where('is_active', 1)
                ->orderBy('section')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->groupBy('section');
        }

        return view('deals', compact('setting','cmsSettings','cmsCards'));
    }

    /**
     * Backward compatibility for old /offers links.
     * Keeps old bookmarks/marketing links working while the canonical page is /deals.
     */
    public function all_offers()
    {
        return redirect()->route('all_deals', [], 301);
    }

    public function single_offer($slug)
    {
        $detail = Offer::where('slug',$slug)->first();
        if($detail)
        {
            $info = compact('detail');

            return view('single_offer')->with($info);
        }
        else
        {
            return redirect('no_found');
        }
    }

    public function all_destinations()
    {
        $destination = Destination::where('is_visible','1')->get();
        $info = compact('destination');

        return view('destinations')->with($info);
    }

    public function single_destination($slug)
    {
        $destination = Destination::where('slug',$slug)->first();
        if($destination)
        {
            $info = compact('destination');

            return view('single_destination')->with($info);
        }
        else
        {
            return redirect('no_found');
        }
    }

    public function thankyou()
    {
        return view('thankyou');
    }

    public function no_found()
    {
        return view('no_found');
    }

    public function help_center()
    {
        $faq = Faq::where('is_visible','1')->orderBy('sequence')->get();

        $info = compact('faq');

        return view('help_center')->with($info);
    }

    public function contact_us()
    {
        $detail = \App\Models\Content::where('slug','contact-us')->first();
        return view('contact_us', compact('detail'));
    }
}
