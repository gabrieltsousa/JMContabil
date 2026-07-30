<?php

declare(strict_types=1);

namespace App\Application\DTOs\Customer;

use App\Application\DTOs\DataTransferObject;
use App\Domain\Customer\Enums\CustomerStatus;
use App\Domain\Customer\ValueObjects\DueDay;
use App\Domain\Customer\ValueObjects\PixKey;
use App\Domain\Shared\ValueObjects\Money;
use App\Domain\Shared\ValueObjects\PhoneNumber;

/**
 * DTO de atualização parcial de cliente.
 * Campos null = não alterar (exceto quando explicitamente enviados via fromArray com chave presente).
 */
final readonly class UpdateCustomerData implements DataTransferObject
{
    /**
     * @param  array<string, true>  $presentKeys
     */
    public function __construct(
        public ?string $name = null,
        public ?PhoneNumber $phone = null,
        public ?string $email = null,
        public ?PixKey $pixKey = null,
        public ?Money $monthlyValue = null,
        public ?DueDay $dueDay = null,
        public ?CustomerStatus $status = null,
        public ?int $officeId = null,
        private array $presentKeys = [],
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $presentKeys = [];

        foreach (array_keys($data) as $key) {
            $presentKeys[(string) $key] = true;
        }

        $monthlyValue = null;
        if (array_key_exists('monthly_value_cents', $data)) {
            $monthlyValue = Money::fromCents((int) $data['monthly_value_cents']);
        } elseif (array_key_exists('monthly_value', $data)) {
            $monthlyValue = Money::fromDecimal($data['monthly_value']);
        }

        return new self(
            name: array_key_exists('name', $data) ? trim((string) $data['name']) : null,
            phone: array_key_exists('phone', $data) ? PhoneNumber::from((string) $data['phone']) : null,
            email: array_key_exists('email', $data) ? self::nullableString($data['email']) : null,
            pixKey: array_key_exists('pix_key', $data) ? PixKey::from((string) $data['pix_key']) : null,
            monthlyValue: $monthlyValue,
            dueDay: array_key_exists('due_day', $data) ? DueDay::from((int) $data['due_day']) : null,
            status: array_key_exists('status', $data)
                ? ($data['status'] instanceof CustomerStatus
                    ? $data['status']
                    : CustomerStatus::from((string) $data['status']))
                : null,
            officeId: array_key_exists('office_id', $data)
                ? ($data['office_id'] !== null ? (int) $data['office_id'] : null)
                : null,
            presentKeys: $presentKeys,
        );
    }

    public function has(string $key): bool
    {
        return isset($this->presentKeys[$key]) || isset($this->presentKeys[str_replace('_', '', $key)]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->toPersistenceArray();
    }

    /**
     * Somente campos presentes na requisição.
     *
     * @return array<string, mixed>
     */
    public function toPersistenceArray(): array
    {
        $attributes = [];

        if ($this->has('name') && $this->name !== null) {
            $attributes['name'] = $this->name;
        }

        if ($this->has('phone') && $this->phone !== null) {
            $attributes['phone'] = $this->phone->value();
        }

        if ($this->has('email')) {
            $attributes['email'] = $this->email;
        }

        if ($this->has('pix_key') && $this->pixKey !== null) {
            $attributes['pix_key'] = $this->pixKey->value();
        }

        if (($this->has('monthly_value') || $this->has('monthly_value_cents')) && $this->monthlyValue !== null) {
            $attributes['monthly_value'] = $this->monthlyValue->amountInCents();
        }

        if ($this->has('due_day') && $this->dueDay !== null) {
            $attributes['due_day'] = $this->dueDay->value();
        }

        if ($this->has('status') && $this->status !== null) {
            $attributes['status'] = $this->status->value;
        }

        if ($this->has('office_id')) {
            $attributes['office_id'] = $this->officeId;
        }

        return $attributes;
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }
}
