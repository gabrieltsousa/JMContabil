<?php

declare(strict_types=1);

namespace App\Infrastructure\WhatsApp\DTOs;

final readonly class PixChargeMessage
{
    public function __construct(
        public string $customerName,
        public string $amountFormatted,
        public string $dueDate,
        public string $pixKey,
        public string $messageBody,
        public string $paymentMethodType = 'pix_key',
        public ?string $pixCopiaCola = null,
        public ?string $qrCodePayload = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'customer_name' => $this->customerName,
            'amount_formatted' => $this->amountFormatted,
            'due_date' => $this->dueDate,
            'pix_key' => $this->pixKey,
            'message_body' => $this->messageBody,
            'payment_method_type' => $this->paymentMethodType,
            'pix_copia_cola' => $this->pixCopiaCola,
            'qr_code_payload' => $this->qrCodePayload,
        ];
    }
}
