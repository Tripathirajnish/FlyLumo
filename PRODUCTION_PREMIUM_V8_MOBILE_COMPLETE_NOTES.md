# FaresHunt Production Premium V8 — Full Mobile Customer Flow

This is a **full project build**, based on Production Premium V6/V7. It is not a patch-only folder.

## Mobile-first scope included

- Homepage / hero / flight search mobile pass from V7.
- Flight Listing mobile layout, compact modify-search control, mobile filter actions, responsive result cards, mobile price/select area, expanded-flight-detail overflow protection and Multi-City mobile handling.
- Passenger / Booking / Add-ons / Payment / Billing mobile layouts already integrated in the active `flight_booking.blade.php`, plus a final shared hardening layer.
- Mobile-safe form typography (16px inputs to avoid iOS zoom), touch targets, viewport overflow protection and responsive tables.
- Small-phone fallbacks down through ~320–350px widths.
- Tablet and desktop behaviour retained.

## Protected and intentionally unchanged

- Existing flight search routes and request field names.
- Current Flight API integration.
- FareLeaders integration.
- Fare Rule Engine.
- One Way / Round Trip / Multi-City business logic.
- Booking creation and payment business logic.
- Manual Booking / Invoice / CRM backend behaviour.

## Main V8 hardening file

`public/assets/css/fareshunt-mobile-complete-v8.css`

It is loaded last by `resources/views/layout.blade.php` so it can resolve legacy responsive conflicts without deleting working legacy code.

## Recommended regression widths

320, 360, 375, 390, 412, 430, 768, 1024 and desktop.
