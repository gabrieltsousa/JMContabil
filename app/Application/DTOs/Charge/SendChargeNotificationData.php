<?php

declare(strict_types=1);

namespace App\Application\DTOs\Charge;

use App\Application\DTOs\DataTransferObject;

/**
 * Solicitação de envio manual de cobrança via WhatsApp.
 */
final readonly class SendChargeNotificationData implements DataTransferObject
{
    public function __construct(
        public ?int $chargeId = null,
        public ?int $customerId = null,
        public bool $force = false,
        public ?int $officeId = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            chargeId: isset($data['charge_id']) ? (int) $data['charge_id'] : null,
            customerId: isset($data['customer_id']) ? (int) $data['customer_id'] : null,
            force: (bool) ($data['force'] ?? false),
            officeId: isset($data['office_id']) ? (int) $data['office_id'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'charge_id' => $this->chargeId,
            'customer_id' => $this->customerId,
            'force' => $this->force,
            'office_id' => $this->officeId,
        ];
    }
}
