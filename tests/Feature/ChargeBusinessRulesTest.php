<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Application\DTOs\Charge\CreateChargeData;
use App\Application\Services\ChargeNotificationService;
use App\Application\Services\ChargeService;
use App\Domain\Charge\Enums\ChargeStatus;
use App\Domain\Shared\Exceptions\BusinessRuleException;
use App\Infrastructure\Persistence\Eloquent\Models\Charge;
use App\Infrastructure\Persistence\Eloquent\Models\Customer;
use App\Infrastructure\Persistence\Eloquent\Models\Office;
use App\Infrastructure\Persistence\Eloquent\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ChargeBusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function charge_service_rejects_duplicate_competence(): void
    {
        $customer = Customer::factory()->create();

        Charge::factory()->create([
            'customer_id' => $customer->id,
            'office_id' => $customer->office_id,
            'reference_month' => '2026-07',
        ]);

        /** @var ChargeService $service */
        $service = $this->app->make(ChargeService::class);

        $this->expectException(BusinessRuleException::class);

        $service->create(CreateChargeData::fromCustomerSnapshot(
            customerId: $customer->id,
            officeId: $customer->office_id,
            amountCents: 10000,
            pixKey: $customer->pix_key,
            dueDay: 15,
            now: new \DateTimeImmutable('2026-07-15'),
        ));
    }

    #[Test]
    public function notification_service_blocks_inactive_customer(): void
    {
        $office = Office::factory()->create();
        Setting::factory()->create(['office_id' => $office->id]);

        $customer = Customer::factory()->forOffice($office)->inactive()->create();
        $charge = Charge::factory()->create([
            'office_id' => $office->id,
            'customer_id' => $customer->id,
            'status' => ChargeStatus::Pending,
        ]);

        /** @var ChargeNotificationService $service */
        $service = $this->app->make(ChargeNotificationService::class);

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('Cliente inativo');

        $service->send($charge->id);
    }
}
