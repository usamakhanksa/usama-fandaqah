<?php

namespace App\Http\Controllers;

use App\Models\ClientProfile;
use App\Http\Requests\SaveClientProfileRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientProfileController extends Controller {
    public function index(Request $request) {
        $this->authorize('viewAny', ClientProfile::class);

        $filters = $request->only(['search', 'type']);

        $profiles = ClientProfile::query()
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->search($s))
            ->when($filters['type'] ?? null, fn ($q, $t) => $q->where('type', $t))
            ->with('membership')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('ClientRelations/Index', [
            'profiles' => $profiles,
            'filters' => $filters,
        ]);
    }

    public function create() {
        $this->authorize('create', ClientProfile::class);
        return Inertia::render('ClientRelations/Create');
    }

    public function store(SaveClientProfileRequest $request) {
        $this->authorize('create', ClientProfile::class);
        $profile = ClientProfile::create($request->validated());
        
        // Auto-create basic membership
        $profile->membership()->create(['tier' => 'standard', 'points' => 0]);

        return redirect()->route('client-relations.show', $profile)->with('success', 'Client created successfully.');
    }

    public function show(ClientProfile $client_relation) {
        $this->authorize('view', $client_relation);
        
        $client_relation->load([
            'activities' => fn($q) => $q->latest('scheduled_at'), 
            'membership', 
            'sales' => fn($q) => $q->latest()
        ]);

        return Inertia::render('ClientRelations/Show', [
            'client' => $client_relation,
        ]);
    }

    public function edit(ClientProfile $client_relation) {
        $this->authorize('update', $client_relation);
        return Inertia::render('ClientRelations/Edit', ['client' => $client_relation]);
    }

    public function update(SaveClientProfileRequest $request, ClientProfile $client_relation) {
        $this->authorize('update', $client_relation);
        $client_relation->update($request->validated());
        return redirect()->route('client-relations.show', $client_relation)->with('success', 'Client updated successfully.');
    }

    public function destroy(ClientProfile $client_relation) {
        $this->authorize('delete', $client_relation);
        $client_relation->delete();
        return redirect()->route('client-relations.index')->with('success', 'Client deleted successfully.');
    }
}
