(function(){
  function txt(el){return (el&&el.textContent||'').replace(/\s+/g,' ').trim().toLowerCase()}
  function directHeading(el){var h=el.querySelector('.card-header h6,.card-header h4');return txt(h)}
  function init(){
    var form=document.getElementById('form4'); if(!form)return;
    document.body.classList.add('tnb-checkout-active');
    var mainCol=form.closest('.col-xl-9,.col-lg-9'); if(!mainCol)return;
    var intro=document.createElement('div'); intro.className='tnb-checkout-intro';
    intro.innerHTML='<div class="tnb-checkout-steps"><div class="tnb-step-chip active" data-chip="0"><div class="dot"><i class="fa-solid fa-users"></i></div>Passengers</div><div class="tnb-step-chip" data-chip="1"><div class="dot"><i class="fa-solid fa-shield-heart"></i></div>Add-ons</div><div class="tnb-step-chip" data-chip="2"><div class="dot"><i class="fa-solid fa-credit-card"></i></div>Payment</div></div><div class="tnb-checkout-secure"><i class="fa-solid fa-lock"></i> Secure checkout · SSL encrypted · your current fare session continues</div>';
    mainCol.insertBefore(intro,form);
    var title=document.createElement('div'); title.className='tnb-checkout-title'; title.innerHTML='<h2>Passenger Details</h2><span><i class="fa-solid fa-shield-halved me-1"></i> Secure checkout</span>'; form.insertBefore(title,form.firstChild);
    var panels=[0,1,2].map(function(i){var d=document.createElement('div');d.className='tnb-step-panel'+(i===0?' active':'');d.dataset.step=i;return d});
    var anchor=title.nextSibling; panels.forEach(function(p){form.insertBefore(p,anchor)});
    var children=Array.from(form.children).filter(function(el){return !el.classList.contains('tnb-step-panel')&&!el.classList.contains('tnb-checkout-title')});
    children.forEach(function(el){
      if(el.matches('input,textarea')||el.querySelector&&el.querySelector('input[type="hidden"]')===el.firstElementChild&&el.children.length===1)return;
      var h=directHeading(el), step=-1;
      if(/review your booking|flight detail|contact details|passenger information/.test(h))step=0;
      else if(/web check-in|cancellation protection/.test(h))step=1;
      else if(/payment and billing|payment information|billing information/.test(h))step=2;
      if(el.querySelector&&el.querySelector('#submitBtn')){step=2;el.classList.add('tnb-final-action')}
      if(step>=0)panels[step].appendChild(el);
    });
    // Anything visual left between title/panels and hidden controls: place in passenger step, except service textarea/hidden fields.
    Array.from(form.children).forEach(function(el){
      if(el===title||el.classList.contains('tnb-step-panel')||el.matches('input[type="hidden"],textarea[hidden]'))return;
      if(el.id==='service')return;
      if(el.tagName==='DIV' && !el.classList.contains('tnb-step-panel')) panels[0].appendChild(el);
    });
    var addActions=function(panel,prev,next,label){var bar=document.createElement('div');bar.className='tnb-wizard-actions';bar.innerHTML=(prev?'<button type="button" class="tnb-wizard-btn secondary" data-prev="'+prev+'"><i class="fa-solid fa-arrow-left me-2"></i>Back</button>':'<span></span>')+(next?'<button type="button" class="tnb-wizard-btn primary" data-next="'+next+'">'+label+'<i class="fa-solid fa-arrow-right ms-2"></i></button>':'');panel.appendChild(bar)};
    addActions(panels[0],null,1,'Continue to Add-ons'); addActions(panels[1],0,2,'Continue to Payment');
    function validatePanel(panel){
      var fields=Array.from(panel.querySelectorAll('input,select,textarea')).filter(function(x){return x.required&&!x.disabled&&x.type!=='hidden'});
      for(var i=0;i<fields.length;i++){if(!fields[i].checkValidity()){fields[i].reportValidity();fields[i].scrollIntoView({behavior:'smooth',block:'center'});return false}}return true;
    }
    function show(n){
      panels.forEach(function(p,i){p.classList.toggle('active',i===n)});document.querySelectorAll('.tnb-step-chip').forEach(function(c,i){c.classList.toggle('active',i===n);c.classList.toggle('done',i<n)});
      var headings=['Passenger Details','Enhance Your Trip','Secure Payment'];title.querySelector('h2').textContent=headings[n]; window.scrollTo({top:Math.max(0,intro.getBoundingClientRect().top+window.scrollY-90),behavior:'smooth'});
    }
    form.addEventListener('click',function(e){var n=e.target.closest('[data-next]'),p=e.target.closest('[data-prev]');if(n){var cur=parseInt(n.dataset.next,10)-1;if(validatePanel(panels[cur]))show(parseInt(n.dataset.next,10))}if(p)show(parseInt(p.dataset.prev,10))});
    // Make optional service cards feel like add-ons.
    panels[1].querySelectorAll('.card-header h6').forEach(function(h){var b=document.createElement('span');b.className='tnb-addon-badge';b.textContent='Optional';h.appendChild(b)});
    var submit=document.getElementById('submitBtn'); if(submit)submit.value='Confirm & Book Securely';
  }
  document.addEventListener('DOMContentLoaded',init);
})();
