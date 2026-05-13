<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\PromissoryNote;
use App\Models\PromissoryCollection;
use App\Services\Finance\PromissoryNoteService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class PromissoryCollectionController extends Controller
{
    protected $noteService;

    public function __construct(PromissoryNoteService $noteService)
    {
        $this->noteService = $noteService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', PromissoryCollection::class);

        $query = PromissoryCollection::with(['promissoryNote', 'collector'])
            ->where('team_id', Auth::user()->current_team_id);

        if ($request->search) {
            $query->whereHas('promissoryNote', function($q) use ($request) {
                $q->where('promissory_number', 'like', "%{$request->search}%");
            });
        }

        $collections = $query->latest()->paginate(15);

        return Inertia::render('Finance/PromissoryCollections/Index', [
            'collections' => $collections,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request, PromissoryNote $promissoryNote)
    {
        $this->authorize('collect', $promissoryNote);

        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $promissoryNote->remaining_amount,
            'collection_date' => 'required|date',
            'payment_method' => 'required|in:cash,card,bank_transfer,cheque',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $this->noteService->recordCollection($promissoryNote, $request->all());

        return back()->with('success', 'Collection recorded successfully.');
    }

    public function reverse(PromissoryCollection $promissoryCollection)
    {
        $this->authorize('reverse', $promissoryCollection);

        if ($promissoryCollection->status === 'reversed') {
            return back()->with('error', 'Collection already reversed.');
        }

        $this->noteService->reverseCollection($promissoryCollection);

        return back()->with('success', 'Collection reversed successfully.');
    }
}
