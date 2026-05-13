<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InvoiceTransferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class InvoiceTransferController extends Controller
{
    use AuthorizesRequests;

    protected $service;

    public function __construct(InvoiceTransferService $service)
    {
        $this->service = $service;
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', \App\Models\InvoiceTransfer::class);
        return $this->service->getTransfers($request->all());
    }

    public function export(Request $request)
    {
        $this->authorize('viewAny', \App\Models\InvoiceTransfer::class);
        $exportService = app(\App\Services\ExportService::class);
        
        return $exportService->exportToCsv(
            'invoice_transfers',
            ['Date', 'By', 'Reservation', 'Guest', 'Company', 'Amount', 'Notes'],
            function () use ($request) {
                $query = \App\Models\InvoiceTransfer::with(['reservation', 'company', 'transferredBy'])
                    ->filter($request->all());
                
                foreach ($query->cursor() as $t) {
                    yield [
                        $t->transferred_at?->toDateString() ?? $t->created_at?->toDateString(),
                        $t->transferredBy?->name ?? 'System',
                        $t->reservation?->code,
                        $t->reservation?->guest?->name,
                        $t->company?->name,
                        $t->amount,
                        $t->notes
                    ];
                }
            }
        );
    }

    public function store(Request $request)
    {
        // Simple permission check for now as we haven't implemented full policies for this new model
        if (!Auth::user()->hasPermissionTo('ar.invoice_transfer')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'company_id' => 'required|exists:companies,id',
            'amount' => 'required|numeric|min:0.01',
            'invoice_id' => 'nullable',
            'notes' => 'nullable|string'
        ]);

        try {
            $transfer = $this->service->transferToAR($validated, Auth::id());
            return response()->json([
                'message' => 'Invoice successfully transferred to AR',
                'data' => $transfer
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
