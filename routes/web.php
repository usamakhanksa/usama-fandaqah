<?php

use App\Http\Controllers\Foundation\CustomerController;
use App\Http\Controllers\Foundation\ReservationController;
use App\Http\Controllers\Foundation\UnitCategoryController;
use App\Http\Controllers\Foundation\UnitController;
use App\Http\Controllers\ClientProfileController;
use App\Http\Controllers\FrontDeskController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\HousekeepingController;
use App\Http\Controllers\RoomRestrictionController;
use App\Http\Controllers\FinancialsController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\MiscellaneousController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::prefix('foundation')->name('foundation.')->group(function (): void {
    Route::resource('unit-categories', UnitCategoryController::class);
    Route::resource('units', UnitController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('reservations', ReservationController::class);
});

use App\Http\Controllers\BookingController;
use App\Http\Controllers\BookingBlockController;
use App\Http\Controllers\BookingEventController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('client-relations', ClientProfileController::class);
    Route::resource('bookings', BookingController::class)->except(['create', 'edit', 'show']);
    Route::resource('booking-blocks', BookingBlockController::class)->except(['create', 'edit', 'show']);
    Route::resource('booking-events', BookingEventController::class)->except(['create', 'edit', 'show']);

    // Front Desk Routes
    Route::get('/front-desk', [FrontDeskController::class, 'index'])->name('front-desk.index');
    Route::put('/front-desk/{guest_checkin}/process', [FrontDeskController::class, 'process'])->name('front-desk.process');

    // Workspace Routes
    Route::resource('workspace', WorkspaceController::class)->except(['create', 'edit', 'show']);

    // Inventory & Rooms Routes
    Route::resource('rooms', RoomController::class);
    Route::resource('housekeeping', HousekeepingController::class);
    Route::resource('room-restrictions', RoomRestrictionController::class);

    // Financials Routes
    Route::get('/financials', [FinancialsController::class, 'index'])->name('financials.index');
    Route::post('/financials/transactions', [FinancialsController::class, 'storeTransaction'])->name('financials.transactions.store');
    Route::post('/financials/ar-accounts', [FinancialsController::class, 'storeArAccount'])->name('financials.ar.store');
    Route::put('/financials/ar-accounts/{id}', [FinancialsController::class, 'updateArAccount'])->name('financials.ar.update');
    Route::post('/financials/comps', [FinancialsController::class, 'storeComp'])->name('financials.comps.store');
    Route::post('/financials/eod', [FinancialsController::class, 'runEod'])->name('financials.eod.run');

    // Analytics & Reports Routes
    Route::get('/analytics', [ReportsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/safe-movement', [ReportsController::class, 'safeTransactions'])->name('analytics.safe');
    Route::post('/analytics/safe-movement', [ReportsController::class, 'storeSafeTransaction'])->name('analytics.safe.store');
    Route::delete('/analytics/safe-movement/{transaction}', [ReportsController::class, 'deleteSafeTransaction'])->name('analytics.safe.destroy');

    // Miscellaneous Routes
    Route::get('/misc', [MiscellaneousController::class, 'index'])->name('misc.index');
    Route::post('/misc/interfaces', [MiscellaneousController::class, 'storeInterface']);
    Route::put('/misc/interfaces/{interface}', [MiscellaneousController::class, 'updateInterface']);
    Route::delete('/misc/interfaces/{interface}', [MiscellaneousController::class, 'destroyInterface']);
    Route::post('/misc/exports', [MiscellaneousController::class, 'storeExport']);
    Route::delete('/misc/exports/{export}', [MiscellaneousController::class, 'destroyExport']);
    Route::post('/misc/requests', [MiscellaneousController::class, 'storeServiceRequest']);
    Route::put('/misc/requests/{pmsServiceRequest}', [MiscellaneousController::class, 'updateServiceRequest']);
    Route::delete('/misc/requests/{pmsServiceRequest}', [MiscellaneousController::class, 'destroyServiceRequest']);

    // Settings Routes
    Route::get('/settings/{group?}', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/{group}', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/dictionary/store', [SettingsController::class, 'storeDictionary'])->name('settings.dictionary.store');
    Route::put('/settings/dictionary/{dictionary}', [SettingsController::class, 'updateDictionary'])->name('settings.dictionary.update');
    Route::delete('/settings/dictionary/{dictionary}', [SettingsController::class, 'deleteDictionary'])->name('settings.dictionary.destroy');
});

// Serve the SPA for all other web routes
Route::get('/{any}', function () {
    return view('inertia');
})->where('any', '.*');
