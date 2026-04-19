<?php

namespace App\Console\Commands;

use App\Reservation;
use Illuminate\Console\Command;

class CreateTransactionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:transaction';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new transaction';

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
        $reservation_id = $this->ask('What is reservation id ?'); 
        $reservation = Reservation::find($reservation_id);
        $options = [
            1 => 'Deposit',
            2 => 'Withdraw',
        ];
        
        $transaction_type = array_search(
            $this->choice('Choose Transaction Type', $options),
            $options
        );


        $amount = $this->ask('What is the amount ?'); 
        $meta = [
            "category" => 'reservation',
            "statement" => 'qualifiy-balance'
        ];

        if($transaction_type == 1){
            $reservation->depositFloat($amount, $meta, true, false);
        }else{
            $reservation->forceWithdrawFloat($amount, $meta, true, false);
        }
        

    }
}
