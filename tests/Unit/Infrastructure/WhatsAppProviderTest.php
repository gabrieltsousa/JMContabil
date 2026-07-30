<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use App\Domain\Settings\Enums\WhatsAppProvider as WhatsAppProviderEnum;
use App\Infrastructure\WhatsApp\Contracts\WhatsAppProviderInterface;
use App\Infrastructure\WhatsApp\DTOs\PixChargeMessage;
use App\Infrastructure\WhatsApp\Providers\EvolutionWhatsAppProvider;
use App\Infrastructure\WhatsApp\Providers\FakeWhatsAppProvider;
use App\Infrastructure\WhatsApp\Support\FakeWhatsAppInbox;
use App\Infrastructure\WhatsApp\WhatsAppProviderResolver;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WhatsAppProviderTest extends TestCase
{
    #[Test]
    public function resolver_returns_fake_by_default(): void
    {
        /** @var WhatsAppProviderInterface $provider */
        $provider = $this->app->make(WhatsAppProviderInterface::class);

        $this->assertSame('fake', $provider->driver());
        $this->assertInstanceOf(FakeWhatsAppProvider::class, $provider);
    }

    #[Test]
    public function fake_provider_sends_pix_charge_and_records_inbox(): void
    {
        $provider = $this->app->make(FakeWhatsAppProvider::class);

        $result = $provider->sendPixCharge(
            '(11) 98888-7777',
            new PixChargeMessage(
                customerName: 'João',
                amountFormatted: 'R$ 350,00',
                dueDate: '15/07/2026',
                pixKey: 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d',
                messageBody: 'Olá João',
            )
        );

        $this->assertTrue($result->success);
        $this->assertNotNull($result->providerMessageId);
        $this->assertStringStartsWith('fake_', (string) $result->providerMessageId);
        $this->assertSame(1, FakeWhatsAppInbox::count());
        $this->assertSame('5511988887777', FakeWhatsAppInbox::last()['phone']);
    }

    #[Test]
    public function fake_provider_can_force_failure(): void
    {
        config(['jmcontabil.whatsapp.fake.should_fail' => true]);

        $provider = $this->app->make(FakeWhatsAppProvider::class);

        $result = $provider->sendText('5511999998888', 'teste');

        $this->assertFalse($result->success);
        $this->assertSame('Fake provider forced failure.', $result->errorMessage);
    }

    #[Test]
    public function resolver_maps_enum_to_stub_providers(): void
    {
        /** @var WhatsAppProviderResolver $resolver */
        $resolver = $this->app->make(WhatsAppProviderResolver::class);

        $evolution = $resolver->resolveFromEnum(WhatsAppProviderEnum::Evolution);

        $this->assertInstanceOf(EvolutionWhatsAppProvider::class, $evolution);
        $this->assertSame('evolution', $evolution->driver());
    }

    #[Test]
    public function evolution_provider_fails_when_not_configured(): void
    {
        config([
            'jmcontabil.whatsapp.evolution.base_url' => '',
            'jmcontabil.whatsapp.evolution.api_key' => '',
            'jmcontabil.whatsapp.evolution.instance' => '',
        ]);

        $result = $this->app->make(EvolutionWhatsAppProvider::class)
            ->sendText('5511999998888', 'hello');

        $this->assertFalse($result->success);
        $this->assertStringContainsString('Evolution API não configurada', (string) $result->errorMessage);
    }
}
