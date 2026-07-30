<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Charge\Enums\PaymentMethodType;
use App\Infrastructure\Persistence\Eloquent\Models\Charge;
use App\Infrastructure\Persistence\Eloquent\Models\ChargePaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChargePaymentMethod>
 */
final class ChargePaymentMethodFactory extends Factory
{
    protected $model = ChargePaymentMethod::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $pixKey = fake()->uuid();

        return [
            'charge_id' => Charge::factory(),
            'type' => PaymentMethodType::PixKey,
            'amount' => fn (array $attributes) => Charge::query()
                ->find($attributes['charge_id'])
                ?->amount ?? 35000,
            'payload' => [
                'pix_key' => $pixKey,
            ],
        ];
    }
}
