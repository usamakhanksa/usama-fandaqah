<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Watson\Rememberable\Rememberable;

class PasswordReset extends Model
{
    use Rememberable;

	public $incrementing = false;
	protected $primaryKey = 'email';
    public $timestamps = false;

    protected $fillable = [
        'email', 'token'
    ];
}
