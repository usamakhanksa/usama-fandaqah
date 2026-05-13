<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guest\StoreCustomerRequest;
use App\Http\Requests\Guest\UpdateCustomerRequest;
use App\Services\Guest\CustomerService;
use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    protected CustomerService $service;

    public function __construct(CustomerService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $this->authorize('customers.view');
        $customers = $this->service->getCustomers($request->user()->currentTeam, $request->all());
        return Inertia::render('Customers/Index', ['customers' => $customers, 'filters' => $request->all()]);
    }

    public function create(Request $request)
    {
        $this->authorize('customers.create');
        $data = $this->service->getCreateData($request->user()->currentTeam);
        return Inertia::render('Customers/Create', $data);
    }

    public function store(StoreCustomerRequest $request)
    {
        $this->authorize('customers.create');
        $customer = $this->service->createCustomer($request->user()->currentTeam, $request->validated());
        return redirect()->route('customers.show', $customer)->with('success', 'Customer created successfully');
    }

    public function show(Customer $customer)
    {
        $this->authorize('customers.view', $customer);
        $data = $this->service->getCustomerDetails($customer);
        return Inertia::render('Customers/Show', $data);
    }

    public function edit(Customer $customer)
    {
        $this->authorize('customers.update', $customer);
        $data = $this->service->getEditData($customer);
        return Inertia::render('Customers/Edit', $data);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $this->authorize('customers.update', $customer);
        $this->service->updateCustomer($customer, $request->validated());
        return redirect()->route('customers.show', $customer)->with('success', 'Customer updated successfully');
    }

    public function destroy(Customer $customer)
    {
        $this->authorize('customers.delete', $customer);
        $this->service->deleteCustomer($customer);
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully');
    }

    public function merge(Request $request)
    {
        $this->authorize('customers.merge');
        $request->validate(['source_ids' => 'required|array', 'target_id' => 'required|exists:customers,id']);
        $this->service->mergeCustomers($request->input('source_ids'), $request->input('target_id'));
        return response()->json(['message' => 'Customers merged successfully']);
    }

    public function history(Customer $customer)
    {
        $this->authorize('customers.view', $customer);
        $history = $this->service->getHistory($customer);
        return response()->json(['data' => $history]);
    }
}
