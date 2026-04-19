<?php

namespace App\Http\Controllers;

use Log;
use App\Team;
use App\Unit;
use App\User;
use Exception;
use App\Company;
use App\Customer;
use Carbon\Carbon;
use App\Promissory;
use App\Reservation;
use App\Transaction;
use GuzzleHttp\Client;
use App\ServiceLogNote;
use App\DigitalSignature;
use App\GroupReservation;
use App\Handlers\Settings;
use App\InvoiceCreditNote;
use App\ReservationInvoice;
use App\ReservationContract;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\Foreach_;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Config;
use App\Services\ZATCA\Phase1\Tags\Seller;
use niklasravnsborg\LaravelPdf\Facades\Pdf;
use Illuminate\Database\Eloquent\Collection;
use App\Services\ZATCA\Phase1\GenerateQrCode;
use App\Services\ZATCA\Phase1\Tags\TaxNumber;
use App\Services\ZATCA\Phase1\Tags\InvoiceDate;
use App\Jobs\SendContractToSignedDigitalyViaSMS;
use App\Services\ZATCA\Phase1\Tags\InvoiceTaxAmount;
use App\Services\ZATCA\Phase1\Tags\InvoiceTotalAmount;

class InvoiceController extends Controller
{

    public function __construct()
    {
        $this->middleware('nova')->except(['getGeneralInvoicePrint', 'getContractPrint' , 'getGroupReservationInvoice' ,'getInvoicePublic' , 'getSubInvoicePublic' , 'getContractPublic' , 'getContractPrintPublic' , 'getSubInvoicePrintPublic' , 'getGeneralInvoicePublic' , 'getPosPrintRecordPublic' ,  'getGroupReservationInvoicePublic','getGroupReservationContractPublic','getGroupReservationSummaryPublic' , 'getCreditNotePrint' , 'getCreditNotePublic','getGroupReservationInvoicePrototype','getGroupInvoicePrintPublic','getGroupInvoiceCreditNotePrintPublic', 'getPromissoryPrint']);
    }



    /**
     * --------------------------------------------------------- Historical Invoices ------------------------------------------------------------
     */



    /**
     * get Invoice
     *
     * @param $id
     * @return mixed
     */
    public function getGeneralInvoicePrint(Request $request , $id)
    {
        $decode  = Hashids::decode("$id");
        $id      = reset($decode);

        $locale = $request->get('lang');
        app()->setLocale($locale);

        $reservation = Reservation::with('team','customer', 'unit', 'creator', 'comments' , 'wallet')->findOrFail($id);
        return view('print.general_invoice', [
            'reservation' => $reservation,
            'locale' => $locale ,
            'print' => $request->has('type') && $request->get('type') == 'embed' ? false : true
        ]);
    }





    function getGeneralInvoicePdf($id){
        $decode  = Hashids::decode("$id");
        $id      = reset($decode);
        $reservation = Reservation::withOutGlobalScope('team_id')->find($id);
        $locale = app()->getLocale();
        $title = __('Invoice') ;
        $config = ['instanceConfigurator' => function ($mpdf) {
            $stylesheet = file_get_contents(storage_path('pdf/pdf.css'));
            $mpdf->WriteHTML($stylesheet, 1);
            $mpdf->autoScriptToLang = true;
            $mpdf->baseScript = 1;
            $mpdf->autoVietnamese = true;
            $mpdf->autoArabic = true;
            $mpdf->useKerning=true;
            $mpdf->autoLangToFont = true;

        }];
//        $pdf = Pdf::loadView('pdf/contract', ['r' => $reservation, 'locale' => $locale , 'title' => $title], [], $config);
        $pdf = Pdf::loadView('pdf/general_invoice', ['reservation' => $reservation, 'locale' => $locale , 'title' => $title], [], $config);

        return $pdf->stream("{$id}-invoice.pdf");
    }



    /**
     * Public get Invoice
     *
     * @param $id
     * @return mixed
     */
    function getGeneralInvoicePublic(Request $request , $id)
    {

        $decode  = Hashids::decode("$id");
        $id      = reset($decode);
        $reservation = Reservation::withOutGlobalScope('team_id')->with('customer', 'unit', 'creator', 'comments' , 'wallet')->findOrFail($id);
        
        // Avoid exception with another authenticated team trying to open scoped reservation with unit
        $unit = Unit::withOutGlobalScope('team_id')->find($reservation->unit_id);
        app()->setlocale($request->get('lang'));
        return view('print.general_invoice', [
            'reservation' => $reservation,
            'locale' => $request->get('lang') ,
            'print' => false,
            'unit' => $unit ?? null
        ]);
    }






    /**
     * --------------------------------------------------------- Historical Invoices ------------------------------------------------------------
     */




    /**
     * get Invoice
     *
     * @param $id
     * @return mixed
     */
    function getInvoicePrint(Request  $request , $id)
    {
        $decode  = Hashids::decode("$id");
        $id      = reset($decode);
        $locale = app()->getLocale();
        // $invoice = ReservationInvoice::with('reservation' , 'reservation.team' , 'reservation.unit' , 'reservation.customer' , 'reservation.creator' , 'reservation.comments')->findOrFail($id);
        $invoice = ReservationInvoice::withoutGlobalScopes()->with('unit')->findOrFail($id);
        $reservation = Reservation::withoutGlobalScopes()->with('team' , 'customer' , 'creator' )->find($invoice->reservation_id);
        $unit = Unit::withoutGlobalScopes()->find($reservation->unit_id);
        $vat_for_services = 0;
        if(isset($invoice->data['services'])){
            foreach ($invoice->data['services'] as $service) {
                if(isset($service['vat'])){
                    $vat_for_services += $service['vat'];
                }
            }
        }

        $total_vat = $invoice->data['vat'] + $vat_for_services;
        // E-Invoice Compatiable with ZACAH phase 1
        $displayQRCodeAsBase64 = GenerateQrCode::fromArray([
            new Seller($invoice->reservation->team->name), // seller name
            new TaxNumber(getSettingItem($invoice->reservation->team_id , 'tax_number') ? getSettingItem($invoice->reservation->team_id , 'tax_number') : '-'), // seller tax number
            new InvoiceDate($invoice->created_at->toIso8601ZuluString()), // invoice date as Zulu ISO8601 @see https://en.wikipedia.org/wiki/ISO_8601
            new InvoiceTotalAmount($invoice->data['amount']), // invoice total amount
            new InvoiceTaxAmount(number_format($total_vat,2)) // invoice tax amount
        ])->render();

             // created to solve the problem of the vat and ewa then will be removed after the problem is solved
    if($invoice->data['vat'] == $invoice->data['ewa'] && $invoice->data['vat'] != 0){

        $invoice->data = [ 'sub_total' => $invoice->data['sub_total'],
        'vat' => $invoice->data['amount'] - ($invoice->data['ewa'] + $invoice->data['sub_total']),
        'ewa' => $invoice->data['ewa'],
        'ttx' => $invoice->data['ttx'],
        'total_price' => $invoice->data['total_price'],
        'nights' => $invoice->data['nights'],
        'servicesSum' => $invoice->data['servicesSum'],
        'services' => $invoice->data['services'],
        'transactions_ids' => $invoice->data['transactions_ids'],
        'amount' => $invoice->data['amount'],
        ];
        $invoice->save();
                    }

        // return view('print.invoice', [
        return view(getSettingItem($reservation->team_id , 'print_invoice_in_two_lang') ? 'print.new-template-invoice' : 'print.invoice', [
            'invoice' => $invoice,
            'reservation' => $reservation,
            'unit' => $unit,
            'locale' => $locale ,
            'print' => $request->has('type') && $request->get('type') == 'embed' ? false :  true,
            'qrCode' => $displayQRCodeAsBase64,
            'showFandaqahQr' => !$invoice->data['vat'] ? true : false
        ]);
    }

    /**
     * get Sub Invoice Print
     *
     * @param $id
     * @return mixed
     */
    function getSubInvoicePrint(Request $request , $id)
    {
        if(!$request->has('typeFrame')){
            $decode  = Hashids::decode("$id");
            $id      = reset($decode);
        }

        $transaction = Transaction::findOrFail(['id' => $id])->first();
        $type = $transaction->type ;
        if($type == 'withdraw'){
            $title = __('Withdraw Transaction') ;
        }else{
            $title = __('Deposit Transaction') ;
        }

        $reservation = Reservation::with('customer', 'unit', 'creator', 'comments')->find($transaction->payable_id);
        $main_reservation_number = null;
        if($reservation->reservation_type == 'group'){
            if(is_null($reservation->attachable_id)){
                $main_reservation = $reservation;
            }else{
                $main_reservation = Reservation::find($reservation->attachable_id);
            }

            $main_reservation_number = $main_reservation->number;
        }
        $locale = app()->getLocale();

        return view('print.sub-invoice', [
            'r' => $reservation,
            't' => $transaction,
            'locale' => $locale ,
            'title' => $title ,
            'contract_number' => $reservation->reservation_type == 'group' ?  $main_reservation_number : $reservation->number,
            'print' => $request->has('typeFrame') && $request->get('typeFrame') == 'embed' ? false :  true
        ]);
    }

    function getPosPrintRecord(Request $request,$id){
        $client = new Client(['base_uri' => config('app.fandaqah_api_url')]);
        $params = [
            'query' => [
                'id' => $id
            ]
        ];
        $engine = $client->get('/api/v1/services-logs/print-record' , $params);
        $serviceObj  = json_decode($engine->getBody()->getContents(), true)['data'];
       
        $zatca = null;
        if(auth()->user()->getSupplierEGS() != null) {
           $service_log_id = $serviceObj['service_log_id'];
           $service_log_notes = ServiceLogNote::where('service_log_id', $service_log_id)->latest('created_at')->first();
           if(isset($service_log_notes->payload)) {
            $zatca = (object) $service_log_notes->payload;
           }
        }

        return view('print.pos-print', [
            'team' => Team::find(auth()->user()->current_team_id),
            'serviceObj' => $serviceObj,
            'locale' => app()->getLocale(),
            'title' => $serviceObj['type'] == 'withdraw' ? $title = __('Loaded On Reservation') :  $title = __('Loaded On Services Revenue')  ,
            'print' => $request->has('type') ? false :  true,
            'zatca' => $zatca
        ]);
    }

    public function getPosPrintRecordPublic(Request $request,$id){
        $client = new Client(['base_uri' => config('app.fandaqah_api_url')]);
        $params = [
            'query' => [
                'id' => $id
            ]
        ];
        $engine = $client->get('/api/v1/services-logs/print-record' , $params);
        $serviceObj  = json_decode($engine->getBody()->getContents(), true)['data'];
        app()->setlocale($request->get('lang'));
        return view('print.pos-print', [
            'team' => Team::find($serviceObj['payable']['id']),
            'serviceObj' => $serviceObj,
            'locale' => $request->get('lang'),
            'title' => $serviceObj['type'] == 'withdraw' ? $title = __('Loaded On Reservation') :  $title = __('Loaded On Services Revenue')  ,
            'print' => $request->has('type') ? false :  true,
        ]);
    }

