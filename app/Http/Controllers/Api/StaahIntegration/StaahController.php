<?php

namespace App\Http\Controllers\Api\StaahIntegration;

use App\Team;
use App\Source;
use App\Customer;
use Carbon\Carbon;
use App\Reservation;
use App\UnitCategory;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use App\FandaqahStaahReservation;
use App\Events\ReservationCreated;
use App\Events\ReservationUpdated;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Events\ShomosReservationUpdated;

class StaahController extends Controller
{
    public function handleIncomingBooking(Request $request)
    {
        $incoming_booking = $request->all();
        $team_id = $incoming_booking['hotel_id'];

        if ($incoming_booking['status'] == 'cancelled') {
            $cancelReservation = Reservation::where('team_id', $team_id)->where('staah_booking_id', $incoming_booking['id'])->first();
            if ($cancelReservation) {
                $cancelReservation->cancel();
                return response()->json(['success' => true]);
            }
        }

        $unit_category_id = $incoming_booking['rooms'][0]['id']; // taking first result to extract unit category id 
        $arrival_date = $incoming_booking['rooms'][0]['arrival_date'];
        $departure_date = $incoming_booking['rooms'][0]['departure_date'];
        // Create or Update Customer information 
        $customer = $this->createOrUpdateCustomer($team_id, $incoming_booking['customer']);
        $team = Team::with('owner')->find($team_id);
        $units = UnitCategory::find($unit_category_id)->available_to_sync_units;
        if (count($units) && count($incoming_booking['rooms'])) {
            // $notAvailableDates = $this->getAvailabilityForBooking($team_id, $unit_category_id, $arrival_date, $departure_date);
            // return response()->json($notAvailableDates);
            foreach ($units as $key => $unit) {
                if (isset($incoming_booking['rooms'][$key])) {
                    $checkStaahReservation = Reservation::where('team_id', $incoming_booking['hotel_id'])->where('staah_booking_id', $incoming_booking['id'])->first();
                    $checkIntersection = false;
                    if (!$checkStaahReservation) {
                        $checkIntersection = checkIfUnitHasReservation($unit->id, Carbon::parse($incoming_booking['rooms'][$key]['arrival_date']));
                    }

                    if (!$checkIntersection) {
                        // return response()->json($incoming_booking['rooms'][$key]);
                        // means room is available to book
                        /** @todo handle source reservation coming from ( we need to make a mapping as we have sources pre-defined table)  */

                        $channelManagerSource = Source::where('team_id', $incoming_booking['hotel_id'])->where('name->en', 'channel-manager')->first();
                        if (!$channelManagerSource) {
                            $channelManagerSource = new Source;
                            $channelManagerSource->team_id = $incoming_booking['hotel_id'];
                            $channelManagerSource->name = ['ar' => 'channel-manager', 'en' => 'channel-manager'];
                            $channelManagerSource->deleteable = 0;
                            $channelManagerSource->save();
                        }


                        if ($checkStaahReservation) {
                            // its a modification process
                            $checkStaahReservation->date_in = $incoming_booking['rooms'][$key]['arrival_date'];
                            $checkStaahReservation->date_out = $incoming_booking['rooms'][$key]['departure_date'];
                            if ($incoming_booking['status'] == 'cancelled') {
                                $checkStaahReservation->status = 'canceled';
                            }

                            $division = $incoming_booking['totalprice'] - $checkStaahReservation->total_price;

                            $x = $incoming_booking['rooms'][$key]['totalprice'];
                            $e = $team->ewa() ? $team->ewa() / 100 : 0;
                            $v = $team->vat() ? $team->vat() / 100 : 0;
                            $t = $team->ttx() ? $team->ttx() / 100 : 0;
                            $y = $x / (1 + $e + $t + $v + ($v * $e));

                            $total_ewa = $y * $e;
                            $total_vat = ($y + $total_ewa) * $v;
                            $total_ttx = $y * $t;
                            $checkStaahReservation->total_price = $x;
                            $checkStaahReservation->sub_total = $y;
                            $checkStaahReservation->vat_total = $total_vat;
                            $checkStaahReservation->ewa_total = $total_ewa;
                            $checkStaahReservation->ttx_total = $total_ttx;

                            $checkStaahReservation->purpose_of_visit = '';
                            $checkStaahReservation->change_rate = 0;

                            $day_start_time =  $team->day_start() ? $team->day_start() : "13:00";
                            $date_in = $incoming_booking['rooms'][$key]['arrival_date'];
                            $day_end_time =  $team->day_end() ? $team->day_end() : "16:00";
                            $date_out = $incoming_booking['rooms'][$key]['departure_date'];
                            $combinedDateInTime = date('Y-m-d H:i:s', strtotime("$date_in $day_start_time"));
                            $combinedDateOutTime = date('Y-m-d H:i:s', strtotime("$date_out $day_end_time"));
                            $checkStaahReservation->date_in_time = $combinedDateInTime;
                            $checkStaahReservation->date_out_time = $combinedDateOutTime;

                            $checkStaahReservation->prices = $unit->getDatesFromRange(new Carbon($checkStaahReservation->date_in), new Carbon($checkStaahReservation->date_out), 1);

                            if ($checkStaahReservation->old_prices) {
                                $old_prices = $checkStaahReservation->unit->getDatesFromRangeWithOldPrices(Carbon::parse($checkStaahReservation->date_in), Carbon::parse($checkStaahReservation->date_out), $checkStaahReservation->old_prices, 0, $checkStaahReservation->rent_type, $request->rent_type);
                                $old_prices_price = $old_prices['price'] > 0 ? $old_prices['price'] : 1;
                                $checkStaahReservation->change_rate = (($checkStaahReservation->sub_total / $old_prices_price) - 1) * 100;
                                $checkStaahReservation->prices = $checkStaahReservation->unit->getDatesFromRangeWithOldPrices(Carbon::parse($checkStaahReservation->date_in), Carbon::parse($checkStaahReservation->date_out), $checkStaahReservation->old_prices, $checkStaahReservation->change_rate, $checkStaahReservation->rent_type, $request->rent_type);
                            }

                            // return response()->json($checkStaahReservation->prices);
                            $checkStaahReservation->action_type = Reservation::ACTION_UPDATERESERVATION;
                            $checkStaahReservation->save();

                            if (
                                $division < 0
                            ) {
                                $checkStaahReservation->depositFloat(abs(floatval($division)), [
                                    'category' => 'update_reservation',
                                    'statement' => 'update Reservation Total Price deposit',
                                ], true, false);
                            } elseif ($division > 0) {
                                $checkStaahReservation->forceWithdrawFloat(floatval($division), [
                                    'category' => 'update_reservation',
                                    'statement' => 'update Reservation Total Price Withdraw',
                                ], true, false);
                            }

                            // $checkFandaqahStaahReservationLogTable = FandaqahStaahReservation::where('team_id', $incoming_booking['hotel_id'])->where('reservation_id', $checkStaahReservation->id)->where('staah_booking_id', $checkStaahReservation->staah_booking_id)->first();

                            // if ($checkFandaqahStaahReservationLogTable) {
                            //     FandaqahStaahReservation::updateOrCreate(
                            //         [
                            //             'team_id' => $team->id,
                            //             'reservation_id' => $checkStaahReservation->id,
                            //             'staah_booking_id' => $checkStaahReservation->staah_booking_id,
                            //         ],
                            //         [
                            //             'staah_room_reservation_id' => $incoming_booking['rooms'][$key]['roomreservation_id'],
                            //             'staah_booking_status' => $incoming_booking['status'],
                            //             'created_at' => Carbon::now()->toDateTimeString(),
                            //             'updated_at' => Carbon::now()->toDateTimeString(),
                            //             'staah_created_at' => $incoming_booking['booked_at'],
                            //             'staah_modified_at' => $incoming_booking['modified_at'],
                            //             'staah_processed_at' => $incoming_booking['processed_at'],
                            //         ]
                            //     );
                            // }


                            // return response()->json($checkFandaqahStaahReservationLogTable);
                            event(new ShomosReservationUpdated($checkStaahReservation));
                            event(new ReservationUpdated($checkStaahReservation));

                            return response()->json($checkStaahReservation);
                        } else {
                            // its a creation process
                            $reservation = new Reservation();
                            $reservation->staah_booking_id = $incoming_booking['id'];
                            $reservation->team_id = $team_id;
                            $reservation->number = $this->generateReservationNumber($team);

                            $reservation->unit_id = $unit->id;
                            $reservation->source_id = $channelManagerSource->id;
                            $reservation->rent_type = 1;
                            $reservation->customer_id = $customer->id;
                            $reservation->date_in = $incoming_booking['rooms'][$key]['arrival_date'];
                            $reservation->date_out = $incoming_booking['rooms'][$key]['departure_date'];
                            $reservation->is_online = 1;
                            $reservation->status = 'confirmed';
                            $reservation->action_type = Reservation::ACTION_CREATERESERVATION;


                            $x = $incoming_booking['rooms'][$key]['totalprice'];
                            $e = $team->ewa() ? $team->ewa() / 100 : 0;
                            $v = $team->vat() ? $team->vat() / 100 : 0;
                            $t = $team->ttx() ? $team->ttx() / 100 : 0;
                            $y = $x / (1 + $e + $t + $v + ($v * $e));

                            $total_ewa = $y * $e;
                            $total_vat = ($y + $total_ewa) * $v;
                            $total_ttx = $y * $t;
                            $reservation->total_price = $x;
                            $reservation->sub_total = $y;
                            $reservation->vat_total = $total_vat;
                            $reservation->ewa_total = $total_ewa;
                            $reservation->ttx_total = $total_ttx;

                            $reservation->purpose_of_visit = '';
                            $reservation->change_rate = 0;

                            $day_start_time =  $team->day_start() ? $team->day_start() : "13:00";
                            $date_in = $incoming_booking['rooms'][$key]['arrival_date'];
                            $day_end_time =  $team->day_end() ? $team->day_end() : "16:00";
                            $date_out = $incoming_booking['rooms'][$key]['departure_date'];
                            $combinedDateInTime = date('Y-m-d H:i:s', strtotime("$date_in $day_start_time"));
                            $combinedDateOutTime = date('Y-m-d H:i:s', strtotime("$date_out $day_end_time"));

                            $reservation->date_in_time = $combinedDateInTime;
                            $reservation->date_out_time = $combinedDateOutTime;
                            $reservation->prices = $unit->getDatesFromRange(new Carbon($reservation->date_in), new Carbon($reservation->date_out), 1);
                            $prices = $this->getPrices($team, $reservation->total_price, $reservation->sub_total, $reservation->ewa_total, $reservation->vat_total, $reservation->ttx_total, new Carbon($reservation->date_in), new Carbon($reservation->date_out));

                            if ($reservation->save()) {
                                $unit = $reservation->unit;
                                $reservation->prices = $prices;
                                $reservation->old_prices = [
                                    'prices' => $unit->prices(),
                                    'min_prices' => $unit->minPrices(),
                                    'tourism_percentage'    =>  $unit->getTourismTax(),
                                    'vat_parentage'    =>  $unit->getVat(),
                                    'ewa_parentage'    =>  $unit->getEwa(),
                                ];

                                $reservation->forceWithdrawFloat($reservation->total_price, [
                                    'category' => 'reservation',
                                    'statement' => 'Reservation Total Price',
                                    'channel-manager' => true
                                ], true, false);

                                $reservation->save();

                                // FandaqahStaahReservation::create([
                                //     'team_id' => $team->id,
                                //     'reservation_id' => $reservation->id,
                                //     'staah_booking_id' => $incoming_booking['id'],
                                //     'staah_room_reservation_id' => $incoming_booking['rooms'][$key]['roomreservation_id'],
                                //     'staah_booking_status' => $incoming_booking['status'],
                                //     'created_at' => Carbon::now()->toDateTimeString(),
                                //     'updated_at' => Carbon::now()->toDateTimeString(),
                                //     'staah_created_at' => $incoming_booking['booked_at'],
                                //     'staah_modified_at' => $incoming_booking['modified_at'],
                                //     'staah_processed_at' => $incoming_booking['processed_at'],
                                // ]);




                                // if ($reservation->customer && $reservation->customer->email) {
                                //     // Mail::to($reservation->customer->email)->send(new AwaitingConfirmationReservationMail($reservation));
                                // }
                                event(new ReservationCreated($reservation, false));
                                return response()->json(['success' => true], 201);
                            }
                        }
                    } else {
                        // handle over booking , send  email to owner , notify the receptionist with the situation etc
                    }
                }
            }
            return response()->json('reservations went successfully', 201);
        }
    }

