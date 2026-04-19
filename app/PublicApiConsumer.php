<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use SMartins\PassportMultiauth\HasMultiAuthApiTokens;
use Watson\Rememberable\Rememberable;

class PublicApiConsumer extends  Authenticatable
{
    use Rememberable;
    use HasApiTokens;

}
