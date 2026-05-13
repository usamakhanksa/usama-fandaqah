<?php

namespace App\Services;

use App\Team;
use App\Models\NightAuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NightAuditService
{
    protected $preflight;
    protected $noShow;
    protected $freeze;
    protected $snapshot;

    public function __construct(
        NightAuditPreflightService $preflight,
        NoShowProcessingService $noShow,
        TransactionFreezeService $freeze,
        NightAuditSnapshotService $snapshot
    ) {
        $this->preflight = $preflight;
        $this->noShow = $noShow;
        $this->freeze = $freeze;
        $this->snapshot = $snapshot;
    }

    public function run(Team $team, $triggeredBy = 'auto', $userId = null)
    {
        $businessDate = $team->business_date;
        
        // 1. Initialize Log
        $auditLog = NightAuditLog::create([
            'team_id' => $team->id,
            'business_date' => $businessDate,
            'status' => 'running',
            'triggered_by' => $triggeredBy,
            'triggered_by_user_id' => $userId,
            'started_at' => now(),
            'steps_completed' => []
        ]);

        try {
            // STEP 1: Pre-flight
            $this->logStep($auditLog, 'pre_flight');
            $preflightResult = $this->preflight->check($team);
            if (!$preflightResult['can_run']) {
                throw new \Exception("Pre-flight checks failed: " . implode(', ', $preflightResult['errors']));
            }

            // STEP 2: No-show processing
            $this->logStep($auditLog, 'no_shows');
            $noShowStats = $this->noShow->process($team, $auditLog);
            $auditLog->update([
                'noshows_flagged' => $noShowStats['flagged'],
                'noshow_charges_posted' => $noShowStats['charged']
            ]);

            // STEP 3: Transaction Freeze
            $this->logStep($auditLog, 'freeze_transactions');
            $frozenCount = $this->freeze->freeze($team);
            $auditLog->update(['transactions_frozen' => $frozenCount]);

            // STEP 4: Revenue & Occupancy Snapshot
            $this->logStep($auditLog, 'snapshot');
            $snapshot = $this->snapshot->createSnapshot($team);
            $auditLog->update(['occupancy_snapshot_id' => $snapshot->id]);

            // STEP 5: Advance Business Date
            $this->logStep($auditLog, 'advance_date');
            $nextDate = \Carbon\Carbon::parse($businessDate)->addDay()->toDateString();
            $team->update([
                'business_date' => $nextDate,
                'last_night_audit_at' => now(),
                'last_night_audit_by' => $userId
            ]);

            // Finalize
            $auditLog->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

            return $auditLog;

        } catch (\Exception $e) {
            $auditLog->update([
                'status' => 'failed',
                'notes' => $e->getMessage(),
                'steps_failed' => [$this->getCurrentStep($auditLog)],
                'completed_at' => now()
            ]);
            Log::error("Night Audit Failed for Team {$team->id}: " . $e->getMessage());
            throw $e;
        }
    }

    protected function logStep(NightAuditLog $log, $step)
    {
        $steps = $log->steps_completed ?: [];
        $steps[] = $step;
        $log->update(['steps_completed' => $steps]);
    }

    protected function getCurrentStep(NightAuditLog $log)
    {
        $steps = $log->steps_completed ?: [];
        return end($steps) ?: 'init';
    }
}
