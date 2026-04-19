<?php

namespace App\Console\Commands;

use App\Unit;
use App\UnitCategory;
use Illuminate\Console\Command;

class ExchangeTeamData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ex:data {old_team} {new_team}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'exhange team data like units , units categories';

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
        $old_team = $this->arguments()['old_team'] ;
        $new_team = $this->arguments()['new_team'];

        $unit_ctaegories = UnitCategory::with('units')->where('team_id' , $old_team)->get();

        foreach ($unit_ctaegories as $category) {

            $new_category = new UnitCategory();
            $new_category->team_id = $new_team;
            $new_category->name = $category->name;
            $new_category->sunday_day_price = $category->sunday_day_price;
            $new_category->monday_day_price = $category->monday_day_price;
            $new_category->tuesday_day_price = $category->tuesday_day_price;
            $new_category->wednesday_day_price = $category->wednesday_day_price;
            $new_category->thursday_day_price = $category->thursday_day_price;
            $new_category->friday_day_price = $category->friday_day_price;
            $new_category->saturday_day_price = $category->saturday_day_price;
            $new_category->general_features = $category->general_features;
            $new_category->special_features = $category->special_features;
            $new_category->main_image = $category->main_image;
            $new_category->description = $category->description;
            $new_category->short_description = $category->short_description;
            $new_category->youtube_link = $category->youtube_link;
            $new_category->status = $category->status;
            $new_category->order = $category->order;
            $new_category->type_id = $category->type_id;
            $new_category->month_price = $category->month_price;
            $new_category->hour_price = $category->hour_price;
            $new_category->min_sunday_day_price = $category->min_sunday_day_price;
            $new_category->min_monday_day_price = $category->min_monday_day_price;
            $new_category->min_tuesday_day_price = $category->min_tuesday_day_price;
            $new_category->min_wednesday_day_price = $category->min_wednesday_day_price;
            $new_category->min_thursday_day_price = $category->min_thursday_day_price;
            $new_category->min_friday_day_price = $category->min_friday_day_price;
            $new_category->min_saturday_day_price = $category->min_saturday_day_price;
            $new_category->save();

            foreach ($category->units as $unit) {
                $new_unit = new Unit();
                $new_unit->team_id = $new_team;
                $new_unit->unit_category_id = $new_category->id;
                $new_unit->unit_number = $unit->unit_number;
                $new_unit->name = $unit->name;
                $new_unit->status = $unit->status;
                $new_unit->sunday_day_price = $unit->sunday_day_price;
                $new_unit->monday_day_price = $unit->monday_day_price;
                $new_unit->tuesday_day_price = $unit->tuesday_day_price;
                $new_unit->wednesday_day_price = $unit->wednesday_day_price;
                $new_unit->thursday_day_price = $unit->thursday_day_price;
                $new_unit->friday_day_price = $unit->friday_day_price;
                $new_unit->saturday_day_price = $unit->saturday_day_price;
                $new_unit->general_features = $unit->general_features;
                $new_unit->special_features = $unit->special_features;
                $new_unit->unit_options = $unit->unit_options;
                $new_unit->main_image = $unit->main_image;
                $new_unit->description = $unit->description;
                $new_unit->short_description = $unit->short_description;
                $new_unit->enabled = $unit->short_description;
                $new_unit->youtube_link = $unit->youtube_link;
                $new_unit->month_price = $unit->month_price;
                $new_unit->min_sunday_day_price = $unit->min_sunday_day_price;
                $new_unit->min_monday_day_price = $unit->min_monday_day_price;
                $new_unit->min_tuesday_day_price = $unit->min_tuesday_day_price;
                $new_unit->min_wednesday_day_price = $unit->min_wednesday_day_price;
                $new_unit->min_thursday_day_price = $unit->min_thursday_day_price;
                $new_unit->min_friday_day_price = $unit->min_friday_day_price;
                $new_unit->min_saturday_day_price = $unit->min_saturday_day_price;
                $new_unit->shomoos_id = $unit->shomoos_id;
                $new_unit->save();

            }

        }


        return $this->info("Everything went ok");
    }
}
