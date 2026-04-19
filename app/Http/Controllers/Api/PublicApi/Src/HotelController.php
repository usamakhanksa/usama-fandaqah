<?php

namespace App\Http\Controllers\Api\PublicApi\Src;


use App\Http\Resources\PublicApi\Hotels\HotelResource;
use App\Http\Resources\PublicApi\Hotels\HotelResourceWithoutRelationships;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Team;
use Vinkla\Hashids\Facades\Hashids;

class HotelController extends Controller
{
    /**
     * @Copyright : Sure Global for tech
     * @description : Get a list of available hotels
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $perPage = request()->get('perPage');
        // Check if hotel id is encoded or not
        if(!(int)$perPage){
            return response()->json([
                'status' => 'error' ,
                'message' => __('Per Page param must be integer')
            ], 403);
        }
        return HotelResourceWithoutRelationships::collection(
            Team::paginate($perPage)
        );
    }


    /**
     * @Copyright : Sure Global for tech
     * @description : Show Hotel Details along-side with available units
     * @return HotelResource
     */
    public function getHotelDetails()
    {
        // Check if id param is sent from the beginning
        if(!\request()->get('id')){
            return response()->json([
                'status' => 'error' ,
                'message' => __('Hotel id param is required')
            ], 403);
        }

        try {
            $hotelDecodedId = Hashids::decode(\request()->get('id'))[0] ;
        }catch(\Exception $e){
            if($e instanceof \ErrorException){
                return response()->json([
                    'status' => 'error' ,
                    'message' => __('Please make sure that hotel id encoded')
                ]);
            }
        }


        return  new HotelResource(Team::find($hotelDecodedId));

    }


}
