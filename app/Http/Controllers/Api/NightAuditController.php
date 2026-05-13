<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NightAuditService;
use App\Services\NightAuditPreflightService;
use App\Services\NightAuditRerunService;
use App\Team;
use Illuminate\Http\Request;

class NightAuditController extends Controller
{
    protected $nightAudit;
    protected $preflight;
    protected $rerun;

    public function __construct(
        NightAuditService $nightAudit, 
        NightAuditPreflightService $preflight,
        NightAuditRerunService $rerun
    ) {
        $this->nightAudit = $nightAudit;
        $this->preflight = $preflight;
        $this->rerun = $rerun;
    }

    public function preflight(Request $request)
    {
        $team = Team::find($request->user()->current_team_id);
        return response()->json($this->preflight->check($team));
    }

    public function run(Request $request)
    {
        abort_if(! $request->user()->hasPermissionTo('run night-audit') && ! $request->user()->hasPermissionTo('run night audit'), 403);
        
        $team = Team::find($request->user()->current_team_id);
        
        try {
            $log = $this->nightAudit->run($team, 'manual', $request->user()->id);
            return response()->json([
                'success' => true,
                'message' => 'Night audit completed successfully.',
                'log' => $log
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function status(Request $request)
    {
        $teamId = $request->user()->current_team_id;
        $lastLog = \App\Models\NightAuditLog::where('team_id', $teamId)
            ->orderBy('id', 'desc')
            ->first();

        $history = \App\Models\NightAuditLog::where('team_id', $teamId)
            ->orderBy('business_date', 'desc')
            ->orderBy('run_number', 'desc')
            ->limit(30)
            ->get();

        return response()->json([
            'last_log' => $lastLog,
            'history' => $history,
            'business_date' => Team::find($teamId)->business_date
        ]);
    }

    public function rerun(Request $request, $logId)
    {
        $oldLog = \App\Models\NightAuditLog::findOrFail($logId);
        $user = $request->user();
        $team = Team::find($user->current_team_id);

        if ($oldLog->team_id !== $team->id) {
            abort(403);
        }

        $businessDate = \Carbon\Carbon::parse($oldLog->business_date);
        $currentDate = \Carbon\Carbon::parse($team->business_date);
        $daysDiff = $businessDate->diffInDays($currentDate);

        // Permission Checks
        if ($daysDiff >= 8) {
            abort_if(! $user->hasPermissionTo('rerun historical night audit') && ! $user->hasPermissionTo('rerun historical night-audit'), 403, 'Missing historical rerun permission');
        } else {
            abort_if(! $user->hasPermissionTo('rerun night audit') && ! $user->hasPermissionTo('rerun night-audit'), 403, 'Missing rerun permission');
        }

        try {
            $newLog = $this->rerun->rerun($oldLog, $user->id, $request->reason);
            return response()->json([
                'success' => true,
                'message' => 'Night audit rerun successfully.',
                'log' => $newLog
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function setInitialDate(Request $request)
    {
        $request->validate(['date' => 'required|date']);
        $team = Team::find($request->user()->current_team_id);
        
        if ($team->business_date) {
            return response()->json(['message' => 'Business date already set.'], 422);
        }

        $team->update(['business_date' => $request->date]);
        return response()->json(['message' => 'Business date initialized.', 'date' => $request->date]);
    }
}
