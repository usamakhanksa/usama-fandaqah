<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontDeskController;

/*
|--------------------------------------------------------------------------
| Front Desk Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'team.scope'])->group(function () {
    
    // Main Front Desk
    Route::get('/front-desk', [FrontDeskController::class, 'index'])
        ->name('front-desk.index')
        ->middleware('can:front-desk.view');
    
    // Check-in workflows
    Route::get('/front-desk/check-in', [FrontDeskController::class, 'checkInForm'])
        ->name('front-desk.checkin.form')
        ->middleware('can:reservations.checkin');
    
    Route::post('/front-desk/check-in/{reservation}', [FrontDeskController::class, 'processCheckIn'])
        ->name('front-desk.checkin.process')
        ->middleware('can:reservations.checkin');
    
    // Check-out workflows
    Route::get('/front-desk/check-out', [FrontDeskController::class, 'checkOutForm'])
        ->name('front-desk.checkout.form')
        ->middleware('can:reservations.checkout');
    
    Route::post('/front-desk/check-out/{reservation}', [FrontDeskController::class, 'processCheckOut'])
        ->name('front-desk.checkout.process')
        ->middleware('can:reservations.checkout');
    
    // Walk-in
    Route::get('/front-desk/walk-in', [FrontDeskController::class, 'walkInForm'])
        ->name('front-desk.walkin.form')
        ->middleware('can:reservations.create');
    
    Route::post('/front-desk/walk-in', [FrontDeskController::class, 'processWalkIn'])
        ->name('front-desk.walkin.process')
        ->middleware('can:reservations.create');
    
    // Room Assignment
    Route::get('/front-desk/room-assignment', [FrontDeskController::class, 'roomAssignment'])
        ->name('front-desk.room-assignment')
        ->middleware('can:units.assign');
    
    Route::post('/front-desk/room-assignment', [FrontDeskController::class, 'assignRoom'])
        ->name('front-desk.room-assignment.store')
        ->middleware('can:units.assign');
    
    Route::post('/front-desk/room-swap', [FrontDeskController::class, 'swapRoom'])
        ->name('front-desk.room-swap')
        ->middleware('can:units.swap');
});
