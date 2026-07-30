<?php

declare(strict_types=1);

namespace App\Domain\Shared\Exceptions;

use DomainException;

final class InvalidValueObjectException extends DomainException
{
    public static function withMessage(string $message): self
    {
        return new self($message);
    }
}
