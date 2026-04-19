<?php

namespace App\Integration;

use App\Team;
use App\Term;
use App\Unit;
use App\Customer;
use Carbon\Carbon;
use App\Reservation;
use App\Transaction;
use GuzzleHttp\Client;
use App\IntegrationLog;
use Illuminate\Support\Str;
use App\Jobs\SCTH\OccupancyUpdate;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Integration\Base\BaseIntegration;
use GuzzleHttp\Exception\GuzzleException;
use App\Http\Resources\ReservationManagement\ReservationGuestsResource;

class ScthFactory
{
    const BOOKING = 1;
    const CHECK_IN = 2;
    const CHECK_OUT = 3;
    public $baseUrl = '';
    public $settings = [];
    protected $client;
    /** @var Reservation */
    protected $model;
    protected $type;
    protected $transactionTypeId;
    protected $checkInOrOut;
    protected $log;
    protected $credentials;
    /**
     * Create a new Scth Factory instance.
     *
     * @param Reservation $model
     * @param $credentials
     * @param null $type
     * @param int $transactionTypeId
     */
    public function __construct(Reservation $model, $credentials, $type = null, $transactionTypeId = self::BOOKING, $checkInOrOut = false)
    {

        setSlackWebHook(config('app.info_log_slack_webhook_url'));
        $this->getbaseUrl();

        $this->model = $model;

        $this->type = $type;
        $this->transactionTypeId = $transactionTypeId;
        $this->checkInOrOut = $checkInOrOut;

        $credentials = json_decode($credentials['values'], true);
        $this->client = new Client([
            'headers' => [
                'X-Gateway-APIKey' => $credentials['token'],
                'Content-Type' => 'application/json'
            ],
            'auth' => [
                $credentials['username'],
                $credentials['password']
            ]
        ]);

        if (!is_null($this->model->checked_in) and is_null($this->model->checked_out)) {
            $this->transactionTypeId = self::CHECK_IN;
        }
        if (!is_null($this->model->checked_in) and !is_null($this->model->checked_out)) {
            $this->transactionTypeId = self::CHECK_OUT;
        }

        $this->log = new IntegrationLog();
        $this->log->team_id = $this->model->team_id;
        $this->log->type = SCTH::class;
        $this->credentials = $credentials;
    }

    protected function getbaseUrl()
    {
        if (App::environment('production')) {
            $this->baseUrl = config('scth.production_base_url');
        } else {
            $this->baseUrl = config('scth.dev_base_url');
        }
    }

    /**
     * create Scth Factory
     *
     * @return array [description]
     * @throws GuzzleException
     */
    public function create()
    {
        $url = $this->baseUrl . 'CreateOrUpdateBooking/'.config('scth.version').'/createOrUpdateBooking';
        $request = $this->client->request('POST', $url, [
            'body' => \GuzzleHttp\json_encode($this->format()),
            'exceptions' => true
        ]);

        $respont_array = json_decode($request->getBody()->getContents());
        $response = $this->handelResponse($respont_array);
        $this->addScthReferenceToModel($response['transaction_id']);

        // Logging into integration logs table
        $this->log->response = $response;
        $this->log->action = 1;
        $this->log->status = count($response['errors'])?2:1;
        $this->log->save();
        return $response;
    }

    /**
     * update Scth Factory
     *
     * @return array [description]
     * @throws GuzzleException
     */
    public function update()
    {
        $request = $this->client->request('POST', $this->baseUrl . 'CreateOrUpdateBooking/'.config('scth.version').'/createOrUpdateBooking', [
            'body' => \GuzzleHttp\json_encode($this->format()),
            'exceptions' => true
        ]);
        $response = $this->handelResponse(json_decode($request->getBody()->getContents()));

        $this->log->response = $response;
        $this->log->status = count($response['errors'])?2:1;
        $this->log->action = 2;
        $this->log->save();
        return $response;
    }


