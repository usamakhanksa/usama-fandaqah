<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompTransaction extends Model {
    use HasFactory;
    
    protected $fillable = [
        'booking_reference', 
        'department', 
        'value_amount', 
        'reason', 
        'approved_by', 
        'date_posted'
    ];
    
    protected $casts = [
        'value_amount' => 'decimal:2', 
        'date_posted' => 'date'
    ];
}
