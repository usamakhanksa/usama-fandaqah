<?php

use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CompanyProfileController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\GuestController;
use App\Http\Controllers\Api\LookupController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\UnitHousingController;
use App\Http\Controllers\Api\ReservationWorkflowController;
use App\Http\Controllers\Api\FinancialManagementController;
use App\Http\Controllers\Api\UserGroupingController;
use App\Http\Controllers\Api\PosController;
use Illuminate\Support\Facades\Route;


Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
Route::get('/dashboard/customer-analytics', [DashboardController::class, 'customerAnalytics']);
Route::get('/dashboard/revenue-metrics', [DashboardController::class, 'revenueMetrics']);
Route::get('/dashboard/unit-status', [DashboardController::class, 'unitStatus']);
Route::get('/notifications', [DashboardController::class, 'notifications']);
Route::get('/settings/{category}', [\App\Http\Controllers\Api\SettingsController::class, 'index']);
Route::post('/settings/global', [\App\Http\Controllers\Api\SettingsController::class, 'updateGlobal']);

// Add Login Route
Route::post('/login', [\App\Http\Controllers\Api\Auth\LoginController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/reservations', [ReservationController::class, 'index']);
    // Other protected routes...
    Route::get('/rooms/availability', [ReservationController::class, 'availability']);

    Route::get('/rooms/metrics', [RoomController::class, 'metrics']);
    Route::get('/rooms/filters', [RoomController::class, 'filters']);
    Route::get('/rooms', [RoomController::class, 'index']);
    Route::post('/rooms', [RoomController::class, 'store']);
    Route::put('/rooms/{room}', [RoomController::class, 'update']);
    Route::delete('/rooms/{room}', [RoomController::class, 'destroy']);
    Route::get('/rooms/export', [RoomController::class, 'export']);
    Route::get('/rooms/availability/list', [RoomController::class, 'availability']);

    Route::get('/guests', [GuestController::class, 'index']);
    Route::post('/guests', [GuestController::class, 'store']);
    Route::put('/guests/{guest}', [GuestController::class, 'update']);
    Route::delete('/guests/{guest}', [GuestController::class, 'destroy']);

    Route::get('/companies', [CompanyProfileController::class, 'index']);
    Route::post('/companies', [CompanyProfileController::class, 'store']);
    Route::put('/companies/{companyProfile}', [CompanyProfileController::class, 'update']);
    Route::delete('/companies/{companyProfile}', [CompanyProfileController::class, 'destroy']);
    Route::post('/companies/drafts', [CompanyProfileController::class, 'saveDraft']);
    Route::get('/companies/drafts/latest', [CompanyProfileController::class, 'latestDraft']);

    Route::get('/lookups/countries', [LookupController::class, 'countries']);
    Route::get('/lookups/cities', [LookupController::class, 'cities']);

    Route::post('/uploads', [UploadController::class, 'store']);
    Route::delete('/uploads/{uploadedMedia}', [UploadController::class, 'destroy']);

    Route::post('/bookings', [BookingController::class, 'store']);
    Route::put('/bookings/{booking}', [BookingController::class, 'update']);


    Route::get('/units/filters', [UnitHousingController::class, 'filters']);
    Route::get('/units/floors', [UnitHousingController::class, 'floors']);
    Route::get('/units/daily-status', [UnitHousingController::class, 'dailyStatus']);
    Route::post('/units/check-in', [UnitHousingController::class, 'checkIn']);
    Route::post('/units/check-out', [UnitHousingController::class, 'checkOut']);

    Route::get('/reservations/schedule', [ReservationWorkflowController::class, 'schedule']);
    Route::post('/reservations/drafts', [ReservationWorkflowController::class, 'saveDraft']);
    Route::get('/reservations/drafts/{reference}', [ReservationWorkflowController::class, 'showDraft']);
    Route::post('/reservations/promo/apply', [ReservationWorkflowController::class, 'applyPromo']);
    Route::post('/reservations/confirm', [ReservationWorkflowController::class, 'confirm']);
    Route::get('/reservations/success/{booking}', [ReservationWorkflowController::class, 'successData']);
    Route::get('/reservations/receipt/{booking}', [ReservationWorkflowController::class, 'receipt']);
    Route::get('/reservations/management/{booking}', [ReservationWorkflowController::class, 'bookingDetails']);
    Route::post('/reservations/management/{booking}/notes', [ReservationWorkflowController::class, 'addNote']);

    Route::get('/financial/{module}', [FinancialManagementController::class, 'index']);
    Route::post('/financial/{type}/drafts', [FinancialManagementController::class, 'storeDraft']);
    Route::post('/financial/{type}/confirm', [FinancialManagementController::class, 'confirm']);


    Route::get('/user-groups/roles', [UserGroupingController::class, 'roles']);
    Route::post('/user-groups/roles', [UserGroupingController::class, 'storeRole']);
    Route::put('/user-groups/roles/{role}', [UserGroupingController::class, 'updateRole']);
    Route::delete('/user-groups/roles/{role}', [UserGroupingController::class, 'deleteRole']);
    Route::post('/user-groups/roles/{role}/duplicate', [UserGroupingController::class, 'duplicateRole']);
    Route::get('/user-groups/users', [UserGroupingController::class, 'users']);
    Route::post('/user-groups/roles/{role}/assign-users', [UserGroupingController::class, 'assignUsers']);
    Route::get('/user-groups/roles/{role}/permissions', [UserGroupingController::class, 'matrix']);
    Route::put('/user-groups/roles/{role}/permissions/{permission}', [UserGroupingController::class, 'updatePermission']);


    Route::get('/pos/stores', [PosController::class, 'stores']);
    Route::get('/pos/categories', [PosController::class, 'categories']);
    Route::get('/pos/sub-categories', [PosController::class, 'subCategories']);
    Route::get('/pos/brands', [PosController::class, 'brands']);
    Route::get('/pos/products', [PosController::class, 'products']);
    Route::get('/pos/services', [PosController::class, 'services']);
    Route::post('/pos/services', [PosController::class, 'createService']);
    Route::get('/pos/transactions', [PosController::class, 'transactions']);
    Route::get('/pos/cart', [PosController::class, 'cart']);
    Route::post('/pos/cart/items', [PosController::class, 'updateCart']);
    Route::delete('/pos/cart/items', [PosController::class, 'clearCart']);
    Route::post('/pos/checkout', [PosController::class, 'checkout']);


    Route::get('/reports/deposits', [\App\Http\Controllers\Api\ReportsController::class, 'revenueTax']); // Mapping existing for demo
    Route::get('/reports/cleaning', [\App\Http\Controllers\Api\ReportsController::class, 'unitCleanings']);
    Route::get('/reports/maintenance', [\App\Http\Controllers\Api\ReportsController::class, 'unitMaintenance']);

    Route::get('/search', [SearchController::class, 'autocomplete']);

    // Admin Lead Routes
    Route::get('/leads', [LeadController::class, 'index']);
    Route::get('/leads/stats', [LeadController::class, 'stats']);
    Route::put('/leads/{lead}', [LeadController::class, 'update']);
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy']);
});

// Public Lead Submission
Route::post('/leads/submit', [LeadController::class, 'store']);
