<?php

namespace App\Console\Commands;

use App\Reservation;
use App\ReservationInvoice;
use Illuminate\Console\Command;

class FillInvoicesForGroupReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fill:group-invoice {team_id} {invoice_id} {reservation_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill Group invoices to fix old added invoices - we can pass team_id or not';

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
        $invoice_id = $this->argument('invoice_id');
        $reservation_id = $this->argument('reservation_id');
        $invoice = ReservationInvoice::where('id' , $invoice_id)->where('reservation_id',$reservation_id)->where('team_id' , $team_id)->first();
        $reservation = Reservation::find($reservation_id);

        if($reservation->reservation_type == 'group'){
            $reservation['attachable_reservations_count'] = count($reservation->attachedReservations());
            $services = [];
            $balances = [];
            $shared_invoice_total_price = null;
            $main_reservation = null ;
            $push_main_reservation_to_collection = false;
            $has_at_least_one_vat = false;
            $reservations_minified = [];
            if(is_null($reservation->attachable_id)){
                $main_reservation = $reservation;
                $push_main_reservation_to_collection = false;
            }else{
                $main_reservation = Reservation::with('unit')->find($reservation->attachable_id);
                 $push_main_reservation_to_collection = true;
            }


            $reservations = Reservation::with('wallet', 'unit')
                                ->where('reservation_type' , 'group')
                                ->where('company_id' , $reservation->company_id)
                                ->where(function ($query) use($reservation,$main_reservation) {
                                    return $query->with('unit')->where('id',$reservation->id)->orWhere('attachable_id',$main_reservation->id);
                                })
                                ->where('status' , 'confirmed')
                                // ->whereNull('checked_out')
                                ->whereNull('deleted_at')
                                ->get();

            if($push_main_reservation_to_collection){
                $reservations->push($main_reservation);
            }
            foreach($reservations as $reservationObject){
                $balances [] = $reservationObject->wallet->decimal_places == 3 ? $reservationObject->balance / 1000 : $reservationObject->balance / 100;
                $shared_invoice_total_price += $reservationObject->total_price + $reservationObject->getServicesSum();
                $reservations_minified [] = [
                    'sub_total' => $reservationObject->sub_total,
                    'ewa' => $reservationObject->ewa_total,
                    'vat' => $reservationObject->vat_total,
                    'ttx' => $reservationObject->ttx_total,
                    'total_price' => $reservationObject->total_price
                ];
                if($reservationObject->services()->count()){
                    foreach($reservationObject->services as $transaction){
                        $services [] = $transaction;
                    }
                }

                if($reservationObject->vat_total){
                    $has_at_least_one_vat = true;
                }
            }

            // $reservation['group_reservation_transactions'] = collect($transactions)->sortByDesc('number')->values();
            $reservation['group_reservation_services'] = collect($services)->sortByDesc('service_log_number')->values();
            $reservation['shared_invoice_total_price'] = $shared_invoice_total_price;

            $invoice->data = [
                'group_balance' => array_sum($balances),
                'has_at_least_one_vat' => $has_at_least_one_vat,
                'reservations' => $reservations,
                'reservations_minified' => $reservations_minified,
                'company' => $reservation->company,
                'services' => $reservation['group_reservation_services'],
                'amount' =>  $reservation['shared_invoice_total_price']
            ];
            if($invoice->save()){
                return $this->info('Every thing went successfully -__-');
            }
        }


    }
}
