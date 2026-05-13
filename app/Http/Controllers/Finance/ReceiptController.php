<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Receipt;
use App\Models\Reservation;
use App\Models\Guest;
use App\Models\Company;
use App\Services\Finance\ReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ReceiptController extends Controller
{
    protected $receiptService;

    public function __construct(ReceiptService $receiptService)
    {
        $this->receiptService = $receiptService;
        // RBAC Middleware (except for index and show if needed, but requirements say except index/show)
        $this->middleware('permission:receipts.view')->only(['index', 'show']);
        $this->middleware('permission:receipts.create')->only(['create', 'store']);
        $this->middleware('permission:receipts.edit')->only(['edit', 'update']);
        $this->middleware('permission:receipts.delete')->only(['destroy']);
        $this->middleware('permission:receipts.cancel')->only(['cancel']);
        $this->middleware('permission:receipts.print')->only(['print']);
        $this->middleware('permission:receipts.export')->only(['export']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'status', 'payment_method', 'guest_id', 'reservation_id', 'search']);
        $receipts = $this->receiptService->list($filters, 25);

        return Inertia::render('Finance/Receipts/Index', [
            'receipts' => $receipts,
            'filters' => $filters,
            'stats' => $this->receiptService->getReceiptStats(Auth::user()->current_team_id, $request->date_from, $request->date_to)
        ]);
    }

    public function create()
    {
        $teamId = Auth::user()->current_team_id;
        return Inertia::render('Finance/Receipts/Create', [
            'reservations' => Reservation::where('team_id', $teamId)->get(['id', 'number']),
            'guests' => Guest::where('team_id', $teamId)->get(['id', 'name']),
            'companies' => Company::where('team_id', $teamId)->get(['id', 'name']),
            'paymentMethods' => ['cash', 'card', 'bank_transfer', 'cheque', 'online', 'other']
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'reservation_id' => 'nullable|exists:reservations,id',
            'guest_id' => 'nullable|exists:guests,id',
            'company_id' => 'nullable|exists:companies,id',
            'receipt_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,bank_transfer,cheque,online,other',
            'currency' => 'nullable|string|max:3',
            'exchange_rate' => 'nullable|numeric',
            'reference_number' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'cheque_number' => 'nullable|string',
            'card_last_four' => 'nullable|string|max:4',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:draft,confirmed',
        ]);

        $data['team_id'] = Auth::user()->current_team_id;
        $data['created_by'] = Auth::id();

        $receipt = $this->receiptService->createReceipt($data);

        return redirect()->route('finance.receipts.show', $receipt->id)
            ->with('success', 'Receipt created successfully.');
    }

    public function show($id)
    {
        $receipt = Receipt::with(['team', 'reservation', 'guest', 'company', 'createdBy', 'updatedBy', 'cancelledBy'])
            ->findOrFail($id);

        return Inertia::render('Finance/Receipts/Show', [
            'receipt' => $receipt
        ]);
    }

    public function edit($id)
    {
        $receipt = Receipt::findOrFail($id);

        if (!$receipt->canBeEdited()) {
            return redirect()->route('finance.receipts.show', $id)
                ->with('error', 'Only draft receipts can be edited.');
        }

        $teamId = Auth::user()->current_team_id;
        return Inertia::render('Finance/Receipts/Edit', [
            'receipt' => $receipt,
            'reservations' => Reservation::where('team_id', $teamId)->get(['id', 'number']),
            'guests' => Guest::where('team_id', $teamId)->get(['id', 'name']),
            'companies' => Company::where('team_id', $teamId)->get(['id', 'name']),
            'paymentMethods' => ['cash', 'card', 'bank_transfer', 'cheque', 'online', 'other']
        ]);
    }

    public function update(Request $request, $id)
    {
        $receipt = Receipt::findOrFail($id);

        if (!$receipt->canBeEdited()) {
            return redirect()->route('finance.receipts.show', $id)
                ->with('error', 'Only draft receipts can be updated.');
        }

        $data = $request->validate([
            'reservation_id' => 'nullable|exists:reservations,id',
            'guest_id' => 'nullable|exists:guests,id',
            'company_id' => 'nullable|exists:companies,id',
            'receipt_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,bank_transfer,cheque,online,other',
            'currency' => 'nullable|string|max:3',
            'exchange_rate' => 'nullable|numeric',
            'reference_number' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'cheque_number' => 'nullable|string',
            'card_last_four' => 'nullable|string|max:4',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:draft,confirmed',
        ]);

        $data['updated_by'] = Auth::id();
        $receipt->update($data);

        return redirect()->route('finance.receipts.show', $receipt->id)
            ->with('success', 'Receipt updated successfully.');
    }

    public function destroy($id)
    {
        $receipt = Receipt::findOrFail($id);
        $receipt->delete();

        return redirect()->route('finance.receipts.index')
            ->with('success', 'Receipt deleted successfully.');
    }

    public function cancel(Request $request, $id)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|min:5',
        ]);

        $receipt = Receipt::findOrFail($id);
        $this->receiptService->cancelReceipt($receipt, Auth::id(), $request->cancellation_reason);

        return redirect()->route('finance.receipts.show', $id)
            ->with('success', 'Receipt cancelled successfully.');
    }

    public function print($id)
    {
        $receipt = Receipt::with(['guest', 'reservation', 'team'])->findOrFail($id);
        return Inertia::render('Finance/Receipts/Print', [
            'receipt' => $receipt
        ]);
    }

    public function confirm($id)
    {
        $receipt = Receipt::findOrFail($id);
        
        if ($receipt->status !== 'draft') {
            return redirect()->back()->with('error', 'Only draft receipts can be confirmed.');
        }

        $receipt->update(['status' => 'confirmed']);
        
        // Also create transaction
        \App\Services\Finance\TransactionService::createFromReceipt($receipt);

        return redirect()->route('finance.receipts.show', $id)
            ->with('success', 'Receipt confirmed successfully.');
    }

    public function export(Request $request)
    {
        // Export logic (Excel/CSV)
        // This usually uses a package like Maatwebsite/Excel
        return response()->json(['message' => 'Exporting...']);
    }
}
