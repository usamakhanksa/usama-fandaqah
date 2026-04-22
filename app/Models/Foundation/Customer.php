<?php

namespace App\Models\Foundation;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Database\Factories\Foundation\CustomerFactory;

class Customer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'first_name_en','last_name_en','first_name_ar','last_name_ar','email','phone','document_type','document_number','date_of_birth','city','address_line','vip','notes',
    ];

    protected $casts = [
        'vip' => 'boolean',
        'date_of_birth' => 'date',
    ];

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function getFullNameEnAttribute(): string
    {
        return trim($this->first_name_en.' '.$this->last_name_en);
    }

    protected static function newFactory(): CustomerFactory
    {
        return CustomerFactory::new();
    }
}
