<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\Charge\CreateChargeData;
use App\Application\Jobs\SendChargeWhatsAppJob;
use App\Domain\Charge\Contracts\ChargeRepositoryInterface;
use App\Domain\Customer\Contracts\CustomerRepositoryInterface;
use App\Domain\Customer\ValueObjects\DueDay;
use App\Infrastructure\Persistence\Eloquent\Models\Charge;
use App\Infrastructure\Persistence\Eloquent\Models\Customer;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use Illuminate\Support\Facades\Log;

/**
 * Processa cobranças do dia: localiza clientes, cria Charge e dispara envio.
 *
 * Com viaQueue=true (padrão na fila), cada envio vira SendChargeWhatsAppJob.
 * Com viaQueue=false (--sync), envia inline via ChargeNotificationService.
 */
final class DailyChargeService
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customers,
        private readonly ChargeRepositoryInterface $charges,
        private readonly ChargeNotificationService $notifications,
    ) {
    }

    /**
     * @return array{
     *     processed: int,
     *     created: int,
     *     skipped: int,
     *     queued: int,
     *     sent: int,
     *     failed: int,
     *     charge_ids: list<int>
     * }
     */
    public function process(
        ?int $officeId = null,
        ?DateTimeImmutable $now = null,
        bool $send = true,
        bool $viaQueue = true,
    ): array {
        $now ??= CarbonImmutable::now()->toDateTimeImmutable();
        $dayOfMonth = (int) $now->format('j');

        if ($dayOfMonth > (int) config('jmcontabil.charges.max_due_day', 28)) {
            Log::info('charges.daily.process.skipped_invalid_day', [
                'day' => $dayOfMonth,
            ]);

            return [
                'processed' => 0,
                'created' => 0,
                'skipped' => 0,
                'queued' => 0,
                'sent' => 0,
                'failed' => 0,
                'charge_ids' => [],
            ];
        }

        $dueDay = DueDay::from($dayOfMonth);

        /** @var list<Customer> $dueCustomers */
        $dueCustomers = $this->customers->findActiveByDueDay($dueDay, $officeId);

        $created = 0;
        $skipped = 0;
        $queued = 0;
        $sent = 0;
        $failed = 0;
        $chargeIds = [];

        Log::info('charges.daily.process.start', [
            'office_id' => $officeId,
            'due_day' => $dueDay->value(),
            'customers' => count($dueCustomers),
            'via_queue' => $viaQueue,
            'at' => $now->format(DateTimeImmutable::ATOM),
        ]);

        foreach ($dueCustomers as $customer) {
            $createData = CreateChargeData::fromCustomerSnapshot(
                customerId: $customer->id,
                officeId: $customer->office_id,
                amountCents: $customer->monthly_value,
                pixKey: $customer->pix_key,
                dueDay: $customer->due_day,
                now: $now,
            );

            $existing = $this->charges->findByCustomerAndReference(
                $customer->id,
                $createData->referenceMonth
            );

            if ($existing !== null) {
                $skipped++;
                $charge = $existing instanceof Charge
                    ? $existing
                    : Charge::query()->findOrFail($existing->id);

                if ($send && $charge->status->canBeSent()) {
                    $outcome = $this->dispatchSend($charge->id, $viaQueue);
                    $queued += $outcome['queued'];
                    $sent += $outcome['sent'];
                    $failed += $outcome['failed'];
                    $chargeIds[] = $charge->id;
                }

                continue;
            }

            /** @var Charge $charge */
            $charge = $this->charges->createWithPaymentMethod(
                $createData->toChargePersistenceArray(),
                [
                    'type' => $createData->paymentMethodType->value,
                    'amount' => $createData->amount->amountInCents(),
                    'payload' => $createData->paymentPayload,
                ]
            );

            $created++;
            $chargeIds[] = $charge->id;

            if (! $send) {
                continue;
            }

            $outcome = $this->dispatchSend($charge->id, $viaQueue);
            $queued += $outcome['queued'];
            $sent += $outcome['sent'];
            $failed += $outcome['failed'];
        }

        $summary = [
            'processed' => count($dueCustomers),
            'created' => $created,
            'skipped' => $skipped,
            'queued' => $queued,
            'sent' => $sent,
            'failed' => $failed,
            'charge_ids' => $chargeIds,
        ];

        Log::info('charges.daily.process.finished', $summary);

        return $summary;
    }

    /**
     * @return array{queued: int, sent: int, failed: int}
     */
    private function dispatchSend(int $chargeId, bool $viaQueue): array
    {
        if ($viaQueue) {
            SendChargeWhatsAppJob::dispatch($chargeId);

            return ['queued' => 1, 'sent' => 0, 'failed' => 0];
        }

        try {
            $this->notifications->send($chargeId);

            return ['queued' => 0, 'sent' => 1, 'failed' => 0];
        } catch (\Throwable $exception) {
            Log::error('charges.daily.send_failed', [
                'charge_id' => $chargeId,
                'error' => $exception->getMessage(),
            ]);

            return ['queued' => 0, 'sent' => 0, 'failed' => 1];
        }
    }
}
