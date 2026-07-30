<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use App\Application\DTOs\Charge\CreateChargeData;
use App\Application\DTOs\Customer\CreateCustomerData;
use App\Application\DTOs\Customer\CustomerData;
use App\Application\DTOs\Customer\UpdateCustomerData;
use App\Application\DTOs\Dashboard\DashboardStatsData;
use App\Application\DTOs\Settings\UpdateSettingsData;
use App\Domain\Customer\Enums\CustomerStatus;
use App\Domain\Settings\Enums\WhatsAppProvider;
use App\Domain\Shared\Exceptions\InvalidValueObjectException;
use App\Infrastructure\Persistence\Eloquent\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class DtoTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function create_customer_data_normalizes_phone_and_money(): void
    {
        $dto = CreateCustomerData::fromArray([
            'name' => ' João ',
            'phone' => '(11) 98888-7777',
            'email' => 'joao@example.com',
            'pix_key' => 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d',
            'monthly_value' => 350.00,
            'due_day' => 15,
            'status' => 'active',
        ]);

        $this->assertSame('João', $dto->name);
        $this->assertSame('5511988887777', $dto->phone->value());
        $this->assertSame(35000, $dto->monthlyValue->amountInCents());
        $this->assertSame(CustomerStatus::Active, $dto->status);
        $this->assertSame(35000, $dto->toPersistenceArray()['monthly_value']);
    }

    #[Test]
    public function create_customer_data_rejects_invalid_pix(): void
    {
        $this->expectException(InvalidValueObjectException::class);

        CreateCustomerData::fromArray([
            'name' => 'João',
            'phone' => '11988887777',
            'pix_key' => 'invalido',
            'monthly_value' => 100,
            'due_day' => 10,
        ]);
    }

    #[Test]
    public function update_customer_data_only_includes_present_fields(): void
    {
        $dto = UpdateCustomerData::fromArray([
            'name' => 'Maria',
            'status' => 'inactive',
        ]);

        $this->assertSame([
            'name' => 'Maria',
            'status' => 'inactive',
        ], $dto->toPersistenceArray());
    }

    #[Test]
    public function customer_data_from_model_includes_formatted_money(): void
    {
        $customer = Customer::factory()->create([
            'monthly_value' => 35000,
            'status' => CustomerStatus::Active,
        ]);

        $dto = CustomerData::fromModel($customer);

        $this->assertSame('R$ 350,00', $dto->toArray()['monthly_value_formatted']);
        $this->assertSame('active', $dto->toArray()['status']);
    }

    #[Test]
    public function create_charge_data_from_customer_snapshot(): void
    {
        $dto = CreateChargeData::fromCustomerSnapshot(
            customerId: 1,
            officeId: 1,
            amountCents: 35000,
            pixKey: 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d',
            dueDay: 15,
            now: new \DateTimeImmutable('2026-07-15'),
        );

        $this->assertSame('2026-07', $dto->referenceMonth->value());
        $this->assertSame('2026-07-15', $dto->dueDate->format('Y-m-d'));
        $this->assertSame(
            'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d',
            $dto->paymentPayload['pix_key']
        );
    }

    #[Test]
    public function update_settings_data_maps_provider_enum(): void
    {
        $dto = UpdateSettingsData::fromArray([
            'company_name' => 'JM Contábil',
            'whatsapp_provider' => 'fake',
        ]);

        $this->assertSame(WhatsAppProvider::Fake, $dto->whatsappProvider);
        $this->assertSame('fake', $dto->toPersistenceArray()['whatsapp_provider']);
    }

    #[Test]
    public function dashboard_stats_data_serializes_metrics(): void
    {
        $dto = DashboardStatsData::fromArray([
            'active_customers' => 10,
            'inactive_customers' => 2,
            'charges_sent_today' => 3,
            'charges_pending' => 1,
            'charges_sent_this_month' => 20,
            'charges_failed' => 0,
        ]);

        $this->assertSame(10, $dto->toArray()['active_customers']);
        $this->assertSame(3, $dto->toArray()['charges_sent_today']);
    }
}