    function createOrUpdateCustomer($team_id, $customer_data)
    {
        $phone = $customer_data['telephone'];
        $name  = $customer_data['first_name'] . ' ' . $customer_data['last_name'];
        $email = $customer_data['email'];
        $address = $customer_data['address'] . '-' . $customer_data['city'] . '-' . $customer_data['countrycode'];
        $matchThese = array('team_id' => $team_id, 'phone' => $phone);
        return Customer::updateOrCreate(
            $matchThese,
            ['team_id' => $team_id, 'name' => $name,  'email' => $email,  'phone' => $phone,  'address' => $address]
        );
    }

    function getPrices($team, $total, $sub_total, $ewa, $vat, $ttx, $start, $end)
    {
        if ($start != $end) {
            $end->subDay();
        }
        $period = CarbonPeriod::create($start, $end);
        $dates = [];
        $total_price = 0;
        $min_sub_total = 0;

        foreach ($period as $date) {
            $day_price = $sub_total / count($period);
            $min_day_price = $sub_total / count($period);
            $min_sub_total += $min_day_price  / count($period);
            $dates[] = [
                'date' => $date->format('Y/m/d'),
                'date_name' => __($date->format('l')),
                'price_row' => $day_price,
                'price' =>  $day_price
            ];
        }


        $totals = [
            'currency' => 'SAR',
            'days' => $dates,
            'price' => $sub_total,
            'vat_parentage' => $team->vat(),
            'ewa_parentage' => $team->ewa(),
            'sub_total' => $sub_total,
            'min_sub_total' => $min_sub_total,
            'total_vat' => $vat,
            'total_ewa' => $ewa,
            'total_price' => number_format($total, 2),
            'total_price_raw' => sprintf("%.2f", $total),
            'total_tourism' => $ttx,
            'tourism_percentage' => $team->ttx()
        ];

        return $totals;
    }

