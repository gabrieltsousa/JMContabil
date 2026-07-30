<?php

declare(strict_types=1);

namespace App\Application\Events;

final class ChargeWhatsAppFailed
{
    public function __construct(
        public readonly int $chargeId,
        public readonly string $error,
        public readonly int $attempt,
        public readonly bool $final = false,
    ) {
    }
}
