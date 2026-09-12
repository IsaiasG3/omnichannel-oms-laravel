<?php

namespace App\Providers;
use App\Domain\Orders\Models\Order;
use App\Policies\OrderPolicy;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use App\Domain\Orders\Events\OrderStatusChanged;
use App\Domain\Billing\Listeners\DispatchInvoiceGenerationOnPaid;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Laravel\Pulse\Facades\Pulse;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        Event::listen(OrderStatusChanged::class, DispatchInvoiceGenerationOnPaid::class);
        Gate::policy(Order::class, OrderPolicy::class);
         RateLimiter::for('purchase-attempts', function ($request) {
        return Limit::perMinute(100)->by($request->user()?->id ?: $request->ip());
        });
        RateLimiter::for('checkout', function ($request) {
        return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });
        Pulse::user(fn ($user) => [
        'name' => $user->name,
        'extra' => $user->email,
    ]);
    }
}
