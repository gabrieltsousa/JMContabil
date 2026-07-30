<?php

declare(strict_types=1);

namespace App\Domain\Charge\Enums;

enum ChargeStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::Sent => 'Enviada',
            self::Paid => 'Paga',
            self::Overdue => 'Vencida',
            self::Failed => 'Falhou',
        };
    }

    public function isFinal(): bool
    {
        return match ($this) {
            self::Paid, self::Failed => true,
            default => false,
        };
    }

    public function canBeSent(): bool
    {
        return match ($this) {
            self::Pending, self::Failed => true,
            default => false,
        };
    }
}
