<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentCorrectionService;
use App\Transaction;
use Illuminate\Http\Request;

class PaymentCorrectionController extends Controller
{
    private PaymentCorrectionService $service;

    public function __construct(PaymentCorrectionService $service)
    {
        $this->service = $service;
    }

    /**
     * POST /api/finance/payment-correction
     *
     * Body:
     *   frozen_transaction_id  int      required
     *   correct_payment_type   string   required  (cash|mada|bank-transfer|credit)
     *   correct_amount         numeric  required  >0
     *   reason                 string   nullable
     */
    public function correct(Request $request)
    {
        // 1. Permission gate
        abort_if(
            ! $request->user()->hasPermissionTo('finance.payment_correction'),
            403,
            'You do not have permission to perform payment corrections.'
        );

        // 2. Validation
        $data = $request->validate([
            'frozen_transaction_id' => ['required', 'integer', 'exists:transactions,id'],
            'correct_payment_type'  => ['required', 'string', 'in:cash,mada,bank-transfer,credit'],
            'correct_amount'        => ['required', 'numeric', 'min:0.01'],
            'reason'                => ['nullable', 'string', 'max:500'],
        ]);

        // 3. Resolve the transaction and verify it belongs to the user's team
        $frozen = Transaction::findOrFail($data['frozen_transaction_id']);

        if ((int) $frozen->team_id !== (int) $request->user()->current_team_id) {
            abort(403, 'Transaction does not belong to your team.');
        }

        // 4. Delegate to service (also validates is_freezed)
        try {
            $log = $this->service->correct(
                frozen:             $frozen,
                correctPaymentType: $data['correct_payment_type'],
                correctAmount:      (float) $data['correct_amount'],
                reason:             $data['reason'] ?? null,
                actor:              $request->user()
            );
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment correction applied successfully.',
            'data'    => [
                'log_id'                => $log->id,
                'correction_type'       => $log->correction_type,
                'original_amount'       => $log->original_amount,
                'correct_amount'        => $log->correct_amount,
                'correction_withdraw_id' => $log->correction_withdraw_id,
                'correction_deposit_id'  => $log->correction_deposit_id,
                'posted_business_date'  => $log->posted_business_date,
            ],
        ], 201);
    }

    /**
     * GET /api/finance/payment-corrections
     * List correction history for the current team.
     */
    public function index(Request $request)
    {
        abort_if(
            ! $request->user()->hasPermissionTo('finance.payment_correction'),
            403
        );

        $teamId = $request->user()->current_team_id;

        $logs = \App\Models\PaymentCorrectionLog::where('team_id', $teamId)
            ->with(['creator:id,name', 'frozenTransaction:id,amount,meta,created_at'])
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json($logs);
    }
}
