<?php

namespace App\Http\Controllers\Api\PublicApi\Consumer;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateConsumerAccount;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;
use App\PublicApiConsumer;
use GuzzleHttp\Client;

class ConsumerController extends Controller
{
    /**
     * @description : Create new consumer with specific client
     * @param CreateConsumerAccount $request
     * @return array
     */
    public function createConsumer(CreateConsumerAccount $request){
        $consumer = new PublicApiConsumer();
        $consumer->name = $request->get('name');
        $consumer->email = $request->get('email');
        $consumer->password = crypt($request->get('password') , '');
        $consumer->web_hook_url = $request->get('web_hook_url');
        $consumer->web_hook_token = $request->get('web_hook_token');
        $consumer->save();

        // Create Password Client
        $client = Passport::client()->forceFill([
            'user_id' => $consumer->id,
            'name' => $consumer->name,
            'secret' => Str::random(40),
            'redirect' => $request->get('redirect'),
            'personal_access_client' => 0,
            'password_client' => 1,
            'revoked' => false,
        ]);


        $data = array();

        if($client->save()){

            // after saving consumer and client i need to authorize the consumer automatically
            // check for account approval will be handled later
            $http = new Client();
            // Send Guzzle Post Request
            $response = $http->post(url('/oauth/token'), [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => $client->id,
                    'client_secret' => $client->secret,
                    'provider' => 'consumers', // our new provider check auth.php ( acting as guard )
                    'username' => $request->get('email'),
                    'password' => $request->get('password')
                ],
            ]);

            // Decode the response
            $responseData = json_decode((string) $response->getBody());
            // Format the Api response
            $data['token_type']     =  $responseData->token_type;
            $data['expires_in']     =  $this->secondsToTime($responseData->expires_in);
            $data['client_id']      = $client->id ;
            $data['client_secret']  = $client->secret ;
            $data['consumer_email'] = $consumer->email;
            $data['access_token']   =  $responseData->access_token;
            $data['refresh_token']  =  $responseData->refresh_token;


            return response()->json(['status' => 'created' , 'message' => __('Consumer Created Successfully') , 'info' => __('As soon as you get an approval, we will be notified') , 'data' => $data],201);
        }else{
            return response()->json(['status' => 'failed' , 'message' => __('Something went wrong')],500);
        }

    }

    /**
     * Convert total numbers of seconds in human readable
     * @param $seconds
     * @return string
     * @throws \Exception
     */
    private function secondsToTime($seconds) {
        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@$seconds");
        return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
    }

}
