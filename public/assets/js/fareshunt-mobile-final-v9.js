(function(){
  'use strict';

  function ready(fn){
    if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn, {once:true});
    else fn();
  }

  ready(function(){
    /* Lazy-load inline background artwork only as it approaches the viewport. */
    var bgNodes = Array.prototype.slice.call(document.querySelectorAll('[data-fh-bg]'));
    function loadBg(el){
      if(!el || el.dataset.fhBgLoaded === '1') return;
      var url = el.getAttribute('data-fh-bg');
      if(!url) return;
      el.style.setProperty('background-image', 'url("' + String(url).replace(/"/g,'\\"') + '")', 'important');
      el.dataset.fhBgLoaded = '1';
      el.classList.add('fh-bg-loaded');
    }
    if('IntersectionObserver' in window){
      var bgObserver = new IntersectionObserver(function(entries){
        entries.forEach(function(entry){
          if(entry.isIntersecting){ loadBg(entry.target); bgObserver.unobserve(entry.target); }
        });
      }, {rootMargin:'500px 0px'});
      bgNodes.forEach(function(el){ bgObserver.observe(el); });
    } else {
      bgNodes.forEach(loadBg);
    }

    /* Prevent accidental rapid double-submit from creating duplicate navigation. */
    document.addEventListener('submit', function(e){
      var form = e.target;
      if(!form || form.id !== 'form2') return;
      var button = form.querySelector('.fh-search-submit');
      if(button){
        button.setAttribute('aria-busy','true');
        button.style.pointerEvents='none';
        setTimeout(function(){ button.style.pointerEvents=''; button.removeAttribute('aria-busy'); }, 8000);
      }
    }, false);

    /* Any long horizontal rail remains keyboard reachable and wheel-friendly. */
    document.querySelectorAll('.fh-home .fh-deal-grid,.fh-home .fh-airport-grid,.fh-home .fh-route-grid,.fh-home .fh-airline-grid,.fh-home .fh-why-grid,.fh-home .fh-guide-grid,.fh-home .fh-premium-grid').forEach(function(rail){
      if(!rail.hasAttribute('tabindex')) rail.setAttribute('tabindex','0');
    });
  });
})();
