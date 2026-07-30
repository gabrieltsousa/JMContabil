<?php

declare(strict_types=1);

namespace App\Infrastructure\WhatsApp\Providers;

use App\Domain\Settings\Enums\WhatsAppProvider as WhatsAppProviderEnum;
use App\Domain\Shared\ValueObjects\PhoneNumber;
use App\Infrastructure\WhatsApp\Contracts\WhatsAppProviderInterface;
use App\Infrastructure\WhatsApp\DTOs\PixChargeMessage;
use App\Infrastructure\WhatsApp\DTOs\WhatsAppSendResult;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Adapter Evolution API v2.
 *
 * POST {base}/message/sendText/{instance}
 * Header: apikey
 * Body: { number, text }
 */
final class EvolutionWhatsAppProvider implements WhatsAppProviderInterface
{
    public function driver(): string
    {
        return WhatsAppProviderEnum::Evolution->value;
    }

    public function sendText(string $phone, string $message): WhatsAppSendResult
    {
        return $this->send($phone, $message);
    }

    public function sendPixCharge(string $phone, PixChargeMessage $payload): WhatsAppSendResult
    {
        return $this->send($phone, $payload->messageBody);
    }

    private function send(string $phone, string $message): WhatsAppSendResult
    {
        $startedAt = hrtime(true);
        $baseUrl = rtrim((string) config('jmcontabil.whatsapp.evolution.base_url'), '/');
        $apiKey = (string) config('jmcontabil.whatsapp.evolution.api_key');
        $instance = (string) config('jmcontabil.whatsapp.evolution.instance');

        if ($baseUrl === '' || $apiKey === '' || $instance === '') {
            return WhatsAppSendResult::failure(
                errorMessage: 'Evolution API não configurada. Defina WHATSAPP_EVOLUTION_URL, WHATSAPP_EVOLUTION_API_KEY e WHATSAPP_EVOLUTION_INSTANCE.',
                durationMs: $this->elapsedMs($startedAt),
            );
        }

        try {
            $number = PhoneNumber::from($phone)->whatsapp();
        } catch (Throwable $exception) {
            return WhatsAppSendResult::failure(
                errorMessage: 'Telefone inválido: '.$exception->getMessage(),
                durationMs: $this->elapsedMs($startedAt),
            );
        }

        $endpoint = sprintf('%s/message/sendText/%s', $baseUrl, rawurlencode($instance));

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'apikey' => $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($endpoint, [
                    'number' => $number,
                    'text' => $message,
                ]);
        } catch (ConnectionException $exception) {
            Log::error('whatsapp.evolution.connection_error', [
                'endpoint' => $endpoint,
                'error' => $exception->getMessage(),
            ]);

            return WhatsAppSendResult::failure(
                errorMessage: 'Falha de conexão com Evolution API: '.$exception->getMessage(),
                durationMs: $this->elapsedMs($startedAt),
            );
        } catch (Throwable $exception) {
            return WhatsAppSendResult::failure(
                errorMessage: 'Erro ao chamar Evolution API: '.$exception->getMessage(),
                durationMs: $this->elapsedMs($startedAt),
            );
        }

        $durationMs = $this->elapsedMs($startedAt);
        $body = $response->body();
        $json = $response->json();

        if (! $response->successful()) {
            $error = is_array($json)
                ? (string) ($json['message'] ?? $json['error'] ?? $json['response']['message'] ?? $body)
                : $body;

            Log::warning('whatsapp.evolution.send_failed', [
                'status' => $response->status(),
                'number' => $number,
                'error' => $error,
            ]);

            return WhatsAppSendResult::failure(
                errorMessage: 'Evolution API HTTP '.$response->status().': '.$error,
                rawResponse: $body,
                durationMs: $durationMs,
            );
        }

        $providerMessageId = null;
        if (is_array($json)) {
            $providerMessageId = $json['key']['id']
                ?? $json['message']['key']['id']
                ?? $json['id']
                ?? null;
            $providerMessageId = is_string($providerMessageId) ? $providerMessageId : null;
        }

        Log::info('whatsapp.evolution.send_ok', [
            'number' => $number,
            'provider_message_id' => $providerMessageId,
            'duration_ms' => $durationMs,
        ]);

        return WhatsAppSendResult::success(
            providerMessageId: $providerMessageId,
            rawResponse: $body,
            durationMs: $durationMs,
        );
    }

    private function elapsedMs(int $startedAt): int
    {
        return max(1, (int) ((hrtime(true) - $startedAt) / 1_000_000));
    }
}
