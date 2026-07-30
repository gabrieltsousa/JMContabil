<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\Customer\CreateCustomerData;
use App\Application\DTOs\Customer\CustomerData;
use App\Application\DTOs\Customer\CustomerFilterData;
use App\Application\DTOs\Customer\UpdateCustomerData;
use App\Domain\Customer\Contracts\CustomerRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Customer;

final class CustomerService
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customers,
    ) {
    }

    /**
     * @return list<CustomerData>
     */
    public function list(CustomerFilterData $filter): array
    {
        /** @var list<Customer> $items */
        $items = $this->customers->filter(
            status: $filter->status,
            dueDay: $filter->dueDay,
            search: $filter->search,
            officeId: $filter->officeId,
        );

        return array_values(array_map(
            static fn (Customer $customer): CustomerData => CustomerData::fromModel($customer),
            $items
        ));
    }

    public function find(int $id): CustomerData
    {
        /** @var Customer $customer */
        $customer = $this->customers->findByIdOrFail($id);

        return CustomerData::fromModel($customer);
    }

    public function create(CreateCustomerData $data): CustomerData
    {
        /** @var Customer $customer */
        $customer = $this->customers->create($data->toPersistenceArray());

        return CustomerData::fromModel($customer);
    }

    public function update(int $id, UpdateCustomerData $data): CustomerData
    {
        /** @var Customer $customer */
        $customer = $this->customers->update($id, $data->toPersistenceArray());

        return CustomerData::fromModel($customer);
    }

    public function delete(int $id): bool
    {
        return $this->customers->delete($id);
    }
}
