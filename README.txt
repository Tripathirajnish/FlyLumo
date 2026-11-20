DESKTOP PERFORMANCE — STEP 1-3 (27 Oct 2026)   [#4 JS defer jaan-boojh kar NAHI kiya — neeche wajah]
=====================================================================================
3 hisse, har hissa alag se rollback ho sakta hai. Search form / booking flow / HTML markup / JS — kuch nahi badla.

HISSA 1 — Font Awesome homepage subset  (#1 critical path)
  public/assets/webfonts/fa-solid-900-home.woff2 (4220), fa-regular-400-home.woff2 (1624), fa-brands-400-home.woff2 (1152)  NAYI
  public/assets/css/fontawesome.css (113775), public/assets/css/critical/fontawesome.css (4881)                              overwrite
  Homepage: fa-solid 147 KB + fa-brands 108 KB -> 4 KB + 1 KB. Baaki pages par jo icon chahiye, poora font apne-aap aata hai.
  font-display:block waisa hi. Lab: 1738 elements x 5 widths = 0 farak; full-page pixel farak 0.

HISSA 2 — Hero image chhoti  (#2 LCP = hero background image)
  public/assets/img/fareshunt-premium-hero-bg-q90.webp   161516 -> 88960 bytes (asli PNG se WebP q80, same 1718x916, SAME naam)
  Naam same hai -> preload / CMS / HTML kuch nahi badla. 2x zoom par bhi farak nahi dikhta (hero par dark overlay hai).

HISSA 3 — Neeche ke sections baad me render (#3 Speed Index / main-thread), SIRF desktop >=992px
  resources/views/index.blade.php  239805 -> 240350  (head me 1 <style id="fh-below-fold-cv"> + comment, aur kuch nahi)
  Deals / airlines / why / guides / alerts sections ka layout+paint browser tab karta hai jab wo screen ke paas aayein.
  Lab (1350 + 1920px, poora scroll): har element ki position/size 0 farak, lazy images 19/19 load, CLS same,
  "#fdLastMinute" jaise anchor same jagah, JS error 0. Mobile par ye rule lagta hi nahi.
  Dhyan: Playwright jaise "full page screenshot" tools (jo scroll nahi karte) me neeche ke sections khali dikh sakte hain —
  asli browser me scroll karte hi sab dikhta hai. GoFullPage jaisa scroll karne wala extension theek rahega.

LAB — Lighthouse DESKTOP (PSI jaisa), har version 5 run, median:
                         score  LCP    SpeedIdx  FCP    TBT   (5 runs ke scores)
  Abhi live (A)           95    1.33s  1.06s     0.87s   2ms  97 93 96 95 95
  + Hissa 1 (B)           97    1.15s  0.89s     0.60s  93ms  85 95 98 98 97
  + Hissa 1+2 (C)         97    1.08s  0.86s     0.61s 109ms  88 97 98 99 96
  + Hissa 1+2+3 (D)       98    1.09s  0.85s     0.60s  14ms  97 99 97 98 98   <- yahi zip
  (Lab ke absolute numbers PSI se behtar aate hain; farak/direction dekho. D me sabse kharab run bhi 97.)

#4 (jQuery/popper/bootstrap/flatpickr defer) KYUN NAHI:
  Homepage ke footer scripts page parse hote hi flatpickr('#depature_date',...) chalate hain. flatpickr defer kiya to
  library baad me aayegi -> date picker / booking toot jayega. Ye scripts page ke END me hain (render-blocking nahi),
  trace me jQuery sirf ~42ms. Fayda kam, booking todne ka risk zyada.

-------------------------------------------------------------------------------------
1) CHECK (sab match hone chahiye, warna upload MAT karo — mujhe batao)
cd /var/www/fareshunt-release-20260829/travelnbreakz-final
md5sum public/assets/css/fontawesome.css public/assets/css/critical/fontawesome.css public/assets/img/fareshunt-premium-hero-bg-q90.webp resources/views/index.blade.php
#  5222e06b77a1692fa2520a219840e6be  fontawesome.css
#  2900b37359b8095de12aed32970dc6b2  critical/fontawesome.css
#  097775368e1ac841d504f34ef44fcd8f  fareshunt-premium-hero-bg-q90.webp
#  1772cd16ef45d915e0f07280e4e9e0a9  index.blade.php

2) BACKUP
cp public/assets/css/fontawesome.css public/assets/css/fontawesome.css.before-perf14-20261027
cp public/assets/css/critical/fontawesome.css public/assets/css/critical/fontawesome.css.before-perf14-20261027
cp public/assets/img/fareshunt-premium-hero-bg-q90.webp public/assets/img/fareshunt-premium-hero-bg-q90.webp.before-perf14-20261027
cp resources/views/index.blade.php resources/views/index.blade.php.before-perf14-20261027

3) UPLOAD (FileZilla) — isi order me:
  a) 3 *-home.woff2 -> public/assets/webfonts/        (purani fa-*.woff2/.ttf MAT hatana)
  b) fontawesome.css -> public/assets/css/ ; critical/fontawesome.css -> public/assets/css/critical/
  c) fareshunt-premium-hero-bg-q90.webp -> public/assets/img/
  d) index.blade.php -> resources/views/
php artisan view:clear

4) CHECK
ls -l public/assets/css/fontawesome.css public/assets/css/critical/fontawesome.css public/assets/img/fareshunt-premium-hero-bg-q90.webp resources/views/index.blade.php
#  113775 / 4881 / 88960 / 240350
grep -c "fh-fa-subset" public/assets/css/fontawesome.css           # 1
grep -c "fh-below-fold-cv" resources/views/index.blade.php         # 2
curl -sI https://fareshunt.com/assets/webfonts/fa-solid-900-home.woff2 | head -1    # 200
Browser Incognito + Ctrl+Shift+R (desktop):
  - hero image same dikhe; saare icons pehle jaise
  - page neeche tak scroll karo: deals, airlines, why, guides, newsletter, footer sab normal
  - ek search karo (loader + results), date picker khol ke dekho
  - Console: performance.getEntriesByType('resource').filter(r=>/webfonts\/fa-/.test(r.name)).map(r=>r.name.split('/').pop())
    -> fa-solid-900.woff2 aur fa-brands-400.woff2 NAHI hone chahiye
  - ek results / checkout / CRM page ke icons dekh lo
PSI desktop 2-3 baar chalao.

5) ROLLBACK (jo hissa wapas karna ho sirf wahi)
Hissa 1: cp public/assets/css/fontawesome.css.before-perf14-20261027 public/assets/css/fontawesome.css
         cp public/assets/css/critical/fontawesome.css.before-perf14-20261027 public/assets/css/critical/fontawesome.css
Hissa 2: cp public/assets/img/fareshunt-premium-hero-bg-q90.webp.before-perf14-20261027 public/assets/img/fareshunt-premium-hero-bg-q90.webp
Hissa 3: cp resources/views/index.blade.php.before-perf14-20261027 resources/views/index.blade.php
phir: php artisan view:clear
