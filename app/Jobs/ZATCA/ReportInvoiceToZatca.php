<?php

namespace App\Jobs\ZATCA;

use App\Team;
use App\Reservation;
use App\ServiceLog;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\ZATCA\Phase2\GenerateOrReportInvoice;



class ReportInvoiceToZatca implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $invoice;
    public $credential; 
    public $reservation_id;
    public $model;
    private $service;
    private $team_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($invoice, $credential, $model, $reservation_id)
    {
        $this->invoice = $invoice;
        $this->credential = $credential;
        $this->reservation_id = $reservation_id;
        $this->model = $model;
        
        $this->team_id = auth()->user()->current_team_id;
        $org = auth()->user()->getSupplierEGS();

        $this->service = new GenerateOrReportInvoice($credential->username, $credential->password, $org);

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $compliant_invoice = $this->generateInvoice();
        $this->reportInvoice($compliant_invoice);

    }

    public function generateInvoice() {
        $team = Team::findOrFail($this->team_id);
        $invoice = ReservationInvoice::findOrFail($reservation_id);
        $compliant_invoice = $this->service->generateCompliantInvoice($invoice->id);
        return $compliant_invoice;
    }

    public function reportInvoice ($invoice) {
        if($invoice['invoice_type'] === 'standard tax invoice') {
            $response = $invoice_pusher->reportSimplified($invoice->invoice, $invoice->invoice_hash, $invoice->uuid);
        } else if ($invoice['invoice_type'] === 'simplified tax invoice') {
            $response = $invoice_pusher->reportStandard($invoice->invoice, $invoice->invoice_hash, $invoice->uuid);
        }
    }
}
