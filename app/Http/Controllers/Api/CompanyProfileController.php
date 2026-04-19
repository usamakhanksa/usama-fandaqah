<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyProfileRequest;
use App\Http\Resources\CompanyProfileResource;
use App\Models\CompanyDraft;
use App\Models\CompanyProfile;
use App\Models\UploadedMedia;
use Illuminate\Http\Request;

class CompanyProfileController extends Controller
{
    public function index(Request $request)
    {
        $query = CompanyProfile::query()->with(['city', 'media'])->withCount('guests');

        if ($request->filled('search')) {
            $term = $request->string('search');
            $query->where(fn ($q) => $q->where('company_name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('mobile_number', 'like', "%{$term}%"));
        }

        return CompanyProfileResource::collection($query->orderBy('company_name')->paginate($request->integer('per_page', 10))->withQueryString());
    }

    public function store(StoreCompanyProfileRequest $request)
    {
        $company = CompanyProfile::create($request->validated());
        $this->attachMedia($company, $request->input('media_ids', []));

        return new CompanyProfileResource($company->load(['city', 'media'])->loadCount('guests'));
    }

    public function update(StoreCompanyProfileRequest $request, CompanyProfile $companyProfile)
    {
        $companyProfile->update($request->validated());
        $this->attachMedia($companyProfile, $request->input('media_ids', []));

        return new CompanyProfileResource($companyProfile->refresh()->load(['city', 'media'])->loadCount('guests'));
    }

    public function destroy(CompanyProfile $companyProfile)
    {
        $companyProfile->delete();

        return response()->json(['message' => 'Company deleted']);
    }

    public function saveDraft(Request $request)
    {
        $payload = $request->validate(['payload' => ['required', 'array']]);

        $draft = CompanyDraft::create([
            'user_id' => $request->user()?->id,
            'payload' => $payload['payload'],
        ]);

        return response()->json($draft, 201);
    }

    public function latestDraft(Request $request)
    {
        $draft = CompanyDraft::query()
            ->when($request->user(), fn ($q) => $q->where('user_id', $request->user()->id))
            ->latest()
            ->first();

        return response()->json(['data' => $draft]);
    }

    private function attachMedia(CompanyProfile $company, array $ids): void
    {
        if (! count($ids)) {
            return;
        }

        UploadedMedia::query()->whereIn('id', $ids)->update([
            'owner_id' => $company->id,
            'owner_type' => CompanyProfile::class,
        ]);
    }
}