    /**
     * cancel Scth Factory
     *
     * @return array [description]
     * @throws GuzzleException
     */
    public function cancel()
    {
        $request = $this->client->request('POST', $this->baseUrl . 'CancelBooking/'.config('scth.version').'/cancelBooking', [
            'body' => \GuzzleHttp\json_encode($this->format()),
            'exceptions' => true
        ]);
        $response = $this->handelResponse(json_decode($request->getBody()->getContents()));

        $this->log->response = $response;
        $this->log->action = 3;
        $this->log->status = count($response['errors'])?2:1;
        $this->log->save();
        return $response;
    }

    /**
     * expense Scth Factory
     *
     * @return array [description]
     * @throws GuzzleException
     */
    public function expense(Transaction $transaction)
    {
        $request = $this->client->request('POST', $this->baseUrl . 'BookingExpense/'.config('scth.version').'/bookingExpense', [
            'body' => \GuzzleHttp\json_encode($this->formatTransaction($transaction)),
            'exceptions' => true
        ]);
        $response = $this->handelResponse(json_decode($request->getBody()->getContents()));

        $this->log->response = $response;
        $this->log->status = count($response['errors'])?2:1;
        $this->log->action = 6;
        $this->log->save();
        return $response;
    }

    public function allExpense($transactions)
    {
        $request = $this->client->request('POST', $this->baseUrl . 'BookingExpense/'.config('scth.version').'/bookingExpense', [
            'body' => \GuzzleHttp\json_encode($this->formatAllTransaction($transactions)),
            'exceptions' => true
        ]);
        $response = $this->handelResponse(json_decode($request->getBody()->getContents()));
        $this->log->response = $response;
        $this->log->status = count($response['errors'])?2:1;
        $this->log->action = 4;
        $this->log->save();
        return $response;
    }


    /**
     * format reservation object
     *
     * @return array [description]
     */
    protected function format()
    {
        $discount = 0;
        $unit = $this->model->unit;
        $unit_number = $unit->unit_number;

        $dailyRoomRate = round($this->model->sub_total / $this->model->nights ,2);
        $vat = round($this->model->vat_total,2);
        $municipalityTax = round($this->model->ewa_total,2);

        if(isset($this->model->sub_total) && !empty($this->model->sub_total)) {
            $grand_total = round($this->model->sub_total + $municipalityTax + $vat - $discount);
        } else{
            $grand_total =0;
        }

        if (isset($this->model->customer->gender))
            if ($this->model->customer->gender == 'male')
                $gender = 1;
            else
                $gender = 2;
        else
            $gender = 0;

        $birthday_date = "0";
        if ($this->isValidDate($this->model->customer->birthday_date)) {
            $date = Carbon::make($this->model->customer->birthday_date);
            $birthday_date = $date->format('Ymd');
        }

        $transaction_id = ($this->type != 1) ? $this->model->scth_reference : "";
        $data = [
            "bookingNo" => strval( $this->model->team_id .  $this->model->number),
            "actualBookingNo" => strval($this->model->number),
            "nationalityCode" => (string)$this->model->customer->country_id,
            "checkInDate" => str_replace("-", "", $this->model->date_in),
            "checkOutDate" => str_replace("-", "", $this->model->date_out),
            "totalDurationDays" => strval($this->model->nights),
            "allotedRoomNo" => strval($unit_number),
            "roomRentType" => strval($this->model->rent_type == 1 ? 1 : 4),
            "dailyRoomRate" => strval($dailyRoomRate),
            "totalRoomRate" => strval(round($this->model->sub_total,2)),
            "vat" => strval($vat),
            "municipalityTax" => strval($municipalityTax),
            "discount" => strval($discount),
            "grandTotal" => strval($grand_total),
            "transactionTypeId" => strval($this->transactionTypeId),
            "gender" => strval($gender),
//            "userId" => $this->credentials['username'],
            "transactionId" => $transaction_id,
            "checkInTime" => strval(0),
            "checkOutTime" => strval(0),
            "customerType" => strval($this->model->customer->customer_type),
            "noOfGuest" => strval($this->model->reservation_guests->count() + 1),
            "roomType" => strval($unit->unit_category->type_id),
//            "isLuxury" => "false",
            "dateOfBirth" => $birthday_date,
            "purposeOfVisit" => strval($this->model->purpose_of_visit),
            "paymentType" => strval(1),
            "noOfRooms" => strval(1),
            "cuFlag" => strval(($this->type == 2) ? 2 : 1),
            'channel' => 'Fandaqah',
        ];

        if (is_null($data['purposeOfVisit']) || empty($data['purposeOfVisit'])) {
            $data['purposeOfVisit'] = Customer::PURPOSE_OTHER;
        }

        if ($this->model->checked_in) {
            $checked_in = Carbon::make($this->model->checked_in);

            $inDate = $checked_in->format('Ymd');
            $inDateTime = $checked_in->format('His');

            $data['checkInTime'] = $inDateTime;
        }

        if ($this->transactionTypeId == self::CHECK_IN and $this->checkInOrOut) {
            $data['cuFlag'] = '1';
        }

        if ($this->model->checked_out) {
            $checked_out = Carbon::make($this->model->checked_out);

            $outDate = $checked_out->format('Ymd');
            $outDateTime = $checked_out->format('His');

            $data['checkOutTime'] = $outDateTime;
        }

        if ($this->transactionTypeId == self::CHECK_OUT and $this->checkInOrOut) {
            $data['cuFlag'] = '1';
        }

        if ($this->type == 3) {
            // in cancel process settings payment type as 0 not applicable
            $data['paymentType'] = strval(0);
            $data = array_merge($data, [
                "cancelReason" => strval(0),
                "cancelWithCharges" => strval(0),
                "chargeableDays" => strval(0)
            ]);
            $data = collect($data)->only([
                'transactionId',
                'cancelReason',
                'cancelWithCharges',
                'chargeableDays',
                'roomRentType',
                'dailyRoomRate',
                'totalRoomRate',
                'vat',
                'municipalityTax',
                'discount',
                'grandTotal',
                'paymentType',
                'cuFlag',
                'channel',
//                "userId",
            ]);

            $data = $data->toArray();
        }

        $this->log->payload = $data;
        // Log::info($data);
        return $data;
    }

