<?php

namespace App\Console\Commands;

use App\Models\ReportSchedule;
use Illuminate\Console\Command;
use Carbon\Carbon;

class RunScheduledReports extends Command
{
    protected $signature = 'reports:run-scheduled';
    protected $description = 'Run scheduled reports and send via email';

    public function handle()
    {
        $now = Carbon::now();
        $schedules = ReportSchedule::where('is_active', true)
            ->where('next_run_at', '<=', $now)
            ->with('customReport')
            ->get();

        $this->info("Found {$schedules->count()} schedules to run.");

        foreach ($schedules as $schedule) {
            try {
                $controller = new \App\Http\Controllers\Report\ReportScheduleController(
                    new \App\Services\Reports\CustomReportService()
                );
                
                $controller->runScheduledReport($schedule);
                
                $this->info("Executed schedule: {$schedule->name}");
            } catch (\Exception $e) {
                $this->error("Failed to run schedule {$schedule->name}: {$e->getMessage()}");
            }
        }

        $this->info('Scheduled reports processing complete.');
    }
}