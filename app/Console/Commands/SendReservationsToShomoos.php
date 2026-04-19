<?php

namespace App\Console\Commands;

use App\Reservation;
use App\Integration\SHMS;
use App\Handlers\Settings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Events\ShomosReservationUpdated;

class SendReservationsToShomoos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shms:send-reservations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reservations to shomoos , the failure ones';

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
        $team_id = $this->ask('What is your team id?');
        if(!$team_id){
            $this->error('Team id is required to go ...');
            return;
        }

        if ($this->confirm('Do you wish to continue?')) {
            $credentials = Settings::checkIntegration('SHMS', $team_id);
            $reservations = Reservation::where('team_id',$team_id)
                            ->whereNotNull('checked_in')
                            ->whereNull('checked_out')
                            ->whereStatus('confirmed')
                            ->where(function($query){
                                $query->whereNull('shomoos_id')
                                ->orWhere('shomoos_id', 0);
                            })
                            ->with('customer','unit')
                            ->get();
            
            $this->info("Total Reservations Found is : " . count($reservations));
            if(count($reservations)){
                if ($this->confirm('Do you still want to proceed ?')){
                    foreach ($reservations as $reservation) {
                        DB::statement("UPDATE IGNORE reservations SET shomoos_id = NULL where id = {$reservation->id} LIMIT 1");
                        event(new ShomosReservationUpdated($reservation));
                    }

                    $this->info('Iteration came to an end , thanks  ...');
                }
            }                

        }else{
            $this->info('Thanks and Godbye ...');
        }
      
    }
}
