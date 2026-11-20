@extends('layout')

@php
    $cmsv = fn($key,$default='') => $cmsSettings[$key] ?? $default;
@endphp

@section('canonical')
<link rel="canonical" href="{{ url('/travel-guide') }}">
@endsection

@section('header-seo')
    <title>{{ $cmsv('travel_guides.listing.seo_title','Travel Guides & Insights | FaresHunt') }}</title>
    <meta name="description" content="{{ $cmsv('travel_guides.listing.seo_description','Explore FaresHunt travel guides, destination ideas and practical flight-booking insights.') }}">
@endsection

@section('mid-content')

@php
    /*
     * Travel Guides page should only show real travel/blog content.
     * Legal/system content stays available on its own pages but is excluded here.
     */
    $guideBlogs = collect($blog ?? [])
        ->reject(function($b){
            return in_array(strtolower((string)($b->slug ?? '')), [
                'privacy-policy',
                'return-policy',
                'terms-of-use',
                'terms-of-service',
                'shipping-policy',
                'cancellation-policy',
                'cookie-policy',
                'disclaimer'
            ], true);
        })
        ->values();
@endphp

<style id="fh-travel-guide-listing-final-v2">
.fhg-page{
    --navy:#071a34;
    --muted:#607493;
    --red:#d31d42;
    --gold:#d89b17;

    background:#f7f9fc;
    font-family:inherit;
}


/* =========================================================
   HERO
   ========================================================= */
.fhg-hero{
    position:relative;
    overflow:hidden;

    padding:58px 20px 54px;

    background:
        radial-gradient(circle at 82% 18%,rgba(216,155,23,.10),transparent 30%),
        linear-gradient(135deg,#071a34 0%,#0b2949 62%,#123a61 100%);

    color:#fff;
    text-align:center;
}

.fhg-hero::before{
    content:"";
    position:absolute;
    inset:0;

    background-image:
        linear-gradient(rgba(255,255,255,.025) 1px,transparent 1px),
        linear-gradient(90deg,rgba(255,255,255,.025) 1px,transparent 1px);

    background-size:42px 42px;

    pointer-events:none;
}

.fhg-hero-inner{
    position:relative;
    z-index:1;

    max-width:900px;
    margin:auto;
}

.fhg-kicker{
    display:block;

    margin:0 0 13px;

    color:#f1bd3d;

    font-size:10px;
    line-height:1;
    font-weight:700;

    letter-spacing:.22em;
    text-transform:uppercase;
}

.fhg-hero h1{
    margin:0 0 12px;

    color:#fff!important;

    font-family:inherit;
    font-size:clamp(40px,3.8vw,58px);
    line-height:1.03;
    font-weight:600;

    letter-spacing:-.045em;

    text-shadow:0 4px 20px rgba(0,0,0,.12);
}

.fhg-hero p{
    max-width:720px;
    margin:0 auto;

    color:rgba(255,255,255,.84)!important;

    font-family:inherit;
    font-size:14px;
    line-height:1.6;
    font-weight:400;
}


/* =========================================================
   CONTENT WRAPPER
   ========================================================= */
.fhg-wrap{
    width:min(1500px,calc(100% - 48px));

    margin:auto;
    padding:46px 0 58px;
}

.fhg-head{
    display:flex;
    align-items:flex-end;
    justify-content:space-between;

    gap:24px;

    margin:0 0 24px;
}

.fhg-head-kicker{
    display:block;

    margin:0 0 8px;

    color:var(--gold);

    font-size:10px;
    line-height:1;
    font-weight:700;

    letter-spacing:.16em;
    text-transform:uppercase;
}

.fhg-head h2{
    margin:0 0 6px;

    color:var(--navy);

    font-family:inherit;
    font-size:30px;
    line-height:1.1;
    font-weight:650;

    letter-spacing:-.035em;
}

.fhg-head p{
    margin:0;

    color:var(--muted);

    font-family:inherit;
    font-size:13.5px;
    line-height:1.5;
}


/* =========================================================
   GRID
   ========================================================= */
.fhg-grid{
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));

    gap:20px;
}

