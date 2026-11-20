@extends('layout')

@section('canonical')
    <link rel="canonical" href="{{ url('/blog/'.$detail->slug) }}">
@endsection

@section('header-seo')
    <title>{{ $detail->seo_title ?: (($detail->title ?? 'Travel Guide').' | FaresHunt') }}</title>
    <meta name="description" content="{{ $detail->seo_description ?: \Illuminate\Support\Str::limit(strip_tags($detail->short ?? $detail->description ?? ''),155) }}">
@endsection

@section('mid-content')
@php
    $cmsv = fn($key,$default='') => $cmsSettings[$key] ?? $default;
    $articleText = trim(strip_tags($detail->description ?? $detail->short ?? ''));
    $readMins = max(3,(int)ceil(max(1,str_word_count($articleText))/200));
    $articleDate = optional($detail->created_at)->format('M d, Y');
    if (!empty($detail->date)) {
        try {
            $articleDate = \Carbon\Carbon::createFromFormat('Y-m-d', $detail->date)->format('M d, Y');
        } catch (\Throwable $e) {
            // Keep created_at fallback if legacy/custom date is malformed.
        }
    }
    $articleCategory = optional($detail->category)->title ?: 'Travel Guide';
    $articleImage = ($detail->image ?? null) ?: (($detail->banner ?? null) ?: '/assets/img/banner-7.jpg');
@endphp

<style>
.fha-page{--navy:#071a34;--muted:#607493;--red:#d31d42;--gold:#d89b17;background:#f7f9fc;font-family:inherit}
.fha-hero{padding:54px 20px 42px;background:linear-gradient(180deg,#fff,#f7f9fc)}
.fha-hero-inner{max-width:1050px;margin:auto;text-align:center}
.fha-back{display:inline-flex;align-items:center;gap:7px;margin-bottom:22px;color:var(--red);font-size:12px;font-weight:700;text-decoration:none}
.fha-meta{display:flex;align-items:center;justify-content:center;flex-wrap:wrap;gap:10px 18px;margin-bottom:16px;color:#71829e;font-size:11px}
.fha-cat{padding:7px 11px;border-radius:999px;background:#fff5dd;color:#a96e00;font-weight:700}
.fha-hero h1{max-width:950px;margin:0 auto 15px;color:var(--navy);font-size:clamp(40px,4.2vw,64px);line-height:1.05;font-weight:600;letter-spacing:-.048em}
.fha-lead{max-width:760px;margin:auto;color:var(--muted);font-size:15px;line-height:1.65}
.fha-feature{width:min(1240px,calc(100% - 48px));height:clamp(300px,42vw,560px);margin:0 auto 0;border-radius:28px;background:#e8edf4 center/cover no-repeat;box-shadow:0 18px 46px rgba(20,42,72,.10)}
.fha-content-wrap{width:min(1180px,calc(100% - 48px));margin:auto;padding:48px 0 72px}
.fha-layout{display:grid;grid-template-columns:minmax(0,760px) 280px;justify-content:center;gap:54px;align-items:start}
.fha-article{padding:34px 38px;border:1px solid #e0e6ef;border-radius:24px;background:#fff;box-shadow:0 12px 28px rgba(24,45,78,.04)}
.fha-article,.fha-article p,.fha-article li{color:#344965;font-size:15px;line-height:1.8}
.fha-article p{margin:0 0 20px}
.fha-article h2{margin:34px 0 14px;color:var(--navy);font-size:28px;line-height:1.2;font-weight:650;letter-spacing:-.03em}
.fha-article h3{margin:28px 0 12px;color:var(--navy);font-size:22px;line-height:1.25;font-weight:650}
.fha-article img{max-width:100%;height:auto;border-radius:18px}
.fha-article a{color:var(--red)}
.fha-side{position:sticky;top:110px;padding:24px;border:1px solid #e0e6ef;border-radius:22px;background:#fff}
.fha-side span{display:block;margin-bottom:8px;color:var(--gold);font-size:10px;font-weight:700;letter-spacing:.14em}
.fha-side h3{margin:0 0 9px;color:var(--navy);font-size:19px}
.fha-side p{margin:0 0 18px;color:var(--muted);font-size:12.5px;line-height:1.55}
.fha-side a{display:flex;align-items:center;justify-content:center;gap:8px;min-height:44px;border-radius:13px;background:#d31d42;color:#fff;font-size:12px;font-weight:700;text-decoration:none}
@media(max-width:900px){.fha-layout{grid-template-columns:1fr}.fha-side{position:static}}
@media(max-width:680px){
    .fha-hero{padding:40px 14px 30px}
    .fha-feature{width:calc(100% - 24px);height:260px;border-radius:20px}
    .fha-content-wrap{width:calc(100% - 24px);padding:30px 0 50px}
    .fha-article{padding:24px 20px;border-radius:18px}
}
</style>

<div class="fha-page">
    <header class="fha-hero">
        <div class="fha-hero-inner">
            <a class="fha-back" href="{{ route('all_blogs') }}"><i class="fa-solid fa-arrow-left"></i> {{ $cmsv('travel_guides.article.back_label','All travel guides') }}</a>
            <div class="fha-meta">
                <span class="fha-cat">{{ $articleCategory }}</span>
                @if(!empty($detail->author))<span><i class="fa-regular fa-user"></i> {{ $detail->author }}</span>@endif
                @if($articleDate)<span><i class="fa-regular fa-calendar"></i> {{ $articleDate }}</span>@endif
                <span><i class="fa-regular fa-clock"></i> {{ $readMins }} {{ $cmsv('travel_guides.article.min_read_suffix','min read') }}</span>
            </div>
            <h1>{{ $detail->title }}</h1>
            @if(!empty($detail->short))
                <p class="fha-lead">{{ strip_tags($detail->short) }}</p>
            @endif
        </div>
    </header>

    <div class="fha-feature" style="background-image:url('{{ $articleImage }}')"></div>

    <main class="fha-content-wrap">
        <div class="fha-layout">
            <article class="fha-article">
                {!! $detail->description ?? $detail->short ?? '<p>Travel guide content coming soon.</p>' !!}
            </article>

            <aside class="fha-side">
                <span>{{ $cmsv('travel_guides.article.cta_kicker','PLAN YOUR NEXT TRIP') }}</span>
                <h3>{{ $cmsv('travel_guides.article.cta_title','Ready to search flights?') }}</h3>
                <p>{{ $cmsv('travel_guides.article.cta_description','Use FaresHunt flight search to compare available itineraries for your next journey.') }}</p>
                <a href="{{ route('search_flight') }}"><i class="fa-solid fa-plane"></i> {{ $cmsv('travel_guides.article.cta_button','Search Flights') }}</a>
            </aside>
        </div>
    </main>
</div>
@endsection
