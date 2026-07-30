<?php

declare(strict_types=1);

namespace App\Domain\Customer\ValueObjects;

use App\Domain\Shared\Exceptions\InvalidValueObjectException;
use Stringable;

/**
 * Chave PIX (MVP: chave aleatória UUID ou texto livre validado).
 * Preparado para evoluir para EVP, CPF, CNPJ, e-mail, telefone.
 */
final readonly class PixKey implements Stringable
{
    private function __construct(
        private string $value,
    ) {
    }

    public static function from(string $raw): self
    {
        $value = trim($raw);

        if ($value === '') {
            throw InvalidValueObjectException::withMessage('Chave PIX não pode ser vazia.');
        }

        if (mb_strlen($value) > 77) {
            throw InvalidValueObjectException::withMessage(
                'Chave PIX excede o tamanho máximo permitido (77 caracteres).'
            );
        }

        if (! self::isValidFormat($value)) {
            throw InvalidValueObjectException::withMessage('Formato de chave PIX inválido.');
        }

        return new self($value);
    }

    private static function isValidFormat(string $value): bool
    {
        // UUID (chave aleatória / EVP)
        if (preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $value
        ) === 1) {
            return true;
        }

        // E-mail
        if (filter_var($value, FILTER_VALIDATE_EMAIL) !== false) {
            return true;
        }

        // Telefone (+5511...) ou apenas dígitos
        $digits = preg_replace('/\D+/', '', $value) ?? '';
        if (strlen($digits) >= 10 && strlen($digits) <= 13) {
            return true;
        }

        // CPF (11) / CNPJ (14)
        if (preg_match('/^\d{11}$/', $digits) === 1 || preg_match('/^\d{14}$/', $digits) === 1) {
            return true;
        }

        return false;
    }

    public function value(): string
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
