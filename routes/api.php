<?php

use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CompanyProfileController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\NightAuditController;
use App\Http\Controllers\Api\NoShowChargeRuleController;
use App\Http\Controllers\Api\GuestController;
use App\Http\Controllers\Api\LookupController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\ReservationGuestController;
use App\Http\Controllers\Api\ReservationRoomController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\UnitHousingController;
use App\Http\Controllers\Api\ReservationWorkflowController;
use App\Http\Controllers\Api\FinancialManagementController;
use App\Http\Controllers\Api\UserGroupingController;
use App\Http\Controllers\Api\PosController;
use App\Http\Controllers\Api\RoomAdjustmentController;
use App\Http\Controllers\Api\PaymentCorrectionController;
use App\Http\Controllers\Api\PromissoryController;
use App\Http\Controllers\Api\CompanyGroupController;
use App\Http\Controllers\Api\CityLedgerController;
use App\Http\Controllers\Api\CashierShiftController;
use App\Http\Controllers\Api\RoomStatusLogController;
use App\Http\Controllers\Api\RoomTypeController;
use App\Http\Controllers\Api\RoomFloorController;
use App\Http\Controllers\Api\CommissionController;
use App\Http\Controllers\Api\SourceController;
use Illuminate\Support\Facades\Route;



Route::get('/settings/{category}', [\App\Http\Controllers\Api\SettingsController::class, 'index']);
Route::post('/settings/global', [\App\Http\Controllers\Api\SettingsController::class, 'updateGlobal']);

// Add Login Route
Route::post('/login', [\App\Http\Controllers\Api\Auth\LoginController::class, 'login']);

