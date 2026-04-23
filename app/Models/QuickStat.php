<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class QuickStat extends Model {
    protected $fillable = ['label', 'value', 'icon'];
}
