<?php


namespace App\Helpers;

class ObjectArray  {
    protected $arr;

    public function set($key, $value) {
        $this->arr[$key] = $value;
    }

    public function get($key) {
        return $this->arr[$key];
    }

}