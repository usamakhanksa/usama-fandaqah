<?php

namespace Surelab\Pos\Http\Controllers;

use App\Team;
use App\Term;
use App\Wallet;
use App\Service;
use Carbon\Carbon;
use App\ServiceLog;
use App\Integration;
use App\Reservation;
use App\TeamCounter;
use App\Transaction;
use App\ServiceLogNote;
use App\Objects\Invoice;
use Carbon\CarbonPeriod;
use App\ServicesCategory;
use App\Handlers\Settings;
use App\InvoiceCreditNote;
use Illuminate\Http\Request;
use App\Events\ReservationUpdated;
// use App\Events\QoyodTransactionHandler;
// use App\Events\Qoyod\PosTransactionHandler;
use Illuminate\Routing\Controller;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendPosInvoiceToCustomer;

use App\Services\ZATCA\Phase2\Constant;
use App\Http\Resources\Pos\ServiceResource;
use App\Http\Resources\Pos\ReservationResource;

class PosController extends Controller
{

    /**
     * Get a list of categories
     * @return \Illuminate\Http\JsonResponse
     */
    public function getServicesCategories()
    {
        $categories = ServicesCategory::where('status', 1)->where('show_in_pos', 1)->where('team_id', auth()->user()->current_team_id)->orderBy('order', 'ASC')->get();
        return response()->json($categories);
    }

    /**
     * Get a list of services per category
     * @param Request $request
     * @param $cat_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getServicesPerCategory(Request $request, $cat_id)
    {
        $services = ServiceResource::collection(Service::where('services_category_id', $cat_id)->where('status', 1)->orderBy('order', 'ASC')->get());
        return response()->json($services);
    }

    public function getTaxesSettingsInformation(Request $request)
    {

        return response()->json(['vat_tax' => Settings::get('tax'), 'ttx_tax' => Settings::get('tourism_tax')]);
    }

    /**
     * Get a list of reservation where has check-in with no checkout
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOccupiedUnits(Request $request)
    {

        $reservations = ReservationResource::collection(Reservation::where('team_id', auth()->user()->current_team_id)
            ->where('status', 'confirmed')
            ->whereNotNull('checked_in')
            ->whereNull('checked_out')
            ->with(['unit', 'customer'])
            ->get());

        return response()->json($reservations);
    }


    /**
     * Add Services According to Reservation
     * Services ( Products ) will be included to customer bills
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addServices(Request $request)
    {

        // i commented it out cause am now sending date from vue timezoned to riyadh
        // if ($request->get('date') == "Invalid date") {
        //     $request->merge(['date' => Carbon::now()->format('Y-m-d H:i')]);
        // }

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

        $reservation_id = $request->get('reservation_id');
        $sumGeneralTotalWithTaxes = $request->get('sumGeneralTotalWithTaxes');
        $sumQuantities = $request->get('sumQuantities');
        $sumSubTotal = $request->get('sumSubTotal');
        $sumVatTotal = $request->get('sumVatTotal');
        $sumTtxTotal = $request->get('sumTtxTotal');
        $model = Reservation::with(['invoices', 'group_reservation'])->find($reservation_id);


        $dates_array = [];

        if (is_null($model->checked_out) && $model->pure_invoices_without_credit_notes->count()) {


            foreach ($model->pure_invoices_without_credit_notes as $invoice) {

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
                    return response()->json(['flag' => 'forbidden', 'invoice_number' => $invoice['number'], 'reservation' => $model]);
                }
            }
        }

        $catch_first_service_id = collect($services)->pluck('id')->toArray()[0];
        $serviceObject = Service::find($catch_first_service_id);

        // needed this cause there was no way to handle zero
        $services_filtered = [];
        $i = 1;
        foreach ($services as $service) {

            // Forming meta
            $serviceObj = new \stdClass();
            $serviceObj->id = $service['id'];
            $serviceObj->category = 'service';
            $serviceObj->statement = $service['name'];
            $serviceObj->qty = $service['qty'];
            $serviceObj->vat = $service['vat_checked'] ? number_format($service['vat_total'], 2) : 0;
            $serviceObj->ttx = $service['ttx_checked'] ? number_format($service['ttx_total'], 2) : 0;
            $serviceObj->vatIsChecked = $service['vat_checked'];
            $serviceObj->ttxIsChecked = $service['ttx_checked'];
            $serviceObj->price = $service['subtotal_price'];
            $serviceObj->sub_total = $service['subtotal_price'] * $service['qty'];
            $serviceObj->totalGeneralSum = $service['total_price'];
            $services_filtered[] = $serviceObj;
            $i++;
        }

        $meta = [];
        $meta['category'] = 'service';
        $meta['pos'] = true;
        $meta['statement'] = $statement;
        $meta['services'] = $services_filtered;
        $meta['total_with_taxes'] = $sumGeneralTotalWithTaxes;
        $meta['sub_total'] = $sumSubTotal;
        $meta['vat_total'] = $sumVatTotal;
        $meta['ttx_total'] = $sumTtxTotal;
        $meta['qty'] = $sumQuantities;
        $meta['date'] = ($request->get('date')) ?? Carbon::now()->format('Y-m-d H:i');
        $meta['from'] = $model->customer->name;
        $meta['payment_type'] = null;
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
        $serviceLog->transaction_id = $transaction->id;
        $serviceLog->type = $transaction->type;
        $serviceLog->number = $last_service_number;
        $serviceLog->amount = $transaction->amount;
        $serviceLog->decimals = $transaction->wallet->decimal_places;
        $serviceLog->meta = $transaction->meta;
        $serviceLog->save();


        event(new ReservationUpdated($model));
        return response()->json($services);
    }

    public function addServicesGeneral(Request $request)
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


        $sumGeneralTotalWithTaxes = $request->get('sumGeneralTotalWithTaxes');
        $sumQuantities = $request->get('sumQuantities');
        $sumSubTotal = $request->get('sumSubTotal');
        $sumVatTotal = $request->get('sumVatTotal');
        $sumTtxTotal = $request->get('sumTtxTotal');
        $customerEmail = $request->get('customerEmail');

        $catch_first_service_id = collect($services)->pluck('id')->toArray()[0];

        $serviceObject = Service::find($catch_first_service_id);
        $model = Team::find(auth()->user()->current_team_id);
        /**
         * @see Copy this to service log
         */
        $counter = $model->counter;
        if (!$counter) {
            $counter = TeamCounter::create();
        }

