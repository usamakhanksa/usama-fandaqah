<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NightAuditService;
use App\Team;

class NightAuditAutoRun extends Command
{
    protected $signature = 'nightaudit:auto-run';
    protected $description = 'Automatically run the Night Audit for teams that have it enabled and scheduled.';

    public function handle(NightAuditService $nightAudit)
    {
        $this->info('Starting Night Audit Auto-Run Process...');
        
        $currentTime = now()->format('H:i:00');
        
        $teams = Team::where('night_audit_auto_enabled', true)
            ->where('night_audit_auto_run_time', '<=', $currentTime)
            ->where(function($query) {
                $query->whereNull('last_night_audit_at')
                      ->orWhereDate('last_night_audit_at', '<', now()->toDateString());
            })
            ->get();

        foreach ($teams as $team) {
            $this->info("Processing Team: {$team->name} (ID: {$team->id})");
            try {
                $nightAudit->run($team, 'auto');
                $this->info("Night Audit successfully completed for Team: {$team->name}");
            } catch (\Exception $e) {
                $this->error("Night Audit failed for Team: {$team->name}. Error: " . $e->getMessage());
            }
        }

        $this->info('Auto-Run Process Finished.');
    }
}
