<?php

namespace App\Console\Commands;

use App\Jobs\UpdateReservationCheckingInJob;
use Carbon\Carbon;
use App\Reservation;
use Illuminate\Console\Command;

class UpdateReservationCheckingInCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:reservation-checking-in';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This commands runs at 6 am every day to update reservations checking-in column value';

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
        UpdateReservationCheckingInJob::dispatch();
    }
}
