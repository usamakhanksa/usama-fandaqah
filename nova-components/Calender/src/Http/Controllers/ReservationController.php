<?php

namespace SureLab\Calender\Http\Controllers;

use Storage;
use App\Team;
use App\Term;
use App\Unit;
use stdClass;
use App\Guest;
use Exception;
use App\Source;
use App\Wallet;
use App\Company;
use App\Service;
use app\Setting;
use App\Customer;
use App\Highlight;
use Carbon\Carbon;
use App\Promissory;
use App\ServiceLog;
use App\Integration;
use App\Reservation;
use App\TeamCounter;
use App\Transaction;
use App\SpecialPrice;
use App\UnitCategory;
use App\UnitCleaning;
use Ramsey\Uuid\Uuid;
use GuzzleHttp\Client;
use App\Objects\Basket;
use App\OtaReservation;
// imports settings class
use App\Objects\Invoice;
use Carbon\CarbonPeriod;
use App\GroupReservation;
use App\ServicesCategory;
use App\Handlers\Settings;
use App\InvoiceCreditNote;
use App\OnlineReservation;
use App\ReservationGuests;
use App\ReservationInvoice;
use App\ReservationService;
use Illuminate\Support\Str;
use App\Events\GuestDeleted;
use App\Helpers\ObjectArray;
use Illuminate\Http\Request;
use App\Helpers\SettingStore;
use App\Jobs\UnlinkUnusedXML;
use App\ReservationMessageLog;
use function Complex\negative;
use App\Helpers\ResponseHelper;
use App\Broadcasting\SMSChannel;
use App\ReservationServiceMapper;
use App\Services\ContractService;
use Laravelista\Comments\Comment;
use App\Events\ReservationCheckIn;
use App\Events\ReservationCreated;
use App\Events\ReservationDeleted;
use App\Events\ReservationUpdated;
use App\Jobs\SCTH\OccupancyUpdate;
use Illuminate\Support\Facades\DB;
use App\Events\ReservationCanceled;
use App\Events\ReservationCheckout;
use App\Events\ShomoosDeleteEscort;
use App\Events\ShomoosInsertEscort;
use App\Events\ShomoosUpdateEscort;
use Illuminate\Support\Facades\Log;
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Spatie\QueryBuilder\QueryBuilder;
use App\GroupReservationBalanceMapper;
use App\Http\Resources\SourceResource;
use App\Events\ShomosReservationUpdated;
use App\Jobs\SCTH\CancelCheckoutBooking;
use Faker\Provider\Uuid as ProviderUuid;
use App\Events\OnlineReservationConfirmed;
use App\Notifications\CustomeNotification;
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Mail\Customer\ReservationPaymentLinkEmail;
use App\Services\ZATCA\Phase2\GenerateOrReportInvoice;
use App\Http\Resources\Reports\ReservationSourcesResource;
use SureLab\Calender\Http\Requests\UpdateReservationPrices;
use App\Events\ShomosResendReservationCheckInAfterCancelCheckout;
use App\Http\Resources\ReservationManagement\ReservationResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use App\Http\Resources\ReservationManagement\CompanyReservationResource;
use App\Http\Resources\ReservationManagement\CompanyGroupReservationResource;
use App\Http\Resources\ReservationManagement\CompanyReservationsHistoryResource;
use App\Http\Resources\ReservationManagement\CustomerReservationsHistoryResource;

class ReservationController extends Controller
{
    public function messages(Reservation $reservation, Request $request)
    {
        /** @var Customer $customer */
        $customer = $reservation->customer;

        switch ($request->get('data')['type']) {
            case 'mail':
                $message = $request->get('data')['email_message'];
                // $customer->notify(new CustomeNotification($reservation, ['mail'], $message, $request->get('data')['email_subject']));
                // $log = ReservationMessageLog::create(['type'   =>  'mail', 'message'    =>  $message, 'reservation_id'  =>  $reservation->id]);
                $data = [
                    'to' => $reservation->customer->email,
                    'reply_to' => null,
                    'subject' => $request->get('data')['email_subject'],
                    'html' => view('email.reservation_mail')->with([
                        'messageLine' => $message,
                        'team_name' => $reservation->team->name
                    ])->render(),
                ];
                $send = sendMailUsingMailMicroservice($data);

                $log = new ReservationMessageLog();
                $log->type = 'mail';
                $log->message = $message;
                $log->reservation_id = $reservation->id;
                $log->save();
                break;
            case 'sms':
                $integartion = Integration::where('key', 'fsms')->where('team_id', $reservation->team_id)->first();
                if ($integartion) {
                    $credentials =  json_decode($integartion->values, true);
                    $check = new \App\Integration\Fsms();
                    if($check->check($credentials)){
                        // now check the balance
                        $client = new Client();
                        $response = $client->request('POST', env('FSMS_URL') . 'api/get-balance', [
                            'form_params' => [
                                'Api_key' => $credentials['appSid']
                            ]
                        ]);
                        $body = json_decode($response->getBody()->getContents(), true);
                        if ($body['status'] == "success" && $body['balance'] > 0 ){
                            $message = $request->get('data')['sms_message'];
                            $customer->notify(new CustomeNotification($reservation, [SMSChannel::class], $message));
                            // $log = ReservationMessageLog::create(['type'   =>  'sms', 'message'    =>  $message, 'reservation_id'  =>  $reservation->id]);
                            $log = new ReservationMessageLog();
                            $log->type = 'sms';
                            $log->message = $message;
                            $log->reservation_id = $reservation->id;
                            $log->save();
                            return response()->json([
                                'data' =>  [
                                    'check' => true,
                                    'type' => 'sms',
                                ]
                            ]);

                        }elseif($body['status'] == "success" && $body['balance'] == 0 ){
                            return response()->json([
                                'data' =>  [
                                    'check' => false,
                                    'type' => 'balance',
                                ]
                            ]);
                            break;
                        }else{
                            return response()->json([
                                'data' =>  [
                                    'check' => false,
                                    'type' => 'sms',
                                ]
                            ]);
                            break;
                        }

                    }else{
                        return response()->json([
                            'data' =>  [
                                'check' => false,
                                'type' => 'sms',
                            ]
                        ]);
                        break;
                    }

                }

                $message = $request->get('data')['sms_message'];
                $customer->notify(new CustomeNotification($reservation, [SMSChannel::class], $message));
                // $log = ReservationMessageLog::create(['type'   =>  'sms', 'message'    =>  $message, 'reservation_id'  =>  $reservation->id]);
                $log = new ReservationMessageLog();
                $log->type = 'sms';
                $log->message = $message;
                $log->reservation_id = $reservation->id;
                $log->save();
                break;
        }
    }

    public function cancelOnline()
    {
        $reservation = OnlineReservation::find(\request('reservation_id'));
        $reservation->cancel();
    }

    public function confirmOnline()
    {

        $reservation = Reservation::with('unit', 'unit.unit_category', 'customer')->find(\request('reservation_id'));

        $response = $reservation->confirmOnline();
        //mytravel confirming reservation
        if(($reservation->source->name == 'dyafa booking engine' || $reservation->source->name == 'محرك حجوزات ضيافا')) {

            $mytravel = $this->confirmReservationMyTravel($reservation);
        }

        //end mytravel confirming reservation



        if (isset($response['status']) && $response['status'] == 'converted_to_confirmed_reservation') {

            event(new OnlineReservationConfirmed($reservation));

            return response()->json([
                'success' => true,
                'status' => 'converted_to_confirmed_reservation',
            ]);
        }

        if (isset($response['status']) && $response['status'] == 'unit_is_not_available') {
            return response()->json([
                'success' => false,
                'status' => 'unit_is_not_available',
            ]);
        }


        event(new OnlineReservationConfirmed($reservation));

        return response()->json([
            'success' => true,
            'reservation' => $reservation,
        ]);
    }

//mytravel confirming reservation
public function confirmReservationMyTravel($reservation){
    $source_num = $reservation->source_num;

    $data = [
        'booking_id' => $source_num,
    ];


    $url = env('MYTRAVEL_API_URL') . '/api/hotel/booking/confirm';
    $key = env('MY_TRAVEL_KEY');
    $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 400,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'key: ' .$key
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            return json_decode($response);
}
//end mytravel confirming reservation

    /**
     * Add comment to reservation
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function comment(Request $request)
    {
        $model = $request->commentable_type::findOrFail($request->commentable_id);

        $commentClass = config('comments.model');
        $comment = new $commentClass;
        $comment->commenter()->associate(auth()->user());
        $comment->commentable()->associate($model);
        $comment->comment = preg_replace('~^\h+|\h+$|(\R){2,}|(\s){2,}~m', '$1$2', $request->message);
        $comment->approved = !config('comments.approval_required');
        $comment->save();

        return response()->json($request);
    }

    public function updateComment(Request $request)
    {

        $commentClass = config('comments.model');
        $comment = $commentClass::find($request->comment_id);
        $comment->comment = preg_replace('~^\h+|\h+$|(\R){2,}|(\s){2,}~m', '$1$2', $request->comment);
        if ($comment->save()) {
            $response = [
                'success' => true,
                'message' => 'comment updated successfully'
            ];

            return response()->json($response);
        }


        $response = [
            'success' => false,
            'message' => 'wrong update process'
        ];

        return response()->json($response);
    }

    public function deleteComment(Request $request, $id)
    {
        $commentClass = config('comments.model');
        $comment = $commentClass::find($id);
        if ($comment->delete()) {
            $response = [
                'success' => true,
                'message' => 'comment deleted successfully'
            ];

            return response()->json($response);
        }

        $response = [
            'success' => false,
            'message' => 'wrong delete process'
        ];

        return response()->json($response);
    }

    /**
     * Add Escort
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeGuest(Request $request)
    {

        $guestRequest = $request['guest'];

        // if guest has a reservation id that is totally different from current reservation id and the process type is add
        // we will prevent the guest from add

        if ((isset($guestRequest['reservation_id']) && !is_null($guestRequest['reservation_id'])) && !is_null($request->current_reservation_id) && ($guestRequest['reservation_id'] != $request->current_reservation_id) && $request->processType == 'add') {
            return response()->json(['success' => false, 'message' => __('Escort is already attached on another reservation')]);
        }

        /**
         * Unique validation for guest id number
         */
        if (isset($guestRequest['id_number']) && !isset($guestRequest['id'])) {
            $validator = Guest::validate($guestRequest, isset($guestRequest['id']) ? $guestRequest['id'] : null, $guestRequest['customer_id']);
            if ($validator->fails()) {
                $response = [
                    'success' => false,
                    'message' => 'Id number is taken , it must be unique'
                ];
                return response()->json($response);
            }

        }

        $reservation = Reservation::find($request->current_reservation_id);

        $guest = Guest::updateOrCreate(['id' => isset($guestRequest['id']) ? $guestRequest['id'] : null], [
            'name' => $guestRequest['name'],
            'customer_id' => $reservation->customer_id,
            'shomoos_id' => $reservation->shomoos_id,
            'reservation_id' => $reservation->id,
            'birthday_date' => isset($guestRequest['birthday_date']) ? Carbon::parse($guestRequest['birthday_date'])->format('Y-m-d') : null,
            'team_id' => $reservation->team_id,
            'relation_type' => $guestRequest['relation_type'] ?? null,
            'id_number' => $guestRequest['id_number'] ?? null,
            'gender' => isset($guestRequest['gender']) ? $guestRequest['gender'] : null,
            'customer_type' => isset($guestRequest['customer_type']) ? $guestRequest['customer_type'] : null,
            'id_type' => isset($guestRequest['id_type']) ? $guestRequest['id_type'] : null,
            'visa_number' => isset($guestRequest['visa_number']) ? $guestRequest['visa_number'] : null,
            'id_serial_number' => isset($guestRequest['id_serial_number']) ? $guestRequest['id_serial_number'] : null,
            'country_id' => isset($guestRequest['country_id']) ? $guestRequest['country_id'] : null
        ]);


