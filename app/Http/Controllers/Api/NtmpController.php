<?php

namespace App\Http\Controllers\Api;
use App\Team;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NtmpController extends Controller
{
    public function getTransactionIDByBookingNo(Request $request)
    {
        if(!$request->has('bNo') || !$request->has('tid')){
            return response()->json(['status' => 'failed' , 'message' => 'we expect a query string called bNo & tid']);
        }

        $team = Team::find($request->get('tid'));
        $scthIntegration =  $team->last_subscription->team->integration;

        if($scthIntegration){
            $scthIntegrationCredentials = json_decode($scthIntegration->values,true);
            $bookingNo = explode(',',$request->get('bNo'));

            $client = new Client([
                'headers' => [
                    'X-Gateway-APIKey' => $scthIntegrationCredentials['token'],
                    'Content-Type' => 'application/json'
                ],
            ]);

            $url = $this->getbaseUrl() . 'GetTransactionIDByBookingNo/1.0/getTransactionIDByBookingNo';

            try {
                $request = $client->request('POST', $url, [
                    'body' => \GuzzleHttp\json_encode([
                        'bookingNo' => $bookingNo
                    ]),
                    'exceptions' => true
                ]);


                $response = json_decode($request->getBody()->getContents());
                return $response;
            } catch (\Throwable $th) {
                return response()->json(['status' => 'failed' , 'message' => $th->getMessage()]);
            }

        }
        return response()->json(['status' => 'failed' , 'message' => 'Integration is not found']);
    }

    protected function getbaseUrl()
    {
        if (app()->environment('production')) {
            return config('scth.production_base_url');
        }
        return config('scth.dev_base_url');
    }
}
