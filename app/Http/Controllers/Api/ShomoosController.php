<?php

namespace App\Http\Controllers\Api;

use App\Events\ShomosResendReservationCheckIn;
use App\Exceptions\ValidationException;
use App\Handlers\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\EmployeeResource;
use App\Integration\SHMS;
use App\IntegrationLog;
use App\Jobs\SHMS\CheckIn;
use App\Jobs\SHMS\CheckOut;
use App\Reservation;
use App\Unit;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ShomoosController extends Controller
{
    /**
     * get transaction index method
     *
     * @return App\Http\Resources\TransactionResource
     */
    public function insertShomosId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'model_id' => 'required|integer',
            'model_type' => 'required|string',
            'shomoos_id' => 'required'
        ]);

        if ($validator->fails())
            throw new ValidationException($validator->errors()->first());
        $class = "\\App\\".$request->model_type;
        $model = $class::findOrfail($request->model_id);
        $model->shomoos_id = $request->shomoos_id;
        $model->update();

        return response()->json([
            'status' => 200,
            'message' => "done"
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function reservationLog($id,$team_id, Request $request)
    {
        $logs = IntegrationLog::where('team_id' , $team_id)
        ->where('type' , 'App\\Integration\\SHMS')
        ->where('subject_id',$id)
        ->where('subject_type',Reservation::class)
        ->whereNotNull('response')
        ->get();
        return response()->json($logs);
        $client = new Client(); 
        $response = $client->get('https://shomoos.fandaqah.com/logs/reservation/'.$id, [
            'form_params' => [
                'sample-form-data' => 'value'
            ]
        ]);
        return json_decode($response->getBody()->getContents(), true) ;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function resend($id, Request $request)
    {
        $reservation = Reservation::find($id);
        $credentials = Settings::checkIntegration('SHMS', $reservation->team_id);

        if ($credentials) {
            if(isset($request->log) && isset($request->log['type']) && $request->log['type'] == 'update_check_in'){
                return SHMS::update($reservation, $credentials);
            }elseif(isset($request->log)  && isset($request->log['type']) && $request->log['type'] == 'check_out'){
                return SHMS::checkOut($reservation, $credentials);
            }else{
                $data = $this->reservationLog($id, new Request)['data'];
                if(isset($data) && count($data)){
                    $last_log = end($data);
                    if($last_log['class'] != 'danger' && ($last_log['type'] == 'check_in' || $last_log['type'] == 'update_check_in') && $reservation->checked_out ){
                        return SHMS::checkOut($reservation, $credentials);
                    }
                }else{
                    return SHMS::checkIn($reservation, $credentials);
                }
            }
        }
        return response()->json(['success'=> 'error'], 401);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function teamLog($id, Request $request)
    {
        $client = new Client(); 
        $response = $client->get('https://shomoos.fandaqah.com/logs/team/'.$id, [
            'form_params' => [
                'sample-form-data' => 'value'
            ]
        ]);
        return json_decode($response->getBody()->getContents(), true) ;
    }
}
