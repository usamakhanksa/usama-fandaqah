<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\System\ActivityLogController;
// use App\Http\Controllers\System\ActionEventController;
// use App\Http\Controllers\System\JobController;
use App\Http\Controllers\System\FailedJobController;
// use App\Http\Controllers\System\DeviceLoginController;
// use App\Http\Controllers\System\SessionController;
// use App\Http\Controllers\System\SecurityLogController;
// use App\Http\Controllers\System\DataImportController;
// use App\Http\Controllers\System\DataExportController;
// use App\Http\Controllers\System\AuditTrailController;

/*
|--------------------------------------------------------------------------
| System Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'team.scope'])->group(function () {
    
    // Activity Log
    Route::get('/system/activity-log', [ActivityLogController::class, 'index'])
        ->name('system.activity-log')
        ->middleware('can:system.activity-log.view');
    
    Route::get('/system/activity-log/{log}', [ActivityLogController::class, 'show'])
        ->name('system.activity-log.show')
        ->middleware('can:system.activity-log.view');
    
    // Action Events
    // Route::get('/system/action-events', [ActionEventController::class, 'index'])
    //     ->name('system.action-events')
    //     ->middleware('can:system.action-events.view');
    
    // Route::get('/system/action-events/{event}', [ActionEventController::class, 'show'])
    //     ->name('system.action-events.show')
    //     ->middleware('can:system.action-events.view');
    
    // Jobs
    // Route::get('/system/jobs', [JobController::class, 'index'])
    //     ->name('system.jobs')
    //     ->middleware('can:system.jobs.view');
    
    // Route::get('/system/jobs/{job}', [JobController::class, 'show'])
    //     ->name('system.jobs.show')
    //     ->middleware('can:system.jobs.view');
    
    // Failed Jobs
    Route::get('/system/failed-jobs', [FailedJobController::class, 'index'])
        ->name('system.failed-jobs')
        ->middleware('can:system.failed-jobs.view');
    
    Route::get('/system/failed-jobs/{job}', [FailedJobController::class, 'show'])
        ->name('system.failed-jobs.show')
        ->middleware('can:system.failed-jobs.view');
    
    Route::post('/system/failed-jobs/{job}/retry', [FailedJobController::class, 'retry'])
        ->name('system.failed-jobs.retry')
        ->middleware('can:system.failed-jobs.retry');
    
    Route::post('/system/failed-jobs/retry-all', [FailedJobController::class, 'retryAll'])
        ->name('system.failed-jobs.retry-all')
        ->middleware('can:system.failed-jobs.retry');
    
    Route::delete('/system/failed-jobs/{job}', [FailedJobController::class, 'destroy'])
        ->name('system.failed-jobs.destroy')
        ->middleware('can:system.failed-jobs.delete');
    
    // Telescope (Laravel Debug Tool)
    Route::get('/system/telescope', [ActivityLogController::class, 'telescope'])
        ->name('system.telescope')
        ->middleware('can:system.telescope.view');
    
    // Device Logins
    // Route::get('/system/device-logins', [DeviceLoginController::class, 'index'])
    //     ->name('system.device-logins')
    //     ->middleware('can:system.device-logins.view');
    
    // Route::delete('/system/device-logins/{device}', [DeviceLoginController::class, 'revoke'])
    //     ->name('system.device-logins.revoke')
    //     ->middleware('can:system.device-logins.revoke');
    
    // Sessions
    // Route::get('/system/sessions', [SessionController::class, 'index'])
    //     ->name('system.sessions')
    //     ->middleware('can:system.sessions.view');
    
    // Route::delete('/system/sessions/{session}', [SessionController::class, 'revoke'])
    //     ->name('system.sessions.revoke')
    //     ->middleware('can:system.sessions.revoke');
    
    // Route::delete('/system/sessions', [SessionController::class, 'revokeAll'])
    //     ->name('system.sessions.revoke-all')
    //     ->middleware('can:system.sessions.revoke');
    
    // Security Logs
    // Route::get('/system/security-logs', [SecurityLogController::class, 'index'])
    //     ->name('system.security-logs')
    //     ->middleware('can:system.security-logs.view');
    
    // Route::get('/system/security-logs/{log}', [SecurityLogController::class, 'show'])
    //     ->name('system.security-logs.show')
    //     ->middleware('can:system.security-logs.view');
    
    // Route::get('/system/security-logs/export', [SecurityLogController::class, 'export'])
    //     ->name('system.security-logs.export')
    //     ->middleware('can:system.security-logs.export');
    
    // Data Import
    // Route::get('/system/data-import', [DataImportController::class, 'index'])
    //     ->name('system.data-import')
    //     ->middleware('can:system.data-import.view');
    
    // Route::post('/system/data-import', [DataImportController::class, 'import'])
    //     ->name('system.data-import.store')
    //     ->middleware('can:system.data-import.execute');
    
    // Route::get('/system/data-import/template/{type}', [DataImportController::class, 'template'])
    //     ->name('system.data-import.template')
    //     ->middleware('can:system.data-import.view');
    
    // Data Export
    // Route::get('/system/data-export', [DataExportController::class, 'index'])
    //     ->name('system.data-export')
    //     ->middleware('can:system.data-export.view');
    
    // Route::post('/system/data-export', [DataExportController::class, 'export'])
    //     ->name('system.data-export.store')
    //     ->middleware('can:system.data-export.execute');
    
    // Route::get('/system/data-export/download/{file}', [DataExportController::class, 'download'])
    //     ->name('system.data-export.download')
    //     ->middleware('can:system.data-export.view');
    
    // Audit Trail
    // Route::get('/system/audit-trail', [AuditTrailController::class, 'index'])
    //     ->name('system.audit-trail')
    //     ->middleware('can:system.audit-trail.view');
    
    // Route::get('/system/audit-trail/{trail}', [AuditTrailController::class, 'show'])
    //     ->name('system.audit-trail.show')
    //     ->middleware('can:system.audit-trail.view');
    
    // Route::get('/system/audit-trail/export', [AuditTrailController::class, 'export'])
    //     ->name('system.audit-trail.export')
    //     ->middleware('can:system.audit-trail.export');
});
