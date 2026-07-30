<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Application\Actions\Charge\DispatchDailyChargesAction;
use App\Application\Services\DailyChargeService;
use Illuminate\Console\Command;
use Throwable;

/**
 * Dispara o processamento diário de cobranças.
 *
 * Usado pelo Scheduler e também manualmente (ops/debug).
 * Sem regra de negócio — apenas delega para Action/Service.
 */
final class DispatchDailyChargesCommand extends Command
{
    protected $signature = 'charges:dispatch-daily
                            {--office= : ID do escritório (opcional)}
                            {--sync : Processa imediatamente sem enfileirar}';

    protected $description = 'Dispara a cobrança diária dos clientes com vencimento no dia';

    public function handle(
        DispatchDailyChargesAction $action,
        DailyChargeService $dailyChargeService,
    ): int {
        $officeOption = $this->option('office');
        $officeId = $officeOption !== null && $officeOption !== ''
            ? (int) $officeOption
            : null;

        $this->info(sprintf(
            'Disparando cobranças diárias%s...',
            $officeId !== null ? " (office #{$officeId})" : ''
        ));

        try {
            if ((bool) $this->option('sync')) {
                $summary = $dailyChargeService->process(
                    officeId: $officeId,
                    send: true,
                    viaQueue: false,
                );

                $this->table(
                    ['Métrica', 'Valor'],
                    [
                        ['Processados', $summary['processed']],
                        ['Criados', $summary['created']],
                        ['Ignorados', $summary['skipped']],
                        ['Enfileirados', $summary['queued']],
                        ['Enviados', $summary['sent']],
                        ['Falhas', $summary['failed']],
                    ]
                );

                $this->info('Processamento síncrono concluído.');

                return self::SUCCESS;
            }

            $action->execute($officeId);

            $this->info('Job de cobrança diária enfileirado com sucesso.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error('Falha ao disparar cobranças: '.$exception->getMessage());

            report($exception);

            return self::FAILURE;
        }
    }
}
