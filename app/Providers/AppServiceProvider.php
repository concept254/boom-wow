<?php

namespace App\Providers;

use App\Services\Payments\ManualPaymentGateway;
use App\Services\Payments\PaymentGateway;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Swap this binding (or add a driver match) when a real gateway is integrated.
        $this->app->bind(PaymentGateway::class, function () {
            return match (config('boomwow.payment.driver')) {
                default => new ManualPaymentGateway(config('boomwow.payment.driver')),
            };
        });
    }

    public function boot(): void
    {
        // Branded pagination (the framework's default relies on Tailwind classes this build does not generate).
        Paginator::defaultView('vendor.pagination.brand');
        Paginator::defaultSimpleView('vendor.pagination.brand');

        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        Gate::before(fn ($user) => $user->hasRole('Super Admin') ? true : null);

        RateLimiter::for('login', fn (Request $r) => Limit::perMinute(5)->by(strtolower((string) $r->input('email')).'|'.$r->ip()));
        RateLimiter::for('forms', fn (Request $r) => Limit::perMinute(10)->by($r->ip()));
    }
}