        if (!$guest->customers()->where('customer_id', $guestRequest['customer_id'])->where('reservation_id', $request->current_reservation_id)->exists()) {
            $guest->customers()->attach($guestRequest['customer_id'], [
                'reservation_id' => $request->current_reservation_id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $guest = Guest::with('reservation', 'escortFromPivot')->find($guest->id);
        event(new ReservationUpdated($reservation));
        if ($reservation->team->integration_shms && !$guest->shomoos_escort_id) {
            event(new ShomoosInsertEscort($reservation->id, $guest));
        } else {
            event(new ShomoosUpdateEscort($reservation->id, $guest));
        }
        event(new ShomosReservationUpdated($reservation));

        return response()->json(['success' => true, 'message' => 'Guest went successfully']);
    }

    /**
     * Delete Escort
     * @param Request $request
     * @throws \Exception
     */
    public function deleteGuest($id, Reservation $reservation)
    {
        $guest = Guest::find($id);

        if ($reservation->shomoos_id && $reservation->team->integration_shms && $guest->shomoos_escort_id) {
            event(new ShomoosDeleteEscort($guest));
        }

        $guest->reservation_id = null;
        $guest->save();

        // detach from customer_guest_reservation table;
        $guest->customers()->wherePivot('reservation_id', $reservation->id)->detach();

        // if ($reservation->team->integration_shomoos_version_one && $reservation->shomoos_id) {
        //     event(new GuestDeleted($new_guest, $team_id));
        // }

        if ($reservation->scth_reference) {
            event(new ReservationUpdated($reservation));
        }
    }


    /**
     * Get all reservations
     * @return \Illuminate\Http\JsonResponse
     */
    //    public function index()
    //    {
    //        $reservations = Reservation::with('customer', 'unit', 'creator', 'comments')->get()->toArray();
    //
    //        return response()->json($reservations);
    //    }

    /**
     * get service list
     * @return \Illuminate\Http\JsonResponse
     */
    public function services()
    {
        $services = ServicesCategory::select(['id', 'name'])
            ->with(
                array(
                    'servicesForReservation' => function ($query) {
                        $query->select('services_category_id', 'team_id', 'id', 'deleted_at', 'name as value', 'price', 'name');
                    }
                )
            )
            ->orderBy('order', 'asc')
            ->where('status', 1)
            ->where('show_in_reservation', 1)
            ->get();

        // @todo  better way for this crap
        foreach ($services as $service) {
            $service->text = $service->getTranslation('name', \App::getLocale());
            foreach ($service->servicesForReservation as $s) {
                $s->text = $s->getTranslation('name', \App::getLocale());
            }
        }

        return response()->json($services);
    }

    public function checkServicesCategories()
    {
        return ServicesCategory::where('team_id', auth()->user()->current_team_id)->count();
    }

    /**
     * Save Transaction for reservation
     * @param Request $request
     */
    public function storeTransaction(Request $request)
    {
        $model = Reservation::with(['wallet', 'group_reservation'])->find($request->id);

        $current_time = date('H:i');
        $current_date = date('Y-m-d');
        $combinedTransactionDate = date('Y-m-d H:i', strtotime("$current_date $current_time"));

        if ($request['transaction_date'] != null and $request['transaction_date'] != 'null' and $request['transaction_date'] != '') {
            $transactionDate = $request['transaction_date'];
        } else {
            $transactionDate = $combinedTransactionDate;
        }

        $meta = [
            "category" => $request->meta['category'],
            "statement" => $request->meta['statement'],
            "type" => $request->meta['type'],
            "payment_type" => $request->meta['payment_type'],
            "note" => $request->meta['note'],
            "reference" => $request->meta['reference'],
            "date" => $transactionDate,
            "from" => $request->meta['from'],
            "employee" => $request->meta['employee'],
            "person_in_charge" => isset($request->meta['person_in_charge']) ? $request->meta['person_in_charge'] : null,
        ];
        if ($request->get('type') === 'withdraw') {
            $meta += ['received_by' => isset($request->meta['received_by']) ? $request->meta['received_by'] : '-'];
        }




        if ($request->type === 'service') {
            $model->wallet->refreshBalance();
            $model->forceWithdrawFloat($request->amount, $meta, true, false);
        } elseif ($request->type === 'deposit') {
            $model->wallet->refreshBalance();
            $transaction = $model->depositFloat($request->amount, $meta, true, true);

            if ($request->get('termName') == 'تامين') {
                $transaction->is_insurance = 1;
                $transaction->confirmed = 0;
                $transaction->save();
                return response()->json(true);
            }

        } else {
            $model->wallet->refreshBalance();
            $transaction = $model->forceWithdrawFloat($request->amount, $meta, true, true);

            if ($request->get('termName') == 'استرجاع تامين') {
                $transaction->is_insurance = 1;
                $transaction->confirmed = 0;
                $transaction->save();
                return response()->json([
                    'success' => true,
                    'transaction' => $transaction,
                    'reservationWithLoadedRelations' => $model->load(['transactions', 'withdrawInsuranceTransactions'])
                ]);
            }
        }


        $meta = [
            "category" => 'reservation',
            "statement" => 'qualifiy-balance'
        ];

        if (abs($model->wallet->balance) <= 9 && abs($model->wallet->balance) >= 1) {

            if ($request->type == 'deposit') {
                $tr = $model->forceWithdrawFloat(abs($model->wallet->balance), $meta, false, false);
                $tr->amount = -1 * $model->wallet->balance;
                $tr->confirmed = 1;
                $tr->save();

                $wallet = Wallet::find($model->wallet->id);
                $wallet->balance = 0;
                $wallet->save();
                $model->wallet->refreshBalance();
            } else {
                $tr = $model->depositFloat(abs($model->wallet->balance), $meta, false, false);
                $tr->amount = 1 * abs($model->wallet->balance);
                $tr->confirmed = 1;
                $tr->save();

                $wallet = Wallet::find($model->wallet->id);
                $wallet->balance = 0;
                $wallet->save();
                $model->wallet->refreshBalance();
            }
            // still some rubbish as creditor

        }

        $model->wallet->refreshBalance();

        if ($model->group_reservation) {
            groupReservationHandler($model->group_reservation);
        }

        return json_encode($model);
    }
    /**
     * Save Transaction for reservation
     * @param Request $request
     */
    public function updateTransaction(Request $request)
    {
        $transaction = Transaction::find($request->id);
        $user = Auth::user();
        if($transaction){
            if ($transaction->is_freezed != null && $transaction->is_freezed == 1 && !$user->hasPermissionTo('edit/delete freezed transactions after business day')) {
                return response()->json([
                    'status' => false,
                    'message' =>__('messages.transaction_frozen')
                ], 403);
            }
        }

        if ($request->type == 'deposit') {

            $term = Term::where('id', $request->meta['type'])->where('name->ar', 'تحويل من الادارة الى الصندوق')->first();
            if ($transaction->payable_type == 'App\Reservation' && $transaction->is_promissory) {



                // linking deposit transaction fulfilled under promissory to promissory to catch any updates on the fly
                $promissory = $transaction->payable->promissory;
                $remainingMaxFulfill = $promissory->total_amount - $promissory->collected_amount;
                $fulfillmentLog = $transaction->payable->promisspryTransactions;


                $sumAllFulfillments = 0;
                if ($fulfillmentLog) {
                    foreach ($fulfillmentLog as $log) {
                        $sumAllFulfillments += $transaction->wallet->decimal_places == 3 ? $log->amount / 1000 : $log->amount / 100;
                    }
                }

                $old_transaction_amount = $transaction->wallet->decimal_places == 3 ? $transaction->amount / 1000 : $transaction->amount / 100;
                $new_transaction_amount = $request->amount;
                if ((($new_transaction_amount - $old_transaction_amount) + $sumAllFulfillments) > $promissory->total_amount) {
                    if ($remainingMaxFulfill <= 0) {

                        return response()->json(['status' => 'no-fulfill-available']);
                    }
                    return response()->json(['status' => 'error', 'remainingMaxFulfillAmount' => number_format($remainingMaxFulfill, 2)]);
                }



                $promissory->collected_amount -= $old_transaction_amount;
                $promissory->collected_amount += $new_transaction_amount;
                if ($promissory->collected_amount != $promissory->total_amount) {
                    $promissory->status = 'pending';
                } else {
                    $promissory->status = 'fulfilled';
                }
                $promissory->save();
            }

            $transaction->amount = $transaction->wallet->decimal_places == 3 ? $request->amount * 1000 : $request->amount * 100;
            $transaction->meta = $request->meta;
            $transaction->transaction_flag = $term ? 'managerial' : 'normal';

            if ($request->get('termName') && $request->get('termName') == 'تامين') {
                $transaction->is_insurance = 1;
                $transaction->confirmed = 0;
                $transaction->save();

                return response()->json(true);
            }
            $transaction->is_insurance = 0;
            $transaction->confirmed = 1;

            $transaction->save();
        } else {

            $term = Term::where('id', $request->meta['type'])->where('name->ar', 'تحويل من الصندوق الى الادارة')->first();
            $transaction->amount = $transaction->wallet->decimal_places == 3 ? $request->amount * -1000 : $request->amount * -100;
            $transaction->meta = $request->meta;
            $transaction->transaction_flag = $term ? 'managerial' : 'normal';

            if ($request->get('termName') && $request->get('termName') == 'استرجاع تامين') {
                $transaction->is_insurance = 1;
                $transaction->confirmed = 0;
                $transaction->save();

                return response()->json(true);
            }

            $transaction->is_insurance = 0;
            $transaction->confirmed = 1;

            $transaction->save();
        }



        $meta = [
            "category" => 'reservation',
            "statement" => 'qualifiy-balance'
        ];

        if (abs($transaction->payable->wallet->balance) <= 9 && abs($transaction->payable->wallet->balance) >= 1) {

            if ($request->get('type') == 'deposit') {
                // still some rubbish as creditor
                $tr = $transaction->payable->forceWithdrawFloat(abs($transaction->payable->wallet->balance), $meta, false, false);
                $tr->amount = -1 * $transaction->payable->wallet->balance;
                $tr->confirmed = 1;
                $tr->save();

                $wallet = Wallet::find($transaction->payable->wallet->id);
                $wallet->balance = 0;
                $wallet->save();
                $transaction->payable->wallet->refreshBalance();
            } else {
                // still some rubbish as debitor
                $tr = $transaction->payable->depositFloat(abs($transaction->payable->wallet->balance), $meta, false, false);
                $tr->amount = 1 * abs($transaction->payable->wallet->balance);
                $tr->confirmed = 1;
                $tr->save();

                $wallet = Wallet::find($transaction->payable->wallet->id);
                $wallet->balance = 0;
                $wallet->save();
                $transaction->payable->wallet->refreshBalance();
            }
        }
        $transaction->payable->wallet->refreshBalance();
        if ($transaction->payable->group_reservation) {
            groupReservationHandler($transaction->payable->group_reservation);
        }
    }


    /**
     * delete transaction from reservation
     * @param Request $request
     * @throws \Exception
     */
    public function deleteTransaction(Request $request)
    {
        $transaction = Transaction::with('payable', 'payable.group_reservation')->find($request->id);
        $user = Auth::user();
        if($transaction){
            if ($transaction->is_freezed != null && $transaction->is_freezed == 1 && !$user->hasPermissionTo('edit/delete freezed transactions after business day')) {
                return response()->json([
                    'status' => false,
                    'message' =>__('messages.transaction_frozen')
                ], 403);
            }
        }
        if ($transaction->payable_type == 'App\Reservation' && $transaction->is_promissory) {
            // when deleting a transactions related to promissory , please affect promissory collected_amount
            $promissory = $transaction->payable->promissory;
            $amount = $transaction->wallet->decimal_places == 2 ? $transaction->amount / 100 : $transaction->amount / 1000;
            $promissory->collected_amount -= $amount;

            if ($promissory->collected_amount != $promissory->total_amount) {
                $promissory->status = 'pending';
            }

            $promissory->save();
        }

        $transaction->delete();
        $transaction->payable->wallet->refreshBalance();
        if ($transaction->payable->group_reservation) {
            groupReservationHandler($transaction->payable->group_reservation);
        }
        // $reservation = Reservation::find($request->reservation_id);
        // $reservation->wallet->refreshBalance();
        // event(new ReservationDeleted($reservation));
    }



    /**
     * Save checkin and checkout for user
     * @param Request $request
     * @throws \Exception
     */
    public function storeChecks(Request $request)
    {
        // incoming request
        $time = $request->get('time');
        $dateIn = $request->get('dateIn');
        $dayStart = Settings::get('day_start');

        $dateOut =  $request->get('dateOut');
        $dayEnd =  Settings::get('day_end');


        $model = Reservation::with('invoices', 'customer')->find($request->id);

        if ($request->type === 'check-in') {

            if (!$time) {
                return response()->json(['status' => 'empty_time']);
            }

            $startDate = \Carbon\Carbon::createFromFormat('Y-m-d H:i', "$dateIn $dayStart");
            $endDate = \Carbon\Carbon::createFromFormat('Y-m-d H:i', "$dateOut $dayEnd");
            $check = \Carbon\Carbon::now()->between($startDate, $endDate);

            if (!$check) {
                return response()->json(['status' => 'can_not_check_in']);
            }

            if (!$model->customer->id_number) {
                return response()->json(['status' => 'missing_id_number']);
            }

            $model->checked_in = new \DateTime($request->time);
            $model->action_type = Reservation::ACTION_CHECKEDIN;
            $model->checking_in = 1;
        } else {


            $automatic_under_cleaning = Setting::where('team_id', $model->team_id)->where('key', 'automatic_under_cleaning')->first();
            if ($automatic_under_cleaning !== null) {

                if ($automatic_under_cleaning->value == 1) {

                    $model->unit->status = Unit::STATUS_UNDER_CLEANING;
                    UnitCleaning::create([
                        'unit_id' => $model->unit_id,
                        'start_at' => new \DateTime(),
                        'team_id' => $model->team_id
                    ]);
                    $model->unit->save();

                }
            }

            if ($request->cleaning) {
                Unit::find($model->unit_id)->update([
                    'status' => 2
                ]);

                try {
                    UnitCleaning::create([
                        'unit_id' => $model->unit_id,
                        'start_at' => new \DateTime(),
                        'team_id' => $model->team_id
                    ]);
                } catch (\Throwable $th) {
                    UnitCleaning::create([
                        'unit_id' => $model->unit_id,
                        'start_at' => new \DateTime(),
                        'team_id' => $model->team_id,
                        'note' => $th->getMessage()
                    ]);
                }

            }

            $model->checked_out = new \DateTime($request->time);
            $model->action_type = Reservation::ACTION_CHECKEDOUT;
            // $model->occ = 0;
        }


        $model->save();

        //        event(new ReservationUpdated($model,  true));
        if ($request->type != 'check-in') {
            event(new ReservationCheckout($model));
            //mytravel checkout
            $team = Team::find($model->team_id);
            if ($team->mytravel_hotel_id != null && ($model->source->name == 'Dyafa Booking Engine'|| $model->source->name == 'محرك حجوزات ضيافا')) {
                $mytravel = $this->changeStatusMytravel($request->id);

            }
            //end mytravel checkout
        } else {
            event(new ReservationCheckIn($model));
        }

        return response()->json($model);
    }
    public function changeUnitStatus(Request $request){
        $unit = Unit::findOrFail($request->unit_id);
        if($unit){

            $unit->status = 1;
            $unit->save();

            return response()->json([
                'message' => 'Unit status updated successfully.',
            ]);
        }else{
            return response()->json([
                'message' => 'Unit not found.',
            ], 404);
        }
    }
    /**
     * @param Request $request
     * @throws \Exception
     */


    public function cancelFees(Request $request)
    {
        $reservation = Reservation::find($request->reservation_id);
        if($request->get('cancellation_fees') > 0){
            $serviceObj = new \stdClass();
            $serviceObj->id = env('CANCELLATION_ID'); // Special identifier for cancellation fee
            $serviceObj->category = 'service';
            $serviceObj->statement = 'Cancellation Fee';
            $serviceObj->qty = 1;
            $serviceObj->vat = 0; // You can adjust VAT if needed
            $serviceObj->ttx = 0; // You can adjust TTX if needed
            $serviceObj->vatIsChecked = false;
            $serviceObj->ttxIsChecked = false;
            $serviceObj->price = $request->get('cancellation_fees');
            $serviceObj->sub_total = $request->get('cancellation_fees');
            $serviceObj->totalGeneralSum = $request->get('cancellation_fees');

            $meta = [
                'category' => 'service',
                'date' => Carbon::now()->format('Y-m-d H:i'),
                'statement' => 'Cancellation Fee',
                'services' => [$serviceObj],
                'total_with_taxes' => $request->get('cancellation_fees'),
                'sub_total' => $request->get('cancellation_fees'),
                'vat_total' => 0,
                'ttx_total' => 0,
                'qty' => 1
            ];

            $transaction = $reservation->forceWithdrawFloat($request->get('cancellation_fees'), $meta, true, false);

            // Create service log entry
            $counter = $reservation->team->counter;
            if (!$counter) {
                $counter = TeamCounter::create();
            }

            $last_service_number = $counter->last_service_number + 1;
            $counter->last_service_number = $last_service_number;
            $counter->save();

            $serviceLog = new ServiceLog;
            $serviceLog->team_id = $reservation->team_id;
            $serviceLog->user_id = auth()->user()->id;
            $serviceLog->transaction_id = $transaction->id;
            $serviceLog->type = $transaction->type;
            $serviceLog->number = $last_service_number;
            $serviceLog->amount = $transaction->amount;
            $serviceLog->decimals = $transaction->wallet->decimal_places;
            $serviceLog->meta = $transaction->meta;
            $serviceLog->save();
        }

        // Handle no-show fees as a service
        if($request->get('no_show_fees') > 0) {
            $reservation->no_show = 1;
            $reservation->save();
            $serviceObj = new \stdClass();
            $serviceObj->id = env('NO_SHOW_ID'); // Special identifier for no-show fee
            $serviceObj->category = 'service';
            $serviceObj->statement = 'No Show Fee';
            $serviceObj->qty = 1;
            $serviceObj->vat = 0; // You can adjust VAT if needed
            $serviceObj->ttx = 0; // You can adjust TTX if needed
            $serviceObj->vatIsChecked = false;
            $serviceObj->ttxIsChecked = false;
            $serviceObj->price = $request->get('no_show_fees');
            $serviceObj->sub_total = $request->get('no_show_fees');
            $serviceObj->totalGeneralSum = $request->get('no_show_fees');

            $meta = [
                'category' => 'service',
                'date' => Carbon::now()->format('Y-m-d H:i'),
                'statement' => 'No Show Fee',
                'services' => [$serviceObj],
                'total_with_taxes' => $request->get('no_show_fees'),
                'sub_total' => $request->get('no_show_fees'),
                'vat_total' => 0,
                'ttx_total' => 0,
                'qty' => 1
            ];

            $transaction = $reservation->forceWithdrawFloat($request->get('no_show_fees'), $meta, true, false);

            // Create service log entry
            $counter = $reservation->team->counter;
            if (!$counter) {
                $counter = TeamCounter::create();
            }

            $last_service_number = $counter->last_service_number + 1;
            $counter->last_service_number = $last_service_number;
            $counter->save();

            $serviceLog = new ServiceLog;
            $serviceLog->team_id = $reservation->team_id;
            $serviceLog->user_id = auth()->user()->id;
            $serviceLog->transaction_id = $transaction->id;
            $serviceLog->type = $transaction->type;
            $serviceLog->number = $last_service_number;
            $serviceLog->amount = $transaction->amount;
            $serviceLog->decimals = $transaction->wallet->decimal_places;
            $serviceLog->meta = $transaction->meta;
            $serviceLog->save();
        }

        $reservation->wallet->refreshBalance();
        return response()->json([
            'message' => 'Fees added successfully.',

        ]);

    }

    public function cancel(Request $request)
    {
        $reservation = Reservation::find($request->id);
        //check if the reservation is from iosell
    if($reservation->cmBookingId != null){
        $otaReservation = OtaReservation::where('cm_booking_id', $reservation->cmBookingId)->first();
        if($otaReservation){
            $otaReservation->update([
                'is_open' => false,
            ]);
        }
    }


        $reservation->depositFloat($reservation->total_price, [
            'category' => 'update_reservation',
            'statement' => 'update Reservation Total Price deposit',
        ], true, false);

        // Handle cancellation fees as a service
        if($request->get('cancellation_fees') > 0){
            $serviceObj = new \stdClass();
            $serviceObj->id = env('CANCELLATION_ID'); // Special identifier for cancellation fee
            $serviceObj->category = 'service';
            $serviceObj->statement = 'Cancellation Fee';
            $serviceObj->qty = 1;
            $serviceObj->vat = 0; // You can adjust VAT if needed
            $serviceObj->ttx = 0; // You can adjust TTX if needed
            $serviceObj->vatIsChecked = false;
            $serviceObj->ttxIsChecked = false;
            $serviceObj->price = $request->get('cancellation_fees');
            $serviceObj->sub_total = $request->get('cancellation_fees');
            $serviceObj->totalGeneralSum = $request->get('cancellation_fees');

            $meta = [
                'category' => 'service',
                'date' => Carbon::now()->format('Y-m-d H:i'),
                'statement' => 'Cancellation Fee',
                'services' => [$serviceObj],
                'total_with_taxes' => $request->get('cancellation_fees'),
                'sub_total' => $request->get('cancellation_fees'),
                'vat_total' => 0,
                'ttx_total' => 0,
                'qty' => 1
            ];

            $transaction = $reservation->forceWithdrawFloat($request->get('cancellation_fees'), $meta, true, false);

            // Create service log entry
            $counter = $reservation->team->counter;
            if (!$counter) {
                $counter = TeamCounter::create();
            }

            $last_service_number = $counter->last_service_number + 1;
            $counter->last_service_number = $last_service_number;
            $counter->save();

            $serviceLog = new ServiceLog;
            $serviceLog->team_id = $reservation->team_id;
            $serviceLog->user_id = auth()->user()->id;
            $serviceLog->transaction_id = $transaction->id;
            $serviceLog->type = $transaction->type;
            $serviceLog->number = $last_service_number;
            $serviceLog->amount = $transaction->amount;
            $serviceLog->decimals = $transaction->wallet->decimal_places;
            $serviceLog->meta = $transaction->meta;
            $serviceLog->save();
        }

        // Handle no-show fees as a service
        if($request->get('no_show_fees') > 0) {
            $reservation->no_show = 1;
            $reservation->save();
            $serviceObj = new \stdClass();
            $serviceObj->id = env('NO_SHOW_ID'); // Special identifier for no-show fee
            $serviceObj->category = 'service';
            $serviceObj->statement = 'No Show Fee';
            $serviceObj->qty = 1;
            $serviceObj->vat = 0; // You can adjust VAT if needed
            $serviceObj->ttx = 0; // You can adjust TTX if needed
            $serviceObj->vatIsChecked = false;
            $serviceObj->ttxIsChecked = false;
            $serviceObj->price = $request->get('no_show_fees');
            $serviceObj->sub_total = $request->get('no_show_fees');
            $serviceObj->totalGeneralSum = $request->get('no_show_fees');

            $meta = [
                'category' => 'service',
                'date' => Carbon::now()->format('Y-m-d H:i'),
                'statement' => 'No Show Fee',
                'services' => [$serviceObj],
                'total_with_taxes' => $request->get('no_show_fees'),
                'sub_total' => $request->get('no_show_fees'),
                'vat_total' => 0,
                'ttx_total' => 0,
                'qty' => 1
            ];

            $transaction = $reservation->forceWithdrawFloat($request->get('no_show_fees'), $meta, true, false);

            // Create service log entry
            $counter = $reservation->team->counter;
            if (!$counter) {
                $counter = TeamCounter::create();
            }

            $last_service_number = $counter->last_service_number + 1;
            $counter->last_service_number = $last_service_number;
            $counter->save();

            $serviceLog = new ServiceLog;
            $serviceLog->team_id = $reservation->team_id;
            $serviceLog->user_id = auth()->user()->id;
            $serviceLog->transaction_id = $transaction->id;
            $serviceLog->type = $transaction->type;
            $serviceLog->number = $last_service_number;
            $serviceLog->amount = $transaction->amount;
            $serviceLog->decimals = $transaction->wallet->decimal_places;
            $serviceLog->meta = $transaction->meta;
            $serviceLog->save();
        }

        $reservation->wallet->refreshBalance();

        if ($reservation->reservation_type == 'group' && $reservation->status == 'confirmed') {
            $this->moveTransactions($reservation);
        }

        $reservation->canceled_reason = $request->get('reason') ? $request->get('reason') : null;
        $reservation->occ = 0;
        $reservation->action_type = Reservation::ACTION_CANCELED;
        $reservation->save();
        $reservation->cancel();

        if($reservation->reservation_type == 'group'){
            $this->removeBalanceMapperRecord($reservation->id);
        }
        $team_id = auth()->user()->current_team_id;
        // in teams table if the team have enable_aisosell is true
        $enable_aisosell = Team::where('id', $team_id)->first()->enable_aiosell;
        if($enable_aisosell ){
            $this->UpdateAiosellInventoryOncancel($reservation);
        }
        $enable_staaah = Team::where('id', $team_id)->first()->enable_staah;
        if ($reservation->unit->available_to_sync && $enable_staaah) {
            $this->UpdateStaaahInventoryOncancel($reservation);
        }

        //cancelation of reservation to mytravel
        if ($reservation->team->mytravel_hotel_id) {
            $mytravel = $this->UpdateMyTravelInventoryOncancel($reservation);
        }
        if ($reservation->team->mytravel_hotel_id && $reservation->attachable_id == null) {
            $mytravel = $this->updateMyTravelInventoryOnCancelSingle($reservation);
        }
        if ($reservation->team->mytravel_hotel_id && $reservation->attachable_id) {
            $mytravel = $this->updateMyTravelInventoryOnCancelGroup($reservation);
        }
    }

    function removeBalanceMapperRecord($reservation_id){
        $check_mapper = GroupReservationBalanceMapper::where('reservation_id' , $reservation_id)->first();
        if($check_mapper){
            $check_mapper->delete($check_mapper->id);
        }
    }

    public function UpdateStaaahInventoryOncancel($request)
    {
        $team_id = $request->team_id;
        $unit_category_id = $request->unit->unit_category_id;
        $date_in = $request->date_in;
        $date_out = $request->date_out;
        // convert it to carbon
        $date_in = Carbon::parse($date_in);
        $date_out = Carbon::parse($date_out);
        // get the unit category where id = unit_category_id
        $unit_category = UnitCategory::find($unit_category_id);

        $roomsData = [];
        for ($date = $date_in->copy(); $date->lte($date_out); $date->addDay()) {
            $dayData = [
                'from' => $date->format('Y-m-d'),
                'to' => $date->copy()->addDay()->format('Y-m-d'), // End date is the next day
                // You can add more fields here as needed
            ];
            $roomsData[] = $dayData;

        }
        $room = [];
        foreach ($roomsData as $date) {
            $from = $date['from'];
            $to = $date['to'];
            $dayName = Carbon::parse($from)->format('l');
            $day_Name_price = strtolower($dayName) . '_day_price';
            $price = $unit_category[$day_Name_price];
            // get the number of units that is no reservation for this from to date and the unit category id is the same as the request and available to sync is true

        $all_units = Unit::where('unit_category_id', $unit_category_id)
        ->where('status', '!=', 3)
        ->where('deleted_at', null)->pluck('id')->toArray();
        $available_arr = [];
        foreach($all_units as $unit_id){
            $hasIntersectionWorkable = checkIfUnitHasReservation($unit_id, Carbon::parse($from));
            if(!$hasIntersectionWorkable){
                array_push($available_arr, $unit_id);
            }



        }
        $available_units = count($available_arr);
            // dd($available_units);
            // if this team have tax and ewa
            $ewa_percantage = getEwaPercentageForUnit($team_id);
            $tax_percentage = getVatPercentageForUnit($team_id);
            $ewa_total = getEwaTotalForUnit($price, $ewa_percantage);
            $tax_total = getVatTotalForUnit($price, $ewa_total, $tax_percentage);
            $total_price = $price + $ewa_total + $tax_total;

    $data = [
        "hotelid" => $team_id,
        "room" => [
            [
                "roomid" => $unit_category_id,
                "date" => [
                    [
                        "from" => $from,
                        "to" => $from,
                        // "rate" => [
                        //     [
                        //         "rateplanid" => "DEF"
                        //     ]
                        // ],
                        "roomstosell" => $available_units,
                        // "price" => [
                        //     [
                        //         "NumberOfGuests" => "1",
                        //         "value" => $total_price
                        //     ]
                        // ],

                    ]
                ]
            ]
        ]
    ];
    $data = [
        "hotelid" => $team_id,
        "room" => $room
    ];
    $url = env('STAAH_MEDIATOR_API_URL') . '/api/v1/availability';
    $curl = curl_init();

    curl_setopt_array($curl, array(
       CURLOPT_URL => $url,
       CURLOPT_RETURNTRANSFER => true,
       CURLOPT_ENCODING => '',
       CURLOPT_MAXREDIRS => 10,
       CURLOPT_TIMEOUT => 0,
       CURLOPT_FOLLOWLOCATION => true,
       CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
       CURLOPT_CUSTOMREQUEST => 'POST',
       CURLOPT_POSTFIELDS => json_encode($data),
       CURLOPT_HTTPHEADER => array(
          'Content-Type: application/json'
       ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);

}
    }
 //mytravel methods for cancelation
 public function updateMyTravelInventoryOnCancelSingle($request)
 {
     // dd($request);
     $reservation_id = $request->id;
     $unit_category_id = $request->unit->unit_category_id;
     $total_amount = $request->total_price;
     $data = [
         'fandaqah_ref_id' => $reservation_id,
         'category_id' => $unit_category_id,
         'total_amount' => $total_amount,
     ];

     $url = env('MYTRAVEL_API_URL') . '/api/booking/single-cancel';
     $key = env('MY_TRAVEL_KEY');

     $curl = curl_init();
     curl_setopt_array($curl, array(
         CURLOPT_URL => $url,
         CURLOPT_RETURNTRANSFER => true,
         CURLOPT_ENCODING => '',
         CURLOPT_MAXREDIRS => 10,
         CURLOPT_TIMEOUT => 400,
         CURLOPT_FOLLOWLOCATION => true,
         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
         CURLOPT_CUSTOMREQUEST => 'POST',
         CURLOPT_POSTFIELDS => json_encode($data),
         CURLOPT_HTTPHEADER => array(
             'Content-Type: application/json',
             'key: ' . $key
         ),
     ));

     $response = curl_exec($curl);

     curl_close($curl);
 }


 public function updateMyTravelInventoryOnCancelGroup($request)
 {
     $attachable_id = $request->attachable_id;
     $unit_category_id = $request->unit->unit_category_id;
     $total_amount = $request->total_price;
     $data = [
         'fandaqah_ref_id' => $attachable_id,
         'category_id' => $unit_category_id,
         'total_amount' => $total_amount,
     ];

     $url = env('MYTRAVEL_API_URL') . '/api/booking/group-cancel';
     $key = env('MY_TRAVEL_KEY');

     $curl = curl_init();
     curl_setopt_array($curl, array(
         CURLOPT_URL => $url,
         CURLOPT_RETURNTRANSFER => true,
         CURLOPT_ENCODING => '',
         CURLOPT_MAXREDIRS => 10,
         CURLOPT_TIMEOUT => 400,
         CURLOPT_FOLLOWLOCATION => true,
         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
         CURLOPT_CUSTOMREQUEST => 'POST',
         CURLOPT_POSTFIELDS => json_encode($data),
         CURLOPT_HTTPHEADER => array(
             'Content-Type: application/json',
             'key: ' . $key
         ),
     ));

     $response = curl_exec($curl);
     curl_close($curl);
 }
 //end mytravel methods for cancelation

//mytravel sync methods after cancelation
public function UpdateMyTravelInventoryOncancel($request)
{

    $team_id = $request->team_id;
    $unit_category_id = $request->unit->unit_category_id;
    $date_in = $request->date_in;
    $date_out = $request->date_out;
    // convert it to carbon
    $date_in = Carbon::parse($date_in);
    $date_out = Carbon::parse($date_out);
    // get the unit category where id = unit_category_id
    $unit_category = UnitCategory::find($unit_category_id);

    $roomsData = [];
    for ($date = $date_in->copy(); $date->lte($date_out); $date->addDay()) {
        $dayData = [
            'from' => $date->format('Y-m-d'),
            'to' => $date->copy()->addDay()->format('Y-m-d'), // End date is the next day
            // You can add more fields here as needed
        ];
        $roomsData[] = $dayData;
    }
    array_pop($roomsData);
    foreach ($roomsData as $date) {

            $from = $date['from'];
            $to = $date['to'];
            $dayName = Carbon::parse($from)->format('l');
            $day_Name_price = strtolower($dayName) . '_day_price';
            // $price = $unit_category[$day_Name_price];
            $specialPrice = SpecialPrice::where('unit_category_id', $unit_category_id)->where('start_date', '<=', $date['from'])->where('end_date', '>=', $date['from'])->where('enabled', true)->first();
            if($specialPrice){
                $price = $specialPrice->days_prices[$dayName];
            }else{

                $price = $unit_category[$day_Name_price];
            }
            // get the number of units that is no reservation for this from to date and the unit category id is the same as the request and available to sync is true
            $all_units = Unit::where('unit_category_id', $unit_category_id)
            ->where('status', '!=', 3)
            ->where('deleted_at', null)->pluck('id')->toArray();
            $available_arr = [];
            foreach($all_units as $unit_id){
                $hasIntersectionWorkable = checkIfUnitHasReservation($unit_id, Carbon::parse($from));
                if(!$hasIntersectionWorkable){
                    array_push($available_arr, $unit_id);
                }
        }
        $available_units = count($available_arr);
            $ewa_percantage = getEwaPercentageForUnit($team_id);
            $tax_percentage = getVatPercentageForUnit($team_id);
            $ewa_total = getEwaTotalForUnit($price, $ewa_percantage);
            $tax_total = getVatTotalForUnit($price, $ewa_total, $tax_percentage);
            $total_price = $price + $ewa_total + $tax_total;
            $data = [
                'fandaqah_ref_id' => $request->id,
                'reservation_amount' => $request->total_price,
                'category_id' => $unit_category_id,
                'start_date' => $from,
                'end_date' => $from,
                'price' => $total_price,
                'active' => 1,
                'note_to_customer' => '',
                'note_to_admin' => '',
                'is_instant' => 1,
                'number' => $available_units,
                'create_user' => $team_id,
                'update_user' => $team_id,
                'status' => "cancel_reservation",
                'original_date_in' => $date_in->format('Y-m-d'),
                'original_date_out' => $date_out->format('Y-m-d'),
            ];

        $curl = curl_init();
        $url = env('MYTRAVEL_API_URL') . '/api/hotel/room/Inventory-delete';
        $key = env('MY_TRAVEL_KEY');
        curl_setopt_array($curl, array(
            CURLOPT_URL =>  $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 400,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'key: ' . $key
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
    }
}
//end mytravel sync methods after cancelation

    /**
     * Update reservation customer
     * @param Request $request
     */
    public function updateCustomer(Request $request)
    {
        // $model = Reservation::find($request->id);
        $c = $request['customer'];
        $c['phone'] = preg_replace('/\s+/', '', $c['phone']);

        /**
         * Unique validation for customer id number
         * @note : commented out as per request from islam
         */
        if ($c['id_number'] && !$c['id']) {

            $validator = Customer::validate($c, $c['id']);

            if ($validator->fails()) {
                $response = [
                    'success' => false,
                    'message' => 'id_number_taken'
                ];
                return response()->json($response);
            }
        }
        $customer = Customer::updateOrCreate(['id' => $c['id']], $c);
        $reservation = Reservation::findOrFail($request->id);

        if (!$reservation->team->integration_shms && !$reservation->team->integration_shomoos_version_one && ($customer->id != $reservation->customer_id)) {
            // in this case i need to remove all escorts for old customer ( if any )
            // i should detach from customer_guest_reservation table
            $reservation->reservation_guests()->where('reservation_id', $reservation->id)->detach();
            // then i should update the escort reservation_id inside guest table and set it to null
            if (count($reservation->guests)) {
                foreach ($reservation->guests as $escort) {
                    $escort->reservation_id = null;
                    $escort->save();

                    // fire a delete escort job to release this escort from shomos
                    event(new ShomoosDeleteEscort($reservation->id, $escort));
                }
            }

        }

        if (!$request->get('attach_in_all_reservation')) {
            if($reservation->company && $reservation->company->entity_type == 'individual'){


                $main_reservation = null;
                $push_main_reservation_to_collection = false;
                if (is_null($reservation->attachable_id)) {
                    $main_reservation = $reservation;
                    $push_main_reservation_to_collection = false;
                } else {
                    $main_reservation = Reservation::find($reservation->attachable_id);
                    $push_main_reservation_to_collection = true;
                }

                $reservations = Reservation::with('wallet', 'unit')
                ->where('reservation_type', 'group')
                ->where('company_id', $reservation->company_id)
                ->where(function ($query) use ($reservation, $main_reservation) {
                    return $query->with('unit')->where('id', $reservation->id)->orWhere('attachable_id', $main_reservation->id);
                })
                ->whereIn('status', ['confirmed', 'awaiting-payment'])
                // ->whereNull('checked_out')
                ->whereNull('deleted_at')
                ->get();

                if ($push_main_reservation_to_collection) {
                    $reservations->push($main_reservation);
                }


                if (count($reservations)) {
                    foreach ($reservations as $related_reservation) {

                        $related_reservation->update(['customer_id' => $customer->id, 'action_type' => Reservation::ACTION_UPDATERESERVATIONCUSTOMER]);
                        // if (is_null($related_reservation->customer_id)) {
                        // }
                    }
                }

                $reservation->company->name = $customer->name;
                $reservation->company->phone = $customer->phone;
                $reservation->company->customer_id = $customer->id;
                $reservation->company->save();
            }
            $reservation->update(['customer_id' => $customer->id, 'action_type' => Reservation::ACTION_UPDATERESERVATIONCUSTOMER]);
        } else {
            $main_reservation = null;
            $push_main_reservation_to_collection = false;
            if (is_null($reservation->attachable_id)) {
                $main_reservation = $reservation;
                $push_main_reservation_to_collection = false;
            } else {
                $main_reservation = Reservation::find($reservation->attachable_id);
                $push_main_reservation_to_collection = true;
            }

            $reservations = Reservation::with('wallet', 'unit')
                ->where('reservation_type', 'group')
                ->whereDoesntHave('customer')
                ->where('company_id', $reservation->company_id)
                ->where(function ($query) use ($reservation, $main_reservation) {
                    return $query->with('unit')->where('id', $reservation->id)->orWhere('attachable_id', $main_reservation->id);
                })
                ->whereIn('status', ['confirmed', 'awaiting-payment'])
                ->whereNull('deleted_at')
                ->get();

            if ($push_main_reservation_to_collection) {
                $reservations->push($main_reservation);
            }

            if (count($reservations)) {
                foreach ($reservations as $related_reservation) {
                    if (is_null($related_reservation->customer_id)) {
                        $related_reservation->update(['customer_id' => $customer->id, 'action_type' => Reservation::ACTION_UPDATERESERVATIONCUSTOMER]);
                    }
                }
            }
        }
        if ($request->has('attach_customer') && $request->get('attach_customer')) {
            // then i need to fetch all public withdraw transactions then update the value of received_by inside meta
            $withdraw_transactions = $reservation->getWithdrawTransactions();
            if (count($withdraw_transactions)) {
                foreach ($withdraw_transactions as $transaction) {
                    if (isset($transaction['meta']) && isset($transaction['meta']['received_by'])) {
                        // the below step to avoid indirect modification exception -- note for developers  -_-
                        $metadata = collect($transaction['meta'])->jsonserialize();
                        $metadata['received_by'] = $customer->name;
                        $transaction['meta'] = $metadata;
                        $transaction->save();
                    }
                }
            }

            // if attach_in_all_reservation option found then we will do the magic  ...

        } else {
            // then i need to fetch all public withdraw transactions then update the value of received_by inside meta
            $withdraw_transactions = $reservation->getWithdrawTransactions();
            if (count($withdraw_transactions)) {
                foreach ($withdraw_transactions as $transaction) {
                    if (isset($transaction['meta']) && isset($transaction['meta']['from'])) {
                        // the below step to avoid indirect modification exception -- note for developers  -_-
                        $metadata = collect($transaction['meta'])->jsonserialize();
                        $metadata['from'] = $customer->name;
                        $transaction['meta'] = $metadata;
                        $transaction->save();
                    }
                }
            }

            $deposit_transactions = $reservation->getDepositTransactions();
            if (count($deposit_transactions)) {
                foreach ($deposit_transactions as $transaction) {
                    if (isset($transaction['meta']) && isset($transaction['meta']['from'])) {
                        // the below step to avoid indirect modification exception -- note for developers  -_-
                        $metadata = collect($transaction['meta'])->jsonserialize();
                        $metadata['from'] = $customer->name;
                        $transaction['meta'] = $metadata;
                        $transaction->save();
                    }
                }
            }
        }

        event(new ShomosReservationUpdated($reservation));
        event(new ReservationUpdated($reservation));
        $response = [
            'success' => true,
            'message' => 'updated'
        ];
        return response()->json($response);
    }
//mytravel sync method for updating inventory
public function updateMyTravelInventory($request)
{

    $unit_id = $request['unit']['id'];
    $start_date = $request['unit']['reservation']['start_date'];
    $end_date = $request['unit']['reservation']['end_date'];
    //convert start date and end date to carbon
    $start_date = Carbon::parse($start_date);
    $end_date = Carbon::parse($end_date);
    $team_id = auth()->user()->current_team_id;
    $unit_category_id = Unit::find($unit_id)->unit_category_id;
    // get the unit category with id = $unit_category_id
    $unit_category = UnitCategory::find($unit_category_id);

    $roomsData = [];

    for ($date = $start_date->copy(); $date->lte($end_date); $date->addDay()) {
        $dayData = [
            'from' => $date->format('Y-m-d'),
            'to' => $date->copy()->addDay()->format('Y-m-d'), // End date is the next day
            // You can add more fields here as needed
        ];
        $roomsData[] = $dayData;
    }
    //remove last index in roomsData
    array_pop($roomsData);
    foreach ($roomsData as $date) {

            $from = $date['from'];
            $to = $date['to'];
            $dayName = Carbon::parse($from)->format('l');
            $day_Name_price = strtolower($dayName) . '_day_price';
            // $price = $unit_category[$day_Name_price];
            $specialPrice = SpecialPrice::where('unit_category_id', $unit_category_id)->where('start_date', '<=', $date['from'])->where('end_date', '>=', $date['from'])->where('enabled', true)->first();
            if($specialPrice){
                $price = $specialPrice->days_prices[$dayName];
            }else{

                $price = $unit_category[$day_Name_price];
            }
            // get the number of units that is no reservation for this from to date and the unit category id is the same as the request and available to sync is true
            $all_units = Unit::where('unit_category_id', $unit_category_id)
            ->where('status', '!=', 3)
            ->where('deleted_at', null)->pluck('id')->toArray();
            $available_arr = [];
        foreach($all_units as $unit_id){
            $hasIntersectionWorkable = checkIfUnitHasReservation($unit_id, Carbon::parse($from));
            if(!$hasIntersectionWorkable){
                array_push($available_arr, $unit_id);
            }



    }
    $available_units = count($available_arr);

        $ewa_percantage = getEwaPercentageForUnit($team_id);
        $tax_percentage = getVatPercentageForUnit($team_id);
        $ewa_total = getEwaTotalForUnit($price, $ewa_percantage);
        $tax_total = getVatTotalForUnit($price, $ewa_total, $tax_percentage);
        $total_price = $price + $ewa_total + $tax_total;
        $data = [

            'category_id' => $unit_category_id,
            'start_date' => $from,
            'end_date' => $from,
            'team_id' => $team_id,
            'price' => $total_price,
            'active' => 1,
            'note_to_customer' => '',
            'note_to_admin' => '',
            'is_instant' => 1,
            'number' => $available_units,
            'create_user' => 1,
            'update_user' => 1
        ];
        $curl = curl_init();
        $url = env('MYTRAVEL_API_URL') . '/api/hotel/room/Inventory-update';
        $key = env('MY_TRAVEL_KEY');

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 400,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'key: ' . $key
            ),
        ));

        $response = curl_exec($curl);


    }
}
//end mytravel sync method for updating inventory

    /**
     * retrieve reservation with relations
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $reservation = Reservation::with('customer', 'unit', 'creator', 'comments', 'customer.guests', 'reservation_guests' , 'source', 'invoices', 'promissory','depositInsuranceTransactions', 'withdrawInsuranceTransactions','company','company.reservations' , 'team' , 'team.terms','reservationFreeServices','pure_invoices_without_credit_notes','signedContract','signedContracts')->withCount('signedContracts')->find($id);
        if(is_null($reservation)) {
            abort(404, 'Resource not found');
        }
        /** @var Unit $unit */
        $unit = Unit::whereId($reservation->unit_id)->withTrashed()->first();

        $reservation->wallet->refreshBalance();
        $date_start = Carbon::parse($reservation['date_in']);
        $date_end = Carbon::parse($reservation['date_out']);
        $reservation['Unifonic'] = Settings::checkIntegration('Unifonic', $reservation->team_id);
        $reservation['SCTH'] = Settings::checkIntegration('SCTH', $reservation->team_id);
        $reservation['SHMS'] = Settings::checkIntegration('SHMS', $reservation->team_id);
        //        $reservation['logs'] = $reservation->activities()->with('causer')->get();
        $reservation['logs'] = $reservation->logs();
        $reservation['transactions'] = $reservation->transactions;
        $reservation['services'] = $reservation->services;
        $reservation['balance'] = $reservation->balance;
        // initiate group_reservation fillings
        if ($reservation->reservation_type == 'group') {
            $reservation['attachable_reservations_count'] = count($reservation->attachedReservations());
            $transactions = [];
            $services = [];
            $balances = [];
            $shared_invoices = [];
            $promissories = [];
            $main_reservation = null;
            $push_main_reservation_to_collection = false;
            if (is_null($reservation->attachable_id)) {
                $main_reservation = $reservation;
                $push_main_reservation_to_collection = false;
            } else {
                $main_reservation = Reservation::find($reservation->attachable_id);
                $push_main_reservation_to_collection = true;
            }


            if ($main_reservation->status == 'canceled') {
                $reservations = Reservation::with('wallet', 'unit')
                    ->where('reservation_type', 'group')
                    ->where('company_id', $reservation->company_id)
                    ->where(function ($query) use ($reservation, $main_reservation) {
                        return $query->with('unit')->where('id', $reservation->id)->orWhere('attachable_id', $main_reservation->id);
                    })
                    ->where('status', 'canceled')
                    ->whereNull('deleted_at')
                    ->get();

            } else {
                $reservations = Reservation::with('wallet', 'unit')
                    ->where('reservation_type', 'group')
                    ->where('company_id', $reservation->company_id)
                    ->where(function ($query) use ($reservation, $main_reservation) {
                        return $query->with('unit')->where('id', $reservation->id)->orWhere('attachable_id', $main_reservation->id);
                    })
                    ->whereIn('status', ['confirmed', 'awaiting-payment'])
                    // ->whereNull('checked_out')
                    ->whereNull('deleted_at')
                    ->get();

            }


            if ($push_main_reservation_to_collection) {
                $reservations->push($main_reservation);
            }
            $all_grouped_reservations_ids = [];
            foreach ($reservations as $reservationObject) {
                $balances[] = $reservationObject->wallet->decimal_places == 3 ? $reservationObject->balance / 1000 : $reservationObject->balance / 100;
                $all_grouped_reservations_ids[] = $reservationObject->id;
                if ($reservationObject->promissory) {
                    $promissories[] = $reservationObject->promissory;
                }

                if ($reservationObject->invoices()->count()) {
                    foreach ($reservationObject->invoices as $invoice) {
                        $shared_invoices[] = $invoice;
                    }
                }

                if ($reservationObject->transactions()->count()) {
                    foreach ($reservationObject->transactions as $transaction) {
                        $transactions[] = $transaction;
                    }
                }

                if ($reservationObject->services()->count()) {
                    foreach ($reservationObject->services as $transaction) {
                        $services[] = $transaction;
                    }
                }
            }
            foreach ($reservations as $obj) {
                GroupReservationBalanceMapper::updateOrCreate(
                    ['reservation_id' => $obj->id],
                    ['balance' => floatval(array_sum($balances) / count($reservations))]
                );
            }

            $reservation['group_reservation_transactions'] = collect($transactions)->sortByDesc('number')->values();
            $reservation['group_reservation_services'] = collect($services)->sortByDesc('service_log_number')->values();
            $reservation['group_balance'] = array_sum($balances);
            $reservation['shared_promissory'] = count($promissories) ? $promissories[0] : null;
            $reservation['shared_invoices'] = collect($shared_invoices)->sortByDesc('number')->values();
            $reservation['all_grouped_reservations'] = $reservations;
            $reservation['all_grouped_reservations_ids'] = $all_grouped_reservations_ids;
            $reservation['dates_calculations'] = startAndEndDateCalculatorWithNights($reservations);
            $reservation['main_reservation_id'] = $main_reservation->id;
        }

        $reservation['invoice_url'] = url("/home/reservation/pdf/invoice/{$reservation->id}");
        //        $reservation['has_checkin'] = Reservation::where('checked_in', '!=',null)->where('checked_out','=',null)->where('unit_id',$reservation->unit_id)->first();

        // became appended attribute inside model
        //        $reservation['hash_id'] = Hashids::connection('fandaqah')->encode($reservation->id);
        $reservation['url_current'] = \Config::get('app.url');
        $reservation['logout_icon'] = asset('img/logout.png');
        $reservation['login_icon'] = asset('img/enter.png');

        // became appended attribute inside model
        $reservation['day_start'] = \App\Handlers\Settings::get('day_start');
        $reservation['day_end'] = \App\Handlers\Settings::get('day_end');
        $reservation['messages_logs'] = $reservation->messagesLog()->get(['type', 'message', 'created_at'])->toArray();
        $reservation['customerNotes'] = $reservation->customer ? $reservation->customer->comments : null;
        $reservation['customer_id_number'] = $reservation->customer ? $reservation->customer->id_number : null;

        $hardDeletedUnitTotals = [
            'currency' => 'SAR',
            'days' => [],
            'price' => '-',
            'vat_parentage' => '-',
            'ewa_parentage' => '-',
            'sub_total' => '-',
            'min_sub_total' => '-',
            'total_vat' => '-',
            'total_ewa' => '-',
            'total_price' => '-',
            'total_price_raw' => '-',
            'total_tourism' => '-',
            'tourism_percentage' => '-'
        ];

        // $prices = $unit ?  $unit->getDatesFromRange($date_start, $date_end , $reservation->rent_type) : $hardDeletedUnitTotals;

        // if ($reservation->old_prices) {
        //     $prices = $unit ?  $unit->getDatesFromRangeWithOldPrices(Carbon::parse($reservation->date_in), Carbon::parse($reservation->date_out), $reservation->old_prices, $reservation->change_rate, $reservation->rent_type, $reservation->rent_type) : $hardDeletedUnitTotals;
        // }


        $reservation['prices'] = $reservation->prices ? $reservation->prices : $unit->getDatesFromRange($date_start, $date_end, $reservation->rent_type);
        $reservation['change_rate'] = $reservation->change_rate;
        $reservation['old_prices'] = $reservation->old_prices;

        return response()->json($reservation);
    }

    public function determineIfUnitHasActualReservationAccordingToStartDate($unit,$date_start,$currentDatesHolder)
    {
              /**
             * Here i will put my logic to handle the intersection based on the unit selected
             */

                $unitReservations = Reservation::where('unit_id' , $unit->id)
                                ->whereNUll('checked_out')
                                ->whereIn('status' , ['confirmed','awaiting-payment' , 'awaiting-confirmation'])
                                ->whereNull('deleted_at')
                                ->get();

                $unitDatesHolder = [];
                if(count($unitReservations)){
                    foreach($unitReservations as $unitReservation){
                        $start  = Carbon::parse($unitReservation->date_in);
                        $end  = Carbon::parse($unitReservation->date_out);
                        if ($start != $end) {
                            $end->subDay();
                        }
                        $period = CarbonPeriod::create($start, $end);
                        foreach ($period as $date) {
                            if(!in_array($date->format('Y-m-d'),$unitDatesHolder)){
                                $unitDatesHolder [] = $date->format('Y-m-d');
                            }
                        }
                    }
                }


                /**
                 * Checking the overlapping the right way -_-
                 */
                if(array_intersect($currentDatesHolder,$unitDatesHolder)){
                    $has_reservation = true;
                }else{
                    $has_reservation = false;
                }

                return $has_reservation;
    }





    public function store(Request $request)
    {

        // avoid two reservations on same unit exception
        $unit = Unit::find($request->unit['id']);
        // $unit_has_reservation = $unit->has_reservation(Carbon::parse($request->unit['reservation']['start_date']));
        // $unit_has_reservation = checkIfUnitHasReservation($unit->id, Carbon::parse($request->unit['reservation']['start_date']));
        // if($unit_has_reservation){
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'unit_has_reservation'
        //     ]);
        // }
        //determineIfUnitHasActualReservationAccordingToStartDate
        $currentDatesHolder = [];

        $period = CarbonPeriod::create(Carbon::parse($request->unit['reservation']['start_date']), Carbon::parse($request->unit['reservation']['end_date']));
        foreach ($period as $date) {


            if(!in_array($date->format('Y-m-d'),$currentDatesHolder)){
                $currentDatesHolder [] = $date->format('Y-m-d');
            }


        }
        array_pop($currentDatesHolder);

        $has_reservation = $this->determineIfUnitHasActualReservationAccordingToStartDate($unit,Carbon::parse($request->unit['reservation']['start_date']),$currentDatesHolder);
        if($has_reservation){
            return response()->json([
                'success' => false,
                'message' => 'unit_has_reservation'
            ]);
        }




        $remove_vat = $request['remove_vat'];
        $customer = $request['customer'];
        $data = $request['unit'];
        $specialPricesCollector = json_encode($request['specialPricesCollector']);
        if ($data['reservation']['start_date'] == $data['reservation']['end_date']) {
            // make the end date +1 day format d-m-Y
            $data['reservation']['end_date'] = Carbon::parse($data['reservation']['end_date'])->addDay()->format('d-m-Y');
        }
        $customer['phone'] = preg_replace('/\s+/', '', $customer['phone']);

        /**
         * Unique validation for customer id number
         * @note : commented out as per request from islam
         * @update : 2021-08-24 ( make the unique validation only when we create new user cause we have problem with old entries )
         * so am here applying the unique validation only when there is an id_number passed and making sure that customer is not selected
         * from the search
         */
        if ($customer['id_number'] && !$customer['id']) {

            $validator = Customer::validate($customer, $customer['id']);

            if ($validator->fails()) {
                $response = [
                    'success' => false,
                    'message' => 'id_number_taken'
                ];
                return response()->json($response);
            }

        }


        $customer = Customer::updateOrCreate(['id' => $customer['id']], $customer);

        // in this part i will mirror the chosen or the newely created customer as an entity company of type individual
        if ($request->has('reservation_group_type') && !is_null($request->get('reservation_group_type')) && $request->get('reservation_group_type') == 'individual') {

            if (!$request->get('company_id')) {
                $company_blue_print = [
                    'name' => $customer->name,
                    'phone' => str_replace(' ', '', $customer->phone),
                    'entity_type' => $request->get('reservation_group_type'),
                    'user_id' => auth()->user()->id
                ];
                $updatedOrCreatedCompany = Company::updateOrCreate(['name' => $customer->name, 'team_id' => auth()->user()->current_team_id], $company_blue_print);
                $request->request->add(['company_id' => $updatedOrCreatedCompany->id]);
            }
        }

        $reservation = new Reservation();


        $reservation->team_id = auth()->user()->current_team_id;
        $reservation->special_prices = $specialPricesCollector;
        $reservation->unit_id = $data['id'];
        $reservation->source_id = $request->get('source_id');
        $reservation->source_num = $request->get('source_num');
        $reservation->rent_type = $request->get('rent_type');
        $reservation->customer_id = $customer->id;
        $reservation->date_in = new Carbon($data['reservation']['start_date']);
        $reservation->date_out = new Carbon($data['reservation']['end_date']);
        // attaching creator id for the reservation
        $reservation->created_by = auth()->user()->id;
        $reservation->total_price = $data['reservation']['prices']['total_price_raw'];
        $reservation->sub_total = $data['reservation']['prices']['price'];
        //        $reservation->vat_total = $data['reservation']['prices']['total_vat'];
        $reservation->vat_total = $request['total_vat'];
        //        $reservation->ewa_total = $data['reservation']['prices']['total_ewa'];
        $reservation->ewa_total = $request['total_ewa'];
        //        $reservation->ttx_total = $data['reservation']['prices']['total_tourism'];

        $reservation->ttx_total = $request['total_ttx'];
        $reservation->purpose_of_visit = $data['purpose'];
        $reservation->change_rate = isset($data['change_rate']) ? $data['change_rate'] : 0;

        // dd($reservation->change_rate);
        /* Add-On to flush date_in_time && date_out_time new columns in reservations tbl to be used in Total Revenues, Taxes & Fees Report */
        $day_start_time = \App\Handlers\Settings::get('day_start');
        $date_in = Carbon::parse($reservation->date_in)->toDateString();
        $day_end_time = \App\Handlers\Settings::get('day_end');
        $date_out = Carbon::parse($reservation->date_out)->toDateString();
        $combinedDateInTime = date('Y-m-d H:i:s', strtotime("$date_in $day_start_time"));
        $combinedDateOutTime = date('Y-m-d H:i:s', strtotime("$date_out $day_end_time"));

        $reservation->date_in_time = $combinedDateInTime;
        $reservation->date_out_time = $combinedDateOutTime;

        // inject counter here cause i need to handle reservation number


        if ($request->get('company_id')) {
            $reservation->reservation_type = $request->get('company_id') ? 'group' : 'single';
            $reservation->company_id = $request->get('company_id');
            if ($request->get('attachable_reservation_id')) {
                // i need first to check if there are previous attached reservations or not
                // cause we need to prefix the attached reservations in this formula From A - Z  + main reservation number
                // we will attach the reservation
                $reservation->attachable_id = $request->get('attachable_reservation_id');
                $main_reservation = Reservation::find($request->get('attachable_reservation_id'));
                $attachables = Reservation::where('attachable_id', $request->get('attachable_reservation_id'))
                    ->withTrashed()
                    ->whereIn('status', ['confirmed', 'canceled'])
                    // ->whereNull('checked_in')
                    ->orderBy('id', 'asc')
                    ->get();
                if (!count($attachables)) {
                    // it means it is the first attach
                    $reservation->number = 'A' . $main_reservation->number;
                } else {
                    $last_key = 0;
                    $limit = "AZZZ";
                    $alphabetArr = [];
                    for ($x = "A", $limit++; $x != $limit; $x++) {
                        $alphabetArr[] = $x;
                    }

                    $last_child_reservation = collect($attachables)->sortByDesc('id')->first();
                    $last_child_reservation_alpha = explode($main_reservation->number, $last_child_reservation->number)[0];
                    $last_child_reservation_alpha_index = array_search($last_child_reservation_alpha, $alphabetArr);
                    $next_child_resservation_alpha = $alphabetArr[$last_child_reservation_alpha_index + 1];
                    $reservation->number = $next_child_resservation_alpha . $main_reservation->number;
                }
            } else {
                $counter = TeamCounter::where('team_id', auth()->user()->current_team_id)->first();
                if (!$counter) {
                    $counter = TeamCounter::create();
                    $counter->forceFill([
                        'team_id' => $reservation->team_id,
                    ])->save();
                }
                $reservation->number = $counter->reservation_num;
                $counter = TeamCounter::where('team_id', auth()->user()->current_team_id)->first();
                $counter->last_reservation_number = $counter->reservation_num;
                $counter->save();
            }
        } else {
            $counter = TeamCounter::where('team_id', auth()->user()->current_team_id)->first();
            if (!$counter) {
                $counter = TeamCounter::create();
                $counter->forceFill([
                    'team_id' => $reservation->team_id,
                ])->save();
            }
            $reservation->number = $counter->reservation_num;
            $counter = TeamCounter::where('team_id', auth()->user()->current_team_id)->first();
            $counter->last_reservation_number = $counter->reservation_num;
            $counter->save();
        }


        $hasIntersection = Reservation::whereUnitId($reservation->unit_id)->where('status', Reservation::STATUS_CONFIRMED)
            //                ->whereIntersectsDateIn($reservation->date_in)
            //                ->whereIntersectsDateOut($reservation->date_out)
            ->whereDateBetweenDates(Carbon::parse($reservation->date_in), Carbon::parse($reservation->date_out))
            ->whereNull('checked_out')
            ->whereNull('deleted_at')
            ->first();


        if ($hasIntersection) {
            return response()->json(['error' => 'This unit has a reservation that intersect with selected dates !'], Response::HTTP_BAD_REQUEST);
        }

        if ($reservation->save()) {

            // if(!$request->has('attachable_reservation_id') && !$request->get('attachable_reservation_id')){

            // }
            if (auth()->user()->currentTeam->reservationDefaultStatusKey() == '' || is_null(auth()->user()->currentTeam->reservationDefaultStatusKey())) {
                $reservation->status = 'confirmed';
            } else {
                $reservation->status = auth()->user()->currentTeam->reservationDefaultStatusKey();
            }
            /** @var Unit $unit */
            $unit = $reservation->unit;


            if($remove_vat){
                $reservation->prices = $request['unit']['reservation']['prices'];
                $reservation->old_prices = [
                    'prices' => $unit->prices(),
                    'min_prices' => $unit->minPrices(),
                    'tourism_percentage' => $unit->getTourismTax(),
                    'vat_parentage' => 0,
                    'ewa_parentage' => $unit->getEwa(),
                ];

            }else{
                $reservation->old_prices = [
                    'prices' => $unit->prices(),
                    'min_prices' => $unit->minPrices(),
                    'tourism_percentage' => $unit->getTourismTax(),
                    'vat_parentage' => $unit->getVat(),
                    'ewa_parentage' => $unit->getEwa(),
                ];
                $reservation->prices = $request['unit']['reservation']['prices'];
            }



            $trick_change_rate = abs($reservation->change_rate);
            if ($trick_change_rate >= 1) {
                if (count($reservation->prices['days'])) {

                    $days_new = [];
                    $new_prices_array = [];
                    foreach ($reservation->prices['days'] as $obj) {
                        $std = new \stdClass;
                        $std->date = $obj['date'];
                        $std->date_name = $obj['date_name'];
                        $std->price_row = (float) number_format($request['unit']['reservation']['prices']['price'] / count($reservation->prices['days']), 5, '.', '');
                        $std->price = (float) number_format($request['unit']['reservation']['prices']['price'] / count($reservation->prices['days']), 5, '.', '');
                        $days_new[] = $std;
                    }

                    $new_prices_array['currency'] = $reservation->prices['currency'];
                    $new_prices_array['days'] = $days_new;
                    $new_prices_array['vat_parentage'] = $reservation->prices['vat_parentage'];
                    $new_prices_array['ewa_parentage'] = $reservation->prices['ewa_parentage'];
                    $new_prices_array['tourism_percentage'] = $reservation->prices['tourism_percentage'];
                    $new_prices_array['min_sub_total'] = $reservation->prices['min_sub_total'];
                    $new_prices_array['total_vat'] = $request['total_vat'];
                    $new_prices_array['total_ewa'] = $request['total_ewa'];
                    $new_prices_array['total_tourism'] = $request['total_ttx'];
                    $new_prices_array['price'] = (float) number_format($data['reservation']['prices']['price'], 2, '.', '');
                    $new_prices_array['sub_total'] = (float) number_format($data['reservation']['prices']['price'], 2, '.', '');
                    $new_prices_array['total_price'] = (float) number_format($data['reservation']['prices']['total_price_raw'], 2, '.', '');
                    $new_prices_array['total_price_raw'] = (float) number_format($data['reservation']['prices']['total_price_raw'], 2, '.', '');
                    //  unset($reservation->prices['days']);

                    $reservation->prices = $new_prices_array;
                } else {
                    $reservation->prices = $unit->getDatesFromRangeWithOldPrices(Carbon::parse($reservation->date_in), Carbon::parse($reservation->date_out), $reservation->old_prices, $reservation->prices['days'], 'create', $reservation->change_rate, $reservation->rent_type, $request->get('rent_type'));
                }
            }


            $reservation->date_out = $reservation->date_out->addDay();

            // $reservation->forceWithdrawFloat($data['reservation']['prices']['total_price_raw'], [
            // $reservation->forceWithdrawFloat($reservation->sub_total + $reservation->ewa_total + $reservation->vat_total, [
            //     'category' => 'reservation',
            //     'statement' => 'Reservation Total Price',
            // ], true, false);

            $meta = [
                'category' => 'create_reservation',
                'statement' => 'Reservation Total Price',
            ];
            $total_price = $data['reservation']['prices']['total_price_raw'];
            DB::transaction(function () use ($reservation, $total_price ,$meta) {

                $negativeAmount = floatval(-1 * $total_price) * 100;
                $currentTimestamp = Carbon::now()->format('Y-m-d H:i:s');

                // create wallet
                $wallet = Wallet::create(
                    [
                        'holder_type' => Reservation::class,
                        'holder_id' => $reservation->id,
                        'name' => 'Default Wallet',
                        'slug' => 'default',
                        'balance' => $negativeAmount,
                        'created_at' => $currentTimestamp,
                        'updated_at' => $currentTimestamp,
                    ]
                );

                // create transaction
                DB::table('transactions')->insert(
                    [
                        'payable_type' => 'App\Reservation',
                        'payable_id' => $reservation->id,
                        'wallet_id' => $wallet->id,
                        'type' => 'withdraw',
                        'transaction_flag' => 'normal',
                        'amount' => $negativeAmount,
                        'confirmed' => 1,
                        'is_public' => 0,
                        'created_by' => auth()->user()->id,
                        'updated_by' => auth()->user()->id,
                        'meta' => json_encode($meta),
                        'uuid' => Str::uuid(),
                        'created_at' => $currentTimestamp,
                        'updated_at' => $currentTimestamp,
                    ]
                );
            });

            // $reservation->wallet->decimal_places = 3;
            // $reservation->wallet->save();
            // $reservation->wallet->refreshBalance();

            $reservation->save();
            // call the function for updating inventory in staah
// if this unit is available to sync then i will update the inventory in staah
            // get team where enable_aiosell is true
            $team_id = auth()->user()->current_team_id;
            // in teams table if the team have enable_aisosell is true
            $enable_aisosell = Team::where('id', $team_id)->first()->enable_aiosell;
            if($enable_aisosell){
            $this->updateAiosellInventory($request);
            }

$enable_staaah = Team::where('id', $team_id)->first()->enable_staaah;
            if ($unit->available_to_sync && $enable_staaah) {
                $this->UpdateStaaahInventory($request);
            }
            event(new ReservationCreated($reservation));
        }
        //mytravel call for updating inventory
        $enable_mytravel = Team::where('id', $team_id)->first()->mytravel_hotel_id;
        if($enable_mytravel != null){
            $my_travel = $this->updateMyTravelInventory($request);
        }
        //end mytravel call for updating inventory

        if ($request['comment'] != null) {

            $commentClass = config('comments.model');
            $comment = new $commentClass;
            $comment->commenter()->associate(auth()->user());
            $comment->commentable()->associate($reservation);
            $comment->comment = $request['comment'];
            $comment->approved = !config('comments.approval_required');
            $comment->save();
        }

        if (count($request->get('reservation_services_selected'))) {
            foreach ($request->get('reservation_services_selected') as $reservation_service) {
                $reservationServiceMapper = new ReservationServiceMapper();
                $reservationServiceMapper->reservation_id = $reservation->id;
                $reservationServiceMapper->reservation_service_id = $reservation_service['id'];
                $reservationServiceMapper->save();
            }
        }
        $response = [
            'success' => true,
            'customer' => $customer,
            'reservation' => $reservation,
        ];
        return response()->json($response);
    }

    /**
     * @todo this is an old version for update reservation rolled back as per islam rashad request
     * Update reservation customer
     * @param Request $request
     */

     //mytravel methods for update reservation and sync


    //end mytravel methods for update reservation and sync
    public function UpdateStaaahInventory($request)
    {
        $unit_id = $request['unit']['id'];
        $start_date = $request['unit']['reservation']['start_date'];
        $end_date = $request['unit']['reservation']['end_date'];
        //convert start date and end date to carbon
        $start_date = Carbon::parse($start_date);
        $end_date = Carbon::parse($end_date);
        $team_id = auth()->user()->current_team_id;
        $unit_category_id = Unit::find($unit_id)->unit_category_id;
        // get the unit category with id = $unit_category_id
        $unit_category = UnitCategory::find($unit_category_id);
        // dd($unit_category);
        $roomsData = [];
        for ($date = $start_date->copy(); $date->lte($end_date); $date->addDay()) {
            $dayData = [
                'from' => $date->format('Y-m-d'),
                'to' => $date->copy()->addDay()->format('Y-m-d'), // End date is the next day
                // You can add more fields here as needed
            ];
            $roomsData[] = $dayData;
        }
        $room = [];
        foreach ($roomsData as $date) {

            $from = $date['from'];
            $to = $date['to'];
            $dayName = Carbon::parse($from)->format('l');
            $day_Name_price = strtolower($dayName) . '_day_price';
            $price = $unit_category[$day_Name_price];

            $all_units = Unit::where('unit_category_id', $unit_category_id)
            ->where('status', '!=', 3)
            ->where('deleted_at', null)->pluck('id')->toArray();
            $available_arr = [];
            foreach($all_units as $unit_id){
                $hasIntersectionWorkable = checkIfUnitHasReservation($unit_id, Carbon::parse($from));
                if(!$hasIntersectionWorkable){
                    array_push($available_arr, $unit_id);
                }



            }
            $available_units = count($available_arr);
            // dd($available_units);
            $ewa_percantage = getEwaPercentageForUnit($team_id);
            $tax_percentage = getVatPercentageForUnit($team_id);
            $ewa_total = getEwaTotalForUnit($price, $ewa_percantage);
            $tax_total = getVatTotalForUnit($price, $ewa_total, $tax_percentage);
            $total_price = $price + $ewa_total + $tax_total;

            $data = [

                "roomid" => $unit_category_id,
                "date" => [
                    [
                        "from" => $from,
                        "to" => $from,
                        // "rate" => [
                        //     [
                        //         "rateplanid" => "DEF"
                        //     ]
                        // ],
                        "roomstosell" => $available_units,
                        // "price" => [
                        //     [
                        //         "NumberOfGuests" => "1",
                        //         "value" => $total_price
                        //     ]
                        // ],

                    ]
                ]

            ];

            // push data to room array
            array_push($room, $data);

        }
        $data = [
            "hotelid" => $team_id,
            "room" => $room
        ];
        // dd($data);
        $url = env('STAAH_MEDIATOR_API_URL') . '/api/v1/availability';
        $curl = curl_init();

        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            )
        );

        $response = curl_exec($curl);
        curl_close($curl);

    }
    public function update_reservation(Request $request)
    {
        $isPriceByDayEnabled = auth()->user()->teams->where('id', auth()->user()->current_team_id)->first()->check_calculate_price_by_day_enable;
        $reservation = Reservation::find($request->id);
        $old_unit_id = $reservation->unit_id;
        $old_date_in = $reservation->date_in;
        $old_date_out = $reservation->date_out;
        $old_unit_category_id = $reservation->unit->unit_category_id;
        $old_unit_category = UnitCategory::find($old_unit_category_id);
        $new_unit = $request->unit_id;

        $reservation->source_id = $request->source_id;
        $reservation->source_num = $request->source_num;
        $reservation->special_prices = json_encode($request->specialPricesCollector);
        $reservation->date_in = $request->date_in;
        $reservation->date_out = $request->date_out;
        $price_change_mode = $request->price_change_mode;
        $total_locked_amount = $request->total_locked_amount;
        $locked_days = $request->locked_days;

        if ($request->get('rent_type') != $reservation->rent_type) {
            $reservation->notPublicTransactions()->delete();
            $reservation->rent_type = $request->rent_type;
            $reservation->total_price = $request->prices['total_price_raw'];
            $reservation->sub_total = $request->prices['subtotal'];
            $reservation->vat_total = $request->prices['total_vat'];
            $reservation->ewa_total = $request->prices['total_ewa'];
            $reservation->ttx_total = $request->prices['total_tourism'];
            $day_start_time = \App\Handlers\Settings::get('day_start');
            $date_in = Carbon::parse($reservation->date_in)->toDateString();
            $day_end_time = \App\Handlers\Settings::get('day_end');
            $date_out = Carbon::parse($reservation->date_out)->toDateString();
            $combinedDateInTime = date('Y-m-d H:i:s', strtotime("$date_in $day_start_time"));
            $combinedDateOutTime = date('Y-m-d H:i:s', strtotime("$date_out $day_end_time"));

            $reservation->date_in_time = $combinedDateInTime;
            $reservation->date_out_time = $combinedDateOutTime;

            $existing_prices    = $reservation->prices;
            $reservation->prices = $reservation->unit->getDatesFromRange(Carbon::parse($reservation->date_in), Carbon::parse($reservation->date_out));

            //$reservation->prices = $this->filterExistingPriceChange($existing_prices, $reservation->prices);
            //mutate reservation root level prices; prices -> reservation (root level)
            // $reservation->total_price   =  $reservation->prices['total_price'];
            // $reservation->sub_total     = $reservation->prices['sub_total'];
            // $reservation->vat_total     = $reservation->prices['total_vat'];
            // $reservation->ewa_total     = $reservation->prices['total_ewa'];

            if ($reservation->old_prices) {
                $old_prices = $reservation->unit->getDatesFromRangeWithOldPrices(Carbon::parse($reservation->date_in), Carbon::parse($reservation->date_out), $reservation->old_prices, 0, $reservation->rent_type, $request->rent_type);
                $old_prices_price = $old_prices['price'] > 0 ? $old_prices['price'] : 1;
                $reservation->change_rate = (($reservation->sub_total / $old_prices_price) - 1) * 100;
                $reservation->prices = $reservation->unit->getDatesFromRangeWithOldPrices(Carbon::parse($reservation->date_in), Carbon::parse($reservation->date_out), $reservation->old_prices, $reservation->change_rate, $reservation->rent_type, $request->rent_type);
            }
            $reservation->action_type = Reservation::ACTION_UPDATERESERVATION;
            $reservation->save();

            $reservation->forceWithdrawFloat(floatval($request->prices['total_price_raw']), [
                'category' => 'reservation',
                'statement' => 'Reservation Total Price',
            ], true, false);

            $reservation->wallet->refreshBalance();

            if ($reservation->group_reservation) {
                groupReservationHandler($reservation->group_reservation);
            }

            event(new ShomosReservationUpdated($reservation));
            event(new ReservationUpdated($reservation));

            return response()->json('true');
        }

        $reservation->rent_type = $request->rent_type;
        if ($request->unit_id) {
            $reservation->unit_id = $request->unit_id;
        }

        $reservation->change_rate = $request->change_rate;

        $existing_total_price = $reservation->total_price;

        $division = $request->prices['total_price_raw'] - $reservation->total_price;

        $reservation->total_price = $request->prices['total_price_raw'];
        $reservation->sub_total = $request->prices['subtotal'];
        $reservation->vat_total = $request->prices['total_vat'];
        $reservation->ewa_total = $request->prices['total_ewa'];
        $reservation->ttx_total = $request->prices['total_tourism'];

        /* Add-On to flush date_in_time && date_out_time new columns in reservations tbl to be used in Total Revenues, Taxes & Fees Report */
        $day_start_time = \App\Handlers\Settings::get('day_start');
        $date_in = Carbon::parse($reservation->date_in)->toDateString();
        $day_end_time = \App\Handlers\Settings::get('day_end');
        $date_out = Carbon::parse($reservation->date_out)->toDateString();
        $combinedDateInTime = date('Y-m-d H:i:s', strtotime("$date_in $day_start_time"));
        $combinedDateOutTime = date('Y-m-d H:i:s', strtotime("$date_out $day_end_time"));

        $reservation->date_in_time = $combinedDateInTime;
        $reservation->date_out_time = $combinedDateOutTime;

        $existing_prices    = $reservation->prices;
        $reservation->prices = $reservation->unit->getDatesFromRange(Carbon::parse($reservation->date_in), Carbon::parse($reservation->date_out));
        //check for existing change in price by day
        //settings.calculatePriceByDay ->
        if($isPriceByDayEnabled &&  $reservation->rent_type == 1) {
            $filterExistingPrice =  $this->filterExistingPriceChange(
                    $existing_prices,
                        $reservation->prices
            );
            $reservation->prices = $filterExistingPrice->dates;
        }
        //end settings.calculatePriceByDay

        if ($reservation->old_prices) {
            $unit = Unit::with('unit_category')->find($request->unit_id);
            $special_prices_array = [];
            $special_prices = $unit->unit_category->special_prices;
            if(count($special_prices)){
                foreach ($special_prices as $special_price) {
                    $special_price_period = CarbonPeriod::create($special_price->start_date, $special_price->end_date);
                    foreach ($special_price_period as $special_price_date) {
                        // saftey in case special prices added with empty days prices
                        if (floatval($special_price->days_prices[Carbon::parse($special_price_date)->format('l')])) {
                            $special_prices_array [Carbon::parse($special_price_date)->format('Y-m-d') ] = floatval($special_price->days_prices[Carbon::parse($special_price_date)->format('l')]);
                        } else {
                            $special_prices_array [Carbon::parse($special_price_date)->format('Y-m-d') ] = floatval($unit->dayPrice(Carbon::parse($special_price_date)->format('l')));
                        }
                    }
                }
            }

            if(count($special_prices_array)){
                $old_prices = $unit->getDatesFromRangeWithOldPricesAndSpecialPrices(Carbon::parse($reservation->date_in), Carbon::parse($reservation->date_out), $reservation->old_prices, $reservation->change_rate, $reservation->rent_type, $request->get('rent_type'),$special_prices_array);
            }else{
                $old_prices = $unit->getDatesFromRangeWithOldPrices(Carbon::parse($reservation->date_in), Carbon::parse($reservation->date_out), $reservation->old_prices, $reservation->change_rate, $reservation->rent_type, $request->get('rent_type'));
            }
            // $old_prices = $reservation->unit->getDatesFromRangeWithOldPrices(Carbon::parse($reservation->date_in), Carbon::parse($reservation->date_out), $reservation->old_prices, 0, $reservation->rent_type, $request->rent_type);
            $old_prices_price = $old_prices['price'] > 0 ? $old_prices['price'] : 1;

            $reservation->change_rate = (($reservation->sub_total / $old_prices_price) - 1) * 100;

            if(count($special_prices_array)){
                $prices = $unit->getDatesFromRangeWithOldPricesAndSpecialPrices(Carbon::parse($reservation->date_in), Carbon::parse($reservation->date_out), $reservation->old_prices, $reservation->change_rate, $reservation->rent_type, $request->get('rent_type'),$special_prices_array);
            }else{
                $prices = $unit->getDatesFromRangeWithOldPrices(Carbon::parse($reservation->date_in), Carbon::parse($reservation->date_out), $reservation->old_prices, $reservation->change_rate, $reservation->rent_type, $request->get('rent_type'));
            }

            //settings.calculatePriceByDay ->
            if($isPriceByDayEnabled && $reservation->rent_type == 1) {
                switch($price_change_mode) {
                    case "input":
                        $prices = $this->mergePricesWithFilterExistingPriceInput($prices,
                        $filterExistingPrice->datesAsIndex,
                        $total_locked_amount,
                        $locked_days,
                        $request->prices['total_price_raw']);
                        break;
                    case "nightCounter":
                        $prices = $this->mergePricesWithFilterExistingPrice($prices, $filterExistingPrice->datesAsIndex);
                        break;
                }


                $reservation->total_price   = $prices['total_price'];
                $reservation->sub_total     = $prices['sub_total'];
                $reservation->vat_total     = $prices['total_vat'];
                $reservation->ewa_total     = $prices['total_ewa'];


                //$division = $request->prices['total_price_raw'] - $reservation->total_price;
                //merge existing price with prices

            }
            //end settings.calculatePriceByDay ->
            // $reservation->prices = $reservation->unit->getDatesFromRangeWithOldPrices(Carbon::parse($reservation->date_in), Carbon::parse($reservation->date_out), $reservation->old_prices, $reservation->change_rate, $reservation->rent_type, $request->rent_type);
            $reservation->prices = $prices;

        }

        $reservation->action_type = Reservation::ACTION_UPDATERESERVATION;

        //dd(  $reservation->prices);

        // dd([
        //     $division,
        //     $request->prices['total_price_raw'],
        //     $reservation->total_price ,
        //     $existing_total_price
        // ]);

        $reservation->save();

        $this->updateReservationWallet($reservation, $division);

        if ($reservation->group_reservation) {
            groupReservationHandler($reservation->group_reservation);
        }

        event(new ShomosReservationUpdated($reservation));
        event(new ReservationUpdated($reservation));

        if ($request->get('reason')) {

            // unit has changed
            if ($request->unit_id != $old_unit_id) {
                $reservation->special_prices = json_encode([]);
                $reservation->save();
                if (count($reservation->invoices)) {
                    foreach ($reservation->invoices as $invoice) {
                        if (is_null($invoice->unit_id)) {
                            $invoice->unit_id = $old_unit_id;
                            $invoice->room_was_changed = 1;
                            $invoice->save();
                        }
                    }
                }
            }

            // then we need to change all transactions json column holding unit number
            $this->handleUnitNumberInTransactionsJsonField($reservation);

            if ($reservation->checked_in && intval(getSettingItem($reservation->team_id, 'automatic_under_cleaning'))) {
                $this->convertUnitStatusToUnderCleaning($old_unit_id);
            }
        }
        // if this unit is available to sync then i will update the inventory in staah



        if (count($request->get('reservation_services_selected'))) {

            if ($reservation->reservationFreeServices) {
                foreach ($reservation->reservationFreeServices as $reservation_service) {
                    $reservation_service->delete();
                }
            }

            foreach ($request->get('reservation_services_selected') as $reservation_service) {
                $reservationServiceMapper = new ReservationServiceMapper();
                $reservationServiceMapper->reservation_id = $reservation->id;
                $reservationServiceMapper->reservation_service_id = $reservation_service['id'];
                $reservationServiceMapper->save();
            }
        } else {
            if ($reservation->reservationFreeServices) {
                foreach ($reservation->reservationFreeServices as $reservation_service) {
                    $reservation_service->delete();
                }
            }
        }

        //mytravel call for updating inventory
        if ($reservation->team->mytravel_hotel_id) {
            $mytravel = $this->UpdateMyTravelInventoryOnupdate($old_date_in, $old_date_out, $reservation);
            $update_reservation_on_mytravel = $this->updateMyTravelReservation($reservation);
        }
        //end mytravel call for updating inventory

        $team_id = auth()->user()->current_team_id;
$enable_staaah = Team::where('id', $team_id)->first()->enable_staaah;
        if ($reservation->unit->available_to_sync && $enable_staaah) {
            $this->UpdateStaaahInventoryOnupdate($old_date_in, $old_date_out, $reservation);
        }
        // in teams table if the team have enable_aisosell is true
        $enable_aisosell = Team::where('id', $team_id)->first()->enable_aiosell;
                if( $enable_aisosell){
                    $this->updateAiosellInventoryOnupdate($old_date_in,$old_date_out, $reservation , $old_unit_category_id , $old_unit_category , $new_unit);
                }
        $reservation->wallet->refreshBalance();

        return response()->json('true');
    }
    public function updateMyTravelReservation($request){
        $fandaqah_ref_id = $request->attachable_id ? $request->attachable_id : $request->id;
        if($request->attachable_id){
            $total = 0;
            $total_amount = Reservation::where('attachable_id', $request->attachable_id)->sum('total_price');
            $main_reservation_amount = Reservation::find($request->attachable_id)->total_price;
            $total = $total_amount + $main_reservation_amount;
        }else{
            $total = $request->total_price;
        }
        $data = [
            'fandaqah_ref_id' => $fandaqah_ref_id,
            'fandaqah_res_number' => $request->number,
            'reservation_total_price' => $request->total_price,
            'category_id' => $request->unit->unit_category_id,
            'start_date' => $request->date_in,
            'end_date' => $request->date_out,
            'price' => $total,
            'active' => 1,
            'note_to_customer' => '',
            'note_to_admin' => '',
            'is_instant' => 1,
            'number' => 1,
            'create_user' => 1,
            'update_user' => 1,
            'status' => "update_reservation",
            'original_date_in' =>$request->date_in,
            'original_date_out' =>$request->date_out,
            'is_old_date' => 0,
            'category_id' => $request->unit->unit_category_id
        ];

        $curl = curl_init();
            $url = env('MYTRAVEL_API_URL') . '/api/hotel/booking/update';
            $key = env('MY_TRAVEL_KEY');

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 400,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'key: ' . $key
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            return $response;
    }
    public function UpdateMyTravelInventoryOnupdate($old_date_in, $old_date_out, $reservation)
    {

        $first_date_in = Carbon::parse($old_date_in);
        $first_date_out = Carbon::parse($old_date_out);
        $second_date_in = Carbon::parse($reservation['date_in']);
        $second_date_out = Carbon::parse($reservation['date_out']);

        $team_id = $reservation->team_id;
        $unit_category_id = $reservation->unit->unit_category_id;
        $unit_category = UnitCategory::find($unit_category_id);

        $send = $this->SendtoMyTravel($reservation, $second_date_in, $second_date_out, $team_id, $unit_category_id, $unit_category ,  0);
        $send = $this->SendtoMyTravel($reservation, $first_date_in, $first_date_out, $team_id, $unit_category_id, $unit_category, 1);
     }
    public function SendtoMyTravel($reservation, $date_in, $date_out, $team_id, $unit_category_id, $unit_category , $old_date)
    {
        $roomsData = [];
        for ($date = $date_in->copy(); $date->lte($date_out); $date->addDay()) {
            $dayData = [
                'from' => $date->format('Y-m-d'),
            ];
            $roomsData[] = $dayData;
        }

        foreach ($roomsData as $date) {

            $from = $date['from'];
            $dayName = Carbon::parse($from)->format('l');
            $day_Name_price = strtolower($dayName) . '_day_price';
            $specialPrice = SpecialPrice::where('unit_category_id', $unit_category_id)->where('start_date', '<=', $date['from'])->where('end_date', '>=', $date['from'])->where('enabled', true)->first();
            if($specialPrice){
                $price = $specialPrice->days_prices[$dayName];
            }else{

                $price = $unit_category[$day_Name_price];
            }
            $all_units = Unit::where('unit_category_id', $unit_category_id)
            ->where('status', '!=', 3)
            ->where('deleted_at', null)->pluck('id')->toArray();

            $available_arr = [];
            foreach($all_units as $unit_id){
                $hasIntersectionWorkable = checkIfUnitHasReservation($unit_id, Carbon::parse($from));
                if(!$hasIntersectionWorkable){
                    array_push($available_arr, $unit_id);
                }
            }

            $available_units = count($available_arr);
            $ewa_percantage = getEwaPercentageForUnit($team_id);
            $tax_percentage = getVatPercentageForUnit($team_id);
            $ewa_total = getEwaTotalForUnit($price, $ewa_percantage);
            $tax_total = getVatTotalForUnit($price, $ewa_total, $tax_percentage);
            $total_price = $price + $ewa_total + $tax_total;

            $data = [
                'fandaqah_ref_id' => $reservation->id,
                'reservation_total_price' => $reservation->total_price,
                'category_id' => $unit_category_id,
                'start_date' => $from,
                'end_date' => $from,
                'price' => $total_price,
                'active' => 1,
                'note_to_customer' => '',
                'note_to_admin' => '',
                'is_instant' => 1,
                'number' => $available_units,
                'create_user' => 1,
                'update_user' => 1,
                'status' => "update_reservation",
                'original_date_in' =>$date_in->format('Y-m-d'),
                'original_date_out' =>$date_out->format('Y-m-d'),
                'is_old_date' => $old_date

            ];

            $curl = curl_init();
            $url = env('MYTRAVEL_API_URL') . '/api/hotel/room/updateInventory';
            $key = env('MY_TRAVEL_KEY');

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 400,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'key: ' . $key
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);

        }
    }


    public function UpdateStaaahInventoryOnupdate($old_date_in, $old_date_out, $reservation)
    {

        $first_date_in = Carbon::parse($old_date_in);
        $first_date_out = Carbon::parse($old_date_out);
        $second_date_in = Carbon::parse($reservation['date_in']);
        $second_date_out = Carbon::parse($reservation['date_out']);
        $team_id = $reservation->team_id;
        $unit_category_id = $reservation->unit->unit_category_id;
        $unit_category = UnitCategory::find($unit_category_id);

        $this->SendtoStaaah($second_date_in, $second_date_out, $team_id, $unit_category_id, $unit_category);
        $this->SendtoStaaah($first_date_in, $first_date_out, $team_id, $unit_category_id, $unit_category);


    }



