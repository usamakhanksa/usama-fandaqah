<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SafeTransaction extends Model {
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type', 'amount', 'reference_number', 'category', 
        'description', 'transaction_date', 'performed_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public function scopeSearch($query, $search) {
        return $query->where('reference_number', 'like', "%{$search}%")
                     ->orWhere('description', 'like', "%{$search}%")
                     ->orWhere('performed_by', 'like', "%{$search}%");
    }
}
