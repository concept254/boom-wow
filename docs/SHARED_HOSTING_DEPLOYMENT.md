# Shared Hosting (cPanel) Deployment

> **Untested.** These steps follow standard cPanel practice and this project's files, but the app has never been run end-to-end and no cPanel account was available. Do a trial deployment on a staging subdomain first. Replace `ACCOUNT` with your cPanel username and `example.co.za` with your domain.

## 0. Check the host supports it
Laravel 13 needs **PHP 8.3+** with: mbstring, openssl, pdo_mysql, tokenizer, xml, ctype, json, fileinfo, gd, curl, zip. In cPanel: **Select PHP Version** (or **MultiPHP Manager**) → choose 8.3 → tick the extensions. Ask the host if 8.3 is unavailable – a lower PHP will not work. You also need MySQL/MariaDB, cron, and (ideally) SSH/Terminal. Node.js is **not** needed on the server.

Target layout (private app outside the web root):
```
/home/ACCOUNT/boom-wow/        <- the Laravel app (app/, vendor/, storage/, .env …)
/home/ACCOUNT/public_html/     <- index.php, .htaccess, build/, images/, favicon.ico, storage -> link
```

## 1. Prepare the project locally
```bash
composer install --no-dev --optimize-autoloader
npm install && npm run build            # creates public/build
php artisan key:generate --show         # copy the base64:… value for .env later
```
Create a ZIP of the project **excluding** `node_modules`, `.env`, `.git`, `storage/logs/*`, `tests` (optional) and `public/build` (this goes to public_html separately). Do **not** exclude `vendor` unless the server has Composer + SSH (section 2).

## 2. Composer dependencies on the server
* **With SSH/Terminal:** upload the ZIP without `vendor` and run, inside `/home/ACCOUNT/boom-wow`:
  `composer install --no-dev --optimize-autoloader` (if `composer` is missing: `php composer.phar install …`, downloading composer.phar from getcomposer.org).
* **Without Composer/SSH:** upload the local `vendor/` folder inside the ZIP (built with the same PHP major version, step 1). See section 16.

## 3. Frontend assets
Built locally (step 1). Upload the contents of `public/build/` to `/home/ACCOUNT/public_html/build/` (must contain `manifest.json`). Also upload `public/images/` (logo, hero folder) and `public/favicon.ico`.

## 4. Create the MySQL database (cPanel)
1. **MySQL® Database Wizard** → create database (e.g. `ACCOUNT_boomwow`).
2. Create a user with a strong password; grant **ALL PRIVILEGES** on that database only.
3. Note the prefixed names; collation `utf8mb4_unicode_ci` is fine.

## 5. Upload application files
**File Manager** or FTP/SFTP: upload the ZIP to `/home/ACCOUNT/`, extract so the app is in `/home/ACCOUNT/boom-wow/`. Folder permissions: directories `755`, files `644`.

## 6. Configure public_html
Copy **`deploy/public_html/index.php`** and **`deploy/public_html/.htaccess`** into `/home/ACCOUNT/public_html/` (show hidden files in File Manager to see `.htaccess`). Together with `build/`, `images/`, `favicon.ico` this is everything public.

## 7. Edit index.php
`public_html/index.php` loads the app from `dirname(__DIR__).'/boom-wow'`. If the app folder is named differently, or the site is an add-on domain/subdomain (`public_html/shop` → use `dirname(__DIR__, 2).'/boom-wow'`), edit the `$appPath` line. The file calls `$app->usePublicPath(__DIR__)` so images, `build/` and hero images are resolved in `public_html`.

## 8. Configure .env
In `/home/ACCOUNT/boom-wow/` copy `.env.example` to `.env` and edit:
```
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:…            # from step 1
APP_URL=https://example.co.za
DB_HOST=localhost
DB_DATABASE=ACCOUNT_boomwow
DB_USERNAME=ACCOUNT_boomuser
DB_PASSWORD=…
SESSION_SECURE_COOKIE=true
SEED_DEMO_DATA=false
ADMIN_EMAIL=you@yourdomain.co.za
ADMIN_PASSWORD=             # empty => random password printed once during seeding
```
`.env` must never be inside `public_html`. Permission `640` or `600`.

## 9. Storage permissions and public uploads
```bash
cd /home/ACCOUNT/boom-wow
chmod -R 775 storage bootstrap/cache
```
Uploaded images live in `storage/app/public` and must be reachable at `/storage/...`. Because `php artisan storage:link` would link into the *private* `public/` folder, create the link into `public_html` instead:
```bash
ln -s /home/ACCOUNT/boom-wow/storage/app/public /home/ACCOUNT/public_html/storage
```
**If symlinks are disabled:** edit `config/filesystems.php`, `public` disk: `'root' => public_path('uploads')`, `'url' => rtrim(env('APP_URL'), '/').'/uploads'`, and create `public_html/uploads` (`755`).

