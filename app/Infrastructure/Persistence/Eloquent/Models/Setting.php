<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use App\Domain\Settings\Enums\WhatsAppProvider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Configurações do escritório.
 *
 * @property int $id
 * @property int|null $office_id
 * @property string $company_name
 * @property string $default_message
 * @property WhatsAppProvider $whatsapp_provider
 * @property string $timezone
 */
class Setting extends Model
{
    /** @use HasFactory<\Database\Factories\SettingFactory> */
    use HasFactory;

    protected $fillable = [
        'office_id',
        'company_name',
        'default_message',
        'whatsapp_provider',
        'timezone',
    ];

    protected function casts(): array
    {
        return [
            'whatsapp_provider' => WhatsAppProvider::class,
        ];
    }

    protected static function newFactory(): \Database\Factories\SettingFactory
    {
        return \Database\Factories\SettingFactory::new();
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }
}