        $last_service_number = $counter->last_service_number + 1;
        $counter->last_service_number = $last_service_number;
        // needed this cause there was no way to handle zero
        $services_filtered = [];
        $i = 1;
        foreach ($services as $service) {

            // Forming meta
            $serviceObj = new \stdClass();
            $serviceObj->id = $service['id'];
            $serviceObj->category = 'service';
            $serviceObj->statement = $service['name'];
            $serviceObj->qty = $service['qty'];
            $serviceObj->vat = $service['vat_checked'] ? number_format($service['vat_total'], 2) : 0;
            $serviceObj->ttx = $service['ttx_checked'] ? number_format($service['ttx_total'], 2) : 0;
            $serviceObj->vatIsChecked = $service['vat_checked'];
            $serviceObj->ttxIsChecked = $service['ttx_checked'];
            $serviceObj->price = $service['subtotal_price'];
            $serviceObj->sub_total = $service['subtotal_price'] * $service['qty'];
            $serviceObj->totalGeneralSum = $service['total_price'];
            $services_filtered[] = $serviceObj;
            $i++;
        }



        $meta = [];
        $meta['type'] = $this->getServiceTermIdFroCurrentTeam() ? $this->getServiceTermIdFroCurrentTeam()['id'] : null;
        $meta['pos'] = true;
        $meta['reference'] = null;
        $meta['category'] = 'service-deposit';
        // $meta['statement'] = $serviceObject->serviceCategory ? $serviceObject->serviceCategory->name : __('Services');
        $meta['statement'] = $statement;
        $meta['services'] = $services_filtered;
        $meta['total_with_taxes'] = $sumGeneralTotalWithTaxes;
        $meta['sub_total'] = $sumSubTotal;
        $meta['vat_total'] = $sumVatTotal;
        $meta['ttx_total'] = $sumTtxTotal;
        $meta['qty'] = $sumQuantities;
        $meta['date'] = $request->has('date') && $request->get('date') ? Carbon::parse($request->get('date'))->format('Y-m-d H:i') : Carbon::now()->format('Y-m-d H:i');
        $meta['employee'] = auth()->user()->name;
        $meta['payment_type'] = $request->get('paymentMethod');
        $meta['note'] = $request->get('note');
        $meta['customer_name'] = $request->get('customer_name');
        $meta['customer_phone'] = $request->get('customer_phone') ? preg_replace('/\s+/', '', $request->get('customer_phone')) : null;
        $meta['address'] = $request->get('address');
        $meta['tax_number'] = $request->get('tax_number');
        $meta['from'] = __('Loaded On Services Revenue');
        $meta['pay_later'] = false;

        $this->initZatcaMetaData($meta);

        if ($request->get('pay_later')) {
            $meta['pay_later'] = true;
        }

        $active_note = null;
        $reported_invoice = null;
        // if($request->get('sendToZatca') && !$request->get('pay_later')) {
        $reported_invoice = $this->pushInvoiceToZatca($last_service_number, $meta, 'simplified tax invoice', 'invoice');
        $active_note = $reported_invoice !== null ? 'invoice' : null;
        // }
        $model->wallet->refreshBalance();

        if ($request->get('pay_later')) {
            $transaction = $model->wallet->forceWithdrawFloat($sumGeneralTotalWithTaxes, $meta, true, false);
            $transaction->save();
        } else {
            $transaction = $model->wallet->depositFloat($sumGeneralTotalWithTaxes, $meta, true, true);
            $transaction->save();
        }

        $counter->save();

