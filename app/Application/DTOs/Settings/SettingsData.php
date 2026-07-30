<?php

declare(strict_types=1);

namespace App\Application\DTOs\Settings;

use App\Application\DTOs\DataTransferObject;
use App\Domain\Settings\Enums\WhatsAppProvider;
use App\Infrastructure\Persistence\Eloquent\Models\Setting;

final readonly class SettingsData implements DataTransferObject
{
    public function __construct(
        public int $id,
        public ?int $officeId,
        public string $companyName,
        public string $defaultMessage,
        public WhatsAppProvider $whatsappProvider,
        public string $timezone,
        public ?string $updatedAt = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            officeId: isset($data['office_id']) ? (int) $data['office_id'] : null,
            companyName: (string) $data['company_name'],
            defaultMessage: (string) $data['default_message'],
            whatsappProvider: $data['whatsapp_provider'] instanceof WhatsAppProvider
                ? $data['whatsapp_provider']
                : WhatsAppProvider::from((string) $data['whatsapp_provider']),
            timezone: (string) $data['timezone'],
            updatedAt: isset($data['updated_at']) ? (string) $data['updated_at'] : null,
        );
    }

    public static function fromModel(Setting $setting): self
    {
        return new self(
            id: $setting->id,
            officeId: $setting->office_id,
            companyName: $setting->company_name,
            defaultMessage: $setting->default_message,
            whatsappProvider: $setting->whatsapp_provider,
            timezone: $setting->timezone,
            updatedAt: $setting->updated_at?->toIso8601String(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'office_id' => $this->officeId,
            'company_name' => $this->companyName,
            'default_message' => $this->defaultMessage,
            'whatsapp_provider' => $this->whatsappProvider->value,
            'whatsapp_provider_label' => $this->whatsappProvider->label(),
            'timezone' => $this->timezone,
            'updated_at' => $this->updatedAt,
        ];
    }
}
