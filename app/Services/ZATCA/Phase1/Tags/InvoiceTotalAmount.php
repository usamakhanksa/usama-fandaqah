<?php

namespace App\Services\ZATCA\Phase1\Tags;

use App\Services\ZATCA\Phase1\Tag;

class InvoiceTotalAmount extends Tag
{
    public function __construct($value)
    {
        parent::__construct(4, $value);
    }
}
