<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormIntegration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'integration_id',
        'form_name',
        'form_url',
        'field_mapping',
        'auto_approve',
        'status',
        'submission_count',
        'last_submission_at',
        'webhook_secret',
        'created_by',
    ];

    protected $casts = [
        'field_mapping' => 'array',
        'auto_approve' => 'boolean',
        'last_submission_at' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
