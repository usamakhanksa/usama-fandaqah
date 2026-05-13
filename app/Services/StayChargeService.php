<?php

namespace App\Services;

use App\Models\StayChargeConfig;
use App\Models\StayChargeOverride;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class StayChargeService
{
    public function checkOverlaps($teamId, $chargeType, $fromHour, $toHour, $appliesTo, $excludeId = null)
    {
        $query = StayChargeConfig::where('team_id', $teamId)
            ->where('charge_type', $chargeType)
            ->where('applies_to', $appliesTo)
            ->where('is_active', true)
            ->where(function ($q) use ($fromHour, $toHour) {
                $q->where(function($qq) use ($fromHour, $toHour) {
                    $qq->where('tier_from_hour', '<', $toHour)
                       ->where('tier_to_hour', '>', $fromHour);
                });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function calculateCharge(Reservation $reservation, $actualTime, $type)
    {
        $time = Carbon::parse($actualTime)->format('H:i:s');
        $unitTypeId = $reservation->unit ? $reservation->unit->unit_type_id : null;

        $config = StayChargeConfig::where('team_id', $reservation->team_id)
            ->where('charge_type', $type)
            ->where('is_active', true)
            ->where('tier_from_hour', '<=', $time)
            ->where('tier_to_hour', '>=', $time)
            ->where(function ($q) use ($unitTypeId) {
                $q->where('applies_to', 'all');
                if ($unitTypeId) {
                    $q->orWhere('applies_to', 'LIKE', "%$unitTypeId%");
                }
            })
            ->orderByRaw("CASE WHEN applies_to = 'all' THEN 1 ELSE 0 END") // Prefer specific
            ->first();

        if (!$config) {
            return 0;
        }

        $amount = 0;
        // Basic rate fallback
        $totalAmount = $reservation->booking?->total_amount ?? $reservation->total_price ?? 0;
        $checkIn = Carbon::parse($reservation->check_in);
        $checkOut = Carbon::parse($reservation->check_out);
        $nights = max(1, $checkIn->diffInDays($checkOut));
        
        $nightlyRate = $totalAmount / $nights;
        $firstNightRate = $nightlyRate; // Simplifying for now as specific nightly rates aren't easily available in schema

        switch ($config->rate_type) {
            case 'fixed':
                $amount = $config->rate_amount;
                break;
            case 'percentage_first_night':
                $amount = ($config->rate_amount / 100) * $firstNightRate;
                break;
            case 'percentage_nightly_rate':
                $amount = ($config->rate_amount / 100) * $nightlyRate;
                break;
        }

        return $amount;
    }

    public function logOverride($data)
    {
        return StayChargeOverride::create([
            'team_id' => Auth::user()->current_team_id ?? $data['team_id'],
            'reservation_id' => $data['reservation_id'],
            'charge_type' => $data['charge_type'],
            'original_amount' => $data['original_amount'],
            'overridden_amount' => $data['overridden_amount'],
            'reason' => $data['reason'],
            'user_id' => Auth::id(),
        ]);
    }
}