//aiosell

public function UpdateAiosellInventoryOncancel($request){

    $team_id = $request->team_id;
    $unit_category_id = $request->unit->unit_category_id;
    $date_in = $request->date_in;
    $date_out = $request->date_out;
    // convert it to carbon
    $date_in = Carbon::parse($date_in);
    $date_out = Carbon::parse($date_out);
    // get the unit category where id = unit_category_id
    $unit_category = UnitCategory::find($unit_category_id);

    $roomsData = [];
    for ($date = $date_in->copy(); $date->lte($date_out); $date->addDay()) {
        $dayData = [
            'from' => $date->format('Y-m-d'),
            'to' => $date->copy()->addDay()->format('Y-m-d'), // End date is the next day
            // You can add more fields here as needed
        ];
        $roomsData[] = $dayData;

    }
    $room = [];
    foreach ($roomsData as $date) {

        $from = $date['from'];
        $to = $date['to'];
        $dayName = Carbon::parse($from)->format('l');


        $all_units = Unit::where('unit_category_id', $unit_category_id)
        ->where('status', '!=', 3)
        ->where('deleted_at', null)->pluck('id')->toArray();
        $available_arr = [];
        foreach($all_units as $unit_id){
            $hasIntersectionWorkable = checkIfUnitHasReservationAiosell($unit_id, Carbon::parse($from));
            if(!$hasIntersectionWorkable){
                array_push($available_arr, $unit_id);
            }



        }
        $available_units = count($available_arr);

        // dd($available_units);
        $unit_category_count = $unit_category->synced_units;
        if($available_units >= $unit_category_count){
            $available_units = $unit_category_count;
        }else{
            $available_units = $available_units;
        }

        $data =

        [
            "startDate" => $from,
            "endDate" => $from,
            "rooms" => [
                [
                    "available" => $available_units ,
                    "roomCode" => strval($unit_category_id)
                ]
            ]
        ]

;

            // push the data to the room array
            array_push($room, $data);




    }


    $data = [
        "hotelCode" => auth()->user()->current_team_id,
        "updates" => $room
    ];




    $URL=env('AIOSELL_MEDIATOR_API_URL').'/api/pms/data';
    $username = env('AIOSELL_MEDIATOR_API_USERNAME');
    $password = env('AIOSELL_MEDIATOR_API_PASSWORD');



    $curl = curl_init();

    curl_setopt_array($curl, array(
       CURLOPT_URL => $URL,
       CURLOPT_RETURNTRANSFER => true,
       CURLOPT_ENCODING => '',
       CURLOPT_MAXREDIRS => 10,
       CURLOPT_TIMEOUT => 0,
       CURLOPT_FOLLOWLOCATION => true,
       CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
       CURLOPT_CUSTOMREQUEST => 'POST',
       CURLOPT_POSTFIELDS => json_encode($data),
       CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Basic '.base64_encode($username.':'.$password)
       ),
    ));

    $response2 = curl_exec($curl);

    curl_close($curl);




}

public function updateAiosellInventory($request){

    $unit_id = $request['unit']['id'];
    $start_date = $request['unit']['reservation']['start_date'];
    $end_date = $request['unit']['reservation']['end_date'];
    //convert start date and end date to carbon
    $start_date = Carbon::parse($start_date);
    $end_date = Carbon::parse($end_date);
    $team_id = auth()->user()->current_team_id;
    $unit_category_id = Unit::find($unit_id)->unit_category_id;
    // get the unit category with id = $unit_category_id
    $unit_category = UnitCategory::find($unit_category_id);
    // dd($unit_category);
    $roomsData = [];
    for ($date = $start_date->copy(); $date->lte($end_date); $date->addDay()) {
        $dayData = [
            'from' => $date->format('Y-m-d'),
            'to' => $date->copy()->addDay()->format('Y-m-d'), // End date is the next day
            // You can add more fields here as needed
        ];
        $roomsData[] = $dayData;
    }
    $room = [];
    foreach ($roomsData as $date) {

        $from = $date['from'];
        $to = $date['to'];
        $dayName = Carbon::parse($from)->format('l');

        $all_units = Unit::where('unit_category_id', $unit_category_id)
        ->where('status', '!=', 3)
        ->where('deleted_at', null)->pluck('id')->toArray();
        $available_arr = [];
        foreach($all_units as $unit_id){
            $hasIntersectionWorkable = checkIfUnitHasReservationAiosell($unit_id, Carbon::parse($from));
            if(!$hasIntersectionWorkable){
                array_push($available_arr, $unit_id);
            }



        }
        $available_units = count($available_arr);
        $unit_category_count = $unit_category->synced_units;
        if($available_units >= $unit_category_count){
            $available_units = $unit_category_count;
        }else{
            $available_units = $available_units;
        }

        $data =

        [
            "startDate" => $from,
            "endDate" => $from,
            "rooms" => [
                [
                    "available" => $available_units ,
                    "roomCode" => strval($unit_category_id)
                ]
            ]
        ]

;

            // push the data to the room array
            array_push($room, $data);




    }


    $data = [
        "hotelCode" => auth()->user()->current_team_id,
        "updates" => $room
    ];




    $URL=env('AIOSELL_MEDIATOR_API_URL').'/api/pms/data';
    $username = env('AIOSELL_MEDIATOR_API_USERNAME');
    $password = env('AIOSELL_MEDIATOR_API_PASSWORD');



    $curl = curl_init();

    curl_setopt_array($curl, array(
       CURLOPT_URL => $URL,
       CURLOPT_RETURNTRANSFER => true,
       CURLOPT_ENCODING => '',
       CURLOPT_MAXREDIRS => 10,
       CURLOPT_TIMEOUT => 0,
       CURLOPT_FOLLOWLOCATION => true,
       CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
       CURLOPT_CUSTOMREQUEST => 'POST',
       CURLOPT_POSTFIELDS => json_encode($data),
       CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Basic '.base64_encode($username.':'.$password)
       ),
    ));

    $response2 = curl_exec($curl);

    curl_close($curl);

}

