<?php

namespace App\Http\Controllers;

use App\Team;
use App\Term;
use Exception;
use Carbon\Carbon;
use App\Reservation;
use App\TeamCounter;
use App\Transaction;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use App\NotificationControl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use App\Mail\Customer\ReservationPaymentCollectedEmail;

class CollectPaymentController extends Controller
{
    public function collectPayment()
    {
        $base_url = env('HYPERPAY_PAYMENT_URL');
        $client = new Client([
            'headers' => [
                'Content-Type' => 'application/json'
            ],
        ]);

        try {
            // just hit api on new hyperpay payment project
            // @note : api only wants the amount and paymentMethod and hyperpay payment project will handle the rest
            $initiateRequest = $client->request('POST', $base_url . 'api/hyperpay/get-payments', [
                'body' => \GuzzleHttp\json_encode([
                    'team_id' => auth()->user()->current_team_id
                ]),
                'exceptions' => true
            ]);

            $payments = json_decode($initiateRequest->getBody()->getContents());
            return view('quick_payment')->with(['quickPayments' => $payments]);
        } catch(Exception $ex) {
            return response()->json($ex->getMessage());
        }
    }


    public function preparePayoutHyperSplitObject($team, $actual_amount, $payment_method, $merchant_transaction_id)
    {
        if(is_null($team)) {
            return null;
        }
        $transaction_fees = $team->transactions_fees;
        if($payment_method == 'visa') {
            $payment_method_percentage_fees = $team->visa_percentage;
        }

        if($payment_method == 'master_card') {
            $payment_method_percentage_fees = $team->master_percentage;
        }

        if($payment_method == 'stc') {
            $payment_method_percentage_fees = $team->stc_percentage;
        }

        if($payment_method == 'amex') {
            $payment_method_percentage_fees = $team->amex_percentage;
        }

        if($payment_method == 'mada') {
            $payment_method_percentage_fees = $team->mada_percentage;
        }


        $payout = [];
        $payout['configId'] = env('HYPERSPLIT_CONFIG_ID');
        $payout['transferOption'] = strval($team->transfer_option);
        $payout['merchantTransactionId'] = $merchant_transaction_id;
        $price_amount = $this->calculateTransferAmount($transaction_fees, $payment_method_percentage_fees, $actual_amount);
        $cost_amount = $this->calculateCostAmount($payment_method, $payment_method_percentage_fees, $actual_amount);
        $fandaqah_revenue_amount = sprintf("%0.5s", number_format($cost_amount - $price_amount, 2, '.', ''));

        $hotel_address = null;
        if(Config::get('app.locale') == 'ar') {
            $string =  getSettingItem($team->id, 'hotel_address');
            $hotel_address = preg_replace('/[^أ-يA-Za-z0-9 ]/ui', "", $string);
        } else {
            $string =  getSettingItem($team->id, 'hotel_en_address');
            $hotel_address = preg_replace('/[^أ-يA-Za-z0-9 ]/ui', "", $string);
        }

        $payout['beneficiary'] = [
            [
                'name' => $team->bank_title,
                'accountId' => $team->bank_iban_number,
                'transferAmount' =>  number_format($price_amount, 2, '.', ''),
                'debitCurrency' => $team->currency ?? 'SAR',
                'transferCurrency' => $team->currency ?? 'SAR',
                'bankIdBIC' => $team->bank_bic == env('FANDAQAH_BANK_Id_BIC') ? null : $team->bank_bic ,
                'payoutBeneficiaryAddress1' =>   $hotel_address ?? 'no address 1',
                'payoutBeneficiaryAddress2' =>   $hotel_address ?? 'no address 2',
                'payoutBeneficiaryAddress3' =>   $hotel_address ?? 'no address 3',
                "batchDescription" => 'Hotelier Split'
            ],
            [
                'name' => env('BENEFICIARY_NAME'),
                'accountId' => env('BENEFICIARY_ACCOUNT_Id'),
                'transferAmount' => number_format($fandaqah_revenue_amount, 2, '.', ''),
                'debitCurrency' => env('BENEFICIARY_DEBIT_CURRENCY'),
                'transferCurrency' => env('BENEFICIARY_TRANSFER_CURRENCY'),
                'bankIdBIC' => env('BENEFICIARY_BANK_Id_BIC'),
                'payoutBeneficiaryAddress1' =>  env('BENEFICIARY_ADDRESS1'),
                'payoutBeneficiaryAddress2' =>  env('BENEFICIARY_ADDRESS2'),
                'payoutBeneficiaryAddress3' =>  env('BENEFICIARY_ADDRESS3'),
                "batchDescription" => 'Fandaqah Split'
            ]

        ];

        return $payout;

    }

