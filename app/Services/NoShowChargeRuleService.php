<?php

namespace App\Services;

use App\Models\NoShowChargeRule;
use App\Models\NightAuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NoShowChargeRuleService
{
    /**
     * Check for overlapping active rules for the same team and applies_to.
     */
    public function checkOverlaps($teamId, $startDate, $endDate, $appliesTo, $excludeId = null)
    {
        $query = NoShowChargeRule::where('team_id', $teamId)
            ->where('is_active', true)
            ->where('applies_to', $appliesTo)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($q2) use ($startDate, $endDate) {
                      $q2->where('start_date', '<=', $startDate)
                         ->where('end_date', '>=', $endDate);
                  });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Check if a night audit has already run for any part of the rule's date range.
     */
    public function hasNightAuditRun($teamId, $startDate, $endDate)
    {
        return NightAuditLog::where('team_id', $teamId)
            ->where('status', 'completed')
            ->whereBetween('business_date', [$startDate, $endDate])
            ->exists();
    }

    /**
     * Get the applicable rule for a reservation on a specific date.
     */
    public function getApplicableRule($teamId, $date, $rentType = 'daily')
    {
        // rentType 'daily' corresponds to 'daily', 'monthly' to 'monthly', etc.
        // If reservation matches a specific 'applies_to' or 'all'.
        
        return NoShowChargeRule::where('team_id', $teamId)
            ->where('is_active', true)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->where(function ($q) use ($rentType) {
                $q->where('applies_to', 'all')
                  ->orWhere('applies_to', $rentType);
            })
            ->orderByRaw("CASE WHEN applies_to = ? THEN 0 ELSE 1 END", [$rentType]) // Prefer specific over 'all'
            ->first();
    }
}
