<?php

namespace App\Objects;

class Basket {
    private $arr;
    public function __construct()
    {
        $this->arr = [];
    }

    public function get() {
        return $this->arr;
    }

    public function push($value) {
        array_push($this->arr, $value);
    }
}