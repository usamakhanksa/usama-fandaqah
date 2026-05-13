<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StayChargeConfig;
use App\Models\Reservation;
use App\Services\StayChargeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StayChargeConfigController extends Controller
{
    protected $service;

    public function __construct(StayChargeService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $teamId = Auth::user()->current_team_id;
        return StayChargeConfig::where('team_id', $teamId)->get();
    }

    public function store(Request $request)
    {
        $teamId = Auth::user()->current_team_id;
        $data = $request->validate([
            'charge_type' => 'required|in:early_checkin,late_checkout',
            'tier_from_hour' => 'required|string',
            'tier_to_hour' => 'required|string',
            'rate_type' => 'required|in:fixed,percentage_first_night,percentage_nightly_rate',
            'rate_amount' => 'required|numeric|min:0',
            'applies_to' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($this->service->checkOverlaps($teamId, $data['charge_type'], $data['tier_from_hour'], $data['tier_to_hour'], $data['applies_to'] ?? 'all')) {
            return response()->json(['message' => 'Time tier overlaps with an existing configuration.'], 422);
        }

        $data['team_id'] = $teamId;
        $data['created_by'] = Auth::id();
        $data['applies_to'] = $data['applies_to'] ?? 'all';

        return StayChargeConfig::create($data);
    }

    public function update(Request $request, $id)
    {
        $config = StayChargeConfig::findOrFail($id);
        $teamId = Auth::user()->current_team_id;

        $data = $request->validate([
            'charge_type' => 'required|in:early_checkin,late_checkout',
            'tier_from_hour' => 'required|string',
            'tier_to_hour' => 'required|string',
            'rate_type' => 'required|in:fixed,percentage_first_night,percentage_nightly_rate',
            'rate_amount' => 'required|numeric|min:0',
            'applies_to' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($this->service->checkOverlaps($teamId, $data['charge_type'], $data['tier_from_hour'], $data['tier_to_hour'], $data['applies_to'] ?? 'all', $id)) {
            return response()->json(['message' => 'Time tier overlaps with an existing configuration.'], 422);
        }

        $config->update($data);
        return $config;
    }

    public function destroy($id)
    {
        StayChargeConfig::findOrFail($id)->delete();
        return response()->json(['message' => 'Config deleted successfully.']);
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'time' => 'required|string',
            'type' => 'required|in:early_checkin,late_checkout',
        ]);

        $reservation = Reservation::with(['unit', 'booking'])->findOrFail($request->reservation_id);
        $amount = $this->service->calculateCharge($reservation, $request->time, $request->type);

        return response()->json(['amount' => $amount]);
    }
}
