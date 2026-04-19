<?php

use Bavix\Wallet\Simple\Rate;

return [
    'package' => [
        'rateable' => Rate::class,
    ],
    'currencies' => [],
    'services' => [
        'exchange' => \Bavix\Wallet\Services\ExchangeService::class,
        'common' => \App\Services\CommonService::class,
        'proxy' => \App\Services\ProxyService::class,
        'wallet' => \App\Services\WalletService::class,
    ],
    'transaction' => [
        'table' => 'transactions',
        'model' => \App\Transaction::class,
    ],
    'transfer' => [
        'table' => 'transfers',
        'model' => \App\Transfer::class,
    ],
    'wallet' => [
        'table' => 'wallets',
        'model' => \App\Wallet::class,
        'default' => [
            'name' => 'Default Wallet',
            'slug' => 'default',
            'decimal_places' => 10
        ],
    ],
];
