<?php

declare(strict_types=1);

namespace App\Domain\Charge\Enums;

enum DeliveryChannel: string
{
    case WhatsApp = 'whatsapp';
    case Email = 'email';
    case Sms = 'sms';

    public function label(): string
    {
        return match ($this) {
            self::WhatsApp => 'WhatsApp',
            self::Email => 'E-mail',
            self::Sms => 'SMS',
        };
    }
}
