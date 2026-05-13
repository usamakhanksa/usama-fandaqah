<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Finance\TransactionController;
use App\Http\Controllers\Finance\ReceiptController;
use App\Http\Controllers\Finance\PaymentController;
use App\Http\Controllers\Finance\InvoiceController;
// use App\Http\Controllers\Finance\ReservationInvoiceController;
// use App\Http\Controllers\Finance\InvoiceCreditNoteController;
// use App\Http\Controllers\Finance\InvoiceTransferController;
// use App\Http\Controllers\Finance\PromissoryController;
// use App\Http\Controllers\Finance\CashierShiftController;
// use App\Http\Controllers\Finance\CheckoutBalanceTransferController;
// use App\Http\Controllers\Finance\BankController;
// use App\Http\Controllers\Finance\SenderController;
// use App\Http\Controllers\Finance\CommissionPaymentController;
// use App\Http\Controllers\Finance\ZatcaController;
// use App\Http\Controllers\Finance\QoyodController;

/*
|--------------------------------------------------------------------------
| Finance & Accounting Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'team.scope'])->group(function () {
    
    // Transactions
    Route::resource('transactions', TransactionController::class)
        ->names('transactions');
    
    Route::post('transactions/{transaction}/reverse', [TransactionController::class, 'reverse'])
        ->name('transactions.reverse')
        ->middleware('can:transactions.reverse');
    
    Route::post('transactions/{transaction}/correct', [TransactionController::class, 'correct'])
        ->name('transactions.correct')
        ->middleware('can:transactions.correct');
    
    Route::post('transactions/{transaction}/void', [TransactionController::class, 'void'])
        ->name('transactions.void')
        ->middleware('can:transactions.void');
    
    Route::get('transactions/export', [TransactionController::class, 'export'])
        ->name('transactions.export')
        ->middleware('can:transactions.export');
    
    // Receipts
    Route::resource('receipts', ReceiptController::class)
        ->names('finance.receipts');
    
    Route::post('receipts/{receipt}/confirm', [ReceiptController::class, 'confirm'])
        ->name('finance.receipts.confirm');

    Route::post('receipts/{receipt}/void', [ReceiptController::class, 'void'])
        ->name('finance.receipts.void');

    Route::post('receipts/{receipt}/cancel', [ReceiptController::class, 'cancel'])
        ->name('finance.receipts.cancel')
        ->middleware('can:receipts.cancel');

    Route::get('receipts/{receipt}/print', [ReceiptController::class, 'print'])
        ->name('finance.receipts.print')
        ->middleware('can:receipts.print');
    
    Route::get('receipts/export', [ReceiptController::class, 'export'])
        ->name('finance.receipts.export')
        ->middleware('can:receipts.export');

    // Payments
    Route::resource('payments', PaymentController::class)
        ->names('finance.payments');
    
    Route::post('payments/{payment}/complete', [PaymentController::class, 'complete'])
        ->name('finance.payments.complete');
    
    Route::post('payments/{payment}/reverse', [PaymentController::class, 'reverse'])
        ->name('finance.payments.reverse');
    
    Route::get('payments/{payment}/print', [PaymentController::class, 'print'])
        ->name('finance.payments.print');
    
    Route::get('payments/export', [PaymentController::class, 'export'])
        ->name('finance.payments.export');
    
    Route::get('payments-daily-summary', [PaymentController::class, 'dailySummary'])
        ->name('finance.payments.daily-summary');
    
    // Invoices
    Route::resource('invoices', InvoiceController::class)
        ->names('finance.invoices');
    
    Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send'])
        ->name('finance.invoices.send')
        ->middleware('can:invoices.send');
    
    Route::post('invoices/{invoice}/send-to-zatca', [InvoiceController::class, 'sendToZatca'])
        ->name('finance.invoices.send-to-zatca')
        ->middleware('can:invoices.send_zatca');
    
    Route::get('invoices/{invoice}/zatca-status', [InvoiceController::class, 'zatcaStatus'])
        ->name('finance.invoices.zatca-status');
    
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])
        ->name('finance.invoices.pdf')
        ->middleware('can:invoices.print');
    
    Route::post('invoices/{invoice}/void', [InvoiceController::class, 'void'])
        ->name('finance.invoices.void')
        ->middleware('can:invoices.void');
    
    Route::post('invoices/{invoice}/apply-payment', [InvoiceController::class, 'applyPayment'])
        ->name('finance.invoices.apply-payment');
    
    Route::get('invoices/export', [InvoiceController::class, 'export'])
        ->name('finance.invoices.export')
        ->middleware('can:invoices.export');
    
    Route::post('invoices/bulk-send-zatca', [InvoiceController::class, 'bulkSendToZatca'])
        ->name('finance.invoices.bulk-send-zatca')
        ->middleware('can:invoices.send_zatca');
    
    Route::get('invoices-daily-summary', [InvoiceController::class, 'dailySummary'])
        ->name('finance.invoices.daily-summary');
    
    // Reservation Invoices
    // Route::resource('reservation-invoices', ReservationInvoiceController::class)
    //     ->names('reservation-invoices');
    
    // Invoice Credit Notes
    // Route::resource('invoice-credit-notes', InvoiceCreditNoteController::class)
    //     ->names('invoice-credit-notes');
    
    // Invoice Transfers
    // Route::resource('invoice-transfers', InvoiceTransferController::class)
    //     ->names('invoice-transfers');
    
    // Promissories
    // Route::resource('promissories', PromissoryController::class)
    //     ->names('promissories');
    
    // Route::post('promissories/{promissory}/collect', [PromissoryController::class, 'collect'])
    //     ->name('promissories.collect')
    //     ->middleware('can:promissories.collect');
    
    // Route::post('promissories/{promissory}/partial-collect', [PromissoryController::class, 'partialCollect'])
    //     ->name('promissories.partial-collect')
    //     ->middleware('can:promissories.collect');
    
    // Cashier Shifts
    // Route::resource('cashier-shifts', CashierShiftController::class)
    //     ->names('cashier-shifts');
    
    // Route::post('cashier-shifts/open', [CashierShiftController::class, 'open'])
    //     ->name('cashier-shifts.open')
    //     ->middleware('can:cashier-shifts.open');
    
    // Route::post('cashier-shifts/{shift}/close', [CashierShiftController::class, 'close'])
    //     ->name('cashier-shifts.close')
    //     ->middleware('can:cashier-shifts.close');
    
    // Route::post('cashier-shifts/{shift}/approve', [CashierShiftController::class, 'approve'])
    //     ->name('cashier-shifts.approve')
    //     ->middleware('can:cashier-shifts.approve');
    
    // Route::get('cashier-shifts/{shift}/report', [CashierShiftController::class, 'report'])
    //     ->name('cashier-shifts.report')
    //     ->middleware('can:cashier-shifts.view');
    
    // Checkout Balance Transfers
    // Route::resource('checkout-balance-transfers', CheckoutBalanceTransferController::class)
    //     ->names('checkout-balance-transfers');
    
    // Banks
    // Route::resource('banks', BankController::class)
    //     ->names('banks');
    
    // Senders
    // Route::resource('senders', SenderController::class)
    //     ->names('senders');
    
    // Commission Payments
    // Route::resource('commission-payments', CommissionPaymentController::class)
    //     ->names('commission-payments');
    
    // ZATCA E-Invoices
    // Route::get('zatca/e-invoices', [ZatcaController::class, 'index'])
    //     ->name('zatca.e-invoices.index')
    //     ->middleware('can:zatca.view');
    
    // Route::post('zatca/e-invoices/{invoice}/report', [ZatcaController::class, 'report'])
    //     ->name('zatca.e-invoices.report')
    //     ->middleware('can:zatca.report');
    
    // Route::post('zatca/e-invoices/{invoice}/clear', [ZatcaController::class, 'clear'])
    //     ->name('zatca.e-invoices.clear')
    //     ->middleware('can:zatca.report');
    
    // Route::get('zatca/e-invoices/{invoice}/xml', [ZatcaController::class, 'downloadXml'])
    //     ->name('zatca.e-invoices.xml')
    //     ->middleware('can:zatca.view');
    
    // Qoyod Sync
    // Route::post('qoyod/sync/{type}/{id}', [QoyodController::class, 'sync'])
    //     ->name('qoyod.sync')
    //     ->middleware('can:qoyod.sync');
    
    // Route::get('qoyod/sync-status/{type}/{id}', [QoyodController::class, 'syncStatus'])
    //     ->name('qoyod.sync-status')
    //     ->middleware('can:qoyod.view');
});
