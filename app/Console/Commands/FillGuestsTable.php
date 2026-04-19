<?php

namespace App\Console\Commands;

use App\Guest;
use App\ReservationGuests;
use Illuminate\Console\Command;

class FillGuestsTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fill:guests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'copy reservation guests table data into new guests table';

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
        $legacy_guests = ReservationGuests::with('reservation')->get();

        foreach ($legacy_guests as $guest_obj) {

            if($guest_obj->reservation){
                Guest::create([
                    'customer_id' => $guest_obj->reservation->customer_id,
                    'shomoos_id' => $guest_obj->shomoos_id,
                    'name' => $guest_obj->name,
                    'gender' => $guest_obj->gender,
                    'relation_type' => $guest_obj->relation_type,
                    'id_number' => $guest_obj->id_number,
                    'id_type' => $guest_obj->id_type,
                    'customer_type' => $guest_obj->customer_type,
                    'country_id' => $guest_obj->country_id,
                    'id_serial_number' => $guest_obj->id_serial_number,
                    'visa_number' => $guest_obj->visa_number,
                    'created_at' => $guest_obj->created_at,
                    'updated_at' => $guest_obj->updated_at
                ]);
            }
        }

    }
}
