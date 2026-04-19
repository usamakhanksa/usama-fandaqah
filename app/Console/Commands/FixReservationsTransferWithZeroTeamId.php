<?php

namespace App\Console\Commands;

use App\ReservationTransfer;
use Illuminate\Console\Command;

class FixReservationsTransferWithZeroTeamId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fandaqah:fix-reservations-transfer-zero-team-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will query reservations transfer data that has zero team id and update it to the correct one';

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
        $transfers = ReservationTransfer::with('reservation')->where('team_id', 0)->get();
        if (count($transfers)) {
            $bar = $this->output->createProgressBar(count($transfers));
            foreach ($transfers as $reservation_transfer) {
                if ($reservation_transfer->reservation) {
                    $reservation_transfer->team_id = $reservation_transfer->reservation->team_id;
                    $reservation_transfer->save();
                    $bar->advance();
                }
            }
            $bar->finish();
            $this->info("\n");
            $this->info('Fixing reservations transfers went successfully');
        } else {
            $this->info('No reservations transfers to fix');
        }
    }
}
