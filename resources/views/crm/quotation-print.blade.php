<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $quote->quote_number }} - FaresHunt Quotation</title>
<style>
:root{
  --navy:#0b2d57;
  --navy2:#123f76;
  --blue:#2563eb;
  --sky:#eaf3ff;
  --cyan:#0ea5b7;
  --cyan-bg:#e8fbfd;
  --green:#138a55;
  --green-bg:#eaf9f1;
  --amber:#b56a00;
  --amber-bg:#fff4d8;
  --purple:#6d3bd1;
  --purple-bg:#f3ecff;
  --rose:#c83257;
  --rose-bg:#fff0f4;
  --ink:#102a43;
  --muted:#6b7f95;
  --line:#dce7f2;
  --paper:#ffffff;
  --soft:#f6f9fc;
}
*{box-sizing:border-box;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important}
html,body{margin:0;padding:0;background:#eaf0f7;color:var(--ink);font-family:Inter,Segoe UI,Arial,Helvetica,sans-serif}
body{font-size:14px}
.actions{max-width:900px;margin:20px auto 10px;padding:0 12px;display:flex;justify-content:flex-end;gap:10px}
.actions button{border:0;border-radius:11px;padding:12px 18px;font-weight:800;cursor:pointer;font-size:13px}
.actions .primary{background:var(--blue);color:#fff;box-shadow:0 8px 20px rgba(37,99,235,.2)}
.actions .light{background:#fff;color:var(--navy);border:1px solid var(--line)}
.sheet{width:210mm;min-height:297mm;margin:0 auto 26px;background:var(--paper);box-shadow:0 18px 55px rgba(15,42,72,.14);overflow:hidden;position:relative}
.topbar{height:9px;background:linear-gradient(90deg,var(--blue) 0 38%,var(--cyan) 38% 58%,var(--purple) 58% 77%,#f4b63e 77% 100%)}
.head{padding:26px 28px 24px;background:linear-gradient(135deg,var(--navy) 0%,var(--navy2) 58%,var(--blue) 100%);color:#fff;display:flex;justify-content:space-between;align-items:flex-start;gap:28px;position:relative;overflow:hidden}
.head:after{content:"";position:absolute;right:-60px;top:-90px;width:260px;height:260px;border-radius:50%;background:rgba(255,255,255,.07)}
.brand,.meta{position:relative;z-index:2}.brand{max-width:62%}
.brandline{display:flex;align-items:center;gap:10px;margin-bottom:12px}.brandmark{width:38px;height:38px;border-radius:12px;background:#fff;color:var(--blue);display:grid;place-items:center;font-weight:900;font-size:20px;box-shadow:0 8px 20px rgba(0,0,0,.12)}
.brand small{display:block;font-size:10px;letter-spacing:2.2px;font-weight:900;opacity:.88}.brand h1{font-size:31px;line-height:1.06;margin:2px 0 7px}.brand p{margin:0;font-size:14px;color:#d9e8ff}
.meta{min-width:205px;padding:14px 15px;border-radius:14px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);backdrop-filter:blur(2px)}
.meta-row{display:flex;justify-content:space-between;gap:14px;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.13)}.meta-row:last-of-type{border-bottom:0}
.meta small{font-size:8px;text-transform:uppercase;letter-spacing:1px;opacity:.72}.meta b{font-size:12px;text-align:right}.status{display:inline-flex;margin-top:9px;padding:6px 10px;border-radius:999px;font-size:9px;font-weight:900;letter-spacing:.4px;background:{{ $quote->status==='Sent' ? '#dff7ea' : '#fff1cb' }};color:{{ $quote->status==='Sent' ? '#0e7a49' : '#9b5a00' }}}
.body{padding:22px 28px 24px}
.section-title{display:flex;align-items:center;gap:9px;margin:2px 0 10px;color:var(--navy);font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.9px}.section-title:before{content:"";width:5px;height:18px;border-radius:4px;background:var(--blue)}
.customer{display:grid;grid-template-columns:1.15fr 1.4fr 1fr;gap:10px;margin-bottom:18px}.box{border:1px solid var(--line);border-radius:12px;padding:12px 13px;background:linear-gradient(180deg,#fff,#f8fbff);min-width:0}.box small,.trip-cell small,.price small,.notes small,.company small{display:block;font-size:8px;text-transform:uppercase;letter-spacing:.85px;color:var(--muted);font-weight:900}.box b{display:block;margin-top:5px;font-size:12px;overflow-wrap:anywhere;color:var(--ink)}
.trip-card{border:1px solid #cfe0f5;border-radius:15px;background:linear-gradient(135deg,#f7fbff,#eef6ff);overflow:hidden;margin-bottom:16px}.trip-head{padding:11px 14px;background:#e3efff;color:var(--navy);font-weight:900;font-size:11px;text-transform:uppercase;letter-spacing:.7px}.trip-body{padding:14px}.itinerary-text{white-space:pre-line;line-height:1.55;font-size:15px;font-weight:800;color:var(--navy);margin:0 0 12px}.trip-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px}.trip-cell{padding:9px 10px;border-radius:10px;background:#fff;border:1px solid #dce9f6}.trip-cell b{display:block;margin-top:4px;font-size:10px;color:var(--ink);overflow-wrap:anywhere}
.price{display:grid;grid-template-columns:1.55fr .8fr;gap:12px;margin:18px 0}.price-main{padding:18px 20px;border-radius:15px;background:linear-gradient(135deg,var(--navy),var(--blue));color:#fff;position:relative;overflow:hidden}.price-main:after{content:"";position:absolute;right:-42px;bottom:-55px;width:170px;height:170px;border-radius:50%;background:rgba(255,255,255,.08)}.price-main small{color:#cfe0ff}.price-main strong{position:relative;z-index:2;display:block;font-size:31px;line-height:1;margin:7px 0 5px;color:#fff;letter-spacing:-.4px}.price-main span{font-size:9px;color:#dbe8ff;font-weight:700}
.valid{padding:18px;border-radius:15px;background:var(--amber-bg);border:1px solid #f6d997;display:flex;flex-direction:column;justify-content:center}.valid small{color:var(--amber);font-weight:900;text-transform:uppercase;letter-spacing:.75px;font-size:8px}.valid b{display:block;margin-top:6px;color:#7d4b00;font-size:14px}.valid span{margin-top:4px;font-size:9px;color:#9a6d26}
.notes{padding:15px 16px;border:1px solid #e4d9fa;border-radius:13px;background:var(--purple-bg);margin-top:15px}.notes small{color:var(--purple)}.notes p{white-space:pre-line;line-height:1.58;color:#4d3b72;margin:8px 0 0;font-size:11px}
.terms{margin-top:16px;padding:12px 14px;border-radius:11px;background:var(--soft);border:1px solid var(--line);font-size:9.5px;color:#60768e;line-height:1.55}.terms b{color:var(--navy)}
.foot{display:grid;grid-template-columns:1fr 1.65fr .9fr;gap:16px;align-items:start;padding:16px 28px 19px;background:#f5f8fc;border-top:1px solid var(--line)}.company b{display:block;color:var(--navy);font-size:12px;margin-bottom:3px}.company div{font-size:9px;line-height:1.55;color:#60748b;overflow-wrap:anywhere}.thank{font-size:9px;line-height:1.55;color:#60748b}.thank b{display:block;color:var(--green);font-size:10px;margin-bottom:3px}.seal{text-align:right}.seal span{display:inline-flex;align-items:center;justify-content:center;width:54px;height:54px;border-radius:50%;background:var(--green-bg);color:var(--green);font-size:22px;font-weight:900;border:1px solid #bfe8d2}.seal small{display:block;margin-top:5px;color:var(--muted);font-size:8px;font-weight:800;text-transform:uppercase;letter-spacing:.55px}
.no-break{break-inside:avoid;page-break-inside:avoid}
@media(max-width:820px){.sheet{width:calc(100% - 20px);min-height:auto}.head{flex-direction:column}.brand{max-width:100%}.meta{width:100%}.customer{grid-template-columns:1fr}.trip-grid{grid-template-columns:1fr 1fr}.price{grid-template-columns:1fr}.foot{grid-template-columns:1fr}.seal{text-align:left}.body,.head{padding-left:20px;padding-right:20px}}
@page{size:A4;margin:0}
@media print{
  html,body{width:210mm;height:297mm;background:#fff!important}
  body{margin:0!important;padding:0!important}
  .actions{display:none!important}
  .sheet{
    width:210mm!important;
    min-height:0!important;
    height:auto!important;
    margin:0!important;
    box-shadow:none!important;
    border-radius:0!important;
    overflow:visible!important;
  }

  /* Keep the premium desktop grid in print. */
  .head{flex-direction:row!important;padding:18px 22px 16px!important;gap:18px!important}
  .brand{max-width:62%!important}
  .brandline{margin-bottom:8px!important;gap:8px!important}
  .brandmark{width:32px!important;height:32px!important;border-radius:10px!important;font-size:17px!important}
  .brand h1{font-size:27px!important;margin:1px 0 5px!important}
  .brand p{font-size:12px!important}
  .meta{width:auto!important;min-width:198px!important;padding:10px 12px!important;border-radius:12px!important}
  .meta-row{padding:4px 0!important}
  .status{margin-top:6px!important;padding:5px 9px!important}

  .topbar{height:6px!important}
  .body{padding:14px 22px 14px!important}
  .section-title{margin:0 0 7px!important;font-size:10px!important;gap:7px!important}
  .section-title:before{height:15px!important;width:4px!important}

  .customer{
    grid-template-columns:1.15fr 1.4fr 1fr!important;
    gap:8px!important;
    margin-bottom:11px!important;
  }
  .box{padding:8px 10px!important;border-radius:10px!important}
  .box b{margin-top:3px!important;font-size:10.5px!important}

  .trip-card{margin-bottom:10px!important;border-radius:12px!important}
  .trip-head{padding:8px 11px!important;font-size:10px!important}
  .trip-body{padding:9px 11px!important}
  .itinerary-text{font-size:12px!important;line-height:1.35!important;margin:0 0 8px!important}
  .trip-grid{grid-template-columns:repeat(4,1fr)!important;gap:6px!important}
  .trip-cell{padding:7px 8px!important;border-radius:8px!important}
  .trip-cell b{margin-top:2px!important;font-size:9px!important}

  .price{
    grid-template-columns:1.55fr .8fr!important;
    gap:9px!important;
    margin:10px 0!important;
  }
  .price-main{padding:12px 15px!important;border-radius:12px!important}
  .price-main strong{font-size:26px!important;margin:5px 0 3px!important}
  .valid{padding:12px 14px!important;border-radius:12px!important}
  .valid b{margin-top:4px!important;font-size:12px!important}
  .valid span{margin-top:2px!important;font-size:8px!important}

  .notes{padding:10px 12px!important;border-radius:10px!important;margin-top:9px!important}
  .notes p{margin:5px 0 0!important;line-height:1.4!important;font-size:9.5px!important}
  .terms{margin-top:9px!important;padding:8px 10px!important;border-radius:9px!important;font-size:8.2px!important;line-height:1.35!important}

  .foot{
    grid-template-columns:1fr 1.65fr .9fr!important;
    gap:12px!important;
    padding:10px 22px 11px!important;
    align-items:center!important;
  }
  .company b{font-size:10.5px!important}
  .company div,.thank{font-size:8px!important;line-height:1.35!important}
  .thank b{font-size:9px!important;margin-bottom:2px!important}
  .seal{text-align:right!important}
  .seal span{width:42px!important;height:42px!important;font-size:18px!important}
  .seal small{margin-top:3px!important;font-size:7px!important}

  .head,.topbar,.price-main,.valid,.notes,.trip-card,.trip-head,.box,.foot,.status,.terms{
    -webkit-print-color-adjust:exact!important;
    print-color-adjust:exact!important;
  }
}
</style>
</head>
<body>
<div class="actions"><button class="light" onclick="window.close()">Close</button><button class="primary" onclick="window.print()">Print / Save as PDF</button></div>
<article class="sheet">
  <div class="topbar"></div>
  <header class="head">
    <div class="brand">
      <div class="brandline"><div class="brandmark">✈</div><div><small>FARESHUNT</small></div></div>
      <h1>Flight Quotation</h1>
      <p>Prepared especially for {{ $lead->name ?: 'Customer' }}</p>
    </div>
    <div class="meta">
      <div class="meta-row"><small>Quotation</small><b>{{ $quote->quote_number }}</b></div>
      <div class="meta-row"><small>Prepared</small><b>{{ \Carbon\Carbon::parse($quote->created_at)->format('d M Y') }}</b></div>
      <div class="meta-row"><small>Valid until</small><b>{{ $quote->valid_until ? \Carbon\Carbon::parse($quote->valid_until)->format('d M Y') : 'Subject to availability' }}</b></div>
      <span class="status">{{ strtoupper($quote->status) }}</span>
    </div>
  </header>

  <div class="body">
    <div class="section-title">Customer details</div>
    <section class="customer no-break">
      <div class="box"><small>Customer</small><b>{{ $lead->name ?: 'Customer' }}</b></div>
      <div class="box"><small>Email</small><b>{{ $lead->emailid ?: 'â€”' }}</b></div>
      <div class="box"><small>Phone</small><b>{{ $lead->contact ?: 'â€”' }}</b></div>
    </section>

    <div class="section-title">Trip & itinerary</div>
    <section class="trip-card no-break">
      <div class="trip-head">Proposed travel plan</div>
      <div class="trip-body">
        <p class="itinerary-text">{{ $quote->itinerary_summary ?: (($leadMeta->travel_from ?? 'From').' â†’ '.($leadMeta->travel_to ?? ($lead->going_to ?: 'To'))) }}</p>
        <div class="trip-grid">
          <div class="trip-cell"><small>From</small><b>{{ $leadMeta->travel_from ?: 'Not specified' }}</b></div>
          <div class="trip-cell"><small>To</small><b>{{ $leadMeta->travel_to ?: ($lead->going_to ?: 'Not specified') }}</b></div>
          <div class="trip-cell"><small>Departure</small><b>{{ $lead->checkin_date ?: 'Not specified' }}</b></div>
          <div class="trip-cell"><small>Return</small><b>{{ $leadMeta->return_date ?: 'Not specified' }}</b></div>
        </div>
      </div>
    </section>

    <div class="section-title">Quotation summary</div>
    <section class="price no-break">
      <div class="price-main">
        <small>Final quotation amount</small>
        <strong>{{ $quote->currency }} {{ number_format((float)$quote->total_amount,2) }}</strong>
        <span>Customer-facing total â€¢ taxes/fare conditions subject to the notes below</span>
      </div>
      <div class="valid"><small>Fare validity</small><b>{{ $quote->valid_until ? \Carbon\Carbon::parse($quote->valid_until)->format('d M Y') : 'Subject to availability' }}</b><span>Please confirm before expiry.</span></div>
    </section>

    @if($quote->notes)
      <section class="notes no-break"><small>Fare notes & conditions</small><p>{{ $quote->notes }}</p></section>
    @endif

    <div class="terms no-break"><b>Important:</b> Fares and availability remain subject to confirmation until ticketed. Airline schedules, baggage, change/cancellation terms and fare rules may vary by itinerary. Ticket issuance is a separate booking operation. Please contact our team to proceed or request a revised quotation.</div>
  </div>

  <footer class="foot no-break">
    <div class="company"><b>FaresHunt</b><div>@if($agency['content_emailid'] ?? null){{ $agency['content_emailid'] }}<br>@endif @if($agency['header_contact'] ?? null){{ $agency['header_contact'] }}@endif</div></div>
    <div class="thank"><b>Thank you for choosing FaresHunt.</b><div>@if($agency['footer_address'] ?? null){{ $agency['footer_address'] }}@else Our travel team is ready to assist you with booking and ticketing. @endif</div></div>
    <div class="seal"><span>âœ“</span><small>Customer Quote</small></div>
  </footer>
</article>
</body>
</html>


<style>
@media print {
  /* Final A4 visual balance: keep one page, but avoid an over-compressed top half. */
  .head{
    padding:24px 22px 22px!important;
    min-height:112px!important;
  }
  .brandline{margin-bottom:10px!important}
  .brandmark{width:36px!important;height:36px!important;font-size:19px!important}
  .brand h1{font-size:30px!important;margin:3px 0 6px!important}
  .brand p{font-size:12.5px!important}
  .meta{min-width:205px!important;padding:12px 14px!important}
  .meta-row{padding:5px 0!important}

  .body{padding:18px 22px 18px!important}
  .section-title{margin:0 0 9px!important;font-size:10.5px!important}
  .customer{gap:9px!important;margin-bottom:15px!important}
  .box{padding:10px 11px!important}
  .box b{font-size:11px!important}

  .trip-card{margin-bottom:14px!important}
  .trip-head{padding:9px 12px!important}
  .trip-body{padding:11px 12px!important}
  .itinerary-text{font-size:12.5px!important;margin-bottom:10px!important}
  .trip-grid{gap:7px!important}
  .trip-cell{padding:9px 9px!important}

  .price{gap:10px!important;margin:13px 0!important}
  .price-main{padding:16px 16px!important;min-height:76px!important}
  .price-main strong{font-size:29px!important;margin:6px 0 4px!important}
  .valid{padding:15px 15px!important;min-height:76px!important}
  .valid b{font-size:13px!important}

  .notes{padding:12px 13px!important;margin-top:12px!important}
  .notes p{font-size:10px!important;line-height:1.45!important}
  .terms{margin-top:11px!important;padding:10px 11px!important;font-size:8.6px!important;line-height:1.4!important}

  .foot{
    padding:15px 22px 16px!important;
    min-height:62px!important;
    gap:14px!important;
  }
  .company b{font-size:11px!important}
  .company div,.thank{font-size:8.4px!important}
  .thank b{font-size:9.5px!important}
  .seal span{width:46px!important;height:46px!important;font-size:20px!important}

  /* Keep print output on a single A4 page for normal quotation content. */
  .sheet{page-break-after:avoid!important}
}
</style>


<style>
@media print {
  /* Final footer anchor fix: preserve the current design and pin footer to A4 bottom. */
  .sheet{
    height:297mm!important;
    min-height:297mm!important;
    display:flex!important;
    flex-direction:column!important;
    overflow:hidden!important;
  }

  .body{
    flex:0 0 auto!important;
  }

  .foot{
    margin-top:auto!important;
    flex-shrink:0!important;
    width:100%!important;
  }
}
</style>

