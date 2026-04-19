<?php

namespace App\Console\Commands;

use App\Team;
use Carbon\Carbon;
use App\Reservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ForceAutoRenewReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'force:auto-renew-reservations {team_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $team_id = $this->argument('team_id');
        $now = now('Asia/Riyadh');
        $todayDate = $now->format('Y-m-d');
        $nextDate = $now->addDay()->format('Y-m-d');
        $now = DB::table('settings')->where('key', '=', 'day_end')->where('team_id', '=', $team_id)->value('value');

        $isAutoRenewEnabled = (bool) DB::table('settings')->where('key', '=', 'automatic_renewal_of_reservations')->where('team_id', '=', $team_id)->value('value');


        if($isAutoRenewEnabled){

            $reservations = Reservation::withoutGlobalScope('team_id')
                                        ->with('wallet')
                                        ->whereNotNull('checked_in')
                                        ->whereNull('checked_out')
                                        ->where('date_out', '=', $todayDate)
                                        ->whereStatus(Reservation::STATUS_CONFIRMED)
                                        ->whereTeamId($team_id)
                                        ->get();

            if($reservations->isNotEmpty()){

                foreach ($reservations as $reservation) {

                    // we need to check that there is no other reservation on the same unit with the nextDate
                    $unitHasAnotherReservation = (bool) Reservation::withoutGlobalScope('team_id')
                                                                        ->whereUnitId($reservation->unit_id)
                                                                        ->whereTeamId($team_id)
                                                                        ->where('id', '!=', $reservation->id)
                                                                        ->whereStatus(Reservation::STATUS_CONFIRMED)
                                                                        ->where('date_in', '>=', $reservation->date_in)
                                                                        ->where('date_out', '<=', $nextDate)
                                                                        ->whereNull('checked_in')
                                                                        ->whereNull('checked_out')
                                                                        ->count();
                    // making sure that unit doesn't have another reservation
                    if(!$unitHasAnotherReservation){

                        $unit = $reservation->unit;
                        if($unit){

                            $decimal_places_factor = 1 *  ($reservation->wallet && $reservation->wallet->decimal_places == 3 ?  1000  : 100);

                            if($reservation->rent_type == 1){

                                $isDailySingleDaysEnabled = DB::table('settings')->where('key', '=', 'daily_single_days')->where('team_id', '=', $team_id)->value('value');
                                $sub_total  = 0;
                                $vat        = 0;
                                $ewa        = 0;
                                $ttx        = 0;


                                if($isDailySingleDaysEnabled){

                                    $ewa =  $reservation->ewa_total / $reservation->getNights();
                                    $vat =  $reservation->vat_total / $reservation->getNights();
                                    $sum_default_vats = (float) number_format($ewa + $vat ,2  , '.' , '');

                                    // 3.75
                                    // 23.0625
                                    // 26.8125


                                    $ttx =  $reservation->ttx_total / $reservation->getNights();
                                    $taxes_sum_and_include_ttx  = \bcadd($sum_default_vats , $ttx , 5);
                                    $one_night_price = (float) number_format($reservation->total_price / $reservation->getNights() ,5 , '.' , '');
                                    $one_night_taxes = (float) number_format(($reservation->ewa_total + $reservation->vat_total + $reservation->ttx_total) / $reservation->getNights() ,5 , '.' , '');

                                    // $sub_total   =  \bcsub(($reservation->total_price / $reservation->getNights()) , $taxes_sum_and_include_ttx,5);
                                    $sub_total = (float) number_format($one_night_price - $one_night_taxes ,2 , '.' , '') ;


                                    $days_new = [];
                                    $new_prices_array = [];
                                     foreach($reservation->prices['days'] as $obj){
                                        $std = new \stdClass;
                                        $std->date = $obj['date'];
                                        $std->date_name = $obj['date_name'];
                                        $std->price_row =  (float) number_format($obj['price'],5  , '.' , '');
                                        $std->price =  (float) number_format($obj['price'],5  , '.' , '');
                                        $days_new [] = $std;
                                     }

                                     $newDay = new \stdClass;
                                     $newDay->date = Carbon::parse($todayDate)->format('Y/m/d');
                                     $newDay->date_name = __(Carbon::parse($todayDate)->format('l'));
                                     $newDay->price_row =  (float) number_format($sub_total ,5  , '.' , '');
                                     $newDay->price =  (float) number_format($sub_total ,5  , '.' , '');



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
                                     $new_prices_array['total_price'] = (float) number_format($reservation->total_price + $sub_total + $vat + $ewa + $ttx,2  , '.' , '');
                                     $new_prices_array['total_price_raw'] = (float) number_format($reservation->total_price + $sub_total + $vat + $ewa + $ttx,2  , '.' , '');
                                    //  unset($reservation->prices['days']);


                                    $reservation->prices = $new_prices_array;
                                    // dd($reservation->prices);

                                    // $sub_total   =  ($reservation->total_price / $reservation->getNights()) - $taxes_sum_and_include_ttx;


                                }else{

                                    $day_name               = Carbon::parse($todayDate)->format('l');
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
                                        $std->price_row =  (float) number_format($obj['price'],5  , '.' , '');
                                        $std->price =  (float) number_format($obj['price'],5  , '.' , '');
                                        $days_new [] = $std;
                                     }

                                     $newDay = new \stdClass;
                                     $newDay->date = Carbon::parse($todayDate)->format('Y/m/d');
                                     $newDay->date_name = __(Carbon::parse($todayDate)->format('l'));
                                     $newDay->price_row =  (float) number_format($sub_total ,5  , '.' , '');
                                     $newDay->price =  (float) number_format($sub_total ,5  , '.' , '');

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
                                     $new_prices_array['total_price'] = (float) number_format($reservation->total_price + $sub_total + $vat + $ewa + $ttx,2  , '.' , '');
                                     $new_prices_array['total_price_raw'] = (float) number_format($reservation->total_price + $sub_total + $vat + $ewa + $ttx,2  , '.' , '');
                                    //  unset($reservation->prices['days']);


                                    $reservation->prices = $new_prices_array;

                                    // dd($reservation->prices);



                                }

                                $total_price            =   (float) number_format($sub_total + $vat + $ewa + $ttx,2  , '.' , '');


                                // dd($decimal_places_factor);
                                // dd($total_price);
                                // dd($decimal_places_factor * $total_price);

                                $reservation->date_out = $nextDate;
                                $reservation->date_out_time = date('Y-m-d H:i' , \strtotime("$nextDate $now"));
                                $reservation->sub_total     += $sub_total;
                                $reservation->vat_total     += $vat;
                                $reservation->ewa_total     += $ewa;
                                $reservation->ttx_total     += $ttx;
                                $reservation->total_price   += $total_price;
                                $reservation->action_type   = Reservation::ACTION_UPDATERESERVATIONFROMCOMMAND;
                                $reservation->save();


                                $reservation->forceWithdrawFloat($total_price, [
                                    'category' => 'update_reservation',
                                    'statement' => 'auto renew reservation',
                                ], true, false);

                                $reservation->wallet->refreshBalance();

                            }else{


                                $isMonthlySingleDaysEnabled = DB::table('settings')->where('key', '=', 'monthly_single_days')->where('team_id', '=', $team_id)->value('value');

                                $extra_night = 0;
                                $sub_total  = 0;
                                $vat        = 0;
                                $ewa        = 0;
                                $ttx        = 0;
                                if($isMonthlySingleDaysEnabled){

                                    // day price will be fetched from unit day price
                                    $day_name               = Carbon::parse($todayDate)->format('l');
                                    $sub_total              = $unit->dayPrice($day_name);
                                    // $vat                    = $unit->getVatTotal($sub_total,false);
                                    // $ewa                    = $unit->getEwaTotal($sub_total,false);
                                    // $ttx                    = $unit->getTourismTaxTotal($sub_total,false);


                                    // $vat = (float) number_format($reservation->vat_total / $reservation->getNights() ,2  , '.' , '');
                                    // $ewa =  (float) number_format($reservation->ewa_total / $reservation->getNights() ,2  , '.' , '');
                                    // $ttx = (float) number_format($reservation->ttx_total / $reservation->getNights() ,2  , '.' , '');
                                    // $taxes_sum = $vat + $ewa + $ttx;

                                    // $sub_total              =   ($reservation->total_price / $reservation->getNights()) - $taxes_sum ;
                                    $sub_total              = (float) number_format($reservation->sub_total + $sub_total,2  , '.' , '');



                                    $vat = $unit->getVatTotal($sub_total,false);
                                    $ewa = $unit->getEwaTotal($sub_total,false);
                                    $ttx = $unit->getTourismTaxTotal($sub_total,false);


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
                                    $new_prices_array['total_price'] =  (float) number_format( $sub_total + $ewa + $vat + $ttx   ,2  , '.' , '');
                                    $new_prices_array['total_price_raw'] =  (float) number_format( $sub_total + $ewa + $vat + $ttx   ,2  , '.' , '');
                                    $reservation->prices = $new_prices_array;

                                }else{

                                    $vat = (float) number_format($reservation->vat_total / $reservation->getNights() ,2  , '.' , '');
                                    $ewa =  (float) number_format($reservation->ewa_total / $reservation->getNights() ,2  , '.' , '');
                                    $ttx = (float) number_format($reservation->ttx_total / $reservation->getNights() ,2  , '.' , '');
                                    $taxes_sum = $vat + $ewa + $ttx;

                                    $sub_total              =   ($reservation->total_price / $reservation->getNights()) - $taxes_sum ;
                                    $sub_total              = (float) number_format($reservation->sub_total + $sub_total,2  , '.' , '');


                                    $vat = $unit->getVatTotal($sub_total,false);
                                    $ewa = $unit->getEwaTotal($sub_total,false);
                                    $ttx = $unit->getTourismTaxTotal($sub_total,false);


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


                                $total_price                = (float) number_format($sub_total + $vat + $ewa + $ttx,2  , '.' , '');

                                $reservation->date_out = $nextDate;
                                $reservation->date_out_time = date('Y-m-d H:i' , \strtotime("$nextDate $now"));
                                $reservation->sub_total     = $sub_total;
                                $reservation->vat_total     = $vat;
                                $reservation->ewa_total     = $ewa;
                                $reservation->ttx_total     = $ttx;
                                $reservation->total_price   = $total_price;
                                $reservation->action_type   = Reservation::ACTION_UPDATERESERVATIONFROMCOMMAND;

                                $reservation->save();
                                $reservation->forceWithdrawFloat($extra_night, [
                                    'category' => 'update_reservation',
                                    'statement' => 'auto renew reservation',
                                ], true, false);

                                $reservation->wallet->refreshBalance();

                            }

                        }


                    }


                }
            }


        }


    }
}
