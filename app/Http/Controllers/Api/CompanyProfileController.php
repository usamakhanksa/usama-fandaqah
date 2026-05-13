<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyProfileRequest;
use App\Http\Resources\CompanyProfileResource;
use App\Models\CompanyProfile;
use App\Services\CompanyProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyProfileController extends Controller
{
    protected $service;

    public function __construct(CompanyProfileService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        // Simple permission check (standard)
        if (!Auth::user()->hasPermissionTo('guests.view')) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        $companies = $this->service->list($request->all());
        return CompanyProfileResource::collection($companies);
    }

    public function store(StoreCompanyProfileRequest $request)
    {
        if (!Auth::user()->hasPermissionTo('guests.create')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $company = $this->service->create($request->validated());
        return new CompanyProfileResource($company->load(['city', 'media'])->loadCount('guests'));
    }

    public function show(CompanyProfile $companyProfile)
    {
        if (!Auth::user()->hasPermissionTo('guests.view')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return new CompanyProfileResource($companyProfile->load(['city', 'media', 'country', 'companyGroup'])->loadCount('guests'));
    }

    public function update(StoreCompanyProfileRequest $request, CompanyProfile $companyProfile)
    {
        if (!Auth::user()->hasPermissionTo('guests.edit')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $company = $this->service->update($companyProfile, $request->validated());
        return new CompanyProfileResource($company->load(['city', 'media'])->loadCount('guests'));
    }

    public function destroy(CompanyProfile $companyProfile)
    {
        if (!Auth::user()->hasPermissionTo('guests.delete')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->service->delete($companyProfile);
        return response()->json(['message' => 'Company successfully deleted']);
    }

    public function restore($id)
    {
        if (!Auth::user()->hasPermissionTo('guests.edit')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->service->restore($id);
        return response()->json(['message' => 'Company successfully restored']);
    }
}
