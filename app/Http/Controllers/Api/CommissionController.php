<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommissionPayment;
use App\Services\CommissionService;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CommissionController extends Controller
{
    protected $service;

    public function __construct(CommissionService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $this->authorize('commission.manage');

        $data = QueryBuilder::for(CommissionPayment::class)
            ->with(['source', 'reservation.customer', 'approver'])
            ->allowedFilters([
                'status',
                AllowedFilter::exact('source_id'),
                AllowedFilter::scope('period_from'),
                AllowedFilter::scope('period_to'),
            ])
            ->defaultSort('-created_at')
            ->where('team_id', auth()->user()->current_team_id)
            ->paginate($request->get('per_page', 15));

        return response()->json($data);
    }

    public function summary(Request $request)
    {
        $this->authorize('commission.manage');
        
        $summary = $this->service->getMonthlySummary(
            auth()->user()->current_team_id,
            $request->get('year'),
            $request->get('month')
        );

        return response()->json(['data' => $summary]);
    }

    public function approve(CommissionPayment $payment)
    {
        $this->authorize('commission.manage');

        try {
            $payment = $this->service->approveCommission($payment, auth()->id());
            return response()->json(['message' => 'Commission approved successfully', 'data' => $payment]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function pay(Request $request, CommissionPayment $payment)
    {
        $this->authorize('commission.manage');

        $request->validate([
            'payment_reference' => 'required|string',
            'paid_at' => 'nullable|date'
        ]);

        try {
            $payment = $this->service->markAsPaid(
                $payment, 
                $request->payment_reference,
                $request->paid_at
            );
            return response()->json(['message' => 'Commission marked as paid', 'data' => $payment]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function cancel(CommissionPayment $payment)
    {
        $this->authorize('commission.manage');

        if ($payment->status === 'paid') {
            return response()->json(['message' => 'Cannot cancel a paid commission.'], 422);
        }

        $payment->update(['status' => 'cancelled']);
        return response()->json(['message' => 'Commission cancelled successfully']);
    }
}
