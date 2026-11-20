# FaresHunt Production Premium V7 — Mobile-First Homepage Pass

This build starts the mobile-first customer website upgrade while preserving V6 backend behavior.

## Changed in this pass

- Added final mobile-first stylesheet: `public/assets/css/fareshunt-mobile-first-v7.css`.
- Loaded the stylesheet last in `resources/views/layout.blade.php` so it can safely override accumulated legacy mobile CSS without deleting existing logic.
- Mobile header is reduced to a 64px sticky row with FaresHunt logo, direct phone action and hamburger menu.
- Promotional/trust strips above the header are hidden on phones so flight search appears much earlier on screen.
- Homepage hero typography and spacing are reduced for phone screens.
- Search card is optimized for touch use with 40–52px controls and iOS-safe 16px form text.
- FROM and TO remain full width; swap stays between them.
- Departure and Return are side-by-side on standard phones, automatically becoming a single departure field for One Way.
- Passenger and Cabin controls share a row on standard phones; Search remains a full-width primary CTA.
- Passenger selector uses a viewport-safe bottom-sheet layout.
- Flatpickr calendar uses a mobile bottom-sheet layout.
- Airport autocomplete is constrained to the phone viewport.
- Multi-City remains stacked and touch-friendly.
- Featured Deals and airport sections are constrained to one safe mobile column.
- Very narrow phones below 360px automatically fall back to single-column dates/passenger/cabin controls.

## Intentionally unchanged

- Flight search POST route and request field names.
- One Way / Round Trip / Multi-City JavaScript hooks.
- Airport autocomplete data flow.
- Current flight API integration.
- FareLeaders integration.
- Fare Rule Engine.
- Booking and payment business logic.
- CRM behavior.

## Next recommended pass

Flight Listing → Passenger / Booking → Payment mobile responsiveness after homepage direction is approved.
