<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    protected DashboardService $service;

    public function __construct(DashboardService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        return view('app');
    }

    public function occupancy(Request $request)
    {
        $this->authorize('dashboard.view');
        
        $data = $this->service->getOccupancyData($request->user()->currentTeam);
        
        return response()->json($data);
    }

    public function revenue(Request $request)
    {
        $this->authorize('dashboard.view');
        
        $data = $this->service->getRevenueData($request->user()->currentTeam);
        
        return response()->json($data);
    }

    public function frontDesk(Request $request)
    {
        $this->authorize('front-desk.view');
        
        $data = $this->service->getFrontDeskData($request->user()->currentTeam);
        
        return response()->json($data);
    }

    public function housekeeping(Request $request)
    {
        $this->authorize('housekeeping.view');
        
        $data = $this->service->getHousekeepingData($request->user()->currentTeam);
        
        return response()->json($data);
    }

    public function finance(Request $request)
    {
        $this->authorize('dashboard.finance');
        
        $data = $this->service->getFinanceData($request->user()->currentTeam);
        
        return response()->json($data);
    }

    public function nightAudit(Request $request)
    {
        $this->authorize('night-audit.view');
        
        $data = $this->service->getNightAuditData($request->user()->currentTeam);
        
        return response()->json($data);
    }

    public function integrationHealth(Request $request)
    {
        $this->authorize('integrations.view');
        
        $data = $this->service->getIntegrationHealthData($request->user()->currentTeam);
        
        return response()->json($data);
    }
}
