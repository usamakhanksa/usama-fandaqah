<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\CashierShift;
use App\Team;
use Carbon\Carbon;

class NightAuditPreflightService
{
    public function check(Team $team)
    {
        $businessDate = $team->business_date;
        if (!$businessDate) {
            return [
                'can_run' => false,
                'errors' => ['Business date not set for this team.']
            ];
        }

        $errors = [];
        $warnings = [];

        // 1. Check pending arrivals (Check-ins)
        $pendingCheckins = Reservation::where('team_id', $team->id)
            ->whereDate('check_in', '<=', $businessDate)
            ->whereIn('status', ['confirmed', 'partial'])
            ->count();

        if ($pendingCheckins > 0) {
            $errors[] = "There are $pendingCheckins pending check-ins that must be checked-in or marked as no-show.";
        }

        // 2. Check pending departures (Check-outs)
        $pendingCheckouts = Reservation::where('team_id', $team->id)
            ->whereDate('check_out', '<=', $businessDate)
            ->where('status', 'checked_in')
            ->count();

        if ($pendingCheckouts > 0) {
            $errors[] = "There are $pendingCheckouts pending check-outs that must be processed.";
        }

        // 3. Check open cashier shifts
        $openShifts = \DB::table('cashier_shifts')
            ->where('team_id', $team->id)
            ->where('status', 'open')
            ->count();

        if ($openShifts > 0) {
            $errors[] = "There are $openShifts open cashier shifts that must be closed.";
        }

        return [
            'can_run' => count($errors) === 0,
            'errors' => $errors,
            'warnings' => $warnings,
            'counts' => [
                'pending_checkins' => $pendingCheckins,
                'pending_checkouts' => $pendingCheckouts,
                'open_shifts' => $openShifts
            ]
        ];
    }
}