    function isValidDate($date, $format = 'Y-m-d')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    /**
     * handel Response with errors
     *
     * @return array [description]
     */
    protected function handelResponse($response)
    {
        $errors = $this->getErrors($response->errorCode);
        return [
            'transaction_id' => $response->transactionId ?? null,
            'correlation_id' => $response->correlationId,
            'errors' => $errors,
        ];
    }

    /**
     * handel errors
     *
     * @return array [description]
     */
    protected function getErrors($response_errors)
    {
        if ($this->type == 1 || $this->type == 2)
            $error_section = 'create_or_update';
        elseif ($this->type == 3)
            $error_section = 'cancel';
        elseif ($this->type == 4)
            $error_section = 'expense';
        elseif ($this->type == 5)
            $error_section = 'occupancy';

        $errors = collect([
            [
                "code" => 1,
                "description" => "Invalid Transaction ID or this Transaction ID not found in SCTH database. It must be of type GUID.",
                "type" => 'cancel'
            ],
            [
                "code" => 2,
                "description" => "Invalid Cancel Reason. It must be numeric and available in lookup list.",
                "type" => 'cancel'
            ],
            [
                "code" => 3,
                "description" => "Invalid Cancel With Charges. It must be numeric and can have only 2 values (1= Yes with Charges, 0 = Cancel without Charges)",
                "type" => 'cancel'
            ],
            [
                "code" => 4,
                "description" => "Invalid Chargeable Days. If Cancelled with Charges then this field should not contain 0.",
                "type" => 'cancel'
            ],
            [
                "code" => 5,
                "description" => "Invalid Room Rent Type. If Cancelled with Charges then this field should not contain 0 It must be numeric and available in lookup list.",
                "type" => 'cancel'
            ],
            [
                "code" => 6,
                "description" => "Invalid Daily Room Rate. If Cancelled with Charges then this field should not contain 0. It must be numeric only If provided.",
                "type" => 'cancel'
            ],
            [
                "code" => 7,
                "description" => "Invalid Total Room Rate. If Cancelled with Charges then this field should not contain 0. It must be numeric only If provided.",
                "type" => 'cancel'
            ],

            [
                "code" => 8,
                "description" => "Invalid VAT. If Cancelled with Charges then this field should not contain 0. It must be numeric (Amount) only If provided.",
                "type" => 'cancel'
            ],

            [
                "code" => 9,
                "description" => "Invalid Municipality Tax. If Cancelled with Charges then this field should not contain 0. It must be numeric (Amount) only If provided.",
                "type" => 'cancel'
            ],
            [
                "code" => 10,
                "description" => " Invalid Discount. If Cancelled with Charges then this field should not contain 0. It must be numeric (Amount) only If provided.",
                "type" => 'cancel'
            ],

            [
                "code" => 11,
                "description" => " Invalid Grand Total. If Cancelled with Charges then this field should not contain 0. It must be numeric (Amount) only If provided.",
                "type" => 'cancel'
            ],
            [
                "code" => 12,
                "description" => " Invalid User Id or UserId not found.",
                "type" => 'cancel'
            ],
            [
                "code" => 13,
                "description" => " Invalid Payment Type value. It must be numeric and available in lookup list. If Cancelled with Charges then this field should not contain 0.",
                "type" => 'cancel'
            ],
            [
                "code" => 14,
                "description" => " This operation is allowed only before Check In.",
                "type" => 'cancel'
            ],
            [
                "code" => 15,
                "description" => " Invalid CU Flag. The value must be 1 or 2.",
                "type" => 'cancel'
            ],
            [
                "code" => 16,
                "description" => " This transaction is already cancelled. If you wish to update this transaction please use CUFlag = 2 for updates.",
                "type" => 'cancel'
            ],


            [
                "code" => 1,
                "description" => "Book No is required and should be numeric only",
                "type" => "create_or_update",
            ],
            [
                "code" => 2,
                "description" => "Invalid Nationality Code. It must be numeric and available in lookup list.",
                "type" => "create_or_update",
            ],
            [
                "code" => 3,
                "description" => "Invalid Check In Date. It must be numeric in YYYYMMDD format",
                "type" => "create_or_update",
            ],
            [
                "code" => 4,
                "description" => "Invalid Check Out Date. It must be numeric in",
                "type" => "create_or_update",
            ],
            [
                "code" => 5,
                "description" => "Invalid Total Duration Days. It must be Numeric",
                "type" => "create_or_update",
            ],
            [
                "code" => 6,
                "description" => "Invalid Room No. It must be Numeric.",
                "type" => "create_or_update",
            ],
            [
                "code" => 7,
                "description" => "Invalid Room Rent Type. It must be numeric and available in lookup list.",
                "type" => "create_or_update",
            ],
            [
                "code" => 8,
                "description" => "Invalid Daily Room Rate. It must be numeric",
                "type" => "create_or_update",
            ],
            [
                "code" => 9,
                "description" => "Invalid Total Room Rate. It must be numeric",
                "type" => "create_or_update",
            ],
            [
                "code" => 10,
                "description" => "Invalid VAT Value. It must be numeric &amp; Amount only.",
                "type" => "create_or_update",
            ],
            [
                "code" => 11,
                "description" => "Invalid Municipality Tax. It must be numeric &amp; Amount only.",
                "type" => "create_or_update",
            ],
            [
                "code" => 12,
                "description" => "Invalid Discount. It must be numeric &amp; Amount only.",
                "type" => "create_or_update",
            ],
            [
                "code" => 13,
                "description" => "Invalid Grand Total. It must be numeric &amp; Amount",
                "type" => "create_or_update",
            ],
            [
                "code" => 14,
                "description" => "Invalid Grand Total. The total must be equal to Sum of (Total Room Rate + VAT + Municipality Tax) – Discount.",
                "type" => "create_or_update",
            ],
            [
                "code" => 15,
                "description" => "Invalid Transaction Type Id. It must be numeric and can be only following values (1 = booking, 2 = CheckIn &amp; 3 = Checkout). Valid values 1, 2 &amp; 3.",
                "type" => "create_or_update",
            ],
            [
                "code" => 16,
                "description" => "Invalid Gender. It must be Numeric and can be only three values (0 = Not Found, 1 = Male , 2 = Female) , Check Lookup for more values if any.",
                "type" => "create_or_update",
            ],
            [
                "code" => 17,
                "description" => "Invalid User Id or UserId not found.",
                "type" => "create_or_update",
            ],
            [
                "code" => 18,
                "description" => "Invalid Transaction No or this Transaction No not found in SCTH database. It must be of type GUID.",
                "type" => "create_or_update",
            ],
            [
                "code" => 19,
                "description" => "Invalid Check In Time. It must be numeric and could hold value 0 if not determined yet in case of Transaction Type ID =1 (Booking), otherwise it Registration Service Service Interface Document should be in following format. HHMMSS",
                "type" => "create_or_update",
            ],
            [
                "code" => 20,
                "description" => "Invalid Check Out Time. It must be numeric and could hold value 0 if not determined yet in case of Transaction Type ID =1 (Booking), otherwise it should be in following format. HHMMSS",
                "type" => "create_or_update",
            ],
            [
                "code" => 21,
                "description" => "Invalid Customer Type. It must be numeric and available in lookup list.",
                "type" => "create_or_update",
            ],
            [
                "code" => 22,
                "description" => "Invalid No Of Guest. It must be numeric.",
                "type" => "create_or_update",
            ],
            [
                "code" => 23,
                "description" => "Invalid Room Type. It must be numeric and available in lookup list.",
                "type" => "create_or_update",
            ],
            [
                "code" => 24,
                "description" => "Invalid Purpose of Visit. It must be numeric and available in lookup list.",
                "type" => "create_or_update",
            ],
            [
                "code" => 25,
                "description" => "Invalid Payment Type value. It must be numeric and available in lookup list.",
                "type" => "create_or_update",
            ],
            [
                "code" => 26,
                "description" => "Invalid “No Of Rooms”. It must be numeric &amp; can contain value from 1 to 99.",
                "type" => "create_or_update",
            ],
            [
                "code" => 27,
                "description" => "Invalid Create or Update Flag. The value must be 1 or 2.",
                "type" => "create_or_update",
            ],
            [
                "code" => 28,
                "description" => "Invalid Date Of Birth. It must be either 0 if not found otherwise it should be always in following format. YYYYMMDD",
                "type" => "create_or_update",
            ],
            [
                "code" => 29,
                "description" => "This TransactionID &amp; TransactionTypeID already exist. If you wish to update the same record please send a request with CUFlag=2. This will be valid in case of new record for Check- In &amp; Checkout only with CUFlag=1 to avoid duplications",
                "type" => "create_or_update",
            ],
            [
                "code" => 30,
                "description" => "Updates on Booking &amp; Check In are not allowed because Check out is created. If transactionTypeID=3 found in our database then Updates (CUFlag=2) will not be allowed on corresponding TrasactionTypeID=1 &amp; TransactionTypeID=2 with same TransationID",
                "type" => "create_or_update",
            ],
            [
                "code" => 31,
                "description" => "Updates on Booking are not allowed because Check-In is created for same booking. If transactionTypeID=2 found only in our database then Updates (CUFlag=2) will not be allowed on corresponding TrasactionTypeID=1 with same TransationID",
                "type" => "create_or_update",
            ],
            [
                "code" => 32,
                "description" => "No updates allowed on this TransactionID because it is already cancelled. If [TransactionCancellationId] is NOT Null then Updates are not allowed.",
                "type" => "create_or_update",
            ],
            [
                "code" => 33,
                "description" => "You are trying to update a record, which does not exist. Please first create a record. It means Transaction ID &amp; Transaction Type ID should first exist in our DB if we are receiving CUFlag =2 for above combination. This is true for booking, check in and check out.",
                "type" => "create_or_update",
            ],
            [
                "code" => 34,
                "description" => "Booking number and Userid combination should be unique. This check is valid if TransactionID is null and only for new booking/checkin/check out entry.",
                "type" => "create_or_update",
            ],
            [
                "code" => 100,
                "description" => "Invalid Credentials. Authentication failed.",
                "type" => "create_or_update",
            ],
            [
                "code" => 101,
                "description" => "Internal Server Error. Please try again later, ifproblem persist, contact with SCTH Support. Hotelsapi.support@scth.gov.sa",
                "type" => "create_or_update",
            ],
            [
                "code" => 1,
                "type" => "expense",
                "description" => "Invalid Transaction ID or this Transaction ID not found in SCTH database. It must be of type GUID."
            ],
            [
                "code" => 2,
                "type" => "expense",
                "description" => "Invalid Expense Date. It must be numeric in YYYYMMDD format"
            ],
            [
                "code" => 3,
                "type" => "expense",
                "description" => "Invalid Item Number. It must be numeric &amp; Unique for each expense Item"
            ],
            [
                "code" => 4,
                "type" => "expense",
                "description" => "ItemNumber not found in SCTH Database. (This case will be valid when CUFlag = 2 )"
            ],
            [
                "code" => 5,
                "type" => "expense",
                "description" => "Invalid Expense Type ID. It must be numeric andavailable in lookup list."
            ],
            [
                "code" => 6,
                "type" => "expense",
                "description" => "Invalid Unit Price. It must be numeric."
            ],
            [
                "code" => 7,
                "type" => "expense",
                "description" => "Invalid Discount. It must be numeric only If provided."
            ],
            [
                "code" => 8,
                "type" => "expense",
                "description" => "Invalid VAT. It must be numeric in Amount only. Itcan contain 0."
            ],
            [
                "code" => 9,
                "type" => "expense",
                "description" => "Invalid Municipality Tax. It must be numeric in Amount only. It can contain 0."
            ],
            [
                "code" => 10,
                "type" => "expense",
                "description" => "Invalid Grand Total. It must be numeric in Amount only."
            ],

            [
                "code" => 12,
                "type" => "expense",
                "description" => "Invalid Payment Type value. It must be numeric and available in lookup list."
            ],

            [
                "code" => 13,
                "type" => "expense",
                "description" => "No checkout data found for Transaction No. Please call this api once the checkout is done."
            ],
            [
                "code" => 14,
                "type" => "expense",
                "description" => "Invalid CU Flag Value. It must be 1=Add, 2=Update"
            ],
            [
                "code" => 15,
                "type" => "expense",
                "description" => "Same Transaction ID Found already found with Item Number. Please send it with CUFlag =2 if you wish to update."
            ],
            [
                "code" => 100,
                "type" => "expense",
                "description" => "Invalid Credentials. Authentication failed."
            ],
            [
                "code" => 101,
                "type" => "expense",
                "description" => "Internal Server Error. Please try again later, if problem persist, contact with SCTH Support. Hotelsapi.support@scth.gov.sa"
            ],

            [
                'code' => 1,
                'description' => "Invalid Update Date. It should be numeric in following format YYYYMMDD.",
                'type' => 'occupancy'
            ],

            [
                'code' => 2,
                'description' => "Invalid Rooms Occupied. It must be numeric only. It can contain 0.",
                'type' => 'occupancy'
            ],

            [
                'code' => 3,
                'description' => "Invalid Rooms Available. It must be numeric only. It can contain 0.",
                'type' => 'occupancy'
            ],

            [
                'code' => 4,
                'description' => "Invalid Rooms Booked. It must be numeric only. It can contain 0.",
                'type' => 'occupancy'
            ],

            [
                'code' => 5,
                'description' => "Invalid “RoomsOnMaintinance”. It must be Registration Service Service Interface Document numeric only. It can contain 0.",
                'type' => 'occupancy'
            ],
            [
                'code' => 6,
                'description' => "Invalid UserId or User Id not found.",
                'type' => 'occupancy'
            ],
            [
                'code' => 100,
                'description' => "Invalid Credentials. Authentication failed.",
                'type' => 'occupancy'
            ],
            [
                'code' => 101,
                'description' => "Internal Server Error. Please try again later, if problem persist, contact with SCTH Support. Hotelsapi.support@scth.gov.sa",
                'type' => 'occupancy'
            ],
        ]);

        return $errors->where('type', $error_section)->whereIn("code", $response_errors)->toArray();
    }

