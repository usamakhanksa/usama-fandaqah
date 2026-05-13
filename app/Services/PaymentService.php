<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Transaction;
use App\Models\TeamCounter;
use App\Exports\PaymentsExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class PaymentService
{
    protected Payment $model;

    public function __construct(Payment $model)
    {
        $this->model = $model;
    }

    /**
     * List payments with filters and pagination
     */
    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Apply team scope
        if (!empty($filters['team_id'])) {
            $query->byTeam($filters['team_id']);
        }

        // Apply status filter
        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        // Apply date range filter
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->byDateRange($filters['date_from'], $filters['date_to']);
        }

        // Apply payment method filter
        if (!empty($filters['payment_method'])) {
            $query->byPaymentMethod($filters['payment_method']);
        }

        // Apply guest filter
        if (!empty($filters['guest_id'])) {
            $query->byGuest($filters['guest_id']);
        }

        // Apply reservation filter
        if (!empty($filters['reservation_id'])) {
            $query->byReservation($filters['reservation_id']);
        }

        // Apply cashier filter
        if (!empty($filters['cashier_id'])) {
            $query->byCashier($filters['cashier_id']);
        }

        // Apply shift filter
        if (!empty($filters['shift_id'])) {
            $query->byShift($filters['shift_id']);
        }

        // Apply deposit/advance filters
        if (isset($filters['is_deposit'])) {
            if ($filters['is_deposit']) {
                $query->deposits();
            } else {
                $query->where('is_deposit', false);
            }
        }

        if (isset($filters['is_advance'])) {
            if ($filters['is_advance']) {
                $query->advances();
            } else {
                $query->where('is_advance', false);
            }
        }

        // Apply search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                  ->orWhereHas('guest', function ($gq) use ($search) {
                      $gq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('company', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Eager load relationships
        $query->with(['guest', 'company', 'reservation', 'cashier', 'transaction', 'bank', 'reversal']);

        // Log activity
        activity()
            ->withProperties(['filters' => $filters])
            ->log('payments.list');

        return $query->orderBy('payment_date', 'desc')->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get a single payment by ID
     */
    public function get(int $id): ?Payment
    {
        $payment = $this->model
            ->with(['guest', 'company', 'reservation', 'cashier', 'transaction', 'bank', 
                   'reversal', 'reversals', 'creator', 'updater', 'reverser'])
            ->find($id);

        if ($payment) {
            activity()
                ->performedOn($payment)
                ->log('payment.viewed');
        }

        return $payment;
    }

    /**
     * Create a new payment
     */
    public function create(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            // Calculate amount_base if not provided
            if (empty($data['amount_base']) && !empty($data['amount']) && !empty($data['exchange_rate'])) {
                $data['amount_base'] = round((float)$data['amount'] * (float)$data['exchange_rate'], 2);
            }

            $payment = $this->model->create($data);

            // Create linked transaction
            $transaction = Transaction::create([
                'team_id' => $payment->team_id,
                'payment_id' => $payment->id,
                'reservation_id' => $payment->reservation_id,
                'guest_id' => $payment->guest_id,
                'transaction_number' => 'TXN-' . now()->format('Ymd') . '-' . str_pad($payment->id, 4, '0', STR_PAD_LEFT),
                'transaction_date' => $payment->payment_date,
                'type' => 'credit',
                'category' => 'payment',
                'amount' => $payment->amount_base,
                'currency' => $payment->currency,
                'exchange_rate' => $payment->exchange_rate,
                'description' => $payment->description ?? 'Payment ' . $payment->payment_number,
                'status' => $payment->status === 'completed' ? 'completed' : 'pending',
                'created_by' => $payment->created_by,
            ]);

            // Link transaction to payment
            $payment->update(['transaction_id' => $transaction->id]);

            // Log activity
            activity()
                ->performedOn($payment)
                ->withProperties(['amount' => $payment->amount, 'status' => $payment->status])
                ->log('payment.created');

            return $payment;
        });
    }

    /**
     * Update a payment (only if pending)
     */
    public function update(int $id, array $data): Payment
    {
        return DB::transaction(function () use ($id, $data) {
            $payment = $this->model->findOrFail($id);

            if (!$payment->isEditable()) {
                throw new \Exception('Only pending payments can be updated');
            }

            // Recalculate amount_base if amount or exchange_rate changed
            if (isset($data['amount']) || isset($data['exchange_rate'])) {
                $amount = (float) ($data['amount'] ?? $payment->amount);
                $exchangeRate = (float) ($data['exchange_rate'] ?? $payment->exchange_rate);
                $data['amount_base'] = round($amount * $exchangeRate, 2);
            }

            $payment->update($data);

            // Update linked transaction if exists
            if ($payment->transaction) {
                $payment->transaction->update([
                    'amount' => $payment->amount_base,
                    'currency' => $payment->currency,
                    'exchange_rate' => $payment->exchange_rate,
                    'description' => $payment->description ?? 'Payment ' . $payment->payment_number,
                ]);
            }

            // Log activity
            activity()
                ->performedOn($payment)
                ->withProperties($data)
                ->log('payment.updated');

            return $payment;
        });
    }

    /**
     * Delete a payment (only if pending)
     */
    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $payment = $this->model->findOrFail($id);

            if (!$payment->isEditable()) {
                throw new \Exception('Only pending payments can be deleted');
            }

            // Delete linked transaction
            if ($payment->transaction) {
                $payment->transaction->delete();
            }

            // Log activity before deletion
            activity()
                ->performedOn($payment)
                ->withProperties(['payment_number' => $payment->payment_number])
                ->log('payment.deleted');

            return $payment->delete();
        });
    }

    /**
     * Complete a pending payment
     */
    public function complete(int $id): Payment
    {
        return DB::transaction(function () use ($id) {
            $payment = $this->model->findOrFail($id);

            $payment->complete();

            // Update linked transaction
            if ($payment->transaction) {
                $payment->transaction->update(['status' => 'completed']);
            }

            // Log activity
            activity()
                ->performedOn($payment)
                ->withProperties(['status' => 'completed'])
                ->log('payment.completed');

            return $payment;
        });
    }

    /**
     * Reverse a completed payment
     */
    public function reverse(int $id, string $reason, int $userId): Payment
    {
        return DB::transaction(function () use ($id, $reason, $userId) {
            $payment = $this->model->findOrFail($id);

            // Create reversal payment
            $reversalPayment = $this->model->create([
                'team_id' => $payment->team_id,
                'reservation_id' => $payment->reservation_id,
                'guest_id' => $payment->guest_id,
                'company_id' => $payment->company_id,
                'payment_number' => $this->generatePaymentNumber($payment->team_id),
                'payment_date' => now()->format('Y-m-d'),
                'payment_time' => now()->format('H:i'),
                'amount' => -$payment->amount, // Negative amount for reversal
                'currency' => $payment->currency,
                'exchange_rate' => $payment->exchange_rate,
                'amount_base' => -$payment->amount_base,
                'payment_method' => $payment->payment_method,
                'description' => 'Reversal of ' . $payment->payment_number,
                'notes' => 'Reversal reason: ' . $reason,
                'status' => 'completed',
                'reversal_id' => $payment->id,
                'created_by' => $userId,
            ]);

            // Create reversal transaction
            $reversalTransaction = Transaction::create([
                'team_id' => $payment->team_id,
                'payment_id' => $reversalPayment->id,
                'reservation_id' => $payment->reservation_id,
                'guest_id' => $payment->guest_id,
                'transaction_number' => 'TXN-' . now()->format('Ymd') . '-' . str_pad($reversalPayment->id, 4, '0', STR_PAD_LEFT),
                'transaction_date' => now()->format('Y-m-d'),
                'type' => 'debit',
                'category' => 'payment_reversal',
                'amount' => abs($reversalPayment->amount_base),
                'currency' => $payment->currency,
                'exchange_rate' => $payment->exchange_rate,
                'description' => 'Reversal of payment ' . $payment->payment_number,
                'status' => 'completed',
                'created_by' => $userId,
            ]);

            // Link transaction to reversal payment
            $reversalPayment->update(['transaction_id' => $reversalTransaction->id]);

            // Update original payment
            $payment->reverse($reason, $userId);

            // Update original transaction
            if ($payment->transaction) {
                $payment->transaction->update(['status' => 'reversed']);
            }

            // Log activity
            activity()
                ->performedOn($payment)
                ->withProperties(['reason' => $reason, 'reversal_id' => $reversalPayment->id])
                ->log('payment.reversed');

            return $payment;
        });
    }

    /**
     * Get shift summary
     */
    public function getShiftSummary(int $shiftId): array
    {
        $payments = $this->model
            ->byShift($shiftId)
            ->with(['cashier'])
            ->get();

        $byMethod = $payments->groupBy('payment_method')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total' => $group->sum('amount_base'),
                'cashier' => $group->first()->cashier?->name,
            ];
        });

        return [
            'shift_id' => $shiftId,
            'total_count' => $payments->count(),
            'total_amount' => $payments->sum('amount_base'),
            'by_payment_method' => $byMethod,
        ];
    }

    /**
     * Get daily summary
     */
    public function getDailySummary(string $date, int $teamId): array
    {
        $payments = $this->model
            ->byTeam($teamId)
            ->whereDate('payment_date', $date)
            ->with(['cashier'])
            ->get();

        $byMethod = $payments->groupBy('payment_method')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total' => $group->sum('amount_base'),
            ];
        });

        return [
            'date' => $date,
            'total_count' => $payments->count(),
            'total_amount' => $payments->sum('amount_base'),
            'by_payment_method' => $byMethod,
        ];
    }

    /**
     * Get payments by reservation
     */
    public function getByReservation(int $reservationId): Collection
    {
        return $this->model
            ->byReservation($reservationId)
            ->with(['guest', 'cashier', 'transaction'])
            ->orderBy('payment_date', 'desc')
            ->get();
    }

    /**
     * Export payments
     */
    public function export(array $filters, string $format = 'excel')
    {
        $teamId = $filters['team_id'] ?? auth()->user()?->current_team_id;

        activity()
            ->withProperties(['filters' => $filters, 'format' => $format])
            ->log('payments.export');

        if ($format === 'excel') {
            return Excel::download(
                new PaymentsExport($teamId, $filters),
                'payments_' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        // PDF export can be added here
        throw new \Exception('Unsupported export format: ' . $format);
    }

    /**
     * Generate payment number with format: PAY-YYYYMMDD-XXXX
     */
    public function generatePaymentNumber($teamId): string
    {
        $prefix = 'PAY-' . now()->format('Ymd') . '-';
        
        return DB::transaction(function () use ($teamId, $prefix) {
            // Use team_counters table for thread-safe auto-increment
            $counter = TeamCounter::lockForUpdate()->firstOrCreate(
                [
                    'team_id' => $teamId,
                    'type' => 'payment',
                    'prefix' => $prefix,
                ],
                ['value' => 0]
            );
            
            $counter->value += 1;
            $counter->save();
            
            return $prefix . str_pad($counter->value, 4, '0', STR_PAD_LEFT);
        });
    }
}
