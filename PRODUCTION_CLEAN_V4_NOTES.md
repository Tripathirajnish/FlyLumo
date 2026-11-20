# FaresHunt Production Clean V4

## Preserved
- Existing FareLeaders controller/service/views and FareLeaders route flow.
- Existing FlightController and supplier/API behavior.
- Existing fare-rule controller/views and fare calculation logic.
- Existing website One Way / Round Trip / Multi-City booking flow.
- Manual flight booking and invoice module.

## Added
- Multi-product Travel Desk for Flights, Hotels, Car Rentals, Holiday Packages and Custom Trips.
- Manual quote builder with line items, pricing, agent assignment and payment/status fields.
- Integration Hub foundation for future meta partners, supplier APIs, widgets, tracking and AI providers.
- Premium product launcher on Smart Dashboard.
- Unified Inter typography across CRM and website-facing design system.

## Removed / cleaned
- Root-level historical CRM/website patch package directories that were not runtime dependencies.
- Root PowerShell patch/install scripts.
- Timestamp backup/bak artifacts in runtime code folders.
- Floating website support/chat launcher.
- Visible fare-session timer/countdown launcher logic.

## Setup
Copy your working `.env` if needed, then run:

```powershell
php artisan migrate
php artisan optimize:clear
php artisan serve
```

## Important architecture boundary
The Integration Hub stores provider configuration securely. Completely new supplier protocols still require a one-time adapter; after an adapter exists, credentials/mapping/status can be managed from CRM.
