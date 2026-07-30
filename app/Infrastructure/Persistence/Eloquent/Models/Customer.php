<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use App\Domain\Customer\Enums\CustomerStatus;
use App\Domain\Customer\ValueObjects\DueDay;
use App\Domain\Customer\ValueObjects\PixKey;
use App\Domain\Shared\ValueObjects\Money;
use App\Domain\Shared\ValueObjects\PhoneNumber;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Cliente do escritório.
 *
 * @property int $id
 * @property int|null $office_id
 * @property string $name
 * @property string $phone
 * @property string|null $email
 * @property string $pix_key
 * @property int $monthly_value Centavos
 * @property int $due_day
 * @property CustomerStatus $status
 */
class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory;

    protected $fillable = [
        'office_id',
        'name',
        'phone',
        'email',
        'pix_key',
        'monthly_value',
        'due_day',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'monthly_value' => 'integer',
            'due_day' => 'integer',
            'status' => CustomerStatus::class,
        ];
    }

    protected static function newFactory(): \Database\Factories\CustomerFactory
    {
        return \Database\Factories\CustomerFactory::new();
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function charges(): HasMany
    {
        return $this->hasMany(Charge::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', CustomerStatus::Active);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', CustomerStatus::Inactive);
    }

    public function scopeDueOn(Builder $query, int $day): Builder
    {
        return $query->where('due_day', $day);
    }

    public function scopeForOffice(Builder $query, ?int $officeId): Builder
    {
        if ($officeId === null) {
            return $query;
        }

        return $query->where('office_id', $officeId);
    }

    public function money(): Money
    {
        return Money::fromCents($this->monthly_value);
    }

    public function phoneNumber(): PhoneNumber
    {
        return PhoneNumber::from($this->phone);
    }

    public function pixKey(): PixKey
    {
        return PixKey::from($this->pix_key);
    }

    public function dueDay(): DueDay
    {
        return DueDay::from($this->due_day);
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }
}