    function getSubInvoiceServicePrint(Request $request,$id)
    {

        $client = new Client(['base_uri' => config('app.fandaqah_api_url')]);


        $params = [
            'query' => [
                'id' => $id
            ]
        ];
        $engine = $client->get('/api/v1/services/print-record' , $params);
        $transaction  = json_decode($engine->getBody()->getContents(), true)['data'];
        $main_reservation_number = null;
        if($transaction['payable_type'] == 'App\\Reservation'){
            $reservation = Reservation::with('customer', 'unit', 'creator', 'comments')->find($transaction['payable']['id']);
            if($reservation->reservation_type == 'group'){
                if(is_null($reservation->attachable_id)){
                    $main_reservation = $reservation;
                }else{
                    $main_reservation = Reservation::find($reservation->attachable_id);
                }

                $main_reservation_number = $main_reservation->number;
            }else{
                $main_reservation_number = $reservation->number;
            }
        }



        return view('print.service-transaction', [
            'transaction' => $transaction,
            'contract_number' => $main_reservation_number,
            'locale' => app()->getLocale(),
            'title' => $transaction['type'] == 'withdraw' ? $title = __('Loaded On Reservation') :  $title = __('Loaded On Services Revenue')  ,
            'print' => $request->has('type') ? false :  true,
        ]);

    }

    function getTeamSubInvoicePrint($id)
    {
        $decode  = Hashids::decode("$id");
        $id      = reset($decode);
        $transaction = Transaction::findOrFail(['id' => $id])->first();
        // dd($transaction);
        $team = Team::find($transaction->payable_id) ;
        $transaction->type ;
        $locale = app()->getLocale();
        return view('print.team-sub-invoice', [

            't' => $transaction,
            'team' => $team ,
            'locale' => $locale ,
            'title' => $transaction->type == 'withdraw' ? __('Withdraw Transaction') : __('Deposit Transaction')  ,
            'print' => true
        ]);
    }

    /**
     * print Contract
     *
     * @param $id
     * @return mixed
     */
    function getContractPrint($id)
    {
        $shorten_url_code = request()->get('co') ?? null;

        // means he is the guest as no session found
        if(!auth()->check() && $shorten_url_code){
            // okay am trying to check if there is a reservation contract that has been already signed or not and i will fetch by the code 
            $checkReservationContract = ReservationContract::where('shorten_url_code',$shorten_url_code)->first();
            if($checkReservationContract){
                // i will redirect the guest to the saved copy 
                return redirect()->away('https://' . env('AWS_BUCKET').'.s3.'.env('AWS_DEFAULT_REGION').'.amazonaws.com/' . $checkReservationContract->html_path);

            }
            
        }
        
        $decode  = Hashids::decode("$id");
        $id      = reset($decode);
        $signature = null;
        $signature_signee = null;

        $reservation = Reservation::withOutGlobalScope('team_id')->with(['customer', 'reservation_guests', 'unit' , 'promissory' , 'wallet' , 'depositInsuranceTransactions'])->findOrFail($id);
        $lang = request()->get('lang');
        if(isset($lang) && $lang !== null && $lang !== "") {
            app()->setLocale($lang);
        }
        $locale = app()->getLocale();
        $team = null;
        if ($reservation instanceof Reservation and $reservation->team_id) {
            $team = Team::find($reservation->team_id);
        }

        $printStatus = true ;
        if(request()->get('type') && request()->get('type') == 'embed'){
            $printStatus = false;
        }
        
        $digital_signature = DigitalSignature::where('team_id',$reservation->team_id)->where('ref_id',$reservation->id)->where('type','reservation')->first();
        if($digital_signature){
            $signature =  gzuncompress(base64_decode($digital_signature->signature_base64));
            if($reservation->reservation_type == 'group'){
                $signature_signee = Company::find($reservation->company_id);
            } else if ($reservation->reservation_type == 'single') {
                $signature_signee = $reservation->customer;
            }
            if(isset($signature_signee)) {
                $signature_signee = $signature_signee->name;
            }
        }else{
            $signature = null;
        }

        $official_signature = null;
        $official_signature_signee = null;
        $official_signature_object = Reservation::getReservationOfficialSignature($reservation->team_id, $reservation->id);

        if(\Auth()->check() && !isset($official_signature_object)) {
            $official_signature_object = DigitalSignature::transactionUserSignReservation($reservation->id, $reservation->team_id);
        }
        if(isset($official_signature_object)) {
            $official_signature = $official_signature_object->signature;
            $official_signature_signee = User::find($official_signature_object->user_id);
            if(isset($official_signature_signee)) {
                $official_signature_signee = $official_signature_signee->name;
            }
        }

        // return view('print.contract', [
        return view(getSettingItem($reservation->team_id , 'print_contract_in_two_lang') ?  'print.new-contract' : 'print.contract', [
            'r' => $reservation,
            'team'  =>  $team,
            'locale' => $locale,
            'print' => $printStatus,
            'signature' => $signature,
            'signature_signee' => $signature_signee,
            'official_signature_signee' => $official_signature_signee,
            'official_signature' => $official_signature
        ]);
    }


    /**
     * Pdf Sub Invoice
     *
     * @param $id
     * @return mixed
     */
    function getSubInvoicePdf($id)
    {
        $decode  = Hashids::decode("$id");
        $id      = reset($decode);

        $transaction = Transaction::findOrFail(['id' => $id])->first();
        $reservation = Reservation::with('customer', 'unit', 'creator', 'comments')->find($transaction->payable_id);
        $locale = app()->getLocale();
        $type = $transaction->type ;
        $type == 'withdraw' ? $title = __('Withdraw Transaction') :  $title = __('Deposit Transaction') ;
        $config = ['instanceConfigurator' => function ($mpdf) {
            $stylesheet = file_get_contents(storage_path('pdf/pdf.css'));
            $mpdf->WriteHTML($stylesheet, 1);
            $mpdf->autoScriptToLang = true;
            $mpdf->autoLangToFont = true;
        }];
        $pdf = Pdf::loadView('pdf/sub-invoice', [ 'r' => $reservation,'t'=>$transaction , 'title' => $title , 'locale' => $locale], [], $config);

        return $pdf->stream('document.pdf');
    }

    function getServiceTransaction($id)
    {
        $decode  = Hashids::decode("$id");
        $id      = reset($decode);

        $transaction = Transaction::findOrFail(['id' => $id])->first();
        $services = $transaction->meta['services'];
        $reservation = Reservation::with('customer', 'unit', 'creator', 'comments')->find($transaction->payable_id);
        $locale = app()->getLocale();
        $type = $transaction->type ;
        $title = __('Service Transaction') ;
        $config = ['instanceConfigurator' => function ($mpdf) {
            $stylesheet = file_get_contents(storage_path('pdf/pdf.css'));
            $mpdf->WriteHTML($stylesheet, 1);
            $mpdf->autoScriptToLang = true;
            $mpdf->autoLangToFont = true;
        }];
        $pdf = Pdf::loadView('pdf/service-transaction', [ 'r' => $reservation,'t'=>$transaction , 'services' => $services , 'title' => $title , 'locale' => $locale], [], $config);

        return $pdf->stream('document.pdf');
    }

    /**
     * Pdf Contract
     *
     * @param $id
     * @return mixed
     */
    function getContractPdf($id)
    {
        $decode  = Hashids::decode("$id");
        $id      = reset($decode);
        $reservation = Reservation::withOutGlobalScope('team_id')->find($id);
        $locale = app()->getLocale();
        $title = __('Contract') ;
        $config = ['instanceConfigurator' => function ($mpdf) {
            $stylesheet = file_get_contents(storage_path('pdf/pdf.css'));
            $mpdf->WriteHTML($stylesheet, 1);
            $mpdf->autoScriptToLang = true;
            $mpdf->baseScript = 1;
            $mpdf->autoVietnamese = true;
            $mpdf->autoArabic = true;
            $mpdf->useKerning=true;
            $mpdf->autoLangToFont = true;

        }];
//        $pdf = Pdf::loadView('pdf/contract', ['r' => $reservation, 'locale' => $locale , 'title' => $title], [], $config);
        $pdf = Pdf::loadView('pdf/contract', ['r' => $reservation, 'locale' => $locale , 'title' => $title], [], $config);

        return $pdf->stream("{$id}-contract.pdf");
    }

    function getInvoicePdf($id){

        $decode  = Hashids::decode("$id");
        $id      = reset($decode);

        $invoice = ReservationInvoice::with('reservation' , 'reservation.unit' , 'reservation.customer' , 'reservation.creator' , 'reservation.comments')->findOrFail($id);

        $locale = app()->getLocale();
        $title = __('Invoice') ;
        $config = ['instanceConfigurator' => function ($mpdf) {
            $stylesheet = file_get_contents(storage_path('pdf/pdf.css'));
            $mpdf->WriteHTML($stylesheet, 1);
            $mpdf->autoScriptToLang = true;
            $mpdf->baseScript = 1;
            $mpdf->autoVietnamese = true;
            $mpdf->autoArabic = true;
            $mpdf->useKerning=true;
            $mpdf->autoLangToFont = true;

        }];

        $pdf = Pdf::loadView('pdf/invoice', ['invoice' => $invoice, 'locale' => $locale , 'title' => $title], [], $config);

        return $pdf->stream("{$id}-invoice.pdf");
    }

    function getReservationSummary($id){
        $decode  = Hashids::decode("$id");
        $id      = reset($decode);
        $reservation = Reservation::withOutGlobalScope('team_id')->find($id);
        $reservationServicesTransactions = $reservation->services ;
        $reservationDepositWithdrawTransactions = $reservation->transactions ;
        $locale = app()->getLocale();
        $title = __('Reservation Summary') ;
        $config = ['instanceConfigurator' => function ($mpdf) {
            $stylesheet = file_get_contents(storage_path('pdf/pdf.css'));
            $mpdf->WriteHTML($stylesheet, 1);
            $mpdf->autoScriptToLang = true;
            $mpdf->baseScript = 1;
            $mpdf->autoVietnamese = true;
            $mpdf->autoArabic = true;
            $mpdf->useKerning=true;
            $mpdf->autoLangToFont = true;

        }];
//        $pdf = Pdf::loadView('pdf/contract', ['r' => $reservation, 'locale' => $locale , 'title' => $title], [], $config);
        $pdf = Pdf::loadView('pdf/reservation-summary', ['reservation' => $reservation, 'reservationServicesTransactions' => $reservationServicesTransactions , 'reservationDepositWithdrawTransactions' => $reservationDepositWithdrawTransactions ,  'locale' => $locale , 'title' => $title], [], $config);

        return $pdf->stream("{$id}-reservation-summary.pdf");
    }


