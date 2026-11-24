# Boom & Wow Holdings

A multi-business website and management platform for **Boom & Wow Holdings**. One Laravel application serves the public corporate site, online storefronts, a customer portal and a back-office admin for all five businesses.

![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?logo=php&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4-06B6D4?logo=tailwindcss&logoColor=white)
![Alpine.js](https://img.shields.io/badge/Alpine.js-3-8BC0D0?logo=alpinedotjs&logoColor=white)

## Businesses

- Salon
- Liquor Store
- IT Consultants
- Property Management
- Events Management

New businesses can be added from the admin as database records. No new code or pages are needed.

## Features

**Public website**
- Hero slideshow, featured stores carousel, services, specials and adverts
- About page (team, values, FAQs) and Contact page (map, directions, opening hours, enquiry form)
- Newsletter subscription, WhatsApp button, store search

**Customers**
- Customer portal with bookings and order history
- Online booking with a unique slot constraint to prevent double bookings
- Shop with cart, orders and stock checks

**Admin**
- Config-driven CRUD for about 20 modules (`config/admin.php`)
- Roles and permissions with per-business isolation (Spatie Laravel Permission)
- Dashboard, reports and CSV export
- Image upload with an option to remove the current image
- Email notifications and scheduled jobs

## Tech stack

Laravel 13, PHP 8.3+, Blade, Tailwind CSS 4, Alpine.js, Vite, Chart.js, MySQL/MariaDB or SQLite.

## Getting started

**Requirements:** PHP 8.3+, Composer 2, Node 20+, and MySQL/MariaDB or SQLite.

```bash
git clone https://github.com/concept254/boom-wow.git
cd boom-wow

cp .env.example .env
composer install
php artisan key:generate
```

Configure the database in `.env`. For SQLite:

```env
DB_CONNECTION=sqlite
```

Then create the file `database/database.sqlite`. For MySQL, set the `DB_*` values instead.

```bash
php artisan migrate --seed
php artisan storage:link
npm install
npm run build        # or npm run dev while developing
php artisan serve
```

Open <http://localhost:8000>. With Laravel Herd the site is served at `http://boom-wow.test`.

### Admin login

- URL: `/login`, then `/admin`
- Email: the `ADMIN_EMAIL` value in `.env`
- Password: `ADMIN_PASSWORD` from `.env`. If it is blank, a random password is printed once when you run `php artisan db:seed`.

### Demo data

Set `SEED_DEMO_DATA=true` in `.env` to seed example customers, bookings, orders, slides and adverts. Demo accounts use the password `DemoPass123`. **Always set `SEED_DEMO_DATA=false` in production.**

## Testing

```bash
php artisan test
```

## Deployment (cPanel shared hosting)

The project is built to run on shared hosting. See [`docs/SHARED_HOSTING_DEPLOYMENT.md`](docs/SHARED_HOSTING_DEPLOYMENT.md) for the full steps. In short:

1. Run `npm run build` and `composer install --no-dev --optimize-autoloader` locally.
2. Upload the project and point the web root to `public` (or use the files in `deploy/public_html`).
3. Create the production `.env` with `APP_ENV=production`, `APP_DEBUG=false` and your database details.
4. Run `php artisan migrate --force` and `php artisan storage:link`.
5. Add a cron job for the scheduler:
   ```
   * * * * * php /path/to/project/artisan schedule:run >> /dev/null 2>&1
   ```

## Documentation

| Guide | Description |
|---|---|
| [Architecture](docs/ARCHITECTURE.md) | How the application is structured |
| [Admin guide](docs/ADMIN_GUIDE.md) | Managing content, businesses and orders |
| [Development guide](docs/DEVELOPMENT_GUIDE.md) | Local setup, conventions and checks |
| [Shared hosting deployment](docs/SHARED_HOSTING_DEPLOYMENT.md) | Deploying to cPanel |
| [Security checklist](docs/SECURITY_CHECKLIST.md) | Pre-launch security review |

## Brand

| Colour | Hex |
|---|---|
| Orange | `#F45422` |
| Charcoal | `#201E1F` |
| White | `#FFFFFF` |
| Off-white | `#F5F5F4` |
| Graphite | `#393536` |
| Dark orange | `#D94316` |

Fonts: Sora (headings) and DM Sans (body). The logo is at `public/images/logo.jpg`.

## Project structure

```
app/            Controllers, models, services, policies, notifications
config/         App config, including admin.php (admin modules)
database/       Migrations and seeders
deploy/         cPanel deployment files
docs/           Project documentation
resources/      Blade views, CSS and JS
routes/         Web routes and scheduled jobs
tests/          Feature tests
```

## Licence

Proprietary. All rights reserved by Boom & Wow Holdings.
