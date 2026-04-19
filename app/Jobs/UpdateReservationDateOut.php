<?php

namespace App\Jobs;

use App\Helpers\CheckDatesLang;
use Carbon\Carbon;
use App\Reservation;
use App\Transaction;
use Carbon\CarbonPeriod;
use Faker\Provider\Uuid;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Mail\CreateAutoRenewHiddenTransactionFailed;

class UpdateReservationDateOut implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public $team_id;
    public $todayDate;
    public $nextDate;
    public $now;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($team_id,$todayDate,$nextDate,$now)
    {
        $this->team_id = $team_id;
        $this->todayDate = $todayDate;
        $this->nextDate = $nextDate;
        $this->now = $now;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

                    $isAutoRenewEnabled = (bool) DB::table('settings')->where('key', '=', 'automatic_renewal_of_reservations')->where('team_id', '=', $this->team_id)->value('value');
                    $isPriceByDayEnabled = (bool) DB::table('settings')->where('key', '=', 'calculate_price_by_day')->where('team_id', '=', $this->team_id)->value('value');
                    if($isAutoRenewEnabled){

                        $reservations = Reservation::withoutGlobalScope('team_id')
                                                    ->with('wallet')
                                                    ->whereNotNull('checked_in')
                                                    ->whereNull('checked_out')
                                                    ->where('date_out', '=', $this->todayDate)
                                                    ->whereStatus(Reservation::STATUS_CONFIRMED)
                                                    ->whereTeamId($this->team_id)
                                                    ->get();

                        if($reservations->isNotEmpty()){

                            foreach ($reservations as $reservation) {

                                // we need to check that there is no other reservation on the same unit with the nextDate
                                // $unitHasAnotherReservation = (bool) Reservation::withoutGlobalScope('team_id')
                                //                                                     ->whereUnitId($reservation->unit_id)
                                //                                                     ->whereTeamId($this->team_id)
                                //                                                     ->where('id', '!=', $reservation->id)
                                //                                                     ->whereStatus(Reservation::STATUS_CONFIRMED)
                                //                                                     ->where('date_in', '>=', $reservation->date_in)
                                //                                                     ->where('date_out', '<=', $this->nextDate)
                                //                                                     ->whereNull('checked_in')
                                //                                                     ->whereNull('checked_out')
                                //                                                     ->count();


                                $currentPeriodAccordingToSelectedDates = CarbonPeriod::create(Carbon::parse($reservation->date_in), Carbon::parse($this->nextDate)->subDay());
                                $currentDatesHolder = [];
                                foreach ($currentPeriodAccordingToSelectedDates as $date) {
                                    if(!in_array($date->format('Y-m-d'),$currentDatesHolder)){
                                        $currentDatesHolder [] = $date->format('Y-m-d');
                                    }
                                }


                                $unitReservations = Reservation::where('unit_id' , $reservation->unit_id)
                                ->whereNUll('checked_out')
                                ->whereIn('status' , ['confirmed','awaiting-payment' , 'awaiting-confirmation'])
                                ->whereNull('deleted_at')
                                ->where('id' , '!=' , $reservation->id)
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


                                if(array_intersect($currentDatesHolder,$unitDatesHolder)){
                                    $reservation_can_not_be_renewed = true;
                                }else{
                                    $reservation_can_not_be_renewed = false;
                                }

                                // making sure that unit doesn't have another reservation
                                if(!$reservation_can_not_be_renewed){

                                    $unit = $reservation->unit;
                                    if($unit){

                                        if($reservation->rent_type == 1){

                                            $isDailySingleDaysEnabled = DB::table('settings')->where('key', '=', 'daily_single_days')->where('team_id', '=', $this->team_id)->value('value');
                                            $sub_total  = 0;
                                            $vat        = 0;
                                            $ewa        = 0;
                                            $ttx        = 0;


                                            if($isDailySingleDaysEnabled){

                                                $ewa =  $reservation->ewa_total / $reservation->getNights();
                                                $vat =  $reservation->vat_total / $reservation->getNights();
                                                #  3.1813361611876996 - 19.565217391304
                                                # "3.1813361611877 - 19.565217391304"
                                                // dd($ewa . ' - ' . $vat);
                                                $ttx =  $reservation->ttx_total / $reservation->getNights();

                                                $one_night_price = (float) ($reservation->total_price / $reservation->getNights());
                                                $one_night_taxes = (float)  ( ($reservation->ewa_total + $reservation->vat_total + $reservation->ttx_total) / $reservation->getNights() );


                                                $sub_total = (float) ($one_night_price - $one_night_taxes);
                                                // dd( 'new night taxes : ' . $one_night_taxes . ' - '  . ' original taxes : ' . ($reservation->ewa_total + $reservation->vat_total) . ' new night subtotal : ' . $sub_total  . ' - ' . ' original subtotal : ' . $reservation->sub_total);

                                                # "new night taxes : 22.746553552492 -  original taxes : 22.746553552492 new night subtotal : 127.25344644751 -  original subtotal : 127.25344644751"

                                                # 22.746553552
                                                # 22.746553552492


                                                $days_new = [];
                                                $new_prices_array = [];
                                                foreach($reservation->prices['days'] as $obj){
                                                    $std = new \stdClass;
                                                    $std->date = $obj['date'];
                                                    $std->date_name = $obj['date_name'];
                                                    $std->price_row =  (float) $obj['price'];
                                                    $std->price =  (float) $obj['price'];
                                                    $days_new [] = $std;
                                                 }

                                                 $newDay = new \stdClass;
                                                 $newDay->date = Carbon::parse($this->todayDate)->format('Y/m/d');
                                                 $newDay->date_name = __(Carbon::parse($this->todayDate)->format('l'));
                                                 $newDay->price_row =  (float) $sub_total;
                                                 $newDay->price =  (float) $sub_total;

                                                 if($isPriceByDayEnabled) {
                                                    $sub_total = 0;
                                                    $day_name_en = CheckDatesLang::getEnglishDay($newDay->date_name) . '_day_price';
                                                    $base_amount = floatval($reservation->old_prices['prices']['day'][$day_name_en]);
                                                    $newDay->price_row = $base_amount;
                                                    $newDay->price = $base_amount;
                                                    $sub_total += $newDay->price;
                                                 }

                                                 //calculate vat and ewa for sub total
                                                 if($isPriceByDayEnabled) {
                                                    $ewa = $sub_total / 100 * floatval($reservation->prices['ewa_parentage']);
                                                    $sub_total_with_ewa = $sub_total + $ewa;
                                                    $vat = $sub_total_with_ewa / 100 * floatval($reservation->prices['vat_parentage']);
                                                 }

                                                 $days_new [] = $newDay;

                                                 $new_prices_array['currency'] = $reservation->prices['currency'];
                                                 $new_prices_array['days'] = $days_new;
                                                 $new_prices_array['vat_parentage'] = $reservation->prices['vat_parentage'];
                                                 $new_prices_array['ewa_parentage'] = $reservation->prices['ewa_parentage'];
                                                 $new_prices_array['tourism_percentage'] = $reservation->prices['tourism_percentage'];
                                                 $new_prices_array['min_sub_total'] = $reservation->prices['min_sub_total']; // 0
                                                 $new_prices_array['total_vat'] =  $reservation->vat_total + $vat;
                                                 $new_prices_array['total_ewa'] = $reservation->ewa_total + $ewa;
                                                 $new_prices_array['total_tourism'] = $reservation->ttx_total + $ttx;
                                                 $new_prices_array['price'] = $reservation->sub_total + $sub_total;
                                                 $new_prices_array['sub_total'] = $reservation->sub_total + $sub_total;
                                                 $new_prices_array['total_price'] = (float) ($reservation->total_price + $sub_total + $vat + $ewa + $ttx);
                                                 $new_prices_array['total_price_raw'] = (float) ($reservation->total_price + $sub_total + $vat + $ewa + $ttx);


                                                $reservation->prices = $new_prices_array;



                                            }else{

                                                $day_name               = Carbon::parse($this->todayDate)->format('l');
                                                $sub_total              = $unit->dayPrice($day_name);
                                                $vat                    = $unit->getVatTotal($sub_total,false);
                                                $ewa                    = $unit->getEwaTotal($sub_total,false);
                                                $ttx                    = $unit->getTourismTaxTotal($sub_total,false);

                                                $days_new = [];
                                                $new_prices_array = [];
                                                 foreach($reservation->prices['days'] as $obj){
                                                    $std = new \stdClass;
                                                    $std->date = $obj['date'];
                                                    $std->date_name = $obj['date_name'];
                                                    $std->price_row =  (float) $obj['price'];
                                                    $std->price =  (float) $obj['price'];
                                                    $days_new [] = $std;
                                                 }

                                                 $newDay = new \stdClass;
                                                 $newDay->date = Carbon::parse($this->todayDate)->format('Y/m/d');
                                                 $newDay->date_name = __(Carbon::parse($this->todayDate)->format('l'));
                                                 $newDay->price_row =  (float) $sub_total;
                                                 $newDay->price =  (float) $sub_total;
                                                 if($isPriceByDayEnabled) {
                                                    $sub_total = 0;
                                                    $day_name_en = CheckDatesLang::getEnglishDay($newDay->date_name) . '_day_price';
                                                    $base_amount = floatval($reservation->old_prices['prices']['day'][$day_name_en]);
                                                    $newDay->price_row = $base_amount;
                                                    $newDay->price = $base_amount;
                                                    $sub_total += $newDay->price;
                                                 }

                                                 //calculate vat and ewa for sub total
                                                 if($isPriceByDayEnabled) {
                                                    $ewa = $sub_total / 100 * floatval($reservation->prices['ewa_parentage']);
                                                    $sub_total_with_ewa = $sub_total + $ewa;
                                                    $vat = $sub_total_with_ewa / 100 * floatval($reservation->prices['vat_parentage']);
                                                 }

                                                 $days_new [] = $newDay;

                                                 $new_prices_array['currency'] = $reservation->prices['currency'];
                                                 $new_prices_array['days'] = $days_new;
                                                 $new_prices_array['vat_parentage'] = $reservation->prices['vat_parentage'];
                                                 $new_prices_array['ewa_parentage'] = $reservation->prices['ewa_parentage'];
                                                 $new_prices_array['tourism_percentage'] = $reservation->prices['tourism_percentage'];
                                                 $new_prices_array['min_sub_total'] = $reservation->prices['min_sub_total'];
                                                 $new_prices_array['total_vat'] =  $reservation->vat_total + $vat;
                                                 $new_prices_array['total_ewa'] = $reservation->ewa_total + $ewa;
                                                 $new_prices_array['total_tourism'] = $reservation->ttx_total + $ttx;
                                                 $new_prices_array['price'] = $reservation->sub_total + $sub_total;
                                                 $new_prices_array['sub_total'] = $reservation->sub_total + $sub_total;
                                                 $new_prices_array['total_price'] = (float) ($reservation->total_price + $sub_total + $vat + $ewa + $ttx);
                                                 $new_prices_array['total_price_raw'] = (float) ($reservation->total_price + $sub_total + $vat + $ewa + $ttx);
                                                //  unset($reservation->prices['days']);


                                                $reservation->prices = $new_prices_array;

                                                // dd($reservation->prices);



                                            }

                                            $total_price            =   (float) ($sub_total + $vat + $ewa + $ttx);

                                            // dd($total_price);
                                            $reservation->date_out = $this->nextDate;
                                            $reservation->date_out_time = date('Y-m-d H:i' , \strtotime("$this->nextDate $this->now"));
                                            $reservation->sub_total     += $sub_total;
                                            $reservation->vat_total     += $vat;
                                            $reservation->ewa_total     += $ewa;
                                            $reservation->ttx_total     += $ttx;
                                            $reservation->total_price   += $total_price;
                                            $reservation->action_type   = Reservation::ACTION_UPDATERESERVATIONFROMCOMMAND;
                                            $reservation->save();

                                            try {


                                                $meta = [
                                                    'category' => 'update_reservation',
                                                    'statement' => 'auto renew reservation',
                                                ];

                                                $transaction = new Transaction();
                                                $transaction->meta = $meta;
                                                $transaction->payable_id = $reservation->id;
                                                $transaction->payable_type = Reservation::class;
                                                $transaction->wallet_id = $reservation->wallet->id;
                                                $transaction->type = 'withdraw';
                                                $transaction->amount = -1 * $total_price * 100;
                                                $transaction->confirmed = 1;
                                                $transaction->is_public = 0;
                                                $transaction->uuid = (string) Str::uuid();
                                                $transaction->save();
                                                // $reservation->forceWithdrawFloat($total_price, [
                                                //     'category' => 'update_reservation',
                                                //     'statement' => 'auto renew reservation',
                                                // ], true, false);
                                            } catch (\Throwable $th) {
                                                // Send user notification of failure, etc...
                                                // Mail::to(['ealabd@sure.com.sa','irashad@sure.com.sa','ahmed@dyafa.com'])
                                                // ->send(new CreateAutoRenewHiddenTransactionFailed($reservation, $th->getMessage()));
                                            }


                                            $reservation->wallet->refreshBalance();

                                        }else{

                                            $ewa_from_reservation = isset($reservation->old_prices['ewa_parentage']) && $reservation->old_prices['ewa_parentage'] ? $reservation->old_prices['ewa_parentage'] : 0;
                                            $vat_from_reservation = isset($reservation->old_prices['vat_parentage']) && $reservation->old_prices['vat_parentage'] ? $reservation->old_prices['vat_parentage'] : 0;

                                            $isMonthlySingleDaysEnabled = DB::table('settings')->where('key', '=', 'monthly_single_days')->where('team_id', '=', $this->team_id)->value('value');

                                            $extra_night = 0;
                                            $sub_total  = 0;
                                            $vat        = 0;
                                            $ewa        = 0;
                                            $ttx        = 0;
                                            if($isMonthlySingleDaysEnabled){

                                                // day price will be fetched from unit day price
                                                $day_name               = Carbon::parse($this->todayDate)->format('l');
                                                $sub_total              = $unit->dayPrice($day_name);
                                                // $vat                    = $unit->getVatTotal($sub_total,false);
                                                // $ewa                    = $unit->getEwaTotal($sub_total,false);
                                                // $ttx                    = $unit->getTourismTaxTotal($sub_total,false);


                                                // $vat = (float) number_format($reservation->vat_total / $reservation->getNights() ,2  , '.' , '');
                                                // $ewa =  (float) number_format($reservation->ewa_total / $reservation->getNights() ,2  , '.' , '');
                                                // $ttx = (float) number_format($reservation->ttx_total / $reservation->getNights() ,2  , '.' , '');
                                                // $taxes_sum = $vat + $ewa + $ttx;

                                                // $sub_total              =   ($reservation->total_price / $reservation->getNights()) - $taxes_sum ;
                                                $sub_total              = (float) ($reservation->sub_total + $sub_total);



                                                // $vat = $unit->getVatTotal($sub_total,false);
                                                // $ewa = $unit->getEwaTotal($sub_total,false);
                                                $ttx = $unit->getTourismTaxTotal($sub_total,false);
                                                $vat = $unit->getVatTotalAutoRenewBasedOnOldPrices($sub_total,false,$vat_from_reservation,$ewa_from_reservation);
                                                $ewa = $unit->getEwaTotalAutoRenewBasedOnOldPrices($sub_total,false,$ewa_from_reservation);


                                                $extra_night = ($sub_total + $vat+$ewa+$ttx) - ($reservation->total_price);



                                                $new_prices_array = [];
                                                $new_prices_array['currency'] = $reservation->prices['currency'];
                                                $new_prices_array['days'] = [];
                                                $new_prices_array['vat_parentage'] = $reservation->prices['vat_parentage'];
                                                $new_prices_array['ewa_parentage'] = $reservation->prices['ewa_parentage'];
                                                $new_prices_array['tourism_percentage'] = $reservation->prices['tourism_percentage'];
                                                $new_prices_array['min_sub_total'] = $reservation->prices['min_sub_total'];
                                                $new_prices_array['total_vat'] =   $vat;
                                                $new_prices_array['total_ewa'] =   $ewa;
                                                $new_prices_array['total_tourism'] =  $ttx;
                                                $new_prices_array['price'] =  $sub_total;
                                                $new_prices_array['sub_total'] = $sub_total;
                                                $new_prices_array['total_price'] =  (float) ( $sub_total + $ewa + $vat + $ttx );
                                                $new_prices_array['total_price_raw'] =  (float) ( $sub_total + $ewa + $vat + $ttx );
                                                $reservation->prices = $new_prices_array;

                                            }else{

                                                $vat = (float) ($reservation->vat_total / $reservation->getNights());
                                                $ewa =  (float) ($reservation->ewa_total / $reservation->getNights() );
                                                $ttx = (float) ($reservation->ttx_total / $reservation->getNights());
                                                $taxes_sum = $vat + $ewa + $ttx;

                                                $sub_total              =   ($reservation->total_price / $reservation->getNights()) - $taxes_sum ;
                                                $sub_total              = (float) ($reservation->sub_total + $sub_total);


                                                // $vat = $unit->getVatTotal($sub_total,false);
                                                // $ewa = $unit->getEwaTotal($sub_total,false);
                                                $ttx = $unit->getTourismTaxTotal($sub_total,false);
                                                $vat = $unit->getVatTotalAutoRenewBasedOnOldPrices($sub_total,false,$vat_from_reservation,$ewa_from_reservation);
                                                $ewa = $unit->getEwaTotalAutoRenewBasedOnOldPrices($sub_total,false,$ewa_from_reservation);


                                                $extra_night = ($sub_total + $vat+$ewa+$ttx) - ($reservation->total_price);

                                                $new_prices_array = [];
                                                $new_prices_array['currency'] = $reservation->prices['currency'];
                                                $new_prices_array['days'] = [];
                                                $new_prices_array['vat_parentage'] = $reservation->prices['vat_parentage'];
                                                $new_prices_array['ewa_parentage'] = $reservation->prices['ewa_parentage'];
                                                $new_prices_array['tourism_percentage'] = $reservation->prices['tourism_percentage'];
                                                $new_prices_array['min_sub_total'] = $reservation->prices['min_sub_total'];
                                                $new_prices_array['total_vat'] =   $vat;
                                                $new_prices_array['total_ewa'] =   $ewa;
                                                $new_prices_array['total_tourism'] =  $ttx;
                                                $new_prices_array['price'] =  $sub_total;
                                                $new_prices_array['sub_total'] = $sub_total;
                                                $new_prices_array['total_price'] =   $sub_total + $ewa + $vat + $ttx  ;
                                                $new_prices_array['total_price_raw'] =  $sub_total + $ewa + $vat + $ttx  ;
                                                $reservation->prices = $new_prices_array;

                                            }


                                            $total_price                = (float) ($sub_total + $vat + $ewa + $ttx);

                                            $reservation->date_out = $this->nextDate;
                                            $reservation->date_out_time = date('Y-m-d H:i' , \strtotime("$this->nextDate $this->now"));
                                            $reservation->sub_total     = $sub_total;
                                            $reservation->vat_total     = $vat;
                                            $reservation->ewa_total     = $ewa;
                                            $reservation->ttx_total     = $ttx;
                                            $reservation->total_price   = $total_price;
                                            $reservation->action_type   = Reservation::ACTION_UPDATERESERVATIONFROMCOMMAND;

                                            $reservation->save();

                                            try {

                                                $meta = [
                                                    'category' => 'update_reservation',
                                                    'statement' => 'auto renew reservation',
                                                ];

                                                $transaction = new Transaction();
                                                $transaction->meta = $meta;
                                                $transaction->payable_id = $reservation->id;
                                                $transaction->payable_type = Reservation::class;
                                                $transaction->wallet_id = $reservation->wallet->id;
                                                $transaction->type = 'withdraw';
                                                $transaction->amount = -1 * $extra_night * 100;
                                                $transaction->confirmed = 1;
                                                $transaction->is_public = 0;
                                                $transaction->uuid = (string) Str::uuid();
                                                $transaction->save();

                                                // $reservation->forceWithdrawFloat($extra_night, [
                                                //     'category' => 'update_reservation',
                                                //     'statement' => 'auto renew reservation',
                                                // ], true, false);
                                            } catch (\Throwable $th) {
                                                // Send user notification of failure, etc...
                                                // Mail::to(['ealabd@sure.com.sa','irashad@sure.com.sa','ahmed@dyafa.com'])
                                                // ->send(new CreateAutoRenewHiddenTransactionFailed($reservation, $th->getMessage()));
                                            }
                                            $reservation->wallet->refreshBalance();

                                        }

                                    }


                                }else{

                                    $properties = [
                                        'extra_day_to_add_to_reservation' => Carbon::parse($this->nextDate)->subDay()->format('Y-m-d'),
                                        'current_reservation_period' => $currentDatesHolder,
                                        'other_reservations_period' => $unitDatesHolder,
                                    ];

                                    activity('auto_renew_failed')
                                    ->causedBy(null)
                                    ->performedOn($reservation)
                                    ->withProperties($properties)
                                    ->log('لا يمكن التجديد التلقائي لوجود حجز اخر متقاطع مع هذا الحجز');
                                }


                            }
                        }


                    }

    }
}