    public function calculateTransferAmount($transaction_fees = 0, $payment_method_percentage_fees = 0, $amount)
    {

        $amount_after_percentage = round($amount - ($amount * ($payment_method_percentage_fees / 100)), 2);
        $amount_after_transaction_fees = round($amount_after_percentage - $transaction_fees, 2);
        $total_fees = round($amount - $amount_after_transaction_fees, 2);
        $total_fees_after_vat = round($total_fees +  ($total_fees * 0.15), 2);
        $final_transfer_amount =  sprintf("%05s", round($amount - $total_fees_after_vat, 2));

        $payout_calculations_data = [
           'total_fees' => $total_fees,
           'vat_on_fees' => round(($total_fees * 0.15), 2),
           'total_fees_includes_vat' => $total_fees_after_vat,
           'final_transfer_amount' => $final_transfer_amount
        ];

        Session::put('payout_calculations_data', $payout_calculations_data);

        return $final_transfer_amount;
    }

    public function getRevenueAccountPercentageFees($method)
    {
        $fees_percentage = null;
        switch ($method) {
            case 'visa':
                $fees_percentage = env('BENEFICIARY_FANDAQAH_VISA_PERCENTAGE_FEES');
                break;
            case 'master_card':
                $fees_percentage = env('BENEFICIARY_FANDAQAH_MASTER_CARD_PERCENTAGE_FEES');
                break;
            case 'mada':
                $fees_percentage = env('BENEFICIARY_FANDAQAH_MADA_PERCENTAGE_FEES');
                break;
            case 'stc':
                $fees_percentage = env('BENEFICIARY_FANDAQAH_STC_PERCENTAGE_FEES');
                break;
            case 'amex':
                $fees_percentage = env('BENEFICIARY_FANDAQAH_AMEX_PERCENTAGE_FEES');
                break;

            case 'apple_pay':
                $fees_percentage = env('BENEFICIARY_FANDAQAH_APPLE_PAY_PERCENTAGE_FEES');
                break;

            default:
                $fees_percentage = env('BENEFICIARY_FANDAQAH_DEFAULT_PERCENTAGE_FEES');
                break;
        }

        return $fees_percentage;
    }
    public function calculateCostAmount($payment_method, $payment_method_percentage_fees = 0, $amount)
    {

        $payment_method_percentage_fees = $this->getRevenueAccountPercentageFees($payment_method);
        $amount_after_percentage = round($amount - ($amount * ($payment_method_percentage_fees / 100)), 2);
        $amount_after_transaction_fees = $amount_after_percentage;
        $total_fees = round($amount - $amount_after_transaction_fees, 2);
        $total_fees_after_vat = round($total_fees +  ($total_fees * 0.15), 2);
        $final_cost_amount =  round($amount - $total_fees_after_vat, 2);
        return $final_cost_amount;
    }

