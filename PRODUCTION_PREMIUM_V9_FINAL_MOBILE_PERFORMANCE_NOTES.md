# FaresHunt Production Premium V9 — Final Mobile & Performance Pass

## Scope
V9 is a consolidated full-project pass on top of V8. It does not replace the existing flight API, FareLeaders integration, fare-rule engine, search contracts, or booking/payment business logic.

## Mobile UX
- Rebuilt the phone search composition as a compact mobile-native flow.
- From/To remain full width; swap occupies almost no vertical space.
- Departure/Return stay together on mainstream phones and safely stack on very narrow phones.
- Passenger/Cabin stay together on mainstream phones.
- Airport suggestions are positioned against the active field instead of floating at the top of the viewport.
- Flatpickr is constrained to a compact mobile bottom sheet.
- Passenger selector remains a safe bottom sheet.
- Homepage browse-heavy grids become horizontal swipe rails on phones, preserving all CMS-driven cards while dramatically reducing page length.
- Premium cabin columns become swipeable on phones.
- Listing Sort/Filter actions remain sticky and touch-friendly.
- Checkout pages retain the V8 structure with smaller mobile vertical footprint.
- Dedicated tablet/iPad rules added for the 761–1024px range.

## Performance
- Disabled the legacy full-page preloader so initial content can paint immediately.
- Added `content-visibility:auto` for deep homepage sections where supported.
- Converted homepage deal/guide background artwork to IntersectionObserver-based lazy loading.
- Existing live fare lazy loader remains unchanged.
- Added non-invasive flight-listing timing telemetry to `storage/logs/laravel.log`:
  - `FARESHUNT_LISTING_PERFORMANCE`
  - markup time
  - upstream API time
  - result rearrange time
  - total controller time
  - result count
- Searches exceeding the 5-second target create `FARESHUNT_LISTING_SLOW` warnings. This identifies whether any miss is upstream API latency or local processing instead of guessing.

## Reliability
- Search CTA receives duplicate-submit protection without changing the existing submit/loader flow.
- Horizontal browse rails remain keyboard-focusable.
- Existing routes, click handlers, CMS card click flow, live route fare loader, One Way/Round Trip/Multi-City behaviour, passenger flow, add-ons, payment, Manual Booking and Invoice are not intentionally altered.

## Acceptance targets
- Phone widths: 320, 360/375, 390, 412/430px.
- Tablet/iPad: 768–1024px.
- Laptop/desktop: no regression from V8.
- No horizontal page overflow.
- No dead primary CTA.
- Search/listing timing is measurable against the 5-second target.
