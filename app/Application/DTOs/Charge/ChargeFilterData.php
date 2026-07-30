<?php

declare(strict_types=1);

namespace App\Application\DTOs\Charge;

use App\Application\DTOs\DataTransferObject;
use App\Domain\Charge\Enums\ChargeStatus;

/**
 * Filtros do histórico de cobranças/envios.
 */
final readonly class ChargeFilterData implements DataTransferObject
{
    public function __construct(
        public ?int $customerId = null,
        public ?ChargeStatus $status = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public ?string $referenceMonth = null,
        public ?int $officeId = null,
        public int $page = 1,
        public int $perPage = 15,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            customerId: isset($data['customer_id']) && $data['customer_id'] !== ''
                ? (int) $data['customer_id']
                : null,
            status: isset($data['status']) && $data['status'] !== ''
                ? ChargeStatus::from((string) $data['status'])
                : null,
            dateFrom: isset($data['date_from']) && $data['date_from'] !== ''
                ? (string) $data['date_from']
                : null,
            dateTo: isset($data['date_to']) && $data['date_to'] !== ''
                ? (string) $data['date_to']
                : null,
            referenceMonth: isset($data['reference_month']) && $data['reference_month'] !== ''
                ? (string) $data['reference_month']
                : null,
            officeId: isset($data['office_id']) ? (int) $data['office_id'] : null,
            page: max(1, (int) ($data['page'] ?? 1)),
            perPage: min(100, max(1, (int) ($data['per_page'] ?? 15))),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'customer_id' => $this->customerId,
            'status' => $this->status?->value,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'reference_month' => $this->referenceMonth,
            'office_id' => $this->officeId,
            'page' => $this->page,
            'per_page' => $this->perPage,
        ];
    }
}
