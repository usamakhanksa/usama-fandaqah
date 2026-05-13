<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\PromissoryNote;
use App\Models\Company;
use App\Models\Guest;
use App\Services\Finance\PromissoryNoteService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class PromissoryNoteController extends Controller
{
    protected $noteService;

    public function __construct(PromissoryNoteService $noteService)
    {
        $this->noteService = $noteService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', PromissoryNote::class);

        $query = PromissoryNote::with(['guest', 'company', 'creator'])
            ->where('team_id', Auth::user()->current_team_id);

        if ($request->search) {
            $query->where('promissory_number', 'like', "%{$request->search}%")
                  ->orWhere('signatory_name', 'like', "%{$request->search}%");
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->is_overdue) {
            $query->where('is_overdue', true);
        }

        $notes = $query->latest()->paginate(10)->withQueryString();
        
        $aging = $this->noteService->getAgingReport(Auth::user()->current_team_id);

        return Inertia::render('Finance/PromissoryNotes/Index', [
            'notes' => $notes,
            'filters' => $request->all(),
            'aging' => $aging,
            'stats' => [
                'total_outstanding' => PromissoryNote::where('team_id', Auth::user()->current_team_id)->whereIn('status', ['pending', 'partially_collected'])->sum('remaining_amount'),
                'overdue_count' => PromissoryNote::where('team_id', Auth::user()->current_team_id)->where('is_overdue', true)->count(),
                'collected_this_month' => \App\Models\PromissoryCollection::where('team_id', Auth::user()->current_team_id)->whereMonth('collection_date', now()->month)->sum('amount'),
            ]
        ]);
    }

    public function create()
    {
        $this->authorize('create', PromissoryNote::class);

        return Inertia::render('Finance/PromissoryNotes/Create', [
            'companies' => Company::where('team_id', Auth::user()->current_team_id)->get(['id', 'name']),
            'guests' => Guest::where('team_id', Auth::user()->current_team_id)->latest()->take(50)->get(['id', 'full_name']),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', PromissoryNote::class);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'due_date' => 'required|date|after_or_equal:today',
            'issue_date' => 'required|date',
            'signatory_name' => 'required|string',
            'signatory_id_number' => 'required|string',
            'company_id' => 'nullable|exists:companies,id',
            'guest_id' => 'nullable|exists:guests,id',
            'notes' => 'nullable|string',
        ]);

        $note = $this->noteService->createPromissoryNote($validated);

        return redirect()->route('finance.promissory-notes.show', $note->id)
            ->with('success', 'Promissory note created successfully.');
    }

    public function show(PromissoryNote $promissoryNote)
    {
        $this->authorize('view', $promissoryNote);

        $promissoryNote->load(['guest', 'company', 'collections.collector', 'logs.performer', 'creator']);

        return Inertia::render('Finance/PromissoryNotes/Show', [
            'note' => $promissoryNote,
        ]);
    }

    public function renew(Request $request, PromissoryNote $promissoryNote)
    {
        $this->authorize('renew', $promissoryNote);

        $request->validate(['due_date' => 'required|date|after:today']);

        $this->noteService->renewNote($promissoryNote, $request->due_date);

        return back()->with('success', 'Promissory note renewed.');
    }

    public function cancel(PromissoryNote $promissoryNote)
    {
        $this->authorize('cancel', $promissoryNote);

        if ($promissoryNote->collected_amount > 0) {
            return back()->with('error', 'Cannot cancel a note that has collections.');
        }

        $promissoryNote->update(['status' => 'cancelled']);

        return back()->with('success', 'Promissory note cancelled.');
    }
}
