<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

// ---- Public website -------------------------------------------------------
Route::controller(PublicController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/about-us', 'about')->name('about');
    Route::get('/businesses', 'businesses')->name('businesses');
    Route::get('/businesses/{business:slug}', 'business')->name('business');
    Route::get('/services', 'services')->name('services');
    Route::get('/services/{service:slug}', 'service')->name('service');
    Route::get('/events', 'events')->name('events');
    Route::get('/events/{event:slug}', 'event')->name('event');
    Route::get('/properties', 'properties')->name('properties');
    Route::get('/properties/{property:slug}', 'property')->name('property');
    Route::get('/search', 'search')->name('search');
    Route::get('/contact', 'contact')->name('contact');
    Route::post('/subscribe', 'subscribe')->middleware('throttle:forms')->name('subscribe');
    Route::post('/enquiries', 'storeEnquiry')->middleware('throttle:forms')->name('enquiries.store');
    Route::get('/faqs', 'faqs')->name('faqs');
    Route::get('/privacy-policy', 'privacy')->name('privacy');
    Route::get('/terms-and-conditions', 'terms')->name('terms');
    Route::get('/news', 'blog')->name('blog');
    Route::get('/news/{slug}', 'blogPost')->name('blog.post');
});

// ---- Shop (browse + cart are public; checkout needs an account) ------------
Route::controller(ShopController::class)->group(function () {
    Route::get('/products', 'index')->name('products');
    Route::get('/products/{business:slug}/{slug}', 'show')->name('product');
    Route::get('/cart', 'cart')->name('cart');
    Route::post('/cart/add/{product}', 'add')->name('cart.add');
    Route::patch('/cart/{product}', 'update')->name('cart.update');
    Route::delete('/cart/{product}', 'remove')->name('cart.remove');
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/checkout', 'checkout')->name('checkout');
        Route::post('/checkout', 'place')->middleware('throttle:forms')->name('checkout.place');
    });
});

Route::post('/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])->middleware(['auth', 'throttle:forms'])->name('reviews.store');

// ---- Authentication ----------------------------------------------------------
Route::middleware('guest')->controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLogin')->name('login');
    Route::post('/login', 'login')->middleware('throttle:login');
    Route::get('/register', 'showRegister')->name('register');
    Route::post('/register', 'register')->middleware('throttle:forms');
    Route::get('/forgot-password', 'showForgot')->name('password.request');
    Route::post('/forgot-password', 'sendResetLink')->middleware('throttle:forms')->name('password.email');
    Route::get('/reset-password/{token}', 'showReset')->name('password.reset');
    Route::post('/reset-password', 'reset')->name('password.update');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->controller(VerificationController::class)->group(function () {
    Route::get('/email/verify', 'notice')->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', 'verify')->middleware('signed')->name('verification.verify');
    Route::post('/email/verification-notification', 'resend')->middleware('throttle:6,1')->name('verification.send');
});

// ---- Customer portal -----------------------------------------------------------
Route::middleware(['auth', 'verified'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/', [PortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/bookings', [PortalController::class, 'bookings'])->name('bookings');
    Route::get('/bookings/new', [BookingController::class, 'create'])->name('bookings.new');
    Route::get('/bookings/slots', [BookingController::class, 'slots'])->name('bookings.slots');
    Route::post('/bookings', [BookingController::class, 'store'])->middleware('throttle:forms')->name('bookings.store');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/bookings/{booking}/reschedule', [BookingController::class, 'reschedule'])->name('bookings.reschedule');
    Route::get('/orders', [PortalController::class, 'orders'])->name('orders');
    Route::get('/orders/{order}', [PortalController::class, 'order'])->name('orders.show');
    Route::get('/settings', [PortalController::class, 'settings'])->name('settings');
    Route::put('/settings', [PortalController::class, 'updateSettings'])->name('settings.update');
    Route::post('/notifications/read', [PortalController::class, 'markNotificationsRead'])->name('notifications.read');
});

// ---- Administration (back-office roles only; permissions enforced in controllers) ----
Route::middleware(['auth', 'verified', 'business'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    Route::get('/orders', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}', [Admin\OrderController::class, 'update'])->name('orders.update');
    Route::post('/orders/{order}/paid', [Admin\OrderController::class, 'markPaid'])->name('orders.paid');

    Route::get('/bookings', [Admin\BookingController::class, 'index'])->name('bookings.index');
    Route::patch('/bookings/{booking}', [Admin\BookingController::class, 'update'])->name('bookings.update');

    Route::get('/inventory', [Admin\InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/stock-movements', [Admin\InventoryController::class, 'movements'])->name('inventory.movements');
    Route::post('/inventory/{product}/adjust', [Admin\InventoryController::class, 'adjust'])->name('inventory.adjust');

    Route::get('/reports', [Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{report}', [Admin\ReportController::class, 'show'])->name('reports.show');

    Route::controller(Admin\SystemController::class)->group(function () {
        Route::get('/customers', 'customers')->name('customers');
        Route::get('/users', 'users')->name('users');
        Route::post('/users', 'storeUser')->name('users.store');
        Route::patch('/users/{user}', 'updateUser')->name('users.update');
        Route::get('/roles', 'roles')->name('roles');
        Route::patch('/roles/{role}', 'updateRole')->name('roles.update');
        Route::get('/media', 'media')->name('media');
        Route::post('/media', 'uploadMedia')->name('media.upload');
        Route::delete('/media/{media}', 'deleteMedia')->name('media.delete');
        Route::get('/notifications', 'notifications')->name('notifications');
        Route::get('/activity-logs', 'logs')->name('logs');
        Route::get('/settings', 'settings')->name('settings');
        Route::put('/settings', 'updateSettings')->name('settings.update');
    });

    // Generic CRUD modules from config/admin.php
    Route::controller(Admin\ResourceController::class)->prefix('r/{resource}')->name('resource.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });
});
