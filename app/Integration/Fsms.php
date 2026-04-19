<?php
/**
 *  * Created by PhpStorm.
 * User: Mohamed Yasser ( yasoesr@gmail.com )
 * Date: 10/21/19
 * Time: 1:46 PM
 */

namespace App\Integration;

use App\Integration;
use App\Integration\Base\BaseIntegration;
use App\IntegrationSettings;
use App\Reservation;
use GuzzleHttp\Client;
use Liliom\Unifonic\UnifonicClient;
use Liliom\Unifonic\UnifonicFacade;

class Fsms
{
    public function check($credentials): bool
    {
        // dd($credentials);
        // fsms url
        $url = env('FSMS_URL');
        // check api $url.api/check-key
        $url = $url . 'api/check-key';
        $client = new Client();
        $response = $client->request('POST', $url, [
            'form_params' => [
                'Api_key' => $credentials['appSid']
            ]
        ]);
        $body = json_decode($response->getBody()->getContents(), true);
        // dd($body);
        if ($body['status'] == "success") {
            return true;
        }
        return false;

    }

    /**
     * create a record for tenant in database
     * @param $fields
     * @return mixed
     */
    public function save($fields): bool
    {
        $integration = Integration::findByKey('fsms')->first();
        if (!$integration) {
            $integration = new Integration();
        }
        $integration->key = 'fsms';
        $fields['type'] = 'sms';
        $integration->values = json_encode($fields);
        return $integration->save();
    }

    /**
     * @param $name
     * @param $team_id
     * @return bool|mixed
     */
    public static function get($name, $team_id)
    {
        return Integration::findByKeyAndTeamId($name, $team_id)->last() ?? false;
    }

    public function errors()
    {
        return [
            'App Sid' => __('App Sid Not valid')
        ];
    }
}
