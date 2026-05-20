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
    public function __construct($invoice, $credential, $model, $reservation_id, $team_id)
    {
        $this->invoice = $invoice;
        $this->credential = $credential;
        $this->reservation_id = $reservation_id;
        $this->model = $model;
        $this->team_id = $team_id;
        
        // We will initialize service in handle() to ensure we have credentials
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $team = Team::findOrFail($this->team_id);
        // Note: getSupplierEGS() should be a method on Team or User, 
        // assuming it's available on Team for background jobs.
        $org = $team->getSupplierEGS(); 

        $this->service = new GenerateOrReportInvoice($this->credential->username, $this->credential->password, $org);

        $compliant_invoice = $this->generateInvoice();
        $this->reportInvoice($compliant_invoice);
    }

    public function generateInvoice() {
        // Use the main Invoice model
        $invoice = \App\Models\Invoice::findOrFail($this->invoice->id);
        $compliant_invoice = $this->service->generateCompliantInvoice($invoice->id);
        return $compliant_invoice;
    }

    public function reportInvoice ($invoice) {
        if($invoice['invoice_type'] === 'standard tax invoice') {
            // Standard (B2B) requires Clearance
            $response = $this->service->reportStandard($invoice->invoice, $invoice->invoice_hash, $invoice->uuid);
        } else if ($invoice['invoice_type'] === 'simplified tax invoice') {
            // Simplified (B2C) requires Reporting
            $response = $this->service->reportSimplified($invoice->invoice, $invoice->invoice_hash, $invoice->uuid);
        }
    }
}
