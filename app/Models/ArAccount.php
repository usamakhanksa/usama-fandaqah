<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArAccount extends Model {
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'company_name', 
        'contact_person', 
        'email', 
        'phone', 
        'credit_limit', 
        'current_balance', 
        'status'
    ];
    
    protected $casts = [
        'credit_limit' => 'decimal:2', 
        'current_balance' => 'decimal:2'
    ];

    public function transactions() {
        return $this->hasMany(FinancialTransaction::class);
    }
    
    public function scopeSearch($query, $search) {
        return $query->where('company_name', 'like', "%{$search}%")
                     ->orWhere('contact_person', 'like', "%{$search}%");
    }
}
