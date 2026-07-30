<?php

declare(strict_types=1);

namespace Tests;

use App\Infrastructure\WhatsApp\Support\FakeWhatsAppInbox;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        FakeWhatsAppInbox::clear();
    }
}
