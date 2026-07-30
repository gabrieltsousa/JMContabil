<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Charge\Contracts\ChargeDeliveryRepositoryInterface;
use App\Domain\Charge\Contracts\ChargeRepositoryInterface;
use App\Domain\Charge\Enums\ChargeStatus;
use App\Domain\Charge\Enums\DeliveryStatus;
use App\Domain\Charge\Enums\PaymentMethodType;
use App\Domain\Charge\ValueObjects\ReferenceMonth;
use App\Domain\Customer\Contracts\CustomerRepositoryInterface;
use App\Domain\Customer\Enums\CustomerStatus;
use App\Domain\Customer\ValueObjects\DueDay;
use App\Domain\Settings\Contracts\SettingRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Charge;
use App\Infrastructure\Persistence\Eloquent\Models\Customer;
use App\Infrastructure\Persistence\Eloquent\Models\Office;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RepositoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function customer_repository_finds_active_by_due_day(): void
    {
        $office = Office::factory()->create();

        Customer::factory()->forOffice($office)->dueOn(15)->create(['name' => 'Ativo']);
        Customer::factory()->forOffice($office)->dueOn(15)->inactive()->create();
        Customer::factory()->forOffice($office)->dueOn(20)->create();

        /** @var CustomerRepositoryInterface $repository */
        $repository = $this->app->make(CustomerRepositoryInterface::class);

        $result = $repository->findActiveByDueDay(DueDay::from(15), $office->id);

        $this->assertCount(1, $result);
        $this->assertSame('Ativo', $result[0]->name);
        $this->assertSame(2, $repository->countByStatus(CustomerStatus::Active, $office->id));
        $this->assertSame(1, $repository->countByStatus(CustomerStatus::Inactive, $office->id));
    }

    #[Test]
    public function charge_repository_creates_with_payment_method_atomically(): void
    {
        $customer = Customer::factory()->create([
            'monthly_value' => 35000,
            'pix_key' => 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d',
        ]);

        /** @var ChargeRepositoryInterface $repository */
        $repository = $this->app->make(ChargeRepositoryInterface::class);

        $charge = $repository->createWithPaymentMethod(
            [
                'office_id' => $customer->office_id,
                'customer_id' => $customer->id,
                'reference_month' => '2026-07',
                'amount' => 35000,
                'status' => ChargeStatus::Pending->value,
                'due_date' => '2026-07-15',
            ],
            [
                'type' => PaymentMethodType::PixKey->value,
                'amount' => 35000,
                'payload' => ['pix_key' => $customer->pix_key],
            ]
        );

        $this->assertDatabaseHas('charges', ['id' => $charge->id, 'reference_month' => '2026-07']);
        $this->assertDatabaseHas('charge_payment_methods', [
            'charge_id' => $charge->id,
            'type' => 'pix_key',
        ]);
        $this->assertNotNull(
            $repository->findByCustomerAndReference($customer->id, ReferenceMonth::from('2026-07'))
        );
    }

    #[Test]
    public function charge_repository_marks_sent_and_counts_today(): void
    {
        $charge = Charge::factory()->create(['status' => ChargeStatus::Pending]);

        /** @var ChargeRepositoryInterface $repository */
        $repository = $this->app->make(ChargeRepositoryInterface::class);

        $repository->markAsSent($charge->id, 'Olá cliente');

        $this->assertSame(1, $repository->countSentToday($charge->office_id));
        $this->assertSame(ChargeStatus::Sent, $charge->fresh()->status);
    }

    #[Test]
    public function delivery_repository_updates_status_and_filters(): void
    {
        $charge = Charge::factory()->create();

        /** @var ChargeDeliveryRepositoryInterface $repository */
        $repository = $this->app->make(ChargeDeliveryRepositoryInterface::class);

        $delivery = $repository->create([
            'charge_id' => $charge->id,
            'channel' => 'whatsapp',
            'status' => DeliveryStatus::Queued->value,
            'message' => 'Teste',
            'provider' => 'fake',
            'attempt' => 1,
        ]);

        $updated = $repository->updateStatus(
            $delivery->id,
            DeliveryStatus::Sent,
            '{"ok":true}',
            55,
            null,
            'fake_abc'
        );

        $this->assertSame(DeliveryStatus::Sent, $updated->status);
        $this->assertSame('fake_abc', $updated->provider_message_id);
        $this->assertCount(1, $repository->filter(customerId: $charge->customer_id));
    }

    #[Test]
    public function setting_repository_update_or_create_and_cache(): void
    {
        $office = Office::factory()->create();

        /** @var SettingRepositoryInterface $repository */
        $repository = $this->app->make(SettingRepositoryInterface::class);

        $setting = $repository->updateOrCreate($office->id, [
            'company_name' => 'JM Contábil',
            'default_message' => 'Olá {nome}',
            'whatsapp_provider' => 'fake',
            'timezone' => 'America/Sao_Paulo',
        ]);

        $this->assertSame('JM Contábil', $repository->firstOrFail($office->id)->company_name);
        $this->assertSame($setting->id, $repository->first($office->id)?->id);
    }
}
