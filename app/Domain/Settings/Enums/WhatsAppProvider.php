<?php

declare(strict_types=1);

namespace App\Domain\Settings\Enums;

enum WhatsAppProvider: string
{
    case Fake = 'fake';
    case Evolution = 'evolution';
    case ZApi = 'zapi';
    case MetaCloud = 'meta_cloud';
    case UltraMsg = 'ultramsg';
    case Dialog360 = '360dialog';

    public function label(): string
    {
        return match ($this) {
            self::Fake => 'Fake (Desenvolvimento)',
            self::Evolution => 'Evolution API',
            self::ZApi => 'Z-API',
            self::MetaCloud => 'Meta Cloud API',
            self::UltraMsg => 'UltraMSG',
            self::Dialog360 => '360Dialog',
        };
    }

    public function isProductionReady(): bool
    {
        return $this !== self::Fake;
    }
}
