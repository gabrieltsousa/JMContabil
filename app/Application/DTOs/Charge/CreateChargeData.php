<?php

declare(strict_types=1);

namespace App\Application\DTOs\Charge;

use App\Application\DTOs\DataTransferObject;
use App\Domain\Charge\Enums\ChargeStatus;
use App\Domain\Charge\Enums\PaymentMethodType;
use App\Domain\Charge\ValueObjects\ReferenceMonth;
use App\Domain\Shared\ValueObjects\Money;
use App\Infrastructure\Persistence\Eloquent\Models\Charge;
use DateTimeImmutable;

/**
 * DTO de criação de cobrança (geração automática ou manual).
 */
final readonly class CreateChargeData implements DataTransferObject
{
    public function __construct(
        public int $customerId,
        public ReferenceMonth $referenceMonth,
        public Money $amount,
        public DateTimeImmutable $dueDate,
        public PaymentMethodType $paymentMethodType,
        public array $paymentPayload,
        public ?int $officeId = null,
        public ChargeStatus $status = ChargeStatus::Pending,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $amount = array_key_exists('amount_cents', $data)
            ? Money::fromCents((int) $data['amount_cents'])
            : Money::fromDecimal($data['amount'] ?? 0);

        $paymentPayload = $data['payment_payload'] ?? [];
        if (! is_array($paymentPayload)) {
            $paymentPayload = [];
        }

        if (isset($data['pix_key']) && ! isset($paymentPayload['pix_key'])) {
            $paymentPayload['pix_key'] = (string) $data['pix_key'];
        }

        return new self(
            customerId: (int) $data['customer_id'],
            referenceMonth: isset($data['reference_month'])
                ? ReferenceMonth::from((string) $data['reference_month'])
                : ReferenceMonth::current(),
            amount: $amount,
            dueDate: new DateTimeImmutable((string) ($data['due_date'] ?? 'today')),
            paymentMethodType: isset($data['payment_method_type'])
                ? PaymentMethodType::from((string) $data['payment_method_type'])
                : PaymentMethodType::PixKey,
            paymentPayload: $paymentPayload,
            officeId: isset($data['office_id']) ? (int) $data['office_id'] : null,
            status: isset($data['status'])
                ? ChargeStatus::from((string) $data['status'])
                : ChargeStatus::Pending,
        );
    }

    public static function fromCustomerSnapshot(
        int $customerId,
        ?int $officeId,
        int $amountCents,
        string $pixKey,
        int $dueDay,
        ?DateTimeImmutable $now = null,
    ): self {
        $now ??= new DateTimeImmutable('now');
        $year = (int) $now->format('Y');
        $month = (int) $now->format('m');
        $dueDate = DateTimeImmutable::createFromFormat(
            'Y-m-d',
            sprintf('%04d-%02d-%02d', $year, $month, $dueDay)
        );

        if ($dueDate === false) {
            $dueDate = $now;
        }

        return new self(
            customerId: $customerId,
            referenceMonth: ReferenceMonth::fromDate($now),
            amount: Money::fromCents($amountCents),
            dueDate: $dueDate->setTime(0, 0),
            paymentMethodType: PaymentMethodType::PixKey,
            paymentPayload: ['pix_key' => $pixKey],
            officeId: $officeId,
            status: ChargeStatus::Pending,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'customer_id' => $this->customerId,
            'office_id' => $this->officeId,
            'reference_month' => $this->referenceMonth->value(),
            'amount' => $this->amount->amountInCents(),
            'due_date' => $this->dueDate->format('Y-m-d'),
            'status' => $this->status->value,
            'payment_method_type' => $this->paymentMethodType->value,
            'payment_payload' => $this->paymentPayload,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toChargePersistenceArray(): array
    {
        return [
            'customer_id' => $this->customerId,
            'office_id' => $this->officeId,
            'reference_month' => $this->referenceMonth->value(),
            'amount' => $this->amount->amountInCents(),
            'due_date' => $this->dueDate->format('Y-m-d'),
            'status' => $this->status->value,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toPaymentMethodPersistenceArray(int $chargeId): array
    {
        return [
            'charge_id' => $chargeId,
            'type' => $this->paymentMethodType->value,
            'amount' => $this->amount->amountInCents(),
            'payload' => $this->paymentPayload,
        ];
    }
}
