<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LongStayContract;
use App\Models\Building;
use App\Models\Unit;
use App\Services\LongStayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class LongStayController extends Controller
{
    protected LongStayService $service;

    public function __construct(LongStayService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of buildings.
     */
    public function buildings(Request $request)
    {
        $buildings = $this->service->getBuildings($request->user()->currentTeam);
        return Response::json($buildings);
    }

    /**
     * Store a new building.
     */
    public function storeBuilding(Request $request)
    {
        $data = $request->validate([
            'name_en' => 'required|string',
            'name_ar' => 'nullable|string',
            'address' => 'nullable|string',
            'total_floors' => 'integer|min:1',
        ]);

        $building = Building::create(array_merge($data, [
            'team_id' => $request->user()->currentTeam->id
        ]));

        return Response::json($building, 201);
    }

    /**
     * Display a listing of contracts.
     */
    public function contracts(Request $request)
    {
        $contracts = LongStayContract::with(['unit', 'customer'])
            ->where('team_id', $request->user()->currentTeam->id)
            ->get();
        return Response::json($contracts);
    }

    /**
     * Store a new contract.
     */
    public function storeContract(Request $request)
    {
        $data = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'customer_id' => 'required|exists:customers,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'billing_cycle' => 'required|in:weekly,monthly,quarterly,yearly',
            'amount' => 'required|numeric|min:0',
            'security_deposit' => 'numeric|min:0',
            'terms' => 'nullable|string',
        ]);

        $contract = $this->service->createContract($request->user()->currentTeam, $data);

        return Response::json($contract, 201);
    }

    /**
     * Terminate a contract.
     */
    public function terminateContract(LongStayContract $contract)
    {
        $this->service->terminateContract($contract);
        return Response::json(['message' => 'Contract terminated successfully']);
    }

    /**
     * Get utility meters for a unit.
     */
    public function utilityMeters(Unit $unit)
    {
        $meters = $unit->utilityMeters()->with('readings')->get();
        return Response::json($meters);
    }

    /**
     * Store a utility reading.
     */
    public function storeUtilityReading(Request $request)
    {
        $data = $request->validate([
            'meter_id' => 'required|exists:utility_meters,id',
            'reading_date' => 'required|date',
            'reading_value' => 'required|numeric',
            'image_path' => 'nullable|string',
        ]);

        $reading = $this->service->addUtilityReading(array_merge($data, [
            'created_by' => $request->user()->id
        ]));

        return Response::json($reading, 201);
    }

    /**
     * Get inventory for a unit.
     */
    public function inventory(Unit $unit)
    {
        $inventory = $this->service->getUnitInventory($unit);
        return Response::json($inventory);
    }
}
