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
 * DTO de criação de cliente.
 * Valida e normaliza via Value Objects no fromArray.
 */
final readonly class CreateCustomerData implements DataTransferObject
{
    public function __construct(
        public string $name,
        public PhoneNumber $phone,
        public ?string $email,
        public PixKey $pixKey,
        public Money $monthlyValue,
        public DueDay $dueDay,
        public CustomerStatus $status,
        public ?int $officeId = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $monthlyValue = array_key_exists('monthly_value_cents', $data)
            ? Money::fromCents((int) $data['monthly_value_cents'])
            : Money::fromDecimal($data['monthly_value'] ?? 0);

        return new self(
            name: trim((string) $data['name']),
            phone: PhoneNumber::from((string) $data['phone']),
            email: self::nullableString($data['email'] ?? null),
            pixKey: PixKey::from((string) $data['pix_key']),
            monthlyValue: $monthlyValue,
            dueDay: DueDay::from((int) $data['due_day']),
            status: isset($data['status'])
                ? ($data['status'] instanceof CustomerStatus
                    ? $data['status']
                    : CustomerStatus::from((string) $data['status']))
                : CustomerStatus::Active,
            officeId: isset($data['office_id']) ? (int) $data['office_id'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'office_id' => $this->officeId,
            'name' => $this->name,
            'phone' => $this->phone->value(),
            'email' => $this->email,
            'pix_key' => $this->pixKey->value(),
            'monthly_value' => $this->monthlyValue->amountInCents(),
            'due_day' => $this->dueDay->value(),
            'status' => $this->status->value,
        ];
    }

    /**
     * Atributos prontos para persistência Eloquent/Repository.
     *
     * @return array<string, mixed>
     */
    public function toPersistenceArray(): array
    {
        return $this->toArray();
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
