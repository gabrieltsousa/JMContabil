<?php

declare(strict_types=1);

namespace App\Application\DTOs\Dashboard;

use App\Application\DTOs\DataTransferObject;

/**
 * Métricas do dashboard.
 */
final readonly class DashboardStatsData implements DataTransferObject
{
    public function __construct(
        public int $activeCustomers,
        public int $inactiveCustomers,
        public int $chargesSentToday,
        public int $chargesPending,
        public int $chargesSentThisMonth,
        public int $chargesFailed,
        public int $chargesPaidThisMonth = 0,
        public ?int $officeId = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            activeCustomers: (int) ($data['active_customers'] ?? 0),
            inactiveCustomers: (int) ($data['inactive_customers'] ?? 0),
            chargesSentToday: (int) ($data['charges_sent_today'] ?? 0),
            chargesPending: (int) ($data['charges_pending'] ?? 0),
            chargesSentThisMonth: (int) ($data['charges_sent_this_month'] ?? 0),
            chargesFailed: (int) ($data['charges_failed'] ?? 0),
            chargesPaidThisMonth: (int) ($data['charges_paid_this_month'] ?? 0),
            officeId: isset($data['office_id']) ? (int) $data['office_id'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'active_customers' => $this->activeCustomers,
            'inactive_customers' => $this->inactiveCustomers,
            'charges_sent_today' => $this->chargesSentToday,
            'charges_pending' => $this->chargesPending,
            'charges_sent_this_month' => $this->chargesSentThisMonth,
            'charges_failed' => $this->chargesFailed,
            'charges_paid_this_month' => $this->chargesPaidThisMonth,
            'office_id' => $this->officeId,
        ];
    }
}
