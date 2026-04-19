<?php

namespace SureLab\Calender\Http\Controllers;

use Config;
use App\Team;
use App\Reservation;
use App\ReservationContract;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\SendContractToSignedDigitalyViaSMS;
class ContractSMSController extends Controller
{
  public function sendContractViaSMS(Request $request){

    $team = Team::find($request->team_id);
    $sms_title = $team->name . ' - ' . __('Rental Contract',[],$request->lang) ;

    $phone = $request->customer_phone;
    if($request->has('reservation_id')){
      $reservation = Reservation::with('company')->find($request->get('reservation_id'));
      if($reservation && $reservation->reservation_type == 'group'){
        if($reservation->company && $reservation->company->entity_type == 'company' && $reservation->company->person_incharge_phone ){
          $phone = preg_replace('/\s+/', '', $reservation->company->person_incharge_phone );
        }
      }
    }

    /**
     * clean way to make short-urls it saves our back in sms 
     * if failed to generate shorturl - original url will be returned as fallback
     */
    $document_url = getShortUrl($request->contract_url);

    $data = [
        'team_id' => $request->team_id,
        'contract_url' => $sms_title . ' ' . $document_url,
        'customer_phone' => $phone
    ];

    SendContractToSignedDigitalyViaSMS::dispatch($data);

    return response()->json([
        'success' => true,
        'message' => 'job dispatched',
        'abort' => false
    ]);
  }

  public function sendPromissoryViaSMS (Request $request) {
    $promissory = $request->get('promissory');
    if(!isset($promissory)) {
      return response()->json(null);
    }
    $team = Auth()->user()->currentTeam;
    $sms_title = null;
    $sms_title = $team->name . ' - ' .  __('The Promissory',[],$request->lang) ; 
    $promissory_id = $promissory['hash_id'];
    $promissory_url = env('APP_URL') . '/home/reservation/promissory/' . $promissory_id;
    
    $reservation = Reservation::with('company')->with('customer')->find($promissory['reservation']['id']);
    if(isset($reservation->customer)) {
      $phone = $reservation->customer->phone ?? "";
    }
    if($reservation && $reservation->reservation_type == 'group') {
      if($reservation->company && $reservation->company->entity_type == 'company' && $reservation->company->person_incharge_phone ){
        $phone = preg_replace('/\s+/', '', $reservation->company->person_incharge_phone );
      }
    }

    /**
     * clean way to make short-urls it saves our back in sms 
     * if failed to generate shorturl - original url will be returned as fallback
     */
    $document_url = getShortUrl($promissory_url);

    $data = [
      'team_id' => $team->id,
      'contract_url' => $sms_title . ' ' . $document_url,
      'customer_phone' => $phone
    ];

    SendContractToSignedDigitalyViaSMS::dispatch($data);
    
    return response()->json([
      'success' => true,
      'message' => 'job dispatched',
      'abort' => false
    ]);
  }
}
