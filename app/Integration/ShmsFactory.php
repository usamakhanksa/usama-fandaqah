<?php

namespace App\Integration;

use App\Team;
use App\Guest;
use Carbon\Carbon;
use App\Reservation;
use GuzzleHttp\Client;
use App\IntegrationLog;
use App\Events\ShomosActionCreated;

class ShmsFactory
{
    public $baseUrl;
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
        $this->baseUrl = env('SHOMOOS_SERVICE_URL');
        $credentials = json_decode($credentials['values'], true);
        $this->credentials = $credentials;
        $this->client = new Client([
            'headers' => [
                'Content-Type' => 'application/json'
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
        try {
            // just hit api on new shomos project
            $initiateRequest = $this->client->request('POST', $this->baseUrl . 'check-in', [
                'body' => \GuzzleHttp\json_encode([
                    'RequestId' => $this->model->id,
                    'TeamId' => $this->model->team_id,
                    // 'DateOfCheckIn' => strval(Carbon::parse($this->model->checked_in)->format('m/d/Y h:i A')),
                    'DateOfCheckIn' => strval(Carbon::now()->format('m/d/Y h:i A')),
                    'DateOfCheckOut' => strval(Carbon::parse($this->model->date_out_time)->format('m/d/Y h:i A')),
                    'IdentityNum' => strval($this->model->customer->id_number),
                    'IdentityType' => strval('T1_' . $this->model->customer->id_type),
                    'Nationality' => strval('T2_' . $this->model->customer->country_id),
                    'DateOfBirth' => strval(Carbon::parse($this->model->customer->birthday_date)->format('m/d/Y')),
                    'VersionNumber' => strval($this->model->customer->id_serial_number),
                    'RoomNumber' => strval($this->model->unit->unit_number),
                    'HaveEscorts' => false,
                    'EscortDetails' => [],
                    'StayPeriod' => $this->model->nights,
                    'credentials' => $this->credentials,
                    'shomoos_api_private_access' => env('SHOMOOS_API_PRIVATE_ACCESS'),
                    'enable_integrations_logs' => $this->model->team->enable_integrations_logs ? $this->model->team->enable_integrations_logs : 0
                ]),
                'exceptions' => false
            ]);

            $response = json_decode($initiateRequest->getBody()->getContents());

            //    if($response && $response->success && $this->model->team->enable_integrations_logs){
            if ($response && $response->success) {
                $data = [
                    'reservation_id' => $this->model->id,
                    'message' => 'Checkin message sent to shomos v2'
                ];
                $this->log->action = 1;
                $this->log->payload = [];
                $this->log->response = $data;
                $this->log->subject_id = null;
                $this->log->subject_type = null;
                $this->log->status = 1;
                $this->log->save();
            }
        } catch (\Throwable $th) {
            $data = [
                'reservation_id' => $this->model->id,
                'message' => $th->getMessage()
            ];
            $this->log->action = 1;
            $this->log->payload = [];
            $this->log->response = $data;
            $this->log->subject_id = null;
            $this->log->subject_type = null;
            $this->log->status = 3;
            $this->log->save();
        }
    }

    /**
     * update Shms Factory
     *
     * @return array [description]
     */
    public function update()
    {
        try {
            // just hit api on new shomos project
            $initiateRequest = $this->client->request('POST', $this->baseUrl . 'check-in/update', [
                'body' => \GuzzleHttp\json_encode([
                    'Accom_Trx_MainId' => $this->model->shomoos_id,
                    'DateOfCheckOut' => strval(Carbon::parse($this->model->date_out_time)->format('m/d/Y h:i A')),
                    'RoomNumber' => strval($this->model->unit->unit_number),
                    'TeamId' => $this->model->team_id,
                    'RequestId' => $this->model->id,
                    'credentials' => $this->credentials,
                    'shomoos_api_private_access' => env('SHOMOOS_API_PRIVATE_ACCESS'),
                    'enable_integrations_logs' => $this->model->team->enable_integrations_logs ? $this->model->team->enable_integrations_logs : 0
                ]),
                'exceptions' => false
            ]);

            $response = json_decode($initiateRequest->getBody()->getContents());

            if ($response && $response->success && $this->model->team->enable_integrations_logs) {
                $data = [
                    'reservation_id' => $this->model->id,
                    'message' => 'Update Checkin message sent to shomos v2'
                ];
                $this->log->action = 2;
                $this->log->payload = [];
                $this->log->response = $data;
                $this->log->subject_id = null;
                $this->log->subject_type = null;
                $this->log->status = 1;
                $this->log->save();
            }
        } catch (\Throwable $th) {
            $data = [
                'reservation_id' => $this->model->id,
                'message' => $th->getMessage()
            ];
            $this->log->action = 2;
            $this->log->payload = [];
            $this->log->response = $data;
            $this->log->subject_id = null;
            $this->log->subject_type = null;
            $this->log->status = 3;
            $this->log->save();
        }
    }

    // Checkout And Rating Guest
    public function checkout()
    {
        try {
            // just hit api on new shomos project
            $initiateRequest = $this->client->request('POST', $this->baseUrl . 'check-out', [
                'body' => \GuzzleHttp\json_encode([
                    'Accom_Trx_MainId' => $this->model->shomoos_id,
                    'DateOfCheckOut' => strval(Carbon::parse($this->model->checked_out)->format('m/d/Y h:i A')),
                    'Rating' => 3,
                    'TeamId' => $this->model->team_id,
                    'RequestId' => $this->model->id,
                    'credentials' => $this->credentials,
                    'shomoos_api_private_access' => env('SHOMOOS_API_PRIVATE_ACCESS'),
                    'enable_integrations_logs' => $this->model->team->enable_integrations_logs ? $this->model->team->enable_integrations_logs : 0
                ]),
                'exceptions' => false
            ]);

            $response = json_decode($initiateRequest->getBody()->getContents());

            if ($response && $response->success && $this->model->team->enable_integrations_logs) {
                $data = [
                    'reservation_id' => $this->model->id,
                    'message' => 'Checkout message sent to shomos v2'
                ];
                $this->log->action = 3;
                $this->log->payload = [];
                $this->log->response = $data;
                $this->log->subject_id = null;
                $this->log->subject_type = null;
                $this->log->status = 1;
                $this->log->save();

                // $fetch_guests = Guest::where('team_id', $this->model->team_id)->where('reservation_id', $this->model->id)->get();
                // if (count($fetch_guests)) {
                //     foreach ($fetch_guests as $guest) {
                //         $guest->shomoos_escort_id = null;
                //         $guest->reservation_id = null;
                //         $guest->save();
                //     }
                // }
            }
        } catch (\Throwable $th) {
            $data = [
                'reservation_id' => $this->model->id,
                'message' => $th->getMessage()
            ];
            $this->log->action = 3;
            $this->log->payload = [];
            $this->log->response = $data;
            $this->log->subject_id = null;
            $this->log->subject_type = null;
            $this->log->status = 3;
            $this->log->save();
        }
    }

    /**
     * Add new Escort
     * BTW : $escort object is injected to constructor function
     *
     * @return array [description]
     */
    public function insertEscort($reservation_id)
    {
        try {
            // just hit api on new shomos project
            $initiateRequest = $this->client->request('POST', $this->baseUrl . 'insert-escort', [
                'body' => \GuzzleHttp\json_encode([
                    'VersionNumber' => $this->model->id_serial_number,
                    'Nationality' => strval('T2_' . $this->model->country_id),
                    'IdentityNum' => strval($this->model->id_number),
                    'IdentityType' => strval('T1_' . $this->model->id_type),
                    'DateOfBirth' => strval(Carbon::parse($this->model->birthday_date)->format('m/d/Y')),
                    // 'CheckInDate' => strval(Carbon::parse($this->model->escortFromPivot->created_at)->format('m/d/Y h:i A')),
                    'CheckInDate' => strval(Carbon::parse(Carbon::today()->addDay())->format('m/d/Y h:i A')),
                    'MainGuestID' => $this->model->reservation->shomoos_id,
                    'TeamId' => $this->model->reservation->team_id,
                    'RequestId' => $this->model->id,
                    'credentials' => $this->credentials,
                    'shomoos_api_private_access' => env('SHOMOOS_API_PRIVATE_ACCESS'),
                    'ReservationId' => $reservation_id,
                    'enable_integrations_logs' => $this->model->reservation && $this->model->reservation->team->enable_integrations_logs ? $this->model->reservation->team->enable_integrations_logs : 0
                ]),
                'exceptions' => false
            ]);

            $response = json_decode($initiateRequest->getBody()->getContents());

            if ($response && $response->success && $this->model->reservation->team->enable_integrations_logs) {
                $data = [
                    'reservation_id' => $this->model->reservation_id,
                    'message' => 'Insert Escort signal sent to shomoos v2'
                ];
                $this->log->action = 11;
                $this->log->payload = [];
                $this->log->response = $data;
                $this->log->subject_id = null;
                $this->log->subject_type = null;
                $this->log->status = 1;
                $this->log->save();
            }
        } catch (\Throwable $th) {
            $data = [
                'reservation_id' => $this->model->reservation_id,
                'message' => $th->getMessage()
            ];
            $this->log->action = 11;
            $this->log->payload = [];
            $this->log->response = $data;
            $this->log->subject_id = null;
            $this->log->subject_type = null;
            $this->log->status = 3;
            $this->log->save();
        }
        return;
    }

    public function deleteEscort($escort)
    {
        try {
            // just hit api on new shomos project
            $initiateRequest = $this->client->request('POST', $this->baseUrl . 'delete-escort', [
                'body' => \GuzzleHttp\json_encode([
                    'EscortId' => $escort->shomoos_escort_id,
                    'ReservationId' => $escort->reservation_id,
                    'TeamId' => $escort->team_id,
                    'RequestId' => $escort->id,
                    'credentials' => $this->credentials,
                    'shomoos_api_private_access' => env('SHOMOOS_API_PRIVATE_ACCESS'),
                    'enable_integrations_logs' => $escort->reservation && $escort->reservation->team->enable_integrations_logs ? $escort->reservation->team->enable_integrations_logs : 0
                ]),
                'exceptions' => false
            ]);

            $response = json_decode($initiateRequest->getBody()->getContents());

            if ($response && $response->success && $escort->reservation->team->enable_integrations_logs) {
                $data = [
                    'reservation_id' => $escort->reservation_id,
                    'message' => 'Delete Escort signal sent to shomoos v2'
                ];
                $this->log->action = 13;
                $this->log->payload = [];
                $this->log->response = $data;
                $this->log->subject_id = null;
                $this->log->subject_type = null;
                $this->log->status = 1;
                $this->log->save();
            }
        } catch (\Throwable $th) {
            $data = [
                'reservation_id' => $escort->reservation_id,
                'message' => $th->getMessage()
            ];
            $this->log->action = 13;
            $this->log->payload = [];
            $this->log->response = $data;
            $this->log->subject_id = null;
            $this->log->subject_type = null;
            $this->log->status = 3;
            $this->log->save();
        }
        return;
    }

    public function updateEscort($escort)
    {
        try {
            // just hit api on new shomos project
            $initiateRequest = $this->client->request('POST', $this->baseUrl . 'update-escort', [
                'body' => \GuzzleHttp\json_encode([
                    'MainEscortID' => $escort->shomoos_escort_id,
                    'MainGuestID' => $escort->shomoos_id,
                    'DateOfCheckIn' => strval(Carbon::parse($this->model->escortFromPivot->created_at)->format('m/d/Y h:i A')),
                    'TeamId' => $escort->team_id,
                    'RequestId' => $escort->id,
                    'credentials' => $this->credentials,
                    'shomoos_api_private_access' => env('SHOMOOS_API_PRIVATE_ACCESS'),
                    'ReservationId' => $escort->reservation_id,
                    'enable_integrations_logs' => $escort->reservation && $escort->reservation->team->enable_integrations_logs ? $escort->reservation->team->enable_integrations_logs : 0
                ]),
                'exceptions' => false
            ]);

            $response = json_decode($initiateRequest->getBody()->getContents());

            if ($response && $response->success && $escort->reservation->team->enable_integrations_logs) {
                $data = [
                    'reservation_id' => $escort->reservation_id,
                    'message' => 'Update Escort signal sent to shomoos v2'
                ];
                $this->log->action = 12;
                $this->log->payload = [];
                $this->log->response = $data;
                $this->log->subject_id = null;
                $this->log->subject_type = null;
                $this->log->status = 1;
                $this->log->save();
            }
        } catch (\Throwable $th) {
            $data = [
                'reservation_id' => $escort->reservation_id,
                'message' => $th->getMessage()
            ];
            $this->log->action = 12;
            $this->log->payload = [];
            $this->log->response = $data;
            $this->log->subject_id = null;
            $this->log->subject_type = null;
            $this->log->status = 3;
            $this->log->save();
        }
        return;
    }

    /**
     * transfer reservation from room to anther
     *
     * @return array [description]
     */
    public function transfer($from, $to)
    {
        $request = $this->client->request('POST', $this->baseUrl . 'check_out', [
            'body' => \GuzzleHttp\json_encode($this->format($from)),
            'exceptions' => false
        ]);

        $request = $this->client->request('POST', $this->baseUrl . 'check_in', [
            'body' => \GuzzleHttp\json_encode($this->format($to)),
            'exceptions' => false
        ]);

        $response_object = json_decode($request->getBody()->getContents());
        $response = $this->handelResponse($response_object);

        $this->log->action = 8;
        $this->log->response = $response_object ?? [];
        $this->log->status = ($response == 200) ? 1 : 2;
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
            'shomoos_id' => $guest->shomoos_id,
            'username' => $this->credentials['username'],
            'password' => $this->credentials['password'],

        ];
        $request = $this->client->request('POST', $this->baseUrl . 'dependent/check_out', [
            'body' => \GuzzleHttp\json_encode($data),
            'exceptions' => false
        ]);

        $response_object = json_decode($request->getBody()->getContents());
        $response = $this->handelResponse($response_object);

        $this->log->payload = $data;
        $this->log->action = 7;
        $this->log->response = $response_object ?? [];
        $this->log->status = ($response == 200) ? 1 : 2;
        $this->log->save();
        event(new ShomosActionCreated($this->model, $this->log->status));

        return $response;
    }

    /**
     * format reservation object
     *
     * @return array [description]
     */
    protected function format($room_number = null, $type = 'create')
    {
        switch($type) {
            case 'checkout':
                $data = [
                    'Accom_Trx_MainId' => $this->model->shomoos_id,
                    'DateOfCheckOut' => $this->model->date_out_time,
                    //mytodo get real rating
                    'Rating' => 3,
                    'userid' => $this->credentials['userid'],
                    'branchcode' => $this->credentials['branchcode'],
                    'branchsecret' => $this->credentials['branchsecret'],

                ];
                break;
            case 'update':
                $data = [
                    'Accom_Trx_MainId' => $this->model->shomoos_id,
                    'DateOfCheckOut' => $this->model->date_out_time,
                    'RoomNumber' => $room_number ?? $this->model->unit->unit_number,
                    'userid' => $this->credentials['userid'],
                    'branchcode' => $this->credentials['branchcode'],
                    'branchsecret' => $this->credentials['branchsecret'],

                ];
                break;
            case 'insert-escort':
                $data = [
                    'MainGuestID' => $this->model->shomoos_id,
                    'DateOfCheckIn' => $this->model->reservation->date_in_time,
                    'IdentityType' => 'T1_4',
                    'Nationality' => 'T2_104',
                    'DateOfBirth' => $this->model->birthday_date,
                    'VersionNumber' => 0,
                    'IdentityNum' => $this->model->id_number,
                    'userid' => $this->credentials['userid'],
                    'branchcode' => $this->credentials['branchcode'],
                    'branchsecret' => $this->credentials['branchsecret']
                ];
                break;
            default:
                $data = [
                    'DateOfCheckIn' => Carbon::parse($this->model->date_in_time)->format('m/d/Y H:i A'),
                    'DateOfCheckOut' => Carbon::parse($this->model->date_out_time)->format('m/d/Y H:i A'),
                    'IdentityNum' => $this->model->customer->toArray()['id_number'],
                    'IdentityType' => 'T1_4',
                    'Nationality' => 'T2_104',
                    'DateOfBirth' => Carbon::parse($this->model->customer->toArray()['birthday_date'])->format('m/d/Y'),
                    'VersionNumber' => 0,
                    'RoomNumber' => $room_number ?? $this->model->unit->unit_number,
                    'HaveEscorts' => count($this->model->reservation_guests) ? true : false,
                    'EscortDetails' => $this->model->reservation_guests->toArray(),
                    'RequestId' => $this->model->id,
                    // "check_in_type"  =>  1 ,
                    // "visit_reason_id"  =>  $this->model->purpose_of_visit ,
                    // "nights"  =>  $this->model->nights,
                    'userid' => $this->credentials['userid'],
                    'branchcode' => $this->credentials['branchcode'],
                    'branchsecret' => $this->credentials['branchsecret'],

                ];
                break;
        }

        $this->log->payload = $data;
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
