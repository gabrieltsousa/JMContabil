<?php

declare(strict_types=1);

namespace App\Application\Jobs;

use App\Application\Events\ChargeWhatsAppFailed;
use App\Application\Events\ChargeWhatsAppSent;
use App\Application\Services\ChargeNotificationService;
use App\Domain\Charge\Contracts\ChargeRepositoryInterface;
use App\Domain\Shared\Exceptions\BusinessRuleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Envia uma cobrança via WhatsApp.
 * Até 3 tentativas; após esgotar, marca charge como failed.
 */
final class SendChargeWhatsAppJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries;

    /** @var list<int> */
    public array $backoff = [10, 30, 60];

    public function __construct(
        public readonly int $chargeId,
    ) {
        $this->tries = (int) config('jmcontabil.whatsapp.max_tries', 3);
        $this->onQueue((string) config('jmcontabil.whatsapp.queue', 'whatsapp'));
    }

    public function handle(ChargeNotificationService $notifications): void
    {
        Log::info('jobs.send_charge_whatsapp.started', [
            'charge_id' => $this->chargeId,
            'attempt' => $this->attempts(),
        ]);

        try {
            $result = $notifications->send(
                chargeId: $this->chargeId,
                attempt: $this->attempts(),
                maxAttempts: $this->tries,
            );

            event(new ChargeWhatsAppSent(
                chargeId: $this->chargeId,
                message: (string) $result->messageSent,
                attempt: $this->attempts(),
            ));
        } catch (BusinessRuleException $exception) {
            Log::warning('jobs.send_charge_whatsapp.business_rule', [
                'charge_id' => $this->chargeId,
                'error' => $exception->getMessage(),
            ]);

            app(ChargeRepositoryInterface::class)->markAsFailed(
                $this->chargeId,
                $exception->getMessage()
            );

            event(new ChargeWhatsAppFailed(
                chargeId: $this->chargeId,
                error: $exception->getMessage(),
                attempt: $this->attempts(),
                final: true,
            ));

            $this->fail($exception);
        }
    }

    public function failed(?Throwable $exception): void
    {
        $message = $exception?->getMessage() ?? 'Falha desconhecida no envio WhatsApp.';

        Log::error('jobs.send_charge_whatsapp.exhausted', [
            'charge_id' => $this->chargeId,
            'error' => $message,
            'tries' => $this->tries,
        ]);

        app(ChargeRepositoryInterface::class)->markAsFailed($this->chargeId, $message);

        event(new ChargeWhatsAppFailed(
            chargeId: $this->chargeId,
            error: $message,
            attempt: $this->tries,
            final: true,
        ));
    }

    public function tags(): array
    {
        return [
            'whatsapp',
            'charge:'.$this->chargeId,
        ];
    }
}
