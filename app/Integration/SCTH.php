<?php

namespace App\Integration;

use App\Team;
use App\Integration;
use App\Jobs\SCTH\AllExpenseBooking;
use App\Reservation;
use App\Transaction;
use GuzzleHttp\Client;
use App\Jobs\SCTH\OccupancyUpdate;
use Illuminate\Support\Facades\App;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class SCTH
{
    /**
     * create SCTH
     * @param Reservation $reservation [description]
     * @param array $credentials [description]
     * @return array                   [description]
     * @throws GuzzleException
     */
    public static function create(Reservation $reservation, $credentials)
    {
        $scth = new ScthFactory($reservation, $credentials, 1);
        $response = $scth->create();

        $transaction_id = isset($response['transaction_id']) ? $response['transaction_id'] : null;
        // Dispatching occupancy job
        OccupancyUpdate::dispatch($reservation->team,$transaction_id,false);
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
        if(!$checkInOrOut){
            $scth = new ScthFactory($reservation, $credentials, 2, ScthFactory::BOOKING, $checkInOrOut);
        }

        if ($checkInOrOut and $reservation->checked_out) {
            $scth = new ScthFactory($reservation, $credentials, 2, ScthFactory::CHECK_OUT, $checkInOrOut);
        }

        if ($checkInOrOut and $reservation->checked_in) {
            $scth = new ScthFactory($reservation, $credentials, 2, ScthFactory::CHECK_IN, $checkInOrOut);
        }

        $response = $scth->update();

        if ($checkInOrOut and $reservation->checked_out) {
            if ($reservation->getDepositSum() > 0) {
                AllExpenseBooking::dispatch($reservation,$credentials);
            }
        }

        $transaction_id = isset($response['transaction_id']) ? $response['transaction_id'] : null;
        OccupancyUpdate::dispatch($reservation->team,$transaction_id,false);
        return $response;
    }

    /**
     * cancel SCTH
     * @param Reservation $reservation [description]
     * @param array $credentials [description]
     * @return array                   [description]
     * @throws GuzzleException
     */
    public static function cancel(Reservation $reservation, $credentials)
    {
        $scth = new ScthFactory($reservation, $credentials, 3);
        $response = $scth->cancel();
        OccupancyUpdate::dispatch($reservation->team,$reservation->scth_reference,false);
        return $response;
    }

    /**
     * expense SCTH
     * @param Reservation $reservation [description]
     * @param Transaction $transaction [description]
     * @param array $credentials [description]
     * @return array                   [description]
     * @throws GuzzleException
     */
    public static function expense(Reservation $reservation, Transaction $transaction, $credentials)
    {
        $scth = new ScthFactory($reservation, $credentials, 4);
        $response = $scth->expense($transaction);
        // Log::info($response);

        return $response;
    }

    /**
     * @param Reservation $reservation
     * @param $credentials
     * @return array
     * @throws GuzzleException
     */
    public static function AllExpense(Reservation $reservation, $credentials)
    {
        $scth = new ScthFactory($reservation, $credentials, 4);
        $response = $scth->allExpense($reservation->getDepositTransactions());
        // Log::info($response);
        return $response;
    }

    /**
     * occupancy update SCTH
     * @param Reservation $reservation [description]
     * @param Transaction $transaction [description]
     * @param array $credentials [description]
     * @return array                   [description]
     * @throws GuzzleException
     */
    public static function occupancy(Team $team, $credentials , $version=2.0 , $transaction_id = null, $dayClosing = false)
    {
        $reservation = new Reservation;
        $scth = new ScthFactory($reservation, $credentials, 5);
        $response = $scth->occupancy($team,number_format($version,1) , $transaction_id, $dayClosing);
        // Log::info($response);
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
        $client = new Client([
            'headers' => ['X-Gateway-APIKey' => $credentials['token']],
        ]);

        // for some reasons the below url is no longer working on check ( it used to work before )
        $url = $this->getbaseUrl() . 'CreateOrUpdateBooking/1.0/createOrUpdateBooking';
        // $url = $this->getbaseUrl() . 'OccupancyUpdate/2.0/occupancyUpdate';
        $statusCode = $client->request('POST', $url, [
            'exceptions' => false,
            'auth' => [
                $credentials['username'],
                $credentials['password']
            ]
        ])->getStatusCode();

        if ($statusCode == 200) {
            return true;
        }
        return false;
    }

    protected function getbaseUrl()
    {
        if (App::environment('production')) {
            return config('scth.production_base_url');
        }

        return config('scth.dev_base_url');
    }

    /**
     * @param $fields
     * @return bool
     */
    public function save($fields): bool
    {
        // TODO: check if already integrated and replace
        $integration = new Integration();
        $integration->key = 'SCTH';
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
