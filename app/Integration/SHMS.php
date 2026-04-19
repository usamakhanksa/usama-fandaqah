<?php

namespace App\Integration;

use App\Guest;
use App\Integration;
use App\Reservation;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use App\Integration\ShmsFactory;

class SHMS
{
    /**
     * create SHMS
     * @param  Reservation $reservation [description]
     * @param  array       $credentials [description]
     * @return array                   [description]
     */
    public static function checkIn(Reservation $reservation, $credentials)
    {
        $shms =  new ShmsFactory($reservation, $credentials, 1);
        $response =  $shms->create();
        return $response;
    }

    /**
     * update SHMS
     * @param  Reservation $reservation [description]
     * @param  array       $credentials [description]
     * @return array                   [description]
     */
    public static function update(Reservation $reservation, $credentials)
    {
        $shms =  new ShmsFactory($reservation, $credentials, 2);
        $response = $shms->update();
        return $response;
    }

    // Checkout And Rating Guest
    public static function checkOut(Reservation $reservation, $credentials)
    {
        $shms =  new ShmsFactory($reservation, $credentials, 3);
        $response = $shms->checkout();
        return $response;
    }

    public static function checkInAfterCancelCheckout(Reservation $reservation, $credentials)
    {  
        $shms =  new ShmsFactory($reservation, $credentials, 1);
        $response =  $shms->create();
        return $response;
    }

    

    /**
     * Add New Escort SHMS
     * @param  $reservation [reservation object]
     * @param  Guest $escort [Guest object]
     * @param  array       $credentials [description]
     * @return array                   [description]
     */
    public static function insertEscort($reservation_id,Guest $escort, $credentials)
    {
        $shms =  new ShmsFactory($escort, $credentials, 2);
        $response = $shms->insertEscort($reservation_id);
        return $response;
    }


    public static function deleteEscort(Guest $escort, $credentials)
    {
        $shms =  new ShmsFactory($escort, $credentials, 2);
        $response = $shms->deleteEscort($escort);
        return $response;
    }

    public static function updateEscort(Guest $escort, $credentials)
    {
        $shms =  new ShmsFactory($escort, $credentials, 2);
        $response = $shms->updateEscort($escort);
        return $response;
    }

    /**
     * cancel SHMS
     * @param  Reservation $reservation [description]
     * @param  array       $credentials [description]
     * @return array                   [description]
     */
    public static function transfer(Reservation $reservation, $credentials, $from, $to)
    {
        // If SHMS connection for the old version keep using this endpoint unless the user changed the integration 
        // As in the new integration now user can change room by updating the room nunmber
        if($reservation->team->integration_shomoos_version_one){
            $shms =  new ShmsFactory($reservation, $credentials, 3);
            $response = $shms->transfer($from, $to);
            return $response;
        }
    }

    /**
     * cancel SHMS
     * @param  Reservation $reservation [description]
     * @param  array       $credentials [description]
     * @return array                   [description]
     */
    public static function checkOutGuest($guest, $credentials)
    {
        $shms =  new ShmsFactory([], $credentials, 3);
        $response = $shms->checkoutGuest($guest);
        return $response;
    }


    /**
     * @param $model
     * @return bool|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function check($credentials): bool
    {
        $client = new Client();
        $headers = [
            'Authorization' => env('SHOMOOSTOKEN'),
            'Content-Type' => 'application/json'
        ];
        $body = '{
            "Header": {
                "RequestId": "123654",
                "UserId": "'.$credentials['userid'].'",
                "BranchCode": "'.$credentials['branchcode'].'",
                "BranchSecret": "'.$credentials['branchsecret'].'",
                "Lang": "ar"
            }
        }';

        $shomoos_api_url = app()->environment() == 'development' ? 'https://apistg.shomoos.com.sa' : 'https://api.shomoos.com.sa';
        $request = new Request('POST', "$shomoos_api_url/Accommodation/LookupService.svc/GetNationalities", $headers, $body);
        try {
            $response = $client->send($request);
            $response = json_decode($response->getBody());
            if (isset($response->Header->ProxyFaults[0]->FaultCode) && $response->Header->ProxyFaults[0]->FaultCode == 200){

                // i should here after validation user input with the new credentials 
                // we should disconnect the old integration as shomoos v1 will be dis-continued 

                $old_shomoos_integration = Integration::where('team_id' , auth()->user()->current_team_id)
                ->where('key' , 'SHMS')
                ->whereJsonContains('values->SPSCID', null)
                ->whereNull('deleted_at')
                ->first();
                // Delete the old integration 
                if($old_shomoos_integration){
                    $old_shomoos_integration->deleted_at = now();
                    $old_shomoos_integration->save();
                }
                return true;
            }
        } catch (\Throwable $e) {
            return false;
        }

       
       
    }

    protected function getbaseUrl()
    {
        return env('SHOMOOS_SERVICE_URL');
    }

    /**
     * @param $fields
     * @return bool
     */
    public function save($fields): bool
    {
        // TODO: check if already integrated and replace
        $integration = new Integration();
        $integration->key = 'SHMS';
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

    /**
     * @return mixed|void
     */
    public static function get($name, $team_id)
    {
        return Integration::findByKeyAndTeamId($name, $team_id)->last() ?? false;
    }
}
