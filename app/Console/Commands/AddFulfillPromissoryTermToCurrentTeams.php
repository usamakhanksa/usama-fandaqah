<?php

namespace App\Console\Commands;

use App\Team;
use App\Term;
use Illuminate\Console\Command;

class AddFulfillPromissoryTermToCurrentTeams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add-promissory-term:to-current-teams';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'flush non deletable promissory term to current teams';

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
        $teams = Team::whereNull('deleted_at')->get()->pluck('id') ;
        if($teams) {
            $promissory_term =  [
                'title' => [
                    'en' => 'Fulfill Promissory',
                    'ar' => 'تحصيل كمبيالة'
                ],
                'type' => 2,
                'deleteable' => 0
            ];
            foreach ($teams as $id) {
                $term = new Term;
                $term->setTranslations('name',$promissory_term['title']);
                $term->type = $promissory_term['type'];
                $term->deleteable = isset($promissory_term['deleteable']) ? $promissory_term['deleteable']  : 1 ;
                $term->team_id = $id;
                $term->save();
            }
        }
    }
}
