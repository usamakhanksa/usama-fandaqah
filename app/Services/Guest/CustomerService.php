<?php

namespace App\Services\Guest;

use App\Models\Customer;
use App\Models\Team;
use Illuminate\Support\Collection;

class CustomerService
{
    public function getCustomers(Team $team, array $filters): Collection
    {
        return Customer::where('team_id', $team->id)
            ->when($filters['search'] ?? null, fn($q, $v) => $q->where('name', 'like', "%$v%"))
            ->paginate($filters['per_page'] ?? 20);
    }

    public function getCreateData(Team $team): array
    {
        return ['countries' => [], 'id_types' => []];
    }

    public function createCustomer(Team $team, array $data): Customer
    {
        return Customer::create([...$data, 'team_id' => $team->id, 'created_by' => auth()->id()]);
    }

    public function getCustomerDetails(Customer $customer): array
    {
        return ['customer' => $customer->load(['reservations', 'comments'])];
    }

    public function getEditData(Customer $customer): array
    {
        return ['customer' => $customer];
    }

    public function updateCustomer(Customer $customer, array $data): void
    {
        $customer->update($data);
    }

    public function deleteCustomer(Customer $customer): void
    {
        $customer->delete();
    }

    public function mergeCustomers(array $sourceIds, int $targetId): void
    {
        // Merge logic here
    }

    public function getHistory(Customer $customer): array
    {
        return ['reservations' => $customer->reservations, 'activities' => []];
    }
}
