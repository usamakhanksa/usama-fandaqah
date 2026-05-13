<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\InvoiceTransfer;
use App\Models\Invoice;
use App\Services\Finance\InvoiceTransferService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class InvoiceTransferController extends Controller
{
    protected $transferService;

    public function __construct(InvoiceTransferService $transferService)
    {
        $this->transferService = $transferService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', InvoiceTransfer::class);

        $query = InvoiceTransfer::with(['fromInvoice', 'toInvoice', 'fromGuest', 'toGuest', 'fromCompany', 'toCompany'])
            ->where('team_id', Auth::user()->current_team_id);

        if ($request->search) {
            $query->where('transfer_number', 'like', "%{$request->search}%");
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $transfers = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Finance/InvoiceTransfers/Index', [
            'transfers' => $transfers,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', InvoiceTransfer::class);

        $fromInvoice = null;
        if ($request->from_invoice_id) {
            $fromInvoice = Invoice::with('items')->where('team_id', Auth::user()->current_team_id)->findOrFail($request->from_invoice_id);
        }

        $invoices = Invoice::where('team_id', Auth::user()->current_team_id)
            ->whereIn('status', ['confirmed', 'paid', 'partially_paid'])
            ->latest()
            ->take(20)
            ->get();

        return Inertia::render('Finance/InvoiceTransfers/Create', [
            'fromInvoice' => $fromInvoice,
            'invoices' => $invoices,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', InvoiceTransfer::class);

        $request->validate([
            'from_invoice_id' => 'required|exists:invoices,id',
            'to_invoice_id' => 'nullable|exists:invoices,id',
            'reason' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:invoice_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        $transfer = $this->transferService->createTransfer($request->all());

        return redirect()->route('finance.invoice-transfers.show', $transfer->id)
            ->with('success', 'Transfer request created successfully.');
    }

    public function show(InvoiceTransfer $invoiceTransfer)
    {
        $this->authorize('view', $invoiceTransfer);

        $invoiceTransfer->load([
            'fromInvoice', 'toInvoice', 
            'items.fromItem', 'items.toItem',
            'fromGuest', 'toGuest', 
            'fromCompany', 'toCompany', 
            'creator', 'approver'
        ]);

        return Inertia::render('Finance/InvoiceTransfers/Show', [
            'transfer' => $invoiceTransfer,
        ]);
    }

    public function approve(InvoiceTransfer $invoiceTransfer)
    {
        $this->authorize('approve', $invoiceTransfer);

        if ($invoiceTransfer->status !== 'pending') {
            return back()->with('error', 'Only pending transfers can be approved.');
        }

        $this->transferService->approveTransfer($invoiceTransfer);

        return back()->with('success', 'Transfer approved and completed.');
    }

    public function reject(Request $request, InvoiceTransfer $invoiceTransfer)
    {
        $this->authorize('reject', $invoiceTransfer);

        $request->validate(['reason' => 'required|string']);

        if ($invoiceTransfer->status !== 'pending') {
            return back()->with('error', 'Only pending transfers can be rejected.');
        }

        $this->transferService->rejectTransfer($invoiceTransfer, $request->reason);

        return back()->with('success', 'Transfer rejected.');
    }
}
