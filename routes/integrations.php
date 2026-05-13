<?php

use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Integration\IntegrationController;
// use App\Http\Controllers\Integration\IntegrationSettingController;
// use App\Http\Controllers\Integration\IntegrationLogController;
// use App\Http\Controllers\Integration\FormIntegrationController;
// use App\Http\Controllers\Integration\PublicApiConsumerController;
// use App\Http\Controllers\Integration\ApiTokenController;

Route::middleware(['auth', 'verified', 'team.scope'])->group(function () {
    // Route::resource('integrations', IntegrationController::class)->names('integrations');
    // Route::post('integrations/{integration}/test', [IntegrationController::class, 'test'])->name('integrations.test');
    // Route::post('integrations/{integration}/sync', [IntegrationController::class, 'sync'])->name('integrations.sync');
    
    // Route::resource('integration-settings', IntegrationSettingController::class)->names('integration-settings');
    // Route::resource('integration-logs', IntegrationLogController::class)->only(['index', 'show'])->names('integration-logs');
    // Route::resource('form-integrations', FormIntegrationController::class)->names('form-integrations');
    // Route::post('form-integrations/{form}/approve', [FormIntegrationController::class, 'approve'])->name('form-integrations.approve');
    // Route::post('form-integrations/{form}/reject', [FormIntegrationController::class, 'reject'])->name('form-integrations.reject');
    
    // Route::resource('public-api-consumers', PublicApiConsumerController::class)->names('public-api-consumers');
    // Route::resource('api-tokens', ApiTokenController::class)->names('api-tokens');
});
