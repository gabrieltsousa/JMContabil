<?php

declare(strict_types=1);

namespace App\Application\Jobs;

use App\Application\Services\DailyChargeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job diário: cria cobranças e enfileira envios individuais.
 */
final class ProcessDailyChargesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [30, 60, 120];

    public function __construct(
        public readonly ?int $officeId = null,
    ) {
        $this->onQueue((string) config('jmcontabil.whatsapp.charges_queue', 'charges'));
    }

    public function handle(DailyChargeService $dailyCharges): void
    {
        Log::info('jobs.process_daily_charges.started', [
            'office_id' => $this->officeId,
            'attempt' => $this->attempts(),
        ]);

        $summary = $dailyCharges->process(
            officeId: $this->officeId,
            send: true,
            viaQueue: true,
        );

        Log::info('jobs.process_daily_charges.finished', $summary);
    }

    public function failed(?\Throwable $exception): void
    {
        Log::error('jobs.process_daily_charges.failed', [
            'office_id' => $this->officeId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
