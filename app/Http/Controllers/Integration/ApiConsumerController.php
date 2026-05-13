<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\ApiConsumer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ApiConsumerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:integrations.api_consumers.view')->only(['index', 'show']);
        $this->middleware('permission:integrations.api_consumers.create')->only(['create', 'store']);
        $this->middleware('permission:integrations.api_consumers.edit')->only(['edit', 'update']);
        $this->middleware('permission:integrations.api_consumers.delete')->only(['destroy']);
    }

    /**
     * Display a listing of API consumers.
     */
    public function index()
    {
        $consumers = ApiConsumer::with(['team', 'creator', 'tokens'])
            ->where('team_id', auth()->user()->currentTeam->id)
            ->paginate(15);

        return Inertia::render('Integrations/ApiConsumers/Index', [
            'consumers' => $consumers,
        ]);
    }

    /**
     * Show the form for creating a new API consumer.
     */
    public function create()
    {
        return Inertia::render('Integrations/ApiConsumers/Create');
    }

    /**
     * Store a newly created API consumer.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'allowed_ips' => 'nullable|array',
            'allowed_endpoints' => 'nullable|array',
            'rate_limit_per_minute' => 'required|integer|min:1|max:10000',
        ]);

        $validated['team_id'] = auth()->user()->currentTeam->id;
        $validated['created_by'] = auth()->id();

        $consumer = ApiConsumer::create($validated);

        return redirect()->route('api-consumers.show', $consumer)
            ->with('success', 'API consumer created successfully.');
    }

    /**
     * Display the specified API consumer.
     */
    public function show(ApiConsumer $consumer)
    {
        $this->authorize('view', $consumer);

        $consumer->load(['team', 'creator', 'tokens']);

        return Inertia::render('Integrations/ApiConsumers/Show', [
            'consumer' => $consumer,
        ]);
    }

    /**
     * Show the form for editing the API consumer.
     */
    public function edit(ApiConsumer $consumer)
    {
        $this->authorize('update', $consumer);

        return Inertia::render('Integrations/ApiConsumers/Edit', [
            'consumer' => $consumer,
        ]);
    }

    /**
     * Update the API consumer.
     */
    public function update(Request $request, ApiConsumer $consumer)
    {
        $this->authorize('update', $consumer);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'allowed_ips' => 'nullable|array',
            'allowed_endpoints' => 'nullable|array',
            'rate_limit_per_minute' => 'required|integer|min:1|max:10000',
        ]);

        $consumer->update($validated);

        return redirect()->route('api-consumers.show', $consumer)
            ->with('success', 'API consumer updated successfully.');
    }

    /**
     * Delete the API consumer.
     */
    public function destroy(ApiConsumer $consumer)
    {
        $this->authorize('delete', $consumer);

        $consumer->delete();

        return redirect()->route('api-consumers.index')
            ->with('success', 'API consumer deleted successfully.');
    }

    /**
     * Toggle API consumer active status.
     */
    public function toggleActive(ApiConsumer $consumer)
    {
        $this->authorize('update', $consumer);

        $consumer->update([
            'is_active' => !$consumer->is_active,
        ]);

        return back()->with('success', 'API consumer status updated.');
    }

    /**
     * Get usage statistics for an API consumer.
     */
    public function usageStats(ApiConsumer $consumer)
    {
        $this->authorize('view', $consumer);

        return response()->json([
            'request_count' => $consumer->request_count,
            'last_access_at' => $consumer->last_access_at,
            'is_active' => $consumer->is_active,
        ]);
    }
}
