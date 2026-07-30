<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use App\Domain\Charge\Enums\PaymentMethodType;
use App\Domain\Shared\ValueObjects\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Forma de pagamento da cobrança.
 *
 * @property int $id
 * @property int $charge_id
 * @property PaymentMethodType $type
 * @property int $amount Centavos
 * @property array<string, mixed> $payload
 */
class ChargePaymentMethod extends Model
{
    /** @use HasFactory<\Database\Factories\ChargePaymentMethodFactory> */
    use HasFactory;

    protected $fillable = [
        'charge_id',
        'type',
        'amount',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'type' => PaymentMethodType::class,
            'amount' => 'integer',
            'payload' => 'array',
        ];
    }

    protected static function newFactory(): \Database\Factories\ChargePaymentMethodFactory
    {
        return \Database\Factories\ChargePaymentMethodFactory::new();
    }

    public function charge(): BelongsTo
    {
        return $this->belongsTo(Charge::class);
    }

    public function money(): Money
    {
        return Money::fromCents($this->amount);
    }

    public function pixKey(): ?string
    {
        $key = $this->payload['pix_key'] ?? null;

        return is_string($key) ? $key : null;
    }
}
