<?php

namespace App\Console\Commands;

use App\Events\ReservationUpdated;
use App\Reservation;
use App\Unit;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class UpdateReservationDateOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:reservations-dates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Reservation DateOut';

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
        $now = now('Asia/Riyadh');
        $date = $now->format('Y-m-d');
        $nextDate = $now->addDay()->format('Y-m-d');
        $now =  date('H:i');
        /** @var Collection $day_ends */
        $day_ends = \DB::table('settings')
            ->where('key', '=', 'day_end')
            ->where('value', '=', (string)$now)
            ->get();

        if ($day_ends->isNotEmpty()) {
            $arr = [] ;
            $arr2 = [] ;
            foreach ($day_ends as $day_end) {
                $team_id = $day_end->team_id;
                $enabled = \DB::table('settings')->where('key', '=', 'automatic_renewal_of_reservations')->where('team_id', '=', $day_end->team_id)->value('value');
                $arr [] = $enabled ;
                $arr2 [] = boolval($enabled);
                if (boolval($enabled)) {
                    /** @var Collection $reservations */
                    $reservations = Reservation::withoutGlobalScope('team_id')
                        ->whereNotNull('checked_in')
                        ->whereNull('checked_out')
                        ->where('date_out', '<=', $date)
                        ->whereStatus(Reservation::STATUS_CONFIRMED)
                        ->whereTeamId($team_id)
                        ->get();




                    if ($reservations->isNotEmpty()) {
                        /** @var Reservation $reservation */
                        foreach ($reservations as $reservation) {
                            $check = Reservation::withoutGlobalScope('team_id')
                                ->whereUnitId($reservation->unit_id)
                                ->whereTeamId($team_id)
                                ->where('id', '!=', $reservation->id)
                                ->whereStatus(Reservation::STATUS_CONFIRMED)
                                ->where('date_out', '>=', $nextDate)
                                ->whereNull('checked_in')
                                ->whereNull('checked_out')
                                ->count();

                            if ($check == 0) {
                                $reservation->date_out = $nextDate;
                                $reservation->date_out_time = now('Asia/Riyadh')->addDay();
                                /** @var Unit $unit */
                                $unit = Unit::withoutGlobalScope('team_id')->find($reservation->unit_id);

                                if($unit){
                                    $prices = $unit->getDatesFromRange(Carbon::parse($reservation->date_in), Carbon::parse($reservation->date_out));

                                    if ($reservation->old_prices) {
                                        $prices = $unit->getDatesFromRangeWithOldPrices(Carbon::parse($reservation->date_in), Carbon::parse($reservation->date_out), $reservation->old_prices, $reservation->change_rate, $reservation->rent_type, $reservation->rent_type);
                                    }

                                    $reservation->prices = $prices;

                                    $division = $prices['total_price_raw'] - $reservation->total_price;

                                    $reservation->total_price = $prices['total_price_raw'];
                                    $reservation->sub_total = $prices['price'];
                                    $reservation->vat_total = $prices['total_vat'];
                                    $reservation->ewa_total = $prices['total_ewa'];
                                    $reservation->ttx_total = $prices['total_tourism'];

                                    if ($reservation->save()) {
                                        $reservation->balance;
                                        if ($division < 0) {
                                            $reservation->depositFloat(abs($division), [
                                                'category' => 'update_reservation',
                                                'statement' => 'update Reservation Total Price deposit',
                                            ], true, false);
                                        } elseif ($division > 0) {
                                            $reservation->forceWithdrawFloat($division, [
                                                'category' => 'update_reservation',
                                                'statement' => 'update Reservation Total Price Withdraw',
                                            ], true, false);
                                        }

                                        $reservation->wallet->refreshBalance();

                                        event(new ReservationUpdated($reservation));
                                    }

                                }


                            }
                        }
                    }
                }
            }

        }

        $this->info('Thanks and GodBye');
    }
}
