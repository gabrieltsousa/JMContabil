<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Actions\Charge\SendChargeNotificationAction;
use App\Application\DTOs\Charge\SendChargeNotificationData;
use App\Application\Jobs\SendChargeWhatsAppJob;
use App\Application\Services\ChargeService;
use App\Domain\Charge\Enums\DeliveryStatus;
use App\Domain\Shared\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Charge\SendNotificationRequest;
use App\Http\Resources\ChargeDeliveryResource;
use App\Http\Resources\ChargeResource;
use App\Infrastructure\Persistence\Eloquent\Models\Charge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class NotificationController extends Controller
{
    public function __construct(
        private readonly ChargeService $charges,
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Charge::class);

        $status = $request->query('status');

        $items = $this->charges->listDeliveries(
            customerId: $request->integer('customer_id') ?: null,
            status: is_string($status) && $status !== ''
                ? DeliveryStatus::from($status)
                : null,
            dateFrom: $request->query('date_from') ? (string) $request->query('date_from') : null,
            dateTo: $request->query('date_to') ? (string) $request->query('date_to') : null,
            officeId: $request->user()?->office_id,
        );

        return ChargeDeliveryResource::collection(
            collect($items)->map(static fn ($dto) => $dto->toArray())
        );
    }

    public function send(
        SendNotificationRequest $request,
        SendChargeNotificationAction $action,
    ): JsonResponse {
        $this->authorize('send', Charge::class);

        $data = SendChargeNotificationData::fromArray([
            ...$request->validated(),
            'office_id' => $request->user()?->office_id,
        ]);

        if ($data->chargeId === null && $data->customerId === null) {
            throw BusinessRuleException::withMessage(
                'Informe charge_id ou customer_id para envio.'
            );
        }

        $chargeId = $data->chargeId ?? $this->charges->findLatestIdForCustomer(
            (int) $data->customerId,
            $data->officeId,
        );

        /** @var Charge $charge */
        $charge = Charge::query()->findOrFail($chargeId);
        $this->authorize('sendOne', $charge);

        if ($request->boolean('async', true)) {
            SendChargeWhatsAppJob::dispatch($charge->id);

            return response()->json([
                'message' => 'Envio enfileirado com sucesso.',
                'charge_id' => $charge->id,
            ], 202);
        }

        $result = $action->execute($charge->id);

        return response()->json([
            'message' => 'Cobrança enviada com sucesso.',
            'data' => (new ChargeResource($result->toArray()))->resolve(),
        ]);
    }
}
