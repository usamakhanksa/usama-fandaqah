<?php

namespace App\Console\Commands;

use App\Highlight;
use App\Team;
use App\Term;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateIsDeleteableValueForInsuranceTermsForAllTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insurance-terms:update-deleteable';

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

        $insuranceTerms = Term::whereJsonContains('name->ar' , 'تامين')->where('type' , 2)->whereNull('deleted_at')->get();
        $insuranceRetrievalTerms = Term::whereJsonContains('name->ar' , 'استرجاع تامين')->where('type' , 1)->whereNull('deleted_at')->get();
        $collection = $insuranceTerms->merge($insuranceRetrievalTerms);


        if(count($collection)) {

            foreach ($collection as $term) {
                $term->deleteable = 0;
                $term->save();
            }
        }



    }
}
