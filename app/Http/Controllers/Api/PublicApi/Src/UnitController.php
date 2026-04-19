<?php

namespace App\Http\Controllers\Api\PublicApi\Src;

use App\Customer;
use App\Http\Resources\PublicApi\Reservations\OnlineReservationResource;
use App\Http\Resources\PublicApi\Reservations\ReservationResource;
use App\OnlineReservation;
use App\Reservation;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PublicApi\Units\UnitResource;
use App\Unit;
use Vinkla\Hashids\Facades\Hashids;

class UnitController extends Controller
{
    /**
     * @Copyright : Sure Global for tech
     * @description : Get list of units
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {

        $hotelIds = request()->get('hotelsIds') ? explode(",",request()->get('hotelsIds')) : 0;
        $perPage = request()->get('perPage');

        $hotelIdsDecoded = array();
        if($hotelIds){

            foreach ($hotelIds as $hotelId){
                try{
                    $hotelIdsDecoded [] = Hashids::decode($hotelId)[0];
                }catch(\Exception $e){
                    if($e instanceof \ErrorException){
                        return response()->json([
                            'status' => 'error' ,
                            'message' => __('Please make sure that all hotels ids are encoded')
                        ]);
                    }
                }
            }
        }


        $units = Unit::withOutGlobalScope('team_id')
            ->when($hotelIds , function($query) use($hotelIdsDecoded){
                return $query->whereIn('team_id' , $hotelIdsDecoded);
            })->get()->count();

        if($units){
            return UnitResource::collection(
                Unit::withOutGlobalScope('team_id')
                    ->when($hotelIdsDecoded , function($query) use($hotelIdsDecoded){
                        return $query->whereIn('team_id' , $hotelIdsDecoded);
                    })
                    ->paginate($perPage)
            )->additional(['status' => 'success']);
        }else{
            return response()->json([
                'status' => 'error' ,
                'data' => __('No units found for specified hotels')
            ]);
        }


    }


    /**
     * @Copyright : Sure Global for tech
     * @description : Get Unit Details | Check Availability
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnitDetails()
    {
       // Accept function params
        $id = \request()->get('id');
        $dateFrom = \request()->get('dateFrom');
        $dateTo   = \request()->get('dateTo');


        if(!$id){
            return response()->json([
                'status' => 'error' ,
                'message' => __('Unit is is required')
            ]);
        }

        if(!$dateFrom){
           return response()->json([
               'status' => 'error' ,
               'message' => __('Please enter Date From')
           ]);
       }
        if(!$dateTo){
            return response()->json([
                'status' => 'error' ,
                'message' => __('Please enter Date To')
            ]);
        }

        try {
            $unitDecodedId =  Hashids::decode($id)[0];
        }catch(\Exception $e){
            if($e instanceof \ErrorException){
                return response()->json([
                    'status' => 'error' ,
                    'message' => __('Please make sure that unit id encoded')
                ]);
            }
        }

        // Get the Unit Object to be passed to Unit Resource
        $unit = Unit::withOutGlobalScope('team_id')->find($unitDecodedId);
        // Check Unit Availability based on specified dates
        $availability = $this->checkUnitAvailability($unitDecodedId,$dateFrom,$dateTo);



        if(!$availability){

           return response()->json([
               'status' => 'success',
               'message' => __('Unit is available for specified dates'),
                'data' =>   new UnitResource($unit)
            ]);
       }else{

           return response()->json([
               'status' => 'error',
               'message' => __('Unit is not available for specified dates'),
               'data' =>   new UnitResource($unit)
           ]);
       }

    }

    /**
     * @Copyright : Sure Global for tech
     * @description : Book unit if it's available
     * @param Request $request
     * @note : this function used helper functions from App\helpers.php file to handle vat and ewa taxes & totals
     * @return \Illuminate\Http\JsonResponse
     */
   public function bookUnit(Request $request){

       /*----------------------------------------------------- Receive Endpoint Params ----------------------------------------------------*/
       $id = \request()->get('id');
       $dateFrom = \request()->get('dateFrom');
       $dateTo   = \request()->get('dateTo');
       $customerName   = \request()->get('customerName');
       $customerEmail   = \request()->get('customerEmail');
       $customerPhone   = \request()->get('customerPhone');


       /*----------------------------------------------------- Custom Json validation  ----------------------------------------------------*/
       if(!$id){
           return response()->json([
                'status' => 'error' ,
                'message' => __('Unit id is required')
            ], 403);
       }
       if(!$dateFrom){
           return response()->json([
               'status' => 'error' ,
               'message' => __('Date from is required')
           ], 403);
       }

       if(!$dateTo){
           return response()->json([
               'status' => 'error' ,
               'message' => __('Date to is required')
           ], 403);
       }
       if(!$customerName){
           return response()->json([
               'status' => 'error' ,
               'message' =>  __('Customer name is required')
           ], 403);
       }

       if(!$customerEmail){
           return response()->json([
               'status' => 'error' ,
               'message' =>  __('Customer email is required')
           ], 403);
       }

       // Rule for valid email
       if(!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)){
           return response()->json([
               'status' => 'error' ,
               'message' =>  __('Customer email must be valid email')
           ], 403);
       }


       if(!$customerPhone){
           return response()->json([
               'status' => 'error' ,
               'message' =>  __('Customer phone is required')
           ], 403);
       }

