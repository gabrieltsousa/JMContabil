<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use App\Application\Services\MessageTemplateService;
use App\Shared\Support\MessagePlaceholders;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MessagePlaceholdersTest extends TestCase
{
    #[Test]
    public function placeholders_constant_list_is_complete(): void
    {
        $this->assertSame(
            ['nome', 'valor', 'pix', 'data', 'empresa', 'competencia'],
            MessagePlaceholders::all()
        );
    }

    #[Test]
    public function template_service_matches_placeholder_constants(): void
    {
        $service = new MessageTemplateService;

        $this->assertSame(MessagePlaceholders::all(), $service->availablePlaceholders());

        $rendered = $service->render(
            '{empresa} cobrança de {competencia}',
            [
                MessagePlaceholders::COMPANY => 'JM Contábil',
                MessagePlaceholders::COMPETENCE => '2026-07',
            ]
        );

        $this->assertSame('JM Contábil cobrança de 2026-07', $rendered);
    }
}
