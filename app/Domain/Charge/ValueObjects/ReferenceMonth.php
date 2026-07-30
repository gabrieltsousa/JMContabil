<?php

declare(strict_types=1);

namespace App\Domain\Charge\ValueObjects;

use App\Domain\Shared\Exceptions\InvalidValueObjectException;
use DateTimeImmutable;
use Stringable;

/**
 * Competência da cobrança no formato YYYY-MM.
 */
final readonly class ReferenceMonth implements Stringable
{
    private function __construct(
        private string $value,
    ) {
    }

    public static function from(string $yearMonth): self
    {
        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $yearMonth) !== 1) {
            throw InvalidValueObjectException::withMessage(
                'Competência inválida. Use o formato YYYY-MM.'
            );
        }

        return new self($yearMonth);
    }

    public static function fromDate(DateTimeImmutable $date): self
    {
        return new self($date->format('Y-m'));
    }

    public static function current(?DateTimeImmutable $now = null): self
    {
        return self::fromDate($now ?? new DateTimeImmutable('now'));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function year(): int
    {
        return (int) substr($this->value, 0, 4);
    }

    public function month(): int
    {
        return (int) substr($this->value, 5, 2);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
