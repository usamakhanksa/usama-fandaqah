<?php

namespace App\Nova\Lenses;

use App\Nova\Filters\EmployeeFilter;
use App\Nova\Filters\ReservationNumberFilter;
use App\Nova\Filters\TransactionDateRange;
use App\Nova\Filters\TransactionNumber;
use App\Nova\Filters\TransactionType;
use App\Reservation;
use App\Team;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\LensRequest;
use Laravel\Nova\Lenses\Lens;

class FinancialManagement extends Lens
{

    /**
     * Get the query builder / paginator for the lens.
     *
     * @param  \Laravel\Nova\Http\Requests\LensRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return mixed
     */
    public static function query(LensRequest $request, $query)
    {


        $query = $request->withFilters($query);
        $reservations = Reservation::where('team_id', auth()->user()->current_team_id)->pluck('id')->toArray();

        // this is injected as a prop in route props
        $transactionType = $request->get('transactionType');
        $termId = $request->get('termId');

        if($termId != 0 ){
            $query =  $request->withFilters(
                $query->where('type' , '=' , $transactionType)
                        ->where('is_public' ,1)
                            ->whereHasMorph('payable', Team::class, function ($query) use($transactionType,$termId) {
                                 $query->where('payable_id', auth()->user()->current_team_id)
                                        ->where('type' , '=' , $transactionType)
                                        ->where('is_public' , 1)
                                        ->where('meta->type' , $termId);
                            })
                            ->orWhereHasMorph('payable', Reservation::class, function ($query) use ($reservations,$transactionType,$termId) {
                                    $query
                                        ->where('type' , $transactionType)
                                        ->where('meta->type' , $termId)
                                        ->where('is_public' , 1)
                                        ->whereIn('payable_id', $reservations);
                            })
            );

        }else{

            // zero
            $query = $request->withFilters(
                    $query->where('type' , '=' , $transactionType)
                        ->where('is_public' ,1)
                            ->whereHasMorph('payable', Team::class, function ($query) use($transactionType,$termId) {
                                $query->where('payable_id', auth()->user()->current_team_id)
                                    ->where('type' , '=' , $transactionType)
                                    ->where('is_public' , 1);

                            })
                            ->orWhereHasMorph('payable', Reservation::class, function ($query) use ($reservations,$transactionType,$termId) {
                                $query
                                    ->where('type' , $transactionType)
                                    ->where('is_public' , 1)
                                    ->whereIn('payable_id', $reservations);
                            })
            );
        }

        $query = $request->withFilters($query);
//        $query->orderByDesc('number');
//        dd($query->toSql());
        return $query->orderByDesc('created_at') ;
    }

    /**
     * Get the fields available to the lens.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        $label = \request('transactionType') == 'withdraw' ? __('Exchange to'): __('Received From');
        return [
//            ID::make('ID', 'id')->sortable(),
            Text::make(__('Transaction Number') , 'number')->onlyOnIndex() ,
//            BelongsTo::make(__('Reservation Num') , 'reservation'  , \App\Nova\Reservation::class),
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

                    if($this->reservation->unit){

                        return  $this->reservation->unit->unit_number ;
                    }else{
                        return '-';
                    }
                }else{
                    return '—';
                }
            }),

            Currency::make(__('Amount') , 'amount')->displayUsing(function($amount){
                return  abs($amount  / 100 ) .' '. __('SAR')  ;
            }),

            Text::make($label, 'meta')->displayUsing(function ($meta) {
                if (isset($meta['from']))
                    return $meta['from'];

                return '-';
            }),

            Text::make(__('For') , 'meta')->displayUsing(function($meta){
                if(isset($meta['statement']))
                    return $meta['statement'] ;

                return '-' ;
            }),


            Text::make(__('Date Receipt') , 'meta->date'),


            Text::make(__('Payment Method') , 'meta')->displayUsing(function($meta){
                if(isset($meta['payment_type']))
                    return __(ucfirst($meta['payment_type']));

                return  __('Nothing')  ;


            }),
            Text::make(__('Reference Number'), 'meta')->displayUsing(function ($meta) {
                if (isset($meta['reference']))
                    return __($meta['reference']);

                return __('Nothing');


            }),
            Text::make(__('Employee') , 'meta')->displayUsing(function($meta){
//                $transaction = $this ;
//                if($transaction->reservation){
//                    return $transaction->reservation->creator->name ;
//                }
                if(isset($meta['employee']))
                    return $meta['employee'] ;

                return '-' ;
            }),

        ];
    }

    /**
     * Get the filters available for the lens.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [

            new TransactionNumber,
            new EmployeeFilter ,
            new ReservationNumberFilter,
            new TransactionDateRange,
            new TransactionType() ,


        ];
    }

    /**
     * Get the actions available on the lens.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return parent::actions($request);
    }

    /**
     * Get the URI key for the lens.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'financial-management';
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
}
