<?php

namespace App\Http\Controllers\api;

use App\Term;
use App\Unit;
use App\User;
use stdClass;
use App\Company;
use App\Setting;
use App\Customer;
use Carbon\Carbon;
use App\Integration;
use App\Reservation;
use App\TeamCounter;
use App\Transaction;
use App\Objects\Invoice;
use Carbon\CarbonPeriod;
use App\InvoiceCreditNote;
use App\ReservationInvoice;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddCompanyRequest;
use App\SpecialPrice;

class confirmBookingMytravel extends Controller
{
    public function confirmBooking(Request $request)
    {

        $integration = Integration::where('key', 'ZatcaPhaseTwo')->where('team_id', $request->team_id)->first();

        $counter = TeamCounter::where('team_id', $request->team_id)->first();
        $tax_number = Setting::where('key', 'tax_number')->where('team_id', $request->team_id)->first();
        $tax_number = $tax_number->value;
        if (!$counter) {
            $counter = TeamCounter::create();
            $counter->forceFill([
                'team_id' => $request->team_id,
            ])->save();
        }

        $reservation = Reservation::find($request->reservation_id);
        $reservation->number = $counter->reservation_num;
        $counter->last_reservation_number =  $counter->reservation_num;
        $counter->save();

        $first_deposit_term = Term::where('team_id', $request->team_id)->where('type', 2)->first();

        if ($request->preprocessor == 'hyperpay') {

            $meta = [
                'category' => 'reservation',
                'statement' => 'Billed Online',
                'type' => $first_deposit_term ? $first_deposit_term->id : null,
                'payment_type' => 'credit',
                'note' => $request->meta['note'],
                'reference' => $request->reference,
                'date' => Carbon::now()->format('Y-m-d H:i'),
                'from' => $request['guest_name'],
                'employee' => null,
                'preprocessor' => $request->preprocessor
            ];

            $transaction = $reservation->depositFloat(floatval($request->amount), $meta, true, true);
        }
        $reservation->status = 'confirmed';

        $reservation->save();

        $unit = Unit::find($reservation->unit_id);

        $unit_number = $unit->unit_number;

        $fromDate = Carbon::parse($reservation->date_in_time)->utc()->format('Y-m-d\TH:i:s.000\Z');
        $toDate = Carbon::parse($reservation->date_out_time)->utc()->format('Y-m-d\TH:i:s.000\Z');

        $orginal_to = new \DateTime($reservation->date_in_time);
        $orginal_from = new \DateTime($reservation->date_out_time);
        $total_nighs = $orginal_to->diff($orginal_from)->days;

        $group_invoice_data = new Request();
        $group_invoice_data->merge([

            "id" => $reservation->id,
            "team_id" => $request->team_id,
            "attachable_id" => null,
            "all_grouped_reservations_ids" => [
                0 => $reservation->id
            ],
            'amount' => $request->amount,
            "dates_calculations" => [
                "start_date" => $fromDate,
                "end_date" => $toDate,
                "first_checked_in_date" => null,
                "last_checked_out_date" => null,
                "nights" => $total_nighs,
            ],
            "from_date" => $fromDate,
            "to_date" => $toDate,
            "note" => null,
            "company_id" => $reservation->company_id,
        ]);
        $hijri_date = convertGregorianToHijriDate($request->created_at);
        if ($request->amount > 0 && $request->enable_invoicing) {

            $add_group_invoice = $this->createGroupInvoice($group_invoice_data);
        }
        if (isset($integration->values)) {
            $zatca_values = json_decode($integration->values, true);
            $response = [
                'unit_number' => $unit_number,
                'zatca_integration' => $zatca_values,
                'tax_number' => $tax_number,
                'hijri_date' => $hijri_date,
                'reservation_number' => $reservation->number,
            ];
            return $response;
        } else {
            $response = [
                'unit_number' => $unit_number,
                'hijri_date' => $hijri_date,
                'reservation_number' => $reservation->number,
            ];
            return $response;
        }
    }
    public function storeCompany(AddCompanyRequest $request)
    {

        $company = Company::updateOrCreate([
            'email' => $request->get('email'),
            'team_id' => $request->get('team_id'),
        ], [
            'user_id' => $request->get('user_id'),
            'name' => $request->get('name'),
            'phone' => str_replace(' ', '', $request->get('phone')),
            'city' => $request->get('city'),
            'address' => $request->get('address'),
            'person_incharge_name' => $request->get('person_incharge_name'),
            'person_incharge_phone' => $request->get('person_incharge_phone'),
            'tax_number' => $request->get('tax_number'),
        ]);

        if ($request->has('reservation_id')) {
            $reservation = Reservation::find($request->get('reservation_id'));

            if ($request->get('reservation_type') == 'group') {
                // its a group reservation of entity type individual and needs a company
                $push_main_reservation_to_collection = false;
                if (is_null($reservation->attachable_id)) {
                    $main_reservation = $reservation;
                    $push_main_reservation_to_collection = false;
                } else {
                    $main_reservation = Reservation::find($reservation->attachable_id);
                    $push_main_reservation_to_collection = true;
                }
                $reservations = Reservation::with('wallet', 'unit')
                    ->where('reservation_type', 'group')
                    ->where('company_id', $reservation->company_id)
                    ->where(function ($query) use ($reservation, $main_reservation) {
                        return $query->with('unit')->where('id', $reservation->id)->orWhere('attachable_id', $main_reservation->id);
                    })
                    ->where('status', 'confirmed')
                    ->whereNull('deleted_at')
                    ->get();

                if ($push_main_reservation_to_collection) {
                    $reservations->push($main_reservation);
                }

                if (count($reservations)) {
                    foreach ($reservations as $reservationObject) {
                        $reservationObject->company_id = $company->id;
                        $reservationObject->save();
                    }

                    return response()->json(['success' => true, 'reload_reservation' => true], Response::HTTP_CREATED);
                }
            } else {
                // its a single reservation
                $reservation->company_id = $company->id;
                $reservation->reservation_type = 'group';
                $reservation->save();

                return $company->id;
            }
        }
        return response()->json(['success' => true, 'company' => $company], Response::HTTP_CREATED);
    }
    public function add_Invoice($invoice_data, $note)
    {
        $reservation_id = $invoice_data['reservation_id'];
        $amount = $invoice_data['amount'];

        $from_date = $invoice_data['from_date'];
        $to_date = $invoice_data['to_date'];


        $reservation = Reservation::find($reservation_id);

        $team_id = $reservation->team_id;

        $invoice = new ReservationInvoice();
        $invoice->team_id = $team_id;

        $invoice->reservation_id = $reservation_id;
        $invoice->from = $from_date;
        $invoice->to = $to_date;
        $counter = TeamCounter::where('team_id', $team_id)->first();

        $next = $counter->invoice_num;
        $invoice->number = $next;
        $invoice->note = $note;
        $counter->last_invoice_number = $next;


        $orginal_to = new \DateTime($to_date);
        $orginal_from = new \DateTime($from_date);
        $total_nighs = $orginal_to->diff($orginal_from)->days;
        $user = User::updateOrCreate([
            'email' => "info@dyafa.com"
        ], [
            'current_team_id' => $team_id,
            'name' => 'Dyafa Booking Engine',
            'phone' => "+966558439000",
            'billing_address' => "Prince Turky Street",
            'password' => bcrypt('fandaqah2024'),
        ]);
        $dyafa_user_id = $user->id;


        $aftertax = $amount;
        $teamid = $team_id;
        $tax = (getVatPercentageForUnit($teamid) === null) ? 0 : getVatPercentageForUnit($teamid);
        $ewa = (getEwaPercentageForUnit($teamid) === null) ? 0 : getEwaPercentageForUnit($teamid);
        $tax = $tax / 100;
        $ewa = $ewa / 100;
        $total_withot_vat = $aftertax / ($tax + 1);
        $total__without_ewa = $total_withot_vat / ($ewa + 1);
        $total_vat = $aftertax - $total_withot_vat;
        $total_ewa = $total_withot_vat - $total__without_ewa;
        $subtotal = $aftertax - $total_vat - $total_ewa;
        $total_price = $aftertax;


        $data = new \stdClass();
        $data->sub_total = (float) number_format($subtotal, 2, '.', '');
        $data->vat = (float) number_format($total_vat, 2, '.', '');
        $data->ewa = (float) number_format($total_ewa, 2, '.', '');
        $data->ttx = 0;
        $data->total_price = (float) number_format($total_price, 2, '.', '');
        $data->nights = $total_nighs;
        $data->servicesSum = 0;
        $data->services = [];
        $data->transactions_ids = [];
        $data->amount = (float) number_format($total_price + $data->servicesSum, 2, '.', '');
        $invoice->data = $data;

        $invoice->created_by = $dyafa_user_id;

        $user = User::find($dyafa_user_id);
        $counter->save();

        $this->syncInvoiceOnCreate($invoice, 'tax invoice', 'invoice', '0', $team_id, $dyafa_user_id);
        $invoice->save();
        $customer_id = $reservation->customer_id;

        $customer = Customer::find($customer_id);
        $customer->name = 'My Travel';
        $customer->save();

        $id = $invoice->id;
        return $id;
    }

