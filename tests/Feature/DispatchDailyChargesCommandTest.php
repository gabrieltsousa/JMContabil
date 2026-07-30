<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Models\Customer;
use App\Infrastructure\Persistence\Eloquent\Models\Office;
use App\Infrastructure\Persistence\Eloquent\Models\Setting;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class DispatchDailyChargesCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function command_is_registered(): void
    {
        $exitCode = Artisan::call('list', ['--raw' => true]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('charges:dispatch-daily', Artisan::output());
    }

    #[Test]
    public function sync_option_processes_due_customers(): void
    {
        $this->travelTo(now()->setDate(2026, 7, 15)->setTime(0, 5));

        $office = Office::factory()->create();
        Setting::factory()->create(['office_id' => $office->id]);

        Customer::factory()->forOffice($office)->dueOn(15)->create([
            'monthly_value' => 25000,
            'pix_key' => 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d',
            'phone' => '5511987654321',
        ]);

        $this->artisan('charges:dispatch-daily', [
            '--office' => $office->id,
            '--sync' => true,
        ])->assertSuccessful();

        $this->assertDatabaseHas('charges', [
            'office_id' => $office->id,
            'reference_month' => '2026-07',
            'status' => 'sent',
        ]);
    }

    #[Test]
    public function default_option_enqueues_without_error(): void
    {
        $exitCode = Artisan::call('charges:dispatch-daily');

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('enfileirado', Artisan::output());
    }

    #[Test]
    public function schedule_registers_daily_charges_command(): void
    {
        /** @var Schedule $schedule */
        $schedule = $this->app->make(Schedule::class);

        $matched = false;

        foreach ($schedule->events() as $event) {
            $command = (string) ($event->command ?? '');
            $description = (string) ($event->description ?? '');

            if (
                str_contains($command, 'charges:dispatch-daily')
                || $description === 'dispatch-daily-charges'
            ) {
                $matched = true;
                break;
            }
        }

        $this->assertTrue($matched, 'Schedule event charges:dispatch-daily não encontrado.');
    }
}
