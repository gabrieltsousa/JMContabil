<?php

declare(strict_types=1);

namespace App\Domain\Customer\ValueObjects;

use App\Domain\Shared\Exceptions\InvalidValueObjectException;
use Stringable;

/**
 * Dia de vencimento mensal (1–28 para evitar ambiguidade em fevereiro).
 * Dias 29–31 são rejeitados no MVP por previsibilidade operacional.
 */
final readonly class DueDay implements Stringable
{
    private const int MIN = 1;

    private const int MAX = 28;

    private function __construct(
        private int $day,
    ) {
    }

    public static function from(int $day): self
    {
        if ($day < self::MIN || $day > self::MAX) {
            throw InvalidValueObjectException::withMessage(
                sprintf('Dia de vencimento deve estar entre %d e %d.', self::MIN, self::MAX)
            );
        }

        return new self($day);
    }

    public function value(): int
    {
        return $this->day;
    }

    public function matches(int $dayOfMonth): bool
    {
        return $this->day === $dayOfMonth;
    }

    public function equals(self $other): bool
    {
        return $this->day === $other->day;
    }

    public function __toString(): string
    {
        return (string) $this->day;
    }
}