public function updateAiosellInventoryOnupdate($old_date_in ,$old_date_out, $reservation , $old_unit_category_id , $old_unit_category , $new_unit){
    $first_date_in = Carbon::parse($old_date_in);
    $first_date_out = Carbon::parse($old_date_out);
    $second_date_in = Carbon::parse($reservation['date_in']);
    $second_date_out = Carbon::parse($reservation['date_out']);

    $team_id = $reservation->team_id;
    $unit_id = $new_unit;

    $unit_category_id = Unit::find($unit_id)->unit_category_id;
    $unit_category = UnitCategory::find($unit_category_id);

    $this->sendToAiosell($second_date_in, $second_date_out, $team_id, $unit_category_id, $unit_category);
    $this->sendToAiosell($first_date_in, $first_date_out, $team_id, $unit_category_id, $unit_category);
    if($old_unit_category_id != $unit_category_id){
        $this->sendToAiosell($second_date_in, $second_date_out, $team_id, $old_unit_category_id, $old_unit_category);
    }

}

public function sendToAiosell($date_in, $date_out, $team_id, $unit_category_id, $unit_category){
    $roomsData = [];
    for ($date = $date_in->copy(); $date->lte($date_out); $date->addDay()) {
        $dayData = [
            'from' => $date->format('Y-m-d'),
            'to' => $date->copy()->addDay()->format('Y-m-d'), // End date is the next day
            // You can add more fields here as needed
        ];
        $roomsData[] = $dayData;

    }
    $room = [];
    foreach ($roomsData as $date) {
        $from = $date['from'];
        $to = $date['to'];

        $all_units = Unit::where('unit_category_id', $unit_category_id)
        ->where('status', '!=', 3)
        ->where('deleted_at', null)->pluck('id')->toArray();
        $available_arr = [];
        foreach($all_units as $unit_id){
            $hasIntersectionWorkable = checkIfUnitHasReservationAiosell($unit_id, Carbon::parse($from));
            if(!$hasIntersectionWorkable){
                array_push($available_arr, $unit_id);
            }



        }
        $available_units = count($available_arr);
        $unit_category_count = $unit_category->synced_units;
        if($available_units >= $unit_category_count){
            $available_units = $unit_category_count;
        }else{
            $available_units = $available_units;
        }


        $data =

        [
            "startDate" => $from,
            "endDate" => $from,
            "rooms" => [
                [
                    "available" => $available_units ,
                    "roomCode" => strval($unit_category_id)
                ]
            ]
        ]

;
        array_push($room, $data);

    }

    $data = [
        "hotelCode" => auth()->user()->current_team_id,
        "updates" => $room
    ];




    $URL=env('AIOSELL_MEDIATOR_API_URL').'/api/pms/data';
    $username = env('AIOSELL_MEDIATOR_API_USERNAME');
    $password = env('AIOSELL_MEDIATOR_API_PASSWORD');



    $curl = curl_init();

    curl_setopt_array($curl, array(
       CURLOPT_URL => $URL,
       CURLOPT_RETURNTRANSFER => true,
       CURLOPT_ENCODING => '',
       CURLOPT_MAXREDIRS => 10,
       CURLOPT_TIMEOUT => 0,
       CURLOPT_FOLLOWLOCATION => true,
       CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
       CURLOPT_CUSTOMREQUEST => 'POST',
       CURLOPT_POSTFIELDS => json_encode($data),
       CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Basic '.base64_encode($username.':'.$password)
       ),
    ));

    $response2 = curl_exec($curl);

    curl_close($curl);

}



    public function SendtoStaaah($date_in, $date_out, $team_id, $unit_category_id, $unit_category)
    {
        $roomsData = [];
        for ($date = $date_in->copy(); $date->lte($date_out); $date->addDay()) {
            $dayData = [
                'from' => $date->format('Y-m-d'),
                'to' => $date->copy()->addDay()->format('Y-m-d'), // End date is the next day
                // You can add more fields here as needed
            ];
            $roomsData[] = $dayData;

    }
    $room = [];
    foreach ($roomsData as $date) {
        $from = $date['from'];
        $to = $date['to'];
        $dayName = Carbon::parse($from)->format('l');
        $day_Name_price = strtolower($dayName) . '_day_price';
        $price = $unit_category[$day_Name_price];
        // get the number of units that is no reservation for this from to date and the unit category id is the same as the request and available to sync is true

        $all_units = Unit::where('unit_category_id', $unit_category_id)
        ->where('status', '!=', 3)
        ->where('deleted_at', null)->pluck('id')->toArray();
        $available_arr = [];
        foreach($all_units as $unit_id){
            $hasIntersectionWorkable = checkIfUnitHasReservation($unit_id, Carbon::parse($from));
            if(!$hasIntersectionWorkable){
                array_push($available_arr, $unit_id);
            }



        }
        $available_units = count($available_arr);
        // dd($available_units);
        // if this team have tax and ewa
        $team_id = auth()->user()->current_team_id;
        $ewa_percantage = getEwaPercentageForUnit($team_id);
        $tax_percentage = getVatPercentageForUnit($team_id);
        $ewa_total = getEwaTotalForUnit($price, $ewa_percantage);
        $tax_total = getVatTotalForUnit($price, $ewa_total, $tax_percentage);
        $total_price = $price + $ewa_total + $tax_total;

            $data = [

            "roomid" => $unit_category['id'],
            "date" => [
                [
                    "from" => $from,
                    "to" => $from,
                    // "rate" => [
                    //     [
                    //         "rateplanid" => "DEF"
                    //     ]
                    // ],
                    "roomstosell" => $available_units,
                    // "price" => [
                    //     [
                    //         "NumberOfGuests" => "1",
                    //         "value" => $total_price
                    //     ]
                    // ],

                ]
            ]
        ];
        array_push($room, $data);

        }
        $data = [
            "hotelid" => $team_id,
            "room" => $room
        ];
        // dd($data);
        var_dump($data);
        $url = env('STAAH_MEDIATOR_API_URL') . '/api/v1/availability';
        $curl = curl_init();

    curl_setopt_array(
        $curl,
        array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        )
    );

        $response = curl_exec($curl);

        curl_close($curl);


}

    function convertUnitStatusToUnderCleaning($unit_id)
    {
        $unit = Unit::find($unit_id);
        $unit->status = 2;
        $unit->save();
    }

    public function handleUnitNumberInTransactionsJsonField($reservation)
    {
        $transactions = $reservation->transactions;
        $meto = [];
        $mota = [];
        foreach ($transactions as $transaction) {
            $meta = $transaction->meta;
            $new_meta = [
                'date' => $meta['date'],
                'from' => $meta['from'],
                'note' => $meta['note'],
                'type' => $meta['type'],
                'category' => $meta['category'],
                'employee' => $meta['employee'],
                'reference' => $meta['reference'],
                'payment_type' => $meta['payment_type']
            ];
            if ($transaction->type == 'withdraw') {
                $new_meta['received_by'] = $meta['received_by'];
            }
            $old_statement = $meta['statement'];
            $exploded = explode(' - ', $old_statement);

            if (isset($exploded[0])) {
                $new_meta['statement'] = $exploded[0] . ' - ' . 'وحدة' . ' - ' . $reservation->unit->unit_number;
            } else {
                // just in case if the explode failed
                $new_meta['statement'] = $old_statement;
            }
            // do our final update
            $transaction->meta = $new_meta;
            $transaction->save();
        }

    }
    public function hashTransactionId(Request $request)
    {
        return Hashids::encode($request->transaction_id);
    }

    /**
     * Soft Delete Service From Transactions
     * @param \Illuminate\Http\Request $request
     */
    public function deleteService(Request $request)
    {

        // $reservation = Reservation::find($request->reservation_id);
        // $reservation->wallet->refreshBalance();

        $transaction = Transaction::with('service_log')->find($request->id);
        $user = Auth::user();
        if($transaction){
            if ($transaction->is_freezed != null && $transaction->is_freezed == 1 && !$user->hasPermissionTo('edit/delete freezed transactions after business day')) {
                return response()->json([
                    'status' => false,
                    'message' =>__('messages.transaction_frozen')
                ], 403);
            }
        }
        $transaction->service_log()->delete();

        $transaction->delete();
        $transaction->payable->wallet->refreshBalance();

        if ($transaction->payable->group_reservation) {
            groupReservationHandler($transaction->payable->group_reservation);
        }

        return response()->json(['status' => 'service_deleted']);
    }

    /**
     * @description : This Function Handle Reservation Statistics Blocks
     * @param \Illuminate\Http\Request $request
     * @see : nova-components/Calendar/resources/js/components/block_helpers/
     * @see : nova-components/Calendar/resources/js/components/Reservations.vue
     * @return \Illuminate\Http\JsonResponse
     */
    public function reservationStatisticsBlocks(NovaRequest $request)
    {



        $query = Reservation::query();
        $filters = json_decode(base64_decode(\request('filters')), true);
        collect($filters)->each(function ($filter) use ($request, $query) {
            if (!is_null($filter['value']) and !empty ($filter['value'])) {
                (new $filter['class'])->apply($request, $query, $filter['value']);
            }
        });

        $query = \App\Nova\Reservation::indexQuery($request, $query);



        $reservations = $query->get()->pluck('id')->toArray();

        // The Story -_-
        // So Here i need to fetch all transactions Deposit , Withdraw etc and make all my calculations inside this function
        // then return back the result to our component , and every time filters will be changed the function will be recalled
        // inside vue js to handle new reservations ids  -_-

        $reservations_ids = $reservations;
        $current_team_id = auth()->user()->current_team_id;
        $withdraw_transactions_amount_arr = array();
        $deposit_transactions_amount_arr = array();
        $total_creditor = array();
        $total_debtor = array();
        $income_array = array();
        $rent_array = array();
        $tax_array = array();

        // Count the incoming reservation ids
        if (count($reservations_ids)) {
            $total_amount_array = [];
            $reservations_services_sum = array();

            $reservations = Reservation::whereIn('id', $reservations_ids)->get();

            foreach ($reservations as $reservation) {

                // Below Pre Steps for Total Creditor and Total Debtor  -- لحساب المدين والدائن الاجمالى
                //                $reservation = Reservation::find($reservation_id) ;

                $reservations_services_sum[] = $reservation->getServicesWithoutTaxesSum();
                $total_amount_array[] = $reservation->total_price;
                $income_array[] = $reservation->sub_total + $reservation->getServicesWithoutTaxesSum();
                $rent_array[] = $reservation->sub_total;
                $tax_array[] = $reservation->ewa_total + $reservation->vat_total + $reservation->ttx_total + $reservation->getServicesTaxesSum();
                $balance = $reservation->balance;
                if ($balance > 0) {
                    // So it's Creditor
                    $total_creditor[] = $balance;
                } else {
                    // So it's Debtor
                    $total_debtor[] = $balance;
                }
                // Fetch All Withdraw Transactions Amount -- المصروفات
                $withdraw_transactions_amount = Transaction::where('payable_id', $reservation->id)
                    ->where('type', 'withdraw')
                    ->where('is_public', 1)
                    ->whereHas('reservation', function ($reservation) use ($current_team_id) {
                        $reservation->whereHas('creator', function ($creator) use ($current_team_id) {
                            $creator->where('current_team_id', $current_team_id);
                        });
                    })->sum('amount');
                $withdraw_transactions_amount_arr[] = $withdraw_transactions_amount;

                // Fetch All Withdraw Transactions Amount -- المقبوضات
                $deposit_transactions_amount = Transaction::where('payable_id', $reservation->id)
                    ->where('type', 'deposit')
                    ->where('is_public', 1)
                    ->whereHas('reservation', function ($reservation) use ($current_team_id) {
                        $reservation->whereHas('creator', function ($creator) use ($current_team_id) {
                            $creator->where('current_team_id', $current_team_id);
                        });
                    })->sum('amount');
                $deposit_transactions_amount_arr[] = $deposit_transactions_amount;
            }

            // The total deposit  اجمالى المقبوضات
            $total_receipts = $this->number_format_abs(array_sum($deposit_transactions_amount_arr));
            // The total withdraw اجمالى المصروفات
            $total_cost = $this->number_format_abs(array_sum($withdraw_transactions_amount_arr));
            // The total credit  الرصيد = اجمالى المقبوضات - اجمالى المصروفات

            $totalCredit = $this->number_format_abs(array_sum($deposit_transactions_amount_arr) + array_sum($withdraw_transactions_amount_arr));
            // Total creditor -- اجمالى الدائن
            $total_creditor = $this->number_format_abs(array_sum($total_creditor));
            // Total Debtor -- اجمالى المدين
            $total_debtor = $this->number_format_abs(array_sum($total_debtor));


            $total_taxes = number_format(array_sum($tax_array), 2);
            $total_services = number_format(array_sum($reservations_services_sum), 2);
            $total_rent = number_format(array_sum($rent_array), 2);
            $total_income = number_format(array_sum($income_array), 2);

            $total_amount = number_format(array_sum($income_array) + array_sum($tax_array), 2);



            return response()->json([
                'status' => 'success',
                'total_receipts' => $total_receipts,
                'total_cost' => $total_cost,
                'the_total_credit' => $totalCredit,
                'total_creditor' => $total_creditor,
                'total_debtor' => $total_debtor,
                'total_amount' => $total_amount,
                'reservations_ids' => $reservations_ids,
                'total_income' => $total_income,
                'total_rent' => $total_rent,
                'total_services' => $total_services,
                'total_taxes' => $total_taxes
            ]);
        } else {
            // Else here means no reservations ids was retrieved and passed along with the request
            return response()->json([
                'status' => 'silence_is_good',
                'total_receipts' => 0,
                'total_cost' => 0,
                'the_total_credit' => 0,
                'total_creditor' => 0,
                'total_debtor' => 0,
                'total_amount' => 0,
                'total_income' => 0,
                'total_rent' => 0,
                'total_services' => 0,
                'total_taxes' => 0
            ]);
        }
    }

    protected function number_format_abs($number)
    {
        return number_format(abs($number) / 100, 2);
    }

    public function tenantSettings(NovaRequest $request)
    {

        return response()->json([
            'day_start' => \App\Handlers\Settings::get('day_start'),
            'day_end' => \App\Handlers\Settings::get('day_end')
        ]);
    }


    public function reservationsManagementExcel(NovaRequest $request)
    {
        $reservations_ids = json_decode($request->get('params'))->reservations_ids;
        $reservations = Reservation::whereIn('id', $reservations_ids)->get();
        // Holder array
        $holder = array();

        foreach ($reservations as $reservation) {

            $reservation = (object) $reservation;

            $checked_in = new \DateTime($reservation->date_in);
            $checked_out = new \DateTime($reservation->date_out);
            $duration = $checked_in->diff($checked_out);
            $day = $duration->format('%a');

            $status = $reservation->status == 'confirmed' ? __('Confirmed') : __('Canceled');

            if (is_null($reservation->checked_in) && is_null($reservation->checked_out)) {
                $reservationStatus = __('Pending');
            } else if (!is_null($reservation->checked_in) && is_null($reservation->checked_out)) {
                $reservationStatus = __('Checked In');
            } else {
                $reservationStatus = __('Checked Out');
            }


            $reservation->wallet->refreshBalance();
            // i need balance to find debit and credit
            $balance = $reservation->balance / 100;
            if ($balance > 0) {
                $creditLabel = '(' . __('credit') . ')';
            } elseif ($balance < 0) {
                $creditLabel = '(' . __('debit') . ')';
            } else {
                $creditLabel = '';
            }

            $balance = abs($balance);


            $data[__('Reservation Number')] = $reservation->number;
            $data[__('Customer name')] = $reservation->customer['name'];
            $data[__('Unit Number')] = $reservation->unit['unit_number'];
            $data[__('Status')] = $status;
            $data[__('Reservation Status')] = $reservationStatus;
            $data[__('Date In')] = $reservation->date_in;
            $data[__('Date Out')] = $reservation->date_out;
            $data[__('Nights Count')] = $day;
            $data[__('Total Price')] = $reservation->total_price;
            $data[__('Rent Type')] = $reservation->rent_type == 1 ? __('Daily') : __('Monthly');
            $data[__('Total Credit')] = $balance . $creditLabel;
            $holder[] = $data;
        }



        return response()->json([
            'status' => 'success',
            'data' => $holder,
            'filename' => __('Reservation Management Report')
        ]);
    }


    /**
     * @description : Add new Services to transaction table
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addServices(Request $request)
    {
        $services = $request->get('items');

        $services_ids = [];
        foreach ($services as $item) {
            $services_ids[] = $item['id'];
        }


        $services_categories_ids = collect(Service::with('serviceCategory')->whereIn('id', $services_ids)->get())->pluck('services_category_id')->unique();
        $category_label = null;
        $statement = null;
        if (count($services_categories_ids) > 1) {
            $statement = 'various_services';
        } else {
            $service_category = ServicesCategory::whereIn('id', $services_categories_ids)->first();
            $statement = $service_category->name;
        }


        // $category = $request->get('category');
        $reservation_id = $request->get('reservation_id');
        $sumGeneralTotalWithTaxes = $request->get('sumGeneralTotalWithTaxes');


        $sumQuantities = $request->get('sumQuantities');
        $sumSubTotal = $request->get('sumSubTotal');
        $sumVatTotal = $request->get('sumVatTotal');
        $sumTtxTotal = $request->get('sumTtxTotal');
        $model = Reservation::with('customer', 'unit', 'creator', 'comments', 'guests', 'source', 'invoices', 'promissory', 'depositInsuranceTransactions', 'withdrawInsuranceTransactions', 'company', 'group_reservation', 'group_reservation.reservations')->find($reservation_id);

        // needed this cause there was no way to handle zero
        $services_filtered = [];
        $i = 1;


        foreach ($services as $service) {

            // Forming meta
            $serviceObj = new \stdClass();
            // $serviceObj->id = $i;
            $serviceObj->id = $service['id'];
            $serviceObj->category = 'service';
            $serviceObj->statement = $service['text'];
            $serviceObj->qty = $service['qty'];
            $serviceObj->vat = $service['vatIsChecked'] ? $service['vat'] : 0;
            $serviceObj->ttx = $service['ttxIsChecked'] ? $service['ttx'] : 0;
            $serviceObj->vatIsChecked = $service['vatIsChecked'];
            $serviceObj->ttxIsChecked = $service['ttxIsChecked'];
            $serviceObj->price = $service['price'];
            $serviceObj->sub_total = $service['subTotal'];
            $serviceObj->totalGeneralSum = $serviceObj->sub_total + $serviceObj->vat + $serviceObj->ttx;
            $services_filtered[] = $serviceObj;
            $i++;
        }

        $meta = [];
        $meta['category'] = 'service';
        $meta['date'] = Carbon::now()->format('Y-m-d H:i');
        $meta['statement'] = $statement;
        $meta['services'] = $services_filtered;
        $meta['total_with_taxes'] = $sumGeneralTotalWithTaxes;
        $meta['sub_total'] = $sumSubTotal;
        $meta['vat_total'] = $sumVatTotal;
        $meta['ttx_total'] = $sumTtxTotal;
        $meta['qty'] = $sumQuantities;
        $model->wallet->refreshBalance();
        $transaction = $model->forceWithdrawFloat($sumGeneralTotalWithTaxes, $meta, true, false);
        /**
         * @see Copy this to service log
         */

        $counter = $model->team->counter;
        if (!$counter) {
            $counter = TeamCounter::create();
        }

        $last_service_number = $counter->last_service_number + 1;
        $counter->last_service_number = $last_service_number;
        $counter->save();


        $serviceLog = new ServiceLog;
        $serviceLog->team_id = $model->team_id;
        $serviceLog->user_id = auth()->user()->id;
        ;
        $serviceLog->transaction_id = $transaction->id;
        $serviceLog->type = $transaction->type;
        $serviceLog->number = $last_service_number;
        $serviceLog->amount = $transaction->amount;
        $serviceLog->decimals = $transaction->wallet->decimal_places;
        $serviceLog->meta = $transaction->meta;
        $serviceLog->save();

        if ($model->group_reservation) {
            groupReservationHandler($model->group_reservation);
        }

        return response()->json($services);
    }

    /**
     * Update Service Transaction
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateServices(Request $request)
    {
        $incomingServices = $request->get('items');
        $transaction_id = $request->get('transaction_id');
        $reservation_id = $request->get('reservation_id');
        $sumGeneralTotalWithTaxes = $request->get('sumGeneralTotalWithTaxes');
        $sumQuantities = $request->get('sumQuantities');
        $sumSubTotal = $request->get('sumSubTotal');
        $sumVatTotal = $request->get('sumVatTotal');
        $sumTtxTotal = $request->get('sumTtxTotal');
        $model = Reservation::with('customer', 'unit', 'creator', 'comments', 'guests', 'source', 'invoices', 'promissory', 'depositInsuranceTransactions', 'withdrawInsuranceTransactions', 'company', 'group_reservation', 'group_reservation.reservations')->find($reservation_id);


        if (!count($incomingServices)) {
            // then i will delete the transaction it self
            $reservation = Reservation::find($reservation_id);
            $reservation->wallet->refreshBalance();

            $transaction = Transaction::with('service_log')->find($transaction_id);
            $user = Auth::user();
            if($transaction){
                if ($transaction->is_freezed != null && $transaction->is_freezed == 1 && !$user->hasPermissionTo('edit/delete freezed transactions after business day')) {
                    return response()->json([
                        'status' => false,
                        'message' =>__('messages.transaction_frozen')
                    ], 403);
                }
            }
            $transaction->service_log()->delete();
            $transaction->delete();

            return response()->json(['status' => 'services-deleted']);
        } else {
            // i will update the meta object and remove service missing from it
            $transaction = Transaction::find($transaction_id);
            $user = Auth::user();
            if($transaction){
                if ($transaction->is_freezed != null && $transaction->is_freezed == 1 && !$user->hasPermissionTo('edit/delete freezed transactions after business day')) {
                    return response()->json([
                        'status' => false,
                        'message' =>__('messages.transaction_frozen')
                    ], 403);
                }
            }
            $services_filtered = [];

            $services_ids = [];
            $note = [];
            foreach ($incomingServices as $item) {
                $services_ids[] = $item['id'];
                $note[] = ' ' . (string) $item['qty'] . ' × ' . $item['text'];
            }
            $note = implode(' - ', $note);
            // dd($services_ids);
            $services_categories_ids = collect(Service::withoutGlobalScopes()->with('serviceCategory')->whereIn('id', $services_ids)->get())->pluck('services_category_id')->unique();
            // dd($services_categories_ids);

            $statement = null;
            if (count($services_categories_ids) > 1) {
                $statement = 'various_services';
            } else {



                    $service_category = ServicesCategory::withoutGlobalScopes()->whereIn('id', $services_categories_ids)->first();



                    $statement = $service_category->name;



            }

            foreach ($incomingServices as $service) {

                // Forming meta
                $serviceObj = new \stdClass();
                $serviceObj->id = $service['id'];
                $serviceObj->category = 'service';
                $serviceObj->statement = $service['text'];
                $serviceObj->qty = $service['qty'];
                $serviceObj->vat = $service['vatIsChecked'] ? $service['vat'] : 0;
                $serviceObj->ttx = $service['ttxIsChecked'] ? $service['ttx'] : 0;
                $serviceObj->vatIsChecked = $service['vatIsChecked'];
                $serviceObj->ttxIsChecked = $service['ttxIsChecked'];
                $serviceObj->price = $service['price'];
                $serviceObj->sub_total = $service['subTotal'];
                $serviceObj->totalGeneralSum = $serviceObj->sub_total + $serviceObj->vat + $serviceObj->ttx;
                $services_filtered[] = $serviceObj;
            }


            $meta = [];
            $meta['category'] = 'service';
            if (isset($transaction->meta['pos'])) {
                $meta['pos'] = true;
            }
            $meta['date'] = isset($transaction->meta['date']) ? $transaction->meta['date'] : $transaction->created_at;
            $meta['from'] = isset($transaction->meta['from']) ? $transaction->meta['from'] : '';
            $meta['statement'] = $statement;
            $meta['payment_type'] = isset($transaction->meta['payment_type']) ? $transaction->meta['payment_type'] : null;
            $meta['services'] = $services_filtered;
            $meta['total_with_taxes'] = $sumGeneralTotalWithTaxes;
            $meta['sub_total'] = $sumSubTotal;
            $meta['vat_total'] = $sumVatTotal;
            $meta['ttx_total'] = $sumTtxTotal;
            $meta['qty'] = $sumQuantities;

            $transaction->amount = $sumGeneralTotalWithTaxes * ($model->wallet->decimal_places == 3 ? -1000 : -100);
            $transaction->meta = $meta;
            $transaction->save();

            $service_log = $transaction->service_log;
            $service_log->amount = $transaction->amount;
            $service_log->meta = $transaction->meta;
            $service_log->save();

            $model->wallet->refreshBalance();
            if ($model->group_reservation) {
                groupReservationHandler($model->group_reservation);
            }

            return response()->json(['status' => 'services-updated']);
        }
    }



    /**
     * Add new Invoice
     * @param Request $request
     * @param $extra  ( coming from generate automated invoice in the one only request of the new checkout)
     * @return ReservationInvoice
     * @throws \Exception
     */
    public function add_invoice(Request $request, $extra = null)
    {
        $isPriceByDayEnabled = SettingStore::getUserSetting('check_calculate_price_by_day_enable');
        if ($extra) {
            $reservation = Reservation::find($extra['id']);
        } else {
            $reservation = Reservation::find($request->id);
        }
        $invoice = new ReservationInvoice();
        $invoice->team_id = $reservation->team_id;
        $invoice->reservation_id = $reservation->id;
        if ($extra) {
            $invoice->from = $extra['from_date'];
            $invoice->to = $extra['to_date'];
        } else {
            $invoice->from = date(Carbon::createFromTimestamp(strtotime($request->get('from_date')))->format('Y-m-d'));
            $invoice->to = date(Carbon::parse($request->get('to_date'))->addDay()->format('Y-m-d'));
        }
        $reservation_date_in = $reservation->date_in;
        $reservation_date_out = $reservation->date_out;
        $counter = TeamCounter::where('team_id', $reservation->team_id)->first();
        $next = $counter->invoice_num;
        $invoice->number = $next;

        if ($extra) {
            $invoice->note = $extra['note'];
        } else {
            $invoice->note = $request->note;
        }

        $counter->last_invoice_number = $next;
        // cast invoice from , to as DateTimeObjects
        $to = new \DateTime($invoice->to);
        $from = new \DateTime($invoice->from);
        $orginal_to = new \DateTime($reservation_date_out);
        $orginal_from = new \DateTime($reservation_date_in);
        $total_nighs = $orginal_to->diff($orginal_from)->days + 1;

        $diff = $to->diff($from)->days + 1;
        $parsedFrom = Carbon::parse($from);
        $parsedTo = Carbon::parse($to);
        $period = CarbonPeriod::create($parsedFrom, $parsedTo);
        $sub_total = 0;
        foreach ($period as $date) {
            if ($reservation->rent_type == 1 && $reservation->reservation_notif_id == null && $reservation->cmBookingId == null) {
                // it's a daily reservation
                foreach ($reservation->prices['days'] as $obj) {
                    if (Carbon::parse($obj['date'])->format('Y-m-d') == $date->format('Y-m-d')) {
                        //if the price by day is not enabled then apply change rate if exist
                        if($reservation->change_rate && !$isPriceByDayEnabled){
                            $sub_total += $reservation->sub_total / $reservation->nights;
                        }else{
                            $sub_total += $obj['price'];
                        }
                    }
                }
            }
           else {
                // it's a monthly reservation
                $sub_total += $reservation->sub_total / $reservation->nights;
            }

        }

        $ewa_percentage = isset($reservation->old_prices) && isset($reservation->old_prices['ewa_parentage']) ? $reservation->old_prices['ewa_parentage'] : 0;
        $ttx_percentage = isset($reservation->old_prices) && isset($reservation->old_prices['tourism_percentage']) ? $reservation->old_prices['tourism_percentage'] : 0;
        $vat_percentage = isset($reservation->old_prices) && isset($reservation->old_prices['vat_parentage']) ? $reservation->old_prices['vat_parentage'] : 0;
        $data = new \stdClass();
        if($reservation->special_prices != null){
            $sumOfPrices = collect(json_decode($reservation->special_prices));
            $sumOfPrices = $sumOfPrices->pluck('specialPrice')->map(function ($price) {
                return (int) $price;
            })->sum();
            if (abs($reservation->sub_total - $sumOfPrices) < 1) {
                $matchingDates = collect(json_decode($reservation->special_prices))
                    ->filter(function ($item) use ($parsedFrom, $parsedTo) {
                        $date = Carbon::parse($item->date);
                        return $date->between($parsedFrom, $parsedTo);
                    });
                $sumOfSpecialPrices = $matchingDates->pluck('specialPrice')->map(function ($price) {

                    return $price; // Convert the price to integer if needed
                })->sum();
                $sub_totall2 = $sumOfSpecialPrices;
                // use ewa__percentage , ttx_percentage , vat_percentage to calculate the taxes and total price
                $ewa_total2 = $sub_totall2 * $ewa_percentage / 100;
                $subtotalwithewa = $sub_totall2 + $ewa_total2;
                $ttx_total2 = $sub_totall2 * $ttx_percentage / 100;
                $vat_total2 = $subtotalwithewa * $vat_percentage / 100;
                $total_price2 = $sub_totall2 + $ewa_total2 + $ttx_total2 + $vat_total2;


                $data->sub_total = (float) $sub_totall2;
                $data->vat = (float) $vat_total2;
                $data->ewa = (float) $ewa_total2;
                $data->ttx = (float) $ttx_total2;
                $data->total_price = (float) $total_price2;
                $data->nights = $diff;


            } else {

                $data->sub_total = (float) $sub_total;
                // $data->ewa = (float) number_format($reservation->unit->getEwaTotal($data->sub_total, false), 2, '.', '');
                $data->ewa = (float)  ($data->sub_total / 100) * $ewa_percentage;
                $data->ttx = (float) $reservation->unit->getTourismTaxTotal($data->sub_total, false);
                if ($reservation->remove_vat) {
                    $data->vat = 0;
                    $data->total_price = (float) $sub_total + $data->ewa + $reservation->unit->getTourismTaxTotal($sub_total, false);
                    $data->nights = $diff;
                } else {
                    $data->vat = (float)  (($data->sub_total + $data->ewa) / 100) * $vat_percentage  ;
                    $data->total_price = (float) $sub_total + $data->vat  + $data->ewa + $reservation->unit->getTourismTaxTotal($sub_total, false);
                    $data->nights = $diff;
                }

            }


        } else {
            $data->sub_total = (float) $sub_total;
            // $data->ewa = (float) $reservation->unit->getEwaTotal($data->sub_total, false);
            $data->ewa = (float)  ($data->sub_total / 100) * $ewa_percentage;
            $data->vat = (float)  (($data->sub_total + $data->ewa) / 100) * $vat_percentage  ;
            $data->ttx = (float) $reservation->unit->getTourismTaxTotal($data->sub_total, false);
            $data->total_price = (float) $sub_total + $reservation->unit->getVatTotal($sub_total, false) + $data->ewa + $reservation->unit->getTourismTaxTotal($sub_total, false);
            $data->nights = $diff;
        }
        // collecting data , save them with the invoice

        // $data->sub_total =  (float) number_format($sub_totall2, 2, '.', '');
        // $data->vat =  (float) number_format($ewa_total2, 2, '.', '');
        // $data->ewa =  (float) number_format($ewa_total2, 2, '.', '');
        // $data->ttx =  (float) number_format($ttx_total2, 2, '.', '');
        // $data->total_price = (float) number_format($total_price2, 2, '.', '');
        // $data->nights = $diff;
        /**
         * Check if this is the last day in the reservation
         * if it was then this is the last possible invoice our client is gonna make
         * if it was the last invoice then we need to get all free service transaction
         * to attach it in the last invoice no matter that date if those services transactions
         */
        if ($invoice->to == date(Carbon::parse($reservation->date_out)->subDay()->format('Y-m-d'))) {
            $filterServices = $this->filterServices($reservation->id, $from, $to, true);
        } else {
            $filterServices = $this->filterServices($reservation->id, $from, $to);
        }
        $data->servicesSum = abs($filterServices['servicesSum']);
        $data->services = $filterServices['services'];
        $data->transactions_ids = $filterServices['transactions_ids'];
        $data->amount = (float) $data->total_price + $data->servicesSum;
        $invoice->data = $data;
        $invoice->created_by = auth()->user()->id;
        $counter->save();
        $invoice = $this->syncInvoiceOnCreate($invoice, "", "invoice", "0");
        $invoice->save();
        return $invoice->load('invoiceCreditNote', 'reservation.services');
    }

    /**
     * Filter Service Transactions
     * @param $reservation_id
     * @param $from
     * @param $to
     * @return array
     */
    private function filterServices($reservation_id, $from, $to, $is_last_invoice = false)
    {
        $services = [];
        $transactions_ids = [];
        $servicesSum = [];
        if ($is_last_invoice) {
            $servicesTransactions = Transaction::with('wallet')->where('payable_type', 'App\\Reservation')->where('payable_id', $reservation_id)->where('is_public', 0)->where('meta->category', 'service')
                ->where('is_attached_to_invoice', 0)->get();
        } else {
            $servicesTransactions = Transaction::with('wallet')->where('payable_type', 'App\\Reservation')->where('payable_id', $reservation_id)->where('is_public', 0)->where('meta->category', 'service')->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->get();
        }

        if (count($servicesTransactions)) {
            foreach ($servicesTransactions as $transaction) {
                $servicesSum[] = $transaction->amount / ($transaction->wallet->decimal_places == 3 ? 1000 : 100);
                foreach ($transaction->meta['services'] as $serviceObj) {
                    $services[] = $serviceObj;
                }
                $transactions_ids[] = $transaction->id;
                $transaction->is_attached_to_invoice = 1;
                $transaction->save();
            }
        }
        return [
            'services' => $services,
            'transactions_ids' => $transactions_ids,
            'servicesSum' => array_sum($servicesSum)
        ];
    }

    /**
     * Delete Invoice
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteInvoice(Request $request, $id)
    {

        $invoice = ReservationInvoice::find($id);

        $invoice->delete();


        return response()->json('invoice_deleted');
    }

    /**
     * Get List of Invoices According to reservation
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReservationInvoices(Request $request)
    {

        return response()->json(ReservationInvoice::where('reservation_id', $request->get('id'))->whereNull('deleted_at')->get());
    }


    /**
     * Check Newest Invoices to be deleted first notification
     * @param Request $request
     * @return int
     */
    public function checkPreviousInvoices(Request $request)
    {

        $invoice_id = $request->get('invoice_id');
        $recentInvoice = ReservationInvoice::where('reservation_id', $request->get('reservation_id'))->orderByDesc('number')->first();

        if ($recentInvoice->id == $invoice_id) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Unit Filter Data
     * @return array
     */
    public function unitFilterValues()
    {
        $alphabet = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"];
        $units = Unit::where('team_id', '=', auth()->user()->current_team_id)
            ->get()
            ->sortBy(function ($i) use ($alphabet) {
                return trim(str_replace($alphabet, '', $i['unit_number']));
            });
        $arr = [];
        foreach ($units as $unit) {
            $arr[] = [
                'name' => $unit->unit_number . ' - ' . $unit->name,
                'value' => $unit->id
            ];
        }
        return $arr;
    }

    /**
     * Customer Highlight Filter Data
     * @return array
     */
    public function customerCategories()
    {
        $highlights = Highlight::all();
        $filtered = [];
        foreach ($highlights as $highlight) {
            $filtered[] = [
                'name' => $highlight->name,
                'value' => $highlight->id
            ];
        }
        return $filtered;
    }

    public function getReservationsData(Request $request)
    {
        $search_criteria = $request->get('search_criteria');

        // $query = Reservation::query();
        $reservation_type = \request()->get('reservationType');
        $query = QueryBuilder::for(Reservation::class);
        $query->whereTeamId(auth()->user()->current_team_id);
        $query->whereNull('deleted_at');

        if ($reservation_type) {

            if ($reservation_type == 'all') {
                $query->whereIn('reservation_type', ['single', 'group']);
            } elseif ($reservation_type == 'single') {
                $query->where('reservation_type', 'single');
            } else {
                $query->where('reservation_type', 'group');
            }
        }






        if (\request()->get('dateFrom') != 'null' && \request()->get('dateFrom') != '') {

            if($search_criteria == 'all'){
                $query->where(function ($query) {
                    $query->where('date_in', '>=', \request()->get('dateFrom'))
                    ->orWhere('date_out', '>' , \request()->get('dateFrom'));
                });
            }

            if($search_criteria == 'date_in'){
                $query->where('date_in', '>=', \request()->get('dateFrom'));
                if(\request()->get('dateTo') != 'null' && \request()->get('dateTo') != '') {
                    $query->where('date_in', '<=' , \request()->get('dateTo'));
                }
            }



        }

        if (\request()->get('dateTo') != 'null' && \request()->get('dateTo') != '') {
            if($search_criteria == 'all'){
                $query->where('date_in', '<=', \request()->get('dateTo'));
            }


             // if (\request()->get('dateFrom') != 'null' && \request()->get('dateFrom') != '') {
                    //     $query->where('date_in', '>=', \request()->get('dateFrom'));
                    // }

                    // if (\request()->get('dateTo') != 'null' && \request()->get('dateTo') != '') {
                    //     $query->where('date_in', '<=' , \request()->get('dateTo'));
                    // }


            if($search_criteria == 'date_out'){
                $query->where('date_out', '<=' , \request()->get('dateTo'));
                if (\request()->get('dateFrom') != 'null' && \request()->get('dateFrom') != '') {
                    $query->where('date_out', '>=', \request()->get('dateFrom'));
                }
            }

        }

        if (\request()->get('reservationNumber') != 'null' && \request()->get('reservationNumber') != '') {
            $query->where('number', 'LIKE', '%' . \request()->get('reservationNumber') . '%');
        }

        if (\request()->get('reservationStatus') != 'null' && \request()->get('reservationStatus') != '') {

            switch (\request()->get('reservationStatus')) {
                case 'open_all':
                    $query->whereIn('status', ['confirmed', 'awaiting-confirmation', 'awaiting-payment', 'hold'])->whereNull('checked_out');
                    break;
                case 'all':
                    $query->where(function ($query) {
                        $query->where(function ($query) {
                            $query->whereNotNull('checked_out')->whereStatus('confirmed');

                        })
                            ->orWhere(function ($query) {
                                $query->whereIn('status', ['confirmed', 'awaiting-confirmation', 'awaiting-payment', 'hold'])
                                    ->whereNull('checked_out');
                            })
                            ->orWhere(function ($query) {
                                $query->whereNull('checked_in')

                                    ->whereNull('checked_out')
                                    ->whereStatus('confirmed');
                            });
                    });

                    break;
                case 'closed_all':
                    $query->where(function ($query) {
                        // $query->whereNotNull('checked_out')->whereStatus('confirmed')
                        //     ->orWhereNull('checked_out')->whereStatus('canceled');

                        $query->whereNotNull('checked_out')->whereStatus('confirmed')
                            ->orWhere('status', 'canceled');
                    });
                    break;
                case 'checked_in':
                    $query->whereNotNull('checked_in')->whereNull('checked_out')->whereStatus('confirmed');
                    break;
                case 'checked_out':
                    $query->whereNotNull('checked_out')->whereStatus('confirmed');
                    break;
                case 'canceled':
                    $query->whereStatus('canceled');
                    break;
                case 'timeout':
                    $query->whereStatus('timeout');
                    break;
                case 'awaiting-payment':
                    $query->whereStatus('awaiting-payment');
                    break;
                case 'awaiting-confirmation':
                    $query->whereStatus('awaiting-confirmation');
                    break;
                case 'pending':
                    $query->whereNull('checked_in')->whereNull('checked_out')->whereStatus('confirmed');
                    break;
                default:
                    $query->whereStatus('confirmed');
                    break;
            }
        } else {
            $query->whereStatus('confirmed');
        }

        if (\request()->get('customerName') != 'null' && \request()->get('customerName') != '') {
            $query->where(function ($subQuery) {
                $subQuery->whereHas('customer', function ($customer) {
                    $customer->where('name', 'LIKE', '%' . \request()->get('customerName') . '%');
                })
                    ->orWhereHas('company', function ($company) {
                        $company->where('name', 'LIKE', '%' . \request()->get('customerName') . '%');
                    });
            })->whereHas('unit', function ($unit) {
                $unit->whereNull('deleted_at');
            });
            // $query->whereHas('customer', function ($customer) {
            //     $customer->where('name', 'LIKE', '%' . \request()->get('customerName') . '%');
            // })->whereHas('unit', function ($unit) {
            //     $unit->whereNull('deleted_at');
            // });
        }

        if (\request()->get('unitId') != 'null' && \request()->get('unitId') != '') {
            $query->whereHas('unit', function ($unit) {
                $unit->where('id', \request()->get('unitId'))->whereNull('deleted_at');
            });
        }

        if (\request()->get('unitCategoryId') != 'null' && \request()->get('unitCategoryId') != '') {
            $query->whereHas('unit', function ($unit) {
                $unit->where('unit_category_id', \request()->get('unitCategoryId'))->whereNull('deleted_at');
            });
        }

        if (\request()->get('unitName') != 'null' && \request()->get('unitName') != '') {
            $query->whereHas('unit', function ($unit) {
                $unit->where('name', 'LIKE', '%' . \request()->get('unitName') . '%')->whereNull('deleted_at');
            });
        }

        if (\request()->get('indebtednessType') != 'null' && \request()->get('indebtednessType') != '') {
            if ($reservation_type == 'group') {
                $query->whereNull('attachable_id');
            }
            $query->indebtednessType(\request()->get('indebtednessType'), $reservation_type);
        }

        if (\request()->get('rentType') != 'null' && \request()->get('rentType') != '') {
            $query->rentType(\request()->get('rentType'));
        }

        if (\request()->get('customerHighlight') != 'null' && \request()->get('customerHighlight') != '') {
            $query->whereHas('customer', function ($customer) {
                $customer->whereHas('highlight', function ($highlight) {
                    $highlight->where('id', \request()->get('customerHighlight'));
                });
            });
        }

        if (\request()->get('reservationSource') != 'null' && \request()->get('reservationSource') != '') {
            $query->where('source_id', \request()->get('reservationSource'));
        }

        if (\request()->get('reservationService') != 'null' && \request()->get('reservationService') != '') {
            $query->whereHas('reservationFreeServices', function ($freeServiceMapper) {
                $freeServiceMapper->where('reservation_service_id', \request()->get('reservationService'));
            });
        }

        if (\request()->get('reservationSourceNumber') != 'null' && \request()->get('reservationSourceNumber') != '') {
            $query->where('source_num', \request()->get('reservationSourceNumber'));
        }

        $reservations_ids = $query->pluck('id');
        return ReservationResource::collection($query->with(['customer', 'unit', 'wallet', 'services', 'transactions', 'groupReservationBalanceMapper'])->orderByDesc('id')->paginate(25))->additional(['ids' => $reservations_ids]);
    }

    public function getReservationsHaveServicesData (Request $request) {
        $reservation_type = \request()->get('reservationType');
        $query = QueryBuilder::for(Reservation::class);
        $query->whereTeamId(auth()->user()->current_team_id);
        $query->whereNull('deleted_at');

        if ($reservation_type) {

            if ($reservation_type == 'all') {
                $query->whereIn('reservation_type', ['single', 'group']);
            } elseif ($reservation_type == 'single') {
                $query->where('reservation_type', 'single');
            } else {
                $query->where('reservation_type', 'group');
            }
        }

        if (\request()->get('dateFrom') != 'null' && \request()->get('dateFrom') != '') {
            $query->where('date_in', '>=', \request()->get('dateFrom'));
        }

        if (\request()->get('dateTo') != 'null' && \request()->get('dateTo') != '') {
            $query->where('date_in', '<=', \request()->get('dateTo'));
        }

        if (\request()->get('reservationNumber') != 'null' && \request()->get('reservationNumber') != '') {
            $query->where('number', 'LIKE', '%' . \request()->get('reservationNumber') . '%');
        }

        if (\request()->get('reservationStatus') != 'null' && \request()->get('reservationStatus') != '') {

            switch (\request()->get('reservationStatus')) {
                case 'open_all':
                    $query->whereIn('status', ['confirmed', 'awaiting-confirmation', 'awaiting-payment', 'hold'])->whereNull('checked_out');
                    break;
                case 'all':
                    $query->where(function ($query) {
                        $query->where(function ($query) {
                            $query->whereNotNull('checked_out')->whereStatus('confirmed');

                        })
                            ->orWhere(function ($query) {
                                $query->whereIn('status', ['confirmed', 'awaiting-confirmation', 'awaiting-payment', 'hold'])
                                    ->whereNull('checked_out');
                            })
                            ->orWhere(function ($query) {
                                $query->whereNull('checked_in')

                                    ->whereNull('checked_out')
                                    ->whereStatus('confirmed');
                            });
                    });

                    break;
                case 'closed_all':
                    $query->where(function ($query) {
                        // $query->whereNotNull('checked_out')->whereStatus('confirmed')
                        //     ->orWhereNull('checked_out')->whereStatus('canceled');

                        $query->whereNotNull('checked_out')->whereStatus('confirmed')
                            ->orWhere('status', 'canceled');
                    });
                    break;
                case 'checked_in':
                    $query->whereNotNull('checked_in')->whereNull('checked_out')->whereStatus('confirmed');
                    break;
                case 'checked_out':
                    $query->whereNotNull('checked_out')->whereStatus('confirmed');
                    break;
                case 'canceled':
                    $query->whereStatus('canceled');
                    break;
                case 'timeout':
                    $query->whereStatus('timeout');
                    break;
                case 'awaiting-payment':
                    $query->whereStatus('awaiting-payment');
                    break;
                case 'awaiting-confirmation':
                    $query->whereStatus('awaiting-confirmation');
                    break;
                case 'pending':
                    $query->whereNull('checked_in')->whereNull('checked_out')->whereStatus('confirmed');
                    break;
                default:
                    $query->whereStatus('confirmed');
                    break;
            }
        } else {
            $query->whereStatus('confirmed');
        }

        if (\request()->get('customerName') != 'null' && \request()->get('customerName') != '') {
            $query->where(function ($subQuery) {
                $subQuery->whereHas('customer', function ($customer) {
                    $customer->where('name', 'LIKE', '%' . \request()->get('customerName') . '%');
                })
                    ->orWhereHas('company', function ($company) {
                        $company->where('name', 'LIKE', '%' . \request()->get('customerName') . '%');
                    });
            })->whereHas('unit', function ($unit) {
                $unit->whereNull('deleted_at');
            });
            // $query->whereHas('customer', function ($customer) {
            //     $customer->where('name', 'LIKE', '%' . \request()->get('customerName') . '%');
            // })->whereHas('unit', function ($unit) {
            //     $unit->whereNull('deleted_at');
            // });
        }

        if (\request()->get('unitId') != 'null' && \request()->get('unitId') != '') {
            $query->whereHas('unit', function ($unit) {
                $unit->where('id', \request()->get('unitId'))->whereNull('deleted_at');
            });
        }

        if (\request()->get('unitName') != 'null' && \request()->get('unitName') != '') {
            $query->whereHas('unit', function ($unit) {
                $unit->where('name', 'LIKE', '%' . \request()->get('unitName') . '%')->whereNull('deleted_at');
            });
        }

        if (\request()->get('indebtednessType') != 'null' && \request()->get('indebtednessType') != '') {
            if ($reservation_type == 'group') {
                $query->whereNull('attachable_id');
            }
            $query->indebtednessType(\request()->get('indebtednessType'), $reservation_type);
        }

        if (\request()->get('rentType') != 'null' && \request()->get('rentType') != '') {
            $query->rentType(\request()->get('rentType'));
        }

        if (\request()->get('customerHighlight') != 'null' && \request()->get('customerHighlight') != '') {
            $query->whereHas('customer', function ($customer) {
                $customer->whereHas('highlight', function ($highlight) {
                    $highlight->where('id', \request()->get('customerHighlight'));
                });
            });
        }

        if (\request()->get('reservationSource') != 'null' && \request()->get('reservationSource') != '') {
            $query->where('source_id', \request()->get('reservationSource'));
        }

        if (\request()->get('reservationService') != 'null' && \request()->get('reservationService') != '') {
            $query->whereHas('reservationFreeServices', function ($freeServiceMapper) {
                $freeServiceMapper->where('reservation_service_id', \request()->get('reservationService'));
            });
        }

        if (\request()->get('reservationSourceNumber') != 'null' && \request()->get('reservationSourceNumber') != '') {
            $query->where('source_num', \request()->get('reservationSourceNumber'));
        }

        $reservations_ids = $query->pluck('id');
        return ReservationResource::collection($query->with(['customer', 'unit', 'wallet', 'services', 'transactions', 'groupReservationBalanceMapper', 'reservationFreeServices'])
                ->has('reservationFreeServices', '>', 0)
                ->orderByDesc('id')
                ->paginate(25))
                ->additional(['ids' => $reservations_ids]);
    }


    function getReservationsDataStatistics(Request $request)
    {


        $reservations_ids = $request->get('params');

        $withdraw_transactions_amount_arr = array();
        $deposit_transactions_amount_arr = array();
        $total_creditor = array();
        $total_debtor = array();
        $income_array = array();
        $rent_array = array();
        $tax_array = array();
        $total_amount_array = [];
        $reservations_services_sum = array();


        if (count($reservations_ids)) {
            $reservations = QueryBuilder::for(Reservation::class)
                ->with('groupReservationBalanceMapper')
                ->whereIn('id', $reservations_ids)
                ->with(['services', 'transactions', 'wallet'])->get();
            foreach ($reservations as $reservation) {
                $reservations_services_sum[] = $reservation->getServicesWithoutTaxesSum();
                $total_amount_array[] = $reservation->total_price + $reservation->getServicesWithoutTaxesSum() + $reservation->getServicesTaxesSum();
                $income_array[] = $reservation->sub_total + $reservation->getServicesWithoutTaxesSum();
                $rent_array[] = $reservation->sub_total;
                $tax_array[] = $reservation->ewa_total + $reservation->vat_total + $reservation->ttx_total + $reservation->getServicesTaxesSum();
                if ($reservation->reservation_type == 'single') {
                    $balance = $reservation->balance;
                    if ($balance > 0) {
                        // So it's Creditor
                        $total_creditor[] = $balance / ($reservation->wallet->decimal_places == 3 ? 1000 : 100);
                    } else {
                        // So it's Debtor
                        $total_debtor[] = $balance / ($reservation->wallet->decimal_places == 3 ? 1000 : 100);
                    }
                } else {
                    if ($reservation->groupReservationBalanceMapper) {
                        $balance = $reservation->groupReservationBalanceMapper->balance;
                        if ($balance > 0) {
                            $total_creditor[] = $balance;
                        } else {
                            $total_debtor[] = $balance;
                        }
                    }
                }


                $withdraw_transactions_amount_arr[] = $reservation->getWithdrawSum();

                $deposit_transactions_amount_arr[] = $reservation->getDepositSum();
            }

            // The total deposit  اجمالى المقبوضات
            $total_receipts = (float) number_format(array_sum($deposit_transactions_amount_arr), 2, '.', '');
            // The total withdraw اجمالى المصروفات
            $total_cost = (float) number_format(array_sum($withdraw_transactions_amount_arr), 2, '.', '');
            // The total credit  الرصيد = اجمالى المقبوضات - اجمالى المصروفات
            $totalCredit = (float) number_format(array_sum($deposit_transactions_amount_arr) - array_sum($withdraw_transactions_amount_arr), 2, '.', '');
            // Total creditor -- اجمالى الدائن
            $total_creditor = (float) number_format(array_sum($total_creditor), 2, '.', '');
            // Total Debtor -- اجمالى المدين
            $total_debtor = (float) number_format(array_sum($total_debtor), 2, '.', '');


            $total_taxes = (float) number_format(array_sum($tax_array), 2, '.', '');
            $total_services = (float) number_format(array_sum($reservations_services_sum), 2, '.', '');
            $total_rent = (float) number_format(array_sum($rent_array), 2, '.', '');
            $total_income = (float) number_format(array_sum($income_array), 2, '.', '');

            $total_amount = (float) number_format(array_sum($total_amount_array), 2, '.', '');



            return [
                'total_receipts' => $total_receipts,
                'total_cost' => $total_cost,
                'the_total_credit' => $totalCredit,
                'total_creditor' => $total_creditor,
                'total_debtor' => $total_debtor,
                'total_amount' => $total_amount,
                'total_income' => $total_income,
                'total_rent' => $total_rent,
                'total_services' => $total_services,
                'total_taxes' => number_format($total_taxes, 2)
            ];
        } else {
            return [
                'total_receipts' => number_format(0, 2),
                'total_cost' => number_format(0, 2),
                'the_total_credit' => number_format(0, 2),
                'total_creditor' => number_format(0, 2),
                'total_debtor' => number_format(0, 2),
                'total_amount' => number_format(0, 2),
                'total_income' => number_format(0, 2),
                'total_rent' => number_format(0, 2),
                'total_services' => number_format(0, 2),
                'total_taxes' => number_format(0, 2)
            ];
        }
    }

    public function deleteReservation($id)
    {
        return \response()->json(Response::HTTP_OK);
        $reservation = Reservation::find($id);
        if ($reservation->reservation_type == 'group' && $reservation->status == 'confirmed') {
            $this->moveTransactions($reservation);
        }
        $trids = $reservation->services()->pluck('id')->toArray();
        $services_logs = ServiceLog::whereIn('transaction_id', $trids)->delete();
        $reservation->services()->delete();
        $reservation->destroy($id);

        return \response()->json(Response::HTTP_OK);
    }

    /**
     * Unlink Reservation From Group Reservation
     *
     * @param Request $request
     * @param Reservation $reservation
     * @return void
     */
    public function unlinkReservation(Reservation $reservation)
    {
        // caching group reservation id
        $group_reservation_id = $reservation->group_reservation_id;
        // getting group reservation
        $groupReservation = GroupReservation::with('reservations')->find($group_reservation_id);
        // making a pre-step as if there were only 2 linked reservations and we are unlinking
        // so we must unlink them all
        if ($groupReservation->reservations()->count() == 2) {
            $sourceReservation = $reservation;
            $targetReservation = null;
            foreach ($groupReservation->reservations as $reservation) {
                if ($reservation->id != $sourceReservation->id) {
                    $targetReservation = $reservation;
                }
                $reservation->group_reservation_id = null;
                $reservation->save();
            }
            $this->moveTransactions($sourceReservation, $targetReservation);
            // then delete group reservation
            $groupReservation->delete();
            // return response()->json(['success' => true , 'status' => 'unlink-process-went-successfully']);
        } else {
            $sourceReservation = $reservation;
            $targetReservation = $groupReservation->reservations()->where('id', '!=', $reservation->id)->first();
            $this->moveTransactions($sourceReservation, $targetReservation);
            // set free our target reservation
            $reservation->group_reservation_id = null;
            $reservation->save();
            // getting group reservation once again to refresh group reservations
            $groupReservation = GroupReservation::with('reservations')->find($group_reservation_id);
            $balances = [];
            $ids = [];
            foreach ($groupReservation->reservations as $reservation) {
                $balances[] = $reservation->balance / ($reservation->wallet->decimal_places == 3 ? 1000 : 100);
                $ids[] = $reservation->id;
            }
            $groupReservation->data = ['ids' => $ids];
            $groupReservation->balance = array_sum($balances);
            $groupReservation->save();
        }
    }
    /**
     * Moving Transactions Between Reservations
     * @param Reservation $sourceReservation
     * @param Reservation $targetReservation
     * @return void
     */
    public function moveTransactions(Reservation $reservation)
    {
        // Getting the main reservation
        $main_reservation = null;
        if (is_null($reservation->attachable_id)) {
            $main_reservation = $reservation;
        } else {
            $main_reservation = Reservation::find($reservation->attachable_id);
        }

        // making sure that if the user keep canceling or deleting reservations
        // while they are attached to main reservation to move all related info the the main reservation it self
        // well if there is only the main reservation our if will not be applied as all related info live and die in the main reservation
        if ($reservation->id != $main_reservation->id) {
            $depositAndWithdrawTransactionsCollection = $reservation->transactions;
            $serviceTransactonsCollection = $reservation->services;
            $transactionsCollection = $depositAndWithdrawTransactionsCollection->merge($serviceTransactonsCollection);

            if (count($transactionsCollection)) {
                // moving transactions here by just chaining their payable_id && wallet_id as well so that balance gets affected
                foreach ($transactionsCollection as $transaction) {
                    $transaction->payable_id = $main_reservation->id;
                    $transaction->wallet_id = $main_reservation->wallet->id;
                    $transaction->save();
                }
            }
        }
    }




    public function reservationsManagementExcelReport(Request $request)
    {

        $reservations_ids = json_decode($request->get('params')[0]);
        $statistics = json_decode($request->get('params')[1]);
        $reservations = Reservation::with('company', 'groupReservationBalanceMapper')->whereIn('id', $reservations_ids)->orderByDesc('id')->get();
        // Holder array
        $holder = array();

        foreach ($reservations as $reservation) {

            if (is_null($reservation->checked_in) && is_null($reservation->checked_out)) {
                $reservationStatus = __('Pending');
            } else if (!is_null($reservation->checked_in) && is_null($reservation->checked_out)) {
                $reservationStatus = __('Checked In');
            } else {
                $reservationStatus = __('Checked Out');
            }


            $nights = $reservation->nights > 0 ? $reservation->nights : 1;
            $leasing = (float) number_format($reservation->sub_total / $nights, 2, '.', '');

            $balance = 0;
            $total_creditor = 0;
            $total_debtor = 0;
            if ($reservation->reservation_type == 'single') {
                $balance = $reservation->balance / ($reservation->wallet->decimal_places == 2 ? 100 : 1000);

                if ($balance > 0) {
                    // So it's Creditor
                    $total_creditor = $balance;
                } else {
                    // So it's Debtor
                    $total_debtor = abs($balance);
                }

            } else {
                $balance = $reservation->groupReservationBalanceMapper ? $reservation->groupReservationBalanceMapper->balance : null;
                if ($balance > 0) {
                    $total_creditor = $balance;
                } else {
                    $total_debtor = abs($balance);
                }
            }

            $data[__('Reservation Number')] = $reservation->number;
            if ($reservation->company_id) {
                $data[__('Customer')] = $reservation->company ? $reservation->company->name : '-';
            } else {
                $data[__('Customer')] = $reservation->customer ? $reservation->customer->name : '-';
            }
            $data[__('Unit Number')] = $reservation->unit->unit_number;
            $data[__('Unit Name')] = $reservation->unit->name;
            $data[__('Status')] = __(ucfirst($reservation->status));
            $data[__('Reservation Status')] = $reservationStatus;
            $data[__('Source')] = $reservation->source->name ?? null;
            $data[__('Rent Type')] = $reservation->rent_type == 1 ? __('Daily') : __('Monthly');
            $data[__('Date In')] = $reservation->date_in;
            $data[__('Date Out')] = $reservation->date_out;
            $data[__('Nights Count')] = $reservation->nights;
            $data[__('Leasing')] = $leasing;
            $data[__('Services')] = $reservation->getServicesWithoutTaxesSum();
            $data[__('Amount')] = (float) number_format($reservation->sub_total + $reservation->getServicesWithoutTaxesSum(), 2, '.', '');
            $data[__('Taxes')] = (float) number_format($reservation->ewa_total + $reservation->vat_total + $reservation->ttx_total + floatval($reservation->getServicesTaxesSum()), 2, '.', '');
            $data[__('The Total')] = (float) number_format(($reservation->sub_total + $reservation->getServicesWithoutTaxesSum() + $reservation->ewa_total + $reservation->vat_total + $reservation->ttx_total + floatval($reservation->getServicesTaxesSum())), 2, '.', '');
            $data[__('Paid')] = (float) number_format($reservation->getDepositSum() - $reservation->getWithdrawSum(), 2, '.', '');
            $data[__('Creditor')] = $total_creditor;
            $data[__('Debtor')] = $total_debtor;
            $holder[] = $data;
        }

        return response()->json([
            'status' => 'success',
            'data' => $holder,
            'filename' => __('Reservation Management Report')
        ]);
    }


    public function customerTotalBalance(Request $request)
    {
        $reservations = Reservation::with('wallet')->whereDoesntHave('groupReservationBalanceMapper')->where('customer_id', $request->get('id'))->where('team_id', auth()->user()->current_team_id)->whereNotIn('status', ['awaiting-payment', 'timeout'])->orderBy('id', 'desc')->get();
        $balance = 0;
        if ($reservations) {
            foreach ($reservations as $reservation) {
                $balance += $reservation->balance / ($reservation->wallet->decimal_places == 3 ? 1000 : 100);
            }
        }


        $company = Company::with('company_reservations_history')->where('customer_id',$request->get('id'))->where('team_id',auth()->user()->current_team_id)->first();
        $grand_group_balance = 0;

        $company_reservations = new Collection();
        if($company){
            if (count($company->company_reservations_history)) {
                $company_reservations = $company->company_reservations_history;
                foreach ($company_reservations as $reservation) {
                    $grand_group_balance += shareableGroupBalance($reservation);
                }
            }
        }

        $total_balance = $balance + $grand_group_balance;
        return \response()->json(['total_balance' => $total_balance, 'reservations_count' => count($reservations) + count($company_reservations) ]);
    }
    public function reservationBalance(Request $request)
    {
        $reservation = Reservation::with('wallet')->find($request->get('id'));
        $balance = 0;

        if ($reservation) {
            if ($reservation->reservation_type == 'single') {
                $balance += $reservation->balance / ($reservation->wallet->decimal_places == 3 ? 1000 : 100);
            } else {
                $balance = shareableGroupBalance($reservation);
            }
        }

        return \response()->json(['total_balance' => $balance, 'reservation_type' => $reservation->reservation_type]);
    }
    public function customerCreditorDebtor(Request $request)
    {
        $reservations = Reservation::with('unit')->whereDoesntHave('groupReservationBalanceMapper')->where('customer_id', $request->get('id'))->where('team_id', auth()->user()->current_team_id)->whereNotIn('status', ['awaiting-payment', 'timeout'])->whereNull('deleted_at')->get();
        $customer = DB::table('customer')->find($request->get('id'));

        $company = Company::with('company_reservations_history')->where('customer_id',$customer->id)->where('team_id',auth()->user()->current_team_id)->first();
        $grand_group_balance = 0;

        $company_reservations = new Collection();
        if($company){
            if (count($company->company_reservations_history)) {
                $company_reservations = $company->company_reservations_history;
                foreach ($company_reservations as $reservation) {
                    $grand_group_balance += shareableGroupBalance($reservation);
                }
            }
        }

        $original = new Collection($reservations);

        $latest = new Collection($company_reservations);

        $merged = $original->merge($latest)->sortBy('number');



        return CustomerReservationsHistoryResource::collection($this->custom_paginate($merged->values(),10))->additional(['customer_name' => $customer->name, 'customer_id' => $customer->team_id]);
    }

    public function companyCreditorDebtor(Request $request)
    {
        $company = Company::with('company_reservations_history')->find($request->get('company_id'));
        $grand_group_balance = 0;
        if (count($company->company_reservations_history)) {
            foreach ($company->company_reservations_history as $reservation) {
                $grand_group_balance += shareableGroupBalance($reservation);
            }
        }

        // dd($grand_group_balance);
        $single_reservations = new Collection();
        if($company && $company->entity_type == 'individual'){
            if($company->customer_id){
                $single_reservations = Reservation::where('team_id',$company->team_id)
                ->where('customer_id',$company->customer_id)
                ->whereNull('deleted_at')
                ->whereNotIn('status',['canceled','timeout'])
                ->where('reservation_type','single')
                ->get();

                if(count($single_reservations)){
                    foreach($single_reservations as $normal_reservation){
                        $grand_group_balance += $normal_reservation->balance / 100;
                    }
                }
            }
        }

        $original = new Collection($company->company_reservations_history);

        $latest = new Collection($single_reservations);

        $merged = $original->merge($latest); //


        return CompanyReservationsHistoryResource::collection($this->custom_paginate($merged->values(),10))->additional(['grand_group_balance' => $grand_group_balance]);
    }

    public function custom_paginate($items, $perPage = 15, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }


    public function getLastReservationId(Request $request)
    {

        if ($request->has('id')) {
            //           $reservation =  Reservation::where('unit_id' , $request->get('unit_id'))->where('id' , '!=' , $request->get('id'))->whereIn('status' , ['confirmed','canceled'])->whereDate('date_in' , '>=' , $request->get('date_in'))->whereDate('date_out' , '<=' , $request->get('date_out'))->orderBy('date_in' , 'asc')->first();
            $reservation = Reservation::where('unit_id', $request->get('unit_id'))->where('id', '!=', $request->get('id'))->whereIn('status', ['confirmed', 'awaiting-payment'])->intersection(Carbon::parse($request->get('date_in')), Carbon::parse($request->get('date_out')))->whereNull('deleted_at')->whereNull('checked_out')->orderBy('date_in', 'asc')->first();
            return \response()->json($reservation);
        }
        if ($request->get('unit_id') && $request->has('date_out')) {
            return \response()->json(Reservation::where('unit_id', $request->get('unit_id'))->where('status', 'confirmed')->where('date_in', '>=', $request->get('date_out'))->whereNull('deleted_at')->whereNull('checked_out')->where('date_out', '>=', $request->get('date_out'))->orderBy('date_in', 'asc')->first());
        }
    }

    public function checkCheckinReservation()
    {
        $reservation_id = \request()->get('id');
        $unit_id = \request()->get('unit_id');

        $prReservation = Reservation::where('team_id', auth()->user()->current_team_id)
            ->where('id', '!=', $reservation_id)
            ->whereNull('checked_out')
            ->whereNotNull('checked_in')
            ->whereNull('deleted_at')
            ->where('status', '!=', 'canceled')
            ->where('unit_id', $unit_id)
            ->orderBy('date_out', 'desc')
            ->first();
        return \response()->json($prReservation);
    }

    public function lastCheckedinReservation(Request $request)
    {
        $unit = Reservation::find($request->get('id'))->unit;
        $prReservation = Reservation::where('team_id', auth()->user()->current_team_id)
            ->where('id', '!=', $request->get('id'))
            ->whereNull('checked_out')
            ->whereNotNull('checked_in')
            ->whereNull('deleted_at')
            ->where('status', '!=', 'canceled')
            ->where('unit_id', $unit->id)
            ->first();
        return \response()->json($prReservation);
    }

    public function resetReservationCheckedOut(Request $request)
    {
        $reservation = Reservation::find($request->get('id'));
        if (!is_null($reservation->checked_out)) {
            $reservation->occ = 1;
            $reservation->save();
            return \response()->json(['status' => 'reopen']);
        } else {
            return \response()->json(['status' => 'already-opened']);
        }
    }

    public function cancelCheckout(Request $request)
    {
        $reservation = Reservation::find($request->get('id'));
        if (!is_null($reservation->checked_out)) {
            $reservation->checked_out = null;
            $reservation->occ = 1;
            $reservation->save();
        }

        if ($reservation->team->integration_shms && !$reservation->team->integration_shomoos_version_one) {
            event(new ShomosResendReservationCheckInAfterCancelCheckout($reservation));
        }
    }

    public function updateInvoice(Request $request)
    {

        $invoices = Reservation::find($request->get('reservation_id'))->invoices;
        return \response()->json($invoices);
    }

    public function getCurrentChange(Request $request)
    {
        return Reservation::find($request->get('id'))->change_rate;
    }

    public function checkAddServiceCapability(Request $request)
    {

        $reservation = Reservation::with('invoices')->find($request->get('res_id'));

        $dates_array = [];

        if (is_null($reservation->checked_out) && $reservation->invoices->count()) {


            foreach ($reservation->invoices as $invoice) {

                // create a period from the current invoice
                $period = CarbonPeriod::create(Carbon::parse($invoice->from), Carbon::parse($invoice->to));

                // iterate through this period  to fill dates array to be used later
                foreach ($period as $key => $date) {
                    $dates_array[] = $date->format('Y-m-d');
                }
                // parse service date
                $service_date = Carbon::now()->format('Y-m-d');


                // check if service date in_array of dates_array
                // if found then we can not delete this service cause it's included in this invoice and user must delete invoice first


                if (in_array($service_date, $dates_array)) {

                    if (!$invoice->invoiceCreditNote) {
                        return response()->json(['flag' => 'forbidden', 'invoice_number' => $invoice['number']]);
                    } else {
                        return response()->json(['flag' => 'can-add']);
                    }
                }
            }
        }

        return response()->json(['flag' => 'can-add']);
    }
    /**
     * Get a list of all company reservations
     * we use this list in drawing reservations in linking modal
     *
     * @param Request $request
     * @param Company $company
     * @return void
     */
    public function getCompanyReservations(Request $request, Company $company)
    {
        return CompanyReservationResource::collection($company->reservations);
    }

    /**
     * Link reservations together under one umbrella called group_reservation
     *
     * @param Request $request
     * @return void
     */
    public function linkReservations(Request $request)
    {

        if ($request->get('group_reservation_id')) {

            if (count($request->ids) == 1) {

                // it means that user wishes to remove the group reservation and set the reservation free

                $groupReservation = GroupReservation::with('reservations')->find($request->get('group_reservation_id'));
                foreach ($groupReservation->reservations as $reservation) {
                    $reservation->group_reservation_id = null;
                    $reservation->save();
                }

                $groupReservation->delete();

                return response()->json(['success' => true, 'status' => 'group_reservation_deleted']);
            }

            // means there is a group reservation id
            $groupReservation = GroupReservation::with('reservations')->find($request->get('group_reservation_id'));
            $groupReservation->data = ['ids' => $request->ids];
            $groupReservation->save();
            // first reset any grouped reservation
            foreach ($groupReservation->reservations as $reservation) {
                $reservation->group_reservation_id = null;
                $reservation->save();
            }

        } else {

            if ($request->get('selectedGroupReservationId') && $request->get('selectedGroupReservationId') != 'new_group_reservation') {
                // this means i will attach the ignorant reservation on the incoming group reservation  as i now have group reservation id
                $groupReservation = GroupReservation::with('reservations')->find($request->get('selectedGroupReservationId'));
                $groupReservation->data = ['ids' => $request->ids];
                $groupReservation->save();

            } else {
                // make new group and store it
                $groupReservation = new GroupReservation();
                $groupReservation->company_id = $request->company_id;
                $groupReservation->data = ['ids' => $request->ids];
                $groupReservation->save();
            }
        }

        $reservations = Reservation::with('wallet')->whereIn('id', $request->ids)->get();
        $balances = [];
        foreach ($reservations as $reservation) {
            $reservation->group_reservation_id = $groupReservation->id;
            $reservation->save();
            $balances[] = $reservation->balance / ($reservation->wallet->decimal_places == 3 ? 1000 : 100);
        }

        $groupReservation->balance = array_sum($balances);
        $groupReservation->save();

        return response()->json(['success' => true, 'status' => 'group_reservation_updated']);
    }

    public function getCompanAttachableReservations(Company $company)
    {
        /**
         * i need the checked_out reservations that may they still have onging reservations
         */
        $checked_out_reservations = Reservation::where('company_id', $company->id)
            ->whereNotNull('checked_out')
            ->whereNull('deleted_at')
            ->where('status', 'confirmed')
            ->whereNull('attachable_id')
            ->with('invoices')
            ->get();

        if (count($checked_out_reservations)) {
            foreach ($checked_out_reservations as $main_checked_out_reservation) {
                if (count($main_checked_out_reservation->attachedReservations())) {
                    // push to the collection
                    $company->reservations->push($main_checked_out_reservation);
                }
            }
        }

        return CompanyReservationResource::collection($company->reservations);
    }

    public function checkInsuranceTransactionForGroupReservation(Request $request, Reservation $reservation)
    {
        $reservations_deposit_insurance_transactions = [];
        $reservations_withdraw_insurance_transactions = [];
        if ($reservation->reservation_type == 'group') {
            $main_reservation = null;
            if (is_null($reservation->attachable_id)) {
                $main_reservation = $reservation;
                $push_main_reservation_to_collection = false;
            } else {
                $main_reservation = Reservation::find($reservation->attachable_id);
                $push_main_reservation_to_collection = true;
            }

            $reservations = Reservation::with('wallet')
                ->where('reservation_type', 'group')
                ->where('company_id', $reservation->company_id)
                ->where(function ($query) use ($reservation, $main_reservation) {
                    return $query->where('id', $reservation->id)->orWhere('attachable_id', $main_reservation->id);
                })
                ->where('status', 'confirmed')
                // ->whereNull('checked_out')
                ->whereNull('deleted_at')
                ->orderBy('created_at')
                ->get();

            if ($push_main_reservation_to_collection) {
                $reservations->push($main_reservation);
            }


            foreach ($reservations as $reservationObject) {
                if ($reservationObject->depositInsuranceTransactions()->count()) {
                    $reservations_deposit_insurance_transactions[] = $reservationObject->depositInsuranceTransactions;
                }

                if ($reservationObject->withdrawInsuranceTransactions()->count()) {
                    $reservations_withdraw_insurance_transactions[] = $reservationObject->withdrawInsuranceTransactions;
                }
            }

            if ($request->get('from') == 'deposit') {
                if (count($reservations_deposit_insurance_transactions)) {
                    return response()->json([
                        'can_add_insurance_transaction' => false,
                        'message' => 'insurance transaction found'
                    ]);
                } else {
                    return response()->json([
                        'can_add_insurance_transaction' => true
                    ]);
                }
            }
            if ($request->get('from') == 'withdraw') {
                if (!count($reservations_deposit_insurance_transactions)) {
                    return response()->json([
                        'can_add_retrieval_insurance_transaction' => false,
                        'status' => 'no_insurance_transaction_found'
                    ]);
                }


                if (count($reservations_withdraw_insurance_transactions)) {
                    return response()->json([
                        'can_add_retrieval_insurance_transaction' => false,
                        'status' => 'another_retrieval_insurance_found'
                    ]);
                } else {
                    return response()->json([
                        'can_add_retrieval_insurance_transaction' => true,
                        'insurance_transaction' => $reservations_deposit_insurance_transactions[0][0]
                    ]);
                }


            }


            if ($request->get('from') == 'reservation') {
                return response()->json([
                    'withdraw_insurance_transactions' => count($reservations_withdraw_insurance_transactions)
                ]);
            }


            if ($request->get('from') == 'edit_deposit') {
                return response()->json([
                    'withdraw_insurance_transactions' => count($reservations_withdraw_insurance_transactions),
                    'deposit_insurance_transactions' => count($reservations_deposit_insurance_transactions)
                ]);
            }


            if ($request->get('from') == 'edit_withdraw') {
                return response()->json([
                    'withdraw_insurance_transactions' => count($reservations_withdraw_insurance_transactions),
                    'deposit_insurance_transactions' => count($reservations_deposit_insurance_transactions),
                    'deposit_insurance_transaction' => count($reservations_deposit_insurance_transactions) ? $reservations_deposit_insurance_transactions[0][0] : null,
                ]);
            }


        }
    }

    /**
     * Detected if we shall make a full checkout or simple checkout for group reservation
     *
     * @param Reservation $reservation
     * @return void
     */
    public function checkIfCurrentReservationIsTheLastOneToCheckout(Reservation $reservation)
    {
        $reservations_deposit_insurance_transactions = [];
        $reservations_withdraw_insurance_transactions = [];
        $balances = [];
        if ($reservation->reservation_type == 'group') {
            $main_reservation = null;
            if (is_null($reservation->attachable_id)) {
                $main_reservation = $reservation;
                $push_main_reservation_to_collection = false;
            } else {
                $main_reservation = Reservation::find($reservation->attachable_id);
                $push_main_reservation_to_collection = true;
            }

            $reservations = Reservation::with('wallet')
                ->where('reservation_type', 'group')
                ->where('company_id', $reservation->company_id)
                ->where(function ($query) use ($reservation, $main_reservation) {
                    return $query->where('id', $reservation->id)->orWhere('attachable_id', $main_reservation->id);
                })
                ->where('status', 'confirmed')
                // ->whereNull('checked_out')
                ->whereNull('deleted_at')
                ->orderBy('created_at')
                ->get();

            if ($push_main_reservation_to_collection) {
                $reservations->push($main_reservation);
            }


            if (count($reservations)) {


                // Checking insurance transaction
                foreach ($reservations as $reservationObject) {
                    $balances[] = $reservationObject->wallet->decimal_places == 3 ? $reservationObject->balance / 1000 : $reservationObject->balance / 100;

                    if ($reservationObject->depositInsuranceTransactions()->count()) {
                        $reservations_deposit_insurance_transactions[] = $reservationObject->depositInsuranceTransactions;
                    }
                    if ($reservationObject->withdrawInsuranceTransactions()->count()) {
                        $reservations_withdraw_insurance_transactions[] = $reservationObject->withdrawInsuranceTransactions;
                    }
                }

                $checkedOutReservationsCount = $reservations->filter(function ($item) {
                    return $item->checked_out;
                })->values()->count();

                if ($reservations->count() - $checkedOutReservationsCount == 1) {
                    // it means there is only one reservation
                    // so we shall make the normal checkout as we used
                    return response()->json([
                        'simple_checkout' => false,
                        'is_last' => true,
                        'message' => 'this is the last reservation to make a full checkout',
                        'group_balance' => array_sum($balances),
                        'withdraw_insurance_transactions' => count($reservations_withdraw_insurance_transactions),
                        'deposit_insurance_transactions' => count($reservations_deposit_insurance_transactions),
                        'deposit_insurance_transaction' => count($reservations_deposit_insurance_transactions) ? $reservations_deposit_insurance_transactions[0][0] : null,
                    ]);
                }

                // perform simple checkout
                return response()->json([
                    'simple_checkout' => true,
                    'is_last' => false,
                    'message' => 'this is not the last reservation to make a full checkout',
                    'group_balance' => array_sum($balances)
                ]);
            }
        }
    }


    public function getQoyodLogs(Request $request, Reservation $reservation)
    {
        return \response()->json($reservation->qoyodLogs());
    }

    /**
     * Generate Automated Invoice For Group Reservation
     *
     * @param Reservation $reservation
     * @return void
     */
    public function createAutomatedGroupInvoice(Reservation $reservation)
    {
        return response()->json('welcome');
        if ($reservation->reservation_type == 'group') {
            $reservation['attachable_reservations_count'] = count($reservation->attachedReservations());
            $services = [];
            $balances = [];
            $shared_invoice_total_price = null;
            $main_reservation = null;
            $push_main_reservation_to_collection = false;
            $has_at_least_one_vat = false;
            $reservations_minified = [];
            if (is_null($reservation->attachable_id)) {
                $main_reservation = $reservation;
                $push_main_reservation_to_collection = false;
            } else {
                $main_reservation = Reservation::with('unit')->find($reservation->attachable_id);
                $push_main_reservation_to_collection = true;
            }


            $reservations = Reservation::with('wallet', 'unit', 'customer')
                ->where('reservation_type', 'group')
                ->where('company_id', $reservation->company_id)
                ->where(function ($query) use ($reservation, $main_reservation) {
                    return $query->with('unit')->where('id', $reservation->id)->orWhere('attachable_id', $main_reservation->id);
                })
                ->where('status', 'confirmed')
                // ->whereNull('checked_out')
                ->whereNull('deleted_at')
                ->get();

            if ($push_main_reservation_to_collection) {
                $reservations->push($main_reservation);
            }
            foreach ($reservations as $reservationObject) {
                $balances[] = $reservationObject->wallet->decimal_places == 3 ? $reservationObject->balance / 1000 : $reservationObject->balance / 100;
                $shared_invoice_total_price += $reservationObject->total_price + $reservationObject->getServicesSum();
                $reservations_minified[] = [
                    'sub_total' => $reservationObject->sub_total,
                    'ewa' => $reservationObject->ewa_total,
                    'vat' => $reservationObject->vat_total,
                    'ttx' => $reservationObject->ttx_total,
                    'total_price' => $reservationObject->total_price
                ];
                if ($reservationObject->services()->count()) {
                    foreach ($reservationObject->services as $transaction) {
                        $services[] = $transaction;
                    }
                }

                if ($reservationObject->vat_total) {
                    $has_at_least_one_vat = true;
                }
            }

            // $reservation['group_reservation_transactions'] = collect($transactions)->sortByDesc('number')->values();
            $reservation['group_reservation_services'] = collect($services)->sortByDesc('service_log_number')->values();
            $reservation['shared_invoice_total_price'] = $shared_invoice_total_price;

            $invoice = new ReservationInvoice();
            $invoice->team_id = $reservation->team_id;
            $invoice->reservation_id = $main_reservation->id;
            $invoice->from = $reservation->date_in;
            $invoice->to = $reservation->date_out;

            $invoice->data = [
                'group_balance' => array_sum($balances),
                'has_at_least_one_vat' => $has_at_least_one_vat,
                'reservations' => $reservations,
                'reservations_minified' => $reservations_minified,
                'company' => $reservation->company,
                'services' => $reservation['group_reservation_services'],
                'amount' => $reservation['shared_invoice_total_price'],
                'extra_addon' => count($reservations) > 1 ? null : [
                    'customer_name' => $reservations[0]->customer->name,
                    'customer_id_number' => $reservations[0]->customer->id_number,
                    'unit_name' => $reservations[0]->unit->name,
                    'unit_number' => $reservations[0]->unit->unit_number,
                ]
            ];

            $counter = TeamCounter::where('team_id', $reservation->team_id)->first();
            $next = $counter->invoice_num;
            $invoice->number = $next;
            $invoice->note = __('Automated Invoice');
            $invoice->created_by = auth()->user()->id;
            $counter->last_invoice_number = $next;
            $counter->save();
            $invoice->save();
            return response()->json(['success' => true, 'invoice' => $invoice]);

        }
    }
    /**
     * Create a credit note for an invoice
     *
     * @param ReservationInvoice $reservationInvoice
     * @return void
     */
    public function createCreditNote(ReservationInvoice $reservationInvoice)
    {
        if (TeamCounter::get()->count()) {
            $counter = TeamCounter::first();
        } else {
            $counter = TeamCounter::create();
        }
        $next = $counter->credit_note_num;

        $creditNote = new InvoiceCreditNote();
        $creditNote->number = $next;
        $creditNote->reservation_invoice_id = $reservationInvoice->id;
        $creditNote->team_id = auth()->user()->current_team_id;
        $creditNote->created_by = auth()->user()->id;
        $counter->last_credit_note_number = $next;
        $counter->save();

        /**
         * This is a new dev as we used to not store transactions ids alongside with the invoice
         * but now we are storing it inside data column as json
         * so if this invoice isset a transactions ids key
         * i will fetch those transactions and set them free again as the invoice now is deleted
         */

        if (isset($reservationInvoice->data['transactions_ids'])) {
            $transactions = Transaction::whereIn('id', $reservationInvoice->data['transactions_ids'])->get();
            if (count($transactions)) {
                foreach ($transactions as $transaction) {
                    $transaction->is_attached_to_invoice = 0;
                    $transaction->save();
                }
            }
        }

        //process invoice fro zatca
        $res = null;
        if ($reservationInvoice->is_reported_to_zatca && $reservationInvoice->is_reported_to_zatca !== null) {
            $in = $reservationInvoice->is_reported_to_zatca;
            $invoice_type = '';
            $invoice_sub_type = 'credit note';
            $is_reported_to_zatca = $reservationInvoice->is_reported_to_zatca;

            if (isset($is_reported_to_zatca->reportingStatus)) {
                $invoice_type = "simplified tax invoice";
            }

            if (isset($is_reported_to_zatca->clearanceStatus)) {
                $invoice_type = "tax invoice";
            }

            $res = $this->syncInvoiceOnCreate($reservationInvoice, $invoice_type, $invoice_sub_type, "0");
            if(isset($res->is_reported_to_zatca)) {
                $creditNote->is_reported_to_zatca = $res->is_reported_to_zatca;
            }
        }

        $creditNote->save();

        return response()->json(['success' => true, 'credit_note' => $creditNote, 'invoice' => $reservationInvoice]);
    }

    public function checkCreditNote(ReservationInvoice $reservationInvoice)
    {
        if ($reservationInvoice->invoiceCreditNote) {
            return response()->json(['success' => true, 'invoiceCreditNote' => $reservationInvoice->invoiceCreditNote]);
        }
        return response()->json(['success' => false, 'invoiceCreditNote' => null]);
    }

    public function getInvoices(Request $request, $id)
    {
        $main_reservation_id = $id;
        // as we store group invoices on main reservation
        if ($request->has('type') && $request->get('type') == 'group') {
            $main_reservation_id = $request->get('main_reservation_id');
        }

        $reservation = Reservation::with('invoices')->find($main_reservation_id);
        return response()->json($reservation->invoices);
    }

    public function getNights($reservation)
    {
        $date_in = new \DateTime($reservation->date_in);
        $date_out = new \DateTime($reservation->date_out);
        return $date_out->diff($date_in)->days ;
    }
    public function createGroupInvoice(Request $request)
    {
        if($request->note == 'Automated Invoice'){
            // it means incoming group invoice from group checkout
            $excluded_teams_from_auto_invoicing = explode(',' , env('AUTO_INVOICE_AFTER_CHECKOUT_DISABLED_TEAMS'));
            if(in_array(auth()->user()->current_team_id,$excluded_teams_from_auto_invoicing)){
                return response()->json(['success' => false , 'message' => 'Automated invoice creation disabled from env']);
            }
        }

        $team_id = auth()->user()->current_team_id;
        $company = Company::find($request->get('company_id'));
        $invoice = new ReservationInvoice();
        $invoice->team_id = $team_id;
        $invoice->from = date(Carbon::createFromTimestamp(strtotime($request->get('from_date')))->format('Y-m-d'));
        $invoice->to = date(Carbon::parse($request->get('to_date'))->addDay()->format('Y-m-d'));
        $counter = TeamCounter::where('team_id', $team_id)->first();
        $next = $counter->invoice_num;
        $invoice->number = $next;
        $invoice->note = $request->note;
        $counter->last_invoice_number = $next;
        $to = new \DateTime($invoice->to);
        $from = new \DateTime($invoice->from);
        $parsedFrom = Carbon::parse($from);
        $parsedTo = Carbon::parse($to);
        $period = CarbonPeriod::create($parsedFrom, $parsedTo);
        $main_sub_total = 0;
        $reservations = Reservation::whereIn('id', $request->get('all_grouped_reservations_ids'))->get();
        $reservations_subtotals_arr = [];
        $reservations_ewa_percentage_arr = [];
        $reservations_vat_percentage_arr = [];
        $reservations_tourism_percentage_arr = [];
        $balances = [];
        $periods = [];
        $has_at_least_one_vat = false;
        $reservations_units_holder = [];
        if (count($reservations)) {
            foreach ($reservations as $reservation) {
                $reservation_scoped_sub_total = 0;
                if (is_null($reservation->attachable_id)) {
                    $invoice->reservation_id = $reservation->id;
                }
                $reservations_ewa_percentage_arr[$reservation->id] = $reservation->old_prices['ewa_parentage'];
                $reservations_vat_percentage_arr[$reservation->id] = $reservation->old_prices['vat_parentage'];
                $reservations_tourism_percentage_arr[$reservation->id] = $reservation->old_prices['tourism_percentage'];
                $balances[] = $reservation->wallet->decimal_places == 3 ? $reservation->balance / 1000 : $reservation->balance / 100;
                $reservations_units_holder[$reservation->id][] = [
                    'id' => $reservation->unit->id,
                    'name' => json_decode($reservation->unit->getOriginal('name')),
                    'unit_number' => $reservation->unit->unit_number
                ];
                if ($reservation->vat_total) {
                    $has_at_least_one_vat = true;
                }
                foreach ($period as $date) {
                    if ($reservation->rent_type == 1) {
                        // it's a daily reservation
                        foreach ($reservation->prices['days'] as $obj) {
                            if (Carbon::parse($obj['date'])->format('Y-m-d') == $date->format('Y-m-d')) {
                                $nights = $this->getNights($reservation);
                                if(count($reservations) == 1){
                                    $night_price = $reservation->sub_total / $nights;
                                    $reservation_scoped_sub_total = $night_price;
                                    $reservations_subtotals_arr[$reservation->id][] = $reservation_scoped_sub_total;
                                    $periods[$reservation->id][] = $date->format('Y-m-d');
                                }else{
                                    $main_sub_total += $obj['price'];
                                    $reservation_scoped_sub_total = $obj['price'];
                                    $reservations_subtotals_arr[$reservation->id][] = $reservation_scoped_sub_total;
                                    $periods[$reservation->id][] = $date->format('Y-m-d');
                                }
                            }
                        }
                    } else {
                        // it's a monthly reservation
                        // i need to construct an array that will hold days and create period here for monthly reservations
                        $monthlyDays = [];
                        $monthlyStart = new \DateTime($reservation->date_in);
                        $monthlyEnd = new \DateTime($reservation->date_out);
                        $parsedMonthlyStart = Carbon::parse($monthlyStart);
                        $parsedMonthlyEnd = Carbon::parse($monthlyEnd);

                        if ($parsedMonthlyStart != $parsedMonthlyEnd) {
                            $parsedMonthlyEnd->subDay();
                        }

                        $monthlyPeriod = CarbonPeriod::create($parsedMonthlyStart, $parsedMonthlyEnd);
                        $single_night_in_month_price = $reservation->sub_total / $reservation->nights;

                        foreach ($monthlyPeriod as $obj) {
                            if (Carbon::parse($obj)->format('Y-m-d') == $date->format('Y-m-d')) {
                                $main_sub_total += $single_night_in_month_price;
                                $reservation_scoped_sub_total = $single_night_in_month_price;
                                $reservations_subtotals_arr[$reservation->id][] = $reservation_scoped_sub_total;
                                $periods[$reservation->id][] = $date->format('Y-m-d');
                            }
                        }

                        // $main_sub_total += $reservation->sub_total / $reservation->nights;
                        // $reservation_scoped_sub_total = $reservation->sub_total / $reservation->nights;
                        // $reservations_subtotals_arr[$reservation->id] [] = $reservation_scoped_sub_total;
                        // $periods [$reservation->id] [] = $date->format('Y-m-d');
                    }

                }
            }
        }
        $reservations_minified = [];
        $reservations_minified_keyed = [];
        $services_sum = 0;
        $services = [];
        $invoice_total_reservation_amount = 0;
        foreach ($reservations_subtotals_arr as $key => $subtotals_arr) {
            $current_reservation_ewa_percentage = $reservations_ewa_percentage_arr[$key];
            $current_reservation_vat_percentage = $reservations_vat_percentage_arr[$key];
            $current_reservation_tourism_percentage = $reservations_tourism_percentage_arr[$key];
            $current_reservation_sub_total = array_sum($subtotals_arr);
            $ewa_amount = ($current_reservation_sub_total / 100) * $current_reservation_ewa_percentage;
            $ttx_amount = ($current_reservation_sub_total / 100) * $current_reservation_tourism_percentage;
            $vat_amount = (($current_reservation_sub_total + $ewa_amount) / 100) * $current_reservation_vat_percentage;
            $reservation_std = new stdClass();
            $reservation_std->id = $key;
            $reservation_std->unit = $reservations_units_holder[$key];
            $reservation_std->ewa = (float) number_format($ewa_amount, 2, '.', '');
            $reservation_std->ttx = (float) number_format($ttx_amount, 2, '.', '');
            $reservation_std->vat = (float) number_format($vat_amount, 2, '.', '');
            $reservation_std->sub_total = (float) number_format($current_reservation_sub_total, 2, '.', '');
            $reservation_std->total_price = (float) number_format($current_reservation_sub_total + $ewa_amount + $ttx_amount + $vat_amount, 2, '.', '');
            $reservations_minified[] = $reservation_std;

            $reservation_std_keyed = new stdClass();
            $reservation_std_keyed->id = $key;
            $reservation_std_keyed->ewa = (float) number_format($ewa_amount, 2, '.', '');
            $reservation_std_keyed->ttx = (float) number_format($ttx_amount, 2, '.', '');
            $reservation_std_keyed->vat = (float) number_format($vat_amount, 2, '.', '');
            $reservation_std_keyed->sub_total = (float) number_format($current_reservation_sub_total, 2, '.', '');
            $reservation_std_keyed->total_price = (float) number_format($current_reservation_sub_total + $ewa_amount + $ttx_amount + $vat_amount, 2, '.', '');
            $reservations_minified_keyed[$key][] = $reservation_std_keyed;
            $invoice_total_reservation_amount += $reservation_std->total_price;
        }

        /**
         * The part of attaching services to invoices - very important
         */
        $maximum_possible_date_to_as_invoice = Carbon::parse($request->get('dates_calculations')['end_date'])->subDay()->format('Y-m-d');
        $grouped_reservations_ids = $request->get('all_grouped_reservations_ids');
        ;
        if ($invoice->to == $maximum_possible_date_to_as_invoice) {
            $filterServicesForGroupReservationInoice = $this->filterServicesForGroupReservationInvoice($grouped_reservations_ids, $from, $to, true);
        } else {
            $filterServicesForGroupReservationInoice = $this->filterServicesForGroupReservationInvoice($grouped_reservations_ids, $from, $to);
        }

        $services_sum = abs($filterServicesForGroupReservationInoice['servicesSum']);
        if (isset($filterServicesForGroupReservationInoice['services'])) {
            foreach ($filterServicesForGroupReservationInoice['services'] as $service) {
                $services[] = $service;
            }
        }

        $invoice_total_amount_with_services = (float) number_format($invoice_total_reservation_amount + $services_sum, 2, '.', '');

        $custom_reservations = [];
        if (count($reservations)) {
            foreach ($reservations as $reservation) {
                $reservationSkeleton = new stdClass();
                $reservationSkeleton->id = $reservation->id;
                $reservationSkeleton->attachable_id = $reservation->attachable_id;
                $reservationSkeleton->number = $reservation->number;
                $reservationSkeleton->ewa_total = $reservation->ewa_total;
                $reservationSkeleton->vat_total = $reservation->vat_total;
                $reservationSkeleton->sub_total = $reservation->sub_total;
                $reservationSkeleton->total_price = $reservation->total_price;
                $reservationSkeleton->unit = [
                    'id' => $reservation->unit->id,
                    'name' => json_decode($reservation->unit->getOriginal('name')),
                    'unit_number' => $reservation->unit->unit_number
                ];
                $reservationSkeleton->customer = $reservation->customer ? [
                    'id' => $reservation->customer->id,
                    'name' => $reservation->customer->name,
                    'id_number' => $reservation->customer->id_number,
                    'phone' => $reservation->customer->phone,
                ] : null;

                $reservationSkeleton->old_prices = [
                    'ewa_parentage' => $reservation->old_prices['ewa_parentage'],
                    'vat_parentage' => $reservation->old_prices['vat_parentage'],
                ];
                $reservationSkeleton->rent_type = $reservation->rent_type;
                $custom_reservations[] = $reservationSkeleton;
            }
        }

        $final_data = [
            'amount' => $invoice_total_amount_with_services,
            'company' => $company,
            'services' => $services,
            'extra_addon' => null,
            'reservations' => $custom_reservations,
            'group_balance' => array_sum($balances),
            'has_at_least_one_vat' => $has_at_least_one_vat,
            'reservations_minified' => $reservations_minified,
            'reservations_minified_keyed' => $reservations_minified_keyed,
            'periods' => $periods,
            'transactions_ids' => $filterServicesForGroupReservationInoice['transactions_ids']
        ];
        // dd($final_data);
        $invoice->data = $final_data;
        $invoice->is_group_reservation = 1;
        $invoice->created_by = auth()->user()->id;
        $counter->save();
        $invoice = $this->syncInvoiceOnCreate($invoice, "tax invoice", "invoice", "0");
        $invoice->save();
        return response()->json(['invoice' => $invoice->load('invoiceCreditNote')]);
    }


    private function filterServicesForGroupReservationInvoice($reservations_ids, $from, $to, $is_last_invoice = false)
    {
        $services = [];
        $servicesSum = [];
        if ($is_last_invoice) {
            $servicesTransactions = Transaction::with('wallet')
                ->where('payable_type', 'App\\Reservation')
                ->whereIn('payable_id', $reservations_ids)
                ->where('is_public', 0)
                ->where('meta->category', 'service')
                ->where('is_attached_to_invoice', 0)
                ->get();
        } else {
            $servicesTransactions = Transaction::with('wallet')
                ->where('payable_type', 'App\\Reservation')
                ->whereIn('payable_id', $reservations_ids)
                ->where('is_public', 0)
                ->where('meta->category', 'service')
                ->whereDate('created_at', '>=', $from)
                ->whereDate('created_at', '<=', $to)
                ->get();
        }
        // $servicesTransactions  = Transaction::with('wallet')->where('payable_type', 'App\\Reservation')->where('payable_id', $reservation_id)->where('is_public', 0)->where('meta->category', 'service')->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->get();
        $transactions_ids = [];
        if (count($servicesTransactions)) {
            foreach ($servicesTransactions as $transaction) {
                $servicesSum[] = $transaction->amount / ($transaction->wallet->decimal_places == 3 ? 1000 : 100);
                foreach ($transaction->meta['services'] as $serviceObj) {
                    $services[] = $serviceObj;
                    $transaction->is_attached_to_invoice = 1;
                    $transactions_ids[] = $transaction->id;
                    $transaction->save();
                }
            }
        }
        return ['services' => $services, 'servicesSum' => array_sum($servicesSum), 'transactions_ids' => $transactions_ids];
    }

    public function createInvoiceForFreeServices(Request $request, $services_transactions_ids = null)
    {
        $reservation = Reservation::find($request->get('reservation_id'));
        $invoice = new ReservationInvoice();
        $invoice->team_id = $reservation->team_id;
        $invoice->reservation_id = $request->get('reservation_id');
        $invoice->from = date(Carbon::createFromTimestamp(strtotime($reservation->date_out))->format('Y-m-d'));
        $invoice->to = date(Carbon::createFromTimestamp(strtotime($reservation->date_out))->format('Y-m-d'));
        $counter = TeamCounter::where('team_id', $reservation->team_id)->first();
        $next = $counter->invoice_num;
        $invoice->number = $next;
        $invoice->note = $request->note;
        $counter->last_invoice_number = $next;
        $to = new \DateTime($invoice->to);
        $from = new \DateTime($invoice->from);
        $diff = $to->diff($from)->days + 1;
        // collecting data , save them with the invoice
        $data = new \stdClass();
        $data->sub_total = 0;
        $data->vat = 0;
        $data->ewa = 0;
        $data->ttx = 0;
        $data->total_price = 0;
        $data->nights = $diff;
        $data->free_services_invoices = true;
        $handleFreeServicesToCreateAnInvoice = $this->handleFreeServicesToCreateAnInvoice($services_transactions_ids ? $services_transactions_ids : $request->get('transactions_ids'));
        $data->servicesSum = abs($handleFreeServicesToCreateAnInvoice['servicesSum']);
        $data->amount = (float) number_format($data->total_price + $data->servicesSum, 2, '.', '');
        $data->services = $handleFreeServicesToCreateAnInvoice['services'];
        $data->transactions_ids = $services_transactions_ids;
        $invoice->data = $data;
        $invoice->note = $request->get('noteOnInvoice');
        $invoice->created_by = auth()->user()->id;
        $counter->save();
        $invoice->save();
        if ($services_transactions_ids) {
            return $invoice;
        }
        return response()->json(['success' => true, 'invoice' => $invoice->load('invoiceCreditNote')]);
    }

    public function handleFreeServicesToCreateAnInvoice($ids)
    {
        $services = [];
        $transactions_ids = [];
        $servicesSum = [];
        $servicesTransactions = Transaction::with('wallet')->whereIn('id', $ids)->get();

        if (count($servicesTransactions)) {
            foreach ($servicesTransactions as $transaction) {
                $servicesSum[] = $transaction->amount / ($transaction->wallet->decimal_places == 3 ? 1000 : 100);
                foreach ($transaction->meta['services'] as $serviceObj) {
                    $services[] = $serviceObj;
                }
                $transactions_ids[] = $transaction->id;
                $transaction->is_attached_to_invoice = 1;
                $transaction->save();
            }
        }
        return ['services' => $services, 'transactions_ids' => $transactions_ids, 'servicesSum' => array_sum($servicesSum)];
    }

    //mytravel change status method
    public function changeStatusMytravel($reservation_id){
        $fandaqah_ref_id = $reservation_id;
        $reservation = Reservation::find($reservation_id);
        $data = [
            'fandaqah_ref_id' => $fandaqah_ref_id,
            'status' => 'checked_out'
        ];
        $current_date = Carbon::now()->format('Y-m-d');
        $team_id = $reservation->team_id;
        $unit_id = $reservation->unit_id;
        $unit_category_id = Unit::where('id', $unit_id)->first()->unit_category_id;
        $date_in = $reservation->date_in;
        $date_out = $reservation->date_out;
        // convert it to carbon
        $date_in = Carbon::parse($date_in);
        $date_out = Carbon::parse($date_out);
        // get the unit category where id = unit_category_id
        $unit_category = UnitCategory::find($unit_category_id);

        $roomsData = [];
        for ($date = $date_in->copy(); $date->lte($date_out); $date->addDay()) {
            $dayData = [
                'from' => $date->format('Y-m-d'),
                'to' => $date->copy()->addDay()->format('Y-m-d'), // End date is the next day
                // You can add more fields here as needed
            ];
            $roomsData[] = $dayData;
        }
        array_pop($roomsData);
        foreach ($roomsData as $date) {

            $from = $date['from'];
            $to = $date['to'];
            $dayName = Carbon::parse($from)->format('l');
            $day_Name_price = strtolower($dayName) . '_day_price';

            // $price = $unit_category[$day_Name_price];
            $specialPrice = SpecialPrice::where('unit_category_id', $unit_category_id)->where('start_date', '<=', $date['from'])->where('end_date', '>=', $date['from'])->where('enabled', true)->first();
            if($specialPrice){
                $price = $specialPrice->days_prices[$dayName];
            }else{

                $price = $unit_category[$day_Name_price];
            }
            // get the number of units that is no reservation for this from to date and the unit category id is the same as the request and available to sync is true
            $all_units = Unit::where('unit_category_id', $unit_category_id)
            ->where('status', '!=', 3)
            ->where('deleted_at', null)->pluck('id')->toArray();
            $available_arr = [];
            foreach($all_units as $unit_id){
                $hasIntersectionWorkable = checkIfUnitHasReservation($unit_id, Carbon::parse($from));
                if(!$hasIntersectionWorkable){
                    array_push($available_arr, $unit_id);
                }
        }
        $available_units = count($available_arr);
            $ewa_percantage = getEwaPercentageForUnit($team_id);
            $tax_percentage = getVatPercentageForUnit($team_id);
            $ewa_total = getEwaTotalForUnit($price, $ewa_percantage);
            $tax_total = getVatTotalForUnit($price, $ewa_total, $tax_percentage);
            $total_price = $price + $ewa_total + $tax_total;
            $data = [
                'fandaqah_ref_id' => $fandaqah_ref_id,
                'category_id' => $unit_category_id,
                'start_date' => $from,
                'end_date' => $from,
                'price' => $total_price,
                'active' => 1,
                'note_to_customer' => '',
                'note_to_admin' => '',
                'is_instant' => 1,
                'number' => $available_units,
                'create_user' => $team_id,
                'update_user' => $team_id,
                'status' => "checkout",
                'original_date_in' => $date_in->format('Y-m-d'),
                'original_date_out' => $date_out->format('Y-m-d'),
            ];

        $url = env('MYTRAVEL_API_URL') . '/api/booking/check-out';
        $key = env('MY_TRAVEL_KEY');
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 400,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'key: ' . $key
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);


    }}
    //end mytravel change status method

    /**
     * Entity may be promissory or transaction ( deposit - withdraw ) or may be a simple checkout only
     */
    public function createEntityAndCheckout(Request $request)
    {
        $reservation_id = $request->get('reservation_id');
        if ($request->has('liquidation_type') && $request->get('liquidation_type') == 'promissory') {
            // handle promissory
            $this->handlePromissoryCreationWhenPerformingCheckoutActionForSingleReservation($request, $reservation_id);
        } else {
            // handle transaction
            // checkout_type maybe with crwating transaction or simple checkout
            if ($request->checkout_type == 'transaction') {
                $this->handleTransactionCreationWhenPerformingCheckoutActionForSingleReservation($request, $reservation_id);
            }
        }
        // handle invoice creation if any
        $this->handleInvoiceCreationWhenPerformingCheckoutActionForSingleReservation($request, $reservation_id);

        // handle reservation update for checked_out , action columns
        $updated_reservation = $this->updateReservationCheckoutInformation($request, $reservation_id);


        $automatic_under_cleaning = Setting::where('team_id', $request->get('team_id'))->where('key', 'automatic_under_cleaning')->first();
        if ($automatic_under_cleaning !== null) {

            if ($automatic_under_cleaning->value == 1) {

                $this->handleReservationUnitCleaningStatus($request, $updated_reservation);
            }
        }
        // if we passed safely to here , then it's time to handle the cleaning

        // Fire events if found
        if ($updated_reservation) {
            // reservation checkout event , that will be used later -_-
            event(new ReservationCheckout($updated_reservation));
        }

        // $re-filled reservation
        $refilled_reservation = $updated_reservation->load('promissory', 'invoices', 'transactions', 'services');

        $reservation = Reservation::find($reservation_id);
        //mytravel change status to checkout
        $team = Team::find($reservation->team_id);
        if ($team->mytravel_hotel_id != null) {
            $mytravel = $this->changeStatusMytravel($reservation_id);


        }
        //check if the reservation is from iosell
        if($reservation->cmBookingId != null){
            $otaReservation = OtaReservation::where('cm_booking_id', $reservation->cmBookingId)->first();
            if($otaReservation){
                $otaReservation->update([
                    'is_open' => false,
                ]);
            }
        }

        //end mytravel change status to checkout

        return response()->json([
            'success' => true,
            'refilled_reservation' => [
                'promissory' => $refilled_reservation->promissory,
                'invoices' => $refilled_reservation->invoices,
                'transactions' => $refilled_reservation->transactions,
                'services' => $refilled_reservation->services,
                'balance' => $refilled_reservation->balance,
                'checked_out' => Carbon::parse($refilled_reservation->checked_out)->format('Y-m-d H:i'),
            ]
        ]);

    }

    function handleTransactionCreationWhenPerformingCheckoutActionForSingleReservation($request, $reservation_id)
    {
        $reservation = Reservation::find($request->get('reservation_id'));
        $transaction_date = date('Y-m-d H:i');
        $meta = [
            "category" => $request->meta['category'],
            "statement" => $request->meta['statement'],
            "type" => $request->meta['type'],
            "payment_type" => $request->meta['payment_type'],
            "note" => $request->meta['note'],
            "reference" => $request->meta['reference'],
            "date" => $transaction_date,
            "from" => $request->get('transaction_type') === 'deposit' && isset($request->meta['from']) ? $request->meta['from'] : null,
            "employee" => $request->meta['employee'],
            "to" => $request->get('transaction_type') === 'withdraw' && isset($request->meta['to']) ? $request->meta['to'] : null,
            "received_by" => $request->get('transaction_type') === 'withdraw' && isset($request->meta['received_by']) ? $request->meta['received_by'] : null
        ];
        // create transaction based on transaction type
        $request->get('transaction_type') === 'deposit' ? $reservation->depositFloat($request->amount, $meta, true, true) : $reservation->forceWithdrawFloat($request->amount, $meta, true, true);
    }

    function handlePromissoryCreationWhenPerformingCheckoutActionForSingleReservation($request, $reservation_id)
    {
        $promissory = new Promissory;
        $promissory->team_id = $request->get('team_id');
        $promissory->reservation_id = $reservation_id;
        $promissory->user_id = $request->get('user_id');
        $promissory->due_location = $request->get('due_location');
        $promissory->due_date = $request->get('due_date');
        $promissory->due_owner = $request->get('due_owner');
        $promissory->due_for = $request->get('due_for');
        $promissory->total_amount = $request->get('total_amount');
        $promissory->notes = $request->get('notes');

        $counter = TeamCounter::where('team_id', $promissory->team_id)->first();
        if (!$counter) {
            TeamCounter::create(['team_id' => $promissory->team_id]);
        }
        $promissory->serial = $counter->promissory_num;
        $counter->last_promissory_number = $counter->promissory_num;
        $counter->save();
        $promissory->save();
    }

    function handleInvoiceCreationWhenPerformingCheckoutActionForSingleReservation($request, $reservation_id)
    {
        $excluded_teams_from_auto_invoicing = explode(',' , env('AUTO_INVOICE_AFTER_CHECKOUT_DISABLED_TEAMS'));
        $latestInvoiceWithoutCreditNote = ReservationInvoice::where('reservation_id', $reservation_id)
            ->whereNull('deleted_at')
            ->whereDoesntHave('invoiceCreditNote')
            ->orderByDesc('number')
            ->first();
        if ($latestInvoiceWithoutCreditNote) {
            // this means other invoices found for the same reservation and we need to check
            // if seriously there is a space to add new and final invoice
            $last_invoice_date = Carbon::parse($latestInvoiceWithoutCreditNote->to);
            $reservation_last_date = Carbon::parse($latestInvoiceWithoutCreditNote->reservation->date_out)->subDay();
            $diff_in_days = $reservation_last_date->diffInDays($last_invoice_date);

            if ($diff_in_days > 0) {
                // this means there is a space to add a new invoice
                $invoice_from = $last_invoice_date->addDay()->format('Y-m-d');
                $invoice_to = $reservation_last_date->format('Y-m-d');
                $note = 'Automated Invoice';

                $invoice_params = [
                    'id' => $reservation_id,
                    'from_date' => $invoice_from,
                    'to_date' => $invoice_to,
                    'note' => $note
                ];

                if(!in_array(auth()->user()->current_team_id,$excluded_teams_from_auto_invoicing)){
                    // Generate a new invoice for the available period
                    $this->add_invoice($request, $invoice_params);
                }
            } else {
                /**
                 * Our start here
                 * 1- else here will mean that full period completed as invoices and no available dates
                 * 2- then after that i will fetch reservation service transactions
                 * 3- get the filtered services transactions that are not attached to invoices
                 * 4- create a new invoice with start & end date as the checkout date
                 * 5- this invoice will include those not attached service transactions
                 */

                if (count($request->get('services'))) {
                    // it means we have some services
                    $collection = collect($request->get('services'));
                    // filtering services to fetch only free services which is not attached to any invoice
                    $services_transactions_ids = $collection->filter(function ($item, $key) {
                        return $item['is_attached_to_invoice'] === 0;
                    })->pluck('id')->toArray();
                    if (count($services_transactions_ids)) {
                        // this now means some free services found and ready to generate an invoice for them
                        // Generate a new invoice for the available period
                        $this->createInvoiceForFreeServices($request, $services_transactions_ids);
                    }
                }
            }
        } else {

            /**
             * There are no invoices at all so we will create a full period invoice
             */
            $invoice_from = Carbon::parse($request->get('from_date'))->format('Y-m-d');
            $invoice_to = Carbon::parse($request->get('to_date'))->subDay()->format('Y-m-d');
            $note = 'Automated Invoice';

            $invoice_params = [
                'id' => $reservation_id,
                'from_date' => $invoice_from,
                'to_date' => $invoice_to,
                'note' => $note
            ];
            if(!in_array(auth()->user()->current_team_id,$excluded_teams_from_auto_invoicing)){
                // Generate a new invoice for the available period
                $this->add_invoice($request, $invoice_params);
            }
        }
    }

    function updateReservationCheckoutInformation($request, $reservation_id)
    {
        $reservation = Reservation::find($reservation_id);
        $reservation->checked_out = new \DateTime($request->get('time'));
        $reservation->action_type = Reservation::ACTION_CHECKEDOUT;
        $reservation->save();
        return $reservation;
    }

    function handleReservationUnitCleaningStatus($request, $reservation)
    {
        $reservation->unit->status = Unit::STATUS_UNDER_CLEANING;
        UnitCleaning::create([
            'unit_id' => $reservation->unit->id,
            'start_at' => new \DateTime(),
            'team_id' => $reservation->unit->team_id
        ]);
        $reservation->unit->save();
    }

    public function convertToGroupReservation(Request $request)
    {
        if ($request->has('reservation_id')) {
            $reservation = Reservation::find($request->get('reservation_id'));
            if ($request->get('reservation_type') == 'group') {
                // its a group reservation of entity type individual and needs a company
                $push_main_reservation_to_collection = false;
                if (is_null($reservation->attachable_id)) {
                    $main_reservation = $reservation;
                    $push_main_reservation_to_collection = false;
                } else {
                    $main_reservation = Reservation::find($reservation->attachable_id);
                    $push_main_reservation_to_collection = true;
                }
                $reservations = Reservation::with('wallet', 'unit')
                    ->where('reservation_type', 'group')
                    ->where('company_id', $reservation->company_id)
                    ->where(function ($query) use ($reservation, $main_reservation) {
                        return $query->with('unit')->where('id', $reservation->id)->orWhere('attachable_id', $main_reservation->id);
                    })
                    ->where('status', 'confirmed')
                    ->whereNull('deleted_at')
                    ->get();

                if ($push_main_reservation_to_collection) {
                    $reservations->push($main_reservation);
                }
                if (count($reservations)) {
                    foreach ($reservations as $reservationObject) {
                        $reservationObject->company_id = $request->get('company_id');
                        $reservationObject->save();
                    }
                    return response()->json(['success' => true, 'reload_reservation' => true], Response::HTTP_CREATED);
                }
            } else {
                $reservation->company_id = $request->get('company_id');
                $reservation->reservation_type = 'group';
                $reservation->save();
                return response()->json(['success' => true, 'reload_reservation' => true]);
            }

        }
    }
    public function updateGroupReservationPriceThroughUnitCategory(Request $request)
    {

        /** @todo : please refresh the balance as it affect only one unit if multiple changed  */
        $incoming_unit_category_id = $request->get('unit_category_id');
        $incoming_total_price = $request->get('price');
        $team = auth()->user()->currentTeam;
        $integration_shms = $team->integration_shms;
        $integration_scth = $team->integration_scth;

        $x = $incoming_total_price;
        $e = $team->ewa() ? $team->ewa() / 100 : 0;
        $v = $team->vat() ? $team->vat() / 100 : 0;
        $t = $team->ttx() ? $team->ttx() / 100 : 0;
        $y = $x / (1 + $e + $t + $v + ($v * $e));

        $ewa = $y * $e;
        $vat = ($y + $ewa) * $v;
        $ttx = $y * $t;

        $bluePrintPricesObject = new stdClass;
        $bluePrintPricesObject->total_price = $x;
        $bluePrintPricesObject->sub_total = $y;
        $bluePrintPricesObject->ewa = $ewa;
        $bluePrintPricesObject->vat = $vat;
        $bluePrintPricesObject->ttx = $ttx;

        $reservations =
            Reservation::with('unit', 'unit.unit_category', 'wallet')->whereIntegerInRaw('id', $request->get('all_grouped_reservations_ids'))
                ->whereHas('unit', function ($u) use ($incoming_unit_category_id) {
                    $u->where('unit_category_id', $incoming_unit_category_id);
                })
                ->get();
        // Now after having reservations filtered with unit_category_id
        foreach ($reservations as $reservation) {

            $division = $incoming_total_price - $reservation->total_price;

            $reservation->total_price = $bluePrintPricesObject->total_price;
            $reservation->sub_total = $bluePrintPricesObject->sub_total;
            $reservation->ewa_total = $bluePrintPricesObject->ewa;
            $reservation->vat_total = $bluePrintPricesObject->vat;
            $reservation->ttx_total = $bluePrintPricesObject->ttx;

            $old_prices = $reservation->unit->getDatesFromRangeWithOldPrices(Carbon::parse($reservation->date_in), Carbon::parse($reservation->date_out), $reservation->old_prices, 0, $reservation->rent_type, $reservation->rent_type);
            $old_prices_price = $old_prices['price'] > 0 ? $old_prices['price'] : 1;
            $reservation->change_rate = (($reservation->sub_total / $old_prices_price) - 1) * 100;
            $reservation->prices = $reservation->unit->getDatesFromRangeWithOldPrices(Carbon::parse($reservation->date_in), Carbon::parse($reservation->date_out), $reservation->old_prices, $reservation->change_rate, $reservation->rent_type, $reservation->rent_type);
            $reservation->action_type = Reservation::ACTION_UPDATE_GROUP_RESERVATION_PRICE_FROM_MODAL;
            if ($reservation->save()) {

                if ($division < 0) {

                    DB::transaction(function () use ($reservation, $division) {
                        DB::table('transactions')->insert([
                            'payable_id' => $reservation->id,
                            'payable_type' => 'App\Reservation',
                            'wallet_id' => $reservation->wallet->id,
                            'type' => 'deposit',
                            'amount' => floatval(abs($division)) * 100,
                            'is_public' => 0,
                            'confirmed' => 1,
                            'created_by' => auth()->user()->id,
                            'updated_by' => auth()->user()->id,
                            'meta' => json_encode([
                                'grp_pricing_modal' => true,
                                'category' => 'update_reservation',
                                'statement' => 'update Reservation Total Price deposit'
                            ]),
                            'uuid' => Str::uuid(),
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    });

                }
                if ($division > 0) {

                    DB::transaction(function () use ($reservation, $division) {
                        DB::table('transactions')->insert([
                            'payable_id' => $reservation->id,
                            'payable_type' => 'App\Reservation',
                            'wallet_id' => $reservation->wallet->id,
                            'type' => 'withdraw',
                            'amount' => -1 * ($division * 100),
                            'is_public' => 0,
                            'confirmed' => 1,
                            'created_by' => auth()->user()->id,
                            'updated_by' => auth()->user()->id,
                            'meta' => json_encode([
                                'grp_pricing_modal' => true,
                                'category' => 'update_reservation',
                                'statement' => 'update Reservation Total Price withdraw'
                            ]),
                            'uuid' => Str::uuid(),
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    });
                }



                if ($reservation->checked_in && $integration_shms) {
                    event(new ShomosReservationUpdated($reservation));
                }

                if ($reservation->scth_reference && $integration_scth) {
                    event(new ReservationUpdated($reservation));
                }


            }

        }

        $main_reservation = null;
        $push_main_reservation_to_collection = false;
        if (is_null($reservation->attachable_id)) {
            $main_reservation = $reservation;
            $push_main_reservation_to_collection = false;
        } else {
            $main_reservation = Reservation::find($reservation->attachable_id);
            $push_main_reservation_to_collection = true;
        }


        if ($main_reservation->status == 'canceled') {
            $reservations = Reservation::with('wallet', 'unit')
                ->where('reservation_type', 'group')
                ->where('company_id', $reservation->company_id)
                ->where(function ($query) use ($reservation, $main_reservation) {
                    return $query->with('unit')->where('id', $reservation->id)->orWhere('attachable_id', $main_reservation->id);
                })
                ->where('status', 'canceled')
                ->whereNull('deleted_at')
                ->get();

        } else {
            $reservations = Reservation::with('wallet', 'unit')
                ->where('reservation_type', 'group')
                ->where('company_id', $reservation->company_id)
                ->where(function ($query) use ($reservation, $main_reservation) {
                    return $query->with('unit')->where('id', $reservation->id)->orWhere('attachable_id', $main_reservation->id);
                })
                ->whereIn('status', ['confirmed', 'awaiting-payment'])
                // ->whereNull('checked_out')
                ->whereNull('deleted_at')
                ->get();

        }


        if ($push_main_reservation_to_collection) {
            $reservations->push($main_reservation);
        }
        foreach ($reservations as $reservationObject) {
            $balances[] = $reservationObject->wallet->decimal_places == 3 ? $reservationObject->balance / 1000 : $reservationObject->balance / 100;

            $reservationObject->wallet->refreshBalance();
        }
        foreach ($reservations as $obj) {
            GroupReservationBalanceMapper::updateOrCreate(
                ['reservation_id' => $obj->id],
                ['balance' => floatval(array_sum($balances) / count($reservations))]
            );
        }


        if ($reservation->group_reservation) {
            groupReservationHandler($reservation->group_reservation);
        }

        return response()->json(['success' => true, 'message' => 'Prices updated successfully']);

    }

    public function reservationsSources()
    {
        $sources = Source::where('team_id', auth()->user()->current_team_id)->get();
        return response()->json($sources);
    }

    public function sendPaymentSms(Request $request)
    {

        try {
            $sms_content = urldecode($request->get('sms_content'));
            $log = sendSms(auth()->user()->current_team_id, $sms_content, $request->get('phone'));
            if ($log['status']) {
                return response()->json(['success' => true]);
            } else {
                return response()->json(['success' => false, 'response' => $log['response']]);
            }

        } catch (Exception $ex) {
            return response()->json($ex->getMessage());
        }


    }

    public function sendPaymentEmail(Request $request)
    {

        try {

            $reservation_id = $request->get('reservation_id');
            $payment_link = urldecode($request->get('payment_link'));
            $email_recipient = $request->get('email');
            $reservation = Reservation::with('team')->find($reservation_id);

            $data = [
                'to' => $email_recipient,
                'reply_to' => null,
                'subject' => __('Reservation Payment Link'),
                'html' => view('email.customer.reservation_payment_link')
            ->with(['reservation'   =>  $reservation , 'payment_link' => $payment_link])->render(),
            ];
            $send = sendMailUsingMailMicroservice($data);

            // Mail::to($email_recipient)->send(new ReservationPaymentLinkEmail($reservation_id, $payment_link));

            return response()->json(['success' => true]);

        } catch (Exception $ex) {
            return response()->json(['success' => false, 'message' => $ex->getMessage()]);
        }


    }


    //only use for later push in case failure / retry trigger
    public function pushInvoiceToZatca($id, $invoice_type, $invoice_sub_type, $mark_credit_notes_as_sent)
    {
        $integration = Integration::findByKeyAndTeamId('ZatcaPhaseTwo', auth()->user()->current_team_id)->first();

        if (!$integration) {
            return response()->json([
                'message' => "Failed",
                'success' => false
            ], 500);
        }

        $org = auth()->user()->getSupplierEGS();

        if ($org == null) {
            return response()->json([
                'message' => __('Tax Number is required')
            ], 500);
        }
        $integration = Integration::findByKeyAndTeamId('ZatcaPhaseTwo', auth()->user()->current_team_id)->first();

        if(!$integration) {
            return null;
        }

        $invoice = ReservationInvoice::with('reservation', 'reservation.unit', 'reservation.customer', 'reservation.creator', 'reservation.comments')->findOrFail($id);

        $credential = (object) json_decode($integration->values);

        $zatcaInvoice = new Invoice($credential->username, $credential->password, $invoice_type, $invoice_sub_type, $org);

        $zatcaInvoice->seedInvoice($invoice);

        $compliant_invoice = $zatcaInvoice->getCompliantInvoice();

        //activity()->performedOn((new IntegrationSettings()))->log(__('Team Id :TEAM has reported Invoice#:INVOICE to zatca successfully', ['team' => $key, 'invoice' => '']));
        if (
            !isset($compliant_invoice->data->base64_signed_invoice_string) &&
            !isset($compliant_invoice->data->invoice_hash) &&
            !isset($compliant_invoice->data->uuid)
        ) {
            activity()->performedOn((new ReservationInvoice()))->log(__('Invoice :ID push to zatca has been failed', ['id' => $id]));
            return response()->json([
                'message' => $compliant_invoice ?? 'Failed' ,
                'success' => false

            ], 500);
        }

        $response = $zatcaInvoice->reportInvoice($compliant_invoice);

        if (!isset($response->status) || $response->status !== 200 || $response == null) {
            return response()->json($response, 500);
        }

        $response->data->invoice = $compliant_invoice->data->base64_signed_invoice_string;
        $response->data->invoice_number = $compliant_invoice->data->invoice_number;
        $response->data->qrcode = $compliant_invoice->data->qrcode;

        if (isset($response->data->reportingStatus) && $response->data->reportingStatus == "REPORTED") {
            $invoice->is_reported_to_zatca = $response->data;

        }

        if (isset($response->data->clearanceStatus) && $response->data->clearanceStatus == "CLEARED") {
            $invoice->is_reported_to_zatca = $response->data;
        }


        if ($mark_credit_notes_as_sent == "1") {
            $invoice_credit_note = InvoiceCreditNote::where("reservation_invoice_id", $id)->first();

            if ($invoice_credit_note) {
                $zatcaInvoice->setCanceledInvoiceBillingReferenceId($invoice->number);
                $zatcaInvoice->setPaymentInstruction("Returned");
                $zatcaInvoice->setInvoiceSubType("credit note");
                $compliant_invoice = $zatcaInvoice->getCompliantInvoice();

                if (
                    !isset($compliant_invoice->data->base64_signed_invoice_string) &&
                    !isset($compliant_invoice->data->invoice_hash) &&
                    !isset($compliant_invoice->data->uuid)
                ) {
                    activity()->performedOn((new ReservationInvoice()))->log(__('Invoice :ID push to zatca has been failed', ['id' => $id]));
                    return response()->json([
                        'message' => "Failed",
                        'success' => false
                    ], 500);
                }

                $response = $zatcaInvoice->reportInvoice($compliant_invoice);
                if (!isset($response->status) || $response->status !== 200 || $response == null) {
                    return response()->json($response, 500);
                }

                $response->data->invoice = $compliant_invoice->data->base64_signed_invoice_string;
                $response->data->invoice_number = $compliant_invoice->data->invoice_number;
                $response->data->qrcode = $compliant_invoice->data->qrcode;

                if (isset($response->data->reportingStatus) && $response->data->reportingStatus == "REPORTED") {
                    $invoice_credit_note->is_reported_to_zatca = $response->data;
                }

                if (isset($response->data->clearanceStatus) && $response->data->clearanceStatus == "CLEARED") {
                    $invoice_credit_note->is_reported_to_zatca = $response->data;
                }

                $invoice_credit_note->save();

                activity()->performedOn((new InvoiceCreditNote()))->log(__('Invoice :ID credit note has been pushed to zatca successfully', ['id' => $id]));


            }
        }

        $invoice->save();

        activity()->performedOn((new ReservationInvoice()))->log(__('Invoice :ID has been pushed to zatca successfully', ['id' => $id]));

        return response()->json($response->data, 200);
    }


    /**
     * @var ReservationInvoice
     */
    public function syncInvoiceOnCreate(ReservationInvoice $invoice, $invoice_type, $invoice_sub_type, $mark_credit_notes_as_sent)
    {
        $invoice->is_reported_to_zatca = null;
        //reset in order to update it with newer payload
        $integration = Integration::findByKeyAndTeamId('ZatcaPhaseTwo', auth()->user()->current_team_id)->first();

        if (!$integration) {
            return $invoice;
        }

        $org = auth()->user()->getSupplierEGS();

        if ($org == null) {
            return $invoice;
        }

        $credential = (object) json_decode($integration->values);

        $invoice->is_reported_to_zatca = null;

        $invoice_data = (object) $invoice->data;

        if (isset($invoice_data->company)) {
            $invoice_type = 'tax invoice';
        } else {
            $invoice_type = 'simplified tax invoice';
        }

        $zatcaInvoice = new Invoice($credential->username, $credential->password, $invoice_type, $invoice_sub_type, $org);

        $zatcaInvoice->seedInvoice($invoice);

        $compliant_invoice = $zatcaInvoice->getCompliantInvoice();

        //activity()->performedOn((new IntegrationSettings()))->log(__('Team Id :TEAM has reported Invoice#:INVOICE to zatca successfully', ['team' => $key, 'invoice' => '']));
        if (
            !isset($compliant_invoice->data->base64_signed_invoice_string) &&
            !isset($compliant_invoice->data->invoice_hash) &&
            !isset($compliant_invoice->data->uuid)
        ) {
            activity()->performedOn((new ReservationInvoice()))->log(__('Invoice :ID push to zatca has been failed', ['id' => $invoice->id]));
            return $invoice;
        }

        $response = $zatcaInvoice->reportInvoice($compliant_invoice);

        if (!isset($response->status) || $response->status !== 200 || $response == null) {
            return $invoice;
        }

        $response->data->invoice = $compliant_invoice->data->base64_signed_invoice_string;
        $response->data->invoice_number = $compliant_invoice->data->invoice_number;
        $response->data->qrcode = $compliant_invoice->data->qrcode;

        if (isset($response->data->reportingStatus) && $response->data->reportingStatus == "REPORTED") {
            $invoice->is_reported_to_zatca = $response->data;
        }

        if (isset($response->data->clearanceStatus) && $response->data->clearanceStatus == "CLEARED") {
            $invoice->is_reported_to_zatca = $response->data;
        }


        //since invoice has been credit we check credit notes and mark them as synced with zatca
        if ($mark_credit_notes_as_sent == "1") {
            $invoice_credit_note = InvoiceCreditNote::where("reservation_invoice_id", $invoice->id)->first();

            if ($invoice_credit_note) {
                $zatcaInvoice->setCanceledInvoiceBillingReferenceId($invoice->number);
                $zatcaInvoice->setPaymentInstruction("Returned");
                $zatcaInvoice->setInvoiceSubType("credit note");
                $compliant_invoice = $zatcaInvoice->getCompliantInvoice();

                if (
                    !isset($compliant_invoice->data->base64_signed_invoice_string) &&
                    !isset($compliant_invoice->data->invoice_hash) &&
                    !isset($compliant_invoice->data->uuid)
                ) {
                    activity()->performedOn((new ReservationInvoice()))->log(__('Invoice :ID push to zatca has been failed', ['id' => $invoice->id]));
                    return $invoice;
                }

                $response = $zatcaInvoice->reportInvoice($compliant_invoice);

                if (!isset($response->status) || $response->status !== 200 || $response == null) {
                    return response()->json($response, 500);
                }

                $response->data->invoice = $compliant_invoice->data->base64_signed_invoice_string;
                $response->data->invoice_number = $compliant_invoice->data->invoice_number;
                $response->data->qrcode = $compliant_invoice->data->qrcode;

                if (isset($response->data->reportingStatus) && $response->data->reportingStatus == "REPORTED") {
                    $invoice_credit_note->is_reported_to_zatca = $response->data;
                }

                if (isset($response->data->clearanceStatus) && $response->data->clearanceStatus == "CLEARED") {
                    $invoice_credit_note->is_reported_to_zatca = $response->data;
                }

                $invoice_credit_note->save();

                activity()->performedOn((new InvoiceCreditNote()))->log(__('Invoice :ID credit note has been pushed to zatca successfully', ['id' => $invoice->id]));

            }
        }

        //emit activity
        activity()->performedOn((new ReservationInvoice()))->log(__('Invoice :ID has been pushed to zatca successfully', ['id' => $invoice->id]));

        return $invoice;
    }

    public function getQualifiedForCheckinReservations(Request $request)
    {


        /**
         * @note : First layer of check
         * I just got a clean version of reservations where reservation has customer
         * and this customer has an id number
         */
        $time = $request->get('time');
        $main_reservation_id = $request->get('main_id');
        $process_type = $request->has('type') ? $request->get('type') : 'checkin';
        $main_reservation = Reservation::where('id', $main_reservation_id)
            ->where('reservation_type', 'group')
            ->whereHas('customer', function ($c) {
                $c->whereNotNull('id_number');
            })
            ->whereHas('unit', function ($u) {
                $u->whereStatus(1);
            })
            ->where('status', '!=', 'canceled')
            // ->whereIn('status' , ['confirmed','awaiting-payment'])
            ->when($process_type == 'checkin', function ($query) {
                $query->whereNull('checked_in');
            })
            ->when($process_type == 'checkout', function ($query) {
                $query->whereNotNull('checked_in')
                    ->whereNull('checked_out');
            })
            // ->whereNull('checked_in')
            ->whereNull('deleted_at')
            ->first();

        $reservations = Reservation::where('reservation_type', 'group')
            ->whereHas('customer', function ($c) {
                $c->whereNotNull('id_number');
            })
            ->whereHas('unit', function ($u) {
                $u->whereStatus(1);
            })
            ->where('attachable_id', $main_reservation_id)
            ->where('status', '!=', 'canceled')
            // ->whereIn('status' , ['confirmed','awaiting-payment'])
            ->when($process_type == 'checkin', function ($query) {
                $query->whereNull('checked_in');
            })
            ->when($process_type == 'checkout', function ($query) {
                $query->whereNotNull('checked_in')
                    ->whereNull('checked_out');
            })
            // ->whereNull('checked_in')
            ->whereNull('deleted_at')
            ->get();

        if (($main_reservation)) {
            $reservations->push($main_reservation);
        }
        $reservations = collect($reservations)->sortBy('id');
        /**
         * @note : Second layer of check
         * Avoid any intersection with any previous checked in reservation on the same unit
         */
        $noCheckedInReservationsForTheSameUnitArray = [];
        if (count($reservations)) {
            foreach ($reservations as $reservation) {
                $prReservation = Reservation::where('team_id', $reservation->team_id)
                    ->where('id', '!=', $reservation->id)
                    ->whereNull('checked_out')
                    ->whereNotNull('checked_in')
                    ->whereNull('deleted_at')
                    ->where('status', '!=', 'canceled')
                    ->where('unit_id', $reservation->unit_id)
                    ->orderBy('date_out', 'desc')
                    ->first();

                if (!$prReservation) {
                    if ($this->applicableTimeForCheckin($reservation, $time) && $process_type == 'checkin') {
                        array_push($noCheckedInReservationsForTheSameUnitArray, $reservation);
                    }

                    if ($process_type == 'checkout') {
                        array_push($noCheckedInReservationsForTheSameUnitArray, $reservation);
                    }
                }

            }
        }

        return response()->json($noCheckedInReservationsForTheSameUnitArray);
    }

    function applicableTimeForCheckin($reservation, $time)
    {
        // incoming request

        $dateIn = $reservation->date_in;
        $dayStart = Settings::get('day_start');

        $dayEnd = Settings::get('day_end');
        $dateOut = $reservation->date_out;

        if (!$time) {
            return false;
        }

        $startDate = \Carbon\Carbon::createFromFormat('Y-m-d H:i', "$dateIn $dayStart");
        $endDate = \Carbon\Carbon::createFromFormat('Y-m-d H:i', "$dateOut $dayEnd");
        $check = \Carbon\Carbon::now()->between($startDate, $endDate);

        if (!$check) {
            return false;
        }

        return true;

    }

    public function doCheckinGroupReservations(Request $request)
    {
        $time = $request->get('time');
        $checked_reservations = $request->get('checked_reservations');
        $reservations = Reservation::whereIn('id', $checked_reservations)->get();
        if (count($reservations)) {
            foreach ($reservations as $reservation) {
                $reservation->checked_in = new \DateTime($time);
                $reservation->action_type = Reservation::ACTION_CHECKEDIN;
                $reservation->save();
                event(new ReservationCheckIn($reservation));
            }

            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false]);
    }

    public function doCheckoutGroupReservations(Request $request)
    {
        $time = $request->get('time');
        $main_reservation_id = $request->get('main_id');
        $checked_reservations_to_checkout = $request->get('checked_reservations');
        $checked_units_to_clean = $request->get('checked_units');
        $automatic_under_cleaning = $request->get('automatic_under_cleaning');
        $reservations = Reservation::with('wallet', 'unit', 'company', 'customer')
            ->where('reservation_type', 'group')
            ->where(function ($query) use ($main_reservation_id) {
                return $query->with('unit')->where('id', $main_reservation_id)->orWhere('attachable_id', $main_reservation_id);
            })
            ->whereIn('status', ['confirmed', 'awaiting-payment'])
            ->whereNull('deleted_at')
            ->get();


        $checkedOutReservationsCount = $reservations->filter(function ($item) {
            return $item->checked_out;
        })->values()->count();

        if (($reservations->count() - $checkedOutReservationsCount) == count($checked_reservations_to_checkout)) {

            // i shoud here check the count of the incoming checked reservations if they are greater than one
            // i should quickly pop one reservation out then check out all remaining reservations
            // then use the poped out reservation to complete the logic
            // it is fucking feature and it blows my mind -_-
            // remember i need to re-fetch the reservations again
            if(count($checked_reservations_to_checkout) > 1){

                /**
                 * i used array shif helper to pop a reservation id and re-construct the simple checkout ids
                 */
                $reservation_id_to_make_final_checkout = array_shift($checked_reservations_to_checkout);
                $final_reservation = Reservation::with('unit', 'wallet', 'company', 'customer')->where('id', $reservation_id_to_make_final_checkout)->first();
                $reservations_ids_to_checkout_simply = $checked_reservations_to_checkout; // not this after shifted

                // just for saftey however am 100% sure there will be at least one id in the array
                if(count($reservations_ids_to_checkout_simply)){

                     // do simple checkout ...
                    // get a list of the checked to checkout reservations that are coming as an array of ids in the request
                    $fetch_checked_reservations = Reservation::whereIn('id', $reservations_ids_to_checkout_simply)->get();
                    foreach ($fetch_checked_reservations as $reservation) {
                        # do simple checkout to reservations as there is remaining reservation pending

                        $automatic_under_cleaning = Setting::where('team_id', $reservation->team_id)->where('key', 'automatic_under_cleaning')->first();
                        if ($automatic_under_cleaning !== null) {
                            if ($automatic_under_cleaning->value == 1) {

                                if ($final_reservation->unit_id != $reservation->unit_id) {

                                    // change unit status to under cleaning
                                    Unit::find($reservation->unit_id)->update([
                                        'status' => 2
                                    ]);

                                    // create cleaning record in unit cleanings table

                                    try {
                                        UnitCleaning::create([
                                            'unit_id' => $reservation->unit_id,
                                            'start_at' => new \DateTime(),
                                            'team_id' => $reservation->team_id
                                        ]);
                                    } catch (\Throwable $th) {
                                        UnitCleaning::create([
                                            'unit_id' => $reservation->unit_id,
                                            'start_at' => new \DateTime(),
                                            'team_id' => $reservation->team_id,
                                            'note' => $th->getMessage()
                                        ]);
                                    }

                                }

                            }
                        }

                        if(count($checked_units_to_clean)){
                            if(in_array($reservation->unit_id,$checked_units_to_clean)){
                                $unit  = Unit::find($reservation->unit_id);

                                # update unit status to under cleaning based on checked units
                                $unit->status = 2;
                                $unit->save();

                                // create cleaning record in unit cleanings table
                                  // create cleaning record in unit cleanings table

                                try {
                                    UnitCleaning::create([
                                        'unit_id' => $reservation->unit_id,
                                        'start_at' => new \DateTime(),
                                        'team_id' => $reservation->team_id
                                    ]);
                                } catch (\Throwable $th) {
                                    UnitCleaning::create([
                                        'unit_id' => $reservation->unit_id,
                                        'start_at' => new \DateTime(),
                                        'team_id' => $reservation->team_id,
                                        'note' => $th->getMessage()
                                    ]);
                                }


                            }

                        }

                        // simply update checked_out value
                        $reservation->checked_out = new \DateTime($time);
                        $reservation->action_type = Reservation::ACTION_CHECKEDOUT;
                        $reservation->save();

                        // then fire an event of checkout
                        event(new ReservationCheckout($reservation));
                    }

                }

                // after all the above steps ...
                // am now ready to make the final check for the remaining reservation
                // but i need to call reservations again
                $reservations = Reservation::with('wallet','unit','company','customer')
                ->where('reservation_type' , 'group')
                ->where(function ($query) use($reservation_id_to_make_final_checkout) {
                    return $query->with('unit')->where('id',$reservation_id_to_make_final_checkout)->orWhere('attachable_id',$reservation_id_to_make_final_checkout);
                })
                ->whereIn('status' , ['confirmed','awaiting-payment'])
                ->whereNull('deleted_at')
                ->get();


                $reservations_deposit_insurance_transactions = [];
                $reservations_withdraw_insurance_transactions = [];
                $balances = [];
                $shared_invoices = [];
                $all_grouped_reservations_ids = [];
                $transactions = [];
                $services = [];
                $promissories = [];

                foreach($reservations as $reservationObject){
                    $balances [] = $reservationObject->wallet->decimal_places == 3 ? $reservationObject->balance / 1000 : $reservationObject->balance / 100;
                    $all_grouped_reservations_ids [] = $reservationObject->id;

                    if($reservationObject->depositInsuranceTransactions()->count()){
                        $reservations_deposit_insurance_transactions [] = $reservationObject->depositInsuranceTransactions;
                    }
                    if ($reservationObject->withdrawInsuranceTransactions()->count()) {
                        $reservations_withdraw_insurance_transactions[] = $reservationObject->withdrawInsuranceTransactions;
                    }

                    if($reservationObject->invoices()->count()){
                        foreach($reservationObject->invoices as $invoice){
                            $shared_invoices [] = $invoice;
                        }
                    }


                    if($reservationObject->services()->count()){
                        foreach($reservationObject->services as $transaction){
                            $services [] = $transaction;
                        }
                    }

                }

                foreach ($reservations as $obj) {
                    GroupReservationBalanceMapper::updateOrCreate(
                        ['reservation_id' => $obj->id],
                        ['balance' => floatval(array_sum($balances) / count($reservations))]
                    );
                }

                $final_reservation['group_reservation_services'] = collect($services)->sortByDesc('service_log_number')->values();
                $final_reservation['group_balance'] = array_sum($balances);
                $final_reservation['shared_invoices'] = collect($shared_invoices)->sortByDesc('number')->values();
                $final_reservation['all_grouped_reservations_ids'] = $all_grouped_reservations_ids;
                $final_reservation['dates_calculations'] = startAndEndDateCalculatorWithNights($reservations);
                $final_reservation['main_reservation_id'] = $main_reservation_id;


                return response()->json([
                    'success' => true,
                    'simple_checkout' => false,
                    'is_last' => true,
                    'last_reservation' => $final_reservation,
                    'shared_invoices' => $shared_invoices,
                    'message' => 'we shifted a reservation and checked out others and this is the last reservation to make a full checkout',
                    'group_balance' => array_sum($balances),
                    'withdraw_insurance_transactions' => count($reservations_withdraw_insurance_transactions),
                    'deposit_insurance_transactions' => count($reservations_deposit_insurance_transactions),
                    'deposit_insurance_transaction' => count($reservations_deposit_insurance_transactions) ? $reservations_deposit_insurance_transactions[0][0] : null,
                    'hide_checkout_group_btn' => true,
                    'show_insurance_retrieval_modal' => true
                ]);

            }else{

                $the_remaining_not_checked_out_reservation = $reservations->filter(function ($item) {
                    return !$item->checked_out;
                })->first();

                // it means there is only one reservation
                // so we shall make the normal checkout as we used

                $reservations_deposit_insurance_transactions = [];
                $reservations_withdraw_insurance_transactions = [];
                $balances = [];
                $shared_invoices = [];
                $all_grouped_reservations_ids = [];
                $transactions = [];
                $services = [];
                $promissories = [];

                foreach($reservations as $reservationObject){
                    $balances [] = $reservationObject->wallet->decimal_places == 3 ? $reservationObject->balance / 1000 : $reservationObject->balance / 100;
                    $all_grouped_reservations_ids [] = $reservationObject->id;

                    if($reservationObject->depositInsuranceTransactions()->count()){
                        $reservations_deposit_insurance_transactions [] = $reservationObject->depositInsuranceTransactions;
                    }
                    if ($reservationObject->withdrawInsuranceTransactions()->count()) {
                        $reservations_withdraw_insurance_transactions[] = $reservationObject->withdrawInsuranceTransactions;
                    }

                    if($reservationObject->invoices()->count()){
                        foreach($reservationObject->invoices as $invoice){
                            $shared_invoices [] = $invoice;
                        }
                    }

                    if($reservationObject->services()->count()){
                        foreach($reservationObject->services as $transaction){
                            $services [] = $transaction;
                        }
                    }

                }

                foreach ($reservations as $obj) {
                    GroupReservationBalanceMapper::updateOrCreate(
                        ['reservation_id' => $obj->id],
                        ['balance' => floatval(array_sum($balances) / count($reservations))]
                    );
                }

                $the_remaining_not_checked_out_reservation['group_reservation_services'] = collect($services)->sortByDesc('service_log_number')->values();
                $the_remaining_not_checked_out_reservation['group_balance'] = array_sum($balances);
                $the_remaining_not_checked_out_reservation['shared_invoices'] = collect($shared_invoices)->sortByDesc('number')->values();
                $the_remaining_not_checked_out_reservation['all_grouped_reservations_ids'] = $all_grouped_reservations_ids;
                $the_remaining_not_checked_out_reservation['dates_calculations'] = startAndEndDateCalculatorWithNights($reservations);
                $the_remaining_not_checked_out_reservation['main_reservation_id'] = $main_reservation_id;


                return response()->json([
                    'success' => true,
                    'simple_checkout' => false,
                    'is_last' => true,
                    'last_reservation' => $the_remaining_not_checked_out_reservation,
                    'shared_invoices' => $shared_invoices,
                    'message' => 'this is the last reservation to make a full checkout',
                    'group_balance' => array_sum($balances),
                    'withdraw_insurance_transactions' => count($reservations_withdraw_insurance_transactions),
                    'deposit_insurance_transactions' => count($reservations_deposit_insurance_transactions),
                    'deposit_insurance_transaction' => count($reservations_deposit_insurance_transactions) ? $reservations_deposit_insurance_transactions[0][0] : null,
                    'show_insurance_retrieval_modal' => true
                ]);
            }

        }else{
             // do simple checkout ...
             // get a list of the checked to checkout reservations that are coming as an array of ids in the request
            $fetch_checked_reservations = Reservation::whereIn('id',$checked_reservations_to_checkout)->get();
            foreach ($fetch_checked_reservations as $reservation) {
                # do simple checkout to reservations as there is remaining reservation pending

                $automatic_under_cleaning = Setting::where('team_id',$reservation->team_id)->where('key','automatic_under_cleaning')->first();
                if($automatic_under_cleaning !== null) {
                    if($automatic_under_cleaning->value == 1){
                        // change unit status to under cleaning
                        Unit::find($reservation->unit_id)->update([
                            'status' => 2
                        ]);

                        // create cleaning record in unit cleanings table

                        try {
                            UnitCleaning::create([
                                'unit_id' => $reservation->unit_id,
                                'start_at' => new \DateTime(),
                                'team_id' => $reservation->team_id
                            ]);
                        } catch (\Throwable $th) {
                            UnitCleaning::create([
                                'unit_id' => $reservation->unit_id,
                                'start_at' => new \DateTime(),
                                'team_id' => $reservation->team_id,
                                'note' => $th->getMessage()
                            ]);
                        }


                    }
                }
                if (count($checked_units_to_clean)) {
                    if (in_array($reservation->unit_id, $checked_units_to_clean)) {
                        $unit = Unit::find($reservation->unit_id);

                        # update unit status to under cleaning based on checked units
                        $unit->status = 2;
                        $unit->save();

                        // create cleaning record in unit cleanings table
                          // create cleaning record in unit cleanings table

                        try {
                            UnitCleaning::create([
                                'unit_id' => $reservation->unit_id,
                                'start_at' => new \DateTime(),
                                'team_id' => $reservation->team_id
                            ]);
                        } catch (\Throwable $th) {
                            UnitCleaning::create([
                                'unit_id' => $reservation->unit_id,
                                'start_at' => new \DateTime(),
                                'team_id' => $reservation->team_id,
                                'note' => $th->getMessage()
                            ]);
                        }


                    }

                }

                // simply update checked_out value
                $reservation->checked_out = new \DateTime($time);
                $reservation->action_type = Reservation::ACTION_CHECKEDOUT;
                $reservation->save();

                // then fire an event of checkout
                event(new ReservationCheckout($reservation));
            }

            return response()->json([
                'success' => true ,
                'simple_checkout' => true ,
                'message' => 'there is pending reservation needs to fulfill',
                'show_insurance_retrieval_modal' => false
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Something went wrong, please contact support']);

    }

    public function getZatcaReceipt(Request $request)
    {
        try {

            $team = auth()->user()->teams()->where('id', auth()->user()->current_team_id)->first();
            $logo = auth()->user()->getTeamLogo();
            $meta = array(
                'credit_note_number' => $request->meta['credit_note_number'] ?? null,
                'debit_note_number' => $request->meta['debit_note_number'] ?? null,
                'invoice_reference_number' => $request->meta['invoice_reference_number'] ?? null,
                'invoice_type' => $request->meta['invoice_type'] ?? null,
                'seller' => array(
                    'name' => $team->en_name ?? null,
                    'nameAR' => $team->name ?? null,
                    'city' => $team->address()['city'] ?? null,
                    'cityAR' => $team->address()['city_ar'] ?? null,
                    'district' =>  $team->address()['district'] ?? null,
                    'districtAR' =>  $team->address()['district_ar'] ?? null,
                    'street' =>  $team->address()['street'] ?? null,
                    'streetAR' =>  $team->address()['street_ar'] ?? null,
                )
            );
            if(isset($logo) && Storage::disk('s3')->exists($logo)) {
                $meta['logo_url'] = Storage::url(
                    $logo
                );
            }
            $response = GenerateOrReportInvoice::getInvoiceInPDFA3($request->invoiceXML, $meta);
            if(!$response) {
                throw new Exception('Failed to download pdf');
            }

            if(isset($response->resource_url)) {
               $pdf = GenerateOrReportInvoice::downloadPDF($response->resource_url);
            }

            $base_path = public_path() . "/runtimes/";

            if(isset($response->pdf_file_name)) {
                $file_name = $response->pdf_file_name;
            } else {
                $file_name = Str::uuid() . '.pdf';
            }

            $alternative_path = $base_path . $file_name;

            $stream = fopen($alternative_path, 'w');

            // $pdf = base64_decode($response->pdf);

            fwrite($stream, $pdf);

            fclose($stream);

            $headers = array(
                'Content-Type: application/pdf',
                'File-Name: ' . $file_name,
            );
            //nice trap ... :)
            return response()->download($alternative_path)->deleteFileAfterSend(true);

        } catch (Exception $err) {
            return $err;
        }
    }

    public function getQualifiedForCancelReservations(Request $request){
        $main_reservation_id = $request->get('main_id');
        $main_reservation = Reservation::where('id',$main_reservation_id)
        ->where('reservation_type' , 'group')
        // ->whereHas('customer' , function($c) {
        //     $c->whereNotNull('id_number');
        // })
        ->whereHas('unit' , function($u) {
            // $u->whereStatus(1);
        })
        ->where('status', '!=', 'canceled')
        ->whereNull('deleted_at')
        ->with('invoices')
        ->first();

        $reservations = Reservation::where('reservation_type' , 'group')
            // ->whereHas('customer' , function($c) {
            //     $c->whereNotNull('id_number');
            // })
            ->whereHas('unit' , function($u) {
                // $u->whereStatus(1);
            })
            ->where('attachable_id',$main_reservation_id)
            ->where('status', '!=', 'canceled')
            ->whereNull('deleted_at')
            ->with('invoices')
            ->get();

            if( ($main_reservation)){
                $reservations->push($main_reservation);
            }
            $reservations =  collect($reservations)->sortBy('id')->values();
            $reservations_ids_in_sphere = collect($reservations)->pluck('id')->toArray();
            $reservation_ids_in_invoices = [];
            if(count($reservations)){
                foreach ($reservations as $reservation) {
                    if($reservation->pure_invoices_without_credit_notes()->count()){
                        foreach($reservation->pure_invoices_without_credit_notes as $invoice){
                            foreach ($invoice->data['periods'] as $key => $period) {
                                    $reservation_ids_in_invoices [] = $key;
                            }
                        }
                    }
                }
            }
            $reservations_that_are_applicable_to_cancel = Reservation::whereIn('id',array_diff($reservations_ids_in_sphere,array_unique($reservation_ids_in_invoices)))->get();
            return response()->json($reservations_that_are_applicable_to_cancel);
    }

    public function cancelGroupReservations(Request $request){




        $ids = $request->get('checked_reservations');
        $main_reservation_id = $request->get('main_reservation_id');
        $main_reservation = Reservation::find($main_reservation_id);
        if($request->get('cancellation_fees') > 0){
            $serviceObj = new \stdClass();
            $serviceObj->id = env('CANCELLATION_ID'); // Special identifier for cancellation fee
            $serviceObj->category = 'service';
            $serviceObj->statement = 'Cancellation Fee';
            $serviceObj->qty = 1;
            $serviceObj->vat = 0; // You can adjust VAT if needed
            $serviceObj->ttx = 0; // You can adjust TTX if needed
            $serviceObj->vatIsChecked = false;
            $serviceObj->ttxIsChecked = false;
            $serviceObj->price = $request->get('cancellation_fees');
            $serviceObj->sub_total = $request->get('cancellation_fees');
            $serviceObj->totalGeneralSum = $request->get('cancellation_fees');

            $meta = [
                'category' => 'service',
                'date' => Carbon::now()->format('Y-m-d H:i'),
                'statement' => 'Cancellation Fee',
                'services' => [$serviceObj],
                'total_with_taxes' => $request->get('cancellation_fees'),
                'sub_total' => $request->get('cancellation_fees'),
                'vat_total' => 0,
                'ttx_total' => 0,
                'qty' => 1
            ];

            $transaction = $main_reservation->forceWithdrawFloat($request->get('cancellation_fees'), $meta, true, false);

            // Create service log entry
            $counter = $main_reservation->team->counter;
            if (!$counter) {
                $counter = TeamCounter::create();
            }

            $last_service_number = $counter->last_service_number + 1;
            $counter->last_service_number = $last_service_number;
            $counter->save();

            $serviceLog = new ServiceLog;
            $serviceLog->team_id = $main_reservation->team_id;
            $serviceLog->user_id = auth()->user()->id;
            $serviceLog->transaction_id = $transaction->id;
            $serviceLog->type = $transaction->type;
            $serviceLog->number = $last_service_number;
            $serviceLog->amount = $transaction->amount;
            $serviceLog->decimals = $transaction->wallet->decimal_places;
            $serviceLog->meta = $transaction->meta;
            $serviceLog->save();
        }

        // Handle no-show fees as a service
        if($request->get('no_show_fees') > 0) {
            $main_reservation->no_show = 1;
            $main_reservation->save();
            $serviceObj = new \stdClass();
            $serviceObj->id = env('NO_SHOW_ID'); // Special identifier for no-show fee
            $serviceObj->category = 'service';
            $serviceObj->statement = 'No Show Fee';
            $serviceObj->qty = 1;
            $serviceObj->vat = 0; // You can adjust VAT if needed
            $serviceObj->ttx = 0; // You can adjust TTX if needed
            $serviceObj->vatIsChecked = false;
            $serviceObj->ttxIsChecked = false;
            $serviceObj->price = $request->get('no_show_fees');
            $serviceObj->sub_total = $request->get('no_show_fees');
            $serviceObj->totalGeneralSum = $request->get('no_show_fees');

            $meta = [
                'category' => 'service',
                'date' => Carbon::now()->format('Y-m-d H:i'),
                'statement' => 'No Show Fee',
                'services' => [$serviceObj],
                'total_with_taxes' => $request->get('no_show_fees'),
                'sub_total' => $request->get('no_show_fees'),
                'vat_total' => 0,
                'ttx_total' => 0,
                'qty' => 1
            ];

            $transaction = $main_reservation->forceWithdrawFloat($request->get('no_show_fees'), $meta, true, false);

            // Create service log entry
            $counter = $main_reservation->team->counter;
            if (!$counter) {
                $counter = TeamCounter::create();
            }

            $last_service_number = $counter->last_service_number + 1;
            $counter->last_service_number = $last_service_number;
            $counter->save();

            $serviceLog = new ServiceLog;
            $serviceLog->team_id = $main_reservation->team_id;
            $serviceLog->user_id = auth()->user()->id;
            $serviceLog->transaction_id = $transaction->id;
            $serviceLog->type = $transaction->type;
            $serviceLog->number = $last_service_number;
            $serviceLog->amount = $transaction->amount;
            $serviceLog->decimals = $transaction->wallet->decimal_places;
            $serviceLog->meta = $transaction->meta;
            $serviceLog->save();
        }

        $main_reservation->wallet->refreshBalance();
        if(count($ids)){

            $reservations = Reservation::whereIn('id',$ids)->get();
            if(count($reservations)){
                foreach ($reservations as $reservation) {
                    $reservation->depositFloat($reservation->total_price, [
                        'category' => 'update_reservation',
                        'statement' => 'update Reservation Total Price deposit',
                    ], true, false);

                    $reservation->wallet->refreshBalance();

                    $this->moveTransactions($reservation);
                    $reservation->canceled_reason  = $request->get('cancel_reason') ?  $request->get('cancel_reason') : null;
                    $reservation->occ = 0;
                    $reservation->action_type = Reservation::ACTION_CANCELED;
                    $reservation->save();
                    $reservation->cancel();

                }
            }




        }


        $attachable_reservations_count = Reservation::where('reservation_type' , 'group')
        ->whereHas('unit' , function($u) {
            $u->whereStatus(1);
        })
        ->where('attachable_id',$main_reservation_id)
        ->where('status','!=','canceled')
        ->whereNull('deleted_at')
        ->get()
        ->count();
        if(!$attachable_reservations_count){
            // this means there is only the main reservation remaining
            // initiate canceling it
            // i need to check if main reservation can be canceled
            $callRequest = new \Illuminate\Http\Request();
            $callRequest->replace([
                'current_id' => $main_reservation_id,
                'main_id' => $main_reservation_id,
            ]);

            if($this->checkReservationCanBeCanceled($callRequest)->getData()->success){
                // it means now that main reservation can be canceled safely
                $main_reservation = Reservation::find($main_reservation_id);
                $main_reservation->canceled_reason  = $request->get('cancel_reason') ?  $request->get('cancel_reason') : null;
                $main_reservation->occ = 0;
                $main_reservation->action_type = Reservation::ACTION_CANCELED;
                $main_reservation->save();
                $main_reservation->cancel();

                $main_reservation->depositFloat($main_reservation->total_price, [
                    'category' => 'update_reservation',
                    'statement' => 'update Reservation Total Price deposit',
                ], true, false);

                $main_reservation->wallet->refreshBalance();

                //mytravel update inventory on group and single cancel
                $team = Team::find($main_reservation->team_id);

                if($team->mytravel_hotel_id != null){
                    $merged_reservations = $request->checked_reservations;
                    $merged_reservations[] = $request->main_reservation_id;
                    $reservations = Reservation::whereIn('id', $merged_reservations)->get();


                    foreach($reservations as $reservation){
                        $mytravel = $this->UpdateMyTravelInventoryOnGroupcancel($reservation);

                        $this->$mytravel = $this->updateMyTravelInventoryOnCancelSingle($reservation);
                    }
                }
                //end mytravel update inventory on group and single cancel

                return response()->json([
                    'success' => true,
                    'all_attachable_canceled' => true,
                    'is_main_reservation' => true
                ]);

            }
        }


        return response()->json([
            'success' => true,
            'all_attachable_canceled' => true,
            'is_main_reservation' => false
        ]);



    }
    //mytravel cancel group reservation
    public function UpdateMyTravelInventoryOnGroupcancel($request)
    {


        $team_id = $request->team_id;
        $unit_id = $request->unit_id;
        $unit_category_id = Unit::where('id', $unit_id)->first()->unit_category_id;
        $date_in = $request->date_in;
        $date_out = $request->date_out;
        // convert it to carbon
        $date_in = Carbon::parse($date_in);
        $date_out = Carbon::parse($date_out);
        // get the unit category where id = unit_category_id
        $unit_category = UnitCategory::find($unit_category_id);

        $roomsData = [];
        for ($date = $date_in->copy(); $date->lte($date_out); $date->addDay()) {
            $dayData = [
                'from' => $date->format('Y-m-d'),
                'to' => $date->copy()->addDay()->format('Y-m-d'), // End date is the next day
                // You can add more fields here as needed
            ];
            $roomsData[] = $dayData;
        }
        array_pop($roomsData);
        foreach ($roomsData as $date) {

            $from = $date['from'];
            $to = $date['to'];
            $dayName = Carbon::parse($from)->format('l');
            $day_Name_price = strtolower($dayName) . '_day_price';
            $price = $unit_category[$day_Name_price];
            $specialPrice = SpecialPrice::where('unit_category_id', $unit_category_id)->where('start_date', '<=', $date['from'])->where('end_date', '>=', $date['from'])->where('enabled', true)->first();
            if($specialPrice){
                $price = $specialPrice->days_prices[$dayName];
            }else{

                $price = $unit_category[$day_Name_price];
            }
            // get the number of units that is no reservation for this from to date and the unit category id is the same as the request and available to sync is true
            $all_units = Unit::where('unit_category_id', $unit_category_id)
            ->where('status', '!=', 3)
            ->where('deleted_at', null)->pluck('id')->toArray();
            $available_arr = [];
            foreach($all_units as $unit_id){
                $hasIntersectionWorkable = checkIfUnitHasReservation($unit_id, Carbon::parse($from));
                if(!$hasIntersectionWorkable){
                    array_push($available_arr, $unit_id);
                }
        }
        $available_units = count($available_arr);
            $ewa_percantage = getEwaPercentageForUnit($team_id);
            $tax_percentage = getVatPercentageForUnit($team_id);
            $ewa_total = getEwaTotalForUnit($price, $ewa_percantage);
            $tax_total = getVatTotalForUnit($price, $ewa_total, $tax_percentage);
            $total_price = $price + $ewa_total + $tax_total;
            $data = [
                'fandaqah_ref_id' => $request->id,
                'category_id' => $unit_category_id,
                'start_date' => $from,
                'end_date' => $from,
                'price' => $total_price,
                'active' => 1,
                'note_to_customer' => '',
                'note_to_admin' => '',
                'is_instant' => 1,
                'number' => $available_units,
                'create_user' => $team_id,
                'update_user' => $team_id,
                'status' => "cancel_reservation",
                'original_date_in' => $date_in->format('Y-m-d'),
                'original_date_out' => $date_out->format('Y-m-d'),
            ];

            $curl = curl_init();
            $url = env('MYTRAVEL_API_URL') . '/api/hotel/room/Inventory-delete';
            $key = env('MY_TRAVEL_KEY');
            curl_setopt_array($curl, array(
                CURLOPT_URL =>  $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 400,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'key: ' . $key
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            // return $response;
        }
    }
    //end mytravel cancel group reservation

    public function checkReservationCanBeCanceled(Request $request){
        $current_reservation_id = $request->get('current_id');
        $reservation = Reservation::with('pure_invoices_without_credit_notes')->find($request->main_id);
        if($reservation->pure_invoices_without_credit_notes()->count()){

            if($reservation->reservation_type == 'single'){
                return response()->json([
                    'success' => true,
                    'can_be_canceled' => false,
                    'message' => 'Reservation is already included in an active invoice'
                ]);
            }
            foreach($reservation->pure_invoices_without_credit_notes as $invoice){
                foreach ($invoice->data['periods'] as $key => $period) {
                        $reservation_ids_in_invoices [] = $key;
                }
            }
            $unique_reservation_ids_in_invoices = array_unique($reservation_ids_in_invoices);
            if(in_array($current_reservation_id,$unique_reservation_ids_in_invoices)){
                return response()->json([
                    'success' => true,
                    'can_be_canceled' => false,
                    'message' => 'Reservation is already included in an active invoice'
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'can_be_canceled' => true,
            'message' => 'Reservation can be safely canceled'
        ]);
    }

    public function reservationHasCustomer(Request $request){
        $reservation = Reservation::find($request->id);
        return response()->json(['customer_id' => $reservation->customer_id]);
    }
    public function removeCompanyFromReservation(Request $request,Reservation $reservation){
        $reservation->company_id = null;
        $reservation->reservation_type = 'single';
        if($reservation->save()){
            return response()->json([
                'success' => true,
                'message' => 'company was removed'
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'something went wrong'
        ]);
    }

    public function changeCompanyOnReservation(Request $request,Reservation $reservation , $company_id){
        $reservation->company_id = $company_id;
        if($reservation->save()){

            // get all attached reservations and change company as well
            $reservations = Reservation::where('reservation_type' , 'group')
            ->where('attachable_id',$reservation->id)
            ->whereNull('deleted_at')
            ->get();
            if(count($reservations)){
                foreach($reservations as $attachable_reservation){
                    $attachable_reservation->company_id = $company_id;
                    $attachable_reservation->save();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'company changed successfully'
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'something went wrong'
        ]);
    }

    public function unitCategoryFilterValues()
    {
        $unit_categories = UnitCategory::where('team_id', '=', auth()->user()->current_team_id)
            ->get();
        $arr = [];
        foreach ($unit_categories as $category) {
            $arr[] = [
                'name' =>  $category->name,
                'value' => $category->id
            ];
        }
        return $arr;
    }

    public function getServerDate(Request $request){
        return response()->json(Carbon::now()->format('Y-m-d H:i:s'));
    }
    /*
    * This intended to perform price change for each day in a reservation
    * Briefed at 2025 / 01 / 07
    * Created date 2025 / 01 / 08
    */
    public function updateReservationPrices (UpdateReservationPrices $request) {
        $isPriceByDayEnabled = SettingStore::getUserSetting('check_calculate_price_by_day_enable');
        $days               = $request->days;
        $reservation_id     = $request->reservationId;
        $days_has_invoices  = new Basket;
        $days_to_mutate     = new Basket;
        $reservation        = Reservation::find($reservation_id);
        $user               = auth()->user();

        //check permission
        if(!$isPriceByDayEnabled) {
            return ResponseHelper::unauthorized(__('Not Enabled'));
        }

        if(isset($days) && count($days) == 0) {
            return ResponseHelper::unauthorized(__('Not Enabled'));
        }

        //check if it has invoice
        foreach ($days as $key => $day) {
            $day = (object) $day;

            if(!$request->get('checkDatesOnly')) {
                if(!isset($day->mutatedValue)
                || round($day->mutatedValue, 2)
                    == round($day->price, 2)) {
                continue;
                }
            }

            $date = $day->date;

            $existedInvoice = ReservationInvoice::where('reservation_id', $reservation_id)
                                        ->where(function ($query) use ($date) {
                                        $query->where('from', '<=', $date)
                                                ->where('to', '>=', $date);
                                        })
                                        ->orderBy('created_at', 'desc')
                                        ->first();

            if($existedInvoice) {
                $existedCreditNote = InvoiceCreditNote::where('reservation_invoice_id', $existedInvoice->id)->orderBy('created_at', 'desc')->first();
            } else {
                $existedCreditNote = null;
            }
            //check if there's credit note

            if(($existedInvoice && !$existedCreditNote) || Reservation::checkNightRunExecuted($reservation, $day->date)) {
                $day_copy = $day;
                if($existedInvoice && !$existedCreditNote) {
                    $day_copy->hasInvoice = true;
                }
                if(Reservation::checkNightRunExecuted($reservation, $day->date)) {
                    $day_copy->hasNightRun = true;
                }
                $day = $day_copy;
                $days_has_invoices->push($day);
            } else {
                $days_to_mutate->push($day);
            }

        }

        if($request->get('checkDatesOnly')) {
            return ResponseHelper::success(__(''), $days_has_invoices->get());
        }

        if(!$user->hasPermissionTo('can edit reservation day price')) {
            return ResponseHelper::unauthorized();
        }

        $days = $days_to_mutate->get();

        if(count($days_has_invoices->get()) > 0) {
            return ResponseHelper::error(__('Invoice existed for targetted dates'), $days_has_invoices->get());
        }

        if(count($days) < 1) {
            return ResponseHelper::success(__('No date effected'), $reservation);
        }

        $total_price_old = $reservation->total_price;

        $reservation->updateReservationPriceByDay($days);

        $division = $reservation->total_price - $total_price_old;

        // dd([
        //     $reservation->total_price,
        //     $reservation->total_price - $total_price_old,
        //     round( $division , 2)
        // ]);
        if($reservation->save()) {
            $this->updateReservationWallet($reservation, round($division, 2));
            event(new ShomosReservationUpdated($reservation));
            event(new ReservationUpdated($reservation));
            return ResponseHelper::success(
                __('Price has been changed for :count dates', ['count' => count($days_to_mutate->get()) ]),
                $reservation
            );
        }

        return ResponseHelper::error(
            __('Price has been failed to update for :count dates', ['count' => count($days_to_mutate->get())]),
            $reservation
        );

    }

    private function updateReservationWallet(Reservation $reservation, $division) {

        if($reservation->status == 'canceled'){
            return;
        }

        $reservation->balance;

        if ($division < 0) {
            // $reservation->depositFloat(abs(floatval($division)), [
            //     'category' => 'update_reservation',
            //     'statement' => 'update Reservation Total Price deposit',
            // ], true, false);

            $meta = [
                    'category' => 'update_reservation',
                    'statement' => 'update Reservation Total Price deposit',
            ];
            DB::transaction(function () use ($reservation, $division,$meta) {
                DB::table('transactions')->insert(
                    [
                        'payable_type' => 'App\Reservation',
                        'payable_id' => $reservation->id,
                        'wallet_id' => $reservation->wallet->id,
                        'type' => 'deposit',
                        'transaction_flag' => 'normal',
                        'amount' => floatval(abs($division)) * 100,
                        'confirmed' => 1,
                        'is_public' => 0,
                        'created_by' => auth()->user()->id,
                        'updated_by' => auth()->user()->id,
                        'meta' => json_encode($meta),
                        'uuid' => Str::uuid(),
                        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    ]
                );
            });



        } elseif ($division > 0) {
            // $reservation->forceWithdrawFloat(floatval($division), [
            //     'category' => 'update_reservation',
            //     'statement' => 'update Reservation Total Price Withdraw',
            // ], true, false);

            $meta = [
                'category' => 'update_reservation',
                'statement' => 'update Reservation Total Price withdraw',
            ];
            DB::transaction(function () use ($reservation, $division,$meta) {
                DB::table('transactions')->insert(
                    [
                        'payable_type' => 'App\Reservation',
                        'payable_id' => $reservation->id,
                        'wallet_id' => $reservation->wallet->id,
                        'type' => 'withdraw',
                        'transaction_flag' => 'normal',
                        'amount' => floatval(-1*$division) * 100,
                        'confirmed' => 1,
                        'is_public' => 0,
                        'created_by' => auth()->user()->id,
                        'updated_by' => auth()->user()->id,
                        'meta' => json_encode($meta),
                        'uuid' => Str::uuid(),
                        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    ]
                );
            });
        }

    }

    public function filterExistingPriceChange($exist_dates,$dates) {

        $datesAsIndex = Array();

        foreach ($exist_dates['days'] as $exist_day) {
            $exist_date = $exist_day['date'];
            foreach ($dates['days'] as $key => $day) {
                if($exist_date == $day['date']) {
                    $dates['days'][$key]['price_row'] = $exist_day['price_row'];
                    $dates['days'][$key]['price'] = $exist_day['price'];
                }

                $dateArray = new ObjectArray();
                $dateArray->set('price_row',  $dates['days'][$key]['price_row']);
                $dateArray->set('price',  $dates['days'][$key]['price']);
                $datesAsIndex[$day['date']] = $dateArray;

            }
        }

        $dates = $this->calculateDatePricesArray($dates);

        return (object) [
            'dates' => $dates,
            'datesAsIndex' => $datesAsIndex
        ];
    }

    /*
    *
    */
    public function mergePricesWithFilterExistingPrice($prices, $datesAsIndex) {
        foreach ($prices['days'] as $key => $day) {
            $prices['days'][$key]['price_row'] = $datesAsIndex[$day['date']]->get('price_row');
            $prices['days'][$key]['price']     = $datesAsIndex[$day['date']]->get('price');
        }

        $dates = $this->calculateDatePricesArray($prices);

        return $dates;

    }

    public function mergePricesWithFilterExistingPriceInput($prices, $datesAsIndex, $total_locked_amount, $locked_days, $price_total_raw) {
        $distributableAmount = $price_total_raw - $total_locked_amount;

        $ewa_percentage = $prices['ewa_parentage']; //2.5

        $vat_percentage = $prices['vat_parentage']; //15

        $distributableAmountWithoutVat = $distributableAmount / (1 + floatval($vat_percentage) / 100);

        $distributableAmountWithoutEwa =  $distributableAmountWithoutVat / (1 + floatval($ewa_percentage) / 100);

        $in_days = Array (
            'days' => []
        );

        $out_days = Array (
            'days' => []
        );



        foreach ($prices['days'] as $day) {
            $is_locked = false;
            foreach ($locked_days as $locked_day) {
                if ($day['date'] === $locked_day['date']) {
                    $is_locked = true;
                    break; // Break out of the inner loop as we found a match
                }
            }
            if (!$is_locked) {
                $in_days['days'][] = $day; // Push only if it's not locked
            }
        }

        $distributableAmount = $distributableAmountWithoutEwa / count($in_days['days']);

        foreach ($locked_days as $key => $locked_day) {
            array_push($out_days['days'], $locked_day);
        }

        for ($i= 0; $i < count($in_days['days']); $i++) {
            $in_days['days'][$i]['price_row'] = $distributableAmount;
            $in_days['days'][$i]['price']     = $distributableAmount;
            $out_days['days'][] = $in_days['days'][$i];
        }

        $prices['days'] = $out_days['days'];

        $dates = $this->calculateDatePricesArray($prices);

        return $dates;
    }

    public function calculateDatePricesArray ($dates) {

        $total = 0;
        $sub_total = 0;
        $ewa_total = 0;
        $vat_total = 0;
        $ewa_percentage = $dates['ewa_parentage'];
        $vat_percentage = $dates['vat_parentage'];

        foreach ($dates['days'] as $key => $day) {
            $sub_total += floatval($day['price']);
        }

        $ewa_total = $sub_total / 100 * floatval($ewa_percentage);

        $dates['sub_total'] = $sub_total;

        $dates['price'] = $sub_total;

        $sub_total = $sub_total + $ewa_total;

        $vat_total = $sub_total / 100 * floatval($vat_percentage);

        $total = $sub_total + $vat_total;

        $dates['total_vat'] = $vat_total;

        $dates['total_ewa'] = $ewa_total;

        $dates['total_price'] = $total;

        $dates['total_price_raw'] = $total;

        return $dates;
    }

    public function getMainReservation(Request $request,$id){
        return response()->json(Reservation::with('signedContracts')->withCount('signedContracts')->find($id));
    }

    public function callGrpMapper(Request $request,$id){

        $reservation = Reservation::with('wallet')->find($id);

        $balance = shareableGroupBalance($reservation);

        return response()->json([
            'success' => true,
            'group_balance' =>  $balance,
        ]);


    }

    public function getTransactions($reservation_id)
    {
        $transactions = DB::table('transactions')
            ->leftJoin('users', 'transactions.created_by', '=', 'users.id')
            ->where('transactions.payable_id', $reservation_id)
            // ->where('transactions.is_insurance', '!=', 1)
            ->orderByDesc('transactions.id')
            ->select([
                'transactions.type',
                'transactions.amount',
                'transactions.is_public',
                'transactions.is_insurance',
                DB::raw("
                    CASE
                        WHEN transactions.created_by IS NULL THEN 'System'
                        ELSE users.name
                    END AS created_by
                "),
            ])
            ->get();

        return response()->json($transactions);
    }


    public function updateReservationBalance(Request $request)
    {
        $reservation = Reservation::find($request->reservation_id);
        if (!$reservation) {
            return response()->json(['success' => false, 'message' => 'Reservation not found'], 404);
        }
        $amount = -1 * (float) $request->amount;
        $update = $this->updateReservationWallet($reservation,  $amount);
        return response()->json(['success' => true, 'balance' => $reservation->wallet->balance]);
    }
}
