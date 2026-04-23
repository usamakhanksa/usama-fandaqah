<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientSale extends Model {
    use HasFactory;
    protected $fillable = ['client_profile_id', 'property_reference', 'amount', 'status', 'closed_at'];
    protected $casts = ['closed_at' => 'datetime', 'amount' => 'decimal:2'];

    public function profile(): BelongsTo {
        return $this->belongsTo(ClientProfile::class, 'client_profile_id');
    }
}
