<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DailyReport extends Model {
    protected $fillable = ['report_date', 'total_revenue', 'occupied_rooms', 'adr', 'revpar'];
}
