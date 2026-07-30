<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Domain\Charge\Enums\ChargeStatus;
use App\Domain\Charge\Enums\DeliveryStatus;
use App\Infrastructure\Persistence\Eloquent\Models\Charge;
use App\Infrastructure\Persistence\Eloquent\Models\ChargeDelivery;
use App\Infrastructure\Persistence\Eloquent\Models\Customer;
use App\Infrastructure\Persistence\Eloquent\Models\Office;
use App\Infrastructure\Persistence\Eloquent\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ApiNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function actingUser(): User
    {
        $office = Office::factory()->create();
        Setting::factory()->create([
            'office_id' => $office->id,
            'default_message' => 'Olá {nome}. Valor {valor}. PIX {pix}.',
        ]);

        $user = User::factory()->forOffice($office)->create();
        Sanctum::actingAs($user);

        return $user;
    }

    #[Test]
    public function can_list_notifications_with_filters(): void
    {
        $user = $this->actingUser();
        $customer = Customer::factory()->forOffice(
            Office::query()->findOrFail($user->office_id)
        )->create(['name' => 'Cliente Filtro']);

        $charge = Charge::factory()->create([
            'office_id' => $user->office_id,
            'customer_id' => $customer->id,
            'amount' => 20000,
        ]);

        ChargeDelivery::factory()->sent()->create([
            'charge_id' => $charge->id,
            'status' => DeliveryStatus::Sent,
            'sent_at' => now(),
        ]);

        ChargeDelivery::factory()->failed()->create([
            'charge_id' => $charge->id,
        ]);

        $this->getJson('/api/notifications?status=sent')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.customer_name', 'Cliente Filtro');
    }

    #[Test]
    public function sync_send_updates_charge_status(): void
    {
        $user = $this->actingUser();
        $customer = Customer::factory()->forOffice(
            Office::query()->findOrFail($user->office_id)
        )->create([
            'name' => 'Sync User',
            'pix_key' => 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d',
        ]);

        $charge = Charge::factory()->create([
            'office_id' => $user->office_id,
            'customer_id' => $customer->id,
            'amount' => 12300,
            'status' => ChargeStatus::Pending,
        ]);

        $charge->paymentMethods()->create([
            'type' => 'pix_key',
            'amount' => 12300,
            'payload' => ['pix_key' => $customer->pix_key],
        ]);

        $this->postJson('/api/notifications/send', [
            'charge_id' => $charge->id,
            'async' => false,
        ])->assertOk()
            ->assertJsonPath('data.status', 'sent');

        $this->assertDatabaseHas('charges', [
            'id' => $charge->id,
            'status' => ChargeStatus::Sent->value,
        ]);
    }
}
