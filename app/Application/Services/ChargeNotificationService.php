<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\Charge\ChargeData;
use App\Application\Exceptions\WhatsAppSendException;
use App\Domain\Charge\Contracts\ChargeDeliveryRepositoryInterface;
use App\Domain\Charge\Contracts\ChargeRepositoryInterface;
use App\Domain\Charge\Enums\DeliveryChannel;
use App\Domain\Charge\Enums\DeliveryStatus;
use App\Domain\Settings\Contracts\SettingRepositoryInterface;
use App\Domain\Shared\Exceptions\BusinessRuleException;
use App\Infrastructure\Persistence\Eloquent\Models\Charge;
use App\Infrastructure\Persistence\Eloquent\Models\ChargeDelivery;
use App\Infrastructure\Persistence\Eloquent\Models\Setting;
use App\Infrastructure\WhatsApp\Contracts\WhatsAppProviderInterface;
use App\Infrastructure\WhatsApp\DTOs\PixChargeMessage;
use App\Infrastructure\WhatsApp\WhatsAppProviderResolver;
use App\Shared\Support\MessagePlaceholders;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Orquestra montagem da mensagem + envio WhatsApp + persistência do histórico.
 */
final class ChargeNotificationService
{
    public function __construct(
        private readonly ChargeRepositoryInterface $charges,
        private readonly ChargeDeliveryRepositoryInterface $deliveries,
        private readonly SettingRepositoryInterface $settings,
        private readonly MessageTemplateServiceInterface $templates,
        private readonly WhatsAppProviderResolver $providerResolver,
    ) {
    }

    public function send(int $chargeId, int $attempt = 1, ?int $maxAttempts = null): ChargeData
    {
        $maxAttempts ??= (int) config('jmcontabil.whatsapp.max_tries', 3);

        /** @var Charge $charge */
        $charge = $this->charges->findByIdOrFail($chargeId);
        $charge->loadMissing(['customer', 'paymentMethods', 'office']);

        if ($charge->customer === null) {
            throw BusinessRuleException::withMessage('Cobrança sem cliente vinculado.');
        }

        if (! $charge->customer->isActive()) {
            throw BusinessRuleException::withMessage('Cliente inativo — envio bloqueado.');
        }

        /** @var Setting|null $setting */
        $setting = $this->settings->first($charge->office_id);
        $companyName = $setting?->company_name ?? 'JM Contábil';
        $template = $setting?->default_message
            ?? (string) config('jmcontabil.message.default_template');
        $providerName = $setting?->whatsapp_provider->value
            ?? (string) config('jmcontabil.whatsapp.default_provider', 'fake');

        /** @var WhatsAppProviderInterface $whatsapp */
        $whatsapp = $this->providerResolver->resolve($providerName);

        $pixKey = $charge->paymentMethods->first()?->pixKey()
            ?? $charge->customer->pix_key;

        $amountFormatted = $charge->money()->formatBrl();
        $dueDateFormatted = $charge->due_date->format('d/m/Y');

        $message = $this->templates->render($template, [
            MessagePlaceholders::NAME => $charge->customer->name,
            MessagePlaceholders::AMOUNT => $amountFormatted,
            MessagePlaceholders::PIX => $pixKey,
            MessagePlaceholders::DUE_DATE => $dueDateFormatted,
            MessagePlaceholders::COMPANY => $companyName,
            MessagePlaceholders::COMPETENCE => $charge->reference_month,
        ]);

        /** @var ChargeDelivery $delivery */
        $delivery = $this->deliveries->create([
            'charge_id' => $charge->id,
            'channel' => DeliveryChannel::WhatsApp->value,
            'status' => DeliveryStatus::Sending->value,
            'message' => $message,
            'provider' => $providerName,
            'attempt' => $attempt,
        ]);

        $startedAt = hrtime(true);

        try {
            $result = $whatsapp->sendPixCharge(
                $charge->customer->phone,
                new PixChargeMessage(
                    customerName: $charge->customer->name,
                    amountFormatted: $amountFormatted,
                    dueDate: $dueDateFormatted,
                    pixKey: $pixKey,
                    messageBody: $message,
                )
            );

            $durationMs = $result->durationMs > 0
                ? $result->durationMs
                : (int) ((hrtime(true) - $startedAt) / 1_000_000);

            if (! $result->success) {
                $error = $result->errorMessage ?? 'Falha no envio WhatsApp.';

                $this->deliveries->updateStatus(
                    id: $delivery->id,
                    status: DeliveryStatus::Failed,
                    providerResponse: $result->rawResponse,
                    durationMs: $durationMs,
                    errorMessage: $error,
                );

                $this->failAttempt($charge->id, $error, $attempt, $maxAttempts, $durationMs);

                throw new WhatsAppSendException($error, $charge->id, $attempt);
            }

            $this->deliveries->updateStatus(
                id: $delivery->id,
                status: DeliveryStatus::Sent,
                providerResponse: $result->rawResponse,
                durationMs: $durationMs,
                errorMessage: null,
                providerMessageId: $result->providerMessageId,
            );

            $this->charges->markAsSent($charge->id, $message);

                Log::info('charges.notification.sent', [
                'charge_id' => $charge->id,
                'customer_id' => $charge->customer_id,
                'customer_name' => $charge->customer->name,
                'provider' => $whatsapp->driver(),
                'provider_message_id' => $result->providerMessageId,
                'duration_ms' => $durationMs,
                'attempt' => $attempt,
                'sent_at' => now()->toIso8601String(),
            ]);

            return ChargeData::fromModel($this->charges->findByIdOrFail($charge->id));
        } catch (WhatsAppSendException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            $durationMs = (int) ((hrtime(true) - $startedAt) / 1_000_000);

            $this->deliveries->updateStatus(
                id: $delivery->id,
                status: DeliveryStatus::Failed,
                providerResponse: null,
                durationMs: $durationMs,
                errorMessage: $exception->getMessage(),
            );

            $this->failAttempt(
                $charge->id,
                $exception->getMessage(),
                $attempt,
                $maxAttempts,
                $durationMs
            );

            throw new WhatsAppSendException(
                $exception->getMessage(),
                $charge->id,
                $attempt,
                $exception
            );
        }
    }

    private function failAttempt(
        int $chargeId,
        string $error,
        int $attempt,
        int $maxAttempts,
        int $durationMs,
    ): void {
        Log::warning('charges.notification.failed', [
            'charge_id' => $chargeId,
            'error' => $error,
            'duration_ms' => $durationMs,
            'attempt' => $attempt,
            'max_attempts' => $maxAttempts,
        ]);

        if ($attempt >= $maxAttempts) {
            $this->charges->markAsFailed($chargeId, $error);
        }
    }
}