.fhg-card{
    display:flex;
    flex-direction:column;

    min-width:0;
    overflow:hidden;

    border:1px solid #dfe6ef;
    border-radius:22px;

    background:#fff;

    color:var(--navy);
    text-decoration:none;

    box-shadow:0 10px 24px rgba(24,45,78,.055);

    transition:
        transform .18s ease,
        border-color .18s ease,
        box-shadow .18s ease;
}

.fhg-card:hover{
    transform:translateY(-3px);

    border-color:#ccd7e5;

    box-shadow:0 17px 36px rgba(24,45,78,.09);
}


/* =========================================================
   IMAGE
   ========================================================= */
.fhg-image{
    position:relative;

    min-height:195px;

    background-color:#e8edf4;
    background-position:center;
    background-size:cover;
    background-repeat:no-repeat;
}

.fhg-image::after{
    content:"";

    position:absolute;
    inset:0;

    background:linear-gradient(
        to bottom,
        rgba(5,21,42,.02) 38%,
        rgba(5,21,42,.55) 100%
    );

    pointer-events:none;
}

.fhg-badge,
.fhg-read{
    position:absolute;
    z-index:2;

    display:inline-flex;
    align-items:center;

    min-height:28px;

    border-radius:999px;

    font-family:inherit;
    font-size:10px;
    line-height:1;
    font-weight:650;
}

.fhg-badge{
    top:14px;
    left:14px;

    padding:0 12px;

    background:rgba(255,255,255,.94);
    color:#16385d;

    box-shadow:0 5px 14px rgba(0,0,0,.07);
}

.fhg-read{
    right:14px;
    bottom:13px;

    gap:6px;
    padding:0 11px;

    background:rgba(7,26,52,.84);
    color:#fff;

    backdrop-filter:blur(6px);
}


/* =========================================================
   CARD BODY
   ========================================================= */
.fhg-body{
    display:flex;
    flex:1;
    flex-direction:column;

    min-width:0;

    padding:20px 20px 18px;
}

.fhg-date{
    margin:0 0 8px;

    color:var(--gold);

    font-size:9.5px;
    line-height:1;
    font-weight:700;

    letter-spacing:.10em;
    text-transform:uppercase;
}

.fhg-body h3{
    margin:0 0 9px;

    color:var(--navy);

    font-family:inherit;
    font-size:20px;
    line-height:1.22;
    font-weight:650;

    letter-spacing:-.025em;
}

.fhg-body p{
    display:-webkit-box;
    -webkit-box-orient:vertical;
    -webkit-line-clamp:2;

    overflow:hidden;

    margin:0 0 17px;

    color:var(--muted);

    font-family:inherit;
    font-size:12.5px;
    line-height:1.52;
}

.fhg-footer{
    display:flex;
    align-items:center;
    justify-content:space-between;

    margin-top:auto;
    padding-top:14px;

    border-top:1px solid #e8edf4;

    color:var(--red);

    font-size:12px;
    line-height:1;
    font-weight:700;
}


/* =========================================================
   ONE-ARTICLE STATE
   Prevent giant empty right-side space.
   ========================================================= */
.fhg-grid.one-guide{
    grid-template-columns:minmax(0,1fr);
}

.fhg-grid.one-guide .fhg-card{
    display:grid;
    grid-template-columns:minmax(360px,.9fr) minmax(0,1.1fr);

    min-height:285px;
}

.fhg-grid.one-guide .fhg-image{
    min-height:285px;
}

.fhg-grid.one-guide .fhg-body{
    justify-content:center;

    padding:30px 34px;
}

.fhg-grid.one-guide .fhg-body h3{
    font-size:clamp(26px,2.2vw,34px);
}


/* =========================================================
   EMPTY
   ========================================================= */
