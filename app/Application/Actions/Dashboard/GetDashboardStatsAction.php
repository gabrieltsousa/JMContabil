<?php

declare(strict_types=1);

namespace App\Application\Actions\Dashboard;

use App\Application\Actions\ActionInterface;
use App\Application\DTOs\Dashboard\DashboardStatsData;
use App\Application\Services\DashboardService;

/**
 * @implements ActionInterface<DashboardStatsData>
 */
final class GetDashboardStatsAction implements ActionInterface
{
    public function __construct(
        private readonly DashboardService $dashboard,
    ) {
    }

    public function execute(mixed ...$args): DashboardStatsData
    {
        $officeId = array_key_exists(0, $args) && $args[0] !== null
            ? (int) $args[0]
            : null;

        return $this->dashboard->stats($officeId);
    }
}
