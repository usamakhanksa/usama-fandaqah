<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

trait Filterable
{
    /**
     * Apply common filters to the query.
     */
    public function scopeFilter(Builder $query, array $filters)
    {
        // 1. Hotel / Team
        $query->when($filters['team_id'] ?? null, function ($q, $v) {
            $q->where('team_id', $v);
        });

        // 2. Date Range (Created At)
        $query->when($filters['date_from'] ?? null, function ($q, $v) {
            $q->whereDate('created_at', '>=', $v);
        });
        $query->when($filters['date_to'] ?? null, function ($q, $v) {
            $q->whereDate('created_at', '<=', $v);
        });

        // 3. Business Date
        if (Schema::hasColumn($this->getTable(), 'business_date')) {
            $query->when($filters['business_date'] ?? null, function ($q, $v) {
                $q->where('business_date', $v);
            });
        }

        // 4. Status
        if (Schema::hasColumn($this->getTable(), 'status')) {
            $query->when($filters['status'] ?? null, function ($q, $v) {
                if (is_array($v)) {
                    $q->whereIn('status', $v);
                } else {
                    $q->where('status', $v);
                }
            });
        }

        // 5. User / Created By
        $query->when($filters['user_id'] ?? null, function ($q, $v) {
            if (Schema::hasColumn($this->getTable(), 'user_id')) {
                $q->where('user_id', $v);
            } elseif (Schema::hasColumn($this->getTable(), 'created_by')) {
                $q->where('created_by', $v);
            }
        });

        // 6. Amount Range
        $amountColumn = $filters['amount_column'] ?? 'amount';
        if (Schema::hasColumn($this->getTable(), $amountColumn)) {
            $query->when($filters['amount_min'] ?? null, fn($q, $v) => $q->where($amountColumn, '>=', $v));
            $query->when($filters['amount_max'] ?? null, fn($q, $v) => $q->where($amountColumn, '<=', $v));
        }

        // 7. Search
        $query->when($filters['search'] ?? null, function ($q, $v) {
            $searchable = $this->getSearchableColumns();
            $q->where(function ($sub) use ($v, $searchable) {
                foreach ($searchable as $column) {
                    $sub->orWhere($column, 'LIKE', "%{$v}%");
                }
            });
        });

        // 8. Sorting
        $sortField = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        if (Schema::hasColumn($this->getTable(), $sortField)) {
            $query->orderBy($sortField, $sortOrder);
        }

        return $query;
    }

    /**
     * Get columns that should be searchable via 'search' filter.
     */
    protected function getSearchableColumns(): array
    {
        return property_exists($this, 'searchable') ? $this->searchable : ['id'];
    }
}
