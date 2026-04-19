<?php

namespace App\Console\Commands;

use App\Highlight;
use App\Team;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FlushCustomers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flush:highlights';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will update all current teams Highlights table';

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

        if ($this->confirm('Are you sure to proceed with flushing Highlights date in time & date out time ?')) {

            $teams = Team::all()->pluck('id') ;
            if($teams){
                foreach ($teams as $team) {
                    $highlights = [
                        [
                            'en' => 'Special',
                            'ar' => 'مميز'
                        ],
                        [
                            'en' => 'VIP',
                            'ar' => 'خاص'
                        ],
                        [
                            'en' => 'unwanted',
                            'ar' => 'غير مرغوب فيه'
                        ],
                    ];

                    foreach ($highlights as $key => $data) {
                        $term = new Highlight;
                        $term->setTranslations('name', $data);
                        $term->team_id = $team;
                        $term->order = $key + 1;
                        $term->save();
                    }
                }

                $this->info('Congratulations , all teams updated successfully');

            }else{
                $this->error('No teams to update');
            }


        }else{
            $this->info('Thanks and GodBye -_-');
        }



    }
}
