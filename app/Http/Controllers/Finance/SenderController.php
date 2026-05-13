<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Sender;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SenderController extends Controller
{
    public function index()
    {
        $senders = Sender::with('bank')->orderBy('name')->get();
        return Inertia::render('Finance/Senders/Index', [
            'senders' => $senders
        ]);
    }

    public function create()
    {
        $banks = Bank::where('is_active', true)->orderBy('name')->get();
        return Inertia::render('Finance/Senders/Create', [
            'banks' => $banks
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'type' => 'required|in:individual,company,government',
            'id_number' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'bank_id' => 'nullable|exists:banks,id',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['team_id'] = auth()->user()->current_team_id;

        Sender::create($validated);

        return redirect()->route('finance.senders.index')->with('success', 'Sender created successfully.');
    }

    public function show(Sender $sender)
    {
        return Inertia::render('Finance/Senders/Show', [
            'sender' => $sender->load('bank')
        ]);
    }

    public function edit(Sender $sender)
    {
        $banks = Bank::where('is_active', true)->orderBy('name')->get();
        return Inertia::render('Finance/Senders/Edit', [
            'sender' => $sender,
            'banks' => $banks
        ]);
    }

    public function update(Request $request, Sender $sender)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'type' => 'required|in:individual,company,government',
            'id_number' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'bank_id' => 'nullable|exists:banks,id',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $sender->update($validated);

        return redirect()->route('finance.senders.index')->with('success', 'Sender updated successfully.');
    }

    public function toggleActive(Sender $sender)
    {
        $sender->update(['is_active' => !$sender->is_active]);
        return back()->with('success', 'Sender status updated.');
    }

    public function destroy(Sender $sender)
    {
        $sender->delete();
        return redirect()->route('finance.senders.index')->with('success', 'Sender deleted successfully.');
    }
}
