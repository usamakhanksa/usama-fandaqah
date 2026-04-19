<?php

namespace Surelab\FinancialManagement\Http\Controllers;

use App\Handlers\Settings;
use App\Team;
use App\Term;
use App\Wallet;
use Carbon\Carbon;
use App\Promissory;
use App\Reservation;
use App\Transaction;
use Illuminate\Http\Request;
use App\Services\CustomPagination;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Spatie\QueryBuilder\QueryBuilder;
use App\Observers\TransactionObserver;
use Spatie\QueryBuilder\AllowedFilter;
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Http\Resources\FinancialManagement\FinancialResource;
use App\Objects\Invoice;
use App\ReservationInvoice;
use App\Integration;
use App\InvoiceCreditNote;
use App\OnlinePaymentServiceInvoice;
use App\ServiceLog;
use App\ServiceLogNote;
use Illuminate\Support\Facades\Auth;

class ToolController extends Controller
{
    public function storeTransaction(Request $request)
    {

        /** @var Team $team */
        $team = auth()->user()->currentTeam;
        $current_time = date('H:i');
        $incomingDate = $request->meta['date'];
        if(!$this->checkDate($incomingDate)) {
            return response()->json('invalid-date');
        }
        // $combinedTransactionDate = date('Y-m-d H:i', strtotime("$incomingDate $current_time"));

        $unit_category_id = request()->get('unit_category_id') != '' || !is_null(request()->get('unit_category_id')) ? request()->get('unit_category_id') : null;
        $combinedTransactionDate = $incomingDate;
        $newMeta = [
          "category" => $request->meta['category'],
          "statement" => $request->meta['statement'],
          "type" => $request->meta['type'],
          "payment_type" => $request->meta['payment_type'],
          "note" => $request->meta['note'],
          "reference" =>  $request->meta['reference'],
          "date" => $combinedTransactionDate,
          "from" => $request->meta['from'] ,
          "employee" => $request->meta['employee']
        ];

        if($request->get('type')  === 'withdraw') {
            $newMeta += ['received_by' => $request->meta['received_by']];
        }

        if($request->get('type')  === 'deposit') {
            $newMeta += ['person_in_charge' => $request->meta['person_in_charge']];
        }


        switch ($request->get('type')) {
            case 'deposit':
                $transaction = $team->wallet->depositFloat($request->amount, $newMeta, true, true);

                if($unit_category_id){
                    $transaction->unit_category_id = $unit_category_id;
                    $transaction->save();
                }
                if($request->get('termName') == 'تامين') {
                    $transaction->is_insurance = 1;
                    $transaction->confirmed = 0;
                    $transaction->unit_category_id = $unit_category_id;
                    $transaction->save();
                    return response()->json('success');
                }
                break;
            case 'withdraw':


                if($request->get('enable_tax_on_withdraw')) {
                    $transaction = $team->wallet->forceWithdrawFloat($request->amount_include_tax, $newMeta, true, true);
                    $transaction->amount_without_tax = -100 * $request->amount;
                    $transaction->enable_tax_on_withdraw = $request->enable_tax_on_withdraw;
                    $transaction->tax_percentage = $request->tax_percentage;
                    $transaction->tax_amount = -100 * $request->tax_amount;
                    $transaction->supplier_tax_number = $request->supplier_tax_number;
                    $transaction->invoice_number = $request->invoice_number;
                    $transaction->unit_category_id = $unit_category_id;
                    $transaction->save();
                } else {
                    $transaction = $team->wallet->forceWithdrawFloat($request->amount, $newMeta, true, true);
                }

                if($request->get('termName') == 'استرجاع تامين') {
                    $transaction->is_insurance = 1;
                    $transaction->confirmed = 0;
                    $transaction->unit_category_id = $unit_category_id;
                    $transaction->save();
                    return response()->json(true);
                }

                if($unit_category_id){
                    $transaction->unit_category_id = $unit_category_id;
                    $transaction->save();
                }
                break;
        }

        return response()->json('success');
    }


    public function checkDate($date)
    {
        $tempDate = explode('-', $date);
        if($tempDate[0] == "") {
            return false;
        }
        return true;
    }

