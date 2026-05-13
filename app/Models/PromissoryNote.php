<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class PromissoryNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'collected_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'last_collection_date' => 'date',
        'next_follow_up_date' => 'date',
        'is_overdue' => 'boolean',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function collections(): HasMany
    {
        return $this->hasMany(PromissoryCollection::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PromissoryCollectionLog::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check and update overdue status.
     */
    public function updateOverdueStatus(): void
    {
        if ($this->status === 'collected' || $this->status === 'cancelled') {
            $this->update(['is_overdue' => false, 'overdue_days' => 0]);
            return;
        }

        $isOverdue = Carbon::now()->isAfter($this->due_date);
        $overdueDays = $isOverdue ? Carbon::now()->diffInDays($this->due_date) : 0;

        $this->update([
            'is_overdue' => $isOverdue,
            'overdue_days' => $overdueDays
        ]);
    }

    /**
     * Update remaining amount.
     */
    public function updateAmounts(): void
    {
        $collected = $this->collections()
            ->where('status', 'confirmed')
            ->sum('amount');

        $remaining = $this->amount - $collected;
        $status = $this->status;

        if ($remaining <= 0) {
            $status = 'collected';
        } elseif ($collected > 0) {
            $status = 'partially_collected';
        }

        $this->update([
            'collected_amount' => $collected,
            'remaining_amount' => max(0, $remaining),
            'status' => $status,
            'last_collection_date' => $this->collections()->where('status', 'confirmed')->latest('collection_date')->value('collection_date'),
        ]);
    }
}
