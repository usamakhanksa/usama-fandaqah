<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ZatcaService
{
    private $apiUrl;
    private $apiKey;
    private $certificatePath;
    private $privateKeyPath;

    public function __construct()
    {
        $this->apiUrl = config('services.zatca.api_url', 'https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal');
        $this->apiKey = config('services.zatca.api_key');
        $this->certificatePath = config('services.zatca.certificate_path');
        $this->privateKeyPath = config('services.zatca.private_key_path');
    }

    public function generateTlvQrCode(array $data): string
    {
        $sellerName = $data['seller_name'];
        $vatNumber = $data['vat_number'];
        $timestamp = $data['timestamp'];
        $total = number_format($data['total'], 2, '.', '');
        $vatTotal = number_format($data['vat_total'], 2, '.', '');

        // TLV (Tag-Length-Value) format for ZATCA QR code
        $tlvData = '';
        
        // Tag 1: Seller Name
        $tlvData .= $this->encodeTlv(1, $sellerName);
        
        // Tag 2: VAT Number
        $tlvData .= $this->encodeTlv(2, $vatNumber);
        
        // Tag 3: Timestamp
        $tlvData .= $this->encodeTlv(3, $timestamp);
        
        // Tag 4: Invoice Total
        $tlvData .= $this->encodeTlv(4, $total);
        
        // Tag 5: VAT Total
        $tlvData .= $this->encodeTlv(5, $vatTotal);

        // Base64 encode the TLV data
        return base64_encode($tlvData);
    }

    public function generateXml(array $invoiceData): string
    {
        $xml = new \SimpleXMLElement('<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2"/>');

        // Invoice header
        $xml->addAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $xml->addAttribute('xsi:schemaLocation', 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2 ../xsd/maindoc/UBL-Invoice-2.1.xsd');

        // UBL Version
        $cbc = $xml->addChild('cbc:UBLVersionID', '2.1');
        
        // Customization ID
        $cbc = $xml->addChild('cbc:CustomizationID', 'urn:fdc.gov.sa:invoice:1.0');
        
        // Profile ID
        $cbc = $xml->addChild('cbc:ProfileID', 'reporting:1.0');
        
        // ID (Invoice Number)
        $cbc = $xml->addChild('cbc:ID', $invoiceData['invoice_number']);
        
        // UUID
        $cbc = $xml->addChild('cbc:UUID', $invoiceData['uuid']);
        
        // Issue Date
        $cbc = $xml->addChild('cbc:IssueDate', $invoiceData['issue_date']);
        
        // Due Date
        if (!empty($invoiceData['due_date'])) {
            $cbc = $xml->addChild('cbc:DueDate', $invoiceData['due_date']);
        }
        
        // Invoice Type Code
        $cbc = $xml->addChild('cbc:InvoiceTypeCode', $invoiceData['invoice_type_code'] ?? '388');
        
        // Currency Code
        $cbc = $xml->addChild('cbc:DocumentCurrencyCode', $invoiceData['currency']);

        // Seller (Accounting Supplier Party)
        $cac = $xml->addChild('cac:AccountingSupplierParty');
        $party = $cac->addChild('cac:Party');
        
        // Seller Name
        $partyName = $party->addChild('cac:PartyName');
        $partyName->addChild('cbc:Name', $invoiceData['seller']['name']);
        
        // VAT Registration
        $taxScheme = $party->addChild('cac:PartyTaxScheme');
        $taxScheme->addChild('cbc:CompanyID', $invoiceData['seller']['vat_number']);
        $taxSchemeId = $taxScheme->addChild('cac:TaxScheme');
        $taxSchemeId->addChild('cbc:ID', 'VAT');
        
        // Legal Entity
        $legalEntity = $party->addChild('cac:PartyLegalEntity');
        $legalEntity->addChild('cbc:RegistrationName', $invoiceData['seller']['name']);
        if (!empty($invoiceData['seller']['cr_number'])) {
            $legalEntity->addChild('cbc:CompanyID', $invoiceData['seller']['cr_number']);
        }

        // Buyer (Accounting Customer Party)
        if (!empty($invoiceData['buyer']['name'])) {
            $cac = $xml->addChild('cac:AccountingCustomerParty');
            $party = $cac->addChild('cac:Party');
            
            // Buyer Name
            $partyName = $party->addChild('cac:PartyName');
            $partyName->addChild('cbc:Name', $invoiceData['buyer']['name']);
            
            // Buyer VAT
            if (!empty($invoiceData['buyer']['vat_number'])) {
                $taxScheme = $party->addChild('cac:PartyTaxScheme');
                $taxScheme->addChild('cbc:CompanyID', $invoiceData['buyer']['vat_number']);
                $taxSchemeId = $taxScheme->addChild('cac:TaxScheme');
                $taxSchemeId->addChild('cbc:ID', 'VAT');
            }
            
            // Buyer Address
            if (!empty($invoiceData['buyer']['address'])) {
                $address = $party->addChild('cac:PostalAddress');
                if (!empty($invoiceData['buyer']['address'])) {
                    $address->addChild('cbc:StreetName', $invoiceData['buyer']['address']);
                }
                if (!empty($invoiceData['buyer']['city'])) {
                    $address->addChild('cbc:CityName', $invoiceData['buyer']['city']);
                }
                if (!empty($invoiceData['buyer']['state'])) {
                    $address->addChild('cbc:CountrySubentity', $invoiceData['buyer']['state']);
                }
                if (!empty($invoiceData['buyer']['postal_code'])) {
                    $address->addChild('cbc:PostalZone', $invoiceData['buyer']['postal_code']);
                }
                if (!empty($invoiceData['buyer']['country'])) {
                    $country = $address->addChild('cac:Country');
                    $country->addChild('cbc:IdentificationCode', $invoiceData['buyer']['country']);
                }
            }
            
            // Contact
            if (!empty($invoiceData['buyer']['email']) || !empty($invoiceData['buyer']['phone'])) {
                $contact = $party->addChild('cac:Contact');
                if (!empty($invoiceData['buyer']['email'])) {
                    $contact->addChild('cbc:ElectronicMail', $invoiceData['buyer']['email']);
                }
                if (!empty($invoiceData['buyer']['phone'])) {
                    $contact->addChild('cbc:Telephone', $invoiceData['buyer']['phone']);
                }
            }
        }

        // Invoice Lines
        foreach ($invoiceData['items'] as $index => $item) {
            $line = $xml->addChild('cac:InvoiceLine');
            
            // Line ID
            $line->addChild('cbc:ID', $index + 1);
            
            // Quantity
            $line->addChild('cbc:InvoicedQuantity', $item['quantity']);
            $line->children()[count($line->children()) - 1]->addAttribute('unitCode', 'PCE');
            
            // Line Extension Amount
            $line->addChild('cbc:LineExtensionAmount', number_format($item['total_price'], 2, '.', ''));
            $line->children()[count($line->children()) - 1]->addAttribute('currencyID', $invoiceData['currency']);
            
            // Price
            $price = $line->addChild('cac:Price');
            $price->addChild('cbc:PriceAmount', number_format($item['unit_price'], 2, '.', ''));
            $price->children()[0]->addAttribute('currencyID', $invoiceData['currency']);
            
            // Item
            $itemXml = $line->addChild('cac:Item');
            $itemXml->addChild('cbc:Description', $item['description']);
            $itemXml->addChild('cbc:Name', $item['description']);
            
            // Tax Category
            if ($item['tax_rate'] > 0) {
                $taxCategory = $itemXml->addChild('cac:ClassifiedTaxCategory');
                $taxCategory->addChild('cbc:ID', 'S');
                $taxCategory->addChild('cbc:Percent', number_format($item['tax_rate'], 2, '.', ''));
                $taxScheme = $taxCategory->addChild('cac:TaxScheme');
                $taxScheme->addChild('cbc:ID', 'VAT');
            }
        }

        // Tax Total
        $taxTotal = $xml->addChild('cac:TaxTotal');
        $taxTotal->addChild('cbc:TaxAmount', number_format($invoiceData['tax_amount'], 2, '.', ''));
        $taxTotal->children()[0]->addAttribute('currencyID', $invoiceData['currency']);

        // Legal Monetary Total
        $legalTotal = $xml->addChild('cac:LegalMonetaryTotal');
        $legalTotal->addChild('cbc:LineExtensionAmount', number_format($invoiceData['total_amount'] - $invoiceData['tax_amount'], 2, '.', ''));
        $legalTotal->children()[0]->addAttribute('currencyID', $invoiceData['currency']);
        $legalTotal->addChild('cbc:TaxExclusiveAmount', number_format($invoiceData['total_amount'] - $invoiceData['tax_amount'], 2, '.', ''));
        $legalTotal->children()[1]->addAttribute('currencyID', $invoiceData['currency']);
        $legalTotal->addChild('cbc:TaxInclusiveAmount', number_format($invoiceData['total_amount'], 2, '.', ''));
        $legalTotal->children()[2]->addAttribute('currencyID', $invoiceData['currency']);
        $legalTotal->addChild('cbc:PayableAmount', number_format($invoiceData['total_amount'], 2, '.', ''));
        $legalTotal->children()[3]->addAttribute('currencyID', $invoiceData['currency']);

        // Format XML
        $dom = dom_import_simplexml($xml);
        $dom->formatOutput = true;
        
        return $dom->saveXML();
    }

    public function reportInvoice(Invoice $invoice): array
    {
        try {
            // Prepare request data
            $data = [
                'uuid' => $invoice->zatca_uuid,
                'invoice' => base64_encode($invoice->zatca_xml),
                'qr_code' => $invoice->zatca_qr_code,
                'hash' => $invoice->zatca_hash,
            ];

            // Make API request
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/invoices/reporting', $data);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'clearance_number' => $response->json('clearance_number'),
                    'message' => 'Invoice reported successfully',
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $response->json('message') ?? 'Unknown error',
                    'errors' => $response->json('errors') ?? [],
                ];
            }

        } catch (\Exception $e) {
            Log::error('ZATCA reporting failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'API Error: ' . $e->getMessage(),
            ];
        }
    }

    public function clearInvoice(Invoice $invoice): array
    {
        try {
            // Prepare request data
            $data = [
                'uuid' => $invoice->zatca_uuid,
                'clearance_number' => $invoice->zatca_clearance_number,
            ];

            // Make API request
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/invoices/clearance', $data);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Invoice cleared successfully',
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $response->json('message') ?? 'Unknown error',
                    'errors' => $response->json('errors') ?? [],
                ];
            }

        } catch (\Exception $e) {
            Log::error('ZATCA clearance failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'API Error: ' . $e->getMessage(),
            ];
        }
    }

    public function cancelInvoice(Invoice $invoice): array
    {
        try {
            // Prepare request data
            $data = [
                'uuid' => $invoice->zatca_uuid,
                'reason' => 'Invoice voided',
            ];

            // Make API request
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/invoices/cancellation', $data);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Invoice cancelled successfully',
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $response->json('message') ?? 'Unknown error',
                    'errors' => $response->json('errors') ?? [],
                ];
            }

        } catch (\Exception $e) {
            Log::error('ZATCA cancellation failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'API Error: ' . $e->getMessage(),
            ];
        }
    }

    public function getInvoiceStatus(string $uuid): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->get($this->apiUrl . '/invoices/status/' . $uuid);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $response->json('status'),
                    'clearance_number' => $response->json('clearance_number'),
                    'reported_at' => $response->json('reported_at'),
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $response->json('message') ?? 'Unknown error',
                ];
            }

        } catch (\Exception $e) {
            Log::error('ZATCA status check failed', [
                'uuid' => $uuid,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'API Error: ' . $e->getMessage(),
            ];
        }
    }

    public function validateInvoice(array $invoiceData): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/invoices/validate', $invoiceData);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'valid' => $response->json('valid'),
                    'errors' => $response->json('errors') ?? [],
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $response->json('message') ?? 'Unknown error',
                    'errors' => $response->json('errors') ?? [],
                ];
            }

        } catch (\Exception $e) {
            Log::error('ZATCA validation failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'API Error: ' . $e->getMessage(),
            ];
        }
    }

    private function encodeTlv(int $tag, string $value): string
    {
        $length = strlen($value);
        return pack('C', $tag) . pack('n', $length) . $value;
    }

    private function signXml(string $xml): string
    {
        if (empty($this->privateKeyPath) || empty($this->certificatePath)) {
            return $xml;
        }

        // This would implement XML signing using OpenSSL
        // For now, return original XML
        return $xml;
    }
}
