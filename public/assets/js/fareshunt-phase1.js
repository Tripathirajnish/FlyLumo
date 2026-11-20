(function(){
  'use strict';

  function qs(s,c){return (c||document).querySelector(s)}
  function qsa(s,c){return Array.from((c||document).querySelectorAll(s))}

  document.addEventListener('DOMContentLoaded', function(){
    var header=qs('#fhHeader');
    function onScroll(){if(header)header.classList.toggle('scrolled',window.scrollY>20)}
    onScroll(); window.addEventListener('scroll',onScroll,{passive:true});

    var ann=qs('#fhAnnouncement'), annClose=qs('#fhAnnouncementClose');
    if(localStorage.getItem('fh_announcement_dismissed')==='1' && ann) ann.style.display='none';
    if(annClose) annClose.addEventListener('click',function(){if(ann)ann.style.display='none';localStorage.setItem('fh_announcement_dismissed','1')});

    var mt=qs('#fhMenuToggle'), mm=qs('#fhMobileMenu');
    if(mt&&mm) mt.addEventListener('click',function(){
      var open=mm.hasAttribute('hidden');
      if(open)mm.removeAttribute('hidden');else mm.setAttribute('hidden','');
      mt.setAttribute('aria-expanded',open?'true':'false');
      var i=mt.querySelector('i');if(i)i.className=open?'fa-solid fa-xmark':'fa-solid fa-bars';
    });


    var cookie=qs('#fhCookieConsent');
    if(cookie && !localStorage.getItem('fh_cookie_consent')){
      setTimeout(function(){cookie.removeAttribute('hidden')},1100);
      qsa('[data-cookie]',cookie).forEach(function(btn){
        btn.addEventListener('click',function(){
          localStorage.setItem('fh_cookie_consent',btn.dataset.cookie);
          cookie.setAttribute('hidden','');
        });
      });
    }

    var swap=qs('#fhSwapAirports');
    if(swap) swap.addEventListener('click',function(){
      var a=qs('#location_from'),b=qs('#location_to');if(!a||!b)return;
      var tmp=a.value;a.value=b.value;b.value=tmp;
    });

    qsa('[data-origin-airport]').forEach(function(btn){
      btn.addEventListener('click',function(){
        var from=qs('#location_from');
        if(from){from.value=btn.dataset.originAirport||'';window.scrollTo({top:Math.max(0,(qs('.fh-search-shell')?.getBoundingClientRect().top||0)+window.scrollY-150),behavior:'smooth'});setTimeout(function(){qs('#location_to')?.focus()},450)}
      });
    });

    // Enhance the existing passenger renderer without changing field names/back-end payload.
    function refreshPassengerButton(){
      var a=parseInt(qs('#adult-count')?.value||1,10), c=parseInt(qs('#child-count')?.value||0,10), i=parseInt(qs('#infant-count')?.value||0,10), total=a+c+i;
      var b=qs('#passenger-count'); if(!b)return;
      b.innerHTML='<i class="fa-solid fa-user-group"></i><span><strong>'+total+' Passenger'+(total===1?'':'s')+'</strong><small>'+a+' Adult'+(a===1?'':'s')+(c?' · '+c+' Child'+(c===1?'':'ren'):'')+(i?' · '+i+' Infant'+(i===1?'':'s'):'')+'</small></span><i class="fa-solid fa-chevron-down"></i>';
    }
    var oldStep=window.tnbPaxStep;
    if(typeof oldStep==='function') window.tnbPaxStep=function(type,step){oldStep(type,step);refreshPassengerButton()};
    refreshPassengerButton();

    // Keep return field visually and functionally aligned with existing round/one-way backend values.
    var oldTrip=window.tnbSetTrip;
    if(typeof oldTrip==='function') window.tnbSetTrip=function(v){
      oldTrip(v);
      var rf=qs('.fh-return-field'),ri=qs('#return_date');
      if(rf)rf.classList.toggle('is-disabled',v==='one_way');
      if(ri){
        if(v==='one_way'){ri.removeAttribute('required');}
        else{ri.setAttribute('required','required');}
      }
    };
  });
})();