<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Reservation\ReservationController;
use App\Http\Controllers\GroupReservationController;
use App\Http\Controllers\ReservationCalendarController;

/*
|--------------------------------------------------------------------------
| Reservations Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'team.scope'])->group(function () {
    
    // Main Reservations Resource
    Route::resource('reservations', ReservationController::class)
        ->names('reservations');
    
    // Reservation Actions
    Route::prefix('reservations/{reservation}')->group(function () {
        Route::post('check-in', [ReservationController::class, 'checkIn'])
            ->name('reservations.checkin')
            ->middleware('can:reservations.checkin');
        
        Route::post('check-out', [ReservationController::class, 'checkOut'])
            ->name('reservations.checkout')
            ->middleware('can:reservations.checkout');
        
        Route::post('cancel', [ReservationController::class, 'cancel'])
            ->name('reservations.cancel')
            ->middleware('can:reservations.cancel');
        
        Route::post('no-show', [ReservationController::class, 'noShow'])
            ->name('reservations.no-show')
            ->middleware('can:reservations.no-show');
        
        Route::post('extend', [ReservationController::class, 'extend'])
            ->name('reservations.extend')
            ->middleware('can:reservations.extend');
        
        Route::post('transfer', [ReservationController::class, 'transfer'])
            ->name('reservations.transfer')
            ->middleware('can:reservations.transfer');
        
        Route::post('contract', [ReservationController::class, 'generateContract'])
            ->name('reservations.contract')
            ->middleware('can:reservations.contract.view');
        
        Route::post('signature', [ReservationController::class, 'storeSignature'])
            ->name('reservations.signature')
            ->middleware('can:reservations.signature');
        
        Route::post('restore', [ReservationController::class, 'restore'])
            ->name('reservations.restore')
            ->middleware('can:reservations.restore');
        
        Route::get('export', [ReservationController::class, 'export'])
            ->name('reservations.export')
            ->middleware('can:reservations.export');
    });
    
    // Bulk Actions
    Route::post('reservations/bulk-actions', [ReservationController::class, 'bulkActions'])
        ->name('reservations.bulk-actions')
        ->middleware('can:reservations.bulk-actions');
    
    Route::post('reservations/import', [ReservationController::class, 'import'])
        ->name('reservations.import')
        ->middleware('can:reservations.import');
    
    // Calendar Views
    Route::get('reservations-calendar', [ReservationCalendarController::class, 'index'])
        ->name('reservations.calendar');
    
    Route::get('reservations-calendar/events', [ReservationCalendarController::class, 'events'])
        ->name('reservations.calendar.events');
    
    // Arrivals & Departures
    Route::get('arrivals', [ReservationController::class, 'arrivals'])
        ->name('reservations.arrivals')
        ->middleware('can:reservations.view');
    
    Route::get('departures', [ReservationController::class, 'departures'])
        ->name('reservations.departures')
        ->middleware('can:reservations.view');
    
    Route::get('in-house-guests', [ReservationController::class, 'inHouseGuests'])
        ->name('reservations.in-house')
        ->middleware('can:reservations.view');
    
    // Group Reservations
    Route::resource('group-reservations', GroupReservationController::class)
        ->names('group-reservations');
});