    public function createCheckoutID(Request $request)
    {
        if (!$request->get('reference_id') || !$request->get('guest_name') || !$request->get('source') || !$request->get('amount')) {
            return response()->json(['success' => false, 'message' => 'PLease make sure to fill are form fields', 'checkout_id' => null]);
        }

        $enabled_hyper_split = false;
        $team = null;
        if($request->get('entity_type') == 'reservation') {
            $reservation = Reservation::with('team')->find($request->get('reference_id'));
            $team = $reservation->team;
            if($reservation->team->enable_hyper_split) {
                $enabled_hyper_split = true;
            }
            if($reservation && $reservation->team) {
                if($reservation->team->payment_preprocessor != 'hyperpay') {
                    return response()->json(['status' => 'unauthorized' , 'message' => __('Invalid Payment Provider Contact Support Team')]);
                }
            }
        }

        $merchant_transaction_id = date('mdHi') . $team->id . $request->get('reference_id');

        $customer = [
            'name' => $reservation->customer->name ?? 'no-name',
            'email' => $reservation->customer->email ?? $reservation->id . '@fandaqah.com',
            'merchantCustomerId' =>  $reservation->team_name,
            'billing_street1' => $reservation->customer->address ?? 'no-info',
            'billing_country' => $reservation->customer->nationality_string ?? 'no-country'
        ];
        // Prepare Payout Object For HyperSplit
        $payout = $this->preparePayoutHyperSplitObject($team, $request->get('amount'), $request->payment_method, $merchant_transaction_id);
        $base_url = env('HYPERPAY_PAYMENT_URL');
        $client = new Client([
            'headers' => [
                'Content-Type' => 'application/json'
            ],
        ]);

        $payment_method = $request->get('payment_method');
        Session::put('payment_method', $payment_method);
        try {
            // just hit api on new hyperpay payment project
            // @note : api only wants the amount and paymentMethod and hyperpay payment project will handle the rest
            $initiateRequest = $client->request('POST', $base_url . 'api/hyperpay/pay', [
                'body' => \GuzzleHttp\json_encode([
                    'paymentMethod' => $payment_method,
                    'amount' => $request->get('amount'),
                    'payout' => $enabled_hyper_split ? json_encode($payout) : null,
                    'payout_calculations_data' => $enabled_hyper_split ? Session::get('payout_calculations_data') : null,
                    'guest_name' => $request->get('guest_name'),
                    'source' => $request->get('source'),
                    'merchantTransactionId' => $merchant_transaction_id,
                    'customer' => $customer,
                    'user_id' => auth()->user()->id ?? null,
                    'team_id' => auth()->user()->current_team_id ?? null,
                    'reference_id' => $request->get('reference_id') ?? null
                ]),
                'exceptions' => true
            ]);

            $response = json_decode($initiateRequest->getBody()->getContents());
            if ($response->entity_id && !is_null($response->checkout_id)) {
                Session::put('entity_id', $response->entity_id);
                Session::put('entity_type', $request->get('entity_type'));
                Session::put('reference_id', $request->get('reference_id'));
                Session::put('guest_name', $request->get('guest_name'));
                Session::put('source', $request->get('source'));
                Session::put('amount', $request->get('amount'));
                Session::put('lang', $request->get('lang'));
                Session::put('merchant_transaction_id', $merchant_transaction_id);
                if ($request->get('entity_type') == 'team') {
                    Session::put('team_id', auth()->user() ? auth()->user()->current_team_id : null);
                }
            }
            return response()->json($response);
        } catch(Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function quickPaymentCallback(Request $request)
    {
        $base_url = env('HYPERPAY_PAYMENT_URL');
        $client = new Client([
            'headers' => [
                'Content-Type' => 'application/json'
            ],
        ]);

        $payment_method = Session::get('payment_method');
        $entity_id = Session::get('entity_id');
        $reference_id = Session::get('reference_id');
        $entity_type = Session::get('entity_type');
        $team_id = Session::get('team_id');
        $lang = Session::get('lang');
        $amount = Session::get('amount');
        $merchant_transaction_id = Session::get('merchant_transaction_id');
        $guest_name = Session::get('guest_name');
        $source = Session::get('source');
        $payout_calculations_data = Session::get('payout_calculations_data');

        $reservation = null;
        if ($entity_type == 'reservation') {
            $reservation = Reservation::with('customer', 'promissory')->find($reference_id);
            $team_id = $reservation->team_id;
        }
        $data = [
            'reference_id' => $reference_id,
            'team_id' => $team_id,
            'guest_name' => $guest_name,
            'source' => $source,
            'amount' => $amount,
            'merchant_transaction_id' => $merchant_transaction_id
        ];

        try {

            // just hit api on new hyperpy payment project
            // @note : api only wants the amount and paymentMethod and hyperpay payment project will handle the rest
            $initiateRequest = $client->request('POST', $base_url . 'api/hyperpay/validate-payment', [
                'body' => \GuzzleHttp\json_encode([
                    'payment_id_url' => $request->get('id'),
                    'entity_id' => $entity_id,
                    'entity_type' => $entity_type,
                    'data' => $data,
                    'payout_calculations_data' => $payout_calculations_data

                ]),
                'exceptions' => true
            ]);

            $response = json_decode($initiateRequest->getBody()->getContents());

            if ($response->success) {
                Session::flash('payment_callback_code', $response->code);
                Session::flash('payment_callback_message', $response->description);

                if ($entity_type == 'reservation') {

                    // check if there is promissory on the reservation then we need to collect ( all / part ) of it
                    $promissoryTerm = null;
                    if($reservation->promissory) {
                        $promissoryTerm = Term::whereJsonContains('name->ar', 'تحصيل كمبيالة')->whereJsonContains('name->en', 'Fulfill Promissory')->where('type', 2)->whereNull('deleted_at')->first();

                        if($reservation->promissory->status == 'pending') {
                            // means promissory is not collected yet
                            $reservation->promissory->collected_amount = $reservation->promissory->total_amount;
                            $reservation->promissory->status = 'fulfilled';
                            $reservation->promissory->save();
                        }
                    }
                    $term_id = null;
                    if($promissoryTerm) {
                        $term_id = $promissoryTerm->id;
                    } else {
                        $first_deposit_term = Term::where('team_id', $team_id)->where('type', 2)->first();
                        $term_id = $first_deposit_term->id;
                    }

                    $meta = [
                        'category' => 'reservation',
                        'statement' => $reservation->promissory ? 'Billed Online Fulfill Promissory Number' : 'Billed Online',
                        'type' => $term_id ? $term_id : null,
                        'payment_type' => $payment_method,
                        'note' => $request->meta['note'],
                        'reference' => $response->reference,
                        'date' => Carbon::now()->format('Y-m-d H:i'),
                        'from' => $data['guest_name'],
                        'employee' => null,
                        'preprocessor' => $response->preprocessor,
                        'related_to_prmissory' => $reservation->promissory ? true : false,
                        'promissory_serial' =>  $reservation->promissory ? $reservation->promissory->serial : null,
                    ];

                    // need to create a transaction
                    $transaction = $reservation->depositFloat(floatval($response->amount), $meta, true, true);

                    // turn reservation into confirmed reervation
                    $reservation->status = 'confirmed';
                    $reservation->save();

                    // send sms

                    if ($reservation->customer->phone) {
                        $data = [
                            'amount' => $response->amount,
                            'currency' => Team::find($team_id)->currency ?? __('SAR', [], $lang),
                            'unit_name' => $reservation->unit->unit_category->name,
                            'contract_number' => $reservation->number,
                            'date_in' => $reservation->date_in,
                            'date_out' => $reservation->date_out,
                        ];

                        $this->sendSms($team_id, $data, $lang, $reservation->customer->phone);
                        // $payment_message = "Your card was charged by $response->amount for reservation $reservation->number";
                        // sendSms($team_id, $payment_message, $reservation->customer->phone);
                    }

                    // send email
                    if ($reservation->customer->email) {
                        $hotelEmail =  DB::table('settings')->where('key' , '=' , 'day_end')->where('team_id' , '=' , $reservation->team_id)->value('value');
                        $hotelEmail = ($hotelEmail != "" && !is_null($hotelEmail) && filter_var($hotelEmail, FILTER_VALIDATE_EMAIL)) ? $hotelEmail : 'no-reply@fandaqah.com';

                        $data = [
                            'to' => $reservation->customer->email,
                            'reply_to' => $hotelEmail,
                            'subject' => __('Payment Receipt For Reservation : :number',['number' => $reservation->number],$lang),
                            'html' => view('email.customer.reservation_payment_collected')
                                        ->with(['reservation'   =>  $reservation , 'amount' => floatval($response->amount) , 'transaction_meta' => $meta , 'lang' => $lang])
                                        ->render(),
                        ];

                        $send = sendMailUsingMailMicroservice($data);
                        // Mail::to($reservation->customer->email)->send(new ReservationPaymentCollectedEmail($reservation->id, floatval($response->amount), $meta, $lang));
                    }


                    Session::forget('payment_method');
                    Session::forget('entity_id');
                    Session::forget('reference_id');
                    Session::forget('entity_type');
                    Session::forget('team_id');
                    Session::forget('payment_callback_code');
                    Session::forget('payment_callback_message');
                    Session::forget('payout_calculations_data');

                    Session::put('transaction_meta', $meta);
                    Session::put('amount', $response->amount);

                    // initiate redirect
                    return redirect("/reservation/collect-payments?id=$reservation->id&l=$lang&tid=$transaction->id");
                } else {
                    return redirect('/collect-payments');
                }
            } else {
                Session::flash('payment_failed_message', $response->res->result->description);
                return redirect("/reservation/collect-payments?id=$reservation->id&l=$lang&n=$amount");
            }
        } catch(Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function sendSms($team_id, $data, $lang, $customer_phone_number)
    {
        $notificationQueryObj = NotificationControl::query();
        $options = $notificationQueryObj->whereRaw(' `key` = ? ', ['alert_payment_successful'])->whereRaw(' team_id = ? ', [$team_id])->first();
        if($options) {
            $options = $options->value;
            if ($options['sms']) {
                $message = $this->formatMessage($options, $data, $lang);
                try{
                    sendSms($team_id, $message, $customer_phone_number);
                } catch (\Throwable $th) {
                    Log::warning("Hyperpay callback - Failed to send sms to guest , There was a problem with your request: " . $th->getMessage());
                }

            }
        }
    }

    public function formatMessage($options, $data, $lang)
    {
        $message = $options['content'];
        $message .= PHP_EOL;

        if ($options['contentOptions']['contractNumber']) {
            $message .= __('Contract Number', [], $lang) . ' ' . $data['contract_number'];
            $message .= PHP_EOL;
        }

        if ($options['contentOptions']['date']) {
            $message .= __('Reservation From :from To :to', ['from' => $data['date_in'], 'to' => $data['date_out']], $lang);
            $message .= PHP_EOL;
        }

        if ($options['contentOptions']['unitName']) {
            $message .= __('Unit Type :type', ['type' => $data['unit_name']], $lang);
            $message .= PHP_EOL;
        }

        if ($options['contentOptions']['invoiceAmount']) {
            $message .= __('Amount Paid :amount :currency', ['amount' => $data['amount'], 'currency' => $data['currency']], $lang);
            $message .= PHP_EOL;
        }

        return $message;
    }

    public function collectReservationPayment(Request $request)
    {
        if($request->has('data')) {
            $encoded = $request['data'];
            $decoded_data = json_decode(base64_decode($encoded));
            $reservation_id = $decoded_data->id;
            $reservation = Reservation::with(['customer', 'wallet', 'source'])->find($reservation_id);
            $amount_to_charge = $decoded_data->n;

            // call hyperpay to check if there is a pre-payment record for the reservation or not
            // if found it means there is a pending payment process that will be fulfilled
            $data = [
                'reference_id' => $reservation->id,
                'team_id' => $reservation->team_id,
            ];

            $headers =  [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '. env('API_V2_JWT_AUTHORIZATION')
            ];

            $url = env('HYPERPAY_PAYMENT_URL') . 'api/v2/external/check-pre-payment-transaction';
            $checkReservationPrePaymentInMs = guzzleRequester($url, $headers, 'POST', $data);
            if($checkReservationPrePaymentInMs->data && !$checkReservationPrePaymentInMs->data->can_use_payment_link){
                return view('collect_reservation_payment_not_allowed')->with([
                    'lang' => $decoded_data->l ?? 'ar'
                ]);
            }
            $reservation->wallet->refreshBalance();
            return view('collect_reservation_payment')->with([
                'reservation' => $reservation,
                'amount_to_charge' => $amount_to_charge,
                'amount_is_valid' => $amount_to_charge > 0 ? true : false,
                'lang' => $decoded_data->l ?? 'ar',
                't' => request()->has('tid') && request()->get('tid') ? Transaction::find(request()->get('tid')) : null
            ]);
        } else {
            $reservation = Reservation::with(['customer', 'wallet', 'source'])->find($request->get('id'));

            $amount_to_charge = $request->get('n');

            return view('collect_reservation_payment')->with([
                'reservation' => $reservation,
                'amount_to_charge' => $amount_to_charge,
                'amount_is_valid' => $amount_to_charge > 0 ? true : false,
                'lang' => $request->get('l') ?? 'ar',
                't' => request()->has('tid') && request()->get('tid') ? Transaction::find(request()->get('tid')) : null
            ]);

        }
    }


    public function paymentRenewalCallback(Request $request)
    {
        $base_url = env('HYPERPAY_PAYMENT_URL');
        $client = new Client([
            'headers' => [
                'Content-Type' => 'application/json'
            ],
        ]);

        $data = Session::get('data');
        $entity_id = Session::get('entity_id');
        try {
            $initiateRequest = $client->request('POST', $base_url . 'api/hyperpay/validate-payment', [
                'body' => \GuzzleHttp\json_encode([
                    'payment_id_url' => $request->get('id'),
                    'entity_id' => $entity_id,
                    'entity_type' => 'team',
                    'data' => $data
                ]),
                'exceptions' => true
            ]);

        } catch(Exception $ex) {
            return response()->json($ex->getMessage());
        }
    }

    public function createTransactionAfterCallbackFailure(Request $request)
    {

        $reservation = Reservation::with('customer', 'promissory','wallet')->find($request->reservation_id);
        if (!$reservation) {
           Log::warning("Trying to get reservation that is not exists. res id : " . $request->reservation_id);
            return;
        }
        $team_id = $reservation->team_id;
        $amount = $request->amount;
         // check if there is promissory on the reservation then we need to collect ( all / part ) of it
         $promissoryTerm = null;
         if($reservation->promissory) {
             $promissoryTerm = Term::whereJsonContains('name->ar', 'تحصيل كمبيالة')->whereJsonContains('name->en', 'Fulfill Promissory')->where('type', 2)->whereNull('deleted_at')->first();

             if($reservation->promissory->status == 'pending') {
                 // means promissory is not collected yet
                 $reservation->promissory->collected_amount = $reservation->promissory->total_amount;
                 $reservation->promissory->status = 'fulfilled';
                 $reservation->promissory->save();
             }
         }
         $term_id = null;
         if($promissoryTerm) {
             $term_id = $promissoryTerm->id;
         } else {
             $first_deposit_term = Term::where('team_id', $team_id)->where('type', 2)->first();
             $term_id = $first_deposit_term->id;
         }

         $meta = [
             'category' => 'reservation',
             'statement' => $reservation->promissory ? 'Billed Online Fulfill Promissory Number' : 'Billed Online',
             'type' => $term_id ? $term_id : null,
             'payment_type' => $request->payment_method,
             'note' => $request->meta['note'],
             'reference' => $request->payment_id,
             'date' => Carbon::parse($request->created_at)->format('Y-m-d H:i'),
             'from' => $request->guest_name,
             'employee' => null,
             'preprocessor' => $request->preprocessor,
             'related_to_prmissory' => $reservation->promissory ? true : false,
             'promissory_serial' =>  $reservation->promissory ? $reservation->promissory->serial : null,
         ];

         // need to create a transaction
        //  $transaction = $reservation->depositFloat(floatval($amount), $meta, true, true);
        /**
         * i had to use it this way to avoid observer as it needs team id for auth user
         * since the job runs in background i can not use the approach
         */
        try{
            $transaction = DB::table('transactions')->insert(
                [
                    'payable_type' => 'App\Reservation',
                    'payable_id' => $reservation->id,
                    'wallet_id' => $reservation->wallet->id,
                    'type' => 'deposit',
                    'transaction_flag' => 'normal',
                    'amount' => floatval($amount) * 100,
                    'number' => $this->getTransactionNumber($team_id),
                    'confirmed' => 1,
                    'meta' => json_encode($meta),
                    'uuid' => Str::uuid(),
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ]
            );
        } catch (\Throwable $th)  {
            Log::warning("Error in creating transaction " . $th->getMessage());

            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }


         // turn reservation into confirmed reervation
         $reservation->status = 'confirmed';
         $reservation->save();

         // send sms

         if ($reservation->customer->phone) {
             $data = [
                 'amount' => $amount,
                 'currency' => Team::find($team_id)->currency ?? __('SAR', [], 'ar'),
                 'unit_name' => $reservation->unit->unit_category->name,
                 'contract_number' => $reservation->number,
                 'date_in' => $reservation->date_in,
                 'date_out' => $reservation->date_out,
             ];

             $this->sendSms($team_id, $data, 'ar', $reservation->customer->phone);

         }

         // send email
         if ($reservation->customer->email) {
            try{
                $hotelEmail =  DB::table('settings')->where('key' , '=' , 'day_end')->where('team_id' , '=' , $reservation->team_id)->value('value');
                $hotelEmail = ($hotelEmail != "" && !is_null($hotelEmail) && filter_var($hotelEmail, FILTER_VALIDATE_EMAIL)) ? $hotelEmail : 'no-reply@fandaqah.com';

                $data = [
                    'to' => $reservation->customer->email,
                    'reply_to' => $hotelEmail,
                    'subject' => __('Payment Receipt For Reservation : :number',['number' => $reservation->number], 'ar'),
                    'html' => view('email.customer.reservation_payment_collected')
                                ->with(['reservation'   =>  $reservation , 'amount' => floatval($amount) , 'transaction_meta' => $meta , 'lang' => 'ar'])
                                ->render(),
                ];

                $send = sendMailUsingMailMicroservice($data);
                // Mail::to($reservation->customer->email)->send(new ReservationPaymentCollectedEmail($reservation->id, floatval($amount), $meta, 'ar'));
            } catch (\Throwable $th)  {
                Log::warning("Hyperpay callback - Failed to send mail to the guest , There was a problem with your request: " . $th->getMessage());
            }
         }
         if($transaction){
            return response()->json([
                'success' => true,
                'message' => 'transaction created successfully'
            ]);
         }

         return response()->json([
            'success' => false,
            'message' => 'transaction has not been created, i will retry again'
        ]);
    }

    function getTransactionNumber($team_id){
        $counter = TeamCounter::where('team_id', $team_id)->first();
        if (!$counter) {
            $counter = TeamCounter::create();
            $counter->forceFill([
                'team_id' => $team_id,
            ])->save();
        }
        $transaction_number  = $counter->receipt_num;
        $counter->last_receipt_number = $transaction_number;
        $counter->save();

        return $transaction_number;

    }
}
