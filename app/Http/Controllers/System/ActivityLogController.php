<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('system.activity-log.view');
        return Inertia::render('System/ActivityLog/Index');
    }

    public function show($log)
    {
        $this->authorize('system.activity-log.view');
        return Inertia::render('System/ActivityLog/Show', ['log' => $log]);
    }

    public function telescope()
    {
        $this->authorize('system.telescope.view');
        return redirect('/telescope');
    }
}
