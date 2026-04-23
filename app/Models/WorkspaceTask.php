<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkspaceTask extends Model {
    use HasFactory;

    protected $fillable = ['title', 'description', 'room_number', 'priority', 'status', 'due_at'];

    protected $casts = [
        'due_at' => 'datetime',
    ];

    public function scopeSearch($query, $search) {
        return $query->where('title', 'like', "%{$search}%")
                     ->orWhere('room_number', 'like', "%{$search}%");
    }
}
