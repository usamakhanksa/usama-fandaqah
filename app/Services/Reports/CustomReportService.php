<?php

namespace App\Services\Reports;

use App\Models\CustomReport;
use App\Models\Reservation;
use App\Models\Transaction;
use App\Models\Unit;
use App\Models\Guest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CustomReportService
{
    public function getAvailableColumns(string $module): array
    {
        return $this->availableColumns()[$module] ?? [];
    }

    private function availableColumns(): array
    {
        return [
            'reservations' => [
                ['key' => 'id', 'label' => 'ID', 'type' => 'integer'],
                ['key' => 'code', 'label' => 'Reservation Code', 'type' => 'string'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'string'],
                ['key' => 'check_in', 'label' => 'Check-In Date', 'type' => 'date'],
                ['key' => 'check_out', 'label' => 'Check-Out Date', 'type' => 'date'],
                ['key' => 'guest.first_name', 'label' => 'Guest First Name', 'type' => 'string', 'relation' => 'guest'],
                ['key' => 'guest.last_name', 'label' => 'Guest Last Name', 'type' => 'string', 'relation' => 'guest'],
                ['key' => 'guest.nationality', 'label' => 'Guest Nationality', 'type' => 'string', 'relation' => 'guest.country'],
                ['key' => 'unit.unit_number', 'label' => 'Unit Number', 'type' => 'string', 'relation' => 'unit'],
                ['key' => 'unit.unitType.name', 'label' => 'Unit Type', 'type' => 'string', 'relation' => 'unit.unitType'],
                ['key' => 'adults', 'label' => 'Adults', 'type' => 'integer'],
                ['key' => 'children', 'label' => 'Children', 'type' => 'integer'],
                ['key' => 'total_charge', 'label' => 'Total Charge', 'type' => 'decimal'],
                ['key' => 'paid_amount', 'label' => 'Paid Amount', 'type' => 'decimal'],
                ['key' => 'balance', 'label' => 'Balance', 'type' => 'decimal'],
                ['key' => 'source.name', 'label' => 'Source', 'type' => 'string', 'relation' => 'source'],
                ['key' => 'market_segment', 'label' => 'Market Segment', 'type' => 'string'],
                ['key' => 'created_at', 'label' => 'Created At', 'type' => 'datetime'],
            ],
            'finance' => [
                ['key' => 'id', 'label' => 'Transaction ID', 'type' => 'integer'],
                ['key' => 'kind', 'label' => 'Kind', 'type' => 'string'],
                ['key' => 'amount', 'label' => 'Amount', 'type' => 'decimal'],
                ['key' => 'description', 'label' => 'Description', 'type' => 'string'],
                ['key' => 'payment_method', 'label' => 'Payment Method', 'type' => 'string'],
                ['key' => 'payable.code', 'label' => 'Reservation Code', 'type' => 'string', 'relation' => 'payable'],
                ['key' => 'createdBy.name', 'label' => 'Created By', 'type' => 'string', 'relation' => 'createdBy'],
                ['key' => 'created_at', 'label' => 'Created At', 'type' => 'datetime'],
            ],
            'rooms' => [
                ['key' => 'id', 'label' => 'Unit ID', 'type' => 'integer'],
                ['key' => 'unit_number', 'label' => 'Unit Number', 'type' => 'string'],
                ['key' => 'name', 'label' => 'Name', 'type' => 'string'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'string'],
                ['key' => 'unitType.name', 'label' => 'Unit Type', 'type' => 'string', 'relation' => 'unitType'],
                ['key' => 'floor', 'label' => 'Floor', 'type' => 'string'],
                ['key' => 'capacity', 'label' => 'Capacity', 'type' => 'integer'],
                ['key' => 'price_per_night', 'label' => 'Price Per Night', 'type' => 'decimal'],
                ['key' => 'created_at', 'label' => 'Created At', 'type' => 'datetime'],
            ],
            'guests' => [
                ['key' => 'id', 'label' => 'Guest ID', 'type' => 'integer'],
                ['key' => 'first_name', 'label' => 'First Name', 'type' => 'string'],
                ['key' => 'last_name', 'label' => 'Last Name', 'type' => 'string'],
                ['key' => 'email', 'label' => 'Email', 'type' => 'string'],
                ['key' => 'phone', 'label' => 'Phone', 'type' => 'string'],
                ['key' => 'country.name', 'label' => 'Nationality', 'type' => 'string', 'relation' => 'country'],
                ['key' => 'id_number', 'label' => 'ID Number', 'type' => 'string'],
                ['key' => 'created_at', 'label' => 'Created At', 'type' => 'datetime'],
            ],
            'pos' => [
                ['key' => 'id', 'label' => 'Sale ID', 'type' => 'integer'],
                ['key' => 'total', 'label' => 'Total Amount', 'type' => 'decimal'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'string'],
                ['key' => 'payment_method', 'label' => 'Payment Method', 'type' => 'string'],
                ['key' => 'items_count', 'label' => 'Items Count', 'type' => 'integer'],
                ['key' => 'created_by', 'label' => 'Created By', 'type' => 'string'],
                ['key' => 'created_at', 'label' => 'Created At', 'type' => 'datetime'],
            ],
        ];
    }

    public function buildQuery(CustomReport $report): Builder
    {
        $query = $this->getBaseQuery($report->module);
        $query->where($report->module . '.team_id', $report->team_id);

        $filters = $report->filters ?? [];
        $this->applyFilters($query, $report->module, $filters);

        if ($report->sort_by && !str_contains($report->sort_by, '.')) {
            $query->orderBy($report->sort_by, $report->sort_direction ?? 'asc');
        }

        if ($report->group_by) {
            $query->groupBy($report->group_by);
        }

        $columns = $report->columns ?? [];
        $selects = [$report->module . '.id'];
        foreach ($columns as $col) {
            $key = $col['key'] ?? $col;
            if (!str_contains($key, '.')) {
                $selects[] = $report->module . '.' . $key;
            }
        }
        $query->select(array_unique($selects));

        return $query;
    }

    public function executeReport(CustomReport $report, $perPage = 50)
    {
        return $this->buildQuery($report)->paginate($perPage);
    }

    public function exportReport(CustomReport $report, $format = 'csv')
    {
        $results = $this->buildQuery($report)->get();
        $columns = $report->columns ?? [];
        $headers = [];
        foreach ($columns as $col) {
            $headers[] = $col['label'] ?? $col['key'] ?? $col;
        }

        $filename = "custom_report_{$report->id}.csv";
        $handle = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        fputcsv($handle, $headers);

        foreach ($results as $row) {
            $line = [];
            foreach ($columns as $col) {
                $key = $col['key'] ?? $col;
                if (str_contains($key, '.')) {
                    $parts = explode('.', $key);
                    $val = $row;
                    foreach ($parts as $part) {
                        $val = $val->{$part} ?? null;
                    }
                    $line[] = $val ?? '';
                } else {
                    $line[] = $row->{$key} ?? '';
                }
            }
            fputcsv($handle, $line);
        }

        fclose($handle);
        exit;
    }

    protected function getBaseQuery(string $module): Builder
    {
        switch ($module) {
            case 'reservations':
                return Reservation::query()
                    ->leftJoin('guests', 'reservations.guest_id', '=', 'guests.id')
                    ->leftJoin('units', 'reservations.unit_id', '=', 'units.id')
                    ->leftJoin('unit_types', 'units.unit_type_id', '=', 'unit_types.id')
                    ->leftJoin('sources', 'reservations.source_id', '=', 'sources.id');
            case 'finance':
                return Transaction::query()
                    ->leftJoin('users', 'transactions.created_by', '=', 'users.id');
            case 'rooms':
                return Unit::query()
                    ->leftJoin('unit_types', 'units.unit_type_id', '=', 'unit_types.id');
            case 'guests':
                return Guest::query()
                    ->leftJoin('countries', 'guests.nationality', '=', 'countries.id');
            case 'pos':
                return DB::table('pos_sales')
                    ->leftJoin('users', 'pos_sales.created_by', '=', 'users.id');
            default:
                throw new \InvalidArgumentException("Unknown module: {$module}");
        }
    }

    protected function applyFilters(Builder $query, string $module, array $filters): void
    {
        foreach ($filters as $field => $value) {
            if (is_null($value) || $value === '') continue;

            switch ($field) {
                case 'date_from':
                    $query->whereDate($module . '.created_at', '>=', $value);
                    break;
                case 'date_to':
                    $query->whereDate($module . '.created_at', '<=', $value);
                    break;
                case 'status':
                    is_array($value) ? $query->whereIn($module . '.status', $value) : $query->where($module . '.status', $value);
                    break;
                case 'check_in_from':
                    $query->whereDate('reservations.check_in', '>=', $value);
                    break;
                case 'check_in_to':
                    $query->whereDate('reservations.check_in', '<=', $value);
                    break;
                case 'check_out_from':
                    $query->whereDate('reservations.check_out', '>=', $value);
                    break;
                case 'check_out_to':
                    $query->whereDate('reservations.check_out', '<=', $value);
                    break;
                case 'amount_min':
                    $query->where('transactions.amount', '>=', $value);
                    break;
                case 'amount_max':
                    $query->where('transactions.amount', '<=', $value);
                    break;
                case 'kind':
                    $query->where($module . '.kind', $value);
                    break;
                case 'search':
                    $query->where(function ($q) use ($value, $module) {
                        $cols = ['name', 'code', 'first_name', 'last_name', 'unit_number', 'description'];
                        foreach ($cols as $c) {
                            if (in_array($c, ['first_name', 'last_name'])) {
                                $q->orWhere('guests.' . $c, 'LIKE', "%{$value}%");
                            } else {
                                $q->orWhere($module . '.' . $c, 'LIKE', "%{$value}%");
                            }
                        }
                    });
                    break;
                default:
                    if (!str_contains($field, '.')) {
                        $query->where($module . '.' . $field, $value);
                    }
                    break;
            }
        }
    }

    public function preview(string $module, array $columns, array $filters = [], ?string $sortBy = null, string $sortDirection = 'asc', ?string $groupBy = null)
    {
        $query = $this->getBaseQuery($module);
        $query->where($module . '.team_id', auth()->user()->team_id);

        $this->applyFilters($query, $module, $filters);

        if ($sortBy && !str_contains($sortBy, '.')) {
            $query->orderBy($sortBy, $sortDirection);
        }
        if ($groupBy) {
            $query->groupBy($groupBy);
        }

        $selects = [$module . '.id'];
        foreach ($columns as $col) {
            $key = $col['key'] ?? $col;
            if (!str_contains($key, '.')) {
                $selects[] = $module . '.' . $key;
            }
        }
        $query->select(array_unique($selects));

        return $query->take(50)->get();
    }
}