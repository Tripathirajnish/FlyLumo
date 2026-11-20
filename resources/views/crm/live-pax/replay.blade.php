@extends('crm.layout')
@section('content')
<style>
.rp{--navy:#10284b;--line:#e1e8f1}.rp-head{display:flex;justify-content:space-between;align-items:center;gap:14px;margin-bottom:14px}.rp-head h1{margin:0;color:var(--navy);font-size:24px}.rp-head p{margin:4px 0 0;color:#75849a;font-size:11px}.rp-actions{display:flex;gap:7px}.rp-btn{border:0;border-radius:9px;padding:8px 11px;background:#112d52;color:#fff;font-size:10px;font-weight:800;cursor:pointer;text-decoration:none}.rp-btn.alt{background:#eef3f8;color:#264869}.rp-shell{background:#fff;border:1px solid var(--line);border-radius:16px;overflow:hidden;box-shadow:0 8px 28px rgba(18,40,75,.06)}.rp-bar{display:grid;grid-template-columns:auto auto 1fr auto;gap:10px;align-items:center;padding:11px 13px;border-bottom:1px solid #e8edf4;background:#fafbfd}.rp-time{font-family:ui-monospace,monospace;font-size:10px;color:#52647d}.rp-range{width:100%}.rp-stage{position:relative;background:#dfe5ec;min-height:650px;display:flex;justify-content:center;align-items:flex-start;overflow:auto;padding:24px}.rp-frame-wrap{position:relative;width:min(1280px,100%);background:white;box-shadow:0 20px 50px rgba(0,0,0,.18);transform-origin:top center}.rp-frame{display:block;width:100%;height:720px;border:0;background:#fff;pointer-events:none;user-select:none}.rp-cursor{position:absolute;z-index:20;width:16px;height:16px;border-radius:50%;border:2px solid #fff;background:#d91f43;box-shadow:0 1px 8px rgba(0,0,0,.35);transform:translate(-50%,-50%);pointer-events:none;display:none}.rp-pulse{position:absolute;z-index:19;width:28px;height:28px;border-radius:50%;border:2px solid #d91f43;transform:translate(-50%,-50%) scale(.4);opacity:0;pointer-events:none}.rp-pulse.go{animation:pulse .5s ease-out}@keyframes pulse{to{transform:translate(-50%,-50%) scale(1.6);opacity:0}0%{opacity:.8}}.rp-meta{display:flex;gap:7px;flex-wrap:wrap;margin-bottom:14px}.rp-chip{padding:6px 9px;border-radius:99px;background:#eef3f8;color:#48617d;font-size:9px}.rp-live{background:#e9f8ef;color:#168f49}.rp-note{padding:9px 12px;background:#fff6df;color:#8a650d;border:1px solid #f4dfaa;border-radius:10px;font-size:9px;margin-bottom:12px}
.rp-field-active{outline:2px solid rgba(211,29,66,.45)!important;outline-offset:1px!important;box-shadow:0 0 0 3px rgba(211,29,66,.08)!important}
</style>

<div class="rp">
 <div class="rp-head">
   <div><h1>Session Replay</h1><p>Anonymous visitor · privacy-safe recording</p></div>
   <div class="rp-actions">
     <a class="rp-btn alt" href="{{ route('crm.live_pax') }}">← Live Pax Monitor</a>
     <button class="rp-btn" id="rpLive">Jump to Live</button>
   </div>
 </div>

 <div class="rp-meta">
   <span class="rp-chip">PX-{{ substr($session->visitor_key,-12) }}</span>
   <span class="rp-chip">{{ $session->country ?: 'Unknown' }}</span>
   <span class="rp-chip">{{ $session->device_type }} · {{ $session->browser }}</span>
   <span class="rp-chip">{{ $session->funnel_stage }}</span>
   <span class="rp-chip" id="rpPage">{{ $session->current_path ?: '/' }}</span>
   <span class="rp-chip" id="rpState">Loading…</span>
 </div>

 <div class="rp-note">Sensitive booking/payment pages are intentionally masked. Form values, card data, CVV, passwords, passenger details and authentication tokens are never recorded.</div>

 <div class="rp-shell">
   <div class="rp-bar">
     <button class="rp-btn" id="rpPlay">▶ Play</button>
     <select id="rpSpeed" class="rp-btn alt">
       <option value="0.5">0.5×</option>
       <option value="1" selected>1×</option>
       <option value="2">2×</option>
       <option value="4">4×</option>
     </select>
     <input type="range" min="0" max="1000" value="0" class="rp-range" id="rpRange">
     <span class="rp-time" id="rpTime">00:00 / 00:00</span>
   </div>

   <div class="rp-stage">
     <div class="rp-frame-wrap" id="rpWrap">
       <iframe class="rp-frame" id="rpFrame" sandbox="allow-same-origin" tabindex="-1" aria-hidden="true"></iframe>
       <i class="rp-cursor" id="rpCursor"></i>
       <i class="rp-pulse" id="rpPulse"></i>
     </div>
   </div>
 </div>
</div>

<script>
/* Live Pax Replay v11: client-timestamped realtime masked action reconstruction. */
/* Live Pax Replay v4: reassembles chunked snapshot parts before rendering. */
(function(){
const URL='{{ route('crm.live_pax_chunks',['id'=>$session->id]) }}';
let events=[],lastId=0,playing=false,startWall=0,startPos=0,pos=0,speed=1,raf=0,applied=-1,lastSnap=-1,snapshotParts={},timelineOrigin=null,frameReady=false,pendingFrameEvents=[],liveFollow=true,lastPollTotal=0,frameGeneration=0,lastRenderedSnapshotAbs=0;
const $=s=>document.querySelector(s),frame=$('#rpFrame'),cursor=$('#rpCursor'),pulse=$('#rpPulse'),range=$('#rpRange');

function fmt(ms){
  let s=Math.max(0,Math.floor(ms/1000)),m=Math.floor(s/60);
  return String(m).padStart(2,'0')+':'+String(s%60).padStart(2,'0');
}
function total(){return events.length?Math.max(0,Number(events[events.length-1]._rt||0)):0}

function chunkBaseMs(c){
  const created=Date.parse(c.created_at||'');
  const ended=Number(c.ended_ms||0);
  if(Number.isFinite(created)){
    return created-ended;
  }
  return Date.now()-ended;
}

function normalizeTimeline(){
  if(!events.length){
    timelineOrigin=null;
    return;
  }

  let min=Infinity;
  events.forEach(e=>{
    const abs=Number(e._abs||0);
    if(Number.isFinite(abs) && abs<min)min=abs;
  });

  if(!Number.isFinite(min))min=0;
  timelineOrigin=min;

  events.forEach(e=>{
    const abs=Number(e._abs||min);
    e._rt=Math.max(0,abs-min);
  });

  events.sort((a,b)=>(a._rt||0)-(b._rt||0));
}

function latestSnapshotIndexAt(ms){
  let snap=-1;
  for(let i=0;i<events.length;i++){
    if((events[i]._rt||0)>ms)break;
    if(events[i].type==='snapshot')snap=i;
  }
  return snap;
}

function rebuildAt(ms){
  if(!events.length)return;
  pos=Math.max(0,Math.min(total(),ms));
  applied=-1;
  cursor.style.display='none';
  const snap=latestSnapshotIndexAt(pos);
  if(snap<0){sync();return;}
  apply(events[snap]);
  applied=snap;
  /* Events after the snapshot are queued while the frame is loading; none are lost. */
  for(let i=snap+1;i<events.length;i++){
    if((events[i]._rt||0)>pos)break;
    if(events[i].type!=='snapshot')apply(events[i]);
    applied=i;
  }
  sync();
}

function ingestEvent(e,c){
  if(!e)return;

  const clientTs=Number(e.client_ts||0);
  const base=chunkBaseMs(c||{});
  const localT=Number(e.t!=null?e.t:(c&&c.started_ms)||0);
  const abs=(Number.isFinite(clientTs)&&clientTs>1000000000000)
    ? clientTs
    : base+(Number.isFinite(localT)?localT:0);

  if(e.type==='snapshot_part'){
    const id=String(e.snapshot_id||'');
    const total=Math.max(1,Number(e.part_total)||1);
    const index=Math.max(0,Number(e.part_index)||0);

    if(!id)return;

    if(!snapshotParts[id]){
      snapshotParts[id]={
        total:total,
        parts:new Array(total),
        meta:e,
        received:0,
        firstAbs:abs
      };
    }

    const bag=snapshotParts[id];

    if(typeof bag.parts[index]!=='string'){
      bag.parts[index]=String(e.html_part||'');
      bag.received++;
      bag.firstAbs=Math.min(Number(bag.firstAbs||abs),abs);
    }

    if(bag.received>=bag.total && bag.parts.every(p=>typeof p==='string')){
      events.push({
        type:'snapshot',
        reason:bag.meta.reason||'page',
        html:bag.parts.join(''),
        scrollX:bag.meta.scrollX||0,
        scrollY:bag.meta.scrollY||0,
        vw:bag.meta.vw||0,
        vh:bag.meta.vh||0,
        _abs:bag.firstAbs
      });

      delete snapshotParts[id];
    }

    return;
  }

  const copy=Object.assign({},e,{_abs:abs});
  events.push(copy);
}

async function poll(){
  try{
    let r=await fetch(URL+'?after='+lastId,{credentials:'same-origin'}),p=await r.json();

    (p.chunks||[]).forEach(c=>{
      (c.events||[]).forEach(e=>ingestEvent(e,c));
    });

    lastId=p.last_id||lastId;
    const previousTotal=lastPollTotal;
    normalizeTimeline();
    const currentTotal=total();

    /* True live edge: every new batch is reconstructed immediately against the
       latest complete snapshot. This makes each masked keystroke visible as soon
       as the chunk arrives, rather than waiting for the playback clock to catch up. */
    if(liveFollow && currentTotal>previousTotal && events.some(e=>e.type==='snapshot')){
      rebuildAt(currentTotal);
      if(!playing){
        playing=true;
        startWall=performance.now();
        startPos=currentTotal;
        $('#rpPlay').textContent='❚❚ Pause';
        raf=requestAnimationFrame(tick);
      }else{
        startWall=performance.now();
        startPos=currentTotal;
      }
    }
    lastPollTotal=currentTotal;

    const pending=Object.keys(snapshotParts).length;
    $('#rpState').textContent=events.length
      ? (liveFollow ? (pending ? 'LIVE · loading visual state' : 'LIVE · realtime') : (pending ? 'Recording available · loading snapshot' : 'Recording available'))
      : (pending ? 'Loading snapshot…' : 'Waiting for events');

    if(pos>total())pos=total();
    sync();

    /*
     * On first data load, render the earliest available snapshot immediately
     * instead of leaving the frame blank until the user touches the slider.
     */
    if(applied===-1 && events.length){
      rebuildAt(0);
    }
  }catch(e){}
}
function sync(){
  range.max=Math.max(1,total());
  range.value=Math.min(pos,total());
  $('#rpTime').textContent=fmt(pos)+' / '+fmt(total());
}

/*
 * Replay rescue CSS:
 * Recorded pages often use animation/loader rules that keep content at
 * opacity:0 or beneath a full-screen overlay until JavaScript runs.
 * Scripts are intentionally removed from recordings for safety, so replay
 * must force the recorded DOM visible without executing page JavaScript.
 */
function prepareSnapshotHtml(html){
  if(!html)return html;

  const rescue=`
<style id="fh-live-pax-replay-rescue">
html,body{
  visibility:visible!important;
  opacity:1!important;
  display:block!important;
  min-height:100%!important;
}
body{
  overflow:auto!important;
  transform:none!important;
  filter:none!important;
}
#tnbSearchOverlay,
.tnb-search-overlay,
.fhx-loader,
.page-loader,
.preloader,
.loader-overlay,
.loading-overlay,
.modal-backdrop{
  display:none!important;
  visibility:hidden!important;
  opacity:0!important;
  pointer-events:none!important;
}
[data-aos],
.wow,
.animate__animated,
.animated,
.fadeIn,
.fade-up,
.reveal,
.reveal-up{
  visibility:visible!important;
  opacity:1!important;
  transform:none!important;
  animation:none!important;
  transition:none!important;
}
main,
[role="main"],
main *,
[role="main"] *{
  visibility:visible!important;
}
</style>`;

  if(/<\/head>/i.test(html)){
    return html.replace(/<\/head>/i,rescue+'</head>');
  }
  return rescue+html;
}

function eventNeedsFrame(e){
  return !!e && (e.type==='form_input'||e.type==='form_interaction'||e.type==='form_keystroke'||e.type==='scroll'||e.type==='resize');
}

function drainPendingFrameEvents(){
  if(!frameReady||!pendingFrameEvents.length)return;
  const pending=pendingFrameEvents.splice(0,pendingFrameEvents.length);
  pending.forEach(function(ev){apply(ev,true)});
}

function applySnapshot(e){
  const html=prepareSnapshotHtml(e.html);
  frameReady=false;
  const thisGeneration=++frameGeneration;
  const snapshotAbs=Number(e._abs||0);
  lastRenderedSnapshotAbs=snapshotAbs;
  /* Never discard form events merely because a multipart snapshot finishes later.
     Keep only events at/after this snapshot and replay them after iframe load. */
  pendingFrameEvents=pendingFrameEvents.filter(ev=>Number(ev._abs||0)>=snapshotAbs);

  frame.onload=function(){
    if(thisGeneration!==frameGeneration)return;
    try{
      const doc=frame.contentDocument;
      if(doc && doc.documentElement){
        doc.documentElement.style.visibility='visible';
        doc.documentElement.style.opacity='1';
      }
      if(doc && doc.body){
        doc.body.style.visibility='visible';
        doc.body.style.opacity='1';
        doc.body.style.display='block';
      }

      frame.contentWindow.scrollTo(e.scrollX||0,e.scrollY||0);
      frameReady=true;
      drainPendingFrameEvents();

      /*
       * If the document ended up with almost no visible height, expand the
       * iframe using the real recorded document height instead of showing a
       * blank white viewport.
       */
      if(doc && doc.body){
        const h=Math.max(
          e.vh||0,
          doc.body.scrollHeight||0,
          doc.documentElement ? doc.documentElement.scrollHeight||0 : 0,
          720
        );
        frame.style.height=Math.min(Math.max(h,420),5000)+'px';
      }
    }catch(_){}
  };

  frame.srcdoc=html;
  lastSnap=e._rt||0;
}

function replayField(key,locator){
  try{
    const doc=frame.contentDocument;
    if(!doc)return null;
    if(key){
      const byKey=doc.querySelector('[data-fh-replay-field="'+String(key).replace(/"/g,'')+'"]');
      if(byKey)return byKey;
    }
    if(locator){
      try{return doc.querySelector(String(locator))}catch(_){ }
    }
    return null;
  }catch(_){return null}
}

function maskedBullets(n){
  n=Math.max(0,Math.min(64,Number(n)||0));
  return n ? '•'.repeat(n) : '';
}

function applyMaskedFormEvent(e){
  const el=replayField(e.field_key,e.field_locator);
  if(!el)return;
  try{
    const doc=frame.contentDocument;
    if(doc)doc.querySelectorAll('.rp-field-active').forEach(x=>x.classList.remove('rp-field-active'));
    el.classList.add('rp-field-active');

    const kind=String(e.field_kind||'text');
    if(kind==='checkbox'||kind==='radio'){
      el.checked=!!e.checked;
    }else if(kind==='select'){
      let opt=el.querySelector('option[data-fh-masked-option]');
      if(!opt){
        opt=(doc||document).createElement('option');
        opt.setAttribute('data-fh-masked-option','1');
        opt.textContent='••••'; opt.value='';
        el.insertBefore(opt,el.firstChild);
      }
      opt.selected=true;
      el.value='';
    }else if(kind==='contenteditable'){
      el.textContent=maskedBullets(e.masked_length);
    }else{
      el.value=maskedBullets(e.masked_length);
      if(el.tagName==='TEXTAREA')el.textContent=el.value;
    }
  }catch(_){ }
}

function apply(e,fromDrain){
  if(!fromDrain && eventNeedsFrame(e) && !frameReady){
    pendingFrameEvents.push(e);
    return;
  }
  if(e.type==='snapshot'&&e.html){
    if(e.path)$('#rpPage').textContent=e.path;
    applySnapshot(e);
  }else if(e.type==='page_view'){
    if(e.path)$('#rpPage').textContent=e.path;
  }else if(e.type==='form_input'){
    applyMaskedFormEvent(e);
  }else if(e.type==='form_interaction'||e.type==='form_keystroke'){
    const el=replayField(e.field_key,e.field_locator);
    if(el){
      try{
        const doc=frame.contentDocument;
        if(doc)doc.querySelectorAll('.rp-field-active').forEach(x=>x.classList.remove('rp-field-active'));
        el.classList.add('rp-field-active');
      }catch(_){}
    }
  }else if(e.type==='move'){
    cursor.style.display='block';
    cursor.style.left=(e.x||0)+'px';
    cursor.style.top=(e.y||0)+'px';
  }else if(e.type==='scroll'){
    try{frame.contentWindow.scrollTo(e.x||0,e.y||0)}catch(_){}
  }else if(e.type==='click'){
    cursor.style.display='block';
    cursor.style.left=(e.cx||e.x||0)+'px';
    cursor.style.top=(e.cy||0)+'px';
    pulse.style.left=cursor.style.left;
    pulse.style.top=cursor.style.top;
    pulse.classList.remove('go');
    void pulse.offsetWidth;
    pulse.classList.add('go');
  }else if(e.type==='resize'){
    if(e.vh)frame.style.height=Math.max(420,Math.min(1200,e.vh))+'px';
  }
}

function seek(ms){
  liveFollow=false;
  rebuildAt(ms);
}

function tick(now){
  if(!playing)return;

  pos=startPos+(now-startWall)*speed;

  for(let i=applied+1;i<events.length;i++){
    if((events[i]._rt||0)>pos)break;
    apply(events[i]);
    applied=i;
  }

  if(pos>=total()){
    pos=total();
    if(!liveFollow){
      playing=false;
      $('#rpPlay').textContent='▶ Play';
    }else{
      /* Stay armed at the live edge. New polled events extend total(). */
      startPos=pos;
      startWall=now;
    }
  }

  sync();
  if(playing)raf=requestAnimationFrame(tick);
}

$('#rpPlay').onclick=()=>{
  if(!events.length)return;
  liveFollow=false;

  if(playing){
    playing=false;
    cancelAnimationFrame(raf);
    $('#rpPlay').textContent='▶ Play';
  }else{
    if(pos>=total())seek(0);
    playing=true;
    startWall=performance.now();
    startPos=pos;
    $('#rpPlay').textContent='❚❚ Pause';
    raf=requestAnimationFrame(tick);
  }
};

$('#rpSpeed').onchange=e=>{
  speed=Number(e.target.value)||1;
  if(playing){
    startPos=pos;
    startWall=performance.now();
  }
};

range.oninput=e=>{liveFollow=false;seek(Number(e.target.value));};
$('#rpLive').onclick=()=>{liveFollow=true;seek(total());if(!playing){playing=true;startWall=performance.now();startPos=pos;$('#rpPlay').textContent='❚❚ Pause';raf=requestAnimationFrame(tick);}};

poll();
setInterval(poll,150);

/* Wait for the first poll before seeking, so a snapshot actually exists. */
setTimeout(()=>{
  if(events.length)rebuildAt(0);
},1000);

})();
</script>
@endsection
