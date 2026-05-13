<?php

namespace App\Services;

use App\Team;
use App\Models\NightAuditLog;
use App\Models\NightAuditOccupancySnapshot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NightAuditRerunService
{
    protected $snapshot;

    public function __construct(NightAuditSnapshotService $snapshot)
    {
        $this->snapshot = $snapshot;
    }

    /**
     * Rerun Night Audit for a specific historical log.
     */
    public function rerun(NightAuditLog $oldLog, $userId, $reason = null)
    {
        $team = $oldLog->team;
        $businessDate = $oldLog->business_date;
        $daysDiff = Carbon::parse($businessDate)->diffInDays(Carbon::parse($team->business_date));

        // 1. Validation & Policy
        $this->validateRerun($oldLog, $daysDiff, $reason);

        // 2. Initialize New Log Entry
        $newLog = NightAuditLog::create([
            'team_id' => $team->id,
            'business_date' => $businessDate,
            'run_number' => $oldLog->run_number + 1,
            'status' => 'running',
            'triggered_by' => 'manual',
            'triggered_by_user_id' => $userId,
            'rerun_of_log_id' => $oldLog->id,
            'notes' => $reason,
            'started_at' => now(),
            'steps_completed' => []
        ]);

        try {
            // STEP 1: Skip No-Show & Freeze (per rules)
            $this->logStep($newLog, 'skipped_noshows');
            $this->logStep($newLog, 'skipped_freeze');

            // STEP 2: Mark old snapshot as NOT final
            if ($oldLog->occupancy_snapshot_id) {
                NightAuditOccupancySnapshot::where('id', $oldLog->occupancy_snapshot_id)
                    ->update(['is_final' => false]);
            }

            // STEP 3: Recalculate Revenue & Occupancy (Create new snapshot)
            $this->logStep($newLog, 'snapshot_recalc');
            $newSnapshot = $this->snapshot->createSnapshot($team, $newLog->run_number, $businessDate);

            // STEP 4: Finalize
            $newLog->update([
                'status' => 'completed',
                'occupancy_snapshot_id' => $newSnapshot->id,
                'completed_at' => now()
            ]);

            return $newLog;

        } catch (\Exception $e) {
            $newLog->update([
                'status' => 'failed',
                'notes' => 'Rerun failed: ' . $e->getMessage(),
                'completed_at' => now()
            ]);
            Log::error("Night Audit Rerun Failed for Team {$team->id}, Date {$businessDate}: " . $e->getMessage());
            throw $e;
        }
    }

    protected function validateRerun(NightAuditLog $log, int $daysDiff, $reason)
    {
        if ($daysDiff > 30) {
            throw new \Exception("Rerunning audits older than 30 days is blocked.");
        }

        if ($daysDiff >= 8 && empty($reason)) {
            throw new \Exception("A mandatory reason is required for historical reruns (8-30 days).");
        }

        if ($log->status !== 'completed') {
            throw new \Exception("Only completed audits can be rerun.");
        }
    }

    protected function logStep(NightAuditLog $log, $step)
    {
        $steps = $log->steps_completed ?: [];
        $steps[] = $step;
        $log->update(['steps_completed' => $steps]);
    }
}
