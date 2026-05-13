<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\QoyodSyncLog;
use App\Services\Finance\QoyodService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QoyodSyncController extends Controller
{
    protected $qoyodService;

    public function __construct(QoyodService $qoyodService)
    {
        $this->qoyodService = $qoyodService;
    }

    public function index()
    {
        $teamId = auth()->user()->current_team_id;
        $logs = QoyodSyncLog::where('team_id', $teamId)->latest()->paginate(20);
        $status = $this->qoyodService->getSyncStatus($teamId);

        return Inertia::render('Finance/Qoyod/Index', [
            'logs' => $logs,
            'lastStatus' => $status,
        ]);
    }

    public function sync(Request $request)
    {
        $request->validate([
            'type' => 'required|in:invoices,payments,credit_notes,accounts,full',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $teamId = auth()->user()->current_team_id;
        $userId = auth()->id();

        switch ($request->type) {
            case 'invoices':
                $this->qoyodService->syncInvoices($teamId, $request->from, $request->to, $userId);
                break;
            case 'payments':
                $this->qoyodService->syncPayments($teamId, $request->from, $request->to, $userId);
                break;
            case 'credit_notes':
                $this->qoyodService->syncCreditNotes($teamId, $request->from, $request->to, $userId);
                break;
            case 'accounts':
                $this->qoyodService->syncAccounts($teamId, $userId);
                break;
            case 'full':
                $this->qoyodService->fullSync($teamId, $userId);
                break;
        }

        return redirect()->back()->with('success', 'Sync triggered successfully.');
    }
}
