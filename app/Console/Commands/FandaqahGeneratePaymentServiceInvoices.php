<?php

namespace App\Console\Commands;

use App\Jobs\FetchOnlinePaymentsPerTeam;
use App\Team;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FandaqahGeneratePaymentServiceInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fandaqah:generate-payment-service-invoices {--team_id= : specific team id to run the command} {--flag= : flag will be payment option service invoicing type daily,weekly or monthly}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will generate payment service invoices it will run by a cron job';

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
        $force_team_id = $this->option('team_id');
        $force_invoicing_type = $this->option('flag');
        $teams = Team::where('payment_preprocessor','hyperpay')
                ->whereNull('deleted_at')
                ->when($force_invoicing_type , function($q) use($force_invoicing_type) {
                    $q->where('online_payment_service_invoicing_type',$force_invoicing_type);
                }) 
                ->when($force_team_id , function($q) use($force_team_id) {
                    $q->where('id',$force_team_id);
                })
                ->get();
                       
       if(count($teams)){
        foreach ($teams as $team) {
            $team_needed_data = [
                'id' => $team->id,
                'name' => $team->name,
                'tax_number' => $team->tax_number(),
                'online_payment_service_invoicing_type' => $team->online_payment_service_invoicing_type,
                'country' => $team->country->title['en'] ?? 113
            ];
            FetchOnlinePaymentsPerTeam::dispatch($team_needed_data);
        }
       }
    }
}
