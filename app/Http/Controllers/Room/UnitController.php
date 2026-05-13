<?php

namespace App\Http\Controllers\Room;

use App\Http\Controllers\Controller;
use App\Http\Requests\Room\StoreUnitRequest;
use App\Http\Requests\Room\UpdateUnitRequest;
use App\Services\Room\UnitService;
use App\Models\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UnitController extends Controller
{
    protected UnitService $service;

    public function __construct(UnitService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $this->authorize('units.view');
        
        $units = $this->service->getUnits(
            $request->user()->currentTeam,
            $request->all()
        );
        
        return Inertia::render('Units/Index', [
            'units' => $units,
            'filters' => $request->all(),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('units.create');
        
        $data = $this->service->getCreateData($request->user()->currentTeam);
        
        return Inertia::render('Units/Create', $data);
    }

    public function store(StoreUnitRequest $request)
    {
        $this->authorize('units.create');
        
        $unit = $this->service->createUnit(
            $request->user()->currentTeam,
            $request->validated()
        );
        
        return redirect()->route('units.show', $unit)
            ->with('success', 'Unit created successfully');
    }

    public function show(Unit $unit)
    {
        $this->authorize('units.view', $unit);
        
        $data = $this->service->getUnitDetails($unit);
        
        return Inertia::render('Units/Show', $data);
    }

    public function edit(Unit $unit)
    {
        $this->authorize('units.update', $unit);
        
        $data = $this->service->getEditData($unit);
        
        return Inertia::render('Units/Edit', $data);
    }

    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        $this->authorize('units.update', $unit);
        
        $this->service->updateUnit($unit, $request->validated());
        
        return redirect()->route('units.show', $unit)
            ->with('success', 'Unit updated successfully');
    }

    public function destroy(Unit $unit)
    {
        $this->authorize('units.delete', $unit);
        
        $this->service->deleteUnit($unit);
        
        return redirect()->route('units.index')
            ->with('success', 'Unit deleted successfully');
    }

    public function changeStatus(Request $request, Unit $unit)
    {
        $this->authorize('units.update', $unit);
        
        $request->validate(['status' => 'required|string']);
        
        $this->service->changeStatus($unit, $request->input('status'));
        
        return response()->json(['message' => 'Status updated successfully']);
    }

    public function bulkStatus(Request $request)
    {
        $this->authorize('units.bulk-update');
        
        $request->validate([
            'ids' => 'required|array',
            'status' => 'required|string',
        ]);
        
        $this->service->bulkStatusChange(
            $request->input('ids'),
            $request->input('status')
        );
        
        return response()->json(['message' => 'Status updated for selected units']);
    }
}
