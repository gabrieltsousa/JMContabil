<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use App\Infrastructure\WhatsApp\DTOs\PixChargeMessage;
use App\Infrastructure\WhatsApp\Providers\EvolutionWhatsAppProvider;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EvolutionWhatsAppProviderTest extends TestCase
{
    #[Test]
    public function it_sends_text_via_evolution_api(): void
    {
        config([
            'jmcontabil.whatsapp.evolution.base_url' => 'http://evolution.test',
            'jmcontabil.whatsapp.evolution.api_key' => 'test-key',
            'jmcontabil.whatsapp.evolution.instance' => 'jmcontabil',
        ]);

        Http::fake([
            'evolution.test/message/sendText/jmcontabil' => Http::response([
                'key' => ['id' => 'MSG123'],
                'status' => 'PENDING',
            ], 201),
        ]);

        $provider = new EvolutionWhatsAppProvider;
        $result = $provider->sendPixCharge(
            '11977291983',
            new PixChargeMessage(
                customerName: 'Gabriel',
                amountFormatted: 'R$ 100,00',
                dueDate: '30/07/2026',
                pixKey: '41340336839',
                messageBody: 'Olá Gabriel, teste.',
            )
        );

        $this->assertTrue($result->success);
        $this->assertSame('MSG123', $result->providerMessageId);

        Http::assertSent(function ($request): bool {
            return $request->url() === 'http://evolution.test/message/sendText/jmcontabil'
                && $request->hasHeader('apikey', 'test-key')
                && $request['number'] === '5511977291983'
                && $request['text'] === 'Olá Gabriel, teste.';
        });
    }
}
