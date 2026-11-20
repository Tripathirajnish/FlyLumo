
<style id="fareshunt-v10-listing-journey-bg">
.tnb-listing-results-section{position:relative;overflow:hidden!important;background:linear-gradient(180deg,#f5f7fb 0%,#f7f9fc 100%)!important}
.tnb-listing-results-section:before{content:"✈";position:absolute;right:3vw;top:120px;font-size:180px;color:rgba(12,49,91,.025);transform:rotate(-10deg);pointer-events:none}.tnb-listing-results-section>.container{position:relative;z-index:1}
@media(max-width:760px){.tnb-listing-results-section:before{display:none}}
</style>
<style id="fareshunt-listing-mobile-responsive-v1">
@media (max-width:900px){

    body{
        overflow-x:hidden!important;
    }

    .tnb-results-page,
    .tnb-results-layout,
    .tnb-results-main,
    .tnb-results-content{
        max-width:100%!important;
        min-width:0!important;
        overflow-x:hidden!important;
    }

    .tnb-results-layout{
        display:block!important;
    }

    /* Filter/sidebar becomes top drawer/card on mobile */
    .tnb-results-sidebar,
    .tnb-filter-sidebar,
    aside.tnb-filters{
        width:100%!important;
        max-width:100%!important;
        position:static!important;
        margin:0 0 12px!important;
    }

    .tnb-results-toolbar,
    .tnb-results-sort,
    .tnb-search-summary{
        width:100%!important;
        max-width:100%!important;
        flex-wrap:wrap!important;
        gap:8px!important;
    }

    .tnb-search-summary{
        padding:12px!important;
        border-radius:14px!important;
    }

    .tnb-search-summary > div{
        min-width:0!important;
        width:100%!important;
    }

    .tnb-summary-date{
        justify-content:space-between!important;
    }

    /* Result card */
    .tnb-flight-card,
    .flight-list-card,
    .search_result_box,
    .flight_result{
        width:100%!important;
        max-width:100%!important;
        min-width:0!important;
        overflow:hidden!important;
        border-radius:16px!important;
    }

    .tnb-flight-card .row,
    .flight-list-card .row,
    .search_result_box .row{
        margin-left:0!important;
        margin-right:0!important;
    }

    /* Price / select area drops below itinerary */
    .tnb-flight-card .tnb-price-col,
    .tnb-flight-card .price-col,
    .tnb-flight-card .flight-price,
    .search_result_box .price-part{
        width:100%!important;
        max-width:100%!important;
        border-left:0!important;
        border-top:1px solid #e6edf5!important;
        padding:12px!important;
        text-align:center!important;
    }

    .tnb-flight-card .tnb-price-col .btn,
    .tnb-flight-card .price-col .btn,
    .search_result_box .price-part .btn,
    .tnb-select-btn{
        width:100%!important;
        min-height:48px!important;
    }

    /* Standard One Way / Round Trip leg block */
    .tnb-flight-card .col,
    .tnb-flight-card [class*="col-"],
    .search_result_box [class*="col-"]{
        min-width:0!important;
    }

    /* Multi-City cards */
    .tnb-mc-leg-main{
        grid-template-columns:1fr 1fr!important;
        gap:10px!important;
    }

    .tnb-mc-airline{
        grid-column:1/-1!important;
    }

    .tnb-mc-route-line{
        grid-column:1/-1!important;
        grid-row:auto!important;
        order:3!important;
        padding-top:4px!important;
    }

    .tnb-mc-time{
        text-align:left!important;
    }

    .tnb-mc-arrival{
        text-align:right!important;
    }

    .tnb-mc-time strong{
        font-size:16px!important;
    }

    .tnb-mc-leg-meta{
        flex-wrap:wrap!important;
    }

    /* Expanded flight detail */
    .tnb-inline-details{
        padding:10px!important;
        overflow:hidden!important;
    }

    .tnb-detail-journey-head{
        flex-wrap:wrap!important;
        gap:6px!important;
        padding:10px 0!important;
    }

    .tnb-segment-row,
    .flight-detail-row,
    .tnb-flight-segment{
        display:grid!important;
        grid-template-columns:1fr 1fr!important;
        gap:10px!important;
        min-width:0!important;
    }

    /* Compare fares / flexible dates */
    .tnb-fare-compare{
        overflow-x:auto!important;
        -webkit-overflow-scrolling:touch!important;
    }

    .tnb-fare-compare table{
        min-width:720px!important;
    }

    .tnb-price-calendar{
        overflow-x:auto!important;
        -webkit-overflow-scrolling:touch!important;
    }

    .tnb-price-calendar > div,
    .tnb-price-calendar .tnb-calendar-strip{
        min-width:max-content!important;
    }

    /* Results-page search form */
    #form2 .fhx-multicity-editor,
    #form2 .fhx-grid,
    #form2 .fhx-grid-multicity{
        width:100%!important;
        max-width:100%!important;
    }

    #form2 .fhx-grid,
    #form2 .fhx-grid-multicity{
        display:grid!important;
        grid-template-columns:1fr!important;
        gap:9px!important;
    }

    #form2 .fhx-field,
    #form2 .fhx-input,
    #form2 .fhx-search-btn{
        width:100%!important;
        min-width:0!important;
    }

    #form2 .fhx-multi-leg{
        display:grid!important;
        grid-template-columns:1fr!important;
        gap:9px!important;
        position:relative!important;
        padding:12px!important;
    }

    #form2 .fhx-multi-leg-title{
        height:auto!important;
        padding-right:44px!important;
    }

    #form2 .fhx-multi-remove{
        position:absolute!important;
        right:10px!important;
        top:10px!important;
        width:40px!important;
        height:40px!important;
    }

    /* Prevent any long airline/route text from causing horizontal scroll */
    .tnb-results-page *,
    .tnb-results-layout *{
        min-width:0;
        overflow-wrap:anywhere;
    }
}

@media (max-width:600px){
    .tnb-mc-leg-main{
        grid-template-columns:1fr 1fr!important;
    }

    .tnb-mc-airline{
        grid-column:1/-1!important;
    }

    .tnb-inline-details{
        padding:8px!important;
    }

    .tnb-segment-row,
    .flight-detail-row,
    .tnb-flight-segment{
        grid-template-columns:1fr!important;
    }

    .tnb-search-summary{
        padding:10px!important;
    }
}
</style>

@extends('layout')

@section('header-seo')
    <meta name="robots" content="noindex, nofollow">
