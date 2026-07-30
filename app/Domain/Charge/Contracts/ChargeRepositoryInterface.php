<?php

declare(strict_types=1);

namespace App\Domain\Charge\Contracts;

use App\Domain\Charge\Enums\ChargeStatus;
use App\Domain\Charge\ValueObjects\ReferenceMonth;

interface ChargeRepositoryInterface
{
    public function findById(int $id): ?object;

    public function findByIdOrFail(int $id): object;

    public function findByCustomerAndReference(int $customerId, ReferenceMonth $reference): ?object;

    /**
     * @return list<object>
     */
    public function filter(
        ?int $customerId = null,
        ?ChargeStatus $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?int $officeId = null,
        ?string $referenceMonth = null,
    ): array;

    public function create(array $attributes): object;

    /**
     * Cria cobrança + forma de pagamento atomicamente.
     *
     * @param  array<string, mixed>  $chargeAttributes
     * @param  array<string, mixed>  $paymentMethodAttributes  sem charge_id
     */
    public function createWithPaymentMethod(array $chargeAttributes, array $paymentMethodAttributes): object;

    public function update(int $id, array $attributes): object;

    public function markAsSent(int $id, string $message, ?string $sentAt = null): object;

    public function markAsFailed(int $id, ?string $reason = null): object;

    public function markAsPaid(int $id, ?string $paidAt = null): object;

    public function countByStatus(ChargeStatus $status, ?int $officeId = null): int;

    public function countSentToday(?int $officeId = null): int;

    public function countSentInMonth(ReferenceMonth $reference, ?int $officeId = null): int;

    public function countPending(?int $officeId = null): int;

    public function countPaidInMonth(ReferenceMonth $reference, ?int $officeId = null): int;
}