    /**
     * @description : Fetch transaction details
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return mixed\
     */
    public function transactionDetails(NovaRequest $request)
    {

        $transaction = Transaction::find($request->get('id')) ;
        $current_url = \Config::get('app.url');
        return response()->json([
            'transaction' => $transaction ,
            'current_url' => $current_url
        ]);

    }

    public function updateTransaction(NovaRequest $request)
    {

        $transaction = Transaction::find($request->id);
        $user = Auth::user();
        if ($transaction->is_freezed == 1 && !$user->hasPermissionTo('edit/delete freezed transactions after business day')) {
            return response()->json([
                'status' => false,
                'message' =>__('messages.transaction_frozen')
            ], 403);
        }
        $unit_category_id = request()->get('unit_category_id') != '' || !is_null(request()->get('unit_category_id')) ? request()->get('unit_category_id') : null;


        if ($request->type == 'deposit') {
            $term = Term::where('id', $request->meta['type'])->where('name->ar', 'تحويل من الادارة الى الصندوق')->first();

            // a new fix here is required cause am editing a deposit transaction but it's related to pos deposit service
            if(isset($transaction->meta['category']) && $transaction->meta['category'] == 'service-deposit') {

                $newMeta = [
                    "category" => 'service-deposit',
                    "statement" => $request->meta['statement'],
                    "type" => $request->meta['type'],
                    "payment_type" => $request->meta['payment_type'],
                    "note" => $request->meta['note'],
                    "reference" =>  $request->meta['reference'],
                    "date" => $request->meta['date'],
                    "from" => $request->meta['from'] ,
                    "employee" => $request->meta['employee'],
                    "pos" =>  true,
                    "qty" =>  1,
                    "services" => $transaction->meta['services'],
                    "sub_total" => $transaction->meta['sub_total'] ,
                    "ttx_total" =>  $transaction->meta['ttx_total'],
                    "vat_total" =>  $transaction->meta['vat_total'],
                    "total_with_taxes" =>  $request->amount
                  ];

                $transaction->amount = $transaction->wallet->decimal_places == 3 ? $request->amount * 1000 : $request->amount * 100;
                $transaction->meta = $newMeta;
                $transaction->transaction_flag = $term ? 'managerial' : 'normal';


                $transaction->unit_category_id = $unit_category_id;


                $transaction->save();
            } else {

                $transaction->amount = $transaction->wallet->decimal_places == 3 ? $request->amount * 1000 : $request->amount * 100 ;
                $transaction->meta = $request->meta;
                $transaction->transaction_flag = $term ? 'managerial' : 'normal';

                if($request->get('termName') && $request->get('termName') == 'تامين') {
                    $transaction->is_insurance = 1;
                    $transaction->confirmed = 0;
                    $transaction->save();

                    return response()->json(true);
                }

                $transaction->is_insurance = 0;
                $transaction->confirmed = 1;

                $transaction->unit_category_id = $unit_category_id;

                $transaction->save();
            }


        } else {
            $term = Term::where('id', $request->meta['type'])->where('name->ar', 'تحويل من الصندوق الى الادارة')->first();

            if($request->get('enable_tax_on_withdraw')) {
                $transaction->amount = ($transaction->wallet->decimal_places == 3 ? -1000 : -100) *  $request->amount_include_tax ;
                $transaction->amount_without_tax = ($transaction->wallet->decimal_places == 3 ? -1000 : -100) * $request->amount;
                $transaction->enable_tax_on_withdraw = $request->enable_tax_on_withdraw;
                $transaction->tax_percentage = $request->tax_percentage;
                $transaction->tax_amount = ($transaction->wallet->decimal_places == 3 ? -1000 : -100) * $request->tax_amount;
                $transaction->supplier_tax_number = $request->supplier_tax_number;
                $transaction->invoice_number = $request->invoice_number;
                $transaction->save();
            } else {
                $transaction->amount = $transaction->wallet->decimal_places == 3 ? $request->amount * -1000 : $request->amount * -100;
                $transaction->amount_without_tax = 0;
                $transaction->enable_tax_on_withdraw = false;
                $transaction->tax_percentage = 0;
                $transaction->tax_amount = 0;
                $transaction->supplier_tax_number = null;
                $transaction->invoice_number = null;
            }

            $transaction->meta = $request->meta;
            $transaction->transaction_flag = $term ? 'managerial' : 'normal';
            if($request->get('termName') && $request->get('termName') == 'استرجاع تامين') {
                $transaction->is_insurance = 1;
                $transaction->confirmed = 0;
                $transaction->save();

                return response()->json(true);
            }

            $transaction->unit_category_id = $unit_category_id;
            $transaction->is_insurance = 0;
            $transaction->confirmed = 1;
            $transaction->save();
        }


        return response()->json([
            'status' => true
        ]) ;

    }

