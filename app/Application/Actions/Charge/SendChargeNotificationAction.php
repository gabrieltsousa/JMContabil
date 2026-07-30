<?php

declare(strict_types=1);

namespace App\Application\Actions\Charge;

use App\Application\Actions\ActionInterface;
use App\Application\DTOs\Charge\ChargeData;
use App\Application\Services\ChargeNotificationService;

/**
 * Envio manual de cobrança (API POST /notifications/send).
 *
 * @implements ActionInterface<ChargeData>
 */
final class SendChargeNotificationAction implements ActionInterface
{
    public function __construct(
        private readonly ChargeNotificationService $notifications,
    ) {
    }

    public function execute(mixed ...$args): ChargeData
    {
        /** @var int $chargeId */
        $chargeId = $args[0];
        $attempt = isset($args[1]) ? (int) $args[1] : 1;

        return $this->notifications->send($chargeId, $attempt);
    }
}
