@extends('crm.layout')
@section('content')

<style id="fareshunt-booking-detail-clean-rollback-v5">
/* CLEAN ROLLBACK V5
   Keeps the original premium page structure that looked better.
   Only removes the noisy form helper buttons and compacts servicing.
   No JS reordering. No full-width lower-section hacks. */

.bdo-page form .crm-ua-form-tools{
    display:none!important;
}

/* Keep the original balanced 2-column booking-detail layout */
@media(min-width:1051px){
    .bdo-page .bdo-layout{
        display:grid!important;
        grid-template-columns:minmax(0,1.55fr) minmax(340px,.75fr)!important;
        gap:16px!important;
        align-items:start!important;
    }
}

/* Restore natural sticky operations card — no internal scrollbar */
.bdo-page .bdo-sticky{
    position:sticky!important;
    top:96px!important;
    max-height:none!important;
    overflow:visible!important;
}

/* Compact servicing accordions without changing where sections live */
.bdo-page #booking-servicing-v2 .bdo-body{
    padding:14px!important;
}
.bdo-page #booking-servicing-v2 .svc-grid{
    gap:10px!important;
}
.bdo-page #booking-servicing-v2 .svc-box{
    padding:0!important;
    overflow:hidden!important;
    background:#fff!important;
    border:1px solid #dce6f2!important;
}
.bdo-page #booking-servicing-v2 .svc-box > summary{
    min-height:52px!important;
    display:flex!important;
    align-items:center!important;
    padding:0 14px!important;
    list-style:none!important;
    cursor:pointer!important;
    background:#fbfdff!important;
    color:#0b315f!important;
    font-size:12px!important;
    font-weight:800!important;
}
.bdo-page #booking-servicing-v2 .svc-box > summary::-webkit-details-marker{
    display:none!important;
}
.bdo-page #booking-servicing-v2 .svc-box > summary:before{
    content:"›";
    display:inline-grid;
    place-items:center;
    width:22px;
    height:22px;
    margin-right:7px;
    border-radius:7px;
    background:#edf4ff;
    color:#1f5bd8;
    font-size:16px;
    line-height:1;
    transition:transform .16s ease;
}
.bdo-page #booking-servicing-v2 .svc-box[open] > summary:before{
    transform:rotate(90deg);
}
.bdo-page #booking-servicing-v2 .svc-box[open] > summary{
    border-bottom:1px solid #e7eef6!important;
}
.bdo-page #booking-servicing-v2 .svc-box > form,
.bdo-page #booking-servicing-v2 .svc-box > details,
.bdo-page #booking-servicing-v2 .svc-box > .svc-note,
.bdo-page #booking-servicing-v2 .svc-box > .svc-history,
.bdo-page #booking-servicing-v2 .svc-box > b,
.bdo-page #booking-servicing-v2 .svc-box > hr{
    margin-left:14px!important;
    margin-right:14px!important;
}
.bdo-page #booking-servicing-v2 .svc-box > form:first-of-type,
.bdo-page #booking-servicing-v2 .svc-box > .svc-note:first-of-type,
.bdo-page #booking-servicing-v2 .svc-box > b:first-of-type{
    margin-top:14px!important;
}
.bdo-page #booking-servicing-v2 .svc-box > form:last-child,
.bdo-page #booking-servicing-v2 .svc-box > details:last-child,
.bdo-page #booking-servicing-v2 .svc-box > .svc-history:last-child{
    margin-bottom:14px!important;
}

/* Restore normal lower cards in the left main column */
.bdo-page .bdo-fare-card-v3,
.bdo-page .bdo-activity-card-v3{
    width:auto!important;
    max-width:none!important;
    grid-column:auto!important;
}
.bdo-page .bdo-fare-card-v3 .bdo-fare-layout{
    display:grid!important;
    grid-template-columns:minmax(0,1.1fr) minmax(260px,.9fr)!important;
    gap:16px!important;
    align-items:start!important;
}

/* Slightly cleaner spacing */
.bdo-page .bdo-card{
    margin-bottom:16px!important;
}
.bdo-page .bdo-action-center,
.bdo-page .bdo-tabs,
.bdo-page .bdo-stagebar{
    margin-bottom:14px!important;
}

@media(max-width:1050px){
    .bdo-page .bdo-layout{
        grid-template-columns:1fr!important;
    }
    .bdo-page .bdo-sticky{
        position:static!important;
    }
}
@media(max-width:760px){
    .bdo-page .bdo-fare-card-v3 .bdo-fare-layout{
        grid-template-columns:1fr!important;
    }
}
</style>


<style id="fareshunt-booking-detail-premium-v1">
:root{
  --bdo-navy:#071f4f;
  --bdo-navy2:#0d356f;
  --bdo-blue:#1f5bd8;
  --bdo-red:#e3194a;
  --bdo-line:#dce6f2;
  --bdo-muted:#73839b;
  --bdo-bg:#f4f8fd;
  --bdo-card:#fff;
}

.bdo-page{
  font-family:"Jost",sans-serif!important;
  color:#173555;
}

