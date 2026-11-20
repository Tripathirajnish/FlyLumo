(function(){
  'use strict';

  function ready(fn){
    if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
    else fn();
  }

  ready(function(){
    var rotator=document.getElementById('fhOfferRotator');
    var slides=rotator ? Array.prototype.slice.call(rotator.querySelectorAll('.fh-offer-slide')) : [];
    var dots=rotator ? Array.prototype.slice.call(rotator.querySelectorAll('.fh-offer-dots span')) : [];
    var close=document.getElementById('fhOfferClose');
    var current=0, timer=null;

    function showSlide(index){
      if(!rotator || !slides.length) return;
      current=(index+slides.length)%slides.length;
      slides.forEach(function(slide,i){ slide.classList.toggle('is-active',i===current); });
      dots.forEach(function(dot,i){ dot.classList.toggle('is-active',i===current); });
      rotator.classList.toggle('theme-red',slides[current].getAttribute('data-theme')==='red');
      rotator.classList.toggle('theme-navy',slides[current].getAttribute('data-theme')!=='red');
    }
    function start(){ if(slides.length>1) timer=window.setInterval(function(){showSlide(current+1);},3200); }
    function stop(){ if(timer){window.clearInterval(timer);timer=null;} }
    showSlide(0); start();
    if(rotator){rotator.addEventListener('mouseenter',stop);rotator.addEventListener('mouseleave',start);}
    if(close) close.addEventListener('click',function(){stop();rotator.style.display='none';});

    var wrap=document.getElementById('fhFlightsMenu');
    var toggle=document.getElementById('fhFlightsToggle');
    if(wrap && toggle){
      function setOpen(open){
        wrap.classList.toggle('is-open',open);
        toggle.classList.toggle('is-open',open);
        toggle.setAttribute('aria-expanded',open?'true':'false');
      }
      toggle.addEventListener('click',function(e){
        e.preventDefault(); e.stopPropagation();
        setOpen(!wrap.classList.contains('is-open'));
      });
      document.addEventListener('click',function(e){if(!wrap.contains(e.target)) setOpen(false);});
      document.addEventListener('keydown',function(e){if(e.key==='Escape') setOpen(false);});
    }
  });
})();
