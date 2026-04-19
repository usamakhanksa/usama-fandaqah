<?php

namespace App\Console\Commands;

use App\ReservationInvoice;
use Illuminate\Console\Command;

class InvoiceFixCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fx {team_id} {--ids=} {--numbers=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to handle the wrong massive update happens to reservation invoices table ';

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
        $invoices_numbers = explode(',' , $this->option('numbers'));

        $invoices = ReservationInvoice::where('team_id' , $team_id)->whereIn('id' , $invoices_ids)->withTrashed()->get();

        foreach ($invoices as $key => $invoice) {
            $invoice->number = $invoices_numbers[$key];
            $invoice->save();
        }

        return $this->info('Every thing went successfully -__-');
    }
}
