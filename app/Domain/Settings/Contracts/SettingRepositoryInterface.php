<?php

declare(strict_types=1);

namespace App\Domain\Settings\Contracts;

interface SettingRepositoryInterface
{
    public function first(?int $officeId = null): ?object;

    public function firstOrFail(?int $officeId = null): object;

    public function updateOrCreate(?int $officeId, array $attributes): object;
}