        $serviceLog = new ServiceLog;
        $serviceLog->team_id = $model->id;
        $serviceLog->user_id = auth()->user()->id;
        $serviceLog->transaction_id = $transaction->id;
        $serviceLog->type = $transaction->type;
        $serviceLog->number = $last_service_number;
        $serviceLog->amount = $transaction->amount;
        $serviceLog->decimals = $transaction->wallet->decimal_places;
        $serviceLog->meta = $transaction->meta;
        $serviceLog->active_note = $active_note;
        if ($reported_invoice) {
            $serviceLog->zatca_invoice_number = $reported_invoice->invoice_number;
        }
        $serviceLog->save();



        $service_log_note = new ServiceLogNote();
        $service_log_note->type = 'invoice';
        $service_log_note->service_log_id = $serviceLog->id;
        $service_log_note->payload = isset($reported_invoice) ? $reported_invoice : null;
        $service_log_note->save();

        if ($reported_invoice) {
            $service_log_note = new ServiceLogNote();
            $service_log_note->type = 'invoice';
            $service_log_note->service_log_id = $serviceLog->id;
            $service_log_note->payload = $reported_invoice;
            $service_log_note->save();
        }

        if ($customerEmail) {
            $subject = __('POS Invoice');
            $subject .= ' - ';
            $subject .= $request->teamName;
            $data = [
                'to' => $customerEmail,
                'reply_to' => $request->teamOwnerEmail,
                'subject' => __('Payment Receipt For Reservation : :number',['number' => $transaction->meta['reference']], 'ar'),
                'html' => view('email.customer-pos-invoice')
                ->with(['data' => $request->all()])->render(),
            ];
            $send = sendMailUsingMailMicroservice($data);

            // Mail::to($customerEmail)->send(new SendPosInvoiceToCustomer($request->all()));
        }
        // if(!$request->get('pay_later')){
        //     event(new PosTransactionHandler($transaction,'update-or-create'));
        // }

