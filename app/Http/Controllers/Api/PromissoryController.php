<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promissory;
use App\Models\PromissoryPaymentLog;
use App\Services\PromissoryService;
use Illuminate\Http\Request;

class PromissoryController extends Controller
{
    protected $service;

    public function __construct(PromissoryService $service)
    {
        $this->service = $service;
    }

    /**
     * List promissories with filters.
     */
    public function index(Request $request)
    {
        $query = Promissory::query()
            ->with(['reservation.guest', 'company', 'user'])
            ->where('team_id', $request->user()->current_team_id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('due_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('due_date', '<=', $request->date_to);
        }

        return $query->latest()->paginate(20);
    }

    /**
     * List payment logs with filters.
     */
    public function paymentLogs(Request $request)
    {
        $query = PromissoryPaymentLog::query()
            ->with(['promissory.reservation.guest', 'promissory.company', 'appliedBy'])
            ->where('team_id', $request->user()->current_team_id);

        if ($request->filled('promissory_id')) {
            $query->where('promissory_id', $request->promissory_id);
        }
        if ($request->filled('is_reversed')) {
            $query->where('is_reversed', $request->boolean('is_reversed'));
        }
        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }
        if ($request->filled('company_id')) {
            $query->whereHas('promissory', function($q) use ($request) {
                $q->where('company_id', $request->company_id);
            });
        }

        return $query->latest('applied_at')->paginate(20);
    }

    /**
     * Apply a new payment to a promissory.
     */
    public function applyPayment(Request $request, Promissory $promissory)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_type' => 'required|string',
            'notes' => 'nullable|string',
            'transaction_id' => 'nullable|integer',
        ]);

        $log = $this->service->applyPayment(
            $promissory,
            $data['amount'],
            $data['payment_type'],
            $data['transaction_id'] ?? null,
            $data['notes'] ?? null
        );

        return response()->json($log, 201);
    }

    /**
     * Reverse a payment log.
     */
    public function reversePayment(PromissoryPaymentLog $log)
    {
        try {
            $this->service->reversePayment($log);
            return response()->json(['message' => 'Payment reversed successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