    function generateReservationNumber($team)
    {
        $number = $team->counter->reservation_num;
        $counter = $team->counter;
        $counter->last_reservation_number = $counter->reservation_num;
        $counter->last_receipt_number = $counter->receipt_num;
        $counter->save();

        return $number;
    }


    public function getAvailabilityForBooking($team_id, $cat_id, $start, $end)
    {

        $team_id = $team_id;
        $team = Team::find($team_id);
        $units = DB::table('units as u')
            ->select('u.id as uid', 'u.name as uname')
            ->whereNull('u.deleted_at')
            ->where('u.unit_category_id', $cat_id)
            ->where('u.team_id', $team_id)
            ->where('u.status', '=', 1)
            ->get();

        $units_count = count($units);

        $period = CarbonPeriod::create($start, $end);

        $result = [];
        $days_list = [];

        if ($units) {
            foreach ($units as $unit) {
                $days = [];
                $i = 1;
                foreach ($period as $key => $date) {
                    $days_list[$key]['name'] = __($date->format('l'));
                    $days_list[$key]['number'] = $date->format('Y-m-d');
                    $days_list[$key]['is_today'] = $date->isToday();
                    $reservations =  DB::table('reservations as r')
                        ->leftJoin('customer as c', 'r.customer_id', '=', 'c.id')
                        ->leftJoin('highlights as h', 'c.highlight_id', '=', 'h.id')
                        ->select(
                            'r.id as rid',
                            'r.date_in as rdi',
                            'r.date_out as rdo'
                        )
                        ->whereRaw('? between r.date_in and r.date_out', [$date->format('Y-m-d')])
                        ->where('r.date_out', '!=', $date->format('Y-m-d'))
                        ->whereNull('r.checked_out')
                        ->whereNull('r.deleted_at')
                        ->whereIn('r.status', ['confirmed', $team->payment_preprocessor == 'sure-bills' ? 'awaiting-payment' : 'awaiting-confirmation'])
                        ->where('r.team_id', $team_id)
                        ->where('r.unit_id', $unit->uid)
                        ->get();

                    if (count($reservations)) {

                        if ($key == 0) {
                            $days[$key] = $this->formDay($date, $reservations, $unit);
                        } elseif ($date->format('Y-m-d') >= $reservations[0]->rdi) {
                            $days[$key] = $this->formDay($date, $reservations, $unit);
                        }
                    } else {
                        $days[$key] = $this->formDay($date, $reservations, $unit);
                    }
                }

                $result[] = [
                    'id' => $unit->uid,
                    'uname' => json_decode($unit->uname)->ar,
                    'days' => $days,
                ];
            }
        }

        $reservationsCounter = [];
        foreach ($result as $item) {
            $item = (object) $item;
            foreach ($item->days as $obj) {
                $obj = (object) $obj;
                $reservationsCounter[$obj->number][]  = $obj->reservations;
            }
        }

        $notAvailabileDates = [];
        $availability = [];
        foreach ($reservationsCounter as $key => $value) {

            if (array_sum($value) >= $units_count) {
                $notAvailabileDates[] = Carbon::parse($key)->format('Y-m-d');
            }
        }
        return $notAvailabileDates;
    }

    protected function daysPrices($cat_id, $period)
    {

        $unitCategory = UnitCategory::find($cat_id);
        $prices = [];
        foreach ($period as $date) {
            $prices[$date->format('Y-m-d')] = number_format($unitCategory->dayPrice($date->format('l')), 2);
        }

        return $prices;
    }

    protected function formDay($date, $reservations, $unit)
    {
        $day['is_today'] = $date->isToday();
        $day['number'] = $date->format('Y-m-d');
        $day['reservations'] = count($reservations);
        return $day;
    }
}
