<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyGroup;
use App\Models\CompanyProfile;
use Illuminate\Http\Request;

class CompanyGroupController extends Controller
{
    public function index(Request $request)
    {
        $teamId = auth()->user()->current_team_id;
        $groups = CompanyGroup::where('team_id', $teamId)
            ->withCount('companies')
            ->get();
        return response()->json(['data' => $groups]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'credit_limit' => 'numeric|min:0',
            'payment_terms_days' => 'integer|min:0',
        ]);

        $validated['team_id'] = auth()->user()->current_team_id;
        $group = CompanyGroup::create($validated);

        return response()->json(['data' => $group], 201);
    }

    public function update(Request $request, CompanyGroup $companyGroup)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'credit_limit' => 'numeric|min:0',
            'payment_terms_days' => 'integer|min:0',
        ]);

        $companyGroup->update($validated);
        return response()->json(['data' => $companyGroup]);
    }

    public function destroy(CompanyGroup $companyGroup)
    {
        // Unlink companies before deleting
        CompanyProfile::where('company_group_id', $companyGroup->id)->update(['company_group_id' => null]);
        $companyGroup->delete();
        return response()->json(null, 204);
    }

    public function linkCompany(Request $request, CompanyGroup $companyGroup)
    {
        $request->validate(['company_id' => 'required|exists:company_profiles,id']);
        CompanyProfile::where('id', $request->company_id)->update(['company_group_id' => $companyGroup->id]);
        return response()->json(['message' => 'Company linked successfully']);
    }

    public function unlinkCompany(Request $request, CompanyProfile $companyProfile)
    {
        $companyProfile->update(['company_group_id' => null]);
        return response()->json(['message' => 'Company unlinked successfully']);
    }
}
