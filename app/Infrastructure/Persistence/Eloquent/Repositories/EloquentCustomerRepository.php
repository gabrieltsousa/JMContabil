<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Customer\Contracts\CustomerRepositoryInterface;
use App\Domain\Customer\Enums\CustomerStatus;
use App\Domain\Customer\ValueObjects\DueDay;
use App\Infrastructure\Persistence\Eloquent\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class EloquentCustomerRepository implements CustomerRepositoryInterface
{
    public function findById(int $id): ?Customer
    {
        return Customer::query()->find($id);
    }

    public function findByIdOrFail(int $id): Customer
    {
        return Customer::query()->findOrFail($id);
    }

    /**
     * {@inheritdoc}
     */
    public function all(?int $officeId = null): array
    {
        return Customer::query()
            ->forOffice($officeId)
            ->orderBy('name')
            ->get()
            ->all();
    }

    /**
     * {@inheritdoc}
     */
    public function findActiveByDueDay(DueDay $dueDay, ?int $officeId = null): array
    {
        return Customer::query()
            ->forOffice($officeId)
            ->active()
            ->dueOn($dueDay->value())
            ->orderBy('name')
            ->get()
            ->all();
    }

    /**
     * {@inheritdoc}
     */
    public function findByStatus(CustomerStatus $status, ?int $officeId = null): array
    {
        return Customer::query()
            ->forOffice($officeId)
            ->where('status', $status)
            ->orderBy('name')
            ->get()
            ->all();
    }

    /**
     * {@inheritdoc}
     */
    public function filter(
        ?CustomerStatus $status = null,
        ?int $dueDay = null,
        ?string $search = null,
        ?int $officeId = null,
    ): array {
        return $this->filterQuery($status, $dueDay, $search, $officeId)
            ->orderBy('name')
            ->get()
            ->all();
    }

    public function create(array $attributes): Customer
    {
        return Customer::query()->create($attributes);
    }

    public function update(int $id, array $attributes): Customer
    {
        $customer = $this->findByIdOrFail($id);
        $customer->fill($attributes);
        $customer->save();

        return $customer->refresh();
    }

    public function delete(int $id): bool
    {
        $customer = $this->findById($id);

        if ($customer === null) {
            throw (new ModelNotFoundException)->setModel(Customer::class, [$id]);
        }

        return (bool) $customer->delete();
    }

    public function countByStatus(CustomerStatus $status, ?int $officeId = null): int
    {
        return Customer::query()
            ->forOffice($officeId)
            ->where('status', $status)
            ->count();
    }

    private function filterQuery(
        ?CustomerStatus $status,
        ?int $dueDay,
        ?string $search,
        ?int $officeId,
    ): Builder {
        return Customer::query()
            ->forOffice($officeId)
            ->when($status !== null, fn (Builder $query) => $query->where('status', $status))
            ->when($dueDay !== null, fn (Builder $query) => $query->where('due_day', $dueDay))
            ->when($search !== null && $search !== '', function (Builder $query) use ($search): void {
                $term = '%'.$search.'%';
                $query->where(function (Builder $inner) use ($term): void {
                    $inner->where('name', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            });
    }
}
