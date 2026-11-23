# Security Checklist

Status legend: **Implemented** = present in code (not executed/tested in a running app); **Operator** = you must do it.

| Control | Status | Where / action |
|---|---|---|
| CSRF | Implemented | Laravel `web` middleware; all forms use `@csrf` |
| XSS | Implemented | Blade `{{ }}` everywhere; no `{!! !!}` in views (verify with `grep -rn "{!!" resources/views`) |
| SQL injection | Implemented | Eloquent/query builder bindings; no raw user input in SQL (review `whereRaw` before adding) |
| Password hashing | Implemented | `hashed` cast on User (bcrypt) |
| Auth: verification, reset, throttle | Implemented | `AuthController`, `VerificationController`; `throttle:login` 5/min per email+IP, `throttle:forms` 10/min per IP |
| Authorisation | Implemented | Spatie permissions in admin controllers, policies for portal, `EnsureBusinessAccess`, business global scope |
| Business data isolation | Implemented | `BelongsToBusiness`; writes validated with `canAccessBusiness` |
| Mass assignment | Implemented | `$fillable` on models; validated data only |
| File uploads | Implemented | image + mime whitelist (jpg/jpeg/png/webp), 5 MB, max dimensions; stored with generated names |
| Sessions | Implemented/Operator | DB sessions; set `SESSION_SECURE_COOKIE=true` under HTTPS |
| Security headers | Implemented | `SecurityHeaders` middleware (X-Frame-Options and related headers; **no Content-Security-Policy is set** – add one if desired) |
| Safe errors | Operator | `APP_DEBUG=false` in production |
| Secrets | Operator | `.env` never committed or in `public_html`; only `.env.example` ships |
| Stock/booking races | Implemented | transactions, row locks, unique slot constraint |
| Age-restricted sales | Implemented (notice + confirmation only) | see ADMIN_GUIDE |
| Dependency vulnerabilities | Operator | `composer audit`, `npm audit` periodically |

## Before go-live
- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `php artisan key:generate` run once on the server
- [ ] `SEED_DEMO_DATA=false`; **do not** run the demo seeder in production (demo password `DemoPass123` is public)
- [ ] Strong `ADMIN_PASSWORD` (or use the generated one) and change it after first login; remove/rename demo accounts if created
- [ ] HTTPS enforced, `SESSION_SECURE_COOKIE=true`, `APP_URL=https://…`
- [ ] `.env`, `vendor`, `storage`, `app` outside `public_html` (see deployment guide); verify `https://domain/.env` returns 404/403
- [ ] `storage` and `bootstrap/cache` writable (755/775) but not web-reachable
- [ ] Working SMTP and SPF/DKIM for the sending domain
- [ ] Database user has only that database's privileges; automated backups tested
- [ ] `composer audit` and `npm audit` clean or reviewed
- [ ] Run `php artisan test` and a manual role check: a Business Manager cannot open another business's records (try IDs in the URL)
- [ ] POPIA: privacy policy text reviewed by the business; data retention/export process defined

## Known gaps
No two-factor authentication, no CAPTCHA (rate limiting only), no malware scanning of uploads, no WAF. Add as needed.
