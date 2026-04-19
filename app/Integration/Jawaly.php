<?php

namespace App\Integration;

use Aghanem\Jawaly\Facades\Jawaly as JawalyGateway;
use App;
use App\Integration;
use App\Jobs\Jawaly\OccupancyUpdate;
use App\Reservation;
use App\Team;
use App\Transaction;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Jawaly
{
    /**
     * @return mixed|void
     */
    public static function get($name, $team_id)
    {
        return Integration::findByKeyAndTeamId($name, $team_id)->last() ?? false;
    }

    /**
     * @param $model
     * @return bool|mixed
     * @throws GuzzleException
     */
    public function check($credentials)
    {
        // config()->set('jawaly.username', $credentials['username']);
        // config()->set('jawaly.password', $credentials['password']);
        // config()->set('jawaly.sender', $credentials['sender']);

        // $test = JawalyGateway::getCredits();
        // return $test[0];

        $gateWayRequest = $this->sendSMS($credentials['username'],$credentials['password'],'sms-gateway','966561187386',$credentials['sender']);
        if($gateWayRequest) {
            if($gateWayRequest->Code == 100 && $gateWayRequest->currentuserpoints > 0){
                return true;
            }

            if($gateWayRequest->Code == 102 || $gateWayRequest->Code == 103 || $gateWayRequest->Code == 106){
                return false;
            }
        }
        return false;
    }

    public function sendSMS($oursmsusername,$oursmspassword,$messageContent,$mobileNumber,$senderName)
    {
        $user = $oursmsusername;
        $password = $oursmspassword;
        $sendername = urlencode($senderName);
        $text = urlencode( $messageContent);
        $to = $mobileNumber;

        $client = new Client();
        $url = "http://www.4jawaly.net/api/sendsms.php?username=$user&password=$password&numbers=$to&message=$text&sender=$sendername&unicode=E&return=json";

        $request = $client->request('GET',$url);
        $response = json_decode($request->getBody()->getContents());
        return $response;
    }

    /**
     * @param $fields
     * @return bool
     */
    public function save($fields): bool
    {
        // TODO: check if already integrated and replace
        $integration = new Integration();
        $integration->key = 'Jawaly';
        $integration->values = json_encode($fields);
        return $integration->save();
    }

    /**
     * @return mixed|void
     */
    public function errors()
    {
        return [];
    }
}