    public function fetchStatistics(NovaRequest $request)
    {

        // Fetch resources ids
        $resources_ids = $request->all();

        $cash_transactions = [];
        $bank_transfer_transactions = [];
        $mada_transactions = [];
        $credit_card_transactions = [];
        foreach ($resources_ids as $id) {

            // find the transaction
            $transaction = Transaction::find($id);
            // check if payment type key isset
            if (isset($transaction->meta['payment_type'])) {
                // switch the payment type key
                switch ($transaction->meta['payment_type']) {

                    case 'cash':
                        $cash_transactions [] = abs($transaction->amount / 100);
                        break;
                    case 'bank-transfer':
                        $bank_transfer_transactions [] = abs($transaction->amount / 100);
                        break;
                    case 'mada':
                        $mada_transactions [] = abs($transaction->amount / 100);
                        break;
                    case 'credit':
                        $credit_card_transactions [] = abs($transaction->amount / 100);
                        break;
                    default:
                        break;

                }
            }
        }


        $cash = array_sum($cash_transactions);
        $bank_transfer = array_sum($bank_transfer_transactions);
        $mada = array_sum($mada_transactions);
        $credit_card = array_sum($credit_card_transactions);

        $grand = $cash + $bank_transfer + $mada + $credit_card;

        return response()->json([
            'cash_total' => $cash,
            'bank_transfer_total' => $bank_transfer,
            'mada_total' => $mada,
            'credit_card_total' => $credit_card,
            'grand' => $grand

        ]);
    }

    /**
     * @Description : Delete Transaction from financial management
     * @param NovaRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteTransaction(NovaRequest $request)
    {

        $transaction_id = $request->get('transaction_id');
        // Find the target transaction
        $transaction = Transaction::find($transaction_id);
        $user = Auth::user();
        if ($transaction->is_freezed == 1 && !$user->hasPermissionTo('edit/delete freezed transactions after business day')) {
            return response()->json([
                'status' => false,
                'message' =>__('messages.transaction_frozen')
            ], 403);
        }
        if($transaction->payable_type == 'App\Reservation' && $transaction->is_promissory) {
            // when deleting a transactions related to promissory , please affect promissory collected_amount
            $promissory =  $transaction->payable->promissory;
            $amount = $transaction->wallet->decimal_places  == 2 ? $transaction->amount / 100 : $transaction->amount / 1000;
            $promissory->collected_amount -= $amount;

            if($promissory->collected_amount != $promissory->total_amount) {
                $promissory->status = 'pending';
            }

            $promissory->save();
        }
        // delete transaction
        $transaction->delete();
        // refresh the wallet
        $transaction->payable->wallet->refreshBalance();
        // after deletion balance
        $balance = abs($transaction->payable->wallet->balance / 100);
        // return json response
        return response()->json(['flag' => 'success', 'balance' => $balance, 'transaction' => $transaction]);
    }

    /**
     * @description  : Get the available balance to be transferred to management
     * @return float|int
     */
    public function managementBalance()
    {

        // fetch reservations
        $reservations = Reservation::where('team_id', auth()->user()->current_team_id)->pluck('id')->toArray();

        $types = ['withdraw','deposit'] ;

        foreach ($types as $type) {

            switch ($type) {

                case 'withdraw':
                    $withdraw_amount = Transaction::whereHasMorph('payable', Team::class, function ($t) {
                        $t->where('payable_id', auth()->user()->current_team_id)->where('is_public', 1)
                            ->where('type', 'withdraw');
                    })
                                            ->orWhereHasMorph('payable', Reservation::class, function ($t) use ($reservations) {
                                                $t->whereIn('payable_id', $reservations)->where('is_public', 1)
                                                    ->where('type', 'withdraw');
                                            })
                                            ->sum('amount');
                    break;

                case 'deposit':
                    $deposit_amount = Transaction::whereHasMorph('payable', Team::class, function ($t) {
                        $t->where('payable_id', auth()->user()->current_team_id)->where('is_public', 1)
                            ->where('type', 'deposit');
                    })
                                        ->orWhereHasMorph('payable', Reservation::class, function ($t) use ($reservations) {
                                            $t->whereIn('payable_id', $reservations)->where('is_public', 1)
                                                ->where('type', 'deposit');
                                        })
                                        ->sum('amount');
                    break;
            }
        }

        $final_withdraw_amount = abs($withdraw_amount);
        $final_deposit_amount = abs($deposit_amount);

        $balance = ($final_deposit_amount - $final_withdraw_amount) / 100 ;

        return $balance ;


    }



