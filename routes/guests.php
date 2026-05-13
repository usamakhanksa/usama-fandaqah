<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Guest\CustomerController;
use App\Http\Controllers\Guest\GuestController;
use App\Http\Controllers\Guest\CompanyController;
use App\Http\Controllers\HighlightController;

/*
|--------------------------------------------------------------------------
| Guests & Companies Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'team.scope'])->group(function () {
    
    // Customers
    Route::resource('customers', CustomerController::class)
        ->names('customers');
    
    Route::post('customers/merge', [CustomerController::class, 'merge'])
        ->name('customers.merge')
        ->middleware('can:customers.merge');
    
    Route::post('customers/{customer}/block', [CustomerController::class, 'block'])
        ->name('customers.block')
        ->middleware('can:customers.block');
    
    Route::get('customers/{customer}/history', [CustomerController::class, 'history'])
        ->name('customers.history')
        ->middleware('can:customers.view');
    
    // Guests
    Route::resource('guests', GuestController::class)
        ->names('guests');
    
    Route::post('guests/{guest}/verify-shomoos', [GuestController::class, 'verifyShomoos'])
        ->name('guests.verify-shomoos')
        ->middleware('can:guests.verify');
    
    // Companies
    Route::resource('companies', CompanyController::class)
        ->names('companies');
    
    Route::post('companies/{company}/add-contact', [CompanyController::class, 'addContact'])
        ->name('companies.add-contact')
        ->middleware('can:companies.update');
    
    Route::get('companies/{company}/statement', [CompanyController::class, 'statement'])
        ->name('companies.statement')
        ->middleware('can:companies.view');
    
    // Company Groups
    // Route::resource('company-groups', CompanyGroupController::class)
    //     ->names('company-groups');
    
    // Company Notes
    // Route::resource('company-notes', CompanyNoteController::class)
    //     ->names('company-notes');
    
    // Blocked Guests
    // Route::resource('blocked-guests', BlockedGuestController::class)
    //     ->names('blocked-guests');
    
    // Route::post('blocked-guests/{blocked}/unblock', [BlockedGuestController::class, 'unblock'])
    //     ->name('blocked-guests.unblock')
    //     ->middleware('can:blocked-guests.update');
    
    // Turnaway Logs
    // Route::resource('turnaway-logs', TurnawayLogController::class)
    //     ->names('turnaway-logs');
    
    // Turnaway Reasons
    // Route::resource('turnaway-reasons', TurnawayReasonController::class)
    //     ->names('turnaway-reasons');
    
    // Comments
    // Route::resource('comments', CommentController::class)
    //     ->names('comments');
    
    // Highlights
    Route::resource('highlights', HighlightController::class)
        ->names('highlights');
});
