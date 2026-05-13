<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BankController extends Controller
{
    public function index()
    {
        $banks = Bank::orderBy('name')->get();
        return Inertia::render('Finance/Banks/Index', [
            'banks' => $banks
        ]);
    }

    public function create()
    {
        return Inertia::render('Finance/Banks/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:255',
            'branch' => 'nullable|string|max:255',
            'branch_code' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:255',
            'account_name' => 'nullable|string|max:255',
            'currency' => 'required|string|max:3',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['team_id'] = auth()->user()->current_team_id;

        Bank::create($validated);

        return redirect()->route('finance.banks.index')->with('success', 'Bank created successfully.');
    }

    public function show(Bank $bank)
    {
        return Inertia::render('Finance/Banks/Show', [
            'bank' => $bank
        ]);
    }

    public function edit(Bank $bank)
    {
        return Inertia::render('Finance/Banks/Edit', [
            'bank' => $bank
        ]);
    }

    public function update(Request $request, Bank $bank)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:255',
            'branch' => 'nullable|string|max:255',
            'branch_code' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:255',
            'account_name' => 'nullable|string|max:255',
            'currency' => 'required|string|max:3',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $bank->update($validated);

        return redirect()->route('finance.banks.index')->with('success', 'Bank updated successfully.');
    }

    public function toggleActive(Bank $bank)
    {
        $bank->update(['is_active' => !$bank->is_active]);
        return back()->with('success', 'Bank status updated.');
    }

    public function destroy(Bank $bank)
    {
        $bank->delete();
        return redirect()->route('finance.banks.index')->with('success', 'Bank deleted successfully.');
    }
}
