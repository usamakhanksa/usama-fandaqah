<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Scopes\TeamScope;

class ReservationContract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reservation_id',
        'team_id',
        'uuid',
        'contract_number',
        'status',
        'html_path',
        'pdf_path',
        'version',
        'shorten_url_code',
        'generated_at',
        'signed_at',
        'generated_by',
        'signed_by',
        'signature_data',
        'notes',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'signed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new TeamScope());

        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'generated_by');
    }

    public function signedBy(): BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'signed_by');
    }

    public function getHtmlUrlAttribute(): ?string
    {
        return $this->html_path ? Storage::disk(env('FILESYSTEM_DRIVER', 'local'))->url($this->html_path) : null;
    }

    public function getPdfUrlAttribute(): ?string
    {
        return $this->pdf_path ? Storage::disk(env('FILESYSTEM_DRIVER', 'local'))->url($this->pdf_path) : null;
    }

    public function getIsSignedAttribute(): bool
    {
        return $this->status === 'signed' && $this->signed_at !== null;
    }

    public function markAsSigned(int $userId = null): void
    {
        $this->update([
            'status' => 'signed',
            'signed_at' => now(),
            'signed_by' => $userId ?? auth()->id(),
        ]);
    }

    public function markAsPending(): void
    {
        $this->update([
            'status' => 'pending',
        ]);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSigned($query)
    {
        return $query->where('status', 'signed');
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('generated_at', $date);
    }
}
