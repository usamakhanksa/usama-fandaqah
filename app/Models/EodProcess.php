<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EodProcess extends Model {
    use HasFactory;
    
    protected $fillable = [
        'audit_date', 
        'total_revenue', 
        'total_payments', 
        'total_comps', 
        'status', 
        'run_by', 
        'completed_at'
    ];
    
    protected $casts = [
        'audit_date' => 'date', 
        'completed_at' => 'datetime', 
        'total_revenue' => 'decimal:2', 
        'total_payments' => 'decimal:2', 
        'total_comps' => 'decimal:2'
    ];
}
