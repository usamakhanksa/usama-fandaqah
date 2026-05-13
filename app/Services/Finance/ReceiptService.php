<?php

namespace App\Services\Finance;

use App\Models\Receipt;
use App\Models\TeamCounter;
use App\Services\Finance\TransactionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReceiptService
{
    protected Receipt $model;

    public function __construct(Receipt $model)
    {
        $this->model = $model;
    }

    /**
     * List receipts with filters and pagination
     */
    public function list(array $filters = [], int $perPage = 25)
    {
        $query = $this->model->newQuery();

        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->byDateRange($filters['date_from'], $filters['date_to']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (!empty($filters['guest_id'])) {
            $query->where('guest_id', $filters['guest_id']);
        }

        if (!empty($filters['reservation_id'])) {
            $query->where('reservation_id', $filters['reservation_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                  ->orWhereHas('guest', function ($gq) use ($search) {
                      $gq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        return $query->with(['guest', 'reservation', 'createdBy'])
            ->orderBy('receipt_date', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create a new receipt and linked transaction
     */
    public function createReceipt(array $data): Receipt
    {
        return DB::transaction(function () use ($data) {
            $receipt = $this->model->create($data);

            if ($receipt->status === 'confirmed') {
                TransactionService::createFromReceipt($receipt);
            }

            return $receipt;
        });
    }

    /**
     * Cancel a receipt and reverse its transaction
     */
    public function cancelReceipt(Receipt $receipt, $userId, $reason): void
    {
        DB::transaction(function () use ($receipt, $userId, $reason) {
            $receipt->cancel($userId, $reason);
            TransactionService::reverseReceipt($receipt, $userId, $reason);
        });
    }

    /**
     * Generate receipt number with format: RCP-{YYYY}{MM}-{0001}
     */
    public function generateReceiptNumber($teamId): string
    {
        $prefix = 'RCP-' . now()->format('Ym') . '-';
        
        return DB::transaction(function () use ($teamId, $prefix) {
            $counter = TeamCounter::lockForUpdate()->firstOrCreate(
                [
                    'team_id' => $teamId,
                    'type' => 'receipt',
                    'prefix' => $prefix,
                ],
                ['value' => 0]
            );
            
            $counter->value += 1;
            $counter->save();
            
            return $prefix . str_pad($counter->value, 4, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Get receipt statistics
     */
    public function getReceiptStats($teamId, $dateFrom = null, $dateTo = null): array
    {
        $query = Receipt::where('team_id', $teamId)->confirmed();
        
        if ($dateFrom && $dateTo) {
            $query->byDateRange($dateFrom, $dateTo);
        }
        
        $receipts = $query->get();
        
        $byMethod = $receipts->groupBy('payment_method')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total' => $group->sum('amount'),
            ];
        });
        
        $dailyTotals = $receipts->groupBy(function ($receipt) {
            return $receipt->receipt_date->format('Y-m-d');
        })->map(function ($group) {
            return $group->sum('amount');
        });
        
        return [
            'total_count' => $receipts->count(),
            'total_amount' => $receipts->sum('amount'),
            'by_payment_method' => $byMethod,
            'daily_totals' => $dailyTotals,
        ];
    }

    /**
     * Validate receipt data
     */
    public function validateReceiptData(array $data): array
    {
        $errors = [];
        
        if (empty($data['amount']) || $data['amount'] <= 0) {
            $errors[] = 'Amount must be greater than zero';
        }
        
        $validMethods = ['cash', 'card', 'bank_transfer', 'cheque', 'online', 'other'];
        if (!in_array($data['payment_method'] ?? '', $validMethods)) {
            $errors[] = 'Invalid payment method';
        }
        
        return $errors;
    }
}
