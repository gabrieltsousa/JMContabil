<?php

declare(strict_types=1);

namespace App\Infrastructure\WhatsApp\Providers;

use App\Domain\Settings\Enums\WhatsAppProvider as WhatsAppProviderEnum;

final class ZApiWhatsAppProvider extends AbstractStubWhatsAppProvider
{
    public function driver(): string
    {
        return WhatsAppProviderEnum::ZApi->value;
    }
}
