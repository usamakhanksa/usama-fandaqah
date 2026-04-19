<?php

namespace App\Integration;

use App;
use App\Integration;
use App\Jobs\SureBills\OccupancyUpdate;
use App\Reservation;
use App\Team;
use App\Transaction;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class SureBills
{
    /**
     * create SureBills
     * @param Reservation $reservation [description]
     * @param array $credentials [description]
     * @return array                   [description]
     * @throws GuzzleException
     */
    public static function create(Reservation $reservation, $credentials)
    {
        $scth = new ScthFactory($reservation, $credentials, 1);
        $response = $scth->create();

        return $response;
    }

    /**
     * @param Reservation $reservation
     * @param $credentials
     * @param bool $checkInOrOut
     * @return array
     * @throws GuzzleException
     */
    public static function update(Reservation $reservation, $credentials, $checkInOrOut = false)
    {
        $scth = new ScthFactory($reservation, $credentials, 2, ScthFactory::BOOKING, $checkInOrOut);
        $response = $scth->update();

        if ($checkInOrOut and $reservation->checked_out) {
            if ($reservation->getDepositSum() > 0) {
                self::AllExpense($reservation, $credentials);
            }
            self::occupancy($reservation->team, $credentials);
        }
        return $response;
    }

    /**
     * cancel SureBills
     * @param Reservation $reservation [description]
     * @param array $credentials [description]
     * @return array                   [description]
     * @throws GuzzleException
     */
    public static function cancel(Reservation $reservation, $credentials)
    {
        $scth = new ScthFactory($reservation, $credentials, 3);
        $response = $scth->cancel();

        self::occupancy($reservation->team, $credentials);

        return $response;
    }




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
    public function check($credentials): bool
    {
        // $client = new Client([
        //     'headers' => ['X-Gateway-APIKey' => $credentials['token']],
        // ]);

        // $url = $this->getbaseUrl() . 'CreateOrUpdateBooking/1.0/createOrUpdateBooking';

        // $statusCode = $client->request('POST', $url, [
        //     'exceptions' => false,
        //     'auth' => [
        //         $credentials['username'],
        //         $credentials['password']
        //     ]
        // ])->getStatusCode();

        // if ($statusCode == 400) {
        //     return true;
        // }
        // return false;

        return true;
    }

    protected function getbaseUrl()
    {
        if (App::environment('production')) {
            return 'https://api.ntmp.gov.sa/gateway/';
        }

        return 'https://dev-hotelsapi.scth.gov.sa/gateway/';
    }

    /**
     * @param $fields
     * @return bool
     */
    public function save($fields): bool
    {
        // TODO: check if already integrated and replace
        $integration = new Integration();
        $integration->key = 'SureBills';
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