// Logout route
Route::post('/logout', [\App\Http\Controllers\Api\Auth\LoginController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/sidebar', [\App\Http\Controllers\Api\SidebarController::class, 'index']);
    
    // Adjustments
    Route::post('/adjustments', [RoomAdjustmentController::class, 'store']);
    Route::get('/reservations/{reservation}/adjustments', [RoomAdjustmentController::class, 'index']);

    // Payment Corrections (Task 10)
    Route::post('/finance/payment-correction', [PaymentCorrectionController::class, 'correct']);
    Route::get('/finance/payment-corrections', [PaymentCorrectionController::class, 'index']);


    
    // Dashboards
    Route::get('/dashboard/overview', [DashboardController::class, 'overview']);
    Route::get('/dashboard/overview/export', [DashboardController::class, 'exportOverview']);
    Route::get('/dashboard/front-desk', [DashboardController::class, 'frontDesk']);
    Route::get('/dashboard/front-desk/export', [DashboardController::class, 'exportFrontDesk']);
    Route::get('/dashboard/occupancy', [DashboardController::class, 'occupancy']);
    Route::get('/dashboard/occupancy/export', [DashboardController::class, 'exportOccupancy']);
    Route::get('/dashboard/housekeeping', [DashboardController::class, 'housekeeping']);
    Route::get('/dashboard/finance', [DashboardController::class, 'finance']);
    Route::get('/dashboard/night-audit', [DashboardController::class, 'nightAuditDashboard']);
    Route::get('/dashboard/revenue', [DashboardController::class, 'revenue']);
    Route::get('/dashboard/revenue/export', [DashboardController::class, 'exportRevenue']);
    Route::get('/dashboard/ar', [DashboardController::class, 'ar']);
    Route::get('/dashboard/cashier', [DashboardController::class, 'cashier']);
    Route::get('/dashboard/commissions', [DashboardController::class, 'commissions']);
    Route::get('/dashboard/metabase', [DashboardController::class, 'metabase']);

    // Master Data / Options
    Route::group(['prefix' => 'master-data'], function () {
        Route::get('/tables', [\App\Http\Controllers\Api\MasterDataController::class, 'tables']);
        Route::get('/{table}', [\App\Http\Controllers\Api\MasterDataController::class, 'index']);
        Route::post('/{table}', [\App\Http\Controllers\Api\MasterDataController::class, 'store']);
        Route::put('/{table}/{id}', [\App\Http\Controllers\Api\MasterDataController::class, 'update']);
        Route::delete('/{table}/{id}', [\App\Http\Controllers\Api\MasterDataController::class, 'destroy']);
    });

    // Cashier Shifts
    Route::group(['prefix' => 'cashier-shifts'], function () {
        Route::get('/', [CashierShiftController::class, 'index']);
        Route::get('/active', [CashierShiftController::class, 'activeShift']);
        Route::post('/open', [CashierShiftController::class, 'open']);
        Route::post('/{shift}/close', [CashierShiftController::class, 'close']);
        Route::post('/{shift}/approve', [CashierShiftController::class, 'approve']);
        Route::get('/{shift}', [CashierShiftController::class, 'show']);
        Route::get('/{shift}/transactions', [CashierShiftController::class, 'transactions']);
    });

    // Room Status Logs
    Route::group(['prefix' => 'room-status-logs'], function () {
        Route::get('/', [RoomStatusLogController::class, 'index']);
        Route::get('/timeline/{unit}', [RoomStatusLogController::class, 'timeline']);
    });

    // Room Types
    Route::group(['prefix' => 'room-types'], function () {
        Route::get('/', [RoomTypeController::class, 'index']);
        Route::post('/', [RoomTypeController::class, 'store']);
        Route::put('/{roomType}', [RoomTypeController::class, 'update']);
        Route::delete('/{roomType}', [RoomTypeController::class, 'destroy']);
    });

    // Room Floors
    Route::group(['prefix' => 'room-floors'], function () {
        Route::get('/', [RoomFloorController::class, 'index']);
        Route::post('/', [RoomFloorController::class, 'store']);
        Route::put('/{roomFloor}', [RoomFloorController::class, 'update']);
        Route::delete('/{roomFloor}', [RoomFloorController::class, 'destroy']);
    });

    // Commissions
    Route::group(['prefix' => 'commissions'], function () {
        Route::get('/', [CommissionController::class, 'index']);
        Route::get('/summary', [CommissionController::class, 'summary']);
        Route::post('/{payment}/approve', [CommissionController::class, 'approve']);
        Route::post('/{payment}/pay', [CommissionController::class, 'pay']);
        Route::post('/{payment}/cancel', [CommissionController::class, 'cancel']);
    });

    // Sources (Travel Agents)
    Route::apiResource('sources', SourceController::class);

    // Night Audit
    Route::get('/night-audit/preflight', [NightAuditController::class, 'preflight']);
    Route::post('/night-audit/run', [NightAuditController::class, 'run']);
    Route::post('/night-audit/rerun/{id}', [NightAuditController::class, 'rerun']);
    Route::get('/night-audit/status', [NightAuditController::class, 'status']);
    Route::post('/night-audit/init-date', [NightAuditController::class, 'setInitialDate']);
    
    // No-Show Rules
    Route::get('/no-show-rules/preview', [NoShowChargeRuleController::class, 'previewAffected']);
    Route::apiResource('no-show-rules', NoShowChargeRuleController::class);

    // Stay Charge Configs (Early Check-in / Late Checkout)
    Route::get('/stay-charge-configs/calculate', [\App\Http\Controllers\Api\StayChargeConfigController::class, 'calculate']);
    Route::apiResource('stay-charge-configs', \App\Http\Controllers\Api\StayChargeConfigController::class);

    // Company Profiles
    Route::apiResource('company-profiles', \App\Http\Controllers\Api\CompanyProfileController::class);
    Route::post('/company-profiles/{id}/restore', [\App\Http\Controllers\Api\CompanyProfileController::class, 'restore']);
    Route::post('/company-profiles/drafts', [\App\Http\Controllers\Api\CompanyProfileController::class, 'saveDraft']);
    Route::get('/company-profiles/drafts/latest', [\App\Http\Controllers\Api\CompanyProfileController::class, 'latestDraft']);
    
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::get('/reservations/calendar', [ReservationController::class, 'calendar']);
    Route::get('/reservations/arrivals', [ReservationController::class, 'arrivals']);
    Route::get('/reservations/departures', [ReservationController::class, 'departures']);
    Route::get('/reservations/in-house', [ReservationController::class, 'inHouse']);
    Route::get('/reservations/online', [ReservationController::class, 'online']);
    Route::get('/reservations/ota', [ReservationController::class, 'ota']);
    Route::get('/reservations/groups', [ReservationController::class, 'groupIndex']);
    Route::post('/reservations/groups', [ReservationController::class, 'groupStore']);
    Route::post('/reservations/groups/{id}/cancel', [ReservationController::class, 'groupCancel']);
    Route::post('/reservations/{id}/confirm', [ReservationController::class, 'confirm']);
    Route::post('/reservations/{id}/reject', [ReservationController::class, 'reject']);
    Route::post('/reservations/{id}/sync-status', [ReservationController::class, 'syncStatus']);
    Route::get('/reservations/transfers', [\App\Http\Controllers\Api\ReservationTransferController::class, 'index'])->name('reservation-transfers.index');
    Route::get('/reservations/transfers/{id}', [\App\Http\Controllers\Api\ReservationTransferController::class, 'show'])->name('reservation-transfers.show');
    Route::get('/reservations/extensions', [\App\Http\Controllers\Api\ReservationExtensionController::class, 'index'])->name('reservation-extensions.index');
    Route::get('/reservations/extensions/{id}', [\App\Http\Controllers\Api\ReservationExtensionController::class, 'show'])->name('reservation-extensions.show');

    // Cancellations & No-Shows
    Route::get('/reservations/cancellations', [\App\Http\Controllers\Api\ReservationCancellationController::class, 'index'])->name('reservations.cancellations');
    Route::get('/reservations/cancellations/{id}', [\App\Http\Controllers\Api\ReservationCancellationController::class, 'show'])->name('reservations.cancellations.show');

    // Reservation Messages
    Route::get('/reservations/messages', [\App\Http\Controllers\Api\ReservationMessageController::class, 'index'])->name('reservation-messages.index');
    Route::get('/reservations/messages/{id}', [\App\Http\Controllers\Api\ReservationMessageController::class, 'show'])->name('reservation-messages.show');
    Route::post('/reservations/messages', [\App\Http\Controllers\Api\ReservationMessageController::class, 'store'])->name('reservation-messages.store');

    // Reservation Audit Locks
    Route::get('/reservations/audit-locks', [\App\Http\Controllers\Api\ReservationAuditLockController::class, 'index'])->name('reservations.audit-locks');
    Route::get('/reservations/audit-locks/{id}', [\App\Http\Controllers\Api\ReservationAuditLockController::class, 'show'])->name('reservations.audit-locks.show');

    // Reservation Contracts
    Route::get('/reservations/contracts', [\App\Http\Controllers\Api\ReservationContractController::class, 'index'])->name('reservation-contracts.index');
    Route::get('/reservations/contracts/{id}', [\App\Http\Controllers\Api\ReservationContractController::class, 'show'])->name('reservation-contracts.show');
    Route::post('/reservations/contracts', [\App\Http\Controllers\Api\ReservationContractController::class, 'store'])->name('reservation-contracts.store');
    Route::get('/reservations/contracts/{id}/download', [\App\Http\Controllers\Api\ReservationContractController::class, 'download'])->name('reservation-contracts.download');
    Route::post('/reservations/contracts/{id}/sign', [\App\Http\Controllers\Api\ReservationContractController::class, 'sign'])->name('reservation-contracts.sign');

    // Digital Signatures
    Route::get('/reservations/signatures', [\App\Http\Controllers\Api\DigitalSignatureController::class, 'index'])->name('reservation-signatures.index');
    Route::get('/reservations/signatures/{id}', [\App\Http\Controllers\Api\DigitalSignatureController::class, 'show'])->name('reservation-signatures.show');

    // Reservation Ratings
    Route::get('/reservations/ratings', [ReservationController::class, 'ratings'])->name('reservation-ratings.index');
    Route::get('/reservations/ratings/{id}', [ReservationController::class, 'ratingShow'])->name('reservation-ratings.show');

    // Reservation Guests
    Route::get('/reservations/{reservation}/guests', [ReservationGuestController::class, 'index'])->name('reservation-guests.index');
    Route::post('/reservations/{reservation}/guests', [ReservationGuestController::class, 'store']);
    Route::delete('/reservations/{reservation}/guests/{guest}', [ReservationGuestController::class, 'destroy']);

    // Reservation Rooms
    Route::get('/reservations/{reservation}/rooms', [ReservationRoomController::class, 'index'])->name('reservation-rooms.index');
    Route::post('/reservations/{reservation}/rooms', [ReservationRoomController::class, 'store']);
    Route::delete('/reservations/{reservation}/rooms/{subReservation}', [ReservationRoomController::class, 'destroy']);
    Route::get('/reservations/export', [ReservationController::class, 'export']);
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::post('/reservations/quick-create', [ReservationController::class, 'quickStore']);
    Route::get('/reservations/{reservation}', [ReservationController::class, 'show']);
    Route::put('/reservations/{reservation}', [ReservationController::class, 'update']);
    Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy']);
    Route::post('/reservations/{id}/check-in', [ReservationController::class, 'checkIn']);
    Route::post('/reservations/{id}/check-out', [ReservationController::class, 'checkOut']);
    
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
    Route::get('/reservations/{reservation}/balance', [UnitHousingController::class, 'getBalance']);
    Route::put('/units/{unit}/status', [UnitHousingController::class, 'updateStatus']);

    // Promissories
    Route::get('/promissories', [PromissoryController::class, 'index']);
    Route::get('/promissories/payment-logs', [PromissoryController::class, 'paymentLogs']);
    Route::post('/promissories/{promissory}/apply-payment', [PromissoryController::class, 'applyPayment']);
    Route::post('/promissories/payment-logs/{log}/reverse', [PromissoryController::class, 'reversePayment']);

    // AR Company Groups & City Ledger
    Route::apiResource('company-groups', CompanyGroupController::class);
    Route::post('/company-groups/{companyGroup}/link', [CompanyGroupController::class, 'linkCompany']);
    Route::post('/company-profiles/{companyProfile}/unlink', [CompanyGroupController::class, 'unlinkCompany']);
    Route::get('/city-ledger/dashboard', [CityLedgerController::class, 'dashboard']);
    Route::get('/city-ledger/aging', [CityLedgerController::class, 'agingReport']);
    Route::get('/city-ledger/export', [CityLedgerController::class, 'export']);

    // Invoice Transfers to AR
    Route::get('/ar/invoice-transfers', [\App\Http\Controllers\Api\InvoiceTransferController::class, 'index']);
    Route::get('/ar/invoice-transfers/export', [\App\Http\Controllers\Api\InvoiceTransferController::class, 'export']);
    Route::post('/ar/invoice-transfer', [\App\Http\Controllers\Api\InvoiceTransferController::class, 'store']);

    // Reports / Metabase
    Route::get('/reports/metabase/{id}', [\App\Http\Controllers\Api\MetabaseController::class, 'getDashboardUrl']);

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
    Route::get('/user-groups/teams', [UserGroupingController::class, 'teams']);


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
    Route::get('/pos/checkout', [PosController::class, 'checkout']);

    Route::get('/service-categories', [\App\Http\Controllers\Api\ServiceCategoryController::class, 'index']);
    Route::post('/service-categories', [\App\Http\Controllers\Api\ServiceCategoryController::class, 'store']);
    Route::get('/service-categories/users', [\App\Http\Controllers\Api\ServiceCategoryController::class, 'users']);
    Route::get('/service-categories/{serviceCategory}', [\App\Http\Controllers\Api\ServiceCategoryController::class, 'show']);
    Route::put('/service-categories/{serviceCategory}', [\App\Http\Controllers\Api\ServiceCategoryController::class, 'update']);
    Route::delete('/service-categories/{serviceCategory}', [\App\Http\Controllers\Api\ServiceCategoryController::class, 'destroy']);


    // Reports
    Route::get('/reports/deposits', [\App\Http\Controllers\Api\ReportsController::class, 'deposits']);
    Route::get('/reports/withdraws', [\App\Http\Controllers\Api\ReportsController::class, 'withdraws']);
    Route::get('/reports/safe-movement', [\App\Http\Controllers\Api\ReportsController::class, 'safeMovement']);
    Route::get('/reports/customer-movement', [\App\Http\Controllers\Api\ReportsController::class, 'customerMovement']);
    Route::get('/reports/services', [\App\Http\Controllers\Api\ReportsController::class, 'services']);
    Route::get('/reports/monthly', [\App\Http\Controllers\Api\ReportsController::class, 'monthly']);
    Route::get('/reports/units-movement', [\App\Http\Controllers\Api\ReportsController::class, 'unitsMovement']);
    Route::get('/reports/occupancy', [\App\Http\Controllers\Api\ReportsController::class, 'occupancy']);
    Route::get('/reports/cleaning', [\App\Http\Controllers\Api\ReportsController::class, 'cleaning']);
    Route::get('/reports/maintenance', [\App\Http\Controllers\Api\ReportsController::class, 'maintenance']);
    Route::get('/reports/transfers', [\App\Http\Controllers\Api\ReportsController::class, 'transfers']);
    Route::get('/reports/revenues', [\App\Http\Controllers\Api\ReportsController::class, 'revenues']);
    Route::get('/reports/resources', [\App\Http\Controllers\Api\ReportsController::class, 'resources']);
    Route::get('/reports/contracts', [\App\Http\Controllers\Api\ReportsController::class, 'contracts']);
    Route::get('/reports/invoices', [\App\Http\Controllers\Api\ReportsController::class, 'invoices']);
    Route::get('/reports/daily', [\App\Http\Controllers\Api\ReportsController::class, 'daily']);

    Route::get('/search', [SearchController::class, 'autocomplete']);

    // ── Rooms & Housekeeping Module ─────────────────────────────────
    Route::prefix('rooms-module')->group(function () {
        // 4.1 Units
        Route::get('/units', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'unitsIndex'])->name('units.index');
        Route::post('/units', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'unitsStore'])->name('units.store');
        Route::get('/units/{id}', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'unitsShow'])->name('units.show');
        Route::put('/units/{id}', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'unitsUpdate'])->name('units.update');
        Route::delete('/units/{id}', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'unitsDestroy'])->name('units.destroy');
        Route::post('/units/{id}/restore', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'unitsRestore'])->name('units.restore');

        // 4.2 Unit Categories
        Route::get('/unit-categories', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'categoriesIndex'])->name('unit-categories.index');
        Route::post('/unit-categories', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'categoriesStore'])->name('unit-categories.store');
        Route::get('/unit-categories/{id}', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'categoriesShow'])->name('unit-categories.show');
        Route::put('/unit-categories/{id}', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'categoriesUpdate'])->name('unit-categories.update');
        Route::delete('/unit-categories/{id}', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'categoriesDestroy'])->name('unit-categories.destroy');

        // 4.3 Availability Board
        Route::get('/availability-board', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'availabilityBoard'])->name('units.availability');

        // 4.4 Status Board
        Route::get('/status-board', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'statusBoard'])->name('units.status-board');
        Route::put('/units/{id}/status', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'updateUnitStatus'])->name('units.status.update');

        // 4.5 Housekeeping Board
        Route::get('/housekeeping-board', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'housekeepingBoard'])->name('housekeeping.board');
        Route::put('/housekeeping-tasks/{id}', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'updateHousekeepingTask'])->name('housekeeping.task.update');

        // 4.6 Unit Cleanings
        Route::get('/unit-cleanings', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'cleaningsIndex'])->name('unit-cleanings.index');
        Route::post('/unit-cleanings/{id}/start', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'startCleaning'])->name('unit-cleanings.start');
        Route::post('/unit-cleanings/{id}/complete', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'completeCleaning'])->name('unit-cleanings.complete');

        // 4.7 Maintenance Requests
        Route::get('/unit-maintenances', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'maintenancesIndex'])->name('unit-maintenances.index');
        Route::post('/unit-maintenances', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'maintenancesStore'])->name('unit-maintenances.store');
        Route::post('/unit-maintenances/{id}/assign', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'assignMaintenance'])->name('unit-maintenances.assign');
        Route::post('/unit-maintenances/{id}/complete', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'completeMaintenance'])->name('unit-maintenances.complete');

        // 4.8 Room Status Log
        Route::get('/room-status-log', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'statusLog'])->name('room-status-log.index');
        Route::get('/room-status-log/timeline/{unitId}', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'statusLogTimeline'])->name('room-status-log.timeline');

        // 4.9 Unit Media
        Route::get('/units/{unitId}/media', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'mediaIndex'])->name('unit-media.index');
        Route::post('/units/{unitId}/media', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'mediaStore'])->name('unit-media.store');
        Route::delete('/units/{unitId}/media/{mediaId}', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'mediaDestroy'])->name('unit-media.destroy');

        // 4.10 Unit Features
        Route::get('/unit-features', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'featuresIndex'])->name('unit-features.index');
        Route::post('/unit-features', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'featuresStore'])->name('unit-features.store');
        Route::put('/unit-features/{id}', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'featuresUpdate'])->name('unit-features.update');
        Route::delete('/unit-features/{id}', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'featuresDestroy'])->name('unit-features.destroy');

        // 4.11 Unit Options
        Route::get('/unit-options', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'optionsIndex'])->name('unit-options.index');
        Route::post('/unit-options', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'optionsStore'])->name('unit-options.store');
        Route::put('/unit-options/{id}', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'optionsUpdate'])->name('unit-options.update');
        Route::delete('/unit-options/{id}', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'optionsDestroy'])->name('unit-options.destroy');

        // 4.12 Unit Category Services
        Route::get('/unit-category-services', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'categoryServicesIndex'])->name('unit-category-services.index');
        Route::post('/unit-category-services', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'categoryServicesStore'])->name('unit-category-services.store');
        Route::delete('/unit-category-services/{id}', [\App\Http\Controllers\Api\RoomsHousekeepingController::class, 'categoryServicesDestroy'])->name('unit-category-services.destroy');
    });

    // ── Front Desk Module ─────────────────────────────────────────
    Route::prefix('front-desk')->group(function () {
        // 3.1 Check-in
        Route::get('/arrivals', [\App\Http\Controllers\Api\FrontDeskApiController::class, 'arrivals'])->name('front-desk.arrivals');
        Route::post('/check-in/{id}', [\App\Http\Controllers\Api\FrontDeskApiController::class, 'checkIn'])->name('front-desk.check-in.store');

        // 3.2 Check-out
        Route::get('/departures', [\App\Http\Controllers\Api\FrontDeskApiController::class, 'departures'])->name('front-desk.departures');
        Route::post('/check-out/{id}', [\App\Http\Controllers\Api\FrontDeskApiController::class, 'checkOut'])->name('front-desk.check-out.store');
        Route::get('/folio/{id}', [\App\Http\Controllers\Api\FrontDeskApiController::class, 'folio'])->name('front-desk.folio');

        // 3.3 Walk-in
        Route::post('/walk-in', [\App\Http\Controllers\Api\FrontDeskApiController::class, 'walkIn'])->name('front-desk.walk-in.store');

        // 3.4 Guest Registration
        Route::post('/registration/{reservationId}', [\App\Http\Controllers\Api\FrontDeskApiController::class, 'saveRegistration'])->name('front-desk.registration.store');

        // 3.5 Room Assignment
        Route::get('/unassigned-reservations', [\App\Http\Controllers\Api\FrontDeskApiController::class, 'unassignedReservations'])->name('front-desk.unassigned');
        Route::get('/available-rooms', [\App\Http\Controllers\Api\FrontDeskApiController::class, 'availableRooms'])->name('front-desk.available-rooms');
        Route::post('/room-assignment', [\App\Http\Controllers\Api\FrontDeskApiController::class, 'assignRoom'])->name('front-desk.room-assignment.store');

        // 3.6 Room Swap
        Route::post('/room-swap', [\App\Http\Controllers\Api\FrontDeskApiController::class, 'swapRoom'])->name('front-desk.room-swap');

        // 3.7 Early Check-in
        Route::get('/early-check-in/charge', [\App\Http\Controllers\Api\FrontDeskApiController::class, 'earlyCheckinCharge'])->name('front-desk.early-check-in.charge');
        Route::post('/early-check-in', [\App\Http\Controllers\Api\FrontDeskApiController::class, 'applyEarlyCheckin'])->name('front-desk.early-check-in.store');

        // 3.8 Late Checkout
        Route::get('/late-checkout/charge', [\App\Http\Controllers\Api\FrontDeskApiController::class, 'lateCheckoutCharge'])->name('front-desk.late-checkout.charge');
        Route::post('/late-checkout', [\App\Http\Controllers\Api\FrontDeskApiController::class, 'applyLateCheckout'])->name('front-desk.late-checkout.store');

        // 3.9 No-Show
        Route::post('/no-show/{id}', [\App\Http\Controllers\Api\FrontDeskApiController::class, 'markNoShow'])->name('front-desk.no-show');

        // 3.10 Wake-up Calls
        Route::get('/wake-up-calls', [\App\Http\Controllers\Api\FrontDeskApiController::class, 'wakeUpCalls'])->name('wake-up-calls.index');
        Route::post('/wake-up-calls', [\App\Http\Controllers\Api\FrontDeskApiController::class, 'storeWakeUpCall'])->name('wake-up-calls.store');
        Route::put('/wake-up-calls/{id}', [\App\Http\Controllers\Api\FrontDeskApiController::class, 'updateWakeUpCall'])->name('wake-up-calls.update');
        Route::post('/wake-up-calls/{id}/complete', [\App\Http\Controllers\Api\FrontDeskApiController::class, 'completeWakeUpCall'])->name('wake-up-calls.complete');
        Route::delete('/wake-up-calls/{id}', [\App\Http\Controllers\Api\FrontDeskApiController::class, 'deleteWakeUpCall'])->name('wake-up-calls.destroy');

        // 3.11 IPTV Guest Needs
        Route::get('/iptv-needs', [\App\Http\Controllers\Api\FrontDeskApiController::class, 'iptvNeeds'])->name('iptv-needs.index');
        Route::post('/iptv-needs', [\App\Http\Controllers\Api\FrontDeskApiController::class, 'storeIptvNeed'])->name('iptv-needs.store');
        Route::put('/iptv-needs/{id}', [\App\Http\Controllers\Api\FrontDeskApiController::class, 'updateIptvNeed'])->name('iptv-needs.update');
        Route::delete('/iptv-needs/{id}', [\App\Http\Controllers\Api\FrontDeskApiController::class, 'deleteIptvNeed'])->name('iptv-needs.destroy');

        // 3.12 Registration Cards
        Route::get('/registration-cards', [\App\Http\Controllers\Api\FrontDeskApiController::class, 'registrationCards'])->name('front-desk.registration-cards');
        Route::get('/registration-cards/{id}', [\App\Http\Controllers\Api\FrontDeskApiController::class, 'registrationCardData'])->name('front-desk.registration-cards.show');

        // 3.13 Balance Transfer
        Route::post('/balance-transfer', [\App\Http\Controllers\Api\FrontDeskApiController::class, 'balanceTransfer'])->name('front-desk.balance-transfer');
    });

    // Admin Lead Routes
    Route::get('/leads', [LeadController::class, 'index']);

    // ── POS & Services Module ──────────────────────────────────
    Route::prefix('pos-module')->group(function () {
        // 6.1 POS Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Api\PosServicesController::class, 'dashboard'])->name('pos.dashboard');

        // 6.2 Service Categories
        Route::get('/service-categories', [\App\Http\Controllers\Api\PosServicesController::class, 'categoriesIndex'])->name('service-categories.index');
        Route::post('/service-categories', [\App\Http\Controllers\Api\PosServicesController::class, 'categoriesStore'])->name('service-categories.store');
        Route::put('/service-categories/{id}', [\App\Http\Controllers\Api\PosServicesController::class, 'categoriesUpdate'])->name('service-categories.update');
        Route::delete('/service-categories/{id}', [\App\Http\Controllers\Api\PosServicesController::class, 'categoriesDestroy'])->name('service-categories.destroy');

        // 6.3 Services
        Route::get('/services', [\App\Http\Controllers\Api\PosServicesController::class, 'servicesIndex'])->name('services.index');
        Route::post('/services', [\App\Http\Controllers\Api\PosServicesController::class, 'servicesStore'])->name('services.store');
        Route::get('/services/{id}', [\App\Http\Controllers\Api\PosServicesController::class, 'servicesShow'])->name('services.show');
        Route::put('/services/{id}', [\App\Http\Controllers\Api\PosServicesController::class, 'servicesUpdate'])->name('services.update');
        Route::delete('/services/{id}', [\App\Http\Controllers\Api\PosServicesController::class, 'servicesDestroy'])->name('services.destroy');

        // 6.4 POS Sale
        Route::post('/sale', [\App\Http\Controllers\Api\PosServicesController::class, 'saleStore'])->name('pos.sale.store');

        // 6.5 Service Logs
        Route::get('/service-logs', [\App\Http\Controllers\Api\PosServicesController::class, 'serviceLogsIndex'])->name('service-logs.index');
        Route::get('/service-logs/{id}', [\App\Http\Controllers\Api\PosServicesController::class, 'serviceLogsShow'])->name('service-logs.show');

        // 6.6 Service Log Notes
        Route::post('/service-logs/{logId}/notes', [\App\Http\Controllers\Api\PosServicesController::class, 'serviceLogNoteStore'])->name('service-log-notes.store');

        // 6.7 Quick Payments
        Route::get('/quick-payments', [\App\Http\Controllers\Api\PosServicesController::class, 'quickPaymentsIndex'])->name('quick-payments.index');
        Route::post('/quick-payments', [\App\Http\Controllers\Api\PosServicesController::class, 'quickPaymentsStore'])->name('quick-payments.store');

        // 6.8 POS Transactions
        Route::get('/pos-transactions', [\App\Http\Controllers\Api\PosServicesController::class, 'posTransactionsIndex'])->name('pos-transactions.index');
        Route::post('/pos-transactions/{id}/void', [\App\Http\Controllers\Api\PosServicesController::class, 'voidTransaction'])->name('pos-transactions.void');
        Route::post('/pos-transactions/{id}/refund', [\App\Http\Controllers\Api\PosServicesController::class, 'refundTransaction'])->name('pos-transactions.refund');

        // 6.9 Reservation Services
        Route::get('/reservations/{reservationId}/services', [\App\Http\Controllers\Api\PosServicesController::class, 'reservationServicesIndex'])->name('reservation-services.index');
        Route::post('/reservations/{reservationId}/services', [\App\Http\Controllers\Api\PosServicesController::class, 'reservationServicesStore'])->name('reservation-services.store');

        // 6.10 Qoyod Mapping
        Route::get('/service-qoyods', [\App\Http\Controllers\Api\PosServicesController::class, 'qoyodIndex'])->name('service-qoyods.index');
        Route::post('/service-qoyods', [\App\Http\Controllers\Api\PosServicesController::class, 'qoyodStore'])->name('service-qoyods.store');
        Route::put('/service-qoyods/{id}', [\App\Http\Controllers\Api\PosServicesController::class, 'qoyodUpdate'])->name('service-qoyods.update');
        Route::delete('/service-qoyods/{id}', [\App\Http\Controllers\Api\PosServicesController::class, 'qoyodDestroy'])->name('service-qoyods.destroy');
    });

    // ── Guests & Companies Module ────────────────────────────────
    Route::prefix('guests-module')->group(function () {
        // 5.1 Guest Directory
        Route::get('/guests', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'guestsIndex'])->name('guests.index');
        Route::get('/guests/{id}', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'guestsShow'])->name('guests.show');

        // 5.2 Customer Profiles
        Route::get('/customers', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'customersIndex'])->name('customers.index');
        Route::post('/customers', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'customersStore'])->name('customers.store');
        Route::get('/customers/{id}', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'customersShow'])->name('customers.show');
        Route::put('/customers/{id}', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'customersUpdate'])->name('customers.update');
        Route::delete('/customers/{id}', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'customersDestroy'])->name('customers.destroy');
        Route::post('/customers/{id}/restore', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'customersRestore'])->name('customers.restore');
        Route::post('/customers/merge', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'mergeCustomers'])->name('customers.merge');

        // 5.3 Companies
        Route::get('/companies', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'companiesIndex'])->name('companies.index');
        Route::post('/companies', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'companiesStore'])->name('companies.store');
        Route::get('/companies/{id}', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'companiesShow'])->name('companies.show');
        Route::put('/companies/{id}', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'companiesUpdate'])->name('companies.update');
        Route::delete('/companies/{id}', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'companiesDestroy'])->name('companies.destroy');

        // 5.4 Company Groups
        Route::get('/company-groups', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'groupsIndex'])->name('company-groups.index');
        Route::post('/company-groups', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'groupsStore'])->name('company-groups.store');
        Route::get('/company-groups/{id}', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'groupsShow'])->name('company-groups.show');
        Route::put('/company-groups/{id}', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'groupsUpdate'])->name('company-groups.update');
        Route::delete('/company-groups/{id}', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'groupsDestroy'])->name('company-groups.destroy');
        Route::get('/company-groups/{id}/exposure', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'groupsExposure'])->name('company-groups.exposure');

        // 5.5 Company Notes
        Route::get('/companies/{companyId}/notes', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'companyNotesIndex'])->name('company-notes.index');
        Route::post('/companies/{companyId}/notes', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'companyNotesStore'])->name('company-notes.store');
        Route::delete('/companies/{companyId}/notes/{noteId}', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'companyNotesDestroy'])->name('company-notes.destroy');

        // 5.6 Blocked Guests
        Route::get('/blocked-guests', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'blockedIndex'])->name('blocked-guests.index');
        Route::post('/blocked-guests', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'blockedStore'])->name('blocked-guests.store');
        Route::delete('/blocked-guests/{id}', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'blockedDestroy'])->name('blocked-guests.destroy');

        // 5.7 Turnaway Logs
        Route::get('/turnaway-logs', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'turnawayLogsIndex'])->name('turnaway-logs.index');
        Route::post('/turnaway-logs', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'turnawayLogsStore'])->name('turnaway-logs.store');

        // 5.8 Turnaway Reasons
        Route::get('/turnaway-reasons', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'turnawayReasonsIndex'])->name('turnaway-reasons.index');
        Route::post('/turnaway-reasons', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'turnawayReasonsStore'])->name('turnaway-reasons.store');
        Route::put('/turnaway-reasons/{id}', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'turnawayReasonsUpdate'])->name('turnaway-reasons.update');
        Route::delete('/turnaway-reasons/{id}', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'turnawayReasonsDestroy'])->name('turnaway-reasons.destroy');

        // 5.9 Customer Notes
        Route::get('/customers/{customerId}/notes', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'customerNotesIndex'])->name('customer-notes.index');
        Route::post('/customers/{customerId}/notes', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'customerNotesStore'])->name('customer-notes.store');
        Route::delete('/customers/{customerId}/notes/{noteId}', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'customerNotesDestroy'])->name('customer-notes.destroy');

        // 5.10 Comments
        Route::get('/comments', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'commentsIndex'])->name('comments.index');
        Route::post('/comments', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'commentsStore'])->name('comments.store');
        Route::delete('/comments/{id}', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'commentsDestroy'])->name('comments.destroy');

        // 5.11 Highlights
        Route::get('/highlights', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'highlightsIndex'])->name('highlights.index');
        Route::post('/highlights', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'highlightsStore'])->name('highlights.store');
        Route::put('/highlights/{id}', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'highlightsUpdate'])->name('highlights.update');
        Route::delete('/highlights/{id}', [\App\Http\Controllers\Api\GuestsCompaniesController::class, 'highlightsDestroy'])->name('highlights.destroy');
    });
    Route::get('/leads/stats', [LeadController::class, 'stats']);
    Route::put('/leads/{lead}', [LeadController::class, 'update']);
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy']);
});

// Public Lead Submission
Route::post('/leads/submit', [LeadController::class, 'store']);
