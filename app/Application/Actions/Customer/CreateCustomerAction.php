<?php

declare(strict_types=1);

namespace App\Application\Actions\Customer;

use App\Application\Actions\ActionInterface;
use App\Application\DTOs\Customer\CreateCustomerData;
use App\Application\DTOs\Customer\CustomerData;
use App\Application\Services\CustomerService;

/**
 * @implements ActionInterface<CustomerData>
 */
final class CreateCustomerAction implements ActionInterface
{
    public function __construct(
        private readonly CustomerService $customers,
    ) {
    }

    public function execute(mixed ...$args): CustomerData
    {
        /** @var CreateCustomerData $data */
        $data = $args[0];

        return $this->customers->create($data);
    }
}
