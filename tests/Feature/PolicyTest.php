<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Policies\ChargePolicy;
use App\Http\Policies\CustomerPolicy;
use App\Infrastructure\Persistence\Eloquent\Models\Charge;
use App\Infrastructure\Persistence\Eloquent\Models\Customer;
use App\Infrastructure\Persistence\Eloquent\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PolicyTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function customer_policy_scopes_by_office(): void
    {
        $officeA = Office::factory()->create();
        $officeB = Office::factory()->create();

        $userA = User::factory()->forOffice($officeA)->create();
        $customerB = Customer::factory()->forOffice($officeB)->create();

        $policy = new CustomerPolicy;

        $this->assertTrue($policy->viewAny($userA));
        $this->assertFalse($policy->view($userA, $customerB));
        $this->assertFalse($policy->update($userA, $customerB));
        $this->assertFalse($policy->delete($userA, $customerB));
    }

    #[Test]
    public function charge_policy_allows_same_office_send(): void
    {
        $office = Office::factory()->create();
        $user = User::factory()->forOffice($office)->create();
        $charge = Charge::factory()->create([
            'office_id' => $office->id,
            'customer_id' => Customer::factory()->forOffice($office),
        ]);

        $policy = new ChargePolicy;

        $this->assertTrue($policy->send($user));
        $this->assertTrue($policy->sendOne($user, $charge));
    }
}
