<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Pos\PosController;
use App\Http\Controllers\Pos\ServiceCategoryController;
// use App\Http\Controllers\Pos\ServiceController;
// use App\Http\Controllers\Pos\ServiceLogController;
// use App\Http\Controllers\Pos\PosTransactionController;
// use App\Http\Controllers\Pos\QuickPaymentController;

Route::middleware(['auth', 'verified', 'team.scope'])->group(function () {
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/sale', [PosController::class, 'processSale'])->name('pos.sale');
    
    Route::resource('service-categories', ServiceCategoryController::class)->names('service-categories');
    // Route::resource('services', ServiceController::class)->names('services');
    // Route::resource('service-logs', ServiceLogController::class)->names('service-logs');
    // Route::resource('pos-transactions', PosTransactionController::class)->names('pos-transactions');
    
    // Route::post('pos-transactions/{transaction}/void', [PosTransactionController::class, 'void'])
    //     ->name('pos-transactions.void');
    // Route::post('pos-transactions/{transaction}/refund', [PosTransactionController::class, 'refund'])
    //     ->name('pos-transactions.refund');
    
    // Route::resource('quick-payments', QuickPaymentController::class)->names('quick-payments');
});
