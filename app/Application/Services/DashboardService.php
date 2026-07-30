<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\Dashboard\DashboardStatsData;
use App\Domain\Charge\Contracts\ChargeRepositoryInterface;
use App\Domain\Charge\Enums\ChargeStatus;
use App\Domain\Charge\ValueObjects\ReferenceMonth;
use App\Domain\Customer\Contracts\CustomerRepositoryInterface;
use App\Domain\Customer\Enums\CustomerStatus;
use Carbon\CarbonImmutable;
use DateTimeImmutable;

final class DashboardService
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customers,
        private readonly ChargeRepositoryInterface $charges,
    ) {
    }

    public function stats(?int $officeId = null, ?DateTimeImmutable $now = null): DashboardStatsData
    {
        $now ??= CarbonImmutable::now()->toDateTimeImmutable();
        $reference = ReferenceMonth::fromDate($now);

        return new DashboardStatsData(
            activeCustomers: $this->customers->countByStatus(CustomerStatus::Active, $officeId),
            inactiveCustomers: $this->customers->countByStatus(CustomerStatus::Inactive, $officeId),
            chargesSentToday: $this->charges->countSentToday($officeId),
            chargesPending: $this->charges->countPending($officeId),
            chargesSentThisMonth: $this->charges->countSentInMonth($reference, $officeId),
            chargesFailed: $this->charges->countByStatus(ChargeStatus::Failed, $officeId),
            chargesPaidThisMonth: $this->charges->countPaidInMonth($reference, $officeId),
            officeId: $officeId,
        );
    }
}
