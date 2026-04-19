<?php

namespace App\Console\Commands;

use App\Occupied;
use Illuminate\Console\Command;

class FixFandaqahOccupied extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:fandaqah-occupied {target_date} {team_ids*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix Percentage for fandaqah occupied';

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
        $team_ids = explode(',' , $this->argument('team_ids')[0]);
        $target_date = $this->argument('target_date');
        $occupieds = Occupied::whereIn('team_id' ,  $team_ids)->where('created_at' , 'LIKE' , "%$target_date%")->get();
        $occupieds_bar = $this->output->createProgressBar(count($occupieds));

        foreach($occupieds as $occupied){
            $occupied->percentage = $occupied->units_count ?   number_format( ( ( ($occupied->occupied + $occupied->booked) / $occupied->units_count)  * 100 ) , 2)  : 0 ;
            $occupied->save();
            $occupieds_bar->advance();
        }

        $occupieds_bar->finish();
    }
}
