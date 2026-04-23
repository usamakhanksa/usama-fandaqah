<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataExport extends Model {
    use HasFactory;
    protected $fillable = ['name', 'format', 'status', 'file_path', 'file_size_kb', 'requested_by', 'expires_at'];
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function scopeSearch($query, $search) {
        return $query->where('name', 'like', "%{$search}%");
    }
}
