<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\Reports\DailyReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Mail;

class DailyReportController extends Controller
{
    protected $dailyReportService;

    public function __construct(DailyReportService $dailyReportService)
    {
        $this->dailyReportService = $dailyReportService;
        
        // Apply permissions
        $this->middleware('can:report.daily')->only(['index', 'generate']);
        $this->middleware('can:report.daily.export')->only(['export']);
        $this->middleware('can:report.daily.email')->only(['email']);
    }

    /**
     * Display the daily report page
     */
    public function index()
    {
        $this->authorize('report.daily');
        
        return Inertia::render('Reports/DailyReport', [
            'initialDate' => Carbon::today()->toDateString(),
            'permissions' => [
                'can_export' => auth()->user()->can('report.daily.export'),
                'can_email' => auth()->user()->can('report.daily.email'),
            ],
        ]);
    }

    /**
     * Generate daily report for specific date
     */
    public function generate(Request $request)
    {
        $this->authorize('report.daily');
        
        $request->validate([
            'date' => 'nullable|date',
        ]);

        $date = Carbon::parse($request->get('date', Carbon::today()));
        $teamId = auth()->user()->team_id;

        try {
            $report = $this->dailyReportService->generate($teamId, $date);
            return response()->json($report);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate report',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export daily report
     */
    public function export(Request $request)
    {
        $this->authorize('report.daily.export');
        
        $request->validate([
            'date' => 'nullable|date',
            'format' => 'required|in:pdf,excel',
        ]);

        $date = Carbon::parse($request->get('date', Carbon::today()));
        $format = $request->get('format', 'pdf');
        $teamId = auth()->user()->team_id;

        try {
            $report = $this->dailyReportService->generate($teamId, $date);
            return $this->dailyReportService->export($report, $format);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to export report',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Email daily report to recipients
     */
    public function email(Request $request)
    {
        $this->authorize('report.daily.email');
        
        $request->validate([
            'date' => 'nullable|date',
            'recipients' => 'required|array',
            'recipients.*' => 'email',
        ]);

        $date = Carbon::parse($request->get('date', Carbon::today()));
        $recipients = $request->get('recipients');
        $teamId = auth()->user()->team_id;

        try {
            $report = $this->dailyReportService->generate($teamId, $date);
            
            foreach ($recipients as $recipient) {
                Mail::send('emails.daily_report', ['report' => $report], function ($message) use ($recipient, $date) {
                    $message->to($recipient)
                        ->subject("Daily Report - {$date->toDateString()}");
                });
            }

            return response()->json([
                'message' => 'Report emailed successfully',
                'recipients_count' => count($recipients),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to email report',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Compare two dates
     */
    public function compare(Request $request)
    {
        $this->authorize('report.daily');
        
        $request->validate([
            'date1' => 'required|date',
            'date2' => 'required|date',
        ]);

        $date1 = Carbon::parse($request->get('date1'));
        $date2 = Carbon::parse($request->get('date2'));
        $teamId = auth()->user()->team_id;

        try {
            $report1 = $this->dailyReportService->generate($teamId, $date1);
            $report2 = $this->dailyReportService->generate($teamId, $date2);

            return response()->json([
                'date1' => $report1,
                'date2' => $report2,
                'comparison' => $this->calculateComparison($report1, $report2),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to compare reports',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate comparison metrics
     */
    protected function calculateComparison($report1, $report2)
    {
        return [
            'occupancy_change' => $report2['occupancy']['occupancy_rate'] - $report1['occupancy']['occupancy_rate'],
            'revenue_change' => $report2['total_revenue']['total'] - $report1['total_revenue']['total'],
            'adr_change' => $report2['adr_revpar']['adr'] - $report1['adr_revpar']['adr'],
            'revpar_change' => $report2['adr_revpar']['revpar'] - $report1['adr_revpar']['revpar'],
        ];
    }
}