    public function getSupplierEGS($team_id)
    {


        $settings = (object) \App\Setting::where('team_id', $team_id)->whereIn('key', ['hotel_address', 'hotel_email', 'hotel_phone_number', 'tax_number', 'city', 'district', 'street', 'commercial_register'])->pluck('value', 'key')->all();
        //get from table teams where id = $team_id
        $team = \App\Team::find($team_id);


        if (!isset($settings->tax_number)) {
            return null;
        }

        return array(
            "model" => "Team-" . $team_id,
            "uuid" => '1-' . $team_id . '-' . $settings->tax_number,
            "solution_name" => "Sol-" . $team_id,
            "vat_number" => $settings->tax_number ?? "",
            "location" => array(
                "city" => $settings->city ?? "",
                "city_subdivision" => $settings->district ?? "",
                "street" => $settings->street ?? "",
                "plot_identification" => $settings->plot_no ?? "",
                "building" => $settings->building ?? "",
                "postal_zone" => $settings->postal_zone ?? "",
            ),
            "branch_industry" => $settings->industry ?? "hospitality",
            "branch_name" =>  $settings->branch ?? "Test branch",
            "org_name" => $team->name,
            "taxpayer_name" => $team->name,
            "CRN_number" => $settings->commercial_register ?? "",
            "production" => env('APP_ENV') == 'production' ? "1" : "0",
            "team_id" => $team_id
        );
    }

