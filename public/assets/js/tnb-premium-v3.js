(function(){
  function qs(s,c){return (c||document).querySelector(s)}
  function qsa(s,c){return Array.from((c||document).querySelectorAll(s))}
  window.tnbSetTrip=function(v){
    qsa('.tnb-trip-tab').forEach(function(b){b.classList.toggle('active',b.dataset.trip===v)});
    var r=qs('#flight_types_'+v); if(r) r.checked=true;
    var rt=qs('#return_date'); if(rt){rt.disabled=(v!=='round_trip'); if(v!=='round_trip') rt.value=''; rt.required=(v==='round_trip');}
    if(window.fhToggleMultiCity) window.fhToggleMultiCity(v==='multi_city');
  };
  window.tnbTogglePax=function(){var p=qs('#tnbPaxPopover');if(p)p.classList.toggle('open')};
  window.tnbClosePax=function(){var p=qs('#tnbPaxPopover');if(p)p.classList.remove('open')};
  window.tnbPaxStep=function(type,step){
    var sel=qs('#'+type+'-count'); if(!sel)return; var n=parseInt(sel.value||0,10)+step;
    var min=type==='adult'?1:0,max=type==='adult'?9:5; n=Math.max(min,Math.min(max,n)); sel.value=String(n); updatePaxText();
  };
  function updatePaxText(){
    var a=parseInt((qs('#adult-count')||{}).value||1,10),c=parseInt((qs('#child-count')||{}).value||0,10),i=parseInt((qs('#infant-count')||{}).value||0,10);
    var total=a+c+i, b=qs('#passenger-count'); if(b)b.innerHTML='<strong>'+total+' Passenger'+(total>1?'s':'')+'</strong><span>'+a+' Adult'+(a>1?'s':'')+(c?' · '+c+' Child'+(c>1?'ren':''):'')+(i?' · '+i+' Infant'+(i>1?'s':''):'')+'</span>';
    [['adult',a],['child',c],['infant',i]].forEach(function(x){var t=qs('[data-pax-value="'+x[0]+'"]');if(t)t.textContent=x[1]});
  }
  function showSearchOverlay(form){
    var o=qs('#tnbSearchOverlay'); if(!o)return;
    var from=(qs('#location_from')||{}).value||'FROM',to=(qs('#location_to')||{}).value||'TO';
    var f=from.substring(0,3).toUpperCase(),t=to.substring(0,3).toUpperCase();
    var a=parseInt((qs('#adult-count')||{}).value||1,10),c=parseInt((qs('#child-count')||{}).value||0,10),i=parseInt((qs('#infant-count')||{}).value||0,10);
    var cabin=(qs('#cabin_classes')||{}).selectedOptions?.[0]?.textContent||'Economy';
    var trip=(qs('input[name="flight_types"]:checked')||{}).value||'round_trip';
    qsa('[data-board-from]').forEach(function(x){x.textContent=f});qsa('[data-board-to]').forEach(function(x){x.textContent=t});
    var meta=qs('[data-board-meta]');if(meta)meta.textContent='Searching '+(a+c+i)+' passenger'+((a+c+i)>1?'s':'')+' · '+cabin+' · '+(trip==='one_way'?'One Way':(trip==='multi_city'?'Multi-City':'Round Trip'));
    o.classList.add('show'); document.body.style.overflow='hidden';
  }
  function bindSearch(){
    qsa('form[action*="submit-search"]').forEach(function(f){f.addEventListener('submit',function(){showSearchOverlay(f)})});
  }
  document.addEventListener('DOMContentLoaded',function(){updatePaxText();bindSearch();document.addEventListener('click',function(e){var p=qs('#tnbPaxPopover');if(p&&p.classList.contains('open')&&!p.contains(e.target)&&!e.target.closest('#passenger-count'))p.classList.remove('open')})});
})();
