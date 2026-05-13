<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Services\Finance\CreditNoteService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CreditNoteController extends Controller
{
    protected $creditNoteService;

    public function __construct(CreditNoteService $creditNoteService)
    {
        $this->creditNoteService = $creditNoteService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', CreditNote::class);

        $query = CreditNote::with(['invoice', 'guest', 'company'])
            ->where('team_id', Auth::user()->current_team_id);

        if ($request->search) {
            $query->where('credit_note_number', 'like', "%{$request->search}%")
                ->orWhereHas('invoice', function($q) use ($request) {
                    $q->where('invoice_number', 'like', "%{$request->search}%");
                });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $creditNotes = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Finance/CreditNotes/Index', [
            'creditNotes' => $creditNotes,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', CreditNote::class);

        $invoiceId = $request->invoice_id;
        $invoice = null;
        if ($invoiceId) {
            $invoice = Invoice::with('items')->where('team_id', Auth::user()->current_team_id)->findOrFail($invoiceId);
        }

        $invoices = Invoice::where('team_id', Auth::user()->current_team_id)
            ->whereIn('status', ['confirmed', 'paid', 'partially_paid'])
            ->latest()
            ->take(20)
            ->get();

        return Inertia::render('Finance/CreditNotes/Create', [
            'invoice' => $invoice,
            'invoices' => $invoices,
            'reasons' => ['cancellation', 'correction', 'discount', 'partial_refund', 'other'],
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', CreditNote::class);

        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'reason' => 'required|string',
            'reason_description' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:invoice_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        $invoice = Invoice::where('team_id', Auth::user()->current_team_id)->findOrFail($request->invoice_id);

        // Check if full or partial
        $allInvoiceItemsCount = $invoice->items()->count();
        $selectedItemsCount = count($request->items);
        
        $isFull = ($allInvoiceItemsCount === $selectedItemsCount);
        // Additional check: are quantities same?
        if ($isFull) {
            foreach ($request->items as $selection) {
                $item = $invoice->items->find($selection['id']);
                if ($item->quantity != $selection['quantity']) {
                    $isFull = false;
                    break;
                }
            }
        }

        $overrides = [
            'reason' => $request->reason,
            'reason_description' => $request->reason_description,
            'notes' => $request->notes,
        ];

        if ($isFull) {
            $creditNote = $this->creditNoteService->createFromInvoice($invoice, $overrides);
        } else {
            $creditNote = $this->creditNoteService->createPartialCreditNote($invoice, $request->items, $overrides);
        }

        // Process immediately (create reversal transaction)
        $this->creditNoteService->processCreditNote($creditNote);

        return redirect()->route('finance.credit-notes.show', $creditNote->id)
            ->with('success', 'Credit Note created successfully.');
    }

    public function show(CreditNote $creditNote)
    {
        $this->authorize('view', $creditNote);

        $creditNote->load(['invoice', 'items', 'guest', 'company', 'reservation', 'transaction', 'creator']);

        return Inertia::render('Finance/CreditNotes/Show', [
            'creditNote' => $creditNote,
        ]);
    }

    public function submitToZatca(CreditNote $creditNote)
    {
        $this->authorize('zatca_submit', $creditNote);

        try {
            $response = $this->creditNoteService->submitToZatca($creditNote);
            return back()->with('success', 'Credit Note submitted to ZATCA successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'ZATCA Submission failed: ' . $e->getMessage());
        }
    }

    public function downloadXml(CreditNote $creditNote)
    {
        $this->authorize('zatca_download', $creditNote);
        
        return response($creditNote->zatca_xml, 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="CN-' . $creditNote->credit_note_number . '.xml"',
        ]);
    }

    public function cancel(CreditNote $creditNote)
    {
        $this->authorize('cancel', $creditNote);

        if ($creditNote->status === 'confirmed') {
            // Cannot cancel if already confirmed/processed in some systems, 
            // but here we might allow it if not reported to ZATCA yet
            if ($creditNote->is_zatca_reported) {
                return back()->with('error', 'Cannot cancel a reported credit note.');
            }

            DB::transaction(function() use ($creditNote) {
                $creditNote->update(['status' => 'cancelled']);
                if ($creditNote->transaction) {
                    $creditNote->transaction->delete(); // Or reverse it
                }
            });
        }

        return back()->with('success', 'Credit Note cancelled.');
    }
}
