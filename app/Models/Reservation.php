<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Reservation extends Model {
  protected $guarded=[];
  public function guest(){return $this->belongsTo(Guest::class);} 
  public function room(){return $this->belongsTo(Room::class);} 
  public function unit(){return $this->belongsTo(Unit::class);} 
}
