<?php

namespace App\Http\Controllers\Api;

use App\Reservation;
use App\Customer;
use Carbon\Carbon;
use App\Team;
use App\Events\ReservationCreated;
use App\UnitCategory;
use App\Source;
use App\Http\Controllers\Api\Corneer\UnitController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\OtaReservation;
use App\Unit;
use DB;
use Carbon\CarbonPeriod;

class AiosselChannelManagerController extends Controller
{
    private $oldReservationPrice = 0;

    public function createFromAiossel(Request $request, $incomingReservation = null )
    {



        $requestData = $request->all();
        // dd($requestData);
        if(isset($requestData['selected_unit'])){
            $requestData = json_decode($requestData['request'], true); // <- true makes it return an array
            $selected_unit = $requestData['selected_unit'];
            $status = $requestData['action'];
        }else{
            $selected_unit = null;
            $status = $requestData['action'];
            $this->saveOtaReservation($requestData , $status);
              \Log::info($requestData);

        }


        $status = $requestData['action'];

        // dd($status);
        $reservation = $this->handleReservationStatus($requestData, $status, $incomingReservation);
        if ($reservation) {
            return response()->json([
                'success' => true,
                'message' => 'Reservation Cancelled Successfully'
            ]);
        }

        $teamId = $requestData['hotelCode'];
        $customerData = $this->getCustomerData($requestData);
        $customer = $this->createOrUpdateCustomer($teamId, $customerData);
        $rooms = $requestData['rooms'];
        $comment = $requestData['specialRequests'];
        // dd($comment);
        $this->saveOtaReservation($requestData , $status );
        foreach ($rooms as $room) {
            $this->handleRoomReservation($room, $teamId, $customer, $requestData, $status, $incomingReservation , $comment , $selected_unit);
        }
        if($status == 'modify'){
            return response()->json([
                'success' => true,
                'message' => '"Reservation Modified Successfully'
            ]);
        }else{

            return response()->json([
                'success' => true,
                'message' => 'Reservation Updated Successfully'
            ]);
        }
    }

    private function saveOtaReservation($requestData , $status)
    {
        $data = $requestData;
     if($status == 'book'){
        $check_reservation = OtaReservation::where('cm_booking_id', $data['cmBookingId'])->first();
        if($check_reservation){

         return response()->json([
            'success' => false,
            'message' => 'Reservation Already Exists'
         ]);
        }else{


        $reservation = OtaReservation::create([
            'team_id' => $data['hotelCode'],
            'action' => $data['action'],
            'hotel_code' => $data['hotelCode'],
            'channel' => $data['channel'],
            'booking_id' => $data['bookingId'],
            'cm_booking_id' => $data['cmBookingId'],
            'booked_on' => $data['bookedOn'],
            'checkin' => $data['checkin'],
            'checkout' => $data['checkout'],
            'segment' => $data['segment'],
            'special_requests' => $data['specialRequests'],
            'pah' => $data['pah'],
            'is_posted' => false, // default to false
            'amount' => $data['amount'],
            'guest' => $data['guest'],
            'rooms' => $data['rooms'],
            'request' => $data,
            'is_open' => true,
        ]);
        }

     }elseif($status == 'modify'){
        $reservation = OtaReservation::where('cm_booking_id', $data['cmBookingId'])->first();
        if($reservation){
            $reservation->update($data);
        }else{
            $reservation = OtaReservation::create([
                'team_id' => $data['hotelCode'],
                'action' => $data['action'],
                'hotel_code' => $data['hotelCode'],
                'channel' => $data['channel'],
                'booking_id' => $data['bookingId'],
                'cm_booking_id' => $data['cmBookingId'],
                'booked_on' => $data['bookedOn'],
                'checkin' => $data['checkin'],
                'checkout' => $data['checkout'],
                'segment' => $data['segment'],
                'special_requests' => $data['specialRequests'],
                'pah' => $data['pah'],
                'is_posted' => false, // default to false
                'amount' => $data['amount'],
                'guest' => $data['guest'],
                'rooms' => $data['rooms'],
                'is_open' => true,
            ]);
        }
     }elseif($status == 'cancel'){
        $reservation = OtaReservation::where('cm_booking_id', $data['bookingId'])->first();
        if($reservation){
            $reservation->update([
                'action' => 'cancel',
            ]);
        }
     }
    }

    private function handleReservationStatus($requestData, $status, $incomingReservation)
    {
        if ($status == 'cancel') {
            //get the all reservations with the cmBookingId and update the status to canceled
            $cmBookingId = $requestData['bookingId'];
            $reservation = Reservation::where('cmBookingId', $cmBookingId)->get();
            // loop through the reservations and update the status to canceled
            foreach ($reservation as $res) {


                $res->status = 'canceled';
                $res->save();
            }
            return $reservation;


        }

        return null;
    }

