<?php

namespace App\Console\Commands;

use App\Country;
use App\Handlers\Settings;
use App\Jobs\SCTH\ExpenseBooking;
use App\Jobs\SHMS\CheckIn;
use App\Reservation;
use App\Transaction;
use App\Video;
use Faker\Factory as Faker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


class TestIntegration extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:integration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'test integration';

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
        $reservation = Reservation::find(2);

        $credentials = Settings::checkIntegration('SHMS', $reservation->team_id);
        if ($credentials) {
            CheckIn::dispatch($reservation, $credentials);
        }

    }
}
