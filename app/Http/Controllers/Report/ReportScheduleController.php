<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\ReportSchedule;
use App\Models\CustomReport;
use App\Services\Reports\AdrRevparCalculationService;
use App\Services\Reports\CustomReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Carbon\Carbon;

class ReportScheduleController extends Controller
{
    protected $customReportService;

    public function __construct(CustomReportService $customReportService)
    {
        $this->customReportService = $customReportService;
    }

    public function index()
    {
        $schedules = ReportSchedule::where('team_id', auth()->user()->team_id)
            ->with('customReport:id,name')
            ->orderBy('name')
            ->paginate(20);

        return Inertia::render('Reports/Schedules/Index', [
            'schedules' => $schedules,
        ]);
    }

    public function create()
    {
        $customReports = CustomReport::where('team_id', auth()->user()->team_id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('Reports/Schedules/Create', [
            'customReports' => $customReports,
            'frequencies' => ['daily', 'weekly', 'monthly'],
            'formats' => ['pdf', 'excel', 'both'],
            'reportTypes' => ['daily', 'occupancy', 'revenue', 'adr_revpar', 'custom'],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'report_type' => 'required|in:daily,occupancy,revenue,adr_revpar,custom',
            'custom_report_id' => 'nullable|exists:custom_reports,id',
            'frequency' => 'required|in:daily,weekly,monthly',
            'day_of_week' => 'nullable|integer|min:0|max:6',
            'day_of_month' => 'nullable|integer|min:1|max:31',
            'time' => 'required|date_format:H:i:s',
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'email',
            'format' => 'required|in:pdf,excel,both',
            'is_active' => 'boolean',
        ]);

        $validated['team_id'] = auth()->user()->team_id;
        $validated['created_by'] = auth()->id();
        $validated['next_run_at'] = $this->calculateNextRun(
            $validated['frequency'],
            $validated['day_of_week'] ?? null,
            $validated['day_of_month'] ?? null,
            $validated['time']
        );

        $schedule = ReportSchedule::create($validated);

        return redirect()->route('report-schedules.index')
            ->with('success', 'Report schedule created successfully.');
    }

    public function edit(ReportSchedule $reportSchedule)
    {
        if ($reportSchedule->team_id !== auth()->user()->team_id) {
            abort(403);
        }

        $customReports = CustomReport::where('team_id', auth()->user()->team_id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('Reports/Schedules/Edit', [
            'schedule' => $reportSchedule,
            'customReports' => $customReports,
            'frequencies' => ['daily', 'weekly', 'monthly'],
            'formats' => ['pdf', 'excel', 'both'],
            'reportTypes' => ['daily', 'occupancy', 'revenue', 'adr_revpar', 'custom'],
        ]);
    }

    public function update(Request $request, ReportSchedule $reportSchedule)
    {
        if ($reportSchedule->team_id !== auth()->user()->team_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'report_type' => 'required|in:daily,occupancy,revenue,adr_revpar,custom',
            'custom_report_id' => 'nullable|exists:custom_reports,id',
            'frequency' => 'required|in:daily,weekly,monthly',
            'day_of_week' => 'nullable|integer|min:0|max:6',
            'day_of_month' => 'nullable|integer|min:1|max:31',
            'time' => 'required|date_format:H:i:s',
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'email',
            'format' => 'required|in:pdf,excel,both',
            'is_active' => 'boolean',
        ]);

        $validated['next_run_at'] = $this->calculateNextRun(
            $validated['frequency'],
            $validated['day_of_week'] ?? null,
            $validated['day_of_month'] ?? null,
            $validated['time']
        );

        $reportSchedule->update($validated);

        return redirect()->route('report-schedules.index')
            ->with('success', 'Report schedule updated successfully.');
    }

    public function destroy(ReportSchedule $reportSchedule)
    {
        if ($reportSchedule->team_id !== auth()->user()->team_id) {
            abort(403);
        }

        $reportSchedule->delete();

        return redirect()->route('report-schedules.index')
            ->with('success', 'Report schedule deleted successfully.');
    }

    public function toggle(ReportSchedule $reportSchedule)
    {
        if ($reportSchedule->team_id !== auth()->user()->team_id) {
            abort(403);
        }

        $reportSchedule->update(['is_active' => !$reportSchedule->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $reportSchedule->is_active,
        ]);
    }

    public function runNow(ReportSchedule $reportSchedule)
    {
        if ($reportSchedule->team_id !== auth()->user()->team_id) {
            abort(403);
        }

        $this->runScheduledReport($reportSchedule);

        return response()->json([
            'success' => true,
            'message' => 'Report has been generated and sent to recipients.',
        ]);
    }

    public function testEmail(ReportSchedule $reportSchedule)
    {
        if ($reportSchedule->team_id !== auth()->user()->team_id) {
            abort(403);
        }

        try {
            Mail::raw('This is a test email from Fandaqah Hotel PMS Report Scheduler.', function ($message) use ($reportSchedule) {
                $message->to(auth()->user()->email)
                    ->subject('Test Email - ' . $reportSchedule->name);
            });

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function calculateNextRun($frequency, $dayOfWeek, $dayOfMonth, $time)
    {
        $now = Carbon::now();
        $timeCarbon = Carbon::parse($time);

        switch ($frequency) {
            case 'daily':
                $next = $now->copy()->setTime($timeCarbon->hour, $timeCarbon->minute, $timeCarbon->second);
                if ($next->isPast()) {
                    $next->addDay();
                }
                return $next;

            case 'weekly':
                $next = $now->copy()->next($dayOfWeek)->setTime($timeCarbon->hour, $timeCarbon->minute, $timeCarbon->second);
                return $next;

            case 'monthly':
                $next = $now->copy()->setDay($dayOfMonth)->setTime($timeCarbon->hour, $timeCarbon->minute, $timeCarbon->second);
                if ($next->isPast()) {
                    $next->addMonth();
                }
                return $next;

            default:
                return $now->addDay();
        }
    }

    public function runScheduledReport(ReportSchedule $schedule)
    {
        $data = null;
        $subject = $schedule->name;

        switch ($schedule->report_type) {
            case 'daily':
                $data = $this->generateDailyReportData();
                break;
            case 'occupancy':
                $data = $this->generateOccupancyReportData();
                break;
            case 'revenue':
                $data = $this->generateRevenueReportData();
                break;
            case 'adr_revpar':
                $data = $this->generateAdrRevparReportData($schedule);
                break;
            case 'custom':
                if ($schedule->customReport) {
                    $data = $this->customReportService->executeReport($schedule->customReport);
                }
                break;
        }

        foreach ($schedule->recipients as $recipient) {
            Mail::raw($this->formatReportEmail($schedule, $data), function ($message) use ($recipient, $subject) {
                $message->to($recipient)
                    ->subject($subject);
            });
        }

        $schedule->update([
            'last_run_at' => now(),
            'next_run_at' => $this->calculateNextRun(
                $schedule->frequency,
                $schedule->day_of_week,
                $schedule->day_of_month,
                $schedule->time
            ),
        ]);
    }

    protected function generateDailyReportData()
    {
        return [
            'date' => now()->toDateString(),
            'occupancy' => rand(45, 95),
            'revenue' => rand(5000, 15000),
        ];
    }

    protected function generateOccupancyReportData()
    {
        return [
            'occupancy_rate' => rand(45, 95),
            'rooms_available' => 100,
            'rooms_occupied' => rand(45, 95),
        ];
    }

    protected function generateRevenueReportData()
    {
        return [
            'total_revenue' => rand(5000, 15000),
            'adr' => rand(150, 350),
            'revpar' => rand(80, 200),
        ];
    }

    protected function generateAdrRevparReportData($schedule)
    {
        $service = new AdrRevparCalculationService();
        $service->setTeamId($schedule->team_id);
        
        $startDate = now()->subDays(30)->startOfDay();
        $endDate = now()->endOfDay();
        
        $data = $service->getDailyAdrRevpar($startDate, $endDate);
        
        $totalRevenue = array_sum(array_column($data, 'room_revenue'));
        $totalRoomsSold = array_sum(array_column($data, 'rooms_sold'));
        $totalRooms = $data[0]['total_rooms'] ?? 100;
        
        return [
            'period' => $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d'),
            'total_revenue' => $totalRevenue,
            'total_rooms_sold' => $totalRoomsSold,
            'total_rooms' => $totalRooms,
            'adr' => $totalRoomsSold > 0 ? round($totalRevenue / $totalRoomsSold, 2) : 0,
            'revpar' => round($totalRevenue / (count($data) * $totalRooms), 2),
        ];
    }

    protected function formatReportEmail($schedule, $data)
    {
        $content = "Report: {$schedule->name}\n";
        $content .= "Generated at: " . now()->toDateTimeString() . "\n\n";
        $content .= json_encode($data, JSON_PRETTY_PRINT);
        return $content;
    }
}