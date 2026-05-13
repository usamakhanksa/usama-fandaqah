<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Services\Guest\CompanyService;
use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyController extends Controller
{
    protected CompanyService $service;

    public function __construct(CompanyService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $this->authorize('companies.view');
        $companies = $this->service->getCompanies($request->user()->currentTeam, $request->all());
        return Inertia::render('Companies/Index', ['companies' => $companies]);
    }

    public function create(Request $request)
    {
        $this->authorize('companies.create');
        return Inertia::render('Companies/Create');
    }

    public function store(Request $request)
    {
        $this->authorize('companies.create');
        $company = $this->service->createCompany($request->user()->currentTeam, $request->all());
        return redirect()->route('companies.show', $company)->with('success', 'Company created');
    }

    public function show(Company $company)
    {
        $this->authorize('companies.view', $company);
        $data = $this->service->getCompanyDetails($company);
        return Inertia::render('Companies/Show', $data);
    }

    public function edit(Company $company)
    {
        $this->authorize('companies.update', $company);
        return Inertia::render('Companies/Edit', ['company' => $company]);
    }

    public function update(Request $request, Company $company)
    {
        $this->authorize('companies.update', $company);
        $this->service->updateCompany($company, $request->all());
        return redirect()->route('companies.show', $company)->with('success', 'Company updated');
    }

    public function destroy(Company $company)
    {
        $this->authorize('companies.delete', $company);
        $this->service->deleteCompany($company);
        return redirect()->route('companies.index')->with('success', 'Company deleted');
    }

    public function addContact(Request $request, Company $company)
    {
        $this->authorize('companies.update', $company);
        $this->service->addContact($company, $request->all());
        return response()->json(['message' => 'Contact added']);
    }

    public function statement(Company $company)
    {
        $this->authorize('companies.view', $company);
        $statement = $this->service->getStatement($company);
        return response()->json(['data' => $statement]);
    }
}
