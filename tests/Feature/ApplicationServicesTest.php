<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Application\DTOs\Customer\CreateCustomerData;
use App\Application\Services\ChargeNotificationService;
use App\Application\Services\CustomerService;
use App\Application\Services\DailyChargeService;
use App\Application\Services\DashboardService;
use App\Domain\Charge\Enums\ChargeStatus;
use App\Domain\Charge\Enums\DeliveryStatus;
use App\Infrastructure\Persistence\Eloquent\Models\Charge;
use App\Infrastructure\Persistence\Eloquent\Models\Customer;
use App\Infrastructure\Persistence\Eloquent\Models\Office;
use App\Infrastructure\Persistence\Eloquent\Models\Setting;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ApplicationServicesTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function customer_service_creates_normalized_customer(): void
    {
        $office = Office::factory()->create();

        /** @var CustomerService $service */
        $service = $this->app->make(CustomerService::class);

        $customer = $service->create(CreateCustomerData::fromArray([
            'office_id' => $office->id,
            'name' => 'João Silva',
            'phone' => '(11) 98888-7777',
            'email' => 'joao@example.com',
            'pix_key' => 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d',
            'monthly_value' => 350.00,
            'due_day' => 15,
        ]));

        $this->assertSame('5511988887777', $customer->phone);
        $this->assertSame(35000, $customer->monthlyValueCents);
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'phone' => '5511988887777',
        ]);
    }

    #[Test]
    public function daily_charge_service_creates_and_sends_for_due_customers(): void
    {
        $office = Office::factory()->create();
        Setting::factory()->create([
            'office_id' => $office->id,
            'company_name' => 'JM Contábil',
            'default_message' => 'Olá {nome}. Valor: {valor}. PIX: {pix}. Data: {data}.',
        ]);

        Customer::factory()->forOffice($office)->dueOn(15)->create([
            'name' => 'Maria',
            'monthly_value' => 35000,
            'pix_key' => 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d',
            'phone' => '5511999998888',
        ]);
        Customer::factory()->forOffice($office)->dueOn(20)->create();

        /** @var DailyChargeService $service */
        $service = $this->app->make(DailyChargeService::class);

        $summary = $service->process(
            officeId: $office->id,
            now: new DateTimeImmutable('2026-07-15'),
            send: true,
            viaQueue: false,
        );

        $this->assertSame(1, $summary['processed']);
        $this->assertSame(1, $summary['created']);
        $this->assertSame(1, $summary['sent']);
        $this->assertSame(0, $summary['failed']);

        $this->assertDatabaseHas('charges', [
            'reference_month' => '2026-07',
            'status' => ChargeStatus::Sent->value,
            'amount' => 35000,
        ]);
        $this->assertDatabaseHas('charge_deliveries', [
            'status' => DeliveryStatus::Sent->value,
            'provider' => 'fake',
        ]);
    }

    #[Test]
    public function daily_charge_service_skips_duplicate_competence(): void
    {
        $office = Office::factory()->create();
        Setting::factory()->create(['office_id' => $office->id]);

        $customer = Customer::factory()->forOffice($office)->dueOn(15)->create([
            'monthly_value' => 20000,
        ]);

        Charge::factory()->create([
            'office_id' => $office->id,
            'customer_id' => $customer->id,
            'reference_month' => '2026-07',
            'amount' => 20000,
            'status' => ChargeStatus::Sent,
            'due_date' => '2026-07-15',
            'sent_at' => now(),
        ]);

        /** @var DailyChargeService $service */
        $service = $this->app->make(DailyChargeService::class);

        $summary = $service->process(
            officeId: $office->id,
            now: new DateTimeImmutable('2026-07-15'),
            send: true,
            viaQueue: false,
        );

        $this->assertSame(1, $summary['skipped']);
        $this->assertSame(0, $summary['created']);
        $this->assertSame(1, Charge::query()->count());
    }

    #[Test]
    public function charge_notification_service_sends_existing_charge(): void
    {
        $office = Office::factory()->create();
        Setting::factory()->create([
            'office_id' => $office->id,
            'default_message' => 'Oi {nome} — {valor} — {pix}',
        ]);

        $customer = Customer::factory()->forOffice($office)->create([
            'name' => 'Pedro',
            'pix_key' => 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d',
        ]);

        $charge = Charge::factory()->create([
            'office_id' => $office->id,
            'customer_id' => $customer->id,
            'amount' => 15000,
            'status' => ChargeStatus::Pending,
        ]);

        $charge->paymentMethods()->create([
            'type' => 'pix_key',
            'amount' => 15000,
            'payload' => ['pix_key' => $customer->pix_key],
        ]);

        /** @var ChargeNotificationService $service */
        $service = $this->app->make(ChargeNotificationService::class);

        $result = $service->send($charge->id);

        $this->assertSame(ChargeStatus::Sent, $result->status);
        $this->assertStringContainsString('Pedro', (string) $result->messageSent);
        $this->assertStringContainsString('R$ 150,00', (string) $result->messageSent);
    }

    #[Test]
    public function dashboard_service_returns_counts(): void
    {
        $office = Office::factory()->create();
        Customer::factory()->forOffice($office)->count(2)->create();
        Customer::factory()->forOffice($office)->inactive()->create();
        Charge::factory()->sent()->create([
            'office_id' => $office->id,
            'customer_id' => Customer::factory()->forOffice($office),
            'sent_at' => now(),
            'reference_month' => now()->format('Y-m'),
        ]);

        /** @var DashboardService $service */
        $service = $this->app->make(DashboardService::class);

        $stats = $service->stats($office->id);

        $this->assertSame(3, $stats->activeCustomers); // 2 + 1 from charge factory customer
        $this->assertSame(1, $stats->inactiveCustomers);
        $this->assertGreaterThanOrEqual(1, $stats->chargesSentToday);
    }
}
