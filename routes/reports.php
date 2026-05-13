<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\Report\ForecastReportController;
use App\Http\Controllers\Report\NoShowReportController;
use App\Http\Controllers\Report\CancellationReportController;
use App\Http\Controllers\Report\CommissionReportController;
use App\Http\Controllers\Report\HousekeepingDiscrepancyReportController;
use App\Http\Controllers\Report\PaidOutsReportController;
use App\Http\Controllers\Report\TurnawayReportController;
use App\Http\Controllers\Report\SourcePerformanceReportController;
use App\Http\Controllers\Report\CompanyArReportController;
use App\Http\Controllers\Report\TrialBalanceReportController;

/*
|--------------------------------------------------------------------------
| Reports Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'team.scope'])->group(function () {

    // Forecast Report
    Route::get('/reports/forecast-history', [ForecastReportController::class, 'index'])
        ->name('reports.forecast-history')
        ->middleware('can:reports.forecast');
    Route::get('/reports/forecast-history/generate', [ForecastReportController::class, 'generate'])
        ->name('reports.forecast-history.generate')
        ->middleware('can:reports.forecast');
    Route::get('/reports/forecast-history/export', [ForecastReportController::class, 'export'])
        ->name('reports.forecast-history.export')
        ->middleware('can:reports.export');

    // No-Show Report
    Route::get('/reports/no-show', [NoShowReportController::class, 'index'])
        ->name('reports.no-show')
        ->middleware('can:reports.noshow');
    Route::get('/reports/no-show/generate', [NoShowReportController::class, 'generate'])
        ->name('reports.no-show.generate')
        ->middleware('can:reports.noshow');
    Route::get('/reports/no-show/export', [NoShowReportController::class, 'export'])
        ->name('reports.no-show.export')
        ->middleware('can:reports.export');

    // Cancellation Report
    Route::get('/reports/cancellation', [CancellationReportController::class, 'index'])
        ->name('reports.cancellation')
        ->middleware('can:reports.cancellation');
    Route::get('/reports/cancellation/generate', [CancellationReportController::class, 'generate'])
        ->name('reports.cancellation.generate')
        ->middleware('can:reports.cancellation');
    Route::get('/reports/cancellation/export', [CancellationReportController::class, 'export'])
        ->name('reports.cancellation.export')
        ->middleware('can:reports.export');

    // Commission Report
    Route::get('/reports/commission', [CommissionReportController::class, 'index'])
        ->name('reports.commission')
        ->middleware('can:reports.commission');
    Route::get('/reports/commission/generate', [CommissionReportController::class, 'generate'])
        ->name('reports.commission.generate')
        ->middleware('can:reports.commission');
    Route::get('/reports/commission/export', [CommissionReportController::class, 'export'])
        ->name('reports.commission.export')
        ->middleware('can:reports.export');

     // Housekeeping Discrepancy Report
     Route::get('/reports/housekeeping-discrepancy', [HousekeepingDiscrepancyReportController::class, 'index'])
         ->name('reports.housekeeping-discrepancy')
         ->middleware('can:reports.housekeeping_discrepancy');
     Route::get('/reports/housekeeping-discrepancy/generate', [HousekeepingDiscrepancyReportController::class, 'generate'])
         ->name('reports.housekeeping-discrepancy.generate')
         ->middleware('can:reports.housekeeping_discrepancy');
     Route::get('/reports/housekeeping-discrepancy/export', [HousekeepingDiscrepancyReportController::class, 'export'])
         ->name('reports.housekeeping-discrepancy.export')
         ->middleware('can:reports.export');

     // Paid-Outs Report
     Route::get('/reports/paid-outs', [PaidOutsReportController::class, 'index'])
         ->name('reports.paidouts')
         ->middleware('can:reports.paidouts');
     Route::get('/reports/paid-outs/generate', [PaidOutsReportController::class, 'generate'])
         ->name('reports.paidouts.generate')
         ->middleware('can:reports.paidouts');
     Route::get('/reports/paid-outs/export', [PaidOutsReportController::class, 'export'])
         ->name('reports.paidouts.export')
         ->middleware('can:reports.export');

     // Turnaway Report
     Route::get('/reports/turnaway', [TurnawayReportController::class, 'index'])
         ->name('reports.turnaway')
         ->middleware('can:reports.turnaway');
     Route::get('/reports/turnaway/generate', [TurnawayReportController::class, 'generate'])
         ->name('reports.turnaway.generate')
         ->middleware('can:reports.turnaway');
     Route::get('/reports/turnaway/export', [TurnawayReportController::class, 'export'])
         ->name('reports.turnaway.export')
         ->middleware('can:reports.export');

     // Source Performance Report
     Route::get('/reports/source-performance', [SourcePerformanceReportController::class, 'index'])
         ->name('reports.source_performance')
         ->middleware('can:reports.source_performance');
     Route::get('/reports/source-performance/generate', [SourcePerformanceReportController::class, 'generate'])
         ->name('reports.source_performance.generate')
         ->middleware('can:reports.source_performance');
     Route::get('/reports/source-performance/export', [SourcePerformanceReportController::class, 'export'])
         ->name('reports.source_performance.export')
         ->middleware('can:reports.export');

     // Company AR Report
     Route::get('/reports/company-ar', [CompanyArReportController::class, 'index'])
         ->name('reports.company_ar')
         ->middleware('can:reports.company_ar');
     Route::get('/reports/company-ar/generate', [CompanyArReportController::class, 'generate'])
         ->name('reports.company_ar.generate')
         ->middleware('can:reports.company_ar');
     Route::get('/reports/company-ar/export', [CompanyArReportController::class, 'export'])
         ->name('reports.company_ar.export')
         ->middleware('can:reports.export');

     // Trial Balance Report
     Route::get('/reports/trial-balance', [TrialBalanceReportController::class, 'index'])
         ->name('reports.trial_balance')
         ->middleware('can:reports.trial_balance');
     Route::get('/reports/trial-balance/generate', [TrialBalanceReportController::class, 'generate'])
         ->name('reports.trial_balance.generate')
         ->middleware('can:reports.trial_balance');
     Route::get('/reports/trial-balance/export', [TrialBalanceReportController::class, 'export'])
         ->name('reports.trial_balance.export')
         ->middleware('can:reports.export');

     // Other existing report routes...
    Route::get('/reports/daily', [App\Http\Controllers\Report\DailyReportController::class, 'index'])
        ->name('daily')
        ->middleware('can:reports.daily');
    Route::get('/reports/daily/generate', [App\Http\Controllers\Report\DailyReportController::class, 'generate'])
        ->name('daily.generate')
        ->middleware('can:reports.daily');
    Route::get('/reports/daily/export', [App\Http\Controllers\Report\DailyReportController::class, 'export'])
        ->name('daily.export')
        ->middleware('can:reports.export');

    Route::get('/reports/occupancy', [App\Http\Controllers\Report\OccupancyReportController::class, 'index'])
        ->name('occupancy')
        ->middleware('can:reports.occupancy');
    Route::get('/reports/occupancy/generate', [App\Http\Controllers\Report\OccupancyReportController::class, 'generate'])
        ->name('occupancy.generate')
        ->middleware('can:reports.occupancy');

    Route::get('/reports/revenue', [App\Http\Controllers\Report\RevenueReportController::class, 'index'])
        ->name('revenue')
        ->middleware('can:reports.revenue');
    Route::get('/reports/revenue/generate', [App\Http\Controllers\Report\RevenueReportController::class, 'generate'])
        ->name('revenue.generate')
        ->middleware('can:reports.revenue');

    Route::get('/reports/adr-revpar', [App\Http\Controllers\Report\AdrRevparReportController::class, 'index'])
        ->name('adr-revpar.index')
        ->middleware('can:reports.adr');
    Route::get('/reports/adr-revpar/generate', [App\Http\Controllers\Report\AdrRevparReportController::class, 'generate'])
        ->name('adr-revpar.generate')
        ->middleware('can:reports.adr');
    Route::get('/reports/adr-revpar/export', [App\Http\Controllers\Report\AdrRevparReportController::class, 'export'])
        ->name('adr-revpar.export')
        ->middleware('can:reports.export');

    // Custom Reports
    Route::resource('custom-reports', App\Http\Controllers\Report\CustomReportController::class);
    Route::get('/custom-reports/{customReport}/run', [App\Http\Controllers\Report\CustomReportController::class, 'run'])
        ->name('custom-reports.run');
    Route::get('/custom-reports/{customReport}/export', [App\Http\Controllers\Report\CustomReportController::class, 'export'])
        ->name('custom-reports.export');
    Route::post('/custom-reports/preview', [App\Http\Controllers\Report\CustomReportController::class, 'preview'])
        ->name('custom-reports.preview');
    Route::get('/custom-reports/columns/{module}', [App\Http\Controllers\Report\CustomReportController::class, 'availableColumns'])
        ->name('custom-reports.columns');

    // Report Schedules
    Route::resource('report-schedules', App\Http\Controllers\Report\ReportScheduleController::class);
    Route::post('/report-schedules/{reportSchedule}/toggle', [App\Http\Controllers\Report\ReportScheduleController::class, 'toggle'])
        ->name('report-schedules.toggle');
    Route::post('/report-schedules/{reportSchedule}/run-now', [App\Http\Controllers\Report\ReportScheduleController::class, 'runNow'])
        ->name('report-schedules.run-now');
    Route::post('/report-schedules/{reportSchedule}/test-email', [App\Http\Controllers\Report\ReportScheduleController::class, 'testEmail'])
        ->name('report-schedules.test-email');
});
