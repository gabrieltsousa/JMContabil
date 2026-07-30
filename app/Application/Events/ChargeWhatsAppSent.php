<?php

declare(strict_types=1);

namespace App\Application\Events;

final class ChargeWhatsAppSent
{
    public function __construct(
        public readonly int $chargeId,
        public readonly string $message,
        public readonly int $attempt,
    ) {
    }
}
