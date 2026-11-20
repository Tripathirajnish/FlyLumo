<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Credit Card Authorization | Fares Hunt</title>
<style>
:root{--navy:#062a63;--navy2:#031c46;--blue:#1559d6;--pink:#e50055;--pink2:#ff1f68;--text:#10294c;--muted:#687a96;--line:#d9e3f1;--soft:#f5f8fd;--green:#15965a;--amber:#f2a900}
*{box-sizing:border-box}body{margin:0;font-family:Inter,ui-sans-serif,-apple-system,BlinkMacSystemFont,"Segoe UI",Arial,sans-serif;color:var(--text);background:linear-gradient(180deg,#f9fbff 0,#eef4fb 100%)}
.page{max-width:1120px;margin:0 auto;padding:0 18px 42px}.top{min-height:112px;display:flex;align-items:center;justify-content:space-between;gap:24px;background:#fff;padding:18px 24px;border-bottom:3px solid var(--pink)}
.brand img{width:min(330px,55vw);max-height:82px;object-fit:contain;object-position:left center}.trust{display:flex;gap:24px;flex-wrap:wrap;justify-content:flex-end}.trust-item{display:flex;gap:9px;align-items:center;font-size:12px;color:#4f6482;font-weight:700}.trust-icon{width:34px;height:34px;border-radius:50%;display:grid;place-items:center;background:#edf4ff;color:var(--navy);font-size:17px}
.hero{margin-top:22px;border-radius:18px 18px 0 0;padding:25px 28px;color:#fff;background:linear-gradient(110deg,#ed0755 0%,#8b236e 42%,#114caf 100%);display:grid;grid-template-columns:1.5fr .8fr .8fr;gap:22px;align-items:center;box-shadow:0 18px 45px rgba(8,40,91,.15)}.hero h1{font-size:26px;margin:0 0 5px;letter-spacing:.2px}.hero p{margin:0;opacity:.9}.hero-meta{border-left:1px solid rgba(255,255,255,.22);padding-left:22px}.meta-label{font-size:11px;text-transform:uppercase;letter-spacing:.8px;opacity:.75}.meta-value{font-size:18px;font-weight:800;margin-top:5px}.badge{display:inline-block;padding:4px 8px;border-radius:999px;background:#16a05d;font-size:10px;margin-left:7px;vertical-align:2px}
.card{background:#fff;border:1px solid #e0e8f3;border-radius:0 0 18px 18px;box-shadow:0 18px 45px rgba(8,40,91,.10);padding:24px}.summary{display:grid;grid-template-columns:1fr 1fr 1.25fr;border:1px solid var(--line);border-radius:14px;overflow:hidden;margin-bottom:22px}.summary-item{padding:17px 20px;display:flex;gap:12px;align-items:center}.summary-item+.summary-item{border-left:1px solid var(--line)}.bubble{width:42px;height:42px;border-radius:50%;display:grid;place-items:center;background:#e9f2ff;color:#0755b9;font-size:20px}.bubble.pink{background:#fff0f5;color:var(--pink)}.bubble.gold{background:#fff6dc;color:#c78800}.summary strong{display:block;font-size:18px}.amount{color:#df174c!important}.small{font-size:11px;color:var(--muted);font-weight:800;text-transform:uppercase;letter-spacing:.55px}.secure-note{font-size:13px;color:#506682}
.section{border:1px solid var(--line);border-radius:14px;margin-top:16px;overflow:hidden}.section-head{padding:15px 18px;border-bottom:1px solid var(--line);font-weight:850;display:flex;align-items:center;gap:10px}.step{width:25px;height:25px;border-radius:7px;display:grid;place-items:center;color:#fff;background:var(--pink);font-size:12px}.section-body{padding:18px}.grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}label.field{display:block;font-size:13px;font-weight:800;color:#183252}.field input,.field select{width:100%;height:48px;margin-top:8px;border:1px solid #cbd8ea;border-radius:10px;padding:0 14px;font-size:15px;color:#183252;background:#fff;outline:none;transition:.2s}.field input:focus,.field select:focus{border-color:#3378df;box-shadow:0 0 0 3px rgba(51,120,223,.12)}
.info{margin-top:14px;border-left:4px solid #4d91f1;background:#f1f7ff;padding:12px 14px;border-radius:8px;color:#24558e;font-size:13px}.checks{display:grid;gap:10px}.check{display:flex;align-items:flex-start;gap:12px;padding:13px 14px;border:1px solid #e0e7f0;border-radius:10px;background:#fff;font-weight:600;font-size:14px}.check input{width:18px;height:18px;margin-top:0;accent-color:var(--blue)}
.two{display:grid;grid-template-columns:1fr 1.12fr;gap:18px;margin-top:16px}.panel{border:1px solid var(--line);border-radius:14px;padding:18px;background:#fff}.panel-title{font-weight:850;margin-bottom:13px;display:flex;gap:9px;align-items:center}.upload{border:2px dashed #f18aaa;background:#fff8fb;border-radius:12px;padding:25px;text-align:center}.upload input{width:100%;margin-top:10px}.upload p{margin:8px 0 0;color:var(--muted);font-size:12px}
.sig-wrap{position:relative}.sig{display:block;width:100%;height:210px;border:1.5px solid #bfcde1;border-radius:12px;background:#fff;touch-action:none;cursor:crosshair}.sig-hint{position:absolute;inset:0;display:grid;place-items:center;pointer-events:none;color:#a8b5c7;font-size:14px}.sig-actions{display:flex;justify-content:space-between;gap:10px;margin-top:10px}.btn{appearance:none;border:0;border-radius:10px;padding:12px 18px;font-weight:800;cursor:pointer}.btn-clear{background:#fff0f5;color:#d81150;border:1px solid #f2aac1}.btn-type{background:#eef5ff;color:#1757b7;border:1px solid #b9d2fa}.type-box{display:none;margin-top:10px}.type-box input{width:100%;height:46px;border:1px solid #cbd8ea;border-radius:10px;padding:0 14px}
.assurance{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px}.assure{padding:16px;border-radius:12px;font-size:13px;line-height:1.65}.assure.green{background:#effbf5;border:1px solid #ccefdc}.assure.gold{background:#fff9e9;border:1px solid #f2dfad}.assure b{display:block;margin-bottom:4px}.submit-row{display:flex;justify-content:flex-end;margin-top:18px}.submit{min-width:360px;color:#fff;background:linear-gradient(90deg,var(--pink),#4b50d8);box-shadow:0 9px 22px rgba(214,15,82,.22);font-size:15px;padding:15px 22px}.submit:disabled{opacity:.55;cursor:not-allowed}
.footer{margin-top:20px;background:var(--navy2);color:#fff;border-radius:14px;padding:20px;display:flex;justify-content:space-around;gap:15px;flex-wrap:wrap;font-size:12px}.foot strong{display:block;margin-bottom:3px}.error{background:#fff0f0;color:#9b1c1c;border:1px solid #ffc9c9;padding:12px 15px;border-radius:10px;margin-bottom:15px}
.itinerary-box{margin:18px 0 22px;border:1px solid #dbe5f2;border-radius:15px;overflow:hidden;background:#fbfdff}
.itinerary-head{padding:15px 18px;background:linear-gradient(90deg,#071f4b,#0d4ea9);color:#fff;display:flex;justify-content:space-between;gap:15px;align-items:center}
.itinerary-head strong{font-size:15px}.trip-block{padding:16px 18px;border-top:1px solid #e7edf5}.trip-label{font-size:11px;font-weight:900;color:#e50055;text-transform:uppercase;letter-spacing:.9px;margin-bottom:9px}
.seg{display:grid;grid-template-columns:1fr 150px 1fr;gap:14px;align-items:center;padding:12px 0;border-top:1px solid #edf2f8}.seg:first-of-type{border-top:0}
.airport-code{font-size:25px;font-weight:900;color:#082d66}.airport-meta{font-size:12px;color:#6c7d95;margin-top:4px}.seg-mid{text-align:center;color:#46668f;font-size:12px}.seg-mid .plane{font-size:20px;color:#e50055}
.pax-strip{padding:14px 18px;background:#f5f8fd;border-top:1px solid #e1e8f1;display:flex;flex-wrap:wrap;gap:8px}.pax-chip{background:#fff;border:1px solid #dbe5f1;border-radius:999px;padding:7px 11px;font-size:12px;font-weight:700}
.expiry-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
@media(max-width:780px){.trust{display:none}.top{justify-content:center}.brand img{width:min(340px,80vw)}.hero{grid-template-columns:1fr}.hero-meta{border-left:0;border-top:1px solid rgba(255,255,255,.2);padding:14px 0 0}.summary,.grid,.two,.assurance{grid-template-columns:1fr}.summary-item+.summary-item{border-left:0;border-top:1px solid var(--line)}.submit{min-width:100%;width:100%}.card{padding:15px}.page{padding:0 8px 25px}}
</style>
</head>
<body>
<div class="page">
<header class="top">
 <div class="brand"><img src="{{ asset('mail_images/fareshunt-logo.png') }}" alt="Fares Hunt"></div>
 <div class="trust"><div class="trust-item"><span class="trust-icon">✓</span><span>Secure & Encrypted<br><small>Your data is protected</small></span></div><div class="trust-item"><span class="trust-icon">◉</span><span>Customer Support<br><small>We're here to help</small></span></div><div class="trust-item"><span class="trust-icon">★</span><span>Trusted Booking<br><small>Secure authorization</small></span></div></div>
</header>
<section class="hero"><div><h1>Credit Card Authorization</h1><p>Secure your booking with Fares Hunt</p></div><div class="hero-meta"><div class="meta-label">Booking Reference</div><div class="meta-value">#{{ $b->prefix }}{{ $b->id }} <span class="badge">SECURE</span></div></div><div class="hero-meta"><div class="meta-label">Passenger</div><div class="meta-value">{{ $b->o_Name ?: 'Guest' }}</div></div></section>
<div class="card">

 @php
   $snap = json_decode((string)($a->itinerary_snapshot ?? ''), true);
   $decodeMaybe = function($value) {
       if (is_array($value)) return $value;
       if (!is_string($value) || trim($value)==='') return [];
       $d = json_decode($value,true);
       return (json_last_error()===JSON_ERROR_NONE && is_array($d)) ? $d : [];
   };
   $walkSegments = function($node) use (&$walkSegments) {
       $found=[];
       if(!is_array($node)) return $found;
       $looks = isset($node['Departure_Iata']) || isset($node['DepartureIata']) ||
                isset($node['Arrival_Iata']) || isset($node['ArrivalIata']) ||
                isset($node['FlightNum']) || isset($node['FlightNumber']);
       if($looks) return [$node];
       foreach($node as $child) if(is_array($child)) $found=array_merge($found,$walkSegments($child));
       return $found;
   };
   $goRaw = $snap['sector_go'] ?? $b->o_SectorGo ?? [];
   $backRaw = $snap['sector_back'] ?? $b->o_SectorBack ?? [];
   $paxRaw = $snap['passengers'] ?? $b->o_Passenger ?? [];
   $goSegments = $walkSegments($decodeMaybe($goRaw));
   $backSegments = $walkSegments($decodeMaybe($backRaw));
   $paxList = $decodeMaybe($paxRaw);
   if($paxList && array_keys($paxList)!==range(0,count($paxList)-1)) $paxList=[$paxList];
   $fmtDate = function($v) {
      if(!$v) return '—';
      try { return \Carbon\Carbon::parse($v)->format('d M Y, h:i A'); } catch(\Throwable $e) { return (string)$v; }
   };
   $paxName = function($p) {
      if(!is_array($p)) return (string)$p;
      $parts = [
        $p['FirstName'] ?? $p['first_name'] ?? $p['First_Name'] ?? '',
        $p['MiddleName'] ?? $p['middle_name'] ?? $p['Middle_Name'] ?? '',
        $p['LastName'] ?? $p['last_name'] ?? $p['Last_Name'] ?? '',
      ];
      $name=trim(implode(' ',array_filter($parts)));
      return $name ?: ($p['Name'] ?? $p['name'] ?? 'Passenger');
   };
 @endphp

 @if($errors->any())<div class="error"><strong>Please check the form:</strong> {{ $errors->first() }}</div>@endif
 <div class="summary"><div class="summary-item"><span class="bubble">$</span><div><span class="small">Amount Authorized</span><strong class="amount">{{ $a->currency }} {{ number_format($a->authorized_amount,2) }}</strong></div></div><div class="summary-item"><span class="bubble pink">▣</span><div><span class="small">Expires On</span><strong>{{ \Carbon\Carbon::parse($a->expires_at)->format('d M Y, h:i A') }}</strong></div></div><div class="summary-item"><span class="bubble gold">🔒</span><div><strong style="font-size:14px">Secure authorization form</strong><span class="secure-note">IP address, timestamp and authorization evidence are recorded on submission.</span></div></div></div>
 <div class="itinerary-box">
   <div class="itinerary-head"><strong>Flight & Passenger Details</strong><span>Review before signing</span></div>
   @if(count($goSegments))
   <div class="trip-block"><div class="trip-label">Outbound</div>
     @foreach($goSegments as $s)
       @php
         $from=$s['Departure_Iata'] ?? $s['DepartureIata'] ?? '—';
         $to=$s['Arrival_Iata'] ?? $s['ArrivalIata'] ?? '—';
         $fromCity=$s['Departure_Name'] ?? $s['Departure_City'] ?? $s['DepartureCity'] ?? '';
         $toCity=$s['Arrival_Name'] ?? $s['Arrival_City'] ?? $s['ArrivalCity'] ?? '';
         $airline=$s['AirlineName'] ?? $s['OperatingAirlineName'] ?? $s['Airline'] ?? 'Airline';
         $flight=$s['FlightNum'] ?? $s['FlightNumber'] ?? '';
         $dep=$s['Departure_Date'] ?? $s['DepartureDate'] ?? null;
         $arr=$s['Arrival_Date'] ?? $s['ArrivalDate'] ?? null;
       @endphp
       <div class="seg">
         <div><div class="airport-code">{{ $from }}</div><div class="airport-meta">{{ $fromCity }}<br>{{ $fmtDate($dep) }}</div></div>
         <div class="seg-mid"><div>{{ $airline }}{{ $flight ? ' · '.$flight : '' }}</div><div class="plane">✈</div></div>
         <div style="text-align:right"><div class="airport-code">{{ $to }}</div><div class="airport-meta">{{ $toCity }}<br>{{ $fmtDate($arr) }}</div></div>
       </div>
     @endforeach
   </div>
   @endif

   @if(count($backSegments))
   <div class="trip-block"><div class="trip-label">Return</div>
     @foreach($backSegments as $s)
       @php
         $from=$s['Departure_Iata'] ?? $s['DepartureIata'] ?? '—';
         $to=$s['Arrival_Iata'] ?? $s['ArrivalIata'] ?? '—';
         $fromCity=$s['Departure_Name'] ?? $s['Departure_City'] ?? $s['DepartureCity'] ?? '';
         $toCity=$s['Arrival_Name'] ?? $s['Arrival_City'] ?? $s['ArrivalCity'] ?? '';
         $airline=$s['AirlineName'] ?? $s['OperatingAirlineName'] ?? $s['Airline'] ?? 'Airline';
         $flight=$s['FlightNum'] ?? $s['FlightNumber'] ?? '';
         $dep=$s['Departure_Date'] ?? $s['DepartureDate'] ?? null;
         $arr=$s['Arrival_Date'] ?? $s['ArrivalDate'] ?? null;
       @endphp
       <div class="seg">
         <div><div class="airport-code">{{ $from }}</div><div class="airport-meta">{{ $fromCity }}<br>{{ $fmtDate($dep) }}</div></div>
         <div class="seg-mid"><div>{{ $airline }}{{ $flight ? ' · '.$flight : '' }}</div><div class="plane">✈</div></div>
         <div style="text-align:right"><div class="airport-code">{{ $to }}</div><div class="airport-meta">{{ $toCity }}<br>{{ $fmtDate($arr) }}</div></div>
       </div>
     @endforeach
   </div>
   @endif

   <div class="pax-strip">
     @forelse($paxList as $p)<span class="pax-chip">👤 {{ $paxName($p) }}</span>
     @empty<span class="pax-chip">👤 {{ $b->o_Name ?: 'Passenger' }}</span>@endforelse
   </div>
 </div>
 @if($a->status==='completed')
  <div class="info">This authorization has already been completed.</div>
 @elseif($a->revoked_at || ($a->expires_at && now()->gt($a->expires_at)))
  <div class="error">This authorization link is no longer active.</div>
 @else
 <form id="authForm" method="POST" enctype="multipart/form-data" action="{{ route('authorization.submit',$token) }}">@csrf
  <section class="section"><div class="section-head"><span class="step">1</span> Signer & Cardholder Information</div><div class="section-body grid"><label class="field">Signer full name <span style="font-weight:500;color:#6c7f98">(as per authorization)</span><input name="signer_name" value="{{ old('signer_name') }}" placeholder="Enter signer full name" required></label><label class="field">Cardholder name <span style="font-weight:500;color:#6c7f98">(as it appears on card)</span><input name="cardholder_name" value="{{ old('cardholder_name') }}" placeholder="Enter cardholder name" required></label></div></section>
  <section class="section"><div class="section-head"><span class="step">2</span> Card Information <span style="font-size:12px;font-weight:600;color:#6c7f98">(verification only)</span></div><div class="section-body"><div class="grid"><label class="field">Card brand<select name="card_brand" required><option value="">Select card brand</option><option>Visa</option><option>Mastercard</option><option>American Express</option><option>Discover</option><option>Other</option></select></label><label class="field">Card last 4 digits<input name="card_last4" inputmode="numeric" autocomplete="off" maxlength="4" pattern="[0-9]{4}" placeholder="1234" required></label></div><div class="grid" style="margin-top:14px"><label class="field">Card expiry month<select name="card_expiry_month" required><option value="">MM</option>@for($m=1;$m<=12;$m++)<option value="{{ str_pad($m,2,'0',STR_PAD_LEFT) }}">{{ str_pad($m,2,'0',STR_PAD_LEFT) }}</option>@endfor</select></label><label class="field">Card expiry year<select name="card_expiry_year" required><option value="">YYYY</option>@for($y=(int)date('Y');$y<=(int)date('Y')+15;$y++)<option value="{{ $y }}">{{ $y }}</option>@endfor</select></label></div><div class="info">🔒 For security, do not enter or upload your full card number or CVV. Only card brand, last 4 and expiry are recorded for this authorization.</div></div></section>
  <section class="section"><div class="section-head"><span class="step">3</span> Authorization & Acknowledgment</div><div class="section-body checks"><label class="check"><input type="checkbox" name="consent_amount" required><span>I authorize the exact amount of <strong>{{ $a->currency }} {{ number_format($a->authorized_amount,2) }}</strong> shown above.</span></label><label class="check"><input type="checkbox" name="consent_itinerary" required><span>I reviewed and approve the passenger and itinerary details for this booking.</span></label><label class="check"><input type="checkbox" name="consent_terms" required><span>I authorize this transaction and acknowledge the applicable booking, cancellation and refund terms.</span></label></div></section>
  <div class="two"><section class="panel"><div class="panel-title"><span class="step">4</span> Supporting Documents <span style="font-size:11px;color:#7889a1">OPTIONAL</span></div><div class="upload"><div style="font-size:34px;color:#315ec4">☁</div><strong>Choose files</strong> or attach supporting documents<input type="file" name="documents[]" multiple accept=".pdf,.jpg,.jpeg,.png"><p>PDF, JPG or PNG · Max 10MB per file.<br>Do not upload an image containing CVV or a full card number.</p></div></section>
  <section class="panel"><div class="panel-title"><span class="step">5</span> Signature <span style="font-size:11px;color:#7889a1">DRAW OR TYPE</span></div><div class="sig-wrap"><canvas id="sig" class="sig"></canvas><div id="sigHint" class="sig-hint">Draw your signature here</div></div><input type="hidden" name="signature" id="signature"><div class="type-box" id="typeBox"><input id="typedSignature" placeholder="Type your full signature"></div><div class="sig-actions"><button class="btn btn-clear" type="button" id="clearSig">Clear signature</button><button class="btn btn-type" type="button" id="typeSig">Type signature instead</button></div></section></div>
  <div class="assurance"><div class="assure green"><b>✓ Your authorization includes</b>Booking confirmation and itinerary<br>✓ Exact payment amount approval<br>✓ Acceptance of applicable terms<br>✓ IP address and timestamp verification</div><div class="assure gold"><b>🔒 Your information is protected</b>This authorization creates an evidence record for this booking. Full card numbers and CVV must not be entered or uploaded here.</div></div>
  <div class="submit-row"><button class="btn submit" id="submitBtn" type="submit">🔒 SIGN & SUBMIT AUTHORIZATION</button></div>
 </form>
 <script>
 (()=>{const c=document.getElementById('sig'),ctx=c.getContext('2d'),hint=document.getElementById('sigHint'),hidden=document.getElementById('signature'),form=document.getElementById('authForm'),typed=document.getElementById('typedSignature'),typeBox=document.getElementById('typeBox');let drawing=false,drawn=false,typedMode=false,last=null;
 function resize(){const r=c.getBoundingClientRect(),ratio=Math.max(window.devicePixelRatio||1,1),old=drawn?c.toDataURL():null;c.width=Math.round(r.width*ratio);c.height=Math.round(r.height*ratio);ctx.setTransform(ratio,0,0,ratio,0,0);ctx.lineWidth=2.4;ctx.lineCap='round';ctx.lineJoin='round';ctx.strokeStyle='#0a2e65';if(old){const im=new Image();im.onload=()=>ctx.drawImage(im,0,0,r.width,r.height);im.src=old}}
 function point(e){const r=c.getBoundingClientRect();return{x:e.clientX-r.left,y:e.clientY-r.top}}
 c.addEventListener('pointerdown',e=>{if(typedMode)return;drawing=true;drawn=true;hint.style.display='none';last=point(e);c.setPointerCapture(e.pointerId);e.preventDefault()});
 c.addEventListener('pointermove',e=>{if(!drawing||typedMode)return;const p=point(e);ctx.beginPath();ctx.moveTo(last.x,last.y);ctx.lineTo(p.x,p.y);ctx.stroke();last=p;e.preventDefault()});
 ['pointerup','pointercancel','pointerleave'].forEach(ev=>c.addEventListener(ev,e=>{drawing=false;last=null}));
 document.getElementById('clearSig').addEventListener('click',()=>{ctx.clearRect(0,0,c.width,c.height);drawn=false;typed.value='';hint.style.display='grid'});
 document.getElementById('typeSig').addEventListener('click',()=>{typedMode=!typedMode;typeBox.style.display=typedMode?'block':'none';c.style.opacity=typedMode?'.45':'1';hint.textContent=typedMode?'Typed signature mode':'Draw your signature here';hint.style.display=drawn&&!typedMode?'none':'grid';document.getElementById('typeSig').textContent=typedMode?'Draw signature instead':'Type signature instead'});
 form.addEventListener('submit',e=>{if(typedMode){const v=typed.value.trim();if(v.length<2){e.preventDefault();alert('Please type your signature before submitting.');typed.focus();return}const temp=document.createElement('canvas');temp.width=1200;temp.height=300;const t=temp.getContext('2d');t.fillStyle='#fff';t.fillRect(0,0,temp.width,temp.height);t.fillStyle='#0a2e65';t.font='italic 76px Georgia,serif';t.fillText(v,55,175);hidden.value=temp.toDataURL('image/png');}else{if(!drawn){e.preventDefault();alert('Please draw your signature before submitting.');c.scrollIntoView({behavior:'smooth',block:'center'});return}hidden.value=c.toDataURL('image/png');}});
 resize();window.addEventListener('resize',()=>{clearTimeout(window.__sigResize);window.__sigResize=setTimeout(resize,150)});
 })();
 </script>
 @endif
</div>
<footer class="footer"><div class="foot"><strong>🔒 Secure Authorization</strong>Protected submission</div><div class="foot"><strong>🛡 Data Protection</strong>Card number & CVV not stored here</div><div class="foot"><strong>★ Fares Hunt</strong>Hunt the best fares</div><div class="foot"><strong>◉ Customer Support</strong>Booking assistance</div></footer>
</div>
</body></html>
