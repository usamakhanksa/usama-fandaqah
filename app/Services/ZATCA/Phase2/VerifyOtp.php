<?php

namespace App\Services\ZATCA\Phase2;

use App\Services\ZATCA\Phase2\IVerifyOtp;


class VerifyOtp implements IVerifyOtp {
    private $baseUrl;
    private $token = null;
    
    public function __construct ($org) {
        $this->baseUrl = env('ZATCA_EINVOICING_API_URL');
        $this->token = base64_encode(json_encode($org)) . "::" . env('ZATCA_EINVOICING_API_TOKEN');
    }

    public function verifyOtp(string $otp) {
        
        $body = [
            'otp' => $otp,
        ];
      
        $ch = curl_init("{$this->baseUrl}/onboarding-csid"); // Initialise cURL
        $post = json_encode($body); // Encode the data array into a JSON string
        $authorization = "Authorization: Bearer ". $this->token; // Prepare the authorisation token
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization )); // Inject the token into the header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1); // Specify the request method as POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post); // Set the posted fields
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
        $result = curl_exec($ch); // Execute the cURL statement
        curl_close($ch); // Close the cURL connection
        $response = json_decode($result); // Return the received data
        return $response;
    }
}


?>