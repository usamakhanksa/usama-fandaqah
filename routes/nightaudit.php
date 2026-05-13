<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NightAudit\NightAuditController;
// use App\Http\Controllers\NightAudit\NightAuditSnapshotController;
// use App\Http\Controllers\NightAudit\NoShowController;
// use App\Http\Controllers\NightAudit\FrozenTransactionController;
// use App\Http\Controllers\NightAudit\SnapshotQueueController;

/*
|--------------------------------------------------------------------------
| Night Audit Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'team.scope'])->group(function () {
    
    // Main Night Audit
    Route::get('/night-audit', [NightAuditController::class, 'index'])
        ->name('night-audit.index')
        ->middleware('can:night-audit.view');
    
    Route::post('/night-audit/run', [NightAuditController::class, 'run'])
        ->name('night-audit.run')
        ->middleware('can:night-audit.run');
    
    Route::post('/night-audit/rerun/{run}', [NightAuditController::class, 'rerun'])
        ->name('night-audit.rerun')
        ->middleware('can:night-audit.run');
    
    Route::get('/night-audit/history', [NightAuditController::class, 'history'])
        ->name('night-audit.history')
        ->middleware('can:night-audit.view');
    
    Route::get('/night-audit/{run}/details', [NightAuditController::class, 'details'])
        ->name('night-audit.details')
        ->middleware('can:night-audit.view');
    
    // Snapshots
    // Route::get('/night-audit/snapshots', [NightAuditSnapshotController::class, 'index'])
    //     ->name('night-audit.snapshots.index')
    //     ->middleware('can:night-audit.view');
    
    // Route::get('/night-audit/snapshots/{snapshot}', [NightAuditSnapshotController::class, 'show'])
    //     ->name('night-audit.snapshots.show')
    //     ->middleware('can:night-audit.view');
    
    // No-shows
    // Route::get('/night-audit/no-shows', [NoShowController::class, 'index'])
    //     ->name('night-audit.no-shows.index')
    //     ->middleware('can:night-audit.view');
    
    // Route::post('/night-audit/no-shows/process', [NoShowController::class, 'process'])
    //     ->name('night-audit.no-shows.process')
    //     ->middleware('can:night-audit.process');
    
    // Route::post('/night-audit/no-shows/{reservation}/charge', [NoShowController::class, 'charge'])
    //     ->name('night-audit.no-shows.charge')
    //     ->middleware('can:night-audit.process');
    
    // Route::post('/night-audit/no-shows/{reservation}/waive', [NoShowController::class, 'waive'])
    //     ->name('night-audit.no-shows.waive')
    //     ->middleware('can:night-audit.process');
    
    // Frozen Transactions
    // Route::get('/night-audit/frozen-transactions', [FrozenTransactionController::class, 'index'])
    //     ->name('night-audit.frozen-transactions.index')
    //     ->middleware('can:night-audit.view');
    
    // Route::get('/night-audit/business-date-transactions', [FrozenTransactionController::class, 'businessDateTransactions'])
    //     ->name('night-audit.business-date-transactions')
    //     ->middleware('can:night-audit.view');
    
    // Snapshot Queue
    // Route::get('/night-audit/snapshot-queue', [SnapshotQueueController::class, 'index'])
    //     ->name('night-audit.snapshot-queue.index')
    //     ->middleware('can:night-audit.view');
    
    // Route::post('/night-audit/snapshot-queue/{item}/retry', [SnapshotQueueController::class, 'retry'])
    //     ->name('night-audit.snapshot-queue.retry')
    //     ->middleware('can:night-audit.process');
    
    // Route::post('/night-audit/snapshot-queue/process-all', [SnapshotQueueController::class, 'processAll'])
    //     ->name('night-audit.snapshot-queue.process-all')
    //     ->middleware('can:night-audit.process');
});
