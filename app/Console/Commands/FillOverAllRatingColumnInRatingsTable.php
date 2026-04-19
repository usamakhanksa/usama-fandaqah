<?php

namespace App\Console\Commands;

use App\Rating;
use Illuminate\Console\Command;

class FillOverAllRatingColumnInRatingsTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fill:overall-ratings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command will calculate the overal rating for a single rate and store it in overall_rating column';

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
        $ratings = Rating::whereNull('overall_rating')->get();
        if(count($ratings)){
            foreach($ratings as $rating){
                $total_questions_factor = 0; 
                $total_questions_factor += $rating->q_one ? 1 : 0;
                $total_questions_factor += $rating->q_two ? 1 : 0; 
                $total_questions_factor += $rating->q_three ? 1 : 0; 
                $total_questions_factor += $rating->q_four ? 1 : 0; 
                $total_questions_factor += $rating->q_five ? 1 : 0; 
                $total_questions_factor += $rating->q_six ? 1 : 0; 

                 // to avoid division by zero exception;
                if(!$total_questions_factor){
                    $total_questions_factor = 1;
                }

                $total_ratings_factor = 0; 
                $total_ratings_factor += $rating->q_one;
                $total_ratings_factor += $rating->q_two;
                $total_ratings_factor += $rating->q_three;
                $total_ratings_factor += $rating->q_four;
                $total_ratings_factor += $rating->q_five;
                $total_ratings_factor += $rating->q_six;
                $rating->overall_rating = $total_ratings_factor / $total_questions_factor;
                $rating->save();
            }
        }
    }
}