    public function syncInvoiceOnCreate(ReservationInvoice $invoice, $invoice_type, $invoice_sub_type, $mark_credit_notes_as_sent, $team_id, $my_travel_user_id)
    {
        //reset in order to update it with newer payload
        $invoice->is_reported_to_zatca = null;
        $integration = Integration::findByKeyAndTeamId('ZatcaPhaseTwo', $team_id);

        if (!isset($integration[0]->values)) {
            return $invoice;
        } else {
            $total_amount_excl_tax = 0;
            $total_amount_incl_tax = 0;
            $total_tax_amount = 0;
            $items = [];

            //  $org = auth()->user()->getSupplierEGS();
            // insted of auth()->user() we will use the user with id 22
            $auth_user = User::find($my_travel_user_id);

            $org = $this->getSupplierEGS($team_id);


            if ($org == null) {
                return $invoice;
            }
            $credential = (object) json_decode($integration)[0]->values;
            $credential = json_decode($credential->scalar);

            $invoice_type = 'tax invoice';

            $zatcaInvoice = new Invoice($credential->username, $credential->password, $invoice_type, $invoice_sub_type, $org);

            $invoice_data = (object) $invoice->data;

            $company = (object) array(
                "name" => "MyTravel",
                "tax_number" => "",
                "street" => "Prince Turki",
                "building" => "1000",
                "city_subdivision" => "South Khobar",
                "city" => "Khobar",
                "postal_zone" => "14455",
                "countryCode" => "SA"

            );
            // response()->json($company->countryCode);
            $zatcaInvoice->setCustomerInformation(
                $company->tax_number ?? "",
                $company->name ?? "",
                $company->countryCode ?? "SA",
                $company->street ?? "",
                $company->building ?? "",
                $company->city_subdivision ?? "",
                $company->city ?? "",
                $company->postal_zone ?? ""
            );


            //group reservations invoice

            if (isset($invoice_data->reservations_minified)) {
                foreach ($invoice_data->reservations_minified as $key => $reservation) {
                    $prices = (object) $reservation;
                    $total_amount_excl_tax =  number_format(floatval($prices->sub_total ?? 0.0), 2, '.', '') + $total_amount_excl_tax;
                    $total_amount_incl_tax = floatval($prices->sub_total ?? 0.0) + floatval($prices->vat ?? 0.0) + $total_amount_incl_tax;
                    $total_tax_amount = number_format(floatval($prices->vat ?? 0.0), 2, '.', '') + $total_tax_amount;
                }
                //single reservation invoice
            } elseif (isset($invoice->reservation)) {
                $prices = (object) $invoice->reservation->prices;
                $total_amount_excl_tax = number_format(floatval($prices->sub_total ?? 0.0), 2, '.', '') + $total_amount_excl_tax;
                $total_amount_incl_tax = floatval($prices->sub_total ?? 0.0) + floatval($prices->total_vat ?? 0.0) + $total_amount_incl_tax;
                $total_tax_amount =  number_format(floatval($prices->total_vat ?? 0.0), 2, '.', '') + $total_tax_amount;
            }

            $total_amount_excl_tax = number_format((float)$total_amount_excl_tax, 2, '.', '');
            $total_amount_incl_tax = number_format((float)$total_amount_incl_tax, 2, '.', '');
            $total_tax_amount = number_format((float)$total_tax_amount, 2, '.', '');

            $zatcaInvoice->setTaxTotal($total_tax_amount ?? "0.0", $total_amount_excl_tax ?? "0.0");
            $zatcaInvoice->setMonetaryTotal($total_amount_incl_tax ?? "0.0", $total_amount_excl_tax ?? "0.0", null, null);
            //quantity -> nights
            //total_amount
            if (isset($invoice_data->reservations)) {
                foreach ($invoice_data->reservations as $key => $reservation_item) {
                    $reservation_item = (object) $reservation_item;
                    $unit = (object) $invoice->reservation->unit->name;
                    $prices =  (object) $invoice_data->reservations_minified[$key];

                    $total_amount_incl_tax = floatval($prices->sub_total ?? 0.0) + floatval($prices->vat ?? 0.0);
                    $total_amount_incl_tax = number_format((float)$total_amount_incl_tax, 2, '.', '');

                    array_push($items, array(
                        "quantity" => '1',
                        "total_amount_excl_tax" => number_format((float)$prices->sub_total ?? 0.0, 2, '.', ''),
                        "total_tax_amount" => number_format((float)$prices->vat ?? 0.0, 2, '.', ''),
                        "total_amount_incl_tax" => $total_amount_incl_tax,
                        "item_name" => $unit->scalar ?? "",
                        "item_cost" => number_format((float)$prices->sub_total ?? 0.0, 2, '.', ''),
                        "item_discount" => '0.0'
                    ));
                }
            } elseif (isset($invoice->reservation)) {
                $unit = (object) $invoice->reservation->unit->name;
                // return response()->json($unit);
                $prices = (object) $invoice->reservation->prices;

                $total_amount_incl_tax = floatval($prices->sub_total ?? 0.0) + floatval($prices->total_vat ?? 0.0);
                $total_amount_incl_tax = number_format((float)$total_amount_incl_tax, 2, '.', '');

                array_push($items, array(
                    "quantity" => '1',
                    "total_amount_excl_tax" => number_format((float)$prices->sub_total ?? 0.0, 2, '.', ''),
                    "total_tax_amount" => number_format((float)$prices->total_vat ?? 0.0, 2, '.', ''),
                    "total_amount_incl_tax" => $total_amount_incl_tax,
                    "item_name" => $unit->scalar ?? "",
                    "item_cost" => number_format((float)$prices->sub_total ?? 0.0, 2, '.', ''),
                    "item_discount" => '0.0'
                ));
            }

            $zatcaInvoice->setItems($items);

            $datetime = Carbon::parse($invoice->created_at);
            $zatcaInvoice->setIssueDateTime($datetime->format('Y-m-d'), $datetime->format('h:i:s'));

            $zatcaInvoice->setInvoiceBillingReferenceId($invoice->number);

            if ($zatcaInvoice->checkIfCreditNote($invoice_sub_type)) {
                $zatcaInvoice->setCanceledInvoiceBillingReferenceId($invoice->number);
                $zatcaInvoice->setPaymentInstruction("Returned");
            }

            //return response()->json($zatcaInvoice);
            $compliant_invoice = $zatcaInvoice->getCompliantInvoice();

            //activity()->performedOn((new IntegrationSettings()))->log(__('Team Id :TEAM has reported Invoice#:INVOICE to zatca successfully', ['team' => $key, 'invoice' => '']));
            if (
                !isset($compliant_invoice->data->base64_signed_invoice_string) &&
                !isset($compliant_invoice->data->invoice_hash) &&
                !isset($compliant_invoice->data->uuid)
            ) {
                activity()->performedOn((new ReservationInvoice()))->log(__('Invoice :ID push to zatca has been failed', ['id' => $invoice->id]));
                return $invoice;
            }

            $response = $zatcaInvoice->reportInvoice($compliant_invoice);

            if (!isset($response->status) || $response->status !== 200 || $response == null) {
                return $invoice;
            }

            if (isset($response->data->reportingStatus) && $response->data->reportingStatus == "REPORTED") {
                $invoice->is_reported_to_zatca = $response->data;
            }

            if (isset($response->data->clearanceStatus) && $response->data->clearanceStatus == "CLEARED") {
                $invoice->is_reported_to_zatca = $response->data;
            }

            //since invoice has been credit we check credit notes and mark them as synced with zatca
            if ($mark_credit_notes_as_sent == "1") {
                $invoice_credit_note = InvoiceCreditNote::where("reservation_invoice_id", $invoice->id)->first();

                if ($invoice_credit_note) {
                    $zatcaInvoice->setCanceledInvoiceBillingReferenceId($invoice->number);
                    $zatcaInvoice->setPaymentInstruction("Returned");
                    $zatcaInvoice->setInvoiceSubType("credit note");
                    $compliant_invoice = $zatcaInvoice->getCompliantInvoice();

                    if (
                        !isset($compliant_invoice->data->base64_signed_invoice_string) &&
                        !isset($compliant_invoice->data->invoice_hash) &&
                        !isset($compliant_invoice->data->uuid)
                    ) {
                        activity()->performedOn((new ReservationInvoice()))->log(__('Invoice :ID push to zatca has been failed', ['id' => $invoice->id]));
                        return $invoice;
                    }

                    $response = $zatcaInvoice->reportInvoice($compliant_invoice);

                    if (!isset($response->status) || $response->status !== 200 || $response == null) {
                        return response()->json($response, 500);
                    }

                    if (isset($response->data->reportingStatus) && $response->data->reportingStatus == "REPORTED") {
                        $invoice_credit_note->is_reported_to_zatca = json_encode($response->data);
                    }

                    if (isset($response->data->clearanceStatus) && $response->data->clearanceStatus == "CLEARED") {
                        $invoice_credit_note->is_reported_to_zatca = json_encode($response->data);
                    }

                    $invoice_credit_note->save();

                    activity()->performedOn((new InvoiceCreditNote()))->log(__('Invoice :ID credit note has been pushed to zatca successfully', ['id' => $invoice->id]));
                }
            }

            //emit activity
            activity()->performedOn((new ReservationInvoice()))->log(__('Invoice :ID has been pushed to zatca successfully', ['id' => $invoice->id]));

            return $invoice;
        }
    }

