<?php

namespace App\Console\Commands;

use App\UnitCategory;
use App\WebsiteSetting;
use Illuminate\Console\Command;
use Vinkla\Hashids\Facades\Hashids;

class FixAllFeaturedUnitCategoriesByAddingOptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'featured:categories-fix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'this command will fetch all featured unit categories from website settings table and add extra options to it';

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
        $website_settings_for_all_teams = WebsiteSetting::whereNotNull('featured_unit_categories')->get();
        foreach ($website_settings_for_all_teams as $setting) {
            $unitCategoriesData = [];
            foreach ($setting->featured_unit_categories as $featured_category) {
                $original_category = UnitCategory::with('units','team')->where('team_id',$setting->team_id)->where('id',$featured_category['id'])->first();
                $unitCategoriesData [] = [
                    'id' => $original_category->id,
                    'hash'  =>  Hashids::encode($original_category->id),
                    'team_id' => $original_category->team_id,
                    'team_name' => $original_category->team->name,
                    'name' => $original_category->attributesToArray()['name'],
                    'main_image' => is_null($original_category->getFirstMediaUrl('main')) || empty($original_category->getFirstMediaUrl('main')) ? asset('images/no-hotel-placeholder.png') : $original_category->getFirstMediaUrl('main'),
                    'daily_prices' => $original_category->dailyPrices(),
                    'show_in_website' => $original_category->show_in_website,
                    'units_count' => $original_category->units()->count()
                ];
            }
            $setting->featured_unit_categories = $unitCategoriesData;
            $setting->save();
        }
    }
}
