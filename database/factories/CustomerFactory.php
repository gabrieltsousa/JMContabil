<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Customer\Enums\CustomerStatus;
use App\Infrastructure\Persistence\Eloquent\Models\Customer;
use App\Infrastructure\Persistence\Eloquent\Models\Office;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
final class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'office_id' => Office::factory(),
            'name' => fake()->name(),
            'phone' => '5511'.fake()->numerify('9########'),
            'email' => fake()->unique()->safeEmail(),
            'pix_key' => fake()->uuid(),
            'monthly_value' => fake()->numberBetween(15000, 80000),
            'due_day' => fake()->numberBetween(1, 28),
            'status' => CustomerStatus::Active,
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn (): array => [
            'status' => CustomerStatus::Inactive,
        ]);
    }

    public function dueOn(int $day): self
    {
        return $this->state(fn (): array => [
            'due_day' => $day,
        ]);
    }

    public function forOffice(Office|int $office): self
    {
        return $this->state(fn (): array => [
            'office_id' => $office instanceof Office ? $office->id : $office,
        ]);
    }
}
