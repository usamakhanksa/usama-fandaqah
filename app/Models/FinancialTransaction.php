<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialTransaction extends Model {
    use HasFactory;
    
    protected $fillable = [
        'booking_reference', 
        'ar_account_id', 
        'type', 
        'amount', 
        'payment_method', 
        'description', 
        'receipt_number', 
        'transaction_date'
    ];
    
    protected $casts = [
        'amount' => 'decimal:2', 
        'transaction_date' => 'date'
    ];

    public function arAccount() {
        return $this->belongsTo(ArAccount::class);
    }
}
