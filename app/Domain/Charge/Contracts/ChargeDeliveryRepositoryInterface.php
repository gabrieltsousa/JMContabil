<?php

declare(strict_types=1);

namespace App\Domain\Charge\Contracts;

use App\Domain\Charge\Enums\DeliveryStatus;

interface ChargeDeliveryRepositoryInterface
{
    public function findById(int $id): ?object;

    /**
     * @return list<object>
     */
    public function findByChargeId(int $chargeId): array;

    /**
     * @return list<object>
     */
    public function filter(
        ?int $customerId = null,
        ?DeliveryStatus $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?int $officeId = null,
    ): array;

    public function create(array $attributes): object;

    public function updateStatus(
        int $id,
        DeliveryStatus $status,
        ?string $providerResponse = null,
        ?int $durationMs = null,
        ?string $errorMessage = null,
        ?string $providerMessageId = null,
    ): object;
}
