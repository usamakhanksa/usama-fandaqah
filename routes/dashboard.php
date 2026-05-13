<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\WidgetController;

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
|
| Routes for the main dashboard and dashboard widgets management.
|
*/

Route::middleware(['auth', 'team.scope'])->group(function () {
    
    // Main Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard.index');
    
    // Dashboard Widget Data Endpoints
    Route::get('/dashboard/occupancy', [DashboardController::class, 'occupancy'])
        ->name('dashboard.occupancy');
    
    Route::get('/dashboard/revenue', [DashboardController::class, 'revenue'])
        ->name('dashboard.revenue');
    
    Route::get('/dashboard/front-desk', [DashboardController::class, 'frontDesk'])
        ->name('dashboard.front-desk');
    
    Route::get('/dashboard/housekeeping', [DashboardController::class, 'housekeeping'])
        ->name('dashboard.housekeeping');
    
    Route::get('/dashboard/finance', [DashboardController::class, 'finance'])
        ->name('dashboard.finance');
    
    Route::get('/dashboard/night-audit', [DashboardController::class, 'nightAudit'])
        ->name('dashboard.night-audit');
    
    Route::get('/dashboard/integration-health', [DashboardController::class, 'integrationHealth'])
        ->name('dashboard.integration-health');
    
    // Dashboard Widget Management
    Route::post('/dashboard/widgets', [WidgetController::class, 'store'])
        ->name('dashboard.widgets.store');
    
    Route::patch('/dashboard/widgets/{widget}', [WidgetController::class, 'update'])
        ->name('dashboard.widgets.update');
    
    Route::delete('/dashboard/widgets/{widget}', [WidgetController::class, 'destroy'])
        ->name('dashboard.widgets.destroy');
    
    Route::post('/dashboard/widgets/reorder', [WidgetController::class, 'reorder'])
        ->name('dashboard.widgets.reorder');
});
