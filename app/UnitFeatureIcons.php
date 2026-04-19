<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;
use Watson\Rememberable\Rememberable;

class UnitFeatureIcons extends Model
{
    use Rememberable;
    use LogsActivity;
    //


//    protected $table = 'unit_feature_icons';
}
