<?php

namespace App\Console\Commands;

use App\Transaction;
use App\ReservationInvoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixZeroAmountInManualInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'reservation-invoices:fix-amount {team_id} {ids*}';
    protected $signature = 'reservation-invoices:fix-amount {team_id}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix Zero Based Manaul Invoices While Reservation is not Zero Based';

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
        try {
            $team_id = $this->argument('team_id');
            // $ids = explode(',' , $this->argument('ids')[0]);
            // $invoices = ReservationInvoice::with('reservation')->where('team_id',$team_id)->whereIn('id' , $ids)->get();
            $invoices = ReservationInvoice::with('reservation')->where('team_id',$team_id)->where('data->amount' , '<=' , 0 )->get();
            // $invoices = ReservationInvoice::with('reservation')->where('team_id',$team_id)->where('reservation_id',213523)->get();
           
            $invoices_bar = $this->output->createProgressBar(count($invoices));
            foreach ($invoices as $invoice) {
                $to = new \DateTime($invoice->to);
                $from = new \DateTime($invoice->from);
                $old_data = $invoice->data;
                $data = new \stdClass();
                $data->sub_total =  (float) number_format($invoice->reservation->sub_total, 2, '.', '');
                $data->vat =  (float) number_format($invoice->reservation->vat_total, 2, '.', '');
                $data->ewa =  (float) number_format($invoice->reservation->ew_total, 2, '.', '');
                $data->ttx =  (float) number_format($invoice->reservation->ttx_total, 2, '.', '');
                $data->total_price = (float) number_format($invoice->reservation->total_price, 2, '.', '');
                $data->nights = $old_data['nights'];
                $filtered_services = $this->filterServices($invoice->reservation->id, $from, $to);
                $data->servicesSum = abs($filtered_services['servicesSum']);
                $data->amount = (float) number_format($data->total_price + $data->servicesSum, 2, '.', '');
                $data->services = $filtered_services['services'];

                $invoice->data = $data;
                $invoice->save();

                $invoices_bar->advance();
            }

            $invoices_bar->finish();
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }

    }

    private function filterServices($reservation_id, $from, $to)
    {

        $container = [];
        $servicesSum = [];
        $servicesTransactions  = Transaction::with('wallet')->where('payable_type', 'App\\Reservation')->where('payable_id', $reservation_id)->where('is_public', 0)->where('meta->category', 'service')->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->get();

        if (count($servicesTransactions)) {
            foreach ($servicesTransactions as $transaction) {

                $servicesSum[] = $transaction->amount / ($transaction->wallet->decimal_places == 3 ? 1000 : 100);

                foreach ($transaction->meta['services'] as $serviceObj) {

                    $container[] = $serviceObj;
                }
            }
        }

        return ['services' => $container, 'servicesSum' => array_sum($servicesSum)];
    }
}
