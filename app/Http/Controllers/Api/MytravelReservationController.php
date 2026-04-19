<?php

namespace App\Http\Controllers\Api;

use App\Team;
use App\Unit;
use App\Source;
use App\Company;
use App\Customer;
use Carbon\Carbon;
use App\Events\ReservationCreated;
use App\Reservation;
use App\UnitCategory;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddCompanyRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Api\Corneer\UnitController;

class MytravelReservationController extends Controller
{
    //creating reservations coming from mytravel
    public function getReservationMyTravel(Request $request, $commingresevation = null)
    {
        $teamId = $request['team_id'];
        $unitCategory = $request['category_id'][0];
        $unit = Unit::where('unit_category_id', $unitCategory)->first();

        $date_in = $request['check_in'];
        $date_in = Carbon::parse($date_in);
        $date_out = $request['check_out'];
        $date_out = Carbon::parse($date_out);

        //for calculating price details (sub-total, EWA, and VAT)
        $price = $request['total_price'];
        $totalDetails = $this->calculateTotalDetails($price, $teamId);

        $customerData = $this->getCustomerData($request);
        $customer = $this->createOrUpdateCustomer($teamId, $customerData);

        $source_en = $request['source_en'];
        $source_ar = $request['source_ar'];
        $referance_id = $request['referance_id'];

        $units = $this->getUnits($unitCategory, $teamId);

        $unitController = new UnitController();
        $notAvailableDates = $unitController->getAvailabilityForBooking($unitCategory, $request['check_in'], $request['check_out'], $teamId);

        if (!in_array(Carbon::parse($request['check_in'])->format('Y-m-d'), $notAvailableDates)) {
            $unit = $this->getAvailableUnit($units, $request['check_in'], $request['check_out']);


            if ($unit) {
                $source = $this->getOrCreateSource($source_en, $source_ar, $teamId);
                $team = Team::with('owner')->find($teamId);

                $reservation = new Reservation();
                $reservation->status = 'awaiting-payment';
                $reservation->team_id = $team->id;
                $reservation->unit_id = $unit->id;
                $reservation->source_id = $source->id;
                $reservation->rent_type = 1;
                $reservation->source_num = $referance_id;
                $reservation->customer_id = $customer->id;
                $reservation->date_in = Carbon::parse($request['check_in'])->format('Y-m-d');
                $reservation->date_out = Carbon::parse($request['check_out'])->format('Y-m-d');
                $reservation->is_online = 1;
                $reservation->action_type = Reservation::ACTION_RESERVATION_AWAITING_CONFIRMATION;
                $reservation->total_price = $totalDetails['total_price'];
                $reservation->sub_total = $totalDetails['subtotal'];
                $reservation->vat_total = $totalDetails['vat_total'];
                $reservation->ewa_total = $totalDetails['ewa_total'];
                $reservation->ttx_total = 0;
                $reservation->purpose_of_visit = '';
                $reservation->change_rate = 0;
                $dayStartTime = $team->day_start() ? $team->day_start() : "13:00";
                $dayEndTime = $team->day_start() ? $team->day_start() : "16:00";
                $combinedDateInTime = date('Y-m-d H:i:s', strtotime($reservation->date_in . " $dayStartTime"));
                $combinedDateOutTime = date('Y-m-d H:i:s', strtotime($reservation->date_out . " $dayEndTime"));

                $reservation->date_in_time = $combinedDateInTime;
                $reservation->date_out_time = $combinedDateOutTime;

                if ($reservation->save()) {
                    $this->finalizeReservation($reservation);
                    event(new ReservationCreated($reservation, false));
                }
                $add_company_request = new AddCompanyRequest();
                $add_company_request->merge([
                    'team_id' => $request->team_id,
                    'name' => "Dyafa Booking Engine",
                    'phone' => "+966558439000",
                    'city' => "Alkhubar",
                    'address' => "Prince Turky Street",
                    'person_incharge_name' => "Dyafa Booking Engine",
                    'person_incharge_phone' => "+966558439000",
                    'email' => "booking@dyafa.com",
                    'tax_number' => "511423123300003",
                    'building_number' => "300",
                    'street_name' => "Prince Turky Street",
                    'reservation_id' => $reservation->id,
                    'reservation_type' => 'single',
                ]);
                $add_company = $this->storeCompany($add_company_request);

                $data = [
                    'reservation_id' => $reservation->id,
                    'sub_total' => $reservation->sub_total,
                    'vat_total' => $reservation->vat_total,
                    'ewa_total' => $reservation->ewa_total,
                    'single' => true,
                ];
                return $data;
            }
        }
    }

    //finalizing reservation logic
    private function finalizeReservation($reservation)
    {
        $unit = $reservation->unit;
        $reservation->prices = $unit->getDatesFromRange(new Carbon($reservation->date_in), new Carbon($reservation->date_out), 1);
        $reservation->old_prices = [
            'prices' => $unit->prices(),
            'min_prices' => $unit->minPrices(),
            'tourism_percentage' => $unit->getTourismTax(),
            'vat_parentage' => $unit->getVat(),
            'ewa_parentage' => $unit->getEwa(),
        ];
        $reservation->change_rate = (($reservation->sub_total / $reservation->prices['sub_total']) - 1) * 100;

        $reservation->forceWithdrawFloat($reservation->total_price, [
            'category' => 'reservation',
            'statement' => 'Reservation Total Price',
        ], true, false);
        $reservation->wallet->refreshBalance();

        $reservation->wallet->save();
        $reservation->save();
    }

