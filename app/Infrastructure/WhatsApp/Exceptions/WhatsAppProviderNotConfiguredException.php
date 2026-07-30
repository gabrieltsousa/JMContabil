<?php

declare(strict_types=1);

namespace App\Infrastructure\WhatsApp\Exceptions;

use RuntimeException;

final class WhatsAppProviderNotConfiguredException extends RuntimeException
{
    public static function forDriver(string $driver): self
    {
        return new self(
            sprintf(
                'Provider WhatsApp "%s" ainda não está implementado. Use "fake" no MVP ou implemente o adapter em Infrastructure/WhatsApp/Providers.',
                $driver
            )
        );
    }
}
