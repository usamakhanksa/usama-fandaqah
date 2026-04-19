<?php

namespace App\Http\Controllers\Api;

use App\Events\ReservationDeleted;
use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Http\Resources\ServicesCategoryResource;
use App\Http\Resources\TransactionResource;
use App\Reservation;
use App\Service;
use App\ServicesCategory;
use App\Team;
use App\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TransactionController extends Controller
{
    /**
    * get transaction index method 
    * 
    * @return App\Http\Resources\TransactionResource 
    */
    public function store(Request $request, $type)
    {
        $model = Reservation::find($request->reservation_id);

        if ($type === 'service') {
            $model->wallet->refreshBalance();
            $model->forceWithdrawFloat($request->amount, $request->meta, true, false);
        } elseif ($type === 'withdraw') {
            $model->wallet->refreshBalance();
            $model->forceWithdrawFloat($request->amount, $request->meta, true, true);
        } elseif ($type === 'deposit') {
            $model->wallet->refreshBalance();
            $model->depositFloat($request->amount, $request->meta, true, true);
        }

        return response()->json(['data' => [
            'status' => true
        ]]);
    } 

    /**
    * get transaction index method 
    * 
    * @return App\Http\Resources\TransactionResource 
    */
    public function index(Request $request)
    { 
        $transactionType = $request->get('type');
        $current_team_id = $request->get('current_team_id');
        $reservation_ids = Reservation::where('team_id', $current_team_id)->pluck('id')->toArray();

        $transactions = QueryBuilder::for(Transaction::class)
            ->allowedIncludes(['customer'])
            ->where('amount', '!=' , 0)
            ->where('type' , '=' , $transactionType)
            ->where('is_public' ,1)
            ->whereHasMorph('payable', Team::class, function ($query) use($current_team_id){
                $query->where('payable_id', $current_team_id);
            })
            ->orWhereHasMorph('payable', Reservation::class, function ($query) use ($reservation_ids,$transactionType) {
                $query
                    ->where('type' , $transactionType)
                    ->where('is_public' , 1)
                    ->whereIn('payable_id', $reservation_ids);
            })
            ->orderBy('id', 'desc')
            ->allowedFilters([
                AllowedFilter::scope('by_number'),
                AllowedFilter::scope('by_creator'),
                AllowedFilter::scope('by_customer_id_number'),
                AllowedFilter::scope('by_reservation_number'),
                AllowedFilter::scope('by_transaction_type'),
                AllowedFilter::scope('by_unit_number'),
                AllowedFilter::scope('by_date_range'),
                AllowedFilter::scope('by_statement'),
            ])
            ->defaultSort('-meta->date')
            ->paginate($request->get('per_page', 30));

		return  TransactionResource::collection($transactions)->additional([
			'meta' => [
                'statistics' => $this->getTotals($reservation_ids, $current_team_id, $transactionType)
            ]
        ]);
	}

    public function getTotals($reservation_ids, $current_team_id, $transactionType)
    {
        $transactions = QueryBuilder::for(Transaction::class)
            ->allowedIncludes(['customer'])
            ->where('amount', '!=' , 0)
            ->where('type' , '=' , $transactionType)
            ->where('is_public' ,1)
            ->whereHasMorph('payable', Team::class, function ($query) use($current_team_id){
                $query->where('payable_id', $current_team_id);
            })
            ->orWhereHasMorph('payable', Reservation::class, function ($query) use ($reservation_ids,$transactionType) {
                $query
                    ->where('type' , $transactionType)
                    ->where('is_public' , 1)
                    ->whereIn('payable_id', $reservation_ids);
            })
            ->allowedFilters([
                AllowedFilter::scope('by_number'),
                AllowedFilter::scope('by_creator'),
                AllowedFilter::scope('by_customer_id_number'),
                AllowedFilter::scope('by_reservation_number'),
                AllowedFilter::scope('by_transaction_type'),
                AllowedFilter::scope('by_unit_number'),
                AllowedFilter::scope('by_date_range'),
                AllowedFilter::scope('by_statement'),
            ])
            ->get();

        $total_cash = [] ;
        $total_bank_cash = [] ;
        $total_mada = [] ;
        $total_credit = [] ;

        foreach ($transactions as $transaction){
            if(isset($transaction->meta['payment_type']) && $transaction->meta['payment_type'] == 'cash' ){
                    $total_cash [] = abs($transaction->amount / 100) ;
            }
            elseif(isset($transaction->meta['payment_type']) && $transaction->meta['payment_type'] == 'bank-transfer' ){
                $total_bank_cash [] = abs($transaction->amount / 100) ;
            }
            elseif(isset($transaction->meta['payment_type']) && $transaction->meta['payment_type'] == 'mada' ){
                $total_mada [] = abs($transaction->amount / 100) ;
            }
            else{
                $total_credit [] = abs($transaction->amount / 100) ;
            }
        }

        $total_cash_attr = array_sum($total_cash);
        $total_bank_cash_attr = array_sum($total_bank_cash);
        $total_mada_attr = array_sum($total_mada);
        $total_credit_attr = array_sum($total_credit);
        $total_c = $total_cash_attr + $total_bank_cash_attr + $total_mada_attr + $total_credit_attr;

        return [
            'total_cash' => $this->number_format_abs($total_cash_attr),
            'total_bank_transfer_cash' => $this->number_format_abs($total_bank_cash_attr),
            'total_mada' => $this->number_format_abs($total_mada_attr),
            'total_credit' => $this->number_format_abs($total_credit_attr),
            'total' => $this->number_format_abs($total_c),
            'transactions_query' => $transactions->pluck('id')
        ];
    }

    protected function number_format_abs($number)
    {
        return number_format(floatval($number), 2);
    }

    /**
    * get payment types
    * 
    * @return App\Http\Resources\TransactionResource 
    */
    public function paymentTypes()
    {
        $payment_types = [
            "cash" => __('Cash'),
            "bank-transfer" => __('Bank Transfer'),
            "mada" => __('Mada'),
            "credit" => __('Credit Card'),
        ];
        return response()->json($payment_types);
    }

    /**
     * get service list
     * @return \Illuminate\Http\JsonResponse
     */
    public function servicesList(Request $request)
    {
        $data = QueryBuilder::for(ServicesCategory::class)
            ->allowedFilters([
                // AllowedFilter::scope('status'),
            ])
            ->defaultSort('-order')
            ->where(['status' => true])
            ->allowedSorts('id', 'order')
            ->get();
        return ServicesCategoryResource::collection($data);
    }

    /**
     * delete transaction from reservation
     * @param Request $request
     * @throws \Exception
     */
    public function delete(Transaction $transaction, Request $request)
    {
        $transaction->delete();

        $reservation = Reservation::find($request->reservation_id);
        $reservation->wallet->refreshBalance();
        event(new ReservationDeleted($reservation));

        return response()->json(['data' => [
            'status' => true
        ]]);
    }
}