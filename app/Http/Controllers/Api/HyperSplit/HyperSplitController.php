<?php

namespace App\Http\Controllers\Api\HyperSplit;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HyperSplitController extends Controller
{
    public function hyperSplitWebhookNotification(Request $request)
    {
        /* Php 7.1 or later */
        $key_from_configuration = env('HYPERSPLIT_NOTIFICATION_DECRYPT_ID');
        $iv_from_http_header = $request->header('X-Initialization-Vector');
        $auth_tag_from_http_header = $request->header('X-Authentication-Tag');
        $http_body = $request->get('encryptedBody');
        $key = hex2bin($key_from_configuration);
        $iv = hex2bin($iv_from_http_header);
        $auth_tag = hex2bin($auth_tag_from_http_header);
        $cipher_text = hex2bin($http_body);
        $result = openssl_decrypt(
            $cipher_text,
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $auth_tag
        );
        $result = json_decode($result);
        $result = json_decode($result);
        if ($result->status) {
            if (count($result->data->transactions)) {
                foreach ($result->data->transactions as $transaction) {
                    $payment_id = $transaction->uniqueId;

                    $base_url = env('HYPERPAY_PAYMENT_URL');
                    $client = new Client([
                        'headers' => [
                            'Content-Type' => 'application/json'
                        ],
                    ]);

                    try {
                        // just hit api on new hyperpay payment project
                        // @note : api only wants the amount and paymentMethod and hyperpay payment project will handle the rest
                        $initiateRequest = $client->request('POST', $base_url . 'api/hyperpay/payment-split-status', [
                            'body' => \GuzzleHttp\json_encode([
                                'payment_id' => $payment_id
                            ]),
                            'exceptions' => true
                        ]);

                        $response = json_decode($initiateRequest->getBody()->getContents());
                    } catch(Exception $ex) {
                        logger($ex->getMessage());
                    }
                }
            }
        }

        return response()->json('hyper split incoming notification');
    }
}
