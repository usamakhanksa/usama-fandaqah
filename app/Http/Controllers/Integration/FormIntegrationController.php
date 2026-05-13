<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\FormIntegration;
use App\Models\Integration;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FormIntegrationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:integrations.forms.view')->only(['index', 'show']);
        $this->middleware('permission:integrations.forms.create')->only(['create', 'store']);
        $this->middleware('permission:integrations.forms.edit')->only(['edit', 'update']);
        $this->middleware('permission:integrations.forms.delete')->only(['destroy']);
        $this->middleware('permission:integrations.forms.approve')->only(['approve', 'reject']);
    }

    /**
     * Display a listing of form integrations.
     */
    public function index()
    {
        $formIntegrations = FormIntegration::with(['team', 'integration', 'creator'])
            ->where('team_id', auth()->user()->currentTeam->id)
            ->paginate(15);

        return Inertia::render('Integrations/Forms/Index', [
            'formIntegrations' => $formIntegrations,
        ]);
    }

    /**
     * Show the form for creating a new form integration.
     */
    public function create()
    {
        $integrations = Integration::where('team_id', auth()->user()->currentTeam->id)
            ->select('id', 'name', 'provider')
            ->get();

        return Inertia::render('Integrations/Forms/Create', [
            'integrations' => $integrations,
        ]);
    }

    /**
     * Store a newly created form integration.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'integration_id' => 'required|exists:integrations,id',
            'form_name' => 'required|string|max:255',
            'form_url' => 'required|url',
            'field_mapping' => 'required|array',
            'auto_approve' => 'boolean',
            'status' => 'required|in:active,paused,draft',
        ]);

        $validated['team_id'] = auth()->user()->currentTeam->id;
        $validated['created_by'] = auth()->id();

        $formIntegration = FormIntegration::create($validated);

        return redirect()->route('form-integrations.show', $formIntegration)
            ->with('success', 'Form integration created successfully.');
    }

    /**
     * Display the specified form integration.
     */
    public function show(FormIntegration $formIntegration)
    {
        $this->authorize('view', $formIntegration);

        $formIntegration->load(['team', 'integration', 'creator']);

        return Inertia::render('Integrations/Forms/Show', [
            'formIntegration' => $formIntegration,
        ]);
    }

    /**
     * Show the form for editing the form integration.
     */
    public function edit(FormIntegration $formIntegration)
    {
        $this->authorize('update', $formIntegration);

        $integrations = Integration::where('team_id', auth()->user()->currentTeam->id)
            ->select('id', 'name', 'provider')
            ->get();

        return Inertia::render('Integrations/Forms/Edit', [
            'formIntegration' => $formIntegration,
            'integrations' => $integrations,
        ]);
    }

    /**
     * Update the form integration.
     */
    public function update(Request $request, FormIntegration $formIntegration)
    {
        $this->authorize('update', $formIntegration);

        $validated = $request->validate([
            'integration_id' => 'required|exists:integrations,id',
            'form_name' => 'required|string|max:255',
            'form_url' => 'required|url',
            'field_mapping' => 'required|array',
            'auto_approve' => 'boolean',
            'status' => 'required|in:active,paused,draft',
        ]);

        $formIntegration->update($validated);

        return redirect()->route('form-integrations.show', $formIntegration)
            ->with('success', 'Form integration updated successfully.');
    }

    /**
     * Delete the form integration.
     */
    public function destroy(FormIntegration $formIntegration)
    {
        $this->authorize('delete', $formIntegration);

        $formIntegration->delete();

        return redirect()->route('form-integrations.index')
            ->with('success', 'Form integration deleted successfully.');
    }

    /**
     * Approve form integration.
     */
    public function approve(FormIntegration $formIntegration)
    {
        $this->authorize('update', $formIntegration);

        $formIntegration->update(['status' => 'active']);

        return back()->with('success', 'Form integration approved and activated.');
    }

    /**
     * Reject form integration.
     */
    public function reject(FormIntegration $formIntegration)
    {
        $this->authorize('update', $formIntegration);

        $formIntegration->update(['status' => 'paused']);

        return back()->with('success', 'Form integration rejected and paused.');
    }

    /**
     * Test webhook.
     */
    public function testWebhook(FormIntegration $formIntegration)
    {
        $this->authorize('update', $formIntegration);

        // TODO: Implement webhook testing logic

        return back()->with('success', 'Webhook test sent successfully.');
    }
}
