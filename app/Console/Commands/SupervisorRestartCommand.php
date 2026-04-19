<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Artisan;

class SupervisorRestartCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'supervisor:restart';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'run peacfully the command queue:restart to restart supervisor if it is stuck';

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
 
            $directory = '/home/forge/app.fandaqah.com';
            $process = new Process('cd ' . $directory . " && php artisan queue:restart");
            $process->setTimeout(3600);
            $process->setPty(true);
            $process->run(function ($type, $buffer) {
                echo $buffer;
            });
          
    }
}
