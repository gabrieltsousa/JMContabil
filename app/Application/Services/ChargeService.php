<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\Charge\ChargeData;
use App\Application\DTOs\Charge\ChargeDeliveryData;
use App\Application\DTOs\Charge\ChargeFilterData;
use App\Application\DTOs\Charge\CreateChargeData;
use App\Domain\Charge\Contracts\ChargeDeliveryRepositoryInterface;
use App\Domain\Charge\Contracts\ChargeRepositoryInterface;
use App\Domain\Charge\Enums\DeliveryStatus;
use App\Domain\Shared\Exceptions\BusinessRuleException;
use App\Infrastructure\Persistence\Eloquent\Models\Charge;
use App\Infrastructure\Persistence\Eloquent\Models\ChargeDelivery;

final class ChargeService
{
    public function __construct(
        private readonly ChargeRepositoryInterface $charges,
        private readonly ChargeDeliveryRepositoryInterface $deliveries,
    ) {
    }

    /**
     * @return list<ChargeData>
     */
    public function list(ChargeFilterData $filter): array
    {
        /** @var list<Charge> $items */
        $items = $this->charges->filter(
            customerId: $filter->customerId,
            status: $filter->status,
            dateFrom: $filter->dateFrom,
            dateTo: $filter->dateTo,
            officeId: $filter->officeId,
            referenceMonth: $filter->referenceMonth,
        );

        return array_values(array_map(
            static fn (Charge $charge): ChargeData => ChargeData::fromModel($charge),
            $items
        ));
    }

    public function find(int $id): ChargeData
    {
        /** @var Charge $charge */
        $charge = $this->charges->findByIdOrFail($id);

        return ChargeData::fromModel($charge);
    }

    public function findLatestIdForCustomer(int $customerId, ?int $officeId = null): int
    {
        $query = Charge::query()
            ->where('customer_id', $customerId)
            ->when($officeId !== null, fn ($builder) => $builder->where('office_id', $officeId))
            ->latest('id');

        /** @var Charge $charge */
        $charge = $query->firstOrFail();

        return $charge->id;
    }

    public function create(CreateChargeData $data): ChargeData
    {
        $existing = $this->charges->findByCustomerAndReference(
            $data->customerId,
            $data->referenceMonth
        );

        if ($existing !== null) {
            throw BusinessRuleException::withMessage(
                sprintf(
                    'Já existe cobrança para o cliente %d na competência %s.',
                    $data->customerId,
                    $data->referenceMonth->value()
                )
            );
        }

        /** @var Charge $charge */
        $charge = $this->charges->createWithPaymentMethod(
            $data->toChargePersistenceArray(),
            [
                'type' => $data->paymentMethodType->value,
                'amount' => $data->amount->amountInCents(),
                'payload' => $data->paymentPayload,
            ]
        );

        return ChargeData::fromModel($charge);
    }

    /**
     * @return list<ChargeDeliveryData>
     */
    public function listDeliveries(
        ?int $customerId = null,
        ?DeliveryStatus $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?int $officeId = null,
    ): array {
        /** @var list<ChargeDelivery> $items */
        $items = $this->deliveries->filter(
            customerId: $customerId,
            status: $status,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            officeId: $officeId,
        );

        return array_values(array_map(
            static fn (ChargeDelivery $delivery): ChargeDeliveryData => ChargeDeliveryData::fromModel($delivery),
            $items
        ));
    }
}
