<?php

namespace App\Console\Commands;

use App\Team;
use App\ReservationInvoice;
use App\TeamCounter;
use Illuminate\Console\Command;

class FixTenantInvoicesSerial extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix-tenant:serial {team_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is to fixed invoices serials in invoices report';

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
        try {
            $team_id = $this->argument('team_id');
            if(!Team::find($team_id)){
              return  $this->error('Tenant is not found');
            }

            $invoices = ReservationInvoice::withTrashed()->where('team_id' , $team_id)->get();
            $team_counter = TeamCounter::where('team_id' , $team_id)->first();
            if($invoices->count()){
                $iteration = 1;
                foreach ($invoices as $invoice) {
                    $invoice->number = $iteration;
                    $invoice->save();
                    $iteration++;
                    if($iteration > $invoices->count()){
                        // update invoices serial in settings
                        $team_counter->last_invoice_number = $invoice->number;
                        $team_counter->save();
                    }
                }
                return $this->info('Every thing went successfully -__-');
            }else{
                return $this->info('No invoices found -_-');
            }
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }
}
