<?php

declare(strict_types=1);

namespace App\Application\Actions\Customer;

use App\Application\Actions\ActionInterface;
use App\Application\Services\CustomerService;

/**
 * @implements ActionInterface<bool>
 */
final class DeleteCustomerAction implements ActionInterface
{
    public function __construct(
        private readonly CustomerService $customers,
    ) {
    }

    public function execute(mixed ...$args): bool
    {
        /** @var int $id */
        $id = $args[0];

        return $this->customers->delete($id);
    }
}
