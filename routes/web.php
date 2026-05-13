<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontDeskController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReservationCalendarController;
use App\Http\Controllers\ArrivalsDeparturesController;
use App\Http\Controllers\GroupReservationController;
use App\Http\Controllers\Nova\LocaleController;

// Language switching route
Route::get('/locale/{locale}', [LocaleController::class, 'handle'])->name('locale.switch');

// Test route to check current locale
Route::get('/test-locale', function () {
    return [
        'current_locale' => app()->getLocale(),
        'session_locale' => session('locale'),
        'available_locales' => config('nova.locales'),
        'default_locale' => config('nova.default_locale'),
    ];
});

Route::fallback(function () {
    return view('app');
});

// Reservation Routes
Route::prefix('reservations')->group(function () {
    Route::get('/', [ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/create', [ReservationController::class, 'create'])->name('reservations.create');
    Route::post('/', [ReservationController::class, 'store'])->name('reservations.store');
    Route::get('/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');
    Route::get('/{reservation}/edit', [ReservationController::class, 'edit'])->name('reservations.edit');
    Route::put('/{reservation}', [ReservationController::class, 'update'])->name('reservations.update');
    Route::delete('/{reservation}', [ReservationController::class, 'destroy'])->name('reservations.destroy');
    
    // Reservation actions
    Route::post('/bulk-actions', [ReservationController::class, 'bulkActions'])->name('reservations.bulk.actions');
    Route::post('/{reservation}/check-in', [ReservationController::class, 'checkIn'])->name('reservations.check.in');
    Route::post('/{reservation}/check-out', [ReservationController::class, 'checkOut'])->name('reservations.check.out');
    Route::post('/{reservation}/extend', [ReservationController::class, 'extend'])->name('reservations.extend');
    Route::post('/{reservation}/no-show', [ReservationController::class, 'markNoShow'])->name('reservations.no.show');
    Route::post('/{reservation}/transfer', [ReservationController::class, 'transfer'])->name('reservations.transfer');
});

// Calendar Routes
Route::prefix('calendar')->group(function () {
    Route::get('/reservations', [ReservationCalendarController::class, 'index'])->name('calendar.reservations.index');
    Route::get('/reservations/events', [ReservationCalendarController::class, 'getCalendarEvents'])->name('calendar.reservations.events');
});

// Arrivals & Departures Routes
Route::prefix('arrivals-departures')->group(function () {
    Route::get('/', [ArrivalsDeparturesController::class, 'index'])->name('arrivals.departures.index');
    Route::get('/data', [ArrivalsDeparturesController::class, 'getArrivalsDeparturesData'])->name('arrivals.departures.data');
});

// Group Reservation Routes
Route::prefix('group-reservations')->group(function () {
    Route::get('/', [GroupReservationController::class, 'index'])->name('group.reservations.index');
    Route::get('/create', [GroupReservationController::class, 'create'])->name('group.reservations.create');
    Route::post('/', [GroupReservationController::class, 'store'])->name('group.reservations.store');
    Route::get('/{groupReservation}', [GroupReservationController::class, 'show'])->name('group.reservations.show');
    Route::get('/{groupReservation}/edit', [GroupReservationController::class, 'edit'])->name('group.reservations.edit');
    Route::put('/{groupReservation}', [GroupReservationController::class, 'update'])->name('group.reservations.update');
    Route::delete('/{groupReservation}', [GroupReservationController::class, 'destroy'])->name('group.reservations.destroy');
});

// Front Desk Routes
Route::prefix('front-desk')->group(function () {
    Route::post('/check-in/{reservationId}', [FrontDeskController::class, 'checkIn']);
    Route::post('/check-out/{reservationId}', [FrontDeskController::class, 'checkOut']);
    Route::post('/reservation/{reservationId}/guest', [FrontDeskController::class, 'addGuest']);
    Route::put('/guest/{guestId}', [FrontDeskController::class, 'updateGuest']);
    Route::delete('/guest/{guestId}', [FrontDeskController::class, 'deleteGuest']);
    Route::post('/validate-shomoos-id', [FrontDeskController::class, 'validateShomoosId']);
    Route::post('/assign-room', [FrontDeskController::class, 'assignRoom']);
    Route::post('/walk-in-booking', [FrontDeskController::class, 'createWalkInBooking']);
    Route::put('/reservation/{reservationId}/extend', [FrontDeskController::class, 'extendReservation']);
    Route::post('/reservation/{reservationId}/no-show', [FrontDeskController::class, 'handleNoShow']);
    Route::post('/iptv-request', [FrontDeskController::class, 'createIptvRequest']);
    Route::post('/iptv-request/{requestId}/mark-treated', [FrontDeskController::class, 'markIptvRequestAsTreated']);
});

Route::prefix('finance')->name('finance.')->middleware(['auth'])->group(function () {
    // Receipts Routes
    Route::get('/receipts/export', [App\Http\Controllers\Finance\ReceiptController::class, 'export'])->name('receipts.export');
    Route::resource('receipts', App\Http\Controllers\Finance\ReceiptController::class);
    Route::post('/receipts/{receipt}/cancel', [App\Http\Controllers\Finance\ReceiptController::class, 'cancel'])->name('receipts.cancel');
    Route::get('/receipts/{receipt}/print', [App\Http\Controllers\Finance\ReceiptController::class, 'print'])->name('receipts.print');
    Route::post('/receipts/{receipt}/confirm', [App\Http\Controllers\Finance\ReceiptController::class, 'confirm'])->name('receipts.confirm');

    // Payments Routes
    Route::get('/payments/export', [App\Http\Controllers\Finance\PaymentController::class, 'export'])->name('payments.export');
    Route::resource('payments', App\Http\Controllers\Finance\PaymentController::class);
    Route::post('/payments/{payment}/confirm', [App\Http\Controllers\Finance\PaymentController::class, 'confirm'])->name('payments.confirm');
    Route::post('/payments/{payment}/cancel', [App\Http\Controllers\Finance\PaymentController::class, 'cancel'])->name('payments.cancel');
    Route::post('/payments/{payment}/reverse', [App\Http\Controllers\Finance\PaymentController::class, 'reverse'])->name('payments.reverse');
    Route::get('/payments/{payment}/print', [App\Http\Controllers\Finance\PaymentController::class, 'print'])->name('payments.print');

    // Invoices Routes
    Route::get('/invoices/export', [App\Http\Controllers\Finance\InvoiceController::class, 'export'])->name('invoices.export');
    Route::resource('invoices', App\Http\Controllers\Finance\InvoiceController::class);
    Route::post('/invoices/{invoice}/send-to-zatca', [App\Http\Controllers\Finance\InvoiceController::class, 'sendToZatca'])->name('invoices.zatca_submit');
    Route::get('/invoices/{invoice}/download-xml', [App\Http\Controllers\Finance\InvoiceController::class, 'downloadXml'])->name('invoices.zatca_download');
    Route::get('/invoices/{invoice}/download-pdf', [App\Http\Controllers\Finance\InvoiceController::class, 'downloadPdf'])->name('invoices.download_pdf');
    Route::get('/invoices/{invoice}/print', [App\Http\Controllers\Finance\InvoiceController::class, 'print'])->name('invoices.print');
    Route::post('/invoices/{invoice}/cancel', [App\Http\Controllers\Finance\InvoiceController::class, 'cancel'])->name('invoices.cancel');
    Route::post('/invoices/{invoice}/mark-as-paid', [App\Http\Controllers\Finance\InvoiceController::class, 'markAsPaid'])->name('invoices.mark_paid');

    // Credit Notes Routes
    Route::get('/credit-notes/export', [App\Http\Controllers\Finance\CreditNoteController::class, 'export'])->name('credit-notes.export');
    Route::resource('credit-notes', App\Http\Controllers\Finance\CreditNoteController::class);
    Route::post('/credit-notes/{credit_note}/submit-to-zatca', [App\Http\Controllers\Finance\CreditNoteController::class, 'submitToZatca'])->name('credit-notes.zatca_submit');
    Route::get('/credit-notes/{credit_note}/download-xml', [App\Http\Controllers\Finance\CreditNoteController::class, 'downloadXml'])->name('credit-notes.zatca_download');
    Route::post('/credit-notes/{credit_note}/cancel', [App\Http\Controllers\Finance\CreditNoteController::class, 'cancel'])->name('credit-notes.cancel');

    // Invoice Transfers Routes
    Route::resource('invoice-transfers', App\Http\Controllers\Finance\InvoiceTransferController::class);
    Route::post('/invoice-transfers/{invoice_transfer}/approve', [App\Http\Controllers\Finance\InvoiceTransferController::class, 'approve'])->name('invoice-transfers.approve');
    Route::post('/invoice-transfers/{invoice_transfer}/reject', [App\Http\Controllers\Finance\InvoiceTransferController::class, 'reject'])->name('invoice-transfers.reject');

    // Promissory Notes Routes
    Route::resource('promissory-notes', App\Http\Controllers\Finance\PromissoryNoteController::class);
    Route::post('/promissory-notes/{promissory_note}/renew', [App\Http\Controllers\Finance\PromissoryNoteController::class, 'renew'])->name('promissory-notes.renew');
    Route::post('/promissory-notes/{promissory_note}/cancel', [App\Http\Controllers\Finance\PromissoryNoteController::class, 'cancel'])->name('promissory-notes.cancel');
    
    // Promissory Collections Routes
    Route::get('/promissory-collections', [App\Http\Controllers\Finance\PromissoryCollectionController::class, 'index'])->name('promissory-collections.index');
    Route::post('/promissory-notes/{promissory_note}/collect', [App\Http\Controllers\Finance\PromissoryCollectionController::class, 'store'])->name('promissory-collections.store');
    Route::post('/promissory-collections/{promissory_collection}/reverse', [App\Http\Controllers\Finance\PromissoryCollectionController::class, 'reverse'])->name('promissory-collections.reverse');

    // Banks Routes
    Route::resource('banks', App\Http\Controllers\Finance\BankController::class);
    Route::post('/banks/{bank}/toggle-active', [App\Http\Controllers\Finance\BankController::class, 'toggleActive'])->name('banks.toggle-active');

    // Senders Routes
    Route::resource('senders', App\Http\Controllers\Finance\SenderController::class);
    Route::post('/senders/{sender}/toggle-active', [App\Http\Controllers\Finance\SenderController::class, 'toggleActive'])->name('senders.toggle-active');

    // Commission Payments Routes
    Route::get('/commission-payments/calculate', [App\Http\Controllers\Finance\CommissionPaymentController::class, 'calculate'])->name('commission-payments.calculate');
    Route::post('/commission-payments/pay', [App\Http\Controllers\Finance\CommissionPaymentController::class, 'pay'])->name('commission-payments.pay');
    Route::get('/commission-payments/export', [App\Http\Controllers\Finance\CommissionPaymentController::class, 'export'])->name('commission-payments.export');
    Route::resource('commission-payments', App\Http\Controllers\Finance\CommissionPaymentController::class);

    // Payment Correction Routes
    Route::resource('payment-corrections', App\Http\Controllers\Finance\PaymentCorrectionController::class);
    Route::post('/payment-corrections/{payment_correction}/approve', [App\Http\Controllers\Finance\PaymentCorrectionController::class, 'approve'])->name('payment-corrections.approve');
    Route::post('/payment-corrections/{payment_correction}/reject', [App\Http\Controllers\Finance\PaymentCorrectionController::class, 'reject'])->name('payment-corrections.reject');

    // Paid-outs Routes
    Route::resource('paid-outs', App\Http\Controllers\Finance\PaidOutController::class);
    Route::post('/paid-outs/{paid_out}/approve', [App\Http\Controllers\Finance\PaidOutController::class, 'approve'])->name('paid-outs.approve');
    Route::post('/paid-outs/{paid_out}/reject', [App\Http\Controllers\Finance\PaidOutController::class, 'reject'])->name('paid-outs.reject');

    // Qoyod Sync Routes
    Route::get('/qoyod-sync', [App\Http\Controllers\Finance\QoyodSyncController::class, 'index'])->name('qoyod-sync.index');
    Route::post('/qoyod-sync', [App\Http\Controllers\Finance\QoyodSyncController::class, 'sync'])->name('qoyod-sync.sync');

    // Trial Balance Routes
    Route::get('/reports/trial-balance', [App\Http\Controllers\Finance\TrialBalanceController::class, 'index'])->name('reports.trial-balance');
    Route::get('/reports/trial-balance/export', [App\Http\Controllers\Finance\TrialBalanceController::class, 'export'])->name('reports.trial-balance.export');

    // Cashier Shifts Routes
    Route::get('/cashier-shifts/my-shift', [App\Http\Controllers\Finance\CashierShiftController::class, 'myShift'])->name('cashier-shifts.my-shift');
    Route::get('/cashier-shifts/open', [App\Http\Controllers\Finance\CashierShiftController::class, 'open'])->name('cashier-shifts.open');
    Route::post('/cashier-shifts/open', [App\Http\Controllers\Finance\CashierShiftController::class, 'store'])->name('cashier-shifts.store');
    Route::get('/cashier-shifts/export', [App\Http\Controllers\Finance\CashierShiftController::class, 'export'])->name('cashier-shifts.export');
    Route::get('/cashier-shifts/{cashier_shift}/report', [App\Http\Controllers\Finance\CashierShiftController::class, 'report'])->name('cashier-shifts.report');
    Route::post('/cashier-shifts/{cashier_shift}/close', [App\Http\Controllers\Finance\CashierShiftController::class, 'close'])->name('cashier-shifts.close');
    Route::post('/cashier-shifts/{cashier_shift}/approve', [App\Http\Controllers\Finance\CashierShiftController::class, 'approve'])->name('cashier-shifts.approve');
    Route::post('/cashier-shifts/{cashier_shift}/reject', [App\Http\Controllers\Finance\CashierShiftController::class, 'reject'])->name('cashier-shifts.reject');
    Route::resource('cashier-shifts', App\Http\Controllers\Finance\CashierShiftController::class)->except(['create', 'store']);
});

// Reports Routes
Route::middleware(['auth'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [App\Http\Controllers\Report\ReportController::class, 'index'])->name('dashboard');
    
    // Daily Report
    Route::get('/daily', [App\Http\Controllers\Report\DailyReportController::class, 'index'])->name('daily');
    Route::get('/daily/generate', [App\Http\Controllers\Report\DailyReportController::class, 'generate'])->name('daily.generate');
    Route::get('/daily/export', [App\Http\Controllers\Report\DailyReportController::class, 'export'])->name('daily.export');

    // Occupancy Report
    Route::get('/occupancy', [App\Http\Controllers\Report\OccupancyReportController::class, 'index'])->name('occupancy');
    Route::get('/occupancy/generate', [App\Http\Controllers\Report\OccupancyReportController::class, 'generate'])->name('occupancy.generate');

    // Revenue Report
    Route::get('/revenue', [App\Http\Controllers\Report\RevenueReportController::class, 'index'])->name('revenue');
    Route::get('/revenue/generate', [App\Http\Controllers\Report\RevenueReportController::class, 'generate'])->name('revenue.generate');

    // ADR RevPAR Report
    Route::get('/adr-revpar', [App\Http\Controllers\Report\AdrRevparReportController::class, 'index'])->name('adr-revpar.index');
    Route::get('/adr-revpar/generate', [App\Http\Controllers\Report\AdrRevparReportController::class, 'generate'])->name('adr-revpar.generate');
    Route::get('/adr-revpar/export', [App\Http\Controllers\Report\AdrRevparReportController::class, 'export'])->name('adr-revpar.export');

    // Custom Reports
    Route::resource('custom-reports', App\Http\Controllers\Report\CustomReportController::class);
    Route::get('/custom-reports/{customReport}/run', [App\Http\Controllers\Report\CustomReportController::class, 'run'])->name('custom-reports.run');
    Route::get('/custom-reports/{customReport}/export', [App\Http\Controllers\Report\CustomReportController::class, 'export'])->name('custom-reports.export');
    Route::post('/custom-reports/preview', [App\Http\Controllers\Report\CustomReportController::class, 'preview'])->name('custom-reports.preview');
    Route::get('/custom-reports/columns/{module}', [App\Http\Controllers\Report\CustomReportController::class, 'availableColumns'])->name('custom-reports.columns');

    // Report Schedules
    Route::resource('report-schedules', App\Http\Controllers\Report\ReportScheduleController::class);
    Route::post('/report-schedules/{reportSchedule}/toggle', [App\Http\Controllers\Report\ReportScheduleController::class, 'toggle'])->name('report-schedules.toggle');
    Route::post('/report-schedules/{reportSchedule}/run-now', [App\Http\Controllers\Report\ReportScheduleController::class, 'runNow'])->name('report-schedules.run-now');
    Route::post('/report-schedules/{reportSchedule}/test-email', [App\Http\Controllers\Report\ReportScheduleController::class, 'testEmail'])->name('report-schedules.test-email');
});

// Integrations Routes
Route::middleware(['auth'])->prefix('integrations')->name('integrations.')->group(function () {
    // Main Integration CRUD
    Route::resource('integrations', App\Http\Controllers\Integration\IntegrationController::class);
    Route::post('/integrations/{integration}/test', [App\Http\Controllers\Integration\IntegrationController::class, 'test'])->name('integrations.test');
    Route::post('/integrations/{integration}/sync', [App\Http\Controllers\Integration\IntegrationController::class, 'sync'])->name('integrations.sync');
    Route::post('/integrations/{integration}/activate', [App\Http\Controllers\Integration\IntegrationController::class, 'activate'])->name('integrations.activate');
    Route::post('/integrations/{integration}/deactivate', [App\Http\Controllers\Integration\IntegrationController::class, 'deactivate'])->name('integrations.deactivate');
    Route::post('/integrations/{integration}/reset-credentials', [App\Http\Controllers\Integration\IntegrationController::class, 'resetCredentials'])->name('integrations.reset-credentials');

    // Integration Settings
    Route::get('/integrations/{integration}/settings', [App\Http\Controllers\Integration\IntegrationSettingController::class, 'index'])->name('settings.index');
    Route::post('/integrations/{integration}/settings', [App\Http\Controllers\Integration\IntegrationSettingController::class, 'update'])->name('settings.update');

    // Integration Logs
    Route::get('/logs', [App\Http\Controllers\Integration\IntegrationLogController::class, 'index'])->name('logs.index');
    Route::get('/logs/{integrationLog}', [App\Http\Controllers\Integration\IntegrationLogController::class, 'show'])->name('logs.show');
    Route::post('/logs/clear', [App\Http\Controllers\Integration\IntegrationLogController::class, 'clear'])->name('logs.clear');
    Route::get('/logs/export', [App\Http\Controllers\Integration\IntegrationLogController::class, 'export'])->name('logs.export');

    // Form Integrations
    Route::resource('form-integrations', App\Http\Controllers\Integration\FormIntegrationController::class);
    Route::post('/form-integrations/{formIntegration}/approve', [App\Http\Controllers\Integration\FormIntegrationController::class, 'approve'])->name('form-integrations.approve');
    Route::post('/form-integrations/{formIntegration}/reject', [App\Http\Controllers\Integration\FormIntegrationController::class, 'reject'])->name('form-integrations.reject');
    Route::post('/form-integrations/{formIntegration}/test-webhook', [App\Http\Controllers\Integration\FormIntegrationController::class, 'testWebhook'])->name('form-integrations.test-webhook');

    // API Consumers
    Route::resource('api-consumers', App\Http\Controllers\Integration\ApiConsumerController::class);
    Route::post('/api-consumers/{apiConsumer}/toggle-active', [App\Http\Controllers\Integration\ApiConsumerController::class, 'toggleActive'])->name('api-consumers.toggle-active');
    Route::get('/api-consumers/{apiConsumer}/usage-stats', [App\Http\Controllers\Integration\ApiConsumerController::class, 'usageStats'])->name('api-consumers.usage-stats');

    // API Tokens
    Route::resource('api-tokens', App\Http\Controllers\Integration\ApiTokenController::class);
    Route::post('/api-tokens/{apiToken}/regenerate', [App\Http\Controllers\Integration\ApiTokenController::class, 'regenerate'])->name('api-tokens.regenerate');
    Route::get('/api-tokens/{apiToken}/usage-stats', [App\Http\Controllers\Integration\ApiTokenController::class, 'usageStats'])->name('api-tokens.usage-stats');
});

