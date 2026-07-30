<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\Settings\SettingsData;
use App\Application\DTOs\Settings\UpdateSettingsData;
use App\Domain\Settings\Contracts\SettingRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Setting;

final class SettingsService
{
    public function __construct(
        private readonly SettingRepositoryInterface $settings,
    ) {
    }

    public function get(?int $officeId = null): SettingsData
    {
        /** @var Setting $setting */
        $setting = $this->settings->firstOrFail($officeId);

        return SettingsData::fromModel($setting);
    }

    public function update(?int $officeId, UpdateSettingsData $data): SettingsData
    {
        /** @var Setting|null $existing */
        $existing = $this->settings->first($officeId);

        $attributes = array_merge(
            [
                'company_name' => $existing?->company_name ?? 'JM Contábil',
                'default_message' => $existing?->default_message
                    ?? (string) config('jmcontabil.message.default_template'),
                'whatsapp_provider' => $existing?->whatsapp_provider->value ?? 'fake',
                'timezone' => $existing?->timezone ?? 'America/Sao_Paulo',
            ],
            $data->toPersistenceArray()
        );

        /** @var Setting $setting */
        $setting = $this->settings->updateOrCreate($officeId, $attributes);

        return SettingsData::fromModel($setting);
    }
}
