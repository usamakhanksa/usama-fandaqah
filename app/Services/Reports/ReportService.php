<?php

namespace App\Services\Reports;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class ReportService
{
    protected $teamId;

    public function __construct($teamId = null)
    {
        $this->teamId = $teamId ?? auth()->user()->team_id ?? null;
    }

    public function setTeamId($teamId)
    {
        $this->teamId = $teamId;
        return $this;
    }

    /**
     * Validate and format date range
     */
    public function validateDateRange($startDate, $endDate)
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        if ($start->gt($end)) {
            throw new \Exception('Start date cannot be after end date.');
        }

        return [$start, $end];
    }

    public function validateRequest($request)
    {
        $start = $request->get('start_date', Carbon::today()->subDays(30)->toDateString());
        $end = $request->get('end_date', Carbon::today()->toDateString());
        return $this->validateDateRange($start, $end);
    }

    /**
     * Cache wrapper for report data
     */
    public function rememberReport($key, $callback, $ttl = 3600)
    {
        $cacheKey = "report_{$this->teamId}_{$key}";
        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Clear report cache
     */
    public function clearCache($key = null)
    {
        if ($key) {
            Cache::forget("report_{$this->teamId}_{$key}");
        } else {
            // Ideally clear all report keys for this team
        }
    }
}
