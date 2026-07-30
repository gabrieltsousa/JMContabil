<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Application\Jobs\ProcessDailyChargesJob;
use App\Application\Jobs\SendChargeWhatsAppJob;
use App\Domain\Charge\Enums\ChargeStatus;
use App\Infrastructure\Persistence\Eloquent\Models\Charge;
use App\Infrastructure\Persistence\Eloquent\Models\Customer;
use App\Infrastructure\Persistence\Eloquent\Models\Office;
use App\Infrastructure\Persistence\Eloquent\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ChargeJobsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function dispatch_daily_action_pushes_process_job(): void
    {
        Queue::fake();

        $this->artisan('charges:dispatch-daily')->assertSuccessful();

        Queue::assertPushed(ProcessDailyChargesJob::class);
    }

    #[Test]
    public function process_daily_job_queues_whatsapp_jobs_per_customer(): void
    {
        Queue::fake([SendChargeWhatsAppJob::class]);

        $this->travelTo(now()->setDate(2026, 7, 15)->setTime(0, 5));

        $office = Office::factory()->create();
        Setting::factory()->create(['office_id' => $office->id]);
        Customer::factory()->forOffice($office)->dueOn(15)->count(2)->create([
            'pix_key' => 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d',
        ]);

        (new ProcessDailyChargesJob($office->id))->handle(
            $this->app->make(\App\Application\Services\DailyChargeService::class)
        );

        Queue::assertPushed(SendChargeWhatsAppJob::class, 2);
        $this->assertSame(2, Charge::query()->count());
        $this->assertSame(2, Charge::query()->where('status', ChargeStatus::Pending)->count());
    }

    #[Test]
    public function send_whatsapp_job_marks_charge_as_sent(): void
    {
        $office = Office::factory()->create();
        Setting::factory()->create([
            'office_id' => $office->id,
            'default_message' => 'Olá {nome} — {valor}',
        ]);

        $customer = Customer::factory()->forOffice($office)->create([
            'name' => 'Ana',
            'pix_key' => 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d',
        ]);

        $charge = Charge::factory()->create([
            'office_id' => $office->id,
            'customer_id' => $customer->id,
            'status' => ChargeStatus::Pending,
            'amount' => 10000,
        ]);

        $charge->paymentMethods()->create([
            'type' => 'pix_key',
            'amount' => 10000,
            'payload' => ['pix_key' => $customer->pix_key],
        ]);

        (new SendChargeWhatsAppJob($charge->id))->handle(
            $this->app->make(\App\Application\Services\ChargeNotificationService::class)
        );

        $this->assertSame(ChargeStatus::Sent, $charge->fresh()->status);
        $this->assertDatabaseHas('charge_deliveries', [
            'charge_id' => $charge->id,
            'status' => 'sent',
        ]);
    }

    #[Test]
    public function send_whatsapp_job_failed_marks_charge_failed(): void
    {
        $charge = Charge::factory()->create(['status' => ChargeStatus::Pending]);

        (new SendChargeWhatsAppJob($charge->id))->failed(
            new \RuntimeException('Provider offline')
        );

        $this->assertSame(ChargeStatus::Failed, $charge->fresh()->status);
        $this->assertSame('Provider offline', $charge->fresh()->failure_reason);
    }
}
