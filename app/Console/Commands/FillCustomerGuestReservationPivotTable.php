<?php

namespace App\Console\Commands;

use App\CustomerGuestReservation;
use App\ReservationGuests;
use Illuminate\Console\Command;

class FillCustomerGuestReservationPivotTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fill:customer-guest-reservation-pivot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill The intermediate table with required foreign keys';

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
        $legacy_guests = ReservationGuests::with(['reservation' , 'reservation.customer'])->get();

        foreach ($legacy_guests as $guest_obj) {
            if($guest_obj->reservation){
                CustomerGuestReservation::create([
                    'customer_id' => $guest_obj->reservation->customer->id,
                    'guest_id' => $guest_obj->id,
                    'reservation_id' => $guest_obj->reservation->id
                ]);
            }
        }
    }
}
