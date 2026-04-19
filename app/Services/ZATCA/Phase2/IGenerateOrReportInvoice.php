<?php


namespace App\Services\ZATCA\Phase2;



interface IGenerateOrReportInvoice
{
    public function __construct(string $username, string $password, $org);
    
    public function generateCompliantInvoice($invoice);

    public function reportSimplified($invoice_base64, $invoice_hash, $uuid);

    public function reportStandard($invoice_base64, $invoice_hash, $uuid);

    static public function getInvoiceInPDFA ($invoice_xml, $meta); 
}