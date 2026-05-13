<?php

namespace App\Services;

use App\Team;
use App\Models\NightAuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AuditEnforcementService
{
    /**
     * Check if a specific date is closed (audited) for a team.
     */
    public function isDateClosed($teamId, $date)
    {
        $date = Carbon::parse($date)->format('Y-m-d');
        
        return NightAuditLog::where('team_id', $teamId)
            ->where('business_date', $date)
            ->where('status', 'completed')
            ->exists();
    }

    /**
     * Check if a date is "backdated" relative to the team's current business date.
     */
    public function isBackdated($teamId, $date)
    {
        $team = Team::find($teamId);
        if (!$team) return false;
        
        $targetDate = Carbon::parse($date)->startOfDay();
        $currentBusinessDate = Carbon::parse($team->business_date)->startOfDay();
        
        return $targetDate->lt($currentBusinessDate);
    }

    /**
     * Check if the current user can perform backdated operations.
     */
    public function canBackdate()
    {
        return Auth::check() && Auth::user()->hasPermissionTo('finance.backdate');
    }

    /**
     * Throw an exception if an action is blocked due to closed date or freeze.
     */
    public function enforce(bool $condition, string $message = 'Action blocked: This record is frozen or belongs to a closed business date.')
    {
        if ($condition) {
            abort(403, $message);
        }
    }
}
