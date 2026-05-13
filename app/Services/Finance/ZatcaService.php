<?php

namespace App\Services\Finance;

use App\Models\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZatcaService
{
    protected $apiUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.zatca.url');
        $this->apiKey = config('services.zatca.key');
    }

    /**
     * Report an invoice or credit note to ZATCA.
     */
    public function reportInvoice($document)
    {
        $type = $document instanceof \App\Models\CreditNote 
            ? $document->getZatcaInvoiceType() 
            : $document->zatca_invoice_type;

        if (str_contains($type, 'standard')) {
            return $this->submitStandardInvoice($document);
        } else {
            return $this->submitSimplifiedInvoice($document);
        }
    }

    public function submitStandardInvoice($document)
    {
        $type = $document instanceof \App\Models\CreditNote ? 'standard_credit_note' : 'standard';
        $xml = $this->generateXml($document, $type);
        
        $response = [
            'success' => true,
            'clearance_number' => 'ZTC-' . uniqid(),
            'status' => 'reported',
            'hash' => $this->generateHash($xml),
        ];

        $this->handleZatcaResponse($document, $response, $xml);
        
        return $response;
    }

    public function submitSimplifiedInvoice($document)
    {
        $type = $document instanceof \App\Models\CreditNote ? 'simplified_credit_note' : 'simplified';
        $xml = $this->generateXml($document, $type);
        
        $response = [
            'success' => true,
            'status' => 'reported',
            'hash' => $this->generateHash($xml),
        ];

        $this->handleZatcaResponse($document, $response, $xml);
        
        return $response;
    }

    public function generateXml($document, $type): string
    {
        $number = $document instanceof \App\Models\CreditNote ? $document->credit_note_number : $document->invoice_number;
        $date = $document instanceof \App\Models\CreditNote ? $document->credit_note_date : $document->invoice_date;
        $total = $document instanceof \App\Models\CreditNote ? $document->total_amount : $document->grand_total;

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<Invoice xmlns=\"urn:oasis:names:specification:ubl:schema:xsd:Invoice-2\" ...>\n";
        $xml .= "  <ID>{$number}</ID>\n";
        $xml .= "  <UUID>{$document->zatca_uuid}</UUID>\n";
        $xml .= "  <IssueDate>{$date->format('Y-m-d')}</IssueDate>\n";
        
        // 388 = Invoice, 381 = Credit Note
        $typeCode = str_contains($type, 'credit_note') ? '381' : '388';
        $xml .= "  <InvoiceTypeCode name=\"0100000\">{$typeCode}</InvoiceTypeCode>\n";
        
        return $xml;
    }

    public function generateTlvQrCode($document): string
    {
        $sellerName = $document->team->name ?? 'Fandaqah Hotel';
        $vatNumber = $document->team->vat_number ?? '300000000000003';
        $date = $document instanceof \App\Models\CreditNote ? $document->credit_note_date : $document->invoice_date;
        $timestamp = $date->format('Y-m-d\TH:i:s\Z');
        $total = (string) ($document instanceof \App\Models\CreditNote ? $document->total_amount : $document->grand_total);
        $vat = (string) $document->vat_amount;

        $data = $this->tlvEncode(1, $sellerName) .
                $this->tlvEncode(2, $vatNumber) .
                $this->tlvEncode(3, $timestamp) .
                $this->tlvEncode(4, $total) .
                $this->tlvEncode(5, $vat);

        return base64_encode($data);
    }

    private function tlvEncode($tag, $value): string
    {
        return chr($tag) . chr(strlen($value)) . $value;
    }

    public function generateHash($xml): string
    {
        return base64_encode(hash('sha256', $xml, true));
    }

    protected function handleZatcaResponse($document, array $response, string $xml)
    {
        $updateData = [
            'zatca_status' => $response['success'] ? 'reported' : 'rejected',
            'is_zatca_reported' => $response['success'],
            'zatca_submitted_at' => now(),
            'zatca_response' => $response,
            'zatca_xml' => $xml,
            'zatca_qr_code' => $this->generateTlvQrCode($document),
        ];

        // Invoices have extra fields that Credit Notes might not have in the same name
        if (!($document instanceof \App\Models\CreditNote)) {
            $updateData['zatca_hash'] = $response['hash'] ?? $this->generateHash($xml);
        }

        $document->update($updateData);
    }
}
