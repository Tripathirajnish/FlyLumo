@extends('layout')

@section('canonical')
<link rel="canonical" href="{{ url('/'.$detail->slug) }}">
@endsection

@section('header-seo')
<title>{{ $detail->seo_title ?: $detail->title }}</title>
<meta name="keywords" content="{{ $detail->seo_keyword }}">
<meta name="description" content="{{ $detail->seo_description }}">
@endsection

@section('mid-content')
@php
    $setting = getAllSetting();
    $rawContent = trim((string) ($detail->description ?? ''));
    $pageType = trim((string)($detail->type ?: 'Company'));
    $isAbout = str_contains(strtolower((string)$detail->title), 'about');
    $isTerms = in_array(strtolower(trim((string)($detail->slug ?? ''))), ['terms-of-use','terms-and-conditions'], true)
        || str_contains(strtolower((string)$detail->title), 'terms');
    $isPrivacy = strtolower(trim((string)($detail->slug ?? ''))) === 'privacy-policy'
        || str_contains(strtolower((string)$detail->title), 'privacy policy');

    $isCookies = in_array(strtolower(trim((string)($detail->slug ?? ''))), ['cookie-policy','cookies-policy'], true)
        || str_contains(strtolower((string)$detail->title), 'cookie');
    $isRefundDispute = str_contains(strtolower((string)($detail->slug ?? '')), 'refund')
        || str_contains(strtolower((string)$detail->title), 'refund')
        || str_contains(strtolower((string)$detail->title), 'dispute');

    $isDisclaimer = strtolower(trim((string)($detail->slug ?? ''))) === 'disclaimer'
        || str_contains(strtolower((string)$detail->title), 'disclaimer');


    /*
     * PREMIUM CONTENT FORMATTER V6
     *
     * Important fix:
     * CMS may save Markdown-looking text INSIDE <p>, <div> or <br> tags.
     * Older formatter treated that as "already HTML" and skipped conversion,
     * which is why #, ** and --- were still visible.
     *
     * V6 first normalizes CMS HTML back into readable lines, then rebuilds
     * clean semantic HTML headings / paragraphs / lists.
     */

    $normalized = $rawContent;

    // Preserve visual line breaks before stripping wrapper HTML.
    $normalized = preg_replace('/<br\s*\/?>/i', "\n", $normalized);
    $normalized = preg_replace('/<\/(p|div|h[1-6]|li|section|article|blockquote)>/i', "\n", $normalized);

    // Remove remaining tags and decode entities.
    $normalized = strip_tags($normalized);
    $normalized = html_entity_decode($normalized, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    // Normalize whitespace / line endings.
    $normalized = str_replace(["\r\n", "\r"], "\n", $normalized);
    $normalized = preg_replace("/[ \t]+\n/", "\n", $normalized);
    $normalized = preg_replace("/\n{3,}/", "\n\n", $normalized);
    $normalized = trim($normalized);

    // Inline markdown formatting.
    $normalized = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $normalized);
    $normalized = preg_replace('/__(.+?)__/s', '<strong>$1</strong>', $normalized);
    $normalized = preg_replace('/(?<!\*)\*([^*\n]+)\*(?!\*)/', '<em>$1</em>', $normalized);

    $lines = explode("\n", $normalized);
    $html = '';
    $paragraph = [];
    $inUl = false;
    $inOl = false;

    $flushParagraph = function () use (&$paragraph, &$html) {
        if (!$paragraph) return;

        $value = trim(implode(' ', array_map('trim', $paragraph)));

        if ($value !== '') {
            $html .= '<p>'.$value.'</p>';
        }

        $paragraph = [];
    };

    $closeLists = function () use (&$html, &$inUl, &$inOl) {
        if ($inUl) {
            $html .= '</ul>';
            $inUl = false;
        }

        if ($inOl) {
            $html .= '</ol>';
            $inOl = false;
        }
    };

    foreach ($lines as $line) {
        $trimmed = trim($line);

        if ($trimmed === '') {
            $flushParagraph();
            $closeLists();
            continue;
        }

        // Markdown separator
        if (preg_match('/^---+$/', $trimmed)) {
            $flushParagraph();
            $closeLists();
            $html .= '<hr>';
            continue;
        }

        // Markdown headings (#, ##, ###...)
        if (preg_match('/^(#{1,6})\s*(.+)$/', $trimmed, $m)) {
            $flushParagraph();
            $closeLists();

            // Content pages visually work best with H2/H3 hierarchy.
            $hashCount = strlen($m[1]);
            $level = $hashCount <= 2 ? 2 : ($hashCount === 3 ? 3 : 4);

            $heading = trim($m[2]);

            // Prevent duplicated page title (# DISCLAIMER) from repeating
            // immediately inside the document body.
            if (strcasecmp(strip_tags($heading), trim((string)$detail->title)) !== 0) {
                $html .= '<h'.$level.'>'.$heading.'</h'.$level.'>';
            }
            continue;
        }

        // Bullet list
        if (preg_match('/^(?:\*|-|â€¢)\s+(.+)$/u', $trimmed, $m)) {
            $flushParagraph();

            if ($inOl) {
                $html .= '</ol>';
                $inOl = false;
            }

            if (!$inUl) {
                $html .= '<ul>';
                $inUl = true;
            }

            $html .= '<li>'.$m[1].'</li>';
            continue;
        }

        // Numbered list such as 1. / 2)
        if (preg_match('/^\d+[\.\)]\s+(.+)$/', $trimmed, $m)) {
            $flushParagraph();

            if ($inUl) {
                $html .= '</ul>';
                $inUl = false;
            }

            if (!$inOl) {
                $html .= '<ol>';
                $inOl = true;
            }

            $html .= '<li>'.$m[1].'</li>';
            continue;
        }

        // Treat "1. Heading" embedded after "# " removal or similar legal labels
        // as a heading when it clearly reads like a section title.
        if (preg_match('/^\d+\.\s+[A-Z].{2,90}$/', $trimmed)) {
            $flushParagraph();
            $closeLists();
            $html .= '<h2>'.$trimmed.'</h2>';
            continue;
        }

        $paragraph[] = $trimmed;
    }

    $flushParagraph();
    $closeLists();

    $content = $html;

    $phone = $setting['header_contact'] ?? '';
    $email = $setting['content_emailid'] ?? '';
    $address = $setting['footer_address'] ?? '';
@endphp

<style>
:root{
    --fh-navy:#071a31;
    --fh-navy-2:#0d294a;
    --fh-red:#d91f43;
    --fh-gold:#d7b33f;
    --fh-ink:#0a2340;
    --fh-copy:#506984;
    --fh-soft:#f4f7fb;
    --fh-line:#dfe6ef;
}

.fh-content-v5{
    background:#fff;
    color:var(--fh-ink);
    font-family:Inter,"Segoe UI",Arial,sans-serif;
}

