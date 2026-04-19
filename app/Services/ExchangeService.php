<?php

namespace App\Services;

use App\Interfaces\Rateable;
use App\Interfaces\Wallet;

class ExchangeService
{

    /**
     * @param Wallet $from
     * @param Wallet $to
     * @return float
     */
    public function rate(Wallet $from, Wallet $to): float
    {
        return app(Rateable::class)
            ->withAmount(1)
            ->withCurrency($from)
            ->convertTo($to);
    }

}
