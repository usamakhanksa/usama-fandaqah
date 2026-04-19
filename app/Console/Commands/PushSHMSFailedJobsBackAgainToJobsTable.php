<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Artisan;

class PushSHMSFailedJobsBackAgainToJobsTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'push:shms-failed-jobs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'push back all failed shms jobs back to jobs table to process';

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
        $failed_jobs_ids = DB::table('failed_jobs')
            ->select('id')
            ->where('queue','shms')
            ->orderBy('id')
            ->get()
            ->pluck('id')
            ->toArray();

        if(count($failed_jobs_ids)){
            $directory = '/home/forge/app.fandaqah.com';
            foreach ($failed_jobs_ids as $id) {
                $process = new Process('cd ' . $directory . " && php artisan queue:retry {$id}");
                $process->setTimeout(3600);
                $process->setPty(true);
                $process->run(function ($type, $buffer) {
                    echo $buffer;
                });
                // Artisan::call("queue:retry $id");
            }
        }    

    }
}
