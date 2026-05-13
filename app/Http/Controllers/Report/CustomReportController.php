<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\CustomReport;
use App\Services\Reports\CustomReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomReportController extends Controller
{
    protected $customReportService;

    public function __construct(CustomReportService $customReportService)
    {
        $this->customReportService = $customReportService;
    }

    public function index()
    {
        $reports = CustomReport::where('team_id', auth()->user()->team_id)
            ->orWhere('is_shared', true)
            ->orderBy('name')
            ->paginate(20);

        return Inertia::render('Reports/CustomReports/Index', [
            'reports' => $reports,
        ]);
    }

    public function create()
    {
        return Inertia::render('Reports/CustomReports/Create', [
            'availableColumns' => $this->customReportService->getAvailableColumns('reservations'),
            'report' => null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'module' => 'required|in:reservations,finance,rooms,guests,pos',
            'columns' => 'required|array|min:1',
            'columns.*.key' => 'required|string',
            'columns.*.label' => 'required|string',
            'filters' => 'nullable|array',
            'sort_by' => 'nullable|string',
            'sort_direction' => 'nullable|in:asc,desc',
            'group_by' => 'nullable|string',
            'is_shared' => 'boolean',
        ]);

        $validated['team_id'] = auth()->user()->team_id;
        $validated['created_by'] = auth()->id();

        $report = CustomReport::create($validated);

        return redirect()->route('custom-reports.show', $report)
            ->with('success', 'Report created successfully.');
    }

    public function show(CustomReport $customReport)
    {
        if ($customReport->team_id !== auth()->user()->team_id && !$customReport->is_shared) {
            abort(403);
        }

        $columns = $customReport->columns ?? [];

        return Inertia::render('Reports/CustomReports/Show', [
            'report' => $customReport,
            'columns' => $columns,
        ]);
    }

    public function edit(CustomReport $customReport)
    {
        if ($customReport->team_id !== auth()->user()->team_id) {
            abort(403);
        }

        return Inertia::render('Reports/CustomReports/Create', [
            'report' => $customReport,
            'availableColumns' => $this->customReportService->getAvailableColumns($customReport->module),
        ]);
    }

    public function update(Request $request, CustomReport $customReport)
    {
        if ($customReport->team_id !== auth()->user()->team_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'module' => 'required|in:reservations,finance,rooms,guests,pos',
            'columns' => 'required|array|min:1',
            'columns.*.key' => 'required|string',
            'columns.*.label' => 'required|string',
            'filters' => 'nullable|array',
            'sort_by' => 'nullable|string',
            'sort_direction' => 'nullable|in:asc,desc',
            'group_by' => 'nullable|string',
            'is_shared' => 'boolean',
        ]);

        $customReport->update($validated);

        return redirect()->route('custom-reports.show', $customReport)
            ->with('success', 'Report updated successfully.');
    }

    public function destroy(CustomReport $customReport)
    {
        if ($customReport->team_id !== auth()->user()->team_id) {
            abort(403);
        }

        $customReport->delete();

        return redirect()->route('custom-reports.index')
            ->with('success', 'Report deleted successfully.');
    }

    public function run(CustomReport $customReport)
    {
        if ($customReport->team_id !== auth()->user()->team_id && !$customReport->is_shared) {
            abort(403);
        }

        $results = $this->customReportService->executeReport($customReport);

        return response()->json([
            'results' => $results,
            'columns' => $customReport->columns,
        ]);
    }

    public function export(CustomReport $customReport, Request $request)
    {
        if ($customReport->team_id !== auth()->user()->team_id && !$customReport->is_shared) {
            abort(403);
        }

        $format = $request->get('format', 'csv');
        return $this->customReportService->exportReport($customReport, $format);
    }

    public function preview(Request $request)
    {
        $validated = $request->validate([
            'module' => 'required|in:reservations,finance,rooms,guests,pos',
            'columns' => 'required|array|min:1',
            'columns.*.key' => 'required|string',
            'columns.*.label' => 'required|string',
            'filters' => 'nullable|array',
            'sort_by' => 'nullable|string',
            'sort_direction' => 'nullable|in:asc,desc',
            'group_by' => 'nullable|string',
        ]);

        $results = $this->customReportService->preview(
            $validated['module'],
            $validated['columns'],
            $validated['filters'] ?? [],
            $validated['sort_by'] ?? null,
            $validated['sort_direction'] ?? 'asc',
            $validated['group_by'] ?? null
        );

        return response()->json([
            'results' => $results,
            'columns' => $validated['columns'],
        ]);
    }

    public function availableColumns(Request $request)
    {
        $module = $request->get('module', 'reservations');
        return response()->json([
            'columns' => $this->customReportService->getAvailableColumns($module),
        ]);
    }
}