<title>Flight Listing - Search</title>
    <meta name="description" content="Flight Listing - Search">
    <style>      
        .autocomplete-items {
        position: absolute;
        border-bottom: none;
        border-top: none;
        z-index: 99;
        top: 84%;
        width: 420px;
        border:1px solid #d4d4d4;
        }

        .autocomplete-items div {
        padding: 5px;
        cursor: pointer;
        background-color: #fff; 
        border-bottom: 1px solid #d4d4d4;
        }

        .autocomplete-items div:hover {
        background-color: #e9e9e9; 
        }

        .autocomplete-active {
        background-color: DodgerBlue !important; 
        color: #ffffff; 
        }

        /* Premium OTA result experience */
        body{background:#f3f6fa}
        .gray-simple{background:#eef3f8!important}
        .filter-searchBar-head{border-radius:14px 14px 0 0!important;background:#fff!important;border:1px solid #dfe6ef!important}
        .filter-searchBar-body{background:#fff;border:1px solid #dfe6ef;border-top:0;border-radius:0 0 14px 14px;padding:14px}
        .result-step .flights-list-item{border:1px solid #dfe6ef!important;border-radius:14px!important;padding:0!important;overflow:hidden;box-shadow:0 4px 12px rgba(12,35,62,.045)}
        .result-step .flights-list-item:hover{border-color:#9fb8d4!important;box-shadow:0 10px 26px rgba(12,35,62,.08)}
        .result-step .row.gy-4{margin:0!important}
        .result-step .row.gy-4>.col{padding:0!important}
        .tnb-itin-row{display:grid;grid-template-columns:minmax(165px,1.15fr) 78px minmax(150px,1fr) 78px 88px;gap:12px;align-items:center;padding:18px}
        .tnb-return-row{border-top:1px dashed #dce4ed}
        .tnb-airline{display:flex;align-items:center;gap:10px;min-width:0}.tnb-airline img{width:38px;height:38px;object-fit:contain}.tnb-airline strong,.tnb-airline span{display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.tnb-airline strong{font-size:13px}.tnb-airline span{font-size:10px;color:#7b899a;margin-top:3px}
        .tnb-time{text-align:center}.tnb-time strong{display:block;font-size:18px;color:#0a1d34}.tnb-time span{font-size:11px;color:#708198;font-weight:700}
        .tnb-path{text-align:center;min-width:0}.tnb-path>span{font-size:10px;color:#75859b}.tnb-path-line{height:1px;background:#9fb0c3;margin:7px 0;position:relative}.tnb-path-line:before,.tnb-path-line:after{content:'';position:absolute;width:5px;height:5px;border:1px solid #9fb0c3;background:#fff;border-radius:50%;top:-2px}.tnb-path-line:before{left:0}.tnb-path-line:after{right:0}.tnb-path-line i{position:absolute;left:50%;top:-7px;transform:translateX(-50%);background:#fff;padding:0 5px;color:#5078a8;font-size:11px}.tnb-path b{font-size:10px;font-weight:700}.tnb-path .direct{color:#07966a}.tnb-path .stop{color:#e23662}.tnb-path small{display:block;font-size:9px;color:#8a97a9;margin-top:2px}
        .tnb-cabin span,.tnb-cabin strong{display:block}.tnb-cabin span{font-size:9px;color:#8492a4;text-transform:uppercase}.tnb-cabin strong{font-size:11px}
        .tnb-price-col{border-left:1px solid #e0e6ee;padding:0!important;align-self:stretch;min-width:180px}.tnb-price-box{height:100%;padding:17px;display:flex;flex-direction:column;justify-content:center;align-items:flex-end}.tnb-price-box small{font-size:9px;color:#7f8c9d}.tnb-price-box strong{font-size:25px;color:#081d36;line-height:1.15}.tnb-price-box span{font-size:9px;color:#7e8b9b}.tnb-old-price{text-decoration:line-through!important;font-size:10px!important}.tnb-select-btn{width:100%;margin-top:12px;border-radius:8px!important;background:#09233f!important;border-color:#09233f!important}
        .result-step .accordion{padding:0 18px 14px}.result-step .accordion-button{color:#31587e!important;font-weight:700}
        .tnb-sortbar{background:#fff;border:1px solid #dfe6ef;border-radius:14px;display:grid;grid-template-columns:repeat(3,1fr);overflow:hidden;margin:12px 0 16px}.tnb-sortitem{padding:12px 16px;border:0;background:#fff;text-align:left}.tnb-sortitem+ .tnb-sortitem{border-left:1px solid #e3e8ef}.tnb-sortitem.active{background:#0a2949;color:#fff}.tnb-sortitem b{display:block;font-size:13px}.tnb-sortitem span{font-size:10px;opacity:.72}
        .tnb-search-summary{background:#0a2949;color:#fff;border-radius:14px;padding:14px 18px;display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:14px}.tnb-search-summary strong{font-size:16px}.tnb-search-summary span{font-size:11px;color:#c5d2e0}
        .tnb-results-title{font-weight:800;color:#0a1d34}
        @media(max-width:991px){.tnb-itin-row{grid-template-columns:1fr 70px 1fr}.tnb-airline{grid-column:1/-1}.tnb-cabin{display:none}.tnb-price-col{border-left:0;border-top:1px solid #e0e6ee}.tnb-price-box{align-items:stretch;text-align:right}.filter-searchBar-body{display:none}}
        @media(max-width:575px){.tnb-itin-row{grid-template-columns:62px 1fr 62px;padding:14px 10px}.tnb-airline{grid-column:1/-1}.tnb-time strong{font-size:15px}.tnb-sortbar{grid-template-columns:1fr}.tnb-sortitem+ .tnb-sortitem{border-left:0;border-top:1px solid #e3e8ef}}

        /* =========================================================
           V8 — PREMIUM RESULTS EXTRAS
           Airline fare comparison + real-date calendar + detail polish
           ========================================================= */


        /* =========================================================
           FINAL APPROVED PREMIUM FARE SECTION
           - Uses only fares from the current live search result
           - Nearby dates trigger a fresh search; no estimated prices
           ========================================================= */
        .tnb-fare-compare{
            margin-bottom:16px;
            overflow:hidden;
            border:1px solid #dce5ef;
            border-radius:20px;
            background:#fff;
            box-shadow:0 12px 34px rgba(13,38,67,.07);
        }
        .tnb-fare-overview-head{
            display:flex;
            align-items:flex-start;
            justify-content:space-between;
            gap:16px;
            padding:16px 18px 14px;
            border-bottom:1px solid #edf1f5;
        }
        .tnb-fare-overview-head h3{
            margin:0;
            color:#102d4d;
            font-size:15px;
            font-weight:900;
            letter-spacing:.01em;
        }
        .tnb-fare-overview-head p{
            margin:4px 0 0;
            color:#7f8ea2;
            font-size:10px;
        }
        .tnb-compare-link{
            border:0;
            background:transparent;
            color:#1458c9;
            font-size:10px;
            font-weight:850;
            cursor:pointer;
            white-space:nowrap;
        }
        .tnb-fare-compare-scroll{overflow-x:auto}
        .tnb-fare-matrix{
            display:grid;
            min-width:940px;
            grid-template-columns:150px 170px repeat(var(--airline-count),minmax(155px,1fr));
        }
        .tnb-fare-cell{
            min-height:62px;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:10px 12px;
            border-right:1px solid #e8eef4;
            border-bottom:1px solid #e8eef4;
            background:#fff;
            color:#18314f;
            text-align:center;
        }
        .tnb-fare-label{
            justify-content:flex-start;
            padding-left:16px;
            background:#fbfcfe;
            color:#263e5b;
            font-size:10px;
            font-weight:850;
        }
        .tnb-fare-head{
            min-height:88px;
            flex-direction:column;
            gap:4px;
            background:#fbfcfe;
        }
        .tnb-fare-head img{
            width:42px;
            height:28px;
            object-fit:contain;
        }
        .tnb-fare-head strong{
            max-width:135px;
            overflow:hidden;
            color:#1b3553;
            font-size:10px;
            font-weight:800;
            text-overflow:ellipsis;
            white-space:nowrap;
        }
        .tnb-airline-code{
            display:inline-flex;
            padding:2px 6px;
            border-radius:6px;
            background:#edf3ff;
            color:#2457ba;
            font-size:8px;
            font-weight:900;
        }
        .tnb-best-overall{
            display:flex;
            flex-direction:column;
            align-items:center;
            justify-content:center;
            gap:2px;
            width:100%;
            min-height:45px;
        }
        .tnb-best-overall strong{
            color:#0b213c;
            font-size:15px;
            font-weight:900;
        }
        .tnb-best-overall small{
            color:#6f8096;
            font-size:9px;
            font-weight:700;
        }
        .tnb-best-overall .best-chip{
            display:inline-flex;
            align-items:center;
            margin-left:5px;
            padding:2px 6px;
            border-radius:999px;
            background:#e4183f;
            color:#fff;
            font-size:7px;
            font-weight:900;
            text-transform:uppercase;
        }
        .tnb-stop-best{
            display:block;
            margin-top:3px;
            color:#16a05d;
            font-size:8px;
            font-weight:850;
        }
        .tnb-fare-filter{
            width:100%;
            min-height:48px;
            display:flex;
            flex-direction:column;
            align-items:center;
            justify-content:center;
            gap:2px;
            padding:6px;
            border:1px solid transparent;
            border-radius:10px;
            background:#fff;
            cursor:pointer;
            transition:.16s ease;
        }
        .tnb-fare-filter:hover{
            border-color:#c9d7e6;
            background:#f7faff;
            transform:translateY(-1px);
        }
        .tnb-fare-filter.active{
            border-color:#1b4e81;
            background:#eef5fc;
        }
        .tnb-fare-price{
            color:#0b213c;
            font-size:15px;
            font-weight:900;
        }
        .tnb-fare-action{
            color:#135bd2;
            font-size:8px;
            font-weight:800;
        }
        .tnb-best-price .tnb-fare-price{color:#159353}
        .tnb-best-price .tnb-fare-action{color:#159353}
        .tnb-fare-empty{
            color:#9faebe;
            font-size:10px;
            font-weight:700;
        }
        .tnb-fare-reset{
            border:0;
            background:transparent;
            color:#1458c9;
            font-size:10px;
            font-weight:850;
            cursor:pointer;
        }
        .tnb-fare-note{
            display:flex;
            align-items:flex-start;
            gap:8px;
            margin:0;
            padding:11px 14px;
            background:#edf5ff;
            border-top:1px solid #dbe8f7;
            color:#627891;
            font-size:9px;
            line-height:1.55;
        }
        .tnb-fare-note i{margin-top:2px;color:#2166d1}

        .tnb-price-calendar{
            margin:0 0 16px;
            overflow:hidden;
            border:1px solid #dce5ef;
            border-radius:20px;
            background:#fff;
            box-shadow:0 10px 28px rgba(13,38,67,.055);
        }
        .tnb-price-calendar-head{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:14px;
            padding:14px 17px;
            border-bottom:1px solid #edf1f5;
        }
        .tnb-price-calendar-title{
            display:flex;
            align-items:center;
            gap:12px;
        }
        .tnb-price-calendar-title .label{
            color:#1458c9;
            font-size:10px;
            font-weight:900;
            text-transform:uppercase;
        }
        .tnb-price-calendar-title strong{
            color:#102d4d;
            font-size:15px;
            font-weight:900;
        }
        .tnb-price-calendar-head-right{
            display:flex;
            align-items:center;
            gap:10px;
            color:#7b8ca1;
            font-size:9px;
        }
        .tnb-date-strip{
            display:flex;
            gap:8px;
            overflow-x:auto;
            padding:14px 16px 15px;
            scrollbar-width:thin;
        }
        .tnb-date-pill{
            flex:0 0 105px;
            min-height:104px;
            display:flex;
            flex-direction:column;
            align-items:center;
            justify-content:center;
            gap:3px;
            padding:9px 6px;
            border:1px solid #dce5ef;
            border-radius:15px;
            background:#fff;
            color:#405873;
            cursor:pointer;
            transition:.17s ease;
        }
        .tnb-date-pill:hover{
            transform:translateY(-2px);
            border-color:#b9cadb;
            box-shadow:0 8px 18px rgba(15,40,69,.08);
        }
        .tnb-date-pill small{
            color:#7f8ea1;
            font-size:9px;
            font-weight:750;
        }
        .tnb-date-pill b{
            color:#17314f;
            font-size:11px;
            font-weight:850;
        }
        .tnb-date-pill em{
            color:#0d213b;
            font-size:15px;
            font-style:normal;
            font-weight:900;
        }
        .tnb-date-pill .check{
            color:#1458c9;
            font-size:8px;
            font-weight:850;
        }
        .tnb-date-pill.active{
            border-color:#0f2f58;
            background:linear-gradient(180deg,#102f59,#0b2445);
            color:#fff;
            box-shadow:0 12px 25px rgba(11,36,69,.20);
        }
        .tnb-date-pill.active small,
        .tnb-date-pill.active b,
        .tnb-date-pill.active em,
        .tnb-date-pill.active .check{color:#fff}
        .tnb-date-pill.active .selected-label{
            margin-top:2px;
            color:#fff;
            font-size:7px;
            font-weight:900;
            text-transform:uppercase;
        }

        .tnb-search-summary{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:20px;
            margin:0 0 12px;
            padding:14px 18px;
            border-radius:15px;
            background:linear-gradient(135deg,#0b2c52,#123b6a);
            color:#fff;
            box-shadow:0 12px 28px rgba(13,44,76,.13);
        }
        .tnb-search-summary strong{
            display:block;
            font-size:14px;
            font-weight:900;
        }
        .tnb-search-summary span{
            display:block;
            margin-top:4px;
            color:#d7e4f0;
            font-size:9px;
        }
        .tnb-summary-date{
            display:flex;
            align-items:center;
            gap:8px;
            white-space:nowrap;
            color:#fff;
            font-size:10px;
            font-weight:750;
        }
        .tnb-change-search{
            margin-left:10px;
            padding:7px 12px;
            border:1px solid rgba(255,255,255,.75);
            border-radius:999px;
            background:transparent;
            color:#fff;
            font-size:9px;
            font-weight:850;
            cursor:pointer;
        }

        .tnb-results-toolbar{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:14px;
            margin-bottom:8px;
        }
        .tnb-results-toolbar-left{
            display:flex;
            align-items:baseline;
            gap:10px;
        }
        .tnb-results-title{
            margin:0;
            color:#102d4d;
            font-size:15px;
            font-weight:900;
        }
        .tnb-results-toolbar small{
            color:#8998aa;
            font-size:9px;
        }
        .tnb-sortbar{
            display:grid;
            grid-template-columns:repeat(3,1fr);
            overflow:hidden;
            margin-bottom:12px;
            border:1px solid #dce5ef;
            border-radius:15px;
            background:#fff;
        }
        .tnb-sortitem{
            min-height:70px;
            display:flex;
            flex-direction:column;
            align-items:flex-start;
            justify-content:center;
            gap:3px;
            padding:11px 16px;
            border:0;
            border-right:1px solid #e8eef4;
            background:#fff;
            color:#18314f;
            text-align:left;
            cursor:pointer;
        }
        .tnb-sortitem:last-child{border-right:0}
        .tnb-sortitem b{font-size:12px;font-weight:900}
        .tnb-sortitem span{color:#7f8ea1;font-size:9px}
        .tnb-sortitem.active{
            background:#0d315a;
            color:#fff;
        }
        .tnb-sortitem.active span{color:#d9e5ef}
        .tnb-filter-state{
            display:none;
            align-items:center;
            gap:7px;
            margin-bottom:10px;
            padding:9px 11px;
            border:1px solid #dce5ef;
            border-radius:11px;
            background:#f8fafc;
            color:#536a83;
            font-size:9px;
        }
        .tnb-filter-state.show{display:flex}
        .tnb-filter-state button{
            margin-left:auto;
            border:0;
            background:transparent;
            color:#d7193f;
            font-size:9px;
            font-weight:850;
        }

        @media(max-width:767px){
            .tnb-fare-overview-head,.tnb-price-calendar-head,.tnb-search-summary,.tnb-results-toolbar{
                align-items:flex-start;
                flex-direction:column;
            }
            .tnb-search-summary .text-end{text-align:left!important}
            .tnb-summary-date{white-space:normal}
            .tnb-sortitem{padding:9px 10px}
        }


        /* =========================================================
           V13 — CORRECT LISTING PAGE HIERARCHY
           Fare overview is full width. Filters belong to results only.
           ========================================================= */
        .tnb-listing-results-section{
            padding-top:22px!important;
            padding-bottom:44px!important;
        }
        .tnb-premium-top{
            width:100%;
            margin-bottom:18px;
        }
        .tnb-premium-top .tnb-fare-compare,
        .tnb-premium-top .tnb-price-calendar,
        .tnb-premium-top .tnb-search-summary,
        .tnb-premium-top .tnb-sortbar{
            width:100%;
        }
        .tnb-results-layout{
            align-items:flex-start!important;
        }
        .tnb-results-layout > .col-xl-3,
        .tnb-results-layout > .col-lg-4{
            align-self:stretch;
        }
        .tnb-results-layout .filter-searchBar{
            position:sticky;
            top:92px;
            z-index:25;
            max-height:calc(100vh - 112px);
            display:flex;
            flex-direction:column;
            overflow:hidden;
            border:1px solid #dce5ef;
            border-radius:18px!important;
            background:#fff;
            box-shadow:0 10px 28px rgba(13,38,67,.065);
        }
        .tnb-results-layout .filter-searchBar-head{
            flex:0 0 auto;
            border:0!important;
            border-bottom:1px solid #e7edf4!important;
            border-radius:18px 18px 0 0!important;
        }
        .tnb-results-layout .filter-searchBar-body{
            flex:1 1 auto;
            overflow-y:auto;
            overscroll-behavior:contain;
            border:0!important;
            border-radius:0 0 18px 18px!important;
            scrollbar-width:thin;
        }
        .tnb-results-layout .filter-searchBar-body::-webkit-scrollbar{width:5px}
        .tnb-results-layout .filter-searchBar-body::-webkit-scrollbar-thumb{
            border-radius:999px;
            background:#c8d3df;
        }
        .tnb-flight-results-column{
            margin-top:0!important;
        }
        .tnb-flight-results-column > .row{
            margin-top:0!important;
        }
        .tnb-flight-results-column .result-step:first-child{
            margin-top:0!important;
        }

        /* Keep the top controls visually connected and spacious. */
        .tnb-premium-top .tnb-fare-matrix{
            min-width:1000px;
        }
        .tnb-premium-top .tnb-date-strip{
            padding-bottom:13px;
        }

        @media(max-width:1199px){
            .tnb-results-layout .filter-searchBar{
                top:78px;
            }
        }
        @media(max-width:991px){
            .tnb-results-layout .filter-searchBar{
                position:static;
                max-height:none;
                overflow:visible;
            }
            .tnb-results-layout .filter-searchBar-body{
                max-height:none;
                overflow:visible;
            }
        }


        /* =========================================================
           V14 — FINAL FILTERS + FARE MATRIX + FLIGHT CARDS
           ========================================================= */

        /* --- Fare matrix: compact "Show All Fares" style --- */
        .tnb-fare-overview-head{
            padding:14px 16px 12px!important;
        }
        .tnb-fare-matrix{
            grid-template-columns:135px repeat(var(--airline-count),minmax(150px,1fr))!important;
            min-width:820px!important;
        }
        .tnb-fare-head{
            min-height:78px!important;
            gap:3px!important;
        }
        .tnb-fare-cell{
            min-height:54px!important;
            padding:8px 9px!important;
        }
        .tnb-fare-head img{
            width:38px!important;
            height:27px!important;
        }
        .tnb-fare-label{
            padding-left:14px!important;
            font-size:10px!important;
        }
        .tnb-fare-filter{
            min-height:41px!important;
        }
        .tnb-fare-price{
            font-size:14px!important;
        }
        .tnb-fare-action{
            font-size:7.5px!important;
        }
        .tnb-show-all-fares{
            width:100%;
            border:0;
            background:transparent;
            color:#17314f;
            font-size:10px;
            font-weight:900;
            text-align:left;
            cursor:pointer;
        }

        /* --- Sidebar / filters --- */
        .tnb-results-layout{
            --filter-width:310px;
        }
        .tnb-results-layout > .tnb-filter-column{
            width:var(--filter-width);
            flex:0 0 var(--filter-width);
        }
        .tnb-flight-results-column{
            width:calc(100% - var(--filter-width));
            flex:0 0 calc(100% - var(--filter-width));
        }
        .tnb-results-layout .filter-searchBar{
            top:86px!important;
            border-radius:20px!important;
            box-shadow:0 12px 32px rgba(13,38,67,.075)!important;
        }
        .tnb-results-layout .filter-searchBar-head{
            background:#fff!important;
        }
        .tnb-filter-title{
            margin:0;
            color:#162d49;
            font-size:15px;
            font-weight:850;
        }
        .tnb-filter-reset{
            border:0;
            background:transparent;
            color:#d7193f;
            font-size:10px;
            font-weight:800;
            cursor:pointer;
        }
        .tnb-extra-filter{
            padding:14px 4px 16px;
            border-bottom:1px solid #e6edf4;
        }
        .tnb-extra-filter:last-child{border-bottom:0}
        .tnb-extra-filter h6{
            margin:0 0 10px;
            color:#263b55;
            font-size:10px;
            font-weight:900;
            letter-spacing:.04em;
            text-transform:uppercase;
        }
        .tnb-range-top,
        .tnb-range-bottom{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:10px;
            color:#71839a;
            font-size:10px;
        }
        .tnb-range-top strong{
            color:#d7193f;
            font-size:11px;
            font-weight:850;
        }
        .tnb-range{
            width:100%;
            margin:10px 0 7px;
            accent-color:#d7193f;
        }
        .tnb-time-chip-grid{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:7px;
        }
        .tnb-time-chip{
            display:flex;
            align-items:center;
            gap:7px;
            padding:9px 10px;
            border:1px solid #dbe4ee;
            border-radius:13px;
            color:#334c68;
            font-size:10px;
            cursor:pointer;
        }
        .tnb-time-chip input{margin:0}
        .tnb-time-chip small{
            display:block;
            color:#8796a8;
            font-size:8px;
        }
        .tnb-toggle-row{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:14px;
            min-height:54px;
            padding:10px 0;
            margin:0;
            cursor:pointer;
        }
        .tnb-toggle-row + .tnb-toggle-row{
            border-top:1px solid #e8eef5;
        }
        .tnb-toggle-copy{
            min-width:0;
            flex:1 1 auto;
        }
        .tnb-toggle-copy strong{
            display:block;
            color:#183552;
            font-size:11px;
            line-height:1.25;
            font-weight:850;
        }
        .tnb-toggle-copy span{
            display:block;
            margin-top:4px;
            color:#7e91a8;
            font-size:8px;
            line-height:1.35;
        }
        .tnb-switch{
            appearance:none;
            -webkit-appearance:none;
            flex:0 0 38px;
            width:38px!important;
            height:22px!important;
            margin:0!important;
            padding:0!important;
            position:relative;
            border:1px solid #d7e0ea!important;
            border-radius:999px!important;
            outline:0!important;
            background:#edf2f7!important;
            cursor:pointer;
            transition:background .18s ease,border-color .18s ease,box-shadow .18s ease;
            box-shadow:inset 0 0 0 1px rgba(255,255,255,.35);
        }
        .tnb-switch:before{
            content:"";
            position:absolute;
            width:16px;
            height:16px;
            top:2px;
            left:2px;
            border-radius:50%;
            background:#fff;
            box-shadow:0 2px 6px rgba(20,48,78,.18);
            transition:transform .18s ease;
        }
        .tnb-switch:hover{
            border-color:#bdcad8!important;
        }
        .tnb-switch:focus-visible{
            box-shadow:0 0 0 3px rgba(215,25,63,.12)!important;
        }
        .tnb-switch:checked{
            background:#d7193f!important;
            border-color:#d7193f!important;
        }
        .tnb-switch:checked:before{
            transform:translateX(16px);
        }
        .tnb-filter-meta-note{
            margin-top:5px;
            color:#9aa7b5;
            font-size:7.5px;
            line-height:1.4;
        }

        /* Existing component filter content: make it look like supplied reference */
        .tnb-results-layout .filter-searchBar-body{
            padding:0 14px 16px!important;
        }
        .tnb-results-layout .filter-searchBar-body .filter-searchBar-single{
            padding:15px 0!important;
            border-bottom:1px solid #e8eef4!important;
        }
        .tnb-results-layout .filter-searchBar-body h6,
        .tnb-results-layout .filter-searchBar-body .fs-6{
            color:#293f59!important;
            font-size:10px!important;
            font-weight:900!important;
            text-transform:uppercase;
            letter-spacing:.035em;
        }
        .tnb-results-layout .filter-searchBar-body label{
            color:#4a6079;
            font-size:10px;
        }
        .tnb-results-layout .filter-searchBar-body input[type="checkbox"]{
            width:16px;
            height:16px;
            accent-color:#d7193f;
        }

        /* --- Compact premium result card like supplied example --- */
        .tnb-flight-results-column .result-step{
            margin-bottom:14px;
        }
        .result-step .flights-list-item{
            border-radius:18px!important;
            border:1px solid #dce5ef!important;
            box-shadow:0 9px 24px rgba(14,38,68,.06)!important;
        }
        .result-step .flights-list-item:hover{
            border-color:#b8c9da!important;
            box-shadow:0 14px 32px rgba(14,38,68,.095)!important;
        }
        .result-step .tnb-itin-row{
            grid-template-columns:minmax(150px,1.05fr) 92px minmax(130px,.85fr) 92px 72px!important;
            gap:9px!important;
            min-height:94px;
            padding:13px 16px!important;
        }
        .result-step .tnb-return-row{
            border-top:1px solid #e7edf4!important;
        }
        .result-step .tnb-airline img{
            width:34px!important;
            height:34px!important;
        }
        .result-step .tnb-airline strong{font-size:11px!important}
        .result-step .tnb-airline span{font-size:9px!important}
        .result-step .tnb-time strong{
            font-size:17px!important;
            letter-spacing:-.02em;
        }
        .result-step .tnb-time span{
            font-size:9px!important;
        }
        .result-step .tnb-path>span{
            font-size:9px!important;
            font-weight:750;
        }
        .result-step .tnb-path b{
            font-size:9px!important;
        }
        .result-step .tnb-cabin span{font-size:7px!important}
        .result-step .tnb-cabin strong{font-size:9px!important}
        .result-step .tnb-price-col{
            min-width:185px!important;
        }
        .result-step .tnb-price-box{
            padding:15px 16px!important;
            align-items:center!important;
            text-align:center!important;
        }
        .result-step .tnb-price-box strong{
            font-size:25px!important;
        }
        .result-step .tnb-select-btn{
            width:132px!important;
            margin-top:10px!important;
            border-radius:9px!important;
            background:linear-gradient(135deg,#e31b3f,#cc1235)!important;
            border-color:#d7193f!important;
            box-shadow:0 9px 20px rgba(215,25,63,.18);
        }
        .result-step .accordion{
            padding:0!important;
            border-top:1px solid #e7edf4;
        }
        .result-step .accordion-item{
            border:0!important;
        }
        .result-step .accordion-header{
            padding:0!important;
        }
        .result-step .accordion-button{
            min-height:42px;
            padding:0 16px!important;
            background:#fff!important;
            color:#e05a19!important;
            font-size:10px!important;
            font-weight:850!important;
            box-shadow:none!important;
        }
        .result-step .accordion-button:not(.collapsed){
            background:#fbfcfe!important;
        }
        .result-step .accordion-button:after{
            width:12px!important;
            height:12px!important;
            background-size:12px!important;
        }

        /* Full itinerary panel loaded by existing showDetail() */
        .result-step .accordion-body.shadow-wrap{
            padding:0!important;
            border-top:1px solid #edf1f5!important;
            background:#fff!important;
        }
        .result-step .airLines-fullsegment{
            padding:16px 18px 18px!important;
            color:#203752!important;
            font-size:10px!important;
        }
        .result-step .airLines-fullsegment img{
            max-width:38px!important;
            max-height:30px!important;
            object-fit:contain!important;
        }
        .result-step .airLines-fullsegment table{
            width:100%!important;
            border-collapse:collapse!important;
        }
        .result-step .airLines-fullsegment td,
        .result-step .airLines-fullsegment th{
            padding:9px 8px!important;
            border-bottom:1px solid #edf1f5!important;
            vertical-align:middle!important;
        }

        @media(max-width:1199px){
            .tnb-results-layout{
                --filter-width:280px;
            }
        }
        @media(max-width:991px){
            .tnb-results-layout > .tnb-filter-column,
            .tnb-flight-results-column{
                width:100%;
                flex:0 0 100%;
            }
            .tnb-results-layout .filter-searchBar{
                position:static!important;
                max-height:none!important;
            }
        }
        @media(max-width:767px){
            .result-step .tnb-itin-row{
                grid-template-columns:1fr 70px 1fr!important;
            }
            .result-step .tnb-airline{grid-column:1/-1}
            .result-step .tnb-cabin{display:none}
            .result-step .tnb-price-col{
                min-width:0!important;
                border-left:0!important;
                border-top:1px solid #e7edf4!important;
            }
            .tnb-time-chip-grid{grid-template-columns:1fr}
        }


        /* =========================================================
           V16 ACTUAL FIX — based on the applied-page screenshots
           ========================================================= */

        /* FILTERS: page scrolls normally; no second scrollbar inside sidebar */
        .tnb-results-layout .filter-searchBar{
            position:sticky!important;
            top:86px!important;
            max-height:none!important;
            height:auto!important;
            overflow:visible!important;
            border-radius:18px!important;
        }
        .tnb-results-layout .filter-searchBar-body{
            max-height:none!important;
            height:auto!important;
            overflow:visible!important;
            padding:0 18px 18px!important;
        }
        .tnb-results-layout .filter-searchBar-body::-webkit-scrollbar{display:none!important}
        .tnb-filter-column{
            align-self:flex-start!important;
        }
        .tnb-filter-title{
            font-size:17px!important;
            line-height:1.2!important;
        }
        .tnb-filter-reset{
            font-size:10px!important;
            white-space:nowrap;
        }
        .tnb-extra-filter{
            padding:18px 0!important;
        }
        .tnb-extra-filter h6,
        .tnb-results-layout .filter-searchBar-body h6,
        .tnb-results-layout .filter-searchBar-body .fs-6{
            margin-bottom:13px!important;
            color:#263d59!important;
            font-size:11px!important;
            font-weight:850!important;
            letter-spacing:.015em!important;
        }

        /* Reference-style stop / airline lists */
        .tnb-results-layout .filter-searchBar-body label{
            min-height:28px;
            display:flex!important;
            align-items:center!important;
            gap:10px!important;
            margin-bottom:4px!important;
            color:#405873!important;
            font-size:11px!important;
        }
        .tnb-results-layout .filter-searchBar-body input[type="checkbox"]{
            width:17px!important;
            height:17px!important;
            margin:0!important;
        }

        /* Departure/Return time becomes a clean 2-column chip grid where component wrappers allow it */
        .label-filter-dpttime,
        .label-filter-rtntime{
            display:grid!important;
            grid-template-columns:1fr 1fr!important;
            gap:8px!important;
        }
        .label-filter-dpttime label,
        .label-filter-rtntime label{
            min-height:52px!important;
            padding:8px 10px!important;
            border:1px solid #dce5ef!important;
            border-radius:14px!important;
            background:#fff!important;
            margin:0!important;
        }

        /* Fare table compact like supplied reference */
        .tnb-fare-compare{
            border-radius:15px!important;
        }
        .tnb-fare-overview-head{
            padding:12px 16px!important;
        }
        .tnb-fare-overview-head h3{font-size:14px!important}
        .tnb-fare-overview-head p{font-size:9px!important}
        .tnb-fare-matrix{
            min-width:880px!important;
            grid-template-columns:125px repeat(var(--airline-count),minmax(135px,1fr))!important;
        }
        .tnb-fare-head{
            min-height:70px!important;
        }
        .tnb-fare-cell{
            min-height:48px!important;
            padding:7px 8px!important;
        }
        .tnb-fare-price{font-size:13px!important}
        .tnb-fare-action{font-size:7px!important}
        .tnb-fare-note{padding:8px 13px!important;font-size:8px!important}

        /* RESULT CARD: much closer to the reference screenshot */
        .tnb-flight-results-column .result-step{
            margin-bottom:14px!important;
        }
        .result-step .flights-list-item{
            border:1px solid #dce4ed!important;
            border-radius:14px!important;
            background:#fff!important;
            box-shadow:0 6px 18px rgba(14,38,68,.055)!important;
        }
        .result-step .tnb-itin-row{
            min-height:82px!important;
            padding:10px 14px!important;
            grid-template-columns:minmax(150px,1.05fr) 96px minmax(130px,.9fr) 96px 70px!important;
        }
        .result-step .tnb-airline{
            padding:7px 9px!important;
            border:0!important;
            background:transparent!important;
        }
        .result-step .tnb-airline img{
            width:37px!important;
            height:31px!important;
            object-fit:contain!important;
        }
        .result-step .tnb-airline strong{font-size:11px!important}
        .result-step .tnb-airline span{font-size:8.5px!important}
        .result-step .tnb-time strong{
            font-size:18px!important;
            line-height:1.05!important;
        }
        .result-step .tnb-time span{font-size:9px!important}
        .result-step .tnb-path>span{font-size:9px!important}
        .result-step .tnb-path b{font-size:9px!important}
        .result-step .tnb-price-col{
            min-width:190px!important;
            background:#fff!important;
        }
        .result-step .tnb-price-box{
            padding:12px 14px!important;
            text-align:center!important;
            align-items:center!important;
        }
        .result-step .tnb-price-box strong{
            font-size:26px!important;
            color:#132c49!important;
        }
        .result-step .tnb-select-btn,
        .result-step .tnb-price-box .btn-primary{
            width:130px!important;
            min-height:43px!important;
            margin-top:9px!important;
            border:0!important;
            border-radius:8px!important;
            background:#ff6800!important;
            box-shadow:none!important;
            color:#fff!important;
            font-size:12px!important;
            font-weight:850!important;
            text-transform:uppercase!important;
        }
        .result-step .tnb-select-btn:hover,
        .result-step .tnb-price-box .btn-primary:hover{
            background:#ea5f00!important;
        }

        /* Flight Detail bar + full detail area */
        .result-step .accordion{
            padding:0!important;
            border-top:1px solid #e7edf4!important;
        }
        .result-step .accordion-button{
            min-height:42px!important;
            padding:0 16px!important;
            background:#fff!important;
            color:#ef6412!important;
            font-size:10px!important;
            font-weight:850!important;
            box-shadow:none!important;
        }
        .result-step .accordion-button:not(.collapsed){
            background:#fbfcfe!important;
        }
        .result-step .accordion-body{
            padding:0!important;
            background:#fff!important;
        }
        .result-step .airLines-fullsegment{
            min-height:90px;
            padding:14px 18px 18px!important;
            background:#fff!important;
            color:#203752!important;
        }
        .tnb-detail-loading{
            display:flex;
            align-items:center;
            gap:8px;
            padding:18px 2px;
            color:#71849a;
            font-size:10px;
        }

        @media(max-width:991px){
            .tnb-results-layout .filter-searchBar{
                position:static!important;
            }
        }


        /* =========================================================
           V17 — FILTER + FLIGHTS SIDE-BY-SIDE FIX
           Bootstrap row gutters were making 310px + remaining 100%
           exceed the row width, so the flight column wrapped below.
           Use CSS Grid for a stable 2-column listing layout.
           ========================================================= */
        .tnb-results-layout{
            display:grid!important;
            grid-template-columns:310px minmax(0,1fr)!important;
            gap:18px!important;
            width:100%!important;
            margin-left:0!important;
            margin-right:0!important;
            --bs-gutter-x:0!important;
            --bs-gutter-y:0!important;
        }

        .tnb-results-layout > .tnb-filter-column,
        .tnb-results-layout > .tnb-flight-results-column{
            width:auto!important;
            max-width:none!important;
            flex:none!important;
            padding-left:0!important;
            padding-right:0!important;
            min-width:0!important;
        }

        .tnb-filter-column{
            grid-column:1!important;
            align-self:start!important;
        }

        .tnb-flight-results-column{
            grid-column:2!important;
            min-width:0!important;
            margin:0!important;
        }

        .tnb-flight-results-column > .row{
            width:100%!important;
            margin:0!important;
            --bs-gutter-x:0!important;
        }

        .tnb-flight-results-column .result-step{
            width:100%!important;
            max-width:100%!important;
            flex:0 0 100%!important;
            padding-left:0!important;
            padding-right:0!important;
        }

        @media(max-width:1199px){
            .tnb-results-layout{
                grid-template-columns:280px minmax(0,1fr)!important;
                gap:14px!important;
            }
        }

        @media(max-width:991px){
            .tnb-results-layout{
                display:block!important;
            }
            .tnb-filter-column,
            .tnb-flight-results-column{
                width:100%!important;
            }
            .tnb-flight-results-column{
                margin-top:16px!important;
            }
        }


        /* =========================================================
           V18 — COMPACT DESKTOP LAYOUT
           Same live fare/search logic; only size/spacing reduced.
           ========================================================= */

        /* Keep listing content premium but stop it stretching edge-to-edge */
        .tnb-listing-results-section > .container{
            max-width:1420px!important;
        }

        /* ---------- Compare Fares: much more compact ---------- */
        .tnb-premium-top .tnb-fare-compare{
            margin-bottom:10px!important;
            border-radius:14px!important;
        }
        .tnb-premium-top .tnb-fare-overview-head{
            padding:10px 14px!important;
        }
        .tnb-premium-top .tnb-fare-overview-head h3{
            font-size:13px!important;
        }
        .tnb-premium-top .tnb-fare-overview-head p{
            margin-top:2px!important;
            font-size:8px!important;
        }
        .tnb-premium-top .tnb-compare-link{
            font-size:8px!important;
        }
        .tnb-premium-top .tnb-fare-matrix{
            min-width:760px!important;
            grid-template-columns:112px repeat(var(--airline-count),minmax(120px,1fr))!important;
        }
        .tnb-premium-top .tnb-fare-head{
            min-height:52px!important;
            padding:5px 6px!important;
            gap:2px!important;
        }
        .tnb-premium-top .tnb-fare-head img{
            width:29px!important;
            height:22px!important;
        }
        .tnb-premium-top .tnb-fare-head strong{
            font-size:8.5px!important;
        }
        .tnb-premium-top .tnb-airline-code{
            padding:1px 5px!important;
            font-size:6.5px!important;
        }
        .tnb-premium-top .tnb-fare-cell{
            min-height:36px!important;
            padding:5px 7px!important;
        }
        .tnb-premium-top .tnb-fare-label{
            padding-left:10px!important;
            font-size:8px!important;
        }
        .tnb-premium-top .tnb-show-all-fares{
            font-size:8px!important;
        }
        .tnb-premium-top .tnb-fare-filter{
            min-height:31px!important;
            gap:0!important;
            padding:2px 4px!important;
        }
        .tnb-premium-top .tnb-fare-price{
            font-size:11.5px!important;
        }
        .tnb-premium-top .tnb-fare-action{
            margin-top:1px!important;
            font-size:6.5px!important;
        }
        .tnb-premium-top .tnb-fare-empty{
            font-size:8px!important;
        }
        .tnb-premium-top .tnb-fare-note{
            padding:7px 11px!important;
            font-size:7px!important;
            line-height:1.35!important;
        }

        /* ---------- Flexible dates: slim horizontal ribbon ---------- */
        .tnb-premium-top .tnb-price-calendar{
            margin-bottom:10px!important;
            border-radius:14px!important;
        }
        .tnb-premium-top .tnb-price-calendar-head{
            padding:9px 14px!important;
        }
        .tnb-premium-top .tnb-price-calendar-title{
            gap:9px!important;
        }
        .tnb-premium-top .tnb-price-calendar-title .label{
            font-size:8px!important;
        }
        .tnb-premium-top .tnb-price-calendar-title strong{
            font-size:13px!important;
        }
        .tnb-premium-top .tnb-price-calendar-head-right{
            font-size:7.5px!important;
        }
        .tnb-premium-top .tnb-date-strip{
            gap:6px!important;
            padding:9px 12px 10px!important;
        }
        .tnb-premium-top .tnb-date-pill{
            flex-basis:86px!important;
            min-height:72px!important;
            padding:5px 4px!important;
            border-radius:12px!important;
            gap:1px!important;
        }
        .tnb-premium-top .tnb-date-pill small{
            font-size:7px!important;
        }
        .tnb-premium-top .tnb-date-pill b{
            font-size:9px!important;
        }
        .tnb-premium-top .tnb-date-pill em{
            font-size:12px!important;
        }
        .tnb-premium-top .tnb-date-pill .check,
        .tnb-premium-top .tnb-date-pill .selected-label{
            font-size:6.5px!important;
        }

        /* ---------- Route summary ---------- */
        .tnb-premium-top .tnb-search-summary{
            min-height:58px!important;
            margin-bottom:9px!important;
            padding:10px 14px!important;
            border-radius:13px!important;
        }
        .tnb-premium-top .tnb-search-summary strong{
            font-size:12.5px!important;
        }
        .tnb-premium-top .tnb-search-summary span{
            margin-top:2px!important;
            font-size:7.5px!important;
        }
        .tnb-premium-top .tnb-summary-date{
            font-size:8px!important;
        }
        .tnb-premium-top .tnb-change-search{
            padding:5px 9px!important;
            font-size:7px!important;
        }

        /* ---------- Results count + sort ---------- */
        .tnb-premium-top .tnb-results-toolbar{
            margin-bottom:5px!important;
        }
        .tnb-premium-top .tnb-results-title{
            font-size:13px!important;
        }
        .tnb-premium-top .tnb-results-toolbar small{
            font-size:7.5px!important;
        }
        .tnb-premium-top .tnb-sortbar{
            margin-bottom:12px!important;
            border-radius:12px!important;
        }
        .tnb-premium-top .tnb-sortitem{
            min-height:52px!important;
            padding:7px 13px!important;
            gap:1px!important;
        }
        .tnb-premium-top .tnb-sortitem b{
            font-size:10px!important;
        }
        .tnb-premium-top .tnb-sortitem span{
            font-size:7.5px!important;
        }

        /* ---------- Filter/results region ---------- */
        .tnb-results-layout{
            grid-template-columns:275px minmax(0,1fr)!important;
            gap:14px!important;
        }
        .tnb-results-layout .filter-searchBar{
            top:78px!important;
        }
        .tnb-results-layout .filter-searchBar-head > div{
            padding:12px 15px!important;
        }
        .tnb-filter-title{
            font-size:14px!important;
        }

        /* Flight card widths/heights */
        .result-step .tnb-itin-row{
            min-height:72px!important;
            padding:8px 12px!important;
            grid-template-columns:minmax(135px,1fr) 82px minmax(118px,.85fr) 82px 62px!important;
            gap:7px!important;
        }
        .result-step .tnb-airline img{
            width:31px!important;
            height:27px!important;
        }
        .result-step .tnb-airline strong{
            font-size:10px!important;
        }
        .result-step .tnb-airline span{
            font-size:7.5px!important;
        }
        .result-step .tnb-time strong{
            font-size:15px!important;
        }
        .result-step .tnb-time span,
        .result-step .tnb-path > span,
        .result-step .tnb-path b{
            font-size:8px!important;
        }
        .result-step .tnb-price-col{
            min-width:170px!important;
        }
        .result-step .tnb-price-box{
            padding:10px 12px!important;
        }
        .result-step .tnb-price-box strong{
            font-size:22px!important;
        }
        .result-step .tnb-select-btn,
        .result-step .tnb-price-box .btn-primary{
            width:118px!important;
            min-height:38px!important;
            font-size:10px!important;
        }

        @media(max-width:1199px){
            .tnb-listing-results-section > .container{
                max-width:1140px!important;
            }
            .tnb-results-layout{
                grid-template-columns:250px minmax(0,1fr)!important;
                gap:12px!important;
            }
        }

        @media(max-width:991px){
            .tnb-listing-results-section > .container{
                max-width:960px!important;
            }
            .tnb-results-layout{
                display:block!important;
            }
        }


        /* V19 — INLINE FULL FLIGHT DETAILS (no AJAX dependency) */
        .tnb-inline-details{
            padding:0!important;
            background:#fff;
        }
        .tnb-detail-journey-head{
            display:flex;
            align-items:center;
            gap:7px;
            padding:12px 16px;
            border-bottom:1px solid #dfe6ee;
            color:#263d59;
        }
        .tnb-detail-journey-head i{color:#4c7ed9;font-size:14px}
        .tnb-detail-journey-head strong{font-size:12px;font-weight:900}
        .tnb-detail-journey-head span{font-size:10px;font-weight:700}
        .tnb-detail-segment{
            display:grid;
            grid-template-columns:minmax(210px,1.25fr) minmax(150px,.9fr) 92px minmax(170px,1fr);
            gap:16px;
            align-items:center;
            padding:14px 16px;
            border-bottom:1px solid #e7edf3;
        }
        .tnb-detail-airline{
            display:flex;
            align-items:flex-start;
            gap:10px;
        }
        .tnb-detail-airline img{width:36px;height:30px;object-fit:contain;margin-top:2px}
        .tnb-detail-airline strong{display:block;color:#16304e;font-size:11px;font-weight:900}
        .tnb-detail-airline span{display:block;margin-top:3px;color:#435a73;font-size:9px}
        .tnb-detail-airline em{display:block;margin-top:5px;color:#f05b16;font-size:9px;font-style:normal;font-weight:800}
        .tnb-detail-point strong{display:block;color:#172f4c;font-size:16px;font-weight:800}
        .tnb-detail-point span{display:block;margin-top:3px;color:#263e59;font-size:9px}
        .tnb-detail-point b{display:block;margin-top:3px;color:#172f4c;font-size:9px}
        .tnb-detail-duration{text-align:center;color:#647991}
        .tnb-detail-duration i{display:block;margin-bottom:5px;color:#9aa8b6;font-size:18px}
        .tnb-detail-duration strong{font-size:10px}
        .tnb-connection-bar{
            display:flex;
            align-items:center;
            justify-content:center;
            gap:7px;
            padding:8px 12px;
            background:#f3f4f5;
            border-bottom:1px solid #e2e6ea;
            color:#243b55;
            font-size:9px;
        }
        .tnb-connection-bar i{color:#6b7e93}
        .tnb-connection-bar b{font-weight:900}
        @media(max-width:767px){
            .tnb-detail-segment{grid-template-columns:1fr 1fr;gap:10px}
            .tnb-detail-airline{grid-column:1/-1}
            .tnb-detail-duration{display:none}
        }


        /* =========================================================
           V20 — FLIGHT DETAILS MATCH REFERENCE SCREENSHOT
           ========================================================= */
        .result-step .accordion{
            margin-top:0!important;
            padding:0!important;
            border-top:1px solid #d6d6d6!important;
            background:#fff!important;
        }
        .result-step .accordion-item{
            border:0!important;
            background:#fff!important;
        }
        .result-step .accordion-button{
            min-height:36px!important;
            padding:0 6px!important;
            background:#fff!important;
            color:#ff5b00!important;
            font-size:11px!important;
            font-weight:800!important;
            box-shadow:none!important;
        }
        .result-step .accordion-button::after{
            width:10px!important;
            height:10px!important;
            margin-left:6px!important;
            background-size:10px!important;
        }
        .result-step .accordion-button:not(.collapsed){
            background:#fff!important;
            color:#ff5b00!important;
        }
        .result-step .accordion-body.shadow-wrap{
            margin:0!important;
            padding:0!important;
            border:0!important;
            border-radius:0!important;
            background:#fff!important;
            box-shadow:none!important;
        }
        .tnb-inline-details{
            padding:0!important;
            background:#fff!important;
        }
        .tnb-detail-journey-head{
            display:flex!important;
            align-items:center!important;
            gap:5px!important;
            min-height:38px!important;
            padding:7px 6px!important;
            border-top:1px solid #d8d8d8!important;
            border-bottom:0!important;
            color:#3a3a3a!important;
            background:#fff!important;
        }
        .tnb-detail-journey-head:first-child{border-top:0!important}
        .tnb-detail-journey-head i{
            color:#4f7ef7!important;
            font-size:17px!important;
        }
        .tnb-detail-journey-head strong{
            color:#424242!important;
            font-size:13px!important;
            font-weight:800!important;
        }
        .tnb-detail-journey-head span{
            color:#444!important;
            font-size:11px!important;
            font-weight:500!important;
        }
        .tnb-detail-segment{
            display:grid!important;
            grid-template-columns:minmax(190px,1.15fr) minmax(175px,1fr) 95px minmax(175px,1fr)!important;
            gap:14px!important;
            align-items:center!important;
            min-height:88px!important;
            padding:10px 12px!important;
            border-bottom:1px solid #d8d8d8!important;
            background:#fff!important;
        }
        .tnb-detail-airline{
            display:flex!important;
            align-items:center!important;
            gap:12px!important;
        }
        .tnb-detail-airline img{
            width:40px!important;
            height:30px!important;
            object-fit:contain!important;
            margin:0!important;
        }
        .tnb-detail-airline strong{
            display:block!important;
            margin:0!important;
            color:#111!important;
            font-size:12px!important;
            font-weight:700!important;
        }
        .tnb-detail-airline span{
            display:block!important;
            margin-top:3px!important;
            color:#111!important;
            font-size:10px!important;
        }
        .tnb-detail-airline span b{font-weight:800!important}
        .tnb-detail-airline em{
            display:block!important;
            margin-top:4px!important;
            color:#ff5b00!important;
            font-size:10px!important;
            font-style:normal!important;
            font-weight:500!important;
        }
        .tnb-detail-point{
            color:#111!important;
        }
        .tnb-detail-point strong{
            display:block!important;
            color:#111!important;
            font-size:16px!important;
            font-weight:500!important;
            line-height:1.1!important;
        }
        .tnb-detail-point span{
            display:block!important;
            margin-top:4px!important;
            color:#222!important;
            font-size:10px!important;
            line-height:1.25!important;
        }
        .tnb-detail-point b{
            display:block!important;
            margin-top:4px!important;
            color:#111!important;
            font-size:10px!important;
            font-weight:700!important;
        }
        .tnb-detail-duration{
            text-align:center!important;
            color:#555!important;
        }
        .tnb-detail-duration i{
            display:block!important;
            margin-bottom:3px!important;
            color:#a9a9a9!important;
            font-size:20px!important;
        }
        .tnb-detail-duration strong{
            color:#222!important;
            font-size:10px!important;
            font-weight:500!important;
        }
        .tnb-connection-bar{
            display:flex!important;
            align-items:center!important;
            justify-content:center!important;
            gap:4px!important;
            min-height:34px!important;
            padding:6px 10px!important;
            border:0!important;
            border-bottom:1px solid #d8d8d8!important;
            background:#f1f1f1!important;
            color:#111!important;
            font-size:10px!important;
        }
        .tnb-connection-bar i{display:none!important}
        .tnb-connection-bar span{color:#111!important}
        .tnb-connection-bar b{
            color:#111!important;
            font-weight:800!important;
        }
        .tnb-connection-bar span + b::before{
            content:' | ';
            font-weight:400;
        }
        @media(max-width:767px){
            .tnb-detail-segment{
                grid-template-columns:1fr 1fr!important;
                gap:10px!important;
                padding:10px!important;
            }
            .tnb-detail-airline{grid-column:1/-1!important}
            .tnb-detail-duration{display:none!important}
            .tnb-detail-point.text-end{text-align:left!important}
        }


        /* V21 flight-detail reference polish */
        .result-step .accordion-button{
            color:#f06413!important;
        }
        .tnb-inline-details{
            padding:2px 0 0!important;
        }
        .tnb-detail-journey-head{
            min-height:38px!important;
            padding:8px 10px!important;
            border-bottom:1px solid #d9dee5!important;
        }
        .tnb-detail-journey-head i{
            color:#4a78f0!important;
        }
        .tnb-detail-journey-head strong{
            font-size:12px!important;
        }
        .tnb-detail-journey-head span{
            margin-left:2px!important;
            color:#263d59!important;
            font-size:10px!important;
        }
        .tnb-detail-segment{
            min-height:94px!important;
            padding:12px 10px!important;
            border-bottom:1px solid #d9dee5!important;
        }
        .tnb-detail-airline strong{
            font-size:11px!important;
        }
        .tnb-detail-airline span,
        .tnb-detail-airline em{
            font-size:9px!important;
        }
        .tnb-detail-airline em{
            color:#ff5b00!important;
        }
        .tnb-detail-point strong{
            font-size:15px!important;
        }
        .tnb-detail-point span,
        .tnb-detail-point b{
            font-size:9px!important;
        }
        .tnb-detail-duration i{
            color:#a9b2bc!important;
            font-size:19px!important;
        }
        .tnb-detail-duration strong{
            margin-top:4px!important;
            font-size:10px!important;
        }
        .tnb-connection-bar{
            min-height:34px!important;
            padding:7px 10px!important;
            background:#f0f0f0!important;
            color:#111!important;
            text-align:center!important;
            font-size:9px!important;
        }

        /* Existing AJAX flight detail response — premium container treatment */
        .result-step .accordion-body.shadow-wrap{
            margin-top:10px;
            padding:16px!important;
            border:1px solid #e0e7ef;
            border-radius:14px;
            background:#f8fafc;
            box-shadow:none!important;
        }
        .result-step .airLines-fullsegment{
            color:#405675;
            font-size:12px;
        }
        .result-step .airLines-fullsegment img{
            max-width:48px;
            max-height:38px;
            object-fit:contain;
        }
        .result-step .accordion-button:after{margin-left:auto}
        .result-step .accordion-button{
            min-height:34px;
            color:#d91f43!important;
        }

        @media(max-width:767px){
            .tnb-price-calendar{padding:14px 12px}
            .tnb-fare-compare{border-radius:14px}
        }


        /* =========================================================
           FARESHUNT LISTING — FINAL PREMIUM SEARCH BAR
           Only the listing search bar/date picker/autocomplete UI.
           ========================================================= */
        .fhx-wrap{
            padding:24px 0 20px;
            background:#f4f7fb;
        }
        .fhx-card{
            position:relative;
            z-index:50;
            padding:18px 20px 20px;
            background:#fff;
            border:1px solid #dfe7ef;
            border-radius:22px;
            box-shadow:0 18px 44px rgba(15,39,68,.10);
            overflow:visible;
        }
        .fhx-top{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:16px;
            margin-bottom:14px;
        }
        .fhx-trip{
            display:flex;
            align-items:center;
            gap:24px;
        }
        .fhx-trip label{
            display:flex;
            align-items:center;
            gap:8px;
            margin:0;
            color:#455870;
            font-size:14px;
            font-weight:800;
            cursor:pointer;
        }
        .fhx-trip input{
            width:19px;
            height:19px;
            margin:0;
            accent-color:#d7193f;
        }
        .fhx-secure{
            color:#8291a4;
            font-size:11px;
            font-weight:650;
        }
        .fhx-grid{
            display:grid;
            grid-template-columns:minmax(220px,1.55fr) minmax(220px,1.55fr) minmax(145px,.88fr) minmax(145px,.88fr) minmax(165px,.98fr) minmax(135px,.78fr) 118px;
            gap:10px;
            align-items:stretch;
        }
        .fhx-field{
            position:relative;
            min-width:0;
            min-height:76px;
            padding:11px 13px 10px;
            border:1px solid #dbe4ee;
            border-radius:14px;
            background:#fff;
            transition:.18s ease;
        }
        .fhx-field:hover{
            border-color:#b9c8d8;
            box-shadow:0 8px 20px rgba(18,45,76,.05);
        }
        .fhx-field:focus-within,
        .fhx-field.is-open{
            border-color:#173f70;
            box-shadow:0 0 0 3px rgba(23,63,112,.08);
        }
        .fhx-label{
            display:block;
            margin-bottom:5px;
            color:#8493a6;
            font-size:9px;
            line-height:1;
            font-weight:900;
            letter-spacing:.08em;
            text-transform:uppercase;
        }
        .fhx-input,
        .fhx-select{
            display:block;
            width:100%;
            height:34px;
            padding:0!important;
            border:0!important;
            outline:0!important;
            background:transparent!important;
            box-shadow:none!important;
            color:#172e4b!important;
            font-size:13px!important;
            font-weight:750!important;
            text-overflow:ellipsis;
        }
        .fhx-input::placeholder{color:#9faaba;font-weight:500}
        .fhx-airport-field{padding-right:13px}
        .fhx-airport-symbol{display:none!important;}
        .fhx-date-display{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:8px;
            color:#172e4b;
            font-size:13px;
            font-weight:750;
        }
        .fhx-date-display i{color:#5b718d}
        .fhx-date-note{
            margin-top:2px;
            color:#8a98aa;
            font-size:10px;
            font-weight:600;
        }
        .fhx-return-disabled{
            opacity:.48;
            background:#f7f9fc;
        }
        .fhx-pax-btn{
            width:100%;
            height:40px;
            padding:0!important;
            border:0!important;
            background:transparent!important;
            color:#172e4b!important;
            box-shadow:none!important;
            text-align:left!important;
        }
        .fhx-pax-line{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:7px;
            font-size:13px;
            font-weight:800;
            line-height:1.15;
        }
        .fhx-pax-sub{
            display:block;
            margin-top:4px;
            color:#8997a9;
            font-size:10px;
            font-weight:600;
        }
        .fhx-pax-line > span:first-child{
            display:flex;
            align-items:center;
            min-width:0;
            white-space:nowrap;
        }
        #fhxTravelerCount{margin-right:4px}
        .fhx-pax-line .fa-user-group{font-size:12px;color:#214e7e}
        .fhx-pax-line .fa-chevron-down{font-size:10px;color:#5d7088}

        .fhx-search-btn{
            min-height:74px;
            border:0;
            border-radius:13px;
            background:linear-gradient(135deg,#e21b3e 0%,#c51232 100%);
            color:#fff;
            font-size:13px;
            font-weight:900;
            box-shadow:0 13px 26px rgba(213,24,59,.24);
            transition:.18s ease;
        }
        .fhx-search-btn:hover{
            transform:translateY(-1px);
            box-shadow:0 16px 30px rgba(213,24,59,.29);
        }

        /* Airport suggestion panel */
        .fhx-airport-list{
            display:none;
            position:absolute;
            z-index:999999;
            left:0;
            top:calc(100% + 8px);
            width:max(100%,380px);
            max-height:340px;
            overflow:auto;
            padding:7px;
            background:#fff;
            border:1px solid #dce4ed;
            border-radius:14px;
            box-shadow:0 22px 50px rgba(14,37,66,.18);
        }
        .fhx-airport-list.show{display:block}
        .fhx-airport-item{
            width:100%;
            display:grid;
            grid-template-columns:44px minmax(0,1fr);
            gap:10px;
            align-items:center;
            padding:10px;
            border:0;
            border-radius:10px;
            background:#fff;
            text-align:left;
            cursor:pointer;
        }
        .fhx-airport-item:hover{background:#f1f6fc}
        .fhx-code{
            height:34px;
            display:grid;
            place-items:center;
            border-radius:9px;
            background:#eaf2ff;
            color:#1d58a0;
            font-size:11px;
            font-weight:900;
        }
        .fhx-ap-copy strong{
            display:block;
            color:#173252;
            font-size:12px;
            line-height:1.25;
        }
        .fhx-ap-copy small{
            display:block;
            margin-top:2px;
            color:#8492a5;
            font-size:10px;
            line-height:1.25;
        }
        .fhx-airport-empty{
            padding:14px 10px;
            color:#8291a4;
            font-size:11px;
            text-align:center;
        }

        /* Passenger popup */
        .fhx-card .passenger-box{
            z-index:999999!important;
            top:calc(100% + 8px)!important;
            right:0!important;
            min-width:290px!important;
            padding:15px!important;
            background:#fff!important;
            border:1px solid #dce4ed!important;
            border-radius:14px!important;
            box-shadow:0 22px 50px rgba(14,37,66,.18)!important;
        }
        .fhx-card .passenger-box .form-label{
            color:#52677f;
            font-size:11px;
            font-weight:800;
        }
        .fhx-card .passenger-box .form-select{
            height:40px;
            border:1px solid #dce4ee!important;
            border-radius:9px!important;
            padding:0 10px!important;
        }

        /* Premium calendar */
        .flatpickr-calendar{
            z-index:999999!important;
            border:1px solid #dce5ef!important;
            border-radius:16px!important;
            box-shadow:0 24px 58px rgba(14,37,66,.20)!important;
            font-family:inherit!important;
            overflow:hidden;
        }
        .flatpickr-months{
            padding:9px 8px 5px!important;
            background:#fff!important;
        }
        .flatpickr-current-month{
            color:#17395f!important;
            font-size:14px!important;
            font-weight:850!important;
        }
        .flatpickr-weekdays{background:#fff!important}
        span.flatpickr-weekday{
            color:#8997a8!important;
            font-size:10px!important;
            font-weight:850!important;
            line-height:1!important;
            padding:8px 0!important;
        }
        .flatpickr-day{
            border-radius:9px!important;
            color:#38506d!important;
            font-size:11px!important;
            font-weight:700!important;
        }
        .flatpickr-day:hover{
            background:#edf4fb!important;
            border-color:#edf4fb!important;
        }
        .flatpickr-day.selected,
        .flatpickr-day.startRange,
        .flatpickr-day.endRange{
            background:#d7193f!important;
            border-color:#d7193f!important;
            color:#fff!important;
        }
        .flatpickr-day.inRange{
            background:#fff0f3!important;
            border-color:#fff0f3!important;
            box-shadow:-5px 0 0 #fff0f3,5px 0 0 #fff0f3!important;
            color:#b81737!important;
        }
        .flatpickr-calendar.multiMonth{
            width:640px!important;
            min-width:640px!important;
        }
        .flatpickr-calendar.multiMonth .flatpickr-months .flatpickr-month{
            width:50%!important;
            min-width:0!important;
        }
        .flatpickr-calendar.multiMonth .flatpickr-innerContainer{
            width:100%!important;
        }
        .flatpickr-calendar.multiMonth .flatpickr-rContainer{
            width:100%!important;
        }
        .flatpickr-calendar.multiMonth .flatpickr-weekdays{
            width:100%!important;
        }
        .flatpickr-calendar.multiMonth .flatpickr-weekdaycontainer{
            width:50%!important;
            min-width:0!important;
            flex:0 0 50%!important;
        }
        .flatpickr-calendar.multiMonth .flatpickr-days{
            width:100%!important;
            max-width:none!important;
            min-width:0!important;
        }
        .flatpickr-calendar.multiMonth .dayContainer{
            width:50%!important;
            min-width:0!important;
            max-width:none!important;
            padding:0 10px 10px!important;
        }
        .flatpickr-calendar.multiMonth .dayContainer + .dayContainer{
            border-left:1px solid #edf1f5;
        }
        @media(max-width:899px){
            .flatpickr-calendar.multiMonth{
                width:auto!important;
                min-width:0!important;
            }
        }



        .fhx-card,
        .fhx-wrap{
            overflow:visible!important;
        }
        .flatpickr-calendar.open{
            z-index:1000000!important;
        }

        @media(max-width:1450px){
            .fhx-grid{
                grid-template-columns:minmax(210px,1.45fr) minmax(210px,1.45fr) minmax(140px,.85fr) minmax(140px,.85fr) minmax(155px,.95fr) minmax(130px,.76fr) 122px;
            }
        }
        @media(max-width:1199px){
            .fhx-grid{grid-template-columns:repeat(4,1fr)}
            .fhx-search-btn{min-height:58px}
        }
        @media(max-width:767px){
            .fhx-wrap{padding:14px 0}
            .fhx-card{padding:13px;border-radius:17px}
            .fhx-top{align-items:flex-start;flex-direction:column}
            .fhx-grid{grid-template-columns:1fr 1fr}
            .fhx-search-btn{grid-column:1/-1;min-height:52px}
            .fhx-airport-list{width:min(390px,calc(100vw - 46px))}
        }
        @media(max-width:520px){
            .fhx-grid{grid-template-columns:1fr}
            .fhx-search-btn{grid-column:auto}
        }

    

        /* =========================================================
           V15 — PIXEL-CLOSE REFERENCE POLISH
           Visual-only overrides. Existing API/search/fare logic remains intact.
           ========================================================= */
        .tnb-listing-results-section{background:#f5f7fb!important;padding-top:18px!important}
        .tnb-results-layout{--filter-width:292px!important;gap:18px!important}
        .tnb-results-layout .filter-searchBar{border-radius:16px!important;border-color:#dbe4ee!important;box-shadow:0 8px 24px rgba(16,42,70,.055)!important}
        .tnb-results-layout .filter-searchBar-head{min-height:58px!important;padding:0 18px!important;display:flex!important;align-items:center!important;justify-content:space-between!important}
        .tnb-filter-title{font-size:15px!important;color:#1c2f47!important}
        .tnb-filter-reset{font-size:10px!important;color:#e11d3f!important}
        .tnb-results-layout .filter-searchBar-body{padding:0 18px 18px!important}
        .tnb-extra-filter{padding:16px 0 18px!important}
        .tnb-extra-filter h6{font-size:10px!important;margin-bottom:12px!important}
        .tnb-time-chip{min-height:52px!important;border-radius:16px!important;background:#fff!important}

        .tnb-fare-compare,.tnb-price-calendar{border-radius:16px!important;border:1px solid #dbe4ee!important;box-shadow:0 8px 24px rgba(16,42,70,.05)!important;background:#fff!important}
        .tnb-fare-overview-head{min-height:66px!important;padding:15px 18px 12px!important}
        .tnb-fare-overview-head h3{font-size:15px!important}
        .tnb-fare-overview-head p{font-size:9px!important}
        .tnb-fare-matrix{grid-template-columns:125px repeat(var(--airline-count),minmax(155px,1fr))!important;min-width:920px!important}
        .tnb-fare-head{min-height:82px!important;background:#fff!important}
        .tnb-fare-cell{min-height:52px!important;background:#fff!important}
        .tnb-fare-price{font-size:15px!important;color:#102a45!important}
        .tnb-fare-note{min-height:38px!important;padding:10px 16px!important;background:#f5f9ff!important}

        .tnb-price-calendar-head{min-height:62px!important;padding:0 18px!important}
        .tnb-date-strip{gap:9px!important;padding:14px 14px 12px!important}
        .tnb-date-pill{min-width:112px!important;height:106px!important;border-radius:16px!important;border:1px solid #d8e3ee!important;background:#fff!important;box-shadow:none!important}
        .tnb-date-pill.active{background:#10375f!important;border-color:#10375f!important;color:#fff!important;box-shadow:0 10px 22px rgba(16,55,95,.18)!important}

        .tnb-search-summary{min-height:76px!important;border-radius:15px!important;background:linear-gradient(135deg,#0d355f,#164b7d)!important;box-shadow:0 10px 25px rgba(13,53,95,.13)!important}
        .tnb-results-toolbar{margin-top:12px!important}
        .tnb-sortbar{border-radius:14px!important;overflow:hidden!important;border:1px solid #d9e3ed!important;background:#fff!important}
        .tnb-sortitem{min-height:68px!important;padding:13px 18px!important}
        .tnb-sortitem.active{background:#10375f!important;color:#fff!important}

        .tnb-flight-results-column .result-step{margin-bottom:12px!important}
        .result-step .flights-list-item{border-radius:16px!important;border:1px solid #dbe4ee!important;box-shadow:0 7px 20px rgba(15,42,70,.055)!important;background:#fff!important}
        .result-step .tnb-itin-row{grid-template-columns:minmax(190px,1.2fr) 105px minmax(145px,.9fr) 105px 78px!important;gap:12px!important;min-height:104px!important;padding:15px 18px!important}
        .result-step .tnb-airline img{width:40px!important;height:40px!important}
        .result-step .tnb-airline strong{font-size:12px!important;color:#182d45!important}
        .result-step .tnb-airline span{font-size:9px!important;color:#72849a!important}
        .result-step .tnb-time strong{font-size:19px!important;color:#132b46!important}
        .result-step .tnb-time span{font-size:9.5px!important;color:#5f738c!important}
        .result-step .tnb-path>span,.result-step .tnb-path b{font-size:9.5px!important}
        .result-step .tnb-price-col{min-width:190px!important;background:#fff!important}
        .result-step .tnb-price-box{padding:14px 18px!important}
        .result-step .tnb-price-box strong{font-size:26px!important;color:#172a40!important}
        .result-step .tnb-select-btn{width:132px!important;min-height:44px!important;border-radius:8px!important;background:#ff6a00!important;border-color:#ff6a00!important;color:#fff!important;font-weight:900!important;box-shadow:0 9px 20px rgba(255,106,0,.18)!important;text-transform:uppercase!important}
        .result-step .tnb-select-btn:hover{background:#ea6100!important;border-color:#ea6100!important;transform:translateY(-1px)}
        .result-step .accordion-button{min-height:44px!important;padding:0 18px!important;color:#ff5d00!important;font-size:10px!important;background:#fff!important}
        .result-step .accordion-button:not(.collapsed){background:#fff!important}
        .result-step .accordion-body.shadow-wrap{background:#fff!important}
        .result-step .airLines-fullsegment{padding:14px 18px 18px!important;font-size:10px!important}



        /* Uniform Multi-City result cards */
        .tnb-multicity-card-legs{display:flex;flex-direction:column}
        .tnb-multicity-card-leg{padding:14px 4px;border-top:1px solid #e6edf5}
        .tnb-multicity-card-leg:first-child{padding-top:2px;border-top:0}
        .tnb-mc-leg-meta{display:flex;align-items:center;gap:9px;margin-bottom:9px}
        .tnb-mc-leg-meta strong{font-size:13px;color:#0b2b4b}
        .tnb-mc-leg-pill{display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;background:#eef5ff;color:#1765c1;font-size:9px;font-weight:900;text-transform:uppercase;letter-spacing:.35px}
        .tnb-mc-leg-main{display:grid;grid-template-columns:minmax(170px,1.2fr) 100px minmax(180px,1fr) 100px;gap:16px;align-items:center}
        .tnb-mc-airline{display:flex;align-items:center;gap:10px;min-width:0}
        .tnb-mc-airline img,.tnb-mc-airline-fallback{width:42px;height:42px;border-radius:10px;object-fit:contain;background:#f5f8fc;border:1px solid #e6edf5}
        .tnb-mc-airline-fallback{display:grid;place-items:center;font-weight:900;color:#174878}
        .tnb-mc-airline strong{display:block;font-size:12px;color:#112e4d;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .tnb-mc-airline small{display:block;font-size:9px;color:#718398;margin-top:2px}
        .tnb-mc-time strong{display:block;font-size:18px;line-height:1;color:#0b2947}
        .tnb-mc-time small{display:block;margin-top:5px;font-size:9px;font-weight:800;color:#63788f}
        .tnb-mc-arrival{text-align:right}
        .tnb-mc-route-line{position:relative;text-align:center;padding-top:12px;color:#6c8197}
        .tnb-mc-route-line:before{content:"";position:absolute;left:0;right:0;top:25px;border-top:1px solid #9bb2c8}
        .tnb-mc-route-line i{position:relative;z-index:1;background:#fff;padding:0 8px;color:#184e80;transform:rotate(0deg)}
        .tnb-mc-route-line span{display:block;font-size:9px;font-weight:800;margin-bottom:4px}
        .tnb-mc-route-line em{display:block;position:relative;z-index:1;width:max-content;margin:5px auto 0;padding:1px 7px;background:#fff;color:#10935e;font-style:normal;font-size:9px;font-weight:900}
        @media(max-width:900px){
            .tnb-mc-leg-main{grid-template-columns:1fr 90px 90px}
            .tnb-mc-route-line{grid-column:1/-1;grid-row:2;padding-top:4px}
            .tnb-mc-route-line:before{top:17px}
            .tnb-mc-airline{grid-column:1}
        }
        @media(max-width:600px){
            .tnb-multicity-card-leg{padding:12px 0}
            .tnb-mc-leg-main{grid-template-columns:1fr 1fr;gap:12px}
            .tnb-mc-airline{grid-column:1/-1}
            .tnb-mc-time{grid-column:auto}
            .tnb-mc-route-line{grid-column:1/-1}
        }

        /* Results-page Multi-City search editor */
        .fhx-multicity-editor{margin:12px 0 14px;padding:14px;border:1px solid #dbe5ef;border-radius:18px;background:linear-gradient(180deg,#fbfdff,#f7faff)}
        .fhx-multicity-head{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:12px}
        .fhx-multicity-head>div{display:flex;flex-direction:column;gap:2px}
        .fhx-multicity-head strong{font-size:13px;color:#102a45}
        .fhx-multicity-head span{font-size:10px;color:#73859a}
        .fhx-multicity-badge{display:inline-flex!important;flex-direction:row!important;align-items:center;gap:6px;padding:7px 10px;border-radius:999px;background:#edf5ff;color:#1763c5!important;font-weight:800}
        .fhx-multi-leg{display:grid;grid-template-columns:95px minmax(190px,1fr) minmax(190px,1fr) minmax(150px,.72fr) 42px;gap:10px;align-items:end;padding:11px 0;border-top:1px solid #e8eef5}
        .fhx-multi-leg:first-child{border-top:0;padding-top:0}
        .fhx-multi-leg-title{height:56px;display:flex;align-items:center;gap:8px;color:#183653}
        .fhx-multi-number{width:28px;height:28px;border-radius:50%;display:grid;place-items:center;background:#113f70;color:#fff;font-size:11px;font-weight:900}
        .fhx-multi-leg-title strong{font-size:11px;white-space:nowrap}
        .fhx-multi-remove{height:56px;border:1px solid #e3e9f0;border-radius:13px;background:#fff;color:#9a5260}
        .fhx-multi-remove:hover{border-color:#f3c7cf;background:#fff7f8;color:#d7193f}
        .fhx-add-flight{margin-top:8px;min-height:42px;padding:0 15px;border:1px solid #cfddea;border-radius:12px;background:#fff;color:#154d82;font-size:11px;font-weight:900}
        .fhx-add-flight:disabled{opacity:.45;cursor:not-allowed}
        .fhx-grid-multicity .fhx-standard-route-field{display:none!important}
        .fhx-grid-multicity{grid-template-columns:minmax(220px,1.2fr) minmax(180px,.8fr) 170px!important}
        .fhx-grid-multicity .fhx-search-btn{width:100%!important}
        .tnb-multicity-summary-routes{display:flex;gap:7px;flex-wrap:wrap;margin-top:4px}
        .tnb-multicity-summary-routes em{font-style:normal;font-size:9px;font-weight:800;padding:5px 8px;border-radius:999px;background:rgba(255,255,255,.12);color:#fff}
        @media(max-width:1100px){
            .fhx-multi-leg{grid-template-columns:80px 1fr 1fr 160px 42px}
        }
        @media(max-width:900px){
            .fhx-multi-leg{grid-template-columns:1fr 1fr}
            .fhx-multi-leg-title{height:auto;grid-column:1/-1}
            .fhx-multi-remove{position:absolute;right:14px;margin-top:0;width:40px;height:40px}
            .fhx-multi-leg{position:relative}
            .fhx-grid-multicity{grid-template-columns:1fr 1fr!important}
            .fhx-grid-multicity .fhx-search-btn{grid-column:1/-1}
        }
        @media(max-width:600px){
            .fhx-multicity-head{align-items:flex-start}
            .fhx-multi-leg{grid-template-columns:1fr}
            .fhx-grid-multicity{grid-template-columns:1fr!important}
        }

        @media(max-width:1199px){.tnb-results-layout{--filter-width:270px!important;gap:14px!important}}
        @media(max-width:991px){.tnb-results-layout{gap:0!important}.tnb-results-layout>.tnb-filter-column,.tnb-flight-results-column{width:100%!important;flex:0 0 100%!important}}
</style>

<style id="fareshunt-listing-mobile-recovery-v2">
@media (max-width:760px){
.fhx-wrap{padding:10px 0!important}
    .fhx-card{padding:10px!important;border-radius:16px!important}

    .tnb-mobile-search-toggle{
        width:100%!important;min-height:58px!important;border:1px solid #d9e3ee!important;border-radius:14px!important;
        background:#fff!important;display:flex!important;align-items:center!important;justify-content:space-between!important;
        gap:12px!important;padding:10px 12px!important;color:#102d4d!important;text-align:left!important
    }
    .tnb-mobile-search-toggle small{display:block!important;font-size:9px!important;text-transform:uppercase!important;letter-spacing:.08em!important;color:#7d8da2!important;font-weight:800!important}
    .tnb-mobile-search-toggle strong{display:block!important;margin-top:2px!important;font-size:14px!important;line-height:1.25!important}
    .tnb-mobile-search-action{flex:0 0 auto!important;font-size:11px!important;font-weight:900!important;color:#1761bb!important}

    #form2:not(.tnb-mobile-search-open)>.fhx-top,
    #form2:not(.tnb-mobile-search-open)>#fhxMultiCityEditor,
    #form2:not(.tnb-mobile-search-open)>#fhxStandardGrid{display:none!important}

    #form2.tnb-mobile-search-open>.fhx-top{display:flex!important;flex-direction:column!important;align-items:flex-start!important;gap:8px!important;margin-top:10px!important}
    #form2.tnb-mobile-search-open .fhx-trip{width:100%!important;display:grid!important;grid-template-columns:repeat(3,1fr)!important;gap:5px!important}
    #form2.tnb-mobile-search-open .fhx-trip label{min-width:0!important;justify-content:center!important;font-size:11px!important}

    .tnb-fare-compare,.tnb-price-calendar{display:none!important}
    .tnb-search-summary{margin:8px 0 10px!important;padding:12px!important}
    .tnb-results-title{font-size:20px!important}

    .tnb-sortbar{display:grid!important;grid-template-columns:repeat(3,1fr)!important;gap:0!important;width:100%!important;overflow:hidden!important;border-radius:14px!important}
    .tnb-sortitem{min-width:0!important;padding:10px 6px!important}
    .tnb-sortitem b{font-size:11px!important;white-space:nowrap!important}
    .tnb-sortitem span{display:none!important}

    .tnb-mobile-results-actions{display:grid!important;grid-template-columns:1fr 1fr!important;gap:8px!important;margin:10px 0!important}
    .tnb-mobile-results-actions button{min-height:44px!important;border:1px solid #dce5ef!important;border-radius:12px!important;background:#fff!important;color:#15385c!important;font-size:12px!important;font-weight:900!important}

    .tnb-filter-column{display:none!important;width:100%!important;flex:0 0 100%!important}
    .tnb-results-layout.tnb-mobile-filters-open .tnb-filter-column{display:block!important}
    .tnb-results-layout{display:block!important;margin:0!important}
    .tnb-flight-results-column{width:100%!important;max-width:100%!important;flex:0 0 100%!important;padding:0!important}
    .tnb-flight-results-column>.row{margin:0!important;gap:12px!important}
    .tnb-flight-results-column .result-step{width:100%!important;padding:0!important;margin:0!important}

    .flights-list-item{width:100%!important;padding:12px!important;border:1px solid #e1e8f0!important;border-radius:16px!important;box-shadow:0 8px 24px rgba(19,43,72,.06)!important;overflow:hidden!important}
    .tnb-desktop-flight-summary{display:none!important}
    .tnb-mobile-flight-summary{display:block!important;width:100%!important}

    .tnb-mobile-airline{display:flex!important;align-items:center!important;gap:9px!important;padding-bottom:10px!important;border-bottom:1px solid #e8eef5!important}
    .tnb-mobile-airline img,.tnb-mobile-airline>span{width:38px!important;height:38px!important;flex:0 0 38px!important;object-fit:contain!important;display:grid!important;place-items:center!important;border-radius:9px!important;background:#f5f8fc!important;font-size:10px!important;font-weight:900!important}
    .tnb-mobile-airline strong{display:block!important;font-size:13px!important;color:#102c49!important}
    .tnb-mobile-airline small{display:block!important;margin-top:2px!important;font-size:9px!important;color:#77889d!important}

    .tnb-mobile-leg{padding:10px 0!important;border-bottom:1px solid #e8eef5!important}
    .tnb-mobile-leg-head{display:flex!important;align-items:center!important;justify-content:space-between!important;gap:8px!important;margin-bottom:9px!important}
    .tnb-mobile-leg-head span{font-size:9px!important;text-transform:uppercase!important;font-weight:900!important;color:#5c77a0!important}
    .tnb-mobile-leg-head strong{font-size:12px!important;color:#153655!important}
    .tnb-mobile-leg-times{display:grid!important;grid-template-columns:1fr 80px 1fr!important;gap:8px!important;align-items:center!important}
    .tnb-mobile-leg-times b{display:block!important;font-size:17px!important;color:#0d2946!important}
    .tnb-mobile-leg-times small{display:block!important;margin-top:3px!important;font-size:9px!important;color:#6e8095!important}
    .tnb-mobile-leg-path{text-align:center!important;position:relative!important}
    .tnb-mobile-leg-path:before{content:""!important;position:absolute!important;left:0!important;right:0!important;top:10px!important;border-top:1px solid #a9bacb!important}
    .tnb-mobile-leg-path i{position:relative!important;z-index:1!important;background:#fff!important;padding:0 5px!important;color:#1f5e9b!important;font-size:12px!important}
    .tnb-mobile-leg-path small{position:relative!important;z-index:1!important;width:max-content!important;max-width:80px!important;margin:4px auto 0!important;padding:1px 4px!important;background:#fff!important;color:#168a5c!important;font-weight:900!important}

    .tnb-mobile-price-wrap{padding-top:10px!important}
    .tnb-mobile-price-wrap>*,.tnb-mobile-price-wrap [class*="col-"]{width:100%!important;max-width:100%!important;flex:0 0 100%!important;margin:0!important;text-align:center!important}
    .tnb-mobile-price-wrap a,.tnb-mobile-price-wrap button,.tnb-mobile-price-wrap .btn{min-height:46px!important;min-width:148px!important}

    .flights-list-item>.row.mt-3{margin:10px 0 0!important}
    .flights-list-item .accordion-body{padding:10px 0 0!important}
}
@media (min-width:761px){
    .tnb-mobile-search-toggle,.tnb-mobile-results-actions,.tnb-mobile-flight-summary{display:none!important}
}
</style>


<style id="fareshunt-flight-results-final-pass-v1">
/*
 * FaresHunt Flight Results — FINAL responsive/UI layer.
 * Purpose: resolve conflicts created by older V13/V14/V15/V16/V17/V18
 * presentation rules without changing search/API/fare/booking behavior.
 */

/* ---------- shared safety ---------- */
.tnb-listing-results-section,
.tnb-listing-results-section *{
    box-sizing:border-box;
}
.tnb-listing-results-section img{
    max-width:100%;
}
.tnb-flight-results-column,
.tnb-flight-results-column .result-step,
.tnb-flight-results-column .flights-list-item{
    min-width:0!important;
}

/* ---------- large desktop ---------- */
@media(min-width:1200px){
    .tnb-listing-results-section>.container{
        max-width:1420px!important;
        padding-left:18px!important;
        padding-right:18px!important;
    }

    .tnb-results-layout{
        display:grid!important;
        grid-template-columns:292px minmax(0,1fr)!important;
        gap:18px!important;
        align-items:start!important;
    }

    .tnb-filter-column{
        grid-column:1!important;
        width:auto!important;
        max-width:none!important;
    }

    .tnb-flight-results-column{
        grid-column:2!important;
        width:auto!important;
        max-width:none!important;
        margin:0!important;
    }

    .tnb-results-layout .filter-searchBar{
        position:sticky!important;
        top:86px!important;
    }
}

/* ---------- laptop / tablet landscape ---------- */
@media(min-width:992px) and (max-width:1199px){
    .tnb-listing-results-section>.container{
        max-width:100%!important;
        padding-left:14px!important;
        padding-right:14px!important;
    }

    .tnb-results-layout{
        display:grid!important;
        grid-template-columns:250px minmax(0,1fr)!important;
        gap:14px!important;
    }

    .tnb-filter-column,
    .tnb-flight-results-column{
        width:auto!important;
        max-width:none!important;
        flex:none!important;
        padding:0!important;
    }

    .result-step .tnb-itin-row{
        grid-template-columns:minmax(135px,1fr) 78px minmax(110px,.85fr) 78px 62px!important;
        gap:7px!important;
        padding:12px!important;
    }

    .result-step .tnb-price-col{
        min-width:165px!important;
    }

    .result-step .tnb-select-btn{
        width:120px!important;
    }
}

/* ---------- tablet portrait ---------- */
@media(min-width:761px) and (max-width:991px){
    .fhx-wrap{
        padding:12px 0!important;
    }

    .fhx-card{
        padding:13px!important;
        border-radius:16px!important;
    }

    .fhx-grid{
        display:grid!important;
        grid-template-columns:1fr 1fr!important;
        gap:9px!important;
    }

    .fhx-search-btn{
        grid-column:1/-1!important;
        min-height:50px!important;
    }

    .tnb-premium-top{
        margin-bottom:14px!important;
    }

    .tnb-fare-compare-scroll,
    .tnb-date-strip{
        -webkit-overflow-scrolling:touch;
        scrollbar-width:thin;
    }

    .tnb-results-layout{
        display:block!important;
        margin:0!important;
    }

    .tnb-filter-column,
    .tnb-flight-results-column{
        width:100%!important;
        max-width:100%!important;
        flex:0 0 100%!important;
        padding:0!important;
    }

    .tnb-filter-column{
        margin-bottom:14px!important;
    }

    .tnb-results-layout .filter-searchBar{
        position:static!important;
        max-height:none!important;
    }

    .tnb-flight-results-column{
        margin-top:0!important;
    }

    .result-step .tnb-itin-row{
        grid-template-columns:minmax(130px,1fr) 82px minmax(105px,.9fr) 82px!important;
        gap:8px!important;
        padding:12px!important;
    }

    .result-step .tnb-cabin{
        display:none!important;
    }

    .result-step .tnb-price-col{
        min-width:0!important;
        border-left:0!important;
        border-top:1px solid #e5ecf3!important;
    }

    .result-step .tnb-price-box{
        min-height:90px!important;
        display:grid!important;
        grid-template-columns:1fr 150px!important;
        align-items:center!important;
        gap:12px!important;
        text-align:left!important;
    }

    .result-step .tnb-select-btn{
        width:150px!important;
        margin:0!important;
    }
}

/* ---------- phone ---------- */
@media(max-width:760px){
    html,body{
        max-width:100%!important;
        overflow-x:hidden!important;
    }

    /* Listing page must not show the booking fare timer. */
.tnb-listing-results-section{
        padding:10px 0 28px!important;
        background:#f4f7fb!important;
    }

    .tnb-listing-results-section>.container{
        width:100%!important;
        max-width:100%!important;
        padding-left:9px!important;
        padding-right:9px!important;
    }

    /* compact modify-search header */
    .fhx-wrap{
        padding:9px 0!important;
        background:#f4f7fb!important;
    }

    .fhx-wrap>.container{
        padding-left:9px!important;
        padding-right:9px!important;
    }

    .fhx-card{
        padding:9px!important;
        border-radius:15px!important;
        box-shadow:0 7px 20px rgba(12,38,68,.05)!important;
    }

    .tnb-mobile-search-toggle{
        min-height:54px!important;
        padding:9px 11px!important;
        border-radius:12px!important;
    }

    .tnb-mobile-search-toggle strong{
        font-size:13px!important;
    }

    .tnb-mobile-search-action{
        font-size:10px!important;
        white-space:nowrap!important;
    }

    #form2:not(.tnb-mobile-search-open)>.fhx-top,
    #form2:not(.tnb-mobile-search-open)>#fhxMultiCityEditor,
    #form2:not(.tnb-mobile-search-open)>#fhxStandardGrid{
        display:none!important;
    }

    #form2.tnb-mobile-search-open>.fhx-top{
        display:flex!important;
        margin-top:9px!important;
    }

    #form2.tnb-mobile-search-open #fhxStandardGrid,
    #form2.tnb-mobile-search-open #fhxMultiCityEditor{
        margin-top:9px!important;
    }

    #form2.tnb-mobile-search-open .fhx-grid,
    #form2.tnb-mobile-search-open .fhx-grid-multicity{
        display:grid!important;
        grid-template-columns:1fr!important;
        gap:8px!important;
    }

    #form2.tnb-mobile-search-open .fhx-field,
    #form2.tnb-mobile-search-open .fhx-input,
    #form2.tnb-mobile-search-open .fhx-search-btn{
        width:100%!important;
        min-width:0!important;
    }

    #form2.tnb-mobile-search-open .fhx-trip{
        display:grid!important;
        grid-template-columns:repeat(3,minmax(0,1fr))!important;
        gap:5px!important;
        width:100%!important;
    }

    #form2.tnb-mobile-search-open .fhx-trip label{
        min-width:0!important;
        justify-content:center!important;
        padding-left:4px!important;
        padding-right:4px!important;
        font-size:10px!important;
    }

    /* Dense fare matrix and flexible-date widgets are intentionally desktop/tablet only.
       On phone, prioritize actual available flights. */
    .tnb-fare-compare,
    .tnb-price-calendar{
        display:none!important;
    }

    .tnb-premium-top{
        margin:0 0 10px!important;
    }

    .tnb-search-summary{
        min-height:0!important;
        margin:0 0 9px!important;
        padding:10px 11px!important;
        border-radius:13px!important;
        display:grid!important;
        grid-template-columns:1fr!important;
        gap:7px!important;
    }

    .tnb-search-summary strong{
        font-size:13px!important;
    }

    .tnb-search-summary span{
        font-size:9px!important;
        line-height:1.35!important;
    }

    .tnb-summary-date{
        width:100%!important;
        display:flex!important;
        justify-content:space-between!important;
        align-items:center!important;
        gap:7px!important;
        white-space:normal!important;
    }

    .tnb-change-search{
        margin-left:auto!important;
        padding:6px 10px!important;
    }

    .tnb-results-toolbar{
        display:flex!important;
        align-items:center!important;
        justify-content:space-between!important;
        gap:8px!important;
        margin:5px 0 7px!important;
    }

    .tnb-results-toolbar-left{
        min-width:0!important;
        display:flex!important;
        flex-wrap:wrap!important;
        gap:4px 8px!important;
    }

    .tnb-results-title{
        font-size:18px!important;
        line-height:1.15!important;
    }

    .tnb-results-toolbar small{
        font-size:8px!important;
    }

    /* Best / Cheapest / Fastest remains visible and compact. */
    .tnb-sortbar{
        display:grid!important;
        grid-template-columns:repeat(3,minmax(0,1fr))!important;
        margin:0 0 8px!important;
        min-height:48px!important;
        border-radius:12px!important;
    }

    .tnb-sortitem{
        min-height:48px!important;
        padding:7px 5px!important;
        align-items:center!important;
        text-align:center!important;
    }

    .tnb-sortitem b{
        font-size:10px!important;
        white-space:nowrap!important;
    }

    .tnb-sortitem span{
        display:none!important;
    }

    /* Mobile action bar */
    .tnb-mobile-results-actions{
        display:grid!important;
        grid-template-columns:1fr 1fr!important;
        gap:7px!important;
        margin:8px 0 10px!important;
    }

    .tnb-mobile-results-actions button{
        min-height:42px!important;
        border-radius:11px!important;
    }

    /* Filter drawer appears inline only when requested. */
    .tnb-results-layout{
        display:block!important;
        width:100%!important;
        margin:0!important;
    }

    .tnb-filter-column{
        display:none!important;
        width:100%!important;
        max-width:100%!important;
        padding:0!important;
        margin:0 0 10px!important;
    }

    .tnb-results-layout.tnb-mobile-filters-open .tnb-filter-column{
        display:block!important;
    }

    .tnb-results-layout .filter-searchBar{
        position:static!important;
        width:100%!important;
        max-height:none!important;
        border-radius:14px!important;
        box-shadow:0 5px 16px rgba(15,42,70,.05)!important;
    }

    .tnb-results-layout .filter-searchBar-head{
        min-height:50px!important;
        padding:0 14px!important;
    }

    .tnb-filter-title{
        font-size:14px!important;
    }

    .tnb-results-layout .filter-searchBar-body{
        display:block!important;
        max-height:none!important;
        overflow:visible!important;
        padding:0 14px 14px!important;
    }

    .tnb-extra-filter{
        padding:13px 0!important;
    }

    .label-filter-dpttime,
    .label-filter-rtntime{
        grid-template-columns:1fr 1fr!important;
        gap:7px!important;
    }

    .label-filter-dpttime label,
    .label-filter-rtntime label{
        min-height:46px!important;
        padding:7px 8px!important;
    }

    /* Results occupy full width, never a Bootstrap side column. */
    .tnb-flight-results-column{
        width:100%!important;
        max-width:100%!important;
        flex:0 0 100%!important;
        padding:0!important;
        margin:0!important;
    }

    .tnb-flight-results-column>.row{
        width:100%!important;
        margin:0!important;
        gap:10px!important;
    }

    .tnb-flight-results-column .result-step{
        width:100%!important;
        max-width:100%!important;
        flex:0 0 100%!important;
        padding:0!important;
        margin:0 0 10px!important;
    }

    .flights-list-item{
        width:100%!important;
        padding:0!important;
        border-radius:14px!important;
        overflow:hidden!important;
        box-shadow:0 5px 17px rgba(15,42,70,.055)!important;
    }

    /* Explicit mobile card is the only summary shown on phone. */
    .tnb-desktop-flight-summary{
        display:none!important;
    }

    .tnb-mobile-flight-summary{
        display:block!important;
        width:100%!important;
        padding:12px 12px 8px!important;
        overflow:hidden!important;
    }

    .tnb-mobile-airline{
        display:flex!important;
        align-items:center!important;
        gap:9px!important;
        padding:0 0 9px!important;
        border-bottom:1px solid #e8eef4!important;
    }

    .tnb-mobile-airline img,
    .tnb-mobile-airline>span{
        width:36px!important;
        height:36px!important;
        flex:0 0 36px!important;
        border-radius:8px!important;
    }

    .tnb-mobile-airline strong{
        font-size:12px!important;
    }

    .tnb-mobile-airline small{
        font-size:8.5px!important;
    }

    .tnb-mobile-leg{
        padding:9px 0!important;
        border-bottom:1px solid #e8eef4!important;
    }

    .tnb-mobile-leg-head{
        display:flex!important;
        justify-content:space-between!important;
        align-items:center!important;
        gap:8px!important;
        margin-bottom:8px!important;
    }

    .tnb-mobile-leg-head span{
        flex:0 0 auto!important;
        font-size:8px!important;
    }

    .tnb-mobile-leg-head strong{
        min-width:0!important;
        font-size:11px!important;
        text-align:right!important;
        overflow:hidden!important;
        text-overflow:ellipsis!important;
        white-space:nowrap!important;
    }

    .tnb-mobile-leg-times{
        display:grid!important;
        grid-template-columns:minmax(0,1fr) 70px minmax(0,1fr)!important;
        gap:7px!important;
        align-items:center!important;
    }

    .tnb-mobile-leg-times b{
        font-size:16px!important;
        line-height:1!important;
        white-space:nowrap!important;
    }

    .tnb-mobile-leg-times small{
        font-size:8px!important;
    }

    .tnb-mobile-leg-path{
        min-width:0!important;
    }

    .tnb-mobile-leg-path small{
        max-width:70px!important;
        font-size:7.5px!important;
    }

    /* Reuse existing price component but normalize its geometry. */
    .tnb-mobile-price-wrap{
        padding:10px 0 2px!important;
        overflow:hidden!important;
    }

    .tnb-mobile-price-wrap>*,
    .tnb-mobile-price-wrap .row,
    .tnb-mobile-price-wrap [class*="col-"]{
        width:100%!important;
        max-width:100%!important;
        flex:0 0 100%!important;
        margin-left:0!important;
        margin-right:0!important;
        padding-left:0!important;
        padding-right:0!important;
    }

    .tnb-mobile-price-wrap .tnb-price-col{
        border:0!important;
        min-width:0!important;
    }

    .tnb-mobile-price-wrap .tnb-price-box{
        min-height:0!important;
        padding:5px 0!important;
        display:grid!important;
        grid-template-columns:minmax(0,1fr) 132px!important;
        align-items:center!important;
        gap:10px!important;
        text-align:left!important;
    }

    .tnb-mobile-price-wrap .tnb-price-box strong{
        font-size:22px!important;
    }

    .tnb-mobile-price-wrap .tnb-select-btn,
    .tnb-mobile-price-wrap .btn-primary{
        width:132px!important;
        min-width:0!important;
        min-height:44px!important;
        margin:0!important;
    }

    /* Flight Detail bar always visible; body only appears when Bootstrap opens it. */
    .result-step .accordion{
        margin:0!important;
        padding:0!important;
        border-top:1px solid #e8eef4!important;
    }

    .result-step .accordion-button{
        min-height:42px!important;
        padding:0 13px!important;
        font-size:10px!important;
    }

    .result-step .accordion-body{
        padding:0!important;
        overflow:hidden!important;
    }

    .tnb-inline-details{
        width:100%!important;
        padding:0!important;
        overflow:hidden!important;
    }

    .tnb-detail-journey{
        width:100%!important;
        min-width:0!important;
    }

    .tnb-detail-journey-head{
        display:flex!important;
        flex-wrap:wrap!important;
        gap:5px!important;
        padding:10px 12px!important;
        font-size:10px!important;
    }

    .tnb-detail-segment{
        width:100%!important;
        min-width:0!important;
        padding:12px!important;
    }

    .tnb-detail-segment-main{
        min-width:0!important;
    }

    /* Generic protection for provider segment tables. */
    .airLines-fullsegment,
    .airLines-fullsegment table,
    .airLines-fullsegment tbody,
    .airLines-fullsegment tr{
        max-width:100%!important;
    }

    .airLines-fullsegment{
        padding:10px 12px!important;
        overflow-x:auto!important;
        -webkit-overflow-scrolling:touch!important;
    }

    .airLines-fullsegment table{
        min-width:620px!important;
    }

    /* Multi-City mobile cards */
    .tnb-multicity-card-leg{
        padding:10px 0!important;
    }

    .tnb-mc-leg-main{
        display:grid!important;
        grid-template-columns:1fr 1fr!important;
        gap:9px!important;
    }

    .tnb-mc-airline{
        grid-column:1/-1!important;
    }

    .tnb-mc-route-line{
        grid-column:1/-1!important;
        padding-top:4px!important;
    }

    .tnb-mc-time strong{
        font-size:16px!important;
    }
}

/* Small phones */
@media(max-width:390px){
    .tnb-listing-results-section>.container,
    .fhx-wrap>.container{
        padding-left:7px!important;
        padding-right:7px!important;
    }

    .tnb-mobile-leg-times{
        grid-template-columns:minmax(0,1fr) 62px minmax(0,1fr)!important;
        gap:5px!important;
    }

    .tnb-mobile-leg-times b{
        font-size:15px!important;
    }

    .tnb-mobile-price-wrap .tnb-price-box{
        grid-template-columns:minmax(0,1fr) 120px!important;
        gap:7px!important;
    }

    .tnb-mobile-price-wrap .tnb-select-btn,
    .tnb-mobile-price-wrap .btn-primary{
        width:120px!important;
    }
}

/* Desktop/tablet use the original card renderer. */
@media(min-width:761px){
    .tnb-mobile-search-toggle,
    .tnb-mobile-results-actions,
    .tnb-mobile-flight-summary{
        display:none!important;
    }

    .tnb-desktop-flight-summary{
        display:flex!important;
    }
}
</style>

@endsection

@section('mid-content')

@php $setting = getAllSetting(); @endphp

    <!-- FaresHunt Listing Search Bar -->
    <div class="fhx-wrap">
        <div class="container">
            <div class="fhx-card" id="fhxSearchCard">
                <form name="form2" id="form2" action="{{ url('submit-search') }}" method="post">
                    @csrf

                    <button type="button" class="tnb-mobile-search-toggle" id="tnbMobileSearchToggle" aria-expanded="false">
                        <span>
                            <small>Current search</small>
                            @if(($fields['FlightType'] ?? '') === 'multi_city')
                                <strong>Multi-City · {{ count((array)($fields['SearchLegs'] ?? [])) }} flights</strong>
                            @else
                                <strong>{{ $fields['IATAFrom'] ?? '' }} → {{ $fields['IATATo'] ?? '' }}</strong>
                            @endif
                        </span>
                        <span class="tnb-mobile-search-action"><i class="fa-solid fa-pen"></i> Modify</span>
                    </button>

                    <div class="fhx-top">
                        <div class="fhx-trip">
                            <label for="flight_types1">
                                <input type="radio" name="flight_types" id="flight_types1" value="round_trip"
                                    <?php echo (($fields['FlightType'] ?? '') === 'round_trip') ? 'checked' : ''; ?>
                                    onclick="fhxSetTrip(this.value)">
                                <span>Return</span>
                            </label>
                            <label for="flight_types2">
                                <input type="radio" name="flight_types" id="flight_types2" value="one_way"
                                    <?php echo (($fields['FlightType'] ?? '') === 'one_way') ? 'checked' : ''; ?>
                                    onclick="fhxSetTrip(this.value)">
                                <span>One Way</span>
                            </label>
                            <label for="flight_types3">
                                <input type="radio" name="flight_types" id="flight_types3" value="multi_city"
                                    <?php echo (($fields['FlightType'] ?? '') === 'multi_city') ? 'checked' : ''; ?>
                                    onclick="fhxSetTrip(this.value)">
                                <span>Multi-City</span>
                            </label>
                        </div>
                        <div class="fhx-secure"><i class="fa-solid fa-shield-halved me-1"></i>Secure flight search</div>
                    </div>

                    @php
                        $isMultiCitySearch = (($fields['FlightType'] ?? '') === 'multi_city');
                        $resultMultiLegs = (array)($fields['SearchLegs'] ?? []);
                        if (count($resultMultiLegs) < 2) {
                            $resultMultiLegs = [
                                [
                                    'IATAFromFull' => $fields['IATAFromFull'] ?? '',
                                    'IATAToFull' => $fields['IATAToFull'] ?? '',
                                    'Date' => $fields['DeptDate'] ?? '',
                                ],
                                [
                                    'IATAFromFull' => '',
                                    'IATAToFull' => '',
                                    'Date' => '',
                                ],
                            ];
                        }
                    @endphp

                    <div class="fhx-multicity-editor" id="fhxMultiCityEditor" @if(!$isMultiCitySearch) hidden @endif>
                        <div class="fhx-multicity-head">
                            <div>
                                <strong>Multi-City itinerary</strong>
                                <span>2 to 5 flights · each flight is searched as one itinerary</span>
                            </div>
                            <span class="fhx-multicity-badge"><i class="fa-solid fa-route"></i> Multi-City</span>
                        </div>

                        <div id="fhxMultiCityLegs">
                            @foreach($resultMultiLegs as $mcIndex => $mcLeg)
                                @php
                                    $mcLeg = is_object($mcLeg) ? (array)$mcLeg : (array)$mcLeg;
                                    $mcFrom = $mcLeg['IATAFromFull'] ?? ($mcLeg['IATAFrom'] ?? '');
                                    $mcTo = $mcLeg['IATAToFull'] ?? ($mcLeg['IATATo'] ?? '');
                                    $mcDate = !empty($mcLeg['Date']) ? date('Y-m-d', strtotime(str_replace('.','-',$mcLeg['Date']))) : '';
                                @endphp
                                <div class="fhx-multi-leg" data-fhx-multi-leg>
                                    <div class="fhx-multi-leg-title">
                                        <span class="fhx-multi-number">{{ $mcIndex + 1 }}</span>
                                        <strong>Flight {{ $mcIndex + 1 }}</strong>
                                    </div>

                                    <div class="fhx-field fhx-airport-field">
                                        <label class="fhx-label">From</label>
                                        <input class="fhx-input" data-fhx-multi-airport type="text" name="multi_from[]"
                                            value="{{ $mcFrom }}" placeholder="City or airport code" autocomplete="off"
                                            @if(!$isMultiCitySearch) disabled @endif>
                                        <i class="fa-solid fa-plane-departure fhx-airport-symbol"></i>
                                        <div class="fhx-airport-list" data-fhx-multi-list></div>
                                    </div>

                                    <div class="fhx-field fhx-airport-field">
                                        <label class="fhx-label">To</label>
                                        <input class="fhx-input" data-fhx-multi-airport type="text" name="multi_to[]"
                                            value="{{ $mcTo }}" placeholder="City or airport code" autocomplete="off"
                                            @if(!$isMultiCitySearch) disabled @endif>
                                        <i class="fa-solid fa-plane-arrival fhx-airport-symbol"></i>
                                        <div class="fhx-airport-list" data-fhx-multi-list></div>
                                    </div>

                                    <div class="fhx-field">
                                        <label class="fhx-label">Depart</label>
                                        <input class="fhx-input" data-fhx-multi-date type="text" name="multi_date[]"
                                            value="{{ $mcDate }}" autocomplete="off"
                                            @if(!$isMultiCitySearch) disabled @endif>
                                    </div>

                                    <button type="button" class="fhx-multi-remove" data-fhx-remove-leg aria-label="Remove flight">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <button type="button" class="fhx-add-flight" id="fhxAddMultiLeg">
                            <i class="fa-solid fa-plus"></i> Add another flight
                        </button>
                    </div>

                    @php
                        // 13 Nov 2026 (search box): city + airport name shown under the airport code.
                        // Display only: the input value (code first) and the search itself are unchanged.
                        $fhxApSub = function ($full, $code) {
                            $full = trim((string) $full);
                            $code = strtoupper(trim((string) $code));
                            if ($code === '' && $full !== '') $code = strtoupper(substr($full, 0, 3));
                            if (!preg_match('/^[A-Z]{3}$/', $code)) return '';
                            try {
                                $ap = \App\Models\Airport::with('city')->where('code', $code)->first();
                            } catch (\Throwable $fhxApErr) {
                                return '';
                            }
                            if (!$ap) return '';
                            $city = trim((string) optional($ap->city)->name);
                            $name = trim((string) $ap->name);
                            // "LAX - Los Angeles International Airport" already shows the airport name: add only the city.
                            if (strpos($full, ' - ') !== false) return $city;
                            return implode(' · ', array_values(array_filter([$city, $name], 'strlen')));
                        };
                    @endphp

                    <div class="fhx-grid {{ $isMultiCitySearch ? 'fhx-grid-multicity' : '' }}" id="fhxStandardGrid">
                        <div class="fhx-field fhx-airport-field fhx-standard-route-field">
                            <label class="fhx-label" for="location_from">From</label>
                            <input class="fhx-input" type="text" name="location_from" id="location_from" required
                                value="{{ @$fields['IATAFromFull'] }}" placeholder="City or airport code" autocomplete="off"
                                @if($isMultiCitySearch) disabled @endif>
                            <i class="fa-solid fa-plane-departure fhx-airport-symbol"></i>
                            <div class="fhx-airport-list" id="fhxFromList"></div>
                            {{-- 13 Nov 2026 (search box): city/airport name + swap button --}}
                            <span class="fhx-ap-sub" id="fhxFromSub">{{ $fhxApSub($fields['IATAFromFull'] ?? '', $fields['IATAFrom'] ?? '') }}</span>
                            <button type="button" class="fhx-swap" id="fhxSwapRoute" aria-label="Swap From and To" title="Swap From and To"><i class="fa-solid fa-arrow-right-arrow-left"></i></button>
                        </div>

                        <div class="fhx-field fhx-airport-field fhx-standard-route-field">
                            <label class="fhx-label" for="location_to">To</label>
                            <input class="fhx-input" type="text" name="location_to" id="location_to" required
                                value="{{ @$fields['IATAToFull'] }}" placeholder="City or airport code" autocomplete="off"
                                @if($isMultiCitySearch) disabled @endif>
                            <i class="fa-solid fa-plane-arrival fhx-airport-symbol"></i>
                            <div class="fhx-airport-list" id="fhxToList"></div>
                            <span class="fhx-ap-sub" id="fhxToSub">{{ $fhxApSub($fields['IATAToFull'] ?? '', $fields['IATATo'] ?? '') }}</span>
                        </div>

                        <div class="fhx-field fhx-standard-route-field">
                            <label class="fhx-label" for="depature_date">Depart</label>
                            <input class="fhx-input" type="text" name="depature_date" id="depature_date" required
                                value="{{ date('Y-m-d',strtotime($fields['DeptDate'])) }}" autocomplete="off"
                                @if($isMultiCitySearch) disabled @endif>
                        </div>

                        <div class="fhx-field fhx-standard-route-field <?php echo (($fields['FlightType'] ?? '') === 'one_way') ? 'fhx-return-disabled' : ''; ?>" id="fhxReturnField">
                            <label class="fhx-label" for="return_date">Return</label>
                            <input class="fhx-input" type="text" name="return_date" id="return_date" autocomplete="off"
                                <?php if (!empty($fields['RetnDate'])): ?> value="{{ date('Y-m-d',strtotime($fields['RetnDate'])) }}" <?php endif; ?>
                                <?php echo in_array(($fields['FlightType'] ?? ''), ['one_way','multi_city'], true) ? 'disabled' : ''; ?>>
                        </div>

                        <div class="fhx-field">
                            <span class="fhx-label">Travelers</span>
                            <button class="fhx-pax-btn" type="button" id="fhx-passenger-count" onclick="fhxTogglePassengerBox()">
                                <span class="fhx-pax-line">
                                    <span><i class="fa-solid fa-user-group me-1"></i><span id="fhxTravelerCount">1</span> Traveler<span id="fhxTravelerPlural"></span></span>
                                    <i class="fa-solid fa-chevron-down"></i>
                                </span>
                                <span class="fhx-pax-sub" id="fhxTravelerBreakdown">1 Adult</span>
                            </button>

                            <div id="passenger-box" class="passenger-box">
                                <div class="mb-2">
                                    <label for="adult-count" class="form-label">Adults</label>
                                    <select id="adult-count" name="adult" class="form-select" onchange="fhxUpdatePassengerCount()" required>
                                        <option value="1" <?php echo ((int)($fields['Adults'] ?? 1) === 1) ? 'selected' : ''; ?>>1</option>
                                        <option value="2" <?php echo ((int)($fields['Adults'] ?? 1) === 2) ? 'selected' : ''; ?>>2</option>
                                        <option value="3" <?php echo ((int)($fields['Adults'] ?? 1) === 3) ? 'selected' : ''; ?>>3</option>
                                        <option value="4" <?php echo ((int)($fields['Adults'] ?? 1) === 4) ? 'selected' : ''; ?>>4</option>
                                        <option value="5" <?php echo ((int)($fields['Adults'] ?? 1) === 5) ? 'selected' : ''; ?>>5</option>
                                        <option value="6" <?php echo ((int)($fields['Adults'] ?? 1) === 6) ? 'selected' : ''; ?>>6</option>
                                        <option value="7" <?php echo ((int)($fields['Adults'] ?? 1) === 7) ? 'selected' : ''; ?>>7</option>
                                        <option value="8" <?php echo ((int)($fields['Adults'] ?? 1) === 8) ? 'selected' : ''; ?>>8</option>
                                        <option value="9" <?php echo ((int)($fields['Adults'] ?? 1) === 9) ? 'selected' : ''; ?>>9</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label for="child-count" class="form-label">Children</label>
                                    <select id="child-count" name="children" class="form-select" onchange="fhxUpdatePassengerCount()" required>
                                        <option value="0" <?php echo ((int)($fields['Childrens'] ?? 0) === 0) ? 'selected' : ''; ?>>0</option>
                                        <option value="1" <?php echo ((int)($fields['Childrens'] ?? 0) === 1) ? 'selected' : ''; ?>>1</option>
                                        <option value="2" <?php echo ((int)($fields['Childrens'] ?? 0) === 2) ? 'selected' : ''; ?>>2</option>
                                        <option value="3" <?php echo ((int)($fields['Childrens'] ?? 0) === 3) ? 'selected' : ''; ?>>3</option>
                                        <option value="4" <?php echo ((int)($fields['Childrens'] ?? 0) === 4) ? 'selected' : ''; ?>>4</option>
                                        <option value="5" <?php echo ((int)($fields['Childrens'] ?? 0) === 5) ? 'selected' : ''; ?>>5</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="infant-count" class="form-label">Infants</label>
                                    <select id="infant-count" name="infant" class="form-select" onchange="fhxUpdatePassengerCount()" required>
                                        <option value="0" <?php echo ((int)($fields['Infants'] ?? 0) === 0) ? 'selected' : ''; ?>>0</option>
                                        <option value="1" <?php echo ((int)($fields['Infants'] ?? 0) === 1) ? 'selected' : ''; ?>>1</option>
                                        <option value="2" <?php echo ((int)($fields['Infants'] ?? 0) === 2) ? 'selected' : ''; ?>>2</option>
                                        <option value="3" <?php echo ((int)($fields['Infants'] ?? 0) === 3) ? 'selected' : ''; ?>>3</option>
                                        <option value="4" <?php echo ((int)($fields['Infants'] ?? 0) === 4) ? 'selected' : ''; ?>>4</option>
                                        <option value="5" <?php echo ((int)($fields['Infants'] ?? 0) === 5) ? 'selected' : ''; ?>>5</option>
                                    </select>
                                </div>
                                <button class="btn btn-primary w-100" type="button" onclick="fhxClosePassengerBox()">Done</button>
                            </div>
                        </div>

                        <div class="fhx-field">
                            <label class="fhx-label" for="cabin_classes">Cabin</label>
                            <select class="fhx-select" name="cabin_classes" id="cabin_classes" required>
                                <option value="Econom" <?php echo (($fields['FlightClass'] ?? '') === 'Econom') ? 'selected' : ''; ?>>Economy</option>
                                <option value="PremiumEconomy" <?php echo (($fields['FlightClass'] ?? '') === 'PremiumEconomy') ? 'selected' : ''; ?>>Premium Economy</option>
                                <option value="Business" <?php echo (($fields['FlightClass'] ?? '') === 'Business') ? 'selected' : ''; ?>>Business</option>
                                <option value="First" <?php echo (($fields['FlightClass'] ?? '') === 'First') ? 'selected' : ''; ?>>First Class</option>
                            </select>
                        </div>

                        <button class="fhx-search-btn" type="submit">
                            <i class="fa-solid fa-magnifying-glass me-2"></i>Search
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    @if($output && $status=='1')

        @php
            /*
             * Build comparison data ONLY from the live search results already returned
             * by the existing API. No fare is invented.
             */
            $resultRows = collect($output['result'] ?? []);

            $resolveDisplayedFare = function($r) {
                $candidates = [
                    $r['DisplayedAdultPrice'] ?? null,
                    $r['FinalAdultPrice'] ?? null,
                    $r['FinalPrice'] ?? null,
                    $r['DisplayPrice'] ?? null,
                    $r['TotalAdultFare'] ?? null,
                    (($r['Is_Markup'] ?? 'No') === 'No'
                        ? ($r['AdultPrice'] ?? null)
                        : ($r['MinMarkup']['New_AdultPrice'] ?? $r['AdultPrice'] ?? null))
                ];
                foreach ($candidates as $v) {
                    if (is_numeric($v) && (float)$v > 0) return (float)$v;
                }
                return 0.0;
            };

            $lowestDisplayedFare = $resultRows
                ->map(fn($r)=>$resolveDisplayedFare($r))
                ->filter(fn($v)=>$v > 0)
                ->min();

            $airlineStats = [];
            $fareMatrix = [];

            // 13 Nov 2026: the matrix is built from the same first 30 flights that are shown on the page,
            // so every price in it can be clicked and matches a visible card (the page JS recounts visible cards).
            $matrixRows = array_slice($output['result'] ?? [], 0, 30, true);

            // Exact outbound stop counts available in this live search.
            $availableStopCounts = collect($matrixRows)
                ->map(fn($r)=>(int)($r['DptFlightStop'] ?? 0))
                ->unique()
                ->sort()
                ->values()
                ->all();

            foreach ($matrixRows as $r) {
                $outboundSegments = $r['AllFlight']['Rph_1'] ?? [];
                $returnSegments   = $r['AllFlight']['Rph_2'] ?? [];

                // Multi-City can contain Rph_1...Rph_N. For listing/header statistics
                // include every returned journey leg while preserving the existing
                // One Way / Round Trip behavior.
                if (($fields['FlightType'] ?? '') === 'multi_city') {
                    $allSegments = [];
                    $allRphForStats = $r['AllFlight'] ?? [];
                    uksort($allRphForStats, function ($a, $b) {
                        return (int)str_replace('Rph_', '', $a) <=> (int)str_replace('Rph_', '', $b);
                    });
                    foreach ($allRphForStats as $rphSegmentsForStats) {
                        foreach ((array)$rphSegmentsForStats as $segmentForStats) {
                            $allSegments[] = $segmentForStats;
                        }
                    }
                } else {
                    $allSegments = array_merge($outboundSegments, $returnSegments);
                }

                // Build header airlines from every airline actually present in this live result.
                foreach ($allSegments as $segment) {
                    $segmentCode = strtoupper((string)($segment['Airline'] ?? ''));
                    if (!$segmentCode) continue;

                    if (!isset($airlineStats[$segmentCode])) {
                        $airlineStats[$segmentCode] = [
                            'code'  => $segmentCode,
                            'name'  => $segment['AirlineName'] ?? $segmentCode,
                            'logo'  => $segment['AirlineLogo'] ?? '',
                            'count' => 0,
                        ];
                    }
                    $airlineStats[$segmentCode]['count']++;
                }

                // Fare matrix remains tied to the outbound marketing carrier for the itinerary.
                $seg = $outboundSegments[0] ?? [];
                $airlineCode = strtoupper((string)($seg['Airline'] ?? ''));
                if (!$airlineCode) continue;

                $stops = (int)($r['DptFlightStop'] ?? 0);
                $bucket = 'stop_'.$stops;

                /*
                 * Use the SAME displayed-price basis everywhere on this page.
                 * First prefer explicit final/displayed fare keys when a provider
                 * returns them; then fall back to the existing markup/adult fare.
                 */
                $priceCandidates = [
                    $r['DisplayedAdultPrice'] ?? null,
                    $r['FinalAdultPrice'] ?? null,
                    $r['FinalPrice'] ?? null,
                    $r['DisplayPrice'] ?? null,
                    $r['TotalAdultFare'] ?? null,
                    (($r['Is_Markup'] ?? 'No') === 'No'
                        ? ($r['AdultPrice'] ?? null)
                        : ($r['MinMarkup']['New_AdultPrice'] ?? $r['AdultPrice'] ?? null))
                ];

                $price = 0.0;
                foreach ($priceCandidates as $candidatePrice) {
                    if (is_numeric($candidatePrice) && (float)$candidatePrice > 0) {
                        $price = (float)$candidatePrice;
                        break;
                    }
                }

                if ($price > 0 && (!isset($fareMatrix[$bucket][$airlineCode]) || $price < $fareMatrix[$bucket][$airlineCode])) {
                    $fareMatrix[$bucket][$airlineCode] = $price;
                }
            }

            // 13 Nov 2026: columns = airlines that really have a fare in the matrix, cheapest first
            // (before: airlines counted from every segment, incl. connecting/return carriers -> empty columns).
            $pricedAirlineMin = [];
            foreach ($fareMatrix as $bucketFares) {
                foreach ($bucketFares as $pCode => $pFare) {
                    if (!isset($pricedAirlineMin[$pCode]) || $pFare < $pricedAirlineMin[$pCode]) {
                        $pricedAirlineMin[$pCode] = $pFare;
                    }
                }
            }
            asort($pricedAirlineMin);
            $topAirlines = [];
            foreach (array_slice(array_keys($pricedAirlineMin), 0, 5) as $pCode) {
                if (isset($airlineStats[$pCode])) {
                    $topAirlines[$pCode] = $airlineStats[$pCode];
                }
            }
            // Only stop rows that have at least one fare among the shown airlines.
            $availableStopCounts = array_values(array_filter($availableStopCounts, function ($sc) use ($fareMatrix, $topAirlines) {
                foreach (array_keys($topAirlines) as $pCode) {
                    if (isset($fareMatrix['stop_'.$sc][$pCode])) return true;
                }
                return false;
            }));

            $selectedDeparture = \Carbon\Carbon::createFromFormat('d.m.Y',$fields['DeptDate']);
            $selectedReturn = !empty($fields['RetnDate'])
                ? \Carbon\Carbon::createFromFormat('d.m.Y',$fields['RetnDate'])
                : null;
            $tripDays = $selectedReturn ? $selectedDeparture->diffInDays($selectedReturn) : 0;
            $calendarDates = collect(range(-3,10))->map(fn($offset)=>$selectedDeparture->copy()->addDays($offset));
        @endphp

        <section class="gray-simple tnb-listing-results-section">
            <div class="container">

                <!-- Full-width premium fare intelligence area -->
                <div class="tnb-premium-top">
                        @if(($fields['FlightType'] ?? '') !== 'multi_city' && count($topAirlines))
                        <div class="tnb-fare-compare" id="tnbFareCompare">
                            <div class="tnb-fare-overview-head">
                                <div>
                                    <h3>Compare Fares</h3>
                                    <p>Lowest fares among the flights listed below, by airline and outbound stops.</p>
                                </div>
                                <button type="button" class="tnb-compare-link" id="tnbFareReset">
                                    Show all flights <i class="fa-solid fa-chevron-right ms-1"></i>
                                </button>
                            </div>

                            <div class="tnb-fare-compare-scroll">
                                <div class="tnb-fare-matrix" style="--airline-count:{{ count($topAirlines) }}">
                                    <div class="tnb-fare-cell tnb-fare-label tnb-fare-head">
                                        <button type="button" class="tnb-show-all-fares" id="tnbFareResetMatrix">Show All Fares</button>
                                    </div>

                                    @foreach($topAirlines as $airline)
                                        <div class="tnb-fare-cell tnb-fare-head">
                                            @if($airline['logo'])
                                                <img src="{{ $airline['logo'] }}" alt="{{ $airline['name'] }}" onerror="this.style.display='none'">
                                            @endif
                                            <strong>{{ $airline['name'] }}</strong>
                                            <span class="tnb-airline-code">{{ $airline['code'] }}</span>
                                        </div>
                                    @endforeach

                                    @foreach($availableStopCounts as $stopCount)
                                        @php
                                            $bucket = 'stop_'.$stopCount;
                                            $stopLabel = $stopCount === 0
                                                ? 'Non Stop'
                                                : ($stopCount === 1 ? '1 Stop' : $stopCount.' Stops');
                                        @endphp

                                        <div class="tnb-fare-cell tnb-fare-label">{{ $stopLabel }}</div>

                                        @foreach($topAirlines as $airline)
                                            @php $matrixFare = $fareMatrix[$bucket][$airline['code']] ?? null; @endphp
                                            <div class="tnb-fare-cell"
                                                 data-matrix-airline="{{ $airline['code'] }}"
                                                 data-matrix-stops="{{ $stopCount }}">
                                                @if($matrixFare)
                                                    <button
                                                        type="button"
                                                        class="tnb-fare-filter"
                                                        data-airline="{{ $airline['code'] }}"
                                                        data-stops="{{ $stopCount }}"
                                                        data-label="{{ $airline['name'] }} · {{ $stopLabel }}"
                                                    >
                                                        <span class="tnb-fare-price">${{ number_format($matrixFare,0) }}</span>
                                                        <span class="tnb-fare-action">View flights</span>
                                                    </button>
                                                @else
                                                    <span class="tnb-fare-empty">---</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>
                            </div>

                            <p class="tnb-fare-note">
                                <i class="fa-solid fa-shield-halved"></i>
                                <span>Only fares returned by this live search are shown. Taxes &amp; fees are based on the currently displayed fare. Airline conditions and baggage rules may vary.</span>
                            </p>
                        </div>
                        @endif

                        @if(($fields['FlightType'] ?? '') !== 'multi_city')
                        <div class="tnb-price-calendar">
                            <div class="tnb-price-calendar-head">
                                <div class="tnb-price-calendar-title">
                                    <span class="label"><i class="fa-regular fa-calendar me-1"></i>Flexible Dates</span>
                                    <strong>{{ $fields['IATAFrom'] }} → {{ $fields['IATATo'] }}</strong>
                                </div>
                                <div class="tnb-price-calendar-head-right">
                                    <span>Choose another date to run a fresh live fare search</span>
                                </div>
                            </div>

                            <div class="tnb-date-strip">
                                @foreach($calendarDates as $calDate)
                                    @php $isSelected = $calDate->isSameDay($selectedDeparture); @endphp
                                    <button
                                        type="button"
                                        class="tnb-date-pill {{ $isSelected ? 'active' : '' }}"
                                        data-search-date="{{ $calDate->format('Y-m-d') }}"
                                        data-trip-days="{{ $tripDays }}"
                                    >
                                        <small>{{ $calDate->format('D') }}</small>
                                        <b>{{ $calDate->format('M d') }}</b>

                                        @if($isSelected && $lowestDisplayedFare)
                                            <em>${{ number_format($lowestDisplayedFare,0) }}</em>
                                            <span class="selected-label">Selected</span>
                                        @else
                                            <span class="check">Live check</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div class="tnb-search-summary">
                            <div>
                                @if(($fields['FlightType'] ?? '') === 'multi_city')
                                    <strong>Multi-City · {{ count((array)($fields['SearchLegs'] ?? [])) }} flights</strong>
                                    <span>{{ (@$fields['Adults'] + @$fields['Childrens'] + @$fields['Infants']) ?: 1 }} Traveler(s) · {{ ['Econom'=>'Economy','Peconom'=>'Premium Economy','firstclass'=>'First','Business'=>'Business'][@$fields['FlightClass']] ?? @$fields['FlightClass'] }}</span>
                                    <div class="tnb-multicity-summary-routes">
                                        @foreach((array)($fields['SearchLegs'] ?? []) as $summaryIndex => $summaryLeg)
                                            @php $summaryLeg = is_object($summaryLeg) ? (array)$summaryLeg : (array)$summaryLeg; @endphp
                                            <em>Flight {{ $summaryIndex+1 }} · {{ $summaryLeg['IATAFrom'] ?? '' }} → {{ $summaryLeg['IATATo'] ?? '' }} · {{ !empty($summaryLeg['Date']) ? date('M d',strtotime(str_replace('.','-',$summaryLeg['Date']))) : '' }}</em>
                                        @endforeach
                                    </div>
                                @else
                                    <strong>{{ @$fields['IATAFromFull'] ?: @$fields['IATAFrom'] }} → {{ @$fields['IATAToFull'] ?: @$fields['IATATo'] }}</strong>
                                    <span>{{ (@$fields['Adults'] + @$fields['Childrens'] + @$fields['Infants']) ?: 1 }} Traveler(s) · {{ ['Econom'=>'Economy','Peconom'=>'Premium Economy','firstclass'=>'First','Business'=>'Business'][@$fields['FlightClass']] ?? @$fields['FlightClass'] }} · {{ ($fields['FlightType']??'')==='round_trip' ? 'Round Trip' : 'One Way' }}</span>
                                @endif
                            </div>

                            <div class="tnb-summary-date">
                                <i class="fa-regular fa-calendar"></i>
                                @if(($fields['FlightType'] ?? '') === 'multi_city')
                                    <span>{{ count((array)($fields['SearchLegs'] ?? [])) }}-flight itinerary</span>
                                @else
                                    <span>{{ date('D, M d',strtotime($fields['DeptDate'])) }}@if(!empty($fields['RetnDate'])) → {{ date('D, M d',strtotime($fields['RetnDate'])) }}@endif</span>
                                @endif
                                <button type="button" class="tnb-change-search" onclick="document.getElementById('form2')?.scrollIntoView({behavior:'smooth',block:'center'})">Change search</button>
                            </div>
                        </div>

                        <div class="tnb-filter-state" id="tnbFareFilterState">
                            <i class="fa-solid fa-filter"></i>
                            <span id="tnbFareFilterLabel">Filtered fares</span>
                            <button type="button" id="tnbClearFareFilter">Clear filter</button>
                        </div>

                        <div class="tnb-results-toolbar">
                            <div class="tnb-results-toolbar-left">
                                <h5 class="tnb-results-title" id="flight-count">{{ @$output['Search-ResultCount'] }} flight options</h5>
                                <small id="tnbResultContext">Prices include displayed taxes &amp; fees</small>
                            </div>
                        </div>

                        <div class="tnb-sortbar" id="tnbSortBar">
                            <button class="tnb-sortitem active" type="button" data-sort="best">
                                <b><i class="fa-solid fa-medal me-2"></i>Best</b>
                                <span>Balanced price &amp; duration</span>
                            </button>
                            <button class="tnb-sortitem" type="button" data-sort="cheap">
                                <b><i class="fa-solid fa-dollar-sign me-2"></i>Cheapest</b>
                                <span>Lowest displayed live fare</span>
                            </button>
                            <button class="tnb-sortitem" type="button" data-sort="fast">
                                <b><i class="fa-regular fa-clock me-2"></i>Fastest</b>
                                <span>Shortest flying time</span>
                            </button>
                        </div>
                </div>

                <div class="tnb-mobile-results-actions">
                    <button type="button" id="tnbMobileFiltersToggle"><i class="fa-solid fa-sliders"></i> Filters</button>
                    <button type="button" onclick="document.getElementById('tnbSortBar')?.scrollIntoView({behavior:'smooth',block:'start'})"><i class="fa-solid fa-arrow-up-short-wide"></i> Sort</button>
                </div>

                <!-- Filters + actual flight results -->
                <div class="row justify-content-between gy-4 gx-xl-4 gx-lg-3 gx-md-3 gx-4 tnb-results-layout">

                    <!-- Sticky Filters -->
                    @php
                        $resultPrices = collect($output['result'] ?? [])
                            ->map(fn($r)=>$resolveDisplayedFare($r))
                            ->filter(fn($v)=>$v>0);

                        $resultDurations = collect($output['result'] ?? [])->map(function($r) use ($fields){
                            if (($fields['FlightType'] ?? '') === 'multi_city') {
                                return collect($r['AllFlight'] ?? [])->sum(function($segments){
                                    return collect($segments ?? [])->sum(fn($f)=>(int)($f['FlightMinutes'] ?? 0));
                                });
                            }

                            return collect($r['AllFlight']['Rph_1'] ?? [])->sum(fn($f)=>(int)($f['FlightMinutes'] ?? 0))
                                + collect($r['AllFlight']['Rph_2'] ?? [])->sum(fn($f)=>(int)($f['FlightMinutes'] ?? 0));
                        })->filter(fn($v)=>$v>0);

                        $filterMinPrice = (int)floor($resultPrices->min() ?? 0);
                        $filterMaxPrice = (int)ceil($resultPrices->max() ?? 3500);
                        $filterMaxDuration = (int)ceil(($resultDurations->max() ?? 1440) / 60);

                        $hasRefundableMeta = collect($output['result'] ?? [])->contains(function($r){
                            return array_key_exists('Refundable',$r) || array_key_exists('IsRefundable',$r);
                        });
                        $hasBaggageMeta = collect($output['result'] ?? [])->contains(function($r){
                            return !empty($r['Baggage']) || !empty($r['BaggageInfo']) || !empty($r['BaggageAllowance']);
                        });
                        $hasAllianceMeta = collect($output['result'] ?? [])->contains(function($r){
                            return !empty($r['Alliance']) || !empty($r['AirlineAlliance']);
                        });
                    @endphp

                    <div class="tnb-filter-column">
                        <div class="filter-searchBar bg-white rounded-3">
                            <div class="filter-searchBar-head">
                                <div class="d-flex align-items-center justify-content-between px-3 py-3">
                                    <h6 class="tnb-filter-title">Filter Flights</h6>
                                    <button type="button" id="clearAllPremium" class="tnb-filter-reset">
                                        <i class="fa-solid fa-rotate-right me-1"></i>Reset All
                                    </button>
                                </div>
                            </div>

                            <div class="filter-searchBar-body" id="filterForm">

                                <div class="tnb-extra-filter">
                                    <h6>Price Range</h6>
                                    <div class="tnb-range-top">
                                        <span>Up to</span>
                                        <strong id="tnbPriceValue">${{ number_format($filterMaxPrice,0) }}</strong>
                                    </div>
                                    <input
                                        type="range"
                                        class="tnb-range"
                                        id="tnbPriceRange"
                                        min="{{ $filterMinPrice }}"
                                        max="{{ $filterMaxPrice }}"
                                        value="{{ $filterMaxPrice }}"
                                        step="1"
                                    >
                                    <div class="tnb-range-bottom">
                                        <span>${{ number_format($filterMinPrice,0) }}</span>
                                        <span>${{ number_format($filterMaxPrice,0) }}</span>
                                    </div>
                                </div>

                                <!-- Preserve all original website filters -->
                                <x-left-filter :lFilter="$lFilter" />

                                <div class="tnb-extra-filter">
                                    <h6>Max Duration</h6>
                                    <div class="tnb-range-top">
                                        <span>Up to</span>
                                        <strong id="tnbDurationValue">Any</strong>
                                    </div>
                                    <input
                                        type="range"
                                        class="tnb-range"
                                        id="tnbDurationRange"
                                        min="1"
                                        max="{{ max(1,$filterMaxDuration) }}"
                                        value="{{ max(1,$filterMaxDuration) }}"
                                        step="1"
                                    >
                                    <div class="tnb-range-bottom">
                                        <span>1h</span>
                                        <span>Any</span>
                                    </div>
                                </div>

                                <div class="tnb-extra-filter">
                                    <h6>Cabin Class</h6>
                                    @foreach([
                                        'econom'=>'Economy',
                                        'premiumeconomy'=>'Premium Economy',
                                        'business'=>'Business',
                                        'first'=>'First'
                                    ] as $cabinValue=>$cabinLabel)
                                        <label class="d-flex align-items-center gap-2 mb-2">
                                            <input type="checkbox" class="tnb-extra-cabin" value="{{ $cabinValue }}">
                                            <span>{{ $cabinLabel }}</span>
                                        </label>
                                    @endforeach
                                    <div class="tnb-filter-meta-note">Cabin filtering uses cabin metadata returned with each result when available.</div>
                                </div>

                                @if($hasRefundableMeta || $hasBaggageMeta)
                                <div class="tnb-extra-filter">
                                    <h6>Fare &amp; Service</h6>

                                    @if($hasRefundableMeta)
                                    <label class="tnb-toggle-row" for="tnbRefundableOnly">
                                        <span class="tnb-toggle-copy">
                                            <strong>Refundable Only</strong>
                                            <span>Show fully refundable fares</span>
                                        </span>
                                        <input type="checkbox" class="tnb-switch" id="tnbRefundableOnly">
                                    </label>
                                    @endif

                                    @if($hasBaggageMeta)
                                    <label class="tnb-toggle-row" for="tnbBaggageOnly">
                                        <span class="tnb-toggle-copy">
                                            <strong>Baggage Included</strong>
                                            <span>Checked bag included in fare</span>
                                        </span>
                                        <input type="checkbox" class="tnb-switch" id="tnbBaggageOnly">
                                    </label>
                                    @endif
                                </div>
                                @endif

                                @if($hasAllianceMeta)
                                <div class="tnb-extra-filter">
                                    <h6>Airline Alliance</h6>
                                    @foreach(['star alliance'=>'Star Alliance','oneworld'=>'oneworld','skyteam'=>'SkyTeam'] as $allianceValue=>$allianceLabel)
                                        <label class="d-flex align-items-center gap-2 mb-2">
                                            <input type="checkbox" class="tnb-extra-alliance" value="{{ $allianceValue }}">
                                            <span>{{ $allianceLabel }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @endif

                            </div>
                        </div>
                    </div>

                    <!-- Flight Cards -->
                    <div class="tnb-flight-results-column">
                        <div class="row align-items-center g-4 mt-2"
                             id="tnbFlightResults"
                             data-code="{{ $code }}"
                             data-total="{{ count($output['result'] ?? []) }}">

                            @php $sno = 1; @endphp
                            @foreach(array_slice($output['result'] ?? [], 0, 30, true) as $key=>$results)

                                @include('partials.flight-result-card')
                            @endforeach

                        </div>

                        @if(count($output['result'] ?? []) > 30)
                            <div class="text-center mt-4 mb-3" id="tnbLoadMoreWrap">
                                <button type="button"
                                        class="btn btn-outline-primary px-4"
                                        id="tnbLoadMoreFlights">
                                    Load more flights
                                </button>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </section>

        <div class="modal fade" id="model1" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content main-boxx border-0 shadow-lg" style="border-radius:18px;">
                    <div class="modal-body px-4 py-4">
                        <div class="text-center" id="not-flight-msz">
                            <div class="spinner-border text-primary mb-3" role="status" aria-hidden="true"></div>
                            <h5 class="fw-bold mb-2">Securing your flight...</h5>
                            <p class="text-muted small mb-0">Please wait while we confirm the latest fare and availability.</p>
                        </div>
                        <div class="text-center" id="model-button" style="display:none; margin-top:20px;">
                            <a class="btn btn-md btn-primary" href="/">HOME</a>
                            <a class="btn btn-md btn-dark" href='{{url("flight-listing/$code")}}'>REFRESH</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="model2" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md">
                <div class="modal-content main-boxx">
                    <div class="modal-body">
                        <div class="row gx-3 mb-3">
                            <div class="text-center" id="not-flight-msz2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @else

        <section class="gray-simple">
            <div class="container">
                <div class="row justify-content-between gy-4 gx-xl-4 gx-lg-3 gx-md-3 gx-4">

                    <span style="text-align:center;">{{$error}}
                    @if($error=='Search limit')
                    : Current Application in Development Mode, Please rehit the link to get the result, <br/><a href="{{route('flight_listing',$code)}}">REHIT</a>
                    @endif
                    </span>

                </div>
            </div>
        </section>
    
    @endif

    <!-- Special fare onload popup removed -->

@endsection


@section('footer')

<script>
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.tnb-date-pill[data-search-date]').forEach(function(btn){
        btn.addEventListener('click', function(){
            if (btn.classList.contains('active')) return;

            const departInput = document.getElementById('depature_date');
            const returnInput = document.getElementById('return_date');
            const form = document.getElementById('form2');
            if (!departInput || !form) return;

            const newDeparture = btn.dataset.searchDate;
            departInput.value = newDeparture;

            const tripDays = Number(btn.dataset.tripDays || 0);
            if (returnInput && !returnInput.disabled && tripDays > 0) {
                const d = new Date(newDeparture + 'T12:00:00');
                d.setDate(d.getDate() + tripDays);
                const yyyy = d.getFullYear();
                const mm = String(d.getMonth()+1).padStart(2,'0');
                const dd = String(d.getDate()).padStart(2,'0');
                returnInput.value = `${yyyy}-${mm}-${dd}`;
            }

            form.submit();
        });
    });
});

document.addEventListener('DOMContentLoaded', function(){
    const sortBar = document.getElementById('tnbSortBar');
    const firstResult = document.querySelector('.result-step');
    const list = firstResult ? firstResult.parentElement : null;
    const fareCompare = document.getElementById('tnbFareCompare');
    const fareReset = document.getElementById('tnbFareReset');
    const filterState = document.getElementById('tnbFareFilterState');
    const filterLabel = document.getElementById('tnbFareFilterLabel');
    const clearFilter = document.getElementById('tnbClearFareFilter');
    const flightCount = document.getElementById('flight-count');
    const resultContext = document.getElementById('tnbResultContext');

    if (!list) return;

    const allRows = () => Array.from(list.querySelectorAll('.result-step'));
    let activeFilter = null;

    function visibleRows(){
        return allRows().filter(row => row.style.display !== 'none');
    }

    function updateFareMatrixFromVisibleRows(){
        if (!fareCompare) return;

        const visible = visibleRows();
        const groups = {};

        visible.forEach(row=>{
            const airline = String(row.dataset.airline || '').toUpperCase();
            const stops = Number(row.dataset.stops || 0);
            const price = Number(row.dataset.price || 0);
            if (!airline || !price) return;

            const key = airline + '|' + stops;
            if (!(key in groups) || price < groups[key]) groups[key] = price;
        });

        fareCompare.querySelectorAll('[data-matrix-airline][data-matrix-stops]').forEach(cell=>{
            const airline = String(cell.dataset.matrixAirline || '').toUpperCase();
            const stops = Number(cell.dataset.matrixStops || 0);
            const key = airline + '|' + stops;
            const price = groups[key];

            const priceNode = cell.querySelector('.tnb-fare-price');
            const emptyNode = cell.querySelector('.tnb-fare-empty');
            const button = cell.querySelector('.tnb-fare-filter');

            if (price) {
                if (priceNode) priceNode.textContent = '$' + Math.round(price).toLocaleString('en-US');
                if (button) button.style.display = '';
                if (emptyNode) emptyNode.style.display = 'none';
            } else {
                if (button) button.style.display = 'none';
                if (emptyNode) {
                    emptyNode.style.display = '';
                    emptyNode.textContent = '---';
                }
            }
        });
    }

    function updateCount(){
        const shown = visibleRows().length;
        const total = Number(list.dataset.total || shown);
        const count = activeFilter ? shown : total;

        if (flightCount) flightCount.textContent = `${count} flight option${count === 1 ? '' : 's'}`;
        if (resultContext) {
            resultContext.textContent = activeFilter ? `matching ${activeFilter.label}` : 'from this live search';
        }
        updateFareMatrixFromVisibleRows();
    }

    function clearFareFilter(){
        activeFilter = null;
        allRows().forEach(row => row.style.display = '');
        fareCompare?.querySelectorAll('.tnb-fare-filter').forEach(btn => btn.classList.remove('active'));
        filterState?.classList.remove('show');
        updateCount();
    }

    function applyFareFilter(button){
        const airline = String(button.dataset.airline || '').toUpperCase();
        const stops = Number(button.dataset.stops || 0);
        const label = button.dataset.label || 'selected fare';

        activeFilter = {airline, stops, label};

        fareCompare?.querySelectorAll('.tnb-fare-filter').forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');

        allRows().forEach(row => {
            const rowAirline = String(row.dataset.airline || '').toUpperCase();
            const rowStops = Number(row.dataset.stops || 0);
            const stopMatches = rowStops === stops;
            row.style.display = (rowAirline === airline && stopMatches) ? '' : 'none';
        });

        if (filterLabel) filterLabel.textContent = label;
        filterState?.classList.add('show');
        updateCount();

        document.querySelector('.tnb-results-toolbar')?.scrollIntoView({behavior:'smooth', block:'center'});
    }

    fareCompare?.addEventListener('click', function(e){
        const fareBtn = e.target.closest('.tnb-fare-filter');
        if (fareBtn) applyFareFilter(fareBtn);
    });

    fareReset?.addEventListener('click', clearFareFilter);
    document.getElementById('tnbFareResetMatrix')?.addEventListener('click', clearFareFilter);
    clearFilter?.addEventListener('click', clearFareFilter);

    sortBar?.addEventListener('click', function(e){
        const btn = e.target.closest('[data-sort]');
        if(!btn) return;

        sortBar.querySelectorAll('.tnb-sortitem').forEach(b=>b.classList.remove('active'));
        btn.classList.add('active');

        const rows = Array.from(list.querySelectorAll('.result-step'));
        const sort = btn.dataset.sort;

        rows.sort((a,b)=>{
            const pa = Number(a.dataset.price||0), pb = Number(b.dataset.price||0);
            const da = Number(a.dataset.duration||999999), db = Number(b.dataset.duration||999999);
            if(sort==='cheap') return pa-pb;
            if(sort==='fast') return da-db;
            return (pa + da*.08) - (pb + db*.08);
        }).forEach(r=>list.appendChild(r));
    });

    document.addEventListener('tnb:filters-applied', function(){
        updateFareMatrixFromVisibleRows();
    });

    updateCount();
});</script>

<script>

    let tnbPrebookInProgress = false;

    function checkArray(code,OfferCode,SearchGuid,key)
    {
        // Instant booking page (18 Nov 2026): open the booking page at once. The supplier check
        // ("Securing your flight") now runs in the background on that page, while the customer
        // fills in the passenger details. Old version: flight_list.blade.php.before-instantbook-20261118
        if (tnbPrebookInProgress) return;
        tnbPrebookInProgress = true;
        window.location.href = "/flight-payment/"+code+"/"+key;
    }

    window.addEventListener('pageshow', function () {
        tnbPrebookInProgress = false;

        const modalElement = document.getElementById('model1');
        if (modalElement) {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) modal.hide();

            modalElement.classList.remove('show');
            modalElement.style.display = 'none';
            modalElement.setAttribute('aria-hidden', 'true');
            modalElement.removeAttribute('aria-modal');
        }

        document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
            backdrop.remove();
        });

        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
    });

    function submitForm() 
    {
        //jQuery('#model1').modal('show');
        //document.getElementById("form1").submit();
    }

    function checkFlightType(val)
    {
        if(val=='round_trip')
        {
            //alert('round_trip');
            //jQuery('#return_date_div').show();
            jQuery('#return_date').prop("disabled", false);
            jQuery('#return_date').addAttribute('required', "true");
        }
        if(val=='one_way')
        {
            //alert('one_way');
            //jQuery('#return_date_div').hide();
            jQuery('#return_date').val('').prop("disabled", true);
            jQuery('#return_date').removeAttribute('required');
        }
    }

    function showDetail(code,key)
    {
        $.ajax({
            url: '/ajax/get-flight-detail', 
            type: 'GET', 
            data: { code: code,key: key },
            success: function(response) 
            {
                jQuery("#fullsegment_"+key).html(response);
            }
        });
    }

    function showMarkup(code,key)
    {
        $.ajax({
            url: '/ajax/get-markup-detail', 
            type: 'GET', 
            data: { code: code,key: key },
            success: function(response) 
            {
                jQuery('#not-flight-msz2').html(response);
                jQuery('#model2').modal('show');
            }
        });
    }

    document.getElementById('form2').addEventListener('submit', function() 
    {
        jQuery('#model1').modal('show');

        // setTimeout(() => 
        // {
        //     
        // }, 5000);
    });

</script>



<script type="text/javascript">

    document.addEventListener("DOMContentLoaded", function() 
    {
        if (window.location.search === "") 
        {            
            setTimeout(() => 
            {
                
            }, 5000);
        }
    });
</script>










<script>
document.addEventListener('DOMContentLoaded', function(){
    function clean(v){ return String(v || '').replace(/\s+/g,' ').trim(); }

    function syncDetailFromCompactCard(card){
        if(!card) return;
        const summaryRows = Array.from(card.querySelectorAll('.tnb-itin-row')).slice(0,2);
        const journeyNames = ['depart','return'];

        summaryRows.forEach(function(row, journeyIndex){
            const journey = journeyNames[journeyIndex];
            const detailSegments = Array.from(card.querySelectorAll(
                '.tnb-detail-segment[data-journey="' + journey + '"]'
            ));
            if(!detailSegments.length) return;

            const times = Array.from(row.querySelectorAll('.tnb-time strong'))
                .map(el=>clean(el.textContent))
                .filter(Boolean);

            if(times.length >= 2){
                const firstDep = detailSegments[0].querySelector('.tnb-detail-point strong');
                const lastArr = detailSegments[detailSegments.length-1]
                    .querySelector('.tnb-detail-point.text-end strong');

                if(firstDep && (!clean(firstDep.textContent) || clean(firstDep.textContent)==='—')){
                    firstDep.textContent = times[0];
                }
                if(lastArr && (!clean(lastArr.textContent) || clean(lastArr.textContent)==='—')){
                    lastArr.textContent = times[times.length-1];
                }
            }
        });
    }

    document.addEventListener('click', function(event){
        const btn = event.target.closest('.tnb-detail-trigger');
        if(!btn) return;

        const card = btn.closest('.result-step');
        if(!card) return;

        const key = btn.getAttribute('data-detail-key');
        const code = btn.getAttribute('data-detail-code');
        const loader = document.getElementById('detail-loader-' + key);

        if(!loader) return;

        if(loader.dataset.loaded === '1'){
            syncDetailFromCompactCard(card);
            setTimeout(()=>syncDetailFromCompactCard(card),180);
            return;
        }

        if(loader.dataset.loading === '1') return;

        loader.dataset.loading = '1';
        loader.innerHTML = '<div class="py-3 text-center text-muted">Loading flight details...</div>';

        jQuery.ajax({
            url: '/ajax/get-flight-detail',
            type: 'GET',
            data: { code: code, key: key },
            success: function(response){
                loader.innerHTML = response;
                loader.dataset.loaded = '1';
                loader.dataset.loading = '0';
                syncDetailFromCompactCard(card);
                setTimeout(()=>syncDetailFromCompactCard(card),180);
            },
            error: function(){
                loader.dataset.loading = '0';
                loader.innerHTML = '<div class="py-3 text-center text-danger">Unable to load flight details. Please try again.</div>';
            }
        });
    });
});
</script>

<!-- Filter -->

<script>
(function(){
    const originalFilterGroups = [
        'dptstop','rtnstop',
        'dpttime','rtntime',
        'dptairline','rtnairline',
        'dptairport','rtnairport'
    ];

    function timeToMinutes(timeStr){
        const parts = String(timeStr || '').split(':').map(Number);
        return (parts[0] || 0) * 60 + (parts[1] || 0);
    }

    function isTimeInRange(time, range){
        const pieces = String(range || '').split('-');
        if(pieces.length !== 2) return false;
        const start = timeToMinutes(pieces[0]);
        const end = timeToMinutes(pieces[1]);
        const t = timeToMinutes(time);

        // Supports ranges that cross midnight.
        return start <= end ? (t >= start && t <= end) : (t >= start || t <= end);
    }

    function cabinNormal(value){
        return String(value || '')
            .toLowerCase()
            .replace(/\s+/g,'')
            .replace('premiumeconomyclass','premiumeconomy')
            .replace('peconom','premiumeconomy')
            .replace('firstclass','first')
            .replace('economy','econom');
    }

    function selectedOriginalFilters(){
        const selected = {};
        originalFilterGroups.forEach(group=>{
            selected[group] = [];
            $(`.label-filter-${group} input[type="checkbox"]:checked`).each(function(){
                selected[group].push(String($(this).val()));
            });
        });
        return selected;
    }

    function matchesOriginalFilters($row, selected){
        for(const group of originalFilterGroups){
            const values = selected[group] || [];
            if(!values.length) continue;

            if(group === 'rtntime' || group === 'dpttime'){
                const classes = String($row.attr('class') || '').split(/\s+/);
                const timeClass = classes.find(cls=>cls.startsWith(group+'-'));
                if(!timeClass) return false;

                const actualTime = timeClass.replace(group+'-','');
                if(!values.some(range=>isTimeInRange(actualTime,range))) return false;
            }else{
                if(!values.some(val=>$row.hasClass(group+'-'+val))) return false;
            }
        }
        return true;
    }

    function applyAllFilters(){
        const selected = selectedOriginalFilters();

        const priceMax = Number(document.getElementById('tnbPriceRange')?.value || Infinity);
        const durationHours = Number(document.getElementById('tnbDurationRange')?.value || Infinity);
        const durationMaxMinutes = durationHours * 60;

        const cabins = Array.from(document.querySelectorAll('.tnb-extra-cabin:checked'))
            .map(el=>cabinNormal(el.value));

        const alliances = Array.from(document.querySelectorAll('.tnb-extra-alliance:checked'))
            .map(el=>String(el.value).toLowerCase());

        const refundableOnly = !!document.getElementById('tnbRefundableOnly')?.checked;
        const baggageOnly = !!document.getElementById('tnbBaggageOnly')?.checked;

        let shown = 0;

        $('.result-step').each(function(){
            const $row = $(this);
            const node = this;

            let ok = matchesOriginalFilters($row, selected);

            if(ok && Number(node.dataset.price || 0) > priceMax) ok = false;
            if(ok && Number(node.dataset.duration || 0) > durationMaxMinutes) ok = false;

            if(ok && cabins.length){
                const rowCabin = cabinNormal(node.dataset.cabin || '');
                ok = cabins.includes(rowCabin);
            }

            if(ok && alliances.length){
                ok = alliances.includes(String(node.dataset.alliance || '').toLowerCase());
            }

            if(ok && refundableOnly) ok = node.dataset.refundable === '1';
            if(ok && baggageOnly) ok = node.dataset.baggage === '1';

            node.style.display = ok ? '' : 'none';
            if(ok) shown++;
        });

        $('#flight-count').text(shown + ' flight option' + (shown === 1 ? '' : 's'));

        // Keep top fare matrix on the same visible result set.
        document.dispatchEvent(new CustomEvent('tnb:filters-applied'));
    }

    function resetAllFilters(){
        $('#filterForm input[type="checkbox"]').prop('checked',false);

        const price = document.getElementById('tnbPriceRange');
        if(price){
            price.value = price.max;
            document.getElementById('tnbPriceValue').textContent =
                '$' + Number(price.max).toLocaleString('en-US');
        }

        const duration = document.getElementById('tnbDurationRange');
        if(duration){
            duration.value = duration.max;
            document.getElementById('tnbDurationValue').textContent = 'Any';
        }

        const refundable = document.getElementById('tnbRefundableOnly');
        const baggage = document.getElementById('tnbBaggageOnly');
        if(refundable) refundable.checked = false;
        if(baggage) baggage.checked = false;

        $('.result-step').show();
        $('#flight-count').text($('.result-step:visible').length + ' flight options');
    }

    document.addEventListener('DOMContentLoaded',function(){
        const price = document.getElementById('tnbPriceRange');
        const duration = document.getElementById('tnbDurationRange');

        price?.addEventListener('input',function(){
            document.getElementById('tnbPriceValue').textContent =
                '$' + Number(this.value).toLocaleString('en-US');
            applyAllFilters();
        });

        duration?.addEventListener('input',function(){
            document.getElementById('tnbDurationValue').textContent =
                Number(this.value) >= Number(this.max) ? 'Any' : this.value + 'h';
            applyAllFilters();
        });

        document.getElementById('clearAllPremium')?.addEventListener('click',resetAllFilters);
        document.getElementById('clearAll')?.addEventListener('click',function(e){
            e.preventDefault();
            resetAllFilters();
        });

        document.querySelectorAll(
            '#filterForm input[type="checkbox"], #tnbRefundableOnly, #tnbBaggageOnly'
        ).forEach(el=>el.addEventListener('change',applyAllFilters));
    });

    // Fare overview click filtering and regular sidebar filters should work together.
    window.tnbReapplyListingFilters = applyAllFilters;
})();
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const removedPopup = document.getElementById('onload');
    if (removedPopup) removedPopup.remove();

    // Defensive cleanup in case Bootstrap/theme JS already created a backdrop.
    document.querySelectorAll('.modal-backdrop').forEach(function(el){
        if (!document.querySelector('.modal.show')) el.remove();
    });

    if (!document.querySelector('.modal.show')) {
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('padding-right');
        document.body.style.removeProperty('overflow');
    }
});
</script>



<script>
(function(){
    const airportCache = {};

    function normalizeAirport(row){
        if(typeof row === 'string'){
            const code = (row.split(' - ')[0] || '').trim();
            return { value: row, code: code, title: row, subtitle: '' };
        }
        const value = row.value || row.label || row.name || '';
        const code = row.code || (String(value).split(' - ')[0] || '').trim();
        const title = row.city || row.airport || row.label || value;
        const subtitle = row.airport_name || row.airport || row.country || row.label || value;
        return { value, code, title, subtitle };
    }

    function closeAirportLists(){
        document.querySelectorAll('.fhx-airport-list').forEach(function(el){
            el.classList.remove('show');
        });
    }

    function renderAirportList(listEl, rows, input){
        listEl.innerHTML = '';

        if(!rows.length){
            listEl.innerHTML = '<div class="fhx-airport-empty">No matching airport found.</div>';
            listEl.classList.add('show');
            return;
        }

        rows.slice(0,12).forEach(function(raw){
            const item = normalizeAirport(raw);

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'fhx-airport-item';

            const code = document.createElement('span');
            code.className = 'fhx-code';
            code.textContent = item.code || 'AIR';

            const copy = document.createElement('span');
            copy.className = 'fhx-ap-copy';

            const strong = document.createElement('strong');
            strong.textContent = item.title || item.value;

            const small = document.createElement('small');
            small.textContent = item.subtitle || item.value;

            copy.appendChild(strong);
            copy.appendChild(small);
            button.appendChild(code);
            button.appendChild(copy);

            button.addEventListener('mousedown', function(e){
                e.preventDefault();
                input.value = item.value;
                listEl.classList.remove('show');
            });

            listEl.appendChild(button);
        });

        listEl.classList.add('show');
    }

    function setupAirport(inputId, listId){
        const input = document.getElementById(inputId);
        const list = document.getElementById(listId);
        if(!input || !list) return;

        let timer = null;
        let controller = null;

        async function run(){
            const term = String(input.value || '').trim();

            if(term.length < 1){
                list.classList.remove('show');
                return;
            }

            const key = term.toLowerCase();
            closeAirportLists();

            if(airportCache[key]){
                renderAirportList(list, airportCache[key], input);
                return;
            }

            if(controller) controller.abort();
            controller = new AbortController();

            try{
                const response = await fetch('/ajax/get-airport?term=' + encodeURIComponent(term), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: controller.signal
                });

                if(!response.ok) throw new Error('Airport HTTP ' + response.status);

                let payload = await response.json();

                if(!Array.isArray(payload)){
                    if(payload && Array.isArray(payload.data)) payload = payload.data;
                    else if(payload && Array.isArray(payload.results)) payload = payload.results;
                    else payload = [];
                }

                airportCache[key] = payload;
                renderAirportList(list, payload, input);
            }catch(error){
                if(error.name === 'AbortError') return;
                console.error('Airport search failed:', error);
                list.innerHTML = '<div class="fhx-airport-empty">Airport suggestions could not load.</div>';
                list.classList.add('show');
            }
        }

        input.addEventListener('input', function(){
            clearTimeout(timer);
            timer = setTimeout(run, 120);
        });

        input.addEventListener('focus', function(){
            const value = String(input.value || '').trim();
            // Existing selected values like "JFK - New York..." should not trigger
            // a new AJAX search merely by focusing the field.
            if(value && !value.includes(' - ') && value.length <= 12) run();
        });
    }

    window.fhxTogglePassengerBox = function(){
        const box = document.getElementById('passenger-box');
        if(!box) return;
        box.style.display = box.style.display === 'block' ? 'none' : 'block';
    };

    function refreshPassengerText(){
        const adults = parseInt(document.getElementById('adult-count')?.value || '1', 10);
        const children = parseInt(document.getElementById('child-count')?.value || '0', 10);
        const infants = parseInt(document.getElementById('infant-count')?.value || '0', 10);
        const total = adults + children + infants;

        const count = document.getElementById('fhxTravelerCount');
        const plural = document.getElementById('fhxTravelerPlural');
        const breakdown = document.getElementById('fhxTravelerBreakdown');

        if(count) count.textContent = total;
        if(plural) plural.textContent = total === 1 ? '' : 's';

        const parts = [adults + ' Adult' + (adults === 1 ? '' : 's')];
        if(children > 0) parts.push(children + ' Child' + (children === 1 ? '' : 'ren'));
        if(infants > 0) parts.push(infants + ' Infant' + (infants === 1 ? '' : 's'));

        if(breakdown) breakdown.textContent = parts.join(' · ');
    }

    window.fhxUpdatePassengerCount = refreshPassengerText;

    window.fhxClosePassengerBox = function(){
        refreshPassengerText();
        const box = document.getElementById('passenger-box');
        if(box) box.style.display = 'none';
    };

    let returnPicker = null;

    function fhxSetStandardRouteEnabled(enabled){
        ['location_from','location_to','depature_date'].forEach(function(id){
            const input = document.getElementById(id);
            if(input) input.disabled = !enabled;
        });
    }

    function fhxRefreshMultiLegs(){
        const box = document.getElementById('fhxMultiCityLegs');
        const add = document.getElementById('fhxAddMultiLeg');
        if(!box) return;

        const rows = [...box.querySelectorAll('[data-fhx-multi-leg]')];
        rows.forEach(function(row,index){
            const num = row.querySelector('.fhx-multi-number');
            const title = row.querySelector('.fhx-multi-leg-title strong');
            const remove = row.querySelector('[data-fhx-remove-leg]');
            if(num) num.textContent = index + 1;
            if(title) title.textContent = 'Flight ' + (index + 1);
            if(remove) remove.hidden = rows.length <= 2;
        });

        if(add) add.disabled = rows.length >= 5;
    }

    function fhxSetupMultiRow(row){
        if(!row) return;

        row.querySelectorAll('[data-fhx-multi-airport]').forEach(function(input,index){
            const list = input.closest('.fhx-airport-field')?.querySelector('[data-fhx-multi-list]');
            if(!list) return;

            if(!input.id) input.id = 'fhxMultiAirport_' + Date.now() + '_' + Math.random().toString(36).slice(2,7);
            if(!list.id) list.id = 'fhxMultiList_' + Date.now() + '_' + index + '_' + Math.random().toString(36).slice(2,7);

            setupAirport(input.id, list.id);
        });

        row.querySelectorAll('[data-fhx-multi-date]').forEach(function(input){
            if(input._flatpickr) return;
            if(window.flatpickr){
                flatpickr(input,{
                    dateFormat:'Y-m-d',
                    altInput:true,
                    altFormat:'D, M j, Y',
                    altInputClass:'fhx-input fhx-date-alt',
                    minDate:'today',
                    disableMobile:true,
                    allowInput:false,
                    showMonths:window.innerWidth>=900?2:1,
                    onOpen:function(){ closeAirportLists(); }
                });
            }
        });
    }

    function fhxAddMultiLeg(prefill){
        const box = document.getElementById('fhxMultiCityLegs');
        if(!box || box.querySelectorAll('[data-fhx-multi-leg]').length >= 5) return;

        const data = prefill || {};
        const row = document.createElement('div');
        row.className = 'fhx-multi-leg';
        row.setAttribute('data-fhx-multi-leg','');
        row.innerHTML = `
            <div class="fhx-multi-leg-title">
                <span class="fhx-multi-number"></span>
                <strong>Flight</strong>
            </div>
            <div class="fhx-field fhx-airport-field">
                <label class="fhx-label">From</label>
                <input class="fhx-input" data-fhx-multi-airport type="text" name="multi_from[]" placeholder="City or airport code" autocomplete="off">
                <i class="fa-solid fa-plane-departure fhx-airport-symbol"></i>
                <div class="fhx-airport-list" data-fhx-multi-list></div>
            </div>
            <div class="fhx-field fhx-airport-field">
                <label class="fhx-label">To</label>
                <input class="fhx-input" data-fhx-multi-airport type="text" name="multi_to[]" placeholder="City or airport code" autocomplete="off">
                <i class="fa-solid fa-plane-arrival fhx-airport-symbol"></i>
                <div class="fhx-airport-list" data-fhx-multi-list></div>
            </div>
            <div class="fhx-field">
                <label class="fhx-label">Depart</label>
                <input class="fhx-input" data-fhx-multi-date type="text" name="multi_date[]" autocomplete="off">
            </div>
            <button type="button" class="fhx-multi-remove" data-fhx-remove-leg aria-label="Remove flight">
                <i class="fa-solid fa-xmark"></i>
            </button>
        `;

        box.appendChild(row);
        const airportInputs = row.querySelectorAll('[data-fhx-multi-airport]');
        if(airportInputs[0]) airportInputs[0].value = data.from || '';
        if(airportInputs[1]) airportInputs[1].value = data.to || '';
        const dateInput = row.querySelector('[data-fhx-multi-date]');
        if(dateInput) dateInput.value = data.date || '';

        fhxSetupMultiRow(row);
        fhxRefreshMultiLegs();
    }

    window.fhxSetTrip = function(type){
        const returnInput = document.getElementById('return_date');
        const returnField = document.getElementById('fhxReturnField');
        const multiEditor = document.getElementById('fhxMultiCityEditor');
        const standardGrid = document.getElementById('fhxStandardGrid');
        const isMulti = type === 'multi_city';

        if(multiEditor) multiEditor.hidden = !isMulti;
        standardGrid?.classList.toggle('fhx-grid-multicity',isMulti);
        fhxSetStandardRouteEnabled(!isMulti);

        document.querySelectorAll('#fhxMultiCityEditor input').forEach(function(input){
            input.disabled = !isMulti;
            input.required = isMulti;
        });

        if(isMulti){
            if(returnInput) returnInput.disabled = true;
            returnField?.classList.add('fhx-return-disabled');

            const box = document.getElementById('fhxMultiCityLegs');
            while(box && box.querySelectorAll('[data-fhx-multi-leg]').length < 2){
                fhxAddMultiLeg();
            }

            document.querySelectorAll('#fhxMultiCityLegs [data-fhx-multi-leg]').forEach(fhxSetupMultiRow);
            fhxRefreshMultiLegs();
            return;
        }

        if(type === 'one_way'){
            if(returnInput){
                returnInput.value = '';
                returnInput.disabled = true;
            }
            returnField?.classList.add('fhx-return-disabled');
            if(returnPicker) returnPicker.clear();
        }else{
            if(returnInput) returnInput.disabled = false;
            returnField?.classList.remove('fhx-return-disabled');
        }
    };

    document.addEventListener('DOMContentLoaded', function(){
        setupAirport('location_from','fhxFromList');
        setupAirport('location_to','fhxToList');
        refreshPassengerText();

        document.querySelectorAll('#fhxMultiCityLegs [data-fhx-multi-leg]').forEach(fhxSetupMultiRow);
        fhxRefreshMultiLegs();

        document.getElementById('fhxAddMultiLeg')?.addEventListener('click',function(){
            fhxAddMultiLeg();
        });

        document.getElementById('fhxMultiCityLegs')?.addEventListener('click',function(event){
            const remove = event.target.closest('[data-fhx-remove-leg]');
            if(!remove) return;

            const box = document.getElementById('fhxMultiCityLegs');
            if(!box || box.querySelectorAll('[data-fhx-multi-leg]').length <= 2) return;

            remove.closest('[data-fhx-multi-leg]')?.remove();
            fhxRefreshMultiLegs();
        });

        if(window.flatpickr){
            if(document.getElementById('depature_date')) flatpickr('#depature_date', {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'M j, Y',
                altInputClass: 'fhx-input fhx-date-alt',
                minDate: 'today',
                disableMobile: true,
                allowInput: false,
                showMonths: window.innerWidth >= 900 ? 2 : 1,
                onOpen: function(){
                    closeAirportLists();
                },
                onChange: function(selectedDates, dateStr){
                    if(returnPicker){
                        returnPicker.set('minDate', dateStr || 'today');
                        if(returnPicker.input.value && returnPicker.input.value < dateStr){
                            returnPicker.clear();
                        }
                    }
                }
            });

            if(document.getElementById('return_date')) returnPicker = flatpickr('#return_date', {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'M j, Y',
                altInputClass: 'fhx-input fhx-date-alt',
                minDate: document.getElementById('depature_date')?.value || 'today',
                disableMobile: true,
                allowInput: false,
                showMonths: window.innerWidth >= 900 ? 2 : 1,
                onOpen: function(){
                    closeAirportLists();
                }
            });
        }

        fhxSetTrip(document.querySelector('input[name="flight_types"]:checked')?.value || 'round_trip');

        document.addEventListener('click', function(e){
            const card = document.getElementById('fhxSearchCard');
            if(card && !card.contains(e.target)){
                closeAirportLists();
                const box = document.getElementById('passenger-box');
                if(box) box.style.display = 'none';
            }
        });
    });
})();
</script>

<script id="fareshunt-listing-mobile-recovery-v2-js">
document.addEventListener('DOMContentLoaded',function(){
    const form=document.getElementById('form2');
    const searchToggle=document.getElementById('tnbMobileSearchToggle');
    if(form&&searchToggle){
        searchToggle.addEventListener('click',function(){
            const open=!form.classList.contains('tnb-mobile-search-open');
            form.classList.toggle('tnb-mobile-search-open',open);
            searchToggle.setAttribute('aria-expanded',open?'true':'false');
            const a=searchToggle.querySelector('.tnb-mobile-search-action');
            if(a)a.innerHTML=open?'<i class="fa-solid fa-xmark"></i> Close':'<i class="fa-solid fa-pen"></i> Modify';
        });
    }
    const filterToggle=document.getElementById('tnbMobileFiltersToggle');
    const layout=document.querySelector('.tnb-results-layout');
    if(filterToggle&&layout){
        filterToggle.addEventListener('click',function(){
            const open=!layout.classList.contains('tnb-mobile-filters-open');
            layout.classList.toggle('tnb-mobile-filters-open',open);
            filterToggle.innerHTML=open?'<i class="fa-solid fa-xmark"></i> Close filters':'<i class="fa-solid fa-sliders"></i> Filters';
            if(open && window.innerWidth<=760){
                setTimeout(function(){
                    layout.querySelector('.tnb-filter-column')?.scrollIntoView({behavior:'smooth',block:'start'});
                },50);
            }
        });
    }

    if(form){
        form.addEventListener('submit',function(){
            form.classList.remove('tnb-mobile-search-open');
            if(searchToggle){
                searchToggle.setAttribute('aria-expanded','false');
            }
        });
    }
});
</script>
<script id="fareshunt-progressive-flight-results">
document.addEventListener('DOMContentLoaded', function () {
    const list = document.getElementById('tnbFlightResults');
    const button = document.getElementById('tnbLoadMoreFlights');
    const wrap = document.getElementById('tnbLoadMoreWrap');

    if (!list || !button) return;

    const code = list.dataset.code || '';
    const total = Number(list.dataset.total || 0);
    let offset = list.querySelectorAll('.result-step').length;
    let loading = false;

    if (!code || offset >= total) {
        if (wrap) wrap.style.display = 'none';
        return;
    }

    button.addEventListener('click', function () {
        if (loading || offset >= total) return;

        loading = true;
        button.disabled = true;
        button.textContent = 'Loading more flights...';

        jQuery.ajax({
            url: '/ajax/get-flight-results',
            type: 'GET',
            data: {
                code: code,
                offset: offset,
                limit: 30
            },
            success: function (response) {
                if (response && response.html) {
                    list.insertAdjacentHTML('beforeend', response.html);
                    if (wrap && list.parentNode) { list.parentNode.appendChild(wrap); }
                }

                offset = Number(response.next || list.querySelectorAll('.result-step').length);

                if (typeof window.tnbReapplyListingFilters === 'function') {
                    window.tnbReapplyListingFilters();
                }

                document.dispatchEvent(new CustomEvent('tnb:filters-applied'));

                if (!response.has_more || offset >= Number(response.total || total)) {
                    if (wrap) wrap.style.display = 'none';
                } else {
                    button.disabled = false;
                    button.textContent = 'Load more flights';
                }

                loading = false;
            },
            error: function () {
                loading = false;
                button.disabled = false;
                button.textContent = 'Try loading more flights';
            }
        });
    });
});
</script>

<style id="fh-results-design-fix-20261113">
/* Flight Detail arrow: down when closed, up when open */
.result-step .tnb-detail-trigger::after{content:''!important;background:none!important;width:0!important;height:0!important;border-left:5px solid transparent!important;border-right:5px solid transparent!important;border-top:6px solid currentColor!important;border-bottom:0!important;margin-left:8px!important;transform:none!important;transition:transform .2s ease!important}
.result-step .tnb-detail-trigger:not(.collapsed)::after{transform:rotate(180deg)!important}
/* Airline code badge when a logo is missing or fails to load */
.tnb-airline-badge{display:inline-flex;align-items:center;justify-content:center;flex:0 0 auto;width:38px;height:38px;border-radius:10px;background:#eef3f9;color:#1d3a5c;font-size:11px;font-weight:800;letter-spacing:.3px}
/* Different airport notice */
.tnb-airport-notice{display:flex;align-items:flex-start;gap:8px;margin:10px 18px 0;padding:8px 12px;border-radius:10px;background:#fff7e6;border:1px solid #f5d48a;color:#7a4b00;font-size:12px;line-height:1.45}
.tnb-airport-notice i{margin-top:2px}
/* Desktop: filter sidebar stays in view and scrolls on its own */
@media (min-width:992px){
  .tnb-results-layout > .tnb-filter-column{align-self:stretch!important}
  .tnb-results-layout .filter-searchBar{position:sticky!important;top:86px!important;max-height:calc(100vh - 100px)!important;overflow-y:auto!important;overscroll-behavior:contain}
}
</style>

<style id="fh-searchbox-fix-20261113">
/* 13 Nov 2026 (search box): one card, equal fields, every value on one line */
#fhxSearchCard{padding:16px 18px!important}
#fhxSearchCard>#form2{margin:0!important;padding:0!important;border:0!important;border-radius:0!important;box-shadow:none!important;background:transparent!important}
#fhxSearchCard .fhx-top{margin-bottom:12px!important}
#fhxSearchCard .fhx-trip label{font-family:inherit!important;font-size:13px!important;font-weight:700!important;color:#344a63!important;letter-spacing:0!important}
#fhxSearchCard .fhx-trip input{width:17px!important;height:17px!important}

#fhxSearchCard .fhx-field{box-sizing:border-box!important;display:flex!important;flex-direction:column!important;justify-content:flex-start!important;height:78px!important;min-height:78px!important;padding:11px 14px 10px!important}
#fhxSearchCard .fhx-label{margin:0 0 6px!important;line-height:1!important}
#fhxSearchCard .fhx-input,
#fhxSearchCard .fhx-select,
#fhxSearchCard .fhx-field input.form-control{display:block!important;width:100%!important;height:22px!important;min-height:22px!important;line-height:22px!important;margin:0!important;padding:0!important;border:0!important;border-radius:0!important;background-color:transparent!important;box-shadow:none!important;outline:0!important;font-family:inherit!important;font-size:14px!important;font-weight:750!important;color:#172e4b!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important}
#fhxSearchCard .fhx-date-alt,
#fhxSearchCard .fhx-field input.form-control{cursor:pointer!important}

/* name line under airports, breakdown under travelers */
#fhxSearchCard .fhx-ap-sub,
#fhxSearchCard .fhx-pax-sub{display:block!important;height:14px!important;margin:3px 0 0!important;font-size:10.5px!important;line-height:14px!important;font-weight:600!important;color:#7d8ca0!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important}

/* travelers + cabin: value and arrow on the same line, arrow at the right edge */
#fhxSearchCard .fhx-pax-btn{display:block!important;height:auto!important;font-family:inherit!important}
#fhxSearchCard .fhx-pax-line{height:22px!important;line-height:22px!important;font-size:14px!important}
#fhxSearchCard .fhx-pax-line .fa-chevron-down{font-size:10px!important;color:#5d7088!important;margin-right:2px!important}
#fhxSearchCard .fhx-select{-webkit-appearance:none!important;-moz-appearance:none!important;appearance:none!important;padding-right:18px!important;cursor:pointer!important;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath d='M1.5 3.5 5 7l3.5-3.5' fill='none' stroke='%235d7088' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E")!important;background-repeat:no-repeat!important;background-position:right 2px center!important;background-size:10px 10px!important}
#fhxSearchCard .fhx-select::-ms-expand{display:none}
#fhxSearchCard .passenger-box{position:absolute!important}

/* swap button between From and To */
#fhxSearchCard .fhx-swap{box-sizing:border-box;position:absolute!important;z-index:6;top:50%;right:-20px;transform:translateY(-50%);width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;border:1px solid #d3deea;border-radius:50%;background:#fff;color:#1d4f86;font-size:12px;line-height:1;box-shadow:0 4px 12px rgba(16,42,72,.10);cursor:pointer;transition:border-color .15s ease,background .15s ease}
#fhxSearchCard .fhx-swap:hover{border-color:#173f70;background:#f3f7fc}
#fhxSearchCard .fhx-swap:focus-visible{outline:2px solid #173f70;outline-offset:2px}
#fhxSearchCard .fhx-swap i{transition:transform .2s ease}
#fhxSearchCard .fhx-swap.is-flipped i{transform:rotate(180deg)}

@media (min-width:1200px){
  #fhxSearchCard .fhx-search-btn{box-sizing:border-box!important;height:78px!important;min-height:78px!important}
  #fhxSearchCard .fhx-multi-leg-title,
  #fhxSearchCard .fhx-multi-remove{height:78px!important}
}
/* 992-1199px: 4 columns, the Search button fills the rest of the second row */
@media (min-width:992px) and (max-width:1199px){
  #fhxSearchCard #fhxStandardGrid:not(.fhx-grid-multicity) .fhx-search-btn{grid-column:span 2!important;min-height:78px!important}
}
/* fields stacked on small screens: the swap button sits between From and To, turned vertical */
@media (max-width:900px){
  #fhxSearchCard .fhx-swap{top:auto;bottom:-20px;right:22px;transform:rotate(90deg)}
}
</style>

<script id="fh-searchbox-fix-20261113-js">
document.addEventListener('DOMContentLoaded', function () {
    var byId = function (id) { return document.getElementById(id); };

    // Name line follows the airport box: cleared while typing, filled when a suggestion is picked.
    [['location_from', 'fhxFromList', 'fhxFromSub'], ['location_to', 'fhxToList', 'fhxToSub']].forEach(function (ids) {
        var input = byId(ids[0]), list = byId(ids[1]), sub = byId(ids[2]);
        if (!input || !sub) return;
        input.addEventListener('input', function () { sub.textContent = ''; });
        if (list) list.addEventListener('mousedown', function (e) {
            var item = e.target.closest ? e.target.closest('.fhx-airport-item') : null;
            if (!item) return;
            var city = item.querySelector('.fhx-ap-copy strong');
            sub.textContent = city ? city.textContent.trim() : '';
        });
    });

    // Swap From and To (values and name lines).
    var swap = byId('fhxSwapRoute');
    if (swap) swap.addEventListener('click', function () {
        var from = byId('location_from'), to = byId('location_to');
        if (!from || !to || from.disabled || to.disabled) return;
        var v = from.value; from.value = to.value; to.value = v;
        var fs = byId('fhxFromSub'), ts = byId('fhxToSub');
        if (fs && ts) { var t = fs.textContent; fs.textContent = ts.textContent; ts.textContent = t; }
        swap.classList.toggle('is-flipped');
    });
});
</script>

<style id="fh-results-polish-20261113">
/* 13 Nov 2026: results page polish (design only) */

/* ---- Search box dates: "Jan 16, 2027" + weekday below (same as the airport fields) ---- */
#fhxSearchCard .fhx-date-sub{display:block!important;height:14px!important;margin:3px 0 0!important;font-size:10.5px!important;line-height:14px!important;font-weight:600!important;color:#7d8ca0!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important}

/* ---- Compare Fares: compact table, columns no wider than needed ---- */
@media (min-width:761px){
  #tnbFareCompare .tnb-fare-overview-head{padding-bottom:10px!important}
  #tnbFareCompare .tnb-fare-matrix{grid-template-columns:130px repeat(var(--airline-count),minmax(150px,1fr))!important;min-width:0!important;width:100%!important;border:1px solid #e4ebf3!important;border-radius:12px!important;overflow:hidden!important}
  #tnbFareCompare .tnb-fare-cell{min-height:0!important;padding:6px 12px!important;border-color:#edf1f6!important}
  #tnbFareCompare .tnb-fare-head{display:flex!important;flex-direction:column!important;align-items:center!important;justify-content:center!important;gap:3px!important;padding:10px 12px!important;background:#f8fafc!important}
  #tnbFareCompare .tnb-fare-head img{display:block;height:22px!important;width:auto!important;max-width:96px!important;object-fit:contain!important;margin:0 0 2px!important}
  #tnbFareCompare .tnb-fare-head strong{font-size:12px!important;line-height:1.2!important}
  #tnbFareCompare .tnb-fare-label{display:flex!important;align-items:center!important;font-size:12px!important;font-weight:700!important;color:#1b2f48!important}
  #tnbFareCompare .tnb-fare-filter{display:inline-flex!important;flex-direction:column!important;align-items:center!important;gap:1px!important;min-height:0!important;height:auto!important;padding:3px 12px!important;border-radius:9px!important;transition:background .15s ease}
  #tnbFareCompare .tnb-fare-filter:hover{background:#eef4fb!important}
  #tnbFareCompare .tnb-fare-price{font-size:16px!important;line-height:1.2!important}
  #tnbFareCompare .tnb-fare-action{font-size:10.5px!important}
  #tnbFareCompare .tnb-fare-empty{color:#b8c3d0!important}
  #tnbFareCompare .tnb-fare-note{margin-top:10px!important}
}

/* ---- Flexible dates: slim date chips, thin scrollbar, arrows ---- */
@media (min-width:761px){
  .tnb-price-calendar{position:relative!important;overflow:hidden!important}
  .tnb-price-calendar .tnb-price-calendar-head{padding-top:12px!important;padding-bottom:10px!important}
  .tnb-price-calendar .tnb-date-strip{overflow-x:auto!important;overflow-y:hidden!important;scroll-behavior:smooth;scrollbar-width:none!important;padding-left:40px!important;padding-right:40px!important}
  .tnb-price-calendar .tnb-date-strip::-webkit-scrollbar{display:none!important;height:0!important}
  .tnb-price-calendar .tnb-date-pill{min-width:92px!important;width:92px!important;height:64px!important;padding:6px 4px!important;border-radius:12px!important;display:flex!important;flex-direction:column!important;align-items:center!important;justify-content:center!important;gap:1px!important;transition:border-color .15s ease,background .15s ease}
  .tnb-price-calendar .tnb-date-pill:hover{border-color:#173f70!important;background:#f5f8fc!important}
  .tnb-price-calendar .tnb-date-pill.active:hover{background:#10375f!important}
  .tnb-price-calendar .tnb-date-pill small{font-size:10px!important;line-height:1.2!important}
  .tnb-price-calendar .tnb-date-pill b{font-size:13px!important;line-height:1.25!important}
  .tnb-price-calendar .tnb-date-pill .check{display:none!important}
  .tnb-price-calendar .tnb-date-pill em{font-size:13px!important;line-height:1.2!important;font-style:normal!important}
  .tnb-price-calendar .tnb-date-pill .selected-label{display:none!important}
  .fh-strip-nav{position:absolute;z-index:3;bottom:0;width:38px;display:flex;align-items:center;justify-content:center;border:0;padding:0;color:#173f70;font-size:13px;cursor:pointer}
  .fh-strip-nav.prev{left:0;border-radius:0 0 0 16px;background:linear-gradient(90deg,#fff 55%,rgba(255,255,255,0))}
  .fh-strip-nav.next{right:0;border-radius:0 0 16px 0;background:linear-gradient(270deg,#fff 55%,rgba(255,255,255,0))}
  .fh-strip-nav span{width:28px;height:28px;display:flex;align-items:center;justify-content:center;border:1px solid #d3deea;border-radius:50%;background:#fff;box-shadow:0 3px 10px rgba(16,42,72,.10)}
  .fh-strip-nav:hover span{border-color:#173f70}
  .fh-strip-nav[hidden]{display:none!important}
}

/* ---- Route summary band: one slim light line (the search box above already shows the route) ---- */
@media (min-width:761px){
  .tnb-premium-top .tnb-search-summary{min-height:0!important;padding:10px 16px!important;margin:10px 0 12px!important;background:#fff!important;border:1px solid #e1e8f0!important;border-radius:12px!important;box-shadow:none!important;color:#1b2f48!important}
  .tnb-premium-top .tnb-search-summary>div:first-child{display:flex!important;align-items:baseline!important;gap:12px!important;flex-wrap:wrap!important}
  .tnb-premium-top .tnb-search-summary strong{font-size:14px!important;color:#10375f!important}
  .tnb-premium-top .tnb-search-summary span{color:#6b7c90!important;font-size:12px!important}
  .tnb-premium-top .tnb-summary-date{color:#1b2f48!important}
  .tnb-premium-top .tnb-summary-date i{color:#5b718d!important}
  .tnb-premium-top .tnb-summary-date span{color:#1b2f48!important;font-weight:700!important}
  .tnb-premium-top .tnb-change-search{background:#fff!important;color:#173f70!important;border:1px solid #c9d6e4!important;border-radius:999px!important;padding:5px 14px!important;font-size:11.5px!important;font-weight:800!important}
  .tnb-premium-top .tnb-change-search:hover{border-color:#173f70!important;background:#f3f7fc!important}
}

/* ---- Flight cards: no empty band at the top, simple "Flight Detail" line ---- */
@media (min-width:761px){
  .result-step .tnb-desktop-flight-summary>*{margin-top:0!important}
  .result-step .flights-list-item{transition:border-color .15s ease,box-shadow .15s ease}
  .result-step .row.mt-3{margin:12px 0 0!important}
  .result-step .row.mt-3>.col-12{padding:0!important;border-top:1px solid #edf1f6!important}
  .result-step .accordion,
  .result-step .accordion-item,
  .result-step .accordion-header{border:0!important;border-radius:0!important;box-shadow:none!important;background:transparent!important;margin:0!important}
  .result-step .tnb-detail-trigger{width:auto!important;padding:11px 18px!important;background:transparent!important;border:0!important;box-shadow:none!important;font-size:12.5px!important;font-weight:700!important;color:#1761bb!important}
  .result-step .tnb-detail-trigger:hover{color:#0d4b94!important;text-decoration:underline}
  .result-step .tnb-detail-trigger:focus{box-shadow:none!important}
  .result-step .tnb-airport-notice{margin:10px 18px 0!important}
  .result-step .tnb-time strong{white-space:nowrap!important}
}
@media (min-width:761px) and (max-width:1279px){
  .result-step .tnb-time strong{font-size:15px!important}
  .result-step .tnb-cabin{padding-left:6px!important}
  .result-step .tnb-cabin span{display:none!important}
}

/* ---- Filter sidebar: same left edge for every section, checkboxes not indented ---- */
.filter-searchBar .searchBar-single{padding-left:0!important;padding-right:0!important}
.filter-searchBar .searchBar-single-title{margin:0 0 6px!important}
.filter-searchBar .searchBar-single-title .sidebar-subTitle{margin:0!important}
.filter-searchBar .sidebar-subTitle,
.filter-searchBar .tnb-extra-filter h6{font-size:11px!important;line-height:1.3!important;letter-spacing:.06em!important;text-transform:uppercase!important;font-weight:800!important;color:#1b2f48!important}
.filter-searchBar .searchBar-single-wrap ul{margin:0!important;padding:0!important;list-style:none!important;--bs-gutter-x:0!important}
.filter-searchBar .searchBar-single-wrap li{padding:0!important;margin:0!important;list-style:none!important}
.filter-searchBar .searchBar-single-wrap .form-check{padding-left:0!important;margin:0!important;min-height:0!important}
.filter-searchBar .searchBar-single-wrap .frm-slicing,
.filter-searchBar .searchBar-single-wrap .frm-slicing-first{width:100%!important;padding:0!important;margin:0!important}
.filter-searchBar .searchBar-single-wrap .form-check-label{display:flex!important;align-items:center!important;gap:10px!important;width:calc(100% + 16px)!important;margin:0 -8px!important;padding:6px 8px!important;border-radius:8px!important;cursor:pointer!important}
.filter-searchBar .searchBar-single-wrap .form-check-label:hover{background:#f4f7fb!important}
.filter-searchBar .searchBar-single-wrap .form-check-input{float:none!important;position:static!important;flex:0 0 auto!important;margin:0!important}
.filter-searchBar .searchBar-single-wrap .frm-slicing-title span{font-size:13px!important}
/* 15 Nov 2026 (fh-detail-trigger-20261115): "Flight Detail" = full-width row, arrow on the right */
.result-step .flights-list-item .accordion-button.tnb-detail-trigger{display:flex!important;width:100%!important;align-items:center!important;justify-content:flex-start!important;gap:0!important;padding:0 14px!important;min-height:40px!important;font-weight:700!important}
.result-step .flights-list-item .accordion-button.tnb-detail-trigger::after{margin-left:auto!important}
/* 15 Nov 2026 (fh-filter-sticky-20261115): "Filter Flights / Reset All" stays visible while the filter box scrolls (desktop); full width, square corners so nothing shows behind it */
@media (min-width:992px){.tnb-results-layout .filter-searchBar .filter-searchBar-head{position:sticky!important;top:0!important;z-index:6!important;background:#fff!important;border-bottom:1px solid #e8eef4!important}.tnb-results-layout .tnb-filter-column .filter-searchBar .filter-searchBar-head{margin:0!important;width:auto!important;border-radius:0!important}}
/* 15 Nov 2026 (fh-fare-compare-20261115): Compare Fares small texts readable, cheapest fare marked (desktop; Compare Fares is hidden on mobile) */
@media (min-width:761px){#tnbFareCompare .tnb-fare-overview-head p{font-size:11px!important;line-height:1.4!important}#tnbFareCompare .tnb-compare-link,#tnbFareCompare .tnb-show-all-fares{font-size:11px!important}#tnbFareCompare .tnb-fare-head .tnb-airline-code{font-size:9.5px!important}#tnbFareCompare .tnb-fare-note{font-size:10.5px!important}#tnbFareCompare .tnb-fare-cell.is-cheapest{background:#ecfdf3!important}#tnbFareCompare .is-cheapest .tnb-fare-filter{background:transparent!important}#tnbFareCompare .is-cheapest .tnb-fare-price{color:#067647!important}#tnbFareCompare .tnb-cheapest-tag{display:inline-block;margin-top:2px;padding:1px 7px;border-radius:999px;background:#067647;color:#fff;font-size:9.5px;font-weight:800;line-height:1.5;letter-spacing:.02em}}
/* 15 Nov 2026 (fh-fare-hidden-20261115): keep matrix cells hidden when the page JS hides them (style display:none) */
#tnbFareCompare .tnb-fare-filter[style*="display: none"],#tnbFareCompare .tnb-fare-filter[style*="display:none"]{display:none!important}
/* 15 Nov 2026 (fh-filter-collapse-20261115): less-used filter sections start closed; click the title to open (desktop) */
@media (min-width:992px){
.filter-searchBar .tnb-fsec-toggle{display:flex;align-items:center;gap:8px;width:100%;flex:1 1 auto;cursor:pointer;user-select:none}
.filter-searchBar .tnb-fsec-toggle:focus-visible{outline:2px solid #1761bb;outline-offset:2px;border-radius:4px}
.filter-searchBar .tnb-fsec-meta{margin-left:auto;font-size:11px;font-weight:600;color:#7c8ba0;white-space:nowrap}
.filter-searchBar .tnb-fsec-meta.is-on{color:#d7193f}
.filter-searchBar .tnb-fsec-toggle>i{font-size:11px;color:#7c8ba0;transition:transform .15s}
.filter-searchBar .tnb-fsec:not(.is-collapsed) .tnb-fsec-toggle>i{transform:rotate(180deg)}
.filter-searchBar .searchBar-single.is-collapsed .searchBar-single-wrap{display:none!important}
.filter-searchBar .searchBar-single.is-collapsed .searchBar-single-title{margin-bottom:0!important}
.filter-searchBar .tnb-extra-filter.is-collapsed>:not(.tnb-fsec-toggle){display:none!important}
.filter-searchBar .tnb-extra-filter.is-collapsed .tnb-fsec-toggle h6{margin-bottom:0!important}
}
</style>
<style id="fh-results-polish-20261113">
/* =========================================================
   FaresHunt — Flight Results Polish (2026-11-13)
   Fixes: card height, alignment, detail hierarchy, spacing
   Safe: CSS only. No HTML, no JS, no backend logic touched.
   ========================================================= */

/* ---------- 1. CARD HEIGHT: 104px → 84px ---------- */
.result-step .tnb-itin-row{
    min-height:84px!important;
    padding:11px 16px!important;
    gap:10px!important;
}

/* Tighter airline block */
.result-step .tnb-airline img{
    width:32px!important;
    height:32px!important;
}
.result-step .tnb-airline strong{
    font-size:11.5px!important;
    line-height:1.2!important;
}
.result-step .tnb-airline span{
    font-size:8.5px!important;
    margin-top:1px!important;
}

/* Tighter time block */
.result-step .tnb-time strong{
    font-size:16px!important;
    line-height:1.15!important;
}
.result-step .tnb-time span{
    font-size:8.5px!important;
}

/* Tighter path block */
.result-step .tnb-path>span{
    font-size:8.5px!important;
}
.result-step .tnb-path-line{
    margin:5px 0!important;
}
.result-step .tnb-path b{
    font-size:8.5px!important;
}
.result-step .tnb-path small{
    font-size:8px!important;
}

/* ---------- 2. ALIGNMENT: consistent grid + centered price ---------- */
.result-step .tnb-itin-row{
    grid-template-columns:
        minmax(160px,1.15fr)
        92px
        minmax(120px,1fr)
        92px
        68px!important;
    align-items:center!important;
}

.result-step .tnb-price-col{
    min-width:172px!important;
    border-left:1px solid #e5ebf3!important;
}
.result-step .tnb-price-box{
    min-height:0!important;
    padding:12px 14px!important;
    display:flex!important;
    flex-direction:column!important;
    justify-content:center!important;
    align-items:center!important;
    text-align:center!important;
    gap:3px!important;
}
.result-step .tnb-price-box strong{
    font-size:24px!important;
    line-height:1.1!important;
    color:#0a2440!important;
}
.result-step .tnb-price-box small,
.result-step .tnb-price-box>span{
    font-size:9px!important;
    line-height:1.3!important;
    color:#7c8ba0!important;
}
.result-step .tnb-old-price{
    font-size:10px!important;
    text-decoration:line-through!important;
    opacity:.7!important;
}
.result-step .tnb-select-btn{
    width:100%!important;
    max-width:140px!important;
    margin-top:8px!important;
    min-height:36px!important;
    font-size:11px!important;
    font-weight:800!important;
}

/* ---------- 3. FLIGHT DETAILS: stronger hierarchy ---------- */
.result-step .accordion{
    margin:0!important;
    padding:0!important;
    border-top:1px solid #e8eef4!important;
}
.result-step .accordion-button{
    min-height:38px!important;
    padding:0 16px!important;
    color:#d7193f!important;
    font-size:11px!important;
    font-weight:800!important;
    letter-spacing:.01em!important;
    background:#fafcff!important;
    box-shadow:none!important;
}
/* Left plane icon */
.result-step .accordion-button::before{
    content:"\f072";
    font-family:"Font Awesome 6 Free";
    font-weight:900;
    margin-right:8px;
    font-size:11px;
    color:#d7193f;
}
.result-step .accordion-button:not(.collapsed){
    background:#fff5f7!important;
}
.result-step .accordion-button:hover{
    background:#f2f6fb!important;
    color:#0a2440!important;
}
.result-step .accordion-button:focus{
    box-shadow:none!important;
    outline:0!important;
}
.result-step .accordion-body{
    padding:0!important;
    background:#fbfcfe!important;
    border-top:1px solid #e8eef4!important;
}

/* ---------- 4. SPACING: consistent between cards ---------- */
.tnb-flight-results-column>.row{
    gap:10px!important;
    margin:0!important;
}
.tnb-flight-results-column .result-step{
    margin-bottom:0!important;
}
.result-step .flights-list-item{
    overflow:hidden!important;
    border-radius:14px!important;
}

/* ---------- TABLET: stack price below, keep alignment ---------- */
@media(min-width:761px) and (max-width:991px){
    .result-step .tnb-itin-row{
        grid-template-columns:
            minmax(140px,1.1fr)
            82px
            minmax(100px,.9fr)
            82px!important;
        min-height:76px!important;
        padding:10px 14px!important;
    }
    .result-step .tnb-cabin{
        display:none!important;
    }
    .result-step .tnb-price-col{
        min-width:0!important;
        border-left:0!important;
        border-top:1px solid #e5ebf3!important;
    }
    .result-step .tnb-price-box{
        flex-direction:row!important;
        justify-content:space-between!important;
        align-items:center!important;
        text-align:left!important;
        padding:12px 16px!important;
        gap:12px!important;
    }
    .result-step .tnb-price-box .tnb-select-btn{
        width:auto!important;
        max-width:none!important;
        margin-top:0!important;
        padding:0 18px!important;
    }
}
</style>
<script id="fh-results-polish-20261113-js">
document.addEventListener('DOMContentLoaded', function () {
    // Search box dates: weekday under the date (updates when the date changes).
    ['depature_date', 'return_date'].forEach(function (id) {
        var el = document.getElementById(id), fp = el && el._flatpickr;
        if (!fp || !fp.altInput) return;
        var sub = document.createElement('span');
        sub.className = 'fhx-date-sub';
        fp.altInput.insertAdjacentElement('afterend', sub);
        var update = function () {
            sub.textContent = fp.selectedDates && fp.selectedDates[0] ? fp.formatDate(fp.selectedDates[0], 'l') : '';
        };
        if (fp.config && Array.isArray(fp.config.onChange)) fp.config.onChange.push(update);
        update();
    });

    // Flexible dates: arrows + selected date in view.
    var strip = document.querySelector('.tnb-price-calendar .tnb-date-strip');
    if (strip && window.matchMedia('(min-width:761px)').matches) {
        var box = strip.parentElement;
        var make = function (dir) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'fh-strip-nav ' + dir;
            b.setAttribute('aria-label', dir === 'prev' ? 'Earlier dates' : 'Later dates');
            b.innerHTML = '<span><i class="fa-solid fa-chevron-' + (dir === 'prev' ? 'left' : 'right') + '"></i></span>';
            b.style.top = strip.offsetTop + 'px';
            b.style.height = strip.offsetHeight + 'px';
            b.style.bottom = 'auto';
            b.addEventListener('click', function () {
                strip.scrollBy({left: (dir === 'prev' ? -1 : 1) * Math.max(200, strip.clientWidth * 0.7), behavior: 'smooth'});
            });
            box.appendChild(b);
            return b;
        };
        var prev = make('prev'), next = make('next');
        var sync = function () {
            prev.hidden = strip.scrollLeft <= 4;
            next.hidden = strip.scrollLeft + strip.clientWidth >= strip.scrollWidth - 4;
        };
        var active = strip.querySelector('.tnb-date-pill.active');
        if (active) strip.scrollLeft = Math.max(0, active.offsetLeft - strip.offsetLeft - (strip.clientWidth - active.offsetWidth) / 2);
        strip.addEventListener('scroll', sync, {passive: true});
        window.addEventListener('resize', function () {
            prev.style.top = next.style.top = strip.offsetTop + 'px';
            prev.style.height = next.style.height = strip.offsetHeight + 'px';
            sync();
        });
        sync();
    }
});
</script>
<script id="fh-filter-collapse-20261115">
/* 15 Nov 2026: filter sections open/close (desktop only). Filter logic is not touched: same checkboxes, same events. */
(function () {
    function init() {
        if (!window.matchMedia || !window.matchMedia('(min-width:992px)').matches) return;
        var body = document.getElementById('filterForm');
        if (!body || body.getAttribute('data-fh-collapse')) return;
        body.setAttribute('data-fh-collapse', '1');
        var keepOpen = ['price range', 'onward stops', 'return stops', 'departure time', 'max duration'];
        var all = [];
        body.querySelectorAll('.searchBar-single, .tnb-extra-filter').forEach(function (sec) {
            var h = sec.querySelector('h6');
            var boxes = sec.querySelectorAll('input[type=checkbox]');
            if (!h || !boxes.length) return;
            var title = (h.textContent || '').trim().toLowerCase();
            var t = document.createElement('div');
            t.className = 'tnb-fsec-toggle';
            t.setAttribute('role', 'button');
            t.tabIndex = 0;
            h.parentNode.insertBefore(t, h);
            t.appendChild(h);
            var meta = document.createElement('span');
            meta.className = 'tnb-fsec-meta';
            t.appendChild(meta);
            var ic = document.createElement('i');
            ic.className = 'fa-solid fa-chevron-down';
            ic.setAttribute('aria-hidden', 'true');
            t.appendChild(ic);
            sec.classList.add('tnb-fsec');
            var upd = function () {
                var n = sec.querySelectorAll('input[type=checkbox]:checked').length;
                meta.textContent = n ? n + ' selected' : boxes.length + ' options';
                meta.classList.toggle('is-on', n > 0);
                t.setAttribute('aria-expanded', sec.classList.contains('is-collapsed') ? 'false' : 'true');
            };
            if (keepOpen.indexOf(title) === -1 && !sec.querySelector('input[type=checkbox]:checked')) sec.classList.add('is-collapsed');
            var toggle = function () { sec.classList.toggle('is-collapsed'); upd(); };
            t.addEventListener('click', toggle);
            t.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggle(); } });
            sec.addEventListener('change', function () {
                if (sec.querySelector('input[type=checkbox]:checked')) sec.classList.remove('is-collapsed');
                upd();
            });
            upd();
            all.push(upd);
        });
        ['clearAllPremium', 'clearAll'].forEach(function (id) {
            var b = document.getElementById(id);
            if (b) b.addEventListener('click', function () { setTimeout(function () { all.forEach(function (u) { u(); }); }, 0); });
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
</script>
<script id="fh-fare-cheapest-20261115">
/* 15 Nov 2026: Compare Fares - mark the lowest visible fare. Re-checks whenever the matrix prices change
   (filters / fare clicks rewrite them in updateFareMatrixFromVisibleRows). Display only. */
(function () {
    var last = null;
    function mark() {
        var box = document.getElementById('tnbFareCompare');
        if (!box) return;
        var btns = Array.prototype.filter.call(box.querySelectorAll('.tnb-fare-filter'), function (b) {
            return b.style.display !== 'none' && b.offsetParent !== null;
        });
        var vals = btns.map(function (b) {
            var p = b.querySelector('.tnb-fare-price');
            return p ? Number(String(p.textContent).replace(/[^0-9.]/g, '')) : NaN;
        });
        var sig = vals.join(',');
        if (sig === last) return;
        last = sig;
        box.querySelectorAll('.tnb-fare-cell.is-cheapest').forEach(function (c) { c.classList.remove('is-cheapest'); });
        box.querySelectorAll('.tnb-cheapest-tag').forEach(function (t) { t.remove(); });
        var ok = vals.filter(function (v) { return v > 0; });
        if (ok.length < 2) return;
        var min = Math.min.apply(null, ok);
        btns.forEach(function (b, i) {
            if (vals[i] !== min) return;
            var cell = b.closest('.tnb-fare-cell');
            if (cell) cell.classList.add('is-cheapest');
            var t = document.createElement('span');
            t.className = 'tnb-cheapest-tag';
            t.textContent = 'Cheapest';
            b.appendChild(t);
        });
    }
    function init() {
        var box = document.getElementById('tnbFareCompare');
        if (!box) return;
        mark();
        var pending = false;
        new MutationObserver(function () {
            if (pending) return;
            pending = true;
            setTimeout(function () { pending = false; mark(); }, 0);
        }).observe(box, { subtree: true, childList: true, characterData: true, attributes: true, attributeFilter: ['style'] });
        window.addEventListener('resize', mark);
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
</script>


@endsection
