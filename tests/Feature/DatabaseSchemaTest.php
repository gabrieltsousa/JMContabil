<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_core_business_tables(): void
    {
        foreach ([
            'offices',
            'customers',
            'charges',
            'charge_payment_methods',
            'charge_deliveries',
            'settings',
            'users',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table: {$table}");
        }
    }

    #[Test]
    public function customers_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('customers', [
            'office_id',
            'name',
            'phone',
            'email',
            'pix_key',
            'monthly_value',
            'due_day',
            'status',
        ]));
    }

    #[Test]
    public function charges_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('charges', [
            'office_id',
            'customer_id',
            'reference_month',
            'amount',
            'status',
            'due_date',
            'sent_at',
            'paid_at',
            'message_sent',
            'failure_reason',
        ]));
    }

    #[Test]
    public function users_table_has_office_id(): void
    {
        $this->assertTrue(Schema::hasColumn('users', 'office_id'));
    }
}
