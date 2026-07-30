<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Charge\Contracts\ChargeDeliveryRepositoryInterface;
use App\Domain\Charge\Enums\DeliveryStatus;
use App\Infrastructure\Persistence\Eloquent\Models\ChargeDelivery;
use Illuminate\Database\Eloquent\Builder;

final class EloquentChargeDeliveryRepository implements ChargeDeliveryRepositoryInterface
{
    public function findById(int $id): ?ChargeDelivery
    {
        return ChargeDelivery::query()
            ->with(['charge.customer'])
            ->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findByChargeId(int $chargeId): array
    {
        return ChargeDelivery::query()
            ->where('charge_id', $chargeId)
            ->orderByDesc('id')
            ->get()
            ->all();
    }

    /**
     * {@inheritdoc}
     */
    public function filter(
        ?int $customerId = null,
        ?DeliveryStatus $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?int $officeId = null,
    ): array {
        return ChargeDelivery::query()
            ->with(['charge.customer'])
            ->when($officeId !== null, function (Builder $query) use ($officeId): void {
                $query->whereHas('charge', fn (Builder $charge) => $charge->where('office_id', $officeId));
            })
            ->when($customerId !== null, function (Builder $query) use ($customerId): void {
                $query->whereHas('charge', fn (Builder $charge) => $charge->where('customer_id', $customerId));
            })
            ->when($status !== null, fn (Builder $query) => $query->where('status', $status))
            ->when($dateFrom !== null, fn (Builder $query) => $query->whereDate('sent_at', '>=', $dateFrom))
            ->when($dateTo !== null, fn (Builder $query) => $query->whereDate('sent_at', '<=', $dateTo))
            ->orderByDesc('id')
            ->get()
            ->all();
    }

    public function create(array $attributes): ChargeDelivery
    {
        return ChargeDelivery::query()->create($attributes);
    }

    public function updateStatus(
        int $id,
        DeliveryStatus $status,
        ?string $providerResponse = null,
        ?int $durationMs = null,
        ?string $errorMessage = null,
        ?string $providerMessageId = null,
    ): ChargeDelivery {
        $delivery = ChargeDelivery::query()->findOrFail($id);

        $payload = [
            'status' => $status,
            'provider_response' => $providerResponse,
            'duration_ms' => $durationMs ?? $delivery->duration_ms,
            'error_message' => $errorMessage,
        ];

        if ($providerMessageId !== null) {
            $payload['provider_message_id'] = $providerMessageId;
        }

        if ($status->isSuccessful()) {
            $payload['sent_at'] = $delivery->sent_at ?? now();
            $payload['error_message'] = null;
        }

        $delivery->forceFill($payload)->save();

        return $delivery->refresh()->load(['charge.customer']);
    }
}
