<?php

declare(strict_types=1);

namespace App\Infrastructure\WhatsApp;

use App\Domain\Settings\Enums\WhatsAppProvider as WhatsAppProviderEnum;
use App\Infrastructure\WhatsApp\Contracts\WhatsAppProviderInterface;
use App\Infrastructure\WhatsApp\Providers\Dialog360WhatsAppProvider;
use App\Infrastructure\WhatsApp\Providers\EvolutionWhatsAppProvider;
use App\Infrastructure\WhatsApp\Providers\FakeWhatsAppProvider;
use App\Infrastructure\WhatsApp\Providers\MetaCloudWhatsAppProvider;
use App\Infrastructure\WhatsApp\Providers\UltraMsgWhatsAppProvider;
use App\Infrastructure\WhatsApp\Providers\ZApiWhatsAppProvider;
use Illuminate\Contracts\Container\Container;

/**
 * Resolve o adapter WhatsApp a partir do enum/driver.
 * Permite trocar provider sem alterar Domain/Application.
 */
final class WhatsAppProviderResolver
{
    public function __construct(
        private readonly Container $container,
    ) {
    }

    public function resolve(?string $driver = null): WhatsAppProviderInterface
    {
        $driver ??= (string) config('jmcontabil.whatsapp.default_provider', 'fake');

        $enum = WhatsAppProviderEnum::tryFrom($driver) ?? WhatsAppProviderEnum::Fake;

        return match ($enum) {
            WhatsAppProviderEnum::Fake => $this->container->make(FakeWhatsAppProvider::class),
            WhatsAppProviderEnum::Evolution => $this->container->make(EvolutionWhatsAppProvider::class),
            WhatsAppProviderEnum::ZApi => $this->container->make(ZApiWhatsAppProvider::class),
            WhatsAppProviderEnum::MetaCloud => $this->container->make(MetaCloudWhatsAppProvider::class),
            WhatsAppProviderEnum::UltraMsg => $this->container->make(UltraMsgWhatsAppProvider::class),
            WhatsAppProviderEnum::Dialog360 => $this->container->make(Dialog360WhatsAppProvider::class),
        };
    }

    public function resolveFromEnum(WhatsAppProviderEnum $provider): WhatsAppProviderInterface
    {
        return $this->resolve($provider->value);
    }
}
