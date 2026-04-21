<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CheckOutRecord extends Model
{
    protected $guarded = [];
    protected $table = 'check_out_records';

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
