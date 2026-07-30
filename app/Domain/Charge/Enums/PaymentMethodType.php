<?php

declare(strict_types=1);

namespace App\Domain\Charge\Enums;

enum PaymentMethodType: string
{
    case PixKey = 'pix_key';
    case PixCopiaCola = 'pix_copia_cola';
    case QrCode = 'qr_code';
    case Boleto = 'boleto';

    public function label(): string
    {
        return match ($this) {
            self::PixKey => 'PIX (Chave)',
            self::PixCopiaCola => 'PIX Copia e Cola',
            self::QrCode => 'QR Code PIX',
            self::Boleto => 'Boleto',
        };
    }

    public function isPix(): bool
    {
        return match ($this) {
            self::PixKey, self::PixCopiaCola, self::QrCode => true,
            self::Boleto => false,
        };
    }
}