    /**
     * add Scth Reference To reservation Model
     *
     * @param [type] $transaction_id [description]
     * @return boolean
     */
    protected function addScthReferenceToModel($transaction_id)
    {
        if (isset($transaction_id) && !empty($transaction_id)) {
            $this->model->scth_reference = $transaction_id;
            return $this->model->save();
        }
        return false;
    }




    protected function formatTransaction($transaction)
    {
        $unit_price = ltrim($transaction->amount, '-') /100;
        //$vat = round(($unit_price * 5) / 100);
        //$municipalityTax = round(($unit_price * 2.5) / 100);
        $discount = 0;
        //$grand_total = round($this->model->total_price + $vat + $municipalityTax - $discount);
        $grand_total = $unit_price;

        $transaction_id = ($this->type != 1) ? $this->model->scth_reference : '';

        $paymentType = $transaction->meta['payment_type'];

        $payment_types = [
            "cash" => 1,
            "bank-transfer" => 4,
            "mada" => 2,
            "credit" => 2,
        ];

        $type = 0;
        if (isset($payment_types[$paymentType])) {
            $type = $payment_types[$paymentType];
        }
        $expenseTypeId = 0;
        $transactionType = $transaction->meta['type'];
        /** @var Term $term */
        $term = Term::find($transactionType);
        if ($term) {
            $title = $term->getTranslation('name', 'en', 'Not Applicable');
            if ($title and in_array($title, Transaction::scthTypes())) {
                $expenseTypeId = array_search($title, Transaction::scthTypes());
            }
        }

        $data = [
            "transactionId" => $transaction_id,
            'channel' => 'Fandaqah',
//            "userId" => $this->credentials['username'],
            "expenseItems" => [
                "expenseDate" => strval(str_replace("-", "", $transaction->created_at->toDateString())),
                "itemNumber" => strval($transaction->id),
                "expenseTypeId" => "$expenseTypeId",
                "unitPrice" => strval($unit_price),
                "discount" => strval($discount),
                "vat" => "0",
                "municipalityTax" => strval(0),
                "grandTotal" => strval($grand_total),
                "paymentType" => strval($type),
                "cuFlag" => "1",
            ]
        ];
        // Log::info($data);
        return $data;
    }

