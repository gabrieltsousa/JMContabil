<?php

declare(strict_types=1);

namespace App\Infrastructure\WhatsApp\Providers;

use App\Infrastructure\WhatsApp\Contracts\WhatsAppProviderInterface;
use App\Infrastructure\WhatsApp\DTOs\PixChargeMessage;
use App\Infrastructure\WhatsApp\DTOs\WhatsAppSendResult;
use App\Infrastructure\WhatsApp\Exceptions\WhatsAppProviderNotConfiguredException;

/**
 * Stub base para providers futuros.
 * Garante que a troca de driver falhe de forma explícita até a implementação real.
 */
abstract class AbstractStubWhatsAppProvider implements WhatsAppProviderInterface
{
    abstract public function driver(): string;

    public function sendText(string $phone, string $message): WhatsAppSendResult
    {
        throw WhatsAppProviderNotConfiguredException::forDriver($this->driver());
    }

    public function sendPixCharge(string $phone, PixChargeMessage $payload): WhatsAppSendResult
    {
        throw WhatsAppProviderNotConfiguredException::forDriver($this->driver());
    }
}
