<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Charge\Contracts\ChargeRepositoryInterface;
use App\Domain\Charge\Enums\ChargeStatus;
use App\Domain\Charge\ValueObjects\ReferenceMonth;
use App\Infrastructure\Persistence\Eloquent\Models\Charge;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class EloquentChargeRepository implements ChargeRepositoryInterface
{
    public function findById(int $id): ?Charge
    {
        return Charge::query()
            ->with(['customer', 'paymentMethods', 'deliveries'])
            ->find($id);
    }

    public function findByIdOrFail(int $id): Charge
    {
        return Charge::query()
            ->with(['customer', 'paymentMethods', 'deliveries'])
            ->findOrFail($id);
    }

    public function findByCustomerAndReference(int $customerId, ReferenceMonth $reference): ?Charge
    {
        return Charge::query()
            ->where('customer_id', $customerId)
            ->where('reference_month', $reference->value())
            ->first();
    }

    /**
     * {@inheritdoc}
     */
    public function filter(
        ?int $customerId = null,
        ?ChargeStatus $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?int $officeId = null,
        ?string $referenceMonth = null,
    ): array {
        return Charge::query()
            ->with(['customer', 'primaryPaymentMethod', 'deliveries'])
            ->forOffice($officeId)
            ->when($customerId !== null, fn (Builder $query) => $query->where('customer_id', $customerId))
            ->when($status !== null, fn (Builder $query) => $query->where('status', $status))
            ->when($referenceMonth !== null, fn (Builder $query) => $query->where('reference_month', $referenceMonth))
            ->when($dateFrom !== null, fn (Builder $query) => $query->whereDate('due_date', '>=', $dateFrom))
            ->when($dateTo !== null, fn (Builder $query) => $query->whereDate('due_date', '<=', $dateTo))
            ->orderByDesc('due_date')
            ->orderByDesc('id')
            ->get()
            ->all();
    }

    public function create(array $attributes): Charge
    {
        return Charge::query()->create($attributes);
    }

    public function createWithPaymentMethod(array $chargeAttributes, array $paymentMethodAttributes): Charge
    {
        return DB::transaction(function () use ($chargeAttributes, $paymentMethodAttributes): Charge {
            /** @var Charge $charge */
            $charge = Charge::query()->create($chargeAttributes);

            $charge->paymentMethods()->create($paymentMethodAttributes);

            return $charge->load(['customer', 'paymentMethods', 'deliveries']);
        });
    }

    public function update(int $id, array $attributes): Charge
    {
        $charge = Charge::query()->findOrFail($id);
        $charge->fill($attributes);
        $charge->save();

        return $charge->refresh()->load(['customer', 'paymentMethods', 'deliveries']);
    }

    public function markAsSent(int $id, string $message, ?string $sentAt = null): Charge
    {
        $charge = Charge::query()->findOrFail($id);
        $charge->forceFill([
            'status' => ChargeStatus::Sent,
            'message_sent' => $message,
            'sent_at' => $sentAt ?? now(),
            'failure_reason' => null,
        ])->save();

        return $charge->refresh();
    }

    public function markAsFailed(int $id, ?string $reason = null): Charge
    {
        $charge = Charge::query()->findOrFail($id);
        $charge->forceFill([
            'status' => ChargeStatus::Failed,
            'failure_reason' => $reason,
        ])->save();

        return $charge->refresh();
    }

    public function markAsPaid(int $id, ?string $paidAt = null): Charge
    {
        $charge = Charge::query()->findOrFail($id);
        $charge->forceFill([
            'status' => ChargeStatus::Paid,
            'paid_at' => $paidAt ?? now(),
            'failure_reason' => null,
        ])->save();

        return $charge->refresh();
    }

    public function countByStatus(ChargeStatus $status, ?int $officeId = null): int
    {
        return Charge::query()
            ->forOffice($officeId)
            ->where('status', $status)
            ->count();
    }

    public function countSentToday(?int $officeId = null): int
    {
        return Charge::query()
            ->forOffice($officeId)
            ->sentToday()
            ->count();
    }

    public function countSentInMonth(ReferenceMonth $reference, ?int $officeId = null): int
    {
        return Charge::query()
            ->forOffice($officeId)
            ->where('reference_month', $reference->value())
            ->where('status', ChargeStatus::Sent)
            ->count();
    }

    public function countPending(?int $officeId = null): int
    {
        return $this->countByStatus(ChargeStatus::Pending, $officeId);
    }

    public function countPaidInMonth(ReferenceMonth $reference, ?int $officeId = null): int
    {
        return Charge::query()
            ->forOffice($officeId)
            ->where('reference_month', $reference->value())
            ->where('status', ChargeStatus::Paid)
            ->count();
    }
}
