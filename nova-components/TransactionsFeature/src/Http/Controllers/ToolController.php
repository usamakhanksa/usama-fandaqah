<?php

namespace Surelab\TransactionsFeature\Http\Controllers;
use App\Team;
use App\Unit;
use App\User;
use stdClass;
use App\Customer;
use Carbon\Carbon;
use App\Reservation;
use App\Transaction;
use Laravel\Nova\Nova;
use Carbon\CarbonPeriod;
use R64\NovaFields\JSON;
use App\ServicesCategory;
use App\Handlers\Settings;
use App\Http\Resources\Reports\EmployeeContractsResource;
use App\ReservationInvoice;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\DateTime;
use App\Services\CustomPagination;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Config;
use Spatie\QueryBuilder\AllowedFilter;
use App\Nova\Lenses\ReservationResources;
use Laravel\Nova\Http\Requests\LensRequest;
use Laravel\Nova\Http\Requests\NovaRequest;
use niklasravnsborg\LaravelPdf\Facades\Pdf;
use Laravel\Nova\Http\Requests\ResourceIndexRequest;
use App\Http\Resources\Reports\ReservationSourcesResource;
use App\Http\Resources\Transactions\ServicesTransactionsResource;

//use Barryvdh\DomPDF\PDF;

//use Nova;

//use App\Exports\TransactionReport;


class ToolController extends Controller {

