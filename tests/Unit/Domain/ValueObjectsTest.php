<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Charge\ValueObjects\ReferenceMonth;
use App\Domain\Customer\ValueObjects\DueDay;
use App\Domain\Customer\ValueObjects\PixKey;
use App\Domain\Shared\Exceptions\InvalidValueObjectException;
use App\Domain\Shared\ValueObjects\Money;
use App\Domain\Shared\ValueObjects\PhoneNumber;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ValueObjectsTest extends TestCase
{
    #[Test]
    public function it_normalizes_brazilian_phone_numbers(): void
    {
        $phone = PhoneNumber::from('(11) 98888-7777');

        $this->assertSame('5511988887777', $phone->whatsapp());
    }

    #[Test]
    public function it_rejects_invalid_phone_numbers(): void
    {
        $this->expectException(InvalidValueObjectException::class);

        PhoneNumber::from('123');
    }

    #[Test]
    public function it_formats_money_in_brl(): void
    {
        $money = Money::fromDecimal(350.00);

        $this->assertSame(35000, $money->amountInCents());
        $this->assertSame('R$ 350,00', $money->formatBrl());
    }

    #[Test]
    public function it_accepts_due_day_between_1_and_28(): void
    {
        $dueDay = DueDay::from(15);

        $this->assertTrue($dueDay->matches(15));
    }

    #[Test]
    public function it_rejects_due_day_above_28(): void
    {
        $this->expectException(InvalidValueObjectException::class);

        DueDay::from(31);
    }

    #[Test]
    public function it_accepts_uuid_pix_key(): void
    {
        $pix = PixKey::from('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d');

        $this->assertSame('a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d', $pix->value());
    }

    #[Test]
    public function it_parses_reference_month(): void
    {
        $reference = ReferenceMonth::from('2026-07');

        $this->assertSame(2026, $reference->year());
        $this->assertSame(7, $reference->month());
    }
}
