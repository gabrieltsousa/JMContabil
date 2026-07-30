<?php

declare(strict_types=1);

namespace App\Application\DTOs\Customer;

use App\Application\DTOs\DataTransferObject;
use App\Domain\Customer\Enums\CustomerStatus;
use App\Domain\Customer\ValueObjects\DueDay;
use App\Domain\Customer\ValueObjects\PixKey;
use App\Domain\Shared\ValueObjects\Money;
use App\Domain\Shared\ValueObjects\PhoneNumber;
use App\Infrastructure\Persistence\Eloquent\Models\Customer;

/**
 * DTO de saída / leitura de cliente.
 */
final readonly class CustomerData implements DataTransferObject
{
    public function __construct(
        public int $id,
        public ?int $officeId,
        public string $name,
        public string $phone,
        public ?string $email,
        public string $pixKey,
        public int $monthlyValueCents,
        public int $dueDay,
        public CustomerStatus $status,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            officeId: isset($data['office_id']) ? (int) $data['office_id'] : null,
            name: (string) $data['name'],
            phone: (string) $data['phone'],
            email: isset($data['email']) ? (string) $data['email'] : null,
            pixKey: (string) $data['pix_key'],
            monthlyValueCents: (int) $data['monthly_value'],
            dueDay: (int) $data['due_day'],
            status: $data['status'] instanceof CustomerStatus
                ? $data['status']
                : CustomerStatus::from((string) $data['status']),
            createdAt: isset($data['created_at']) ? (string) $data['created_at'] : null,
            updatedAt: isset($data['updated_at']) ? (string) $data['updated_at'] : null,
        );
    }

    public static function fromModel(Customer $customer): self
    {
        return new self(
            id: $customer->id,
            officeId: $customer->office_id,
            name: $customer->name,
            phone: $customer->phone,
            email: $customer->email,
            pixKey: $customer->pix_key,
            monthlyValueCents: $customer->monthly_value,
            dueDay: $customer->due_day,
            status: $customer->status,
            createdAt: $customer->created_at?->toIso8601String(),
            updatedAt: $customer->updated_at?->toIso8601String(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'office_id' => $this->officeId,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'pix_key' => $this->pixKey,
            'monthly_value' => $this->monthlyValueCents,
            'monthly_value_formatted' => Money::fromCents($this->monthlyValueCents)->formatBrl(),
            'due_day' => $this->dueDay,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
