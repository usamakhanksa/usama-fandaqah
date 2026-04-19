<?php

return [
    'gateways' => [
        'hyperpay' => [
            'development' => [
                'url' => env('HYPERPAY_BASE_URL', null),
                'test_mode' =>  env('HYPERPAY_TEST_MODE', 'INTERNAL'),
                'access_token' =>  env('HYPERPAY_ACCESS_TOKEN', null),
                'currency' =>  env('HYPERPAY_CURRENCY', 'SAR')
            ],
            'production' => [
                'url' => env('HYPERPAY_BASE_URL', null),
                'access_token' =>  env('HYPERPAY_ACCESS_TOKEN', null),
                'currency' =>  env('HYPERPAY_CURRENCY', 'SAR')
            ],

            'entities' => [
                'visa_mastercard_stc_amex' =>  env('HYPERPAY_ENTITY_ID_VISA_MASTERCARD_STC_AMEX', null),
                'mada' =>  env('HYPERPAY_ENTITY_ID_MADA', null),
                'apple' =>  env('HYPERPAY_ENTITY_ID_APPLE', null),
            ],

            'brands' => env('HYPERPAY_BRANDS', null),
            'information' => [
                'customer_email' => env('CUSTOMER_EMAIL', null),
                'billing_street1' => env('BILLING_STREET_1', null),
                'billing_city' => env('BILLING_CITY', null),
                'billing_state' => env('BILLING_STATE', null),
                'billing_country' => env('BILLING_COUNTRY', null),
                'billing_postcode' => env('BILLING_POSTCODE', null),
                'customer_givenName' => env('CUSTOMER_GIVEN_NAME', null),
                'customer_surname' => env('CUSTOMER_SURNAME', null),
            ]

        ]
    ]
];
