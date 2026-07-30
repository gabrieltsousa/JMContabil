<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Actions\Customer\CreateCustomerAction;
use App\Application\Actions\Customer\DeleteCustomerAction;
use App\Application\Actions\Customer\ListCustomersAction;
use App\Application\Actions\Customer\UpdateCustomerAction;
use App\Application\DTOs\Customer\CreateCustomerData;
use App\Application\DTOs\Customer\CustomerFilterData;
use App\Application\DTOs\Customer\UpdateCustomerData;
use App\Application\Services\CustomerService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Infrastructure\Persistence\Eloquent\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerService $customers,
    ) {
    }

    public function index(Request $request, ListCustomersAction $action): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Customer::class);

        $filter = CustomerFilterData::fromArray([
            ...$request->query(),
            'office_id' => $request->user()?->office_id,
        ]);

        $items = $action->execute($filter);

        return CustomerResource::collection(
            collect($items)->map(fn ($dto) => $dto->toArray())
        );
    }

    public function store(StoreCustomerRequest $request, CreateCustomerAction $action): JsonResponse
    {
        $this->authorize('create', Customer::class);

        $data = CreateCustomerData::fromArray([
            ...$request->validated(),
            'office_id' => $request->user()?->office_id,
        ]);

        $customer = $action->execute($data);

        return (new CustomerResource($customer->toArray()))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Customer $customer): CustomerResource
    {
        $this->authorize('view', $customer);

        return new CustomerResource($this->customers->find($customer->id)->toArray());
    }

    public function update(
        UpdateCustomerRequest $request,
        Customer $customer,
        UpdateCustomerAction $action,
    ): CustomerResource {
        $this->authorize('update', $customer);

        $data = UpdateCustomerData::fromArray($request->validated());
        $updated = $action->execute($customer->id, $data);

        return new CustomerResource($updated->toArray());
    }

    public function destroy(Customer $customer, DeleteCustomerAction $action): JsonResponse
    {
        $this->authorize('delete', $customer);

        $action->execute($customer->id);

        return response()->json(null, 204);
    }
}
