<?php

namespace App\Http\Controllers;

use App\Team;
use App\Unit;
use App\User;
use App\Customer;
use Carbon\Carbon;
use App\Reservation;
use App\Transaction;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Config;
use Spatie\QueryBuilder\AllowedFilter;
use Laravel\Nova\Http\Requests\NovaRequest;
use niklasravnsborg\LaravelPdf\Facades\Pdf;
use App\Http\Resources\Reports\ReservationSourcesResource;
use App\Http\Resources\OverrideNovaResources\OverrideUnitResource;

class ReportsPrintController extends Controller
{

    public function __construct()
    {
        //        $this->middleware('nova');
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function transactionsReportPrint(Request $request)
    {

        $type = $request->get('type');
        $data = json_decode($request->get('query'), true);
        $data['team_id'] = $request->get('team_id');
        $client = new Client(['base_uri' => config('app.fandaqah_api_url')]);
        $engine = $client->get('/api/v1/transactions/print', ['query' => $data]);
        if ($request->has('management')) {
            $title = $type == 'deposit' ? __('Deposit Transactions - Financial Management') : __('Withdraw Transactions - Financial Management');
        } else {
            $title = $type == 'deposit' ? __('Deposit Report') : __('Withdraw Report');
        }

        $transactions = json_decode($engine->getBody()->getContents(), true);
        $calculations = json_decode($request->get('calculations'));
        $team = Team::find($data['team_id']);
        $dateFrom = isset(json_decode($request->get('query'), true)['filter[by_date_from]']) ? json_decode($request->get('query'), true)['filter[by_date_from]'] : null;
        $dateTo = isset(json_decode($request->get('query'), true)['filter[by_date_to]']) ? json_decode($request->get('query'), true)['filter[by_date_to]'] : null;

        return view('print.transactions-report')->with(['transactions' => $transactions, 'calculations' => $calculations, 'title' => $title, 'type' => $type, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'locale' => app()->getLocale(), 'team' => $team]);
    }
    public function serviceTransactionsReportPrint(Request $request)
    {
        $client = new Client(['base_uri' => config('app.fandaqah_api_url')]);
        $data = json_decode($request->get('query'), true);
        $data['team_id'] =  $request->get('team_id');
        $calculations = json_decode($request->get('calculations'), true);;
        $title = $request->has('coming_from') ? __('Pos Operations')  : __('Services Report');
        $from = $request->get('from', null);
        $data['from'] =  $from;
        $engine = $client->get("/api/v1/services-logs/print-service-logs", [
            'query' => $data,
        ]);
        $servicesLogs = json_decode($engine->getBody()->getContents(), true)['data'];
        // $calculationsEngine = $client->get('/api/v1/services-logs/get-totals', [
        //     'query' => $data,
        // ]);
        // $calculations = json_decode($calculationsEngine->getBody()->getContents(), true);
        $dateFrom = isset($data['filter[by_created_from]']) ? $data['filter[by_created_from]'] : null;
        $dateTo = isset($data['filter[by_created_to]']) ? $data['filter[by_created_to]'] : null;

        return view('print.services-logs-report')->with(['servicesLogs' => $servicesLogs, 'calculations' => $calculations, 'title' => $title, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'locale' => app()->getLocale()]);
    }

    public function customersReport(Request $request)
    {

        $data = json_decode($request->get('query'), true);
        $data['team_id'] =  $request->get('team_id');


        $client = new Client(['base_uri' => config('app.fandaqah_api_url')]);
        $engine = $client->get('/api/v1/customers/print', [
            'query' => $data,
        ]);

        $customers = json_decode($engine->getBody()->getContents(), true)['data'];
        return view('print.customers-report')->with(['customers' => $customers, 'title' => __('Customers Movement Report')]);
    }

    public function unitsReservationsReport(Request $request)
    {

        $data = json_decode($request->get('query'), true);
        $data['team_id'] =  $request->get('team_id');

        $client = new Client(['base_uri' => config('app.fandaqah_api_url')]);
        $engine = $client->get('/api/v1/units/units-reservations-print', [
            'query' => $data,
        ]);

        $reservations = json_decode($engine->getBody()->getContents(), true)['data'];
        return view('print.units-movement-report')->with(['reservations' => $reservations, 'title' => __('Units Movement Report')]);
    }


    public function safeMovementReport(Request $request)
    {

        $data = json_decode($request->get('query'), true);
        $data['team_id'] = $request->get('team_id');


        $client = new Client(['base_uri' => config('app.fandaqah_api_url')]);
        $engine = $client->get('/api/v1/transactions/print', [
            'query' => $data
        ]);

        $dateFrom = isset($data['filter[by_date_from]']) ? Carbon::parse($data['filter[by_date_from]'])->format('Y/m/d H:i') : null;
        $dateTo = isset($data['filter[by_date_to]']) ? Carbon::parse($data['filter[by_date_to]'])->format('Y/m/d H:i') : null;

        $team = Team::find($data['team_id']);
        $safeMovementData = json_decode($engine->getBody()->getContents(), true);
        $statistics = json_decode($request->get('statistics'));
        $request->get('type') == 'safe_withdraw_report' ? $title = __('Safe Movement Report For Withdraw Transactions') : $title = __('Safe Movement Report For Deposit Transactions');
        return view('print.safe-movement-report')->with(['safeMovementData' => $safeMovementData, 'title' => $title, 'type' =>  $request->get('type'), 'statistics' => $statistics, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'locale' => app()->getLocale(), 'team' => $team]);
    }

    public function safeMovementReportAll(Request $request)
    {


        $client = new Client(['base_uri' => config('app.fandaqah_api_url')]);

        $depositStatistics = json_decode($request->get('depositStatistics'));
        $depositQuery = json_decode($request->get('depositQuery'), true);
        $depositQuery['team_id'] = $request->get('team_id');

        $depositEngine = $client->get('/api/v1/transactions/print', [
            'query' => $depositQuery
        ]);

        $depositData = json_decode($depositEngine->getBody()->getContents(), true);


        $withdrawStatistics = json_decode($request->get('withdrawStatistics'));
        $withdrawQuery = json_decode($request->get('withdrawQuery'), true);
        $withdrawQuery['team_id'] = $request->get('team_id');

        $withdrawEngine = $client->get('/api/v1/transactions/print', [
            'query' => $withdrawQuery
        ]);

        $withdrawData = json_decode($withdrawEngine->getBody()->getContents(), true);

        $generalStatistics = json_decode($request->get('generalStatistics'));
        // merging transactions
        $transactions = array_merge($depositData, $withdrawData);

        $team = Team::find($request->get('team_id'));

        $title = __('Safe Movement Report');
        return view('print.safe-movement-report-all')->with(['title' => $title, 'depositStatistics' => $depositStatistics,  'withdrawStatistics' => $withdrawStatistics, 'generalStatistics' => $generalStatistics, 'transactions' => $transactions, 'locale' => app()->getLocale(), 'team' => $team]);
    }

    public function safeMovementReportStatisticsOnly(Request $request)
    {
        $depositStatistics = json_decode($request->get('depositStatistics'));
        $withdrawStatistics = json_decode($request->get('withdrawStatistics'));
        $generalStatistics = json_decode($request->get('generalStatistics'));

        $team = auth()->user()->currentTeam;
        return view('print.safe-movement-report-statistics-only')->with(['depositStatistics' => $depositStatistics,  'withdrawStatistics' => $withdrawStatistics, 'generalStatistics' => $generalStatistics, 'locale' => app()->getLocale(), 'team' => $team]);
    }

    public function receiptReport(Request $request)
    {

        // dd($request->all());
        $depositCalculations = json_decode($request->get('depositCalculations'));
        $withdrawCalculations = json_decode($request->get('withdrawCalculations'));
        $employeeName =  $request->get('employeeId') ? User::find($request->get('employeeId'))->name : null;
        $receiptEmployee =  $request->get('receiptEmployee') ? $request->get('receiptEmployee') : null;
        $dateFrom =  $request->get('dateFrom') ?  $request->get('dateFrom') : null;
        $dateTo =  $request->get('dateTo') ? $request->get('dateTo') : null;

        return view('print.receipt-report')->with(['depositCalculations' => $depositCalculations,  'withdrawCalculations' => $withdrawCalculations, 'employeeName' => $employeeName, 'receiptEmployee' => $receiptEmployee, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo]);
    }

    public function revenueTaxFeesReport(Request $request)
    {

        $revenueTaxFeesData = json_decode($request->get('all_data'));
        $revenueTaxFeesStatistics = json_decode($request->get('calculations'));
        $hasTtx = json_decode($request->get('hasTtx'));
        $dateFilter =  json_decode($request->get('dateFilter'));
        $ewa_status = $request->get('ewa_status');
        $reservation_status = $request->get('reservation_status');
        return view('print.revenue-tax-fee-report')->with(['revenueTaxFeesData' => $revenueTaxFeesData, 'revenueTaxFeesStatistics' => $revenueTaxFeesStatistics, 'title' => __('Revenues & Taxes , Fees'), 'hasTtx' => $hasTtx, 'dateFilter' => $dateFilter , 'ewa_status' => $ewa_status , 'reservation_status' => $reservation_status]);
    }
public function printGuests(Request $request , $id)
    {
        $reservation_id  = $id;
        // get if there is guests for this reservation
        $reservation = Reservation::find($reservation_id);
        $guests = $reservation->reservation_guests;
        $locale = app()->getLocale();
        return view('print.guests')->with(['guests' => $guests , 'reservation' => $reservation , 'locale' => $locale]);

    }
    public function reservationResources(Request $request)
    {


        $reservations = QueryBuilder::for(Reservation::class)
            ->with(['wallet', 'company', 'unit', 'customer', 'source', 'groupReservationBalanceMapper'])
            ->allowedFilters([
                AllowedFilter::scope('by_source'),
                AllowedFilter::scope('by_date_from'),
                AllowedFilter::scope('by_date_to')
            ])
            ->where('status', '!=', 'awaiting-payment')
            ->where('team_id', auth()->user()->current_team_id)
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->get();

        return view('print.reservation-resources')->with(['reservations' => $reservations, 'total' => $request->get('total'), 'title' => __('Reservation Resources Report'), 'locale' => app()->getLocale()]);
    }


    public function reservationsReport(Request $request)
    {
        $reservationsData = json_decode($request->get('reservationsData'));

        $reservations = Reservation::whereIn('id', $reservationsData->reservations_ids)->get();


        return view('print.reservations-report')->with(['reservations' => $reservations, 'statistics' => $reservationsData, 'title' => __('Reservations Report')]);
    }

    public function reservationsPrintReport(Request $request)
    {
        $ids = json_decode($request->get('ids'));
        $statistics = json_decode($request->get('statistics'));
        $canShowStatistics = json_decode($request->get('canShowStatistics'));
        $reservations = Reservation::with('wallet', 'transactions', 'company', 'groupReservationBalanceMapper', 'source')
            ->whereIn('id', $ids)
            ->orderByDesc('id')
            ->get();
        return view('print.reservations-report')->with([
            'reservations' => $reservations,
            'statistics' => $statistics,
            'title' => __('Reservations Report'),
            'canShowStatistics' => $canShowStatistics,
            'locale' => app()->getLocale()
        ]);
    }

    public function reservationsServicesIncludedPrintReport (Request $request) {
        $ids = json_decode($request->get('ids'));
        $reservations = Reservation::with('wallet', 'transactions', 'company', 'groupReservationBalanceMapper', 'source', 'reservationFreeServices')
            ->whereIn('id', $ids)
            ->has('reservationFreeServices', '>', 0)
            ->orderByDesc('id')
            ->get();
        return view('print.reservations-services-included-report')->with([
            'reservations' => $reservations,
            'title' => __('Reservations With Services Included Report'),
            'locale' => app()->getLocale()
        ]);
    }


    public function reservationSummary(Request $request, $id)
    {

        $decode  = Hashids::decode($id);
        $id      = reset($decode);
        $type = $request->get('type');
        $print = true;
        if ($type == 'embed') {
            $print = false;
        }
        $reservation = Reservation::with(['team', 'wallet', 'unit', 'customer', 'reservation_guests', 'creator', 'services', 'comments', 'depositInsuranceTransactions'])->find($id);
        $reservationServicesTransactions = $reservation->services;
        $reservationDepositWithdrawTransactions = $reservation->transactions;
        $locale = Config::get('app.locale');
        return view('print.reservation-summary')->with([
            'reservation' => $reservation,
            'locale' => $locale,
            'reservationServicesTransactions' => $reservationServicesTransactions,
            'reservationDepositWithdrawTransactions' => $reservationDepositWithdrawTransactions,
            'print' => $print
        ]);
    }

    public function contractsReport(Request $request)
    {

        $reservations = QueryBuilder::for(Reservation::class)
            ->with(['unit', 'customer', 'creator', 'source', 'groupReservationBalanceMapper'])
            ->allowedFilters([
                AllowedFilter::scope('by_unit_number'),
                AllowedFilter::scope('by_creator'),
                AllowedFilter::scope('by_created_from'),
                AllowedFilter::scope('by_created_to')
            ])
            ->where('status', 'confirmed')
            ->where('team_id', auth()->user()->current_team_id)
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->get();
        return view('print.employees-contracts')->with(['reservations' => $reservations, 'total' => $request->get('total'), 'title' => __('Employees Contracts'), 'locale' => app()->getLocale()]);
    }


    public function invoicesReportPrint(Request $request)
    {

        $locale = Config::get('app.locale');

        $ids = ['ids' => json_decode($request->get('ids'))];
        $client = new Client(['base_uri' => config('app.fandaqah_api_url')]);
        $engine = $client->post('/api/v1/reservations/invoices-report-print', [
            'form_params' => $ids
        ]);

        $invoices = json_decode($engine->getBody()->getContents(), true)['data'];
        return view('print.invoices_report')->with(['invoices' => $invoices, 'locale' => $locale]);
    }

    public function managerFlashPrint(Request $request) {
        $locale = Config::get('app.locale');
        $data   = (object) json_decode($request->get('data'));
        return view('print.manager_flash_report')->with(['result' => $data, 'locale' => $locale]);
    }

    public function houseKeepingDiscrepanciesPrint(Request $request) {
        $locale     = Config::get('app.locale');
        $date       = Carbon::now()->format('Y-m-d');
        $data       = (object) json_decode($request->get('data'));
        $headers    = [
            __('Room', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Unit Name', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Room Type', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Room Status', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('HK Status', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Discrepancy', [],  $locale == 'ar' ? 'ar' : 'en'),
        ];
        return view('print.house_keeping_discrepancies_report')->with(['result' => $data, 'headers' => $headers, 'locale' => $locale, 'date' => $date]);
    }

    public function historyForecastReportPrint (Request $request) {
        $locale     = Config::get('app.locale');
        $data       = (object) json_decode($request->get('data'));
        $headers    = [
            __('Date', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Occupied Units', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Arrival Rooms', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Complimentary Rooms', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('House Use Rooms', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Deducted Individuals', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Deducted Group', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Occupied Percentage', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Revenue', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Average Rate', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Departure Rooms', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Day Use Rooms', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('No Show Rooms', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('OOO Rooms', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Adlts and Chlds', [],  $locale == 'ar' ? 'ar' : 'en')
        ];
        return view('print.history_forecast_report')->with(['result' => $data, 'headers' => $headers, 'locale' => $locale]);

    }


    public function paidOutsReportPrint (Request $request) {
        $locale     = Config::get('app.locale');
        $data       = (object) json_decode($request->get('data'));
        $headers    = [
            __('Date Time', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Room', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Room Number', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Customer', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('TRN Group', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('TRN Code', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Description', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Credit', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Debit', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Payment Type', [],  $locale == 'ar' ? 'ar' : 'en'),
        ];
        return view('print.paid_outs_report')->with(['result' => $data, 'headers' => $headers, 'locale' => $locale]);

    }

    public function trialBalanceReportPrint(Request $request) {
        $locale     = Config::get('app.locale');
        $data       = (object) json_decode($request->get('data'));
        $headers    = [
            __('Reservations Revenue', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Services Reveunue', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Total Revenue', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Ewa', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Vat On Reservations', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Vat On Services', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Total Non Revenue', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Cash', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Bank Transfer', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Mada', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Credit', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Credit Payment', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Total Payment', [],  $locale == 'ar' ? 'ar' : 'en'),
            __('Total Transaction', [],  $locale == 'ar' ? 'ar' : 'en'),
        ];
        $currency   = __('SAR', [], $locale);
        return view('print.trial_balance_report')->with(['data' => $data, 'headers' => $headers, 'locale' => $locale, 'currency' => $currency]);

    }

    public function baladyReport(Request $request)
    {
        $data = json_decode($request->get('all_data'));
        return view('print.balady_report')->with(['data' => $data, 'total_amount' => json_decode($request->get('total'))]);
    }

    public function promissoriesPrint(Request $request)
    {

        $locale = Config::get('app.locale');

        $ids = ['ids' => json_decode($request->get('ids'))];
        $client = new Client(['base_uri' => config('app.fandaqah_api_url')]);
        $engine = $client->post('/api/v1/promissories/print', [
            'form_params' => $ids
        ]);

        $promissories = json_decode($engine->getBody()->getContents(), true);
        return view('print.promissories')->with(['promissories' => $promissories, 'locale' => $locale]);
    }


    public function creditNotesPrint(Request $request)
    {

        $locale = Config::get('app.locale');

        $query = json_decode($request->get('query'), true);
        $query['team_id'] = $request->get('team_id');

        $client = new Client(['base_uri' => config('app.fandaqah_api_url')]);
        $engine = $client->get('/api/v1/reservations/credit-notes-report-print', [
            'query' => $query
        ]);

        $credit_notes = json_decode($engine->getBody()->getContents(), true)['data'];
        return view('print.credit_notes_report')->with(['credit_notes' => $credit_notes, 'locale' => $locale]);
    }

    public function printUnitHousingUnits(Request $request)
    {

        $statuses = [
            Unit::STATUS_UNDER_CLEANING   =>  __('under cleaning'),
            Unit::STATUS_UNDER_MAINTENANCE    =>  __('under maintenance'),
        ];
        $title = __('Units Status');
        $units = json_decode($request->get('units'));
        $filter_status = $request->get('unit_status');
        $selected_date = $request->get('selected_date');
        return view('print.unit_housing_print', [
            'units' => $units,
            'statuses' => $statuses,
            'title' => $title,
            'filter_status' => $filter_status,
            'selected_date' => $selected_date,
            'locale' => app()->getLocale()
        ]);
    }

    public function exportExcelUnitHousingUnits(Request $request)
    {
        $units = $request->all();
        $container = [];
        if (count($units)) {
            foreach ($units as $unit) {
                $data[__('Unit Number')]         = $unit['unit_number'];
                $data[__('Unit name')]         = $unit['name'];
                if ($unit['status'] != 1) {
                    if ($unit['status'] == 2) {
                        $last_cleaning = getLastCleaning($unit['id']);
                        if($last_cleaning){
                            $data[__('Date')]   = $last_cleaning ?  Carbon::parse($last_cleaning->created_at)->format('Y-m-d H:i') : Carbon::parse(now())->format('Y-m-d H:i');
                        }else{
                            $last_cleaning_with_completed_at = getLastCleaning($unit['id'],true);
                            if($last_cleaning_with_completed_at){
                                $data[__('Date')]   = $last_cleaning_with_completed_at ?  Carbon::parse($last_cleaning_with_completed_at->created_at)->format('Y-m-d H:i') : Carbon::parse(now())->format('Y-m-d H:i');
                            }
                        }
                    }else{
                        $last_maintenance = getLastMaintenance($unit['id']);
                        $data[__('Date')]   = $last_maintenance ?  Carbon::parse($last_maintenance->created_at)->format('Y-m-d H:i') : Carbon::parse(now())->format('Y-m-d H:i');
                    }
        
                }

                $container[]  = $data;
            }
        }
        return response()->json([
            'status' => 'success',
            'msg' => __('Data exported successfully'),
            'data' => $container
        ]);
    }

    public function unitsPrintReport(Request $request){
        $alphabet = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"];
        $units = Unit::where('team_id' , auth()->user()->current_team_id)
            ->with('unit_category')
            ->whereNull('deleted_at')
            ->get()
            ->sortBy(function ($i) use ($alphabet) {
                return trim(str_replace($alphabet, '', $i['unit_number']));
            });
        return view('print.units-print-report',['units' => $units]);    
    }

    public function unitsOccupiedReportPrint(Request $request)
    {

        $data = json_decode($request->get('query'), true);
        $data['team_id'] = $request->get('team_id');


        $client = new Client(['base_uri' => config('app.fandaqah_api_url')]);
        $engine = $client->get('/api/v1/reports/occupied', [
            'query' => $data
        ]);

        $dateFrom = isset($data['filter[by_date_from]']) ? Carbon::parse($data['filter[by_date_from]'])->format('Y/m/d H:i') : null;
        $dateTo = isset($data['filter[by_date_to]']) ? Carbon::parse($data['filter[by_date_to]'])->format('Y/m/d H:i') : null;

        $team = Team::find($data['team_id']);
        $occupiedData = json_decode($engine->getBody()->getContents(), true);
        $title = __('Occupieds report');
        return view('print.occupied-report')->with(['occupiedData' => $occupiedData['data'], 'title' => $title , 'dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'locale' => app()->getLocale(), 'team' => $team , 'occupied_percentage' => $occupiedData['meta']['total_occupied_percentage']]);
    }

}
