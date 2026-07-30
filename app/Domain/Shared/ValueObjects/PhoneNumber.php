<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

use App\Domain\Shared\Exceptions\InvalidValueObjectException;
use Stringable;

/**
 * Telefone brasileiro normalizado para E.164 (WhatsApp).
 * Aceita formatos com/sem máscara e normaliza para dígitos com DDI 55.
 */
final readonly class PhoneNumber implements Stringable
{
    private function __construct(
        private string $value,
    ) {
    }

    public static function from(string $raw): self
    {
        $digits = preg_replace('/\D+/', '', $raw) ?? '';

        if ($digits === '') {
            throw InvalidValueObjectException::withMessage('Telefone inválido: valor vazio.');
        }

        if (str_starts_with($digits, '55') && strlen($digits) >= 12) {
            $normalized = $digits;
        } elseif (strlen($digits) === 10 || strlen($digits) === 11) {
            $normalized = '55'.$digits;
        } else {
            throw InvalidValueObjectException::withMessage(
                'Telefone inválido: informe DDD + número (10 ou 11 dígitos).'
            );
        }

        $national = substr($normalized, 2);

        if (strlen($national) < 10 || strlen($national) > 11) {
            throw InvalidValueObjectException::withMessage(
                'Telefone inválido após normalização.'
            );
        }

        return new self($normalized);
    }

    public function value(): string
    {
        return $this->value;
    }

    /**
     * Formato esperado pela maioria das APIs WhatsApp (DDI + DDD + número).
     */
    public function whatsapp(): string
    {
        return $this->value;
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
