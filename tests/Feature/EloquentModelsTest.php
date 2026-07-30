<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Charge\Enums\ChargeStatus;
use App\Domain\Charge\Enums\DeliveryStatus;
use App\Domain\Charge\Enums\PaymentMethodType;
use App\Domain\Customer\Enums\CustomerStatus;
use App\Domain\Settings\Enums\WhatsAppProvider;
use App\Infrastructure\Persistence\Eloquent\Models\Charge;
use App\Infrastructure\Persistence\Eloquent\Models\ChargeDelivery;
use App\Infrastructure\Persistence\Eloquent\Models\ChargePaymentMethod;
use App\Infrastructure\Persistence\Eloquent\Models\Customer;
use App\Infrastructure\Persistence\Eloquent\Models\Office;
use App\Infrastructure\Persistence\Eloquent\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EloquentModelsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function customer_casts_status_and_exposes_value_objects(): void
    {
        $customer = Customer::factory()->create([
            'monthly_value' => 35000,
            'due_day' => 15,
            'pix_key' => 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d',
            'phone' => '5511988887777',
        ]);

        $this->assertInstanceOf(CustomerStatus::class, $customer->status);
        $this->assertTrue($customer->isActive());
        $this->assertSame('R$ 350,00', $customer->money()->formatBrl());
        $this->assertSame(15, $customer->dueDay()->value());
        $this->assertSame('5511988887777', $customer->phoneNumber()->whatsapp());
    }

    #[Test]
    public function charge_relationships_and_status_transitions_work(): void
    {
        $office = Office::factory()->create();
        $customer = Customer::factory()->forOffice($office)->create();

        $charge = Charge::factory()->create([
            'office_id' => $office->id,
            'customer_id' => $customer->id,
            'amount' => 35000,
        ]);

        ChargePaymentMethod::factory()->create([
            'charge_id' => $charge->id,
            'type' => PaymentMethodType::PixKey,
            'amount' => 35000,
            'payload' => ['pix_key' => $customer->pix_key],
        ]);

        $delivery = ChargeDelivery::factory()->create([
            'charge_id' => $charge->id,
        ]);

        $charge->markSent('Mensagem enviada');
        $delivery->markSent('fake_123', '{"ok":true}', 42);

        $charge->refresh();
        $delivery->refresh();

        $this->assertSame(ChargeStatus::Sent, $charge->status);
        $this->assertNotNull($charge->sent_at);
        $this->assertSame(DeliveryStatus::Sent, $delivery->status);
        $this->assertTrue($charge->paymentMethods->first()->type->isPix());
        $this->assertTrue($charge->customer->is($customer));
        $this->assertTrue($charge->office->is($office));
    }

    #[Test]
    public function setting_casts_whatsapp_provider_enum(): void
    {
        $setting = Setting::factory()->create([
            'whatsapp_provider' => WhatsAppProvider::Fake,
        ]);

        $this->assertSame(WhatsAppProvider::Fake, $setting->whatsapp_provider);
        $this->assertNotNull($setting->office);
    }

    #[Test]
    public function active_customers_due_on_scope_filters_correctly(): void
    {
        $office = Office::factory()->create();

        Customer::factory()->forOffice($office)->dueOn(10)->create();
        Customer::factory()->forOffice($office)->dueOn(10)->inactive()->create();
        Customer::factory()->forOffice($office)->dueOn(20)->create();

        $result = Customer::query()
            ->forOffice($office->id)
            ->active()
            ->dueOn(10)
            ->get();

        $this->assertCount(1, $result);
    }
}