/* TOP */
.bdo-topbar{
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:22px;
  padding:24px 26px;
  margin-bottom:16px;
  border:1px solid var(--bdo-line);
  border-radius:20px;
  background:
    radial-gradient(circle at 100% 0,rgba(35,91,216,.08),transparent 35%),
    linear-gradient(135deg,#fff,#f8fbff);
  box-shadow:0 14px 32px rgba(12,44,88,.05);
}
.bdo-topbar .crm-back{
  display:inline-flex;
  align-items:center;
  color:#5d718d!important;
  font-size:12px!important;
  font-weight:500;
}
.bdo-title-row{
  display:flex;
  align-items:center;
  gap:12px;
  margin-top:10px;
}
.bdo-title-row h1{
  margin:0;
  color:var(--bdo-navy);
  font-size:38px;
  line-height:1;
  font-weight:800;
  letter-spacing:-.035em;
}
.bdo-topbar p{
  margin:10px 0 0;
  color:#6f8098;
  font-size:14px;
}
.bdo-quick-actions{
  display:flex;
  align-items:center;
  justify-content:flex-end;
  gap:10px;
  flex-wrap:wrap;
}
.bdo-quick-actions form{
  margin:0;
}
.bdo-quick-actions .btn{
  min-height:44px;
  padding:0 16px!important;
  border-radius:12px!important;
  font-size:12px!important;
  font-weight:700!important;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:7px;
  white-space:nowrap;
  transition:all .18s ease;
}
.bdo-quick-actions .btn i{
  font-size:13px;
  margin-right:0!important;
  opacity:.88;
}
.bdo-quick-actions .btn-light{
  border:1px solid #dbe4ef!important;
  background:linear-gradient(180deg,#ffffff,#f8fafc)!important;
  color:#294969!important;
  box-shadow:0 3px 10px rgba(15,45,80,.05);
}
.bdo-quick-actions .btn-light:hover{
  background:#fff!important;
  border-color:#bfcde0!important;
  color:#123c69!important;
  transform:translateY(-1px);
  box-shadow:0 7px 16px rgba(15,45,80,.09);
}
.bdo-quick-actions .btn-primary{
  background:linear-gradient(135deg,#174fbc,#2f73ea)!important;
  border-color:#1f5fd1!important;
  color:#fff!important;
  box-shadow:0 8px 18px rgba(31,91,216,.20);
}
.bdo-quick-actions .btn-primary:hover{
  background:linear-gradient(135deg,#1244a8,#2468dc)!important;
  transform:translateY(-1px);
  box-shadow:0 11px 22px rgba(31,91,216,.26);
}
.bdo-quick-actions .crm-ua-archive{
  color:#b4233f!important;
  background:linear-gradient(180deg,#fffafa,#fff4f6)!important;
  border-color:#efc4cd!important;
  box-shadow:0 3px 10px rgba(180,35,63,.05);
}
.bdo-quick-actions .crm-ua-archive:hover{
  color:#991b35!important;
  background:#fff0f3!important;
  border-color:#e4a9b6!important;
  transform:translateY(-1px);
  box-shadow:0 7px 16px rgba(180,35,63,.10);
}

/* NEXT ACTION */
.bdo-next-action{
  display:flex;
  align-items:center;
  gap:12px;
  margin:0 0 14px;
  padding:14px 16px;
  border:1px solid #f2d39a;
  border-radius:14px;
  background:#fffaf0;
}
.bdo-next-action>span{
  width:38px;height:38px;display:grid;place-items:center;
  border-radius:11px;background:#fff0cc;color:#b66b00;
}
.bdo-next-action small{
  display:block;color:#8b7248;font-size:9px;text-transform:uppercase;font-weight:800;letter-spacing:.07em
}
.bdo-next-action strong{display:block;color:#60410a;font-size:13px}
.bdo-next-action em{display:block;margin-top:2px;color:#95764f;font-size:10px;font-style:normal}

/* STAGES */
.bdo-stagebar{
  display:grid;
  grid-template-columns:repeat(6,1fr);
  overflow:hidden;
  margin-bottom:14px;
  border:1px solid var(--bdo-line);
  border-radius:16px;
  background:#fff;
  box-shadow:0 10px 24px rgba(12,44,88,.04);
}
.bdo-stagebar>div{
  position:relative;
  min-height:72px;
  display:flex;
  align-items:center;
  gap:10px;
  padding:0 16px;
  border-right:1px solid #e8eef5;
  color:#8b99ac;
}
.bdo-stagebar>div:last-child{border-right:0}
.bdo-stagebar>div span{
  width:34px;height:34px;flex:0 0 34px;
  display:grid;place-items:center;
  border-radius:50%;
  background:#eef2f6;
  color:#6f849e;
  font-size:11px;
  font-weight:800;
}
.bdo-stagebar>div b{
  font-size:12px;
  font-weight:700;
}
.bdo-stagebar>div.done{
  background:#f7fbff;
  color:#2f5d9d;
}
.bdo-stagebar>div.done span{
  background:#e6f1ff;
  color:#1f5bd8;
}
.bdo-stagebar>div.active{
  background:linear-gradient(180deg,#edf4ff,#f8fbff);
  color:#1755ca;
  box-shadow:inset 0 -4px 0 #1f5bd8;
}
.bdo-stagebar>div.active span{
  background:linear-gradient(145deg,#1c56d0,#2f70ef);
  color:#fff;
}

/* TABS */
.bdo-tabs{
  display:flex;
  gap:8px;
  align-items:center;
  margin-bottom:14px;
  padding:8px;
  border:1px solid var(--bdo-line);
  border-radius:14px;
  background:#fff;
  overflow:auto;
}
.bdo-tabs a{
  padding:9px 13px;
  border-radius:9px;
  color:#536a88;
  text-decoration:none;
  font-size:12px;
  font-weight:650;
  white-space:nowrap;
}
.bdo-tabs a:hover{
  background:#f5f8fc;
  color:#1b58ca;
}
.bdo-tabs a.active{
  background:#edf4ff;
  color:#1d5bd2;
  font-weight:800;
}

/* QUICK ACTION CENTER */
.bdo-action-center{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:18px;
  padding:16px 18px;
  margin-bottom:14px;
  border-radius:18px;
  background:linear-gradient(135deg,#0b2d5d,#153f7a);
  color:#fff;
  box-shadow:0 14px 30px rgba(10,45,94,.15);
}
.bdo-action-center-title{
  display:flex;
  align-items:center;
  gap:12px;
}
.bdo-action-center-title>span{
  width:46px;height:46px;display:grid;place-items:center;
  border-radius:13px;background:rgba(255,255,255,.11);
  color:#ffd86a;font-size:18px;
}
.bdo-action-center-title small{
  display:block;color:#aecdff;font-size:9px;text-transform:uppercase;font-weight:800;letter-spacing:.08em
}
.bdo-action-center-title strong{
  display:block;margin-top:3px;color:#fff;font-size:15px
}
.bdo-action-center-links{
  display:flex;
  align-items:center;
  justify-content:flex-end;
  gap:8px;
  flex-wrap:wrap;
}
.bdo-action-center-links a{
  min-height:40px;
  display:inline-flex;
  align-items:center;
  gap:7px;
  padding:0 12px;
  border:1px solid rgba(255,255,255,.13);
  border-radius:10px;
  background:rgba(255,255,255,.08);
  color:#fff;
  text-decoration:none;
  font-size:11px;
}
.bdo-action-center-links a:hover{
  background:rgba(255,255,255,.14);
}

/* SUMMARY */
.bdo-summary-grid-v3{
  display:grid;
  grid-template-columns:repeat(8,minmax(0,1fr));
  gap:12px;
  margin-bottom:14px;
}
.bdo-summary-card{
  min-height:150px;
  position:relative;
  overflow:hidden;
  padding:16px;
  border:1px solid #e0e8f1;
  border-radius:16px;
  background:#fff;
  box-shadow:0 9px 24px rgba(12,44,88,.045);
}
.bdo-summary-card:after{
  content:"";
  position:absolute;
  width:105px;height:105px;
  right:-32px;top:-32px;
  border-radius:50%;
  background:rgba(255,255,255,.34);
}
.summary-icon{
  position:relative;z-index:2;
  width:38px;height:38px;display:grid;place-items:center;
  border-radius:11px;background:rgba(255,255,255,.72);
  font-size:14px;
}
.bdo-summary-card>small{
  position:relative;z-index:2;
  display:block;margin-top:18px;
  color:#7d8da3;font-size:8px;text-transform:uppercase;font-weight:800;letter-spacing:.06em
}
.bdo-summary-card>strong{
  position:relative;z-index:2;
  display:block;margin-top:7px;
  color:#173555;font-size:18px;line-height:1.25
}
.summary-purple{background:linear-gradient(145deg,#f4efff,#fbf9ff)}
.summary-blue{background:linear-gradient(145deg,#edf4ff,#f8fbff)}
.summary-amber{background:linear-gradient(145deg,#fff6df,#fffaf0)}
.summary-green{background:linear-gradient(145deg,#ecfaf3,#f8fffb)}
.summary-cyan{background:linear-gradient(145deg,#eaf9fb,#f8feff)}
.summary-indigo{background:linear-gradient(145deg,#eef0ff,#f9faff)}
.summary-rose{background:linear-gradient(145deg,#fff0f3,#fff9fa)}
.summary-slate{background:linear-gradient(145deg,#f1f4f7,#fbfcfd)}

.summary-status-good>strong{color:#16804b}
.summary-status-warn>strong{color:#b87107}
.summary-status-bad>strong{color:#c92b49}
.summary-status-purple>strong{color:#6d3cc3}
.summary-status-info>strong{color:#205bcc}

/* OPS STRIP */
.bdo-ops-strip{
  display:grid;
  grid-template-columns:repeat(4,1fr);
  gap:10px;
  margin-bottom:14px;
}
.bdo-ops-strip-item{
  display:flex;
  align-items:center;
  gap:10px;
  padding:12px 14px;
  border:1px solid var(--bdo-line);
  border-radius:14px;
  background:#fff;
}
.ops-strip-icon{
  width:36px;height:36px;display:grid;place-items:center;border-radius:11px
}
.ops-strip-icon.blue{background:#edf4ff;color:#1f5bd8}
.ops-strip-icon.purple{background:#f2ebff;color:#7440c6}
.ops-strip-icon.green{background:#eaf8f0;color:#16804b}
.ops-strip-icon.amber{background:#fff5df;color:#b87107}
.bdo-ops-strip-item small{
  display:block;color:#8392a6;font-size:8px;text-transform:uppercase;font-weight:800
}
.bdo-ops-strip-item strong{
  display:block;margin-top:3px;color:#173555;font-size:12px
}
.bdo-ops-strip-item em{
  display:block;margin-top:2px;color:#8290a4;font-size:9px;font-style:normal
}

/* LOWER LAYOUT */
.bdo-layout{
  display:grid;
  grid-template-columns:minmax(0,1.55fr) minmax(340px,.75fr);
  gap:16px;
  align-items:start;
}
.bdo-layout main,
.bdo-layout aside{
  min-width:0;
}
.bdo-card{
  margin-bottom:16px;
  border:1px solid var(--bdo-line);
  border-radius:18px;
  background:#fff;
  box-shadow:0 10px 26px rgba(12,44,88,.045);
  overflow:hidden;
}
.bdo-card>header{
  min-height:62px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  padding:15px 18px;
  border-bottom:1px solid #e9eff5;
  background:linear-gradient(180deg,#fff,#fbfdff);
}
.bdo-card>header h3{
  margin:2px 0 0;
  color:var(--bdo-navy);
  font-size:16px;
  font-weight:800;
}
.bdo-kicker{
  display:block;
  color:#1f5bd8;
  font-size:8px;
  text-transform:uppercase;
  font-weight:850;
  letter-spacing:.08em;
}
.bdo-body{
  padding:18px;
}
.bdo-info-grid{
  display:grid;
  grid-template-columns:repeat(3,minmax(0,1fr));
  gap:10px;
}
.bdo-info-grid>div{
  min-height:76px;
  padding:12px;
  border:1px solid #e5ecf4;
  border-radius:12px;
  background:#fbfdff;
}
.bdo-info-grid small{
  display:block;
  color:#8290a4;
  font-size:9px;
  text-transform:uppercase;
  font-weight:800;
}
.bdo-info-grid strong{
  display:block;
  margin-top:5px;
  color:#183858;
  font-size:13px;
  word-break:break-word;
}

/* PASSENGERS */
.premium-pax{
  display:grid;
  grid-template-columns:90px 1fr;
  gap:16px;
  padding:16px;
  border:1px solid #e1e9f2;
  border-radius:15px;
  background:#fbfdff;
  margin-bottom:12px;
}
.bdo-passenger-avatar{
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
  min-height:110px;
  border-radius:13px;
  background:linear-gradient(145deg,#0d356f,#1f5bd8);
  color:#fff;
}
.bdo-passenger-avatar span{font-size:28px;font-weight:800}
.bdo-passenger-avatar small{margin-top:6px;font-size:8px;opacity:.75}
.premium-pax-head{
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:12px;
}
.premium-pax-head small{
  display:block;color:#8492a5;font-size:8px;text-transform:uppercase;font-weight:800
}
.premium-pax-head h4{
  margin:4px 0 0;color:#153555;font-size:17px
}
.premium-pax-type{
  padding:5px 8px;border-radius:999px;background:#edf4ff;color:#1f5bd8;font-size:9px;font-weight:800
}
.premium-name-grid,
.premium-pax-grid{
  display:grid;
  grid-template-columns:repeat(3,minmax(0,1fr));
  gap:9px;
  margin-top:12px;
}
.premium-name-grid>div,
.premium-pax-grid>div{
  padding:10px;
  border:1px solid #e6edf4;
  border-radius:10px;
  background:#fff;
}
.premium-name-grid small,
.premium-pax-grid small{
  display:block;color:#8593a6;font-size:8px;text-transform:uppercase;font-weight:800
}
.premium-name-grid strong,
.premium-pax-grid b{
  display:block;margin-top:4px;color:#183858;font-size:12px
}

/* FLIGHT */
.bdo-journey-label{
  margin:5px 0 9px;
  color:#173555;
  font-size:11px;
  text-transform:uppercase;
  font-weight:800;
  letter-spacing:.06em;
}
.bdo-flight-card{
  margin-bottom:12px;
  border:1px solid #e0e9f2;
  border-radius:15px;
  overflow:hidden;
  background:#fff;
}
.bdo-flight-main{
  display:grid;
  grid-template-columns:minmax(0,1fr) minmax(240px,1.2fr) minmax(0,1fr);
  align-items:center;
  gap:14px;
  padding:18px;
}
.bdo-flight-main strong{
  display:block;color:#0d3260;font-size:22px
}
.bdo-flight-main span{
  display:block;margin-top:3px;color:#647891;font-size:10px
}
.bdo-flight-main small{
  display:block;margin-top:5px;color:#8492a5;font-size:9px
}
.bdo-flight-mid{
  text-align:center;
}
.bdo-flight-mid i{
  display:block;
  margin:7px auto;
  color:#1f5bd8;
}
.bdo-flight-mid div{
  height:1px;
  background:linear-gradient(90deg,transparent,#aac4e7,transparent);
}
.bdo-flight-footer-v3{
  display:flex;
  gap:7px;
  flex-wrap:wrap;
  padding:11px 14px;
  border-top:1px solid #edf2f7;
  background:#f9fbfd;
}
.flight-chip{
  display:inline-flex;
  align-items:center;
  gap:5px;
  padding:5px 8px;
  border-radius:999px;
  font-size:8px;
}
.flight-chip.blue{background:#edf4ff;color:#205bcc}
.flight-chip.purple{background:#f2ebff;color:#6d3cc3}
.flight-chip.green{background:#eaf8f0;color:#16804b}
.flight-chip.slate{background:#eef2f6;color:#5a6d84}

/* FORMS */
.svc-box{
  box-shadow:none!important;
}
.svc-box summary{
  font-family:"Jost",sans-serif!important;
}
.svc-fields label,
.bdo-ops-form label>span,
.bdo-status-form label>span{
  font-size:10px!important;
  font-weight:800!important;
}
.bdo-remark-form{
  display:grid;
  grid-template-columns:1fr auto;
  gap:9px;
}

/* RIGHT SIDE */
.bdo-sticky{
  position:sticky;
  top:96px;
}
.bdo-side-status-grid{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:8px;
  margin-bottom:14px;
}
.bdo-side-status-grid>div{
  padding:10px;
  border:1px solid #e4ebf3;
  border-radius:10px;
  background:#fbfdff;
}
.bdo-side-status-grid small{
  display:block;color:#8290a4;font-size:8px;text-transform:uppercase;font-weight:800
}
.bdo-side-status-grid strong{
  display:block;margin-top:4px;color:#173555;font-size:11px
}
.bdo-side-actions{
  display:grid;
  gap:8px;
}
.bdo-side-actions a,
.bdo-side-actions button{
  width:100%;
  min-height:42px;
  display:flex;
  align-items:center;
  gap:8px;
  padding:0 11px;
  border:1px solid #dde7f1;
  border-radius:10px;
  background:#fff;
  color:#365777;
  text-decoration:none;
  font-size:11px;
}
.bdo-doc-count{
  display:flex;
  align-items:center;
  justify-content:space-between;
  margin-top:8px;
  padding:10px 0 0;
  border-top:1px solid #edf2f7;
  color:#718198;
  font-size:10px;
}
.bdo-doc-count strong{color:#163858}

/* RELATED */
.bdo-related a{
  display:block;
  padding:11px 0;
  border-bottom:1px solid #edf2f7;
  text-decoration:none;
}
.bdo-related a:last-child{border-bottom:0}
.bdo-related strong{
  display:block;color:#1250ad;font-size:12px
}
.bdo-related span{
  display:block;margin-top:3px;color:#7b8ba0;font-size:9px
}

/* Fix mojibake checkmark if it appears */
.bdo-stagebar .done span{
  font-size:0;
}
.bdo-stagebar .done span:after{
  content:"✓";
  font-size:12px;
  font-weight:900;
}

@media(max-width:1350px){
  .bdo-summary-grid-v3{grid-template-columns:repeat(4,1fr)}
  .bdo-layout{grid-template-columns:minmax(0,1fr) 330px}
}
@media(max-width:1050px){
  .bdo-topbar{flex-direction:column}
  .bdo-quick-actions{justify-content:flex-start}
  .bdo-stagebar{grid-template-columns:repeat(3,1fr)}
  .bdo-summary-grid-v3{grid-template-columns:repeat(2,1fr)}
  .bdo-ops-strip{grid-template-columns:1fr 1fr}
  .bdo-layout{grid-template-columns:1fr}
  .bdo-sticky{position:static}
}
@media(max-width:700px){
  .bdo-title-row h1{font-size:30px}
  .bdo-stagebar{grid-template-columns:1fr 1fr}
  .bdo-tabs{align-items:stretch}
  .bdo-action-center{align-items:flex-start;flex-direction:column}
  .bdo-summary-grid-v3{grid-template-columns:1fr}
  .bdo-ops-strip{grid-template-columns:1fr}
  .bdo-info-grid{grid-template-columns:1fr}
  .premium-pax{grid-template-columns:1fr}
  .premium-name-grid,.premium-pax-grid{grid-template-columns:1fr}
  .bdo-flight-main{grid-template-columns:1fr;text-align:left}
  .bdo-flight-mid{text-align:left}
  .bdo-remark-form{grid-template-columns:1fr}
}
</style>


<style id="fareshunt-booking-detail-production-v8">
/* Production polish only: no routes, forms or booking logic changed. */
.bdo-page{max-width:1540px;margin:0 auto;padding-bottom:34px}
.bdo-page .bdo-tabs{
  position:sticky;top:64px;z-index:18;
  background:rgba(247,250,253,.96);backdrop-filter:blur(10px);
  border:1px solid #e1e8f0;border-radius:14px;padding:7px;
  box-shadow:0 7px 22px rgba(22,52,86,.06)
}
.bdo-page .bdo-tabs a{border-radius:9px;padding:9px 12px}
.bdo-page .bdo-tabs a:hover{background:#fff;color:#1556bd}
.bdo-page .bdo-card{
  border:1px solid #dfe7f0!important;
  box-shadow:0 8px 24px rgba(26,54,86,.055)!important;
}
.bdo-page .bdo-card>header{
  min-height:68px;padding:15px 18px!important;
  background:linear-gradient(180deg,#fff,#fbfdff);
  border-bottom:1px solid #e8eef5!important;
}
.bdo-page .bdo-card>header h3{font-size:15px!important;letter-spacing:-.01em}
.bdo-page .fh-section-actions{display:flex;align-items:center;justify-content:flex-end;gap:7px;flex-wrap:wrap}
.bdo-page .fh-edit-btn{
  min-height:34px;padding:7px 11px!important;border-radius:9px!important;
  font-size:10px!important;font-weight:850!important;white-space:nowrap
}
.bdo-page .bdo-info-grid>div{
  min-height:66px;padding:12px 13px!important;border:1px solid #e7edf4;
  border-radius:11px;background:#fbfdff
}
.bdo-page .bdo-info-grid small{font-size:8px!important;letter-spacing:.065em}
.bdo-page .bdo-info-grid strong{margin-top:5px;line-height:1.35;overflow-wrap:anywhere}
.bdo-page .bdo-summary-card{min-height:126px}
.bdo-page .bdo-summary-card>strong{overflow-wrap:anywhere}
.bdo-page .bdo-flight-card{border-color:#e0e8f1!important}
.bdo-page .bdo-payment-card{box-shadow:none!important}
.bdo-page .bdo-payment-card small{
  display:block;margin-top:10px;padding-top:9px;border-top:1px solid rgba(255,255,255,.22);
  line-height:1.45
}
.bdo-page .fh-inline-editor{
  border-top:1px solid #dfe8f2!important;
  box-shadow:inset 0 8px 20px rgba(29,67,108,.025)
}
.bdo-page .fh-inline-editor.is-open{animation:fhEditorIn .16s ease-out}
@keyframes fhEditorIn{from{opacity:.35;transform:translateY(-4px)}to{opacity:1;transform:none}}
.bdo-page .fh-inline-editor .form-control,
.bdo-page .fh-inline-editor .form-select,
.bdo-page .bdo-ops-form .form-control,
.bdo-page .bdo-ops-form .form-select,
.bdo-page .bdo-status-form .form-control,
.bdo-page .bdo-status-form .form-select{
  min-height:39px;border-color:#d8e2ed;border-radius:9px
}
.bdo-page .fh-inline-editor .form-control:focus,
.bdo-page .fh-inline-editor .form-select:focus,
.bdo-page .bdo-ops-form .form-control:focus,
.bdo-page .bdo-ops-form .form-select:focus{
  border-color:#8bb3ef;box-shadow:0 0 0 3px rgba(47,111,214,.10)
}
/* Section-level edit is the production interaction; suppress legacy per-field edit clutter. */
.bdo-page .bdo-info-grid .crm-ua-actions,
.bdo-page .bdo-info-grid .crm-ua-edit,
.bdo-page .bdo-info-grid .crm-ua-form-tools{display:none!important}
.bdo-page .bdo-remark-form{
  padding:12px;border:1px solid #e3eaf2;border-radius:12px;background:#fbfdff
}
.bdo-page .bdo-timeline>div{padding-top:11px;padding-bottom:11px}
.bdo-page .bdo-side-status-grid>div{border:1px solid #e7edf4;border-radius:10px;background:#fbfdff;padding:10px}
.bdo-page #booking-operations .bdo-body>hr{border:0;border-top:1px solid #e8eef5;margin:17px 0}
.bdo-page .bdo-assignment-locked{box-shadow:inset 3px 0 0 #37a36f}
.bdo-page #booking-email-center .bdo-side-actions a,
.bdo-page #booking-email-center .bdo-side-actions button{min-height:38px}
.bdo-page .bdo-doc-count{padding:11px 0!important}
.bdo-page .fh-prod-security{
  margin-top:10px;padding:9px 10px;border:1px solid #d8e8df;border-radius:9px;
  background:#f4fbf7;color:#347154;font-size:9px;font-weight:700;line-height:1.45
}
.bdo-page .fh-prod-security i{margin-right:5px}
@media(max-width:1050px){
  .bdo-page .bdo-tabs{top:58px;overflow-x:auto;flex-wrap:nowrap;scrollbar-width:none}
  .bdo-page .bdo-tabs::-webkit-scrollbar{display:none}
  .bdo-page .bdo-tabs a{flex:0 0 auto}
}
@media(max-width:700px){
  .bdo-page{padding-left:10px!important;padding-right:10px!important}
  .bdo-page .bdo-card>header{align-items:flex-start!important;gap:10px}
  .bdo-page .fh-section-actions{justify-content:flex-start;width:100%}
  .bdo-page .fh-edit-btn{flex:1 1 auto;justify-content:center}
  .bdo-page .fh-inline-editor-head{align-items:flex-start;flex-direction:column}
  .bdo-page .fh-inline-editor-head .fh-edit-btn{flex:0 0 auto}
  .bdo-page .bdo-action-center-links{display:grid!important;grid-template-columns:1fr 1fr!important;width:100%}
  .bdo-page .bdo-action-center-links a{justify-content:flex-start!important}
}
</style>

@php
    $statusTone = $statusMeta[$booking->status]['tone'] ?? 'slate';
@endphp

@php
    $normalize = function($value) {
        if (!is_array($value)) return $value;
        // Common storage shape: [0 => {...}]
        if (array_keys($value) === range(0, count($value)-1) && count($value) === 1 && is_array($value[0])) {
            return $value[0];
        }
        return $value;
    };

    // 14 Nov 2026 (crm cabin label): show the cabin name, not the stored code ("Econom" -> "Economy").
    // Display only: stored data and the segment edit form keep the original value.
    $crmCabin = function ($cabin) {
        $c = trim((string) $cabin);
        if ($c === '') return '—';
        $map = ['econom' => 'Economy', 'economy' => 'Economy', 'peconom' => 'Premium Economy', 'premiumeconomy' => 'Premium Economy', 'premium economy' => 'Premium Economy', 'business' => 'Business', 'firstclass' => 'First', 'first' => 'First'];
        return $map[strtolower($c)] ?? $c;
    };
    $segments = function($value) use ($normalize) {
        $value = $normalize($value);
        $found = [];
        $walk = function($node) use (&$walk, &$found) {
            if (!is_array($node)) return;
            $keys = array_keys($node);
            $looksLikeFlight = isset($node['Departure_Iata']) || isset($node['Arrival_Iata'])
                || isset($node['Departure_Date']) || isset($node['Arrival_Date'])
                || isset($node['FlightNum']) || isset($node['AirlineName']);
            if ($looksLikeFlight) {
                $found[] = $node;
                return;
            }
            foreach ($node as $child) $walk($child);
        };
        $walk($value);
        return $found;
    };

    $passengers = $normalize($details['passengers'] ?? []);
    if (is_array($passengers) && array_keys($passengers) !== range(0, count($passengers)-1)) {
        $passengers = [$passengers];
    }
    $passengers = is_array($passengers) ? $passengers : [];

    $price = $normalize($details['price'] ?? []);
    $billing = $normalize($details['billing'] ?? []);
    $outbound = $segments($details['sector_go'] ?? []);
    $returnSegments = $segments($details['sector_back'] ?? []);

    $money = function($v) {
        return is_numeric($v) ? '$'.number_format((float)$v,2) : ($v ?: '—');
    };
@endphp

@php
    $allSegments = collect(array_merge($outbound,$returnSegments));
    $firstSegment = $outbound[0] ?? null;
    $firstReturn = $returnSegments[0] ?? null;

    $from = $firstSegment['Departure_Iata'] ?? $firstSegment['DepartureIata'] ?? '—';
    $to = $firstSegment['Arrival_Iata'] ?? $firstSegment['ArrivalIata'] ?? '—';
    $airline = $firstSegment['AirlineName'] ?? $firstSegment['OperatingAirlineName'] ?? $firstSegment['Airline'] ?? '—';

    $other = $normalize($details['other'] ?? []);
    $findValue = function($node, $keys) use (&$findValue) {
        if (!is_array($node)) return null;
        foreach($keys as $key) if(isset($node[$key]) && $node[$key] !== '') return $node[$key];
        foreach($node as $child) if(is_array($child)) {
            $found = $findValue($child,$keys);
            if($found !== null && $found !== '') return $found;
        }
        return null;
    };

    $pnrKeys = ['PNR','Pnr','pnr','BookingReference','BookingRef','RecordLocator'];
    $supplierOutboundPnr = $findValue($outbound, $pnrKeys);
    $supplierReturnPnr = $findValue($returnSegments, $pnrKeys);
    $pnr = $crmMeta->pnr
        ?: $supplierOutboundPnr
        ?: $supplierReturnPnr
        ?: $findValue(is_array($other)?$other:[], $pnrKeys);
    $ticketNo = $crmMeta->ticket_number ?: $findValue(is_array($other)?$other:[],['TicketNumber','TicketNo','E_Ticket','ETicket']);

    $paymentStatus = $crmMeta->payment_status ?: (
        $booking->status === 'Payment Declined' ? 'Declined' :
        (in_array($booking->status,['Refunded','Partial Refund'],true) ? 'Refunded' : 'Untracked')
    );

    $ticketStatus = $crmMeta->ticket_status ?: (
        in_array($booking->status,['Issued','Ticketed','E-Ticket'],true) ? 'Ticketed' : 'Not Ticketed'
    );

    $statusStage = match(true) {
        in_array($booking->status,['Fresh','New Booking','Incomplete'],true) => 1,
        in_array($booking->status,['Follow Up','No Response'],true) => 2,
        in_array($booking->status,['Booked','Sent to Ticketing'],true) => 3,
        in_array($booking->status,['Sent for Issuance','Issuance in Progress'],true) => 4,
        in_array($booking->status,['Issued','Ticketed'],true) => 5,
        $booking->status === 'E-Ticket' => 6,
        default => 1,
    };

    $stages = [
        1 => 'New',
        2 => 'Follow-up',
        3 => 'Ticketing',
        4 => 'Issuance',
        5 => 'Ticketed',
        6 => 'E-Ticket',
    ];

    $departureRaw = $firstSegment['Departure_Date'] ?? $firstSegment['DepartureDate'] ?? null;
    $travelDate = $departureRaw ? \Carbon\Carbon::parse($departureRaw) : null;
    $daysToDeparture = $travelDate ? now()->startOfDay()->diffInDays($travelDate->copy()->startOfDay(), false) : null;

    $assignedAgent = collect($admins)->firstWhere('id', $booking->assign_to);
    $assignedAgentName = $assignedAgent->name ?? 'Unassigned';

    $nextActionLabel = $crmMeta->next_action ?: 'No next action';
    $nextActionDate = $crmMeta->next_action_at ? \Carbon\Carbon::parse($crmMeta->next_action_at) : null;

    $paymentTone = match($paymentStatus) {
        'Paid' => 'good',
        'Pending','Partially Paid','Refund Pending' => 'warn',
        'Declined','Chargeback' => 'bad',
        'Refunded' => 'purple',
        default => 'neutral',
    };

    $ticketTone = match($ticketStatus) {
        'Issued','Ticketed','E-Ticket' => 'good',
        'Ticketing' => 'info',
        'Voided' => 'bad',
        'Exchanged' => 'purple',
        default => 'neutral',
    };
@endphp

<style id="fareshunt-booking-inline-edit-v6">
.bdo-page .fh-section-actions{display:flex;align-items:center;gap:7px;flex-wrap:wrap;justify-content:flex-end}
.bdo-page .fh-edit-btn{border:1px solid #cdddf2;background:#f6f9ff;color:#164fbe;border-radius:9px;padding:7px 10px;font-size:10px;font-weight:800;line-height:1;display:inline-flex;align-items:center;gap:6px;cursor:pointer;transition:.15s ease}
.bdo-page .fh-edit-btn:hover{background:#eaf2ff;border-color:#afc8ef;color:#0e43a8}
.bdo-page .fh-edit-btn.secondary{background:#fff;color:#52647c;border-color:#dce5ef}
.bdo-page .fh-edit-btn.secondary:hover{background:#f7f9fc;color:#173250}
.bdo-page .fh-inline-editor{display:none;border-top:1px solid #e7edf5;background:linear-gradient(180deg,#fbfdff,#f7faff);padding:16px 18px}
.bdo-page .fh-inline-editor.is-open{display:block}
.bdo-page .fh-inline-editor-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px}
.bdo-page .fh-inline-editor-head h4{margin:0;color:#082d59;font-size:13px;font-weight:850}
.bdo-page .fh-inline-editor-head p{margin:3px 0 0;color:#74849a;font-size:10px}
.bdo-page .fh-inline-editor .svc-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px;margin-top:12px}
.bdo-page .fh-inline-editor .svc-fields label{font-size:10px;font-weight:800;text-transform:uppercase;color:#718096}
.bdo-page .fh-inline-editor .svc-fields input,.bdo-page .fh-inline-editor .svc-fields select{margin-top:5px}
.bdo-page .fh-inline-editor .svc-wide{grid-column:1/-1}
.bdo-page .fh-inline-editor .svc-actions{display:flex;gap:8px;margin-top:12px;flex-wrap:wrap}
.bdo-page .fh-inline-editor .svc-danger{color:#b42318!important}
.bdo-page .fh-inline-editor .svc-history{font-size:11px;border-top:1px solid #edf1f6;padding:8px 0}
.bdo-page .fh-inline-editor .svc-note{font-size:11px;color:#667085;line-height:1.5;margin-top:8px}
.bdo-page .fh-inline-editor details{border:1px solid #dbe5f0;border-radius:11px;background:#fff;padding:10px 12px;margin-top:10px}
.bdo-page .fh-inline-editor details>summary{cursor:pointer;font-weight:800;color:#082d59}
.bdo-page .fh-editor-divider{height:1px;background:#e7edf5;margin:15px 0}
.bdo-page .fh-audit-list{display:grid;gap:7px}
@media(max-width:900px){
  .bdo-page .fh-inline-editor .svc-fields{grid-template-columns:1fr}
  .bdo-page .fh-section-actions{justify-content:flex-start}
}
</style>

<div class="bdo-page">
    <div class="bdo-topbar">
        <div>
            <a href="{{ route('crm.bookings') }}" class="crm-back"><i class="fa-solid fa-arrow-left me-1"></i>Back to bookings</a>
            <div class="bdo-title-row">
                <h1>#FH{{ $booking->id }}</h1>
                <span class="crm-status-badge tone-{{ $statusTone }}"><span class="crm-status-dot"></span>{{ $booking->status ?: 'Unspecified' }}</span>
            </div>
            <p>{{ $booking->o_Name ?: 'Guest' }} · {{ $from }} → {{ $to }} · Created {{ optional($booking->created_at)->format('d M Y, h:i A') }}</p>
        </div>

        <div class="bdo-quick-actions">
            <a class="btn btn-light" href="{{ route('crm.booking_email_preview',$booking->id) }}" target="_blank"><i class="fa-regular fa-eye me-1"></i>Confirmation</a>
            @if(\Illuminate\Support\Facades\Route::has('crm.invoice_create'))
                <a class="btn btn-light" href="{{ route('crm.invoice_create',$booking->id) }}"><i class="fa-solid fa-file-invoice-dollar me-1"></i>Invoice</a>
            @endif
            <a class="btn btn-light" href="#activity"><i class="fa-regular fa-clock me-1"></i>Activity</a>
            <a class="btn btn-primary" href="#booking-operations"><i class="fa-regular fa-pen-to-square me-1"></i>Update Booking</a>
            <form method="POST" action="{{ route('crm.universal_archive') }}" onsubmit="return confirm('Move this booking to CRM Trash? Source booking data will not be permanently deleted.')">@csrf
                <input type="hidden" name="module" value="booking"><input type="hidden" name="record_key" value="{{ $booking->id }}"><input type="hidden" name="label" value="#FH{{ $booking->id }} · {{ $booking->o_Name }}">
                <button class="btn btn-light crm-ua-archive"><i class="fa-regular fa-trash-can me-1"></i>Delete / Archive</button>
            </form>
        </div>
    </div>

    @if($crmMeta->next_action)
    <div class="bdo-next-action">
        <span><i class="fa-regular fa-bell"></i></span>
        <div>
            <small>Next action</small>
            <strong>{{ $crmMeta->next_action }}</strong>
            @if($crmMeta->next_action_at)
                <em>{{ \Carbon\Carbon::parse($crmMeta->next_action_at)->format('d M Y, h:i A') }}</em>
            @endif
        </div>
    </div>
    @endif

    <section class="bdo-stagebar">
        @foreach($stages as $num=>$label)
            <div class="{{ $num < $statusStage ? 'done' : ($num === $statusStage ? 'active' : '') }}">
                <span>{{ $num < $statusStage ? '✓' : $num }}</span>
                <b>{{ $label }}</b>
            </div>
        @endforeach
    </section>

    <nav class="bdo-tabs">
        <a href="#overview" class="active">Overview</a>
        <a href="#passengers">Passengers</a>
        <a href="#flight">Itinerary</a>
        <a href="#fare-payment">Fare & Payment</a>
        <a href="#booking-operations">Agent Workspace</a>
        <a href="#activity">Remarks & History</a>
    </nav>

    <section class="bdo-action-center">
        <div class="bdo-action-center-title">
            <span><i class="fa-solid fa-bolt"></i></span>
            <div><small>Agent Action Center</small><strong>Quick booking actions</strong></div>
        </div>
        <div class="bdo-action-center-links">
            <a href="#activity"><i class="fa-solid fa-phone"></i><span>Call / Follow-up</span></a>
            <a href="#activity"><i class="fa-regular fa-note-sticky"></i><span>Add Note</span></a>
            <a href="#booking-operations"><i class="fa-regular fa-bell"></i><span>Set Reminder</span></a>
            <a href="{{ route('crm.booking_email_preview',$booking->id) }}" target="_blank"><i class="fa-regular fa-envelope"></i><span>Email / Confirmation</span></a>
            <a href="#booking-operations"><i class="fa-solid fa-ticket"></i><span>Ticketing</span></a>
            <a href="#booking-operations"><i class="fa-solid fa-arrow-rotate-left"></i><span>Refund / Change</span></a>
        </div>
    </section>

    <section class="bdo-summary-grid bdo-summary-grid-v3" id="overview">
        <div class="bdo-summary-card summary-purple">
            <span class="summary-icon"><i class="fa-solid fa-barcode"></i></span>
            <small>PNR</small>
            <strong>{{ $pnr ?: 'Not added' }}</strong>
        </div>

        <div class="bdo-summary-card summary-blue">
            <span class="summary-icon"><i class="fa-solid fa-dollar-sign"></i></span>
            <small>Total Fare</small>
            <strong>{{ $money($booking->o_FullPrice) }}</strong>
        </div>

        <div class="bdo-summary-card summary-amber">
            <span class="summary-icon"><i class="fa-solid fa-chart-line"></i></span>
            <small>Markup</small>
            <strong>{{ $crmMeta->markup_amount !== null ? '$'.number_format((float)$crmMeta->markup_amount,2) : ($booking->is_markup ?: '—') }}</strong>
        </div>

        <div class="bdo-summary-card summary-green summary-status-{{ $paymentTone }}">
            <span class="summary-icon"><i class="fa-regular fa-credit-card"></i></span>
            <small>Payment</small>
            <strong>{{ $paymentStatus }}</strong>
        </div>

        <div class="bdo-summary-card summary-cyan summary-status-{{ $ticketTone }}">
            <span class="summary-icon"><i class="fa-solid fa-ticket"></i></span>
            <small>Ticket</small>
            <strong>{{ $ticketStatus }}</strong>
        </div>

        <div class="bdo-summary-card summary-indigo">
            <span class="summary-icon"><i class="fa-solid fa-globe"></i></span>
            <small>Source</small>
            <strong>{{ $crmMeta->via_source ?: ($booking->source ?: '—') }}</strong>
        </div>

        <div class="bdo-summary-card summary-rose">
            <span class="summary-icon"><i class="fa-regular fa-calendar"></i></span>
            <small>Travel Date</small>
            <strong>{{ $travelDate ? $travelDate->format('d M Y') : '—' }}</strong>
        </div>

        <div class="bdo-summary-card summary-slate">
            <span class="summary-icon"><i class="fa-solid fa-user-tie"></i></span>
            <small>Assigned Agent</small>
            <strong>{{ $assignedAgentName }}</strong>
        </div>
    </section>

    <section class="bdo-ops-strip">
        <div class="bdo-ops-strip-item">
            <span class="ops-strip-icon blue"><i class="fa-solid fa-plane-departure"></i></span>
            <div>
                <small>Departure</small>
                <strong>
                    @if($daysToDeparture === null)
                        Not available
                    @elseif($daysToDeparture < 0)
                        Departed {{ abs($daysToDeparture) }} day{{ abs($daysToDeparture)==1?'':'s' }} ago
                    @elseif($daysToDeparture === 0)
                        Travels today
                    @else
                        In {{ $daysToDeparture }} day{{ $daysToDeparture==1?'':'s' }}
                    @endif
                </strong>
            </div>
        </div>

        <div class="bdo-ops-strip-item">
            <span class="ops-strip-icon purple"><i class="fa-regular fa-bell"></i></span>
            <div>
                <small>Next Action</small>
                <strong>{{ $nextActionLabel }}</strong>
                @if($nextActionDate)<em>{{ $nextActionDate->format('d M, h:i A') }}</em>@endif
            </div>
        </div>

        <div class="bdo-ops-strip-item">
            <span class="ops-strip-icon green"><i class="fa-solid fa-user-check"></i></span>
            <div>
                <small>Owner</small>
                <strong>{{ $assignedAgentName }}</strong>
            </div>
        </div>

        <div class="bdo-ops-strip-item">
            <span class="ops-strip-icon amber"><i class="fa-regular fa-clock"></i></span>
            <div>
                <small>Booking Age</small>
                <strong>{{ optional($booking->created_at)->diffForHumans() }}</strong>
            </div>
        </div>
    </section>

    <div class="bdo-layout">
        <main>
            <section class="bdo-card">
                <header>
                    <div><span class="bdo-kicker">Booking details</span><h3>Customer & booking overview</h3></div>
                    <div class="fh-section-actions">
                        <button type="button" class="fh-edit-btn" data-fh-toggle="fh-contact-editor"><i class="fa-regular fa-pen-to-square"></i>Edit Contact & Billing</button>
                    </div>
                </header>
                <div class="bdo-body">
                    <div class="bdo-info-grid">
                        <div><small>Booking Ref</small><strong>#FH{{ $booking->id }}</strong></div>
                        <div data-crm-ua data-ua-module="booking" data-ua-key="{{ $booking->id }}" data-ua-field="pnr" data-ua-label="PNR" data-ua-value="{{ $pnr }}"><small>PNR</small><strong>{{ $pnr ?: '—' }}</strong></div>
                        <div data-crm-ua data-ua-module="booking" data-ua-key="{{ $booking->id }}" data-ua-field="ticket_number" data-ua-label="Ticket Number" data-ua-value="{{ $ticketNo }}"><small>Ticket Number</small><strong>{{ $ticketNo ?: '—' }}</strong></div>
                        <div data-crm-ua data-ua-module="booking" data-ua-key="{{ $booking->id }}" data-ua-field="o_Name" data-ua-label="Passenger Name" data-ua-value="{{ $booking->o_Name }}"><small>Passenger</small><strong>{{ $booking->o_Name ?: 'Guest' }}</strong></div>
                        <div data-crm-ua data-ua-module="booking" data-ua-key="{{ $booking->id }}" data-ua-field="o_Email" data-ua-label="Passenger Email" data-ua-value="{{ $booking->o_Email }}"><small>Email</small><strong>{{ $booking->o_Email ?: '—' }}</strong></div>
                        <div data-crm-ua data-ua-module="booking" data-ua-key="{{ $booking->id }}" data-ua-field="o_Phone" data-ua-label="Passenger Phone" data-ua-value="{{ $booking->o_Phone }}"><small>Phone</small><strong>{{ $booking->o_Phone ?: '—' }}</strong></div>
                        <div><small>Travel</small><strong>{{ $from }} → {{ $to }}</strong></div>
                        <div><small>Airline</small><strong>{{ $airline }}</strong></div>
                        <div><small>Created On</small><strong>{{ optional($booking->created_at)->format('d M Y, h:i A') }}</strong></div>
                    </div>
                </div>
                <div class="fh-inline-editor" id="fh-contact-editor">
                    <div class="fh-inline-editor-head">
                        <div><h4>Edit contact & billing</h4><p>Update passenger contact and billing details without leaving this section.</p></div>
                        <button type="button" class="fh-edit-btn secondary" data-fh-close="fh-contact-editor"><i class="fa-solid fa-xmark"></i>Close</button>
                    </div>
                    <form method="POST" action="{{ route('crm.booking_contact_billing',$booking->id) }}" class="svc-fields">@csrf
                                <label>Email<input class="form-control" type="email" name="email" value="{{ $booking->o_Email }}" required></label><label>Passenger phone<input class="form-control" name="phone" value="{{ $booking->o_Phone }}"></label>
                                <label class="svc-wide">Billing address<input class="form-control" name="billing_address" value="{{ $billing['billing_address'] ?? '' }}"></label><label>City<input class="form-control" name="billing_city" value="{{ $billing['billing_city'] ?? '' }}"></label><label>State<input class="form-control" name="billing_state" value="{{ $billing['billing_state'] ?? '' }}"></label><label>ZIP / Postal<input class="form-control" name="billing_pincode" value="{{ $billing['billing_pincode'] ?? '' }}"></label><label>Country<input class="form-control" name="billing_country" value="{{ $billing['billing_country'] ?? '' }}"></label><label>Billing country code<input class="form-control" name="billing_mobile_telcode" value="{{ $billing['billing_mobile_telcode'] ?? '' }}"></label><label>Billing phone<input class="form-control" name="billing_mobile" value="{{ $billing['billing_mobile'] ?? '' }}"></label><div class="svc-actions svc-wide"><button class="btn btn-primary">Save Contact & Billing</button></div>
                            </form>
                </div>
            </section>

            <section class="bdo-card" id="passengers">
                <header>
                    <div><span class="bdo-kicker">PAX details</span><h3>Passengers</h3></div>
                    <div class="fh-section-actions">
                        <span class="soft blue">{{ count($passengers) ?: 1 }} passenger{{ count($passengers)==1?'':'s' }}</span>
                        <button type="button" class="fh-edit-btn" data-fh-toggle="fh-passenger-editor"><i class="fa-regular fa-pen-to-square"></i>Edit Passengers</button>
                    </div>
                </header>
                <div class="bdo-body">
                    @forelse($passengers as $i=>$p)
                        @php
                            $title = trim((string)($p['Title'] ?? $p['Prefix'] ?? ''));
                            $firstName = trim((string)(
                                $p['FirstName'] ?? $p['First_Name'] ?? $p['GivenName'] ?? $p['Given_Name'] ?? $p['Name'] ?? ''
                            ));
                            $middleName = trim((string)(
                                $p['MiddleName'] ?? $p['Middle_Name'] ?? $p['Middle'] ?? ''
                            ));
                            $lastName = trim((string)(
                                $p['LastName'] ?? $p['Last_Name'] ?? $p['Surname'] ?? $p['FamilyName'] ?? $p['Family_Name'] ?? ''
                            ));

                            $fullName = trim(implode(' ', array_filter([$firstName,$middleName,$lastName], fn($v) => trim((string)$v) !== '')));
                            if ($fullName === '') {
                                $fullName = trim((string)($p['FullName'] ?? $p['PassengerName'] ?? ''));
                            }
                            if ($fullName === '') $fullName = 'Passenger '.($i+1);

                            $dob = $p['BirthDay'] ?? $p['DOB'] ?? $p['DateOfBirth'] ?? '—';
                            $gender = $p['GenderType'] ?? $p['Gender'] ?? '—';
                            $passport = $p['PassportNumber'] ?? $p['PassportNo'] ?? $p['DocumentNumber'] ?? '—';
                            $passportExpiry = $p['PassportExpiry'] ?? $p['PassportExpiryDate'] ?? $p['DocumentExpiry'] ?? '—';
                            $nationality = $p['Nationality'] ?? $p['NationalityCode'] ?? $p['Country'] ?? '—';
                            $paxType = $p['AgeType'] ?? $p['PassengerType'] ?? $p['PaxType'] ?? '—';
                        @endphp

                        <div class="bdo-passenger premium-pax">
                            <div class="bdo-passenger-avatar">
                                <span>{{ strtoupper(substr($firstName ?: 'P',0,1)) }}{{ strtoupper(substr($lastName ?: '',0,1)) }}</span>
                                <small>PAX {{ $i+1 }}</small>
                            </div>

                            <div class="premium-pax-content">
                                <div class="premium-pax-head">
                                    <div>
                                        <small>Full Passenger Name</small>
                                        <h4>{{ $title ? $title.' ' : '' }}{{ $fullName }}</h4>
                                    </div>
                                    <span class="premium-pax-type">{{ $paxType }}</span>
                                </div>

                                <div class="premium-name-grid">
                                    <div class="name-first">
                                        <small>First Name</small>
                                        <strong>{{ $firstName ?: '—' }}</strong>
                                    </div>
                                    <div class="name-middle">
                                        <small>Middle Name</small>
                                        <strong>{{ $middleName ?: '—' }}</strong>
                                    </div>
                                    <div class="name-last">
                                        <small>Last Name</small>
                                        <strong>{{ $lastName ?: '—' }}</strong>
                                    </div>
                                </div>

                                <div class="bdo-passenger-grid premium-pax-grid">
                                    <div><small>Title</small><b>{{ $title ?: '—' }}</b></div>
                                    <div><small>Date of Birth</small><b>{{ $dob }}</b></div>
                                    <div><small>Gender</small><b>{{ $gender }}</b></div>
                                    <div><small>Nationality</small><b>{{ $nationality }}</b></div>
                                    <div><small>Passport Number</small><b>{{ $passport }}</b></div>
                                    <div><small>Passport Expiry</small><b>{{ $passportExpiry }}</b></div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="empty">Passenger data was not stored in a readable structure for this booking.</div>
                    @endforelse
                </div>
                <div class="fh-inline-editor" id="fh-passenger-editor">
                    <div class="fh-inline-editor-head">
                        <div><h4>Manage passengers</h4><p>Edit existing passenger details or add another traveller.</p></div>
                        <button type="button" class="fh-edit-btn secondary" data-fh-close="fh-passenger-editor"><i class="fa-solid fa-xmark"></i>Close</button>
                    </div>
                    @foreach($passengers as $i=>$p)
                                <form method="POST" action="{{ route('crm.booking_passenger_update',[$booking->id,$i]) }}" class="svc-fields">@csrf
                                    <label>Type<select class="form-select" name="AgeType"><option>Adult</option><option>Child</option><option>Infant</option></select></label>
                                    <label>First name<input class="form-control" name="Name" value="{{ $p['Name'] ?? $p['FirstName'] ?? '' }}" required></label>
                                    <label>Middle name<input class="form-control" name="MiddleName" value="{{ $p['MiddleName'] ?? '' }}"></label>
                                    <label>Last name<input class="form-control" name="Surname" value="{{ $p['Surname'] ?? $p['LastName'] ?? '' }}" required></label>
                                    <label>DOB<input class="form-control" name="BirthDay" value="{{ $p['BirthDay'] ?? $p['DOB'] ?? '' }}"></label>
                                    <label>Gender<input class="form-control" name="GenderType" value="{{ $p['GenderType'] ?? $p['Gender'] ?? '' }}"></label>
                                    <label>Nationality<input class="form-control" name="Nationality" value="{{ $p['Nationality'] ?? '' }}"></label>
                                    <label>Passport<input class="form-control" name="PassportNumber" value="{{ $p['PassportNumber'] ?? '' }}"></label>
                                    <label>Passport expiry<input class="form-control" name="PassportExpiry" value="{{ $p['PassportExpiry'] ?? '' }}"></label>
                                    <div class="svc-actions svc-wide"><button class="btn btn-primary">Save Passenger {{ $i+1 }}</button></div>
                                </form>
                                <form method="POST" action="{{ route('crm.booking_passenger_delete',[$booking->id,$i]) }}" onsubmit="return confirm('Remove this passenger from the CRM booking record?')">@csrf @method('DELETE')<button class="btn btn-light svc-danger">Remove Passenger {{ $i+1 }}</button></form>
                                <hr>
                            @endforeach
                            <details><summary>+ Add passenger</summary><form method="POST" action="{{ route('crm.booking_passenger_add',$booking->id) }}" class="svc-fields">@csrf
                                <label>Type<select class="form-select" name="AgeType"><option>Adult</option><option>Child</option><option>Infant</option></select></label><label>First name<input class="form-control" name="Name" required></label><label>Middle name<input class="form-control" name="MiddleName"></label><label>Last name<input class="form-control" name="Surname" required></label><label>DOB<input class="form-control" name="BirthDay"></label><label>Gender<input class="form-control" name="GenderType"></label><label>Nationality<input class="form-control" name="Nationality"></label><label>Passport<input class="form-control" name="PassportNumber"></label><label>Passport expiry<input class="form-control" name="PassportExpiry"></label><div class="svc-actions svc-wide"><button class="btn btn-primary">Add Passenger</button></div>
                            </form></details>
                </div>
            </section>

            <section class="bdo-card" id="flight">
                <header>
                    <div><span class="bdo-kicker">Flight details</span><h3>Complete itinerary</h3></div>
                    <div class="fh-section-actions">
                        <span class="soft blue">{{ $allSegments->count() }} segments</span>
                        <button type="button" class="fh-edit-btn" data-fh-toggle="fh-itinerary-editor"><i class="fa-regular fa-pen-to-square"></i>Edit Itinerary</button>
                    </div>
                </header>
                <div class="bdo-body">
                    @foreach([['Outbound',$outbound],['Return',$returnSegments]] as [$journeyLabel,$sectorList])
                        @if(count($sectorList))
                            @php
                                $journeyPnr = $journeyLabel === 'Outbound'
                                    ? $supplierOutboundPnr
                                    : $supplierReturnPnr;
                            @endphp
                            <div class="bdo-journey-label">
                                {{ $journeyLabel }}{{ $journeyPnr ? ' · Supplier PNR: '.$journeyPnr : '' }}
                            </div>
                            @foreach($sectorList as $segment)
                                @php
                                    $segFrom = $segment['Departure_Iata'] ?? $segment['DepartureIata'] ?? '—';
                                    $segTo = $segment['Arrival_Iata'] ?? $segment['ArrivalIata'] ?? '—';
                                    $segFromCity = $segment['Departure_Name'] ?? $segment['Departure_City'] ?? $segment['DepartureCity'] ?? '';
                                    $segToCity = $segment['Arrival_Name'] ?? $segment['Arrival_City'] ?? $segment['ArrivalCity'] ?? '';
                                    $segAirline = $segment['AirlineName'] ?? $segment['OperatingAirlineName'] ?? $segment['Airline'] ?? '—';
                                    $flightNo = $segment['FlightNum'] ?? $segment['FlightNumber'] ?? '';
                                    $depRaw = $segment['Departure_Date'] ?? $segment['DepartureDate'] ?? null;
                                    $arrRaw = $segment['Arrival_Date'] ?? $segment['ArrivalDate'] ?? null;
                                @endphp
                                <div class="bdo-flight-card">
                                    <div class="bdo-flight-main">
                                        <div>
                                            <strong>{{ $segFrom }}</strong>
                                            <span>{{ $segFromCity ?: 'Departure' }}</span>
                                            <small>{{ $depRaw ? \Carbon\Carbon::parse($depRaw)->format('d M Y, h:i A') : '—' }}</small>
                                        </div>
                                        <div class="bdo-flight-mid">
                                            <span>{{ $segAirline }}{{ $flightNo ? ' · '.$flightNo : '' }}</span>
                                            <i class="fa-solid fa-plane"></i>
                                            <div></div>
                                        </div>
                                        <div class="text-end">
                                            <strong>{{ $segTo }}</strong>
                                            <span>{{ $segToCity ?: 'Arrival' }}</span>
                                            <small>{{ $arrRaw ? \Carbon\Carbon::parse($arrRaw)->format('d M Y, h:i A') : '—' }}</small>
                                        </div>
                                    </div>
                                    <div class="bdo-flight-footer bdo-flight-footer-v3">
                                        <span class="flight-chip blue"><i class="fa-solid fa-chair"></i> Cabin <b>{{ $crmCabin($segment['FlightClass'] ?? $segment['CabinClass'] ?? '') }}</b></span>
                                        <span class="flight-chip purple"><i class="fa-solid fa-tag"></i> Class <b>{{ $segment['BookingClass'] ?? $segment['Class'] ?? '—' }}</b></span>
                                        <span class="flight-chip green"><i class="fa-solid fa-suitcase-rolling"></i> Baggage <b>{{ $segment['Baggage'] ?? $segment['BaggageAllowance'] ?? '—' }}</b></span>
                                        <span class="flight-chip slate"><i class="fa-solid fa-plane-circle-check"></i> {{ $segAirline }}</span>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    @endforeach

                    @if(!$allSegments->count())
                        <div class="empty">No structured itinerary was found for this booking.</div>
                    @endif
                </div>
                <div class="fh-inline-editor" id="fh-itinerary-editor">
                    <div class="fh-inline-editor-head">
                        <div><h4>Edit itinerary</h4><p>Update, add or remove flight segments from the current CRM booking record.</p></div>
                        <button type="button" class="fh-edit-btn secondary" data-fh-close="fh-itinerary-editor"><i class="fa-solid fa-xmark"></i>Close</button>
                    </div>
                    @foreach(['outbound'=>$outbound,'return'=>$returnSegments] as $trip=>$tripSegments)
                                <b style="display:block;margin-top:12px">{{ ucfirst($trip) }}</b>
                                @foreach($tripSegments as $i=>$s)
                                <form method="POST" action="{{ route('crm.booking_segment_update',[$booking->id,$trip,$i]) }}" class="svc-fields">@csrf
                                    <label>From<input class="form-control" name="Departure_Iata" value="{{ $s['Departure_Iata'] ?? '' }}" required></label><label>Departure city<input class="form-control" name="Departure_City" value="{{ $s['Departure_City'] ?? '' }}"></label><label>Departure airport<input class="form-control" name="Departure_Name" value="{{ $s['Departure_Name'] ?? '' }}"></label><label>Departure date/time<input class="form-control" name="Departure_Date" value="{{ $s['Departure_Date'] ?? '' }}" required></label>
                                    <label>To<input class="form-control" name="Arrival_Iata" value="{{ $s['Arrival_Iata'] ?? '' }}" required></label><label>Arrival city<input class="form-control" name="Arrival_City" value="{{ $s['Arrival_City'] ?? '' }}"></label><label>Arrival airport<input class="form-control" name="Arrival_Name" value="{{ $s['Arrival_Name'] ?? '' }}"></label><label>Arrival date/time<input class="form-control" name="Arrival_Date" value="{{ $s['Arrival_Date'] ?? '' }}" required></label>
                                    <label>Airline code<input class="form-control" name="Airline" value="{{ $s['Airline'] ?? '' }}"></label><label>Airline<input class="form-control" name="AirlineName" value="{{ $s['AirlineName'] ?? '' }}" required></label><label>Flight no.<input class="form-control" name="FlightNum" value="{{ $s['FlightNum'] ?? '' }}"></label><label>Cabin<input class="form-control" name="FlightClass" value="{{ $s['FlightClass'] ?? '' }}"></label><label>Booking class<input class="form-control" name="BookingClass" value="{{ $s['BookingClass'] ?? '' }}"></label><label>Baggage<input class="form-control" name="Bag_Count" value="{{ $s['Bag_Count'] ?? '' }}"></label><label>PNR<input class="form-control" name="PNR" value="{{ $s['PNR'] ?? '' }}"></label><div class="svc-actions svc-wide"><button class="btn btn-primary">Save Segment</button></div>
                                </form><form method="POST" action="{{ route('crm.booking_segment_delete',[$booking->id,$trip,$i]) }}" onsubmit="return confirm('Remove this flight segment?')">@csrf @method('DELETE')<button class="btn btn-light svc-danger">Remove Segment</button></form><hr>
                                @endforeach
                                <details><summary>+ Add {{ $trip }} segment</summary><form method="POST" action="{{ route('crm.booking_segment_add',[$booking->id,$trip]) }}" class="svc-fields">@csrf
                                    <label>From<input class="form-control" name="Departure_Iata" required></label><label>Departure city<input class="form-control" name="Departure_City"></label><label>Departure airport<input class="form-control" name="Departure_Name"></label><label>Departure date/time<input class="form-control" name="Departure_Date" required></label><label>To<input class="form-control" name="Arrival_Iata" required></label><label>Arrival city<input class="form-control" name="Arrival_City"></label><label>Arrival airport<input class="form-control" name="Arrival_Name"></label><label>Arrival date/time<input class="form-control" name="Arrival_Date" required></label><label>Airline code<input class="form-control" name="Airline"></label><label>Airline<input class="form-control" name="AirlineName" required></label><label>Flight no.<input class="form-control" name="FlightNum"></label><label>Cabin<input class="form-control" name="FlightClass"></label><label>Booking class<input class="form-control" name="BookingClass"></label><label>Baggage<input class="form-control" name="Bag_Count"></label><label>PNR<input class="form-control" name="PNR"></label><div class="svc-actions svc-wide"><button class="btn btn-primary">Add Segment</button></div>
                                </form></details>
                            @endforeach
                </div>
            </section>

                        @include('crm.partials.stripe-booking-status', ['bookingId' => $booking->id])
                        <section class="bdo-card bdo-fare-card-v3" id="fare-payment">
                <header>
                    <div><span class="bdo-kicker">Fare & payment</span><h3>Fare, payment & ticketing snapshot</h3></div>
                    <div class="fh-section-actions">
                        <div class="fare-header-badges">
                            <span class="fare-state {{ $paymentTone }}">{{ $paymentStatus }}</span>
                            <span class="fare-state {{ $ticketTone }}">{{ $ticketStatus }}</span>
                        </div>
                        <button type="button" class="fh-edit-btn" data-fh-toggle="fh-fare-editor"><i class="fa-solid fa-dollar-sign"></i>Reprice Fare</button>
                        @if(!empty($payment['is_card_admin']))
                        <button type="button" class="fh-edit-btn secondary" data-fh-toggle="fh-card-editor"><i class="fa-regular fa-credit-card"></i>Payment Details</button>
                        @endif
                    </div>
                </header>
                <div class="bdo-body bdo-fare-layout">
                    <div>
                        @if(is_array($price) && count($price))
                            @foreach(['ADULT'=>'Adult','CHILD'=>'Child','INFANT'=>'Infant','Adult'=>'Adult','Child'=>'Child','Infant'=>'Infant'] as $key=>$label)
                                @if(isset($price[$key]) && is_array($price[$key]))
                                    @php $fareRow=$price[$key]; @endphp
                                    <div class="bdo-fare-row">
                                        <span>{{ $label }} × {{ $fareRow['Number'] ?? $fareRow['Qty'] ?? '—' }}</span>
                                        <strong>{{ $money($fareRow['SubTotal'] ?? $fareRow['Subtotal'] ?? null) }}</strong>
                                    </div>
                                @endif
                            @endforeach
                        @endif

                        <div class="bdo-fare-row total"><span>Total Fare</span><strong>{{ $money($booking->o_FullPrice) }}</strong></div>
                        <div class="bdo-fare-row"><span>Markup</span><strong>{{ $crmMeta->markup_amount !== null ? '$'.number_format((float)$crmMeta->markup_amount,2) : ($booking->is_markup ?: '—') }}</strong></div>

                        @php
                            $selectedAddons = [];
                            if (is_array($price)) {
                                foreach ($price as $addonKey => $addonRow) {
                                    if (!is_array($addonRow) || (($addonRow['Enable'] ?? 'No') !== 'Yes')) {
                                        continue;
                                    }

                                    $selectedAddons[] = [
                                        'name' => ucwords(str_replace('_', ' ', (string)$addonKey)),
                                        'amount' => $addonRow['SubTotal'] ?? $addonRow['Price'] ?? null,
                                    ];
                                }
                            }
                        @endphp

                        @if(count($selectedAddons))
                            <div style="margin:14px 0;padding:14px 16px;border:1px solid #e5e7eb;border-radius:12px;background:#f8fafc;">
                                <strong style="display:block;margin-bottom:8px;">Selected Add-ons</strong>
                                @foreach($selectedAddons as $addon)
                                    <div class="bdo-fare-row">
                                        <span>{{ $addon['name'] }}</span>
                                        <strong>{{ $money($addon['amount']) }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @php
                            $fareRuleOther = is_array($details['other'] ?? null) ? $details['other'] : [];
                            $fareRuleName = trim((string)($fareRuleOther['MarkupName'] ?? ''));
                            $fareRuleStoredDiscount = isset($fareRuleOther['MarkupDiscount']) && is_numeric($fareRuleOther['MarkupDiscount'])
                                ? (float)$fareRuleOther['MarkupDiscount']
                                : null;
                            $fareRuleOriginal = isset($fareRuleOther['OldTotalPrice']) && is_numeric($fareRuleOther['OldTotalPrice'])
                                ? (float)$fareRuleOther['OldTotalPrice']
                                : null;
                            $fareRuleFinal = (float)preg_replace('/[^0-9.\-]/', '', (string)$booking->o_FullPrice);
                            $fareRuleImpact = $fareRuleOriginal !== null ? round($fareRuleFinal - $fareRuleOriginal, 2) : null;
                        @endphp

                        <div style="margin:14px 0;padding:14px 16px;border:1px solid #e5e7eb;border-radius:12px;background:#f8fafc;">
                            <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;margin-bottom:10px;">
                                <strong>Fare Rule Impact</strong>
                                @if($fareRuleImpact !== null)
                                    <span style="font-weight:800;color:{{ $fareRuleImpact > 0 ? '#15803d' : ($fareRuleImpact < 0 ? '#b91c1c' : '#475569') }}">
                                        {{ $fareRuleImpact > 0 ? '+' : '' }}${{ number_format($fareRuleImpact,2) }}
                                    </span>
                                @endif
                            </div>

                            @if($fareRuleName !== '')
                                <div class="bdo-fare-row"><span>Applied Fare Rule</span><strong>{{ $fareRuleName }}</strong></div>
                                <div class="bdo-fare-row"><span>Original Fare</span><strong>{{ $fareRuleOriginal !== null ? '$'.number_format($fareRuleOriginal,2) : '—' }}</strong></div>
                                <div class="bdo-fare-row"><span>Final Booking Fare</span><strong>${{ number_format($fareRuleFinal,2) }}</strong></div>
                                <div class="bdo-fare-row"><span>Stored Rule Adjustment</span><strong>{{ $fareRuleStoredDiscount !== null ? '$'.number_format($fareRuleStoredDiscount,2) : '—' }}</strong></div>
                            @else
                                <div style="color:#64748b;font-size:13px;">No fare rule recorded for this booking.</div>
                            @endif
                        </div>

                        <div class="fare-ops-grid">
                            <div><small>Payment Status</small><strong>{{ $paymentStatus }}</strong></div>
                            <div><small>Ticket Status</small><strong>{{ $ticketStatus }}</strong></div>
                            <div><small>PNR</small><strong>{{ $pnr ?: 'Not added' }}</strong></div>
                            <div><small>Ticket No.</small><strong>{{ $ticketNo ?: 'Not added' }}</strong></div>
                        </div>
                    </div>

                    @if(app(\App\Services\CrmAccessService::class)->can((int)session('crm_admin_id'), 'payments.view'))
                    <div class="bdo-payment-card">
                        <div class="d-flex justify-content-between"><strong>{{ $payment['brand'] ?: 'Payment card' }}</strong><i class="fa-solid fa-shield-halved"></i></div>
                        {{-- FH card visibility (31 Oct 2026): full details for Super Admin only; others see last 4. --}}
                        <div class="bdo-card-digits">
    @if(!empty($payment['is_card_admin']) && !empty($payment['full_number']))
        {{ trim(chunk_split($payment['full_number'], 4, ' ')) }}
    @else
        **** **** **** {{ $payment['last4'] ?: '****' }}
    @endif
    @if(!empty($payment['is_card_admin']) && !empty($payment['expire_month']) && !empty($payment['expire_year']))
        <span><br/>  {{ str_pad($payment['expire_month'], 2, '0', STR_PAD_LEFT) }}/{{ $payment['expire_year'] }} </span>
    @endif
    @if(!empty($payment['is_card_admin']) && !empty($payment['cvv']))
        <span> &middot; CVV {{ $payment['cvv'] }}</span>
    @endif
</div>
                        @if(!empty($payment['is_card_admin']))
                        <span>{{ $payment['holder'] ?: 'Card holder' }}</span>
                        <small>Full card details: visible to Super Admin only.</small>
                        @else
                        <small>Only the last 4 digits are shown. Full card details are visible to Super Admin only.</small>
                        @endif
                    </div>
                    @endif
                </div>
                <div class="fh-inline-editor" id="fh-fare-editor">
                    <div class="fh-inline-editor-head">
                        <div><h4>Reprice current fare</h4><p>The previous fare remains in fare history for audit.</p></div>
                        <button type="button" class="fh-edit-btn secondary" data-fh-close="fh-fare-editor"><i class="fa-solid fa-xmark"></i>Close</button>
                    </div>
                    <div class="svc-note">Old fare is preserved in fare history. The booking's current total is updated so CRM communications can use the new total.</div>
                            <form method="POST" action="{{ route('crm.booking_reprice',$booking->id) }}" class="svc-fields">@csrf
                                <label>Current total<input class="form-control" value="{{ number_format((float)$booking->o_FullPrice,2,'.','') }}" disabled></label><label>New total<input class="form-control" type="number" step="0.01" min="0" name="new_total" required></label><label>Markup<input class="form-control" type="number" step="0.01" min="0" name="markup_amount" value="{{ $crmMeta->markup_amount }}"></label><label class="svc-wide">Reason<input class="form-control" name="reason" placeholder="Fare expired / airline price changed" required></label><div class="svc-actions svc-wide"><button class="btn btn-primary">Save New Fare</button></div>
                            </form>
                            @foreach($fareHistory as $h)<div class="svc-history"><b>${{ number_format((float)$h->old_total,2) }} → ${{ number_format((float)$h->new_total,2) }}</b> · {{ $h->reason }} · {{ $h->admin_name ?: 'Admin' }}</div>@endforeach
                </div>
                @if(!empty($payment['is_card_admin']) && app(\App\Services\CrmAccessService::class)->can((int)session('crm_admin_id'), 'payments.edit'))
                <div class="fh-inline-editor" id="fh-card-editor">
                    <div class="fh-inline-editor-head">
                        <div><h4>Edit safe payment metadata</h4><p>Only masked/non-sensitive payment metadata is managed here.</p></div>
                        <button type="button" class="fh-edit-btn secondary" data-fh-close="fh-card-editor"><i class="fa-solid fa-xmark"></i>Close</button>
                    </div>
                    <div class="svc-note"><b>Security:</b> this editor never accepts or stores a full card number or CVV. Use your PCI-compliant payment provider/hosted card form when a passenger needs to replace the actual card.</div>
                            <form method="POST" action="{{ route('crm.booking_card_metadata',$booking->id) }}" class="svc-fields">@csrf
                                <label>Cardholder<input class="form-control" name="cardholder_name" value="{{ $cardMeta->cardholder_name ?? $payment['holder'] ?? '' }}"></label><label>Brand<select class="form-select" name="brand"><option value="">Select</option>@foreach(['Visa','Mastercard','American Express','Discover / Other'] as $br)<option value="{{ $br }}" @selected(($cardMeta->brand ?? $payment['brand'] ?? '')===$br)>{{ $br }}</option>@endforeach</select></label><label>Last 4<input class="form-control" name="last4" maxlength="4" inputmode="numeric" value="{{ $cardMeta->last4 ?? $payment['last4'] ?? '' }}"></label><label>Expiry month<input class="form-control" type="number" min="1" max="12" name="expire_month" value="{{ $cardMeta->expire_month ?? $payment['expire_month'] ??  '' }}"></label><label>Expiry year<input class="form-control" type="number" min="2020" max="2200" name="expire_year" value="{{ $cardMeta->expire_year ?? $payment['expire_year'] ?? '' }}"></label><label class="svc-wide">Internal note<input class="form-control" name="note" value="{{ $cardMeta->note ?? '' }}"></label><div class="svc-actions svc-wide"><button class="btn btn-primary">Save Payment Metadata</button></div>
                            </form>
                </div>
                @endif
            </section>

            <section class="bdo-card bdo-activity-card-v3" id="activity">
                <header>
                    <div><span class="bdo-kicker">Activity</span><h3>Remarks & status history</h3></div>
                    <div class="fh-section-actions">
                        <button type="button" class="fh-edit-btn secondary" data-fh-toggle="fh-audit-editor"><i class="fa-solid fa-clock-rotate-left"></i>Servicing Audit</button>
                    </div>
                </header>
                <div class="bdo-body">
                    <form method="POST" action="{{ route('crm.booking_remark',$booking->id) }}" class="bdo-remark-form">
                        @csrf
                        <input class="form-control" name="remark" required placeholder="Add internal note, call update or agent remark">
                        <button class="btn btn-primary">Add remark</button>
                    </form>

                    <div class="bdo-timeline">
                        <div class="timeline-event created">
                            <span><i class="fa-solid fa-plus"></i></span>
                            <p>
                                <b>Booking created</b>
                                <small>{{ optional($booking->created_at)->format('d M Y, h:i A') }} · {{ $booking->source ?: 'Source not recorded' }}</small>
                            </p>
                        </div>

                        @foreach($remarks->take(10) as $r)
                            <div><span><i class="fa-regular fa-message"></i></span><p><b>{{ $r->remark }}</b><small>Admin #{{ $r->admin_id }} · {{ \Carbon\Carbon::parse($r->created_at)->format('d M Y, h:i A') }}</small></p></div>
                        @endforeach

                        @foreach($statusHistory->take(10) as $h)
                            <div><span><i class="fa-solid fa-arrows-rotate"></i></span><p><b>{{ $h->old_status ?: 'Status' }} → {{ $h->new_status }}</b><small>{{ $h->admin_name ?: ('Admin #'.$h->admin_id) }} · {{ \Carbon\Carbon::parse($h->created_at)->format('d M Y, h:i A') }}</small></p></div>
                        @endforeach

                    </div>
                </div>
            
                <div class="fh-inline-editor" id="fh-audit-editor">
                    <div class="fh-inline-editor-head">
                        <div><h4>Servicing audit history</h4><p>Read-only history of passenger, itinerary, billing, fare and card-metadata servicing changes.</p></div>
                        <button type="button" class="fh-edit-btn secondary" data-fh-close="fh-audit-editor"><i class="fa-solid fa-xmark"></i>Close</button>
                    </div>
                    <div class="fh-audit-list">
                        @forelse($serviceHistory as $h)<div class="svc-history"><b>{{ $h->area }} · {{ $h->action }}</b><br>{{ $h->admin_name ?: 'Admin' }} · {{ \Carbon\Carbon::parse($h->created_at)->format('d M Y, h:i A') }} @if($h->note)<br>{{ $h->note }}@endif</div>@empty<div class="svc-note">No servicing changes recorded yet.</div>@endforelse
                    </div>
                </div>
</section>
        </main>

        <aside>
            <section class="bdo-card" style="padding:18px;margin-bottom:18px"><span class="bdo-kicker">Verification</span><h3>Authorization & Verification</h3><p style="font-size:12px;color:#6b7b90">Send a secure amount/itinerary authorization for passenger signature and optional document upload.</p><a class="btn btn-primary" href="{{ route('crm.authorization_center',$booking->id) }}">Open Authorization Center</a></section>
            <section class="bdo-card" id="booking-email-center">
                <header>
                    <div>
                        <span class="bdo-kicker">Communication</span>
                        <h3>Email & documents</h3>
                    </div>
                </header>

                <div class="bdo-body">
                    <div style="padding:12px;border:1px solid #e4eaf1;border-radius:12px;background:#fbfcfe;margin-bottom:12px;">
                        <div style="font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:#7b899b;font-weight:700;">Passenger email</div>
                        <div style="font-size:12px;color:#16365c;font-weight:700;margin-top:4px;word-break:break-word;">
                            {{ $booking->o_Email ?: 'No passenger email available' }}
                        </div>
                    </div>

                    <form method="POST" action="{{ route('crm.booking_template_email_send',$booking->id) }}" style="margin-bottom:14px;">
                        @csrf

                        <label style="display:block;margin-bottom:10px;">
                            <span style="display:block;font-size:10px;text-transform:uppercase;letter-spacing:.06em;color:#718096;font-weight:700;margin-bottom:6px;">
                                Select email template
                            </span>

                            <select class="form-select" name="template_id" required {{ empty($booking->o_Email)?'disabled':'' }}>
                                <option value="">Choose passenger email...</option>

                                @foreach($emailTemplates as $template)
                                    @php
                                        $templateActive = !isset($template->is_active) || (bool)$template->is_active;
                                        $templateCategory = $template->category ?? 'General';
                                    @endphp

                                    <option value="{{ $template->id }}">
                                        {{ $templateCategory }} · {{ $template->label }}{{ $templateActive ? '' : ' (Inactive)' }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                            <button
                                class="btn btn-light"
                                type="submit"
                                formaction="{{ route('crm.booking_template_email_preview',$booking->id) }}"
                                formmethod="GET"
                                formtarget="_blank"
                                {{ empty($booking->o_Email)?'disabled':'' }}
                            >
                                <i class="fa-regular fa-eye me-1"></i> Preview
                            </button>

                            <button
                                class="btn btn-primary"
                                type="submit"
                                {{ empty($booking->o_Email)?'disabled':'' }}
                                onclick="return confirm('Send the selected email template to {{ addslashes($booking->o_Email) }}?')"
                            >
                                <i class="fa-regular fa-paper-plane me-1"></i> Send Email
                            </button>
                        </div>
                    </form>

                    @if($emailTemplates->where('is_active',0)->count())
                        <div style="font-size:10px;line-height:1.5;color:#8b6a1d;background:#fff8e8;border:1px solid #f3dfb2;border-radius:9px;padding:8px 10px;margin-bottom:12px;">
                            Inactive templates are shown but cannot be sent. Activate them from CRM → Email Templates first.
                        </div>
                    @endif

                    <div class="bdo-side-actions">
                        <a href="{{ route('crm.booking_email_preview',$booking->id) }}" target="_blank">
                            <i class="fa-regular fa-eye"></i><span>Preview confirmation</span>
                        </a>

                        <form method="POST" action="{{ route('crm.booking_email_resend',$booking->id) }}">
                            @csrf
                            <button {{ empty($booking->o_Email)?'disabled':'' }}>
                                <i class="fa-regular fa-paper-plane"></i><span>Send confirmation</span>
                            </button>
                        </form>
                    </div>

                    <div class="bdo-doc-count"><span>Stored documents</span><strong>{{ $docs->count() }}</strong></div>
                    <div class="bdo-doc-count"><span>Email logs</span><strong>{{ $emailLogs->count() }}</strong></div>
                </div>
            </section>


            <section class="bdo-card bdo-sticky" id="booking-operations">
                <header><div><span class="bdo-kicker">Agent workspace</span><h3>Booking operations</h3></div></header>
                <div class="bdo-body">
                    <div class="bdo-side-status-grid">
                        <div><small>Overall Status</small><strong>{{ $booking->status ?: 'Unspecified' }}</strong></div>
                        <div><small>Payment</small><strong>{{ $paymentStatus }}</strong></div>
                        <div><small>Ticket</small><strong>{{ $ticketStatus }}</strong></div>
                        <div><small>Agent</small><strong>{{ $assignedAgentName }}</strong></div>
                    </div>

                    <form method="POST" action="{{ route('crm.booking_operations',$booking->id) }}" class="bdo-ops-form">
                        @csrf
                        <div class="bdo-form-two">
                            <label><span>PNR</span><input class="form-control" name="pnr" value="{{ old('pnr',$crmMeta->pnr ?? '') }}" placeholder="ABC123"></label>
                            <label><span>Ticket Number</span><input class="form-control" name="ticket_number" value="{{ old('ticket_number',$crmMeta->ticket_number ?? $ticketNo) }}" placeholder="E-ticket no."></label>
                        </div>

                        <label><span>Payment Status</span>
                            <select class="form-select" name="payment_status">
                                @foreach(['Untracked','Pending','Paid','Partially Paid','Declined','Refund Pending','Refunded','Chargeback'] as $s)
                                    <option value="{{ $s }}" @selected(old('payment_status',$paymentStatus)===$s)>{{ $s }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label><span>Ticket Status</span>
                            <select class="form-select" name="ticket_status">
                                @foreach(['Not Ticketed','Ticketing','Issued','Ticketed','E-Ticket','Voided','Exchanged'] as $s)
                                    <option value="{{ $s }}" @selected(old('ticket_status',$ticketStatus)===$s)>{{ $s }}</option>
                                @endforeach
                            </select>
                        </label>

                        <div class="bdo-form-two">
                            <label><span>Markup Amount</span><input class="form-control" type="number" step="0.01" min="0" name="markup_amount" value="{{ old('markup_amount',$crmMeta->markup_amount) }}" placeholder="0.00"></label>
                            <label><span>Via / Source</span><input class="form-control" name="via_source" value="{{ old('via_source',$crmMeta->via_source ?: $booking->source) }}" placeholder="Website / FareLeader / Agent"></label>
                        </div>

                        <label><span>Next Action</span><input class="form-control" name="next_action" value="{{ old('next_action',$crmMeta->next_action) }}" placeholder="Call passenger / issue ticket / follow up"></label>
                        <label><span>Next Action Date & Time</span><input class="form-control" type="datetime-local" name="next_action_at" value="{{ old('next_action_at',$crmMeta->next_action_at ? \Carbon\Carbon::parse($crmMeta->next_action_at)->format('Y-m-d\TH:i') : '') }}"></label>
                        <label><span>Operations Note</span><textarea class="form-control" rows="3" name="operations_note" placeholder="Private operational note">{{ old('operations_note',$crmMeta->operations_note) }}</textarea></label>

                        <button class="btn btn-primary w-100"><i class="fa-solid fa-floppy-disk me-1"></i>Save Operations</button>
                    </form>

                    <hr>

                    <form method="POST" action="{{ route('crm.booking_status',$booking->id) }}" class="bdo-status-form">
                        @csrf
                        <label><span>Overall Booking Status</span>
                            <select class="form-select" name="status">
                                @foreach($statusGroups as $group=>$statuses)
                                    <optgroup label="{{ $group }}">
                                        @foreach($statuses as $s)
                                            <option value="{{ $s }}" @selected($booking->status===$s)>{{ $s }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </label>
                        <label><span>Status Note</span><input class="form-control" name="status_note" placeholder="Why is status changing?"></label>
                        <button class="btn btn-light w-100"><i class="fa-solid fa-arrows-rotate me-1"></i>Update Status</button>
                    </form>

                    <hr>

                    @if((int)($booking->assign_to ?? 0) > 0)
                        <div class="bdo-assignment-locked" style="border:1px solid #dce8f5;background:#f7fbff;border-radius:12px;padding:12px 13px;display:flex;align-items:center;justify-content:space-between;gap:12px;">
                            <div>
                                <span style="display:block;font-size:9px;text-transform:uppercase;letter-spacing:.06em;color:#7a8ba2;font-weight:800;margin-bottom:3px;">Assigned Agent</span>
                                <strong style="display:block;color:#0b2d58;font-size:13px;">{{ $assignedAgentName }}</strong>
                            </div>
                            <span class="soft green"><i class="fa-solid fa-circle-check me-1"></i>Assigned</span>
                        </div>
                    @else
                        <form method="POST" action="{{ route('crm.booking_assign',$booking->id) }}">
                            @csrf
                            <label><span>Assigned Agent</span>
                                <div class="d-flex gap-2">
                                    <select class="form-select" name="assign_to" required>
                                        <option value="" selected disabled>Select agent</option>
                                        @foreach($admins as $a)
                                            <option value="{{ $a->id }}">{{ $a->name }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-light">Assign</button>
                                </div>
                            </label>
                        </form>
                    @endif
                </div>
            </section>

            @if($related->count())
            <section class="bdo-card">
                <header><div><span class="bdo-kicker">Customer history</span><h3>Other bookings</h3></div></header>
                <div class="bdo-body bdo-related">
                    @foreach($related as $r)
                        <a href="{{ route('crm.booking_detail',$r->id) }}">
                            <strong>#FH{{ $r->id }}</strong>
                            <span>{{ optional($r->created_at)->format('d M Y') }} · {{ $r->status }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
            @endif
        </aside>
    </div>
</div>

<script id="fareshunt-booking-detail-production-v8-js">
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.bdo-page form .crm-ua-form-tools').forEach(function(el){ el.remove(); });

    function closeEditor(id){
        var panel=document.getElementById(id);
        if(panel) panel.classList.remove('is-open');
    }

    document.querySelectorAll('[data-fh-toggle]').forEach(function(btn){
        btn.addEventListener('click', function(){
            var id=btn.getAttribute('data-fh-toggle');
            var panel=document.getElementById(id);
            if(!panel) return;

            document.querySelectorAll('.fh-inline-editor.is-open').forEach(function(openPanel){
                if(openPanel!==panel) openPanel.classList.remove('is-open');
            });

            panel.classList.toggle('is-open');
            if(panel.classList.contains('is-open')){
                panel.scrollIntoView({behavior:'smooth',block:'nearest'});
                var first=panel.querySelector('input:not([type="hidden"]),select,textarea');
                if(first) setTimeout(function(){ first.focus({preventScroll:true}); },220);
            }
        });
    });

    document.querySelectorAll('[data-fh-close]').forEach(function(btn){
        btn.addEventListener('click', function(){ closeEditor(btn.getAttribute('data-fh-close')); });
    });
});
</script>

@endsection
