<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Actions\Settings\UpdateSettingsAction;
use App\Application\DTOs\Settings\UpdateSettingsData;
use App\Application\Services\SettingsService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateSettingsRequest;
use App\Http\Resources\SettingsResource;
use Illuminate\Http\Request;

final class SettingsController extends Controller
{
    public function __construct(
        private readonly SettingsService $settings,
    ) {
    }

    public function show(Request $request): SettingsResource
    {
        $settings = $this->settings->get($request->user()?->office_id);

        return new SettingsResource($settings->toArray());
    }

    public function update(
        UpdateSettingsRequest $request,
        UpdateSettingsAction $action,
    ): SettingsResource {
        $data = UpdateSettingsData::fromArray($request->validated());
        $settings = $action->execute($request->user()?->office_id, $data);

        return new SettingsResource($settings->toArray());
    }
}