    public function finanacialManagementExcel(NovaRequest $request)
    {


        $transactions = $request->get('transactions');
        $transactionsType = $request->get('type') ;
        $transactionsType == 'deposit' ? $file_name  = __('Financial Management Deposits') : $file_name = __('Financial Management Withdraw');


        $pure_array = array();
        foreach ($transactions as $transaction) {
            $transaction = (object) $transaction;
            $data[__('Transaction Number')]         = $transaction->number;
            $data[__('Reservation Number')]         = $transaction->payable_type == 'App\Team' ? '-' : $transaction->reservation['number'] ;
            $data[__('Unit Number')]                = $transaction->payable_type == 'App\Team' ? '-' : $transaction->reservation['unit']['unit_number'] ;
            $data[__('Amount')]                     = number_format(abs($transaction->amount / 100), 2)  ;
            if($transaction->type == 'withdraw') {
                $data[__('Exchange to')]            = isset($transaction->meta['from']) ? $transaction->meta['from'] : '-'  ;
            } else {
                $data[__('Received From')]          = isset($transaction->meta['from']) ? $transaction->meta['from'] : '-'  ;
            }

            $data[__('For')]                 = isset($transaction->meta['statement']) ? $transaction->meta['statement'] : '-' ;
            $data[__('Transaction Date')]                 = isset($transaction->meta['date']) ? $transaction->meta['date'] : '-' ;
            $data[__('Payment Method')]             = isset($transaction->meta['payment_type']) ? __(ucfirst($transaction->meta['payment_type'])) : '-' ;
            $data[__('Reference Number')]          = isset($transaction->meta['reference']) ? __(ucfirst($transaction->meta['reference'])) : '-' ;
            $data[__('Employee')]                   = isset($transaction->meta['employee']) ? __(ucfirst($transaction->meta['employee'])) : '-' ;

            // Push our variables to the pure array
            $pure_array [] = $data ;
        }
        return response()->json([
            'status' => 'success' ,
            'data' => $pure_array ,
            'filename' => $file_name
        ]);


    }


