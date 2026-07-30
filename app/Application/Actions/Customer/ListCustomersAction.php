<?php

declare(strict_types=1);

namespace App\Application\Actions\Customer;

use App\Application\Actions\ActionInterface;
use App\Application\DTOs\Customer\CustomerData;
use App\Application\DTOs\Customer\CustomerFilterData;
use App\Application\Services\CustomerService;

/**
 * @implements ActionInterface<list<CustomerData>>
 */
final class ListCustomersAction implements ActionInterface
{
    public function __construct(
        private readonly CustomerService $customers,
    ) {
    }

    public function execute(mixed ...$args): array
    {
        /** @var CustomerFilterData $filter */
        $filter = $args[0] ?? CustomerFilterData::fromArray([]);

        return $this->customers->list($filter);
    }
}
