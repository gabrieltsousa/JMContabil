<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use App\Domain\Charge\Enums\ChargeStatus;
use App\Domain\Charge\ValueObjects\ReferenceMonth;
use App\Domain\Shared\ValueObjects\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Cobrança mensal por competência.
 *
 * @property int $id
 * @property int|null $office_id
 * @property int $customer_id
 * @property string $reference_month
 * @property int $amount Centavos
 * @property ChargeStatus $status
 * @property \Illuminate\Support\Carbon $due_date
 * @property \Illuminate\Support\Carbon|null $sent_at
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property string|null $message_sent
 * @property string|null $failure_reason
 */
class Charge extends Model
{
    /** @use HasFactory<\Database\Factories\ChargeFactory> */
    use HasFactory;

    protected $fillable = [
        'office_id',
        'customer_id',
        'reference_month',
        'amount',
        'status',
        'due_date',
        'sent_at',
        'paid_at',
        'message_sent',
        'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'status' => ChargeStatus::class,
            'due_date' => 'date',
            'sent_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    protected static function newFactory(): \Database\Factories\ChargeFactory
    {
        return \Database\Factories\ChargeFactory::new();
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(ChargePaymentMethod::class);
    }

    public function primaryPaymentMethod(): HasOne
    {
        return $this->hasOne(ChargePaymentMethod::class)->oldestOfMany();
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(ChargeDelivery::class);
    }

    public function scopeStatus(Builder $query, ChargeStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', ChargeStatus::Pending);
    }

    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', ChargeStatus::Sent);
    }

    public function scopeForOffice(Builder $query, ?int $officeId): Builder
    {
        if ($officeId === null) {
            return $query;
        }

        return $query->where('office_id', $officeId);
    }

    public function scopeSentToday(Builder $query): Builder
    {
        return $query
            ->where('status', ChargeStatus::Sent)
            ->whereDate('sent_at', now()->toDateString());
    }

    public function scopeForReferenceMonth(Builder $query, string $referenceMonth): Builder
    {
        return $query->where('reference_month', $referenceMonth);
    }

    public function money(): Money
    {
        return Money::fromCents($this->amount);
    }

    public function referenceMonth(): ReferenceMonth
    {
        return ReferenceMonth::from($this->reference_month);
    }

    public function markSent(string $message): void
    {
        $this->forceFill([
            'status' => ChargeStatus::Sent,
            'message_sent' => $message,
            'sent_at' => now(),
            'failure_reason' => null,
        ])->save();
    }

    public function markFailed(string $reason): void
    {
        $this->forceFill([
            'status' => ChargeStatus::Failed,
            'failure_reason' => $reason,
        ])->save();
    }

    public function markPaid(?\DateTimeInterface $paidAt = null): void
    {
        $this->forceFill([
            'status' => ChargeStatus::Paid,
            'paid_at' => $paidAt ?? now(),
            'failure_reason' => null,
        ])->save();
    }
}
