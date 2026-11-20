<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Authorization Evidence | Fares Hunt | #{{ fh_booking_ref($b->id) }}</title>
<style>
:root{--navy:#062a63;--navy2:#031c46;--blue:#1559d6;--pink:#e50055;--text:#10294c;--muted:#687a96;--line:#d9e3f1;--soft:#f5f8fd;--green:#15965a}
*{box-sizing:border-box}body{margin:0;background:#eef4fb;color:var(--text);font-family:Inter,Arial,sans-serif}.page{max-width:1080px;margin:auto;padding:20px}.top{display:flex;align-items:center;justify-content:space-between;background:#fff;padding:18px 24px;border-bottom:3px solid var(--pink);border-radius:14px 14px 0 0}.top img{width:300px;max-width:52vw}.actions{display:flex;gap:8px}.btn{border:0;border-radius:9px;padding:11px 15px;font-weight:800;cursor:pointer;text-decoration:none}.btn-primary{background:#1559d6;color:#fff}.btn-light{background:#f2f5f9;color:#16385f}.hero{padding:24px 28px;color:#fff;background:linear-gradient(110deg,#e50055,#8b236e 42%,#114caf);display:grid;grid-template-columns:1.3fr .7fr .7fr;gap:20px}.small{display:block;font-size:10px;text-transform:uppercase;letter-spacing:.8px;opacity:.72}.big{font-size:22px;font-weight:900;margin-top:5px}.card{background:#fff;padding:24px;border-radius:0 0 14px 14px}.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}.box{border:1px solid var(--line);border-radius:12px;padding:14px;background:#fbfdff}.box b{display:block;margin-top:5px}.section{margin-top:22px;border:1px solid #dfe7f2;border-radius:14px;overflow:hidden}.section h3{margin:0;padding:14px 17px;background:#f4f8fd;font-size:15px}.body{padding:17px}.trip{border-top:1px solid #edf2f7;padding:12px 0}.trip:first-child{border-top:0}.seg{display:grid;grid-template-columns:1fr 120px 1fr;gap:12px;align-items:center}.code{font-size:24px;font-weight:900}.meta{font-size:12px;color:#6b7c93;margin-top:4px}.mid{text-align:center;font-size:12px;color:#527094}.pax{display:flex;flex-wrap:wrap;gap:8px}.chip{border:1px solid #dce5f1;border-radius:999px;padding:7px 11px;background:#fff;font-size:12px;font-weight:700}.consents{display:grid;gap:9px}.consent{padding:11px 13px;border-radius:10px;background:#f2fbf6;color:#12633d;border:1px solid #ccebd9}.sig{max-width:520px;width:100%;min-height:130px;object-fit:contain;border:1px solid #dbe3ed;border-radius:9px;background:#fff}.docs a{color:#1559d6}
.device-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
.device-box{border:1px solid #dbe5f2;border-radius:12px;padding:14px;background:linear-gradient(180deg,#fff,#f7faff)}
.device-label{font-size:10px;text-transform:uppercase;letter-spacing:.8px;color:#7889a0;font-weight:800}
.device-value{font-size:15px;font-weight:900;color:#0b315f;margin-top:6px}
.technical{margin-top:12px;border-top:1px dashed #dbe3ee;padding-top:10px}
.technical summary{cursor:pointer;color:#1559d6;font-weight:800;font-size:12px}
.raw-ua{margin-top:8px;font-family:ui-monospace,SFMono-Regular,Consolas,monospace;font-size:10px;line-height:1.45;color:#64758c;word-break:break-word;background:#f7f9fc;border-radius:8px;padding:10px}.foot{margin-top:18px;padding:15px;text-align:center;color:#fff;background:var(--navy2);border-radius:12px;font-size:12px}
@media(max-width:780px){.hero,.grid,.seg,.device-grid{grid-template-columns:1fr}.mid{text-align:left}.top{align-items:flex-start;gap:10px}.actions{flex-direction:column}}
@media print{body{background:#fff}.page{max-width:none;padding:0}.actions{display:none}.top,.card{border-radius:0}.page{page-break-inside:auto}.section{break-inside:avoid}.foot{background:#fff;color:#333;border-top:1px solid #ddd}}
</style>
</head>
<body>
@php
 $snap=json_decode((string)$a->itinerary_snapshot,true) ?: [];
 $decodeMaybe=function($v){if(is_array($v))return $v;$d=json_decode((string)$v,true);return json_last_error()===JSON_ERROR_NONE&&is_array($d)?$d:[];};
 $walk=function($node) use (&$walk){$out=[];if(!is_array($node))return$out;$looks=isset($node['Departure_Iata'])||isset($node['DepartureIata'])||isset($node['Arrival_Iata'])||isset($node['ArrivalIata'])||isset($node['FlightNum']);if($looks)return[$node];foreach($node as $c)if(is_array($c))$out=array_merge($out,$walk($c));return$out;};
 $go=$walk($decodeMaybe($snap['sector_go']??[]));$back=$walk($decodeMaybe($snap['sector_back']??[]));$pax=$decodeMaybe($snap['passengers']??[]);
 if($pax && array_keys($pax)!==range(0,count($pax)-1))$pax=[$pax];
 $fmt=function($v){if(!$v)return'—';try{return \Carbon\Carbon::parse($v)->format('d M Y, h:i A');}catch(\Throwable$e){return(string)$v;}};
 $paxName=function($p){if(!is_array($p))return(string)$p;$n=trim(implode(' ',array_filter([$p['FirstName']??$p['first_name']??'',$p['MiddleName']??$p['middle_name']??'',$p['LastName']??$p['last_name']??''])));return$n?:($p['Name']??$p['name']??'Passenger');};
 $ua=(string)($a->user_agent??'');
 $device='Desktop';
 if(preg_match('/iPad|Tablet/i',$ua)) $device='Tablet';
 elseif(preg_match('/Mobile|Android|iPhone|iPod/i',$ua)) $device='Mobile';

 $os='Unknown';
 if(preg_match('/Windows NT 10\.0/i',$ua)) $os='Windows 10 / 11';
 elseif(preg_match('/Windows NT 6\.3/i',$ua)) $os='Windows 8.1';
 elseif(preg_match('/Windows NT 6\.1/i',$ua)) $os='Windows 7';
 elseif(preg_match('/Android\s+([0-9.]+)/i',$ua,$m)) $os='Android '.$m[1];
 elseif(preg_match('/iPhone OS ([0-9_]+)/i',$ua,$m)) $os='iOS '.str_replace('_','.',$m[1]);
 elseif(preg_match('/iPad; CPU OS ([0-9_]+)/i',$ua,$m)) $os='iPadOS '.str_replace('_','.',$m[1]);
 elseif(preg_match('/Mac OS X ([0-9_]+)/i',$ua,$m)) $os='macOS '.str_replace('_','.',$m[1]);
 elseif(preg_match('/Linux/i',$ua)) $os='Linux';

 $browser='Unknown';
 if(preg_match('/Edg\/([0-9]+)/i',$ua,$m)) $browser='Microsoft Edge '.$m[1];
 elseif(preg_match('/OPR\/([0-9]+)/i',$ua,$m)) $browser='Opera '.$m[1];
 elseif(preg_match('/Chrome\/([0-9]+)/i',$ua,$m)) $browser='Chrome '.$m[1];
 elseif(preg_match('/Firefox\/([0-9]+)/i',$ua,$m)) $browser='Firefox '.$m[1];
 elseif(preg_match('/Version\/([0-9.]+).*Safari/i',$ua,$m)) $browser='Safari '.$m[1];

@endphp
<div class="page">
 <header class="top">
  <img src="{{ asset('mail_images/fareshunt-logo.png') }}" alt="Fares Hunt">
  <div class="actions"><a class="btn btn-light" href="{{ route('crm.authorization_center',$b->id) }}">Back</a><button class="btn btn-primary" onclick="window.print()">Print / Save PDF</button></div>
 </header>
 <section class="hero">
  <div><span class="small">Authorization Evidence</span><div class="big">COMPLETED & SIGNED</div></div>
  <div><span class="small">Booking Reference</span><div class="big">#{{ fh_booking_ref($b->id) }}</div></div>
  <div><span class="small">Authorized Amount</span><div class="big">{{ $a->currency }} {{ number_format($a->authorized_amount,2) }}</div></div>
 </section>
 <main class="card">
  <div class="grid">
   <div class="box"><span class="small" style="color:#687a96;opacity:1">Signer</span><b>{{ $a->signer_name ?: '—' }}</b></div>
   <div class="box"><span class="small" style="color:#687a96;opacity:1">Card Verification</span><b>{{ $a->card_brand ?: 'Card' }} ending {{ $a->card_last4 ?: '—' }} · Exp {{ $a->card_expiry_month ?: '—' }}/{{ $a->card_expiry_year ?: '—' }}</b></div>
   <div class="box"><span class="small" style="color:#687a96;opacity:1">Completed</span><b>{{ $a->completed_at ? \Carbon\Carbon::parse($a->completed_at)->format('d M Y, h:i:s A') : '—' }}</b></div>
  </div>

  <section class="section"><h3>Flight & Passenger Details — signed snapshot</h3><div class="body">
   @if(count($go))
    <b style="color:#e50055">OUTBOUND</b>
    @foreach($go as $s)
    @php $from=$s['Departure_Iata']??$s['DepartureIata']??'—';$to=$s['Arrival_Iata']??$s['ArrivalIata']??'—';$air=$s['AirlineName']??$s['OperatingAirlineName']??$s['Airline']??'Airline';$fn=$s['FlightNum']??$s['FlightNumber']??'';$dep=$s['Departure_Date']??$s['DepartureDate']??null;$arr=$s['Arrival_Date']??$s['ArrivalDate']??null; @endphp
    <div class="trip"><div class="seg"><div><div class="code">{{ $from }}</div><div class="meta">{{ $fmt($dep) }}</div></div><div class="mid">{{ $air }}{{ $fn?' · '.$fn:'' }}<br>✈</div><div style="text-align:right"><div class="code">{{ $to }}</div><div class="meta">{{ $fmt($arr) }}</div></div></div></div>
    @endforeach
   @endif
   @if(count($back))
    <b style="color:#e50055;display:block;margin-top:15px">RETURN</b>
    @foreach($back as $s)
    @php $from=$s['Departure_Iata']??$s['DepartureIata']??'—';$to=$s['Arrival_Iata']??$s['ArrivalIata']??'—';$air=$s['AirlineName']??$s['OperatingAirlineName']??$s['Airline']??'Airline';$fn=$s['FlightNum']??$s['FlightNumber']??'';$dep=$s['Departure_Date']??$s['DepartureDate']??null;$arr=$s['Arrival_Date']??$s['ArrivalDate']??null; @endphp
    <div class="trip"><div class="seg"><div><div class="code">{{ $from }}</div><div class="meta">{{ $fmt($dep) }}</div></div><div class="mid">{{ $air }}{{ $fn?' · '.$fn:'' }}<br>✈</div><div style="text-align:right"><div class="code">{{ $to }}</div><div class="meta">{{ $fmt($arr) }}</div></div></div></div>
    @endforeach
   @endif
   <div class="pax" style="margin-top:15px">@forelse($pax as $p)<span class="chip">👤 {{ $paxName($p) }}</span>@empty<span class="chip">👤 {{ $b->o_Name }}</span>@endforelse</div>
  </div></section>

  <section class="section"><h3>Authorization & Consents</h3><div class="body consents">
   <div class="consent">✓ Exact amount {{ $a->currency }} {{ number_format($a->authorized_amount,2) }} approved.</div>
   <div class="consent">✓ Passenger names and itinerary snapshot reviewed and approved.</div>
   <div class="consent">✓ Booking, cancellation and refund terms acknowledged.</div>
  </div></section>

  <section class="section"><h3>Signature</h3><div class="body">@if($a->signature_data)<img class="sig" src="{{ $a->signature_data }}" alt="Customer signature">@else—@endif</div></section>

  <section class="section"><h3>Security & Submission Evidence</h3><div class="body">
   <div class="grid">
    <div class="box"><span class="small" style="color:#687a96;opacity:1">IP Address</span><b>{{ $a->signer_ip ?: '—' }}</b></div>
    <div class="box"><span class="small" style="color:#687a96;opacity:1">Viewed</span><b>{{ $a->viewed_at ?: '—' }}</b></div>
    <div class="box"><span class="small" style="color:#687a96;opacity:1">Completed</span><b>{{ $a->completed_at ?: '—' }}</b></div>
   </div>

   <div class="device-grid" style="margin-top:14px">
    <div class="device-box"><div class="device-label">Device</div><div class="device-value">{{ $device }}</div></div>
    <div class="device-box"><div class="device-label">Operating System</div><div class="device-value">{{ $os }}</div></div>
    <div class="device-box"><div class="device-label">Browser</div><div class="device-value">{{ $browser }}</div></div>
   </div>

   <details class="technical">
    <summary>Technical details</summary>
    <div class="raw-ua">{{ $a->user_agent ?: 'No user-agent recorded.' }}</div>
   </details>
  </div></section>

  <section class="section"><h3>Supporting Documents</h3><div class="body docs">
   @forelse($docs as $d)<p>📎 <a href="{{ route('crm.authorization_document',$d->id) }}">{{ $d->original_name }}</a> <small>({{ number_format($d->size_bytes/1024,0) }} KB)</small></p>@empty<p>None uploaded.</p>@endforelse
  </div></section>
 </main>
 <div class="foot">Fares Hunt · Secure booking authorization evidence · Booking #{{ fh_booking_ref($b->id) }}</div>
</div>
</body>
</html>
