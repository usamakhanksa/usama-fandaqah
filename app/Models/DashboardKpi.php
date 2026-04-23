<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DashboardKpi extends Model {
    protected $fillable = ['key', 'label', 'value', 'trend', 'icon', 'color', 'is_active', 'sort_order'];
}
