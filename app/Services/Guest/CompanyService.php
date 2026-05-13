<?php

namespace App\Services\Guest;

use App\Models\Company;
use App\Models\Team;
use Illuminate\Support\Collection;

class CompanyService
{
    public function getCompanies(Team $team, array $filters): Collection
    {
        return Company::where('team_id', $team->id)
            ->when($filters['search'] ?? null, fn($q, $v) => $q->where('name', 'like', "%$v%"))
            ->paginate($filters['per_page'] ?? 20);
    }

    public function createCompany(Team $team, array $data): Company
    {
        return Company::create([...$data, 'team_id' => $team->id]);
    }

    public function getCompanyDetails(Company $company): array
    {
        return ['company' => $company->load(['contacts', 'reservations'])];
    }

    public function updateCompany(Company $company, array $data): void
    {
        $company->update($data);
    }

    public function deleteCompany(Company $company): void
    {
        $company->delete();
    }

    public function addContact(Company $company, array $data): void
    {
        // Add contact logic
    }

    public function getStatement(Company $company): array
    {
        return ['transactions' => [], 'balance' => 0];
    }
}
