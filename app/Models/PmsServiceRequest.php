<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PmsServiceRequest extends Model {
    use HasFactory, SoftDeletes;
    protected $fillable = ['ticket_number', 'title', 'description', 'category', 'priority', 'status', 'reported_by', 'assigned_to'];

    public function scopeSearch($query, $search) {
        return $query->where('ticket_number', 'like', "%{$search}%")
                     ->orWhere('title', 'like', "%{$search}%");
    }
}
