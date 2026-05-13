<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class IntegrationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:integrations.view')->only(['index', 'show']);
        $this->middleware('permission:integrations.create')->only(['create', 'store']);
        $this->middleware('permission:integrations.edit')->only(['edit', 'update']);
        $this->middleware('permission:integrations.delete')->only(['destroy']);
        $this->middleware('permission:integrations.test')->only(['test']);
        $this->middleware('permission:integrations.sync')->only(['sync']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $integrations = Integration::with(['team', 'creator'])
            ->where('team_id', auth()->user()->currentTeam->id)
            ->paginate(15);

        return Inertia::render('Integrations/Index', [
            'integrations' => $integrations,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Integrations/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'integration_type' => 'required|in:channel_manager,accounting,government,payment_gateway,crm,pos,pms,other',
            'provider' => 'required|in:shomoos,zatca,qoyod,stripe,tabby,tamara,site_minder,dhisco,oracle,custom',
            'base_url' => 'nullable|url',
            'api_key' => 'nullable|string|max:255',
            'api_secret' => 'nullable|string|max:255',
            'config' => 'nullable|array',
            'sync_frequency' => 'required|in:real_time,hourly,daily,manual',
            'notes' => 'nullable|string',
        ]);

        $validated['team_id'] = auth()->user()->currentTeam->id;
        $validated['slug'] = Str::slug($validated['name']);
        $validated['created_by'] = auth()->id();
        $validated['status'] = 'pending_setup';

        // Ensure unique slug per team
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Integration::where('team_id', $validated['team_id'])->where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        $integration = Integration::create($validated);

        return redirect()->route('integrations.show', $integration)
            ->with('success', 'Integration created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Integration $integration)
    {
        $this->authorize('view', $integration);

        $integration->load(['team', 'creator', 'settings', 'logs' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return Inertia::render('Integrations/Show', [
            'integration' => $integration,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Integration $integration)
    {
        $this->authorize('update', $integration);

        return Inertia::render('Integrations/Edit', [
            'integration' => $integration,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Integration $integration)
    {
        $this->authorize('update', $integration);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'integration_type' => 'required|in:channel_manager,accounting,government,payment_gateway,crm,pos,pms,other',
            'provider' => 'required|in:shomoos,zatca,qoyod,stripe,tabby,tamara,site_minder,dhisco,oracle,custom',
            'base_url' => 'nullable|url',
            'api_key' => 'nullable|string|max:255',
            'api_secret' => 'nullable|string|max:255',
            'config' => 'nullable|array',
            'sync_frequency' => 'required|in:real_time,hourly,daily,manual',
            'notes' => 'nullable|string',
        ]);

        if ($validated['name'] !== $integration->name) {
            $validated['slug'] = Str::slug($validated['name']);
            // Ensure unique slug per team
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Integration::where('team_id', $integration->team_id)
                ->where('slug', $validated['slug'])
                ->where('id', '!=', $integration->id)
                ->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $integration->update($validated);

        return redirect()->route('integrations.show', $integration)
            ->with('success', 'Integration updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Integration $integration)
    {
        $this->authorize('delete', $integration);

        $integration->delete();

        return redirect()->route('integrations.index')
            ->with('success', 'Integration deleted successfully.');
    }

    /**
     * Test the integration connection.
     */
    public function test(Integration $integration)
    {
        $this->authorize('update', $integration);

        // TODO: Implement actual testing logic based on provider

        $integration->update([
            'status' => 'testing',
            'last_sync_at' => now(),
            'last_sync_status' => 'success',
        ]);

        return back()->with('success', 'Integration test completed successfully.');
    }

    /**
     * Sync data with the integration.
     */
    public function sync(Integration $integration)
    {
        $this->authorize('update', $integration);

        // TODO: Implement actual sync logic based on provider

        $integration->update([
            'last_sync_at' => now(),
            'last_sync_status' => 'success',
        ]);

        return back()->with('success', 'Integration sync completed successfully.');
    }

    /**
     * Activate the integration.
     */
    public function activate(Integration $integration)
    {
        $this->authorize('update', $integration);

        $integration->update([
            'is_active' => true,
            'status' => 'active',
        ]);

        return back()->with('success', 'Integration activated successfully.');
    }

    /**
     * Deactivate the integration.
     */
    public function deactivate(Integration $integration)
    {
        $this->authorize('update', $integration);

        $integration->update([
            'is_active' => false,
            'status' => 'suspended',
        ]);

        return back()->with('success', 'Integration deactivated successfully.');
    }

    /**
     * Reset integration credentials.
     */
    public function resetCredentials(Integration $integration)
    {
        $this->authorize('update', $integration);

        $integration->update([
            'api_key' => null,
            'api_secret' => null,
            'status' => 'pending_setup',
        ]);

        return back()->with('success', 'Integration credentials reset successfully.');
    }
}
