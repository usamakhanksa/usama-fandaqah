<?php

namespace App\Http\Controllers\Api;

use App\Reservation;
use App\Customer;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

use App\Team;
use App\Events\ReservationCreated;
use App\UnitCategory;
use App\Events\ReservationUpdated;
use App\Source;
use App\Http\Controllers\Api\Corneer\UnitController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

use DB;

class ChannelManagerController extends Controller
{

    // varible of old reservation price
    private $oldReservationPrice = 0;

    public function createFromStaah(Request $request, $incomingReservation = null)
    {
        $requestData = $request->all();
        $status = $requestData['reservations'][0]['status'];

        $teamId = $requestData['reservations'][0]['hotel_id'];
        $customerData = $this->getCustomerData($requestData);
        if ($status == 'new') {
            $customer = $this->createOrUpdateCustomer($teamId, $customerData);
        }else{
            $customer = Customer::where('name', $customerData['name'])->first();
            if(!$customer){
                $customer = $this->createOrUpdateCustomer($teamId, $customerData);
            }
        }
        $rooms = $requestData['reservations'][0]['rooms'];

        foreach ($rooms as $room) {
            $this->handleRoomReservation($room, $teamId, $customer, $requestData, $status, $incomingReservation);
        }

        $response = [
            'reservation_notif' => [
                'reservation_notif_id' => array_column($requestData['reservations'], 'reservation_notif_id')
            ]
        ];

        return response()->json($response);
    }

    private function handleReservationStatus($requestData, $status, &$incomingReservation)
    {
        if ($status == 'cancelled') {
            $channelBookingIds = array_column($requestData['reservations'], 'channel_booking_id');
            DB::table('reservations')
                ->whereIn('channel_booking_id', $channelBookingIds)
                ->update(['status' => 'canceled']);
            return Reservation::whereIn('channel_booking_id', $channelBookingIds)->get();
        }

        return null;
    }

    private function getCustomerData($requestData)
    {
        return [
            'phone' => $requestData['reservations'][0]['customer']['telephone'],
            'name' => $requestData['reservations'][0]['customer']['first_name'] . ' ' . $requestData['reservations'][0]['customer']['last_name'],
            'email' => $requestData['reservations'][0]['customer']['email'],
            'address' => $requestData['reservations'][0]['customer']['address']
        ];
    }

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

    private function handleRoomReservation($room, $teamId, $customer, $requestData, $status, $incomingReservation)
    {
        $unitCategory = $room['id'];
        $units = $this->getUnits($unitCategory, $teamId);
        $totalDetails = $this->calculateTotalDetails($room, $teamId);

        $unitController = new UnitController();
        $notAvailableDates = $unitController->getAvailabilityForBooking($unitCategory, $room['arrival_date'], $room['departure_date'], $teamId);
        if (!in_array(Carbon::parse($room['arrival_date'])->format('Y-m-d'), $notAvailableDates)) {
            $unit = $this->getAvailableUnit($units, $room['arrival_date'] , $room['departure_date']);


            if ($unit) {
                $source = $this->getOrCreateSource($requestData['reservations'][0]['affiliation']['source'], $teamId);
                $team = Team::with('owner')->find($teamId);
                $reservation = $this->createOrUpdateReservation($unit, $team, $customer, $requestData, $totalDetails, $status, $incomingReservation, $source, $room);

                if($reservation != null && $reservation != "Reservation already exists"){

                if ($reservation->save()) {
                    $this->finalizeReservation($reservation , $room);
                    if($room["roomstaystatus"] != "modified" ){

                        event(new ReservationCreated($reservation, false));
                    }
                }
            }else{
                return response()->json(['error' => 'Reservation not found'], 404);
                // log info that reservation already exists with room reservation id
                \Log::info('Reservation already exists with room reservation id: ' . $room['roomreservation_id'] . 'or unit not available');
            }
            }else{
                \Log::info('Unit not available with room reservation id: ' . $room['roomreservation_id']);

            }
        }
    }

    private function getUnits($unitCategory, $teamId)
    {
        if ($unitCategory != null) {
            return UnitCategory::with('units')->find($unitCategory)->units;
        } else {
            $unitCategory = UnitCategory::where('team_id', $teamId)->first();
            return UnitCategory::with('units')->find($unitCategory->id)->units;
        }
    }

