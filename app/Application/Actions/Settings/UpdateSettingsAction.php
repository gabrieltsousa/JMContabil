<?php

declare(strict_types=1);

namespace App\Application\Actions\Settings;

use App\Application\Actions\ActionInterface;
use App\Application\DTOs\Settings\SettingsData;
use App\Application\DTOs\Settings\UpdateSettingsData;
use App\Application\Services\SettingsService;

/**
 * @implements ActionInterface<SettingsData>
 */
final class UpdateSettingsAction implements ActionInterface
{
    public function __construct(
        private readonly SettingsService $settings,
    ) {
    }

    public function execute(mixed ...$args): SettingsData
    {
        $officeId = $args[0] ?? null;
        $officeId = $officeId !== null ? (int) $officeId : null;

        /** @var UpdateSettingsData $data */
        $data = $args[1];

        return $this->settings->update($officeId, $data);
    }
}
