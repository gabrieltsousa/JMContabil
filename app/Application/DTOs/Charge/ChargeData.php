<?php

declare(strict_types=1);

namespace App\Application\DTOs\Charge;

use App\Application\DTOs\DataTransferObject;
use App\Domain\Charge\Enums\ChargeStatus;
use App\Domain\Shared\ValueObjects\Money;
use App\Infrastructure\Persistence\Eloquent\Models\Charge;

/**
 * DTO de leitura de cobrança.
 */
final readonly class ChargeData implements DataTransferObject
{
    /**
     * @param  array<string, mixed>|null  $paymentMethod
     * @param  list<array<string, mixed>>  $deliveries
     */
    public function __construct(
        public int $id,
        public ?int $officeId,
        public int $customerId,
        public ?string $customerName,
        public string $referenceMonth,
        public int $amountCents,
        public ChargeStatus $status,
        public string $dueDate,
        public ?string $sentAt,
        public ?string $paidAt,
        public ?string $messageSent,
        public ?string $failureReason,
        public ?array $paymentMethod = null,
        public array $deliveries = [],
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
            customerId: (int) $data['customer_id'],
            customerName: isset($data['customer_name']) ? (string) $data['customer_name'] : null,
            referenceMonth: (string) $data['reference_month'],
            amountCents: (int) $data['amount'],
            status: $data['status'] instanceof ChargeStatus
                ? $data['status']
                : ChargeStatus::from((string) $data['status']),
            dueDate: (string) $data['due_date'],
            sentAt: isset($data['sent_at']) ? (string) $data['sent_at'] : null,
            paidAt: isset($data['paid_at']) ? (string) $data['paid_at'] : null,
            messageSent: isset($data['message_sent']) ? (string) $data['message_sent'] : null,
            failureReason: isset($data['failure_reason']) ? (string) $data['failure_reason'] : null,
            paymentMethod: isset($data['payment_method']) && is_array($data['payment_method'])
                ? $data['payment_method']
                : null,
            deliveries: isset($data['deliveries']) && is_array($data['deliveries'])
                ? array_values($data['deliveries'])
                : [],
            createdAt: isset($data['created_at']) ? (string) $data['created_at'] : null,
            updatedAt: isset($data['updated_at']) ? (string) $data['updated_at'] : null,
        );
    }

    public static function fromModel(Charge $charge): self
    {
        $paymentMethod = null;
        if ($charge->relationLoaded('primaryPaymentMethod') && $charge->primaryPaymentMethod !== null) {
            $paymentMethod = [
                'id' => $charge->primaryPaymentMethod->id,
                'type' => $charge->primaryPaymentMethod->type->value,
                'amount' => $charge->primaryPaymentMethod->amount,
                'payload' => $charge->primaryPaymentMethod->payload,
            ];
        } elseif ($charge->relationLoaded('paymentMethods') && $charge->paymentMethods->isNotEmpty()) {
            $method = $charge->paymentMethods->first();
            $paymentMethod = [
                'id' => $method->id,
                'type' => $method->type->value,
                'amount' => $method->amount,
                'payload' => $method->payload,
            ];
        }

        $deliveries = [];
        if ($charge->relationLoaded('deliveries')) {
            foreach ($charge->deliveries as $delivery) {
                $deliveries[] = [
                    'id' => $delivery->id,
                    'channel' => $delivery->channel->value,
                    'status' => $delivery->status->value,
                    'message' => $delivery->message,
                    'provider' => $delivery->provider,
                    'provider_message_id' => $delivery->provider_message_id,
                    'error_message' => $delivery->error_message,
                    'duration_ms' => $delivery->duration_ms,
                    'attempt' => $delivery->attempt,
                    'sent_at' => $delivery->sent_at?->toIso8601String(),
                    'whatsapp_response' => $delivery->whatsapp_response,
                ];
            }
        }

        return new self(
            id: $charge->id,
            officeId: $charge->office_id,
            customerId: $charge->customer_id,
            customerName: $charge->relationLoaded('customer') ? $charge->customer?->name : null,
            referenceMonth: $charge->reference_month,
            amountCents: $charge->amount,
            status: $charge->status,
            dueDate: $charge->due_date->toDateString(),
            sentAt: $charge->sent_at?->toIso8601String(),
            paidAt: $charge->paid_at?->toIso8601String(),
            messageSent: $charge->message_sent,
            failureReason: $charge->failure_reason,
            paymentMethod: $paymentMethod,
            deliveries: $deliveries,
            createdAt: $charge->created_at?->toIso8601String(),
            updatedAt: $charge->updated_at?->toIso8601String(),
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
            'customer_id' => $this->customerId,
            'customer_name' => $this->customerName,
            'reference_month' => $this->referenceMonth,
            'amount' => $this->amountCents,
            'amount_formatted' => Money::fromCents($this->amountCents)->formatBrl(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'due_date' => $this->dueDate,
            'sent_at' => $this->sentAt,
            'paid_at' => $this->paidAt,
            'message_sent' => $this->messageSent,
            'failure_reason' => $this->failureReason,
            'payment_method' => $this->paymentMethod,
            'deliveries' => $this->deliveries,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
