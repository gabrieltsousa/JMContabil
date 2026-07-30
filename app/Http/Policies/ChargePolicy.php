<?php

declare(strict_types=1);

namespace App\Http\Policies;

use App\Infrastructure\Persistence\Eloquent\Models\Charge;
use App\Models\User;

final class ChargePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Charge $charge): bool
    {
        return $this->sameOffice($user, $charge->office_id);
    }

    public function send(User $user): bool
    {
        return true;
    }

    public function sendOne(User $user, Charge $charge): bool
    {
        return $this->sameOffice($user, $charge->office_id);
    }

    private function sameOffice(User $user, ?int $officeId): bool
    {
        if ($user->office_id === null || $officeId === null) {
            return true;
        }

        return $user->office_id === $officeId;
    }
}
