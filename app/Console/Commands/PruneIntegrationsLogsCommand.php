<?php

namespace App\Console\Commands;

use App\IntegrationLog;
use Illuminate\Console\Command;

class PruneIntegrationsLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prune:integration-logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune integration logs older than x days';

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
        $prune_logs_days = config('app.prune_integration_logs_days');
        IntegrationLog::where( 'created_at', '<=', now()->subDays($prune_logs_days)->format('Y-m-d'))->delete();
    }
}
