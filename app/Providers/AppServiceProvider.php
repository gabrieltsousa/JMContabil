<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Actions\Charge\DispatchDailyChargesAction;
use App\Application\Events\ChargeWhatsAppFailed;
use App\Application\Events\ChargeWhatsAppSent;
use App\Application\Listeners\LogChargeWhatsAppOutcome;
use App\Http\Policies\ChargePolicy;
use App\Http\Policies\CustomerPolicy;
use App\Infrastructure\Persistence\Eloquent\Models\Charge;
use App\Infrastructure\Persistence\Eloquent\Models\Customer;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(DispatchDailyChargesAction::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $timezone = (string) config('jmcontabil.timezone', 'America/Sao_Paulo');

        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);

        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(Charge::class, ChargePolicy::class);

        Event::listen(
            ChargeWhatsAppSent::class,
            [LogChargeWhatsAppOutcome::class, 'handleSent']
        );

        Event::listen(
            ChargeWhatsAppFailed::class,
            [LogChargeWhatsAppOutcome::class, 'handleFailed']
        );
    }
}