    //creating or updating mytravel source in fandaqah
    private function getOrCreateSource($source_en, $source_ar, $teamId)
    {
        $sourceQuery = Source::query()->where('name->en', $source_en)->where('team_id', $teamId)->first();
        if (!$sourceQuery) {
            return Source::create([
                'name' => ['en' => $source_en, 'ar' => $source_ar],
                'team_id' => $teamId,
                'deleteable' => 0
            ]);
        }

        return $sourceQuery;
    }

    //get all the available units from unit_category
    private function getUnits($unitCategory, $teamId)
    {
        if ($unitCategory != null) {
            $units = Unit::where('unit_category_id', $unitCategory)->get();
            return $units;
        } else {
            $unitCategory = UnitCategory::where('team_id', $teamId)->first();
            return UnitCategory::with('units')->find($unitCategory->id)->units;
        }
    }
    private function getAvailableUnit($units, $dateIn, $dateOut)
    {
        $currentDatesHolder = [];
        $start  = Carbon::parse($dateIn);
        $end  = Carbon::parse($dateOut);

        if ($start != $end) {
            $end->subDay();
        }
        $period = CarbonPeriod::create($start, $end);
        foreach ($period as $date) {
            if (!in_array($date->format('Y-m-d'), $currentDatesHolder)) {
                $currentDatesHolder[] = $date->format('Y-m-d');
            }
        }

        foreach ($units as $unit) {


            $has_reservation = $this->determineIfUnitHasActualReservationAccordingToStartDate($unit, $dateIn, $currentDatesHolder);
            if (!$has_reservation) {
                return $unit;
            }
        }

        return null;
    }
    public function determineIfUnitHasActualReservationAccordingToStartDate($unit, $date_start, $currentDatesHolder)
    {
        /**
         * Here i will put my logic to handle the intersection based on the unit selected
         */

        $unitReservations = Reservation::where('unit_id', $unit->id)
            ->whereNUll('checked_out')
            ->whereIn('status', ['confirmed', 'awaiting-payment', 'awaiting-confirmation'])
            ->whereNull('deleted_at')
            ->get();

        $unitDatesHolder = [];
        if (count($unitReservations)) {
            foreach ($unitReservations as $unitReservation) {
                $start  = Carbon::parse($unitReservation->date_in);
                $end  = Carbon::parse($unitReservation->date_out);
                if ($start != $end) {
                    $end->subDay();
                }
                $period = CarbonPeriod::create($start, $end);


                foreach ($period as $date) {
                    if (!in_array($date->format('Y-m-d'), $unitDatesHolder)) {
                        $unitDatesHolder[] = $date->format('Y-m-d');
                    }
                }
            }
        }


        /**
         * Checking the overlapping the right way -_-
         */
        if (array_intersect($currentDatesHolder, $unitDatesHolder)) {
            $has_reservation = true;
        } else {
            $has_reservation = false;
        }

        return $has_reservation;
    }

    //handeling cutomer data
    private function createOrUpdateCustomer($teamId, $customerData)
    {
        $matchThese = ['team_id' => $teamId, 'name' => $customerData['name']];
        return Customer::updateOrCreate(
            $matchThese,
            [
                'team_id' => $teamId,
                'name' => $customerData['name'],
                'email' => $customerData['email'],
                'phone' => $customerData['phone'],
                'address' => $customerData['address']
            ]
        );
    }
    private function getCustomerData($request)
    {
        return [
            'phone' => $request['phone'],
            'name' => $request['first_name'] . ' ' . $request['last_name'],
            'email' => $request['email'],
            'address' => $request['address']
        ];
    }

    //storing mytravel company
    private function storeCompany(AddCompanyRequest $request)
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

    //handeling prices
    private function calculateTotalDetails($room, $teamId)
    {
        $prices_arr = $room;
        $aftertax = 0;

        foreach ($prices_arr as $price) {
            $aftertax += $price;
        }

        $tax = (getVatPercentageForUnit($teamId) === null) ? 0 : getVatPercentageForUnit($teamId);
        $ewa = (getEwaPercentageForUnit($teamId) === null) ? 0 : getEwaPercentageForUnit($teamId);
        $tax = $tax / 100;
        $ewa = $ewa / 100;
        $totalWithoutVat = $aftertax / ($tax + 1);
        $totalWithoutEwa = $totalWithoutVat / ($ewa + 1);
        $totalVat = $aftertax - $totalWithoutVat;
        $totalEwa = $totalWithoutVat - $totalWithoutEwa;
        $subtotal = $aftertax - $totalVat - $totalEwa;
        $totalPrice = $aftertax;

        return [
            'total_price' => $totalPrice,
            'subtotal' => $subtotal,
            'vat_total' => $totalVat,
            'ewa_total' => $totalEwa
        ];
    }


}
