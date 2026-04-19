<?php

namespace Surelab\CustomerReviews\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerReviews\RatingResource;
use App\Rating;
use App\Reservation;
use App\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerReviewsController extends Controller
{

    public function getTotal()
    {
        $all_reviews = request()->get('all_reviews');
        // dd($all_reviews);
        if($all_reviews){
            $ratings = Rating::whereIn('team_id',explode(',',env('DYAFA_HOTELS')))->get();
            $approved_ratings = Rating::where('status',1)->whereIn('team_id',explode(',',env('DYAFA_HOTELS')))->count();
        }else{
            $ratings = Rating::where('team_id', auth()->user()->current_team_id)->get();
            $approved_ratings = Rating::where('team_id', auth()->user()->current_team_id)->where('status',1)->count();
        }

        $q_one_factor = 0;
        $q_two_factor = 0;
        $q_three_factor = 0;
        $q_four_factor = 0;
        $q_five_factor = 0;
        $q_six_factor = 0;
        $q_nine_factor = 0;
        $q_ten_factor = 0;
        $q_eleven_factor = 0;
        $q_twelve_factor = 0;
        if(count($ratings)){
            foreach ($ratings as $rating) {
                 if($rating->q_one > 0) {
                    $q_one_factor += 10;
                 }
                 if($rating->q_two > 0) {
                    $q_two_factor += 10;
                 }
                 if($rating->q_three > 0) {
                    $q_three_factor += 10;
                 }
                 if($rating->q_four > 0) {
                    $q_four_factor += 10;
                 }
                 if($rating->q_five > 0) {
                    $q_five_factor += 10;
                 }
                 if($rating->q_six > 0) {
                    $q_six_factor += 10;
                 }
                if($rating->q_nine > 0) {
                    $q_nine_factor += 10;
                }
                if($rating->q_ten > 0) {
                    $q_ten_factor += 10;
                }
                if($rating->q_eleven > 0) {
                    $q_eleven_factor += 10;
                }
                if($rating->q_twelve > 0) {
                    $q_twelve_factor += 10;
                }
            }
        }

        if(!$q_one_factor){
            $q_one_factor = 10;
        }
        if(!$q_two_factor){
            $q_two_factor = 10;
        }
        if(!$q_three_factor){
            $q_three_factor = 10;
        }
        if(!$q_four_factor){
            $q_four_factor = 10;
        }
        if(!$q_five_factor){
            $q_five_factor = 10;
        }
        if(!$q_six_factor){
            $q_six_factor = 10;
        }
        if(!$q_nine_factor){
            $q_nine_factor = 10;
        }
        if(!$q_ten_factor){
            $q_ten_factor = 10;
        }
        if(!$q_eleven_factor){
            $q_eleven_factor = 10;
        }
        if(!$q_twelve_factor){
            $q_twelve_factor = 10;
        }


        $data = [
            'q_one' =>  number_format(floatval(($ratings->sum('q_one')/$q_one_factor)) * 10, 2, '.', ''),
            'q_two' =>  number_format(floatval(($ratings->sum('q_two')/$q_two_factor)) * 10, 2, '.', ''),
            'q_three'   =>  number_format(floatval(($ratings->sum('q_three')/$q_three_factor)) * 10, 2, '.', ''),
            'q_four'    =>  number_format(floatval(($ratings->sum('q_four')/$q_four_factor)) * 10, 2, '.', ''),
            'q_five'    =>  number_format(floatval(($ratings->sum('q_five')/$q_five_factor)) * 10, 2, '.', ''),
            'q_six' =>  number_format(floatval(($ratings->sum('q_six')/$q_six_factor)) * 10, 2, '.', ''),
            'q_nine'    =>  number_format(floatval(($ratings->sum('q_nine')/$q_nine_factor)) * 10, 2, '.', ''),
            'q_ten' =>  number_format(floatval(($ratings->sum('q_ten')/$q_ten_factor)) * 10, 2, '.', ''),
            'q_eleven'  =>  number_format(floatval(($ratings->sum('q_eleven')/$q_eleven_factor)) * 10, 2, '.', ''),
            'q_twelve'  =>  number_format(floatval(($ratings->sum('q_twelve')/$q_twelve_factor)) * 10, 2, '.', ''),
        ];

        $total = 0;
        if(count($ratings)){
            $total = collect($ratings)->pluck('overall_rating')->sum() / count(collect($ratings)->where('overall_rating' , '>' , 0));
        }
        $data['total'] = number_format($total, 2, '.', '');
        $data['count'] = $ratings->count();
        $data['approved_ratings'] = $approved_ratings;
        if(!$total){
            $label = __('No Reviews Found');
        }else{
            if ($total <= 5) {
                $label = __('Acceptable');
            } elseif ($total < 7) {
                $label = __('Good');
            } elseif ($total >= 7 and $total <= 8.5) {
                $label = __('Very good');
            }elseif ($total > 8.5) {
                $label = __('Excellent');
            }
        }


        $data['label'] = $label;

        return $data;
    }

    public function getReviews(){

        $query = Rating::query();
        if(!\request()->get('all_reviews')){
            $query->where('team_id' , auth()->user()->current_team_id);
        }else{
            $query->whereIn('team_id',explode(',',env('DYAFA_HOTELS')));
        }

        if((\request()->get('dateFrom') != 'null' && \request()->get('dateFrom') != '') && (\request()->get('dateTo') != 'null' && \request()->get('dateTo') != '') ){
            // $ratings = Reservation::has('rating')->whereBetween('checked_in', [\request()->get('dateFrom'), \request()->get('dateTo')])->pluck('rating_id');
            // $query->whereIn( 'id', $ratings);
            $query->whereBetween('created_at', [\request()->get('dateFrom'), \request()->get('dateTo')]);
        }

        if(\request()->get('status') != 'null'){
            $query->where('status', \request()->get('status'));
        }

        if(\request()->get('team_id_filter') != 'null'){
            $query->where('team_id', \request()->get('team_id_filter'));
        }

        // $ratings = Reservation::has('rating')->withTrashed()->pluck('rating_id');
        // $query->whereIn( 'id', $ratings);
        // $query->orderByDesc('overall_rating');
        $query->orderByDesc('created_at');
        $query->with('reservation.unit');

        return RatingResource::collection($query->paginate(10));
    }

    public function approveReview(Rating $rating){

        $rating->status = 1;
        $rating->save();
        return response()->json(['status' => 'success']);
    }

    public function getReview(Rating $rating){

        return response()->json($rating);
    }

    public function updateReview(Request $request , Rating $rating){

        $rating->q_seven = $request->get('positive_comment');
        $rating->q_eight = $request->get('negative_comment');
        $rating->save();

        return response()->json(['status' => 'success']);
    }

    public function getTeams(Request $request){
        $teams = Team::whereNull('deleted_at')
        ->whereIn('id',[1884,2411,2434,2578,2584,2585,2595,2623,2624,2626,2627,2628,2630,2631,3957,2632,2633,2635,2636,2668,2669,2672,2821,2983,3019,3044,3380,3519,3971])
        ->orderBy('name')
        ->get();
        $data = [];
        if(count($teams)) {
            foreach ($teams as $team) {
                $data [] = [
                    'id' => $team->id,
                    'name' => $team->name,
                    'name_en' => $team->en_name
                ];
            }
        }
        return response()->json($data);
    }

}
