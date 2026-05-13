<?php

namespace App\Http\Controllers\NightAudit;

use App\Http\Controllers\Controller;
use App\Services\NightAudit\NightAuditService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NightAuditController extends Controller
{
    protected NightAuditService $service;

    public function __construct(NightAuditService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $this->authorize('night-audit.view');
        $data = $this->service->getAuditData($request->user()->currentTeam);
        return Inertia::render('NightAudit/Index', $data);
    }

    public function run(Request $request)
    {
        $this->authorize('night-audit.run');
        $result = $this->service->runAudit($request->user()->currentTeam);
        return response()->json(['message' => 'Night audit completed', 'result' => $result]);
    }

    public function rerun(Request $request, $run)
    {
        $this->authorize('night-audit.run');
        $result = $this->service->rerunAudit($run);
        return response()->json(['message' => 'Night audit rerun completed', 'result' => $result]);
    }

    public function history(Request $request)
    {
        $this->authorize('night-audit.view');
        $history = $this->service->getHistory($request->user()->currentTeam);
        return Inertia::render('NightAudit/History', ['history' => $history]);
    }

    public function details(Request $request, $run)
    {
        $this->authorize('night-audit.view');
        $details = $this->service->getRunDetails($run);
        return Inertia::render('NightAudit/Details', ['details' => $details]);
    }
}
