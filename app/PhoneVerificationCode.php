<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PhoneVerificationCode extends Model
{
    protected $fillable = [
        'user_id', 
        'verification_code', 
        'expires_at'
    ];

    public function isExpired()
    {
        return $this->expires_at < now();
    }
}
