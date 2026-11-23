<?php

return [
    // Initial super admin created by AdminUserSeeder. Set these in .env; nothing is hardcoded.
    'admin' => [
        'email' => env('ADMIN_EMAIL', 'admin@example.com'),
        'name' => env('ADMIN_NAME', 'Site Admin'),
        'password' => env('ADMIN_PASSWORD'), // empty => a random one-time password is generated and printed
    ],

    // Demo data (customers, bookings, orders, enquiries). Turn off in production.
    'seed_demo' => (bool) env('SEED_DEMO_DATA', true),

    'hero' => [
        'directory' => 'images/hero',
        'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
        'fallback_url' => env('HERO_FALLBACK_URL', 'https://picsum.photos/seed/{seed}/1920/1080'),
        'randomize_fallback' => true, // swap the /seed/<name>/ part for a random one on every request
        'cache_seconds' => (int) env('HERO_CACHE_SECONDS', 3600),
    ],

    'logo' => [
        'light' => 'images/logo.svg',        // vector logo (transparent) for light backgrounds
        'dark' => 'images/logo-dark.svg',    // vector logo for dark surfaces (header, footer)
        'mark_light' => 'images/logo-mark.svg',      // emblem only (no name line), for tight spaces
        'mark_dark' => 'images/logo-mark-dark.svg',
        'fallback' => 'images/logo.jpg',     // original client JPG, used only if the SVGs are missing
    ],

    'booking' => [
        'slot_minutes' => 30,
        'max_days_ahead' => 60,
        'min_notice_hours' => 2,
    ],

    'liquor' => [
        'age_gate' => (bool) env('LIQUOR_AGE_GATE', true),
        'min_age' => (int) env('LIQUOR_MIN_AGE', 18),
    ],

    // Payment driver recorded on payments: manual | cash_on_collection | pending.
    // To add PayFast/PayGate/Ozow: implement App\Services\Payments\PaymentGateway and
    // add a match arm in AppServiceProvider. Credentials belong in .env only.
    'payment' => [
        'driver' => env('PAYMENT_DRIVER', 'manual'),
    ],
];