    public function createGroupInvoice(Request $request)
    {
        $team_id = $request->team_id;
        $company = Company::find($request->get('company_id'));
        $invoice = new ReservationInvoice();
        $invoice->team_id = $team_id;
        $invoice->from = date(Carbon::createFromTimestamp(strtotime($request->get('from_date')))->format('Y-m-d'));
        $invoice->to = date(Carbon::parse($request->get('to_date'))->addDay()->format('Y-m-d'));
        $counter = TeamCounter::where('team_id', $team_id)->first();
        $next = $counter->invoice_num;
        $invoice->number = $next;
        $invoice->note = $request->note;
        $counter->last_invoice_number = $next;
        $to = new \DateTime($invoice->to);
        $from = new \DateTime($invoice->from);
        $parsedFrom = Carbon::parse($from);
        $parsedTo = Carbon::parse($to);
        $period = CarbonPeriod::create($parsedFrom, $parsedTo);
        $main_sub_total = 0;
        $reservations = Reservation::whereIn('id', $request->get('all_grouped_reservations_ids'))->get();
        $reservations_subtotals_arr = [];
        $reservations_ewa_percentage_arr = [];
        $reservations_vat_percentage_arr = [];
        $reservations_tourism_percentage_arr = [];
        $balances = [];
        $periods = [];
        $has_at_least_one_vat = false;
        $reservations_units_holder = [];
        if (count($reservations)) {
            foreach ($reservations as $reservation) {
                $reservation_scoped_sub_total = 0;
                if (is_null($reservation->attachable_id)) {
                    $invoice->reservation_id = $reservation->id;
                }
                $reservations_ewa_percentage_arr[$reservation->id] = $reservation->old_prices['ewa_parentage'];
                $reservations_vat_percentage_arr[$reservation->id] = $reservation->old_prices['vat_parentage'];
                $reservations_tourism_percentage_arr[$reservation->id] = $reservation->old_prices['tourism_percentage'];
                $balances[] = $reservation->wallet->decimal_places == 3 ? $reservation->balance / 1000 : $reservation->balance / 100;
                $reservations_units_holder[$reservation->id][] =  [
                    'id' => $reservation->unit->id,
                    'name' => json_decode($reservation->unit->getOriginal('name')),
                    'unit_number' => $reservation->unit->unit_number
                ];
                if ($reservation->vat_total) {
                    $has_at_least_one_vat = true;
                }
                foreach ($period as $date) {
                    if ($reservation->rent_type == 1) {
                        // it's a daily reservation
                        foreach ($reservation->prices['days'] as $obj) {
                            if (Carbon::parse($obj['date'])->format('Y-m-d') == $date->format('Y-m-d')) {
                                $unit_category_id = $reservation->unit->unit_category_id;
                                $specialPrice = SpecialPrice::where('unit_category_id', $unit_category_id)->where('start_date', '<=', $obj['date'])->where('end_date', '>=', $obj['date'])->where('enabled', true)->first();
                                $dayName = Carbon::parse($obj['date'])->format('l');
                                if($specialPrice){
                                    $main_sub_total = (float) number_format($specialPrice->days_prices[$dayName], 2, '.', '');
                                    $reservation_scoped_sub_total = (float) number_format($specialPrice->days_prices[$dayName], 2, '.', '');

                                    if($main_sub_total == null && $reservation_scoped_sub_total == null){
                                        $main_sub_total = $obj['price'];
                                        $reservation_scoped_sub_total = $obj['price'];

                                    }
                                }else{
                                    $main_sub_total = $obj['price'];
                                    $reservation_scoped_sub_total = $obj['price'];
                                }
                                $reservations_subtotals_arr[$reservation->id][] = $reservation_scoped_sub_total;
                                $periods[$reservation->id][] = $date->format('Y-m-d');
                            }
                        }
                    } else {
                        // it's a monthly reservation
                        // i need to construct an array that will hold days and create period here for monthly reservations
                        $monthlyDays = [];
                        $monthlyStart = new \DateTime($reservation->date_in);
                        $monthlyEnd = new \DateTime($reservation->date_out);
                        $parsedMonthlyStart = Carbon::parse($monthlyStart);
                        $parsedMonthlyEnd = Carbon::parse($monthlyEnd);

                        if ($parsedMonthlyStart != $parsedMonthlyEnd) {
                            $parsedMonthlyEnd->subDay();
                        }

                        $monthlyPeriod = CarbonPeriod::create($parsedMonthlyStart, $parsedMonthlyEnd);
                        $single_night_in_month_price = $reservation->sub_total / $reservation->nights;
                        foreach ($monthlyPeriod as $obj) {
                            if (Carbon::parse($obj)->format('Y-m-d') == $date->format('Y-m-d')) {
                                $main_sub_total += $single_night_in_month_price;
                                $reservation_scoped_sub_total = $single_night_in_month_price;
                                $reservations_subtotals_arr[$reservation->id][] = $reservation_scoped_sub_total;
                                $periods[$reservation->id][] = $date->format('Y-m-d');
                            }
                        }
                    }
                }
            }
        }
        $reservations_minified = [];
        $reservations_minified_keyed = [];
        $services_sum = 0;
        $services = [];
        $invoice_total_reservation_amount = 0;
        foreach ($reservations_subtotals_arr as $key => $subtotals_arr) {
            $current_reservation_ewa_percentage =  $reservations_ewa_percentage_arr[$key];
            $current_reservation_vat_percentage =  $reservations_vat_percentage_arr[$key];
            $current_reservation_tourism_percentage =  $reservations_tourism_percentage_arr[$key];
            $current_reservation_sub_total = array_sum($subtotals_arr);
            $ewa_amount = ($current_reservation_sub_total / 100) * $current_reservation_ewa_percentage;
            $ttx_amount = ($current_reservation_sub_total / 100) * $current_reservation_tourism_percentage;
            $vat_amount = (($current_reservation_sub_total + $ewa_amount) / 100) * $current_reservation_vat_percentage;
            $reservation_std = new stdClass();
            $reservation_std->id = $key;
            $reservation_std->unit = $reservations_units_holder[$key];
            $reservation_std->ewa = (float) number_format($ewa_amount, 2, '.', '');
            $reservation_std->ttx = (float) number_format($ttx_amount, 2, '.', '');
            $reservation_std->vat = (float) number_format($vat_amount, 2, '.', '');
            $reservation_std->sub_total = (float) number_format($current_reservation_sub_total, 2, '.', '');
            $reservation_std->total_price = (float) number_format($current_reservation_sub_total + $ewa_amount + $ttx_amount + $vat_amount, 2, '.', '');
            $reservations_minified[] = $reservation_std;

            $reservation_std_keyed = new stdClass();
            $reservation_std_keyed->id = $key;
            $reservation_std_keyed->ewa = (float) number_format($ewa_amount, 2, '.', '');
            $reservation_std_keyed->ttx = (float) number_format($ttx_amount, 2, '.', '');
            $reservation_std_keyed->vat = (float) number_format($vat_amount, 2, '.', '');
            $reservation_std_keyed->sub_total = (float) number_format($current_reservation_sub_total, 2, '.', '');
            $reservation_std_keyed->total_price = (float) number_format($current_reservation_sub_total + $ewa_amount + $ttx_amount + $vat_amount, 2, '.', '');
            $reservations_minified_keyed[$key][] = $reservation_std_keyed;
            $invoice_total_reservation_amount += $reservation_std->total_price;
        }

        /**
         * The part of attaching services to invoices - very important
         */
        $maximum_possible_date_to_as_invoice = Carbon::parse($request->get('dates_calculations')['end_date'])->subDay()->format('Y-m-d');
        $grouped_reservations_ids = $request->get('all_grouped_reservations_ids');;
        if ($invoice->to == $maximum_possible_date_to_as_invoice) {
            $filterServicesForGroupReservationInoice = $this->filterServicesForGroupReservationInvoice($grouped_reservations_ids, $from, $to, true);
        } else {
            $filterServicesForGroupReservationInoice = $this->filterServicesForGroupReservationInvoice($grouped_reservations_ids, $from, $to);
        }

        $services_sum  = abs($filterServicesForGroupReservationInoice['servicesSum']);
        if (isset($filterServicesForGroupReservationInoice['services'])) {
            foreach ($filterServicesForGroupReservationInoice['services'] as $service) {
                $services[] = $service;
            }
        }

        $invoice_total_amount_with_services = (float) number_format($invoice_total_reservation_amount + $services_sum, 2, '.', '');

        $custom_reservations = [];
        if (count($reservations)) {
            foreach ($reservations as $reservation) {
                $reservationSkeleton = new stdClass();
                $reservationSkeleton->id = $reservation->id;
                $reservationSkeleton->attachable_id = $reservation->attachable_id;
                $reservationSkeleton->number = $reservation->number;
                $reservationSkeleton->ewa_total = $reservation->ewa_total;
                $reservationSkeleton->vat_total = $reservation->vat_total;
                $reservationSkeleton->sub_total = $reservation->sub_total;
                $reservationSkeleton->total_price = $reservation->total_price;
                $reservationSkeleton->pre_included_services_total = 0;
                $reservationSkeleton->unit =  [
                    'id' => $reservation->unit->id,
                    'name' => json_decode($reservation->unit->getOriginal('name')),
                    'unit_number' => $reservation->unit->unit_number
                ];
                $reservationSkeleton->customer =  $reservation->customer ?  [
                    'id' => $reservation->customer->id,
                    'name' => $reservation->customer->name,
                    'id_number' => $reservation->customer->id_number,
                    'phone' => $reservation->customer->phone,
                ] : null;

                $reservationSkeleton->old_prices = [
                    'ewa_parentage' => $reservation->old_prices['ewa_parentage'],
                    'vat_parentage' => $reservation->old_prices['vat_parentage'],
                ];
                $reservationSkeleton->rent_type = $reservation->rent_type;
                $custom_reservations[] = $reservationSkeleton;
            }
        }

        $final_data = [
            'amount' => (float) number_format($request->amount, 2, '.', ''),
            'company' => $company,
            'services' => $services,
            'extra_addon' => null,
            'reservations' => $custom_reservations,
            'group_balance' => array_sum($balances),
            'has_at_least_one_vat' => $has_at_least_one_vat,
            'reservations_minified' => $reservations_minified,
            'reservations_minified_keyed' => $reservations_minified_keyed,
            'periods' => $periods,
            'transactions_ids' => $filterServicesForGroupReservationInoice['transactions_ids']
        ];

        $invoice->data = $final_data;
        $invoice->is_group_reservation = 1;
        $user = User::updateOrCreate([
            'email' => "info@dyafa.com"
        ], [
            'current_team_id' => $team_id,
            'name' => 'Dyafa Booking Engine',
            'phone' => "+966558439000",
            'billing_address' => "Prince Turky Street",
            'password' => bcrypt('fandaqah2024'),
        ]);
        $dyafa_user_id = $user->id;
        $invoice->created_by = $dyafa_user_id;
        $counter->save();

        $invoice = $this->syncInvoiceOnCreate($invoice, "tax invoice", "invoice", "0", $team_id, $dyafa_user_id);
        $invoice->save();
        return response()->json(['invoice' => $invoice->load('invoiceCreditNote')]);
    }

