(function () {
  'use strict';

  // Visual/UX-only enhancements. Existing API requests and booking/fare logic are untouched.
  document.addEventListener('DOMContentLoaded', function () {
    var cardInput = document.getElementById('creditcard_no');
    if (!cardInput) return;

    // Checkout has its own card-brand + validation UI in flight_booking.blade.php.
    // Avoid rendering a second brand badge/status from this global enhancement.
    if (document.getElementById('form4')) return;

    var parent = cardInput.parentElement;
    if (parent && !parent.classList.contains('tnb-card-field')) {
      parent.classList.add('tnb-card-field');
    }

    var brand = parent && parent.querySelector('.tnb-card-brand');
    if (!brand && parent) {
      brand = document.createElement('span');
      brand.className = 'tnb-card-brand';
      brand.setAttribute('aria-hidden', 'true');
      parent.appendChild(brand);
    }

    var status = parent && parent.querySelector('.tnb-inline-status');
    if (!status && parent) {
      status = document.createElement('div');
      status.className = 'tnb-inline-status';
      status.setAttribute('aria-live', 'polite');
      parent.appendChild(status);
    }

    function digits(value) { return (value || '').replace(/\D/g, ''); }

    function detectBrand(number) {
      if (/^4/.test(number)) return 'VISA';
      if (/^(5[1-5]|2(2[2-9]|[3-6][0-9]|7[0-1]|720))/.test(number)) return 'MASTERCARD';
      if (/^3[47]/.test(number)) return 'AMEX';
      if (/^(6011|65|64[4-9])/.test(number)) return 'DISCOVER';
      if (/^(30[0-5]|36|38|39)/.test(number)) return 'DINERS';
      if (/^(35)/.test(number)) return 'JCB';
      if (/^62/.test(number)) return 'UNIONPAY';
      if (/^(50|5[6-9]|6[0-9])/.test(number)) return 'MAESTRO';
      return '';
    }

    function format(number) {
      // Keep the existing backend field name/value contract; formatting is only for the input display.
      if (/^3[47]/.test(number)) return number.replace(/(.{4})/g, '$1 ').trim().slice(0, 17);
      return number.replace(/(.{4})/g, '$1 ').trim().slice(0, 23);
    }

    function update() {
      var raw = digits(cardInput.value);
      var cardBrand = detectBrand(raw);
      cardInput.value = format(raw);
      if (brand) brand.textContent = cardBrand;
      if (status) {
        status.className = 'tnb-inline-status';
        status.textContent = '';
        if (raw.length >= 4 && !cardBrand) {
          status.classList.add('warn');
          status.textContent = 'Card type will appear when the number is recognized.';
        } else if (cardBrand && raw.length >= 12) {
          status.classList.add('ok');
          status.textContent = cardBrand === 'AMEX' ? 'American Express detected' : cardBrand + ' detected';
        }
      }
    }

    cardInput.addEventListener('input', update);
    cardInput.addEventListener('paste', function () { setTimeout(update, 0); });
    update();
  });
})();
