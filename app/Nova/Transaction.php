<?php

namespace App\Nova;
use App\Scopes\TeamScope;
use App\Nova\Lenses\FinancialManagement;
use App\Nova\Lenses\MonthlyTotalReport;
use App\Reservation;
use App\Team;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Database\Eloquent\Builder;

class Transaction extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Transaction';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * Remove Zero Transactions rubbish from database
     * @Todo : Remember to retrieve also here all transactions that don't relate to reservation
     * @param NovaRequest $request
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {

        if(auth()->user()->hasPermissionTo('view financial')) {
        $filters = json_decode(base64_decode(\request('filters')), true);

        collect($filters)->each(function ($filter) use ($request, $query) {
            if (!is_null($filter['value']) and !empty($filter['value'])) {
                (new $filter['class'])->apply($request, $query, $filter['value']);
            }
        });


        $reservations = Reservation::where('team_id', auth()->user()->current_team_id)->pluck('id')->toArray();
        $current_team_id = auth()->user()->current_team_id ;
        $transactionType = $request->get('transactionType') ;
        $termId = $request->get('termId');

        if($transactionType == 'deposit' || $transactionType == 'withdraw'){
             if($termId == 0 ){
                 $query = $query->where('amount', '!=' , 0)
                    ->where('type' , '=' , $transactionType)
                    ->where('is_public' ,1)
                    ->whereHasMorph('payable', Team::class, function ($query) {
                        $query->where('payable_id', auth()->user()->current_team_id);
                    })
                    ->orWhereHasMorph('payable', Reservation::class, function ($query) use ($reservations,$transactionType) {
                        $query
                            ->where('type' , $transactionType)
                            ->where('is_public' , 1)
                            ->whereIn('payable_id', $reservations);
                    });
            }else{
                 $query = $query->where('amount', '!=' , 0)
                        ->whereHasMorph('payable', Team::class, function ($query) use($transactionType,$termId) {
                            $query->where('payable_id', auth()->user()->current_team_id)
                                ->where('type' , '=' , $transactionType)
                                ->where('is_public' , 1)
                                ->where('meta->type' , $termId);
                        })
                        ->orWhereHasMorph('payable', Reservation::class, function ($query) use ($reservations,$transactionType,$termId) {
                            $query
                                ->whereIn('payable_id', $reservations)
                                ->where('type' , '=' , $transactionType)
                                ->where('is_public' , 1)
                                ->where('meta->type' , $termId);
                        });
            }
        }

        $filters = json_decode(base64_decode(\request('filters')), true);

        collect($filters)->each(function ($filter) use ($request, $query) {
            if (!is_null($filter['value']) and !empty($filter['value'])) {
                (new $filter['class'])->apply($request, $query, $filter['value']);
            }
        });

        return $query;
        }
        abort(404);
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {

        $label = \request('transaction_type') == 'withdraw' ? __('Exchange to'): __('Received From');
        return [
//            ID::make()->sortable(),
            Text::make(__('Transaction Number') , 'number')->onlyOnIndex() ,
            Text::make(__('Reservation Num') , 'payable_type')->displayUsing(function($payable_type)use($request){
                if($payable_type == 'App\Reservation'){
                    return '<a href="" data-attr-id='.$this->payable_id.' class="no-underline dim text-primary font-bold view_reservation">' .
                                        $this->reservation->number . '</a>';
                }else{
                    return '<span>—</span>';
                }
            })->asHtml(),

            Text::make(__('Unit Number') , 'payable_type')->displayUsing(function($payable_type)use($request){
                if($payable_type == 'App\Reservation'){

                    return  $this->reservation->unit ? $this->reservation->unit->unit_number : '';
                }else{
                    return '—';
                }
            }),

            Text::make($label, 'meta')->displayUsing(function($meta){


                    if(isset($meta['from']))
                        return $meta['from'] ;

                    return __('Nothing');
            }),

//            Text::make($this->type == 'deposit' ? __('Received From') : __('Exchange to') , 'meta')->displayUsing(function(){
//                if($this->type == 'deposit'){
//                    $customer_name = $this->reservation->customer['name'] ;
//                    return $customer_name ;
//                }else{
//                    if(array_key_exists('from' , $this->meta)){
//                        $exchange_to = $this->meta['from'] ;
//                        return $exchange_to ;
//                    }else{
//                       $exchange_to = __('Nothing')  ;
//                       return $exchange_to ;
//                    }
//
//                }
//
//            }),

            Currency::make(__('Amount') , 'amount')->displayUsing(function($amount){
              return  abs($amount  / 100 ) .' '. __('SAR')  ;
            }),

//            Text::make(__('For') , 'meta')->displayUsing(function($meta){
//                $transaction  = $this ;
//                return $meta['statement'] . ' - ' . __('The Unit')  . ' - ' . $transaction->reservation->unit['unit_number']  ;
//            }),


            Text::make(__('Date Receipt') , 'meta->date'),


            Text::make(__('Payment Method') , 'payment_method'),
            Text::make(__('Employee Name'),'creator->name'),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [



        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {

        return [
            new Filters\TransactionNumber,
            new Filters\EmployeeFilter ,
            new Filters\IdNumberFilter,
            new Filters\ReservationNumberFilter,
            new Filters\TransactionDateRange,
            new Filters\TransactionType() ,
            new Filters\UnitNumber,
//            (new Filters\DepositTransactionType)->withMeta(['type' => 'deposit']),
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [
            new FinancialManagement()
//            new MonthlyTotalReport()
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [

        ];
    }

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }
    public function authorizedToDelete(Request $request)
    {
        return auth()->user()->hasPermissionTo('delete financial');
    }

    public function authorizedToForceDelete(Request $request)
    {
        return auth()->user()->hasPermissionTo('delete financial');
    }

    public function authorizedToUpdate(Request $request)
    {
        return auth()->user()->hasPermissionTo('edit financial');
    }

    public static function detailQuery(NovaRequest $request, $query)
    {
        abort(404); 
        return $query;
    }

    public static function scoutQuery(NovaRequest $request, $query)
    {
        abort(404); 
        return $query;
    }


}