.fhg-empty{
    padding:52px 24px;

    border:1px dashed #cfd9e6;
    border-radius:22px;

    background:#fff;

    color:var(--muted);
    text-align:center;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */
@media(max-width:1000px){
    .fhg-grid{
        grid-template-columns:repeat(2,minmax(0,1fr));
    }

    .fhg-grid.one-guide .fhg-card{
        grid-template-columns:1fr;
    }
}

@media(max-width:680px){
    .fhg-hero{
        padding:48px 16px 46px;
    }

    .fhg-wrap{
        width:calc(100% - 24px);
        padding:36px 0 46px;
    }

    .fhg-head{
        align-items:flex-start;
        flex-direction:column;
    }

    .fhg-grid{
        grid-template-columns:1fr;
    }

    .fhg-image,
    .fhg-grid.one-guide .fhg-image{
        min-height:210px;
    }

    .fhg-grid.one-guide .fhg-body{
        padding:22px 20px;
    }

    .fhg-grid.one-guide .fhg-body h3{
        font-size:24px;
    }
}
</style>


<div class="fhg-page">

    <section class="fhg-hero">
        <div class="fhg-hero-inner">

            <span class="fhg-kicker">
                {{ $cmsv('travel_guides.listing.hero_kicker','FARESHUNT TRAVEL DESK') }}
            </span>

            <h1>
                {{ $cmsv('travel_guides.listing.hero_title','Travel Guides & Insights') }}
            </h1>

            <p>
                {{ $cmsv('travel_guides.listing.hero_subtitle','Expert destination inspiration, practical travel advice and smarter ways to plan your next journey.') }}
            </p>

        </div>
    </section>


    <main class="fhg-wrap">

        <div class="fhg-head">
            <div>

                <span class="fhg-head-kicker">
                    {{ $cmsv('travel_guides.listing.section_kicker','TRAVEL INSPIRATION') }}
                </span>

                <h2>
                    {{ $cmsv('travel_guides.listing.section_title','Latest Travel Guides') }}
                </h2>

                <p>
                    {{ $cmsv('travel_guides.listing.section_subtitle','Fresh ideas and useful information from the FaresHunt travel desk.') }}
                </p>

            </div>
        </div>


        @if($guideBlogs->count() > 0)

            <div class="fhg-grid {{ $guideBlogs->count() === 1 ? 'one-guide' : '' }}">

                @foreach($guideBlogs as $b)

                    @php
                        $text = trim(strip_tags($b->short ?? $b->description ?? ''));

                        $mins = max(
                            3,
                            (int)ceil(
                                max(1,str_word_count($text)) / 200
                            )
                        );

                        $date = optional($b->created_at)->format('M d, Y');

                        $cat = optional($b->category)->title ?: 'Travel Guide';

                        $img = ($b->image ?? null)
                            ?: (($b->banner ?? null)
                            ?: '/assets/img/banner-7.jpg');
                    @endphp


                    <a
                        class="fhg-card"
                        href="{{ url('/blog/'.$b->slug) }}"
                    >

                        <div
                            class="fhg-image"
                            style="background-image:url('{{ $img }}')"
                        >

                            <span class="fhg-badge">
                                {{ $cat }}
                            </span>

                            <span class="fhg-read">
                                <i class="fa-regular fa-clock"></i>
                                {{ $mins }} {{ $cmsv('travel_guides.listing.min_read_suffix','min read') }}
                            </span>

                        </div>


                        <div class="fhg-body">

                            <div class="fhg-date">
                                {{ $date ?: 'FaresHunt Travel Desk' }}
                            </div>


                            <h3>
                                {{ $b->title }}
                            </h3>


                            @if($text)

                                <p>
                                    {{ \Illuminate\Support\Str::limit($text,170) }}
                                </p>

                            @endif


                            <div class="fhg-footer">

                                <span>
                                    {{ $cmsv('travel_guides.listing.read_label','Read guide') }}
                                </span>

                                <i class="fa-solid fa-arrow-right"></i>

                            </div>

                        </div>

                    </a>

                @endforeach

            </div>

        @else

            <div class="fhg-empty">
                {{ $cmsv('travel_guides.listing.empty_text','New travel guides are being prepared. Please check back soon.') }}
            </div>

        @endif

    </main>

</div>

@endsection