        return response()->json(['id' => Hashids::encode($transaction->id)], 201);
    }

    public function getServiceTermIdFroCurrentTeam()
    {

        return Term::where('team_id', auth()->user()->current_team_id)->where('deleteable', 0)->whereJsonContains('name->en', 'Services ')->first();
    }


    public function updateServiceTransaction(Request $request)
    {

        $incomingServices = $request->get('items');
        $transaction_id = $request->get('transaction_id');

        $sumGeneralTotalWithTaxes = $request->get('sumGeneralTotalWithTaxes');
        $sumQuantities = $request->get('sumQuantities');
        $sumSubTotal = $request->get('sumSubTotal');
        $sumVatTotal = $request->get('sumVatTotal');
        $sumTtxTotal = $request->get('sumTtxTotal');
        $transaction = Transaction::with('service_log', 'payable')->withTrashed()->find($transaction_id);
        $user = Auth::user();
        if ($transaction->is_freezed == 1 && !$user->hasPermissionTo('edit/delete freezed transactions after business day')) {
            return response()->json([
                'status' => false,
                'message' =>__('messages.transaction_frozen')
            ],400);
        }
        $meta = $transaction->service_log->meta;
        $service_log_id = $transaction->service_log->id;

        //issue credit note
        // $invoice_type = 'credit note';
        // $reported_invoice = $this->pushInvoiceToZatca($service_log_id, $meta, 'simplified tax invoice', $invoice_type);
        // $service_log_note                   = new ServiceLogNote();
        // $service_log_note->type             = $invoice_type;
        // $service_log_note->service_log_id   = $service_log_id;
        // $service_log_note->payload          = $reported_invoice ? $reported_invoice : null;
        // $service_log_note->save();


        $decimals = $transaction->wallet->decimal_places == 3 ? -1000 : -100;

        if (!count($incomingServices)) {
            if ($transaction->payable_type == 'App\\Reservation') {
                $transaction->payable->wallet->refreshBalance();
            }
            $transaction->service_log()->delete();
            $transaction->delete();
            return response()->json(['status' => 'services-deleted']);
        } else {
            // i will update the meta object and remove service missing from it
            $services_filtered = [];

            $services_ids = [];
            $note = [];
            foreach ($incomingServices as $item) {
                $services_ids[] = $item['id'];
                $note[] = ' ' . (string) $item['qty'] . ' × ' . $item['text'];
            }
            $note = implode(' - ', $note);
            $services_categories_ids = collect(Service::withoutGlobalScopes()->with('serviceCategory')->whereIn('id', $services_ids)->get())->pluck('services_category_id')->unique();
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

            if ($transaction->payable_type == 'App\\Team') {

                $meta['type'] = $this->getServiceTermIdFroCurrentTeam() ? $this->getServiceTermIdFroCurrentTeam()['id'] : null;
                $meta['pos'] = true;
                if (is_null($transaction->meta['payment_type'])) {
                    $meta['pay_later'] = true;
                }
                $meta['reference'] = null;
                $meta['category'] = 'service-deposit';
                $meta['statement'] = $statement;
                $meta['services'] = $services_filtered;
                $meta['total_with_taxes'] = $sumGeneralTotalWithTaxes;
                $meta['sub_total'] = $sumSubTotal;
                $meta['vat_total'] = $sumVatTotal;
                $meta['ttx_total'] = $sumTtxTotal;
                $meta['qty'] = $sumQuantities;
                $meta['payment_type'] = $transaction->meta['payment_type'];
                $meta['employee'] = auth()->user()->name;
                $meta['note'] = $note;
                $meta['from'] = __('Loaded On Services Revenue');
                $meta['customer_name'] = $request->get('customer_name');
                $meta['address'] = $request->get('address');
                $meta['tax_number'] = $request->get('tax_number');
            } else {

                $meta['category'] = 'service';
                if (isset($transaction->meta['pos'])) {
                    $meta['pos'] = true;
                }


                $meta['statement'] = $statement;
                $meta['services'] = $services_filtered;
                $meta['total_with_taxes'] = $sumGeneralTotalWithTaxes;
                $meta['sub_total'] = $sumSubTotal;
                $meta['vat_total'] = $sumVatTotal;
                $meta['ttx_total'] = $sumTtxTotal;
                $meta['qty'] = $sumQuantities;
                $meta['from'] = $transaction->payable->customer ? $transaction->payable->customer->name : '-';
                // $meta['payment_type'] = $transaction->meta['payment_type'];
                $meta['note'] = $note;
            }
            $meta['date'] = $request->get('transaction_date') ? $request->get('transaction_date') : Carbon::now()->format('Y-m-d H:i');

            $transaction->amount = $transaction->type == 'deposit' ? abs($sumGeneralTotalWithTaxes * $decimals) : $sumGeneralTotalWithTaxes * $decimals;
            $transaction->meta = $meta;
            $transaction->save();

            $service_log = $transaction->service_log;
            $service_log->amount = $transaction->type == 'deposit' ? abs($transaction->amount) : $transaction->amount;
            $service_log->meta = $transaction->meta;
            $service_log->created_at = $transaction->created_at;


            if($transaction->payable_type == 'App\\Team') {
                //issue debit note
                $service_log_id = $transaction->service_log->id;
                $service_log_number = $transaction->service_log->number;
                $invoice_type = 'debit note';
                $reported_invoice = $this->pushInvoiceToZatca($service_log_number, $meta, 'simplified tax invoice', $invoice_type);
                $service_log_note = new ServiceLogNote();
                $service_log_note->type = $invoice_type;
                $service_log_note->service_log_id = $service_log_id;
                $service_log_note->payload = $reported_invoice ? $reported_invoice : null;
                $service_log->active_note = $reported_invoice ? 'debit note' : null;

                if ($reported_invoice) {
                    $service_log->zatca_invoice_number = $reported_invoice->invoice_number;
                }

                $service_log_note->save();
                $service_log->save();
            }
            // if($transaction->payable_type == 'App\\Reservation'){
            //     event(new ReservationUpdated($transaction->payable));
            // }else{
            //     event(new PosTransactionHandler($transaction,'update-or-create'));
            // }
            return response()->json(['status' => 'services-updated']);
        }
    }


    public function checkDeleteUpdateCapability(Request $request, $id)
    {


        $transaction = Transaction::with('service_log', 'payable.invoices')->withTrashed()->find($id);

        $dates_array = [];

        if (is_null($transaction->payable->checked_out) && $transaction->payable->invoices->count()) {

            foreach ($transaction->payable->invoices as $invoice) {

                // create a period from the current invoice
                $period = CarbonPeriod::create(Carbon::parse($invoice->from), Carbon::parse($invoice->to));

                // iterate through this period  to fill dates array to be used later
                foreach ($period as $key => $date) {
                    $dates_array[] = $date->format('Y-m-d');
                }
                // parse service date
                $service_date = Carbon::parse($transaction->service_log->created_at)->format('Y-m-d');

                // check if service date in_array of dates_array
                // if found then we can not delete this service cause it's included in this invoice and user must delete invoice first

                if (in_array($service_date, $dates_array)) {
                    return response()->json(['flag' => 'forbidden', 'invoice_number' => $invoice['number'], 'reservation' => $transaction->payable]);
                }
            }
        }

        return response()->json($transaction);
    }


    public function deleteTransaction(Request $request)
    {
        $transaction_id = $request->get('id');

        // Find the target transaction
        $transaction = Transaction::with('service_log', 'payable')->withTrashed()->find($transaction_id);
        $user = Auth::user();
        if ($transaction->is_freezed == 1 && !$user->hasPermissionTo('edit/delete freezed transactions after business day')) {
            return response()->json([
                'status' => false,
                'message' =>__('messages.transaction_frozen')
            ],400);
        }
        // delete transaction
        $transaction->service_log()->delete();

        // if($transaction->payable_type == 'App\\Reservation'){
        //     event(new ReservationUpdated($transaction->payable));
        // }else{
        //     event(new PosTransactionHandler($transaction,'delete'));
        // }

        $transaction->delete();
    }

    public function regenerateTransaction(Request $request)
    {
        $transaction = Transaction::withTrashed()->find($request->transaction_id);
        if ($transaction) {

            $team = $transaction->payable;
            $counter = $team->counter;
            if (!$counter) {
                $counter = TeamCounter::create();
            }



            $last_receipt_num = $counter->last_receipt_number + 1;
            $counter->last_receipt_number = $last_receipt_num;
            $counter->save();

            // Set Transaction new amount according to the current pos
            $transaction->amount = $transaction->service_log->amount;

            // i need to iterate though the services and re-create the meta
            // why ? cause may be the user before deleting the transaction he updates it's amount
            // the updated amount lies inside the meta json column , so when i try to recreate
            // okay the transaction will be reborn , but no affect will happen to the meta and this is totally wrong
            $transaction->meta = $transaction->service_log->meta;

            // change transaction serial like it is being created now
            $transaction->number = $last_receipt_num;
            // remove the deleted_at
            $transaction->deleted_at = null;


            $transaction->save();
            // then re-create pos sales invoice agaiiiiin
            // event(new PosTransactionHandler($transaction,'update-or-create'));

        }
    }


    public function createPostponedTransaction(Request $request)
    {


        $service_log = ServiceLog::where('transaction_id', $request->get('oldWithdrawTransactionId'))->first();
        $oldWithdrawTransaction = Transaction::find($request->get('oldWithdrawTransactionId'));
        $oldWithdrawTransaction->delete();
        /** @var Team $team */
        $team = auth()->user()->currentTeam;
        $current_time = date('H:i');
        $incomingDate = $request->meta['date'];
        if (!$this->checkDate($incomingDate)) {
            return response()->json('invalid-date');
        }
        $combinedTransactionDate = date('Y-m-d H:i', strtotime("$incomingDate $current_time"));
        $newMeta = [
            "category" => $request->meta['category'],
            "statement" => $request->meta['statement'],
            "type" => $request->meta['type'],
            "payment_type" => $request->meta['payment_type'],
            "note" => $request->meta['note'],
            "reference" => $request->meta['reference'],
            "date" => $combinedTransactionDate,
            "from" => $request->meta['from'],
            "employee" => $request->meta['employee']
        ];

        $service_log_new_meta = [
            "pos" => $service_log->meta['pos'],
            "qty" => $service_log->meta['qty'],
            "date" => $combinedTransactionDate,
            "from" => $service_log->meta['from'],
            "note" => $service_log->meta['note'],
            "type" => $service_log->meta['type'],
            "category" => $service_log->meta['category'],
            "employee" => $service_log->meta['employee'],
            "services" => $service_log->meta['services'],
            "pay_later" => false,
            "reference" => $request->meta['reference'],
            "statement" => $service_log->meta['statement'],
            "sub_total" => $service_log->meta['sub_total'],
            "ttx_total" => $service_log->meta['ttx_total'],
            "vat_total" => $service_log->meta['vat_total'],
            "total_with_taxes" => $service_log->meta['total_with_taxes'],
            "payment_type" => $request->meta['payment_type'],
        ];

        $transaction = $team->wallet->depositFloat($request->amount, $service_log_new_meta, true, true);

        $service_log_new_meta = [
            "pos" => $service_log->meta['pos'],
            "qty" => $service_log->meta['qty'],
            "date" => $service_log->meta['date'],
            "from" => $service_log->meta['from'],
            "note" => $service_log->meta['note'],
            "type" => $service_log->meta['type'],
            "category" => $service_log->meta['category'],
            "employee" => $service_log->meta['employee'],
            "services" => $service_log->meta['services'],
            "pay_later" => false,
            "reference" => $request->meta['reference'],
            "statement" => $service_log->meta['statement'],
            "sub_total" => $service_log->meta['sub_total'],
            "ttx_total" => $service_log->meta['ttx_total'],
            "vat_total" => $service_log->meta['vat_total'],
            "total_with_taxes" => $service_log->meta['total_with_taxes'],
            "payment_type" => $request->meta['payment_type'],
        ];

        $service_log->transaction_id = $transaction->id;
        $service_log->type = $transaction->type;
        $service_log->amount = $transaction->amount;
        $service_log->meta = $service_log_new_meta;
        $service_log->save();

        return response()->json('success');
    }


    function checkDate($date)
    {
        $tempDate = explode('-', $date);
        if ($tempDate[0] == "") {
            return false;
        }
        return true;
    }

    public function syncUpdateInvoiceToZatca(Request $request)
    {
        $incomingServices = $request->get('items');
        $transaction_id = $request->get('transaction_id');
        $invoice_type = $request->get('invoice_type');

        $sumGeneralTotalWithTaxes = $request->get('sumGeneralTotalWithTaxes');
        $sumQuantities = $request->get('sumQuantities');
        $sumSubTotal = $request->get('sumSubTotal');
        $sumVatTotal = $request->get('sumVatTotal');
        $sumTtxTotal = $request->get('sumTtxTotal');
        $transaction = Transaction::with('service_log', 'payable')->withTrashed()->find($transaction_id);
        $decimals = $transaction->wallet->decimal_places == 3 ? -1000 : -100;

        if (!count($incomingServices)) {
            return response()->json(['status' => 'no-items']);
        } else {
            // i will update the meta object and remove service missing from it
            $services_filtered = [];

            $services_ids = [];
            $note = [];
            foreach ($incomingServices as $item) {
                $services_ids[] = $item['id'];
                $note[] = ' ' . (string) $item['qty'] . ' × ' . $item['text'];
            }
            $note = implode(' - ', $note);
            $services_categories_ids = collect(Service::with('serviceCategory')->whereIn('id', $services_ids)->get())->pluck('services_category_id')->unique();
            $statement = null;
            if (count($services_categories_ids) > 1) {
                $statement = 'various_services';
            } else {
                $service_category = ServicesCategory::whereIn('id', $services_categories_ids)->first();
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

            if ($transaction->payable_type == 'App\\Team') {

                $meta['type'] = $this->getServiceTermIdFroCurrentTeam() ? $this->getServiceTermIdFroCurrentTeam()['id'] : null;
                $meta['pos'] = true;
                if (is_null($transaction->meta['payment_type'])) {
                    $meta['pay_later'] = true;
                }
                $meta['reference'] = null;
                $meta['category'] = 'service-deposit';
                $meta['statement'] = $statement;
                $meta['services'] = $services_filtered;
                $meta['total_with_taxes'] = $sumGeneralTotalWithTaxes;
                $meta['sub_total'] = $sumSubTotal;
                $meta['vat_total'] = $sumVatTotal;
                $meta['ttx_total'] = $sumTtxTotal;
                $meta['qty'] = $sumQuantities;
                $meta['payment_type'] = $transaction->meta['payment_type'];
                $meta['employee'] = auth()->user()->name;
                $meta['note'] = $note;
                $meta['from'] = __('Loaded On Services Revenue');
                $meta['customer_name'] = $request->get('customer_name');
                $meta['address'] = $request->get('address');
                $meta['tax_number'] = $request->get('tax_number');
            } else {

                $meta['category'] = 'service';
                if (isset($transaction->meta['pos'])) {
                    $meta['pos'] = true;
                }


                $meta['statement'] = $statement;
                $meta['services'] = $services_filtered;
                $meta['total_with_taxes'] = $sumGeneralTotalWithTaxes;
                $meta['sub_total'] = $sumSubTotal;
                $meta['vat_total'] = $sumVatTotal;
                $meta['ttx_total'] = $sumTtxTotal;
                $meta['qty'] = $sumQuantities;
                $meta['from'] = $transaction->payable->customer ? $transaction->payable->customer->name : '-';
                $meta['payment_type'] = $transaction->meta['payment_type'];
                $meta['note'] = $note;
            }
            $meta['date'] = $request->get('transaction_date') ? $request->get('transaction_date') : Carbon::now()->format('Y-m-d H:i');

            switch ($invoice_type) {
                case 'invoice':
                    $transaction->type = 'deposit';
                    break;
                case 'debit note':
                    $transaction->type = 'deposit';
                    break;
                case 'credit note':
                    $transaction->type = 'withdraw';
                    break;
            }

            $transaction->amount = $transaction->type == 'deposit' ? abs($sumGeneralTotalWithTaxes * $decimals) : $sumGeneralTotalWithTaxes * $decimals;
            $transaction->meta = $meta;
            $transaction->save();

            $service_log = $transaction->service_log;
            $service_log_id = $transaction->service_log->id;

            $reported_invoice = $this->pushInvoiceToZatca($service_log_id, $meta, 'simplified tax invoice', $invoice_type);

            if ($reported_invoice == null) {
                activity()->performedOn((new ServiceLog()))->log(__('Invoice :ID push to zatca has been failed', ['id' => $service_log->id]));
                return response()->json([
                    'success' => false
                ], 500);
            }

            $service_log->amount = $transaction->type == 'deposit' ? abs($transaction->amount) : $transaction->amount;
            $service_log->meta = $transaction->meta;
            $service_log->created_at = $transaction->created_at;
            $service_log->active_note = $invoice_type;
            if ($reported_invoice) {
                $service_log->zatca_invoice_number = $reported_invoice->invoice_number;
            }
            $service_log->save();


            $service_log_note = new ServiceLogNote();
            $service_log_note->type = $invoice_type;
            $service_log_note->service_log_id = $service_log_id;
            $service_log_note->payload = $reported_invoice;
            $service_log_note->save();

            activity()->performedOn((new ServiceLog()))->log(__(ucfirst($invoice_type) . ' :ID has been pushed to zatca successfully', ['id' => $service_log->id]));

            return response()->json([
                'data' => $transaction,
                'success' => true
            ], 200);
        }
    }

    public function syncInvoiceToZatca($id, $invoice_type)
    {
        $transaction_id = $id;
        $transaction = Transaction::with('service_log', 'payable')->withTrashed()->find($transaction_id);
        $model = Team::find(auth()->user()->current_team_id);
        $service_log = $transaction->service_log;
        //return response()->json($service_log);

        $meta = $service_log->meta;



        $reported_invoice = $this->pushInvoiceToZatca($service_log->id, $meta, 'simplified tax invoice', $invoice_type);

        if ($reported_invoice == null) {
            activity()->performedOn((new ServiceLog()))->log(__('Invoice :ID push to zatca has been failed', ['id' => $service_log->id]));
            return response()->json([
                'success' => false
            ], 500);
        }

        $model->wallet->refreshBalance();

        switch ($invoice_type) {
            case 'invoice':
                $service_log->type = 'deposit';
                $transaction = $model->wallet->depositFloat($transaction->meta['total_with_taxes'], $meta, true, true);
                break;
            case 'debit note':
                $service_log->type = 'deposit';
                $transaction = $model->wallet->depositFloat($transaction->meta['total_with_taxes'], $meta, true, true);
                break;
            case 'credit note':
                $service_log->type = 'withdraw';
                $transaction = $model->wallet->forceWithdrawFloat($transaction->meta['total_with_taxes'], $meta, true, false);
                break;
        }
        //
        $meta['employee'] = auth()->user()->name;
        $meta['payment_type'] = $meta['payment_type'];

        //
        $transaction->type = $service_log->type;
        $transaction->meta = $meta;
        $transaction->save();

        $service_log = $transaction->service_log;
        $service_log->meta = $transaction->meta;
        $service_log->active_note = $invoice_type;
        $service_log->transaction_id = $transaction->id;
        $service_log->created_at = $transaction->created_at;
        $service_log->save();


        //ServiceLog::where("id", $service_log->id)->update(["transaction_id" => $transaction_id, "meta" => $meta, "type" =>  $service_log->type ]);

        $service_log_note = new ServiceLogNote();
        $service_log_note->type = $invoice_type;
        $service_log_note->service_log_id = $service_log->id;
        $service_log_note->payload = $reported_invoice;
        $service_log_note->save();

        activity()->performedOn((new ServiceLog()))->log(__(ucfirst($invoice_type) . ' :ID has been pushed to zatca successfully', ['id' => $service_log->id]));

        return response()->json([
            'data' => $transaction,
            'success' => true
        ], 200);
    }

    function pushInvoiceToZatca($invoice_number, $data, $invoice_type, $invoice_sub_type)
    {
        $total_amount_excl_tax = 0;
        $total_amount_incl_tax = 0;
        $total_tax_amount = 0;
        $items = [];

        $integration = Integration::findByKeyAndTeamId('ZatcaPhaseTwo', auth()->user()->current_team_id)->first();

        if (!$integration) {
            return null;
        }

        $org = auth()->user()->getSupplierEGS();

        if ($org == null) {
            return null;
        }

        $credential = (object) json_decode($integration->values);

        $zatcaInvoice = new Invoice($credential->username, $credential->password, $invoice_type, $invoice_sub_type, $org);

        $zatcaInvoice->setCustomerInformation(
            $data['tax_number'] ?? "",
            $data['customer_name'] ?? "",
            $data['countryCode'] ?? "SA",
            $data['street'] ?? "",
            $data['building'] ?? "",
            $data['city_subdivision'] ?? "",
            $data['city'] ?? "",
            $data['postal_zone'] ?? ""
        );

        $total_amount_excl_tax = number_format((float) $data['sub_total'] ?? 0.0, 2, '.', '');
        $total_amount_incl_tax = number_format((float) $data['total_with_taxes'] ?? 0.0, 2, '.', '');
        $total_tax_amount = number_format((float) $data['vat_total'] ?? 0.0, 2, '.', '');

        $total_amount_incl_tax = floatval($total_amount_excl_tax) + floatval($total_tax_amount);
        $total_amount_incl_tax = number_format($total_amount_incl_tax ?? 0.0, 2, '.', '');

        $zatcaInvoice->setTaxTotal($total_tax_amount, $total_amount_excl_tax);
        $zatcaInvoice->setMonetaryTotal($total_amount_incl_tax, $total_amount_excl_tax, null, null);

        //dd($data['services']);
        if (isset($data['services'])) {
            foreach ($data['services'] as $key => $item) {
                $item = (object) $item;
                $unit_price =  floatval((float) $item->price ?? 0.0);
                $total_amount_incl_tax = number_format((float) $item->totalGeneralSum ?? 0.0, 2, '.', '');
                array_push(
                    $items,
                    array(
                        "quantity" => $item->qty,
                        "total_amount_excl_tax" => number_format((float) $item->sub_total ?? 0.0, 2, '.', ''),
                        "total_tax_amount" => number_format((float) $item->vat ?? 0.0, 2, '.', ''),
                        "total_amount_incl_tax" => $total_amount_incl_tax,
                        "item_name" => $item->statement ?? "",
                        "item_cost" => number_format((float) $unit_price ?? 0.0, 2, '.', ''),
                        "item_discount" => '0.0',
                        "item_vat_percentage" => $item->vatIsChecked ? '15.00' : '0.00'
                    )
                );
            }
        }

        //dd($items);

        $zatcaInvoice->setItems($items);

        $datetime = Carbon::parse($data['date']);

        $zatcaInvoice->setIssueDateTime($datetime->format('Y-m-d'), $datetime->format('h:i:s'));

        $zatcaInvoice->setInvoiceBillingReferenceId($invoice_number);

        if ($zatcaInvoice->checkIfCreditNote($invoice_sub_type) || $zatcaInvoice->checkIfDebitNote($invoice_sub_type)) {
            $zatcaInvoice->setCanceledInvoiceBillingReferenceId($invoice_number);
            $zatcaInvoice->setPaymentInstruction("Returned");
        }

        $compliant_invoice = $zatcaInvoice->getCompliantInvoice();

        if (
            !isset($compliant_invoice->data->base64_signed_invoice_string) &&
            !isset($compliant_invoice->data->invoice_hash) &&
            !isset($compliant_invoice->data->uuid)
        ) {
            activity()->performedOn((new ServiceLog()))->log(__('Invoice :ID push to zatca has been failed', ['id' => $invoice_number]));
            return null;
        }

        $response = $zatcaInvoice->reportInvoice($compliant_invoice);

        if (!isset($response->status) || $response->status !== 200 || $response == null) {
            activity()->performedOn((new ServiceLog()))->log(__('Invoice :ID push to zatca has been failed', ['id' => $invoice_number]));
            return null;
        }

        if (isset($response->data->reportingStatus) && $response->data->reportingStatus !== "REPORTED") {
            return null;

        }
        if (isset($response->data->clearanceStatus) && $response->data->clearanceStatus !== "CLEARED") {
            return null;
        }

        $response->data->invoice = $compliant_invoice->data->base64_signed_invoice_string;
        $response->data->invoice_number = $compliant_invoice->data->invoice_number;
        $response->data->qrcode = $compliant_invoice->data->qrcode;
        $response = $response->data;

        activity()->performedOn((new ServiceLog()))->log(__('Invoice :ID has been pushed to zatca successfully', ['id' => $invoice_number]));

        return $response;
    }


    public function getZatcaEInvoices($transaction_id)
    {
        $transaction = Transaction::with('service_log', 'payable')->withTrashed()->find($transaction_id);
        $service_log_id = $transaction->service_log->id;
        $service_notes = ServiceLogNote::where('service_log_id', $service_log_id)->get();
        return ['service_notes' => $service_notes, 'service_log_number' => $transaction->service_log->number ];
    }

    public function createCreditNote(Request $request)
    {
        $transaction_id = $request->get('transaction_id');
        $transaction = Transaction::with('service_log', 'payable')->withTrashed()->find($transaction_id);
        $service_log = $transaction->service_log;
        $meta = $transaction->service_log->meta;
        $service_log_id = $transaction->service_log->id;
        $service_log_number = $transaction->service_log->number;
        $invoice_type = 'credit note';
        $reported_invoice = $this->pushInvoiceToZatca($service_log_number, $meta, 'simplified tax invoice', $invoice_type);
        //->
        if ($reported_invoice == null) {
            activity()->performedOn((new ServiceLog()))->log(__('Invoice :ID push to zatca has been failed', ['id' => $service_log->id]));
            return response()->json([
                'success' => false
            ], 500);
        }

        if ($reported_invoice) {
            $service_log->zatca_invoice_number = $reported_invoice->invoice_number;
        }

        $service_log->save();

        $service_log_note = new ServiceLogNote();
        $service_log_note->type = $invoice_type;
        $service_log_note->service_log_id = $service_log_id;
        $service_log_note->payload = $reported_invoice ? $reported_invoice : null;
        $service_log->active_note = $reported_invoice ? 'credit note' : $service_log->active_note;
        $service_log_note->save();
        $service_log->save();


        /**
         * Create Credit Note In Fadaqah
         * reservation_invoice_id is null because this is a credit note for a service transaction
         * and not for a reservation invoice
         * there is a payload column that will store the $transaction->service_log->meta 
         */
        $counter = TeamCounter::first();
        $next = $counter->credit_note_num;

        $creditNote = new InvoiceCreditNote();
        $creditNote->number = $next;
        $creditNote->team_id = $transaction->service_log->team_id;
        $counter->last_credit_note_number = $next;
        $creditNote->created_by = auth()->user()->id;

        $metadata = collect($transaction->service_log->meta)->jsonserialize();
        $metadata['inv_number'] = $service_log_number;
        $metadata['inv_created_at'] = Carbon::parse($transaction->service_log->created_at)->format('Y-m-d H:i:s');
        $creditNote->payload = $metadata;
        $creditNote->service_log_id = $service_log_id;
        $counter->save();
        $creditNote->save();

    }

    public function initZatcaMetaData($data)
    {
        $data['zatca_invoice_number'] = null;
        $data[Constant::IS_REPORTED_ZATCA_CREDIT_NOTE] = null;
        $data[Constant::IS_REPORTED_ZATCA_DEBIT_NOTE] = null;
        return $data;
    }

}
