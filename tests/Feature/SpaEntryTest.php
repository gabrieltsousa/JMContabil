<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class SpaEntryTest extends TestCase
{
    public function test_spa_entry_returns_successful_response(): void
    {
        $this->get('/')->assertOk();
        $this->get('/login')->assertOk();
        $this->get('/customers')->assertOk();
    }
}
