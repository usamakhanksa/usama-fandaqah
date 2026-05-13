<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\CashierShift;
use App\Services\Finance\CashierShiftService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CashierShiftController extends Controller
{
    protected $shiftService;

    public function __construct(CashierShiftService $shiftService)
    {
        $this->shiftService = $shiftService;
    }

    public function index(Request $request)
    {
        $teamId = Auth::user()->current_team_id;
        
        $query = CashierShift::with(['cashier', 'approvedBy'])
            ->where('team_id', $teamId);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->cashier_id) {
            $query->where('user_id', $request->cashier_id);
        }

        if ($request->date_from) {
            $query->whereDate('opened_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('opened_at', '<=', $request->date_to);
        }

        $shifts = $query->orderBy('opened_at', 'desc')->paginate(15)->withQueryString();

        $stats = [
            'open_shifts' => CashierShift::where('team_id', $teamId)->where('status', 'open')->count(),
            'pending_approval' => CashierShift::where('team_id', $teamId)->where('status', 'pending_approval')->count(),
            'today_shifts' => CashierShift::where('team_id', $teamId)->whereDate('opened_at', today())->count(),
        ];

        return Inertia::render('Finance/CashierShifts/Index', [
            'shifts' => $shifts,
            'filters' => $request->all(['status', 'cashier_id', 'date_from', 'date_to']),
            'stats' => $stats
        ]);
    }

    public function open()
    {
        $teamId = Auth::user()->current_team_id;
        $currentShift = $this->shiftService->getCurrentShift(Auth::id(), $teamId);

        return Inertia::render('Finance/CashierShifts/Open', [
            'currentShift' => $currentShift
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'opening_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        try {
            $this->shiftService->openShift(Auth::id(), Auth::user()->current_team_id, $request->opening_balance, $request->notes);
            return redirect()->route('finance.cashier-shifts.index')->with('success', 'Shift opened successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $shift = CashierShift::with(['cashier', 'transactions', 'approvedBy', 'rejectedBy'])
            ->findOrFail($id);

        return Inertia::render('Finance/CashierShifts/Show', [
            'shift' => $shift
        ]);
    }

    public function close(Request $request, $id)
    {
        $shift = CashierShift::findOrFail($id);
        
        $request->validate([
            'actual_closing_balance' => 'required|numeric',
            'variance_reason' => 'required_if:has_variance,true|string|nullable',
            'notes' => 'nullable|string'
        ]);

        try {
            $this->shiftService->closeShift($shift, $request->actual_closing_balance, $request->variance_reason);
            return redirect()->route('finance.cashier-shifts.show', $id)->with('success', 'Shift closed and pending approval.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function approve(Request $request, $id)
    {
        $shift = CashierShift::findOrFail($id);
        
        try {
            $this->shiftService->approveShift($shift, Auth::id(), $request->notes);
            return back()->with('success', 'Shift approved successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function reject(Request $request, $id)
    {
        $shift = CashierShift::findOrFail($id);
        
        $request->validate([
            'rejection_reason' => 'required|string'
        ]);

        try {
            $this->shiftService->rejectShift($shift, Auth::id(), $request->rejection_reason);
            return back()->with('success', 'Shift rejected.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function myShift()
    {
        $teamId = Auth::user()->current_team_id;
        $shift = $this->shiftService->getCurrentShift(Auth::id(), $teamId);

        if (!$shift) {
            return redirect()->route('finance.cashier-shifts.open');
        }

        return redirect()->route('finance.cashier-shifts.show', $shift->id);
    }

    public function report($id)
    {
        $shift = CashierShift::findOrFail($id);
        $report = $this->shiftService->getShiftReport($shift);

        return Inertia::render('Finance/CashierShifts/Report', [
            'report' => $report
        ]);
    }
}
