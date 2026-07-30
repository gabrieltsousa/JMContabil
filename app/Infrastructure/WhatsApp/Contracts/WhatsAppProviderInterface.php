<?php

declare(strict_types=1);

namespace App\Infrastructure\WhatsApp\Contracts;

use App\Infrastructure\WhatsApp\DTOs\PixChargeMessage;
use App\Infrastructure\WhatsApp\DTOs\WhatsAppSendResult;

/**
 * Contrato pluggable para provedores WhatsApp.
 *
 * MVP: FakeWhatsAppProvider
 * Futuro: Evolution, Z-API, Meta Cloud, UltraMSG, 360Dialog
 *
 * Trocar provider = alterar config/settings + binding no Resolver.
 * Domain e Application NÃO conhecem a implementação concreta.
 */
interface WhatsAppProviderInterface
{
    /**
     * Identificador do driver (ex.: fake, evolution).
     */
    public function driver(): string;

    public function sendText(string $phone, string $message): WhatsAppSendResult;

    public function sendPixCharge(string $phone, PixChargeMessage $payload): WhatsAppSendResult;
}
