<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreWidgetRequest;
use App\Http\Requests\Dashboard\UpdateWidgetRequest;
use App\Services\Dashboard\WidgetService;
use App\Models\DashboardWidget;
use Illuminate\Http\Request;

class WidgetController extends Controller
{
    protected WidgetService $service;

    public function __construct(WidgetService $service)
    {
        $this->service = $service;
    }

    public function store(StoreWidgetRequest $request)
    {
        $this->authorize('dashboard.widgets.manage');
        
        $widget = $this->service->createWidget(
            $request->user()->currentTeam,
            $request->validated()
        );
        
        return response()->json(['data' => $widget], 201);
    }

    public function update(UpdateWidgetRequest $request, DashboardWidget $widget)
    {
        $this->authorize('dashboard.widgets.manage');
        
        $widget = $this->service->updateWidget($widget, $request->validated());
        
        return response()->json(['data' => $widget]);
    }

    public function destroy(DashboardWidget $widget)
    {
        $this->authorize('dashboard.widgets.manage');
        
        $this->service->deleteWidget($widget);
        
        return response()->noContent();
    }

    public function reorder(Request $request)
    {
        $this->authorize('dashboard.widgets.manage');
        
        $request->validate([
            'widgets' => 'required|array',
            'widgets.*.id' => 'required|exists:dashboard_widgets,id',
            'widgets.*.position' => 'required|integer',
        ]);
        
        $this->service->reorderWidgets($request->input('widgets'));
        
        return response()->json(['message' => 'Widgets reordered successfully']);
    }
}
