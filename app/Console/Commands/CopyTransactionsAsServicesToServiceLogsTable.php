<?php

namespace App\Console\Commands;

use App\Team;
use App\ServiceLog;
use App\TeamCounter;
use App\Transaction;
use Illuminate\Console\Command;
use App\Jobs\CopyTransactionsAsServicesJob;

class CopyTransactionsAsServicesToServiceLogsTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copy:transactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will copy all transactions that were created under the name of service [deposit or withdraw] to store them in service logs table';

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
     * @return int
     */
    public function handle()
    {

 
        Team::with('users')->whereNull('deleted_at')->chunk(20, function ($teams) {
            foreach ($teams as $team) {
                
                $last_service_number = 0;
                $current_team_id = $team->id;
                $employees_ids = $team->employees->pluck('id');

                $transactions =  Transaction::whereIn('created_by' , $employees_ids)
                                            ->where(function($q){
                                                $q->whereJsonContains('meta->category' , 'service')->orWhereJsonContains('meta->category' , 'service-deposit');
                                            })
                                            ->whereNull('deleted_at')
                                            ->get();
                                        
                    foreach ($transactions as $key => $transaction) {
                        
                        // \dispatch(new TransactionServiceJob($this->team->id , $transaction , $key)) ;
                        $serviceLog = new ServiceLog;
                        $serviceLog->team_id         = $team->id;
                        $serviceLog->user_id         = $transaction->created_by ? $transaction->created_by : $this->team->owner->id ;
                        $serviceLog->transaction_id  = $transaction->id;
                        $serviceLog->type            = $transaction->type;
                        $serviceLog->number          = $key + 1 ; 
                        $serviceLog->amount          = $transaction->amount;
                        $serviceLog->decimals        = $transaction->wallet->decimal_places;
                        $serviceLog->meta            = $transaction->meta; 
                        $serviceLog->created_at      = $transaction->created_at; 
                        $serviceLog->updated_at      = $transaction->updated_at; 
                        $serviceLog->save();
                        // $last_service_number  = $serviceLog->number; 
                    }  
                    $counter = $team->counter; 
                    if(!$counter){
                        $counter = TeamCounter::create(); 
                    }
                    $counter->service_number = 0;
                    // $counter->last_service_number = $last_service_number;
                    $counter->last_service_number = count($transactions);
                    $counter->save();                   
         
                // CopyTransactionsAsServicesJob::dispatch($team);
            }
        });


        // $teams = Team::with('users')->whereNull('deleted_at')->get();
        // $bar = $this->output->createProgressBar(count($teams));
        // $bar->start();
        // if($teams){
        //     foreach ($teams as $team) {
        //         CopyTransactionsAsServicesJob::dispatch($team);
        //         $bar->advance();
        //         $this->info("current team is {$team->id}");
        //         sleep(1);
        //     }
        //     $bar->finish();
        // }else{
        //     $bar->finish();
        // }

    }
}
