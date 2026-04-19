<?php

namespace App\Http\Controllers;

use App\Team;
use App\Unit;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        // $this->middleware('subscribed');

        // $this->middleware('verified');
    }

    /**
     * Show the application dashboard.
     *
     * @return Response
     */
    public function show()
    {
        return view('home');
    }


    public function upgrade(){
        return view('upgrade');
    }

    public function checkUnitReservations(Request $request)
    {
        $unit = Unit::find($request->id);
        $team_id = $unit->team_id;
        $reservations = $unit->reservations()->whereNull('checked_out')
        ->where('team_id', $team_id)
        ->get();
        return response()->json(['disable_active' => $reservations->count() ? true : false]);
    }

    public function updateFeaturedCategories(Request $request)
    {
        $team = Team::find($request->team_id);
        $incoming_unit_category_id = $request->id;
        $featured_unit_categories = $team->websiteSetting->featured_unit_categories ?? null ;
        $new_featured_categories = [];
        if(count($featured_unit_categories)){

            if(!$request->status){

                foreach ($featured_unit_categories as $unit_category) {
                    if($unit_category['id'] != $incoming_unit_category_id){
                        $new_featured_categories [] = $unit_category;
                    }
                }

            }else{
                $new_featured_categories = $featured_unit_categories;
            }

        }
        DB::table('website_settings')->where('team_id' , $team->id)
                    ->update(['featured_unit_categories' => json_encode($new_featured_categories)]);

        return response()->json(true);
    }

    public function checkUserNeedsVerification(Request $request){
        if( is_null(auth()->user()->email_verified_at) || is_null(auth()->user()->phone_verified_at) ){
            $expiryDate = \Carbon\Carbon::parse(auth()->user()->verification_expiry_at);
            $days_left = \Carbon\Carbon::now()->diffInDays($expiryDate, false);
            if($days_left < 0){
                $days_left = 0;
            }

            $isAdmin = \Illuminate\Support\Facades\DB::table('role_user')
                ->join('roles', 'role_user.role_id', '=', 'roles.id')
                ->where('role_user.user_id', auth()->id())
                ->where('roles.slug', 'admin')
                ->exists();

            if ($days_left <= 0 && !$isAdmin) {
               
                return response()->json([
                    'success' => false,
                    'needs_verification' => true
                ]);
                
            }else{
                return response()->json([
                    'success' => true,
                    'needs_verification' => false
                ]);
            }
        }
       
    }
}
