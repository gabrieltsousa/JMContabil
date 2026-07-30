<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Charge\Enums\ChargeStatus;
use App\Infrastructure\Persistence\Eloquent\Models\Charge;
use App\Infrastructure\Persistence\Eloquent\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Charge>
 */
final class ChargeFactory extends Factory
{
    protected $model = Charge::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dueDate = now()->startOfDay();

        return [
            'customer_id' => Customer::factory(),
            'office_id' => fn (array $attributes) => Customer::query()
                ->find($attributes['customer_id'])
                ?->office_id,
            'reference_month' => $dueDate->format('Y-m'),
            'amount' => fake()->numberBetween(15000, 80000),
            'status' => ChargeStatus::Pending,
            'due_date' => $dueDate->toDateString(),
            'sent_at' => null,
            'paid_at' => null,
            'message_sent' => null,
            'failure_reason' => null,
        ];
    }

    public function sent(): self
    {
        return $this->state(fn (): array => [
            'status' => ChargeStatus::Sent,
            'sent_at' => now(),
            'message_sent' => 'Mensagem de cobrança enviada.',
        ]);
    }

    public function paid(): self
    {
        return $this->state(fn (): array => [
            'status' => ChargeStatus::Paid,
            'sent_at' => now()->subDay(),
            'paid_at' => now(),
            'message_sent' => 'Mensagem de cobrança enviada.',
        ]);
    }

    public function failed(): self
    {
        return $this->state(fn (): array => [
            'status' => ChargeStatus::Failed,
            'failure_reason' => 'Falha no envio WhatsApp.',
        ]);
    }
}
