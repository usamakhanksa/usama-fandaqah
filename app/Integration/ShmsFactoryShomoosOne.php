<?php

namespace App\Integration;

use App\Events\ShomosActionCreated;
use App\Integration;
use App\IntegrationLog;
use App\Integration\Base\BaseIntegration;
use App\Integration\SHMS;
use App\Team;
use App\Transaction;
use App\Unit;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\App;
use Psy\Util\Json;

class ShmsFactoryShomoosOne
{
    public $baseUrl = 'https://shomoos.fandaqah.com/';
    public $settings = [];

    protected $client;
    protected $model;
    protected $type;
    protected $credentials;
    protected $log;

    /**
     * Create a new Shms Factory instance.
     *
     * @return void
     */
    public function __construct($model, $credentials, $type = null)
    {
        $this->model = $model;
        $this->type = $type;

        $credentials = json_decode( $credentials['values'], true);
        $this->credentials = $credentials;
        $this->client = new Client([
            'headers' => [
                'Content-Type'=>'application/json'
            ],
        ]);

        $this->log = new IntegrationLog();
        $this->log->team_id = $this->model->team_id;
        $this->log->type = SHMS::class;
    }


    /**
     * create Shms Factory
     *
     * @return array [description]
     */
    public function create()
    {
        $request = $this->client->request('POST', $this->baseUrl . 'check_in', [
            'body'=> \GuzzleHttp\json_encode($this->format()),
            'exceptions' => false
        ]);

        $response_object = json_decode($request->getBody()->getContents());
        $response = $this->handelResponse($response_object);

        $this->log->action = 1;
        $this->log->response = $response_object ?? [];
        $this->log->status = ($response == 200) ? 1:2;
        $this->log->save();

        event(new ShomosActionCreated($this->model, $this->log->status));

        return $response;
    }

    /**
     * update Shms Factory
     *
     * @return array [description]
     */
    public function update()
    {
        $request = $this->client->request('POST', $this->baseUrl . 'check_in/update', [
            'body'=> \GuzzleHttp\json_encode($this->format()),
            'exceptions' => false
        ]);

        $response_object = json_decode($request->getBody()->getContents());
        $response = $this->handelResponse($response_object);

        $this->log->action = 2;
        $this->log->response = $response_object ?? [];
        $this->log->status = ($response == 200) ? 1:2;
        $this->log->save();

        event(new ShomosActionCreated($this->model, $this->log->status));

        return $response;
    }

    /**
     * cancel Shms Factory
     *
     * @return array [description]
     */
    public function checkout()
    {
        $request = $this->client->request('POST', $this->baseUrl . 'check_out', [
            'body'=>\GuzzleHttp\json_encode($this->format()),
            'exceptions' => false
        ]);
        $response_object = json_decode($request->getBody()->getContents());
        $response = $this->handelResponse($response_object);

        $this->log->action = 3;
        $this->log->response = $response_object ?? [];
        $this->log->status = ($response == 200) ? 1:2;
        $this->log->save();
        event(new ShomosActionCreated($this->model, $this->log->status));

        return $response;
    }

    /**
     * transfer reservation from room to anther
     *
     * @return array [description]
     */
    public function transfer($from, $to)
    {
        $request = $this->client->request('POST', $this->baseUrl . 'check_out', [
            'body'=>\GuzzleHttp\json_encode($this->format($from)),
            'exceptions' => false
        ]);

        $request = $this->client->request('POST', $this->baseUrl . 'check_in', [
            'body'=> \GuzzleHttp\json_encode($this->format($to)),
            'exceptions' => false
        ]);

        $response_object = json_decode($request->getBody()->getContents());
        $response = $this->handelResponse($response_object);

        $this->log->action = 8;
        $this->log->response = $response_object ?? [];
        $this->log->status = ($response == 200) ? 1:2;
        $this->log->save();
        event(new ShomosActionCreated($this->model, $this->log->status));

        return $response;
    }

    /**
     * check Out Guest Factory
     *
     * @return array [description]
     */
    public function checkOutGuest($guest)
    {
        $data = [
            "shomoos_id"  => $guest->shomoos_id,
            "username" => $this->credentials['username'],
            "password" => $this->credentials['password'],
            "token" => $this->credentials['token'],
        ];
        $request = $this->client->request('POST', $this->baseUrl . 'dependent/check_out', [
            'body'=>\GuzzleHttp\json_encode($data),
            'exceptions' => false
        ]);

        $response_object = json_decode($request->getBody()->getContents());
        $response = $this->handelResponse($response_object);

        $this->log->payload = $data;
        $this->log->action = 7;
        $this->log->response = $response_object  ?? [];
        $this->log->status = ($response == 200) ? 1:2;
        $this->log->save();
        event(new ShomosActionCreated($this->model, $this->log->status));

        return $response;
    }


    /**
     * format reservation object
     *
     * @return array [description]
     */
    protected function format($room_number = null)
    {
        $data = [
            'model' =>     [
                "shomoos_id"  => $this->model->shomoos_id,
                "room_number"  => $room_number ?? $this->model->unit->unit_number,
                "id" => $this->model->id,
                "team_id" => $this->model->team_id,
                "number" => $this->model->number,
                "date_in"  =>  $this->model->date_in,
                "date_out"  =>  $this->model->date_out,
                "total_price"  =>  $this->model->total_price ,
                "check_in_type"  =>  1 ,
                "visit_reason_id"  =>  $this->model->purpose_of_visit ,
                "nights"  =>  $this->model->nights,
                "customer" => $this->model->customer->toArray(),
                "dependents" => $this->model->reservation_guests->toArray(),
                "room" => $this->model->unit->toArray()
            ],
            "username" => $this->credentials['username'],
            "password" => $this->credentials['password'],
            "token" => $this->credentials['token'],
        ];
        $this->log->payload = $data['model'];
        return $data;
    }

    /**
     * handel Response with errors
     *
     * @return array [description]
     */
    protected function handelResponse($response)
    {
        $errors = [];
        return $response->status ?? 500;
    }

    /**
     * handel errors
     *
     * @return array [description]
     */
    protected function getErrors($response_errors)
    {
        return [];
    }

}
