<?php

namespace App\Console\Commands;

use App\Activity;
use App\IntegrationLog;
use Illuminate\Console\Command;

class PruneActivityLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prune:activity-logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune activity logs older than x days';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $prune_logs_days = config('app.prune_activity_logs_days');
        Activity::where( 'created_at', '<=', now()->subDays($prune_logs_days)->format('Y-m-d'))->delete();
    }
}
