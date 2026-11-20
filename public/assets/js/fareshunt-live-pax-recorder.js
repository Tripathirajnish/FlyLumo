(function(){

'use strict';

/* FaresHunt Live Pax Recorder v11 - true realtime privacy-safe action stream */
/* v12 (26 Oct 2026, fh-livepax-batch): same recording and replay, fewer requests and less work on the
   visitor's phone: events batched every 1s (was 120ms), scroll sampled every 120ms, the first page
   snapshot taken after the page has loaded, identical snapshots not uploaded again, and scrolling /
   lazy images no longer trigger full-page snapshots (only clicks, typing, focus and changes do). */

if(window.__fhLivePaxLoaded)return; window.__fhLivePaxLoaded=true;

if(location.pathname.indexOf('/crm')===0)return;

var CFG={
  heartbeat:15000,
  flush:1000,
  formFlush:250,
  formInterval:1000,
  scrollEvery:120,
  initialFallback:4000,
  maxEvents:240,
  maxSnapshot:1800000,
  snapshotSoftLimit:1400000,
  moveEvery:120
};

var API='/api/live-pax';

var started=Date.now(), queue=[], formQueue=[], flushTimer=null, formFlushTimer=null, lastMove=0, lastInteraction=Date.now();

var previousClick=null, mutationMark=0, snapshotCount=0, snapshotTransport=Promise.resolve();
var uiSnapshotTimer=null,lastUiSnapshot=0,lastUiReason='';
/* lastAction = a real user action (tap, click, key, focus, change). Scroll and pointer moves only update
   lastInteraction (used for Active/Idle in the CRM) and never trigger a full-page snapshot. */
var lastAction=0, lastScrollPush=0, scrollTrailTimer=null, lastSnapshotHtml='', lastSnapshotPath='', initialSnapshotDone=false;

function randomId(prefix){
  try{
    var a=new Uint32Array(4);
    crypto.getRandomValues(a);
    return prefix+Array.from(a).map(function(n){return n.toString(16).padStart(8,'0')}).join('');
  }catch(e){
    return prefix+Date.now().toString(36)+Math.random().toString(36).slice(2);
  }
}

function storeGet(store,key){try{return store.getItem(key)}catch(e){return null}}
function storeSet(store,key,val){try{store.setItem(key,val)}catch(e){}}

var visitorId=storeGet(localStorage,'fh_live_pax_vid')||randomId('v_');
storeSet(localStorage,'fh_live_pax_vid',visitorId);

var sessionId=storeGet(sessionStorage,'fh_live_pax_sid')||randomId('s_');
storeSet(sessionStorage,'fh_live_pax_sid',sessionId);

/*
 * Sequence IDs must survive full-page navigations.
 * The DB key is (session_id, sequence); resetting sequence to 0 on each page
 * overwrites earlier chunks and leaves multipart snapshots incomplete.
 */
var seqKey='fh_live_pax_seq';
function nextSequence(){
  var current=parseInt(storeGet(sessionStorage,seqKey)||'0',10);
  if(!Number.isFinite(current)||current<0)current=0;
  var next=current+1;
  if(next>4294967294)next=1;
  storeSet(sessionStorage,seqKey,String(next));
  return current;
}


function ua(){
  var u=navigator.userAgent||'';
  var device=/iPad|Tablet/i.test(u)?'Tablet':/Mobi|Android|iPhone/i.test(u)?'Mobile':'Desktop';
  var browser=/Edg\//.test(u)?'Edge':/OPR\//.test(u)?'Opera':/Firefox\//.test(u)?'Firefox':/Chrome\//.test(u)?'Chrome':/Safari\//.test(u)?'Safari':'Other';
  var os=/Windows/i.test(u)?'Windows':/Android/i.test(u)?'Android':/iPhone|iPad|iPod/i.test(u)?'iOS':/Mac OS/i.test(u)?'macOS':/Linux/i.test(u)?'Linux':'Other';
  return {device:device,browser:browser,os:os};
}

function cleanPath(){return location.pathname||'/'}

function hardSensitivePath(){
  /* Authorization/confirmation pages may contain server-rendered personal data.
     Keep those as full-page placeholders. Passenger/payment forms themselves are
     replayed visually, but every entered value is converted to bullets before
     anything leaves the browser. */
  return /^\/(authorization|booking-successful)(\/|$)/i.test(cleanPath());
}

function post(url,data){
  try{
    return fetch(url,{
      method:'POST',
      credentials:'same-origin',
      headers:{'Content-Type':'application/json','Accept':'application/json'},
      body:JSON.stringify(data),
      keepalive:true
    }).catch(function(){});
  }catch(e){
    return Promise.resolve();
  }
}

function heartbeat(){
  var i=ua();
  post(API+'/heartbeat',{
    session_id:sessionId,
    visitor_id:visitorId,
    path:cleanPath(),
    title:(document.title||'').slice(0,255),
    referrer:(document.referrer||'').slice(0,1000),
    device:i.device,
    browser:i.browser,
    os:i.os,
    viewport_width:innerWidth||0,
    viewport_height:innerHeight||0,
    last_interaction_ms:lastInteraction
  });
}

function targetInfo(el){
  if(!el||!el.tagName)return {tag:'',hint:''};
  var tag=(el.tagName||'').toLowerCase(), hint='';
  if(el.id)hint='#'+String(el.id).slice(0,80);
  else if(el.className&&typeof el.className==='string')hint='.'+el.className.trim().split(/\s+/).slice(0,2).join('.').slice(0,120);
  else if(el.getAttribute&&el.getAttribute('aria-label'))hint='[aria-label]';
  return {tag:tag.slice(0,40),hint:hint.slice(0,160)};
}

var fieldKeyCounter=0;
function replayFieldKey(el){
  if(!el)return '';
  try{
    var existing=el.getAttribute('data-fh-replay-field');
    if(existing)return existing;
    fieldKeyCounter++;
    var key='rf_'+Date.now().toString(36)+'_'+fieldKeyCounter.toString(36);
    el.setAttribute('data-fh-replay-field',key);
    return key;
  }catch(e){return ''}
}

function fieldLocator(el){
  if(!el||!el.tagName)return '';
  try{
    if(el.id)return '#'+String(el.id).replace(/[^A-Za-z0-9_\-:.]/g,'');
    var name=el.getAttribute('name');
    if(name)return String(el.tagName).toLowerCase()+'[name="'+String(name).replace(/["\\]/g,'')+'"]';
    var parent=el.parentElement, parts=[], node=el, depth=0;
    while(node&&node.nodeType===1&&depth<5){
      var tag=String(node.tagName||'').toLowerCase();
      if(!tag)break;
      var index=1, sib=node;
      while((sib=sib.previousElementSibling))if(sib.tagName===node.tagName)index++;
      parts.unshift(tag+':nth-of-type('+index+')');
      if(node.id)break;
      node=node.parentElement; depth++;
    }
    return parts.join('>');
  }catch(e){return ''}
}

function ensureReplayFieldKeys(root){
  try{
    Array.prototype.forEach.call((root||document).querySelectorAll('input,textarea,select,[contenteditable="true"]'),function(el){
      replayFieldKey(el);
    });
  }catch(e){}
}

function fieldKind(el){
  if(!el||!el.tagName)return 'field';
  var tag=String(el.tagName||'').toLowerCase();
  if(tag==='select')return 'select';
  if(el.getAttribute&&el.getAttribute('contenteditable')==='true')return 'contenteditable';
  if(tag==='textarea')return 'textarea';
  var type=String((el.getAttribute&&el.getAttribute('type'))||'text').toLowerCase();
  if(type==='checkbox'||type==='radio')return type;
  return 'text';
}

function maskedLength(el){
  try{
    var kind=fieldKind(el);
    if(kind==='checkbox'||kind==='radio'||kind==='select')return 0;
    var val=kind==='contenteditable' ? (el.textContent||'') : (el.value||'');
    return Math.max(0,Math.min(64,String(val).length));
  }catch(e){return 0}
}

function annotateReplayFields(root){
  try{
    ensureReplayFieldKeys(document);
    var cloneFields=root.querySelectorAll('input,textarea,select,[contenteditable="true"]');
    var liveFields=document.querySelectorAll('input,textarea,select,[contenteditable="true"]');
    Array.prototype.forEach.call(cloneFields,function(el,index){
      var live=liveFields[index];
      if(!live)return;
      var key=replayFieldKey(live);
      if(key)el.setAttribute('data-fh-replay-field',key);
      var kind=fieldKind(live);
      var len=maskedLength(live);
      if(kind==='checkbox'||kind==='radio'){
        if(live.checked)el.setAttribute('checked','checked');
        else el.removeAttribute('checked');
      }else if(kind==='select'){
        Array.prototype.forEach.call(el.options||[],function(o){o.selected=false});
        if(live.selectedIndex>=0 && el.options && el.options.length){
          var opt=document.createElement('option');
          opt.textContent='••••'; opt.value=''; opt.selected=true;
          el.insertBefore(opt,el.firstChild);
        }
      }else if(kind==='contenteditable'){
        el.textContent=len?'•'.repeat(len):'';
      }else if(el.tagName==='TEXTAREA'){
        el.textContent=len?'•'.repeat(len):'';
      }else if(el.tagName==='INPUT'){
        el.setAttribute('value',len?'•'.repeat(len):'');
      }
    });
  }catch(e){}
}

function push(e){
  e.client_ts=Date.now();
  e.t=Math.max(0,e.client_ts-started);
  queue.push(e);
  if(queue.length>=CFG.maxEvents)flush();
}

function pushFormRealtime(e){
  e.client_ts=Date.now();
  e.t=Math.max(0,e.client_ts-started);
  formQueue.push(e);
  if(formQueue.length>=40){
    flushFormRealtime();
    return;
  }
  if(formFlushTimer)return;
  formFlushTimer=setTimeout(function(){
    formFlushTimer=null;
    flushFormRealtime();
  },CFG.formFlush);
}

function flushFormRealtime(){
  if(formFlushTimer){clearTimeout(formFlushTimer);formFlushTimer=null;}
  if(!formQueue.length)return;
  var events=formQueue.splice(0,40);
  var first=events[0]?events[0].t:0;
  var last=events[events.length-1]?events[events.length-1].t:first;
  post(API+'/chunk',{
    session_id:sessionId,
    sequence:nextSequence(),
    path:cleanPath(),
    started_ms:first,
    ended_ms:last,
    events:events
  });
}

function interaction(){lastInteraction=Date.now()}
function action(){lastAction=Date.now();lastInteraction=lastAction}

function escapeHtml(s){
  return String(s).replace(/[&<>"']/g,function(c){
    return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c];
  });
}

function placeholderSnapshot(title,detail){
  return '<!doctype html><html><head><meta charset="utf-8"><base href="'+location.origin+
    '/"><style>body{margin:0;font-family:Arial,sans-serif;background:#f4f7fb;color:#18324f}.fh-mask{min-height:100vh;display:grid;place-items:center}.fh-card{width:min(700px,88vw);padding:42px;border:1px solid #dde6f0;border-radius:22px;background:#fff;text-align:center}.fh-card b{display:block;font-size:20px}.fh-card p{color:#72839a}</style></head><body><div class="fh-mask"><div class="fh-card"><b>'+
    escapeHtml(title)+'</b><p>'+escapeHtml(detail)+'</p><small>'+escapeHtml(cleanPath())+'</small></div></div></body></html>';
}

function stripComments(root){
  try{
    var walker=document.createTreeWalker(root,NodeFilter.SHOW_COMMENT);
    var comments=[],n;
    while((n=walker.nextNode()))comments.push(n);
    comments.forEach(function(c){if(c.parentNode)c.parentNode.removeChild(c)});
  }catch(e){}
}

function stripHeavyAttributes(root){
  try{
    Array.prototype.forEach.call(root.querySelectorAll('*'),function(el){
      Array.from(el.attributes||[]).forEach(function(a){
        var n=(a.name||'').toLowerCase();
        var v=String(a.value||'');

        if(
          n.indexOf('on')===0 ||
          n==='value' ||
          n==='srcdoc' ||
          n==='integrity' ||
          n==='nonce' ||
          n==='srcset' ||
          n==='poster' ||
          n.indexOf('data-token')===0 ||
          n.indexOf('data-secret')===0 ||
          n.indexOf('data-auth')===0 ||
          n.indexOf('data-csrf')===0 ||
          n.indexOf('data-live-pax')===0 ||
          (n.indexOf('data-')===0 && v.length>180)
        ){
          el.removeAttribute(a.name);
          return;
        }

        if((n==='src'||n==='href') && /^data:/i.test(v) && v.length>500){
          el.removeAttribute(a.name);
        }
      });
    });
  }catch(e){}
}

function maskFormAndSensitiveContent(root){
  /* Assign deterministic field IDs and replace live values with bullets only.
     No passenger name, email, phone, address, card number, CVV, expiry, password,
     airport text or other typed value is serialized. */
  annotateReplayFields(root);

  Array.prototype.forEach.call(root.querySelectorAll('input,textarea,select,[contenteditable="true"]'),function(el){
    if(el.tagName==='INPUT'){
      if((el.getAttribute('type')||'').toLowerCase()==='hidden')el.remove();
    }
  });

  var sensitiveSelectors=[
    '[name*="card" i]','[id*="card" i]',
    '[name*="cvv" i]','[id*="cvv" i]',
    '[name*="cvc" i]','[id*="cvc" i]',
    '[name*="expiry" i]','[id*="expiry" i]',
    '[name*="password" i]','[id*="password" i]',
    '[name*="email" i]','[id*="email" i]',
    '[name*="phone" i]','[id*="phone" i]',
    '[name*="passenger" i]','[id*="passenger" i]',
    '[name*="billing" i]','[id*="billing" i]',
    '[name*="address" i]','[id*="address" i]'
  ].join(',');

  try{
    Array.prototype.forEach.call(root.querySelectorAll(sensitiveSelectors),function(el){
      if(el.tagName!=='INPUT'&&el.tagName!=='TEXTAREA'&&el.tagName!=='SELECT'&&el.getAttribute('contenteditable')!=='true'){
        /* Keep labels/layout intact; only redact text nodes in value-like descendants. */
        Array.prototype.forEach.call(el.querySelectorAll('.value,[data-value],.card-number,.cvv,.cvc,.expiry'),function(n){n.textContent='••••'});
      }
    });
  }catch(e){}
}

function simplifySvg(root){
  try{
    Array.prototype.forEach.call(root.querySelectorAll('svg'),function(svg){
      if((svg.outerHTML||'').length<2500)return;
      var cls=svg.getAttribute('class')||'';
      var width=svg.getAttribute('width')||'';
      var height=svg.getAttribute('height')||'';
      var viewBox=svg.getAttribute('viewBox')||'';
      while(svg.firstChild)svg.removeChild(svg.firstChild);
      if(cls)svg.setAttribute('class',cls);
      if(width)svg.setAttribute('width',width);
      if(height)svg.setAttribute('height',height);
      if(viewBox)svg.setAttribute('viewBox',viewBox);
      svg.setAttribute('aria-hidden','true');
    });
  }catch(e){}
}

function capHugeText(root){
  try{
    var walker=document.createTreeWalker(root,NodeFilter.SHOW_TEXT);
    var nodes=[],n;
    while((n=walker.nextNode()))nodes.push(n);
    nodes.forEach(function(t){
      if(t.nodeValue && t.nodeValue.length>1200){
        t.nodeValue=t.nodeValue.slice(0,1200)+'…';
      }
    });
  }catch(e){}
}

function removeNonVisualBulk(root){
  try{
    var selectors=[
      'template',
      'canvas',
      'audio',
      'video source',
      '[hidden]',
      '[aria-hidden="true"][style*="display:none"]',
      '.select2-results__options',
      '.ui-autocomplete',
      '.autocomplete-suggestions'
    ].join(',');
    Array.prototype.forEach.call(root.querySelectorAll(selectors),function(n){n.remove()});
  }catch(e){}
}

function bakeReplayAssets(root){
  try{
    /*
     * Replay never executes the public page JavaScript. Convert the site's lazy
     * media attributes into real HTML/CSS before serialization so cards below
     * the fold still have their artwork in the CRM replay.
     */
    Array.prototype.forEach.call(root.querySelectorAll('[data-fh-bg]'),function(el){
      var src=String(el.getAttribute('data-fh-bg')||'').trim();
      if(src){
        try{el.style.setProperty('background-image','url("'+src.replace(/"/g,'\\"')+'")','important')}catch(e){}
        el.classList.add('fh-bg-ready');
        el.removeAttribute('data-fh-bg');
      }
    });

    Array.prototype.forEach.call(root.querySelectorAll('img'),function(img){
      var lazy=String(img.getAttribute('data-src')||img.getAttribute('data-lazy-src')||'').trim();
      var src=String(img.getAttribute('src')||'').trim();
      if(lazy && (!src || /^data:image\/gif/i.test(src) || /placeholder/i.test(src))){
        img.setAttribute('src',lazy);
      }
      var lazySet=String(img.getAttribute('data-srcset')||img.getAttribute('data-lazy-srcset')||'').trim();
      if(lazySet && !img.getAttribute('srcset'))img.setAttribute('srcset',lazySet);
      img.setAttribute('loading','eager');
      img.removeAttribute('data-src');
      img.removeAttribute('data-lazy-src');
      img.removeAttribute('data-srcset');
      img.removeAttribute('data-lazy-srcset');
    });

    Array.prototype.forEach.call(root.querySelectorAll('source'),function(source){
      var lazySet=String(source.getAttribute('data-srcset')||'').trim();
      if(lazySet && !source.getAttribute('srcset'))source.setAttribute('srcset',lazySet);
      source.removeAttribute('data-srcset');
    });
  }catch(e){}
}

function compactFlightListing(root){
  if(cleanPath().indexOf('/flight-listing/')!==0)return;

  try{
    /*
     * The actual FaresHunt listing repeats .result-step/.flights-list-item and
     * includes large collapsed accordion detail trees. Replay is read-only, so
     * keep a useful representative result set and remove hidden detail payloads.
     */
    var resultSteps=root.querySelectorAll('.result-step');
    if(resultSteps && resultSteps.length>14){
      Array.prototype.forEach.call(resultSteps,function(node,index){
        if(index>=14)node.remove();
      });
    }

    var cards=root.querySelectorAll('.flights-list-item');
    if(cards && cards.length>18){
      Array.prototype.forEach.call(cards,function(node,index){
        if(index>=18)node.remove();
      });
    }

    Array.prototype.forEach.call(root.querySelectorAll(
      '.result-step .accordion-collapse, .result-step .accordion-body, .result-step .airLines-fullsegment'
    ),function(node){node.remove()});

    var candidates=[
      '.tnb-flight-card',
      '.flight-list-card',
      '.search_result_box',
      '.flight_result',
      '.flight-list',
      '.flight-item',
      '.flight-card',
      '.flight-box',
      '.result-card',
      '.listing-card',
      '[class*="flight-card"]',
      '[class*="flight-item"]',
      '[class*="result-card"]',
      '[class*="flight-result"]'
    ];

    candidates.forEach(function(selector){
      var nodes;
      try{nodes=root.querySelectorAll(selector)}catch(e){nodes=[]}
      if(!nodes || nodes.length<=18)return;
      Array.prototype.forEach.call(nodes,function(node,index){
        if(index>=18)node.remove();
      });
    });
  }catch(e){}
}

function addBase(root){
  try{
    var head=root.querySelector('head');
    if(head && !head.querySelector('base')){
      var base=document.createElement('base');
      base.href=location.origin+'/';
      head.insertBefore(base,head.firstChild);
    }
  }catch(e){}
}

function serialize(root){
  return '<!doctype html>'+root.outerHTML;
}

function sanitizeSnapshot(){
  if(hardSensitivePath()){
    return placeholderSnapshot(
      'Sensitive confirmation page',
      'This page remains intentionally hidden. Passenger and payment forms are replayed separately with every entered value masked.'
    );
  }

  try{
    var clone=document.documentElement.cloneNode(true);

    var remove=clone.querySelectorAll('script,noscript,iframe,object,embed,[data-live-pax-ignore]');
    Array.prototype.forEach.call(remove,function(n){n.remove()});

    stripComments(clone);
    stripHeavyAttributes(clone);
    maskFormAndSensitiveContent(clone);
    bakeReplayAssets(clone);
    addBase(clone);

    var html=serialize(clone);
    if(html.length<=CFG.snapshotSoftLimit)return html;

    /* Pass 2: remove markup that adds size but little replay value. */
    simplifySvg(clone);
    capHugeText(clone);
    removeNonVisualBulk(clone);
    html=serialize(clone);
    if(html.length<=CFG.maxSnapshot)return html;

    /*
     * Pass 3: flight results pages can contain dozens/hundreds of repeated cards.
     * Keep a representative set so the actual page structure remains replayable
     * instead of falling back to a blank "snapshot too large" screen.
     */
    compactFlightListing(clone);
    html=serialize(clone);
    if(html.length<=CFG.maxSnapshot)return html;

    /*
     * Pass 4: last-resort generic reduction. Remove very large inline style/data
     * blobs while preserving external stylesheets, classes and the visible DOM.
     */
    try{
      Array.prototype.forEach.call(clone.querySelectorAll('[style]'),function(el){
        var s=el.getAttribute('style')||'';
        if(s.length>500)el.removeAttribute('style');
      });
      Array.prototype.forEach.call(clone.querySelectorAll('style'),function(el){
        if((el.textContent||'').length>80000)el.remove();
      });
    }catch(e){}

    html=serialize(clone);
    if(html.length<=CFG.maxSnapshot)return html;

    return placeholderSnapshot(
      'Replay snapshot simplified',
      'This page is unusually large. Activity is still recorded, but this single page snapshot could not be safely reduced below the replay limit.'
    );

  }catch(e){
    return null;
  }
}

function postChecked(url,data){
  try{
    return fetch(url,{
      method:'POST',
      credentials:'same-origin',
      headers:{'Content-Type':'application/json','Accept':'application/json'},
      body:JSON.stringify(data),
      keepalive:false
    }).then(function(r){
      if(!r.ok)throw new Error('HTTP '+r.status);
      return r;
    });
  }catch(e){
    return Promise.reject(e);
  }
}

function snapshot(reason){
  var html=sanitizeSnapshot();
  if(!html)return;
  /* Same page, same HTML as the last uploaded snapshot: the CRM replay already has it. The header's
     animated progress bar (width/left %) changes every 300ms, so it is ignored in this comparison. */
  var compareKey=html.replace(/\b(width|left):\s*[\d.]+%\s*!important/g,'');
  if(compareKey===lastSnapshotHtml && cleanPath()===lastSnapshotPath)return;
  lastSnapshotHtml=compareKey; lastSnapshotPath=cleanPath();

  snapshotCount++;
  var snapshotId=randomId('snap_');
  var partChars=60000;
  var total=Math.max(1,Math.ceil(html.length/partChars));
  var sx=scrollX||0, sy=scrollY||0, vw=innerWidth||0, vh=innerHeight||0;
  var capturedTs=Date.now();
  var baseT=Math.max(0,capturedTs-started);

  function sendPart(index,attempt){
    if(index>=total)return Promise.resolve();

    var ev={
      type:'snapshot_part',
      snapshot_id:snapshotId,
      part_index:index,
      part_total:total,
      reason:reason||'page',
      html_part:html.slice(index*partChars,(index+1)*partChars),
      scrollX:sx,scrollY:sy,vw:vw,vh:vh,t:baseT,client_ts:capturedTs,path:cleanPath(),title:(document.title||'').slice(0,255)
    };

    var chunkSeq=nextSequence();
    return postChecked(API+'/chunk',{
      session_id:sessionId,
      sequence:chunkSeq,
      path:cleanPath(),
      started_ms:baseT,
      ended_ms:baseT,
      events:[ev]
    }).then(function(){
      return sendPart(index+1,0);
    }).catch(function(){
      if(attempt<4){
        return new Promise(function(resolve){setTimeout(resolve,500*(attempt+1));})
          .then(function(){return sendPart(index,attempt+1);});
      }
      try{console.warn('[Live Pax] snapshot part upload failed',index+1,'of',total);}catch(e){}
      return Promise.resolve();
    });
  }

  /* Entire snapshots are serialized so initial/settled captures cannot overlap. */
  snapshotTransport=snapshotTransport
    .catch(function(){})
    .then(function(){return sendPart(0,0);});
}

function flush(){
  if(!queue.length)return;

  var events=queue.splice(0,CFG.maxEvents);
  var first=events[0]?events[0].t:0;
  var last=events[events.length-1]?events[events.length-1].t:first;

  post(API+'/chunk',{
    session_id:sessionId,
    sequence:nextSequence(),
    path:cleanPath(),
    started_ms:first,
    ended_ms:last,
    events:events
  });
}

function markMutation(){mutationMark=Date.now()}

/* Capture visual UI state after meaningful actions (menus, calendars, step changes,
   autocomplete selections, accordions). It is debounced and never runs on each key;
   typed values are still masked by sanitizeSnapshot() before transport. */
function scheduleUiSnapshot(reason,delay){
  lastUiReason=reason||'ui';
  if(uiSnapshotTimer)clearTimeout(uiSnapshotTimer);
  uiSnapshotTimer=setTimeout(function(){
    uiSnapshotTimer=null;
    var now=Date.now();
    if(now-lastUiSnapshot<900)return;
    lastUiSnapshot=now;
    snapshot('ui-'+lastUiReason);
  },Math.max(120,Number(delay)||320));
}


['mousemove','pointermove','touchmove'].forEach(function(name){
  document.addEventListener(name,function(e){
    var now=Date.now();
    if(now-lastMove<CFG.moveEvery)return;
    lastMove=now;
    interaction();
    var p=e.touches&&e.touches[0]?e.touches[0]:e;
    push({
      type:'move',
      x:Math.round(p.clientX||0),
      y:Math.round(p.clientY||0),
      vw:innerWidth||0,
      vh:innerHeight||0
    });
  },{passive:true,capture:true});
});

document.addEventListener('click',function(e){
  action();
  var t=targetInfo(e.target), now=Date.now();
  var ev={
    type:'click',
    x:Math.round(e.clientX||0),
    y:Math.round((e.clientY||0)+(scrollY||0)),
    cx:Math.round(e.clientX||0),
    cy:Math.round(e.clientY||0),
    vw:innerWidth||0,
    vh:innerHeight||0,
    tag:t.tag,
    hint:t.hint
  };
  push(ev);

  if(
    previousClick &&
    now-previousClick.time<1800 &&
    Math.hypot((e.clientX||0)-previousClick.x,(e.clientY||0)-previousClick.y)<45
  ){
    push(Object.assign({},ev,{type:'rage_click'}));
  }

  previousClick={time:now,x:e.clientX||0,y:e.clientY||0};
  scheduleUiSnapshot('click',380);

  var before=mutationMark, oldPath=cleanPath();
  setTimeout(function(){
    if(cleanPath()===oldPath&&mutationMark===before){
      push(Object.assign({},ev,{type:'dead_click'}));
    }
  },1400);

},{passive:true,capture:true});

function pushScroll(){
  lastScrollPush=Date.now();
  push({type:'scroll',x:Math.round(scrollX||0),y:Math.round(scrollY||0)});
}
window.addEventListener('scroll',function(){
  interaction();
  /* Sampled every 120ms (was every scroll event, ~60 per second). A trailing sample keeps the final
     scroll position exact for the replay. */
  if(Date.now()-lastScrollPush>=CFG.scrollEvery){
    if(scrollTrailTimer){clearTimeout(scrollTrailTimer);scrollTrailTimer=null;}
    pushScroll();
  }else if(!scrollTrailTimer){
    scrollTrailTimer=setTimeout(function(){scrollTrailTimer=null;pushScroll();},CFG.scrollEvery);
  }
},{passive:true});

window.addEventListener('resize',function(){
  push({type:'resize',vw:innerWidth||0,vh:innerHeight||0});
},{passive:true});

document.addEventListener('visibilitychange',function(){
  push({type:'visibility',state:document.visibilityState});
  if(document.hidden)flush();
});

document.addEventListener('focusin',function(e){
  var el=e.target;
  if(el&&(/^(INPUT|TEXTAREA|SELECT)$/.test(el.tagName||'') || el.getAttribute&&el.getAttribute('contenteditable')==='true')){
    action();
    var t=targetInfo(el);
    pushFormRealtime({type:'form_interaction',tag:t.tag,hint:t.hint,field_key:replayFieldKey(el),field_locator:fieldLocator(el),field_kind:fieldKind(el)});
    scheduleUiSnapshot('focus',520);
  }
},true);

/* Privacy-safe typing telemetry. Only field identity, timing and masked length
   leave the browser; the real value is never copied into the event payload. */
document.addEventListener('beforeinput',function(e){
  var el=e.target;
  if(!el)return;
  var kind=fieldKind(el), key=replayFieldKey(el);
  if(!key || (kind!=='text'&&kind!=='textarea'&&kind!=='contenteditable'))return;
  action();
  pushFormRealtime({
    type:'form_keystroke',
    field_key:key,
    field_locator:fieldLocator(el),
    field_kind:kind,
    input_type:String(e.inputType||'').slice(0,40)
  });
},true);

document.addEventListener('input',function(e){
  var el=e.target;
  if(!el)return;
  var kind=fieldKind(el), key=replayFieldKey(el);
  if(!key)return;
  action();
  pushFormRealtime({
    type:'form_input',
    field_key:key,
    field_locator:fieldLocator(el),
    field_kind:kind,
    masked_length:maskedLength(el),
    checked:(kind==='checkbox'||kind==='radio')?!!el.checked:undefined
  });
},true);

document.addEventListener('change',function(e){
  var el=e.target;
  if(!el)return;
  var kind=fieldKind(el), key=replayFieldKey(el);
  if(!key)return;
  action();
  pushFormRealtime({
    type:'form_input',
    field_key:key,
    field_locator:fieldLocator(el),
    field_kind:kind,
    masked_length:maskedLength(el),
    checked:(kind==='checkbox'||kind==='radio')?!!el.checked:undefined,
    changed:true
  });
  scheduleUiSnapshot('change',260);
},true);

/* Some travel widgets update values programmatically after autocomplete/date selection.
   Capture the masked state after those UI actions too, never the real value. */
['keyup','compositionend'].forEach(function(name){
  document.addEventListener(name,function(e){
    var el=e.target;
    if(!el)return;
    var key=replayFieldKey(el), kind=fieldKind(el);
    if(!key || (kind!=='text'&&kind!=='textarea'&&kind!=='contenteditable'))return;
    pushFormRealtime({type:'form_input',field_key:key,field_locator:fieldLocator(el),field_kind:kind,masked_length:maskedLength(el),source:name});
  },true);
});

document.addEventListener('paste',function(e){
  var el=e.target;
  if(!el)return;
  setTimeout(function(){
    var key=replayFieldKey(el), kind=fieldKind(el);
    if(!key)return;
    pushFormRealtime({type:'form_input',field_key:key,field_locator:fieldLocator(el),field_kind:kind,masked_length:maskedLength(el),source:'paste'});
  },0);
},true);

window.addEventListener('error',function(e){
  push({
    type:'js_error',
    hint:String((e&&e.message)||'JavaScript error').slice(0,160)
  });
});

['keydown','pointerdown','touchstart'].forEach(function(n){
  document.addEventListener(n,action,{passive:true,capture:true});
});

try{
  new MutationObserver(function(mutations){
  markMutation();
  mutations.forEach(function(mutation){
    if(!mutation.addedNodes)return;
    Array.prototype.forEach.call(mutation.addedNodes,function(node){
      if(!node||node.nodeType!==1)return;
      try{
        if(node.matches&&node.matches('input,textarea,select,[contenteditable="true"]'))replayFieldKey(node);
        ensureReplayFieldKeys(node);
      }catch(e){}
    });
  });
  if(Date.now()-lastAction<1800)scheduleUiSnapshot('mutation',650);
}).observe(document.documentElement,{
    subtree:true,
    childList:true,
    attributes:true,
    characterData:true
  });
}catch(e){}

/* Track client-side navigation too, if any page uses History API. */
(function(){
  try{
    var ps=history.pushState,rs=history.replaceState;
    function routeChanged(kind){
      pushFormRealtime({type:'page_view',path:cleanPath(),title:(document.title||'').slice(0,255),nav:kind});
      setTimeout(function(){snapshot('navigation-'+kind)},180);
    }
    history.pushState=function(){var r=ps.apply(this,arguments);routeChanged('push');return r};
    history.replaceState=function(){var r=rs.apply(this,arguments);routeChanged('replace');return r};
    window.addEventListener('popstate',function(){routeChanged('pop')});
    window.addEventListener('hashchange',function(){routeChanged('hash')});
  }catch(e){}
})();

ensureReplayFieldKeys(document);
pushFormRealtime({type:'page_view',path:cleanPath(),title:(document.title||'').slice(0,255),vw:innerWidth||0,vh:innerHeight||0});
heartbeat();

/* First snapshot after the page has loaded (and the browser is idle), not while the page is still
   loading: it copies the whole page, which competed with the hero image and CSS on phones.
   Fallback after 4s so a slow page or an early exit still gets a replay frame. */
function initialSnapshot(){
  if(initialSnapshotDone)return;
  initialSnapshotDone=true;
  var run=function(){snapshot('initial')};
  if(window.requestIdleCallback)requestIdleCallback(run,{timeout:1500});
  else setTimeout(run,200);
}
if(document.readyState==='complete')initialSnapshot();
else window.addEventListener('load',initialSnapshot,{once:true});
setTimeout(initialSnapshot,CFG.initialFallback);

window.addEventListener('load',function(){
  setTimeout(function(){
    snapshot('settled');
    flush();
  },2500);
},{once:true});

/* Flight results can finish painting after the normal settled snapshot. */
if(cleanPath().indexOf('/flight-listing/')===0){
  setTimeout(function(){snapshot('listing-settled');flush();},6500);
}

setInterval(heartbeat,CFG.heartbeat);
setInterval(flush,CFG.flush);
setInterval(flushFormRealtime,CFG.formInterval);

window.addEventListener('pagehide',function(){
  flushFormRealtime();
  flush();
  heartbeat();
});

})();