    private function getCustomerData($requestData)
    {
        return [
            'phone' => $requestData['guest']['phone'],
            'name' => $requestData['guest']['firstName'] . ' ' . $requestData['guest']['lastName'],
            'email' => $requestData['guest']['email'],
            'address' => $requestData['guest']['address']['line1']. ' ' . $requestData['guest']['address']['city']. ' ' . $requestData['guest']['address']['state']. ' ' . $requestData['guest']['address']['country']. ' ' . $requestData['guest']['address']['zipCode']
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

    private function handleRoomReservation($room, $teamId, $customer, $requestData, $status, $incomingReservation , $comment , $selected_unit)
    {
        $unitCategory = $room['roomCode'];
        $units = $this->getUnits($unitCategory, $teamId);

        $totalDetails = $this->calculateTotalDetails($room, $teamId);
if($selected_unit != null){
    $unit = Unit::find($selected_unit);

    if ($unit) {
        $source = $this->getOrCreateSource($requestData['channel'], $teamId);
        $team = Team::with('owner')->find($teamId);
        $reservation = $this->createOrUpdateReservation($unit, $team, $customer, $requestData, $totalDetails, $status, $incomingReservation, $source, $room);

        if ($reservation->save()) {
            $this->finalizeReservation($reservation , $status , $comment);
            event(new ReservationCreated($reservation, false));
        }
    }


}else{


    $unitController = new UnitController();
    $notAvailableDates = $unitController->getAvailabilityForBooking($unitCategory, $requestData['checkin'], $requestData['checkout'], $teamId);
    if (!in_array(Carbon::parse($requestData['checkin'])->format('Y-m-d'), $notAvailableDates)) {
        $unit = $this->getAvailableUnit($units, $requestData['checkin'], $requestData['checkout']);


        if ($unit) {
            $source = $this->getOrCreateSource($requestData['channel'], $teamId);
            $team = Team::with('owner')->find($teamId);
            $reservation = $this->createOrUpdateReservation($unit, $team, $customer, $requestData, $totalDetails, $status, $incomingReservation, $source, $room);

            if ($reservation->save()) {
                $this->finalizeReservation($reservation , $status , $comment);
                event(new ReservationCreated($reservation, false));
            }
        }
    }
}
    }

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

    private function calculateTotalDetails($room, $teamId)
    {
        $prices_arr = $room['prices'];
        $aftertax = 0;

        foreach ($prices_arr as $price) {
            $aftertax += $price['sellRate'];
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
        $cmBookingId= $requestData['cmBookingId'];

        if ($status == 'modify') {
            $reservation = Reservation::where('cmBookingId', $cmBookingId)
                ->where('status', '!=', 'canceled')
                ->first();
            if ($reservation) {
                $unit = $reservation->unit;
                $this->oldReservationPrice = $reservation->total_price;

            } else {
                $reservation = new Reservation();
                $reservation->status = 'awaiting-payment';
            }
        } else {
            $reservation = new Reservation();
            $reservation->status = 'awaiting-payment';
        }
        // dd($reservation);

        $this->updateOtaReservation($requestData , $status , $unit);

        $reservation->team_id = $team->id;
        $reservation->unit_id = $unit->id;
        $reservation->source_id = $source->id;
        $reservation->rent_type = 1;
        $reservation->source_num = $cmBookingId;
        $reservation->customer_id = $customer->id;
        $reservation->date_in = Carbon::parse($requestData['checkin'])->format('Y-m-d');
        $reservation->date_out = Carbon::parse($requestData['checkout'])->format('Y-m-d');
        $reservation->is_online = 1;
        $reservation->action_type = Reservation::ACTION_RESERVATION_AWAITING_CONFIRMATION;
        $reservation->total_price = $totalDetails['total_price'];
        $reservation->sub_total = $totalDetails['subtotal'];
        $reservation->vat_total = $totalDetails['vat_total'];
        $reservation->ewa_total = $totalDetails['ewa_total'];
        $reservation->ttx_total = 0;
        $reservation->purpose_of_visit = '';
        $reservation->change_rate = 0;
        $reservation->cmBookingId = $cmBookingId;
        $dayStartTime = $team->day_start() ? $team->day_start() : "13:00";
        $dayEndTime = $team->day_start() ? $team->day_start() : "16:00";
        $combinedDateInTime = date('Y-m-d H:i:s', strtotime($reservation->date_in . " $dayStartTime"));
        $combinedDateOutTime = date('Y-m-d H:i:s', strtotime($reservation->date_out . " $dayEndTime"));

        $reservation->date_in_time = $combinedDateInTime;
        $reservation->date_out_time = $combinedDateOutTime;

        return $reservation;
    }

    private function updateOtaReservation($requestData , $status , $unit)
    {
        $otaReservation = OtaReservation::where('cm_booking_id', $requestData['cmBookingId'])->first();
        if($otaReservation){
            $otaReservation->update([
                'is_posted' => true,
                'is_open' => true,
                'unit' => $unit->unit_number,
            ]);
        }
    }

    private function finalizeReservation($reservation , $status , $comment_message)
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


        if($status == "modify" || $status == "cancel" ){
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

if($comment_message != null){
        $commentable_type = 'App\Reservation';
        $commentable_id = $reservation->id;


        $model = $commentable_type::findOrFail($commentable_id);

        $commentClass = config('comments.model');
        $comment = new $commentClass;
        $comment->commenter()->associate(7073);
        $comment->commentable()->associate($model);
        $comment->comment = $comment_message;
        $comment->approved = !config('comments.approval_required');
        $comment->save();
}
    }
}
