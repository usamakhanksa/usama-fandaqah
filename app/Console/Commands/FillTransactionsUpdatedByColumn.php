<?php

namespace App\Console\Commands;

use App\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FillTransactionsUpdatedByColumn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fill:transactions-updated-by';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command copy current created_by value then add it to updated_by column';

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

        $transactions = DB::table('transactions')->whereNotNull('created_by')->whereNull('deleted_at')->get();
        $bar = $this->output->createProgressBar(count($transactions));


        foreach($transactions as $transaction){

            DB::table('transactions')->where('id' , $transaction->id)->update(['updated_by' => $transaction->created_by]);
            $bar->advance();
        }

        $bar->finish();
    }
}
