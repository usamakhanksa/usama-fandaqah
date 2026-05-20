<?php

namespace App\Services;

use App\Models\LongStayContract;
use App\Models\Building;
use App\Models\UtilityMeter;
use App\Models\UtilityReading;
use App\Models\UnitInventory;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LongStayService
{
    /**
     * Get all buildings for the team.
     */
    public function getBuildings($team)
    {
        return Building::where('team_id', $team->id)->get();
    }

    /**
     * Create a new long-stay contract.
     */
    public function createContract($team, array $data)
    {
        return DB::transaction(function () use ($team, $data) {
            $contract = LongStayContract::create(array_merge($data, [
                'team_id' => $team->id,
                'status' => 'active'
            ]));

            // Update unit status if necessary
            $unit = Unit::find($data['unit_id']);
            if ($unit) {
                // Assuming unit_status_id for 'Occupied' or 'Long Stay'
                // For now, just update the status field if it exists
                $unit->update(['status' => 'occupied']);
            }

            return $contract;
        });
    }

    /**
     * Terminate a contract.
     */
    public function terminateContract(LongStayContract $contract)
    {
        return DB::transaction(function () use ($contract) {
            $contract->update(['status' => 'terminated']);
            
            $unit = $contract->unit;
            if ($unit) {
                $unit->update(['status' => 'available']);
            }

            return $contract;
        });
    }

    /**
     * Add a utility reading.
     */
    public function addUtilityReading(array $data)
    {
        return UtilityReading::create($data);
    }

    /**
     * Get unit inventory.
     */
    public function getUnitInventory(Unit $unit)
    {
        return UnitInventory::where('unit_id', $unit->id)->get();
    }

    /**
     * Update unit inventory item.
     */
    public function updateInventoryItem(UnitInventory $item, array $data)
    {
        return $item->update($data);
    }
}
