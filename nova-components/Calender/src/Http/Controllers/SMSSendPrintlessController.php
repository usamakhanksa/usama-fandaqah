<?php

namespace SureLab\Calender\Http\Controllers;

use App\Reservation;
use Illuminate\Http\Request;
use App\Jobs\SMSPrintlessJob;
use App\Jobs\SendInvoiceViaSMSJob;
use App\Http\Controllers\Controller;
use App\Team;
use GuzzleHttp\Client;
class SMSSendPrintlessController extends Controller
{
    /**
     * Send SMS To Customer With the document info
     * at the end i only care about data variable which should be formed like this 
     *  'team_id' => $team->id,
     *  'sms_content' => $team_name . ' - ' . $sms_base_title . ' ' . $document_url,
     *  'phone' => $customer_phone
     * these data are coming through a vue component called SmsComponent that can be used in too many places
     * @param Request $request
     * @return void
     */
    public function send(Request $request){
        $data = [];
        $document_type = $request->document_type;
        $original_url = $request->document_url;
        $sms_base_title = $request->sms_base_title;

        /**
         * clean way to make short-urls it saves our back in sms 
         * if failed to generate shorturl - original url will be returned as fallback
         */
        $document_url = getShortUrl($original_url);

        if($document_type == 'pos'){
            $phone = $request->phone;
            $team_id = $request->team_id;
            $team = Team::find($team_id);
            $data = [
                'team_id' => $team->id,
                'sms_content' => $team->name . ' - ' . $sms_base_title . ' ' . $document_url,
                'phone' => $phone
            ];
    
            SMSPrintlessJob::dispatch($data);

            return response()->json([
                'success' => true,
                'message' => 'job dispatched',
                'abort' => false
            ]);
        }

        $reservation = Reservation::with('customer','team','company')->find($request->entity_id);
        if(!$reservation){
            return response()->json([
                'success' => false,
                'message' => 'no reservation found',
                'abort' => true
            ]);
        }
        $team = $reservation->team;
        $phone = null;

        if($reservation && $reservation->reservation_type == 'group'){
            if($reservation->company && $reservation->company->entity_type == 'company' && $reservation->company->person_incharge_phone ){
                $phone = preg_replace('/\s+/', '', $reservation->company->person_incharge_phone );
            }

            if(!$phone){
                return response()->json([
                    'success' => false,
                    'message' => 'no person in charge phone found',
                    'abort' => true
                ]);
            }
            if($reservation->company && $reservation->company->entity_type == 'individual' && $reservation->customer && $reservation->customer->phone ){
                $phone = preg_replace('/\s+/', '', $reservation->customer->phone );
            }
        }
          
        if($reservation && $reservation->reservation_type == 'single'){
            if($reservation->customer && $reservation->customer->phone){
                $phone =  preg_replace('/\s+/', '', $reservation->customer->phone );
            }
        }


        if(!$phone){
            return response()->json([
                'success' => false,
                'message' => 'no customer phone found',
                'abort' => true
            ]);
        }

        $team_name = $team->name;
        $data = [
            'team_id' => $team->id,
            'sms_content' => $team_name . ' - ' . $sms_base_title . ' ' . $document_url,
            'phone' => $phone
        ];

        SMSPrintlessJob::dispatch($data);

        return response()->json([
            'success' => true,
            'message' => 'job dispatched',
            'abort' => false
        ]);
    }
}
