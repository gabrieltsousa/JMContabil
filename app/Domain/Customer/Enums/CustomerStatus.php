<?php

declare(strict_types=1);

namespace App\Domain\Customer\Enums;

enum CustomerStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Ativo',
            self::Inactive => 'Inativo',
        };
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }
}
