<?php

use App\Http\Controllers\Foundation\CustomerController;
use App\Http\Controllers\Foundation\ReservationController;
use App\Http\Controllers\Foundation\UnitCategoryController;
use App\Http\Controllers\Foundation\UnitController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'inertia');

Route::prefix('foundation')->name('foundation.')->group(function (): void {
    Route::resource('unit-categories', UnitCategoryController::class);
    Route::resource('units', UnitController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('reservations', ReservationController::class);
});

Route::fallback(function () {
    return view('inertia');
});