    protected function formatAccurateTransaction($transaction){

        $paymentType = $transaction->meta['payment_type'];

        $payment_types = [
            "cash" => 1,
            "bank-transfer" => 4,
            "mada" => 2,
            "credit" => 2,
        ];

        $type = 0;
        if (isset($payment_types[$paymentType])) {
            $type = $payment_types[$paymentType];
        }
        $expenseTypeId = 0;
        $transactionType = $transaction->meta['type'];
        /** @var Term $term */
        $term = Term::find($transactionType);
        if ($term) {
            $title = $term->getTranslation('name', 'en', 'Not Applicable');
            if ($title and in_array($title, Transaction::scthTypes())) {
                $expenseTypeId = array_search($title, Transaction::scthTypes());
            }
        }

        $item = new \stdClass();
        $item->expenseDate = strval(str_replace("-", "", $transaction->created_at->toDateString()));
        $item->itemNumber = strval($transaction->id);
        $item->expenseTypeId = strval($expenseTypeId);
        $item->unitPrice = strval(ltrim($transaction->amount, '-') /100);;
        $item->discount = strval(0) ;
        $item->vat = strval(0) ;
        $item->municipalityTax = strval(0) ;
        $item->grandTotal = strval(ltrim($transaction->amount, '-') /100);
        $item->paymentType = strval($type);
        $item->cuFlag = strval(1);

        return $item;
    }