    /**
     * Fetch Transactions based on type Deposit & Withdraw
     * @param NovaRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(NovaRequest $request){

        $current_team_id = auth()->user()->current_team_id ;
        $transaction_type = $request['type'] ;
        $transactions = Transaction::latest()
                                    ->where('amount', '!=' , 0)
                                    ->where('type' , 'LIKE' , "%$transaction_type%")
                                    ->whereHas('reservation',function($reservation) use($current_team_id){
                                        $reservation->whereHas('creator' , function($creator) use($current_team_id){
                                           $creator->where('current_team_id' , $current_team_id) ;
                                        });
                                    })
                                    ->with('wallet')
                                    ->with('payable')
                                    ->with('reservation.customer')
                                    ->with('reservation.creator')
                                    ->with('reservation.unit')
                                    ->get();

        if(count($transactions)){
            return response()->json([
                'msg' => 'transactions were retrieved successfully' ,
                'data' => $transactions
            ]);
        }else{
            return response()->json([
                'msg' => 'Awch , There are no transactions found'
            ]);
        }

    }

    /**
     * Calculate total cash based on payment type cash
     * @param NovaRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function total_cash(NovaRequest $request){
        // Receive request params
        $type = $request->get('transactionType') ;
        $payment_type = $request->get('payment_type') ;

        // Fetch Data based on transaction type and the related reservation through auth user
        $data = Transaction::where('type' , '=' , $type)
                            ->whereHas('reservation' , function($reservation){
                                    $reservation->where('created_by' , auth()->user()->id) ;
                            })->get();
        if($data){
            // init array
            $total_cash_array = [] ;
            // iterate through fetched data
            foreach($data as $transaction){
                $meta =  $transaction->meta ;
                if(isset($transaction->meta['payment_type'])){
                    if($transaction->meta['payment_type'] == 'cash'){
                        // Then it is a cash payment transaction
                        $total_cash_array [] = $transaction->amount / 100  ;
                    }
                }
            }
            $total_cash = abs(array_sum($total_cash_array)) ;
            return response()->json([
                'msg' => 'total_cash_found' ,
                'data' => $total_cash
            ]);

        }else{
            return response()->json([
                'msg' => 'total_cash_notfound' ,
                'data' => 0
            ]);
        }



    }

    /**
     * Calculate total bank cash based on payment type [ credit , mada , bank-transfer ]
     * @param NovaRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function total_bank_cash(NovaRequest $request){

        $type = $request->get('transactionType') ;

        $data = Transaction::where('type' , '=' , $type)
                            ->whereHas('reservation' , function($reservation){
                                    $reservation->where('created_by' , auth()->user()->id) ;
                            })->get();
        if($data){
            // init holder array
            $total_bank_cash_array = [] ;
            // iterate through fetched data
            foreach($data as $transaction){
                $meta =  $transaction->meta ;
                if(isset($transaction->meta['payment_type'])){
                    if($transaction->meta['payment_type'] != 'cash'){
                        // Then it is a cash payment transaction
                        $total_bank_cash_array [] = $transaction->amount / 100  ;
                    }
                }
            }

            $total_bank_cash = abs(array_sum($total_bank_cash_array)) ;

            return response()->json([
                'msg' => 'total_bank_cash_found' ,
                'data' => $total_bank_cash
            ]);
        }else{

            return response()->json([
                'msg' => 'total_bank_cash_notfound' ,
                'data' => 0
            ]);
        }



    }

    /**
     * Calculate total cash the sum of total cash and total bank cash
     * @param NovaRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function grand_total(NovaRequest $request){

        $type = $request->get('transactionType') ;
        $data = Transaction::where('type' , '=' , $type)
                            ->whereHas('reservation' , function($reservation){
                                $reservation->where('created_by' , auth()->user()->id) ;
                            })
                            ->sum('amount') ;


        if($data){

            $grand_total = abs($data/100) ;
            return response()->json([
                'msg' => 'grand_total_found' ,
                'data' => $grand_total
            ]);
        }else{

            return response()->json([
                'msg' => 'grand_total_notfound' ,
                'data' => 0
            ]);
        }



    }

    /**
     * Refresh the cash numbers based on filters
     * @param NovaRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filter_resources(NovaRequest $request){


        $resources_ids = $request->all();
        $cash_transactions = [] ;
        $other_transactions = [] ;
        foreach ($resources_ids as $id) {

            $transaction = Transaction::where('is_public' , 1)->find($id);

            if(isset($transaction->meta['payment_type'])){
                    if($transaction->meta['payment_type'] == 'cash'){
                        // Then it is a cash payment transaction
                        $cash_transactions [] = abs($transaction->amount / 100)  ;
                    }

                    if($transaction->meta['payment_type'] != 'cash'){
                        // Then it is a cash payment transaction
                        $other_transactions [] = abs($transaction->amount / 100)  ;
                    }
            }


        }


        $cash   = array_sum($cash_transactions);
        $other  = array_sum($other_transactions);

        $grand  = array_sum($cash_transactions) + array_sum($other_transactions) ;

        return response()->json([
            'cash' => $cash ,
            'other' => $other ,
            'grand' => abs($grand)

        ]);



    }

    /**
     * @TODO : needs refactor
     * @author emad rashad
     * @description : function to fetch all statistics no matter of the pagination
     * @param NovaRequest $request
     */
    public function allResourceStatistics(NovaRequest $request)
    {
        $query = Transaction::query();

        $filters = json_decode(base64_decode(\request('filters')), true);

        collect($filters)->each(function ($filter) use ($request, $query) {
            if (!is_null($filter['value']) and !empty($filter['value'])) {
                (new $filter['class'])->apply($request, $query, $filter['value']);
            }
        });

//        dump($query->toSql());

        $reservations = Reservation::where('team_id', auth()->user()->current_team_id)->pluck('id')->toArray();
        $transactionType = $request->get('type') ;
        $termId = $request->get('termId');


        if($transactionType == 'deposit' || $transactionType == 'withdraw'){
            if($termId == 0 ){

                $query = $query->where('type' , '=' , $transactionType)
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

                $query = $query->whereHasMorph('payable', Team::class, function ($query) use($transactionType,$termId) {
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

//        dd($query->toSql());

        $transactions = $query->get() ;

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

        return response()->json([
            'total_cash' => $this->number_format_abs($total_cash_attr),
            'total_bank_transfer_cash' => $this->number_format_abs($total_bank_cash_attr),
            'total_mada' => $this->number_format_abs($total_mada_attr),
            'total_credit' => $this->number_format_abs($total_credit_attr),
            'total' => $this->number_format_abs($total_c),
            'transactions_query' => $transactions->pluck('id')
        ]);

    }

    protected function number_format_abs($number)
    {
        return number_format(floatval($number), 2);
    }

    /**
    * Brief about the following code
    |--------------------------------------------------------------------------
    | Export Functionality implement in Print , PDF and Excel
    |--------------------------------------------------------------------------
    |
    | The Below Functions will take care of all possible logic for generating reports
    | implemented reports type are of [ Print , PDF , Excel ]
    | There are some private functions used for refactoring
    | I know we may use some design pattern for swapping different functions
    |
    */

    public function guestsMovementReportExcel(NovaRequest $request){


        $customersIds = json_decode($request->get('customersData') , true) ;
        $customers  = Customer::whereIn('id' , $customersIds)->get();
        $holder_array = array();
         foreach ($customers as $customer){
             $data[__('Customer Name')]          = $customer->name ;
             $data[__('Customer ID Number')]     = $customer->id_number ;
             $data[__('Nationality')]            = $customer->nationality_string ;
             $data[__('Phone')]                  = $customer->phone ;
             $data[__('Gender')]                 = __(ucfirst($customer->gender)) ;
             $data[__('Reservations Count')]     = $customer->reservations_count ;

             $holder_array [] = $data;
         }

         return response()->json([
             'status' => 'success' ,
             'data' => $holder_array ,
             'filename' => __('Guests Movement Report')
         ]);

    }

    public function monthlyReportExcel(NovaRequest $request){

        $withdraw_transactions = $request->get('resources_ids')[0] ;
        $deposit_transactions  = $request->get('resources_ids')[1] ;

        $days = $request->get('days');
        $pure_array = []  ;



        foreach ($days as $day){

            $data[__('Day')]          =     $day ;
            $data[__('Total Withdraw Transactions')]    = $withdraw_transactions[$day-1] ;
            $data[__('Total Deposit Transactions')]     = abs($deposit_transactions[$day-1]) ;
            $data[__('Total')]                          =   $deposit_transactions[$day -1] + $withdraw_transactions[$day - 1] ;
            $pure_array [] = $data ;
        }

        // that's how XLSX wants data to be formed
        $footer_array = array();
        $holder = array();

        $footer_array[__('Total Deposit')] = array_sum($deposit_transactions);
        $footer_array[__('Total Withdraw')] = abs(array_sum($withdraw_transactions));
        $footer_array[__('Total Credit')] = array_sum($deposit_transactions) + array_sum($withdraw_transactions);
        $holder [] = $footer_array;

        $file_name = __('Monthly Transactions Report');
        $monthly_details = __('Monthly Report Details') ;
        $monthly_statistics = __('Monthly Report Statistics') ;


        return response()->json([
            'status' => 'success' ,
            'data' => $pure_array ,
            'footer' => $holder,
            'file_name' => $file_name,
            'monthly_details' => $monthly_details,
            'monthly_statistics' => $monthly_statistics

        ]);

    }

    public function unitsMovementReportExcel(NovaRequest $request){

        $reservation_ids = json_decode($request->get('params')) ;
        // Fetch all matching reservations
        $reservations = Reservation::whereIn('id' , $reservation_ids)
            ->with('unit')
            ->with('customer')
            ->get() ;



        $counter = 1 ;
        // Iterate through each reservation
        foreach ($reservations as $reservation) {

                $data['#'] = $counter ;
                $data[__('Unit Name')]                  = $reservation->unit->name ;
                $data[__('Unit Number')]                = $reservation->unit->unit_number ;
                $data[__('Reservation Number')]         = $reservation->number ;
                $data[__('Reservation Date In')]                    = date('Y-m-d' , strtotime($reservation->date_in));
                $data[__('Reservation Date Out')]                   = date('Y-m-d' , strtotime($reservation->date_out));
                $data[__('Reservation Date')]           = date('Y-m-d' , strtotime($reservation->created_at));
                $data[__('Customer Name')]              = $reservation->customer['name'] ;

            $pure_array [] = $data ;
            // increment the counter
            $counter++ ;
        }

        $file_name = __('Units Movement Report') ;
        return response()->json([
            'status' => 'success' ,
            'data' => $pure_array ,
            'filename' => $file_name
        ]);

    }

    /**
     * Function will handle Excel Export
     * @author Emad Rashad
     * @param NovaRequest $request
     * @return json ( Transactions )
     */
    public function exportExcel(ResourceIndexRequest $request){

        /**
         * Common and General Players  -_-
         */
        $type    =  $request->get('type');
        $resources_ids    =  $request->toQuery()->pluck('id')->toArray();
        $pure_array = [] ;
        $file_name = '' ;


        switch ($type) {
            /** Guests Movement Report */
            case 'guests-report':

                # Export Guests Movement table report from here
                $self_export = 'excel' ;
                $pure_array = $this->fetchGuestsMovementReportData($resources_ids,$pure_array,$counter = 1 , $self_export);
                $file_name = 'Guests Movement Report' ;
                break;
            /** Units Movement Report */
            case 'units-report' :
                $self_export = 'excel' ;
                $pure_array = $this->fetchUnitsMovementReportData($resources_ids , $pure_array , $counter = 1 , $self_export) ;
                $file_name = 'Units Movement Report' ;
                break;
            /** Transactions Movement Reports  */
            case 'withdraw-deposit-transaction' :
                # we will export both withdraw and deposite transactions from here
                $transaction_type =  $request->get('transaction_type')  ;
                $self_export = 'excel' ;
                $pure_array = $this->fetchTransactionMovementReportData($resources_ids , $pure_array , $transaction_type ,  $counter = 1 , $self_export) ;
                $transaction_type == 'withdraw' ? $file_name = 'Withdraw Transactions Report' : $file_name = 'Deposit Transactions Report' ;
                break;
            /** Monthly Transactions Report  */
            case 'monthly-report' :


                # we will export report per month and year by drawing data as days
                $self_export = 'excel' ;
                $withdraw_transactions = $resources_ids[0] ;
                $deposit_transactions  = $resources_ids[1] ;
                $days = $request->get('days');
                $pure_array = []  ;

                foreach ($days as $day){

                    $data[__('Day')]          =     $day ;
                    $data[__('Total Withdraw Transactions')]    = abs($withdraw_transactions[$day-1]) ;
                    $data[__('Total Deposit Transactions')]     = abs($deposit_transactions[$day-1]) ;
                    $data[__('Total')]                          = abs(  $deposit_transactions[$day -1] - $withdraw_transactions[$day - 1] ) ;
                    $pure_array [] = $data ;
                }


                    $file_name = 'Monthly Transactions Report' ;
                break;

            default:
                # we may through exception here  -_-
                break;
        }


        /** return @array of collected data */
        return response()->json([
            'status' => 'success' ,
            'data' => $pure_array ,
            'filename' => $file_name
        ]);

    }

    /**
     * Function will handle PDF Export
     * @author Emad Rashad
     * @param ResourceIndexRequest $request
     * @return json
     */
    public function exportPdf(ResourceIndexRequest $request)
    {


        /** PDF General Configurations */
        $config = ['instanceConfigurator' => function ($mpdf) {
            $stylesheet = file_get_contents(storage_path('pdf/pdf.css'));
            $mpdf->WriteHTML($stylesheet, 1);

            $mpdf->autoScriptToLang = true;
            $mpdf->autoLangToFont = true;
        }];

        /** General Values that will be handled through our switch */
        $type    =  $request->get('type');

        $resources_ids = $request->get('transactions');
//        $resources_ids    =  $request->toQuery()->pluck('id')->toArray();
        $pure_array = [] ;
        /**  General Check if there was no selections for any resources */


        switch ($type) {
            /** Guests Movement Report */
            case 'guests-report':
                # Export Guests table report from here
                $self_export = 'pdf' ;
                $pure_array = $this->fetchGuestsMovementReportData($resources_ids,$pure_array,$counter = 1 , $self_export);
                $file_name = 'Guests Movement Report' ;
                $pdf = Pdf::loadView('pdf/guests-movement-report', ['guests' => $pure_array , 'title' => ucfirst($file_name) ], [], $config);
                break;
            /** Units Movement Report */
            case 'units-report':
                # Export Units Movement table report from here
                $self_export = 'pdf' ;
                $pure_array = $this->fetchUnitsMovementReportData($resources_ids , $pure_array , $counter = 1 , $self_export) ;
                $file_name = 'Units Movement Report' ;
                $pdf = Pdf::loadView('pdf/units-movement-report', ['reservations' => $pure_array , 'title' => ucfirst($file_name) ], [], $config);
                break;
            /** Transactions Movement Report */
            case 'withdraw-deposit-transaction':
                # we will export both withdraw and deposite transactions from here
                $transaction_type =  $request->get('transaction_type')  ;
                $self_export = 'pdf' ;
                $pure_array = $this->fetchTransactionMovementReportData($resources_ids , $pure_array , $transaction_type ,  $counter = 1 , $self_export) ;
                $transaction_type == 'withdraw' ? $file_name = 'Withdraw Transactions Report' : $file_name = 'Deposit Transactions Report' ;

                $pdf = Pdf::loadView('pdf/transactionsPdf', ['transactions' => $pure_array , 'title' => $file_name ], [], $config);
                break;

            /** Monthly Transactions Report */
            case 'monthly-report':
                # we will export report per month and year by drawing data as days
                $withdraw_transactions = $resources_ids[0] ;
                $deposit_transactions  = $resources_ids[1] ;
                $days = $request->get('days') ;
                $total_withdraw_widget = number_format(abs($request->get('total_withdraw_widget')),0) ;
                $total_deposit_widget = number_format(abs($request->get('total_deposit_widget')),0) ;
                $grand_total = number_format(abs(  $request->get('total_deposit_widget') - $request->get('total_withdraw_widget')),0) ;
                $file_name = __('Monthly Transaction Report') ;
                $pdf = Pdf::loadView('pdf/monthlyTransactionsPdf', [
                                        'days' => $days ,
                                        'withdraw_transactions' => $withdraw_transactions ,
                                        'deposit_transactions' => $deposit_transactions ,
                                        'total_withdraw_widget' => $total_withdraw_widget ,
                                        'total_deposit_widget' => $total_deposit_widget ,
                                        'grand_total' => $grand_total ,
                                        'title' => ucfirst($file_name)
                                    ],
                       [], $config);
                break;

            default:
                # We may throw exception here
                break;
        }
        /** Stream the pdf to be downloaded  */
        return $pdf->stream(ucfirst($file_name) .'.pdf');

    }

    /**
     * Function will handle Print Export
     * @author Emad Rashad
     * @param ResourceIndexRequest $request
     * @return json
     */
    public function printReport(NovaRequest $request)
    {


        /** General Variables */
        $type    =  $request->get('type');
        $resources_ids    =  $request->get('transactions');
        $pure_array = [];
        $properties = [];
        $title = '';

        switch ($type) {
            /** Guests Movement Report */
            case 'guests-report':
                    # Export Guests table report from here
                    $properties = [

                        __('Customer Name'),
                        __('Customer ID Number'),
                        __('Nationality'),
                        __('Phone'),
                        __('Gender'),
                        __('Reservations Count')

                    ];
                    $self_export = 'print' ;
                    $pure_array = $this->fetchGuestsMovementReportData($resources_ids,$pure_array,$counter = 1 , $self_export);
                    $title = __('Guests Movement Report') ;
                break;
            /** Units Movement Report */
            case 'units-report':
                # Print Units Movement table report from here
                $self_export = 'print' ;
                $properties = [

                    __('#'),
                    __('Unit Name'),
                    __('Unit Number'),
                    __('Reservation Number'),
                    __('Reservation Date In'),
                    __('Reservation Date Out'),
                    __('Reservation Date'),
                    __('Customer Name')

                ];
                $pure_array = $this->fetchUnitsMovementReportData($resources_ids , $pure_array , $counter = 1 , $self_export) ;
                $title = __('Units Movement Report') ;

                break;
            /** Transactions Movement Report */
            case 'withdraw-deposit-transaction':
                # Print Transaction Movement Report
                $transaction_type =  $request->get('transaction_type')  ;
                $properties = [

                    __('Transaction Number'),
                    __('Reservation Number'),
                    __('Unit Number'),
                    $transaction_type == 'withdraw' ? __('Exchange to'): __('Received From'),
                    __('Amount'),
                    __('Date Added'),
                    __('Payment Method'),
                    __('Payment Reference'),
                    __('Employee')


                ];
                $self_export = 'print' ;
                $pure_array = $this->fetchTransactionMovementReportData($resources_ids , $pure_array , $transaction_type ,  $counter = 1 , $self_export) ;

                $transaction_type == 'withdraw' ? $title = __('Withdraw Transactions Report') : $title = __('Deposit Transactions Report') ;
                break;

            default:
                # we may through exception here  -_-
                break;
        }
        /** return @array of collected data */
        return response()->json([
            'properties' => $properties ,
            'printable' => $pure_array ,
            'title' => $title
        ]);

    }

    /**
     * Refactoring Code Inside our export target [ Transactions Movement Report ]
     * @author Emad Rashad
     * @target : Transactions Movement Report
     * @param $resources_ids
     * @param $pure_array
     * @param $transaction_type
     * @param $counter
     * @param $self_export
     * @return array
     */
    private function fetchTransactionMovementReportData($resources_ids , $pure_array , $transaction_type , $counter , $self_export){

        // fetch transactions
        $transactions = Transaction::where('type' , '=' , $transaction_type )
                                    ->whereIn('id' , $resources_ids)
                                    ->where('is_public' , 1)
                                    ->with('reservation')
                                    ->with('reservation.unit')
                                    ->with('reservation.customer')
                                    ->with('reservation.creator')
                                    ->orderByDesc('created_at')
                                    ->get();

        foreach ($transactions as $transaction) {

            if($self_export != 'pdf'){

//                    dd($transaction->meta['date']);
                    $data[__('Transaction Number')]         = $transaction->number;
                    $data[__('Reservation Number')]         = $transaction->payable_type == 'App\Team' ? '--' :  $transaction->reservation->number ;
                    $data[__('Unit Number')]                = $transaction->payable_type == 'App\Team' ? '--' : $transaction->reservation->unit->unit_number ;
                    if($transaction->type == 'withdraw'){
                        $data[__('Exchange to')]            = isset($transaction->meta['from'])  ? $transaction->meta['from'] : '--'  ;
                    }else{
                        $data[__('Received From')]          = isset($transaction->meta['from'])  ? $transaction->meta['from'] : '--'  ;
                    }
                    $data[__('Amount')]                     = number_format(abs($transaction->amount/100) , 2 )  ;
                    $data[__('Date Added')]                 = isset($transaction->meta['date']) ? $transaction->meta['date'] : '--' ;
                    $data[__('Payment Method')]             = isset($transaction->meta['payment_type']) ? __(ucfirst($transaction->meta['payment_type'])) : '--' ;
                    $data[__('Payment Reference')]          = isset($transaction->meta['reference']) ? __(ucfirst($transaction->meta['reference'])) : '--' ;
                    $data[__('Employee')]                   = isset($transaction->meta['employee']) ? __(ucfirst($transaction->meta['employee'])) : '--' ;



            }else{
                $data = new stdClass();
                $data->customer_name          = $transaction->reservation->customer->name ;
                $data->customer_id_number     = $transaction->reservation->customer->id_number ;
                $data->nationality            = $transaction->reservation->customer->nationality_string ;
                $data->phone                  = $transaction->reservation->customer->phone ;
                $data->gender                 = ucfirst($transaction->reservation->customer->gender) ;
                $data->unit_number            = $transaction->reservation->unit->unit_number ;
                $data->unit_name              = $transaction->reservation->unit->name ;
                $data->amount                 = number_format(abs($transaction->amount) , 0 )  ;
                $data->category               = ucfirst($transaction->meta['category']) ;
                $data->date_added             = date('Y-m-d' , strtotime($transaction->created_at)) ;
                $data->reservation_date_in    = date('Y-m-d' , strtotime($transaction->reservation->date_in)) ;
                $data->reservation_date_out   = date('Y-m-d' , strtotime($transaction->reservation->date_out)) ;
                $data->added_dy               = $transaction->reservation->creator->name ;
            }

            // Push our variables to the pure array
            $pure_array [] = $data ;
        }

        return $pure_array ;
    }

    /**
     * Refactoring Code Inside our export target [ Guests Movement Report ]
     * @author Emad Rashad
     * @target : Guests Movement Report
     * @param $resources_ids
     * @param $pure_array
     * @param $counter
     * @param $self_export
     * @return array
     */
    private function fetchGuestsMovementReportData($resources_ids , $pure_array , $counter , $self_export){

        $customers = Customer::whereIn('id' , $resources_ids)->get();
        foreach ($customers as $customer) {
            if($self_export != 'pdf'){
                $data[__('Customer Name')]          = $customer->name ;
                $data[__('Customer ID Number')]     = $customer->id_number ;
                $data[__('Nationality')]            = $customer->nationality_string ;
                $data[__('Phone')]                  = $customer->phone ;
                $data[__('Gender')]                 = ucfirst($customer->gender) ;
                $data[__('Reservations Count')]     = $customer->reservations_count ;
            }else{
                $data = new stdClass();
                $data->customer_name          = $customer->name ;
                $data->customer_id_number     = $customer->id_number ;
                $data->nationality            = $customer->nationality_string ;
                $data->phone                  = $customer->phone ;
                $data->gender                 = ucfirst($customer->gender) ;
                $data->reservations_count     = $customer->reservations_count ;
            }

            // Push our variables to the pure array
            $pure_array [] = $data ;
        }

        return $pure_array ;
    }

    /**
     * Refactoring Code Inside our export target [ Units Movement Report ]
     * @author Emad Rashad
     * @target : Units Movement Report
     * @param $resources_ids
     * @param $pure_array
     * @param $counter
     * @param $self_export
     * @return array
     */
    private function fetchUnitsMovementReportData($resources_ids , $pure_array , $counter , $self_export){
        // Fetch all matching reservations
        $reservations = Reservation::whereIn('id' , $resources_ids)
                                        ->with('unit')
                                            ->with('customer')
                                                ->get() ;


        // Iterate through each reservation
        foreach ($reservations as $reservation) {
            // Check the type of our export
            if($self_export != 'pdf'){
                $data['#'] = $counter ;
                $data[__('Unit Name')]                  = $reservation->unit->name ;
                $data[__('Unit Number')]                = $reservation->unit->unit_number ;
                $data[__('Reservation Number')]         = $reservation->number ;
                $data[__('Reservation Date In')]                    = date('Y-m-d' , strtotime($reservation->date_in));
                $data[__('Reservation Date Out')]                   = date('Y-m-d' , strtotime($reservation->date_out));
                $data[__('Reservation Date')]           = date('Y-m-d' , strtotime($reservation->created_at));
                $data[__('Customer Name')]              = $reservation->customer->name ;
            }else{
                $data = new stdClass();
                $data->serial = $counter ;
                $data->unit_name          = $reservation->unit->name;
                $data->unit_number     =  $reservation->unit->unit_number ;
                $data->reservation_number            = $reservation->number ;
                $data->date_in                  = date('Y-m-d' , strtotime($reservation->date_in));
                $data->date_out                 = date('Y-m-d' , strtotime($reservation->date_out));
                $data->reservation_date                 = date('Y-m-d' , strtotime($reservation->created_at));
                $data->customer_name     = $reservation->customer->name ;
            }
            $pure_array [] = $data ;
            // increment the counter
            $counter++ ;
        }
        // return the result
        return $pure_array ;
    }

    public function monthlyReport(NovaRequest $request )
    {
      
        $current_team_id = $request->team_id;
        $month           = $request->month;
        $year            = $request->year;
        $employee_id     = $request->employee_id;

        $total_withdraw  = [];
        $total_deposit   = [];
        $days_arr        = [];


        if($month == 0 || $year == 0 ){
            return response()->json([
                'status' => 'invalid_month_or_year'
            ]);
        }

        $reservation_ids = Reservation::where('team_id', $current_team_id)->whereNull('deleted_at')->pluck('id')->toArray();
        $days = $this->days_in_month($month,$year);


        $transactions_reservations = QueryBuilder::for(Transaction::class)
        ->with('payable','wallet','creator')
        ->where('amount', '!=' , 0)
        ->where('is_public' ,1)
        ->where('payable_type' , 'App\\Reservation')
        ->whereIn('payable_id',$reservation_ids)
        ->when(!is_null($employee_id) , function($t) use($employee_id){
            $t->whereHas('creator' , function($c) use($employee_id){
                $c->where('id' , $employee_id);
            });
        })
        ->get();

        $transactions_teams = QueryBuilder::for(Transaction::class)
        ->with('payable','wallet','creator')
        ->where('amount', '!=' , 0)
        ->where('is_public' ,1)
        ->where('payable_type' , 'App\\Team')
        ->where('payable_id',$current_team_id)
        ->when(!is_null($employee_id) , function($t) use($employee_id){
            $t->whereHas('creator' , function($c) use($employee_id){
                $c->where('id' , $employee_id);
            });
        })
        ->get();

        $transactions = $transactions_reservations->merge($transactions_teams);

        for($day = 1 ; $day <= $days ; $day++){

            $date = date('Y-m-d' , strtotime($year .'-'. $month . '-' . $day));

            // calc current day transactions
            $withdraw_transactions_current_sum = 0;
            foreach ($transactions->where('type', 'withdraw') as $transaction) {
                if (isset($transaction->meta['date']) &&  strpos($transaction->meta['date'], $date) !== false) {
                    $withdraw_transactions_current_sum += $transaction->wallet->decimal_places == 3 ? $transaction->amount / 1000 :  $transaction->amount / 100 ;
                }
            }

            // calc current day transactions
            $deposit_transactions_current_sum = 0;
            foreach ($transactions->where('type', 'deposit') as $transaction) {
                if (isset($transaction->meta['date']) && strpos($transaction->meta['date'], $date) !== false) {
                    $deposit_transactions_current_sum += $transaction->wallet->decimal_places == 3 ? $transaction->amount / 1000 : $transaction->amount / 100;
                }
            }

            $total_withdraw [] = $withdraw_transactions_current_sum;
            $total_deposit [] = $deposit_transactions_current_sum;
            $days_arr [] = $day ;
        }


        $data[]= $total_withdraw ;
        $data[] = $total_deposit ;

        $total_withdraw_widget = array_sum($total_withdraw);
        $total_deposit_widget  = array_sum($total_deposit) ;
        $credit = $total_deposit_widget -(-$total_withdraw_widget)  ;

        return response()->json([
            'status' => 'success' ,
            'days' => $days_arr ,
            'result' => $data ,
            'total_withdraw_widget' => $total_withdraw_widget ,
            'total_deposit_widget' => $total_deposit_widget ,
            'credit' => $credit
        ]);
    }

    /**
     * Function will fetch all employees in the same team
     * @needed in monthly report
     * @param NovaRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchEmployees(NovaRequest $request){
        $users = User::where('current_team_id' , Auth::user()->current_team_id)->select('id','name')->get() ;
        return response()->json($users) ;
    }

    /**
     * Function will return the total numbers of days in specific month and year
     * @author : Emad Rashad
     * days_in_month($month, $year)
     * Returns the number of days in a given month and year, taking into account leap years.
     *
     * $month: numeric month (integers 1-12)
     * $year: numeric year (any integer)
     *
     * Prec: $month is an integer between 1 and 12, inclusive, and $year is an integer.
     * Post: none
     * @see : https://www.php.net/manual/en/function.cal-days-in-month.php
     */
    protected function days_in_month($month, $year){
        return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
    }

    /**
     * @author Emad Rashad
     * @description Function will return the hashed transaction id
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function hashTransactionId(NovaRequest $request){

        return response()->json(Hashids::connection('fandaqah')->encode($request->transaction_id)) ;
    }

    /**
     * @author Emad Rashad
     * @description :Withdraw transaction function used by safe movement report
     * @TODO : Needs refactor
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function withdrawTransactions(Request $request)
    {
        // Optional Date Range Filter
        $dateFrom  = $request->get('dateFrom') ;
        $dateTo    = $request->get('dateTo') ;

        $cash_transactions = [];
        $bank_transfer_transactions = [];
        $mada_transactions = [];
        $credit_card_transactions = [];
        $total_funds_to_management = 0;


        // fetch reservations
        $reservations_ids = Reservation::where('team_id', auth()->user()->current_team_id)->whereNull('deleted_at')->pluck('id')->toArray();

            $transactions = Transaction::whereNull('deleted_at')
                                        ->where('type' , 'withdraw')
                                        ->where('is_public' , 1)
                                        ->whereHasMorph('payable', [Team::class, Reservation::class], function ($query, $type) use($reservations_ids,$dateFrom,$dateTo){
                                            if ($type === Team::class) {
                                                 $query->where('payable_id', auth()->user()->current_team_id);
                                                 if($dateFrom != 'null' && $dateTo != 'null'){
                                                    $query->where('meta->date' , '>=' , $dateFrom)
                                                            ->where('meta->date' , '<' , $dateTo);
                                                 }
                                            }

                                            if ($type === Reservation::class) {
                                                $query->whereIn('payable_id', $reservations_ids);
                                                if($dateFrom != 'null' && $dateTo != 'null'){
                                                    $query->where('meta->date' , '>=' , $dateFrom)
                                                        ->where('meta->date' , '<' , $dateTo);
                                                }
                                            }
                                        })
                                        ->orderBy('created_at' , 'desc')
                                        ->get();

            if($transactions){
                foreach ($transactions as $transaction){

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
                            default :
                                break;

                        }
                    }

                    if($transaction->transaction_flag == 'managerial'){
                        $total_funds_to_management += $transaction->amount;
                    }


                }

                return response()->json([
                    'flag' => 'data_found',
                    'withdraw_transactions' => (new CustomPagination($transactions))->paginate(20),
                    'cash' => number_format(array_sum($cash_transactions),2),
                    'bank_transfer' => number_format(array_sum($bank_transfer_transactions),2),
                    'mada' => number_format(array_sum($mada_transactions),2),
                    'credit_card' => number_format(array_sum($credit_card_transactions),2),
                    'total_funds_to_management' => $total_funds_to_management / 100 ,
//                    'fixed_total_funds_to_management' => $this->getTotalFundsTransferred('withdraw' , 'managerial'),
                    'total_withdraw' => number_format($transactions->sum('amount') / 100,2),
                    'total_withdraw_without_format' => $transactions->sum('amount') / 100,
                    'all_withdraw_transactions' => $transactions

                ] , 200 ) ;

            }

    }

    /**
     * @author Emad Rashad
     * @description : Deposit transaction function used by safe movement report
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function depositTransactions(Request $request)
    {
        // Optional Date Range Filter
        $dateFrom  = $request->get('dateFrom') ;
        $dateTo    = $request->get('dateTo') ;


        $cash_transactions = [];
        $bank_transfer_transactions = [];
        $mada_transactions = [];
        $credit_card_transactions = [];
        $total_funds_from_management = 0;


        // fetch reservations
        $reservations_ids = Reservation::where('team_id', auth()->user()->current_team_id)->whereNull('deleted_at')->pluck('id')->toArray();

            $transactions = Transaction::whereNull('deleted_at')
                ->where('type' , 'deposit')
                ->where('is_public' , 1)
                ->whereHasMorph('payable', [Team::class, Reservation::class], function ($query, $type) use($reservations_ids,$dateFrom,$dateTo){
                    if ($type === Team::class) {
                        $query->where('payable_id', auth()->user()->current_team_id);
                        if($dateFrom != 'null' && $dateTo != 'null'){
                            $query->where('meta->date' , '>=' , $dateFrom)
                                ->where('meta->date' , '<' , $dateTo);
                        }
                    }

                    if ($type === Reservation::class) {
                        $query->whereIn('payable_id', $reservations_ids);
                        if($dateFrom != 'null' && $dateTo != 'null'){
                            $query->where('meta->date' , '>=' , $dateFrom)
                                ->where('meta->date' , '<' , $dateTo);
                        }
                    }
                })
                ->orderBy('created_at' , 'desc')
                ->get();

            if($transactions){
                foreach ($transactions as $transaction){

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
                            default :
                                break;

                        }
                    }

                    if($transaction->transaction_flag == 'managerial' && $transaction->termIsTransaferFromManagementToSafe()){
                        $total_funds_from_management += $transaction->amount;
                    }


                }

                return response()->json([
                    'flag' => 'data_found',
                    'deposit_transactions' => (new CustomPagination($transactions))->paginate(20),
                    'cash' => number_format(array_sum($cash_transactions),2),
                    'bank_transfer' => number_format(array_sum($bank_transfer_transactions),2),
                    'mada' => number_format(array_sum($mada_transactions),2),
                    'credit_card' => number_format(array_sum($credit_card_transactions),2),
                    'total_funds_from_management' => $total_funds_from_management / 100,
//                    'fixed_total_funds_to_management' => $this->getTotalFundsTransferred('withdraw' , 'managerial'),
                    'total_deposit' => number_format($transactions->sum('amount') / 100,2),
                    'total_deposit_without_format' => $transactions->sum('amount') / 100,
                    'all_deposit_transactions' => $transactions

                ] , 200 ) ;

            }


    }

    /**
     * @author Emad Rashad
     * @description : Export to excel for both withdraw & deposit transactions  | function is working on main data or paginated data
     * @param Request $request
     * @return json
     */
    public function safeMovementReportExcel(Request $request){

        // Receive transactions array
        $transactions =  $request->get('params');
        // exportable array
        $exportableArray = array();

        // holder data
        $data = array() ;
        // no need to check the array first cause am hiding export options if no data
        foreach ($transactions as $key => $transaction){
            // casting array as object
            $transaction = (object) $transaction ;

            $data[__('Transaction Number')] = $transaction->number ;
            $data[__('Amount')]             = abs($transaction->amount / 100) ;
            $data[__('Payment Method')]     = isset($transaction->meta['payment_type']) ? __(ucfirst($transaction->meta['payment_type'])) : '-';
            $data[__('For')]                = isset($transaction->meta['statement']) ? $transaction->meta['statement'] : '-';
            $data[__('Employee')]           = isset($transaction->meta['employee']) ? $transaction->meta['employee'] : '-';
            $data[__('Date')]               = isset($transaction->meta['date']) ? $transaction->meta['date'] : '-';

            $exportableArray [] = $data ;

        }


        return response()->json(['data' => $exportableArray] , 200) ;
    }

    /**
     * @Todo : needs investigation cause it's exporting empty file
     * @author : emad rashad
     * @description handle pdf export for transactions from safe movement report
     * @return Stream Pdf
     */
    public function safeMovementReportPdf(Request $request){

        /** PDF General Configurations */
        $config = ['instanceConfigurator' => function ($mpdf) {
            $stylesheet = file_get_contents(storage_path('pdf/pdf.css'));
            $mpdf->WriteHTML($stylesheet, 1);

            $mpdf->autoScriptToLang = true;
            $mpdf->autoLangToFont = true;
        }];

        // initial file name
        $filename = '';

        // exportable array
        $exportableArray = array();
        // Receive transactions array
        $transactions =  $request->get('params');

        // Type
        $type = $request->get('type') ;

        foreach ($transactions as $transaction){

            $transaction = (object) $transaction ;

            $data = new stdClass();

            $data->transaction_number   = $transaction->number ;
            $data->amount               = abs($transaction->amount / 100) ;
            $data->payment_method       = isset($transaction->meta['payment_type']) ? __(ucfirst($transaction->meta['payment_type'])) : '-';
            $data->for                  = isset($transaction->meta['statement']) ? $transaction->meta['statement'] : '-';
            $data->employee             = isset($transaction->meta['employee']) ? $transaction->meta['employee'] : '-';
            $data->date                 = isset($transaction->meta['date']) ? $transaction->meta['date'] : '-';

            $exportableArray [] = $data ;
        }

        switch ($type){
            case 'deposit':
                $filename = 'Deposit Transactions' ;
                break;
            case 'withdraw':
                $filename = 'Withdraw Transactions';
                break;

        }

        $pdf = Pdf::loadView('pdf/safe-movement-report-transactions', ['transactions' => $exportableArray , 'title' => $filename ], [], $config);

        /** Stream the pdf to be downloaded  */
        return $pdf->stream( $filename.'.pdf');
    }

    public function safeMovementReportPrint(Request $request){

        $transactions =  $request->get('params');
        $exportableArray = array();
        $properties = [
            __('Transaction Number'),
            __('Amount'),
            __('Payment Method'),
            __('For'),
            __('Employee'),
            __('Date')
        ];
        foreach ($transactions as $key => $transaction){
            // casting array as object
            $transaction = (object) $transaction ;

            $data[__('Transaction Number')] = $transaction->number ;
            $data[__('Amount')]             = abs($transaction->amount / 100) ;
            $data[__('Payment Method')]     = isset($transaction->meta['payment_type']) ? __(ucfirst($transaction->meta['payment_type'])) : '-';
            $data[__('For')]                = isset($transaction->meta['statement']) ? $transaction->meta['statement'] : '-';
            $data[__('Employee')]           = isset($transaction->meta['employee']) ? $transaction->meta['employee'] : '-';
            $data[__('Date')]               = isset($transaction->meta['date']) ? $transaction->meta['date'] : '-';

            $exportableArray [] = $data ;

        }

        /** return @array of collected data */
        return response()->json([
            'properties' => $properties ,
            'printable' => $exportableArray
        ]);
    }


    public function revenueTaxFeeReport(NovaRequest $request){

        // Request Filters
        $dateFrom     = $request->get('dateFrom') ;
        $dateTo       = $request->get('dateTo') ;

        $reservations = Reservation::where('team_id' , auth()->user()->current_team_id)
                                    ->whereIntersectsDateIn($dateFrom)
                                    ->whereIntersectsDateOut($dateTo)
                                    ->where('status' ,  'confirmed')
                                    ->orderBy('id', 'desc')
                                    ->get();



        if(count($reservations)){

            $collection = collect($reservations);
            $count  =  $collection->filter(function($reservation)
            {
                return $reservation->ttx_total > 0 ;
            })->count();

            $hasAtLeastOneTourismTaxApplied = $count > 0 ? true : false ;

            // General Holders
            $leasing_revenue        = [];
            $services_revenue       = [];
            $total_revenue          = [];
            $total_ewa              = [];
            $vat_on_reservation     = [];
            $vat_on_services        = [];
            $ttx_on_reservation     = [];
            $ttx_on_services        = [];
            $reservationsSkeleton   = [];
            $reservationsHolder  = [];
            $total_reservation      = [];


            foreach ($reservations as $reservation){
                // Reservation Dates
                $dateFromParsed = Carbon::parse($reservation->date_in);
                $dateToParsed   = Carbon::parse($reservation->date_out);

                // Selected Dates
                $fromParsed = Carbon::parse($dateFrom);
                $toParsed   = Carbon::parse($dateTo);

                $reservationsSkeleton ['reservation_id'] = $reservation->id;
                $reservationsSkeleton ['reservation_number'] = $reservation->number;
                $reservationsSkeleton ['unit_number'] = $reservation->unit ? $reservation->unit->unit_number : '-';
                $reservationsSkeleton ['customer_name'] = $reservation->customer ? $reservation->customer->name : '-';
                $reservationsSkeleton ['from_date'] = date('Y/m/d' , strtotime($dateFrom));

                // Nights
                $nights = $this->calculateNights($reservation, $dateFrom, $dateTo);


                // Override From Date && To Date According To Required Logic
                if($reservation->date_out > $dateTo){
                    $dateCompared = $dateTo ;
                }else{
                    $dateCompared = $reservation->date_out ;
                }

                if($fromParsed->lessThanOrEqualTo($dateFromParsed)){
                    $reservationsSkeleton ['from_date'] = $dateFromParsed->format('Y/m/d');
                    if ($reservation->nights == 1)
                        $dateCompared = $reservation->date_in;
                }else{
                    $reservationsSkeleton ['to_date'] = $reservation->date_in;
                }

                if ($toParsed->greaterThanOrEqualTo($dateToParsed) and $reservation->nights != 1) {
                    if($dateToParsed == $fromParsed){
                        $dateCompared = $dateToParsed ;
                    }else{
                        $dateCompared = $dateToParsed->subDay();
                    }
                    $nights = $reservation->nights;
                }

                $reservationsSkeleton ['to_date'] =   date('Y/m/d' , strtotime($dateCompared));
                $reservationsSkeleton ['nights_count'] =  $nights;

                if ($reservationsSkeleton ['from_date'] == $reservationsSkeleton ['to_date']) {
                    $reservationsSkeleton ['nights_count'] = 1;
                }

                $from = Carbon::parse($reservationsSkeleton ['from_date']);
                $to = Carbon::parse($reservationsSkeleton ['to_date']);
                $nights = $to->diff($from)->days;

                $reservationsSkeleton ['nights_count'] = $nights + 1 ;
                $reservation_nights = $reservation->nights <= 0 ? 1 : $reservation->nights ;


                /*----------------------------------------------------- Data --------------------------------------------------------------*/
                $servicesTransactionsCalculations                           = $this->servicesPerDate($reservation,$reservationsSkeleton ['from_date'] , $reservationsSkeleton ['to_date']);
                $reservationsSkeleton ['leasing_price']                     = number_format(($reservation->sub_total / $reservation_nights), 2) ;
                $reservationsSkeleton ['services']                          = number_format($servicesTransactionsCalculations['services_subtotal'] , 2);
                $reservationsSkeleton ['total_revenue']                     = number_format( ( ($reservation->sub_total / $reservation_nights) * $reservationsSkeleton ['nights_count']) + $servicesTransactionsCalculations['services_subtotal'] , 2);
                $totalRevenue                                               = (($reservation->sub_total / $reservation_nights) * $reservationsSkeleton ['nights_count']) + $servicesTransactionsCalculations['services_subtotal'] ;
                $reservationsSkeleton ['total_ewa']                         = number_format(($reservation->ewa_total / $reservation_nights) * $reservationsSkeleton['nights_count'], 2)  ;
                $totalEwa                                                   = (($reservation->ewa_total / $reservation_nights) * $reservationsSkeleton['nights_count']);
                $reservationsSkeleton ['vat_on_reservation']                = number_format(($reservation->vat_total / $reservation_nights) * $reservationsSkeleton['nights_count'] , 2);
                $reservationsSkeleton ['vat_on_services']                   = number_format($servicesTransactionsCalculations['services_vat'] , 2);
                $reservationsSkeleton ['total_vat']                         = number_format((($reservation->vat_total / $reservation_nights) * $reservationsSkeleton['nights_count']) + $servicesTransactionsCalculations['services_vat'] , 2);
                $totalVat                                                   = (($reservation->vat_total / $reservation_nights) * $reservationsSkeleton['nights_count']) + $servicesTransactionsCalculations['services_vat'];
                $reservationsSkeleton ['ttx_on_reservation']                = number_format( ($reservation->ttx_total / $reservation_nights) * $reservationsSkeleton['nights_count'] , 2) ;
                $reservationsSkeleton ['ttx_on_services']                   = number_format($servicesTransactionsCalculations['services_ttx'],2);
                $reservationsSkeleton ['total_ttx']                         = number_format((($reservation->ttx_total / $reservation_nights) * $reservationsSkeleton['nights_count']) + $servicesTransactionsCalculations['services_ttx'] , 2);
                $totalTtx                                                   = (($reservation->ttx_total / $reservation_nights) * $reservationsSkeleton['nights_count']) + $servicesTransactionsCalculations['services_ttx'];

                $reservationsSkeleton ['total_reservation']                 = number_format( (double) $totalRevenue + (double) $totalEwa  + (double) $reservationsSkeleton ['total_vat'] +  (double) $reservationsSkeleton ['total_ttx'] , 2);
                $totalReservation                                           = $totalRevenue + $totalEwa  + $totalVat +  $totalTtx;

                /*--------------------------------------------------- Calculations -------------------------------------------------------- */
                $leasing_revenue    []      = ($reservation->sub_total / $reservation_nights) * $reservationsSkeleton['nights_count'];
                $services_revenue   []      = $reservationsSkeleton ['services'];
                $total_revenue      []      = $totalRevenue;
                $total_ewa          []      = ($reservation->ewa_total / $reservation_nights) * $reservationsSkeleton['nights_count'];
                $vat_on_reservation []      = ($reservation->vat_total / $reservation_nights) * $reservationsSkeleton['nights_count'];
                $vat_on_services    []      = $reservationsSkeleton ['vat_on_services'];
                $ttx_on_reservation []      = ($reservation->ttx_total / $reservation_nights) * $reservationsSkeleton['nights_count'];
                $ttx_on_services    []      = $reservationsSkeleton ['ttx_on_services'];
                $total_reservation  []      = $totalReservation;


                $reservationsHolder [] = $reservationsSkeleton ;
            }


            /**
             * This Pagination is coming from side service in App\Services
             */
            $reservationsFormatted = (new CustomPagination($reservationsHolder))->paginate(10);


            /**
             * Calculations needed for our table statistics
             */
            $calculations['leasing_revenue'] = number_format(array_sum($leasing_revenue) , 2);
            $calculations['services_revenue'] = number_format(array_sum($services_revenue) , 2);
            $calculations['total_revenue'] = number_format(array_sum($total_revenue) , 2);
            $calculations['total_ewa'] = number_format(array_sum($total_ewa) , 2);
            $calculations['vat_on_reservation'] = number_format(array_sum($vat_on_reservation) , 2);
            $calculations['vat_on_services'] = number_format(array_sum($vat_on_services) , 2);
            $calculations['total_vat'] = number_format(array_sum($vat_on_reservation)  + array_sum($vat_on_services)  , 2);
            $calculations['ttx_on_reservation'] = number_format(array_sum($ttx_on_reservation) , 2);
            $calculations['ttx_on_services'] = number_format(array_sum($ttx_on_services) , 2);
            $calculations['total_ttx'] = number_format(array_sum($ttx_on_reservation)  + array_sum($ttx_on_services)  , 2);
            $calculations['total_reservations'] = number_format(array_sum($total_reservation),2);



            /**
             * Returning our custom json response
             */
            return response()->json([
                'flag' => 'data_found' ,
                'reservations' => $reservationsFormatted,
                'calculations' => $calculations,
                'reservations_formatted' => $reservationsHolder,
                'hasAtLeastOneTourismTaxApplied' => $hasAtLeastOneTourismTaxApplied
            ]);
        }else{
            return response()->json([
                'flag' => 'date_not_found',
                'reservations' => [],
                'calculations' => [],
                'reservations_formatted' => [],
                'hasAtLeastOneTourismTaxApplied' => false
            ]);
        }
    }

    function calculateNights($reservation,$dateFrom,$dateTo){
        $date_in = new \DateTime($dateFrom);
        $date_out = new \DateTime($dateTo);

        $days = $date_out->diff($date_in)->days;

        if ($days == 0){
            $days = 1 ;
            return $days;
        }

        return $days > $reservation->nights ?   $days  : $reservation->nights ;
    }

    /**
     * Function will fetch services per date
     * @param $reservation_id
     * @param $from
     * @param $to
     * @return array
     */
    function servicesPerDate($reservation,$from,$to){


        $from  = Carbon::parse($from)->format('Y-m-d');
        $to  = Carbon::parse($to)->format('Y-m-d');
        $services =  $reservation->services()
                                ->where('is_public' , 0)
                                ->where('meta->category' , 'service')
                                ->whereCreatedCustom($from,$to)
                                ->get();

        if($services){
           return  $this->filterServices($services);
        }
        return $services;

    }

    /**
     * Function will filter services then return array of calculations
     * @param $services
     * @return array
     */
    function filterServices($services){

        $vat = [] ;
        $ttx = [];
        $subtotal = [];
        $total_with_taxes = [];
        foreach ($services as $service){

            $vat [] = $service->meta['vat_total'];
            $ttx [] = $service->meta['ttx_total'];
            $subtotal [] = $service->meta['sub_total'];
            $total_with_taxes [] = $service->meta['total_with_taxes'];
        }

        return [
          'services_vat' =>  array_sum($vat),
          'services_ttx' =>  array_sum($ttx),
          'services_subtotal' => array_sum($subtotal),
          'services_total_with_taxes' => array_sum($total_with_taxes)
        ];
    }


    /**
     * @description this function will return amount of tax applied on services per reservation ( taxes are vat - ttx )
     * @param $reservation object
     * @param $type string
     * @param $formatted boolean
     * @return string
     */
    function taxesOnServices($reservation , $type , $formatted = false){
        $taxes = [];
        $services = $reservation->services ;
        if($services){
            foreach ($services as $service){
                $taxes [] = $type == 'vat' ? $service->meta['vat_total'] : $service->meta['ttx_total'];
            }
            return  $formatted ? number_format(array_sum($taxes) , 2) : array_sum($taxes);
        }else{
            return $formatted ? number_format(0 , 2) : 0;
        }
    }

    /**
     * @author Emad Rashad
     * @description : Function is used to generate print for revenue report
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function revenueTaxFeeReportPrint(Request $request){

        // catch report data
        $reportData = $request->get('reportData') ;
        // Holder array
        $holder = array();
        // array needed for print js
        $properties = [

            __('Reservation Number'),
            __('Customer name'),
            __('Date In'),
            __('Date Out'),
            __('Duration'),
            __('Revenue'),
            __('The value of the occupancy fees of the accommodation facilities'),
            __('Total VAT')

        ];

        foreach ($reportData as $reservation){

            $reservation = (object) $reservation ;

            $checked_in  = new \DateTime($reservation->date_in_time);
            $checked_out = new \DateTime($reservation->date_out_time);
            $duration = $checked_in->diff($checked_out);
            $day    = $duration->format('%a');
            $hour   = $duration->format('%h');
            $mintue = $duration->format('%i');



            $data[__('Reservation Number')]                                                  = $reservation->number ;
            $data[__('Customer name')]                                                       = $reservation->customer['name'] ;
            $data[__('Date In')]                                                        = $reservation->date_in_time ;
            $data[__('Date Out')]                                                       = $reservation->date_out_time ;
            $data[__('Duration')]                                                            =  $day . ' ' . __('Day') . '<br>' . $hour . ' ' . __('Hour') . '<br>' . $mintue .' ' . __('Minute') ;
            $data[__('Revenue')]                                                            =  $reservation->sub_total ;
            $data[__('The value of the occupancy fees of the accommodation facilities')]     = $reservation->ewa_total ;
            $data[__('Total VAT')]                                                           = $reservation->vat_total ;

            $holder[] = $data ;
        }


        /** return @array of collected data */
        return response()->json([
            'properties' => $properties ,
            'printable' => $holder
        ]);


    }


    public function revenueTaxFeeReportExcel(Request $request){

        // catch report data
        $reportData = $request->get('reportData') ;
        $hasTtx = $request->get('hasTtx');

        // Holder array
        $holder = array();
        foreach ($reportData as $reservation){
            $reservation = (object) $reservation;
            $data[__('Reservation Number')] = $reservation->reservation_number;
            $data[__('Unit Number')] = $reservation->unit_number;
            $data[__('Customer name')] = $reservation->customer_name;
            $data[__('From Date')] = $reservation->from_date;
            $data[__('To Date')] = $reservation->to_date;
            $data[__('Nights Count')] = $reservation->nights_count;
            $data[__('Leasing')] = $reservation->leasing_price;
            $data[__('Services')] = $reservation->services;
            $data[__('Total Revenue')] = $reservation->total_revenue;
            $data[__('Ewa')] = $reservation->total_ewa;
            $data[__('Vat on reservation')] = $reservation->vat_on_reservation;
            $data[__('Vat on services')] = $reservation->vat_on_services;
            $data[__('Total Vat')] = $reservation->total_vat;
            if($hasTtx){
                $data[__('Ttx on reservation')] = $reservation->ttx_on_reservation;
                $data[__('Ttx on services')] = $reservation->ttx_on_services;
                $data[__('Total Ttx')] = $reservation->total_ttx;
            }
            $data[__('Total Reservation')] = $reservation->total_reservation;

            $holder[] = $data ;
        }


        return response()->json(['data' => $holder] , 200) ;



    }

    public function getReservationResourcesTotals(Request $request)
    {
        $reservations = QueryBuilder::for(Reservation::class)
                    ->with(['wallet','unit' , 'customer' ,'source' , 'services','groupReservationBalanceMapper'])
                    ->allowedFilters([
                        AllowedFilter::scope('by_source'),
                        AllowedFilter::scope('by_date_from'),
                        AllowedFilter::scope('by_date_to'),
                        AllowedFilter::scope('by_source_number')
                    ])
                    ->where('status' , '!=' , 'awaiting-payment')
                    ->where('team_id' , auth()->user()->current_team_id)
                    ->whereNull('deleted_at')
                    ->orderByDesc('id')
                    ->get();

        $total_cost = [];
        if(count($reservations)){
            foreach ($reservations as $reservation) {
                $total_cost [] = $reservation->total_price + $reservation->getServicesSum();
            }
        }
        return response()->json(['total_cost' => number_format(array_sum($total_cost),2) , 'total_count' => count($reservations)]);
    }
    public function getReservationResources(Request $request)
    {
        $reservations = QueryBuilder::for(Reservation::class)
                                ->with(['unit' , 'customer' ,'source','groupReservationBalanceMapper'])
                                ->allowedFilters([
                                    AllowedFilter::scope('by_source'),
                                    AllowedFilter::scope('by_date_from'),
                                    AllowedFilter::scope('by_date_to'),
                                    AllowedFilter::scope('by_source_number')
                                ])
                                ->where('status' , '!=' , 'awaiting-payment')
                                ->where('team_id' , auth()->user()->current_team_id)
                                ->whereNull('deleted_at')
                                ->orderByDesc('id')
                                ->paginate($request->get('per_page', 20));

         return ReservationSourcesResource::collection($reservations);
    }

    public function reservationResourceExcel(Request $request){

        $reservations = QueryBuilder::for(Reservation::class)
        ->with(['wallet','unit' , 'customer' ,'source' , 'services','groupReservationBalanceMapper'])
        ->allowedFilters([
            AllowedFilter::scope('by_source'),
            AllowedFilter::scope('by_date_from'),
            AllowedFilter::scope('by_date_to')
        ])
        ->where('status' , '!=' , 'awaiting-payment')
        ->where('team_id' , auth()->user()->current_team_id)
        ->whereNull('deleted_at')
        ->orderByDesc('id')
        ->get();

        // Holder array
        $holder = array();

        foreach ($reservations as $reservation){

            $reservation = (object) $reservation ;

            $balance = 0;
            if($reservation->reservation_type == 'single'){
                $balance = $reservation->balance / ($reservation->wallet->decimal_places == 2 ? 100 : 1000) ;
                $balance = abs($balance) ;
            }else{
                $balance = abs($reservation->groupReservationBalanceMapper->balance);
            }

            if(is_null($reservation->checked_in) && is_null($reservation->checked_out)) {
                $reservation_status = __('Pending');
            } else if (!is_null($reservation->checked_in) && is_null($reservation->checked_out)) {
                $reservation_status = __('Checked In');
            } else {
                $reservation_status = __('Checked Out');
            }

            $data[__('Reservation Number')]                                                  = $reservation->number ;
            $data[__('Customer')]                                                       = $reservation->reservation_type == 'single' ?  $reservation->customer->name : $reservation->company->name  ;
            $data[__('The Unit')]                                                       = $reservation->unit ? $reservation->unit->unit_number . '-' . $reservation->unit->name : '-';
            $data[__('Source')]                                                       = $reservation->source ? $reservation->source->name : '-' ;
            $data[__('Source number')]                                                = $reservation->source_num ? $reservation->source_num : '-' ;
            $data[__('Status')]                                                       = $reservation->status == 'confirmed' ? __('Confirmed') : __('Canceled');
            $data[__('Reservation Status')]                                                       = $reservation_status ;
            $data[__('Date In')]                                                             = $reservation->date_in;
            $data[__('Date Out')]                                                           = $reservation->date_out ;
            $data[__('Nights Count')]                                                            =  $reservation->nights;
            $data[__('Rent Type')]                                                            =  $reservation->rent_type == 1 ? __('Daily') : __('Monthly') ;
            $data[__('Balance')]                                                            =  $balance;
            $holder [] = $data ;

        }



        return response()->json([
            'status' => 'success' ,
            'data' => $holder ,
            'msg' => __('Data exported successfully'),
            'filename' => __('Reservation Resources Report')
        ]);


    }




    public function getEmployeesContractsTotals(Request $request)
    {
        $reservations = QueryBuilder::for(Reservation::class)
                    ->with(['wallet','unit' , 'creator' , 'customer' ,'source' , 'services','groupReservationBalanceMapper'])
                    ->allowedFilters([
                        AllowedFilter::scope('by_unit_number'),
                        AllowedFilter::scope('by_creator'),
                        AllowedFilter::scope('by_created_from'),
                        AllowedFilter::scope('by_created_to')
                    ])
                    ->where('status' ,'confirmed')
                    ->where('team_id' , auth()->user()->current_team_id)
                    ->whereNull('deleted_at')
                    ->orderByDesc('id')
                    ->get();

        $total_cost = [];
        if(count($reservations)){
            foreach ($reservations as $reservation) {
                $total_cost [] = $reservation->total_price + $reservation->getServicesSum();
            }
        }
        return response()->json(['total_cost' => number_format(array_sum($total_cost),2) , 'total_count' => count($reservations)]);
    }

    public function getEmployeesContracts(Request $request)
    {
        $reservations = QueryBuilder::for(Reservation::class)
                                ->with(['unit' , 'customer' , 'creator' ,'source','groupReservationBalanceMapper'])
                                ->allowedFilters([
                                    AllowedFilter::scope('by_unit_number'),
                                    AllowedFilter::scope('by_creator'),
                                    AllowedFilter::scope('by_created_from'),
                                    AllowedFilter::scope('by_created_to')
                                ])
                                ->where('status' ,'confirmed')
                                ->where('team_id' , auth()->user()->current_team_id)
                                ->whereNull('deleted_at')
                                ->orderByDesc('id')
                                ->paginate($request->get('per_page', 20));

         return EmployeeContractsResource::collection($reservations);
    }

    public function employeeContractsReportExcel(Request $request){

        $reservations = QueryBuilder::for(Reservation::class)
                    ->with(['wallet','unit' , 'creator' , 'customer' ,'source' , 'services','groupReservationBalanceMapper'])
                    ->allowedFilters([
                        AllowedFilter::scope('by_unit_number'),
                        AllowedFilter::scope('by_creator'),
                        AllowedFilter::scope('by_created_from'),
                        AllowedFilter::scope('by_created_to')
                    ])
                    ->where('status' ,'confirmed')
                    ->where('team_id' , auth()->user()->current_team_id)
                    ->whereNull('deleted_at')
                    ->orderByDesc('id')
                    ->get();
        $data = [] ;
        $holder = [] ;
        foreach ($reservations as $reservation) {

            $balance = 0;
            if($reservation->reservation_type == 'single'){
                $balance = $reservation->balance / ($reservation->wallet->decimal_places == 2 ? 100 : 1000) ;
                $balance = abs($balance) ;
            }else{
                $balance = abs($reservation->groupReservationBalanceMapper->balance);
            }


            $data[__('Reservation Number')]             = $reservation->number;
            $data[__('Customer')]                  = $reservation->reservation_type == 'single' ?  $reservation->customer->name : $reservation->company->name  ;
            $data[__('The Unit')]                    = $reservation->unit ? $reservation->unit->unit_number . '-' . $reservation->unit->name : '-' ;
            $data[__('Employee Name')]                  = $reservation->creator ? $reservation->creator->name : '-' ;
            $data[__('Creation Date')]                  = Carbon::parse($reservation->created_at)->format('Y-m-d') ;
            $data[__('Total Price')]                    = $reservation->total_price + $reservation->getServicesSum() ;
            $data[__('Balance')]                         = $balance;

            $holder [] = $data ;
        }


        return response()->json([
            'status' => 'success' ,
            'msg' => __('Data exported successfully'),
            'data' => $holder ,
            'file_name' => __('Employees Contracts')
        ]);

    }

    /**
     * @description : this function ( way ) is a hack for nova
     * @issue : when resource results get paginated in vue , the only available resources are those on screen only , not what included in other pages
     *          and this case we need to do a functionality like print or excel export not only for data on screen but also the other data in other pages
     *          and no simple or direct way to do so so we hacked the indexQuery of the resource , which target the registered lens we needed
     *          and guess what it worked -_- !!!
     * @param LensRequest $request
     * @return array
     */
    public function refreshData(LensRequest $request){


        $query = Reservation::query();
        $filters = json_decode(base64_decode(\request('filters')), true);
        collect($filters)->each(function ($filter) use ($request, $query) {
            if (!is_null($filter['value']) and !empty($filter['value'])) {
                (new $filter['class'])->apply($request, $query, $filter['value']);
            }
        });

        $query = \App\Nova\Reservation::indexQuery($request,$query);
        $reservations = $query->get()->pluck('id')->toArray();

        $reservationResourcesSum = Reservation::whereIn('id' , $reservations)->sum('total_price') ;

        $services_cost_array = [] ;
        foreach ($reservations as $id){
            $reservation = Reservation::find($id);
            $services_cost_array []  = $reservation->getServicesSum() ;
        }

        $total_amount = array_sum($services_cost_array) + $reservationResourcesSum ;
        return response()->json(['reservations' => $reservations , 'reservations_count' => count($reservations) , 'reservations_total_amount' => number_format( $total_amount , 2)]);



    }

    public function contractsInformations(NovaRequest $request){

        $query = Reservation::query();

        $filters = json_decode(base64_decode(\request('filters')), true);
        collect($filters)->each(function ($filter) use ($request, $query) {
            if (!is_null($filter['value']) and !empty($filter['value'])) {
                (new $filter['class'])->apply($request, $query, $filter['value']);
            }
        });

        $query = \App\Nova\Reservation::indexQuery($request,$query);

        $reservations = $query->where('status' , '!=' , 'canceled')->get()->pluck('id')->toArray();

        $reservationResourcesSum = Reservation::whereIn('id' , $reservations)->sum('total_price') ;

        $services_cost_array = [] ;
        foreach ($reservations as $id){
            $reservation = Reservation::find($id);
            $services_cost_array []  = $reservation->getServicesSum() ;
        }


        $total_amount = array_sum($services_cost_array) + $reservationResourcesSum ;

        return response()->json(['reservations' => $reservations , 'reservations_count' => count($reservations) , 'reservations_total_amount' => number_format( $total_amount , 2)]);



    }



    public function customersInformation(NovaRequest $request){

        $query = Customer::query();

        $filters = json_decode(base64_decode(\request('filters')), true);
        collect($filters)->each(function ($filter) use ($request, $query) {
            if (!is_null($filter['value']) and !empty($filter['value'])) {
                (new $filter['class'])->apply($request, $query, $filter['value']);
            }
        });

        $query = \App\Nova\Customer::indexQuery($request,$query);

        $customers = $query->get()->pluck('id') ;

        return $customers ;

    }


    public function unitsInformation(NovaRequest $request){

        $query = Reservation::query();

        $filters = json_decode(base64_decode(\request('filters')), true);
        collect($filters)->each(function ($filter) use ($request, $query) {
            if (!is_null($filter['value']) and !empty($filter['value'])) {
                (new $filter['class'])->apply($request, $query, $filter['value']);
            }
        });

        $query = \App\Nova\Reservation::indexQuery($request,$query);

        $units = $query->get()->pluck('id') ;

        return $units ;

    }


    public function reservationsInvoices(Request $request){

        $dateFrom           = $request->get('dateFrom');
        $dateTo             = $request->get('dateTo');
        $invoice_number     = $request->get('invoiceNumber');
        $reservation_number = $request->get('reservationNumber');
        $employee_id        = $request->get('employeeId');

        $query = ReservationInvoice::query();

        $query->whereNull('deleted_at')
                ->whereHas('reservation', function ($reservation) {
                    $reservation->whereNull('deleted_at');
                })
                ->with('reservation')
                ->with('reservation.customer')
                ->with('creator');



        /**
         * Conditional Queries , I know we should have used when , but for some reasons it's not working
         */
        if($dateFrom != 'null' && $dateFrom != ''){
//            $query->whereDate('from','=' , $dateFrom)->orWhereDate('to' , '=' , $dateFrom);
            $query->whereIntersectsFrom($dateFrom);
        }

        if($dateTo != 'null' && $dateTo != ''){
//            $query->whereDate('to' , '=' , $dateTo)->orWhereDate('from' , '=' , $dateTo);
            $query->whereIntersectsTo($dateTo);
        }

        if($invoice_number != 'null'){
            $query->where('number' , $invoice_number);
        }

        if($reservation_number != 'null'){
            $query->whereHas('reservation', function ($reservation) use($reservation_number) {
                $reservation->where('number' , $reservation_number)->whereNull('deleted_at');
            });
        }

        if($employee_id != 'null'){
            $query->whereHas('creator', function ($user) use($employee_id) {
                $user->where('id' , $employee_id)->whereNull('deleted_at');
            });
        }

        $query->orderByDesc('number');

//        dd($query->toSql());

        $invoices = $query->get();



        if($invoices){

            $invoicesPaginated  = (new CustomPagination($invoices))->paginate(20);

            return json_encode(['invoices' => $invoices , 'invoicesPaginated' => $invoicesPaginated]);
        }


        return json_encode($invoices);
    }

    /**
     * Invoices Report
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function invoicesExcelReport(Request $request){


        $invoices = json_decode($request->get('invoices')) ;

        $holder_array = array();
        foreach ($invoices as $invoice){

            $data[__('Invoice Number')]                 = $invoice->number;
            $data[__('Reservation Number')]             = $invoice->reservation->number ;
            $data[__('Customer name')]                  = $invoice->reservation->customer->name ;
            $data[__('Invoice Amount')]                 = number_format($invoice->data->amount,2) ;
            $data[__('Invoice Creation Date')]                   = date('Y/m/d H:i' , strtotime($invoice->created_at));
            $data[__('Period From')]                        = date('Y/m/d' , strtotime($invoice->from));
            $data[__('Period To')]                       = date('Y/m/d' , strtotime($invoice->to));
            $data[__('Employee Name')]                  = $invoice->creator ?  $invoice->creator->name : '-';
            $holder_array [] = $data;
        }

        return response()->json([
            'status' => 'success' ,
            'data' => $holder_array ,
            'filename' => __('Reservations Invoices Report')
        ]);


    }


    public function servicesTransactions(Request $request){

        # get all employees ids in the current team
        # select all transaction of meta category services
        # where created_by in [ array of employees ids ]

        $query = Transaction::query();
        $employees_ids = Team::find(\auth()->user()->current_team_id)->employees->pluck('id');

        $query->whereIn('created_by' , $employees_ids)->where(function($q) use ($employees_ids) {
            $q->whereJsonContains('meta->category' , 'service')->orWhereJsonContains('meta->category' , 'service-deposit')->whereIn('created_by' , $employees_ids);
        });

        $query->whereNull('deleted_at');


        // filters  applied to our query
        if(\request()->get('dateFrom') != 'null' && \request()->get('dateFrom') != ''){
            $query->where('created_at' , '>=' , \request()->get('dateFrom'));
        }

        if(\request()->get('dateTo') != 'null' && \request()->get('dateTo') != ''){
            $query->where('created_at' , '<=' , \request()->get('dateTo'));
        }


        if(\request()->get('contractNumber') != 'null' && \request()->get('contractNumber') != ''){
            $query->whereHas('reservation' , function($reservation){
               $reservation->where('number' , \request()->get('contractNumber'));
            });
        }

        if(\request()->get('unitId') != 'null' && \request()->get('unitId') != ''){
            $query->whereHas('reservation' , function($reservation){
                $reservation->where('unit_id' , \request()->get('unitId'));
            });
        }

        if(\request()->get('transactionType') != 'null' && \request()->get('transactionType') != ''){
            \request()->get('transactionType') == 'on_reservations' ? $query->where('payable_type' , 'App\\Reservation') :  $query->where('payable_type' , 'App\\Team');
        }

        if(\request()->get('employeeId') != 'null' && \request()->get('employeeId') != ''){
             $query->where('created_by' , \request()->get('employeeId'));
        }


        if(\request()->get('serviceType') != 'null' && \request()->get('serviceType') != ''){
            $query->whereJsonContains('meta->statement' , \request()->get('serviceType'));
        }


        $query->orderBy('id' , 'desc');

        $query->with('payable');

        $servicesTransactions = $query->get();

        $servicesTransactionsPaginated  = (new CustomPagination(ServicesTransactionsResource::collection($servicesTransactions)))->paginate(25);
//        count($servicesTransactions)

        return json_encode(['servicesTransactions' => ServicesTransactionsResource::collection($servicesTransactions) , 'servicesTransactionsPaginated' => $servicesTransactionsPaginated]);


    }



    public function updateTransaction(Request $request){

        $transaction = Transaction::find($request->id);
        if ($request->type == 'deposit') {
            $transaction->amount = $request->amount * 100;
            $transaction->meta = $request->meta;
            $transaction->save();
        } else {
            $transaction->amount = $request->amount * -100;
            $transaction->meta = $request->meta;
            $transaction->save();
        }


        return response()->json([
            'status' => true
        ]) ;

    }

    function your_array_diff($arraya, $arrayb) {

        foreach ($arraya as $keya => $valuea) {
            if (in_array($valuea, $arrayb)) {
                unset($arraya[$keya]);
            }
        }
        return $arraya;
    }

    public function deleteTransaction(Request $request)
    {

        $transaction_id = $request->get('id');

        // Find the target transaction
        $transaction = Transaction::with('service_log' , 'payable')->withTrashed()->find($transaction_id);

        // delete transaction
        $transaction->service_log()->delete();

        $transaction->delete();
        // refresh the wallet
        // $transaction->payable->wallet->refreshBalance();
        // // after deletion balance
        // $balance = abs($transaction->payable->wallet->balance / 100);
        // // return json response
        // return response()->json(['flag' => 'success', 'balance' => $balance, 'transaction' => $transaction]);
    }


    public function fetchUnits(Request $request){
        return response()->json(Unit::whereNull('deleted_at')->where('team_id' , auth()->user()->current_team_id)->get());
    }

    public function getServicesCategoriesNames(){

        $categories = ServicesCategory::where('team_id' , \auth()->user()->current_team_id)->where('status' , 1)->get();
        $names = [];
        if($categories){
            foreach ($categories as $category){
                $names []  = $category->name ;
            }
        }

        $names [] = __('Services');
        return response()->json($names);

    }


    public function serviceTransactionsExcelReport(Request $request){

        // Receive transactions array
        $transactions =   json_decode($request->get('transactions'));
        // exportable array
        $exportableArray = array();

        // holder data
        $data = array() ;

        foreach ($transactions as  $transaction){
            // casting array as object
            $transaction = (object) $transaction ;

            $data[__('Contract Number')] = $transaction->reservation_number ;
            $data[__('The Unit')]             = $transaction->unit_number ;
            $data[__('Amount')]     =  (float) $transaction->sub_total ;
            $data[__('TTX')]                = (float) $transaction->ttx_total;
            $data[__('VAT')]           = (float) $transaction->vat_total;
            $data[__('Total Sum')]               = (float) $transaction->total_with_taxes ;
            $data[__('Received From')]               = $transaction->received_from ;
            $data[__('For')]               = $transaction->for ;
            $data[__('Date Receipt')]               = $transaction->transaction_date ;
            $data[__('Payment Method')]               = __(ucfirst($transaction->payment_method)) ;
            $data[__('Reference Number')]               = $transaction->reference ;
            $data[__('Employee')]               = $transaction->employee ;

            $exportableArray [] = $data ;

        }


        return response()->json(['data' => $exportableArray] , 200) ;
    }

    public function baladyReport(Request $request){

        $dt = Carbon::create($request->get('year') , $request->get('month'));
        $period = CarbonPeriod::create($dt->startOfMonth()->format('Y-m-d'),$dt->endOfMonth()->format('Y-m-d'));
        $reservations = [];
        foreach ($period as $date){
            $collection  =  DB::table('reservations as r')
                ->leftJoin('units as u','r.unit_id' , '=' ,'u.id')
                ->leftJoin('unit_categories as uc' , 'u.unit_category_id' , '=' , 'uc.id')
                ->leftJoin('customer as c','r.customer_id' , '=' ,'c.id')
                ->leftJoin('highlights as h' , 'c.highlight_id' , '=' , 'h.id')
                ->select( 'r.id as rid',
                    'r.date_in as rdi',
                    'r.date_out as rdo',
                    'r.sub_total as amount',
                    'r.number as rnumber',
                    'u.unit_number as unumber',
                    'uc.name as ucname',
                    'c.name as cname'
                )
                ->whereRaw('? between r.date_in and r.date_out', [$date->format('Y-m-d')])
                ->where('r.date_out', '!=', $date->format('Y-m-d'))
                ->whereNotNull('checked_in')
                ->whereNull('r.deleted_at')
                ->where('r.status' , '=' , 'confirmed')
                ->where('r.team_id' , \auth()->user()->current_team_id)
                ->get();

            if(count($collection)){
                foreach ($collection as $r){
                    if(!array_key_exists($r->rid,$reservations)){

                        // reservations dates
                        $dateFromParsed = Carbon::parse($r->rdi);
                        $dateToParsed   = Carbon::parse($r->rdo);
                        // period dates
                        $fromParsed = Carbon::parse($dt->startOfMonth());
                        $toParsed   = Carbon::parse($dt->endOfMonth());
                        $reservationsSkeleton ['from_date'] = $dt->startOfMonth()->format('Y-m-d');
                        $nights = $this->nightsForBaladyReport($r, $fromParsed->format('Y-m-d'), $toParsed->format('Y-m-d'));

                        if($r->rdo > $dt->endOfMonth()->format('Y-m-d')){
                            $dateCompared = $dt->endOfMonth()->format('Y-m-d') ;
                        }else{
                            $dateCompared = $r->rdo ;
                        }
                        if ($fromParsed->lessThanOrEqualTo($dateFromParsed)) {
                            $reservationsSkeleton ['from_date'] = $dateFromParsed->format('Y/m/d');
                            if ($nights == 1)
                                $dateCompared = $r->rdi;
                        } else {
                            $reservationsSkeleton ['to_date'] = $r->rdi;
                        }

                        if ($toParsed->greaterThanOrEqualTo($dateToParsed) and $nights != 1) {
                            if($dateToParsed == $fromParsed){
                                $dateCompared = $dateToParsed ;
                            }else{
                                $dateCompared = $dateToParsed->subDay();
                            }
                            $nights = $nights;

                        }

                        $reservationsSkeleton ['to_date'] =   date('Y/m/d' , strtotime($dateCompared));
                        $reservationsSkeleton ['nights_count'] =  $nights;

                        if ($reservationsSkeleton ['from_date'] == $reservationsSkeleton ['to_date']) {
                            $reservationsSkeleton ['nights_count'] = 1;
                        }

                        $from = Carbon::parse($reservationsSkeleton ['from_date']);
                        $to = Carbon::parse($reservationsSkeleton ['to_date']);
                        $nights =  $to->diff($from)->days + 1;



                        $globalStart = new \DateTime($r->rdi);
                        $globalEnd = new \DateTime($r->rdo);
                        $globalNights = $globalEnd->diff($globalStart)->format('%a');
                        $price_per_night = $r->amount / $globalNights ;

                        $std = new stdClass();
                        $std->rid = $r->rid;
                        $std->rdi = $from->format('m/d/Y');
                        $std->rdo = $to->format('m/d/Y');
                        $std->amount =  floatval($price_per_night * $nights) ;
                        $std->rnumber = $r->rnumber;
                        $std->unumber = $r->unumber;
                        $std->ucname = json_decode($r->ucname);
                        $std->cname = $r->cname;
                        $std->nights = $nights;
                        $reservations [$r->rid] = $std;
                    }

                }
            }

        }

        $data = (new CustomPagination(array_values($reservations)))->paginate(25);
        $ids = collect(array_values($reservations))->pluck('rid')->toArray();
        $total_amount = number_format(collect(array_values($reservations))->sum('amount'),2);
        return response()->json(['collection' => $data , 'all_data' => array_values($reservations) , 'total_amount' => $total_amount]);
    }

    function nightsForBaladyReport($reservation,$dateFrom,$dateTo){
        $date_in = new \DateTime($dateFrom);
        $date_out = new \DateTime($dateTo);

        $days = $date_out->diff($date_in)->days;

        if ($days == 0){
            $days = 1 ;
            return $days;
        }

        $reservation = Reservation::find($reservation->rid);
        return $days > $reservation->nights ?   $days  : $reservation->nights ;
    }

    public function baladyReportExcel(Request $request){

        $result  = array();
        foreach ($request->all() as $reservation){

            $reservation = (object) $reservation;
            $data[__('Unit number')]          =     $reservation->unumber;
            $data[__('Date in')]    = $reservation->rdi;
            $data[__('Date out')]     = $reservation->rdo ;
            $data[__('Amount')]     = number_format($reservation->amount,2) ;
            $data[__('Customer Name')]     = $reservation->cname ;
            $data[__('Reservation Number')]     = $reservation->rnumber ;
            $data[__('Unit type')]     = $reservation->ucname ? $reservation->ucname[app()->getLocale()] : '-' ;
            $data[__('Notes')] = '';
            $result [] = $data ;
        }


        return response()->json($result);
    }

    public function searchWithTimeSetting()
    {
         return response()->json(['time_12hrs_enabled' => Settings::get('time_12hr') ? true : false]);
    }


    public function unitsOccupiedReportAll(Request $request){

        $user_id = $request->header('x-user');

        $team_ids = DB::table('team_users')->where('user_id', $user_id)->pluck('team_id')->toArray();

        $data = [];
        foreach($team_ids as $team_id){

            $team = Team::find($team_id);

            // get all unit_categories with team_id = $team_id
            $unitCategory = DB::table('unit_categories')->where('team_id', $team_id)->pluck('id')->toArray();
            $booked_units = $this->getBookedUnits($unitCategory);


            $counts = DB::table('units')
                ->selectRaw("
                    COUNT(*) as total_units,
                    SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) as maintenance_count,
                    SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as cleaning_count
                ")
                ->where('team_id', $team_id)
                ->whereNull('deleted_at')
                ->first();

            $total_units = $counts->total_units;
            $maintenance_count = $counts->maintenance_count;
            $cleaning_count = $counts->cleaning_count;


            $rooms_available = $total_units - ($booked_units + $maintenance_count + $cleaning_count);

            $units_count = $total_units;

            $data[] = [
                'team_id' => $team->name,
                'unit_count' => $units_count,
                'available_units' => $rooms_available,
                'cleaning' => (int)$cleaning_count,
                'maintance' => (int)$maintenance_count,
                'booked' => $booked_units,

            ];

        }

        return $data;

    }
    public function getBookedUnits($unitCategory)
    {

        $now = Carbon::now();
        $unit_ids = DB::table('units')
            ->whereIn('unit_category_id', $unitCategory)
            ->pluck('id')
            ->toArray();

        if (empty($unit_ids)) {
            return 0;
        }

        $count = DB::table('reservations as r')
            ->leftJoin('customer as c', 'r.customer_id', '=', 'c.id')
            ->leftJoin('highlights as h', 'c.highlight_id', '=', 'h.id')
            ->select(
                'r.unit_id',
                DB::raw('count(r.id) as reservation_count'),
                'r.checked_in as rchi',
                'r.date_in as rdi',
                'r.date_out as rdo',
                'r.status',
                'c.name as cname',
                'h.name as hlabel',
                'h.color as hcolor'
            )
            ->whereIn('r.unit_id', $unit_ids)
            ->whereRaw('? between r.date_in and r.date_out', [Carbon::parse($now)->format('Y-m-d')])
            ->where('r.date_out', '!=', Carbon::parse($now)->format('Y-m-d'))
            ->whereNull('r.checked_out')
            ->whereNull('r.deleted_at')
            ->whereNotIn('r.status', ['timeout', 'canceled'])
            ->groupBy('r.unit_id')
            ->havingRaw('count(r.id) > 0')
            ->pluck('r.unit_id')
            ->count();

        return $count;
    }



}