    /**
     * Public get Invoice
     *
     * @param $id
     * @return mixed
     */
    function getInvoicePublic(Request $request , $id)
    {
        $decode  = Hashids::decode("$id");
        $id      = reset($decode);
        $invoice = ReservationInvoice::withoutGlobalScopes()->findOrFail($id);
        $reservation = Reservation::withoutGlobalScopes()->with('team' , 'customer' , 'creator' )->find($invoice->reservation_id);
        $unit = Unit::withoutGlobalScopes()->find($reservation->unit_id);
        app()->setlocale($request->get('lang'));
        $vat_for_services = 0;
        if(isset($invoice->data['services'])){
            foreach ($invoice->data['services'] as $service) {
                if(isset($service['vat'])){
                    $vat_for_services += $service['vat'];
                }
            }
        }

        $total_vat = $invoice->data['vat'] + $vat_for_services;
         // E-Invoice Compatiable with ZACAH phase 1
         $displayQRCodeAsBase64 = GenerateQrCode::fromArray([
            new Seller($invoice->reservation->team->name), // seller name
            new TaxNumber(getSettingItem($invoice->reservation->team_id , 'tax_number')), // seller tax number
            new InvoiceDate($invoice->created_at->toIso8601ZuluString()), // invoice date as Zulu ISO8601 @see https://en.wikipedia.org/wiki/ISO_8601
            new InvoiceTotalAmount($invoice->data['amount']), // invoice total amount
            new InvoiceTaxAmount(number_format($total_vat,2)) // invoice tax amount
        ])->render();
        // return view('print.invoice', [
        return view(getSettingItem($reservation->team_id , 'print_invoice_in_two_lang') ? 'print.new-template-invoice' : 'print.invoice', [
            'invoice' => $invoice,
            'reservation' => $reservation,
            'unit' => $unit,
            'locale' => $request->get('lang') ,
            'print' => false,
            'qrCode' => $displayQRCodeAsBase64,
            'showFandaqahQr' => !$invoice->data['vat'] ? true : false
        ]);
    }


    /**
     * Public get Sub Invoice
     *
     * @param $id
     * @return mixed
     */
    function getSubInvoicePublic($id)
    {
        $decode  = Hashids::decode("$id");
        $id      = reset($decode);
        $transaction = Transaction::withOutGlobalScope('team_id')->findOrFail(['id' => $id])->first();
        $reservation = Reservation::withOutGlobalScope('team_id')->with('customer', 'unit', 'creator', 'comments')->find($transaction->payable_id);
        $type = $transaction->type ;
        $type == 'withdraw' ? $title = __('Withdraw Transaction') :  $title = __('Deposit Transaction') ;
        $config = ['instanceConfigurator' => function ($mpdf) {
            $stylesheet = file_get_contents(storage_path('pdf/pdf.css'));
            $mpdf->WriteHTML($stylesheet, 1);
            $mpdf->autoScriptToLang = true;
            $mpdf->autoLangToFont = true;
        }];
        $pdf = Pdf::loadView('pdf/sub-invoice', [ 'r' => $reservation,'t'=>$transaction , 'title' => $title], [], $config);

        return $pdf->stream('document.pdf');
    }

    /**
     * get Contract
     *
     * @param $id
     * @return mixed
     */
    function getContractPublic($id)
    {
        $decode  = Hashids::decode("$id");
        $id      = reset($decode);
        $reservation = Reservation::withOutGlobalScope('team_id')->find($id);
        $locale = app()->getLocale();

        $config = ['instanceConfigurator' => function ($mpdf) {
            $stylesheet = file_get_contents(storage_path('pdf/pdf.css'));
            $mpdf->WriteHTML($stylesheet, 1);
            $mpdf->autoScriptToLang = true;
            $mpdf->baseScript = 1;
            $mpdf->autoVietnamese = true;
            $mpdf->autoArabic = true;
            $mpdf->useKerning=true;
            $mpdf->autoLangToFont = true;

        }];
        $pdf = Pdf::loadView('pdf/contract', ['r' => $reservation, 'locale' => $locale], [], $config);

        return $pdf->stream("{$id}-contract.pdf");
    }

