<?php

declare(strict_types=1);

namespace App\Application\Listeners;

use App\Application\Events\ChargeWhatsAppFailed;
use App\Application\Events\ChargeWhatsAppSent;
use Illuminate\Support\Facades\Log;

final class LogChargeWhatsAppOutcome
{
    public function handleSent(ChargeWhatsAppSent $event): void
    {
        Log::info('events.charge_whatsapp_sent', [
            'charge_id' => $event->chargeId,
            'attempt' => $event->attempt,
            'message_preview' => mb_substr($event->message, 0, 120),
            'at' => now()->toIso8601String(),
        ]);
    }

    public function handleFailed(ChargeWhatsAppFailed $event): void
    {
        Log::warning('events.charge_whatsapp_failed', [
            'charge_id' => $event->chargeId,
            'attempt' => $event->attempt,
            'final' => $event->final,
            'error' => $event->error,
            'at' => now()->toIso8601String(),
        ]);
    }
}
