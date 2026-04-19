<?php

namespace App\Console\Commands;

use App\ServiceLog;
use Illuminate\Console\Command;

class ServiceLogsJsonDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'json:services {team_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command will copy the created at value of service log and insert it directly to meta json column with key as date and value as created at';

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
        $team_id = $this->argument('team_id');
        if($team_id){
            
        }else{
            $services = ServiceLog::whereNull('deleted_at')
            ->where('created_at','>=','2024-01-01')
            ->get();

            if(count($services)){
                $this->info('Total Number Of Services is : ' . count($services));
                foreach ($services as $service) {
                 
                    if(!isset($service->meta['date'])){
                        $this->comment('Updating service : ' . $service->id);
                        $meta = collect($service->meta)->jsonserialize();
                        $meta['date'] = $service->created_at->format('Y-m-d H:i');
                        $meta['run_by'] = 'command';
                        $service->meta = $meta;
                        $service->save();
                    }
                }
            }
            
        }
    }
}
