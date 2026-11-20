(function(){
  'use strict';
  if(typeof window.flatpickr!=='function') return;

  const PUBLIC_DATE_RE=/(depart|departure|depature|return|travel|journey|flight|checkin|checkout)/i;
  const DOB_RE=/(dob|birth|birthday)/i;
  const FUTURE_RE=/(passport.*exp|expiry|expiration)/i;

  function clampCalendar(instance){
    if(!instance || !instance.calendarContainer) return;
    const cal=instance.calendarContainer;
    requestAnimationFrame(function(){
      const r=cal.getBoundingClientRect();
      const pad=12;
      let left=parseFloat(cal.style.left||'0');
      if(r.right>window.innerWidth-pad && Number.isFinite(left)) left-=r.right-(window.innerWidth-pad);
      if(r.left<pad && Number.isFinite(left)) left+=pad-r.left;
      if(Number.isFinite(left)) cal.style.left=Math.round(left)+'px';
    });
  }

  function syncDisabled(input, instance){
    if(!instance || !instance.altInput) return;
    instance.altInput.disabled=input.disabled;
    const obs=new MutationObserver(function(){instance.altInput.disabled=input.disabled;});
    obs.observe(input,{attributes:true,attributeFilter:['disabled']});
  }

  function optionsFor(input){
    const key=((input.name||'')+' '+(input.id||'')+' '+(input.dataset.dateRole||'')).toLowerCase();
    const isCRM=document.body.classList.contains('crm-worldclass');
    const dateFormat=input.dataset.dateFormat || 'Y-m-d';
    let minDate=input.getAttribute('min')||null;
    let maxDate=input.getAttribute('max')||null;

    if(!isCRM && PUBLIC_DATE_RE.test(key) && !DOB_RE.test(key)) minDate=minDate||'today';
    if(DOB_RE.test(key)){maxDate=maxDate||'today';minDate=null;}
    if(FUTURE_RE.test(key) && !DOB_RE.test(key)) minDate=minDate||'today';

    return {
      dateFormat:dateFormat,
      altInput:true,
      altFormat:input.dataset.altFormat || 'M j, Y',
      altInputClass:(input.className||'form-control')+' tnb-date-alt',
      allowInput:false,
      clickOpens:true,
      disableMobile:true,
      animate:true,
      monthSelectorType:'dropdown',
      minDate:minDate,
      maxDate:maxDate,
      defaultDate:input.value||null,
      position:'auto',
      onOpen:[function(_,__,instance){clampCalendar(instance);}]
    };
  }

  function enhance(input){
    if(!input || input.dataset.premiumCalendar==='off') return null;
    if(input._flatpickr){
      input._flatpickr.config.onOpen.push(function(_,__,instance){clampCalendar(instance);});
      syncDisabled(input,input._flatpickr);
      return input._flatpickr;
    }
    input.classList.add('tnb-premium-date');
    const inst=window.flatpickr(input,optionsFor(input));
    syncDisabled(input,inst);
    return inst;
  }

  function findInputs(){
    const selectors=[
      'input[type="date"]',
      'input[data-premium-date]',
      '#departDate1','#returnDate1',
      'input[name*="departDate"]','input[name*="returnDate"]'
    ];
    document.querySelectorAll(selectors.join(',')).forEach(enhance);
  }

  function linkPair(depart,ret){
    if(!depart || !ret) return;
    const d=enhance(depart), r=enhance(ret);
    if(!d || !r) return;
    const old=d.config.onChange.slice();
    d.config.onChange=[function(dates){
      if(dates[0]){
        r.set('minDate',dates[0]);
        if(r.selectedDates[0] && r.selectedDates[0]<dates[0]) r.clear();
      }
    }].concat(old);
  }

  function linkCommonPairs(){
    linkPair(document.getElementById('depature_date'),document.getElementById('return_date'));
    linkPair(document.getElementById('departDate1'),document.getElementById('returnDate1'));
    document.querySelectorAll('[data-date-pair]').forEach(function(dep){
      const target=dep.getAttribute('data-date-pair');
      if(target) linkPair(dep,document.querySelector(target));
    });
  }

  function boot(){
    findInputs();
    linkCommonPairs();
    window.addEventListener('resize',function(){
      document.querySelectorAll('.flatpickr-calendar.open').forEach(function(cal){
        const input=document.querySelector('.flatpickr-input.active');
        if(input && input._flatpickr) clampCalendar(input._flatpickr);
      });
    },{passive:true});
  }

  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',boot);
  else boot();
})();
