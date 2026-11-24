# Administrator Guide

Sign in at `/login` with a back-office account; you land on `/admin`. The menu shows only what your role may use, and the server enforces the same rules.

## Roles
| Role | Can do |
|---|---|
| Super Admin | Everything, all businesses, users, roles, CMS, settings, logs |
| Business Manager | Only assigned businesses: services, products, stock, staff, bookings, orders, enquiries, events/properties, reports |
| Staff | Assigned business: view/update bookings, view orders, inventory, enquiries |
| Customer | Portal only (own bookings, orders, enquiries, profile) |

Assign a user to businesses: **Users** → edit → pick role and businesses. Review the matrix under **Roles & Permissions**.

## Daily tasks
* **Bookings** – filter by status/business; confirm, complete, cancel or mark no-show. Customer reschedule requests show as "reschedule requested"; contact the customer, then cancel and ask them to rebook or arrange manually.
* **Orders** – change status (pending → processing → ready → completed/cancelled). Cancelling returns stock. Use "Mark paid" after manual/EFT/cash payment.
* **Inventory / Stock Movements** – receive stock, adjust or deduct with a reason; the reorder level drives low-stock alerts. Every change keeps previous qty, change, new qty, reason, user, date. Negative stock is refused.
* **Enquiries** – set status new / in progress / resolved / closed.

## Content
* **Business Units** – logo, cover, description, contact, address, social links, flags (accepts bookings, sells products, age restricted). Status draft/published/archived.
* **Business Hours / Closures** – opening hours per day and holidays; staff working hours (**Staff Availability**) and time off (**Staff Time Off**). A staff member with no availability rows cannot be booked online.
* **Services, Service Packages, Products, Categories, Events, Properties, Portfolio** – full create/edit/delete with search, filters, sorting, pagination.
* **CMS Pages, Blog & News, Testimonials, Promotions, FAQs, Menus** – homepage sections, About/Privacy/Terms text, navigation, footer links. Settings hold hero headline/subtitle, social links and SEO defaults.
* **Media Library** – upload JPG/PNG/WEBP (max 5 MB). Tick "use as hero" to add to the hero rotation.

## Home page & marketing (Super Admin)
All of this is managed under the admin menu and appears on the public site straight away.
| Menu item | What it controls |
|---|---|
| **Hero Slides** | The slideshow at the top of the home page. Each slide has a type (general / store / service / product / announcement / promotion), caption, heading, sub-heading, an uploaded image (or an external image URL), two call-to-action buttons, an order number and optional show-from / show-until dates. Draft or expired slides are hidden. With no slides published, a default slide from *Settings* (hero headline/copy) and a random photo from `public/images/hero/` is shown. |
| **Business Units → "Show in home page store carousel"** | Untick to hide a business from the Featured Stores carousel. Logo and cover image come from the business record. |
| **Services / Products** | Tick *Featured* to show them in the Featured Services / Products sections. |
| **Promotions & Adverts** | Adverts and specials. *Where it appears*: **Top announcement bar** (one line, dismissible), **Home specials cards**, **Home wide banner**, or **Inner pages banner** (shown at the bottom of Services, Businesses, Products, Events, Properties, FAQs, About, Contact and storefront pages). Choose badge text (e.g. SPECIAL, -20%), button text and link, colour theme, order and start/end dates. |
| **Team & Management** | People shown on the About page, split into *Management* and *Team* tabs (photo, title, short bio, optional email/LinkedIn). |
| **Newsletter Subscribers** | Emails collected by the subscribe forms (home page, footer, news page). View, edit or delete. Export is not built in yet. |
| **FAQs / Testimonials** | Shown on the home, About and Contact pages. |
| **Settings** | Address, phone, email, **map latitude/longitude** (used for the Contact page map and directions link), **WhatsApp number** (digits with country code, e.g. `27821234567` - shows a floating WhatsApp button and links), and an opening-hours note shown on the home page. |

Links in buttons may be a full `https://` address, a site path such as `/products`, `mailto:`, `tel:` or `#section`. Anything else (for example `javascript:`) is ignored for safety.
Opening hours shown on the home and Contact pages come from **Opening Hours** for each business.

## Liquor store compliance (South Africa) – operator's responsibility
The app provides: age-restricted flag per business/product, an 18+ confirmation at checkout, a responsible-sales notice (Settings → `liquor_notice`) and a required delivery address for delivery orders. It does **not** verify age or licences. Before trading, confirm with a legal adviser/your provincial liquor authority:
* a valid retail liquor licence for the premises (National Liquor Act 59 of 2003 and provincial liquor Acts), displayed and matching the trading name;
* no sale to persons under 18, and ID checks at hand-over (collection or delivery);
* permitted trading days/hours under your licence and municipal by-laws;
* whether **online sales and delivery** are allowed under your licence conditions (varies by province);
* no sale to intoxicated persons; consumption-off-premises conditions;
* record keeping, and consumer protection / POPIA obligations for customer data;
* advertising restrictions (Liquor Products Act / ARA code).
Set `LIQUOR_AGE_GATE=true` and `LIQUOR_MIN_AGE=18` in `.env`.

## Reports
**Reports** → choose report (sales by business/product/date, inventory levels, stock movements, low stock, booking stats, customer registrations, enquiry stats) → **Export CSV**. Managers see only their businesses.

## Notifications
Emails: registration/verification, booking confirmation and reminders, order confirmation/updates, enquiry received, low stock. All are also stored in-app. Configure SMTP in `.env`; with `MAIL_MAILER=log` they are written to `storage/logs/laravel.log`.

## Adding a new business (no code needed)
1. **Business Units → Add new**. Only the **Name** is required; the URL slug is built from it and 7 days of default opening hours are created for you (edit them under **Opening Hours**).
2. **Category label** is free text shown on the site (e.g. "Clothing store"). **Enquiry form style** picks which enquiry form the business page shows (general, project, viewing, event, support).
3. Tick **Accepts bookings** and/or **Sells products**, upload a logo and cover image (both optional), set **Status** to Published, and tick **Show in home page store carousel** to feature it.
4. Add its services/products, and create a manager login under Users if it needs its own back-office staff. The home page count ("Six businesses") and the About page list update by themselves.

## Page headers
**Page Headers** controls the banner at the top of each public page: optional title and sub title overrides, an optional background image (upload or URL), and **Use fallback** (when ticked and no image is set, the site-wide *default* row's image or an online placeholder is used; untick for a plain dark banner). A dark overlay keeps the text readable. Set `PAGE_HEADER_FALLBACK_URL=` (empty) in `.env` to disable the online placeholder completely.

## Brands (logo carousel)
**Brands** feeds the scrolling logo strip on the home, About, Our businesses and Products pages, and per-business pages (brands linked to that business). Logo is optional (the brand name is shown without it). Untick **Show on the home page and About page** to keep a brand off those two pages. The strip hides itself when there are no brands.

## Hero image & logo
Add images to `public/images/hero/` or via Media Library. The logo is a transparent SVG (`public/images/logo.svg` for light surfaces, `logo-dark.svg` for the dark header/footer), traced from the client's print JPG with the tagline removed. Favicons: `public/favicon.ico`, `images/favicon.svg`, `apple-touch-icon.png`. If the client later supplies an official vector, replace those files keeping the same names. The original JPG stays as `logo.jpg` and is only a fallback.
