<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FailedJobController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('system.failed-jobs.view');
        return Inertia::render('System/FailedJobs/Index');
    }

    public function show($job)
    {
        $this->authorize('system.failed-jobs.view');
        return Inertia::render('System/FailedJobs/Show', ['job' => $job]);
    }

    public function retry($job)
    {
        $this->authorize('system.failed-jobs.retry');
        // Retry job logic
        return response()->json(['message' => 'Job retry initiated']);
    }

    public function retryAll()
    {
        $this->authorize('system.failed-jobs.retry');
        // Retry all jobs logic
        return response()->json(['message' => 'All jobs retry initiated']);
    }

    public function destroy($job)
    {
        $this->authorize('system.failed-jobs.delete');
        // Delete job logic
        return response()->noContent();
    }
}
