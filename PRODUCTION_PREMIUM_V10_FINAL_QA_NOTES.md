# FaresHunt Production Premium V10 — Final QA Fixes

Consolidated from the completed V9 user QA pass.

- Mobile homepage calendar clarity fixed: calendar remains crisp while backdrop dims only the page.
- Checkout desktop spacing normalized across Passenger, Add-ons and Payment.
- Passenger DOB uses premium Flatpickr UI with quick month/year navigation and YYYY-MM-DD form payload.
- Booking support card now exposes click-to-call and WhatsApp using CRM settings, with working Fare Help / Checkout Help links.
- Web Check-In and Cancellation Protection no longer expose redundant “No thanks” buttons; Add to Booking acts as a reversible toggle.
- Selected add-ons render as clean line-item rows in Fare Breakdown; totals still come from the existing service calculation endpoint.
- Lightweight travel motif added to checkout/listing empty backgrounds without heavy image payloads.
- Top offer rotator interval reduced from 4.5s to 3.2s.
- Homepage Top 10 US Departure Airports section removed and replaced with Holiday Packages using existing deal/search/live-fare flow.
- Homepage visible deal headings aligned to Explore Our Best Fares / Within USA / International Deals / Holiday Packages / Last Minute Deals without changing the existing card/font system.
- Large Greek Island Escape feature added using an existing local lightweight image asset; no external image dependency.
- Slow live-search loader messaging becomes reassuring at 5s and 12s instead of feeling dead.
- Existing Flight API, FareLeaders, fare rules, One Way / Round Trip / Multi-City, booking/payment contracts remain untouched except presentation/service-summary HTML.
