<?php

declare(strict_types=1);

namespace App\Application\Actions\Charge;

use App\Application\Actions\ActionInterface;
use App\Application\Jobs\ProcessDailyChargesJob;
use Illuminate\Support\Facades\Log;

/**
 * Action disparada pelo Scheduler.
 * Única responsabilidade: enfileirar ProcessDailyChargesJob.
 *
 * @implements ActionInterface<void>
 */
final class DispatchDailyChargesAction implements ActionInterface
{
    public function execute(mixed ...$args): mixed
    {
        $officeId = array_key_exists(0, $args) && $args[0] !== null
            ? (int) $args[0]
            : null;

        Log::info('charges.daily.dispatch', [
            'office_id' => $officeId,
            'dispatched_at' => now()->toIso8601String(),
        ]);

        ProcessDailyChargesJob::dispatch($officeId);

        return null;
    }
}
