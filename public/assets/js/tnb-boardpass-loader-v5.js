
(function(){
  function q(s,c){return(c||document).querySelector(s)}
  function qa(s,c){return Array.from((c||document).querySelectorAll(s))}
  function set(s,v){qa(s).forEach(function(n){n.textContent=v})}
  function code(v,f){v=(v||'').trim();var m=v.match(/\(([A-Z0-9]{3,4})\)/i);if(m)return m[1].toUpperCase();var c=((v.split(/[-,|]/)[0]||'').replace(/[^A-Za-z0-9]/g,'').slice(0,3)).toUpperCase();return c||f}
  function current(){return{from:code((q('#location_from')||{}).value,'FROM'),to:code((q('#location_to')||{}).value,'TO')}}
  var slowSearchTimer=null;
  window.tnbShowBoardingLoader=function(o){
    o=o||{};var x=q('#tnbSearchOverlay');if(!x)return;var d=current(),mode=o.mode||'search';x.dataset.loaderMode=mode;
    set('[data-board-title]',o.title||(mode==='fare'?'Confirming your selected flight…':'Searching for the best flights for you…'));
    set('[data-board-subtitle]',o.subtitle||(mode==='fare'?'We are rechecking live fare, seat availability and booking conditions.':'This may take a few seconds while we compare live availability.'));
    set('[data-board-from]',o.from||d.from);set('[data-board-to]',o.to||d.to);
    set('[data-board-center]',o.center||(mode==='fare'?'Rechecking fare & seats':'Comparing routes & fares'));
    set('[data-board-status]',o.status||(mode==='fare'?'Validating selected fare':'Searching live inventory'));
    set('[data-board-mode]',mode==='fare'?'FARE CHECK':'LIVE SEARCH');
    set('[data-board-meta]',o.meta||(mode==='fare'?'Securing the selected option before passenger details':'Searching live flight availability'));
    x.classList.add('show');document.body.style.overflow='hidden';
    if(slowSearchTimer) clearTimeout(slowSearchTimer);
    if(mode==='search') slowSearchTimer=setTimeout(function(){if(x.classList.contains('show')){set('[data-board-title]','Still searching live fares…');set('[data-board-subtitle]','Live availability is taking a little longer than usual. Please keep this page open.');set('[data-board-status]','Waiting for live airline availability');}},8000);
  };
  window.tnbHideBoardingLoader=function(){if(slowSearchTimer){clearTimeout(slowSearchTimer);slowSearchTimer=null;}var x=q('#tnbSearchOverlay');if(x)x.classList.remove('show');document.body.style.overflow=''};
  document.addEventListener('DOMContentLoaded',function(){qa('form[action*="submit-search"]').forEach(function(f){f.addEventListener('submit',function(){window.tnbShowBoardingLoader({mode:'search'})})})});
})();