/* ================= HERO ================= */
.fh-v5-hero{
    position:relative;
    overflow:hidden;
    padding:86px 0 92px;
    background:
        radial-gradient(circle at 84% 18%,rgba(217,31,67,.13),transparent 23%),
        radial-gradient(circle at 15% 0%,rgba(61,120,184,.12),transparent 28%),
        linear-gradient(120deg,#07182d 0%,#0b213d 58%,#102e50 100%);
}
.fh-v5-hero:before{
    content:"";
    position:absolute;
    inset:0;
    background:
        linear-gradient(rgba(255,255,255,.025) 1px,transparent 1px),
        linear-gradient(90deg,rgba(255,255,255,.025) 1px,transparent 1px);
    background-size:48px 48px;
    mask-image:linear-gradient(to bottom,rgba(0,0,0,.85),transparent 100%);
}
.fh-v5-hero .container{position:relative;z-index:2}
.fh-v5-hero-inner{max-width:980px;margin:0 auto;text-align:center}
.fh-v5-kicker{
    display:inline-flex;
    align-items:center;
    gap:10px;
    margin-bottom:18px;
    color:#ff3157;
    font-size:11px;
    font-weight:600;
    letter-spacing:.16em;
    text-transform:uppercase;
}
.fh-v5-kicker:before,.fh-v5-kicker:after{
    content:"";
    width:28px;
    height:1px;
    background:currentColor;
}
.fh-v5-hero h1{
    margin:0;
    color:#fff!important;
    font-size:clamp(48px,5.2vw,72px)!important;
    line-height:1.02!important;
    font-weight:300!important;
    letter-spacing:-.055em!important;
}
.fh-v5-hero p{
    max-width:760px;
    margin:19px auto 0;
    color:#c3cfdd;
    font-size:16px;
    line-height:1.7;
}
.fh-v5-trust{
    display:flex;
    flex-wrap:wrap;
    justify-content:center;
    gap:10px;
    margin-top:28px;
}
.fh-v5-trust span{
    display:inline-flex;
    align-items:center;
    gap:8px;
    min-height:34px;
    padding:7px 12px;
    border:1px solid rgba(255,255,255,.13);
    border-radius:999px;
    background:rgba(255,255,255,.05);
    color:#d9e2ec;
    font-size:11px;
}

/* ================= MAIN ================= */
.fh-v5-main{
    padding:74px 0 82px;
    background:#fff;
}
.fh-v5-article{
    max-width:1180px;
    margin:0 auto;
}
.fh-v5-prose{
    color:var(--fh-copy);
    font-size:16px;
    line-height:1.9;
}
.fh-v5-prose > :first-child{margin-top:0!important}
.fh-v5-prose > :last-child{margin-bottom:0!important}

.fh-v5-prose h1,
.fh-v5-prose h2,
.fh-v5-prose h3,
.fh-v5-prose h4{
    color:var(--fh-ink)!important;
    font-family:Inter,"Segoe UI",Arial,sans-serif!important;
}
.fh-v5-prose h1{
    margin:0 0 24px!important;
    font-size:36px!important;
    line-height:1.15!important;
    font-weight:400!important;
    letter-spacing:-.035em!important;
}
.fh-v5-prose h2{
    margin:58px 0 18px!important;
    font-size:32px!important;
    line-height:1.16!important;
    font-weight:400!important;
    letter-spacing:-.035em!important;
}
.fh-v5-prose h3{
    margin:38px 0 14px!important;
    font-size:24px!important;
    line-height:1.25!important;
    font-weight:400!important;
}
.fh-v5-prose h4{
    margin:28px 0 12px!important;
    font-size:19px!important;
    font-weight:500!important;
}
.fh-v5-prose p{
    max-width:1040px;
    margin:0 0 24px!important;
    color:var(--fh-copy)!important;
    font-size:16px!important;
    line-height:1.9!important;
}
.fh-v5-prose strong{
    color:#263f5d!important;
    font-weight:600!important;
}
.fh-v5-prose hr{
    height:1px!important;
    margin:46px 0!important;
    border:0!important;
    background:linear-gradient(90deg,#dfe6ef,transparent)!important;
}
.fh-v5-prose ul,.fh-v5-prose ol{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:10px 20px;
    max-width:980px;
    margin:12px 0 30px!important;
    padding:0!important;
    list-style:none!important;
}
.fh-v5-prose li{
    position:relative;
    min-height:48px;
    margin:0!important;
    padding:12px 16px 12px 42px!important;
    border:1px solid #e7edf4;
    border-radius:13px;
    background:#f8fafc;
    color:#506984!important;
    font-size:14px!important;
    line-height:1.6!important;
}
.fh-v5-prose ul li:before{
    content:"";
    position:absolute;
    left:17px;
    top:20px;
    width:7px;
    height:7px;
    border-radius:50%;
    background:var(--fh-red);
}
.fh-v5-prose ol{counter-reset:item}
.fh-v5-prose ol li{counter-increment:item}
.fh-v5-prose ol li:before{
    content:counter(item);
    position:absolute;
    left:12px;
    top:11px;
    display:grid;
    place-items:center;
    width:23px;
    height:23px;
    border-radius:7px;
    background:#fff0f3;
    color:var(--fh-red);
    font-size:11px;
    font-weight:600;
}
.fh-v5-prose blockquote{
    margin:32px 0!important;
    padding:22px 24px!important;
    border-left:4px solid var(--fh-red)!important;
    border-radius:0 15px 15px 0!important;
    background:#fff5f7!important;
    color:#4f6680!important;
}

/* ================= ABOUT PAGE ENHANCEMENTS ================= */
.fh-v5-feature-strip{
    padding:12px 0 72px;
    background:#fff;
}
.fh-v5-feature-grid{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:18px;
}
.fh-v5-feature{
    min-height:180px;
    padding:28px;
    border:1px solid var(--fh-line);
    border-radius:20px;
    background:linear-gradient(180deg,#fff,#f8fafc);
    box-shadow:0 12px 34px rgba(22,47,79,.05);
}
.fh-v5-feature i{
    display:grid;
    place-items:center;
    width:46px;
    height:46px;
    margin-bottom:22px;
    border-radius:14px;
    background:#eef3f8;
    color:#315a86;
    font-size:20px;
}
.fh-v5-feature h3{
    margin:0 0 8px;
    color:var(--fh-ink);
    font-size:16px;
    font-weight:500;
}
.fh-v5-feature p{
    margin:0;
    color:#6b7f98;
    font-size:13px;
    line-height:1.65;
}

/* ================= CORPORATE INFO ================= */
.fh-v5-corp{
    padding:72px 0 76px;
    background:#f2f5f9;
}
.fh-v5-section-title{
    margin:0 0 34px;
    text-align:center;
    color:var(--fh-ink);
    font-size:34px;
    font-weight:400;
    letter-spacing:-.035em;
}
.fh-v5-corp-grid{
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:20px;
    max-width:940px;
    margin:0 auto;
}
.fh-v5-corp-card{
    min-height:190px;
    padding:28px 24px;
    border:1px solid #e3e9f1;
    border-radius:18px;
    background:#fff;
    text-align:center;
    box-shadow:0 12px 34px rgba(22,47,79,.05);
}
.fh-v5-corp-card i{
    display:grid;
    place-items:center;
    width:52px;
    height:52px;
    margin:0 auto 18px;
    border-radius:14px;
    background:#edf3f8;
    color:#3a6089;
    font-size:21px;
}
.fh-v5-corp-card b{
    display:block;
    margin-bottom:7px;
    color:var(--fh-ink);
    font-size:16px;
    font-weight:500;
}
.fh-v5-corp-card span,.fh-v5-corp-card a{
    display:block;
    color:#617792!important;
    font-size:14px;
    line-height:1.6;
    text-decoration:none!important;
}
.fh-v5-company-lines{
    margin-top:30px;
    text-align:center;
    color:#506984;
    font-size:14px;
    line-height:1.7;
}
.fh-v5-company-lines b{color:var(--fh-ink)}

/* ================= CLOSING CTA ================= */
.fh-v5-closing{
    position:relative;
    overflow:hidden;
    padding:62px 0 68px;
    background:linear-gradient(120deg,#0b213d,#102e50);
    text-align:center;
}
.fh-v5-closing h2{
    margin:0 0 14px;
    color:#fff;
    font-size:32px;
    font-weight:400;
    letter-spacing:-.03em;
}
.fh-v5-closing p{
    max-width:760px;
    margin:0 auto 17px;
    color:#c6d1df;
    font-size:16px;
    line-height:1.65;
}
.fh-v5-closing strong{
    color:#f0c83e;
    font-weight:500;
}

/* ================= MOBILE ================= */
@media(max-width:991px){
    .fh-v5-feature-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
    .fh-v5-corp-grid{grid-template-columns:1fr}
}
@media(max-width:767px){
    .fh-v5-hero{padding:58px 0 64px}
    .fh-v5-hero h1{font-size:40px!important}
    .fh-v5-main{padding:42px 0 52px}
    .fh-v5-prose h2{margin-top:42px!important;font-size:27px!important}
    .fh-v5-prose p{font-size:14px!important;line-height:1.8!important}
    .fh-v5-prose ul,.fh-v5-prose ol{grid-template-columns:1fr}
    .fh-v5-feature-grid{grid-template-columns:1fr}
    .fh-v5-corp{padding:52px 0}
}

/* V6 content hierarchy hardening */
.fh-v5-prose{
    max-width:1120px!important;
}
.fh-v5-prose h2{
    position:relative!important;
    padding-left:18px!important;
}
.fh-v5-prose h2:before{
    content:""!important;
    position:absolute!important;
    left:0!important;
    top:5px!important;
    bottom:5px!important;
    width:4px!important;
    border-radius:999px!important;
    background:linear-gradient(180deg,var(--fh-red),#f45d79)!important;
}
.fh-v5-prose p{
    max-width:1080px!important;
}
.fh-v5-prose > p:first-child{
    font-size:17px!important;
    color:#405a77!important;
}

</style>



<style id="fh-policy-premium-v1">
.fhp-page{
    --fhp-navy:#071a31;--fhp-red:#d91f43;--fhp-gold:#d7a82d;--fhp-ink:#0a2340;
    --fhp-copy:#5e728c;--fhp-soft:#f6f8fb;--fhp-line:#e0e7ef;
    background:#fff;color:var(--fhp-ink);font-family:inherit
}
.fhp-page *{box-sizing:border-box}
.fhp-page .container{width:min(1380px,calc(100% - 48px));max-width:none;margin:0 auto}
.fhp-hero{position:relative;overflow:hidden;padding:62px 20px 66px;text-align:center;color:#fff;
    background:radial-gradient(circle at 84% 14%,rgba(217,31,67,.10),transparent 24%),
    radial-gradient(circle at 12% 6%,rgba(71,127,186,.12),transparent 26%),
    linear-gradient(120deg,#07182d 0%,#0b213d 58%,#103255 100%)}
.fhp-hero::before{content:"";position:absolute;inset:0;background:
    linear-gradient(rgba(255,255,255,.022) 1px,transparent 1px),
    linear-gradient(90deg,rgba(255,255,255,.022) 1px,transparent 1px);background-size:46px 46px}
.fhp-hero-inner{position:relative;z-index:1;max-width:900px;margin:auto}
.fhp-kicker{display:inline-flex;align-items:center;gap:10px;margin-bottom:13px;color:#f1bd3d;
    font-size:10px;font-weight:700;letter-spacing:.18em;text-transform:uppercase}
.fhp-kicker::before,.fhp-kicker::after{content:"";width:28px;height:1px;background:currentColor}
.fhp-hero h1{margin:0 0 12px!important;color:#fff!important;font-size:clamp(42px,4vw,60px)!important;
    line-height:1.04!important;font-weight:600!important;letter-spacing:-.045em!important}
.fhp-hero p{max-width:800px;margin:0 auto;color:#cad5e2;font-size:13.5px;line-height:1.68}
.fhp-meta{display:flex;flex-wrap:wrap;justify-content:center;gap:9px;margin-top:21px}
.fhp-meta span{display:inline-flex;align-items:center;gap:7px;min-height:34px;padding:0 12px;
    border:1px solid rgba(255,255,255,.13);border-radius:999px;background:rgba(255,255,255,.05);
    color:#e6edf5;font-size:10.5px}
.fhp-wrap{padding:54px 0 62px}
.fhp-layout{display:grid;grid-template-columns:270px minmax(0,1fr);gap:34px;align-items:start}
.fhp-toc{position:sticky;top:100px;padding:20px;border:1px solid var(--fhp-line);border-radius:18px;
    background:linear-gradient(180deg,#fff,#f7f9fc)}
.fhp-toc b{display:block;margin-bottom:12px;color:var(--fhp-ink);font-size:12px}
.fhp-toc a{display:block;padding:6px 0;color:#677b93!important;font-size:10.5px;line-height:1.35;text-decoration:none!important}
.fhp-toc a:hover{color:var(--fhp-red)!important}
.fhp-doc{min-width:0}
.fhp-intro{margin-bottom:24px;padding:24px 26px;border:1px solid #e8ddbe;border-radius:18px;background:#fffaf0}
.fhp-intro p{margin:0 0 10px;color:#5f6f80;font-size:12.5px;line-height:1.7}
.fhp-intro p:last-child{margin-bottom:0}
.fhp-section{scroll-margin-top:110px;padding:25px 0 29px;border-bottom:1px solid var(--fhp-line)}
.fhp-section h2{position:relative;margin:0 0 15px;padding-left:17px;color:var(--fhp-ink)!important;
    font-size:23px!important;line-height:1.25!important;font-weight:650!important;letter-spacing:-.025em!important}
.fhp-section h2::before{content:"";position:absolute;left:0;top:2px;bottom:2px;width:4px;border-radius:999px;
    background:linear-gradient(180deg,var(--fhp-red),#ef5774)}
.fhp-section h3{margin:18px 0 8px;color:#263e5a!important;font-size:14px!important;font-weight:700!important}
.fhp-section p{margin:0 0 12px;color:var(--fhp-copy)!important;font-size:12.5px!important;line-height:1.72!important}
.fhp-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px 12px;margin:10px 0 14px;padding:0;list-style:none}
.fhp-list li{position:relative;min-height:40px;padding:10px 12px 10px 32px;border:1px solid #e7edf4;
    border-radius:12px;background:#f9fbfd;color:#60748c;font-size:11.5px;line-height:1.5}
.fhp-list li::before{content:"";position:absolute;left:14px;top:17px;width:6px;height:6px;border-radius:50%;background:var(--fhp-red)}
.fhp-callout{margin-top:14px;padding:15px 17px;border:1px solid #dfe8f1;border-left:4px solid #365f8a;
    border-radius:0 13px 13px 0;background:#f7faff;color:#5b7089;font-size:11.5px;line-height:1.6}
.fhp-contact{margin-top:18px;padding:22px;border:1px solid var(--fhp-line);border-radius:18px;background:var(--fhp-soft)}
.fhp-contact-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-top:14px}
.fhp-contact-card{padding:17px;border:1px solid var(--fhp-line);border-radius:14px;background:#fff}
.fhp-contact-card b{display:block;margin-bottom:5px;color:var(--fhp-ink);font-size:11.5px}
.fhp-contact-card span,.fhp-contact-card a{display:block;color:#667b94!important;font-size:10.5px;line-height:1.5;text-decoration:none!important}
.fhp-ack{margin-top:24px;padding:24px;border-radius:18px;background:linear-gradient(120deg,#0b213d,#103255);color:#fff}
.fhp-ack h3{margin:0 0 9px;color:#fff!important;font-size:18px!important}
.fhp-ack p{margin:0;color:#d3dde9!important;font-size:12px!important;line-height:1.65!important}

/* REFUND & DISPUTE — FINAL COMPACT POLISH */
.fhp-refund-page .fhp-wrap{
    padding:42px 0 50px;
}
.fhp-refund-page .fhp-layout{
    grid-template-columns:260px minmax(0,1fr);
    gap:30px;
}
.fhp-refund-page .fhp-toc{
    top:92px;
    padding:18px 18px 16px;
    border-radius:16px;
    box-shadow:0 10px 26px rgba(18,42,72,.05);
}
.fhp-refund-page .fhp-toc b{
    margin-bottom:10px;
    font-size:12.5px;
}
.fhp-refund-page .fhp-toc a{
    position:relative;
    padding:6px 0 6px 14px;
    font-size:11px;
    line-height:1.35;
}
.fhp-refund-page .fhp-toc a::before{
    content:"";
    position:absolute;
    left:0;
    top:11px;
    width:5px;
    height:5px;
    border-radius:50%;
    background:#d91f43;
}
.fhp-refund-page .fhp-intro{
    margin-bottom:18px;
    padding:20px 22px;
}
.fhp-refund-page .fhp-intro p{
    font-size:13px;
    line-height:1.65;
}
.fhp-refund-page .fhp-section{
    padding:20px 0 22px;
}
.fhp-refund-page .fhp-section h2{
    margin-bottom:12px;
    font-size:21px!important;
    line-height:1.22!important;
}
.fhp-refund-page .fhp-section h3{
    margin:14px 0 6px;
    font-size:13.5px!important;
}
.fhp-refund-page .fhp-section p{
    margin-bottom:9px;
    font-size:13px!important;
    line-height:1.62!important;
}
.fhp-refund-page .fhp-list{
    gap:7px 10px;
    margin:8px 0 10px;
}
.fhp-refund-page .fhp-list li{
    min-height:36px;
    padding:8px 10px 8px 29px;
    font-size:11.5px;
}
.fhp-refund-page .fhp-list li::before{
    left:12px;
    top:15px;
}
.fhp-refund-page .fhp-callout{
    margin-top:10px;
    padding:12px 14px;
    font-size:11.5px;
}
.fhp-refund-page .fhp-part-divider{
    display:flex;
    align-items:center;
    gap:10px;
    margin:10px 0 2px;
    padding:12px 14px;
    border:1px solid #dfe7f0;
    border-radius:14px;
    background:linear-gradient(90deg,#f7faff,#fff);
}
.fhp-refund-page .fhp-part-divider span{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:58px;
    height:24px;
    border-radius:999px;
    background:#0b213d;
    color:#fff;
    font-size:9px;
    font-weight:700;
    letter-spacing:.08em;
}
.fhp-refund-page .fhp-part-divider strong{
    color:#17324f;
    font-size:12.5px;
    font-weight:700;
}
.fhp-refund-page .fhp-contact{
    margin-top:14px;
    padding:18px;
}
.fhp-refund-page .fhp-contact-grid{
    gap:10px;
    margin-top:0;
}
.fhp-refund-page .fhp-contact-card{
    min-height:105px;
    padding:15px;
}
.fhp-refund-page .fhp-contact-card b{
    font-size:11.5px;
}
.fhp-refund-page .fhp-contact-card span,
.fhp-refund-page .fhp-contact-card a{
    font-size:10.5px;
}
.fhp-refund-page .fhp-ack{
    margin-top:18px;
    padding:20px 22px;
}
.fhp-refund-page .fhp-ack h3{
    margin-bottom:7px;
    font-size:17px!important;
}
.fhp-refund-page .fhp-ack p{
    font-size:11.75px!important;
    line-height:1.6!important;
}

@media(max-width:980px){.fhp-layout{grid-template-columns:1fr}.fhp-toc{display:none}.fhp-contact-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:700px){.fhp-page .container{width:calc(100% - 24px)}.fhp-hero{padding:50px 14px 54px}.fhp-wrap{padding:40px 0 46px}
    .fhp-list,.fhp-contact-grid{grid-template-columns:1fr}}
</style>

<style id="fh-terms-premium-v1">
.fht-page{
    --fht-navy:#071a31;
    --fht-navy2:#0d294a;
    --fht-red:#d91f43;
    --fht-gold:#d7a82d;
    --fht-ink:#0a2340;
    --fht-copy:#5e728c;
    --fht-soft:#f6f8fb;
    --fht-line:#e0e7ef;
    background:#fff;
    color:var(--fht-ink);
    font-family:inherit;
}
.fht-page *{box-sizing:border-box}
.fht-page .container{
    width:min(1380px,calc(100% - 48px));
    max-width:none;
    margin:0 auto;
}
.fht-hero{
    position:relative;
    overflow:hidden;
    padding:62px 20px 66px;
    background:
        radial-gradient(circle at 84% 14%,rgba(217,31,67,.10),transparent 24%),
        radial-gradient(circle at 12% 6%,rgba(71,127,186,.12),transparent 26%),
        linear-gradient(120deg,#07182d 0%,#0b213d 58%,#103255 100%);
    text-align:center;
    color:#fff;
}
.fht-hero::before{
    content:"";
    position:absolute;
    inset:0;
    background:
        linear-gradient(rgba(255,255,255,.022) 1px,transparent 1px),
        linear-gradient(90deg,rgba(255,255,255,.022) 1px,transparent 1px);
    background-size:46px 46px;
}
.fht-hero-inner{
    position:relative;
    z-index:1;
    max-width:900px;
    margin:auto;
}
.fht-kicker{
    display:inline-flex;
    align-items:center;
    gap:10px;
    margin-bottom:13px;
    color:#f1bd3d;
    font-size:10px;
    font-weight:700;
    letter-spacing:.18em;
    text-transform:uppercase;
}
.fht-kicker::before,.fht-kicker::after{
    content:"";
    width:28px;
    height:1px;
    background:currentColor;
}
.fht-hero h1{
    margin:0 0 12px!important;
    color:#fff!important;
    font-size:clamp(42px,4vw,60px)!important;
    line-height:1.04!important;
    font-weight:600!important;
    letter-spacing:-.045em!important;
}
.fht-hero p{
    max-width:780px;
    margin:0 auto;
    color:#cad5e2;
    font-size:13.5px;
    line-height:1.68;
}
.fht-meta{
    display:flex;
    flex-wrap:wrap;
    justify-content:center;
    gap:9px;
    margin-top:21px;
}
.fht-meta span{
    display:inline-flex;
    align-items:center;
    gap:7px;
    min-height:34px;
    padding:0 12px;
    border:1px solid rgba(255,255,255,.13);
    border-radius:999px;
    background:rgba(255,255,255,.05);
    color:#e6edf5;
    font-size:10.5px;
}
.fht-wrap{
    padding:54px 0 62px;
    background:#fff;
}
.fht-layout{
    display:grid;
    grid-template-columns:270px minmax(0,1fr);
    gap:34px;
    align-items:start;
}
.fht-toc{
    position:sticky;
    top:100px;
    padding:20px;
    border:1px solid var(--fht-line);
    border-radius:18px;
    background:linear-gradient(180deg,#ffffff,#f7f9fc);
}
.fht-toc b{
    display:block;
    margin-bottom:12px;
    color:var(--fht-ink);
    font-size:12px;
}
.fht-toc a{
    display:block;
    padding:6px 0;
    color:#677b93!important;
    font-size:10.5px;
    line-height:1.35;
    text-decoration:none!important;
}
.fht-toc a:hover{color:var(--fht-red)!important}
.fht-doc{
    min-width:0;
}
.fht-intro{
    margin-bottom:24px;
    padding:24px 26px;
    border:1px solid #e8ddbe;
    border-radius:18px;
    background:#fffaf0;
}
.fht-intro p{
    margin:0 0 10px;
    color:#5f6f80;
    font-size:12.5px;
    line-height:1.7;
}
.fht-intro p:last-child{margin-bottom:0}
.fht-section{
    scroll-margin-top:110px;
    padding:25px 0 29px;
    border-bottom:1px solid var(--fht-line);
}
.fht-section:first-of-type{padding-top:8px}
.fht-section:last-of-type{border-bottom:0}
.fht-section h2{
    position:relative;
    margin:0 0 15px;
    padding-left:17px;
    color:var(--fht-ink)!important;
    font-size:23px!important;
    line-height:1.25!important;
    font-weight:650!important;
    letter-spacing:-.025em!important;
}
.fht-section h2::before{
    content:"";
    position:absolute;
    left:0;
    top:2px;
    bottom:2px;
    width:4px;
    border-radius:999px;
    background:linear-gradient(180deg,var(--fht-red),#ef5774);
}
.fht-section h3{
    margin:18px 0 8px;
    color:#263e5a!important;
    font-size:14px!important;
    font-weight:700!important;
}
.fht-section p{
    margin:0 0 12px;
    color:var(--fht-copy)!important;
    font-size:12.5px!important;
    line-height:1.72!important;
}
.fht-section ul{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:8px 12px;
    margin:10px 0 14px!important;
    padding:0!important;
    list-style:none!important;
}
.fht-section li{
    position:relative;
    min-height:40px;
    padding:10px 12px 10px 32px!important;
    border:1px solid #e7edf4;
    border-radius:12px;
    background:#f9fbfd;
    color:#60748c!important;
    font-size:11.5px!important;
    line-height:1.5!important;
}
.fht-section li::before{
    content:"";
    position:absolute;
    left:14px;
    top:17px;
    width:6px;
    height:6px;
    border-radius:50%;
    background:var(--fht-red);
}
.fht-callout{
    margin-top:14px;
    padding:15px 17px;
    border:1px solid #dfe8f1;
    border-left:4px solid #365f8a;
    border-radius:0 13px 13px 0;
    background:#f7faff;
    color:#5b7089;
    font-size:11.5px;
    line-height:1.6;
}
.fht-contact{
    margin-top:26px;
    padding:24px;
    border:1px solid var(--fht-line);
    border-radius:18px;
    background:var(--fht-soft);
}
.fht-contact-grid{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:12px;
    margin-top:16px;
}
.fht-contact-card{
    padding:17px;
    border:1px solid var(--fht-line);
    border-radius:14px;
    background:#fff;
}
.fht-contact-card b{
    display:block;
    margin-bottom:5px;
    color:var(--fht-ink);
    font-size:11.5px;
}
.fht-contact-card span,.fht-contact-card a{
    display:block;
    color:#667b94!important;
    font-size:10.5px;
    line-height:1.5;
    text-decoration:none!important;
}
.fht-ack{
    margin-top:24px;
    padding:24px;
    border-radius:18px;
    background:linear-gradient(120deg,#0b213d,#103255);
    color:#fff;
}
.fht-ack h3{
    margin:0 0 9px;
    color:#fff!important;
    font-size:18px!important;
}
.fht-ack p{
    margin:0;
    color:#d3dde9!important;
    font-size:12px!important;
    line-height:1.65!important;
}
@media(max-width:980px){
    .fht-layout{grid-template-columns:1fr}
    .fht-toc{position:static}
    .fht-toc{display:none}
    .fht-contact-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
}
@media(max-width:700px){
    .fht-page .container{width:calc(100% - 24px)}
    .fht-hero{padding:50px 14px 54px}
    .fht-wrap{padding:40px 0 46px}
    .fht-section ul,.fht-contact-grid{grid-template-columns:1fr}
}
</style>

@if($isRefundDispute)
<div class="fhp-page fhp-refund-page">
<section class="fhp-hero"><div class="fhp-hero-inner">
<span class="fhp-kicker">REFUNDS, BILLING &amp; DISPUTES</span>
<h1>Refund &amp; Dispute Policy</h1>
<p>Understand refund eligibility, supplier rules, processing timelines, billing inquiries, transaction verification and dispute procedures for travel booked through Fares Hunt.</p>
<div class="fhp-meta"><span><i class="fa-regular fa-calendar"></i> Effective June 10, 2026</span><span><i class="fa-solid fa-rotate-left"></i> Refund Procedures</span><span><i class="fa-solid fa-shield-halved"></i> Billing &amp; Disputes</span></div>
</div></section>
<section class="fhp-wrap"><div class="container"><div class="fhp-layout">
<aside class="fhp-toc"><b>On this page</b>
@foreach([[1,'General Refund Parameters'],[2,'Hotels & Other Travel'],[3,'U.S. DOT 24-Hour Rule'],[4,'Refunds After 24 Hours'],[5,'Processing Timelines'],[6,'Partial Refunds'],[7,'No-Show Policy'],[8,'Support Before Dispute'],[9,'Transaction Verification'],[10,'Generally Ineligible Situations'],[11,'Fraud Prevention'],[12,'Unauthorized Transactions'],[13,'Contact Information']] as $toc)
<a href="#refund-{{ $toc[0] }}">{{ $toc[0] }}. {{ $toc[1] }}</a>@endforeach
</aside><main class="fhp-doc">
<div class="fhp-intro"><p>At Fares Hunt, operated by Aeronza Travel Group LLC, we are committed to transparency, fairness, and customer satisfaction. This Refund and Dispute Policy explains the procedures, limitations, and responsibilities associated with cancellations, refunds, billing inquiries, and payment disputes related to travel services booked through our platform.</p><p>By making a reservation, submitting payment, or using our services, you acknowledge and agree to the terms outlined below.</p></div>
<div class="fhp-part-divider"><span>PART I</span><strong>Refund Policy</strong></div>
<section class="fhp-section" id="refund-1"><h2>1. General Refund Parameters</h2>
<p>All cancellations, refunds, credits, exchanges, and modifications are governed by the fare rules, restrictions, policies, and contracts established by the applicable airline, hotel, cruise operator, car rental provider, tour operator, or other travel supplier.</p>
<p>Many discounted, promotional, consolidator, negotiated, bulk, and economy travel products are subject to significant restrictions and may be non-refundable, non-changeable, or partially refundable.</p>
<p>Fares Hunt acts solely as an intermediary and does not have the authority to override a supplier's refund determination.</p>
<p>All refund requests remain subject to the applicable supplier's approval and policies.</p>
</section>
<section class="fhp-section" id="refund-2"><h2>2. Hotels, Vacation Packages, Cruises, and Car Rentals</h2>
<p>Reservations involving hotels, vacation packages, cruises, car rentals, tours, and other travel products are subject to the cancellation, modification, refund, and operational policies established by the applicable supplier.</p>
<p>Refund eligibility, penalties, supplier fees, cancellation deadlines, and processing timelines may vary depending on the supplier and travel product purchased.</p>
<p>Fares Hunt is not responsible for supplier-imposed penalties, operational decisions, overbooking situations, or refund denials issued by third-party travel providers.</p>
<p>Supplier-specific policies shall govern all non-air travel products.</p>
</section>
<section class="fhp-section" id="refund-3"><h2>3. U.S. DOT 24-Hour Risk-Free Cancellation Rule</h2>
<p>Where required by applicable U.S. Department of Transportation regulations, eligible flight reservations may be canceled within twenty-four (24) hours of purchase for a full refund when the reservation is made at least seven (7) days before the scheduled departure date.</p>
<p>During this qualifying period, Fares Hunt will not impose agency cancellation fees or administrative penalties.</p>
<p>After the applicable cancellation window expires, all supplier fare rules, restrictions, penalties, and agency service fees become fully applicable.</p>
<p>Eligibility for the 24-hour cancellation rule is determined by applicable DOT regulations and supplier policies.</p>
</section>
<section class="fhp-section" id="refund-4"><h2>4. Cancellations and Refunds After 24 Hours</h2>
<p>Once the applicable cancellation period has expired, cancellations and refund requests become subject to the policies of the airline, hotel, cruise line, car rental company, or other travel supplier.</p>
<p>If a supplier authorizes a refund, partial refund, travel credit, or voucher, Fares Hunt may charge an administrative processing fee of USD $50 per passenger where permitted by law.</p>
<p>The remaining balance, if any, will be processed according to the supplier's applicable refund procedures.</p>
<p>Administrative fees are separate from any airline or supplier penalties that may apply.</p>
</section>
<section class="fhp-section" id="refund-5"><h2>5. Financial Processing Timelines</h2>
<p>Once a refund request is approved by the applicable airline, hotel, cruise operator, car rental provider, or other travel supplier, Fares Hunt will initiate refund processing as soon as reasonably practicable.</p>
<p>Refund processing generally occurs within seven (7) to fourteen (14) business days; however, actual posting times may vary depending on:</p>
<p>Airlines and travel suppliers</p>
<p>Payment processors</p>
<p>Credit card issuers</p>
<p>Banks and financial institutions</p>
<p>International payment networks</p>
<p>Certain refunds may require one or more billing cycles before appearing on a customer's account statement.</p>
<p>Actual refund posting times remain outside the control of Fares Hunt.</p>
</section>
<section class="fhp-section" id="refund-6"><h2>6. Partial Refunds and Supplier-Initiated Changes</h2>
<p>Partial refunds may be considered when authorized by the applicable supplier in circumstances including, but not limited to:</p>
<p>Flight cancellations initiated by the airline</p>
<p>Significant involuntary schedule changes</p>
<p>Involuntary service downgrades</p>
<p>Supplier service failures</p>
<p>Supplier-approved travel disruptions</p>
<p>All refund determinations remain subject to supplier approval and applicable fare rules.</p>
<p>Fares Hunt cannot guarantee refund approval where supplier authorization is required.</p>
</section>
<section class="fhp-section" id="refund-7"><h2>7. Passenger No-Show Policy</h2>
<p>Passengers who fail to check in, arrive late, miss boarding deadlines, fail to board their assigned transportation, or otherwise become a "No-Show" may forfeit some or all of the value associated with the reservation.</p>
<p>No-show policies are governed by the applicable supplier and may result in cancellation of remaining travel segments without refund.</p>
<p>No-show penalties and forfeitures are determined solely by the supplier's rules.</p>
</section>
<div class="fhp-part-divider"><span>PART II</span><strong>Dispute &amp; Chargeback Policy</strong></div>
<section class="fhp-section" id="refund-8"><h2>8. Customer Support Before Filing a Dispute</h2>
<p>Fares Hunt is committed to resolving customer concerns promptly and professionally.</p>
<p>Customers are encouraged to contact our support team before initiating a payment dispute or chargeback with a financial institution so that billing concerns may be reviewed and resolved efficiently.</p>
<p>Many billing questions can be resolved significantly faster through direct communication than through the formal chargeback process.</p>
<p>We encourage customers to provide Fares Hunt an opportunity to investigate and address concerns before pursuing external dispute procedures.</p>
</section>
<section class="fhp-section" id="refund-9"><h2>9. Transaction Verification and Dispute Response</h2>
<p>To protect customers and prevent fraud, Fares Hunt and Aeronza Travel Group LLC may retain records relating to reservations and payments, including:</p>
<p>Booking records</p>
<p>Invoices and itineraries</p>
<p>Payment authorizations</p>
<p>Email communications</p>
<p>Text message communications</p>
<p>Recorded telephone conversations</p>
<p>Transaction logs</p>
<p>IP address records</p>
<p>Identity verification documents voluntarily provided by customers</p>
<p>Supplier confirmations and ticket records</p>
<p>Fares Hunt may provide booking records, payment authorizations, customer communications, ticket confirmations, and other relevant transaction records to financial institutions, payment processors, and dispute-resolution providers for the purpose of investigating and resolving payment disputes.</p>
<p>These records may be retained for a reasonable period as required for fraud prevention, accounting, legal compliance, and dispute resolution purposes.</p>
<p>Transaction records may be used to verify customer authorization and booking fulfillment.</p>
</section>
<section class="fhp-section" id="refund-10"><h2>10. Circumstances Generally Not Eligible for Agency Refunds</h2>
<p>By completing a reservation, customers acknowledge that the following situations are generally governed by supplier policies and may not qualify for refunds from Fares Hunt:</p>
<p>Non-refundable fares purchased knowingly by the traveler</p>
<p>Airline penalties imposed after expiration of applicable cancellation periods</p>
<p>Missed flights caused by late arrival at the airport</p>
<p>Missed flights resulting from security screening delays</p>
<p>Missed connections caused by weather or supplier operational disruptions</p>
<p>Denied boarding due to insufficient travel documentation</p>
<p>Denied boarding resulting from incorrect passenger information provided by the traveler</p>
<p>Visa denials, passport issues, or immigration-related restrictions</p>
<p>Supplier-imposed restrictions outside the control of Fares Hunt</p>
<p>Nothing in this section limits any rights available under applicable law.</p>
<p>Refund eligibility in such circumstances remains subject to supplier policies and applicable law.</p>
</section>
<section class="fhp-section" id="refund-11"><h2>11. Fraud Prevention and Identity Verification</h2>
<p>To protect customers from identity theft, payment fraud, and unauthorized transactions, Fares Hunt reserves the right to conduct verification procedures before issuing tickets or confirming reservations.</p>
<p>Verification may include requests for:</p>
<p>Government-issued identification</p>
<p>Credit card ownership confirmation</p>
<p>Billing address verification</p>
<p>Additional fraud-prevention documentation</p>
<p>Verification documents are collected solely for fraud prevention, payment verification, reservation security, legal compliance, and dispute-resolution purposes.</p>
<p>Failure to provide requested verification information within the required timeframe may result in cancellation of the reservation and issuance of a refund, subject to supplier policies.</p>
<p>Verification procedures help protect both customers and the company from fraudulent transactions.</p>
</section>
<section class="fhp-section" id="refund-12"><h2>12. Unauthorized or Fraudulent Transactions</h2>
<p>If a customer believes a transaction was completed without authorization, they should immediately notify both their financial institution and Fares Hunt.</p>
<p>Fares Hunt reserves the right to investigate suspected fraudulent activity and may cooperate with payment processors, financial institutions, travel suppliers, fraud prevention providers, and law enforcement authorities when necessary to protect customers and prevent fraud.</p>
<p>Where permitted by applicable law, Fares Hunt reserves the right to pursue available legal remedies in cases involving fraudulent transactions, intentional misrepresentation, identity theft, or demonstrably abusive chargeback activity.</p>
<p>Fraudulent activity may result in reservation cancellation, reporting to relevant authorities, and other remedies available under applicable law.</p>
</section>
<div class="fhp-part-divider"><span>PART III</span><strong>Contact Information</strong></div>
<section class="fhp-section fhp-contact-section" id="refund-13">
<h2>13. Contact Information</h2>
<p>For refund requests, cancellation inquiries, billing questions, or dispute-related matters, please contact Aeronza Travel Group LLC (DBA Fares Hunt).</p>

<div class="fhp-contact">
<div class="fhp-contact-grid">
<div class="fhp-contact-card"><b>Customer Support</b><a href="tel:+18887170346">+1-888-717-0346</a><span>24 Hours / 7 Days a Week</span></div>
<div class="fhp-contact-card"><b>WhatsApp</b><a href="https://wa.me/13072638435" target="_blank" rel="noopener">+1 (307) 263-8435</a><span>Travel &amp; billing assistance</span></div>
<div class="fhp-contact-card"><b>Email</b><a href="mailto:info@fareshunt.com">info@fareshunt.com</a><span>Refund, cancellation &amp; dispute inquiries</span></div>
<div class="fhp-contact-card"><b>Mailing Address</b><span>1603 Capitol Ave Suite 413G-2599<br>Cheyenne, Wyoming 82001<br>United States</span></div>
</div>
</div>
</section>

<div class="fhp-ack" id="refund-14">
<h3>Acknowledgement</h3>
<p>By booking travel services through Fares Hunt, making a payment, submitting authorization forms, responding to booking confirmations, or confirming a reservation through any communication channel, you acknowledge that you have read, understood, and agreed to this Refund and Dispute Policy in its entirety. Your continued use of Fares Hunt services constitutes acceptance of this Refund and Dispute Policy.</p>
</div>
</main></div></div></section></div>

@elseif($isDisclaimer)
<div class="fhp-page">
<section class="fhp-hero"><div class="fhp-hero-inner">
<span class="fhp-kicker">IMPORTANT TRAVEL INFORMATION</span>
<h1>Disclaimer</h1>
<p>Important information about Fares Hunt's role as an independent travel intermediary, supplier responsibility, pricing, documentation, travel risks and limitations of liability.</p>
<div class="fhp-meta"><span><i class="fa-regular fa-calendar"></i> Effective July 24, 2026</span><span><i class="fa-solid fa-circle-info"></i> Travel Information</span><span><i class="fa-solid fa-building"></i> Aeronza Travel Group LLC</span></div>
</div></section>
<section class="fhp-wrap"><div class="container"><div class="fhp-layout">
<aside class="fhp-toc"><b>On this page</b>
@foreach([[1,'Independent Travel Agency'],[2,'Supplier Responsibility'],[3,'Pricing & Availability'],[4,'Website Information'],[5,'Travel Documentation'],[6,'International Travel'],[7,'Travel Insurance'],[8,'Limitation of Liability'],[9,'Force Majeure'],[10,'Third-Party Websites'],[11,'No Professional Advice'],[12,'Changes to Disclaimer'],[13,'Contact Information']] as $toc)
<a href="#disclaimer-{{ $toc[0] }}">{{ $toc[0] }}. {{ $toc[1] }}</a>@endforeach
</aside><main class="fhp-doc">
<div class="fhp-intro"><p>Fares Hunt is operated by Aeronza Travel Group LLC and provides travel booking assistance and reservation services for flights, hotels, vacation packages, cruises, car rentals, and other travel-related products offered by independent third-party travel suppliers.</p><p>By accessing this website, requesting travel information, obtaining travel quotations, or making reservations through Fares Hunt, you acknowledge and agree to the following disclaimers.</p></div>
<section class="fhp-section" id="disclaimer-1"><h2>1. Independent Travel Agency Disclaimer</h2>
<p>Fares Hunt acts solely as an independent travel agency and booking intermediary.</p>
<p>We do not own, operate, manage, control, or provide transportation services, hotel accommodations, cruise operations, rental vehicles, or other travel services directly.</p>
<p>All travel services are provided by independent third-party suppliers, including airlines, hotels, cruise lines, tour operators, car rental companies, and other travel providers.</p>
<p>Fares Hunt serves only as an intermediary between customers and travel suppliers.</p>
</section>
<section class="fhp-section" id="disclaimer-2"><h2>2. Supplier Responsibility Disclaimer</h2>
<p>Travel suppliers are solely responsible for providing their respective services.</p>
<p>Fares Hunt is not responsible for:</p>
<ul class="fhp-list">
<li>Flight delays</li>
<li>Flight cancellations</li>
<li>Schedule changes</li>
<li>Aircraft substitutions</li>
<li>Hotel overbookings</li>
<li>Cruise itinerary changes</li>
<li>Lost, delayed, or damaged baggage</li>
<li>Supplier service interruptions</li>
<li>Supplier insolvency or bankruptcy</li>
<li>Acts or omissions of travel suppliers</li>
</ul>
<p>All travel services remain subject to the terms, conditions, policies, and operational decisions of the applicable supplier.</p>
<p>Travel supplier policies and operating decisions remain outside our control.</p>
</section>
<section class="fhp-section" id="disclaimer-3"><h2>3. Pricing and Availability Disclaimer</h2>
<p>Travel prices, availability, schedules, and inventory are subject to change without notice.</p>
<p>Displayed fares, rates, promotions, and travel offers are not guaranteed until payment has been successfully processed and the reservation has been confirmed by the applicable supplier.</p>
<p>Errors in pricing, availability, taxes, fees, or supplier data may occasionally occur.</p>
<p>Fares Hunt reserves the right to correct errors and, where necessary, cancel affected reservations and issue applicable refunds.</p>
<p>Fares Hunt further reserves the right to decline, cancel, suspend, or refuse any booking where payment verification, fraud screening, supplier confirmation, pricing errors, regulatory requirements, or compliance obligations cannot be satisfactorily completed.</p>
<p>Travel products remain subject to availability, verification, and supplier confirmation.</p>
</section>
<section class="fhp-section" id="disclaimer-4"><h2>4. Website Information Disclaimer</h2>
<p>While we strive to provide accurate and current information, Fares Hunt does not guarantee the completeness, accuracy, reliability, or timeliness of information appearing on this website.</p>
<p>Website content may contain inaccuracies, typographical errors, or outdated information.</p>
<p>Information may be updated, modified, or removed without prior notice.</p>
<p>Website content is provided for general informational purposes only.</p>
</section>
<section class="fhp-section" id="disclaimer-5"><h2>5. Travel Documentation Disclaimer</h2>
<p>Travelers are solely responsible for obtaining and maintaining all required travel documentation.</p>
<p>This may include:</p>
<ul class="fhp-list">
<li>Valid passports</li>
<li>Tourist visas</li>
<li>Transit visas</li>
<li>Entry permits</li>
<li>Health documentation</li>
<li>Vaccination requirements</li>
<li>Government travel authorizations</li>
</ul>
<p>Fares Hunt is not responsible for denied boarding, denied entry, deportation, missed travel, or travel disruptions resulting from incomplete, inaccurate, expired, or missing documentation.</p>
<p>Travel documentation requirements remain the responsibility of the traveler.</p>
</section>
<section class="fhp-section" id="disclaimer-6"><h2>6. International Travel Disclaimer</h2>
<p>International travel may involve additional risks, including:</p>
<ul class="fhp-list">
<li>Government travel restrictions</li>
<li>Political instability</li>
<li>Civil unrest</li>
<li>Health concerns</li>
<li>Natural disasters</li>
<li>Currency fluctuations</li>
<li>Entry requirement changes</li>
</ul>
<p>Travelers are encouraged to review official government travel advisories before departure.</p>
<p>Fares Hunt is not responsible for disruptions caused by international travel conditions beyond our reasonable control.</p>
<p>Travel conditions may change rapidly and without notice.</p>
</section>
<section class="fhp-section" id="disclaimer-7"><h2>7. Travel Insurance Disclaimer</h2>
<p>Travel insurance is generally not included with travel bookings unless specifically purchased and confirmed.</p>
<p>Customers are strongly encouraged to obtain appropriate travel insurance coverage for trip cancellations, medical emergencies, travel interruptions, baggage loss, travel delays, and other unforeseen circumstances.</p>
<p>Fares Hunt does not guarantee reimbursement for losses that may otherwise be covered by travel insurance policies.</p>
<p>Travel insurance may provide valuable protection against unexpected travel disruptions.</p>
</section>
<section class="fhp-section" id="disclaimer-8"><h2>8. Limitation of Liability</h2>
<p>To the fullest extent permitted by applicable law, Fares Hunt and Aeronza Travel Group LLC shall not be liable for any indirect, incidental, consequential, special, punitive, or exemplary damages arising from:</p>
<ul class="fhp-list">
<li>Travel interruptions</li>
<li>Supplier failures</li>
<li>Booking issues</li>
<li>Travel delays</li>
<li>Missed connections</li>
<li>Lost profits</li>
<li>Loss of enjoyment</li>
<li>Personal inconvenience</li>
<li>Third-party service failures</li>
</ul>
<p>Any liability, where legally established, shall be limited to the amount paid directly to Fares Hunt for the applicable booking transaction.</p>
<p>Certain legal limitations may vary by jurisdiction.</p>
</section>
<section class="fhp-section" id="disclaimer-9"><h2>9. Force Majeure Disclaimer</h2>
<p>Fares Hunt shall not be responsible for delays, cancellations, disruptions, losses, or failures caused by events beyond our reasonable control, including:</p>
<ul class="fhp-list">
<li>Severe weather</li>
<li>Natural disasters</li>
<li>Floods</li>
<li>Earthquakes</li>
<li>Pandemics</li>
<li>Public health emergencies</li>
<li>Government actions</li>
<li>Airport closures</li>
<li>Labor disputes</li>
<li>Civil unrest</li>
<li>Terrorist acts</li>
<li>War</li>
<li>Technology failures</li>
</ul>
<p>Travel supplier policies shall govern any available credits, vouchers, refunds, or travel accommodations resulting from such events.</p>
<p>Force majeure events may affect travel services without prior warning.</p>
</section>
<section class="fhp-section" id="disclaimer-10"><h2>10. Third-Party Website Disclaimer</h2>
<p>This website may contain links to third-party websites operated by airlines, hotels, travel suppliers, advertisers, or other organizations.</p>
<p>Fares Hunt does not control and is not responsible for:</p>
<ul class="fhp-list">
<li>Third-party content</li>
<li>Privacy practices</li>
<li>Security practices</li>
<li>Website availability</li>
<li>Products or services offered by third parties</li>
</ul>
<p>Users access third-party websites at their own discretion and risk.</p>
<p>Third-party websites operate independently of Fares Hunt.</p>
</section>
<section class="fhp-section" id="disclaimer-11"><h2>11. No Professional Advice</h2>
<p>Information provided on this website does not constitute legal, immigration, tax, financial, medical, or professional advice.</p>
<p>Travelers should consult qualified professionals regarding matters involving visas, immigration requirements, taxation, health requirements, insurance coverage, or other specialized issues.</p>
<p>Professional advice should be obtained directly from qualified experts.</p>
</section>
<section class="fhp-section" id="disclaimer-12"><h2>12. Changes to This Disclaimer</h2>
<p>Fares Hunt reserves the right to modify, update, or revise this Disclaimer at any time without prior notice.</p>
<p>Updated versions will be posted on this website with a revised effective date.</p>
<p>Continued use of our website or services following any updates constitutes acceptance of the revised Disclaimer.</p>
<p>Customers are encouraged to review this Disclaimer periodically.</p>
</section>
<section class="fhp-section" id="disclaimer-13"><h2>Contact Information</h2>
<p>Aeronza Travel Group LLC (DBA Fares Hunt)</p>
<p>Mailing Address:</p>
<p>1603 Capitol Ave Suite 413G-2599</p>
<p>Cheyenne, Wyoming 82001</p>
<p>United States</p>
<p>WhatsApp: +1 (307) 263-8435</p>
<p>General Support: [info@fareshunt.com](mailto:info@fareshunt.com)</p>
<p>Customer Support: Available 24 Hours a Day, 7 Days a Week</p>
</section>
<section class="fhp-section" id="disclaimer-14"><h2>Acknowledgement</h2>
<p>By accessing the Fares Hunt website, requesting travel services, obtaining travel quotations, making reservations, or otherwise using our services, you acknowledge that you have read, understood, and agreed to this Disclaimer.</p>
<p>Your continued use of our website and services constitutes acceptance of this Disclaimer.</p>
</section>
<div class="fhp-contact"><div class="fhp-contact-grid">
<div class="fhp-contact-card"><b>Customer Support</b><a href="tel:+18887170346">+1-888-717-0346</a></div>
<div class="fhp-contact-card"><b>WhatsApp</b><a href="https://wa.me/13072638435" target="_blank" rel="noopener">+1 (307) 263-8435</a></div>
<div class="fhp-contact-card"><b>Email</b><a href="mailto:info@fareshunt.com">info@fareshunt.com</a></div>
<div class="fhp-contact-card"><b>Address</b><span>1603 Capitol Ave Suite 413G-2599<br>Cheyenne, Wyoming 82001<br>United States</span></div>
</div></div>
</main></div></div></section></div>

@elseif($isPrivacy)
<div class="fhp-page">
<section class="fhp-hero"><div class="fhp-hero-inner">
<span class="fhp-kicker">PRIVACY &amp; DATA PROTECTION</span>
<h1>Privacy Policy</h1>
<p>Learn how Fares Hunt collects, uses, shares, stores and protects information when you use our website and travel booking services.</p>
<div class="fhp-meta"><span><i class="fa-regular fa-calendar"></i> Effective June 10, 2026</span><span><i class="fa-solid fa-shield-halved"></i> Privacy &amp; Security</span><span><i class="fa-solid fa-building"></i> Aeronza Travel Group LLC</span></div>
</div></section>
<section class="fhp-wrap"><div class="container"><div class="fhp-layout">
<aside class="fhp-toc"><b>On this page</b>
@foreach([[1,'Information We Collect'],[2,'How We Use Information'],[3,'Sharing of Information'],[4,'Fraud Prevention'],[5,'Cookies & Tracking'],[6,'Data Retention'],[7,'Data Security'],[8,'Third-Party Websites'],[9,"Children's Privacy"],[10,'Marketing Communications'],[11,'Your Privacy Rights'],[12,'State-Specific Rights'],[13,'Policy Changes'],[14,'Contact Information']] as $toc)
<a href="#privacy-{{ $toc[0] }}">{{ $toc[0] }}. {{ $toc[1] }}</a>@endforeach
</aside>
<main class="fhp-doc">
<div class="fhp-intro"><p>Fares Hunt, operated by Aeronza Travel Group LLC, respects your privacy and is committed to protecting the personal information you provide when using our website and travel booking services.</p><p>By using our website or services, you acknowledge that you have read and understood this Privacy Policy.</p></div>

<section class="fhp-section" id="privacy-1"><h2>1. Information We Collect</h2>
<h3>Personal Information</h3><ul class="fhp-list"><li>Full name</li><li>Date of birth</li><li>Gender</li><li>Mailing and billing address</li><li>Email address</li><li>Telephone number</li><li>Passport information when required</li><li>Government-issued identification when required for verification</li></ul>
<h3>Travel Information</h3><ul class="fhp-list"><li>Passenger names</li><li>Flight preferences</li><li>Hotel preferences</li><li>Cruise selections</li><li>Car rental selections</li><li>Travel dates</li><li>Destination information</li><li>Frequent flyer information</li></ul>
<h3>Payment Information</h3><p>Payments are processed through authorized payment providers. We may collect billing name, billing address, the last four digits of payment cards, transaction details and payment authorization records.</p>
<div class="fhp-callout">For security purposes, Fares Hunt does not intentionally store complete credit card numbers unless required by applicable law or authorized payment processing procedures.</div>
<h3>Technical Information</h3><ul class="fhp-list"><li>IP address</li><li>Browser type</li><li>Device information</li><li>Operating system</li><li>Website usage data</li><li>Referral sources</li><li>Cookies and analytics information</li></ul></section>

<section class="fhp-section" id="privacy-2"><h2>2. How We Use Your Information</h2><p>We may use collected information for legitimate business purposes related to travel services and customer support, including:</p><ul class="fhp-list"><li>Process reservations</li><li>Issue airline tickets</li><li>Confirm travel services</li><li>Provide customer support</li><li>Process payments</li><li>Verify customer identity</li><li>Prevent fraud</li><li>Respond to inquiries</li><li>Deliver booking confirmations</li><li>Provide travel updates</li><li>Improve website functionality</li><li>Comply with legal obligations</li><li>Resolve billing disputes</li></ul></section>

<section class="fhp-section" id="privacy-3"><h2>3. Sharing of Information</h2><p>To fulfill reservations, we may share information with airlines, hotels, cruise lines, car rental providers, tour operators, global distribution systems, payment processors, fraud prevention providers, customer support providers and government agencies when legally required.</p><p>Information shared with suppliers is limited to what is reasonably necessary to complete travel arrangements.</p><div class="fhp-callout">Fares Hunt does not sell personal information to unrelated third parties for marketing purposes.</div><h3>International Data Transfers</h3><p>Because travel services are global, customer information may be transferred to travel suppliers, reservation systems and service providers in different countries where reasonably necessary to fulfill travel reservations and related services.</p></section>

<section class="fhp-section" id="privacy-4"><h2>4. Fraud Prevention and Identity Verification</h2><p>To protect customers and prevent unauthorized transactions, we may request government-issued identification, payment card verification, proof of billing address or additional verification documentation.</p><p>Verification records may be retained for fraud prevention, payment verification, legal compliance and dispute resolution purposes.</p></section>

<section class="fhp-section" id="privacy-5"><h2>5. Cookies and Tracking Technologies</h2><p>Our website may use cookies and similar technologies to improve functionality, remember preferences, analyze website traffic, improve customer experience and detect fraudulent activity.</p><p>Users may adjust browser settings to manage cookies; however, certain website functions may become unavailable if cookies are disabled.</p></section>

<section class="fhp-section" id="privacy-6"><h2>6. Data Retention</h2><p>Fares Hunt may retain customer information for as long as reasonably necessary to complete travel services, maintain accounting records, comply with legal obligations, prevent fraud, resolve disputes and enforce agreements.</p><p>Retention periods may vary depending on legal, regulatory, operational and contractual requirements.</p></section>

<section class="fhp-section" id="privacy-7"><h2>7. Data Security</h2><p>We implement reasonable administrative, technical and organizational safeguards designed to protect personal information from unauthorized access, disclosure, alteration or destruction.</p><div class="fhp-callout">No internet transmission, electronic storage system or security measure can be guaranteed to be completely secure.</div></section>

<section class="fhp-section" id="privacy-8"><h2>8. Third-Party Websites</h2><p>Our website may contain links to third-party websites, including airlines, hotels, payment processors and travel suppliers. Fares Hunt is not responsible for their privacy practices, content or security. Users should review those websites' privacy policies independently.</p></section>

<section class="fhp-section" id="privacy-9"><h2>9. Children's Privacy</h2><p>Fares Hunt does not knowingly collect personal information directly from children under the age of 13 without appropriate parental involvement. Travel reservations involving minors must be completed by a parent, legal guardian or authorized adult.</p></section>

<section class="fhp-section" id="privacy-10"><h2>10. Marketing Communications</h2><p>Fares Hunt may send booking-related communications, service updates, promotional offers, newsletters and travel-related marketing communications. Customers may opt out of marketing communications using the unsubscribe instructions provided, while essential transactional and booking-related notices may still be sent.</p></section>

<section class="fhp-section" id="privacy-11"><h2>11. Your Privacy Rights</h2><p>Depending on applicable law, customers may have rights to request access to personal information, correction of inaccurate information, deletion where permitted by law, and information regarding data processing practices.</p><p>Requests may be subject to identity verification and legal limitations.</p></section>

<section class="fhp-section" id="privacy-12"><h2>12. State-Specific Privacy Rights</h2><p>Residents of certain U.S. states may have additional privacy rights under applicable state laws. Fares Hunt will respond to verified privacy requests as required by applicable law.</p></section>

<section class="fhp-section" id="privacy-13"><h2>13. Changes to This Privacy Policy</h2><p>Fares Hunt reserves the right to update or modify this Privacy Policy at any time. Updated versions will be posted on this website with a revised effective date. Continued use following publication of changes constitutes acceptance of the revised Privacy Policy.</p></section>

<section class="fhp-section" id="privacy-14"><h2>14. Contact Information</h2>
<div class="fhp-contact"><p><strong>Aeronza Travel Group LLC (DBA Fares Hunt)</strong></p><div class="fhp-contact-grid">
<div class="fhp-contact-card"><b>Customer Support</b><a href="tel:+18887170346">+1-888-717-0346</a></div>
<div class="fhp-contact-card"><b>WhatsApp</b><a href="https://wa.me/13072638435" target="_blank" rel="noopener">+1 (307) 263-8435</a></div>
<div class="fhp-contact-card"><b>Email</b><a href="mailto:info@fareshunt.com">info@fareshunt.com</a></div>
<div class="fhp-contact-card"><b>Mailing Address</b><span>1603 Capitol Ave Suite 413G-2599<br>Cheyenne, Wyoming 82001<br>United States</span></div>
</div></div></section>

<div class="fhp-ack"><h3>Acknowledgement</h3><p>By using the Fares Hunt website, requesting travel services, obtaining quotations, making reservations, submitting information or otherwise interacting with our services, you acknowledge that you have read, understood and agreed to this Privacy Policy.</p></div>
</main></div></div></section></div>

@elseif($isCookies)
<div class="fhp-page">
<section class="fhp-hero"><div class="fhp-hero-inner">
<span class="fhp-kicker">WEBSITE &amp; COOKIE INFORMATION</span>
<h1>Cookies Policy</h1>
<p>Learn what cookies are, how Fares Hunt uses cookies and similar technologies, and the choices available to you regarding their use.</p>
<div class="fhp-meta"><span><i class="fa-regular fa-calendar"></i> Effective June 10, 2026</span><span><i class="fa-solid fa-cookie-bite"></i> Cookie Preferences</span><span><i class="fa-solid fa-building"></i> Aeronza Travel Group LLC</span></div>
</div></section>
<section class="fhp-wrap"><div class="container"><div class="fhp-layout">
<aside class="fhp-toc"><b>On this page</b>
@foreach([[1,'What Are Cookies?'],[2,'How We Use Cookies'],[3,'Types of Cookies'],[4,'Third-Party Cookies'],[5,'Managing Cookies'],[6,'Do Not Track Signals'],[7,'Data Protection & Security'],[8,'International Users'],[9,'Policy Changes'],[10,'Contact Information']] as $toc)
<a href="#cookies-{{ $toc[0] }}">{{ $toc[0] }}. {{ $toc[1] }}</a>@endforeach
</aside>
<main class="fhp-doc">
<div class="fhp-intro"><p>Fares Hunt, operated by Aeronza Travel Group LLC, uses cookies and similar tracking technologies to improve website functionality, enhance user experience, analyze website performance and help protect against fraudulent activity.</p><p>By continuing to use our website, you consent to our use of cookies in accordance with this Cookies Policy.</p></div>

<section class="fhp-section" id="cookies-1"><h2>1. What Are Cookies?</h2><p>Cookies are small text files placed on your computer, smartphone, tablet or other device when you visit a website. They help websites recognize users, remember preferences, improve functionality and provide a more personalized browsing experience.</p><p>Cookies generally do not contain information that directly identifies an individual user; however, personal information we store may be linked to information stored in cookies.</p></section>

<section class="fhp-section" id="cookies-2"><h2>2. How We Use Cookies</h2><p>Fares Hunt uses cookies and similar technologies for business and operational purposes, including:</p><ul class="fhp-list"><li>Maintaining website functionality</li><li>Remembering user preferences</li><li>Improving website performance</li><li>Enhancing user experience</li><li>Processing travel searches and reservations</li><li>Preventing fraud and unauthorized activity</li><li>Measuring website traffic and usage trends</li><li>Improving customer support services</li><li>Supporting marketing and advertising activities</li></ul></section>

<section class="fhp-section" id="cookies-3"><h2>3. Types of Cookies We Use</h2>
<h3>Essential Cookies</h3><p>These cookies are necessary for website operation and cannot be disabled through our systems.</p><ul class="fhp-list"><li>Security functions</li><li>Fraud prevention</li><li>Session management</li><li>Website navigation</li><li>Reservation processing</li></ul>
<h3>Performance and Analytics Cookies</h3><p>These cookies help us understand website usage through information such as pages visited, time spent on pages, performance metrics, error reports and traffic sources. Analytics information is generally aggregated and used to improve website performance.</p>
<h3>Functional Cookies</h3><p>Functional cookies remember choices such as language preferences, location preferences, search settings and other user preferences to provide a more customized browsing experience.</p>
<h3>Marketing and Advertising Cookies</h3><p>Marketing cookies may be used to deliver relevant advertisements, measure advertising effectiveness, limit repetitive advertisements and support marketing campaigns. They may be placed by us or approved third-party advertising partners.</p></section>

<section class="fhp-section" id="cookies-4"><h2>4. Third-Party Cookies</h2><p>Some cookies may be placed by third-party providers that assist with website analytics, advertising services, payment processing, fraud prevention, customer support tools and social media integrations.</p><p>Fares Hunt does not control cookies used by third-party providers. Users should review the privacy and cookie policies of those providers separately.</p></section>

<section class="fhp-section" id="cookies-5"><h2>5. Managing Cookies</h2><p>Most web browsers allow users to view stored cookies, delete cookies, block cookies and receive notifications when cookies are being placed.</p><p>Users who disable or block cookies may experience reduced website functionality or may not be able to access certain features.</p><div class="fhp-callout">Depending on your location, you may be presented with a cookie consent banner that allows you to accept, reject or customize certain categories of non-essential cookies.</div></section>

<section class="fhp-section" id="cookies-6"><h2>6. Do Not Track Signals</h2><p>Some web browsers may transmit “Do Not Track” (DNT) signals. Because there is currently no universally accepted standard for responding to DNT signals, Fares Hunt may not respond to such signals. Website functionality and data collection practices may continue regardless of DNT browser settings.</p></section>

<section class="fhp-section" id="cookies-7"><h2>7. Data Protection and Security</h2><p>Information collected through cookies may be used with other information collected under our Privacy Policy. Fares Hunt implements reasonable safeguards designed to protect information collected through cookies and similar technologies.</p><div class="fhp-callout">No electronic system can be guaranteed to be completely secure.</div></section>

<section class="fhp-section" id="cookies-8"><h2>8. International Users</h2><p>Because Fares Hunt provides travel services to customers traveling worldwide, information collected through cookies may be processed in different jurisdictions where service providers, travel suppliers or technology partners operate.</p><p>By using our website, you consent to such processing where reasonably necessary to provide services and operate our business.</p></section>

<section class="fhp-section" id="cookies-9"><h2>9. Changes to This Cookies Policy</h2><p>Fares Hunt reserves the right to modify or update this Cookies Policy at any time. Updated versions will be posted on this website along with a revised effective date. Continued use following updates constitutes acceptance of the revised Cookies Policy.</p></section>

<section class="fhp-section" id="cookies-10"><h2>10. Contact Information</h2>
<div class="fhp-contact"><p><strong>Aeronza Travel Group LLC (DBA Fares Hunt)</strong></p><div class="fhp-contact-grid">
<div class="fhp-contact-card"><b>Customer Support</b><a href="tel:+18887170346">+1-888-717-0346</a></div>
<div class="fhp-contact-card"><b>WhatsApp</b><a href="https://wa.me/13072638435" target="_blank" rel="noopener">+1 (307) 263-8435</a></div>
<div class="fhp-contact-card"><b>Email</b><a href="mailto:info@fareshunt.com">info@fareshunt.com</a></div>
<div class="fhp-contact-card"><b>Mailing Address</b><span>1603 Capitol Ave Suite 413G-2599<br>Cheyenne, Wyoming 82001<br>United States</span></div>
</div></div></section>

<div class="fhp-ack"><h3>Acknowledgement</h3><p>By continuing to use the Fares Hunt website, you acknowledge that you have read, understood and agreed to this Cookies Policy. Your continued use of our website constitutes acceptance of this policy.</p></div>
</main></div></div></section></div>

@elseif($isTerms)

<div class="fht-page">

    <section class="fht-hero">
        <div class="fht-hero-inner">
            <span class="fht-kicker">LEGAL INFORMATION</span>
            <h1>Terms &amp; Conditions</h1>
            <p>
                These Terms and Conditions govern your use of Fares Hunt's website and travel booking services,
                including airline tickets, hotel accommodations, vacation packages, cruises, car rentals and related travel products.
            </p>

            <div class="fht-meta">
                <span><i class="fa-regular fa-calendar"></i> Effective June 10, 2026</span>
                <span><i class="fa-solid fa-building"></i> Aeronza Travel Group LLC</span>
                <span><i class="fa-solid fa-scale-balanced"></i> Wyoming, United States</span>
            </div>
        </div>
    </section>

    <section class="fht-wrap">
        <div class="container">
            <div class="fht-layout">

                <aside class="fht-toc">
                    <b>On this page</b>
                    @foreach([
                        [1,'Our Role as an Intermediary'],
                        [2,'Pricing, Fares & Payments'],
                        [3,'Booking Confirmations'],
                        [4,'Electronic Acceptance'],
                        [5,'Promotional Fares'],
                        [6,'Cancellations & Refunds'],
                        [7,'Ticket Changes'],
                        [8,'Names & Travel Documents'],
                        [9,'Hotels, Cruises & Cars'],
                        [10,'Baggage & Ancillaries'],
                        [11,'Force Majeure'],
                        [12,'Verification & Billing Disputes'],
                        [13,'Privacy & Data Protection'],
                        [14,'Dispute Resolution'],
                        [15,'Contact Information'],
                    ] as $toc)
                    <a href="#term-{{ $toc[0] }}">{{ $toc[0] }}. {{ $toc[1] }}</a>
                    @endforeach
                </aside>

                <main class="fht-doc">

                    <div class="fht-intro">
                        <p>
                            Welcome to Fares Hunt. By accessing our platform, consulting with our travel agents,
                            requesting quotations, making payments or finalizing a booking, you acknowledge that you
                            have read, understood and agree to be legally bound by these Terms and Conditions.
                        </p>
                        <p>
                            Fares Hunt is owned and operated by Aeronza Travel Group LLC, a company organized and
                            existing under the laws of the State of Wyoming, United States.
                        </p>
                    </div>

                    <section class="fht-section" id="term-1">
                        <h2>1. Our Role as an Intermediary (Agency Disclaimer)</h2>
                        <p>Fares Hunt operates solely as an independent travel agency and booking intermediary.</p>
                        <p>We do not own, manage, operate or control any airline, hotel, cruise line, car rental company, tour operator, transportation provider, aircraft, vessel, vehicle or travel supplier.</p>
                        <p>Travel services may be provided by independent third-party airlines, hotels, cruise operators, tour operators, car rental companies and other travel suppliers.</p>
                        <p>Your booking is subject to the applicable terms, conditions, policies and contracts of the respective travel supplier.</p>
                        <p>Fares Hunt shall not be responsible for supplier actions, operational decisions, delays, cancellations, schedule changes, overbooking situations, baggage issues, service interruptions, or any injury, loss, damage or expense arising from the acts or omissions of third-party travel suppliers.</p>
                    </section>

                    <section class="fht-section" id="term-2">
                        <h2>2. Pricing, Fares, and Payment Processing</h2>
                        <h3>Fare Guarantees</h3>
                        <p>All fares, prices and travel products displayed on our website are subject to real-time availability and may change without prior notice. No fare is guaranteed until full payment has been successfully processed and the reservation has been ticketed or confirmed by the applicable supplier.</p>
                        <h3>Taxes and Fees</h3>
                        <p>Displayed prices generally include applicable taxes, government-imposed fees and agency service charges unless otherwise stated. Optional services such as seat assignments, baggage, upgrades, insurance or other ancillary services may incur additional charges.</p>
                        <h3>Transaction Processing</h3>
                        <p>Payments may appear on your billing statement as a single charge or multiple charges. Charges may be processed by Fares Hunt, Aeronza Travel Group LLC, an airline, hotel, cruise supplier or another travel supplier depending on the booking structure.</p>
                        <h3>Pricing Discrepancies</h3>
                        <p>In the event of a pricing error, technical malfunction, supplier error or system issue, Fares Hunt reserves the right to cancel the affected reservation and issue a full refund.</p>
                        <h3>Merchant Billing Descriptor</h3>
                        <p>Card statements may reflect charges from Aeronza Travel Group LLC, Fares Hunt, travel services, airline services or affiliated payment processing descriptors. Billing descriptors may vary depending on the payment processor utilized.</p>
                    </section>

                    <section class="fht-section" id="term-3">
                        <h2>3. Booking Confirmations and Ticket Verification</h2>
                        <p>An order confirmation, itinerary receipt or booking request does not guarantee ticket issuance.</p>
                        <p>A reservation becomes confirmed only after a valid confirmation number, ticket number or supplier-issued booking reference has been generated and delivered.</p>
                        <p>Customers are responsible for reviewing booking confirmations immediately upon receipt and notifying Fares Hunt of any errors within twenty-four (24) hours.</p>
                    </section>

                    <section class="fht-section" id="term-4">
                        <h2>4. Electronic Acceptance and Authorization</h2>
                        <p>By placing an order, requesting a reservation, making a payment, signing an authorization form, responding to a booking confirmation email, authorizing a transaction by telephone, or otherwise authorizing a booking, you acknowledge and agree to these Terms and Conditions.</p>
                        <p>Electronic signatures, emails, text messages, recorded telephone authorizations, digital communications and online confirmations may be used as evidence of customer authorization and acceptance.</p>
                    </section>

                    <section class="fht-section" id="term-5">
                        <h2>5. Special Promotional and Discounted Fares</h2>
                        <p>Fares Hunt may offer promotional, negotiated, consolidator, unpublished or discounted fares.</p>
                        <p>These fares may be subject to additional restrictions, including:</p>
                        <ul>
                            <li>Limited availability</li>
                            <li>Non-refundable status</li>
                            <li>Non-changeable status</li>
                            <li>Restricted routing</li>
                            <li>Delayed ticket issuance</li>
                        </ul>
                        <p>Ticket issuance may occur after booking confirmation and, in some circumstances, closer to the departure date.</p>
                        <p>Such delayed ticket issuance shall not constitute non-delivery of services, fraud or grounds for payment disputes where the reservation remains active and valid.</p>
                        <p>Promotional fares are generally non-changeable and non-refundable unless otherwise stated by the applicable supplier.</p>
                    </section>

                    <section class="fht-section" id="term-6">
                        <h2>6. Cancellations, Refunds, and the U.S. 24-Hour Rule</h2>
                        <h3>U.S. DOT 24-Hour Rule</h3>
                        <p>Where required by applicable U.S. Department of Transportation regulations, eligible flight reservations may be canceled within twenty-four (24) hours of purchase for a full refund when booked at least seven (7) days prior to departure.</p>
                        <h3>After Twenty-Four Hours</h3>
                        <p>Once the applicable cancellation window has expired, all cancellations, refunds and credits become subject to the supplier's fare rules and restrictions.</p>
                        <h3>Agency Service Fees</h3>
                        <p>Where permitted, Fares Hunt may charge an administrative processing fee of USD $50 per passenger for voluntary cancellations, modifications or refund processing.</p>
                        <h3>Refund Timelines</h3>
                        <p>Approved refunds are generally processed within seven (7) to fourteen (14) business days; however, actual posting times depend on suppliers, payment processors, banks and card issuers.</p>
                    </section>

                    <section class="fht-section" id="term-7">
                        <h2>7. Ticket Changes and Itinerary Modifications</h2>
                        <p>Changes to travel dates, destinations, routes, names or travel arrangements are subject to supplier rules, availability, fare differences, penalties and applicable service fees.</p>
                        <p>Many promotional fares do not permit modifications.</p>
                    </section>

                    <section class="fht-section" id="term-8">
                        <h2>8. Passenger Name Accuracy and Legal Documentation</h2>
                        <p>Passenger names must exactly match the government-issued identification used for travel.</p>
                        <p>Travelers are solely responsible for obtaining:</p>
                        <ul>
                            <li>Passports</li>
                            <li>Visas</li>
                            <li>Transit permits</li>
                            <li>Health documentation</li>
                            <li>Entry permits</li>
                            <li>Other required travel documents</li>
                        </ul>
                        <p>Fares Hunt is not responsible for denied boarding, refused entry, deportation or travel disruption resulting from insufficient documentation.</p>
                    </section>

                    <section class="fht-section" id="term-9">
                        <h2>9. Hotels, Vacation Packages, Cruises, and Car Rentals</h2>
                        <p>Reservations involving hotels, vacation packages, cruises, car rentals and other travel products are subject to the individual terms, cancellation policies, refund rules, operational requirements and limitations imposed by the applicable supplier.</p>
                        <p>Fares Hunt acts solely as an intermediary and is not responsible for supplier performance, availability, pricing changes, service interruptions, overbooking situations or operational decisions made by third-party travel providers.</p>
                    </section>

                    <section class="fht-section" id="term-10">
                        <h2>10. Baggage and Ancillary Policies</h2>
                        <p>Baggage allowances, carry-on limits, weight restrictions, seat assignments, upgrades, meals and ancillary services are governed solely by the policies of the applicable travel supplier.</p>
                        <p>Fares Hunt is not responsible for supplier-imposed fees related to baggage or ancillary services.</p>
                    </section>

                    <section class="fht-section" id="term-11">
                        <h2>11. Force Majeure and Unforeseen Disruptions</h2>
                        <p>Fares Hunt and Aeronza Travel Group LLC shall not be liable for delays, cancellations, interruptions, losses or failures caused by events beyond reasonable control, including but not limited to:</p>
                        <ul>
                            <li>Natural disasters</li>
                            <li>Severe weather</li>
                            <li>Pandemics</li>
                            <li>Public health emergencies</li>
                            <li>Labor disputes</li>
                            <li>Government actions</li>
                            <li>Airport closures</li>
                            <li>Civil unrest</li>
                            <li>Terrorism</li>
                            <li>War</li>
                            <li>Transportation disruptions</li>
                        </ul>
                        <p>Any credits, refunds, vouchers, rebooking options or other remedies arising from such events shall be governed exclusively by the policies of the applicable supplier.</p>
                    </section>

                    <section class="fht-section" id="term-12">
                        <h2>12. Customer Communications, Transaction Verification, and Billing Disputes</h2>
                        <p>Fares Hunt and Aeronza Travel Group LLC may retain records relating to reservations and payments, including:</p>
                        <ul>
                            <li>Booking records</li>
                            <li>Invoices and itineraries</li>
                            <li>Payment authorizations</li>
                            <li>Email communications</li>
                            <li>Text messages</li>
                            <li>Recorded telephone conversations</li>
                            <li>Transaction logs</li>
                            <li>IP address records</li>
                            <li>Identity verification documents voluntarily provided by customers</li>
                            <li>Supplier confirmations and ticket records</li>
                        </ul>
                        <p>Such records may be used to verify customer authorization, investigate billing inquiries, respond to financial institutions, prevent fraud and defend against unauthorized transaction claims.</p>
                        <div class="fht-callout">Customers are encouraged to contact Fares Hunt before initiating a payment dispute or chargeback so that billing concerns may be reviewed and resolved promptly.</div>
                    </section>

                    <section class="fht-section" id="term-13">
                        <h2>13. Privacy and Data Protection</h2>
                        <p>Fares Hunt and Aeronza Travel Group LLC collect, store and process customer information solely for the purpose of providing travel-related services, payment processing, fraud prevention, customer support, legal compliance and reservation fulfillment.</p>
                        <p>Customer information may be shared with airlines, hotels, cruise operators, car rental providers, payment processors, fraud prevention providers and other travel suppliers solely for the purpose of fulfilling reservations and related travel services.</p>
                        <p>By using our services, you consent to the collection and use of information in accordance with our Privacy Policy.</p>
                        <p>While reasonable security measures are utilized, no electronic transmission or storage system can be guaranteed to be completely secure.</p>
                    </section>

                    <section class="fht-section" id="term-14">
                        <h2>14. Dispute Resolution, Arbitration, and Governing Law</h2>
                        <h3>Governing Law</h3>
                        <p>These Terms and Conditions shall be governed and interpreted in accordance with the laws of the State of Wyoming, United States.</p>
                        <h3>Mandatory Arbitration</h3>
                        <p>To the fullest extent permitted by law, any dispute, claim or controversy arising out of or relating to these Terms, your booking or our services shall be resolved through binding individual arbitration.</p>
                        <h3>Class Action Waiver</h3>
                        <p>You and Fares Hunt agree to waive participation in any class action, collective action or representative proceeding.</p>
                    </section>

                    <section class="fht-section" id="term-15">
                        <h2>15. Contact Information</h2>

                        <div class="fht-contact">
                            <p><strong>Aeronza Travel Group LLC (DBA Fares Hunt)</strong></p>
                            <div class="fht-contact-grid">
                                <div class="fht-contact-card">
                                    <b>Customer Support</b>
                                    <a href="tel:+18887170346">+1-888-717-0346</a>
                                </div>
                                <div class="fht-contact-card">
                                    <b>WhatsApp</b>
                                    <a href="https://wa.me/13072638435" target="_blank" rel="noopener">+1 (307) 263-8435</a>
                                </div>
                                <div class="fht-contact-card">
                                    <b>Email</b>
                                    <a href="mailto:info@fareshunt.com">info@fareshunt.com</a>
                                </div>
                                <div class="fht-contact-card">
                                    <b>Mailing Address</b>
                                    <span>1603 Capitol Ave Suite 413G-2599<br>Cheyenne, Wyoming 82001<br>United States</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <div class="fht-ack">
                        <h3>Acknowledgement</h3>
                        <p>
                            By using Fares Hunt services, requesting travel arrangements, obtaining quotations,
                            making payments, submitting authorization forms or confirming reservations, you acknowledge
                            that you have read, understood and agreed to these Terms and Conditions in their entirety.
                        </p>
                    </div>

                </main>
            </div>
        </div>
    </section>

</div>

@elseif($isAbout)





<style id="fh-about-premium-v2">
.fha-about{
    --fha-navy:#071a31;
    --fha-navy2:#0d294a;
    --fha-red:#d91f43;
    --fha-gold:#d7a82d;
    --fha-ink:#0a2340;
    --fha-copy:#5b708b;
    --fha-soft:#f5f8fc;
    --fha-line:#e1e7ef;
    background:#fff;
    color:var(--fha-ink);
    font-family:inherit;
}
.fha-about *{box-sizing:border-box}
.fha-about .container{
    width:min(1500px,calc(100% - 48px));
    max-width:none;
    margin:0 auto;
}

/* HERO */
.fha-about-hero{
    position:relative;
    overflow:hidden;
    padding:66px 20px 70px;
    background:
        radial-gradient(circle at 80% 15%,rgba(217,31,67,.10),transparent 24%),
        radial-gradient(circle at 15% 10%,rgba(75,132,194,.12),transparent 26%),
        linear-gradient(120deg,#07182d 0%,#0b213d 56%,#103255 100%);
    color:#fff;
    text-align:center;
}
.fha-about-hero::before{
    content:"";
    position:absolute;
    inset:0;
    background:
        radial-gradient(circle at 12% 62%,rgba(255,255,255,.10) 1px,transparent 1.5px),
        radial-gradient(circle at 84% 32%,rgba(255,255,255,.08) 1px,transparent 1.5px);
    background-size:90px 90px,120px 120px;
    opacity:.35;
}
.fha-about-hero::after{
    content:"✈";
    position:absolute;
    right:7%;
    top:44%;
    color:rgba(255,255,255,.68);
    font-size:20px;
    transform:rotate(-18deg);
}
.fha-about-hero-inner{
    position:relative;
    z-index:1;
    max-width:940px;
    margin:auto;
}
.fha-about-kicker{
    display:inline-flex;
    align-items:center;
    gap:10px;
    margin-bottom:14px;
    color:#f1bd3d;
    font-size:10px;
    font-weight:700;
    letter-spacing:.20em;
    text-transform:uppercase;
}
.fha-about-kicker::before,.fha-about-kicker::after{
    content:"";
    width:30px;
    height:1px;
    background:currentColor;
}
.fha-about-hero h1{
    margin:0 0 14px!important;
    color:#fff!important;
    font-family:inherit!important;
    font-size:clamp(42px,4.3vw,62px)!important;
    line-height:1.04!important;
    font-weight:600!important;
    letter-spacing:-.045em!important;
}
.fha-about-hero p{
    max-width:760px;
    margin:0 auto;
    color:#c9d4e2;
    font-size:14px;
    line-height:1.7;
}
.fha-about-trust{
    display:flex;
    flex-wrap:wrap;
    justify-content:center;
    gap:9px;
    margin-top:23px;
}
.fha-about-trust span{
    display:inline-flex;
    align-items:center;
    gap:7px;
    min-height:34px;
    padding:0 12px;
    border:1px solid rgba(255,255,255,.13);
    border-radius:999px;
    background:rgba(255,255,255,.05);
    color:#e6edf5;
    font-size:10.5px;
}

/* SHARED */
.fha-section{padding:56px 0}
.fha-section.soft{background:var(--fha-soft)}
.fha-eyebrow{
    display:block;
    margin-bottom:8px;
    color:var(--fha-gold);
    font-size:9px;
    font-weight:700;
    letter-spacing:.18em;
    text-transform:uppercase;
}
.fha-title{
    margin:0 0 12px;
    color:var(--fha-ink);
    font-size:clamp(30px,2.5vw,40px);
    line-height:1.08;
    font-weight:650;
    letter-spacing:-.035em;
}
.fha-copy{
    color:var(--fha-copy);
    font-size:14px;
    line-height:1.75;
}

/* INTRO */
.fha-intro-grid{
    display:grid;
    grid-template-columns:minmax(0,1.12fr) minmax(340px,.88fr);
    gap:42px;
    align-items:center;
}
.fha-intro-copy p{margin:0 0 15px}
.fha-intro-card{
    padding:25px;
    border:1px solid var(--fha-line);
    border-radius:20px;
    background:#fff;
    box-shadow:0 12px 30px rgba(20,44,77,.055);
}
.fha-intro-card h3{
    margin:0 0 16px;
    color:var(--fha-ink);
    font-size:17px;
    font-weight:650;
}
.fha-intro-card ul{
    display:grid;
    gap:9px;
    margin:0;
    padding:0;
    list-style:none;
}
.fha-intro-card li{
    display:flex;
    align-items:center;
    gap:9px;
    color:#536a86;
    font-size:12px;
}
.fha-intro-card li i{
    display:grid;
    place-items:center;
    width:24px;
    height:24px;
    border-radius:8px;
    background:#fff1f4;
    color:var(--fha-red);
    font-size:9px;
}

/* SERVICES */
.fha-services-grid{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:14px;
    margin-top:25px;
}
.fha-service{
    min-height:128px;
    padding:20px;
    border:1px solid var(--fha-line);
    border-radius:18px;
    background:#fff;
    box-shadow:0 8px 22px rgba(20,44,77,.035);
}
.fha-service i{
    display:grid;
    place-items:center;
    width:38px;
    height:38px;
    margin-bottom:13px;
    border-radius:12px;
    background:#edf3f8;
    color:#315a86;
    font-size:15px;
}
.fha-service b{
    display:block;
    margin-bottom:5px;
    color:var(--fha-ink);
    font-size:12.5px;
}
.fha-service span{
    color:#6a7f98;
    font-size:11px;
    line-height:1.5;
}

/* DIFFERENCE */
.fha-difference{background:#fff}
.fha-difference-grid{
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:14px;
    margin-top:24px;
}
.fha-diff-card{
    padding:22px;
    border:1px solid var(--fha-line);
    border-radius:18px;
    background:#fff;
}
.fha-diff-card i{color:var(--fha-red);font-size:17px}
.fha-diff-card h3{
    margin:12px 0 6px;
    color:var(--fha-ink);
    font-size:14px;
    font-weight:650;
}
.fha-diff-card p{
    margin:0;
    color:#667b95;
    font-size:11.5px;
    line-height:1.55;
}

/* BUSINESS */
.fha-business{
    background:
        radial-gradient(circle at 84% 18%,rgba(217,31,67,.08),transparent 24%),
        linear-gradient(120deg,#0a1e38,#102f50);
    color:#fff;
}
.fha-business-grid{
    display:grid;
    grid-template-columns:1.05fr .95fr;
    gap:38px;
    align-items:center;
}
.fha-business .fha-title{color:#fff}
.fha-business .fha-copy{color:#c6d2df}
.fha-business-points{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:10px;
}
.fha-business-points div{
    display:flex;
    align-items:center;
    gap:9px;
    min-height:48px;
    padding:10px 13px;
    border:1px solid rgba(255,255,255,.11);
    border-radius:14px;
    background:rgba(255,255,255,.045);
    color:#e6edf4;
    font-size:11px;
}
.fha-business-points i{color:#f1bd3d}

/* MISSION / VISION */
.fha-mv-grid,.fha-support-grid{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:18px;
}
.fha-mv-card,.fha-support-card{
    padding:28px;
    border:1px solid var(--fha-line);
    border-radius:20px;
    background:#fff;
}
.fha-mv-card{box-shadow:0 10px 28px rgba(20,44,77,.04)}
.fha-mv-icon{
    display:grid;
    place-items:center;
    width:44px;
    height:44px;
    margin-bottom:16px;
    border-radius:14px;
    background:#edf3f8;
    color:#315a86;
    font-size:18px;
}
.fha-mv-card h3,.fha-support-card h3{
    margin:0 0 10px;
    color:var(--fha-ink);
    font-size:18px;
    font-weight:650;
}
.fha-mv-card p,.fha-support-card p{
    margin:0 0 10px;
    color:#60758e;
    font-size:12px;
    line-height:1.65;
}

/* TRANSPARENCY */
.fha-transparency{
    padding:22px 24px;
    border:1px solid #eadfc2;
    border-radius:18px;
    background:#fffaf0;
}
.fha-transparency-head{
    display:flex;
    align-items:center;
    gap:11px;
    margin-bottom:8px;
}
.fha-transparency-head i{
    display:grid;
    place-items:center;
    width:36px;
    height:36px;
    border-radius:11px;
    background:#fff3d6;
    color:#b97800;
}
.fha-transparency h3{
    margin:0;
    color:#263d58;
    font-size:16px;
    font-weight:650;
}
.fha-transparency p{
    margin:0;
    color:#6b6f72;
    font-size:11.5px;
    line-height:1.65;
}

/* CORPORATE */
.fha-corp{background:#f3f6fa}
.fha-corp-grid{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:14px;
    margin-top:26px;
}
.fha-corp-card{
    min-height:150px;
    padding:21px;
    border:1px solid var(--fha-line);
    border-radius:18px;
    background:#fff;
    text-align:center;
}
.fha-corp-card i{
    display:grid;
    place-items:center;
    width:42px;
    height:42px;
    margin:0 auto 13px;
    border-radius:13px;
    background:#edf3f8;
    color:#315a86;
    font-size:16px;
}
.fha-corp-card b{
    display:block;
    margin-bottom:6px;
    color:var(--fha-ink);
    font-size:12.5px;
}
.fha-corp-card span,.fha-corp-card a{
    display:block;
    color:#617792!important;
    font-size:11px;
    line-height:1.5;
    text-decoration:none!important;
}
.fha-company-meta{
    margin-top:20px;
    color:#60758d;
    font-size:11px;
    line-height:1.6;
    text-align:center;
}

/* CLOSING CTA */
.fha-closing{
    position:relative;
    overflow:hidden;
    padding:52px 20px 56px;
    background:
        radial-gradient(circle at 50% 0%,rgba(255,255,255,.05),transparent 32%),
        linear-gradient(120deg,#071a31,#103255);
    color:#fff;
    text-align:center;
}
.fha-closing::before{
    content:"✈";
    display:grid;
    place-items:center;
    width:42px;
    height:42px;
    margin:0 auto 14px;
    border:1px solid rgba(241,189,61,.45);
    border-radius:50%;
    color:#f1bd3d;
    font-size:15px;
}
.fha-closing h2{
    margin:0 0 11px;
    color:#fff!important;
    font-size:30px;
    font-weight:650;
    letter-spacing:-.03em;
}
.fha-closing p{
    max-width:780px;
    margin:0 auto 12px;
    color:#d5deea!important;
    font-size:13px;
    line-height:1.65;
}
.fha-closing strong{
    color:#f1bd3d!important;
    font-size:12px;
    font-weight:700;
}

@media(max-width:1050px){
    .fha-intro-grid,.fha-business-grid{grid-template-columns:1fr}
    .fha-services-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
    .fha-difference-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
    .fha-corp-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
}
@media(max-width:700px){
    .fha-about .container{width:calc(100% - 24px)}
    .fha-about-hero{padding:52px 14px 56px}
    .fha-section{padding:42px 0}
    .fha-services-grid,.fha-difference-grid,.fha-mv-grid,.fha-support-grid,.fha-corp-grid{grid-template-columns:1fr}
    .fha-business-points{grid-template-columns:1fr}
    .fha-intro-card,.fha-mv-card,.fha-support-card{padding:22px}
}
</style>


<div class="fha-about">

    <section class="fha-about-hero">
        <div class="fha-about-hero-inner">
            <span class="fha-about-kicker">ABOUT FARES HUNT</span>
            <h1>Your Journey Begins with Fares Hunt</h1>
            <p>
                Fares Hunt, operated by Aeronza Travel Group LLC, is a U.S.-based online travel agency
                focused on helping travelers find affordable, convenient and reliable travel solutions worldwide.
            </p>

            <div class="fha-about-trust">
                <span><i class="fa-solid fa-plane"></i> Domestic &amp; international travel</span>
                <span><i class="fa-solid fa-shield-halved"></i> Secure booking support</span>
                <span><i class="fa-solid fa-headset"></i> Real human assistance</span>
            </div>
        </div>
    </section>


    <section class="fha-section">
        <div class="container">
            <div class="fha-intro-grid">
                <div class="fha-intro-copy">
                    <span class="fha-eyebrow">WHO WE ARE</span>
                    <h2 class="fha-title">Travel support built around real people</h2>

                    <div class="fha-copy">
                        <p>
                            Fares Hunt was created with a simple goal: to provide travelers with competitive airfare,
                            trusted travel assistance and personalized customer service that many large booking platforms
                            no longer offer.
                        </p>

                        <p>
                            As an independent travel agency, we work with a network of airlines, travel suppliers and
                            reservation systems to help customers compare travel options and secure competitive fares
                            for destinations around the world.
                        </p>

                        <p>
                            Our focus is helping travelers save time, reduce costs and travel with confidence.
                        </p>
                    </div>
                </div>

                <aside class="fha-intro-card">
                    <h3>How we can help</h3>
                    <ul>
                        <li><i class="fa-solid fa-check"></i> Domestic &amp; international flights</li>
                        <li><i class="fa-solid fa-check"></i> Multi-city itineraries</li>
                        <li><i class="fa-solid fa-check"></i> Last-minute arrangements</li>
                        <li><i class="fa-solid fa-check"></i> Family &amp; group travel</li>
                        <li><i class="fa-solid fa-check"></i> Business travel</li>
                        <li><i class="fa-solid fa-check"></i> Travel support &amp; itinerary assistance</li>
                    </ul>
                </aside>
            </div>
        </div>
    </section>


    <section class="fha-section soft">
        <div class="container">
            <span class="fha-eyebrow">TRAVEL SERVICES</span>
            <h2 class="fha-title">More ways to plan your journey</h2>
            <p class="fha-copy">Support for leisure, business, family and complex travel needs.</p>

            <div class="fha-services-grid">
                @foreach([
                    ['fa-plane','Flight Bookings','Domestic and international flight options.'],
                    ['fa-route','Multi-City Travel','Help with more complex itineraries and multiple destinations.'],
                    ['fa-users','Family & Group Travel','Travel planning support for families and groups.'],
                    ['fa-briefcase','Business Travel','Domestic and international travel support for professionals.'],
                    ['fa-hotel','Hotel Bookings','Accommodation support where available.'],
                    ['fa-car','Car Rentals','Rental car options for destination travel.'],
                    ['fa-ship','Cruise Reservations','Cruise booking assistance where available.'],
                    ['fa-headset','Travel Assistance','Help with booking questions and itinerary support.'],
                ] as $svc)
                <div class="fha-service">
                    <i class="fa-solid {{ $svc[0] }}"></i>
                    <b>{{ $svc[1] }}</b>
                    <span>{{ $svc[2] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </section>


    <section class="fha-section fha-difference">
        <div class="container">
            <span class="fha-eyebrow">WHY FARES HUNT</span>
            <h2 class="fha-title">Technology backed by personalized support</h2>
            <p class="fha-copy">
                Unlike fully automated booking platforms, Fares Hunt combines travel technology with customer assistance.
            </p>

            <div class="fha-difference-grid">
                @foreach([
                    ['fa-tags','Competitive Airfare','Access to competitive travel options through our supplier and reservation network.'],
                    ['fa-user-check','Personalized Assistance','Real support for travel questions, booking needs and itinerary changes when permitted.'],
                    ['fa-receipt','Transparent Pricing','Clear fare information and a straightforward booking experience.'],
                    ['fa-lock','Secure Payments','Payment information handled through secure booking processes.'],
                    ['fa-headset','Dedicated Support','Assistance before, during and after travel whenever possible.'],
                    ['fa-compass','Travel Expertise','Support for leisure, family, group and business travel needs.'],
                ] as $item)
                <div class="fha-diff-card">
                    <i class="fa-solid {{ $item[0] }}"></i>
                    <h3>{{ $item[1] }}</h3>
                    <p>{{ $item[2] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>


    <section class="fha-section fha-business">
        <div class="container">
            <div class="fha-business-grid">
                <div>
                    <span class="fha-eyebrow">BUSINESS &amp; CORPORATE TRAVEL</span>
                    <h2 class="fha-title">Travel support that keeps business moving</h2>
                    <p class="fha-copy">
                        Fares Hunt assists business travelers, corporate clients and organizations with domestic and
                        international travel arrangements. Whether you are attending a business meeting, corporate
                        conference, trade event or managing travel for a team, our goal is to help keep your plans on track.
                    </p>
                </div>

                <div class="fha-business-points">
                    <div><i class="fa-solid fa-briefcase"></i> Individual business travel</div>
                    <div><i class="fa-solid fa-users"></i> Team travel assistance</div>
                    <div><i class="fa-solid fa-building"></i> Conferences &amp; corporate events</div>
                    <div><i class="fa-solid fa-earth-americas"></i> Domestic &amp; international trips</div>
                </div>
            </div>
        </div>
    </section>


    <section class="fha-section soft">
        <div class="container">
            <div class="fha-mv-grid">

                <div class="fha-mv-card">
                    <div class="fha-mv-icon"><i class="fa-regular fa-eye"></i></div>
                    <span class="fha-eyebrow">OUR VISION</span>
                    <h3>A trusted travel brand for journeys worldwide</h3>
                    <p>
                        Our vision is to help connect people, families and businesses with destinations around the world
                        through affordable travel solutions and exceptional customer care.
                    </p>
                    <p>
                        We believe travel creates opportunities, strengthens relationships and builds unforgettable experiences.
                    </p>
                </div>

                <div class="fha-mv-card">
                    <div class="fha-mv-icon"><i class="fa-solid fa-bullseye"></i></div>
                    <span class="fha-eyebrow">OUR MISSION</span>
                    <h3>Reliable booking services with transparency and care</h3>
                    <p>
                        Our mission is to provide reliable travel booking services while maintaining transparency,
                        integrity and customer satisfaction.
                    </p>
                    <p>
                        We work every day to help travelers find better options, access competitive pricing,
                        receive professional support and enjoy a smoother booking experience.
                    </p>
                </div>

            </div>
        </div>
    </section>


    <section class="fha-section">
        <div class="container">
            <div class="fha-support-grid">
                <div class="fha-support-card">
                    <span class="fha-eyebrow">CUSTOMER SUPPORT</span>
                    <h3>Support you can count on</h3>
                    <p>
                        Travel plans can change unexpectedly. Our customer support team assists with travel-related
                        questions, booking inquiries and reservation support whenever possible. We understand that
                        travel can be time-sensitive and work to provide prompt, professional assistance.
                    </p>
                </div>

                <div class="fha-support-card">
                    <span class="fha-eyebrow">SERVING TRAVELERS WORLDWIDE</span>
                    <h3>From the United States to destinations around the globe</h3>
                    <p>
                        From domestic travel within the United States to international journeys across Europe, Asia,
                        the Middle East, Africa, Australia, South America and beyond, we help travelers reach the places
                        that matter most.
                    </p>
                </div>
            </div>
        </div>
    </section>


    <section class="fha-section soft">
        <div class="container">
            <div class="fha-transparency">
                <div class="fha-transparency-head">
                    <i class="fa-solid fa-circle-info"></i>
                    <h3>Travel Supplier Relationships</h3>
                </div>

                <p>
                    Fares Hunt acts as an independent travel agency and booking intermediary. We do not own, operate,
                    manage or control airlines, hotels, cruise lines, car rental companies, tour operators or other
                    travel suppliers. Travel services are provided by independent third-party suppliers and are subject
                    to the respective supplier's terms, conditions, rules, availability and operating policies.
                </p>
            </div>
        </div>
    </section>


    <section class="fha-section fha-corp">
        <div class="container">
            <span class="fha-eyebrow" style="text-align:center">CORPORATE INFORMATION</span>
            <h2 class="fha-title" style="text-align:center">Get in touch with Fares Hunt</h2>

            <div class="fha-corp-grid">
                <div class="fha-corp-card">
                    <i class="fa-solid fa-phone"></i>
                    <b>Customer Support</b>
                    <a href="tel:+18887170346">+1-888-717-0346</a>
                    <span>24 hours a day, 7 days a week</span>
                </div>

                <div class="fha-corp-card">
                    <i class="fa-brands fa-whatsapp"></i>
                    <b>WhatsApp</b>
                    <a href="https://wa.me/13072638435" target="_blank" rel="noopener">+1 (307) 263-8435</a>
                    <span>Travel assistance via WhatsApp</span>
                </div>

                <div class="fha-corp-card">
                    <i class="fa-regular fa-envelope"></i>
                    <b>Email</b>
                    <a href="mailto:info@fareshunt.com">info@fareshunt.com</a>
                </div>

                <div class="fha-corp-card">
                    <i class="fa-solid fa-location-dot"></i>
                    <b>Business Address</b>
                    <span>1603 Capitol Ave Suite 413G-2599</span>
                    <span>Cheyenne, Wyoming 82001, United States</span>
                </div>
            </div>

            <div class="fha-company-meta">
                <div><b>Company Name:</b> Aeronza Travel Group LLC</div>
                <div><b>Brand Name:</b> Fares Hunt</div>
            </div>
        </div>
    </section>


    <section class="fha-closing">
        <div class="container">
            <h2>Thank You for Choosing Fares Hunt</h2>
            <p>
                Whether you are traveling for business, leisure, family, adventure or a special occasion,
                we look forward to helping you create memorable journeys with confidence and peace of mind.
            </p>
            <strong>Making Travel Simpler, Smarter, and More Affordable.</strong>
        </div>
    </section>

</div>

@else

<div class="fh-content-v5">

    <section
        class="fh-v5-hero"
        @if($detail->banner)
            style="background-image:
                linear-gradient(90deg,rgba(7,24,45,.94),rgba(7,30,55,.82)),
                url('{{ $detail->banner }}');"
        @endif
    >
        <div class="container">
            <div class="fh-v5-hero-inner">
                <span class="fh-v5-kicker">{{ $pageType }}</span>
                <h1>{{ $detail->title }}</h1>

                @if(!empty($detail->seo_description))
                    <p>{{ $detail->seo_description }}</p>
                @endif

                <div class="fh-v5-trust">
                    <span><i class="fa-solid fa-shield-halved"></i> Secure booking support</span>
                    <span><i class="fa-solid fa-globe"></i> Worldwide travel options</span>
                    <span><i class="fa-solid fa-headset"></i> Human assistance</span>
                </div>
            </div>
        </div>
    </section>

    <section class="fh-v5-main">
        <div class="container">
            <article class="fh-v5-article fh-v5-prose">
                {!! $content !!}
            </article>
        </div>
    </section>

</div>

@endif

@endsection