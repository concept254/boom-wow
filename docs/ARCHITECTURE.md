# Architecture

> Written alongside code that has **not been executed** (no network in the build environment). Paths and class names are accurate to the source; behaviour is untested.

## Shape
A single Laravel 13 monolith, server-rendered with Blade. Alpine.js adds small interactions; Chart.js draws dashboard charts; Vite compiles assets locally (no Node needed in production).

```
Public site ─┐
Portal      ─┼─ Controllers ─ Services ─ Eloquent models ─ MySQL
Admin       ─┘      │             └─ Notifications / Payments interface
                 Form validation, Policies/permissions, middleware
```

## Multi-business model
* `business_units` is the root entity. Every business-owned table has `business_unit_id` (services, products, staff, bookings, events, properties, portfolio items, hours, closures, …).
* Storefront `/businesses/{slug}` renders one generic Blade view (`public/business.blade.php`) from database rows; sections (services, packages, products, properties, events, portfolio, staff) appear when the business has such rows, and the `accepts_bookings` / `sells_products` flags control the Book and shop actions. Adding a business = adding a row in **Admin → Business Units**.
* **Isolation:** trait `App\Models\Concerns\BelongsToBusiness` registers a global scope. When the request passed the `business` middleware (`EnsureBusinessAccess`, admin routes only) the scope restricts queries to `User::businessIds()` for everyone except Super Admin. Public and customer queries are unscoped by design. The generic admin controller also forces/validates `business_unit_id` against the user's assignments on write.

## Authorisation
* Spatie Laravel Permission: roles `Super Admin`, `Business Manager`, `Staff`, `Customer`; permissions like `products.update` (matrix in `database/seeders/RolesAndPermissionsSeeder.php`).
* `Gate::before` grants Super Admin everything. Admin controllers check `$user->can('<module>.<ability>')` server-side and portal controllers use policies; menu hiding is cosmetic only.
* Customers are not back-office users: `EnsureBusinessAccess` returns 403 for them. `BookingPolicy` and `OrderPolicy` (auto-discovered, checked with `Gate::authorize`) limit portal records to their owner; lists load through `$user->bookings()` / `$user->orders()`.
* `business_user` pivot assigns managers/staff to businesses.

## Admin CRUD
`config/admin.php` declares each module (model, columns, searchable fields, form fields with rules, permission prefix). `Admin\ResourceController` serves `/admin/r/{resource}` index/create/edit/update/delete with search, filtering, sorting, pagination and delete confirmation. Add a module by adding a config entry (+ model/migration), no new controller.
Bespoke screens: orders, bookings, inventory/stock movements, reports, customers, users, roles, media, notifications, activity logs, settings.

## Bookings (`BookingService`)
1. Slot generation from business hours (or staff hours via `StaffAvailability`), minus `BusinessClosure` holidays and `StaffTimeOff`, minus existing bookings, honouring service duration, notice and horizon (`config/boomwow.php`).
2. `book()` runs in a DB transaction, re-checks availability, and writes `BookingSlot` rows. A **UNIQUE(resource_key, slot_start)** constraint makes a racing second insert fail, so double-booking is prevented even if two requests pass the check simultaneously.
3. Status changes go through `changeStatus()` and are recorded in `booking_status_histories`.

## Shop
* `CartService` – session cart, multi-business.
* `OrderService::place()` – one transaction: validates stock, age-restricted rules, creates order + items (each keeps `business_unit_id`), deducts stock through `InventoryService`, creates a `payments` row. Insufficient stock throws and rolls everything back.
* `InventoryService` – only writer of stock: locks the inventory row, refuses negative stock, writes `stock_movements` (previous qty, change, new qty, reason, user, timestamp).
* **Payments:** `PaymentGateway` interface + `ManualPaymentGateway` (manual / cash_on_collection / pending). Choose via `PAYMENT_DRIVER`. To add PayFast/PayGate/Ozow implement the interface, add a `match` arm in `AppServiceProvider`, add credentials to `.env`. Cart/order code does not change.

## Content
`cms_pages`, `menus`, `blog_posts`, `testimonials`, `promotions`, `faqs`, `settings`, `media` – all statuses draft/published/archived where relevant. Homepage sections read these tables. Hero images: `HeroImageService` scans `public/images/hero` (cached for `HERO_CACHE_SECONDS`), picks one at random, falls back to `HERO_FALLBACK_URL`, and the view degrades to a charcoal background if the remote image fails.

## Shared-hosting constraints honoured
Database sessions/cache=file/queue=sync, mail via SMTP (or log), local `public` disk, cron `schedule:run`, precompiled assets, Laravel kept outside `public_html` via `deploy/public_html/index.php` (calls `usePublicPath`).

## Database
Migrations: `database/migrations` (framework, permissions, business, commerce, content). Foreign keys, indexes, unique constraints, timestamps and soft deletes on business records. See migrations for the authoritative schema.
