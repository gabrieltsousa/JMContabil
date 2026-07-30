<?php

declare(strict_types=1);

namespace App\Http\Policies;

use App\Infrastructure\Persistence\Eloquent\Models\Customer;
use App\Models\User;

final class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Customer $customer): bool
    {
        return $this->sameOffice($user, $customer->office_id);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Customer $customer): bool
    {
        return $this->sameOffice($user, $customer->office_id);
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $this->sameOffice($user, $customer->office_id);
    }

    private function sameOffice(User $user, ?int $officeId): bool
    {
        if ($user->office_id === null || $officeId === null) {
            return true;
        }

        return $user->office_id === $officeId;
    }
}
