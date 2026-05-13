<?php

namespace App\Services\NightAudit;

use App\Models\Team;
use App\Models\NightAuditLog;
use App\Services\NightAuditService as EngineNightAuditService;
use Carbon\Carbon;

class NightAuditService
{
    public function __construct(
        protected EngineNightAuditService $engine
    ) {}

    /**
     * Data needed by Inertia NightAudit/Index and related dashboard cards.
     * Keep this minimal for Phase 1.
     */
    public function getAuditData(Team $team): array
    {
        $businessDate = $team->business_date ?? now()->toDateString();

        $lastLog = NightAuditLog::query()
            ->where('team_id', $team->id)
            ->orderByDesc('id')
            ->first();

        // If you have a scheduler flag later, you can extend this. For Phase 1 keep safe:
        $canRun = !empty($businessDate);

        return [
            'business_date' => $businessDate,
            'last_run' => $lastLog ? $this->mapLastRun($lastLog) : null,
            'pending_tasks' => [], // Phase 1: leave empty; engine preflight is covered via API
            'can_run' => $canRun,

            // These are helpful for existing Vue logic if it expects them:
            'pending_runs' => $lastLog?->status === 'running' ? 1 : 0,
            'needs_attention' => $lastLog?->status === 'failed' ? ($lastLog->notes ? [$lastLog->notes] : []) : [],
            'next_scheduled_run' => null,
            'auto_enabled' => false,
        ];
    }

    public function runAudit(Team $team): array
    {
        $log = $this->engine->run($team, 'manual', null);

        return [
            'success' => true,
            'log' => $log,
        ];
    }

    public function rerunAudit($runId): array
    {
        $oldLog = NightAuditLog::query()->findOrFail($runId);

        // For Phase 1, controller auth/permissions exist; just rerun and return.
        // We don't have userId here, so pass null (engine/rerun service may require it later).
        // If rerun needs userId, controller should be updated in Phase 2.
        $newLog = $this->engine->rerun($oldLog, null, null);

        return [
            'success' => true,
            'log' => $newLog,
        ];
    }

    /**
     * History list for the Inertia table.
     * Phase 1 keeps it basic but non-empty.
     */
    public function getHistory(Team $team): array
    {
        $history = NightAuditLog::query()
            ->where('team_id', $team->id)
            ->orderByDesc('business_date')
            ->orderByDesc('run_number')
            ->limit(30)
            ->get();

        return $history->map(function (NightAuditLog $log) {
            return [
                'id' => $log->id,
                'business_date' => $log->business_date,
                'run_number' => $log->run_number,
                'status' => $log->status,
                'triggered_by' => $log->triggered_by,
                'noshows_flagged' => $log->noshows_flagged ?? 0,
                'transactions_frozen' => $log->transactions_frozen ?? 0,
                'started_at' => $log->started_at,
            ];
        })->values()->all();
    }

    public function getRunDetails($runId): array
    {
        $log = NightAuditLog::query()->findOrFail($runId);

        return [
            'run_id' => $log->id,
            'team_id' => $log->team_id,
            'business_date' => $log->business_date,
            'run_number' => $log->run_number,
            'status' => $log->status,
            'triggered_by' => $log->triggered_by,
            'triggered_by_user_id' => $log->triggered_by_user_id,
            'started_at' => $log->started_at,
            'completed_at' => $log->completed_at,
            'steps_completed' => $log->steps_completed ?? [],
            'notes' => $log->notes,
        ];
    }

    private function mapLastRun(NightAuditLog $log): array
    {
        return [
            'id' => $log->id,
            'run_number' => $log->run_number,
            'status' => $log->status,
            'started_at' => $log->started_at ? Carbon::parse($log->started_at)->toDateTimeString() : null,
            'completed_at' => $log->completed_at ? Carbon::parse($log->completed_at)->toDateTimeString() : null,
            'business_date' => $log->business_date,
        ];
    }
}
