<?php

namespace App\Jobs;

use App\Team;
use App\UnitCategory;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RefreshFeaturedCategoriesOnCustomerWebsite implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;

    public $unit_category;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(UnitCategory $unit_category)
    {
        $this->unit_category = $unit_category;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $team = Team::find($this->unit_category->team_id);
        $incoming_unit_category_id = $this->unit_category->id;
        $featured_unit_categories = $team->websiteSetting->featured_unit_categories ? $team->websiteSetting->featured_unit_categories : [] ;

        $new_featured_categories = [];
        if(count($featured_unit_categories)){
            $featured_ids = collect($featured_unit_categories)->pluck('id')->toArray();

                if(in_array($incoming_unit_category_id , $featured_ids) && !$this->unit_category->show_in_website){
                    foreach ($featured_unit_categories as $featured) {
                            if($featured['id'] != $this->unit_category->id){
                                $new_featured_categories [] = $featured;
                            }
                    }

                    DB::table('website_settings')->where('team_id' , $team->id)
                    ->update(['featured_unit_categories' => json_encode($new_featured_categories)]);
                }

        }

    }
}
