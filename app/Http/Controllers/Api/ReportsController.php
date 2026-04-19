<?php

namespace App\Http\Controllers\Api;

use App\Customer;
use App\Http\Controllers\Controller;
use App\Http\Resources\CleaningResource;
use App\Http\Resources\GuestResource;
use App\Http\Resources\MaintenanceResource;
use App\Http\Resources\OccupiedResource;
use App\Http\Resources\ReservationTransferResource;
use App\Http\Resources\RevenueTaxResource;
use App\Http\Resources\UnitsMovementResource;
use App\Occupied;
use App\Reservation;
use App\ReservationTransfer;
use App\Team;
use App\Transaction;
use App\UnitCleaning;
use App\UnitMaintenance;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ReportsController extends Controller
{
	/**
	* get transaction index method
    *
	* @return App\Http\Resources\TransactionResource
	*/
	public function unitCleanings(Request $request)
	{
        $data = QueryBuilder::for(UnitCleaning::class)
            ->allowedIncludes(['customer'])
            ->allowedFilters([
                AllowedFilter::exact('number'),
                AllowedFilter::scope('by_creator'),
            ])
            ->defaultSort('-id')
            ->paginate($request->get('per_page', 30));

		return  CleaningResource::collection($data)->additional([
			'meta' => [
            ]
        ]);
	}

    /**
    * get transaction index method
    *
    * @return App\Http\Resources\TransactionResource
    */
    public function unitMaintenance(Request $request)
    {
        $data = QueryBuilder::for(UnitMaintenance::class)
            ->allowedIncludes(['customer'])
            ->allowedFilters([
                AllowedFilter::exact('number'),
                AllowedFilter::scope('by_creator'),
            ])
            ->defaultSort('-id')
            ->paginate($request->get('per_page', 30));

        return  MaintenanceResource::collection($data)->additional([
            'meta' => [
            ]
        ]);
    }

    /**
    * get transaction index method
    *
    * @return App\Http\Resources\TransactionResource
    */
    public function Occupied(Request $request)
    {
        $data = QueryBuilder::for(Occupied::class)
            ->allowedIncludes(['customer'])
            ->defaultSort('-id')
            ->paginate($request->get('per_page', 30));

        return  OccupiedResource::collection($data)->additional([
            'meta' => [
            ]
        ]);
    }


    /**
    * get transaction index method
    *
    * @return App\Http\Resources\TransactionResource
    */
    public function reservationTransfers(Request $request)
    {
        $data = QueryBuilder::for(ReservationTransfer::class)
            ->allowedIncludes(['customer'])
            ->defaultSort('-id')
            ->paginate($request->get('per_page', 30));

        return  ReservationTransferResource::collection($data)->additional([
            'meta' => [
            ]
        ]);
    }

    /**
    * get transaction index method
    *
    * @return App\Http\Resources\TransactionResource
    */
    public function guests(Request $request)
    {
        $data = QueryBuilder::for(Customer::class)
            ->allowedFilters([
                AllowedFilter::scope('by_registration_date'),
                AllowedFilter::scope('by_gender'),
                AllowedFilter::scope('by_phone_number'),
                AllowedFilter::scope('by_id_number'),
            ])
            ->defaultSort('-id')
            ->paginate($request->get('per_page', 30));

        return  GuestResource::collection($data)->additional([
            'meta' => [
            ]
        ]);
    }

    /**
    * get transaction index method
    *
    * @return App\Http\Resources\TransactionResource
    */
    public function unitsMovement(Request $request)
    {
        $data = QueryBuilder::for(Reservation::class)
            ->whereHas('unit')
            ->allowedFilters([
                AllowedFilter::scope('by_number'),
                AllowedFilter::scope('by_unit_number'),
                AllowedFilter::scope('by_customer_id_number'),
                AllowedFilter::scope('by_created_at'),
            ])
            ->defaultSort('-id')
            ->paginate($request->get('per_page', 30));

        return  UnitsMovementResource::collection($data)->additional([
            'meta' => [
            ]
        ]);
    }


    /**
     * Function will handle generating a monthly report for transactions
     */
    public function monthly(Request $request)
    {
        // Generals and Initials
        $current_team_id = $request->current_team_id;
        $month = $request['month'] ;
        $year = $request['year'] ;
        $employee_id = $request['employee_id'] ;
        $total_withdraw = 0;
        $total_deposit = 0;
        $days_arr = [];


        // Month and Year required to proceed , Stop from here can not proceed
        if($month == 0 || $year == 0 ){
            return response()->json([
                'status' => 'invalid_month_or_year'
            ]);
        }

        // fetch all reservations to be used in our morph relation
        $reservations = Reservation::where('team_id', $current_team_id)->pluck('id')->toArray();
        $users = User::where('current_team_id', $current_team_id)->pluck('id')->toArray();

        $days = $this->days_in_month($month,$year);

        // Loop through days
        for($day = 1 ; $day <= $days ; $day++){
            // Forming our date
            $date = date('Y-m-d' , strtotime($year .'-'. $month . '-' . $day)) ;

            // Nice and Elegant Switch case to swap employee id
            switch ($employee_id){
                case !0 :
                    /**
                     * Query Transactions
                     * Why am doing what you see
                     * cause orWhereHasMorph Relation having a critical issue
                     * so i had to separate the logic in to categorized deposit transactions
                     * one for team and one for reservation
                     */
                    $withdraw_team_transaction = Transaction::where('type' , 'withdraw')
                        ->whereHasMorph('payable', Team::class, function ($query) use($employee_id, $current_team_id) {
                            $query->where('payable_id', $current_team_id);
                        })
                        ->whereHas('creator' , function($u) use($employee_id){
                           $u->where('id' , $employee_id);
                        })
                        ->where('is_public' , 1)
                        ->where('meta->date' , 'LIKE' , "%$date%")
                        ->sum('amount');

                    $withdraw_reservation_transaction = Transaction::where('type' , 'withdraw')
                        ->whereHasMorph('payable', Reservation::class, function ($query) use ($reservations , $employee_id) {
                            $query->whereIn('payable_id', $reservations);
                        })
                        ->whereHas('creator' , function($u) use($employee_id){
                            $u->where('id' , $employee_id);
                        })
                        ->where('is_public' , 1)
                        ->where('meta->date' , 'LIKE' , "%$date%")
                        ->sum('amount');
                    $withdraw_transaction = $withdraw_team_transaction + $withdraw_reservation_transaction ;


                    $deposit_team_transaction = Transaction::where('type' , 'deposit')
                        ->whereHasMorph('payable', Team::class, function ($query) use($employee_id, $current_team_id) {
                            $query->where('payable_id', $current_team_id);
                        })
                        ->whereHas('creator' , function($u) use($employee_id){
                            $u->where('id' , $employee_id);
                        })
                        ->where('is_public' , 1)
                        ->where('meta->date' , 'LIKE' , "%$date%")
                        ->sum('amount');

                    $deposit_reservation_transaction = Transaction::where('type' , 'deposit')
                        ->whereHasMorph('payable', Reservation::class, function ($query) use ($reservations , $employee_id) {
                            $query->whereIn('payable_id', $reservations);
                        })
                        ->whereHas('creator' , function($u) use($employee_id){
                            $u->where('id' , $employee_id);
                        })
                        ->where('is_public' , 1)
                        ->where('meta->date' , 'LIKE' , "%$date%")
                        ->sum('amount');

                    $deposit_transaction = $deposit_team_transaction + $deposit_reservation_transaction ;
                break;
                default :
                    /**
                     * Query Transactions
                     * Why am doing what you see
                     * cause orWhereHasMorph Relation having a critical issue
                     * so i had to separate the logic in to categorized deposit transactions
                     * one for team and one for reservation
                     */
                    $withdraw_team_transaction = Transaction::where('type' , 'withdraw')
                        ->whereHasMorph('payable', Team::class, function ($query) use($current_team_id) {
                            $query->where('payable_id', $current_team_id);
                        })
                        ->where('is_public' , 1)
                        ->where('meta->date' , 'LIKE' , "%$date%")
                        ->sum('amount');

                    $withdraw_reservation_transaction = Transaction::where('type' , 'withdraw')
                        ->whereHasMorph('payable', Reservation::class, function ($query) use ($reservations) {
                            $query->whereIn('payable_id', $reservations);
                        })
                        ->where('is_public' , 1)
                        ->where('meta->date' , 'LIKE' , "%$date%")
                        ->sum('amount');

                    $withdraw_transaction = $withdraw_team_transaction + $withdraw_reservation_transaction ;

                    $deposit_team_transaction = Transaction::where('type' , 'deposit')
                        ->where('is_public' , 1)
                        ->whereHasMorph('payable', Team::class, function ($query) use($current_team_id){
                            $query->where('payable_id', $current_team_id);
                        })
                        ->where('meta->date' , 'LIKE' , "%$date%")
                        ->sum('amount');

                    $deposit_reservation_transaction = Transaction::where('type' , 'deposit')
                        ->where('is_public' , 1)
                        ->whereHasMorph('payable', Reservation::class, function ($query) use ($reservations) {
                            $query->whereIn('payable_id', $reservations);
                        })
                        ->where('meta->date' , 'LIKE' , "%$date%")
                        ->sum('amount');

                    $deposit_transaction = $deposit_team_transaction + $deposit_reservation_transaction ;
                    break ;
            }

            // Fill Days array
            $days_arr[$day]['number'] = $day;
            $days_arr[$day]['withdraw'] = $withdraw_transaction/100;
            $days_arr[$day]['deposit'] = $deposit_transaction/100;
            $days_arr[$day]['total'] = $days_arr[$day]['deposit'] + $days_arr[$day]['withdraw'] ;

            // Fill withdraw transactions array
            $total_withdraw += $withdraw_transaction/100;
            // Fill deposit transactions array
            $total_deposit += $deposit_transaction/100;
        }

        // Returning a Json Response
        return response()->json([
            'data' => [
                'days' => $days_arr,
            ],
            'meta' => [
                'total_withdraw' => abs($total_withdraw),
                'total_deposit' => $total_deposit,
                'credit' => $total_deposit -(-$total_withdraw)
            ]
        ], 200);
    }

    protected function days_in_month($month, $year){
        return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
    }


    /**
     * @author Emad rashad
     * @description  : function is used to render data for revenue-tax-fee-report
     * @param NovaRequest $request
     * @return json
     */
    public function revenueTax(Request $request)
    {
        // Request Filters
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');
        $current_team_id = $request->current_team_id;

        $perPage = $request->get('per_page');

        // General Holders
        $total = [];
        $total_rent_price = 0;
        $total_ewa_tax_amount = 0;
        // total rent amount is the vat for now cause there is no service tax till now
        $total_rent_tax_amount = 0;
        $total_service_tax_amount = 0;
        // this will be the sum of total rent amount & total service amount
        $total_vat_tax_amount = 0;

        // when is not working here ( when conditional eloquent )
        if($dateFrom == null || $dateTo == null){
            $reservations = Reservation::where('team_id' , $current_team_id)
                ->with('customer')
                ->orderByDesc('created_at')
                ->paginate($perPage);
        }else{
            $reservations = Reservation::where('team_id' , $current_team_id)
                ->dateTimeBetween($dateFrom, $dateTo)
                ->with('customer')
                ->orderByDesc('created_at')
                ->paginate($perPage);
        }


        if(count($reservations)){
            // Reservations without pagination need for some point of view
            if($dateFrom == null || $dateTo == null){
                $reservationsWithoutPagination = Reservation::where('team_id' , $current_team_id)
                    ->with('customer')
                    ->orderByDesc('created_at')
                    ->get();
            }else{
                $reservationsWithoutPagination = Reservation::where('team_id' , $current_team_id)
                    ->dateTimeBetween($dateFrom, $dateTo)
                    ->with('customer')
                    ->orderByDesc('created_at')
                    ->get();
            }


            foreach ($reservationsWithoutPagination as $reservation){
                $total_rent_price += $reservation->sub_total ;
                $total_ewa_tax_amount += $reservation->ewa_total ;
                $total_rent_tax_amount += $reservation->vat_total ;
                $total_service_tax_amount += 0 ;
                $total_vat_tax_amount += $reservation->vat_total  + 0 ;
            }

            // Filling the total array
            $total['rent_price'] = number_format($total_rent_price, 2);
            $total['ewa_tax_amount'] = number_format($total_ewa_tax_amount, 2);
            $total['rent_tax_amount'] = number_format($total_rent_tax_amount, 2);
            $total['service_tax_amount'] = number_format($total_service_tax_amount, 2);
            $total['vat_tax_amount'] = number_format($total_vat_tax_amount, 2);
        }

        return RevenueTaxResource::collection($reservations)->additional([
            'meta' => [
                'total' => $total
            ]
        ]);
    }
}
