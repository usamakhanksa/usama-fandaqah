<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\Finance\InvoiceService;
use App\Services\Finance\ZatcaService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Redirect;

class InvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status', 'zatca_status', 'date_from', 'date_to', 'guest_id', 'company_id', 'reservation_id']);
        
        $invoices = Invoice::with(['guest', 'company', 'reservation'])
            ->byTeam(auth()->user()->current_team_id)
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where('invoice_number', 'like', "%{$search}%");
            })
            ->when($filters['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($filters['zatca_status'] ?? null, function ($query, $status) {
                $query->where('zatca_status', $status);
            })
            ->orderBy('invoice_date', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Finance/Invoices/Index', [
            'invoices' => $invoices,
            'filters' => $filters,
            'stats' => $this->invoiceService->getInvoiceStats(auth()->user()->current_team_id),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Finance/Invoices/Create', [
            'guests' => \App\Guest::all(['id', 'name']),
            'companies' => \App\Company::all(['id', 'name']),
            'reservations' => \App\Reservation::all(['id', 'reservation_number']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'team_id' => 'required',
            'zatca_invoice_type' => 'required|in:standard,simplified',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $invoice = $this->invoiceService->createInvoice($request->all(), $request->items);

        return Redirect::route('finance.invoices.show', $invoice->id)->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice): Response
    {
        return Inertia::render('Finance/Invoices/Show', [
            'invoice' => $invoice->load(['items', 'taxes', 'guest', 'company', 'reservation']),
        ]);
    }

    public function edit(Invoice $invoice): Response
    {
        if (!$invoice->isEditable()) {
            return Redirect::back()->with('error', 'Only draft invoices can be edited.');
        }

        return Inertia::render('Finance/Invoices/Edit', [
            'invoice' => $invoice->load('items'),
            'guests' => \App\Guest::all(['id', 'name']),
            'companies' => \App\Company::all(['id', 'name']),
        ]);
    }

    public function update(Request $request, Invoice $invoice)
    {
        if (!$invoice->isEditable()) {
            return Redirect::back()->with('error', 'Only draft invoices can be edited.');
        }

        $invoice->update($request->all());
        
        // Update items if provided
        if ($request->has('items')) {
            $invoice->items()->delete();
            foreach ($request->items as $index => $item) {
                InvoiceItem::create(array_merge($item, [
                    'invoice_id' => $invoice->id,
                    'team_id' => $invoice->team_id,
                    'sort_order' => $index,
                ]));
            }
        }

        $this->invoiceService->updateInvoiceTotals($invoice);

        return Redirect::route('finance.invoices.show', $invoice->id)->with('success', 'Invoice updated successfully.');
    }

    public function destroy(Invoice $invoice)
    {
        if (!$invoice->isEditable()) {
            return Redirect::back()->with('error', 'Only draft invoices can be deleted.');
        }

        $invoice->delete();
        return Redirect::route('finance.invoices.index')->with('success', 'Invoice deleted.');
    }

    public function sendToZatca(Invoice $invoice)
    {
        $this->invoiceService->submitToZatca($invoice);
        return Redirect::back()->with('success', 'Invoice submitted to ZATCA.');
    }

    public function downloadXml(Invoice $invoice)
    {
        return response($invoice->zatca_xml, 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="invoice-' . $invoice->invoice_number . '.xml"',
        ]);
    }

    public function downloadPdf(Invoice $invoice)
    {
        // Placeholder for PDF generation
        return Redirect::back()->with('info', 'PDF generation feature coming soon.');
    }

    public function print(Invoice $invoice)
    {
        return view('print.invoice', ['invoice' => $invoice->load(['items', 'taxes'])]);
    }

    public function cancel(Request $request, Invoice $invoice)
    {
        $request->validate(['reason' => 'required|string']);
        $this->invoiceService->cancelInvoice($invoice, auth()->id(), $request->reason);
        return Redirect::back()->with('success', 'Invoice cancelled.');
    }

    public function markAsPaid(Invoice $invoice)
    {
        $invoice->markAsPaid();
        return Redirect::back()->with('success', 'Invoice marked as paid.');
    }

    public function export(Request $request)
    {
        // Implementation for Excel export
        return Redirect::back()->with('info', 'Export feature coming soon.');
    }
}
