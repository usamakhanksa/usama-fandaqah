<?php

namespace App\Services\ZATCA\Phase2;

interface IVerifyOtp
{
    public function __construct($org);

    public function verifyOtp(string $otp);
}