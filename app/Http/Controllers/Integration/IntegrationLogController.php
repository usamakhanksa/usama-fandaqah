<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use App\Models\IntegrationLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IntegrationLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:integrations.logs.view')->only(['index', 'show']);
        $this->middleware('permission:integrations.logs.clear')->only(['clear']);
    }

    /**
     * Display a listing of integration logs.
     */
    public function index(Request $request)
    {
        $query = IntegrationLog::with(['integration', 'performer'])
            ->where('team_id', auth()->user()->currentTeam->id);

        if ($request->filled('integration_id')) {
            $query->where('integration_id', $request->integration_id);
        }

        if ($request->filled('log_type')) {
            $query->where('log_type', $request->log_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }

        $logs = $query->latest()->paginate(50);

        $integrations = Integration::where('team_id', auth()->user()->currentTeam->id)
            ->select('id', 'name')
            ->get();

        return Inertia::render('Integrations/Logs/Index', [
            'logs' => $logs,
            'integrations' => $integrations,
            'filters' => $request->only(['integration_id', 'log_type', 'date_from', 'date_to', 'action']),
        ]);
    }

    /**
     * Display the specified log.
     */
    public function show(IntegrationLog $log)
    {
        $this->authorize('view', $log);

        return Inertia::render('Integrations/Logs/Show', [
            'log' => $log->load(['integration', 'performer']),
        ]);
    }

    /**
     * Clear logs older than specified days.
     */
    public function clear(Request $request)
    {
        $validated = $request->validate([
            'integration_id' => 'nullable|exists:integrations,id',
            'days' => 'required|integer|min:1|max:365',
        ]);

        $query = IntegrationLog::where('team_id', auth()->user()->currentTeam->id);

        if ($validated['integration_id']) {
            $query->where('integration_id', $validated['integration_id']);
        }

        $deletedCount = $query->where('created_at', '<', now()->subDays($validated['days']))
            ->delete();

        return back()->with('success', "Cleared {$deletedCount} log entries older than {$validated['days']} days.");
    }

    /**
     * Export logs.
     */
    public function export(Request $request)
    {
        // TODO: Implement export functionality
        return response()->json(['message' => 'Export functionality not yet implemented']);
    }
}