    /**
     * @author Emad Rashad
     * @description as per request of mr faisel , contract public will open as web page
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    function getContractPrintPublic($id){
        $decode  = Hashids::decode("$id");
        $id      = reset($decode);
        $reservation = Reservation::withOutGlobalScope('team_id')->with('unit' , 'customer')->find($id);
        $locale = app()->getLocale();
        return view('print.contract-public', [
            'r' => $reservation,
            'unit' => $reservation->unit()->withTrashed()->withOutGlobalScope('team_id')->first(),
            'customer' => $reservation->customer()->withTrashed()->withOutGlobalScope('team_id')->first(),
            'locale' => $locale ,
            'print' => false
        ]);
    }


    function getSubInvoicePrintPublic($id){

        $decode  = Hashids::decode("$id");
        $id      = reset($decode);
        $transaction = Transaction::findOrFail(['id' => $id])->first();
        $type = $transaction->type ;
        $type == 'withdraw' ? $title = __('Withdraw Transaction') :  $title = __('Deposit Transaction') ;
        $reservation = Reservation::with('customer', 'unit', 'creator', 'comments')->find($transaction->payable_id);

        $main_reservation_number = null;
        if($reservation->reservation_type == 'group'){
            if(is_null($reservation->attachable_id)){
                $main_reservation = $reservation;
            }else{
                $main_reservation = Reservation::find($reservation->attachable_id);
            }

            $main_reservation_number = $main_reservation->number;
        }

        $locale = app()->getLocale();

        return view('print.sub-invoice', [
            'r' => $reservation,
            't' => $transaction,
            'locale' => $locale ,
            'contract_number' => $reservation->reservation_type == 'group' ?  $main_reservation_number : $reservation->number,
            'title' => $title ,
            'print' => false
        ]);
    }



    public function getPosPrint(Request $request){
        $data = json_decode($request->get('data'));
        return view('print.customer-pos-invoice')->with(['data' => $data]);
    }

    public function getPromissoryPrint(Request $request, $id)
    {
        $locale = $request->get('locale');
        if(isset($locale)) {
            app()->setLocale($locale);
        }
        $client = new Client(['base_uri' => config('app.fandaqah_api_url')]);
        $params = [
            'query' => [
                'id' => Hashids::decode($id)[0]
            ]
        ];
        $engine = $client->get('/api/v1/promissories/show' , $params);
        $promissory =   json_decode($engine->getBody()->getContents(), true)['data'];
        $reservation = Reservation::withOutGlobalScope('team_id')->find($promissory['reservation']['id']);
        $current_team = Team::find($reservation->team_id);
        $main_reservation = null ;
        $signature = null;
        $signature_signee = null;

        if($reservation->reservation_type == 'group'){
            if(is_null($reservation->attachable_id)){
                $main_reservation = $reservation;
            }else{
                $main_reservation = Reservation::find($reservation->attachable_id);
            }
        }
        
        $signature = Promissory::getPromissoryCustomerSignature($reservation->team_id, $reservation->id);
        
        if(isset($signature)){
            if($reservation->reservation_type == 'group'){
                $signature_signee = Company::find($reservation->company_id);
            } else if ($reservation->reservation_type == 'single') {
                $signature_signee = Customer::find($reservation->customer_id);
            }
            if(isset($signature_signee)) {
                $signature_signee = $signature_signee->name;
            }
            $signature = $signature->signature;
        }

        $official_signature = null;
        $official_signature_signee = null;
        $official_signature_object = Promissory::getPromissoryOfficialSignature($reservation->team_id, $reservation->id);
        //print digital signature respective to current user if (authenticated)
        if(\Auth()->check() && !isset($official_signature_object)) {
            $official_signature_object = DigitalSignature::transactionUserSignPromissory($reservation->id, $reservation->team_id);
        }

        if(isset($official_signature_object)) {
            $official_signature = $official_signature_object->signature;
            $official_signature_signee = User::find($official_signature_object->user_id);
            if(isset($official_signature_signee)) {
                $official_signature_signee = $official_signature_signee->name;
            }
        }

        return view('print.promissory' , [
            'r' => $reservation,
            'promissory' => $promissory ,
            'current_team' => $current_team, 
            'main_reservation' => $main_reservation , 
            'locale' => app()->getLocale(), 
            'official_signature' => $official_signature, 
            'official_signature_signee' => $official_signature_signee, 
            'signature' => $signature,
            'signature_signee' => $signature_signee,
            'reservation_id' => $reservation->id
        ]);

    }

    public function getGroupReservationInvoicePrototype(Request $request)
    {
        $locale = $request->get('locale');
        app()->setLocale($locale);
        $decode = Hashids::decode($request->get('hs'));
        $reservation_id = reset($decode);
        $balances  = [];
        $reservation = Reservation::find($reservation_id);
        $push_main_reservation_to_collection = false;
        $has_at_least_one_vat = false;
        if($reservation->reservation_type == 'group'){
            $main_reservation = null ;
            if(is_null($reservation->attachable_id)){
                $main_reservation = $reservation;
                $push_main_reservation_to_collection = false;
            }else{
                $main_reservation = Reservation::find($reservation->attachable_id);
                $push_main_reservation_to_collection = true;
            }
            if($main_reservation->status == 'canceled'){
                $reservations = Reservation::with('wallet','unit','customer')
                ->where('reservation_type' , 'group')
                ->where('company_id' , $reservation->company_id)
                ->where(function ($query) use($reservation,$main_reservation) {
                    return $query->with('unit')->where('id',$reservation->id)->orWhere('attachable_id',$main_reservation->id);
                })
                ->where('status' , 'canceled')
                // ->whereNull('checked_out')
                ->whereNull('deleted_at')
                ->orderBy('created_at')
                ->get();
            }else{
                $reservations = Reservation::with('wallet','unit','customer')
                ->where('reservation_type' , 'group')
                ->where('company_id' , $reservation->company_id)
                ->where(function ($query) use($reservation,$main_reservation) {
                    return $query->with('unit')->where('id',$reservation->id)->orWhere('attachable_id',$main_reservation->id);
                })
                ->where('status' , 'confirmed')
                // ->whereNull('checked_out')
                ->whereNull('deleted_at')
                ->orderBy('created_at')
                ->get();
            }


            if($push_main_reservation_to_collection){
                $reservations->push($main_reservation);
            }

            foreach($reservations as $reservationObject){
                $balances [] = $reservationObject->wallet->decimal_places == 3 ? $reservationObject->balance / 1000 : $reservationObject->balance / 100;
                if($reservationObject->vat_total){
                    $has_at_least_one_vat = true;
                }
            }

            $services = Transaction::whereIn('payable_id' , collect($reservations)->pluck('id'))
                            ->where('payable_type' , 'App\\Reservation')
                            ->where('meta->category', 'service')
                            ->whereNull('deleted_at')
                            ->orderBy('created_at' , 'asc')
                            ->get();

                return view('print.company_live_invoice_prototype', [
                    'count_reservations' => $reservations->count(),
                    'reservations' => $reservations->sortBy('created_at'),
                    'has_at_least_one_vat' => $has_at_least_one_vat,
                    'main_reservation' => $main_reservation,
                    'services' => $services,
                    'inv_num' => $request->get('inv_num') ? $request->get('inv_num') : null,
                    'credit_note_num' => $request->get('credit_note_num') && $request->get('credit_note_num') != 'no-credit-note' ? $request->get('credit_note_num') : null,
                    'inv_dt' => $request->get('inv_dt') ? $request->get('inv_dt') : null,
                    'company' => $main_reservation->company,
                    'team' => $main_reservation->company->team,
                    'dates_calculations' => startAndEndDateCalculatorWithNights($reservations),
                    'group_balance' => array_sum($balances),
                    'locale' => $locale ,
                    'hs' => $request->get('hs'),
                    'print' => $request->has('type') && $request->get('type') == 'embed' ? false :  true,
                    'extra_addon' => count($reservations) > 1 ? null : [
                        'customer_name' => $main_reservation->customer ? $main_reservation->customer->name : '-',
                        'customer_id_number' => $main_reservation->customer ? $main_reservation->customer->id_number : null,
                        'unit_name' => $main_reservation->unit ? $main_reservation->unit->name : '-',
                        'unit_number' => $main_reservation->unit ? $main_reservation->unit->unit_number : '-',
                    ]
                ]);
        }

    }

    public function getGroupReservationInvoice(Request $request)
    {
        $locale = $request->get('locale');
        app()->setLocale($locale);
        $decode = Hashids::decode($request->get('inv'));
        $invoice_id = reset($decode);
        $res_ids = [];
        $invoice = ReservationInvoice::withoutGlobalScopes()->with('team')->find($invoice_id);
        $main_reservation = null ;
        foreach ($invoice->data['reservations'] as $reservation) {
            if(is_null($reservation['attachable_id'])){
                $main_reservation = $reservation;
            }
            $res_ids []  = $reservation['id'];
        }

        $res_collection = collect(Reservation::whereIn('id',$res_ids)->get());
        return view('print.company_live_invoice', [
            'invoice' => $invoice,
            'main_reservation' => $main_reservation,
            'team' => $invoice->team,
            'dates_calculations' => startAndEndDateCalculatorWithNights($res_collection),
            'locale' => $locale ,
            'inv' => $request->get('inv'),
            'print' => $request->has('type') && $request->get('type') == 'embed' ? false :  true
        ]);

    }

    public function getGroupReservationInvoicePublic(Request $request)
    {
        $locale = $request->get('locale');
        app()->setlocale($locale);
        $decode = Hashids::decode($request->get('hs'));
        $reservation_id = reset($decode);
        $balances  = [];
        $reservation = Reservation::find($reservation_id);
        $push_main_reservation_to_collection = false;
        $has_at_least_one_vat = false;
        if($reservation->reservation_type == 'group'){
            $main_reservation = null ;
            if(is_null($reservation->attachable_id)){
                $main_reservation = $reservation;
                $push_main_reservation_to_collection = false;
            }else{
                $main_reservation = Reservation::find($reservation->attachable_id);
                $push_main_reservation_to_collection = true;
            }

            $reservations = Reservation::with('wallet')
                    ->where('reservation_type' , 'group')
                    ->where('company_id' , $reservation->company_id)
                    ->where(function ($query) use($reservation,$main_reservation) {
                        return $query->where('id',$reservation->id)->orWhere('attachable_id',$main_reservation->id);
                    })
                    ->where('status' , 'confirmed')
                    // ->whereNull('checked_out')
                    ->whereNull('deleted_at')
                    ->orderBy('created_at')
                    ->get();

            if($push_main_reservation_to_collection){
                $reservations->push($main_reservation);
            }

            foreach($reservations as $reservationObject){
                $balances [] = $reservationObject->wallet->decimal_places == 3 ? $reservationObject->balance / 1000 : $reservationObject->balance / 100;
                $has_at_least_one_vat = true;
            }

            $services = Transaction::whereIn('payable_id' , collect($reservations)->pluck('id'))
                            ->where('payable_type' , 'App\\Reservation')
                            ->where('meta->category', 'service')
                            ->whereNull('deleted_at')
                            ->orderBy('created_at' , 'asc')
                            ->get();

                return view('print.company_live_invoice', [
                    'count_reservations' => $reservations->count(),
                    'reservations' => $reservations->sortBy('created_at'),
                    'has_at_least_one_vat' => $has_at_least_one_vat,
                    'main_reservation' => $main_reservation,
                    'services' => $services,
                    'inv_num' => $request->get('inv_num') ? $request->get('inv_num') : null,
                    'credit_note_num' => $request->get('credit_note_num') && $request->get('credit_note_num') != 'no-credit-note' ? $request->get('credit_note_num') : null,
                    'inv_dt' => $request->get('inv_dt') ? $request->get('inv_dt') : null,
                    'company' => $main_reservation->company,
                    'team' => $main_reservation->company->team,
                    'dates_calculations' => startAndEndDateCalculatorWithNights($reservations),
                    'group_balance' => array_sum($balances),
                    'locale' => $locale ,
                    'hs' => $request->get('hs'),
                    'print' => $request->has('type') && $request->get('type') == 'embed' ? false :  true
                ]);
        }
    }

    public function getGroupReservationContract(Request $request)
    {

        $shorten_url_code = request()->get('co') ?? null;

        // means he is the guest as no session found
        if(!auth()->check() && $shorten_url_code){
            // okay am trying to check if there is a reservation contract that has been already signed or not and i will fetch by the code 
            $checkReservationContract = ReservationContract::where('shorten_url_code',$shorten_url_code)->first();
            if($checkReservationContract){
                // i will redirect the guest to the saved copy 
                return redirect()->away('https://' . env('AWS_BUCKET').'.s3.'.env('AWS_DEFAULT_REGION').'.amazonaws.com/' . $checkReservationContract->html_path);

            }
            
        }

        $signature = null;
        $signature_signee = null;
        $locale = $request->get('locale');
        $decode = Hashids::decode($request->get('hs'));
        $reservation_id = reset($decode);
        $balances  = [];
        $reservations_total_prices = [];
        $reservations_total_services = [];
        $reservations_subtotals = [];
        $reservations_taxes = [];
        $vat_taxes = [];
        $ewa_taxes = [];
        $ttx_taxes = [];
        $reservations_services_without_taxes = [];
        $reservations_services_taxes = [];
        $reservations_deposit_insurance_transactions = [];
        $reservation = Reservation::find($reservation_id);
        $push_main_reservation_to_collection = false;
        $reservations_paid = [];
        if($reservation->reservation_type == 'group'){
            $main_reservation = null ;
            if(is_null($reservation->attachable_id)){
                $main_reservation = $reservation;
                $push_main_reservation_to_collection = false;
            }else{
                $main_reservation = Reservation::find($reservation->attachable_id);
                $push_main_reservation_to_collection = true;
            }

            if($main_reservation->status == 'canceled'){
                $reservations = Reservation::with('wallet','unit','customer')
                ->where('reservation_type' , 'group')
                ->where('company_id' , $reservation->company_id)
                ->where(function ($query) use($reservation,$main_reservation) {
                    return $query->with('unit')->where('id',$reservation->id)->orWhere('attachable_id',$main_reservation->id);
                })
                ->where('status' , 'canceled')
                // ->whereNull('checked_out')
                ->whereNull('deleted_at')
                ->orderBy('created_at')
                ->get();
            }else{
                $reservations = Reservation::with('wallet','unit','customer')
                ->where('reservation_type' , 'group')
                ->where('company_id' , $reservation->company_id)
                ->where(function ($query) use($reservation,$main_reservation) {
                    return $query->with('unit')->where('id',$reservation->id)->orWhere('attachable_id',$main_reservation->id);
                })
                ->where('status' , 'confirmed')
                // ->whereNull('checked_out')
                ->whereNull('deleted_at')
                ->orderBy('created_at')
                ->get();
            }

            if($push_main_reservation_to_collection){
                $reservations->push($main_reservation);
            }


            foreach($reservations as $reservationObject){
                $balances [] = $reservationObject->wallet->decimal_places == 3 ? $reservationObject->balance / 1000 : $reservationObject->balance / 100;
                $reservations_total_prices [] = $reservationObject->total_price;
                $reservations_total_services [] = $reservationObject->getServicesSum();
                $reservations_subtotals [] = $reservationObject->sub_total;
                $reservations_taxes [] = $reservationObject->ewa_total + $reservationObject->vat_total + $reservationObject->ttx_total;
                $reservations_services_without_taxes [] = $reservationObject->getServicesWithoutTaxesSum();
                $reservations_services_taxes [] = $reservationObject->getServicesTaxesSum();
                $reservations_deposit_insurance_transactions [] = $reservationObject->depositInsuranceTransactions()->sum('amount');
                $reservations_paid [] = $reservationObject->getDepositSum() - $reservationObject->getWithdrawSum();
                $vat_taxes [] = $reservationObject->vat_total;
                $ewa_taxes [] = $reservationObject->ewa_total;
                $ttx_taxes [] = $reservationObject->ttx_total;
            }

            $digital_signature = DigitalSignature::where('team_id',$main_reservation->team_id)->where('ref_id',$main_reservation->id)->where('type','reservation')->first();
            if($digital_signature){
                $signature =  gzuncompress(base64_decode($digital_signature->signature_base64));
                if($main_reservation->reservation_type == 'group'){
                    $signature_signee = Company::find($main_reservation->company_id);
                } else if ($reservation->reservation_type == 'single') {
                    $signature_signee = $main_reservation->customer;
                }
                if(isset($signature_signee)) {
                    $signature_signee = $signature_signee->name;
                }
            }else{
                $signature = null;
            }
    
            $official_signature = null;
            $official_signature_signee = null;
            $official_signature_object = Reservation::getReservationOfficialSignature($main_reservation->team_id, $main_reservation->id);
    
            if(\Auth()->check() && !isset($official_signature_object)) {
                $official_signature_object = DigitalSignature::transactionUserSignReservation($main_reservation->id, $main_reservation->team_id);
            }
            if(isset($official_signature_object)) {
                $official_signature = $official_signature_object->signature;
                $official_signature_signee = User::find($official_signature_object->user_id);
                if(isset($official_signature_signee)) {
                    $official_signature_signee = $official_signature_signee->name;
                }
            }



            // return view('print.company_live_contract', [
            return view(getSettingItem($main_reservation->team_id , 'print_contract_in_two_lang') ? 'print.new-group-contract' : 'print.company_live_contract', [
                    'units_count' => $reservations->count(),
                    'main_reservation' => $main_reservation,
                    'company' => $main_reservation->company,
                    'team' => $main_reservation->company->team,
                    'calculations' => [
                        'reservations_total_prices_with_services' => number_format(array_sum($reservations_subtotals) + array_sum($reservations_total_services) + array_sum($reservations_taxes),2),
                        'reservations_subtotals' => number_format(array_sum($reservations_subtotals),2),
                        'reservations_services_without_taxes' => number_format(array_sum($reservations_services_without_taxes),2),
                        'leasing_with_services' => number_format(array_sum($reservations_subtotals) + array_sum($reservations_services_without_taxes) , 2),
                        'reservations_taxes' => number_format(array_sum($reservations_taxes),2),
                        'reservations_services_taxes' => number_format(array_sum($reservations_services_taxes),2),
                        'reservations_deposit_insurance_transactions' => number_format(array_sum($reservations_deposit_insurance_transactions) / ($main_reservation->wallet->decimal_places == 3 ? 1000 : 100 ),2),
                        'reservations_paid' => number_format(array_sum($reservations_paid),2),
                        'vat_taxes' => number_format(array_sum($vat_taxes),2),
                        'ewa_taxes' => number_format(array_sum($ewa_taxes),2),
                        'ttx_taxes' => number_format(array_sum($ttx_taxes),2)
                    ],
                    'dates_calculations' => startAndEndDateCalculatorWithNightsForGroupContract($reservations),
                    'group_balance' => array_sum($balances),
                    'locale' => $locale ,
                    'print' => $request->has('type') && $request->get('type') == 'embed' ? false :  true,
                    'signature' => $signature,
                    'signature_signee' => $signature_signee,
                    'official_signature_signee' => $official_signature_signee,
                    'official_signature' => $official_signature
                ]);
        }
    }

    public function getGroupReservationContractPublic(Request $request)
    {


        $shorten_url_code = request()->get('co') ?? null;

        // means he is the guest as no session found
        if(!auth()->check() && $shorten_url_code){
            // okay am trying to check if there is a reservation contract that has been already signed or not and i will fetch by the code 
            $checkReservationContract = ReservationContract::where('shorten_url_code',$shorten_url_code)->first();
            if($checkReservationContract){
                // i will redirect the guest to the saved copy 
                return redirect()->away('https://' . env('AWS_BUCKET').'.s3.'.env('AWS_DEFAULT_REGION').'.amazonaws.com/' . $checkReservationContract->html_path);

            }
            
        }
        
        $signature = null;
        $signature_signee = null;
        $locale = $request->get('locale');
        app()->setlocale($locale);
        $decode = Hashids::decode($request->get('hs'));
        $reservation_id = reset($decode);
        $balances  = [];
        $reservations_total_prices = [];
        $reservations_total_services = [];
        $reservations_subtotals = [];
        $reservations_taxes = [];
        $vat_taxes = [];
        $ewa_taxes = [];
        $ttx_taxes = [];
        $reservations_services_without_taxes = [];
        $reservations_services_taxes = [];
        $reservations_deposit_insurance_transactions = [];
        $reservation = Reservation::find($reservation_id);
        $push_main_reservation_to_collection = false;
        $reservations_paid = [];
        if($reservation->reservation_type == 'group'){
            
            $main_reservation = null ;
            if(is_null($reservation->attachable_id)){
                $main_reservation = $reservation;
                $push_main_reservation_to_collection = false;
            }else{
                $main_reservation = Reservation::find($reservation->attachable_id);
                $push_main_reservation_to_collection = true;
            }

            if($main_reservation->status == 'canceled'){
                $reservations = Reservation::with('wallet','unit','customer')
                ->where('reservation_type' , 'group')
                ->where('company_id' , $reservation->company_id)
                ->where(function ($query) use($reservation,$main_reservation) {
                    return $query->with('unit')->where('id',$reservation->id)->orWhere('attachable_id',$main_reservation->id);
                })
                ->where('status' , 'canceled')
                // ->whereNull('checked_out')
                ->whereNull('deleted_at')
                ->orderBy('created_at')
                ->get();
            }else{
                $reservations = Reservation::with('wallet','unit','customer')
                ->where('reservation_type' , 'group')
                ->where('company_id' , $reservation->company_id)
                ->where(function ($query) use($reservation,$main_reservation) {
                    return $query->with('unit')->where('id',$reservation->id)->orWhere('attachable_id',$main_reservation->id);
                })
                ->where('status' , 'confirmed')
                // ->whereNull('checked_out')
                ->whereNull('deleted_at')
                ->orderBy('created_at')
                ->get();
            }

            if($push_main_reservation_to_collection){
                $reservations->push($main_reservation);
            }

            foreach($reservations as $reservationObject){
                $balances [] = $reservationObject->wallet->decimal_places == 3 ? $reservationObject->balance / 1000 : $reservationObject->balance / 100;
                $reservations_total_prices [] = $reservationObject->total_price;
                $reservations_total_services [] = $reservationObject->getServicesSum();
                $reservations_subtotals [] = $reservationObject->sub_total;
                $reservations_taxes [] = $reservationObject->ewa_total + $reservationObject->vat_total + $reservationObject->ttx_total;
                $reservations_services_without_taxes [] = $reservationObject->getServicesWithoutTaxesSum();
                $reservations_services_taxes [] = $reservationObject->getServicesTaxesSum();
                $reservations_deposit_insurance_transactions [] = $reservationObject->depositInsuranceTransactions()->sum('amount');
                $reservations_paid [] = $reservationObject->getDepositSum() - $reservationObject->getWithdrawSum();
                $vat_taxes [] = $reservationObject->vat_total;
                $ewa_taxes [] = $reservationObject->ewa_total;
                $ttx_taxes [] = $reservationObject->ttx_total;
            }

            $digital_signature = DigitalSignature::where('team_id',$main_reservation->team_id)->where('ref_id',$main_reservation->id)->where('type','reservation')->first();
            if($digital_signature){
                $signature =  gzuncompress(base64_decode($digital_signature->signature_base64));
                if($main_reservation->reservation_type == 'group'){
                    $signature_signee = Company::find($main_reservation->company_id);
                } else if ($reservation->reservation_type == 'single') {
                    $signature_signee = $main_reservation->customer;
                }
                if(isset($signature_signee)) {
                    $signature_signee = $signature_signee->name;
                }
            }else{
                $signature = null;
            }
    
            $official_signature = null;
            $official_signature_signee = null;
            $official_signature_object = Reservation::getReservationOfficialSignature($main_reservation->team_id, $main_reservation->id);
    
            if(\Auth()->check() && !isset($official_signature_object)) {
                $official_signature_object = DigitalSignature::transactionUserSignReservation($main_reservation->id, $main_reservation->team_id);
            }
            if(isset($official_signature_object)) {
                $official_signature = $official_signature_object->signature;
                $official_signature_signee = User::find($official_signature_object->user_id);
                if(isset($official_signature_signee)) {
                    $official_signature_signee = $official_signature_signee->name;
                }
            }

            return view(getSettingItem($main_reservation->team_id , 'print_contract_in_two_lang') ? 'print.new-group-contract' : 'print.company_live_contract', [
                    'units_count' => $reservations->count(),
                    'main_reservation' => $main_reservation,
                    'company' => $main_reservation->company,
                    'team' => $main_reservation->company->team,
                    'calculations' => [
                        'reservations_total_prices_with_services' => number_format(array_sum($reservations_subtotals) + array_sum($reservations_total_services) + array_sum($reservations_taxes),2),
                        'reservations_subtotals' => number_format(array_sum($reservations_subtotals),2),
                        'reservations_services_without_taxes' => number_format(array_sum($reservations_services_without_taxes),2),
                        'leasing_with_services' => number_format(array_sum($reservations_subtotals) + array_sum($reservations_services_without_taxes) , 2),
                        'reservations_taxes' => number_format(array_sum($reservations_taxes),2),
                        'reservations_services_taxes' => number_format(array_sum($reservations_services_taxes),2),
                        'reservations_deposit_insurance_transactions' => number_format(array_sum($reservations_deposit_insurance_transactions) / ($main_reservation->wallet->decimal_places == 3 ? 1000 : 100 ),2),
                        'reservations_paid' => number_format(array_sum($reservations_paid),2),
                        'vat_taxes' => number_format(array_sum($vat_taxes),2),
                        'ewa_taxes' => number_format(array_sum($ewa_taxes),2),
                        'ttx_taxes' => number_format(array_sum($ttx_taxes),2)
                    ],
                    'dates_calculations' => startAndEndDateCalculatorWithNightsForGroupContract($reservations),
                    'group_balance' => array_sum($balances),
                    'locale' => $locale ,
                    'print' => $request->has('type') && $request->get('type') == 'embed' ? false :  true,
                    'signature' => $signature,
                    'signature_signee' => $signature_signee,
                    'official_signature_signee' => $official_signature_signee,
                    'official_signature' => $official_signature
                ]);
        }
    }


    public function getGroupReservationSummary(Request $request)
    {
        $locale = $request->get('locale');
        $decode = Hashids::decode($request->get('hs'));
        $reservation_id = reset($decode);
        $balances  = [];
        $reservations_total_prices = [];
        $reservations_total_services = [];
        $reservations_subtotals = [];
        $reservations_taxes = [];
        $vat_taxes = [];
        $ewa_taxes = [];
        $ttx_taxes = [];
        $reservations_services_without_taxes = [];
        $reservations_services_taxes = [];
        $reservations_deposit_insurance_transactions = [];
        $reservations_transactions = [];
        $reservations_services = [];
        $creditor = [];
        $debitor = [];
        $reservation = Reservation::find($reservation_id);
        $push_main_reservation_to_collection = false;
        if($reservation->reservation_type == 'group'){
            $main_reservation = null ;
            if(is_null($reservation->attachable_id)){
                $main_reservation = $reservation;
                $push_main_reservation_to_collection = false;
            }else{
                $main_reservation = Reservation::find($reservation->attachable_id);
                $push_main_reservation_to_collection = true;
            }
            if($main_reservation->status == 'canceled'){
                $reservations = Reservation::with('wallet')
                    ->where('reservation_type' , 'group')
                    ->where('company_id' , $reservation->company_id)
                    ->where(function ($query) use($reservation,$main_reservation) {
                        return $query->where('id',$reservation->id)->orWhere('attachable_id',$main_reservation->id);
                    })
                    ->where('status' , 'canceled')
                    // ->whereNull('checked_out')
                    ->whereNull('deleted_at')
                    ->orderBy('created_at')
                    ->get();
            }else{
                $reservations = Reservation::with('wallet')
                    ->where('reservation_type' , 'group')
                    ->where('company_id' , $reservation->company_id)
                    ->where(function ($query) use($reservation,$main_reservation) {
                        return $query->where('id',$reservation->id)->orWhere('attachable_id',$main_reservation->id);
                    })
                    ->where('status' , 'confirmed')
                    // ->whereNull('checked_out')
                    ->whereNull('deleted_at')
                    ->orderBy('created_at')
                    ->get();
            }


            if($push_main_reservation_to_collection){
                $reservations->push($main_reservation);
            }


            foreach($reservations as $reservationObject){
                $balances [] = $reservationObject->wallet->decimal_places == 3 ? $reservationObject->balance / 1000 : $reservationObject->balance / 100;
                $reservations_total_prices [] = $reservationObject->total_price;
                $reservations_total_services [] = $reservationObject->getServicesSum();
                $reservations_subtotals [] = $reservationObject->sub_total;
                $reservations_taxes [] = $reservationObject->ewa_total + $reservationObject->vat_total + $reservationObject->ttx_total;
                $reservations_services_without_taxes [] = $reservationObject->getServicesWithoutTaxesSum();
                $reservations_services_taxes [] = $reservationObject->getServicesTaxesSum();
                $reservations_deposit_insurance_transactions [] = $reservationObject->depositInsuranceTransactions()->sum('amount');
                $reservations_paid [] = $reservationObject->getDepositSum() - $reservationObject->getWithdrawSum();
                $vat_taxes [] = $reservationObject->vat_total;
                $ewa_taxes [] = $reservationObject->ewa_total;
                $ttx_taxes [] = $reservationObject->ttx_total;
                if($reservationObject->services()->count()){
                    $reservations_services [] =   collect($reservationObject->services)->toArray();
                }
                if($reservationObject->transactions()->count()){
                    $reservations_transactions [] = collect($reservationObject->transactions)->toArray();
                }
                $creditor [] = $reservationObject->getDepositSum();
                $debitor [] = $reservationObject->getWithdrawSum() + $reservationObject->getServicesSum() + $reservationObject->total_price;
            }

            return view('print.company_liveـsummary', [
                    'units_count' => $reservations->count(),
                    'main_reservation' => $main_reservation,
                    'company' => $main_reservation->company,
                    'team' => $main_reservation->company->team,
                    'reservations_services' => $reservations_services ?  array_merge(...$reservations_services) : [],
                    'reservations_transactions' => $reservations_transactions ? array_merge(...$reservations_transactions) : [],
                    'calculations' => [
                        'reservations_total_prices_with_services' => number_format(array_sum($reservations_subtotals) + array_sum($reservations_total_services) + array_sum($reservations_taxes),2),
                        'reservations_subtotals' => number_format(array_sum($reservations_subtotals),2),
                        'reservations_services_without_taxes' => number_format(array_sum($reservations_services_without_taxes),2),
                        'leasing_with_services' => number_format(array_sum($reservations_subtotals) + array_sum($reservations_services_without_taxes) , 2),
                        'reservations_taxes' => number_format(array_sum($reservations_taxes),2),
                        'reservations_services_taxes' => number_format(array_sum($reservations_services_taxes),2),
                        'reservations_deposit_insurance_transactions' => number_format(array_sum($reservations_deposit_insurance_transactions) / ($main_reservation->wallet->decimal_places == 3 ? 1000 : 100 ),2),
                        'reservations_paid' => number_format(array_sum($reservations_paid),2),
                        'vat_taxes' => number_format(array_sum($vat_taxes) + array_sum($reservations_services_taxes) ,2),
                        'ewa_taxes' => number_format(array_sum($ewa_taxes),2),
                        'ttx_taxes' => number_format(array_sum($ttx_taxes),2),
                        'creditor' => number_format(array_sum($creditor),2),
                        'debitor' => number_format(array_sum($debitor),2),

                    ],
                    'dates_calculations' => startAndEndDateCalculatorWithNights($reservations),
                    'group_balance' => array_sum($balances),
                    'locale' => $locale ,
                    'print' => $request->has('type') && $request->get('type') == 'embed' ? false :  true
                ]);
        }
    }
    public function getGroupReservationSummaryPublic(Request $request)
    {
        $locale = $request->get('locale');
        app()->setlocale($locale);
        $decode = Hashids::decode($request->get('hs'));
        $reservation_id = reset($decode);
        $balances  = [];
        $reservations_total_prices = [];
        $reservations_total_services = [];
        $reservations_subtotals = [];
        $reservations_taxes = [];
        $vat_taxes = [];
        $ewa_taxes = [];
        $ttx_taxes = [];
        $reservations_services_without_taxes = [];
        $reservations_services_taxes = [];
        $reservations_deposit_insurance_transactions = [];
        $reservations_transactions = [];
        $reservations_services = [];
        $creditor = [];
        $debitor = [];
        $reservation = Reservation::find($reservation_id);
        $push_main_reservation_to_collection = false;
        if($reservation->reservation_type == 'group'){
            $main_reservation = null ;
            if(is_null($reservation->attachable_id)){
                $main_reservation = $reservation;
                $push_main_reservation_to_collection = false;
            }else{
                $main_reservation = Reservation::find($reservation->attachable_id);
                $push_main_reservation_to_collection = true;
            }

            $reservations = Reservation::with('wallet')
                    ->where('reservation_type' , 'group')
                    ->where('company_id' , $reservation->company_id)
                    ->where(function ($query) use($reservation,$main_reservation) {
                        return $query->where('id',$reservation->id)->orWhere('attachable_id',$main_reservation->id);
                    })
                    ->where('status' , 'confirmed')
                    // ->whereNull('checked_out')
                    ->whereNull('deleted_at')
                    ->orderBy('created_at')
                    ->get();

            if($push_main_reservation_to_collection){
                $reservations->push($main_reservation);
            }


            foreach($reservations as $reservationObject){
                $balances [] = $reservationObject->wallet->decimal_places == 3 ? $reservationObject->balance / 1000 : $reservationObject->balance / 100;
                $reservations_total_prices [] = $reservationObject->total_price;
                $reservations_total_services [] = $reservationObject->getServicesSum();
                $reservations_subtotals [] = $reservationObject->sub_total;
                $reservations_taxes [] = $reservationObject->ewa_total + $reservationObject->vat_total + $reservationObject->ttx_total;
                $reservations_services_without_taxes [] = $reservationObject->getServicesWithoutTaxesSum();
                $reservations_services_taxes [] = $reservationObject->getServicesTaxesSum();
                $reservations_deposit_insurance_transactions [] = $reservationObject->depositInsuranceTransactions()->sum('amount');
                $reservations_paid [] = $reservationObject->getDepositSum() - $reservationObject->getWithdrawSum();
                $vat_taxes [] = $reservationObject->vat_total;
                $ewa_taxes [] = $reservationObject->ewa_total;
                $ttx_taxes [] = $reservationObject->ttx_total;
                if($reservationObject->services()->count()){
                    $reservations_services [] =   collect($reservationObject->services)->toArray();
                }
                if($reservationObject->transactions()->count()){
                    $reservations_transactions [] = collect($reservationObject->transactions)->toArray();
                }
                $creditor [] = $reservationObject->getDepositSum();
                $debitor [] = $reservationObject->getWithdrawSum() + $reservationObject->getServicesSum() + $reservationObject->total_price;
            }

            return view('print.company_liveـsummary', [
                    'units_count' => $reservations->count(),
                    'main_reservation' => $main_reservation,
                    'company' => $main_reservation->company,
                    'team' => $main_reservation->company->team,
                    'reservations_services' => $reservations_services ?  array_merge(...$reservations_services) : [],
                    'reservations_transactions' => $reservations_transactions ? array_merge(...$reservations_transactions) : [],
                    'calculations' => [
                        'reservations_total_prices_with_services' => number_format(array_sum($reservations_subtotals) + array_sum($reservations_total_services) + array_sum($reservations_taxes),2),
                        'reservations_subtotals' => number_format(array_sum($reservations_subtotals),2),
                        'reservations_services_without_taxes' => number_format(array_sum($reservations_services_without_taxes),2),
                        'leasing_with_services' => number_format(array_sum($reservations_subtotals) + array_sum($reservations_services_without_taxes) , 2),
                        'reservations_taxes' => number_format(array_sum($reservations_taxes),2),
                        'reservations_services_taxes' => number_format(array_sum($reservations_services_taxes),2),
                        'reservations_deposit_insurance_transactions' => number_format(array_sum($reservations_deposit_insurance_transactions) / ($main_reservation->wallet->decimal_places == 3 ? 1000 : 100 ),2),
                        'reservations_paid' => number_format(array_sum($reservations_paid),2),
                        'vat_taxes' => number_format(array_sum($vat_taxes) + array_sum($reservations_services_taxes) ,2),
                        'ewa_taxes' => number_format(array_sum($ewa_taxes),2),
                        'ttx_taxes' => number_format(array_sum($ttx_taxes),2),
                        'creditor' => number_format(array_sum($creditor),2),
                        'debitor' => number_format(array_sum($debitor),2),

                    ],
                    'dates_calculations' => startAndEndDateCalculatorWithNights($reservations),
                    'group_balance' => array_sum($balances),
                    'locale' => $locale ,
                    'print' => $request->has('type') && $request->get('type') == 'embed' ? false :  true
                ]);
        }
    }


    public function dummyCompanyInvoice()
    {
        return view('print.dummy_company_invoice');
    }


    function getCreditNotePrint(Request  $request , $id)
    {
        $decode     = Hashids::decode("$id");
        $id         = reset($decode);
        $locale     = app()->getLocale();

        $has_invoice = $request->get('nov') ? false : true;

        if($request->has('nov') && $request->get('nov')){

            $invoice = null;

            $credit_note = InvoiceCreditNote::with('team')->find($id);
            $vat_for_services = 0;
            if(isset($credit_note->payload['services'])){
                foreach ($credit_note->payload['services'] as $service) {
                    if(isset($service['vat'])){
                        $vat_for_services += $service['vat'];
                    }
                }
            }
    
        
            $total_vat =  $vat_for_services;
            // E-Invoice Compatiable with ZACAH phase 1
            $displayQRCodeAsBase64 = GenerateQrCode::fromArray([
                new Seller($credit_note->team->name), // seller name
                new TaxNumber(getSettingItem($credit_note->team_id , 'tax_number')), // seller tax number
                new InvoiceDate($credit_note->payload['inv_created_at']), // invoice date as Zulu ISO8601 @see https://en.wikipedia.org/wiki/ISO_8601
                new InvoiceTotalAmount($credit_note->payload['total_with_taxes']), // invoice total amount
                new InvoiceTaxAmount(number_format($total_vat,2)) // invoice tax amount
            ])->render();

        }else{
            $credit_note = InvoiceCreditNote::with('invoice' , 'invoice.reservation' , 'invoice.reservation.team')->find($id);
            $invoice = $credit_note->invoice;
            $vat_for_services = 0;
            if(isset($invoice->data['services'])){
                foreach ($invoice->data['services'] as $service) {
                    if(isset($service['vat'])){
                        $vat_for_services += $service['vat'];
                    }
                }
            }
    
            $total_vat = $invoice->data['vat'] + $vat_for_services;
            // E-Invoice Compatiable with ZACAH phase 1
            $displayQRCodeAsBase64 = GenerateQrCode::fromArray([
                new Seller($invoice->reservation->team->name), // seller name
                new TaxNumber(getSettingItem($invoice->reservation->team_id , 'tax_number')), // seller tax number
                new InvoiceDate($invoice->created_at), // invoice date as Zulu ISO8601 @see https://en.wikipedia.org/wiki/ISO_8601
                new InvoiceTotalAmount($invoice->data['amount']), // invoice total amount
                new InvoiceTaxAmount(number_format($total_vat,2)) // invoice tax amount
            ])->render();
        }
        
        if($has_invoice){
            $showFandaqahQr = !$invoice->data['vat'] ? true : false;
        }else{
            $showFandaqahQr = !$credit_note->payload['vat_total'] ? true : false;
        }

        
        return view('print.invoice_credit_note', [
            'team' => $credit_note->team,
            'has_invoice' => $has_invoice,
            'credit_note' => $credit_note,
            'invoice' => $has_invoice ? $invoice : null,
            'locale' => $locale ,
            'print' => $request->has('type') && $request->get('type') == 'embed' ? false :  true,
            'qrCode' => $displayQRCodeAsBase64,
            'showFandaqahQr' => $showFandaqahQr
        ]);
    }


    function getCreditNotePublic(Request $request , $id)
    {
        $decode  = Hashids::decode("$id");
        $id      = reset($decode);
        $credit_note = InvoiceCreditNote::with('invoice' , 'invoice.reservation' , 'invoice.reservation.team')->find($id);
        $invoice = $credit_note->invoice;
        app()->setlocale($request->get('lang'));

        $vat_for_services = 0;
        if(isset($invoice->data['services'])){
            foreach ($invoice->data['services'] as $service) {
                if(isset($service['vat'])){
                    $vat_for_services += $service['vat'];
                }
            }
        }

        $total_vat = $invoice->data['vat'] + $vat_for_services;
         // E-Invoice Compatiable with ZACAH phase 1
         $displayQRCodeAsBase64 = GenerateQrCode::fromArray([
            new Seller($invoice->reservation->team->name), // seller name
            new TaxNumber(getSettingItem($invoice->reservation->team_id , 'tax_number')), // seller tax number
            new InvoiceDate($invoice->created_at), // invoice date as Zulu ISO8601 @see https://en.wikipedia.org/wiki/ISO_8601
            new InvoiceTotalAmount($invoice->data['amount']), // invoice total amount
            new InvoiceTaxAmount(number_format($total_vat,2)) // invoice tax amount
        ])->render();
        return view('print.invoice_credit_note', [
            'credit_note' => $credit_note,
            'invoice' => $invoice,
            'locale' => $request->get('lang') ,
            'print' => false,
            'qrCode' => $displayQRCodeAsBase64,
            'showFandaqahQr' => !$invoice->data['vat'] ? true : false
        ]);
    }

    public function getCompanyInvoiceCreditNotePrint(Request $request , $id)
    {
        $decode     = Hashids::decode("$id");
        $id         = reset($decode);
        $locale     = $request->get('locale');
        $credit_note = InvoiceCreditNote::with('invoice' , 'invoice.reservation' , 'invoice.reservation.team')->find($id);
        $invoice = $credit_note->invoice;

        $total_vat = 0;
        foreach ($invoice->data['reservations_minified'] as $obj) {
            $total_vat += $obj['vat'];
        }
        $main_reservation = null;
        $ids = [];
        foreach ($invoice->data['reservations'] as $reservation) {
            $ids [] = $reservation['id'];
            if(is_null($reservation['attachable_id'])){
                $main_reservation = $reservation;
            }
        }

        $services_vat = 0;
        if(count($invoice->data['services'])){
            foreach ($invoice->data['services'] as $transaction) {

                foreach ($transaction['meta']['services'] as $service) {
                    $vat [] = $service['vat'];
                }
            }

            $services_vat = array_sum($vat);
        }

        $res_collection = collect(Reservation::whereIn('id',$ids)->get());
        // E-Invoice Compatiable with ZACAH phase 1
        $displayQRCodeAsBase64 = GenerateQrCode::fromArray([
            new Seller($invoice->reservation->team->name), // seller name
            new TaxNumber(getSettingItem($invoice->reservation->team_id , 'tax_number')), // seller tax number
            new InvoiceDate($credit_note->created_at->toIso8601ZuluString()), // invoice date as Zulu ISO8601 @see https://en.wikipedia.org/wiki/ISO_8601
            new InvoiceTotalAmount($invoice->data['amount']), // invoice total amount
            new InvoiceTaxAmount(number_format( ($total_vat + $services_vat) ,2)) // invoice tax amount
        ])->render();

        return view('print.company_invoice_credit_note', [
            'credit_note' => $credit_note,
            'invoice' => $invoice,
            'locale' => $locale ,
            'print' => $request->has('type') && $request->get('type') == 'embed' ? false :  true,
            'qrCode' => $displayQRCodeAsBase64,
            'showFandaqahQr' => $invoice->data['has_at_least_one_vat'] ? false : true,
            'team' => auth()->user()->currentTeam,
            'main_reservation' => $main_reservation,
            'dates_calculations' => startAndEndDateCalculatorWithNights($res_collection),
            'services_vat' => $services_vat
        ]);
    }

    public function getGroupInvoicePrint(Request $request)
    {
        $decode  = Hashids::decode($request->get('inv'));
        $id      = reset($decode);
        $locale = $request->get('locale');
        $invoice = ReservationInvoice::with('reservation' , 'reservation.team' , 'reservation.unit' , 'reservation.company' , 'reservation.customer' , 'reservation.creator' , 'reservation.comments','team','unit')->findOrFail($id);
        $vat_for_services = 0;
        if(isset($invoice->data['services'])){
            foreach ($invoice->data['services'] as $service) {
                if(isset($service['vat']) && $service['vat']){
                    $vat_for_services += $service['vat'];
                    $has_at_least_one_vat = true;
                }
            }
        }

        $vat_for_reservations = 0;
        if(isset($invoice->data['has_at_least_one_vat'])){
            foreach ($invoice->data['reservations_minified'] as $obj) {
                $vat_for_reservations += $obj['vat'];
            }
        }

        $total_vat = $vat_for_reservations + $vat_for_services;
        $main_reservation = null;
        $ids = [];
        $has_at_least_one_vat = false;
        if(isset($invoice['data']['free_services_invoices'])){
            if(is_null($invoice->reservation->attachable_id)){
                $main_reservation = $invoice->reservation;
                $push_main_reservation_to_collection = false;
            }else{
                $main_reservation = Reservation::find($invoice->reservation->attachable_id);
                 $push_main_reservation_to_collection = true;
            }

            $current_reservation = $invoice->reservation;
            $reservations = Reservation::with('wallet','unit')
            ->where('reservation_type' , 'group')
            ->where('company_id' , $main_reservation->company_id)
            ->where(function ($query) use($current_reservation,$main_reservation) {
                return $query->with('unit')->where('id',$current_reservation->id)->orWhere('attachable_id',$main_reservation->id);
            })
            ->where('status' , 'confirmed')
            ->whereNull('deleted_at')
            ->get();

            if($push_main_reservation_to_collection){
                $reservations->push($main_reservation);
            }
            $ids  = collect($reservations)->pluck('id')->toArray();
        }else{
            $has_at_least_one_vat = $invoice->data['has_at_least_one_vat'];
            if(isset($invoice->data['reservations'])){
                foreach ($invoice->data['reservations'] as $reservation) {
                    $ids [] = $reservation['id'];
                    if(is_null($reservation['attachable_id'])){
                        $main_reservation = $reservation;
                    }
                }
            }
        }

        // dd($invoice->data['amount']);
        $res_collection = collect(Reservation::whereIn('id',$ids)->get());
        // E-Invoice Compatiable with ZACAH phase 1
        $displayQRCodeAsBase64 = GenerateQrCode::fromArray([
            new Seller($invoice->reservation->team->name), // seller name
            new TaxNumber(getSettingItem($invoice->reservation->team_id , 'tax_number') ? getSettingItem($invoice->reservation->team_id , 'tax_number') : '-'), // seller tax number
            new InvoiceDate($invoice->created_at->toIso8601ZuluString()), // invoice date as Zulu ISO8601 @see https://en.wikipedia.org/wiki/ISO_8601
            new InvoiceTotalAmount( (float) $invoice->data['amount']), // invoice total amount
            new InvoiceTaxAmount( (float) $total_vat) // invoice tax amount
        ])->render();

        $extra_addon = null;
        if(isset($invoice->data['reservations'])){
            if(count($invoice->data['reservations']) == 1){
               $extra_addon =  [
                    'customer_name' => $invoice->data['reservations'][0]['customer']['name'],
                    'customer_id_number' => $invoice->data['reservations'][0]['customer']['id_number'],
                    'unit_name' => $invoice->unit ? $invoice->unit->name : $invoice->data['reservations'][0]['unit']['name'][app()->getLocale()],
                    'unit_number' => $invoice->unit ? $invoice->unit->unit_number : $invoice->data['reservations'][0]['unit']['unit_number'],
               ];
            }
        }else{
            // no reservations attached
            $extra_addon = [
                'customer_name' => $invoice->reservation->customer->name,
                'customer_id_number' => $invoice->reservation->customer->id_number,
                'unit_name' => $invoice->unit ? $invoice->unit->name : $invoice->reservation->unit->name,
                'unit_number' => $invoice->unit ? $invoice->unit->unit_number : $invoice->reservation->unit->unit_number,
            ];
        }
   
        // return view('print.group-invoice', [
        return view(getSettingItem($invoice->reservation->team_id , 'print_invoice_in_two_lang') ? 'print.group-invoice-new' : 'print.group-invoice', [
            'invoice' => $invoice,
            'locale' => $locale ,
            'print' => $request->has('type') && $request->get('type') == 'embed' ? false :  true,
            'qrCode' => $displayQRCodeAsBase64,
            'main_reservation' => $main_reservation,
            'team' => auth()->user()->currentTeam,
            'has_at_least_one_vat' => $has_at_least_one_vat,
            'dates_calculations' => startAndEndDateCalculatorWithNights($res_collection),
            'extra_addon' =>  $extra_addon
        ]);

    }

    public function getGroupInvoicePrintPublic(Request $request)
    {
        $decode  = Hashids::decode($request->get('inv'));
        $id      = reset($decode);
        $locale = $request->get('locale');
        $invoice = ReservationInvoice::withoutGlobalScopes()->with('reservation' , 'reservation.team' , 'reservation.unit' , 'reservation.customer' , 'reservation.creator' , 'reservation.comments')->findOrFail($id);
        app()->setLocale($locale);
        $vat_for_services = 0;
        $main_reservation = null;
        $vat_for_reservations = 0;
        if(isset($invoice->data['services'])){
            foreach ($invoice->data['services'] as $service) {
                if(isset($service['vat'])){
                    $vat_for_services += $service['vat'];
                }
            }
        }

        $vat_for_reservations = 0;
        if(isset($invoice->data['reservations_minified']) && isset($invoice->data['has_at_least_one_vat'])){
            foreach ($invoice->data['reservations_minified'] as $obj) {
                $vat_for_reservations += $obj['vat'];
            }
        }
        $ids = [];
        $has_at_least_one_vat = false;
        if(isset($invoice['data']['free_services_invoices'])){
            if(is_null($invoice->reservation->attachable_id)){
                $main_reservation = $invoice->reservation;
                $push_main_reservation_to_collection = false;
            }else{
                $main_reservation = Reservation::find($invoice->reservation->attachable_id);
                 $push_main_reservation_to_collection = true;
            }

            $current_reservation = $invoice->reservation;
            $reservations = Reservation::with('wallet','unit')
            ->where('reservation_type' , 'group')
            ->where('company_id' , $main_reservation->company_id)
            ->where(function ($query) use($current_reservation,$main_reservation) {
                return $query->with('unit')->where('id',$current_reservation->id)->orWhere('attachable_id',$main_reservation->id);
            })
            ->where('status' , 'confirmed')
            ->whereNull('deleted_at')
            ->get();

            if($push_main_reservation_to_collection){
                $reservations->push($main_reservation);
            }
            $ids  = collect($reservations)->pluck('id')->toArray();
        }else{
            $has_at_least_one_vat = $invoice->data['has_at_least_one_vat'];
            if(isset($invoice->data['reservations'])){
                foreach ($invoice->data['reservations'] as $reservation) {
                    $ids [] = $reservation['id'];
                    if(is_null($reservation['attachable_id'])){
                        $main_reservation = $reservation;
                    }
                }
            }
        }
        $total_vat = $vat_for_reservations + $vat_for_services;

        $res_collection = collect(Reservation::withoutGlobalScopes()->whereIn('id',$ids)->get());
        // E-Invoice Compatiable with ZACAH phase 1
        $displayQRCodeAsBase64 = GenerateQrCode::fromArray([
            new Seller($invoice->reservation->team->name), // seller name
            new TaxNumber(getSettingItem($invoice->reservation->team_id , 'tax_number') ? getSettingItem($invoice->reservation->team_id , 'tax_number') : '-'), // seller tax number
            new InvoiceDate($invoice->created_at->toIso8601ZuluString()), // invoice date as Zulu ISO8601 @see https://en.wikipedia.org/wiki/ISO_8601
            new InvoiceTotalAmount( (float) $invoice->data['amount']), // invoice total amount
            new InvoiceTaxAmount( (float) $total_vat) // invoice tax amount
        ])->render();

        $team = Team::find($invoice->team_id);
        $extra_addon = null;
        if(isset($invoice->data['reservations'])){
            if(count($invoice->data['reservations']) == 1){
               $extra_addon =  [
                    'customer_name' => $invoice->data['reservations'][0]['customer']['name'],
                    'customer_id_number' => $invoice->data['reservations'][0]['customer']['id_number'],
                    'unit_name' => $invoice->data['reservations'][0]['unit']['name'][app()->getLocale()],
                    'unit_number' => $invoice->data['reservations'][0]['unit']['unit_number'],
               ];
            }
        }else{
            // no reservations attached
            $extra_addon = [
                'customer_name' => $invoice->reservation->customer->name,
                'customer_id_number' => $invoice->reservation->customer->id_number,
                'unit_name' => $invoice->reservation->unit->name,
                'unit_number' => $invoice->reservation->unit->unit_number,
            ];
        }

        // return view('print.group-invoice', [
        return view(getSettingItem($invoice->reservation->team_id , 'print_invoice_in_two_lang') ? 'print.group-invoice-new' : 'print.group-invoice', [
            'invoice' => $invoice,
            'locale' => $locale ,
            'print' => $request->has('type') && $request->get('type') == 'embed' ? false :  true,
            'qrCode' => $displayQRCodeAsBase64,
            'main_reservation' => $main_reservation,
            'team' => $team,
            'has_at_least_one_vat' => $has_at_least_one_vat,
            'dates_calculations' => startAndEndDateCalculatorWithNights($res_collection),
            'extra_addon' =>  $extra_addon
        ]);

    }

    public function getGroupInvoiceCreditNotePrint(Request $request)
    {
        $id = $request->get('cnid');
        $decode     = Hashids::decode("$id");
        $id         = reset($decode);
        $locale     = $request->get('locale');
        $credit_note = InvoiceCreditNote::with('invoice' , 'invoice.reservation' , 'invoice.reservation.team')->find($id);
        $invoice = $credit_note->invoice;

        $main_reservation = null;
        $ids = [];
        $reservations = [];
        $vat_for_reservations = 0;
        $has_at_least_one_vat = false;
        if(isset($invoice['data']['free_services_invoices'])){
            if(is_null($invoice->reservation->attachable_id)){
                $main_reservation = $invoice->reservation;
                $push_main_reservation_to_collection = false;
            }else{
                $main_reservation = Reservation::find($invoice->reservation->attachable_id);
                 $push_main_reservation_to_collection = true;
            }

            $current_reservation = $invoice->reservation;
            $reservations = Reservation::with('wallet','unit')
            ->where('reservation_type' , 'group')
            ->where('company_id' , $main_reservation->company_id)
            ->where(function ($query) use($current_reservation,$main_reservation) {
                return $query->with('unit')->where('id',$current_reservation->id)->orWhere('attachable_id',$main_reservation->id);
            })
            ->where('status' , 'confirmed')
            ->whereNull('deleted_at')
            ->get();

            if($push_main_reservation_to_collection){
                $reservations->push($main_reservation);
            }
            $ids  = collect($reservations)->pluck('id')->toArray();

        }else{
            $has_at_least_one_vat = $invoice->data['has_at_least_one_vat'];
            if(isset($invoice->data['reservations'])){
                foreach ($invoice->data['reservations'] as $reservation) {
                    $ids [] = $reservation['id'];
                    if(is_null($reservation['attachable_id'])){
                        $main_reservation = $reservation;
                    }
                }
            }

            if( isset($invoice->data['reservations_minified']) && $invoice->data['has_at_least_one_vat']){
                foreach ($invoice->data['reservations_minified'] as $obj) {
                    $vat_for_reservations += $obj['vat'];
                }
            }
        }



        $vat_for_services = 0;
        if(isset($invoice->data['services'])){
            foreach ($invoice->data['services'] as $service) {
                if(isset($service['vat'])){
                    $vat_for_services += $service['vat'];
                    $has_at_least_one_vat = true;
                }
            }
        }

        $total_vat = $vat_for_reservations + $vat_for_services;

        $res_collection = collect(Reservation::whereIn('id',$ids)->get());
        // E-Invoice Compatiable with ZACAH phase 1
        $displayQRCodeAsBase64 = GenerateQrCode::fromArray([
            new Seller($invoice->reservation->team->name), // seller name
            new TaxNumber(getSettingItem($invoice->reservation->team_id , 'tax_number')), // seller tax number
            new InvoiceDate($credit_note->created_at->toIso8601ZuluString()), // invoice date as Zulu ISO8601 @see https://en.wikipedia.org/wiki/ISO_8601
            new InvoiceTotalAmount( (float) $invoice->data['amount']), // invoice total amount
            new InvoiceTaxAmount( (float) $total_vat) // invoice tax amount
        ])->render();
        return view('print.group_invoice_credit_note', [
            'credit_note' => $credit_note,
            'invoice' => $invoice,
            'locale' => $locale ,
            'print' => $request->has('type') && $request->get('type') == 'embed' ? false :  true,
            'qrCode' => $displayQRCodeAsBase64,
            'showFandaqahQr' =>  $has_at_least_one_vat ? false : true,
            'team' => auth()->user()->currentTeam,
            'main_reservation' => $main_reservation,
            'dates_calculations' => startAndEndDateCalculatorWithNights($res_collection),
            'reservations_count' => count($reservations)
        ]);
    }

    public function getGroupInvoiceCreditNotePrintPublic(Request $request)
    {
        $id = $request->get('cnid');
        $decode     = Hashids::decode("$id");
        $id         = reset($decode);
        $locale     = $request->get('locale');
        app()->setLocale($locale);
        $credit_note = InvoiceCreditNote::with('invoice' , 'invoice.reservation' , 'invoice.reservation.team')->find($id);
        $invoice = ReservationInvoice::withoutGlobalScopes()->find($credit_note->reservation_invoice_id);
        $main_reservation = null;
        $ids = [];
        $reservations = [];
        $vat_for_reservations = 0;
        $has_at_least_one_vat = false;
        if(isset($invoice['data']['free_services_invoices'])){
            if(is_null($invoice->reservation->attachable_id)){
                $main_reservation = $invoice->reservation;
                $push_main_reservation_to_collection = false;
            }else{
                $main_reservation = Reservation::find($invoice->reservation->attachable_id);
                 $push_main_reservation_to_collection = true;
            }

            $current_reservation = $invoice->reservation;
            $reservations = Reservation::with('wallet','unit')
                ->where('reservation_type' , 'group')
                ->where('company_id' , $main_reservation->company_id)
                ->where(function ($query) use($current_reservation,$main_reservation) {
                    return $query->with('unit')->where('id',$current_reservation->id)->orWhere('attachable_id',$main_reservation->id);
                })
                ->where('status' , 'confirmed')
                ->whereNull('deleted_at')
                ->get();

            if($push_main_reservation_to_collection){
                $reservations->push($main_reservation);
            }
            $ids  = collect($reservations)->pluck('id')->toArray();
        }else{
            $has_at_least_one_vat = $invoice->data['has_at_least_one_vat'];
            if(isset($invoice->data['reservations'])){
                foreach ($invoice->data['reservations'] as $reservation) {
                    $ids [] = $reservation['id'];
                    if(is_null($reservation['attachable_id'])){
                        $main_reservation = $reservation;
                    }
                }
            }

            if( isset($invoice->data['reservations_minified']) && $invoice->data['has_at_least_one_vat']){
                foreach ($invoice->data['reservations_minified'] as $obj) {
                    $vat_for_reservations += $obj['vat'];
                }
            }
        }

        $vat_for_services = 0;
        if(isset($invoice->data['services'])){
            foreach ($invoice->data['services'] as $service) {
                if(isset($service['vat'])){
                    $vat_for_services += $service['vat'];
                    $has_at_least_one_vat = true;
                }
            }
        }

        $total_vat = $vat_for_reservations + $vat_for_services;

        $res_collection = collect(Reservation::withoutGlobalScopes()->whereIn('id',$ids)->get());
        // E-Invoice Compatiable with ZACAH phase 1
        $displayQRCodeAsBase64 = GenerateQrCode::fromArray([
            new Seller($invoice->reservation->team->name), // seller name
            new TaxNumber(getSettingItem($invoice->reservation->team_id , 'tax_number')), // seller tax number
            new InvoiceDate($credit_note->created_at->toIso8601ZuluString()), // invoice date as Zulu ISO8601 @see https://en.wikipedia.org/wiki/ISO_8601
            new InvoiceTotalAmount( (float) $invoice->data['amount']), // invoice total amount
            new InvoiceTaxAmount( (float) $total_vat) // invoice tax amount
        ])->render();

        return view('print.group_invoice_credit_note', [
            'credit_note' => $credit_note,
            'invoice' => $invoice,
            'locale' => $locale ,
            'print' => $request->has('type') && $request->get('type') == 'embed' ? false :  true,
            'qrCode' => $displayQRCodeAsBase64,
            'showFandaqahQr' => $has_at_least_one_vat ? false : true,
            'team' => $invoice->reservation->team,
            'main_reservation' => $main_reservation,
            'dates_calculations' => startAndEndDateCalculatorWithNights($res_collection),
            'reservations_count' => count($reservations)
        ]);
    }
    public function sendPromissorySMS (Request $request) {
        $reservation_id = $request->get('reservation_id');
        $team = Team::find(\Auth::user()->current_team_id);
        $sms_title = null;
        
        $sms_title = $team->name . ' - ' .  __('Promissory',[],$request->lang) ; 
        
    
        if($request->has('reservation_id')){
          $reservation = Reservation::with('company')->with('customer')->find($request->get('reservation_id'));
          $phone = $reservation->customer->phone;
          if($reservation && $reservation->reservation_type == 'group'){
            if($reservation->company && $reservation->company->entity_type == 'company' && $reservation->company->person_incharge_phone ){
              $phone = preg_replace('/\s+/', '', $reservation->company->person_incharge_phone );
            }
          }
        }
    
        $data = [
            'team_id' => $team->id,
            'contract_url' => $sms_title . ' ' . $request->promissory_url,
            'customer_phone' => $phone
        ];

        SendContractToSignedDigitalyViaSMS::dispatch($data);
    
        return back()->with('success', __('A message will be sent shortly'));

    }
}
