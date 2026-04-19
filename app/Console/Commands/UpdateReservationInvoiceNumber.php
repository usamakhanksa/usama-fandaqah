<?php

namespace App\Console\Commands;

use App\Reservation;
use App\Team;
use App\TeamCounter;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class UpdateReservationInvoiceNumber extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:reservation-invoice-number';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Reservation Invoice Number';

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
        $teams = Team::all();
        /** @var Team $team */
        foreach ($teams as $team) {
            $counter = TeamCounter::withoutGlobalScope('team_id')
                ->whereTeamId($team->id)
                ->get()
                ->first()
            ;
            if ($counter) {
                /** @var Collection $reservations */
                $reservations = Reservation::withoutGlobalScope('team_id')
                    ->whereTeamId($team->id)
                    ->get()
                ;
                /** @var Reservation $reservation */
                foreach ($reservations as $reservation) {
                    $counter->invoice_number++;
                    $reservation->invoice_number = $counter->invoice_number;
                    $reservation->save();
                    $counter->save();
                }
                $counter->last_invoice_number = $counter->invoice_number;
                $counter->save();
            }
        }
        $this->info('Thanks and GodBye');
    }
}
