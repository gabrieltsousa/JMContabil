<?php

declare(strict_types=1);

namespace App\Infrastructure\WhatsApp\DTOs;

final readonly class WhatsAppSendResult
{
    public function __construct(
        public bool $success,
        public ?string $providerMessageId = null,
        public ?string $rawResponse = null,
        public ?string $errorMessage = null,
        public int $durationMs = 0,
    ) {
    }

    public static function success(
        ?string $providerMessageId = null,
        ?string $rawResponse = null,
        int $durationMs = 0,
    ): self {
        return new self(
            success: true,
            providerMessageId: $providerMessageId,
            rawResponse: $rawResponse,
            durationMs: $durationMs,
        );
    }

    public static function failure(
        string $errorMessage,
        ?string $rawResponse = null,
        int $durationMs = 0,
    ): self {
        return new self(
            success: false,
            rawResponse: $rawResponse,
            errorMessage: $errorMessage,
            durationMs: $durationMs,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'provider_message_id' => $this->providerMessageId,
            'raw_response' => $this->rawResponse,
            'error_message' => $this->errorMessage,
            'duration_ms' => $this->durationMs,
        ];
    }
}
