<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Services\MessageTemplateService;
use App\Application\Services\MessageTemplateServiceInterface;
use App\Domain\Charge\Contracts\ChargeDeliveryRepositoryInterface;
use App\Domain\Charge\Contracts\ChargeRepositoryInterface;
use App\Domain\Customer\Contracts\CustomerRepositoryInterface;
use App\Domain\Settings\Contracts\SettingRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentChargeDeliveryRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentChargeRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentCustomerRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentSettingRepository;
use App\Infrastructure\WhatsApp\Contracts\WhatsAppProviderInterface;
use App\Infrastructure\WhatsApp\WhatsAppProviderResolver;
use Illuminate\Support\ServiceProvider;

/**
 * Bindings da camada de domínio e infraestrutura.
 */
final class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            MessageTemplateServiceInterface::class,
            MessageTemplateService::class
        );

        $this->app->singleton(WhatsAppProviderResolver::class);

        $this->app->singleton(
            WhatsAppProviderInterface::class,
            fn ($app): WhatsAppProviderInterface => $app
                ->make(WhatsAppProviderResolver::class)
                ->resolve()
        );

        $this->app->bind(CustomerRepositoryInterface::class, EloquentCustomerRepository::class);
        $this->app->bind(ChargeRepositoryInterface::class, EloquentChargeRepository::class);
        $this->app->bind(ChargeDeliveryRepositoryInterface::class, EloquentChargeDeliveryRepository::class);
        $this->app->bind(SettingRepositoryInterface::class, EloquentSettingRepository::class);

        $this->app->singleton(\App\Application\Services\CustomerService::class);
        $this->app->singleton(\App\Application\Services\ChargeService::class);
        $this->app->singleton(\App\Application\Services\ChargeNotificationService::class);
        $this->app->singleton(\App\Application\Services\DailyChargeService::class);
        $this->app->singleton(\App\Application\Services\DashboardService::class);
        $this->app->singleton(\App\Application\Services\SettingsService::class);
    }

    public function boot(): void
    {
        //
    }
}
