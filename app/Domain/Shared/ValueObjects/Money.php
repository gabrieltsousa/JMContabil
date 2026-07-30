<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

use App\Domain\Shared\Exceptions\InvalidValueObjectException;
use Stringable;

/**
 * Valor monetário em centavos (inteiro) para evitar float.
 */
final readonly class Money implements Stringable
{
    private function __construct(
        private int $amountInCents,
    ) {
    }

    public static function fromCents(int $cents): self
    {
        if ($cents < 0) {
            throw InvalidValueObjectException::withMessage('Valor monetário não pode ser negativo.');
        }

        return new self($cents);
    }

    public static function fromDecimal(float|string $amount): self
    {
        if (is_string($amount)) {
            $normalized = str_replace(['R$', ' ', '.'], '', $amount);
            $normalized = str_replace(',', '.', $normalized);
            $amount = (float) $normalized;
        }

        if ($amount < 0) {
            throw InvalidValueObjectException::withMessage('Valor monetário não pode ser negativo.');
        }

        return new self((int) round($amount * 100));
    }

    public function amountInCents(): int
    {
        return $this->amountInCents;
    }

    public function toDecimal(): float
    {
        return $this->amountInCents / 100;
    }

    public function formatBrl(): string
    {
        return 'R$ '.number_format($this->toDecimal(), 2, ',', '.');
    }

    public function equals(self $other): bool
    {
        return $this->amountInCents === $other->amountInCents;
    }

    public function __toString(): string
    {
        return $this->formatBrl();
    }
}
