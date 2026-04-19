<?php

namespace App\Console\Commands;

use App\ReservationGuests;
use Illuminate\Console\Command;

class AddCustomerIdToReservationGuestsTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rgtble:add-customer-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will fetch customer id through the reservation id then update it in reservation guests table';

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
        $guests = ReservationGuests::with('reservation')->get();
        foreach($guests as $guest){
            if($guest->reservation){
                $guest->customer_id = $guest->reservation->customer_id;
                $guest->save();
            }
        }

    }
}
