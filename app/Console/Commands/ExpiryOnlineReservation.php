<?php

namespace App\Console\Commands;

use App\Reservation;
use Illuminate\Console\Command;

class ExpiryOnlineReservation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expiry:online_reservation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expiry Online Reservation';

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
        $reservations = Reservation::awaitingPaymentForSureBills()->get();
        foreach ($reservations as $reservation) {
            $minutes = $reservation->team->websiteSetting->time_payment_completed ?? 10;
            $date = $reservation->created_at->addMinutes($minutes);
            if($date->isPast() ){
                $this->info("make Online Reservation id: {$reservation->id} expired!");
                $reservation->timeout();
                $reservation->timeoutBill();
            }
        }
    }
}
