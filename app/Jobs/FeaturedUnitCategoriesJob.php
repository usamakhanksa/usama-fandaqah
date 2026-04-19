<?php

namespace App\Jobs;

use App\UnitCategory;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class FeaturedUnitCategoriesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;

    public $data;
    public $team_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data,$team_id)
    {
        $this->data = $data;
        $this->team_id = $team_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ids = collect($this->data)->pluck('id');
        $categories = UnitCategory::with('units','team')->whereIn('id' , $ids)->get();

        $unitCategoriesData = [];

        foreach ($categories as $category) {
            $unitCategoriesData [] = [
                'id' => $category->id,
                'hash'  =>  Hashids::encode($category->id),
                'team_id' => $category->team_id,
                'team_name' => $category->team->name,
                'name' => $category->attributesToArray()['name'],
                'main_image' => is_null($category->getFirstMediaUrl('main')) || empty($category->getFirstMediaUrl('main')) ? asset('images/no-hotel-placeholder.png') : $category->getFirstMediaUrl('main'),
                'daily_prices' => $category->dailyPrices(),
                'show_in_website' => $category->show_in_website,
                'units_count' => $category->units()->count()
            ];
        }

        DB::table('website_settings')->where('team_id' , $this->team_id)
                    ->update(['featured_unit_categories' => json_encode($unitCategoriesData)]);
    }
}