    private function calculateTotalDetails($room, $teamId)
    {
        $aftertax = $room['totalprice'] + $room['totaltax'];
        $tax = (getVatPercentageForUnit($teamId) === null || getVatPercentageForUnit($teamId) === "") ? 0 : getVatPercentageForUnit($teamId);
        $ewa = (getEwaPercentageForUnit($teamId) === null || getEwaPercentageForUnit($teamId) === "") ? 0 : getEwaPercentageForUnit($teamId);
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
    public function determineIfUnitHasActualReservationAccordingToStartDate($unit,$date_start,$currentDatesHolder)
    {
              /**
             * Here i will put my logic to handle the intersection based on the unit selected
             */

                $unitReservations = Reservation::where('unit_id' , $unit->id)
                                ->whereNUll('checked_out')
                                ->whereIn('status' , ['confirmed','awaiting-payment' , 'awaiting-confirmation'])
                                ->whereNull('deleted_at')
                                ->get();

                $unitDatesHolder = [];
                if(count($unitReservations)){
                    foreach($unitReservations as $unitReservation){
                        $start  = Carbon::parse($unitReservation->date_in);
                        $end  = Carbon::parse($unitReservation->date_out);
                        if ($start != $end) {
                            $end->subDay();
                        }
                        $period = CarbonPeriod::create($start, $end);


                        foreach ($period as $date) {
                            if(!in_array($date->format('Y-m-d'),$unitDatesHolder)){
                                $unitDatesHolder [] = $date->format('Y-m-d');
                            }
                        }
                    }
                }


                /**
                 * Checking the overlapping the right way -_-
                 */
                if(array_intersect($currentDatesHolder,$unitDatesHolder)){
                    $has_reservation = true;
                }else{
                    $has_reservation = false;
                }

                return $has_reservation;
    }

    private function getAvailableUnit($units, $dateIn , $dateOut)
    {
        $currentDatesHolder = [];
        $start  = Carbon::parse($dateIn);

        $end  = Carbon::parse($dateOut);

        if ($start != $end) {
            $end->subDay();
        }
        $period = CarbonPeriod::create($start, $end);
        foreach ($period as $date) {
            if(!in_array($date->format('Y-m-d'),$currentDatesHolder)){
                $currentDatesHolder [] = $date->format('Y-m-d');
            }
        }

        foreach ($units as $unit) {


            $has_reservation = $this->determineIfUnitHasActualReservationAccordingToStartDate($unit,$dateIn,$currentDatesHolder);
            if(!$has_reservation){
            return $unit;
            }
        }

        return null ;

    }

    private function getOrCreateSource($source, $teamId)
    {
        $sourceQuery = Source::query()->where('name->en', $source)->where('team_id', $teamId)->first();
        if (!$sourceQuery) {
            return Source::create([
                'name' => ['en' => $source, 'ar' => $source],
                'team_id' => $teamId,
                'deleteable' => 0
            ]);
        }

        return $sourceQuery;
    }

    private function createOrUpdateReservation($unit, $team, $customer, $requestData, $totalDetails, $status, $incomingReservation, $source, $room)
    {
        $roomReservationId = $room['roomreservation_id'];

        if($room["roomstaystatus"] == "modified" ) {
            $reservation = Reservation::where('roomreservation_id', $roomReservationId)
                ->where('status', '!=', 'canceled')
                ->first();
            if ($reservation) {

                // $unit = $reservation->unit;9

                $this->oldReservationPrice = $reservation->total_price;
            } else {
                $reservation = new Reservation();
                $reservation->status = 'awaiting-payment';
            }

        }elseif($room["roomstaystatus"] == "new" ) {
            $reservation = Reservation::where('roomreservation_id', $roomReservationId)
            ->where('status', '!=', 'canceled')
            ->first();
            if($reservation != null){

                return "Reservation already exists";


            }

            $reservation = new Reservation();
            $reservation->status = 'awaiting-payment';
        }elseif($room["roomstaystatus"] == "cancelled" ) {
            $reservation = Reservation::where('roomreservation_id', $roomReservationId)
                ->first();
                if($reservation != null){

                    if($reservation->status != 'confirmed'){

                        $reservation->status = 'canceled';
                        $reservation->save();

                    }else{
                        //log that resrvation is already confirmed so it can't be cancelled
                 \Log::info('Reservation already confirmed so it can\'t be cancelled with room reservation id: ' . $roomReservationId);

                    }
                }else{
                    \Log::info('Reservation not found with room reservation id: ' . $roomReservationId);

                }
                return $reservation;
        }


        $reservation->team_id = $team->id;
        $reservation->unit_id = $unit->id;
        $reservation->reservation_notif_id = $requestData['reservations'][0]['reservation_notif_id'];
        $reservation->channel_booking_id = $requestData['reservations'][0]['channel_booking_id'];
        $reservation->source_num = $requestData['reservations'][0]['channel_booking_id'];
        $reservation->source_id = $source->id;
        $reservation->rent_type = 1;
        $reservation->customer_id = $customer->id;
        $reservation->date_in = Carbon::parse($room['arrival_date'])->format('Y-m-d');
        $reservation->date_out = Carbon::parse($room['departure_date'])->format('Y-m-d');
        $reservation->is_online = 1;
        $reservation->action_type = Reservation::ACTION_RESERVATION_AWAITING_CONFIRMATION;
        $reservation->total_price = $totalDetails['total_price'];
        $reservation->sub_total = $totalDetails['subtotal'];
        $reservation->vat_total = $totalDetails['vat_total'];
        $reservation->ewa_total = $totalDetails['ewa_total'];
        $reservation->ttx_total = 0;
        $reservation->purpose_of_visit = '';
        $reservation->change_rate = 0;
        $reservation->roomreservation_id = $roomReservationId;
        $dayStartTime = $team->day_start() ? $team->day_start() : "13:00";
        $dayEndTime = $team->day_start() ? $team->day_start() : "16:00";
        $combinedDateInTime = date('Y-m-d H:i:s', strtotime($reservation->date_in . " $dayStartTime"));
        $combinedDateOutTime = date('Y-m-d H:i:s', strtotime($reservation->date_out . " $dayEndTime"));

        $reservation->date_in_time = $combinedDateInTime;
        $reservation->date_out_time = $combinedDateOutTime;

        return $reservation;
    }

    private function finalizeReservation($reservation , $room)
    {




        $unit = $reservation->unit;
        $prices = $unit->getDatesFromRange(new Carbon($reservation->date_in), new Carbon($reservation->date_out), 1);
        $reservation->prices = $prices;
        $reservation->old_prices = [
            'prices' => $unit->prices(),
            'min_prices' => $unit->minPrices(),
            'tourism_percentage' => $unit->getTourismTax(),
            'vat_parentage' => $unit->getVat(),
            'ewa_parentage' => $unit->getEwa(),
        ];
        //change rate = ((newrate / oldrate) - 1)*100
        $reservation->change_rate = (($reservation->sub_total / $reservation->prices['sub_total']) - 1) * 100;

        // Convert change rate to a decimal adjustment factor
        $adjustment_factor = 1 + ($reservation->change_rate / 100);

        // dd($prices ,  $adjustment_factor);
        $prices['price'] = is_numeric($prices['price'] ?? null) ? $prices['price'] * $adjustment_factor : (float) $prices['price'] * $adjustment_factor;
        $prices['sub_total'] = is_numeric($prices['sub_total'] ?? null) ? $prices['sub_total'] * $adjustment_factor : (float) $prices['sub_total'] * $adjustment_factor;
        $prices['total_vat'] = is_numeric($prices['total_vat'] ?? null) ? $prices['total_vat'] * $adjustment_factor : (float) $prices['total_vat'] * $adjustment_factor;
        $prices['total_ewa'] = is_numeric($prices['total_ewa'] ?? null) ? $prices['total_ewa'] * $adjustment_factor : (float) $prices['total_ewa'] * $adjustment_factor;
        $prices['total_price'] = is_numeric($prices['total_price'] ?? null) ? $prices['total_price'] * $adjustment_factor : (float) $prices['total_price'] * $adjustment_factor;
        $prices['total_price_raw'] = is_numeric($prices['total_price_raw'] ?? null) ? $prices['total_price_raw'] * $adjustment_factor : (float) $prices['total_price_raw'] * $adjustment_factor;



        // Apply adjustment to each day's price as well
        foreach ($prices['days'] as &$day) {
            $day['price'] *= $adjustment_factor;
            $day['price_row'] *= $adjustment_factor;
        }

        // Assign modified prices back to $reservation->prices
        $reservation->prices = $prices;



        if($room["roomstaystatus"] == "modified" || $room["roomstaystatus"] == "cancelled" ){
            $reservation_id = $reservation->id;



            $division = $reservation->total_price - $this->oldReservationPrice;

            if($division < 0){
                $reservation->depositFloat(abs($division), [
                    'category' => 'update_reservation',
                    'statement' => 'update Reservation Total Price deposit',
                ], true, false);
            }elseif($division > 0){
                $reservation->forceWithdrawFloat($division, [
                    'category' => 'update_reservation',
                    'statement' => 'update Reservation Total Price Withdraw',
                ], true, false);
            }

            $reservation->wallet->refreshBalance();



        }else{
            $reservation->forceWithdrawFloat($reservation->total_price, [
                'category' => 'reservation',
                'statement' => 'Reservation Total Price',
            ], true, false);
            $reservation->wallet->refreshBalance();
        }

        $reservation->wallet->save();
        $reservation->save();
        if($room["roomstaystatus"] == "modified" ){
            event(new ReservationUpdated($reservation));
        }
    }
}
