<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use App\Application\Services\MessageTemplateService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class MessageTemplateServiceTest extends TestCase
{
    #[Test]
    public function it_replaces_placeholders(): void
    {
        $service = new MessageTemplateService;

        $result = $service->render(
            'Olá {nome}. Valor: {valor}. PIX: {pix}. Vencimento: {data}.',
            [
                'nome' => 'João',
                'valor' => 'R$ 350,00',
                'pix' => 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d',
                'data' => '30/07/2026',
            ]
        );

        $this->assertSame(
            'Olá João. Valor: R$ 350,00. PIX: a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d. Vencimento: 30/07/2026.',
            $result
        );
    }
}