## 10. Run migrations
SSH/Terminal (cPanel → **Terminal**):
```bash
cd /home/ACCOUNT/boom-wow
php artisan migrate --force
php artisan db:seed --force          # roles, five businesses, content, admin user
php artisan config:cache && php artisan route:cache && php artisan view:cache
```
The admin password is printed once if `ADMIN_PASSWORD` was empty – save it. If `php` is the wrong version use the full path, e.g. `/opt/cpanel/ea-php83/root/usr/bin/php`. After changing `.env` later run `php artisan config:clear`.

## 11. Enable HTTPS
cPanel → **SSL/TLS Status** → *Run AutoSSL* for the domain. Then force HTTPS by adding at the top of `public_html/.htaccess`:
```
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```
Confirm `APP_URL` uses `https://` and `SESSION_SECURE_COOKIE=true`.

## 12. Configure SMTP
cPanel → **Email Accounts** → create `noreply@example.co.za`. In `.env`:
```
MAIL_MAILER=smtp
MAIL_HOST=mail.example.co.za
MAIL_PORT=465              # or 587 with MAIL_ENCRYPTION=tls
MAIL_USERNAME=noreply@example.co.za
MAIL_PASSWORD=…
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="noreply@example.co.za"
```
Then `php artisan config:clear` and test by registering a customer. Add SPF/DKIM (cPanel → **Email Deliverability**).

## 13. Cron jobs
cPanel → **Cron Jobs** → add, every minute:
```
* * * * * /opt/cpanel/ea-php83/root/usr/bin/php /home/ACCOUNT/boom-wow/artisan schedule:run >> /dev/null 2>&1
```
This runs booking reminders (hourly) and low-stock checks (daily 07:00). Queues are synchronous (`QUEUE_CONNECTION=sync`), so no worker is needed.

## 14. Backups
* cPanel **Backup**/JetBackup: full + home + MySQL backups; download off-site.
* Nightly database dump via cron: `0 2 * * * mysqldump -u ACCOUNT_boomuser -p'…' ACCOUNT_boomwow | gzip > /home/ACCOUNT/backups/db-$(date +\%F).sql.gz` (create `backups/` outside public_html; keep the password out of shared screens).
* Back up `/home/ACCOUNT/boom-wow/storage/app/public` (uploads) and `.env`.
* Test a restore at least once.

## 15. Troubleshooting
| Symptom | Fix |
|---|---|
| 500 error, blank page | Check `storage/logs/laravel.log`; temporarily `APP_DEBUG=true` (revert!); check PHP version/extensions; `chmod -R 775 storage bootstrap/cache` |
| "Vite manifest not found" | `public_html/build/manifest.json` missing – upload the built `build/` folder |
| Images/uploads 404 | symlink `public_html/storage` missing or broken (step 9) |
| `vendor/autoload.php` not found | `$appPath` wrong in `public_html/index.php`, or vendor not uploaded |
| "No application encryption key" | set `APP_KEY` in `.env`, `php artisan config:clear` |
| Class/Config changes ignored | `php artisan config:clear && php artisan view:clear` |
| Emails not sent | wrong SMTP host/port; try 587/tls; check `laravel.log`; port blocked by host |
| 403/404 on every route | `.htaccess` missing in public_html or mod_rewrite off |
| Wrong PHP version in cron/SSH | use the full `ea-php83` path |
| Hero image not changing | cached for an hour – `php artisan cache:clear` |
| `.env` reachable in browser | the app is inside public_html – move it out immediately and rotate all secrets |

## 16. Alternative: no SSH and no Composer on the server
1. Locally: `composer install --no-dev --optimize-autoloader` (use PHP 8.3), `npm run build`.
2. Set up a **local** database, `.env` with `SEED_DEMO_DATA=false`, then `php artisan migrate:fresh --seed` (note the admin password printed).
3. Export the local DB with `mysqldump boomwow > boomwow.sql` (or phpMyAdmin → Export) and import it into the cPanel database via **phpMyAdmin → Import**. (Use gzip if over the size limit.)
4. Upload the whole app **including `vendor/`** by ZIP + File Manager "Extract"; set up public_html, `.env`, permissions as above.
5. Symlinks: without Terminal use a PHP one-off in File Manager, e.g. a temporary file `public_html/link.php` containing `<?php symlink('/home/ACCOUNT/boom-wow/storage/app/public', __DIR__.'/storage'); echo 'ok';` – open it once in the browser, then **delete it**. Or use the `uploads` option in step 9.
6. Cron (section 13) works from the cPanel UI without SSH.
Never leave helper scripts or a database-running web route in place after use.
