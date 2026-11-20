# FaresHunt Production Premium V6

- Premium light CRM workspace with navy collapsible sidebar.
- Sidebar hide/show persists in localStorage; mobile drawer supported.
- Global CRM search goes to Booking Management.
- Smart Dashboard redesigned with real booking/revenue trend, lead funnel, product mix, payment-method mix, recent bookings/leads, payment overview, top routes/airlines and agent performance.
- Public CRM booking references standardized to FaresHunt `FH{id}` display without changing database primary keys.
- New invoice numbers use `FH-INV-YYYY-######`. Existing invoice records are not mutated.
- Customer-facing/CRM-visible legacy Travel N Breakz naming removed where found. Internal legacy CSS class/file names such as `tnb-*` are intentionally retained because they are technical dependencies and are not user-visible.
- Existing FareLeaders service/controller, current flight API flow and Fare Rule Engine are not replaced.
- Obvious route/view backup artifacts removed from the packaged production tree.
