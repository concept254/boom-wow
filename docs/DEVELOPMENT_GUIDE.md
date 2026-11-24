# Development Guide

> **The code has not been run.** The environment that produced it had no network, so Composer/npm packages were never installed. Work through the first-run checklist below and fix anything it surfaces.

## 1. Install prerequisites
| Tool | Version | Notes |
|---|---|---|
| PHP | 8.3+ | extensions: mbstring, openssl, pdo_mysql, tokenizer, xml, ctype, json, fileinfo, gd (+ pdo_sqlite for tests) |
| Composer | 2.x | https://getcomposer.org |
| Node.js | 20+ (22 fine) | build-time only |
| MySQL / MariaDB | 8 / 10.6+ | or XAMPP/Laragon/Herd bundles |

Windows: Laragon or XAMPP provide PHP+MySQL; macOS: `brew install php composer node mysql`; Ubuntu: `sudo apt install php8.3-{cli,mbstring,xml,mysql,sqlite3,gd,curl,zip} composer nodejs npm mysql-server`.

## 2. Set up
```bash
cp .env.example .env
composer install
php artisan key:generate
mysql -u root -e "CREATE DATABASE boomwow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
# edit DB_USERNAME / DB_PASSWORD in .env
php artisan migrate --seed
php artisan storage:link
npm install && npm run build      # npm run dev for hot reload
php artisan serve
```

## 3. First-run verification checklist (never executed yet)
1. `composer install` – if Laravel 13 / `spatie/laravel-permission` constraint fails, check Packagist for the release that supports Laravel 13 and edit `composer.json` (`"spatie/laravel-permission": "^6.0 || ^7.0"` is a guess).
2. `php artisan migrate:fresh --seed` – should create all tables and seed the six businesses.
3. `npm install && npm run build` – if `laravel-vite-plugin`/`vite`/Tailwind 4 versions conflict, align them with the versions a fresh Laravel 13 skeleton ships (`composer create-project laravel/laravel` in a scratch folder and compare `package.json`, `vite.config.js`, `resources/css/app.css`).
4. `php artisan test` – run all; fix failures. Tests use SQLite in memory (`phpunit.xml`).
5. Click through: `/`, `/businesses/salon`, register → verify (see mail in `storage/logs/laravel.log`) → book → cart → checkout, then `/admin`.

## 3b. Updating an existing local install
```bash
# copy the updated files over the project, then:
composer install            # only if composer.json changed
php artisan migrate         # NOT migrate:fresh - keeps your data
php artisan db:seed --class=HomepageContentSeeder   # optional sample slides/adverts/team
php artisan optimize:clear
npm run build               # or keep `npm run dev` running
```

## 4. Conventions
* Colours only via brand tokens (`bg-brand-orange`, `text-brand-charcoal`…) defined in `resources/css/app.css`. Do not hardcode hex in views.
* Business-owned model ⇒ `use BelongsToBusiness`, `business_unit_id` column, add to `config/admin.php`.
* Stock changes only through `InventoryService`; booking creation only through `BookingService`; orders only through `OrderService`.
* Fonts: Sora (headings) + DM Sans (body), set in `resources/css/app.css` (`--font-display`, `--font-sans`) and loaded from fonts.bunny.net in both layouts.
* Blade gotcha: never use Alpine's `@error=`/`@can=`/`@auth=` shorthand in views - Blade treats them as directives. Use `x-on:error=` etc.
* Tailwind 4 gotcha: a class reused with `@apply` must be declared with `@utility` (see `btn`).
* New permission: add to `RolesAndPermissionsSeeder::MATRIX`, re-run `php artisan db:seed --class=RolesAndPermissionsSeeder`.

## 5. Add a new business
Admin → Business Units → Create (name, slug, contact, flags). It appears at `/businesses/{slug}` automatically; add services/products/staff/hours to it. No code.

## 6. Add an admin module
Create model + migration (+ `business_unit_id`), then add an entry in `config/admin.php` (label, model, permission prefix, columns, search, fields). Add the permissions to the seeder matrix and a link in `resources/views/layouts/admin.blade.php` if it should show in the menu.

## 7. Add a payment gateway
Implement `App\Services\Payments\PaymentGateway`, add a `match` arm for the new `PAYMENT_DRIVER` value in `AppServiceProvider`, put credentials in `.env` and `config/services.php` (never in code). Cart and order code stay unchanged.

## 8. Hero images
Drop `.jpg/.jpeg/.png/.webp` into `public/images/hero/` (or upload via Admin → Media Library with "use as hero"). The file list is cached for `HERO_CACHE_SECONDS` (default 3600); after adding files by FTP run `php artisan cache:clear` or wait. If the folder is empty the app uses `HERO_FALLBACK_URL`.

## 9. Scheduled work
`php artisan schedule:run` (cron every minute) runs `bookings:send-reminders` hourly and `inventory:check-low-stock` daily. Locally: `php artisan schedule:work`.

## 10. Testing
`php artisan test` (or `composer test`). Feature tests cover authentication, registration, roles, business management, isolation, services/products, stock, orders, booking availability/double-booking, enquiries, CMS, super-admin access.
