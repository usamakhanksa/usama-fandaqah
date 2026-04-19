<?php

namespace App\Http\Controllers\Api\PublicApi\Src;

use App\Http\Resources\PublicApi\Reservations\OnlineReservationResource;
use App\OnlineReservation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Vinkla\Hashids\Facades\Hashids;

class ReservationController extends Controller
{
    /**
     * @Copyright : Sure Global for tech
     * @description : Get a list of all reservations made by consumer from online-reservations
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(){

        $perPage = request()->get('perPage');
        // Check if hotel id is encoded or not
        if(!(int)$perPage){
            return response()->json([
                'status' => 'error' ,
                'message' => __('Per Page param must be integer')
            ], 403);
        }
        $onlineReservationsCount = OnlineReservation::withOutGlobalScope('team_id')->withOutGlobalScope('unit_id')->withOutGlobalScope('customer_id')->where('provider' , 'public_api')->where('public_api_consumer_id' , auth()->guard('consumer')->user()->id )->count();
        if($onlineReservationsCount){
            return OnlineReservationResource::collection(
                OnlineReservation::withOutGlobalScope('team_id')->withOutGlobalScope('unit_id')->withOutGlobalScope('customer_id')->where('provider' , 'public_api')->where('public_api_consumer_id' , auth()->guard('consumer')->user()->id )->paginate($perPage)
            )->additional(['status' => 'success']);
        }else{
            return response()->json([
               'status' => 'error',
               'message' => __('No reservations found')
            ]);
        }

    }

    /**
     * @Copyright : Sure Global for tech
     * @description : Get details of reservation based on specific id
     * @return OnlineReservationResource|\Illuminate\Http\JsonResponse
     */
    public function getReservationDetails(){

        if(!\request()->get('id')){
            return response()->json([
                'status' => 'error' ,
                'message' => __('Reservation id param is required')
            ], 403);
        }
        try {
            $onlineReservationId =  Hashids::decode(\request()->get('id'))[0];
        }catch(\Exception $e){
            if($e instanceof \ErrorException){
                return response()->json([
                    'status' => 'error' ,
                    'message' => __('Please make sure that reservation id encoded')
                ]);
            }
        }

        $onlineReservationObject = OnlineReservation::withOutGlobalScope('team_id')->withOutGlobalScope('customer_id')->withOutGlobalScope('unit_id')->find($onlineReservationId);
        if(!$onlineReservationObject){
            return response()->json([
                'status' => 'error',
                'message' => __('Online Reservation is not found')
            ],404);
        }
        return new OnlineReservationResource($onlineReservationObject);

    }
}
