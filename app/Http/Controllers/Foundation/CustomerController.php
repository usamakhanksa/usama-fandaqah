<?php
namespace App\Http\Controllers\Foundation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Foundation\StoreCustomerRequest;
use App\Http\Requests\Foundation\UpdateCustomerRequest;
use App\Models\Foundation\Customer;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index(){ return Inertia::render('Foundation/Profiles/Index',['rows'=>Customer::orderBy('last_name_en')->paginate(10)]); }
    public function create(){ return Inertia::render('Foundation/Profiles/Create'); }
    public function store(StoreCustomerRequest $request){ Customer::create($request->validated()); return to_route('foundation.customers.index')->with('success','Profile created.'); }
    public function show(Customer $customer){ return Inertia::render('Foundation/Profiles/Show',['row'=>$customer]); }
    public function edit(Customer $customer){ return Inertia::render('Foundation/Profiles/Edit',['row'=>$customer]); }
    public function update(UpdateCustomerRequest $request, Customer $customer){ $customer->update($request->validated()); return to_route('foundation.customers.index')->with('success','Profile updated.'); }
    public function destroy(Customer $customer){ $customer->delete(); return to_route('foundation.customers.index')->with('success','Profile deleted.'); }
}
