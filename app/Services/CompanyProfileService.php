<?php

namespace App\Services;

use App\Models\CompanyProfile;
use App\Models\UploadedMedia;
use Illuminate\Support\Facades\DB;

class CompanyProfileService
{
    public function list(array $filters)
    {
        return CompanyProfile::query()
            ->with(['city', 'media'])
            ->withCount('guests')
            ->filter($filters)
            ->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $company = CompanyProfile::create($data);
            $this->attachMedia($company, $data['media_ids'] ?? []);
            return $company;
        });
    }

    public function update(CompanyProfile $company, array $data)
    {
        return DB::transaction(function () use ($company, $data) {
            $company->update($data);
            $this->attachMedia($company, $data['media_ids'] ?? []);
            return $company->refresh();
        });
    }

    public function delete(CompanyProfile $company)
    {
        return $company->delete();
    }

    public function restore(int $id)
    {
        $company = CompanyProfile::withTrashed()->findOrFail($id);
        return $company->restore();
    }

    private function attachMedia(CompanyProfile $company, array $ids): void
    {
        if (!count($ids)) return;

        UploadedMedia::whereIn('id', $ids)->update([
            'owner_id' => $company->id,
            'owner_type' => CompanyProfile::class,
        ]);
    }
}
