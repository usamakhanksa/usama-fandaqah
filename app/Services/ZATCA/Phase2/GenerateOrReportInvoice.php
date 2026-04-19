<?php
namespace App\Services\ZATCA\Phase2;

use App\Jobs\HandleDownloadPdf;
use App\Services\ZATCA\Phase2\IGenerateOrReportInvoice;

class GenerateOrReportInvoice implements IGenerateOrReportInvoice
{
    private $baseUrl;
    private $username = null;
    private $password = null;
    private $token = null;

    public function __construct(string $username, string $password, $org)
    {
        $this->username = $username;
        $this->password = $password;
        $this->baseUrl = env('ZATCA_EINVOICING_API_URL');
        $this->token = base64_encode(json_encode($org)) . "::" . env('ZATCA_EINVOICING_API_TOKEN');
    }

    /**
     * see request payload example at generateOrReportInvoice.example.json 
     */
    public function generateCompliantInvoice($invoice)
    {


        $ch = curl_init("{$this->baseUrl}/generate-invoice"); // Initialise cURL

        $post = json_encode($invoice); // Encode the data array into a JSON string

        $authorization = "Authorization: Bearer " . $this->token; // Prepare the authorisation token
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json', $authorization)); // Inject the token into the header
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1); // Specify the request method as POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post); // Set the posted fields
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects

        $result = curl_exec($ch); // Execute the cURL statement
        curl_close($ch); // Close the cURL connection
        $response = json_decode($result); // Return the received data

        return $response;

    }

    public function reportSimplified($invoice_base64, $invoice_hash, $invoice_uuid)
    {
        $payload = [
            'credential' => array(
                'binarySecurityToken' => $this->username,
                'secret' => $this->password
            ),
            'invoice_hash' => $invoice_hash,
            'invoice' => $invoice_base64,
            'uuid' => $invoice_uuid
        ];

        $ch = curl_init("{$this->baseUrl}/reporting"); // Initialise cURL
        $post = json_encode($payload); // Encode the data array into a JSON string
        $authorization = "Authorization: Bearer " . $this->token; // Prepare the authorisation token
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization, 'Accept: application/json')); // Inject the token into the header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1); // Specify the request method as POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post); // Set the posted fields
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects

        $result = curl_exec($ch); // Execute the cURL statement
        curl_close($ch); // Close the cURL connection
        $response = json_decode($result); // Return the received data

        return $response;
    }

    public function reportStandard($invoice_base64, $invoice_hash, $invoice_uuid)
    {
        $payload = [
            'credential' => array(
                'binarySecurityToken' => $this->username,
                'secret' => $this->password
            ),
            'invoice_hash' => $invoice_hash,
            'invoice' => $invoice_base64,
            'uuid' => $invoice_uuid
        ];

        $ch = curl_init("{$this->baseUrl}/clearance"); // Initialise cURL
        $post = json_encode($payload); // Encode the data array into a JSON string
        $authorization = "Authorization: Bearer " . $this->token; // Prepare the authorisation token
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization, 'Accept: application/json')); // Inject the token into the header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1); // Specify the request method as POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post); // Set the posted fields
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
        $result = curl_exec($ch); // Execute the cURL statement
        curl_close($ch); // Close the cURL connection
        $response = json_decode($result); // Return the received data

        return $response;
    }

    static public function getInvoiceInPDFA($invoice_xml, $meta)
    {
        $baseUrl = env('ZATCA_EINVOICING_API_URL');
        $token = "T::" . env('ZATCA_EINVOICING_API_TOKEN');
        $payload = [
            'invoiceXML' => $invoice_xml,
            'meta' => $meta
        ];
        
        $ch = curl_init("https://ms-zatca.fandaqah.com/api/print-invoice-pdf-a3"); // Initialise cURL
        $post = json_encode($payload); // E ncode the data array into a JSON string
        $authorization = "Authorization: Bearer " . $token; // Prepare the authorisation token
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization, 'Accept: application/json')); // Inject the token into the header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1); // Specify the request method as POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post); // Set the posted fields
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects

        $result = curl_exec($ch); // Execute the cURL statement
        curl_close($ch); // Close the cURL connection

        $response = json_decode($result); // Return the received data

        return $response;
    }
    

    private function validateItem($item)
    {
        if (isset($item->quantity) && isset($item->total_amount_excl_tax) && isset($item->total_tax_amount) && isset($item->total_amount_incl_tax) && isset($item->item_name) && isset($item->item_cost)) {
            return true;
        } else {
            return false;
        }
    }

    static public function getInvoiceInPDFA3($invoice_xml, $meta)
    {
        $baseUrl = env('ZATCA_EINVOICING_API_URL');
        $token = "T::" . env('ZATCA_EINVOICING_API_TOKEN');
        $payload = [
            'invoiceXML' => $invoice_xml,
            'meta' => $meta
        ];
        
        $ch = curl_init("https://ms-zatca.fandaqah.com/api/v2/print-invoice-pdf-a3"); // Initialise cURL
        $post = json_encode($payload); // E ncode the data array into a JSON string
        $authorization = "Authorization: Bearer " . $token; // Prepare the authorisation token
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization, 'Accept: application/json')); // Inject the token into the header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1); // Specify the request method as POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post); // Set the posted fields
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects

        $result = curl_exec($ch); // Execute the cURL statement
        curl_close($ch); // Close the cURL connection

        $response = json_decode($result); // Return the received data

        return $response;
    }

    public static function downloadPDF ($resource_url) {
        $ch = curl_init($resource_url); // Initialise cURL
        $token = "T::" . env('ZATCA_EINVOICING_API_TOKEN');
        $authorization = "Authorization: Bearer " . $token; // Prepare the authorisation token
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization, 'Accept: application/json')); // Inject the token into the header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
        $result = curl_exec($ch); // Execute the cURL statement
        curl_close($ch); // Close the cURL connection
        return $result;
    }

        
}
