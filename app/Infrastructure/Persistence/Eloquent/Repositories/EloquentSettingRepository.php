<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Settings\Contracts\SettingRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Setting;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;

final class EloquentSettingRepository implements SettingRepositoryInterface
{
    private const string CACHE_KEY_PREFIX = 'settings:office:';

    public function first(?int $officeId = null): ?Setting
    {
        return Cache::remember(
            $this->cacheKey($officeId),
            now()->addMinutes(10),
            function () use ($officeId): ?Setting {
                $query = Setting::query();

                if ($officeId !== null) {
                    return $query->where('office_id', $officeId)->first();
                }

                return $query->orderBy('id')->first();
            }
        );
    }

    public function firstOrFail(?int $officeId = null): Setting
    {
        $setting = $this->first($officeId);

        if ($setting === null) {
            throw (new ModelNotFoundException)->setModel(Setting::class);
        }

        return $setting;
    }

    public function updateOrCreate(?int $officeId, array $attributes): Setting
    {
        $setting = Setting::query()->updateOrCreate(
            ['office_id' => $officeId],
            $attributes
        );

        Cache::forget($this->cacheKey($officeId));

        return $setting->refresh();
    }

    private function cacheKey(?int $officeId): string
    {
        return self::CACHE_KEY_PREFIX.($officeId ?? 'default');
    }
}
