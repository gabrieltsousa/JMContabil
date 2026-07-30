<?php

declare(strict_types=1);

namespace App\Application\Exceptions;

use RuntimeException;
use Throwable;

final class WhatsAppSendException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $chargeId,
        public readonly int $attempt,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
