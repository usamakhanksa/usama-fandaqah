<?php
namespace App\Http\Controllers\Api\Booking;
use App\City;
use App\Team;

use App\HotelType;
use App\UnitCategory;

use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Controllers\Controller;
use App\Http\Resources\Booking\CitiesResource;
use App\Http\Resources\Booking\HotelTypeResource;
use App\Http\Resources\Booking\UnitCategoryResource;
use App\Http\Resources\Booking\RelatedHotelsResource;
use App\Http\Resources\Booking\UnitCategoryWithSpecialPricesResource;

class FandaqahController extends Controller
{

    public function __construct()
    {
        $this->middleware(['fandaqah.booking'])->except(['getCities','getAttachedHotels']);
    }

    public function getCities(Request $request){
        return CitiesResource::collection(City::all());
    }
    public function getHotelTypes(Request $request){
        return HotelTypeResource::collection(HotelType::orderBy('id','desc')->get());
    }

    public function getAttachedHotels(Request $request , $city_id){
        $hotelsPerCity = RelatedHotelsResource::collection(Team::where('city_id' , (int) $city_id)->whereNull('deleted_at')->get());
        return response()->json($hotelsPerCity, 200) ;
    }

    public function searchUnitCategories(Request $request , $date_start, $date_end , $city_id){

        
        if($city_id == 'all'){
            
            if($request->has('type')){
                $team_ids = Team::whereIn('city_id' , City::all()->pluck('id'))->where('type_id' , $request->get('type'))->whereNull('deleted_at')->get()->pluck('id');
            }else{
                $team_ids = Team::whereIn('city_id' , City::all()->pluck('id'))->whereNull('deleted_at')->get()->pluck('id');
            }
        }else{
            if($request->has('type')){
                $team_ids = Team::where('city_id' , (int) $city_id)->where('type_id' , $request->get('type'))->whereNull('deleted_at')->get()->pluck('id');
            }else{
                $team_ids = Team::where('city_id' , (int) $city_id)->whereNull('deleted_at')->get()->pluck('id');
            }
        }

        return UnitCategoryResource::collection(
            UnitCategory::whereStatus(true)->whereIn('team_id' , $team_ids)->orderBy('team_id' , 'desc')->whereHasUnits()->paginate(config('app.search_per_page'))
        );
    }
    
}
