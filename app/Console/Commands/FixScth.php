<?php

namespace App\Console\Commands;

use App\Country;
use App\Events\ReservationUpdated;
use App\Handlers\Settings;
use App\Jobs\SCTH\ExpenseBooking;
use App\Jobs\SHMS\CheckIn;
use App\Reservation;
use App\Transaction;
use App\Video;
use Faker\Factory as Faker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


class FixScth extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:scth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fix scth';

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
        $scth_ids = [
            '11FC71C2-BECA-4B88-AF56-6C3C2C0693ED',
            '97110357-C149-493C-97C7-9A7A9B49F7C6'
        ];

        foreach ($scth_ids as $scth_id) {
            $reservation = Reservation::where('scth_reference', $scth_id)->first();
            $this->info("fixing {$scth_id}");
            if(isset($reservation)){
                event(new ReservationUpdated($reservation));
            }
        }
    }
}
