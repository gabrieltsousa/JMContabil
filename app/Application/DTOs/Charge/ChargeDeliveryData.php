<?php

declare(strict_types=1);

namespace App\Application\DTOs\Charge;

use App\Application\DTOs\DataTransferObject;
use App\Domain\Charge\Enums\DeliveryChannel;
use App\Domain\Charge\Enums\DeliveryStatus;
use App\Infrastructure\Persistence\Eloquent\Models\ChargeDelivery;

/**
 * DTO de leitura de entrega (histórico de envio).
 */
final readonly class ChargeDeliveryData implements DataTransferObject
{
    public function __construct(
        public int $id,
        public int $chargeId,
        public DeliveryChannel $channel,
        public DeliveryStatus $status,
        public string $message,
        public string $provider,
        public ?string $providerMessageId,
        public ?string $errorMessage,
        public int $durationMs,
        public int $attempt,
        public ?string $sentAt,
        public ?string $whatsappResponse,
        public ?string $customerName = null,
        public ?int $amountCents = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            chargeId: (int) $data['charge_id'],
            channel: DeliveryChannel::from((string) $data['channel']),
            status: DeliveryStatus::from((string) $data['status']),
            message: (string) $data['message'],
            provider: (string) $data['provider'],
            providerMessageId: isset($data['provider_message_id']) ? (string) $data['provider_message_id'] : null,
            errorMessage: isset($data['error_message']) ? (string) $data['error_message'] : null,
            durationMs: (int) ($data['duration_ms'] ?? 0),
            attempt: (int) ($data['attempt'] ?? 1),
            sentAt: isset($data['sent_at']) ? (string) $data['sent_at'] : null,
            whatsappResponse: isset($data['whatsapp_response']) ? (string) $data['whatsapp_response'] : null,
            customerName: isset($data['customer_name']) ? (string) $data['customer_name'] : null,
            amountCents: isset($data['amount']) ? (int) $data['amount'] : null,
        );
    }

    public static function fromModel(ChargeDelivery $delivery): self
    {
        return new self(
            id: $delivery->id,
            chargeId: $delivery->charge_id,
            channel: $delivery->channel,
            status: $delivery->status,
            message: $delivery->message,
            provider: $delivery->provider,
            providerMessageId: $delivery->provider_message_id,
            errorMessage: $delivery->error_message,
            durationMs: $delivery->duration_ms,
            attempt: $delivery->attempt,
            sentAt: $delivery->sent_at?->toIso8601String(),
            whatsappResponse: $delivery->whatsapp_response,
            customerName: $delivery->relationLoaded('charge')
                && $delivery->charge?->relationLoaded('customer')
                    ? $delivery->charge->customer?->name
                    : null,
            amountCents: $delivery->relationLoaded('charge') ? $delivery->charge?->amount : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'charge_id' => $this->chargeId,
            'customer_name' => $this->customerName,
            'amount' => $this->amountCents,
            'channel' => $this->channel->value,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'message' => $this->message,
            'provider' => $this->provider,
            'provider_message_id' => $this->providerMessageId,
            'error_message' => $this->errorMessage,
            'duration_ms' => $this->durationMs,
            'attempt' => $this->attempt,
            'sent_at' => $this->sentAt,
            'whatsapp_response' => $this->whatsappResponse,
        ];
    }
}
