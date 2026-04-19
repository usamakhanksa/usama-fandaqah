<?php

namespace App\Interfaces;

interface MinimalTaxable extends Taxable
{
    /**
     * @return int
     */
    public function getMinimalFee(): int;
}