    protected function formatAllTransaction($transactions)
    {
        foreach ($transactions as $transaction){
            $items [] = $this->formatAccurateTransaction($transaction);
        }

        $data = [
            "transactionId" => strval(strtoupper($this->model->scth_reference)),
            'channel' => strval('Fandaqah'),
            "expenseItems" => $items
        ];

        $this->log->payload = $data;
        // Log::info($data);
        return $data;
    }

    /**
     * @param Team $team
     * @param $version ( 1.0 , 2.0 )
     * @return array
     */
    public function occupancy(Team $team , $version , $transaction_id, $dayClosing)
    {
        $request = $this->client->request('POST', $this->baseUrl . 'OccupancyUpdate/'.$version.'/occupancyUpdate', [
            'body' => \GuzzleHttp\json_encode($this->formatOccupancy($team,$transaction_id,$dayClosing)),
            'exceptions' => true
        ]);
        $response = $this->handelResponse(json_decode($request->getBody()->getContents()));

        $this->log->response = $response;
        $this->log->status = count($response['errors'])?2:1;
        $this->log->action = 5;
        $this->log->team_id = $team->id;
        $this->log->save();
        return $response;
    }

    protected function formatOccupancy($team,$transaction_id,$dayClosing)
    {

        $now = $dayClosing ? Carbon::yesterday() :  Carbon::now();
        $date = str_replace("-", "", $now->toDateString());
        $units_ids = Unit::withoutGlobalScope('team_id')->whereTeamId($team->id)->pluck('id')->toArray();
        $rooms_occupied = Reservation::withoutGlobalScope('team_id')
            ->whereIn('unit_id', $units_ids)
            ->whereDateBetween($now)
            ->where('status', '!=', 'canceled')
            ->whereNull('checked_out')
            ->whereNotNull('checked_in')
            ->count();


        $rooms_booked = Reservation::withoutGlobalScope('team_id')
            ->whereIn('unit_id', $units_ids)
            ->whereDateBetween($now)
            ->where('status', '!=', 'canceled')
            ->whereNull('checked_out')
            ->whereNull('checked_in')
            ->count();

        $rooms_on_maintenance = Unit::withoutGlobalScope('team_id')
            ->whereDate('created_at', '<=', $now->toDateString())
            ->whereStatus(Unit::STATUS_UNDER_MAINTENANCE)
            ->whereNull('deleted_at')
            ->whereEnabled(true)
            ->whereTeamId($team->id)
            ->count();

        $total_rooms = Unit::withoutGlobalScope('team_id')
            ->whereTeamId($team->id)
            ->whereEnabled(true)
            ->whereNull('deleted_at')
            ->count();

        $rooms_available = $total_rooms - ($rooms_occupied + $rooms_booked + $rooms_on_maintenance);

        $adults = 0;
        $childrens = 0;
        $guests = ReservationGuestsResource::collection(Reservation::withoutGlobalScope('team_id')
                ->whereIn('unit_id' , $units_ids)
                ->whereDateBetween($now)
                ->where('status' , '!=' , 'canceled')
                ->whereNull('checked_out')
                ->withCount('adults')
                ->withCount('childrens')
                ->get());

        $guests = json_decode(json_encode($guests)) ;
            foreach ($guests as $obj){
                $adults += $obj->adults_count ;
                $childrens += $obj->childrens_count ;
            }

        $totalGuests = $adults + $childrens;
            $client = new Client([
                'headers' => [
                    'x-team' => $team->id,
                ]
            ]);
        $now_formatted = $now->format('Y-m-d');
        $revenueRequest = $client->request('GET', config('app.fandaqah_api_url') . "/reservations/revenue-and-tax-report?per_page=20&date_from=$now_formatted&date_to=$now_formatted&page=1");
        $revenueResponse = json_decode($revenueRequest->getBody()->getContents());
        $totalRevenue = $revenueResponse->flag == 'data_found' ? (float) filter_var($revenueResponse->calculations->leasing_revenue, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) + (float) filter_var($revenueResponse->calculations->total_ewa, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)  + (float) filter_var($revenueResponse->calculations->vat_on_reservation, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0;

        $data = [
            "updateDate" => strval($date),
            "roomsOccupied" => strval($rooms_occupied),
            "roomsAvailable" => strval($rooms_available),
            "roomsBooked" => strval($rooms_booked),
            "roomsOnMaintenance" => strval($rooms_on_maintenance),
            'channel' => "Fandaqah",
            "dayClosing" => $dayClosing  ?  strval('true') : strval('false'),
            "totalRooms" => strval($total_rooms),
            "totalAdults" => strval($adults),
            "totalChildren" => strval($childrens),
            "totalGuests" => strval($totalGuests),
            "totalRevenue" => strval($totalRevenue),
            "transactionId" => strval($transaction_id)
        ];

        // Log::info($data);
        $this->log->payload = $data;
        return $data;
    }
}
