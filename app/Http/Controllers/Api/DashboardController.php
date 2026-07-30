<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Actions\Dashboard\GetDashboardStatsAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardStatsResource;
use Illuminate\Http\Request;

final class DashboardController extends Controller
{
    public function show(Request $request, GetDashboardStatsAction $action): DashboardStatsResource
    {
        $stats = $action->execute($request->user()?->office_id);

        return new DashboardStatsResource($stats->toArray());
    }
}