       /*----------------------------------------------------- Proceed with the Logic ----------------------------------------------------------*/
       try {
           $unitDecodedId = Hashids::decode($id)[0];
       }catch(\Exception $e){
           if($e instanceof \ErrorException){
               return response()->json([
                   'status' => 'error' ,
                   'message' => __('Please make sure that unit id encoded')
               ]);
           }
       }

       // Get the Unit Object with out the global scope
       $unit = Unit::withOutGlobalScope('team_id')->find($unitDecodedId);
       // Check the occupation status of the unit based on supplied dates
       $unitOccupation = $this->checkUnitAvailability($unitDecodedId,$dateFrom,$dateTo);
       if(!$unitOccupation){

           /**----------------------------------------------------------- Prices & Period ------------------------------------------------------------*/
           $dateFromParsed = Carbon::parse($dateFrom);
           $dateToParsed = Carbon::parse($dateTo);
           $nights = $dateToParsed->diffInDays($dateFromParsed);
           if ($dateFromParsed != $dateToParsed) {
               $dateToParsed->subDay();
           }
           $period = CarbonPeriod::create($dateFromParsed, $dateToParsed);

           $dates = [];
           $total_price = 0;
               foreach ($period as $date) {
                   $day_price = $unit->dayPrice($date->format('l'));
                   $total_price += $day_price;
                   $dates[] = [
                       'date' => $date->format('Y-m-d'),
                       'date_name' => __($date->format('l')),
                       'price_row' => $day_price,
                       'price' => money_format('%i', $day_price)
                   ];
               }

           $vatPercentage = getVatPercentageForUnit($unit->team_id) ;
           $ewaPercentage = getEwaPercentageForUnit($unit->team_id) ;
           $ewaTotalUnFormatted =  getEwaTotalForUnit($total_price , getEwaPercentageForUnit($unit->team_id) , false) ;
           $ewaTotalFormatted =  getEwaTotalForUnit($total_price , getEwaPercentageForUnit($unit->team_id) , true) ;
           $vatTotal = getVatTotalForUnit($total_price , $ewaTotalUnFormatted , $vatPercentage , false);


           $vat = $vatTotal;
           $ewa = $ewaTotalFormatted;
           $total_raw = $total_price + $vat + $ewa;
           $total = number_format($total_raw, 2);

           // Collecting data if needed in the future
           $totals = [
               'currency' => 'SAR',
               'days' => $dates,
               'price' => $total_price,
               'vat_parentage' => $vatPercentage,
               'ewa_parentage' => $ewaPercentage,
               'total_vat' => $vat,
               'total_ewa' => $ewa,
               'total_price' => $total,
               'total_price_raw' => $total_raw,
           ];

           /**------------------------------------------------------ Prices & Period Ends ------------------------------------------------------------*/


           /**------------------------------------------------------- Customer Data ------------------------------------------------------------------*/



           $customer = Customer::withOutGlobalScope('team_id')->where('email' , '=' , $customerEmail)->where('phone' , '=' , $customerPhone)->where('team_id' , '=' , $unit->team_id)->first();

           if(!$customer) {
               // here we are going to register the new customer
               $customer = new Customer();
               $customer->team_id = $unit->team_id;
               $customer->name = $customerName;
               $customer->email = $customerEmail;
               $customer->phone = $customerPhone;
               $customer->save();

           }

           /**------------------------------------------------------- Customer Data Ends ------------------------------------------------------------*/


           /**------------------------------------------------------- Online Reservation ------------------------------------------------------------*/
          $onlineReservation =  OnlineReservation::create([
               'customer_id'   =>  $customer->id,
               'date_in'   =>  $dateFrom,
               'date_out'   =>  $dateTo,
               'price'   =>  $total,
               'nights'   =>  $nights ,
               'unit_id'   =>  $unitDecodedId,
               'team_id'   =>  $unit->team_id ,
               'provider' => 'public_api',
               'public_api_consumer_id' => auth()->guard('consumer')->user()->id
           ]);

           $onlineReservationObject = OnlineReservation::withOutGlobalScope('team_id')->withOutGlobalScope('unit_id')->withOutGlobalScope('customer_id')->find($onlineReservation->id);
           /**---------------------------------------------------- Online Reservation Ends ------------------------------------------------------------*/

           /**--------------------------------------------------------------- Response ----------------------------------------------------------------*/
           return response()->json([
               'status' => 'success',
               'message' => __('Reservation has been made successfully'),
               'data' =>   new OnlineReservationResource($onlineReservationObject)
           ]);

       }else{
           /**-------------------------------------------------------- Unit Unavailable Response --------------------------------------------------------*/
           return response()->json([
               'status' => 'error',
               'message' => __('Unit is not available for specified dates'),
               'data' =>   new UnitResource($unit)
           ]);
       }

   }

    /**
     * Check Unit Availability based on specified dates
     * @param $unitDecodedId
     * @param $dateFrom
     * @param $dateTo
     * @return \Illuminate\Database\Eloquent\Builder
     */
   private function checkUnitAvailability($unitDecodedId,$dateFrom,$dateTo){
       // Initialize new reservation query
       $query = Reservation::query();
       $query =  Reservation::where('unit_id' , $unitDecodedId) ;
       $query->unitAvailableInBetween($dateFrom , $dateTo);
       return $query->first() ;
   }

}
