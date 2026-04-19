<?php

namespace App\Console\Commands;

use App\Highlight;
use App\Team;
use App\Term;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AddServicesTermToCurrentTeams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services-term:current-teams';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will add non deletable term called pos service to all teams';

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

        $teams = Team::all()->pluck('id') ;
        if($teams) {
            $services_term =  [
                'title' => [
                    'en' => 'Services ',
                    'ar' => 'خدمات'
                ],
                'type' => 2,
                'deleteable' => 0
            ];
            foreach ($teams as $id) {
                $term = new Term;
                $term->setTranslations('name',$services_term['title']);
                $term->type = $services_term['type'];
                $term->deleteable = isset($services_term['deleteable']) ? $services_term['deleteable']  : 1 ;
                $term->team_id = $id;
                $term->save();
            }
        }



    }
}