    private function filterServicesForGroupReservationInvoice($reservations_ids, $from, $to, $is_last_invoice = false)
    {
        $services = [];
        $servicesSum = [];
        if ($is_last_invoice) {
            $servicesTransactions  = Transaction::with('wallet')
                ->where('payable_type', 'App\\Reservation')
                ->whereIn('payable_id', $reservations_ids)
                ->where('is_public', 0)
                ->where('meta->category', 'service')
                ->where('is_attached_to_invoice', 0)
                ->get();
        } else {
            $servicesTransactions  = Transaction::with('wallet')
                ->where('payable_type', 'App\\Reservation')
                ->whereIn('payable_id', $reservations_ids)
                ->where('is_public', 0)
                ->where('meta->category', 'service')
                ->whereDate('created_at', '>=', $from)
                ->whereDate('created_at', '<=', $to)
                ->get();
        }
        // $servicesTransactions  = Transaction::with('wallet')->where('payable_type', 'App\\Reservation')->where('payable_id', $reservation_id)->where('is_public', 0)->where('meta->category', 'service')->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->get();
        $transactions_ids = [];
        if (count($servicesTransactions)) {
            foreach ($servicesTransactions as $transaction) {
                $servicesSum[] = $transaction->amount / ($transaction->wallet->decimal_places == 3 ? 1000 : 100);
                foreach ($transaction->meta['services'] as $serviceObj) {
                    $services[] = $serviceObj;
                    $transaction->is_attached_to_invoice = 1;
                    $transactions_ids[] = $transaction->id;
                    $transaction->save();
                }
            }
        }
        return ['services' => $services, 'servicesSum' => array_sum($servicesSum), 'transactions_ids' => $transactions_ids];
    }
}
