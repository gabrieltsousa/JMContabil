<?php

declare(strict_types=1);

namespace App\Application\DTOs\Settings;

use App\Application\DTOs\DataTransferObject;
use App\Domain\Settings\Enums\WhatsAppProvider;

final readonly class UpdateSettingsData implements DataTransferObject
{
    /**
     * @param  array<string, true>  $presentKeys
     */
    public function __construct(
        public ?string $companyName = null,
        public ?string $defaultMessage = null,
        public ?WhatsAppProvider $whatsappProvider = null,
        public ?string $timezone = null,
        private array $presentKeys = [],
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $presentKeys = [];
        foreach (array_keys($data) as $key) {
            $presentKeys[(string) $key] = true;
        }

        return new self(
            companyName: array_key_exists('company_name', $data)
                ? trim((string) $data['company_name'])
                : null,
            defaultMessage: array_key_exists('default_message', $data)
                ? (string) $data['default_message']
                : null,
            whatsappProvider: array_key_exists('whatsapp_provider', $data)
                ? WhatsAppProvider::from((string) $data['whatsapp_provider'])
                : null,
            timezone: array_key_exists('timezone', $data)
                ? trim((string) $data['timezone'])
                : null,
            presentKeys: $presentKeys,
        );
    }

    public function has(string $key): bool
    {
        return isset($this->presentKeys[$key]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->toPersistenceArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistenceArray(): array
    {
        $attributes = [];

        if ($this->has('company_name') && $this->companyName !== null) {
            $attributes['company_name'] = $this->companyName;
        }

        if ($this->has('default_message') && $this->defaultMessage !== null) {
            $attributes['default_message'] = $this->defaultMessage;
        }

        if ($this->has('whatsapp_provider') && $this->whatsappProvider !== null) {
            $attributes['whatsapp_provider'] = $this->whatsappProvider->value;
        }

        if ($this->has('timezone') && $this->timezone !== null) {
            $attributes['timezone'] = $this->timezone;
        }

        return $attributes;
    }
}
