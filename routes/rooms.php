<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Room\UnitController;

/*
|--------------------------------------------------------------------------
| Rooms & Housekeeping Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'team.scope'])->group(function () {
    
    // Units (Rooms)
    Route::resource('units', UnitController::class)->names('units');
    
    Route::post('units/bulk-status', [UnitController::class, 'bulkStatus'])
        ->name('units.bulk-status')
        ->middleware('can:units.bulk-update');
    
    Route::post('units/{unit}/change-status', [UnitController::class, 'changeStatus'])
        ->name('units.change-status')
        ->middleware('can:units.update');
});
