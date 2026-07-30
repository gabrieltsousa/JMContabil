<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Charge\Enums\DeliveryChannel;
use App\Domain\Charge\Enums\DeliveryStatus;
use App\Domain\Settings\Enums\WhatsAppProvider;
use App\Infrastructure\Persistence\Eloquent\Models\Charge;
use App\Infrastructure\Persistence\Eloquent\Models\ChargeDelivery;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChargeDelivery>
 */
final class ChargeDeliveryFactory extends Factory
{
    protected $model = ChargeDelivery::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'charge_id' => Charge::factory(),
            'channel' => DeliveryChannel::WhatsApp,
            'status' => DeliveryStatus::Queued,
            'message' => 'Olá. Sua mensalidade venceu hoje.',
            'provider' => WhatsAppProvider::Fake->value,
            'provider_message_id' => null,
            'provider_response' => null,
            'error_message' => null,
            'duration_ms' => 0,
            'attempt' => 1,
            'sent_at' => null,
            'whatsapp_response' => null,
        ];
    }

    public function sent(): self
    {
        return $this->state(fn (): array => [
            'status' => DeliveryStatus::Sent,
            'provider_message_id' => 'fake_'.fake()->uuid(),
            'duration_ms' => fake()->numberBetween(10, 250),
            'sent_at' => now(),
        ]);
    }

    public function failed(): self
    {
        return $this->state(fn (): array => [
            'status' => DeliveryStatus::Failed,
            'error_message' => 'Provider timeout',
            'duration_ms' => fake()->numberBetween(1000, 5000),
        ]);
    }
}
