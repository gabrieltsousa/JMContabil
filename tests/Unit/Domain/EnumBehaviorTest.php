<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Charge\Enums\ChargeStatus;
use App\Domain\Charge\Enums\PaymentMethodType;
use App\Domain\Customer\Enums\CustomerStatus;
use App\Domain\Settings\Enums\WhatsAppProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EnumBehaviorTest extends TestCase
{
    #[Test]
    public function charge_status_can_be_sent_rules(): void
    {
        $this->assertTrue(ChargeStatus::Pending->canBeSent());
        $this->assertTrue(ChargeStatus::Failed->canBeSent());
        $this->assertFalse(ChargeStatus::Sent->canBeSent());
        $this->assertFalse(ChargeStatus::Paid->canBeSent());
        $this->assertTrue(ChargeStatus::Paid->isFinal());
    }

    #[Test]
    public function payment_method_identifies_pix(): void
    {
        $this->assertTrue(PaymentMethodType::PixKey->isPix());
        $this->assertTrue(PaymentMethodType::QrCode->isPix());
        $this->assertFalse(PaymentMethodType::Boleto->isPix());
    }

    #[Test]
    public function customer_and_provider_labels_exist(): void
    {
        $this->assertSame('Ativo', CustomerStatus::Active->label());
        $this->assertSame('Fake (Desenvolvimento)', WhatsAppProvider::Fake->label());
        $this->assertFalse(WhatsAppProvider::Fake->isProductionReady());
        $this->assertTrue(WhatsAppProvider::Evolution->isProductionReady());
    }
}
