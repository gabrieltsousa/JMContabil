<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Settings\Enums\WhatsAppProvider;
use App\Infrastructure\Persistence\Eloquent\Models\Office;
use App\Infrastructure\Persistence\Eloquent\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setting>
 */
final class SettingFactory extends Factory
{
    protected $model = Setting::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'office_id' => Office::factory(),
            'company_name' => fake()->company(),
            'default_message' => (string) config('jmcontabil.message.default_template'),
            'whatsapp_provider' => WhatsAppProvider::Fake,
            'timezone' => 'America/Sao_Paulo',
        ];
    }
}
