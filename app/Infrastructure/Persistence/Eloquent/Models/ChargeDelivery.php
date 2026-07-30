<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use App\Domain\Charge\Enums\DeliveryChannel;
use App\Domain\Charge\Enums\DeliveryStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Histórico de entrega de uma cobrança por canal.
 *
 * @property int $id
 * @property int $charge_id
 * @property DeliveryChannel $channel
 * @property DeliveryStatus $status
 * @property string $message
 * @property string $provider
 * @property string|null $provider_message_id
 * @property string|null $provider_response
 * @property string|null $error_message
 * @property int $duration_ms
 * @property int $attempt
 * @property \Illuminate\Support\Carbon|null $sent_at
 * @property string|null $whatsapp_response
 */
class ChargeDelivery extends Model
{
    /** @use HasFactory<\Database\Factories\ChargeDeliveryFactory> */
    use HasFactory;

    protected $fillable = [
        'charge_id',
        'channel',
        'status',
        'message',
        'provider',
        'provider_message_id',
        'provider_response',
        'error_message',
        'duration_ms',
        'attempt',
        'sent_at',
        'whatsapp_response',
    ];

    protected function casts(): array
    {
        return [
            'channel' => DeliveryChannel::class,
            'status' => DeliveryStatus::class,
            'duration_ms' => 'integer',
            'attempt' => 'integer',
            'sent_at' => 'datetime',
        ];
    }

    protected static function newFactory(): \Database\Factories\ChargeDeliveryFactory
    {
        return \Database\Factories\ChargeDeliveryFactory::new();
    }

    public function charge(): BelongsTo
    {
        return $this->belongsTo(Charge::class);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', DeliveryStatus::Failed);
    }

    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->whereIn('status', [
            DeliveryStatus::Sent,
            DeliveryStatus::Delivered,
            DeliveryStatus::Read,
        ]);
    }

    public function markSent(
        ?string $providerMessageId,
        ?string $providerResponse,
        int $durationMs,
    ): void {
        $this->forceFill([
            'status' => DeliveryStatus::Sent,
            'provider_message_id' => $providerMessageId,
            'provider_response' => $providerResponse,
            'duration_ms' => $durationMs,
            'error_message' => null,
            'sent_at' => now(),
        ])->save();
    }

    public function markFailed(
        string $errorMessage,
        ?string $providerResponse,
        int $durationMs,
    ): void {
        $this->forceFill([
            'status' => DeliveryStatus::Failed,
            'error_message' => $errorMessage,
            'provider_response' => $providerResponse,
            'duration_ms' => $durationMs,
        ])->save();
    }
}
