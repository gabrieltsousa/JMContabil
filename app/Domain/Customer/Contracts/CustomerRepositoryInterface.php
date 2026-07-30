<?php

declare(strict_types=1);

namespace App\Domain\Customer\Contracts;

use App\Domain\Customer\Enums\CustomerStatus;
use App\Domain\Customer\ValueObjects\DueDay;

/**
 * Contrato de persistência de clientes.
 * Implementação fica em Infrastructure (Eloquent).
 */
interface CustomerRepositoryInterface
{
    public function findById(int $id): ?object;

    public function findByIdOrFail(int $id): object;

    /**
     * @return list<object>
     */
    public function all(?int $officeId = null): array;

    /**
     * @return list<object>
     */
    public function findActiveByDueDay(DueDay $dueDay, ?int $officeId = null): array;

    /**
     * @return list<object>
     */
    public function findByStatus(CustomerStatus $status, ?int $officeId = null): array;

    /**
     * @return list<object>
     */
    public function filter(
        ?CustomerStatus $status = null,
        ?int $dueDay = null,
        ?string $search = null,
        ?int $officeId = null,
    ): array;

    public function create(array $attributes): object;

    public function update(int $id, array $attributes): object;

    public function delete(int $id): bool;

    public function countByStatus(CustomerStatus $status, ?int $officeId = null): int;
}