    public function getTransactions(Request $request)
    {


        // Query String Filters
        $requestType = $request->get('request-type');
        $type = $request->get('type');

        $dateFrom = $request->get('dateFrom');
        $dateTo = $request->get('dateTo');


        $rnum = $request->get('re-num');
        $tnum = $request->get('tr-num');
        $paymentType = $request->get('payment-type');
        $transactionTerm = $request->get('tr-term');

        $cash_transactions = [];
        $bank_transfer_transactions = [];
        $mada_transactions = [];
        $credit_card_transactions = [];

        $reservations_ids = Reservation::where('team_id', auth()->user()->current_team_id)
            ->whereNull('deleted_at')
            ->pluck('id')
            ->toArray();


        // $tr = Transaction::whereIntegerInRaw('payable_id' , $reservations_ids)
        //     ->where('payable_type' , Reservation::class)
        //     ->where('amount', '!=' , 0)
        //     ->where('type' , '=' , 'withdraw')
        //     ->where('is_public' , true)
        //     ->whereNull('deleted_at')
        //     ->get()
        //     ->take(10);
        // dd($tr);
        // dd($reservations_ids);
        // $reservations_ids = DB::table('reservations')->select('id')
        //             ->where('team_id', auth()->user()->current_team_id)
        //             ->whereNull('deleted_at')
        //             ->pluck('id')->toArray();
        $team_id = auth()->user()->current_team_id;

        if($type == 'withdraw') {
            $transactions = QueryBuilder::for(Transaction::class)
            ->with(['payable' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    Reservation::class => ['unit', 'creator', 'customer', 'customer.nationality', 'customer.highlight'],
                ]);
            }])
            ->with(['wallet','creator','last_user_update'])
            ->allowedFilters([
                AllowedFilter::scope('by_number'),
                AllowedFilter::scope('by_reservation_number'),
                AllowedFilter::scope('by_customer_id_number'),
                AllowedFilter::scope('by_unit_number'),
                AllowedFilter::scope('by_creator'),
                AllowedFilter::scope('by_payment_type'),
                AllowedFilter::scope('by_date_from'),
                AllowedFilter::scope('by_date_to'),
                AllowedFilter::scope('by_term')
            ])
            ->allowedSorts([
                    'id',
                    '-id',
                    'created_at',
                    '-created_at'
            ])
            ->whereHasMorphIn('payable', ['App\\Team','App\\Reservation'], function ($query, $modelType) use ($reservations_ids, $team_id, $type) {

                if ($modelType == 'App\\Team') {
                    $query->where('payable_type', 'App\\Team')
                    ->where('payable_id', $team_id);
                }

                if ($modelType === 'App\\Reservation') {
                    $query->where('payable_type', 'App\Reservation')
                    ->whereIntegerInRaw('payable_id', $reservations_ids);
                }
            })
            ->where('amount', '!=', 0)
            ->where('type', '=', $type)
            ->where('is_public', true)
            ->whereNull('deleted_at')
            ->orderByDesc('number')
            ->simplePaginate(20);
        } else {
            $transactions = QueryBuilder::for(Transaction::class)
            ->with(['payable' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    Reservation::class => ['unit', 'creator', 'customer', 'customer.nationality', 'customer.highlight'],
                ]);
            }])
            ->with(['wallet','creator','last_user_update'])
            ->allowedFilters([
                AllowedFilter::scope('by_number'),
                AllowedFilter::scope('by_reservation_number'),
                AllowedFilter::scope('by_customer_id_number'),
                AllowedFilter::scope('by_unit_number'),
                AllowedFilter::scope('by_creator'),
                AllowedFilter::scope('by_payment_type'),
                AllowedFilter::scope('by_date_from'),
                AllowedFilter::scope('by_date_to'),
                AllowedFilter::scope('by_term')
            ])
            ->allowedSorts([
                    'id',
                    '-id',
                    'created_at',
                    '-created_at'
            ])
            ->whereHasMorphIn('payable', ['App\\Team','App\\Reservation'], function ($query, $modelType) use ($reservations_ids, $team_id, $type) {

                if ($modelType == 'App\\Team') {
                    $query->where('payable_type', 'App\\Team')
                    ->where('payable_id', $team_id)
                    ->where('amount', '!=', 0)
                    ->where('type', '=', $type)
                    ->where('is_public', true)
                    ->whereNull('deleted_at');
                }

                if ($modelType === 'App\\Reservation') {
                    $query->where('payable_type', 'App\Reservation')
                    ->whereIntegerInRaw('payable_id', $reservations_ids)
                    ->where('amount', '!=', 0)
                    ->where('type', '=', $type)
                    ->where('is_public', true)
                    // ->with('unit','customer','customer.nationality','customer.highlight')
                    ->whereNull('deleted_at');
                }
            })
            ->orderByDesc('number')
            ->simplePaginate(20);
        }


        return  FinancialResource::collection($transactions);
        return response()->json($transactions);
        //             $transactions = Transaction::with('reservation.unit')
        //                 ->where('type' , $type)
        //                 ->where('is_public' , 1)
        //                 ->when($dateFrom != 'null' , function($t) use($dateFrom){
        //                     $from = Carbon::parse($dateFrom)->format('Y-m-d H:i');
        //                     $t->where('meta->date' , '>=' , $from);
        //                 })->when($dateTo != 'null' , function($t) use($dateTo){
        //                     $to = Carbon::parse($dateTo)->format('Y-m-d H:i');
        //                     $t->where('meta->date' , '<=' , $to);
        //                 })
        //                 ->when(isset($rnum) , function($t) use($rnum){
        //                     $t->whereHasMorph('payable' , [ Reservation::class] , function($query,$type) use($rnum){
        //                         $query->where('number' , $rnum);
        //                     });
        // //                    $t->whereHas('reservation' , function($r) use ($rnum){
        // //                        $r->where('number' , $rnum);
        // //                    });
        //                 })
        //                 ->when(isset($tnum) , function($t) use($tnum){
        //                     $t->where('number' , $tnum);
        //                 })
        //                 ->when(isset($paymentType) , function($t) use($paymentType){
        //                     $t->where('meta->payment_type' , $paymentType);
        //                 })->when(isset($transactionTerm) , function($t) use($transactionTerm){
        //                     $t->where('meta->type' , $transactionTerm);
        //                 })

        //                 ->whereHasMorph('payable', [Team::class, Reservation::class], function ($query, $type) use($reservations_ids,$paymentType,$transactionTerm){
        //                     if ($type === Team::class) {
        //                         $query->where('payable_id', auth()->user()->current_team_id);
        //                     }

        //                     if ($type === Reservation::class) {
        //                         $query->whereIn('payable_id', $reservations_ids);
        //                     }
        //                 })
        //                 ->orderBy('created_at' , 'desc')
        //                 ->whereNull('deleted_at')
        //                 ->get();



        if(count($transactions)) {
            foreach ($transactions as $transaction) {

                if (isset($transaction->meta['payment_type'])) {
                    // switch the payment type key
                    switch ($transaction->meta['payment_type']) {

                        case 'cash':
                            $cash_transactions [] =  abs($transaction->amount / 100);
                            break;
                        case 'bank-transfer':
                            $bank_transfer_transactions [] = abs($transaction->amount / 100);
                            break;
                        case 'mada':
                            $mada_transactions [] = abs($transaction->amount / 100);
                            break;
                        case 'credit':
                            $credit_card_transactions [] = abs($transaction->amount / 100);
                            break;
                        default:
                            break;

                    }
                }

            }

            if($requestType == 'paginated') {
                return response()->json([
                    'status' => 'data-found',
                    'transactions' => (new CustomPagination($transactions))->paginate(20),
                    'cash' => number_format(array_sum($cash_transactions), 2),
                    'bank_transfer' => number_format(array_sum($bank_transfer_transactions), 2),
                    'mada' => number_format(array_sum($mada_transactions), 2),
                    'credit_card' => number_format(array_sum($credit_card_transactions), 2),
                    'total' => $transactions->sum('amount') / 100,
                ], 200) ;
            } else {
                return response()->json(['all_transactions' => $transactions]);
            }


        } else {
            return response()->json(['status' => 'data-not-found']);
        }



    }

    public function getTerms(Request $request)
    {

        return response()->json(Term::where('team_id', auth()->user()->current_team_id)->where('type', $request->get('type'))->whereNull('deleted_at')->where('status', 1)->get());

    }


    /**
     * Add Deposit transaction to fulfill promissory
     * @param Request $request
     * @return void
     */
    public function addPromissoryTransaction(Request $request)
    {
        $due_amount = $request->get('amount') ;
        $max_amount = $request->get('max_amount');

        $status = 'pending';
        if($due_amount  ==  $max_amount) {
            $status = 'fulfilled';
        }

        $meta = $request->get('meta');

        $current_time = date('H:i');
        $incomingDate = $meta['date'];
        if(!$this->checkDate($incomingDate)) {
            return response()->json('invalid-date');
        }
        $combinedTransactionDate = date('Y-m-d H:i', strtotime("$incomingDate $current_time"));
        $newMeta = [
          "category" => $request->meta['category'],
          "statement" => $request->meta['statement'],
          "type" => $request->meta['type'],
          "payment_type" => $request->meta['payment_type'],
          "note" => $request->meta['note'],
          "reference" =>  $request->meta['reference'],
          "date" => $combinedTransactionDate,
          "from" => $request->meta['from'] ,
          "employee" => $request->meta['employee']
        ];


        // add transaction
        $reservation = Reservation::find($request->get('reservation_id'));

        $reservation->wallet->depositFloat($due_amount, $newMeta, true, true);

        // update promissory
        $promissory  = Promissory::find($request->get('promissory_id'));
        $promissory->collected_amount += $due_amount;
        $promissory->status = $status;
        $promissory->save();

        return response()->json('success');

    }


    public function hashTransactionId(Request $request)
    {
        return Hashids::encode($request->get('id')) ;
    }

    public function getVatSetting(Request $request)
    {
        return response()->json(['vat' => Settings::get('tax') ? Settings::get('tax') : 0 ]);
    }

    public function processZatcaEInvoice($team_id, Request $request)
    {

        $invoice_type =  $request->get('invoice_type');
        $invoice_sub_type =  $request->get('invoice_sub_type');
        //reservation invoices ids or service log ids
        $id = $request->get('ref_id');
        //credit note ids or service log note ids
        //$child_id = $request->get('child_id');
        $integration = Integration::findByKeyAndTeamId('ZatcaPhaseTwo', auth()->user()->current_team_id)->first();

        $org = auth()->user()->getSupplierEGS();

        if(!$integration) {
            return response()->json([
                'message' => "Failed",
                'success' => false
            ], 500);
        }

        if($org == null) {
            return response()->json([
                'message' => __('Tax Number is required')
            ], 500);
        }

        $credential = (object) json_decode($integration->values);

        $model = $request->get('model');

        $invoice_config = (object) array(
            'invoice_type' => $invoice_type,
            'invoice_sub_type' => $invoice_sub_type,
            'org' => $org
        );

        $obj = "";

        if($model == "Reservation") {

            $obj = ReservationInvoice::with('reservation', 'reservation.unit', 'reservation.customer', 'reservation.creator', 'reservation.comments')->findOrFail($id);

            //check if group reservation for company or single for individual
            if($obj->is_group_reservation == 1) {
                $invoice_config->invoice_type = 'tax invoice';
            }

            $zatcaInvoice = new Invoice(
                $credential->username,
                $credential->password,
                $invoice_config->invoice_type,
                $invoice_config->invoice_sub_type,
                $invoice_config->org
            );

            $zatcaInvoice->seedInvoice($obj);

        }


        if($model == "ServiceLog") {

            $obj = ServiceLogNote::with('service_log')->findOrFail($id);

            $zatcaInvoice = new Invoice(
                $credential->username,
                $credential->password,
                $invoice_config->invoice_type,
                $invoice_config->invoice_sub_type,
                $invoice_config->org
            );

            $zatcaInvoice->seedInvoicePOS($obj);

        }

        $compliant_invoice = $zatcaInvoice->getCompliantInvoice();

        if(!isset($compliant_invoice->data->base64_signed_invoice_string) &&
           !isset($compliant_invoice->data->invoice_hash) &&
           !isset($compliant_invoice->data->uuid)) {
            activity()->performedOn((new ReservationInvoice()))->log(__('Invoice :ID push to zatca has been failed', ['id' => $id]));
            return response()->json([
                'message' => "Failed",
                'success' => false
            ], 500);
        }

        $response = $zatcaInvoice->reportInvoice($compliant_invoice);

        if(!isset($response->status) || $response->status !== 200 || $response == null) {
            return response()->json([
                'message' => "Failed",
                'success' => false
            ], 500);
        }

        //check if the incoming sub type is credit note then switch obj to credit note
        if($model == "Reservation" && $invoice_config->invoice_sub_type == 'credit note') {
            $obj = InvoiceCreditNote::where("reservation_invoice_id", $id)->first();
        }

        //presist invoice and invoice number
        $response->data->invoice = $compliant_invoice->data->base64_signed_invoice_string;
        $response->data->invoice_number = $compliant_invoice->data->invoice_number;
        $response->data->qrcode = $compliant_invoice->data->qrcode;

        if(isset($response->data->reportingStatus) && $response->data->reportingStatus == "REPORTED") {
            if($model == "Reservation") {
                $obj->is_reported_to_zatca = $response->data;
            } elseif($model == "ServiceLog") {
                $obj->payload = $response->data;
            }
        }

        if(isset($response->data->clearanceStatus) && $response->data->clearanceStatus == "CLEARED") {
            if($model == "Reservation") {
                $obj->is_reported_to_zatca = $response->data;
            } elseif($model == "ServiceLog") {
                $obj->payload = $response->data;
            }
        }

        //presist obj
        $obj->save();

        activity()->performedOn((new $obj()))->log(__('Team Id :TEAM has reported Invoice#:INVOICE to zatca successfully', ['team' => auth()->user()->current_team_id, 'invoice' => '']));

        //check for reservation credit notes
        if($model == "Reservation" && $invoice_config->invoice_sub_type !== 'credit note') {

            $invoice_credit_note = InvoiceCreditNote::where("reservation_invoice_id", $id)->first();

            if($invoice_credit_note && $invoice_credit_note->is_reported_to_zatca == null) {
                //transform invoice to credit note
                $zatcaInvoice->setCanceledInvoiceBillingReferenceId($obj->number);
                $zatcaInvoice->setPaymentInstruction("Returned");
                $zatcaInvoice->setInvoiceSubType("credit note");
                $compliant_invoice = $zatcaInvoice->getCompliantInvoice();

                if(!isset($compliant_invoice->data->base64_signed_invoice_string) &&
                !isset($compliant_invoice->data->invoice_hash) &&
                !isset($compliant_invoice->data->uuid)) {
                    activity()->performedOn((new ReservationInvoice()))->log(__('Invoice :ID push to zatca has been failed', ['id' => $id]));
                    return response()->json([
                        'message' => "Failed",
                        'success' => false
                    ], 500);
                }

                $response_credit_note = $zatcaInvoice->reportInvoice($compliant_invoice);

                //presist invoice and invoice number
                $response_credit_note->data->invoice = $compliant_invoice->data->base64_signed_invoice_string;
                $response_credit_note->data->invoice_number = $compliant_invoice->data->invoice_number;
                $response_credit_note->data->qrcode = $compliant_invoice->data->qrcode;

                if(isset($response_credit_note->data->reportingStatus) && $response_credit_note->data->reportingStatus == "REPORTED") {
                    $invoice_credit_note->is_reported_to_zatca = $response_credit_note->data;
                }

                if(isset($response_credit_note->data->clearanceStatus) && $response_credit_note->data->clearanceStatus == "CLEARED") {
                    $invoice_credit_note->is_reported_to_zatca = $response_credit_note->data;
                }
                activity()->performedOn((new InvoiceCreditNote()))->log(__('Invoice :ID credit note has been pushed to zatca successfully', ['id' => $id]));
                $invoice_credit_note->save();
            }
            //return response()->json($invoice_credit_note);
        }
        //ammend credit note with outgoing response
        // if($invoice_credit_note->is_reported_to_zatca) {
        //     $response->credit_note = $invoice_credit_note->is_reported_to_zatca;
        // }
        return response()->json($response->data, 200);
    }


    public function getAllTeams(Request $request)
    {
        $teams = Team::whereNull('deleted_at')
        ->where('payment_preprocessor', 'hyperpay')
        // ->where('enabled_payment_link',true)
        ->orderBy('name')->get();
        $data = [];
        if(count($teams)) {
            foreach ($teams as $team) {
                $data [] = [
                    'id' => $team->id,
                    'name' => $team->name,
                    'bank_iban_number' => $team->bank_iban_number,
                    'transfer_option' => $team->transfer_option
                ];
            }
        }
        return response()->json($data);
    }

    public function getPaymentServiceInvoices(Request $request)
    {

        $query_scope = request()->header('x-query');
        $invoices = QueryBuilder::for(OnlinePaymentServiceInvoice::class)
        ->when($query_scope == 'team-scope', function ($q) {
            $q->where('team_id', request()->header('x-team'));
        })
        ->allowedFilters([
            AllowedFilter::scope('by_team'),
            AllowedFilter::scope('by_date_from'),
            AllowedFilter::scope('by_date_to'),
        ])
        ->orderByDesc('created_at')
        ->paginate(request()->get('per_page'));
        return $invoices;
    }

    public function getReservationsNumbers(Request $request)
    {
        $filtered = [];
        $ids = $request->get('ids');
        $res_numbers = Reservation::whereIn('id', $ids)->get(['id','number']);
        if(count($res_numbers)){
            $ids_flipped = array_flip($ids);
            foreach($res_numbers as $res){
                if(isset($ids_flipped[$res->id])){
                    $filtered [$res->id] = $res->number;
                }
            }
        }
        return response()->json($filtered);
    }

    public function deletePromissory(Request $request){
        $promissory = Promissory::find($request->id);
        try {
            $promissory->delete();
            return response()->json([
                'success' => true,
                'message' => 'promissory deleted'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
        
    }
}
