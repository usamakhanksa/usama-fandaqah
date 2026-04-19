<?php

namespace App\Console\Commands;

use App\TeamCounter;
use App\ReservationInvoice;
use Illuminate\Console\Command;

class FixReservationsInvoicesSerial extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:invoices {team_id}  {--ids=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'command will fix invoices serial with an implemented logic';

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
        $team_id =$this->argument('team_id');
        $invoices_ids = explode(',' , $this->option('ids'));
        $invoices = ReservationInvoice::where('team_id' , $team_id)->whereNotIn('id' , $invoices_ids)->withTrashed()->get();
        $excluded_invoices = ReservationInvoice::where('team_id' , $team_id)->whereIn('id' , $invoices_ids)->withTrashed()->get();
        $team_counter = TeamCounter::where('team_id' , $team_id)->first();
        $last_invoice_number = null;

        if(count($excluded_invoices)){
            foreach ($excluded_invoices as $excluded_invoice) {
                $excluded_invoice->number = -1;
                $excluded_invoice->save();
            }
        }

        $invoices_bar = $this->output->createProgressBar(count($invoices));
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
                        $last_invoice_number = $invoice->number;
                    }

                    $invoices_bar->advance();
                }
        }
        $invoices_bar->finish();

        $all_counter = count($excluded_invoices) + count($invoices);
        $all_invoices_bar = $this->output->createProgressBar($all_counter);
        if(count($excluded_invoices)){
            $ex_iteration = $last_invoice_number + 1;
            foreach ($excluded_invoices as $invoice) {
                $invoice->number = $ex_iteration;
                $invoice->save();
                $ex_iteration++;
                if($ex_iteration > $all_counter) {
                    $team_counter->last_invoice_number = $invoice->number;
                    $team_counter->save();
                }
                $all_invoices_bar->advance();
            }

            $all_invoices_bar->finish();
        }

        return $this->info('Every thing went successfully -__-');
    }
}
