<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedReport extends Model {
    use HasFactory;

    protected $fillable = ['name', 'report_type', 'filters', 'is_favorite'];

    protected $casts = [
        'filters' => 'array',
        'is_favorite' => 'boolean',
    ];
}
