<?php

declare(strict_types=1);

namespace App\Infrastructure\WhatsApp\Providers;

use App\Domain\Settings\Enums\WhatsAppProvider as WhatsAppProviderEnum;
use App\Domain\Shared\ValueObjects\PhoneNumber;
use App\Infrastructure\WhatsApp\Contracts\WhatsAppProviderInterface;
use App\Infrastructure\WhatsApp\DTOs\PixChargeMessage;
use App\Infrastructure\WhatsApp\DTOs\WhatsAppSendResult;
use App\Infrastructure\WhatsApp\Support\FakeWhatsAppInbox;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Implementação Fake para desenvolvimento e testes.
 * Não envia mensagem real — registra log + inbox em memória.
 *
 * Simular falha: config('jmcontabil.whatsapp.fake.should_fail') = true
 */
final class FakeWhatsAppProvider implements WhatsAppProviderInterface
{
    public function driver(): string
    {
        return WhatsAppProviderEnum::Fake->value;
    }

    public function sendText(string $phone, string $message): WhatsAppSendResult
    {
        $startedAt = hrtime(true);
        $normalizedPhone = $this->normalizePhone($phone);

        if ($this->shouldFail()) {
            return $this->failure($normalizedPhone, 'text', 'Fake provider forced failure.', $startedAt);
        }

        $providerMessageId = 'fake_'.Str::uuid()->toString();
        $durationMs = $this->elapsedMs($startedAt);

        $raw = [
            'provider' => $this->driver(),
            'phone' => $normalizedPhone,
            'type' => 'text',
            'message' => $message,
            'provider_message_id' => $providerMessageId,
        ];

        FakeWhatsAppInbox::record($raw);

        Log::info('whatsapp.fake.send_text', [
            ...$raw,
            'duration_ms' => $durationMs,
            'sent_at' => now()->toIso8601String(),
        ]);

        return WhatsAppSendResult::success(
            providerMessageId: $providerMessageId,
            rawResponse: json_encode($raw, JSON_THROW_ON_ERROR),
            durationMs: $durationMs,
        );
    }

    public function sendPixCharge(string $phone, PixChargeMessage $payload): WhatsAppSendResult
    {
        $startedAt = hrtime(true);
        $normalizedPhone = $this->normalizePhone($phone);

        if ($this->shouldFail()) {
            return $this->failure($normalizedPhone, 'pix_charge', 'Fake provider forced failure.', $startedAt, $payload);
        }

        $providerMessageId = 'fake_'.Str::uuid()->toString();
        $durationMs = $this->elapsedMs($startedAt);

        $raw = [
            'provider' => $this->driver(),
            'phone' => $normalizedPhone,
            'type' => 'pix_charge',
            'payload' => $payload->toArray(),
            'provider_message_id' => $providerMessageId,
        ];

        FakeWhatsAppInbox::record($raw);

        Log::info('whatsapp.fake.send_pix_charge', [
            'phone' => $normalizedPhone,
            'customer' => $payload->customerName,
            'amount' => $payload->amountFormatted,
            'pix_key' => $payload->pixKey,
            'message' => $payload->messageBody,
            'duration_ms' => $durationMs,
            'provider_message_id' => $providerMessageId,
            'sent_at' => now()->toIso8601String(),
        ]);

        return WhatsAppSendResult::success(
            providerMessageId: $providerMessageId,
            rawResponse: json_encode($raw, JSON_THROW_ON_ERROR),
            durationMs: $durationMs,
        );
    }

    private function normalizePhone(string $phone): string
    {
        return PhoneNumber::from($phone)->whatsapp();
    }

    private function shouldFail(): bool
    {
        return (bool) config('jmcontabil.whatsapp.fake.should_fail', false);
    }

    private function failure(
        string $phone,
        string $type,
        string $error,
        int $startedAt,
        ?PixChargeMessage $payload = null,
    ): WhatsAppSendResult {
        $durationMs = $this->elapsedMs($startedAt);

        $raw = [
            'provider' => $this->driver(),
            'phone' => $phone,
            'type' => $type,
            'error' => $error,
            'payload' => $payload?->toArray(),
        ];

        Log::warning('whatsapp.fake.failed', [
            ...$raw,
            'duration_ms' => $durationMs,
        ]);

        return WhatsAppSendResult::failure(
            errorMessage: $error,
            rawResponse: json_encode($raw, JSON_THROW_ON_ERROR),
            durationMs: $durationMs,
        );
    }

    private function elapsedMs(int $startedAt): int
    {
        return max(1, (int) ((hrtime(true) - $startedAt) / 1_000_000));
    }
}
