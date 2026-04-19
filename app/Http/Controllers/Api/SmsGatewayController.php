<?php

namespace App\Http\Controllers\Api;

use App\Country;
use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Integration;
use App\IntegrationSettings;
use Illuminate\Http\Request;
use Liliom\Unifonic\UnifonicFacade;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use GuzzleHttp\Client;

class SmsGatewayController extends Controller
{
    /**
    * get transaction index method
    *
    * @return App\Http\Resources\TransactionResource
    */
    public function check_integrate_with_sms_gateway(Request $request)
    {
        $list = IntegrationSettings::with('integration')->whereIn('key' ,['fsms', 'Jawaly'])
        ->where('status' , true)->whereHas('integration', function ($query) use($request) {
                $query->where('team_id', $request->team_id);
            })->first();

            $key = $list->key ?? null;
            // dd($key);

            if($key == 'fsms'){

                // get from integration where el key = fsms and team_id = auth()->user()->team_id
                $integartion = Integration::where('key', 'fsms')->where('team_id', $request->team_id)->first();
                $credentials =  json_decode($integartion->values, true);

                // check if the key is valid or not from check function in app\integration\fsms.php
                $check = new \App\Integration\Fsms();

                if($check->check($credentials)){
                    // now check the balance
                    $client = new Client();
                    $response = $client->request('POST', env('FSMS_URL') . 'api/get-balance', [
                        'form_params' => [
                            'Api_key' => $credentials['appSid']
                        ]
                    ]);
                    $body = json_decode($response->getBody()->getContents(), true);
                    if ($body['status'] == "success" && $body['balance'] > 0 ){
                        return response()->json([
                            'data' =>  [
                                'check' => true,
                                'type' => $key,
                            ]
                        ]);
                    }elseif($body['status'] == "success" && $body['balance'] == 0 ){
                        return response()->json([
                            'data' =>  [
                                'check' => false,
                                'type' =>"no_balance",
                            ]
                        ]);
                    }
                    else{
                        return response()->json([
                            'data' =>  [
                                'check' => false,
                                'type' => $key,
                            ]
                        ]);
                    }

                }else{
                    return response()->json([
                        'data' =>  [
                            'check' => false,
                            'type' => $key,
                        ]
                    ]);
                }






            }


        return response()->json([
            'data' =>  [
                'check' => isset($list),
                'type' => $key,
            ]
        ]);
    }
}
