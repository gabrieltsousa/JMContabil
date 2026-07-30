<?php

declare(strict_types=1);

namespace App\Domain\Charge\Enums;

enum DeliveryStatus: string
{
    case Queued = 'queued';
    case Sending = 'sending';
    case Sent = 'sent';
    case Failed = 'failed';
    case Delivered = 'delivered';
    case Read = 'read';

    public function label(): string
    {
        return match ($this) {
            self::Queued => 'Na fila',
            self::Sending => 'Enviando',
            self::Sent => 'Enviado',
            self::Failed => 'Falhou',
            self::Delivered => 'Entregue',
            self::Read => 'Lido',
        };
    }

    public function isSuccessful(): bool
    {
        return match ($this) {
            self::Sent, self::Delivered, self::Read => true,
            default => false,
        };
    }
}
