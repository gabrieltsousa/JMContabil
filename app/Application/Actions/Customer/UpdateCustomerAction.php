<?php

declare(strict_types=1);

namespace App\Application\Actions\Customer;

use App\Application\Actions\ActionInterface;
use App\Application\DTOs\Customer\CustomerData;
use App\Application\DTOs\Customer\UpdateCustomerData;
use App\Application\Services\CustomerService;

/**
 * @implements ActionInterface<CustomerData>
 */
final class UpdateCustomerAction implements ActionInterface
{
    public function __construct(
        private readonly CustomerService $customers,
    ) {
    }

    public function execute(mixed ...$args): CustomerData
    {
        /** @var int $id */
        $id = $args[0];
        /** @var UpdateCustomerData $data */
        $data = $args[1];

        return $this->customers->update($id, $data);
    }
}
