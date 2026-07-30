<?php

declare(strict_types=1);

namespace App\Application\DTOs\Customer;

use App\Application\DTOs\DataTransferObject;
use App\Domain\Customer\Enums\CustomerStatus;

/**
 * Filtros de listagem de clientes.
 */
final readonly class CustomerFilterData implements DataTransferObject
{
    public function __construct(
        public ?CustomerStatus $status = null,
        public ?int $dueDay = null,
        public ?string $search = null,
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
            status: isset($data['status']) && $data['status'] !== ''
                ? CustomerStatus::from((string) $data['status'])
                : null,
            dueDay: isset($data['due_day']) && $data['due_day'] !== ''
                ? (int) $data['due_day']
                : null,
            search: isset($data['search']) && trim((string) $data['search']) !== ''
                ? trim((string) $data['search'])
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
            'status' => $this->status?->value,
            'due_day' => $this->dueDay,
            'search' => $this->search,
            'office_id' => $this->officeId,
            'page' => $this->page,
            'per_page' => $this->perPage,
        ];
    }
}
