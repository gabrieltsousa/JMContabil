<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Infrastructure\Persistence\Eloquent\Models\Charge;
use App\Infrastructure\Persistence\Eloquent\Models\Customer;
use App\Infrastructure\Persistence\Eloquent\Models\Office;
use App\Infrastructure\Persistence\Eloquent\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ApiCustomerTest extends TestCase
{
    use RefreshDatabase;

    private function actingUser(?Office $office = null): User
    {
        $office ??= Office::factory()->create();
        Setting::factory()->create(['office_id' => $office->id]);

        $user = User::factory()->forOffice($office)->create([
            'email' => 'admin@jmcontabil.test',
            'password' => 'password',
        ]);

        Sanctum::actingAs($user);

        return $user;
    }

    #[Test]
    public function login_returns_token(): void
    {
        $office = Office::factory()->create();
        User::factory()->forOffice($office)->create([
            'email' => 'admin@jmcontabil.test',
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@jmcontabil.test',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'token_type', 'user' => ['id', 'email']]);
    }

    #[Test]
    public function can_crud_customers(): void
    {
        $user = $this->actingUser();

        $create = $this->postJson('/api/customers', [
            'name' => 'João',
            'phone' => '(11) 98888-7777',
            'email' => 'joao@example.com',
            'pix_key' => 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d',
            'monthly_value' => 350.00,
            'due_day' => 15,
        ]);

        $create->assertCreated()
            ->assertJsonPath('data.phone', '5511988887777')
            ->assertJsonPath('data.monthly_value', 35000);

        $id = $create->json('data.id');

        $this->getJson('/api/customers')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->putJson("/api/customers/{$id}", [
            'name' => 'João Atualizado',
            'status' => 'inactive',
        ])->assertOk()
            ->assertJsonPath('data.name', 'João Atualizado')
            ->assertJsonPath('data.status', 'inactive');

        $this->deleteJson("/api/customers/{$id}")->assertNoContent();

        $this->assertDatabaseMissing('customers', ['id' => $id]);
    }

    #[Test]
    public function validation_rejects_invalid_due_day(): void
    {
        $this->actingUser();

        $this->postJson('/api/customers', [
            'name' => 'João',
            'phone' => '11988887777',
            'pix_key' => 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d',
            'monthly_value' => 100,
            'due_day' => 31,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['due_day']);
    }

    #[Test]
    public function unauthenticated_requests_are_rejected(): void
    {
        $this->getJson('/api/customers')->assertUnauthorized();
    }

    #[Test]
    public function dashboard_and_settings_endpoints_work(): void
    {
        $this->actingUser();

        $this->getJson('/api/dashboard')->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'active_customers',
                    'inactive_customers',
                    'charges_sent_today',
                    'charges_pending',
                ],
            ]);

        $this->getJson('/api/settings')->assertOk()
            ->assertJsonStructure(['data' => ['company_name', 'default_message', 'whatsapp_provider']]);

        $this->putJson('/api/settings', [
            'company_name' => 'JM Contábil Atualizado',
        ])->assertOk()
            ->assertJsonPath('data.company_name', 'JM Contábil Atualizado');
    }

    #[Test]
    public function notifications_send_enqueues_job(): void
    {
        Queue::fake();

        $user = $this->actingUser();
        $customer = Customer::factory()->forOffice(
            Office::query()->findOrFail($user->office_id)
        )->create();

        $charge = Charge::factory()->create([
            'office_id' => $user->office_id,
            'customer_id' => $customer->id,
        ]);

        $this->postJson('/api/notifications/send', [
            'charge_id' => $charge->id,
            'async' => true,
        ])->assertAccepted()
            ->assertJsonPath('charge_id', $charge->id);

        Queue::assertPushed(\App\Application\Jobs\SendChargeWhatsAppJob::class);
    }
}
